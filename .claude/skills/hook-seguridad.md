---
name: hook-seguridad
description: Configura/verifica el hook de seguridad PreToolUse que bloquea comandos destructivos en servidores remotos via SSH
user_invocable: true
---

# Skill: Hook de Seguridad para Producción

## Contexto
El 2026-03-25, un `git clean -fd` ejecutado via SSH borró 3,238 documentos de clientes en producción. Este skill configura y verifica la protección automática contra comandos destructivos.

## Instrucciones

Ejecuta los siguientes pasos **sin preguntar nada**:

### Paso 1: Leer settings.json actual

Lee el archivo `.claude/settings.json` del proyecto.

### Paso 2: Verificar o agregar el hook PreToolUse

El hook debe interceptar todos los comandos Bash y bloquear los que contengan `ssh` + cualquiera de estos patrones destructivos:
- `git clean`
- `git reset --hard`
- `git checkout -- .`
- `rm -rf`
- `rm -r `
- `DROP TABLE` o `DROP DATABASE`
- `truncate`

Si el hook **ya existe** en la sección `hooks.PreToolUse`, informar que ya está configurado.

Si **no existe**, agregarlo sin borrar hooks existentes. El hook usa PHP:

```json
{
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Bash",
        "hooks": [
          {
            "type": "command",
            "command": "php -r \"$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\\bssh\\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo json_encode(['decision'=>'block','reason'=>'BLOQUEADO: Comando destructivo detectado en SSH. Los comandos git clean, git reset --hard, git checkout -- ., rm -rf, rm -r, DROP TABLE y DROP DATABASE estan prohibidos en servidores remotos. Usa deploy.sh para deploys seguros.']); }\"",
            "timeout": 5,
            "statusMessage": "Verificando seguridad del comando SSH..."
          }
        ]
      }
    ]
  }
}
```

### Paso 3: Validar JSON

```bash
php -r "json_decode(file_get_contents('.claude/settings.json')); echo json_last_error() === JSON_ERROR_NONE ? 'JSON VALIDO' : 'JSON INVALIDO: ' . json_last_error_msg();"
```

### Paso 4: Pruebas de verificación

Ejecutar las 4 pruebas en paralelo:

**Prueba 1 - DEBE bloquear git clean via SSH:**
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"ssh root@1.2.3.4 \"git clean -fd\""}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo json_encode(['decision'=>'block','reason'=>'BLOQUEADO']); } else { echo 'NO BLOQUEADO - ERROR'; }"
```

**Prueba 2 - DEBE bloquear rm -rf via SSH (otra IP):**
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"ssh deploy@192.168.1.100 \"rm -rf /var/www/uploads\""}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo json_encode(['decision'=>'block','reason'=>'BLOQUEADO']); } else { echo 'NO BLOQUEADO - ERROR'; }"
```

**Prueba 3 - NO debe bloquear SSH normal (git pull):**
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"ssh root@1.2.3.4 \"git pull origin main\""}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo 'BLOQUEADO - ERROR'; } else { echo 'PERMITIDO - OK'; }"
```

**Prueba 4 - NO debe bloquear comandos locales:**
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"rm -rf node_modules"}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo 'BLOQUEADO - ERROR'; } else { echo 'PERMITIDO - OK'; }"
```

### Paso 5: Verificar/crear memoria de feedback

Verificar si existe el archivo de memoria `feedback_deploy.md` en el directorio de memoria del proyecto. Si no existe o no contiene la regla sobre comandos destructivos, crear/actualizar:

```markdown
---
name: feedback_deploy_seguro
description: NUNCA ejecutar comandos destructivos en servidores remotos via SSH
type: feedback
---

NUNCA ejecutar estos comandos en NINGUN servidor de produccion via SSH:
- git clean -fd (borra archivos de uploads/datos de usuarios)
- git checkout -- . (descarta cambios locales sin preguntar)
- git reset --hard (destruye todo)
- rm -rf en directorios de uploads, writable, o datos de usuarios
- DROP TABLE / DROP DATABASE sin backup verificado

**Why:** El 2026-03-25, git clean -fd borro 3,238 documentos de 55 clientes en produccion.

**How to apply:**
- Para deploys: usar siempre deploy.sh o equivalente
- Si git pull falla por conflictos: resolver manualmente uno por uno
- Si hay archivos untracked que bloquean: moverlos a /tmp/, NUNCA borrarlos con git clean
- Existe un hook PreToolUse que bloquea estos comandos automaticamente
```

### Paso 6: Verificar deploy.sh en servidor

Verificar si existe `deploy.sh` en el servidor de produccion (`/www/wwwroot/phorizontal/enterprisesstph/deploy.sh`). Si no existe, crearlo:

```bash
#!/bin/bash
set -e
echo "=== DEPLOY SEGURO ==="
STASHED=0
if [ -n "$(git status --porcelain)" ]; then
    echo "[1/3] Guardando cambios locales..."
    git stash push -m "deploy-$(date +%Y%m%d_%H%M%S)"
    STASHED=1
fi
echo "[2/3] Descargando cambios..."
git pull origin main
if [ "$STASHED" -eq 1 ]; then
    echo "[3/3] Restaurando cambios locales..."
    git stash pop || echo "ADVERTENCIA: Conflicto en stash pop"
fi
echo "=== DEPLOY COMPLETADO ==="
echo "NUNCA ejecutar: git clean -fd"
```

### Paso 7: Reportar resultado

Muestra un resumen con:
- Estado del hook (ya existia / recien creado)
- Resultado de validacion JSON
- Resultado de las 4 pruebas
- Estado de la memoria de feedback
- Estado de deploy.sh
