<?php
$totalHechas         = 0;
$totalPend           = 0;
$totalAtrasadas      = 0;
$totalNoAplica       = 0;
$totalAlDia          = 0;
$totalPorSincronizar = 0;
foreach ($filas as $f) {
    if      ($f['estado'] === 'hecha')     $totalHechas++;
    elseif  ($f['estado'] === 'al_dia')    $totalAlDia++;
    elseif  ($f['estado'] === 'pendiente') $totalPend++;
    elseif  ($f['estado'] === 'atrasada')  $totalAtrasadas++;
    else                                   $totalNoAplica++;

    // "Por sincronizar": tiene inspecciones realizadas y o bien hay PTAs abiertas, o no hay PTAs
    $ptaAbiertasC = 0;
    foreach ($f['pta_vinculados'] as $v) {
        if (($v['estado_actividad'] ?? '') !== 'CERRADA') $ptaAbiertasC++;
    }
    $tieneRealiz = $f['total'] > 0 || (int) ($f['realizadas_anio'] ?? 0) > 0;
    if ($tieneRealiz && ($ptaAbiertasC > 0 || empty($f['pta_vinculados']))) {
        $totalPorSincronizar++;
    }
}
$totalRealizadas = $totalHechas + $totalAlDia;

// "Faltan para estar al día": inspecciones que faltan para cumplir la meta anual
// configurada, sobre los tipos con frecuencia definida (>0) aún no alcanzada.
// También: $planeadasCounts (cantidad de PTA planeadas => nº de filas, excluye
// No Aplica) y $frecuenciasSet (valores distintos de veces_anio).
$totalFaltantes  = 0;
$totalSinMeta    = 0;
$planeadasCounts = []; // [cantidad_de_planeadas => nº de filas]
$frecuenciasSet  = [];
foreach ($filas as $f) {
    $vaRaw = $f['veces_anio'] ?? null;
    $frecuenciasSet[$vaRaw === null ? 'sin_definir' : (string) (int) $vaRaw] = true;

    if ($f['estado'] === 'no_aplica') continue;

    $va = $f['veces_anio'] ?? null;
    $ra = (int) ($f['realizadas_anio'] ?? 0);
    if ($va === null)             $totalSinMeta++;
    elseif ($va > 0 && $ra < $va) $totalFaltantes += ($va - $ra);

    $nPlaneadas = 0;
    foreach ($f['pta_vinculados'] as $v) {
        if (!empty($v['fecha_propuesta'])) $nPlaneadas++;
    }
    $planeadasCounts[$nPlaneadas] = ($planeadasCounts[$nPlaneadas] ?? 0) + 1;
}
// Orden del dropdown de frecuencia: 'sin_definir' primero, luego numérico ascendente.
$frecuenciasOpciones = array_keys($frecuenciasSet);
usort($frecuenciasOpciones, function ($a, $b) {
    if ($a === 'sin_definir') return -1;
    if ($b === 'sin_definir') return 1;
    return (int) $a <=> (int) $b;
});
// Orden del dropdown de planeadas: cantidades ascendentes (0, 1, 2, ...).
ksort($planeadasCounts);

$totalTodos = count($filas);
$aplicables = $totalTodos - $totalNoAplica;
// 'Al día' cuenta como cumplido para cobertura (la inspección está vigente según frecuencia)
$cobertura  = $aplicables > 0 ? round((($totalHechas + $totalAlDia) / $aplicables) * 100) : 0;

?>
<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="fas fa-th-list"></i> Matriz de Inspecciones</h6>
        <a href="<?= base_url('inspecciones/matriz') ?>" class="btn btn-sm btn-outline-secondary" style="padding:4px 10px;font-size:12px;">
            <i class="fas fa-arrow-left"></i> Cambiar cliente
        </a>
    </div>

    <?php
    $idClienteUrl = (int) $cliente['id_cliente'];
    $baseMatrizUrl = base_url('inspecciones/matriz/' . $idClienteUrl);
    $anioActualPhp = (int) date('Y');
    $rangoEsAnioCompleto = ($fechaDesde === $anio . '-01-01' && $fechaHasta === $anio . '-12-31');
    $mesesAbrev = ['Ene.','Feb.','Mar.','Abr.','May.','Jun.','Jul.','Ago.','Sept.','Oct.','Nov.','Dic.'];
    $mesesNombre = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    // Frecuencia del contrato (mensual/bimensual/trimestral)
    $frecuenciaTxt = $lastContract['frecuencia_visitas'] ?? '';
    $frecLow = strtolower($frecuenciaTxt);
    $frecBg = '#6c757d';
    if (strpos($frecLow, 'bimensual') !== false)        $frecBg = '#0d6efd';
    elseif (strpos($frecLow, 'trimestral') !== false)   $frecBg = '#fd7e14';
    elseif (strpos($frecLow, 'mensual') !== false)      $frecBg = '#198754';
    elseif (strpos($frecLow, 'semestral') !== false)    $frecBg = '#6610f2';
    elseif (strpos($frecLow, 'anual') !== false)        $frecBg = '#dc3545';

    // Texto del header de la columna "Fechas en ..."
    if ($mesActivo) {
        $colFechasHeader = 'Fechas en ' . $mesesNombre[$mesActivo - 1] . ' ' . (int) $anio;
    } elseif ($rangoEsAnioCompleto) {
        $colFechasHeader = 'Fechas en ' . (int) $anio;
    } else {
        $colFechasHeader = 'Fechas ' . date('d/m/Y', strtotime($fechaDesde)) . ' – ' . date('d/m/Y', strtotime($fechaHasta));
    }
    ?>
    <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #1c2437 0%, #2a3449 100%); border-radius:12px;">
        <div class="card-body py-3 px-3">
            <div style="color:#bd9751; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Cliente</div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span style="color:#fff; font-size:17px; font-weight:600;"><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
                <?php if (!empty($frecuenciaTxt)): ?>
                    <span class="badge" style="background:<?= $frecBg ?>; color:#fff; font-size:11px; padding:4px 10px; border-radius:12px;">
                        <i class="fas fa-calendar-check"></i> <?= esc($frecuenciaTxt) ?>
                    </span>
                <?php endif; ?>
                <?php if ($mesActivo): ?>
                    <span class="badge" style="background:#bd9751; color:#fff; font-size:11px; padding:4px 10px; border-radius:12px;">
                        <i class="fas fa-filter"></i> Filtrando: <?= $mesesNombre[$mesActivo - 1] ?> <?= (int) $anio ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if (!empty($cliente['nit_cliente'])): ?>
                <div style="color:rgba(255,255,255,0.6); font-size:12px;">NIT <?= esc($cliente['nit_cliente']) ?></div>
            <?php endif; ?>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <form method="get" action="<?= $baseMatrizUrl ?>" class="d-flex align-items-center gap-2 m-0">
                    <label for="anio" style="color:#fff; font-size:12px; margin:0;">Año:</label>
                    <select name="anio" id="anio" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                        <?php foreach ($aniosDisponibles as $y): ?>
                            <option value="<?= (int) $y ?>" <?= ((int) $y === (int) $anio) ? 'selected' : '' ?>>
                                <?= (int) $y ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <button class="btn btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#matrizFiltrosPanel"
                    style="background:#bd9751; color:#fff; border:none; padding:5px 12px; font-size:12px; border-radius:6px;">
                    <i class="fas fa-layer-group"></i> Filtros por Tarjetas
                    <i class="fas fa-chevron-down ms-1" style="font-size:10px;"></i>
                </button>

                <span style="color:rgba(255,255,255,0.85); font-size:11px;">
                    <i class="fas fa-calendar"></i>
                    Rango: <strong><?= date('d/m/Y', strtotime($fechaDesde)) ?></strong> – <strong><?= date('d/m/Y', strtotime($fechaHasta)) ?></strong>
                </span>
            </div>
        </div>
    </div>

    <!-- Cards Mes: siempre visibles para navegar rapido por el año activo -->
    <div class="card border-0 mb-3" style="background:#f8f9fa; border-radius:12px;">
        <div class="card-body py-3 px-3">
            <div class="section-title-matriz">
                <i class="fas fa-calendar-week"></i> Filtrar por Mes <small style="font-weight:normal; color:#888;">(año <?= (int) $anio ?>)</small>
            </div>
            <div class="row g-2 mb-0">
                <?php for ($m = 1; $m <= 12; $m++):
                    $cm = (int) ($inspeccionesPorMes[$m] ?? 0);
                    $ultDia = (int) date('t', strtotime(sprintf('%04d-%02d-01', (int) $anio, $m)));
                    $urlMes = $baseMatrizUrl . '?fecha_desde=' . sprintf('%04d-%02d-01', (int) $anio, $m)
                            . '&fecha_hasta=' . sprintf('%04d-%02d-%02d', (int) $anio, $m, $ultDia);
                    $isActiveMes = ($mesActivo === $m);
                ?>
                    <div class="col-6 col-md-1">
                        <a href="<?= $urlMes ?>" class="text-decoration-none">
                            <div class="card border-0 card-matriz-filtro card-mes<?= $isActiveMes ? ' active' : '' ?>" data-mes="<?= $m ?>">
                                <div class="card-body text-center p-2">
                                    <div style="font-size:11px; font-weight:600; color:#fff;"><?= $mesesAbrev[$m - 1] ?></div>
                                    <div class="matriz-mes-count" style="font-size:16px; font-weight:700; color:#fff; line-height:1;"><?= $cm ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Panel colapsable: cards Año / Rango Desde-Hasta -->
    <div class="collapse mb-3" id="matrizFiltrosPanel">
        <div class="card border-0" style="background:#f8f9fa; border-radius:12px;">
            <div class="card-body py-3 px-3">

                <!-- Cards Año -->
                <div class="section-title-matriz">
                    <i class="fas fa-calendar-alt"></i> Filtrar por Año
                </div>
                <div class="row g-2 mb-3">
                    <?php foreach ($aniosDisponibles as $y):
                        $yInt = (int) $y;
                        $count = (int) ($inspeccionesPorAnio[$yInt] ?? 0);
                        $urlAnio = $baseMatrizUrl . '?fecha_desde=' . $yInt . '-01-01&fecha_hasta=' . $yInt . '-12-31';
                        $isActive = ($yInt === (int) $anio && $rangoEsAnioCompleto);
                    ?>
                        <div class="col-6 col-md-2">
                            <a href="<?= $urlAnio ?>" class="text-decoration-none">
                                <div class="card border-0 card-matriz-filtro card-anio<?= $isActive ? ' active' : '' ?>" data-anio="<?= $yInt ?>">
                                    <div class="card-body text-center p-2">
                                        <div style="font-size:15px; font-weight:700; color:#fff;"><?= $yInt ?></div>
                                        <div class="matriz-anio-count" style="font-size:20px; font-weight:700; color:#fff; line-height:1;"><?= $count ?></div>
                                        <small style="font-size:10px; color:rgba(255,255,255,0.85);">inspecciones</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Rango Fecha Desde / Hasta -->
                <div class="section-title-matriz">
                    <i class="fas fa-calendar-day"></i> Rango personalizado
                </div>
                <form method="get" action="<?= $baseMatrizUrl ?>" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="fecha_desde" class="form-label" style="font-size:12px; margin-bottom:2px; color:#555;">
                            <i class="fas fa-calendar-plus"></i> Fecha Desde
                        </label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control form-control-sm" value="<?= esc($fechaDesde) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_hasta" class="form-label" style="font-size:12px; margin-bottom:2px; color:#555;">
                            <i class="fas fa-calendar-minus"></i> Fecha Hasta
                        </label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control form-control-sm" value="<?= esc($fechaHasta) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background:#bd9751; color:#fff; border:none; padding:5px 12px; font-size:12px;">
                            <i class="fas fa-filter"></i> Aplicar
                        </button>
                        <a href="<?= $baseMatrizUrl ?>?anio=<?= $anioActualPhp ?>" class="btn btn-sm btn-outline-secondary" style="font-size:12px;">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cards clickeables para filtrar la tabla -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro active" data-filtro="todas" style="background:#eef2f7; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#1c2437; font-weight:600;"><i class="fas fa-list"></i> Todas</div>
                    <div class="card-count-num" style="font-size:20px; font-weight:700; color:#1c2437;"><?= $totalTodos ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="realizadas" style="background:#d4edda; border-radius:10px; cursor:pointer;" title="Inspecciones realizadas — cumplan o no la meta anual configurada">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#155724; font-weight:600;"><i class="fas fa-check-circle"></i> Realizadas</div>
                    <div class="card-count-num" style="font-size:20px; font-weight:700; color:#155724;"><?= $totalRealizadas ?></div>
                    <div id="cardRealizadasSubtext">
                        <?php if ($totalFaltantes > 0): ?>
                            <div style="font-size:9px; color:#0f5132; font-weight:600; line-height:1.15;">
                                Faltan <?= $totalFaltantes ?> para estar al día
                            </div>
                        <?php elseif ($totalSinMeta > 0): ?>
                            <div style="font-size:9px; color:#856404; font-weight:600; line-height:1.15;">
                                <?= $totalSinMeta ?> sin meta definida
                            </div>
                        <?php elseif ($totalRealizadas > 0): ?>
                            <div style="font-size:9px; color:#0f5132; font-weight:600; line-height:1.15;">
                                <i class="fas fa-shield-alt"></i> Todas al día
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="por_sincronizar" style="background:#cfe2ff; border-radius:10px; cursor:pointer;" title="Hay inspecciones realizadas pendientes de cerrar en el Plan de Trabajo">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#084298; font-weight:600;"><i class="fas fa-clipboard-check"></i> Por sincronizar</div>
                    <div class="card-count-num" style="font-size:20px; font-weight:700; color:#084298;"><?= $totalPorSincronizar ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="pendiente" style="background:#fff3cd; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#856404; font-weight:600;"><i class="fas fa-clock"></i> Pendientes</div>
                    <div class="card-count-num" style="font-size:20px; font-weight:700; color:#856404;"><?= $totalPend ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="atrasada" style="background:#f8d7da; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#721c24; font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Atrasadas</div>
                    <div class="card-count-num" style="font-size:20px; font-weight:700; color:#721c24;"><?= $totalAtrasadas ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="no_aplica" style="background:#e2e3e5; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#383d41; font-weight:600;"><i class="fas fa-ban"></i> No Aplica</div>
                    <div class="card-count-num" style="font-size:20px; font-weight:700; color:#383d41;"><?= $totalNoAplica ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 mb-3" style="background:#f8f9fa; border-radius:10px;">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <span style="font-size:12px; color:#555;"><i class="fas fa-chart-pie"></i> Cobertura (sobre aplicables)</span>
            <span id="coberturaPct" style="font-size:18px; font-weight:700; color:#2e7d4f;"><?= $cobertura ?>%</span>
        </div>
    </div>

    <div class="card border-0 mb-2" style="background:#fff; border-radius:10px;">
        <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center gap-2">
            <span id="bulkSelectedCount" class="small text-muted me-auto" style="font-size:12px;">
                <i class="fas fa-check-square"></i> 0 seleccionadas
            </span>
            <button type="button" id="btnBulkFrecuencia" class="btn btn-sm btn-outline-primary" style="font-size:12px; padding:5px 10px;" disabled>
                <i class="fas fa-redo-alt"></i> Definir frecuencia
            </button>
            <button type="button" id="btnBulkNoAplica" class="btn btn-sm btn-outline-secondary" style="font-size:12px; padding:5px 10px;" disabled>
                <i class="fas fa-ban"></i> No Aplica
            </button>
            <button type="button" id="btnBulkCerrarPta" class="btn btn-sm" style="font-size:12px; padding:5px 10px; background:#87ceeb; border-color:#6bbfe3; color:#0b3d4f;" disabled>
                <i class="fas fa-print"></i> Imprimir en PTA
            </button>
            <button type="button" id="btnBulkClear" class="btn btn-sm btn-outline-dark" style="font-size:12px; padding:5px 10px;" disabled>
                <i class="fas fa-times"></i> Limpiar
            </button>
            <button type="button" id="btnLimpiarFiltrosMatriz" class="btn btn-sm btn-outline-secondary" style="font-size:12px; padding:5px 10px;" title="Limpia card, Grupo, Tipo, Estado, Frecuencia y Planeadas">
                <i class="fas fa-broom"></i> Limpiar todos los filtros
            </button>
            <button type="button" id="btnRefrescarTodoMatriz" class="btn btn-sm btn-outline-info" style="font-size:12px; padding:5px 10px;" title="Recarga todas las filas con datos frescos (PTAs, inspecciones, frecuencias y vinculaciones modificadas desde otros módulos)">
                <i class="fas fa-sync-alt"></i> Refrescar todo
            </button>
            <button type="button" id="btnLimpiarFrecuencias" class="btn btn-sm btn-outline-danger" style="font-size:12px; padding:5px 10px;" title="Borra TODAS las frecuencias (veces/año) configuradas del cliente. Útil para reconfigurar tras upgrade de contrato (trimestral → bimensual → mensual). Pide doble validación.">
                <i class="fas fa-eraser"></i> Limpiar frecuencias
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tablaMatriz" class="table table-sm table-hover" style="width:100%; background:#fff;">
            <thead style="background:#1c2437; color:#fff;">
                <tr>
                    <th style="font-size:12px;">Grupo</th>
                    <th style="font-size:12px; text-align:center; width:42px;">
                        <input type="checkbox" id="bulkSelectVisible" title="Seleccionar filas visibles">
                    </th>
                    <th style="font-size:12px;">Tipo de Inspección</th>
                    <th style="font-size:12px;"><?= esc($colFechasHeader) ?></th>
                    <th style="font-size:12px;">Estado</th>
                    <th style="font-size:12px; text-align:right;">Acciones</th>
                </tr>
                <tr style="background:#2a3449;">
                    <th><input type="text" class="form-control form-control-sm col-filter" data-col="0" placeholder="Filtrar grupo" style="font-size:11px;"></th>
                    <th></th>
                    <th>
                        <input type="text" class="form-control form-control-sm col-filter" data-col="2" placeholder="Filtrar tipo" style="font-size:11px;">
                        <select class="form-select form-select-sm filtro-frecuencia" style="font-size:11px; margin-top:4px;">
                            <option value="">Frecuencia: todas</option>
                            <?php foreach ($frecuenciasOpciones as $fk): ?>
                                <option value="<?= esc($fk) ?>"><?php
                                    if ($fk === 'sin_definir')      echo 'Sin definir';
                                    elseif ($fk === '0')            echo 'Puntual';
                                    elseif ($fk === '1')            echo '1 vez al año';
                                    else                            echo esc($fk) . ' veces al año';
                                ?></option>
                            <?php endforeach; ?>
                        </select>
                    </th>
                    <th>
                        <select class="form-select form-select-sm filtro-fechas" style="font-size:11px;">
                            <option value="">Planeadas: todas</option>
                            <?php foreach ($planeadasCounts as $cant => $nFilas): ?>
                                <option value="<?= (int) $cant ?>"><?= (int) $cant ?> planeada<?= $cant == 1 ? '' : 's' ?> (<?= (int) $nFilas ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </th>
                    <th>
                        <select class="form-select form-select-sm col-filter" data-col="4" style="font-size:11px;">
                            <option value="">Todos</option>
                            <option value="Realizadas">Realizadas</option>
                            <option value="Por sincronizar">Por sincronizar</option>
                            <option value="Pendiente">Pendientes</option>
                            <option value="Atrasada">Atrasadas</option>
                            <option value="No Aplica">No Aplica</option>
                        </select>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $f): ?>
                <?= view('inspecciones/matriz/_fila', ['f' => $f, 'cliente' => $cliente]) ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal definir/cambiar frecuencia -->
<div class="modal fade" id="modalFrecuencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1c2437; color:#fff;">
                <h6 class="modal-title"><i class="fas fa-redo-alt"></i> Veces por año</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="font-size:13px;">
                    <strong>Tipo:</strong> <span id="frecModalLabel" style="color:#bd9751;"></span>
                </div>
                <p class="small text-muted mt-2" style="font-size:11px;">
                    Cantidad de veces que debe realizarse esta inspección en el año, para este cliente.
                    La matriz usa este valor para marcar como "Al día" cuando se cumple la meta.
                </p>
                <div class="mt-2">
                    <label for="frecModalInput" class="form-label small fw-bold">Veces por año</label>
                    <input type="number" id="frecModalInput" class="form-control" min="0" max="365" step="1" placeholder="Ej: 1, 4, 12">
                    <small class="text-muted" style="font-size:11px;">0 = puntual (sin frecuencia fija)</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="frecModalBtnGuardar" style="background:#bd9751; border-color:#bd9751;">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal vincular PTA -->
<div class="modal fade" id="modalVincularPta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1c2437; color:#fff;">
                <h6 class="modal-title"><i class="fas fa-link"></i> Vincular actividades del Plan de Trabajo</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="font-size:12px;">
                    <strong>Tipo:</strong> <span id="modalSlugLabel" style="color:#bd9751;"></span>
                </div>
                <div class="small text-muted mb-2" style="font-size:11px;">
                    Marca las actividades del PTA que correspondan a este tipo de inspección. El vínculo sirve para mostrar la fecha propuesta en la matriz.
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <input type="text" id="modalBuscador" class="form-control form-control-sm" placeholder="Buscar por numeral, actividad o PHVA..." style="font-size:12px;">
                    <div class="form-check form-switch mb-0" style="white-space:nowrap;">
                        <input class="form-check-input" type="checkbox" id="modalToggleCerradas">
                        <label class="form-check-label small" for="modalToggleCerradas" style="font-size:11px;">Incluir cerradas</label>
                    </div>
                </div>
                <div id="modalPtaList" style="font-size:12px;">
                    <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                </div>
            </div>
            <div class="modal-footer">
                <span id="modalCountSelected" class="me-auto small text-muted"></span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="modalBtnGuardar" style="background:#bd9751; border-color:#bd9751;">
                    <i class="fas fa-save"></i> Guardar vínculos
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.card-filtro { transition: transform .15s, box-shadow .15s, outline .15s; outline: 2px solid transparent; }
.card-filtro:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
.card-filtro.active { outline: 2px solid #bd9751; box-shadow: 0 4px 10px rgba(189,151,81,0.25); }

/* Filtros año/mes/rango */
.section-title-matriz {
    font-size: 12px; font-weight: 700; color: #1c2437;
    border-left: 3px solid #bd9751; padding-left: 8px;
    margin: 6px 0 10px 0; text-transform: uppercase; letter-spacing: 0.4px;
}
.card-matriz-filtro {
    border-radius: 8px; cursor: pointer; transition: transform .15s, box-shadow .15s, outline .15s;
    outline: 2px solid transparent; min-height: 60px;
}
.card-matriz-filtro:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,0,0,0.12); }
.card-matriz-filtro.active { outline: 3px solid #bd9751; box-shadow: 0 0 0 4px rgba(189,151,81,0.25); transform: scale(1.04); }
.card-anio { background: linear-gradient(135deg, #1c2437 0%, #2a3449 100%); }
.card-mes  { background: linear-gradient(135deg, #2a3449 0%, #3a4659 100%); }
.pta-row { padding:6px 8px; border-bottom:1px solid #eef2f7; display:flex; align-items:flex-start; gap:8px; }
.pta-row:hover { background:#f8f9fa; }
.pta-row.pta-closed { opacity:0.7; background:#f8f9fa; }
.pta-row.pta-checked { background:#e8f1fb; }
.pta-row input[type=checkbox] { margin-top:3px; flex-shrink:0; }
.pta-row .pta-meta { font-size:11px; color:#666; }
.pta-row .pta-activity { font-size:12px; }

.pta-details { display:inline-block; }
.pta-details > .pta-summary {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 8px; background:#e8f1fb; color:#0b5ed7;
    border-radius:10px; font-size:10px; font-weight:600;
    cursor:pointer; list-style:none; user-select:none;
    transition: background .15s;
}
.pta-details > .pta-summary::-webkit-details-marker { display:none; }
.pta-details > .pta-summary::after {
    content:"\f107"; /* fa-chevron-down */
    font-family:"Font Awesome 6 Free", "Font Awesome 5 Free", FontAwesome;
    font-weight:900; margin-left:4px; font-size:9px;
    transition: transform .15s;
}
.pta-details[open] > .pta-summary::after { transform: rotate(180deg); }
.pta-details > .pta-summary:hover { background:#d6e7fb; }
.pta-details .pta-badges { display:flex; flex-wrap:wrap; gap:2px; max-width:100%; }
.pta-details .pta-list {
    display:flex; flex-direction:column; gap:4px; max-width:520px;
}
.pta-item {
    display:flex; gap:6px; align-items:flex-start; flex-wrap:wrap;
    background:#e8f1fb; padding:5px 8px; border-radius:6px;
    font-size:10px; line-height:1.35;
}
.pta-item.pta-item-cerrada { background:#d4edda; }
.pta-item .pta-date { color:#0b5ed7; font-weight:600; white-space:nowrap; flex-shrink:0; }
.pta-item.pta-item-cerrada .pta-date { color:#155724; }
.pta-item .pta-check { color:#155724; font-weight:700; margin-left:2px; }
.pta-item .pta-edit-fecha {
    color:#6c757d; cursor:pointer; margin-left:6px; font-size:10px;
    transition: color .15s;
}
.pta-item .pta-edit-fecha:hover { color:#bd9751; }
.pta-item .pta-reabrir {
    color:#856404; cursor:pointer; margin-left:6px; font-size:10px;
    transition: color .15s;
}
.pta-item .pta-reabrir:hover { color:#dc3545; }
.pta-item .pta-numeral {
    background:#fff; color:#555; padding:1px 5px; border-radius:4px;
    font-size:9px; font-weight:600; flex-shrink:0;
}
.pta-item .pta-estado-gest {
    background:#fff3cd; color:#856404; padding:1px 5px; border-radius:4px;
    font-size:9px; font-weight:600; flex-shrink:0;
}
.pta-item .pta-text {
    color:#1c2437; flex:1 1 100%; font-weight:400;
    overflow-wrap: break-word; word-wrap: break-word;
}
@media (min-width: 768px) {
    .pta-item .pta-text { flex:1 1 auto; min-width:0; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ID_CLIENTE = <?= (int) $cliente['id_cliente'] ?>;
    const ANIO = <?= (int) $anio ?>;
    const URL_MARCAR = '<?= base_url('inspecciones/matriz/no-aplica') ?>';
    const URL_MARCAR_MASIVO = '<?= base_url('inspecciones/matriz/no-aplica-masivo') ?>';
    const URL_QUITAR = '<?= base_url('inspecciones/matriz/quitar-no-aplica') ?>';
    const URL_SET_FRECUENCIA = '<?= base_url('inspecciones/matriz/set-frecuencia') ?>';
    const URL_SET_FRECUENCIA_MASIVA = '<?= base_url('inspecciones/matriz/set-frecuencia-masiva') ?>';
    const URL_CERRAR_PTA_MATRIZ = '<?= base_url('inspecciones/matriz/cerrar-pta-por-matriz') ?>';
    const URL_CERRAR_PTA_MATRIZ_MASIVO = '<?= base_url('inspecciones/matriz/cerrar-pta-por-matriz-masivo') ?>';
    const URL_PTA_LIST = '<?= base_url('inspecciones/matriz/pta-list/' . (int) $cliente['id_cliente']) ?>';
    const URL_PTA_LINK = '<?= base_url('inspecciones/matriz/vincular-pta') ?>';
    const URL_PTA_UNLINK_TIPO = '<?= base_url('inspecciones/matriz/desvincular-pta-tipo') ?>';
    const URL_PTA_CREAR = '<?= base_url('inspecciones/matriz/crear-pta') ?>';
    const URL_PTA_EDITAR_FECHA = '<?= base_url('inspecciones/matriz/editar-fecha-pta') ?>';
    const URL_PTA_REABRIR      = '<?= base_url('inspecciones/matriz/reabrir-pta') ?>';
    const URL_FILA_SLUG = '<?= base_url('inspecciones/matriz/fila-slug/' . (int) $cliente['id_cliente']) ?>';
    const URL_FILAS_TODAS = '<?= base_url('inspecciones/matriz/filas-todas/' . (int) $cliente['id_cliente']) ?>';
    const URL_LIMPIAR_FRECUENCIAS = '<?= base_url('inspecciones/matriz/limpiar-frecuencias') ?>';

    /**
     * Refresca UNA fila de la matriz (un slug) por AJAX sin recargar la página.
     * Se usa tras cada acción (Fase 2). Por ahora está definida pero no wireada.
     */
    function actualizarFila(slug) {
        const url = new URL(window.location.href);
        const fd = (url.searchParams.get('fecha_desde')) || (<?= (int) $anio ?> + '-01-01');
        const fh = (url.searchParams.get('fecha_hasta')) || (<?= (int) $anio ?> + '-12-31');
        const q = new URLSearchParams({ slug: slug, fecha_desde: fd, fecha_hasta: fh });
        return fetch(URL_FILA_SLUG + '?' + q.toString())
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.ok || !res.html) {
                    console.warn('actualizarFila: respuesta inválida', res);
                    return false;
                }
                // Buscar la <tr> actual por data-slug y reemplazarla en la DataTable
                const $old = $('#tablaMatriz tbody tr[data-slug="' + slug + '"]');
                if ($old.length === 0) {
                    console.warn('actualizarFila: fila no encontrada para slug=' + slug);
                    return false;
                }
                const $new = $(res.html.trim());
                tabla.row($old[0]).remove();
                tabla.row.add($new[0]);
                tabla.draw(false); // false = mantener pagina actual
                return true;
            })
            .catch(function (e) {
                console.warn('actualizarFila: error de red', e);
                return false;
            });
    }
    window.actualizarFila = actualizarFila; // exponer para depuración
    const URL_PTA_IA = '<?= base_url('inspecciones/matriz/generar-pta-ia') ?>';

    const estadoLabelMap = { 'realizadas': 'Realizadas', 'por_sincronizar': 'Por sincronizar', 'pendiente': 'Pendiente', 'atrasada': 'Atrasada', 'no_aplica': 'No Aplica' };
    let activeEstadoCardFilter = 'todas';
    let activeFrecuenciaFilter = '';
    let activeFechasFilter = '';

    // ── Persistencia de filtros por cliente (sobrevive a F5) ───────────────────
    // Guarda card de estado + filtros de texto Grupo/Tipo/Estado en localStorage.
    const FILTROS_KEY = 'matriz_filtros_' + ID_CLIENTE;

    // Orden por defecto del DataTable (espejo del config `order:` de abajo).
    // Se usa para resetear desde "Limpiar todos los filtros".
    const ORDER_DEFAULT = [[0, 'asc'], [2, 'asc']];

    function guardarFiltros() {
        try {
            let order = null;
            try { if (typeof tabla !== 'undefined' && tabla) order = tabla.order(); } catch (e) {}
            localStorage.setItem(FILTROS_KEY, JSON.stringify({
                card:       activeEstadoCardFilter || 'todas',
                grupo:      tabla.column(0).search(),
                tipo:       tabla.column(2).search(),
                estado:     tabla.column(4).search(),
                frecuencia: activeFrecuenciaFilter || '',
                fechas:     activeFechasFilter || '',
                order:      order,
            }));
        } catch (e) {}
    }

    function leerFiltros() {
        try {
            const o = JSON.parse(localStorage.getItem(FILTROS_KEY) || 'null');
            return (o && typeof o === 'object') ? o : null;
        } catch (e) { return null; }
    }

    function reloadMatriz() {
        const url = new URL(window.location.href);
        url.searchParams.set('_r', String(Date.now()));
        window.location.href = url.toString();
    }

    const tabla = $('#tablaMatriz').DataTable({
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todas']],
        order: [[0, 'asc'], [2, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1, 5] }
        ],
        orderCellsTop: true,
        // stateSave deshabilitado: la persistencia de filtros la maneja nuestro
        // propio localStorage (matriz_filtros_<id_cliente>). El estado interno de
        // DataTables corrompía la tabla ("Requested unknown parameter").
        initComplete: function () {
            const api = this.api();
            const grupoEl  = document.querySelector('.col-filter[data-col="0"]');
            const tipoEl   = document.querySelector('.col-filter[data-col="2"]');
            const estadoEl = document.querySelector('.col-filter[data-col="4"]');
            const f = leerFiltros();

            if (f) {
                // localStorage es la fuente de verdad de los filtros
                api.column(0).search(f.grupo || '');
                api.column(2).search(f.tipo || '');
                api.column(4).search(f.estado || '');
                activeEstadoCardFilter = f.card || 'todas';
                activeFrecuenciaFilter = f.frecuencia || '';
                activeFechasFilter     = f.fechas || '';
                if (grupoEl) grupoEl.value = f.grupo || '';
                if (tipoEl)  tipoEl.value  = f.tipo || '';
                if (estadoEl) {
                    estadoEl.value = (activeEstadoCardFilter !== 'todas')
                        ? (estadoLabelMap[activeEstadoCardFilter] || '')
                        : (f.estado || '');
                }
                const frecEl   = document.querySelector('.filtro-frecuencia');
                const fechasEl = document.querySelector('.filtro-fechas');
                if (frecEl) {
                    frecEl.value = activeFrecuenciaFilter;
                    // si el valor guardado ya no existe como opción, se descarta
                    activeFrecuenciaFilter = frecEl.value;
                }
                if (fechasEl) {
                    fechasEl.value = activeFechasFilter;
                    // si el valor guardado ya no existe como opción (ej. 'sin'/'con'
                    // viejos), .value queda '' y se descarta limpiamente
                    activeFechasFilter = fechasEl.value;
                }
            } else {
                document.querySelectorAll('.col-filter').forEach(function (el) {
                    const saved = api.column(el.dataset.col).search();
                    if (saved) el.value = saved;
                });
                activeEstadoCardFilter = 'todas';
            }

            document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('active'));
            const activeCard = document.querySelector('.card-filtro[data-filtro="' + activeEstadoCardFilter + '"]');
            if (activeCard) activeCard.classList.add('active');

            // Restaurar orden de columnas guardado (si existe y es válido)
            if (f && Array.isArray(f.order) && f.order.length) {
                try { api.order(f.order); } catch (e) {}
            }

            // Redibujar para aplicar los filtros restaurados (columnas + card custom).
            // Va DENTRO de initComplete: con language.url la init de DataTables es
            // asíncrona, y un draw() externo se ejecutaba antes de tiempo
            // ("Requested unknown parameter").
            api.draw();

            // Persistir cambios de orden a partir de aquí (no durante la inicialización)
            api.on('order.dt', function () { guardarFiltros(); });
        }
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'tablaMatriz') return true;

        const node = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
        if (!node) return true;

        // Filtro de estado (cards)
        if (activeEstadoCardFilter && activeEstadoCardFilter !== 'todas') {
            if (activeEstadoCardFilter === 'por_sincronizar') {
                if (node.getAttribute('data-por-sincronizar') !== '1') return false;
            } else if (activeEstadoCardFilter === 'realizadas') {
                const e = node.getAttribute('data-estado');
                if (e !== 'hecha' && e !== 'al_dia') return false;
            } else {
                if (node.getAttribute('data-estado') !== activeEstadoCardFilter) return false;
            }
        }

        // Filtro de frecuencia (veces al año)
        if (activeFrecuenciaFilter) {
            if ((node.getAttribute('data-frecuencia') || '') !== activeFrecuenciaFilter) return false;
        }

        // Filtro de planeadas (PTA): cantidad exacta. No Aplica nunca cuenta.
        if (activeFechasFilter !== '') {
            if (node.getAttribute('data-estado') === 'no_aplica') return false;
            if ((node.getAttribute('data-planeadas-count') || '') !== activeFechasFilter) return false;
        }

        return true;
    });

    const selectedSlugs = new Set();
    const bulkSelectVisible = document.getElementById('bulkSelectVisible');
    const bulkCount = document.getElementById('bulkSelectedCount');
    const btnBulkFrecuencia = document.getElementById('btnBulkFrecuencia');
    const btnBulkNoAplica = document.getElementById('btnBulkNoAplica');
    const btnBulkCerrarPta = document.getElementById('btnBulkCerrarPta');
    const btnBulkClear = document.getElementById('btnBulkClear');

    function syncBulkControls() {
        const count = selectedSlugs.size;
        bulkCount.innerHTML = '<i class="fas fa-check-square"></i> ' + count + ' seleccionada' + (count === 1 ? '' : 's');
        btnBulkFrecuencia.disabled = count === 0;
        btnBulkNoAplica.disabled = count === 0;
        btnBulkCerrarPta.disabled = count === 0;
        btnBulkClear.disabled = count === 0;

        document.querySelectorAll('.bulk-row-check').forEach(function (cb) {
            cb.checked = selectedSlugs.has(cb.value);
        });

        const visibleChecks = Array.from(document.querySelectorAll('.bulk-row-check'));
        const visibleSelected = visibleChecks.filter(cb => cb.checked).length;
        bulkSelectVisible.checked = visibleChecks.length > 0 && visibleSelected === visibleChecks.length;
        bulkSelectVisible.indeterminate = visibleSelected > 0 && visibleSelected < visibleChecks.length;
    }

    document.querySelector('#tablaMatriz tbody').addEventListener('change', function (ev) {
        if (!ev.target.classList.contains('bulk-row-check')) return;
        if (ev.target.checked) selectedSlugs.add(ev.target.value);
        else selectedSlugs.delete(ev.target.value);
        syncBulkControls();
    });

    bulkSelectVisible.addEventListener('change', function () {
        document.querySelectorAll('.bulk-row-check').forEach(function (cb) {
            if (bulkSelectVisible.checked) selectedSlugs.add(cb.value);
            else selectedSlugs.delete(cb.value);
            cb.checked = bulkSelectVisible.checked;
        });
        syncBulkControls();
    });

    btnBulkClear.addEventListener('click', function () {
        selectedSlugs.clear();
        syncBulkControls();
    });

    // Botón "Limpiar todos los filtros" — resetea card + Grupo/Tipo/Estado +
    // Frecuencia + Planeadas, limpia localStorage y redibuja la tabla.
    document.getElementById('btnLimpiarFiltrosMatriz').addEventListener('click', function () {
        activeEstadoCardFilter = 'todas';
        activeFrecuenciaFilter = '';
        activeFechasFilter = '';

        tabla.column(0).search('');
        tabla.column(2).search('');
        tabla.column(4).search('');

        document.querySelectorAll('.col-filter').forEach(function (el) {
            if (el.tagName === 'INPUT' || el.tagName === 'SELECT') el.value = '';
        });
        const frecEl   = document.querySelector('.filtro-frecuencia');
        const fechasEl = document.querySelector('.filtro-fechas');
        if (frecEl)   frecEl.value   = '';
        if (fechasEl) fechasEl.value = '';

        document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('active'));
        const cardTodas = document.querySelector('.card-filtro[data-filtro="todas"]');
        if (cardTodas) cardTodas.classList.add('active');

        try { localStorage.removeItem(FILTROS_KEY); } catch (e) {}

        // Reset del orden de columnas al default
        try { tabla.order(ORDER_DEFAULT); } catch (e) {}

        tabla.draw();
        guardarFiltros(); // persiste el estado limpio
    });

    // Botón "Limpiar frecuencias" — operación destructiva con doble validación aritmética
    // (espejo del patrón de borrar evaluación / Imprimir-PTA con fecha futura).
    document.getElementById('btnLimpiarFrecuencias').addEventListener('click', function () {
        const n1a = Math.floor(Math.random() * 9) + 1, n1b = Math.floor(Math.random() * 9) + 1;
        const n2a = Math.floor(Math.random() * 9) + 1, n2b = Math.floor(Math.random() * 9) + 1;
        Swal.fire({
            title: 'Limpiar frecuencias del cliente',
            html: 'Vas a borrar <b>TODAS</b> las frecuencias (veces/año) configuradas.<br>'
                + 'Esto NO toca PTAs ya creadas, solo la configuración base de la matriz.<br><br>'
                + 'Validación 1 de 2:<br><b>¿Cuánto es ' + n1a + ' + ' + n1b + '?</b>',
            input: 'number',
            inputAttributes: { min: 0 },
            showCancelButton: true,
            confirmButtonText: 'Siguiente',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            preConfirm: function (v) {
                if (parseInt(v, 10) !== (n1a + n1b)) {
                    Swal.showValidationMessage('Resultado incorrecto.');
                    return false;
                }
                return true;
            }
        }).then(function (r1) {
            if (!r1.isConfirmed) return;
            Swal.fire({
                title: 'Confirma de nuevo',
                html: 'Validación 2 de 2:<br><b>¿Cuánto es ' + n2a + ' + ' + n2b + '?</b>',
                input: 'number',
                inputAttributes: { min: 0 },
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar definitivamente',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                preConfirm: function (v) {
                    if (parseInt(v, 10) !== (n2a + n2b)) {
                        Swal.showValidationMessage('Resultado incorrecto.');
                        return false;
                    }
                    return true;
                }
            }).then(function (r2) {
                if (!r2.isConfirmed) return;
                const fd = new FormData();
                fd.append('id_cliente', ID_CLIENTE);
                fetch(URL_LIMPIAR_FRECUENCIAS, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Frecuencias borradas',
                                text: res.frecuencias_borradas + ' frecuencia(s) eliminadas. Recargando matriz...',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            document.getElementById('btnRefrescarTodoMatriz').click();
                        } else {
                            Swal.fire('Error', res.msg || 'No se pudo limpiar.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
            });
        });
    });

    // Botón "Refrescar todo" — pide al servidor todas las filas pre-renderizadas con
    // cache invalidado y reemplaza el contenido de la DataTable manteniendo filtros y página.
    document.getElementById('btnRefrescarTodoMatriz').addEventListener('click', function () {
        const btn = this;
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refrescando...';

        const url = new URL(window.location.href);
        const fd = (url.searchParams.get('fecha_desde')) || (<?= (int) $anio ?> + '-01-01');
        const fh = (url.searchParams.get('fecha_hasta')) || (<?= (int) $anio ?> + '-12-31');
        const q = new URLSearchParams({ fecha_desde: fd, fecha_hasta: fh });

        fetch(URL_FILAS_TODAS + '?' + q.toString())
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.ok || !Array.isArray(res.filas)) {
                    Swal.fire('Error', (res && res.msg) || 'No se pudieron cargar las filas.', 'error');
                    return;
                }
                // Reemplazo masivo: quitar TODAS las filas y agregar las nuevas en bloque
                tabla.clear();
                res.filas.forEach(function (item) {
                    if (!item || !item.html) return;
                    const $new = $(item.html.trim());
                    if ($new && $new.length) tabla.row.add($new[0]);
                });
                tabla.draw(false); // dispara recalcEstadoCards y mantiene la página actual

                // Repoblar dropdowns dinámicos del <thead> (frecuencia + planeadas)
                // con las opciones derivadas de las filas frescas. Preserva la selección
                // actual si todavía existe; si no, queda en "todas" y re-aplica el filtro.
                if (Array.isArray(res.frecuencias_opciones)) {
                    const frecEl = document.querySelector('.filtro-frecuencia');
                    if (frecEl) {
                        const prev = frecEl.value;
                        let html = '<option value="">Frecuencia: todas</option>';
                        res.frecuencias_opciones.forEach(function (fk) {
                            let lbl;
                            if (fk === 'sin_definir')      lbl = 'Sin definir';
                            else if (fk === '0' || fk === 0) lbl = 'Puntual';
                            else if (fk === '1' || fk === 1) lbl = '1 vez al año';
                            else                            lbl = fk + ' veces al año';
                            html += '<option value="' + fk + '">' + lbl + '</option>';
                        });
                        frecEl.innerHTML = html;
                        // Restaurar valor si aún existe; si no, limpiar el filtro custom
                        const stillValid = res.frecuencias_opciones.map(String).indexOf(String(prev)) !== -1;
                        if (prev && stillValid) {
                            frecEl.value = prev;
                        } else if (prev) {
                            activeFrecuenciaFilter = '';
                            tabla.draw(false);
                            guardarFiltros();
                        }
                    }
                }
                if (res.planeadas_counts && typeof res.planeadas_counts === 'object') {
                    const fechasEl = document.querySelector('.filtro-fechas');
                    if (fechasEl) {
                        const prev = fechasEl.value;
                        let html = '<option value="">Planeadas: todas</option>';
                        Object.keys(res.planeadas_counts).map(Number).sort((a, b) => a - b).forEach(function (cant) {
                            const n = res.planeadas_counts[cant];
                            html += '<option value="' + cant + '">' + cant + ' planeada' + (cant == 1 ? '' : 's') + ' (' + n + ')</option>';
                        });
                        fechasEl.innerHTML = html;
                        const stillValidF = Object.prototype.hasOwnProperty.call(res.planeadas_counts, prev);
                        if (prev && stillValidF) {
                            fechasEl.value = prev;
                        } else if (prev) {
                            activeFechasFilter = '';
                            tabla.draw(false);
                            guardarFiltros();
                        }
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Matriz actualizada',
                    text: res.total + ' fila(s) recargadas con datos frescos.',
                    timer: 1500,
                    showConfirmButton: false
                });
            })
            .catch(function () {
                Swal.fire('Error', 'Error de red al refrescar la matriz.', 'error');
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = original;
            });
    });

    function appendSelectedSlugs(fd) {
        Array.from(selectedSlugs).forEach(slug => fd.append('slugs[]', slug));
    }

    btnBulkFrecuencia.addEventListener('click', function () {
        Swal.fire({
            title: 'Definir frecuencia masiva',
            html: '<div style="text-align:left; font-size:13px;">Se aplicará a <strong>' + selectedSlugs.size + '</strong> tipo(s) de inspección seleccionados.</div>',
            input: 'number',
            inputPlaceholder: 'Ej: 1, 4, 12',
            inputAttributes: { min: 0, max: 365, step: 1 },
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Aplicar frecuencia',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
            preConfirm: value => {
                const n = parseInt(value, 10);
                if (value === '' || isNaN(n) || n < 0 || n > 365) {
                    Swal.showValidationMessage('Escribe un número entre 0 y 365.');
                    return false;
                }
                return n;
            }
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            fd.append('veces_anio', String(r.value));
            appendSelectedSlugs(fd);
            const slugsToRefresh = Array.from(selectedSlugs);
            fetch(URL_SET_FRECUENCIA_MASIVA, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) {
                        Promise.all(slugsToRefresh.map(s => actualizarFila(s))).then(() => {
                            selectedSlugs.clear();
                            syncBulkControls();
                        });
                        Swal.fire({ icon: 'success', title: 'Frecuencia aplicada', text: res.updated + ' de ' + res.total + ' actualizadas.', timer: 1600, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', res.msg || 'No se pudo aplicar.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
        });
    });

    btnBulkNoAplica.addEventListener('click', function () {
        Swal.fire({
            title: 'Marcar No Aplica masivo',
            html: '<div style="text-align:left; font-size:13px;">Se marcarán <strong>' + selectedSlugs.size + '</strong> tipo(s) de inspección seleccionados.</div>',
            input: 'text',
            inputPlaceholder: 'Motivo común (opcional)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Marcar No Aplica',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#6c757d'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            fd.append('motivo', r.value || '');
            appendSelectedSlugs(fd);
            const slugsToRefreshNa = Array.from(selectedSlugs);
            fetch(URL_MARCAR_MASIVO, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) {
                        Promise.all(slugsToRefreshNa.map(s => actualizarFila(s))).then(() => {
                            selectedSlugs.clear();
                            syncBulkControls();
                        });
                        Swal.fire({ icon: 'success', title: 'No Aplica aplicado', text: res.updated + ' de ' + res.total + ' actualizadas.', timer: 1600, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', res.msg || 'No se pudo marcar.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
        });
    });

    btnBulkCerrarPta.addEventListener('click', function () {
        Swal.fire({
            title: 'Imprimir en PTA masivo',
            html: '<div style="text-align:left; font-size:13px;">Se procesarán <strong>' + selectedSlugs.size + '</strong> tipo(s) seleccionados.<br><br>Por cada tipo se cerrará solo la PTA abierta más antigua por fecha propuesta ascendente. Si no hay PTA vinculada, se crearán PTAs cerradas retroactivas con las inspecciones reales.</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, imprimir en PTA',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0ea5d7'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            appendSelectedSlugs(fd);
            const slugsToRefreshCerrar = Array.from(selectedSlugs);
            fetch(URL_CERRAR_PTA_MATRIZ_MASIVO, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) {
                        Promise.all(slugsToRefreshCerrar.map(s => actualizarFila(s))).then(() => {
                            selectedSlugs.clear();
                            syncBulkControls();
                        });
                        const msg = res.procesadas + ' procesada(s). ' +
                            res.cerradas + ' PTA(s) cerrada(s). ' +
                            res.creadas + ' PTA(s) creada(s).' +
                            (res.omitidas > 0 ? ' ' + res.omitidas + ' omitida(s).' : '');
                        Swal.fire({ icon: 'success', title: 'Impreso en PTA', text: msg, timer: 2200, showConfirmButton: false });
                    } else {
                        const detalle = (res.errores || []).slice(0, 3).map(e => (e.label || e.slug) + ': ' + e.msg).join('\n');
                        Swal.fire('Sin cambios', detalle || res.msg || 'No se pudo imprimir en PTA.', 'info');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
        });
    });

    tabla.on('draw', syncBulkControls);
    syncBulkControls();

    // Recalcular cards Año/Mes según filas visibles (interconexión con cards de estado).
    // Cada fila lleva data-fechas (CSV YYYY-MM-DD) con todas sus fechas relevantes (realizadas + programadas).
    function recalcMatrizCards() {
        const monthly = new Array(13).fill(0);
        let yearActiveCount = 0;
        tabla.rows({ search: 'applied' }).every(function () {
            const node = this.node();
            if (!node) return;
            const raw = node.getAttribute('data-fechas') || '';
            if (!raw) return;
            raw.split(',').forEach(function (d) {
                if (d.length < 10) return;
                const y = parseInt(d.substring(0, 4), 10);
                const m = parseInt(d.substring(5, 7), 10);
                if (y === ANIO) {
                    yearActiveCount++;
                    if (m >= 1 && m <= 12) monthly[m]++;
                }
            });
        });
        document.querySelectorAll('.card-mes').forEach(function (card) {
            const m = parseInt(card.getAttribute('data-mes'), 10);
            const el = card.querySelector('.matriz-mes-count');
            if (el && m >= 1 && m <= 12) el.textContent = monthly[m];
        });
        const cardAnio = document.querySelector('.card-anio[data-anio="' + ANIO + '"]');
        if (cardAnio) {
            const elA = cardAnio.querySelector('.matriz-anio-count');
            if (elA) elA.textContent = yearActiveCount;
        }
    }
    tabla.on('draw', recalcMatrizCards);
    recalcMatrizCards();

    // ── Recalcular cards de Estado + Cobertura desde las <tr> del tbody ─────
    // Cuenta TODAS las filas que DataTables conoce (incluyendo otras páginas y
    // filtradas, que no están en el DOM con paginación). Por eso usamos
    // tabla.rows().nodes() en vez de querySelectorAll.
    function recalcEstadoCards() {
        if (typeof tabla === 'undefined' || !tabla) return;
        const rows = tabla.rows().nodes().toArray();
        let total = 0, hecha = 0, alDia = 0, pendiente = 0, atrasada = 0, noAplica = 0;
        let porSinc = 0, sinMeta = 0, faltantes = 0;

        rows.forEach(function (tr) {
            if (!tr || !tr.getAttribute) return;
            const e = tr.getAttribute('data-estado');
            if (!e) return;
            total++;
            if (e === 'hecha') hecha++;
            else if (e === 'al_dia') alDia++;
            else if (e === 'pendiente') pendiente++;
            else if (e === 'atrasada') atrasada++;
            else if (e === 'no_aplica') noAplica++;

            if (tr.getAttribute('data-por-sincronizar') === '1') porSinc++;

            if (e !== 'no_aplica') {
                const fre = tr.getAttribute('data-frecuencia');
                if (fre === 'sin_definir') {
                    sinMeta++;
                } else {
                    const va = parseInt(fre, 10) || 0;
                    const ra = parseInt(tr.getAttribute('data-realizadas-anio') || '0', 10);
                    if (va > 0 && ra < va) faltantes += (va - ra);
                }
            }
        });

        const realizadas = hecha + alDia;
        const aplicables = total - noAplica;
        const cobertura = aplicables > 0 ? Math.round((realizadas / aplicables) * 100) : 0;

        const setCount = function (filtro, n) {
            const el = document.querySelector('.card-filtro[data-filtro="' + filtro + '"] .card-count-num');
            if (el) el.textContent = n;
        };
        setCount('todas', total);
        setCount('realizadas', realizadas);
        setCount('por_sincronizar', porSinc);
        setCount('pendiente', pendiente);
        setCount('atrasada', atrasada);
        setCount('no_aplica', noAplica);

        // Sub-texto del card "Realizadas"
        const sub = document.getElementById('cardRealizadasSubtext');
        if (sub) {
            if (faltantes > 0) {
                sub.innerHTML = '<div style="font-size:9px; color:#0f5132; font-weight:600; line-height:1.15;">Faltan ' + faltantes + ' para estar al día</div>';
            } else if (sinMeta > 0) {
                sub.innerHTML = '<div style="font-size:9px; color:#856404; font-weight:600; line-height:1.15;">' + sinMeta + ' sin meta definida</div>';
            } else if (realizadas > 0) {
                sub.innerHTML = '<div style="font-size:9px; color:#0f5132; font-weight:600; line-height:1.15;"><i class="fas fa-shield-alt"></i> Todas al día</div>';
            } else {
                sub.innerHTML = '';
            }
        }

        // Cobertura
        const cobEl = document.getElementById('coberturaPct');
        if (cobEl) cobEl.textContent = cobertura + '%';
    }
    tabla.on('draw', recalcEstadoCards);
    recalcEstadoCards();

    document.querySelectorAll('.col-filter').forEach(function (el) {
        el.addEventListener('input', function () {
            if (this.dataset.col === '4') {
                activeEstadoCardFilter = 'todas';
                document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('active'));
                const todas = document.querySelector('.card-filtro[data-filtro="todas"]');
                if (todas) todas.classList.add('active');
            }
            tabla.column(this.dataset.col).search(this.value).draw();
            guardarFiltros();
        });
        el.addEventListener('change', function () {
            if (this.dataset.col === '4') {
                activeEstadoCardFilter = 'todas';
                document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('active'));
                const todas = document.querySelector('.card-filtro[data-filtro="todas"]');
                if (todas) todas.classList.add('active');
            }
            tabla.column(this.dataset.col).search(this.value).draw();
            guardarFiltros();
        });
    });

    document.querySelectorAll('.card-filtro').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const filtro = this.dataset.filtro;
            const selectEstado = document.querySelector('.col-filter[data-col="4"]');
            activeEstadoCardFilter = filtro;

            if (filtro === 'todas') {
                selectEstado.value = '';
                tabla.column(4).search('').draw();
            } else {
                const label = estadoLabelMap[filtro] || '';
                selectEstado.value = label;
                tabla.column(4).search('').draw();
            }
            guardarFiltros();
        });
    });

    // Dropdowns de Frecuencia y Fechas (filtros custom, persistentes)
    document.querySelectorAll('.filtro-frecuencia').forEach(function (el) {
        el.addEventListener('change', function () {
            activeFrecuenciaFilter = this.value;
            tabla.draw();
            guardarFiltros();
        });
    });
    document.querySelectorAll('.filtro-fechas').forEach(function (el) {
        el.addEventListener('change', function () {
            activeFechasFilter = this.value;
            tabla.draw();
            guardarFiltros();
        });
    });

    // Delegado para sobrevivir reemplazos de <tr> por actualizarFila()
    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.btn-marcar-na');
        if (!btn) return;
        const slug = btn.dataset.slug;
        const label = btn.dataset.label;

        Swal.fire({
            title: 'Marcar "' + label + '" como No Aplica',
            input: 'text',
            inputPlaceholder: 'Motivo (opcional), ej: El edificio no tiene ascensor',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Marcar N/A',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#6c757d'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            fd.append('tipo_inspeccion', slug);
            fd.append('motivo', r.value || '');
            fetch(URL_MARCAR, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) actualizarFila(slug);
                    else Swal.fire('Error', res.msg || 'No se pudo marcar.', 'error');
                })
                .catch(() => Swal.fire('Error', 'No se pudo marcar.', 'error'));
        });
    });

    // ================ VINCULAR PTA ================
    let modalSlug = null;
    let modalLabel = null;
    let modalPtas = [];
    let modalVinculados = new Set();
    const modalEl = document.getElementById('modalVincularPta');
    const modalBs = new bootstrap.Modal(modalEl);

    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    }
    function fmtDate(s) {
        if (!s) return '—';
        const d = new Date(s + 'T00:00:00');
        if (isNaN(d)) return s;
        return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
    }

    function renderPtaList(filter) {
        const container = document.getElementById('modalPtaList');
        const term = (filter || '').toLowerCase().trim();
        const filtered = term
            ? modalPtas.filter(p => (p.numeral_plandetrabajo || '').toLowerCase().includes(term)
                || (p.actividad_plandetrabajo || '').toLowerCase().includes(term)
                || (p.phva_plandetrabajo || '').toLowerCase().includes(term))
            : modalPtas;

        if (filtered.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-3">Sin resultados.</div>';
            updateCountSelected();
            return;
        }

        let html = '';
        filtered.forEach(p => {
            const checked = modalVinculados.has(p.id_ptacliente);
            const closed = p.estado_actividad === 'CERRADA';
            html += `<label class="pta-row ${closed ? 'pta-closed' : ''} ${checked ? 'pta-checked' : ''}">
                <input type="checkbox" value="${p.id_ptacliente}" ${checked ? 'checked' : ''}>
                <div style="flex:1; min-width:0;">
                    <div class="pta-activity">
                        <strong>${escHtml(p.numeral_plandetrabajo)}</strong>
                        <span class="badge" style="background:#eef2f7; color:#555; font-size:9px; padding:2px 5px;">${escHtml(p.phva_plandetrabajo)}</span>
                        <span class="badge" style="background:${closed ? '#d4edda' : (p.estado_actividad === 'GESTIONANDO' ? '#fff3cd' : '#cfe2ff')}; color:#000; font-size:9px; padding:2px 5px;">${escHtml(p.estado_actividad)}</span>
                        ${escHtml((p.actividad_plandetrabajo || '').substring(0, 180))}${(p.actividad_plandetrabajo || '').length > 180 ? '…' : ''}
                    </div>
                    <div class="pta-meta">
                        Propuesta: ${fmtDate(p.fecha_propuesta)}${p.fecha_cierre ? ' · Cierre: ' + fmtDate(p.fecha_cierre) : ''}
                    </div>
                </div>
            </label>`;
        });
        container.innerHTML = html;
        container.querySelectorAll('input[type=checkbox]').forEach(cb => {
            cb.addEventListener('change', function () {
                const id = parseInt(this.value, 10);
                if (this.checked) modalVinculados.add(id); else modalVinculados.delete(id);
                this.closest('.pta-row').classList.toggle('pta-checked', this.checked);
                updateCountSelected();
            });
        });
        updateCountSelected();
    }

    function updateCountSelected() {
        document.getElementById('modalCountSelected').textContent =
            modalVinculados.size + ' seleccionada(s)';
    }

    // Editar fecha propuesta de una PTA con un clic (icono lápiz en .pta-item)
    document.addEventListener('click', function (ev) {
        const icon = ev.target.closest('.pta-edit-fecha');
        if (!icon) return;
        ev.preventDefault();
        ev.stopPropagation();
        const idPta = icon.getAttribute('data-id-pta');
        const fechaActual = icon.getAttribute('data-fecha') || '';
        const tr = icon.closest('tr[data-slug]');
        const slug = tr ? tr.getAttribute('data-slug') : null;
        Swal.fire({
            title: 'Editar fecha propuesta',
            input: 'date',
            inputValue: fechaActual,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-1"></i> Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
            preConfirm: function (val) {
                if (!val) { Swal.showValidationMessage('Selecciona una fecha.'); return false; }
                return val;
            }
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            fd.append('id_ptacliente', idPta);
            fd.append('fecha_propuesta', r.value);
            fetch(URL_PTA_EDITAR_FECHA, { method: 'POST', body: fd })
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (res.ok) {
                        if (slug) actualizarFila(slug);
                        Swal.fire({ icon: 'success', title: 'Fecha actualizada', timer: 1200, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', res.msg || 'No se pudo actualizar.', 'error');
                    }
                })
                .catch(function () { Swal.fire('Error', 'Error de red.', 'error'); });
        });
    });

    // Reabrir una PTA cerrada (icono borrador) → estado ABIERTA, fecha_cierre NULL, avance 0
    document.addEventListener('click', function (ev) {
        const icon = ev.target.closest('.pta-reabrir');
        if (!icon) return;
        ev.preventDefault();
        ev.stopPropagation();
        const idPta = icon.getAttribute('data-id-pta');
        const tr    = icon.closest('tr[data-slug]');
        const slug  = tr ? tr.getAttribute('data-slug') : null;
        Swal.fire({
            title: 'Reabrir esta actividad PTA',
            html: 'Estado <b>CERRADA</b> → <b>ABIERTA</b>.<br>Se borra la fecha de cierre y % avance vuelve a 0.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-eraser me-1"></i> Sí, reabrir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#856404'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            fd.append('id_ptacliente', idPta);
            fetch(URL_PTA_REABRIR, { method: 'POST', body: fd })
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (res.ok) {
                        if (slug) actualizarFila(slug);
                        Swal.fire({ icon: 'success', title: 'Reabierta', timer: 1200, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', res.msg || 'No se pudo reabrir.', 'error');
                    }
                })
                .catch(function () { Swal.fire('Error', 'Error de red.', 'error'); });
        });
    });

    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.btn-vincular-pta');
        if (!btn) return;
        modalSlug = btn.dataset.slug;
        modalLabel = btn.dataset.label;
        document.getElementById('modalSlugLabel').textContent = modalLabel;
        document.getElementById('modalBuscador').value = '';
        document.getElementById('modalToggleCerradas').checked = false;
        document.getElementById('modalPtaList').innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
        modalPtas = [];
        modalVinculados = new Set();
        modalBs.show();

        cargarPtas(false);
    });

    function cargarPtas(incluirCerradas) {
        const params = new URLSearchParams({ slug: modalSlug, anio: ANIO });
        if (incluirCerradas) params.append('cerradas', '1');
        fetch(URL_PTA_LIST + '?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (!data.ok) {
                    document.getElementById('modalPtaList').innerHTML =
                        '<div class="text-center text-danger py-3">' + (data.msg || 'Error al cargar.') + '</div>';
                    return;
                }
                modalPtas = (data.ptas || []).map(p => ({ ...p, id_ptacliente: parseInt(p.id_ptacliente, 10) }));
                modalVinculados = new Set((data.vinculados || []).map(i => parseInt(i, 10)));
                renderPtaList('');
            })
            .catch(() => {
                document.getElementById('modalPtaList').innerHTML =
                    '<div class="text-center text-danger py-3">Error de red.</div>';
            });
    }

    document.getElementById('modalBuscador').addEventListener('input', function () {
        renderPtaList(this.value);
    });

    document.getElementById('modalToggleCerradas').addEventListener('change', function () {
        cargarPtas(this.checked);
    });

    document.getElementById('modalBtnGuardar').addEventListener('click', function () {
        const fd = new FormData();
        fd.append('id_cliente', ID_CLIENTE);
        fd.append('slug_inspeccion', modalSlug);
        fd.append('anio', ANIO);
        Array.from(modalVinculados).forEach(id => fd.append('ids_ptacliente[]', id));

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        fetch(URL_PTA_LINK, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    modalBs.hide();
                    if (modalSlug) actualizarFila(modalSlug);
                    Swal.fire({
                        icon: 'success',
                        title: 'Vínculos guardados',
                        text: (res.added > 0 ? '+' + res.added + ' nuevo(s). ' : '') + (res.removed > 0 ? '-' + res.removed + ' quitado(s).' : ''),
                        timer: 1600,
                        showConfirmButton: false
                    });
                    document.getElementById('modalBtnGuardar').disabled = false;
                    document.getElementById('modalBtnGuardar').innerHTML = '<i class="fas fa-save"></i> Guardar vínculos';
                } else {
                    Swal.fire('Error', res.msg || 'No se pudo guardar.', 'error');
                    document.getElementById('modalBtnGuardar').disabled = false;
                    document.getElementById('modalBtnGuardar').innerHTML = '<i class="fas fa-save"></i> Guardar vínculos';
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Error de red.', 'error');
                document.getElementById('modalBtnGuardar').disabled = false;
                document.getElementById('modalBtnGuardar').innerHTML = '<i class="fas fa-save"></i> Guardar vínculos';
            });
    });
    // ================ FIN VINCULAR PTA ================

    // ================ DESVINCULAR PTA POR TIPO ================
    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.btn-desvincular-pta');
        if (!btn) return;
        const slug = btn.dataset.slug;
        const label = btn.dataset.label;
        const count = parseInt(btn.dataset.count, 10) || 0;

        Swal.fire({
            title: 'Desvincular del Plan de Trabajo',
            html: '<div style="text-align:left; font-size:13px;">' +
                  '<strong>Tipo:</strong> ' + label + '<br>' +
                  '<strong>Vínculos visibles:</strong> ' + count + '<br><br>' +
                  'Se quitará el vínculo de este tipo de inspección con las actividades PTA del año ' + ANIO + '. No se eliminarán actividades del plan ni inspecciones.' +
                  '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desvincular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            fd.append('slug_inspeccion', slug);
            fd.append('anio', ANIO);
            fetch(URL_PTA_UNLINK_TIPO, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) {
                        actualizarFila(slug);
                        Swal.fire({
                            icon: 'success',
                            title: 'Desvinculado',
                            text: res.removed + ' vínculo(s) quitado(s).',
                            timer: 1600,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Sin cambios', res.msg || 'No se pudo desvincular.', 'info');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
        });
    });
    // ================ FIN DESVINCULAR PTA POR TIPO ================

    // ================ CREAR PTA ================
    document.addEventListener('click', function (ev) {
        const btnCrearPta = ev.target.closest('.btn-crear-pta');
        if (!btnCrearPta) return;
        const slug = btnCrearPta.dataset.slug;
        const label = btnCrearPta.dataset.label;
        const today = new Date().toISOString().slice(0, 10);

            Swal.fire({
                title: 'Crear actividad en el Plan de Trabajo',
                width: 620,
                html: `
                    <div style="text-align:left; font-size:13px;">
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold m-0">Actividad</label>
                                <button type="button" id="ptaBtnIA" class="btn btn-sm"
                                    style="background:linear-gradient(135deg,#7B2D3B,#bd9751);color:#fff;padding:2px 10px;font-size:11px;border:none;border-radius:12px;">
                                    <i class="fas fa-wand-magic-sparkles"></i> IA: autocompletar
                                </button>
                            </div>
                            <textarea id="ptaAct" class="form-control form-control-sm" rows="2" style="font-size:12px;">Inspección de ${label}</textarea>
                            <div id="ptaIAStatus" class="small text-muted mt-1" style="font-size:10px; min-height:14px;"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Fecha propuesta</label>
                                <input id="ptaFecha" type="date" class="form-control form-control-sm" value="${today}" style="font-size:12px;">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">PHVA</label>
                                <select id="ptaPhva" class="form-select form-select-sm" style="font-size:12px;">
                                    <option value="PLANEAR">PLANEAR</option>
                                    <option value="HACER" selected>HACER</option>
                                    <option value="VERIFICAR">VERIFICAR</option>
                                    <option value="ACTUAR">ACTUAR</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label small fw-bold mb-1">Numeral <span class="text-muted">(D. 1072)</span></label>
                            <input id="ptaNumeral" type="text" class="form-control form-control-sm" placeholder="Ej: 1.2.3" style="font-size:12px;">
                        </div>
                        <div class="mt-2">
                            <label class="form-label small fw-bold mb-1">Responsable sugerido</label>
                            <input id="ptaResp" type="text" class="form-control form-control-sm" value="CONSULTOR CYCLOID" style="font-size:12px;">
                        </div>
                        <div class="mt-2">
                            <label class="form-label small fw-bold mb-1">Observaciones <span class="text-muted">(opcional)</span></label>
                            <textarea id="ptaObs" class="form-control form-control-sm" rows="1" style="font-size:12px;"></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Crear y vincular',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
                focusConfirm: false,
                didOpen: () => {
                    const btnIA = document.getElementById('ptaBtnIA');
                    btnIA.addEventListener('click', function () {
                        const act = document.getElementById('ptaAct').value.trim();
                        if (!act) {
                            document.getElementById('ptaIAStatus').innerHTML =
                                '<span style="color:#dc3545;"><i class="fas fa-exclamation-circle"></i> Escribe la actividad primero.</span>';
                            return;
                        }
                        btnIA.disabled = true;
                        const orig = btnIA.innerHTML;
                        btnIA.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
                        document.getElementById('ptaIAStatus').innerHTML = '';

                        const fd = new FormData();
                        fd.append('actividad', act);
                        fd.append('slug_inspeccion', slug);

                        fetch(URL_PTA_IA, { method: 'POST', body: fd })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.ok) {
                                    document.getElementById('ptaIAStatus').innerHTML =
                                        '<span style="color:#dc3545;"><i class="fas fa-exclamation-circle"></i> ' + (res.msg || 'Falló la IA.') + '</span>';
                                    return;
                                }
                                if (res.numeral) document.getElementById('ptaNumeral').value = res.numeral;
                                if (res.phva) document.getElementById('ptaPhva').value = res.phva;
                                document.getElementById('ptaResp').value = 'CONSULTOR CYCLOID';
                                document.getElementById('ptaObs').value = '';
                                document.getElementById('ptaIAStatus').innerHTML =
                                    '<span style="color:#155724;"><i class="fas fa-check-circle"></i> Numeral y PHVA autocompletados por IA. Responsable y observaciones quedan por defecto.</span>';
                            })
                            .catch(() => {
                                document.getElementById('ptaIAStatus').innerHTML =
                                    '<span style="color:#dc3545;"><i class="fas fa-exclamation-circle"></i> Error de red.</span>';
                            })
                            .finally(() => {
                                btnIA.disabled = false;
                                btnIA.innerHTML = orig;
                            });
                    });
                },
                preConfirm: () => {
                    const act = document.getElementById('ptaAct').value.trim();
                    const fecha = document.getElementById('ptaFecha').value;
                    const phva = document.getElementById('ptaPhva').value;
                    const numeral = document.getElementById('ptaNumeral').value.trim();
                    const resp = document.getElementById('ptaResp').value.trim();
                    const obs = document.getElementById('ptaObs').value.trim();
                    if (!act) { Swal.showValidationMessage('La actividad es obligatoria.'); return false; }
                    if (!fecha) { Swal.showValidationMessage('La fecha es obligatoria.'); return false; }
                    return { act, fecha, phva, numeral, resp, obs };
                }
            }).then(function (r) {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id_cliente', ID_CLIENTE);
                fd.append('slug_inspeccion', slug);
                fd.append('actividad', r.value.act);
                fd.append('fecha_propuesta', r.value.fecha);
                fd.append('phva', r.value.phva);
                fd.append('numeral', r.value.numeral);
                fd.append('responsable_sugerido', r.value.resp);
                fd.append('observaciones', r.value.obs);
                fetch(URL_PTA_CREAR, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.ok) {
                            actualizarFila(slug);
                            Swal.fire({
                                icon: 'success',
                                title: 'Actividad creada y vinculada',
                                text: 'id_ptacliente=' + res.id_ptacliente,
                                timer: 1800, showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.msg || 'No se pudo crear.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
            });
    });
    // ================ FIN CREAR PTA ================

    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.btn-quitar-na');
        if (!btn) return;
        const slug = btn.dataset.slug;
        const label = btn.dataset.label;

        Swal.fire({
            title: '¿Quitar marca No Aplica?',
            text: '"' + label + '" volverá al listado como pendiente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ffc107'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('id_cliente', ID_CLIENTE);
            fd.append('tipo_inspeccion', slug);
            fetch(URL_QUITAR, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) actualizarFila(slug);
                    else Swal.fire('Error', res.msg || 'No se pudo quitar.', 'error');
                })
                .catch(() => Swal.fire('Error', 'No se pudo quitar.', 'error'));
        });
    });

    // ================ IMPRIMIR EN PTA DESDE MATRIZ ================
    function ejecutarImprimirPta(slug) {
        const fd = new FormData();
        fd.append('id_cliente', ID_CLIENTE);
        fd.append('slug_inspeccion', slug);
        return fetch(URL_CERRAR_PTA_MATRIZ, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if (res.ok) {
                    actualizarFila(slug);
                    const msg = res.cerradas > 0
                        ? res.cerradas + ' PTA(s) cerrada(s) con éxito.'
                        : res.creadas + ' PTA(s) CERRADA(s) creada(s) retroactivamente.';
                    Swal.fire({
                        icon: 'success',
                        title: 'Impreso en PTA',
                        text: msg,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', res.msg || 'No se pudo cerrar.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
    }

    function pedirDobleValidacionImprimirPta(slug, label, fechaMinFutura) {
        const n1a = Math.floor(Math.random() * 9) + 1, n1b = Math.floor(Math.random() * 9) + 1;
        const n2a = Math.floor(Math.random() * 9) + 1, n2b = Math.floor(Math.random() * 9) + 1;
        const fechaTxt = fechaMinFutura.split('-').reverse().join('/');
        Swal.fire({
            title: 'Atención: fecha futura',
            html: 'La PTA más próxima de <b>' + label + '</b> está programada para <b>' + fechaTxt + '</b> (aún no llega).<br><br>'
                + 'Si continúas, se cerrará con la fecha de la inspección más reciente.<br><br>'
                + 'Validación 1 de 2:<br><b>¿Cuánto es ' + n1a + ' + ' + n1b + '?</b>',
            input: 'number',
            inputAttributes: { min: 0 },
            showCancelButton: true,
            confirmButtonText: 'Siguiente',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            preConfirm: function (v) {
                if (parseInt(v, 10) !== (n1a + n1b)) {
                    Swal.showValidationMessage('Resultado incorrecto.');
                    return false;
                }
                return true;
            }
        }).then(function (r1) {
            if (!r1.isConfirmed) return;
            Swal.fire({
                title: 'Confirma de nuevo',
                html: 'Validación 2 de 2:<br><b>¿Cuánto es ' + n2a + ' + ' + n2b + '?</b>',
                input: 'number',
                inputAttributes: { min: 0 },
                showCancelButton: true,
                confirmButtonText: 'Sí, imprimir en PTA',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                preConfirm: function (v) {
                    if (parseInt(v, 10) !== (n2a + n2b)) {
                        Swal.showValidationMessage('Resultado incorrecto.');
                        return false;
                    }
                    return true;
                }
            }).then(function (r2) {
                if (!r2.isConfirmed) return;
                ejecutarImprimirPta(slug);
            });
        });
    }

    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.btn-cerrar-pta-matriz');
        if (!btn) return;
        const slug      = btn.dataset.slug;
        const label     = btn.dataset.label;
        const real      = parseInt(btn.dataset.realizadas, 10) || 0;
        const abiertas  = parseInt(btn.dataset.abiertas, 10) || 0;

        // Detectar la fecha programada más próxima ABIERTA (mínima) y compararla con hoy.
        // Si es estrictamente futura, exigimos doble validación aritmética para evitar
        // cierres accidentales de actividades aún no vencidas.
        // Importante: solo miramos PTAs aún abiertas — las ya cerradas no se vuelven a
        // cerrar, así que su fecha no debe influir en la decisión.
        const tr = btn.closest('tr[data-slug]');
        const fechasProgRaw = tr ? (tr.getAttribute('data-fechas-programadas-abiertas') || '') : '';
        const fechasProg = fechasProgRaw.split(',').map(s => s.trim()).filter(Boolean).sort();
        const minFecha = fechasProg.length ? fechasProg[0] : null;
        const hoyISO = (function () {
            const d = new Date();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return d.getFullYear() + '-' + m + '-' + dd;
        })();
        const esFutura = !!(minFecha && minFecha > hoyISO);

        if (esFutura) {
            pedirDobleValidacionImprimirPta(slug, label, minFecha);
            return;
        }

        const accion = abiertas > 0
            ? 'Cerrar SOLO la PTA más antigua abierta (1 de ' + abiertas + ') con la fecha de la inspección más reciente. Si quedan PTAs por cerrar, vuelve a usar este botón.'
            : 'No hay PTAs vinculadas. Se crearán ' + real + ' PTA(s) CERRADA(s) retroactivamente con la fecha de cada inspección.';

        Swal.fire({
            title: 'Imprimir en PTA',
            html: '<div style="text-align:left; font-size:13px;">' +
                  '<strong>Tipo:</strong> ' + label + '<br>' +
                  '<strong>Inspecciones realizadas:</strong> ' + real + '<br>' +
                  '<strong>PTAs abiertas vinculadas:</strong> ' + abiertas + '<br><br>' +
                  '<strong>Acción:</strong> ' + accion +
                  '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, imprimir en PTA',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0ea5d7'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            ejecutarImprimirPta(slug);
        });
    });
    // ================ FIN IMPRIMIR EN PTA DESDE MATRIZ ================

    // ================ FRECUENCIA ================
    const frecModalEl  = document.getElementById('modalFrecuencia');
    const frecModalBs  = new bootstrap.Modal(frecModalEl);
    let frecModalSlug  = null;
    let frecModalLabel = null;

    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.btn-frecuencia');
        if (!btn) return;
        frecModalSlug  = btn.dataset.slug;
        frecModalLabel = btn.dataset.label;
        document.getElementById('frecModalLabel').textContent = frecModalLabel;
        document.getElementById('frecModalInput').value = btn.dataset.vecesAnio || '';
        frecModalBs.show();
        setTimeout(() => document.getElementById('frecModalInput').focus(), 200);
    });

    document.getElementById('frecModalBtnGuardar').addEventListener('click', function () {
        const raw = document.getElementById('frecModalInput').value;
        if (raw === '' || raw === null) {
            Swal.fire('Falta valor', 'Escribe un número entre 0 y 365.', 'info');
            return;
        }
        const valor = parseInt(raw, 10);
        if (isNaN(valor) || valor < 0 || valor > 365) {
            Swal.fire('Valor inválido', 'El número debe estar entre 0 y 365.', 'warning');
            return;
        }
        const fd = new FormData();
        fd.append('id_cliente', ID_CLIENTE);
        fd.append('slug_inspeccion', frecModalSlug);
        fd.append('veces_anio', String(valor));
        this.disabled = true;
        const orig = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        fetch(URL_SET_FRECUENCIA, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    frecModalBs.hide();
                    const slugRefresh = frecModalSlug;
                    if (slugRefresh) actualizarFila(slugRefresh);
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: frecModalLabel + ' → ' + valor + ' veces/año',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    this.disabled = false;
                    this.innerHTML = orig;
                } else {
                    Swal.fire('Error', res.msg || 'No se pudo guardar.', 'error');
                    this.disabled = false;
                    this.innerHTML = orig;
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Error de red.', 'error');
                this.disabled = false;
                this.innerHTML = orig;
            });
    });
    // ================ FIN FRECUENCIA ================
});
</script>
