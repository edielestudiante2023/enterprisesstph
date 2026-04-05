# PROMPT: HARDENING DE REPOSITORIO

## Objetivo

Profesionalizar un repositorio de software aplicando estandares de casas de software. Este prompt es reutilizable para cualquier aplicativo.

**IMPORTANTE — Dos tipos de entregable:**

1. **Archivos funcionales en el repo** — Se DEBEN crear/modificar directamente en el repositorio
2. **Documento consolidado** — UN solo `HARDENING-{aplicativo}.md` que resume todo, listo para PDF

**Los archivos funcionales NO son opcionales.** Sin ellos el hardening esta incompleto.

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

**Accion:** Conectar a la base de datos y recopilar informacion.

Documentar:

- Motor, version, host, tamano total
- Usuarios de BD y permisos
- Listado de tablas con registros y tamano, agrupadas por modulo funcional
- Listado de vistas
- Foreign keys y arbol de dependencias (tabla central)
- Tablas mas grandes y tablas vacias
- Observaciones (tablas huerfanas, obsoletas, etc.)

**Resultado:** Informacion lista para incluir en el documento consolidado.

---

## FASE 2: INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

**Accion:** Buscar en el codigo (excluyendo vendor/) todas las credenciales y servicios.

Documentar:

- API Keys hardcodeadas (grep por patrones del proveedor)
- Variables de entorno referenciadas (getenv, env, $_ENV)
- Para cada servicio: nombre, proposito, archivos donde se usa, metodo de carga, estado
- Evaluacion de seguridad: claves expuestas, hardcodeadas, por rotar
- Recomendaciones para migracion

**Resultado:** Informacion lista para incluir en el documento consolidado.

---

## FASE 3: CREAR ARCHIVOS FUNCIONALES EN EL REPOSITORIO

**IMPORTANTE: Esta fase CREA archivos reales en el repo. No es solo documentacion.**

### 3.1 — Crear README.md (archivo en raiz del repo)

Crear o reescribir `README.md` con:

- Nombre y descripcion del proyecto
- Stack tecnologico completo
- Modulos principales con descripcion
- Roles de usuario
- Estructura de carpetas principales
- Requisitos previos e instrucciones de instalacion local
- Variables de entorno necesarias (sin valores, solo nombres)
- Cron jobs con frecuencia y descripcion
- Instrucciones de deploy
- Links a documentacion adicional en docs/

### 3.2 — Crear CONTRIBUTING.md (archivo en raiz del repo)

Crear `CONTRIBUTING.md` con:

- Flujo de ramas (main → develop → feature/ → hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:)
- Convencion de nombres de ramas (feature/modulo-desc, hotfix/bug-desc)
- Reglas (no push directo a main, no credenciales, no destructivos en produccion)
- Proceso de revision con pipeline CI/CD

### 3.3 — Crear .env.example (archivo en raiz del repo)

Crear `.env.example` con:

- Todas las variables de entorno que usa la aplicacion
- Sin valores reales (solo placeholders o vacios)
- Comentarios explicativos por seccion
- Debe servir para que un desarrollador nuevo configure el proyecto

---

## FASE 4: RAMAS DE TRABAJO

**Accion:** Crear ramas en git.

- Verificar que `main` refleje lo que esta en produccion
- Crear rama `develop` desde `main` (si no existe)
- Documentar estado de ramas existentes (cuales conservar, cuales eliminar)

Estructura objetivo:

```
main          <- Produccion. Solo codigo validado y estable.
develop       <- Integracion. Cambios se unen aqui antes de ir a main.
feature/xxx   <- Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    <- Correcciones urgentes. Se crean desde main.
```

Proteccion de ramas (pendiente configurar en Gitea):
- main: requiere PR, no push directo
- develop: requiere PR desde feature/

---

## FASE 5: CREAR PIPELINES CI/CD

**IMPORTANTE: Esta fase CREA archivos de workflow reales en el repo.**

Plataforma: **Gitea con Gitea Runner (act_runner)**

### 5.1 — Crear .gitea/workflows/validate-and-deploy-qa.yml

Pipeline para Dev/QA. Trigger: push/PR a develop o feature/*

```
git push → Gitea → Runner → Tests + Trivy + Semgrep → Deploy SSH → LXC (Dev/QA)
```

Jobs en orden:
1. **test** — Verificacion de sintaxis del lenguaje (ej: `php -l`)
2. **trivy** — Escaneo de vulnerabilidades en dependencias (aquasecurity/trivy, HIGH+CRITICAL)
3. **semgrep** — Analisis estatico de seguridad (reglas del lenguaje + secrets + security-audit)
4. **secrets-scan** — Busqueda de credenciales hardcodeadas (patrones especificos del proyecto)
5. **deploy-qa** — SSH al servidor Dev/QA y ejecutar deploy (solo en push a develop)

### 5.2 — Crear .gitea/workflows/cutover-production.yml

Pipeline para produccion. Trigger: push a main (merge de PR)

```
PR develop → main → Validacion → Trivy → Semgrep → Deploy SSH → LXC Produccion (Hetzner)
                                                                → Verificacion post-deploy
```

Jobs: validate → trivy + semgrep (paralelo) → deploy-production + verificacion HTTP

**Todo por pipeline, nada manual.**

---

## FASE 6: ORGANIZACION DEL REPOSITORIO

**Accion:** Verificar y actualizar .gitignore, identificar basura.

- Verificar/actualizar `.gitignore` — debe excluir: .env, credenciales, uploads, logs, cache, archivos temporales, .claude/
- Identificar archivos basura trackeados (listar, no eliminar sin aprobacion)
- Identificar archivos que deberian moverse (ej: .md sueltos en raiz → docs/)
- Verificar visibilidad del repo (debe ser privado en Gitea)
- Verificar que no haya credenciales en el historial de git

---

## FASE 7: DOCUMENTO CONSOLIDADO PARA PDF

**Accion:** Crear `HARDENING-{aplicativo}.md` en docs/

Este documento consolida TODA la informacion de las fases anteriores en un solo archivo, listo para exportar a PDF y entregar al consultor.

Estructura del documento:

1. Descripcion del aplicativo (stack, modulos, roles, estructura, cron jobs)
2. Mapa de base de datos (tablas por modulo, vistas, relaciones, tamanos)
3. Inventario de API Keys (servicios, archivos, hallazgos de seguridad)
4. Documentacion del proyecto (resumen de lo que contiene README, CONTRIBUTING, .env.example)
5. Ramas de trabajo (estructura, estado actual, proteccion pendiente)
6. Pipelines CI/CD (workflows creados, jobs, secrets necesarios, flujo completo)
7. Organizacion del repositorio (estado, archivos basura, acciones)
8. Hallazgos criticos y acciones pendientes (priorizados con responsable)

---

## CHECKLIST DE ENTREGABLES

Antes de dar por terminado, verificar que TODOS estos existan:

### Archivos funcionales en el repositorio (obligatorios)

- [ ] `README.md` — Documentacion principal profesional
- [ ] `CONTRIBUTING.md` — Guia de contribucion con flujo de ramas
- [ ] `.env.example` — Template de variables de entorno
- [ ] `.gitea/workflows/validate-and-deploy-qa.yml` — Pipeline Dev/QA
- [ ] `.gitea/workflows/cutover-production.yml` — Pipeline produccion
- [ ] `.gitignore` actualizado
- [ ] Rama `develop` creada

### Documento para el consultor (obligatorio)

- [ ] `docs/HARDENING-{aplicativo}.md` — Documento consolidado listo para PDF

**Si falta alguno de estos archivos, el hardening esta INCOMPLETO.**

---

## COMO USAR ESTE PROMPT

1. Abrir conversacion con Claude Code en el directorio del aplicativo
2. Pegar este prompt
3. Proporcionar los datos requeridos (BD, API Keys, URL repo)
4. Ejecutar fase por fase (1 a 7)
5. Verificar checklist antes de terminar

> Reutilizable para cada aplicativo. La documentacion final se aloja en Gitea.
