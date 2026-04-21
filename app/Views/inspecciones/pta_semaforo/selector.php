<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas fa-tachometer-alt"></i> Semáforo PTA</h6>
        <a href="<?= base_url('inspecciones') ?>" class="btn btn-sm btn-outline-secondary" style="padding:4px 10px;font-size:12px;">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card border-0 mb-3" style="background:#f8f9fa; border-radius:12px;">
        <div class="card-body">
            <p class="text-muted small mb-3">
                <i class="fas fa-info-circle"></i>
                Selecciona un cliente para analizar el cumplimiento de su Plan de Trabajo Anual frente a las inspecciones ejecutadas.
            </p>

            <form method="get" id="formSelector" action="" onsubmit="return irADetalle(event);">
                <div class="mb-3">
                    <label for="idCliente" class="form-label small fw-bold">Cliente</label>
                    <select id="idCliente" name="id_cliente" class="form-select" required style="width:100%;">
                        <option value=""></option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= (int) $c['id_cliente'] ?>">
                                <?= esc($c['nombre_cliente']) ?><?php if (!empty($c['nit_cliente'])): ?> — NIT <?= esc($c['nit_cliente']) ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="anio" class="form-label small fw-bold">Año</label>
                    <select id="anio" name="anio" class="form-select" style="max-width:180px;">
                        <?php $actual = (int) date('Y'); for ($y = $actual; $y >= $actual - 4; $y--): ?>
                            <option value="<?= $y ?>" <?= $y === (int) $anio ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-pwa-primary" style="width:auto;padding:8px 20px;background:#2e7d4f;border-color:#2e7d4f;color:#fff;">
                    <i class="fas fa-tachometer-alt"></i> Ver semáforo
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#idCliente').select2({
        placeholder: 'Buscar cliente por nombre o NIT...',
        allowClear: true,
        width: '100%'
    });
});

function irADetalle(e) {
    e.preventDefault();
    var id = document.getElementById('idCliente').value;
    var anio = document.getElementById('anio').value;
    if (!id) {
        Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', timer: 1800, showConfirmButton: false });
        return false;
    }
    window.location.href = '<?= base_url('inspecciones/pta-semaforo/') ?>' + id + '?anio=' + encodeURIComponent(anio);
    return false;
}
</script>
