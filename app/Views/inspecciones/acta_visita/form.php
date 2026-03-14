<?php
$isEdit = !empty($acta);
$action = $isEdit ? base_url('/inspecciones/acta-visita/update/') . $acta['id'] : base_url('/inspecciones/acta-visita/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="actaForm">
        <?= csrf_field() ?>

        <!-- Errores de validación -->
        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Accordion de secciones -->
        <div class="accordion mt-2" id="accordionActa">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <!-- Cliente -->
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Fecha *</label>
                                <input type="date" name="fecha_visita" class="form-control"
                                    value="<?= $acta['fecha_visita'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hora *</label>
                                <input type="time" name="hora_visita" class="form-control"
                                    value="<?= $acta['hora_visita'] ?? date('H:i') ?>" required>
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="mb-3">
                            <label class="form-label">Motivo *</label>
                            <input type="text" name="motivo" class="form-control"
                                value="<?= esc($acta['motivo'] ?? '') ?>"
                                placeholder="Ej: Visita mensual de seguimiento" required>
                        </div>

                        <!-- Modalidad -->
                        <div class="mb-3">
                            <label class="form-label">Modalidad</label>
                            <select name="modalidad" class="form-select">
                                <option value="Presencial" <?= ($acta['modalidad'] ?? '') === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                                <option value="Virtual" <?= ($acta['modalidad'] ?? '') === 'Virtual' ? 'selected' : '' ?>>Virtual</option>
                                <option value="Mixta" <?= ($acta['modalidad'] ?? '') === 'Mixta' ? 'selected' : '' ?>>Mixta</option>
                            </select>
                        </div>

                        <!-- Ubicación GPS -->
                        <input type="hidden" name="ubicacion_gps" id="ubicacionGps" value="<?= esc($acta['ubicacion_gps'] ?? '') ?>">
                        <div class="mb-0" id="gpsStatus" style="font-size:13px; color:#999;">
                            <i class="fas fa-map-marker-alt"></i> Capturando ubicacion...
                        </div>
                    </div>
                </div>
            </div>

            <!-- INTEGRANTES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secIntegrantes">
                        Integrantes (<span id="countIntegrantes"><?= count($integrantes) ?></span>)
                    </button>
                </h2>
                <div id="secIntegrantes" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <div id="integrantesContainer">
                            <?php if (!empty($integrantes)): ?>
                                <?php foreach ($integrantes as $integrante): ?>
                                <div class="row g-2 mb-2 integrante-row">
                                    <div class="col-5">
                                        <input type="text" name="integrante_nombre[]" class="form-control" placeholder="Nombre" value="<?= esc($integrante['nombre']) ?>">
                                    </div>
                                    <div class="col-5">
                                        <select name="integrante_rol[]" class="form-select">
                                            <option value="">Rol...</option>
                                            <?php foreach (['ADMINISTRADOR', 'ASISTENTE DE ADMINISTRACIÓN', 'CONSULTOR CYCLOID', 'VIGÍA SST', 'OTRO'] as $rol): ?>
                                            <option value="<?= $rol ?>" <?= $integrante['rol'] === $rol ? 'selected' : '' ?>><?= $rol ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddIntegrante">
                            <i class="fas fa-plus"></i> Agregar integrante
                        </button>
                    </div>
                </div>
            </div>

            <!-- TEMAS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secTemas">
                        Temas (<span id="countTemas"><?= count($temas) ?></span>)
                    </button>
                </h2>
                <div id="secTemas" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <div id="temasContainer">
                            <?php if (!empty($temas)): ?>
                                <?php foreach ($temas as $tema): ?>
                                <div class="mb-2 tema-row d-flex gap-2">
                                    <textarea name="tema[]" class="form-control" rows="2" placeholder="Descripcion del tema"><?= esc($tema['descripcion']) ?></textarea>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-width:44px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddTema">
                            <i class="fas fa-plus"></i> Agregar tema
                        </button>
                    </div>
                </div>
            </div>

            <!-- TEMAS ABIERTOS (auto) -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secTemasAbiertos">
                        Temas Abiertos y Vencidos (auto)
                    </button>
                </h2>
                <div id="secTemasAbiertos" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body" id="temasAbiertosContent">
                        <p class="text-muted" style="font-size:13px;">Selecciona un cliente para ver sus pendientes y mantenimientos.</p>
                    </div>
                </div>
            </div>

            <!-- ACTIVIDADES PTA DEL MES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secPta">
                        Actividades PTA del Mes (<span id="countPta">0</span>)
                    </button>
                </h2>
                <div id="secPta" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body" id="ptaContent">
                        <p class="text-muted" style="font-size:13px;">Selecciona cliente y fecha para cargar actividades del PTA.</p>
                    </div>
                </div>
            </div>

            <!-- OBSERVACIONES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secObs">
                        Observaciones
                    </button>
                </h2>
                <div id="secObs" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones generales..."><?= esc($acta['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- COMPROMISOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secCompromisos">
                        Compromisos (<span id="countCompromisos"><?= count($compromisos ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secCompromisos" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <div id="compromisosContainer">
                            <?php if (!empty($compromisos)): ?>
                                <?php foreach ($compromisos as $comp): ?>
                                <div class="card mb-2 compromiso-row">
                                    <div class="card-body p-2">
                                        <input type="text" name="compromiso_actividad[]" class="form-control mb-1" placeholder="Actividad" value="<?= esc($comp['tarea_actividad']) ?>">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="date" name="compromiso_fecha[]" class="form-control" value="<?= $comp['fecha_cierre'] ?? '' ?>">
                                            </div>
                                            <div class="col-5">
                                                <input type="text" name="compromiso_responsable[]" class="form-control" placeholder="Responsable" value="<?= esc($comp['responsable'] ?? '') ?>">
                                            </div>
                                            <div class="col-1 text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddCompromiso">
                            <i class="fas fa-plus"></i> Agregar compromiso
                        </button>
                    </div>
                </div>
            </div>

            <!-- FOTOS Y SOPORTES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secFotos">
                        Fotos y Soportes
                    </button>
                </h2>
                <div id="secFotos" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <!-- Fotos existentes -->
                        <?php if (!empty($fotos)): ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($fotos as $foto): ?>
                            <div class="col-4">
                                <img src="<?= base_url($foto['ruta_archivo']) ?>" class="img-fluid rounded" style="max-height:120px; object-fit:cover; width:100%;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <label class="form-label">Agregar fotos</label>
                        <div class="photo-input-group">
                            <input type="file" name="fotos[]" class="file-preview" accept="image/*" multiple style="display:none;">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i> Camara</button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                            </div>
                            <div class="preview-img mt-1"></div>
                        </div>
                        <small class="text-muted">Usa Camara para tomar foto o Galeria para seleccionar existentes</small>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- Botones de acción -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save me-1"></i> Guardar borrador
            </button>
            <button type="button" class="btn btn-pwa btn-pwa-primary py-3" id="btnIrFirmas" style="font-size:17px;">
                <i class="fas fa-signature me-1"></i> Guardar e ir a firmas
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // --- Select2 para clientes ---
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteId && c.id_cliente == clienteId) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });

            // Restaurar cliente desde autoguardado si aplica
            if (window._pendingClientRestore) {
                $('#selectCliente').val(window._pendingClientRestore).trigger('change');
                window._pendingClientRestore = null;
            }

            // Si ya hay cliente seleccionado, cargar temas abiertos
            if (clienteId) loadTemasAbiertos(clienteId);
        }
    });

    // Cargar temas abiertos y PTA al cambiar cliente
    $('#selectCliente').on('change', function() {
        const id = this.value;
        if (id) {
            loadTemasAbiertos(id);
            loadPtaActividades();
        }
    });

    // Cargar PTA al cambiar fecha
    document.querySelector('[name="fecha_visita"]').addEventListener('change', function() {
        loadPtaActividades();
    });

    function loadTemasAbiertos(idCliente) {
        const container = document.getElementById('temasAbiertosContent');
        container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';

        Promise.all([
            fetch('/inspecciones/api/pendientes/' + idCliente).then(r => r.json()),
            fetch('/inspecciones/api/mantenimientos/' + idCliente).then(r => r.json()),
        ]).then(([pendientes, mantenimientos]) => {
            let html = '';

            // Mantenimientos
            html += '<h6 style="font-size:14px; font-weight:700;">Mantenimientos por vencer</h6>';
            if (mantenimientos.length === 0) {
                html += '<p style="font-size:13px; color:green;"><i class="fas fa-check-circle"></i> Sin mantenimientos por vencer</p>';
            } else {
                html += '<ul style="font-size:13px; padding-left:20px;">';
                mantenimientos.forEach(m => {
                    html += '<li>' + (m.detalle_mantenimiento || 'Mantenimiento') + ' - Vence: ' + m.fecha_vencimiento + '</li>';
                });
                html += '</ul>';
            }

            // Pendientes
            html += '<h6 style="font-size:14px; font-weight:700; margin-top:12px;">Pendientes abiertos</h6>';
            if (pendientes.length === 0) {
                html += '<p style="font-size:13px; color:green;"><i class="fas fa-check-circle"></i> Sin pendientes abiertos</p>';
            } else {
                html += '<ul style="font-size:13px; padding-left:20px;">';
                pendientes.forEach(p => {
                    var fecha = p.fecha_asignacion ? p.fecha_asignacion.split('-').reverse().join('/') : '';
                    var cierre = p.fecha_cierre ? ' → Cierre: ' + p.fecha_cierre.split('-').reverse().join('/') : '';
                    html += '<li>' + p.tarea_actividad + ' - ' + (p.responsable || '') + '<br><small style="color:#888;">Asignado: ' + fecha + cierre + ' (' + p.conteo_dias + ' dias)</small></li>';
                });
                html += '</ul>';
            }

            container.innerHTML = html;
        });
    }

    // --- PTA Actividades ---
    const actaIdForPta = <?= $acta['id'] ?? 'null' ?>;

    function loadPtaActividades() {
        const idCliente = document.getElementById('selectCliente').value;
        const fechaVisita = document.querySelector('[name="fecha_visita"]').value;
        const container = document.getElementById('ptaContent');

        if (!idCliente || !fechaVisita) {
            container.innerHTML = '<p class="text-muted" style="font-size:13px;">Selecciona cliente y fecha para cargar actividades del PTA.</p>';
            document.getElementById('countPta').textContent = '0';
            return;
        }

        container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando actividades PTA...</p>';

        let url = '/inspecciones/acta-visita/api/pta-actividades?id_cliente=' + idCliente + '&fecha_visita=' + fechaVisita;
        if (actaIdForPta) url += '&id_acta=' + actaIdForPta;

        fetch(url).then(r => r.json()).then(data => {
            const actividades = data.actividades || [];
            const prevLinks = data.prevLinks || {};

            if (actividades.length === 0) {
                container.innerHTML = '<p class="text-muted" style="font-size:13px;"><i class="fas fa-info-circle"></i> No hay actividades PTA abiertas para este mes.</p>';
                document.getElementById('countPta').textContent = '0';
                return;
            }

            document.getElementById('countPta').textContent = actividades.length;

            const mesVisita = parseInt(fechaVisita.split('-')[1], 10);
            let html = '<div style="font-size:13px; color:#666; margin-bottom:8px;">Marca las actividades que se cerraron en esta visita:</div>';

            actividades.forEach(a => {
                const id = a.id_ptacliente;
                const yaCerrada = a._ya_cerrada || false;
                const prev = prevLinks[id];
                const isChecked = yaCerrada || (prev && prev.cerrada);
                const isDisabled = yaCerrada;

                html += '<div class="form-check mb-2 pta-item" style="border-bottom:1px solid #eee; padding-bottom:6px;">';
                html += '<input type="hidden" name="pta_actividad_id[]" value="' + id + '">';
                html += '<input class="form-check-input pta-checkbox" type="checkbox" value="' + id + '" name="pta_actividad_checked[]" id="ptaCheck' + id + '"' + (isChecked ? ' checked' : '') + (isDisabled ? ' disabled' : '') + ' style="margin-top:4px;">';
                html += '<label class="form-check-label" for="ptaCheck' + id + '" style="font-size:13px;">';
                html += '<strong>' + (a.numeral_plandetrabajo || '') + '</strong> - ' + (a.actividad_plandetrabajo || '');
                if (a.fecha_propuesta) {
                    const mesPta = parseInt(a.fecha_propuesta.split('-')[1], 10);
                    html += ' <small class="text-muted">(' + a.fecha_propuesta + ')</small>';
                    if (mesPta < mesVisita) {
                        html += ' <span class="badge bg-warning text-dark" style="font-size:10px;">REZAGADA</span>';
                    }
                }
                if (yaCerrada) {
                    html += ' <span class="badge bg-success" style="font-size:10px;">CERRADA</span>';
                }
                html += '</label>';
                html += '</div>';
            });

            container.innerHTML = html;
        }).catch(() => {
            container.innerHTML = '<p class="text-danger" style="font-size:13px;"><i class="fas fa-exclamation-circle"></i> Error al cargar actividades PTA.</p>';
        });
    }

    // Cargar PTA al inicio si ya hay cliente y fecha
    if (clienteId) {
        setTimeout(() => loadPtaActividades(), 500);
    }

    // --- GPS ---
    if (navigator.geolocation && !document.getElementById('ubicacionGps').value) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById('ubicacionGps').value = pos.coords.latitude + ',' + pos.coords.longitude;
                document.getElementById('gpsStatus').innerHTML = '<i class="fas fa-map-marker-alt text-success"></i> Ubicacion capturada';
            },
            () => {
                document.getElementById('gpsStatus').innerHTML = '<i class="fas fa-map-marker-alt text-warning"></i> No se pudo capturar ubicacion';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else if (document.getElementById('ubicacionGps').value) {
        document.getElementById('gpsStatus').innerHTML = '<i class="fas fa-map-marker-alt text-success"></i> Ubicacion capturada';
    }

    // --- Dynamic rows ---
    function updateCounts() {
        document.getElementById('countIntegrantes').textContent = document.querySelectorAll('.integrante-row').length;
        document.getElementById('countTemas').textContent = document.querySelectorAll('.tema-row').length;
        document.getElementById('countCompromisos').textContent = document.querySelectorAll('.compromiso-row').length;
    }

    // Remove row handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('.integrante-row, .tema-row, .compromiso-row').remove();
            updateCounts();
        }
    });

    // Add integrante
    document.getElementById('btnAddIntegrante').addEventListener('click', function() {
        const roles = ['ADMINISTRADOR', 'ASISTENTE DE ADMINISTRACIÓN', 'CONSULTOR CYCLOID', 'VIGÍA SST', 'OTRO'];
        const options = roles.map(r => '<option value="' + r + '">' + r + '</option>').join('');
        const html = `
            <div class="row g-2 mb-2 integrante-row">
                <div class="col-5">
                    <input type="text" name="integrante_nombre[]" class="form-control" placeholder="Nombre">
                </div>
                <div class="col-5">
                    <select name="integrante_rol[]" class="form-select">
                        <option value="">Rol...</option>${options}
                    </select>
                </div>
                <div class="col-2 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;
        document.getElementById('integrantesContainer').insertAdjacentHTML('beforeend', html);
        updateCounts();
    });

    // Add tema
    document.getElementById('btnAddTema').addEventListener('click', function() {
        const html = `
            <div class="mb-2 tema-row d-flex gap-2">
                <textarea name="tema[]" class="form-control" rows="2" placeholder="Descripcion del tema"></textarea>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-width:44px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        document.getElementById('temasContainer').insertAdjacentHTML('beforeend', html);
        updateCounts();
    });

    // Add compromiso
    document.getElementById('btnAddCompromiso').addEventListener('click', function() {
        const html = `
            <div class="card mb-2 compromiso-row">
                <div class="card-body p-2">
                    <input type="text" name="compromiso_actividad[]" class="form-control mb-1" placeholder="Actividad">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="compromiso_fecha[]" class="form-control">
                        </div>
                        <div class="col-5">
                            <input type="text" name="compromiso_responsable[]" class="form-control" placeholder="Responsable">
                        </div>
                        <div class="col-1 text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.getElementById('compromisosContainer').insertAdjacentHTML('beforeend', html);
        updateCounts();
    });

    // --- Justificación PTA para actividades no marcadas ---
    function askPtaJustifications(callback) {
        const unchecked = [];
        document.querySelectorAll('.pta-item').forEach(item => {
            const cb = item.querySelector('.pta-checkbox');
            if (!cb.checked && !cb.disabled) {
                const label = item.querySelector('.form-check-label');
                const id = cb.value;
                unchecked.push({ id: id, text: label ? label.textContent.trim() : 'Actividad ' + id });
            }
        });

        if (unchecked.length === 0) {
            callback();
            return;
        }

        // Construir HTML del formulario de justificaciones
        let htmlInputs = '<div style="text-align:left; font-size:13px; max-height:60vh; overflow-y:auto;">';
        unchecked.forEach((a, i) => {
            htmlInputs += '<div style="margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:8px;">';
            htmlInputs += '<strong>' + (i + 1) + '. ' + a.text + '</strong>';
            htmlInputs += '<textarea id="swalJust_' + a.id + '" class="swal2-textarea" placeholder="¿Por qué no se cerró esta actividad?" style="font-size:13px; min-height:60px; width:100%; margin-top:4px;"></textarea>';
            htmlInputs += '</div>';
        });
        htmlInputs += '</div>';

        Swal.fire({
            title: 'Actividades PTA no cerradas',
            html: htmlInputs,
            icon: 'question',
            width: '90%',
            confirmButtonText: 'Guardar justificaciones',
            confirmButtonColor: '#bd9751',
            allowOutsideClick: false,
            preConfirm: () => {
                let allFilled = true;
                unchecked.forEach(a => {
                    const val = document.getElementById('swalJust_' + a.id).value.trim();
                    if (!val) allFilled = false;
                });
                if (!allFilled) {
                    Swal.showValidationMessage('Debes justificar todas las actividades no cerradas');
                    return false;
                }
                return true;
            }
        }).then(result => {
            if (result.isConfirmed) {
                // Inyectar justificaciones como hidden inputs en el form
                const form = document.getElementById('actaForm');
                unchecked.forEach(a => {
                    const val = document.getElementById('swalJust_' + a.id).value.trim();
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'pta_justificacion[' + a.id + ']';
                    hidden.value = val;
                    form.appendChild(hidden);
                });
                callback();
            }
        });
    }

    // --- Interceptar submit del form (Guardar borrador) ---
    document.getElementById('actaForm').addEventListener('submit', function(e) {
        // No interceptar autosave ni si ya fue procesado
        if (this._ptaProcessed || this._isAutosave) return;

        const hasPtaItems = document.querySelectorAll('.pta-item').length > 0;
        if (!hasPtaItems) return;

        e.preventDefault();
        const form = this;
        askPtaJustifications(() => {
            form._ptaProcessed = true;
            form.requestSubmit();
        });
    });

    // --- Validación mínima antes de ir a firmas ---
    document.getElementById('btnIrFirmas').addEventListener('click', function(e) {
        var btn = this;
        var form = document.getElementById('actaForm');
        var cliente = document.getElementById('selectCliente').value;
        var temas = document.querySelectorAll('.tema-row').length;
        var integrantes = document.querySelectorAll('.integrante-row').length;

        if (!cliente || temas === 0 || integrantes === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                html: 'Para ir a firmas necesitas al menos:<br><br>' +
                    (!cliente ? '- Seleccionar un cliente<br>' : '') +
                    (integrantes === 0 ? '- Agregar al menos 1 integrante<br>' : '') +
                    (temas === 0 ? '- Agregar al menos 1 tema<br>' : ''),
                confirmButtonColor: '#bd9751',
            });
            return;
        }

        const hasPtaItems = document.querySelectorAll('.pta-item').length > 0;

        function doSubmitFirmas() {
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'ir_a_firmas';
            hidden.value = '1';
            form.appendChild(hidden);
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            form._lastClickedSubmit = btn;
            form._ptaProcessed = true;
            form.requestSubmit();
        }

        if (hasPtaItems) {
            askPtaJustifications(doSubmitFirmas);
        } else {
            doSubmitFirmas();
        }
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE (restaurar borradores)
    // ============================================================
    const STORAGE_KEY = 'acta_draft_<?= $acta['id'] ?? 'new' ?>';
    const isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        // Cliente - se restaura después de que Select2 cargue
        if (data.id_cliente) {
            window._pendingClientRestore = data.id_cliente;
        }
        if (data.fecha_visita) document.querySelector('[name="fecha_visita"]').value = data.fecha_visita;
        if (data.hora_visita) document.querySelector('[name="hora_visita"]').value = data.hora_visita;
        if (data.motivo) document.querySelector('[name="motivo"]').value = data.motivo;
        if (data.modalidad) document.querySelector('[name="modalidad"]').value = data.modalidad;
        if (data.observaciones) document.querySelector('[name="observaciones"]').value = data.observaciones;
        if (data.ubicacion_gps) document.getElementById('ubicacionGps').value = data.ubicacion_gps;

        // Integrantes
        const roles = ['ADMINISTRADOR', 'ASISTENTE DE ADMINISTRACIÓN', 'CONSULTOR CYCLOID', 'VIGÍA SST', 'OTRO'];
        const roleOpts = roles.map(r => '<option value="' + r + '">' + r + '</option>').join('');
        (data.integrantes || []).forEach(int => {
            const html = '<div class="row g-2 mb-2 integrante-row"><div class="col-5"><input type="text" name="integrante_nombre[]" class="form-control" placeholder="Nombre" value="' + (int.nombre||'').replace(/"/g,'&quot;') + '"></div><div class="col-5"><select name="integrante_rol[]" class="form-select"><option value="">Rol...</option>' + roleOpts + '</select></div><div class="col-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;"><i class="fas fa-times"></i></button></div></div>';
            document.getElementById('integrantesContainer').insertAdjacentHTML('beforeend', html);
            if (int.rol) {
                const rows = document.querySelectorAll('.integrante-row');
                rows[rows.length - 1].querySelector('[name="integrante_rol[]"]').value = int.rol;
            }
        });

        // Temas
        (data.temas || []).forEach(t => {
            const html = '<div class="mb-2 tema-row d-flex gap-2"><textarea name="tema[]" class="form-control" rows="2" placeholder="Descripcion del tema">' + t.replace(/</g,'&lt;') + '</textarea><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-width:44px;"><i class="fas fa-times"></i></button></div>';
            document.getElementById('temasContainer').insertAdjacentHTML('beforeend', html);
        });

        // Compromisos
        (data.compromisos || []).forEach(c => {
            const html = '<div class="card mb-2 compromiso-row"><div class="card-body p-2"><input type="text" name="compromiso_actividad[]" class="form-control mb-1" placeholder="Actividad" value="' + (c.actividad||'').replace(/"/g,'&quot;') + '"><div class="row g-2"><div class="col-6"><input type="date" name="compromiso_fecha[]" class="form-control" value="' + (c.fecha||'') + '"></div><div class="col-5"><input type="text" name="compromiso_responsable[]" class="form-control" placeholder="Responsable" value="' + (c.responsable||'').replace(/"/g,'&quot;') + '"></div><div class="col-1 text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;"><i class="fas fa-times"></i></button></div></div></div></div>';
            document.getElementById('compromisosContainer').insertAdjacentHTML('beforeend', html);
        });

        updateCounts();
    }

    // Verificar si hay borrador guardado (solo en creación nueva sin datos previos del servidor)
    if (!isEditLocal) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const savedTime = new Date(data._savedAt);
                const hoursAgo = ((Date.now() - savedTime.getTime()) / 3600000).toFixed(1);

                // Solo ofrecer restaurar si tiene menos de 24 horas
                if (hoursAgo < 24) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Borrador recuperado',
                        html: 'Tienes un borrador guardado hace <strong>' + hoursAgo + ' horas</strong>.<br>Deseas restaurarlo?',
                        showCancelButton: true,
                        confirmButtonText: 'Si, restaurar',
                        cancelButtonText: 'No, empezar de cero',
                        confirmButtonColor: '#bd9751',
                    }).then(result => {
                        if (result.isConfirmed) {
                            restoreFromLocal(data);
                        } else {
                            localStorage.removeItem(STORAGE_KEY);
                        }
                    });
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            }
        } catch(e) {}
    }

    // ============================================================
    // AUTOGUARDADO SERVIDOR (cada 60s)
    // ============================================================
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

    // --- Botones Camara / Galeria ---
    document.addEventListener('click', function(e) {
        const cameraBtn = e.target.closest('.btn-photo-camera');
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!cameraBtn && !galleryBtn) return;

        const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');

        input.removeAttribute('capture');
        input.click();
    });

    // --- Preview de fotos al seleccionar/tomar ---
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        const input = e.target;
        const group = input.closest('.photo-input-group');
        const previewDiv = group ? group.querySelector('.preview-img') : null;
        if (!previewDiv) return;

        previewDiv.innerHTML = '';
        if (input.files && input.files[0]) {
            // Para inputs multiple, mostrar conteo
            if (input.files.length > 1) {
                previewDiv.innerHTML = '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> ' + input.files.length + ' fotos seleccionadas</div>';
            } else {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer; border:2px solid #28a745;">' +
                        '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    });
});
</script>
