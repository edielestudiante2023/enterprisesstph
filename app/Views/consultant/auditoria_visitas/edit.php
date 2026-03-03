<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Ciclo de Visita</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 80px; }
        .card-header-custom {
            background: linear-gradient(135deg, #1c2437, #2c3e50);
            color: #bd9751;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>
    <?= view('partials/navbar') ?>

    <div class="container" style="max-width: 700px;">
        <div class="card shadow-sm">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Editar Ciclo de Visita</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 p-3" style="background:#f0f4f8; border-radius:8px; font-size:13px;">
                    <strong>Cliente:</strong> <?= esc($cliente['nombre_cliente'] ?? '—') ?><br>
                    <strong>Consultor:</strong> <?= esc($consultor['nombre_consultor'] ?? '—') ?>
                </div>

                <form action="/consultant/auditoria-visitas/update/<?= $ciclo['id'] ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold" style="font-size:13px;">Mes Esperado</label>
                            <select name="mes_esperado" class="form-control form-control-sm">
                                <?php foreach ($meses as $num => $nombre): ?>
                                    <option value="<?= $num ?>" <?= $ciclo['mes_esperado'] == $num ? 'selected' : '' ?>><?= $nombre ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold" style="font-size:13px;">Año</label>
                            <input type="number" name="anio" class="form-control form-control-sm" value="<?= $ciclo['anio'] ?>" min="2024" max="2030">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold" style="font-size:13px;">Estándar</label>
                            <select name="estandar" class="form-control form-control-sm">
                                <?php foreach (['Mensual','Bimensual','Trimestral','Proyecto'] as $e): ?>
                                    <option value="<?= $e ?>" <?= $ciclo['estandar'] == $e ? 'selected' : '' ?>><?= $e ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold" style="font-size:13px;">Fecha Agendada</label>
                            <input type="date" name="fecha_agendada" class="form-control form-control-sm" value="<?= $ciclo['fecha_agendada'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold" style="font-size:13px;">Fecha Acta</label>
                            <input type="date" name="fecha_acta" class="form-control form-control-sm" value="<?= $ciclo['fecha_acta'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold" style="font-size:13px;">Estatus Agenda</label>
                            <select name="estatus_agenda" class="form-control form-control-sm">
                                <?php foreach (['pendiente','cumple','incumple'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $ciclo['estatus_agenda'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold" style="font-size:13px;">Estatus Mes</label>
                            <select name="estatus_mes" class="form-control form-control-sm">
                                <?php foreach (['pendiente','cumple','incumple'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $ciclo['estatus_mes'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold" style="font-size:13px;">Observaciones</label>
                        <textarea name="observaciones" class="form-control form-control-sm" rows="3"><?= esc($ciclo['observaciones'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/consultant/auditoria-visitas" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
