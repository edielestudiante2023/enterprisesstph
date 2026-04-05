# HARDENING DE REPOSITORIO — enterprisesstph

**Fecha:** 2026-04-05
**Aplicativo:** enterprisesstph — Plataforma SST para Propiedad Horizontal
**Empresa:** Cycloid Talent
**Preparado para:** Edwin Lopez Camello (consultor de infraestructura)

---

## TABLA DE CONTENIDO

1. Descripcion del aplicativo
2. Mapa de base de datos
3. Inventario de API Keys y servicios externos
4. Documentacion del proyecto (README, CONTRIBUTING, .env.example)
5. Ramas de trabajo
6. Pipelines CI/CD (Gitea)
7. Organizacion del repositorio
8. Hallazgos criticos y acciones pendientes

---

## 1. DESCRIPCION DEL APLICATIVO

### Stack tecnologico

| Componente | Tecnologia |
|-----------|------------|
| Backend | PHP 8.2 + CodeIgniter 4 |
| Base de datos | MySQL 8 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx 1.24 (Ubuntu 24.04) |
| Email | SendGrid API v3 — por migrar a SMTP2Go |
| PDF | TCPDF (contratos) + DOMPDF 3.0.0 (certificados, actas, inspecciones) |
| Excel | PhpSpreadsheet |
| IA | OpenAI GPT-4o-mini (Chat Otto, generacion de textos) |
| IA (tools) | Anthropic Claude Haiku (clasificacion de PDFs) |
| PWA | Modulo inspecciones (manifest + service worker) |
| Analytics | Looker Studio (embeds) |

### Modulos principales (16)

| Modulo | Descripcion |
|--------|-------------|
| Contratos | Ciclo completo: creacion, firma digital, PDF, renovacion, cancelacion |
| Plan de Trabajo Anual (PTA) | Actividades PHVA por cliente, edicion inline, exportacion Excel, auditoria |
| Evaluacion Estandares Minimos | Decreto 1072, evaluacion por ciclo, historial de puntajes |
| Actas de Visita | Registro con fotos, firma digital, PDF, notificaciones |
| Inspecciones (PWA) | Locativa, extintores, botiquin, gabinetes, senalizacion, comunicaciones, recursos |
| Capacitaciones | Cronograma, asistencia induccion, evaluacion, reportes |
| KPIs | 20+ indicadores SST (frecuencia, severidad, mortalidad, ausentismo, etc.) |
| Pendientes | Compromisos con conteo de dias, recordatorios automaticos |
| Plan de Saneamiento | Limpieza, residuos, plagas, agua potable, contingencias, KPIs |
| Documentos SGSST | 40+ documentos normativos (politicas, programas, formatos) |
| Matrices | Riesgos, vulnerabilidad, EPP (generacion automatica desde plantillas Excel) |
| Chat Otto (IA) | Asistente IA con function calling, consultas SQL readonly, 3 capas de seguridad |
| Presupuesto SST | Categorias, items, detalle de ejecucion |
| Informes de Avances | Reportes mensuales con metricas e imagenes |
| Firmas Digitales | Firma electronica via token por email (contratos + protocolo alturas) |
| Portal Cliente | Dashboard readonly, chat Otto, inspecciones, reportes, pendientes |

### Roles de usuario

| Rol | Acceso |
|-----|--------|
| admin | Todo el sistema + gestion de usuarios + configuracion |
| consultant | Gestion de clientes asignados + inspecciones + chat IA completo |
| client | Portal readonly + chat Otto (solo SELECT) |

### Estructura del proyecto

```
enterprisesstph/
├── app/
│   ├── Commands/          # 10 comandos spark (cron jobs)
│   ├── Config/            # Routes.php, Database.php, Filters.php
│   ├── Controllers/       # ~160 controladores
│   │   └── Inspecciones/  # ~40 controladores de inspecciones (PWA)
│   ├── Filters/           # AuthFilter, ApiKeyFilter, AuthOrApiKeyFilter
│   ├── Libraries/         # 17 librerias de logica de negocio
│   ├── Models/            # ~100 modelos
│   ├── Services/          # IADocumentacionService
│   ├── SQL/               # Scripts de migracion
│   ├── Templates/         # Plantillas Excel para matrices
│   └── Views/             # Vistas PHP
├── docs/                  # Documentacion tecnica
├── public/                # Punto de entrada web (index.php)
├── tools/                 # Scripts utilitarios
├── tests/                 # Tests PHPUnit
├── writable/              # Logs, cache, sesiones
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables (SI commitear)
├── deploy.sh              # Script de deploy seguro
├── CONTRIBUTING.md        # Guia de contribucion
├── README.md              # Documentacion principal
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

### Cron jobs (10 tareas programadas)

| Comando | Frecuencia | Descripcion |
|---------|-----------|-------------|
| `php spark auditoria:revisar-visitas-diario` | Diario 7 AM | Verificar visitas del dia anterior |
| `php spark actas:notificaciones` | Diario 7 AM | Notificaciones de actas |
| `php spark inspecciones:resumen-pendientes` | Diario 5 PM | Inspecciones pendientes |
| `php spark visitas:recordatorio` | Diario 3 PM | Recordatorio de visitas |
| `php spark seguimiento:agenda-diario` | Diario 3 PM | Seguimiento agenda |
| `php spark firmas:protocolo-alturas --reporte` | Diario 7 AM | Reporte firmas alturas |
| `php spark contratos:resumen-semanal` | Lunes 7 AM | Resumen contratos |
| `php spark auditoria:recordatorio-sin-agendar` | L-V 7 AM | Clientes sin agendar |
| `php spark pendientes:recordatorio` | Dia 1 y 16 | Recordatorio pendientes |
| `curl .../cron/send-emails` | Lunes 8 AM | Emails programados |

---

## 2. MAPA DE BASE DE DATOS

**Motor:** MySQL 8 (DigitalOcean Managed)
**Base de datos:** propiedad_horizontal
**Tamano total:** 18.31 MB
**SSL:** Required

### Usuarios de base de datos

| Usuario | Permisos | Uso |
|---------|----------|-----|
| cycloid_userdb | Full access | Aplicacion principal (CRUD) |
| cycloid_readonly | SELECT only (vistas v_* + tablas maestras) | Portal cliente (Chat Otto) |

### Resumen

- **107 tablas** (BASE TABLE)
- **79 vistas** (VIEW) — 60 con prefijo `v_` para portal cliente + 19 de negocio
- **72 foreign keys** definidas
- **28 tablas vacias** (26%) — modulos pendientes o posiblemente obsoletos

### Tablas principales por modulo

**Nucleo (7 tablas):** tbl_clientes (60 reg), tbl_usuarios (61), tbl_usuario_roles (61), tbl_roles (3), tbl_consultor (4), tbl_sesiones_usuario (220), tbl_chat_log (302)

**Plan de trabajo (5 tablas):** tbl_pta_cliente (5,189 reg — la mas grande), tbl_pta_cliente_audit (4,108), tbl_pta_cliente_old (1,483), tbl_pta_transiciones (200), tbl_inventario_actividades (146)

**Evaluacion estandares (5 tablas):** evaluacion_inicial_sst (2,044 reg — 2.52 MB), estandares (4), estandares_accesos (88), historial_resumen_estandares (765), historial_resumen_plan_trabajo (333)

**Reportes (5 tablas):** tbl_reporte (3,282 reg — 1.08 MB), report_type_table (18), detail_report (34), document_versions (1,428), tbl_listado_maestro_documentos (28)

**Actas de visita (5 tablas):** tbl_acta_visita (28), tbl_acta_visita_fotos (2), tbl_acta_visita_integrantes (58), tbl_acta_visita_pta (402), tbl_acta_visita_temas (68)

**Inspecciones (12 tablas):** 6 master + 5 detalle + 1 recursos seguridad

**Capacitaciones (6 tablas):** capacitaciones_sst (31), tbl_cronog_capacitacion (434), tbl_reporte_capacitacion (13), tbl_asistencia_induccion (13) + asistentes (87)

**KPIs (10 tablas):** tbl_kpis (17), tbl_kpi_definition (17), tbl_client_kpi (34), + variables, periodos, objetivos

**Plan saneamiento (13 tablas):** programas (agua, limpieza, plagas, residuos) + KPIs + contingencias

**Otros:** contratos (70 reg), pendientes (487), informe_avances (29 — 1.52 MB), presupuesto (4 tablas), matrices (43), mantenimientos (18), vigias (15), dotaciones (3 tablas)

### Tabla central: tbl_clientes

40+ tablas dependen de `tbl_clientes.id_cliente` via foreign key. Es la entidad central del sistema.

### Tablas mas grandes por peso

| Tabla | Registros | Tamano |
|-------|-----------|--------|
| evaluacion_inicial_sst | 2,044 | 2.52 MB |
| tbl_pta_cliente_audit | 4,108 | 2.52 MB |
| tbl_informe_avances | 29 | 1.52 MB |
| tbl_pta_cliente | 5,189 | 1.48 MB |
| tbl_reporte | 3,282 | 1.08 MB |

### Vistas de negocio (19)

cronograma_capacitaciones_cliente, evaluacion_inicial_cliente, evaluacion_inicial_cliente_consultor, mantenimientos_por_vencer, pendientes_abiertos_vencidos, pendientes_del_cliente, plan_de_trabajo_del_cliente, resumen_estandares_cliente, resumen_mensual_plan_trabajo, view_clientes_consultores, vista_cronograma_capacitaciones, vw_consumo_usuarios, vw_reporte_completo, + 6 adicionales

---

## 3. INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

### Resumen

| Servicio | Variable | Archivos | Estado |
|----------|----------|----------|--------|
| SendGrid | `SENDGRID_API_KEY` | 15+ | Activa — por migrar a SMTP2Go |
| OpenAI | `OPENAI_API_KEY` | 7 | Activa |
| Anthropic | `ANTHROPIC_API_KEY` | 1 | Activa (solo tools/) |
| Token API | `APP_API_KEY` | 2 | Activa — token interno |
| Token Cron | `CRON_TOKEN` | 1 | Activa |

### SendGrid (por migrar a SMTP2Go)

Usado en 15+ archivos para todo el email transaccional: recuperacion de password, notificaciones de firma, recordatorios de pendientes, auditorias, inspecciones, contratos.

**Patron:** `new \SendGrid(getenv('SENDGRID_API_KEY'))`

**Archivos principales:**
- 7 controladores (Auth, Chat, AdminDashboard, Email, FirmaElectronica, FirmaAlturas, User)
- 3 libraries (ResumenPendientesNotificador, NotificadorVisita, InspeccionEmailNotifier)
- 6 commands/cron (Seguimiento, Contratos, Pendientes, ProtocoloAlturas, Auditoria, SinAgendar)

**Recomendacion:** Crear una clase `EmailService` centralizada antes de migrar a SMTP2Go.

### OpenAI

Usado en 7 archivos para IA generativa: Chat Otto, generacion de clausulas de contratos, sugerencias PTA, analisis de pendientes, evaluaciones de capacitacion.

**Patron:** cURL directo a `https://api.openai.com/v1/chat/completions`
**Modelo:** gpt-4o-mini (configurable via `OPENAI_MODEL`)

### Anthropic (Claude)

Solo 1 archivo: `tools/clasificar_pdfs.php` — clasificacion automatica de PDFs con Claude Haiku.

### HALLAZGOS CRITICOS DE SEGURIDAD

**CRITICO — Credencial hardcodeada en codigo:**

| Archivo | Problema |
|---------|----------|
| `app/SQL/query_pta_indicadores.php:5` | Password de BD hardcodeado directamente |
| `app/Controllers/UserController.php:288` | Fallback `SG.xxxxxx` hardcodeado |

**CRITICO — Repositorio publico:**

El repositorio `github.com/edielestudiante2023/enterprisesstph` es **PUBLICO**. El `.env` de produccion contiene todas las credenciales. Las siguientes claves deben rotarse:

| Variable | Accion |
|----------|--------|
| `SENDGRID_API_KEY` | Rotar (se reemplaza por SMTP2Go) |
| `OPENAI_API_KEY` | ROTAR INMEDIATAMENTE |
| `database.default.password` | Rotar en DigitalOcean |
| `readonly.password` | Rotar en DigitalOcean |
| `APP_API_KEY` | Regenerar |
| `CRON_TOKEN` | Cambiar |

---

## 4. DOCUMENTACION DEL PROYECTO

### Archivos creados en el repositorio

| Archivo | Descripcion |
|---------|-------------|
| `README.md` | Documentacion principal: stack, modulos, roles, estructura, instalacion, cron jobs, deploy |
| `CONTRIBUTING.md` | Guia de contribucion: flujo de ramas, convencion de commits, reglas, proceso de revision |
| `.env.example` | Template con todas las variables de entorno necesarias (sin valores reales) |

### README.md incluye

- Stack tecnologico completo
- 16 modulos con descripcion
- 3 roles de usuario con accesos
- Estructura de carpetas
- Requisitos previos e instrucciones de instalacion
- 10 variables de entorno documentadas
- 10 cron jobs con frecuencia y descripcion
- Instrucciones de deploy
- Links a documentacion adicional

### CONTRIBUTING.md incluye

- Flujo de ramas (main → develop → feature/ → hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:)
- Convencion de nombres de ramas
- 5 reglas (no push directo, no credenciales, no temporales, no destructivos)
- Proceso de revision con pipeline CI/CD

### .env.example incluye

- Variables de entorno para BD principal y readonly
- API Keys de email (SendGrid/SMTP2Go), OpenAI, Anthropic
- Tokens internos (APP_API_KEY, CRON_TOKEN)
- Ruta de uploads y cache

---

## 5. RAMAS DE TRABAJO

### Estructura creada

```
main          ← Produccion. Solo codigo validado y estable.
develop       ← Integracion. Aqui se unen los cambios antes de ir a main.
feature/xxx   ← Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    ← Correcciones urgentes. Se crean desde main.
```

### Estado actual

| Rama | Estado | Commit actual |
|------|--------|---------------|
| main | Existente, en remoto | 5b5bd0e (dashboard superadmin) |
| develop | Creada, pendiente push a remoto | Mismo commit que main |
| cycloid | Legacy — sera reemplazada por develop | Mismo commit que main |

### Proteccion de ramas (pendiente en Gitea)

- **main:** protegida, requiere PR, no push directo
- **develop:** protegida, requiere PR desde feature/

### Flujo de trabajo

- Nueva funcionalidad: `develop` → `feature/nombre` → PR a `develop` → PR a `main`
- Hotfix urgente: `main` → `hotfix/nombre` → PR a `main` + PR a `develop`

---

## 6. PIPELINES CI/CD

### Plataforma: Gitea con Gitea Runner (act_runner)

### Pipeline 1: Validar y Deploy a Dev/QA

**Archivo:** `.gitea/workflows/validate-and-deploy-qa.yml`
**Trigger:** Push/PR a develop o feature/*

```
git push → Gitea → Runner → Tests + Trivy + Semgrep → Deploy SSH → LXC (Dev/QA)
```

| Job | Que hace | Bloquea si falla |
|-----|---------|-----------------|
| test | `php -l` en todos los .php de app/ | Si |
| trivy | Escaneo de vulnerabilidades en dependencias (HIGH/CRITICAL) | Si |
| semgrep | Analisis estatico de seguridad (reglas PHP + secrets + security-audit) | Si |
| secrets-scan | Busca API keys hardcodeadas (SendGrid, OpenAI, Anthropic, DB) | Si |
| deploy-qa | SSH al LXC Dev/QA y ejecuta deploy.sh | Solo en push a develop |

### Pipeline 2: Cutover a Produccion

**Archivo:** `.gitea/workflows/cutover-production.yml`
**Trigger:** Push a main (despues de merge de PR desde develop)

```
PR develop → main → Validacion → Trivy → Semgrep → Deploy SSH → LXC Produccion (Hetzner)
                                                                → Verificacion post-deploy
```

| Job | Que hace |
|-----|---------|
| validate | Sintaxis PHP + busqueda de credenciales |
| trivy | Escaneo vulnerabilidades (paralelo con semgrep) |
| semgrep | Analisis estatico seguridad (paralelo con trivy) |
| deploy-production | SSH al Hetzner + deploy.sh + verificacion HTTP post-deploy |

**Todo por pipeline, nada manual.**

### Secrets necesarios en Gitea

**Para Dev/QA:** QA_HOST, QA_USER, QA_SSH_KEY, QA_PATH
**Para Produccion:** PROD_HOST, PROD_USER, PROD_SSH_KEY, PROD_PATH

### Flujo completo

```
feature/xxx → push → Validacion → PR a develop → Validacion → merge
                                                                 ↓
                                          Deploy automatico a LXC Dev/QA
                                                                 ↓
                                              Pruebas en QA (manuales o auto)
                                                                 ↓
                                          PR develop → main → Validacion → merge
                                                                             ↓
                                                     Cutover automatico a Hetzner LXC
                                                                             ↓
                                                          Verificacion post-deploy
                                                                             ↓
                                                              EN PRODUCCION
```

---

## 7. ORGANIZACION DEL REPOSITORIO

### Estado del repositorio

| Aspecto | Estado actual | Accion |
|---------|--------------|--------|
| Visibilidad | PUBLICO en GitHub | Migrar a Gitea privado |
| .gitignore | Actualizado (excluye tmp, basura, .claude) | OK |
| .env.example | Creado con todas las variables | OK |
| Archivos basura | 15+ .txt, tmp_*.php, stackdump trackeados | Pendiente limpieza |

### Archivos basura trackeados en git (pendiente limpieza)

**Archivos de notas/bocetos (no son parte del sistema):**
- z_botiquin.txt, z_gabinetes.txt, z_comunicaciones.txt, z_limpieza.txt, z_ocurrencia.txt, z_plagas.txt, z_plandeemergencia.txt, z_recursosseguridad.txt, z_residuos.txt, z_responsabilidadessst.txt, z_aguapotable.txt, z_asistentes.txt, z_dotacion_vigilante.txt, z_hvbrigadista.txt, z_kpis.txt, y_appscriptbrigadista.txt, Z_PLANSANEAMIENTO.TXT, ZZ_PASCRIPTEVSIMULACTRO.txt

**Scripts temporales:**
- tmp_actas.php, tmp_describe.php, tmp_insert_compromisos.php, tmp_query.php

**Otros:**
- bash.exe.stackdump
- composer-setup.php (3 MB)

**15 archivos .md sueltos en raiz** que deberian moverse a `docs/`

### Archivos CSV que SI deben quedarse (usados por Libraries)

- PTA2026.csv (WorkPlanLibrary)
- capacitaciones ph.csv (TrainingLibrary)
- csvevaluacionestandaresminimosph.csv (StandardsLibrary)

---

## 8. HALLAZGOS CRITICOS Y ACCIONES PENDIENTES

### Prioridad CRITICA

| # | Accion | Responsable |
|---|--------|-------------|
| 1 | Hacer repo privado o migrar a Gitea | Consultor/Cliente |
| 2 | Rotar TODAS las API Keys y passwords de BD | Cliente |
| 3 | Eliminar `app/SQL/query_pta_indicadores.php` (password hardcodeado) | Cliente |

### Prioridad ALTA

| # | Accion | Responsable |
|---|--------|-------------|
| 4 | Push de rama develop al remoto | Cliente |
| 5 | Configurar proteccion de ramas en Gitea | Consultor |
| 6 | Configurar secrets en Gitea para pipelines | Consultor |
| 7 | Migrar SendGrid → SMTP2Go (15+ archivos) | Cliente |

### Prioridad MEDIA

| # | Accion | Responsable |
|---|--------|-------------|
| 8 | Limpiar archivos basura del repo (commit de limpieza) | Cliente |
| 9 | Mover 15 .md sueltos de raiz a docs/ | Cliente |
| 10 | Centralizar email en clase EmailService | Cliente |
| 11 | Centralizar OpenAI en un servicio unico | Cliente |

---

*Documento generado el 2026-04-05. Preparado como entregable del proceso de hardening del repositorio enterprisesstph.*
