<?php

namespace App\Libraries;

use App\Models\ContractModel;
use App\Models\ClientModel;
use App\Models\PlanModel;
use App\Libraries\WorkPlanLibrary;

class ContractLibrary
{
    protected $contractModel;
    protected $clientModel;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->clientModel = new ClientModel();
    }

    /**
     * Crea un nuevo contrato para un cliente
     */
    public function createContract($data, array $options = [])
    {
        $skipInit = $options['skip_init'] ?? false;
        // Validar que el cliente existe
        $client = $this->clientModel->find($data['id_cliente']);
        if (!$client) {
            return [
                'success' => false,
                'message' => 'El cliente no existe'
            ];
        }

        // Generar número de contrato automáticamente si no se proporciona
        if (empty($data['numero_contrato'])) {
            $data['numero_contrato'] = $this->contractModel->generateContractNumber($data['id_cliente']);
        }

        // Determinar el tipo de contrato si no se especifica
        if (empty($data['tipo_contrato'])) {
            $existingContracts = $this->contractModel->where('id_cliente', $data['id_cliente'])->countAllResults();
            $data['tipo_contrato'] = $existingContracts > 0 ? 'renovacion' : 'inicial';
        }

        // Establecer estado por defecto
        if (empty($data['estado'])) {
            $data['estado'] = 'activo';
        }

        // Si es un contrato activo, desactivar otros contratos activos del mismo cliente
        if ($data['estado'] === 'activo') {
            $this->deactivateClientContracts($data['id_cliente']);
        }

        // Guardar el contrato
        if ($this->contractModel->insert($data)) {
            $contractId = $this->contractModel->getInsertID();

            // Actualizar las fechas en tbl_clientes para mantener retrocompatibilidad
            $this->updateClientDates($data['id_cliente']);

            if (!$skipInit) {
                // Activar cliente automáticamente si tiene contrato activo
                if ($data['estado'] === 'activo') {
                    $this->clientModel->update($data['id_cliente'], ['estado' => 'activo']);
                }

                // Auto-generar plan de trabajo para el cliente
                $this->autoGenerateWorkPlan($data['id_cliente'], $data['frecuencia_visitas'] ?? null);
            }

            // Sincronizar estandares en tbl_clientes desde frecuencia_visitas del contrato
            $this->syncEstandaresFromContract($data['id_cliente'], $data['frecuencia_visitas'] ?? null);

            return [
                'success' => true,
                'message' => 'Contrato creado exitosamente',
                'contract_id' => $contractId,
                'contract_number' => $data['numero_contrato']
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al crear el contrato',
            'errors' => $this->contractModel->errors()
        ];
    }

    /**
     * Renueva un contrato (crea uno nuevo basado en el anterior)
     */
    public function renewContract($idContrato, $newEndDate, $valorContrato = null, $observaciones = null)
    {
        $oldContract = $this->contractModel->find($idContrato);

        if (!$oldContract) {
            return [
                'success' => false,
                'message' => 'Contrato no encontrado'
            ];
        }

        // Marcar el contrato anterior como renovado
        $this->contractModel->update($idContrato, ['estado' => 'renovado']);

        // Crear el nuevo contrato heredando todos los campos del anterior
        $newContractData = [
            'id_cliente'                    => $oldContract['id_cliente'],
            'fecha_inicio'                  => date('Y-m-d'),
            'fecha_fin'                     => $newEndDate,
            'valor_contrato'                => $valorContrato ?? $oldContract['valor_contrato'],
            'tipo_contrato'                 => 'renovacion',
            'estado'                        => 'activo',
            'observaciones'                 => $observaciones ?? "Renovación del contrato {$oldContract['numero_contrato']}",
            // Heredar datos del representante legal del cliente
            'nombre_rep_legal_cliente'      => $oldContract['nombre_rep_legal_cliente'] ?? null,
            'cedula_rep_legal_cliente'      => $oldContract['cedula_rep_legal_cliente'] ?? null,
            'direccion_cliente'             => $oldContract['direccion_cliente'] ?? null,
            'telefono_cliente'              => $oldContract['telefono_cliente'] ?? null,
            'email_cliente'                 => $oldContract['email_cliente'] ?? null,
            // Heredar datos del contratista
            'nombre_rep_legal_contratista'  => $oldContract['nombre_rep_legal_contratista'] ?? null,
            'cedula_rep_legal_contratista'  => $oldContract['cedula_rep_legal_contratista'] ?? null,
            'email_contratista'             => $oldContract['email_contratista'] ?? null,
            // Heredar consultor y responsable SST
            'id_consultor_responsable'      => $oldContract['id_consultor_responsable'] ?? null,
            'nombre_responsable_sgsst'      => $oldContract['nombre_responsable_sgsst'] ?? null,
            'cedula_responsable_sgsst'      => $oldContract['cedula_responsable_sgsst'] ?? null,
            'licencia_responsable_sgsst'    => $oldContract['licencia_responsable_sgsst'] ?? null,
            'email_responsable_sgsst'       => $oldContract['email_responsable_sgsst'] ?? null,
            // Heredar condiciones económicas y operativas
            'valor_mensual'                 => $oldContract['valor_mensual'] ?? null,
            'numero_cuotas'                 => $oldContract['numero_cuotas'] ?? null,
            'frecuencia_visitas'            => $oldContract['frecuencia_visitas'] ?? null,
            // Heredar datos bancarios
            'banco'                         => $oldContract['banco'] ?? null,
            'tipo_cuenta'                   => $oldContract['tipo_cuenta'] ?? null,
            'cuenta_bancaria'               => $oldContract['cuenta_bancaria'] ?? null,
            // Heredar cláusula primera si existe
            'clausula_primera_objeto'       => $oldContract['clausula_primera_objeto'] ?? null,
        ];

        return $this->createContract($newContractData);
    }

    /**
     * Obtiene información completa de un contrato con datos del cliente y consultor
     */
    public function getContractWithClient($idContrato)
    {
        // Usar tbl_contratos.* para obtener TODOS los campos del contrato incluyendo id_consultor_responsable
        return $this->contractModel->select('tbl_contratos.*,
                                             tbl_clientes.nombre_cliente,
                                             tbl_clientes.nit_cliente,
                                             tbl_clientes.correo_cliente,
                                             tbl_clientes.telefono_1_cliente,
                                             tbl_consultor.nombre_consultor,
                                             tbl_consultor.firma_consultor')
                                   ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
                                   ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_contratos.id_consultor_responsable', 'left')
                                   ->where('tbl_contratos.id_contrato', $idContrato)
                                   ->first();
    }

    /**
     * Obtiene el historial completo de contratos de un cliente con estadísticas
     */
    public function getClientContractHistory($idCliente)
    {
        $contracts = $this->contractModel->getClientContracts($idCliente);
        $renewals = $this->contractModel->countRenewals($idCliente);
        $firstDate = $this->contractModel->getFirstContractDate($idCliente);
        $antiquity = $this->contractModel->getClientAntiquity($idCliente);

        return [
            'contracts' => $contracts,
            'total_contracts' => count($contracts),
            'total_renewals' => $renewals,
            'first_contract_date' => $firstDate,
            'client_antiquity_months' => $antiquity,
            'client_antiquity_years' => round($antiquity / 12, 1)
        ];
    }

    /**
     * Obtiene alertas de contratos próximos a vencer
     */
    public function getContractAlerts($idConsultor = null, $days = 30)
    {
        $expiringContracts = $this->contractModel->getExpiringContracts($days);

        // Filtrar por consultor si se especifica
        if ($idConsultor) {
            $expiringContracts = array_filter($expiringContracts, function ($contract) use ($idConsultor) {
                $client = $this->clientModel->find($contract['id_cliente']);
                return $client && $client['id_consultor'] == $idConsultor;
            });
        }

        // Calcular días restantes y nivel de urgencia
        foreach ($expiringContracts as &$contract) {
            $fechaFin = new \DateTime($contract['fecha_fin']);
            $hoy = new \DateTime();
            $diferencia = $hoy->diff($fechaFin);
            $diasRestantes = (int)$diferencia->format('%r%a');

            $contract['dias_restantes'] = $diasRestantes;

            if ($diasRestantes <= 7) {
                $contract['urgencia'] = 'alta';
                $contract['color'] = 'danger';
            } elseif ($diasRestantes <= 15) {
                $contract['urgencia'] = 'media';
                $contract['color'] = 'warning';
            } else {
                $contract['urgencia'] = 'baja';
                $contract['color'] = 'info';
            }
        }

        return $expiringContracts;
    }

    /**
     * Cancela un contrato
     */
    public function cancelContract($idContrato, $motivo = null)
    {
        $contract = $this->contractModel->find($idContrato);

        if (!$contract) {
            return [
                'success' => false,
                'message' => 'Contrato no encontrado'
            ];
        }

        $observaciones = $contract['observaciones'];
        if ($motivo) {
            $observaciones .= "\n\nCancelado: " . $motivo . " (Fecha: " . date('Y-m-d H:i:s') . ")";
        }

        if ($this->contractModel->update($idContrato, [
            'estado' => 'cancelado',
            'observaciones' => $observaciones
        ])) {
            // Actualizar las fechas en tbl_clientes
            $this->updateClientDates($contract['id_cliente']);

            return [
                'success' => true,
                'message' => 'Contrato cancelado exitosamente'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al cancelar el contrato'
        ];
    }

    /**
     * Auto-genera el plan de trabajo al crear/renovar un contrato.
     * 1. Elimina actividades ABIERTA del cliente
     * 2. Genera desde CSV sin insertar actividades ya CERRADA en el año actual
     */
    protected function autoGenerateWorkPlan($idCliente, $frecuenciaVisitas = null)
    {
        try {
            $db = \Config\Database::connect();
            $planModel = new PlanModel();

            // 1. Eliminar actividades ABIERTA del cliente
            $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('estado_actividad', 'ABIERTA')
                ->delete();

            // 2. Detectar año SGSST del cliente
            $workPlanLibrary = new WorkPlanLibrary();
            $year = $workPlanLibrary->detectCurrentYear((int)$idCliente) ?? 1;

            // 3. Mapear frecuencia_visitas del contrato al service_type del CSV
            $serviceTypeMap = [
                'MENSUAL'    => 'mensual',
                'BIMENSUAL'  => 'bimensual',
                'TRIMESTRAL' => 'trimestral',
                'SEMESTRAL'  => 'proyecto',
                'ANUAL'      => 'proyecto',
            ];
            $serviceType = $serviceTypeMap[strtoupper($frecuenciaVisitas ?? '')] ?? 'mensual';

            // 4. Obtener actividades del CSV filtradas
            $activities = $workPlanLibrary->getActivities((int)$idCliente, $year, $serviceType);

            // 5. Obtener actividades ya CERRADA en el año actual (por texto exacto)
            $currentYear = date('Y');
            $closedActivities = $db->table('tbl_pta_cliente')
                ->select('actividad_plandetrabajo')
                ->where('id_cliente', $idCliente)
                ->where('estado_actividad', 'CERRADA')
                ->where("YEAR(fecha_propuesta)", $currentYear)
                ->get()
                ->getResultArray();
            $closedSet = array_column($closedActivities, 'actividad_plandetrabajo');

            // 6. Insertar solo actividades cuyo texto no coincida con una cerrada este año
            $inserted = 0;
            foreach ($activities as $activity) {
                if (in_array($activity['actividad_plandetrabajo'], $closedSet)) {
                    continue;
                }
                $planModel->insert($activity);
                $inserted++;
            }

            log_message('info', "AutoGenerateWorkPlan: Cliente {$idCliente}, Año {$year}, Servicio {$serviceType}, Insertadas {$inserted}, Cerradas omitidas " . count($closedSet));
        } catch (\Exception $e) {
            log_message('error', "AutoGenerateWorkPlan error: " . $e->getMessage());
        }
    }

    /**
     * Desactiva todos los contratos activos de un cliente
     */
    protected function deactivateClientContracts($idCliente)
    {
        return $this->contractModel->where('id_cliente', $idCliente)
                                   ->where('estado', 'activo')
                                   ->set(['estado' => 'renovado'])
                                   ->update();
    }

    /**
     * Actualiza las fechas en tbl_clientes basándose en el contrato activo
     * Esto mantiene la retrocompatibilidad con el sistema anterior
     */
    public function updateClientDates($idCliente)
    {
        $activeContract = $this->contractModel->getActiveContract($idCliente);

        if ($activeContract) {
            $this->clientModel->update($idCliente, [
                'fecha_fin_contrato' => $activeContract['fecha_fin']
            ]);

            return true;
        }

        return false;
    }

    /**
     * Sincroniza tbl_clientes.estandares derivándolo de tbl_contratos.frecuencia_visitas.
     * Se llama en createContract(), renewContract() y al editar un contrato,
     * garantizando que ambos campos estén siempre alineados.
     */
    public function syncEstandaresFromContract(int $idCliente, ?string $frecuenciaVisitas): void
    {
        $map = [
            'MENSUAL'    => 'Mensual',
            'BIMENSUAL'  => 'Bimensual',
            'TRIMESTRAL' => 'Trimestral',
            'PROYECTO'   => 'Proyecto',
            'SEMESTRAL'  => 'Proyecto',
            'ANUAL'      => 'Proyecto',
        ];
        $estandares = $map[strtoupper($frecuenciaVisitas ?? '')] ?? null;
        if ($estandares) {
            $this->clientModel->update($idCliente, ['estandares' => $estandares]);
        }
    }

    /**
     * Ejecuta el mantenimiento automático de contratos
     * - Actualiza contratos vencidos
     * - Sincroniza fechas con tbl_clientes
     */
    public function runMaintenance()
    {
        // Actualizar contratos vencidos
        $updatedContracts = $this->contractModel->updateExpiredContracts();

        // Sincronizar fechas con todos los clientes
        $clients = $this->clientModel->findAll();
        $syncedClients = 0;

        foreach ($clients as $client) {
            if ($this->updateClientDates($client['id_cliente'])) {
                $syncedClients++;
            }
        }

        return [
            'expired_contracts_updated' => $updatedContracts,
            'clients_synced' => $syncedClients
        ];
    }

    /**
     * Obtiene estadísticas generales de contratos
     * @param int|null $idConsultor Filtrar por consultor
     * @param string|null $estadoCliente Filtrar por estado del cliente (activo, inactivo, pendiente)
     */
    public function getContractStats($idConsultor = null, $estadoCliente = null)
    {
        $builder = $this->contractModel->builder();

        // Siempre hacer join con clientes para poder filtrar por estado_cliente
        $builder->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente');

        if ($idConsultor) {
            $builder->where('tbl_clientes.id_consultor', $idConsultor);
        }

        // Filtrar por estado del cliente si se especifica
        if ($estadoCliente) {
            $builder->where('tbl_clientes.estado', $estadoCliente);
        }

        $result = $builder->select("
            COUNT(*) as total_contratos,
            SUM(CASE WHEN tbl_contratos.estado = 'activo' THEN 1 ELSE 0 END) as contratos_activos,
            SUM(CASE WHEN tbl_contratos.estado = 'vencido' THEN 1 ELSE 0 END) as contratos_vencidos,
            SUM(CASE WHEN tbl_contratos.estado = 'cancelado' THEN 1 ELSE 0 END) as contratos_cancelados,
            SUM(CASE WHEN tbl_contratos.estado = 'renovado' THEN 1 ELSE 0 END) as contratos_renovados,
            SUM(CASE WHEN tbl_contratos.tipo_contrato = 'renovacion' THEN 1 ELSE 0 END) as total_renovaciones,
            SUM(CASE WHEN tbl_contratos.estado = 'activo' THEN tbl_contratos.valor_contrato ELSE 0 END) as valor_total_activos
        ")->get()->getRowArray();

        $stats = $result ?? [
            'total_contratos' => 0,
            'contratos_activos' => 0,
            'contratos_vencidos' => 0,
            'contratos_cancelados' => 0,
            'contratos_renovados' => 0,
            'total_renovaciones' => 0,
            'valor_total_activos' => 0
        ];

        // Calcular tasa de renovación
        $builderInicial = $this->contractModel->builder();
        $builderInicial->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente');

        if ($idConsultor) {
            $builderInicial->where('tbl_clientes.id_consultor', $idConsultor);
        }

        if ($estadoCliente) {
            $builderInicial->where('tbl_clientes.estado', $estadoCliente);
        }

        $totalInicial = $builderInicial->where('tbl_contratos.tipo_contrato', 'inicial')->countAllResults();

        $stats['tasa_renovacion'] = $totalInicial > 0
            ? round(((int)$stats['total_renovaciones'] / $totalInicial) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Valida si un cliente puede tener un nuevo contrato
     */
    public function canCreateContract($idCliente, $fechaInicio, $fechaFin)
    {
        // Verificar si hay contratos activos que se superpongan
        $overlapping = $this->contractModel->where('id_cliente', $idCliente)
                                          ->where('estado', 'activo')
                                          ->groupStart()
                                              ->where('fecha_inicio <=', $fechaFin)
                                              ->where('fecha_fin >=', $fechaInicio)
                                          ->groupEnd()
                                          ->first();

        if ($overlapping) {
            return [
                'can_create' => false,
                'message' => 'Ya existe un contrato activo que se superpone con las fechas especificadas',
                'overlapping_contract' => $overlapping
            ];
        }

        return [
            'can_create' => true,
            'message' => 'Se puede crear el contrato'
        ];
    }
}
