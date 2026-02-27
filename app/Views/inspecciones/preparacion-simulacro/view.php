<?php
$alarmaSeleccionados = !empty($inspeccion['tipo_alarma']) ? explode(',', $inspeccion['tipo_alarma']) : [];
$distintivosSeleccionados = !empty($inspeccion['distintivos_brigadistas']) ? explode(',', $inspeccion['distintivos_brigadistas']) : [];
$equiposSeleccionados = !empty($inspeccion['equipos_emergencia']) ? explode(',', $inspeccion['equipos_emergencia']) : [];

$tiempoTotal = '';
if (!empty($inspeccion['hora_inicio']) && !empty($inspeccion['agradecimiento_cierre'])) {
    $inicio = new \DateTime($inspeccion['hora_inicio']);
    $fin = new \DateTime($inspeccion['agradecimiento_cierre']);
    $diff = $inicio->diff($fin);
    $tiempoTotal = $diff->format('%H:%I:%S');
}
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Preparacion Simulacro</h6>
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
                <tr><td class="text-muted">Fecha simulacro</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_simulacro'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <?php if (!empty($inspeccion['ubicacion'])): ?>
                <tr><td class="text-muted">Ubicacion</td><td><?= esc($inspeccion['ubicacion']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['direccion'])): ?>
                <tr><td class="text-muted">Direccion</td><td><?= esc($inspeccion['direccion']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Configuracion del simulacro -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CONFIGURACION DEL SIMULACRO</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <?php if (!empty($inspeccion['evento_simulado'])): ?>
                <tr><td class="text-muted" style="width:45%;">Evento simulado</td><td><?= esc(ucfirst($inspeccion['evento_simulado'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['alcance_simulacro'])): ?>
                <tr><td class="text-muted">Alcance</td><td><?= esc(ucfirst($inspeccion['alcance_simulacro'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['tipo_evacuacion'])): ?>
                <tr><td class="text-muted">Tipo evacuacion</td><td><?= esc(ucfirst($inspeccion['tipo_evacuacion'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['personal_no_evacua'])): ?>
                <tr><td class="text-muted">Personal no evacua</td><td><?= nl2br(esc($inspeccion['personal_no_evacua'])) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Alarma y distintivos -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ALARMA Y DISTINTIVOS</h6>
            <?php if (!empty($alarmaSeleccionados)): ?>
            <div class="mb-2">
                <small class="text-muted">Tipo de alarma</small><br>
                <?php foreach ($alarmaSeleccionados as $val): ?>
                <span class="badge bg-primary me-1 mb-1" style="font-size:11px;"><?= esc($opcionesAlarma[$val] ?? $val) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($distintivosSeleccionados)): ?>
            <div class="mb-2">
                <small class="text-muted">Distintivos brigadistas</small><br>
                <?php foreach ($distintivosSeleccionados as $val): ?>
                <span class="badge bg-info me-1 mb-1" style="font-size:11px;"><?= esc($opcionesDistintivos[$val] ?? $val) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logistica -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">LOGISTICA</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <?php if (!empty($inspeccion['puntos_encuentro'])): ?>
                <tr><td class="text-muted" style="width:45%;">Puntos de encuentro</td><td><?= nl2br(esc($inspeccion['puntos_encuentro'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['recurso_humano'])): ?>
                <tr><td class="text-muted">Recurso humano</td><td><?= nl2br(esc($inspeccion['recurso_humano'])) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php if (!empty($equiposSeleccionados)): ?>
            <div class="mt-2">
                <small class="text-muted">Equipos de emergencia</small><br>
                <?php foreach ($equiposSeleccionados as $val): ?>
                <span class="badge bg-warning text-dark me-1 mb-1" style="font-size:11px;"><?= esc($opcionesEquipos[$val] ?? $val) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Brigadista lider -->
    <?php if (!empty($inspeccion['nombre_brigadista_lider'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">BRIGADISTA LIDER</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Nombre</td><td><?= esc($inspeccion['nombre_brigadista_lider']) ?></td></tr>
                <?php if (!empty($inspeccion['email_brigadista_lider'])): ?>
                <tr><td class="text-muted">Email</td><td><?= esc($inspeccion['email_brigadista_lider']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['whatsapp_brigadista_lider'])): ?>
                <tr><td class="text-muted">WhatsApp</td><td><?= esc($inspeccion['whatsapp_brigadista_lider']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Registro fotografico -->
    <?php if (!empty($inspeccion['imagen_1']) || !empty($inspeccion['imagen_2'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRAFICO</h6>
            <div class="d-flex gap-2 flex-wrap">
                <?php if (!empty($inspeccion['imagen_1'])): ?>
                <div>
                    <small class="text-muted">Imagen 1</small>
                    <img src="/<?= esc($inspeccion['imagen_1']) ?>" class="img-fluid rounded d-block"
                        style="max-height:120px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php if (!empty($inspeccion['imagen_2'])): ?>
                <div>
                    <small class="text-muted">Imagen 2</small>
                    <img src="/<?= esc($inspeccion['imagen_2']) ?>" class="img-fluid rounded d-block"
                        style="max-height:120px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cronograma -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CRONOGRAMA</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <thead>
                    <tr><th>Etapa</th><th style="width:100px;">Hora</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cronogramaItems as $key => $label): ?>
                    <tr>
                        <td><?= $label ?></td>
                        <td><?= !empty($inspeccion[$key]) ? esc($inspeccion[$key]) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($tiempoTotal): ?>
                    <tr class="table-dark">
                        <td><strong>Tiempo Total</strong></td>
                        <td><strong><?= $tiempoTotal ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Evaluacion -->
    <?php if (!empty($inspeccion['entrega_formato_evaluacion'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">EVALUACION</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Entrega formato evaluacion</td><td><?= esc(ucfirst($inspeccion['entrega_formato_evaluacion'])) ?></td></tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
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
        <a href="/inspecciones/preparacion-simulacro/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/preparacion-simulacro/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>
        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/preparacion-simulacro/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
