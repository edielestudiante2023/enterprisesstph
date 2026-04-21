-- ============================================================================
-- Tabla: tbl_pta_inspeccion_match
-- Proposito: Mapeo semantico entre actividades del Plan de Trabajo Anual (PTA)
-- y tipos de inspeccion del catalogo InspeccionTypes.
-- Poblada por Claude Haiku 4.5 via PtaClassifier; editable manualmente.
-- Consumida por PtaSemaforoController (tablero ejecutivo).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_pta_inspeccion_match` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_ptacliente` INT NOT NULL COMMENT 'FK a tbl_pta_cliente.id_ptacliente',
    `slug_inspeccion` VARCHAR(60) NOT NULL COMMENT 'Slug canonico del catalogo InspeccionTypes',
    `score` DECIMAL(4,3) NOT NULL DEFAULT 0.000 COMMENT 'Confianza del match 0.000 - 1.000',
    `method` ENUM('ai','manual','confirmed') NOT NULL DEFAULT 'ai' COMMENT 'Origen del mapeo',
    `reasoning` TEXT NULL COMMENT 'Explicacion breve del match (solo para ai)',
    `ai_model` VARCHAR(60) NULL COMMENT 'Modelo que genero el match',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pta_slug` (`id_cliente`, `id_ptacliente`, `slug_inspeccion`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_slug` (`slug_inspeccion`),
    KEY `idx_score` (`score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
