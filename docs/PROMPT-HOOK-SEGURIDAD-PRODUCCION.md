# PROMPT: Configurar hook de seguridad para bloquear comandos destructivos en CUALQUIER servidor

Copia y pega esto en cualquier proyecto de Claude Code:

---

## Contexto

El 2026-03-25, un `git clean -fd` ejecutado via SSH en un servidor de producción borró 3,238 documentos de clientes. Esto ocurrió porque Claude sugirió ese comando para resolver un conflicto de merge durante un deploy. Necesitamos una protección a nivel de sistema que **bloquee automáticamente** cualquier comando destructivo antes de que se ejecute en CUALQUIER servidor, sin depender de la memoria ni del comportamiento del agente.

## Tarea

Configura un **PreToolUse hook** en `.claude/settings.json` que intercepte TODOS los comandos Bash y bloquee los que sean destructivos contra CUALQUIER servidor remoto (SSH).

### Comandos que DEBEN ser bloqueados

Cualquier comando que contenga `ssh` y además contenga:
- `git clean` (borra archivos no trackeados)
- `git reset --hard` (destruye cambios locales)
- `git checkout -- .` (revierte todos los archivos)
- `rm -rf` (borra directorios recursivamente)
- `rm -r ` (borra directorios recursivamente)
- `DROP TABLE` o `DROP DATABASE` (destrucción de BD)
- `truncate` en contexto SQL

### Implementación

1. Lee el archivo `.claude/settings.json` existente
2. Si ya tiene una sección `hooks`, agrégale el nuevo hook sin borrar los existentes
3. Si no tiene `hooks`, créala

El hook usa PHP (disponible en XAMPP/cualquier entorno con PHP) porque `jq` no siempre está instalado en Windows:

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

### Verificación

Después de agregar el hook:

1. Valida que el JSON sea correcto:
```bash
php -r "json_decode(file_get_contents('.claude/settings.json')); echo json_last_error() === JSON_ERROR_NONE ? 'JSON VALIDO' : 'JSON INVALIDO: ' . json_last_error_msg();"
```

2. Prueba que BLOQUEE comandos destructivos contra CUALQUIER servidor:
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"ssh root@1.2.3.4 \"git clean -fd\""}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo json_encode(['decision'=>'block','reason'=>'BLOQUEADO']); }"
```
Debe imprimir: `{"decision":"block","reason":"BLOQUEADO"}`

3. Prueba con otra IP:
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"ssh deploy@192.168.1.100 \"rm -rf /var/www/uploads\""}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo json_encode(['decision'=>'block','reason'=>'BLOQUEADO']); }"
```
Debe imprimir: `{"decision":"block","reason":"BLOQUEADO"}`

4. Prueba que NO bloquee un comando SSH normal:
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"ssh root@1.2.3.4 \"git pull origin main\""}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo json_encode(['decision'=>'block','reason'=>'BLOQUEADO']); }"
```
NO debe imprimir nada (comando permitido).

5. Prueba que NO bloquee comandos locales (sin SSH):
```bash
echo '{"tool_name":"Bash","tool_input":{"command":"rm -rf node_modules"}}' | php -r "$d=json_decode(file_get_contents('php://stdin'),true); $cmd=$d['tool_input']['command']??''; if(preg_match('/\bssh\b/', $cmd) && preg_match('/git clean|git reset --hard|git checkout -- \\\\.|rm -rf|rm -r |DROP TABLE|DROP DATABASE|truncate/i', $cmd)) { echo json_encode(['decision'=>'block','reason'=>'BLOQUEADO']); }"
```
NO debe imprimir nada (es local, no SSH).

### También agregar a memoria de feedback

Crea o actualiza el archivo de memoria de feedback del deploy:

```markdown
---
name: feedback_deploy_seguro
description: NUNCA ejecutar comandos destructivos en servidores remotos via SSH
type: feedback
---

NUNCA ejecutar estos comandos en NINGUN servidor de producción via SSH:
- git clean -fd (borra archivos de uploads/datos de usuarios)
- git checkout -- . (descarta cambios locales sin preguntar)
- git reset --hard (destruye todo)
- rm -rf en directorios de uploads, writable, o datos de usuarios
- DROP TABLE / DROP DATABASE sin backup verificado

**Why:** El 2026-03-25, git clean -fd borró 3,238 documentos de 55 clientes en producción.

**How to apply:**
- Para deploys: usar siempre deploy.sh o equivalente
- Si git pull falla por conflictos: resolver manualmente uno por uno
- Si hay archivos untracked que bloquean: moverlos a /tmp/, NUNCA borrarlos con git clean
- Existe un hook PreToolUse que bloquea estos comandos automáticamente
```

### Crear deploy.sh si no existe

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

## NO preguntes nada. Configura el hook, verifica que funcione, y confirma.

---
