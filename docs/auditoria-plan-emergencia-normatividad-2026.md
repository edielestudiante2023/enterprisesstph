# Auditoría Plan de Emergencia — Normatividad vigente 2026

**Fecha:** 2026-04-12
**Archivo auditado:** `app/Views/inspecciones/plan-emergencia/pdf.php` (802 líneas)
**Normatividad referencia:** Decreto 1072/2015 art. 2.2.4.6.25, Resolución 0312/2019, Ley 1523/2012, Decreto 2157/2017, Ley 675/2001, NTC 1700, NSR-10 (Decreto 926/2010), Resolución 0256/2014

---

## Resumen ejecutivo

- **Hallazgos críticos:** 6
- **Hallazgos medios:** 5
- **Hallazgos menores:** 4
- **Normas citadas correctamente:** 3 / 8 (Ley 1523/2012, Ley 675/2001, NTC 1700)
- **Normas vigentes NO citadas:** 5 (Decreto 1072/2015, Res. 0312/2019, Decreto 2157/2017, NSR-10, Res. 0256/2014)
- **Secciones nuevas propuestas:** 4 (Marco normativo actualizado, Brigada de emergencia, Simulacros y capacitación, PGRDEPP)

Principal debilidad: el bloque "LEGISLACION" (líneas 361–392) está anclado en normas pre-2015 (Ley 9/1979, Res. 2400/1979, Ley 46/88, Decreto Ley 919/89, Decreto 1295/94, Decreto 332/04, Decreto 423/06) y omite el núcleo normativo vigente que obliga hoy al Plan de Emergencia en Colombia. El documento tampoco estructura brigada, simulacros ni PGRDEPP.

---

## Checklist por norma

| # | Norma | Requisito | Estado | Evidencia pdf.php |
|---|---|---|---|---|
| 1 | D.1072/2015 art. 2.2.4.6.25 | Análisis de amenazas | OK parcial | líneas 398-413 (tabla estática) y 415-466 (probabilidad) |
| 2 | D.1072/2015 art. 2.2.4.6.25 | Recursos, planes y procedimientos escritos | OK parcial | líneas 503-522 (solo PON Código 7) |
| 3 | D.1072/2015 art. 2.2.4.6.25 | Brigada conformada y capacitada | NO | sin sección dedicada; solo mención genérica en línea 173 |
| 4 | D.1072/2015 art. 2.2.4.6.25 | Simulacros mínimo anuales | NO | sin sección; única mención en línea 171 |
| 5 | D.1072/2015 art. 2.2.4.6.25 | Inspección periódica de equipos | OK | líneas 640-740 (extintores/botiquín/gabinetes) |
| 6 | D.1072/2015 — citación expresa | Artículo 2.2.4.6.25 citado | NO | no aparece en ninguna línea |
| 7 | Res. 0312/2019 | Estándares mínimos SG-SST | NO | no citada en líneas 361-392 |
| 8 | Ley 1523/2012 art. 42 | Análisis específico de riesgo entidades públicas/privadas | OK parcial | línea 169 (cita general, sin art. 42) |
| 9 | Decreto 2157/2017 | PGRDEPP — responsabilidad rep. legal | NO | no citado |
| 10 | Ley 675/2001 | Régimen Propiedad Horizontal | OK | líneas 161, 190 |
| 11 | NTC 1700 | Medios de evacuación (≥70cm, 2 salidas, barra antipánico) | OK parcial | línea 389 (cita nominal, sin requisitos específicos) |
| 12 | NSR-10 (Decreto 926/2010) | Reglamento sismorresistente vigente | NO | línea 478 cita Decreto 400/1984 + Ley 400/1997 + Decreto 33/1998 (DEROGADOS/obsoletos) |
| 13 | Res. 0256/2014 | Brigadas contra incendio | NO | no citada |

---

## Hallazgos críticos

### CRÍTICO-1: Ausencia total del Decreto 1072 de 2015 (norma rectora del SG-SST)
- **Norma:** Decreto 1072/2015 art. 2.2.4.6.25
- **Ubicación:** pdf.php línea 364 (inicio del bloque LEGISLACION NACIONAL)
- **Texto actual:** > `LEY 9 DE 1979 Codigo Sanitario Titulo III: Relativo a la salud ocupacional. [...]`
- **Problema:** El encabezado del marco legal nacional arranca con la Ley 9/1979 y nunca menciona el Decreto Único Reglamentario del Sector Trabajo (Decreto 1072/2015), que es la norma que actualmente obliga a las copropiedades (como empleadores del personal de administración, aseo y vigilancia directo) a implementar el Plan de Prevención, Preparación y Respuesta ante Emergencias. La omisión hace el documento inadmisible ante un requerimiento de MinTrabajo.
- **Texto propuesto:** Insertar como PRIMER párrafo del bloque LEGISLACION NACIONAL, antes de la línea 364:
  > `<strong>DECRETO 1072 DE 2015 — Artículo 2.2.4.6.25</strong> "Por medio del cual se expide el Decreto Único Reglamentario del Sector Trabajo". Obliga al empleador a implementar y mantener el Plan de Prevención, Preparación y Respuesta ante Emergencias, el cual debe incluir: (i) análisis de amenazas y vulnerabilidad con alcance sobre todos los trabajadores, contratistas, visitantes y demás personas que se encuentren en la copropiedad; (ii) recursos humanos, técnicos y financieros para su implementación; (iii) procedimientos escritos para prevenir y controlar las amenazas identificadas; (iv) informar, capacitar y entrenar a todos los trabajadores sobre el plan; (v) conformar, capacitar, entrenar y dotar la brigada de prevención, preparación y respuesta ante emergencias; (vi) realizar simulacros como mínimo una (1) vez al año con la participación de todos los trabajadores; (vii) inspeccionar con la periodicidad que sea definida los equipos de detección, alarma, control y atención de emergencias, así como los sistemas de señalización y las rutas de evacuación.`
- **Acción:** Edit línea 364 (insertar párrafo nuevo antes).

---

### CRÍTICO-2: Ausencia de la Resolución 0312 de 2019 (estándares mínimos SG-SST)
- **Norma:** Resolución 0312 de 2019 — MinTrabajo
- **Ubicación:** pdf.php línea 377 (último párrafo de LEGISLACION NACIONAL antes de cierre)
- **Texto actual:** > `DECRETO 1478 DE 2022 Actualizacion del Plan Nacional de Gestion del Riesgo de Desastres.`
- **Problema:** La Resolución 0312/2019 define los estándares mínimos del SG-SST aplicables a toda empresa/entidad con trabajadores, incluidas copropiedades con nómina. Exige expresamente: análisis de vulnerabilidad, plan con intervenciones derivadas del análisis, y brigada conformada y capacitada. Es norma viva y fiscalizable. No mencionarla es un vacío crítico.
- **Texto propuesto:** Insertar después de la línea 377:
  > `<strong>RESOLUCION 0312 DE 2019</strong> "Por la cual se definen los Estándares Mínimos del Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST". Exige a los empleadores, en función del número de trabajadores y la clase de riesgo, realizar el análisis de vulnerabilidad ante amenazas, formular y ejecutar el plan de prevención, preparación y respuesta ante emergencias con las intervenciones que se deriven de dicho análisis, y conformar, capacitar y dotar la brigada de prevención, preparación y respuesta ante emergencias. El cumplimiento de estos estándares es verificable por el Ministerio del Trabajo.`
- **Acción:** Edit línea 377 (agregar párrafo a continuación).

---

### CRÍTICO-3: Ausencia del Decreto 2157 de 2017 (PGRDEPP) y de la cita al art. 42 de la Ley 1523/2012
- **Norma:** Decreto 2157/2017 + Ley 1523/2012 art. 42
- **Ubicación:** pdf.php línea 169 (cita actual de Ley 1523) y línea 377 (fin del bloque)
- **Texto actual (línea 169):** > `La gestion del riesgo, de acuerdo con la Ley 1523 de 2012, es un proceso social orientado a la formulacion [...]`
- **Problema:** La cita actual es una definición genérica. No invoca el art. 42 que obliga a toda entidad pública y privada a formular un análisis específico de riesgo y planes de emergencia y contingencia. Además el Decreto 2157/2017 (reglamentario del art. 42) crea el PGRDEPP — Plan de Gestión del Riesgo de Desastres de Entidades Públicas y Privadas — con contenido obligatorio: análisis específico de riesgo, medidas de reducción de riesgo actual y futuro, y planes de emergencia y contingencia. El responsable es el representante legal (en copropiedad: el administrador/consejo). Omitirlo deja al cliente expuesto a sanción de la UNGRD.
- **Texto propuesto:** Insertar después de la línea 377, como bloque separado:
  > `<strong>LEY 1523 DE 2012 — Artículo 42</strong> "Por la cual se adopta la Política Nacional de Gestión del Riesgo de Desastres y se establece el Sistema Nacional de Gestión del Riesgo de Desastres". El artículo 42 obliga a todas las entidades públicas y privadas que desarrollen actividades consideradas de alto riesgo para la población — entre ellas las copropiedades en régimen de propiedad horizontal por la concentración de personas — a realizar un análisis específico de riesgo que considere los posibles efectos de eventos naturales sobre la infraestructura expuesta y aquellos que se deriven de los daños de la misma en su área de influencia, así como los que se deriven de su operación. Con base en este análisis diseñarán e implementarán las medidas de reducción del riesgo y el plan de emergencia y contingencia.`
  > `<strong>DECRETO 2157 DE 2017</strong> "Por medio del cual se adoptan directrices generales para la elaboración del Plan de Gestión del Riesgo de Desastres de las Entidades Públicas y Privadas (PGRDEPP) en el marco del artículo 42 de la Ley 1523 de 2012". Establece que el representante legal de la entidad — en el caso de la propiedad horizontal, el administrador designado por la asamblea — es el responsable de la formulación, implementación, socialización, actualización y seguimiento del PGRDEPP. Contenido obligatorio: (i) proceso de conocimiento del riesgo con análisis específico; (ii) proceso de reducción del riesgo con medidas de intervención correctiva y prospectiva; (iii) proceso de manejo del desastre con los planes de emergencia y contingencia.`
- **Acción:** Edit línea 377 (agregar dos párrafos a continuación).

---

### CRÍTICO-4: Cita de normatividad sismorresistente DEROGADA (Decreto 400/1984, Ley 400/1997, Decreto 33/1998)
- **Norma:** NSR-10 — Decreto 926 de 2010 (y sus reglamentarios)
- **Ubicación:** pdf.php línea 478
- **Texto actual:** > `[...] en consonancia con las exigencias del codigo colombiano de construcciones sismo resistentes, adoptado por el Decreto 400 con vigencia desde el ano de 1984 y actualizado por la Ley 400 de 1997 y el Decreto 33 de 1998.`
- **Problema:** El Decreto 400/1984 y el Decreto 33/1998 ya no son la norma vigente. El reglamento aplicable desde 2010 es la NSR-10, adoptada por el Decreto 926 de 2010 y sus modificaciones. Entregar a un cliente un plan que cita un reglamento sísmico de 1984 es un error técnico-legal grave.
- **Texto propuesto (reemplazo del segmento final de la línea 478):**
  > `[...] en consonancia con las exigencias del Reglamento Colombiano de Construcción Sismo Resistente NSR-10, adoptado mediante el Decreto 926 de 2010 y sus actos modificatorios, el cual constituye la norma técnica vigente en Colombia para el diseño y construcción sismo resistente de edificaciones.`
- **Acción:** Edit línea 478 (reemplazar la frase del Decreto 400/Ley 400/Decreto 33 por la redacción propuesta).

---

### CRÍTICO-5: No existe sección de conformación, capacitación y funciones de la Brigada de Emergencia
- **Norma:** Decreto 1072/2015 art. 2.2.4.6.25 num. 5 + Res. 0312/2019 + Res. 0256/2014
- **Ubicación:** pdf.php línea 522 (final del PON Código 7, antes del diagrama de actuación en línea 526)
- **Texto actual:** El documento solo menciona en la línea 173 que "se conformaran brigadas de emergencia con sus diferentes acciones y responsabilidades, el equipo de evacuacion, el equipo de primeros auxilios y el equipo control de incendios". No hay capítulo dedicado con estructura, número de brigadistas, perfil, funciones antes/durante/después, dotación ni plan de capacitación.
- **Problema:** Es uno de los requisitos expresos y verificables del Decreto 1072/2015 art. 2.2.4.6.25 y de la Resolución 0312/2019. La ausencia de este capítulo hace que el Plan no cumpla con los estándares mínimos.
- **Texto propuesto:** Ver sección "Secciones faltantes — Nueva sección: BRIGADA DE EMERGENCIA" más abajo.
- **Acción:** Edit línea 522 (insertar nuevo `<div class="section-title">` después).

---

### CRÍTICO-6: No existe sección de Simulacros y Capacitación (obligación anual expresa)
- **Norma:** Decreto 1072/2015 art. 2.2.4.6.25 num. 6
- **Ubicación:** pdf.php línea 522 (inmediatamente después de Brigada)
- **Texto actual:** Única mención en línea 171: "divulgado a todas las personas [...] e implementarlo por medio de simulacros periodicos". No hay frecuencia, tipos, registro ni procedimiento documentado.
- **Problema:** El Decreto 1072/2015 exige realizar simulacros "como mínimo una (1) vez al año" con participación de todos los trabajadores. El Plan debe contener la planificación anual, los escenarios, la metodología de evaluación y la forma de registro. Omitirlo constituye incumplimiento directo.
- **Texto propuesto:** Ver sección "Nueva sección: SIMULACROS Y CAPACITACIÓN" más abajo.
- **Acción:** Edit línea 522 (insertar nuevo `<div class="section-title">` después de Brigada).

---

## Hallazgos medios

### MEDIO-1: NTC 1700 citada pero sin los requisitos numéricos mínimos
- **Norma:** NTC 1700 (ICONTEC, 1982)
- **Ubicación:** pdf.php línea 389
- **Texto actual:** > `NTC-1700: Higiene y Seguridad. Medidas de Seguridad en Edificaciones. Medios de Evacuacion y Codigo NFPA 101. Establece los requerimientos que debe cumplir las edificaciones en cuanto a salidas de evacuacion, escaleras de emergencia, iluminacion de evacuacion, sistema de proteccion especiales, numero de personas maximo por unidad de area.`
- **Problema:** Cita la norma pero no traslada al cliente los requisitos concretos que debe verificar en su copropiedad: ancho mínimo de puertas de salida (≥70 cm), número mínimo de salidas por piso (mínimo 2), requisito de barra antipánico en áreas con más de 100 ocupantes por puerta. Estos son los valores auditables por Bomberos.
- **Texto propuesto (reemplazo de la línea 389):**
  > `<strong>NTC-1700:</strong> Higiene y Seguridad. Medidas de Seguridad en Edificaciones. Medios de Evacuación y su correlato con el Código NFPA 101 (Life Safety Code). Establece los requerimientos que deben cumplir las edificaciones en cuanto a salidas de evacuación, escaleras de emergencia, iluminación de evacuación y sistemas de protección especiales. Requisitos mínimos aplicables a propiedad horizontal: (i) ancho libre mínimo de las puertas de salida de setenta centímetros (70 cm); (ii) mínimo dos (2) salidas independientes por piso; (iii) instalación de barra antipánico (dispositivo de apertura de emergencia) en aquellas puertas que sirvan a áreas con carga ocupacional superior a cien (100) personas; (iv) demarcación y señalización permanente de la ruta de evacuación; (v) iluminación de emergencia con autonomía suficiente para garantizar la evacuación segura.`
- **Acción:** Edit línea 389.

---

### MEDIO-2: Resolución 0256/2014 sobre Brigadas contra Incendio no citada
- **Norma:** Resolución 0256 de 2014
- **Ubicación:** pdf.php línea 371 (cerca de la mención a Ley 322/1996 de bomberos)
- **Texto actual:** > `LEY 322 DE 1996 SISTEMA NACIONAL DE BOMBEROS. [...]`
- **Problema:** La Resolución 0256/2014 reglamenta específicamente la conformación, capacitación, certificación y dotación de las brigadas contra incendio. Es la norma técnica de referencia para la brigada que el Plan exige conformar. Omitirla debilita el marco legal del capítulo de brigada.
- **Texto propuesto:** Insertar después de la línea 371:
  > `<strong>RESOLUCION 0256 DE 2014</strong> (Dirección Nacional de Bomberos de Colombia) "Por medio de la cual se reglamenta la conformación, capacitación y entrenamiento para las brigadas contraincendios de los sectores energético, industrial, petrolero, minero, portuario, comercial y similar en Colombia". Establece los lineamientos técnicos para la conformación de brigadas, contenidos mínimos de capacitación, niveles de formación (básico, intermedio, avanzado), dotación y entrenamiento periódico aplicables como referente a las brigadas de copropiedades en régimen de propiedad horizontal.`
- **Acción:** Edit línea 371 (agregar párrafo a continuación).

---

### MEDIO-3: El bloque LEGISLACION DISTRITAL (Bogotá) se muestra a clientes de cualquier ciudad
- **Norma:** aplicación territorial
- **Ubicación:** pdf.php líneas 379-385
- **Texto actual:** > `LEGISLACION DISTRITAL` con Resolución 1428/2002, Decreto 332/04, Decreto 423/06, Resolución 375/06, Resolución 137/07, Decreto 633/07 — todos distritales de Bogotá.
- **Problema:** El documento se entrega a conjuntos residenciales en múltiples ciudades (la variable `$ciudad` en línea 4 evidencia soporte multi-ciudad). Mostrar legislación distrital de Bogotá a un cliente en Medellín, Cali o Barranquilla es incorrecto y confuso.
- **Texto propuesto:** Envolver el bloque entre líneas 379 y 385 en un condicional PHP: solo mostrar LEGISLACION DISTRITAL cuando `strtolower($ciudad) === 'bogota'`. Alternativamente, renombrar la sección a "LEGISLACION DISTRITAL (aplica solo para Bogotá D.C.)" si el cliente está en Bogotá, y ocultarla en otro caso.
- **Acción:** Edit línea 379 (envolver con `<?php if (strtolower($ciudad ?? '') === 'bogota'): ?>` y cerrar en línea 385 con `<?php endif; ?>`).

---

### MEDIO-4: Cita de Decreto Ley 919/89 sin aclarar que fue derogado por la Ley 1523/2012
- **Norma:** Ley 1523/2012 (deroga Decreto Ley 919/89)
- **Ubicación:** pdf.php línea 369
- **Texto actual:** > `DECRETO LEY 919/89 "Por el cual se organiza el Sistema Nacional para la Prevencion y Atencion de Desastres". [...]`
- **Problema:** El Decreto Ley 919/1989 fue derogado expresamente por la Ley 1523/2012. Citarlo como marco legal vigente induce a error.
- **Texto propuesto:** Eliminar la línea 369 completa, o bien añadir al final: `(Nota: derogado por la Ley 1523 de 2012).`
- **Acción:** Edit línea 369.

---

### MEDIO-5: Ley 46/88 y CONPES 3146/2001 históricos — mezclados con normativa vigente
- **Norma:** depuración de legislación
- **Ubicación:** pdf.php líneas 367-368
- **Texto actual:** > `CONPES 3146 de 2001 [...]` y `LEY 46/88 "Por la cual se crea y organiza el Sistema Nacional para la Prevencion y Atencion de Desastres".`
- **Problema:** La Ley 46/88 fue reemplazada por el DL 919/89 (también derogado) y por la Ley 1523/2012. El CONPES 3146 es un documento de política histórico. Ambas referencias están obsoletas.
- **Texto propuesto:** Eliminar líneas 367 y 368, o moverlas a una sección explícita "Antecedentes históricos del Sistema Nacional de Gestión del Riesgo".
- **Acción:** Edit líneas 367-368.

---

## Hallazgos menores

### MENOR-1: Ortografía — "por tanto" y tildes
- **Ubicación:** pdf.php líneas 161-173 (introducción y justificación)
- **Problema:** El documento está escrito sin tildes (problema de codificación histórico). Es estético pero afecta la calidad percibida del entregable. No se propone corrección masiva aquí; se deja como observación.

### MENOR-2: Decreto 1347/2021 y Decreto 4272/2021 citados sin contexto de propiedad horizontal
- **Ubicación:** pdf.php líneas 374-375
- **Texto actual:** > `DECRETO 1347 DE 2021 Programa de Prevencion de Accidentes Mayores - PPAM.` y `DECRETO 4272 DE 2021 Requisitos minimos de seguridad para trabajo en alturas.`
- **Problema:** El PPAM aplica a instalaciones con sustancias peligrosas (no aplica a copropiedad residencial típica). El Decreto 4272/2021 sobre trabajo en alturas es correcto pero convendría aclarar que aplica al personal de mantenimiento contratado.
- **Acción:** Edit líneas 374-375 (aclarar aplicabilidad o remover PPAM).

### MENOR-3: NTC-5254 desactualizada
- **Ubicación:** pdf.php línea 388
- **Texto actual:** > `NTC-5254: Gestion de Riesgo.`
- **Problema:** La NTC 5254 fue reemplazada por la familia ISO 31000 (NTC-ISO 31000:2011, luego actualizada). Se recomienda citar NTC-ISO 31000 en su lugar.
- **Acción:** Edit línea 388.

### MENOR-4: Título del documento inconsistente
- **Ubicación:** pdf.php línea 133 (`PLAN DE EMERGENCIA`) vs línea 154 (`PLAN DE EMERGENCIA Y CONTINGENCIA`) vs línea 186 (`Plan de Preparacion y Respuesta ante Situaciones de Emergencia (PPRSE)`)
- **Problema:** Tres denominaciones distintas para el mismo documento. Para alinear con el Decreto 1072/2015, el nombre técnicamente correcto es "Plan de Prevención, Preparación y Respuesta ante Emergencias". Unificar.
- **Acción:** Edit líneas 133, 154, 186 con el nombre unificado.

---

## Secciones faltantes que deben agregarse

### Nueva sección: MARCO NORMATIVO ACTUALIZADO
- **Insertar:** reemplazar integralmente o anteponer al bloque actual que empieza en pdf.php línea 362 (`<div class="section-title">LEGISLACION</div>`)
- **Texto completo:**
  > `<div class="section-title">MARCO NORMATIVO APLICABLE</div>`
  > `<p class="content-text">El presente Plan de Prevención, Preparación y Respuesta ante Emergencias se formula en cumplimiento del siguiente marco normativo vigente en la República de Colombia:</p>`
  > `<p class="content-text"><strong>DECRETO 1072 DE 2015 — Artículo 2.2.4.6.25.</strong> Decreto Único Reglamentario del Sector Trabajo. Establece la obligación del empleador de implementar y mantener un Plan de Prevención, Preparación y Respuesta ante Emergencias con análisis de amenazas, recursos, procedimientos escritos, capacitación, brigada conformada y capacitada, simulacros mínimo una vez al año e inspección periódica de equipos de atención.</p>`
  > `<p class="content-text"><strong>RESOLUCION 0312 DE 2019.</strong> Estándares Mínimos del Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST. Exige análisis de vulnerabilidad, plan con intervenciones derivadas del análisis, y brigada de prevención, preparación y respuesta ante emergencias conformada y capacitada.</p>`
  > `<p class="content-text"><strong>LEY 1523 DE 2012 — Artículo 42.</strong> Política Nacional de Gestión del Riesgo de Desastres. Obliga a toda entidad pública y privada a realizar análisis específico de riesgo y a formular los planes de emergencia y contingencia derivados del mismo.</p>`
  > `<p class="content-text"><strong>DECRETO 2157 DE 2017.</strong> Reglamenta el artículo 42 de la Ley 1523 de 2012 y crea el Plan de Gestión del Riesgo de Desastres de Entidades Públicas y Privadas — PGRDEPP. Contenido obligatorio: proceso de conocimiento del riesgo, proceso de reducción del riesgo y proceso de manejo del desastre. La responsabilidad de su formulación e implementación recae en el representante legal de la entidad.</p>`
  > `<p class="content-text"><strong>LEY 675 DE 2001.</strong> Régimen de Propiedad Horizontal. Establece la personería jurídica de los edificios y conjuntos de uso residencial, por lo cual les resulta aplicable la legislación colombiana en materia de Seguridad y Salud en el Trabajo.</p>`
  > `<p class="content-text"><strong>NTC 1700 (ICONTEC).</strong> Medios de evacuación en edificaciones. Requisitos mínimos: ancho libre de puertas ≥ 70 cm, mínimo dos salidas por piso, barra antipánico en puertas que sirvan a áreas con más de 100 ocupantes.</p>`
  > `<p class="content-text"><strong>NSR-10 — DECRETO 926 DE 2010.</strong> Reglamento Colombiano de Construcción Sismo Resistente. Norma técnica vigente para el diseño y construcción sismo resistente de edificaciones en el territorio nacional.</p>`
  > `<p class="content-text"><strong>RESOLUCION 0256 DE 2014.</strong> Dirección Nacional de Bomberos de Colombia. Reglamenta la conformación, capacitación y entrenamiento de brigadas contraincendios.</p>`

- **Acción:** Insertar como primer bloque después de línea 361, antes del subtítulo LEGISLACION NACIONAL.

---

### Nueva sección: CONFORMACIÓN DE BRIGADA DE EMERGENCIA
- **Insertar:** después de línea 522 (al cerrar el PON Código 7), antes del DIAGRAMA DE ACTUACION en línea 526.
- **Texto completo:**
  > `<div class="section-title">CONFORMACION DE LA BRIGADA DE EMERGENCIA</div>`
  > `<p class="content-text">En cumplimiento del numeral 5 del artículo 2.2.4.6.25 del Decreto 1072 de 2015 y de la Resolución 0312 de 2019, <?= $nombreCliente ?> conformará una Brigada de Prevención, Preparación y Respuesta ante Emergencias, integrada por personal voluntario de la administración, vigilancia, aseo, mantenimiento y residentes que manifiesten disposición de servicio.</p>`
  > `<div class="section-subtitle">ESTRUCTURA DE LA BRIGADA</div>`
  > `<p class="content-text">La brigada se organizará en tres grupos funcionales: <strong>(i) Grupo de Evacuación y Rescate</strong>: responsable de guiar a residentes y visitantes por las rutas de evacuación hasta los puntos de encuentro. <strong>(ii) Grupo de Primeros Auxilios</strong>: responsable de brindar atención inicial a lesionados hasta la llegada de los organismos de socorro. <strong>(iii) Grupo de Control de Incendios</strong>: responsable del manejo de extintores portátiles y gabinetes contra incendio en conatos y del apoyo al Cuerpo de Bomberos.</p>`
  > `<div class="section-subtitle">PERFIL DEL BRIGADISTA</div>`
  > `<p class="content-text">Mayor de edad, en condiciones físicas y mentales aptas para la actividad, con disponibilidad para recibir capacitación y entrenamiento periódico, disposición de servicio y liderazgo.</p>`
  > `<div class="section-subtitle">FUNCIONES ANTES, DURANTE Y DESPUES</div>`
  > `<p class="content-text"><strong>Antes:</strong> participar en las capacitaciones, inspeccionar periódicamente equipos de emergencia, divulgar el Plan, participar en simulacros.<br><strong>Durante:</strong> activar el sistema de alarma, ejecutar las acciones propias de su grupo funcional, coordinar con organismos externos de socorro, proteger la vida como prioridad absoluta.<br><strong>Después:</strong> verificar que todos los ocupantes hayan evacuado, rendir informe de la novedad, participar en la evaluación del evento y en la actualización del Plan.</p>`
  > `<div class="section-subtitle">CAPACITACION Y ENTRENAMIENTO</div>`
  > `<p class="content-text">La brigada recibirá capacitación mínima anual en: nociones básicas de gestión del riesgo, evacuación y rescate básico, primeros auxilios, uso y manejo de extintores portátiles, uso de gabinetes contra incendio (cuando aplique), comunicaciones en emergencia y funcionamiento del Sistema Comando de Incidentes. Los contenidos, niveles y periodicidad se alinean con lo establecido en la Resolución 0256 de 2014 de la Dirección Nacional de Bomberos de Colombia.</p>`
  > `<div class="section-subtitle">DOTACION</div>`
  > `<p class="content-text">La administración del conjunto dotará a la brigada con los elementos mínimos de protección personal e identificación: casco tipo brigadista, chaleco reflectivo de identificación, guantes, linterna, pito y botiquín portátil de brigadista.</p>`

- **Acción:** Insertar `<div class="section-title">` después de línea 522.

---

### Nueva sección: SIMULACROS Y CAPACITACIÓN
- **Insertar:** después de la sección nueva de Brigada (antes del DIAGRAMA DE ACTUACION en línea 526).
- **Texto completo:**
  > `<div class="section-title">SIMULACROS Y CAPACITACION</div>`
  > `<p class="content-text">En cumplimiento del numeral 6 del artículo 2.2.4.6.25 del Decreto 1072 de 2015, <?= $nombreCliente ?> realizará simulacros de emergencia como mínimo una (1) vez al año, con la participación de todos los trabajadores, brigadistas, administración y, en lo posible, los residentes del conjunto.</p>`
  > `<div class="section-subtitle">TIPOS DE SIMULACRO</div>`
  > `<p class="content-text"><strong>Simulacro de escritorio (tabletop):</strong> ejercicio teórico en el cual la brigada y la administración analizan la respuesta a un escenario hipotético sin desplazamiento físico.<br><strong>Simulacro parcial:</strong> ejercicio en terreno que evalúa uno o varios procedimientos específicos (evacuación de una torre, uso de extintores, activación de la alarma).<br><strong>Simulacro general:</strong> ejercicio en terreno que activa todos los procedimientos del Plan, incluida la evacuación total hacia los puntos de encuentro.</p>`
  > `<div class="section-subtitle">PROGRAMACION ANUAL</div>`
  > `<p class="content-text">La administración programará al inicio de cada vigencia el cronograma de simulacros del año, incluyendo como mínimo un simulacro general anual. Se recomienda alinear la programación con el Simulacro Nacional de Respuesta a Emergencias convocado por la UNGRD.</p>`
  > `<div class="section-subtitle">EVALUACION Y REGISTRO</div>`
  > `<p class="content-text">Cada simulacro será evaluado por un observador externo o por un brigadista no participante. Se levantará acta con: fecha y hora, escenario, número de participantes, tiempo total de evacuación, hallazgos, desviaciones respecto del procedimiento y plan de mejora. Los registros reposarán en el archivo del SG-SST de la copropiedad y estarán disponibles para verificación por parte del Ministerio del Trabajo.</p>`
  > `<div class="section-subtitle">DIVULGACION Y CAPACITACION A RESIDENTES</div>`
  > `<p class="content-text">La administración divulgará el presente Plan a todos los residentes mediante asambleas, cartelera, circulares y medios electrónicos. Los nuevos residentes recibirán inducción sobre rutas de evacuación, puntos de encuentro y códigos de alarma al momento de su vinculación al conjunto.</p>`

- **Acción:** Insertar `<div class="section-title">` después de la sección de Brigada, antes de línea 526.

---

### Nueva sección: PGRDEPP — Responsable y Seguimiento
- **Insertar:** al inicio de la sección ALCANCE (línea 185) o como sección independiente después.
- **Texto completo:**
  > `<div class="section-title">RESPONSABLE Y SEGUIMIENTO DEL PLAN (PGRDEPP)</div>`
  > `<p class="content-text">En los términos del Decreto 2157 de 2017, reglamentario del artículo 42 de la Ley 1523 de 2012, la formulación, implementación, socialización, actualización y seguimiento del presente Plan de Gestión del Riesgo de Desastres de Entidad Privada (PGRDEPP) es responsabilidad del representante legal de la copropiedad, rol que en <?= $nombreCliente ?> es ejercido por el Administrador debidamente designado por la Asamblea General de Propietarios o el Consejo de Administración.</p>`
  > `<p class="content-text">El Plan será revisado y actualizado como mínimo una vez al año o cuando se produzcan cambios significativos en la infraestructura, la ocupación o las amenazas identificadas. El resultado de cada revisión quedará documentado y será socializado con la brigada, el personal operativo y los residentes.</p>`

- **Acción:** Insertar después de línea 186.

---

## Citas normativas obsoletas o incorrectas

| Línea | Texto actual | Problema | Acción |
|---|---|---|---|
| 367 | CONPES 3146 de 2001 | Documento histórico de política, no norma vinculante | Eliminar o mover a antecedentes |
| 368 | LEY 46/88 | Derogada por DL 919/89 y por Ley 1523/2012 | Eliminar o marcar como antecedente |
| 369 | DECRETO LEY 919/89 | Derogado por Ley 1523/2012 | Eliminar o marcar como antecedente |
| 374 | DECRETO 1347 DE 2021 (PPAM) | No aplica a copropiedad residencial (aplica a sustancias peligrosas) | Eliminar |
| 478 | Decreto 400/1984, Ley 400/1997, Decreto 33/1998 | Reemplazados por NSR-10 (Decreto 926/2010) | Reemplazar por NSR-10 |
| 388 | NTC-5254 | Reemplazada por NTC-ISO 31000 | Actualizar cita |
| 379-385 | LEGISLACION DISTRITAL (Bogotá) | Se muestra a clientes de cualquier ciudad | Condicionar por `$ciudad === 'bogota'` |

**No citar:** Decreto 768 de 2025 (no aplica a emergencias; reglamenta convivencia, privacidad y fachadas).

---

## Checklist de migración al texto definitivo

- [ ] CRÍTICO-1: Insertar Decreto 1072/2015 art. 2.2.4.6.25 antes de línea 364
- [ ] CRÍTICO-2: Insertar Resolución 0312/2019 después de línea 377
- [ ] CRÍTICO-3: Insertar Ley 1523/2012 art. 42 + Decreto 2157/2017 después de línea 377
- [ ] CRÍTICO-4: Reemplazar texto sismo resistente en línea 478 por NSR-10
- [ ] CRÍTICO-5: Agregar sección BRIGADA DE EMERGENCIA después de línea 522
- [ ] CRÍTICO-6: Agregar sección SIMULACROS Y CAPACITACION después de Brigada
- [ ] MEDIO-1: Reemplazar línea 389 con requisitos numéricos NTC 1700
- [ ] MEDIO-2: Insertar Resolución 0256/2014 después de línea 371
- [ ] MEDIO-3: Envolver líneas 379-385 en condicional `$ciudad === 'bogota'`
- [ ] MEDIO-4: Eliminar o marcar como derogado DL 919/89 (línea 369)
- [ ] MEDIO-5: Eliminar o mover CONPES 3146 y Ley 46/88 (líneas 367-368)
- [ ] MENOR-2: Aclarar o remover Decreto 1347/2021 PPAM (línea 374)
- [ ] MENOR-3: Actualizar NTC-5254 a NTC-ISO 31000 (línea 388)
- [ ] MENOR-4: Unificar nombre del plan en líneas 133, 154, 186
- [ ] Agregar sección MARCO NORMATIVO APLICABLE al inicio del bloque legal (línea 362)
- [ ] Agregar sección PGRDEPP — Responsable y Seguimiento después de línea 186
- [ ] Verificar render del PDF con los nuevos bloques y ajustar saltos de página
- [ ] Revisar con el cliente piloto y obtener visto bueno antes de desplegar a producción
