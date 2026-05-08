<?php
$isEdit = !empty($acta);
$ctx = $contexto ?? 'miembro';
$baseUrl = $ctx === 'consultor' ? 'inspecciones/acta-capacitacion' : 'miembro/acta-capacitacion';
$action = $isEdit
    ? site_url($baseUrl . '/update/' . $acta['id'])
    : site_url($baseUrl . '/store');
$tokenUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/generar-token-firma/'
    : 'miembro/acta-capacitacion/generar-token-firma/';
$saveAsistUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/save/'
    : 'miembro/acta-capacitacion/asistente/save/';
$emailUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/enviar-email/'
    : 'miembro/acta-capacitacion/asistente/enviar-email/';
$deleteAsistUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/delete/'
    : 'miembro/acta-capacitacion/asistente/delete/';
$statusUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistentes-status/'
    : 'miembro/acta-capacitacion/asistentes-status/';
$tokenInscripcionUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/generar-token-inscripcion/'
    : 'miembro/acta-capacitacion/generar-token-inscripcion/';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="actaCapForm" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="accordion mt-2" id="accCap">
            <!-- Datos Generales -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $isEdit ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse <?= $isEdit ? '' : 'show' ?>" data-bs-parent="#accCap">
                    <div class="accordion-body">
                        <?php if ($ctx === 'miembro'): ?>
                        <input type="hidden" name="id_cliente" value="<?= $idCliente ?? ($acta['id_cliente'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente'] ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Registrado por</label>
                            <input type="text" class="form-control" value="<?= esc($miembro['nombre_completo'] ?? '') ?>" readonly>
                        </div>
                        <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectClienteCap" class="form-select" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Tema *</label>
                            <input type="text" name="tema" class="form-control"
                                value="<?= esc($acta['tema'] ?? '') ?>" placeholder="Ej: Riesgo biomecánico" required>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Fecha *</label>
                                <input type="date" name="fecha_capacitacion" class="form-control"
                                    value="<?= $acta['fecha_capacitacion'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora inicio</label>
                                <input type="time" name="hora_inicio" class="form-control"
                                    value="<?= $acta['hora_inicio'] ?? '' ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora fin</label>
                                <input type="time" name="hora_fin" class="form-control"
                                    value="<?= $acta['hora_fin'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Dictada por</label>
                                <input type="text" name="dictada_por" class="form-control"
                                    value="<?= esc($acta['dictada_por'] ?? '') ?>" placeholder="Ej: ARL, Consultor SST...">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Modalidad</label>
                                <select name="modalidad" class="form-select">
                                    <?php foreach (['virtual','presencial','mixta'] as $op): ?>
                                        <option value="<?= $op ?>" <?= ($acta['modalidad'] ?? 'virtual') === $op ? 'selected' : '' ?>><?= ucfirst($op) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Entidad capacitadora</label>
                            <input type="text" name="entidad_capacitadora" class="form-control"
                                value="<?= esc($acta['entidad_capacitadora'] ?? '') ?>" placeholder="Consultor SST">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre del capacitador</label>
                            <input type="text" name="nombre_capacitador" class="form-control"
                                value="<?= esc($acta['nombre_capacitador'] ?? '') ?>">
                        </div>
                        <input type="hidden" name="enlace_grabacion" value="<?= esc($acta['enlace_grabacion'] ?? '') ?>">
                        <input type="hidden" name="objetivos" value="<?= esc($acta['objetivos'] ?? '') ?>">
                        <input type="hidden" name="contenido" value="<?= esc($acta['contenido'] ?? '') ?>">
                        <div class="mb-0">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"><?= esc($acta['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cronograma + Asistencia (NUEVO MODELO N:M, mismo bloque para CREATE y EDIT) -->
            <?php if ($ctx === 'consultor'): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $isEdit ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#secCronog">
                        Capacitaciones · Asistencia
                    </button>
                </h2>
                <div id="secCronog" class="accordion-collapse collapse <?= $isEdit ? 'show' : '' ?>" data-bs-parent="#accCap">
                    <div class="accordion-body">

                        <!-- Multi-checkbox unificado: 1 acta puede vincular N capacitaciones del cronograma -->
                        <div class="mb-3">
                            <label class="form-label">
                                Capacitaciones del cronograma <small class="text-muted">(marca todas las que se dictan)</small>
                            </label>
                            <div id="cronogramasCheckboxesContainer" class="border rounded p-2" style="max-height:240px; overflow-y:auto; background:#fafafa;">
                                <p class="text-muted mb-0" style="font-size:13px;"><i class="fas fa-info-circle"></i> Seleccione un cliente para ver sus capacitaciones programadas.</p>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Al finalizar el acta, se generará <strong>1 PDF por cada capacitación marcada</strong>.
                                El objetivo se genera automáticamente con IA según cada tema.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Asistentes esperados <small class="text-muted">(digitado, global)</small></label>
                                <input type="number" name="numero_programados" class="form-control"
                                    value="<?= esc($acta['numero_programados'] ?? '') ?>" placeholder="0" min="0">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Asistentes reales <small class="text-muted">(firmados QR)</small></label>
                                <input type="number" id="asistentesRealesAuto" class="form-control"
                                    value="<?= count(array_filter($asistentes ?? [], fn($a) => !empty($a['firma_path']))) ?>" disabled
                                    style="background:#e9ecef; cursor:not-allowed;">
                                <small class="text-muted">Se autocalcula del conteo de firmas registradas.</small>
                            </div>
                        </div>

                        <!-- Evaluaciones registradas del día (read-only) -->
                        <div class="mb-3 p-2 rounded" style="background:#ecfdf5; border:1px solid #a7f3d0;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size:13px; font-weight:600; color:#065f46;">
                                    <i class="fas fa-clipboard-check"></i> Evaluaciones registradas (cliente + fecha)
                                </span>
                                <button type="button" id="btnRefreshEvalDia" class="btn btn-sm btn-outline-success" style="font-size:11px; padding:2px 8px;">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                            <div id="evalDiaContainer" style="font-size:12px;">
                                <span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</span>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 mb-0" style="font-size:12px;">
                            <i class="fas fa-magic"></i>
                            Al <strong>Finalizar</strong>: por cada capacitación marcada se calcula automáticamente
                            el <strong>objetivo</strong>, <strong>personas evaluadas</strong> y <strong>promedio</strong>
                            con IA (matching cliente + tema). Se generan PDFs y reportes individuales.
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Registro fotográfico (solo modo consultor) -->
            <?php if ($ctx === 'consultor'): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secFotos">
                        Registro fotográfico
                    </button>
                </h2>
                <div id="secFotos" class="accordion-collapse collapse" data-bs-parent="#accCap">
                    <div class="accordion-body">
                        <small class="text-muted d-block mb-2">
                            Las fotos se muestran en la vista web y en cada PDF principal de las capacitaciones marcadas
                            (no aparecen en el PDF de Responsabilidades SST).
                        </small>
                        <?php
                        $fotoFields = [
                            'foto_capacitacion' => 'Foto capacitación',
                            'foto_otros_1'      => 'Foto otros 1',
                            'foto_otros_2'      => 'Foto otros 2',
                        ];
                        foreach ($fotoFields as $fname => $flabel):
                            $hasFoto = $isEdit && !empty($acta[$fname]);
                            $btnText = $hasFoto ? 'Cambiar imagen' : 'Subir imagen';
                        ?>
                        <div class="mb-3" data-foto-field="<?= $fname ?>" style="display:flex;flex-direction:column;align-items:stretch;gap:8px;">
                            <label class="form-label" style="display:block;margin:0;font-size:13px;font-weight:600;"><?= $flabel ?></label>
                            <input type="hidden" name="<?= $fname ?>__delete" value="0" data-foto-delete-flag>
                            <?php if ($hasFoto): ?>
                                <div class="foto-thumb-wrap" style="position:relative;display:block;width:100%;">
                                    <img src="<?= base_url(esc($acta[$fname])) ?>" alt="<?= esc($flabel) ?>"
                                         style="display:block;width:100%;max-height:220px;object-fit:contain;background:#fff;border:2px solid #bd9751;border-radius:8px;padding:4px;">
                                    <button type="button" class="foto-delete-btn" aria-label="Eliminar imagen"
                                            style="position:absolute;top:8px;right:8px;width:36px;height:36px;border-radius:50%;border:2px solid #d33;background:#fff;color:#d33;font-size:18px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.18);padding:0;">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <label style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 18px;background:#bd9751;color:#fff;border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;border:none;width:100%;">
                                <i class="fas fa-image" aria-hidden="true"></i>
                                <span><?= $btnText ?></span>
                                <input type="file" name="<?= $fname ?>" accept="image/*" data-label="<?= esc($flabel) ?>"
                                       style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;">
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <script>
                        (function(){
                            document.querySelectorAll('.foto-delete-btn').forEach(function(btn){
                                btn.addEventListener('click', function(){
                                    if (!confirm('¿Eliminar esta imagen?')) return;
                                    var wrap = btn.closest('[data-foto-field]');
                                    if (!wrap) return;
                                    var thumb = wrap.querySelector('.foto-thumb-wrap');
                                    if (thumb) thumb.style.display = 'none';
                                    var flag = wrap.querySelector('[data-foto-delete-flag]');
                                    if (flag) flag.value = '1';
                                });
                            });
                        })();
                        </script>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Asistentes -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secAsist">
                        Asistentes (<span id="countAsist"><?= count($asistentes ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secAsist" class="accordion-collapse collapse" data-bs-parent="#accCap">
                    <div class="accordion-body">
                        <?php if (!$isEdit): ?>
                            <div class="alert alert-info py-2" style="font-size:13px;">
                                <i class="fas fa-info-circle"></i>
                                Guarda primero el acta como borrador. Después podrás agregar asistentes y enviar enlaces de firma por WhatsApp.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2" style="font-size:13px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Para que el botón <strong>Enviar WhatsApp</strong> funcione, primero <strong>guarda</strong> el asistente recién agregado.
                            </div>
                        <?php endif; ?>

                        <?php if ($isEdit): ?>
                        <div class="mb-3 p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa;">
                            <div class="d-flex justify-content-between align-items-center" style="gap:10px;">
                                <div style="font-size:13px;line-height:1.4;">
                                    <strong style="color:#9a3412;">
                                        <i class="fas fa-qrcode"></i> Auto-inscripcion via QR
                                    </strong>
                                    <div class="text-muted" style="font-size:12px;">
                                        Cada asistente escanea el QR con su celular y llena sus datos. Mas rapido para grupos grandes.
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-warning" id="btnMostrarQR" style="white-space:nowrap;font-weight:600;">
                                    <i class="fas fa-qrcode"></i> Mostrar QR
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php
                        $totalAsist = count($asistentes ?? []);
                        $firmadosAsist = 0;
                        foreach (($asistentes ?? []) as $__a) { if (!empty($__a['firma_path'])) $firmadosAsist++; }
                        $pctAsist = $totalAsist > 0 ? (int) round($firmadosAsist * 100 / $totalAsist) : 0;
                        ?>
                        <?php if ($isEdit && $totalAsist > 0): ?>
                        <div id="firmasProgreso" class="mb-3 p-2 rounded" style="background:#f8f9fa;border:1px solid #e5e7eb;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size:13px;font-weight:600;color:#111827;">
                                    <i class="fas fa-signature text-success"></i>
                                    Firmas: <span id="firmasFirmados"><?= $firmadosAsist ?></span> de <span id="firmasTotal"><?= $totalAsist ?></span>
                                    (<span id="firmasPct"><?= $pctAsist ?></span>%)
                                </span>
                                <button type="button" id="btnRefreshFirmas" class="btn btn-sm btn-outline-primary" style="font-size:12px;padding:3px 10px;">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                            <div style="background:#e5e7eb;border-radius:999px;height:8px;overflow:hidden;">
                                <div id="firmasBar" style="background:#10b981;height:100%;width:<?= $pctAsist ?>%;transition:width 0.3s;"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div id="asistentesContainer">
                            <?php if (!empty($asistentes)): ?>
                                <?php foreach ($asistentes as $i => $a): ?>
                                <div class="card mb-3 asistente-row" data-asistente-id="<?= $a['id'] ?>">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="asistente_id[]" value="<?= $a['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Asistente #<span class="asist-num"><?= $i + 1 ?></span>
                                                <span class="asist-status-badge">
                                                <?php if (!empty($a['firma_path'])): ?>
                                                    <span class="badge bg-success" style="font-size:10px;"><i class="fas fa-check"></i> Firmado</span>
                                                <?php elseif (!empty($a['token_firma'])): ?>
                                                    <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="fas fa-clock"></i> Enlace enviado</span>
                                                <?php endif; ?>
                                                </span>
                                            </strong>
                                            <?php if (!empty($a['firmado_at'])): ?>
                                            <div class="asist-firmado-at text-muted" style="font-size:11px;margin-top:2px;">
                                                <i class="fas fa-clock"></i> Firmó el <?= date('d/m/Y H:i', strtotime($a['firmado_at'])) ?>
                                            </div>
                                            <?php else: ?>
                                            <div class="asist-firmado-at text-muted" style="font-size:11px;margin-top:2px;display:none;"></div>
                                            <?php endif; ?>
                                            <?php if (empty($a['firma_path'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asist" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php else: ?>
                                            <span class="text-muted small" title="No se puede quitar: ya firmo">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" name="asistente_nombre[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['nombre_completo']) ?>" placeholder="Nombre completo *" required>
                                            </div>
                                            <div class="col-4">
                                                <select name="asistente_tipo_doc[]" class="form-select form-select-sm">
                                                    <?php foreach (['CC','CE','PA','TI','NIT'] as $td): ?>
                                                        <option value="<?= $td ?>" <?= ($a['tipo_documento'] ?? 'CC') === $td ? 'selected' : '' ?>><?= $td ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-8">
                                                <input type="text" name="asistente_num_doc[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['numero_documento'] ?? '') ?>" placeholder="Número documento">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="asistente_cargo[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['cargo'] ?? '') ?>" placeholder="Cargo">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="asistente_area[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['area_dependencia'] ?? '') ?>" placeholder="Contratista">
                                            </div>
                                            <div class="col-12">
                                                <input type="email" name="asistente_email[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['email'] ?? '') ?>" placeholder="Email (opcional)">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="asistente_celular[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['celular'] ?? '') ?>" placeholder="Celular (opcional)">
                                            </div>
                                        </div>
                                        <?php if (empty($a['firma_path'])): ?>
                                        <div class="mt-2 d-grid gap-1">
                                            <button type="button" class="btn btn-sm btn-primary btn-save-asist"
                                                data-asistente-id="<?= $a['id'] ?>">
                                                <i class="fas fa-save"></i> Guardar este asistente
                                            </button>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-copiar-firma flex-fill"
                                                    data-asistente-id="<?= $a['id'] ?>"
                                                    data-nombre="<?= esc($a['nombre_completo']) ?>"
                                                    title="Copiar enlace">
                                                    <i class="fas fa-copy"></i> Copiar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-email-firma flex-fill"
                                                    data-asistente-id="<?= $a['id'] ?>"
                                                    data-nombre="<?= esc($a['nombre_completo']) ?>"
                                                    data-email="<?= esc($a['email'] ?? '') ?>"
                                                    title="Enviar enlace al email">
                                                    <i class="fas fa-envelope"></i> Email
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success btn-whatsapp-firma flex-fill"
                                                    data-asistente-id="<?= $a['id'] ?>"
                                                    data-nombre="<?= esc($a['nombre_completo']) ?>"
                                                    title="Compartir por WhatsApp">
                                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                                </button>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddAsist">
                            <i class="fas fa-plus"></i> Agregar asistente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <?php if ($isEdit): ?>
            <button type="button" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar y generar PDF
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var tokenUrlBase = '<?= site_url($tokenUrlBase) ?>';

    <?php if ($ctx === 'consultor'): ?>
    var clienteIdSel = '<?= $idCliente ?? ($acta['id_cliente'] ?? '') ?>';
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: '<?= site_url('inspecciones/api/clientes') ?>',
            dataType: 'json',
            success: function(data) {
                var sel = document.getElementById('selectClienteCap');
                if (!sel) return;
                sel.innerHTML = '<option value="">Seleccionar cliente...</option>';
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id_cliente;
                    opt.textContent = c.nombre_cliente;
                    if (clienteIdSel && String(c.id_cliente) === String(clienteIdSel)) opt.selected = true;
                    sel.appendChild(opt);
                });
                if ($('#selectClienteCap').select2) {
                    $('#selectClienteCap').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
                }
            }
        });
    }
    <?php endif; ?>

    function updateAsist() {
        var rows = document.querySelectorAll('.asistente-row');
        document.getElementById('countAsist').textContent = rows.length;
        rows.forEach((row, i) => {
            var num = row.querySelector('.asist-num');
            if (num) num.textContent = i + 1;
        });
    }

    var deleteAsistUrlBase = '<?= site_url($deleteAsistUrlBase) ?>';
    var statusUrlBase = '<?= site_url($statusUrlBase) ?>';

    function fmtFechaFirma(iso) {
        if (!iso) return '';
        try {
            var d = new Date(iso.replace(' ', 'T'));
            if (isNaN(d.getTime())) return '';
            var dd = String(d.getDate()).padStart(2, '0');
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var yy = d.getFullYear();
            var hh = String(d.getHours()).padStart(2, '0');
            var mi = String(d.getMinutes()).padStart(2, '0');
            return dd + '/' + mm + '/' + yy + ' ' + hh + ':' + mi;
        } catch (e) { return ''; }
    }

    function aplicarEstadoAsistentes(data) {
        if (!data || !data.asistentes) return;

        // Actualizar barra de progreso
        var elFirmados = document.getElementById('firmasFirmados');
        var elTotal    = document.getElementById('firmasTotal');
        var elPct      = document.getElementById('firmasPct');
        var elBar      = document.getElementById('firmasBar');
        if (elFirmados) elFirmados.textContent = data.firmados;
        if (elTotal)    elTotal.textContent    = data.total;
        if (elPct)      elPct.textContent      = data.pct;
        if (elBar)      elBar.style.width      = data.pct + '%';

        // Actualizar cada fila
        var byId = {};
        data.asistentes.forEach(function(a) { byId[String(a.id)] = a; });

        document.querySelectorAll('.asistente-row').forEach(function(row) {
            var hidden = row.querySelector('input[name="asistente_id[]"]');
            var id = hidden ? hidden.value : '';
            if (!id || !byId[id]) return;
            var info = byId[id];

            // Badge de estado
            var badgeWrap = row.querySelector('.asist-status-badge');
            if (badgeWrap) {
                if (info.firmado) {
                    badgeWrap.innerHTML = '<span class="badge bg-success" style="font-size:10px;"><i class="fas fa-check"></i> Firmado</span>';
                } else if (info.enlace_enviado) {
                    badgeWrap.innerHTML = '<span class="badge bg-warning text-dark" style="font-size:10px;"><i class="fas fa-clock"></i> Enlace enviado</span>';
                } else {
                    badgeWrap.innerHTML = '';
                }
            }

            // Timestamp de firma
            var stampEl = row.querySelector('.asist-firmado-at');
            if (stampEl) {
                if (info.firmado && info.firmado_at) {
                    stampEl.style.display = '';
                    stampEl.innerHTML = '<i class="fas fa-clock"></i> Firmó el ' + fmtFechaFirma(info.firmado_at);
                } else {
                    stampEl.style.display = 'none';
                    stampEl.innerHTML = '';
                }
            }

            // Si firmo: ocultar boton X y botones de envio (Copiar/Email/WhatsApp)
            if (info.firmado) {
                var btnRm = row.querySelector('.btn-remove-asist');
                if (btnRm) {
                    var lock = document.createElement('span');
                    lock.className = 'text-muted small';
                    lock.title = 'No se puede quitar: ya firmo';
                    lock.innerHTML = '<i class="fas fa-lock"></i>';
                    btnRm.replaceWith(lock);
                }
                var btnsEnvio = row.querySelectorAll('.btn-copiar-firma, .btn-email-firma, .btn-whatsapp-firma, .btn-save-asist');
                btnsEnvio.forEach(function(b) { b.style.display = 'none'; });
            }
        });
    }

    // ===== QR de auto-inscripcion =====
    var tokenInscripcionUrlBase = '<?= site_url($tokenInscripcionUrlBase) ?>';

    function mostrarQrModal(data) {
        Swal.fire({
            title: '<i class="fas fa-qrcode" style="color:#bd9751;"></i> QR de auto-inscripcion',
            html:
                '<div style="text-align:center;">'
                + '<div style="background:white;padding:14px;border:2px solid #e5e7eb;border-radius:12px;display:inline-block;max-width:320px;width:100%;">'
                +   data.qr_svg
                + '</div>'
                + '<p style="font-size:13px;color:#6b7280;margin-top:14px;line-height:1.4;">'
                +   'Acerca el celular de cada asistente para que escanee.<br>'
                +   'Llena sus datos y firma desde su propio celular.'
                + '</p>'
                + '<div style="background:#f3f4f6;padding:10px;border-radius:8px;margin-top:10px;font-size:11px;word-break:break-all;color:#374151;">'
                +   data.url
                + '</div>'
                + '<div class="d-flex gap-2 mt-3">'
                +   '<button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="btnCopiarQrUrl"><i class="fas fa-copy"></i> Copiar enlace</button>'
                +   '<button type="button" class="btn btn-sm btn-outline-danger flex-fill" id="btnRotarQrUrl"><i class="fas fa-redo"></i> Generar nuevo</button>'
                + '</div>'
                + '</div>',
            width: 460,
            showConfirmButton: true,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#bd9751',
            didOpen: function() {
                var btnCopiar = document.getElementById('btnCopiarQrUrl');
                if (btnCopiar) {
                    btnCopiar.addEventListener('click', function() {
                        navigator.clipboard.writeText(data.url).then(function() {
                            btnCopiar.innerHTML = '<i class="fas fa-check"></i> Copiado';
                            setTimeout(function() { btnCopiar.innerHTML = '<i class="fas fa-copy"></i> Copiar enlace'; }, 1500);
                        });
                    });
                }
                var btnRotar = document.getElementById('btnRotarQrUrl');
                if (btnRotar) {
                    btnRotar.addEventListener('click', function() {
                        Swal.fire({
                            icon: 'warning',
                            title: '¿Generar un nuevo QR?',
                            html: 'El QR actual <strong>dejara de funcionar</strong>. Cualquier persona que tenga el enlace anterior no podra inscribirse.',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Si, regenerar',
                            cancelButtonText: 'Cancelar',
                            reverseButtons: true,
                        }).then(function(res) {
                            if (!res.isConfirmed) return;
                            generarQR(true);
                        });
                    });
                }
            }
        });
    }

    function generarQR(regenerar) {
        if (!idActaActual) {
            Swal.fire('Guarda primero el acta', 'Primero guarda el acta como borrador para generar el QR.', 'info');
            return;
        }
        Swal.fire({ title: 'Generando QR...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

        var fd = new FormData();
        fd.append(csrfName, csrfHash);
        if (regenerar) fd.append('regenerar', '1');

        fetch(tokenInscripcionUrlBase + idActaActual, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                Swal.fire('Error', data.error || 'No se pudo generar el QR', 'error');
                return;
            }
            mostrarQrModal(data);
        })
        .catch(function() {
            Swal.fire('Error de conexion', 'No se pudo generar el QR.', 'error');
        });
    }

    var btnQR = document.getElementById('btnMostrarQR');
    if (btnQR) {
        btnQR.addEventListener('click', function() { generarQR(false); });
    }

    var btnRefresh = document.getElementById('btnRefreshFirmas');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            if (!idActaActual) return;
            var origHtml = btnRefresh.innerHTML;
            btnRefresh.disabled = true;
            btnRefresh.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';

            fetch(statusUrlBase + idActaActual, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btnRefresh.disabled = false;
                btnRefresh.innerHTML = origHtml;
                if (!data.success) {
                    Swal.fire('No se pudo actualizar', data.error || 'Intenta de nuevo', 'error');
                    return;
                }
                aplicarEstadoAsistentes(data);
                Swal.fire({ icon: 'success', title: 'Estado actualizado', toast: true, position: 'top', showConfirmButton: false, timer: 1200 });
            })
            .catch(function() {
                btnRefresh.disabled = false;
                btnRefresh.innerHTML = origHtml;
                Swal.fire('Error de conexión', 'No se pudo actualizar el estado.', 'error');
            });
        });
    }


    document.addEventListener('click', function(e) {
        var btnRm = e.target.closest('.btn-remove-asist');
        if (!btnRm) return;

        var row = btnRm.closest('.asistente-row');
        var hiddenId = row.querySelector('input[name="asistente_id[]"]');
        var idAsistente = (hiddenId && hiddenId.value) ? hiddenId.value : '';
        var nombre = (row.querySelector('input[name="asistente_nombre[]"]') || {}).value || 'sin nombre';

        // Si la fila aun no fue guardada en BD (no tiene id), solo quitar del DOM
        if (!idAsistente) {
            row.remove();
            updateAsist();
            return;
        }

        // Confirmar antes de eliminar de BD
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar este asistente?',
            html: 'Vas a eliminar a <strong>' + nombre + '</strong> del acta.<br><br>'
                + '<span style="color:#dc3545;font-size:13px;">Esta acción no se puede deshacer.</span>',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true,
        }).then(function(result) {
            if (!result.isConfirmed) return;

            if (!idActaActual) {
                // No hay acta guardada, solo remover del DOM
                row.remove();
                updateAsist();
                return;
            }

            var fd = new FormData();
            fd.append(csrfName, csrfHash);

            fetch(deleteAsistUrlBase + idActaActual + '/' + idAsistente, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    Swal.fire('No se pudo eliminar', data.error || 'Intenta de nuevo', 'error');
                    return;
                }
                row.remove();
                updateAsist();
                Swal.fire({ icon: 'success', title: 'Asistente eliminado', toast: true, position: 'top', showConfirmButton: false, timer: 1500 });
            })
            .catch(function() {
                Swal.fire('Error de conexión', 'No se pudo eliminar el asistente.', 'error');
            });
        });
    });

    document.getElementById('btnAddAsist').addEventListener('click', function() {
        var num = document.querySelectorAll('.asistente-row').length + 1;
        var html = `
            <div class="card mb-3 asistente-row" data-asistente-id="">
                <div class="card-body p-2">
                    <input type="hidden" name="asistente_id[]" value="">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Asistente #<span class="asist-num">${num}</span>
                            <span class="badge bg-secondary" style="font-size:10px;">Sin guardar</span>
                        </strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asist" style="min-height:32px;"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="row g-2">
                        <div class="col-12"><input type="text" name="asistente_nombre[]" class="form-control form-control-sm" placeholder="Nombre completo *" required></div>
                        <div class="col-4">
                            <select name="asistente_tipo_doc[]" class="form-select form-select-sm">
                                <option value="CC" selected>CC</option><option value="CE">CE</option>
                                <option value="PA">PA</option><option value="TI">TI</option><option value="NIT">NIT</option>
                            </select>
                        </div>
                        <div class="col-8"><input type="text" name="asistente_num_doc[]" class="form-control form-control-sm" placeholder="Número documento"></div>
                        <div class="col-12"><input type="text" name="asistente_cargo[]" class="form-control form-control-sm" placeholder="Cargo"></div>
                        <div class="col-12"><input type="text" name="asistente_area[]" class="form-control form-control-sm" placeholder="Contratista"></div>
                        <div class="col-12"><input type="email" name="asistente_email[]" class="form-control form-control-sm" placeholder="Email (opcional)"></div>
                        <div class="col-12"><input type="text" name="asistente_celular[]" class="form-control form-control-sm" placeholder="Celular (opcional)"></div>
                    </div>
                    <div class="mt-2 d-grid gap-1">
                        <button type="button" class="btn btn-sm btn-primary btn-save-asist" data-asistente-id="">
                            <i class="fas fa-save"></i> Guardar este asistente
                        </button>
                    </div>
                </div>
            </div>`;
        document.getElementById('asistentesContainer').insertAdjacentHTML('beforeend', html);
        updateAsist();
        var sec = document.getElementById('secAsist');
        if (!sec.classList.contains('show')) new bootstrap.Collapse(sec, { toggle: true });
    });

    var saveAsistUrlBase = '<?= site_url($saveAsistUrlBase) ?>';
    var emailUrlBase = '<?= site_url($emailUrlBase) ?>';
    var idActaActual = <?= (int)($acta['id'] ?? 0) ?>;

    // Inyecta el bloque de 3 botones (Copiar/Email/WhatsApp) en una fila después de guardarla
    function inyectarBotonesFirma(row, idAsistente, nombre, email) {
        if (row.querySelector('.btn-whatsapp-firma')) return; // ya existen
        var div = document.createElement('div');
        div.className = 'd-flex gap-1 mt-1';
        div.innerHTML = ''
            + '<button type="button" class="btn btn-sm btn-outline-secondary btn-copiar-firma flex-fill" '
            +   'data-asistente-id="' + idAsistente + '" data-nombre="' + nombre + '" title="Copiar enlace">'
            +   '<i class="fas fa-copy"></i> Copiar</button>'
            + '<button type="button" class="btn btn-sm btn-outline-primary btn-email-firma flex-fill" '
            +   'data-asistente-id="' + idAsistente + '" data-nombre="' + nombre + '" data-email="' + (email || '') + '" title="Enviar enlace al email">'
            +   '<i class="fas fa-envelope"></i> Email</button>'
            + '<button type="button" class="btn btn-sm btn-success btn-whatsapp-firma flex-fill" '
            +   'data-asistente-id="' + idAsistente + '" data-nombre="' + nombre + '" title="Compartir por WhatsApp">'
            +   '<i class="fab fa-whatsapp"></i> WhatsApp</button>';
        var saveBtn = row.querySelector('.btn-save-asist');
        if (saveBtn && saveBtn.parentElement) {
            saveBtn.parentElement.appendChild(div);
        }
    }

    // ===== Guardar UN asistente (AJAX) =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-save-asist');
        if (!btn) return;

        if (!idActaActual) {
            Swal.fire('Guarda primero el acta', 'Primero guarda el acta como borrador para poder agregar asistentes.', 'info');
            return;
        }

        var row = btn.closest('.asistente-row');
        var nombre = (row.querySelector('input[name="asistente_nombre[]"]') || {}).value || '';
        if (!nombre.trim()) {
            Swal.fire({ icon: 'warning', title: 'Nombre requerido', text: 'Ingresa el nombre del asistente.' });
            return;
        }

        var idAsistente = btn.dataset.asistenteId || (row.querySelector('input[name="asistente_id[]"]') || {}).value || '';
        var email = (row.querySelector('input[name="asistente_email[]"]') || {}).value || '';
        var fd = new FormData();
        fd.append(csrfName, csrfHash);
        fd.append('id_asistente', idAsistente);
        fd.append('nombre_completo', nombre);
        fd.append('tipo_documento', (row.querySelector('select[name="asistente_tipo_doc[]"]') || {}).value || 'CC');
        fd.append('numero_documento', (row.querySelector('input[name="asistente_num_doc[]"]') || {}).value || '');
        fd.append('cargo', (row.querySelector('input[name="asistente_cargo[]"]') || {}).value || '');
        fd.append('area_dependencia', (row.querySelector('input[name="asistente_area[]"]') || {}).value || '');
        fd.append('email', email);
        fd.append('celular', (row.querySelector('input[name="asistente_celular[]"]') || {}).value || '');
        var rows = Array.from(document.querySelectorAll('.asistente-row'));
        fd.append('orden', String(rows.indexOf(row) + 1));

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        fetch(saveAsistUrlBase + idActaActual, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (!data.success) {
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar este asistente';
                Swal.fire('Error', data.error || 'No se pudo guardar', 'error');
                return;
            }
            // Actualiza ID en la fila
            var hiddenId = row.querySelector('input[name="asistente_id[]"]');
            if (hiddenId) hiddenId.value = data.id;
            row.dataset.asistenteId = data.id;
            btn.dataset.asistenteId = data.id;
            btn.innerHTML = '<i class="fas fa-check"></i> Guardado';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');

            // Actualiza badge "Sin guardar" → "Guardado"
            var badge = row.querySelector('.badge.bg-secondary');
            if (badge) {
                badge.textContent = 'Guardado';
                badge.classList.remove('bg-secondary');
                badge.classList.add('bg-success');
            }

            // Inyecta botones de firma si aún no existen
            inyectarBotonesFirma(row, data.id, nombre, email);

            Swal.fire({ icon: 'success', title: 'Asistente guardado', toast: true, position: 'top', showConfirmButton: false, timer: 1500 });
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar este asistente';
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    });

    // ===== Copiar enlace =====
    function generarTokenYHacer(idAsistente, callback) {
        var fd = new FormData();
        fd.append(csrfName, csrfHash);
        fetch(tokenUrlBase + idAsistente, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                Swal.fire('Error', data.error || 'No se pudo generar el enlace', 'error');
                return;
            }
            callback(data.url);
        })
        .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-copiar-firma');
        if (!btn) return;
        var idAsistente = btn.dataset.asistenteId;
        if (!idAsistente) {
            Swal.fire('Guarda primero', 'Guarda este asistente antes de copiar el enlace.', 'info');
            return;
        }
        Swal.fire({ title: 'Generando enlace...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
        generarTokenYHacer(idAsistente, function(url) {
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Enlace copiado',
                    html: '<p style="font-size:13px;">El enlace ya está en tu portapapeles. Pégalo donde necesites.</p>'
                        + '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;border:1px solid #dee2e6;">'
                        + url + '</div>',
                    confirmButtonText: 'Cerrar',
                });
            }).catch(function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Copia manualmente',
                    html: '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;border:1px solid #dee2e6;">'
                        + url + '</div>',
                    confirmButtonText: 'Cerrar',
                });
            });
        });
    });

    // ===== Email firma =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-email-firma');
        if (!btn) return;

        var idAsistente = btn.dataset.asistenteId;
        var nombre = btn.dataset.nombre;
        var email = btn.dataset.email;

        if (!idAsistente) {
            Swal.fire('Guarda primero', 'Guarda este asistente antes de enviar el email.', 'info');
            return;
        }
        // Si el dataset email está vacío, intenta leerlo del input por si lo acaban de escribir
        if (!email) {
            var row = btn.closest('.asistente-row');
            email = (row && row.querySelector('input[name="asistente_email[]"]')) ? row.querySelector('input[name="asistente_email[]"]').value : '';
        }
        if (!email) {
            Swal.fire('Sin email', 'Este asistente no tiene email registrado. Edita la fila y guárdala primero.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Enviar email de firma',
            html: '<p style="font-size:14px;">Se enviará un enlace de firma a:<br><strong>' + email + '</strong><br>(' + nombre + ')</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd',
        }).then(function(r) {
            if (!r.isConfirmed) return;
            Swal.fire({ title: 'Enviando email...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

            var fd = new FormData();
            fd.append(csrfName, csrfHash);

            fetch(emailUrlBase + idAsistente, {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: '¡Email enviado!', text: 'Enviado a ' + (data.email || email), confirmButtonColor: '#0d6efd' });
                } else {
                    Swal.fire('Error', data.error || 'No se pudo enviar el email', 'error');
                }
            })
            .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
        });
    });

    // ===== WhatsApp firma remota =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-whatsapp-firma');
        if (!btn) return;

        var idAsistente = btn.dataset.asistenteId;
        var nombre = btn.dataset.nombre;

        if (!idAsistente) {
            Swal.fire('Guarda primero', 'Guarda el acta para generar el enlace de este asistente.', 'info');
            return;
        }

        Swal.fire({
            title: 'Enviar enlace de firma',
            html: '<p style="font-size:14px;">Se generará un enlace para que <strong>' + nombre + '</strong> firme desde su celular.<br><small class="text-muted">Vence en 7 días.</small></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Generar enlace',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#25D366',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Generando enlace...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

            var formData = new FormData();
            formData.append(csrfName, csrfHash);

            fetch(tokenUrlBase + idAsistente, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    Swal.fire('Error', data.error || 'No se pudo generar el enlace', 'error');
                    return;
                }
                var url = data.url;
                var texto = encodeURIComponent('Hola ' + nombre + ', por favor firma el acta de capacitación haciendo clic en este enlace (vence en 7 días):\n' + url);
                var waUrl = 'https://wa.me/?text=' + texto;

                Swal.fire({
                    title: 'Enlace generado',
                    html: '<p style="font-size:13px;">Comparte con <strong>' + nombre + '</strong>:</p>'
                          + '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;margin-bottom:12px;border:1px solid #dee2e6;">'
                          + url + '</div>'
                          + '<button type="button" id="btnCopiar" class="btn btn-sm btn-outline-secondary"><i class="fas fa-copy"></i> Copiar enlace</button>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fab fa-whatsapp"></i> Abrir WhatsApp',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#25D366',
                    didOpen: function() {
                        document.getElementById('btnCopiar').addEventListener('click', function() {
                            navigator.clipboard.writeText(url).then(function() {
                                var b = document.getElementById('btnCopiar');
                                b.innerHTML = '<i class="fas fa-check"></i> Copiado';
                                b.classList.remove('btn-outline-secondary'); b.classList.add('btn-success');
                            });
                        });
                    }
                }).then(function(r) {
                    if (r.isConfirmed) window.open(waUrl, '_blank');
                });
            })
            .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
        });
    });

    var btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function(e) {
            if (document.querySelectorAll('.asistente-row').length === 0) {
                Swal.fire({ icon: 'warning', title: 'Agrega al menos 1 asistente antes de finalizar', confirmButtonColor: '#bd9751' });
                return;
            }
            Swal.fire({
                icon: 'question',
                title: '¿Ya terminaron todas las evaluaciones?',
                html: 'Sin las evaluaciones cerradas <strong>no se podrá calcular el puntaje automático con IA</strong>.<br><br>'
                    + 'Se generará el PDF con las firmas registradas a la fecha. Los asistentes que aún no firmaron quedarán sin firma.<br><br>'
                    + '¿Confirmas finalizar?',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'No, espero',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
            }).then(r => {
                if (r.isConfirmed) {
                    var i = document.createElement('input');
                    i.type = 'hidden'; i.name = 'finalizar'; i.value = '1';
                    document.getElementById('actaCapForm').appendChild(i);
                    document.getElementById('actaCapForm').submit();
                }
            });
        });
    }

    // ============================================================
    // CAPACITACIONES (multi-checkbox unificado para CREATE y EDIT)
    // ============================================================
    <?php if ($ctx === 'consultor'): ?>
    var isEditModeActa = <?= $isEdit ? 'true' : 'false' ?>;
    var cronogramasDataActa = [];
    // En EDIT, IDs ya vinculados a esta acta (vienen del controller)
    var idCronogIniciales = <?= json_encode($idCronogIds ?? []) ?>;

    function cargarCronogramasActa(idCliente) {
        cronogramasDataActa = [];
        var cont = document.getElementById('cronogramasCheckboxesContainer');
        if (!cont) return;

        cont.innerHTML = idCliente
            ? '<p class="text-muted mb-0" style="font-size:13px;"><i class="fas fa-spinner fa-spin"></i> Cargando capacitaciones programadas...</p>'
            : '<p class="text-muted mb-0" style="font-size:13px;"><i class="fas fa-info-circle"></i> Seleccione un cliente para ver sus capacitaciones programadas.</p>';

        if (!idCliente) return;

        $.ajax({
            url: '<?= site_url('inspecciones/acta-capacitacion/api-cronogramas-pendientes') ?>',
            data: { id_cliente: idCliente, id_acta: <?= $isEdit ? (int)($acta['id'] ?? 0) : 0 ?> },
            dataType: 'json',
            success: function(data) {
                cronogramasDataActa = data || [];
                renderCronogActaCheckboxes();
            }
        });
    }

    function renderCronogActaCheckboxes() {
        var cont = document.getElementById('cronogramasCheckboxesContainer');
        if (!cont) return;
        if (!cronogramasDataActa.length) {
            cont.innerHTML = '<p class="text-muted mb-0" style="font-size:13px;"><i class="fas fa-exclamation-triangle"></i> Este cliente no tiene capacitaciones en estado PROGRAMADA pendientes.</p>';
            return;
        }
        var html = '';
        cronogramasDataActa.forEach(function(c) {
            var fp = c.fecha_programada ? ' <small class="text-muted">(' + c.fecha_programada + ')</small>' : '';
            var checked = (idCronogIniciales.indexOf(parseInt(c.id_cronograma_capacitacion)) !== -1) ? 'checked' : '';
            html += '<div class="form-check">'
                + '<input class="form-check-input cronog-check" type="checkbox" name="id_cronogramas[]" '
                + 'value="' + c.id_cronograma_capacitacion + '" id="cronog_a_' + c.id_cronograma_capacitacion + '" ' + checked + '>'
                + '<label class="form-check-label" for="cronog_a_' + c.id_cronograma_capacitacion + '" style="font-size:13px;">'
                + escHtmlActa(c.nombre_capacitacion || 'Sin nombre') + fp
                + '</label></div>';
        });
        cont.innerHTML = html;
    }

    function escHtmlActa(s) {
        return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Listener: cuando el usuario cambia el cliente, recargar cronogramas
    var sCliente = document.getElementById('selectClienteCap');
    if (sCliente) {
        $(sCliente).on('change', function() { cargarCronogramasActa(this.value); });
    }

    // Carga inicial: usar el id_cliente venido de PHP (no esperar al AJAX de clientes)
    var idClienteIni = '<?= $idCliente ?? ($acta['id_cliente'] ?? '') ?>';
    if (idClienteIni) cargarCronogramasActa(idClienteIni);

    // ============================================================
    // Panel Evaluaciones del día (read-only)
    // ============================================================
    function cargarEvalDia() {
        var cont = document.getElementById('evalDiaContainer');
        if (!cont) return;
        var idCliente = document.querySelector('[name="id_cliente"]') ? document.querySelector('[name="id_cliente"]').value : idClienteIni;
        var fecha     = (document.querySelector('[name="fecha_capacitacion"]') || {}).value || '';
        if (!idCliente || !fecha) {
            cont.innerHTML = '<span class="text-muted">Seleccione cliente y fecha.</span>';
            return;
        }
        cont.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</span>';
        $.ajax({
            url: '<?= site_url('inspecciones/acta-capacitacion/api-evaluaciones-del-dia') ?>',
            data: { id_cliente: idCliente, fecha: fecha },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    cont.innerHTML = '<span class="text-muted">' + (resp.msg || 'Sin datos.') + '</span>';
                    return;
                }
                if (!resp.total) {
                    cont.innerHTML = '<span class="text-muted">Sin evaluaciones registradas para este cliente y fecha (±7 días).</span>';
                    return;
                }
                var html = '<div style="font-size:12px; color:#065f46; margin-bottom:6px;">'
                         + '<strong>' + resp.total + '</strong> respuesta(s) en <strong>' + resp.temas_count + '</strong> tema(s):</div>';
                // Resumen por tema
                html += '<table class="table table-sm mb-2" style="font-size:11px;">';
                html += '<thead><tr><th>Tema</th><th class="text-center">Respuestas</th><th class="text-center">Promedio</th></tr></thead><tbody>';
                resp.resumen.forEach(function(r) {
                    var avg = parseFloat(r.promedio).toFixed(1);
                    var color = (parseFloat(r.promedio) >= 70) ? '#198754' : '#dc3545';
                    html += '<tr><td>' + escHtmlActa(r.tema) + '</td>'
                          + '<td class="text-center">' + r.total + '</td>'
                          + '<td class="text-center" style="font-weight:600; color:' + color + ';">' + avg + '</td></tr>';
                });
                html += '</tbody></table>';
                // Detalle (collapsible)
                html += '<details style="font-size:11px;"><summary style="cursor:pointer; color:#065f46;">Ver detalle por persona</summary>';
                html += '<table class="table table-sm mt-1" style="font-size:11px;"><thead><tr><th>Tema</th><th>Nombre</th><th>Cédula</th><th>Cargo</th><th class="text-center">Calificación</th></tr></thead><tbody>';
                resp.detalle.forEach(function(d) {
                    var cal = parseFloat(d.calificacion).toFixed(1);
                    var cls = (parseFloat(d.calificacion) >= 70) ? 'text-success' : 'text-danger';
                    html += '<tr><td>' + escHtmlActa(d.tema) + '</td>'
                          + '<td>' + escHtmlActa(d.nombre || '') + '</td>'
                          + '<td>' + escHtmlActa(d.cedula || '') + '</td>'
                          + '<td>' + escHtmlActa(d.cargo || '') + '</td>'
                          + '<td class="text-center fw-bold ' + cls + '">' + cal + '</td></tr>';
                });
                html += '</tbody></table></details>';
                cont.innerHTML = html;
            },
            error: function() {
                cont.innerHTML = '<span class="text-danger">Error al cargar.</span>';
            }
        });
    }

    cargarEvalDia();
    var btnRefreshEval = document.getElementById('btnRefreshEvalDia');
    if (btnRefreshEval) btnRefreshEval.addEventListener('click', cargarEvalDia);

    var fechaInputEval = document.querySelector('[name="fecha_capacitacion"]');
    if (fechaInputEval) fechaInputEval.addEventListener('change', cargarEvalDia);
    var clienteSelEval = document.getElementById('selectClienteCap');
    if (clienteSelEval) $(clienteSelEval).on('change', cargarEvalDia);
    <?php endif; ?>
});
</script>
