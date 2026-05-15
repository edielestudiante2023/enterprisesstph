<?php
// Partial: renderiza una <tr> de la matriz de inspecciones.
// Espera en scope: $f (datos de la fila) y $cliente (registro del cliente).
?>
                <?php
                $badgeClass = [
                    'hecha'     => 'background:#d4edda; color:#155724;',
                    'al_dia'    => 'background:#cce5d0; color:#0f5132;',
                    'pendiente' => 'background:#fff3cd; color:#856404;',
                    'atrasada'  => 'background:#f8d7da; color:#721c24;',
                    'no_aplica' => 'background:#e2e3e5; color:#383d41;',
                ][$f['estado']];

                // "Hecha" y "Al día" se presentan unificados como "Realizadas"; el badge
                // muestra el progreso contra la meta anual para que el consultor vea
                // cuántas faltan para quedar al día.
                $vecesAnioBadge      = $f['veces_anio'] ?? null;
                $realizadasAnioBadge = (int) ($f['realizadas_anio'] ?? 0);
                $badgeHint = '';
                if ($f['estado'] === 'al_dia') {
                    $badgeLabel = '<i class="fas fa-shield-alt"></i> Al día'
                        . ($vecesAnioBadge > 0 ? ' · ' . $realizadasAnioBadge . '/' . (int) $vecesAnioBadge : '');
                } elseif ($f['estado'] === 'hecha') {
                    if ($vecesAnioBadge !== null && $vecesAnioBadge > 0) {
                        $faltanBadge = max(0, (int) $vecesAnioBadge - $realizadasAnioBadge);
                        $badgeLabel = '<i class="fas fa-check-circle"></i> ' . $realizadasAnioBadge . '/' . (int) $vecesAnioBadge
                            . ' · falta' . ($faltanBadge === 1 ? '' : 'n') . ' ' . $faltanBadge;
                    } else {
                        $badgeLabel = '<i class="fas fa-check-circle"></i> Hecha' . ($f['total'] > 1 ? ' (' . $f['total'] . ')' : '');
                        if ($vecesAnioBadge === null) {
                            $badgeHint = 'Define la frecuencia para ver cuántas faltan';
                        }
                    }
                } else {
                    $badgeLabel = [
                        'pendiente' => '<i class="fas fa-clock"></i> Pendiente',
                        'atrasada'  => '<i class="fas fa-exclamation-triangle"></i> Atrasada',
                        'no_aplica' => '<i class="fas fa-ban"></i> No Aplica',
                    ][$f['estado']];
                }
                $estadoTexto = ['hecha' => 'Realizadas', 'al_dia' => 'Realizadas', 'pendiente' => 'Pendiente', 'atrasada' => 'Atrasada', 'no_aplica' => 'No Aplica'][$f['estado']];
                $ptaAbiertasCntRow = 0;
                foreach ($f['pta_vinculados'] as $vv) {
                    if (($vv['estado_actividad'] ?? '') !== 'CERRADA') $ptaAbiertasCntRow++;
                }
                $tieneRealizRow = $f['total'] > 0 || (int) ($f['realizadas_anio'] ?? 0) > 0;
                $porSincronizarRow = $tieneRealizRow && ($ptaAbiertasCntRow > 0 || empty($f['pta_vinculados']));
                ?>
                <?php
                $fechasRealizadasArr = array_values(array_filter(array_map(fn($i) => $i['fecha'] ?? null, $f['inspecciones'])));
                $fechasProgramadasArr = array_values(array_filter(array_map(fn($v) => $v['fecha_propuesta'] ?? null, $f['pta_vinculados'])));
                $fechasTodasArr = array_values(array_unique(array_merge($fechasRealizadasArr, $fechasProgramadasArr)));
                ?>
                <tr data-estado="<?= esc($f['estado']) ?>"
                    data-slug="<?= esc($f['slug']) ?>"
                    data-por-sincronizar="<?= $porSincronizarRow ? '1' : '0' ?>"
                    data-frecuencia="<?= $vecesAnioBadge === null ? 'sin_definir' : (string) (int) $vecesAnioBadge ?>"
                    data-realizadas-anio="<?= $realizadasAnioBadge ?>"
                    data-fechas="<?= esc(implode(',', $fechasTodasArr)) ?>"
                    data-fechas-realizadas="<?= esc(implode(',', $fechasRealizadasArr)) ?>"
                    data-fechas-programadas="<?= esc(implode(',', $fechasProgramadasArr)) ?>"
                    data-planeadas-count="<?= count($fechasProgramadasArr) ?>"
                    <?= $f['estado'] === 'no_aplica' ? 'style="opacity:0.6;"' : '' ?>>
                    <td style="font-size:11px; color:#555;"><?= esc($f['group']) ?></td>
                    <td style="text-align:center;">
                        <input type="checkbox" class="bulk-row-check"
                            value="<?= esc($f['slug']) ?>"
                            data-label="<?= esc($f['label']) ?>"
                            title="Seleccionar <?= esc($f['label']) ?>">
                    </td>
                    <td style="font-size:13px;">
                        <?php if (in_array($f['estado'], ['hecha', 'al_dia'], true) && ($f['total'] > 0 || (int) ($f['realizadas_anio'] ?? 0) > 0)): ?>
                            <i class="fas fa-circle-check" style="color:#198754; font-size:18px; margin-right:4px;" title="Elaborada"></i>
                        <?php endif; ?>
                        <i class="fas <?= esc($f['icon']) ?>" style="color:#bd9751; width:18px;"></i>
                        <?= esc($f['label']) ?>
                        <?php
                        $vecesAnio      = $f['veces_anio']      ?? null;
                        $realizadasAnio = (int) ($f['realizadas_anio'] ?? 0);
                        if ($vecesAnio === null) {
                            $frecText  = 'Sin definir';
                            $frecColor = '#888';
                        } elseif ($vecesAnio === 0) {
                            $frecText  = 'Puntual';
                            $frecColor = '#0d6efd';
                        } else {
                            $frecText  = $realizadasAnio . ' / ' . $vecesAnio;
                            $frecColor = $realizadasAnio >= $vecesAnio ? '#0f5132' : '#0d6efd';
                        }
                        ?>
                        <button type="button" class="badge btn-frecuencia"
                            data-slug="<?= esc($f['slug']) ?>"
                            data-label="<?= esc($f['label']) ?>"
                            data-veces-anio="<?= $vecesAnio === null ? '' : (int) $vecesAnio ?>"
                            style="background:transparent; color:<?= $frecColor ?>; border:1px solid <?= $frecColor ?>; font-weight:500; padding:2px 6px; font-size:10px; cursor:pointer; margin-left:4px;"
                            title="Veces por año configuradas para este cliente — click para cambiar">
                            <i class="fas fa-redo-alt"></i> <?= esc($frecText) ?>
                        </button>
                    </td>
                    <td style="font-size:12px;" data-order="<?= esc($f['ultima'] ?? $f['proxima_planeada'] ?? $f['ultima_vencida'] ?? '9999-99-99') ?>">
                        <?php if ($f['estado'] === 'no_aplica'): ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <?php if ($f['total'] === 0): ?>
                                <?php if ($f['estado'] === 'al_dia'): ?>
                                    <span style="color:#0f5132; font-weight:600;">
                                        <i class="fas fa-shield-alt"></i> Cumple meta del año (<?= (int) ($f['realizadas_anio'] ?? 0) ?> de <?= (int) ($f['veces_anio'] ?? 0) ?>)
                                    </span>
                                    <?php if (!empty($f['ultima_global'])): ?>
                                        <small class="d-block text-muted" style="font-size:10px;">
                                            Última realizada: <?= date('d/m/Y', strtotime($f['ultima_global'])) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php elseif ($f['estado'] === 'atrasada' && !empty($f['ultima_vencida'])): ?>
                                    <span style="color:#721c24; font-weight:600;">
                                        <i class="fas fa-exclamation-triangle"></i> Vencida desde <?= date('d/m/Y', strtotime($f['ultima_vencida'])) ?>
                                    </span>
                                <?php elseif (!empty($f['proxima_planeada'])): ?>
                                    <span style="color:#856404; font-weight:600;">
                                        <i class="fas fa-calendar-day"></i> Próxima: <?= date('d/m/Y', strtotime($f['proxima_planeada'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sin registros</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php foreach ($f['inspecciones'] as $i => $insp): if ($i >= 3) break; ?>
                                    <a href="<?= base_url($f['view_route'] . '/' . (int) $insp['id']) ?>"
                                       class="badge text-decoration-none me-1"
                                       style="background:#eef2f7; color:#1c2437; font-weight:500; padding:4px 7px;"
                                       title="Ver inspección">
                                        <?= date('d/m/Y', strtotime($insp['fecha'])) ?>
                                    </a>
                                <?php endforeach; ?>
                                <?php if ($f['total'] > 3): ?>
                                    <a href="<?= base_url($f['list_route']) ?>" class="small text-muted">+<?= $f['total'] - 3 ?> más</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php
                            $ptaVisibles = array_values(array_filter($f['pta_vinculados'], fn($v) => !empty($v['fecha_propuesta'])));
                            $ptaCount = count($ptaVisibles);
                            ?>
                            <?php if ($ptaCount > 0): ?>
                                <details class="pta-details mt-1">
                                    <summary class="pta-summary">
                                        <i class="fas fa-calendar-alt"></i> <?= $ptaCount ?> planeada<?= $ptaCount > 1 ? 's' : '' ?>
                                    </summary>
                                    <div class="pta-list mt-1">
                                        <?php foreach ($ptaVisibles as $v): ?>
                                            <?php
                                            $isCerrada = ($v['estado_actividad'] ?? '') === 'CERRADA';
                                            $isGestionando = ($v['estado_actividad'] ?? '') === 'GESTIONANDO';
                                            ?>
                                            <div class="pta-item<?= $isCerrada ? ' pta-item-cerrada' : '' ?>">
                                                <span class="pta-date">
                                                    <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($v['fecha_propuesta'])) ?>
                                                    <?php if ($isCerrada): ?><span class="pta-check">✓</span><?php endif; ?>
                                                    <i class="fas fa-pen pta-edit-fecha"
                                                       data-id-pta="<?= (int) $v['id_ptacliente'] ?>"
                                                       data-fecha="<?= esc($v['fecha_propuesta']) ?>"
                                                       title="Editar fecha propuesta"></i>
                                                    <?php if ($isCerrada): ?>
                                                        <i class="fas fa-eraser pta-reabrir"
                                                           data-id-pta="<?= (int) $v['id_ptacliente'] ?>"
                                                           title="Reabrir: estado → ABIERTA, vaciar fecha de cierre"></i>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if (!empty($v['numeral_plandetrabajo']) && $v['numeral_plandetrabajo'] !== '-'): ?>
                                                    <span class="pta-numeral"><?= esc($v['numeral_plandetrabajo']) ?></span>
                                                <?php endif; ?>
                                                <?php if ($isGestionando): ?>
                                                    <span class="pta-estado-gest">Gestionando</span>
                                                <?php endif; ?>
                                                <span class="pta-text" title="<?= esc($v['actividad_plandetrabajo'] ?? '') ?>">
                                                    <?= esc(mb_substr($v['actividad_plandetrabajo'] ?? '', 0, 140)) ?><?= mb_strlen($v['actividad_plandetrabajo'] ?? '') > 140 ? '…' : '' ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge" style="<?= $badgeClass ?> font-size:11px; padding:5px 8px;">
                            <?= $badgeLabel ?>
                        </span>
                        <span class="d-none"><?= esc($estadoTexto) ?><?php
                            // Texto invisible adicional para que el filtro 'Por sincronizar' (col 3) matchee
                            if ($porSincronizarRow) {
                                echo ' Por sincronizar';
                            }
                        ?></span>
                        <?php if ($badgeHint !== ''): ?>
                            <div class="small text-muted mt-1" style="font-size:10px;"><i class="fas fa-info-circle"></i> <?= esc($badgeHint) ?></div>
                        <?php endif; ?>
                        <?php if ($f['estado'] === 'no_aplica' && !empty($f['no_aplica']['motivo'])): ?>
                            <div class="small text-muted mt-1" style="font-size:10px;"><?= esc($f['no_aplica']['motivo']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <?php if ($f['estado'] !== 'no_aplica'): ?>
                            <a href="<?= base_url($f['create_route'] . '/' . (int) $cliente['id_cliente']) ?>"
                               class="btn btn-xs btn-outline-success" title="Nueva inspección"
                               style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a href="<?= base_url($f['list_route']) ?>"
                               class="btn btn-xs btn-outline-dark" title="Listar todas"
                               style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-list"></i>
                            </a>
                            <button type="button" class="btn btn-xs btn-outline-primary btn-vincular-pta"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                data-count="<?= count($f['pta_vinculados']) ?>"
                                title="Vincular actividades del Plan de Trabajo"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-link"></i><?= count($f['pta_vinculados']) > 0 ? ' ' . count($f['pta_vinculados']) : '' ?>
                            </button>
                            <?php if (!empty($f['pta_vinculados'])): ?>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-desvincular-pta"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                data-count="<?= count($f['pta_vinculados']) ?>"
                                title="Desvincular actividades del Plan de Trabajo para este año"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-unlink"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (empty($f['pta_vinculados'])): ?>
                            <button type="button" class="btn btn-xs btn-outline-success btn-crear-pta"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                title="Crear actividad en el Plan de Trabajo"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-calendar-plus"></i> PTA
                            </button>
                            <?php endif; ?>
                            <?php
                            // Botón "Imprimir en PTA": visible si hay al menos una inspección realizada
                            // (en el año o de cualquier época) y existen PTAs no cerradas, o no hay PTAs
                            $ptaAbiertasCount = 0;
                            foreach ($f['pta_vinculados'] as $v) {
                                if (($v['estado_actividad'] ?? '') !== 'CERRADA') $ptaAbiertasCount++;
                            }
                            $tieneRealizadas = $f['total'] > 0 || (int) ($f['realizadas_anio'] ?? 0) > 0;
                            $mostrarCerrar = $tieneRealizadas && ($ptaAbiertasCount > 0 || empty($f['pta_vinculados']));
                            ?>
                            <?php if ($mostrarCerrar): ?>
                            <?php $esHuerfana = empty($f['pta_vinculados']); ?>
                            <?php if ($esHuerfana): ?>
                                <i class="fas fa-circle"
                                    title="Hecha pero SIN actividad en el Plan de Trabajo — al cerrar se crearán PTAs retroactivas"
                                    style="color:#ffc107; font-size:14px; margin-right:2px;"></i>
                            <?php endif; ?>
                            <button type="button" class="btn btn-xs btn-outline-success btn-cerrar-pta-matriz"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                data-realizadas="<?= max($f['total'], (int) ($f['realizadas_anio'] ?? 0)) ?>"
                                data-abiertas="<?= $ptaAbiertasCount ?>"
                                title="Imprimir en PTA con las fechas reales de las inspecciones"
                                style="padding:2px 7px;font-size:11px; background:#87ceeb; border-color:#6bbfe3; color:#0b3d4f;">
                                <i class="fas fa-print"></i> Imprimir en PTA
                            </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-xs btn-outline-secondary btn-marcar-na"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                title="Marcar como No Aplica"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-ban"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-xs btn-outline-warning btn-quitar-na"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                title="Quitar No Aplica"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-undo"></i> Quitar N/A
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
