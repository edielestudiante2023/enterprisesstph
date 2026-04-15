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
    protected $usage       = 'firmas:protocolo-alturas [--id=5] [--dry-run] [--recordatorio] [--reporte]';

    public function run(array $params)
    {
        $idFiltro    = CLI::getOption('id') ?: ($params['id'] ?? null);
        $dryRun      = CLI::getOption('dry-run') !== null || isset($params['dry-run']);
        $recordatorio = CLI::getOption('recordatorio') !== null || isset($params['recordatorio']);
        $reporte     = CLI::getOption('reporte') !== null || isset($params['reporte']);

        $clientModel = new ClientModel();

        if ($reporte) {
            // Primero: enviar protocolo a clientes activos que nunca recibieron el email
            $sinEnviar = $clientModel
                ->where('estado', 'Activo')
                ->where('protocolo_alturas_firmado', 0)
                ->where('token_firma_alturas IS NULL')
                ->findAll();

            if (!empty($sinEnviar)) {
                CLI::write("Enviando protocolo a " . count($sinEnviar) . " cliente(s) sin enviar...", 'yellow');
                foreach ($sinEnviar as $c) {
                    $nombre = $c['nombre_cliente'] ?? '?';
                    $result = FirmaAlturasController::enviarProtocolo((int) $c['id_cliente']);
                    if ($result['success']) {
                        CLI::write("  ✓ {$nombre} => Enviado", 'green');
                    } else {
                        CLI::write("  ✗ {$nombre} => ERROR: " . $result['error'], 'red');
                    }
                }
            } else {
                CLI::write("No hay clientes sin enviar.", 'green');
            }

            // Luego: generar y enviar el reporte de estado
            $this->enviarReporteEstado($clientModel);
            return;
        }

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

    /**
     * Envía reporte consolidado de estado de firmas a Edison
     */
    private function enviarReporteEstado(ClientModel $clientModel): void
    {
        CLI::write('=== REPORTE DE ESTADO ===', 'cyan');

        $firmados = $clientModel->where('estado', 'Activo')->where('protocolo_alturas_firmado', 1)->findAll();
        $pendientes = $clientModel->where('estado', 'Activo')->where('protocolo_alturas_firmado', 0)->findAll();
        $sinEnviar = $clientModel->where('estado', 'Activo')->where('protocolo_alturas_firmado', 0)->where('token_firma_alturas IS NULL')->findAll();
        $enviados = $clientModel->where('estado', 'Activo')->where('protocolo_alturas_firmado', 0)->where('token_firma_alturas IS NOT NULL')->findAll();

        CLI::write("Firmados: " . count($firmados), 'green');
        CLI::write("Enviados sin firmar: " . count($enviados), 'yellow');
        CLI::write("Sin enviar: " . count($sinEnviar), 'red');

        // Construir HTML
        $html = '<div style="font-family:Arial,sans-serif;max-width:700px;margin:0 auto;">';
        $html .= '<h2 style="color:#2c3e50;">Reporte Protocolo Trabajo en Alturas</h2>';
        $html .= '<p>Fecha: ' . date('Y-m-d H:i') . '</p>';

        $html .= '<table style="width:100%;border-collapse:collapse;margin:15px 0;">';
        $html .= '<tr style="background:#28a745;color:white;"><td style="padding:10px;font-size:20px;text-align:center;" colspan="2"><strong>' . count($firmados) . '</strong> Firmados</td></tr>';
        $html .= '<tr style="background:#ffc107;"><td style="padding:10px;font-size:20px;text-align:center;" colspan="2"><strong>' . count($enviados) . '</strong> Enviados sin firmar</td></tr>';
        $html .= '<tr style="background:#dc3545;color:white;"><td style="padding:10px;font-size:20px;text-align:center;" colspan="2"><strong>' . count($sinEnviar) . '</strong> Sin enviar</td></tr>';
        $html .= '</table>';

        if (!empty($firmados)) {
            $html .= '<h3 style="color:#28a745;">Firmados</h3><table style="width:100%;border-collapse:collapse;font-size:13px;">';
            $html .= '<tr style="background:#e9ecef;"><th style="padding:6px;text-align:left;">Cliente</th><th style="padding:6px;">Fecha firma</th></tr>';
            foreach ($firmados as $c) {
                $html .= '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:6px;">' . htmlspecialchars($c['nombre_cliente']) . '</td><td style="padding:6px;text-align:center;">' . ($c['firma_alturas_fecha'] ?? '-') . '</td></tr>';
            }
            $html .= '</table>';
        }

        if (!empty($enviados)) {
            $html .= '<h3 style="color:#ffc107;">Pendientes (email enviado)</h3><table style="width:100%;border-collapse:collapse;font-size:13px;">';
            $html .= '<tr style="background:#e9ecef;"><th style="padding:6px;text-align:left;">Cliente</th><th style="padding:6px;">Correo</th><th style="padding:6px;">Vence</th></tr>';
            foreach ($enviados as $c) {
                $html .= '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:6px;">' . htmlspecialchars($c['nombre_cliente']) . '</td><td style="padding:6px;">' . htmlspecialchars($c['correo_cliente'] ?? '') . '</td><td style="padding:6px;text-align:center;">' . substr($c['token_firma_alturas_exp'] ?? '', 0, 10) . '</td></tr>';
            }
            $html .= '</table>';
        }

        $html .= '</div>';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Reporte Protocolo Alturas — " . count($firmados) . " firmados, " . count($enviados) . " pendientes");
        $email->addTo('edison.cuervo@cycloidtalent.com', 'Edison Cuervo');
        $email->addContent("text/html", $html);

        try {
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);
            CLI::write("Reporte enviado a edison.cuervo@cycloidtalent.com (status " . $response->statusCode() . ")", 'green');
        } catch (\Exception $e) {
            CLI::write("Error enviando reporte: " . $e->getMessage(), 'red');
        }
    }
}
