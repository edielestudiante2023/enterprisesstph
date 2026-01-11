# Migración de Tabla tbl_cronog_capacitacion

## Objetivo
Agregar las columnas `nombre_capacitacion` y `objetivo_capacitacion` para soportar el nuevo sistema de capacitaciones basado en texto libre en lugar de referencias rígidas a la tabla `tbl_capacitaciones_sst`.

## Script SQL a Ejecutar

```sql
-- 1. Agregar columnas para nombre y objetivo de capacitación
ALTER TABLE tbl_cronog_capacitacion
ADD COLUMN nombre_capacitacion VARCHAR(255) AFTER id_capacitacion,
ADD COLUMN objetivo_capacitacion TEXT AFTER nombre_capacitacion;

-- 2. Migrar datos existentes: copiar nombres desde tbl_capacitaciones_sst
UPDATE tbl_cronog_capacitacion cc
INNER JOIN tbl_capacitaciones_sst cs ON cc.id_capacitacion = cs.id_capacitacion
SET cc.nombre_capacitacion = cs.capacitacion
WHERE cc.nombre_capacitacion IS NULL OR cc.nombre_capacitacion = '';

-- 3. Opcional: hacer id_capacitacion nullable para nuevos registros
ALTER TABLE tbl_cronog_capacitacion
MODIFY COLUMN id_capacitacion INT(11) NULL;
```

## Pasos para Ejecutar

### Opción 1: phpMyAdmin
1. Acceder a http://localhost/phpmyadmin
2. Seleccionar la base de datos
3. Ir a la pestaña "SQL"
4. Copiar y pegar el script completo
5. Hacer clic en "Continuar"

### Opción 2: Línea de Comandos MySQL
```bash
mysql -u root -p nombre_base_datos < migration_add_capacitacion_fields.sql
```

### Opción 3: Git Bash / Terminal
```bash
cd c:/xampp/htdocs/enterprisesstph
mysql -u root -p -e "USE nombre_base_datos; $(cat migration_add_capacitacion_fields.sql)"
```

## Estructura Resultante

Después de la migración, la tabla `tbl_cronog_capacitacion` tendrá:

```
Field                                      | Type         | Null | Key | Default | Extra
-------------------------------------------|--------------|------|-----|---------|----------------
id_cronograma_capacitacion                 | int(11)      | NO   | PRI |         | auto_increment
id_capacitacion                            | int(11)      | YES  |     |         | (mantiene compatibilidad)
nombre_capacitacion                        | varchar(255) | YES  |     |         | **NUEVO**
objetivo_capacitacion                      | text         | YES  |     |         | **NUEVO**
id_cliente                                 | int(11)      | YES  |     |         |
fecha_programada                           | date         | YES  |     |         |
fecha_de_realizacion                       | date         | YES  |     |         |
estado                                     | varchar(255) | YES  |     |         |
perfil_de_asistentes                       | varchar(255) | YES  |     |         |
nombre_del_capacitador                     | varchar(255) | YES  |     |         |
horas_de_duracion_de_la_capacitacion       | int(11)      | YES  |     |         |
indicador_de_realizacion_de_la_capacitacion| varchar(255) | YES  |     |         |
numero_de_asistentes_a_capacitacion        | int(11)      | YES  |     |         |
numero_total_de_personas_programadas       | int(11)      | YES  |     |         |
porcentaje_cobertura                       | varchar(255) | YES  |     |         |
numero_de_personas_evaluadas               | int(11)      | YES  |     |         |
promedio_de_calificaciones                 | decimal(5,2) | YES  |     |         |
observaciones                              | text         | YES  |     |         |
```

## Verificación Post-Migración

```sql
-- Verificar que las columnas se agregaron correctamente
DESCRIBE tbl_cronog_capacitacion;

-- Verificar que los datos se migraron
SELECT
    id_cronograma_capacitacion,
    id_capacitacion,
    nombre_capacitacion,
    objetivo_capacitacion
FROM tbl_cronog_capacitacion
LIMIT 10;

-- Contar registros migrados vs sin migrar
SELECT
    COUNT(*) as total,
    SUM(CASE WHEN nombre_capacitacion IS NOT NULL THEN 1 ELSE 0 END) as con_nombre,
    SUM(CASE WHEN nombre_capacitacion IS NULL THEN 1 ELSE 0 END) as sin_nombre
FROM tbl_cronog_capacitacion;
```

## Cambios en el Sistema

### Archivos Modificados
1. ✅ **CronogcapacitacionController.php** - Maneja `nombre_capacitacion` y `objetivo_capacitacion`
2. ✅ **add_cronograma.php** - Campos de texto libre
3. ✅ **edit_cronograma.php** - Campos de texto libre
4. ✅ **list_cronogramas.php** - Columna "Objetivo" oculta
5. ✅ **TrainingLibrary.php** - Genera capacitaciones con nombres desde CSV

### Sistema Antiguo vs Nuevo

#### Antes:
- Campo: `id_capacitacion` (INT)
- Referencia: `tbl_capacitaciones_sst`
- Limitación: Solo capacitaciones predefinidas

#### Después:
- Campos: `nombre_capacitacion` (VARCHAR), `objetivo_capacitacion` (TEXT)
- Texto libre: Cualquier capacitación
- Compatibilidad: `id_capacitacion` mantiene registros antiguos

## Rollback (Si es necesario)

```sql
-- ADVERTENCIA: Esto eliminará las nuevas columnas y sus datos
ALTER TABLE tbl_cronog_capacitacion
DROP COLUMN nombre_capacitacion,
DROP COLUMN objetivo_capacitacion;

-- Revertir id_capacitacion a NOT NULL si es necesario
ALTER TABLE tbl_cronog_capacitacion
MODIFY COLUMN id_capacitacion INT(11) NOT NULL;
```

## Notas Importantes

1. **Backup**: Hacer backup de la base de datos antes de ejecutar la migración
2. **Registros Existentes**: Los registros antiguos mantendrán su `id_capacitacion` y tendrán `nombre_capacitacion` copiado automáticamente
3. **Nuevos Registros**: Usarán `nombre_capacitacion` y `objetivo_capacitacion` (pueden tener `id_capacitacion` NULL)
4. **CSV Upload**: La funcionalidad de carga CSV se mantiene como respaldo para casos atípicos
