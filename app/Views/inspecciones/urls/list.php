<?php if (session()->getFlashdata('msg')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('msg') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Accesos Rápidos</h5>
</div>

<a href="<?= base_url('/inspecciones/urls/create') ?>" class="btn btn-pwa btn-pwa-primary mb-3">
    <i class="fas fa-plus me-2"></i>Nuevo Acceso
</a>

<?php if (empty($grouped)): ?>
    <div class="text-center text-muted py-4">
        <i class="fas fa-inbox fa-3x mb-2"></i>
        <p>No hay accesos rápidos registrados.</p>
    </div>
<?php else: ?>
    <?php
    $colores = [
        'AGENDA CONSULTOR' => '#1565c0',
        'BRIGADISTA'       => '#c62828',
        'INDUCCION'        => '#6a1b9a',
        'KPI'              => '#00695c',
        'PROCEDIMIENTOS'   => '#ef6c00',
        'SIMULACRO'        => '#37474f',
    ];
    ?>
    <?php foreach ($grouped as $tipo => $urls): ?>
    <div class="card mb-3">
        <div class="card-header py-2 px-3 text-white" style="background: <?= $colores[$tipo] ?? '#1c2437' ?>; font-size: 13px;">
            <i class="fas fa-folder-open me-1"></i> <?= esc($tipo) ?>
            <span class="badge bg-light text-dark ms-1" style="font-size: 10px;"><?= count($urls) ?></span>
        </div>
        <div class="card-body p-0">
            <?php foreach ($urls as $u): ?>
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="font-size: 13px;">
                <div class="flex-grow-1 me-2" style="min-width: 0;">
                    <a href="<?= esc($u['url']) ?>" target="_blank" class="text-decoration-none fw-bold" style="color: <?= $colores[$tipo] ?? '#1c2437' ?>;">
                        <i class="fas fa-external-link-alt me-1" style="font-size: 10px;"></i><?= esc($u['nombre']) ?>
                    </a>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <a href="<?= base_url('/inspecciones/urls/edit/') ?><?= $u['id'] ?>" class="btn btn-sm btn-outline-dark" style="font-size: 11px; padding: 1px 6px;">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $u['id'] ?>" data-nombre="<?= esc($u['nombre']) ?>" style="font-size: 11px; padding: 1px 6px;">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-delete');
    if (!btn) return;
    e.preventDefault();
    var id = btn.dataset.id;
    confirmarEliminarInsp('<?= base_url('/inspecciones/urls/delete/') ?>' + id);
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
        if(!result.isConfirmed)return;
        var op2=ops[Math.floor(Math.random()*ops.length)];
        var a2,b2,resp2;
        if(op2==='+'){a2=Math.floor(Math.random()*20)+1;b2=Math.floor(Math.random()*20)+1;resp2=a2+b2;}
        else if(op2==='-'){a2=Math.floor(Math.random()*20)+10;b2=Math.floor(Math.random()*a2);resp2=a2-b2;}
        else{a2=Math.floor(Math.random()*9)+2;b2=Math.floor(Math.random()*9)+2;resp2=a2*b2;}
        Swal.fire({
            title:'Confirmar eliminacion',
            html:'<p style="color:#dc3545;font-size:14px;font-weight:600;">Segunda verificacion</p>'+
                 '<div style="font-size:24px;font-weight:700;color:#1c2437;margin:10px 0;">'+a2+' '+op2+' '+b2+' = ?</div>',
            input:'number',inputPlaceholder:'Tu respuesta',icon:'error',showCancelButton:true,
            confirmButtonColor:'#dc3545',confirmButtonText:'Confirmar eliminacion',cancelButtonText:'Cancelar',
            inputValidator:function(value){
                if(!value&&value!=='0')return'Debes ingresar un numero';
                if(parseInt(value)!==resp2)return'Respuesta incorrecta. Intenta de nuevo.';
            }
        }).then(function(result2){
            if(result2.isConfirmed){window.location.href=url;}
        });
    });
}
</script>
