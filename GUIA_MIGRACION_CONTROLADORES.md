# Guía de Migración de Controladores a DocumentLibrary

## 🎯 OBJETIVO

Migrar los 69 controladores que actualmente consultan `client_policies` y `document_versions` en la BD, para que lean directamente de `DocumentLibrary.php` (librería estática PHP).

---

## ✅ CONTROLADOR YA MIGRADO (EJEMPLO)

**Archivo:** `app/Controllers/HzaccioncorrectivaController.php`

Este controlador ya fue actualizado y sirve como referencia para los demás.

---

## 📋 PATRÓN DE MIGRACIÓN

### ANTES (Código Antiguo - BD)

```php
<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClientPoliciesModel; // ❌ ELIMINAR
use App\Models\DocumentVersionModel; // ❌ ELIMINAR
use App\Models\PolicyTypeModel; // ❌ ELIMINAR

use Dompdf\Dompdf;
use CodeIgniter\Controller;

class MiController extends Controller
{
    public function miMetodo()
    {
        $session = session();
        $clientId = $session->get('user_id');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel(); // ❌ ELIMINAR
        $policyTypeModel = new PolicyTypeModel(); // ❌ ELIMINAR
        $versionModel = new DocumentVersionModel(); // ❌ ELIMINAR

        $client = $clientModel->find($clientId);
        $consultant = $consultantModel->find($client['id_consultor']);

        // ❌ ELIMINAR - Busca en BD por cliente
        $policyTypeId = 20; // Ejemplo
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->first();

        // ❌ ELIMINAR - Busca tipo en BD
        $policyType = $policyTypeModel->find($policyTypeId);

        // ❌ ELIMINAR - Busca versiones en BD por cliente
        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();

        // ❌ ELIMINAR - Busca todas las versiones en BD por cliente
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->findAll();

        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions
        ];

        return view('mi_vista', $data);
    }
}
```

---

### DESPUÉS (Código Nuevo - Librería PHP)

```php
<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
// ✅ Ya no necesitas ClientPoliciesModel, DocumentVersionModel ni PolicyTypeModel

use Dompdf\Dompdf;
use CodeIgniter\Controller;

class MiController extends Controller
{
    public function miMetodo()
    {
        // ✅ Cargar helper para acceso a DocumentLibrary
        helper('document_library');

        $session = session();
        $clientId = $session->get('user_id');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        // ✅ Ya no instancias ClientPoliciesModel, PolicyTypeModel ni DocumentVersionModel

        $client = $clientModel->find($clientId);
        $consultant = $consultantModel->find($client['id_consultor']);

        // ✅ ID del documento (el mismo que antes)
        $policyTypeId = 20;

        // ✅ Obtener documento desde librería estática (1 línea en vez de 3)
        $policyType = get_document($policyTypeId);

        // ✅ latestVersion es lo mismo que policyType
        $latestVersion = $policyType;

        // ✅ Obtener todas las versiones (siempre 1 versión para Propiedad Horizontal)
        $allVersions = get_all_document_versions($policyTypeId);

        // ✅ Los datos se pasan igual a la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions
        ];

        return view('mi_vista', $data);
    }
}
```

---

## 🔄 RESUMEN DE CAMBIOS

| Antes (BD) | Después (Librería) |
|------------|---------------------|
| `use App\Models\ClientPoliciesModel;` | ❌ Eliminar |
| `use App\Models\DocumentVersionModel;` | ❌ Eliminar |
| `use App\Models\PolicyTypeModel;` | ❌ Eliminar (opcional) |
| `$clientPoliciesModel = new ClientPoliciesModel();` | ❌ Eliminar |
| `$versionModel = new DocumentVersionModel();` | ❌ Eliminar |
| `$policyTypeModel = new PolicyTypeModel();` | ❌ Eliminar (opcional) |
| `$clientPoliciesModel->where('client_id'...` | ✅ `helper('document_library');` |
| `$policyTypeModel->find($id)` | ✅ `get_document($id)` |
| `$versionModel->where('client_id'...->first()` | ✅ `get_document($id)` |
| `$versionModel->where('client_id'...->findAll()` | ✅ `get_all_document_versions($id)` |

---

## 📝 CHECKLIST POR CONTROLADOR

Para cada controlador:

1. **Eliminar imports:**
   - ❌ `use App\Models\ClientPoliciesModel;`
   - ❌ `use App\Models\DocumentVersionModel;`
   - ❌ `use App\Models\PolicyTypeModel;` (opcional)

2. **Agregar helper al inicio del método:**
   - ✅ `helper('document_library');`

3. **Eliminar instancias:**
   - ❌ `$clientPoliciesModel = new ClientPoliciesModel();`
   - ❌ `$versionModel = new DocumentVersionModel();`
   - ❌ `$policyTypeModel = new PolicyTypeModel();` (opcional)

4. **Reemplazar queries:**
   - ❌ `$clientPoliciesModel->where('client_id', ...)->where('policy_type_id', ...)->first();`
   - ✅ `get_document($policyTypeId);`

5. **Reemplazar búsqueda de tipo:**
   - ❌ `$policyTypeModel->find($policyTypeId);`
   - ✅ `get_document($policyTypeId);`

6. **Reemplazar latest version:**
   - ❌ `$versionModel->where('client_id', ...)->where('policy_type_id', ...)->first();`
   - ✅ `$policyType;` (es lo mismo)

7. **Reemplazar all versions:**
   - ❌ `$versionModel->where('client_id', ...)->findAll();`
   - ✅ `get_all_document_versions($policyTypeId);`

8. **Eliminar validaciones innecesarias:**
   - ❌ `if (!$clientPolicy) return redirect()->with('error', 'No se encontró para este cliente');`
   - ✅ `if (!$policyType) return redirect()->with('error', 'Documento no encontrado');`

9. **Eliminar variables obsoletas en el array $data:**
   - ❌ `'clientPolicy' => $clientPolicy,`
   - ✅ Ya no es necesario, la vista usa `$policyType` directamente

---

## 📂 LISTA DE CONTROLADORES A MIGRAR

Según el grep anterior, estos son los 69 archivos que usan `ClientPoliciesModel`:

```
app/Controllers/HzaccioncorrectivaController.php ✅ MIGRADO (ejemplo)
app/Controllers/PzvigiaController.php
app/Controllers/PzsaneamientoController.php
app/Controllers/PzpoliticaeppsController.php
app/Controllers/HzfuncionesyrespController.php
app/Controllers/PzasignacionresponsableController.php
app/Controllers/PolicyController.php (⚠️ Este es especial, revisar aparte)
app/Controllers/kpivigepidemiologicaController.php
app/Controllers/kprehabilitacionController.php
app/Controllers/kpitodoslosobjetivosController.php
app/Controllers/kpitresperiodosController.php
app/Controllers/kpiprevalenciaController.php
app/Controllers/kpiseisperiodosController.php
app/Controllers/kpimortalidadController.php
app/Controllers/kpiplandetrabajoController.php
app/Controllers/kpigestionriesgoController.php
app/Controllers/kpiincidenciaController.php
app/Controllers/kpiindicefrecuenciaController.php
app/Controllers/kpiindiceseveridadController.php
app/Controllers/kpimipvrdcController.php
app/Controllers/kpiestructuraController.php
app/Controllers/kpievinicialController.php
app/Controllers/kpicapacitacionController.php
app/Controllers/kpicuatroperiodosController.php
app/Controllers/kpicumplilegalController.php
app/Controllers/kpidoceperiodosController.php
app/Controllers/kpianualController.php
app/Controllers/kpiausentismoController.php
app/Controllers/kpatelController.php
app/Controllers/kpiaccpreventivaController.php
app/Controllers/SGSSTPlanear.php
app/Controllers/PzrendicionController.php
app/Controllers/PzrepoaccidenteController.php
app/Controllers/PzreghigsegindController.php
app/Controllers/PzregistroasistenciaController.php
app/Controllers/PzprgcapacitacionController.php
app/Controllers/PzprginduccionController.php
app/Controllers/PzquejacocolabController.php
app/Controllers/PzprccocolabController.php
app/Controllers/PzpoliticapesvController.php
app/Controllers/PzpoliticasstController.php
app/Controllers/PzpoliticaalcoholController.php
app/Controllers/PzpoliticaemergenciasController.php
app/Controllers/PzmanproveedoresController.php
app/Controllers/PzmedpreventivaController.php
app/Controllers/PzobjetivosController.php
app/Controllers/PzinscripcioncocolabController.php
app/Controllers/PzinscripcioncopasstController.php
app/Controllers/PzmanconvivencialaboralController.php
app/Controllers/PzinpeccionplanynoplanController.php
app/Controllers/PzftevaluacioninduccionController.php
app/Controllers/PzexoneracioncocolabController.php
app/Controllers/PzformatodeasistenciaController.php
app/Controllers/PzdocumentacionController.php
app/Controllers/PzcomunicacionController.php
app/Controllers/PzconfidencialidadcocolabController.php
app/Controllers/PzasignacionresponsabilidadesController.php
app/Controllers/PzactacopasstController.php
app/Controllers/PzactacocolabController.php
app/Controllers/Prueba1Controller.php
app/Controllers/HzrespsaludController.php
app/Controllers/HzrevaltagerenciaController.php
app/Controllers/HzpausaactivaController.php
app/Controllers/HzreqlegalesController.php
app/Controllers/HzresponsablepesvController.php
app/Controllers/HzauditoriaController.php
app/Controllers/HzindentpeligroController.php
```

---

## ⚠️ CASOS ESPECIALES

### PolicyController.php

Este controlador gestiona la CRUD de políticas. Tiene dos opciones:

**Opción A: Deprecar completamente**
- Si ya NO usas la interfaz web para crear/editar políticas
- Comentar todos los métodos y mostrar mensaje "Funcionalidad deprecada"

**Opción B: Mantener para editar DocumentLibrary.php**
- Si quieres interfaz web para editar la librería
- Refactorizar para que edite el archivo PHP directamente
- Más complejo, probablemente innecesario

---

## 🧪 CÓMO PROBAR

Después de migrar un controlador:

1. **Accede a la ruta** en tu navegador local
2. **Verifica que la vista carga correctamente**
3. **Revisa que se muestran:**
   - Datos del cliente (logo, nombre)
   - Tipo de documento (document_type, acronym)
   - Versiones en el footer

Si la vista carga bien, **el controlador está migrado correctamente**.

---

## 🚀 PROCESO RECOMENDADO

1. **Empieza con 1 controlador** (ya hicimos `HzaccioncorrectivaController`)
2. **Prueba en local** que funcione
3. **Si funciona**, aplica el mismo patrón a los demás
4. **Como todos siguen el mismo patrón**, deberían funcionar igual

---

## 📞 FUNCIONES HELPER DISPONIBLES

```php
helper('document_library');

// Obtener un documento
$doc = get_document($id);

// Obtener última versión (alias de get_document)
$latest = get_latest_version($id);

// Obtener todas las versiones (siempre array con 1 elemento)
$versions = get_all_document_versions($id);

// Obtener tipo de política (alias de get_document)
$type = get_policy_type($id);

// Verificar si existe
if (document_exists($id)) { ... }
```

---

## ✅ BENEFICIOS DE LA MIGRACIÓN

- ✅ **Cero queries** a BD innecesarias
- ✅ **Más rápido** (arrays PHP en memoria)
- ✅ **Más simple** (menos código)
- ✅ **Centralizado** (un solo archivo para editar)
- ✅ **Sin duplicación** (no 44 registros por cliente)

---

**Fecha:** 2025-01-09
**Autor:** Claude Code
**Versión:** 1.0
