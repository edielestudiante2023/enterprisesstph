-- ============================================================================
-- MIGRACIÓN SEGURA DE TABLA tbl_cronog_capacitacion
-- ============================================================================
-- Este script implementa la estrategia de tabla nueva + renombrado
-- para preservar datos existentes mientras se actualiza la estructura.
--
-- IMPORTANTE: Hacer backup de la base de datos antes de ejecutar
-- ============================================================================

-- PASO 1: Crear nueva tabla con estructura correcta
-- ============================================================================
CREATE TABLE IF NOT EXISTS capacitaciones_nuevo (
    id_cronograma_capacitacion INT(11) NOT NULL AUTO_INCREMENT,
    id_capacitacion INT(11) NULL COMMENT 'Campo legacy para compatibilidad',
    nombre_capacitacion VARCHAR(255) NULL COMMENT 'Nuevo sistema: nombre de texto libre',
    objetivo_capacitacion TEXT NULL COMMENT 'Nuevo sistema: objetivo de texto libre',
    id_cliente INT(11) NULL,
    fecha_programada DATE NULL,
    fecha_de_realizacion DATE NULL,
    estado VARCHAR(255) NULL,
    perfil_de_asistentes VARCHAR(255) NULL,
    nombre_del_capacitador VARCHAR(255) NULL,
    horas_de_duracion_de_la_capacitacion INT(11) NULL,
    indicador_de_realizacion_de_la_capacitacion VARCHAR(255) NULL,
    numero_de_asistentes_a_capacitacion INT(11) NULL,
    numero_total_de_personas_programadas INT(11) NULL,
    porcentaje_cobertura VARCHAR(255) NULL,
    numero_de_personas_evaluadas INT(11) NULL,
    promedio_de_calificaciones DECIMAL(5,2) NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cronograma_capacitacion),
    INDEX idx_cliente (id_cliente),
    INDEX idx_fecha_programada (fecha_programada),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Nueva tabla de cronograma de capacitaciones con campos de texto libre';

-- PASO 2: Copiar datos haciendo JOIN con capacitaciones_sst
-- ============================================================================
-- Este INSERT copia los datos de la tabla original y hace JOIN con
-- capacitaciones_sst para obtener el nombre y objetivo de cada capacitación

INSERT INTO capacitaciones_nuevo (
    id_cronograma_capacitacion,
    id_capacitacion,
    nombre_capacitacion,
    objetivo_capacitacion,
    id_cliente,
    fecha_programada,
    fecha_de_realizacion,
    estado,
    perfil_de_asistentes,
    nombre_del_capacitador,
    horas_de_duracion_de_la_capacitacion,
    indicador_de_realizacion_de_la_capacitacion,
    numero_de_asistentes_a_capacitacion,
    numero_total_de_personas_programadas,
    porcentaje_cobertura,
    numero_de_personas_evaluadas,
    promedio_de_calificaciones,
    observaciones,
    created_at,
    updated_at
)
SELECT
    cc.id_cronograma_capacitacion,
    cc.id_capacitacion,
    COALESCE(cs.capacitacion, CONCAT('Capacitación ID ', cc.id_capacitacion)) as nombre_capacitacion,
    COALESCE(cs.objetivo_capacitacion, '') as objetivo_capacitacion,
    cc.id_cliente,
    cc.fecha_programada,
    cc.fecha_de_realizacion,
    cc.estado,
    cc.perfil_de_asistentes,
    cc.nombre_del_capacitador,
    cc.horas_de_duracion_de_la_capacitacion,
    cc.indicador_de_realizacion_de_la_capacitacion,
    cc.numero_de_asistentes_a_capacitacion,
    cc.numero_total_de_personas_programadas,
    cc.porcentaje_cobertura,
    cc.numero_de_personas_evaluadas,
    cc.promedio_de_calificaciones,
    cc.observaciones,
    NOW() as created_at,
    NOW() as updated_at
FROM tbl_cronog_capacitacion cc
LEFT JOIN capacitaciones_sst cs ON cc.id_capacitacion = cs.id_capacitacion;

-- PASO 3: Verificar la copia de datos
-- ============================================================================
SELECT
    'Registros en tabla original' as tabla,
    COUNT(*) as total_registros
FROM tbl_cronog_capacitacion
UNION ALL
SELECT
    'Registros en tabla nueva' as tabla,
    COUNT(*) as total_registros
FROM capacitaciones_nuevo;

-- PASO 4: Renombrar tablas (ejecutar solo después de verificar PASO 3)
-- ============================================================================
-- COMENTADO POR SEGURIDAD - Descomentar solo después de verificar los datos

-- RENAME TABLE tbl_cronog_capacitacion TO tbl_cronog_capacitacion_old;
-- RENAME TABLE capacitaciones_nuevo TO tbl_cronog_capacitacion;

-- PASO 5: Verificación final (ejecutar después del PASO 4)
-- ============================================================================
-- COMENTADO - Descomentar después de ejecutar el renombrado

-- SELECT
--     'Nueva tabla principal' as info,
--     COUNT(*) as total_registros
-- FROM tbl_cronog_capacitacion;

-- SELECT
--     'Backup tabla antigua' as info,
--     COUNT(*) as total_registros
-- FROM tbl_cronog_capacitacion_old;

-- ============================================================================
-- ROLLBACK (Solo en caso de emergencia)
-- ============================================================================
-- Si algo sale mal después del renombrado, ejecutar:

-- RENAME TABLE tbl_cronog_capacitacion TO capacitaciones_fallida;
-- RENAME TABLE tbl_cronog_capacitacion_old TO tbl_cronog_capacitacion;
-- DROP TABLE capacitaciones_fallida;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
