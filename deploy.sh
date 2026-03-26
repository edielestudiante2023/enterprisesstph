#!/bin/bash
# =============================================================
# DEPLOY SEGURO — enterprisesstph
# =============================================================
# USO: ssh root@66.29.154.174 "cd /www/wwwroot/phorizontal/enterprisesstph && bash deploy.sh"
#
# PROHIBIDO usar:
#   - git clean -fd  (BORRA uploads de clientes)
#   - git checkout -- .  (descarta cambios locales sin preguntar)
#   - git reset --hard  (destruye todo)
# =============================================================

set -e

echo "=========================================="
echo "  DEPLOY SEGURO — enterprisesstph"
echo "  $(date)"
echo "=========================================="

# 1. Verificar que estamos en el directorio correcto
if [ ! -f "spark" ]; then
    echo "ERROR: No estás en el directorio del proyecto"
    exit 1
fi

# 2. Guardar cambios locales si los hay
STASHED=0
if [ -n "$(git status --porcelain)" ]; then
    echo "[1/3] Guardando cambios locales (git stash)..."
    git stash push -m "deploy-$(date +%Y%m%d_%H%M%S)"
    STASHED=1
else
    echo "[1/3] No hay cambios locales que guardar"
fi

# 3. Pull
echo "[2/3] Descargando cambios (git pull)..."
git pull origin main

# 4. Restaurar cambios locales
if [ "$STASHED" -eq 1 ]; then
    echo "[3/3] Restaurando cambios locales (git stash pop)..."
    git stash pop || echo "ADVERTENCIA: Conflicto en stash pop. Revisa manualmente con 'git stash show' y 'git stash drop'"
else
    echo "[3/3] Nada que restaurar"
fi

echo ""
echo "=========================================="
echo "  DEPLOY COMPLETADO"
echo "=========================================="
echo ""
echo "RECORDATORIO: Los uploads de clientes viven en:"
echo "  /www/soportes-clientes/"
echo ""
echo "NUNCA ejecutar: git clean -fd"
echo "=========================================="
