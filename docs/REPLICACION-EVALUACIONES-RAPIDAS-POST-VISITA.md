# Documento de Replicación: Evaluaciones Rápidas Post-Visita

> Generado: 2026-04-11
> Framework: CodeIgniter 4 (PHP 8.2)
> Base de datos: MySQL 8.x
> Email: SendGrid API v3 (cURL directo, sin SDK)

---

## 1. INVENTARIO DE ARCHIVOS

| # | Archivo | Líneas | Propósito |
|---|---------|--------|-----------|
| 1 | `app/Controllers/Inspecciones/ActaVisitaController.php` (líneas 1273-1438) | ~165 del módulo | Controlador principal: genera token, renderiza vista pública, procesa AJAX, envía email |
| 2 | `app/Views/inspecciones/acta_visita/evaluaciones_visita.php` | ~204 | Vista pública HTML+CSS+JS: lista de ítems con checkboxes para marcar cumplimiento |
| 3 | `app/Views/inspecciones/acta_visita/evaluaciones_visita_error.php` | ~23 | Vista de error cuando el enlace es inválido o el acta no está finalizada |
| 4 | `app/Models/EvaluationModel.php` | ~50 | Modelo de la tabla `evaluacion_inicial_sst` (tabla de evaluación de estándares SST) |
| 5 | `app/Config/Routes.php` (líneas 1517-1519) | 3 | Rutas públicas sin autenticación |
| 6 | `app/SQL/reenviar_evaluaciones_rapidas.php` | ~130 | Script standalone para reenvío masivo de emails (uso único) |
| 7 | `app/SQL/evaluacion_inicial_sst.sql` | ~600 | Datos semilla: 60 ítems de estándares mínimos SST (Decreto 1072) por cliente |

### Dependencias internas (archivos del sistema que el módulo usa)

| Archivo | Relación |
|---------|----------|
| `app/Models/ActaVisitaModel.php` | Modelo de `tbl_acta_visita` — busca el acta y valida estado `completo` |
| `app/Models/ClientModel.php` | Modelo de `tbl_clientes` — obtiene nombre del cliente |
| `app/Models/ConsultantModel.php` | Modelo de `tbl_consultor` — obtiene nombre y correo del consultor |

---

## 2. RUTAS DEL APLICATIVO

### Rutas públicas (sin autenticación, fuera del grupo `$routes->group()` con filtro auth)

| Método HTTP | URL | Controlador::Método | Parámetros | Descripción |
|-------------|-----|---------------------|------------|-------------|
| `GET` | `/acta-visita/evaluaciones-visita/{actaId}/{token}` | `Inspecciones\ActaVisitaController::evaluacionesVisita($1, $2)` | `actaId` (int), `token` (string 24 chars) | Renderiza página pública de evaluaciones de cumplimiento |
| `POST` | `/acta-visita/evaluaciones-visita/update` | `Inspecciones\ActaVisitaController::updateEvaluacionPublica()` | POST: `id`, `acta_id`, `token` | AJAX: marca un ítem como "CUMPLE TOTALMENTE" |

### Definición en Routes.php (líneas 1517-1519)

```php
// Evaluaciones rápidas post-visita (acceso por token, sin auth)
$routes->get('acta-visita/evaluaciones-visita/(:num)/(:any)', 'Inspecciones\ActaVisitaController::evaluacionesVisita/$1/$2');
$routes->post('acta-visita/evaluaciones-visita/update', 'Inspecciones\ActaVisitaController::updateEvaluacionPublica');
```

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tabla PROPIA del módulo: `evaluacion_inicial_sst`

```sql
CREATE TABLE IF NOT EXISTS `evaluacion_inicial_sst` (
    `id_ev_ini` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `ciclo` VARCHAR(50) NULL COMMENT 'Fase PHVA: I. PLANEAR, II. HACER, III. VERIFICAR, IV. ACTUAR',
    `estandar` VARCHAR(255) NULL COMMENT 'Grupo estándar: RECURSOS (10%), GESTIÓN SALUD (20%), etc.',
    `detalle_estandar` TEXT NULL COMMENT 'Descripción del grupo estándar',
    `estandares_minimos` VARCHAR(255) NULL COMMENT 'Referencia normativa',
    `numeral` VARCHAR(20) NULL COMMENT 'Numeral del estándar: 1.1.1, 2.3.1, etc.',
    `numerales_del_cliente` INT NULL,
    `siete` INT NULL COMMENT 'Umbral 7 trabajadores',
    `veintiun` INT NULL COMMENT 'Umbral 21 trabajadores',
    `sesenta` INT NULL COMMENT 'Umbral 60 trabajadores',
    `item_del_estandar` TEXT NULL COMMENT 'Nombre descriptivo del ítem',
    `evaluacion_inicial` VARCHAR(50) NULL COMMENT 'CUMPLE TOTALMENTE | NO CUMPLE | NO APLICA | NULL | - (sin evaluar)',
    `valor` DECIMAL(5,2) DEFAULT 0 COMMENT 'Peso del ítem en el puntaje total',
    `puntaje_cuantitativo` DECIMAL(5,2) DEFAULT 0 COMMENT 'Puntaje obtenido (= valor si cumple)',
    `item` TEXT NULL COMMENT 'Nombre corto del criterio',
    `criterio` TEXT NULL COMMENT 'Descripción completa del criterio normativo',
    `modo_de_verificacion` TEXT NULL COMMENT 'Cómo verificar cumplimiento',
    `calificacion` DECIMAL(5,2) DEFAULT 0,
    `nivel_de_evaluacion` VARCHAR(100) NULL,
    `observaciones` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_cliente` (`id_cliente`),
    INDEX `idx_evaluacion` (`evaluacion_inicial`),
    CONSTRAINT `fk_eval_ini_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Valores posibles del campo `evaluacion_inicial`

| Valor | Significado | Color en UI |
|-------|-------------|-------------|
| `NULL` | Sin evaluar | Amarillo (#ffc107) |
| `''` (vacío) | Sin evaluar | Amarillo (#ffc107) |
| `'-'` | Sin evaluar | Amarillo (#ffc107) |
| `'NO CUMPLE'` | No cumple | Rojo (#dc3545) |
| `'CUMPLE TOTALMENTE'` | Cumple | Verde (#28a745) |
| `'NO APLICA'` | No aplica al cliente | (no aparece en evaluaciones rápidas) |

#### Datos semilla

Se insertan ~60 ítems por cliente, basados en los Estándares Mínimos del Decreto 1072/2015 de Colombia. El archivo `app/SQL/evaluacion_inicial_sst.sql` contiene los INSERT con `id_cliente = 999` como plantilla. Al crear un nuevo cliente, se copian estos registros cambiando el `id_cliente`.

### 3.2 Tablas del SISTEMA que el módulo consulta

#### `tbl_acta_visita` (campos usados por el módulo)

| Campo | Tipo | Uso |
|-------|------|-----|
| `id` | INT PK | Identificador del acta, parte del token |
| `id_cliente` | INT FK | Para filtrar evaluaciones del cliente |
| `id_consultor` | INT FK | Para enviar email al consultor |
| `fecha_visita` | DATE | Mostrada en email y vista pública |
| `estado` | ENUM('borrador','pendiente_firma','completo') | Solo accesible si `estado = 'completo'` |

#### `tbl_clientes` (campos usados)

| Campo | Tipo | Uso |
|-------|------|-----|
| `id_cliente` | INT PK | FK desde evaluacion_inicial_sst y tbl_acta_visita |
| `nombre_cliente` | VARCHAR(255) | Mostrado en header de la vista y subject del email |
| `id_consultor` | INT FK | Consultor asignado al cliente (puede diferir del consultor del acta) |

#### `tbl_consultor` (campos usados)

| Campo | Tipo | Uso |
|-------|------|-----|
| `id_consultor` | INT PK | Identificador |
| `nombre_consultor` | VARCHAR(255) | Nombre en el saludo del email |
| `correo_consultor` | VARCHAR(255) | Destinatario del email |

---

## 4. FLUJO FUNCIONAL

### 4.1 Diagrama de flujo completo

```
CONSULTOR finaliza acta de visita
    ↓
ActaVisitaController::finalizar($id)
  ó
ActaVisitaController::finalizarSinFirma($id)
    ↓
  estado = 'completo', genera PDF
    ↓
  enviarEmailEvaluacionesRapidas($acta)  ← TRIGGER del módulo
    ↓
  Genera token SHA256 (actaId + clienteId + salt)
    ↓
  Envía email por SendGrid al consultor del acta
  + al consultor del cliente (si es diferente)
    ↓
CONSULTOR recibe email con botón "✔ Actualizar Evaluaciones"
    ↓
  Clic en botón → GET /acta-visita/evaluaciones-visita/{actaId}/{token}
    ↓
  evaluacionesVisita() valida token + estado acta
    ↓
  Query: evaluaciones del cliente donde evaluacion_inicial
         IS NULL, vacía, '-', o 'NO CUMPLE'
    ↓
  Renderiza vista con checkboxes agrupados por estándar
    ↓
CONSULTOR marca checkbox de un ítem
    ↓
  JS envía POST /acta-visita/evaluaciones-visita/update
    con: id (del ítem), acta_id, token
    ↓
  updateEvaluacionPublica() valida token + pertenencia
    ↓
  UPDATE evaluacion_inicial = 'CUMPLE TOTALMENTE',
         puntaje_cuantitativo = valor
    ↓
  Respuesta JSON { success: true }
    ↓
  JS actualiza UI: card verde, badge "✔ CUMPLE TOTALMENTE",
  contador pendientes -1, cerrados +1, toast confirmación
```

### 4.2 Métodos del controlador

| Método | Visibilidad | Línea | Descripción |
|--------|-------------|-------|-------------|
| `generarTokenEvaluacion(int $actaId, int $clienteId): string` | `private` | 1277 | Genera token SHA256 de 24 chars usando `actaId|clienteId|evvisita2026` como input |
| `evaluacionesVisita(int $actaId, string $token)` | `public` | 1285 | GET: Valida token, consulta evaluaciones pendientes del cliente, renderiza vista |
| `updateEvaluacionPublica()` | `public` | 1326 | POST/AJAX: Valida token, actualiza `evaluacion_inicial` a `CUMPLE TOTALMENTE` |
| `enviarEmailEvaluacionesRapidas(array $acta): void` | `private` | 1364 | Envía email por SendGrid con enlace tokenizado al consultor |

### 4.3 Flujo AJAX detallado

**Request:**
```
POST /acta-visita/evaluaciones-visita/update
Content-Type: multipart/form-data

id=<id_ev_ini>           // PK de evaluacion_inicial_sst
acta_id=<id del acta>    // Para validar token
token=<24 chars>         // Token SHA256
```

**Response éxito:**
```json
{ "success": true }
```

**Response error:**
```json
{ "success": false, "message": "Token inválido" }
```

### 4.4 Estados y transiciones

```
evaluacion_inicial: NULL / '' / '-'  →  'CUMPLE TOTALMENTE'   (vía checkbox)
evaluacion_inicial: 'NO CUMPLE'      →  'CUMPLE TOTALMENTE'   (vía checkbox)
```

> **Nota:** La transición es **unidireccional**. No hay opción de deshacer desde la vista pública. Solo se puede revertir desde el panel del consultor autenticado.

### 4.5 Integración con otros módulos

| Módulo | Relación |
|--------|----------|
| **Acta de Visita** | Trigger: al finalizar un acta (`finalizar()` o `finalizarSinFirma()`), se llama `enviarEmailEvaluacionesRapidas()` |
| **Evaluación Inicial SST** | Tabla compartida: este módulo escribe sobre `evaluacion_inicial_sst`, que también es usada por el panel del consultor (`list_evaluaciones`, `edit_evaluacion`) |
| **Informe de Avances** | Lee `evaluacion_inicial_sst` para calcular porcentaje de cumplimiento del cliente |

---

## 5. SEGURIDAD

### 5.1 Autenticación

- Las rutas son **públicas** (sin filtro auth).
- La autenticación se hace por **token HMAC-like** en la URL.

### 5.2 Generación del token

```php
private function generarTokenEvaluacion(int $actaId, int $clienteId): string
{
    return substr(hash('sha256', $actaId . '|' . $clienteId . '|evvisita2026'), 0, 24);
}
```

- **Input:** `"{actaId}|{clienteId}|evvisita2026"`
- **Hash:** SHA256, truncado a 24 caracteres hexadecimales
- **Salt fijo:** `evvisita2026` (hardcodeado)
- **No expira:** El token es válido mientras el acta tenga `estado = 'completo'`

### 5.3 Validaciones en cada request

1. **GET (vista):** Acta existe + estado `completo` + `hash_equals(token generado, token recibido)`
2. **POST (update):** Acta existe + estado `completo` + `hash_equals` + evaluación pertenece al mismo `id_cliente` del acta

---

## 6. EMAIL

### 6.1 Configuración

| Parámetro | Valor |
|-----------|-------|
| Servicio | SendGrid API v3 |
| Endpoint | `https://api.sendgrid.com/v3/mail/send` |
| API Key | Variable de entorno `SENDGRID_API_KEY` |
| From email | `notificacion.cycloidtalent@cycloidtalent.com` |
| From name | `Cycloid Talent - SG-SST` |
| Click tracking | **Deshabilitado** (para que la URL no sea reescrita) |
| SSL verify | `false` |

### 6.2 Trigger

Se dispara desde dos métodos del controlador de acta de visita:
- `finalizar($id)` — línea 498
- `finalizarSinFirma($id)` — línea 549

### 6.3 Destinatarios

1. **Consultor del acta** (`tbl_acta_visita.id_consultor`) — siempre
2. **Consultor del cliente** (`tbl_clientes.id_consultor`) — solo si es diferente al del acta

### 6.4 Template del email (HTML inline)

```html
<div style='font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:0 auto;'>
    <div style='background:#1c2437;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
        <h1 style='color:#bd9751;margin:0;font-size:20px;'>Evaluación Rápida Post-Visita</h1>
    </div>
    <div style='padding:25px;background:#f8f9fa;border-radius:0 0 10px 10px;'>
        <p>Hola <strong>{nombre_consultor}</strong>,</p>
        <p>El acta de visita del <strong>{fecha_visita dd/mm/yyyy}</strong> para
           <strong>{nombre_cliente}</strong> ha sido finalizada.</p>
        <p>Usa este enlace para marcar los ítems de cumplimiento que se cerraron en esta visita:</p>
        <div style='text-align:center;margin:24px 0;'>
            <a href='{url}' style='background:#bd9751;color:white;padding:14px 28px;
               border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;'>
                ✔ Actualizar Evaluaciones
            </a>
        </div>
        <p style='font-size:12px;color:#999;'>Enlace directo: {url}</p>
        <p style='color:#999;font-size:11px;'>Generado por SG-SST Cycloid Talent.</p>
    </div>
</div>
```

### 6.5 Subject

```
Evaluaciones rápidas — {nombre_cliente} — {fecha dd/mm/yyyy}
```

### 6.6 Payload SendGrid

```php
$payload = json_encode([
    'personalizations' => [[
        'to' => [['email' => $correo, 'name' => $nombre]],
        'subject' => $subject
    ]],
    'from' => [
        'email' => 'notificacion.cycloidtalent@cycloidtalent.com',
        'name' => 'Cycloid Talent - SG-SST'
    ],
    'content' => [['type' => 'text/html', 'value' => $html]],
    'tracking_settings' => [
        'click_tracking' => ['enable' => false, 'enable_text' => false],
    ],
]);
```

---

## 7. FRONTEND (Vista pública)

### 7.1 Stack

| Componente | Fuente | Versión |
|------------|--------|---------|
| Font Awesome | CDN cloudflare | 6.4.0 |
| CSS | Inline en la vista | Custom (no Bootstrap) |
| JS | Vanilla JavaScript | ES5 compatible |

> **No usa jQuery, Bootstrap, SweetAlert ni librerías externas de rating.**

### 7.2 Estructura de la vista

```
┌─────────────────────────────────────────┐
│ HEADER (sticky, #1c2437)                │
│  "Evaluaciones de Cumplimiento"         │
│  Cliente · Visita: dd/mm/yyyy           │
├─────────────────────────────────────────┤
│ STATS BAR (blanca)                      │
│  Pendientes: N    Cerrados hoy: N       │
├─────────────────────────────────────────┤
│ GRUPO ESTÁNDAR (header #1c2437)         │
│  ┌──────────────────────────────────┐   │
│  │ [☐] Numeral 1.1.2               │   │
│  │     Item del estándar...         │   │
│  │     NO CUMPLE (rojo)             │   │
│  ├──────────────────────────────────┤   │
│  │ [☐] Numeral 2.3.1               │   │
│  │     Item del estándar...         │   │
│  │     Sin evaluar (amarillo)       │   │
│  └──────────────────────────────────┘   │
│                                         │
│ (más grupos...)                         │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │  TOAST (fijo abajo, verde)          │ │
│ │  "✔ Marcado como CUMPLE TOTALMENTE" │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

### 7.3 Estado vacío

Si no hay evaluaciones pendientes:

```
┌─────────────────────────────────────────┐
│     ✅ (icono grande)                   │
│     ¡Todo al día!                       │
│     No hay ítems pendientes             │
└─────────────────────────────────────────┘
```

### 7.4 Paleta de colores

| Elemento | Color |
|----------|-------|
| Header / grupo estándar | `#1c2437` (azul oscuro) |
| Dorado corporativo | `#bd9751` |
| Cumple | `#28a745` (verde) |
| No cumple | `#dc3545` (rojo) |
| Sin evaluar | `#ffc107` (amarillo) |
| Fondo | `#f0f2f5` |
| Cards | `#ffffff` |

### 7.5 JavaScript completo

```javascript
var pendienteCount = <?= count($evaluaciones) ?>;
var cerradoCount   = 0;
var actaId = <?= (int) $acta['id'] ?>;
var actaToken = '<?= esc($token ?? '') ?>';

function showToast(msg) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(function() { t.style.display = 'none'; }, 2000);
}

document.querySelectorAll('input[type=checkbox]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        if (!this.checked) return;  // Solo actúa al marcar, no al desmarcar
        var id    = this.dataset.id;
        var self  = this;

        self.disabled = true;  // Bloquea inmediatamente

        var fd = new FormData();
        fd.append('id', id);
        fd.append('acta_id', actaId);
        fd.append('token', actaToken);

        fetch('/acta-visita/evaluaciones-visita/update', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                // Éxito: actualizar UI
                var card   = document.getElementById('card-' + id);
                var estado = document.getElementById('estado-' + id);
                card.classList.remove('vacio');
                card.classList.add('cumple');
                estado.innerHTML = '<span class="badge-done">✔ CUMPLE TOTALMENTE</span>';
                pendienteCount--;
                cerradoCount++;
                document.getElementById('cntPendiente').textContent = pendienteCount;
                document.getElementById('cntCerrado').textContent   = cerradoCount;
                showToast('✔ Marcado como CUMPLE TOTALMENTE');
            } else {
                // Error: desbloquear checkbox
                self.checked  = false;
                self.disabled = false;
                alert('Error al guardar: ' + (data.message || 'Intenta de nuevo.'));
            }
        })
        .catch(function(err) {
            self.checked  = false;
            self.disabled = false;
            alert('Error de conexión.');
        });
    });
});
```

---

## 8. DEPENDENCIAS EXTERNAS

### PHP (Composer)

Ninguna dependencia externa específica para este módulo. Usa solo funciones nativas de PHP:
- `hash('sha256', ...)` — generación de token
- `curl_*` — envío de email por SendGrid
- `json_encode()` — payload del email
- `htmlspecialchars()` — escape de datos en HTML

### CDN Frontend

| Librería | URL | Uso |
|----------|-----|-----|
| Font Awesome 6.4.0 | `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css` | Iconos (clipboard-check, check-circle) |

---

## 9. QUERY CLAVE

### Query para obtener evaluaciones pendientes (en `evaluacionesVisita()`)

```php
$evaluaciones = $evaluacionModel
    ->where('id_cliente', $acta['id_cliente'])
    ->groupStart()
        ->where('evaluacion_inicial IS NULL', null, false)
        ->orWhere('evaluacion_inicial', '')
        ->orWhere('evaluacion_inicial', '-')
        ->orWhere('evaluacion_inicial', 'NO CUMPLE')
    ->groupEnd()
    ->orderBy('estandar', 'ASC')
    ->orderBy('numeral', 'ASC')
    ->findAll();
```

**SQL equivalente:**
```sql
SELECT * FROM evaluacion_inicial_sst
WHERE id_cliente = ?
  AND (
    evaluacion_inicial IS NULL
    OR evaluacion_inicial = ''
    OR evaluacion_inicial = '-'
    OR evaluacion_inicial = 'NO CUMPLE'
  )
ORDER BY estandar ASC, numeral ASC;
```

> Excluye `CUMPLE TOTALMENTE` y `NO APLICA`, mostrando solo los ítems que el consultor puede cerrar.

### Query de actualización (en `updateEvaluacionPublica()`)

```php
$updateData = [
    'evaluacion_inicial'    => 'CUMPLE TOTALMENTE',
    'puntaje_cuantitativo'  => $evaluation['valor'],  // Copia el peso del ítem
];
$model->update($id, $updateData);
```

**SQL equivalente:**
```sql
UPDATE evaluacion_inicial_sst
SET evaluacion_inicial = 'CUMPLE TOTALMENTE',
    puntaje_cuantitativo = valor,
    updated_at = NOW()
WHERE id_ev_ini = ?;
```

---

## 10. SCRIPT DE REENVÍO MASIVO

Archivo: `app/SQL/reenviar_evaluaciones_rapidas.php`

Script standalone (no CI4) para reenviar emails a todos los consultores con actas completadas. Uso:

```bash
SG_KEY=SG.xxx DB_PROD_PASS=xxx php reenviar_evaluaciones_rapidas.php
```

- Se conecta directo a MySQL con PDO (sin framework)
- Genera el mismo token con el mismo algoritmo
- Replica el mismo template HTML del email
- Pausa 200ms entre envíos para respetar rate limits de SendGrid
- Muestra resumen OK/ERR al final

---

## 11. ORDEN DE IMPLEMENTACIÓN

### Paso 1: Base de datos

```sql
-- 1a. Crear tabla evaluacion_inicial_sst (ver sección 3.1)
-- 1b. Insertar datos semilla por cliente (copiar de evaluacion_inicial_sst.sql, cambiar id_cliente)
-- 1c. Las tablas tbl_acta_visita, tbl_clientes, tbl_consultor ya deben existir
```

### Paso 2: Modelo

Crear `app/Models/EvaluationModel.php`:

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class EvaluationModel extends Model
{
    protected $table = 'evaluacion_inicial_sst';
    protected $primaryKey = 'id_ev_ini';
    protected $allowedFields = [
        'id_cliente', 'ciclo', 'estandar', 'detalle_estandar', 'estandares_minimos',
        'numeral', 'numerales_del_cliente', 'siete', 'veintiun', 'sesenta',
        'item_del_estandar', 'evaluacion_inicial', 'valor', 'puntaje_cuantitativo',
        'item', 'criterio', 'modo_de_verificacion', 'calificacion',
        'nivel_de_evaluacion', 'observaciones',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
```

### Paso 3: Rutas

Agregar al final de `app/Config/Routes.php` (fuera de cualquier grupo con filtro auth):

```php
// Evaluaciones rápidas post-visita (acceso por token, sin auth)
$routes->get('acta-visita/evaluaciones-visita/(:num)/(:any)',
    'Inspecciones\ActaVisitaController::evaluacionesVisita/$1/$2');
$routes->post('acta-visita/evaluaciones-visita/update',
    'Inspecciones\ActaVisitaController::updateEvaluacionPublica');
```

### Paso 4: Controlador

Agregar los 4 métodos al `ActaVisitaController` (sección completa: líneas 1273-1438):

1. `generarTokenEvaluacion()` — genera token
2. `evaluacionesVisita()` — renderiza vista pública
3. `updateEvaluacionPublica()` — AJAX de actualización
4. `enviarEmailEvaluacionesRapidas()` — envía email

Agregar llamada a `enviarEmailEvaluacionesRapidas($acta)` al final de:
- `finalizar($id)` — después de `actualizarCicloVisita()`
- `finalizarSinFirma($id)` — después de `actualizarCicloVisita()`

### Paso 5: Vistas

1. Crear `app/Views/inspecciones/acta_visita/evaluaciones_visita.php` (204 líneas, HTML+CSS+JS autocontenido)
2. Crear `app/Views/inspecciones/acta_visita/evaluaciones_visita_error.php` (23 líneas, página de error)

### Paso 6: Variable de entorno

Configurar `SENDGRID_API_KEY` en `.env`:
```
SENDGRID_API_KEY=SG.xxxxxxxxx
```

### Paso 7: Verificación

1. Finalizar un acta de visita
2. Verificar que llega el email al consultor
3. Clic en "Actualizar Evaluaciones"
4. Verificar que se muestra la lista de ítems pendientes
5. Marcar un checkbox → debe cambiar a verde y mostrar toast
6. Recargar la página → el ítem marcado no debe aparecer
7. Verificar en BD que `evaluacion_inicial = 'CUMPLE TOTALMENTE'` y `puntaje_cuantitativo = valor`

---

## 12. NOTAS IMPORTANTES

### Comportamiento del token

- El token es **determinístico**: mismo actaId + clienteId siempre produce el mismo token.
- **No expira** por tiempo: solo se invalida si el acta deja de tener estado `completo`.
- El salt `evvisita2026` está **hardcodeado** en el controlador y en el script de reenvío.

### Agrupación en la vista

Los ítems se agrupan por el campo `estandar` de la tabla `evaluacion_inicial_sst`. Los grupos se renderizan en PHP con `foreach`, no con JavaScript.

### Optimistic UI

La UI bloquea el checkbox inmediatamente al hacer clic (`disabled = true`). Si el servidor responde error, lo desbloquea y desmarca. No hay spinner ni overlay de loading.

### Sin paginación

Todos los ítems pendientes se cargan en una sola página. No hay paginación, lazy loading ni scroll infinito. Para un cliente típico (60 ítems en total), los pendientes suelen ser 10-30.
