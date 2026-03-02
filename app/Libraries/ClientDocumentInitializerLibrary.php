<?php

namespace App\Libraries;

use App\Models\ClientPoliciesModel;
use App\Models\DocumentVersionModel;

/**
 * ClientDocumentInitializerLibrary
 *
 * Inicializa los registros de client_policies y document_versions
 * para un cliente nuevo. Debe llamarse al crear cualquier cliente.
 *
 * Historial: antes esta lógica existía en addClientPost pero fue
 * eliminada por error asumiendo que DocumentLibrary la reemplazaba.
 * Los controllers Pz* siguen requiriendo estos registros en BD.
 */
class ClientDocumentInitializerLibrary
{
    /**
     * Metadatos por policy_type_id: [document_type, acronym]
     * Fuente: estructura de cliente de referencia (id=17)
     */
    private static array $documentMeta = [
        1  => ['FT',  'SST-001'],
        4  => ['FT',  'SST-002'],
        5  => ['FT',  'SST-003'],
        6  => ['FT',  'SST-004'],
        7  => ['FT',  'SST-005'],
        8  => ['FT',  'SST-006'],
        9  => ['FT',  'SST-007'],
        10 => ['FT',  'SST-008'],
        11 => ['FT',  'SST-009'],
        12 => ['FT',  'SST-010'],
        13 => ['FT',  'SST-011'],
        14 => ['MAN', 'SST-001'],
        17 => ['PRC', 'SST-001'],
        18 => ['PRG', 'SST-001'],
        19 => ['PRG', 'SST-002'],
        20 => ['FT',  'SST-012'],
        21 => ['PL',  'SST-001'],
        22 => ['PL',  'SST-002'],
        23 => ['PL',  'SST-003'],
        24 => ['PL',  'SST-004'],
        25 => ['PL',  'SST-005'],
        26 => ['REG', 'SST-001'],
        27 => ['FT',  'SST-013'],
        28 => ['FT',  'SST-014'],
        29 => ['PRC', 'SST-002'],
        30 => ['PRC', 'SST-003'],
        31 => ['MAN', 'SST-002'],
        32 => ['PRC', 'SST-004'],
        33 => ['PRG', 'SST-005'],
        34 => ['PRC', 'SST-006'],
        35 => ['PRG', 'SST-003'],
        36 => ['PRC', 'SST-007'],
        37 => ['FT',  'SST-101'],
        38 => ['FT',  'SST-015'],
        39 => ['PRC', 'SST-008'],
        40 => ['PRC', 'SST-009'],
        41 => ['PRC', 'SST-010'],
        42 => ['PRG', 'SST-004'],
        43 => ['PRG', 'SST-005'],
        44 => ['FT',  'SST-016'],
        45 => ['PRC', 'SST-011'],
        46 => ['MA',  'SST-001'],
    ];

    /**
     * Crea todos los registros de client_policies y document_versions
     * para el cliente dado. No hace nada si ya existen registros.
     *
     * @param int $clientId ID del cliente recién creado
     */
    public static function initialize(int $clientId): void
    {
        $policiesModel  = new ClientPoliciesModel();
        $versionsModel  = new DocumentVersionModel();

        // Verificar que no existan ya registros (evitar duplicados)
        $existing = $policiesModel->where('client_id', $clientId)->countAllResults();
        if ($existing > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $changeControl = 'Elaborado por Cycloid Talent el ' . date('j \d\e F \d\e Y');

        foreach (self::$documentMeta as $policyTypeId => $meta) {
            [$docType, $acronym] = $meta;

            // Insertar client_policy (contenido vacío, se edita desde el panel)
            $policiesModel->insert([
                'client_id'      => $clientId,
                'policy_type_id' => $policyTypeId,
                'policy_content' => '',
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            // Insertar document_version versión 1
            $versionsModel->insert([
                'client_id'      => $clientId,
                'policy_type_id' => $policyTypeId,
                'version_number' => 1,
                'document_type'  => $docType,
                'acronym'        => $acronym,
                'location'       => 'DIGITAL',
                'status'         => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        log_message('info', "ClientDocumentInitializer: documentos inicializados para cliente ID {$clientId}");
    }
}
