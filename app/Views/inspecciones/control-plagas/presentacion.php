<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FT-SST-227 — Programa de Control Integrado de Plagas</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: #0d1117; color: #e6edf3; font-size: 18px; line-height: 1.6; }
.doc-header { background: #1c2437; border-bottom: 4px solid #ff7b72; padding: 28px 48px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.doc-header h1 { font-size: 26px; color: #fff; }
.code-badge { background: #ff7b72; color: #0d1117; font-weight: bold; font-size: 20px; padding: 8px 20px; border-radius: 6px; }
.nav-bar { background: #161b22; padding: 12px 48px; border-bottom: 1px solid #30363d; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.nav-bar a { color: #8b949e; text-decoration: none; font-size: 14px; padding: 4px 10px; border-radius: 4px; transition: all .2s; }
.nav-bar a:hover { background: #21262d; color: #e6edf3; }
.nav-bar .sep { color: #30363d; }
.content { padding: 48px; max-width: 1400px; margin: 0 auto; }
.section { background: #161b22; border: 1px solid #30363d; border-radius: 10px; margin-bottom: 24px; overflow: hidden; }
.section-header { padding: 22px 32px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; transition: background .2s; }
.section-header:hover { background: #1c2437; }
.section-header h2 { font-size: 20px; color: #ff7b72; font-weight: 600; }
.badges { display: flex; gap: 8px; align-items: center; }
.badge { font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.badge-static { background: #21262d; color: #8b949e; border: 1px solid #30363d; }
.badge-ai { background: #3d1f1f; color: #ff7b72; border: 1px solid #8b2222; }
.badge-data { background: #1f2d3f; color: #58a6ff; border: 1px solid #1f6feb; }
.badge-arrow { color: #8b949e; font-size: 18px; transition: transform .2s; }
.badge-arrow.open { transform: rotate(180deg); }
.section-body { padding: 28px 32px; border-top: 1px solid #30363d; font-size: 16px; color: #c9d1d9; display: none; }
.section-body.open { display: block; }
.section-body p { margin-bottom: 14px; text-align: justify; }
.section-body ul, .section-body ol { margin: 8px 0 16px 28px; }
.section-body li { margin-bottom: 6px; }
.sub-section { background: #0d1117; border: 1px solid #21262d; border-radius: 6px; margin: 14px 0; padding: 18px 24px; }
.sub-section h3 { color: #ffa198; font-size: 15px; margin-bottom: 10px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin: 12px 0; }
.data-table th { background: #1c2437; color: #ff7b72; padding: 10px 12px; text-align: left; border: 1px solid #30363d; }
.data-table td { padding: 8px 12px; border: 1px solid #21262d; vertical-align: top; }
.data-table tr:nth-child(even) td { background: #0d1117; }
.ai-note { background: #3d1f1f; border: 1px solid #8b2222; border-radius: 6px; padding: 12px 16px; margin: 14px 0; font-size: 14px; color: #ff7b72; }
.ai-note strong { color: #ffa198; }
.legend { display: flex; gap: 20px; align-items: center; font-size: 14px; }
</style>
</head>
<body>

<div class="doc-header">
    <div>
        <h1><span style="color:#ff7b72">🐛</span> Programa de Control Integrado de Plagas</h1>
        <div style="color:#8b949e; font-size:15px; margin-top:4px;">Plan de Saneamiento Básico — Propiedad Horizontal</div>
    </div>
    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
        <div class="code-badge">FT-SST-227</div>
        <div class="legend">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge badge-data">Dato del cliente</span>
            <span class="badge badge-ai">🤖 Candidato IA</span>
        </div>
    </div>
</div>

<div class="nav-bar">
    <strong style="color:#ff7b72;font-size:14px;">Secciones:</strong>
    <span class="sep">|</span>
    <a href="#s11">1.1 Objetivo</a>
    <a href="#s12">1.2 Alcance</a>
    <a href="#s13">1.3 Definiciones</a>
    <a href="#s14">1.4 Consideraciones generales</a>
    <a href="#s15">1.5 Control roedores</a>
    <a href="#s16">1.6 Control insectos</a>
    <a href="#s17">1.7 Control palomas</a>
    <a href="#s18">1.8 Precauciones</a>
    <a href="#s19">1.9 Responsabilidades</a>
    <a href="#s110">1.10 Frecuencia</a>
    <a href="#s111">1.11 Seguimiento</a>
    <a href="#s112">1.12 Indicadores</a>
    <span class="sep">|</span>
    <a href="javascript:openAll()" style="color:#ff7b72">▶ Expandir todo</a>
    <a href="javascript:closeAll()" style="color:#8b949e">▼ Colapsar todo</a>
</div>

<div class="content">

<div class="section" id="s11">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.1 OBJETIVO</h2>
        <div class="badges"><span class="badge badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Establecer e implementar los procedimientos para la prevención, control y eliminación de plagas (roedores, insectos rastreros y voladores, aves) en las áreas comunes del <strong>[NOMBRE DEL CONJUNTO]</strong>, minimizando riesgos para la salud de residentes y trabajadores.</p>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> El objetivo podría adaptarse mencionando plagas identificadas previamente en el conjunto.</div>
    </div>
</div>

<div class="section" id="s12">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.2 ALCANCE</h2>
        <div class="badges"><span class="badge badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Aplica a todas las áreas comunes del <strong>[NOMBRE DEL CONJUNTO]</strong>: zonas verdes, cuartos de residuos, sótanos, parqueaderos, cuartos técnicos, depósitos, tuberías y espacios confinados.</p>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Podría especificar las áreas críticas del conjunto donde históricamente han aparecido plagas.</div>
    </div>
</div>

<div class="section" id="s13">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.3 DEFINICIONES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Definiciones: plaga, vector, rodenticida, insecticida, fumigación, desratización, desinsectación, MIP (Manejo Integrado de Plagas), estación de cebado, fauna nociva, biocida.</p>
        <p style="color:#8b949e; font-style:italic;">Texto normativo fijo — Decreto 1843 de 1991, Ley 9 de 1979.</p>
    </div>
</div>

<div class="section" id="s14">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.4 CONSIDERACIONES GENERALES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Enfoque MIP: prevención primero (eliminar fuentes de alimento y refugio), luego control físico, y por último químico. Solo personal o empresa certificada puede aplicar plaguicidas. Exigir certificación vigente al contratista.</p>
        <div class="sub-section">
            <h3>Riesgos asociados a la presencia de roedores</h3>
            <p>Transmisión de enfermedades (leptospirosis, salmonelosis, hantavirus), daños a instalaciones eléctricas, contaminación de alimentos, deterioro de la infraestructura.</p>
        </div>
    </div>
</div>

<div class="section" id="s15">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.5 CONTROL DE ROEDORES</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <div class="sub-section"><h3>Control químico — Rodenticidas anticoagulantes</h3><p>Uso de rodenticidas de segunda y tercera generación. Producto activo, dosis y forma de presentación según recomendación técnica del operador autorizado. Rotación semestral de principio activo.</p></div>
        <div class="sub-section"><h3>Ubicación de estaciones de cebado</h3><p>Perimetral del cuarto de residuos, sótanos, parqueaderos, cuartos técnicos, puntos de entrada de tuberías. <strong>[MAPA DE ESTACIONES DEL CONJUNTO]</strong></p></div>
        <div class="sub-section"><h3>Identificación de presencia de roedores</h3><p>Heces, mordeduras en materiales, huellas, madrigueras, avistamiento nocturno.</p></div>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Número y ubicación de estaciones de cebado podría generarse según el plano del conjunto.</div>
    </div>
</div>

<div class="section" id="s16">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.6 CONTROL DE INSECTOS RASTREROS Y VOLADORES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <div class="sub-section"><h3>1.6.1 CUCARACHA</h3><p>Control en grietas, tuberías, cuartos húmedos. Gel cebo, insecticida residual. Inspección en zonas oscuras y húmedas.</p></div>
        <div class="sub-section"><h3>1.6.2 MOSCA</h3><p>Control en cuartos de residuos, zonas húmedas. Trampas adhesivas, insecticida de contacto, eliminación de criaderos.</p></div>
        <div class="sub-section"><h3>1.6.3 MOSQUITO</h3><p>Eliminación de agua estancada, larvicidas en depósitos, insecticidas adulticidas. Especial atención en zonas verdes y piscinas.</p></div>
        <div class="sub-section"><h3>1.6.4–1.6.10 Control químico, insecticidas, aplicación</h3><p>Piretroides y organofosforados. Concentración según ficha técnica. Rotación semestral de principio activo. Aplicación con equipo ULV o termonebulizadora según área.</p></div>
    </div>
</div>

<div class="section" id="s17">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.7 CONTROL DE PALOMAS</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Riesgos: histoplasmosis, criptococosis, psitacosis, daño a fachadas y HVAC, obstrucción de canales.</p>
        <p>Acciones preventivas: pinchos anti-aves en balcones y cornisas, redes en terrazas, no alimentar palomas, limpieza de excrementos con EPP adecuado.</p>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Si el conjunto tiene problema específico de palomas, podría describir las zonas afectadas y las medidas implementadas.</div>
    </div>
</div>

<div class="section" id="s18">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.8 PRECAUCIONES DE SEGURIDAD Y OBSERVACIONES GENERALES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Durante aplicaciones: desalojar área 2 horas mínimo, ventilar 30 minutos antes de reingresar, cubrir alimentos y superficies, retirar mascotas, informar a residentes con 24 horas de anticipación. Exigir certificado de aplicación y ficha técnica de productos usados.</p>
    </div>
</div>

<div class="section" id="s19">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.9 RESPONSABILIDADES</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <ul>
            <li><strong>Administrador(a):</strong> Contratación de empresa certificada, cronograma, archivo de certificados.</li>
            <li><strong>Empresa de control de plagas:</strong> Certificada y con personal idóneo. Entregar informe técnico y certificado de cada servicio.</li>
            <li><strong>Personal de aseo:</strong> Reportar signos de infestación, mantener limpieza preventiva.</li>
            <li><strong>Responsable SG-SST:</strong> [NOMBRE DEL RESPONSABLE]</li>
        </ul>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Podría incluir el nombre de la empresa contratista actual de fumigación si se registra en la plataforma.</div>
    </div>
</div>

<div class="section" id="s110">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.10 FRECUENCIA DE SERVICIOS DE CONTROL PREVENTIVO</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <table class="data-table">
            <tr><th>SERVICIO</th><th>FRECUENCIA MÍNIMA</th><th>ÁREA</th></tr>
            <tr><td>Desinsectación (fumigación)</td><td>Semestral</td><td>Todas las áreas comunes</td></tr>
            <tr><td>Desratización</td><td>Semestral</td><td>Cuarto residuos, sótanos, parqueaderos</td></tr>
            <tr><td>Control larvario (mosquitos)</td><td>Semestral</td><td>Zonas húmedas, piscinas, jardines</td></tr>
            <tr><td>Revisión estaciones de cebado</td><td>Mensual</td><td>Perimetral y puntos críticos</td></tr>
            <tr><td>Inspección preventiva</td><td>Trimestral</td><td>Todo el conjunto</td></tr>
        </table>
    </div>
</div>

<div class="section" id="s111">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.11 SEGUIMIENTO AL PROGRAMA</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Archivo obligatorio: certificados de fumigación, fichas técnicas de productos, informes técnicos de cada visita, registro de hallazgos. Verificación de cumplimiento del cronograma. Acción correctiva inmediata ante brotes.</p>
    </div>
</div>

<div class="section" id="s112">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.12 INDICADORES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <div class="sub-section">
            <h3>Cumplimiento de fumigación semestral</h3>
            <table class="data-table">
                <tr><td style="width:40%;background:#161b22;font-weight:bold;color:#ff7b72;">Fórmula</td><td>(N° fumigaciones realizadas ÷ N° fumigaciones programadas) × 100</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#ff7b72;">Meta</td><td>100%</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#ff7b72;">Periodicidad</td><td>Semestral</td></tr>
            </table>
        </div>
        <div class="sub-section">
            <h3>Cumplimiento de desratización semestral</h3>
            <table class="data-table">
                <tr><td style="width:40%;background:#161b22;font-weight:bold;color:#ff7b72;">Fórmula</td><td>(N° desratizaciones realizadas ÷ N° programadas) × 100</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#ff7b72;">Meta</td><td>100%</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#ff7b72;">Periodicidad</td><td>Semestral</td></tr>
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
