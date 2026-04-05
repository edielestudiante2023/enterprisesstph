# INVENTARIO DE API KEYS — enterprisesstph

**Fecha de auditoría:** 2026-04-04

---

## Resumen de servicios externos

| Servicio | Variable | Método de carga | Archivos que la usan | Estado |
|----------|----------|-----------------|---------------------|--------|
| **SendGrid** | `SENDGRID_API_KEY` | `getenv()` / `env()` desde .env | 15+ archivos | Activa — por migrar a SMTP2Go |
| **OpenAI** | `OPENAI_API_KEY` | `getenv()` / `env()` desde .env | 7 archivos | Activa |
| **OpenAI Model** | `OPENAI_MODEL` | `env()` desde .env | 4 archivos | Config (gpt-4o-mini) |
| **Anthropic (Claude)** | `ANTHROPIC_API_KEY` | `getenv()` desde .env | 1 archivo (tools/) | Activa — solo herramienta interna |
| **APP_API_KEY** | `APP_API_KEY` | `env()` desde .env | 2 archivos (filtros) | Activa — token interno |
| **CRON_TOKEN** | `CRON_TOKEN` | `env()` desde .env | 1 archivo | Activa — protege endpoint cron |

---

## 1. SendGrid (SENDGRID_API_KEY)

**Propósito:** Envío de todos los correos transaccionales del sistema.
**Patrón de uso:** `new \SendGrid(getenv('SENDGRID_API_KEY'))` — SDK de SendGrid via cURL.
**Cuenta asociada:** Cuesta $61.000 COP/mes.
**Estado:** Activa, planificada para reemplazar por SMTP2Go ($0).

### Archivos que la usan (15+)

**Controladores:**
- `app/Controllers/ChatController.php:306` — email desde chat Otto
- `app/Controllers/AuthController.php:225` — recuperación de contraseña
- `app/Controllers/AdminDashboardController.php:577` — envío de credenciales
- `app/Controllers/EmailController.php:28` — envío general
- `app/Controllers/FirmaElectronicaController.php:226,340` — notificación firma contrato
- `app/Controllers/FirmaAlturasController.php:236,289` — notificación firma alturas
- `app/Controllers/UserController.php:288` — envío contraseña temporal

**Libraries:**
- `app/Libraries/ResumenPendientesNotificador.php:106` — resumen pendientes
- `app/Libraries/NotificadorVisita.php:382` — notificación visitas
- `app/Libraries/InspeccionEmailNotifier.php:46` — notificación inspecciones

**Commands (cron jobs):**
- `app/Commands/SeguimientoAgendaCron.php:87` — seguimiento agenda
- `app/Commands/ResumenContratosSemanal.php:48` — resumen contratos
- `app/Commands/RecordatorioSinAgendarCron.php:162` — recordatorio sin agendar
- `app/Commands/RecordatorioPendientesCron.php:148` — recordatorio pendientes
- `app/Commands/ProtocoloAlturas.php:120,181` — protocolo alturas
- `app/Commands/AuditoriaVisitasCron.php:125` — auditoría visitas

**Scripts:**
- `app/SQL/reenviar_evaluaciones_rapidas.php:23` — reenvío evaluaciones (usa `SG_KEY` env var)

---

## 2. OpenAI (OPENAI_API_KEY)

**Propósito:** IA generativa para chat Otto, generación de textos en contratos, sugerencias en PTA, análisis de pendientes, evaluaciones de capacitación.
**Patrón de uso:** cURL directo a `https://api.openai.com/v1/chat/completions`.
**Modelo configurado:** `gpt-4o-mini` (configurable via `OPENAI_MODEL`).
**Estado:** Activa.

### Archivos que la usan (7)

- `app/Controllers/ChatController.php:49` — Chat Otto (principal)
- `app/Controllers/ContractController.php:801` — generación texto contratos
- `app/Controllers/PtaClienteNuevaController.php:806` — sugerencias PTA con IA
- `app/Controllers/PendientesController.php:357` — análisis pendientes
- `app/Controllers/Inspecciones/AsistenciaInduccionController.php:752` — evaluación inducción
- `app/Controllers/Inspecciones/ReporteCapacitacionController.php:439` — reporte capacitación
- `app/Services/IADocumentacionService.php:14` — servicio de documentación IA

---

## 3. Anthropic / Claude (ANTHROPIC_API_KEY)

**Propósito:** Clasificación de PDFs con IA.
**Patrón de uso:** cURL directo a `https://api.anthropic.com/v1/messages`.
**Modelo:** `claude-haiku-4-5-20251001`.
**Estado:** Activa — solo en herramienta interna (tools/).

### Archivos que la usan (1)

- `tools/clasificar_pdfs.php:13` — clasificación automática de PDFs

---

## 4. APP_API_KEY (token interno)

**Propósito:** Autenticación de endpoints API internos (filtros de acceso).
**Patrón de uso:** `env('APP_API_KEY', '')` — validada en filtros.
**Estado:** Activa.

### Archivos que la usan (2)

- `app/Filters/ApiKeyFilter.php:26` — filtro de API key
- `app/Filters/AuthOrApiKeyFilter.php:30` — filtro auth o API key

---

## 5. CRON_TOKEN

**Propósito:** Proteger endpoints que se ejecutan via cron/curl.
**Patrón de uso:** `env('CRON_TOKEN', 'changeme')`.
**Valor actual:** `cycloid2026weekly`.
**Estado:** Activa.

### Archivos que la usan (1)

- `app/Controllers/ContractController.php:461,924` — endpoint de reporte semanal

---

## HALLAZGOS DE SEGURIDAD

### CRITICO: Credencial hardcodeada en código fuente

| Archivo | Problema |
|---------|----------|
| `app/SQL/query_pta_indicadores.php:5` | **Password de BD hardcodeado**: `AVNS_iDypWizlpMRwHIORJGG` (password anterior/diferente de BD) |
| `app/Controllers/UserController.php:288` | Fallback hardcodeado: `'SG.xxxxxx'` como API key por defecto (no funcional pero mala práctica) |

### ALERTA: Claves en .env (no en código, pero expuestas si repo fue público)

Las siguientes claves están en el `.env` de producción. Si el repositorio fue público en algún momento, **todas deben rotarse**:

| Variable | Acción recomendada |
|----------|-------------------|
| `SENDGRID_API_KEY` (SG.6jq...) | Rotar — se va a reemplazar por SMTP2Go de todas formas |
| `OPENAI_API_KEY` (sk-proj-Efj...) | **ROTAR INMEDIATAMENTE** en platform.openai.com |
| `database.default.password` (AVNS_MR2...) | Rotar en DigitalOcean |
| `readonly.password` (CycloidPortal2026!) | Rotar en DigitalOcean |
| `APP_API_KEY` (sst_4f4...) | Regenerar |
| `CRON_TOKEN` (cycloid2026weekly) | Cambiar |

### BUENAS PRACTICAS ENCONTRADAS

- La mayoría de archivos usan `getenv()` o `env()` correctamente — no hardcodean
- Los scripts en `tools/` usan `getenv('DB_PROD_PASS')` — se pasa como variable de entorno al ejecutar
- Hay validación "API key no configurada" en la mayoría de usos

---

## RECOMENDACIONES PARA MIGRACION

1. **SendGrid → SMTP2Go:** Cambiar en 15+ archivos. Patrón repetitivo — candidato a crear una clase `EmailService` centralizada
2. **OpenAI:** Mantener, pero centralizar en un servicio (ya existe `IADocumentacionService` pero no todos lo usan)
3. **Anthropic:** Solo 1 archivo, mantener como está
4. **Eliminar:** `app/SQL/query_pta_indicadores.php` (tiene password hardcodeado y es script temporal)
5. **Crear `.env.example`** con todas las variables sin valores reales
