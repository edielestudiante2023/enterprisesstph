# Guía de Migración Segura - Tabla de Capacitaciones

## Estrategia de Migración

Esta migración utiliza la **técnica de tabla nueva + renombrado** para actualizar la estructura sin perder datos:

1. ✅ Crear tabla nueva `capacitaciones_nuevo` con estructura correcta (incluye `nombre_capacitacion` y `objetivo_capacitacion`)
2. ✅ Copiar datos haciendo **LEFT JOIN** entre `tbl_cronog_capacitacion` y `capacitaciones_sst` para obtener nombres y objetivos
3. ✅ Verificar que los datos se copiaron correctamente
4. ✅ Renombrar `tbl_cronog_capacitacion` → `tbl_cronog_capacitacion_old` (backup)
5. ✅ Renombrar `capacitaciones_nuevo` → `tbl_cronog_capacitacion` (nueva tabla principal)

**Ventajas:**
- ✅ Cero pérdida de datos
- ✅ Migra automáticamente los nombres desde `capacitaciones_sst`
- ✅ Rollback instantáneo si algo falla
- ✅ Backup automático de la tabla antigua
- ✅ Proceso reversible en cualquier momento

**Tablas Involucradas:**
- `tbl_cronog_capacitacion` - Tabla actual (solo tiene `id_capacitacion`)
- `capacitaciones_sst` - Tabla de referencia (tiene `capacitacion` y `objetivo_capacitacion`)
- `capacitaciones_nuevo` - Tabla nueva (tendrá `nombre_capacitacion` y `objetivo_capacitacion` como texto)

---

## PASO 1: Backup de la Base de Datos

**CRÍTICO**: Antes de ejecutar cualquier comando SQL, hacer backup completo.

### Opción A: phpMyAdmin
1. Ir a http://localhost/phpmyadmin
2. Seleccionar la base de datos
3. Pestaña "Exportar"
4. Método: "Rápido"
5. Formato: "SQL"
6. Clic en "Continuar"
7. Guardar el archivo `.sql` en un lugar seguro

### Opción B: Línea de Comandos
```bash
mysqldump -u root -p nombre_base_datos > backup_capacitaciones_$(date +%Y%m%d_%H%M%S).sql
```

---

## PASO 2: Ejecutar Script de Creación y Copia

### Opción A: phpMyAdmin (RECOMENDADO)

1. Abrir http://localhost/phpmyadmin
2. Seleccionar la base de datos
3. Pestaña "SQL"
4. Copiar y pegar **TODO EL CONTENIDO** de `migration_safe_rename.sql`
5. Clic en "Continuar"

**Resultado esperado:**
- Tabla `capacitaciones_nuevo` creada
- Datos copiados desde `tbl_cronog_capacitacion`
- Query de verificación muestra:
  ```
  Registros en tabla original | total_registros
  Registros en tabla nueva    | total_registros (debe ser igual)
  ```

### Opción B: MySQL Command Line

```bash
cd c:/xampp/htdocs/enterprisesstph
mysql -u root -p nombre_base_datos < migration_safe_rename.sql
```

---

## PASO 3: Verificar la Copia de Datos

**CRÍTICO**: NO continuar sin verificar que los datos se copiaron correctamente.

### Query de Verificación Completa

```sql
-- Contar registros
SELECT 'Original' as tabla, COUNT(*) as total FROM tbl_cronog_capacitacion
UNION ALL
SELECT 'Nueva' as tabla, COUNT(*) as total FROM capacitaciones_nuevo;

-- Verificar registros con nombre_capacitacion
SELECT
    'Original' as tabla,
    COUNT(*) as total,
    SUM(CASE WHEN nombre_capacitacion IS NOT NULL AND nombre_capacitacion != '' THEN 1 ELSE 0 END) as con_nombre
FROM tbl_cronog_capacitacion
UNION ALL
SELECT
    'Nueva' as tabla,
    COUNT(*) as total,
    SUM(CASE WHEN nombre_capacitacion IS NOT NULL AND nombre_capacitacion != '' THEN 1 ELSE 0 END) as con_nombre
FROM capacitaciones_nuevo;

-- Comparar muestra de 5 registros
SELECT 'ORIGINAL' as fuente, * FROM tbl_cronog_capacitacion LIMIT 5;
SELECT 'NUEVA' as fuente, * FROM capacitaciones_nuevo LIMIT 5;
```

**✅ Solo continuar si:**
- El número total de registros es IGUAL en ambas tablas
- Los registros con `nombre_capacitacion` son IGUALES
- La muestra de datos se ve correcta

---

## PASO 4: Renombrar Tablas (CRÍTICO)

**ADVERTENCIA**: Este paso cambiará el sistema. Solo ejecutar después de verificar PASO 3.

### Script de Renombrado

```sql
-- Renombrar tabla antigua como backup
RENAME TABLE tbl_cronog_capacitacion TO tbl_cronog_capacitacion_old;

-- Renombrar tabla nueva como principal
RENAME TABLE capacitaciones_nuevo TO tbl_cronog_capacitacion;
```

### Ejecutar en phpMyAdmin

1. Ir a la pestaña "SQL"
2. Copiar y pegar el script anterior
3. Clic en "Continuar"

**Resultado esperado:**
- Tabla `tbl_cronog_capacitacion` ahora tiene la nueva estructura
- Tabla `tbl_cronog_capacitacion_old` contiene el backup

---

## PASO 5: Verificación Final del Sistema

### A. Verificar Estructura de la Tabla

```sql
DESCRIBE tbl_cronog_capacitacion;
```

**Verificar que existan:**
- ✅ `nombre_capacitacion` VARCHAR(255) NULL
- ✅ `objetivo_capacitacion` TEXT NULL
- ✅ `id_capacitacion` INT(11) NULL

### B. Verificar Datos

```sql
-- Contar registros totales
SELECT COUNT(*) as total FROM tbl_cronog_capacitacion;

-- Ver registros con nombres
SELECT
    id_cronograma_capacitacion,
    nombre_capacitacion,
    objetivo_capacitacion,
    estado,
    nombre_del_capacitador
FROM tbl_cronog_capacitacion
LIMIT 10;
```

### C. Probar la Vista Web

1. Abrir http://localhost/enterprisesstph/public/listcronogCapacitacion
2. Verificar que los registros se muestran correctamente
3. **NO** debe aparecer "Capacitación no encontrada"
4. Los nombres de capacitaciones deben mostrarse correctamente

### D. Probar Agregar Nueva Capacitación

1. Ir a http://localhost/enterprisesstph/public/addcronogCapacitacion
2. Llenar el formulario con:
   - Nombre: "Prueba de Migración"
   - Objetivo: "Verificar que el sistema funciona"
   - Cliente: (seleccionar cualquiera)
   - Estado: PROGRAMADA
3. Guardar
4. Verificar que aparece en el listado

### E. Probar Editar Capacitación

1. Desde el listado, editar una capacitación existente
2. Verificar que el nombre y objetivo se muestran correctamente
3. Hacer un cambio y guardar
4. Verificar que los cambios se guardaron

---

## PASO 6: Generar Cronograma Automático (Opcional)

Probar la funcionalidad de generación automática desde TrainingLibrary:

1. Ir al listado de capacitaciones
2. Clic en botón "Cargar Cronograma"
3. Seleccionar:
   - Cliente
   - Tipo de servicio
4. Generar
5. Verificar que las nuevas capacitaciones tienen `nombre_capacitacion` lleno

---

## Rollback - Si Algo Sale Mal

Si después del PASO 4 algo no funciona correctamente, ejecutar inmediatamente:

```sql
-- Restaurar el sistema original
RENAME TABLE tbl_cronog_capacitacion TO capacitaciones_fallida;
RENAME TABLE tbl_cronog_capacitacion_old TO tbl_cronog_capacitacion;

-- Opcional: eliminar la tabla fallida después de verificar
-- DROP TABLE capacitaciones_fallida;
```

**Resultado:**
- Sistema vuelve al estado original
- Ningún dato se pierde

---

## Limpieza Post-Migración (Después de 1 semana)

Una vez verificado que el sistema funciona correctamente durante 1 semana:

```sql
-- SOLO ejecutar si estás 100% seguro que todo funciona

-- Ver cuántos registros tiene el backup
SELECT COUNT(*) FROM tbl_cronog_capacitacion_old;

-- Opcional: Exportar el backup a archivo SQL antes de eliminar
-- (Hacerlo desde phpMyAdmin > Exportar)

-- Eliminar tabla antigua (IRREVERSIBLE)
-- DROP TABLE tbl_cronog_capacitacion_old;
```

---

## Troubleshooting

### Error: "Table 'capacitaciones_nuevo' already exists"

**Solución:**
```sql
DROP TABLE capacitaciones_nuevo;
-- Luego volver a ejecutar el PASO 2
```

### Error: "Unknown column 'nombre_capacitacion' in 'field list'"

**Causa:** La tabla original no tiene las columnas nuevas.

**Solución:**
```sql
-- Agregar las columnas a la tabla original primero
ALTER TABLE tbl_cronog_capacitacion
ADD COLUMN IF NOT EXISTS nombre_capacitacion VARCHAR(255) AFTER id_capacitacion,
ADD COLUMN IF NOT EXISTS objetivo_capacitacion TEXT AFTER nombre_capacitacion;

-- Luego volver a ejecutar el PASO 2
```

### Vista muestra registros vacíos o "Capacitación no encontrada"

**Causa:** Los datos antiguos pueden tener `nombre_capacitacion` NULL.

**Solución:** Ejecutar después del PASO 4:
```sql
-- Poner texto por defecto en registros antiguos sin nombre
UPDATE tbl_cronog_capacitacion
SET nombre_capacitacion = CONCAT('Capacitación ID ', id_cronograma_capacitacion)
WHERE (nombre_capacitacion IS NULL OR nombre_capacitacion = '')
  AND id_capacitacion IS NOT NULL;
```

---

## Resumen de Comandos Rápidos

```sql
-- 1. Crear tabla nueva y copiar datos
-- (Ejecutar todo el archivo migration_safe_rename.sql)

-- 2. Verificar
SELECT 'Original' as t, COUNT(*) as n FROM tbl_cronog_capacitacion
UNION ALL
SELECT 'Nueva' as t, COUNT(*) as n FROM capacitaciones_nuevo;

-- 3. Renombrar
RENAME TABLE tbl_cronog_capacitacion TO tbl_cronog_capacitacion_old;
RENAME TABLE capacitaciones_nuevo TO tbl_cronog_capacitacion;

-- 4. Verificar final
DESCRIBE tbl_cronog_capacitacion;
SELECT * FROM tbl_cronog_capacitacion LIMIT 5;

-- 5. Rollback (si es necesario)
RENAME TABLE tbl_cronog_capacitacion TO capacitaciones_fallida;
RENAME TABLE tbl_cronog_capacitacion_old TO tbl_cronog_capacitacion;
```

---

## Checklist de Migración

- [ ] Backup completo de la base de datos
- [ ] Ejecutar script `migration_safe_rename.sql`
- [ ] Verificar conteo de registros (Original = Nueva)
- [ ] Verificar muestra de datos
- [ ] Renombrar tablas
- [ ] Verificar estructura con DESCRIBE
- [ ] Probar vista web listcronogCapacitacion
- [ ] Probar agregar nueva capacitación
- [ ] Probar editar capacitación existente
- [ ] Probar generación automática desde TrainingLibrary
- [ ] Sistema funcionando correctamente por 1 semana
- [ ] Eliminar tabla backup (opcional)

---

## Contacto de Soporte

Si tienes dudas durante la migración, detén el proceso y consulta antes de continuar.
