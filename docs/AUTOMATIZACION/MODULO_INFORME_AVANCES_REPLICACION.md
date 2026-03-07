# MÓDULO: INFORME DE AVANCES SG-SST — Documento de Replicación Completa

> Generado: 2026-03-07
> Framework: CodeIgniter 4 · PHP 8.2 · MySQL (InnoDB)
> URL base: `/informe-avances/`

---

## 1. INVENTARIO DE ARCHIVOS

### Archivos PROPIOS del módulo

| # | Ruta | Líneas | Propósito |
|---|------|--------|-----------|
| 1 | `app/Controllers/InformeAvancesController.php` | ~1075 | Controlador principal: CRUD, PDF, email, APIs AJAX, integración IA |
| 2 | `app/Models/InformeAvancesModel.php` | ~77 | Modelo CI4 para `tbl_informe_avances` |
| 3 | `app/Libraries/MetricasInformeService.php` | ~383 | Servicio de cálculo de métricas desde 5 tablas del sistema |
| 4 | `app/Views/informe_avances/list.php` | ~181 | Vista listado con DataTables + filtros |
| 5 | `app/Views/informe_avances/form.php` | ~951 | Formulario crear/editar con Chart.js, Select2, IA |
| 6 | `app/Views/informe_avances/view.php` | ~448 | Vista solo-lectura con gráficas de desgloses |
| 7 | `app/Views/informe_avances/pdf.php` | ~577 | Template PDF (DOMPDF) con layout tabla |
| 8 | `app/SQL/migrate_informe_avances.php` | ~174 | Migración: CREATE TABLE + dato semilla detail_report |
| 9 | `app/SQL/migrate_informe_metricas_v2.php` | ~60 | Migración: ADD COLUMN metricas_desglose_json |

### Archivos del SISTEMA que el módulo consume (dependencias)

| # | Ruta | Propósito en el módulo |
|---|------|----------------------|
| 10 | `app/Models/ClientModel.php` | Obtener datos del cliente (nombre, NIT, logo, correo) |
| 11 | `app/Models/ConsultantModel.php` | Obtener datos del consultor |
| 12 | `app/Models/ReporteModel.php` | Registrar PDF en `tbl_reporte` (repositorio de documentos) |
| 13 | `app/Models/ActaVisitaModel.php` | Validar que hubo visitas en el periodo |
| 14 | `app/Models/VencimientosMantenimientoModel.php` | Mantenimientos vencidos/próximos del cliente |
| 15 | `app/Models/HistorialEstandaresModel.php` | Historial snapshots de estándares mínimos |
| 16 | `app/Models/HistorialPlanTrabajoModel.php` | Historial snapshots de plan de trabajo |
| 17 | `app/Services/IADocumentacionService.php` | Generación de resumen con OpenAI (gpt-4o-mini) |
| 18 | `app/Config/Routes.php` | Rutas del módulo (líneas ~1350-1379) |

---

## 2. RUTAS DEL APLICATIVO

### 2.1 Grupo principal (requiere autenticación: `filter => 'auth'`)

| Método | URL | Controller::Método | Descripción |
|--------|-----|--------------------|-------------|
| GET | `/informe-avances` | `list()` | Listado de informes del consultor |
| GET | `/informe-avances/create` | `create()` | Formulario nuevo (sin cliente) |
| GET | `/informe-avances/create/{id}` | `create($id)` | Formulario nuevo con cliente preseleccionado |
| POST | `/informe-avances/store` | `store()` | Guardar nuevo borrador |
| GET | `/informe-avances/edit/{id}` | `edit($id)` | Formulario edición |
| POST | `/informe-avances/update/{id}` | `update($id)` | Actualizar informe existente |
| GET | `/informe-avances/view/{id}` | `view($id)` | Vista solo-lectura |
| GET | `/informe-avances/pdf/{id}` | `generatePdf($id)` | Servir PDF (regenera siempre) |
| POST | `/informe-avances/finalizar/{id}` | `finalizar($id)` | Marcar completo + generar PDF |
| GET | `/informe-avances/delete/{id}` | `delete($id)` | Eliminar informe + archivos |
| POST | `/informe-avances/generar-resumen` | `generarResumen()` | AJAX: generar resumen con IA |
| GET | `/informe-avances/api/metricas/{id}` | `calcularMetricas($id)` | AJAX: calcular métricas del cliente |
| GET | `/informe-avances/api/vencimientos/{id}` | `apiVencimientos($id)` | AJAX: vencimientos de mantenimientos |
| GET | `/informe-avances/api/historial/{id}` | `apiHistorial($id)` | AJAX: historial evolución del cliente |
| GET | `/informe-avances/api/clientes` | `getClientes()` | AJAX: clientes activos (para Select2) |
| POST | `/informe-avances/api/liquidar/{id}` | `liquidarSnapshot($id)` | AJAX: tomar snapshot individual |
| POST | `/informe-avances/enviar/{id}` | `enviar($id)` | Enviar informe por email (SendGrid) |

### 2.2 API externa (autenticación OR API Key: `filter => 'authOrApiKey'`)

| Método | URL | Controller::Método | Descripción |
|--------|-----|--------------------|-------------|
| GET | `/ext-api/informe-avances/clientes` | `getClientes()` | Listar clientes activos |
| GET | `/ext-api/informe-avances/clientes-con-visita` | `getClientesConVisita()` | Clientes con visita en periodo |
| GET | `/ext-api/informe-avances/metricas/{id}` | `calcularMetricas($id)` | Métricas de un cliente |
| POST | `/ext-api/informe-avances/generar-resumen` | `generarResumen()` | Generar resumen IA |
| POST | `/ext-api/informe-avances/generar-y-enviar/{id}` | `apiGenerarYEnviar($id)` | Flujo completo: crear+PDF+email |
| POST | `/ext-api/informe-avances/enviar/{id}` | `enviar($id)` | Enviar informe existente |

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tabla PROPIA: `tbl_informe_avances`

```sql
CREATE TABLE tbl_informe_avances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,

    -- Periodo
    fecha_desde DATE NOT NULL,
    fecha_hasta DATE NOT NULL,
    anio SMALLINT NOT NULL,

    -- Métricas (snapshot al crear)
    puntaje_anterior DECIMAL(5,2) NULL,
    puntaje_actual DECIMAL(5,2) NULL,
    diferencia_neta DECIMAL(5,2) NULL,
    estado_avance VARCHAR(60) NOT NULL DEFAULT 'ESTABLE',

    -- Indicadores (%)
    indicador_plan_trabajo DECIMAL(5,2) NULL,
    indicador_capacitacion DECIMAL(5,2) NULL,

    -- Screenshots opcionales de dashboards
    img_cumplimiento_estandares VARCHAR(500) NULL,
    img_indicador_plan_trabajo VARCHAR(500) NULL,
    img_indicador_capacitacion VARCHAR(500) NULL,

    -- Desgloses por pilar (JSON con datos numéricos + chart_images base64)
    metricas_desglose_json LONGTEXT NULL,

    -- Contenido textual
    resumen_avance LONGTEXT NULL,
    observaciones TEXT NULL,
    actividades_abiertas TEXT NULL,
    actividades_cerradas_periodo TEXT NULL,

    -- Enlaces
    enlace_dashboard VARCHAR(500) NULL,
    acta_visita_url VARCHAR(500) NULL,

    -- Soportes (4 pares texto + imagen)
    soporte_1_texto TEXT NULL,
    soporte_1_imagen VARCHAR(500) NULL,
    soporte_2_texto TEXT NULL,
    soporte_2_imagen VARCHAR(500) NULL,
    soporte_3_texto TEXT NULL,
    soporte_3_imagen VARCHAR(500) NULL,
    soporte_4_texto TEXT NULL,
    soporte_4_imagen VARCHAR(500) NULL,

    -- Sistema
    ruta_pdf VARCHAR(500) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_infavance_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT,
    CONSTRAINT fk_infavance_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT,
    INDEX idx_infavance_cliente (id_cliente),
    INDEX idx_infavance_consultor (id_consultor),
    INDEX idx_infavance_estado (estado),
    INDEX idx_infavance_fecha (fecha_hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Dato semilla: `detail_report`

```sql
INSERT INTO detail_report (id_detailreport, detail_report)
VALUES (37, 'INFORME DE AVANCES');
```

### 3.3 Tablas del SISTEMA que el módulo consulta

| Tabla | Campos usados | Relación |
|-------|--------------|----------|
| `tbl_clientes` | `id_cliente`, `nombre_cliente`, `nit_cliente`, `correo_cliente`, `logo`, `estado`, `id_consultor` | FK desde `tbl_informe_avances.id_cliente` |
| `tbl_consultor` | `id_consultor`, `nombre_consultor` | FK desde `tbl_informe_avances.id_consultor` |
| `evaluacion_inicial_sst` | `id_cliente`, `valor`, `puntaje_cuantitativo`, `ciclo`, `updated_at` | Cálculo % cumplimiento estándares |
| `tbl_pta_cliente` | `id_cliente`, `actividad_plandetrabajo`, `numeral_plandetrabajo`, `phva_plandetrabajo`, `responsable_sugerido_plandetrabajo`, `estado_actividad`, `fecha_cierre`, `created_at` | Plan de trabajo anual |
| `tbl_cronog_capacitacion` | `id_cliente`, `nombre_capacitacion`, `estado`, `fecha_programada` | Programa de capacitación |
| `tbl_pendientes` | `id_cliente`, `tarea_actividad`, `responsable`, `estado`, `fecha_asignacion`, `fecha_cierre`, `conteo_dias` | Compromisos / pendientes |
| `tbl_acta_visita` | `id_cliente`, `id`, `fecha_visita`, `motivo` | Actas de visita del periodo |
| `tbl_vencimientos_mantenimientos` | `id_cliente`, `id_mantenimiento`, `fecha_vencimiento`, `estado_actividad`, `observaciones` | Elementos por vencer |
| `tbl_mantenimientos` | `id_mantenimiento`, `detalle_mantenimiento` | JOIN para nombre del elemento |
| `tbl_reporte` | `id_reporte`, `titulo_reporte`, `id_detailreport`, `enlace`, `estado`, `observaciones`, `id_cliente`, `id_report_type` | Repositorio de documentos |
| `historial_resumen_estandares` | `id_cliente`, `porcentaje_cumplimiento`, `fecha_extraccion` | Evolución histórica |
| `historial_resumen_plan_trabajo` | `id_cliente`, `porcentaje_abiertas`, `fecha_extraccion` | Evolución histórica |
| `resumen_estandares_cliente` | VISTA SQL — usada por `liquidarSnapshot()` | Fuente para snapshot |
| `resumen_mensual_plan_trabajo` | VISTA SQL — usada por `liquidarSnapshot()` | Fuente para snapshot |

---

## 4. FLUJO FUNCIONAL

### 4.1 Estados y transiciones

```
[borrador] ──── finalizar() ────> [completo]
     │                                │
     │ edit/update (ilimitado)         │ update → regenera PDF + re-sube a reportes
     └────────────────────────────────┘
```

- **borrador**: editable, sin PDF
- **completo**: tiene PDF, sigue editable pero auto-regenera PDF en cada update

### 4.2 Métodos del controlador — resumen

| Método | Descripción |
|--------|-------------|
| `list()` | Lista informes del consultor logueado (admin ve todos los pendientes) |
| `create($idCliente)` | Muestra formulario vacío, precarga vencimientos si se pasa cliente |
| `store()` | Inserta borrador con datos POST + sube imágenes de soportes/screenshots |
| `edit($id)` | Muestra formulario con datos existentes + vencimientos |
| `update($id)` | Actualiza datos + si estado='completo' regenera PDF y re-sube a reportes |
| `view($id)` | Vista read-only con gráficas Chart.js + historial evolución |
| `finalizar($id)` | Genera PDF, cambia estado a 'completo', sube a repositorio reportes |
| `generatePdf($id)` | Regenera PDF siempre y lo sirve inline |
| `delete($id)` | Elimina informe + todos los archivos asociados |
| `calcularMetricas($id)` | AJAX: calcula métricas en tiempo real desde las 5 tablas |
| `liquidarSnapshot($id)` | AJAX: elimina snapshots del mes y los reinserta frescos |
| `apiHistorial($id)` | AJAX: historial evolución agrupado por mes, filtrado por año |
| `apiVencimientos($id)` | AJAX: mantenimientos vencidos/próximos a 30 días |
| `generarResumen()` | AJAX POST: envía prompt a OpenAI con métricas + actividades → resumen |
| `getClientes()` | AJAX: lista clientes activos para Select2 |
| `getClientesConVisita()` | API: clientes con actas de visita en periodo |
| `enviar($id)` | Envía informe por email vía SendGrid con PDF adjunto |
| `apiGenerarYEnviar($id)` | API: flujo completo (crear + resumen IA + PDF + email) en 1 call |

### 4.3 Flujos AJAX del formulario (form.php)

1. **Al seleccionar cliente** → `GET /api/metricas/{id}` → llena hidden inputs + renderiza 4 gráficas doughnut + carga actividades abiertas/cerradas
2. **Al seleccionar cliente** → `GET /api/vencimientos/{id}` → renderiza tabla de vencimientos
3. **Al seleccionar cliente** → `GET /api/historial/{id}` → renderiza 2 gráficas de línea (evolución)
4. **Botón "Liquidar Informe"** → `POST /api/liquidar/{id}` → toma snapshot, luego recarga métricas e historial
5. **Botón "Generar con IA"** → `POST /generar-resumen` → envía datos a OpenAI → llena textarea resumen
6. **Submit formulario** → captura imágenes de Chart.js como base64 → las guarda en `metricas_desglose_json` → POST normal
7. **Botón "Finalizar"** → primero guarda (AJAX), luego envía POST a `/finalizar/{id}`

### 4.4 Generación de PDF (DOMPDF)

El PDF se genera server-side en `generarPdfInterno()`:
1. Carga datos del informe, cliente, consultor
2. Convierte logo del cliente a base64
3. Convierte imágenes de soportes a base64
4. Decodifica `metricas_desglose_json` para renderizar barras apiladas (fallback de Chart.js)
5. Genera URLs de QuickChart.io para gráficas de evolución histórica (línea)
6. Renderiza `informe_avances/pdf.php` con DOMPDF (paper: letter, portrait)
7. Guarda en `uploads/informe-avances/pdfs/`
8. Elimina PDF anterior si existe

**Configuración DOMPDF requerida:**
```php
$options->set('isRemoteEnabled', true);     // QuickChart.io images
$options->set('isHtml5ParserEnabled', true);
```

### 4.5 Upload a repositorio de reportes

Al finalizar, el PDF se copia a `public/uploads/{nit_cliente}/` y se registra en `tbl_reporte`:
- `id_report_type = 6`
- `id_detailreport = 37`
- `observaciones` contiene `inf_avance_id:{id}` como marca para upsert

### 4.6 Envío de email (SendGrid)

- Usa **SendGrid SDK PHP** (`\SendGrid\Mail\Mail`)
- From: `notificacion.cycloidtalent@cycloidtalent.com`
- To: `correo_cliente` del cliente
- Adjunta PDF como attachment
- HTML inline con tabla resumen (puntaje + estado avance)
- Env var requerida: `SENDGRID_API_KEY`

### 4.7 Generación de resumen con IA (OpenAI)

- Servicio: `IADocumentacionService` → OpenAI Chat Completions API
- Modelo: `gpt-4o-mini` (configurable vía `OPENAI_MODEL`)
- Prompt incluye: métricas principales + desgloses PHVA + actividades del periodo
- Instrucciones: tercera persona, tono técnico, max 4 párrafos, sin viñetas
- Env var requerida: `OPENAI_API_KEY`

---

## 5. DEPENDENCIAS EXTERNAS

### 5.1 PHP (Composer)

| Paquete | Uso |
|---------|-----|
| `dompdf/dompdf` ^3.0 | Generación PDF server-side |
| `sendgrid/sendgrid` | Envío de emails con adjuntos |

### 5.2 CDN Frontend

| Librería | Versión | Uso |
|----------|---------|-----|
| Bootstrap | 5.3.0 | UI framework |
| jQuery | 3.6.0 | DOM manipulation + AJAX |
| DataTables | 1.13.4 | Tabla paginada en listado |
| Select2 | 4.1.0-rc.0 | Select searchable para clientes |
| Select2 Bootstrap 5 Theme | 1.3.0 | Estilo Bootstrap para Select2 |
| Font Awesome | 6.4.0 | Iconos |
| Chart.js | 4.4.0 | Gráficas doughnut + línea |
| chartjs-plugin-datalabels | 2.2.0 | Labels dentro de las gráficas |
| SweetAlert2 | 11 | Alertas y confirmaciones |

### 5.3 Servicios externos

| Servicio | Uso | Autenticación |
|----------|-----|---------------|
| OpenAI API | Generación de resumen ejecutivo | `OPENAI_API_KEY` env var |
| SendGrid | Envío de email con PDF adjunto | `SENDGRID_API_KEY` env var |
| QuickChart.io | Gráficas de línea en PDF (vía URL) | Sin auth (público) |

---

## 6. PATRONES ESPECIALES

### 6.1 Métricas como snapshot

Las métricas se calculan en tiempo real desde las tablas fuente pero se **almacenan como snapshot** en `tbl_informe_avances`. Esto asegura que el informe conserva los valores del momento en que se creó, aunque los datos fuente cambien después.

### 6.2 Desgloses JSON con imágenes de Chart.js

El campo `metricas_desglose_json` almacena:
```json
{
  "desglose_estandares": [...],
  "desglose_plan_trabajo": [...],
  "desglose_capacitacion": [...],
  "desglose_pendientes": [...],
  "chart_images": {
    "chartEstandares": "data:image/png;base64,...",
    "chartPlanTrabajo": "data:image/png;base64,...",
    "chartCapacitacion": "data:image/png;base64,...",
    "chartPendientes": "data:image/png;base64,..."
  }
}
```
Los `chart_images` se capturan client-side con `canvas.toDataURL()` antes del submit.

### 6.3 Evolución histórica (gráficas de línea)

- **En vistas web**: Chart.js (línea) renderizado client-side
- **En PDF**: QuickChart.io (URL con JSON config) → DOMPDF descarga la imagen

### 6.4 Liquidar snapshot individual

El botón "Liquidar Informe" ejecuta:
1. DELETE snapshots del cliente en el mes actual de `historial_resumen_estandares` e `historial_resumen_plan_trabajo`
2. INSERT frescos desde las VISTAS SQL `resumen_estandares_cliente` y `resumen_mensual_plan_trabajo`

### 6.5 Cálculo de estado de avance

```php
if ($diferencia > 5)  → 'AVANCE SIGNIFICATIVO'
if ($diferencia >= 1) → 'AVANCE MODERADO'
if ($diferencia == 0) → 'ESTABLE'
else                  → 'REINICIO DE CICLO PHVA - BAJA PUNTAJE'
```

### 6.6 Puntaje anterior por defecto

Si es el primer informe del ciclo (año PHVA), el puntaje anterior es **39.75** (línea base Resolución 0312 de 2019).

### 6.7 API programática (OpenClaw)

El endpoint `apiGenerarYEnviar()` permite crear un informe completo en una sola llamada:
1. Calcula fechas y métricas
2. Valida que hubo al menos 1 visita en el periodo
3. Genera resumen con IA
4. Crea informe en BD
5. Genera PDF con DOMPDF
6. Sube a repositorio de reportes
7. Envía por email

### 6.8 Paletas de colores consistentes

```javascript
const COLORS_PHVA = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
const COLORS_ESTADO = ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#6f42c1'];
```

### 6.9 Directorio de uploads

```
uploads/
├── informe-avances/
│   ├── soportes/       ← imágenes de soportes 1-4
│   ├── screenshots/    ← screenshots opcionales de dashboards
│   └── pdfs/           ← PDFs generados
└── {nit_cliente}/      ← copia del PDF para repositorio
```

---

## 7. ORDEN DE IMPLEMENTACIÓN

### Paso 1: Base de datos

1. Ejecutar `migrate_informe_avances.php` — crea la tabla + dato semilla
2. Ejecutar `migrate_informe_metricas_v2.php` — agrega columna `metricas_desglose_json`
3. Verificar que existen las tablas del sistema referenciadas (sección 3.3)
4. Verificar que existen las VISTAS SQL: `resumen_estandares_cliente`, `resumen_mensual_plan_trabajo`

### Paso 2: Modelos

1. Crear `InformeAvancesModel.php` (tabla propia)
2. Verificar que existen: `ClientModel`, `ConsultantModel`, `ReporteModel`, `ActaVisitaModel`, `VencimientosMantenimientoModel`, `HistorialEstandaresModel`, `HistorialPlanTrabajoModel`

### Paso 3: Servicios

1. Crear `app/Libraries/MetricasInformeService.php` — cálculo de métricas desde las 5 tablas fuente
2. Crear `app/Services/IADocumentacionService.php` — wrapper OpenAI API (reutilizable)

### Paso 4: Rutas

1. Agregar grupo `informe-avances` con filter `auth` en `Routes.php`
2. Agregar grupo `ext-api/informe-avances` con filter `authOrApiKey`

### Paso 5: Controlador

1. Crear `InformeAvancesController.php` con todos los métodos
2. Métodos CRUD: list, create, store, edit, update, delete
3. Métodos de vista: view, generatePdf
4. Métodos AJAX: calcularMetricas, apiVencimientos, apiHistorial, getClientes, liquidarSnapshot
5. Métodos IA: generarResumen
6. Métodos email: enviar, enviarInterno
7. Método API: apiGenerarYEnviar, getClientesConVisita
8. Métodos privados: getInformePostData, getVencimientosCliente, uploadFoto, generarPdfInterno, uploadToReportes, buildResumenPrompt, formatDesgloseForPrompt, getHistorialEstandaresCliente, getHistorialPlanCliente, agruparHistorialPorMes, buildQuickChartUrl

### Paso 6: Vistas

1. `list.php` — DataTables + filtros por cliente y año
2. `form.php` — formulario con 7 secciones, Chart.js, Select2, IA, captura base64
3. `view.php` — vista solo-lectura con gráficas y evolución histórica
4. `pdf.php` — template DOMPDF con tablas, barras apiladas, QuickChart

### Paso 7: Variables de entorno

```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
SENDGRID_API_KEY=SG...
```

### Paso 8: Directorios de upload

```bash
mkdir -p public/uploads/informe-avances/{soportes,screenshots,pdfs}
chmod 755 public/uploads/informe-avances
```

---

## 8. CÓDIGO CLAVE — Fragmentos de Referencia

### 8.1 Model: allowedFields

```php
protected $allowedFields = [
    'id_cliente', 'id_consultor',
    'fecha_desde', 'fecha_hasta', 'anio',
    'puntaje_anterior', 'puntaje_actual', 'diferencia_neta', 'estado_avance',
    'indicador_plan_trabajo', 'indicador_capacitacion',
    'img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion',
    'metricas_desglose_json',
    'resumen_avance', 'observaciones', 'actividades_abiertas', 'actividades_cerradas_periodo',
    'enlace_dashboard', 'acta_visita_url',
    'soporte_1_texto', 'soporte_1_imagen', 'soporte_2_texto', 'soporte_2_imagen',
    'soporte_3_texto', 'soporte_3_imagen', 'soporte_4_texto', 'soporte_4_imagen',
    'ruta_pdf', 'estado',
];
```

### 8.2 MetricasInformeService::calcularTodas() — firma

```php
public function calcularTodas(int $idCliente, string $fechaDesde, string $fechaHasta, int $anio): array
```
Retorna array con: `puntaje_actual`, `puntaje_anterior`, `diferencia_neta`, `estado_avance`, `indicador_plan_trabajo`, `indicador_capacitacion`, `actividades_abiertas`, `actividades_cerradas_periodo`, `actividades_cerradas_raw`, `enlace_dashboard`, `fecha_desde_sugerida`, `desglose_estandares`, `desglose_plan_trabajo`, `desglose_capacitacion`, `desglose_pendientes`.

### 8.3 Upload a reportes — parámetros clave

```php
'id_report_type'  => 6,
'id_detailreport' => 37,
'observaciones'   => 'Generado automaticamente. inf_avance_id:' . $informe['id'],
```

### 8.4 Prompt IA — estructura

```
Eres un consultor senior de SG-SST en Colombia.
Genera un resumen ejecutivo de avance del SG-SST para "{cliente}" periodo {desde} a {hasta}.

INDICADORES PRINCIPALES:
- Puntaje actual / anterior / diferencia / estado
- Plan de trabajo / Capacitación

DESGLOSE POR CICLO PHVA: [datos numéricos]
ESTADO PLAN DE TRABAJO: [conteo por estado_actividad]
ESTADO CAPACITACIÓN: [conteo por estado]
ESTADO PENDIENTES: [conteo + promedio días]

ACTIVIDADES DEL PERIODO:
[lista automática de visitas, capacitaciones, PTA cerradas, compromisos cerrados]

INSTRUCCIONES:
1. Tercera persona, tono técnico
2. Mencionar actividades relevantes
3. Analizar tendencia + ciclo PHVA débil
4. Max 4 párrafos, prosa continua
```

### 8.5 Formato de actividades cerradas (texto almacenado)

```
- [numeral] actividad | PHVA: ciclo | Resp: responsable | Cerrada: dd/mm/yyyy
```

### 8.6 QuickChart.io URL — estructura

```php
'https://quickchart.io/chart?width=380&height=160&c=' . urlencode(json_encode($config))
```
Donde `$config` es un objeto Chart.js config estándar (type: line).

---

## 9. DISEÑO VISUAL — CSS Variables

```css
:root {
    --primary-dark: #1c2437;
    --gold-primary: #bd9751;
    --gold-secondary: #d4af37;
    --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
```

Badges de estado de avance:
| Estado | Color |
|--------|-------|
| AVANCE SIGNIFICATIVO | `#28a745` (verde) |
| AVANCE MODERADO | `#17a2b8` (cyan) |
| ESTABLE | `#ffc107` (amarillo) |
| REINICIO | `#dc3545` (rojo) |

---

## 10. NOTAS PARA REPLICACIÓN

1. **DOMPDF no soporta Flexbox/Grid** — todo el PDF usa tablas HTML para layout
2. **@page margin en DOMPDF** debe usar `px`, no `cm`
3. **Logo del cliente** se carga desde `FCPATH . 'uploads/' . $cliente['logo']` y se convierte a base64
4. **Chart.js en PDF** no es posible — se usa QuickChart.io (URL remota) para las gráficas de línea, y barras apiladas (tablas HTML) como fallback para doughnuts
5. **El formulario captura los canvas Chart.js como PNG base64** antes del submit usando `requestAnimationFrame` doble
6. **SendGrid usa SDK** (`\SendGrid\Mail\Mail`), no cURL directo
7. **El endpoint apiGenerarYEnviar** es el flujo completo para automatización sin UI
8. **CSRF** se envía en el body del AJAX de liquidación
9. **Recordatorio SweetAlert** aparece al crear informe nuevo, pidiendo verificar que estándares/PTA/capacitaciones estén actualizados
