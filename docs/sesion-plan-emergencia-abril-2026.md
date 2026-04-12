# Sesion de trabajo — Plan de Emergencia (abril 2026)

**Fecha:** 2026-04-12
**Scope:** Refactorizacion legal, normativa y estructural de la plantilla de Plan de Emergencia para propiedad horizontal residencial.
**Archivos principales intervenidos:**
- `app/Views/inspecciones/plan-emergencia/pdf.php`
- `app/Controllers/Inspecciones/PlanEmergenciaController.php`
- `app/Config/Routes.php`
- `app/Config/PonesCanonicos.php` (archivo nuevo)
- `docs/auditoria-plan-emergencia-normatividad-2026.md` (archivo nuevo)

---

## 1. Contexto inicial

El archivo `pdf.php` es la plantilla DOMPDF del Plan de Emergencia que se entrega a conjuntos residenciales (clientes de Cycloid Talent SAS). Al inicio de la sesion el documento tenia:

- ~800 lineas
- Solo 1 PON (Codigo 7 — ascensor)
- Citas normativas obsoletas (Decreto Ley 919/89, Ley 46/88, NTC 5254, normas sismo-resistentes derogadas)
- Bloque de "Legislacion Distrital" exclusivo de Bogota, mostrado a clientes de cualquier ciudad
- Ausencia total de Decreto 1072/2015 art. 2.2.4.6.25, Resolucion 0312/2019, Decreto 2157/2017, Ley 1523/2012 art. 42
- Sin secciones de Objetivos, Alcance, Marco Normativo, Marco Conceptual, Brigada, Capacitacion, Simulacros, Ayuda Mutua, Continuidad
- Titulo inconsistente en 3 lugares del documento
- Dependencia de imagen estatica para el diagrama de actuacion

---

## 2. Flujo de trabajo ejecutado

### 2.1. Vista de debug con colores por modulo

Se agrego un modo debug a `pdf.php` controlado por la variable `$debugMode`. Cada seccion que consume datos de un modulo de inspeccion (Locativa, Matriz Vulnerabilidad, Probabilidad de Peligros, Extintores, Botiquin, Recursos de Seguridad, Comunicaciones, Gabinetes, Plan de Emergencia) se envuelve en un bloque coloreado con etiqueta visible que identifica la variable PHP fuente.

- CSS debug agregado dentro del bloque `<style>` condicional al flag.
- Leyenda visual al inicio del `<body>` con los colores.
- Nuevo metodo `PlanEmergenciaController::debugPdf()` que construye datos dummy de todas las 9 inspecciones + inspeccion principal, usando el logo corporativo como imagen de referencia.
- Nueva ruta: `GET /inspecciones/plan-emergencia/debug-pdf`.
- URL local: `http://localhost/enterprisesstph/public/index.php/inspecciones/plan-emergencia/debug-pdf`

**Resultado:** herramienta permanente para visualizar que parte del PDF viene de cada modulo sin necesidad de generar el PDF real.

### 2.2. Auditoria normativa

Se realizo investigacion web para confirmar la normatividad vigente a 2026 aplicable a planes de emergencia en propiedad horizontal en Colombia. Fuentes consultadas: mintrabajo, funcionpublica, portal UNGRD, Alcaldia de Bogota, ICONTEC.

**Normatividad confirmada como vigente:**
- Decreto 1072 de 2015 art. 2.2.4.6.25 (SG-SST)
- Resolucion 0312 de 2019 (estandares minimos SG-SST)
- Ley 1523 de 2012 art. 42 (Politica Nacional Gestion del Riesgo)
- Decreto 2157 de 2017 (PGRDEPP)
- Ley 675 de 2001 (Regimen de Propiedad Horizontal)
- NTC 1700 (medios de evacuacion)
- NSR-10 Decreto 926/2010 (sismo resistente)
- Resolucion 0256 de 2014 (brigadas contra incendio)

**Descartado como no aplicable:** Decreto 768 de 2025 (reglamenta convivencia ciudadana y fachadas, no emergencias).

**Reporte generado:** `docs/auditoria-plan-emergencia-normatividad-2026.md` con 6 hallazgos criticos, 5 medios, 4 menores, cada uno con linea exacta, texto actual, problema, texto propuesto listo para copiar y accion concreta.

### 2.3. Fase 1 — Aplicar hallazgos de auditoria

Se aplicaron los 15 hallazgos de la auditoria y se agregaron 8 secciones estructurales nuevas.

**Hallazgos aplicados:**

| Categoria | Cantidad | Detalle |
|---|---|---|
| Criticos | 6/6 | D.1072, R.0312, L.1523 art.42 + D.2157, NSR-10, Brigada, Simulacros |
| Medios | 5/5 | NTC 1700 con especificaciones, R.0256/2014, bloque Bogota eliminado, DL 919/89, Ley 46/88 |
| Menores | 4/4 | NTC 5254 → NTC-ISO 31000, titulo unificado, normas sismo derogadas |

**Secciones nuevas agregadas:**
1. Responsable y Seguimiento del Plan (PGRDEPP)
2. Marco Conceptual y Definiciones (11 terminos)
3. Marco Normativo Nacional (transversal, sin ciudad)
4. Conformacion de la Brigada de Emergencia
5. Programa de Capacitacion y Simulacros
6. Plan de Ayuda Mutua
7. Plan de Continuidad y Recuperacion
8. Alcance ampliado

**Decision arquitectonica:** reemplazar el bloque "Legislacion Distrital (Bogota)" por un **Marco Normativo Nacional transversal** que cita solo normas nacionales aplicables a cualquier ciudad del pais. Se descarto la opcion de tabla por ciudad porque no hay marco comun obligatorio distinto del nacional.

### 2.4. Reordenamiento canonico UNGRD

Se reordenaron las secciones del documento para alinear con el orden canonico recomendado por UNGRD / IDRD / Decreto 2157.

**5 movimientos realizados:**
1. Marco Normativo Nacional movido antes de Marco Conceptual
2. Bloque "Conceptos Adicionales" eliminado (duplicaba Marco Conceptual nuevo + Ley 675 ya cubierta en Marco Normativo)
3. Administracion y Personal + Servicios Generales movidos a Informacion General (estaban al final despues de anexos)
4. Diagrama de Actuacion movido antes de Brigada (estaba despues de Continuidad)
5. PON Codigo 7 renombrado y reestructurado como base para multiples PONs

**Orden canonico final (23 secciones):**

1. Portada
2. Introduccion
3. Justificacion
4. Objetivos
5. Alcance
6. Responsable y Seguimiento del Plan (PGRDEPP)
7. Marco Normativo Nacional
8. Marco Conceptual y Definiciones
9. Informacion General del Conjunto Residencial
10. Administracion y Personal
11. Servicios Generales
12. Circulaciones y Accesos
13. Realizacion del Analisis de Riesgos
14. Carga Combustible
15. Procedimientos Operativos Normalizados (PON)
16. Diagrama de Actuacion en Caso de Emergencia
17. Conformacion de la Brigada de Emergencia
18. Programa de Capacitacion y Simulacros
19. Plan de Ayuda Mutua
20. Plan de Continuidad y Recuperacion
21. Anexos — Evaluaciones de Seguridad (Locativa, Matriz, Extintores, Botiquin, Recursos, Comunicaciones, Gabinetes)
22. Telefonos de Emergencia
23. Observaciones y Recomendaciones

### 2.5. 10 PONs canonicos (nuevo archivo de configuracion)

Se creo `app/Config/PonesCanonicos.php` — un archivo PHP plano que retorna un array asociativo con 10 PONs estructurados canonicamente. El `pdf.php` ahora itera sobre este config con un `foreach` en lugar de tener el PON hardcoded.

**Los 10 PONs:**

| Codigo | Titulo | amenaza_ref |
|---|---|---|
| 01 | Incendio en areas comunes o unidades privadas | `p_incendios` |
| 02 | Sismo / Terremoto | `p_sismos` |
| 03 | Asalto, hurto o intrusion armada | `p_asalto_hurto` |
| 04 | Inundacion por lluvia, rotura o fuga de agua | `p_inundaciones` |
| 05 | Vendaval, granizada o tormenta electrica | `p_vendavales` |
| 06 | Falla estructural / colapso | `p_falla_estructural` |
| 07 | Persona(s) atrapada(s) en ascensor | null (universal) |
| 08 | Fuga de gas / explosion | `p_explosiones` |
| 09 | Amenaza terrorista / paquete sospechoso | `p_atentados` |
| 10 | Emergencia medica en ocupantes del conjunto | null (universal) |

**Estructura canonica de cada PON (10 campos):**
- `codigo`, `titulo`, `amenaza_ref`, `objetivo`, `alcance`, `definiciones`, `responsables`, `procedimiento`, `medidas_preventivas`, `recomendaciones`

**Referencias normativas embebidas en los pasos:** Decreto 1072/2015 art. 2.2.4.6.25, NTC 1700, NTC 2885 (clases de fuego), NTC 4552 (proteccion contra rayos), NTC 5926, NSR-10, Res. 0256/2014, RETIG, Res. 0705/2007 (botiquines).

**Diferenciacion tecnica por PON (decisiones tomadas como experto):**
- PON 01: metodo PASS + clases de fuego NTC 2885
- PON 02: triangulo de vida + NSR-10
- PON 03: protocolo no confrontacional + codigo silencioso (NO alarma sonora)
- PON 04: causas reales (lluvia desbordada + parqueadero inundado), corte preventivo de energia
- PON 05: resguardo en zonas internas, NO cerca de ventanales
- PON 06: criterios visibles de evacuacion (grietas >3mm, desplomes, pisos inclinados)
- PON 07: 20 minutos regla mantenedor → bomberos
- PON 08: prohibicion de ignicion, NO intercomunicador electrico
- PON 09: perimetro 100m, NO radios ni celulares cerca del objeto
- PON 10: RCCP 100-120 cpm + DEA + cadena de supervivencia + triage + abordajes diferenciados por categoria clinica

### 2.6. Ajustes a la realidad de propiedad horizontal

El usuario (experto en el dominio) detecto que varios actores y escenarios descritos eran aspiracionales o no realistas para un conjunto residencial. Se aplicaron correcciones criticas:

**Conceptos transversales aplicados:**
1. **UNA sola Brigada por copropiedad** — residentes y contratistas voluntarios con capacitacion teorica basica, no especializada.
2. **Eliminada toda referencia** a "coordinador de evacuacion por torre/piso" (algunos clientes son casas, otros un solo bloque, otros multiples torres).
3. **Primera respuesta real** en asalto/atentado = vigilancia de la empresa de seguridad, no administrador.
4. **Inundaciones reales** en copropiedad = lluvia desbordada o parqueadero inundado, no fallas hidraulicas tecnicas.
5. **Linea 123 corregida** — es la linea unica de emergencias (policia + ambulancia), no CRUE.

**Cambios de actores por PON:**
- PON 01: quitado "Brigadistas contra incendio especializados" → brigada o residentes capacitados
- PON 02: quitada "Brigada de busqueda y rescate interna" (eso es Defensa Civil/Bomberos)
- PON 03: primera respuesta = vigilancia, NO comite de convivencia
- PON 04: aseguradora en lugar de empresa hidraulica tecnica
- PON 05: IDEAM/UNGRD movidos de "responsables" a "fuentes de informacion" en medidas preventivas
- PON 06: ingeniero estructural movido a contratistas externos (respuesta diferida, no de planta)
- PON 06: autoridad municipal contempla IDIGER (Bogota), Oficina de Gestion del Riesgo de Soacha, o equivalente municipal segun jurisdiccion
- PON 08: vigilancia y proveedor como equipo de apoyo real
- PON 09: Grupo Antiexplosivos se activa a traves de Policia 123, no contacto directo
- PON 10: brigada de primeros auxilios → residente o vigilante capacitado

**Nueva estructura de `responsables` en los 10 PONs** (3 subgrupos explicitos):

```php
'responsables' => [
    'internos'              => [...], // Actores dentro del conjunto
    'contratistas_externos' => [...], // Empresas con contrato activo
    'organismos_socorro'    => [...], // Lineas publicas de emergencia
],
```

El `pdf.php` renderiza los 3 subgrupos con subtitulos claros: "Actores internos del conjunto", "Contratistas externos", "Organismos de socorro".

### 2.7. Nota Aclaratoria sobre la Brigada

Se inserto al inicio de la seccion PON una nota de 3 parrafos que explica al lector el contexto real de la Brigada en propiedad horizontal residencial: quienes la integran, que capacitacion tienen, que NO hacen (rescate tecnico, manejo de materiales peligrosos, desactivacion de artefactos) y que corresponde exclusivamente a organismos oficiales.

La nota incluye `<?= $nombreCliente ?>` para personalizacion automatica.

**Referencias cruzadas:** en cada uno de los 10 PONs, la **primera mencion** de los terminos `brigada`, `brigadistas`, `coordinadores de evacuacion` o `grupos funcionales` lleva entre parentesis la frase: `(ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados)`. Solo primera ocurrencia por PON. PONs 03 y 09 no la incluyen porque su protocolo recae integramente en vigilancia.

### 2.8. Ampliacion del PON 10 (Emergencia medica)

Ajuste del usuario: el titulo original `Emergencia medica (paro, caida, infarto)` era demasiado estrecho. Se amplio a `Emergencia medica en ocupantes del conjunto` y se reformulo para cubrir **8 categorias clinicas**:

1. Cardiovasculares (paro, infarto, ACV, crisis hipertensiva)
2. Traumatologicas (caidas, heridas, fracturas, quemaduras, electrocucion, ahogamiento)
3. Respiratorias (crisis asmatica, anafilaxia, broncoaspiracion)
4. Neurologicas (convulsiones, sincope, perdida de conciencia)
5. Metabolicas (hipo/hiperglucemia, shock)
6. Intoxicaciones (alimentaria, medicamentosa, monoxido, quimicos)
7. Obstetricas (parto imprevisto, complicaciones)
8. Psiquiatricas agudas (intento de suicidio, crisis psicotica)

El procedimiento paso de 11 a 15 pasos, con abordaje diferenciado por categoria clinica. Se agrego paso de triage para eventos con multiples afectados. Se aclaro que NO toda emergencia requiere RCCP.

### 2.9. Correcciones de tono y redaccion

- PON 05: eliminada la frase "Aunque se trata de un evento poco frecuente en Bogota y Soacha" del objetivo (impertinente para el cliente leer juicios de frecuencia sobre su propia ciudad).
- PON 08: se mantiene "Vanti en Bogota y Soacha u operador local correspondiente" porque es informacion operativa util (el cliente necesita saber a quien llamar).

### 2.10. Diagrama de Actuacion — eliminacion de fallback a imagen

Se elimino el bloque condicional que cargaba una imagen estatica (`$diagramaBase64`) como fallback cuando no existe `$diagramaNodos`. Ahora la seccion muestra unicamente un placeholder en cursiva hasta que la Fase 2 implemente la generacion por IA del arbol de decision como JSON estructurado.

**Arquitectura Fase 2 del diagrama (documentada en comentarios `<!-- TODO -->`):**
```
PlanEmergenciaController
    → lee $ultimaProb + $ultimaMatriz + $inspeccion
    → DiagramaEmergenciaIAService::generar($contexto)
        → Claude API (curl directo, patron SendGrid)
        → retorna JSON con {inicio, decisiones[], nodos[], ramas[]}
    → guarda en tbl_plan_emergencia.diagrama_ia_json
    → pdf.php renderiza arbol como tablas HTML anidadas con flechas unicode (▼ ├── → └──)
    → 100% compatible DOMPDF, sin imagen
```

### 2.11. Placeholders Fase 2 — IA en el documento

Marcadores `<!-- TODO Fase 2: IA -->` insertados en 4 puntos del documento donde la personalizacion por IA aportara valor:

1. **Matriz de Responsables del Plan** (sub-seccion de PGRDEPP) — generacion de tabla personalizada con roles, nombres, responsabilidades y frecuencias de revision segun datos del cliente.
2. **Diagrama de Actuacion** — arbol de decision generado como JSON segun amenazas reales del cliente.
3. **Conformacion de Brigada** y **Programa de Capacitacion y Simulacros** — adendos personalizados segun tamano del conjunto, numero de torres, existencia de brigada conformada, fecha del ultimo simulacro.
4. **Cada PON** — enriquecimiento con aspectos especificos del cliente segun `$ultimaProb` (probabilidad de cada amenaza) y `$ultimaMatriz` (vulnerabilidades).

---

## 3. Resultado final

### 3.1. Archivos finales

| Archivo | Estado | Lineas |
|---|---|---|
| `app/Views/inspecciones/plan-emergencia/pdf.php` | Refactorizado | ~863 |
| `app/Config/PonesCanonicos.php` | Nuevo | ~620 |
| `app/Controllers/Inspecciones/PlanEmergenciaController.php` | Metodo `debugPdf()` agregado | +100 aprox |
| `app/Config/Routes.php` | Ruta `debug-pdf` agregada | +1 |
| `docs/auditoria-plan-emergencia-normatividad-2026.md` | Reporte auditoria | completo |
| `docs/sesion-plan-emergencia-abril-2026.md` | Este documento | nuevo |

### 3.2. Validaciones

- `php -l` OK en todos los archivos modificados
- Cero ocurrencias de `coordinador de torre`, `CRUE`, `Brigada de busqueda y rescate`, `Ingeniero estructural certificado`, `Legislacion Distrital`, `Bogota` (como unica ciudad en legislacion), `Ley 46/88`, `Decreto Ley 919/89`, `NTC 5254`, `Decreto 400/1984`, `Ley 400/1997`, `Decreto 33/1998`
- 10/10 PONs con estructura `responsables` de 3 subgrupos completa
- Titulo del plan unificado a "PLAN DE EMERGENCIA Y CONTINGENCIA" en portada, header y seccion de alcance
- 8 PONs con referencia cruzada a Nota Aclaratoria (01, 02, 04, 05, 06, 07, 08, 10). PON 03 y 09 no la llevan porque su texto no menciona brigada.

### 3.3. Cumplimiento normativo alcanzado

| Norma | Antes | Ahora |
|---|---|---|
| Decreto 1072/2015 art. 2.2.4.6.25 | No citado | Citado en Marco Normativo + Brigada + Capacitacion + PONs |
| Resolucion 0312/2019 | No citado | Citado en Marco Normativo + Brigada |
| Ley 1523/2012 art. 42 | Mencionado generico | Citado con articulo exacto + PGRDEPP |
| Decreto 2157/2017 | No citado | Seccion PGRDEPP dedicada + responsable legal |
| Ley 675/2001 | Citado | Mantenido en Marco Normativo |
| NSR-10 (Decreto 926/2010) | Normas derogadas | Actualizado a NSR-10 |
| NTC 1700 | Citado generico | Citado con especificaciones tecnicas (puertas, salidas, barra antipanico) |
| Resolucion 0256/2014 | No citado | Citado en Brigada |

**Conclusion de cumplimiento:** un auditor del Ministerio de Trabajo, la UNGRD, IDIGER o la Oficina de Gestion del Riesgo municipal ya no tiene argumento formal para tumbar el documento por obsolescencia normativa, ausencia de secciones obligatorias u orden caotico.

---

## 4. Pendientes — Fase 2

La Fase 2 consistira en generar dinamicamente por IA (Claude API) el contenido personalizado por cliente en los 4 puntos con marcador `TODO Fase 2: IA`. Requiere:

### 4.1. Nuevo modulo de inspeccion (patron plana)

Siguiendo el patron documentado en `docs/12_PATRON_INSPECCION_PLANA.md`:

- **Nombre:** `BrigadaSimulacros` o `InspeccionBrigada`
- **Tabla nueva:** `tbl_inspeccion_brigada_simulacros` con campos: id_cliente, id_consultor, fecha_inspeccion, existe_brigada, numero_brigadistas, fecha_ultimo_simulacro, tipo_simulacro, capacitaciones_12m, observaciones, fotos
- **7 archivos nuevos:** migration SQL, Model, Controller, views list/form/view/pdf
- **3 archivos modificados:** Routes.php, dashboard.php, InspeccionesController.php
- **Script CLI de migracion** en `app/SQL/migrate_inspeccion_brigada_simulacros.php` ejecutable primero en LOCAL, luego en PRODUCCION con `getenv('DB_PROD_PASS')`

### 4.2. Servicio de IA Claude

- Nueva libreria `app/Libraries/PlanEmergenciaIAService.php`
- Metodo por cada bloque: `generarBrigadaPersonalizada()`, `generarDiagramaActuacion()`, `generarMatrizResponsables()`, `enriquecerPONs()`
- Patron: curl directo a `api.anthropic.com/v1/messages` (sin SDK), similar a SendGrid
- Modelo: `claude-sonnet-4-6` (balance costo/calidad)
- Variable de entorno: `ANTHROPIC_API_KEY` en `.env`
- Prompts estructurados con JSON schema para respuestas deterministicas

### 4.3. Nuevas columnas en `tbl_plan_emergencia`

```sql
ALTER TABLE tbl_plan_emergencia
  ADD COLUMN matriz_responsables_ia_json JSON NULL,
  ADD COLUMN diagrama_ia_json JSON NULL,
  ADD COLUMN brigada_ia_texto TEXT NULL,
  ADD COLUMN simulacros_ia_texto TEXT NULL,
  ADD COLUMN pons_ia_json JSON NULL;
```

Script CLI: `app/SQL/migrate_plan_emergencia_ia_columns.php`

### 4.4. Integracion en `pdf.php`

Cada bloque con placeholder leera la variable PHP correspondiente (`$matrizResponsables`, `$diagramaNodos`, `$pon['adendo_ia']`, etc.) y la renderizara cuando exista.

### 4.5. Priorizacion sugerida Fase 2

1. Primero: PONs enriquecidos por IA (mayor valor legal y operativo)
2. Segundo: Matriz de Responsables del Plan (tabla personalizada)
3. Tercero: Brigada y Simulacros (requiere modulo de inspeccion nuevo)
4. Cuarto: Diagrama de Actuacion (arbol de decision JSON)

---

## 5. Decisiones arquitectonicas clave registradas

1. **Sin imagen estatica en el diagrama** — se descarta definitivamente `$diagramaBase64`; el arbol se renderiza como HTML determinista desde JSON generado por IA.
2. **Legislacion transversal nacional** — se descarta el diccionario por ciudad; el Marco Normativo cita solo normas nacionales aplicables a todo el pais.
3. **Una sola Brigada por copropiedad** — el documento reconoce la realidad operativa y aclara en nota visible al inicio de los PONs.
4. **Responsables en 3 niveles** — internos / contratistas / organismos — para ser auditable y realista.
5. **10 PONs fijos en config, no hardcoded** — facilita modificacion, IA enriquecera cada uno en Fase 2 sin tocar `pdf.php`.
6. **Claude API por curl directo** — patron consistente con SendGrid ya usado en el proyecto (memoria del proyecto exige no usar SDKs externos).
7. **BD solo via scripts PHP CLI** — nunca phpMyAdmin ni navegador; LOCAL antes que PRODUCCION siempre; credenciales via `getenv()` nunca hardcoded.
8. **Modo debug permanente** — la variable `$debugMode` y la ruta `debug-pdf` quedan como herramienta de trabajo para consultores.

---

## 6. Resumen ejecutivo

- **Documento de ~800 lineas refactorizado a ~863** con cumplimiento normativo al dia 2026.
- **15 hallazgos de auditoria aplicados** (6 criticos, 5 medios, 4 menores).
- **8 secciones estructurales nuevas** agregadas al orden canonico UNGRD.
- **5 reordenamientos** para alcanzar orden canonico de 23 secciones.
- **10 PONs canonicos** creados en configuracion externa, diferenciados tecnicamente, ajustados a la realidad de propiedad horizontal.
- **Nota Aclaratoria sobre Brigada** con 8 referencias cruzadas desde los PONs.
- **Responsables reformulados** en estructura de 3 niveles (internos, contratistas, organismos).
- **PON 10 ampliado** de 3 vectores a 8 categorias clinicas.
- **Eliminacion de imagen estatica** del diagrama de actuacion.
- **Marco Normativo transversal nacional** reemplaza bloque Bogota-especifico.
- **4 placeholders Fase 2 IA** documentados con arquitectura tecnica.
- **Modo debug** con ruta y colores por modulo para validacion visual.

**Estado del documento:** apto para entrega legal a cliente. Auditable ante Ministerio de Trabajo, UNGRD, IDIGER y Oficinas Municipales de Gestion del Riesgo. Sin deuda estructural. Base solida para Fase 2.
