<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .contract-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.1rem;
            color: #212529;
            margin-bottom: 15px;
        }
        .timeline-item {
            border-left: 3px solid #667eea;
            padding-left: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            width: 15px;
            height: 15px;
            background: #667eea;
            border-radius: 50%;
            position: absolute;
            left: -9px;
            top: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/contracts') ?>">
                <i class="fas fa-arrow-left"></i> Volver a Contratos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('/listClients') ?>">
                    <i class="fas fa-users"></i> Clientes
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Toast Container -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="toast align-items-center text-bg-success border-0 show" role="alert" data-bs-autohide="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('warning')): ?>
                <div class="toast align-items-center text-bg-warning border-0 show" role="alert" data-bs-autohide="true" data-bs-delay="7000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('warning') ?>
                        </div>
                        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="toast align-items-center text-bg-danger border-0 show" role="alert" data-bs-autohide="true" data-bs-delay="7000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-times-circle me-2"></i><?= session()->getFlashdata('error') ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($contract)): ?>
            <!-- Encabezado del Contrato -->
            <div class="contract-header">
                <div class="row">
                    <div class="col-md-8">
                        <h2><i class="fas fa-file-contract"></i> <?= htmlspecialchars($contract['numero_contrato']) ?></h2>
                        <p class="mb-0"><?= htmlspecialchars($contract['nombre_cliente']) ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php
                            $estadoBadge = [
                                'activo' => 'success',
                                'vencido' => 'danger',
                                'cancelado' => 'secondary'
                            ];
                        ?>
                        <h3>
                            <span class="badge bg-<?= $estadoBadge[$contract['estado']] ?? 'secondary' ?>">
                                <?= ucfirst($contract['estado']) ?>
                            </span>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Columna Izquierda -->
                <div class="col-md-8">
                    <!-- Información del Contrato -->
                    <div class="info-card">
                        <h4 class="mb-4"><i class="fas fa-info-circle"></i> Información del Contrato</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Número de Contrato</div>
                                <div class="info-value"><?= htmlspecialchars($contract['numero_contrato']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Tipo de Contrato</div>
                                <div class="info-value">
                                    <?php
                                        $tipoBadge = [
                                            'inicial' => 'primary',
                                            'renovacion' => 'info',
                                            'ampliacion' => 'warning'
                                        ];
                                    ?>
                                    <span class="badge bg-<?= $tipoBadge[$contract['tipo_contrato']] ?? 'secondary' ?>">
                                        <?= ucfirst($contract['tipo_contrato']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Frecuencia de Visitas</div>
                                <div class="info-value">
                                    <?php
                                        $frecuencia = strtolower($contract['frecuencia_visitas'] ?? '');
                                        $frecuenciaBadge = 'secondary';
                                        if (strpos($frecuencia, 'mensual') !== false && strpos($frecuencia, 'bimensual') === false) {
                                            $frecuenciaBadge = 'warning';
                                        } elseif (strpos($frecuencia, 'bimensual') !== false) {
                                            $frecuenciaBadge = 'info';
                                        } elseif (strpos($frecuencia, 'trimestral') !== false) {
                                            $frecuenciaBadge = 'purple';
                                        }
                                    ?>
                                    <span class="badge bg-<?= $frecuenciaBadge ?>" style="<?= $frecuenciaBadge === 'purple' ? 'background-color: #6f42c1 !important;' : '' ?>">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        <?= htmlspecialchars($contract['frecuencia_visitas'] ?? 'No definida') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Consultor Responsable</div>
                                <div class="info-value">
                                    <?php if (!empty($contract['nombre_consultor'])): ?>
                                        <i class="fas fa-user-tie text-primary"></i>
                                        <?= htmlspecialchars($contract['nombre_consultor']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">No asignado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Fecha de Inicio</div>
                                <div class="info-value">
                                    <i class="fas fa-calendar-plus text-success"></i>
                                    <?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Fecha de Finalización</div>
                                <div class="info-value">
                                    <i class="fas fa-calendar-times text-danger"></i>
                                    <?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Duración del Contrato</div>
                                <div class="info-value">
                                    <?php
                                        $inicio = new DateTime($contract['fecha_inicio']);
                                        $fin = new DateTime($contract['fecha_fin']);
                                        $diff = $inicio->diff($fin);
                                        $meses = ($diff->y * 12) + $diff->m;
                                    ?>
                                    <i class="fas fa-clock"></i> <?= $meses ?> meses
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Días Restantes</div>
                                <div class="info-value">
                                    <?php
                                        $hoy = new DateTime();
                                        $diasRestantes = (int)$hoy->diff($fin)->format('%r%a');
                                        $alertClass = 'success';
                                        if ($diasRestantes < 0) {
                                            $alertClass = 'danger';
                                        } elseif ($diasRestantes <= 15) {
                                            $alertClass = 'warning';
                                        }
                                    ?>
                                    <span class="badge bg-<?= $alertClass ?>">
                                        <?= $diasRestantes ?> días
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if ($contract['valor_contrato']): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-label">Valor del Contrato</div>
                                    <div class="info-value">
                                        <i class="fas fa-dollar-sign text-success"></i>
                                        $<?= number_format($contract['valor_contrato'], 0, ',', '.') ?> COP
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($contract['observaciones']): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-label">Observaciones</div>
                                    <div class="info-value">
                                        <?= nl2br(htmlspecialchars($contract['observaciones'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Información del Cliente -->
                    <div class="info-card">
                        <h4 class="mb-4"><i class="fas fa-building"></i> Información del Cliente</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Nombre del Cliente</div>
                                <div class="info-value"><?= htmlspecialchars($contract['nombre_cliente']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">NIT</div>
                                <div class="info-value"><?= htmlspecialchars($contract['nit_cliente']) ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Correo Electrónico</div>
                                <div class="info-value">
                                    <a href="mailto:<?= htmlspecialchars($contract['correo_cliente']) ?>">
                                        <?= htmlspecialchars($contract['correo_cliente']) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Teléfono</div>
                                <div class="info-value"><?= htmlspecialchars($contract['telefono_1_cliente']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha -->
                <div class="col-md-4">
                    <!-- Acciones -->
                    <div class="info-card">
                        <h5 class="mb-3"><i class="fas fa-tasks"></i> Acciones</h5>

                        <!-- Generar y Enviar Contrato PDF -->
                        <a href="<?= base_url('/contracts/edit-contract-data/' . $contract['id_contrato']) ?>"
                           class="btn btn-<?= isset($contract['contrato_generado']) && $contract['contrato_generado'] ? 'secondary' : 'warning' ?> w-100 mb-2">
                            <i class="fas fa-file-pdf"></i>
                            <?= isset($contract['contrato_generado']) && $contract['contrato_generado'] ? 'Regenerar Contrato PDF' : 'Generar Contrato PDF' ?>
                        </a>

                        <?php if (isset($contract['contrato_generado']) && $contract['contrato_generado']): ?>
                            <a href="<?= base_url('/contracts/download-pdf/' . $contract['id_contrato']) ?>"
                               class="btn btn-outline-primary w-100 mb-2" target="_blank">
                                <i class="fas fa-download"></i> Descargar PDF Generado
                            </a>
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-check-circle text-success"></i>
                                Generado: <?= isset($contract['fecha_generacion_contrato']) ? date('d/m/Y H:i', strtotime($contract['fecha_generacion_contrato'])) : 'N/A' ?>
                            </small>
                        <?php endif; ?>

                        <?php if ($contract['estado'] === 'activo'): ?>
                            <a href="<?= base_url('/contracts/renew/' . $contract['id_contrato']) ?>"
                               class="btn btn-success w-100 mb-2">
                                <i class="fas fa-sync"></i> Renovar Contrato
                            </a>
                        <?php endif; ?>

                        <a href="<?= base_url('/contracts/client-history/' . $contract['id_cliente']) ?>"
                           class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-history"></i> Ver Historial Completo
                        </a>

                        <!-- Descargar Documentación del Contrato -->
                        <a href="<?= base_url('/contracts/documentacion/' . $contract['id_contrato']) ?>"
                           class="btn btn-success w-100 mb-2">
                            <i class="fas fa-folder-open"></i> Descargar Documentación
                        </a>

                        <a href="<?= base_url('/editClient/' . $contract['id_cliente']) ?>"
                           class="btn btn-info w-100 mb-2">
                            <i class="fas fa-user-edit"></i> Editar Cliente
                        </a>

                        <?php if ($contract['estado'] === 'activo'): ?>
                            <a href="<?= base_url('/contracts/cancel/' . $contract['id_contrato']) ?>"
                               class="btn btn-danger w-100 mb-2"
                               onclick="return confirm('¿Estás seguro de cancelar este contrato?')">
                                <i class="fas fa-ban"></i> Cancelar Contrato
                            </a>
                        <?php endif; ?>

                        <a href="<?= base_url('/contracts') ?>" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-left"></i> Volver a Lista
                        </a>
                    </div>

                    <!-- Firma Digital del Contrato -->
                    <div class="info-card">
                        <h5 class="mb-3"><i class="fas fa-pen-nib"></i> Firma Digital</h5>
                        <?php
                            $estadoFirma = $contract['estado_firma'] ?? 'sin_enviar';
                        ?>

                        <?php if ($estadoFirma === 'firmado'): ?>
                            <div class="alert alert-success py-2 mb-2">
                                <i class="fas fa-check-circle"></i> Contrato firmado por <?= esc($contract['firma_cliente_nombre'] ?? '') ?>
                                <br><small>CC: <?= esc($contract['firma_cliente_cedula'] ?? '') ?></small>
                                <br><small><?= !empty($contract['firma_cliente_fecha']) ? date('d/m/Y H:i', strtotime($contract['firma_cliente_fecha'])) : '' ?></small>
                                <?php if (!empty($contract['firma_cliente_ip'])): ?>
                                    <br><small class="text-muted">IP: <?= esc($contract['firma_cliente_ip']) ?></small>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($contract['ruta_pdf_contrato'])): ?>
                                <a href="<?= base_url($contract['ruta_pdf_contrato']) ?>" target="_blank"
                                   class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-file-pdf"></i> Descargar PDF Firmado
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($contract['firma_cliente_imagen'])): ?>
                                <button class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalFirmaCliente">
                                    <i class="fas fa-signature"></i> Ver Firma del Cliente
                                </button>
                            <?php endif; ?>

                            <button class="btn btn-outline-info w-100 mb-2" onclick="regenerarPDFConFirma()">
                                <i class="fas fa-sync-alt"></i> Regenerar PDF con Firma
                            </button>

                            <?php if (!empty($contract['codigo_verificacion'])): ?>
                                <hr class="my-2">
                                <a href="<?= base_url('contrato/certificado-pdf/' . $contract['id_contrato']) ?>"
                                   class="btn btn-outline-success w-100 mb-2" target="_blank">
                                    <i class="fas fa-certificate"></i> Descargar Certificado de Firma
                                </a>
                                <a href="<?= base_url('contrato/verificar/' . $contract['codigo_verificacion']) ?>"
                                   class="btn btn-outline-primary w-100 mb-2" target="_blank">
                                    <i class="fas fa-shield-alt"></i> Ver Certificado Publico
                                </a>
                            <?php endif; ?>

                        <?php elseif ($estadoFirma === 'pendiente_firma'): ?>
                            <div class="alert alert-warning py-2 mb-2">
                                <i class="fas fa-clock"></i> Pendiente de firma del cliente
                            </div>
                            <?php $linkFirma = base_url('contrato/firmar/' . ($contract['token_firma'] ?? '')); ?>
                            <div class="btn-group w-100 mb-2">
                                <button onclick="copiarLinkFirma()" class="btn btn-outline-info" title="Copiar enlace">
                                    <i class="fas fa-copy"></i> Copiar Link
                                </button>
                                <a href="https://wa.me/?text=<?= urlencode('Firme el contrato SST: ' . $linkFirma) ?>"
                                   target="_blank" class="btn btn-success" title="Enviar por WhatsApp">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            </div>
                            <button onclick="reenviarFirma()" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-redo"></i> Reenviar por Email
                            </button>

                        <?php else: ?>
                            <?php if (isset($contract['contrato_generado']) && $contract['contrato_generado']): ?>
                                <button onclick="enviarAFirmar()" class="btn w-100 mb-2 text-white"
                                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="fas fa-pen-nib"></i> Enviar a Firmar Digitalmente
                                </button>
                            <?php else: ?>
                                <div class="alert alert-info py-2 mb-2">
                                    <small><i class="fas fa-info-circle"></i> Primero genere el contrato PDF para habilitar la firma digital.</small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Resumen del Cliente -->
                    <?php if (isset($history)): ?>
                        <div class="info-card">
                            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Resumen del Cliente</h5>

                            <div class="info-label">Total de Contratos</div>
                            <div class="info-value">
                                <h4><?= $history['total_contracts'] ?? 0 ?></h4>
                            </div>

                            <div class="info-label">Total de Renovaciones</div>
                            <div class="info-value">
                                <h4><?= $history['total_renewals'] ?? 0 ?></h4>
                            </div>

                            <div class="info-label">Antigüedad del Cliente</div>
                            <div class="info-value">
                                <h4><?= $history['client_antiquity_years'] ?? 0 ?> años</h4>
                                <small class="text-muted">
                                    (<?= $history['client_antiquity_months'] ?? 0 ?> meses)
                                </small>
                            </div>

                            <?php if ($history['first_contract_date']): ?>
                                <div class="info-label">Cliente desde</div>
                                <div class="info-value">
                                    <?= date('d/m/Y', strtotime($history['first_contract_date'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- Modal: Ver Firma del Cliente -->
        <?php if (($contract['estado_firma'] ?? '') === 'firmado' && !empty($contract['firma_cliente_imagen'])): ?>
        <div class="modal fade" id="modalFirmaCliente" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-signature"></i> Firma Digital del Cliente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="<?= base_url($contract['firma_cliente_imagen']) ?>" alt="Firma del cliente" class="img-fluid" style="max-height: 200px; border: 1px solid #dee2e6; border-radius: 8px; padding: 10px;">
                        <hr>
                        <p class="mb-1"><strong><?= esc($contract['firma_cliente_nombre'] ?? '') ?></strong></p>
                        <p class="text-muted mb-1">CC: <?= esc($contract['firma_cliente_cedula'] ?? '') ?></p>
                        <p class="text-muted mb-0">
                            <small>Firmado el <?= !empty($contract['firma_cliente_fecha']) ? date('d/m/Y H:i:s', strtotime($contract['firma_cliente_fecha'])) : '' ?></small>
                            <?php if (!empty($contract['firma_cliente_ip'])): ?>
                                <br><small>IP: <?= esc($contract['firma_cliente_ip']) ?></small>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-danger">
                <h4>Contrato no encontrado</h4>
                <p>El contrato que buscas no existe o no tienes permisos para verlo.</p>
                <a href="<?= base_url('/contracts') ?>" class="btn btn-primary">
                    Volver a la lista de contratos
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Bootstrap toasts
        document.addEventListener('DOMContentLoaded', function() {
            var toastElList = document.querySelectorAll('.toast');
            toastElList.forEach(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
        });

        // === Firma Digital Functions ===
        function enviarAFirmar() {
            Swal.fire({
                title: 'Enviar a Firmar',
                text: 'Se enviará un correo al representante legal del cliente con el enlace para firmar el contrato digitalmente.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Enviando...',
                        text: 'Procesando solicitud de firma',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('<?= base_url('/contracts/enviar-firma') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            id_contrato: <?= $contract['id_contrato'] ?? 0 ?>
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Enviado', data.message || 'Solicitud de firma enviada correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo enviar la solicitud', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error de conexión: ' + error.message, 'error');
                    });
                }
            });
        }

        function copiarLinkFirma() {
            const link = '<?= base_url('contrato/firmar/' . ($contract['token_firma'] ?? '')) ?>';
            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enlace copiado',
                        text: 'El enlace de firma ha sido copiado al portapapeles',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = link;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                Swal.fire({
                    icon: 'success',
                    title: 'Enlace copiado',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }

        function reenviarFirma() {
            Swal.fire({
                title: 'Reenviar enlace de firma',
                text: 'Se generará un nuevo enlace y se enviará por correo al cliente. El enlace anterior quedará invalidado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, reenviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Reenviando...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('<?= base_url('/contracts/enviar-firma') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            id_contrato: <?= $contract['id_contrato'] ?? 0 ?>
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Reenviado', data.message || 'Enlace reenviado correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo reenviar', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error de conexión: ' + error.message, 'error');
                    });
                }
            });
        }

        function regenerarPDFConFirma() {
            Swal.fire({
                title: 'Regenerar PDF',
                text: 'Se regenerará el PDF del contrato incluyendo la firma digital del cliente.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, regenerar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Regenerando PDF...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('<?= base_url('/contracts/regenerar-pdf-firmado') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            id_contrato: <?= $contract['id_contrato'] ?? 0 ?>
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('PDF Regenerado', data.message || 'El PDF ha sido actualizado con la firma del cliente.', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo regenerar el PDF', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error de conexión: ' + error.message, 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>
