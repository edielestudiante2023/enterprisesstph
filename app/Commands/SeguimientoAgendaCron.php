<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SeguimientoAgendaModel;
use App\Models\SeguimientoHistorialModel;
use SendGrid\Mail\Mail;

class SeguimientoAgendaCron extends BaseCommand
{
    protected $group       = 'Seguimiento';
    protected $name        = 'seguimiento:agenda-diario';
    protected $description = 'Envía emails diarios de seguimiento a clientes difíciles de agendar';
    protected $usage       = 'seguimiento:agenda-diario';

    public function run(array $params): void
    {
        CLI::write('=== Seguimiento Agenda Diario ===', 'yellow');
        CLI::write('Hora: ' . date('Y-m-d H:i:s'), 'white');
        CLI::write('');

        $model    = new SeguimientoAgendaModel();
        $historial = new SeguimientoHistorialModel();

        $activos = $model->getActivosConCliente();

        if (empty($activos)) {
            CLI::write('No hay clientes activos en seguimiento.', 'white');
            return;
        }

        CLI::write('Clientes activos: ' . count($activos), 'white');

        $enviados = 0;
        $errores  = 0;

        foreach ($activos as $seg) {
            CLI::write("→ {$seg['nombre_cliente']}... ", 'white', false);

            if (empty($seg['correo_cliente'])) {
                CLI::write('SIN EMAIL', 'red');
                $errores++;
                continue;
            }

            try {
                $html  = $this->buildHtml($seg);
                $plain = $this->buildPlain($seg);
                $this->enviarEmail($seg, $html, $plain);

                $historial->save([
                    'id_seguimiento' => $seg['id'],
                    'id_cliente'     => $seg['id_cliente'],
                    'fecha_envio'    => date('Y-m-d H:i:s'),
                    'estado'         => 'ENVIADO',
                    'detalle'        => 'Correo enviado correctamente.',
                ]);

                CLI::write('ENVIADO', 'green');
                $enviados++;

            } catch (\Exception $e) {
                $historial->save([
                    'id_seguimiento' => $seg['id'],
                    'id_cliente'     => $seg['id_cliente'],
                    'fecha_envio'    => date('Y-m-d H:i:s'),
                    'estado'         => 'ERROR',
                    'detalle'        => $e->getMessage(),
                ]);

                CLI::write('ERROR: ' . $e->getMessage(), 'red');
                log_message('error', 'SeguimientoAgendaCron: ' . $e->getMessage());
                $errores++;
            }
        }

        CLI::write('');
        CLI::write("=== Resultado: {$enviados} enviados | {$errores} errores ===", 'green');
    }

    private function enviarEmail(array $seg, string $html, string $plain): void
    {
        require_once ROOTPATH . 'vendor/autoload.php';

        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) throw new \Exception('SENDGRID_API_KEY no configurada');

        $email = new Mail();
        $email->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'SST Cycloid Talent');
        $email->setSubject($seg['asunto']);
        $email->addTo($seg['correo_cliente'], $seg['nombre_cliente']);
        $email->addBcc('head.consultant.cycloidtalent@gmail.com');
        $email->addBcc('diana.cuestas@cycloidtalent.com');
        $email->addContent('text/plain', $plain);
        $email->addContent('text/html', $html);

        $sg       = new \SendGrid($apiKey);
        $response = $sg->send($email);

        if ($response->statusCode() < 200 || $response->statusCode() >= 300) {
            throw new \Exception('SendGrid error ' . $response->statusCode() . ': ' . $response->body());
        }
    }

    private function buildHtml(array $seg): string
    {
        $opciones = json_decode($seg['opciones_fechas'] ?? '[]', true) ?: [];
        $opcionesHtml = implode('', array_map(
            fn($op) => "<tr><td style='padding:12px;border:1px solid #d9e2ec;'>{$op}</td></tr>",
            $opciones
        ));

        $tablaOpciones = $opciones ? "
            <p style='font-size:15px;color:#2b2b2b;line-height:1.7;'>{$seg['mensaje']}</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0;font-size:14px;'>
                <tr><td style='padding:12px;border:1px solid #d9e2ec;background:#0b3974;color:#fff;font-weight:bold;'>Opciones disponibles</td></tr>
                {$opcionesHtml}
            </table>
        " : "<p style='font-size:15px;color:#2b2b2b;line-height:1.7;'>{$seg['mensaje']}</p>";

        return "
        <div style='margin:0;padding:0;background-color:#f4f7fb;font-family:Arial,Helvetica,sans-serif;'>
          <div style='max-width:700px;margin:0 auto;padding:30px 20px;'>
            <div style='background:#0b3974;border-radius:12px 12px 0 0;padding:24px 30px;color:#ffffff;'>
              <div style='font-size:22px;font-weight:bold;'>SST CYCLOID TALENT</div>
              <div style='font-size:14px;opacity:0.9;margin-top:6px;'>Seguimiento institucional</div>
            </div>
            <div style='background:#ffffff;border:1px solid #dbe5f0;border-top:none;border-radius:0 0 12px 12px;padding:30px;'>
              <div style='background:#f7faff;border-left:5px solid #38b6ff;padding:16px 18px;margin-bottom:18px;border-radius:8px;'>
                <p style='margin:0;font-size:14px;color:#1f2d3d;'><strong>Cliente:</strong> {$seg['nombre_cliente']}</p>
                <p style='margin:4px 0 0 0;font-size:14px;color:#1f2d3d;'><strong>NIT:</strong> {$seg['nit_cliente']}</p>
              </div>
              {$tablaOpciones}
              <p style='font-size:15px;color:#2b2b2b;line-height:1.7;'>Quedamos atentos a su confirmación.</p>
              <div style='margin-top:30px;padding-top:20px;border-top:1px solid #e6edf5;'>
                <p style='margin:0;font-size:14px;color:#2b2b2b;'><strong>{$seg['consultor']}</strong></p>
                <p style='margin:4px 0 0 0;font-size:14px;color:#2b2b2b;'>{$seg['cargo_consultor']}</p>
                <p style='margin:4px 0 0 0;font-size:14px;color:#2b2b2b;'>Cycloid Talent SAS</p>
              </div>
            </div>
          </div>
        </div>";
    }

    private function buildPlain(array $seg): string
    {
        $opciones = json_decode($seg['opciones_fechas'] ?? '[]', true) ?: [];
        $txt  = $seg['mensaje'] . "\n\n";
        foreach ($opciones as $op) $txt .= "- {$op}\n";
        $txt .= "\nQuedamos atentos a su confirmación.\n\n";
        $txt .= $seg['consultor'] . "\n" . $seg['cargo_consultor'] . "\nCycloid Talent SAS";
        return $txt;
    }
}
