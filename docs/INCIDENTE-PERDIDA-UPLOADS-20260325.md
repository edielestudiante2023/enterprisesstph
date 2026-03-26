# INCIDENTE: Pérdida de archivos de uploads — 2026-03-25

## Resumen

El 25 de marzo de 2026 a las 17:45 (hora Colombia), se ejecutó el comando `git clean -fd` en el servidor de producción como parte de un deploy. Este comando **eliminó permanentemente** todas las carpetas dinámicas dentro de `public/uploads/`, incluyendo la documentación completa de ~55 clientes.

**Estado actual: RECUPERADO** — 5,170 archivos restaurados desde Google Takeout el 2026-03-26.

## Cronología

| Hora | Evento |
|------|--------|
| 17:24 | `git pull origin main` falla por archivos unmerged |
| 17:24 | Se intenta `git checkout -- . && git clean -fd && git pull origin main` |
| 17:44 | Falla por conflictos de merge. Se intenta `git merge --abort` |
| 17:45 | Se ejecuta: `git reset HEAD . && git checkout -- . && git clean -fd && git pull origin main` |
| 17:45 | **`git clean -fd` elimina TODAS las carpetas no trackeadas de uploads** |
| 17:46 | `git pull origin main` — Already up to date |
| ~19:00 | Se detecta el problema al intentar acceder a un PDF de cliente |
| 19:04 | Se inicia exportación de Google Takeout |
| 20:29 | Takeout descargado (9.5 GB, 65 archivos .mbox) |
| 20:30 | Se crea endpoint `bulkUpload` + script `recarga_desde_takeout.php` |
| 21:21 | Primera prueba exitosa del endpoint |
| 21:40 | Inicia recarga masiva desde Takeout |
| 05:25 (mar 26) | Recarga completada: 5,170 archivos subidos |
| 05:32 (mar 26) | Duplicados eliminados (853), limpieza de .env |

## Comando que causó la pérdida

```bash
git reset HEAD . && git checkout -- . && git clean -fd && git pull origin main
```

**`git clean -fd`** elimina archivos y directorios no trackeados por git. Como las carpetas de uploads de clientes se creaban dinámicamente en producción y NO estaban en `.gitignore`, git las consideraba "basura" y las eliminó.

## Qué se perdió

### Carpetas de NIT de clientes (~55 carpetas)
Cada carpeta contenía PDFs de inspecciones, actas de visita, reportes de capacitación, certificados, y documentos subidos manualmente por consultores.

**Cantidad de registros en BD apuntando a archivos perdidos: 2,467** (de 2,479 total, solo 10 archivos sobrevivieron + 2 links de Google Drive)

Desglose por tipo de archivo:
- Documentos subidos manualmente (desde AppSheets original): ~3,062
- Actas de visita: 24
- Informes de avance: 23
- KPIs: 16
- Reportes de capacitación: 13
- Contratos: 12
- Asistencias/Responsabilidades: 18
- Contingencias: 6
- Plan saneamiento: 5
- Inspecciones (locativa, extintores, señalización, botiquín, etc.): 15
- Auditorías zona residuos: 3
- Dotaciones: 3
- Otros: varios

### Otras carpetas eliminadas
- `public/uploads/contratos/` — PDFs de contratos generados por TCPDF
- `public/uploads/firmas/` — Imágenes PNG de firmas electrónicas de contratos
- `public/uploads/inspecciones/` — Fotos, firmas y PDFs temporales de inspecciones
- `public/uploads/informe-avances/` — PDFs e imágenes de soporte de informes
- `public/uploads/matrices/` — Archivos Excel de matrices personalizadas
- `public/uploads/planillas-seguridad-social/`
- `public/uploads/reportes/`
- `public/uploads/imagenesplanemergencias/`

### Lo que NO se perdió
- Logos del sistema (trackeados en git): `logoenterprisesstblancoslogan.png`, etc.
- Fotos/firmas de consultores (trackeados en git)
- Logos de clientes (trackeados en git)
- **Todos los datos en la base de datos** — intactos

## Causa raíz

1. `public/uploads/` **NO estaba en `.gitignore`** — solo `writable/uploads/` lo estaba
2. Las carpetas de NIT se creaban dinámicamente en producción por la app
3. Al no estar trackeadas ni ignoradas, `git clean -fd` las trató como basura
4. El deploy manual no tenía protección contra este escenario

## Intentos de recuperación directa (fallidos)

| Método | Resultado |
|--------|-----------|
| `extundelete` | No funciona con filesystem montado |
| `photorec` | Requiere terminal interactivo (ncurses) |
| `debugfs` | Inodes de directorios borrados están vacíos |
| BaoTa backup | Backup de 0 bytes (nunca se completó) |
| `git stash` | Solo contiene archivos trackeados, no uploads dinámicos |

## Recuperación ejecutada

### Fuente: Google Takeout (Gmail)
Los documentos originales estaban en carpetas de Gmail organizadas por conjunto residencial (etiquetas). Se exportó via Google Takeout.

**Proceso ejecutado:**
1. Exportación de Gmail via Google Takeout — 9.5 GB, 65 archivos .mbox
2. Extracción del zip en local
3. Script `tools/recarga_desde_takeout.php` procesa cada .mbox:
   - Parsea emails y extrae adjuntos (PDF, JPG, PNG)
   - Mapea nombre del conjunto (etiqueta Gmail) → `id_cliente` via BD producción
   - Determina tipo de documento desde subject con tabla de 55 convenciones AppSheets
   - Para documentos sin formato AppSheets: match parcial o genérico (id_report_type=22)
   - Sube via POST a `/addReportPost` con `fecha_original` del email
   - No envía emails (DISABLE_REPORT_EMAILS=true)
4. Limpieza de duplicados post-recarga

**Resultados:**

| Métrica | Valor |
|---------|-------|
| Emails procesados | 4,185 |
| Archivos subidos OK | 5,170 |
| Errores (timeouts Cloudflare) | 2 |
| Duplicados eliminados | 853 |
| **Registros finales en tbl_reporte** | **2,074** |
| Mbox excluidos (no son clientes) | 3 (AFIANCOL, MORELLA, CLIENTES NUEVOS) |

**Archivos que NO se recuperaron:**
- 2 archivos por timeout de Cloudflare (AUDITORIA PROVEEDOR ASEO HELICONIA, GR 046.JPG)
- Fotos originales de inspecciones tomadas en campo (no estaban en Gmail)
- Firmas electrónicas PNG de contratos (no estaban en Gmail)
- PDFs generados por el módulo de inspecciones nativo del sistema (pendiente regeneración via `php spark regenerar:pdfs`)

### Regeneración de PDFs nativos (pendiente)
Los PDFs generados por el módulo `/inspecciones` (DOMPDF) se pueden regenerar desde la BD. El comando `php spark regenerar:pdfs` está creado en `app/Commands/RegenerarPdfs.php` para esto.

## Medidas preventivas implementadas

### 1. Migración de uploads a `writable/soportes-clientes/`
Los uploads dinámicos ahora van a `WRITEPATH . 'soportes-clientes/'` en vez de `public/uploads/`.
- Está dentro del proyecto (accesible por PHP, sin restricción open_basedir)
- Protegido por `.gitignore` (`writable/soportes-clientes/`)
- `writable/` ya estaba ignorado por convención CI4
- Servido via `FileServerController` en ruta `serve-file/`

### 2. `.gitignore` actualizado
```
public/uploads/*/
!public/uploads/.gitkeep
writable/soportes-clientes/
```

### 3. Script de deploy seguro: `deploy.sh`
Reemplaza el deploy manual. Solo hace `git stash` + `git pull` + `git stash pop`. **NUNCA ejecuta `git clean`.**

### 4. `UPLOADS_PATH` como constante centralizada
Definida en `app/Config/Constants.php`:
```php
define('UPLOADS_PATH', WRITEPATH . 'soportes-clientes/');
define('UPLOADS_URL_PREFIX', 'serve-file');
```
44 controladores migrados para usar `UPLOADS_PATH` en vez de rutas hardcodeadas.

### 5. FileServerController
Controlador que sirve archivos desde `writable/soportes-clientes/` via ruta `serve-file/`. Requiere sesión activa.

### 6. Endpoint `addReportPost` acepta `fecha_original`
Para preservar fechas originales en recargas masivas, sin hardcodear `date('Y-m-d H:i:s')`.

## Archivos creados/modificados

| Archivo | Propósito |
|---------|-----------|
| `deploy.sh` | Deploy seguro sin git clean |
| `app/Config/Constants.php` | UPLOADS_PATH, UPLOADS_URL_PREFIX |
| `app/Controllers/FileServerController.php` | Servir archivos desde writable/ |
| `app/Controllers/ReportController.php` | bulkUpload() + fecha_original en addReportPost |
| `tools/recarga_desde_takeout.php` | Procesar mbox de Takeout y subir archivos |
| `tools/regenerate_pdfs.php` | Generar script de regeneración de PDFs |
| `tools/restore_from_mbox.php` | Extractor genérico de adjuntos mbox |
| `app/SQL/migrate_uploads_urls.php` | Migrar URLs en BD de uploads/ a serve-file/ |
| `app/Commands/LimpiarReportes404.php` | Spark command para limpiar registros sin archivo |
| `app/Commands/RegenerarPdfs.php` | Spark command para regenerar PDFs de inspecciones |
| 44 controladores de inspecciones/reportes | Migrados a UPLOADS_PATH |

## Lecciones aprendidas

1. **NUNCA ejecutar `git clean -fd` en un servidor de producción** sin verificar qué archivos se eliminarán primero (`git clean -fdn` para dry-run)
2. **Los uploads de usuarios SIEMPRE deben estar en una ruta protegida** — `writable/` con `.gitignore`
3. **Los backups deben verificarse** — el backup de BaoTa tenía 0 bytes y nunca se detectó
4. **El deploy debe ser un script idempotente** (`deploy.sh`), no comandos manuales
5. **Google Takeout es una fuente de recuperación viable** si los documentos se enviaron por email
6. **Las fechas de los registros deben preservar la fecha original**, no la fecha de recarga
