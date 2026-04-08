<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ClientModel;

class ReporteFirmasDiario extends BaseCommand
{
    protected $group       = 'Notificaciones';
    protected $name        = 'firmas:reporte-diario';
    protected $description = 'Envía reporte diario de nuevas firmas de protocolo alturas a Edison';
    protected $usage       = 'firmas:reporte-diario [--dias=1]';

    public function run(array $params)
    {
        $dias = (int) (CLI::getOption('dias') ?: 1);
        $desde = date('Y-m-d 00:00:00', strtotime("-{$dias} days"));

        $clientModel = new ClientModel();

        $nuevasFirmas = $clientModel
            ->where('protocolo_alturas_firmado', 1)
            ->where('firma_alturas_fecha >=', $desde)
            ->orderBy('firma_alturas_fecha', 'DESC')
            ->findAll();

        CLI::write("Firmas nuevas desde {$desde}: " . count($nuevasFirmas), 'white');

        if (empty($nuevasFirmas)) {
            CLI::write('Sin firmas nuevas. No se envía reporte.', 'yellow');
            return;
        }

        // Propagar firmas a contratos sin firma
        $propagadas = 0;
        $contractModel = new \App\Models\ContractModel();
        foreach ($nuevasFirmas as $cliente) {
            if (empty($cliente['firma_representante_legal'])) continue;

            $contratos = $contractModel->where('id_cliente', $cliente['id_cliente'])
                ->groupStart()
                    ->where('firma_cliente_imagen IS NULL')
                    ->orWhere('firma_cliente_imagen', '')
                ->groupEnd()
                ->findAll();

            foreach ($contratos as $contrato) {
                $contractModel->update($contrato['id_contrato'], [
                    'firma_cliente_imagen' => $cliente['firma_representante_legal'],
                    'firma_cliente_fecha'  => $cliente['firma_alturas_fecha'],
                ]);
                $propagadas++;
                CLI::write("  Firma propagada a contrato #{$contrato['id_contrato']} de {$cliente['nombre_cliente']}", 'green');
            }
        }

        $this->enviarEmail($nuevasFirmas, $propagadas, $dias);
    }

    private function enviarEmail(array $firmas, int $propagadas, int $dias): void
    {
        $periodo = $dias === 1 ? 'hoy' : "últimos {$dias} días";

        $html = '<div style="font-family:Arial,sans-serif;max-width:700px;margin:0 auto;">';
        $html .= '<h2 style="color:#2c3e50;">Nuevas Firmas Protocolo Alturas</h2>';
        $html .= '<p>Período: <strong>' . $periodo . '</strong> — ' . date('Y-m-d H:i') . '</p>';

        $html .= '<div style="background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;padding:15px;margin:15px 0;text-align:center;">';
        $html .= '<span style="font-size:28px;font-weight:bold;color:#155724;">' . count($firmas) . '</span>';
        $html .= '<br><span style="color:#155724;">nueva(s) firma(s)</span>';
        $html .= '</div>';

        if ($propagadas > 0) {
            $html .= '<div style="background:#cce5ff;border:1px solid #b8daff;border-radius:8px;padding:10px;margin:10px 0;text-align:center;">';
            $html .= '<span style="color:#004085;"><strong>' . $propagadas . '</strong> contrato(s) actualizados con firma</span>';
            $html .= '</div>';
        }

        $html .= '<table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:15px;">';
        $html .= '<tr style="background:#e9ecef;"><th style="padding:8px;text-align:left;">Cliente</th><th style="padding:8px;">Fecha firma</th><th style="padding:8px;">IP</th></tr>';

        foreach ($firmas as $c) {
            $html .= '<tr style="border-bottom:1px solid #dee2e6;">';
            $html .= '<td style="padding:8px;">' . htmlspecialchars($c['nombre_cliente']) . '</td>';
            $html .= '<td style="padding:8px;text-align:center;">' . ($c['firma_alturas_fecha'] ?? '-') . '</td>';
            $html .= '<td style="padding:8px;text-align:center;">' . ($c['firma_alturas_ip'] ?? '-') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></div>';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Firmas Alturas — " . count($firmas) . " nueva(s) " . date('Y-m-d'));
        $email->addTo('edison.cuervo@cycloidtalent.com', 'Edison Cuervo');
        $email->addContent("text/html", $html);

        try {
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);
            CLI::write("Reporte enviado a Edison (status " . $response->statusCode() . ")", 'green');
        } catch (\Exception $e) {
            CLI::write("Error enviando reporte: " . $e->getMessage(), 'red');
        }
    }
}
