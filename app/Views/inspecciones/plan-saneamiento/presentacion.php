<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FT-SST-219 — Plan de Saneamiento Básico</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: #0d1117; color: #e6edf3; font-size: 18px; line-height: 1.6; }
.doc-header { background: #1c2437; border-bottom: 4px solid #bd9751; padding: 28px 48px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.doc-header h1 { font-size: 26px; color: #fff; }
.code-badge { background: #bd9751; color: #1c2437; font-weight: bold; font-size: 20px; padding: 8px 20px; border-radius: 6px; }
.nav-bar { background: #161b22; padding: 12px 48px; border-bottom: 1px solid #30363d; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.nav-bar a { color: #8b949e; text-decoration: none; font-size: 14px; padding: 4px 10px; border-radius: 4px; transition: all .2s; }
.nav-bar a:hover { background: #21262d; color: #e6edf3; }
.nav-bar .sep { color: #30363d; }
.content { padding: 48px; max-width: 1400px; margin: 0 auto; }
.section { background: #161b22; border: 1px solid #30363d; border-radius: 10px; margin-bottom: 24px; overflow: hidden; }
.section-header { padding: 22px 32px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; transition: background .2s; }
.section-header:hover { background: #1c2437; }
.section-header h2 { font-size: 20px; color: #bd9751; font-weight: 600; }
.badges { display: flex; gap: 8px; align-items: center; }
.badge { font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.badge-static { background: #21262d; color: #8b949e; border: 1px solid #30363d; }
.badge-ai { background: #1f3a2e; color: #3fb950; border: 1px solid #238636; }
.badge-data { background: #2d2210; color: #bd9751; border: 1px solid #7d6323; }
.badge-arrow { color: #8b949e; font-size: 18px; transition: transform .2s; }
.badge-arrow.open { transform: rotate(180deg); }
.section-body { padding: 28px 32px; border-top: 1px solid #30363d; font-size: 16px; color: #c9d1d9; display: none; }
.section-body.open { display: block; }
.section-body p { margin-bottom: 14px; text-align: justify; }
.section-body ul, .section-body ol { margin: 8px 0 16px 28px; }
.section-body li { margin-bottom: 6px; }
.sub-section { border-radius: 8px; margin: 16px 0; overflow: hidden; }
.sub-header { padding: 14px 20px; display: flex; align-items: center; gap: 12px; }
.sub-header .icon { font-size: 22px; }
.sub-header h3 { font-size: 16px; font-weight: 600; }
.sub-body { padding: 16px 20px; font-size: 15px; }
.prog-limpieza .sub-header { background: #1c2437; border-left: 4px solid #bd9751; }
.prog-limpieza .sub-header h3 { color: #bd9751; }
.prog-limpieza .sub-body { background: #13192a; border: 1px solid #2d3247; border-top: none; }
.prog-residuos .sub-header { background: #1c2e1c; border-left: 4px solid #3fb950; }
.prog-residuos .sub-header h3 { color: #3fb950; }
.prog-residuos .sub-body { background: #111f11; border: 1px solid #1f3a1f; border-top: none; }
.prog-plagas .sub-header { background: #2e1c1c; border-left: 4px solid #ff7b72; }
.prog-plagas .sub-header h3 { color: #ff7b72; }
.prog-plagas .sub-body { background: #1f1111; border: 1px solid #3d1f1f; border-top: none; }
.prog-agua .sub-header { background: #1c1c2e; border-left: 4px solid #58a6ff; }
.prog-agua .sub-header h3 { color: #58a6ff; }
.prog-agua .sub-body { background: #11111f; border: 1px solid #1f2d3f; border-top: none; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; margin: 12px 0; }
.data-table th { background: #1c2437; color: #bd9751; padding: 10px 12px; text-align: left; border: 1px solid #30363d; }
.data-table td { padding: 8px 12px; border: 1px solid #21262d; vertical-align: top; }
.data-table tr:nth-child(even) td { background: #0d1117; }
.ai-note { background: #1f3a2e; border: 1px solid #238636; border-radius: 6px; padding: 12px 16px; margin: 14px 0; font-size: 14px; color: #3fb950; }
.ai-note strong { color: #7ee787; }
.kpi-card { background: #0d1117; border: 1px solid #21262d; border-radius: 8px; padding: 16px 20px; margin: 12px 0; }
.kpi-card .kpi-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; }
.kpi-row { display: flex; gap: 16px; flex-wrap: wrap; margin: 6px 0; font-size: 13px; }
.kpi-label { color: #8b949e; min-width: 120px; }
.kpi-val { color: #e6edf3; }
.legend { display: flex; gap: 20px; align-items: center; font-size: 14px; }
.doc-links { display: flex; gap: 12px; flex-wrap: wrap; margin: 14px 0; }
.doc-link { padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: bold; text-decoration: none; }
.link-limpieza { background: #2d2210; color: #bd9751; border: 1px solid #7d6323; }
.link-residuos { background: #1c2e1c; color: #3fb950; border: 1px solid #238636; }
.link-plagas { background: #2e1c1c; color: #ff7b72; border: 1px solid #8b2222; }
.link-agua { background: #1c1c2e; color: #58a6ff; border: 1px solid #1f6feb; }
</style>
</head>
<body>

<div class="doc-header">
    <div>
        <h1><span style="color:#bd9751">🛡️</span> Plan de Saneamiento Básico</h1>
        <div style="color:#8b949e; font-size:15px; margin-top:4px;">Documento consolidador — Integra los 4 programas del Plan</div>
    </div>
    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
        <div class="code-badge">FT-SST-219</div>
        <div class="legend">
            <span class="badge badge-static">Texto fijo</span>
            <span class="badge badge-data">Dato del cliente</span>
            <span class="badge badge-ai">🤖 Candidato IA</span>
        </div>
    </div>
</div>

<div class="nav-bar">
    <strong style="color:#bd9751;font-size:14px;">Secciones:</strong>
    <span class="sep">|</span>
    <a href="#intro">Introducción</a>
    <a href="#consolidacion">Consolidación indicadores</a>
    <a href="#tabla">Tabla consolidada</a>
    <a href="#resultados">Resultados a la fecha</a>
    <span class="sep">|</span>
    <strong style="font-size:13px;color:#8b949e;">Programas:</strong>
    <a href="#p-limpieza" style="color:#bd9751">📋 Limpieza</a>
    <a href="#p-residuos" style="color:#3fb950">♻️ Residuos</a>
    <a href="#p-plagas" style="color:#ff7b72">🐛 Plagas</a>
    <a href="#p-agua" style="color:#58a6ff">💧 Agua</a>
    <span class="sep">|</span>
    <a href="javascript:openAll()" style="color:#bd9751">▶ Expandir todo</a>
    <a href="javascript:closeAll()" style="color:#8b949e">▼ Colapsar todo</a>
</div>

<div class="content">

<!-- INTRODUCCIÓN -->
<div class="section" id="intro">
    <div class="section-header" onclick="toggle(this)">
        <h2>INTRODUCCIÓN</h2>
        <div class="badges"><span class="badge badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>El Plan de Saneamiento Básico del <strong>[NOMBRE DEL CONJUNTO]</strong> es el documento integrador que consolida los cuatro programas que componen el sistema de gestión sanitaria de la copropiedad, en cumplimiento de la Ley 9 de 1979, la Ley 675 de 2001, el Decreto 1072 de 2015 y demás normas sanitarias vigentes.</p>
        <p>Los cuatro programas que integran el Plan de Saneamiento Básico son:</p>
        <div class="doc-links">
            <span class="doc-link link-limpieza">📋 FT-SST-225 — Programa de Limpieza y Desinfección</span>
            <span class="doc-link link-residuos">♻️ FT-SST-226 — Manejo Integral de Residuos Sólidos</span>
            <span class="doc-link link-plagas">🐛 FT-SST-227 — Control Integrado de Plagas</span>
            <span class="doc-link link-agua">💧 FT-SST-228 — Abastecimiento y Control de Agua Potable</span>
        </div>
        <div class="ai-note">
            <strong>🤖 Potencial IA:</strong> La introducción podría personalizarse describiendo la naturaleza del conjunto (número de torres, unidades, antigüedad, características sanitarias relevantes) y el contexto de implementación del Plan.
        </div>
    </div>
</div>

<!-- CONSOLIDACIÓN -->
<div class="section" id="consolidacion">
    <div class="section-header" onclick="toggle(this)">
        <h2>CONSOLIDACIÓN DE INDICADORES Y DISPONIBILIDAD DE PROGRAMAS</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Este apartado verifica que los 4 programas hayan sido implementados y que sus indicadores estén disponibles para consolidación. Para cada programa se registra:</p>
        <ul>
            <li>Estado de implementación (activo / en proceso)</li>
            <li>Fecha de última actualización</li>
            <li>Responsable de ejecución</li>
            <li>Indicadores con meta y resultado del periodo</li>
        </ul>
        <div class="ai-note">
            <strong>🤖 Potencial IA:</strong> Este apartado podría generarse automáticamente cruzando los datos reales de los 4 programas registrados en la plataforma para el cliente específico (fechas, responsables, estados).
        </div>
    </div>
</div>

<!-- TABLA CONSOLIDADA -->
<div class="section" id="tabla">
    <div class="section-header" onclick="toggle(this)">
        <h2>TABLA CONSOLIDADA DE INDICADORES DEL PLAN DE SANEAMIENTO BÁSICO</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">

        <!-- AGUA POTABLE -->
        <div class="sub-section prog-agua" id="p-agua">
            <div class="sub-header">
                <span class="icon">💧</span>
                <h3>PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE — FT-SST-228</h3>
            </div>
            <div class="sub-body">
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#58a6ff;">KPI 1: Garantía de continuidad del suministro de agua</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Días con suministro ÷ Días del periodo) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">≥ 98%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Mensual</span></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#58a6ff;">KPI 2: Cumplimiento de limpieza y desinfección semestral de tanques</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Limpiezas realizadas ÷ Limpiezas programadas) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">100%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Semestral</span></div>
                </div>
                <div class="ai-note" style="margin-top:12px;"><strong>🤖 Potencial IA:</strong> Los resultados reales del KPI podrían jalarse automáticamente de la tabla de KPIs de agua potable registrados en la plataforma.</div>
            </div>
        </div>

        <!-- PLAGAS -->
        <div class="sub-section prog-plagas" id="p-plagas">
            <div class="sub-header">
                <span class="icon">🐛</span>
                <h3>PROGRAMA DE CONTROL INTEGRAL DE PLAGAS Y ROEDORES — FT-SST-227</h3>
            </div>
            <div class="sub-body">
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#ff7b72;">KPI 1: Cumplimiento de fumigación semestral</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Fumigaciones realizadas ÷ Fumigaciones programadas) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">100%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Semestral</span></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#ff7b72;">KPI 2: Cumplimiento de desratización semestral</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Desratizaciones realizadas ÷ Desratizaciones programadas) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">100%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Semestral</span></div>
                </div>
                <div class="ai-note" style="margin-top:12px;"><strong>🤖 Potencial IA:</strong> Los resultados podrían jalarse de la tabla tbl_kpi_plagas del cliente.</div>
            </div>
        </div>

        <!-- RESIDUOS -->
        <div class="sub-section prog-residuos" id="p-residuos">
            <div class="sub-header">
                <span class="icon">♻️</span>
                <h3>PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SÓLIDOS — FT-SST-226</h3>
            </div>
            <div class="sub-body">
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#3fb950;">KPI 1: Condición sanitaria del cuarto de almacenamiento temporal</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Criterios cumplidos ÷ Criterios evaluados) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">≥ 90%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Mensual</span></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#3fb950;">KPI 2: Nivel de correcta separación en la fuente</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Áreas con separación correcta ÷ Áreas verificadas) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">≥ 85%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Mensual</span></div>
                </div>
                <div class="ai-note" style="margin-top:12px;"><strong>🤖 Potencial IA:</strong> Resultados jalados de tbl_kpi_residuos del cliente.</div>
            </div>
        </div>

        <!-- LIMPIEZA -->
        <div class="sub-section prog-limpieza" id="p-limpieza">
            <div class="sub-header">
                <span class="icon">📋</span>
                <h3>PROGRAMA DE LIMPIEZA Y DESINFECCIÓN — FT-SST-225</h3>
            </div>
            <div class="sub-body">
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#bd9751;">KPI 1: Cumplimiento de actividades de limpieza y desinfección</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Días registrados ÷ Días hábiles del periodo) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">≥ 95%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Mensual</span></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title" style="color:#bd9751;">KPI 2: Estado de los elementos de limpieza</div>
                    <div class="kpi-row"><span class="kpi-label">Fórmula:</span><span class="kpi-val">(Elementos en buen estado ÷ Total verificados) × 100</span></div>
                    <div class="kpi-row"><span class="kpi-label">Meta:</span><span class="kpi-val" style="color:#3fb950;">≥ 90%</span></div>
                    <div class="kpi-row"><span class="kpi-label">Resultado:</span><span class="kpi-val" style="color:#f0a84c;">[DATO REAL DEL KPI]</span></div>
                    <div class="kpi-row"><span class="kpi-label">Periodicidad:</span><span class="kpi-val">Mensual</span></div>
                </div>
                <div class="ai-note" style="margin-top:12px;"><strong>🤖 Potencial IA:</strong> Resultados jalados de tbl_kpi_limpieza del cliente.</div>
            </div>
        </div>

    </div>
</div>

<!-- RESULTADOS -->
<div class="section" id="resultados">
    <div class="section-header" onclick="toggle(this)">
        <h2>RESULTADOS CON CORTE A LA FECHA</h2>
        <div class="badges"><span class="badge badge-data">Dato del cliente</span><span class="badge-ai">🤖 Candidato IA</span><span class="badge-arrow">▼</span></div>
    </div>
    <div class="section-body">
        <p>Este apartado presenta un resumen del estado de implementación del Plan de Saneamiento Básico del <strong>[NOMBRE DEL CONJUNTO]</strong> con corte a la fecha de generación del documento.</p>
        <table class="data-table">
            <tr>
                <th>PROGRAMA</th>
                <th>ESTADO</th>
                <th>ÚLTIMA ACTUALIZACIÓN</th>
                <th>KPI 1</th>
                <th>KPI 2</th>
                <th>SEMÁFORO</th>
            </tr>
            <tr>
                <td>💧 Agua Potable</td>
                <td>[ESTADO]</td>
                <td>[FECHA]</td>
                <td>[%]</td>
                <td>[%]</td>
                <td>[🟢/🟡/🔴]</td>
            </tr>
            <tr>
                <td>🐛 Control Plagas</td>
                <td>[ESTADO]</td>
                <td>[FECHA]</td>
                <td>[%]</td>
                <td>[%]</td>
                <td>[🟢/🟡/🔴]</td>
            </tr>
            <tr>
                <td>♻️ Residuos Sólidos</td>
                <td>[ESTADO]</td>
                <td>[FECHA]</td>
                <td>[%]</td>
                <td>[%]</td>
                <td>[🟢/🟡/🔴]</td>
            </tr>
            <tr>
                <td>📋 Limpieza y Desinfección</td>
                <td>[ESTADO]</td>
                <td>[FECHA]</td>
                <td>[%]</td>
                <td>[%]</td>
                <td>[🟢/🟡/🔴]</td>
            </tr>
        </table>
        <div class="ai-note">
            <strong>🤖 Gran candidato IA:</strong> Este es el apartado de mayor valor para IA — puede generarse automáticamente con texto narrativo que interprete los resultados de los 4 KPIs, identifique el programa con mayor oportunidad de mejora, y sugiera acciones correctivas específicas para el conjunto.
        </div>
    </div>
</div>

</div>

<script>
function toggle(header) { var body=header.nextElementSibling; var arrow=header.querySelector('.badge-arrow'); var isOpen=body.classList.contains('open'); body.classList.toggle('open',!isOpen); arrow.classList.toggle('open',!isOpen); }
function openAll() { document.querySelectorAll('.section-body').forEach(b=>b.classList.add('open')); document.querySelectorAll('.badge-arrow').forEach(a=>a.classList.add('open')); }
function closeAll() { document.querySelectorAll('.section-body').forEach(b=>b.classList.remove('open')); document.querySelectorAll('.badge-arrow').forEach(a=>a.classList.remove('open')); }
document.addEventListener('DOMContentLoaded', function() { openAll(); });
</script>
</body>
</html>
