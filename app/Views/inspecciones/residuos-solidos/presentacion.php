<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FT-SST-226 — Programa de Manejo Integral de Residuos Sólidos</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: #0d1117; color: #e6edf3; font-size: 18px; line-height: 1.6; }
.doc-header { background: #1c2437; border-bottom: 4px solid #3fb950; padding: 28px 48px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.doc-header h1 { font-size: 26px; color: #fff; }
.code-badge { background: #3fb950; color: #0d1117; font-weight: bold; font-size: 20px; padding: 8px 20px; border-radius: 6px; }
.nav-bar { background: #161b22; padding: 12px 48px; border-bottom: 1px solid #30363d; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.nav-bar a { color: #8b949e; text-decoration: none; font-size: 14px; padding: 4px 10px; border-radius: 4px; transition: all .2s; }
.nav-bar a:hover { background: #21262d; color: #e6edf3; }
.nav-bar .sep { color: #30363d; }
.content { padding: 48px; max-width: 1400px; margin: 0 auto; }
.section { background: #161b22; border: 1px solid #30363d; border-radius: 10px; margin-bottom: 24px; overflow: hidden; }
.section-header { padding: 22px 32px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; transition: background .2s; }
.section-header:hover { background: #1c2437; }
.section-header h2 { font-size: 20px; color: #3fb950; font-weight: 600; }
.badges { display: flex; gap: 8px; align-items: center; }
.badge { font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.badge-static { background: #21262d; color: #8b949e; border: 1px solid #30363d; }
.badge-ai { background: #1f3a2e; color: #3fb950; border: 1px solid #238636; }
.badge-data { background: #1f2d3f; color: #58a6ff; border: 1px solid #1f6feb; }
.badge-arrow { color: #8b949e; font-size: 18px; transition: transform .2s; }
.badge-arrow.open { transform: rotate(180deg); }
.section-body { padding: 28px 32px; border-top: 1px solid #30363d; font-size: 16px; color: #c9d1d9; display: none; }
.section-body.open { display: block; }
.section-body p { margin-bottom: 14px; text-align: justify; }
.section-body ul, .section-body ol { margin: 8px 0 16px 28px; }
.section-body li { margin-bottom: 6px; }
.sub-section { background: #0d1117; border: 1px solid #21262d; border-radius: 6px; margin: 14px 0; padding: 18px 24px; }
.sub-section h3 { color: #7ee787; font-size: 15px; margin-bottom: 10px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin: 12px 0; }
.data-table th { background: #1c2437; color: #3fb950; padding: 10px 12px; text-align: left; border: 1px solid #30363d; }
.data-table td { padding: 8px 12px; border: 1px solid #21262d; vertical-align: top; }
.data-table tr:nth-child(even) td { background: #0d1117; }
.ai-note { background: #1f3a2e; border: 1px solid #238636; border-radius: 6px; padding: 12px 16px; margin: 14px 0; font-size: 14px; color: #3fb950; }
.ai-note strong { color: #7ee787; }
.legend { display: flex; gap: 20px; align-items: center; font-size: 14px; }
</style>
</head>
<body>

<div class="doc-header">
    <div>
        <h1><span style="color:#3fb950">♻️</span> Programa de Manejo Integral de Residuos Sólidos</h1>
        <div style="color:#8b949e; font-size:15px; margin-top:4px;">Plan de Saneamiento Básico — Propiedad Horizontal</div>
    </div>
    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
        <div class="code-badge">FT-SST-226</div>
        <div class="legend">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge badge-data">Dato del cliente</span>
            <span class="badge badge-ai">🤖 Candidato IA</span>
        </div>
    </div>
</div>

<div class="nav-bar">
    <strong style="color:#3fb950;font-size:14px;">Secciones:</strong>
    <span class="sep">|</span>
    <a href="#s11">1.1 Objetivo</a>
    <a href="#s12">1.2 Alcance</a>
    <a href="#s13">1.3 Definiciones</a>
    <a href="#s14">1.4 Precauciones</a>
    <a href="#s15">1.5 Recolección fuente</a>
    <a href="#s16">1.6 Bioseguridad</a>
    <a href="#s17">1.7 Responsables</a>
    <a href="#s18">1.8 Insumos</a>
    <a href="#s19">1.9 EPP</a>
    <a href="#s110">1.10 Fuentes / Clasificación</a>
    <a href="#s111">1.11 Procedimiento</a>
    <a href="#s112">1.12 Capacitación</a>
    <a href="#s113">1.13 Control</a>
    <a href="#s114">1.14 Indicadores</a>
    <span class="sep">|</span>
    <a href="javascript:openAll()" style="color:#3fb950">▶ Expandir todo</a>
    <a href="javascript:closeAll()" style="color:#8b949e">▼ Colapsar todo</a>
</div>

<div class="content">

<div class="section" id="s11">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.1 OBJETIVO</h2>
        <div class="badges"><span class="badge badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Establecer los lineamientos, procedimientos y responsabilidades para el manejo integral de residuos sólidos generados en las áreas comunes del <strong>[NOMBRE DEL CONJUNTO]</strong>, garantizando su correcta clasificación, recolección, almacenamiento y disposición final.</p>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> El objetivo podría adaptarse según el tipo de residuos característicos del conjunto y la infraestructura de almacenamiento disponible.</div>
    </div>
</div>

<div class="section" id="s12">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.2 ALCANCE</h2>
        <div class="badges"><span class="badge badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Aplica a todas las áreas comunes del <strong>[NOMBRE DEL CONJUNTO]</strong>: zonas comunes, áreas administrativas, salones, parqueaderos y unidades de almacenamiento de residuos. Involucra al personal de aseo, residentes y visitantes en la correcta separación en la fuente.</p>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Podría mencionar el cuarto de residuos específico del conjunto y la ruta de recolección municipal.</div>
    </div>
</div>

<div class="section" id="s13">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.3 DEFINICIONES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Definiciones técnicas normativas sobre clasificación de residuos:</p>
        <ul>
            <li>Residuos sólidos ordinarios, reciclables, peligrosos (RESPEL), RAEE</li>
            <li>Residuos orgánicos, voluminosos, corto-punzantes</li>
            <li>Separación en la fuente, cuarto de almacenamiento temporal</li>
            <li>Disposición final, empresa prestadora del servicio de aseo</li>
        </ul>
        <p style="color:#8b949e; font-style:italic;">Texto normativo fijo — Decreto 2981 de 2013, Resolución 1407 de 2018.</p>
    </div>
</div>

<div class="section" id="s14">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.4 PRECAUCIONES DE SEGURIDAD Y OBSERVACIONES GENERALES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Precauciones durante el manejo de residuos: no comprimir residuos con las manos, no arrastrar bolsas por el piso, no almacenar más de 24 horas, identificar correctamente los contenedores, verificar que los recipientes no estén rotos.</p>
    </div>
</div>

<div class="section" id="s15">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.5 RECOLECCIÓN DE RESIDUOS EN LA FUENTE</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Código de colores según Resolución 2184 de 2019:</p>
        <ul>
            <li><strong style="color:#7ee787">Verde:</strong> Residuos orgánicos biodegradables</li>
            <li><strong style="color:#58a6ff">Azul:</strong> Plástico, vidrio, metales, papel y cartón</li>
            <li><strong style="color:#f0a84c">Blanco:</strong> Aprovechables (reciclables limpios y secos)</li>
            <li><strong style="color:#ff7b72">Rojo/Negro:</strong> No aprovechables, peligrosos, corto-punzantes</li>
        </ul>
    </div>
</div>

<div class="section" id="s16">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.6 NORMAS DE BIOSEGURIDAD</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Normas para el personal de manejo de residuos: uso permanente de EPP, lavado de manos, vacunación al día (hepatitis B, tétanos), no comer o beber durante la labor, reportar accidentes de inmediato.</p>
    </div>
</div>

<div class="section" id="s17">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.7 RESPONSABLES</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <ul>
            <li><strong>Administrador(a):</strong> Implementación y supervisión del programa.</li>
            <li><strong>Personal de aseo:</strong> Recolección, clasificación y transporte al cuarto de residuos.</li>
            <li><strong>Empresa de aseo contratista:</strong> Cuando aplique.</li>
            <li><strong>Residentes y propietarios:</strong> Separación correcta en la fuente.</li>
            <li><strong>Responsable SG-SST:</strong> [NOMBRE DEL RESPONSABLE]</li>
        </ul>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Podría especificar el nombre real del responsable y el número de operarios de aseo del conjunto.</div>
    </div>
</div>

<div class="section" id="s18">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.8 INSUMOS Y ELEMENTOS NECESARIOS</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Bolsas por código de colores, recipientes identificados, carro de transporte de residuos, balanza, guantes de carnaza, tapabocas, gafas, overol, botas, contenedores de almacenamiento temporal.</p>
    </div>
</div>

<div class="section" id="s19">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.9 ELEMENTOS DE PROTECCIÓN PERSONAL (EPP)</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>EPP específico para manejo de residuos: guantes de carnaza (residuos corto-punzantes), guantes de nitrilo (residuos orgánicos), tapabocas N95 o quirúrgico, gafas de protección, overol, botas antideslizantes impermeables, delantal.</p>
    </div>
</div>

<div class="section" id="s110">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.10 FUENTES DE GENERACIÓN Y CLASIFICACIÓN</h2>
        <div class="badges"><span class="badge badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <div class="sub-section">
            <h3>1.10.1 Fuentes de generación de residuos</h3>
            <p>Áreas comunes, zonas de uso colectivo, administración, zonas verdes, salones comunales, zonas húmedas, parqueaderos.</p>
        </div>
        <div class="sub-section">
            <h3>1.10.2 Clasificación de los residuos generados</h3>
            <table class="data-table">
                <tr><th>TIPO</th><th>SUBTIPO</th><th>RECIPIENTE</th></tr>
                <tr><td>Ordinarios</td><td>No reciclables, domésticos</td><td>Negro/Gris</td></tr>
                <tr><td>Reciclables</td><td>Papel, cartón, plástico, vidrio, metales</td><td>Azul/Blanco</td></tr>
                <tr><td>Orgánicos</td><td>Restos de jardinería, alimentos</td><td>Verde</td></tr>
                <tr><td>Peligrosos (RESPEL)</td><td>Químicos, baterías, aceites</td><td>Rojo</td></tr>
                <tr><td>RAEE</td><td>Equipos electrónicos en desuso</td><td>Punto RAEE</td></tr>
            </table>
        </div>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Podría identificar las fuentes principales de residuos del conjunto específico y sus volúmenes aproximados.</div>
    </div>
</div>

<div class="section" id="s111">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.11 PROCEDIMIENTO DE RECOLECCIÓN, ALMACENAMIENTO Y DISPOSICIÓN FINAL</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <div class="sub-section"><h3>1.11.1 Recolección en áreas comunes y zonas colectivas</h3><p>Ruta de recolección diaria con carro de residuos, verificación de bolsas, transportar al cuarto de almacenamiento temporal.</p></div>
        <div class="sub-section"><h3>1.11.2 Recolección en puntos administrativos</h3><p>Recolección diaria de la oficina de administración con separación en la fuente desde el origen.</p></div>
        <div class="sub-section"><h3>1.11.3 Recolección de residuos peligrosos (RESPEL y RAEE)</h3><p>Gestión a través de gestores autorizados por la autoridad ambiental. Registro de entrega y certificado de disposición.</p></div>
        <div class="sub-section"><h3>1.11.4 Recolección de residuos orgánicos</h3><p>Compostaje o entrega al servicio de recolección municipal. Nunca mezclar con residuos peligrosos.</p></div>
        <div class="sub-section"><h3>1.11.5 Alternativas para optimización del manejo</h3><p>Reducción en la fuente, reutilización, reciclaje, puntos de entrega voluntaria (PEV), campañas de sensibilización a residentes.</p></div>
    </div>
</div>

<div class="section" id="s112">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.12 CAPACITACIÓN Y EDUCACIÓN CONTINUA</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Capacitaciones obligatorias al personal de aseo: separación en la fuente, manejo seguro, bioseguridad, manejo de residuos peligrosos. Mínimo 1 capacitación semestral documentada con lista de asistencia.</p>
        <p>Campañas de sensibilización a residentes y propietarios sobre correcta clasificación.</p>
    </div>
</div>

<div class="section" id="s113">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.13 CONTROL Y SEGUIMIENTO</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Inspecciones periódicas al cuarto de almacenamiento. Verificación mensual del cronograma. Pesaje de residuos para registro de generación. Revisión de registros de entrega a gestores de RESPEL.</p>
    </div>
</div>

<div class="section" id="s114">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.14 INDICADORES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <div class="sub-section">
            <h3>1.14.1 Condición sanitaria del cuarto de almacenamiento temporal</h3>
            <table class="data-table">
                <tr><td style="width:40%;background:#161b22;font-weight:bold;color:#3fb950;">Fórmula</td><td>(N° criterios cumplidos ÷ N° criterios evaluados) × 100</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#3fb950;">Meta</td><td>≥ 90%</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#3fb950;">Periodicidad</td><td>Mensual</td></tr>
            </table>
        </div>
        <div class="sub-section">
            <h3>1.14.2 Nivel de correcta separación en la fuente</h3>
            <table class="data-table">
                <tr><td style="width:40%;background:#161b22;font-weight:bold;color:#3fb950;">Fórmula</td><td>(N° de áreas con separación correcta ÷ N° total de áreas verificadas) × 100</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#3fb950;">Meta</td><td>≥ 85%</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#3fb950;">Periodicidad</td><td>Mensual</td></tr>
            </table>
        </div>
    </div>
</div>

</div>

<script>
function toggle(header) { var body=header.nextElementSibling; var arrow=header.querySelector('.badge-arrow'); var isOpen=body.classList.contains('open'); body.classList.toggle('open',!isOpen); arrow.classList.toggle('open',!isOpen); }
function openAll() { document.querySelectorAll('.section-body').forEach(b=>b.classList.add('open')); document.querySelectorAll('.badge-arrow').forEach(a=>a.classList.add('open')); }
function closeAll() { document.querySelectorAll('.section-body').forEach(b=>b.classList.remove('open')); document.querySelectorAll('.badge-arrow').forEach(a=>a.classList.remove('open')); }
document.addEventListener('DOMContentLoaded', function() { var f=document.querySelector('.section-body'); var fa=document.querySelector('.badge-arrow'); if(f){f.classList.add('open');fa.classList.add('open');} });
</script>
</body>
</html>
