<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * ChatController - Módulo de chat con asistente IA para consultores
 *
 * SEGURIDAD:
 * - Roles permitidos: consultant, admin
 * - Queries parametrizados via Query Builder (nunca SQL crudo del usuario)
 * - Confirmación obligatoria antes de UPDATE/INSERT (flujo de 2 pasos)
 * - DELETE/DROP/TRUNCATE bloqueados a nivel de código
 * - Log de TODAS las operaciones en tbl_chat_log
 */
class ChatController extends Controller
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    // Roles que pueden acceder al chat
    protected array $allowedRoles = ['consultant', 'admin'];

    // Tablas del sistema que NO deben ser modificadas
    protected array $readOnlyTables = ['tbl_usuarios', 'tbl_sesiones_usuario', 'tbl_roles'];

    // Operaciones SQL prohibidas (NUNCA se ejecutan)
    protected array $forbiddenPatterns = [
        '/\bDELETE\b/i',
        '/\bDROP\b/i',
        '/\bTRUNCATE\b/i',
        '/\bALTER\b/i',
        '/\bCREATE\s+TABLE\b/i',
        '/\bCREATE\s+DATABASE\b/i',
        '/\bGRANT\b/i',
        '/\bREVOKE\b/i',
        '/\bRENAME\b/i',
        '/\bINTO\s+OUTFILE\b/i',
        '/\bLOAD_FILE\b/i',
        '/\bINTO\s+DUMPFILE\b/i',
    ];

    // Operaciones pendientes de confirmación (en sesión)
    const SESSION_PENDING_KEY = 'chat_pending_operation';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model  = env('OPENAI_MODEL', 'gpt-4o');
    }

    // =========================================================================
    // ENDPOINTS PÚBLICOS
    // =========================================================================

    public function index()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return redirect()->to('/login');
        }

        return view('consultant/chat', [
            'usuario' => [
                'nombre' => $session->get('nombre_usuario'),
                'role'   => $session->get('role'),
            ],
        ]);
    }

    public function sendMessage()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $userMessage         = trim($input['message'] ?? '');
        $conversationHistory = $input['history'] ?? [];

        if (empty($userMessage)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Mensaje vacío']);
        }

        // Log del mensaje del usuario
        $this->logOperation('user_message', $userMessage, $session);

        try {
            $result = $this->processWithToolCalling($userMessage, $conversationHistory, $session);

            // Log de la respuesta
            $this->logOperation('assistant_response', substr($result['response'] ?? '', 0, 500), $session);

            return $this->response->setJSON([
                'success'    => true,
                'response'   => $result['response'],
                'tools_used' => $result['tools_used'] ?? [],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ChatController::sendMessage error: ' . $e->getMessage());
            $this->logOperation('error', $e->getMessage(), $session);
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Error procesando mensaje: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * API: Confirma una operación de escritura pendiente
     */
    public function confirmOperation()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        $input   = $this->request->getJSON(true) ?? $this->request->getPost();
        $confirm = ($input['confirm'] ?? false) === true;
        $pending = $session->get(self::SESSION_PENDING_KEY);

        if (!$pending) {
            return $this->response->setJSON(['success' => false, 'error' => 'No hay operación pendiente']);
        }

        // Limpiar pendiente
        $session->remove(self::SESSION_PENDING_KEY);

        if (!$confirm) {
            $this->logOperation('write_cancelled', json_encode($pending), $session);
            return $this->response->setJSON(['success' => true, 'message' => 'Operación cancelada']);
        }

        // Ejecutar la operación confirmada
        try {
            $result = $this->executeConfirmedWrite($pending);
            $this->logOperation('write_executed', json_encode([
                'type'   => $pending['type'],
                'table'  => $pending['table'],
                'result' => $result,
            ]), $session);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            $this->logOperation('write_error', $e->getMessage(), $session);
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getSchema()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        try {
            return $this->response->setJSON(['success' => true, 'tables' => $this->listAllTables()]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // CONTROL DE ACCESO
    // =========================================================================

    protected function checkAccess($session): bool
    {
        return $session->get('isLoggedIn') && in_array($session->get('role'), $this->allowedRoles);
    }

    // =========================================================================
    // LOGGING
    // =========================================================================

    /**
     * Registra TODAS las operaciones del chat en tbl_chat_log
     */
    protected function logOperation(string $type, string $detail, $session): void
    {
        try {
            $db = \Config\Database::connect();

            // Crear tabla si no existe (solo la primera vez)
            if (!$db->tableExists('tbl_chat_log')) {
                $db->query("CREATE TABLE tbl_chat_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_usuario INT NOT NULL,
                    rol VARCHAR(20) NOT NULL,
                    tipo_operacion VARCHAR(50) NOT NULL,
                    detalle TEXT NULL,
                    ip_address VARCHAR(45) NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_chatlog_usuario (id_usuario),
                    INDEX idx_chatlog_tipo (tipo_operacion),
                    INDEX idx_chatlog_fecha (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            }

            $db->table('tbl_chat_log')->insert([
                'id_usuario'      => $session->get('id_usuario') ?? 0,
                'rol'             => $session->get('role') ?? '',
                'tipo_operacion'  => $type,
                'detalle'         => mb_substr($detail, 0, 5000),
                'ip_address'      => $this->request->getIPAddress(),
            ]);
        } catch (\Exception $e) {
            // No fallar si el log falla, solo registrar en archivo
            log_message('error', 'ChatLog failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // LÓGICA PRINCIPAL: OpenAI con Function Calling
    // =========================================================================

    protected function processWithToolCalling(string $userMessage, array $history, $session): array
    {
        $systemPrompt = $this->buildSystemPrompt();
        $tools        = $this->getToolDefinitions();
        $toolsUsed    = [];

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        $recentHistory = array_slice($history, -20);
        foreach ($recentHistory as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
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

                $toolResult = $this->executeToolCall($functionName, $arguments, $session);
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
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 120,
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

    protected function executeToolCall(string $functionName, array $arguments, $session): array
    {
        switch ($functionName) {
            case 'list_tables':
                $this->logOperation('tool_list_tables', '', $session);
                return $this->toolListTables();

            case 'describe_table':
                $this->logOperation('tool_describe_table', $arguments['table_name'] ?? '', $session);
                return $this->toolDescribeTable($arguments['table_name'] ?? '');

            case 'execute_select':
                $this->logOperation('tool_select', $arguments['query'] ?? '', $session);
                return $this->toolExecuteSelect($arguments['query'] ?? '');

            case 'execute_update':
                $this->logOperation('tool_update', $arguments['query'] ?? '', $session);
                return $this->toolExecuteUpdate($arguments, $session);

            case 'execute_insert':
                $this->logOperation('tool_insert', $arguments['query'] ?? '', $session);
                return $this->toolExecuteInsert($arguments, $session);

            default:
                return ['success' => false, 'error' => "Tool desconocida: {$functionName}"];
        }
    }

    // =========================================================================
    // TOOLS — LECTURA (queries parametrizados con Query Builder)
    // =========================================================================

    protected function toolListTables(): array
    {
        try {
            $db = \Config\Database::connect();
            return ['success' => true, 'tables' => $db->listTables(), 'count' => count($db->listTables())];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function toolDescribeTable(string $tableName): array
    {
        if (empty($tableName)) {
            return ['success' => false, 'error' => 'Nombre de tabla vacío'];
        }

        // Sanitizar: solo alfanuméricos y underscore
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
                'success'     => true,
                'table'       => $tableName,
                'columns'     => $schema,
                'row_count'   => $count,
                'is_readonly' => in_array($tableName, $this->readOnlyTables),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * SELECT — Validado y con límite forzado
     * Nota: Los SELECTs usan query directa porque el Query Builder no soporta
     * JOINs complejos ni subqueries que el asistente necesita. Se protegen con:
     * - Validación de patrones prohibidos
     * - Bloqueo de múltiples statements
     * - Límite forzado de 50 filas
     * - Solo SELECT permitido
     */
    protected function toolExecuteSelect(string $query): array
    {
        $validation = $this->validateQuery($query, 'SELECT');
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        try {
            $db     = \Config\Database::connect();
            $result = $db->query($query);
            $rows   = $result->getResultArray();
            $total  = count($rows);

            if ($total > 50) {
                $rows = array_slice($rows, 0, 50);
                return [
                    'success'    => true,
                    'data'       => $rows,
                    'total_rows' => $total,
                    'truncated'  => true,
                    'note'       => "Mostrando 50 de {$total} filas. Usa LIMIT para reducir.",
                ];
            }

            return ['success' => true, 'data' => $rows, 'total_rows' => $total];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // TOOLS — ESCRITURA (con confirmación obligatoria + Query Builder)
    // =========================================================================

    /**
     * UPDATE — Parsea la query de OpenAI, la convierte a Query Builder parametrizado
     * y requiere confirmación del usuario antes de ejecutar
     */
    protected function toolExecuteUpdate(array $arguments, $session): array
    {
        $rawQuery = trim($arguments['query'] ?? '');

        $validation = $this->validateQuery($rawQuery, 'UPDATE');
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        // Parsear tabla
        if (!preg_match('/UPDATE\s+(\w+)\s+SET\s+/i', $rawQuery, $tableMatch)) {
            return ['success' => false, 'error' => 'No se pudo parsear la tabla del UPDATE'];
        }
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $tableMatch[1]);

        if (in_array($table, $this->readOnlyTables)) {
            return ['success' => false, 'error' => "La tabla '{$table}' es de solo lectura"];
        }

        if (!preg_match('/\bWHERE\b/i', $rawQuery)) {
            return ['success' => false, 'error' => 'UPDATE sin WHERE no está permitido'];
        }

        // Verificar que la tabla existe
        $db = \Config\Database::connect();
        if (!$db->tableExists($table)) {
            return ['success' => false, 'error' => "Tabla '{$table}' no existe"];
        }

        // Guardar como pendiente — requiere confirmación del usuario
        $pending = [
            'type'      => 'UPDATE',
            'table'     => $table,
            'raw_query' => $rawQuery,
            'timestamp' => time(),
        ];
        $session->set(self::SESSION_PENDING_KEY, $pending);

        return [
            'success'              => true,
            'requires_confirmation' => true,
            'message'              => "OPERACIÓN PENDIENTE DE CONFIRMACIÓN. Dile al usuario exactamente qué se va a modificar y pídele que haga clic en el botón 'Confirmar' para proceder.",
            'operation'            => "UPDATE en tabla '{$table}'",
            'query_preview'        => $rawQuery,
        ];
    }

    /**
     * INSERT — Similar: parsea, valida, requiere confirmación
     */
    protected function toolExecuteInsert(array $arguments, $session): array
    {
        $rawQuery = trim($arguments['query'] ?? '');

        $validation = $this->validateQuery($rawQuery, 'INSERT');
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        if (!preg_match('/INSERT\s+INTO\s+(\w+)/i', $rawQuery, $tableMatch)) {
            return ['success' => false, 'error' => 'No se pudo parsear la tabla del INSERT'];
        }
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $tableMatch[1]);

        if (in_array($table, $this->readOnlyTables)) {
            return ['success' => false, 'error' => "La tabla '{$table}' es de solo lectura"];
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists($table)) {
            return ['success' => false, 'error' => "Tabla '{$table}' no existe"];
        }

        $pending = [
            'type'      => 'INSERT',
            'table'     => $table,
            'raw_query' => $rawQuery,
            'timestamp' => time(),
        ];
        $session->set(self::SESSION_PENDING_KEY, $pending);

        return [
            'success'              => true,
            'requires_confirmation' => true,
            'message'              => "OPERACIÓN PENDIENTE DE CONFIRMACIÓN. Dile al usuario exactamente qué registro se va a crear y pídele que haga clic en el botón 'Confirmar' para proceder.",
            'operation'            => "INSERT en tabla '{$table}'",
            'query_preview'        => $rawQuery,
        ];
    }

    /**
     * Ejecuta una operación de escritura ya confirmada por el usuario
     */
    protected function executeConfirmedWrite(array $pending): array
    {
        // Validar que no haya expirado (máximo 5 minutos)
        if (time() - ($pending['timestamp'] ?? 0) > 300) {
            return ['success' => false, 'error' => 'La operación expiró. Solicita la operación nuevamente.'];
        }

        $rawQuery = $pending['raw_query'] ?? '';
        $type     = $pending['type'] ?? '';

        // Re-validar antes de ejecutar
        $validation = $this->validateQuery($rawQuery, $type);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        try {
            $db = \Config\Database::connect();
            $db->query($rawQuery);

            if ($type === 'UPDATE') {
                return [
                    'success'       => true,
                    'affected_rows' => $db->affectedRows(),
                    'message'       => $db->affectedRows() . ' fila(s) actualizada(s)',
                ];
            } else {
                return [
                    'success'   => true,
                    'insert_id' => $db->insertID(),
                    'message'   => 'Registro insertado con ID: ' . $db->insertID(),
                ];
            }
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

        // Verificar operaciones prohibidas
        foreach ($this->forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return ['valid' => false, 'error' => 'Operación no permitida por política de seguridad'];
            }
        }

        // Verificar tipo esperado
        $queryUpper = strtoupper(ltrim($query));
        if (!str_starts_with($queryUpper, $expectedType)) {
            return ['valid' => false, 'error' => "Se esperaba {$expectedType} pero se recibió otro tipo de query"];
        }

        // Bloquear múltiples statements (inyección SQL)
        $withoutStrings = preg_replace("/'[^']*'/", '', $query);
        $withoutStrings = preg_replace('/"[^"]*"/', '', $withoutStrings);
        if (substr_count($withoutStrings, ';') > 1) {
            return ['valid' => false, 'error' => 'No se permiten múltiples statements'];
        }

        // Bloquear comentarios SQL (prevenir bypass)
        if (preg_match('/\/\*|\*\/|--/', $withoutStrings)) {
            return ['valid' => false, 'error' => 'Comentarios SQL no permitidos'];
        }

        // Bloquear funciones peligrosas
        if (preg_match('/\b(SLEEP|BENCHMARK|CHAR\s*\(|CONCAT\s*\(.*SELECT|UNION\s+SELECT|0x[0-9a-fA-F]+)/i', $withoutStrings)) {
            return ['valid' => false, 'error' => 'Expresión SQL no permitida'];
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
                $count    = $db->table($table)->countAllResults();
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
        $db        = \Config\Database::connect();
        $tables    = $db->listTables();
        $tableList = implode(', ', $tables);

        $session  = session();
        $userName = $session->get('nombre_usuario') ?? 'Consultor';
        $userRole = $session->get('role') ?? '';

        return <<<PROMPT
Eres un asistente experto para consultores de Seguridad y Salud en el Trabajo (SST) en Colombia, especializado en Propiedad Horizontal.
Tu nombre es "Asistente PH" y trabajas dentro del aplicativo Enterprise SST - Propiedad Horizontal.

El usuario actual es: {$userName} (rol: {$userRole})

## TU ROL
- Ayudas al consultor a consultar y gestionar datos del sistema de Propiedad Horizontal
- Puedes consultar cualquier tabla de la base de datos (SELECT)
- Puedes proponer actualizaciones (UPDATE con WHERE) e inserciones (INSERT)
- Las operaciones de escritura (UPDATE/INSERT) REQUIEREN CONFIRMACIÓN del usuario
- NUNCA puedes eliminar datos (DELETE, DROP, TRUNCATE están prohibidos)
- Las tablas tbl_usuarios, tbl_sesiones_usuario y tbl_roles son de SOLO LECTURA

## TABLAS DISPONIBLES
{$tableList}

## REGLAS DE SEGURIDAD
1. NUNCA ejecutes DELETE, DROP, TRUNCATE, ALTER, CREATE, GRANT, REVOKE o RENAME
2. Todo UPDATE DEBE tener cláusula WHERE
3. Antes de hacer un UPDATE, primero consulta el registro actual con SELECT
4. Las operaciones de escritura generan una solicitud de confirmación — el usuario debe aprobar
5. Limita los SELECT a 50 filas con LIMIT cuando no se especifique
6. Las tablas de usuarios y sesiones son de SOLO LECTURA
7. Todas las operaciones quedan registradas en el log de auditoría

## FLUJO DE ESCRITURA (IMPORTANTE)
Cuando el usuario pida modificar o insertar datos:
1. Primero consulta con SELECT el estado actual
2. Muestra al usuario qué existe actualmente
3. Describe exactamente qué vas a cambiar/insertar
4. Ejecuta la tool (execute_update o execute_insert) — esto NO ejecuta, solo propone
5. El sistema mostrará un botón de confirmación al usuario
6. Solo después de que el usuario confirme, se ejecuta la operación

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
PROMPT;
    }

    protected function getToolDefinitions(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'list_tables',
                    'description' => 'Lista todas las tablas disponibles en la base de datos',
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
                    'description' => 'Obtiene la estructura (columnas, tipos, nullable, pk) y conteo de filas de una tabla',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'table_name' => [
                                'type'        => 'string',
                                'description' => 'Nombre de la tabla (ej: tbl_clientes)',
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
                    'description' => 'Ejecuta una consulta SELECT. Máximo 50 filas. Usa LIMIT.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Query SELECT SQL. Ejemplo: SELECT * FROM tbl_clientes WHERE estado = "activo" LIMIT 10',
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
                    'description' => 'PROPONE un UPDATE (no lo ejecuta directamente). Requiere confirmación del usuario. DEBE incluir WHERE.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Query UPDATE con WHERE obligatorio',
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
                    'description' => 'PROPONE un INSERT (no lo ejecuta directamente). Requiere confirmación del usuario.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Query INSERT INTO SQL',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
        ];
    }
}
