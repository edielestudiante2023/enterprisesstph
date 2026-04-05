# AUDITORIA DEL SERVIDOR — enterprisesstph

**Fecha de auditoría:** 2026-04-04
**Dominio:** phorizontal.cycloidtalent.com
**Ruta en servidor:** /www/wwwroot/phorizontal/enterprisesstph/
**Tamaño en disco:** 4.0 GB

> Info general del servidor (SO, RAM, nginx, SSL, servicios) → ver [AUDITORIA-SERVIDOR-GENERAL.md](AUDITORIA-SERVIDOR-GENERAL.md)

---

## 1. Stack del aplicativo

| Campo | Valor |
|-------|-------|
| **Framework** | CodeIgniter 4 |
| **PHP** | 8.2 (CLI) / 8.4 (FPM) |
| **Base de datos** | DigitalOcean Managed MySQL (remota, SSL required) |
| **BD nombre** | propiedad_horizontal |
| **BD usuario** | cycloid_userdb + cycloid_readonly (portal cliente) |
| **Email** | SendGrid API v3 (cURL directo) |
| **PDF** | TCPDF (contratos) + DOMPDF 3.0.0 (certificados/actas) |
| **IA** | OpenAI (gpt-4o-mini) — Chat Otto |
| **PWA** | Módulo inspecciones (manifest + service worker) |

---

## 2. Cron jobs

| Frecuencia | Comando | Descripción |
|------------|---------|-------------|
| Lunes 8 AM | `curl localhost/.../cron/send-emails` | Envío de emails programados |
| Lunes 7 AM | `php spark contratos:resumen-semanal` | Resumen semanal de contratos |
| Diario 7 AM | `php spark auditoria:revisar-visitas-diario` | Auditoría de visitas del día anterior |
| L-V 7 AM | `php spark auditoria:recordatorio-sin-agendar` | Recordatorio clientes sin agendar |
| Diario 7 AM | `php spark actas:notificaciones` | Notificaciones de actas (firma, alertas, tareas) |
| Día 1 y 16, 8 AM | `php spark pendientes:recordatorio` | Recordatorio de pendientes quincenal |
| Diario 5 PM | `php spark inspecciones:resumen-pendientes` | Inspecciones en borrador o sin firma |
| Diario 3 PM | `php spark seguimiento:agenda-diario` | Email a clientes difíciles de contactar |
| Diario 3 PM | `php spark visitas:recordatorio` | Recordatorio preparativo visitas |
| Diario 7 AM | `php spark firmas:protocolo-alturas --reporte` | Reporte firmas protocolo alturas |

---

## 3. Deploy actual

**Script:** `deploy.sh`

```
1. Verifica directorio correcto (busca archivo spark)
2. git stash (si hay cambios locales)
3. git pull origin main
4. git stash pop (si hubo stash)
```

**Ejecutar:** `ssh root@66.29.154.174 "cd /www/wwwroot/phorizontal/enterprisesstph && bash deploy.sh"`

**Prohibiciones:** `git clean -fd`, `git checkout -- .`, `git reset --hard`

---

## 4. Archivo .env en producción

| Variable | Tipo | Observación |
|----------|------|-------------|
| `CI_ENVIRONMENT` | Entorno | production |
| `app.baseURL` | URL | https://phorizontal.cycloidtalent.com/ |
| `database.default.*` | BD principal | Host DigitalOcean, SSL required |
| `readonly.*` | BD readonly | Portal cliente, usuario cycloid_readonly |
| `SENDGRID_API_KEY` | API Key | Hardcodeada en .env |
| `OPENAI_API_KEY` | API Key | Hardcodeada en .env |
| `CRON_TOKEN` | Token | cycloid2026weekly |
| `APP_API_KEY` | Token interno | Hardcodeada en .env |
| `OPENAI_MODEL` | Config | gpt-4o-mini |
| `UPLOADS_PATH` | Ruta | /www/soportes-clientes/ |

---

## 5. Archivos fuera del repositorio

| Ruta | Tamaño | Contenido |
|------|--------|-----------|
| `/www/soportes-clientes/` | 2.9 MB | Uploads dinámicos (firmas_consultores) |
| `/www/ca/ca-certificate_cycloid.crt` | — | Certificado SSL para conexión a BD |

---

## 6. Archivos basura en raíz del proyecto

Archivos que NO deberían estar en la raíz del repo:

- **~15 archivos .txt** temporales: `z_botiquin.txt`, `z_gabinetes.txt`, `z_comunicaciones.txt`, etc.
- **Archivos .csv**: `capacitaciones ph.csv`, `PTA2026.csv`, `csvevaluacionestandaresminimosph.csv`
- **Archivos .sql sueltos**: `tbl_clientes.sql`, `migration_safe_rename.sql`, `EJECUTAR_AHORA_rename.sql`
- **Binario composer**: 3 MB (debería usarse via PATH, no como archivo local)
- **Scripts temporales**: `tmp_actas.php`, `tmp_describe.php`, `tmp_insert_compromisos.php`, `tmp_query.php`
- **Stackdump**: `bash.exe.stackdump`
- **Documentación .md suelta en raíz**: ~10 archivos .md que deberían estar en `docs/`

---

## 7. Hallazgos específicos de enterprisesstph

| # | Hallazgo | Severidad |
|---|----------|-----------|
| 1 | API Keys hardcodeadas en .env (SendGrid, OpenAI, BD passwords) | CRITICA |
| 2 | Archivos temporales y basura en raíz del proyecto | MEDIA |
| 3 | Binario composer de 3 MB en el repo | BAJA |
| 4 | Scripts tmp_*.php con posible acceso a BD en producción | MEDIA |
| 5 | Documentación .md dispersa entre raíz y docs/ | BAJA |
