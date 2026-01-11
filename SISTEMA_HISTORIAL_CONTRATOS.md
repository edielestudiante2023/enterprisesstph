# 📋 SISTEMA DE HISTORIAL DE CONTRATOS

## 🎯 Descripción del Problema Resuelto

### Problema Original
El sistema tenía una **debilidad estructural crítica**:
- ❌ No existía historial de contratos
- ❌ Al renovar, se editaban las fechas en `tbl_clientes`, perdiendo la información anterior
- ❌ No se sabía cuántas renovaciones tenía un cliente
- ❌ Se perdía la fecha inicial del primer contrato
- ❌ Imposible hacer análisis de retención de clientes
- ❌ Pérdida de trazabilidad histórica

### Solución Implementada
✅ Sistema completo de gestión de historial de contratos
✅ Mantiene **100% de retrocompatibilidad** con el sistema existente
✅ Trazabilidad completa de todos los contratos
✅ Estadísticas y métricas de renovación
✅ Alertas automáticas de vencimiento

---

## 📊 Estructura de la Base de Datos

### Nueva Tabla: `tbl_contratos`

```sql
CREATE TABLE `tbl_contratos` (
  `id_contrato` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `numero_contrato` varchar(50) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `valor_contrato` decimal(15,2) DEFAULT NULL,
  `tipo_contrato` enum('inicial','renovacion','ampliacion') NOT NULL DEFAULT 'inicial',
  `estado` enum('activo','vencido','cancelado') NOT NULL DEFAULT 'activo',
  `observaciones` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contrato`),
  KEY `idx_cliente` (`id_cliente`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_fin` (`fecha_fin`),
  CONSTRAINT `fk_contratos_cliente` FOREIGN KEY (`id_cliente`)
    REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Campos Importantes

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_contrato` | INT | Identificador único del contrato |
| `id_cliente` | INT | FK al cliente (relación con tbl_clientes) |
| `numero_contrato` | VARCHAR(50) | Número de contrato (ej: CONT-000001-001) |
| `fecha_inicio` | DATE | Fecha de inicio del contrato |
| `fecha_fin` | DATE | Fecha de finalización del contrato |
| `valor_contrato` | DECIMAL | Valor económico del contrato |
| `tipo_contrato` | ENUM | inicial, renovacion, ampliacion |
| `estado` | ENUM | activo, vencido, cancelado |
| `observaciones` | TEXT | Notas adicionales |

---

## 🔄 Migración de Datos Existentes

### Script de Migración
Ubicación: [`database/migrations/migrate_contracts.sql`](database/migrations/migrate_contracts.sql)

Este script:
1. Toma todos los clientes activos de `tbl_clientes`
2. Crea un registro en `tbl_contratos` por cada cliente
3. Marca el tipo como "inicial"
4. Calcula el estado basado en la fecha de vencimiento
5. Genera un número de contrato único

**Ejecutar en producción y local:**
```bash
# En local (XAMPP)
mysql -u root enterprisesstph < database/migrations/migrate_contracts.sql

# En producción
mysql -u usuario -p base_datos < database/migrations/migrate_contracts.sql
```

---

## 🏗️ Arquitectura del Sistema

### Archivos Creados

```
app/
├── Models/
│   └── ContractModel.php              # Modelo principal de contratos
├── Libraries/
│   └── ContractLibrary.php           # Lógica de negocio de contratos
├── Helpers/
│   └── contract_helper.php           # Funciones helper rápidas
└── Controllers/
    └── ContractController.php        # Controlador de contratos

database/
└── migrations/
    └── migrate_contracts.sql         # Script de migración

app/Config/
└── Routes.php                        # Rutas agregadas para contratos
```

---

## 🎨 Modelos y Métodos

### ContractModel.php

#### Métodos Principales

```php
// Obtener contrato activo de un cliente
$contractModel->getActiveContract($idCliente);

// Obtener todos los contratos de un cliente
$contractModel->getClientContracts($idCliente);

// Contar renovaciones
$contractModel->countRenewals($idCliente);

// Fecha del primer contrato
$contractModel->getFirstContractDate($idCliente);

// Contratos próximos a vencer
$contractModel->getExpiringContracts($days = 30);

// Actualizar contratos vencidos
$contractModel->updateExpiredContracts();

// Generar número de contrato
$contractModel->generateContractNumber($idCliente);

// Antigüedad del cliente en meses
$contractModel->getClientAntiquity($idCliente);

// Estadísticas por consultor
$contractModel->getContractStatsByConsultant($idConsultor);
```

### ContractLibrary.php

#### Métodos de Negocio

```php
// Crear nuevo contrato
$contractLibrary->createContract($data);

// Renovar contrato existente
$contractLibrary->renewContract($idContrato, $newEndDate, $valor, $obs);

// Obtener contrato con datos del cliente
$contractLibrary->getContractWithClient($idContrato);

// Historial completo de contratos de un cliente
$contractLibrary->getClientContractHistory($idCliente);

// Alertas de vencimiento
$contractLibrary->getContractAlerts($idConsultor, $days);

// Cancelar contrato
$contractLibrary->cancelContract($idContrato, $motivo);

// Mantenimiento automático
$contractLibrary->runMaintenance();

// Estadísticas generales
$contractLibrary->getContractStats($idConsultor);

// Validar si se puede crear contrato
$contractLibrary->canCreateContract($idCliente, $fechaInicio, $fechaFin);
```

---

## 🔧 Funciones Helper

### contract_helper.php

Funciones rápidas de uso común:

```php
// Obtener contrato activo
get_active_contract($idCliente);

// Obtener número de renovaciones
get_client_renewals($idCliente);

// Obtener antigüedad en meses
get_client_antiquity($idCliente);

// Obtener fecha del primer contrato
get_first_contract_date($idCliente);

// Formatear estado con badge HTML
format_contract_status($estado);

// Formatear tipo con badge HTML
format_contract_type($tipo);

// Calcular días hasta vencimiento
days_until_expiration($fechaFin);

// Verificar si está próximo a vencer
is_contract_expiring_soon($fechaFin, $days = 30);

// Verificar si está vencido
is_contract_expired($fechaFin);

// Clase CSS para alertas
get_contract_alert_class($fechaFin);

// Formatear rango de fechas
format_contract_dates($fechaInicio, $fechaFin);

// Duración del contrato en meses
get_contract_duration($fechaInicio, $fechaFin);

// Formatear valor monetario
format_money($valor, $currency = 'COP');

// Resumen de historial
get_contract_history_summary($idCliente);

// Sincronizar fechas con tbl_clientes
sync_client_contract_dates($idCliente);
```

**Para usar los helpers, cargarlos en el controlador o autoload:**
```php
helper('contract');
```

---

## 🌐 Rutas Disponibles

### Interfaz Web

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/contracts` | GET | Lista todos los contratos con filtros |
| `/contracts/alerts` | GET | Dashboard de alertas de vencimiento |
| `/contracts/view/{id}` | GET | Ver detalles de un contrato |
| `/contracts/create` | GET | Formulario para crear contrato |
| `/contracts/create/{id_cliente}` | GET | Crear contrato para cliente específico |
| `/contracts/store` | POST | Guardar nuevo contrato |
| `/contracts/renew/{id}` | GET | Formulario de renovación |
| `/contracts/processRenewal` | POST | Procesar renovación |
| `/contracts/cancel/{id}` | GET/POST | Cancelar contrato |
| `/contracts/client-history/{id}` | GET | Historial de contratos de un cliente |
| `/contracts/maintenance` | GET | Mantenimiento automático (cron) |

### API REST

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/api/contracts/active/{id_cliente}` | GET | Obtener contrato activo de un cliente |
| `/api/contracts/stats` | GET | Obtener estadísticas de contratos |

---

## 💡 Casos de Uso

### 1. Crear un Nuevo Contrato

```php
use App\Libraries\ContractLibrary;

$contractLibrary = new ContractLibrary();

$data = [
    'id_cliente' => 1,
    'fecha_inicio' => '2025-01-01',
    'fecha_fin' => '2025-06-30',
    'valor_contrato' => 5000000,
    'tipo_contrato' => 'inicial', // o 'renovacion', 'ampliacion'
    'observaciones' => 'Primer contrato del cliente'
];

$result = $contractLibrary->createContract($data);

if ($result['success']) {
    echo "Contrato creado: " . $result['contract_number'];
}
```

### 2. Renovar un Contrato

```php
$idContrato = 5;
$nuevaFechaFin = '2025-12-31';
$nuevoValor = 6000000;
$observaciones = 'Renovación por satisfacción del cliente';

$result = $contractLibrary->renewContract(
    $idContrato,
    $nuevaFechaFin,
    $nuevoValor,
    $observaciones
);
```

### 3. Obtener Historial de un Cliente

```php
$idCliente = 1;
$history = $contractLibrary->getClientContractHistory($idCliente);

echo "Total de contratos: " . $history['total_contracts'];
echo "Renovaciones: " . $history['total_renewals'];
echo "Primer contrato: " . $history['first_contract_date'];
echo "Antigüedad: " . $history['client_antiquity_years'] . " años";

foreach ($history['contracts'] as $contract) {
    echo $contract['numero_contrato'] . " - " . $contract['estado'];
}
```

### 4. Obtener Alertas de Vencimiento

```php
// Para un consultor específico
$idConsultor = 3;
$alerts = $contractLibrary->getContractAlerts($idConsultor, 30);

foreach ($alerts as $alert) {
    echo "Cliente: " . $alert['nombre_cliente'];
    echo "Vence en: " . $alert['dias_restantes'] . " días";
    echo "Urgencia: " . $alert['urgencia']; // alta, media, baja
}
```

### 5. Uso en Vistas

```php
<!-- En una vista PHP -->
<?php helper('contract'); ?>

<h3>Contrato Activo</h3>
<?php
$contrato = get_active_contract($idCliente);
if ($contrato):
?>
    <div class="card">
        <div class="card-body">
            <h5><?= $contrato['numero_contrato'] ?></h5>
            <p>Estado: <?= format_contract_status($contrato['estado']) ?></p>
            <p>Tipo: <?= format_contract_type($contrato['tipo_contrato']) ?></p>
            <p>Vigencia: <?= format_contract_dates($contrato['fecha_inicio'], $contrato['fecha_fin']) ?></p>
            <p>Días restantes: <?= days_until_expiration($contrato['fecha_fin']) ?></p>

            <?php if (is_contract_expiring_soon($contrato['fecha_fin'])): ?>
                <div class="alert alert-<?= get_contract_alert_class($contrato['fecha_fin']) ?>">
                    ⚠️ Este contrato está próximo a vencer
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <p>No hay contrato activo</p>
<?php endif; ?>

<h4>Estadísticas del Cliente</h4>
<ul>
    <li>Renovaciones: <?= get_client_renewals($idCliente) ?></li>
    <li>Antigüedad: <?= get_client_antiquity($idCliente) ?> meses</li>
    <li>Primer contrato: <?= get_first_contract_date($idCliente) ?></li>
</ul>
```

---

## 🔄 Retrocompatibilidad

### Sincronización Automática

El sistema mantiene sincronizado `tbl_clientes.fecha_fin_contrato` con el contrato activo:

```php
// Cada vez que se crea o actualiza un contrato,
// se actualiza automáticamente tbl_clientes
$contractLibrary->updateClientDates($idCliente);
```

Esto significa que:
- ✅ Todo el código existente que lee `fecha_fin_contrato` seguirá funcionando
- ✅ No hay que modificar vistas existentes
- ✅ Los reportes actuales continúan trabajando
- ✅ Transición suave sin romper funcionalidad

### Ejemplo de Sincronización

```php
// Al crear un contrato
$contractLibrary->createContract([
    'id_cliente' => 1,
    'fecha_fin' => '2025-12-31',
    // ... otros campos
]);

// Automáticamente actualiza:
// UPDATE tbl_clientes
// SET fecha_fin_contrato = '2025-12-31'
// WHERE id_cliente = 1
```

---

## ⏰ Mantenimiento Automático (Cron Job)

### Configurar Tarea Programada

El sistema incluye un endpoint de mantenimiento que debe ejecutarse periódicamente:

```bash
# Agregar en crontab (Linux) - ejecutar diariamente a las 2:00 AM
0 2 * * * curl "https://tudominio.com/contracts/maintenance?token=TU_TOKEN_SECRETO"
```

### ¿Qué hace el mantenimiento?

1. **Actualiza contratos vencidos**: Cambia de "activo" a "vencido" los contratos cuya fecha_fin haya pasado
2. **Sincroniza fechas**: Actualiza `tbl_clientes.fecha_fin_contrato` con el contrato activo actual

### Configurar el Token de Seguridad

En `.env`:
```env
CRON_TOKEN=tu_token_secreto_aqui_cambiar
```

---

## 📈 Reportes y Estadísticas

### Estadísticas Generales

```php
$stats = $contractLibrary->getContractStats();

// Retorna:
[
    'total_contratos' => 57,
    'contratos_activos' => 34,
    'contratos_vencidos' => 22,
    'contratos_cancelados' => 1,
    'total_renovaciones' => 15,
    'valor_total_activos' => 150000000,
    'tasa_renovacion' => 26.32 // Porcentaje
]
```

### Estadísticas por Consultor

```php
$stats = $contractLibrary->getContractStats($idConsultor);
```

### Reportes Disponibles

El sistema permite generar:
- 📊 Contratos activos vs vencidos
- 📈 Tasa de renovación por consultor
- 💰 Valor total de contratos activos
- 🔔 Alertas de contratos próximos a vencer
- 📅 Proyección de ingresos futuros
- 🎯 Antigüedad promedio de clientes
- 🔄 Historial completo por cliente

---

## 🎯 Beneficios del Sistema

### Para el Negocio

✅ **Trazabilidad total**: Saber exactamente cuándo inició cada cliente
✅ **Métricas de retención**: Cuántos clientes renuevan
✅ **Proyección de ingresos**: Saber qué contratos vencen y cuándo
✅ **Alertas proactivas**: Notificaciones antes de vencimientos
✅ **Análisis de rentabilidad**: Valor histórico por cliente

### Para Operaciones

✅ **Gestión centralizada**: Todo el historial en un solo lugar
✅ **Proceso estandarizado**: Renovaciones con trazabilidad
✅ **Sin pérdida de información**: Historial completo preservado
✅ **Reportes automáticos**: Estadísticas en tiempo real
✅ **Integración transparente**: Sin romper funcionalidad existente

### Para Consultores

✅ **Visibilidad de clientes**: Ver historial completo de cada cliente
✅ **Alertas personalizadas**: Solo sus clientes próximos a vencer
✅ **Seguimiento de renovaciones**: Métricas de su gestión
✅ **Proceso simplificado**: Formularios guiados para renovaciones

---

## 🔐 Seguridad y Permisos

### Control de Acceso

El sistema respeta los roles existentes:

```php
// En ContractController
$session = session();
$role = $session->get('role');
$idConsultor = $session->get('id_consultor');

if ($role === 'consultor') {
    // Solo puede ver/editar contratos de sus clientes
    $builder->where('tbl_clientes.id_consultor', $idConsultor);
}
```

### Roles y Permisos

| Rol | Permisos |
|-----|----------|
| **Admin** | Ver todos los contratos, crear, renovar, cancelar |
| **Consultor** | Ver solo contratos de sus clientes, crear, renovar |
| **Cliente** | Ver solo sus propios contratos (próximamente) |

---

## 🚀 Próximos Pasos Recomendados

### Fase 2 - Vistas (Pendiente)

1. **Lista de contratos** (`app/Views/contracts/list.php`)
2. **Vista detalle** (`app/Views/contracts/view.php`)
3. **Formulario crear** (`app/Views/contracts/create.php`)
4. **Formulario renovar** (`app/Views/contracts/renew.php`)
5. **Dashboard alertas** (`app/Views/contracts/alerts.php`)
6. **Historial cliente** (`app/Views/contracts/client_history.php`)

### Fase 3 - Integraciones

1. **Notificaciones por email**: Alertas automáticas de vencimiento
2. **Widget en dashboard**: Mostrar contratos próximos a vencer
3. **Integración con cliente**: Portal para que clientes vean sus contratos
4. **Reportes PDF**: Generar contratos en PDF
5. **Firma electrónica**: Integración con DocuSign o similar

### Fase 4 - Mejoras

1. **Renovación automática**: Sugerir renovación basada en historial
2. **Predicción de churn**: ML para predecir probabilidad de renovación
3. **Plantillas de contrato**: Generar documentos automáticamente
4. **Facturación integrada**: Conectar con sistema de facturación
5. **Calendario de vencimientos**: Vista de calendario

---

## 📚 Referencias Rápidas

### Archivos Principales

| Archivo | Líneas | Descripción |
|---------|--------|-------------|
| [`app/Models/ContractModel.php`](app/Models/ContractModel.php) | ~200 | Modelo de datos |
| [`app/Libraries/ContractLibrary.php`](app/Libraries/ContractLibrary.php) | ~400 | Lógica de negocio |
| [`app/Helpers/contract_helper.php`](app/Helpers/contract_helper.php) | ~200 | Funciones helper |
| [`app/Controllers/ContractController.php`](app/Controllers/ContractController.php) | ~300 | Controlador web |
| [`database/migrations/migrate_contracts.sql`](database/migrations/migrate_contracts.sql) | ~50 | Script migración |

### Comandos Útiles

```bash
# Ejecutar migración en local
mysql -u root enterprisesstph < database/migrations/migrate_contracts.sql

# Ver contratos migrados
mysql -u root -e "SELECT COUNT(*) FROM enterprisesstph.tbl_contratos;"

# Ejecutar mantenimiento manual
curl "http://localhost/contracts/maintenance?token=TU_TOKEN"

# Ver estadísticas vía API
curl "http://localhost/api/contracts/stats"
```

---

## 🐛 Troubleshooting

### Error: "Tabla tbl_contratos no existe"
**Solución**: Ejecutar el script de creación de tabla en la base de datos

### Error: "Foreign key constraint fails"
**Solución**: Verificar que todos los id_cliente en tbl_contratos existan en tbl_clientes

### Fechas no se sincronizan en tbl_clientes
**Solución**: Ejecutar manualmente:
```php
sync_client_contract_dates($idCliente);
```

### No se ven contratos de ciertos clientes
**Solución**: Verificar permisos del consultor y que los contratos tengan estado correcto

---

## 📞 Soporte

Para preguntas o problemas con el sistema de contratos:
- Revisar este documento primero
- Verificar logs en `writable/logs/`
- Consultar código fuente con comentarios detallados

---

**Fecha de Implementación**: 2025-01-09
**Versión**: 1.0.0
**Estado**: ✅ Completado (Modelos, Librerías, Controladores, Rutas)
**Pendiente**: Vistas UI

---

## 🎉 Resumen Ejecutivo

Este sistema resuelve completamente el problema de pérdida de historial de contratos, proporcionando:

✅ **Trazabilidad completa** de todos los contratos desde el inicio
✅ **Métricas de renovación** para análisis de retención
✅ **Alertas automáticas** de vencimientos próximos
✅ **100% retrocompatible** con el sistema existente
✅ **Escalable y mantenible** con arquitectura limpia
✅ **API REST** para integraciones futuras
✅ **Mantenimiento automatizado** vía cron jobs

**El sistema está listo para usar en producción** una vez ejecutada la migración de datos.
