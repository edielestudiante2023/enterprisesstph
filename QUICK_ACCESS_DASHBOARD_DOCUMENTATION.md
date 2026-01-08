# 📊 QuickAccessDashboardController - Documentación Técnica

## 📋 Información General

**Archivo:** `app/Controllers/QuickAccessDashboardController.php`
**Namespace:** `App\Controllers`
**Fecha de Creación:** 05 de Enero 2026
**Autor:** Sistema de Gestión SST
**Versión:** 1.0.0

---

## 🎯 Propósito

El `QuickAccessDashboardController` es un controlador especializado que gestiona el dashboard de acceso rápido del sistema. Su función principal es proporcionar una interfaz centralizada donde los usuarios pueden:

- Seleccionar un cliente de forma global
- Abrir múltiples vistas del sistema simultáneamente
- Sincronizar el cliente seleccionado entre todas las vistas abiertas

---

## 🏗️ Estructura del Controlador

```php
<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\Controller;

class QuickAccessDashboardController extends Controller
{
    public function index()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $data = [
            'clients' => $clients
        ];

        return view('consultant/quick_access_dashboard', $data);
    }
}
```

---

## 📦 Dependencias

### Modelos Utilizados
- **ClientModel**: Modelo para obtener la lista de clientes del sistema

### Librerías de CodeIgniter
- **Controller**: Clase base de CodeIgniter 4 para controladores

---

## 🔧 Métodos

### `index()`

**Descripción:**
Método principal que carga el dashboard de acceso rápido.

**Parámetros:**
Ninguno

**Retorno:**
- **Tipo:** `string` (Vista renderizada)
- **Vista:** `consultant/quick_access_dashboard`

**Flujo de Ejecución:**
1. Instancia el modelo `ClientModel`
2. Obtiene todos los clientes mediante `findAll()`
3. Prepara un array de datos con la lista de clientes
4. Retorna la vista `quick_access_dashboard` con los datos

**Datos Pasados a la Vista:**
```php
[
    'clients' => [
        [
            'id_cliente' => 1,
            'nombre_cliente' => 'Cliente Ejemplo',
            // ... otros campos del cliente
        ],
        // ... más clientes
    ]
]
```

---

## 🌐 Ruta Asociada

**Archivo de Rutas:** `app/Config/Routes.php`

```php
$routes->get('quick-access', 'QuickAccessDashboardController::index');
```

**URL de Acceso:**
- Desarrollo: `http://localhost/enterprisesstph/public/quick-access`
- Producción: `https://phorizontal.cycloidtalent.com/quick-access`

---

## 🎨 Vista Asociada

**Archivo:** `app/Views/consultant/quick_access_dashboard.php`

### Características de la Vista:
1. **Selector de Cliente Global**
   - Dropdown con Select2
   - Sincronización vía localStorage
   - Persistencia entre pestañas

2. **Botón "Abrir Todas las Vistas"**
   - Abre 6 vistas simultáneamente en pestañas separadas
   - Pasa el cliente seleccionado a todas las vistas

3. **Vistas Disponibles:**
   - Lista de Reportes
   - Plan de Trabajo
   - Cronograma de Capacitación
   - Vencimientos
   - Pendientes
   - Evaluaciones

---

## 💾 Integración con LocalStorage

El dashboard utiliza localStorage para sincronizar el cliente seleccionado:

```javascript
// Guardar cliente seleccionado
localStorage.setItem('selectedClient', clientId);

// Las vistas abiertas leen este valor
var storedClient = localStorage.getItem('selectedClient');
```

### Vistas Sincronizadas:
- ✅ `reportList` - Lista de Reportes
- ✅ `pta-cliente-nueva/list` - Plan de Trabajo
- ✅ `listcronogCapacitacion` - Cronogramas
- ✅ `vencimientos` - Vencimientos
- ✅ `listPendientes` - Pendientes
- ✅ `listEvaluaciones` - Evaluaciones

---

## 🔗 Integración con Dashboards

El botón de acceso rápido se agregó en:

### Dashboard del Consultor
**Archivo:** `app/Views/consultant/dashboard.php`
```html
<a href="<?= base_url('/quick-access') ?>" target="_blank">
    <button class="btn btn-logout-custom">
        <i class="fas fa-bolt me-2"></i>Acceso Rápido
    </button>
</a>
```

### Dashboard del Administrador
**Archivo:** `app/Views/consultant/admindashboard.php`
```html
<a href="<?= base_url('/quick-access') ?>" target="_blank">
    <button class="btn btn-logout-custom">
        <i class="fas fa-bolt me-2"></i>Acceso Rápido
    </button>
</a>
```

---

## 🔒 Seguridad

### Validaciones Implementadas:
- ✅ El controlador hereda las protecciones de `CodeIgniter\Controller`
- ✅ Uso de `base_url()` para generar URLs seguras
- ✅ Validación de cliente antes de abrir vistas múltiples

### Consideraciones:
- **Filtros de Autenticación:** Se recomienda aplicar filtros de autenticación en las rutas
- **Autorización:** Verificar que solo usuarios autorizados accedan al dashboard
- **XSS Protection:** CodeIgniter 4 protege automáticamente contra XSS en las vistas

---

## 📊 Flujo de Datos

```
Usuario accede a /quick-access
         ↓
QuickAccessDashboardController::index()
         ↓
Obtiene lista de clientes (ClientModel)
         ↓
Renderiza vista quick_access_dashboard.php
         ↓
Usuario selecciona cliente
         ↓
Cliente se guarda en localStorage
         ↓
Usuario hace clic en "Abrir Todas las Vistas"
         ↓
Se abren 6 pestañas nuevas
         ↓
Cada vista lee el cliente de localStorage
         ↓
Cada vista se filtra automáticamente por el cliente
```

---

## 🧪 Casos de Uso

### Caso 1: Apertura Rápida de Vistas
**Escenario:** Usuario necesita revisar múltiples vistas de un cliente específico
**Flujo:**
1. Usuario accede al Quick Access Dashboard
2. Selecciona el cliente deseado
3. Hace clic en "Abrir Todas las Vistas"
4. Se abren 6 pestañas con el cliente ya filtrado

### Caso 2: Sincronización entre Pestañas
**Escenario:** Usuario cambia de cliente en una vista
**Flujo:**
1. Cliente se actualiza en localStorage
2. Otras pestañas detectan el cambio
3. Todas las vistas se actualizan con el nuevo cliente

---

## 🐛 Troubleshooting

### Problema: Vistas no se filtran automáticamente
**Solución:** Verificar que cada vista tenga implementada la lógica de localStorage:
```javascript
var storedClient = localStorage.getItem('selectedClient');
if (storedClient) {
    $('#clientSelect').val(storedClient).trigger('change');
}
```

### Problema: Navegador bloquea pestañas múltiples
**Solución:** El código incluye un delay de 100ms entre aperturas:
```javascript
setTimeout(function() {
    window.open(url, '_blank');
}, index * 100);
```

---

## 📈 Métricas de Uso

### Vistas Gestionadas: 6
- Lista de Reportes
- Plan de Trabajo
- Cronograma de Capacitación
- Vencimientos
- Pendientes
- Evaluaciones

### Tiempo de Carga Estimado: < 500ms
### Navegadores Compatibles: Chrome, Firefox, Edge, Safari (modern versions)

---

## 🔄 Historial de Cambios

| Versión | Fecha | Descripción | Commit |
|---------|-------|-------------|--------|
| 1.0.0 | 2026-01-05 | Creación inicial del controlador | e53cfae |

---

## 📝 Notas Técnicas

1. **Patrón MVC:** El controlador sigue el patrón MVC de CodeIgniter 4
2. **Single Responsibility:** El controlador tiene una única responsabilidad: cargar el dashboard
3. **Simplicidad:** Código minimalista y fácil de mantener
4. **Escalabilidad:** Fácil agregar nuevas vistas al sistema

---

## 🎓 Ejemplo de Uso

### Desde el Dashboard del Consultor
```php
// El usuario hace clic en el botón "Acceso Rápido"
// Se abre una nueva pestaña con la URL: /quick-access
// El controlador carga la vista con todos los clientes disponibles
```

### Desde el Código
```php
// Instanciar el controlador (normalmente manejado por el router)
$controller = new QuickAccessDashboardController();
$view = $controller->index();
```

---

## 🔗 Referencias

- **CodeIgniter 4 Documentation:** https://codeigniter.com/user_guide/
- **Controller Guide:** https://codeigniter.com/user_guide/incoming/controllers.html
- **View Guide:** https://codeigniter.com/user_guide/outgoing/views.html

---

## 👥 Mantenimiento

**Responsable:** Equipo de Desarrollo SST
**Contacto:** desarrollo@cycloidtalent.com
**Última Revisión:** 05 de Enero 2026

---

## ✅ Checklist de Implementación

- [x] Controlador creado
- [x] Ruta configurada
- [x] Vista implementada
- [x] Integración con localStorage
- [x] Botones en dashboards
- [x] Sincronización con 6 vistas
- [x] Pruebas de funcionalidad
- [x] Documentación completa

---

**Fin de la Documentación**
