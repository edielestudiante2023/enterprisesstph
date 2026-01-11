-- Agregar columnas para nombre y objetivo de capacitación
ALTER TABLE tbl_cronog_capacitacion 
ADD COLUMN nombre_capacitacion VARCHAR(255) AFTER id_capacitacion,
ADD COLUMN objetivo_capacitacion TEXT AFTER nombre_capacitacion;

-- Migrar datos existentes: copiar nombres desde tbl_capacitaciones_sst
UPDATE tbl_cronog_capacitacion cc
INNER JOIN tbl_capacitaciones_sst cs ON cc.id_capacitacion = cs.id_capacitacion
SET cc.nombre_capacitacion = cs.capacitacion
WHERE cc.nombre_capacitacion IS NULL OR cc.nombre_capacitacion = '';

-- Opcional: hacer id_capacitacion nullable para nuevos registros
ALTER TABLE tbl_cronog_capacitacion 
MODIFY COLUMN id_capacitacion INT(11) NULL;
