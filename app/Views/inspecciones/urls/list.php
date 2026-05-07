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
                    <a href="#" class="btn btn-sm btn-outline-primary btn-qr" data-url="<?= esc($u['url']) ?>" data-nombre="<?= esc($u['nombre']) ?>" title="Mostrar QR" style="font-size: 11px; padding: 1px 6px;">
                        <i class="fas fa-qrcode"></i>
                    </a>
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

<!-- QR generator (cliente, offline-capable) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Botón QR: abre modal con QR + opciones (proyectar / copiar / abrir)
document.addEventListener('click', function(e) {
    var btnQr = e.target.closest('.btn-qr');
    if (!btnQr) return;
    e.preventDefault();
    mostrarQR(btnQr.dataset.url, btnQr.dataset.nombre);
});

function mostrarQR(url, nombre) {
    Swal.fire({
        title: nombre,
        html:
            '<div id="qr-container" style="display:flex;justify-content:center;padding:10px;"></div>' +
            '<div style="word-break:break-all;font-size:11px;color:#666;margin-top:10px;background:#f5f5f5;padding:8px;border-radius:4px;font-family:monospace;">' +
                escapeHtml(url) +
            '</div>',
        showCloseButton: true,
        showConfirmButton: true,
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '<i class="fas fa-expand"></i> Proyectar',
        denyButtonText: '<i class="fas fa-copy"></i> Copiar URL',
        cancelButtonText: '<i class="fas fa-external-link-alt"></i> Abrir',
        confirmButtonColor: '#1c2437',
        denyButtonColor: '#6c757d',
        cancelButtonColor: '#198754',
        reverseButtons: true,
        focusConfirm: false,
        didOpen: function() {
            var c = document.getElementById('qr-container');
            c.innerHTML = '';
            new QRCode(c, {
                text: url,
                width: 256,
                height: 256,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            proyectarQR(url, nombre);
        } else if (result.isDenied) {
            copiarAlPortapapeles(url);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.open(url, '_blank');
        }
    });
}

function proyectarQR(url, nombre) {
    var w = window.open('', '_blank', 'width=1024,height=768');
    if (!w) {
        Swal.fire({title: 'Bloqueado', text: 'Permite popups en este sitio para proyectar.', icon: 'warning'});
        return;
    }
    var safeNombre = escapeHtml(nombre);
    var safeUrl = escapeHtml(url);
    var jsUrl = url.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    w.document.write(
        '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>QR - ' + safeNombre + '</title>' +
        '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>' +
        '<style>' +
        '*{box-sizing:border-box;margin:0;padding:0;}' +
        'body{font-family:system-ui,sans-serif;background:#fff;height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;}' +
        'h1{color:#1c2437;font-size:clamp(28px,5vw,52px);margin-bottom:30px;text-align:center;}' +
        '#qrlarge{padding:20px;background:#fff;border:2px solid #1c2437;border-radius:12px;}' +
        'p{color:#666;font-size:clamp(14px,1.5vw,20px);margin-top:30px;word-break:break-all;max-width:90vw;text-align:center;font-family:monospace;}' +
        '.tip{position:fixed;bottom:15px;color:#999;font-size:13px;}' +
        '@media print{.tip{display:none;}}' +
        '</style></head><body>' +
        '<h1>' + safeNombre + '</h1>' +
        '<div id="qrlarge"></div>' +
        '<p>' + safeUrl + '</p>' +
        '<div class="tip">Apunta tu cámara al QR para abrir el contenido en tu celular · Esc para cerrar</div>' +
        '<script>' +
        'new QRCode(document.getElementById("qrlarge"),{text:"' + jsUrl + '",width:Math.min(window.innerHeight*0.55,500),height:Math.min(window.innerHeight*0.55,500),correctLevel:QRCode.CorrectLevel.H});' +
        'document.addEventListener("keydown",function(e){if(e.key==="Escape")window.close();});' +
        '<\/script></body></html>'
    );
    w.document.close();
}

function copiarAlPortapapeles(texto) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(function() {
            Swal.fire({title:'URL copiada',icon:'success',timer:1200,showConfirmButton:false});
        }).catch(function() {
            copiarFallback(texto);
        });
    } else {
        copiarFallback(texto);
    }
}

function copiarFallback(texto) {
    var ta = document.createElement('textarea');
    ta.value = texto; ta.style.position='fixed'; ta.style.opacity='0';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); Swal.fire({title:'URL copiada',icon:'success',timer:1200,showConfirmButton:false}); }
    catch(_) { Swal.fire({title:'No se pudo copiar',icon:'error',timer:1500,showConfirmButton:false}); }
    document.body.removeChild(ta);
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function(c){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}

// Botón eliminar (existente)
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
