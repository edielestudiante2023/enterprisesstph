<?php $SLUG = 'brigada-simulacros'; $TITULO = 'Brigada y Simulacros'; $ICONO = 'fa-users-cog'; ?>
<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas <?= $ICONO ?>"></i> <?= $TITULO ?></h6>
        <a href="<?= base_url('/inspecciones/'.$SLUG.'/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto;padding:8px 16px;"><i class="fas fa-plus"></i> Nuevo</a>
    </div>
    <div class="table-responsive">
    <table id="tablaInsp" class="table table-sm table-hover" style="width:100%">
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th>Brigada</th><th>Brigadistas</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($inspecciones as $i => $r):
            $f = $r['fecha_inspeccion'];
            $e = $r['estado'];
            $estados = ['borrador'=>['Borrador','badge-borrador'],'completo'=>['Completo','badge-completo']];
            [$lbl,$cls] = $estados[$e] ?? [esc($e),'bg-secondary'];
            $mapBrigada = ['si'=>'Sí','no'=>'No','parcial'=>'Parcial'];
            $etiquetaBrigada = $mapBrigada[$r['existe_brigada'] ?? 'no'] ?? 'No';
        ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= esc($r['nombre_cliente']??'') ?></td>
            <td data-order="<?= esc($f) ?>"><?= date('d/m/Y',strtotime($f)) ?></td>
            <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
            <td><?= esc($etiquetaBrigada) ?></td>
            <td><?= (int)($r['numero_brigadistas'] ?? 0) ?></td>
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
    $('#tablaInsp').DataTable({responsive:true,language:{url:'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'},pageLength:25,order:[[2,'desc']],columnDefs:[{orderable:false,targets:[0,6]}]});
    $('#tablaInsp').on('click','.btn-del',function(){
        var id=this.dataset.id;
        confirmarEliminarInsp('<?= $deleteBase ?>'+id);
    });
});
function confirmarEliminarInsp(url){
    var ops=['+','-','x'];
    var op=ops[Math.floor(Math.random()*ops.length)];
    var a,b,respuesta;
    if(op==='+'){a=Math.floor(Math.random()*20)+1;b=Math.floor(Math.random()*20)+1;respuesta=a+b;}
    else if(op==='-'){a=Math.floor(Math.random()*20)+10;b=Math.floor(Math.random()*a);respuesta=a-b;}
    else{a=Math.floor(Math.random()*9)+2;b=Math.floor(Math.random()*9)+2;respuesta=a*b;}
    Swal.fire({
        title:'Eliminar registro',
        html:'<p style="color:#666;font-size:14px;">Esta accion no se puede deshacer.<br>Para confirmar, resuelve la operacion:</p>'+
             '<div style="font-size:24px;font-weight:700;color:#1c2437;margin:10px 0;">'+a+' '+op+' '+b+' = ?</div>',
        input:'number',inputPlaceholder:'Tu respuesta',icon:'warning',showCancelButton:true,
        confirmButtonColor:'#dc3545',confirmButtonText:'Eliminar',cancelButtonText:'Cancelar',
        inputValidator:function(value){
            if(!value&&value!=='0')return'Debes ingresar un numero';
            if(parseInt(value)!==respuesta)return'Respuesta incorrecta. Intenta de nuevo.';
        }
    }).then(function(result){
        if(result.isConfirmed){window.location.href=url;}
    });
}
</script>
