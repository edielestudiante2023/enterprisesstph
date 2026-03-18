<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FT-SST-228 — Programa de Abastecimiento y Control de Agua Potable</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: #0d1117; color: #e6edf3; font-size: 18px; line-height: 1.6; }
.doc-header { background: #1c2437; border-bottom: 4px solid #58a6ff; padding: 28px 48px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.doc-header h1 { font-size: 26px; color: #fff; }
.code-badge { background: #58a6ff; color: #0d1117; font-weight: bold; font-size: 20px; padding: 8px 20px; border-radius: 6px; }
.nav-bar { background: #161b22; padding: 12px 48px; border-bottom: 1px solid #30363d; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.nav-bar a { color: #8b949e; text-decoration: none; font-size: 14px; padding: 4px 10px; border-radius: 4px; transition: all .2s; }
.nav-bar a:hover { background: #21262d; color: #e6edf3; }
.nav-bar .sep { color: #30363d; }
.content { padding: 48px; max-width: 1400px; margin: 0 auto; }
.section { background: #161b22; border: 1px solid #30363d; border-radius: 10px; margin-bottom: 24px; overflow: hidden; }
.section-header { padding: 22px 32px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; transition: background .2s; }
.section-header:hover { background: #1c2437; }
.section-header h2 { font-size: 20px; color: #58a6ff; font-weight: 600; }
.badges { display: flex; gap: 8px; align-items: center; }
.badge { font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.badge-static { background: #21262d; color: #8b949e; border: 1px solid #30363d; }
.badge-ai { background: #1f2d3f; color: #79c0ff; border: 1px solid #1f6feb; }
.badge-data { background: #1a2d4a; color: #58a6ff; border: 1px solid #1f6feb; font-size:13px; }
.badge-arrow { color: #8b949e; font-size: 18px; transition: transform .2s; }
.badge-arrow.open { transform: rotate(180deg); }
.section-body { padding: 28px 32px; border-top: 1px solid #30363d; font-size: 16px; color: #c9d1d9; display: none; }
.section-body.open { display: block; }
.section-body p { margin-bottom: 14px; text-align: justify; }
.section-body ul, .section-body ol { margin: 8px 0 16px 28px; }
.section-body li { margin-bottom: 6px; }
.sub-section { background: #0d1117; border: 1px solid #21262d; border-radius: 6px; margin: 14px 0; padding: 18px 24px; }
.sub-section h3 { color: #79c0ff; font-size: 15px; margin-bottom: 10px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin: 12px 0; }
.data-table th { background: #1c2437; color: #58a6ff; padding: 10px 12px; text-align: left; border: 1px solid #30363d; }
.data-table td { padding: 8px 12px; border: 1px solid #21262d; vertical-align: top; }
.data-table tr:nth-child(even) td { background: #0d1117; }
.ai-note { background: #1f2d3f; border: 1px solid #1f6feb; border-radius: 6px; padding: 12px 16px; margin: 14px 0; font-size: 14px; color: #79c0ff; }
.ai-note strong { color: #a5d6ff; }
.data-highlight { background: #1a2d4a; border: 2px solid #58a6ff; border-radius: 8px; padding: 16px 20px; margin: 14px 0; }
.data-highlight .label { color: #58a6ff; font-size: 13px; font-weight: bold; margin-bottom: 6px; }
.data-highlight .value { color: #e6edf3; font-size: 22px; font-weight: bold; }
.legend { display: flex; gap: 20px; align-items: center; font-size: 14px; }
</style>
</head>
<body>

<div class="doc-header">
    <div>
        <h1><span style="color:#58a6ff">💧</span> Programa de Abastecimiento y Control de Agua Potable</h1>
        <div style="color:#8b949e; font-size:15px; margin-top:4px;">Plan de Saneamiento Básico — Propiedad Horizontal</div>
    </div>
    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
        <div class="code-badge">FT-SST-228</div>
        <div class="legend">
            <span class="badge badge-static">Texto legal fijo</span>
            <span class="badge badge-data">Dato del cliente</span>
            <span class="badge badge-ai">🤖 Candidato IA</span>
        </div>
    </div>
</div>

<div class="nav-bar">
    <strong style="color:#58a6ff;font-size:14px;">Secciones:</strong>
    <span class="sep">|</span>
    <a href="#s11">1.1 Objetivo</a>
    <a href="#s12">1.2 Alcance</a>
    <a href="#s13">1.3 Definiciones</a>
    <a href="#s14">1.4 Proceso abastecimiento</a>
    <a href="#s15">1.5 Mantenimiento</a>
    <a href="#s16">1.6 Controles</a>
    <a href="#s17">1.7 Responsabilidades</a>
    <a href="#s18">1.8 Procedimiento limpieza tanques</a>
    <a href="#s19">1.9 Desinfección tanques</a>
    <a href="#s110">1.10 Control y seguimiento</a>
    <a href="#s111">1.11 Indicadores</a>
    <span class="sep">|</span>
    <a href="javascript:openAll()" style="color:#58a6ff">▶ Expandir todo</a>
    <a href="javascript:closeAll()" style="color:#8b949e">▼ Colapsar todo</a>
</div>

<div class="content">

<div class="section" id="s11">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.1 OBJETIVO</h2>
        <div class="badges"><span class="badge badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Garantizar el abastecimiento continuo de agua potable en calidad y cantidad suficiente para el <strong>[NOMBRE DEL CONJUNTO]</strong>, mediante el mantenimiento, limpieza y desinfección periódica de los tanques de almacenamiento y las redes internas de distribución.</p>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> El objetivo podría personalizarse con los datos reales del sistema de tanques del conjunto (cantidad, capacidad, tipo: enterrado/elevado).</div>
    </div>
</div>

<div class="section" id="s12">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.2 ALCANCE</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Aplica al sistema de almacenamiento y distribución de agua del <strong>[NOMBRE DEL CONJUNTO]</strong>:</p>
        <div style="display:flex; gap:20px; flex-wrap:wrap; margin:16px 0;">
            <div class="data-highlight" style="flex:1; min-width:200px;">
                <div class="label">🛢️ CANTIDAD DE TANQUES</div>
                <div class="value">[CANTIDAD_TANQUES]</div>
            </div>
            <div class="data-highlight" style="flex:1; min-width:200px;">
                <div class="label">📏 CAPACIDAD INDIVIDUAL</div>
                <div class="value">[CAPACIDAD_INDIVIDUAL]</div>
            </div>
            <div class="data-highlight" style="flex:1; min-width:200px;">
                <div class="label">💧 CAPACIDAD TOTAL</div>
                <div class="value">[CAPACIDAD_TOTAL]</div>
            </div>
        </div>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Con los datos de tanques se puede generar texto personalizado sobre la capacidad de almacenamiento y días de autonomía del conjunto.</div>
    </div>
</div>

<div class="section" id="s13">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.3 DEFINICIONES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Agua potable, agua cruda, tanque de almacenamiento, tanque elevado, tanque enterrado, red de distribución interna, cloración, cloro residual libre, turbidez, coliformes totales, coliformes fecales (E. coli).</p>
        <p style="color:#8b949e; font-style:italic;">Referencias: Resolución 2115 de 2007, Decreto 1575 de 2007, Ley 142 de 1994.</p>
    </div>
</div>

<div class="section" id="s14">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.4 PROCESO DE ABASTECIMIENTO DE AGUA POTABLE</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>El agua es suministrada por la empresa de acueducto local y almacenada en los tanques del conjunto antes de su distribución a las unidades privadas y áreas comunes.</p>
        <p>Flujo: <strong>Red pública → Tanques de almacenamiento → Red interna → Unidades y áreas comunes</strong></p>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Podría especificar el nombre de la empresa de acueducto, sistema de bombeo (si aplica), y la capacidad real del conjunto para almacenar agua en caso de corte.</div>
    </div>
</div>

<div class="section" id="s15">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.5 MANTENIMIENTO, LIMPIEZA Y DESINFECCIÓN</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Limpieza y desinfección de tanques: mínimo 2 veces al año (semestral), o cuando se detecten anomalías de color, olor o sabor del agua. Realizada por empresa autorizada o personal capacitado con uso de EPP completo.</p>
        <p>Mantenimiento preventivo de la red interna: revisión semestral de válvulas, tuberías y conexiones. Registro de cada intervención.</p>
    </div>
</div>

<div class="section" id="s16">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.6 CONTROLES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Controles de calidad del agua:</p>
        <ul>
            <li><strong>Cloro residual libre:</strong> 0.3 a 2.0 mg/L (Resolución 2115/2007)</li>
            <li><strong>pH:</strong> 6.5 a 9.0</li>
            <li><strong>Turbidez:</strong> máximo 2 UNT</li>
            <li><strong>Coliformes totales y E. coli:</strong> ausencia en 100 mL</li>
        </ul>
        <p>Medición de cloro residual: mínimo mensual con kit colorimétrico. Resultado registrado en bitácora.</p>
    </div>
</div>

<div class="section" id="s17">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.7 RESPONSABILIDADES</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <ul>
            <li><strong>Administrador(a):</strong> Contratación de empresa de limpieza de tanques, cronograma, verificación de calidad del agua.</li>
            <li><strong>Empresa de mantenimiento:</strong> Limpieza, desinfección y certificación. Entregar acta y certificado de agua tratada.</li>
            <li><strong>Personal de administración:</strong> Lectura mensual de cloro residual, registro en bitácora.</li>
            <li><strong>Responsable SG-SST:</strong> [NOMBRE DEL RESPONSABLE]</li>
        </ul>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Podría incluir el nombre de la empresa contratista de mantenimiento de tanques si se registra.</div>
    </div>
</div>

<div class="section" id="s18">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.8 PROCEDIMIENTO DE LIMPIEZA Y DESINFECCIÓN DE TANQUES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <ol>
            <li>Coordinación previa con la Administración (aviso a residentes 24h antes).</li>
            <li>Cierre de válvula de entrada y vaciado del tanque.</li>
            <li>Colocación de EPP: traje impermeable, botas, guantes, máscara respiratoria, arnés si aplica.</li>
            <li>Retiro de sedimentos y partículas gruesas con escoba y recogedor.</li>
            <li>Lavado de paredes, piso y techo del tanque con detergente y cepillo.</li>
            <li>Enjuague exhaustivo hasta eliminar el detergente.</li>
            <li>Preparación de solución de hipoclorito de sodio según volumen del tanque.</li>
            <li>Aplicación del desinfectante en todas las superficies internas.</li>
            <li>Tiempo de contacto mínimo 30 minutos.</li>
            <li>Enjuague final y verificación de ausencia de olor a cloro excesivo.</li>
            <li>Apertura de válvula de entrada y llenado del tanque.</li>
            <li>Medición de cloro residual libre antes de distribuir el agua.</li>
        </ol>
    </div>
</div>

<div class="section" id="s19">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.9 DESINFECCIÓN DE TANQUES DE AGUA</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Dosis de hipoclorito de sodio para desinfección: 50 mg/L (50 ppm) según volumen del tanque. Tiempo de contacto: mínimo 30 minutos.</p>
        <div class="data-highlight">
            <div class="label">📐 CÁLCULO PARA EL CONJUNTO</div>
            <div style="font-size:16px; color:#c9d1d9; margin-top:8px;">Con una capacidad total de <strong>[CAPACIDAD_TOTAL]</strong>, se requiere:</div>
            <div style="font-size:16px; color:#79c0ff; margin-top:4px;">Volumen tanque × 0.05 g/L = gramos de hipoclorito de calcio al 70%</div>
        </div>
        <div class="ai-note"><strong>🤖 Potencial IA:</strong> Con la capacidad real de los tanques se puede calcular automáticamente la dosis exacta de desinfectante requerida y mostrarla en el documento.</div>
    </div>
</div>

<div class="section" id="s110">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.10 CONTROL Y SEGUIMIENTO</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Registros obligatorios: bitácora mensual de cloro residual, certificados semestrales de limpieza de tanques, resultados de análisis microbiológicos del agua (mínimo 1 por año), registro de mantenimiento de redes.</p>
        <p>Ante anomalías: notificar de inmediato a la empresa de acueducto, no distribuir el agua hasta restablecer condiciones seguras.</p>
    </div>
</div>

<div class="section" id="s111">
    <div class="section-header" onclick="toggle(this)">
        <h2>1.11 INDICADORES</h2>
        <div class="badges"><span class="badge badge-static">Texto legal fijo</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <div class="sub-section">
            <h3>1️⃣ Garantía de continuidad del suministro de agua potable</h3>
            <table class="data-table">
                <tr><td style="width:40%;background:#161b22;font-weight:bold;color:#58a6ff;">Fórmula</td><td>(N° días con suministro continuo ÷ N° días del periodo) × 100</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#58a6ff;">Meta</td><td>≥ 98%</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#58a6ff;">Periodicidad</td><td>Mensual</td></tr>
            </table>
        </div>
        <div class="sub-section">
            <h3>2️⃣ Cumplimiento de limpieza y desinfección semestral de tanques</h3>
            <table class="data-table">
                <tr><td style="width:40%;background:#161b22;font-weight:bold;color:#58a6ff;">Fórmula</td><td>(N° limpiezas realizadas ÷ N° limpiezas programadas) × 100</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#58a6ff;">Meta</td><td>100%</td></tr>
                <tr><td style="background:#161b22;font-weight:bold;color:#58a6ff;">Periodicidad</td><td>Semestral</td></tr>
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
