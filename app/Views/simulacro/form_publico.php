<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ff7a00">
    <title>Evaluacion de Simulacro de Evacuacion</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --brand-orange: #ff7a00;
            --brand-orange-100: #fff3e6;
            --brand-orange-700: #cc6200;
        }
        body { background: #f8f9fa; min-height: 100vh; }

        /* Naranja corporativo */
        .btn-brand { background-color: var(--brand-orange); border-color: var(--brand-orange); color: #fff; }
        .btn-brand:hover, .btn-brand:active { background-color: var(--brand-orange-700); border-color: var(--brand-orange-700); color: #fff; }
        .btn-outline-brand { color: var(--brand-orange); border-color: var(--brand-orange); }
        .btn-outline-brand:hover { background-color: var(--brand-orange); color: #fff; }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand-orange);
            box-shadow: 0 0 0 .25rem rgba(255,122,0,.25);
        }
        .form-check-input:checked { background-color: var(--brand-orange); border-color: var(--brand-orange); }
        .card-header { background: var(--brand-orange-100); }
        .bg-brand { background-color: var(--brand-orange) !important; }

        /* Wizard stepper */
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        .step-indicator { display: flex; gap: 4px; padding: 8px 0; overflow-x: auto; }
        .step-dot { width: 28px; height: 28px; border-radius: 50%; background: #dee2e6; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666; flex-shrink: 0; }
        .step-dot.active { background: var(--brand-orange); color: #fff; font-weight: bold; }
        .step-dot.completed { background: #28a745; color: #fff; }

        /* Cronometro buttons */
        .chrono-btn { text-align: left; padding: 12px 16px; margin-bottom: 8px; position: relative; }
        .chrono-btn .chrono-time { display: block; font-size: 13px; color: #666; margin-top: 4px; }
        .chrono-btn.recorded { background: #d4edda; border-color: #28a745; }
        .chrono-btn.recorded .chrono-time { color: #155724; font-weight: bold; }

        /* Sticky actions */
        .sticky-actions { position: sticky; bottom: 0; z-index: 1030; padding: 12px; background: rgba(255,255,255,.95); backdrop-filter: blur(6px); border-top: 1px solid #dee2e6; }

        /* Photo preview */
        .photo-preview { max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; margin-top: 8px; display: none; }

        /* Select2 Bootstrap match */
        .select2-container .select2-selection--single {
            height: calc(2.5rem + 2px); border: 1px solid #ced4da; border-radius: 0.375rem; padding: 0.375rem 0.75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(2.5rem); padding-left: 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.5rem);
        }

        /* Logos header */
        .header-logo { height: 40px; width: auto; max-width: 80px; object-fit: contain; }
        @media (min-width: 768px) { .header-logo { height: 50px; max-width: 100px; } }
    </style>
</head>
<body>

<!-- Header -->
<nav class="navbar bg-brand">
    <div class="container d-flex justify-content-between align-items-center">
        <img src="https://res.cloudinary.com/drcecuc36/image/upload/v1758225634/CYCLOID_TALENT-sin_fondo_n5tkzh.png" alt="Cycloid" class="header-logo">
        <span class="navbar-brand text-white mx-auto text-center mb-0">Evaluacion de Simulacro</span>
        <img src="https://res.cloudinary.com/drcecuc36/image/upload/v1758225634/LOGO_SST_gelepu.png" alt="SST" class="header-logo">
    </div>
</nav>

<!-- Step Indicator -->
<div class="container mt-2">
    <div class="step-indicator" id="stepIndicator">
        <div class="step-dot active" data-step="1">1</div>
        <div class="step-dot" data-step="2">2</div>
        <div class="step-dot" data-step="3">3</div>
        <div class="step-dot" data-step="4">4</div>
        <div class="step-dot" data-step="5">5</div>
        <div class="step-dot" data-step="6">6</div>
        <div class="step-dot" data-step="7">7</div>
        <div class="step-dot" data-step="8">8</div>
        <div class="step-dot" data-step="9">9</div>
    </div>
</div>

<div class="container py-3">

<!-- ==================== PASO 1: Selector Copropiedad ==================== -->
<div class="wizard-step active" id="step-1">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-building"></i> Busque su Copropiedad</h5></div>
        <div class="card-body">
            <p class="text-muted mb-3">Escriba parte del nombre de su copropiedad para buscarla.</p>
            <select id="select-cliente" class="form-select" style="width:100%">
                <option value="">-- Busque su copropiedad --</option>
            </select>
            <div class="sticky-actions mt-3">
                <button type="button" class="btn btn-brand w-100" id="btn-step1-next" disabled>Continuar <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 2: Info General ==================== -->
<div class="wizard-step" id="step-2">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle"></i> Informacion General</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Direccion</label>
                    <input id="f-direccion" class="form-control" type="text" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Evento simulado</label>
                    <select id="f-evento" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="Sismo" selected>Sismo</option>
                        <option value="Incendio">Incendio</option>
                        <option value="Evacuacion">Evacuacion</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Alcance del simulacro</label>
                    <select id="f-alcance" class="form-select">
                        <option value="Total">Total</option>
                        <option value="Parcial">Parcial</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Tipo de evacuacion</label>
                    <select id="f-tipo-evacuacion" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Mixta" selected>Mixta</option>
                        <option value="Vertical descendente">Vertical descendente</option>
                        <option value="Horizontal">Horizontal</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Personal que no evacua</label>
                    <input id="f-personal-no-evacua" class="form-control" type="text" value="Vigilantes">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Tipo de alarma</label>
                    <div id="alarma-group">
                        <div class="form-check"><input class="form-check-input alarma-opt" type="checkbox" value="Sirena" id="al_sirena"><label class="form-check-label" for="al_sirena">Sirena</label></div>
                        <div class="form-check"><input class="form-check-input alarma-opt" type="checkbox" value="Silbato" id="al_silbato"><label class="form-check-label" for="al_silbato">Silbato</label></div>
                        <div class="form-check"><input class="form-check-input alarma-opt" type="checkbox" value="Megafono" id="al_megafono"><label class="form-check-label" for="al_megafono">Megafono</label></div>
                        <div class="form-check"><input class="form-check-input alarma-opt" type="checkbox" value="Campana / timbre" id="al_campana"><label class="form-check-label" for="al_campana">Campana / timbre</label></div>
                        <div class="form-check"><input class="form-check-input alarma-opt" type="checkbox" value="Radio interno" id="al_radio"><label class="form-check-label" for="al_radio">Radio interno</label></div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Puntos de encuentro</label>
                    <input id="f-puntos-encuentro" class="form-control" type="text">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Recurso humano</label>
                    <input id="f-recurso-humano" class="form-control" type="text" value="Contratistas de Aseo y Vigilancia">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Equipos de emergencia</label>
                    <div id="equipos-group">
                        <div class="form-check"><input class="form-check-input equipo-opt" type="checkbox" value="Paletas de PARE y SIGA" id="eq_paletas"><label class="form-check-label" for="eq_paletas">Paletas de PARE y SIGA</label></div>
                        <div class="form-check"><input class="form-check-input equipo-opt" type="checkbox" value="Chaleco reflectivo" id="eq_chaleco"><label class="form-check-label" for="eq_chaleco">Chaleco reflectivo</label></div>
                        <div class="form-check"><input class="form-check-input equipo-opt" type="checkbox" value="Megafono o, en su defecto, pito" id="eq_megafono"><label class="form-check-label" for="eq_megafono">Megafono o pito</label></div>
                        <div class="form-check"><input class="form-check-input equipo-opt" type="checkbox" value="Camilla" id="eq_camilla"><label class="form-check-label" for="eq_camilla">Camilla</label></div>
                        <div class="form-check"><input class="form-check-input equipo-opt" type="checkbox" value="Botiquin" id="eq_botiquin"><label class="form-check-label" for="eq_botiquin">Botiquin</label></div>
                        <div class="form-check"><input class="form-check-input equipo-opt" type="checkbox" value="Radio de onda corta" id="eq_radio"><label class="form-check-label" for="eq_radio">Radio de onda corta</label></div>
                        <div class="form-check"><input class="form-check-input equipo-opt" type="checkbox" value="Paleta Punto de Encuentro" id="eq_paleta_enc"><label class="form-check-label" for="eq_paleta_enc">Paleta Punto de Encuentro</label></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 3: Brigadista Lider ==================== -->
<div class="wizard-step" id="step-3">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-shield"></i> Brigadista Lider</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Nombre Brigadista Lider</label>
                    <input id="f-bl-nombre" class="form-control" type="text" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Email Brigadista Lider</label>
                    <input id="f-bl-email" class="form-control" type="email" placeholder="opcional">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">WhatsApp Brigadista Lider</label>
                    <input id="f-bl-whatsapp" class="form-control" type="tel" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" placeholder="10 digitos (opcional)">
                    <div class="form-text">Solo numeros, 10 digitos (ej: 3123456789)</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Distintivos Brigadistas</label>
                    <div id="distintivos-group">
                        <div class="form-check"><input class="form-check-input distintivo-opt" type="checkbox" value="Chaleco" id="dist_chaleco"><label class="form-check-label" for="dist_chaleco">Chaleco</label></div>
                        <div class="form-check"><input class="form-check-input distintivo-opt" type="checkbox" value="Brazalete" id="dist_brazalete"><label class="form-check-label" for="dist_brazalete">Brazalete</label></div>
                        <div class="form-check"><input class="form-check-input distintivo-opt" type="checkbox" value="Gorra" id="dist_gorra"><label class="form-check-label" for="dist_gorra">Gorra</label></div>
                        <div class="form-check"><input class="form-check-input distintivo-opt" type="checkbox" value="Ninguno" id="dist_ninguno"><label class="form-check-label" for="dist_ninguno">Ninguno</label></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 4: Cronometro Parte 1 ==================== -->
<div class="wizard-step" id="step-4">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-stopwatch"></i> Cronograma del Simulacro - Parte 1</h5></div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <strong><i class="fas fa-clipboard-list"></i> Instrucciones:</strong> Presione cada boton <strong>en el momento exacto</strong> que ocurra cada evento para registrar el tiempo automaticamente.
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="hora_inicio"><i class="fas fa-flag-checkered"></i> Hora de Inicio<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="alistamiento_recursos"><i class="fas fa-clipboard-list"></i> Alistamiento de Recursos<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="asumir_roles"><i class="fas fa-users"></i> Asumir roles<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="suena_alarma"><i class="fas fa-bell"></i> Suena alarma<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="distribucion_roles"><i class="fas fa-people-arrows"></i> Distribucion de roles<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="llegada_punto_encuentro"><i class="fas fa-map-marker-alt"></i> Llegada punto de encuentro<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="agrupacion_por_afinidad"><i class="fas fa-object-group"></i> Agrupacion por afinidad<span class="chrono-time">--:--:--</span></button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 5: Conteo de Evacuados ==================== -->
<div class="wizard-step" id="step-5">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-calculator"></i> Conteo de Evacuados</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Hombres (18-59)</label>
                    <input id="f-hombre" class="form-control conteo-field" type="tel" inputmode="numeric" value="0">
                </div>
                <div class="col-6">
                    <label class="form-label">Mujeres (18-59)</label>
                    <input id="f-mujer" class="form-control conteo-field" type="tel" inputmode="numeric" value="0">
                </div>
                <div class="col-6">
                    <label class="form-label">Ninos (&lt;18)</label>
                    <input id="f-ninos" class="form-control conteo-field" type="tel" inputmode="numeric" value="0">
                </div>
                <div class="col-6">
                    <label class="form-label">Adultos mayores (60+)</label>
                    <input id="f-adultos-mayores" class="form-control conteo-field" type="tel" inputmode="numeric" value="0">
                </div>
                <div class="col-6">
                    <label class="form-label">Discapacidad</label>
                    <input id="f-discapacidad" class="form-control conteo-field" type="tel" inputmode="numeric" value="0">
                </div>
                <div class="col-6">
                    <label class="form-label">Mascotas</label>
                    <input id="f-mascotas" class="form-control conteo-field" type="tel" inputmode="numeric" value="0">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Total</label>
                    <input id="f-total" class="form-control" type="text" readonly value="0">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 6: Evidencias ==================== -->
<div class="wizard-step" id="step-6">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-camera"></i> Evidencias Fotograficas</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Foto 1 -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold">Foto 1 del Simulacro</label>
                    <input type="file" id="foto1-input" accept="image/*" hidden>
                    <input type="file" id="foto1-camera" accept="image/*" capture="environment" hidden>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-brand btn-sm btn-photo" data-target="foto1-camera"><i class="fas fa-camera"></i> Camara</button>
                        <button type="button" class="btn btn-outline-brand btn-sm btn-photo" data-target="foto1-input"><i class="fas fa-image"></i> Galeria</button>
                    </div>
                    <img id="preview-foto1" class="photo-preview" alt="Vista previa foto 1">
                    <div id="upload-status-1" class="form-text"></div>
                </div>
                <!-- Foto 2 -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold">Foto 2 del Simulacro</label>
                    <input type="file" id="foto2-input" accept="image/*" hidden>
                    <input type="file" id="foto2-camera" accept="image/*" capture="environment" hidden>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-brand btn-sm btn-photo" data-target="foto2-camera"><i class="fas fa-camera"></i> Camara</button>
                        <button type="button" class="btn btn-outline-brand btn-sm btn-photo" data-target="foto2-input"><i class="fas fa-image"></i> Galeria</button>
                    </div>
                    <img id="preview-foto2" class="photo-preview" alt="Vista previa foto 2">
                    <div id="upload-status-2" class="form-text"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">Observaciones adicionales</label>
                    <textarea id="f-observaciones" class="form-control" rows="4" placeholder="Opcional"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 7: Cronometro Parte 2 ==================== -->
<div class="wizard-step" id="step-7">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-stopwatch"></i> Cronograma del Simulacro - Parte 2</h5></div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <strong><i class="fas fa-clipboard-list"></i> Instrucciones:</strong> Presione cada boton <strong>en el momento exacto</strong> que ocurra cada evento.
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="conteo_personal"><i class="fas fa-chart-bar"></i> Conteo de personal (momento)<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary chrono-btn" data-step-key="agradecimiento_y_cierre"><i class="fas fa-handshake"></i> Agradecimiento y cierre<span class="chrono-time">--:--:--</span></button>
            </div>
            <div class="alert alert-info text-center mt-3">
                <div><strong>Tiempo Total del Simulacro</strong></div>
                <div id="tiempo-total-display" style="font-size:1.3rem; font-weight:bold;">--:--:--</div>
                <small class="text-muted">Se calcula automaticamente (Cierre - Inicio)</small>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 8: Evaluacion ==================== -->
<div class="wizard-step" id="step-8">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-star"></i> Calificacion del Simulacro</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Alarma efectiva</label>
                    <select id="ev-alarma" class="form-select eval-field" required>
                        <option value="">-- Seleccione --</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Orden de evacuacion</label>
                    <select id="ev-orden" class="form-select eval-field" required>
                        <option value="">-- Seleccione --</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Liderazgo brigadistas</label>
                    <select id="ev-liderazgo" class="form-select eval-field" required>
                        <option value="">-- Seleccione --</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Organizacion punto de encuentro</label>
                    <select id="ev-organizacion" class="form-select eval-field" required>
                        <option value="">-- Seleccione --</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Participacion general</label>
                    <select id="ev-participacion" class="form-select eval-field" required>
                        <option value="">-- Seleccione --</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Evaluacion cuantitativa</label>
                    <input id="ev-cuantitativa" class="form-control" type="text" readonly placeholder="--/10">
                    <small class="text-muted">Promedio de los 5 criterios</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Evaluacion cualitativa</label>
                    <textarea id="ev-cualitativa" class="form-control" rows="3" placeholder="Ej.: Buen liderazgo y organizacion; reforzar orden de evacuacion."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PASO 9: Resumen y Envio ==================== -->
<div class="wizard-step" id="step-9">
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-check-circle"></i> Resumen Final del Simulacro</h5></div>
        <div class="card-body">
            <p class="text-muted">Revise todos los datos antes del envio final.</p>

            <details class="border rounded p-2 mb-2" open>
                <summary class="fw-semibold"><i class="fas fa-building"></i> Copropiedad</summary>
                <div id="sum-cliente" class="mt-2 small"></div>
            </details>

            <details class="border rounded p-2 mb-2">
                <summary class="fw-semibold"><i class="fas fa-info-circle"></i> Informacion General</summary>
                <div id="sum-info" class="mt-2 small"></div>
            </details>

            <details class="border rounded p-2 mb-2">
                <summary class="fw-semibold"><i class="fas fa-user-shield"></i> Brigadista Lider</summary>
                <div id="sum-brigadista" class="mt-2 small"></div>
            </details>

            <details class="border rounded p-2 mb-2">
                <summary class="fw-semibold"><i class="fas fa-stopwatch"></i> Cronograma</summary>
                <div id="sum-cronograma" class="mt-2 small"></div>
            </details>

            <details class="border rounded p-2 mb-2">
                <summary class="fw-semibold"><i class="fas fa-calculator"></i> Conteo de Evacuados</summary>
                <div id="sum-conteo" class="mt-2 small"></div>
            </details>

            <details class="border rounded p-2 mb-2">
                <summary class="fw-semibold"><i class="fas fa-camera"></i> Evidencias</summary>
                <div id="sum-evidencias" class="mt-2 small"></div>
            </details>

            <details class="border rounded p-2 mb-2">
                <summary class="fw-semibold"><i class="fas fa-star"></i> Evaluacion</summary>
                <div id="sum-evaluacion" class="mt-2 small"></div>
            </details>
        </div>
    </div>
</div>

<!-- Sticky navigation buttons (visible on steps 2-9) -->
<div class="sticky-actions d-flex gap-2" id="wizard-nav">
    <button type="button" class="btn btn-outline-secondary flex-fill" id="btn-prev"><i class="fas fa-arrow-left"></i> Atras</button>
    <button type="button" class="btn btn-brand flex-fill" id="btn-next">Siguiente <i class="fas fa-arrow-right"></i></button>
    <button type="button" class="btn btn-success flex-fill" id="btn-submit" style="display:none;"><i class="fas fa-paper-plane"></i> Enviar Evaluacion</button>
</div>

</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
    'use strict';

    const BASE = '<?= base_url() ?>';
    const TOTAL_STEPS = 9;
    let currentStep = 1;
    let recordId = null; // DB id

    // localStorage state
    const LS_KEY = 'simulacro_state_v2';
    const STATE = {
        load() { try { return JSON.parse(localStorage.getItem(LS_KEY) || '{}'); } catch(e) { return {}; } },
        save(data) { try { localStorage.setItem(LS_KEY, JSON.stringify(data)); } catch(e) {} },
        get(key) { return this.load()[key]; },
        set(key, val) { const d = this.load(); d[key] = val; this.save(d); },
        clear() { localStorage.removeItem(LS_KEY); }
    };

    // Restore state on load
    const savedState = STATE.load();
    if (savedState.recordId) recordId = savedState.recordId;
    if (savedState.currentStep) currentStep = savedState.currentStep;

    // ========== SELECT2 ==========
    $('#select-cliente').select2({
        placeholder: 'Busque su copropiedad...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: BASE + 'simulacro/api/clientes',
            dataType: 'json',
            delay: 300,
            processResults: function(data, params) {
                const term = (params.term || '').toLowerCase();
                const filtered = data.filter(c => c.nombre_cliente.toLowerCase().includes(term));
                return {
                    results: filtered.map(c => ({
                        id: c.id_cliente,
                        text: c.nombre_cliente
                    }))
                };
            },
            cache: true
        }
    }).on('change', function() {
        const val = $(this).val();
        document.getElementById('btn-step1-next').disabled = !val;
    });

    // ========== WIZARD NAVIGATION ==========
    function goToStep(step) {
        if (step < 1 || step > TOTAL_STEPS) return;
        currentStep = step;
        STATE.set('currentStep', currentStep);

        // Show/hide steps
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
        const target = document.getElementById('step-' + step);
        if (target) target.classList.add('active');

        // Update dots
        document.querySelectorAll('.step-dot').forEach(dot => {
            const s = parseInt(dot.dataset.step);
            dot.classList.remove('active', 'completed');
            if (s === step) dot.classList.add('active');
            else if (s < step) dot.classList.add('completed');
        });

        // Nav buttons
        const nav = document.getElementById('wizard-nav');
        const prevBtn = document.getElementById('btn-prev');
        const nextBtn = document.getElementById('btn-next');
        const submitBtn = document.getElementById('btn-submit');

        if (step === 1) {
            nav.style.display = 'none';
        } else {
            nav.style.display = 'flex';
            prevBtn.style.display = step > 1 ? '' : 'none';
            nextBtn.style.display = step < TOTAL_STEPS ? '' : 'none';
            submitBtn.style.display = step === TOTAL_STEPS ? '' : 'none';
        }

        // If going to resumen, populate summary
        if (step === TOTAL_STEPS) populateSummary();

        window.scrollTo(0, 0);
    }

    // Step 1 continue
    document.getElementById('btn-step1-next').addEventListener('click', function() {
        const clienteId = $('#select-cliente').val();
        if (!clienteId) return;

        saveStep(1, {
            id_cliente: parseInt(clienteId),
            fecha: new Date().toISOString().slice(0, 10)
        }, function() {
            STATE.set('clienteName', $('#select-cliente').select2('data')[0]?.text || '');
            goToStep(2);
        });
    });

    document.getElementById('btn-prev').addEventListener('click', function() {
        saveCurrentStep(function() {
            goToStep(currentStep - 1);
        });
    });

    document.getElementById('btn-next').addEventListener('click', function() {
        saveCurrentStep(function() {
            goToStep(currentStep + 1);
        });
    });

    document.getElementById('btn-submit').addEventListener('click', submitFinal);

    // ========== SAVE STEP (AJAX) ==========
    function saveStep(step, data, onSuccess) {
        const payload = { step: step, data: data, id: recordId };

        $.ajax({
            url: BASE + 'simulacro/save-step',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(resp) {
                if (resp.success) {
                    if (resp.id && !recordId) {
                        recordId = resp.id;
                        STATE.set('recordId', recordId);
                    }
                    if (onSuccess) onSuccess();
                } else {
                    Swal.fire('Error', resp.error || 'Error al guardar', 'error');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'Error de conexion';
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    function saveCurrentStep(onSuccess) {
        const data = collectStepData(currentStep);
        if (data === null) {
            // No data to save for this step (e.g., step 9)
            if (onSuccess) onSuccess();
            return;
        }
        saveStep(currentStep, data, onSuccess);
    }

    function collectStepData(step) {
        switch (step) {
            case 1:
                return { id_cliente: parseInt($('#select-cliente').val()), fecha: new Date().toISOString().slice(0, 10) };
            case 2:
                return {
                    direccion: v('f-direccion'),
                    evento_simulado: v('f-evento'),
                    alcance_simulacro: v('f-alcance'),
                    tipo_evacuacion: v('f-tipo-evacuacion'),
                    personal_no_evacua: v('f-personal-no-evacua'),
                    tipo_alarma: collectChecked('.alarma-opt'),
                    puntos_encuentro: v('f-puntos-encuentro'),
                    recurso_humano: v('f-recurso-humano'),
                    equipos_emergencia: collectChecked('.equipo-opt'),
                };
            case 3:
                return {
                    nombre_brigadista_lider: v('f-bl-nombre'),
                    email_brigadista_lider: v('f-bl-email'),
                    whatsapp_brigadista_lider: v('f-bl-whatsapp'),
                    distintivos_brigadistas: collectChecked('.distintivo-opt'),
                };
            case 4:
                return collectChronoData(['hora_inicio', 'alistamiento_recursos', 'asumir_roles', 'suena_alarma', 'distribucion_roles', 'llegada_punto_encuentro', 'agrupacion_por_afinidad']);
            case 5:
                return {
                    hombre: intVal('f-hombre'),
                    mujer: intVal('f-mujer'),
                    ninos: intVal('f-ninos'),
                    adultos_mayores: intVal('f-adultos-mayores'),
                    discapacidad: intVal('f-discapacidad'),
                    mascotas: intVal('f-mascotas'),
                    total: intVal('f-total'),
                };
            case 6:
                return { observaciones: v('f-observaciones') };
            case 7: {
                const d = collectChronoData(['conteo_personal', 'agradecimiento_y_cierre']);
                d.tiempo_total = document.getElementById('tiempo-total-display').textContent !== '--:--:--'
                    ? document.getElementById('tiempo-total-display').textContent : '';
                return d;
            }
            case 8:
                return {
                    alarma_efectiva: intVal('ev-alarma'),
                    orden_evacuacion: intVal('ev-orden'),
                    liderazgo_brigadistas: intVal('ev-liderazgo'),
                    organizacion_punto_encuentro: intVal('ev-organizacion'),
                    participacion_general: intVal('ev-participacion'),
                    evaluacion_cuantitativa: v('ev-cuantitativa'),
                    evaluacion_cualitativa: v('ev-cualitativa'),
                };
            case 9:
                return null; // Nothing to save
        }
        return null;
    }

    // ========== CHRONO BUTTONS ==========
    const chronoTimestamps = STATE.get('chrono') || {};

    document.querySelectorAll('.chrono-btn').forEach(btn => {
        const key = btn.dataset.stepKey;

        // Restore saved timestamps
        if (chronoTimestamps[key]) {
            markChronoButton(btn, chronoTimestamps[key]);
        }

        btn.addEventListener('click', function() {
            const now = new Date();
            const isoStr = now.toISOString();
            chronoTimestamps[key] = isoStr;
            STATE.set('chrono', chronoTimestamps);

            markChronoButton(btn, isoStr);
            recalcTiempoTotal();
        });
    });

    function markChronoButton(btn, isoStr) {
        const d = new Date(isoStr);
        const timeStr = pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds());
        btn.querySelector('.chrono-time').textContent = timeStr;
        btn.classList.add('recorded');
    }

    function collectChronoData(keys) {
        const data = {};
        keys.forEach(key => {
            if (chronoTimestamps[key]) {
                data[key] = chronoTimestamps[key].replace('T', ' ').substring(0, 19);
            }
        });
        return data;
    }

    function recalcTiempoTotal() {
        const inicio = chronoTimestamps['hora_inicio'];
        const cierre = chronoTimestamps['agradecimiento_y_cierre'];
        const el = document.getElementById('tiempo-total-display');
        if (inicio && cierre) {
            const ms = new Date(cierre) - new Date(inicio);
            if (ms > 0) {
                const h = Math.floor(ms / 3600000);
                const m = Math.floor((ms % 3600000) / 60000);
                const s = Math.floor((ms % 60000) / 1000);
                el.textContent = pad2(h) + ':' + pad2(m) + ':' + pad2(s);
                return;
            }
        }
        el.textContent = '--:--:--';
    }

    // ========== CONTEO AUTO-TOTAL ==========
    document.querySelectorAll('.conteo-field').forEach(input => {
        input.addEventListener('input', function() {
            const total = ['f-hombre', 'f-mujer', 'f-ninos', 'f-adultos-mayores', 'f-discapacidad', 'f-mascotas']
                .reduce((sum, id) => sum + intVal(id), 0);
            document.getElementById('f-total').value = total;
        });
    });

    // ========== EVALUACION AUTO-PROMEDIO ==========
    document.querySelectorAll('.eval-field').forEach(sel => {
        sel.addEventListener('change', function() {
            const vals = ['ev-alarma', 'ev-orden', 'ev-liderazgo', 'ev-organizacion', 'ev-participacion']
                .map(id => intVal(id))
                .filter(v => v > 0);

            if (vals.length > 0) {
                const avg = vals.reduce((a, b) => a + b, 0) / vals.length;
                document.getElementById('ev-cuantitativa').value = avg.toFixed(1) + '/10';

                // Auto-fill cualitativa label
                const cualEl = document.getElementById('ev-cualitativa');
                if (!cualEl.value.trim()) {
                    cualEl.value = getCualitativaLabel(avg);
                }
            }
        });
    });

    function getCualitativaLabel(score) {
        if (score >= 9.5) return 'Sobresaliente';
        if (score >= 8.0) return 'Muy bueno';
        if (score >= 6.5) return 'Bueno';
        if (score >= 5.0) return 'Aceptable';
        if (score >= 3.5) return 'Insuficiente';
        return 'Deficiente';
    }

    // ========== FOTOS ==========
    document.querySelectorAll('.btn-photo').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById(this.dataset.target).click();
        });
    });

    ['foto1', 'foto2'].forEach((prefix, idx) => {
        const campo = 'imagen_' + (idx + 1);

        ['input', 'camera'].forEach(suffix => {
            const el = document.getElementById(prefix + '-' + suffix);
            if (!el) return;
            el.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                // Preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-' + prefix);
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);

                // Upload
                if (!recordId) {
                    document.getElementById('upload-status-' + (idx + 1)).textContent = 'Guarde primero el paso 1';
                    return;
                }

                const fd = new FormData();
                fd.append('file', file);
                fd.append('id', recordId);
                fd.append('campo', campo);

                const statusEl = document.getElementById('upload-status-' + (idx + 1));
                statusEl.textContent = 'Subiendo...';

                $.ajax({
                    url: BASE + 'simulacro/upload-foto',
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        statusEl.textContent = resp.success ? 'Foto subida correctamente' : (resp.error || 'Error');
                        statusEl.className = 'form-text ' + (resp.success ? 'text-success' : 'text-danger');
                    },
                    error: function() {
                        statusEl.textContent = 'Error al subir la foto';
                        statusEl.className = 'form-text text-danger';
                    }
                });
            });
        });
    });

    // ========== SUBMIT FINAL ==========
    function submitFinal() {
        if (!recordId) {
            Swal.fire('Error', 'No se ha iniciado la evaluacion. Complete el paso 1.', 'error');
            return;
        }

        Swal.fire({
            title: 'Enviar evaluacion?',
            text: 'Una vez enviada, no podra modificar los datos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ff7a00',
            confirmButtonText: 'Si, enviar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Enviando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: BASE + 'simulacro/store',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id: recordId }),
                success: function(resp) {
                    Swal.close();
                    if (resp.success) {
                        STATE.clear();
                        Swal.fire({
                            title: 'Evaluacion enviada!',
                            text: 'Gracias por completar la evaluacion del simulacro.',
                            icon: 'success',
                            confirmButtonColor: '#ff7a00'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', resp.error || 'Error al enviar', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    const msg = xhr.responseJSON?.error || 'Error de conexion';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    }

    // ========== SUMMARY (Step 9) ==========
    function populateSummary() {
        const clienteName = STATE.get('clienteName') || $('#select-cliente').select2('data')[0]?.text || '';
        setHtml('sum-cliente', `<strong>${esc(clienteName)}</strong>`);

        setHtml('sum-info', `
            <div><strong>Direccion:</strong> ${esc(v('f-direccion'))}</div>
            <div><strong>Evento:</strong> ${esc(v('f-evento'))}</div>
            <div><strong>Alcance:</strong> ${esc(v('f-alcance'))}</div>
            <div><strong>Tipo evacuacion:</strong> ${esc(v('f-tipo-evacuacion'))}</div>
            <div><strong>Personal no evacua:</strong> ${esc(v('f-personal-no-evacua'))}</div>
            <div><strong>Tipo alarma:</strong> ${esc(collectChecked('.alarma-opt'))}</div>
            <div><strong>Puntos encuentro:</strong> ${esc(v('f-puntos-encuentro'))}</div>
            <div><strong>Recurso humano:</strong> ${esc(v('f-recurso-humano'))}</div>
            <div><strong>Equipos emergencia:</strong> ${esc(collectChecked('.equipo-opt'))}</div>
        `);

        setHtml('sum-brigadista', `
            <div><strong>Nombre:</strong> ${esc(v('f-bl-nombre'))}</div>
            <div><strong>Email:</strong> ${esc(v('f-bl-email'))}</div>
            <div><strong>WhatsApp:</strong> ${esc(v('f-bl-whatsapp'))}</div>
            <div><strong>Distintivos:</strong> ${esc(collectChecked('.distintivo-opt'))}</div>
        `);

        // Cronograma
        const chronoKeys = [
            ['hora_inicio', 'Hora de Inicio'],
            ['alistamiento_recursos', 'Alistamiento de Recursos'],
            ['asumir_roles', 'Asumir roles'],
            ['suena_alarma', 'Suena alarma'],
            ['distribucion_roles', 'Distribucion de roles'],
            ['llegada_punto_encuentro', 'Llegada punto de encuentro'],
            ['agrupacion_por_afinidad', 'Agrupacion por afinidad'],
            ['conteo_personal', 'Conteo de personal'],
            ['agradecimiento_y_cierre', 'Agradecimiento y cierre'],
        ];
        let chronoHtml = '';
        chronoKeys.forEach(([key, label]) => {
            const ts = chronoTimestamps[key];
            const timeStr = ts ? formatTime(new Date(ts)) : '--:--:--';
            chronoHtml += `<div><strong>${esc(label)}:</strong> ${timeStr}</div>`;
        });
        const ttEl = document.getElementById('tiempo-total-display');
        chronoHtml += `<div class="mt-2 fw-bold">Tiempo Total: ${ttEl ? ttEl.textContent : '--:--:--'}</div>`;
        setHtml('sum-cronograma', chronoHtml);

        setHtml('sum-conteo', `
            <div>Hombres: ${v('f-hombre')} | Mujeres: ${v('f-mujer')} | Ninos: ${v('f-ninos')}</div>
            <div>Adultos mayores: ${v('f-adultos-mayores')} | Discapacidad: ${v('f-discapacidad')} | Mascotas: ${v('f-mascotas')}</div>
            <div class="fw-bold">Total: ${v('f-total')}</div>
        `);

        // Evidencias
        let evHtml = '<div>' + (v('f-observaciones') ? '<strong>Observaciones:</strong> ' + esc(v('f-observaciones')) : 'Sin observaciones') + '</div>';
        const p1 = document.getElementById('preview-foto1');
        const p2 = document.getElementById('preview-foto2');
        if (p1 && p1.style.display !== 'none') evHtml += '<img src="' + p1.src + '" style="max-width:120px; margin-top:4px; border-radius:4px;">';
        if (p2 && p2.style.display !== 'none') evHtml += '<img src="' + p2.src + '" style="max-width:120px; margin-top:4px; border-radius:4px; margin-left:8px;">';
        setHtml('sum-evidencias', evHtml);

        setHtml('sum-evaluacion', `
            <div><strong>Alarma efectiva:</strong> ${v('ev-alarma')}/10</div>
            <div><strong>Orden evacuacion:</strong> ${v('ev-orden')}/10</div>
            <div><strong>Liderazgo:</strong> ${v('ev-liderazgo')}/10</div>
            <div><strong>Organizacion punto encuentro:</strong> ${v('ev-organizacion')}/10</div>
            <div><strong>Participacion general:</strong> ${v('ev-participacion')}/10</div>
            <div class="mt-1 fw-bold">Cuantitativa: ${v('ev-cuantitativa')}</div>
            <div><strong>Cualitativa:</strong> ${esc(v('ev-cualitativa'))}</div>
        `);
    }

    // ========== HELPERS ==========
    function v(id) { const el = document.getElementById(id); return el ? el.value : ''; }
    function intVal(id) { return parseInt(v(id)) || 0; }
    function pad2(n) { return String(n).padStart(2, '0'); }
    function formatTime(d) { return pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds()); }
    function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
    function setHtml(id, html) { const el = document.getElementById(id); if (el) el.innerHTML = html; }
    function collectChecked(selector) {
        return Array.from(document.querySelectorAll(selector + ':checked')).map(i => i.value).join(', ');
    }

    // Init
    recalcTiempoTotal();
    goToStep(currentStep);

})();
</script>
</body>
</html>
