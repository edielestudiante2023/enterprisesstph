# MODULO: Informe de Avances SG-SST — Documento de Replicacion Completo

> **Framework:** CodeIgniter 4 / PHP 8.2  
> **Base de Datos:** MySQL 8 (DigitalOcean Managed)  
> **Formato:** FT-SST-205 (Sistema de Gestion de Seguridad y Salud en el Trabajo)  
> **Fecha de extraccion:** 2026-04-02

---

## 1. INVENTARIO DE ARCHIVOS

### 1.1 Archivos Propios del Modulo

| # | Ruta | Lineas | Proposito |
|---|------|--------|-----------|
| 1 | `app/Controllers/InformeAvancesController.php` | ~1295 | Controlador principal: CRUD, metricas AJAX, PDF, email, API externa |
| 2 | `app/Models/InformeAvancesModel.php` | ~85 | Modelo CI4 para `tbl_informe_avances` con queries por consultor/cliente |
| 3 | `app/Libraries/MetricasInformeService.php` | ~458 | Servicio de calculo: estandares, PTA, capacitacion, pendientes, desgloses |
| 4 | `app/Services/IADocumentacionService.php` | ~64 | Wrapper OpenAI API para generar resumen ejecutivo con IA |
| 5 | `app/Views/informe_avances/list.php` | ~206 | Vista listado con DataTables, filtros Select2, exportacion Excel |
| 6 | `app/Views/informe_avances/form.php` | ~1003 | Vista formulario crear/editar con Chart.js, Select2, generacion IA |
| 7 | `app/Views/informe_avances/view.php` | ~450+ | Vista solo lectura del informe finalizado con graficas y tablas |
| 8 | `app/Views/informe_avances/pdf.php` | ~550+ | Template DOMPDF para generacion PDF con layout tabular |
| 9 | `app/SQL/migrate_informe_avances.php` | ~174 | Migracion: CREATE TABLE + seed detail_report id=37 |
| 10 | `app/SQL/migrate_informe_metricas_v2.php` | ~60 | Migracion: ADD COLUMN metricas_desglose_json |
| 11 | `app/SQL/migrate_drop_fk_informe_avances.php` | ~30 | Migracion: drop FK del consultor (para permitir NULL) |

### 1.2 Modelos del Sistema que Usa

| Modelo | Tabla | Uso en el Modulo |
|--------|-------|-----------------|
| `ClientModel` | `tbl_clientes` | Datos del cliente: nombre, NIT, logo, correo, consultor |
| `ConsultantModel` | `tbl_consultor` | Datos del consultor: nombre, correo |
| `ReporteModel` | `tbl_reporte` | Subir PDF a repositorio de documentos del cliente |
| `ActaVisitaModel` | `tbl_acta_visita` | Validar que hubo visita en el periodo (API externa) |
| `HistorialEstandaresModel` | `historial_resumen_estandares` | Snapshots historicos de calificacion estandares |
| `HistorialPlanTrabajoModel` | `historial_resumen_plan_trabajo` | Snapshots historicos de plan de trabajo |
| `VencimientosMantenimientoModel` | `tbl_vencimientos_mantenimientos` | Elementos con vencimiento proximo/vencido |

### 1.3 Archivos Relacionados (no exclusivos del modulo)

| Archivo | Relacion |
|---------|----------|
| `app/Config/Routes.php` (lineas 1543-1572) | Definicion de rutas del modulo |
| `app/Commands/RegenerarPdfs.php` (linea 396+) | Regeneracion masiva de PDFs incluye este modulo |
| `app/Commands/LimpiarReportes404.php` (linea 172+) | Limpieza de imagenes huerfanas en tbl_informe_avances |
| `.gitignore` | Excluye `uploads/informe-avances/` |

---

## 2. RUTAS DEL APLICATIVO

### 2.1 Rutas Web (requieren sesion — filter: `auth`)

Prefijo: `/informe-avances/`

| Metodo | URL | Controller::Metodo | Descripcion |
|--------|-----|--------------------|-------------|
| GET | `/` | `list()` | Listado de todos los informes con DataTables |
| GET | `/create` | `create()` | Formulario nuevo informe (sin preseleccion) |
| GET | `/create/{id_cliente}` | `create($id)` | Formulario nuevo con cliente preseleccionado |
| POST | `/store` | `store()` | Guardar nuevo borrador |
| GET | `/edit/{id}` | `edit($id)` | Formulario edicion de informe existente |
| POST | `/update/{id}` | `update($id)` | Actualizar informe (si ya esta completo, regenera PDF) |
| GET | `/view/{id}` | `view($id)` | Vista solo lectura del informe finalizado |
| GET | `/pdf/{id}` | `generatePdf($id)` | Regenerar y servir PDF inline |
| POST | `/finalizar/{id}` | `finalizar($id)` | Cambiar estado a 'completo', generar PDF, subir a reportes |
| GET | `/delete/{id}` | `delete($id)` | Eliminar informe + archivos asociados |
| POST | `/enviar/{id}` | `enviar($id)` | Enviar informe por email al cliente con PDF adjunto |

### 2.2 Rutas AJAX Internas (requieren sesion)

| Metodo | URL | Controller::Metodo | Descripcion |
|--------|-----|--------------------|-------------|
| GET | `/api/metricas/{id_cliente}` | `calcularMetricas($id)` | Calcula todas las metricas en tiempo real |
| GET | `/api/vencimientos/{id_cliente}` | `apiVencimientos($id)` | Lista vencimientos proximos del cliente |
| GET | `/api/historial/{id_cliente}` | `apiHistorial($id)` | Historial mensual de estandares y plan |
| GET | `/api/clientes` | `getClientes()` | Lista clientes activos para Select2 |
| POST | `/api/liquidar/{id_cliente}` | `liquidarSnapshot($id)` | Toma snapshot de estandares y plan de trabajo |
| POST | `/generar-resumen` | `generarResumen()` | Genera resumen ejecutivo con IA (OpenAI) |

### 2.3 Rutas API Externa (filter: `authOrApiKey`)

Prefijo: `/ext-api/informe-avances/`

| Metodo | URL | Controller::Metodo | Descripcion |
|--------|-----|--------------------|-------------|
| GET | `/clientes` | `getClientes()` | Lista clientes activos |
| GET | `/clientes-con-visita` | `getClientesConVisita()` | Clientes con acta de visita en periodo |
| GET | `/metricas/{id_cliente}` | `calcularMetricas($id)` | Metricas calculadas del cliente |
| POST | `/generar-resumen` | `generarResumen()` | Genera resumen IA |
| POST | `/generar-y-enviar/{id_cliente}` | `apiGenerarYEnviar($id)` | Flujo completo automatico: crear + PDF + email |
| POST | `/enviar/{id}` | `enviar($id)` | Enviar email con PDF adjunto |

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tabla Principal: `tbl_informe_avances`

```sql
CREATE TABLE tbl_informe_avances (
    id INT NOT NULL AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_consultor INT DEFAULT NULL,
    
    -- Periodo
    fecha_desde DATE NOT NULL,
    fecha_hasta DATE NOT NULL,
    anio SMALLINT NOT NULL,
    
    -- Metricas (snapshot al momento de crear/liquidar)
    puntaje_anterior DECIMAL(5,2) DEFAULT NULL,
    puntaje_actual DECIMAL(5,2) DEFAULT NULL,
    diferencia_neta DECIMAL(5,2) DEFAULT NULL,
    estado_avance VARCHAR(60) NOT NULL DEFAULT 'ESTABLE',
    
    -- Indicadores (%)
    indicador_plan_trabajo DECIMAL(5,2) DEFAULT NULL,
    indicador_capacitacion DECIMAL(5,2) DEFAULT NULL,
    
    -- Screenshots opcionales de dashboards
    img_cumplimiento_estandares VARCHAR(500) DEFAULT NULL,
    img_indicador_plan_trabajo VARCHAR(500) DEFAULT NULL,
    img_indicador_capacitacion VARCHAR(500) DEFAULT NULL,
    
    -- Desgloses JSON (datos numericos + imagenes base64 de Chart.js)
    metricas_desglose_json LONGTEXT DEFAULT NULL,
    
    -- Contenido textual
    resumen_avance LONGTEXT DEFAULT NULL,
    observaciones TEXT DEFAULT NULL,
    actividades_abiertas TEXT DEFAULT NULL,
    actividades_cerradas_periodo TEXT DEFAULT NULL,
    
    -- Enlaces
    enlace_dashboard VARCHAR(500) DEFAULT NULL,
    acta_visita_url VARCHAR(500) DEFAULT NULL,
    
    -- Soportes (hasta 4 pares texto+imagen)
    soporte_1_texto TEXT DEFAULT NULL,
    soporte_1_imagen VARCHAR(500) DEFAULT NULL,
    soporte_2_texto TEXT DEFAULT NULL,
    soporte_2_imagen VARCHAR(500) DEFAULT NULL,
    soporte_3_texto TEXT DEFAULT NULL,
    soporte_3_imagen VARCHAR(500) DEFAULT NULL,
    soporte_4_texto TEXT DEFAULT NULL,
    soporte_4_imagen VARCHAR(500) DEFAULT NULL,
    
    -- Sistema
    ruta_pdf VARCHAR(500) DEFAULT NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_infavance_cliente (id_cliente),
    KEY idx_infavance_consultor (id_consultor),
    KEY idx_infavance_estado (estado),
    KEY idx_infavance_fecha (fecha_hasta),
    CONSTRAINT fk_infavance_cliente FOREIGN KEY (id_cliente) 
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Informe de Avances (ex Cierre de Mes) - Formato FT-SST-205';
```

### 3.2 Tabla de Snapshots: `historial_resumen_estandares`

```sql
CREATE TABLE historial_resumen_estandares (
    id_cliente INT NOT NULL,
    nombre_cliente VARCHAR(255) DEFAULT NULL,
    estandares TEXT DEFAULT NULL,
    nombre_consultor VARCHAR(255) DEFAULT NULL,
    correo_consultor VARCHAR(255) DEFAULT NULL,
    total_valor DECIMAL(10,2) DEFAULT NULL,
    total_puntaje DECIMAL(10,2) DEFAULT NULL,
    porcentaje_cumplimiento DECIMAL(5,2) DEFAULT NULL,
    fecha_extraccion DATETIME NOT NULL,
    PRIMARY KEY (id_cliente, fecha_extraccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.3 Tabla de Snapshots: `historial_resumen_plan_trabajo`

```sql
CREATE TABLE historial_resumen_plan_trabajo (
    id_cliente INT NOT NULL,
    nombre_cliente VARCHAR(255) DEFAULT NULL,
    estandares TEXT DEFAULT NULL,
    nombre_consultor VARCHAR(255) DEFAULT NULL,
    correo_consultor VARCHAR(255) DEFAULT NULL,
    total_actividades INT DEFAULT NULL,
    actividades_abiertas INT DEFAULT NULL,
    porcentaje_abiertas DECIMAL(5,2) DEFAULT NULL,
    fecha_extraccion DATETIME NOT NULL,
    PRIMARY KEY (id_cliente, fecha_extraccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.4 Datos Semilla

```sql
-- Tipo de detalle para reportes
INSERT INTO detail_report (id_detailreport, detail_report) 
VALUES (37, 'INFORME DE AVANCES');

-- El report_type usado es id=6 ('Actas de Visita') que ya debe existir
-- SELECT id_report_type, report_type FROM report_type_table WHERE id_report_type = 6;
-- → 6, 'Actas de Visita'
```

### 3.5 Tablas del Sistema Consultadas (NO crear — ya existen)

| Tabla | Campos que usa el modulo | Relacion |
|-------|--------------------------|----------|
| `tbl_clientes` | `id_cliente`, `nombre_cliente`, `nit_cliente`, `correo_cliente`, `logo`, `estado`, `id_consultor`, `estandares`, `email_consultor_externo`, `consultor_externo` | FK directa |
| `tbl_consultor` | `id_consultor`, `nombre_consultor`, `correo_consultor` | FK via id_consultor |
| `tbl_reporte` | `id_reporte`, `titulo_reporte`, `id_detailreport`, `id_report_type`, `id_cliente`, `estado`, `observaciones`, `enlace`, `created_at`, `updated_at` | Almacena PDF generado |
| `detail_report` | `id_detailreport`, `detail_report` | Catalogo tipo documento |
| `report_type_table` | `id_report_type`, `report_type` | Catalogo categoria reporte |
| `evaluacion_inicial_sst` | `id_cliente`, `valor`, `puntaje_cuantitativo`, `evaluacion_inicial`, `ciclo`, `updated_at` | Calculo de estandares minimos |
| `tbl_pta_cliente` | `id_cliente`, `actividad_plandetrabajo`, `numeral_plandetrabajo`, `phva_plandetrabajo`, `responsable_sugerido_plandetrabajo`, `estado_actividad`, `fecha_cierre`, `fecha_propuesta`, `created_at` | Plan de trabajo anual |
| `tbl_cronog_capacitacion` | `id_cliente`, `nombre_capacitacion`, `objetivo_capacitacion`, `perfil_de_asistentes`, `nombre_del_capacitador`, `horas_de_duracion_de_la_capacitacion`, `numero_de_asistentes_a_capacitacion`, `numero_total_de_personas_programadas`, `porcentaje_cobertura`, `promedio_de_calificaciones`, `observaciones`, `fecha_programada`, `fecha_de_realizacion`, `estado` | Indicador capacitacion |
| `tbl_pendientes` | `id_cliente`, `tarea_actividad`, `responsable`, `fecha_asignacion`, `fecha_cierre`, `estado`, `conteo_dias` | Compromisos abiertos/cerrados |
| `tbl_acta_visita` | `id_cliente`, `fecha_visita`, `motivo` | Validar visitas en periodo |
| `tbl_vencimientos_mantenimientos` | `id_cliente`, `id_mantenimiento`, `fecha_vencimiento`, `estado_actividad`, `observaciones` | Elementos proximos a vencer |
| `tbl_mantenimientos` | `id_mantenimiento`, `detalle_mantenimiento` | JOIN para nombre del elemento |

---

## 4. FLUJO FUNCIONAL

### 4.1 Estados y Transiciones

```
[NUEVO] --store()--> [borrador] --finalizar()--> [completo]
                         |                            |
                     update()                    generatePdf()
                     delete()                    enviar()
                                                 apiGenerarYEnviar()
```

- **borrador**: Informe en edicion. Se puede modificar, eliminar, generar resumen IA.
- **completo**: Informe finalizado. Tiene PDF generado. Se puede ver, descargar PDF, enviar por email, re-editar (lo cual regenera PDF automaticamente).

### 4.2 Ciclo de Vida Completo (Flujo Manual)

1. **Consultor accede** a `/informe-avances/create` o `/informe-avances/create/{id_cliente}`
2. **Recordatorio SweetAlert** antes de crear: verificar que estandares, PTA, capacitaciones esten actualizados
3. **Selecciona cliente** en Select2 → dispara AJAX `api/clientes`
4. **Selecciona periodo** (mes anterior, bimestre, trimestre, o mes especifico) → auto-llena fechas
5. **Clic "Liquidar Informe"** → AJAX POST `api/liquidar/{id}`:
   - Toma snapshot de estandares (desde `evaluacion_inicial_sst`)
   - Toma snapshot de plan de trabajo (desde `tbl_pta_cliente`)
   - Guarda en `historial_resumen_estandares` y `historial_resumen_plan_trabajo`
   - Elimina snapshots previos del mismo mes para evitar duplicados
6. **Se cargan metricas** automaticamente via AJAX `api/metricas/{id}`:
   - Puntaje actual y anterior de estandares
   - Indicador plan de trabajo (% cerradas)
   - Indicador capacitacion (% ejecutadas)
   - Desgloses por pilar → graficas Chart.js doughnut
   - Actividades cerradas y abiertas del periodo
   - Documentos cargados en el periodo
7. **Se carga historial** via AJAX `api/historial/{id}` → graficas de evolucion lineal
8. **Consultor genera resumen** con boton "Generar con IA" → AJAX POST `generar-resumen`:
   - Construye prompt detallado con todas las metricas, actividades, capacitaciones, vencimientos
   - Envia a OpenAI API (gpt-4o-mini por defecto)
   - Respuesta se muestra en textarea editable
9. **Consultor agrega soportes** (hasta 4 pares titulo+imagen)
10. **Guardar borrador** → POST `store` (captura chart images como base64 en JSON)
11. **Finalizar** → POST `finalizar/{id}`:
    - Genera PDF con DOMPDF usando template `pdf.php`
    - Cambia estado a 'completo'
    - Sube PDF a repositorio de reportes (carpeta del NIT del cliente)
    - Envia email de notificacion via SendGrid

### 4.3 Flujo Automatico (API Externa — OpenClaw)

1. `POST /ext-api/informe-avances/generar-y-enviar/{id_cliente}`
2. Calcula fechas automaticamente (desde ultimo informe o 1 de enero)
3. Valida que el cliente tenga al menos una acta de visita en el periodo
4. Calcula todas las metricas
5. Genera resumen con IA
6. Crea el informe como borrador → lo finaliza → genera PDF → sube a reportes → envia email
7. Retorna JSON con `informe_id`, `pdf_url`, resultado del email

### 4.4 Calculo de Metricas (MetricasInformeService)

| Metrica | Fuente | Logica |
|---------|--------|--------|
| **Puntaje estandares** | `evaluacion_inicial_sst` | `SUM(puntaje_cuantitativo) / SUM(valor) * 100`, acumulado (sin filtro de anio) |
| **Puntaje anterior** | `tbl_informe_avances` | Ultimo informe `completo` del mismo cliente y anio. Si no hay, linea base = 39.75 |
| **Diferencia neta** | Calculada | `puntaje_actual - puntaje_anterior` |
| **Estado de avance** | Calculada | `>5` = SIGNIFICATIVO, `>=1` = MODERADO, `0` = ESTABLE, `<0` = REINICIO |
| **Indicador PTA** | `tbl_pta_cliente` | `cerradas_en_anio / total_en_anio * 100`. Usa `fecha_cierre` (negocio), no audit |
| **Indicador capacitacion** | `tbl_cronog_capacitacion` | `EJECUTADA / total * 100` filtrado por `fecha_programada` en el anio |
| **Actividades abiertas** | `tbl_pendientes` | Lista texto de tareas con estado='ABIERTA' del anio |
| **Actividades cerradas** | `tbl_pta_cliente` | Detalle de PTA cerradas en el periodo (por `fecha_cierre`) |
| **Fecha desde sugerida** | `tbl_informe_avances` | Dia siguiente al ultimo informe completo del anio, o 1 de enero |
| **Desgloses** | Varias tablas | Agrupaciones por ciclo PHVA, estado, para graficas |

### 4.5 Generacion de PDF (DOMPDF)

- **Template:** `app/Views/informe_avances/pdf.php`
- **Libreria:** DOMPDF 3.0.0 con opciones `isRemoteEnabled(true)` + `isHtml5ParserEnabled(true)`
- **Papel:** Letter, portrait
- **Logo:** Convertido a base64 desde `uploads/{logo_cliente}`
- **Soportes:** Imagenes convertidas a base64
- **Graficas de evolucion:** Usa QuickChart.io (URL externa con config JSON del chart)
- **Graficas de pilares:** Si hay `chart_images` base64 capturadas del frontend, las usa; sino, genera stacked bars HTML con la funcion `renderStackedBar()`
- **Almacenamiento:** `uploads/informe-avances/pdfs/informe_avances_{id}_{timestamp}.pdf`
- **PDF anterior:** Se elimina al regenerar

### 4.6 Upload a Reportes

Al finalizar un informe, el PDF se copia al directorio de reportes del cliente:
- **Destino:** `{UPLOADS_PATH}/{nit_cliente}/informe_avances_{id}_{periodo}.pdf`
- **Registro en BD:** `tbl_reporte` con `id_report_type=6`, `id_detailreport=37`
- **Observaciones:** Contiene `inf_avance_id:{id}` para evitar duplicados (upsert)
- **Enlace:** URL publica del PDF

### 4.7 Envio de Email (SendGrid)

**Dos flujos de email diferentes:**

1. **`sendReportEmail()`** — Se ejecuta al subir a reportes (finalizar/update):
   - Destinatarios: cliente + consultor interno + consultor externo
   - Contenido: HTML con boton "Ir a Enterprisesst"
   - Sin adjunto PDF
   - Desde: `notificacion.cycloidtalent@cycloidtalent.com`

2. **`enviar()`** — Ruta dedicada POST `enviar/{id}`:
   - Destinatario: solo el correo del cliente
   - Contenido: HTML con tabla de resumen (puntaje + estado)
   - **Con PDF adjunto** (base64 attachment)
   - Desde: `notificacion.cycloidtalent@cycloidtalent.com`

- Variable `DISABLE_REPORT_EMAILS=true` desactiva el flujo #1
- SDK: `\SendGrid\Mail\Mail` + `\SendGrid($apiKey)`

---

## 5. DEPENDENCIAS EXTERNAS

### 5.1 Librerias PHP (Composer)

| Libreria | Uso |
|----------|-----|
| `dompdf/dompdf` ^3.0 | Generacion de PDF |
| `sendgrid/sendgrid` | Envio de emails con adjuntos |

### 5.2 CDN Frontend

| Libreria | Version | Uso |
|----------|---------|-----|
| Bootstrap | 5.3.0 | Framework CSS |
| jQuery | 3.6.0 | Manipulacion DOM y AJAX |
| DataTables | 1.13.4 | Tabla interactiva en listado |
| DataTables Buttons + JSZip | 2.3.6 / 3.10.1 | Exportacion Excel |
| Font Awesome | 6.4.0 | Iconos |
| Select2 | 4.1.0-rc.0 | Dropdown de busqueda de clientes |
| Select2 Bootstrap 5 Theme | 1.3.0 | Tema Select2 compatible con BS5 |
| Chart.js | 4.4.0 | Graficas doughnut y linea en form/view |
| chartjs-plugin-datalabels | 2.2.0 | Labels dentro de las graficas |
| SweetAlert2 | 11 | Confirmaciones y alertas |

### 5.3 APIs Externas

| API | Uso |
|-----|-----|
| OpenAI API (`gpt-4o-mini`) | Generacion de resumen ejecutivo con IA |
| QuickChart.io | Graficas de evolucion historica en el PDF |

### 5.4 Variables de Entorno Requeridas

```env
OPENAI_API_KEY=sk-...          # Para generacion de resumen IA
OPENAI_MODEL=gpt-4o-mini       # Modelo por defecto
SENDGRID_API_KEY=SG...         # Para envio de emails
DISABLE_REPORT_EMAILS=false    # Desactivar notificacion al subir reporte
UPLOADS_PATH=/ruta/soportes/   # Ruta externa para PDFs (fuera de git)
UPLOADS_URL_PREFIX=serve-file  # Prefijo URL para servir archivos
```

---

## 6. PATRONES ESPECIALES

### 6.1 Snapshot / Liquidacion

Antes de generar un informe, el consultor "liquida" al cliente. Esto toma una foto del estado actual de los estandares y plan de trabajo, y la guarda en tablas de historial. El snapshot usa **PK compuesta (id_cliente, fecha_extraccion)** y reemplaza snapshots del mismo mes para evitar duplicados:

```php
// Eliminar snapshots previos de este cliente en el mes actual
$db->query("DELETE FROM historial_resumen_estandares 
            WHERE id_cliente = ? AND fecha_extraccion >= ? AND fecha_extraccion <= ?", 
            [$id, $inicioMes, $finMes]);
```

### 6.2 Metricas como Snapshot vs Tiempo Real

- **Al crear/liquidar:** Las metricas se calculan en tiempo real y se almacenan en la tabla
- **Al ver/PDF:** Se usan los valores almacenados (snapshot), no se recalculan
- **Excepcion:** La vista `view()` recalcula `documentosCargados` en tiempo real

### 6.3 Desgloses JSON

El campo `metricas_desglose_json` almacena un JSON con:
- Desgloses numericos por pilar (estandares por ciclo PHVA, PTA por estado, etc.)
- Imagenes base64 de las graficas Chart.js capturadas del canvas del frontend

Esto permite que el PDF renderice graficas aunque DOMPDF no soporte Canvas/JS.

### 6.4 Captura de Graficas (Frontend → PDF)

```javascript
// Antes de submit, captura todas las graficas como PNG base64
function captureChartImages() {
    var images = {};
    ['chartEstandares', 'chartPlanTrabajo', 'chartCapacitacion', 'chartPendientes'].forEach(function(id) {
        var canvas = document.getElementById(id);
        if (canvas && canvas.style.display !== 'none' && !isCanvasBlank(canvas)) {
            images[id] = canvas.toDataURL('image/png');
        }
    });
    return images;
}
```

Si no hay imagenes capturadas, el PDF usa `renderStackedBar()` como fallback HTML.

### 6.5 Evolucion Historica (QuickChart.io)

Para el PDF, las graficas de evolucion historica se generan como URLs de QuickChart.io:

```php
$url = 'https://quickchart.io/chart?width=380&height=160&c=' . urlencode(json_encode($config));
```

DOMPDF las descarga como imagen remota (requiere `isRemoteEnabled(true)`).

### 6.6 Puntaje Linea Base

Si un cliente no tiene informe previo en el mismo ciclo PHVA, el puntaje anterior se establece en **39.75** (linea base Resolucion 0312 de 2019).

### 6.7 Prompt de IA para Resumen

El prompt es extremadamente detallado (~100 lineas). Incluye:
- Metricas cuantitativas del periodo
- Lista de actividades cerradas
- Documentos cargados
- Capacitaciones ejecutadas con detalle
- Compromisos PTA del periodo
- Evolucion historica mes a mes
- Vencimientos proximos
- 16 reglas de estilo/tono

Tono: positivo, comercial, orientado a resultados. Maximo 4 parrafos, prosa continua, sin vinetas.

### 6.8 Upsert en Reportes

Al subir el PDF a `tbl_reporte`, busca un registro existente con la misma combinacion `(id_cliente, id_report_type=6, id_detailreport=37)` y texto `inf_avance_id:{id}` en observaciones. Si existe, actualiza; si no, inserta.

---

## 7. ORDEN DE IMPLEMENTACION

### Paso 1: Base de Datos

```
1.1 Crear tabla tbl_informe_avances (script completo en seccion 3.1)
1.2 Crear tabla historial_resumen_estandares (seccion 3.2)
1.3 Crear tabla historial_resumen_plan_trabajo (seccion 3.3)
1.4 Insertar seed: detail_report id=37 'INFORME DE AVANCES'
1.5 Verificar que existan las tablas del sistema (seccion 3.5)
```

### Paso 2: Modelos

```
2.1 InformeAvancesModel (seccion 1.1 - archivo #2)
2.2 HistorialEstandaresModel (simple: tabla + allowedFields)
2.3 HistorialPlanTrabajoModel (simple: tabla + allowedFields)
2.4 Verificar que existan: ClientModel, ConsultantModel, ReporteModel, ActaVisitaModel, VencimientosMantenimientoModel
```

### Paso 3: Servicios/Libraries

```
3.1 MetricasInformeService — toda la logica de calculo de metricas
3.2 IADocumentacionService — wrapper OpenAI API (cURL directo)
```

### Paso 4: Rutas

```
4.1 Grupo 'informe-avances' con filter 'auth' (17 rutas web + AJAX)
4.2 Grupo 'ext-api/informe-avances' con filter 'authOrApiKey' (6 rutas API)
```

### Paso 5: Controlador

```
5.1 InformeAvancesController completo (~1295 lineas)
    - CRUD basico (list, create, store, edit, update, delete)
    - Metricas AJAX (calcularMetricas, apiVencimientos, apiHistorial, getClientes)
    - Snapshot (liquidarSnapshot)
    - Generacion IA (generarResumen con buildResumenPrompt)
    - PDF (generarPdfInterno, generatePdf, regenerarPdf)
    - Reportes (uploadToReportes)
    - Email (sendReportEmail, enviar, enviarInterno)
    - API externa (getClientesConVisita, apiGenerarYEnviar)
    - Historial (getHistorialEstandaresCliente, getHistorialPlanCliente, agruparHistorialPorMes)
    - QuickChart (buildQuickChartUrl)
```

### Paso 6: Vistas

```
6.1 list.php — DataTables con filtros Select2, exportacion Excel
6.2 form.php — Formulario completo con Chart.js, Select2, generacion IA, captura de graficas
6.3 view.php — Vista solo lectura con graficas Chart.js y tablas
6.4 pdf.php — Template DOMPDF con layout tabular, stacked bars fallback, QuickChart.io
```

### Paso 7: Configuracion

```
7.1 Variables de entorno (.env): OPENAI_API_KEY, SENDGRID_API_KEY, UPLOADS_PATH
7.2 Carpetas de upload: uploads/informe-avances/soportes/, uploads/informe-avances/screenshots/, uploads/informe-avances/pdfs/
7.3 .gitignore: excluir uploads/informe-avances/
```

### Paso 8: Integraciones

```
8.1 Verificar que el filter 'authOrApiKey' exista en app/Filters/
8.2 Verificar constantes UPLOADS_PATH y UPLOADS_URL_PREFIX
8.3 Verificar que la ruta serve-file/ funcione (FileServerController)
8.4 Configurar SendGrid con dominio de envio
8.5 Configurar OpenAI API key
```

---

## NOTAS PARA REPLICACION

1. **DOMPDF no soporta Flexbox/Grid** — todo el PDF usa tablas HTML para layout
2. **`@page margin` en DOMPDF** debe usar `px`, NO `cm`
3. **Logo del cliente** se convierte a base64 con `mime_content_type()` para DOMPDF
4. **Chart.js en PDF**: Las graficas del frontend se capturan como PNG base64 antes del submit, se guardan en JSON, y se renderizan como `<img>` en el PDF
5. **QuickChart.io**: Servicio externo gratuito para graficas de evolucion en el PDF. Requiere `isRemoteEnabled(true)` en DOMPDF
6. **Estado `completo` es semi-final**: Se puede re-editar, lo cual regenera el PDF y re-sube a reportes automaticamente
7. **La linea base 39.75** es especifica de la regulacion colombiana (Res. 0312/2019)
8. **`UPLOADS_PATH`** es una constante definida fuera del modulo que apunta a un directorio fuera de git (por el incidente de `git clean -fd` de marzo 2026)
