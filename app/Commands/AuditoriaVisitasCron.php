<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CicloVisitaModel;
use App\Models\ActaVisitaModel;
use SendGrid\Mail\Mail;

class AuditoriaVisitasCron extends BaseCommand
{
    protected $group       = 'Auditoria';
    protected $name        = 'auditoria:revisar-visitas-diario';
    protected $description = 'Revisa agendamientos del día anterior y envía alertas de cumplimiento/incumplimiento';
    protected $usage       = 'auditoria:revisar-visitas-diario';

    public function run(array $params)
    {
        CLI::write('Iniciando revisión diaria de auditoría de visitas...', 'yellow');

        $cicloModel = new CicloVisitaModel();
        $actaModel  = new ActaVisitaModel();

        $ayer = date('Y-m-d', strtotime('-1 day'));
        CLI::write("Revisando agendamientos del: {$ayer}", 'white');

        // Obtener ciclos agendados para ayer sin procesar
        $ciclos = $cicloModel->getAgendadosAyer();
        CLI::write("Ciclos encontrados: " . count($ciclos), 'white');

        if (empty($ciclos)) {
            CLI::write('No hay agendamientos del día anterior pendientes de revisión.', 'green');
            return;
        }

        $alertas = 0;
        $confirmaciones = 0;
        $errores = 0;

        foreach ($ciclos as $ciclo) {
            $idCliente = (int)$ciclo['id_cliente'];

            // Buscar acta de visita completa para este cliente en la fecha agendada
            $acta = $actaModel->where('id_cliente', $idCliente)
                ->where('fecha_visita', $ayer)
                ->where('estado', 'completo')
                ->first();

            if ($acta) {
                // ═══ CUMPLE AGENDA ═══
                $cicloModel->update($ciclo['id'], [
                    'estatus_agenda'       => 'cumple',
                    'estatus_mes'          => 'cumple',
                    'fecha_acta'           => $acta['fecha_visita'],
                    'id_acta'              => $acta['id'],
                    'confirmacion_enviada' => 1,
                ]);

                // Enviar email de confirmación
                $ok = $this->enviarEmail(
                    $ciclo,
                    'VISITA CUMPLIDA',
                    '#28a745',
                    "El consultor <strong>{$ciclo['nombre_consultor']}</strong> visitó al cliente <strong>{$ciclo['nombre_cliente']}</strong> el <strong>" . date('d/m/Y', strtotime($ayer)) . "</strong> conforme a la agenda. Auditoría validada."
                );

                if ($ok) {
                    $confirmaciones++;
                    CLI::write("  OK: {$ciclo['nombre_cliente']} - Visita cumplida", 'green');
                } else {
                    $errores++;
                }

                // Auto-generar siguiente ciclo
                $estandar = $ciclo['estandar'] ?? $ciclo['estandares'] ?? '';
                if ($estandar) {
                    $newId = $cicloModel->generarSiguienteCiclo(
                        $idCliente,
                        $acta['fecha_visita'],
                        $estandar,
                        (int)$ciclo['id_consultor']
                    );
                    if ($newId) {
                        CLI::write("    Siguiente ciclo generado (id={$newId})", 'white');
                    }
                }

            } else {
                // ═══ INCUMPLE AGENDA ═══
                $cicloModel->update($ciclo['id'], [
                    'estatus_agenda' => 'incumple',
                    'alerta_enviada' => 1,
                ]);

                $ok = $this->enviarEmail(
                    $ciclo,
                    'VISITA NO REALIZADA',
                    '#dc3545',
                    "El consultor <strong>{$ciclo['nombre_consultor']}</strong> NO visitó al cliente <strong>{$ciclo['nombre_cliente']}</strong> el <strong>" . date('d/m/Y', strtotime($ayer)) . "</strong> como estaba agendado. Por favor verificar y reprogramar."
                );

                if ($ok) {
                    $alertas++;
                    CLI::write("  ALERTA: {$ciclo['nombre_cliente']} - Visita NO realizada", 'red');
                } else {
                    $errores++;
                }
            }
        }

        CLI::write('');
        CLI::write('=== RESULTADOS ===', 'green');
        CLI::write("Confirmaciones enviadas: {$confirmaciones}", 'green');
        CLI::write("Alertas enviadas: {$alertas}", $alertas > 0 ? 'red' : 'white');
        CLI::write("Errores: {$errores}", $errores > 0 ? 'red' : 'white');
        CLI::write('Proceso completado.', 'green');
    }

    /**
     * Envía email via SendGrid
     */
    private function enviarEmail(array $ciclo, string $titulo, string $color, string $mensaje): bool
    {
        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            CLI::write("  ERROR: SENDGRID_API_KEY no configurada", 'red');
            return false;
        }

        $fechaStr = date('d/m/Y', strtotime($ciclo['fecha_agendada']));

        $html = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: {$color}; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: #fff; margin: 0; font-size: 20px;'>{$titulo}</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa; border-radius: 0 0 10px 10px;'>
                <p>{$mensaje}</p>
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <tr>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'><strong>Cliente:</strong></td>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'>" . htmlspecialchars($ciclo['nombre_cliente'] ?? '') . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'><strong>Consultor:</strong></td>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'>" . htmlspecialchars($ciclo['nombre_consultor'] ?? '') . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'><strong>Fecha agendada:</strong></td>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'>{$fechaStr}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'><strong>Estándar:</strong></td>
                        <td style='padding: 8px; background: #fff; border: 1px solid #ddd;'>" . htmlspecialchars($ciclo['estandar'] ?? '') . "</td>
                    </tr>
                </table>
                <p style='color: #999; font-size: 11px;'>Generado automáticamente por SG-SST Cycloid Talent.</p>
            </div>
        </div>";

        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - Auditoría SST");
            $email->setSubject("{$titulo} - " . htmlspecialchars($ciclo['nombre_cliente'] ?? '') . " - {$fechaStr}");

            // Destinatarios: consultor + supervisores fijos
            $correoConsultor = $ciclo['correo_consultor'] ?? '';
            if ($correoConsultor) {
                $email->addTo($correoConsultor, $ciclo['nombre_consultor'] ?? 'Consultor');
            }
            $email->addTo('edison.cuervo@cycloidtalent.com', 'Edison Cuervo');
            $email->addTo('diana.cuestas@cycloidtalent.com', 'Diana Cuestas');

            $email->addContent("text/html", $html);

            $sendgrid = new \SendGrid($sendgridApiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return true;
            } else {
                CLI::write("  ERROR SendGrid (status {$response->statusCode()}): {$response->body()}", 'red');
                return false;
            }
        } catch (\Exception $e) {
            CLI::write("  EXCEPTION: " . $e->getMessage(), 'red');
            return false;
        }
    }
}
