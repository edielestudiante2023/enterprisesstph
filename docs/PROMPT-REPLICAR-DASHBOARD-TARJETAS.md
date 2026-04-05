# Prompt para replicar rediseño de Dashboard con tarjetas agrupadas

Copia y pega este prompt en el otro proyecto.

---

## OBJETIVO

Rediseñar los dashboards de consultor y superadmin para reemplazar el layout actual (botones hardcodeados sueltos + DataTable con datos técnicos) por un diseño de **tarjetas agrupadas por categoría** con buscador en tiempo real. Toda la información de accesos debe vivir en la tabla `dashboard_items` de la base de datos, eliminando accesos hardcodeados en las vistas.

## CONTEXTO

En el proyecto hermano (`enterprisesstph`) ya se implementó este rediseño con éxito. Aquí necesitamos hacer lo mismo adaptado a los módulos y funcionalidades propias de este proyecto, que son más y diferentes.

## METODOLOGÍA — seguir estos pasos en orden

### Paso 1: Diagnóstico del estado actual

Revisar los dashboards actuales para entender qué hay:

1. **Identificar las rutas y controladores** del dashboard de consultor y superadmin en `app/Config/Routes.php`
2. **Leer los controladores** para ver qué modelo usan y qué query hacen a `dashboard_items`
3. **Leer las vistas** para identificar:
   - Qué botones están hardcodeados (accesos que NO vienen de la BD)
   - Qué muestra la tabla/DataTable (accesos que SÍ vienen de la BD)
   - Qué secciones adicionales existen (dashboards analíticos, modals, etc.)

### Paso 2: Inventario completo de accesos

1. **Consultar la tabla `dashboard_items`** en producción para ver qué registros existen actualmente
2. **Consultar la tabla `dashboard_items`** en local para comparar y detectar diferencias
3. **Listar TODOS los botones hardcodeados** de las vistas con su URL, nombre y función
4. **Presentarme el inventario completo** en una tabla: nombre del acceso, URL, origen (BD o hardcodeado)

### Paso 3: Igualar local con producción

Si hay diferencias entre local y producción en la tabla `dashboard_items`, igualar primero.

### Paso 4: Proponer agrupación por afinidad

Con el inventario completo, proponer categorías agrupadas por **afinidad funcional y frecuencia de uso**, no por módulo técnico. Preguntarme si hay módulos que no se usan o que debo excluir antes de implementar.

Presentarme las categorías propuestas con los accesos asignados a cada una para mi aprobación.

### Paso 5: Migración de la tabla `dashboard_items`

Crear un script PHP CLI en `app/SQL/` que:

1. **Agregue columnas nuevas** a `dashboard_items` (si no existen):
   - `categoria` (VARCHAR 100) — nombre de la categoría/grupo
   - `icono` (VARCHAR 100) — clase Font Awesome del icono
   - `color_gradiente` (VARCHAR 100) — dos colores hex separados por coma
   - `target_blank` (TINYINT 1, default 1) — si abre en nueva pestaña
   - `activo` (TINYINT 1, default 1) — para excluir sin borrar

2. **Asigne categorías** a todos los registros existentes según la agrupación aprobada
3. **Marque como inactivos** los módulos que yo indique que no se usan
4. **Inserte los accesos hardcodeados** que faltan en la tabla (verificando por `accion_url` para no duplicar)

Ejecutar: primero en LOCAL, luego en PRODUCCIÓN.

### Paso 6: Actualizar modelo, controladores y vistas

**Modelo `DashboardItemModel`:**
- Agregar los campos nuevos a `$allowedFields`

**Controladores** (tanto el del consultor como el del admin):
- Filtrar por `activo = 1`
- Ordenar por `categoria ASC, orden ASC`
- Agrupar los items por categoría en un array asociativo
- Definir un orden visual de las categorías (las más usadas primero)
- Pasar `$data['grouped']` a la vista

**Vistas** (dashboard del consultor y del admin):
- Eliminar todos los botones hardcodeados
- Eliminar la DataTable y sus dependencias (jQuery DataTables CSS/JS)
- Implementar el diseño de tarjetas agrupadas por categoría:
  - Buscador en tiempo real arriba
  - Secciones por categoría con header (icono + nombre + badge con conteo)
  - Grid CSS responsive de tarjetas (cada una con: icono con gradiente, nombre, descripción corta, borde izquierdo de color)
  - Para items cuya `accion_url` empiece con `#`, usar `data-bs-toggle="modal"` en vez de navegación
  - Mensaje "sin resultados" cuando el buscador no encuentra nada
- Conservar los modals existentes (como reset PHVA u otros) tal cual están
- Conservar el banner de bienvenida, header, footer y botón de logout

### Paso 7: Verificar accesos del superadmin

El superadmin puede tener accesos exclusivos que el consultor no ve (gestión de usuarios, herramientas de admin, etc.). Identificarlos y migrarlos también a la BD como registros separados.

## REGLAS

- Todo cambio en BD se hace via script PHP CLI, nunca manualmente
- Orden obligatorio: LOCAL primero, PRODUCCIÓN después
- No eliminar registros de la tabla, solo marcar `activo = 0` si hay que excluir
- Verificar duplicados por `accion_url` antes de insertar
- Presentarme el inventario y las categorías para aprobación ANTES de implementar
