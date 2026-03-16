# Otto — Chat IA · Documentación técnica

> Cycloid Enterprises SST · CodeIgniter 4 · GPT-4o (OpenAI function calling)

---

## Índice

1. [Arquitectura general](#1-arquitectura-general)
2. [Contextos: Consultor vs Cliente](#2-contextos-consultor-vs-cliente)
3. [Flujo de una conversación](#3-flujo-de-una-conversación)
4. [Herramientas disponibles (tools)](#4-herramientas-disponibles-tools)
5. [Reglas del system prompt](#5-reglas-del-system-prompt)
6. [Seguridad — capas de protección](#6-seguridad--capas-de-protección)
7. [Confirmación de operaciones de escritura](#7-confirmación-de-operaciones-de-escritura)
8. [Validación SQL](#8-validación-sql)
9. [Log de auditoría](#9-log-de-auditoría)
10. [Sesión e inactividad](#10-sesión-e-inactividad)
11. [Rutas](#11-rutas)
12. [Variables de entorno](#12-variables-de-entorno)
13. [Glosario de estados](#13-glosario-de-estados)
14. [Errores frecuentes y soluciones](#14-errores-frecuentes-y-soluciones)

---

## 1. Arquitectura general

```
Usuario (browser)
    │  POST /chat/send  {message, history[]}
    ▼
ChatController::sendMessage()
    │
    ├─ checkAccess()          — verifica rol en sesión
    ├─ processWithToolCalling()
    │       │
    │       ├─ buildSystemPrompt()   — prompt contextualizado
    │       ├─ callOpenAI()          — GPT-4o con function calling
    │       │
    │       └─ loop (máx 8 iteraciones):
    │               ├─ finish_reason === 'stop'  →  devuelve respuesta final
    │               └─ tool_calls               →  executeToolCall()
    │                       ├─ toolListTables()
    │                       ├─ toolDescribeTable()
    │                       ├─ toolExecuteSelect()   — conexión default o readonly
    │                       └─ toolExecuteWrite()    — guarda pendiente en sesión
    │
    └─ logOperation()         — registra en tbl_chat_log
```

**Motor IA:** OpenAI GPT-4o · `temperature: 0.3` · `max_tokens: 4000` · timeout: 120s
**Historial:** se envían los últimos 20 mensajes del array `conversationHistory` del frontend.

---

## 2. Contextos: Consultor vs Cliente

| Aspecto | `ChatController` (consultor) | `ClientChatController` (cliente) |
|---|---|---|
| **Ruta vista** | `GET /consultant/chat` | `GET /client-chat` |
| **Roles permitidos** | `consultant`, `admin` | `client` |
| **Conexión DB** | `default` (cycloid_userdb) | `readonly` (cycloid_readonly) |
| **Tools disponibles** | list_tables, describe_table, execute_select, execute_update, execute_insert, execute_delete | solo execute_select |
| **Confirmación escritura** | Sí — botones en la UI | No disponible (bloqueado 403) |
| **Panel schema (DB)** | Sí | No |
| **Scope de datos** | Todos los clientes | Solo `id_cliente` del cliente logueado |
| **Guardrail scope** | No aplica | `queryContainsClientScope()` — bloquea SELECTs sin filtro por cliente |
| **Tono del prompt** | Técnico-profesional para consultor SST | Amigable para administrador/residente |

### Herencia

`ClientChatController` **extiende** `ChatController`. Hereda toda la infraestructura
(OpenAI loop, `validateQuery`, `logOperation`, `callOpenAI`) y sobreescribe solo lo necesario.

---

## 3. Flujo de una conversación

### Mensaje normal (SELECT)

```
1. Usuario escribe mensaje
2. Frontend hace POST /chat/send { message, history }
3. ChatController valida acceso (rol en sesión)
4. processWithToolCalling():
   a. Construye array de mensajes: [system, ...history, user]
   b. Llama OpenAI con tools definidas
   c. OpenAI responde con tool_call → execute_select
   d. toolExecuteSelect() ejecuta la query → devuelve rows
   e. Se agrega tool result al array de mensajes
   f. Se vuelve a llamar OpenAI → devuelve respuesta en lenguaje natural
5. Frontend renderiza respuesta con marked.js (Markdown → HTML)
```

### Mensaje con escritura (UPDATE/INSERT/DELETE)

```
1-4. Igual al flujo anterior
   c. OpenAI responde con tool_call → execute_update/insert/delete
   d. toolExecuteWrite():
      - Valida query
      - Verifica que la tabla no sea readOnly
      - UPDATE/DELETE: verifica que tenga WHERE
      - Guarda operación en session['chat_pending_operation']
      - Devuelve { requires_confirmation: true }
   e. OpenAI describe al usuario qué se va a hacer y pide confirmación
5. Frontend muestra botones Confirmar/Cancelar (UPDATE/INSERT)
   o botón Eliminar con reto aritmético (DELETE)
6. Usuario confirma → POST /chat/confirm o /chat/confirm-delete
7. executeConfirmedWrite() recupera pendiente de sesión y ejecuta
```

---

## 4. Herramientas disponibles (tools)

### `list_tables`
Lista todas las tablas de la BD. Sin parámetros.
Solo disponible para consultores.

### `describe_table`
Devuelve estructura (columnas, tipos, PK) y conteo de filas de una tabla.
Parámetro: `table_name` (string).

### `execute_select`
Ejecuta un SELECT. Máximo 50 filas devueltas.
Parámetro: `query` (string SQL).
- Pasa por `validateQuery()` antes de ejecutar.
- Consultor: usa conexión `default`.
- Cliente: usa conexión `readonly` + guardrail de scope.

### `execute_update`
Propone un UPDATE. Requiere confirmación simple del usuario.
Parámetro: `query` (string SQL con WHERE obligatorio).

### `execute_insert`
Propone un INSERT. Requiere confirmación simple del usuario.
Parámetro: `query` (string SQL).

### `execute_delete`
Propone un DELETE. Requiere confirmación **doble aritmética**.
Parámetro: `query` (string SQL con WHERE obligatorio).

> Las herramientas de escritura (`execute_update`, `execute_insert`, `execute_delete`)
> **no existen** en `ClientChatController::getToolDefinitions()`.

---

## 5. Reglas del system prompt

El system prompt se construye dinámicamente en `buildSystemPrompt()` en cada request.
Incluye: arquetipo Otto (`OttoArchetype`), sesión actual, reglas, y mapa de tablas/vistas (`OttoTableMap`).

### Jerarquía de consultas (REGLA ABSOLUTA)

```
SELECT  →  usar SIEMPRE v_* (vistas)
                Las vistas resuelven IDs a textos legibles.
                NUNCA consultar tbl_* directamente en un SELECT.

INSERT / UPDATE / DELETE  →  usar SIEMPRE tbl_* (tablas directas)
                              Las vistas son de solo lectura.
```

**Ejemplo:**
```sql
-- ✅ CORRECTO
SELECT * FROM v_tbl_pendientes WHERE nombre_cliente LIKE '%jacaranda%'

-- ❌ INCORRECTO (devuelve IDs crudos)
SELECT * FROM tbl_pendientes WHERE id_cliente = 35
```

### Mayéutica (preguntar antes de ejecutar)

Antes de generar cualquier query, Otto verifica si faltan parámetros clave.
Si falta alguno, **pregunta primero** (en una sola pregunta agrupada):

| Parámetro | Cuándo preguntar |
|---|---|
| **Cliente / copropiedad** | Cuando el mensaje no identifica un cliente específico |
| **Estado** | ¿Abiertas, cerradas, en gestión, o todas? |
| **Período** | ¿Mes, año, trimestre o rango de fechas? |
| **Tipo / categoría** | ¿Qué tipo de inspección, mantenimiento, capacitación? |

**Excepción:** solicitudes explícitamente generales ("muéstrame todos los clientes",
"lista las tablas") se ejecutan directo sin preguntar.

### Búsqueda de clientes por nombre

El usuario nunca conoce el nombre completo en la BD.

```sql
-- ✅ CORRECTO
WHERE nombre_cliente LIKE '%jacaranda%'

-- ❌ INCORRECTO
WHERE nombre_cliente = 'jacaranda'
```

Si el LIKE devuelve más de un cliente, Otto los lista y pregunta cuál es el correcto.

---

## 6. Seguridad — capas de protección

### Consultor (2 capas)

**Capa 1 — Validación SQL (`validateQuery`)**
Bloqueado permanentemente:
- `DROP`, `TRUNCATE`, `ALTER`, `CREATE TABLE`, `CREATE DATABASE`
- `GRANT`, `REVOKE`, `RENAME`
- `INTO OUTFILE`, `LOAD_FILE`, `INTO DUMPFILE`
- Múltiples statements (`;` doble)
- Comentarios SQL (`--`, `/* */`)
- Patrones de inyección: `SLEEP`, `BENCHMARK`, `UNION SELECT`, `0x...`

**Capa 2 — Tablas de solo lectura**
`tbl_usuarios`, `tbl_sesiones_usuario`, `tbl_roles` — nunca modificables aunque la query pase la validación.

### Cliente (3 capas)

**Capa 1 — DB (MySQL)**
Usuario `cycloid_readonly`: `GRANT SELECT` únicamente sobre 60 vistas `v_*` y 6 tablas maestras.
Físicamente incapaz de INSERT / UPDATE / DELETE / DROP a nivel de base de datos.

**Capa 2 — App (PHP)**
`toolExecuteWrite()` sobreescrito: devuelve error 403.
`confirmOperation()` y `confirmDelete()` devuelven 403.
`getToolDefinitions()` no expone herramientas de escritura.

**Capa 3 — Prompt + Guardrail**
- System prompt ordena filtrar siempre por `id_cliente` del cliente logueado.
- `queryContainsClientScope()` valida en PHP que la query contenga:
  - `id_cliente = N` (valor numérico del cliente), o
  - `nombre_cliente = '...'`, o
  - `nombre_cliente LIKE '...'`, o
  - el `nombre_copropiedad` de sesión como substring en la query.

---

## 7. Confirmación de operaciones de escritura

### Confirmación simple (UPDATE / INSERT)

```
Otto genera query → toolExecuteWrite() guarda en session['chat_pending_operation']
  → responde { requires_confirmation: true, confirmation_type: 'simple' }
  → Frontend muestra botones [Confirmar] [Cancelar]
  → Usuario confirma → POST /chat/confirm { confirm: true }
  → confirmOperation() → executeConfirmedWrite()
```

La operación pendiente **expira en 5 minutos**.

### Confirmación doble aritmética (DELETE)

```
Otto genera query → toolExecuteWrite() guarda pendiente
  → responde { confirmation_type: 'arithmetic' }
  → Frontend muestra botón [Eliminar (requiere verificación)]
  → Usuario hace clic → POST /chat/confirm-delete { step: 'challenge' }
  → Se genera reto: "¿Cuánto es A + B?" (números aleatorios, respuesta en sesión)
  → Usuario ingresa respuesta → POST /chat/confirm-delete { step: 'verify', answer: N }
  → Si respuesta correcta → executeConfirmedWrite()
  → Usuario puede cancelar → POST /chat/confirm-delete { step: 'cancel' }
```

---

## 8. Validación SQL

`validateQuery(string $query, string $expectedType): array`

Validaciones en orden:
1. Query no vacío
2. Sin patrones prohibidos (forbiddenPatterns)
3. Tipo correcto: la query debe empezar con el tipo esperado (SELECT, UPDATE, etc.)
4. Sin múltiples statements (`;` doble, excluyendo strings)
5. Sin comentarios SQL
6. Sin patrones de inyección (SLEEP, BENCHMARK, UNION SELECT, hex literals)

Retorna `['valid' => true]` o `['valid' => false, 'error' => '...']`.

---

## 9. Log de auditoría

Todas las operaciones quedan registradas en `tbl_chat_log`:

```sql
tbl_chat_log (
    id              INT AUTO_INCREMENT PK,
    id_usuario      INT,
    rol             VARCHAR(20),     -- 'consultant', 'admin', 'client'
    tipo_operacion  VARCHAR(50),     -- 'tool_select', 'tool_update', 'tool_delete', etc.
    detalle         TEXT,            -- query SQL ejecutada (máx 5000 chars)
    ip_address      VARCHAR(45),
    created_at      DATETIME
)
```

Tipos de operación registrados:
- `tool_list_tables`
- `tool_describe_table`
- `tool_select`
- `tool_update` / `tool_insert` / `tool_delete`
- `confirm_operation` / `confirm_delete`
- `end_session`

La tabla se crea automáticamente si no existe (primera ejecución).

---

## 10. Sesión e inactividad

### Variables de sesión relevantes

| Variable | Descripción |
|---|---|
| `id_usuario` | ID en tbl_usuarios |
| `id_entidad` | id_cliente (clientes) o id_consultor (consultores) |
| `role` | `client`, `consultant`, `admin` |
| `nombre_usuario` | Nombre completo |
| `nombre_copropiedad` | Nombre del cliente en tbl_clientes (solo rol `client`) |
| `chat_pending_operation` | Operación de escritura pendiente de confirmación |

### Inactividad (frontend)

- Después de **10 minutos sin actividad**, el frontend envía automáticamente el historial de la conversación al endpoint `end-session`.
- También se envía al cerrar la pestaña / navegar fuera (`visibilitychange` + `beforeunload`).
- Usa `navigator.sendBeacon` para garantizar el envío en cierre de pestaña.
- El endpoint `end-session` dispara un email de resumen al usuario.

---

## 11. Rutas

```php
// Consultor
GET  /consultant/chat            → ChatController::index
POST /chat/send                  → ChatController::sendMessage
POST /chat/confirm               → ChatController::confirmOperation
POST /chat/confirm-delete        → ChatController::confirmDelete
GET  /chat/schema                → ChatController::getSchema
POST /chat/end-session           → ChatController::endSession

// Cliente
GET  /client-chat                → ClientChatController::index
POST /client-chat/send           → ClientChatController::sendMessage
POST /client-chat/end-session    → ClientChatController::endSession
```

---

## 12. Variables de entorno

```ini
# .env
OPENAI_API_KEY = sk-...
OPENAI_MODEL   = gpt-4o          # default si no se define

# Conexión readonly (portal cliente)
readonly.hostname = db-mysql-cycloid-...
readonly.database = propiedad_horizontal
readonly.username = cycloid_readonly
readonly.password = CycloidPortal2026!
readonly.port     = 25060
readonly.encrypt  = TRUE
```

---

## 13. Glosario de estados

Mapeo de lenguaje natural del usuario → valores exactos ENUM en la BD.

### `estado_actividad` — `v_tbl_pta_cliente`

| Usuario dice | Query SQL |
|---|---|
| "abiertas" / "pendientes" / "sin cerrar" | `estado_actividad = 'ABIERTA'` |
| "cerradas" / "completadas" / "terminadas" | `estado_actividad IN ('CERRADA', 'CERRADA SIN EJECUCIÓN', 'CERRADA POR FIN CONTRATO')` |
| "en proceso" / "gestionando" / "en gestión" | `estado_actividad = 'GESTIONANDO'` |
| "todas" | sin filtro de estado |

> Para campos de estado/tipo: usar `=` con el valor exacto.
> Para texto libre (`nombre_cliente`, `actividad_plandetrabajo`): usar `LIKE '%...%'`.

---

## 14. Errores frecuentes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "No se encontraron resultados" para un cliente que sí existe | Otto usó `= 'nombre'` en vez de `LIKE '%nombre%'` | Directiva en prompt: siempre LIKE para nombre_cliente |
| Resultado con IDs crudos en lugar de textos | Otto consultó `tbl_*` en vez de `v_*` | Directiva de jerarquía en prompt: SELECT → v_* obligatorio |
| Otto ejecuta sin preguntar parámetros | Faltaba la regla de mayéutica | Directiva mayéutica: preguntar cliente, estado, período antes de ejecutar |
| "cycloid_readonly has insufficient privileges" | Falta GRANT sobre una vista o tabla nueva | Ejecutar `create_readonly_user.php` de nuevo (es idempotente) |
| Operación expirada | El usuario tardó más de 5 min en confirmar | Volver a solicitar la operación a Otto |
| "Query sin WHERE" | Otto intentó UPDATE/DELETE sin WHERE | Validado en PHP — Otto debe reformular |
