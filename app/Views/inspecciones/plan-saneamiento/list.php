<?php $SLUG = 'plan-saneamiento'; $TITULO = 'Plan de Saneamiento'; $ICONO = 'fa-shield-alt'; ?>
<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas <?= $ICONO ?>"></i> <?= $TITULO ?></h6>
        <div class="d-flex gap-1">
            <a href="<?= base_url('/inspecciones/'.$SLUG.'/documento') ?>" class="btn btn-sm btn-outline-secondary" target="_blank" style="padding:4px 8px;font-size:12px;" title="Documento"><i class="fas fa-file-alt"></i></a>
            <a href="<?= base_url('/inspecciones/'.$SLUG.'/presentacion') ?>" class="btn btn-sm btn-outline-secondary" target="_blank" style="padding:4px 8px;font-size:12px;" title="Estructura"><i class="fas fa-tv"></i></a>
            <a href="<?= base_url('/inspecciones/'.$SLUG.'/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto;padding:8px 16px;"><i class="fas fa-plus"></i> Nuevo</a>
        </div>
    </div>
    <div class="table-responsive">
    <table id="tablaInsp" class="table table-sm table-hover" style="width:100%">
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($inspecciones as $i => $r):
            $f = $r['fecha_programa'];
            $e = $r['estado'];
            $estados = ['borrador'=>['Borrador','badge-borrador'],'completo'=>['Completo','badge-completo'],'pendiente_firma'=>['Pend. Firma','badge-pendiente_firma']];
            [$lbl,$cls] = $estados[$e] ?? [esc($e),'bg-secondary'];
        ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= esc($r['nombre_cliente']??'') ?></td>
            <td data-order="<?= esc($f) ?>"><?= date('d/m/Y',strtotime($f)) ?></td>
            <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
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
$(function(){
    $('#tablaInsp').DataTable({responsive:true,language:{url:'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'},pageLength:25,order:[[2,'desc']],columnDefs:[{orderable:false,targets:[0,4]}]});
    $('#tablaInsp').on('click','.btn-del',function(){
        var id=this.dataset.id,n=this.dataset.nombre;
        Swal.fire({title:'¿Eliminar registro?',html:'Se eliminará el registro de <strong>'+n+'</strong>',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545',confirmButtonText:'Sí, eliminar',cancelButtonText:'Cancelar'})
        .then(function(r){if(r.isConfirmed)window.location.href='<?= $deleteBase ?>'+id;});
    });
});
</script>
