# Sistema de Estándares Mínimos - Generación Automática

## Descripción General

Este sistema automatiza la generación de **Estándares Mínimos de Seguridad y Salud en el Trabajo (SST)** cuando se crea un nuevo cliente, eliminando la necesidad de cargar manualmente el mismo archivo CSV en cada ocasión.

## Cómo Funciona

### 1. Generación Automática al Crear Cliente

Cuando se crea un nuevo cliente a través del formulario de **Agregar Cliente** (`/addClient`), el sistema automáticamente:

1. Lee el archivo CSV maestro: `csvevaluacionestandaresminimosph.csv`
2. Prepara los estándares mínimos para el nuevo cliente
3. Reemplaza el `id_cliente` con el ID del cliente recién creado
4. Inserta todos los estándares en la tabla `evaluacion_inicial_sst`

**Resultado:** El cliente tiene todos sus estándares mínimos configurados desde el momento de su creación.

### 2. Archivo Maestro CSV

**Ubicación:** `c:\xampp\htdocs\enterprisesstph\csvevaluacionestandaresminimosph.csv`

**Características:**
- Contiene TODOS los estándares mínimos que aplican a cualquier cliente
- No varía por tipo de servicio (aplica igual para Mensual, Bimensual, Trimestral, Proyecto)
- Es una plantilla única y universal
- Se actualiza una sola vez cuando cambian los requisitos legales

**Columnas del CSV:**
```
id_cliente;ciclo;estandar;detalle_estandar;estandares_minimos;numeral;
numerales_del_cliente;siete;veintiun;sesenta;item_del_estandar;
evaluacion_inicial;valor;puntaje_cuantitativo;item;criterio;
modo_de_verificacion;calificacion;nivel_de_evaluacion;observaciones
```

### 3. Librería: StandardsLibrary.php

**Ubicación:** `app/Libraries/StandardsLibrary.php`

**Métodos principales:**

#### `getStandards(int $idCliente): array`
Obtiene todos los estándares del CSV preparados para un cliente específico.

**Parámetros:**
- `$idCliente`: ID del cliente al que se asignarán los estándares

**Retorna:**
Array de estándares listos para insertar en la base de datos.

**Ejemplo de uso:**
```php
$standardsLibrary = new StandardsLibrary();
$standards = $standardsLibrary->getStandards($clientId);

$evaluationModel = new SimpleEvaluationModel();
foreach ($standards as $standard) {
    $evaluationModel->insert($standard);
}
```

#### `getStandardsCount(): int`
Retorna el número total de estándares disponibles en el archivo maestro.

#### `csvFileExists(): bool`
Verifica si el archivo CSV maestro existe y es legible.

## Implementación Técnica

### Flujo de Creación de Cliente

**Archivo:** `app/Controllers/ConsultantController.php`

**Método:** `addClientPost()`

**Secuencia de generación automática:**
```
1. Guardar datos del cliente
   ↓
2. Crear carpeta de uploads
   ↓
3. Generar Plan de Trabajo Año 1
   ↓
4. Generar Cronograma de Capacitaciones
   ↓
5. Generar Estándares Mínimos ← NUEVO
   ↓
6. Redirigir con mensaje de éxito
```

**Código implementado:**
```php
// Generar automáticamente los Estándares Mínimos
try {
    $standardsLibrary = new StandardsLibrary();
    $standards = $standardsLibrary->getStandards($clientId);

    if (!empty($standards)) {
        $evaluationModel = new SimpleEvaluationModel();
        $insertedCount = 0;

        foreach ($standards as $standard) {
            if ($evaluationModel->insert($standard)) {
                $insertedCount++;
            }
        }

        log_message('info', "Estándares Mínimos generados para cliente ID {$clientId}: {$insertedCount} estándares");
    }
} catch (\Exception $e) {
    log_message('error', 'Error al generar Estándares Mínimos: ' . $e->getMessage());
}
```

### Modelo de Base de Datos

**Archivo:** `app/Models/SimpleEvaluationModel.php`

**Tabla:** `evaluacion_inicial_sst`

**Campos principales:**
- `id_ev_ini` (PK)
- `id_cliente` (FK)
- `ciclo` - Ciclo PHVA (Planear, Hacer, Verificar, Actuar)
- `estandar` - Nombre del estándar
- `detalle_estandar` - Descripción del estándar
- `estandares_minimos` - Texto del estándar mínimo
- `numeral` - Numeración del estándar
- `item_del_estandar` - Ítem específico
- `criterio` - Criterio de cumplimiento
- `modo_de_verificacion` - Cómo se verifica
- `calificacion` - Calificación inicial
- `observaciones` - Observaciones

## Carga Manual (Respaldo)

Para casos excepcionales donde sea necesario cargar estándares manualmente:

**URL:** `http://localhost/enterprisesstph/public/consultant/csvevaluacioninicial`

**Vista:** `app/Views/consultant/csvevaluacioninicial.php`

**Controlador:** `app/Controllers/CsvEvaluacionInicial.php`

**Método:** `upload()`

**Pasos:**
1. Seleccionar archivo CSV
2. Validar encabezados
3. Leer con PhpSpreadsheet
4. Insertar registros

**Nota:** Esta opción solo debe usarse en casos excepcionales, ya que la generación automática es suficiente para el 99% de los casos.

## Ventajas del Nuevo Sistema

### Antes (Sistema Manual)
- ❌ Cargar CSV manualmente para cada cliente nuevo
- ❌ Olvidar cargar los estándares
- ❌ Inconsistencias entre clientes
- ❌ Proceso repetitivo y propenso a errores
- ❌ Tiempo perdido: ~2-3 minutos por cliente

### Ahora (Sistema Automático)
- ✅ Generación automática al crear cliente
- ✅ Todos los clientes tienen estándares desde el inicio
- ✅ Consistencia total entre clientes
- ✅ Proceso automatizado y confiable
- ✅ Tiempo ahorrado: 100%

## Mantenimiento del Sistema

### Actualizar los Estándares Mínimos

Cuando cambian los requisitos legales o se actualizan los estándares:

1. **Editar el archivo CSV maestro:**
   - Ubicación: `csvevaluacionestandaresminimosph.csv`
   - Mantener el formato exacto (20 columnas separadas por `;`)
   - No incluir `id_cliente` real (se reemplaza automáticamente)

2. **Validar el archivo:**
   ```php
   $standardsLibrary = new StandardsLibrary();
   $count = $standardsLibrary->getStandardsCount();
   echo "Total de estándares: {$count}";
   ```

3. **Probar con un cliente de prueba:**
   - Crear un cliente de prueba
   - Verificar que se generaron los estándares
   - Revisar la tabla `evaluacion_inicial_sst`

4. **Los nuevos clientes usarán la versión actualizada automáticamente**

### Logs del Sistema

El sistema registra automáticamente:

```
INFO  - StandardsLibrary: Preparados 125 estándares para Cliente ID: 45
INFO  - Estándares Mínimos generados automáticamente para cliente ID 45: 125 estándares insertados
ERROR - Error al generar Estándares Mínimos automáticos: [mensaje de error]
```

**Ubicación de logs:** `writable/logs/`

## Comparación con Otros Módulos

### Plan de Trabajo
- Archivo CSV: `pta ph.csv`
- Filtro: Por AÑO (1 o 2) y TIPO DE SERVICIO (Mensual, Bimensual, etc.)
- Librería: `WorkPlanLibrary.php`
- Generación: Automática al crear cliente (Año 1)

### Cronograma de Capacitaciones
- Archivo CSV: `capacitaciones ph.csv`
- Filtro: Por TIPO DE SERVICIO (Mensual, Bimensual, etc.)
- Librería: `TrainingLibrary.php`
- Generación: Automática al crear cliente

### Estándares Mínimos ← NUEVO
- Archivo CSV: `csvevaluacionestandaresminimosph.csv`
- Filtro: **NINGUNO** (aplica igual para todos)
- Librería: `StandardsLibrary.php`
- Generación: Automática al crear cliente

## Troubleshooting

### Error: "Archivo CSV maestro no encontrado"

**Causa:** El archivo `csvevaluacionestandaresminimosph.csv` no está en la raíz del proyecto.

**Solución:**
```bash
# Verificar ubicación
ls c:/xampp/htdocs/enterprisesstph/csvevaluacionestandaresminimosph.csv

# Si no existe, copiar desde respaldo
cp backup/csvevaluacionestandaresminimosph.csv ./
```

### Error: "No se insertaron estándares"

**Causa:** Problemas en el modelo o campos del CSV.

**Solución:**
1. Verificar logs en `writable/logs/`
2. Revisar `$allowedFields` en `SimpleEvaluationModel.php`
3. Verificar que el CSV tenga 20 columnas
4. Validar formato del CSV (separador `;`)

### Los estándares no aparecen para el cliente

**Causa:** La generación falló o se deshabilitó el bloque try-catch.

**Solución:**
```sql
-- Verificar si existen estándares para el cliente
SELECT COUNT(*) as total
FROM evaluacion_inicial_sst
WHERE id_cliente = 45;

-- Si no hay registros, generar manualmente:
-- Usar la vista /consultant/csvevaluacioninicial
```

## Archivos Modificados

### Nuevos Archivos
- ✅ `app/Libraries/StandardsLibrary.php` - Librería para leer CSV
- ✅ `SISTEMA_ESTANDARES_MINIMOS.md` - Documentación

### Archivos Modificados
- ✅ `app/Controllers/ConsultantController.php` - Agregada generación automática
- ✅ `app/Controllers/CsvEvaluacionInicial.php` - Agregado método `generateForClient()`
- ✅ `app/Views/consultant/csvevaluacioninicial.php` - Agregada info de generación automática

### Archivos Existentes (No modificados)
- `app/Models/SimpleEvaluationModel.php` - Modelo existente
- `csvevaluacionestandaresminimosph.csv` - CSV maestro existente

## Conclusión

El sistema de **Estándares Mínimos** ahora funciona exactamente igual que el **Plan de Trabajo** y el **Cronograma de Capacitaciones**: se generan automáticamente al crear un cliente, leyendo desde un archivo CSV maestro a través de una librería dedicada.

**Resultado:** Ya no es necesario cargar manualmente el archivo CSV cada vez que se crea un cliente. El sistema es más eficiente, consistente y menos propenso a errores humanos.

---

**Fecha de implementación:** 2026-01-11

**Desarrollado por:** Claude Code (Sonnet 4.5)
