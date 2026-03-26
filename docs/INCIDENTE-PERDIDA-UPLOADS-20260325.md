# INCIDENTE: Pérdida de archivos de uploads — 2026-03-25

## Resumen

El 25 de marzo de 2026 a las 17:45 (hora Colombia), se ejecutó el comando `git clean -fd` en el servidor de producción como parte de un deploy. Este comando **eliminó permanentemente** todas las carpetas dinámicas dentro de `public/uploads/`, incluyendo la documentación completa de ~55 clientes.

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

## Comando que causó la pérdida

```bash
git reset HEAD . && git checkout -- . && git clean -fd && git pull origin main
```

**`git clean -fd`** elimina archivos y directorios no trackeados por git. Como las carpetas de uploads de clientes se creaban dinámicamente en producción y NO estaban en `.gitignore`, git las consideraba "basura" y las eliminó.

## Qué se perdió

### Carpetas de NIT de clientes (~55 carpetas)
Cada carpeta contenía PDFs de inspecciones, actas de visita, reportes de capacitación, certificados, y documentos subidos manualmente por consultores.

**Cantidad de registros en BD apuntando a archivos perdidos: 3,238**

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

## Intentos de recuperación

| Método | Resultado |
|--------|-----------|
| `extundelete` | No funciona con filesystem montado |
| `photorec` | Requiere terminal interactivo (ncurses) |
| `debugfs` | Inodes de directorios borrados están vacíos |
| BaoTa backup | Backup de 0 bytes (nunca se completó) |
| DigitalOcean snapshots | N/A (servidor no es DO) |
| `git stash` | Solo contiene archivos trackeados, no uploads dinámicos |

## Plan de recuperación

### Fuente 1: Google Takeout (Gmail)
Los documentos originales de clientes (antes del sistema actual, subidos desde AppSheets) están en carpetas de Gmail organizadas por conjunto residencial. Se exportó via Google Takeout el 2026-03-25 a las 19:04.

**Proceso:**
1. Descargar archivo .mbox del Takeout
2. Extraer adjuntos con script PHP
3. Organizar por NIT usando mapeo nombre_cliente → nit_cliente
4. Subir a nueva ubicación segura

### Fuente 2: Regeneración desde BD
Los PDFs generados por el sistema (actas, inspecciones, certificados, informes, contratos) se pueden regenerar porque los datos fuente están intactos en la BD.

**Regenerables:**
- Actas de visita (tbl_acta_visita)
- Todas las inspecciones (tbl_inspeccion_*)
- Reportes de capacitación (tbl_reporte_capacitacion)
- Informes de avance (tbl_informe_avances)
- Contratos (tbl_contratos)
- Certificados de servicio (tbl_certificado_servicio)
- KPIs, programas, contingencias, etc.

**NO regenerables (dependen de Gmail/backup):**
- Fotos originales tomadas en campo durante inspecciones
- Firmas electrónicas PNG de contratos
- Documentos subidos manualmente (planillas, certificados de terceros)
- Los ~3,062 documentos del sistema anterior (AppSheets)

## Medidas preventivas implementadas

### 1. `.gitignore` actualizado
```
public/uploads/*/
!public/uploads/.gitkeep
```

### 2. Script de deploy seguro: `deploy.sh`
Reemplaza el deploy manual. Solo hace `git stash` + `git pull` + `git stash pop`. NUNCA ejecuta `git clean`.

### 3. Migración de uploads fuera de git
Nueva ubicación: `/www/soportes-clientes/` — completamente fuera del repositorio git.

### 4. FileServerController
Controlador que sirve archivos desde la nueva ubicación, ya que no estarán en `public/`.

## Lecciones aprendidas

1. **NUNCA sugerir `git clean -fd` en un servidor de producción** sin verificar qué archivos se eliminarán primero (`git clean -fdn` para dry-run)
2. **Los uploads de usuarios SIEMPRE deben estar fuera del repo git** o en una ruta explícitamente protegida
3. **Los backups deben verificarse** — el backup de BaoTa tenía 0 bytes
4. **El deploy debe ser un script idempotente**, no comandos manuales que pueden variar
