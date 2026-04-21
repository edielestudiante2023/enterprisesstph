-- ============================================================================
-- Tabla: tbl_pta_no_aplica
-- Proposito: Excluir actividades del Plan de Trabajo Anual que el consultor
-- marco explicitamente como "no aplica" (no se cruzaran contra inspecciones y
-- no cuentan en el % Cumplimiento del semaforo).
-- Consumida por PtaSemaforoController.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_pta_no_aplica` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_ptacliente` INT NOT NULL COMMENT 'FK a tbl_pta_cliente.id_ptacliente',
    `motivo` VARCHAR(255) NULL COMMENT 'Razon opcional (ej: actividad puramente administrativa)',
    `marcado_por` INT NULL COMMENT 'id_consultor',
    `fecha_marcado` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cliente_pta` (`id_cliente`, `id_ptacliente`),
    KEY `idx_cliente` (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
