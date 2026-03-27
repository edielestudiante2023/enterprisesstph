# PROMPT: Clasificar 4,070 PDFs con IA (Claude API) — extraer tipo, fecha y cliente

---

## Contexto

Tenemos 4,070 PDFs extraídos de un Google Takeout en `D:\DESARROLLO\pdfs_extraidos\`, organizados en carpetas por cliente (nombre del conjunto residencial). Necesitamos clasificar cada PDF para saber: qué tipo de documento es, cuál es su fecha, y confirmar el cliente. Esto se usará después para cargarlos correctamente al sistema.

Ya existe un inventario CSV en `D:\DESARROLLO\pdfs_extraidos\inventario_takeout.csv` con columnas: `carpeta_mbox, archivo, fecha_email, subject, tamaño_bytes`.

**NO preguntes nada. Lee el código, entiende, implementa, ejecuta.**

## Tarea

Crear `tools/clasificar_pdfs.php` que:

1. Recorra TODOS los PDFs en `D:\DESARROLLO\pdfs_extraidos\` (subcarpetas = clientes)
2. Para cada PDF:
   a. Extraiga texto con `pdftotext` (instalar poppler si no está: `choco install poppler` o descargar de https://github.com/oschwartz10612/poppler-windows/releases)
   b. Tome los primeros 3,000 caracteres del texto extraído
   c. Envíe a Claude Haiku via API con un prompt que pida clasificar
   d. Guarde el resultado en un CSV

### API Key de Claude
```
$ANTHROPIC_API_KEY
```

### Modelo a usar
`claude-haiku-4-5-20251001` — el más barato (~$0.001 por PDF)

### Prompt para Claude Haiku (enviar con cada PDF)

```
Eres un clasificador de documentos SST (Seguridad y Salud en el Trabajo) para copropiedades en Colombia.

Dado el siguiente texto extraído de un PDF, responde SOLO en formato JSON con estos campos:
- tipo_documento: el nombre del tipo de documento de esta lista EXACTA (o "OTRO" si no coincide con ninguno)
- fecha_documento: la fecha más reciente del documento en formato YYYY-MM-DD (la fecha real de cuando se hizo el documento, NO la fecha del template)
- confianza: "alta", "media" o "baja"

LISTA DE TIPOS DE DOCUMENTO:
INSPECCION LOCATIVA
ACTA DE VISITA
MATRIZ VULNERABILIDAD
CERTIFICADO DE FUMIGACION
PLAN DE EMERGENCIAS
CERTIFICADO LAVADO DE TANQUES
DOTACION ASEADORAS
DOTACION TODERO
DOTACION VIGILANTES
EVALUACION DE CONTRATISTA
RESULTADOS CALIFICACION DE ESTÁNDARES MINIMOS
INFORME A LA ALTA DIRECCION
INFORME DE CIERRE DE MES
PLAN DE SANEAMIENTO BASICO
MANEJO DE RESIDUOS Y PLAGAS
CERTIFICADO 50 HORAS
ACTA CAPACITACION
REPORTE DE CAPACITACION
RESPONSABILIDADES SST
CONTRATO SG-SST
ACUERDO DE CONFIDENCIALIDAD
INSPECCION DE BOTIQUIN
INSPECCION ZONA DE RESIDUOS
INSPECCION EXTINTORES
INSPECCION GABINETES CONTRA INCENDIO
RECORRIDO DE INSPECCION
INSPECCION RECURSOS PARA LA SEGURIDAD
OCURRENCIA DE PELIGROS
INSPECCION EQUIPOS DE COMUNICACIONES
SEGURIDAD SOCIAL
SOPORTE LAVADO DE TANQUES
SOPORTE MANEJO DE PLAGAS
SOPORTE DESRATIZACION
PUBLICACION POLITICA Y OBJETIVOS
APROBACION EVALUACION INICIAL REP LEGAL
APROBACION PLAN DE TRABAJO REP LEGAL
HOJA DE VIDA BRIGADISTA
DOCUMENTOS DEL RESPONSABLE SST
PLAN DE EMERGENCIAS FAMILIAR
EVALUACION SIMULACRO
PREPARACION GUION SIMULACRO
INSPECCION SENALIZACION
CONSTANCIA DE PARTICIPACION SIMULACRO
AUDITORIA PROVEEDOR DE ASEO
AUDITORIA PROVEEDOR DE VIGILANCIA
AUDITORIA OTROS PROVEEDORES
APROBACION PLAN DE CAPACITACION REP LEGAL
PROGRAMA DE LIMPIEZA Y DESINFECCION
PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS
PROGRAMA DE CONTROL INTEGRADO DE PLAGAS
PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE
KPI PROGRAMA DE LIMPIEZA Y DESINFECCION
KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS
KPI PROGRAMA DE CONTROL INTEGRADO DE PLAGAS
KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE
PLANILLA DE SEGURIDAD SOCIAL

IMPORTANTE:
- La fecha del documento es la fecha MÁS RECIENTE que aparezca en el texto (no la del encabezado/plantilla que suele ser antigua)
- Si el texto está vacío o ilegible, responde con tipo_documento:"ILEGIBLE" y fecha_documento:null
- Responde SOLO el JSON, sin explicaciones

TEXTO DEL PDF:
```

### Tabla de mapeo tipo → id_report_type, id_detailreport

Incluir en el script para generar el CSV completo:

```php
$MAPEO = [
    'INSPECCION LOCATIVA' => [1, 16],
    'ACTA DE VISITA' => [6, 9],
    'MATRIZ VULNERABILIDAD' => [11, 11],
    'CERTIFICADO DE FUMIGACION' => [13, 16],
    'PLAN DE EMERGENCIAS' => [11, 10],
    'CERTIFICADO LAVADO DE TANQUES' => [14, 14],
    'DOTACION ASEADORAS' => [3, 7],
    'DOTACION TODERO' => [3, 6],
    'DOTACION VIGILANTES' => [4, 8],
    'EVALUACION DE CONTRATISTA' => [4, 9],
    'RESULTADOS CALIFICACION DE ESTÁNDARES MINIMOS' => [9, 1],
    'INFORME A LA ALTA DIRECCION' => [2, 1],
    'INFORME DE CIERRE DE MES' => [10, 1],
    'PLAN DE SANEAMIENTO BASICO' => [13, 20],
    'MANEJO DE RESIDUOS Y PLAGAS' => [13, 20],
    'CERTIFICADO 50 HORAS' => [8, 23],
    'ACTA CAPACITACION' => [7, 1],
    'REPORTE DE CAPACITACION' => [7, 1],
    'RESPONSABILIDADES SST' => [7, 1],
    'CONTRATO SG-SST' => [19, 20],
    'ACUERDO DE CONFIDENCIALIDAD' => [19, 20],
    'INSPECCION DE BOTIQUIN' => [1, 3],
    'INSPECCION ZONA DE RESIDUOS' => [1, 15],
    'INSPECCION EXTINTORES' => [1, 2],
    'INSPECCION GABINETES CONTRA INCENDIO' => [1, 4],
    'RECORRIDO DE INSPECCION' => [1, 19],
    'INSPECCION RECURSOS PARA LA SEGURIDAD' => [11, 5],
    'OCURRENCIA DE PELIGROS' => [11, 12],
    'INSPECCION EQUIPOS DE COMUNICACIONES' => [1, 21],
    'SEGURIDAD SOCIAL' => [6, 9],
    'SOPORTE LAVADO DE TANQUES' => [13, 20],
    'SOPORTE MANEJO DE PLAGAS' => [13, 20],
    'SOPORTE DESRATIZACION' => [13, 20],
    'PUBLICACION POLITICA Y OBJETIVOS' => [17, 23],
    'APROBACION EVALUACION INICIAL REP LEGAL' => [17, 23],
    'APROBACION PLAN DE TRABAJO REP LEGAL' => [17, 23],
    'HOJA DE VIDA BRIGADISTA' => [11, 10],
    'DOCUMENTOS DEL RESPONSABLE SST' => [21, 20],
    'PLAN DE EMERGENCIAS FAMILIAR' => [11, 10],
    'EVALUACION SIMULACRO' => [11, 10],
    'PREPARACION GUION SIMULACRO' => [11, 10],
    'INSPECCION SENALIZACION' => [1, 21],
    'CONSTANCIA DE PARTICIPACION SIMULACRO' => [11, 10],
    'AUDITORIA PROVEEDOR DE ASEO' => [3, 9],
    'AUDITORIA PROVEEDOR DE VIGILANCIA' => [4, 9],
    'AUDITORIA OTROS PROVEEDORES' => [12, 9],
    'APROBACION PLAN DE CAPACITACION REP LEGAL' => [17, 23],
    'PROGRAMA DE LIMPIEZA Y DESINFECCION' => [13, 20],
    'PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS' => [13, 20],
    'PROGRAMA DE CONTROL INTEGRADO DE PLAGAS' => [13, 20],
    'PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE' => [13, 20],
    'KPI PROGRAMA DE LIMPIEZA Y DESINFECCION' => [13, 20],
    'KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS' => [13, 20],
    'KPI PROGRAMA DE CONTROL INTEGRADO DE PLAGAS' => [13, 20],
    'KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE' => [13, 20],
    'PLANILLA DE SEGURIDAD SOCIAL' => [6, 9],
    'OTRO' => [22, 20],
    'ILEGIBLE' => [22, 20],
];
```

### Mapeo de clientes (carpeta → NIT, id_cliente)

Consultar de la BD:
```php
$db = new mysqli();
$db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb', getenv('DB_PROD_PASS'),
    'propiedad_horizontal', 25060, null, MYSQLI_CLIENT_SSL
);
// SELECT id_cliente, nit_cliente, nombre_cliente FROM tbl_clientes
```

El nombre de la carpeta coincide parcialmente con `nombre_cliente`. Ya existe lógica de match en `tools/recarga_desde_takeout.php` función `buscarCliente()` — reutilízala.

### Estructura del script

```php
<?php
// tools/clasificar_pdfs.php
// Uso: DB_PROD_PASS=xxx php tools/clasificar_pdfs.php

$pdfDir = 'D:/DESARROLLO/pdfs_extraidos';
$outputCsv = 'D:/DESARROLLO/clasificacion_pdfs.csv';
$apiKey = '$ANTHROPIC_API_KEY';
$pdftotext = 'pdftotext'; // o ruta completa si no está en PATH

// Para cada carpeta (cliente) en $pdfDir:
//   Para cada PDF en la carpeta:
//     1. pdftotext archivo.pdf - | head -3000chars
//     2. Si texto vacío → marcar como ILEGIBLE
//     3. Llamar Claude Haiku con el texto
//     4. Parsear respuesta JSON
//     5. Escribir fila en CSV

// CSV columns:
// carpeta, archivo, nit_cliente, id_cliente, tipo_documento, id_report_type, id_detailreport, fecha_documento, confianza, fecha_email (del inventario)
```

### Manejo de errores y rate limiting

- Si la API responde con error 429 (rate limit), esperar 5 segundos y reintentar
- Si la API responde con error 500/503, esperar 10 segundos y reintentar hasta 3 veces
- Si pdftotext falla (archivo corrupto), marcar como ILEGIBLE
- Guardar progreso cada 100 PDFs (para poder retomar si se corta)
- Mostrar progreso: `[1234/4070] ACTA DE VISITA | 2025-03-15 | alta | CONJUNTO PLAZUELAS`

### Retomar ejecución

Si el script se corta, debe poder retomar. Al inicio, leer el CSV de output y saltar los archivos ya clasificados:
```php
$yaClasificados = [];
if (file_exists($outputCsv)) {
    $fh = fopen($outputCsv, 'r');
    fgetcsv($fh); // header
    while ($row = fgetcsv($fh)) {
        $yaClasificados[$row[0] . '/' . $row[1]] = true;
    }
    fclose($fh);
    echo "Retomando: " . count($yaClasificados) . " ya clasificados\n";
}
```

### Instalar pdftotext en Windows

Antes de ejecutar, verificar que `pdftotext` funcione:
```bash
pdftotext -v
```
Si no está instalado:
1. Descargar poppler de: https://github.com/oschwartz10612/poppler-windows/releases
2. Extraer en `C:\poppler\`
3. Agregar `C:\poppler\Library\bin\` al PATH
4. O pasar ruta completa: `$pdftotext = 'C:/poppler/Library/bin/pdftotext.exe'`

### Resultado esperado

Archivo `D:\DESARROLLO\clasificacion_pdfs.csv` con ~4,070 filas:
```csv
carpeta,archivo,nit_cliente,id_cliente,tipo_documento,id_report_type,id_detailreport,fecha_documento,confianza,fecha_email
CONJUNTOS-CONJUNTO RESIDENCIAL EL ZORZAL,ACTA DE VISITA__CONJUNTO...,901103223,49,ACTA DE VISITA,6,9,2026-02-20,alta,2026-02-20 14:43:32
```

Este CSV es la fuente de verdad para la carga final al sistema.

### Costo estimado
- 4,070 PDFs × ~600 tokens input + 100 output = ~2.8M tokens
- Claude Haiku: $0.80/M input + $4/M output ≈ **$2.64 USD**
- Presupuesto disponible: $10 USD

### NO preguntes nada. Instala pdftotext, crea el script, ejecuta, y muestra progreso.

---
