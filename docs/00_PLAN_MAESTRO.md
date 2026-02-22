# PLAN MAESTRO - Módulo de Inspecciones SST (PWA)

## Resumen Ejecutivo

Nuevo módulo dentro de `enterprisesstph` para gestionar inspecciones de seguridad desde el celular (PWA). Reemplaza AppSheet eliminando la generación manual de PDFs.

**Objetivo:** El consultor abre la PWA en su celular → selecciona cliente → llena la inspección → firma → se genera el PDF automáticamente.

---

## Arquitectura General

```
enterprisesstph/                          (proyecto existente CI4)
├── app/
│   ├── Controllers/
│   │   └── Inspecciones/                 ← NUEVO directorio
│   │       ├── InspeccionesController.php     (dashboard PWA, listados)
│   │       ├── ActaVisitaController.php       (CRUD acta de visita)
│   │       ├── InspeccionSenalizacionController.php  (futuro)
│   │       ├── InspeccionLocativaController.php      (futuro)
│   │       ├── BotiquinController.php                (futuro)
│   │       ├── ExtintoresController.php              (futuro)
│   │       └── ...
│   ├── Views/
│   │   └── inspecciones/                 ← NUEVO directorio
│   │       ├── layout_pwa.php                (layout mobile-first, NO sidebar admin)
│   │       ├── dashboard.php                 (menú principal inspecciones)
│   │       ├── acta_visita/
│   │       │   ├── list.php                  (listado de actas)
│   │       │   ├── create.php                (formulario nueva acta)
│   │       │   ├── edit.php                  (editar acta existente)
│   │       │   ├── view.php                  (vista previa acta)
│   │       │   ├── firma.php                 (canvas de firma)
│   │       │   └── pdf.php                   (template DOMPDF)
│   │       ├── senalizacion/                 (futuro)
│   │       └── ...
│   ├── Models/
│   │   ├── ActaVisitaModel.php           ← NUEVO
│   │   ├── ActaVisitaIntegranteModel.php ← NUEVO
│   │   └── ActaVisitaTemaModel.php       ← NUEVO
│   └── Config/
│       └── Routes.php                    (agregar grupo /inspecciones/*)
├── public/
│   ├── manifest_inspecciones.json        ← NUEVO (PWA manifest)
│   ├── sw_inspecciones.js                ← NUEVO (Service Worker)
│   └── uploads/
│       └── inspecciones/                 ← NUEVO
│           ├── firmas/                       (imágenes de firma)
│           ├── fotos/                        (fotos tomadas en campo)
│           └── pdfs/                         (PDFs generados)
```

---

## Inspecciones a Implementar (Roadmap)

| #  | Inspección              | Prioridad | Estado    |
|----|-------------------------|-----------|-----------|
| 1  | **Acta de Visita**      | ALTA      | EN DISEÑO |
| 2  | Señalización            | Media     | Pendiente |
| 3  | Locativas               | Media     | Pendiente |
| 4  | Botiquín                | Media     | Pendiente |
| 5  | Extintores              | Media     | Pendiente |
| 6  | Gabinetes               | Media     | Pendiente |
| 7  | Comunicaciones          | Media     | Pendiente |

Cada inspección se documenta en su propio archivo (`01_ACTA_DE_VISITA.md`, `04_SENALIZACION.md`, etc.)

---

## Decisiones Arquitectónicas

### 1. PWA (Progressive Web App)
- `manifest.json` con `start_url: "/inspecciones"` y `scope: "/inspecciones/"`
- Service Worker para cache de assets (CSS, JS, iconos) - **NO cache de datos**
- Prompt de instalación "Agregar a pantalla de inicio"
- Layout propio `layout_pwa.php` mobile-first, sin sidebar de admin

### 2. Autenticación
- Mismo login del sistema (`/login`)
- Sesión CI4 estándar con cookie de larga duración (30 días para rol inspector/consultor en PWA)
- El consultor se loguea una vez, la sesión persiste
- **NO se crean roles nuevos**: el consultor accede a `/inspecciones` desde su celular y al panel admin desde PC

### 3. Generación de PDF
- **DOMPDF** (mismo patrón que los controladores `Pz*` existentes)
- Template dedicado por tipo de inspección (`pdf.php`)
- PDF incluye: header con logo del cliente, datos del SGSST, contenido del acta, firmas
- Se guarda en `uploads/inspecciones/pdfs/` y queda linkeable desde el panel admin

### 4. Firmas Digitales
- Canvas HTML5 (mismo patrón de `contrato_firma.php`)
- Protección anti-firma accidental (multi-touch, preview SweetAlert2, validación píxeles)
- Se guardan como PNG en `uploads/inspecciones/firmas/`
- Se incrustan en el PDF generado

### 5. Integración con Datos Existentes
El Acta de Visita jala datos automáticamente de tablas existentes:
- `tbl_clientes` → nombre del cliente, logo
- `tbl_pendientes` → pendientes abiertos del cliente
- `tbl_vencimientos_mantenimientos` → mantenimientos por vencer
- `tbl_hallazgos` → hallazgos locativos abiertos (si existe)
- `tbl_pta_cliente` → actividades del plan de trabajo

### 6. Rutas CI4

```php
// Grupo de inspecciones (protegido por AuthFilter)
$routes->group('inspecciones', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Inspecciones\InspeccionesController::dashboard');

    // Acta de Visita
    $routes->get('acta-visita', 'Inspecciones\ActaVisitaController::list');
    $routes->get('acta-visita/create', 'Inspecciones\ActaVisitaController::create');
    $routes->get('acta-visita/create/(:num)', 'Inspecciones\ActaVisitaController::create/$1');
    $routes->post('acta-visita/store', 'Inspecciones\ActaVisitaController::store');
    $routes->get('acta-visita/edit/(:num)', 'Inspecciones\ActaVisitaController::edit/$1');
    $routes->post('acta-visita/update/(:num)', 'Inspecciones\ActaVisitaController::update/$1');
    $routes->get('acta-visita/view/(:num)', 'Inspecciones\ActaVisitaController::view/$1');
    $routes->get('acta-visita/pdf/(:num)', 'Inspecciones\ActaVisitaController::generatePdf/$1');
    $routes->post('acta-visita/firma/(:num)', 'Inspecciones\ActaVisitaController::saveFirma/$1');

    // API endpoints AJAX
    $routes->get('api/clientes', 'Inspecciones\InspeccionesController::getClientes');
    $routes->get('api/pendientes/(:num)', 'Inspecciones\InspeccionesController::getPendientes/$1');
    $routes->get('api/mantenimientos/(:num)', 'Inspecciones\InspeccionesController::getMantenimientos/$1');
});
```

---

## Stack Tecnológico

| Componente      | Tecnología                              |
|-----------------|-----------------------------------------|
| Backend         | CodeIgniter 4 (PHP 8.2)                |
| Frontend        | Bootstrap 5.3 (mobile-first)           |
| PDF             | DOMPDF                                  |
| Firma           | Canvas HTML5 + SweetAlert2             |
| BD              | MySQL `propiedad_horizontal` (misma BD)|
| PWA             | manifest.json + Service Worker          |
| Cámara/Fotos    | HTML5 `<input type="file" capture>`    |
| Mapas/Ubicación | Geolocation API (coordenadas GPS)      |

---

## Documentos Relacionados

- [01_ACTA_DE_VISITA.md](./01_ACTA_DE_VISITA.md) - Especificacion completa del Acta de Visita
- [02_DB_ACTA_VISITA.md](./02_DB_ACTA_VISITA.md) - Diseno de base de datos
- [03_PWA_LAYOUT.md](./03_PWA_LAYOUT.md) - Diseno del layout PWA y flujo mobile
- [04_ESTRATEGIA_FIRMAS.md](./04_ESTRATEGIA_FIRMAS.md) - Canvas, almacenamiento y flujo presencial de firmas
- [05_ESTRATEGIA_OFFLINE.md](./05_ESTRATEGIA_OFFLINE.md) - IndexedDB, Background Sync, pre-carga y sincronizacion
- [06_ESTRATEGIA_NOTIFICACIONES.md](./06_ESTRATEGIA_NOTIFICACIONES.md) - Web Push, SendGrid, recordatorios por cron
- [07_ESTRATEGIA_PDF_UPLOAD.md](./07_ESTRATEGIA_PDF_UPLOAD.md) - Auto-cargue de PDF a tbl_reporte, reemplazo del pipeline n8n
- [08_ESTRATEGIA_AUTOGUARDADO.md](./08_ESTRATEGIA_AUTOGUARDADO.md) - localStorage para recuperar formularios ante perdida de sesion
