# Fechas en Documentos SST - Propiedad Horizontal

## 📅 Cómo Funcionan las Fechas en los Documentos

### Fecha que se Muestra en los Documentos

**Cada cliente ve la fecha de inicio de su PRIMER CONTRATO**

La fecha que aparece en:
- Encabezado de documentos ("Fecha: XX de mes de YYYY")
- Historial de versiones
- PDFs generados

**NO es una fecha fija del sistema**, sino que es **específica para cada cliente** basada en cuándo firmó su primer contrato.

---

## 🔍 De Dónde Viene la Fecha

### Origen de la Fecha

La fecha se obtiene de la tabla `tbl_contratos` en la base de datos:

```sql
SELECT fecha_inicio
FROM tbl_contratos
WHERE id_cliente = :clientId
ORDER BY fecha_inicio ASC
LIMIT 1
```

**Modelo:** `ContractModel::getFirstContractDate($clientId)`
**Ubicación:** `app/Models/ContractModel.php` líneas 111-118

---

## 💡 Ejemplos

### Cliente A
- Primer contrato: `2023-05-15`
- Todos sus documentos mostrarán: **"15 de mayo de 2023"**

### Cliente B
- Primer contrato: `2024-08-20`
- Todos sus documentos mostrarán: **"20 de agosto de 2024"**

### Cliente C
- Primer contrato: `2025-01-09`
- Todos sus documentos mostrarán: **"09 de enero de 2025"**

---

## ⚙️ Implementación Técnica

### En los Controladores

Todos los controladores (Pz*, Hz*, kpi*) siguen este patrón:

```php
use App\Models\ContractModel;

public function nombreDelMetodo()
{
    // ... código previo para obtener $clientId, $client, $consultant ...

    // Obtener fecha del primer contrato del cliente para documentos
    $contractModel = new ContractModel();
    $firstContractDate = $contractModel->getFirstContractDate($clientId);
    $documentDate = $firstContractDate ?? date('Y-m-d H:i:s');

    // ... obtener el documento ...

    $latestVersion = $policyType;
    // Usar fecha del primer contrato del cliente
    $latestVersion['created_at'] = $documentDate;

    // ... resto del código ...
}
```

### Fallback (si no hay contrato)

Si un cliente NO tiene contratos registrados, se usa la fecha actual del sistema:

```php
$documentDate = $firstContractDate ?? date('Y-m-d H:i:s');
```

---

## 📊 Controladores Actualizados

**Total: 61 controladores**

### Planear (31 controladores Pz*)
- PzasignacionresponsableController.php
- PzasignacionresponsabilidadesController.php
- PzactacocolabController.php
- PzactacopasstController.php
- PzcomunicacionController.php
- PzconfidencialidadcocolabController.php
- PzdocumentacionController.php
- PzexoneracioncocolabController.php
- PzformatodeasistenciaController.php
- PzftevaluacioninduccionController.php
- PzinpeccionplanynoplanController.php
- PzinscripcioncocolabController.php
- PzinscripcioncopasstController.php
- PzmanconvivencialaboralController.php
- PzmanproveedoresController.php
- PzmedpreventivaController.php
- PzobjetivosController.php
- PzpoliticaalcoholController.php
- PzpoliticaemergenciasController.php
- PzpoliticaeppsController.php
- PzpoliticapesvController.php
- PzpoliticasstController.php
- PzprccocolabController.php
- PzprgcapacitacionController.php
- PzprginduccionController.php
- PzquejacocolabController.php
- PzreghigsegindController.php
- PzregistroasistenciaController.php
- PzrendicionController.php
- PzrepoaccidenteController.php
- PzsaneamientoController.php
- PzvigiaController.php

### Hacer (9 controladores Hz*)
- HzaccioncorrectivaController.php
- HzauditoriaController.php
- HzfuncionesyrespController.php
- HzindentpeligroController.php
- HzpausaactivaController.php
- HzreqlegalesController.php
- HzresponsablepesvController.php
- HzrespsaludController.php
- HzrevaltagerenciaController.php

### Indicadores (21 controladores kpi*)
- kpiaccpreventivaController.php
- kpianualController.php
- kpiausentismoController.php
- kpicapacitacionController.php
- kpicuatroperiodosController.php
- kpicumplilegalController.php
- kpidoceperiodosController.php
- kpiestructuraController.php
- kpievinicialController.php
- kpigestionriesgoController.php
- kpiincidenciaController.php
- kpiindicefrecuenciaController.php
- kpiindiceseveridadController.php
- kpimipvrdcController.php
- kpimortalidadController.php
- kpiplandetrabajoController.php
- kpiprevalenciaController.php
- kpiseisperiodosController.php
- kpitodoslosobjetivosController.php
- kpitresperiodosController.php
- kpivigepidemiologicaController.php

---

## ✅ Ventajas de Este Sistema

1. **Personalización por Cliente**: Cada cliente ve su fecha real de inicio
2. **Trazabilidad**: La fecha refleja cuándo comenzó realmente el servicio
3. **Coherencia**: Todos los documentos del mismo cliente tienen la misma fecha base
4. **Cumplimiento Legal**: Refleja la fecha de vigencia del contrato real

---

## 🔧 Cómo Cambiar la Fecha de un Cliente

Si necesitas cambiar la fecha que aparece en los documentos de un cliente:

### Opción 1: Modificar el Primer Contrato (Recomendado)

1. Accede a la base de datos
2. Actualiza `tbl_contratos`:
   ```sql
   UPDATE tbl_contratos
   SET fecha_inicio = '2024-06-15'
   WHERE id_cliente = 123
     AND id_contrato = (
       SELECT id_contrato
       FROM tbl_contratos
       WHERE id_cliente = 123
       ORDER BY fecha_inicio ASC
       LIMIT 1
     );
   ```

### Opción 2: Crear un Contrato Anterior

Si quieres que los documentos muestren una fecha anterior, crea un contrato con una `fecha_inicio` más antigua que los existentes.

---

## 🚨 Casos Especiales

### Cliente sin Contratos

Si un cliente no tiene contratos en `tbl_contratos`, el sistema usa la fecha actual:

```php
$documentDate = $firstContractDate ?? date('Y-m-d H:i:s');
```

**Solución:** Crea al menos un contrato para el cliente con la fecha deseada.

### Múltiples Contratos

Si un cliente tiene varios contratos, se usa **siempre el más antiguo** (primer contrato):

```php
->orderBy('fecha_inicio', 'ASC')
->first();
```

---

## 📝 Historial de Cambios

### 2026-01-10
- ✅ Migración completada: 61 controladores actualizados
- ✅ Todos los documentos ahora usan fecha del primer contrato
- ✅ Eliminada dependencia de fecha estática en DocumentLibrary.php

### Antes (2025-01-09)
- ❌ Todos los clientes veían la misma fecha: "09 de enero de 2025"
- ❌ Fecha fija definida en `DocumentLibrary.php` línea 40
- ❌ No reflejaba la fecha real del cliente

---

## 🔍 Verificación

Para verificar qué fecha verá un cliente específico:

```sql
SELECT
    c.id_cliente,
    c.nombre_cliente,
    MIN(con.fecha_inicio) as primera_fecha_contrato,
    DATE_FORMAT(MIN(con.fecha_inicio), '%d de %M de %Y') as fecha_formateada
FROM clients c
LEFT JOIN tbl_contratos con ON c.id_cliente = con.id_cliente
WHERE c.id_cliente = :clientId
GROUP BY c.id_cliente;
```

---

**Última actualización:** 2026-01-10
**Migración realizada por:** Claude Code (Sonnet 4.5)
