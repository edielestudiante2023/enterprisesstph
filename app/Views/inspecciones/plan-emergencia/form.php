<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/plan-emergencia/update/' . $inspeccion['id'] : '/inspecciones/plan-emergencia/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="planEmgForm" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div id="autoSaveStatus" class="text-end" style="font-size:11px; color:#999; min-height:16px;"></div>

        <!-- 1. DATOS GENERALES -->
        <div class="card mt-2 mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
                <div class="mb-3">
                    <label class="form-label">Cliente *</label>
                    <select name="id_cliente" id="selectCliente" class="form-select" required>
                        <option value="">Seleccionar cliente...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de visita *</label>
                    <input type="date" name="fecha_visita" class="form-control"
                        value="<?= $inspeccion['fecha_visita'] ?? date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>

        <!-- 2. FACHADA Y PANORAMA -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">FACHADA Y PANORAMA</h6>
                <?php foreach (['foto_fachada' => 'Foto Fachada del Conjunto', 'foto_panorama' => 'Vista de Panorama'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                    <div class="mb-1"><img src="/<?= esc($inspeccion[$campo]) ?>" class="img-thumbnail" style="max-height:100px;"></div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i> Camara</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. DESCRIPCION DEL INMUEBLE -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">DESCRIPCION DEL INMUEBLE</h6>
                <div class="mb-3">
                    <label class="form-label">Tipo de inmueble</label>
                    <select name="casas_o_apartamentos" id="casasApartamentos" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="casas" <?= ($inspeccion['casas_o_apartamentos'] ?? '') === 'casas' ? 'selected' : '' ?>>Casas</option>
                        <option value="apartamentos" <?= ($inspeccion['casas_o_apartamentos'] ?? '') === 'apartamentos' ? 'selected' : '' ?>>Apartamentos</option>
                    </select>
                </div>
                <div class="mb-3" id="divTorres" style="display:none;">
                    <label class="form-label">Numero de torres</label>
                    <input type="number" name="numero_torres" class="form-control" min="0" value="<?= $inspeccion['numero_torres'] ?? '' ?>">
                </div>
                <div class="mb-3" id="divCasasPisos" style="display:none;">
                    <label class="form-label">Casas de cuantos pisos</label>
                    <input type="text" name="casas_pisos" class="form-control" value="<?= esc($inspeccion['casas_pisos'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">La estructura es sismo resistente?</label>
                    <input type="text" name="sismo_resistente" class="form-control" value="<?= esc($inspeccion['sismo_resistente'] ?? '') ?>" placeholder="SI / NO / Detalle">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ano de construccion</label>
                    <input type="number" name="anio_construccion" class="form-control" min="1900" max="2100" value="<?= $inspeccion['anio_construccion'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Numero total de unidades habitacionales</label>
                    <input type="number" name="numero_unidades_habitacionales" class="form-control" min="0" value="<?= $inspeccion['numero_unidades_habitacionales'] ?? '' ?>">
                </div>
                <?php foreach (['foto_torres_1' => 'Foto 1 de Torres o Casas', 'foto_torres_2' => 'Foto 2 de Torres o Casas'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                    <div class="mb-1"><img src="/<?= esc($inspeccion[$campo]) ?>" class="img-thumbnail" style="max-height:80px;"></div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4. PARQUEADEROS -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">PARQUEADEROS</h6>
                <?php
                $parkFields = [
                    'parqueaderos_carros_residentes' => 'Parqueaderos carros residentes',
                    'parqueaderos_carros_visitantes' => 'Parqueaderos carros visitantes',
                    'parqueaderos_motos_residentes'  => 'Parqueaderos motos residentes',
                    'parqueaderos_motos_visitantes'  => 'Parqueaderos motos visitantes',
                ];
                foreach ($parkFields as $campo => $label): ?>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <input type="number" name="<?= $campo ?>" class="form-control form-control-sm" min="0" value="<?= $inspeccion[$campo] ?? '0' ?>">
                </div>
                <?php endforeach; ?>
                <div class="mb-3">
                    <label class="form-label">Hay propietarios con parqueadero privado?</label>
                    <select name="hay_parqueadero_privado" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="si" <?= ($inspeccion['hay_parqueadero_privado'] ?? '') === 'si' ? 'selected' : '' ?>>SI</option>
                        <option value="no" <?= ($inspeccion['hay_parqueadero_privado'] ?? '') === 'no' ? 'selected' : '' ?>>NO</option>
                    </select>
                </div>
                <?php foreach (['foto_parqueaderos_carros' => 'Foto Parqueaderos Carros', 'foto_parqueaderos_motos' => 'Foto Parqueaderos Motos'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                    <div class="mb-1"><img src="/<?= esc($inspeccion[$campo]) ?>" class="img-thumbnail" style="max-height:80px;"></div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 5. AREAS COMUNES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">AREAS COMUNES</h6>
                <div class="mb-2">
                    <label class="form-label">Cantidad de salones comunales</label>
                    <input type="number" name="cantidad_salones_comunales" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_salones_comunales'] ?? '0' ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Cantidad de locales comerciales</label>
                    <input type="number" name="cantidad_locales_comerciales" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_locales_comerciales'] ?? '0' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tiene oficina de administracion?</label>
                    <select name="tiene_oficina_admin" id="tieneOficinaAdmin" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="si" <?= ($inspeccion['tiene_oficina_admin'] ?? '') === 'si' ? 'selected' : '' ?>>SI</option>
                        <option value="no" <?= ($inspeccion['tiene_oficina_admin'] ?? '') === 'no' ? 'selected' : '' ?>>NO</option>
                    </select>
                </div>
                <div id="divFotoOficina" style="display:none;">
                    <label class="form-label" style="font-size:13px;">Foto Oficina de Administracion</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_oficina_admin'])): ?>
                    <div class="mb-1"><img src="/<?= esc($inspeccion['foto_oficina_admin']) ?>" class="img-thumbnail" style="max-height:80px;"></div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="foto_oficina_admin" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. SERVICIOS DEL CONJUNTO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">SERVICIOS DEL CONJUNTO</h6>
                <div class="mb-3">
                    <label class="form-label">Cuenta con tanque de almacenamiento de agua? Detalle</label>
                    <textarea name="tanque_agua" class="form-control" rows="2"><?= esc($inspeccion['tanque_agua'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cuenta con planta electrica? Detalle</label>
                    <textarea name="planta_electrica" class="form-control" rows="2"><?= esc($inspeccion['planta_electrica'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 7-11. CIRCULACIONES Y ACCESOS -->
        <?php
        $circulaciones = [
            ['titulo' => 'CIRCULACION VEHICULAR', 'campo_texto' => 'circulacion_vehicular', 'label_texto' => 'Describa las zonas de circulacion vehicular', 'fotos' => ['foto_circulacion_vehicular' => 'Foto Zona de Circulacion Vehicular']],
            ['titulo' => 'CIRCULACION PEATONAL', 'campo_texto' => 'circulacion_peatonal', 'label_texto' => 'Describa las zonas de circulacion peatonal', 'fotos' => ['foto_circulacion_peatonal_1' => 'Foto 1 Circulacion Peatonal', 'foto_circulacion_peatonal_2' => 'Foto 2 Circulacion Peatonal']],
            ['titulo' => 'SALIDAS DE EMERGENCIA', 'campo_texto' => 'salidas_emergencia', 'label_texto' => 'Cuantas salidas de emergencia tiene la copropiedad y cuales son', 'fotos' => ['foto_salida_emergencia_1' => 'Foto 1 Salida Emergencia', 'foto_salida_emergencia_2' => 'Foto 2 Salida Emergencia']],
            ['titulo' => 'INGRESOS PEATONALES', 'campo_texto' => 'ingresos_peatonales', 'label_texto' => 'Describa los ingresos peatonales', 'fotos' => ['foto_ingresos_peatonales' => 'Foto Ingresos Peatonales']],
            ['titulo' => 'ACCESOS VEHICULARES', 'campo_texto' => 'accesos_vehiculares', 'label_texto' => 'Describa los accesos vehiculares', 'fotos' => ['foto_acceso_vehicular_1' => 'Foto 1 Acceso Vehicular', 'foto_acceso_vehicular_2' => 'Foto 2 Acceso Vehicular']],
        ];
        foreach ($circulaciones as $circ): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;"><?= $circ['titulo'] ?></h6>
                <div class="mb-3">
                    <label class="form-label"><?= $circ['label_texto'] ?></label>
                    <textarea name="<?= $circ['campo_texto'] ?>" class="form-control" rows="3"><?= esc($inspeccion[$circ['campo_texto']] ?? '') ?></textarea>
                </div>
                <?php foreach ($circ['fotos'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                    <div class="mb-1"><img src="/<?= esc($inspeccion[$campo]) ?>" class="img-thumbnail" style="max-height:80px;"></div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- 12. CONCEPTO DEL CONSULTOR -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CONCEPTO DEL CONSULTOR</h6>
                <div class="mb-3">
                    <label class="form-label">Concepto de las entradas y salidas ante una emergencia</label>
                    <textarea name="concepto_entradas_salidas" class="form-control" rows="3"><?= esc($inspeccion['concepto_entradas_salidas'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Describa al respecto de los hidrantes (cantidad y ubicacion)</label>
                    <textarea name="hidrantes" class="form-control" rows="2"><?= esc($inspeccion['hidrantes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 13. ENTORNO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">ENTORNO</h6>
                <div class="mb-3">
                    <label class="form-label">Cual es el CAI mas cercano</label>
                    <input type="text" name="cai_cercano" class="form-control" value="<?= esc($inspeccion['cai_cercano'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Cual es la estacion de bomberos mas cercana</label>
                    <input type="text" name="bomberos_cercanos" class="form-control" value="<?= esc($inspeccion['bomberos_cercanos'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- 14. PROVEEDORES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">PROVEEDORES</h6>
                <div class="mb-3">
                    <label class="form-label">Proveedor de vigilancia con NIT</label>
                    <input type="text" name="proveedor_vigilancia" class="form-control" value="<?= esc($inspeccion['proveedor_vigilancia'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Proveedor de aseo con NIT</label>
                    <input type="text" name="proveedor_aseo" class="form-control" value="<?= esc($inspeccion['proveedor_aseo'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Otros proveedores de relevancia</label>
                    <textarea name="otros_proveedores" class="form-control" rows="2"><?= esc($inspeccion['otros_proveedores'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 15. CONTROL DE VISITANTES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CONTROL DE VISITANTES</h6>
                <div class="mb-3">
                    <label class="form-label">Describa la forma en como se registran los visitantes</label>
                    <textarea name="registro_visitantes_forma" class="form-control" rows="2"><?= esc($inspeccion['registro_visitantes_forma'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">El registro de visitantes permite saber cuantas personas hay en el conjunto en caso de emergencia?</label>
                    <select name="registro_visitantes_emergencia" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="si" <?= ($inspeccion['registro_visitantes_emergencia'] ?? '') === 'si' ? 'selected' : '' ?>>SI</option>
                        <option value="no" <?= ($inspeccion['registro_visitantes_emergencia'] ?? '') === 'no' ? 'selected' : '' ?>>NO</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 16. MEGAFONO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">COMUNICACIONES</h6>
                <div class="mb-3">
                    <label class="form-label">Cuenta con megafono el conjunto?</label>
                    <select name="cuenta_megafono" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="si" <?= ($inspeccion['cuenta_megafono'] ?? '') === 'si' ? 'selected' : '' ?>>SI</option>
                        <option value="no" <?= ($inspeccion['cuenta_megafono'] ?? '') === 'no' ? 'selected' : '' ?>>NO</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 17. RUTA DE EVACUACION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">RUTA DE EVACUACION</h6>
                <div class="mb-3">
                    <label class="form-label">Describa el estado de la ruta de evacuacion</label>
                    <textarea name="ruta_evacuacion" class="form-control" rows="3"><?= esc($inspeccion['ruta_evacuacion'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Detalle mapa de evacuacion</label>
                    <textarea name="mapa_evacuacion" class="form-control" rows="2"><?= esc($inspeccion['mapa_evacuacion'] ?? '') ?></textarea>
                </div>
                <?php foreach (['foto_ruta_evacuacion_1' => 'Foto 1 Ruta de Evacuacion', 'foto_ruta_evacuacion_2' => 'Foto 2 Ruta de Evacuacion'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                    <div class="mb-1"><img src="/<?= esc($inspeccion[$campo]) ?>" class="img-thumbnail" style="max-height:80px;"></div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 18. PUNTOS DE ENCUENTRO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">PUNTOS DE ENCUENTRO</h6>
                <div class="mb-3">
                    <label class="form-label">Describa los puntos de encuentro</label>
                    <textarea name="puntos_encuentro" class="form-control" rows="3"><?= esc($inspeccion['puntos_encuentro'] ?? '') ?></textarea>
                </div>
                <?php foreach (['foto_punto_encuentro_1' => 'Foto 1 Punto de Encuentro', 'foto_punto_encuentro_2' => 'Foto 2 Punto de Encuentro'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                    <div class="mb-1"><img src="/<?= esc($inspeccion[$campo]) ?>" class="img-thumbnail" style="max-height:80px;"></div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 19-20. SISTEMA DE ALARMA Y EMERGENCIA -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">SISTEMAS DE ALARMA Y EMERGENCIA</h6>
                <div class="mb-3">
                    <label class="form-label">Describa el sistema de alarma</label>
                    <textarea name="sistema_alarma" class="form-control" rows="2"><?= esc($inspeccion['sistema_alarma'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Describa los codigos de alerta y alarma</label>
                    <textarea name="codigos_alerta" class="form-control" rows="2"><?= esc($inspeccion['codigos_alerta'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Energia de emergencia y luminarias</label>
                    <textarea name="energia_emergencia" class="form-control" rows="2"><?= esc($inspeccion['energia_emergencia'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sistemas de deteccion de fuego</label>
                    <textarea name="deteccion_fuego" class="form-control" rows="2"><?= esc($inspeccion['deteccion_fuego'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cuales son las vias de transito cercanas</label>
                    <textarea name="vias_transito" class="form-control" rows="2"><?= esc($inspeccion['vias_transito'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 22. ADMINISTRACION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">ADMINISTRACION</h6>
                <div class="mb-3">
                    <label class="form-label">Nombre del administrador</label>
                    <input type="text" name="nombre_administrador" class="form-control" value="<?= esc($inspeccion['nombre_administrador'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Horarios de administracion</label>
                    <input type="text" name="horarios_administracion" class="form-control" value="<?= esc($inspeccion['horarios_administracion'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Personal de aseo</label>
                    <textarea name="personal_aseo" class="form-control" rows="2"><?= esc($inspeccion['personal_aseo'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Personal de vigilancia</label>
                    <textarea name="personal_vigilancia" class="form-control" rows="2"><?= esc($inspeccion['personal_vigilancia'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 23. TELEFONOS DE EMERGENCIA -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">TELEFONOS DE EMERGENCIA</h6>
                <div class="mb-3">
                    <label class="form-label">Ciudad del conjunto *</label>
                    <select name="ciudad" id="selectCiudad" class="form-select">
                        <option value="">Seleccionar ciudad...</option>
                        <option value="bogota" <?= ($inspeccion['ciudad'] ?? '') === 'bogota' ? 'selected' : '' ?>>Bogota</option>
                        <option value="soacha" <?= ($inspeccion['ciudad'] ?? '') === 'soacha' ? 'selected' : '' ?>>Soacha</option>
                    </select>
                </div>
                <div id="tablaTelefonos" style="display:none;">
                    <table class="table table-sm" style="font-size:13px;">
                        <thead><tr><th>Entidad</th><th>Telefono</th></tr></thead>
                        <tbody id="tbodyTelefonos"></tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cuadrante de policia</label>
                    <input type="text" name="cuadrante" class="form-control" value="<?= esc($inspeccion['cuadrante'] ?? '') ?>" placeholder="Ej: 3004724337">
                </div>
            </div>
        </div>

        <!-- 24. GABINETES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">GABINETES CONTRA INCENDIO</h6>
                <div class="mb-3">
                    <label class="form-label">Tiene gabinetes con punto hidraulico?</label>
                    <select name="tiene_gabinetes_hidraulico" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="si" <?= ($inspeccion['tiene_gabinetes_hidraulico'] ?? '') === 'si' ? 'selected' : '' ?>>SI</option>
                        <option value="no" <?= ($inspeccion['tiene_gabinetes_hidraulico'] ?? '') === 'no' ? 'selected' : '' ?>>NO</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 25. SERVICIOS GENERALES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">SERVICIOS GENERALES</h6>
                <div class="mb-3">
                    <label class="form-label">Ruta de evacuacion de residuos solidos</label>
                    <textarea name="ruta_residuos_solidos" class="form-control" rows="2"><?= esc($inspeccion['ruta_residuos_solidos'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Empresa de aseo</label>
                    <select name="empresa_aseo" class="form-select">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($empresasAseo as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($inspeccion['empresa_aseo'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Servicios sanitarios</label>
                    <textarea name="servicios_sanitarios" class="form-control" rows="2"><?= esc($inspeccion['servicios_sanitarios'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Frecuencia de recoleccion de basura</label>
                    <input type="text" name="frecuencia_basura" class="form-control" value="<?= esc($inspeccion['frecuencia_basura'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Detalle mascotas</label>
                    <textarea name="detalle_mascotas" class="form-control" rows="2"><?= esc($inspeccion['detalle_mascotas'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Detalle dependencias</label>
                    <textarea name="detalle_dependencias" class="form-control" rows="2"><?= esc($inspeccion['detalle_dependencias'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 26. OBSERVACIONES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES Y RECOMENDACIONES</h6>
                <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones generales..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-outline flex-fill">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary flex-fill"
                onclick="return confirm('Finalizar Plan de Emergencia? Se generara el PDF y no podra editarse.')">
                <i class="fas fa-check-circle"></i> Finalizar
            </button>
        </div>
    </form>
</div>

<!-- Modal foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-1 text-center">
                <img id="photoModalImg" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedCliente = '<?= $idCliente ?? '' ?>';

    // Cargar clientes
    $.ajax({
        url: '/inspecciones/api/clientes',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (c.id_cliente == selectedCliente) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        }
    });

    // Telefonos de emergencia
    const telefonos = <?= json_encode($telefonos) ?>;

    function actualizarTelefonos() {
        const ciudad = document.getElementById('selectCiudad').value;
        const tabla = document.getElementById('tablaTelefonos');
        const tbody = document.getElementById('tbodyTelefonos');
        if (!ciudad || !telefonos[ciudad]) {
            tabla.style.display = 'none';
            return;
        }
        tbody.innerHTML = '';
        Object.entries(telefonos[ciudad]).forEach(([entidad, numero]) => {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td><strong>' + entidad + '</strong></td><td>' + numero + '</td>';
            tbody.appendChild(tr);
        });
        tabla.style.display = '';
    }
    document.getElementById('selectCiudad').addEventListener('change', actualizarTelefonos);
    actualizarTelefonos();

    // Condicional: casas vs apartamentos
    function toggleCasasAptos() {
        const val = document.getElementById('casasApartamentos').value;
        document.getElementById('divTorres').style.display = (val === 'apartamentos') ? '' : 'none';
        document.getElementById('divCasasPisos').style.display = (val === 'casas') ? '' : 'none';
    }
    document.getElementById('casasApartamentos').addEventListener('change', toggleCasasAptos);
    toggleCasasAptos();

    // Condicional: oficina admin
    function toggleOficinaAdmin() {
        const val = document.getElementById('tieneOficinaAdmin').value;
        document.getElementById('divFotoOficina').style.display = (val === 'si') ? '' : 'none';
    }
    document.getElementById('tieneOficinaAdmin').addEventListener('change', toggleOficinaAdmin);
    toggleOficinaAdmin();

    // Camera/Gallery buttons
    document.addEventListener('click', function(e) {
        const cameraBtn = e.target.closest('.btn-photo-camera');
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!cameraBtn && !galleryBtn) return;
        const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');
        if (cameraBtn) { input.setAttribute('capture', 'environment'); }
        else { input.removeAttribute('capture'); }
        input.click();
    });

    // File preview
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        const input = e.target;
        const group = input.closest('.photo-input-group');
        const previewDiv = group ? group.querySelector('.preview-img') : null;
        if (!previewDiv) return;
        previewDiv.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:80px; margin-top:4px;">' +
                    '<div style="font-size:11px; color:#28a745;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Photo modal (click en thumbnails existentes)
    document.querySelectorAll('.img-thumbnail').forEach(img => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function() {
            document.getElementById('photoModalImg').src = this.src;
            new bootstrap.Modal(document.getElementById('photoModal')).show();
        });
    });

    // ===== AUTOGUARDADO localStorage =====
    const formId = '<?= $isEdit ? $inspeccion['id'] : 'new' ?>';
    const STORAGE_KEY = 'plan_emg_draft_' + formId;
    const form = document.getElementById('planEmgForm');
    let debounceTimer;

    function saveDraft() {
        const data = {};
        form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea, select').forEach(el => {
            if (el.name && el.type !== 'file') {
                data[el.name] = el.value;
            }
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        document.getElementById('autoSaveStatus').innerHTML =
            '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
    }

    function loadDraft() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;
        try {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(name => {
                const el = form.querySelector('[name="' + name + '"]');
                if (el && el.type !== 'file' && !el.value) {
                    el.value = data[name];
                }
            });
            // Re-trigger conditionals
            toggleCasasAptos();
            toggleOficinaAdmin();
            actualizarTelefonos();
        } catch(e) {}
    }

    form.addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    form.addEventListener('change', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    setInterval(saveDraft, 30000);
    form.addEventListener('submit', () => { localStorage.removeItem(STORAGE_KEY); });

    if (!<?= $isEdit ? 'true' : 'false' ?>) {
        loadDraft();
    }
});
</script>
