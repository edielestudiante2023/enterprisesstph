# Modulo Agendamiento de Visitas

## Contexto

Reemplazo del sistema AppSheet + Google Apps Script que usaba Google Sheets como base de datos y `CalendarApp.createEvent()` para crear eventos en Google Calendar. Ahora todo vive nativamente en la app CI4.

**Solucion para calendario:** Archivos `.ics` (iCalendar RFC 5545) enviados como attachment via SendGrid. Al abrir el `.ics`, se agrega automaticamente a Google Calendar/Outlook/cualquier cliente.

---

## Arquitectura: Dos Mundos

### 1. PWA Consultor (`/inspecciones/agendamiento/`)

El consultor usa esto cara a cara con el cliente o cuando lo llama para agendar. Vive dentro del modulo de inspecciones con layout mobile-first (`layout_pwa.php`).

**Flujo:**
1. Consultor entra a Agendamientos desde el dashboard de inspecciones
2. Ve lista de sus agendamientos con filtros por cliente y estado
3. Click "Nuevo" → selecciona cliente → AJAX carga ultima visita y fecha sugerida
4. Llena fecha, hora, frecuencia, observaciones
5. Guarda con checkbox "Enviar invitacion" → se envia email con .ics

### 2. Panel Admin (`/admin/agendamientos/`)

El admin/supervisor controla que los consultores esten agendando con sus clientes. Vista standalone con estilo admin (DataTables, cards resumen).

**Flujo:**
1. Admin entra desde boton "Panel de Agendamientos" en dashboard admin o consultor
2. Ve cards por consultor: total clientes activos, agendados, sin agendar, % cumplimiento
3. Click en card → drill-down con detalle de cada cliente del consultor
4. Clientes sin agendar resaltados en rojo

---

## Base de Datos

### Tabla: `tbl_agendamientos`

```sql
CREATE TABLE tbl_agendamientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_visita DATE NOT NULL,
    hora_visita TIME NOT NULL,
    frecuencia ENUM('mensual','bimensual','trimestral') NOT NULL DEFAULT 'mensual',
    estado ENUM('pendiente','confirmado','completado','cancelado') NOT NULL DEFAULT 'pendiente',
    confirmacion_calendar VARCHAR(255) NULL,     -- ID evento o texto de confirmacion
    preparacion_cliente TEXT NULL,                -- Notas de preparacion del cliente
    observaciones TEXT NULL,
    email_enviado TINYINT(1) NOT NULL DEFAULT 0,
    fecha_email_enviado DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_cliente (id_cliente),
    INDEX idx_consultor (id_consultor),
    INDEX idx_fecha (fecha_visita),
    INDEX idx_estado (estado)
);
```

**Migracion:** `app/SQL/migrate_agendamientos.php` — ya ejecutada en LOCAL y PRODUCCION.

### Relaciones

- `id_cliente` → `tbl_clientes.id_cliente`
- `id_consultor` → `tbl_consultor.id_consultor`
- Ultima visita se calcula desde `tbl_acta_visita` (WHERE estado='completo', ORDER BY fecha_visita DESC)

### Nota sobre tbl_acta_visita

Los campos `proxima_reunion_fecha` y `proxima_reunion_hora` existen en `tbl_acta_visita` pero NO se usan actualmente. En un futuro podrian vincularse para auto-sugerir el proximo agendamiento cuando se completa un acta.

---

## Archivos del Modulo

### Modelo

| Archivo | Descripcion |
|---------|-------------|
| `app/Models/AgendamientoModel.php` | Modelo CI4 con metodos especializados |

**Metodos clave:**
- `getByConsultor($id)` — Agendamientos del consultor con JOIN a tbl_clientes
- `getAll()` — Todos los agendamientos con JOIN a tbl_clientes + tbl_consultor (admin)
- `getUltimaVisita($idCliente)` — Query a tbl_acta_visita, ultima visita completa
- `sugerirProximaFecha($idCliente, $frecuencia)` — Calcula fecha segun ultima visita + frecuencia
- `getProximosDelMes($idConsultor)` — Agendamientos pendientes/confirmados del mes actual
- `getResumenPorConsultor()` — Cards resumen para panel admin (total activos, agendados, sin agendar, %)
- `getDetalleConsultor($id)` — Clientes de un consultor con estado de agendamiento

### Controladores

| Archivo | Namespace | Descripcion |
|---------|-----------|-------------|
| `app/Controllers/Inspecciones/AgendamientoController.php` | `App\Controllers\Inspecciones` | PWA consultor: CRUD + .ics + SendGrid |
| `app/Controllers/AdminAgendamientoController.php` | `App\Controllers` | Panel admin: resumen + drill-down |

**AgendamientoController — Metodos:**

| Metodo | Tipo | Descripcion |
|--------|------|-------------|
| `list()` | GET | Lista de agendamientos del consultor con filtros |
| `create()` | GET | Formulario nuevo agendamiento |
| `store()` | POST | Guardar + opcionalmente enviar .ics |
| `edit($id)` | GET | Formulario edicion |
| `update($id)` | POST | Actualizar + opcionalmente reenviar .ics |
| `cancel($id)` | POST/AJAX | Cancelar agendamiento + enviar .ics CANCEL |
| `sendInvitation($id)` | POST/AJAX | Enviar/reenviar invitacion .ics |
| `apiClienteInfo($id)` | GET/AJAX | JSON: datos cliente + ultima visita + fecha sugerida |

**AdminAgendamientoController — Metodos:**

| Metodo | Tipo | Descripcion |
|--------|------|-------------|
| `index()` | GET | Panel: cards resumen + tabla detallada |
| `porConsultor($id)` | GET | Drill-down: clientes de un consultor |
| `apiResumen()` | GET/AJAX | JSON: resumen por consultor |

### Vistas

| Archivo | Layout | Descripcion |
|---------|--------|-------------|
| `app/Views/inspecciones/agendamiento/list.php` | `layout_pwa.php` | Lista PWA con filtros y botones AJAX |
| `app/Views/inspecciones/agendamiento/form.php` | `layout_pwa.php` | Formulario create/edit con AJAX info cliente |
| `app/Views/admin/agendamientos/index.php` | Standalone | Panel admin: cards + DataTables |
| `app/Views/admin/agendamientos/por_consultor.php` | Standalone | Drill-down consultor |

### Rutas

**PWA consultor** (dentro del group `inspecciones`, filter `auth`):

```
GET  /inspecciones/agendamiento                         → AgendamientoController::list
GET  /inspecciones/agendamiento/create                  → AgendamientoController::create
POST /inspecciones/agendamiento/store                   → AgendamientoController::store
GET  /inspecciones/agendamiento/edit/{id}               → AgendamientoController::edit
POST /inspecciones/agendamiento/update/{id}             → AgendamientoController::update
POST /inspecciones/agendamiento/cancel/{id}             → AgendamientoController::cancel
POST /inspecciones/agendamiento/send-invitation/{id}    → AgendamientoController::sendInvitation
GET  /inspecciones/agendamiento/api/cliente-info/{id}   → AgendamientoController::apiClienteInfo
```

**Panel admin** (filter `auth`, solo rol admin):

```
GET  /admin/agendamientos                      → AdminAgendamientoController::index
GET  /admin/agendamientos/consultor/{id}       → AdminAgendamientoController::porConsultor
GET  /admin/agendamientos/api/resumen          → AdminAgendamientoController::apiResumen
```

---

## Generacion de Archivo .ics

Metodo privado `generateIcs()` en AgendamientoController. Formato iCalendar RFC 5545:

```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Cycloid Talent//SG-SST//ES
METHOD:REQUEST (o CANCEL para cancelaciones)
BEGIN:VEVENT
UID:agendamiento-{id}@cycloidtalent.com
DTSTART:20260220T100000
DTEND:20260220T110000 (+1 hora)
SUMMARY:Visita SST - {nombre_cliente}
DESCRIPTION:Visita del consultor {nombre_consultor} al cliente {nombre_cliente}
LOCATION:{direccion_cliente}, {ciudad_cliente}
ORGANIZER;CN=Cycloid Talent:mailto:notificacion.cycloidtalent@cycloidtalent.com
ATTENDEE;CN={nombre_consultor};RSVP=TRUE:mailto:{correo_consultor}
ATTENDEE;CN={nombre_cliente};RSVP=TRUE:mailto:{correo_cliente}
STATUS:CONFIRMED (o CANCELLED)
SEQUENCE:0 (1 para cancelaciones)
END:VEVENT
END:VCALENDAR
```

**Cancelaciones:** Mismo UID, METHOD:CANCEL, STATUS:CANCELLED, SEQUENCE:1. Esto hace que los clientes de calendario reconozcan la cancelacion y remuevan el evento.

---

## Envio Email con SendGrid

Patron reutilizado de `InformeAvancesController::enviar()`.

```php
require_once ROOTPATH . 'vendor/autoload.php';
$email = new \SendGrid\Mail\Mail();
$email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
$email->setSubject("Visita SST Agendada - {$nombreCliente} - {$fecha}");
$email->addTo($correoCliente, $nombreCliente);
$email->addTo($correoConsultor, $nombreConsultor);
$email->addContent("text/html", $htmlBody);
$email->addAttachment(base64_encode($icsContent), 'text/calendar; method=REQUEST', 'invitacion.ics', 'attachment');
$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
$response = $sendgrid->send($email);
```

**Destinatarios:** `correo_cliente` (tbl_clientes) + `correo_consultor` (tbl_consultor)

**Email HTML:** Estilo oscuro (#1c2437) con dorado (#bd9751), tabla con datos de la visita, nota para abrir .ics.

---

## Puntos de Acceso (Navegacion)

| Desde | Enlace | Destino |
|-------|--------|---------|
| Dashboard Inspecciones PWA | Card "Agendamientos" (destacada) | `/inspecciones/agendamiento` |
| Dashboard Consultor (`/dashboardconsultant`) | Boton "Panel de Agendamientos" (verde) | `/admin/agendamientos` |
| Dashboard Admin (`/admindashboard`) | Boton "Panel de Agendamientos" (verde) | `/admin/agendamientos` |

---

## Mapeo AppSheet → CI4

| Campo AppSheet | Campo CI4 | Ubicacion |
|---------------|-----------|-----------|
| CREATEDAT | `created_at` | tbl_agendamientos |
| FECHA DE PROXIMA VISITA | `fecha_visita` | tbl_agendamientos |
| HORA DE VISITA | `hora_visita` | tbl_agendamientos |
| CORREO | `correo_cliente` | tbl_clientes (existente) |
| CLIENTE | `nombre_cliente` | tbl_clientes (existente) |
| IDENTIFICACION | `nit_cliente` | tbl_clientes (existente) |
| CONSULTOR | `id_consultor` → `correo_consultor` | tbl_consultor (existente) |
| ESTADO | `estado` | tbl_agendamientos (pendiente/confirmado/completado/cancelado) |
| FRECUENCIA | `frecuencia` | tbl_agendamientos (mensual/bimensual/trimestral) |
| CONFIRMACION | `confirmacion_calendar` | tbl_agendamientos |
| PREPARACION_CLIENTE | `preparacion_cliente` | tbl_agendamientos |
| MES ULTIMA VISITA | Auto-calculado | Query a tbl_acta_visita |

---

## Estados del Agendamiento

```
pendiente → confirmado → completado
    ↓            ↓
 cancelado    cancelado
```

- **pendiente:** Creado pero sin enviar invitacion
- **confirmado:** Invitacion .ics enviada por email
- **completado:** Visita realizada (cambio manual o futuro automatico al crear acta)
- **cancelado:** Visita cancelada, se envia .ics METHOD:CANCEL

---

## Posibles Mejoras Futuras

1. **Auto-completar desde Acta de Visita:** Cuando se completa un acta (`tbl_acta_visita.estado = 'completo'`), marcar automaticamente el agendamiento como `completado` y crear el siguiente segun frecuencia.

2. **Vincular `proxima_reunion_fecha/hora`:** Los campos existentes en tbl_acta_visita podrian auto-crear un agendamiento al finalizar un acta.

3. **Recordatorios automaticos:** Cron job que envie recordatorio 24h antes de la visita via SendGrid.

4. **Vista calendario:** Integrar FullCalendar.js en el panel admin para visualizar todas las visitas en formato calendario.

5. **Agendamiento masivo:** Boton para agendar todos los clientes sin agendar de un consultor de una vez, con fechas auto-calculadas.

6. **Frecuencia en tbl_clientes:** Mover frecuencia a tbl_clientes como campo default del cliente, y que el agendamiento lo herede pero permita override.
