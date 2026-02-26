<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($mode === 'edit') ? 'Editar' : 'Nuevo' ?> Informe de Avances</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root { --primary-dark: #1c2437; --gold-primary: #bd9751; --gold-secondary: #d4af37; --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        body { background: var(--gradient-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .navbar-custom { background: #fff; box-shadow: 0 8px 32px rgba(28,36,55,0.15); padding: 15px 0; border-bottom: 2px solid var(--gold-primary); }
        .card-section { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-section .card-header { background: var(--primary-dark); color: #fff; border-radius: 16px 16px 0 0; font-weight: 600; }
        .card-section .card-header i { color: var(--gold-primary); }
        .btn-gold { background: var(--gold-primary); color: #fff; border: none; font-weight: 600; }
        .btn-gold:hover { background: var(--gold-secondary); color: #fff; }
        .btn-ia { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; font-weight: 600; }
        .btn-ia:hover { opacity: 0.9; color: #fff; }
        .metric-box { background: #f8f9fa; border-radius: 12px; padding: 15px; text-align: center; border: 1px solid #e9ecef; }
        .metric-box .value { font-size: 1.8rem; font-weight: 700; color: var(--primary-dark); }
        .metric-box .label { font-size: 0.85rem; color: #6c757d; }
        .progress-custom { height: 24px; border-radius: 12px; }
        .progress-custom .progress-bar { border-radius: 12px; font-weight: 600; font-size: 0.8rem; }
        .badge-estado { font-size: 0.95rem; padding: 6px 16px; }
        .soporte-group { border: 1px dashed #dee2e6; border-radius: 12px; padding: 15px; margin-bottom: 10px; }
        .img-preview { max-height: 120px; border-radius: 8px; margin-top: 8px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('informe-avances') ?>" style="color: var(--primary-dark);">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
            <span class="fw-bold" style="color: var(--primary-dark);">
                <i class="fas fa-chart-line me-2" style="color: var(--gold-primary);"></i>
                <?= ($mode === 'edit') ? 'Editar Informe' : 'Nuevo Informe de Avances' ?>
            </span>
        </div>
    </nav>

    <div class="container mt-3">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('msg') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <form id="formInforme" action="<?= base_url('informe-avances/' . ($mode === 'edit' ? 'update/' . $informe['id'] : 'store')) ?>" method="POST" enctype="multipart/form-data">

            <!-- SECCION 1: Datos Generales -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-building me-2"></i>1. Datos Generales</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Desde</label>
                            <input type="date" name="fecha_desde" id="fechaDesde" class="form-control" value="<?= esc($informe['fecha_desde'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Hasta</label>
                            <input type="date" name="fecha_hasta" id="fechaHasta" class="form-control" value="<?= esc($informe['fecha_hasta'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCION 2: Metricas -->
            <div class="card card-section">
                <div class="card-header py-3">
                    <i class="fas fa-chart-bar me-2"></i>2. Metricas del Periodo
                    <span id="metricasLoading" class="ms-2 d-none"><i class="fas fa-spinner fa-spin"></i> Calculando...</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="metric-box">
                                <div class="label">Puntaje Anterior</div>
                                <div class="value" id="displayPuntajeAnterior"><?= number_format($informe['puntaje_anterior'] ?? 0, 1) ?>%</div>
                                <input type="hidden" name="puntaje_anterior" id="puntajeAnterior" value="<?= esc($informe['puntaje_anterior'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-box">
                                <div class="label">Puntaje Actual</div>
                                <div class="value" id="displayPuntajeActual" style="color: var(--gold-primary);"><?= number_format($informe['puntaje_actual'] ?? 0, 1) ?>%</div>
                                <input type="hidden" name="puntaje_actual" id="puntajeActual" value="<?= esc($informe['puntaje_actual'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-box">
                                <div class="label">Diferencia Neta</div>
                                <div class="value" id="displayDiferencia"><?= number_format($informe['diferencia_neta'] ?? 0, 1) ?></div>
                                <input type="hidden" name="diferencia_neta" id="diferenciaNeta" value="<?= esc($informe['diferencia_neta'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-box">
                                <div class="label">Estado de Avance</div>
                                <span class="badge badge-estado mt-2" id="displayEstadoAvance"><?= esc($informe['estado_avance'] ?? 'ESTABLE') ?></span>
                                <input type="hidden" name="estado_avance" id="estadoAvance" value="<?= esc($informe['estado_avance'] ?? 'ESTABLE') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Indicador Plan de Trabajo</label>
                            <div class="progress progress-custom">
                                <div class="progress-bar bg-info" id="barPlanTrabajo" style="width: <?= ($informe['indicador_plan_trabajo'] ?? 0) ?>%">
                                    <?= number_format($informe['indicador_plan_trabajo'] ?? 0, 1) ?>%
                                </div>
                            </div>
                            <input type="hidden" name="indicador_plan_trabajo" id="indicadorPlanTrabajo" value="<?= esc($informe['indicador_plan_trabajo'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Indicador Capacitacion</label>
                            <div class="progress progress-custom">
                                <div class="progress-bar bg-success" id="barCapacitacion" style="width: <?= ($informe['indicador_capacitacion'] ?? 0) ?>%">
                                    <?= number_format($informe['indicador_capacitacion'] ?? 0, 1) ?>%
                                </div>
                            </div>
                            <input type="hidden" name="indicador_capacitacion" id="indicadorCapacitacion" value="<?= esc($informe['indicador_capacitacion'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Screenshots opcionales (colapsable) -->
                    <div class="mt-3">
                        <a data-bs-toggle="collapse" href="#collapseScreenshots" class="text-muted small">
                            <i class="fas fa-camera me-1"></i>Subir screenshots de indicadores (opcional)
                        </a>
                        <div class="collapse mt-2" id="collapseScreenshots">
                            <div class="row g-3">
                                <?php foreach (['img_cumplimiento_estandares' => 'Cumplimiento Estandares', 'img_indicador_plan_trabajo' => 'Plan de Trabajo', 'img_indicador_capacitacion' => 'Capacitacion'] as $campo => $label): ?>
                                <div class="col-md-4">
                                    <label class="form-label small"><?= $label ?></label>
                                    <input type="file" name="<?= $campo ?>" class="form-control form-control-sm" accept="image/*">
                                    <?php if (!empty($informe[$campo])): ?>
                                        <img src="<?= base_url($informe[$campo]) ?>" class="img-preview mt-1">
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCION 3: Resumen de Avance -->
            <div class="card card-section">
                <div class="card-header py-3">
                    <i class="fas fa-file-alt me-2"></i>3. Resumen de Avance del Periodo
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" id="btnGenerarIA" class="btn btn-ia btn-sm" disabled>
                            <i class="fas fa-robot me-1"></i>Generar con IA
                            <span id="iaSpinner" class="d-none"><i class="fas fa-spinner fa-spin ms-1"></i></span>
                        </button>
                    </div>
                    <textarea name="resumen_avance" id="resumenAvance" class="form-control" rows="10" placeholder="Escriba el resumen de avance o genere con IA..."><?= esc($informe['resumen_avance'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 4: Actividades Cerradas en el Periodo -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-check-circle me-2"></i>4. Actividades PTA Cerradas en el Periodo</div>
                <div class="card-body">
                    <div id="tablaCerradas" class="mb-2"></div>
                    <textarea name="actividades_cerradas_periodo" id="actividadesCerradas" class="form-control" rows="5" placeholder="Se auto-pobla al seleccionar cliente..."><?= esc($informe['actividades_cerradas_periodo'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 5: Actividades Abiertas -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-exclamation-triangle me-2"></i>5. Actividades Abiertas (Compromisos)</div>
                <div class="card-body">
                    <textarea name="actividades_abiertas" id="actividadesAbiertas" class="form-control" rows="5" placeholder="Se auto-pobla al seleccionar cliente..."><?= esc($informe['actividades_abiertas'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECCION 6: Observaciones -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-comment-alt me-2"></i>6. Observaciones</div>
                <div class="card-body">
                    <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones adicionales..."><?= esc($informe['observaciones'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Enlaces opcionales -->
            <input type="hidden" name="enlace_dashboard" id="enlaceDashboard" value="<?= esc($informe['enlace_dashboard'] ?? '') ?>">
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-link me-2"></i>Enlaces</div>
                <div class="card-body">
                    <label class="form-label">URL del Acta de Visita (opcional)</label>
                    <input type="url" name="acta_visita_url" class="form-control" value="<?= esc($informe['acta_visita_url'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>

            <!-- SECCION 7: Soportes -->
            <div class="card card-section">
                <div class="card-header py-3"><i class="fas fa-paperclip me-2"></i>7. Soportes (hasta 4)</div>
                <div class="card-body">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="soporte-group">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Soporte <?= $i ?> - Titulo</label>
                                <input type="text" name="soporte_<?= $i ?>_texto" class="form-control" value="<?= esc($informe["soporte_{$i}_texto"] ?? '') ?>" placeholder="Descripcion del soporte">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Imagen</label>
                                <input type="file" name="soporte_<?= $i ?>_imagen" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="col-md-2 text-center">
                                <?php if (!empty($informe["soporte_{$i}_imagen"])): ?>
                                    <img src="<?= base_url($informe["soporte_{$i}_imagen"]) ?>" class="img-preview">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex gap-2 mb-5">
                <button type="submit" class="btn btn-gold btn-lg flex-fill">
                    <i class="fas fa-save me-2"></i>Guardar Borrador
                </button>
                <?php if ($mode === 'edit'): ?>
                <button type="button" id="btnFinalizar" class="btn btn-success btn-lg flex-fill">
                    <i class="fas fa-check-circle me-2"></i>Finalizar y Generar PDF
                </button>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($mode === 'edit'): ?>
        <form id="formFinalizar" action="<?= base_url('informe-avances/finalizar/' . $informe['id']) ?>" method="POST" class="d-none"></form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    const BASE = '<?= base_url() ?>/';
    const EDIT_MODE = <?= json_encode($mode === 'edit') ?>;
    const PRESELECT_CLIENTE = <?= json_encode($id_cliente) ?>;

    $(document).ready(function() {
        // Select2 AJAX clientes
        $('#selectCliente').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar cliente...',
            allowClear: true,
            ajax: {
                url: BASE + 'informe-avances/api/clientes',
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: data.map(function(c) {
                            return { id: c.id_cliente, text: c.nombre_cliente + ' (' + c.nit_cliente + ')' };
                        })
                    };
                }
            }
        });

        // Preselect client if editing
        if (PRESELECT_CLIENTE) {
            $.get(BASE + 'informe-avances/api/clientes', function(data) {
                var found = data.find(c => c.id_cliente == PRESELECT_CLIENTE);
                if (found) {
                    var opt = new Option(found.nombre_cliente + ' (' + found.nit_cliente + ')', found.id_cliente, true, true);
                    $('#selectCliente').append(opt).trigger('change');
                    if (!EDIT_MODE) loadMetricas(found.id_cliente);
                }
            });
        }

        // On client change, load metricas
        $('#selectCliente').on('change', function() {
            var clienteId = $(this).val();
            if (clienteId && !EDIT_MODE) {
                loadMetricas(clienteId);
            }
            $('#btnGenerarIA').prop('disabled', !clienteId);
        });

        // Enable IA button if editing
        if (EDIT_MODE && PRESELECT_CLIENTE) {
            $('#btnGenerarIA').prop('disabled', false);
        }

        function loadMetricas(clienteId) {
            var desde = $('#fechaDesde').val();
            var hasta = $('#fechaHasta').val();
            var params = '';
            if (desde) params += '?fecha_desde=' + desde;
            if (hasta) params += (params ? '&' : '?') + 'fecha_hasta=' + hasta;

            $('#metricasLoading').removeClass('d-none');

            $.get(BASE + 'informe-avances/api/metricas/' + clienteId + params, function(resp) {
                if (!resp.success) return;
                var d = resp.data;

                $('#puntajeAnterior').val(d.puntaje_anterior ?? '');
                $('#puntajeActual').val(d.puntaje_actual);
                $('#diferenciaNeta').val(d.diferencia_neta);
                $('#estadoAvance').val(d.estado_avance);
                $('#indicadorPlanTrabajo').val(d.indicador_plan_trabajo);
                $('#indicadorCapacitacion').val(d.indicador_capacitacion);
                $('#enlaceDashboard').val(d.enlace_dashboard);

                $('#displayPuntajeAnterior').text((d.puntaje_anterior ?? 0).toFixed(1) + '%');
                $('#displayPuntajeActual').text(d.puntaje_actual.toFixed(1) + '%');
                $('#displayDiferencia').text(d.diferencia_neta.toFixed(1));
                $('#displayEstadoAvance').text(d.estado_avance);

                // Color diferencia
                var diffColor = d.diferencia_neta > 0 ? '#28a745' : d.diferencia_neta < 0 ? '#dc3545' : '#6c757d';
                $('#displayDiferencia').css('color', diffColor);

                // Estado badge color
                var estadoBadgeClass = d.estado_avance.includes('SIGNIFICATIVO') ? 'bg-success' :
                    d.estado_avance.includes('MODERADO') ? 'bg-info' :
                    d.estado_avance.includes('ESTABLE') ? 'bg-warning text-dark' : 'bg-danger';
                $('#displayEstadoAvance').removeClass().addClass('badge badge-estado ' + estadoBadgeClass);

                // Progress bars
                $('#barPlanTrabajo').css('width', d.indicador_plan_trabajo + '%').text(d.indicador_plan_trabajo.toFixed(1) + '%');
                $('#barCapacitacion').css('width', d.indicador_capacitacion + '%').text(d.indicador_capacitacion.toFixed(1) + '%');

                // Actividades
                if (!$('#actividadesAbiertas').val()) {
                    $('#actividadesAbiertas').val(d.actividades_abiertas);
                }
                if (!$('#actividadesCerradas').val()) {
                    $('#actividadesCerradas').val(d.actividades_cerradas_periodo);
                }

                // Render tabla cerradas
                if (d.actividades_cerradas_raw && d.actividades_cerradas_raw.length > 0) {
                    var html = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Actividad</th><th>Numeral</th><th>PHVA</th><th>Responsable</th><th>Fecha Cierre</th></tr></thead><tbody>';
                    d.actividades_cerradas_raw.forEach(function(a) {
                        html += '<tr><td>'+esc(a.actividad_plandetrabajo || '')+'</td><td>'+esc(a.numeral_plandetrabajo || '')+'</td><td>'+esc(a.phva_plandetrabajo || '')+'</td><td>'+esc(a.responsable_sugerido_plandetrabajo || '')+'</td><td>'+a.fecha_transicion+'</td></tr>';
                    });
                    html += '</tbody></table>';
                    $('#tablaCerradas').html(html);
                }

                // Fecha desde sugerida
                if (d.fecha_desde_sugerida && !$('#fechaDesde').val()) {
                    $('#fechaDesde').val(d.fecha_desde_sugerida);
                }

            }).always(function() {
                $('#metricasLoading').addClass('d-none');
            });
        }

        // Generar resumen con IA
        $('#btnGenerarIA').on('click', function() {
            var clienteId = $('#selectCliente').val();
            var desde = $('#fechaDesde').val();
            var hasta = $('#fechaHasta').val();

            if (!clienteId || !desde || !hasta) {
                alert('Seleccione cliente y fechas primero');
                return;
            }

            $(this).prop('disabled', true);
            $('#iaSpinner').removeClass('d-none');

            $.post(BASE + 'informe-avances/generar-resumen', {
                id_cliente: clienteId,
                fecha_desde: desde,
                fecha_hasta: hasta
            }, function(resp) {
                if (resp.success) {
                    $('#resumenAvance').val(resp.resumen);
                } else {
                    alert('Error IA: ' + (resp.error || 'Error desconocido'));
                }
            }).fail(function() {
                alert('Error de conexion con el servidor');
            }).always(function() {
                $('#btnGenerarIA').prop('disabled', false);
                $('#iaSpinner').addClass('d-none');
            });
        });

        // Finalizar
        $('#btnFinalizar').on('click', function() {
            if (confirm('Finalizar el informe? Se generara el PDF y no podra editarse.')) {
                // Save first, then finalize
                var formData = new FormData($('#formInforme')[0]);
                $.ajax({
                    url: $('#formInforme').attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#formFinalizar').submit();
                    },
                    error: function() {
                        // If save fails, still try to finalize
                        $('#formFinalizar').submit();
                    }
                });
            }
        });
    });

    function esc(str) { var d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
    </script>
</body>
</html>
