<?php
$idPlan = (int) $inspeccion['id'];
$nombreCliente = esc($cliente['nombre_cliente'] ?? 'Cliente');

$ctxPons     = esc($contextoIA['pons']     ?? '');
$ctxDiagrama = esc($contextoIA['diagrama'] ?? '');
$ctxMatriz   = esc($contextoIA['matriz']   ?? '');
$ctxBrigada  = esc($contextoIA['brigada']  ?? '');

$aprPons     = !empty($aprobadoIA['pons']);
$aprDiagrama = !empty($aprobadoIA['diagrama']);
$aprMatriz   = !empty($aprobadoIA['matriz']);
$aprBrigada  = !empty($aprobadoIA['brigada']);

$hayPons     = !empty($ponsIaAdendo);
$hayDiagrama = !empty($diagramaNodos);
$hayMatriz   = !empty($matrizResponsablesIA);
$hayBrigada  = !empty($inspeccion['brigada_ia_texto']) || !empty($inspeccion['simulacros_ia_texto']);

$baseUrl = base_url('/inspecciones/plan-emergencia');
?>
<style>
.ia-card { border: 1px solid #e0e0e0; border-radius: 10px; padding: 18px; margin-bottom: 18px; background: #fff; }
.ia-card h5 { font-size: 15px; font-weight: 700; margin: 0 0 4px; color: #1c2437; }
.ia-card .ia-status { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; margin-left: 8px; }
.ia-status.vacio { background: #f8d7da; color: #721c24; }
.ia-status.listo { background: #d4edda; color: #155724; }
.ia-status.aprobado { background: #cce5ff; color: #004085; }
.ia-context-box { width: 100%; min-height: 70px; border: 1px solid #ccc; border-radius: 6px; padding: 8px; font-size: 12px; font-family: inherit; resize: vertical; }
.ia-context-label { font-size: 11px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; display: block; }
.ia-btn { font-size: 12px; padding: 6px 14px; border-radius: 6px; border: 1px solid; background: #fff; cursor: pointer; margin-right: 6px; margin-top: 6px; }
.ia-btn.primary { border-color: #8e44ad; color: #8e44ad; }
.ia-btn.primary:hover { background: #8e44ad; color: #fff; }
.ia-btn.success { border-color: #27ae60; color: #27ae60; }
.ia-btn.success:hover { background: #27ae60; color: #fff; }
.ia-btn:disabled { opacity: .5; cursor: not-allowed; }
.ia-preview { margin-top: 12px; padding: 10px; background: #fafafa; border: 1px solid #eee; border-radius: 6px; font-size: 11px; max-height: 280px; overflow-y: auto; }
.ia-preview.empty { color: #999; font-style: italic; text-align: center; padding: 24px; }
.ia-spinner { display: none; margin-left: 8px; font-size: 12px; color: #8e44ad; }
.ia-spinner.active { display: inline-block; }
.ia-master { background: #1c2437; color: #fff; padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
.ia-master button { background: #bd9751; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px; }
.ia-master button:disabled { opacity: .5; }
.ia-pon-item { margin-bottom: 8px; padding: 6px; background: #f5eef8; border-left: 3px solid #8e44ad; font-size: 11px; }
.ia-pon-item strong { color: #5b2c6f; }
.ia-rama { margin: 4px 0; padding: 6px; background: #f5eef8; border-left: 3px solid #8e44ad; font-size: 11px; }
.ia-rama strong { color: #5b2c6f; }
.ia-matriz-table { width: 100%; border-collapse: collapse; font-size: 10px; }
.ia-matriz-table th, .ia-matriz-table td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
.ia-matriz-table th { background: #e8e8e8; }
.ia-final-bar { position: sticky; bottom: 0; background: #fff; padding: 14px; border-top: 2px solid #1c2437; margin-top: 20px; text-align: right; }
.ia-final-bar .btn-finalizar { background: #bd9751; color: #fff; padding: 12px 30px; border: none; border-radius: 6px; font-weight: 700; font-size: 14px; cursor: pointer; }
.ia-final-bar .btn-finalizar:disabled { opacity: .5; cursor: not-allowed; }
.ia-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 14px; }
</style>

<div style="max-width: 980px; margin: 0 auto; padding: 16px;">

    <div class="ia-master">
        <div>
            <h4 style="margin:0; font-size:16px;">REVISION IA</h4>
            <div style="font-size:12px; opacity:.8;"><?= $nombreCliente ?> · Plan #<?= $idPlan ?></div>
        </div>
        <button type="button" id="btnGenerarTodos">
            <i class="fas fa-robot me-1"></i> Generar todos los bloques
        </button>
    </div>

    <div class="ia-warning">
        <i class="fas fa-info-circle"></i> Cada bloque puede recibir <strong>contexto adicional</strong> del profesional antes de generarse. La IA tomara ese contexto en cuenta al producir el contenido. Revisa cada bloque, regenera si no te gusta, y marca como <strong>Aprobado</strong>. Al final, vuelve a la vista del plan y presiona <strong>Finalizar</strong> para generar el PDF con los bloques aprobados.
    </div>

    <!-- ===================== BLOQUE 1: PONs ===================== -->
    <div class="ia-card" data-bloque="pons">
        <h5>
            1. PONs Canonicos (10 adendos personalizados)
            <span class="ia-status <?= $hayPons ? ($aprPons ? 'aprobado' : 'listo') : 'vacio' ?>" id="status-pons">
                <?= $hayPons ? ($aprPons ? 'APROBADO' : 'LISTO') : 'VACIO' ?>
            </span>
        </h5>
        <p style="font-size:11px; color:#666; margin:4px 0 10px;">
            Adendo personalizado por cada uno de los 10 PONs canonicos, adaptando el procedimiento a las amenazas reales del cliente.
        </p>

        <label class="ia-context-label">Contexto adicional para la IA (opcional)</label>
        <textarea class="ia-context-box" data-bloque="pons" placeholder="Ej: El conjunto tiene parqueadero subterraneo con ventilacion limitada, enfatizar fuga de gas..."><?= $ctxPons ?></textarea>

        <div>
            <button type="button" class="ia-btn primary btn-generar" data-bloque="pons" data-url="<?= $baseUrl ?>/enriquecer-pons-ia/<?= $idPlan ?>">
                <i class="fas fa-robot me-1"></i> <?= $hayPons ? 'Regenerar con IA' : 'Generar con IA' ?>
            </button>
            <button type="button" class="ia-btn success btn-aprobar" data-bloque="pons" <?= $hayPons ? '' : 'disabled' ?>>
                <i class="fas <?= $aprPons ? 'fa-check-square' : 'fa-square' ?> me-1"></i> <?= $aprPons ? 'Aprobado' : 'Marcar como aprobado' ?>
            </button>
            <span class="ia-spinner" data-spinner="pons"><i class="fas fa-spinner fa-spin"></i> Generando...</span>
        </div>

        <div class="ia-preview <?= $hayPons ? '' : 'empty' ?>" id="preview-pons">
            <?php if ($hayPons): ?>
                <?php foreach ($ponesCanonicos as $key => $pon): ?>
                    <?php if (!empty($ponsIaAdendo[$key])): ?>
                    <div class="ia-pon-item">
                        <strong>PON <?= esc($pon['codigo']) ?> — <?= esc($pon['titulo']) ?>:</strong><br>
                        <?= esc($ponsIaAdendo[$key]) ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <em>Sin contenido generado. Escribe contexto si quieres, luego haz click en "Generar con IA".</em>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================== BLOQUE 2: Diagrama ===================== -->
    <div class="ia-card" data-bloque="diagrama">
        <h5>
            2. Diagrama de Actuacion
            <span class="ia-status <?= $hayDiagrama ? ($aprDiagrama ? 'aprobado' : 'listo') : 'vacio' ?>" id="status-diagrama">
                <?= $hayDiagrama ? ($aprDiagrama ? 'APROBADO' : 'LISTO') : 'VACIO' ?>
            </span>
        </h5>
        <p style="font-size:11px; color:#666; margin:4px 0 10px;">
            Arbol de decision que muestra las acciones por tipo de emergencia detectada.
        </p>

        <label class="ia-context-label">Contexto adicional para la IA (opcional)</label>
        <textarea class="ia-context-box" data-bloque="diagrama" placeholder="Ej: Priorizar el flujo de incendio porque el conjunto tiene cocinas a gas en la mayoria de apartamentos..."><?= $ctxDiagrama ?></textarea>

        <div>
            <button type="button" class="ia-btn primary btn-generar" data-bloque="diagrama" data-url="<?= $baseUrl ?>/generar-diagrama-ia/<?= $idPlan ?>">
                <i class="fas fa-project-diagram me-1"></i> <?= $hayDiagrama ? 'Regenerar con IA' : 'Generar con IA' ?>
            </button>
            <button type="button" class="ia-btn success btn-aprobar" data-bloque="diagrama" <?= $hayDiagrama ? '' : 'disabled' ?>>
                <i class="fas <?= $aprDiagrama ? 'fa-check-square' : 'fa-square' ?> me-1"></i> <?= $aprDiagrama ? 'Aprobado' : 'Marcar como aprobado' ?>
            </button>
            <span class="ia-spinner" data-spinner="diagrama"><i class="fas fa-spinner fa-spin"></i> Generando...</span>
        </div>

        <div class="ia-preview <?= $hayDiagrama ? '' : 'empty' ?>" id="preview-diagrama">
            <?php if ($hayDiagrama): ?>
                <strong>Inicio:</strong> <?= esc($diagramaNodos['inicio'] ?? '') ?><br>
                <?php foreach (($diagramaNodos['ramas'] ?? []) as $rama): ?>
                <div class="ia-rama">
                    <strong><?= esc($rama['tipo'] ?? '-') ?></strong><br>
                    <?php foreach (($rama['pasos'] ?? []) as $i => $paso): ?>
                    <?= ($i + 1) ?>. <?= esc($paso) ?><br>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <em>Sin diagrama generado. Haz click en "Generar con IA".</em>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================== BLOQUE 3: Matriz Responsables ===================== -->
    <div class="ia-card" data-bloque="matriz">
        <h5>
            3. Matriz de Responsables del Plan
            <span class="ia-status <?= $hayMatriz ? ($aprMatriz ? 'aprobado' : 'listo') : 'vacio' ?>" id="status-matriz">
                <?= $hayMatriz ? ($aprMatriz ? 'APROBADO' : 'LISTO') : 'VACIO' ?>
            </span>
        </h5>
        <p style="font-size:11px; color:#666; margin:4px 0 10px;">
            Tabla con los roles, responsabilidades y frecuencias de revision del Plan.
        </p>

        <label class="ia-context-label">Contexto adicional para la IA (opcional)</label>
        <textarea class="ia-context-box" data-bloque="matriz" placeholder="Ej: El administrador actual es temporal y el consejo se renueva cada 6 meses, considerarlo en frecuencias..."><?= $ctxMatriz ?></textarea>

        <div>
            <button type="button" class="ia-btn primary btn-generar" data-bloque="matriz" data-url="<?= $baseUrl ?>/generar-matriz-ia/<?= $idPlan ?>">
                <i class="fas fa-users-cog me-1"></i> <?= $hayMatriz ? 'Regenerar con IA' : 'Generar con IA' ?>
            </button>
            <button type="button" class="ia-btn success btn-aprobar" data-bloque="matriz" <?= $hayMatriz ? '' : 'disabled' ?>>
                <i class="fas <?= $aprMatriz ? 'fa-check-square' : 'fa-square' ?> me-1"></i> <?= $aprMatriz ? 'Aprobado' : 'Marcar como aprobado' ?>
            </button>
            <span class="ia-spinner" data-spinner="matriz"><i class="fas fa-spinner fa-spin"></i> Generando...</span>
        </div>

        <div class="ia-preview <?= $hayMatriz ? '' : 'empty' ?>" id="preview-matriz">
            <?php if ($hayMatriz): ?>
                <table class="ia-matriz-table">
                    <thead><tr><th>Rol</th><th>Responsabilidad</th><th>Frecuencia</th></tr></thead>
                    <tbody>
                    <?php foreach (($matrizResponsablesIA['filas'] ?? []) as $fila): ?>
                    <tr>
                        <td><strong><?= esc($fila['rol'] ?? '-') ?></strong></td>
                        <td><?= esc($fila['responsabilidad'] ?? '-') ?></td>
                        <td><?= esc($fila['frecuencia'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <em>Sin matriz generada. Haz click en "Generar con IA".</em>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================== BLOQUE 4: Brigada y Simulacros ===================== -->
    <div class="ia-card" data-bloque="brigada">
        <h5>
            4. Brigada y Simulacros
            <span class="ia-status <?= $hayBrigada ? ($aprBrigada ? 'aprobado' : 'listo') : 'vacio' ?>" id="status-brigada">
                <?= $hayBrigada ? ($aprBrigada ? 'APROBADO' : 'LISTO') : 'VACIO' ?>
            </span>
        </h5>
        <p style="font-size:11px; color:#666; margin:4px 0 10px;">
            Texto personalizado del estado actual de la brigada y el programa de simulacros. Requiere inspeccion previa de Brigada y Simulacros del cliente para dar mejor contexto.
        </p>

        <label class="ia-context-label">Contexto adicional para la IA (opcional)</label>
        <textarea class="ia-context-box" data-bloque="brigada" placeholder="Ej: Se acaba de aprobar en asamblea la conformacion de la brigada, incluir cronograma de reuniones mensuales..."><?= $ctxBrigada ?></textarea>

        <div>
            <button type="button" class="ia-btn primary btn-generar" data-bloque="brigada" data-url="<?= $baseUrl ?>/generar-brigada-ia/<?= $idPlan ?>">
                <i class="fas fa-people-carry me-1"></i> <?= $hayBrigada ? 'Regenerar con IA' : 'Generar con IA' ?>
            </button>
            <button type="button" class="ia-btn success btn-aprobar" data-bloque="brigada" <?= $hayBrigada ? '' : 'disabled' ?>>
                <i class="fas <?= $aprBrigada ? 'fa-check-square' : 'fa-square' ?> me-1"></i> <?= $aprBrigada ? 'Aprobado' : 'Marcar como aprobado' ?>
            </button>
            <span class="ia-spinner" data-spinner="brigada"><i class="fas fa-spinner fa-spin"></i> Generando...</span>
        </div>

        <div class="ia-preview <?= $hayBrigada ? '' : 'empty' ?>" id="preview-brigada">
            <?php if ($hayBrigada): ?>
                <?php if (!empty($inspeccion['brigada_ia_texto'])): ?>
                <div style="margin-bottom:10px; padding:8px; background:#fdf2e9; border-left:3px solid #d35400;">
                    <strong>Brigada:</strong><br>
                    <?= nl2br(esc($inspeccion['brigada_ia_texto'])) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($inspeccion['simulacros_ia_texto'])): ?>
                <div style="padding:8px; background:#fdf2e9; border-left:3px solid #d35400;">
                    <strong>Simulacros:</strong><br>
                    <?= nl2br(esc($inspeccion['simulacros_ia_texto'])) ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <em>Sin contenido generado. Haz click en "Generar con IA".</em>
            <?php endif; ?>
        </div>
    </div>

    <div class="ia-final-bar">
        <a href="<?= $baseUrl ?>/view/<?= $idPlan ?>" class="btn btn-outline-secondary" style="margin-right:10px;">
            <i class="fas fa-arrow-left"></i> Volver a la vista del plan
        </a>
        <a href="<?= $baseUrl ?>/view/<?= $idPlan ?>" class="btn-finalizar" style="text-decoration:none; display:inline-block;">
            <i class="fas fa-check"></i> Listo, volver al plan
        </a>
    </div>
</div>

<script>
(function() {
    const PLAN_ID = <?= $idPlan ?>;
    const CSRF_NAME = '<?= csrf_token() ?>';
    const CSRF_HASH = '<?= csrf_hash() ?>';
    const urlSaveContexto = '<?= $baseUrl ?>/ia-save-contexto/' + PLAN_ID;
    const urlAprobar      = '<?= $baseUrl ?>/ia-aprobar/' + PLAN_ID;
    const orden = ['pons', 'diagrama', 'matriz', 'brigada'];

    let csrfHash = CSRF_HASH;

    // Guardar contexto con debounce al escribir
    document.querySelectorAll('.ia-context-box').forEach(textarea => {
        let t;
        textarea.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(() => saveContexto(textarea.dataset.bloque, textarea.value), 800);
        });
    });

    function saveContexto(bloque, contexto) {
        const fd = new FormData();
        fd.append('bloque', bloque);
        fd.append('contexto', contexto);
        fd.append(CSRF_NAME, csrfHash);
        return fetch(urlSaveContexto, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(r => r.json())
            .then(j => { if (j.csrfHash) csrfHash = j.csrfHash; return j; });
    }

    // Generar un bloque
    function generarBloque(bloque) {
        const btn = document.querySelector(`.btn-generar[data-bloque="${bloque}"]`);
        const spinner = document.querySelector(`[data-spinner="${bloque}"]`);
        const status = document.getElementById(`status-${bloque}`);
        const url = btn.dataset.url;

        btn.disabled = true;
        spinner.classList.add('active');

        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(j => {
            spinner.classList.remove('active');
            btn.disabled = false;
            if (!j.ok) {
                alert('Error generando ' + bloque + ': ' + (j.error || 'desconocido'));
                return;
            }
            // Recargar la vista para ver el contenido nuevo
            // (preservamos el scroll position via hash)
            window.location.hash = 'bloque-' + bloque;
            window.location.reload();
        })
        .catch(err => {
            spinner.classList.remove('active');
            btn.disabled = false;
            alert('Error de red: ' + err.message);
        });
    }

    document.querySelectorAll('.btn-generar').forEach(btn => {
        btn.addEventListener('click', async () => {
            // Primero guardar el contexto del bloque (por si hay cambios sin debounce)
            const bloque = btn.dataset.bloque;
            const textarea = document.querySelector(`.ia-context-box[data-bloque="${bloque}"]`);
            if (textarea) {
                await saveContexto(bloque, textarea.value);
            }
            await generarBloque(bloque);
        });
    });

    // Botón master "Generar todos"
    document.getElementById('btnGenerarTodos').addEventListener('click', async () => {
        const btnMaster = document.getElementById('btnGenerarTodos');
        if (!confirm('Generar los 4 bloques con IA en secuencia? Tarda aproximadamente 90 segundos. Veras el progreso bloque por bloque.')) return;
        btnMaster.disabled = true;
        btnMaster.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

        // Guardar todos los contextos primero
        for (const bloque of orden) {
            const ta = document.querySelector(`.ia-context-box[data-bloque="${bloque}"]`);
            if (ta) await saveContexto(bloque, ta.value);
        }

        // Generar uno por uno en secuencia
        for (const bloque of orden) {
            const url = document.querySelector(`.btn-generar[data-bloque="${bloque}"]`).dataset.url;
            const spinner = document.querySelector(`[data-spinner="${bloque}"]`);
            spinner.classList.add('active');
            btnMaster.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Generando ${bloque}...`;
            try {
                const r = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const j = await r.json();
                spinner.classList.remove('active');
                if (!j.ok) {
                    alert(`Fallo ${bloque}: ${j.error || 'desconocido'}. Continuando con los demas.`);
                }
            } catch (e) {
                spinner.classList.remove('active');
                alert(`Error red en ${bloque}: ${e.message}`);
            }
        }

        btnMaster.innerHTML = '<i class="fas fa-check"></i> Completado. Recargando...';
        setTimeout(() => window.location.reload(), 1000);
    });

    // Aprobar / desaprobar
    document.querySelectorAll('.btn-aprobar').forEach(btn => {
        btn.addEventListener('click', () => {
            const bloque = btn.dataset.bloque;
            const estabaAprobado = btn.innerHTML.includes('fa-check-square');
            const nuevoEstado = estabaAprobado ? 0 : 1;

            const fd = new FormData();
            fd.append('bloque', bloque);
            fd.append('aprobado', nuevoEstado.toString());
            fd.append(CSRF_NAME, csrfHash);

            fetch(urlAprobar, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(j => {
                    if (j.csrfHash) csrfHash = j.csrfHash;
                    if (!j.ok) { alert('Error: ' + (j.error || '')); return; }
                    // Actualizar UI
                    const status = document.getElementById('status-' + bloque);
                    if (nuevoEstado) {
                        btn.innerHTML = '<i class="fas fa-check-square me-1"></i> Aprobado';
                        status.classList.remove('listo');
                        status.classList.add('aprobado');
                        status.textContent = 'APROBADO';
                    } else {
                        btn.innerHTML = '<i class="fas fa-square me-1"></i> Marcar como aprobado';
                        status.classList.remove('aprobado');
                        status.classList.add('listo');
                        status.textContent = 'LISTO';
                    }
                });
        });
    });
})();
</script>
