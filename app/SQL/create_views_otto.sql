-- =============================================================
-- VISTAS SQL PARA OTTO - Chat IA enterprisesstph
-- Base de datos: propiedad_horizontal
-- Generado: 2026-03-15
-- Cobertura: 53 vistas (todas las tablas de negocio con FKs)
-- NOTA: Para INSERT/UPDATE/DELETE seguir usando tablas tbl_*
-- =============================================================

-- =============================================
-- GRUPO 1: Vistas de tablas padre
-- (tablas maestras de inspecciones y visitas)
-- =============================================

CREATE OR REPLACE VIEW v_tbl_acta_visita AS
SELECT a.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_acta_visita a
JOIN tbl_clientes c ON a.id_cliente = c.id_cliente
JOIN tbl_consultor con ON a.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_inspeccion_botiquin AS
SELECT i.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_inspeccion_botiquin i
JOIN tbl_clientes c ON i.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_inspeccion_extintores AS
SELECT i.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_inspeccion_extintores i
JOIN tbl_clientes c ON i.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_inspeccion_gabinetes AS
SELECT i.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_inspeccion_gabinetes i
JOIN tbl_clientes c ON i.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_inspeccion_locativa AS
SELECT i.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_inspeccion_locativa i
JOIN tbl_clientes c ON i.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_inspeccion_senalizacion AS
SELECT i.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_inspeccion_senalizacion i
JOIN tbl_clientes c ON i.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_inspeccion_comunicaciones AS
SELECT i.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_inspeccion_comunicaciones i
JOIN tbl_clientes c ON i.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_inspeccion_recursos_seguridad AS
SELECT i.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_inspeccion_recursos_seguridad i
JOIN tbl_clientes c ON i.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON i.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_asistencia_induccion AS
SELECT a.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_asistencia_induccion a
JOIN tbl_clientes c ON a.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON a.id_consultor = con.id_consultor;

-- =============================================
-- GRUPO 2: Vistas de tablas hijas (detalle)
-- =============================================

CREATE OR REPLACE VIEW v_tbl_acta_visita_fotos AS
SELECT f.*,
       av.fecha_visita,
       c.nombre_cliente
FROM tbl_acta_visita_fotos f
JOIN tbl_acta_visita av ON f.id_acta_visita = av.id
JOIN tbl_clientes c ON av.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_acta_visita_integrantes AS
SELECT i.*,
       av.fecha_visita,
       c.nombre_cliente
FROM tbl_acta_visita_integrantes i
JOIN tbl_acta_visita av ON i.id_acta_visita = av.id
JOIN tbl_clientes c ON av.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_acta_visita_temas AS
SELECT t.*,
       av.fecha_visita,
       c.nombre_cliente
FROM tbl_acta_visita_temas t
JOIN tbl_acta_visita av ON t.id_acta_visita = av.id
JOIN tbl_clientes c ON av.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_acta_visita_pta AS
SELECT p.*,
       av.fecha_visita,
       c.nombre_cliente,
       pta.actividad_plandetrabajo,
       pta.estado_actividad
FROM tbl_acta_visita_pta p
JOIN tbl_acta_visita av ON p.id_acta_visita = av.id
JOIN tbl_clientes c ON av.id_cliente = c.id_cliente
JOIN tbl_pta_cliente pta ON p.id_ptacliente = pta.id_ptacliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_elemento_botiquin AS
SELECT e.*,
       ib.fecha_inspeccion,
       c.nombre_cliente
FROM tbl_elemento_botiquin e
JOIN tbl_inspeccion_botiquin ib ON e.id_inspeccion = ib.id
JOIN tbl_clientes c ON ib.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_extintor_detalle AS
SELECT e.*,
       ie.fecha_inspeccion,
       c.nombre_cliente
FROM tbl_extintor_detalle e
JOIN tbl_inspeccion_extintores ie ON e.id_inspeccion = ie.id
JOIN tbl_clientes c ON ie.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_gabinete_detalle AS
SELECT g.*,
       ig.fecha_inspeccion,
       c.nombre_cliente
FROM tbl_gabinete_detalle g
JOIN tbl_inspeccion_gabinetes ig ON g.id_inspeccion = ig.id
JOIN tbl_clientes c ON ig.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_hallazgo_locativo AS
SELECT h.*,
       il.fecha_inspeccion,
       c.nombre_cliente
FROM tbl_hallazgo_locativo h
JOIN tbl_inspeccion_locativa il ON h.id_inspeccion = il.id
JOIN tbl_clientes c ON il.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_item_senalizacion AS
SELECT it.*,
       ins.fecha_inspeccion,
       c.nombre_cliente
FROM tbl_item_senalizacion it
JOIN tbl_inspeccion_senalizacion ins ON it.id_inspeccion = ins.id
JOIN tbl_clientes c ON ins.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_asistencia_induccion_asistente AS
SELECT a.*,
       asi.fecha_sesion,
       asi.tema,
       c.nombre_cliente
FROM tbl_asistencia_induccion_asistente a
JOIN tbl_asistencia_induccion asi ON a.id_asistencia = asi.id
JOIN tbl_clientes c ON asi.id_cliente = c.id_cliente;

-- =============================================
-- GRUPO 3: Vistas con 3+ JOINs (cadena larga)
-- =============================================

CREATE OR REPLACE VIEW v_tbl_presupuesto_items AS
SELECT pi.*,
       ps.anio,
       c.nombre_cliente,
       pc.nombre AS nombre_categoria
FROM tbl_presupuesto_items pi
JOIN tbl_presupuesto_sst ps ON pi.id_presupuesto = ps.id_presupuesto
JOIN tbl_clientes c ON ps.id_cliente = c.id_cliente
JOIN tbl_presupuesto_categorias pc ON pi.id_categoria = pc.id_categoria;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_presupuesto_detalle AS
SELECT d.*,
       pi.actividad AS nombre_item,
       pc.nombre AS nombre_categoria,
       ps.anio AS anio_presupuesto,
       c.nombre_cliente
FROM tbl_presupuesto_detalle d
JOIN tbl_presupuesto_items pi ON d.id_item = pi.id_item
JOIN tbl_presupuesto_sst ps ON pi.id_presupuesto = ps.id_presupuesto
JOIN tbl_clientes c ON ps.id_cliente = c.id_cliente
JOIN tbl_presupuesto_categorias pc ON pi.id_categoria = pc.id_categoria;

-- =============================================
-- GRUPO 4: Resto de vistas de negocio
-- =============================================

CREATE OR REPLACE VIEW v_evaluacion_inicial_sst AS
SELECT e.*,
       c.nombre_cliente
FROM evaluacion_inicial_sst e
JOIN tbl_clientes c ON e.id_cliente = c.id_cliente;

-- -----------------------------------------------

-- NOTA: historial_resumen_estandares ya tiene nombre_cliente desnormalizado.
-- El JOIN permite detectar inconsistencias; alias _fk para no colisionar.
CREATE OR REPLACE VIEW v_historial_resumen_estandares AS
SELECT h.*,
       c.nombre_cliente AS nombre_cliente_fk
FROM historial_resumen_estandares h
JOIN tbl_clientes c ON h.id_cliente = c.id_cliente;

-- -----------------------------------------------

-- NOTA: historial_resumen_plan_trabajo igual que el anterior.
CREATE OR REPLACE VIEW v_historial_resumen_plan_trabajo AS
SELECT h.*,
       c.nombre_cliente AS nombre_cliente_fk
FROM historial_resumen_plan_trabajo h
JOIN tbl_clientes c ON h.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_agendamientos AS
SELECT a.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_agendamientos a
LEFT JOIN tbl_clientes c ON a.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON a.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_auditoria_zona_residuos AS
SELECT a.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_auditoria_zona_residuos a
JOIN tbl_clientes c ON a.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON a.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_carta_vigia AS
SELECT cv.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_carta_vigia cv
JOIN tbl_clientes c ON cv.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON cv.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_chat_log AS
SELECT cl.*,
       u.nombre_completo,
       u.email,
       u.tipo_usuario
FROM tbl_chat_log cl
JOIN tbl_usuarios u ON cl.id_usuario = u.id_usuario;

-- -----------------------------------------------

-- NOTA: id_acta en tbl_ciclos_visita es int; LEFT JOIN seguro para registros modernos.
CREATE OR REPLACE VIEW v_tbl_ciclos_visita AS
SELECT cv.*,
       c.nombre_cliente,
       con.nombre_consultor,
       av.fecha_visita
FROM tbl_ciclos_visita cv
JOIN tbl_clientes c ON cv.id_cliente = c.id_cliente
JOIN tbl_consultor con ON cv.id_consultor = con.id_consultor
LEFT JOIN tbl_acta_visita av ON cv.id_acta = av.id;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_contratos AS
SELECT ct.*,
       c.nombre_cliente
FROM tbl_contratos ct
JOIN tbl_clientes c ON ct.id_cliente = c.id_cliente;

-- -----------------------------------------------

-- NOTA: tabla ya tiene nombre_capacitacion desnormalizado; JOIN con catálogo es adicional.
CREATE OR REPLACE VIEW v_tbl_cronog_capacitacion AS
SELECT cc.*,
       c.nombre_cliente,
       cs.capacitacion AS nombre_capacitacion_catalogo
FROM tbl_cronog_capacitacion cc
JOIN tbl_clientes c ON cc.id_cliente = c.id_cliente
LEFT JOIN capacitaciones_sst cs ON cc.id_capacitacion = cs.id_capacitacion;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_cronog_capacitacion_old AS
SELECT cc.*,
       c.nombre_cliente,
       cs.capacitacion AS nombre_capacitacion
FROM tbl_cronog_capacitacion_old cc
JOIN tbl_clientes c ON cc.id_cliente = c.id_cliente
JOIN capacitaciones_sst cs ON cc.id_capacitacion = cs.id_capacitacion;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_dotacion_aseadora AS
SELECT d.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_dotacion_aseadora d
JOIN tbl_clientes c ON d.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON d.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_dotacion_todero AS
SELECT d.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_dotacion_todero d
JOIN tbl_clientes c ON d.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON d.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_dotacion_vigilante AS
SELECT d.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_dotacion_vigilante d
JOIN tbl_clientes c ON d.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON d.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_evaluacion_induccion AS
SELECT e.*,
       c.nombre_cliente,
       asi.fecha_sesion,
       asi.tema
FROM tbl_evaluacion_induccion e
JOIN tbl_clientes c ON e.id_cliente = c.id_cliente
LEFT JOIN tbl_asistencia_induccion asi ON e.id_asistencia_induccion = asi.id;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_evaluacion_induccion_respuesta AS
SELECT r.*,
       ei.titulo,
       c.nombre_cliente
FROM tbl_evaluacion_induccion_respuesta r
JOIN tbl_evaluacion_induccion ei ON r.id_evaluacion = ei.id
JOIN tbl_clientes c ON ei.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_evaluacion_simulacro AS
SELECT es.*,
       c.nombre_cliente
FROM tbl_evaluacion_simulacro es
JOIN tbl_clientes c ON es.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_hv_brigadista AS
SELECT h.*,
       c.nombre_cliente
FROM tbl_hv_brigadista h
JOIN tbl_clientes c ON h.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_informe_avances AS
SELECT ia.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_informe_avances ia
JOIN tbl_clientes c ON ia.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON ia.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_kpi_agua_potable AS
SELECT k.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_kpi_agua_potable k
JOIN tbl_clientes c ON k.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_kpi_limpieza AS
SELECT k.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_kpi_limpieza k
JOIN tbl_clientes c ON k.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_kpi_plagas AS
SELECT k.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_kpi_plagas k
JOIN tbl_clientes c ON k.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_kpi_residuos AS
SELECT k.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_kpi_residuos k
JOIN tbl_clientes c ON k.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON k.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_lookerstudio AS
SELECT l.*,
       c.nombre_cliente
FROM tbl_lookerstudio l
JOIN tbl_clientes c ON l.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_matrices AS
SELECT m.*,
       c.nombre_cliente
FROM tbl_matrices m
JOIN tbl_clientes c ON m.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_matriz_vulnerabilidad AS
SELECT m.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_matriz_vulnerabilidad m
JOIN tbl_clientes c ON m.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON m.id_consultor = con.id_consultor;

-- -----------------------------------------------

-- NOTA: id_acta (varchar 'AV-{id}') NO se usa en JOIN. Solo id_acta_visita (int).
CREATE OR REPLACE VIEW v_tbl_pendientes AS
SELECT p.*,
       c.nombre_cliente,
       av.fecha_visita
FROM tbl_pendientes p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_acta_visita av ON p.id_acta_visita = av.id;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_plan_emergencia AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_plan_emergencia p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_plan_saneamiento AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_plan_saneamiento p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_preparacion_simulacro AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_preparacion_simulacro p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_presupuesto_sst AS
SELECT ps.*,
       c.nombre_cliente
FROM tbl_presupuesto_sst ps
JOIN tbl_clientes c ON ps.id_cliente = c.id_cliente;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_probabilidad_peligros AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_probabilidad_peligros p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_programa_agua_potable AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_programa_agua_potable p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_programa_limpieza AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_programa_limpieza p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_programa_plagas AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_programa_plagas p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_programa_residuos AS
SELECT p.*,
       c.nombre_cliente,
       con.nombre_consultor
FROM tbl_programa_residuos p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON p.id_consultor = con.id_consultor;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_pta_cliente AS
SELECT pt.*,
       c.nombre_cliente
FROM tbl_pta_cliente pt
JOIN tbl_clientes c ON pt.id_cliente = c.id_cliente;

-- -----------------------------------------------

-- NOTA: tabla ya tiene nombre_usuario y email_usuario desnormalizados.
CREATE OR REPLACE VIEW v_tbl_pta_cliente_audit AS
SELECT a.*,
       c.nombre_cliente,
       pta.actividad_plandetrabajo,
       u.nombre_completo AS nombre_usuario_actual
FROM tbl_pta_cliente_audit a
LEFT JOIN tbl_clientes c ON a.id_cliente = c.id_cliente
LEFT JOIN tbl_pta_cliente pta ON a.id_ptacliente = pta.id_ptacliente
LEFT JOIN tbl_usuarios u ON a.id_usuario = u.id_usuario;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_pta_cliente_old AS
SELECT pt.*,
       c.nombre_cliente
FROM tbl_pta_cliente_old pt
JOIN tbl_clientes c ON pt.id_cliente = c.id_cliente;

-- -----------------------------------------------

-- NOTA: tabla ya tiene nombre_usuario desnormalizado.
CREATE OR REPLACE VIEW v_tbl_pta_transiciones AS
SELECT pt.*,
       c.nombre_cliente,
       pta.actividad_plandetrabajo,
       u.nombre_completo AS nombre_usuario_actual
FROM tbl_pta_transiciones pt
JOIN tbl_clientes c ON pt.id_cliente = c.id_cliente
JOIN tbl_pta_cliente pta ON pt.id_ptacliente = pta.id_ptacliente
LEFT JOIN tbl_usuarios u ON pt.id_usuario = u.id_usuario;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_reporte AS
SELECT r.*,
       c.nombre_cliente,
       con.nombre_consultor,
       dr.detail_report AS tipo_detalle,
       rt.report_type AS tipo_reporte
FROM tbl_reporte r
JOIN tbl_clientes c ON r.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON r.id_consultor = con.id_consultor
JOIN detail_report dr ON r.id_detailreport = dr.id_detailreport
JOIN report_type_table rt ON r.id_report_type = rt.id_report_type;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_reporte_capacitacion AS
SELECT rc.*,
       c.nombre_cliente,
       con.nombre_consultor,
       cc.estado AS estado_cronograma
FROM tbl_reporte_capacitacion rc
JOIN tbl_clientes c ON rc.id_cliente = c.id_cliente
LEFT JOIN tbl_consultor con ON rc.id_consultor = con.id_consultor
LEFT JOIN tbl_cronog_capacitacion cc ON rc.id_cronograma_capacitacion = cc.id_cronograma_capacitacion;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_sesiones_usuario AS
SELECT s.*,
       u.nombre_completo,
       u.email,
       u.tipo_usuario
FROM tbl_sesiones_usuario s
JOIN tbl_usuarios u ON s.id_usuario = u.id_usuario;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_usuario_roles AS
SELECT ur.*,
       u.nombre_completo,
       u.email,
       u.tipo_usuario,
       r.nombre_rol,
       r.descripcion AS descripcion_rol
FROM tbl_usuario_roles ur
JOIN tbl_usuarios u ON ur.id_usuario = u.id_usuario
JOIN tbl_roles r ON ur.id_rol = r.id_rol;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_vencimientos_mantenimientos AS
SELECT v.*,
       c.nombre_cliente,
       con.nombre_consultor,
       m.detalle_mantenimiento AS nombre_mantenimiento
FROM tbl_vencimientos_mantenimientos v
JOIN tbl_clientes c ON v.id_cliente = c.id_cliente
JOIN tbl_consultor con ON v.id_consultor = con.id_consultor
JOIN tbl_mantenimientos m ON v.id_mantenimiento = m.id_mantenimiento;

-- -----------------------------------------------

CREATE OR REPLACE VIEW v_tbl_vigias AS
SELECT v.*,
       c.nombre_cliente
FROM tbl_vigias v
JOIN tbl_clientes c ON v.id_cliente = c.id_cliente;

-- =============================================================
-- FIN DEL SCRIPT - 53 vistas creadas
-- Ejecutar con: mysql -h HOST -P 25060 -u USER -p DATABASE < create_views_otto.sql
-- =============================================================
