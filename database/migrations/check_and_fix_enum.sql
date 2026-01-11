-- =============================================================================
-- VERIFICAR Y CORREGIR ESTRUCTURA DE tbl_contratos
-- =============================================================================

-- PASO 1: Ver la estructura actual de la tabla
SHOW CREATE TABLE tbl_contratos;

-- PASO 2: Ver los valores ENUM permitidos
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'tbl_contratos'
  AND COLUMN_NAME IN ('tipo_contrato', 'estado');

-- PASO 3: Corregir la columna tipo_contrato si está mal
ALTER TABLE tbl_contratos
MODIFY COLUMN tipo_contrato ENUM('inicial', 'renovacion', 'ampliacion') NOT NULL DEFAULT 'inicial';

-- PASO 4: Corregir la columna estado si está mal
ALTER TABLE tbl_contratos
MODIFY COLUMN estado ENUM('activo', 'vencido', 'cancelado') NOT NULL DEFAULT 'activo';

-- PASO 5: Verificar que ahora está correcto
SELECT
    COLUMN_NAME,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'tbl_contratos'
  AND COLUMN_NAME IN ('tipo_contrato', 'estado');

-- PASO 6: Ahora sí, migrar los contratos
INSERT INTO tbl_contratos (
    id_cliente,
    numero_contrato,
    fecha_inicio,
    fecha_fin,
    valor_contrato,
    tipo_contrato,
    estado,
    observaciones,
    created_at,
    updated_at
)
SELECT
    c.id_cliente,
    CONCAT('CONT-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(c.id_cliente, 4, '0')) AS numero_contrato,
    COALESCE(c.fecha_ingreso, DATE(c.datetime), CURDATE()) AS fecha_inicio,
    COALESCE(c.fecha_fin_contrato, DATE_ADD(COALESCE(c.fecha_ingreso, DATE(c.datetime), CURDATE()), INTERVAL 6 MONTH)) AS fecha_fin,
    NULL AS valor_contrato,
    'inicial' AS tipo_contrato,
    CASE
        WHEN COALESCE(c.fecha_fin_contrato, DATE_ADD(CURDATE(), INTERVAL 6 MONTH)) >= CURDATE() THEN 'activo'
        ELSE 'vencido'
    END AS estado,
    CONCAT('Contrato inicial migrado. Cliente: ', c.nombre_cliente) AS observaciones,
    NOW() AS created_at,
    NOW() AS updated_at
FROM tbl_clientes c
WHERE c.estado = 'activo'
ORDER BY c.id_cliente;

-- PASO 7: Verificar resultado
SELECT
  COUNT(*) AS total_contratos,
  SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) AS activos,
  SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) AS vencidos,
  SUM(CASE WHEN tipo_contrato = 'inicial' THEN 1 ELSE 0 END) AS iniciales
FROM tbl_contratos;

-- PASO 8: Ver primeros 10 contratos
SELECT * FROM tbl_contratos ORDER BY id_contrato LIMIT 10;
