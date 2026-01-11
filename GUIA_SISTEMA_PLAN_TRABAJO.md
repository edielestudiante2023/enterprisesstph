# Guía del Sistema de Plan de Trabajo Automatizado

## Resumen

Se ha implementado un sistema automatizado para la gestión de Planes de Trabajo Anual (PTA) que elimina el riesgo de errores al cargar plantillas incorrectas.

## Componentes Implementados

### 1. WorkPlanLibrary (app/Libraries/WorkPlanLibrary.php)

Librería que gestiona las plantillas de planes de trabajo según:
- **Año del SGSST**: 1, 2 o 3
- **Tipo de Servicio**: Mensual, Bimensual, Trimestral o Proyecto

**Características:**
- Lee el archivo CSV maestro `PTA2026.csv`
- Filtra actividades según combinación de año + tipo de servicio
- Inserta actividades con valores por defecto:
  - `responsable_sugerido_plandetrabajo`: "CONSULTOR CYCLOID"
  - `fecha_propuesta`: Fecha actual
  - `estado_actividad`: "ABIERTA"
  - `porcentaje_avance`: 0

### 2. Creación Automática al Agregar Cliente Nuevo

**Ubicación**: ConsultantController::addClientPost()

**Flujo:**
1. Consultor crea un cliente nuevo
2. Selecciona el "Tipo de Servicio" en el formulario (Mensual/Bimensual/Trimestral/Proyecto)
3. El sistema automáticamente:
   - Crea el cliente
   - Genera el Plan de Trabajo del **Año 1**
   - Inserta todas las actividades correspondientes

**Ventaja:** El analista no tiene que subir ningún CSV para clientes nuevos.

### 3. Renovación de Plan de Trabajo (Año 2 y 3)

**Ubicación**: Listado de PTA del cliente (list_pta_cliente_nueva.php)

**Funcionalidad:**
- Botón **"Renovar Plan de Trabajo"** en la vista de listado del plan de trabajo
- Modal para seleccionar:
  - Cliente
  - Año del SGSST (1, 2 o 3)
  - Tipo de Servicio

**Flujo:**
1. Consultor hace clic en "Renovar Plan de Trabajo"
2. Selecciona el cliente que pasará al siguiente año
3. Selecciona el año (normalmente 2 o 3)
4. Confirma el tipo de servicio actual del cliente
5. El sistema inserta las actividades del nuevo año

**Importante:**
- Las actividades del año anterior **NO se borran**
- Las actividades conviven en el sistema por todos los años del cliente
- Permite trazabilidad histórica completa

### 4. Carga Manual CSV (Casos Atípicos)

**Ubicación**: consultant/plan

**Cuándo usar:**
- Clientes con actividades personalizadas
- Casos especiales que no siguen las plantillas estándar
- Ajustes específicos requeridos por el cliente

**Funcionalidad:**
- Se mantiene la opción de subir archivos CSV/Excel
- Mismo flujo anterior: seleccionar archivo y cargar

## Archivo Maestro CSV

**Ubicación**: `C:\xampp\htdocs\enterprisesstph\PTA2026.csv`

**Estructura:**
```
phva_plandetrabajo;numeral_plandetrabajo;actividad_plandetrabajo;CLIENTE AÑO 1;CLIENTE AÑO 2;CLIENTE AÑO 3;MENSUAL;BIMENSUAL;TRIMESTRAL;PROYECTO;RESPONSABLE
```

**Marcadores:**
- Columnas 4-6: Año (CLIENTE AÑO 1, 2, 3)
- Columnas 7-10: Tipo de Servicio (MENSUAL, BIMENSUAL, TRIMESTRAL, PROYECTO)
- Marcador "x": Indica que la actividad aplica para esa combinación

**Ejemplo:**
```
HACER;5.1.2;Asesorar simulacro;x;x;x;x;x;x;;CONSULTOR CYCLOID
```
Esta actividad aplica para:
- Años: 1, 2 y 3
- Tipos de servicio: Mensual, Bimensual, Trimestral
- NO aplica para: Proyecto

## Combinaciones Posibles

Total: **12 combinaciones**

| Año | Mensual | Bimensual | Trimestral | Proyecto |
|-----|---------|-----------|------------|----------|
| 1   | ✓       | ✓         | ✓          | ✓        |
| 2   | ✓       | ✓         | ✓          | ✓        |
| 3   | ✓       | ✓         | ✓          | ✓        |

## Rutas Agregadas

```php
// Obtener lista de clientes para el modal
$routes->get('consultant/plan/getClients', 'PlanController::getClients');

// Generar plan de trabajo automáticamente
$routes->post('consultant/plan/generate', 'PlanController::generate');
```

## Tabla de Base de Datos

**Tabla**: `tbl_pta_cliente`

**Campos relevantes:**
- `id_cliente`: ID del cliente
- `phva_plandetrabajo`: Fase PHVA (PLANEAR, HACER, VERIFICAR, ACTUAR)
- `numeral_plandetrabajo`: Numeral del estándar
- `actividad_plandetrabajo`: Descripción de la actividad
- `responsable_sugerido_plandetrabajo`: Responsable (siempre "CONSULTOR CYCLOID")
- `fecha_propuesta`: Fecha sugerida de ejecución (se asigna fecha actual)
- `estado_actividad`: Estado (ABIERTA, EN PROCESO, CERRADA, etc.)
- `porcentaje_avance`: Porcentaje de completitud (0-100)

## Ventajas del Sistema

1. **Eliminación de errores humanos**: No hay riesgo de subir la plantilla incorrecta
2. **Automatización**: Creación automática al agregar cliente nuevo
3. **Flexibilidad**: Opción manual para casos atípicos
4. **Trazabilidad**: Historial completo de actividades por año
5. **Mantenimiento centralizado**: Un solo archivo CSV maestro
6. **Escalabilidad**: Fácil agregar más combinaciones si es necesario

## Mantenimiento del Sistema

### Para actualizar las plantillas:

1. Editar el archivo `PTA2026.csv`
2. Agregar/modificar/eliminar actividades
3. Marcar con "x" las combinaciones donde aplica cada actividad
4. Guardar el archivo
5. Los cambios se aplican inmediatamente (la librería lee el CSV en cada ejecución)

### Para agregar un nuevo tipo de servicio:

1. Agregar columna en `PTA2026.csv`
2. Actualizar `WorkPlanLibrary::SERVICE_TYPE_COLUMNS`
3. Actualizar `WorkPlanLibrary::getServiceTypes()`
4. Actualizar los selectores en las vistas

### Para agregar Año 4, 5, etc.:

1. Agregar columna en `PTA2026.csv`
2. Actualizar `WorkPlanLibrary::YEAR_COLUMNS`
3. Actualizar `WorkPlanLibrary::getAvailableYears()`
4. Actualizar los selectores en las vistas

## Flujo de Trabajo Recomendado

### Cliente Nuevo (Año 1):
1. Consultor crea el cliente desde `/addClient`
2. Selecciona el tipo de servicio
3. Guarda el cliente
4. ✅ El plan Año 1 se genera automáticamente

### Cliente pasa al Año 2:
1. Consultor va a `consultant/plan`
2. Clic en "Renovar Plan de Trabajo"
3. Selecciona el cliente
4. Selecciona "Año 2"
5. Confirma el tipo de servicio
6. ✅ Se generan las actividades del Año 2

### Cliente pasa al Año 3:
1. Mismo proceso que Año 2
2. Selecciona "Año 3"

### Caso Atípico:
1. Consultor va a `consultant/plan`
2. Usa el formulario tradicional de carga CSV
3. Sube el archivo personalizado

## Notas Importantes

1. **Fecha Propuesta**: Se asigna la fecha del día actual porque el consultor conoce las prioridades reales del cliente (ej: simulacro en octubre por mandato nacional).

2. **Responsable**: Siempre es "CONSULTOR CYCLOID" según las plantillas estándar.

3. **Convivencia de Años**: Las actividades de todos los años permanecen en el sistema para mantener trazabilidad histórica.

4. **Ecosistema**: Esta funcionalidad es exclusiva del **consultor**. El cliente solo tiene acceso de consulta, no puede gestionar planes de trabajo.

## Soporte y Mantenimiento

**Archivos clave:**
- `app/Libraries/WorkPlanLibrary.php` - Librería principal
- `app/Controllers/PlanController.php` - Controlador de planes
- `app/Controllers/ConsultantController.php` - Creación de clientes
- `app/Views/consultant/cargarplandetrabjo.php` - Vista con modal
- `PTA2026.csv` - Archivo maestro de plantillas

**Logs:**
- Éxitos y errores se registran en el sistema de logs de CodeIgniter
- Ubicación: `writable/logs/`
