<?php $SLUG = 'senalizacion'; $TITULO = 'Señalización'; $ICONO = 'fa-sign'; ?>
<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas <?= $ICONO ?>"></i> <?= $TITULO ?></h6>
        <a href="<?= base_url('/inspecciones/'.$SLUG.'/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto;padding:8px 16px;"><i class="fas fa-plus"></i> Nuevo</a>
    </div>
    <div class="table-responsive">
    <table id="tablaInsp" class="table table-sm table-hover" style="width:100%">
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th>Calificación</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($inspecciones as $i => $r):
            $f = $r['fecha_inspeccion'];
            $e = $r['estado'];
            $estados = ['borrador'=>['Borrador','badge-borrador'],'completo'=>['Completo','badge-completo'],'pendiente_firma'=>['Pend. Firma','badge-pendiente_firma']];
            [$lbl,$cls] = $estados[$e] ?? [esc($e),'bg-secondary'];
            $cal = ($e === 'completo' && ($r['calificacion']??0) > 0) ? number_format($r['calificacion'],1).'%' : '';
        ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= esc($r['nombre_cliente']??'') ?></td>
            <td data-order="<?= esc($f) ?>"><?= date('d/m/Y',strtotime($f)) ?></td>
            <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
            <td><?= $cal ?></td>
            <td>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/edit/'.$r['id']) ?>" class="btn btn-xs btn-outline-dark" style="padding:2px 7px;font-size:12px;" title="Editar"><i class="fas fa-edit"></i></a>
                <?php if($e==='completo'):?>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/view/'.$r['id']) ?>" class="btn btn-xs btn-outline-secondary" style="padding:2px 7px;font-size:12px;" title="Ver"><i class="fas fa-eye"></i></a>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/pdf/'.$r['id']) ?>" class="btn btn-xs btn-outline-success" style="padding:2px 7px;font-size:12px;" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                <?php endif;?>
                <button class="btn btn-xs btn-outline-danger btn-del" data-id="<?= $r['id'] ?>" data-nombre="<?= esc($r['nombre_cliente']??'') ?>" style="padding:2px 7px;font-size:12px;"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    </div>
</div>
<?php $deleteBase = base_url('/inspecciones/'.$SLUG.'/delete/'); ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
    $('#tablaInsp').DataTable({responsive:true,language:{url:'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'},pageLength:25,order:[[2,'desc']],columnDefs:[{orderable:false,targets:[0,5]}]});
    $('#tablaInsp').on('click','.btn-del',function(){
        var id=this.dataset.id,n=this.dataset.nombre;
        Swal.fire({title:'¿Eliminar registro?',html:'Se eliminará el registro de <strong>'+n+'</strong>',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545',confirmButtonText:'Sí, eliminar',cancelButtonText:'Cancelar'})
        .then(function(r){if(r.isConfirmed)window.location.href='<?= $deleteBase ?>'+id;});
    });
});
</script>
