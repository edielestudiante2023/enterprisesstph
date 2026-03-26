<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\FirmaAlturasController;
use App\Models\ClientModel;

class ProtocoloAlturas extends BaseCommand
{
    protected $group       = 'Notificaciones';
    protected $name        = 'firmas:protocolo-alturas';
    protected $description = 'Envía el protocolo de trabajo en alturas a clientes activos para firma';
    protected $usage       = 'firmas:protocolo-alturas [--id=5] [--dry-run] [--recordatorio]';

    public function run(array $params)
    {
        $idFiltro    = CLI::getOption('id') ?: ($params['id'] ?? null);
        $dryRun      = CLI::getOption('dry-run') !== null || isset($params['dry-run']);
        $recordatorio = CLI::getOption('recordatorio') !== null || isset($params['recordatorio']);

        $clientModel = new ClientModel();

        if ($recordatorio) {
            // Modo recordatorio: solo clientes que NO han firmado y tienen token vigente
            CLI::write('=== RECORDATORIO: Clientes que no han firmado ===', 'yellow');
            $clientes = $clientModel
                ->where('estado', 'Activo')
                ->where('protocolo_alturas_firmado', 0)
                ->where('token_firma_alturas IS NOT NULL')
                ->findAll();
        } elseif ($idFiltro) {
            $clientes = [$clientModel->find($idFiltro)];
        } else {
            // Envío masivo: todos los activos que NO han firmado
            $clientes = $clientModel
                ->where('estado', 'Activo')
                ->where('protocolo_alturas_firmado', 0)
                ->findAll();
        }

        $total = count($clientes);
        CLI::write("Clientes a procesar: {$total}", 'white');

        if ($dryRun) {
            CLI::write('=== DRY RUN ===', 'yellow');
            foreach ($clientes as $c) {
                CLI::write("  " . ($c['nombre_cliente'] ?? '?') . " => " . ($c['correo_cliente'] ?? 'SIN EMAIL'), 'white');
            }
            return;
        }

        $ok = 0;
        $err = 0;

        foreach ($clientes as $i => $cliente) {
            if (!$cliente) continue;
            $pos = $i + 1;
            $nombre = $cliente['nombre_cliente'] ?? '?';

            if ($recordatorio) {
                // Para recordatorio: notificar al consultor, no al cliente
                $this->notificarConsultorPendiente($cliente);
                CLI::write("  [{$pos}/{$total}] {$nombre} => Consultor notificado", 'yellow');
                $ok++;
                continue;
            }

            $result = FirmaAlturasController::enviarProtocolo((int)$cliente['id_cliente']);

            if ($result['success']) {
                CLI::write("  [{$pos}/{$total}] {$nombre} => OK", 'green');
                $ok++;
            } else {
                CLI::write("  [{$pos}/{$total}] {$nombre} => ERROR: " . $result['error'], 'red');
                $err++;
            }
        }

        CLI::write("\nEnviados: {$ok} | Errores: {$err}", $err > 0 ? 'red' : 'green');
    }

    /**
     * Notifica al consultor asignado que el cliente no ha firmado
     */
    private function notificarConsultorPendiente(array $cliente): void
    {
        if (empty($cliente['id_consultor'])) return;

        $consultorModel = new \App\Models\ConsultantModel();
        $consultor = $consultorModel->find($cliente['id_consultor']);
        if (!$consultor || empty($consultor['correo_consultor'])) return;

        $diasPendiente = '';
        if (!empty($cliente['token_firma_alturas_exp'])) {
            $exp = strtotime($cliente['token_firma_alturas_exp']);
            $diasRestantes = (int)(($exp - time()) / 86400);
            $diasPendiente = " (vence en {$diasRestantes} días)";
        }

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Pendiente: " . $cliente['nombre_cliente'] . " no ha firmado protocolo de alturas");
        $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
        $email->addContent("text/html",
            "<p>El cliente <strong>" . htmlspecialchars($cliente['nombre_cliente']) .
            "</strong> aún no ha firmado el Protocolo de Notificación de Trabajo en Alturas{$diasPendiente}.</p>" .
            "<p>Correo del administrador: " . htmlspecialchars($cliente['correo_cliente'] ?? 'No registrado') . "</p>" .
            "<p>Por favor haga seguimiento.</p>"
        );

        try {
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $sendgrid->send($email);
        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor pendiente alturas: ' . $e->getMessage());
        }
    }
}
