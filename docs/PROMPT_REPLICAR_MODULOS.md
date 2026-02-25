# PROMPT PARA REPLICAR MÓDULOS EN APLICATIVO GEMELO

## Contexto

Se implementaron 8 migraciones SQL + módulo completo Plan de Emergencia (controlador, modelo, vistas, rutas, dashboard) + documentación técnica de 9 módulos nuevos.

**Stack:** CodeIgniter 4, PHP 8.2, MySQL 8, DOMPDF para PDFs, Bootstrap 5 PWA.

---

## ARCHIVOS CREADOS (27 archivos nuevos)

### Migraciones SQL (8 archivos)
Cada script es PHP CLI con soporte LOCAL + PRODUCCIÓN (SSL). Ejecutar con: `php script.php` (local) o `DB_PROD_PASS=xxx php script.php production`

```
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_plan_emergencia.php          → tbl_plan_emergencia (~82 columnas)
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_reporte_capacitacion.php     → tbl_reporte_capacitacion (23 columnas)
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_asistencia_induccion.php     → tbl_asistencia_induccion (17 cols) + tbl_asistencia_induccion_asistente (7 cols)
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_vigilante.php       → tbl_dotacion_vigilante (23 columnas)
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_todero.php          → tbl_dotacion_todero (32 columnas)
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_dotacion_aseadora.php        → tbl_dotacion_aseadora (25 columnas)
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_auditoria_zona_residuos.php  → tbl_auditoria_zona_residuos (33 columnas)
c:\xampp\htdocs\enterprisesstph\app\SQL\migrate_preparacion_simulacro.php    → tbl_preparacion_simulacro (35 columnas)
```

### Controlador (1 archivo)
```
c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\PlanEmergenciaController.php    (617 líneas)
```

### Modelo (1 archivo)
```
c:\xampp\htdocs\enterprisesstph\app\Models\PlanEmergenciaModel.php    (103 líneas)
```

### Vistas Plan de Emergencia (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\form.php    (697 líneas — formulario ~26 secciones, 19 fotos)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\list.php    (111 líneas — listado cards con filtro Select2)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\view.php    (499 líneas — vista read-only)
c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\plan-emergencia\pdf.php     (698 líneas — template DOMPDF, texto estático + datos dinámicos + anexos)
```

### Documentación (9 archivos)
```
c:\xampp\htdocs\enterprisesstph\docs\14_PLAN_EMERGENCIA.md              (311 líneas)
c:\xampp\htdocs\enterprisesstph\docs\15_PATRON_DOCUMENTO_MAESTRO.md     (215 líneas)
c:\xampp\htdocs\enterprisesstph\docs\16_REPORTE_CAPACITACION.md         (310 líneas)
c:\xampp\htdocs\enterprisesstph\docs\17_ASISTENCIA_INDUCCION.md         (424 líneas)
c:\xampp\htdocs\enterprisesstph\docs\18_DOTACION_VIGILANTE.md           (345 líneas)
c:\xampp\htdocs\enterprisesstph\docs\19_DOTACION_TODERO.md              (278 líneas)
c:\xampp\htdocs\enterprisesstph\docs\20_DOTACION_ASEADORA.md            (230 líneas)
c:\xampp\htdocs\enterprisesstph\docs\21_AUDITORIA_ZONA_RESIDUOS.md     (395 líneas)
c:\xampp\htdocs\enterprisesstph\docs\22_PREPARACION_SIMULACRO.md        (370 líneas)
```

### Archivos de referencia/texto estático (4 archivos)
```
c:\xampp\htdocs\enterprisesstph\y_appscriptbrigadista.txt       (1468 líneas — Google Apps Script HTML brigadista)
c:\xampp\htdocs\enterprisesstph\z_asistentes.txt                (138 líneas)
c:\xampp\htdocs\enterprisesstph\z_dotacion_vigilante.txt        (90 líneas)
c:\xampp\htdocs\enterprisesstph\z_plandeemergencia.txt          (1972 líneas — texto estático del PDF plan emergencia)
c:\xampp\htdocs\enterprisesstph\z_responsabilidadessst.txt      (184 líneas)
```

---

## ARCHIVOS MODIFICADOS (3 archivos)

### 1. Rutas — `c:\xampp\htdocs\enterprisesstph\app\Config\Routes.php`

Se agregaron 12 rutas DESPUÉS de `matriz-vulnerabilidad/delete` y ANTES de `pendientes`, dentro del grupo `inspecciones`:

```php
// Plan de Emergencia
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
```

**URL base:** `/inspecciones/plan-emergencia`
**Namespace:** `App\Controllers\Inspecciones`
**Filter:** `auth`

### 2. Dashboard Controller — `c:\xampp\htdocs\enterprisesstph\app\Controllers\Inspecciones\InspeccionesController.php`

Cambios:
- **Import agregado:** `use App\Models\PlanEmergenciaModel;`
- **Conteo agregado en dashboard():**
```php
$planEmgModel = new PlanEmergenciaModel();
$totalPlanEmergencia = $planEmgModel->where('id_consultor', $userId)
    ->where('estado', 'completo')
    ->countAllResults();

if ($role === 'admin') {
    $pendientesPlanEmg = $planEmgModel->getAllPendientes();
} else {
    $pendientesPlanEmg = $planEmgModel->getPendientesByConsultor($userId);
}
```
- **Variables agregadas al array $data:**
  - `'pendientesPlanEmg' => $pendientesPlanEmg`
  - `'totalPlanEmergencia' => $totalPlanEmergencia`

### 3. Dashboard Vista — `c:\xampp\htdocs\enterprisesstph\app\Views\inspecciones\dashboard.php`

Cambios:
- **Sección pendientes** (después de pendientes matriz vulnerabilidad): Card borrador "Plan Emerg." con enlace a `/inspecciones/plan-emergencia/edit/{id}`
- **Card en grid** (después de Matriz Vuln.): `<a href="/inspecciones/plan-emergencia">` con icono `fa-file-medical`, label "Plan Emergencia", conteo `$totalPlanEmergencia`

### 4. Plan Maestro — `c:\xampp\htdocs\enterprisesstph\docs\00_PLAN_MAESTRO.md`

Se actualizó el árbol de directorios para incluir los nuevos controladores y vistas (gabinetes, comunicaciones, recursos seguridad, probabilidad peligros, matriz vulnerabilidad, plan emergencia).

---

## TABLAS SQL CREADAS (9 tablas en LOCAL y PRODUCCIÓN)

| Tabla | Columnas | Migración |
|-------|----------|-----------|
| `tbl_plan_emergencia` | 82 | migrate_plan_emergencia.php |
| `tbl_reporte_capacitacion` | 23 | migrate_reporte_capacitacion.php |
| `tbl_asistencia_induccion` | 17 | migrate_asistencia_induccion.php |
| `tbl_asistencia_induccion_asistente` | 7 | migrate_asistencia_induccion.php |
| `tbl_dotacion_vigilante` | 23 | migrate_dotacion_vigilante.php |
| `tbl_dotacion_todero` | 32 | migrate_dotacion_todero.php |
| `tbl_dotacion_aseadora` | 25 | migrate_dotacion_aseadora.php |
| `tbl_auditoria_zona_residuos` | 33 | migrate_auditoria_zona_residuos.php |
| `tbl_preparacion_simulacro` | 35 | migrate_preparacion_simulacro.php |

---

## CREDENCIALES DE PRODUCCIÓN

```
host     = db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com
port     = 25060
database = propiedad_horizontal
user     = cycloid_userdb
password = (usar variable de entorno DB_PROD_PASS)
sslmode  = REQUIRED
```

---

## INSTRUCCIONES PARA REPLICAR

1. **Copiar los 27 archivos nuevos** a las mismas rutas relativas en el proyecto gemelo
2. **Aplicar los 3 diffs** a los archivos modificados (Routes.php, InspeccionesController.php, dashboard.php)
3. **Ejecutar las 8 migraciones** en orden:
   ```bash
   cd app/SQL
   php migrate_plan_emergencia.php
   php migrate_reporte_capacitacion.php
   php migrate_asistencia_induccion.php
   php migrate_dotacion_vigilante.php
   php migrate_dotacion_todero.php
   php migrate_dotacion_aseadora.php
   php migrate_auditoria_zona_residuos.php
   php migrate_preparacion_simulacro.php
   ```
4. **Verificar** que las 9 tablas existen con el número correcto de columnas
5. **Repetir migraciones en producción** con: `DB_PROD_PASS=xxx php script.php production`

---

## ESTRUCTURA DE CARPETAS COMPLETA AFECTADA

```
c:\xampp\htdocs\enterprisesstph\
├── app\
│   ├── Config\
│   │   └── Routes.php                                    (MODIFICADO — +12 rutas)
│   ├── Controllers\
│   │   └── Inspecciones\
│   │       ├── InspeccionesController.php                 (MODIFICADO — +import +conteo +pendientes)
│   │       └── PlanEmergenciaController.php               (NUEVO — 617 líneas)
│   ├── Models\
│   │   └── PlanEmergenciaModel.php                        (NUEVO — 103 líneas)
│   ├── SQL\
│   │   ├── migrate_plan_emergencia.php                    (NUEVO)
│   │   ├── migrate_reporte_capacitacion.php               (NUEVO)
│   │   ├── migrate_asistencia_induccion.php               (NUEVO)
│   │   ├── migrate_dotacion_vigilante.php                 (NUEVO)
│   │   ├── migrate_dotacion_todero.php                    (NUEVO)
│   │   ├── migrate_dotacion_aseadora.php                  (NUEVO)
│   │   ├── migrate_auditoria_zona_residuos.php            (NUEVO)
│   │   └── migrate_preparacion_simulacro.php              (NUEVO)
│   └── Views\
│       └── inspecciones\
│           ├── dashboard.php                              (MODIFICADO — +card +pendientes)
│           └── plan-emergencia\
│               ├── form.php                               (NUEVO — 697 líneas)
│               ├── list.php                               (NUEVO — 111 líneas)
│               ├── view.php                               (NUEVO — 499 líneas)
│               └── pdf.php                                (NUEVO — 698 líneas)
├── docs\
│   ├── 00_PLAN_MAESTRO.md                                 (MODIFICADO)
│   ├── 14_PLAN_EMERGENCIA.md                              (NUEVO)
│   ├── 15_PATRON_DOCUMENTO_MAESTRO.md                     (NUEVO)
│   ├── 16_REPORTE_CAPACITACION.md                         (NUEVO)
│   ├── 17_ASISTENCIA_INDUCCION.md                         (NUEVO)
│   ├── 18_DOTACION_VIGILANTE.md                           (NUEVO)
│   ├── 19_DOTACION_TODERO.md                              (NUEVO)
│   ├── 20_DOTACION_ASEADORA.md                            (NUEVO)
│   ├── 21_AUDITORIA_ZONA_RESIDUOS.md                     (NUEVO)
│   └── 22_PREPARACION_SIMULACRO.md                        (NUEVO)
├── y_appscriptbrigadista.txt                              (NUEVO)
├── z_asistentes.txt                                       (NUEVO)
├── z_dotacion_vigilante.txt                               (NUEVO)
├── z_plandeemergencia.txt                                 (NUEVO)
└── z_responsabilidadessst.txt                             (NUEVO)
```

**Total: 27 archivos nuevos + 4 archivos modificados = 31 archivos**
**Total líneas agregadas: 10,583**
