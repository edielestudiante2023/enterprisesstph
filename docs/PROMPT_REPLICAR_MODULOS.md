# PROMPT PARA REPLICAR MÓDULO PWA INSPECCIONES EN APLICATIVO GEMELO

## Contexto

Módulo completo PWA "Inspecciones SST" con 34 controladores, 35 modelos, ~131 vistas, 31 migraciones SQL, 2 formularios públicos y dashboard unificado con 33 cards.

**Stack:** CodeIgniter 4, PHP 8.2, MySQL 8, DOMPDF para PDFs, Bootstrap 5 PWA.

---

## 1. CONTROLADORES (34 archivos)

### Dentro del grupo Inspecciones (autenticados — 32 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionesController.php          — Dashboard principal
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ActaVisitaController.php             — Acta de visita
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionLocativaController.php     — Inspección locativa
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionSenalizacionController.php — Señalización (37 ítems)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionExtintoresController.php   — Extintores (N dinámico)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionBotiquinController.php     — Botiquín (32 elementos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionGabineteController.php     — Gabinetes (N dinámico)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionComunicacionController.php — Comunicaciones
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionRecursosSeguridadController.php — Recursos seguridad
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ProbabilidadPeligrosController.php  — Probabilidad peligros (11 items)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\MatrizVulnerabilidadController.php  — Matriz vulnerabilidad (25 criterios)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PlanEmergenciaController.php        — Plan emergencia (doc maestro, ~82 cols, 19 fotos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\EvaluacionSimulacroController.php   — Evaluación simulacro (277 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\HvBrigadistaController.php          — Hoja de vida brigadista (288 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\DotacionVigilanteController.php     — Dotación vigilante (398 líneas, 7 EPP)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\DotacionAseadoraController.php      — Dotación aseadora (399 líneas, 8 EPP)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\DotacionToderoController.php        — Dotación todero (407 líneas, 16 EPP)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\AuditoriaZonaResiduosController.php — Auditoría zona residuos (412 líneas, 12 ítems + 12 fotos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PreparacionSimulacroController.php  — Preparación simulacro (436 líneas, 9 TIME + EnumLists)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ReporteCapacitacionController.php   — Reporte capacitación (382 líneas, 5 fotos)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\AsistenciaInduccionController.php   — Asistencia inducción (516 líneas, master-detalle + firmas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ProgramaLimpiezaController.php      — Programa limpieza y desinfección (306 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ProgramaResiduosController.php     — Programa manejo integral de residuos sólidos
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ProgramaPlagasController.php       — Programa control integrado de plagas
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\ProgramaAguaPotableController.php  — Programa abastecimiento agua potable
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PlanSaneamientoController.php      — Plan de saneamiento básico
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\KpiLimpiezaController.php          — KPI limpieza y desinfección (FT-SST-229)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\KpiResiduosController.php          — KPI residuos sólidos (FT-SST-230)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\KpiPlagasController.php            — KPI control de plagas (FT-SST-231)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\KpiAguaPotableController.php       — KPI agua potable (FT-SST-232)
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\UrlsPwaController.php              — Accesos rápidos / URLs
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\CartaVigiaPwaController.php         — Carta vigía SST
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\MantenimientosPwaController.php     — Mantenimientos
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PendientesPwaController.php         — Pendientes/compromisos
```

### Controladores públicos (SIN autenticación)
```
c:\xampp\htdocs\enterprisesstph\app\Controllers\SimulacroPublicoController.php        — Formulario público evaluación simulacro (280 líneas)
c:\xampp\htdocs\enterprisesstph\app\Controllers\HvBrigadistaPublicoController.php     — Formulario público HV brigadista (198 líneas)
```

---

## 2. MODELOS (35 archivos relevantes al módulo)

```
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaFotoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaIntegranteModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ActaVisitaTemaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionLocativaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\HallazgoLocativoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionSenalizacionModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ItemSenalizacionModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionExtintoresModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ExtintorDetalleModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionBotiquinModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ElementoBotiquinModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionGabineteModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\GabineteDetalleModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionComunicacionModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\InspeccionRecursosSeguridadModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ProbabilidadPeligrosModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\MatrizVulnerabilidadModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\PlanEmergenciaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\EvaluacionSimulacroModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\HvBrigadistaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ProgramaResiduosModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ProgramaPlagasModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\ProgramaAguaPotableModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\PlanSaneamientoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\KpiLimpiezaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\KpiResiduosModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\KpiPlagasModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\KpiAguaPotableModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\UrlModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\CartaVigiaModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\MantenimientoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\VencimientosMantenimientoModel.php
c:\xampp\htdocs\enterprisesstph\app\Models\PendientesModel.php
```

---

## 3. VISTAS (~131 archivos)

### Layout compartido PWA
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\layout_pwa.php
```

### Dashboard
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\dashboard.php
```

### Acta de Visita (5 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\pdf.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\acta_visita\firma.php
```

### Inspección Locativa (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\inspeccion_locativa\pdf.php
```

### Señalización (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\senalizacion\pdf.php
```

### Extintores (5 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\pdf.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\extintores\_extintor_row.php
```

### Botiquín (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\botiquin\pdf.php
```

### Gabinetes (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\gabinetes\pdf.php
```

### Comunicaciones (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\comunicaciones\pdf.php
```

### Recursos de Seguridad (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\recursos-seguridad\pdf.php
```

### Probabilidad de Peligros (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\probabilidad-peligros\pdf.php
```

### Matriz de Vulnerabilidad (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\matriz-vulnerabilidad\pdf.php
```

### Plan de Emergencia (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\pdf.php
```

### Evaluación Simulacro (3 archivos internos + 1 público)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\simulacro\list.php        (128 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\simulacro\view.php        (160 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\simulacro\pdf.php         (266 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\simulacro\form_publico.php             (973 líneas — formulario público)
```

### Hoja de Vida Brigadista (3 archivos internos + 1 público)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\hv-brigadista\list.php    (121 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\hv-brigadista\view.php    (142 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\hv-brigadista\pdf.php     (265 líneas)
c:\xampp\htdocs\enterprisesstph\app\Views\hv-brigadista\form_publico.php         (715 líneas — formulario público)
```

### Carta Vigía (7 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\pdf.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\firma.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\firma_error.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\firma_success.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\carta_vigia\email_firma.php
```

### Mantenimientos (2 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\mantenimientos\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\mantenimientos\form.php
```

### Pendientes (2 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\pendientes\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\pendientes\form.php
```

### Residuos Sólidos (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\residuos-solidos\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\residuos-solidos\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\residuos-solidos\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\residuos-solidos\pdf.php
```

### Control de Plagas (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\control-plagas\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\control-plagas\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\control-plagas\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\control-plagas\pdf.php
```

### Agua Potable (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\agua-potable\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\agua-potable\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\agua-potable\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\agua-potable\pdf.php
```

### Plan de Saneamiento (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-saneamiento\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-saneamiento\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-saneamiento\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-saneamiento\pdf.php
```

### KPI Limpieza (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-limpieza\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-limpieza\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-limpieza\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-limpieza\pdf.php
```

### KPI Residuos (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-residuos\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-residuos\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-residuos\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-residuos\pdf.php
```

### KPI Plagas (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-plagas\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-plagas\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-plagas\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-plagas\pdf.php
```

### KPI Agua Potable (4 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-agua-potable\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-agua-potable\form.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-agua-potable\view.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\kpi-agua-potable\pdf.php
```

### URLs / Accesos Rápidos (2 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\urls\list.php
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\urls\form.php
```

---

## 4. MIGRACIONES SQL (31 archivos)

```
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_acta_visita.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_locativa.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_senalizacion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_extintores.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_botiquin.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_gabinetes.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_comunicaciones.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_inspeccion_recursos_seguridad.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_probabilidad_peligros.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_matriz_vulnerabilidad.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_plan_emergencia.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_reporte_capacitacion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_asistencia_induccion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_vigilante.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_todero.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_aseadora.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_auditoria_zona_residuos.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_preparacion_simulacro.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_evaluacion_simulacro.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_hv_brigadista.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_carta_vigia.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_firma_digital.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_codigo_verificacion.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_pta_audit_table.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_kpis.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_plan_saneamiento.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_programa_agua_potable.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_programa_plagas.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_programa_residuos.php
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_urls.php
```

### Ejecución
```bash
cd app/SQL
php migrate_NOMBRE.php              # LOCAL
DB_PROD_PASS=xxx php migrate_NOMBRE.php production   # PRODUCCIÓN
```

---

## 5. RUTAS — `c:\xampp\htdocs\enterprisesstph\app\Config\Routes.php`

### Grupo autenticado: `/inspecciones` (namespace `App\Controllers\Inspecciones`, filter `auth`)

```php
$routes->group('inspecciones', ['namespace' => 'App\Controllers\Inspecciones', 'filter' => 'auth'], function($routes) {

    // Dashboard
    $routes->get('/', 'InspeccionesController::dashboard');
    $routes->get('api/clientes', 'InspeccionesController::getClientes');

    // Acta de Visita (~12 rutas)
    $routes->get('acta-visita', 'ActaVisitaController::list');
    $routes->get('acta-visita/create', 'ActaVisitaController::create');
    $routes->get('acta-visita/create/(:num)', 'ActaVisitaController::create/$1');
    $routes->post('acta-visita/store', 'ActaVisitaController::store');
    $routes->get('acta-visita/edit/(:num)', 'ActaVisitaController::edit/$1');
    $routes->post('acta-visita/update/(:num)', 'ActaVisitaController::update/$1');
    $routes->get('acta-visita/view/(:num)', 'ActaVisitaController::view/$1');
    $routes->get('acta-visita/pdf/(:num)', 'ActaVisitaController::generatePdf/$1');
    $routes->post('acta-visita/finalizar/(:num)', 'ActaVisitaController::finalizar/$1');
    $routes->get('acta-visita/delete/(:num)', 'ActaVisitaController::delete/$1');
    $routes->post('acta-visita/enviar-firma/(:num)', 'ActaVisitaController::enviarFirma/$1');
    $routes->post('acta-visita/upload-foto', 'ActaVisitaController::uploadFoto');

    // Inspección Locativa (10 rutas)
    $routes->get('inspeccion-locativa', 'InspeccionLocativaController::list');
    $routes->get('inspeccion-locativa/create', 'InspeccionLocativaController::create');
    $routes->get('inspeccion-locativa/create/(:num)', 'InspeccionLocativaController::create/$1');
    $routes->post('inspeccion-locativa/store', 'InspeccionLocativaController::store');
    $routes->get('inspeccion-locativa/edit/(:num)', 'InspeccionLocativaController::edit/$1');
    $routes->post('inspeccion-locativa/update/(:num)', 'InspeccionLocativaController::update/$1');
    $routes->get('inspeccion-locativa/view/(:num)', 'InspeccionLocativaController::view/$1');
    $routes->get('inspeccion-locativa/pdf/(:num)', 'InspeccionLocativaController::generatePdf/$1');
    $routes->post('inspeccion-locativa/finalizar/(:num)', 'InspeccionLocativaController::finalizar/$1');
    $routes->get('inspeccion-locativa/delete/(:num)', 'InspeccionLocativaController::delete/$1');

    // Señalización (10 rutas)
    $routes->get('senalizacion', 'InspeccionSenalizacionController::list');
    $routes->get('senalizacion/create', 'InspeccionSenalizacionController::create');
    $routes->get('senalizacion/create/(:num)', 'InspeccionSenalizacionController::create/$1');
    $routes->post('senalizacion/store', 'InspeccionSenalizacionController::store');
    $routes->get('senalizacion/edit/(:num)', 'InspeccionSenalizacionController::edit/$1');
    $routes->post('senalizacion/update/(:num)', 'InspeccionSenalizacionController::update/$1');
    $routes->get('senalizacion/view/(:num)', 'InspeccionSenalizacionController::view/$1');
    $routes->get('senalizacion/pdf/(:num)', 'InspeccionSenalizacionController::generatePdf/$1');
    $routes->post('senalizacion/finalizar/(:num)', 'InspeccionSenalizacionController::finalizar/$1');
    $routes->get('senalizacion/delete/(:num)', 'InspeccionSenalizacionController::delete/$1');

    // Extintores (10 rutas)
    $routes->get('extintores', 'InspeccionExtintoresController::list');
    $routes->get('extintores/create', 'InspeccionExtintoresController::create');
    $routes->get('extintores/create/(:num)', 'InspeccionExtintoresController::create/$1');
    $routes->post('extintores/store', 'InspeccionExtintoresController::store');
    $routes->get('extintores/edit/(:num)', 'InspeccionExtintoresController::edit/$1');
    $routes->post('extintores/update/(:num)', 'InspeccionExtintoresController::update/$1');
    $routes->get('extintores/view/(:num)', 'InspeccionExtintoresController::view/$1');
    $routes->get('extintores/pdf/(:num)', 'InspeccionExtintoresController::generatePdf/$1');
    $routes->post('extintores/finalizar/(:num)', 'InspeccionExtintoresController::finalizar/$1');
    $routes->get('extintores/delete/(:num)', 'InspeccionExtintoresController::delete/$1');

    // Botiquín (10 rutas)
    $routes->get('botiquin', 'InspeccionBotiquinController::list');
    $routes->get('botiquin/create', 'InspeccionBotiquinController::create');
    $routes->get('botiquin/create/(:num)', 'InspeccionBotiquinController::create/$1');
    $routes->post('botiquin/store', 'InspeccionBotiquinController::store');
    $routes->get('botiquin/edit/(:num)', 'InspeccionBotiquinController::edit/$1');
    $routes->post('botiquin/update/(:num)', 'InspeccionBotiquinController::update/$1');
    $routes->get('botiquin/view/(:num)', 'InspeccionBotiquinController::view/$1');
    $routes->get('botiquin/pdf/(:num)', 'InspeccionBotiquinController::generatePdf/$1');
    $routes->post('botiquin/finalizar/(:num)', 'InspeccionBotiquinController::finalizar/$1');
    $routes->get('botiquin/delete/(:num)', 'InspeccionBotiquinController::delete/$1');

    // Gabinetes (10 rutas)
    $routes->get('gabinetes', 'InspeccionGabineteController::list');
    $routes->get('gabinetes/create', 'InspeccionGabineteController::create');
    $routes->get('gabinetes/create/(:num)', 'InspeccionGabineteController::create/$1');
    $routes->post('gabinetes/store', 'InspeccionGabineteController::store');
    $routes->get('gabinetes/edit/(:num)', 'InspeccionGabineteController::edit/$1');
    $routes->post('gabinetes/update/(:num)', 'InspeccionGabineteController::update/$1');
    $routes->get('gabinetes/view/(:num)', 'InspeccionGabineteController::view/$1');
    $routes->get('gabinetes/pdf/(:num)', 'InspeccionGabineteController::generatePdf/$1');
    $routes->post('gabinetes/finalizar/(:num)', 'InspeccionGabineteController::finalizar/$1');
    $routes->get('gabinetes/delete/(:num)', 'InspeccionGabineteController::delete/$1');

    // Comunicaciones (10 rutas)
    $routes->get('comunicaciones', 'InspeccionComunicacionController::list');
    $routes->get('comunicaciones/create', 'InspeccionComunicacionController::create');
    $routes->get('comunicaciones/create/(:num)', 'InspeccionComunicacionController::create/$1');
    $routes->post('comunicaciones/store', 'InspeccionComunicacionController::store');
    $routes->get('comunicaciones/edit/(:num)', 'InspeccionComunicacionController::edit/$1');
    $routes->post('comunicaciones/update/(:num)', 'InspeccionComunicacionController::update/$1');
    $routes->get('comunicaciones/view/(:num)', 'InspeccionComunicacionController::view/$1');
    $routes->get('comunicaciones/pdf/(:num)', 'InspeccionComunicacionController::generatePdf/$1');
    $routes->post('comunicaciones/finalizar/(:num)', 'InspeccionComunicacionController::finalizar/$1');
    $routes->get('comunicaciones/delete/(:num)', 'InspeccionComunicacionController::delete/$1');

    // Recursos de Seguridad (10 rutas)
    $routes->get('recursos-seguridad', 'InspeccionRecursosSeguridadController::list');
    $routes->get('recursos-seguridad/create', 'InspeccionRecursosSeguridadController::create');
    $routes->get('recursos-seguridad/create/(:num)', 'InspeccionRecursosSeguridadController::create/$1');
    $routes->post('recursos-seguridad/store', 'InspeccionRecursosSeguridadController::store');
    $routes->get('recursos-seguridad/edit/(:num)', 'InspeccionRecursosSeguridadController::edit/$1');
    $routes->post('recursos-seguridad/update/(:num)', 'InspeccionRecursosSeguridadController::update/$1');
    $routes->get('recursos-seguridad/view/(:num)', 'InspeccionRecursosSeguridadController::view/$1');
    $routes->get('recursos-seguridad/pdf/(:num)', 'InspeccionRecursosSeguridadController::generatePdf/$1');
    $routes->post('recursos-seguridad/finalizar/(:num)', 'InspeccionRecursosSeguridadController::finalizar/$1');
    $routes->get('recursos-seguridad/delete/(:num)', 'InspeccionRecursosSeguridadController::delete/$1');

    // Probabilidad de Peligros (10 rutas)
    $routes->get('probabilidad-peligros', 'ProbabilidadPeligrosController::list');
    $routes->get('probabilidad-peligros/create', 'ProbabilidadPeligrosController::create');
    $routes->get('probabilidad-peligros/create/(:num)', 'ProbabilidadPeligrosController::create/$1');
    $routes->post('probabilidad-peligros/store', 'ProbabilidadPeligrosController::store');
    $routes->get('probabilidad-peligros/edit/(:num)', 'ProbabilidadPeligrosController::edit/$1');
    $routes->post('probabilidad-peligros/update/(:num)', 'ProbabilidadPeligrosController::update/$1');
    $routes->get('probabilidad-peligros/view/(:num)', 'ProbabilidadPeligrosController::view/$1');
    $routes->get('probabilidad-peligros/pdf/(:num)', 'ProbabilidadPeligrosController::generatePdf/$1');
    $routes->post('probabilidad-peligros/finalizar/(:num)', 'ProbabilidadPeligrosController::finalizar/$1');
    $routes->get('probabilidad-peligros/delete/(:num)', 'ProbabilidadPeligrosController::delete/$1');

    // Matriz de Vulnerabilidad (10 rutas)
    $routes->get('matriz-vulnerabilidad', 'MatrizVulnerabilidadController::list');
    $routes->get('matriz-vulnerabilidad/create', 'MatrizVulnerabilidadController::create');
    $routes->get('matriz-vulnerabilidad/create/(:num)', 'MatrizVulnerabilidadController::create/$1');
    $routes->post('matriz-vulnerabilidad/store', 'MatrizVulnerabilidadController::store');
    $routes->get('matriz-vulnerabilidad/edit/(:num)', 'MatrizVulnerabilidadController::edit/$1');
    $routes->post('matriz-vulnerabilidad/update/(:num)', 'MatrizVulnerabilidadController::update/$1');
    $routes->get('matriz-vulnerabilidad/view/(:num)', 'MatrizVulnerabilidadController::view/$1');
    $routes->get('matriz-vulnerabilidad/pdf/(:num)', 'MatrizVulnerabilidadController::generatePdf/$1');
    $routes->post('matriz-vulnerabilidad/finalizar/(:num)', 'MatrizVulnerabilidadController::finalizar/$1');
    $routes->get('matriz-vulnerabilidad/delete/(:num)', 'MatrizVulnerabilidadController::delete/$1');

    // Plan de Emergencia (12 rutas — incluye check-inspecciones)
    $routes->get('plan-emergencia', 'PlanEmergenciaController::list');
    $routes->get('plan-emergencia/create', 'PlanEmergenciaController::create');
    $routes->get('plan-emergencia/create/(:num)', 'PlanEmergenciaController::create/$1');
    $routes->post('plan-emergencia/store', 'PlanEmergenciaController::store');
    $routes->get('plan-emergencia/edit/(:num)', 'PlanEmergenciaController::edit/$1');
    $routes->post('plan-emergencia/update/(:num)', 'PlanEmergenciaController::update/$1');
    $routes->get('plan-emergencia/view/(:num)', 'PlanEmergenciaController::view/$1');
    $routes->get('plan-emergencia/pdf/(:num)', 'PlanEmergenciaController::generatePdf/$1');
    $routes->post('plan-emergencia/finalizar/(:num)', 'PlanEmergenciaController::finalizar/$1');
    $routes->get('plan-emergencia/delete/(:num)', 'PlanEmergenciaController::delete/$1');
    $routes->get('plan-emergencia/check-inspecciones/(:num)', 'PlanEmergenciaController::checkInspeccionesCompletas/$1');

    // Evaluación Simulacro (5 rutas — solo lectura, viene de formulario público)
    $routes->get('simulacro', 'EvaluacionSimulacroController::list');
    $routes->get('simulacro/view/(:num)', 'EvaluacionSimulacroController::view/$1');
    $routes->get('simulacro/pdf/(:num)', 'EvaluacionSimulacroController::generatePdf/$1');
    $routes->post('simulacro/finalizar/(:num)', 'EvaluacionSimulacroController::finalizar/$1');
    $routes->get('simulacro/delete/(:num)', 'EvaluacionSimulacroController::delete/$1');

    // Hoja de Vida Brigadista (5 rutas — solo lectura, viene de formulario público)
    $routes->get('hv-brigadista', 'HvBrigadistaController::list');
    $routes->get('hv-brigadista/view/(:num)', 'HvBrigadistaController::view/$1');
    $routes->get('hv-brigadista/pdf/(:num)', 'HvBrigadistaController::generatePdf/$1');
    $routes->post('hv-brigadista/finalizar/(:num)', 'HvBrigadistaController::finalizar/$1');
    $routes->get('hv-brigadista/delete/(:num)', 'HvBrigadistaController::delete/$1');

    // Pendientes / Compromisos
    $routes->get('pendientes', 'PendientesPwaController::list');
    $routes->get('pendientes/cliente/(:num)', 'PendientesPwaController::list/$1');
    $routes->get('pendientes/create/(:num)', 'PendientesPwaController::create/$1');
    $routes->post('pendientes/store', 'PendientesPwaController::store');
    $routes->get('pendientes/edit/(:num)', 'PendientesPwaController::edit/$1');
    $routes->post('pendientes/update/(:num)', 'PendientesPwaController::update/$1');
    $routes->get('pendientes/delete/(:num)', 'PendientesPwaController::delete/$1');

    // Mantenimientos
    $routes->get('mantenimientos', 'MantenimientosPwaController::list');
    $routes->get('api/mantenimientos-catalog', 'MantenimientosPwaController::apiCatalog');
    $routes->get('api/vencimientos/(:num)', 'MantenimientosPwaController::apiVencimientos/$1');

    // Residuos Sólidos (10 rutas)
    $routes->get('residuos-solidos', 'ProgramaResiduosController::list');
    $routes->get('residuos-solidos/create', 'ProgramaResiduosController::create');
    $routes->get('residuos-solidos/create/(:num)', 'ProgramaResiduosController::create/$1');
    $routes->post('residuos-solidos/store', 'ProgramaResiduosController::store');
    $routes->get('residuos-solidos/edit/(:num)', 'ProgramaResiduosController::edit/$1');
    $routes->post('residuos-solidos/update/(:num)', 'ProgramaResiduosController::update/$1');
    $routes->get('residuos-solidos/view/(:num)', 'ProgramaResiduosController::view/$1');
    $routes->get('residuos-solidos/pdf/(:num)', 'ProgramaResiduosController::generatePdf/$1');
    $routes->post('residuos-solidos/finalizar/(:num)', 'ProgramaResiduosController::finalizar/$1');
    $routes->get('residuos-solidos/delete/(:num)', 'ProgramaResiduosController::delete/$1');

    // Control de Plagas (10 rutas)
    $routes->get('control-plagas', 'ProgramaPlagasController::list');
    $routes->get('control-plagas/create', 'ProgramaPlagasController::create');
    $routes->get('control-plagas/create/(:num)', 'ProgramaPlagasController::create/$1');
    $routes->post('control-plagas/store', 'ProgramaPlagasController::store');
    $routes->get('control-plagas/edit/(:num)', 'ProgramaPlagasController::edit/$1');
    $routes->post('control-plagas/update/(:num)', 'ProgramaPlagasController::update/$1');
    $routes->get('control-plagas/view/(:num)', 'ProgramaPlagasController::view/$1');
    $routes->get('control-plagas/pdf/(:num)', 'ProgramaPlagasController::generatePdf/$1');
    $routes->post('control-plagas/finalizar/(:num)', 'ProgramaPlagasController::finalizar/$1');
    $routes->get('control-plagas/delete/(:num)', 'ProgramaPlagasController::delete/$1');

    // Agua Potable (10 rutas)
    $routes->get('agua-potable', 'ProgramaAguaPotableController::list');
    $routes->get('agua-potable/create', 'ProgramaAguaPotableController::create');
    $routes->get('agua-potable/create/(:num)', 'ProgramaAguaPotableController::create/$1');
    $routes->post('agua-potable/store', 'ProgramaAguaPotableController::store');
    $routes->get('agua-potable/edit/(:num)', 'ProgramaAguaPotableController::edit/$1');
    $routes->post('agua-potable/update/(:num)', 'ProgramaAguaPotableController::update/$1');
    $routes->get('agua-potable/view/(:num)', 'ProgramaAguaPotableController::view/$1');
    $routes->get('agua-potable/pdf/(:num)', 'ProgramaAguaPotableController::generatePdf/$1');
    $routes->post('agua-potable/finalizar/(:num)', 'ProgramaAguaPotableController::finalizar/$1');
    $routes->get('agua-potable/delete/(:num)', 'ProgramaAguaPotableController::delete/$1');

    // Plan de Saneamiento (10 rutas)
    $routes->get('plan-saneamiento', 'PlanSaneamientoController::list');
    $routes->get('plan-saneamiento/create', 'PlanSaneamientoController::create');
    $routes->get('plan-saneamiento/create/(:num)', 'PlanSaneamientoController::create/$1');
    $routes->post('plan-saneamiento/store', 'PlanSaneamientoController::store');
    $routes->get('plan-saneamiento/edit/(:num)', 'PlanSaneamientoController::edit/$1');
    $routes->post('plan-saneamiento/update/(:num)', 'PlanSaneamientoController::update/$1');
    $routes->get('plan-saneamiento/view/(:num)', 'PlanSaneamientoController::view/$1');
    $routes->get('plan-saneamiento/pdf/(:num)', 'PlanSaneamientoController::generatePdf/$1');
    $routes->post('plan-saneamiento/finalizar/(:num)', 'PlanSaneamientoController::finalizar/$1');
    $routes->get('plan-saneamiento/delete/(:num)', 'PlanSaneamientoController::delete/$1');

    // KPI Limpieza (10 rutas)
    $routes->get('kpi-limpieza', 'KpiLimpiezaController::list');
    $routes->get('kpi-limpieza/create', 'KpiLimpiezaController::create');
    $routes->get('kpi-limpieza/create/(:num)', 'KpiLimpiezaController::create/$1');
    $routes->post('kpi-limpieza/store', 'KpiLimpiezaController::store');
    $routes->get('kpi-limpieza/edit/(:num)', 'KpiLimpiezaController::edit/$1');
    $routes->post('kpi-limpieza/update/(:num)', 'KpiLimpiezaController::update/$1');
    $routes->get('kpi-limpieza/view/(:num)', 'KpiLimpiezaController::view/$1');
    $routes->get('kpi-limpieza/pdf/(:num)', 'KpiLimpiezaController::generatePdf/$1');
    $routes->post('kpi-limpieza/finalizar/(:num)', 'KpiLimpiezaController::finalizar/$1');
    $routes->get('kpi-limpieza/delete/(:num)', 'KpiLimpiezaController::delete/$1');

    // KPI Residuos (10 rutas)
    $routes->get('kpi-residuos', 'KpiResiduosController::list');
    $routes->get('kpi-residuos/create', 'KpiResiduosController::create');
    $routes->get('kpi-residuos/create/(:num)', 'KpiResiduosController::create/$1');
    $routes->post('kpi-residuos/store', 'KpiResiduosController::store');
    $routes->get('kpi-residuos/edit/(:num)', 'KpiResiduosController::edit/$1');
    $routes->post('kpi-residuos/update/(:num)', 'KpiResiduosController::update/$1');
    $routes->get('kpi-residuos/view/(:num)', 'KpiResiduosController::view/$1');
    $routes->get('kpi-residuos/pdf/(:num)', 'KpiResiduosController::generatePdf/$1');
    $routes->post('kpi-residuos/finalizar/(:num)', 'KpiResiduosController::finalizar/$1');
    $routes->get('kpi-residuos/delete/(:num)', 'KpiResiduosController::delete/$1');

    // KPI Plagas (10 rutas)
    $routes->get('kpi-plagas', 'KpiPlagasController::list');
    $routes->get('kpi-plagas/create', 'KpiPlagasController::create');
    $routes->get('kpi-plagas/create/(:num)', 'KpiPlagasController::create/$1');
    $routes->post('kpi-plagas/store', 'KpiPlagasController::store');
    $routes->get('kpi-plagas/edit/(:num)', 'KpiPlagasController::edit/$1');
    $routes->post('kpi-plagas/update/(:num)', 'KpiPlagasController::update/$1');
    $routes->get('kpi-plagas/view/(:num)', 'KpiPlagasController::view/$1');
    $routes->get('kpi-plagas/pdf/(:num)', 'KpiPlagasController::generatePdf/$1');
    $routes->post('kpi-plagas/finalizar/(:num)', 'KpiPlagasController::finalizar/$1');
    $routes->get('kpi-plagas/delete/(:num)', 'KpiPlagasController::delete/$1');

    // KPI Agua Potable (10 rutas)
    $routes->get('kpi-agua-potable', 'KpiAguaPotableController::list');
    $routes->get('kpi-agua-potable/create', 'KpiAguaPotableController::create');
    $routes->get('kpi-agua-potable/create/(:num)', 'KpiAguaPotableController::create/$1');
    $routes->post('kpi-agua-potable/store', 'KpiAguaPotableController::store');
    $routes->get('kpi-agua-potable/edit/(:num)', 'KpiAguaPotableController::edit/$1');
    $routes->post('kpi-agua-potable/update/(:num)', 'KpiAguaPotableController::update/$1');
    $routes->get('kpi-agua-potable/view/(:num)', 'KpiAguaPotableController::view/$1');
    $routes->get('kpi-agua-potable/pdf/(:num)', 'KpiAguaPotableController::generatePdf/$1');
    $routes->post('kpi-agua-potable/finalizar/(:num)', 'KpiAguaPotableController::finalizar/$1');
    $routes->get('kpi-agua-potable/delete/(:num)', 'KpiAguaPotableController::delete/$1');

    // URLs / Accesos Rápidos (6 rutas)
    $routes->get('urls', 'UrlsPwaController::list');
    $routes->get('urls/create', 'UrlsPwaController::create');
    $routes->post('urls/store', 'UrlsPwaController::store');
    $routes->get('urls/edit/(:num)', 'UrlsPwaController::edit/$1');
    $routes->post('urls/update/(:num)', 'UrlsPwaController::update/$1');
    $routes->get('urls/delete/(:num)', 'UrlsPwaController::delete/$1');

    // Carta Vigía
    $routes->get('carta-vigia', 'CartaVigiaPwaController::list');
    // ... (más rutas carta vigía)
});
```

### Rutas públicas (SIN autenticación)

```php
// Formulario público Evaluación Simulacro
$routes->get('simulacro', 'SimulacroPublicoController::form');
$routes->get('simulacro/api/clientes', 'SimulacroPublicoController::getClientesActivos');
$routes->post('simulacro/save-step', 'SimulacroPublicoController::saveStep');
$routes->post('simulacro/upload-foto', 'SimulacroPublicoController::uploadFoto');
$routes->post('simulacro/store', 'SimulacroPublicoController::store');

// Formulario público HV Brigadista
$routes->get('hv-brigadista', 'HvBrigadistaPublicoController::form');
$routes->get('hv-brigadista/api/clientes', 'HvBrigadistaPublicoController::getClientesActivos');
$routes->post('hv-brigadista/store', 'HvBrigadistaPublicoController::store');

// Carta Vigía pública (firma)
$routes->get('carta-vigia/firmar/(:any)', 'Inspecciones\CartaVigiaPwaController::firmar/$1');
$routes->post('carta-vigia/procesar-firma', 'Inspecciones\CartaVigiaPwaController::procesarFirma');
$routes->get('carta-vigia/verificar/(:any)', 'Inspecciones\CartaVigiaPwaController::verificar/$1');
```

---

## 6. ASSETS PWA

```
c:\xampp\htdocs\enterprisesstph\public\manifest_inspecciones.json
c:\xampp\htdocs\enterprisesstph\public\sw_inspecciones.js
c:\xampp\htdocs\enterprisesstph\public\icons\icon-192.png
c:\xampp\htdocs\enterprisesstph\public\icons\icon-512.png
```

---

## 7. DOCUMENTACIÓN (toda la carpeta docs/)

```
c:\xampp\htdocs\enterprisesstph\docs\00_PLAN_MAESTRO.md
c:\xampp\htdocs\enterprisesstph\docs\01_ACTA_DE_VISITA.md
c:\xampp\htdocs\enterprisesstph\docs\02_DB_ACTA_VISITA.md
c:\xampp\htdocs\enterprisesstph\docs\03_PWA_LAYOUT.md
c:\xampp\htdocs\enterprisesstph\docs\04_ESTRATEGIA_FIRMAS.md
c:\xampp\htdocs\enterprisesstph\docs\05_ESTRATEGIA_OFFLINE.md
c:\xampp\htdocs\enterprisesstph\docs\06_ESTRATEGIA_NOTIFICACIONES.md
c:\xampp\htdocs\enterprisesstph\docs\07_ESTRATEGIA_PDF_UPLOAD.md
c:\xampp\htdocs\enterprisesstph\docs\08_ESTRATEGIA_AUTOGUARDADO.md
c:\xampp\htdocs\enterprisesstph\docs\09_DISENO_PDF_ACTA.md
c:\xampp\htdocs\enterprisesstph\docs\10_INSPECCION_LOCATIVA.md
c:\xampp\htdocs\enterprisesstph\docs\11_INPUT_FILE_CAMARA_GALERIA.md
c:\xampp\htdocs\enterprisesstph\docs\12_PATRON_INSPECCION_PLANA.md
c:\xampp\htdocs\enterprisesstph\docs\13_PATRON_INSPECCION_NITEMS.md
c:\xampp\htdocs\enterprisesstph\docs\14_PLAN_EMERGENCIA.md
c:\xampp\htdocs\enterprisesstph\docs\15_PATRON_DOCUMENTO_MAESTRO.md
c:\xampp\htdocs\enterprisesstph\docs\16_REPORTE_CAPACITACION.md
c:\xampp\htdocs\enterprisesstph\docs\17_ASISTENCIA_INDUCCION.md
c:\xampp\htdocs\enterprisesstph\docs\18_DOTACION_VIGILANTE.md
c:\xampp\htdocs\enterprisesstph\docs\19_DOTACION_TODERO.md
c:\xampp\htdocs\enterprisesstph\docs\20_DOTACION_ASEADORA.md
c:\xampp\htdocs\enterprisesstph\docs\21_AUDITORIA_ZONA_RESIDUOS.md
c:\xampp\htdocs\enterprisesstph\docs\22_PREPARACION_SIMULACRO.md
c:\xampp\htdocs\enterprisesstph\docs\integracion_vencimientos_inspecciones.md
c:\xampp\htdocs\enterprisesstph\docs\cambios-tabla-pta-cliente-nueva.md
```

---

## 8. ARCHIVOS DE REFERENCIA / TEXTO ESTÁTICO

```
c:\xampp\htdocs\enterprisesstph\y_appscriptbrigadista.txt
c:\xampp\htdocs\enterprisesstph\z_asistentes.txt
c:\xampp\htdocs\enterprisesstph\z_dotacion_vigilante.txt
c:\xampp\htdocs\enterprisesstph\z_plandeemergencia.txt
c:\xampp\htdocs\enterprisesstph\z_responsabilidadessst.txt
```

---

## 9. ESTRUCTURA DE CARPETAS COMPLETA

```
c:\xampp\htdocs\enterprisesstph\
├── app\
│   ├── Config\
│   │   └── Routes.php
│   ├── Controllers\
│   │   ├── Inspecciones\
│   │   │   ├── InspeccionesController.php              (dashboard)
│   │   │   ├── ActaVisitaController.php
│   │   │   ├── InspeccionLocativaController.php
│   │   │   ├── InspeccionSenalizacionController.php
│   │   │   ├── InspeccionExtintoresController.php
│   │   │   ├── InspeccionBotiquinController.php
│   │   │   ├── InspeccionGabineteController.php
│   │   │   ├── InspeccionComunicacionController.php
│   │   │   ├── InspeccionRecursosSeguridadController.php
│   │   │   ├── ProbabilidadPeligrosController.php
│   │   │   ├── MatrizVulnerabilidadController.php
│   │   │   ├── PlanEmergenciaController.php
│   │   │   ├── EvaluacionSimulacroController.php
│   │   │   ├── HvBrigadistaController.php
│   │   │   ├── DotacionVigilanteController.php
│   │   │   ├── DotacionAseadoraController.php
│   │   │   ├── DotacionToderoController.php
│   │   │   ├── AuditoriaZonaResiduosController.php
│   │   │   ├── PreparacionSimulacroController.php
│   │   │   ├── ReporteCapacitacionController.php
│   │   │   ├── AsistenciaInduccionController.php
│   │   │   ├── ProgramaLimpiezaController.php
│   │   │   ├── ProgramaResiduosController.php
│   │   │   ├── ProgramaPlagasController.php
│   │   │   ├── ProgramaAguaPotableController.php
│   │   │   ├── PlanSaneamientoController.php
│   │   │   ├── KpiLimpiezaController.php
│   │   │   ├── KpiResiduosController.php
│   │   │   ├── KpiPlagasController.php
│   │   │   ├── KpiAguaPotableController.php
│   │   │   ├── UrlsPwaController.php
│   │   │   ├── CartaVigiaPwaController.php
│   │   │   ├── MantenimientosPwaController.php
│   │   │   └── PendientesPwaController.php
│   │   ├── SimulacroPublicoController.php              (público)
│   │   └── HvBrigadistaPublicoController.php           (público)
│   ├── Models\
│   │   ├── ActaVisitaModel.php
│   │   ├── ActaVisitaFotoModel.php
│   │   ├── ActaVisitaIntegranteModel.php
│   │   ├── ActaVisitaTemaModel.php
│   │   ├── InspeccionLocativaModel.php
│   │   ├── HallazgoLocativoModel.php
│   │   ├── InspeccionSenalizacionModel.php
│   │   ├── ItemSenalizacionModel.php
│   │   ├── InspeccionExtintoresModel.php
│   │   ├── ExtintorDetalleModel.php
│   │   ├── InspeccionBotiquinModel.php
│   │   ├── ElementoBotiquinModel.php
│   │   ├── InspeccionGabineteModel.php
│   │   ├── GabineteDetalleModel.php
│   │   ├── InspeccionComunicacionModel.php
│   │   ├── InspeccionRecursosSeguridadModel.php
│   │   ├── ProbabilidadPeligrosModel.php
│   │   ├── MatrizVulnerabilidadModel.php
│   │   ├── PlanEmergenciaModel.php
│   │   ├── EvaluacionSimulacroModel.php
│   │   ├── HvBrigadistaModel.php
│   │   ├── DotacionVigilanteModel.php
│   │   ├── DotacionAseadoraModel.php
│   │   ├── DotacionToderoModel.php
│   │   ├── AuditoriaZonaResiduosModel.php
│   │   ├── PreparacionSimulacroModel.php
│   │   ├── ReporteCapacitacionModel.php
│   │   ├── AsistenciaInduccionModel.php
│   │   ├── AsistenciaInduccionAsistenteModel.php
│   │   ├── ProgramaLimpiezaModel.php
│   │   ├── ProgramaResiduosModel.php
│   │   ├── ProgramaPlagasModel.php
│   │   ├── ProgramaAguaPotableModel.php
│   │   ├── PlanSaneamientoModel.php
│   │   ├── KpiLimpiezaModel.php
│   │   ├── KpiResiduosModel.php
│   │   ├── KpiPlagasModel.php
│   │   ├── KpiAguaPotableModel.php
│   │   ├── UrlModel.php
│   │   ├── CartaVigiaModel.php
│   │   ├── MantenimientoModel.php
│   │   ├── VencimientosMantenimientoModel.php
│   │   └── PendientesModel.php
│   ├── SQL\
│   │   ├── migrate_acta_visita.php
│   │   ├── migrate_inspeccion_locativa.php
│   │   ├── migrate_inspeccion_senalizacion.php
│   │   ├── migrate_inspeccion_extintores.php
│   │   ├── migrate_inspeccion_botiquin.php
│   │   ├── migrate_inspeccion_gabinetes.php
│   │   ├── migrate_inspeccion_comunicaciones.php
│   │   ├── migrate_inspeccion_recursos_seguridad.php
│   │   ├── migrate_probabilidad_peligros.php
│   │   ├── migrate_matriz_vulnerabilidad.php
│   │   ├── migrate_plan_emergencia.php
│   │   ├── migrate_reporte_capacitacion.php
│   │   ├── migrate_asistencia_induccion.php
│   │   ├── migrate_dotacion_vigilante.php
│   │   ├── migrate_dotacion_todero.php
│   │   ├── migrate_dotacion_aseadora.php
│   │   ├── migrate_auditoria_zona_residuos.php
│   │   ├── migrate_preparacion_simulacro.php
│   │   ├── migrate_evaluacion_simulacro.php
│   │   ├── migrate_hv_brigadista.php
│   │   ├── migrate_carta_vigia.php
│   │   ├── migrate_firma_digital.php
│   │   ├── migrate_codigo_verificacion.php
│   │   ├── migrate_pta_audit_table.php
│   │   ├── migrate_kpis.php
│   │   ├── migrate_plan_saneamiento.php
│   │   ├── migrate_programa_agua_potable.php
│   │   ├── migrate_programa_plagas.php
│   │   ├── migrate_programa_residuos.php
│   │   └── migrate_urls.php
│   └── Views\
│       ├── inspecciones\
│       │   ├── layout_pwa.php                          (layout compartido)
│       │   ├── dashboard.php
│       │   ├── acta_visita\        (5: list, form, view, pdf, firma)
│       │   ├── inspeccion_locativa\ (4: list, form, view, pdf)
│       │   ├── senalizacion\       (4: list, form, view, pdf)
│       │   ├── extintores\         (5: list, form, view, pdf, _extintor_row)
│       │   ├── botiquin\           (4: list, form, view, pdf)
│       │   ├── gabinetes\          (4: list, form, view, pdf)
│       │   ├── comunicaciones\     (4: list, form, view, pdf)
│       │   ├── recursos-seguridad\ (4: list, form, view, pdf)
│       │   ├── probabilidad-peligros\ (4: list, form, view, pdf)
│       │   ├── matriz-vulnerabilidad\ (4: list, form, view, pdf)
│       │   ├── plan-emergencia\    (4: list, form, view, pdf)
│       │   ├── simulacro\          (3: list, view, pdf)
│       │   ├── hv-brigadista\      (3: list, view, pdf)
│       │   ├── dotacion-vigilante\ (4: list, form, view, pdf)
│       │   ├── dotacion-aseadora\  (4: list, form, view, pdf)
│       │   ├── dotacion-todero\    (4: list, form, view, pdf)
│       │   ├── auditoria-zona-residuos\ (4: list, form, view, pdf)
│       │   ├── preparacion-simulacro\   (4: list, form, view, pdf)
│       │   ├── reporte-capacitacion\    (4: list, form, view, pdf)
│       │   ├── asistencia-induccion\    (4: list, form, view, pdf)
│       │   ├── limpieza-desinfeccion\   (4: list, form, view, pdf)
│       │   ├── residuos-solidos\   (4: list, form, view, pdf)
│       │   ├── control-plagas\     (4: list, form, view, pdf)
│       │   ├── agua-potable\       (4: list, form, view, pdf)
│       │   ├── plan-saneamiento\   (4: list, form, view, pdf)
│       │   ├── kpi-limpieza\       (4: list, form, view, pdf)
│       │   ├── kpi-residuos\       (4: list, form, view, pdf)
│       │   ├── kpi-plagas\         (4: list, form, view, pdf)
│       │   ├── kpi-agua-potable\   (4: list, form, view, pdf)
│       │   ├── urls\               (2: list, form)
│       │   ├── carta_vigia\        (7: list, form, pdf, firma, firma_error, firma_success, email_firma)
│       │   ├── mantenimientos\     (2: list, form)
│       │   └── pendientes\         (2: list, form)
│       ├── simulacro\
│       │   └── form_publico.php                        (formulario público)
│       └── hv-brigadista\
│           └── form_publico.php                        (formulario público)
├── public\
│   ├── manifest_inspecciones.json
│   ├── sw_inspecciones.js
│   └── icons\
│       ├── icon-192.png
│       └── icon-512.png
└── docs\
    ├── 00_PLAN_MAESTRO.md ... 22_PREPARACION_SIMULACRO.md
    └── (26+ archivos de documentación)
```

---

## INSTRUCCIONES PARA REPLICAR

1. **Copiar TODOS los archivos** listados arriba a las mismas rutas relativas en el proyecto gemelo
2. **Ejecutar las 31 migraciones SQL** en el orden listado (primero LOCAL, luego PRODUCCIÓN)
3. **Verificar** que todas las tablas existen con el número correcto de columnas
4. **Assets PWA**: copiar manifest, service worker e iconos a `public/`
5. **Permisos carpetas uploads** en producción:
   - `uploads/inspecciones/` y subcarpetas → 775, owner www
   - `uploads/firmas/` → 775, owner www
