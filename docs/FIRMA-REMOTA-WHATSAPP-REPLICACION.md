# DOCUMENTO DE REPLICACION — Firma Remota por WhatsApp

> **Modulo padre:** Acta de Visita (`inspecciones/acta-visita`)
> **Framework:** CodeIgniter 4 / PHP 8.2 / MySQL
> **Fecha:** 2026-04-07

---

## 1. RESUMEN DEL FLUJO

```
CONSULTOR (autenticado)                          FIRMANTE (sin login, via celular)
─────────────────────                            ───────────────────────────────────
1. En pantalla firma.php                         
   click boton "Enviar enlace" (WhatsApp)        
         │                                       
2. SweetAlert: "Se generara enlace              
   para {Administrador} (24h)"                   
   → Confirma                                    
         │                                       
3. POST /acta-visita/generar-token-firma/{id}    
   → Backend genera token hex 64 chars           
   → Guarda en BD + expiracion 24h               
   → Responde {success, url}                     
         │                                       
4. SweetAlert muestra URL                        
   → Click "Abrir WhatsApp"                      
   → window.open("https://wa.me/?text=...")       
         │                                       
5. WhatsApp se abre con mensaje                  
   preformateado + URL                            
         │                                       
         └──── firmante recibe enlace ──────────→ 6. Abre URL en navegador movil
                                                     GET /acta-visita/firmar-remoto/{token}
                                                         │
                                                  7. Backend valida:
                                                     - token existe en BD
                                                     - no expirado (< 24h)
                                                     - firma no registrada aun
                                                         │
                                                  8. Renderiza firma_remota.php:
                                                     - Resumen completo del acta
                                                     - Canvas para firmar
                                                     - Boton "Firmar Acta de Visita"
                                                         │
                                                  9. Firmante dibuja y confirma
                                                     POST /acta-visita/procesar-firma-remota
                                                     Body: {token, firma_imagen (base64)}
                                                         │
                                                  10. Backend:
                                                      - Valida token + expiracion
                                                      - Decodifica base64 → PNG
                                                      - Guarda en uploads/inspecciones/firmas/
                                                      - Actualiza firma_{tipo} en BD
                                                      - LIMPIA token (null) → enlace muere
                                                         │
                                                  11. Responde {success:true}
                                                      → "Firma registrada" en pantalla
                                                         │
                                                  OFFLINE: Si no hay conexion
                                                      → IndexedDB (OfflineQueue)
                                                      → Auto-sync al reconectar
```

---

## 2. ARCHIVOS INVOLUCRADOS

| Archivo | Lineas | Rol |
|---------|--------|-----|
| `app/Controllers/Inspecciones/ActaVisitaController.php` | L673-696 | `generarTokenFirma()` — genera token |
| `app/Controllers/Inspecciones/ActaVisitaController.php` | L701-777 | `firmarRemoto()` — pagina publica |
| `app/Controllers/Inspecciones/ActaVisitaController.php` | L782-817 | `procesarFirmaRemota()` — guarda firma |
| `app/Views/inspecciones/acta_visita/firma.php` | L343-393 | JS: boton WhatsApp + generacion token |
| `app/Views/inspecciones/acta_visita/firma_remota.php` | 381 lineas | Pagina publica completa (HTML+CSS+JS) |
| `app/Views/inspecciones/acta_visita/firma_remota_error.php` | 21 lineas | Pagina error token invalido/expirado |
| `app/Models/ActaVisitaModel.php` | — | Lectura/escritura token en tbl_acta_visita |
| `public/js/offline_queue.js` | 197 lineas | Cola IndexedDB para firma offline |
| `app/Config/Routes.php` | L1513-1518 | Rutas publicas sin auth |

---

## 3. RUTAS

```php
// === Rutas AUTENTICADAS (dentro del grupo inspecciones) ===
$routes->post('acta-visita/generar-token-firma/(:num)', 'Inspecciones\ActaVisitaController::generarTokenFirma/$1');

// === Rutas PUBLICAS (fuera de cualquier grupo auth) ===
$routes->get('acta-visita/firmar-remoto/(:any)',        'Inspecciones\ActaVisitaController::firmarRemoto/$1');
$routes->post('acta-visita/procesar-firma-remota',      'Inspecciones\ActaVisitaController::procesarFirmaRemota');
```

**IMPORTANTE:** Las rutas publicas NO tienen filtro de autenticacion. El token ES la autenticacion.

---

## 4. BASE DE DATOS

### 4.1 Columnas en tbl_acta_visita (ya existente)

```sql
-- Migracion: migrate_acta_firma_remota.php
ALTER TABLE tbl_acta_visita
    ADD COLUMN IF NOT EXISTS token_firma_remota VARCHAR(64) NULL DEFAULT NULL
        AFTER firma_consultor;

ALTER TABLE tbl_acta_visita
    ADD COLUMN IF NOT EXISTS token_firma_tipo VARCHAR(20) NULL DEFAULT NULL
        AFTER token_firma_remota;

ALTER TABLE tbl_acta_visita
    ADD COLUMN IF NOT EXISTS token_firma_expiracion DATETIME NULL DEFAULT NULL
        AFTER token_firma_tipo;
```

### 4.2 Uso de columnas

| Columna | Tipo | Cuando se escribe | Cuando se lee | Cuando se borra |
|---------|------|-------------------|---------------|-----------------|
| `token_firma_remota` | VARCHAR(64) | `generarTokenFirma()` — hex 64 chars | `firmarRemoto()` y `procesarFirmaRemota()` — WHERE token = ? | `procesarFirmaRemota()` — SET NULL |
| `token_firma_tipo` | VARCHAR(20) | `generarTokenFirma()` — 'administrador'\|'vigia'\|'consultor' | `firmarRemoto()` — determina campo firma y nombre firmante | `procesarFirmaRemota()` — SET NULL |
| `token_firma_expiracion` | DATETIME | `generarTokenFirma()` — NOW() + 24h | `firmarRemoto()` y `procesarFirmaRemota()` — validacion | `procesarFirmaRemota()` — SET NULL |

### 4.3 Columnas de firma (preexistentes)

```
firma_administrador  VARCHAR(255) NULL  → ruta PNG relativa
firma_vigia          VARCHAR(255) NULL  → ruta PNG relativa
firma_consultor      VARCHAR(255) NULL  → ruta PNG relativa
```

---

## 5. BACKEND — CODIGO COMPLETO

### 5.1 generarTokenFirma($id) — Generar token (L673-696)

```php
/**
 * AJAX (auth): genera token y devuelve URL para compartir
 */
public function generarTokenFirma(int $id)
{
    $tipo = $this->request->getPost('tipo');
    if (!in_array($tipo, ['administrador', 'vigia', 'consultor'])) {
        return $this->response->setJSON(['success' => false, 'error' => 'Tipo inválido']);
    }

    $acta = $this->actaModel->find($id);
    if (!$acta) {
        return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
    }

    // Token criptografico de 64 caracteres hex (32 bytes)
    $token     = bin2hex(random_bytes(32));
    $expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $this->actaModel->update($id, [
        'token_firma_remota'     => $token,
        'token_firma_tipo'       => $tipo,
        'token_firma_expiracion' => $expiracion,
    ]);

    $url = base_url("acta-visita/firmar-remoto/{$token}");
    return $this->response->setJSON(['success' => true, 'url' => $url, 'tipo' => $tipo]);
}
```

**Notas:**
- Solo 1 token activo por acta a la vez (se sobreescribe si se genera otro)
- El token es para UN tipo de firmante especifico
- `bin2hex(random_bytes(32))` = 64 caracteres hex criptograficamente seguros

### 5.2 firmarRemoto($token) — Pagina publica (L701-777)

```php
/**
 * Página pública: canvas de firma para el firmante remoto
 */
public function firmarRemoto(string $token)
{
    // 1. Buscar acta por token
    $acta = $this->actaModel->where('token_firma_remota', $token)->first();

    if (!$acta) {
        return view('inspecciones/acta_visita/firma_remota_error', [
            'mensaje' => 'Este enlace no es válido o ya fue usado.'
        ]);
    }

    // 2. Validar expiracion
    if (strtotime($acta['token_firma_expiracion']) < time()) {
        return view('inspecciones/acta_visita/firma_remota_error', [
            'mensaje' => 'Este enlace ha expirado. Pida uno nuevo al consultor.'
        ]);
    }

    // 3. Validar que no este firmado ya
    $campoFirma = 'firma_' . $acta['token_firma_tipo'];
    if (!empty($acta[$campoFirma])) {
        return view('inspecciones/acta_visita/firma_remota_error', [
            'mensaje' => 'Esta firma ya fue registrada.'
        ]);
    }

    // 4. Cargar datos del cliente
    $clientModel = new ClientModel();
    $cliente = $clientModel->find($acta['id_cliente']);

    // 5. Determinar nombre del firmante segun integrantes
    $integrantes = $this->integranteModel->getByActa($acta['id']);
    $nombreFirmante = '';
    foreach ($integrantes as $integrante) {
        $rol  = strtoupper($integrante['rol']);
        $tipo = $acta['token_firma_tipo'];
        if ($tipo === 'administrador' && strpos($rol, 'ADMIN') !== false) {
            $nombreFirmante = $integrante['nombre'];
            break;
        }
        if ($tipo === 'vigia' && strpos($rol, 'VIG') !== false) {
            $nombreFirmante = $integrante['nombre'];
            break;
        }
        if ($tipo === 'consultor' && strpos($rol, 'CONSULTOR') !== false) {
            $nombreFirmante = $integrante['nombre'];
            break;
        }
    }

    // 6. Cargar datos del acta para mostrar resumen
    $temas       = $this->temaModel->getByActa($acta['id']);
    $compromisos = (new PendientesModel())->where('id_acta_visita', $acta['id'])->findAll();

    $pendientesAbiertos = (new PendientesModel())
        ->where('id_cliente', $acta['id_cliente'])
        ->where('estado', 'ABIERTA')
        ->groupStart()
            ->where('id_acta_visita IS NULL', null, false)
            ->orWhere('id_acta_visita !=', $acta['id'])
        ->groupEnd()
        ->findAll();

    $dateThreshold = date('Y-m-d', strtotime('+30 days'));
    $mantenimientos = (new VencimientosMantenimientoModel())
        ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
        ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
        ->where('tbl_vencimientos_mantenimientos.id_cliente', $acta['id_cliente'])
        ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
        ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $dateThreshold)
        ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
        ->findAll();

    // 7. Renderizar pagina publica
    return view('inspecciones/acta_visita/firma_remota', [
        'token'              => $token,
        'acta'               => $acta,
        'cliente'            => $cliente,
        'tipo'               => $acta['token_firma_tipo'],
        'nombreFirmante'     => $nombreFirmante,
        'integrantes'        => $integrantes,
        'temas'              => $temas,
        'compromisos'        => $compromisos,
        'pendientesAbiertos' => $pendientesAbiertos,
        'mantenimientos'     => $mantenimientos,
    ]);
}
```

### 5.3 procesarFirmaRemota() — Guardar firma (L782-817)

```php
/**
 * AJAX público: recibe y guarda la firma remota
 */
public function procesarFirmaRemota()
{
    $token       = $this->request->getPost('token');
    $firmaBase64 = $this->request->getPost('firma_imagen');

    // 1. Buscar acta por token
    $acta = $this->actaModel->where('token_firma_remota', $token)->first();
    if (!$acta) {
        return $this->response->setJSON(['success' => false, 'error' => 'Enlace inválido']);
    }

    // 2. Validar expiracion
    if (strtotime($acta['token_firma_expiracion']) < time()) {
        return $this->response->setJSON(['success' => false, 'error' => 'Enlace expirado']);
    }

    // 3. Decodificar base64 a binario PNG
    $tipo = $acta['token_firma_tipo'];
    $firmaData    = explode(',', $firmaBase64);
    $firmaDecoded = base64_decode(end($firmaData));

    // 4. Guardar archivo PNG en disco
    $dir = FCPATH . 'uploads/inspecciones/firmas/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $nombreArchivo = "firma_{$tipo}_{$acta['id']}_" . time() . '.png';
    file_put_contents($dir . $nombreArchivo, $firmaDecoded);

    // 5. Actualizar BD: firma + limpiar token
    $campo = "firma_{$tipo}";
    $this->actaModel->update($acta['id'], [
        $campo                   => "uploads/inspecciones/firmas/{$nombreArchivo}",
        'token_firma_remota'     => null,   // <-- Token de un solo uso
        'token_firma_tipo'       => null,
        'token_firma_expiracion' => null,
    ]);

    return $this->response->setJSON(['success' => true]);
}
```

**Seguridad:**
- El token se anula despues de usarse (un solo uso)
- Si el token se vence, devuelve error y sugiere pedir uno nuevo
- No requiere sesion/login — el token ES la autenticacion

---

## 6. FRONTEND — BOTON WHATSAPP EN firma.php

### 6.1 HTML del boton (L35-39)

```html
<!-- Dentro de cada paso de firma, junto a "Limpiar" -->
<button type="button" class="btn btn-sm btn-outline-success btn-whatsapp-firma"
    data-tipo="<?= esc($firmante['tipo']) ?>"
    title="Enviar enlace para firma remota">
    <i class="fab fa-whatsapp"></i> Enviar enlace
</button>
```

**Ubicacion:** Aparece en cada paso de firma que NO este ya firmado, al lado del boton "Limpiar".

### 6.2 JavaScript completo del flujo WhatsApp (L343-393)

```javascript
// WhatsApp firma remota
document.querySelectorAll('.btn-whatsapp-firma').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var tipo = this.dataset.tipo;
        var tipoLabel = {
            administrador: 'Administrador',
            vigia: 'Vigía SST',
            consultor: 'Consultor'
        }[tipo] || tipo;

        // PASO 1: Confirmacion con SweetAlert
        Swal.fire({
            title: 'Enviar enlace de firma',
            html: '<p style="font-size:14px;">Se generará un enlace para que <strong>'
                  + tipoLabel + '</strong> firme desde su celular.<br>'
                  + '<small class="text-muted">El enlace expira en 24 horas.</small></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Generar enlace',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#25D366',     // Verde WhatsApp
        }).then(function(result) {
            if (!result.isConfirmed) return;

            // PASO 2: Loading mientras genera token
            Swal.fire({
                title: 'Generando enlace...',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            // PASO 3: POST al backend para generar token
            fetch('/inspecciones/acta-visita/generar-token-firma/' + actaId, {
                method: 'POST',
                body: new URLSearchParams({ tipo: tipo }),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    Swal.fire('Error', data.error, 'error');
                    return;
                }

                // PASO 4: Construir URL de WhatsApp
                var url   = data.url;
                var texto = encodeURIComponent(
                    'Hola, por favor firma el acta de visita SST '
                    + 'haciendo clic en este enlace (válido 24 horas):\n'
                    + url
                );
                var waUrl = 'https://wa.me/?text=' + texto;

                // PASO 5: Mostrar enlace + boton WhatsApp
                Swal.fire({
                    title: 'Enlace generado',
                    html: '<p style="font-size:13px;">Comparte este enlace por WhatsApp '
                          + 'con el <strong>' + tipoLabel + '</strong>:</p>'
                          + '<div style="background:#f8f9fa;border-radius:8px;padding:10px;'
                          + 'font-size:11px;word-break:break-all;margin-bottom:12px;">'
                          + url + '</div>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fab fa-whatsapp"></i> Abrir WhatsApp',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#25D366',
                }).then(function(r) {
                    // PASO 6: Abrir WhatsApp Web/App
                    if (r.isConfirmed) window.open(waUrl, '_blank');

                    // Auto-avanzar al siguiente paso de firma
                    if (pasoActual < totalPasos - 1) cambiarPaso(1);
                    else updateNav();
                });
            })
            .catch(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        });
    });
});
```

### 6.3 URL de WhatsApp — Formato exacto

```
https://wa.me/?text=Hola%2C%20por%20favor%20firma%20el%20acta%20de%20visita%20SST%20haciendo%20clic%20en%20este%20enlace%20(v%C3%A1lido%2024%20horas)%3A%0Ahttps%3A%2F%2Fphorizontal.cycloidtalent.com%2Facta-visita%2Ffirmar-remoto%2F{token64chars}
```

**Desglosado:**
- `https://wa.me/?text=` — API universal WhatsApp (sin numero destino, el usuario elige)
- Mensaje: `Hola, por favor firma el acta de visita SST haciendo clic en este enlace (válido 24 horas):\n{URL}`
- El `\n` se codifica como `%0A`

---

## 7. FRONTEND — PAGINA PUBLICA firma_remota.php

### 7.1 Estructura HTML completa

La pagina es **standalone** (no usa layout del app). Incluye:

```
<!DOCTYPE html>
<html lang="es">
<head>
    - Bootstrap 5.3.0 (CDN)
    - Font Awesome 6.4.0 (CDN)
    - CSS inline completo (no depende de archivos del proyecto)
    - viewport: maximum-scale=1.0, user-scalable=no (evitar zoom en firma)
</head>
<body>
    ┌─ TOP BAR (sticky) ─────────────────────────┐
    │ Cycloid Talent · SST                        │
    │ Acta de Visita                              │
    │ {nombre_cliente} · {fecha}                  │
    └─────────────────────────────────────────────┘

    ┌─ AVISO ─────────────────────────────────────┐
    │ Revise el contenido del acta y firme al     │
    │ final como {Administrador}. ({nombre})      │
    └─────────────────────────────────────────────┘

    ┌─ DATOS DE LA VISITA ────────────────────────┐
    │ Fecha | Hora | Motivo | Modalidad | Cliente │
    │ Cartera (si aplica)                         │
    └─────────────────────────────────────────────┘

    ┌─ INTEGRANTES ───────────────────────────────┐
    │ Nombre .................. Badge(Rol)        │
    └─────────────────────────────────────────────┘

    ┌─ TEMAS ABIERTOS Y VENCIDOS ─────────────────┐
    │ Mantenimientos (tabla: nombre, vencimiento) │
    │ Pendientes (tabla: actividad, resp, cierre) │
    │ Dias con color: rojo/amarillo/verde         │
    └─────────────────────────────────────────────┘

    ┌─ TEMAS TRATADOS ────────────────────────────┐
    │ TEMA 1: ...                                 │
    │ TEMA 2: ...                                 │
    └─────────────────────────────────────────────┘

    ┌─ COMPROMISOS ───────────────────────────────┐
    │ Tabla: Actividad | Cierre | Responsable     │
    └─────────────────────────────────────────────┘

    ┌─ OBSERVACIONES ─────────────────────────────┐
    │ Texto libre (si hay)                        │
    └─────────────────────────────────────────────┘

    ┌─ SECCION FIRMA ─────────────────────────────┐
    │ FIRMA — ADMINISTRADOR                       │
    │ {Nombre del firmante}                       │
    │                                             │
    │ Aviso legal: Ley 1581 de 2012              │
    │                                             │
    │ ┌─ Canvas dashed border ─────────────────┐  │
    │ │                                        │  │
    │ │     (area de dibujo 220px alto)        │  │
    │ │                                        │  │
    │ └────────────────────────────────────────┘  │
    │                              [Limpiar]      │
    │                                             │
    │ ┌────────────────────────────────────────┐  │
    │ │   Firmar Acta de Visita (verde)        │  │
    │ └────────────────────────────────────────┘  │
    └─────────────────────────────────────────────┘

<script>
    - SweetAlert2 (CDN)
    - offline_queue.js (local)
    - Canvas con soporte mouse + touch
    - Filtro multi-touch (pinch-zoom no firma)
    - Validacion minima pixeles (>100)
    - Preview antes de confirmar
    - POST a /acta-visita/procesar-firma-remota
    - Fallback offline: IndexedDB + auto-sync
</script>
</body>
</html>
```

### 7.2 CSS Variables y colores

```css
:root {
    --gold: #bd9751;
    --dark: #2c3e50;
}

/* Top bar */
background: var(--dark);  color: white;

/* Aviso firma */
background: #fffbeb;  border: 1px solid #fbbf24;  color: #78350f;

/* Canvas firma */
border: 2px dashed #ccc;  background: #fafafa;  cursor: crosshair;

/* Boton firmar */
background: linear-gradient(135deg, #28a745, #1e7e34);  color: white;

/* Badges dias */
.dias-vencido { background: #fee2e2; color: #dc2626; }  /* rojo */
.dias-urgente { background: #fef3c7; color: #d97706; }  /* amarillo */
.dias-ok      { background: #dcfce7; color: #16a34a; }  /* verde */
```

### 7.3 Canvas — Configuracion

```javascript
var canvas = document.getElementById('firmaCanvas');
var ctx    = canvas.getContext('2d');
var dpr    = window.devicePixelRatio || 1;

// Resize con DPR para pantallas retina
canvas.width  = rect.width * dpr;
canvas.height = 220 * dpr;
ctx.scale(dpr, dpr);

// Estilo de trazo
ctx.strokeStyle = '#000';
ctx.lineWidth   = 3;
ctx.lineCap     = 'round';
ctx.lineJoin    = 'round';

// Eventos mouse
canvas.addEventListener('mousedown',  startDraw);
canvas.addEventListener('mousemove',  draw);
canvas.addEventListener('mouseup',    stopDraw);
canvas.addEventListener('mouseleave', stopDraw);

// Eventos touch (con passive:false para preventDefault)
canvas.addEventListener('touchstart', startDraw, { passive: false });
canvas.addEventListener('touchmove',  draw,      { passive: false });
canvas.addEventListener('touchend',   stopDraw);

// FILTRO MULTI-TOUCH (evita trazos por pinch-zoom)
function startDraw(e) {
    if (e.touches && e.touches.length > 1) return;  // <-- clave
    // ...
}
function draw(e) {
    if (e.touches && e.touches.length > 1) { drawing = false; return; }
    // ...
}
```

### 7.4 Flujo de firma en la pagina publica

```javascript
document.getElementById('btnFirmar').addEventListener('click', function() {
    // 1. Validar minimo pixeles dibujados
    var imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    var pixeles = 0;
    for (var i = 3; i < imgData.data.length; i += 4) {
        if (imgData.data[i] > 128) pixeles++;
    }
    if (pixeles < 100) {
        Swal.fire('Firma requerida', 'Por favor dibuje su firma en el recuadro.', 'warning');
        return;
    }

    // 2. Exportar canvas como PNG base64
    var firmaImagen = canvas.toDataURL('image/png');

    // 3. Preview con SweetAlert
    Swal.fire({
        title: 'Confirmar firma',
        html: '<p>Verifique que su firma es correcta:</p>'
            + '<img src="' + firmaImagen + '" style="max-width:100%;">',
        showCancelButton: true,
        confirmButtonText: 'Sí, firmar',
        cancelButtonText: 'Repetir',
        confirmButtonColor: '#28a745',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        // 4. Loading
        Swal.fire({ title: 'Guardando firma...', didOpen: () => Swal.showLoading() });

        // 5. POST al backend
        var formData = new FormData();
        formData.append('token',        '<?= esc($token) ?>');
        formData.append('firma_imagen', firmaImagen);

        fetch('/acta-visita/procesar-firma-remota', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(function(data) {
            if (data.success) {
                // 6. Exito: deshabilitar boton
                document.getElementById('btnFirmar').disabled = true;
                document.getElementById('btnFirmar').innerHTML =
                    '<i class="fas fa-check-circle me-2"></i>Firma registrada';
                document.getElementById('btnFirmar').style.background = '#6c757d';

                Swal.fire({
                    icon: 'success',
                    title: '¡Firma registrada!',
                    text: 'Gracias. Su firma ha sido guardada exitosamente en el acta.',
                    confirmButtonColor: '#28a745',
                    allowOutsideClick: false,
                });
            } else {
                Swal.fire('Error', data.error || 'No se pudo guardar la firma', 'error');
            }
        })
        .catch(async function() {
            // 7. OFFLINE: guardar en IndexedDB
            try {
                await OfflineQueue.add({
                    type: 'firma_acta_remota',
                    url: '/acta-visita/procesar-firma-remota',
                    id_asistencia: 0,
                    payload: {
                        token: '<?= esc($token) ?>',
                        firma_imagen: firmaImagen
                    },
                    meta: { tipo: '<?= esc($tipo) ?>' }
                });
                await OfflineQueue.requestSync();

                Swal.fire({
                    icon: 'info',
                    title: 'Guardado offline',
                    html: 'Sin conexion. La firma se guardo localmente y se enviara '
                        + 'automaticamente cuando vuelva el internet.<br><br>'
                        + '<button class="btn btn-warning btn-sm" '
                        + 'onclick="syncManualActaRemota()">'
                        + '<i class="fas fa-sync"></i> Reintentar ahora</button>',
                    confirmButtonColor: '#28a745',
                });
            } catch (dbErr) {
                Swal.fire('Error', 'No se pudo guardar la firma.', 'error');
            }
        });
    });
});
```

### 7.5 Sync offline

```javascript
// Sync manual (boton "Reintentar ahora")
window.syncManualActaRemota = async function() {
    Swal.fire({ title: 'Sincronizando...', didOpen: () => Swal.showLoading() });
    try {
        var result = await OfflineQueue.syncAll();
        if (result.synced > 0) {
            Swal.fire({ icon: 'success', title: 'Firma enviada',
                        text: 'Recargando...', timer: 2000 });
            setTimeout(() => window.location.reload(), 2000);
        } else {
            Swal.fire('Sin conexion', 'Aun no hay internet.', 'warning');
        }
    } catch (e) {
        Swal.fire('Error', 'No se pudo sincronizar.', 'error');
    }
};

// Auto-sync cuando vuelve internet
OfflineQueue.startOnlineListener(function(result) {
    if (result.synced > 0) {
        Swal.fire({ icon: 'success', title: 'Conexion restaurada',
                    html: 'Firma enviada automaticamente.<br>Recargando...',
                    timer: 2500 });
        setTimeout(() => window.location.reload(), 2500);
    }
});
```

---

## 8. PAGINA DE ERROR (firma_remota_error.php)

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace inválido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #bd9751 0%, #8b6914 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="text-center text-white p-4">
    <i class="fas fa-times-circle fa-4x mb-3" style="opacity:0.8;"></i>
    <h5><?= esc($mensaje ?? 'Enlace no válido') ?></h5>
    <p style="font-size:13px; opacity:0.85;">Solicite un nuevo enlace al consultor por WhatsApp.</p>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
```

**Mensajes de error posibles:**
| Caso | Mensaje |
|------|---------|
| Token no existe / ya usado | "Este enlace no es válido o ya fue usado." |
| Token expirado (>24h) | "Este enlace ha expirado. Pida uno nuevo al consultor." |
| Firma ya registrada | "Esta firma ya fue registrada." |

---

## 9. SEGURIDAD

| Capa | Mecanismo |
|------|-----------|
| **Generacion token** | `bin2hex(random_bytes(32))` — 64 chars hex, criptograficamente seguro |
| **Expiracion** | 24 horas desde creacion. Validado en GET y POST |
| **Un solo uso** | Al procesar firma, token se pone NULL → enlace muere inmediatamente |
| **Un token por acta** | Generar nuevo token sobreescribe el anterior |
| **Validacion tipo** | Solo acepta 'administrador', 'vigia', 'consultor' |
| **Firma ya existente** | Si el campo `firma_{tipo}` ya tiene valor, rechaza |
| **Multi-touch** | `e.touches.length > 1` descarta evento (evita trazos por zoom) |
| **Pixeles minimos** | Requiere >100 pixeles oscuros (evita firmas accidentales) |
| **Preview obligatorio** | SweetAlert muestra firma antes de confirmar envio |
| **Escape de salida** | `esc()` en todos los valores PHP renderizados en HTML |

---

## 10. OFFLINE (IndexedDB)

### Dependencia: `public/js/offline_queue.js`

```javascript
// Estructura de la cola
DB_NAME: 'inspecciones_offline'
STORE:   'pending_signatures'

// Entrada para firma remota
{
    type: 'firma_acta_remota',
    url: '/acta-visita/procesar-firma-remota',
    id_asistencia: 0,
    payload: { token: '{token}', firma_imagen: 'data:image/png;base64,...' },
    meta: { tipo: 'administrador' },
    created_at: new Date().toISOString()
}

// Triggers de sincronizacion:
// 1. Background Sync API (si SyncManager disponible)
// 2. Evento 'online' del window
// 3. Boton manual "Reintentar ahora"
```

**Riesgo offline:** Si el token expira (24h) antes de que el firmante recupere conexion, la firma quedara en IndexedDB pero el backend la rechazara con "Enlace expirado". El firmante debera pedir nuevo enlace.

---

## 11. ORDEN DE IMPLEMENTACION

```
1. BD: Ejecutar migrate_acta_firma_remota.php (3 columnas en tbl_acta_visita)

2. Rutas: Agregar 3 rutas en Routes.php
   - POST /acta-visita/generar-token-firma/(:num)     [autenticada]
   - GET  /acta-visita/firmar-remoto/(:any)            [publica]
   - POST /acta-visita/procesar-firma-remota           [publica]

3. Backend: 3 metodos en el controlador
   - generarTokenFirma($id)
   - firmarRemoto($token)
   - procesarFirmaRemota()

4. Vistas:
   - firma_remota.php (pagina publica standalone)
   - firma_remota_error.php (pagina error)

5. JS en firma.php: Event listener '.btn-whatsapp-firma'

6. JS: offline_queue.js (si no existe ya)

7. Directorio: uploads/inspecciones/firmas/ (permisos 755/775)

8. Test:
   - Generar token → verificar en BD
   - Abrir enlace → ver resumen + canvas
   - Firmar → verificar PNG en disco + campo en BD + token limpiado
   - Probar enlace expirado → ver error
   - Probar enlace ya usado → ver error
   - Probar offline → IndexedDB → reconectar → sync
```

---

> **Nota:** Este documento cubre exclusivamente el flujo de firma remota por WhatsApp.
> Para el modulo completo de Acta de Visita, ver `docs/REPLICACION-ACTA-VISITA.md`.
