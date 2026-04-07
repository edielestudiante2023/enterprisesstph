# DOCUMENTO DE REPLICACION - Modulo Acta de Visita

> **Proyecto origen:** enterprisesstph (CodeIgniter 4 / PHP 8.2 / MySQL)
> **Fecha de extraccion:** 2026-04-06
> **Total archivos del modulo:** 24 archivos, ~5,500 lineas de codigo

---

## 1. INVENTARIO COMPLETO DE ARCHIVOS

### 1.1 Controlador

| Ruta | Lineas | Proposito |
|------|--------|-----------|
| `app/Controllers/Inspecciones/ActaVisitaController.php` | 1,438 | Controlador principal: CRUD, firmas, PDF, email, PTA, evaluaciones |

### 1.2 Modelos

| Ruta | Lineas | Tabla BD | Proposito |
|------|--------|----------|-----------|
| `app/Models/ActaVisitaModel.php` | 79 | `tbl_acta_visita` | Modelo maestro del acta |
| `app/Models/ActaVisitaIntegranteModel.php` | 42 | `tbl_acta_visita_integrantes` | Participantes/asistentes |
| `app/Models/ActaVisitaTemaModel.php` | 41 | `tbl_acta_visita_temas` | Temas tratados |
| `app/Models/ActaVisitaFotoModel.php` | 30 | `tbl_acta_visita_fotos` | Fotos y soportes |
| `app/Models/ActaVisitaPtaModel.php` | 38 | `tbl_acta_visita_pta` | Enlace acta-actividades PTA |

### 1.3 Vistas

| Ruta | Lineas | Proposito |
|------|--------|-----------|
| `app/Views/inspecciones/acta_visita/form.php` | 854 | Formulario crear/editar acta (con autosave) |
| `app/Views/inspecciones/acta_visita/list.php` | 87 | Listado DataTables de actas |
| `app/Views/inspecciones/acta_visita/view.php` | 224 | Vista solo-lectura del acta completa |
| `app/Views/inspecciones/acta_visita/pdf.php` | 346 | Template HTML para generacion PDF (DOMPDF) |
| `app/Views/inspecciones/acta_visita/firma.php` | 523 | Captura de firmas por canvas (multi-paso) |
| `app/Views/inspecciones/acta_visita/firma_remota.php` | 381 | Firma remota publica (via token WhatsApp) |
| `app/Views/inspecciones/acta_visita/firma_remota_error.php` | 20 | Error de token invalido/expirado |
| `app/Views/inspecciones/acta_visita/pta.php` | 238 | Vista intermedia: cerrar actividades PTA |
| `app/Views/inspecciones/acta_visita/evaluaciones_visita.php` | 203 | Evaluacion rapida post-visita (publica) |
| `app/Views/inspecciones/acta_visita/evaluaciones_visita_error.php` | 22 | Error de enlace evaluacion |
| `app/Views/client/inspecciones/acta_visita_view.php` | 142 | Vista del acta para portal del cliente |

### 1.4 JavaScript

| Ruta | Lineas | Proposito |
|------|--------|-----------|
| `public/js/autosave_server.js` | 262 | Motor de autoguardado AJAX con debounce |
| `public/js/offline_queue.js` | 197 | Cola offline IndexedDB para firmas sin conexion |

### 1.5 Traits

| Ruta | Lineas | Proposito |
|------|--------|-----------|
| `app/Traits/AutosaveJsonTrait.php` | 29 | Detecta request autosave y responde JSON |
| `app/Traits/ImagenCompresionTrait.php` | 247 | Compresion EXIF-aware, base64 para PDF, servir PDF |
| `app/Traits/PreventDuplicateBorradorTrait.php` | 82 | Evita duplicar borradores (mismo cliente+fecha) |

### 1.6 Librerias y Servicios

| Ruta | Lineas | Proposito |
|------|--------|-----------|
| `app/Libraries/InspeccionEmailNotifier.php` | 170 | Envio de email con PDF adjunto via SendGrid |
| `app/Services/PtaAuditService.php` | — | Log de auditoria para cambios en PTA |
| `app/Services/PtaTransicionesService.php` | — | Registro de transiciones de estado PTA |

### 1.7 Migraciones SQL

| Ruta | Proposito |
|------|-----------|
| `app/SQL/migrate_acta_visita.php` | Crea 4 tablas + modifica tbl_pendientes |
| `app/SQL/migrate_acta_visita_pta.php` | Crea tabla junction acta-PTA |
| `app/SQL/migrate_acta_firma_remota.php` | Agrega columnas de token firma remota |
| `app/SQL/add_motivo_sin_firma_acta.php` | Agrega columna motivo_sin_firma |
| `app/SQL/seed_ciclos_visita.php` | Datos semilla para ciclos de visita |

### 1.8 Comandos CLI (Cron Jobs)

| Ruta | Comando | Proposito |
|------|---------|-----------|
| `app/Commands/RecordatorioVisitas.php` | `php spark visitas:recordatorio` | Recordatorio 3 dias antes de visita |
| `app/Commands/AuditoriaVisitasCron.php` | `php spark auditoria:revisar-visitas-diario` | Auditoria diaria de cumplimiento |
| `app/Commands/ResumenPendientesInspecciones.php` | `php spark pendientes:resumen-diario` | Resumen diario de pendientes |
| `app/Commands/RegenerarPdfs.php` | `php spark regenerar:pdfs --modulo=acta-visita` | Regeneracion masiva de PDFs |

### 1.9 Configuracion

| Ruta | Lineas relevantes | Proposito |
|------|-------------------|-----------|
| `app/Config/Routes.php` | 909-927, 1513-1518 | 22 rutas del modulo |

---

## 2. RUTAS DEL APLICATIVO

### 2.1 Vistas (GET)

| URL | Metodo HTTP | Controlador::Metodo | Descripcion |
|-----|-------------|---------------------|-------------|
| `/inspecciones/acta-visita` | GET | `ActaVisitaController::list` | Listado de todas las actas |
| `/inspecciones/acta-visita/create` | GET | `ActaVisitaController::create` | Formulario nueva acta |
| `/inspecciones/acta-visita/create/{idCliente}` | GET | `ActaVisitaController::create/$1` | Formulario con cliente preseleccionado |
| `/inspecciones/acta-visita/edit/{id}` | GET | `ActaVisitaController::edit/$1` | Editar borrador/pendiente_firma |
| `/inspecciones/acta-visita/view/{id}` | GET | `ActaVisitaController::view/$1` | Vista solo-lectura |
| `/inspecciones/acta-visita/pta/{id}` | GET | `ActaVisitaController::pta/$1` | Vista intermedia PTA |
| `/inspecciones/acta-visita/firma/{id}` | GET | `ActaVisitaController::firma/$1` | Pantalla de firmas (multi-paso) |
| `/inspecciones/acta-visita/delete/{id}` | GET | `ActaVisitaController::delete/$1` | Eliminar borrador |

### 2.2 Acciones POST (Formularios y AJAX)

| URL | Metodo HTTP | Controlador::Metodo | Tipo | Descripcion |
|-----|-------------|---------------------|------|-------------|
| `/inspecciones/acta-visita/store` | POST | `ActaVisitaController::store` | Form/AJAX | Guardar nueva acta (siempre borrador) |
| `/inspecciones/acta-visita/update/{id}` | POST | `ActaVisitaController::update/$1` | Form/AJAX | Actualizar acta existente |
| `/inspecciones/acta-visita/save-pta/{id}` | POST | `ActaVisitaController::savePta/$1` | Form | Guardar PTA y redirigir a firmas |
| `/inspecciones/acta-visita/save-firma/{id}` | POST | `ActaVisitaController::saveFirma/$1` | AJAX | Guardar firma individual (base64 PNG) |
| `/inspecciones/acta-visita/finalizar/{id}` | POST | `ActaVisitaController::finalizar/$1` | AJAX | Finalizar: generar PDF + email + reportes |
| `/inspecciones/acta-visita/finalizar-sin-firma/{id}` | POST | `ActaVisitaController::finalizarSinFirma/$1` | AJAX | Finalizar sin firma cliente (con motivo) |
| `/inspecciones/acta-visita/generar-token-firma/{id}` | POST | `ActaVisitaController::generarTokenFirma/$1` | AJAX | Generar token 24h para firma remota |
| `/acta-visita/procesar-firma-remota` | POST | `ActaVisitaController::procesarFirmaRemota` | AJAX publico | Procesar firma remota (sin auth) |
| `/acta-visita/evaluaciones-visita/update` | POST | `ActaVisitaController::updateEvaluacionPublica` | AJAX publico | Marcar evaluacion como cumple |

### 2.3 API/AJAX (GET)

| URL | Controlador::Metodo | Parametros | Respuesta |
|-----|---------------------|------------|-----------|
| `/inspecciones/acta-visita/api/pta-actividades` | `getPtaActividades` | `?id_cliente=X&fecha_visita=Y&id_acta=Z` | `{actividades:[], prevLinks:{}}` |

### 2.4 Exportaciones (PDF)

| URL | Metodo HTTP | Controlador::Metodo | Descripcion |
|-----|-------------|---------------------|-------------|
| `/inspecciones/acta-visita/pdf/{id}` | GET | `ActaVisitaController::generatePdf/$1` | Ver/descargar PDF (regenera siempre) |
| `/inspecciones/acta-visita/regenerar/{id}` | GET | `ActaVisitaController::regenerarPdf/$1` | Regenerar PDF de acta completa |

### 2.5 Email

| URL | Metodo HTTP | Controlador::Metodo | Descripcion |
|-----|-------------|---------------------|-------------|
| `/inspecciones/acta-visita/enviar-email/{id}` | GET | `ActaVisitaController::enviarEmail/$1` | Reenviar email con PDF adjunto |

### 2.6 Rutas Publicas (sin autenticacion)

| URL | Metodo HTTP | Controlador::Metodo | Descripcion |
|-----|-------------|---------------------|-------------|
| `/acta-visita/firmar-remoto/{token}` | GET | `ActaVisitaController::firmarRemoto/$1` | Pagina publica firma remota |
| `/acta-visita/procesar-firma-remota` | POST | `ActaVisitaController::procesarFirmaRemota` | Procesar firma remota |
| `/acta-visita/evaluaciones-visita/{actaId}/{token}` | GET | `ActaVisitaController::evaluacionesVisita/$1/$2` | Formulario evaluacion publica |
| `/acta-visita/evaluaciones-visita/update` | POST | `ActaVisitaController::updateEvaluacionPublica` | Actualizar evaluacion publica |

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tablas PROPIAS del modulo

#### tbl_acta_visita (Tabla maestra)

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

    -- Proxima reunion
    `proxima_reunion_fecha` DATE NULL,
    `proxima_reunion_hora` TIME NULL,

    -- Firmas (rutas a imagenes PNG)
    `firma_administrador` VARCHAR(255) NULL,
    `firma_vigia` VARCHAR(255) NULL,
    `firma_consultor` VARCHAR(255) NULL,
    `motivo_sin_firma` VARCHAR(255) NULL,

    -- Tokens firma remota (WhatsApp)
    `token_firma_remota` VARCHAR(64) NULL,
    `token_firma_tipo` VARCHAR(20) NULL,
    `token_firma_expiracion` DATETIME NULL,

    -- Soportes documentales
    `soporte_lavado_tanques` VARCHAR(255) NULL,
    `soporte_plagas` VARCHAR(255) NULL,

    -- PDF generado
    `ruta_pdf` VARCHAR(255) NULL,

    -- Estado y tracking
    `estado` ENUM('borrador', 'pendiente_firma', 'completo') NOT NULL DEFAULT 'borrador',
    `agenda_id` VARCHAR(50) NULL COMMENT 'Vinculo opcional con agenda',
    `pta_confirmado` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign keys
    CONSTRAINT `fk_acta_visita_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_acta_visita_consultor`
        FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Indices
    INDEX `idx_acta_cliente` (`id_cliente`),
    INDEX `idx_acta_consultor` (`id_consultor`),
    INDEX `idx_acta_fecha` (`fecha_visita`),
    INDEX `idx_acta_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### tbl_acta_visita_integrantes (Participantes)

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_visita_integrantes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_acta_visita` INT NOT NULL,
    `nombre` VARCHAR(200) NOT NULL,
    `rol` VARCHAR(100) NOT NULL COMMENT 'ADMINISTRADOR, CONSULTOR CYCLOID, VIGIA SST, etc.',
    `orden` TINYINT NOT NULL DEFAULT 1 COMMENT 'Orden de aparicion en el acta',

    CONSTRAINT `fk_integrante_acta`
        FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_integrante_acta` (`id_acta_visita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### tbl_acta_visita_temas (Temas tratados)

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

#### tbl_acta_visita_fotos (Registro fotografico)

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

#### tbl_acta_visita_pta (Junction: Acta <-> PTA)

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_visita_pta` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_acta_visita` INT NOT NULL,
    `id_ptacliente` INT NOT NULL,
    `cerrada` TINYINT(1) NOT NULL DEFAULT 0,
    `justificacion_no_cierre` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_ptacliente`) REFERENCES `tbl_pta_cliente`(`id_ptacliente`) ON DELETE CASCADE,
    UNIQUE KEY `uk_acta_pta` (`id_acta_visita`, `id_ptacliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### ALTER TABLE tbl_pendientes (Modificacion a tabla existente)

```sql
ALTER TABLE `tbl_pendientes`
    ADD COLUMN `id_acta_visita` INT NULL DEFAULT NULL
        COMMENT 'FK al acta de visita que genero este pendiente (nullable)';

ALTER TABLE `tbl_pendientes`
    ADD INDEX `idx_pendiente_acta` (`id_acta_visita`);

ALTER TABLE `tbl_pendientes`
    ADD CONSTRAINT `fk_pendiente_acta_visita`
        FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE;
```

### 3.2 Tablas del SISTEMA que el modulo consulta

| Tabla | PK | Campos usados | Relacion |
|-------|-----|---------------|----------|
| `tbl_clientes` | `id_cliente` | nombre_cliente, correo_cliente, correo_consejo_admon, logo, estandares, nit | FK desde tbl_acta_visita.id_cliente |
| `tbl_consultor` | `id_consultor` | nombre_consultor, firma_consultor, correo_consultor | FK desde tbl_acta_visita.id_consultor |
| `tbl_pendientes` | `id_pendientes` | tarea_actividad, fecha_cierre, responsable, estado, id_cliente, id_acta, id_acta_visita | FK bidireccional |
| `tbl_pta_cliente` | `id_ptacliente` | actividad_plandetrabajo, numeral_plandetrabajo, fecha_propuesta, estado_actividad, porcentaje_avance, observaciones | FK desde tbl_acta_visita_pta |
| `tbl_vencimientos_mantenimientos` | `id_vencimientos_mmttos` | fecha_vencimiento, estado_actividad | Consulta directa por id_cliente |
| `tbl_mantenimientos` | `id_mantenimiento` | descripcion_mantenimiento | JOIN con vencimientos |
| `tbl_reporte` | — | titulo_reporte, id_report_type, id_detailreport, estado, observaciones | INSERT al finalizar |
| `tbl_ciclos_visita` | `id` | id_cliente, mes_esperado, anio, fecha_acta, id_acta, estatus_agenda, estatus_mes | UPDATE al finalizar |
| `tbl_agendamientos` | `id` | fecha_visita, hora_visita, estado, id_consultor | Consulta para vincular agenda |
| `tbl_contratos` | `id_contrato` | id_cliente, estado, fecha_fin | Verificar contrato activo |

### 3.3 Diagrama de relaciones

```
tbl_clientes ──(1:N)──> tbl_acta_visita ──(1:N)──> tbl_acta_visita_integrantes
     │                       │
     │                       ├──(1:N)──> tbl_acta_visita_temas
     │                       │
     │                       ├──(1:N)──> tbl_acta_visita_fotos
     │                       │
     │                       ├──(1:N)──> tbl_acta_visita_pta ──(N:1)──> tbl_pta_cliente
     │                       │
     │                       └──(1:N)──> tbl_pendientes (via id_acta_visita)
     │
tbl_consultor ──(1:N)──> tbl_acta_visita

tbl_ciclos_visita ──(1:1 opcional)──> tbl_acta_visita (via id_acta)
tbl_reporte ──(recibe INSERT)──> al finalizar acta
```

### 3.4 Constraints clave

| Constraint | Tipo | Comportamiento |
|-----------|------|----------------|
| FK id_cliente | RESTRICT DELETE | No se puede borrar cliente con actas existentes |
| FK id_consultor | RESTRICT DELETE | No se puede borrar consultor con actas existentes |
| FK tablas hijas | CASCADE DELETE | Borrar acta elimina integrantes, temas, fotos, pta_links |
| FK tbl_pendientes.id_acta_visita | SET NULL | Borrar acta deja pendiente vivo (solo pierde referencia) |
| UNIQUE (id_acta_visita, id_ptacliente) | UNIQUE | Impide duplicar misma actividad PTA en misma acta |

---

## 4. FLUJO FUNCIONAL

### 4.1 Diagrama de estados

```
                    ┌─────────────────┐
                    │    BORRADOR     │ ◄── store() crea aquí siempre
                    │  (editable N    │
                    │   veces)        │
                    └────────┬────────┘
                             │ click "Ir a firmas"
                             │ (valida: cliente, fecha, motivo, 1+ integrante, 1+ tema)
                             ▼
                    ┌─────────────────┐
                    │  PENDIENTE_FIRMA │
                    │  (vista PTA →   │
                    │   canvas firma) │
                    └────────┬────────┘
                             │ todas las firmas recolectadas
                             │ finalizar() o finalizarSinFirma()
                             ▼
                    ┌─────────────────┐
                    │    COMPLETO     │ → PDF generado
                    │  (solo lectura, │ → Email enviado
                    │   PDF, email)   │ → Reporte registrado
                    └─────────────────┘ → Ciclo visita actualizado
```

### 4.2 Metodos del controlador

| Metodo | Linea | Proposito |
|--------|-------|-----------|
| `list()` | 44 | Lista actas con JOIN a clientes y consultores, ordena por fecha DESC |
| `create($idCliente?)` | 66 | Renderiza formulario vacio, pre-selecciona cliente si se pasa ID |
| `store()` | 86 | Valida, crea borrador, guarda integrantes/temas/compromisos/fotos/PTA. Soporta autosave |
| `edit($id)` | 156 | Carga acta con todas sus relaciones y renderiza formulario |
| `update($id)` | 182 | Igual que store() pero actualiza. Soporta autosave |
| `view($id)` | 231 | Vista completa solo-lectura con pendientes abiertos y mantenimientos |
| `pta($id)` | 274 | Carga actividades PTA del mes del cliente. Si ya confirmado, redirige a firma |
| `savePta($id)` | 341 | Guarda cierre/justificacion de actividades PTA, marca pta_confirmado=1 |
| `firma($id)` | 359 | Cambia estado a pendiente_firma, determina firmantes segun roles de integrantes |
| `saveFirma($id)` | 402 | AJAX: decodifica base64, guarda PNG, actualiza campo firma_{tipo} |
| `finalizar($id)` | 441 | Valida firmas, genera PDF, estado=completo, sube a reportes, email, ciclo visita |
| `finalizarSinFirma($id)` | 510 | Igual que finalizar pero guarda motivo_sin_firma |
| `generatePdf($id)` | 560 | Regenera PDF y lo sirve al navegador |
| `regenerarPdf($id)` | 824 | Regenera PDF de acta completa y re-sube a reportes |
| `delete($id)` | 582 | Solo borradores: elimina fotos de disco, firmas de disco, registro BD (CASCADE) |
| `getPtaActividades()` | 613 | API AJAX: actividades PTA abiertas para cliente/mes, con estados previos |
| `generarTokenFirma($id)` | 673 | Genera token hex 64 chars, expiracion 24h, retorna URL compartible |
| `firmarRemoto($token)` | 701 | Pagina publica: valida token, muestra resumen acta + canvas firma |
| `procesarFirmaRemota()` | 782 | AJAX publico: valida token, guarda firma, limpia token |
| `enviarEmail($id)` | 1156 | Reenvia email con PDF adjunto via InspeccionEmailNotifier |
| `evaluacionesVisita($actaId, $token)` | 1285 | Pagina publica: evaluacion rapida post-visita |
| `updateEvaluacionPublica()` | 1326 | AJAX publico: marca evaluacion como CUMPLE TOTALMENTE |

### 4.3 Metodos privados auxiliares

| Metodo | Linea | Proposito |
|--------|-------|-----------|
| `saveIntegrantes($idActa)` | 843 | Extrae nombres/roles del POST, reemplaza atomicamente via replaceForActa() |
| `saveTemas($idActa)` | 864 | Extrae temas del POST, filtra vacios, reemplaza atomicamente |
| `saveCompromisos($idActa)` | 875 | Borra pendientes anteriores del acta, crea nuevos con estado ABIERTA |
| `saveFotos($idActa)` | 905 | Procesa uploads, mueve a uploads/inspecciones/fotos/, crea registros BD |
| `savePtaActividades($idActa)` | 936 | Inserta/actualiza links PTA, cierra actividades marcadas, auditoria |
| `generarPdfInterno($id)` | 1007 | Recopila TODOS los datos, genera PDF via DOMPDF, retorna ruta relativa |
| `uploadToReportes($acta, $pdfPath)` | 1179 | Copia PDF a uploads/{nit}/, crea registro tbl_reporte (type=6, detail=9) |
| `actualizarCicloVisita($acta)` | 1231 | Busca ciclo del mes, actualiza fecha_acta/estatus, auto-genera siguiente |
| `generarTokenEvaluacion($actaId, $clienteId)` | 1277 | SHA256 hash de actaId+clienteId+salt, primeros 24 chars |
| `enviarEmailEvaluacionesRapidas($acta)` | 1364 | Email al consultor con link evaluaciones via SendGrid API |

### 4.4 Flujos AJAX detallados

#### Autosave (form.php)
```
Frontend (cada 60s o 5s despues de cambio):
  POST /inspecciones/acta-visita/store  (o /update/{id})
  Headers: X-Autosave: 1, X-Requested-With: XMLHttpRequest
  Body: FormData (solo campos dirty + archivos con data-dirty="1")

Backend detecta isAutosaveRequest():
  → Exito: autosaveJsonSuccess($id) → {success:true, id:123, saved_at:"..."}
  → Error: autosaveJsonError($msg)  → {success:false, message:"..."}

Frontend transiciona de create→edit:
  → Actualiza URL, storageKey, recordId sin recargar pagina
```

#### Guardar firma (firma.php)
```
Frontend captura canvas como PNG base64:
  POST /inspecciones/acta-visita/save-firma/{id}
  Body: FormData { tipo: "administrador"|"vigia"|"consultor", firma_imagen: "data:image/png;base64,..." }

Backend:
  → Decodifica base64
  → Guarda: uploads/inspecciones/firmas/firma_{tipo}_{id}_{timestamp}.png
  → Actualiza: tbl_acta_visita.firma_{tipo} = ruta_relativa
  → Responde: {success:true, campo:"firma_{tipo}"}
```

#### Finalizar (firma.php)
```
Frontend:
  POST /inspecciones/acta-visita/finalizar/{id}
  Headers: X-Requested-With: XMLHttpRequest
  Body: JSON {}

Backend:
  1. Valida firma_consultor obligatoria
  2. Valida firma_administrador si hay integrante ADMINISTRADOR
  3. generarPdfInterno() → PDF en disco
  4. estado = "completo"
  5. uploadToReportes() → copia a uploads/{nit}/ + registro tbl_reporte
  6. enviarEmail() → InspeccionEmailNotifier con PDF adjunto
  7. actualizarCicloVisita() → tbl_ciclos_visita
  8. enviarEmailEvaluacionesRapidas() → email al consultor
  → Responde: {success:true, pdf_url:"/inspecciones/acta-visita/pdf/{id}"}
```

#### Firma remota (WhatsApp)
```
1. Consultor genera token:
   POST /inspecciones/acta-visita/generar-token-firma/{id}
   Body: { tipo: "administrador" }
   → {success:true, url:"https://dominio/acta-visita/firmar-remoto/{token64chars}"}
   → Consultor comparte via WhatsApp

2. Cliente abre link:
   GET /acta-visita/firmar-remoto/{token}
   → Valida token + expiracion (24h) + no firmado previamente
   → Renderiza vista con resumen del acta + canvas

3. Cliente firma:
   POST /acta-visita/procesar-firma-remota
   Body: FormData { token, firma_imagen }
   → Valida token, guarda PNG, actualiza BD, limpia token
   → {success:true}
   → Si offline: OfflineQueue.add() → IndexedDB → sync automatico al reconectar
```

#### Cargar actividades PTA (form.php)
```
GET /inspecciones/acta-visita/api/pta-actividades?id_cliente=5&fecha_visita=2026-04-01&id_acta=123

Respuesta:
{
  actividades: [
    {id_ptacliente:1, actividad:"...", numeral:"1.1", fecha_propuesta:"2026-04-15", estado:"ABIERTA"},
    ...
  ],
  prevLinks: {
    "1": {cerrada:true, justificacion:""},
    "5": {cerrada:false, justificacion:"Pendiente por proveedor"}
  }
}
```

---

## 5. DEPENDENCIAS EXTERNAS

### 5.1 Librerias PHP (Composer)

| Libreria | Version | Uso en el modulo |
|----------|---------|------------------|
| `dompdf/dompdf` | 3.0.0 | Generacion de PDF del acta |
| CodeIgniter 4 Framework | 4.x | Framework base |

### 5.2 CDN Frontend

| Libreria | Version | CDN URL |
|----------|---------|---------|
| Bootstrap | 5.3.0 | `https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css` |
| Bootstrap JS | 5.3.0 | `https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js` |
| SweetAlert2 | v11 (latest) | `https://cdn.jsdelivr.net/npm/sweetalert2@11` |
| Font Awesome | 6.4.0 | `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css` |
| jQuery | 3.5.1+ | `https://code.jquery.com/jquery-3.5.1.min.js` |
| DataTables | 2.1.8 / 1.10.24 | `https://cdn.datatables.net/` |
| DataTables Buttons | 1.7.0 | `https://cdn.datatables.net/buttons/1.7.0/` |
| JSZip | 3.10.1 | `https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js` |
| Select2 | — | Usado via jQuery para selector de clientes |

### 5.3 Assets locales propios

| Archivo | Uso |
|---------|-----|
| `public/js/autosave_server.js` | Motor autosave para formularios |
| `public/js/offline_queue.js` | Cola IndexedDB para firmas offline |

### 5.4 API Externas

| Servicio | Uso | Configuracion |
|----------|-----|---------------|
| SendGrid v3 | Envio de emails con PDF adjunto | `SENDGRID_API_KEY` (env variable) |
| Geolocation API | Captura GPS automatica al crear acta | Navigator.geolocation (browser) |

---

## 6. PATRONES ESPECIALES

### 6.1 Sistema de firmas

**Firma presencial (canvas HTML5):**
- Clase `SignatureCanvas` en firma.php con soporte mouse + touch
- Filtro multi-touch: `if (e.touches.length > 1) return;` (evita trazos por pinch-zoom)
- Validacion minima pixeles oscuros (>100) para rechazar firmas accidentales
- Preview via SweetAlert2 antes de confirmar
- Almacenamiento: PNG en `uploads/inspecciones/firmas/firma_{tipo}_{id}_{timestamp}.png`

**Firma remota (via WhatsApp):**
- Token hex 64 caracteres, expiracion 24 horas
- URL publica sin autenticacion
- Soporte offline via IndexedDB (OfflineQueue)
- Background Sync API + listener 'online' para sincronizacion automatica

**Firmantes determinados dinamicamente:**
- Se leen los roles de los integrantes del acta
- "ADMINISTRA*" → firma_administrador
- "VIG*" → firma_vigia
- "CONSULTOR*" → firma_consultor

### 6.2 Autosave

**Configuracion del motor:**
```javascript
initAutosave({
    formId: 'actaForm',
    storeUrl: '/inspecciones/acta-visita/store',
    updateUrlBase: '/inspecciones/acta-visita/update/',
    editUrlBase: '/inspecciones/acta-visita/edit/',
    recordId: null | existingId,
    isEdit: true | false,
    storageKey: 'acta_draft_new' | 'acta_draft_{id}',
    intervalSeconds: 60,
    minFieldsCheck: function() { /* validacion custom */ }
})
```

**Comportamiento:**
- Intervalo: cada 60 segundos
- Debounce: 5 segundos despues de input, 3 segundos despues de Select2 change
- Header: `X-Autosave: 1` + `X-Requested-With: XMLHttpRequest`
- Solo envia archivos con `data-dirty="1"`
- Transicion automatica create→edit (actualiza URL sin recargar)
- Override de `form.submit()` para setear flag `submitted=true`
- `clearInterval(intervalId)` en submit para evitar race condition

### 6.3 Prevencion de borradores duplicados

**Trait:** `PreventDuplicateBorradorTrait::reuseExistingBorrador()`
- Busca borrador existente para mismo cliente + fecha + consultor
- Si existe en autosave: responde JSON con id existente
- Si existe en submit normal: redirige al edit del borrador existente

### 6.4 Generacion PDF (DOMPDF)

**Datos recopilados para el PDF:**
- Acta base + cliente + consultor
- Integrantes con roles
- Temas tratados
- Compromisos (pendientes vinculados)
- Fotos comprimidas como base64 (800px max, 55% JPEG)
- Firmas como base64 PNG
- Logo del cliente como base64
- Actividades PTA cerradas en la visita
- Pendientes cerrados en fecha de visita
- Mantenimientos ejecutados en fecha de visita
- Pendientes abiertos del cliente
- Vencimientos de mantenimiento (30 dias)

**Configuracion DOMPDF:**
```php
$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true);
$dompdf->set_option('isHtml5ParserEnabled', true);
$dompdf->setPaper('letter', 'portrait');
// IMPORTANTE: @page margin en px, NO en cm (bug DOMPDF 3.0.0)
```

**Almacenamiento:**
- Ruta: `uploads/inspecciones/pdfs/acta_visita_{id}_{YYYYMMDD_HHMMSS}.pdf`
- Copia a: `uploads/{nit_cliente}/` (para reportes)
- Se borra PDF anterior si existe antes de generar nuevo

### 6.5 Registro en reportes

```php
// uploadToReportes() — tbl_reporte
$data = [
    'titulo_reporte' => "ACTA DE VISITA - {$cliente['nombre_cliente']} - {$fechaFormateada}",
    'id_report_type' => 6,        // Tipo: Inspecciones
    'id_detailreport' => 9,       // Detalle: Acta de Visita
    'estado' => 'CERRADO',
    'id_cliente' => $acta['id_cliente'],
    'observaciones' => "Generado automaticamente desde acta #{$acta['id']}"
];
```

### 6.6 Ciclo de visitas

**Al finalizar un acta:**
1. Busca ciclo para cliente/mes/anio
2. Compara fecha_agendada vs fecha_visita → cumple/incumple
3. Actualiza: fecha_acta, id_acta, estatus_agenda, estatus_mes
4. Auto-genera siguiente ciclo si estandar del cliente lo requiere

### 6.7 Envio de emails

**Email al finalizar (InspeccionEmailNotifier):**
- Destinatarios: correo_cliente + correo_consultor + correo externo (si existe)
- Adjunto: PDF del acta
- Template: HTML con branding corporativo (color primario: #bd9751)
- Servicio: SendGrid v3 API via cURL directo

**Email evaluaciones rapidas:**
- Destinatario: consultor(es) asignados
- Contiene link con token SHA256 para acceso publico
- Token: `substr(hash('sha256', "{$actaId}|{$clienteId}|evvisita2026"), 0, 24)`

### 6.8 Evaluaciones rapidas post-visita

- Pagina publica accesible por token (sin login)
- Muestra items de evaluacion con estado != "CUMPLE TOTALMENTE"
- Checkbox marca automaticamente como cumple via AJAX
- Agrupadas por estandar y numeral

### 6.9 Soporte offline (PWA)

**IndexedDB (offline_queue.js):**
```javascript
// Base de datos
DB_NAME: 'inspecciones_offline'
Store: 'pending_signatures'

// API
OfflineQueue.add({type, url, payload, id_asistencia, meta})
OfflineQueue.syncAll()      // Retorna {synced, failed, errors}
OfflineQueue.requestSync()  // Background Sync API
OfflineQueue.startOnlineListener(callback) // Auto-sync al reconectar
```

### 6.10 Seguridad

- CSRF: `<?= csrf_field() ?>` en todos los formularios
- Verificacion matematica (SweetAlert): puzzle aritmetico para acciones destructivas
- Tokens con expiracion: firma remota (24h), evaluaciones (SHA256 hash)
- Escape de salida: `esc()` en todas las vistas
- Validacion server-side: id_cliente required|integer, fecha required|valid_date, etc.

---

## 7. ORDEN DE IMPLEMENTACION

### Paso 1: Base de datos

```
1.1 Ejecutar migrate_acta_visita.php         → 4 tablas + ALTER pendientes
1.2 Ejecutar migrate_acta_visita_pta.php     → tabla junction PTA
1.3 Ejecutar migrate_acta_firma_remota.php   → columnas token firma remota
1.4 Ejecutar add_motivo_sin_firma_acta.php   → columna motivo_sin_firma
1.5 Ejecutar seed_ciclos_visita.php          → datos semilla ciclos (opcional)
```

**Prerequisitos BD:** Las siguientes tablas deben existir previamente:
- `tbl_clientes` (con id_cliente, nombre_cliente, logo, estandares, correo_cliente, nit)
- `tbl_consultor` (con id_consultor, nombre_consultor, correo_consultor, firma_consultor)
- `tbl_pendientes` (tabla existente del sistema)
- `tbl_pta_cliente` (con id_ptacliente, actividad_plandetrabajo, estado_actividad)
- `tbl_reporte` (para registro de PDFs)
- `tbl_ciclos_visita` (para tracking de visitas)
- `tbl_vencimientos_mantenimientos` + `tbl_mantenimientos` (para consulta de vencimientos)

### Paso 2: Traits

```
2.1 app/Traits/AutosaveJsonTrait.php             → Deteccion autosave + respuesta JSON
2.2 app/Traits/ImagenCompresionTrait.php          → Compresion EXIF + base64 + servirPdf
2.3 app/Traits/PreventDuplicateBorradorTrait.php  → Anti-duplicado de borradores
```

### Paso 3: Modelos

```
3.1 app/Models/ActaVisitaModel.php                → Modelo maestro
3.2 app/Models/ActaVisitaIntegranteModel.php      → Participantes (con replaceForActa)
3.3 app/Models/ActaVisitaTemaModel.php             → Temas (con replaceForActa)
3.4 app/Models/ActaVisitaFotoModel.php             → Fotos
3.5 app/Models/ActaVisitaPtaModel.php              → Junction PTA
```

### Paso 4: Servicios y librerias

```
4.1 app/Libraries/InspeccionEmailNotifier.php      → Envio email SendGrid
4.2 app/Services/PtaAuditService.php               → Auditoria PTA
4.3 app/Services/PtaTransicionesService.php        → Transiciones PTA
```

### Paso 5: Rutas

```
5.1 Agregar 22 rutas en app/Config/Routes.php
    - Rutas autenticadas bajo grupo 'inspecciones'
    - Rutas publicas fuera del grupo (firmar-remoto, procesar-firma-remota, evaluaciones)
```

### Paso 6: Controlador

```
6.1 app/Controllers/Inspecciones/ActaVisitaController.php (1,438 lineas)
    - Constructor: instancia 3 modelos base
    - 15 metodos publicos + 9 metodos privados
    - Usa 3 traits
```

### Paso 7: Vistas

```
7.1  app/Views/inspecciones/acta_visita/form.php                → Formulario principal
7.2  app/Views/inspecciones/acta_visita/list.php                → Listado DataTables
7.3  app/Views/inspecciones/acta_visita/view.php                → Vista solo-lectura
7.4  app/Views/inspecciones/acta_visita/pdf.php                 → Template PDF (DOMPDF)
7.5  app/Views/inspecciones/acta_visita/firma.php               → Captura firmas canvas
7.6  app/Views/inspecciones/acta_visita/firma_remota.php        → Firma remota publica
7.7  app/Views/inspecciones/acta_visita/firma_remota_error.php  → Error token
7.8  app/Views/inspecciones/acta_visita/pta.php                 → Vista PTA intermedia
7.9  app/Views/inspecciones/acta_visita/evaluaciones_visita.php → Evaluacion publica
7.10 app/Views/inspecciones/acta_visita/evaluaciones_visita_error.php → Error evaluacion
7.11 app/Views/client/inspecciones/acta_visita_view.php         → Vista portal cliente
```

### Paso 8: JavaScript

```
8.1 public/js/autosave_server.js   → Motor autosave (262 lineas)
8.2 public/js/offline_queue.js     → Cola offline IndexedDB (197 lineas)
```

### Paso 9: Directorios de uploads

```
Crear directorios con permisos 775:
9.1 uploads/inspecciones/fotos/
9.2 uploads/inspecciones/firmas/
9.3 uploads/inspecciones/pdfs/
```

### Paso 10: Integraciones

```
10.1 Dashboard inspecciones: agregar card/link a acta-visita
10.2 Portal cliente: agregar vista acta_visita_view
10.3 Comandos CLI (opcionales): RecordatorioVisitas, AuditoriaVisitasCron, RegenerarPdfs
10.4 Configurar SENDGRID_API_KEY en .env
```

### Paso 11: Verificacion

```
11.1 Crear acta borrador → verificar autosave funciona
11.2 Agregar integrantes, temas, compromisos, fotos
11.3 Ir a PTA → marcar actividades como cerradas
11.4 Firmar con canvas → verificar PNG guardado
11.5 Generar token firma remota → abrir link en otro dispositivo
11.6 Finalizar → verificar PDF generado, email enviado, reporte creado
11.7 Probar offline: firmar sin conexion → reconectar → verificar sync
```

---

## 8. ESQUEMA DE COLORES Y UI

| Elemento | Color | Uso |
|----------|-------|-----|
| Primario/Dorado | `#bd9751` | Botones, headers, branding |
| Fondo oscuro | `#1c2437`, `#2c3e50` | Backgrounds, sidebars |
| Exito | `#28a745` | Badges "completo", botones confirmar |
| Peligro | `#dc3545` | Badges "borrador", botones eliminar |
| Advertencia | `#ffc107` | Badges "pendiente_firma" |
| Gradiente firma remota | `#bd9751 → #8b6914` | Background paginas publicas |

---

## 9. VARIABLES DE DATOS POR VISTA

### form.php
```php
$acta              // null (create) o array (edit)
$idCliente         // int|null — cliente preseleccionado
$integrantes       // array de {nombre, rol}
$temas             // array de {descripcion}
$compromisos       // array de {tarea_actividad, fecha_cierre, responsable}
$fotos             // array de {id, ruta_archivo, tipo, descripcion}
$isEdit            // bool
```

### list.php
```php
$actas             // array de actas con campos: id, nombre_cliente, fecha_visita, motivo, estado
```

### view.php
```php
$acta, $cliente, $integrantes, $temas, $compromisos, $fotos
$ptaActividades    // actividades PTA vinculadas
$pendientesCerradosEnVisita  // pendientes cerrados en fecha de visita
$mantenimientosEnVisita      // mantenimientos ejecutados en fecha de visita
```

### firma.php
```php
$acta              // datos del acta
$firmantes         // array de {rol_label, nombre, tipo, firmado}
```

### firma_remota.php
```php
$token, $tipo, $nombreFirmante, $cliente, $acta
$integrantes, $temas, $compromisos
$mantenimientos, $pendientesAbiertos
```

### pta.php
```php
$acta, $cliente, $actividades, $prevLinks
```

### evaluaciones_visita.php
```php
$acta, $cliente, $evaluaciones, $token
```

### pdf.php
```php
$acta, $cliente, $consultor, $integrantes, $temas
$compromisos, $fotos (base64), $firmas (base64)
$ptaCerradas, $pendientesAbiertos, $mantenimientosProximos
$pendientesCerradosEnVisita, $mantenimientosEnVisita
$logoBase64
```

---

> **Nota:** Este documento fue generado por analisis estatico del codigo fuente.
> Todas las rutas son relativas a la raiz del proyecto (`c:\xampp\htdocs\enterprisesstph\`).
> Para replicar, asegurar que las tablas del sistema (tbl_clientes, tbl_consultor, etc.) existan con los campos referenciados.
