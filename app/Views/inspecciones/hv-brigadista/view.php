<div class="container-fluid px-3">

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h5 class="mb-0"><i class="fas fa-id-card-alt"></i> HV Brigadista</h5>
        <a href="/inspecciones/hv-brigadista" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <!-- Estado -->
    <div class="mb-3">
        <span class="badge badge-<?= esc($hv['estado']) ?>" style="font-size:12px;">
            <?= $hv['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
        <?php if ($hv['estado'] === 'completo'): ?>
            <a href="/inspecciones/hv-brigadista/pdf/<?= $hv['id'] ?>" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                <i class="fas fa-file-pdf"></i> Ver PDF
            </a>
        <?php else: ?>
            <form action="/inspecciones/hv-brigadista/finalizar/<?= $hv['id'] ?>" method="post" class="d-inline ms-2">
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Finalizar y generar PDF</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Datos Personales -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-user"></i> Datos Personales</strong></div>
        <div class="card-body" style="font-size:13px;">
            <?php if (!empty($hv['foto_brigadista'])): ?>
            <div class="text-center mb-2">
                <img src="<?= base_url($hv['foto_brigadista']) ?>" alt="Foto" style="max-height:150px; border-radius:8px; object-fit:contain;">
            </div>
            <?php endif; ?>
            <div><strong>Copropiedad:</strong> <?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></div>
            <div><strong>Nombre:</strong> <?= esc($hv['nombre_completo'] ?? 'N/A') ?></div>
            <div><strong>Documento:</strong> <?= esc($hv['documento_identidad'] ?? 'N/A') ?></div>
            <div><strong>Fecha nacimiento:</strong> <?= !empty($hv['f_nacimiento']) ? date('d/m/Y', strtotime($hv['f_nacimiento'])) : 'N/A' ?></div>
            <div><strong>Edad:</strong> <?= esc($hv['edad'] ?? 'N/A') ?> anios</div>
            <div><strong>Email:</strong> <?= esc($hv['email'] ?? 'N/A') ?></div>
            <div><strong>Telefono:</strong> <?= esc($hv['telefono'] ?? 'N/A') ?></div>
            <div><strong>Direccion:</strong> <?= esc($hv['direccion_residencia'] ?? 'N/A') ?></div>
            <div><strong>EPS:</strong> <?= esc($hv['eps'] ?? 'N/A') ?></div>
            <div><strong>RH:</strong> <?= esc($hv['rh'] ?? 'N/A') ?></div>
            <div><strong>Peso:</strong> <?= esc($hv['peso'] ?? 'N/A') ?> kg</div>
            <div><strong>Estatura:</strong> <?= esc($hv['estatura'] ?? 'N/A') ?> cm</div>
        </div>
    </div>

    <!-- Estudios -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-graduation-cap"></i> Estudios</strong></div>
        <div class="card-body" style="font-size:13px;">
            <?php
            $tieneEstudios = false;
            for ($i = 1; $i <= 3; $i++):
                $est = $hv["estudios_$i"] ?? '';
                $lugar = $hv["lugar_estudio_$i"] ?? '';
                $anio = $hv["anio_estudio_$i"] ?? '';
                if ($est || $lugar || $anio):
                    $tieneEstudios = true;
            ?>
            <div class="border-bottom pb-1 mb-1">
                <div><strong>Estudio <?= $i ?>:</strong> <?= esc($est ?: 'N/A') ?></div>
                <div class="text-muted">Institucion: <?= esc($lugar ?: 'N/A') ?> | Anio: <?= esc($anio ?: 'N/A') ?></div>
            </div>
            <?php
                endif;
            endfor;
            if (!$tieneEstudios): ?>
                <p class="text-muted mb-0">Sin estudios registrados</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Salud -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-heartbeat"></i> Informacion de Salud</strong></div>
        <div class="card-body" style="font-size:13px;">
            <div><strong>Enfermedades:</strong> <?= esc($hv['enfermedades_importantes'] ?? 'N/A') ?></div>
            <div><strong>Medicamentos:</strong> <?= esc($hv['medicamentos'] ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Cuestionario Medico -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-notes-medical"></i> Cuestionario Medico</strong></div>
        <div class="card-body" style="font-size:13px;">
            <?php
            $preguntas = [
                'cardiaca' => 'Enfermedad cardiaca',
                'pechoactividad' => 'Dolor pecho en actividad',
                'dolorpecho' => 'Dolor pecho en reposo',
                'conciencia' => 'Perdida de conciencia/mareo',
                'huesos' => 'Problemas huesos/articulaciones',
                'medicamentos_bool' => 'Medicamentos presion/diabetes/corazon',
                'actividadfisica' => 'Razon para no hacer actividad',
                'convulsiones' => 'Convulsiones/epilepsia',
                'vertigo' => 'Vertigo',
                'oidos' => 'Enfermedades oidos',
                'lugarescerrados' => 'Miedo lugares cerrados',
                'miedoalturas' => 'Miedo alturas',
                'haceejercicio' => 'Hace ejercicio semanal',
                'miedo_ver_sangre' => 'Miedo a ver sangre',
            ];
            $n = 1;
            foreach ($preguntas as $key => $label):
                $val = $hv[$key] ?? '-';
                $badge = $val === 'SI' ? 'bg-warning text-dark' : ($val === 'NO' ? 'bg-success' : 'bg-secondary');
            ?>
            <div class="d-flex justify-content-between border-bottom py-1">
                <span><?= $n ?>. <?= esc($label) ?></span>
                <span class="badge <?= $badge ?>"><?= esc($val) ?></span>
            </div>
            <?php $n++; endforeach; ?>
        </div>
    </div>

    <!-- Restricciones y Deportes -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-running"></i> Restricciones y Actividad</strong></div>
        <div class="card-body" style="font-size:13px;">
            <div><strong>Restricciones medicas:</strong> <?= esc($hv['restricciones_medicas'] ?? 'N/A') ?></div>
            <div><strong>Deportes/horas semana:</strong> <?= esc($hv['deporte_semana'] ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Firma -->
    <div class="card mb-3">
        <div class="card-header"><strong><i class="fas fa-signature"></i> Firma</strong></div>
        <div class="card-body text-center" style="font-size:13px;">
            <?php if (!empty($hv['firma'])): ?>
                <img src="<?= base_url($hv['firma']) ?>" alt="Firma" style="max-height:120px; border:1px solid #ccc; border-radius:8px;">
            <?php else: ?>
                <p class="text-muted">Sin firma registrada</p>
            <?php endif; ?>
        </div>
    </div>

</div>
