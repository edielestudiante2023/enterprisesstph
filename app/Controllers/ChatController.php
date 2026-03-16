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

    /**
     * Recibe el historial de conversación al cierre por inactividad
     * y envía el resumen por email al consultor + copia a otto.chat@cycloidtalent.com
     */
    public function endSession()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        $input   = $this->request->getJSON(true) ?? $this->request->getPost();
        $history = $input['history'] ?? [];

        if (empty($history)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Sin conversación que enviar']);
        }

        $userName  = $session->get('nombre_usuario') ?? 'Consultor';
        $userEmail = $session->get('email_usuario')  ?? '';
        $now       = date('d/m/Y H:i');

        $transcriptHtml = '';
        foreach ($history as $msg) {
            $role    = ($msg['role'] ?? '') === 'user' ? $userName : 'Otto';
            $content = nl2br(htmlspecialchars($msg['content'] ?? ''));
            $bg      = ($msg['role'] ?? '') === 'user' ? '#f0f4ff' : '#f9f9f9';
            $align   = ($msg['role'] ?? '') === 'user' ? 'right' : 'left';
            $transcriptHtml .= "
            <tr>
                <td style='padding:8px 12px; background:{$bg}; text-align:{$align}; border-bottom:1px solid #eee;'>
                    <strong style='color:#1c2437;'>{$role}:</strong><br>
                    <span style='color:#333; font-size:14px;'>{$content}</span>
                </td>
            </tr>";
        }

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:700px;margin:0 auto;padding:20px;'>
            <div style='text-align:center;margin-bottom:24px;'>
                <h2 style='color:#1c2437;margin:0;'>Resumen de sesión con Otto</h2>
                <p style='color:#bd9751;font-size:13px;margin:4px 0 0;'>Propiedad Horizontal · {$now}</p>
            </div>
            <p style='color:#333;'>El consultor <strong>{$userName}</strong> tuvo la siguiente conversación con Otto:</p>
            <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #ddd;border-radius:8px;overflow:hidden;'>
                {$transcriptHtml}
            </table>
            <hr style='border:none;border-top:1px solid #e0e0e0;margin:24px 0;'>
            <p style='color:#999;font-size:12px;text-align:center;'>
                Cycloid Talent SAS · <a href='https://cycloidtalent.com' style='color:#bd9751;'>www.cycloidtalent.com</a>
            </p>
        </div>";

        try {
            $mail = new \SendGrid\Mail\Mail();
            $mail->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'Otto · Cycloid Talent');
            $mail->setSubject("Resumen sesión Otto · {$userName} · {$now}");
            if ($userEmail) {
                $mail->addTo($userEmail, $userName);
            }
            $mail->addCc('otto.chat@cycloidtalent.com', 'Otto Chat Log');
            $mail->addContent('text/html', $html);

            $sg       = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sg->send($mail);
            $sent     = $response->statusCode() >= 200 && $response->statusCode() < 300;

            $this->logOperation('session_end_email', "email={$userEmail} status=" . $response->statusCode(), $session);

            return $this->response->setJSON(['success' => $sent]);
        } catch (\Throwable $e) {
            log_message('error', 'ChatController::endSession email error: ' . $e->getMessage());
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
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // DASHBOARD DE LOGS OTTO
    // =========================================================================

    public function ottoDashboard()
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        // KPIs globales
        $kpis = $db->query("
            SELECT
                COUNT(DISTINCT CASE WHEN rol='client' THEN id_usuario END)            AS clientes_activos,
                COUNT(DISTINCT CASE WHEN rol IN ('consultant','admin') THEN id_usuario END) AS consultores_activos,
                SUM(tipo_operacion='user_message')                                    AS total_mensajes,
                SUM(tipo_operacion='tool_select')                                     AS total_consultas,
                SUM(tipo_operacion IN ('tool_update','tool_insert','tool_delete','write_executed','delete_executed')) AS total_escrituras,
                COUNT(DISTINCT DATE(created_at))                                      AS dias_con_actividad
            FROM tbl_chat_log
        ")->getRowArray();

        // Uso por CLIENTE
        // id_usuario en tbl_chat_log = id en tbl_usuarios → id_entidad = id_cliente en tbl_clientes
        $clientes = $db->query("
            SELECT
                c.id_cliente,
                COALESCE(c.nombre_cliente, CONCAT('Cliente #', u.id_entidad)) AS nombre,
                SUM(l.tipo_operacion='user_message')            AS mensajes,
                SUM(l.tipo_operacion='tool_select')             AS consultas,
                COUNT(DISTINCT DATE(l.created_at))              AS sesiones,
                MAX(l.created_at)                               AS ultima_actividad
            FROM tbl_chat_log l
            JOIN tbl_usuarios u ON u.id_usuario = l.id_usuario
            LEFT JOIN tbl_clientes c ON c.id_cliente = u.id_entidad
            WHERE l.rol = 'client'
            GROUP BY c.id_cliente, c.nombre_cliente
            ORDER BY ultima_actividad DESC
        ")->getResultArray();

        // Uso por CONSULTOR
        $consultores = $db->query("
            SELECT
                l.id_usuario,
                COALESCE(u.nombre_completo, CONCAT('Usuario #', l.id_usuario)) AS nombre,
                u.email,
                SUM(l.tipo_operacion='user_message')            AS mensajes,
                SUM(l.tipo_operacion='tool_select')             AS consultas,
                SUM(l.tipo_operacion IN ('tool_update','tool_insert','tool_delete','write_executed','delete_executed')) AS escrituras,
                COUNT(DISTINCT DATE(l.created_at))              AS dias_activo,
                MAX(l.created_at)                               AS ultima_actividad
            FROM tbl_chat_log l
            LEFT JOIN tbl_usuarios u ON u.id_usuario = l.id_usuario
            WHERE l.rol IN ('consultant','admin')
            GROUP BY l.id_usuario, u.nombre_completo, u.email
            ORDER BY ultima_actividad DESC
        ")->getResultArray();

        // Actividad reciente (últimas 30 interacciones de usuario)
        $recientes = $db->query("
            SELECT
                l.rol, l.tipo_operacion, l.detalle, l.created_at,
                CASE WHEN l.rol='client'
                     THEN COALESCE(c.nombre_cliente, CONCAT('Cliente #', u.id_entidad))
                     ELSE COALESCE(u.nombre_completo, CONCAT('Usuario #', l.id_usuario))
                END AS nombre
            FROM tbl_chat_log l
            JOIN tbl_usuarios u ON u.id_usuario = l.id_usuario
            LEFT JOIN tbl_clientes c ON c.id_cliente = u.id_entidad AND l.rol = 'client'
            WHERE l.tipo_operacion IN ('user_message','tool_select','tool_update','tool_insert','tool_delete')
            ORDER BY l.created_at DESC
            LIMIT 30
        ")->getResultArray();

        return view('consultant/otto_dashboard', compact('kpis', 'clientes', 'consultores', 'recientes'));
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

            $rows = array_slice($rows, 0, 50);

            // Sanitizar campos para garantizar JSON válido
            array_walk_recursive($rows, function (&$value) {
                if (is_string($value)) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    if (mb_strlen($value) > 800) {
                        $value = mb_substr($value, 0, 800) . '…';
                    }
                }
            });

            return [
                'success'    => true,
                'data'       => $rows,
                'total_rows' => $total,
                'truncated'  => $total > 50,
                'note'       => $total > 50 ? "Mostrando 50 de {$total}. Usa filtros o LIMIT para acotar." : null,
            ];
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

## REGLA ABSOLUTA — JERARQUÍA DE CONSULTAS
**Para SELECT: usa SIEMPRE la vista `v_*` correspondiente si existe.**
Las vistas ya resuelven todos los IDs a nombres legibles (nombre_cliente, nombre_actividad, tipo_mantenimiento, etc.).
Consultar `tbl_*` directamente en un SELECT devuelve IDs crudos que el usuario no puede entender.
- ✅ CORRECTO: `SELECT * FROM v_tbl_pendientes WHERE ...`
- ❌ INCORRECTO: `SELECT * FROM tbl_pendientes WHERE ...`

**Para INSERT / UPDATE / DELETE: usa SIEMPRE la tabla `tbl_*` directamente** (las vistas son de solo lectura).

## REGLA ABSOLUTA — MAYÉUTICA (preguntar antes de ejecutar)
Antes de generar cualquier query, verifica si la solicitud tiene todos los parámetros necesarios.
Si falta alguno de los siguientes, **pregúntalo al usuario antes de ejecutar nada**:
- **Cliente / copropiedad**: ¿para qué cliente o conjunto residencial?
- **Estado**: ¿abiertas, cerradas, en gestión, o todas? (para actividades, pendientes, inspecciones)
- **Período**: ¿de qué mes, año, trimestre o rango de fechas?
- **Tipo o categoría**: ¿qué tipo de inspección, mantenimiento, capacitación, etc.?

No asumas valores. No uses el "primer cliente" ni el "mes actual" ni "todas" por defecto.
Si el usuario dice "de marzo", pregunta de qué año si no es evidente.
Puedes hacer **una sola pregunta agrupando todo lo que falta** en lugar de preguntar uno por uno.
Solo ejecuta cuando tengas suficiente información para hacer una consulta precisa y útil.

**Excepción**: si la solicitud es explícitamente general ("muéstrame todos los clientes", "lista las tablas disponibles"), no preguntes — ejecuta directamente.

## REGLA ABSOLUTA — BÚSQUEDA DE CLIENTES POR NOMBRE
El usuario NUNCA conoce el nombre completo del cliente en la base de datos.
Cuando el usuario mencione un nombre de cliente (ej: "jacaranda", "torres", "el prado"):
- **SIEMPRE** usa `LIKE '%jacaranda%'` (con wildcards en ambos lados)
- **NUNCA** uses `= 'jacaranda'` — casi siempre devuelve 0 resultados
- Si el LIKE devuelve más de un cliente, muéstralos y pregunta cuál es el correcto antes de continuar

## GLOSARIO DE ESTADOS — MAPEO USUARIO → BD
Los campos de estado tienen valores fijos en la BD. Traduce lo que diga el usuario al valor exacto:

**estado_actividad** (v_tbl_pta_cliente):
- "abiertas" / "pendientes" / "sin cerrar" → `estado_actividad = 'ABIERTA'`
- "cerradas" / "completadas" / "terminadas" → `estado_actividad IN ('CERRADA', 'CERRADA SIN EJECUCIÓN', 'CERRADA POR FIN CONTRATO')`
- "en proceso" / "gestionando" / "en gestión" → `estado_actividad = 'GESTIONANDO'`

**En general**: para campos de estado/tipo usa `=` con el valor exacto del ENUM, NO uses LIKE.
Solo usa LIKE para campos de texto libre como `nombre_cliente`, `actividad_plandetrabajo`, `detalle_mantenimiento`.

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
6. **Nunca uses `SELECT *`** — especifica siempre las columnas que necesitas. SELECT * trae campos TEXT muy largos innecesarios que degradan el rendimiento.
7. Todas las operaciones quedan registradas en el log de auditoría

## FLUJO DE ESCRITURA
1. Consulta con SELECT el estado actual (usando la vista v_* correspondiente)
2. Muestra al usuario qué existe
3. Describe qué vas a cambiar/insertar/eliminar
4. Ejecuta la tool — esto NO ejecuta directamente, genera solicitud de confirmación
5. El usuario confirma con botón (UPDATE/INSERT) o reto aritmético (DELETE)

## FORMATO DE RESPUESTA
- Responde siempre en español
- Usa tablas Markdown para mostrar listados de datos
- **NUNCA muestres SQL al usuario** — el usuario es un consultor de SST, no un programador
- Cuando vayas a hacer un UPDATE/INSERT/DELETE, describe la operación en lenguaje natural, por ejemplo: "Voy a cambiar la fecha de propuesta de las actividades 7946, 7947 y 7948 al 1 de mayo de 2026"
- Los bloques de código SQL son para uso interno tuyo, nunca los incluyas en la respuesta visible

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
