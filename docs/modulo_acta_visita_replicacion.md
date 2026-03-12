# MÓDULO ACTA DE VISITA — Documento de Replicación Completo

> **Proyecto origen:** enterprisesstph (CodeIgniter 4 + MySQL)
> **URL de referencia:** `https://phorizontal.cycloidtalent.com/inspecciones/acta-visita/create`
> **Generado:** 2026-03-12

---

## 1. INVENTARIO DE ARCHIVOS

### 1.1 Controladores

| # | Ruta absoluta | Líneas | Propósito |
|---|--------------|--------|-----------|
| 1 | `app/Controllers/Inspecciones/ActaVisitaController.php` | 792 | Controlador principal CRUD, firmas, PDF, email, finalización |
| 2 | `app/Controllers/Inspecciones/InspeccionesController.php` | 319 | API endpoints AJAX (clientes, pendientes, mantenimientos) consumidos por el form |
| 3 | `app/Controllers/ClientInspeccionesController.php` | 2126 | Vista cliente de actas (lectura) |
| 4 | `app/Controllers/InformeAvancesController.php` | 1269 | Referencias cruzadas con acta_visita |

### 1.2 Modelos

| # | Ruta absoluta | Líneas | Tabla | Propósito |
|---|--------------|--------|-------|-----------|
| 1 | `app/Models/ActaVisitaModel.php` | 77 | `tbl_acta_visita` | Modelo principal con queries por consultor, cliente, pendientes |
| 2 | `app/Models/ActaVisitaTemaModel.php` | 41 | `tbl_acta_visita_temas` | Temas tratados, con `replaceForActa()` |
| 3 | `app/Models/ActaVisitaIntegranteModel.php` | 42 | `tbl_acta_visita_integrantes` | Asistentes, con `replaceForActa()` |
| 4 | `app/Models/ActaVisitaFotoModel.php` | 30 | `tbl_acta_visita_fotos` | Fotos/soportes adjuntos |
| 5 | `app/Models/PendientesModel.php` | 88 | `tbl_pendientes` | Compromisos del acta + pendientes abiertos del cliente |
| 6 | `app/Models/CicloVisitaModel.php` | ~200 | `tbl_ciclos_visita` | Ciclos mensuales de visita (hook al finalizar) |
| 7 | `app/Models/ClientModel.php` | — | `tbl_clientes` | Datos del cliente (nombre, NIT, logo, correos) |
| 8 | `app/Models/ConsultantModel.php` | — | `tbl_consultor` | Datos del consultor |
| 9 | `app/Models/ReporteModel.php` | — | `tbl_reporte` | Upload del PDF a reportes del cliente |
| 10 | `app/Models/VencimientosMantenimientoModel.php` | — | `tbl_vencimientos_mantenimientos` | Mantenimientos por vencer (sección auto del PDF) |
| 11 | `app/Models/AgendamientoModel.php` | 200 | — | Referencias con agendamiento de visitas |

### 1.3 Vistas

| # | Ruta absoluta | Líneas | Propósito |
|---|--------------|--------|-----------|
| 1 | `app/Views/inspecciones/acta_visita/form.php` | 598 | Formulario crear/editar con accordion, dynamic rows, autoguardado, GPS |
| 2 | `app/Views/inspecciones/acta_visita/list.php` | 129 | Listado de actas con filtro Select2, badges de estado |
| 3 | `app/Views/inspecciones/acta_visita/view.php` | 163 | Vista de solo lectura con secciones colapsables y modal fotos |
| 4 | `app/Views/inspecciones/acta_visita/firma.php` | 339 | Pantalla de firma digital paso a paso con canvas HTML5 |
| 5 | `app/Views/inspecciones/acta_visita/pdf.php` | 277 | Template HTML para generar PDF con DOMPDF |
| 6 | `app/Views/inspecciones/layout_pwa.php` | 369 | Layout PWA compartido (topbar, bottomnav, CDNs, SW) |
| 7 | `app/Views/inspecciones/dashboard.php` | 1149 | Dashboard con contadores de actas |
| 8 | `app/Views/client/inspecciones/acta_visita_view.php` | 142 | Vista del cliente (lectura) |
| 9 | `app/Views/client/inspecciones/list.php` | 151 | Lista de inspecciones del cliente (incluye actas) |

### 1.4 JavaScript

| # | Ruta absoluta | Líneas | Propósito |
|---|--------------|--------|-----------|
| 1 | `public/js/autosave_server.js` | 262 | Motor de autoguardado servidor (cada 60s) + localStorage fallback |

### 1.5 Traits / Libraries

| # | Ruta absoluta | Líneas | Propósito |
|---|--------------|--------|-----------|
| 1 | `app/Traits/AutosaveJsonTrait.php` | 29 | Trait para detectar request de autosave y responder JSON |
| 2 | `app/Libraries/InspeccionEmailNotifier.php` | 165 | Envío de email con PDF adjunto vía SendGrid SDK |

### 1.6 Migraciones SQL

| # | Ruta absoluta | Líneas | Propósito |
|---|--------------|--------|-----------|
| 1 | `app/SQL/migrate_acta_visita.php` | 281 | CREATE de 4 tablas + ALTER tbl_pendientes |
| 2 | `app/SQL/migrate_ciclos_visita.php` | 215 | Tabla de ciclos de visita |
| 3 | `app/SQL/seed_ciclos_visita.php` | 161 | Datos semilla de ciclos |
| 4 | `app/SQL/fix_updated_at.php` | 133 | Parche de timestamp |

### 1.7 Otros

| # | Ruta absoluta | Propósito |
|---|--------------|-----------|
| 1 | `app/Commands/AuditoriaVisitasCron.php` | Cron para auditoría de visitas |
| 2 | `app/Config/Routes.php` (líneas 872-885) | Definición de rutas del módulo |
| 3 | `public/manifest_inspecciones.json` | Manifest PWA |
| 4 | `public/sw_inspecciones.js` | Service Worker PWA |

---

## 2. RUTAS DEL APLICATIVO

### 2.1 Vistas (GET)

| URL | Controller::Método | Descripción |
|-----|-------------------|-------------|
| `GET /inspecciones/acta-visita` | `ActaVisitaController::list` | Listado de todas las actas |
| `GET /inspecciones/acta-visita/create` | `ActaVisitaController::create` | Formulario nueva acta |
| `GET /inspecciones/acta-visita/create/(:num)` | `ActaVisitaController::create/$1` | Nueva acta con cliente preseleccionado |
| `GET /inspecciones/acta-visita/edit/(:num)` | `ActaVisitaController::edit/$1` | Formulario edición |
| `GET /inspecciones/acta-visita/view/(:num)` | `ActaVisitaController::view/$1` | Vista solo lectura |
| `GET /inspecciones/acta-visita/firma/(:num)` | `ActaVisitaController::firma/$1` | Pantalla firmas paso a paso |

### 2.2 Acciones (POST)

| URL | Controller::Método | Descripción |
|-----|-------------------|-------------|
| `POST /inspecciones/acta-visita/store` | `ActaVisitaController::store` | Crear acta (también autosave AJAX) |
| `POST /inspecciones/acta-visita/update/(:num)` | `ActaVisitaController::update/$1` | Actualizar acta (también autosave AJAX) |
| `POST /inspecciones/acta-visita/save-firma/(:num)` | `ActaVisitaController::saveFirma/$1` | Guardar firma individual (AJAX, JSON) |
| `POST /inspecciones/acta-visita/finalizar/(:num)` | `ActaVisitaController::finalizar/$1` | Finalizar: generar PDF + email + ciclo (AJAX, JSON) |

### 2.3 Acciones (GET)

| URL | Controller::Método | Descripción |
|-----|-------------------|-------------|
| `GET /inspecciones/acta-visita/pdf/(:num)` | `ActaVisitaController::generatePdf/$1` | Ver/descargar PDF (siempre regenera) |
| `GET /inspecciones/acta-visita/regenerar/(:num)` | `ActaVisitaController::regenerarPdf/$1` | Regenerar PDF de acta completa |
| `GET /inspecciones/acta-visita/delete/(:num)` | `ActaVisitaController::delete/$1` | Eliminar acta (y archivos en disco) |
| `GET /inspecciones/acta-visita/enviar-email/(:num)` | `ActaVisitaController::enviarEmail/$1` | Re-enviar email con PDF |

### 2.4 API AJAX (consumidas por el formulario)

| URL | Controller::Método | Retorna |
|-----|-------------------|---------|
| `GET /inspecciones/api/clientes` | `InspeccionesController::getClientes` | JSON array de clientes `[{id_cliente, nombre_cliente}]` |
| `GET /inspecciones/api/pendientes/(:num)` | `InspeccionesController::getPendientes/$1` | JSON array de pendientes abiertos del cliente |
| `GET /inspecciones/api/mantenimientos/(:num)` | `InspeccionesController::getMantenimientos/$1` | JSON array de mantenimientos por vencer (30 días) |

### 2.5 Filtro de autenticación

Todas las rutas están dentro del grupo `inspecciones` con filtro `auth`. No hay rutas públicas en este módulo.

```php
$routes->group('inspecciones', ['namespace' => 'App\Controllers\Inspecciones', 'filter' => 'auth'], function ($routes) {
    // ... todas las rutas acta-visita aquí
});
```

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tablas PROPIAS del módulo

#### `tbl_acta_visita` (tabla principal)

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_visita` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `id_consultor` INT NOT NULL,

    -- Datos de la visita
    `fecha_visita` DATE NOT NULL,
    `hora_visita` TIME NOT NULL,
    `ubicacion_gps` VARCHAR(50) NULL COMMENT 'Coordenadas GPS lat,lng',
    `motivo` VARCHAR(255) NOT NULL,
    `modalidad` VARCHAR(50) NULL DEFAULT 'Presencial' COMMENT 'Presencial/Virtual/Mixta',

    -- Contenido
    `cartera` TEXT NULL,
    `observaciones` TEXT NULL,

    -- Próxima reunión
    `proxima_reunion_fecha` DATE NULL,
    `proxima_reunion_hora` TIME NULL,

    -- Firmas (rutas a imágenes PNG)
    `firma_administrador` VARCHAR(255) NULL,
    `firma_vigia` VARCHAR(255) NULL,
    `firma_consultor` VARCHAR(255) NULL,

    -- Soportes documentales
    `soporte_lavado_tanques` VARCHAR(255) NULL,
    `soporte_plagas` VARCHAR(255) NULL,

    -- PDF generado
    `ruta_pdf` VARCHAR(255) NULL,

    -- Estado y tracking
    `estado` ENUM('borrador', 'pendiente_firma', 'completo') NOT NULL DEFAULT 'borrador',
    `agenda_id` VARCHAR(50) NULL COMMENT 'Vínculo opcional con agenda',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_acta_visita_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_acta_visita_consultor`
        FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX `idx_acta_cliente` (`id_cliente`),
    INDEX `idx_acta_consultor` (`id_consultor`),
    INDEX `idx_acta_fecha` (`fecha_visita`),
    INDEX `idx_acta_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `tbl_acta_visita_integrantes` (detalle — asistentes)

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_visita_integrantes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_acta_visita` INT NOT NULL,
    `nombre` VARCHAR(200) NOT NULL,
    `rol` VARCHAR(100) NOT NULL COMMENT 'ADMINISTRADOR, CONSULTOR CYCLOID, VIGÍA SST, etc.',
    `orden` TINYINT NOT NULL DEFAULT 1 COMMENT 'Orden de aparición en el acta',

    CONSTRAINT `fk_integrante_acta`
        FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_integrante_acta` (`id_acta_visita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `tbl_acta_visita_temas` (detalle — temas tratados)

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_visita_temas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_acta_visita` INT NOT NULL,
    `descripcion` TEXT NOT NULL,
    `orden` TINYINT NOT NULL DEFAULT 1,

    CONSTRAINT `fk_tema_acta`
        FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_tema_acta` (`id_acta_visita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `tbl_acta_visita_fotos` (detalle — fotos y soportes)

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_visita_fotos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_acta_visita` INT NOT NULL,
    `ruta_archivo` VARCHAR(255) NOT NULL,
    `tipo` VARCHAR(50) NOT NULL DEFAULT 'foto' COMMENT 'foto, soporte, seg_social',
    `descripcion` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_foto_acta`
        FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_foto_acta` (`id_acta_visita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### ALTER `tbl_pendientes` (columna FK agregada)

```sql
ALTER TABLE `tbl_pendientes`
    ADD COLUMN `id_acta_visita` INT NULL DEFAULT NULL
    COMMENT 'FK al acta de visita que generó este pendiente';

ALTER TABLE `tbl_pendientes` ADD INDEX `idx_pendiente_acta` (`id_acta_visita`);

ALTER TABLE `tbl_pendientes`
    ADD CONSTRAINT `fk_pendiente_acta_visita`
    FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;
```

### 3.2 Tablas del SISTEMA que el módulo consulta

| Tabla | Campos usados | Relación |
|-------|--------------|----------|
| `tbl_clientes` | `id_cliente`, `nombre_cliente`, `nit_cliente`, `logo`, `correo_cliente`, `consultor_externo`, `email_consultor_externo` | FK desde `tbl_acta_visita.id_cliente` |
| `tbl_consultor` | `id_consultor`, `nombre_consultor`, `correo_consultor` | FK desde `tbl_acta_visita.id_consultor` |
| `tbl_reporte` | `id_reporte`, `titulo_reporte`, `id_detailreport`, `id_report_type`, `id_cliente`, `estado`, `observaciones`, `enlace`, `created_at`, `updated_at` | INSERT/UPDATE al finalizar (`id_report_type=6`, `id_detailreport=9`) |
| `tbl_pendientes` | `id_pendientes`, `id_cliente`, `tarea_actividad`, `fecha_asignacion`, `fecha_cierre`, `responsable`, `estado`, `conteo_dias`, `id_acta_visita` | FK bidireccional: compromisos del acta + pendientes abiertos del cliente |
| `tbl_vencimientos_mantenimientos` | `id_cliente`, `id_mantenimiento`, `fecha_vencimiento`, `estado_actividad` | JOIN con `tbl_mantenimientos` para sección auto del PDF |
| `tbl_mantenimientos` | `id_mantenimiento`, `detalle_mantenimiento` | JOIN para nombre del mantenimiento |
| `tbl_ciclos_visita` | `id`, `id_cliente`, `mes_esperado`, `anio`, `fecha_agendada`, `fecha_acta`, `id_acta`, `estatus_agenda`, `estatus_mes`, `estandar` | UPDATE al finalizar acta (hook ciclo) |

### 3.3 Datos semilla / catálogo

No hay datos semilla propios del módulo acta_visita. Los roles de integrantes son hardcodeados en la vista:

```php
['ADMINISTRADOR', 'ASISTENTE DE ADMINISTRACIÓN', 'CONSULTOR CYCLOID', 'VIGÍA SST', 'OTRO']
```

Los valores de `tbl_reporte` al uploadear:
- `id_report_type = 6` (Inspecciones)
- `id_detailreport = 9` (Actas de visita)

---

## 4. FLUJO FUNCIONAL

### 4.1 Estados y transiciones

```
borrador ──(ir a firmas)──> pendiente_firma ──(finalizar)──> completo
    │                              │
    └──(eliminar)──> ELIMINADO     └──(editar)──> pendiente_firma (mismo)
```

- **borrador**: Acta en construcción. Autoguardado activo. Editable.
- **pendiente_firma**: Se transiciona automáticamente al entrar a la pantalla de firmas. Editable.
- **completo**: PDF generado, email enviado, ciclo actualizado. Solo lectura (solo regenerar PDF o re-enviar email).

### 4.2 Métodos del controlador

| Método | Tipo | Descripción |
|--------|------|-------------|
| `list()` | GET | Consulta todas las actas con JOIN clientes+consultores, renderiza lista |
| `create($idCliente)` | GET | Renderiza formulario vacío, opcionalmente con cliente preseleccionado |
| `store()` | POST | Inserta acta + integrantes + temas + compromisos + fotos. Soporta autosave AJAX |
| `edit($id)` | GET | Carga acta existente con todos sus detalles, renderiza formulario |
| `update($id)` | POST | Actualiza acta + reemplaza integrantes/temas/compromisos + agrega fotos nuevas. Soporta autosave AJAX |
| `view($id)` | GET | Renderiza vista de solo lectura con todos los datos |
| `firma($id)` | GET | Cambia estado a `pendiente_firma` si era `borrador`. Muestra canvas de firma por firmante |
| `saveFirma($id)` | POST/AJAX | Recibe base64 PNG, decodifica, guarda archivo en disco, actualiza campo `firma_X` en BD |
| `finalizar($id)` | POST/AJAX | Verifica firmas obligatorias → genera PDF → actualiza estado a `completo` → upload a reportes → envía email → actualiza ciclo visita |
| `generatePdf($id)` | GET | Siempre regenera PDF desde template actual, lo sirve inline |
| `regenerarPdf($id)` | GET | Solo para actas `completo`: regenera PDF y actualiza reportes |
| `delete($id)` | GET | Elimina fotos y firmas del disco, luego DELETE (CASCADE elimina detalle) |
| `enviarEmail($id)` | GET | Re-envía email con PDF adjunto al cliente, consultor y consultor externo |

### 4.3 Métodos privados del controlador

| Método | Descripción |
|--------|-------------|
| `saveIntegrantes($idActa)` | Lee POST `integrante_nombre[]` + `integrante_rol[]`, llama `replaceForActa()` |
| `saveTemas($idActa)` | Lee POST `tema[]`, llama `replaceForActa()` |
| `saveCompromisos($idActa)` | Lee POST `compromiso_actividad[]`, `compromiso_fecha[]`, `compromiso_responsable[]`. DELETE previos + INSERT nuevos en `tbl_pendientes` |
| `saveFotos($idActa)` | Lee `$_FILES['fotos']`, mueve a `uploads/inspecciones/fotos/`, INSERT en `tbl_acta_visita_fotos` |
| `generarPdfInterno($id)` | Carga todos los datos + convierte firmas/fotos/logo a base64 → renderiza `pdf.php` → DOMPDF → guarda en `uploads/inspecciones/pdfs/` |
| `uploadToReportes($acta, $pdfPath)` | Copia PDF a `uploads/{nit_cliente}/`, INSERT/UPDATE en `tbl_reporte` con `id_report_type=6`, `id_detailreport=9` |
| `actualizarCicloVisita($acta)` | Busca ciclo pendiente del mes → marca `fecha_acta`, `estatus_mes=cumple`, auto-genera siguiente ciclo |

### 4.4 Flujos AJAX detallados

#### Autosave (store/update)
```
Frontend (autosave_server.js)          Backend (store/update)
─────────────────────────              ──────────────────────
Cada 60s o 5s post-input
POST FormData + headers:
  X-Autosave: 1                        isAutosaveRequest() → true
  X-Requested-With: XMLHttpRequest     Skip validación si autosave
                                        INSERT/UPDATE acta + detalles
← JSON {success:true, id:N, saved_at}  autosaveJsonSuccess($id)

Si es create nuevo y retorna id:
  - Cambia form.action a update URL
  - history.replaceState a edit URL
  - Limpia localStorage
```

#### Guardar firma
```
Frontend (firma.php)                   Backend (saveFirma)
────────────────────                   ────────────────────
Canvas → exportar() → base64 PNG
SweetAlert preview → confirmar
POST FormData {tipo, firma_imagen}     Decodifica base64
                                        Guarda PNG en uploads/inspecciones/firmas/
← JSON {success:true, campo}           UPDATE tbl_acta_visita SET firma_X
UI: marca paso como firmado
Auto-advance al siguiente paso
```

#### Finalizar
```
Frontend (firma.php)                   Backend (finalizar)
────────────────────                   ────────────────────
SweetAlert confirm + checkbox
POST JSON {}                           Verifica firma_consultor obligatoria
                                        Verifica firma_administrador si hay integrante ADMIN
                                        generarPdfInterno() → DOMPDF
                                        UPDATE estado='completo', ruta_pdf
                                        uploadToReportes()
                                        InspeccionEmailNotifier::enviar()
                                        actualizarCicloVisita()
← JSON {success, pdf_url, email_msg}
SweetAlert success → abrir PDF
Redirect a /inspecciones
```

#### Carga de temas abiertos (form)
```
Frontend (form.php)                    Backend (InspeccionesController)
────────────────────                   ──────────────────────────────
onChange #selectCliente
fetch /inspecciones/api/pendientes/N   → JSON pendientes abiertos
fetch /inspecciones/api/mantenimientos/N → JSON mantenimientos ≤30 días
Renderiza en #temasAbiertosContent
```

### 4.5 Integraciones con otros módulos

| Módulo | Tipo de integración |
|--------|-------------------|
| **Reportes** (`tbl_reporte`) | Al finalizar, se copia el PDF y se crea/actualiza un registro de reporte para que el cliente lo vea en su panel |
| **Pendientes** (`tbl_pendientes`) | Los compromisos del acta se guardan como pendientes ABIERTOS. Se muestran en la sección automática del PDF |
| **Mantenimientos** (`tbl_vencimientos_mantenimientos`) | Se consultan mantenimientos por vencer (≤30 días) para mostrar en el form y en el PDF |
| **Ciclos de Visita** (`tbl_ciclos_visita`) | Al finalizar se marca el ciclo del mes como cumplido y se auto-genera el siguiente |
| **Agendamiento** (`tbl_agendamiento`) | Vínculo opcional vía `agenda_id` |
| **Email (SendGrid)** | Al finalizar se envía email con PDF adjunto al cliente, consultor y consultor externo |
| **Panel Cliente** (`ClientInspeccionesController`) | El cliente puede ver sus actas de visita en su panel de inspecciones |

---

## 5. DEPENDENCIAS EXTERNAS

### 5.1 PHP (Composer)

| Librería | Uso |
|----------|-----|
| `dompdf/dompdf` (3.0.0) | Generación de PDF desde HTML |
| `sendgrid/sendgrid` | Envío de emails con adjuntos vía API v3 |

### 5.2 CDN Frontend

| Librería | Versión | CDN |
|----------|---------|-----|
| Bootstrap | 5.3.0 | `cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css` |
| Bootstrap JS | 5.3.0 | `cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js` |
| Font Awesome | 6.4.0 | `cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css` |
| SweetAlert2 | 11.x | `cdn.jsdelivr.net/npm/sweetalert2@11` |
| jQuery | 3.7.0 | `code.jquery.com/jquery-3.7.0.min.js` |
| Select2 | 4.1.0-rc.0 | `cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js` |

### 5.3 Assets locales

| Archivo | Propósito |
|---------|-----------|
| `public/js/autosave_server.js` | Motor de autoguardado servidor + localStorage |
| `public/manifest_inspecciones.json` | Manifest PWA |
| `public/sw_inspecciones.js` | Service Worker para cache PWA |
| `public/icons/icon-192.png` | Ícono PWA |

---

## 6. PATRONES ESPECIALES

### 6.1 Autoguardado dual (servidor + localStorage)

El módulo implementa un sistema de autoguardado en dos capas:

1. **Servidor** (`autosave_server.js`): Cada 60 segundos o 5 segundos después del último input, envía un POST AJAX con header `X-Autosave: 1`. El controlador detecta esto con `isAutosaveRequest()` (trait `AutosaveJsonTrait`) y salta validaciones. Responde JSON `{success, id, saved_at}`.

2. **localStorage** (fallback): Si no hay conexión o no se cumplen campos mínimos (`id_cliente` + `fecha_visita`), se guarda en localStorage con key `acta_draft_{id|new}`. Al recargar, SweetAlert ofrece restaurar (si < 24 horas).

3. **Transición create → edit**: Al autosave de un acta nueva, el servidor retorna el `id`. El JS actualiza `form.action`, hace `history.replaceState` a la URL de edit, y limpia localStorage.

Configuración en la vista:
```javascript
initAutosave({
    formId: 'actaForm',
    storeUrl: base_url('/inspecciones/acta-visita/store'),
    updateUrlBase: base_url('/inspecciones/acta-visita/update/'),
    editUrlBase: base_url('/inspecciones/acta-visita/edit/'),
    recordId: <?= $acta['id'] ?? 'null' ?>,
    isEdit: <?= $isEdit ? 'true' : 'false' ?>,
    storageKey: STORAGE_KEY,
    intervalSeconds: 60,
    minFieldsCheck: function() {
        var cliente = document.querySelector('[name="id_cliente"]');
        var fecha = document.querySelector('[name="fecha_visita"]');
        return cliente && cliente.value && fecha && fecha.value;
    },
});
```

### 6.2 Sistema de firmas digitales

- **Canvas HTML5** con clase `SignatureCanvas` que maneja mouse y touch.
- **Protección multi-touch**: `if (e.touches.length > 1) return;` evita trazos accidentales al hacer pinch-zoom.
- **Validación de píxeles**: `validarMinPixeles(100)` — rechaza firmas con menos de 100 píxeles oscuros.
- **Preview SweetAlert**: Antes de enviar, muestra la firma recortada (auto-crop + resize a 150px alto).
- **Almacenamiento**: Base64 → decode → PNG en `uploads/inspecciones/firmas/firma_{tipo}_{id}_{timestamp}.png`.
- **Firmantes dinámicos**: Se detectan del array de integrantes por rol (ADMINISTRADOR → firma_administrador, VIGÍA → firma_vigia). El consultor siempre firma.
- **Vigía opcional**: Botón "No aplica" permite saltar la firma del vigía.

### 6.3 Generación de PDF (DOMPDF)

- **Librería**: DOMPDF 3.0.0
- **Opciones obligatorias**: `isRemoteEnabled(true)` + `isHtml5ParserEnabled(true)`
- **Márgenes**: `@page { margin: 100px 70px 80px 90px; }` (en `px`, NO `cm`)
- **Layout**: Todo con tablas HTML (DOMPDF no soporta Flexbox/Grid)
- **Imágenes**: Firmas, fotos y logo se convierten a base64 con `file_get_contents()` + `mime_content_type()` antes de pasar al template
- **Fotos**: Se renderizan en tabla de 3 columnas con `array_chunk($fotos, 3)`
- **Almacenamiento**: `uploads/inspecciones/pdfs/acta_visita_{id}_{fecha}.pdf`
- **Regeneración**: `generatePdf()` siempre regenera desde el template actual (no sirve cache)

### 6.4 Upload a reportes

Al finalizar, el PDF se copia a `uploads/{nit_cliente}/` y se registra en `tbl_reporte`:
```php
$data = [
    'titulo_reporte'  => 'ACTA DE VISITA - {cliente} - {fecha}',
    'id_detailreport' => 9,    // Actas de visita
    'id_report_type'  => 6,    // Inspecciones
    'id_cliente'      => $acta['id_cliente'],
    'estado'          => 'CERRADO',
    'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. acta_id:{id}',
    'enlace'          => base_url('uploads/{nit}/{filename}'),
];
```
Si ya existe un reporte con `acta_id:{id}` en observaciones, lo actualiza en lugar de crear uno nuevo.

### 6.5 Envío de email (SendGrid)

- **Clase**: `InspeccionEmailNotifier::enviar()` — estática, reutilizable por todos los módulos de inspecciones.
- **Destinatarios**: `correo_cliente` + `correo_consultor` + `email_consultor_externo` (todos opcionales, al menos uno debe existir).
- **Adjunto**: PDF en base64 adjuntado con `addAttachment()`.
- **API Key**: Desde `getenv('SENDGRID_API_KEY')`.
- **Se envía en 2 momentos**: Automáticamente al finalizar, y manualmente con el botón "Enviar por Email" en la vista.

### 6.6 Hook de ciclo de visita

Al finalizar un acta, se busca en `tbl_ciclos_visita` un registro para el mismo cliente, mes y año del acta:
```php
$ciclo = $cicloModel->where('id_cliente', $acta['id_cliente'])
    ->where('mes_esperado', $mesActa)
    ->where('anio', $anioActa)
    ->first();
```
Si existe, se actualiza con `fecha_acta`, `id_acta`, `estatus_mes=cumple`, y se llama `generarSiguienteCiclo()` si tiene estándar definido.

### 6.7 GPS automático

En el formulario, se captura la ubicación GPS del dispositivo automáticamente:
```javascript
navigator.geolocation.getCurrentPosition(pos => {
    document.getElementById('ubicacionGps').value = pos.coords.latitude + ',' + pos.coords.longitude;
}, () => { /* error */ }, { enableHighAccuracy: true, timeout: 10000 });
```
Se almacena en `ubicacion_gps` como string `"lat,lng"`.

### 6.8 Patrón Replace-For-Acta (detalle)

Los modelos de detalle (`IntegranteModel`, `TemaModel`) implementan `replaceForActa()`:
```php
public function replaceForActa(int $idActa, array $items) {
    $this->where('id_acta_visita', $idActa)->delete();  // Borra todos
    foreach ($items as $i => $item) {
        $this->insert([...item, 'orden' => $i + 1]);    // Re-inserta con orden
    }
}
```
Esto simplifica la lógica de update: siempre DELETE + INSERT en lugar de hacer diff.

---

## 7. ORDEN DE IMPLEMENTACIÓN

### Paso 1: Base de datos

1. Crear `tbl_acta_visita` (tabla principal)
2. Crear `tbl_acta_visita_integrantes`
3. Crear `tbl_acta_visita_temas`
4. Crear `tbl_acta_visita_fotos`
5. ALTER `tbl_pendientes` para agregar `id_acta_visita` con FK
6. Verificar que existan las tablas del sistema: `tbl_clientes`, `tbl_consultor`, `tbl_reporte`, `tbl_pendientes`, `tbl_vencimientos_mantenimientos`, `tbl_mantenimientos`, `tbl_ciclos_visita`

### Paso 2: Modelos

1. `ActaVisitaModel` — con métodos `getByConsultor()`, `getPendientesByConsultor()`, `getAllPendientes()`, `getByCliente()`
2. `ActaVisitaTemaModel` — con `getByActa()` y `replaceForActa()`
3. `ActaVisitaIntegranteModel` — con `getByActa()` y `replaceForActa()`
4. `ActaVisitaFotoModel` — con `getByActa()`
5. Verificar que existan: `PendientesModel`, `ClientModel`, `ConsultantModel`, `ReporteModel`, `VencimientosMantenimientoModel`, `CicloVisitaModel`

### Paso 3: Trait y Library compartidos

1. `AutosaveJsonTrait` — `isAutosaveRequest()`, `autosaveJsonSuccess()`, `autosaveJsonError()`
2. `InspeccionEmailNotifier` — método estático `enviar()` con SendGrid

### Paso 4: Rutas

1. Registrar las 14 rutas dentro del grupo `inspecciones` con filtro `auth`
2. Verificar que existan las 3 rutas API (`api/clientes`, `api/pendientes/:id`, `api/mantenimientos/:id`)

### Paso 5: Controlador

1. `ActaVisitaController` con `use AutosaveJsonTrait`
2. Implementar en orden: `list()` → `create()` → `store()` → `edit()` → `update()` → `view()` → `delete()`
3. Implementar: `firma()` → `saveFirma()` → `finalizar()`
4. Implementar: `generatePdf()` → `regenerarPdf()` → `enviarEmail()`
5. Métodos privados: `saveIntegrantes()`, `saveTemas()`, `saveCompromisos()`, `saveFotos()`, `generarPdfInterno()`, `uploadToReportes()`, `actualizarCicloVisita()`

### Paso 6: Layout PWA

1. `layout_pwa.php` — HTML base con topbar, bottomnav, CDNs (Bootstrap 5.3, FA 6, SweetAlert2, jQuery, Select2)
2. CSS inline con variables (`--primary-dark: #1c2437`, `--gold-primary: #bd9751`)
3. Flash messages con SweetAlert toast
4. Registro de Service Worker

### Paso 7: Vistas

1. `list.php` — Listado con Select2 filter, cards con badges de estado, SweetAlert delete confirm
2. `form.php` — Accordion con 7 secciones, dynamic rows JS, GPS auto, autoguardado, botones "Guardar borrador" + "Ir a firmas"
3. `view.php` — Lectura con cards, modal para fotos, botones PDF/Regenerar/Email
4. `firma.php` — Canvas por firmante, clase `SignatureCanvas`, navegación pasos, botón finalizar con checkbox confirm
5. `pdf.php` — Template HTML tablas para DOMPDF (header corporativo, secciones numeradas, firmas al pie, galería fotos)
6. `client/inspecciones/acta_visita_view.php` — Vista simplificada para el panel del cliente

### Paso 8: JavaScript

1. `autosave_server.js` — Motor genérico de autoguardado (copiar tal cual, es reutilizable)

### Paso 9: Directorios de uploads

Crear estas carpetas con permisos 755 (o 775 en producción):
```
uploads/inspecciones/firmas/
uploads/inspecciones/fotos/
uploads/inspecciones/pdfs/
```

### Paso 10: Integraciones opcionales

1. Hook de ciclo de visita (si se usa `tbl_ciclos_visita`)
2. Upload a reportes (si se usa `tbl_reporte`)
3. PWA (manifest + Service Worker)

---

## 8. CÓDIGO CLAVE — FRAGMENTOS DE REFERENCIA

### 8.1 Trait AutosaveJsonTrait

```php
trait AutosaveJsonTrait
{
    protected function isAutosaveRequest(): bool
    {
        return $this->request->isAJAX()
            || $this->request->getHeaderLine('X-Autosave') === '1';
    }

    protected function autosaveJsonSuccess(int $id, array $extra = [])
    {
        return $this->response->setJSON(array_merge([
            'success'  => true,
            'id'       => $id,
            'saved_at' => date('H:i:s'),
        ], $extra));
    }

    protected function autosaveJsonError(string $message, int $statusCode = 400)
    {
        return $this->response->setJSON([
            'success' => false,
            'message' => $message,
        ])->setStatusCode($statusCode);
    }
}
```

### 8.2 Patrón replaceForActa (ejemplo Integrantes)

```php
public function replaceForActa(int $idActa, array $integrantes)
{
    $this->where('id_acta_visita', $idActa)->delete();

    foreach ($integrantes as $i => $integrante) {
        $this->insert([
            'id_acta_visita' => $idActa,
            'nombre'         => $integrante['nombre'],
            'rol'            => $integrante['rol'],
            'orden'          => $i + 1,
        ]);
    }
}
```

### 8.3 Firma: decodificación base64 → PNG

```php
$firmaData = explode(',', $firmaBase64);
$firmaDecoded = base64_decode(end($firmaData));

$dir = FCPATH . 'uploads/inspecciones/firmas/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$nombreArchivo = "firma_{$tipo}_{$id}_" . time() . '.png';
file_put_contents($dir . $nombreArchivo, $firmaDecoded);

$this->actaModel->update($id, [
    "firma_{$tipo}" => "uploads/inspecciones/firmas/{$nombreArchivo}",
]);
```

### 8.4 DOMPDF: opciones y render

```php
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
file_put_contents(FCPATH . $pdfPath, $dompdf->output());
```

### 8.5 Clase SignatureCanvas (JS, resumida)

```javascript
class SignatureCanvas {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.dibujando = false;
        this.hayDibujo = false;
        this.dpr = window.devicePixelRatio || 1;
        this.setup();
    }

    setup() {
        // Scale canvas for retina, set stroke styles
        // Bind mouse + touch events (with multi-touch filter)
    }

    validarMinPixeles(minimo = 100) {
        // Count non-transparent pixels, reject if < minimo
    }

    exportar() {
        // Auto-crop to bounding box + resize to 150px height
        return canvas.toDataURL('image/png');
    }

    limpiar() { /* clear + re-setup */ }
}
```

### 8.6 initAutosave — configuración completa

```javascript
initAutosave({
    formId: 'actaForm',
    storeUrl: '/inspecciones/acta-visita/store',
    updateUrlBase: '/inspecciones/acta-visita/update/',
    editUrlBase: '/inspecciones/acta-visita/edit/',
    recordId: null,  // o ID existente
    isEdit: false,   // o true
    storageKey: 'acta_draft_new',
    intervalSeconds: 60,
    minFieldsCheck: function() {
        var cliente = document.querySelector('[name="id_cliente"]');
        var fecha = document.querySelector('[name="fecha_visita"]');
        return cliente && cliente.value && fecha && fecha.value;
    },
});
```

---

## 9. ESTRUCTURA DE DIRECTORIOS

```
app/
├── Config/
│   └── Routes.php                          ← líneas 872-885
├── Controllers/
│   └── Inspecciones/
│       ├── ActaVisitaController.php         ← controlador principal
│       └── InspeccionesController.php       ← API endpoints AJAX
├── Models/
│   ├── ActaVisitaModel.php
│   ├── ActaVisitaTemaModel.php
│   ├── ActaVisitaIntegranteModel.php
│   ├── ActaVisitaFotoModel.php
│   ├── PendientesModel.php                 ← compartido
│   ├── CicloVisitaModel.php                ← compartido
│   ├── ClientModel.php                     ← compartido
│   ├── ConsultantModel.php                 ← compartido
│   ├── ReporteModel.php                    ← compartido
│   └── VencimientosMantenimientoModel.php  ← compartido
├── Views/
│   ├── inspecciones/
│   │   ├── layout_pwa.php                  ← layout compartido PWA
│   │   ├── dashboard.php
│   │   └── acta_visita/
│   │       ├── list.php
│   │       ├── form.php
│   │       ├── view.php
│   │       ├── firma.php
│   │       └── pdf.php
│   └── client/
│       └── inspecciones/
│           ├── list.php
│           └── acta_visita_view.php
├── Traits/
│   └── AutosaveJsonTrait.php
├── Libraries/
│   └── InspeccionEmailNotifier.php
├── Commands/
│   └── AuditoriaVisitasCron.php
└── SQL/
    ├── migrate_acta_visita.php
    ├── migrate_ciclos_visita.php
    └── seed_ciclos_visita.php

public/
├── js/
│   └── autosave_server.js
├── uploads/
│   └── inspecciones/
│       ├── firmas/                          ← PNGs de firmas
│       ├── fotos/                           ← fotos subidas
│       └── pdfs/                            ← PDFs generados
├── manifest_inspecciones.json
├── sw_inspecciones.js
└── icons/
    └── icon-192.png
```
