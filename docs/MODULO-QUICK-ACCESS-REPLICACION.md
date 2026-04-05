# MÓDULO QUICK ACCESS — Documento de Replicación Completa

> **Fecha de extracción:** 2026-04-02
> **Proyecto origen:** enterprisesstph (CodeIgniter 4 + MySQL)
> **URL del módulo:** `/quick-access` y `/consultor/dashboard`

---

## ÍNDICE

1. [Inventario de Archivos](#1-inventario-de-archivos)
2. [Rutas del Aplicativo](#2-rutas-del-aplicativo)
3. [Estructura de Base de Datos](#3-estructura-de-base-de-datos)
4. [Flujo Funcional](#4-flujo-funcional)
5. [Dependencias Externas](#5-dependencias-externas)
6. [Patrones Especiales](#6-patrones-especiales)
7. [Orden de Implementación](#7-orden-de-implementación)

---

## 1. INVENTARIO DE ARCHIVOS

### 1.1 Controladores

| # | Ruta | Líneas | Propósito |
|---|------|--------|-----------|
| 1 | `app/Controllers/QuickAccessDashboardController.php` | 21 | Dashboard de acceso rápido: carga clientes y renderiza vista con selector global + apertura masiva de pestañas |
| 2 | `app/Controllers/ConsultorTablaItemsController.php` | 58 | Dashboard principal del consultor: lee `dashboard_items` activos, agrupa por categoría y renderiza tarjetas |
| 3 | `app/Controllers/AdminlistdashboardController.php` | 54 | CRUD administrativo de `dashboard_items` (listar, agregar, editar, eliminar) |
| 4 | `app/Controllers/AccesossegunclienteController.php` | 76 | CRUD de la tabla `accesos` (ítems de menú SST por URL) |
| 5 | `app/Controllers/AccesosseguncontractualidadController.php` | 80 | CRUD de la tabla pivote `estandares_accesos` (relación estándar↔acceso) |

### 1.2 Modelos

| # | Ruta | Tabla | PK | Campos permitidos |
|---|------|-------|----|-------------------|
| 1 | `app/Models/DashboardItemModel.php` | `dashboard_items` | `id` | `rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo` |
| 2 | `app/Models/AccesoModel.php` | `accesos` | `id_acceso` | `nombre, url, dimension` |
| 3 | `app/Models/EstandarAccesoModel.php` | `estandares_accesos` | `id` | `id_estandar, id_acceso` |
| 4 | `app/Models/EstandarModel.php` | `estandares` | `id_estandar` | `nombre` |
| 5 | `app/Models/ClientModel.php` | `tbl_clientes` | `id_cliente` | (múltiples campos — modelo del sistema) |
| 6 | `app/Models/UrlModel.php` | `tbl_urls` | `id` | `tipo, nombre, url` (con timestamps) |

### 1.3 Vistas

| # | Ruta | Líneas | Propósito |
|---|------|--------|-----------|
| 1 | `app/Views/consultant/quick_access_dashboard.php` | 380 | Dashboard de acceso rápido: selector de cliente global con Select2, apertura masiva de 6 vistas en pestañas nuevas, sincronización via BroadcastChannel |
| 2 | `app/Views/consultant/dashboard.php` | 766 | Dashboard principal del consultor: tarjetas agrupadas por categoría con buscador en tiempo real, modal de reseteo PHVA, banner PWA móvil |
| 3 | `app/Views/consultant/listitemdashboard.php` | 131 | Tabla DataTables para administrar dashboard_items (CRUD) |
| 4 | `app/Views/consultant/additemdashboard.php` | 42 | Formulario para agregar un dashboard_item |
| 5 | `app/Views/consultant/listaccesosseguncliente.php` | 249 | Tabla DataTables de accesos con filtros y exportación Excel |
| 6 | `app/Views/consultant/addaccesosseguncliente.php` | 68 | Formulario para agregar un acceso |
| 7 | `app/Views/consultant/editaccesosseguncliente.php` | 59 | Formulario para editar un acceso |
| 8 | `app/Views/consultant/listaccesosseguncontractualidad.php` | 126 | Tabla DataTables de relaciones estándar↔acceso |
| 9 | `app/Views/consultant/addaccesosseguncontractualidad.php` | 64 | Formulario con Select2 para vincular estándar con acceso |
| 10 | `app/Views/consultant/editaccesosseguncontractualidad.php` | 50 | Formulario para editar relación estándar↔acceso |

### 1.4 Vistas con integración BroadcastChannel (receptores)

Estas vistas **no son parte del módulo** pero reciben la sincronización de cliente desde Quick Access:

| # | Ruta | Propósito de la integración |
|---|------|-----------------------------|
| 1 | `app/Views/consultant/report_list.php` | Cambia filtro de cliente en lista de reportes |
| 2 | `app/Views/consultant/list_cronogramas.php` | Cambia cliente en cronogramas de capacitación |
| 3 | `app/Views/consultant/list_pendientes.php` | Cambia cliente en lista de pendientes |
| 4 | `app/Views/consultant/list_pta_cliente_nueva.php` | Cambia cliente en plan de trabajo anual |
| 5 | `app/Views/consultant/list_evaluaciones.php` | Cambia cliente en evaluaciones de estándares |
| 6 | `app/Views/consultant/vencimientos/listVencimientosMantenimiento.php` | Cambia cliente en vencimientos |

### 1.5 Migraciones SQL

| # | Ruta | Propósito |
|---|------|-----------|
| 1 | `app/SQL/migrate_dashboard_categorias.php` | Agrega columnas `categoria, icono, color_gradiente, target_blank, activo` a `dashboard_items`, asigna categorías, inserta ítems hardcodeados |

### 1.6 JavaScript / CSS

No hay archivos JS o CSS separados. Todo el estilo y lógica está **embebido** en las vistas PHP.

---

## 2. RUTAS DEL APLICATIVO

### 2.1 Vistas (GET)

| Método | URL | Controlador::Método | Descripción |
|--------|-----|---------------------|-------------|
| GET | `/quick-access` | `QuickAccessDashboardController::index` | Dashboard de acceso rápido con selector global de cliente |
| GET | `/consultor/dashboard` | `ConsultorTablaItemsController::index` | Dashboard principal del consultor con tarjetas por categoría |
| GET | `/consultant/listitemdashboard` | `AdminlistdashboardController::listitemdashboard` | Lista administrativa de dashboard_items |
| GET | `/consultant/additemdashboard` | `AdminlistdashboardController::additemdashboard` | Formulario agregar dashboard_item |
| GET | `/consultant/edititemdashboar/(:num)` | `AdminlistdashboardController::edititemdashboar/$1` | Formulario editar dashboard_item |
| GET | `/consultant/deleteitemdashboard/(:num)` | `AdminlistdashboardController::deleteitemdashboard/$1` | Eliminar dashboard_item |
| GET | `/accesosseguncliente/list` | `AccesossegunclienteController::listaccesosseguncliente` | Lista de accesos |
| GET | `/accesosseguncliente/add` | `AccesossegunclienteController::addaccesosseguncliente` | Formulario agregar acceso |
| GET | `/accesosseguncliente/edit/(:num)` | `AccesossegunclienteController::editaccesosseguncliente/$1` | Formulario editar acceso |
| GET | `/accesosseguncliente/delete/(:num)` | `AccesossegunclienteController::deleteaccesosseguncliente/$1` | Eliminar acceso |
| GET | `/accesosseguncontractualidad/list` | `AccesosseguncontractualidadController::listaccesosseguncontractualidad` | Lista relaciones estándar↔acceso |
| GET | `/accesosseguncontractualidad/add` | `AccesosseguncontractualidadController::addaccesosseguncontractualidad` | Formulario vincular estándar↔acceso |
| GET | `/accesosseguncontractualidad/edit/(:num)` | `AccesosseguncontractualidadController::editaccesosseguncontractualidad/$1` | Editar relación estándar↔acceso |
| GET | `/accesosseguncontractualidad/delete/(:num)` | `AccesosseguncontractualidadController::deleteaccesosseguncontractualidad/$1` | Eliminar relación estándar↔acceso |

### 2.2 AJAX/API (POST)

| Método | URL | Controlador::Método | Descripción |
|--------|-----|---------------------|-------------|
| POST | `/consultant/additemdashboardpost` | `AdminlistdashboardController::additemdashboardpost` | Guarda nuevo dashboard_item |
| POST | `/consultant/editpostitemdashboar/(:num)` | `AdminlistdashboardController::editpostitemdashboar/$1` | Actualiza dashboard_item |
| POST | `/accesosseguncliente/add` | `AccesossegunclienteController::addpostaccesosseguncliente` | Guarda nuevo acceso |
| POST | `/accesosseguncliente/edit` | `AccesossegunclienteController::editpostaccesosseguncliente` | Actualiza acceso |
| POST | `/accesosseguncontractualidad/add` | `AccesosseguncontractualidadController::addpostaccesosseguncontractualidad` | Guarda nueva relación |
| POST | `/accesosseguncontractualidad/edit` | `AccesosseguncontractualidadController::editpostaccesosseguncontractualidad` | Actualiza relación |
| GET | `/api/getClientesParaReseteo` | `EvaluationController::getClientesParaReseteo` | Obtiene lista de clientes para modal de reseteo PHVA |
| POST | `/api/resetCicloPHVA` | `EvaluationController::resetCicloPHVA` | Ejecuta reseteo de evaluaciones PHVA de un cliente |

### 2.3 Filtros de autenticación

Todos los patrones del módulo requieren autenticación via `AuthFilter`:

```php
// app/Config/Filters.php — 'before' array
'quick-access*',
'consultant/*',
'consultor/*',
'accesosseguncliente/*',
'accesosseguncontractualidad/*',
'api/*',
```

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tablas PROPIAS del módulo

#### Tabla `dashboard_items` (principal del módulo)

```sql
CREATE TABLE `dashboard_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(50) DEFAULT NULL,
  `tipo_proceso` varchar(50) DEFAULT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `accion_url` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `color_gradiente` varchar(100) DEFAULT NULL,
  `target_blank` tinyint(1) DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` datetime DEFAULT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Significado de cada campo:**

| Campo | Descripción |
|-------|-------------|
| `rol` | Rol del usuario que ve este ítem (ej: "Consultor") |
| `tipo_proceso` | Agrupación legacy (ej: "IA y Asistencia", "Gestión Clientes") |
| `detalle` | **Título visible** de la tarjeta en el dashboard |
| `descripcion` | Subtítulo/descripción corta debajo del título |
| `accion_url` | URL relativa a `base_url()` o ID de modal (ej: `#resetPHVAModal`) |
| `orden` | Orden numérico dentro de su categoría |
| `categoria` | Nombre de la categoría para agrupación visual |
| `icono` | Clase CSS FontAwesome (ej: `fas fa-robot`) |
| `color_gradiente` | Dos colores hex separados por coma (ej: `#4facfe,#00f2fe`) |
| `target_blank` | 1 = abrir en nueva pestaña, 0 = misma pestaña |
| `activo` | 1 = visible en dashboard, 0 = oculto |

#### Datos semilla `dashboard_items` (52 registros activos)

```sql
-- Categorías y su contenido (orden de visualización en el dashboard):
-- 1. IA y Asistencia (2 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'IA y Asistencia', 'Otto - Asistente IA', 'Chat con el asistente inteligente Otto', '/consultant/chat', 1, 'IA y Asistencia', 'fas fa-robot', '#4facfe,#00f2fe', 0, 1),
('Consultor', 'IA y Asistencia', 'Monitor Otto', 'Monitoreo de conversaciones y logs de Otto', '/otto-logs', 2, 'IA y Asistencia', 'fas fa-desktop', '#1c2437,#2d3a52', 1, 1);

-- 2. Operación Diaria (6 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'Operación Diaria', 'Cronogramas Capacitación', 'Gestión cronogramas de capacitación', '/listcronogCapacitacion', 24, 'Operación Diaria', 'fas fa-calendar-alt', '#0d6efd,#0dcaf0', 1, 1),
('Consultor', 'Operación Diaria', 'Calificación Estándares Mínimos', 'Evaluación estándares mínimos', '/listEvaluaciones', 25, 'Operación Diaria', 'fas fa-tasks', '#e74c3c,#c0392b', 1, 1),
('Consultor', 'Operación Diaria', 'Plan de Trabajo Anual', 'Administración PTA', '/pta-cliente-nueva/list', 5, 'Operación Diaria', 'fas fa-graduation-cap', '#20c997,#13b397', 1, 1),
('Consultor', 'Operación Diaria', 'Pendientes', 'Tareas pendientes', '/listPendientes', 6, 'Operación Diaria', 'fas fa-clipboard-check', '#667eea,#764ba2', 1, 1),
('Consultor', 'Operación Diaria', 'Listado Mantenimientos', 'Gestión de mantenimientos', '/vencimientos', 40, 'Operación Diaria', 'fas fa-tools', '#f39c12,#e67e22', 1, 1),
('Consultor', 'Operación Diaria', 'Vigías', 'Gestión de vigías SST', '/listVigias', 23, 'Operación Diaria', 'fas fa-hard-hat', '#6f42c1,#9b59b6', 1, 1);

-- 3. Gestión Clientes (3 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'Gestión Clientes', 'Nuevo Cliente', 'Registrar un nuevo cliente en la plataforma', '/clients/nuevo', 1, 'Gestión Clientes', 'fas fa-user-plus', '#2d6a4f,#40916c', 1, 1),
('Consultor', 'Gestión Clientes', 'Ver Vista de Cliente', 'Previsualizar el portal como lo ve el cliente', '/vista-cliente', 2, 'Gestión Clientes', 'fas fa-eye', '#6366f1,#8b5cf6', 1, 1),
('Consultor', 'Gestión Clientes', 'Planillas Seg. Social', 'Gestión de planillas de seguridad social', 'planillas-seguridad-social', 3, 'Gestión Clientes', 'fas fa-file-invoice-dollar', '#6f42c1,#9b59b6', 1, 1);

-- 4. Inspecciones y Auditoría (2 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'Inspecciones', 'Inspecciones SST', 'Módulo de inspecciones de seguridad y salud', '/inspecciones', 1, 'Inspecciones y Auditoría', 'fas fa-clipboard-check', '#0d6efd,#0dcaf0', 1, 1),
('Consultor', 'Inspecciones', 'Auditoría de Visitas', 'Control y auditoría de visitas realizadas', 'consultant/auditoria-visitas', 2, 'Inspecciones y Auditoría', 'fas fa-search', '#f39c12,#e67e22', 1, 1);

-- 5. Cumplimiento y Control (3 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'Cumplimiento', 'Auditoría PTA', 'Auditoría del Plan de Trabajo Anual', '/audit-pta', 1, 'Cumplimiento y Control', 'fas fa-history', '#e74c3c,#c0392b', 1, 1),
('Consultor', 'Cumplimiento', 'Transiciones PTA', 'Historial de transiciones del PTA', '/pta-transiciones', 2, 'Cumplimiento y Control', 'fas fa-exchange-alt', '#0d6efd,#0b5ed7', 1, 1),
('Consultor', 'Cumplimiento', 'Listado Maestro', 'Documentos maestros Decreto 1072', 'listado-maestro', 3, 'Cumplimiento y Control', 'fas fa-list-alt', '#e67e22,#f39c12', 1, 1);

-- 6. Planeación SST (3 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'Planeación', 'Presupuesto SST', 'Gestión del presupuesto de SST', 'presupuesto', 1, 'Planeación SST', 'fas fa-calculator', '#11998e,#38ef7d', 1, 1),
('Consultor', 'Planeación', 'Seguimiento Agenda', 'Seguimiento de agenda y actividades', 'seguimiento-agenda', 2, 'Planeación SST', 'fas fa-calendar-check', '#e74c3c,#c0392b', 1, 1),
('Consultor', 'Planeación', 'Acceso Rápido', 'Atajos a funciones frecuentes', '/quick-access', 3, 'Planeación SST', 'fas fa-bolt', '#bd9751,#d4af37', 1, 1);

-- 7. Dashboards Analíticos (5 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'Dashboards', 'Dashboard Estándares Mínimos', 'Tablero analítico de estándares mínimos', 'consultant/dashboard-estandares', 1, 'Dashboards Analíticos', 'fas fa-chart-pie', '#667eea,#764ba2', 1, 1),
('Consultor', 'Dashboards', 'Dashboard Capacitaciones', 'Tablero analítico de capacitaciones', 'consultant/dashboard-capacitaciones', 2, 'Dashboards Analíticos', 'fas fa-graduation-cap', '#f093fb,#f5576c', 1, 1),
('Consultor', 'Dashboards', 'Dashboard Plan de Trabajo', 'Tablero analítico del plan de trabajo', 'consultant/dashboard-plan-trabajo', 3, 'Dashboards Analíticos', 'fas fa-tasks', '#4facfe,#00f2fe', 1, 1),
('Consultor', 'Dashboards', 'Dashboard Pendientes', 'Tablero analítico de pendientes', 'consultant/dashboard-pendientes', 4, 'Dashboards Analíticos', 'fas fa-clipboard-list', '#fa709a,#fee140', 1, 1),
('Consultor', 'Dashboards', 'Informe de Avances', 'Informe consolidado de avances por cliente', 'informe-avances', 5, 'Dashboards Analíticos', 'fas fa-chart-line', '#11998e,#38ef7d', 1, 1);

-- 8. Gestión Documental (9 items - estos ya existían como registros originales, se les asignó categoría)
-- IDs originales: 1, 26, 9, 2, 3, 11, 12, 13, 22
-- Incluye: Cargue de PDFs, Archivos colaboración, Carpetas Word/Excel, Sub Clasificación Reportes,
--          Tipo de Reporte, Versiones, Políticas, Tipos de Documentos, Políticas de SST

-- 9. Carga Masiva CSV (8 items - IDs originales: 29, 28, 30, 31, 32, 33, 34, 42)
-- Incluye: Cronograma de Capacitación, Plan de Trabajo, Pendientes, Evaluación Inicial,
--          Políticas para Documentos, Versiones de Documentos, CSV Editar PTA, KPIs Empresas

-- 10. Usuarios y Accesos (5 items - IDs originales: 7, 8, 36, 37, 38)
-- Incluye: Clientes, Consultores, Accesos según Contractualidad, Universo Estándares, Listado Vistas

-- 11. Configuración (3 items - IDs originales: 4, 35, 39)
-- Incluye: Capacitaciones, Items del Dashboard, Items Nucleares

-- 12. Administración (3 items)
INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES
('Consultor', 'Administración', 'Consumo de Plataforma', 'Métricas de uso de la plataforma', '/admin/usage', 1, 'Administración', 'fas fa-chart-line', '#11998e,#38ef7d', 1, 1),
('Consultor', 'Administración', 'Panel de Agendamientos', 'Gestión de agendamientos de visitas', '/admin/agendamientos', 2, 'Administración', 'fas fa-calendar-check', '#20c997,#13b397', 1, 1),
('Consultor', 'Administración', 'Resetear Ciclo PHVA', 'Resetea evaluaciones de estándares mínimos anuales', '#resetPHVAModal', 3, 'Administración', 'fas fa-redo-alt', '#dc3545,#c82333', 0, 1);
```

#### Tabla `accesos`

```sql
CREATE TABLE `accesos` (
  `id_acceso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_acceso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Datos semilla** (59 registros — ítems de estándares SST Decreto 1072):

```sql
-- Ejemplo de los primeros registros (patrón: nombre del estándar, URL con /1, dimensión PHVA)
INSERT INTO accesos (nombre, url, dimension) VALUES
('1.1.1 Asignación de Responsable', '/asignacionResponsable/1', 'Planear'),
('1.1.2 Asignación de Responsabilidades', '/asignacionResponsabilidades/1', 'Planear'),
('1.1.3 Asignación de Vigía', '/asignacionVigia/1', 'Hacer'),
('2.1.1 Política de Seguridad y Salud en el Trabajo', '/politicaSst/1', 'Planear'),
('2.1.2 Política de No Alcohol, Drogas ni Tabaco', '/politicaAlcohol/1', 'Planear');
-- ... (59 registros en total, cada uno es un estándar SST con su URL y dimensión PHVA)
```

**`dimension`** corresponde al ciclo PHVA: `Planear`, `Hacer`, `Verificar`, `Actuar`.

#### Tabla `estandares_accesos` (pivote)

```sql
CREATE TABLE `estandares_accesos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_estandar` int(11) NOT NULL,
  `id_acceso` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla `estandares` (catálogo de frecuencias contractuales)

```sql
CREATE TABLE `estandares` (
  `id_estandar` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_estandar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO estandares (nombre) VALUES
('Mensual'), ('Bimensual'), ('Trimestral'), ('Proyecto');
```

#### Tabla `tbl_urls` (opcional — puede no existir aún)

```sql
CREATE TABLE `tbl_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(100) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `url` varchar(1000) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 3.2 Tablas del SISTEMA que el módulo consulta

| Tabla | Campos usados | Relación |
|-------|---------------|----------|
| `tbl_clientes` | `id_cliente`, `nombre_cliente` | `QuickAccessDashboardController` carga todos los clientes para el selector global |
| `tbl_consultor` | `id_consultor` | Referenciado indirectamente por el dashboard |

---

## 4. FLUJO FUNCIONAL

### 4.1 Componente 1: Dashboard Principal del Consultor (`/consultor/dashboard`)

**Controlador:** `ConsultorTablaItemsController::index()`

```
1. Lee todos los dashboard_items con activo=1, ordenados por categoria ASC, orden ASC
2. Agrupa ítems en un array asociativo: $grouped[categoria] = [items...]
3. Ordena las categorías según un array fijo de 12 categorías predefinidas
4. Categorías no listadas se agregan al final
5. Pasa $data['grouped'] a la vista
```

**Vista:** `consultant/dashboard.php`

```
1. Renderiza navbar con logos de la empresa
2. Banner de bienvenida con nombre de sesión
3. Buscador en tiempo real (input text que filtra tarjetas por data-search)
4. Para cada categoría:
   - Header con icono (mapeado en $catIcons), nombre y badge con conteo
   - Grid CSS responsive de tarjetas (auto-fill, minmax 220px)
   - Cada tarjeta es un <a> con:
     - Icono con gradiente de color
     - Título (detalle) y descripción
     - Border-left del color primario
     - Si accion_url empieza con '#': abre modal (data-bs-toggle="modal")
     - Si target_blank=1: abre en nueva pestaña
5. Modal de reseteo PHVA (carga clientes por AJAX, doble confirmación)
6. Banner móvil para PWA de Inspecciones (detección por User-Agent)
7. Footer corporativo con redes sociales
```

**Buscador JavaScript:**
```javascript
// Filtra tarjetas en tiempo real por término de búsqueda
searchInput.addEventListener('input', function() {
    var term = this.value.toLowerCase().trim();
    // Itera categorías y tarjetas, muestra/oculta según match en data-search
    // data-search contiene: detalle + descripcion + categoria (lowercase)
});
```

### 4.2 Componente 2: Quick Access Dashboard (`/quick-access`)

**Controlador:** `QuickAccessDashboardController::index()`

```
1. Carga TODOS los clientes desde ClientModel::findAll()
2. Pasa $data['clients'] a la vista
```

**Vista:** `quick_access_dashboard.php`

```
1. Selector de cliente global con Select2
2. Persistencia en localStorage ('selectedClient')
3. Botón "Abrir Todas las Vistas":
   - Abre 6 URLs en pestañas nuevas con delay de 100ms entre cada una
   - URLs: /reportList, /pta-cliente-nueva/list, /listcronogCapacitacion,
           /vencimientos, /listPendientes, /listEvaluaciones
4. Sincronización via BroadcastChannel('quick_access_sync'):
   - Al cambiar cliente: postMessage({type:'clientChange', clientId, clientName})
   - Backup: localStorage 'clientSyncTrigger' con timestamp
5. Tarjetas informativas estáticas de las 6 vistas disponibles
```

**Flujo de sincronización multi-pestaña:**

```
┌──────────────────┐     BroadcastChannel        ┌──────────────────┐
│  Quick Access     │  ──────────────────────────► │  Vista receptora  │
│  Dashboard        │   {type:'clientChange',     │  (ej: reportList) │
│                   │    clientId: '5',            │                   │
│  [Selector]       │    clientName: 'Cliente X'}  │  [Auto-selecciona │
│  Cliente X  ▼     │                              │   Cliente X]      │
│                   │     localStorage              │                   │
│  [Abrir Todas] ──────► 'selectedClient' = '5'   │                   │
│  [Sincronizar] ──────► 'clientSyncTrigger'       │                   │
└──────────────────┘                               └──────────────────┘
```

### 4.3 Componente 3: CRUD de Dashboard Items (Admin)

| Método | Acción |
|--------|--------|
| `listitemdashboard()` | Lee todos los dashboard_items, muestra en DataTable |
| `additemdashboard()` | Muestra formulario vacío |
| `additemdashboardpost()` | `$model->save($this->request->getPost())` → redirect |
| `edititemdashboar($id)` | `$model->find($id)` → muestra formulario con datos |
| `editpostitemdashboar($id)` | `$model->update($id, $data)` → redirect |
| `deleteitemdashboard($id)` | `$model->delete($id)` → redirect |

### 4.4 Componente 4: CRUD de Accesos (Menú SST)

| Método | Acción |
|--------|--------|
| `listaccesosseguncliente()` | Lee accesos con filtros opcionales (nombre, url), DataTable |
| `addpostaccesosseguncliente()` | `$this->accesoModel->insert($data)` → redirect |
| `editpostaccesosseguncliente()` | `$this->accesoModel->update($id, $data)` → redirect |
| `deleteaccesosseguncliente($id)` | `$this->accesoModel->delete($id)` → redirect |

### 4.5 Componente 5: CRUD Estándar↔Acceso (pivote contractualidad)

| Método | Acción |
|--------|--------|
| `listaccesosseguncontractualidad()` | JOIN estandares + accesos, muestra relaciones |
| `addpostaccesosseguncontractualidad()` | Inserta relación id_estandar + id_acceso |
| `editpostaccesosseguncontractualidad()` | Actualiza relación |
| `deleteaccesosseguncontractualidad($id)` | Elimina relación |

### 4.6 Código del receptor BroadcastChannel (para replicar en vistas destino)

```javascript
// Snippet para agregar en cada vista que deba recibir sincronización de cliente
function _syncClientFromQA(newClientId) {
    console.log('[NombreVista] Sync recibido, cliente:', newClientId);
    // Adaptar: buscar el select de cliente de esta vista y cambiar su valor
    if ($('#clientSelect option[value="' + newClientId + '"]').length > 0) {
        $('#clientSelect').val(newClientId).trigger('change');
    }
}

// Fallback via localStorage (para navegadores sin BroadcastChannel)
window.addEventListener('storage', function(e) {
    if (e.key === 'clientSyncTrigger' && e.newValue) {
        _syncClientFromQA(e.newValue.split('|')[0]);
    }
});

// Mecanismo principal: BroadcastChannel
if (typeof BroadcastChannel !== 'undefined') {
    var _qaSyncCh = new BroadcastChannel('quick_access_sync');
    _qaSyncCh.onmessage = function(e) {
        if (e.data && e.data.type === 'clientChange') {
            _syncClientFromQA(e.data.clientId);
        }
    };
}
```

---

## 5. DEPENDENCIAS EXTERNAS

### 5.1 CDN Frontend

| Librería | Versión | Uso | URL CDN |
|----------|---------|-----|---------|
| Bootstrap CSS | 5.3.0 | Layout, componentes, modales, grid | `cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css` |
| Bootstrap JS Bundle | 5.3.0 | Modales, dropdowns, tooltips | `cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js` |
| Font Awesome | 6.4.0 | Iconos en tarjetas y botones | `cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css` |
| jQuery | 3.6.0 | Select2, DataTables, DOM manipulation | `code.jquery.com/jquery-3.6.0.min.js` |
| Select2 CSS | 4.1.0-rc.0 | Selector de cliente mejorado | `cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css` |
| Select2 JS | 4.1.0-rc.0 | Selector de cliente mejorado | `cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js` |
| DataTables CSS | 1.13.4 | Tablas con paginación y filtros | `cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css` |
| DataTables JS | 1.13.4 | Tablas con paginación y filtros | `cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js` |
| DataTables Bootstrap5 | 1.13.4 | Integración DataTables+Bootstrap | `cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js` |
| DataTables i18n ES | 1.13.4 | Traducción al español | `cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json` |
| Bootstrap Icons | 1.10.5 | Iconos en vista admin | `cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css` |

### 5.2 APIs del navegador

| API | Uso |
|-----|-----|
| `BroadcastChannel` | Sincronización de cliente seleccionado entre pestañas |
| `localStorage` | Persistencia del cliente seleccionado y fallback de sincronización |
| `window.open()` | Apertura masiva de vistas en pestañas nuevas |

### 5.3 Librerías PHP

Ninguna librería externa adicional. Solo CodeIgniter 4 core (Controller, Model).

### 5.4 Assets locales (logos)

| Archivo | Uso |
|---------|-----|
| `uploads/logoenterprisesstblancoslogan.png` | Logo Enterprisesst en navbar |
| `uploads/logosst.png` | Logo SST en navbar |
| `uploads/logocycloidsinfondo.png` | Logo Cycloid en navbar |
| `favicon.ico` | Favicon del dashboard |

---

## 6. PATRONES ESPECIALES

### 6.1 Sincronización multi-pestaña (BroadcastChannel)

**Patrón:** El Quick Access Dashboard actúa como **emisor central** de cambios de cliente. Las 6 vistas receptoras escuchan en el canal `'quick_access_sync'` y auto-seleccionan el cliente cuando reciben un mensaje.

**Doble mecanismo de respaldo:**
1. **BroadcastChannel** (principal) — funciona entre pestañas del mismo origen
2. **localStorage + evento `storage`** (fallback) — para navegadores sin soporte BC

**Clave de localStorage:**
- `selectedClient` → ID del cliente seleccionado (persistente)
- `selectedClientName` → Nombre del cliente (persistente)
- `clientSyncTrigger` → `clientId|timestamp` (trigger de cambio)

### 6.2 Ítems de dashboard como modales

Cuando `accion_url` empieza con `#` (ej: `#resetPHVAModal`), la tarjeta no navega sino que abre un modal de Bootstrap:

```php
$isModal = str_starts_with($item['accion_url'], '#');
$href = $isModal ? 'javascript:void(0)' : base_url($item['accion_url']);
$modalAttr = $isModal ? 'data-bs-toggle="modal" data-bs-target="' . esc($item['accion_url']) . '"' : '';
```

### 6.3 Sistema de categorías con iconos y gradientes

Cada categoría tiene un icono FontAwesome mapeado en la vista:

```php
$catIcons = [
    'IA y Asistencia'          => 'fas fa-robot',
    'Operación Diaria'         => 'fas fa-calendar-alt',
    'Gestión Clientes'         => 'fas fa-building',
    'Inspecciones y Auditoría' => 'fas fa-clipboard-check',
    'Cumplimiento y Control'   => 'fas fa-gavel',
    'Planeación SST'           => 'fas fa-project-diagram',
    'Dashboards Analíticos'    => 'fas fa-chart-bar',
    'Gestión Documental'       => 'fas fa-folder-open',
    'Carga Masiva CSV'         => 'fas fa-file-csv',
    'Usuarios y Accesos'       => 'fas fa-users-cog',
    'Configuración'            => 'fas fa-cog',
    'Administración'           => 'fas fa-tools',
];
```

Cada ítem tiene su propio gradiente de 2 colores (ej: `#4facfe,#00f2fe`) que se aplica al icono cuadrado.

### 6.4 Orden de categorías (hardcoded en el controlador)

```php
$ordenCategorias = [
    'IA y Asistencia',
    'Operación Diaria',
    'Gestión Clientes',
    'Inspecciones y Auditoría',
    'Cumplimiento y Control',
    'Planeación SST',
    'Dashboards Analíticos',
    'Gestión Documental',
    'Carga Masiva CSV',
    'Usuarios y Accesos',
    'Configuración',
    'Administración',
];
```

### 6.5 Modal de Reseteo PHVA (integrado en dashboard.php)

- Carga clientes por AJAX (`/api/getClientesParaReseteo`)
- Doble modal de confirmación (advertencia → confirmación final)
- POST a `/api/resetCicloPHVA` con `id_cliente` + token CSRF
- Resetea evaluaciones de estándares mínimos anuales de un cliente

### 6.6 Banner PWA móvil

Detección por User-Agent para mostrar banner fijo inferior con enlace a `/inspecciones`:

```javascript
if (/Android|iPhone|iPad|iPod|Opera Mini|IEMobile|WPDesktop/i.test(navigator.userAgent)) {
    document.getElementById('mobileBanner').style.display = 'block';
}
```

---

## 7. ORDEN DE IMPLEMENTACIÓN

### Paso 1: Base de datos

```
1.1 Crear tabla `dashboard_items` (CREATE TABLE completo en sección 3.1)
1.2 Crear tabla `accesos` (CREATE TABLE completo en sección 3.1)
1.3 Crear tabla `estandares` (CREATE TABLE + 4 registros semilla)
1.4 Crear tabla `estandares_accesos` (CREATE TABLE pivote)
1.5 (Opcional) Crear tabla `tbl_urls`
1.6 Insertar datos semilla en `dashboard_items` (52 registros activos)
1.7 Insertar datos semilla en `accesos` (59 registros de estándares SST)
```

### Paso 2: Modelos

```
2.1 Crear DashboardItemModel.php (tabla: dashboard_items, PK: id)
2.2 Crear AccesoModel.php (tabla: accesos, PK: id_acceso)
2.3 Crear EstandarModel.php (tabla: estandares, PK: id_estandar)
2.4 Crear EstandarAccesoModel.php (tabla: estandares_accesos, PK: id)
2.5 (Opcional) Crear UrlModel.php (tabla: tbl_urls, PK: id, con timestamps)
2.6 Verificar que ClientModel ya exista (tabla: tbl_clientes)
```

### Paso 3: Rutas

```
3.1 Registrar GET /quick-access → QuickAccessDashboardController::index
3.2 Registrar GET /consultor/dashboard → ConsultorTablaItemsController::index
3.3 Registrar 6 rutas CRUD para consultant/listitemdashboard (Admin)
3.4 Registrar 6 rutas CRUD para /accesosseguncliente/*
3.5 Registrar 6 rutas CRUD para /accesosseguncontractualidad/*
3.6 Aplicar filtro de autenticación a todos los patrones
```

### Paso 4: Controladores

```
4.1 Crear QuickAccessDashboardController (21 líneas — carga clientes, renderiza vista)
4.2 Crear ConsultorTablaItemsController (58 líneas — lee items activos, agrupa por categoría)
4.3 Crear AdminlistdashboardController (54 líneas — CRUD de dashboard_items)
4.4 Crear AccesossegunclienteController (76 líneas — CRUD de accesos)
4.5 Crear AccesosseguncontractualidadController (80 líneas — CRUD pivote)
```

### Paso 5: Vistas

```
5.1 Crear consultant/dashboard.php (766 líneas — dashboard principal con tarjetas, buscador, modal PHVA)
5.2 Crear consultant/quick_access_dashboard.php (380 líneas — selector global, apertura masiva, BroadcastChannel)
5.3 Crear consultant/listitemdashboard.php (131 líneas — DataTable admin)
5.4 Crear consultant/additemdashboard.php (42 líneas — form agregar)
5.5 Crear consultant/listaccesosseguncliente.php (249 líneas — DataTable accesos)
5.6 Crear consultant/addaccesosseguncliente.php (68 líneas — form agregar acceso)
5.7 Crear consultant/editaccesosseguncliente.php (59 líneas — form editar acceso)
5.8 Crear consultant/listaccesosseguncontractualidad.php (126 líneas — DataTable relaciones)
5.9 Crear consultant/addaccesosseguncontractualidad.php (64 líneas — form vincular)
5.10 Crear consultant/editaccesosseguncontractualidad.php (50 líneas — form editar relación)
```

### Paso 6: Integración BroadcastChannel en vistas receptoras

```
6.1 Agregar snippet de receptor BroadcastChannel (sección 4.6) en cada vista que tenga
    selector de cliente y deba sincronizarse:
    - report_list.php
    - list_cronogramas.php
    - list_pendientes.php
    - list_pta_cliente_nueva.php
    - list_evaluaciones.php
    - listVencimientosMantenimiento.php
6.2 Adaptar la función _syncClientFromQA() al selector de cliente específico de cada vista
```

### Paso 7: Enlace desde el dashboard de administración

```
7.1 Agregar botón/enlace a /quick-access en el dashboard de administración (admindashboard.php)
    con target="_blank":
    <a href="<?= base_url('/quick-access') ?>" target="_blank">
        <button class="btn" style="background: linear-gradient(135deg, #bd9751, #d4af37);">
            <i class="fas fa-bolt me-2"></i>Acceso Rápido
        </button>
    </a>
```

---

## DIAGRAMA DE ARQUITECTURA

```
                    ┌─────────────────────────────────┐
                    │        dashboard_items           │
                    │  (52 registros activos,          │
                    │   12 categorías, iconos,         │
                    │   gradientes, URLs)              │
                    └────────────┬────────────────────┘
                                 │
                    ┌────────────┴────────────────────┐
                    │                                  │
          ┌─────────▼──────────┐          ┌───────────▼──────────┐
          │ ConsultorTabla     │          │ Adminlistdashboard   │
          │ ItemsController    │          │ Controller           │
          │ (Vista principal)  │          │ (CRUD admin)         │
          └─────────┬──────────┘          └───────────┬──────────┘
                    │                                  │
          ┌─────────▼──────────┐          ┌───────────▼──────────┐
          │ dashboard.php      │          │ listitemdashboard.php│
          │ (tarjetas x cat)   │          │ additemdashboard.php │
          │ (buscador)         │          └──────────────────────┘
          │ (modal PHVA)       │
          └────────────────────┘

          ┌────────────────────┐
          │ QuickAccess        │
          │ DashboardController│
          └─────────┬──────────┘
                    │
          ┌─────────▼──────────┐     BroadcastChannel      ┌──────────────┐
          │ quick_access_      │  ────────────────────────► │ 6 vistas     │
          │ dashboard.php      │   'quick_access_sync'      │ receptoras   │
          │ (Select2 cliente)  │                             │ (auto-sync   │
          │ (abrir 6 pestañas) │     localStorage            │  de cliente) │
          │ (sincronizar)      │  ────────────────────────► │              │
          └────────────────────┘                             └──────────────┘

          ┌────────────────────┐          ┌──────────────────────┐
          │     accesos        │◄────────►│  estandares_accesos  │
          │  (59 ítems SST)    │  pivote  │  (relaciones)        │
          └────────────────────┘          └──────────┬───────────┘
                                                     │
                                          ┌──────────▼───────────┐
                                          │    estandares        │
                                          │ (Mensual, Bimensual, │
                                          │  Trimestral, Proyecto)│
                                          └──────────────────────┘
```

---

## PALETA DE COLORES DEL MÓDULO

```css
:root {
    --primary-dark: #1c2437;      /* Fondo oscuro principal */
    --secondary-dark: #2c3e50;    /* Fondo oscuro secundario */
    --gold-primary: #bd9751;      /* Dorado principal (marca) */
    --gold-secondary: #d4af37;    /* Dorado secundario */
    --white-primary: #ffffff;
    --white-secondary: #f8f9fa;
    --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);  /* Fondo de página */
}
```

---

> **Nota:** Este documento es autocontenido. Para replicar el módulo completo, siga los pasos de la sección 7 en orden. Los datos semilla de `dashboard_items` deben adaptarse a las URLs reales del proyecto destino.
