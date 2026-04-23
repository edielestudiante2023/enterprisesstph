<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/piscinas/update/') . $inspeccion['id'] : base_url('/inspecciones/piscinas/store');

// Campos SI/NO/NA simples agrupados por bloque
$GRUPOS = [
    'infraestructura' => [
        'label' => 'Infraestructura y seguridad',
        'fields' => [
            'cerramiento_perimetral'         => 'Cerramiento perimetral',
            'puerta_control_acceso'          => 'Puerta con control de acceso',
            'alarma_inmersion_80db'          => 'Alarma de inmersion 80dB',
            'boton_parada_emergencia'        => 'Boton parada de emergencia (recirculacion)',
            'drenaje_antiatrapamiento'       => 'Drenaje antiatrapamiento',
            'minimo_dos_drenajes'            => 'Minimo dos drenajes',
            'sistema_liberacion_vacio'       => 'Sistema liberacion de vacio',
            'senalizacion_profundidad'       => 'Senalizacion de profundidad',
            'baldosas_cambio_profundidad'    => 'Baldosas cambio de profundidad',
            'escaleras_acceso_antideslizantes' => 'Escaleras de acceso antideslizantes',
            'baranda_escaleras'              => 'Baranda en escaleras',
            'iluminacion_adecuada'           => 'Iluminacion adecuada',
            'ventilacion_adecuada'           => 'Ventilacion adecuada (si climatizada)',
        ],
    ],
    'avisos' => [
        'label' => 'Avisos visibles',
        'fields' => [
            'aviso_menores_12'          => 'Menores de 12 anos (prohibicion/supervision)',
            'aviso_reglamento'          => 'Reglamento de uso',
            'aviso_horario'             => 'Horario',
            'aviso_ducharse_antes'      => 'Ducharse antes de ingresar',
            'aviso_prohibido_zapatos'   => 'Prohibido zapatos de calle',
            'aviso_telefonos_emergencia'=> 'Telefonos de emergencia',
            'aviso_aforo_visible'       => 'Aforo visible',
        ],
    ],
    'emergencia' => [
        'label' => 'Emergencia',
        'fields' => [
            'camilla_rescate'             => 'Camilla rigida de rescate',
            'flotadores_circulares_min_2' => 'Flotadores circulares (min. 2)',
            'baston_con_gancho'           => 'Baston con gancho',
            'citofono_24h'                => 'Citofono 24h',
        ],
    ],
    'higiene' => [
        'label' => 'Higiene y accesibilidad',
        'fields' => [
            'duchas_previas_obligatorias' => 'Duchas previas obligatorias',
            'baranda_apoyo_duchas'        => 'Baranda de apoyo en duchas',
            'lavapies_funcional'          => 'Lavapies funcional',
        ],
    ],
    'dosificacion' => [
        'label' => 'Dosificacion y equipos (Art. 5)',
        'fields' => [
            'dosificacion_independiente'         => 'Dosificacion independiente por quimico',
            'sistema_seguridad_flujo'            => 'Sistema de seguridad por retorno de flujo',
            'no_dosificacion_manual_con_publico' => 'NO dosificacion manual con publico presente',
            'equipo_bombeo_operativo'            => 'Equipo de bombeo operativo',
            'filtros_operativos'                 => 'Filtros operativos',
        ],
    ],
    'libro' => [
        'label' => 'Libro de registro (Art. 16)',
        'fields' => [
            'libro_registro_existe' => 'Existe libro/registro sistematizado',
        ],
    ],
];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="pisForm">
        <?= csrf_field() ?>

        <div class="mt-2 mb-2 d-flex flex-wrap gap-2 align-items-center">
            <a href="https://camacol.co/sites/default/files/descargables/RESOLUCION%20MINSALUD%20NACIONAL%20234%20DE%20FEBRERO%20DE%202026.pdf"
               target="_blank" rel="noopener"
               class="btn btn-sm"
               style="background:#bd9751;color:#fff;font-size:12px;padding:5px 12px;">
                <i class="fas fa-book"></i> Ver normativa (Res 234/2026 + anexos técnicos)
            </a>
            <span style="font-size:11px;color:#777;">
                Ministerio de Salud y Protección Social · Calidad del agua + buenas prácticas sanitarias + IRAPI + botiquines.
            </span>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <?php $advertencias = session()->getFlashdata('advertencias_cruzadas'); if (!empty($advertencias)): ?>
        <div class="alert alert-warning mt-2" style="font-size:13px;">
            <strong><i class="fas fa-triangle-exclamation"></i> Validaciones cruzadas detectadas:</strong>
            <ul class="mb-2" style="margin-top:4px;">
                <?php foreach ($advertencias as $a): ?><li><?= esc($a) ?></li><?php endforeach; ?>
            </ul>
            <div class="form-text" style="font-size:12px;">Corrige las inconsistencias arriba, o si confirmas que son correctas, marca la casilla al final del formulario para finalizar igual.</div>
        </div>
        <?php endif; ?>

        <div class="accordion mt-2" id="accordionPis">

            <!-- BLOQUE MAESTRO -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">Datos generales del establecimiento</button></h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionPis">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required><option value="">Seleccionar cliente...</option></select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Fecha inspeccion *</label>
                                <input type="date" name="fecha_inspeccion" class="form-control" value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Superficie total zona húmeda (m²)</label>
                                <input type="number" step="0.01" name="superficie_total_establecimiento_m2" class="form-control" value="<?= esc($inspeccion['superficie_total_establecimiento_m2'] ?? '') ?>" placeholder="Ej: 750">
                                <div class="form-text" style="font-size:11px; line-height:1.3;">
                                    Incluye piscina + deck + vestieres + cuarto de bombas. <strong>NO</strong> es la lámina de agua ni el área construida del edificio. Art. 18 Res 234/2026: &lt;500 m² → Tipo A · 500–2000 m² → Tipo B · &gt;2000 m² → Tipo C.
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-1">Empresa de mantenimiento</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Verificar: contrato vigente con la copropiedad, cumplimiento del SG-SST (Decreto 1072/2015),
                            certificados de idoneidad del personal operativo y afiliacion a ARL. Si la empresa asume
                            dosificacion, aplica tambien el Art. 5 Res 234 (dosificacion segura).
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6"><label class="form-label" style="font-size:12px;">Empresa (razon social)</label><input type="text" name="empresa_mantenimiento" class="form-control form-control-sm" value="<?= esc($inspeccion['empresa_mantenimiento'] ?? '') ?>"></div>
                            <div class="col-6 col-md-3"><label class="form-label" style="font-size:12px;">NIT</label><input type="text" name="nit_empresa_mantenimiento" class="form-control form-control-sm" value="<?= esc($inspeccion['nit_empresa_mantenimiento'] ?? '') ?>"></div>
                            <div class="col-6 col-md-3"><label class="form-label" style="font-size:12px;">Contacto</label><input type="text" name="contacto_empresa_mantenimiento" class="form-control form-control-sm" value="<?= esc($inspeccion['contacto_empresa_mantenimiento'] ?? '') ?>"></div>
                        </div>
                        <?php
                        // renderEvidenciaMultiFoto aun no esta definida en este punto del PHP render flow.
                        // Se define mas abajo en el bloque siguiente, entonces lo incrustamos inline con un stub temprano:
                        $rowsEmp = $evidenciasMap['empresa_mantenimiento'] ?? [];
                        ?>
                        <div class="col-12 mb-3">
                            <label class="form-label" style="font-size:11px;"><i class="fas fa-images me-1"></i> Evidencias empresa de mantenimiento (contrato, certificados, SG-SST)</label>
                            <?php if (!empty($rowsEmp)): ?>
                            <div class="d-flex flex-wrap gap-2 mb-2" style="border:1px dashed #ccc; padding:6px; border-radius:4px; background:#fafafa;">
                                <?php foreach ($rowsEmp as $r): ?>
                                <div class="evidencia-thumb" data-id="<?= (int)$r['id'] ?>" style="position:relative; width:90px; height:90px;">
                                    <img src="<?= base_url('/' . $r['foto_path']) ?>" style="width:90px;height:90px;object-fit:cover;border:1px solid #bbb;border-radius:4px;cursor:pointer;" onclick="openPhoto('<?= base_url('/' . $r['foto_path']) ?>')">
                                    <button type="button" class="btn-remove-evidencia" data-id="<?= (int)$r['id'] ?>" title="Eliminar" style="position:absolute;top:-8px;right:-8px;width:22px;height:22px;border-radius:50%;background:#c0392b;color:#fff;border:none;font-size:12px;line-height:20px;padding:0;cursor:pointer;">×</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="item_foto_empresa_mantenimiento[]" class="form-control form-control-sm" accept="image/*" multiple>
                            <div class="form-text" style="font-size:10px;">Multiples fotos: contrato firmado, certificaciones del personal, planilla ARL, polizas vigentes.</div>
                        </div>
                        <hr>
                        <?php
                        /**
                         * Componente multi-foto para evidencia maestro.
                         * - Muestra las fotos existentes como miniaturas con botón de eliminar.
                         * - Permite seleccionar N fotos nuevas (accept="image/*", multiple).
                         */
                        $renderEvidenciaMultiFoto = function($campoCodigo) use ($evidenciasMap) {
                            $rows = $evidenciasMap[$campoCodigo] ?? [];
                            $html = '<div class="col-12 mb-3"><label class="form-label" style="font-size:11px;"><i class="fas fa-images me-1"></i> Evidencias fotograficas (galeria - multiples)</label>';
                            if (!empty($rows)) {
                                $html .= '<div class="d-flex flex-wrap gap-2 mb-2" style="border:1px dashed #ccc; padding:6px; border-radius:4px; background:#fafafa;">';
                                foreach ($rows as $r) {
                                    $id = (int)$r['id'];
                                    $src = base_url('/' . $r['foto_path']);
                                    $html .= '<div class="evidencia-thumb" data-id="' . $id . '" style="position:relative; width:90px; height:90px;">'
                                        . '<img src="' . $src . '" style="width:90px;height:90px;object-fit:cover;border:1px solid #bbb;border-radius:4px;cursor:pointer;" onclick="openPhoto(\'' . $src . '\')">'
                                        . '<button type="button" class="btn-remove-evidencia" data-id="' . $id . '" title="Eliminar" style="position:absolute;top:-8px;right:-8px;width:22px;height:22px;border-radius:50%;background:#c0392b;color:#fff;border:none;font-size:12px;line-height:20px;padding:0;cursor:pointer;">×</button>'
                                        . '</div>';
                                }
                                $html .= '</div>';
                            }
                            $html .= '<input type="file" name="item_foto_' . $campoCodigo . '[]" class="form-control form-control-sm" accept="image/*" multiple>';
                            $html .= '<div class="form-text" style="font-size:10px;">Puedes seleccionar varias fotos a la vez. Se anadiran a las existentes.</div>';
                            $html .= '</div>';
                            return $html;
                        };
                        ?>

                        <!-- Holder oculto para IDs de evidencias a eliminar (lo llena JS) -->
                        <div id="evidenciasBorrarHolder" style="display:none;"></div>
                        <div id="evidenciasDetBorrarHolder" style="display:none;"></div>

                        <!-- CONCEPTO SANITARIO -->
                        <h6 class="mb-1">Concepto sanitario Secretaria de Salud</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Art. 10 Res 234/2026: documento emitido por la autoridad sanitaria (Secretaria de Salud) tras fiscalizar el estanque.
                            Concepto <strong>desfavorable</strong> (Art. 11 num 2) dispara priorizacion de muestreo.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;">Estado</label>
                                <select name="concepto_sanitario" class="form-select form-select-sm">
                                    <?php $cs = $inspeccion['concepto_sanitario'] ?? 'no_emitido'; foreach (['favorable'=>'Favorable','desfavorable'=>'Desfavorable','no_emitido'=>'No emitido'] as $v=>$lbl): ?>
                                    <option value="<?= $v ?>" <?= $cs===$v?'selected':'' ?>><?= $lbl ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2"><label class="form-label" style="font-size:12px;">Fecha</label><input type="date" name="concepto_sanitario_fecha" class="form-control form-control-sm" value="<?= $inspeccion['concepto_sanitario_fecha'] ?? '' ?>"></div>
                            <div class="col-12 col-md-3"><label class="form-label" style="font-size:12px;">Observaciones</label><input type="text" name="concepto_sanitario_observaciones" class="form-control form-control-sm" value="<?= esc($inspeccion['concepto_sanitario_observaciones'] ?? '') ?>"></div>
                            <?= $renderEvidenciaMultiFoto('concepto_sanitario') ?>
                        </div>
                        <hr>

                        <!-- DEA -->
                        <h6 class="mb-1">DEA — Desfibrilador Externo Automatico (Art. 18)</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Equipo portatil que aplica descarga electrica en paro cardiaco. Art. 18 Res 234/2026 lo exige por alta afluencia.
                            <strong>Verifique:</strong> presencia fisica, bateria OK, ubicacion visible con aviso pictograma DEA, personal con curso vigente (BLS / primeros auxilios).
                        </div>
                        <div class="row g-2 mb-3">
                            <?php foreach (['dea_presente'=>'DEA presente','dea_ubicacion_senalizada'=>'Ubicacion senalizada'] as $f=>$lbl): ?>
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;"><?= $lbl ?></label>
                                <select name="<?= $f ?>" class="form-select form-select-sm">
                                    <?php $v = $inspeccion[$f] ?? 'NA'; foreach (['SI','NO','NA'] as $o): ?><option value="<?= $o ?>" <?= $v===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <?php endforeach; ?>
                            <div class="col-6 col-md-2"><label class="form-label" style="font-size:12px;">Personal capacitado</label><input type="number" min="0" name="dea_personal_capacitado_cantidad" class="form-control form-control-sm" value="<?= esc($inspeccion['dea_personal_capacitado_cantidad'] ?? 0) ?>"></div>
                            <?= $renderEvidenciaMultiFoto('dea') ?>
                        </div>
                        <hr>

                        <!-- OPERADOR -->
                        <h6 class="mb-1">Operador de piscinas certificado</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Art. 11 num 7 Res 234/2026: la <strong>ausencia de operador certificado</strong> es factor de priorizacion sanitaria.
                            Entidades que expiden certificacion: SENA, IDEAM, universidades, o autoridad sanitaria municipal.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-3"><label class="form-label" style="font-size:12px;">Nombre</label><input type="text" name="operador_certificado_nombre" class="form-control form-control-sm" value="<?= esc($inspeccion['operador_certificado_nombre'] ?? '') ?>"></div>
                            <div class="col-12 col-md-3"><label class="form-label" style="font-size:12px;">Entidad certificadora</label><input type="text" name="operador_certificado_entidad" class="form-control form-control-sm" value="<?= esc($inspeccion['operador_certificado_entidad'] ?? '') ?>"></div>
                            <div class="col-12 col-md-2"><label class="form-label" style="font-size:12px;">Vigencia</label><input type="date" name="operador_certificado_vigencia" class="form-control form-control-sm" value="<?= $inspeccion['operador_certificado_vigencia'] ?? '' ?>"></div>
                            <?= $renderEvidenciaMultiFoto('operador_cert') ?>
                        </div>
                        <hr>

                        <!-- DOCUMENTACION ART 15 -->
                        <h6 class="mb-1">Documentacion Art. 15 — 8 procedimientos obligatorios</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            La copropiedad debe tener documentado:
                            (1) operacion y mantenimiento del agua ·
                            (2) limpieza del sistema ·
                            (3) cierre temporal ·
                            (4) recoleccion de muestras y analisis in situ ·
                            (5) protocolo de resultados fuera de rango + liberacion fecal ·
                            (6) manejo de microorganismos no listados ·
                            (7) libro o registro sistematizado de control ·
                            (8) Plan de Saneamiento Basico.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;">Estado</label>
                                <select name="documentacion_art15_completa" class="form-select form-select-sm">
                                    <?php $v = $inspeccion['documentacion_art15_completa'] ?? 'NA'; foreach (['SI','NO','PARCIAL','NA'] as $o): ?><option value="<?= $o ?>" <?= $v===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-5"><label class="form-label" style="font-size:12px;">Observaciones</label><input type="text" name="documentacion_art15_observaciones" class="form-control form-control-sm" value="<?= esc($inspeccion['documentacion_art15_observaciones'] ?? '') ?>" placeholder="Cuales faltan, cuales estan desactualizados..."></div>
                            <?= $renderEvidenciaMultiFoto('doc_art15') ?>
                        </div>
                        <hr>

                        <!-- PLAN SANEAMIENTO -->
                        <h6 class="mb-1">Plan de Saneamiento Basico Art. 17 — 5 programas</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Debe contener:
                            (1) limpieza y desinfeccion ·
                            (2) gestion integral de residuos solidos ·
                            (3) gestion integral de residuos liquidos / lodos ·
                            (4) control integrado de plagas ·
                            (5) abastecimiento de agua para consumo humano.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;">Estado</label>
                                <select name="plan_saneamiento_completo" class="form-select form-select-sm">
                                    <?php $v = $inspeccion['plan_saneamiento_completo'] ?? 'NA'; foreach (['SI','NO','PARCIAL','NA'] as $o): ?><option value="<?= $o ?>" <?= $v===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-5"><label class="form-label" style="font-size:12px;">Observaciones</label><input type="text" name="plan_saneamiento_observaciones" class="form-control form-control-sm" value="<?= esc($inspeccion['plan_saneamiento_observaciones'] ?? '') ?>" placeholder="Programas que faltan o estan desactualizados..."></div>
                            <?= $renderEvidenciaMultiFoto('plan_saneamiento') ?>
                        </div>
                        <hr>

                        <!-- MANEJO QUIMICOS -->
                        <h6 class="mb-1">Manejo seguro de productos quimicos (Art. 13)</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Verifique: <strong>fichas tecnicas</strong> y <strong>Hojas de Seguridad (SDS)</strong> visibles,
                            <strong>EPP</strong> apropiados (guantes, gafas, respirador), etiquetado <strong>GHS/SGA</strong> conforme
                            Decreto 1496/2018 y cumplimiento del SG-SST Decreto 1072/2015.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;">Estado</label>
                                <select name="manejo_quimicos_conforme" class="form-select form-select-sm">
                                    <?php $v = $inspeccion['manejo_quimicos_conforme'] ?? 'NA'; foreach (['SI','NO','NA'] as $o): ?><option value="<?= $o ?>" <?= $v===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <?= $renderEvidenciaMultiFoto('manejo_quimicos') ?>
                        </div>
                        <hr>

                        <!-- AREA RESIDUOS -->
                        <h6 class="mb-1">Area de almacenamiento de residuos (Art. 14)</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Verifique: area especifica <strong>senalizada</strong>, separada por tipo de residuo, con iluminacion y
                            ventilacion, pisos con drenajes + rejillas protectoras, paredes lavables, que impidan ingreso de vectores.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;">Estado</label>
                                <select name="area_residuos_conforme" class="form-select form-select-sm">
                                    <?php $v = $inspeccion['area_residuos_conforme'] ?? 'NA'; foreach (['SI','NO','NA'] as $o): ?><option value="<?= $o ?>" <?= $v===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <?= $renderEvidenciaMultiFoto('area_residuos') ?>
                        </div>
                        <hr>

                        <!-- CONTENEDORES COLOR -->
                        <h6 class="mb-1">Contenedores codificados por color</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            <strong>Rojo</strong> = biologicos / biosanitarios (tapabocas, gasas con sangre) ·
                            <strong>Verde</strong> = ordinarios no aprovechables ·
                            <strong>Blanco</strong> = aprovechables (plastico, metal, carton) ·
                            <strong>Negro</strong> = peligrosos (quimicos, tintas, envases de cloro).
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;">Estado</label>
                                <select name="contenedores_codificados_color" class="form-select form-select-sm">
                                    <?php $v = $inspeccion['contenedores_codificados_color'] ?? 'NA'; foreach (['SI','NO','NA'] as $o): ?><option value="<?= $o ?>" <?= $v===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <?= $renderEvidenciaMultiFoto('contenedores_color') ?>
                        </div>
                        <hr>

                        <!-- TABLERO PUBLICO -->
                        <h6 class="mb-1">Tablero publico con resultados mensuales</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            Art. 16 par. 2 Res 234/2026: tablero <strong>visible y legible al publico</strong> con los resultados analiticos
                            efectuados al agua de cada estanque. Se actualiza <strong>mensualmente</strong>.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label" style="font-size:12px;">Estado</label>
                                <select name="tablero_publico_resultados" class="form-select form-select-sm">
                                    <?php $v = $inspeccion['tablero_publico_resultados'] ?? 'NA'; foreach (['SI','NO','NA'] as $o): ?><option value="<?= $o ?>" <?= $v===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <?= $renderEvidenciaMultiFoto('tablero_publico') ?>
                        </div>

                    </div>
                </div>
            </div>

            <!-- BLOQUE PISCINAS -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secPiscinas">Piscinas inspeccionadas (<span id="countPis"><?= count($piscinas ?? []) ?></span>)</button></h2>
                <div id="secPiscinas" class="accordion-collapse collapse" data-bs-parent="#accordionPis">
                    <div class="accordion-body p-2">
                        <input type="hidden" name="total_piscinas" id="totalPiscinasInput" value="<?= count($piscinas ?? []) ?>">
                        <div id="piscinasContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddPiscina"><i class="fas fa-plus"></i> Agregar piscina</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body p-2">
                <label class="form-label" style="font-size:13px;">Recomendaciones generales</label>
                <textarea name="recomendaciones_generales" class="form-control" rows="3"><?= esc($inspeccion['recomendaciones_generales'] ?? '') ?></textarea>
            </div>
        </div>

        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;"><i class="fas fa-cloud"></i> Autoguardado activado</div>

        <?php if (!empty($advertencias)): ?>
        <div class="form-check mt-2 mb-3">
            <input class="form-check-input" type="checkbox" name="ignorar_advertencias" value="1" id="ignorarAdvertencias">
            <label class="form-check-label" for="ignorarAdvertencias" style="font-size:13px;">
                Confirmo que revise las advertencias y quiero finalizar igual.
            </label>
        </div>
        <?php endif; ?>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;"><i class="fas fa-save"></i> Guardar borrador</button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar"><i class="fas fa-check-circle"></i> Finalizar inspeccion</button>
        </div>
    </form>
</div>

<style>
.piscina-row { border:2px solid #bd9751; border-radius:8px; padding:10px; margin-bottom:12px; background:#fff; }
.piscina-title { font-size:14px; font-weight:700; color:#1c2437; margin-bottom:6px; }
.sub-block { border:1px solid #e6e6e6; border-radius:6px; margin:6px 0; padding:8px; }
.sub-block-title { font-size:12px; font-weight:700; color:#1c2437; text-transform:uppercase; margin-bottom:4px; border-bottom:1px solid #ddd; padding-bottom:2px; }
.param-row { display:flex; align-items:center; gap:4px; margin-bottom:3px; }
.param-row label { font-size:11px; width:40%; margin:0; }
.param-row input, .param-row select { font-size:11px; padding:2px 4px; }
.small-label { font-size:10px; font-weight:600; color:#555; text-transform:uppercase; }
</style>

<script>
const GRUPOS = <?= json_encode($GRUPOS) ?>;
const PARAMETROS = <?= json_encode($parametrosCfg ?? []) ?>;
const RANGOS_REF = {
    'pH': '6.8 - 7.3', 'cloro_libre': '1.5 - 3.5 (piscinas)', 'cloro_combinado': '≤ 0.3 (piscinas)',
    'temperatura': '≤ 40', 'turbidez': '< 1', 'orp': '≤ 700',
    'acido_cianurico': '20-40 ideal / ≤ 150', 'dureza_calcica': '200-400 / ≤ 700',
    'alcalinidad_total': '60-150', 'tds': '1000-1200', 'conductividad': '2000-2400', 'bromo_total': '2.0-4.0'
};
const PISCINAS_INIT = <?= json_encode($piscinas ?? []) ?>;
const PARAMETROS_INIT = <?= json_encode($parametrosMap ?? []) ?>;
const ENSAYOS_INIT   = <?= json_encode($ensayosMap ?? []) ?>;
const BOTIQUIN_INIT  = <?= json_encode($botiquinMap ?? []) ?>;
const EVIDENCIAS_DET_INIT = <?= json_encode($evidenciasDetMap ?? []) ?>;

function buildEnumSelect(name, value, options) {
    let html = '<select name="' + name + '" class="form-select form-select-sm">';
    options.forEach(o => html += '<option value="' + o + '" ' + (value === o ? 'selected' : '') + '>' + o + '</option>');
    html += '</select>';
    return html;
}

function buildGrupo(grpKey, grp, data) {
    let rows = '';
    Object.keys(grp.fields).forEach(fKey => {
        const valActual = data[fKey] || 'NA';
        rows += '<div class="col-6 col-md-4 mb-1"><label class="small-label">' + grp.fields[fKey] + '</label>' +
                buildEnumSelect('item_' + fKey + '[]', valActual, ['SI','NO','NA']) + '</div>';
    });
    return '<div class="sub-block"><div class="sub-block-title">' + grp.label + '</div><div class="row g-1">' + rows + '</div></div>';
}

function buildParametros(data, idPisc) {
    // data viene como array de filas [{parametro, valor, observaciones, ...}, ...]
    // Lo convertimos a mapa { pH: {valor, observaciones}, cloro_libre: {...}, ... }
    // para poder precargar los valores existentes al editar.
    const paramMap = {};
    if (Array.isArray(data)) {
        data.forEach(r => {
            if (!r || !r.parametro) return;
            paramMap[r.parametro] = { valor: r.valor, observaciones: r.observaciones };
        });
    } else if (data && typeof data === 'object') {
        Object.assign(paramMap, data);
    }

    let rows = '';
    Object.keys(PARAMETROS).forEach(key => {
        const cfg = PARAMETROS[key];
        const rec = paramMap[key] || {};
        const val = (rec.valor !== undefined && rec.valor !== null) ? rec.valor : '';
        const obs = rec.observaciones || '';
        const rango = RANGOS_REF[key] || '';
        rows += '<div class="row g-1 mb-1 align-items-center">' +
                '<div class="col-5"><label class="small-label">' + cfg.label + (cfg.unidad ? ' (' + cfg.unidad + ')' : '') + '</label><div style="font-size:9px;color:#888;">Rango: ' + rango + '</div></div>' +
                '<div class="col-3"><input type="number" step="0.01" name="param_' + key + '[]" class="form-control form-control-sm" value="' + val + '"></div>' +
                '<div class="col-4"><input type="text" name="param_' + key + '_obs[]" class="form-control form-control-sm" placeholder="obs." value="' + (obs || '').replace(/"/g, '&quot;') + '"></div>' +
                '</div>';
    });
    return '<div class="sub-block" style="background:#f0f7ff;"><div class="sub-block-title">Parametros in situ del dia (Anexo I Res 234/2026)</div>' + rows + '</div>';
}

function buildEnsayos(ensayosData) {
    const arr = ensayosData || [];
    const micro = arr.find(e => e.tipo === 'MICROBIOLOGICO') || {};
    const fq    = arr.find(e => e.tipo === 'FISICOQUIMICO') || {};

    const microHtml = `
        <div class="row g-1">
            <div class="col-6 col-md-3"><label class="small-label">Fecha toma</label><input type="date" name="ensayo_microbiologico_fecha[]" class="form-control form-control-sm" value="${micro.fecha_toma || ''}"></div>
            <div class="col-6 col-md-3"><label class="small-label">Laboratorio</label><input type="text" name="ensayo_microbiologico_lab[]" class="form-control form-control-sm" value="${(micro.laboratorio || '').replace(/"/g, '&quot;')}"></div>
            <div class="col-6 col-md-3"><label class="small-label">N° informe</label><input type="text" name="ensayo_microbiologico_informe[]" class="form-control form-control-sm" value="${(micro.numero_informe || '').replace(/"/g, '&quot;')}"></div>
            <div class="col-6 col-md-3"><label class="small-label">Norma citada</label><input type="text" name="ensayo_microbiologico_norma[]" class="form-control form-control-sm" value="${(micro.norma_citada || '').replace(/"/g, '&quot;')}"></div>
            <div class="col-6 col-md-2"><label class="small-label">Heterotrofos UFC</label><input type="number" step="0.01" name="ensayo_microbiologico_heterotrofos[]" class="form-control form-control-sm" value="${micro.heterotrofos_ufc || ''}"></div>
            <div class="col-6 col-md-2"><label class="small-label">Coliformes UFC</label><input type="number" step="0.01" name="ensayo_microbiologico_coliformes[]" class="form-control form-control-sm" value="${micro.coliformes_termotolerantes_ufc || ''}"></div>
            <div class="col-6 col-md-2"><label class="small-label">E.coli UFC</label><input type="number" step="0.01" name="ensayo_microbiologico_ecoli[]" class="form-control form-control-sm" value="${micro.ecoli_ufc || ''}"></div>
            <div class="col-6 col-md-2"><label class="small-label">Pseudomonas UFC</label><input type="number" step="0.01" name="ensayo_microbiologico_pseudomonas[]" class="form-control form-control-sm" value="${micro.pseudomonas_ufc || ''}"></div>
            <div class="col-6 col-md-2"><label class="small-label">Legionella UFC</label><input type="number" step="0.01" name="ensayo_microbiologico_legionella[]" class="form-control form-control-sm" value="${micro.legionella_ufc || ''}"></div>
            <div class="col-6 col-md-2"><label class="small-label">Conforme global</label>${buildEnumSelect('ensayo_microbiologico_conforme[]', micro.conforme_global || 'NA', ['SI','NO','PARCIAL','NA'])}</div>
        </div>`;

    const fqHtml = `
        <div class="row g-1">
            <div class="col-6 col-md-3"><label class="small-label">Fecha toma</label><input type="date" name="ensayo_fisicoquimico_fecha[]" class="form-control form-control-sm" value="${fq.fecha_toma || ''}"></div>
            <div class="col-6 col-md-3"><label class="small-label">Laboratorio</label><input type="text" name="ensayo_fisicoquimico_lab[]" class="form-control form-control-sm" value="${(fq.laboratorio || '').replace(/"/g, '&quot;')}"></div>
            <div class="col-6 col-md-3"><label class="small-label">N° informe</label><input type="text" name="ensayo_fisicoquimico_informe[]" class="form-control form-control-sm" value="${(fq.numero_informe || '').replace(/"/g, '&quot;')}"></div>
            <div class="col-6 col-md-3"><label class="small-label">Norma citada</label><input type="text" name="ensayo_fisicoquimico_norma[]" class="form-control form-control-sm" value="${(fq.norma_citada || '').replace(/"/g, '&quot;')}"></div>
            <div class="col-6 col-md-3"><label class="small-label">Conforme global</label>${buildEnumSelect('ensayo_fisicoquimico_conforme[]', fq.conforme_global || 'NA', ['SI','NO','PARCIAL','NA'])}</div>
            <div class="col-12 col-md-9"><label class="small-label">Observaciones</label><input type="text" name="ensayo_fisicoquimico_obs[]" class="form-control form-control-sm" value="${(fq.observaciones || '').replace(/"/g, '&quot;')}"></div>
        </div>`;

    const iaHtml = `
        <div class="alert alert-info mb-2" style="font-size:10px;padding:4px 8px;margin-top:4px;">
            <i class="fas fa-wand-magic-sparkles"></i>
            <strong>Acelera captura:</strong> sube el PDF del informe y la IA llenara los campos automaticamente.
            <div class="d-flex gap-1 mt-1">
                <input type="file" class="form-control form-control-sm ia-ensayo-file" accept="application/pdf" style="flex:1;">
                <button type="button" class="btn btn-sm btn-primary ia-ensayo-btn" data-tipo="MICROBIOLOGICO" style="font-size:10px;">Leer Micro</button>
                <button type="button" class="btn btn-sm btn-warning ia-ensayo-btn" data-tipo="FISICOQUIMICO" style="font-size:10px;">Leer Fisicoquimico</button>
            </div>
            <div class="ia-ensayo-status" style="font-size:10px;color:#666;margin-top:2px;"></div>
        </div>`;

    return '<div class="sub-block" style="background:#fff8f3;"><div class="sub-block-title">Ensayos de laboratorio (trimestral Res 234/2026)</div>' +
           iaHtml +
           '<div class="small-label mb-1">Microbiologico</div>' + microHtml +
           '<hr style="margin:6px 0;"><div class="small-label mb-1">Fisico-quimico</div>' + fqHtml + '</div>';
}

function buildPiscinaRow(num, data, paramsData, ensayosData) {
    data = data || {};
    const esNueva = !data.id;
    const idInput = esNueva ? '' : '<input type="hidden" name="item_id[]" value="' + data.id + '">';

    // Enums básicos
    const tipoOpts = ['ADULTOS','NINOS','JACUZZI','CHAPOTEADERO','OTRA'].map(t =>
        '<option value="' + t + '" ' + ((data.tipo||'ADULTOS') === t ? 'selected' : '') + '>' + t + '</option>').join('');
    const usoOpts = [['COLECTIVO_PUBLICO','Colectivo publico'],['RESTRINGIDO','Uso restringido']].map(([v,l]) =>
        '<option value="' + v + '" ' + ((data.uso||'RESTRINGIDO') === v ? 'selected' : '') + '>' + l + '</option>').join('');
    const climaOpts = ['NO','SI'].map(v =>
        '<option value="' + v + '" ' + ((data.climatizada||'NO') === v ? 'selected' : '') + '>' + v + '</option>').join('');
    const perfilOpts = ['UNIFORME','VARIABLE'].map(v =>
        '<option value="' + v + '" ' + ((data.perfil_profundidad||'UNIFORME') === v ? 'selected' : '') + '>' + v + '</option>').join('');
    const botiqOpts = ['NINGUNO','A','B','C'].map(v =>
        '<option value="' + v + '" ' + ((data.botiquin_tipo||'NINGUNO') === v ? 'selected' : '') + '>' + v + '</option>').join('');

    // Header
    let html = '<div class="piscina-row" data-num="' + num + '">' + idInput +
        '<div class="d-flex justify-content-between"><div class="piscina-title">Piscina #' + num + '</div>' +
        '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-piscina" title="Eliminar"><i class="fas fa-trash"></i></button></div>';

    // Identificación
    html += '<div class="sub-block"><div class="sub-block-title">Identificacion</div><div class="row g-1">' +
        '<div class="col-6 col-md-4"><label class="small-label">Identificador</label><input type="text" name="item_identificador[]" class="form-control form-control-sm" value="' + (data.identificador || '').replace(/"/g, '&quot;') + '" required></div>' +
        '<div class="col-6 col-md-2"><label class="small-label">Tipo</label><select name="item_tipo[]" class="form-select form-select-sm">' + tipoOpts + '</select></div>' +
        '<div class="col-6 col-md-3"><label class="small-label">Uso</label><select name="item_uso[]" class="form-select form-select-sm">' + usoOpts + '</select></div>' +
        '<div class="col-3 col-md-1"><label class="small-label">Climat.</label><select name="item_climatizada[]" class="form-select form-select-sm">' + climaOpts + '</select></div>' +
        '<div class="col-3 col-md-1"><label class="small-label">m²</label><input type="number" step="0.01" name="item_superficie_piscina_m2[]" class="form-control form-control-sm" value="' + (data.superficie_piscina_m2 || '') + '"></div>' +
        '<div class="col-3 col-md-1"><label class="small-label">m³</label><input type="number" step="0.01" name="item_volumen_agua_m3[]" class="form-control form-control-sm" value="' + (data.volumen_agua_m3 || '') + '"></div>' +
        '<div class="col-3 col-md-2"><label class="small-label">Perfil prof.</label><select name="item_perfil_profundidad[]" class="form-select form-select-sm">' + perfilOpts + '</select></div>' +
        '<div class="col-3 col-md-2"><label class="small-label">Prof. max (m)</label><input type="number" step="0.01" name="item_profundidad_max_m[]" class="form-control form-control-sm" value="' + (data.profundidad_max_m || '') + '"></div>' +
        '<div class="col-3 col-md-2"><label class="small-label">Prof. min (m)</label><input type="number" step="0.01" name="item_profundidad_min_m[]" class="form-control form-control-sm" value="' + (data.profundidad_min_m || '') + '"></div>' +
        '<div class="col-3 col-md-1"><label class="small-label">Aforo pisc.</label><input type="number" min="0" name="item_aforo_piscina_max[]" class="form-control form-control-sm" value="' + (data.aforo_piscina_max || '') + '"></div>' +
        '<div class="col-3 col-md-1"><label class="small-label">Aforo deck</label><input type="number" min="0" name="item_aforo_deck_max[]" class="form-control form-control-sm" value="' + (data.aforo_deck_max || '') + '"></div>' +
        '</div></div>';

    // Grupos SI/NO/NA
    Object.keys(GRUPOS).forEach(k => {
        html += buildGrupo(k, GRUPOS[k], data);
    });

    // Higiene numéricos
    html += '<div class="sub-block"><div class="sub-block-title">Cubiculos de ducha</div><div class="row g-1">' +
        '<div class="col-6"><label class="small-label">Cubiculos mujeres</label><input type="number" min="0" name="item_cubiculos_duchas_mujeres[]" class="form-control form-control-sm" value="' + (data.cubiculos_duchas_mujeres || 0) + '"></div>' +
        '<div class="col-6"><label class="small-label">Cubiculos hombres</label><input type="number" min="0" name="item_cubiculos_duchas_hombres[]" class="form-control form-control-sm" value="' + (data.cubiculos_duchas_hombres || 0) + '"></div>' +
        '</div></div>';

    // Botiquín tipo
    html += '<div class="sub-block"><div class="sub-block-title">Botiquin (Anexo III Res 234)</div><div class="row g-1">' +
        '<div class="col-6"><label class="small-label">Tipo</label><select name="item_botiquin_tipo[]" class="form-select form-select-sm">' + botiqOpts + '</select></div>' +
        '<div class="col-6"><div class="small-label">A&lt;500m² · B 500-2000 · C &gt;2000</div></div>' +
        '</div></div>';

    // Libro fecha
    html += '<div class="sub-block"><div class="sub-block-title">Libro diario</div><div class="row g-1">' +
        '<div class="col-4"><label class="small-label">Fecha ultima semana</label><input type="date" name="item_libro_ultima_semana_fecha[]" class="form-control form-control-sm" value="' + (data.libro_ultima_semana_fecha || '') + '"></div>' +
        '<div class="col-8"><label class="small-label">Obs. libro</label><input type="text" name="item_libro_observaciones[]" class="form-control form-control-sm" value="' + (data.libro_observaciones || '').replace(/"/g, '&quot;') + '"></div>' +
        '</div></div>';

    // Parámetros
    html += buildParametros(paramsData, num);

    // Ensayos
    html += buildEnsayos(ensayosData);

    // Foto principal + observaciones
    const fotoHtml = data.foto
        ? '<div class="mb-1"><img src="' + '<?= base_url('/') ?>' + data.foto + '" style="max-width:120px;max-height:90px;cursor:pointer;" onclick="openPhoto(this.src)"></div>'
        : '';
    html += '<div class="sub-block"><div class="row g-1">' +
        '<div class="col-12 col-md-4">' + fotoHtml + '<label class="small-label"><i class="fas fa-images me-1"></i> Foto principal piscina</label><input type="file" name="item_foto[]" class="form-control form-control-sm" accept="image/*"></div>' +
        '<div class="col-12 col-md-8"><label class="small-label">Observaciones</label><textarea name="item_observaciones[]" class="form-control form-control-sm" rows="2">' + (data.observaciones || '') + '</textarea></div>' +
        '</div></div>';

    // Evidencias multi-foto por piscina (N fotos con categoria libre)
    const idxPisc = (num - 1);
    const evidPiscinaData = (typeof EVIDENCIAS_DET_INIT !== 'undefined' && data.id) ? (EVIDENCIAS_DET_INIT[data.id] || []) : [];
    let thumbsHtml = '';
    if (evidPiscinaData.length > 0) {
        thumbsHtml = '<div class="d-flex flex-wrap gap-2 mb-2" style="border:1px dashed #ccc; padding:6px; border-radius:4px; background:#fafafa;">';
        evidPiscinaData.forEach(e => {
            const src = '<?= base_url('/') ?>' + e.foto_path;
            const cat = (e.categoria || '').replace(/"/g, '&quot;');
            const desc = (e.descripcion || '').replace(/"/g, '&quot;');
            thumbsHtml += '<div class="evidencia-det-thumb" data-id="' + e.id + '" style="position:relative; width:92px; margin-bottom:14px;" title="' + cat + (desc ? ' — ' + desc : '') + '">'
                + '<img src="' + src + '" style="width:92px;height:92px;object-fit:cover;border:1px solid #bbb;border-radius:4px;cursor:pointer;" onclick="openPhoto(\'' + src + '\')">'
                + '<div style="font-size:9px;color:#444;line-height:1.1;text-align:center;margin-top:2px;"><strong>' + cat + '</strong>' + (desc ? '<br><em>' + desc + '</em>' : '') + '</div>'
                + '<button type="button" class="btn-remove-evidencia-det" data-id="' + e.id + '" title="Eliminar" style="position:absolute;top:-8px;right:-8px;width:20px;height:20px;border-radius:50%;background:#c0392b;color:#fff;border:none;font-size:11px;line-height:18px;padding:0;cursor:pointer;">×</button>'
                + '</div>';
        });
        thumbsHtml += '</div>';
    }
    const listaId = 'catEvidList_' + idxPisc;
    // 6 SLOTS FIJOS (3x2 en desktop, 1 por fila en movil). Cada slot = card con
    // foto + categoria (datalist libre) + descripcion opcional.
    let slotsHtml = '<div class="row g-2">';
    for (let s = 0; s < 6; s++) {
        slotsHtml +=
              '<div class="col-12 col-md-6 col-lg-4">'
            + '  <div style="border:1px solid #d0d3d7; border-radius:6px; padding:6px; background:#fff; height:100%;">'
            + '    <div class="small-label">Slot #' + (s + 1) + '</div>'
            + '    <label class="small-label"><i class="fas fa-images me-1"></i>Foto</label>'
            + '    <input type="file" name="item_evidencia_' + idxPisc + '[]" class="form-control form-control-sm" accept="image/*" data-multi-name="1">'
            + '    <label class="small-label mt-1">Categoria</label>'
            + '    <input type="text" name="item_evidencia_categoria_' + idxPisc + '[]" class="form-control form-control-sm" list="' + listaId + '" placeholder="Ej: Drenaje">'
            + '    <label class="small-label mt-1">Descripcion</label>'
            + '    <input type="text" name="item_evidencia_descripcion_' + idxPisc + '[]" class="form-control form-control-sm" placeholder="Hallazgo / detalle (opcional)">'
            + '  </div>'
            + '</div>';
    }
    slotsHtml += '</div>';

    html += '<div class="sub-block evid-det-block" style="background:#f5f5fa;" data-idx="' + idxPisc + '">'
        + '<div class="sub-block-title">Evidencias adicionales de esta piscina</div>'
        + thumbsHtml
        + '<datalist id="' + listaId + '">'
        +   '<option value="Infraestructura"><option value="Avisos"><option value="Emergencia">'
        +   '<option value="Higiene"><option value="Agua"><option value="Cerramientos">'
        +   '<option value="Drenajes"><option value="Escaleras"><option value="Lavapies">'
        +   '<option value="Senalizacion"><option value="Iluminacion"><option value="Ventilacion">'
        +   '<option value="Equipos"><option value="Cuarto bombas"><option value="Otra">'
        + '</datalist>'
        + '<div class="form-text mb-1" style="font-size:10px;">6 slots por piscina. Llena los que necesites, los vacios se ignoran. Para agregar mas fotos guarda y vuelve a entrar.</div>'
        + slotsHtml
        + '</div>';

    html += '</div>'; // /piscina-row
    return html;
}

function renumerarPiscinas() {
    const rows = document.querySelectorAll('.piscina-row');
    rows.forEach((r, i) => {
        r.setAttribute('data-num', i + 1);
        const t = r.querySelector('.piscina-title');
        if (t) t.textContent = 'Piscina #' + (i + 1);
    });
    document.getElementById('countPis').textContent = rows.length;
    document.getElementById('totalPiscinasInput').value = rows.length;
}

// Preview en vivo de cualquier input type="file" del form (delegado, funciona con inputs dinamicos).
document.addEventListener('change', function(e) {
    const input = e.target;
    if (!(input && input.tagName === 'INPUT' && input.type === 'file')) return;
    if (!input.files || input.files.length === 0) return;

    // Solo preview para imagenes (no PDF u otros)
    const file = input.files[0];
    if (!file.type.startsWith('image/')) return;

    // Busca o crea el contenedor de preview ANTES del input
    let preview = input.previousElementSibling;
    const isPreview = preview && preview.classList && preview.classList.contains('file-live-preview');
    if (!isPreview) {
        preview = document.createElement('div');
        preview.className = 'file-live-preview';
        preview.style.cssText = 'margin: 2px 0 4px 0;';
        input.parentNode.insertBefore(preview, input);
    }

    // Revocar URL previo si existe para liberar memoria
    const existingImg = preview.querySelector('img');
    if (existingImg && existingImg.src.startsWith('blob:')) {
        URL.revokeObjectURL(existingImg.src);
    }

    const url = URL.createObjectURL(file);
    const nombreCorto = file.name.length > 28 ? file.name.slice(0, 25) + '...' : file.name;
    preview.innerHTML = '<img src="' + url + '" style="max-width:110px;max-height:85px;border:2px solid #1b7e3f;border-radius:4px;display:block;margin-bottom:2px;"><span style="font-size:10px;color:#1b7e3f;"><i class="fas fa-check"></i> ' + nombreCorto + '</span>';
});

document.addEventListener('DOMContentLoaded', function() {
    // --- Eliminar evidencia maestro (marca ID y oculta la miniatura) ---
    document.querySelectorAll('.btn-remove-evidencia').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = parseInt(this.dataset.id, 10);
            if (!id) return;
            if (!confirm('Eliminar esta foto al guardar?')) return;
            // Agregar input hidden con el ID
            const holder = document.getElementById('evidenciasBorrarHolder');
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'evidencia_borrar_ids[]';
            inp.value = id;
            holder.appendChild(inp);
            // Ocultar la miniatura con efecto
            const thumb = this.closest('.evidencia-thumb');
            if (thumb) {
                thumb.style.opacity = '0.3';
                thumb.style.pointerEvents = 'none';
                thumb.setAttribute('title', 'Se eliminara al guardar');
            }
        });
    });

    const container = document.getElementById('piscinasContainer');
    if (PISCINAS_INIT.length > 0) {
        PISCINAS_INIT.forEach((p, i) => {
            const idDet = p.id;
            container.insertAdjacentHTML('beforeend', buildPiscinaRow(i + 1, p, PARAMETROS_INIT[idDet] || [], ENSAYOS_INIT[idDet] || []));
            // convertir array de paramsData a mapa clave->{valor,observaciones}
            const arr = PARAMETROS_INIT[idDet] || [];
            const map = {};
            arr.forEach(x => map[x.parametro] = { valor: x.valor, observaciones: x.observaciones });
            // re-render params con el mapa
            const row = container.lastElementChild;
            const paramBlock = row.querySelector('.sub-block:last-of-type').previousElementSibling;
            // simplificación: ya los renderizamos arriba con el array, no hace falta re-render
        });
    } else {
        container.insertAdjacentHTML('beforeend', buildPiscinaRow(1, {}, [], []));
    }

    document.getElementById('btnAddPiscina').addEventListener('click', () => {
        const n = document.querySelectorAll('.piscina-row').length + 1;
        container.insertAdjacentHTML('beforeend', buildPiscinaRow(n, {}, [], []));
    });

    // --- IA: leer PDF de ensayo y rellenar campos ---
    const URL_IA_ENSAYO = '<?= base_url('/inspecciones/piscinas/extraer-ensayo-ia') ?>';
    const CSRF_NAME_PIS = '<?= csrf_token() ?>';
    const CSRF_HASH_PIS = '<?= csrf_hash() ?>';

    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.ia-ensayo-btn');
        if (!btn) return;
        const tipo = btn.dataset.tipo;
        const subBlock = btn.closest('.sub-block');
        const fileInput = subBlock.querySelector('.ia-ensayo-file');
        const statusEl = subBlock.querySelector('.ia-ensayo-status');
        if (!fileInput.files || !fileInput.files[0]) {
            statusEl.textContent = 'Adjunta primero el PDF.';
            statusEl.style.color = '#c0392b';
            return;
        }
        const file = fileInput.files[0];

        const fd = new FormData();
        fd.append('tipo', tipo);
        fd.append('pdf', file);
        fd.append(CSRF_NAME_PIS, CSRF_HASH_PIS);

        btn.disabled = true;
        statusEl.style.color = '#666';
        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Leyendo PDF con IA...';

        try {
            const resp = await fetch(URL_IA_ENSAYO, { method: 'POST', body: fd });
            const json = await resp.json();
            if (!json.ok) throw new Error(json.error || 'Error IA');

            const d = json.data || {};
            const piscinaRow = subBlock.closest('.piscina-row');
            if (!piscinaRow) throw new Error('No se pudo ubicar la fila de piscina');

            // Mapa de campo -> nombre del input en el DOM del bloque ensayo
            const prefix = tipo === 'MICROBIOLOGICO' ? 'ensayo_microbiologico_' : 'ensayo_fisicoquimico_';
            const mapa = {
                'fecha_toma':       prefix + 'fecha',
                'laboratorio':      prefix + 'lab',
                'numero_informe':   prefix + 'informe',
                'norma_citada':     prefix + 'norma',
                'conforme_global':  prefix + 'conforme',
                'observaciones':    prefix + 'obs',
            };
            if (tipo === 'MICROBIOLOGICO') {
                Object.assign(mapa, {
                    'heterotrofos_ufc': 'ensayo_microbiologico_heterotrofos',
                    'coliformes_termotolerantes_ufc': 'ensayo_microbiologico_coliformes',
                    'ecoli_ufc': 'ensayo_microbiologico_ecoli',
                    'pseudomonas_ufc': 'ensayo_microbiologico_pseudomonas',
                    'legionella_ufc': 'ensayo_microbiologico_legionella',
                });
            }
            let llenados = 0;
            for (const [srcKey, targetName] of Object.entries(mapa)) {
                const v = d[srcKey];
                if (v === undefined || v === null || v === '') continue;
                const el = piscinaRow.querySelector('[name="' + targetName + '[]"]');
                if (el) { el.value = v; llenados++; }
            }

            statusEl.style.color = '#1b7e3f';
            statusEl.innerHTML = '<i class="fas fa-check"></i> ' + llenados + ' campos llenados por IA. Revisa y ajusta.';
        } catch (err) {
            statusEl.style.color = '#c0392b';
            statusEl.innerHTML = '<i class="fas fa-xmark"></i> ' + err.message;
        } finally {
            btn.disabled = false;
        }
    });

    container.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-piscina')) {
            e.target.closest('.piscina-row').remove();
            renumerarPiscinas();
        }
        const btnDet = e.target.closest('.btn-remove-evidencia-det');
        if (btnDet) {
            const id = parseInt(btnDet.dataset.id, 10);
            if (!id) return;
            if (!confirm('Eliminar esta foto al guardar?')) return;
            const holder = document.getElementById('evidenciasDetBorrarHolder');
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'detalle_evidencia_borrar_ids[]';
            inp.value = id;
            holder.appendChild(inp);
            const thumb = btnDet.closest('.evidencia-det-thumb');
            if (thumb) {
                thumb.style.opacity = '0.3';
                thumb.style.pointerEvents = 'none';
                thumb.setAttribute('title', 'Se eliminara al guardar');
            }
        }
    });

    // Select2 clientes via AJAX
    const clienteIdPrefill = '<?= $inspeccion['id_cliente'] ?? $idCliente ?? '' ?>';
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteIdPrefill && c.id_cliente == clienteIdPrefill) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        }
    });

    // Finalizar con confirmación
    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        if (!document.getElementById('selectCliente').value) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', confirmButtonColor: '#bd9751' });
            return;
        }
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar inspeccion?', html: 'Se generara el PDF y no podras editar despues.',
            icon: 'question', showCancelButton: true, confirmButtonText: 'Si, finalizar',
            cancelButtonText: 'Cancelar', confirmButtonColor: '#bd9751',
        }).then(result => {
            if (result.isConfirmed) {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'finalizar'; inp.value = '1';
                document.getElementById('pisForm').appendChild(inp);
                document.getElementById('pisForm').submit();
            }
        });
    });

    // Autosave (trait del controlador)
    if (typeof initAutosave === 'function') {
        initAutosave({
            formId: 'pisForm',
            storeUrl: '<?= base_url('/inspecciones/piscinas/store') ?>',
            updateUrlBase: '<?= base_url('/inspecciones/piscinas/update/') ?>',
            editUrlBase: '<?= base_url('/inspecciones/piscinas/edit/') ?>',
            recordId: <?= $inspeccion['id'] ?? 'null' ?>,
            isEdit: <?= $isEdit ? 'true' : 'false' ?>,
            storageKey: 'pis_draft_<?= $inspeccion['id'] ?? 'new' ?>',
            detailRowSelector: '.piscina-row',
            detailIdInputName: 'item_id[]',
            intervalSeconds: 60,
        });
    }
});

function openPhoto(src) {
    const m = document.getElementById('photoModal');
    if (!m) return;
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(m).show();
}
</script>

<div class="modal fade" id="photoModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content bg-dark"><div class="modal-body p-1 text-center"><img id="photoModalImg" src="" class="img-fluid" style="max-height:80vh;"></div></div></div></div>
