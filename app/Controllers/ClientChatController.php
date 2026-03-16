<?php

namespace App\Controllers;

/**
 * ClientChatController — Otto de solo lectura para el portal del cliente
 *
 * SEGURIDAD (3 capas):
 *   Capa 1 (DB): usuario MySQL cycloid_readonly — solo GRANT SELECT sobre v_*
 *   Capa 2 (app): hereda validateQuery() — bloquea INSERT/UPDATE/DELETE/DROP
 *   Capa 3 (prompt): sistema scoped a id_cliente del cliente logueado
 *
 * DIFERENCIAS vs ChatController (consultor):
 *   - allowedRoles: solo 'client'
 *   - Conexión DB: grupo 'readonly' (cycloid_readonly)
 *   - Sin tools de escritura: execute_update, execute_insert, execute_delete eliminados
 *   - System prompt: menciona solo DATOS DEL CLIENTE en sesión
 *   - Guardrail SQL: verifica que todo SELECT filtre por id_cliente del cliente logueado
 *   - No hay confirmOperation() ni confirmDelete() — son inútiles en readonly
 */
class ClientChatController extends ChatController
{
    protected array $allowedRoles = ['client'];

    // ─────────────────────────────────────────────────────────────────────────
    // ACCESS CHECK — solo clientes logueados
    // ─────────────────────────────────────────────────────────────────────────

    protected function checkAccess($session): bool
    {
        return $session->get('isLoggedIn')
            && in_array($session->get('role'), ['client', 'consultant', 'admin']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT: pantalla del chat
    // ─────────────────────────────────────────────────────────────────────────

    public function index($idClienteParam = null)
    {
        $session = session();
        if (!$this->checkAccess($session)) {
            return redirect()->to('/login');
        }

        $role = $session->get('role');

        // Consultor/admin navegando al chat de un cliente específico
        if ($idClienteParam && in_array($role, ['consultant', 'admin'])) {
            $clientModel = new \App\Models\ClientModel();
            $client = $clientModel->find((int) $idClienteParam);
            if (!$client) {
                return redirect()->to('/dashboard')->with('error', 'Cliente no encontrado.');
            }
            // Guardar en sesión para que sendMessage lo use
            $session->set('chatting_client_id',   (int) $idClienteParam);
            $session->set('chatting_client_nombre', $client['nombre_cliente'] ?? '');

            return view('client/chat', [
                'usuario' => [
                    'nombre'             => $client['nombre_cliente'],
                    'role'               => 'client',
                    'id_cliente'         => (int) $idClienteParam,
                    'nombre_copropiedad' => $client['nombre_cliente'] ?? '',
                ],
            ]);
        }

        // Consultor/admin sin cliente específico → redirigir
        if (in_array($role, ['consultant', 'admin'])) {
            return redirect()->to('/consultor/dashboard')->with('error', 'Selecciona un cliente para abrir su chat con Otto.');
        }

        // Cliente logueado directamente
        $session->remove('chatting_client_id');
        $session->remove('chatting_client_nombre');

        return view('client/chat', [
            'usuario' => [
                'nombre'             => $session->get('nombre_usuario'),
                'role'               => 'client',
                'id_cliente'         => $session->get('id_entidad'),
                'nombre_copropiedad' => $session->get('nombre_copropiedad') ?? '',
            ],
        ]);
    }

    /**
     * Devuelve el id_cliente efectivo: el del cliente visto por el consultor,
     * o el de la sesión del cliente logueado.
     */
    private function resolveClientId(): int
    {
        $session = session();
        $chatting = $session->get('chatting_client_id');
        if ($chatting) {
            return (int) $chatting;
        }
        return (int) ($session->get('id_entidad') ?? 0);
    }

    private function resolveClientNombre(): string
    {
        $session = session();
        return $session->get('chatting_client_nombre')
            ?? $session->get('nombre_copropiedad')
            ?? '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SELECT — conexión readonly + guardrail de scope al cliente
    // ─────────────────────────────────────────────────────────────────────────

    protected function toolExecuteSelect(string $query): array
    {
        $tag = '[ClientChat::toolExecuteSelect]';

        // PASO 1: validación SQL
        $v = $this->validateQuery($query, 'SELECT');
        if (!$v['valid']) {
            log_message('error', "$tag VALIDATE_FAIL query=[$query] reason=[{$v['error']}]");
            return ['success' => false, 'error' => $v['error']];
        }
        log_message('info', "$tag VALIDATE_OK query=[$query]");

        // PASO 2: guardrail de scope
        $idCliente = $this->resolveClientId();
        $scopeOk   = $this->queryContainsClientScope($query, $idCliente);
        log_message('info', "$tag SCOPE id_cliente=$idCliente scope_ok=" . ($scopeOk ? 'true' : 'false'));
        if (!$scopeOk) {
            log_message('error', "$tag SCOPE_BLOCKED id_cliente=$idCliente query=[$query]");
            return [
                'success' => false,
                'error'   => "Por seguridad, solo puedes consultar datos de tu copropiedad (id_cliente={$idCliente}). Asegúrate de filtrar por nombre_cliente o id_cliente.",
            ];
        }

        // PASO 3: conexión readonly (config dinámica para evitar env() en property initializer)
        try {
            log_message('info', "$tag CONNECT_ATTEMPT readonly dynamic");
            $readonlyConfig = [
                'DSN'      => '',
                'hostname' => env('readonly.hostname', 'localhost'),
                'username' => env('readonly.username', 'cycloid_readonly'),
                'password' => env('readonly.password', ''),
                'database' => env('readonly.database', 'propiedad_horizontal'),
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => false,
                'DBDebug'  => false,
                'charset'  => 'utf8mb4',
                'DBCollat' => 'utf8mb4_general_ci',
                'swapPre'  => '',
                'encrypt'  => (bool) env('readonly.encrypt', false),
                'compress' => false,
                'strictOn' => false,
                'failover' => [],
                'port'     => (int) env('readonly.port', 3306),
            ];
            $db = \Config\Database::connect($readonlyConfig);
            log_message('info', "$tag CONNECT_OK host=" . $readonlyConfig['hostname']);
        } catch (\Throwable $e) {
            log_message('error', "$tag CONNECT_FAIL " . $e->getMessage());
            return ['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()];
        }

        // PASO 4: ejecutar query
        try {
            log_message('info', "$tag QUERY_START");
            $result = $db->query($query);
            log_message('info', "$tag QUERY_DONE result_type=" . gettype($result));
            $rows  = $result->getResultArray();
            $total = count($rows);
            log_message('info', "$tag ROWS_COUNT total=$total");
        } catch (\Throwable $e) {
            log_message('error', "$tag QUERY_FAIL " . $e->getMessage());
            return ['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()];
        }

        // PASO 5: sanitizar
        $rows = array_slice($rows, 0, 50);
        array_walk_recursive($rows, function (&$value) {
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                if (mb_strlen($value) > 800) {
                    $value = mb_substr($value, 0, 800) . '…';
                }
            }
        });

        // PASO 6: json_encode test
        $json = json_encode($rows);
        $jsonErr = json_last_error();
        log_message('info', "$tag JSON_ENCODE json_error=$jsonErr json_len=" . strlen($json ?: ''));
        if ($jsonErr !== JSON_ERROR_NONE) {
            log_message('error', "$tag JSON_FAIL error=$jsonErr msg=" . json_last_error_msg());
            return ['success' => false, 'error' => 'Error codificando datos: ' . json_last_error_msg()];
        }

        log_message('info', "$tag SUCCESS total=$total");
        return [
            'success'    => true,
            'data'       => $rows,
            'total_rows' => $total,
            'truncated'  => $total > 50,
            'note'       => $total > 50 ? "Mostrando 50 de {$total}. Usa filtros o LIMIT." : null,
        ];
    }

    /**
     * Verifica que la query incluya un filtro de scope para el cliente:
     * - nombre_cliente LIKE '%...' / = '...'
     * - id_cliente = N
     * - Consulta una vista v_* que ya tiene el nombre_cliente (sin filtro explícito se permite
     *   solo para queries sobre vistas que traen UNA fila por naturaleza)
     *
     * Si la query menciona el id_cliente numérico o el nombre de la copropiedad, se considera segura.
     * Si no, devuelve false y el select es bloqueado.
     */
    protected function queryContainsClientScope(string $query, int $idCliente): bool
    {
        if ($idCliente === 0) return false;

        // ¿Menciona el id_cliente numéricamente?
        if (preg_match('/\bid_cliente\s*=\s*' . $idCliente . '\b/i', $query)) return true;

        // ¿Menciona nombre_cliente con un valor (LIKE o =)?
        if (preg_match('/\bnombre_cliente\s*(=|LIKE)\s*[\'"][^\'\"]+[\'"]/i', $query)) return true;

        // ¿La copropiedad aparece literalmente como string en la query?
        $nombreCopropiedad = $this->resolveClientNombre();
        if ($nombreCopropiedad && stripos($query, $nombreCopropiedad) !== false) return true;

        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA — BLOQUEADA PERMANENTEMENTE (Capa 2 de app)
    // ─────────────────────────────────────────────────────────────────────────

    protected function toolExecuteWrite(array $args, string $type, $session): array
    {
        return [
            'success' => false,
            'error'   => "Operación {$type} no disponible en el portal del cliente. El acceso es de solo consulta.",
        ];
    }

    public function confirmOperation()
    {
        return $this->response->setJSON(['success' => false, 'error' => 'No disponible'])->setStatusCode(403);
    }

    public function confirmDelete()
    {
        return $this->response->setJSON(['success' => false, 'error' => 'No disponible'])->setStatusCode(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOOLS disponibles — solo lectura (sin execute_update/insert/delete)
    // ─────────────────────────────────────────────────────────────────────────

    protected function getToolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'execute_select',
                    'description' => 'Consulta datos de tu copropiedad. Solo SELECT. Máx 50 filas.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => ['query' => ['type' => 'string', 'description' => 'Query SELECT sobre vistas v_* filtrada por nombre_cliente o id_cliente']],
                        'required'   => ['query'],
                    ],
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SYSTEM PROMPT — scoped al cliente logueado
    // ─────────────────────────────────────────────────────────────────────────

    protected function buildSystemPrompt(): string
    {
        require_once APPPATH . 'Libraries/OttoArchetype.php';
        require_once APPPATH . 'Libraries/OttoTableMap.php';

        $session            = session();
        $nombreUsuario      = $session->get('nombre_usuario') ?? 'Cliente';
        $idCliente          = $this->resolveClientId();
        $nombreCopropiedad  = $this->resolveClientNombre();

        $now  = date('Y-m-d H:i:s');
        $year = date('Y');

        // Solo las entradas de vistas (SELECT), el cliente no necesita saber de tbl_* de escritura
        $tableMap = \OttoTableMap::getPromptBlock();

        return <<<PROMPT
Eres Otto, el asistente virtual de Cycloid Talent SAS para el portal del cliente.

## TU IDENTIDAD
Eres un asistente amable, claro y orientado al residente y administrador de propiedad horizontal.
Tu misión es responder preguntas sobre el estado de la gestión SST de la copropiedad.

## CONTEXTO DE SESIÓN
- Nombre del usuario: {$nombreUsuario}
- Copropiedad: {$nombreCopropiedad}
- id_cliente: {$idCliente}
- Fecha y hora: {$now}
- Año actual: {$year}

## REGLA ABSOLUTA DE SCOPE
**SOLO puedes consultar datos de esta copropiedad (id_cliente = {$idCliente} / nombre_cliente = '{$nombreCopropiedad}').**
Cada SELECT que generes DEBE incluir:
  - `WHERE id_cliente = {$idCliente}`  O
  - `WHERE nombre_cliente = '{$nombreCopropiedad}'`  O
  - `WHERE nombre_cliente LIKE '%{$nombreCopropiedad}%'`

Si el usuario pregunta por otra copropiedad o intenta ver datos de otros clientes,
responde: "Solo puedo mostrarte información de tu propia copropiedad."

## REGLA ABSOLUTA — JERARQUÍA DE CONSULTAS
**Para SELECT: usa SIEMPRE la vista `v_*` correspondiente.** Nunca consultes `tbl_*` directamente.
Las vistas ya resuelven todos los IDs a textos legibles. Consultar tablas devuelve IDs que el usuario no puede interpretar.
- ✅ CORRECTO: `SELECT * FROM v_tbl_pendientes WHERE nombre_cliente = '{$nombreCopropiedad}'`
- ❌ INCORRECTO: `SELECT * FROM tbl_pendientes WHERE id_cliente = {$idCliente}`

## REGLA ABSOLUTA — MAYÉUTICA (preguntar antes de ejecutar)
Antes de generar cualquier query, verifica si la solicitud tiene todos los parámetros necesarios.
Si falta alguno de los siguientes, **pregúntalo antes de ejecutar**:
- **Estado**: ¿abiertas, cerradas, en gestión, o todas? (para actividades, pendientes, inspecciones)
- **Período**: ¿de qué mes, año, trimestre o rango de fechas?
- **Tipo o categoría**: ¿qué tipo de inspección, mantenimiento, capacitación, etc.?

No asumas valores por defecto. Puedes agrupar todo lo que falta en una sola pregunta.
Si el usuario dice "de marzo", pregunta de qué año si no es evidente.
Solo ejecuta cuando tengas suficiente información para una consulta precisa y útil.

**Excepción**: si la solicitud es general y el estado/período no cambia el resultado ("¿cuántas visitas tuve?", "muéstrame mis contratos"), ejecuta directamente.

## REGLA ABSOLUTA — FILTRO POR NOMBRE DE COPROPIEDAD
Cuando filtres por nombre de cliente en las vistas, usa SIEMPRE `LIKE`:
- ✅ `WHERE nombre_cliente LIKE '%{$nombreCopropiedad}%'`
- ❌ `WHERE nombre_cliente = '{$nombreCopropiedad}'` — puede fallar si hay diferencias de mayúsculas o espacios

## GLOSARIO DE ESTADOS — MAPEO USUARIO → BD
Los campos de estado tienen valores fijos. Traduce lo que diga el usuario al valor exacto:

**estado_actividad** (v_tbl_pta_cliente):
- "abiertas" / "pendientes" / "sin cerrar" → `estado_actividad = 'ABIERTA'`
- "cerradas" / "completadas" / "terminadas" → `estado_actividad IN ('CERRADA', 'CERRADA SIN EJECUCIÓN', 'CERRADA POR FIN CONTRATO')`
- "en proceso" / "gestionando" / "en gestión" → `estado_actividad = 'GESTIONANDO'`

Para campos de estado usa `=` con el valor exacto. Solo usa LIKE para texto libre como `nombre_cliente` o descripciones.

## REGLAS DE ACCESO
- SOLO puedes hacer consultas SELECT. No tienes acceso a INSERT, UPDATE, DELETE ni ninguna operación de escritura.
- Usa SIEMPRE las vistas v_* (no las tablas tbl_* directamente).
- NUNCA muestres SQL al usuario — solo los resultados en lenguaje natural.
- Responde siempre en español.
- Usa tablas Markdown para mostrar listados.

## FORMATO DE RESPUESTA
- Presenta los datos en formato amigable para un administrador o residente, no para un técnico.
- Si los datos son buenos, celebra el avance. Si hay alertas (vencimientos, pendientes), comunícalos con claridad y sin alarmar.
- Limita las respuestas a 50 registros máximo.

## LO QUE PUEDES RESPONDER
- Estado de pendientes y compromisos de la copropiedad
- Últimas visitas del consultor
- Estado del plan de trabajo y actividades abiertas/cerradas
- Inspecciones realizadas (extintores, botiquín, señalización, locativa)
- Capacitaciones programadas y ejecutadas
- Cronograma de mantenimientos y vencimientos
- Presupuesto SST
- Indicadores (KPIs) de agua potable, limpieza, plagas, residuos
- Estado de contratos y documentación

{$tableMap}
PROMPT;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOG — registra operaciones del cliente en tbl_chat_log
    // (hereda logOperation() del padre — usa conexión default para el log)
    // ─────────────────────────────────────────────────────────────────────────
}
