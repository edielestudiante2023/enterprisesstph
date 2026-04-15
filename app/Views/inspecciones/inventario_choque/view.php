<?php
$csrfName  = csrf_token();
$csrfHash  = csrf_hash();
$toggleUrl = base_url('/inspecciones/inventario-choque/toggle');
?>
<style>
.ic-sticky {
    position: sticky; top: 0; z-index: 10;
    background: #1c2437; color: #fff;
    padding: 10px 12px; border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    margin-bottom: 12px;
}
.ic-sticky .ic-title { font-size: 13px; font-weight: 600; }
.ic-sticky .ic-sub   { font-size: 11px; color: #bd9751; }
.ic-progress { height: 10px; background: rgba(255,255,255,0.15); border-radius: 6px; overflow: hidden; margin-top: 6px; }
.ic-progress > div { height: 100%; background: #28a745; transition: width .3s; }
.ic-count { font-size: 14px; font-weight: 700; }

.ic-cat-title {
    font-size: 12px; font-weight: 700; text-transform: uppercase;
    color: #1c2437; letter-spacing: 0.5px;
    padding: 8px 4px 6px; margin-top: 10px;
    border-bottom: 2px solid #bd9751;
}
.ic-cat-title .ic-cat-count { font-size: 11px; color: #6c757d; font-weight: 500; float: right; }

.ic-grid {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 8px;
}
@media (min-width: 768px) { .ic-grid { grid-template-columns: repeat(3, 1fr); } }

.ic-item {
    background: #fff; border: 2px solid #e0e0e0; border-radius: 10px;
    padding: 14px 10px; text-align: center; cursor: pointer;
    font-size: 12px; font-weight: 500; color: #1c2437;
    transition: all .18s ease;
    display: flex; align-items: center; justify-content: center; min-height: 64px;
    user-select: none;
    position: relative;
}
.ic-item:active { transform: scale(0.97); }
.ic-item.marked {
    background: #d4edda; border-color: #28a745; color: #155724;
    text-decoration: line-through;
}
.ic-item.marked::before {
    content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
    position: absolute; top: 6px; right: 8px; color: #28a745; font-size: 14px;
}
.ic-item.saving { opacity: 0.6; }
</style>

<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <a href="<?= base_url('/inspecciones/inventario-choque') ?>" class="btn btn-sm btn-outline-secondary" style="padding:4px 10px;"><i class="fas fa-arrow-left"></i></a>
        <a href="<?= base_url('/inspecciones/inventario-choque/edit/'.$inv['id']) ?>" class="btn btn-sm btn-outline-dark" style="padding:4px 10px;"><i class="fas fa-edit"></i> Datos</a>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <div class="ic-sticky">
        <div class="ic-title"><i class="fas fa-clipboard-check"></i> <?= esc($cliente['nombre_cliente'] ?? 'Sin cliente') ?></div>
        <div class="ic-sub"><?= date('d/m/Y', strtotime($inv['fecha_captura'])) ?></div>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <div class="ic-count"><span id="icMarcados"><?= $marcados ?></span> <small style="opacity:.7;">de</small> <span id="icTotal"><?= $total ?></span> fotos</div>
            <div id="icPctLabel" style="font-size:12px; color:#bd9751;"><?= $total > 0 ? round(($marcados/$total)*100) : 0 ?>%</div>
        </div>
        <div class="ic-progress"><div id="icBar" style="width: <?= $total > 0 ? round(($marcados/$total)*100) : 0 ?>%;"></div></div>
    </div>

    <?php foreach ($grouped as $categoria => $items):
        $catMarcados = 0;
        foreach ($items as $it) if ((int)$it['marcado'] === 1) $catMarcados++;
    ?>
        <div class="ic-cat-title">
            <?= esc($categoria) ?>
            <span class="ic-cat-count"><span class="cat-marcados" data-cat="<?= esc($categoria) ?>"><?= $catMarcados ?></span>/<?= count($items) ?></span>
        </div>
        <div class="ic-grid">
            <?php foreach ($items as $it): ?>
                <div class="ic-item <?= (int)$it['marcado'] === 1 ? 'marked' : '' ?>"
                     data-id="<?= $it['id'] ?>"
                     data-cat="<?= esc($it['categoria']) ?>">
                    <?= esc($it['item']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <div class="text-center mt-4 mb-3">
        <a href="<?= base_url('/inspecciones/inventario-choque') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-list"></i> Volver al listado
        </a>
    </div>
</div>

<script>
(function() {
    const TOGGLE_URL = '<?= $toggleUrl ?>';
    const CSRF_NAME  = '<?= $csrfName ?>';
    let csrfHash     = '<?= $csrfHash ?>';

    const bar        = document.getElementById('icBar');
    const pctLabel   = document.getElementById('icPctLabel');
    const marcadosEl = document.getElementById('icMarcados');
    const totalEl    = document.getElementById('icTotal');

    function refreshHeader(marcados, total) {
        marcadosEl.textContent = marcados;
        totalEl.textContent    = total;
        const pct = total > 0 ? Math.round((marcados/total)*100) : 0;
        bar.style.width     = pct + '%';
        pctLabel.textContent = pct + '%';
    }

    function refreshCatCount(cat) {
        const items = document.querySelectorAll('.ic-item[data-cat="'+ CSS.escape(cat) +'"]');
        const marked = document.querySelectorAll('.ic-item[data-cat="'+ CSS.escape(cat) +'"].marked').length;
        document.querySelectorAll('.cat-marcados[data-cat="'+ CSS.escape(cat) +'"]').forEach(el => {
            el.textContent = marked;
        });
    }

    document.querySelectorAll('.ic-item').forEach(el => {
        el.addEventListener('click', function() {
            if (el.classList.contains('saving')) return;
            const willMark = !el.classList.contains('marked');
            el.classList.toggle('marked', willMark);
            el.classList.add('saving');
            refreshCatCount(el.dataset.cat);

            const fd = new FormData();
            fd.append('id_item', el.dataset.id);
            fd.append('marcado', willMark ? '1' : '0');
            fd.append(CSRF_NAME, csrfHash);

            fetch(TOGGLE_URL, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(j => {
                el.classList.remove('saving');
                if (!j.ok) {
                    el.classList.toggle('marked', !willMark);
                    refreshCatCount(el.dataset.cat);
                    Swal.fire({ icon:'error', title:'No se pudo guardar', text: j.error || '' });
                    return;
                }
                refreshHeader(j.marcados, j.total);
                if (j.csrf_hash) csrfHash = j.csrf_hash;
            })
            .catch(err => {
                el.classList.remove('saving');
                el.classList.toggle('marked', !willMark);
                refreshCatCount(el.dataset.cat);
                Swal.fire({ icon:'error', title:'Error de red', text:'Intenta nuevamente' });
            });
        });
    });
})();
</script>
