<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title>Evaluaciones — <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { font-family: Segoe UI, Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; }

        .header {
            background: #1c2437;
            color: white;
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .header h1 { margin: 0; font-size: 16px; color: #bd9751; }
        .header p  { margin: 4px 0 0; font-size: 13px; color: #ccc; }

        .stats-bar {
            background: white;
            padding: 10px 20px;
            font-size: 13px;
            color: #555;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .stats-bar span { font-weight: 700; color: #bd9751; }

        .estandar-group { margin: 12px 16px; }
        .estandar-title {
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            background: #1c2437;
            padding: 6px 12px;
            border-radius: 6px 6px 0 0;
            margin-bottom: 0;
        }

        .item-card {
            background: white;
            border-left: 4px solid #dc3545;
            padding: 12px 14px;
            margin-bottom: 1px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .item-card.cumple { border-left-color: #28a745; opacity: 0.6; }
        .item-card.vacio  { border-left-color: #ffc107; }

        .item-card input[type=checkbox] {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            margin-top: 2px;
            accent-color: #28a745;
            cursor: pointer;
        }
        .item-card input[type=checkbox]:disabled { cursor: default; }

        .item-body { flex: 1; }
        .item-numeral { font-size: 11px; color: #bd9751; font-weight: 700; margin-bottom: 2px; }
        .item-texto   { font-size: 13px; color: #333; line-height: 1.4; }
        .item-estado  { font-size: 11px; margin-top: 4px; }
        .badge-nocumple { color: #dc3545; font-weight: 700; }
        .badge-vacio    { color: #856404; font-weight: 700; }
        .badge-done     { color: #28a745; font-weight: 700; }

        .empty-state { text-align: center; padding: 48px 20px; color: #666; }
        .empty-state i { font-size: 48px; color: #28a745; display: block; margin-bottom: 12px; }
        .empty-state h3 { color: #1c2437; }

        .toast {
            position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
            background: #28a745; color: white; padding: 10px 20px; border-radius: 8px;
            font-size: 14px; display: none; z-index: 999;
        }
    </style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-clipboard-check"></i> Evaluaciones de Cumplimiento</h1>
    <p><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; Visita: <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></p>
</div>

<div class="stats-bar">
    <div>Pendientes: <span id="cntPendiente"><?= count($evaluaciones) ?></span></div>
    <div>Cerrados hoy: <span id="cntCerrado">0</span></div>
</div>

<?php if (empty($evaluaciones)): ?>
<div class="empty-state">
    <i class="fas fa-check-circle"></i>
    <h3>¡Todo al día!</h3>
    <p>No hay ítems pendientes para este cliente.</p>
</div>
<?php else: ?>

<?php
// Agrupar por estandar
$grupos = [];
foreach ($evaluaciones as $ev) {
    $grupos[$ev['estandar'] ?? 'General'][] = $ev;
}
?>

<?php foreach ($grupos as $estandar => $items): ?>
<div class="estandar-group">
    <div class="estandar-title"><?= esc($estandar) ?></div>
    <?php foreach ($items as $ev):
        $val = trim($ev['evaluacion_inicial'] ?? '');
        if ($val === 'NO CUMPLE') {
            $cssExtra = '';
            $badgeHtml = '<span class="badge-nocumple">NO CUMPLE</span>';
        } else {
            $cssExtra = 'vacio';
            $badgeHtml = '<span class="badge-vacio">Sin evaluar</span>';
        }
    ?>
    <div class="item-card <?= $cssExtra ?>" id="card-<?= $ev['id_ev_ini'] ?>">
        <input type="checkbox" id="ev-<?= $ev['id_ev_ini'] ?>" data-id="<?= $ev['id_ev_ini'] ?>" data-valor="<?= (int)($ev['valor'] ?? 0) ?>">
        <div class="item-body">
            <div class="item-numeral"><?= esc($ev['numeral'] ?? '') ?></div>
            <div class="item-texto"><?= esc($ev['item_del_estandar'] ?? $ev['detalle_estandar'] ?? '') ?></div>
            <div class="item-estado" id="estado-<?= $ev['id_ev_ini'] ?>"><?= $badgeHtml ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php endif; ?>

<div class="toast" id="toast"></div>

<script>
var pendienteCount = <?= count($evaluaciones) ?>;
var cerradoCount   = 0;
var actaId = <?= (int) $acta['id'] ?>;
var actaToken = '<?= esc($token ?? '') ?>';

function showToast(msg) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(function() { t.style.display = 'none'; }, 2000);
}

document.querySelectorAll('input[type=checkbox]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        if (!this.checked) return;
        var id    = this.dataset.id;
        var valor = parseInt(this.dataset.valor) || 0;
        var self  = this;

        self.disabled = true;

        // Actualizar evaluacion_inicial = CUMPLE TOTALMENTE
        var fd = new FormData();
        fd.append('id', id);
        fd.append('acta_id', actaId);
        fd.append('token', actaToken);

        fetch('/acta-visita/evaluaciones-visita/update', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var card   = document.getElementById('card-' + id);
                var estado = document.getElementById('estado-' + id);
                card.classList.remove('', 'vacio');
                card.classList.add('cumple');
                estado.innerHTML = '<span class="badge-done">✔ CUMPLE TOTALMENTE</span>';
                pendienteCount--;
                cerradoCount++;
                document.getElementById('cntPendiente').textContent = pendienteCount;
                document.getElementById('cntCerrado').textContent   = cerradoCount;
                showToast('✔ Marcado como CUMPLE TOTALMENTE');
            } else {
                self.checked  = false;
                self.disabled = false;
                alert('Error al guardar. Intenta de nuevo.');
            }
        })
        .catch(function() {
            self.checked  = false;
            self.disabled = false;
            alert('Error de conexión.');
        });
    });
});
</script>
</body>
</html>
