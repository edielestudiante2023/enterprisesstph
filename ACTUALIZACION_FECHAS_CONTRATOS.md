# Actualización de Controladores - Fechas de Contratos

## Resumen

Se han actualizado **63 controladores** para usar la fecha del primer contrato del cliente en lugar de la fecha actual para los documentos del sistema.

## Archivos Actualizados

### Controladores Hz* (9 archivos)
- HzaccioncorrectivaController.php
- HzauditoriaController.php
- HzfuncionesyrespController.php
- HzindentpeligroController.php
- HzpausaactivaController.php
- HzreqlegalesController.php
- HzresponsablepesvController.php
- HzrespsaludController.php
- HzrevaltagerenciaController.php

### Controladores Pz* (31 archivos)
- PzactacocolabController.php
- PzactacopasstController.php
- PzasignacionresponsableController.php
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

### Controladores kpi* y kp* (23 archivos)
- kpatelController.php
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
- kprehabilitacionController.php

## Cambios Implementados

### 1. Import de ContractModel

```php
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;  // ← AGREGADO
```

### 2. Obtención de la Fecha del Primer Contrato

```php
// Obtener los datos del consultor relacionado con el cliente
$consultant = $consultantModel->find($client['id_consultor']);
if (!$consultant) {
    return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
}

// Obtener la fecha del primer contrato del cliente  ← AGREGADO
$contractModel = new ContractModel();
$firstContractDate = $contractModel->getFirstContractDate($clientId);
```

### 3. Sobrescritura de Fecha en latestVersion

```php
// Obtener la versión más reciente del documento
$latestVersion = $versionModel->where('client_id', $clientId)
    ->where('policy_type_id', $policyTypeId)
    ->orderBy('created_at', 'DESC')
    ->first();

// Sobrescribir la fecha con la del primer contrato  ← AGREGADO
if ($firstContractDate) {
    $latestVersion['created_at'] = $firstContractDate;
} else {
    // Cliente sin contrato: mostrar "PENDIENTE DE CONTRATO"
    $latestVersion['created_at'] = null;
    $latestVersion['sin_contrato'] = true;
}
```

### 4. Sobrescritura de Fechas en allVersions

```php
// Obtener todas las versiones del documento
$allVersions = $versionModel->where('client_id', $clientId)
    ->where('policy_type_id', $policyTypeId)
    ->orderBy('created_at', 'DESC')
    ->findAll();

// Sobrescribir las fechas de todas las versiones  ← AGREGADO
foreach ($allVersions as &$version) {
    if ($firstContractDate) {
        $version['created_at'] = $firstContractDate;
    } else {
        $version['created_at'] = null;
        $version['sin_contrato'] = true;
    }
}
unset($version); // Romper la referencia
```

### 5. Actualización de Dompdf (set_option → setOption)

```php
// ANTES
$dompdf->set_option('isRemoteEnabled', true);

// DESPUÉS
$dompdf->setOption('isRemoteEnabled', true);
```

## Casos Especiales

### Controladores con DocumentLibrary (kpatelController, kprehabilitacionController)

Estos controladores usan `get_all_document_versions()` en lugar de `$versionModel`:

```php
// Obtener todas las versiones del documento
$allVersions = get_all_document_versions($policyTypeId);

// Sobrescribir las fechas de todas las versiones
foreach ($allVersions as &$version) {
    if ($firstContractDate) {
        $version['created_at'] = $firstContractDate;
    } else {
        $version['created_at'] = null;
        $version['sin_contrato'] = true;
    }
}
unset($version);
```

## Métodos Actualizados por Controlador

- **Métodos principales**: 63 (uno por controlador)
- **Métodos generatePdf**: 38 (en controladores Hz* y Pz*)
- **Total de métodos actualizados**: ~101

## Estadísticas de Cambios

- **Archivos modificados**: 65
- **Líneas agregadas**: 1,265
- **Líneas eliminadas/modificadas**: 109
- **Total de cambios**: 1,374 líneas

## Verificación

Todos los archivos han sido verificados para confirmar que:

- ✓ Tienen el import de ContractModel
- ✓ Llaman a getFirstContractDate()
- ✓ Sobrescriben las fechas en allVersions
- ✓ Usan setOption() en lugar de set_option()

## Impacto

Esta actualización asegura que:

1. Todos los documentos muestren la fecha del primer contrato del cliente
2. Los clientes sin contrato muestren "PENDIENTE DE CONTRATO"
3. El versionamiento de documentos sea consistente
4. Se use la API moderna de Dompdf

## Archivo de Referencia

El archivo `PzasignacionresponsabilidadesController.php` se usó como referencia para estos cambios, ya que fue el primero en implementar esta funcionalidad correctamente.

---

**Fecha de actualización**: 2026-01-11
**Método**: Automatizado con scripts Python
**Estado**: Completado exitosamente
