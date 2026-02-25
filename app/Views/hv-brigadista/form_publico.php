<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ff7a00">
    <title>Hoja de Vida Brigadista</title>
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

        .select2-container .select2-selection--single {
            height: calc(2.5rem + 2px); border: 1px solid #ced4da; border-radius: 0.375rem; padding: 0.375rem 0.75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(2.5rem); padding-left: 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.5rem);
        }

        .header-logo { height: 40px; width: auto; max-width: 80px; object-fit: contain; }
        @media (min-width: 768px) { .header-logo { height: 50px; max-width: 100px; } }

        .section-card { margin-bottom: 16px; }
        .section-card .card-header { font-weight: 600; font-size: 14px; }

        /* Firma canvas */
        .firma-canvas {
            border: 2px dashed #ccc;
            border-radius: 8px;
            background: #fafafa;
            touch-action: none;
            cursor: crosshair;
            width: 100%;
            height: 180px;
        }

        /* Photo preview */
        .photo-preview-img { max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; margin-top: 8px; }

        /* Estudio row */
        .estudio-row { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 12px; margin-bottom: 8px; position: relative; }
        .btn-remove-estudio { position: absolute; top: 4px; right: 8px; }

        /* Required asterisk */
        .req::after { content: ' *'; color: #dc3545; }
    </style>
</head>
<body>

<!-- Header -->
<nav class="navbar bg-brand">
    <div class="container d-flex justify-content-between align-items-center">
        <img src="https://res.cloudinary.com/drcecuc36/image/upload/v1758225634/CYCLOID_TALENT-sin_fondo_n5tkzh.png" alt="Cycloid" class="header-logo">
        <span class="navbar-brand text-white mx-auto text-center mb-0" style="font-size:15px;">Hoja de Vida Brigadista</span>
        <img src="https://res.cloudinary.com/drcecuc36/image/upload/v1758225634/LOGO_SST_gelepu.png" alt="SST" class="header-logo">
    </div>
</nav>

<div class="container py-3" style="max-width: 700px;">

    <!-- Seccion 1: Selector Copropiedad -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-building"></i> Seleccion de Copropiedad</div>
        <div class="card-body">
            <label class="form-label req">Busque su copropiedad</label>
            <select id="clienteSelect" class="form-select" style="width:100%;">
                <option value="">-- Seleccione o busque --</option>
            </select>
        </div>
    </div>

    <!-- Seccion 2: Datos Personales -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-user"></i> Datos Personales</div>
        <div class="card-body">
            <div class="mb-2">
                <label class="form-label req">Nombre completo</label>
                <input type="text" id="nombre_completo" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label req">Documento de identidad</label>
                <input type="text" id="documento_identidad" class="form-control" pattern="[0-9]+" inputmode="numeric" maxlength="12" placeholder="Ej: 1234567890">
            </div>
            <div class="mb-2">
                <label class="form-label req">Fecha de nacimiento</label>
                <div class="input-group">
                    <input type="text" id="f_nacimiento_txt" class="form-control" placeholder="dd/mm/yyyy" maxlength="10" pattern="\d{2}/\d{2}/\d{4}">
                    <input type="date" id="f_nacimiento_date" class="form-control" style="display:none;">
                    <button type="button" id="btnToggleDateMode" class="btn btn-outline-secondary"><i class="fas fa-calendar-alt"></i></button>
                </div>
                <input type="hidden" id="f_nacimiento_iso">
                <small id="dateHint" class="text-muted">Escriba dd/mm/yyyy</small>
            </div>
            <div class="mb-2">
                <label class="form-label req">Email</label>
                <input type="email" id="email" class="form-control" placeholder="nombre@email.com">
            </div>
            <div class="mb-2">
                <label class="form-label req">Telefono</label>
                <input type="text" id="telefono" class="form-control" pattern="[0-9]{10}" inputmode="tel" maxlength="10" placeholder="Ej: 3001234567">
            </div>
            <div class="mb-2">
                <label class="form-label req">Direccion de residencia</label>
                <input type="text" id="direccion_residencia" class="form-control">
            </div>
            <div class="mb-2">
                <label class="form-label req">EPS</label>
                <input type="text" id="eps" class="form-control">
            </div>
            <div class="mb-2">
                <label class="form-label req">Tipo de sangre (RH)</label>
                <select id="rh" class="form-select">
                    <option value="">-- Seleccionar --</option>
                    <option value="O+">O+</option><option value="O-">O-</option>
                    <option value="A+">A+</option><option value="A-">A-</option>
                    <option value="B+">B+</option><option value="B-">B-</option>
                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                </select>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label req">Peso (kg)</label>
                    <input type="number" id="peso" class="form-control" step="0.1" min="20" max="300">
                </div>
                <div class="col-6">
                    <label class="form-label req">Estatura (cm)</label>
                    <input type="number" id="estatura" class="form-control" step="0.1" min="80" max="250">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label req">Edad</label>
                <input type="number" id="edad" class="form-control" readonly style="background:#e9ecef;">
                <small class="text-muted">Se calcula automaticamente desde la fecha de nacimiento</small>
            </div>
        </div>
    </div>

    <!-- Seccion 3: Estudios -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-graduation-cap"></i> Estudios relacionados con Respuesta ante Emergencias</div>
        <div class="card-body">
            <div id="estudiosContainer">
                <div class="estudio-row">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-estudio"><i class="fas fa-times"></i></button>
                    <div class="mb-2">
                        <label class="form-label">Que estudio?</label>
                        <input type="text" class="form-control estudio-nombre" placeholder="Ej: Primeros Auxilios, Rescate en Alturas...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Donde lo estudio?</label>
                        <input type="text" class="form-control estudio-institucion" placeholder="Ej: Cruz Roja, SENA, Universidad...">
                    </div>
                    <div>
                        <label class="form-label">En que anio?</label>
                        <input type="number" class="form-control estudio-anio" placeholder="Ej: 2020" min="1950" max="2030">
                    </div>
                </div>
            </div>
            <button type="button" id="btnAddEstudio" class="btn btn-sm btn-outline-success mt-1"><i class="fas fa-plus"></i> Agregar otro estudio</button>
            <small class="text-muted d-block mt-1">Maximo 3 estudios</small>
        </div>
    </div>

    <!-- Seccion 4: Salud -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-heartbeat"></i> Informacion de Salud</div>
        <div class="card-body">
            <div class="mb-2">
                <label class="form-label req">Enfermedades importantes</label>
                <textarea id="enfermedades_importantes" class="form-control" rows="2" placeholder="Indica si tienes alguna enfermedad importante o escribe 'Ninguna'"></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label req">Medicamentos</label>
                <textarea id="medicamentos" class="form-control" rows="2" placeholder="Indica si tomas medicamentos o escribe 'Ninguno'"></textarea>
            </div>
        </div>
    </div>

    <!-- Seccion 5: Cuestionario Medico -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-notes-medical"></i> Cuestionario Medico</div>
        <div class="card-body">
            <p class="text-muted" style="font-size:13px;">Responda SI o NO a cada pregunta</p>

            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">1. Alguna vez su doctor le ha dicho que tenga alguna enfermedad cardiaca y le ha recomendado que solo practique ejercicio medicamente prescrito?</label>
                <select id="cardiaca" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">2. Siente dolor en el pecho cuando hace alguna actividad fisica?</label>
                <select id="pechoactividad" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">3. En el mes pasado ha tenido dolor en el pecho en reposo?</label>
                <select id="dolorpecho" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">4. Pierde usted el equilibrio por mareo o ha perdido la conciencia alguna vez?</label>
                <select id="conciencia" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">5. Tiene usted algun problema de los huesos o articulaciones que pudieran empeorar por un cambio en la actividad fisica?</label>
                <select id="huesos" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">6. Esta su medico prescribiendo actualmente medicamentos para la presion arterial, diabetes o alguna condicion cardiaca?</label>
                <select id="medicamentos_bool" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">7. Conoce alguna otra razon por la cual usted no pueda realizar actividad fisica?</label>
                <select id="actividadfisica" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">8. Ha sufrido o sufre de convulsiones o epilepsia?</label>
                <select id="convulsiones" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">9. Sufre o ha sufrido de vertigo?</label>
                <select id="vertigo" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">10. Ha sufrido de enfermedades en los oidos?</label>
                <select id="oidos" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">11. Ha sufrido de miedo a los lugares cerrados (ascensores, cuartos pequenos)?</label>
                <select id="lugarescerrados" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">12. Ha sufrido de miedo a las alturas (aviones, puentes peatonales, terrazas)?</label>
                <select id="miedoalturas" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">13. Hace en la semana ejercicio?</label>
                <select id="haceejercicio" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:13px;">14. Sufre de miedo al ver sangre?</label>
                <select id="miedo_ver_sangre" class="form-select form-select-sm"><option value="">--</option><option value="SI">SI</option><option value="NO">NO</option></select>
            </div>
        </div>
    </div>

    <!-- Seccion 6: Extras Salud -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-running"></i> Restricciones y Actividad Fisica</div>
        <div class="card-body">
            <div class="mb-2">
                <label class="form-label req">Restricciones medicas</label>
                <textarea id="restricciones_medicas" class="form-control" rows="2" placeholder="Indica si tienes restricciones medicas o escribe 'Ninguna'"></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label req">Deportes y horas por semana</label>
                <textarea id="deporte_semana" class="form-control" rows="2" placeholder="Indica que deportes practicas y cuantas horas por semana, o escribe 'Ninguno'"></textarea>
            </div>
        </div>
    </div>

    <!-- Seccion 7: Foto -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-camera"></i> Foto del Brigadista</div>
        <div class="card-body">
            <div class="photo-input-group">
                <input type="file" id="inputFoto" class="file-preview" accept="image/*" style="display:none;">
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera flex-fill"><i class="fas fa-camera"></i> Camara</button>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery flex-fill"><i class="fas fa-images"></i> Galeria</button>
                </div>
                <div class="preview-img text-center">
                    <img id="fotoPreview" class="photo-preview-img" style="display:none;">
                </div>
                <button type="button" id="btnClearFoto" class="btn btn-sm btn-outline-danger mt-1" style="display:none;"><i class="fas fa-trash"></i> Borrar foto</button>
            </div>
        </div>
    </div>

    <!-- Seccion 8: Firma -->
    <div class="card section-card">
        <div class="card-header"><i class="fas fa-signature"></i> Firma del Brigadista</div>
        <div class="card-body">
            <p class="text-muted mb-2" style="font-size:13px;">Dibuje su firma en el recuadro</p>
            <canvas id="canvasFirma" class="firma-canvas"></canvas>
            <button type="button" id="btnLimpiarFirma" class="btn btn-sm btn-outline-secondary mt-2"><i class="fas fa-eraser"></i> Limpiar firma</button>
        </div>
    </div>

    <!-- Seccion 9: Enviar -->
    <div class="card section-card">
        <div class="card-body text-center">
            <button type="button" id="btnEnviar" class="btn btn-brand btn-lg w-100" style="font-size:18px; padding: 14px;">
                <i class="fas fa-paper-plane"></i> Enviar Hoja de Vida
            </button>
            <div id="statusMsg" class="mt-2"></div>
        </div>
    </div>

</div>

<script>
const BASE = '<?= base_url() ?>/';

// ===== SELECT2: Clientes activos =====
$.ajax({
    url: BASE + 'hv-brigadista/api/clientes',
    dataType: 'json',
    success: function(data) {
        const sel = $('#clienteSelect');
        data.forEach(c => {
            sel.append(`<option value="${c.id_cliente}">${c.nombre_cliente}</option>`);
        });
        sel.select2({ placeholder: '-- Busque su copropiedad --', allowClear: true, width: '100%' });
    }
});

// ===== FECHA NACIMIENTO: dd/mm/yyyy con toggle calendario =====
const txtDate = document.getElementById('f_nacimiento_txt');
const calDate = document.getElementById('f_nacimiento_date');
const isoDate = document.getElementById('f_nacimiento_iso');
const edadInput = document.getElementById('edad');
let isCalMode = false;

document.getElementById('btnToggleDateMode').addEventListener('click', function() {
    isCalMode = !isCalMode;
    if (isCalMode) {
        txtDate.style.display = 'none';
        calDate.style.display = 'block';
        document.getElementById('dateHint').textContent = 'Seleccione fecha del calendario';
    } else {
        txtDate.style.display = 'block';
        calDate.style.display = 'none';
        document.getElementById('dateHint').textContent = 'Escriba dd/mm/yyyy';
        txtDate.focus();
    }
});

txtDate.addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2);
    if (v.length >= 5) v = v.substring(0,5) + '/' + v.substring(5,9);
    this.value = v;
});

txtDate.addEventListener('blur', function() {
    const match = this.value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) { isoDate.value = ''; edadInput.value = ''; return; }
    const [, dd, mm, yyyy] = match;
    const d = new Date(yyyy, mm - 1, dd);
    if (d.getFullYear() != yyyy || d.getMonth() != mm - 1 || d.getDate() != dd) {
        this.classList.add('is-invalid'); isoDate.value = ''; edadInput.value = ''; return;
    }
    this.classList.remove('is-invalid');
    const iso = `${yyyy}-${mm}-${dd}`;
    isoDate.value = iso;
    calDate.value = iso;
    edadInput.value = calcEdad(iso);
});

calDate.addEventListener('change', function() {
    if (!this.value) return;
    isoDate.value = this.value;
    const [y, m, d] = this.value.split('-');
    txtDate.value = `${d}/${m}/${y}`;
    txtDate.classList.remove('is-invalid');
    edadInput.value = calcEdad(this.value);
    // Volver a modo texto
    setTimeout(() => { isCalMode = true; document.getElementById('btnToggleDateMode').click(); }, 300);
});

function calcEdad(isoStr) {
    if (!isoStr) return '';
    const b = new Date(isoStr), t = new Date();
    let e = t.getFullYear() - b.getFullYear();
    const m = t.getMonth() - b.getMonth();
    if (m < 0 || (m === 0 && t.getDate() < b.getDate())) e--;
    return e >= 0 ? e : '';
}

// ===== ESTUDIOS DINAMICOS (max 3) =====
document.getElementById('btnAddEstudio').addEventListener('click', function() {
    const container = document.getElementById('estudiosContainer');
    if (container.querySelectorAll('.estudio-row').length >= 3) {
        Swal.fire('Maximo 3 estudios', '', 'info');
        return;
    }
    const div = document.createElement('div');
    div.className = 'estudio-row';
    div.innerHTML = `
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-estudio"><i class="fas fa-times"></i></button>
        <div class="mb-2"><label class="form-label">Que estudio?</label><input type="text" class="form-control estudio-nombre" placeholder="Ej: Primeros Auxilios, Rescate en Alturas..."></div>
        <div class="mb-2"><label class="form-label">Donde lo estudio?</label><input type="text" class="form-control estudio-institucion" placeholder="Ej: Cruz Roja, SENA, Universidad..."></div>
        <div><label class="form-label">En que anio?</label><input type="number" class="form-control estudio-anio" placeholder="Ej: 2020" min="1950" max="2030"></div>
    `;
    container.appendChild(div);
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-remove-estudio')) {
        const row = e.target.closest('.estudio-row');
        const container = document.getElementById('estudiosContainer');
        if (container.querySelectorAll('.estudio-row').length > 1) {
            row.remove();
        } else {
            // Limpiar en vez de eliminar la ultima
            row.querySelectorAll('input').forEach(i => i.value = '');
        }
    }
});

function collectEstudios() {
    const rows = document.querySelectorAll('.estudio-row');
    const estudios = [];
    rows.forEach(row => {
        const nombre = row.querySelector('.estudio-nombre').value.trim();
        const inst = row.querySelector('.estudio-institucion').value.trim();
        const anio = row.querySelector('.estudio-anio').value;
        if (nombre || inst || anio) {
            estudios.push({ nombre, institucion: inst, anio: anio ? Number(anio) : '' });
        }
    });
    return estudios;
}

// ===== FOTO: patron camara/galeria (2 botones) =====
const inputFoto = document.getElementById('inputFoto');
const fotoPreview = document.getElementById('fotoPreview');
const btnClearFoto = document.getElementById('btnClearFoto');

document.addEventListener('click', function(e) {
    const cameraBtn = e.target.closest('.btn-photo-camera');
    const galleryBtn = e.target.closest('.btn-photo-gallery');
    if (!cameraBtn && !galleryBtn) return;
    const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
    const input = group.querySelector('input[type="file"]');
    if (cameraBtn) {
        input.setAttribute('capture', 'environment');
    } else {
        input.removeAttribute('capture');
    }
    input.click();
});

inputFoto.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            fotoPreview.src = ev.target.result;
            fotoPreview.style.display = 'block';
            btnClearFoto.style.display = 'inline-block';
        };
        reader.readAsDataURL(this.files[0]);
    }
});

btnClearFoto.addEventListener('click', function() {
    inputFoto.value = '';
    fotoPreview.src = '';
    fotoPreview.style.display = 'none';
    this.style.display = 'none';
});

// ===== FIRMA: Canvas HTML5 =====
const canvas = document.getElementById('canvasFirma');
const ctx = canvas.getContext('2d');
let dibujando = false;
let tieneContenidoCanvas = false;

function initCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = 180;
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}
initCanvas();
window.addEventListener('resize', function() { if (!tieneContenidoCanvas) initCanvas(); });

function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX || e.touches[0].clientX) - rect.left;
    const y = (e.clientY || e.touches[0].clientY) - rect.top;
    return { x, y };
}

function iniciarDibujo(e) {
    dibujando = true;
    const pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
    e.preventDefault();
}

function dibujar(e) {
    if (!dibujando) return;
    const pos = getPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
    tieneContenidoCanvas = true;
    e.preventDefault();
}

function terminarDibujo() { dibujando = false; }

canvas.addEventListener('mousedown', iniciarDibujo);
canvas.addEventListener('mousemove', dibujar);
canvas.addEventListener('mouseup', terminarDibujo);
canvas.addEventListener('mouseleave', terminarDibujo);
// Touch: multi-touch protection
canvas.addEventListener('touchstart', function(e) {
    if (e.touches.length > 1) return;
    iniciarDibujo(e);
});
canvas.addEventListener('touchmove', function(e) {
    if (e.touches.length > 1) { terminarDibujo(); return; }
    dibujar(e);
});
canvas.addEventListener('touchend', terminarDibujo);

document.getElementById('btnLimpiarFirma').addEventListener('click', function() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    tieneContenidoCanvas = false;
});

// Validar pixeles oscuros de la firma (min 100)
function contarPixelesOscuros() {
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;
    let count = 0;
    for (let i = 0; i < data.length; i += 4) {
        if (data[i] < 128 && data[i+1] < 128 && data[i+2] < 128 && data[i+3] > 50) count++;
    }
    return count;
}

// ===== ENVIAR =====
document.getElementById('btnEnviar').addEventListener('click', function() {
    const btn = this;

    // Validar campos requeridos
    const missingFields = [];
    const clienteId = $('#clienteSelect').val();
    if (!clienteId) missingFields.push('Copropiedad');

    const camposRequeridos = {
        'nombre_completo': 'Nombre completo',
        'documento_identidad': 'Documento de identidad',
        'email': 'Email',
        'telefono': 'Telefono',
        'direccion_residencia': 'Direccion',
        'eps': 'EPS',
        'rh': 'Tipo de sangre',
        'peso': 'Peso',
        'estatura': 'Estatura',
        'enfermedades_importantes': 'Enfermedades importantes',
        'medicamentos': 'Medicamentos',
        'restricciones_medicas': 'Restricciones medicas',
        'deporte_semana': 'Deportes y horas/semana',
    };

    Object.entries(camposRequeridos).forEach(([id, label]) => {
        const el = document.getElementById(id);
        if (!el || !el.value.trim()) {
            missingFields.push(label);
            if (el) el.classList.add('is-invalid');
        } else if (el) {
            el.classList.remove('is-invalid');
        }
    });

    if (!document.getElementById('f_nacimiento_iso').value) missingFields.push('Fecha de nacimiento');

    // 14 preguntas medicas
    const medQ = ['cardiaca','pechoactividad','dolorpecho','conciencia','huesos','medicamentos_bool',
        'actividadfisica','convulsiones','vertigo','oidos','lugarescerrados','miedoalturas','haceejercicio','miedo_ver_sangre'];
    let medFaltantes = 0;
    medQ.forEach(id => {
        const el = document.getElementById(id);
        if (!el || !el.value) { medFaltantes++; if (el) el.classList.add('is-invalid'); }
        else if (el) el.classList.remove('is-invalid');
    });
    if (medFaltantes > 0) missingFields.push('Cuestionario medico (' + medFaltantes + ' respuestas faltantes)');

    // Foto
    if (!inputFoto.files || !inputFoto.files[0]) missingFields.push('Foto del brigadista');

    // Firma
    if (!tieneContenidoCanvas) missingFields.push('Firma');

    if (missingFields.length > 0) {
        Swal.fire({
            title: 'Campos faltantes',
            html: '<ul style="text-align:left; font-size:14px;">' + missingFields.map(f => '<li>' + f + '</li>').join('') + '</ul>',
            icon: 'warning',
            confirmButtonColor: '#ff7a00',
        });
        return;
    }

    // Validar pixeles firma
    if (contarPixelesOscuros() < 100) {
        Swal.fire('Firma muy pequena', 'Por favor dibuje una firma mas visible', 'warning');
        return;
    }

    // SweetAlert2 confirmacion con preview de firma
    const firmaDataUrl = canvas.toDataURL('image/png');
    Swal.fire({
        title: 'Confirmar envio',
        html: '<p style="font-size:14px;">Su firma quedara registrada asi:</p>' +
              '<img src="' + firmaDataUrl + '" style="max-width:100%; max-height:120px; border:1px solid #ccc; border-radius:8px;">',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ff7a00',
    }).then(result => {
        if (!result.isConfirmed) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        const fd = new FormData();
        fd.append('id_cliente', clienteId);
        fd.append('fecha_inscripcion', new Date().toISOString().slice(0,10));
        fd.append('nombre_completo', document.getElementById('nombre_completo').value.trim());
        fd.append('documento_identidad', document.getElementById('documento_identidad').value.trim());
        fd.append('f_nacimiento', document.getElementById('f_nacimiento_iso').value);
        fd.append('email', document.getElementById('email').value.trim());
        fd.append('telefono', document.getElementById('telefono').value.trim());
        fd.append('direccion_residencia', document.getElementById('direccion_residencia').value.trim());
        fd.append('edad', document.getElementById('edad').value);
        fd.append('eps', document.getElementById('eps').value.trim());
        fd.append('peso', document.getElementById('peso').value);
        fd.append('estatura', document.getElementById('estatura').value);
        fd.append('rh', document.getElementById('rh').value);

        // Estudios como JSON
        fd.append('estudios', JSON.stringify(collectEstudios()));

        // Salud
        fd.append('enfermedades_importantes', document.getElementById('enfermedades_importantes').value.trim());
        fd.append('medicamentos', document.getElementById('medicamentos').value.trim());

        // 14 preguntas
        medQ.forEach(id => fd.append(id, document.getElementById(id).value));

        // Extras
        fd.append('restricciones_medicas', document.getElementById('restricciones_medicas').value.trim());
        fd.append('deporte_semana', document.getElementById('deporte_semana').value.trim());

        // Foto (File)
        fd.append('foto_brigadista', inputFoto.files[0]);

        // Firma (base64)
        fd.append('firma_imagen', firmaDataUrl);

        $.ajax({
            url: BASE + 'hv-brigadista/store',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({
                        title: 'Enviado!',
                        text: resp.message || 'Hoja de vida registrada exitosamente',
                        icon: 'success',
                        confirmButtonColor: '#ff7a00',
                        allowOutsideClick: false,
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', resp.error || 'Error al guardar', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Hoja de Vida';
                }
            },
            error: function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.error : 'Error de conexion';
                Swal.fire('Error', err, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Hoja de Vida';
            }
        });
    });
});
</script>
</body>
</html>
