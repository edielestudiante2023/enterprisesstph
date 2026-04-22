<?php $SLUG = 'procedimiento-emergencia-area'; $TITULO = 'Procedimientos de Emergencia por Área'; $ICONO = 'fa-triangle-exclamation'; ?>
<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas <?= $ICONO ?>"></i> <?= $TITULO ?></h6>
        <a href="<?= base_url('/inspecciones/'.$SLUG.'/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto;padding:8px 16px;"><i class="fas fa-plus"></i> Nuevo</a>
    </div>
    <div class="table-responsive">
    <table id="tablaPEA" class="table table-sm table-hover" style="width:100%">
        <thead><tr><th>#</th><th>Cliente</th><th>Área</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($procedimientos as $i => $r):
            $f = $r['fecha_elaboracion'];
            $e = $r['estado'];
            $estados = ['borrador'=>['Borrador','badge-borrador'],'completo'=>['Completo','badge-completo']];
            [$lbl,$cls] = $estados[$e] ?? [esc($e),'bg-secondary'];
            $areaLbl = $areasLabels[$r['area']] ?? $r['area'];
        ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= esc($r['nombre_cliente']??'') ?></td>
            <td><?= esc($areaLbl) ?></td>
            <td data-order="<?= esc($f) ?>"><?= date('d/m/Y',strtotime($f)) ?></td>
            <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
            <td>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/edit/'.$r['id']) ?>" class="btn btn-xs btn-outline-dark" style="padding:2px 7px;font-size:12px;" title="Editar"><i class="fas fa-edit"></i></a>
                <?php if($e==='completo'):?>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/view/'.$r['id']) ?>" class="btn btn-xs btn-outline-secondary" style="padding:2px 7px;font-size:12px;" title="Ver"><i class="fas fa-eye"></i></a>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/pdf/'.$r['id']) ?>" class="btn btn-xs btn-outline-success" style="padding:2px 7px;font-size:12px;" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                <?php endif;?>
                <a href="<?= base_url('/inspecciones/'.$SLUG.'/delete/'.$r['id']) ?>" class="btn btn-xs btn-outline-danger" style="padding:2px 7px;font-size:12px;" onclick="return confirm('Eliminar este procedimiento?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#tablaPEA').DataTable({responsive:true,language:{url:'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'},pageLength:25,order:[[3,'desc']],columnDefs:[{orderable:false,targets:[0,5]}]});
    }
});
</script>
