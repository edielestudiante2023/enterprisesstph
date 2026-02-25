<div class="container-fluid px-3">

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h5 class="mb-0"><i class="fas fa-running"></i> Evaluacion Simulacro</h5>
        <a href="/inspecciones/simulacro" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <!-- Estado -->
    <div class="mb-3">
        <span class="badge badge-<?= esc($eval['estado']) ?>" style="font-size:12px;">
            <?= $eval['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
        <?php if ($eval['estado'] === 'completo'): ?>
            <a href="/inspecciones/simulacro/pdf/<?= $eval['id'] ?>" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                <i class="fas fa-file-pdf"></i> Ver PDF
            </a>
        <?php else: ?>
            <form action="/inspecciones/simulacro/finalizar/<?= $eval['id'] ?>" method="post" class="d-inline ms-2">
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Finalizar y generar PDF</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Identificacion -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-building"></i> Identificacion</strong></div>
        <div class="card-body" style="font-size:13px;">
            <div><strong>Copropiedad:</strong> <?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></div>
            <div><strong>NIT:</strong> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></div>
            <div><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($eval['fecha'])) ?></div>
            <div><strong>Direccion:</strong> <?= esc($eval['direccion'] ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Info General -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-info-circle"></i> Informacion General</strong></div>
        <div class="card-body" style="font-size:13px;">
            <div><strong>Evento simulado:</strong> <?= esc($eval['evento_simulado'] ?? 'N/A') ?></div>
            <div><strong>Alcance:</strong> <?= esc($eval['alcance_simulacro'] ?? 'N/A') ?></div>
            <div><strong>Tipo evacuacion:</strong> <?= esc($eval['tipo_evacuacion'] ?? 'N/A') ?></div>
            <div><strong>Personal no evacua:</strong> <?= esc($eval['personal_no_evacua'] ?? 'N/A') ?></div>
            <div><strong>Tipo alarma:</strong> <?= esc($eval['tipo_alarma'] ?? 'N/A') ?></div>
            <div><strong>Puntos de encuentro:</strong> <?= esc($eval['puntos_encuentro'] ?? 'N/A') ?></div>
            <div><strong>Recurso humano:</strong> <?= esc($eval['recurso_humano'] ?? 'N/A') ?></div>
            <div><strong>Equipos de emergencia:</strong> <?= esc($eval['equipos_emergencia'] ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Brigadista Lider -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-user-shield"></i> Brigadista Lider</strong></div>
        <div class="card-body" style="font-size:13px;">
            <div><strong>Nombre:</strong> <?= esc($eval['nombre_brigadista_lider'] ?? 'N/A') ?></div>
            <div><strong>Email:</strong> <?= esc($eval['email_brigadista_lider'] ?? 'N/A') ?></div>
            <div><strong>WhatsApp:</strong> <?= esc($eval['whatsapp_brigadista_lider'] ?? 'N/A') ?></div>
            <div><strong>Distintivos:</strong> <?= esc($eval['distintivos_brigadistas'] ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Cronograma -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-stopwatch"></i> Cronograma del Simulacro</strong></div>
        <div class="card-body" style="font-size:13px;">
            <?php
            $cronoPasos = [
                'hora_inicio' => 'Hora de Inicio',
                'alistamiento_recursos' => 'Alistamiento de Recursos',
                'asumir_roles' => 'Asumir roles',
                'suena_alarma' => 'Suena alarma',
                'distribucion_roles' => 'Distribucion de roles',
                'llegada_punto_encuentro' => 'Llegada punto de encuentro',
                'agrupacion_por_afinidad' => 'Agrupacion por afinidad',
                'conteo_personal' => 'Conteo de personal',
                'agradecimiento_y_cierre' => 'Agradecimiento y cierre',
            ];
            foreach ($cronoPasos as $key => $label):
                $valor = $eval[$key] ?? null;
            ?>
            <div class="d-flex justify-content-between border-bottom py-1">
                <span><?= esc($label) ?></span>
                <span class="fw-bold"><?= $valor ? date('H:i:s', strtotime($valor)) : '--:--:--' ?></span>
            </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between pt-2 mt-1 border-top">
                <span class="fw-bold">Tiempo Total</span>
                <span class="fw-bold text-primary" style="font-size:16px;"><?= esc($eval['tiempo_total'] ?? '--:--:--') ?></span>
            </div>
        </div>
    </div>

    <!-- Conteo de Evacuados -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-calculator"></i> Conteo de Evacuados</strong></div>
        <div class="card-body" style="font-size:13px;">
            <table class="table table-sm mb-0">
                <tr><td>Hombres (18-59)</td><td class="text-end fw-bold"><?= (int)($eval['hombre'] ?? 0) ?></td></tr>
                <tr><td>Mujeres (18-59)</td><td class="text-end fw-bold"><?= (int)($eval['mujer'] ?? 0) ?></td></tr>
                <tr><td>Ninos (&lt;18)</td><td class="text-end fw-bold"><?= (int)($eval['ninos'] ?? 0) ?></td></tr>
                <tr><td>Adultos mayores (60+)</td><td class="text-end fw-bold"><?= (int)($eval['adultos_mayores'] ?? 0) ?></td></tr>
                <tr><td>Discapacidad</td><td class="text-end fw-bold"><?= (int)($eval['discapacidad'] ?? 0) ?></td></tr>
                <tr><td>Mascotas</td><td class="text-end fw-bold"><?= (int)($eval['mascotas'] ?? 0) ?></td></tr>
                <tr class="table-primary"><td class="fw-bold">Total</td><td class="text-end fw-bold"><?= (int)($eval['total'] ?? 0) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Evaluacion -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-star"></i> Evaluacion del Simulacro</strong></div>
        <div class="card-body" style="font-size:13px;">
            <?php
            $criterios = [
                'alarma_efectiva' => 'Alarma efectiva',
                'orden_evacuacion' => 'Orden de evacuacion',
                'liderazgo_brigadistas' => 'Liderazgo brigadistas',
                'organizacion_punto_encuentro' => 'Organizacion punto de encuentro',
                'participacion_general' => 'Participacion general',
            ];
            foreach ($criterios as $key => $label):
            ?>
            <div class="d-flex justify-content-between border-bottom py-1">
                <span><?= esc($label) ?></span>
                <span class="fw-bold"><?= ($eval[$key] ?? '-') ?>/10</span>
            </div>
            <?php endforeach; ?>
            <div class="mt-2">
                <div><strong>Cuantitativa:</strong> <span class="badge bg-info" style="font-size:13px;"><?= esc($eval['evaluacion_cuantitativa'] ?? 'N/A') ?></span></div>
                <div class="mt-1"><strong>Cualitativa:</strong> <?= esc($eval['evaluacion_cualitativa'] ?? 'N/A') ?></div>
            </div>
        </div>
    </div>

    <!-- Evidencias -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-camera"></i> Evidencias</strong></div>
        <div class="card-body" style="font-size:13px;">
            <?php if (!empty($eval['observaciones'])): ?>
                <div class="mb-2"><strong>Observaciones:</strong> <?= esc($eval['observaciones']) ?></div>
            <?php endif; ?>
            <div class="row g-2">
                <?php foreach (['imagen_1', 'imagen_2'] as $idx => $campo): ?>
                    <?php if (!empty($eval[$campo])): ?>
                    <div class="col-6">
                        <img src="<?= base_url($eval[$campo]) ?>" class="img-fluid rounded" alt="Foto <?= $idx + 1 ?>" style="max-height:200px; object-fit:contain;">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if (empty($eval['imagen_1']) && empty($eval['imagen_2'])): ?>
                <p class="text-muted">Sin fotos de evidencia</p>
            <?php endif; ?>
        </div>
    </div>

</div>
