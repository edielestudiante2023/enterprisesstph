<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ClientModel;
use App\Models\ContractModel;

class PropagarFirmasExistentes extends BaseCommand
{
    protected $group       = 'Firmas';
    protected $name        = 'firmas:propagar';
    protected $description = 'Propaga firmas de protocolo alturas a contratos sin firma';
    protected $usage       = 'firmas:propagar [--dry-run]';

    public function run(array $params)
    {
        $dryRun = CLI::getOption('dry-run') !== null;

        $clientModel = new ClientModel();
        $contractModel = new ContractModel();

        // Clientes que ya firmaron protocolo alturas
        $firmados = $clientModel
            ->where('protocolo_alturas_firmado', 1)
            ->where('firma_representante_legal IS NOT NULL')
            ->where('firma_representante_legal !=', '')
            ->findAll();

        CLI::write("Clientes con firma protocolo alturas: " . count($firmados), 'cyan');

        if (empty($firmados)) {
            CLI::write('No hay clientes con firma para propagar.', 'yellow');
            return;
        }

        $totalPropagadas = 0;

        foreach ($firmados as $cliente) {
            $nombre = $cliente['nombre_cliente'];
            $firma = $cliente['firma_representante_legal'];

            CLI::write("\n{$nombre}", 'white');
            CLI::write("  Firma: {$firma}", 'light_gray');
            CLI::write("  Fecha: " . ($cliente['firma_alturas_fecha'] ?? '-'), 'light_gray');

            // Buscar contratos sin firma
            $contratos = $contractModel->where('id_cliente', $cliente['id_cliente'])
                ->groupStart()
                    ->where('firma_cliente_imagen IS NULL')
                    ->orWhere('firma_cliente_imagen', '')
                ->groupEnd()
                ->findAll();

            if (empty($contratos)) {
                // Mostrar contratos que YA tienen firma
                $conFirma = $contractModel->where('id_cliente', $cliente['id_cliente'])
                    ->where('firma_cliente_imagen IS NOT NULL')
                    ->where('firma_cliente_imagen !=', '')
                    ->findAll();

                if (!empty($conFirma)) {
                    CLI::write("  Ya tiene firma en " . count($conFirma) . " contrato(s)", 'green');
                } else {
                    CLI::write("  Sin contratos registrados", 'yellow');
                }
                continue;
            }

            foreach ($contratos as $contrato) {
                $numContrato = $contrato['numero_contrato'] ?? "ID:{$contrato['id_contrato']}";

                if ($dryRun) {
                    CLI::write("  [DRY-RUN] Propagaría firma a contrato {$numContrato}", 'yellow');
                } else {
                    $contractModel->update($contrato['id_contrato'], [
                        'firma_cliente_imagen' => $firma,
                        'firma_cliente_fecha'  => $cliente['firma_alturas_fecha'],
                    ]);
                    CLI::write("  Firma propagada a contrato {$numContrato}", 'green');
                }
                $totalPropagadas++;
            }
        }

        CLI::write("\nTotal contratos " . ($dryRun ? 'a propagar' : 'propagados') . ": {$totalPropagadas}", $totalPropagadas > 0 ? 'green' : 'yellow');
    }
}
