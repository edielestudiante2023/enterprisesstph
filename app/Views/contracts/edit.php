<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            margin-top: 30px;
        }
        .section-header:first-child {
            margin-top: 0;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/contracts') ?>">
                <i class="fas fa-file-contract"></i> Gestión de Contratos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('/contracts/view/' . $contract['id_contrato']) ?>">
                    <i class="fas fa-arrow-left"></i> Volver al Contrato
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">
                <i class="fas fa-edit text-warning"></i> Editar Contrato #<?= esc($contract['numero_contrato'] ?? $contract['id_contrato']) ?>
            </h2>

            <!-- Mensajes Flash -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('contracts/update/' . $contract['id_contrato']) ?>" method="POST" id="contractForm">
                <?= csrf_field() ?>

                <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Básica del Contrato</h5>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_cliente" class="form-label required-field">Cliente</label>
                        <select class="form-select" id="id_cliente" name="id_cliente" required>
                            <option value="">Seleccione un cliente...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id_cliente'] ?>"
                                        <?= ($contract['id_cliente'] == $client['id_cliente']) ? 'selected' : '' ?>>
                                    <?= esc($client['nombre_cliente']) ?> - <?= esc($client['nit_cliente']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tipo_contrato" class="form-label required-field">Tipo de Contrato</label>
                        <select class="form-select" id="tipo_contrato" name="tipo_contrato" required>
                            <option value="inicial" <?= ($contract['tipo_contrato'] ?? '') === 'inicial' ? 'selected' : '' ?>>Inicial</option>
                            <option value="renovacion" <?= ($contract['tipo_contrato'] ?? '') === 'renovacion' ? 'selected' : '' ?>>Renovación</option>
                            <option value="ampliacion" <?= ($contract['tipo_contrato'] ?? '') === 'ampliacion' ? 'selected' : '' ?>>Ampliación</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label required-field">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                               value="<?= esc($contract['fecha_inicio'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin" class="form-label required-field">Fecha de Finalización</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                               value="<?= esc($contract['fecha_fin'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="valor_contrato" class="form-label required-field">Valor Total del Contrato</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="valor_contrato" name="valor_contrato"
                                   value="<?= esc($contract['valor_contrato'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="valor_mensual" class="form-label">Valor Mensual</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="valor_mensual" name="valor_mensual"
                                   value="<?= esc($contract['valor_mensual'] ?? '') ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="numero_cuotas" class="form-label">Número de Cuotas</label>
                        <input type="number" class="form-control" id="numero_cuotas" name="numero_cuotas"
                               value="<?= esc($contract['numero_cuotas'] ?? '') ?>" min="1">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="frecuencia_visitas" class="form-label">Frecuencia de Visitas</label>
                        <select class="form-select" id="frecuencia_visitas" name="frecuencia_visitas">
                            <?php $freq = $contract['frecuencia_visitas'] ?? 'MENSUAL'; ?>
                            <option value="MENSUAL" <?= $freq === 'MENSUAL' ? 'selected' : '' ?>>Mensual</option>
                            <option value="BIMENSUAL" <?= $freq === 'BIMENSUAL' ? 'selected' : '' ?>>Bimensual</option>
                            <option value="TRIMESTRAL" <?= $freq === 'TRIMESTRAL' ? 'selected' : '' ?>>Trimestral</option>
                            <option value="SEMESTRAL" <?= $freq === 'SEMESTRAL' ? 'selected' : '' ?>>Semestral</option>
                            <option value="ANUAL" <?= $freq === 'ANUAL' ? 'selected' : '' ?>>Anual</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <?php $estado = $contract['estado'] ?? 'activo'; ?>
                            <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="vencido" <?= $estado === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                            <option value="cancelado" <?= $estado === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            <option value="renovado" <?= $estado === 'renovado' ? 'selected' : '' ?>>Renovado</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                              placeholder="Notas adicionales sobre el contrato..."><?= esc($contract['observaciones'] ?? '') ?></textarea>
                </div>

                <!-- SECCIÓN: CLÁUSULA CUARTA - DURACIÓN -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Cláusula Cuarta - Duración y Plazo de Ejecución</h5>
                </div>

                <!-- Generador de Cláusula con IA -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-robot"></i> Generar Cláusula con Inteligencia Artificial</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="instrucciones_ia" class="form-label">
                                <i class="fas fa-lightbulb text-warning"></i> Instrucciones para la IA
                            </label>
                            <textarea class="form-control" id="instrucciones_ia" rows="4"
                                      placeholder="Describa las condiciones específicas del contrato. Ejemplos:
- Anticipo del 50% al inicio
- Plazo de ejecución de 30 días para diseño documental
- Contrato de 6 meses
- Sin prórroga automática
- Incluir condiciones para terminación anticipada"></textarea>
                            <small class="text-muted">
                                Escriba las condiciones que desea incluir y la IA generará el texto legal completo.
                            </small>
                        </div>
                        <button type="button" id="btnGenerarIA" class="btn btn-primary">
                            <i class="fas fa-magic"></i> Generar con IA
                        </button>
                        <span id="iaStatus" class="ms-3 text-muted" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Generando cláusula...
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="clausula_cuarta_duracion" class="form-label">
                        <i class="fas fa-file-contract"></i> Texto de la Cláusula Cuarta
                    </label>
                    <textarea class="form-control" id="clausula_cuarta_duracion" name="clausula_cuarta_duracion" rows="10"><?= esc($contract['clausula_cuarta_duracion'] ?? '') ?></textarea>
                    <small class="text-muted">
                        Este texto aparecerá en el PDF del contrato como la CLÁUSULA CUARTA.
                    </small>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('/contracts/view/' . $contract['id_contrato']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inicializar Select2 en el selector de clientes
        $(document).ready(function() {
            $('#id_cliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar cliente por nombre o NIT...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron clientes";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        });

        // Calcular valor mensual automáticamente
        document.getElementById('valor_contrato').addEventListener('input', calcularValorMensual);
        document.getElementById('fecha_inicio').addEventListener('change', calcularValorMensual);
        document.getElementById('fecha_fin').addEventListener('change', calcularValorMensual);

        function calcularValorMensual() {
            const valorTotal = parseFloat(document.getElementById('valor_contrato').value) || 0;
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;

            if (valorTotal > 0 && fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);

                // Calcular diferencia en meses
                const meses = (fin.getFullYear() - inicio.getFullYear()) * 12 +
                             (fin.getMonth() - inicio.getMonth());

                if (meses > 0) {
                    const valorMensual = Math.round(valorTotal / meses);
                    document.getElementById('valor_mensual').value = valorMensual;
                    document.getElementById('numero_cuotas').value = meses;
                }
            }
        }

        // Validar que la fecha de fin sea posterior a la fecha de inicio
        document.getElementById('contractForm').addEventListener('submit', function(e) {
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
            const fechaFin = new Date(document.getElementById('fecha_fin').value);

            if (fechaFin <= fechaInicio) {
                e.preventDefault();
                alert('La fecha de finalización debe ser posterior a la fecha de inicio');
                return false;
            }
        });

        // Generación de Cláusula con IA
        document.getElementById('btnGenerarIA').addEventListener('click', function() {
            const instrucciones = document.getElementById('instrucciones_ia').value.trim();

            if (!instrucciones) {
                alert('Por favor ingrese las instrucciones para generar la cláusula');
                return;
            }

            const clienteSelect = document.getElementById('id_cliente');
            const nombreCliente = clienteSelect.options[clienteSelect.selectedIndex]?.text || '';
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const valorContrato = document.getElementById('valor_contrato').value;
            const tipoContrato = document.getElementById('tipo_contrato').value;

            const btnGenerarIA = document.getElementById('btnGenerarIA');
            const iaStatus = document.getElementById('iaStatus');
            btnGenerarIA.disabled = true;
            iaStatus.style.display = 'inline';

            fetch('<?= base_url('/contracts/generate-clausula-ia') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    'instrucciones': instrucciones,
                    'nombre_cliente': nombreCliente,
                    'fecha_inicio': fechaInicio,
                    'fecha_fin': fechaFin,
                    'valor_contrato': valorContrato,
                    'tipo_contrato': tipoContrato
                })
            })
            .then(response => response.json())
            .then(data => {
                btnGenerarIA.disabled = false;
                iaStatus.style.display = 'none';

                if (data.success) {
                    document.getElementById('clausula_cuarta_duracion').value = data.clausula;
                    const tokensMsg = data.tokens_usados ? ` (${data.tokens_usados} tokens usados)` : '';
                    alert('¡Cláusula generada exitosamente!' + tokensMsg + '\n\nRevise y ajuste el texto si es necesario.');
                } else {
                    alert('Error: ' + (data.message || 'No se pudo generar la cláusula'));
                }
            })
            .catch(error => {
                btnGenerarIA.disabled = false;
                iaStatus.style.display = 'none';
                console.error('Error:', error);
                alert('Error de conexión. Por favor intente de nuevo.');
            });
        });
    </script>
</body>
</html>
