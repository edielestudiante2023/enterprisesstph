-- =============================================================================
-- AGREGAR CAMPOS PARA GENERACIÓN AUTOMÁTICA DE CONTRATOS
-- =============================================================================
-- Agrega campos necesarios para generar contratos en PDF y enviarlos por email
-- =============================================================================

-- PASO 1: Agregar columnas a tbl_contratos
ALTER TABLE tbl_contratos
ADD COLUMN nombre_rep_legal_cliente VARCHAR(255) NULL AFTER observaciones,
ADD COLUMN cedula_rep_legal_cliente VARCHAR(50) NULL AFTER nombre_rep_legal_cliente,
ADD COLUMN direccion_cliente VARCHAR(255) NULL AFTER cedula_rep_legal_cliente,
ADD COLUMN telefono_cliente VARCHAR(50) NULL AFTER direccion_cliente,
ADD COLUMN email_cliente VARCHAR(255) NULL AFTER telefono_cliente,

-- Datos de Cycloid Talent (EL CONTRATISTA)
ADD COLUMN nombre_rep_legal_contratista VARCHAR(255) DEFAULT 'DIANA PATRICIA CUESTAS NAVIA' AFTER email_cliente,
ADD COLUMN cedula_rep_legal_contratista VARCHAR(50) DEFAULT '52.425.982' AFTER nombre_rep_legal_contratista,
ADD COLUMN email_contratista VARCHAR(255) DEFAULT 'Diana.cuestas@cycloidtalent.com' AFTER cedula_rep_legal_contratista,

-- Responsable SG-SST asignado
ADD COLUMN nombre_responsable_sgsst VARCHAR(255) DEFAULT 'Edison Ernesto Cuervo Salazar' AFTER email_contratista,
ADD COLUMN cedula_responsable_sgsst VARCHAR(50) DEFAULT '80.039.147' AFTER nombre_responsable_sgsst,
ADD COLUMN licencia_responsable_sgsst VARCHAR(50) DEFAULT '4241' AFTER cedula_responsable_sgsst,
ADD COLUMN email_responsable_sgsst VARCHAR(255) DEFAULT 'Edison.cuervo@cycloidtalent.com' AFTER licencia_responsable_sgsst,

-- Detalles de pago
ADD COLUMN valor_mensual DECIMAL(15,2) NULL AFTER email_responsable_sgsst,
ADD COLUMN numero_cuotas INT DEFAULT 12 AFTER valor_mensual,
ADD COLUMN frecuencia_visitas VARCHAR(50) DEFAULT 'BIMENSUAL' AFTER numero_cuotas,
ADD COLUMN cuenta_bancaria VARCHAR(100) DEFAULT '108900260762' AFTER frecuencia_visitas,
ADD COLUMN banco VARCHAR(100) DEFAULT 'Davivienda' AFTER cuenta_bancaria,
ADD COLUMN tipo_cuenta VARCHAR(50) DEFAULT 'Ahorros' AFTER banco,

-- Control de generación y envío
ADD COLUMN contrato_generado TINYINT(1) DEFAULT 0 AFTER tipo_cuenta,
ADD COLUMN fecha_generacion_contrato DATETIME NULL AFTER contrato_generado,
ADD COLUMN ruta_pdf_contrato VARCHAR(255) NULL AFTER fecha_generacion_contrato,
ADD COLUMN contrato_enviado TINYINT(1) DEFAULT 0 AFTER ruta_pdf_contrato,
ADD COLUMN fecha_envio_contrato DATETIME NULL AFTER contrato_enviado,
ADD COLUMN email_envio_contrato VARCHAR(255) NULL AFTER fecha_envio_contrato;

-- PASO 2: Actualizar registros existentes con datos del cliente desde tbl_clientes
UPDATE tbl_contratos ct
INNER JOIN tbl_clientes c ON ct.id_cliente = c.id_cliente
SET
    ct.nombre_rep_legal_cliente = c.nombre_rep_legal,
    ct.cedula_rep_legal_cliente = c.cedula_rep_legal,
    ct.direccion_cliente = c.direccion_cliente,
    ct.telefono_cliente = c.telefono_1_cliente,
    ct.email_cliente = c.correo_cliente,
    ct.valor_mensual = CASE
        WHEN ct.valor_contrato IS NOT NULL THEN ct.valor_contrato / 12
        ELSE NULL
    END;

-- PASO 3: Crear índices para mejorar rendimiento
CREATE INDEX idx_contrato_generado ON tbl_contratos(contrato_generado);
CREATE INDEX idx_contrato_enviado ON tbl_contratos(contrato_enviado);
CREATE INDEX idx_fecha_generacion ON tbl_contratos(fecha_generacion_contrato);

-- PASO 4: Verificar estructura actualizada
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'tbl_contratos'
  AND COLUMN_NAME IN (
    'nombre_rep_legal_cliente',
    'cedula_rep_legal_cliente',
    'nombre_rep_legal_contratista',
    'nombre_responsable_sgsst',
    'valor_mensual',
    'contrato_generado',
    'ruta_pdf_contrato'
  )
ORDER BY ORDINAL_POSITION;

-- PASO 5: Ver resumen de contratos con nueva información
SELECT
    ct.id_contrato,
    ct.numero_contrato,
    c.nombre_cliente,
    ct.nombre_rep_legal_cliente,
    ct.email_cliente,
    ct.valor_contrato,
    ct.valor_mensual,
    ct.nombre_responsable_sgsst,
    ct.contrato_generado,
    ct.fecha_generacion_contrato
FROM tbl_contratos ct
INNER JOIN tbl_clientes c ON ct.id_cliente = c.id_cliente
LIMIT 5;
