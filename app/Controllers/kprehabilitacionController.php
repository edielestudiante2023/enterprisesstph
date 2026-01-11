<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;
// Ya no usamos ClientPoliciesModel, DocumentVersionModel, PolicyTypeModel (migrado a DocumentLibrary.php)
use App\Models\ClientKpiModel;
use App\Models\KpisModel;
use App\Models\KpiDefinitionModel;
use App\Models\DataOwnerModel;
use App\Models\VariableNumeratorModel;
use App\Models\VariableDenominatorModel;
use App\Models\KpiTypeModel;


use Dompdf\Dompdf;

use CodeIgniter\Controller;

class kprehabilitacionController extends Controller
{



    public function rehabilitacionKpi()
    {
        // Cargar helper para acceso a DocumentLibrary
        helper('document_library');

        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
                                $clientKpiModel = new ClientKpiModel();
        $kpisModel = new KpisModel();
        $kpiDefinitionModel = new KpiDefinitionModel();
        $dataOwnerModel = new DataOwnerModel();
        $numeratorModel = new VariableNumeratorModel();
        $denominatorModel = new VariableDenominatorModel();
        $kpiTypeModel = new KpiTypeModel();


        // Obtener los datos del cliente
        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del cliente');
        }

        // Obtener los datos del consultor relacionado con el cliente
        $consultant = $consultantModel->find($client['id_consultor']);
        if (!$consultant) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
        }


        // Obtener la fecha del primer contrato del cliente
        $contractModel = new ContractModel();
        $firstContractDate = $contractModel->getFirstContractDate($clientId);
        // Obtener la política de alcohol y drogas del cliente
        $policyTypeId = 46; // Supongamos que el ID de la política de alcohol y drogas es 1
        $id_kpis = 16; // Primer indicador: Plan de Trabajo Anual
        
        

        // Obtener el tipo de política
        $policyType = get_document($policyTypeId);
        if (!$policyType) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento.');
        }

        // Obtener la versión más reciente del documento
        $latestVersion = $policyType;

        // Sobrescribir la fecha con la del primer contrato (o mostrar pendiente si no hay)
        if ($firstContractDate) {
            $latestVersion['created_at'] = $firstContractDate;
        } else {
            // Cliente sin contrato: mostrar \"PENDIENTE DE CONTRATO\"
            $latestVersion['created_at'] = null;
            $latestVersion['sin_contrato'] = true;
        }


        

        // Obtener todas las versiones del documento
        $allVersions = get_all_document_versions($policyTypeId);

        // Sobrescribir las fechas de todas las versiones con la del primer contrato
        foreach ($allVersions as &$version) {
            if ($firstContractDate) {
                $version['created_at'] = $firstContractDate;
            } else {
                $version['created_at'] = null;
                $version['sin_contrato'] = true;
            }
        }
        unset($version); // Romper la referencia


        

        $clientKpi = $clientKpiModel->where('id_cliente', $clientId)
            ->where('id_kpis', $id_kpis)
            ->first();

        if (!$clientKpi) {
            return redirect()->to('/dashboardclient')->with('error', 'KPI no encontrado');
        }

        // Obtener la definición del KPI
        $kpiDefinition = $kpiDefinitionModel->find($clientKpi['id_kpi_definition']);
        $kpiData = $kpisModel->find($id_kpis);
        $kpiType = $kpiTypeModel->find($clientKpi['id_kpi_type']);
        if (!$kpiType) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró el tipo de KPI');
        }

        // Obtener los datos del responsable del dato
        $dataOwner = $dataOwnerModel->find($clientKpi['id_data_owner']);

        // Obtener numeradores y denominadores para los periodos de medición
        $sumNumerador = 0;
        $countNumerador = 0;
        $sumDenominador = 0;
        $countDenominador = 0;
        $sumIndicadores = 0;
        $countIndicadores = 0;

        $periodos = [];
        for ($i = 1; $i <= 12; $i++) {
            $numerador = $numeratorModel->find($clientKpi['variable_numerador_' . $i]);
            $denominador = $denominatorModel->find($clientKpi['variable_denominador_' . $i]);

            $datoNumerador = $clientKpi['dato_variable_numerador_' . $i];
            $datoDenominador = $clientKpi['dato_variable_denominador_' . $i];
            $valorIndicador = $clientKpi['valor_indicador_' . $i];

            // Sumar los valores del numerador y denominador, omitiendo los ceros
            if ($datoNumerador != 0) {
                $sumNumerador += $datoNumerador;
                $countNumerador++;
            }

            if ($datoDenominador != 0) {
                $sumDenominador += $datoDenominador;
                $countDenominador++;
            }

            // Sumar el valor del indicador, omitiendo los ceros
            if ($valorIndicador != 0) {
                $sumIndicadores += $valorIndicador;
                $countIndicadores++;
            }


            $periodos[] = [
                'numerador' => $numerador['numerator_variable_text'] ?? 'No definido',
                'denominador' => $denominador['denominator_variable_text'] ?? 'No definido',
                'dato_variable_numerador' => $clientKpi['dato_variable_numerador_' . $i],
                'dato_variable_denominador' => $clientKpi['dato_variable_denominador_' . $i],
                'valor_indicador' => $clientKpi['valor_indicador_' . $i],
            ];
        }

        // Calcular los promedios de los numeradores, denominadores y el valor real (indicador)
        $promedioNumerador = $countNumerador > 0 ? ($sumNumerador / $countNumerador) : 0;
        $promedioDenominador = $countDenominador > 0 ? ($sumDenominador / $countDenominador) : 0;
        $promedioIndicadores = $countIndicadores > 0 ? ($sumIndicadores / $countIndicadores) : 0;



        // Calcular el gran total del indicador
        $granTotalIndicador = $clientKpi['gran_total_indicador']; // O usar el promedio de indicadores ya calculado


        // Obtener el seguimiento y análisis de datos
        $analisis_datos = $clientKpi['analisis_datos'];
        $seguimiento1 = $clientKpi['seguimiento1'];
        $seguimiento2 = $clientKpi['seguimiento2'];
        $seguimiento3 = $clientKpi['seguimiento3'];


        // Para compatibilidad con vistas que usan $clientPolicy
        $clientPolicy = [
            'policy_content' => $policyType['default_content'] ?? ''
        ];

        // Pasar los datos a la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
            'clientKpi' => $clientKpi,
            'kpiDefinition' => $kpiDefinition,
            'kpiData' => $kpiData,
            'dataOwner' => $dataOwner,
            'kpiType' => $kpiType,
            'periodos' => $periodos,
            'analisis_datos' => $analisis_datos,
            'seguimiento1' => $seguimiento1,
            'seguimiento2' => $seguimiento2,
            'seguimiento3' => $seguimiento3,
            'clientKpi' => $clientKpi,
            'periodos' => $periodos,
            'promedioNumerador' => $promedioNumerador,
            'promedioDenominador' => $promedioDenominador,
            'granTotalIndicador' => $granTotalIndicador,
            'promedioIndicadores' => $promedioIndicadores,
        ];

        return view('client/sgsst/kpi/krehabilitacion', $data);
    }
}
