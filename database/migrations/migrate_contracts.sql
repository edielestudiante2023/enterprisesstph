-- =============================================================================
-- MIGRACIÓN DE DATOS: tbl_clientes -> tbl_contratos
-- =============================================================================
-- Este script migra los contratos existentes en tbl_clientes hacia la nueva
-- tabla tbl_contratos, creando un registro histórico para cada cliente.
-- =============================================================================

-- Insertar contratos existentes desde tbl_clientes
-- Se considera el primer contrato como tipo 'inicial'
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
    id_cliente,
    CONCAT('CONT-', LPAD(id_cliente, 6, '0'), '-001') AS numero_contrato,
    COALESCE(fecha_ingreso, datetime) AS fecha_inicio,
    fecha_fin_contrato AS fecha_fin,
    NULL AS valor_contrato, -- No existe en la tabla actual
    'inicial' AS tipo_contrato,
    CASE
        WHEN fecha_fin_contrato IS NULL THEN 'activo'
        WHEN fecha_fin_contrato >= CURDATE() THEN 'activo'
        ELSE 'vencido'
    END AS estado,
    CONCAT('Contrato migrado desde sistema legacy. Cliente: ', nombre_cliente) AS observaciones,
    COALESCE(datetime, NOW()) AS created_at,
    NOW() AS updated_at
FROM tbl_clientes
WHERE estado = 'activo'; -- Solo migrar clientes activos

-- Verificar la migración
SELECT
    'Total clientes activos' AS descripcion,
    COUNT(*) AS cantidad
FROM tbl_clientes
WHERE estado = 'activo'

UNION ALL

SELECT
    'Total contratos migrados' AS descripcion,
    COUNT(*) AS cantidad
FROM tbl_contratos;

-- Mostrar contratos migrados con información del cliente
SELECT
    ct.id_contrato,
    ct.numero_contrato,
    c.nombre_cliente,
    ct.fecha_inicio,
    ct.fecha_fin,
    ct.tipo_contrato,
    ct.estado,
    DATEDIFF(ct.fecha_fin, CURDATE()) AS dias_para_vencer
FROM tbl_contratos ct
INNER JOIN tbl_clientes c ON ct.id_cliente = c.id_cliente
ORDER BY ct.id_cliente;
