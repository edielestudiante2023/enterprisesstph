# PROMPT PARA AGENTE: Recarga masiva de documentos desde Gmail via endpoint addReportPost

Copia y pega esto en un nuevo chat de Claude Code:

---

## Contexto

El 2026-03-25 se perdieron TODOS los archivos PDF de clientes en producción (3,238+ documentos de 55 copropiedades). Los datos en la BD se borraron con el comando `php spark reportes:limpiar-404`. Los archivos originales están en Gmail, organizados por carpetas de conjuntos residenciales.

Lee `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` para el contexto completo.

**NO preguntes nada. Solo ejecuta.**

## Cómo funcionaba el sistema antes

### Flujo original (n8n workflow "PDF SST - PH")

1. **AppSheets** generaba un PDF de inspección/documento SST
2. AppSheets enviaba el PDF por email a Gmail (sender: `noreply@appsheet.com`)
3. El **subject** del email tenía este formato:
   ```
   NOMBRE_DOCUMENTO__NOMBRE_CLIENTE___FECHA
   ```
   Ejemplo: `INSPECCION LOCATIVA__CONJUNTO RESIDENCIAL EL ZORZAL___2025-04-09`
4. **n8n** leía los emails no leídos, descargaba el adjunto PDF
5. n8n parseaba el subject para extraer: nombre_documento, nombre_cliente, fecha
6. n8n consultaba una Google Sheet con mapeo de documentos→(id_report_type, id_detailreport) y clientes→id_cliente
7. n8n hacía `POST /addReportPost` con el PDF como `multipart-form-data` + query params:
   - `id_cliente`
   - `id_report_type`
   - `id_detailreport`
   - `titulo_reporte` (el subject completo)
   - `estado` = "CERRADO"
   - `archivo` = el PDF binario
8. El endpoint guardaba el PDF en `UPLOADS_PATH/{nit}/` y creaba registro en `tbl_reporte`

### Tabla de convenciones (nombre_documento → id_report_type, id_detailreport)

```
nombre_documento                                         id_report_type  id_detailreport
INSPECCION LOCATIVA                                      1               16
ACTA DE VISITA                                           6               9
MATRIZ VULNERABILIDAD                                    11              11
CERTIFICADO DE FUMIGACION                                13              16
PLAN DE EMERGENCIAS                                      11              10
CERTIFICADO LAVADO DE TANQUES                            14              14
DOTACION ASEADORAS                                       3               7
DOTACION TODERO                                          3               6
DOTACION VIGILANTES                                      4               8
EVALUACION DE CONTRATISTA                                4               9
RESULTADOS CALIFICACION DE ESTÁNDARES MINIMOS            9               1
INFORME A LA ALTA DIRECCION                              2               1
INFORME DE CIERRE DE MES                                 10              1
PLAN DE SANEAMIENTO BASICO                               13              20
MANEJO DE RESIDUOS Y PLAGAS                              13              20
CERTIFICADO 50 HORAS                                     8               23
ACTA CAPACITACION                                        7               1
REPORTE DE CAPACITACION                                  7               1
RESPONSABILIDADES SST                                    7               1
CONTRATO SG-SST                                          19              20
ACUERDO DE CONFIDENCIALIDAD                              19              20
INSPECCION DE BOTIQUIN                                   1               3
INSPECCION ZONA DE RESIDUOS                              1               15
INSPECCION EXTINTORES                                    1               2
INSPECCION GABINETES CONTRA INCENDIO                     1               4
RECORRIDO DE INSPECCION                                  1               19
INSPECCION RECURSOS PARA LA SEGURIDAD                    11              5
OCURRENCIA DE PELIGROS                                   11              12
INSPECCION EQUIPOS DE COMUNICACIONES                     1               21
SEGURIDAD SOCIAL                                         6               9
SOPORTE LAVADO DE TANQUES                                13              20
SOPORTE MANEJO DE PLAGAS                                 13              20
SOPORTE DESRATIZACION                                    13              20
PUBLICACION POLITICA Y OBJETIVOS                         17              23
APROBACION EVALUACION INICIAL REP LEGAL                  17              23
APROBACION PLAN DE TRABAJO REP LEGAL                     17              23
HOJA DE VIDA BRIGADISTA                                  11              10
DOCUMENTOS DEL RESPONSABLE SST                           21              20
PLAN DE EMERGENCIAS FAMILIAR                             11              10
EVALUACION SIMULACRO                                     11              10
PREPARACION GUION SIMULACRO                              11              10
INSPECCION SENALIZACION                                  1               21
CONSTANCIA DE PARTICIPACION SIMULACRO                    11              10
AUDITORIA PROVEEDOR DE ASEO                              3               9
AUDITORIA PROVEEDOR DE VIGILANCIA                        4               9
AUDITORIA OTROS PROVEEDORES                              12              9
APROBACION PLAN DE CAPACITACION REP LEGAL                17              23
PROGRAMA DE LIMPIEZA Y DESINFECCION                      13              20
PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS          13              20
PROGRAMA DE CONTROL INTEGRADO DE PLAGAS                  13              20
PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE     13              20
KPI PROGRAMA DE LIMPIEZA Y DESINFECCION                  13              20
KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS      13              20
KPI PROGRAMA DE CONTROL INTEGRADO DE PLAGAS              13              20
KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE 13              20
```

### Tabla de clientes (nombre_cliente → id_cliente, nit_cliente)

Consulta la BD local: `SELECT id_cliente, nit_cliente, nombre_cliente FROM tbl_clientes ORDER BY nombre_cliente;`

## Tarea 1: Modificar endpoint `addReportPost` para aceptar fecha original

El endpoint actual en `app/Controllers/ReportController.php` método `addReportPost()` usa `date('Y-m-d H:i:s')` como `created_at`. Necesita aceptar un parámetro opcional `fecha_original` que, si viene, se use como `created_at` y `updated_at` en vez de la fecha actual.

Esto es para que al re-cargar los documentos, queden con la fecha del email original, no con la fecha de hoy.

```php
// En addReportPost():
$fechaOriginal = $this->request->getGet('fecha_original') ?? $this->request->getPost('fecha_original');
$created_at = $fechaOriginal ? date('Y-m-d H:i:s', strtotime($fechaOriginal)) : date('Y-m-d H:i:s');
```

También agregar flag para no enviar email SendGrid durante recarga masiva:
```php
$skipEmail = $this->request->getGet('skip_email') ?? $this->request->getPost('skip_email');
// ... y antes del envío de email:
if (!$skipEmail) {
    // enviar email normal
}
```

Lee el ReportController.php completo para entender la estructura antes de modificar.

## Tarea 2: Crear script PHP que procese el Google Takeout y re-cargue via addReportPost

El Takeout de Gmail descarga archivos `.mbox`. Pero más importante: el Takeout organiza por etiquetas de Gmail, y las etiquetas son los nombres de los conjuntos residenciales.

Crear: `tools/recarga_desde_takeout.php`

### Lógica del script

1. **Recibir ruta del directorio Takeout descomprimido** (argumento CLI)
2. **Leer cada archivo .mbox** — cada uno corresponde a una etiqueta de Gmail (carpeta de un conjunto)
3. **Para cada email en el mbox:**
   a. Parsear el subject con formato `NOMBRE_DOCUMENTO__NOMBRE_CLIENTE___FECHA`
   b. Extraer la fecha del email (header `Date:`) como `fecha_original`
   c. Extraer el adjunto PDF
   d. Buscar el mapeo en la tabla de convenciones hardcodeada (nombre_documento → id_report_type, id_detailreport)
   e. Buscar el cliente en la BD (nombre_cliente → id_cliente)
   f. Hacer `POST /addReportPost` con cURL al servidor:
      - `id_cliente`, `id_report_type`, `id_detailreport`, `titulo_reporte`, `estado=CERRADO`
      - `fecha_original` = fecha del email
      - `skip_email=1` para no enviar notificación
      - `archivo` = el PDF como multipart
   g. Registrar resultado (OK/ERROR) en log
4. **Al final:** mostrar resumen de cuántos se cargaron, cuántos fallaron

### Manejo de emails que NO son de AppSheets

No todos los emails en las carpetas de Gmail vienen de AppSheets. Algunos pueden ser emails normales con adjuntos PDF que un consultor subió manualmente. Para esos:
- Si el subject NO tiene formato `__` y `___`, intentar match fuzzy del nombre del documento contra la tabla de convenciones
- Si no se puede determinar el tipo de documento, usar `id_report_type=22, id_detailreport=20` como genérico ("DOCUMENTO GENERAL")
- Siempre extraer y subir el adjunto PDF si existe

### Autenticación

El script necesita estar autenticado para hacer POST a `addReportPost`. Opciones:
1. Ejecutar desde el servidor y hacer el POST a localhost
2. O crear un endpoint especial `addReportBulk` que acepte un token de API en vez de sesión

La opción más segura: crear un endpoint temporal `POST /api/bulk-report-upload` con un token hardcodeado que solo funcione cuando `BULK_UPLOAD_TOKEN` está definido en `.env`. Después de la recarga, se quita el token del `.env`.

```php
// En Routes.php:
$routes->post('api/bulk-report-upload', 'ReportController::bulkUpload');

// En ReportController:
public function bulkUpload()
{
    $token = $this->request->getHeaderLine('X-Bulk-Token');
    if ($token !== env('BULK_UPLOAD_TOKEN')) {
        return $this->response->setStatusCode(403)->setJSON(['error' => 'Token inválido']);
    }
    // ... misma lógica de addReportPost pero con fecha_original y sin email
}
```

## Tarea 3: Manejar documentos que no vienen de AppSheets

Además de los emails de `noreply@appsheet.com`, hay documentos que los consultores enviaron directamente al Gmail y que se organizaron en carpetas. Estos emails:
- NO tienen el formato de subject `NOMBRE_DOC__CLIENTE___FECHA`
- Pueden tener cualquier subject
- Lo importante es: el adjunto PDF + la etiqueta/carpeta de Gmail = el cliente

Para estos, el script debe:
1. Extraer el adjunto
2. Determinar el cliente por la etiqueta del mbox (nombre de la carpeta = nombre del conjunto)
3. Usar el nombre del archivo PDF como `titulo_reporte`
4. Usar `id_report_type=22, id_detailreport=20` (genérico)
5. Usar la fecha del email como `fecha_original`

## Archivos clave que debes leer

1. `app/Controllers/ReportController.php` — endpoint addReportPost actual
2. `app/Config/Constants.php` — UPLOADS_PATH
3. `app/Config/Routes.php` — rutas existentes
4. `app/Models/ReporteModel.php` — modelo de reportes
5. `app/Models/ClientModel.php` — modelo de clientes
6. `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` — contexto del incidente

## Resultado esperado

1. Endpoint `addReportPost` modificado para aceptar `fecha_original` y `skip_email`
2. Endpoint `api/bulk-report-upload` temporal con autenticación por token
3. Script `tools/recarga_desde_takeout.php` que procese el .mbox y re-cargue todo
4. Al ejecutar, los documentos quedan en `/www/soportes-clientes/{nit}/` con fecha original
5. Los registros en `tbl_reporte` quedan con `created_at` = fecha del email original
6. No se envían emails a nadie durante la recarga

## NO preguntes nada. Lee el código, entiende el flujo, implementa, y muestra el resultado.

---
