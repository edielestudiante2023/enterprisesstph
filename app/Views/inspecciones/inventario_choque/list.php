<?php $SLUG = 'inventario-choque'; $TITULO = 'Inventario Fotos de Choque'; $ICONO = 'fa-clipboard-check'; ?>
<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas <?= $ICONO ?>"></i> <?= $TITULO ?></h6>
        <a href="<?= base_url('/inspecciones/'.$SLUG.'/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto;padding:8px 16px;"><i class="fas fa-plus"></i> Nuevo</a>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="table-responsive">
    <table id="tablaInv" class="table table-sm table-hover" style="width:100%">
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Progreso</th><th>Consultor</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($inventarios as $i => $r):
            $pct = $r['total_items'] > 0 ? round(($r['marcados'] / $r['total_items']) * 100) : 0;
            $barCls = $pct === 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : 'bg-warning');
        ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= esc($r['nombre_cliente'] ?? 'Sin cliente') ?></td>
            <td data-order="<?= esc($r['fecha_captura']) ?>"><?= date('d/m/Y', strtotime($r['fecha_captura'])) ?></td>
            <td style="min-width:140px;">
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:8px;">
                        <div class="progress-bar <?= $barCls ?>" style="width:<?= $pct ?>%;"></div>
                    </div>
                    <small style="font-size:11px; white-space:nowrap;"><?= $r['marcados'] ?>/<?= $r['total_items'] ?></small>
                </div>
            </td>
            <td><small><?= esc($r['nombre_consultor'] ?? '') ?></small></td>
            <td>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/view/'.$r['id']) ?>" class="btn btn-xs btn-outline-primary" style="padding:2px 7px;font-size:12px;" title="Capturar"><i class="fas fa-camera"></i></a>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/edit/'.$r['id']) ?>" class="btn btn-xs btn-outline-dark" style="padding:2px 7px;font-size:12px;" title="Editar datos"><i class="fas fa-edit"></i></a>
                <button class="btn btn-xs btn-outline-danger btn-del" data-id="<?= $r['id'] ?>" style="padding:2px 7px;font-size:12px;"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    </div>
</div>

<?php $deleteBase = base_url('/inspecciones/'.$SLUG.'/delete/'); ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    $('#tablaInv').DataTable({
        responsive:true,
        language:{url:'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'},
        pageLength:25,
        order:[[2,'desc']],
        columnDefs:[{orderable:false,targets:[0,3,5]}]
    });
    $('#tablaInv').on('click','.btn-del',function(){
        var id=this.dataset.id;
        Swal.fire({
            title:'Eliminar inventario',
            text:'Se perderan las marcas. Esta accion no se puede deshacer.',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#dc3545',
            confirmButtonText:'Eliminar',
            cancelButtonText:'Cancelar'
        }).then(function(r){
            if(r.isConfirmed) window.location.href='<?= $deleteBase ?>'+id;
        });
    });
});
</script>
