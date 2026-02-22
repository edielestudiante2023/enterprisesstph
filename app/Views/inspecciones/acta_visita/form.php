<?php
$isEdit = !empty($acta);
$action = $isEdit ? '/inspecciones/acta-visita/update/' . $acta['id'] : '/inspecciones/acta-visita/store';
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
                                            <?php foreach (['ADMINISTRADOR', 'CONSULTOR CYCLOID', 'VIGÍA SST', 'PRESIDENTE COPASST', 'REPRESENTANTE LEGAL', 'OTRO'] as $rol): ?>
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

            <!-- CARTERA -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secCartera">
                        Cartera
                    </button>
                </h2>
                <div id="secCartera" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <textarea name="cartera" class="form-control" rows="3" placeholder="Estado de cartera..."><?= esc($acta['cartera'] ?? '') ?></textarea>
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

            <!-- PRÓXIMA REUNIÓN -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secProxima">
                        Proxima Reunion
                    </button>
                </h2>
                <div id="secProxima" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="proxima_reunion_fecha" class="form-control"
                                    value="<?= $acta['proxima_reunion_fecha'] ?? '' ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hora</label>
                                <input type="time" name="proxima_reunion_hora" class="form-control"
                                    value="<?= $acta['proxima_reunion_hora'] ?? '' ?>">
                            </div>
                        </div>
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
                        <input type="file" name="fotos[]" class="form-control" accept="image/*" capture="environment" multiple>
                        <small class="text-muted">Puedes tomar fotos con la camara o seleccionar de la galeria</small>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Botones de acción -->
        <div class="mt-3 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-outline">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="ir_a_firmas" value="1" class="btn btn-pwa btn-pwa-primary" id="btnIrFirmas">
                <i class="fas fa-signature"></i> Guardar e ir a firmas
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // --- Select2 para clientes ---
    $.ajax({
        url: '/inspecciones/api/clientes',
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

            // Si ya hay cliente seleccionado, cargar temas abiertos
            if (clienteId) loadTemasAbiertos(clienteId);
        }
    });

    // Cargar temas abiertos al cambiar cliente
    $('#selectCliente').on('change', function() {
        const id = this.value;
        if (id) loadTemasAbiertos(id);
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
                    html += '<li>' + (m.descripcion_mantenimiento || 'Mantenimiento') + ' - Vence: ' + m.fecha_vencimiento + '</li>';
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
                    html += '<li>' + p.tarea_actividad + ' - ' + (p.responsable || '') + ' (' + p.conteo_dias + ' dias)</li>';
                });
                html += '</ul>';
            }

            container.innerHTML = html;
        });
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
        const roles = ['ADMINISTRADOR', 'CONSULTOR CYCLOID', 'VIGÍA SST', 'PRESIDENTE COPASST', 'REPRESENTANTE LEGAL', 'OTRO'];
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

    // --- Validación mínima antes de ir a firmas ---
    document.getElementById('btnIrFirmas').addEventListener('click', function(e) {
        const cliente = document.getElementById('selectCliente').value;
        const temas = document.querySelectorAll('.tema-row').length;
        const integrantes = document.querySelectorAll('.integrante-row').length;

        if (!cliente || temas === 0 || integrantes === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                html: 'Para ir a firmas necesitas al menos:<br><br>' +
                    (!cliente ? '- Seleccionar un cliente<br>' : '') +
                    (integrantes === 0 ? '- Agregar al menos 1 integrante<br>' : '') +
                    (temas === 0 ? '- Agregar al menos 1 tema<br>' : ''),
                confirmButtonColor: '#bd9751',
            });
        }
    });
});
</script>
