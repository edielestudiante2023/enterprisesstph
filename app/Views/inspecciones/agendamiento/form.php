<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0"><?= $agendamiento ? 'Editar' : 'Nuevo' ?> Agendamiento</h6>
        <a href="/inspecciones/agendamiento" class="btn btn-sm btn-outline-dark" style="width:auto;">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Errores de validación -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger py-2" style="font-size:13px;">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <div><?= esc($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= $agendamiento ? '/inspecciones/agendamiento/update/' . $agendamiento['id'] : '/inspecciones/agendamiento/store' ?>" method="POST">
        <?= csrf_field() ?>

        <!-- Cliente -->
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:14px;">Cliente *</label>
            <?php if ($agendamiento): ?>
                <input type="hidden" name="id_cliente" value="<?= $agendamiento['id_cliente'] ?>">
                <?php
                    $nombreSel = '';
                    foreach ($clientes as $c) {
                        if ($c['id_cliente'] == $agendamiento['id_cliente']) { $nombreSel = $c['nombre_cliente']; break; }
                    }
                ?>
                <input type="text" class="form-control" value="<?= esc($nombreSel) ?>" disabled>
            <?php else: ?>
                <select name="id_cliente" id="selectCliente" class="form-select" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>" <?= old('id_cliente') == $c['id_cliente'] ? 'selected' : '' ?>>
                            <?= esc($c['nombre_cliente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <!-- Info del cliente (se llena por AJAX) -->
        <div id="clienteInfo" class="mb-3" style="display:none;">
            <div class="card border-0" style="background: #f0f4f8; border-radius: 10px;">
                <div class="card-body py-2 px-3" style="font-size: 13px;">
                    <div class="row">
                        <div class="col-6">
                            <strong>Última visita:</strong>
                            <div id="infoUltimaVisita" class="text-muted">—</div>
                        </div>
                        <div class="col-6">
                            <strong>Fecha sugerida:</strong>
                            <div id="infoFechaSugerida" class="text-success fw-bold">—</div>
                        </div>
                    </div>
                    <div class="mt-1">
                        <strong>Correo:</strong> <span id="infoCorreo" class="text-muted">—</span>
                    </div>
                    <div>
                        <strong>Dirección:</strong> <span id="infoDireccion" class="text-muted">—</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fecha y hora -->
        <div class="row">
            <div class="col-6 mb-3">
                <label class="form-label fw-bold" style="font-size:14px;">Fecha *</label>
                <input type="date" name="fecha_visita" class="form-control" id="inputFecha"
                       value="<?= old('fecha_visita', $agendamiento['fecha_visita'] ?? '') ?>" required>
            </div>
            <div class="col-6 mb-3">
                <label class="form-label fw-bold" style="font-size:14px;">Hora *</label>
                <input type="time" name="hora_visita" class="form-control"
                       value="<?= old('hora_visita', $agendamiento['hora_visita'] ?? '08:00') ?>" required>
            </div>
        </div>

        <!-- Frecuencia -->
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:14px;">Frecuencia *</label>
            <select name="frecuencia" class="form-select" required>
                <?php
                $freq = old('frecuencia', $agendamiento['frecuencia'] ?? 'mensual');
                ?>
                <option value="mensual" <?= $freq === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                <option value="bimensual" <?= $freq === 'bimensual' ? 'selected' : '' ?>>Bimensual</option>
                <option value="trimestral" <?= $freq === 'trimestral' ? 'selected' : '' ?>>Trimestral</option>
            </select>
        </div>

        <!-- Preparación cliente -->
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:14px;">Preparación del cliente</label>
            <textarea name="preparacion_cliente" class="form-control" rows="2" placeholder="Notas sobre preparación del cliente..."><?= old('preparacion_cliente', $agendamiento['preparacion_cliente'] ?? '') ?></textarea>
        </div>

        <!-- Observaciones -->
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:14px;">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2" placeholder="Observaciones adicionales..."><?= old('observaciones', $agendamiento['observaciones'] ?? '') ?></textarea>
        </div>

        <!-- Enviar invitación -->
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="enviar_invitacion" value="1" id="chkEnviar"
                <?= !$agendamiento ? 'checked' : '' ?>>
            <label class="form-check-label" for="chkEnviar" style="font-size:14px;">
                <i class="fas fa-paper-plane me-1"></i> Enviar invitación de calendario al guardar
            </label>
        </div>

        <!-- Botón guardar -->
        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-pwa-primary">
                <i class="fas fa-save me-1"></i>
                <?= $agendamiento ? 'Actualizar' : 'Crear' ?> Agendamiento
            </button>
        </div>
    </form>
</div>

<script>
// AJAX: Cargar info del cliente al seleccionar
document.getElementById('selectCliente')?.addEventListener('change', function() {
    const idCliente = this.value;
    const infoDiv = document.getElementById('clienteInfo');

    if (!idCliente) {
        infoDiv.style.display = 'none';
        return;
    }

    fetch('/inspecciones/agendamiento/api/cliente-info/' + idCliente)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                infoDiv.style.display = '';
                document.getElementById('infoUltimaVisita').textContent = data.ultima_visita
                    ? new Date(data.ultima_visita).toLocaleDateString('es-CO')
                    : 'Sin visitas previas';
                document.getElementById('infoFechaSugerida').textContent = new Date(data.fecha_sugerida).toLocaleDateString('es-CO');
                document.getElementById('infoCorreo').textContent = data.correo_cliente || 'No configurado';
                document.getElementById('infoDireccion').textContent = (data.direccion || '') + (data.ciudad ? ', ' + data.ciudad : '') || 'No configurada';

                // Auto-fill fecha sugerida
                document.getElementById('inputFecha').value = data.fecha_sugerida;
            }
        });
});
</script>
