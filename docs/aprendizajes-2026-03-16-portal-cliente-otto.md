# Aprendizajes — Portal Cliente Otto · 2026-03-16

## Contexto
Sesión de construcción y depuración del módulo de chat Otto para el portal del cliente.
Portal: phorizontal.cycloidtalent.com · Stack: CodeIgniter 4 + OpenAI GPT-4o + MySQL DigitalOcean

---

## 1. `env()` NO puede usarse en inicializadores de propiedades PHP

**Problema:**
```php
// ❌ FALLA en PHP 8.2 — "Constant expression contains invalid operations"
public array $readonly = [
    'hostname' => env('readonly.hostname', 'localhost'),
];
```

**Causa:** PHP trata los valores por defecto de propiedades de clase como "expresiones constantes". Las llamadas a función (`env()`) no están permitidas ahí.

**Solución correcta:**
```php
// ✅ Construir el array de config dinámicamente en el método que lo usa
$config = [
    'hostname' => env('readonly.hostname', 'localhost'),
    'username' => env('readonly.username', 'cycloid_readonly'),
    // ...
];
$db = \Config\Database::connect($config);
```

O alternativa CI4: asignar en `__construct()` después de `parent::__construct()`.

---

## 2. `Database.php` estaba en `.gitignore` — nunca llegó a producción

**Problema:** El grupo `readonly` fue añadido al `Database.php` local pero el archivo está en `.gitignore`. El deploy via `git push/pull` nunca lo transfirió.

**Síntoma en producción:**
```
"readonly" is not a valid database connection group.
```

**Solución aplicada:** Construir la config readonly dinámicamente en el controlador con `\Config\Database::connect($arrayConfig)` — sin necesidad de tocar `Database.php`.

**Lección:** Cualquier cambio en archivos de `.gitignore` debe copiarse manualmente al servidor con `scp` o configurarse vía variables de entorno en `.env`.

---

## 3. Causa raíz del "problema técnico" en el chat del cliente

La secuencia completa de errores que hacía que Otto dijera "parece que hubo un problema técnico":

| # | Error | Causa |
|---|---|---|
| 1 | `SELECT *` devolvía payload enorme | json_encode fallaba silenciosamente con campos TEXT muy largos o encoding inválido |
| 2 | `cycloid_readonly@%` Access Denied | Contraseña del usuario MySQL no sincronizada — necesitaba `ALTER USER ... IDENTIFIED BY` |
| 3 | `"readonly" is not a valid connection group` | `Database.php` local nunca llegó a producción (estaba en `.gitignore`) |

**El síntoma era siempre el mismo** (mensaje genérico de error de OpenAI) pero cada vez tenía una causa raíz diferente.

---

## 4. Logging ácido para diagnóstico

Cuando OpenAI dice "parece que hubo un problema técnico", el error es **silencioso** — no aparece en el response del chat. La única forma de encontrar la causa raíz es agregar `log_message()` en cada paso del flujo:

```php
log_message('info',  "[tag] PASO_X descripción resultado=xxx");
log_message('error', "[tag] PASO_X_FAIL " . $e->getMessage());
```

Pasos críticos a loguear en `toolExecuteSelect`:
1. `VALIDATE_OK/FAIL` — si la query pasa el validador
2. `SCOPE id_cliente=X scope_ok=true/false` — si el guardrail de scope pasa
3. `CONNECT_OK/FAIL` — si la conexión DB se establece
4. `QUERY_OK rows=N` — si la query ejecuta y cuántas filas devuelve
5. `JSON_ENCODE json_error=0 json_len=N` — si el encode es válido

Ver logs en producción:
```bash
ssh root@66.29.154.174 "grep '\[ClientChat\]' /www/wwwroot/.../writable/logs/log-YYYY-MM-DD.log"
```

---

## 5. `ALTER USER` para sincronizar contraseña en DigitalOcean MySQL

`CREATE USER IF NOT EXISTS` no actualiza la contraseña si el usuario ya existe.
Si la contraseña no coincide, el usuario conecta pero MySQL dice "Access Denied".

```sql
-- Siempre resetear después de crear para garantizar sincronización:
ALTER USER 'cycloid_readonly'@'%' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
```

---

## 6. `grep -v` puede ocultar evidencia crítica

Durante el diagnóstico, usé `grep -v 'v_tbl_venc'` para filtrar resultados y accidentalmente **excluí** la vista `v_tbl_vencimientos_mantenimientos` de la lista. Concluí erróneamente que la vista no existía.

**Regla:** Nunca usar `grep -v` para "limpiar" output de diagnóstico — puede ocultar precisamente lo que se busca.

---

## 7. `SELECT *` en contexto de chat IA — riesgo real

`SELECT *` sobre una vista con columnas TEXT largas (ej: `actividad_plandetrabajo`, `observaciones`) puede:
- Devolver megabytes de texto a OpenAI innecesariamente
- Causar fallos silenciosos de `json_encode` si hay encoding inválido
- Exceder `max_tokens: 4000` de la respuesta OpenAI

**Fix aplicado:**
```php
// Sanitizar antes de devolver a OpenAI
array_walk_recursive($rows, function (&$value) {
    if (is_string($value)) {
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        if (mb_strlen($value) > 800) {
            $value = mb_substr($value, 0, 800) . '…';
        }
    }
});
```

Y regla en el system prompt: **Nunca `SELECT *` — siempre columnas específicas.**

---

## 8. Consultor accediendo a `/client-chat` sin ID de cliente

**Problema:** El consultor accede a `/client-chat` (sin `id_cliente` en la URL). La sesión del consultor no tiene `id_entidad`, por lo que `resolveClientId()` devuelve `0`. El guardrail bloquea con `id_cliente = 0` y Otto dice "error técnico".

**Fix:**
```php
// En ClientChatController::index()
if (in_array($role, ['consultant', 'admin']) && !$idClienteParam) {
    return redirect()->to('/consultor/dashboard')
        ->with('error', 'Selecciona un cliente para abrir su chat con Otto.');
}
```

**Para que el consultor pruebe el chat de un cliente específico:**
Debe acceder a `/client-chat/{id_cliente}` (con el ID).

---

## 9. Arquitectura final del Portal Cliente Otto

```
Login cliente → /dashboard → botón "Otto — Asistente IA" → /client-chat
                                    ↓
                         ClientChatController::index()
                                    ↓
                    checkAccess: role === 'client' ✓
                    resolveClientId: session id_entidad
                                    ↓
                POST /client-chat/send { message, history }
                                    ↓
                    toolExecuteSelect()
                        1. validateQuery (heredado)
                        2. queryContainsClientScope (guardrail)
                        3. connect readonly (config dinámica)
                        4. query → sanitize → json
                                    ↓
                    OpenAI → respuesta en lenguaje natural
```

**3 capas de seguridad:**
- DB: `cycloid_readonly@%` — solo SELECT sobre `v_*`
- App: `toolExecuteWrite` bloqueado, `confirmOperation/Delete` → 403
- Prompt: `WHERE id_cliente = {id}` obligatorio en cada query

---

## 10. Mayéutica + Jerarquía de vistas — reglas en el system prompt

Dos reglas que cambiaron completamente el comportamiento de Otto:

**Mayéutica:** Antes de ejecutar, verificar si faltan:
- Cliente/copropiedad
- Estado (abierta/cerrada/en gestión)
- Período (mes, año, rango)
- Tipo/categoría

**Jerarquía:** `SELECT → v_*` siempre · `INSERT/UPDATE/DELETE → tbl_*` siempre

Sin estas reglas, Otto asumía valores, usaba tablas directas (devolviendo IDs crudos) y ejecutaba queries sin suficiente contexto.
