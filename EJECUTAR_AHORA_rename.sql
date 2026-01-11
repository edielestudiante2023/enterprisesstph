-- ============================================================================
-- RENOMBRADO DE TABLAS - EJECUTAR AHORA
-- ============================================================================
-- Este script renombra las tablas para activar la nueva estructura.
-- La tabla capacitaciones_nuevo ya tiene todos los datos migrados.
-- ============================================================================

-- Renombrar tabla antigua como backup
RENAME TABLE tbl_cronog_capacitacion TO tbl_cronog_capacitacion_old;

-- Renombrar tabla nueva como principal
RENAME TABLE capacitaciones_nuevo TO tbl_cronog_capacitacion;

-- ============================================================================
-- VERIFICACIÓN POST-RENOMBRADO
-- ============================================================================

-- Ver estructura de la nueva tabla principal
DESCRIBE tbl_cronog_capacitacion;

-- Contar registros
SELECT 'Nueva tabla principal' as info, COUNT(*) as total FROM tbl_cronog_capacitacion;
SELECT 'Backup tabla antigua' as info, COUNT(*) as total FROM tbl_cronog_capacitacion_old;

-- Ver muestra de datos con nombres de capacitaciones
SELECT
    id_cronograma_capacitacion,
    nombre_capacitacion,
    objetivo_capacitacion,
    estado
FROM tbl_cronog_capacitacion
LIMIT 5;
