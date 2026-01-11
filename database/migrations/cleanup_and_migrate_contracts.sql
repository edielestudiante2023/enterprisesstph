-- =============================================================================
-- LIMPIEZA Y MIGRACIÓN DE CONTRATOS
-- =============================================================================
-- Este script limpia la tabla de contratos y vuelve a migrar correctamente
-- =============================================================================

-- PASO 1: Limpiar tabla de contratos (eliminar todos los registros)
TRUNCATE TABLE tbl_contratos;

-- PASO 2: Resetear el AUTO_INCREMENT
ALTER TABLE tbl_contratos AUTO_INCREMENT = 1;

-- PASO 3: Migrar contratos desde tbl_clientes (solo clientes activos, sin duplicados)
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
    CONCAT('CONT-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(id_cliente, 4, '0')) AS numero_contrato,
    COALESCE(fecha_ingreso, datetime) AS fecha_inicio,
    fecha_fin_contrato AS fecha_fin,
    NULL AS valor_contrato,
    'inicial' AS tipo_contrato,
    CASE
        WHEN fecha_fin_contrato IS NULL THEN 'activo'
        WHEN fecha_fin_contrato >= CURDATE() THEN 'activo'
        ELSE 'vencido'
    END AS estado,
    CONCAT('Contrato migrado automáticamente. Cliente: ', nombre_cliente) AS observaciones,
    COALESCE(datetime, NOW()) AS created_at,
    NOW() AS updated_at
FROM tbl_clientes
WHERE estado = 'activo'
  AND id_cliente IS NOT NULL
GROUP BY id_cliente -- Esto evita duplicados si hay algún problema en tbl_clientes
ORDER BY id_cliente;

-- PASO 4: Verificar la migración
SELECT
    'Resumen de Migración' AS descripcion,
    '' AS detalle
UNION ALL
SELECT
    'Total clientes activos:',
    CAST(COUNT(DISTINCT id_cliente) AS CHAR)
FROM tbl_clientes
WHERE estado = 'activo'
UNION ALL
SELECT
    'Total contratos migrados:',
    CAST(COUNT(*) AS CHAR)
FROM tbl_contratos
UNION ALL
SELECT
    'Contratos activos:',
    CAST(COUNT(*) AS CHAR)
FROM tbl_contratos
WHERE estado = 'activo'
UNION ALL
SELECT
    'Contratos vencidos:',
    CAST(COUNT(*) AS CHAR)
FROM tbl_contratos
WHERE estado = 'vencido';

-- PASO 5: Mostrar primeros 10 contratos migrados
SELECT
    ct.id_contrato,
    ct.numero_contrato,
    c.nombre_cliente,
    ct.fecha_inicio,
    ct.fecha_fin,
    ct.tipo_contrato,
    ct.estado,
    DATEDIFF(ct.fecha_fin, CURDATE()) AS dias_restantes
FROM tbl_contratos ct
INNER JOIN tbl_clientes c ON ct.id_cliente = c.id_cliente
ORDER BY ct.id_contrato
LIMIT 10;

-- PASO 6: Buscar y mostrar posibles duplicados (no debería haber ninguno)
SELECT
    id_cliente,
    COUNT(*) as cantidad_contratos,
    GROUP_CONCAT(numero_contrato) as numeros_contrato
FROM tbl_contratos
GROUP BY id_cliente
HAVING COUNT(*) > 1;
