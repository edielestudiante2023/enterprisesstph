<?php
$totalAsistentes = count($asistentes);
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3">
        <h6 class="mb-1">Firmas - Asistencia Induccion</h6>
        <small class="text-muted">ID: <?= $inspeccion['id'] ?></small>
    </div>

    <?php if ($totalAsistentes === 0): ?>
    <div class="alert alert-warning" style="font-size:14px;">
        No hay asistentes registrados. <a href="/inspecciones/asistencia-induccion/edit/<?= $inspeccion['id'] ?>">Agregar asistentes</a>
    </div>
    <?php else: ?>

    <!-- Progress -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size:13px; font-weight:bold;" id="progressLabel">Firmante 1 de <?= $totalAsistentes ?></span>
            <span style="font-size:12px;" id="firmasCount">0 / <?= $totalAsistentes ?> firmados</span>
        </div>
        <div class="progress" style="height:6px;">
            <div class="progress-bar bg-success" id="progressBar" style="width:0%"></div>
        </div>
    </div>

    <!-- Firmante actual -->
    <div id="firmantePanel">
        <?php foreach ($asistentes as $i => $a): ?>
        <div class="firmante-card" data-index="<?= $i ?>" data-id="<?= $a['id'] ?>" style="display:<?= $i === 0 ? 'block' : 'none' ?>;">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title" style="font-size:14px; color:#999;">DATOS DEL ASISTENTE</h6>
                    <table class="table table-sm mb-0" style="font-size:13px;">
                        <tr><td class="text-muted" style="width:30%;">Nombre</td><td><strong><?= esc($a['nombre']) ?></strong></td></tr>
                        <tr><td class="text-muted">Cedula</td><td><?= esc($a['cedula']) ?></td></tr>
                        <tr><td class="text-muted">Cargo</td><td><?= esc($a['cargo']) ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- Firma existente -->
            <?php if (!empty($a['firma'])): ?>
            <div class="card mb-3 firma-existente" data-index="<?= $i ?>">
                <div class="card-body text-center">
                    <p style="font-size:12px; color:#28a745; margin-bottom:5px;"><i class="fas fa-check-circle"></i> Firma registrada</p>
                    <img src="/<?= esc($a['firma']) ?>?t=<?= time() ?>" style="max-width:200px; max-height:80px; border:1px solid #ddd;">
                </div>
            </div>
            <?php endif; ?>

            <!-- Canvas firma -->
            <div class="card mb-3">
                <div class="card-body">
                    <p style="font-size:12px; color:#666; margin-bottom:5px;"><?= !empty($a['firma']) ? 'Reemplazar firma:' : 'Firmar aqui:' ?></p>
                    <div style="text-align:center;">
                        <canvas class="firma-canvas" data-index="<?= $i ?>"
                            width="300" height="150"
                            style="border:2px solid #ccc; border-radius:6px; touch-action:none; background:#fff;"></canvas>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill btn-limpiar" data-index="<?= $i ?>">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                        <button type="button" class="btn btn-sm btn-success flex-fill btn-guardar-firma" data-index="<?= $i ?>" data-id="<?= $a['id'] ?>">
                            <i class="fas fa-save"></i> Guardar Firma
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navegacion -->
            <div class="d-flex gap-2 mb-3">
                <?php if ($i > 0): ?>
                <button type="button" class="btn btn-sm btn-outline-dark flex-fill btn-nav" data-target="<?= $i - 1 ?>">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
                <?php endif; ?>
                <?php if ($i < $totalAsistentes - 1): ?>
                <button type="button" class="btn btn-sm btn-outline-dark flex-fill btn-nav" data-target="<?= $i + 1 ?>">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Mensaje de completado -->
    <div id="completadoPanel" style="display:none;">
        <div class="card mb-3">
            <div class="card-body text-center">
                <i class="fas fa-check-circle text-success" style="font-size:40px;"></i>
                <h6 class="mt-2">Todas las firmas completadas</h6>
                <p class="text-muted" style="font-size:13px;">Puede volver a la vista del registro.</p>
                <a href="/inspecciones/asistencia-induccion/view/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary">
                    <i class="fas fa-eye"></i> Volver al registro
                </a>
            </div>
        </div>
    </div>

    <!-- Boton volver -->
    <div class="mb-4">
        <a href="/inspecciones/asistencia-induccion/view/<?= $inspeccion['id'] ?>" class="btn btn-sm btn-outline-dark">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalAsistentes = <?= $totalAsistentes ?>;
    if (totalAsistentes === 0) return;

    const canvases = {};
    const contexts = {};
    const drawing = {};
    let currentIndex = 0;

    // Initialize canvases
    document.querySelectorAll('.firma-canvas').forEach(canvas => {
        const idx = parseInt(canvas.dataset.index);
        canvases[idx] = canvas;
        contexts[idx] = canvas.getContext('2d');
        drawing[idx] = false;

        // Touch events
        canvas.addEventListener('touchstart', function(e) {
            if (e.touches.length > 1) return; // Multi-touch filter
            e.preventDefault();
            drawing[idx] = true;
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (e.touches[0].clientX - rect.left) * scaleX;
            const y = (e.touches[0].clientY - rect.top) * scaleY;
            contexts[idx].beginPath();
            contexts[idx].moveTo(x, y);
        }, { passive: false });

        canvas.addEventListener('touchmove', function(e) {
            if (e.touches.length > 1) return;
            e.preventDefault();
            if (!drawing[idx]) return;
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (e.touches[0].clientX - rect.left) * scaleX;
            const y = (e.touches[0].clientY - rect.top) * scaleY;
            contexts[idx].lineWidth = 2;
            contexts[idx].lineCap = 'round';
            contexts[idx].strokeStyle = '#000';
            contexts[idx].lineTo(x, y);
            contexts[idx].stroke();
        }, { passive: false });

        canvas.addEventListener('touchend', function(e) {
            drawing[idx] = false;
        });

        // Mouse events
        canvas.addEventListener('mousedown', function(e) {
            drawing[idx] = true;
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (e.clientX - rect.left) * scaleX;
            const y = (e.clientY - rect.top) * scaleY;
            contexts[idx].beginPath();
            contexts[idx].moveTo(x, y);
        });

        canvas.addEventListener('mousemove', function(e) {
            if (!drawing[idx]) return;
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (e.clientX - rect.left) * scaleX;
            const y = (e.clientY - rect.top) * scaleY;
            contexts[idx].lineWidth = 2;
            contexts[idx].lineCap = 'round';
            contexts[idx].strokeStyle = '#000';
            contexts[idx].lineTo(x, y);
            contexts[idx].stroke();
        });

        canvas.addEventListener('mouseup', () => drawing[idx] = false);
        canvas.addEventListener('mouseleave', () => drawing[idx] = false);
    });

    // Limpiar buttons
    document.querySelectorAll('.btn-limpiar').forEach(btn => {
        btn.addEventListener('click', function() {
            const idx = parseInt(this.dataset.index);
            contexts[idx].clearRect(0, 0, canvases[idx].width, canvases[idx].height);
        });
    });

    // Guardar firma buttons
    document.querySelectorAll('.btn-guardar-firma').forEach(btn => {
        btn.addEventListener('click', function() {
            const idx = parseInt(this.dataset.index);
            const idAsistente = this.dataset.id;
            const canvas = canvases[idx];

            // Check if canvas has content
            const imageData = contexts[idx].getImageData(0, 0, canvas.width, canvas.height);
            let darkPixels = 0;
            for (let i = 3; i < imageData.data.length; i += 4) {
                if (imageData.data[i] > 0 && imageData.data[i - 1] < 200) darkPixels++;
            }
            if (darkPixels < 100) {
                Swal.fire('Firma vacia', 'Por favor dibuje su firma antes de guardar.', 'warning');
                return;
            }

            const firmaData = canvas.toDataURL('image/png');
            const btnThis = this;
            btnThis.disabled = true;
            btnThis.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            fetch('/inspecciones/asistencia-induccion/guardar-firma/' + idAsistente, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'firma=' + encodeURIComponent(firmaData) + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Firma guardada', timer: 1500, showConfirmButton: false });

                    // Update existing firma display
                    const existente = document.querySelector('.firma-existente[data-index="' + idx + '"]');
                    if (existente) {
                        existente.querySelector('img').src = data.firma_url + '?t=' + Date.now();
                    } else {
                        // Create firma display
                        const card = document.createElement('div');
                        card.className = 'card mb-3 firma-existente';
                        card.dataset.index = idx;
                        card.innerHTML = '<div class="card-body text-center"><p style="font-size:12px; color:#28a745; margin-bottom:5px;"><i class="fas fa-check-circle"></i> Firma registrada</p><img src="' + data.firma_url + '?t=' + Date.now() + '" style="max-width:200px; max-height:80px; border:1px solid #ddd;"></div>';
                        const firmanteCard = document.querySelector('.firmante-card[data-index="' + idx + '"]');
                        const canvasCard = firmanteCard.querySelectorAll('.card')[1];
                        firmanteCard.insertBefore(card, canvasCard);
                    }

                    updateProgress();
                } else {
                    Swal.fire('Error', data.error || 'Error al guardar', 'error');
                }
                btnThis.disabled = false;
                btnThis.innerHTML = '<i class="fas fa-save"></i> Guardar Firma';
            })
            .catch(err => {
                Swal.fire('Error', 'Error de conexion', 'error');
                btnThis.disabled = false;
                btnThis.innerHTML = '<i class="fas fa-save"></i> Guardar Firma';
            });
        });
    });

    // Navigation buttons
    document.querySelectorAll('.btn-nav').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = parseInt(this.dataset.target);
            showFirmante(target);
        });
    });

    function showFirmante(index) {
        document.querySelectorAll('.firmante-card').forEach(card => {
            card.style.display = 'none';
        });
        const target = document.querySelector('.firmante-card[data-index="' + index + '"]');
        if (target) {
            target.style.display = 'block';
            currentIndex = index;
            document.getElementById('progressLabel').textContent = 'Firmante ' + (index + 1) + ' de ' + totalAsistentes;
        }
    }

    function updateProgress() {
        const firmados = document.querySelectorAll('.firma-existente').length;
        document.getElementById('firmasCount').textContent = firmados + ' / ' + totalAsistentes + ' firmados';
        document.getElementById('progressBar').style.width = (firmados / totalAsistentes * 100) + '%';

        if (firmados >= totalAsistentes) {
            document.getElementById('completadoPanel').style.display = 'block';
        }
    }

    // Initial progress
    updateProgress();
});
</script>
