-- ============================================================================
-- Tabla: tbl_inspeccion_no_aplica
-- Proposito: Permite marcar por cliente que cierto tipo de inspeccion
-- (p.ej. Piscinas, Ascensores, Piscinero, Senalizacion) NO APLICA.
-- Consumida por: MatrizInspeccionesController (vista Matriz de Inspecciones).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_inspeccion_no_aplica` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `tipo_inspeccion` VARCHAR(50) NOT NULL COMMENT 'Slug canonico del catalogo InspeccionTypes (p.ej. piscinas, ascensores, senalizacion)',
    `motivo` VARCHAR(255) NULL COMMENT 'Razon opcional por la que no aplica (p.ej. Edificio sin ascensor)',
    `marcado_por` INT NULL COMMENT 'id_consultor que marco la exclusion',
    `fecha_marcado` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cliente_tipo` (`id_cliente`, `tipo_inspeccion`),
    KEY `idx_tipo` (`tipo_inspeccion`),
    KEY `idx_cliente` (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
