# Plan Completo de Vistas SQL para Otto

> Generado: 2026-03-15
> Base de datos: `propiedad_horizontal`
> Cobertura: 100% de tablas analizadas (73 tablas totales)

---

## Tablas con vistas a crear (53 total)

---

### v_evaluacion_inicial_sst

- Tabla base: `evaluacion_inicial_sst`

- JOINs:
  - `JOIN tbl_clientes c ON e.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_historial_resumen_estandares

- Tabla base: `historial_resumen_estandares`

- JOINs:
  - `JOIN tbl_clientes c ON h.id_cliente = c.id_cliente` → traer `c.nombre_cliente` (como `nombre_cliente_fk` para no colisionar con la columna desnormalizada)

- Columnas resueltas: `id_cliente` → `nombre_cliente_fk` (la tabla ya tiene `nombre_cliente` desnormalizado; el JOIN permite detectar inconsistencias)

---

### v_historial_resumen_plan_trabajo

- Tabla base: `historial_resumen_plan_trabajo`

- JOINs:
  - `JOIN tbl_clientes c ON h.id_cliente = c.id_cliente` → traer `c.nombre_cliente` (como `nombre_cliente_fk`)

- Columnas resueltas: `id_cliente` → `nombre_cliente_fk` (igual al anterior, tabla ya desnormalizada)

---

### v_tbl_acta_visita

- Tabla base: `tbl_acta_visita`

- JOINs:
  - `JOIN tbl_clientes c ON a.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN tbl_consultor con ON a.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_acta_visita_fotos

- Tabla base: `tbl_acta_visita_fotos`

- JOINs:
  - `JOIN tbl_acta_visita av ON f.id_acta_visita = av.id` → traer `av.fecha_visita`, `av.id_cliente`
  - `JOIN tbl_clientes c ON av.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_acta_visita` → `fecha_visita` + `nombre_cliente`

---

### v_tbl_acta_visita_integrantes

- Tabla base: `tbl_acta_visita_integrantes`

- JOINs:
  - `JOIN tbl_acta_visita av ON i.id_acta_visita = av.id` → traer `av.fecha_visita`, `av.id_cliente`
  - `JOIN tbl_clientes c ON av.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_acta_visita` → `fecha_visita` + `nombre_cliente`

---

### v_tbl_acta_visita_pta

- Tabla base: `tbl_acta_visita_pta`

- JOINs:
  - `JOIN tbl_acta_visita av ON p.id_acta_visita = av.id` → traer `av.fecha_visita`, `av.id_cliente`
  - `JOIN tbl_clientes c ON av.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN tbl_pta_cliente pta ON p.id_ptacliente = pta.id_ptacliente` → traer `pta.actividad_plandetrabajo`, `pta.estado_actividad`

- Columnas resueltas: `id_acta_visita` → `fecha_visita` + `nombre_cliente`, `id_ptacliente` → `actividad_plandetrabajo`

---

### v_tbl_acta_visita_temas

- Tabla base: `tbl_acta_visita_temas`

- JOINs:
  - `JOIN tbl_acta_visita av ON t.id_acta_visita = av.id` → traer `av.fecha_visita`, `av.id_cliente`
  - `JOIN tbl_clientes c ON av.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_acta_visita` → `fecha_visita` + `nombre_cliente`

---

### v_tbl_agendamientos

- Tabla base: `tbl_agendamientos`

- JOINs:
  - `LEFT JOIN tbl_clientes c ON a.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON a.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_asistencia_induccion

- Tabla base: `tbl_asistencia_induccion`

- JOINs:
  - `JOIN tbl_clientes c ON a.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON a.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_asistencia_induccion_asistente

- Tabla base: `tbl_asistencia_induccion_asistente`

- JOINs:
  - `JOIN tbl_asistencia_induccion asi ON a.id_asistencia = asi.id` → traer `asi.fecha_sesion`, `asi.tema`, `asi.id_cliente`
  - `JOIN tbl_clientes c ON asi.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_asistencia` → `fecha_sesion` + `tema` + `nombre_cliente`

---

### v_tbl_auditoria_zona_residuos

- Tabla base: `tbl_auditoria_zona_residuos`

- JOINs:
  - `JOIN tbl_clientes c ON a.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON a.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_carta_vigia

- Tabla base: `tbl_carta_vigia`

- JOINs:
  - `JOIN tbl_clientes c ON cv.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON cv.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_chat_log

- Tabla base: `tbl_chat_log`

- JOINs:
  - `JOIN tbl_usuarios u ON cl.id_usuario = u.id_usuario` → traer `u.nombre_completo`, `u.email`, `u.tipo_usuario`

- Columnas resueltas: `id_usuario` → `nombre_completo`, `email`, `tipo_usuario`

---

### v_tbl_ciclos_visita

- Tabla base: `tbl_ciclos_visita`

- JOINs:
  - `JOIN tbl_clientes c ON cv.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN tbl_consultor con ON cv.id_consultor = con.id_consultor` → traer `con.nombre_consultor`
  - `LEFT JOIN tbl_acta_visita av ON cv.id_acta = av.id` → traer `av.fecha_visita` (JOIN opcional: `id_acta` es int pero tiene nota de legacy varchar)

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`, `id_acta` → `fecha_visita`

---

### v_tbl_contratos

- Tabla base: `tbl_contratos`

- JOINs:
  - `JOIN tbl_clientes c ON ct.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_tbl_cronog_capacitacion

- Tabla base: `tbl_cronog_capacitacion`

- JOINs:
  - `JOIN tbl_clientes c ON cc.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN capacitaciones_sst cs ON cc.id_capacitacion = cs.id_capacitacion` → traer `cs.capacitacion` AS `nombre_capacitacion_catalogo`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_capacitacion` → `nombre_capacitacion_catalogo`

- Nota: la tabla ya tiene `nombre_capacitacion` desnormalizado; el JOIN con `capacitaciones_sst` es adicional para referencia al catálogo maestro.

---

### v_tbl_cronog_capacitacion_old

- Tabla base: `tbl_cronog_capacitacion_old`

- JOINs:
  - `JOIN tbl_clientes c ON cc.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN capacitaciones_sst cs ON cc.id_capacitacion = cs.id_capacitacion` → traer `cs.capacitacion` AS `nombre_capacitacion`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_capacitacion` → `nombre_capacitacion`

- Nota: tabla legacy. Vista útil para consultas históricas de Otto.

---

### v_tbl_dotacion_aseadora

- Tabla base: `tbl_dotacion_aseadora`

- JOINs:
  - `JOIN tbl_clientes c ON d.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON d.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_dotacion_todero

- Tabla base: `tbl_dotacion_todero`

- JOINs:
  - `JOIN tbl_clientes c ON d.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON d.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_dotacion_vigilante

- Tabla base: `tbl_dotacion_vigilante`

- JOINs:
  - `JOIN tbl_clientes c ON d.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON d.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_elemento_botiquin

- Tabla base: `tbl_elemento_botiquin`

- JOINs:
  - `JOIN tbl_inspeccion_botiquin ib ON e.id_inspeccion = ib.id` → traer `ib.fecha_inspeccion`, `ib.id_cliente`
  - `JOIN tbl_clientes c ON ib.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_inspeccion` → `fecha_inspeccion` + `nombre_cliente`

---

### v_tbl_evaluacion_induccion

- Tabla base: `tbl_evaluacion_induccion`

- JOINs:
  - `JOIN tbl_clientes c ON e.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_asistencia_induccion asi ON e.id_asistencia_induccion = asi.id` → traer `asi.fecha_sesion`, `asi.tema`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_asistencia_induccion` → `fecha_sesion` + `tema`

---

### v_tbl_evaluacion_induccion_respuesta

- Tabla base: `tbl_evaluacion_induccion_respuesta`

- JOINs:
  - `JOIN tbl_evaluacion_induccion ei ON r.id_evaluacion = ei.id` → traer `ei.titulo`, `ei.id_cliente`
  - `JOIN tbl_clientes c ON ei.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_evaluacion` → `titulo` + `nombre_cliente`

- Nota: actualmente 0 registros; vista se crea para cuando haya respuestas.

---

### v_tbl_evaluacion_simulacro

- Tabla base: `tbl_evaluacion_simulacro`

- JOINs:
  - `JOIN tbl_clientes c ON es.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_tbl_extintor_detalle

- Tabla base: `tbl_extintor_detalle`

- JOINs:
  - `JOIN tbl_inspeccion_extintores ie ON e.id_inspeccion = ie.id` → traer `ie.fecha_inspeccion`, `ie.id_cliente`
  - `JOIN tbl_clientes c ON ie.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_inspeccion` → `fecha_inspeccion` + `nombre_cliente`

---

### v_tbl_gabinete_detalle

- Tabla base: `tbl_gabinete_detalle`

- JOINs:
  - `JOIN tbl_inspeccion_gabinetes ig ON g.id_inspeccion = ig.id` → traer `ig.fecha_inspeccion`, `ig.id_cliente`
  - `JOIN tbl_clientes c ON ig.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_inspeccion` → `fecha_inspeccion` + `nombre_cliente`

---

### v_tbl_hallazgo_locativo

- Tabla base: `tbl_hallazgo_locativo`

- JOINs:
  - `JOIN tbl_inspeccion_locativa il ON h.id_inspeccion = il.id` → traer `il.fecha_inspeccion`, `il.id_cliente`
  - `JOIN tbl_clientes c ON il.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_inspeccion` → `fecha_inspeccion` + `nombre_cliente`

---

### v_tbl_hv_brigadista

- Tabla base: `tbl_hv_brigadista`

- JOINs:
  - `JOIN tbl_clientes c ON h.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_tbl_informe_avances

- Tabla base: `tbl_informe_avances`

- JOINs:
  - `JOIN tbl_clientes c ON ia.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON ia.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_inspeccion_botiquin

- Tabla base: `tbl_inspeccion_botiquin`

- JOINs:
  - `JOIN tbl_clientes c ON i.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_inspeccion_comunicaciones

- Tabla base: `tbl_inspeccion_comunicaciones`

- JOINs:
  - `JOIN tbl_clientes c ON i.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_inspeccion_extintores

- Tabla base: `tbl_inspeccion_extintores`

- JOINs:
  - `JOIN tbl_clientes c ON i.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_inspeccion_gabinetes

- Tabla base: `tbl_inspeccion_gabinetes`

- JOINs:
  - `JOIN tbl_clientes c ON i.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_inspeccion_locativa

- Tabla base: `tbl_inspeccion_locativa`

- JOINs:
  - `JOIN tbl_clientes c ON i.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_inspeccion_recursos_seguridad

- Tabla base: `tbl_inspeccion_recursos_seguridad`

- JOINs:
  - `JOIN tbl_clientes c ON i.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_inspeccion_senalizacion

- Tabla base: `tbl_inspeccion_senalizacion`

- JOINs:
  - `JOIN tbl_clientes c ON i.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_item_senalizacion

- Tabla base: `tbl_item_senalizacion`

- JOINs:
  - `JOIN tbl_inspeccion_senalizacion is_ ON it.id_inspeccion = is_.id` → traer `is_.fecha_inspeccion`, `is_.id_cliente`
  - `JOIN tbl_clientes c ON is_.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_inspeccion` → `fecha_inspeccion` + `nombre_cliente`

---

### v_tbl_kpi_agua_potable

- Tabla base: `tbl_kpi_agua_potable`

- JOINs:
  - `JOIN tbl_clientes c ON k.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_kpi_limpieza

- Tabla base: `tbl_kpi_limpieza`

- JOINs:
  - `JOIN tbl_clientes c ON k.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_kpi_plagas

- Tabla base: `tbl_kpi_plagas`

- JOINs:
  - `JOIN tbl_clientes c ON k.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_kpi_residuos

- Tabla base: `tbl_kpi_residuos`

- JOINs:
  - `JOIN tbl_clientes c ON k.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_lookerstudio

- Tabla base: `tbl_lookerstudio`

- JOINs:
  - `JOIN tbl_clientes c ON l.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_tbl_matrices

- Tabla base: `tbl_matrices`

- JOINs:
  - `JOIN tbl_clientes c ON m.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_tbl_matriz_vulnerabilidad

- Tabla base: `tbl_matriz_vulnerabilidad`

- JOINs:
  - `JOIN tbl_clientes c ON m.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON m.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_pendientes

- Tabla base: `tbl_pendientes`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_acta_visita av ON p.id_acta_visita = av.id` → traer `av.fecha_visita`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_acta_visita` → `fecha_visita`

- ADVERTENCIA: `id_acta` (varchar, formato 'AV-{id}') NO se usa en JOIN. Solo usar `id_acta_visita` (int) para el JOIN con `tbl_acta_visita`.

---

### v_tbl_plan_emergencia

- Tabla base: `tbl_plan_emergencia`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_plan_saneamiento

- Tabla base: `tbl_plan_saneamiento`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_preparacion_simulacro

- Tabla base: `tbl_preparacion_simulacro`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_presupuesto_detalle

- Tabla base: `tbl_presupuesto_detalle`

- JOINs:
  - `JOIN tbl_presupuesto_items pi ON d.id_item = pi.id_item` → traer `pi.actividad` AS `nombre_item`, `pi.id_presupuesto`, `pi.id_categoria`
  - `JOIN tbl_presupuesto_sst ps ON pi.id_presupuesto = ps.id_presupuesto` → traer `ps.anio`, `ps.id_cliente`
  - `JOIN tbl_clientes c ON ps.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN tbl_presupuesto_categorias pc ON pi.id_categoria = pc.id_categoria` → traer `pc.nombre` AS `nombre_categoria`

- Columnas resueltas: `id_item` → `nombre_item` + `nombre_categoria` + `anio` + `nombre_cliente`

---

### v_tbl_presupuesto_items

- Tabla base: `tbl_presupuesto_items`

- JOINs:
  - `JOIN tbl_presupuesto_sst ps ON pi.id_presupuesto = ps.id_presupuesto` → traer `ps.anio`, `ps.id_cliente`
  - `JOIN tbl_clientes c ON ps.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN tbl_presupuesto_categorias pc ON pi.id_categoria = pc.id_categoria` → traer `pc.nombre` AS `nombre_categoria`

- Columnas resueltas: `id_presupuesto` → `anio` + `nombre_cliente`, `id_categoria` → `nombre_categoria`

---

### v_tbl_presupuesto_sst

- Tabla base: `tbl_presupuesto_sst`

- JOINs:
  - `JOIN tbl_clientes c ON ps.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_tbl_probabilidad_peligros

- Tabla base: `tbl_probabilidad_peligros`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_programa_agua_potable

- Tabla base: `tbl_programa_agua_potable`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_programa_limpieza

- Tabla base: `tbl_programa_limpieza`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_programa_plagas

- Tabla base: `tbl_programa_plagas`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_programa_residuos

- Tabla base: `tbl_programa_residuos`

- JOINs:
  - `JOIN tbl_clientes c ON p.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor` → traer `con.nombre_consultor`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`

---

### v_tbl_pta_cliente

- Tabla base: `tbl_pta_cliente`

- JOINs:
  - `JOIN tbl_clientes c ON pt.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

### v_tbl_pta_cliente_audit

- Tabla base: `tbl_pta_cliente_audit`

- JOINs:
  - `LEFT JOIN tbl_clientes c ON a.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_pta_cliente pta ON a.id_ptacliente = pta.id_ptacliente` → traer `pta.actividad_plandetrabajo`
  - `LEFT JOIN tbl_usuarios u ON a.id_usuario = u.id_usuario` → traer `u.nombre_completo` AS `nombre_usuario_actual`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_ptacliente` → `actividad_plandetrabajo`, `id_usuario` → `nombre_usuario_actual`

- Nota: la tabla ya tiene `nombre_usuario` y `email_usuario` desnormalizados.

---

### v_tbl_pta_cliente_old

- Tabla base: `tbl_pta_cliente_old`

- JOINs:
  - `JOIN tbl_clientes c ON pt.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

- Nota: tabla legacy. Vista útil para consultas históricas.

---

### v_tbl_pta_transiciones

- Tabla base: `tbl_pta_transiciones`

- JOINs:
  - `JOIN tbl_clientes c ON pt.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN tbl_pta_cliente pta ON pt.id_ptacliente = pta.id_ptacliente` → traer `pta.actividad_plandetrabajo`
  - `LEFT JOIN tbl_usuarios u ON pt.id_usuario = u.id_usuario` → traer `u.nombre_completo` AS `nombre_usuario_actual`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_ptacliente` → `actividad_plandetrabajo`, `id_usuario` → `nombre_usuario_actual`

- Nota: la tabla ya tiene `nombre_usuario` desnormalizado.

---

### v_tbl_reporte

- Tabla base: `tbl_reporte`

- JOINs:
  - `JOIN tbl_clientes c ON r.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON r.id_consultor = con.id_consultor` → traer `con.nombre_consultor`
  - `JOIN detail_report dr ON r.id_detailreport = dr.id_detailreport` → traer `dr.detail_report` AS `tipo_detalle`
  - `JOIN report_type_table rt ON r.id_report_type = rt.id_report_type` → traer `rt.report_type` AS `tipo_reporte`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`, `id_detailreport` → `tipo_detalle`, `id_report_type` → `tipo_reporte`

---

### v_tbl_reporte_capacitacion

- Tabla base: `tbl_reporte_capacitacion`

- JOINs:
  - `JOIN tbl_clientes c ON rc.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `LEFT JOIN tbl_consultor con ON rc.id_consultor = con.id_consultor` → traer `con.nombre_consultor`
  - `LEFT JOIN tbl_cronog_capacitacion cc ON rc.id_cronograma_capacitacion = cc.id_cronograma_capacitacion` → traer `cc.estado` AS `estado_cronograma`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`, `id_cronograma_capacitacion` → `estado_cronograma`

---

### v_tbl_sesiones_usuario

- Tabla base: `tbl_sesiones_usuario`

- JOINs:
  - `JOIN tbl_usuarios u ON s.id_usuario = u.id_usuario` → traer `u.nombre_completo`, `u.email`, `u.tipo_usuario`

- Columnas resueltas: `id_usuario` → `nombre_completo`, `email`, `tipo_usuario`

---

### v_tbl_usuario_roles

- Tabla base: `tbl_usuario_roles`

- JOINs:
  - `JOIN tbl_usuarios u ON ur.id_usuario = u.id_usuario` → traer `u.nombre_completo`, `u.email`, `u.tipo_usuario`
  - `JOIN tbl_roles r ON ur.id_rol = r.id_rol` → traer `r.nombre_rol`, `r.descripcion`

- Columnas resueltas: `id_usuario` → `nombre_completo`, `id_rol` → `nombre_rol`

---

### v_tbl_vencimientos_mantenimientos

- Tabla base: `tbl_vencimientos_mantenimientos`

- JOINs:
  - `JOIN tbl_clientes c ON v.id_cliente = c.id_cliente` → traer `c.nombre_cliente`
  - `JOIN tbl_consultor con ON v.id_consultor = con.id_consultor` → traer `con.nombre_consultor`
  - `JOIN tbl_mantenimientos m ON v.id_mantenimiento = m.id_mantenimiento` → traer `m.detalle_mantenimiento` AS `nombre_mantenimiento`

- Columnas resueltas: `id_cliente` → `nombre_cliente`, `id_consultor` → `nombre_consultor`, `id_mantenimiento` → `nombre_mantenimiento`

---

### v_tbl_vigias

- Tabla base: `tbl_vigias`

- JOINs:
  - `JOIN tbl_clientes c ON v.id_cliente = c.id_cliente` → traer `c.nombre_cliente`

- Columnas resueltas: `id_cliente` → `nombre_cliente`

---

## Tablas sin vista necesaria (lookup / config puros)

Estas tablas son catálogos de referencia con valores fijos o tablas maestras que son destino de JOINs desde otras tablas. No tienen FKs hacia entidades con nombres legibles que requieran resolución adicional.

| Tabla | Registros | Motivo |
| --- | --- | --- |
| `capacitaciones_sst` | 31 | Catálogo de capacitaciones. Destino de JOIN desde `tbl_cronog_capacitacion`. |
| `detail_report` | 36 | Lookup de tipos de detalle de reporte. Destino de JOIN desde `tbl_reporte`. |
| `estandares` | 4 | Catálogo de estándares. Sin FKs hacia tablas con nombres. |
| `estandares_accesos` | 80 | Tabla pivote estandares ↔ accesos de menú. Datos de config interna. |
| `report_type_table` | 20 | Lookup de tipos de reporte. Destino de JOIN desde `tbl_reporte`. |
| `tbl_clientes` | 59 | Tabla maestra. Es el destino de todos los JOINs `id_cliente`. Ya tiene nombres. |
| `tbl_consultor` | 4 | Tabla maestra. Es el destino de todos los JOINs `id_consultor`. Ya tiene nombres. |
| `tbl_data_owner` | 1 | Lookup KPI. Solo 1 registro. Sin FKs hacia entidades SST. |
| `tbl_inventario_actividades_plandetrabajo` | 146 | Catálogo de actividades predefinidas. Sin FKs. Solo texto. |
| `tbl_kpi_definition` | 17 | Lookup KPI. Sin FKs hacia entidades SST. |
| `tbl_kpi_policy` | 1 | Lookup KPI. 1 registro. Sin FKs. |
| `tbl_kpi_type` | 4 | Lookup KPI. Sin FKs. |
| `tbl_kpis` | 17 | Lookup KPI. Sin FKs. |
| `tbl_listado_maestro_documentos` | 28 | Catálogo de 28 documentos Decreto 1072. Sin FKs. |
| `tbl_mantenimientos` | 18 | Lookup de tipos de mantenimiento. Destino de JOIN desde `tbl_vencimientos_mantenimientos`. |
| `tbl_matricescycloid` | 2 | Catálogo global de matrices Cycloid (sin FK a clientes). |
| `tbl_measurement_period` | 2 | Lookup KPI. 2 registros. |
| `tbl_objectives_policy` | 3 | Lookup KPI. 3 registros. |
| `tbl_presupuesto_categorias` | 7 | Catálogo de categorías. Destino de JOIN desde `tbl_presupuesto_items`. |
| `tbl_roles` | 3 | Catálogo de 3 roles. Destino de JOIN desde `tbl_usuario_roles`. |
| `tbl_urls` | 11 | Catálogo de URLs configurables del sistema. Sin FKs. |
| `tbl_usuarios` | 66 | Tabla maestra de usuarios. Destino de JOINs `id_usuario`. |
| `tbl_variable_denominator` | 15 | Lookup KPI. |
| `tbl_variable_numerator` | 15 | Lookup KPI. |

---

## Tablas sistema (no exponer a Otto)

Tablas de infraestructura técnica, auth interna, auditoría de sistema o datos de prueba.

| Tabla | Registros | Motivo |
| --- | --- | --- |
| `accesos` | 59 | Menú de accesos del sistema por URL. Config interna de navegación. |
| `dashboard_items` | 41 | Items de dashboard configurables por rol. Config interna. |
| `tbl_log_conteo_dias` | 869 | Log técnico de ejecuciones de script cron de conteo de días. Sin valor de negocio para Otto. |
| `tbl_planillas_seguridad_social` | 1 | Archivos de planilla de aportes. Scope contable-administrativo, no SST. |
| `tbl_sesiones_usuario` | 425 | Historial de sesiones activas/cerradas. Sistema auth. Se puede crear vista pero no exponer por defecto. |
| `tbl_tests` | 1 | Tabla de pruebas de desarrollo. |
| `prueba` | 2 | Tabla de pruebas de desarrollo. |

---

## Tablas para revisión humana (dominio desconocido o estructura compleja)

| Tabla | Registros | Motivo |
| --- | --- | --- |
| `client_policies` | 2638 | Tiene `client_id` y `policy_type_id` (convención en inglés, NO `id_cliente`). Desconocido si `client_id` referencia `tbl_clientes.id_cliente`. Revisar antes de crear vista. |
| `document_versions` | 2520 | Igual que `client_policies`. `client_id` y `policy_type_id` en inglés. Posible dominio externo de versionado de documentos. |
| `policy_types` | 44 | Lookup para `client_policies` y `document_versions`. En inglés. Revisar con `client_policies`. |
| `tbl_client_kpi` | 34 | 11 FKs declaradas hacia tablas KPI especializadas (`tbl_kpi_definition`, `tbl_kpi_type`, `tbl_kpis`, `tbl_objectives_policy`, `tbl_kpi_policy`, `tbl_data_owner`, `tbl_variable_numerator`, `tbl_variable_denominator`, `tbl_measurement_period`). Estructura con 12 bloques de variables numerador/denominador (columnas `variable_numerador_1` a `_12`). Sistema de cálculo de indicadores altamente especializado. Definir con el equipo qué columnas exponer a Otto antes de crear la vista. |

---

## Notas de implementación

### Convenciones de alias en SQL

Cuando la tabla base ya tenga una columna con el mismo nombre que la que se trae por JOIN (ej: tablas con `nombre_cliente` desnormalizado), usar alias con sufijo `_fk`:

- `c.nombre_cliente AS nombre_cliente_fk`

### LEFT JOIN vs INNER JOIN para `id_consultor`

Todas las columnas `id_consultor` en tablas de inspecciones, programas, actas y dotaciones están definidas como NULLable (FK solo lógica, sin restricción declarada). Usar siempre `LEFT JOIN tbl_consultor` para no perder filas donde el consultor es NULL.

### Columna `tbl_pendientes.id_acta` (varchar legacy)

El campo `id_acta` tiene formato `'AV-{id}'` y es `varchar(255)`. NO se puede usar en un `JOIN` numérico. La vista solo hace JOIN por `id_acta_visita` (int, FK declarada).

### Columna `tbl_ciclos_visita.id_acta`

Es `int` pero la documentación indica "varchar legacy". El JOIN con `tbl_acta_visita.id` es seguro para registros modernos. Usar `LEFT JOIN`.

### Tablas con 0 registros en producción

Las siguientes tablas están vacías pero las vistas se crean igual para cuando se llenen:
`tbl_carta_vigia`, `tbl_dotacion_aseadora`, `tbl_dotacion_todero`, `tbl_dotacion_vigilante`,
`tbl_evaluacion_induccion_respuesta`, `tbl_evaluacion_simulacro`, `tbl_gabinete_detalle`,
`tbl_hv_brigadista`, `tbl_inspeccion_comunicaciones`, `tbl_inspeccion_gabinetes`,
`tbl_inspeccion_recursos_seguridad`, `tbl_inspeccion_senalizacion`, `tbl_item_senalizacion`,
`tbl_kpi_agua_potable`, `tbl_matriz_vulnerabilidad`, `tbl_plan_emergencia`,
`tbl_preparacion_simulacro`, `tbl_probabilidad_peligros`.

### Orden de creación recomendado

Crear las vistas en este orden para evitar dependencias circulares:

1. Vistas de tablas maestras que son destino de JOINs pero no necesitan vista (no aplica, ya están en lookup)
2. Vistas de tablas padre: `v_tbl_acta_visita`, `v_tbl_asistencia_induccion`, `v_tbl_inspeccion_*`
3. Vistas de tablas hijas: `v_tbl_acta_visita_fotos`, `v_tbl_acta_visita_integrantes`, `v_tbl_acta_visita_temas`, `v_tbl_acta_visita_pta`, `v_tbl_elemento_botiquin`, `v_tbl_extintor_detalle`, `v_tbl_gabinete_detalle`, `v_tbl_hallazgo_locativo`, `v_tbl_item_senalizacion`, `v_tbl_asistencia_induccion_asistente`
4. Vistas con cadena larga (3+ JOINs): `v_tbl_presupuesto_detalle`, `v_tbl_presupuesto_items`
5. Resto de vistas en cualquier orden

### Archivo SQL de creación

Script recomendado: `app/SQL/create_views_otto.sql`
Ejecutar en producción con: `DB_PROD_PASS=xxx php app/SQL/apply_migration.php production`
