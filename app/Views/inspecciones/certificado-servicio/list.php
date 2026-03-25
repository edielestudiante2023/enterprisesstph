<?php
/**
 * @var array $cfg   ['nombre', 'slug', 'icon', 'detailreport']
 * @var int   $tipo
 * @var array $registros
 */
?>
<div class="container-fluid px-3 mt-2">
    <?php if ($flash = session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= esc($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mt-2" style="font-size:14px;"><?= esc($flash) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas <?= esc($cfg['icon']) ?>"></i> <?= esc($cfg['nombre']) ?></h6>
        <a href="<?= base_url('/inspecciones/' . $cfg['slug'] . '/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto;padding:8px 16px;">
            <i class="fas fa-plus"></i> Registrar
        </a>
    </div>

    <div class="table-responsive">
    <table id="tablaInsp" class="table table-sm table-hover" style="width:100%">
        <thead>
            <tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Observaciones</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $i => $r):
            $f = $r['fecha_servicio'];
        ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($r['nombre_cliente'] ?? '') ?></td>
            <td data-order="<?= esc($f) ?>"><?= date('d/m/Y', strtotime($f)) ?></td>
            <td><?= esc(mb_strimwidth($r['observaciones'] ?? '', 0, 50, '…')) ?>
                <?php if (!empty($r['id_vencimiento'])): ?>
                    <span class="badge bg-success" style="font-size:10px;">Venc. cerrado</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="<?= base_url('/inspecciones/' . $cfg['slug'] . '/view/' . $r['id']) ?>" class="btn btn-xs btn-outline-secondary" style="padding:2px 7px;font-size:12px;" title="Ver"><i class="fas fa-eye"></i></a>
                <?php if (!empty($r['archivo'])): ?>
                <a href="<?= base_url($r['archivo']) ?>" target="_blank" class="btn btn-xs btn-outline-dark" style="padding:2px 7px;font-size:12px;" title="Certificado"><i class="fas fa-file-alt"></i></a>
                <?php endif; ?>
                <button class="btn btn-xs btn-outline-danger btn-del" data-id="<?= $r['id'] ?>" data-nombre="<?= esc($r['nombre_cliente'] ?? '') ?>" style="padding:2px 7px;font-size:12px;"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php $deleteBase = base_url('/inspecciones/' . $cfg['slug'] . '/delete/'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#tablaInsp').DataTable({responsive:true,language:{url:'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'},pageLength:25,order:[[2,'desc']],columnDefs:[{orderable:false,targets:[0,4]}]});
    $('#tablaInsp').on('click', '.btn-del', function() {
        var id = this.dataset.id, n = this.dataset.nombre;
        Swal.fire({title:'¿Eliminar registro?',html:'Se eliminará el registro de <strong>'+n+'</strong>',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545',confirmButtonText:'Sí, eliminar',cancelButtonText:'Cancelar'})
        .then(function(r) { if (r.isConfirmed) window.location.href = '<?= $deleteBase ?>'+id; });
    });
});
</script>
