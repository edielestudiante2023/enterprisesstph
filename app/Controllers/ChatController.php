<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * ChatController - Módulo de chat con asistente IA para consultores
 *
 * SEGURIDAD:
 * - Roles: consultant, admin
 * - Motor: GPT-4o (OpenAI function calling)
 * - UPDATE/INSERT: confirmación simple (botón Confirmar/Cancelar)
 * - DELETE: doble confirmación aritmética (ej: "¿Cuánto es 7+3?")
 * - DROP/TRUNCATE: bloqueados permanentemente
 * - Log de TODAS las operaciones en tbl_chat_log
 * - PWA para uso desde celular
 */
class ChatController extends Controller
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    protected array $allowedRoles = ['consultant', 'admin'];

    // Tablas que NUNCA se pueden modificar ni eliminar registros
    protected array $readOnlyTables = ['tbl_usuarios', 'tbl_sesiones_usuario', 'tbl_roles'];

    // Operaciones SQL prohibidas permanentemente
    protected array $forbiddenPatterns = [
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

    const SESSION_PENDING_KEY = 'chat_pending_operation';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model  = env('OPENAI_MODEL', 'gpt-4o');
    }

    // =========================================================================
    // ENDPOINTS
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

        $input               = $this->request->getJSON(true) ?? $this->request->getPost();
        $userMessage         = trim($input['message'] ?? '');
        $conversationHistory = $input['history'] ?? [];

        if (empty($userMessage)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Mensaje vacío']);
        }

        $this->logOperation('user_message', $userMessage, $session);

        try {
            $result = $this->processWithToolCalling($userMessage, $conversationHistory, $session);
            $this->logOperation('assistant_response', substr($result['response'] ?? '', 0, 500), $session);

            return $this->response->setJSON([
                'success'    => true,
                'response'   => $result['response'],
                'tools_used' => $result['tools_used'] ?? [],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ChatController::sendMessage error: ' . $e->getMessage());
            $this->logOperation('error', $e->getMessage(), $session);
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Error procesando mensaje: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Confirmación simple para UPDATE/INSERT (botón Confirmar/Cancelar)
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

        if (!$pending || !in_array($pending['type'] ?? '', ['UPDATE', 'INSERT'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'No hay operación pendiente']);
        }

        $session->remove(self::SESSION_PENDING_KEY);

        if (!$confirm) {
            $this->logOperation('write_cancelled', json_encode($pending), $session);
            return $this->response->setJSON(['success' => true, 'message' => 'Operación cancelada']);
        }

        try {
            $result = $this->executeConfirmedWrite($pending);
            $this->logOperation('write_executed', json_encode([
                'type'   => $pending['type'],
                'table'  => $pending['table'],
                'result' => $result,
            ]), $session);
            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            $this->logOperation('write_error', $e->getMessage(), $session);
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Doble confirmación aritmética para DELETE
     * Paso 1: El usuario hace clic en "Eliminar" → recibe un reto aritmético
     * Paso 2: El usuario escribe la respuesta → si es correcta, se ejecuta
     */
    public function confirmDelete()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        $input   = $this->request->getJSON(true) ?? $this->request->getPost();
        $step    = $input['step'] ?? '';
        $pending = $session->get(self::SESSION_PENDING_KEY);

        if (!$pending || ($pending['type'] ?? '') !== 'DELETE') {
            return $this->response->setJSON(['success' => false, 'error' => 'No hay operación DELETE pendiente']);
        }

        // Paso 1: Generar reto aritmético
        if ($step === 'challenge') {
            $a = rand(2, 15);
            $b = rand(2, 15);
            $answer = $a + $b;

            // Guardar respuesta correcta en sesión
            $pending['arithmetic_answer'] = $answer;
            $session->set(self::SESSION_PENDING_KEY, $pending);

            $this->logOperation('delete_challenge_sent', "Reto: {$a}+{$b}={$answer}", $session);

            return $this->response->setJSON([
                'success'   => true,
                'challenge' => "¿Cuánto es {$a} + {$b}?",
                'message'   => "Para confirmar la eliminación, resuelve esta operación:",
            ]);
        }

        // Paso 2: Verificar respuesta
        if ($step === 'verify') {
            $userAnswer    = intval($input['answer'] ?? -1);
            $correctAnswer = $pending['arithmetic_answer'] ?? null;

            if ($correctAnswer === null) {
                return $this->response->setJSON(['success' => false, 'error' => 'Solicita el reto aritmético primero']);
            }

            if ($userAnswer !== $correctAnswer) {
                $this->logOperation('delete_failed_challenge', "Respuesta incorrecta: {$userAnswer} (correcta: {$correctAnswer})", $session);
                $session->remove(self::SESSION_PENDING_KEY);
                return $this->response->setJSON([
                    'success' => false,
                    'error'   => 'Respuesta incorrecta. Operación cancelada por seguridad.',
                ]);
            }

            // Respuesta correcta — ejecutar DELETE
            $session->remove(self::SESSION_PENDING_KEY);

            try {
                $result = $this->executeConfirmedWrite($pending);
                $this->logOperation('delete_executed', json_encode([
                    'table'  => $pending['table'],
                    'result' => $result,
                ]), $session);
                return $this->response->setJSON($result);
            } catch (\Throwable $e) {
                $this->logOperation('delete_error', $e->getMessage(), $session);
                return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        // Cancelar
        if ($step === 'cancel') {
            $session->remove(self::SESSION_PENDING_KEY);
            $this->logOperation('delete_cancelled', json_encode($pending), $session);
            return $this->response->setJSON(['success' => true, 'message' => 'Eliminación cancelada']);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Step inválido']);
    }

    public function getSchema()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        try {
            return $this->response->setJSON(['success' => true, 'tables' => $this->listAllTables()]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // ACCESO Y LOGGING
    // =========================================================================

    protected function checkAccess($session): bool
    {
        return $session->get('isLoggedIn') && in_array($session->get('role'), $this->allowedRoles);
    }

    private static bool $logTableChecked = false;

    protected function logOperation(string $type, string $detail, $session): void
    {
        try {
            $db = \Config\Database::connect();

            if (!self::$logTableChecked) {
                $db->query("CREATE TABLE IF NOT EXISTS tbl_chat_log (
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
                self::$logTableChecked = true;
            }

            $db->table('tbl_chat_log')->insert([
                'id_usuario'     => $session->get('id_usuario') ?? 0,
                'rol'            => $session->get('role') ?? '',
                'tipo_operacion' => $type,
                'detalle'        => mb_substr($detail, 0, 5000),
                'ip_address'     => $this->request->getIPAddress(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ChatLog failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // OPENAI CON FUNCTION CALLING
    // =========================================================================

    protected function processWithToolCalling(string $userMessage, array $history, $session): array
    {
        $systemPrompt = $this->buildSystemPrompt();
        $tools        = $this->getToolDefinitions();
        $toolsUsed    = [];

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach (array_slice($history, -20) as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        for ($i = 0; $i < 8; $i++) {
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
                return ['response' => $message['content'] ?? '', 'tools_used' => $toolsUsed];
            }

            $messages[] = $message;

            foreach ($message['tool_calls'] as $toolCall) {
                $fn   = $toolCall['function']['name'];
                $args = json_decode($toolCall['function']['arguments'], true) ?? [];

                $toolResult = $this->executeToolCall($fn, $args, $session);
                $toolsUsed[] = ['tool' => $fn, 'args' => $args, 'status' => $toolResult['success'] ? 'ok' : 'error'];

                $messages[] = [
                    'role'         => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content'      => json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        return ['response' => 'Límite de consultas alcanzado. Reformula tu pregunta.', 'tools_used' => $toolsUsed];
    }

    protected function callOpenAI(array $messages, array $tools): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $this->apiKey],
            CURLOPT_POSTFIELDS     => json_encode([
                'model' => $this->model, 'messages' => $messages, 'tools' => $tools,
                'temperature' => 0.3, 'max_tokens' => 4000,
            ]),
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) return ['success' => false, 'error' => "Error cURL: {$error}"];

        $result = json_decode($response, true);
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? "Error HTTP {$httpCode}"];
        }

        return ['success' => true, 'data' => $result];
    }

    protected function executeToolCall(string $fn, array $args, $session): array
    {
        switch ($fn) {
            case 'list_tables':
                $this->logOperation('tool_list_tables', '', $session);
                return $this->toolListTables();
            case 'describe_table':
                $this->logOperation('tool_describe_table', $args['table_name'] ?? '', $session);
                return $this->toolDescribeTable($args['table_name'] ?? '');
            case 'execute_select':
                $this->logOperation('tool_select', $args['query'] ?? '', $session);
                return $this->toolExecuteSelect($args['query'] ?? '');
            case 'execute_update':
                $this->logOperation('tool_update', $args['query'] ?? '', $session);
                return $this->toolExecuteWrite($args, 'UPDATE', $session);
            case 'execute_insert':
                $this->logOperation('tool_insert', $args['query'] ?? '', $session);
                return $this->toolExecuteWrite($args, 'INSERT', $session);
            case 'execute_delete':
                $this->logOperation('tool_delete', $args['query'] ?? '', $session);
                return $this->toolExecuteWrite($args, 'DELETE', $session);
            default:
                return ['success' => false, 'error' => "Tool desconocida: {$fn}"];
        }
    }

    // =========================================================================
    // TOOLS — LECTURA
    // =========================================================================

    protected function toolListTables(): array
    {
        try {
            $db     = \Config\Database::connect();
            $tables = $db->listTables();
            return ['success' => true, 'tables' => $tables, 'count' => count($tables)];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function toolDescribeTable(string $tableName): array
    {
        if (empty($tableName)) return ['success' => false, 'error' => 'Nombre de tabla vacío'];

        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists($tableName)) {
                return ['success' => false, 'error' => "Tabla '{$tableName}' no existe"];
            }

            $fields = $db->getFieldData($tableName);
            $count  = $db->table($tableName)->countAllResults();
            $schema = [];
            foreach ($fields as $f) {
                $schema[] = [
                    'name' => $f->name, 'type' => $f->type,
                    'max_length' => $f->max_length ?? null, 'nullable' => $f->nullable ?? null,
                    'default' => $f->default ?? null, 'primary_key' => $f->primary_key ?? false,
                ];
            }

            return ['success' => true, 'table' => $tableName, 'columns' => $schema, 'row_count' => $count, 'is_readonly' => in_array($tableName, $this->readOnlyTables)];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function toolExecuteSelect(string $query): array
    {
        $v = $this->validateQuery($query, 'SELECT');
        if (!$v['valid']) return ['success' => false, 'error' => $v['error']];

        try {
            $db    = \Config\Database::connect();
            $rows  = $db->query($query)->getResultArray();
            $total = count($rows);

            if ($total > 50) {
                return ['success' => true, 'data' => array_slice($rows, 0, 50), 'total_rows' => $total, 'truncated' => true, 'note' => "Mostrando 50 de {$total}. Usa LIMIT."];
            }
            return ['success' => true, 'data' => $rows, 'total_rows' => $total];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // TOOLS — ESCRITURA (confirmación simple para UPDATE/INSERT, aritmética para DELETE)
    // =========================================================================

    protected function toolExecuteWrite(array $args, string $type, $session): array
    {
        $rawQuery = trim($args['query'] ?? '');
        $v = $this->validateQuery($rawQuery, $type);
        if (!$v['valid']) return ['success' => false, 'error' => $v['error']];

        // Parsear tabla
        $tablePattern = match ($type) {
            'UPDATE' => '/UPDATE\s+(\w+)/i',
            'INSERT' => '/INSERT\s+INTO\s+(\w+)/i',
            'DELETE' => '/DELETE\s+FROM\s+(\w+)/i',
        };

        if (!preg_match($tablePattern, $rawQuery, $m)) {
            return ['success' => false, 'error' => "No se pudo parsear la tabla del {$type}"];
        }

        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $m[1]);

        if (in_array($table, $this->readOnlyTables)) {
            return ['success' => false, 'error' => "La tabla '{$table}' es de solo lectura"];
        }

        // UPDATE y DELETE deben tener WHERE
        if (in_array($type, ['UPDATE', 'DELETE']) && !preg_match('/\bWHERE\b/i', $rawQuery)) {
            return ['success' => false, 'error' => "{$type} sin WHERE no está permitido"];
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists($table)) {
            return ['success' => false, 'error' => "Tabla '{$table}' no existe"];
        }

        // Guardar como pendiente
        $session->set(self::SESSION_PENDING_KEY, [
            'type'      => $type,
            'table'     => $table,
            'raw_query' => $rawQuery,
            'timestamp' => time(),
        ]);

        $confirmType = $type === 'DELETE' ? 'aritmética (doble)' : 'simple';

        return [
            'success'               => true,
            'requires_confirmation' => true,
            'confirmation_type'     => $type === 'DELETE' ? 'arithmetic' : 'simple',
            'message'               => "OPERACIÓN PENDIENTE DE CONFIRMACIÓN ({$confirmType}). Describe al usuario exactamente qué se va a hacer y pídele que use los botones de confirmación.",
            'operation'             => "{$type} en tabla '{$table}'",
            'query_preview'         => $rawQuery,
        ];
    }

    protected function executeConfirmedWrite(array $pending): array
    {
        if (time() - ($pending['timestamp'] ?? 0) > 300) {
            return ['success' => false, 'error' => 'Operación expirada (5 min). Solicítala de nuevo.'];
        }

        $rawQuery = $pending['raw_query'] ?? '';
        $type     = $pending['type'] ?? '';

        $v = $this->validateQuery($rawQuery, $type);
        if (!$v['valid']) return ['success' => false, 'error' => $v['error']];

        try {
            $db = \Config\Database::connect();
            $db->query($rawQuery);
            $affected = $db->affectedRows();

            if ($type === 'INSERT') {
                return ['success' => true, 'insert_id' => $db->insertID(), 'message' => 'Registro insertado con ID: ' . $db->insertID()];
            }
            return ['success' => true, 'affected_rows' => $affected, 'message' => "{$affected} fila(s) afectada(s)"];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // VALIDACIÓN SQL
    // =========================================================================

    protected function validateQuery(string $query, string $expectedType): array
    {
        $query = trim($query);
        if (empty($query)) return ['valid' => false, 'error' => 'Query vacío'];

        foreach ($this->forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return ['valid' => false, 'error' => 'Operación no permitida por política de seguridad'];
            }
        }

        if (!str_starts_with(strtoupper(ltrim($query)), $expectedType)) {
            return ['valid' => false, 'error' => "Se esperaba {$expectedType} pero se recibió otro tipo"];
        }

        $clean = preg_replace("/'[^']*'/", '', $query);
        $clean = preg_replace('/"[^"]*"/', '', $clean);

        if (substr_count($clean, ';') > 1) {
            return ['valid' => false, 'error' => 'No se permiten múltiples statements'];
        }
        if (preg_match('/\/\*|\*\/|--/', $clean)) {
            return ['valid' => false, 'error' => 'Comentarios SQL no permitidos'];
        }
        if (preg_match('/\b(SLEEP|BENCHMARK|CHAR\s*\(|CONCAT\s*\(.*SELECT|UNION\s+SELECT|0x[0-9a-fA-F]+)/i', $clean)) {
            return ['valid' => false, 'error' => 'Expresión SQL no permitida'];
        }

        return ['valid' => true];
    }

    protected function listAllTables(): array
    {
        $db = \Config\Database::connect();
        $result = [];
        foreach ($db->listTables() as $table) {
            try {
                $result[] = ['name' => $table, 'rows' => $db->table($table)->countAllResults()];
            } catch (\Throwable $e) {
                $result[] = ['name' => $table, 'rows' => '?'];
            }
        }
        return $result;
    }

    // =========================================================================
    // SYSTEM PROMPT Y TOOL DEFINITIONS
    // =========================================================================

    protected function buildSystemPrompt(): string
    {
        require_once APPPATH . 'Libraries/OttoArchetype.php';
        require_once APPPATH . 'Libraries/OttoTableMap.php';

        $db       = \Config\Database::connect();
        $session  = session();
        $userName = $session->get('nombre_usuario') ?? 'Consultor';
        $userRole = $session->get('role') ?? '';

        $now      = date('Y-m-d H:i:s');
        $year     = date('Y');
        $base     = \OttoArchetype::getSystemPrompt();
        $tableMap = \OttoTableMap::getPromptBlock();

        return $base . <<<PROMPT


---

## SESIÓN ACTUAL
- Usuario: {$userName} (rol: {$userRole})
- Fecha y hora actual: {$now}
- Año actual: {$year} — usa SIEMPRE este año como referencia cuando el usuario diga "este año", "de marzo", "del mes", etc.

## NIVELES DE CONFIRMACIÓN
- **SELECT**: se ejecuta directamente, sin confirmación
- **UPDATE / INSERT**: confirmación SIMPLE (botón Confirmar/Cancelar)
- **DELETE**: confirmación DOBLE con reto aritmético (ej: "¿Cuánto es 7+3?")
- **DROP / TRUNCATE / ALTER**: PROHIBIDOS permanentemente

## REGLAS DE SEGURIDAD
1. DROP, TRUNCATE, ALTER, CREATE TABLE, GRANT, REVOKE, RENAME están PROHIBIDOS
2. UPDATE y DELETE DEBEN tener cláusula WHERE (no afectar toda la tabla)
3. Antes de modificar/eliminar, primero consulta con SELECT el estado actual
4. Describe exactamente qué vas a hacer antes de ejecutar la tool
5. Limita los SELECT a 50 filas con LIMIT cuando no se especifique
6. Todas las operaciones quedan registradas en el log de auditoría

## FLUJO DE ESCRITURA
1. Consulta con SELECT el estado actual
2. Muestra al usuario qué existe
3. Describe qué vas a cambiar/insertar/eliminar
4. Ejecuta la tool — esto NO ejecuta directamente, genera solicitud de confirmación
5. El usuario confirma con botón (UPDATE/INSERT) o reto aritmético (DELETE)

## FORMATO DE RESPUESTA
- Responde en español
- Usa Markdown para tablas y código
- Formatea datos en tablas legibles

## CONTEXTO DEL SISTEMA
- Base de datos: propiedad_horizontal (MySQL)
- Framework: CodeIgniter 4
- La mayoría de tablas usan prefijo tbl_ pero hay excepciones (ver mapa abajo)
- Clientes = conjuntos residenciales / edificios / copropiedades

{$tableMap}
PROMPT;
    }

    protected function getToolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_tables',
                    'description' => 'Lista todas las tablas de la base de datos',
                    'parameters' => ['type' => 'object', 'properties' => new \stdClass(), 'required' => []],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'describe_table',
                    'description' => 'Estructura de una tabla (columnas, tipos, PK) y conteo de filas',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => ['table_name' => ['type' => 'string', 'description' => 'Nombre de la tabla (ej: tbl_clientes)']],
                        'required' => ['table_name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'execute_select',
                    'description' => 'Ejecuta un SELECT. Máx 50 filas. Usa LIMIT.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => ['query' => ['type' => 'string', 'description' => 'Query SELECT SQL']],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'execute_update',
                    'description' => 'PROPONE un UPDATE (requiere confirmación simple del usuario). DEBE incluir WHERE.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => ['query' => ['type' => 'string', 'description' => 'Query UPDATE con WHERE']],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'execute_insert',
                    'description' => 'PROPONE un INSERT (requiere confirmación simple del usuario).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => ['query' => ['type' => 'string', 'description' => 'Query INSERT INTO SQL']],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'execute_delete',
                    'description' => 'PROPONE un DELETE (requiere doble confirmación aritmética del usuario). DEBE incluir WHERE. Usar con precaución.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => ['query' => ['type' => 'string', 'description' => 'Query DELETE FROM con WHERE obligatorio']],
                        'required' => ['query'],
                    ],
                ],
            ],
        ];
    }
}
