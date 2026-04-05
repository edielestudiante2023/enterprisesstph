# Guia de contribucion — enterprisesstph

## Flujo de ramas

```
main            Produccion. Solo codigo validado y estable.
  └── develop   Integracion. Aqui se unen los cambios antes de ir a main.
       ├── feature/xxx   Nuevas funcionalidades. Se crean desde develop.
       └── hotfix/xxx    Correcciones urgentes. Se crean desde main.
```

### Crear una nueva funcionalidad

```bash
# 1. Partir desde develop actualizado
git checkout develop
git pull origin develop

# 2. Crear rama feature
git checkout -b feature/nombre-descriptivo

# 3. Trabajar y hacer commits
git add archivos-modificados
git commit -m "feat: descripcion breve del cambio"

# 4. Subir la rama
git push -u origin feature/nombre-descriptivo

# 5. Crear Pull Request hacia develop
# Desde GitHub: feature/nombre-descriptivo → develop
```

### Corregir un bug urgente en produccion

```bash
# 1. Partir desde main
git checkout main
git pull origin main

# 2. Crear rama hotfix
git checkout -b hotfix/descripcion-del-bug

# 3. Corregir y commitear
git add archivos-modificados
git commit -m "fix: descripcion del fix"

# 4. Subir y crear PR hacia main Y develop
git push -u origin hotfix/descripcion-del-bug
# Crear PR hacia main (para produccion)
# Crear PR hacia develop (para que el fix quede en integracion)
```

## Convencion de commits

Usar prefijos para identificar el tipo de cambio:

| Prefijo | Uso |
|---------|-----|
| `feat:` | Nueva funcionalidad |
| `fix:` | Correccion de bug |
| `docs:` | Cambios en documentacion |
| `refactor:` | Refactorizacion sin cambio funcional |
| `style:` | Formato, espacios, sin cambio de logica |
| `chore:` | Tareas de mantenimiento (deps, configs) |

Ejemplos:
- `feat: agregar modulo de dotacion de aseadora`
- `fix: corregir calculo de dias en pendientes`
- `docs: actualizar README con nuevas variables de entorno`

## Convencion de nombres de ramas

```
feature/modulo-descripcion      → feature/inspecciones-plan-emergencia
hotfix/bug-descripcion          → hotfix/fix-calculo-kpi-frecuencia
```

## Reglas

1. **Nunca hacer push directo a main** — siempre via Pull Request
2. **Nunca hacer push directo a develop** — siempre via Pull Request desde feature/
3. **No commitear credenciales** — usar variables de entorno (.env)
4. **No commitear archivos temporales** — verificar .gitignore antes
5. **No usar** `git clean -fd`, `git reset --hard`, `git checkout -- .` **en produccion**

## Proceso de revision

1. Crear Pull Request con descripcion clara del cambio
2. El pipeline de CI/CD valida automaticamente (sintaxis PHP, secrets scanning)
3. Si el pipeline falla, corregir antes de solicitar revision
4. Una vez aprobado, hacer merge a la rama destino

## Deploy a produccion

Solo se hace deploy cuando hay merge a `main`:

```bash
ssh root@SERVIDOR "cd /ruta/proyecto && bash deploy.sh"
```

El script `deploy.sh` es seguro: hace stash, pull, pop. Nunca borra archivos.
