<?php
$enumSiNo = ['si' => 'SI', 'no' => 'NO'];
$tipoInmueble = ['casas' => 'Casas', 'apartamentos' => 'Apartamentos'];
$ciudad = $inspeccion['ciudad'] ?? null;
$telefonosCiudad = ($ciudad && isset($telefonos[$ciudad])) ? $telefonos[$ciudad] : [];
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Plan de Emergencia</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha de visita</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_visita'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Fachada y Panorama -->
    <?php
    $fotosFachada = [
        'foto_fachada'  => 'Foto Fachada del Conjunto',
        'foto_panorama' => 'Vista de Panorama',
    ];
    $tieneFotosFachada = false;
    foreach ($fotosFachada as $c => $l) { if (!empty($inspeccion[$c])) { $tieneFotosFachada = true; break; } }
    if ($tieneFotosFachada): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">FACHADA Y PANORAMA</h6>
            <div class="row g-2">
                <?php foreach ($fotosFachada as $campo => $label): ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="col-6">
                    <p class="mb-1" style="font-size:12px; color:#666;"><?= $label ?></p>
                    <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded" style="max-height:140px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Descripcion del Inmueble -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DESCRIPCION DEL INMUEBLE</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:50%;">Tipo de inmueble</td><td><?= $tipoInmueble[$inspeccion['casas_o_apartamentos'] ?? ''] ?? '<em class="text-muted">-</em>' ?></td></tr>
                <?php if (($inspeccion['casas_o_apartamentos'] ?? '') === 'apartamentos'): ?>
                <tr><td class="text-muted">Numero de torres</td><td><?= $inspeccion['numero_torres'] ?? '-' ?></td></tr>
                <?php elseif (($inspeccion['casas_o_apartamentos'] ?? '') === 'casas'): ?>
                <tr><td class="text-muted">Casas de cuantos pisos</td><td><?= esc($inspeccion['casas_pisos'] ?? '-') ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Sismo resistente</td><td><?= esc($inspeccion['sismo_resistente'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Ano de construccion</td><td><?= $inspeccion['anio_construccion'] ?? '-' ?></td></tr>
                <tr><td class="text-muted">Unidades habitacionales</td><td><?= $inspeccion['numero_unidades_habitacionales'] ?? '-' ?></td></tr>
            </table>
            <?php if (!empty($inspeccion['foto_torres_1']) || !empty($inspeccion['foto_torres_2'])): ?>
            <div class="row g-2 mt-2">
                <?php foreach (['foto_torres_1', 'foto_torres_2'] as $f): ?>
                <?php if (!empty($inspeccion[$f])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$f]) ?>" class="img-fluid rounded" style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Parqueaderos -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">PARQUEADEROS</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:60%;">Carros residentes</td><td><?= $inspeccion['parqueaderos_carros_residentes'] ?? '0' ?></td></tr>
                <tr><td class="text-muted">Carros visitantes</td><td><?= $inspeccion['parqueaderos_carros_visitantes'] ?? '0' ?></td></tr>
                <tr><td class="text-muted">Motos residentes</td><td><?= $inspeccion['parqueaderos_motos_residentes'] ?? '0' ?></td></tr>
                <tr><td class="text-muted">Motos visitantes</td><td><?= $inspeccion['parqueaderos_motos_visitantes'] ?? '0' ?></td></tr>
                <tr><td class="text-muted">Parqueadero privado</td><td><?= $enumSiNo[$inspeccion['hay_parqueadero_privado'] ?? ''] ?? '-' ?></td></tr>
            </table>
            <?php if (!empty($inspeccion['foto_parqueaderos_carros']) || !empty($inspeccion['foto_parqueaderos_motos'])): ?>
            <div class="row g-2 mt-2">
                <?php foreach (['foto_parqueaderos_carros' => 'Carros', 'foto_parqueaderos_motos' => 'Motos'] as $f => $lbl): ?>
                <?php if (!empty($inspeccion[$f])): ?>
                <div class="col-6">
                    <p class="mb-1" style="font-size:11px; color:#999;"><?= $lbl ?></p>
                    <img src="/<?= esc($inspeccion[$f]) ?>" class="img-fluid rounded" style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Areas Comunes -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">AREAS COMUNES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:60%;">Salones comunales</td><td><?= $inspeccion['cantidad_salones_comunales'] ?? '0' ?></td></tr>
                <tr><td class="text-muted">Locales comerciales</td><td><?= $inspeccion['cantidad_locales_comerciales'] ?? '0' ?></td></tr>
                <tr><td class="text-muted">Oficina de administracion</td><td><?= $enumSiNo[$inspeccion['tiene_oficina_admin'] ?? ''] ?? '-' ?></td></tr>
            </table>
            <?php if (!empty($inspeccion['foto_oficina_admin'])): ?>
            <div class="mt-2">
                <img src="/<?= esc($inspeccion['foto_oficina_admin']) ?>" class="img-fluid rounded" style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Servicios del Conjunto -->
    <?php if (!empty($inspeccion['tanque_agua']) || !empty($inspeccion['planta_electrica'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">SERVICIOS DEL CONJUNTO</h6>
            <?php if (!empty($inspeccion['tanque_agua'])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;">Tanque de agua</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['tanque_agua'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($inspeccion['planta_electrica'])): ?>
            <div class="mb-0">
                <strong style="font-size:12px; color:#555;">Planta electrica</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['planta_electrica'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Circulaciones y Accesos -->
    <?php
    $secciones = [
        ['titulo' => 'CIRCULACION VEHICULAR', 'campo' => 'circulacion_vehicular', 'fotos' => ['foto_circulacion_vehicular']],
        ['titulo' => 'CIRCULACION PEATONAL', 'campo' => 'circulacion_peatonal', 'fotos' => ['foto_circulacion_peatonal_1', 'foto_circulacion_peatonal_2']],
        ['titulo' => 'SALIDAS DE EMERGENCIA', 'campo' => 'salidas_emergencia', 'fotos' => ['foto_salida_emergencia_1', 'foto_salida_emergencia_2']],
        ['titulo' => 'INGRESOS PEATONALES', 'campo' => 'ingresos_peatonales', 'fotos' => ['foto_ingresos_peatonales']],
        ['titulo' => 'ACCESOS VEHICULARES', 'campo' => 'accesos_vehiculares', 'fotos' => ['foto_acceso_vehicular_1', 'foto_acceso_vehicular_2']],
    ];
    foreach ($secciones as $sec):
        $tieneTexto = !empty($inspeccion[$sec['campo']]);
        $tieneFotos = false;
        foreach ($sec['fotos'] as $f) { if (!empty($inspeccion[$f])) { $tieneFotos = true; break; } }
        if (!$tieneTexto && !$tieneFotos) continue;
    ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;"><?= $sec['titulo'] ?></h6>
            <?php if ($tieneTexto): ?>
            <p style="font-size:14px; margin:0 0 8px;"><?= nl2br(esc($inspeccion[$sec['campo']])) ?></p>
            <?php endif; ?>
            <?php if ($tieneFotos): ?>
            <div class="row g-2">
                <?php foreach ($sec['fotos'] as $f): ?>
                <?php if (!empty($inspeccion[$f])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$f]) ?>" class="img-fluid rounded" style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Concepto del Consultor -->
    <?php if (!empty($inspeccion['concepto_entradas_salidas']) || !empty($inspeccion['hidrantes'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CONCEPTO DEL CONSULTOR</h6>
            <?php if (!empty($inspeccion['concepto_entradas_salidas'])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;">Entradas y salidas ante emergencia</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['concepto_entradas_salidas'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($inspeccion['hidrantes'])): ?>
            <div class="mb-0">
                <strong style="font-size:12px; color:#555;">Hidrantes</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['hidrantes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Entorno -->
    <?php if (!empty($inspeccion['cai_cercano']) || !empty($inspeccion['bomberos_cercanos'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ENTORNO</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <?php if (!empty($inspeccion['cai_cercano'])): ?>
                <tr><td class="text-muted" style="width:45%;">CAI mas cercano</td><td><?= esc($inspeccion['cai_cercano']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['bomberos_cercanos'])): ?>
                <tr><td class="text-muted">Bomberos mas cercanos</td><td><?= esc($inspeccion['bomberos_cercanos']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Proveedores -->
    <?php if (!empty($inspeccion['proveedor_vigilancia']) || !empty($inspeccion['proveedor_aseo']) || !empty($inspeccion['otros_proveedores'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">PROVEEDORES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <?php if (!empty($inspeccion['proveedor_vigilancia'])): ?>
                <tr><td class="text-muted" style="width:45%;">Vigilancia</td><td><?= esc($inspeccion['proveedor_vigilancia']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['proveedor_aseo'])): ?>
                <tr><td class="text-muted">Aseo</td><td><?= esc($inspeccion['proveedor_aseo']) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php if (!empty($inspeccion['otros_proveedores'])): ?>
            <div class="mt-2">
                <strong style="font-size:12px; color:#555;">Otros proveedores</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['otros_proveedores'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Control de Visitantes -->
    <?php if (!empty($inspeccion['registro_visitantes_forma']) || !empty($inspeccion['registro_visitantes_emergencia'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CONTROL DE VISITANTES</h6>
            <?php if (!empty($inspeccion['registro_visitantes_forma'])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;">Forma de registro</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['registro_visitantes_forma'])) ?></p>
            </div>
            <?php endif; ?>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:60%;">Permite saber personas en emergencia</td><td><?= $enumSiNo[$inspeccion['registro_visitantes_emergencia'] ?? ''] ?? '-' ?></td></tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comunicaciones -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">COMUNICACIONES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:50%;">Cuenta con megafono</td><td><?= $enumSiNo[$inspeccion['cuenta_megafono'] ?? ''] ?? '-' ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Ruta de Evacuacion -->
    <?php
    $tieneEvac = !empty($inspeccion['ruta_evacuacion']) || !empty($inspeccion['mapa_evacuacion'])
        || !empty($inspeccion['foto_ruta_evacuacion_1']) || !empty($inspeccion['foto_ruta_evacuacion_2']);
    if ($tieneEvac): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RUTA DE EVACUACION</h6>
            <?php if (!empty($inspeccion['ruta_evacuacion'])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;">Estado de la ruta</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['ruta_evacuacion'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($inspeccion['mapa_evacuacion'])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;">Mapa de evacuacion</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['mapa_evacuacion'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($inspeccion['foto_ruta_evacuacion_1']) || !empty($inspeccion['foto_ruta_evacuacion_2'])): ?>
            <div class="row g-2 mt-1">
                <?php foreach (['foto_ruta_evacuacion_1', 'foto_ruta_evacuacion_2'] as $f): ?>
                <?php if (!empty($inspeccion[$f])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$f]) ?>" class="img-fluid rounded" style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Puntos de Encuentro -->
    <?php
    $tienePE = !empty($inspeccion['puntos_encuentro'])
        || !empty($inspeccion['foto_punto_encuentro_1']) || !empty($inspeccion['foto_punto_encuentro_2']);
    if ($tienePE): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">PUNTOS DE ENCUENTRO</h6>
            <?php if (!empty($inspeccion['puntos_encuentro'])): ?>
            <p style="font-size:14px; margin:0 0 8px;"><?= nl2br(esc($inspeccion['puntos_encuentro'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($inspeccion['foto_punto_encuentro_1']) || !empty($inspeccion['foto_punto_encuentro_2'])): ?>
            <div class="row g-2">
                <?php foreach (['foto_punto_encuentro_1', 'foto_punto_encuentro_2'] as $f): ?>
                <?php if (!empty($inspeccion[$f])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$f]) ?>" class="img-fluid rounded" style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sistemas de Alarma y Emergencia -->
    <?php
    $camposAlarma = [
        'sistema_alarma'     => 'Sistema de alarma',
        'codigos_alerta'     => 'Codigos de alerta y alarma',
        'energia_emergencia' => 'Energia de emergencia y luminarias',
        'deteccion_fuego'    => 'Sistemas de deteccion de fuego',
        'vias_transito'      => 'Vias de transito cercanas',
    ];
    $tieneAlarma = false;
    foreach ($camposAlarma as $c => $l) { if (!empty($inspeccion[$c])) { $tieneAlarma = true; break; } }
    if ($tieneAlarma): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">SISTEMAS DE ALARMA Y EMERGENCIA</h6>
            <?php foreach ($camposAlarma as $campo => $label): ?>
            <?php if (!empty($inspeccion[$campo])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;"><?= $label ?></strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion[$campo])) ?></p>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Administracion -->
    <?php
    $tieneAdmin = !empty($inspeccion['nombre_administrador']) || !empty($inspeccion['horarios_administracion'])
        || !empty($inspeccion['personal_aseo']) || !empty($inspeccion['personal_vigilancia']);
    if ($tieneAdmin): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ADMINISTRACION</h6>
            <table class="table table-sm mb-2" style="font-size:14px;">
                <?php if (!empty($inspeccion['nombre_administrador'])): ?>
                <tr><td class="text-muted" style="width:45%;">Administrador</td><td><?= esc($inspeccion['nombre_administrador']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['horarios_administracion'])): ?>
                <tr><td class="text-muted">Horarios</td><td><?= esc($inspeccion['horarios_administracion']) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php if (!empty($inspeccion['personal_aseo'])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;">Personal de aseo</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['personal_aseo'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($inspeccion['personal_vigilancia'])): ?>
            <div class="mb-0">
                <strong style="font-size:12px; color:#555;">Personal de vigilancia</strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion['personal_vigilancia'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Telefonos de Emergencia -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">TELEFONOS DE EMERGENCIA</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Ciudad</td><td><strong><?= ucfirst($ciudad ?? '-') ?></strong></td></tr>
                <?php if (!empty($inspeccion['cuadrante'])): ?>
                <tr><td class="text-muted">Cuadrante</td><td><?= esc($inspeccion['cuadrante']) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php if (!empty($telefonosCiudad)): ?>
            <table class="table table-sm table-bordered mt-2 mb-0" style="font-size:13px;">
                <thead style="background:#f8f9fa;"><tr><th>Entidad</th><th>Telefono</th></tr></thead>
                <tbody>
                <?php foreach ($telefonosCiudad as $entidad => $numero): ?>
                <tr><td><?= esc($entidad) ?></td><td><strong><?= esc($numero) ?></strong></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gabinetes -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">GABINETES CONTRA INCENDIO</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:60%;">Tiene gabinetes hidraulicos</td><td><?= $enumSiNo[$inspeccion['tiene_gabinetes_hidraulico'] ?? ''] ?? '-' ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Servicios Generales -->
    <?php
    $camposServ = [
        'ruta_residuos_solidos' => 'Ruta de residuos solidos',
        'servicios_sanitarios'  => 'Servicios sanitarios',
        'detalle_mascotas'      => 'Detalle mascotas',
        'detalle_dependencias'  => 'Detalle dependencias',
    ];
    $tieneServ = !empty($inspeccion['empresa_aseo']) || !empty($inspeccion['frecuencia_basura']);
    foreach ($camposServ as $c => $l) { if (!empty($inspeccion[$c])) { $tieneServ = true; break; } }
    if ($tieneServ): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">SERVICIOS GENERALES</h6>
            <table class="table table-sm mb-2" style="font-size:14px;">
                <?php if (!empty($inspeccion['empresa_aseo'])): ?>
                <tr><td class="text-muted" style="width:45%;">Empresa de aseo</td><td><?= $empresasAseo[$inspeccion['empresa_aseo']] ?? esc($inspeccion['empresa_aseo']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['frecuencia_basura'])): ?>
                <tr><td class="text-muted">Frecuencia basura</td><td><?= esc($inspeccion['frecuencia_basura']) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php foreach ($camposServ as $campo => $label): ?>
            <?php if (!empty($inspeccion[$campo])): ?>
            <div class="mb-2">
                <strong style="font-size:12px; color:#555;"><?= $label ?></strong>
                <p style="font-size:14px; margin:2px 0 0;"><?= nl2br(esc($inspeccion[$campo])) ?></p>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES Y RECOMENDACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal foto -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-body p-1 text-center">
                    <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
                </div>
            </div>
        </div>
    </div>
    <script>
    function openPhoto(src) {
        document.getElementById('photoFull').src = src;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
    </script>

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="/inspecciones/plan-emergencia/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/plan-emergencia/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/plan-emergencia/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
