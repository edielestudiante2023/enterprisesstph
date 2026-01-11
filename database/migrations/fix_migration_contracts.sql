-- =============================================================================
-- DIAGNÓSTICO Y CORRECCIÓN DE MIGRACIÓN DE CONTRATOS
-- =============================================================================

-- PASO 1: Verificar si hay clientes activos
SELECT
    'DIAGNÓSTICO' as paso,
    '==================' as detalle
UNION ALL
SELECT
    'Total clientes en tbl_clientes:',
    CAST(COUNT(*) AS CHAR)
FROM tbl_clientes
UNION ALL
SELECT
    'Clientes activos:',
    CAST(COUNT(*) AS CHAR)
FROM tbl_clientes
WHERE estado = 'activo'
UNION ALL
SELECT
    'Clientes con fecha_fin_contrato:',
    CAST(COUNT(*) AS CHAR)
FROM tbl_clientes
WHERE fecha_fin_contrato IS NOT NULL;

-- PASO 2: Ver primeros 5 clientes activos con sus fechas
SELECT
    id_cliente,
    nombre_cliente,
    fecha_ingreso,
    fecha_fin_contrato,
    estado
FROM tbl_clientes
WHERE estado = 'activo'
ORDER BY id_cliente
LIMIT 5;

-- PASO 3: MIGRACIÓN MEJORADA - Inserta contratos desde tbl_clientes
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
    -- Fecha inicio: usar fecha_ingreso, si no existe usar datetime, si no usar hoy
    COALESCE(c.fecha_ingreso, DATE(c.datetime), CURDATE()) AS fecha_inicio,
    -- Fecha fin: usar fecha_fin_contrato, si no existe calcular 6 meses desde inicio
    COALESCE(
        c.fecha_fin_contrato,
        DATE_ADD(COALESCE(c.fecha_ingreso, DATE(c.datetime), CURDATE()), INTERVAL 6 MONTH)
    ) AS fecha_fin,
    NULL AS valor_contrato,
    'inicial' AS tipo_contrato,
    -- Estado: activo si no ha vencido, vencido si ya pasó
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

-- PASO 4: Verificar resultado de la migración
SELECT
    '==================' as separador,
    'RESULTADO MIGRACIÓN' as titulo
UNION ALL
SELECT
    'Contratos migrados:',
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
    DATEDIFF(ct.fecha_fin, CURDATE()) AS dias_restantes,
    ct.tipo_contrato,
    ct.estado
FROM tbl_contratos ct
INNER JOIN tbl_clientes c ON ct.id_cliente = c.id_cliente
ORDER BY ct.id_contrato
LIMIT 10;

-- PASO 6: Verificar que NO hay duplicados
SELECT
    id_cliente,
    COUNT(*) as cantidad,
    GROUP_CONCAT(numero_contrato) as contratos
FROM tbl_contratos
GROUP BY id_cliente
HAVING COUNT(*) > 1;
