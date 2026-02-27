<?php
$e = $eval;
$alarmaArr = !empty($e['tipo_alarma']) ? explode(',', $e['tipo_alarma']) : [];
$distintivosArr = !empty($e['distintivos_brigadistas']) ? explode(',', $e['distintivos_brigadistas']) : [];
$equiposArr = !empty($e['equipos_emergencia']) ? explode(',', $e['equipos_emergencia']) : [];

$opcionesAlarma = ['Sirena', 'Silbato', 'Megafono', 'Campana / timbre', 'Radio interno'];
$opcionesDistintivos = ['Chaleco', 'Brazalete', 'Gorra', 'Ninguno'];
$opcionesEquipos = ['Paletas de PARE y SIGA', 'Chaleco reflectivo', 'Megafono o, en su defecto, pito', 'Camilla', 'Botiquin', 'Radio de onda corta', 'Paleta Punto de Encuentro'];

$cronoCampos = [
    'hora_inicio' => 'Hora de inicio',
    'alistamiento_recursos' => 'Alistamiento de recursos',
    'asumir_roles' => 'Asumir roles',
    'suena_alarma' => 'Suena la alarma',
    'distribucion_roles' => 'Distribucion de roles',
    'llegada_punto_encuentro' => 'Llegada punto de encuentro',
    'agrupacion_por_afinidad' => 'Agrupacion por afinidad',
    'conteo_personal' => 'Conteo de personal',
    'agradecimiento_y_cierre' => 'Agradecimiento y cierre',
];

$criterios = [
    'alarma_efectiva' => 'Alarma efectiva',
    'orden_evacuacion' => 'Orden de evacuacion',
    'liderazgo_brigadistas' => 'Liderazgo brigadistas',
    'organizacion_punto_encuentro' => 'Organizacion punto de encuentro',
    'participacion_general' => 'Participacion general',
];
?>

<div class="container-fluid px-3">
    <form method="post" action="/inspecciones/simulacro/update/<?= $e['id'] ?>" id="formEvalSim" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <!-- Cliente + Fecha -->
        <div class="card mb-3 mt-2">
            <div class="card-header py-2"><strong><i class="fas fa-building"></i> Datos Generales</strong></div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente'] ?? 'Sin cliente') ?>" disabled>
                </div>
                <div class="mb-2">
                    <label class="form-label">Fecha *</label>
                    <input type="date" name="fecha" class="form-control" value="<?= esc($e['fecha'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Direccion</label>
                    <input type="text" name="direccion" class="form-control" value="<?= esc($e['direccion'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Configuracion -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-cogs"></i> Configuracion del Simulacro</strong></div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label">Evento simulado</label>
                    <select name="evento_simulado" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach (['Sismo', 'Incendio', 'Evacuacion'] as $op): ?>
                        <option value="<?= $op ?>" <?= ($e['evento_simulado'] ?? '') === $op ? 'selected' : '' ?>><?= $op ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Alcance</label>
                    <select name="alcance_simulacro" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach (['Total', 'Parcial'] as $op): ?>
                        <option value="<?= $op ?>" <?= ($e['alcance_simulacro'] ?? '') === $op ? 'selected' : '' ?>><?= $op ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Tipo de evacuacion</label>
                    <select name="tipo_evacuacion" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach (['Mixta', 'Vertical descendente', 'Horizontal'] as $op): ?>
                        <option value="<?= $op ?>" <?= ($e['tipo_evacuacion'] ?? '') === $op ? 'selected' : '' ?>><?= $op ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Personal que no evacua</label>
                    <textarea name="personal_no_evacua" class="form-control" rows="2"><?= esc($e['personal_no_evacua'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Alarma y Distintivos -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-bell"></i> Alarma y Distintivos</strong></div>
            <div class="card-body py-2">
                <label class="form-label">Tipo de alarma</label>
                <?php foreach ($opcionesAlarma as $op): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tipo_alarma[]" value="<?= $op ?>" id="al_<?= md5($op) ?>" <?= in_array($op, $alarmaArr) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="al_<?= md5($op) ?>"><?= $op ?></label>
                </div>
                <?php endforeach; ?>

                <hr class="my-2">
                <label class="form-label">Distintivos brigadistas</label>
                <?php foreach ($opcionesDistintivos as $op): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="distintivos_brigadistas[]" value="<?= $op ?>" id="dist_<?= md5($op) ?>" <?= in_array($op, $distintivosArr) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dist_<?= md5($op) ?>"><?= $op ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Logistica -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-boxes"></i> Logistica</strong></div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label">Puntos de encuentro</label>
                    <textarea name="puntos_encuentro" class="form-control" rows="2"><?= esc($e['puntos_encuentro'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Recurso humano</label>
                    <textarea name="recurso_humano" class="form-control" rows="2"><?= esc($e['recurso_humano'] ?? '') ?></textarea>
                </div>
                <label class="form-label">Equipos de emergencia</label>
                <?php foreach ($opcionesEquipos as $op): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="equipos_emergencia[]" value="<?= $op ?>" id="eq_<?= md5($op) ?>" <?= in_array($op, $equiposArr) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="eq_<?= md5($op) ?>"><?= $op ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Brigadista Lider -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-user-shield"></i> Brigadista Lider</strong></div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre_brigadista_lider" class="form-control" value="<?= esc($e['nombre_brigadista_lider'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email_brigadista_lider" class="form-control" value="<?= esc($e['email_brigadista_lider'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">WhatsApp</label>
                    <input type="tel" name="whatsapp_brigadista_lider" class="form-control" maxlength="10" value="<?= esc($e['whatsapp_brigadista_lider'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Cronograma -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-stopwatch"></i> Cronograma</strong></div>
            <div class="card-body py-2">
                <?php foreach ($cronoCampos as $key => $label): ?>
                <?php $raw = $e[$key] ?? ''; $val = $raw ? date('Y-m-d\TH:i', strtotime($raw)) : ''; ?>
                <div class="mb-2">
                    <label class="form-label"><?= $label ?></label>
                    <input type="datetime-local" name="<?= $key ?>" class="form-control crono-input" value="<?= $val ?>">
                </div>
                <?php endforeach; ?>
                <div class="mb-2">
                    <label class="form-label">Tiempo total</label>
                    <input type="text" name="tiempo_total" id="tiempoTotal" class="form-control" value="<?= esc($e['tiempo_total'] ?? '') ?>" placeholder="HH:MM:SS" readonly>
                </div>
            </div>
        </div>

        <!-- Conteo Evacuados -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-users"></i> Conteo de Evacuados</strong></div>
            <div class="card-body py-2">
                <?php
                $conteoCampos = [
                    'hombre' => 'Hombres', 'mujer' => 'Mujeres', 'ninos' => 'Ninos',
                    'adultos_mayores' => 'Adultos mayores', 'discapacidad' => 'Discapacidad', 'mascotas' => 'Mascotas'
                ];
                foreach ($conteoCampos as $key => $label): ?>
                <div class="mb-2">
                    <label class="form-label"><?= $label ?></label>
                    <input type="number" name="<?= $key ?>" class="form-control conteo-input" min="0" value="<?= (int)($e[$key] ?? 0) ?>">
                </div>
                <?php endforeach; ?>
                <div class="mb-2">
                    <label class="form-label"><strong>Total</strong></label>
                    <input type="number" name="total" id="conteoTotal" class="form-control" value="<?= (int)($e['total'] ?? 0) ?>" readonly>
                </div>
            </div>
        </div>

        <!-- Evaluacion -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-star"></i> Evaluacion</strong></div>
            <div class="card-body py-2">
                <?php foreach ($criterios as $key => $label): ?>
                <div class="mb-2">
                    <label class="form-label"><?= $label ?></label>
                    <select name="<?= $key ?>" class="form-select criterio-select">
                        <option value="">--</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>" <?= ((int)($e[$key] ?? 0)) === $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <?php endforeach; ?>
                <div class="mb-2">
                    <label class="form-label">Evaluacion cuantitativa</label>
                    <input type="text" name="evaluacion_cuantitativa" id="evalCuant" class="form-control" value="<?= esc($e['evaluacion_cuantitativa'] ?? '') ?>" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label">Evaluacion cualitativa</label>
                    <textarea name="evaluacion_cualitativa" class="form-control" rows="3"><?= esc($e['evaluacion_cualitativa'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Evidencias y Observaciones -->
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fas fa-camera"></i> Evidencias y Observaciones</strong></div>
            <div class="card-body py-2">
                <?php foreach (['imagen_1' => 'Imagen 1', 'imagen_2' => 'Imagen 2'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label"><?= $label ?></label>
                    <?php if (!empty($e[$campo])): ?>
                    <div class="mb-1"><img src="/<?= esc($e[$campo]) ?>" style="max-width:200px; max-height:150px; border-radius:8px;" alt="<?= $label ?>"></div>
                    <?php endif; ?>
                    <input type="file" name="<?= $campo ?>" class="form-control" accept="image/*">
                    <div class="form-text">Dejar vacio para mantener la imagen actual</div>
                </div>
                <?php endforeach; ?>

                <div class="mb-2">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"><?= esc($e['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-primary">
                <i class="fas fa-save"></i> Guardar
            </button>
            <?php if (($e['estado'] ?? '') === 'borrador'): ?>
            <button type="submit" name="finalizar" value="1" class="btn btn-success">
                <i class="fas fa-check"></i> Finalizar
            </button>
            <?php endif; ?>
            <a href="/inspecciones/simulacro" class="btn btn-outline-secondary">Volver</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calcular total evacuados
    function actualizarConteo() {
        var total = 0;
        document.querySelectorAll('.conteo-input').forEach(function(inp) {
            total += parseInt(inp.value || 0);
        });
        document.getElementById('conteoTotal').value = total;
    }
    document.querySelectorAll('.conteo-input').forEach(function(inp) {
        inp.addEventListener('input', actualizarConteo);
    });

    // Auto-calcular evaluacion cuantitativa
    function calcularEval() {
        var keys = document.querySelectorAll('.criterio-select');
        var sum = 0, count = 0;
        keys.forEach(function(sel) {
            var v = parseInt(sel.value || 0);
            if (v > 0) { sum += v; count++; }
        });
        var avg = count === 5 ? (sum / 5).toFixed(2) : '';
        document.getElementById('evalCuant').value = avg ? avg + '/10' : '';
    }
    document.querySelectorAll('.criterio-select').forEach(function(sel) {
        sel.addEventListener('change', calcularEval);
    });

    // Auto-calcular tiempo total
    function calcularTiempo() {
        var inicio = document.querySelector('[name="hora_inicio"]').value;
        var cierre = document.querySelector('[name="agradecimiento_y_cierre"]').value;
        if (!inicio || !cierre) return;
        var diff = new Date(cierre) - new Date(inicio);
        if (diff <= 0) return;
        var h = Math.floor(diff / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        document.getElementById('tiempoTotal').value =
            String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }
    document.querySelectorAll('.crono-input').forEach(function(inp) {
        inp.addEventListener('change', calcularTiempo);
    });
});
</script>
