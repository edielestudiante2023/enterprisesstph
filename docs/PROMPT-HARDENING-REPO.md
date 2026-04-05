# PROMPT: HARDENING DE REPOSITORIO

## Objetivo

Profesionalizar un repositorio de software aplicando estandares de casas de software. Este prompt es reutilizable para cualquier aplicativo. Al finalizar se entrega:

1. **Un solo documento PDF** por aplicativo (`HARDENING-{aplicativo}.md`) con todo consolidado
2. **Archivos funcionales** en el repo (README, CONTRIBUTING, .env.example, workflows)

> La auditoria del servidor ya fue realizada y esta documentada aparte.

---

## Datos requeridos antes de empezar

Antes de ejecutar este prompt, el usuario debe proporcionar:

| Dato | Descripcion | Ejemplo |
|------|-------------|---------|
| Nombre del aplicativo | Nombre del proyecto | mi-aplicacion |
| Credenciales de base de datos | Host, puerto, usuario, password, nombre BD | Host gestionado o local |
| API Keys en uso | Listado de servicios externos | Correo, IA, pagos, etc. |
| URL del repositorio | URL de Gitea o GitHub | gitea.servidor.com/org/repo |

---

## FASE 1: MAPA DE BASE DE DATOS

Conectar a la base de datos y documentar:

- Motor, version, host, tamano total
- Usuarios de BD y permisos
- Listado de tablas con registros y tamano, agrupadas por modulo funcional
- Listado de vistas
- Foreign keys y arbol de dependencias (tabla central)
- Tablas mas grandes y tablas vacias
- Observaciones (tablas huerfanas, obsoletas, etc.)

---

## FASE 2: INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

Buscar en el codigo (excluyendo vendor/):

- API Keys hardcodeadas (grep por patrones del proveedor)
- Variables de entorno referenciadas (getenv, env, $_ENV)
- Para cada servicio: nombre, proposito, archivos donde se usa, metodo de carga, estado
- Evaluacion de seguridad: claves expuestas, hardcodeadas, por rotar
- Recomendaciones para migracion

---

## FASE 3: DOCUMENTACION DEL PROYECTO

Crear en el repositorio:

### README.md
- Nombre y descripcion
- Stack tecnologico
- Modulos principales con descripcion
- Roles de usuario
- Estructura de carpetas
- Requisitos e instalacion local
- Variables de entorno (sin valores)
- Cron jobs
- Deploy
- Links a docs adicionales

### CONTRIBUTING.md
- Flujo de ramas (main, develop, feature/, hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:)
- Convencion de nombres de ramas
- Reglas (no push directo, no credenciales, no destructivos)
- Proceso de revision con pipeline

### .env.example
- Todas las variables de entorno sin valores reales
- Comentarios explicativos por seccion

---

## FASE 4: RAMAS DE TRABAJO

- Crear estructura: main, develop, feature/, hotfix/
- Verificar que main refleje produccion
- Crear develop desde main
- Documentar estado de ramas existentes
- Definir proteccion de ramas (pendiente configurar en Gitea)

---

## FASE 5: PIPELINES CI/CD

Plataforma: **Gitea con Gitea Runner (act_runner)**

### Pipeline Dev/QA: `.gitea/workflows/validate-and-deploy-qa.yml`

Trigger: push/PR a develop o feature/*

```
git push → Gitea → Runner → Tests + Trivy + Semgrep → Deploy SSH → LXC (Dev/QA)
```

Jobs: test (sintaxis) → trivy (vulnerabilidades) → semgrep (seguridad) → secrets-scan → deploy-qa

### Pipeline Produccion: `.gitea/workflows/cutover-production.yml`

Trigger: push a main (merge de PR)

```
PR develop → main → Validacion → Trivy → Semgrep → Deploy SSH → LXC Produccion (Hetzner)
                                                                → Verificacion post-deploy
```

**Todo por pipeline, nada manual.**

Documentar secrets necesarios en Gitea (QA_HOST, PROD_HOST, SSH keys, rutas).

---

## FASE 6: ORGANIZACION DEL REPOSITORIO

- Verificar visibilidad (debe ser privado en Gitea)
- Verificar y actualizar .gitignore
- Identificar archivos basura trackeados (listar, no eliminar sin aprobacion)
- Identificar archivos que deberian moverse (ej: .md sueltos en raiz → docs/)
- Verificar que no haya credenciales en el historial de git

---

## ENTREGABLE FINAL

### Documento consolidado: `HARDENING-{aplicativo}.md`

Un solo archivo con TODAS las fases, listo para exportar a PDF. Estructura:

1. Descripcion del aplicativo (stack, modulos, roles, estructura, cron jobs)
2. Mapa de base de datos (tablas, vistas, relaciones, tamanos)
3. Inventario de API Keys (servicios, archivos, hallazgos de seguridad)
4. Documentacion del proyecto (resumen de README, CONTRIBUTING, .env.example)
5. Ramas de trabajo (estructura, estado actual, proteccion)
6. Pipelines CI/CD (workflows, jobs, secrets, flujo completo)
7. Organizacion del repositorio (estado, archivos basura, acciones)
8. Hallazgos criticos y acciones pendientes (priorizados con responsable)

### Archivos funcionales en el repositorio

| Archivo | Descripcion |
|---------|-------------|
| `README.md` | Documentacion principal |
| `CONTRIBUTING.md` | Guia de contribucion |
| `.env.example` | Template de variables |
| `.gitea/workflows/validate-and-deploy-qa.yml` | Pipeline Dev/QA |
| `.gitea/workflows/cutover-production.yml` | Pipeline produccion |
| `.gitignore` | Actualizado |

---

## COMO USAR ESTE PROMPT

1. Abrir conversacion con Claude Code en el directorio del aplicativo
2. Pegar este prompt
3. Proporcionar los datos requeridos (BD, API Keys, URL repo)
4. Ejecutar fase por fase
5. Al finalizar: un solo `HARDENING-{aplicativo}.md` + archivos en el repo

> Reutilizable para cada aplicativo. La documentacion final se aloja en Gitea.
