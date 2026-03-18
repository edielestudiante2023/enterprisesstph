<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Agenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { background:#f8f9fa; }
        .badge-activo    { background:#198754; color:#fff; }
        .badge-detenido  { background:#dc3545; color:#fff; }
        .badge-pendiente { background:#6c757d; color:#fff; }
        .btn-ia {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border: none;
            font-size: 0.82rem;
        }
        .btn-ia:hover { opacity:.88; color:#fff; }
        .btn-ia:disabled { opacity:.5; }
        .ia-spinner { display:none; }
        .btn-ia.loading .ia-spinner { display:inline-block; }
        .btn-ia.loading .ia-icon    { display:none; }
    </style>
</head>
<body>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Seguimiento de Agenda</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="fas fa-plus me-1"></i>Agregar Cliente
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Cliente</th>
                        <th>Asunto</th>
                        <th>Consultor</th>
                        <th class="text-center">Estado</th>
                        <th>Motivo detención</th>
                        <th>Creado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($seguimientos)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay clientes en seguimiento.</td></tr>
                <?php else: ?>
                    <?php foreach ($seguimientos as $s): ?>
                    <tr>
                        <td>
                            <strong><?= esc($s['nombre_cliente']) ?></strong><br>
                            <small class="text-muted"><?= esc($s['nit_cliente']) ?></small>
                        </td>
                        <td><small><?= esc($s['asunto']) ?></small></td>
                        <td><?= esc($s['consultor']) ?></td>
                        <td class="text-center">
                            <?php if ($s['activo'] && !$s['detenido']): ?>
                                <span class="badge badge-activo">Activo</span>
                            <?php elseif ($s['detenido']): ?>
                                <span class="badge badge-detenido">Detenido</span>
                            <?php else: ?>
                                <span class="badge badge-pendiente">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= esc($s['motivo_detencion'] ?? '—') ?></small></td>
                        <td><small><?= date('d/m/Y', strtotime($s['created_at'])) ?></small></td>
                        <td class="text-center">
                            <!-- Historial -->
                            <button class="btn btn-sm btn-outline-info me-1"
                                    onclick="verHistorial(<?= $s['id'] ?>, '<?= esc($s['nombre_cliente']) ?>')"
                                    title="Ver historial de envíos">
                                <i class="fas fa-history"></i>
                            </button>
                            <!-- Detener / Reactivar -->
                            <?php if ($s['activo'] && !$s['detenido']): ?>
                                <button class="btn btn-sm btn-outline-warning me-1"
                                        onclick="detener(<?= $s['id'] ?>, '<?= esc($s['nombre_cliente']) ?>')"
                                        title="Detener seguimiento">
                                    <i class="fas fa-stop-circle"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-success me-1"
                                        onclick="reactivar(<?= $s['id'] ?>)"
                                        title="Reactivar seguimiento">
                                    <i class="fas fa-play-circle"></i>
                                </button>
                            <?php endif; ?>
                            <!-- Eliminar -->
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="eliminar(<?= $s['id'] ?>, '<?= esc($s['nombre_cliente']) ?>')"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Agregar Cliente a Seguimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Cliente *</label>
                    <select class="form-select" id="sel_cliente" required>
                        <option value="">Seleccione un cliente...</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>"><?= esc($c['nombre_cliente']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Botón IA -->
                <div class="mb-3">
                    <button type="button" class="btn btn-ia btn-sm px-3" id="btnGenerar" onclick="generarConIA()" disabled>
                        <i class="fas fa-magic ia-icon me-1"></i>
                        <span class="spinner-border spinner-border-sm ia-spinner me-1" role="status"></span>
                        Generar texto con IA
                    </button>
                    <small class="text-muted ms-2">Selecciona un cliente primero</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Asunto del correo *</label>
                    <input type="text" class="form-control" id="sel_asunto"
                           placeholder="Ej: Seguimiento programación de visita mensual">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mensaje *</label>
                    <textarea class="form-control" id="sel_mensaje" rows="5"
                              placeholder="Texto del cuerpo del correo..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Opciones de fecha (una por línea)</label>
                    <textarea class="form-control" id="sel_opciones" rows="4"
                              placeholder="Ej:&#10;Viernes 20 de marzo – 5:00 p. m.&#10;Lunes 24 de marzo – 5:00 p. m."></textarea>
                    <small class="text-muted">Escribe cada opción en una línea. Pueden dejarse en blanco si no aplica.</small>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Consultor</label>
                        <input type="text" class="form-control" id="sel_consultor" value="Edison Cuervo">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cargo</label>
                        <input type="text" class="form-control" id="sel_cargo" value="Consultor SST">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarSeguimiento()">
                    <i class="fas fa-save me-1"></i>Activar Seguimiento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Historial de Envíos — <span id="historialNombre"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historialBody">
                <div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const BASE = '<?= base_url() ?>';
const CSRF_NAME  = '<?= csrf_token() ?>';
const CSRF_VALUE = '<?= csrf_hash() ?>';

function csrfBody(extra = {}) {
    return new URLSearchParams({ [CSRF_NAME]: CSRF_VALUE, ...extra });
}

// ── Select2 ──────────────────────────────────────────────────────────────────
document.getElementById('modalAgregar').addEventListener('shown.bs.modal', function () {
    $('#sel_cliente').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalAgregar'),
        placeholder: 'Buscar o seleccionar cliente...',
        allowClear: true,
        width: '100%'
    }).off('change.seg').on('change.seg', function () {
        const tiene = !!$(this).val();
        const btn   = document.getElementById('btnGenerar');
        btn.disabled = !tiene;
        btn.nextElementSibling.textContent = tiene ? 'Haz clic para generar asunto y mensaje automáticamente' : 'Selecciona un cliente primero';
    });
});

document.getElementById('modalAgregar').addEventListener('hidden.bs.modal', function () {
    if ($('#sel_cliente').hasClass('select2-hidden-accessible')) {
        $('#sel_cliente').select2('destroy');
    }
    document.getElementById('btnGenerar').disabled = true;
});

// ── Generar con IA ───────────────────────────────────────────────────────────
async function generarConIA() {
    const clienteOpt = document.getElementById('sel_cliente');
    const nombreCliente = clienteOpt.options[clienteOpt.selectedIndex]?.text ?? '';
    if (!nombreCliente || nombreCliente === 'Seleccione un cliente...') {
        Swal.fire('Atención', 'Selecciona un cliente primero.', 'warning');
        return;
    }

    const btn = document.getElementById('btnGenerar');
    btn.classList.add('loading');
    btn.disabled = true;

    try {
        const res  = await fetch(BASE + 'seguimiento-agenda/generar-texto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: csrfBody({ nombre_cliente: nombreCliente })
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('sel_asunto').value  = data.data.asunto  ?? '';
            document.getElementById('sel_mensaje').value = data.data.mensaje  ?? '';
            if (Array.isArray(data.data.opciones_fechas)) {
                document.getElementById('sel_opciones').value = data.data.opciones_fechas.join('\n');
            }
        } else {
            Swal.fire('Error IA', data.message ?? 'No se pudo generar el texto.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Fallo al contactar el servicio de IA.', 'error');
    } finally {
        btn.classList.remove('loading');
        btn.disabled = false;
    }
}

// ── CRUD ─────────────────────────────────────────────────────────────────────
async function guardarSeguimiento() {
    const id_cliente      = document.getElementById('sel_cliente').value;
    const asunto          = document.getElementById('sel_asunto').value.trim();
    const mensaje         = document.getElementById('sel_mensaje').value.trim();
    const opcionesRaw     = document.getElementById('sel_opciones').value.trim();
    const consultor       = document.getElementById('sel_consultor').value.trim();
    const cargo_consultor = document.getElementById('sel_cargo').value.trim();

    if (!id_cliente || !asunto || !mensaje) {
        Swal.fire('Campos requeridos', 'Cliente, asunto y mensaje son obligatorios.', 'warning');
        return;
    }

    const opciones = opcionesRaw ? opcionesRaw.split('\n').map(l => l.trim()).filter(Boolean) : [];

    const res = await fetch(BASE + 'seguimiento-agenda/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: csrfBody({ id_cliente, asunto, mensaje, opciones_fechas: JSON.stringify(opciones), consultor, cargo_consultor })
    });
    const data = await res.json();

    if (data.success) {
        Swal.fire('Listo', data.message, 'success').then(() => location.reload());
    } else {
        Swal.fire('Error', data.message, 'error');
    }
}

async function detener(id, nombre) {
    const { value: motivo } = await Swal.fire({
        title: 'Detener seguimiento',
        text: nombre,
        input: 'text',
        inputPlaceholder: 'Motivo (ej: Cliente respondió por WhatsApp)',
        inputValue: 'Cliente respondió',
        showCancelButton: true,
        confirmButtonText: 'Detener',
        confirmButtonColor: '#dc3545'
    });
    if (motivo === undefined) return;

    const res = await fetch(BASE + 'seguimiento-agenda/detener/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: csrfBody({ motivo })
    });
    const data = await res.json();
    if (data.success) location.reload();
}

async function reactivar(id) {
    const conf = await Swal.fire({ title: '¿Reactivar seguimiento?', icon: 'question', showCancelButton: true });
    if (!conf.isConfirmed) return;
    const res = await fetch(BASE + 'seguimiento-agenda/reactivar/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: csrfBody()
    });
    const data = await res.json();
    if (data.success) location.reload();
}

async function eliminar(id, nombre) {
    const conf = await Swal.fire({ title: '¿Eliminar?', text: nombre, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545' });
    if (!conf.isConfirmed) return;
    const res = await fetch(BASE + 'seguimiento-agenda/destroy/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: csrfBody()
    });
    const data = await res.json();
    if (data.success) location.reload();
}

async function verHistorial(id, nombre) {
    document.getElementById('historialNombre').textContent = nombre;
    document.getElementById('historialBody').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
    new bootstrap.Modal(document.getElementById('modalHistorial')).show();

    const res  = await fetch(BASE + 'seguimiento-agenda/historial/' + id);
    const data = await res.json();

    if (!data.data || data.data.length === 0) {
        document.getElementById('historialBody').innerHTML = '<p class="text-muted text-center py-3">Sin envíos registrados aún.</p>';
        return;
    }

    const badges = { ENVIADO: 'success', ERROR: 'danger', DETENIDO: 'warning' };
    const rows = data.data.map(r => `
        <tr>
            <td>${r.fecha_envio}</td>
            <td><span class="badge bg-${badges[r.estado] || 'secondary'}">${r.estado}</span></td>
            <td><small>${r.detalle || '—'}</small></td>
        </tr>`).join('');

    document.getElementById('historialBody').innerHTML = `
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Fecha</th><th>Estado</th><th>Detalle</th></tr></thead>
            <tbody>${rows}</tbody>
        </table>`;
}
</script>
</body>
</html>
