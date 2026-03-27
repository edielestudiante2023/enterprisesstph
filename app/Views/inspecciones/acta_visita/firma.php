<div class="container-fluid px-3">
    <div class="mt-2 mb-3">
        <h6>Firmas del Acta</h6>
        <p class="text-muted" style="font-size:13px;">Cada firmante dibuja su firma en el recuadro. Use un dedo para firmar.</p>
    </div>

    <?php foreach ($firmantes as $i => $firmante): ?>
    <div class="card mb-3 firma-step" id="step-<?= $i ?>" style="<?= $i > 0 ? 'display:none;' : '' ?>">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="badge bg-dark">Paso <?= $i + 1 ?> de <?= count($firmantes) ?></span>
                    <h6 class="mt-1 mb-0">Firma — <?= esc($firmante['rol_label']) ?></h6>
                    <small class="text-muted"><?= esc($firmante['nombre']) ?></small>
                </div>
                <?php if ($firmante['firmado']): ?>
                    <span class="badge bg-success"><i class="fas fa-check"></i> Firmado</span>
                <?php endif; ?>
            </div>

            <?php if (!$firmante['firmado']): ?>
            <div style="border: 2px solid #ccc; border-radius: 8px; background: white; position: relative;">
                <canvas id="canvas-<?= esc($firmante['tipo']) ?>" style="width: 100%; height: 200px; touch-action: none; cursor: crosshair;"></canvas>
            </div>

            <div class="d-flex gap-2 mt-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="canvases['<?= esc($firmante['tipo']) ?>'].limpiar()">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
                <?php if ($firmante['tipo'] === 'vigia'): ?>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="skipFirma(<?= $i ?>)">
                    No aplica
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-success btn-whatsapp-firma"
                    data-tipo="<?= esc($firmante['tipo']) ?>"
                    title="Enviar enlace para firma remota">
                    <i class="fab fa-whatsapp"></i> Enviar enlace
                </button>
            </div>

            <button type="button" class="btn btn-pwa btn-pwa-primary mt-3"
                onclick="guardarFirma('<?= esc($firmante['tipo']) ?>', <?= $i ?>)">
                <i class="fas fa-check"></i> Confirmar firma
            </button>
            <?php else: ?>
            <div class="text-center py-3">
                <i class="fas fa-check-circle text-success fa-3x"></i>
                <p class="mt-2 mb-0">Firma registrada</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Navegación entre pasos -->
    <div class="d-flex gap-2 mb-2" id="navPasos">
        <button type="button" class="btn btn-pwa btn-pwa-outline" id="btnPrev" onclick="cambiarPaso(-1)" style="display:none;">
            <i class="fas fa-arrow-left"></i> Anterior
        </button>
        <button type="button" class="btn btn-pwa btn-pwa-outline" id="btnNext" onclick="cambiarPaso(1)">
            Siguiente <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <!-- Botón finalizar -->
    <button type="button" class="btn btn-pwa btn-pwa-primary mb-4" id="btnFinalizar" style="display:none;" onclick="finalizarActa()">
        <i class="fas fa-file-pdf"></i> Finalizar y generar PDF
    </button>
    <button type="button" class="btn btn-pwa btn-pwa-outline mb-4" id="btnFinalizarSinFirma" style="display:none;" onclick="finalizarSinFirma()">
        <i class="fas fa-file-alt"></i> Finalizar sin firma del cliente
    </button>

    <!-- Volver al acta -->
    <a href="<?= base_url('/inspecciones/acta-visita/edit/') ?><?= $acta['id'] ?>" class="btn btn-pwa btn-pwa-outline mb-4">
        <i class="fas fa-arrow-left"></i> Volver al acta
    </a>
</div>

<script src="/js/offline_queue.js"></script>
<script>
const actaId = <?= $acta['id'] ?>;
const totalPasos = <?= count($firmantes) ?>;
let pasoActual = 0;
const canvases = {};
const rolLabels = {
    <?php foreach ($firmantes as $f): ?>
    '<?= esc($f['tipo']) ?>': '<?= esc($f['rol_label']) ?>',
    <?php endforeach; ?>
};

// --- SignatureCanvas class ---
class SignatureCanvas {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.dibujando = false;
        this.hayDibujo = false;
        this.dpr = window.devicePixelRatio || 1;
        this.setup();
    }

    setup() {
        const rect = this.canvas.getBoundingClientRect();
        this.canvas.width = rect.width * this.dpr;
        this.canvas.height = 200 * this.dpr;
        this.ctx.scale(this.dpr, this.dpr);
        this.canvas.style.touchAction = 'none';

        this.ctx.strokeStyle = '#000';
        this.ctx.lineWidth = 3;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';

        this.canvas.addEventListener('mousedown', e => this.iniciar(e));
        this.canvas.addEventListener('mousemove', e => this.dibujar(e));
        this.canvas.addEventListener('mouseup', () => this.terminar());
        this.canvas.addEventListener('mouseout', () => this.terminar());

        this.canvas.addEventListener('touchstart', e => {
            e.preventDefault();
            if (e.touches.length > 1) return;
            this.iniciar(e.touches[0]);
        });
        this.canvas.addEventListener('touchmove', e => {
            e.preventDefault();
            if (e.touches.length > 1) { this.terminar(); return; }
            this.dibujar(e.touches[0]);
        });
        this.canvas.addEventListener('touchend', () => this.terminar());
    }

    getPos(e) {
        const rect = this.canvas.getBoundingClientRect();
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    iniciar(e) {
        this.dibujando = true;
        this.hayDibujo = true;
        const pos = this.getPos(e);
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);
    }

    dibujar(e) {
        if (!this.dibujando) return;
        const pos = this.getPos(e);
        this.ctx.lineTo(pos.x, pos.y);
        this.ctx.stroke();
    }

    terminar() { this.dibujando = false; }

    limpiar() {
        this.ctx.setTransform(1, 0, 0, 1, 0, 0);
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.hayDibujo = false;
        this.setup();
    }

    validarMinPixeles(minimo = 100) {
        const imgData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
        let pixelesOscuros = 0;
        for (let i = 3; i < imgData.length; i += 4) {
            if (imgData[i] > 0) pixelesOscuros++;
        }
        return pixelesOscuros >= minimo;
    }

    exportar() {
        const data = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
        let minX = this.canvas.width, minY = this.canvas.height, maxX = 0, maxY = 0;
        for (let y = 0; y < this.canvas.height; y++) {
            for (let x = 0; x < this.canvas.width; x++) {
                if (data[(y * this.canvas.width + x) * 4 + 3] > 0) {
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }
        if (maxX <= minX || maxY <= minY) return this.canvas.toDataURL('image/png');
        const pad = 20;
        minX = Math.max(0, minX - pad); minY = Math.max(0, minY - pad);
        maxX = Math.min(this.canvas.width, maxX + pad); maxY = Math.min(this.canvas.height, maxY + pad);
        const tc = document.createElement('canvas');
        const tctx = tc.getContext('2d');
        const cW = maxX - minX, cH = maxY - minY;
        const fH = 150, fW = Math.round(fH * (cW / cH));
        tc.width = fW; tc.height = fH;
        tctx.drawImage(this.canvas, minX, minY, cW, cH, 0, 0, fW, fH);
        return tc.toDataURL('image/png');
    }
}

// Initialize canvases
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($firmantes as $firmante): ?>
    <?php if (!$firmante['firmado']): ?>
    canvases['<?= esc($firmante['tipo']) ?>'] = new SignatureCanvas('canvas-<?= esc($firmante['tipo']) ?>');
    <?php endif; ?>
    <?php endforeach; ?>
    updateNav();
});

function cambiarPaso(dir) {
    document.getElementById('step-' + pasoActual).style.display = 'none';
    pasoActual += dir;
    document.getElementById('step-' + pasoActual).style.display = '';

    // Re-setup canvas al hacerse visible (fix: canvas oculto tiene width=0)
    Object.keys(canvases).forEach(tipo => {
        var c = canvases[tipo];
        if (c && c.canvas && c.canvas.offsetParent !== null && !c.hayDibujo) {
            c.setup();
        }
    });

    updateNav();
}

function skipFirma(paso) {
    // Skip vigia firma (no aplica)
    if (paso < totalPasos - 1) {
        cambiarPaso(1);
    }
}

function updateNav() {
    document.getElementById('btnPrev').style.display = pasoActual > 0 ? '' : 'none';
    document.getElementById('btnNext').style.display = pasoActual < totalPasos - 1 ? '' : 'none';

    const esUltimoPaso = pasoActual === totalPasos - 1;
    document.getElementById('btnFinalizar').style.display = esUltimoPaso ? '' : 'none';
    document.getElementById('btnFinalizarSinFirma').style.display = esUltimoPaso ? '' : 'none';
}

function guardarFirma(tipo, paso) {
    const canvas = canvases[tipo];
    if (!canvas || !canvas.hayDibujo) {
        Swal.fire({ icon: 'warning', title: 'Dibuje su firma primero', confirmButtonColor: '#bd9751' });
        return;
    }
    if (!canvas.validarMinPixeles(100)) {
        Swal.fire({ icon: 'warning', title: 'La firma es muy pequena', text: 'Por favor firme con un trazo mas visible', confirmButtonColor: '#bd9751' });
        return;
    }

    const preview = canvas.exportar();

    // SweetAlert preview (Layer 3)
    Swal.fire({
        title: 'Confirmar firma',
        html: '<p style="font-size:14px;">Esta es su firma:</p><img src="' + preview + '" style="max-width:100%; border:1px solid #ccc; border-radius:8px; padding:8px;">',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Repetir',
        confirmButtonColor: '#bd9751',
    }).then(result => {
        if (!result.isConfirmed) return;

        // Send to server
        const formData = new FormData();
        formData.append('tipo', tipo);
        formData.append('firma_imagen', preview);

        fetch('/inspecciones/acta-visita/save-firma/' + actaId, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Mark as signed visually
                const step = document.getElementById('step-' + paso);
                step.querySelector('.card-body').innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">Firma — ${rolLabels[tipo] || tipo}</h6>
                        </div>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Firmado</span>
                    </div>
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle text-success fa-3x"></i>
                        <p class="mt-2 mb-0">Firma registrada</p>
                    </div>`;

                // Auto-advance to next step
                if (paso < totalPasos - 1) {
                    setTimeout(() => cambiarPaso(1), 500);
                } else {
                    updateNav();
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.error });
            }
        })
        .catch(async () => {
            // ── Offline: guardar en IndexedDB ──
            try {
                await OfflineQueue.add({
                    type: 'firma_acta_visita',
                    url: '/inspecciones/acta-visita/save-firma/' + actaId,
                    id_asistencia: actaId,
                    payload: { tipo, firma_imagen: preview },
                    meta: { tipo, actaId }
                });
                await OfflineQueue.requestSync();

                // Marcar como pendiente visualmente
                const step = document.getElementById('step-' + paso);
                step.querySelector('.card-body').innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">Firma — ${rolLabels[tipo] || tipo}</h6>
                        </div>
                        <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pendiente sync</span>
                    </div>
                    <div class="text-center py-3">
                        <i class="fas fa-clock text-warning fa-3x"></i>
                        <p class="mt-2 mb-0">Firma guardada offline</p>
                        <small class="text-muted">Se enviara al volver la conexion</small>
                    </div>`;

                Swal.fire({ icon: 'info', title: 'Guardado offline', html: 'Sin conexion. La firma se enviara automaticamente cuando vuelva el internet.', confirmButtonColor: '#bd9751' });

                if (paso < totalPasos - 1) {
                    setTimeout(() => cambiarPaso(1), 1000);
                } else {
                    updateNav();
                }
            } catch (dbErr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la firma.' });
            }
        });
    });
}

// WhatsApp firma remota
document.querySelectorAll('.btn-whatsapp-firma').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const tipo = this.dataset.tipo;
        const tipoLabel = { administrador: 'Administrador', vigia: 'Vigía SST', consultor: 'Consultor' }[tipo] || tipo;

        Swal.fire({
            title: 'Enviar enlace de firma',
            html: `<p style="font-size:14px;">Se generará un enlace para que <strong>${tipoLabel}</strong> firme desde su celular.<br><small class="text-muted">El enlace expira en 24 horas.</small></p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Generar enlace',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#25D366',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Generando enlace...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch('/inspecciones/acta-visita/generar-token-firma/' + actaId, {
                method: 'POST',
                body: new URLSearchParams({ tipo: tipo }),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { Swal.fire('Error', data.error, 'error'); return; }

                const url = data.url;
                const texto = encodeURIComponent(`Hola, por favor firma el acta de visita SST haciendo clic en este enlace (válido 24 horas):\n${url}`);
                const waUrl = `https://wa.me/?text=${texto}`;

                Swal.fire({
                    title: 'Enlace generado',
                    html: `<p style="font-size:13px;">Comparte este enlace por WhatsApp con el <strong>${tipoLabel}</strong>:</p>
                           <div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;margin-bottom:12px;">${url}</div>`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fab fa-whatsapp"></i> Abrir WhatsApp',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#25D366',
                }).then(r => {
                    if (r.isConfirmed) window.open(waUrl, '_blank');
                    // Avanzar al siguiente paso automáticamente
                    if (pasoActual < totalPasos - 1) cambiarPaso(1);
                    else updateNav();
                });
            })
            .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
        });
    });
});

function finalizarSinFirma() {
    const motivos = [
        'Cliente no disponible durante la visita',
        'Firma enviada por WhatsApp/email, pendiente de respuesta',
        'Token de firma vencido por demora del cliente',
    ];
    const opcionesHtml = motivos.map((m, i) =>
        `<label style="display:block;text-align:left;padding:6px 0;font-size:14px;cursor:pointer;">
            <input type="radio" name="motivoSinFirma" value="${m}" style="margin-right:8px;">${m}
        </label>`
    ).join('');

    Swal.fire({
        title: 'Finalizar sin firma del cliente',
        html: `<p style="font-size:13px;color:#666;">El acta quedará completa. El PDF indicará el motivo por el que no se obtuvo la firma.</p>
               <div style="text-align:left;margin-top:8px;">${opcionesHtml}</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Finalizar sin firma',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#bd9751',
        preConfirm: () => {
            const sel = document.querySelector('input[name="motivoSinFirma"]:checked');
            if (!sel) {
                Swal.showValidationMessage('Selecciona el motivo');
                return false;
            }
            return sel.value;
        },
    }).then(result => {
        if (!result.isConfirmed) return;
        const motivo = result.value;

        Swal.fire({ title: 'Generando PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch('/inspecciones/acta-visita/finalizar-sin-firma/' + actaId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify({ motivo: motivo }),
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Acta finalizada',
                    text: 'PDF generado sin firma del cliente',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Ver PDF',
                    showCancelButton: true,
                    cancelButtonText: 'Volver al inicio',
                }).then(r => {
                    if (r.isConfirmed) window.open(data.pdf_url, '_blank');
                    window.location.href = '/inspecciones';
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.error });
            }
        });
    });
}

// ── Offline sync helpers ──
OfflineQueue.startOnlineListener(function(result) {
    if (result.synced > 0) {
        Swal.fire({ icon: 'success', title: 'Conexion restaurada', html: result.synced + ' firma(s) sincronizada(s).<br>Recargando...', timer: 2500, showConfirmButton: false });
        setTimeout(() => window.location.reload(), 2500);
    }
});

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'sync-firmas-complete') {
            Swal.fire({ icon: 'success', title: 'Sincronizado', html: event.data.synced + ' firma(s) enviada(s).<br>Recargando...', timer: 2500, showConfirmButton: false });
            setTimeout(() => window.location.reload(), 2500);
        }
    });
}

function finalizarActa() {
    Swal.fire({
        title: 'Finalizar acta?',
        html: '<p>Se generara el PDF y el acta quedara <strong>bloqueada</strong>.</p><label style="font-size:14px;"><input type="checkbox" id="checkConfirm" style="margin-right:8px;">Confirmo que el acta esta completa</label>',
        showCancelButton: true,
        confirmButtonText: 'Finalizar y generar PDF',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            if (!document.getElementById('checkConfirm').checked) {
                Swal.showValidationMessage('Debes confirmar que el acta esta completa');
                return false;
            }
        },
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Generando PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch('/inspecciones/acta-visita/finalizar/' + actaId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Acta finalizada',
                    text: 'PDF generado exitosamente',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Ver PDF',
                    showCancelButton: true,
                    cancelButtonText: 'Volver al inicio',
                }).then(r => {
                    if (r.isConfirmed) {
                        window.open(data.pdf_url, '_blank');
                    }
                    window.location.href = '/inspecciones';
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.error });
            }
        });
    });
}
</script>
