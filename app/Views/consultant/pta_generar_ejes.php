<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar PTA por Eje Temático — <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { background:#f4f5f7; }
        .header { background:#1c2437; color:#fff; padding:14px 20px; }
        .eje-card { border:1px solid #dfe3eb; border-radius:8px; background:#fff; margin-bottom:14px; }
        .eje-header { padding:10px 14px; background:#eef1f6; border-radius:8px 8px 0 0; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
        .eje-header h6 { margin:0; font-size:14px; color:#1c2437; }
        .eje-header .badge-count { background:#bd9751; color:#fff; padding:2px 8px; border-radius:12px; font-size:11px; }
        .eje-body { padding:0; display:none; }
        .eje-card.open .eje-body { display:block; }
        .eje-card.open .eje-header .chev { transform: rotate(90deg); }
        .chev { transition: transform .15s; }
        .act-row { display:grid; grid-template-columns: 38px 1fr 160px 90px; align-items:center; gap:10px; padding:8px 14px; border-top:1px solid #f0f1f4; font-size:13px; }
        .act-row:hover { background:#fafbfc; }
        .act-row .tarea { color:#333; }
        .act-row .componente { font-size:11px; color:#666; font-style:italic; margin-bottom:2px; }
        .act-row .badge-existe { background:#198754; color:#fff; padding:2px 8px; border-radius:10px; font-size:10px; }
        .act-row.existe { background:#f0f9f3; opacity:.85; }
        .act-row input[type="date"] { font-size:12px; padding:3px 6px; }
        .toolbar { position:sticky; top:0; z-index:10; background:#fff; padding:10px 14px; border-bottom:1px solid #e0e3e9; display:flex; gap:10px; align-items:center; }
        .toolbar .selected-count { font-weight:600; color:#bd9751; }
        .footer-bar { position:sticky; bottom:0; background:#fff; padding:12px 16px; border-top:1px solid #e0e3e9; display:flex; justify-content:space-between; align-items:center; }
    </style>
</head>
<body>
    <div class="header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-list-check me-2"></i> Generar PTA por Eje Temático</h5>
        <a href="<?= base_url('/pta-cliente-nueva/list?cliente=' . (int) $cliente['id_cliente']) ?>" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="container-fluid py-3">
        <div class="alert alert-info py-2 mb-3" style="font-size:13px;">
            <strong>Cliente:</strong> <?= esc($cliente['nombre_cliente']) ?>
            &nbsp;·&nbsp; <strong>Año actual:</strong> <?= (int) $anio ?>
            &nbsp;·&nbsp; <i class="fas fa-info-circle"></i>
            Selecciona las tareas, asigna fecha individual y presiona <em>"Crear seleccionadas"</em>. Las actividades con <span class="badge-existe"><i class="fas fa-check"></i> ya existe</span> ya están creadas en el PTA del cliente este año (puedes igual marcarlas si quieres duplicarlas).
        </div>

        <div class="toolbar mb-3">
            <button type="button" id="btnExpandAll" class="btn btn-sm btn-outline-secondary"><i class="fas fa-expand"></i> Expandir todos</button>
            <button type="button" id="btnCollapseAll" class="btn btn-sm btn-outline-secondary"><i class="fas fa-compress"></i> Contraer todos</button>
            <span class="ms-auto">Seleccionadas: <span class="selected-count" id="selectedCount">0</span></span>
            <button type="button" id="btnLimpiarSeleccion" class="btn btn-sm btn-outline-warning"><i class="fas fa-eraser"></i> Limpiar selección</button>
        </div>

        <?php foreach ($ejes as $eje): ?>
            <?php $totalEje = count($eje['actividades']); ?>
            <div class="eje-card" data-eje-slug="<?= esc($eje['slug']) ?>">
                <div class="eje-header" onclick="this.parentElement.classList.toggle('open')">
                    <h6>
                        <i class="fas <?= esc($eje['icon']) ?> me-2"></i>
                        <?= esc($eje['titulo']) ?>
                        <span class="badge-count ms-2"><?= $totalEje ?> tarea<?= $totalEje === 1 ? '' : 's' ?></span>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-check-label" style="font-size:11px; cursor:pointer;" onclick="event.stopPropagation()">
                            <input type="checkbox" class="form-check-input check-all-eje" data-eje="<?= esc($eje['slug']) ?>"> Todos
                        </label>
                        <i class="fas fa-chevron-right chev"></i>
                    </div>
                </div>
                <div class="eje-body">
                    <?php foreach ($eje['actividades'] as $act): ?>
                        <?php $existe = isset($existSet[$act['tarea']]); ?>
                        <div class="act-row <?= $existe ? 'existe' : '' ?>" data-eje="<?= esc($eje['slug']) ?>">
                            <div>
                                <input type="checkbox" class="form-check-input act-check"
                                       data-id="<?= esc($act['id']) ?>"
                                       data-eje="<?= esc($eje['slug']) ?>">
                            </div>
                            <div>
                                <div class="componente"><?= esc($act['componente']) ?></div>
                                <div class="tarea"><?= esc($act['tarea']) ?></div>
                            </div>
                            <div>
                                <input type="date" class="form-control form-control-sm act-fecha"
                                       data-id="<?= esc($act['id']) ?>"
                                       value="<?= esc(date('Y-m-d')) ?>">
                            </div>
                            <div class="text-end">
                                <?php if ($existe): ?>
                                    <span class="badge-existe"><i class="fas fa-check"></i> ya existe</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer-bar">
        <div>
            <span class="text-muted" style="font-size:12px;">
                <i class="fas fa-info-circle"></i> Las actividades creadas aquí van al PTA del cliente sin vincularse a la Matriz de Inspecciones (son de gestión estratégica).
            </span>
        </div>
        <button type="button" id="btnCrearSeleccionadas" class="btn btn-success" disabled>
            <i class="fas fa-plus-circle me-1"></i> Crear seleccionadas (<span id="footerCount">0</span>)
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ID_CLIENTE = <?= (int) $cliente['id_cliente'] ?>;
        const URL_GUARDAR = '<?= base_url('/pta-cliente-nueva/generar-ejes-guardar') ?>';
        const CSRF_NAME  = '<?= csrf_token() ?>';
        const CSRF_HASH  = '<?= csrf_hash() ?>';

        function updateSelectedCount() {
            const n = document.querySelectorAll('.act-check:checked').length;
            document.getElementById('selectedCount').textContent = n;
            document.getElementById('footerCount').textContent = n;
            document.getElementById('btnCrearSeleccionadas').disabled = (n === 0);

            // Sincronizar checkboxes "Todos" por eje
            document.querySelectorAll('.check-all-eje').forEach(el => {
                const eje = el.getAttribute('data-eje');
                const checks = document.querySelectorAll('.act-check[data-eje="' + eje + '"]');
                const checked = document.querySelectorAll('.act-check[data-eje="' + eje + '"]:checked').length;
                el.checked = (checks.length > 0 && checked === checks.length);
                el.indeterminate = (checked > 0 && checked < checks.length);
            });
        }

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('act-check')) {
                updateSelectedCount();
            } else if (e.target.classList.contains('check-all-eje')) {
                const eje = e.target.getAttribute('data-eje');
                document.querySelectorAll('.act-check[data-eje="' + eje + '"]').forEach(c => c.checked = e.target.checked);
                updateSelectedCount();
            }
        });

        document.getElementById('btnExpandAll').addEventListener('click', function () {
            document.querySelectorAll('.eje-card').forEach(c => c.classList.add('open'));
        });
        document.getElementById('btnCollapseAll').addEventListener('click', function () {
            document.querySelectorAll('.eje-card').forEach(c => c.classList.remove('open'));
        });
        document.getElementById('btnLimpiarSeleccion').addEventListener('click', function () {
            document.querySelectorAll('.act-check').forEach(c => c.checked = false);
            updateSelectedCount();
        });

        document.getElementById('btnCrearSeleccionadas').addEventListener('click', function () {
            const seleccionadas = [];
            let sinFecha = 0;
            document.querySelectorAll('.act-check:checked').forEach(c => {
                const id = c.getAttribute('data-id');
                const fechaEl = document.querySelector('.act-fecha[data-id="' + id + '"]');
                const fecha = fechaEl ? fechaEl.value : '';
                if (!fecha) { sinFecha++; return; }
                seleccionadas.push({ id: id, fecha: fecha });
            });

            if (sinFecha > 0) {
                Swal.fire('Falta fecha', sinFecha + ' actividad(es) seleccionada(s) no tienen fecha asignada.', 'warning');
                return;
            }
            if (!seleccionadas.length) {
                Swal.fire('Nada que crear', 'No has seleccionado actividades.', 'info');
                return;
            }

            Swal.fire({
                title: 'Crear ' + seleccionadas.length + ' actividad(es) en el PTA',
                text: '¿Confirmas la creación con las fechas asignadas?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754'
            }).then(function (r) {
                if (!r.isConfirmed) return;
                const btn = document.getElementById('btnCrearSeleccionadas');
                const orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';

                $.ajax({
                    url: URL_GUARDAR,
                    method: 'POST',
                    data: {
                        id_cliente: ID_CLIENTE,
                        seleccionadas: seleccionadas,
                        [CSRF_NAME]: CSRF_HASH
                    },
                    dataType: 'json',
                    traditional: false
                }).done(function (res) {
                    if (res.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Listo',
                            text: res.creadas + ' actividad(es) creada(s) en el PTA.',
                            confirmButtonText: 'Ir al PTA'
                        }).then(function () {
                            window.location.href = '<?= base_url('/pta-cliente-nueva/list?cliente=' . (int) $cliente['id_cliente']) ?>';
                        });
                    } else {
                        Swal.fire('Error', res.msg || 'No se pudieron crear.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = orig;
                    }
                }).fail(function () {
                    Swal.fire('Error', 'Error de red.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = orig;
                });
            });
        });

        // Auto-expandir el primer eje al cargar
        document.querySelector('.eje-card')?.classList.add('open');
    </script>
</body>
</html>
