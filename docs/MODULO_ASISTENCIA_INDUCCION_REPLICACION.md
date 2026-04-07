# DOCUMENTO DE REPLICACION - Modulo Asistencia Induccion + Evaluacion SST

> Generado: 2026-04-06 | Framework: CodeIgniter 4 | BD: MySQL 8  
> Objetivo: Permitir a otro chat de IA replicar este modulo completo sin ambiguedades.

---

## TABLA DE CONTENIDOS

1. [Inventario de archivos](#1-inventario-de-archivos)
2. [Rutas del aplicativo](#2-rutas-del-aplicativo)
3. [Estructura de base de datos](#3-estructura-de-base-de-datos)
4. [Flujo funcional](#4-flujo-funcional)
5. [Dependencias externas](#5-dependencias-externas)
6. [Patrones especiales](#6-patrones-especiales)
7. [Orden de implementacion](#7-orden-de-implementacion)

---

## 1. INVENTARIO DE ARCHIVOS

### 1.1 Controladores

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/Controllers/Inspecciones/AsistenciaInduccionController.php` | 805 | CRUD asistencia, registro asistentes con firma, PDF DOMPDF, email SendGrid, IA OpenAI para objetivos |
| `app/Controllers/Inspecciones/EvaluacionInduccionController.php` | 376 | CRUD evaluaciones, formulario publico por token, calificacion automatica, QR, sesiones por cliente |
| `app/Controllers/ClientInspeccionesController.php` | 2171 | Portal cliente read-only: `listAsistenciaInduccion()` y `viewAsistenciaInduccion()` |
| `app/Controllers/PdfUnificadoController.php` | 527 | PDF unificado - policy type 19 (Programa Induccion) y 20 (Evaluacion Induccion) |

### 1.2 Modelos

| Archivo | Tabla BD | Lineas | Proposito |
|---------|----------|--------|-----------|
| `app/Models/AsistenciaInduccionModel.php` | `tbl_asistencia_induccion` | 60 | Master: sesiones de asistencia con joins a clientes/consultores |
| `app/Models/AsistenciaInduccionAsistenteModel.php` | `tbl_asistencia_induccion_asistente` | 18 | Detalle: asistentes individuales con firma |
| `app/Models/EvaluacionInduccionModel.php` | `tbl_evaluaciones` | 20 | Master: evaluaciones con token unico |
| `app/Models/EvaluacionInduccionRespuestaModel.php` | `tbl_evaluacion_respuestas` | 31 | Respuestas con calificacion calculada |
| `app/Models/EvaluacionTemaModel.php` | `tbl_evaluacion_tema` | 35 | Catalogo de temas con conteo de preguntas |
| `app/Models/EvaluacionPreguntaModel.php` | `tbl_evaluacion_pregunta` | 65 | Preguntas + opciones + calculo de calificacion |
| `app/Models/EvaluacionSesionModel.php` | `tbl_evaluacion_sesiones` | 55 | Agrupacion respuestas por cliente+fecha, codigo EV-YYYY-NNNN |

### 1.3 Vistas - Asistencia Induccion

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/Views/inspecciones/asistencia-induccion/form.php` | 329 | Formulario crear/editar sesion (Select2 cliente, AI objetivo, autosave) |
| `app/Views/inspecciones/asistencia-induccion/list.php` | 87 | Listado DataTables de sesiones con badges de estado |
| `app/Views/inspecciones/asistencia-induccion/view.php` | 134 | Vista detalle: datos sesion + tabla asistentes + botones PDF/email |
| `app/Views/inspecciones/asistencia-induccion/registrar.php` | 434 | Registro uno-a-uno de asistentes con canvas de firma integrado (AJAX) |
| `app/Views/inspecciones/asistencia-induccion/firmas.php` | 429 | Captura de firmas offline para asistentes sin firma |
| `app/Views/inspecciones/asistencia-induccion/pdf.php` | 230 | Template DOMPDF: genera FT-SST-005 (asistencia) o FT-SST-003 (responsabilidades) segun `$pdfType` |

### 1.4 Vistas - Evaluacion Induccion

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/Views/inspecciones/evaluacion-induccion/form.php` | 84 | Formulario admin crear/editar evaluacion (Select2 cliente + tema) |
| `app/Views/inspecciones/evaluacion-induccion/list.php` | 57 | Listado de evaluaciones con estadisticas (promedio, aprobados) |
| `app/Views/inspecciones/evaluacion-induccion/view.php` | 190 | Vista detalle: QR, enlace compartible, resultados por cliente/sesion |
| `app/Views/inspecciones/evaluacion-induccion/form-publico.php` | 168 | Formulario PUBLICO (sin auth): datos personales + preguntas + GDPR |
| `app/Views/inspecciones/evaluacion-induccion/cerrado.php` | 25 | Pagina evaluacion cerrada/no disponible |
| `app/Views/inspecciones/evaluacion-induccion/gracias.php` | 40 | Pagina resultado post-evaluacion con circulo de calificacion |
| `app/Views/inspecciones/evaluacion-induccion/resultados.php` | 91 | Tabla de calificaciones con promedio |

### 1.5 Vistas - Portal Cliente

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/Views/client/inspecciones/asistencia_induccion_view.php` | 105 | Vista read-only: datos sesion + tabla asistentes con miniatura firma |

### 1.6 Layout y Wrapper

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/Views/inspecciones/layout_pwa.php` | ~300 | Layout PWA: topbar, bottomnav, CSS dark theme dorado, scripts globales |

### 1.7 Traits (compartidos con otros modulos de inspecciones)

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/Traits/AutosaveJsonTrait.php` | 30 | `isAutosaveRequest()`, `autosaveJsonSuccess()`, `autosaveJsonError()` |
| `app/Traits/PreventDuplicateBorradorTrait.php` | 65 | `reuseExistingBorrador()` - evita duplicados cliente+fecha+consultor |
| `app/Traits/ImagenCompresionTrait.php` | 200 | Compresion JPEG, correccion EXIF, base64 para PDF, servir PDF |

### 1.8 Libraries

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/Libraries/InspeccionEmailNotifier.php` | ~170 | Email SendGrid con PDF adjunto a cliente+consultor+consultor externo |

### 1.9 JavaScript

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `public/js/autosave_server.js` | 263 | Motor autosave: FormData selectivo, timer 60s, debounce, create-to-edit |
| `public/js/offline_queue.js` | 198 | Cola IndexedDB para firmas offline, Background Sync |
| `public/js/prevent_double_tap.js` | 95 | Anti doble-tap: cooldown 2s, overlay loading, warning offline |

### 1.10 Migraciones SQL

| Archivo | Lineas | Proposito |
|---------|--------|-----------|
| `app/SQL/migrate_asistencia_induccion.php` | 118 | CREATE TABLE master + detalle asistentes |
| `app/SQL/create_evaluacion_induccion.php` | 93 | CREATE TABLE evaluaciones + respuestas + ALTER asistencia |
| `app/SQL/migrate_evaluacion_preguntas.php` | 230 | CREATE TABLE tema + pregunta + opcion + seed 10 preguntas |
| `app/SQL/migrate_evaluacion_sesion.php` | 63 | CREATE TABLE sesiones + retroactivo desde respuestas |

### 1.11 Configuracion

| Archivo | Seccion relevante | Proposito |
|---------|-------------------|-----------|
| `app/Config/Routes.php` | Lineas 873-876, 1212-1241 | Rutas publicas evaluacion + CRUD admin |

---

## 2. RUTAS DEL APLICATIVO

### 2.1 Asistencia Induccion - Admin (requiere auth)

Grupo: `inspecciones/` | Namespace: `App\Controllers\Inspecciones` | Filter: `auth`

| Metodo | URL | Controller::Method | Descripcion |
|--------|-----|-------------------|-------------|
| GET | `/inspecciones/asistencia-induccion` | `AsistenciaInduccionController::list` | Listado de sesiones |
| GET | `/inspecciones/asistencia-induccion/create` | `::create` | Form nueva sesion |
| GET | `/inspecciones/asistencia-induccion/create/{idCliente}` | `::create/$1` | Form nueva con cliente pre-seleccionado |
| POST | `/inspecciones/asistencia-induccion/store` | `::store` | Guardar nueva sesion |
| GET | `/inspecciones/asistencia-induccion/edit/{id}` | `::edit/$1` | Form editar sesion |
| POST | `/inspecciones/asistencia-induccion/update/{id}` | `::update/$1` | Actualizar sesion |
| GET | `/inspecciones/asistencia-induccion/view/{id}` | `::view/$1` | Ver detalle sesion |
| GET | `/inspecciones/asistencia-induccion/delete/{id}` | `::delete/$1` | Eliminar sesion + asistentes + firmas + PDFs |
| GET | `/inspecciones/asistencia-induccion/registrar/{id}` | `::registrar/$1` | Vista registro asistentes uno-a-uno |
| POST | `/inspecciones/asistencia-induccion/store-asistente/{id}` | `::storeAsistente/$1` | **AJAX**: agregar asistente con firma base64 |
| POST | `/inspecciones/asistencia-induccion/delete-asistente/{id}` | `::deleteAsistente/$1` | **AJAX**: eliminar asistente |
| GET | `/inspecciones/asistencia-induccion/firmas/{id}` | `::firmas/$1` | Vista captura firmas pendientes |
| POST | `/inspecciones/asistencia-induccion/guardar-firma/{id}` | `::guardarFirma/$1` | **AJAX**: guardar firma individual |
| POST | `/inspecciones/asistencia-induccion/finalizar/{id}` | `::finalizar/$1` | **AJAX**: finalizar + generar PDF + email |
| GET | `/inspecciones/asistencia-induccion/pdf/{id}` | `::generatePdf/$1` | Servir PDF asistencia (cache o genera) |
| GET | `/inspecciones/asistencia-induccion/pdf-responsabilidades/{id}` | `::generatePdfResponsabilidades/$1` | PDF responsabilidades SST (solo induccion_reinduccion) |
| GET | `/inspecciones/asistencia-induccion/regenerar/{id}` | `::regenerarPdf/$1` | Regenerar PDF de registro completo |
| GET | `/inspecciones/asistencia-induccion/enviar-email/{id}` | `::enviarEmail/$1` | Re-enviar email con PDF adjunto |
| POST | `/inspecciones/asistencia-induccion/generar-objetivo` | `::generarObjetivo` | **AJAX/JSON**: genera objetivo con OpenAI |

### 2.2 Evaluacion Induccion - Admin (requiere auth)

Mismo grupo `inspecciones/`:

| Metodo | URL | Controller::Method | Descripcion |
|--------|-----|-------------------|-------------|
| GET | `/inspecciones/evaluacion-induccion` | `EvaluacionInduccionController::list` | Listado evaluaciones con estadisticas |
| GET | `/inspecciones/evaluacion-induccion/create` | `::create` | Form nueva evaluacion |
| POST | `/inspecciones/evaluacion-induccion/store` | `::store` | Guardar evaluacion + generar token |
| GET | `/inspecciones/evaluacion-induccion/edit/{id}` | `::edit/$1` | Form editar evaluacion |
| POST | `/inspecciones/evaluacion-induccion/update/{id}` | `::update/$1` | Actualizar evaluacion |
| GET | `/inspecciones/evaluacion-induccion/view/{id}` | `::view/$1` | Ver detalle + QR + resultados por sesion |
| GET | `/inspecciones/evaluacion-induccion/delete/{id}` | `::delete/$1` | Eliminar evaluacion + respuestas |
| GET | `/inspecciones/evaluacion-induccion/toggle/{id}` | `::toggleEstado/$1` | Activar/cerrar evaluacion |
| GET | `/inspecciones/evaluacion-induccion/api-resultados-fecha` | `::apiResultadosPorFecha` | **API JSON**: resultados por cliente+fecha (+-7 dias) |

### 2.3 Evaluacion - Publico (SIN autenticacion)

| Metodo | URL | Controller::Method | Descripcion |
|--------|-----|-------------------|-------------|
| GET | `/evaluar/{token}` | `EvaluacionInduccionController::form/$1` | Formulario publico de evaluacion |
| POST | `/evaluar/{token}/submit` | `::submit/$1` | Enviar respuestas, calcular calificacion |
| GET | `/evaluar/{token}/gracias` | `::gracias/$1` | Resultado con calificacion visual |

### 2.4 Portal Cliente (read-only, requiere auth)

Grupo: `client/inspecciones/` | Filter: `auth`

| Metodo | URL | Controller::Method | Descripcion |
|--------|-----|-------------------|-------------|
| GET | `/client/inspecciones/asistencia-induccion` | `ClientInspeccionesController::listAsistenciaInduccion` | Listado de sesiones del cliente |
| GET | `/client/inspecciones/asistencia-induccion/{id}` | `::viewAsistenciaInduccion/$1` | Vista read-only de sesion |

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tabla: `tbl_asistencia_induccion` (Master)

```sql
CREATE TABLE IF NOT EXISTS tbl_asistencia_induccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_sesion DATE NOT NULL,

    -- Datos de la sesion
    tema TEXT NULL,
    lugar VARCHAR(255) NULL,
    objetivo TEXT NULL,
    capacitador VARCHAR(255) NULL,
    tipo_charla ENUM('induccion_reinduccion','reunion','charla','capacitacion','otros_temas') NULL,
    material VARCHAR(255) NULL,
    tiempo_horas DECIMAL(4,1) NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf_asistencia VARCHAR(255) NULL,
    ruta_pdf_responsabilidades VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',

    -- Evaluacion (agregado via create_evaluacion_induccion.php)
    evaluacion_habilitada TINYINT(1) NOT NULL DEFAULT 0,
    evaluacion_token VARCHAR(64) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_asist_ind_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_asist_ind_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_asist_ind_cliente (id_cliente),
    INDEX idx_asist_ind_consultor (id_consultor),
    INDEX idx_asist_ind_estado (estado),
    INDEX idx_asist_ind_tipo (tipo_charla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Tabla: `tbl_asistencia_induccion_asistente` (Detalle)

```sql
CREATE TABLE IF NOT EXISTS tbl_asistencia_induccion_asistente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_asistencia INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    cedula VARCHAR(50) NOT NULL,
    cargo VARCHAR(255) NULL,
    firma VARCHAR(255) NULL,  -- Ruta relativa al PNG de firma digital
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_asist_det_master FOREIGN KEY (id_asistencia)
        REFERENCES tbl_asistencia_induccion(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_asist_det_master (id_asistencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.3 Tabla: `tbl_evaluaciones` (Master evaluaciones)

> Nota: el CREATE original dice `tbl_evaluacion_induccion`, pero el Model apunta a `tbl_evaluaciones`. 
> Fue renombrada via `rename_evaluacion_tables.php`.

```sql
CREATE TABLE IF NOT EXISTS tbl_evaluaciones (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_asistencia_induccion INT UNSIGNED NULL,
    id_cliente INT UNSIGNED NOT NULL,
    id_tema INT UNSIGNED NULL DEFAULT NULL,
    titulo VARCHAR(255) NOT NULL DEFAULT 'Evaluacion Induccion SST',
    token VARCHAR(64) NOT NULL,
    estado ENUM('activo','cerrado') NOT NULL DEFAULT 'activo',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.4 Tabla: `tbl_evaluacion_respuestas` (Respuestas evaluacion)

```sql
CREATE TABLE IF NOT EXISTS tbl_evaluacion_respuestas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_evaluacion INT UNSIGNED NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    cedula VARCHAR(30) NOT NULL,
    whatsapp VARCHAR(30) NOT NULL DEFAULT '',
    empresa_contratante VARCHAR(255) NOT NULL DEFAULT '',
    cargo VARCHAR(100) NOT NULL DEFAULT '',
    id_cliente_conjunto INT UNSIGNED NULL,  -- FK a tbl_clientes
    acepta_tratamiento TINYINT(1) NOT NULL DEFAULT 0,
    respuestas JSON NULL,           -- Array de letras: ["c","d","c",...]
    calificacion DECIMAL(5,2) NOT NULL DEFAULT 0.00,  -- 0-100
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_id_evaluacion (id_evaluacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.5 Tabla: `tbl_evaluacion_tema` (Catalogo de temas)

```sql
CREATE TABLE IF NOT EXISTS tbl_evaluacion_tema (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Seed data:**
```sql
INSERT INTO tbl_evaluacion_tema (nombre, descripcion, estado, created_at, updated_at)
VALUES ('Induccion SST PH',
        'Evaluacion de conocimientos en Seguridad y Salud en el Trabajo para proveedores y contratistas de propiedades horizontales.',
        'activo', NOW(), NOW());
```

### 3.6 Tabla: `tbl_evaluacion_pregunta`

```sql
CREATE TABLE IF NOT EXISTS tbl_evaluacion_pregunta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_tema INT UNSIGNED NOT NULL,
    orden TINYINT UNSIGNED NOT NULL DEFAULT 0,
    texto TEXT NOT NULL,
    correcta CHAR(1) NOT NULL COMMENT 'Letra correcta: a,b,c,d',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    KEY idx_tema_orden (id_tema, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.7 Tabla: `tbl_evaluacion_opcion`

```sql
CREATE TABLE IF NOT EXISTS tbl_evaluacion_opcion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pregunta INT UNSIGNED NOT NULL,
    letra CHAR(1) NOT NULL COMMENT 'a, b, c, d',
    texto TEXT NOT NULL,
    KEY idx_pregunta (id_pregunta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.8 Tabla: `tbl_evaluacion_sesiones`

```sql
CREATE TABLE IF NOT EXISTS tbl_evaluacion_sesiones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_evaluacion INT UNSIGNED NOT NULL,
    id_cliente INT UNSIGNED NOT NULL,
    fecha_sesion DATE NOT NULL,
    codigo VARCHAR(20) NOT NULL,  -- Formato: EV-YYYY-NNNN
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uk_codigo (codigo),
    UNIQUE KEY uk_sesion (id_evaluacion, id_cliente, fecha_sesion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.9 Seed: 10 preguntas del tema "Induccion SST PH"

```
Pregunta 1: "Cual es el principal objetivo del SG-SST?" → Correcta: c
Pregunta 2: "Quienes deben implementar el SG-SST en una propiedad horizontal?" → Correcta: d
Pregunta 3: "Que es un peligro en el contexto de SST?" → Correcta: c
Pregunta 4: "Diferencia entre peligro y riesgo?" → Correcta: d
Pregunta 5: "Funcion de la Brigada de Emergencia?" → Correcta: b
Pregunta 6: "Proposito de un FURAT?" → Correcta: b
Pregunta 7: "Que debe exigir la copropiedad sobre dotaciones?" → Correcta: d
Pregunta 8: "Politica sobre alcohol, tabaco y drogas?" → Correcta: c
Pregunta 9: "Objetivo de politica de prevencion ante emergencias?" → Correcta: c
Pregunta 10: "Tipo de emergencia mencionado como ejemplo?" → Correcta: d
```

(Texto completo de preguntas y opciones en `app/SQL/migrate_evaluacion_preguntas.php`)

### 3.10 Tablas del SISTEMA que el modulo consulta

| Tabla | Campos usados | Relacion |
|-------|---------------|----------|
| `tbl_clientes` | `id_cliente`, `nombre_cliente`, `nit_cliente`, `correo_cliente`, `logo`, `consultor_externo`, `email_consultor_externo`, `estado` | FK desde id_cliente |
| `tbl_consultor` | `id_consultor`, `nombre_consultor`, `correo_consultor` | FK desde id_consultor |
| `tbl_reporte` | `id_reporte`, `titulo_reporte`, `id_detailreport`, `id_report_type`, `id_cliente`, `estado`, `observaciones`, `enlace` | Upload PDF como reporte |
| `detail_report` | `id_detailreport=34` (Asistencia Induccion), `id_detailreport=35` (Responsabilidades SST) | Catalogo tipo reporte |
| `tbl_reporte_capacitacion` | `mostrar_evaluacion_induccion` | Flag para incluir evaluacion en reporte |

### 3.11 Report Type IDs

| id_report_type | id_detailreport | Significado |
|----------------|-----------------|-------------|
| 6 | 34 | FT-SST-005 Asistencia Induccion |
| 6 | 35 | FT-SST-003 Responsabilidades SST |

---

## 4. FLUJO FUNCIONAL

### 4.1 Ciclo de vida - Asistencia Induccion

```
BORRADOR ──────────────────────────────────────────> COMPLETO
   │                                                     │
   ├─ create/store → borrador                            ├─ finalizar() valida:
   ├─ edit/update → sigue borrador                       │   1. Al menos 1 asistente
   ├─ registrar → agregar asistentes (AJAX)              │   2. TODOS firmados
   ├─ firmas → capturar firmas pendientes                │   3. Genera PDF(s) DOMPDF
   │                                                     │   4. Upload a tbl_reporte
   │                                                     │   5. Envia email SendGrid
   │                                                     │
   └─ delete → elimina todo (firmas, PDFs, asistentes)   └─ regenerar → re-genera PDF
```

**Estados:** `borrador` | `completo`

### 4.2 Metodos del controlador AsistenciaInduccion

| Metodo | Tipo | Que hace |
|--------|------|----------|
| `list()` | GET/View | Lista sesiones con JOIN clientes+consultores, DataTables |
| `create($idCliente)` | GET/View | Form vacio, cliente opcional pre-seleccionado |
| `store()` | POST | Valida, previene duplicado borrador, inserta master + asistentes batch, autosave JSON |
| `edit($id)` | GET/View | Carga sesion + asistentes existentes en form |
| `update($id)` | POST | Solo actualiza metadatos (asistentes via AJAX), autosave compatible |
| `view($id)` | GET/View | Detalle completo con cliente+consultor+asistentes |
| `registrar($id)` | GET/View | Vista mobile-first para agregar asistentes uno a uno con firma canvas |
| `storeAsistente($id)` | POST/AJAX | Recibe nombre+cedula+cargo+firma(base64), decodifica PNG, guarda archivo, retorna JSON |
| `deleteAsistente($id)` | POST/AJAX | Elimina asistente + archivo firma, retorna total restante |
| `firmas($id)` | GET/View | Vista para capturar firmas de asistentes que no firmaron |
| `guardarFirma($id)` | POST/AJAX | Decodifica base64, guarda PNG, actualiza BD, elimina firma anterior |
| `finalizar($id)` | POST | Valida firmas completas, genera PDF, upload reportes, envia email |
| `generatePdf($id)` | GET | Sirve PDF (cache o genera), Content-Disposition inline |
| `generatePdfResponsabilidades($id)` | GET | Solo para tipo_charla='induccion_reinduccion' |
| `regenerarPdf($id)` | GET | Fuerza re-generacion de PDF(s) de registro completo |
| `delete($id)` | GET | Elimina master+asistentes+firmas+PDFs del disco |
| `enviarEmail($id)` | GET | Re-envia email con PDF(s) adjunto(s) via SendGrid |
| `generarObjetivo()` | POST/AJAX/JSON | Envia tema a OpenAI, retorna objetivo profesional SST |

### 4.3 Flujos AJAX detallados

**Agregar asistente (registrar.php → storeAsistente):**
```
Frontend:
  - Canvas firma → toDataURL('image/png') → base64
  - fetch POST {nombre, cedula, cargo, firma: base64, csrf_token}

Backend:
  - Decodifica base64 → PNG file en uploads/inspecciones/asistencia-induccion/firmas/
  - INSERT en tbl_asistencia_induccion_asistente
  - Return JSON {success, id_asistente, total, csrf_hash}

Frontend:
  - Agrega fila a tabla de asistentes
  - Actualiza contador
  - Limpia form + canvas
  - Actualiza csrf_hash
```

**Generar objetivo con IA (form.php → generarObjetivo):**
```
Frontend:
  - fetch POST JSON {tema: "texto del tema"}

Backend:
  - Construye prompt SST contextualizado
  - curl OpenAI API (gpt-4o-mini, max_tokens=200, temp=0.6)
  - Return JSON {objetivo: "texto generado"}

Frontend:
  - Llena textarea#objetivo con la respuesta
```

**Finalizar (registrar.php → finalizar):**
```
Frontend:
  - SweetAlert confirmacion
  - fetch POST (form submit)

Backend:
  1. Valida >= 1 asistente
  2. Valida todas las firmas completas
  3. generarPdfInterno() → PDF asistencia SIEMPRE + PDF responsabilidades SI tipo_charla='induccion_reinduccion'
  4. UPDATE estado='completo' + rutas PDF
  5. uploadToReportes() → copia PDF a uploads/{nit_cliente}/, INSERT/UPDATE en tbl_reporte
  6. InspeccionEmailNotifier::enviar() → SendGrid con PDF(s) adjunto(s)
  7. Redirect a view con mensaje
```

### 4.4 Ciclo de vida - Evaluacion Induccion

```
CREAR (admin) ──> ACTIVA ──> Compartir QR/Link ──> Asistentes responden (publico) ──> CERRADA
                    │                                         │
                    │                                         ├─ Calificacion automatica
                    │                                         ├─ Auto-crea sesion por cliente+fecha
                    │                                         └─ Resultado inmediato al usuario
                    │
                    └─ Admin ve resultados agrupados por cliente/sesion
```

**Estados:** `activo` | `cerrado` (toggle manual)

### 4.5 Metodos del controlador EvaluacionInduccion

| Metodo | Tipo | Que hace |
|--------|------|----------|
| `list()` | GET/View | Lista evaluaciones con >0 respuestas, calcula promedio/aprobados |
| `create()` | GET/View | Form con Select2 para cliente y tema |
| `store()` | POST | Genera token aleatorio (`bin2hex(random_bytes(20))`), inserta evaluacion |
| `edit($id)` | GET/View | Editar titulo, cliente, tema, estado |
| `view($id)` | GET/View | Detalle completo: QR inline, enlace copiable, resultados por sesion |
| `delete($id)` | GET | Elimina evaluacion + todas sus respuestas |
| `toggleEstado($id)` | GET | Alterna activo/cerrado |
| `apiResultadosPorFecha()` | GET/JSON | API: resultados por id_cliente + fecha +-7 dias |
| `form($token)` | GET/Public | Formulario publico: datos personales + preguntas dinamicas |
| `submit($token)` | POST/Public | Valida campos, calcula calificacion, auto-crea sesion |
| `gracias($token)` | GET/Public | Resultado visual con circulo de calificacion |

### 4.6 Calculo de calificacion

```php
// En EvaluacionPreguntaModel::calcularCalificacion()
$calificacion = (correctas / total_preguntas) * 100;
// Umbral de aprobacion: >= 70%
```

---

## 5. DEPENDENCIAS EXTERNAS

### 5.1 Librerias PHP (Composer)

| Libreria | Version | Para que se usa |
|----------|---------|-----------------|
| `dompdf/dompdf` | 3.0.0 | Generacion PDF (asistencia + responsabilidades) |
| `sendgrid/sendgrid` | - | Envio email con PDF adjunto |
| `chillerlan/php-qrcode` | - | Generacion QR inline (base64 PNG) para evaluaciones |

### 5.2 CDN Frontend (en layout_pwa.php)

| Libreria | Version | CDN |
|----------|---------|-----|
| Bootstrap CSS + JS | 5.3.0 | cdn.jsdelivr.net |
| Font Awesome | 6.4.0 | cdnjs.cloudflare.com |
| SweetAlert2 | 11 | cdn.jsdelivr.net |
| jQuery | 3.7.0 | code.jquery.com |
| Select2 CSS + JS | 4.1.0-rc.0 | cdn.jsdelivr.net |
| DataTables + Bootstrap5 | 2.1.8 | cdn.datatables.net |
| DataTables Responsive | 3.0.3 | cdn.datatables.net |

### 5.3 CDN adicionales en form-publico.php

| Libreria | Version | CDN |
|----------|---------|-----|
| Select2 | 4.0.13 | cdnjs.cloudflare.com |
| Select2 Bootstrap 5 Theme | 1.3.0 | cdnjs.cloudflare.com |

### 5.4 APIs externas

| API | Proposito | Configuracion |
|-----|-----------|---------------|
| OpenAI Chat Completions | Generar objetivo de sesion desde tema | `env('OPENAI_API_KEY')`, modelo `env('OPENAI_MODEL', 'gpt-4o-mini')` |
| SendGrid v3 | Enviar email con PDF adjunto | `getenv('SENDGRID_API_KEY')`, from: `notificacion.cycloidtalent@cycloidtalent.com` |

### 5.5 Assets locales JS

| Archivo | Proposito |
|---------|-----------|
| `/js/autosave_server.js` | Motor de autoguardado periodico |
| `/js/offline_queue.js` | Cola IndexedDB para firmas sin conexion |
| `/js/prevent_double_tap.js` | Prevencion de doble-tap en mobile |

---

## 6. PATRONES ESPECIALES

### 6.1 Patron Master-Detalle con firmas digitales

```
tbl_asistencia_induccion (1) ←→ (N) tbl_asistencia_induccion_asistente
```

- Asistentes se gestionan exclusivamente via AJAX (storeAsistente/deleteAsistente)
- Firma es un canvas HTML5, enviada como base64 PNG
- Archivos en: `uploads/inspecciones/asistencia-induccion/firmas/firma_{id}_{timestamp}_{random}.png`
- Al eliminar asistente, se elimina archivo de firma del disco
- Finalizacion requiere TODAS las firmas completas

### 6.2 Autosave (AutosaveJsonTrait)

- Se detecta autosave via header `X-Autosave: 1` o request AJAX
- `store()` retorna JSON con `{success, id, saved_at}` en vez de redirect
- `autosave_server.js` cambia URL de store a update despues del primer guardado
- Timer: cada 60s + debounce 5s tras input
- Previene duplicados con `PreventDuplicateBorradorTrait`

### 6.3 Prevencion de borradores duplicados (PreventDuplicateBorradorTrait)

```php
// Busca borrador existente para mismo cliente + fecha + consultor
$existing = $model->where('id_cliente', $idCliente)
    ->where($dateField, $fecha)
    ->where('id_consultor', $idConsultor)
    ->whereIn('estado', ['borrador', 'pendiente_firma'])
    ->first();
// Si existe → redirige al edit o retorna JSON con ID existente
```

### 6.4 Generacion dual de PDF con DOMPDF

La misma vista `pdf.php` genera 2 documentos distintos controlados por `$pdfType`:
- `$pdfType = 'asistencia'` → FT-SST-005: Listado de asistencia (SIEMPRE)
- `$pdfType = 'responsabilidades'` → FT-SST-003: Responsabilidades SST (SOLO si `tipo_charla === 'induccion_reinduccion'`)

Configuracion DOMPDF requerida:
```php
$options->set('isRemoteEnabled', true);      // Para logos remotos
$options->set('isHtml5ParserEnabled', true);  // Para HTML5
$dompdf->setPaper('letter', 'portrait');      // Carta vertical
```

**Conversion de imagenes a base64 para PDF:**
- Logo cliente: `FCPATH . 'uploads/' . $cliente['logo']` → `fotoABase64ParaPdf()`
- Firmas asistentes: cada firma PNG → `data:{mime};base64,{encoded}`

### 6.5 Upload a sistema de reportes

Despues de generar PDF, se registra automaticamente en `tbl_reporte`:
```php
$this->uploadToReportes($inspeccion, $pdfPath, $idDetailReport, $tag);
```
- Copia PDF a `{UPLOADS_PATH}/{nit_cliente}/`
- INSERT/UPDATE en tbl_reporte con tag unico para idempotencia (`asist_ind_id:{id}`)
- id_report_type = 6 siempre
- id_detailreport = 34 (asistencia) o 35 (responsabilidades)

### 6.6 Email con SendGrid (InspeccionEmailNotifier)

- Envia a: correo_cliente + correo_consultor + email_consultor_externo
- Template HTML corporativo (colores #1c2437 dorado #bd9751)
- PDF adjunto como base64 attachment
- Soporte para adjuntos adicionales (PDF responsabilidades)
- Variable de desactivacion: `env('DISABLE_REPORT_EMAILS')`

### 6.7 Evaluacion publica por token

- Token unico: `bin2hex(random_bytes(20))` = 40 chars hex
- URL publica sin autenticacion: `/evaluar/{token}`
- QR generado en memoria con `chillerlan/php-qrcode` como base64 inline
- Formulario incluye consentimiento GDPR (Ley 1581/2012 Colombia)
- Preguntas cargadas dinamicamente desde BD segun tema asociado
- Calificacion calculada server-side y mostrada al usuario inmediatamente
- Sesiones auto-creadas por cliente+fecha con codigo unico EV-YYYY-NNNN

### 6.8 Generacion de objetivo con IA

```php
// Endpoint: POST /inspecciones/asistencia-induccion/generar-objetivo
// Body: JSON {"tema": "Manejo de extintores"}
// Respuesta: JSON {"objetivo": "Capacitar al personal de aseo y vigilancia..."}
```

Prompt contextualizado para SST en propiedades horizontales colombianas. Modelo: gpt-4o-mini, max_tokens=200, temperature=0.6.

### 6.9 Cola offline (IndexedDB)

`offline_queue.js` permite capturar firmas sin conexion:
- Almacena en IndexedDB: tipo, URL, payload, id_asistencia
- Background Sync (`sync-firmas`) cuando vuelve conexion
- Fallback: sincroniza al detectar evento `online`

### 6.10 PWA Layout

Todas las vistas se envuelven en `layout_pwa.php`:
```php
return view('inspecciones/layout_pwa', [
    'content' => view('inspecciones/asistencia-induccion/...', $data),
    'title'   => 'Titulo',
]);
```
- Topbar fija con boton back
- Bottom nav con 5 items
- Floating home button (FAB)
- Service Worker registrado (`sw_inspecciones.js`)
- Flash messages via SweetAlert2 toast
- Anti-duplicacion de submit embebida
- Dark theme corporativo (#1c2437 + #bd9751 dorado)

### 6.11 Tipos de charla (constante del controlador)

```php
public const TIPOS_CHARLA = [
    'induccion_reinduccion' => 'Induccion / Reinduccion',
    'reunion'              => 'Reunion',
    'charla'               => 'Charla',
    'capacitacion'         => 'Capacitacion',
    'otros_temas'          => 'Otros Temas',
];
```

Solo `induccion_reinduccion` genera el segundo PDF de responsabilidades SST.

---

## 7. ORDEN DE IMPLEMENTACION

### Paso 1: Base de datos

Ejecutar en este orden:

```bash
# 1. Tablas master + detalle asistencia
php app/SQL/migrate_asistencia_induccion.php

# 2. Tablas evaluacion + respuestas + ALTER asistencia
php app/SQL/create_evaluacion_induccion.php

# 3. Tablas tema + pregunta + opcion + seed 10 preguntas
php app/SQL/migrate_evaluacion_preguntas.php

# 4. Tabla sesiones evaluacion
php app/SQL/migrate_evaluacion_sesion.php
```

Verificar que existan las tablas del sistema: `tbl_clientes`, `tbl_consultor`, `tbl_reporte`, `detail_report`.

Insertar registros catalogo si no existen:
```sql
INSERT INTO detail_report (id_detailreport, detail_report) VALUES (34, 'Asistencia Induccion');
INSERT INTO detail_report (id_detailreport, detail_report) VALUES (35, 'Responsabilidades SST');
```

### Paso 2: Rutas

Agregar a `app/Config/Routes.php`:

```php
// PUBLICAS (fuera de cualquier grupo, sin filter)
$routes->get('/evaluar/(:segment)/gracias', 'Inspecciones\EvaluacionInduccionController::gracias/$1');
$routes->post('/evaluar/(:segment)/submit', 'Inspecciones\EvaluacionInduccionController::submit/$1');
$routes->get('/evaluar/(:segment)', 'Inspecciones\EvaluacionInduccionController::form/$1');

// Dentro del grupo 'inspecciones' con filter auth:
// ... (copiar bloque de rutas de seccion 2.1 y 2.2)

// Dentro del grupo 'client/inspecciones' con filter auth:
$routes->get('asistencia-induccion', 'ClientInspeccionesController::listAsistenciaInduccion');
$routes->get('asistencia-induccion/(:num)', 'ClientInspeccionesController::viewAsistenciaInduccion/$1');
```

### Paso 3: Modelos

Crear en `app/Models/`:
1. `AsistenciaInduccionModel.php`
2. `AsistenciaInduccionAsistenteModel.php`
3. `EvaluacionInduccionModel.php`
4. `EvaluacionInduccionRespuestaModel.php`
5. `EvaluacionTemaModel.php`
6. `EvaluacionPreguntaModel.php`
7. `EvaluacionSesionModel.php`

### Paso 4: Traits y Libraries

Crear si no existen:
1. `app/Traits/AutosaveJsonTrait.php`
2. `app/Traits/PreventDuplicateBorradorTrait.php`
3. `app/Traits/ImagenCompresionTrait.php`
4. `app/Libraries/InspeccionEmailNotifier.php`

### Paso 5: Controladores

Crear en `app/Controllers/Inspecciones/`:
1. `AsistenciaInduccionController.php`
2. `EvaluacionInduccionController.php`

Agregar metodos a `ClientInspeccionesController.php`:
- `listAsistenciaInduccion()`
- `viewAsistenciaInduccion($id)`

### Paso 6: Vistas

Crear estructura de directorios:
```
app/Views/inspecciones/asistencia-induccion/
    form.php
    list.php
    view.php
    registrar.php
    firmas.php
    pdf.php

app/Views/inspecciones/evaluacion-induccion/
    form.php
    list.php
    view.php
    form-publico.php
    cerrado.php
    gracias.php
    resultados.php

app/Views/client/inspecciones/
    asistencia_induccion_view.php
```

Asegurar que exista: `app/Views/inspecciones/layout_pwa.php`

### Paso 7: JavaScript

Copiar a `public/js/`:
1. `autosave_server.js`
2. `offline_queue.js`
3. `prevent_double_tap.js`

### Paso 8: Directorios de uploads

```bash
mkdir -p public/uploads/inspecciones/asistencia-induccion/firmas/
mkdir -p public/uploads/inspecciones/asistencia-induccion/pdfs/
```

Permisos en produccion: `chmod 775` con owner `www`.

### Paso 9: Configuracion de entorno (.env)

```
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
SENDGRID_API_KEY=SG....
DISABLE_REPORT_EMAILS=false
```

### Paso 10: Dependencias Composer

```bash
composer require dompdf/dompdf
composer require sendgrid/sendgrid
composer require chillerlan/php-qrcode
```

### Paso 11: Integraciones

- Agregar links al dashboard de inspecciones (`app/Views/inspecciones/dashboard.php`)
- Agregar metodos al `ClientInspeccionesController` para el portal cliente
- Agregar links al listado de inspecciones del cliente (`app/Views/client/inspecciones/list.php`)
- Configurar el Service Worker (`sw_inspecciones.js`) para cachear las rutas nuevas

---

## DIAGRAMA DE RELACIONES BD

```
tbl_clientes ──────────┐
                       │
tbl_consultor ─────────┼──> tbl_asistencia_induccion ──> tbl_asistencia_induccion_asistente
                       │           │                              (firmas PNG)
                       │           │
                       │           ├── evaluacion_habilitada
                       │           └── evaluacion_token
                       │
                       ├──> tbl_evaluaciones ──────────> tbl_evaluacion_respuestas
                       │        │                               │
                       │        │                               └── id_cliente_conjunto → tbl_clientes
                       │        │
                       │        └── id_tema → tbl_evaluacion_tema
                       │                          │
                       │                          └── tbl_evaluacion_pregunta
                       │                                    │
                       │                                    └── tbl_evaluacion_opcion
                       │
                       └──> tbl_evaluacion_sesiones
                                (agrupa respuestas por cliente+fecha)

tbl_reporte ←── uploadToReportes() (PDFs generados)
detail_report ←── id_detailreport 34, 35
```

---

## CONSTANTES Y VALORES CLAVE

| Constante | Valor | Donde se usa |
|-----------|-------|-------------|
| `TIPOS_CHARLA` | 5 tipos enum | Controlador, form, pdf |
| `id_report_type` | 6 | uploadToReportes (capacitaciones) |
| `id_detailreport` asistencia | 34 | uploadToReportes |
| `id_detailreport` responsabilidades | 35 | uploadToReportes |
| Umbral aprobacion evaluacion | >= 70% | Calculo calificacion |
| Token evaluacion | 40 chars hex | `bin2hex(random_bytes(20))` |
| Codigo sesion | EV-YYYY-NNNN | Auto-generado, unique |
| Firma path | `uploads/inspecciones/asistencia-induccion/firmas/` | storeAsistente, guardarFirma |
| PDF path | `uploads/inspecciones/asistencia-induccion/pdfs/` | generarPdfInterno |

---

*Fin del documento de replicacion.*
