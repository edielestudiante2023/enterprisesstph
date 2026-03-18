<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FT-SST-225 — Programa de Limpieza y Desinfección</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #0d1117;
    color: #e6edf3;
    font-size: 18px;
    line-height: 1.6;
}
.doc-header {
    background: #1c2437;
    border-bottom: 4px solid #bd9751;
    padding: 28px 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
}
.doc-header h1 { font-size: 26px; color: #fff; }
.doc-header .code-badge {
    background: #bd9751;
    color: #1c2437;
    font-weight: bold;
    font-size: 20px;
    padding: 8px 20px;
    border-radius: 6px;
}
.nav-bar {
    background: #161b22;
    padding: 12px 48px;
    border-bottom: 1px solid #30363d;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}
.nav-bar a {
    color: #8b949e;
    text-decoration: none;
    font-size: 14px;
    padding: 4px 10px;
    border-radius: 4px;
    transition: all .2s;
}
.nav-bar a:hover { background: #21262d; color: #e6edf3; }
.nav-bar .sep { color: #30363d; }
.content { padding: 48px; max-width: 1400px; margin: 0 auto; }
.section {
    background: #161b22;
    border: 1px solid #30363d;
    border-radius: 10px;
    margin-bottom: 24px;
    overflow: hidden;
}
.section-header {
    padding: 22px 32px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
    transition: background .2s;
}
.section-header:hover { background: #1c2437; }
.section-header h2 {
    font-size: 20px;
    color: #bd9751;
    font-weight: 600;
}
.section-header .badges { display: flex; gap: 8px; align-items: center; }
.badge {
    font-size: 12px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.badge-static  { background: #21262d; color: #8b949e; border: 1px solid #30363d; }
.badge-ai      { background: #1f3a2e; color: #3fb950; border: 1px solid #238636; }
.badge-data    { background: #1f2d3f; color: #58a6ff; border: 1px solid #1f6feb; }
.badge-arrow   { color: #8b949e; font-size: 18px; transition: transform .2s; }
.badge-arrow.open { transform: rotate(180deg); }
.section-body {
    padding: 28px 32px;
    border-top: 1px solid #30363d;
    font-size: 16px;
    color: #c9d1d9;
    display: none;
}
.section-body.open { display: block; }
.section-body p {
    margin-bottom: 14px;
    text-align: justify;
}
.section-body ul, .section-body ol {
    margin: 8px 0 16px 28px;
}
.section-body li { margin-bottom: 6px; }
.sub-section {
    background: #0d1117;
    border: 1px solid #21262d;
    border-radius: 6px;
    margin: 14px 0;
    padding: 18px 24px;
}
.sub-section h3 {
    color: #f0a84c;
    font-size: 15px;
    margin-bottom: 10px;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    margin: 12px 0;
}
.data-table th {
    background: #1c2437;
    color: #bd9751;
    padding: 10px 12px;
    text-align: left;
    border: 1px solid #30363d;
}
.data-table td {
    padding: 8px 12px;
    border: 1px solid #21262d;
    vertical-align: top;
}
.data-table tr:nth-child(even) td { background: #0d1117; }
.ai-note {
    background: #1f3a2e;
    border: 1px solid #238636;
    border-radius: 6px;
    padding: 12px 16px;
    margin: 14px 0;
    font-size: 14px;
    color: #3fb950;
}
.ai-note strong { color: #7ee787; }
.legend {
    display: flex;
    gap: 20px;
    align-items: center;
    font-size: 14px;
}
</style>
</head>
<body>

<div class="doc-header">
    <div>
        <h1><span style="color:#bd9751">📋</span> Programa de Limpieza y Desinfección</h1>
        <div style="color:#8b949e; font-size:15px; margin-top:4px;">Plan de Saneamiento Básico — Propiedad Horizontal</div>
    </div>
    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
        <div class="code-badge">FT-SST-225</div>
        <div class="legend">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge badge-data">Dato del cliente</span>
            <span class="badge badge-ai">🤖 Candidato IA</span>
        </div>
    </div>
</div>

<div class="nav-bar">
    <strong style="color:#bd9751;font-size:14px;">Secciones:</strong>
    <span class="sep">|</span>
    <a href="#s11">1.1 Objetivo</a>
    <a href="#s12">1.2 Alcance</a>
    <a href="#s13">1.3 Definiciones</a>
    <a href="#s14">1.4 Responsables</a>
    <a href="#s15">1.5 EPP</a>
    <a href="#s16">1.6 Insumos</a>
    <a href="#s17">1.7 Técnicas</a>
    <a href="#s18">1.8 Procedimiento</a>
    <a href="#s19">1.9 Baños</a>
    <a href="#s110">1.10 Partes altas</a>
    <a href="#s111">1.11 Frecuencia</a>
    <a href="#s112">1.12 Registros</a>
    <a href="#s113">1.13 Control</a>
    <a href="#s114">1.14 Indicadores</a>
    <span class="sep">|</span>
    <a href="javascript:openAll()" style="color:#bd9751">▶ Expandir todo</a>
    <a href="javascript:closeAll()" style="color:#8b949e">▼ Colapsar todo</a>
</div>

<div class="content">

<!-- 1.1 -->
<div class="section" id="s11">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.1 OBJETIVO</h2>
        <div class="badges">
            <span class="badge badge-ai">🤖 Candidato IA</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>Definir, planificar y establecer las actividades, insumos, recursos humanos, EPP, sustancias químicas autorizadas, responsabilidades, frecuencias, mecanismos de control e indicadores para ejecutar de manera segura, eficiente y continua las labores de limpieza y desinfección en las áreas comunes del <strong>[NOMBRE DEL CONJUNTO]</strong>.</p>
        <p>Se desarrolla en cumplimiento de la Ley 9 de 1979, Ley 675 de 2001, Decreto 1072 de 2015 (SG-SST) y demás normas sanitarias vigentes.</p>
        <div class="ai-note">
            <strong>🤖 Potencial IA:</strong> El objetivo podría personalizarse con características específicas del conjunto (número de torres, tipo de zonas comunes, particularidades sanitarias).
        </div>
    </div>
</div>

<!-- 1.2 -->
<div class="section" id="s12">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.2 ALCANCE</h2>
        <div class="badges">
            <span class="badge badge-ai">🤖 Candidato IA</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>Aplica a todas las áreas comunes del <strong>[NOMBRE DEL CONJUNTO]</strong>, bajo responsabilidad de la Administración. Incluye personal propio y empresas contratistas.</p>
        <p><strong>Áreas cubiertas:</strong> Zonas comunes, pasillos, escaleras, rampas, pocetas, andenes, cuartos de almacenamiento, contenedores, oficina de administración, baños, salón social, unidad de residuos, techos, canales, parqueaderos.</p>
        <div class="ai-note">
            <strong>🤖 Potencial IA:</strong> El alcance podría listar las áreas REALES del conjunto específico (número de torres, nombre de zonas sociales, etc.) en lugar del listado genérico.
        </div>
    </div>
</div>

<!-- 1.3 -->
<div class="section" id="s13">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.3 DEFINICIONES</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>Contiene 13 definiciones técnicas fijas:</p>
        <ul>
            <li>Ambiente (Decreto 1843/1991)</li>
            <li>Barrido, Limpieza, Desinfección</li>
            <li>Detergente, Desinfectante (tipos: cloro, yodo, orgánicos, alcoholes)</li>
            <li>Higiene, Hábitos higiénicos, Inspección sanitaria, Inocuidad</li>
            <li>Solución, Trampa de grasas (RAS 2000), Vertimiento</li>
            <li>Residuo corto punzante, Residuos especiales</li>
        </ul>
        <p style="color:#8b949e; font-style:italic; margin-top:12px;">Texto normativo fijo — no requiere personalización por cliente.</p>
    </div>
</div>

<!-- 1.4 -->
<div class="section" id="s14">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.4 RESPONSABLES</h2>
        <div class="badges">
            <span class="badge badge-data">Dato del cliente</span>
            <span class="badge-ai">🤖 Candidato IA</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>Actores definidos:</p>
        <ul>
            <li><strong>Administrador(a) de la Copropiedad</strong> — implementación, recursos, cronograma, supervisión.</li>
            <li><strong>Personal de servicios generales / aseo</strong> — ejecución, EPP, registros.</li>
            <li><strong>Empresas contratistas de aseo</strong> — cuando aplique.</li>
            <li><strong>Consejo de Administración o Comité de Convivencia</strong> — verificación.</li>
            <li><strong>Responsable del SG-SST del Conjunto</strong> — [NOMBRE DEL RESPONSABLE]</li>
        </ul>
        <div class="ai-note">
            <strong>🤖 Potencial IA:</strong> Podría generarse texto con el nombre real del responsable y datos específicos del cargo en el conjunto.
        </div>
    </div>
</div>

<!-- 1.5 -->
<div class="section" id="s15">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.5 ELEMENTOS DE PROTECCIÓN PERSONAL (EPP)</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>EPP mínimo requerido: guantes caucho/nitrilo, tapabocas o respirador, gafas de seguridad, botas antideslizantes, delantal/overol impermeable, gorra (cuando aplique).</p>
        <p>Obligatorio según Decreto 1072 de 2015. La Administración garantiza suministro, reposición y capacitación.</p>
        <p style="color:#8b949e; font-style:italic;">Texto estándar normativo — aplica igual a todos los conjuntos.</p>
    </div>
</div>

<!-- 1.6 -->
<div class="section" id="s16">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.6 INSUMOS Y PRODUCTOS</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>Incluye: detergente multiusos, desinfectante (hipoclorito, amonio cuaternario), jabón líquido, limpiavidrios, desengrasante, bolsas por código de colores, implementos (traperos, mopas, escobas, cepillos, paños).</p>
        <div class="sub-section">
            <h3>Matriz de insumos, productos, usos e implementos</h3>
            <table class="data-table">
                <tr><th>ACTIVIDAD</th><th>SUSTANCIAS</th><th>CONCENTRACIÓN</th><th>EPP</th><th>IMPLEMENTOS</th></tr>
                <tr><td>Barrido</td><td>Agua</td><td>Mínima</td><td>Overol, Tapabocas, Guantes</td><td>Escoba, Recogedor</td></tr>
                <tr><td>Limpieza general</td><td>Detergente</td><td>Ficha técnica</td><td>Overol, Guantes, Botas</td><td>Traperos, Cepillos</td></tr>
                <tr><td>Limpieza profunda</td><td>Detergente industrial</td><td>Ficha técnica</td><td>Overol, Guantes, Botas</td><td>Hidrolavadora</td></tr>
                <tr><td>Partes altas</td><td>Detergente industrial</td><td>Ficha técnica</td><td>Arnés, Casco, Overol</td><td>Escaleras, Andamios</td></tr>
                <tr><td>Desinfección superficies</td><td>Hipoclorito de sodio</td><td>0.05–0.1% / 10 min</td><td>Overol, Guantes, Botas</td><td>Traperos, Cepillos</td></tr>
                <tr><td>Desinfección partes altas</td><td>Hipoclorito de sodio</td><td>0.05–0.1% / 10 min</td><td>Arnés, Casco, Overol</td><td>Hidrolavadora, Mangueras</td></tr>
            </table>
        </div>
    </div>
</div>

<!-- 1.7 -->
<div class="section" id="s17">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.7 TÉCNICAS DE LIMPIEZA Y DESINFECCIÓN</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>5 técnicas inductivas obligatorias:</p>
        <ul>
            <li><strong>Arriba – Abajo:</strong> De techos/paredes hacia pisos.</li>
            <li><strong>Adentro – Afuera:</strong> Desde el interior hacia la salida.</li>
            <li><strong>De lo más limpio a lo más contaminado.</strong></li>
            <li><strong>Lo más seco posible:</strong> Minimizar uso de agua.</li>
            <li><strong>Técnica en zig–zag:</strong> Movimientos continuos sin devolver contaminación.</li>
        </ul>
    </div>
</div>

<!-- 1.8 -->
<div class="section" id="s18">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.8 PROCEDIMIENTO GENERAL</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>15 pasos: EPP → insumos → análisis condiciones → despejar área → barrer → solución detergente (10 min) → restregar paredes/puertas/pisos → enjuagar → solución desinfectante → dejar actuar 10 min → restregar → enjuagar → secar.</p>
        <p style="color:#8b949e; font-style:italic;">Nota: Si el producto tiene acción conjunta detergente+desinfectante, omitir pasos 11-14.</p>
    </div>
</div>

<!-- 1.9 -->
<div class="section" id="s19">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.9 PROCEDIMIENTO DE BAÑOS</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>Áreas críticas — manejo riguroso. 13 pasos específicos para servicios sanitarios: detergente en inodoros/orinales (20 min) → superficies (10 min) → restregar → desinfectante → enjuagar → secar.</p>
    </div>
</div>

<!-- 1.10 -->
<div class="section" id="s110">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.10 PROCEDIMIENTO PARTES ALTAS</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <p>14 pasos para techos, paredes y áreas de difícil acceso. Requiere informar previamente a usuarios, EPP de alturas (arnés, cuerdas, casco), escaleras/andamios certificados.</p>
    </div>
</div>

<!-- 1.11 -->
<div class="section" id="s111">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.11 FRECUENCIA DE LIMPIEZA Y DESINFECCIÓN</h2>
        <div class="badges">
            <span class="badge badge-data">Dato del cliente</span>
            <span class="badge-ai">🤖 Candidato IA</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <table class="data-table">
            <tr><th>ÁREA</th><th>FRECUENCIA</th></tr>
            <tr><td>Zonas comunes y pasillos</td><td>Diaria</td></tr>
            <tr><td>Escaleras y rampas</td><td>Diaria</td></tr>
            <tr><td>Baños administrativos y sociales</td><td>Diaria</td></tr>
            <tr><td>Oficina de administración</td><td>Diaria</td></tr>
            <tr><td>Salón social</td><td>Antes y después de cada uso</td></tr>
            <tr><td>Parqueaderos</td><td>Trimestral</td></tr>
            <tr><td>Andenes perimetrales</td><td>Mensual</td></tr>
            <tr><td>Cuartos de almacenamiento</td><td>Semanal</td></tr>
            <tr><td>Contenedores y unidad de residuos</td><td>Diaria</td></tr>
            <tr><td>Canales de aguas lluvias y negras</td><td>Semestral</td></tr>
            <tr><td>Techos y partes altas</td><td>Semestral</td></tr>
        </table>
        <div class="ai-note">
            <strong>🤖 Potencial IA:</strong> La tabla de frecuencias podría ajustarse según las áreas reales del conjunto y la dotación de personal de aseo disponible.
        </div>
    </div>
</div>

<!-- 1.12 -->
<div class="section" id="s112">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.12 REGISTROS Y FORMATOS</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <ol>
            <li>Formato de limpieza y desinfección por área</li>
            <li>Cronograma mensual de actividades</li>
            <li>Registro de entrega, reposición y uso de EPP</li>
            <li>Hojas de seguridad (SDS) y fichas técnicas</li>
            <li>Listas de chequeo de inspección sanitaria</li>
        </ol>
    </div>
</div>

<!-- 1.13 -->
<div class="section" id="s113">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.13 MEDIDAS DE CONTROL, SEGUIMIENTO Y MEJORA</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <ol>
            <li>Inspecciones periódicas a áreas intervenidas</li>
            <li>Verificación mensual del cronograma</li>
            <li>Revisión del estado de limpieza</li>
            <li>Registro y corrección de no conformidades</li>
            <li>Seguimiento a indicadores</li>
            <li>Acciones correctivas y preventivas</li>
            <li>Actualización anual del Programa</li>
        </ol>
    </div>
</div>

<!-- 1.14 -->
<div class="section" id="s114">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.14 INDICADORES DEL PROGRAMA</h2>
        <div class="badges">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge-arrow">▼</span>
        </div>
    </div>
    <div class="section-body">
        <div class="sub-section">
            <h3>1.14.1 Cumplimiento de actividades de limpieza y desinfección</h3>
            <table class="data-table">
                <tr><td style="width:40%; background:#161b22; font-weight:bold; color:#bd9751;">Fórmula</td><td>(N° días registrados ÷ Días hábiles del periodo) × 100</td></tr>
                <tr><td style="background:#161b22; font-weight:bold; color:#bd9751;">Meta</td><td>≥ 95%</td></tr>
                <tr><td style="background:#161b22; font-weight:bold; color:#bd9751;">Periodicidad</td><td>Mensual</td></tr>
                <tr><td style="background:#161b22; font-weight:bold; color:#bd9751;">Fuente</td><td>Planilla de limpieza diligenciada</td></tr>
            </table>
        </div>
        <div class="sub-section">
            <h3>1.14.2 Estado de los elementos de limpieza</h3>
            <table class="data-table">
                <tr><td style="width:40%; background:#161b22; font-weight:bold; color:#bd9751;">Fórmula</td><td>(N° elementos en buen estado ÷ N° total verificados) × 100</td></tr>
                <tr><td style="background:#161b22; font-weight:bold; color:#bd9751;">Meta</td><td>≥ 90%</td></tr>
                <tr><td style="background:#161b22; font-weight:bold; color:#bd9751;">Periodicidad</td><td>Mensual</td></tr>
                <tr><td style="background:#161b22; font-weight:bold; color:#bd9751;">Elementos</td><td>Escobas, traperos, cepillos, mopas, baldes, recogedores, atomizadores</td></tr>
            </table>
        </div>
    </div>
</div>

</div><!-- /content -->

<script>
function toggle(header) {
    var body = header.nextElementSibling;
    var arrow = header.querySelector('.badge-arrow');
    var isOpen = body.classList.contains('open');
    body.classList.toggle('open', !isOpen);
    arrow.classList.toggle('open', !isOpen);
}
function openAll() {
    document.querySelectorAll('.section-body').forEach(b => b.classList.add('open'));
    document.querySelectorAll('.badge-arrow').forEach(a => a.classList.add('open'));
}
function closeAll() {
    document.querySelectorAll('.section-body').forEach(b => b.classList.remove('open'));
    document.querySelectorAll('.badge-arrow').forEach(a => a.classList.remove('open'));
}
// Abrir primera sección por defecto
document.addEventListener('DOMContentLoaded', function() {
    var first = document.querySelector('.section-body');
    var firstArrow = document.querySelector('.badge-arrow');
    if (first) { first.classList.add('open'); firstArrow.classList.add('open'); }
});
</script>
</body>
</html>
