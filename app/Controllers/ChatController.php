<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * ChatController - Módulo de chat con asistente IA para consultores
 *
 * Permite interactuar con un asistente que tiene acceso de lectura y escritura
 * a todas las tablas del aplicativo. NUNCA permite eliminación de datos.
 */
class ChatController extends Controller
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    // Tablas del sistema que NO deben ser modificadas
    protected array $readOnlyTables = ['tbl_usuarios', 'tbl_sesiones_usuario', 'tbl_roles'];

    // Operaciones SQL prohibidas (NUNCA se ejecutan)
    protected array $forbiddenPatterns = [
        '/\bDELETE\b/i',
        '/\bDROP\b/i',
        '/\bTRUNCATE\b/i',
        '/\bALTER\b/i',
        '/\bCREATE\b/i',
        '/\bGRANT\b/i',
        '/\bREVOKE\b/i',
        '/\bRENAME\b/i',
    ];

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model  = env('OPENAI_MODEL', 'gpt-4o');
    }

    /**
     * Renderiza la vista del chat
     */
    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['consultant', 'admin'])) {
            return redirect()->to('/login');
        }

        $data = [
            'usuario' => [
                'nombre' => $session->get('nombre_usuario'),
                'role'   => $session->get('role'),
            ],
        ];

        return view('consultant/chat', $data);
    }

    /**
     * API: Recibe mensaje del usuario y devuelve respuesta del asistente
     */
    public function sendMessage()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['consultant', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $userMessage    = trim($input['message'] ?? '');
        $conversationHistory = $input['history'] ?? [];

        if (empty($userMessage)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Mensaje vacío']);
        }

        try {
            $result = $this->processWithToolCalling($userMessage, $conversationHistory);
            return $this->response->setJSON([
                'success'  => true,
                'response' => $result['response'],
                'tools_used' => $result['tools_used'] ?? [],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ChatController::sendMessage error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Error procesando mensaje: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * API: Devuelve el schema de la base de datos
     */
    public function getSchema()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['consultant', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        try {
            $tables = $this->listAllTables();
            return $this->response->setJSON(['success' => true, 'tables' => $tables]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // LÓGICA PRINCIPAL: OpenAI con Function Calling
    // =========================================================================

    protected function processWithToolCalling(string $userMessage, array $history): array
    {
        $systemPrompt = $this->buildSystemPrompt();
        $tools        = $this->getToolDefinitions();
        $toolsUsed    = [];

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        $recentHistory = array_slice($history, -20);
        foreach ($recentHistory as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messages[] = [
                    'role'    => $msg['role'],
                    'content' => $msg['content'],
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $maxIterations = 8;
        for ($i = 0; $i < $maxIterations; $i++) {
            $apiResponse = $this->callOpenAI($messages, $tools);

            if (!$apiResponse['success']) {
                throw new \Exception($apiResponse['error']);
            }

            $choice  = $apiResponse['data']['choices'][0] ?? null;
            $message = $choice['message'] ?? null;

            if (!$message) {
                throw new \Exception('Respuesta vacía de OpenAI');
            }

            if ($choice['finish_reason'] === 'stop' || empty($message['tool_calls'])) {
                return [
                    'response'   => $message['content'] ?? '',
                    'tools_used' => $toolsUsed,
                ];
            }

            $messages[] = $message;

            foreach ($message['tool_calls'] as $toolCall) {
                $functionName = $toolCall['function']['name'];
                $arguments    = json_decode($toolCall['function']['arguments'], true) ?? [];

                $toolResult = $this->executeToolCall($functionName, $arguments);
                $toolsUsed[] = [
                    'tool'   => $functionName,
                    'args'   => $arguments,
                    'status' => $toolResult['success'] ? 'ok' : 'error',
                ];

                $messages[] = [
                    'role'         => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content'      => json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        return [
            'response'   => 'Se alcanzó el límite de consultas en una sola interacción. Por favor reformula tu pregunta.',
            'tools_used' => $toolsUsed,
        ];
    }

    protected function callOpenAI(array $messages, array $tools): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $data = [
            'model'       => $this->model,
            'messages'    => $messages,
            'tools'       => $tools,
            'temperature' => 0.3,
            'max_tokens'  => 4000,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS  => json_encode($data),
            CURLOPT_TIMEOUT     => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "Error cURL: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? "Error HTTP {$httpCode}";
            return ['success' => false, 'error' => $errorMsg];
        }

        return ['success' => true, 'data' => $result];
    }

    protected function executeToolCall(string $functionName, array $arguments): array
    {
        switch ($functionName) {
            case 'list_tables':
                return $this->toolListTables();
            case 'describe_table':
                return $this->toolDescribeTable($arguments['table_name'] ?? '');
            case 'execute_select':
                return $this->toolExecuteSelect($arguments['query'] ?? '');
            case 'execute_update':
                return $this->toolExecuteUpdate($arguments['query'] ?? '');
            case 'execute_insert':
                return $this->toolExecuteInsert($arguments['query'] ?? '');
            default:
                return ['success' => false, 'error' => "Tool desconocida: {$functionName}"];
        }
    }

    // =========================================================================
    // TOOLS
    // =========================================================================

    protected function toolListTables(): array
    {
        try {
            $db     = \Config\Database::connect();
            $tables = $db->listTables();
            return ['success' => true, 'tables' => $tables, 'count' => count($tables)];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function toolDescribeTable(string $tableName): array
    {
        if (empty($tableName)) {
            return ['success' => false, 'error' => 'Nombre de tabla vacío'];
        }

        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists($tableName)) {
                return ['success' => false, 'error' => "Tabla '{$tableName}' no existe"];
            }

            $fields = $db->getFieldData($tableName);
            $count  = $db->table($tableName)->countAllResults();

            $schema = [];
            foreach ($fields as $field) {
                $schema[] = [
                    'name'        => $field->name,
                    'type'        => $field->type,
                    'max_length'  => $field->max_length ?? null,
                    'nullable'    => $field->nullable ?? null,
                    'default'     => $field->default ?? null,
                    'primary_key' => $field->primary_key ?? false,
                ];
            }

            return [
                'success'    => true,
                'table'      => $tableName,
                'columns'    => $schema,
                'row_count'  => $count,
                'is_readonly' => in_array($tableName, $this->readOnlyTables),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function toolExecuteSelect(string $query): array
    {
        $validation = $this->validateQuery($query, 'SELECT');
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        try {
            $db      = \Config\Database::connect();
            $result  = $db->query($query);
            $rows    = $result->getResultArray();
            $numRows = count($rows);

            if ($numRows > 50) {
                $rows = array_slice($rows, 0, 50);
                return [
                    'success'    => true,
                    'data'       => $rows,
                    'total_rows' => $numRows,
                    'truncated'  => true,
                    'note'       => "Mostrando 50 de {$numRows} filas. Usa LIMIT para reducir resultados.",
                ];
            }

            return ['success' => true, 'data' => $rows, 'total_rows' => $numRows];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()];
        }
    }

    protected function toolExecuteUpdate(string $query): array
    {
        $validation = $this->validateQuery($query, 'UPDATE');
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        if (preg_match('/UPDATE\s+(\w+)/i', $query, $matches)) {
            $table = $matches[1];
            if (in_array($table, $this->readOnlyTables)) {
                return ['success' => false, 'error' => "La tabla '{$table}' es de solo lectura por seguridad"];
            }
        }

        if (!preg_match('/\bWHERE\b/i', $query)) {
            return ['success' => false, 'error' => 'UPDATE sin WHERE no está permitido. Debes especificar una condición.'];
        }

        try {
            $db = \Config\Database::connect();
            $db->query($query);
            $affectedRows = $db->affectedRows();

            return [
                'success'       => true,
                'affected_rows' => $affectedRows,
                'message'       => "{$affectedRows} fila(s) actualizada(s)",
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()];
        }
    }

    protected function toolExecuteInsert(string $query): array
    {
        $validation = $this->validateQuery($query, 'INSERT');
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        if (preg_match('/INSERT\s+INTO\s+(\w+)/i', $query, $matches)) {
            $table = $matches[1];
            if (in_array($table, $this->readOnlyTables)) {
                return ['success' => false, 'error' => "La tabla '{$table}' es de solo lectura por seguridad"];
            }
        }

        try {
            $db = \Config\Database::connect();
            $db->query($query);
            $insertId = $db->insertID();

            return [
                'success'   => true,
                'insert_id' => $insertId,
                'message'   => "Registro insertado con ID: {$insertId}",
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // VALIDACIÓN Y SEGURIDAD
    // =========================================================================

    protected function validateQuery(string $query, string $expectedType): array
    {
        $query = trim($query);

        if (empty($query)) {
            return ['valid' => false, 'error' => 'Query vacío'];
        }

        foreach ($this->forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                $operation = strtoupper(trim($pattern, '/\\bi'));
                return ['valid' => false, 'error' => "Operación '{$operation}' no está permitida"];
            }
        }

        $queryUpper = strtoupper(ltrim($query));
        if (!str_starts_with($queryUpper, $expectedType)) {
            return ['valid' => false, 'error' => "Se esperaba una query {$expectedType} pero se recibió otro tipo"];
        }

        $withoutStrings = preg_replace("/'[^']*'/", '', $query);
        $withoutStrings = preg_replace('/"[^"]*"/', '', $withoutStrings);
        $semiCount = substr_count($withoutStrings, ';');
        if ($semiCount > 1) {
            return ['valid' => false, 'error' => 'No se permiten múltiples statements en una sola query'];
        }

        return ['valid' => true];
    }

    protected function listAllTables(): array
    {
        $db     = \Config\Database::connect();
        $tables = $db->listTables();
        $result = [];

        foreach ($tables as $table) {
            try {
                $count = $db->table($table)->countAllResults();
                $result[] = ['name' => $table, 'rows' => $count];
            } catch (\Exception $e) {
                $result[] = ['name' => $table, 'rows' => '?'];
            }
        }

        return $result;
    }

    // =========================================================================
    // PROMPTS Y TOOLS
    // =========================================================================

    protected function buildSystemPrompt(): string
    {
        $db     = \Config\Database::connect();
        $tables = $db->listTables();
        $tableList = implode(', ', $tables);

        $session = session();
        $userName = $session->get('nombre_usuario') ?? 'Consultor';

        return <<<PROMPT
Eres un asistente experto para consultores de Seguridad y Salud en el Trabajo (SST) en Colombia, especializado en Propiedad Horizontal.
Tu nombre es "Asistente PH" y trabajas dentro del aplicativo Enterprise SST - Propiedad Horizontal.

El usuario actual es: {$userName} (rol: {$session->get('role')})

## TU ROL
- Ayudas al consultor a consultar y gestionar datos del sistema de Propiedad Horizontal
- Puedes consultar cualquier tabla de la base de datos (SELECT)
- Puedes actualizar registros existentes (UPDATE con WHERE)
- Puedes insertar nuevos registros (INSERT)
- NUNCA puedes eliminar datos (DELETE, DROP, TRUNCATE están prohibidos)
- Las tablas tbl_usuarios, tbl_sesiones_usuario y tbl_roles son de SOLO LECTURA

## TABLAS DISPONIBLES
{$tableList}

## REGLAS DE SEGURIDAD
1. NUNCA ejecutes DELETE, DROP, TRUNCATE, ALTER, CREATE, GRANT, REVOKE o RENAME
2. Todo UPDATE DEBE tener cláusula WHERE (no actualizar toda la tabla)
3. Antes de hacer un UPDATE, primero consulta el registro actual con SELECT
4. Confirma con el usuario antes de ejecutar cambios (UPDATE/INSERT) - describe qué harás
5. Limita los SELECT a 50 filas con LIMIT cuando no se especifique
6. Las tablas de usuarios y sesiones son de SOLO LECTURA

## FORMATO DE RESPUESTA
- Responde en español
- Usa formato Markdown para tablas y código
- Cuando muestres datos, formátalos en tablas legibles
- Si haces un cambio, confirma qué se modificó
- Si no puedes hacer algo, explica por qué

## CONTEXTO DEL SISTEMA
- Base de datos: propiedad_horizontal (MySQL)
- Framework: CodeIgniter 4
- El sistema gestiona: conjuntos residenciales, copropiedades, contratos, inspecciones SST, actas de visita, documentos, comités, capacitaciones, indicadores, planes de trabajo, pendientes, mantenimientos, etc.
- Prefijo de tablas: la mayoría usa 'tbl_' como prefijo
- Los clientes son conjuntos residenciales / edificios / copropiedades

## FLUJO DE TRABAJO
1. Cuando el usuario pregunte algo, usa las herramientas para consultar la BD
2. Primero haz un describe_table para entender la estructura
3. Luego ejecuta el SELECT apropiado
4. Presenta los resultados de forma clara
5. Para cambios: primero muestra el estado actual → confirma → ejecuta
PROMPT;
    }

    protected function getToolDefinitions(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'list_tables',
                    'description' => 'Lista todas las tablas disponibles en la base de datos con su conteo de filas',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => new \stdClass(),
                        'required'   => [],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'describe_table',
                    'description' => 'Obtiene la estructura (columnas, tipos, nullable, pk) de una tabla específica y su conteo de filas',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'table_name' => [
                                'type'        => 'string',
                                'description' => 'Nombre de la tabla a describir (ej: tbl_clientes)',
                            ],
                        ],
                        'required' => ['table_name'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'execute_select',
                    'description' => 'Ejecuta una consulta SELECT en la base de datos. Máximo 50 filas por consulta. Usa LIMIT para controlar resultados.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Query SELECT SQL válido. Ejemplo: SELECT * FROM tbl_clientes WHERE estado = "activo" LIMIT 10',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'execute_update',
                    'description' => 'Ejecuta un UPDATE en la base de datos. DEBE incluir WHERE. Primero consulta el registro con SELECT antes de actualizar. Confirma con el usuario antes de ejecutar.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Query UPDATE SQL con WHERE obligatorio. Ejemplo: UPDATE tbl_clientes SET telefono_1_cliente = "3001234567" WHERE id_cliente = 5',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'execute_insert',
                    'description' => 'Inserta un nuevo registro en la base de datos. Confirma con el usuario los datos antes de insertar.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Query INSERT INTO SQL. Ejemplo: INSERT INTO tbl_pendientes (id_cliente, detalle_mantenimiento, estado) VALUES (5, "Actualizar extintor", "ABIERTA")',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
        ];
    }
}
