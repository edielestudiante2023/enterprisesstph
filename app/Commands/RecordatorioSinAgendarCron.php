<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CicloVisitaModel;
use SendGrid\Mail\Mail;

class RecordatorioSinAgendarCron extends BaseCommand
{
    protected $group       = 'Auditoria';
    protected $name        = 'auditoria:recordatorio-sin-agendar';
    protected $description = 'Envía recordatorio diario (L-V, día>=5) de clientes con visita este mes sin agendar';
    protected $usage       = 'auditoria:recordatorio-sin-agendar';

    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function run(array $params)
    {
        $dia       = (int)date('j');
        $diaSemana = (int)date('N'); // 1=lun … 7=dom

        // Solo día >= 5 y lunes a viernes
        if ($dia < 5) {
            CLI::write("Día {$dia}: antes del 5, no se envía recordatorio.", 'yellow');
            return;
        }
        if ($diaSemana > 5) {
            CLI::write('Fin de semana, no se envía recordatorio.', 'yellow');
            return;
        }

        CLI::write('Iniciando recordatorio de visitas sin agendar...', 'yellow');

        $cicloModel = new CicloVisitaModel();
        $pendientes = $cicloModel->getSinAgendarMesActual();

        if (empty($pendientes)) {
            CLI::write('Todos los clientes de este mes ya tienen agenda. Nada que enviar.', 'green');
            return;
        }

        $mesActual  = (int)date('n');
        $anioActual = (int)date('Y');
        $mesNombre  = self::MESES[$mesActual];

        CLI::write("Clientes sin agendar para {$mesNombre} {$anioActual}: " . count($pendientes), 'white');

        // ── Agrupar por consultor interno (id_consultor) ──
        $porConsultor = [];
        foreach ($pendientes as $c) {
            $idCons = (int)$c['id_consultor'];
            $porConsultor[$idCons]['nombre'] = $c['nombre_consultor'] ?? 'Sin consultor';
            $porConsultor[$idCons]['correo'] = $c['correo_consultor'] ?? '';
            $porConsultor[$idCons]['clientes'][] = $c;
        }

        // ── Agrupar por consultor externo (email_consultor_externo) ──
        $porExterno = [];
        foreach ($pendientes as $c) {
            $emailExt = trim($c['email_consultor_externo'] ?? '');
            if ($emailExt === '') {
                continue;
            }
            $nombreExt = $c['consultor_externo'] ?? 'Consultor Externo';
            $porExterno[$emailExt]['nombre'] = $nombreExt;
            $porExterno[$emailExt]['clientes'][] = $c;
        }

        $enviados = 0;
        $errores  = 0;

        // ── Enviar a cada consultor interno ──
        foreach ($porConsultor as $idCons => $info) {
            $destinatarios = [];
            if ($info['correo']) {
                $destinatarios[] = ['email' => $info['correo'], 'name' => $info['nombre']];
            }
            $destinatarios[] = ['email' => 'edison.cuervo@cycloidtalent.com', 'name' => 'Edison Cuervo'];
            $destinatarios[] = ['email' => 'diana.cuestas@cycloidtalent.com', 'name' => 'Diana Cuestas'];

            $ok = $this->enviarRecordatorio(
                $destinatarios,
                $info['clientes'],
                $info['nombre'],
                $mesNombre,
                $anioActual
            );

            if ($ok) {
                $enviados++;
                CLI::write("  Enviado a consultor: {$info['nombre']} ({$info['correo']}) — " . count($info['clientes']) . " clientes", 'green');
            } else {
                $errores++;
                CLI::write("  ERROR enviando a: {$info['nombre']}", 'red');
            }
        }

        // ── Enviar a cada consultor externo ──
        foreach ($porExterno as $emailExt => $info) {
            $destinatarios = [
                ['email' => $emailExt, 'name' => $info['nombre']],
                ['email' => 'edison.cuervo@cycloidtalent.com', 'name' => 'Edison Cuervo'],
                ['email' => 'diana.cuestas@cycloidtalent.com', 'name' => 'Diana Cuestas'],
            ];

            $ok = $this->enviarRecordatorio(
                $destinatarios,
                $info['clientes'],
                $info['nombre'] . ' (Externo)',
                $mesNombre,
                $anioActual
            );

            if ($ok) {
                $enviados++;
                CLI::write("  Enviado a externo: {$info['nombre']} ({$emailExt}) — " . count($info['clientes']) . " clientes", 'green');
            } else {
                $errores++;
                CLI::write("  ERROR enviando a externo: {$info['nombre']}", 'red');
            }
        }

        CLI::write('');
        CLI::write('=== RESULTADOS ===', 'green');
        CLI::write("Emails enviados: {$enviados}", 'green');
        CLI::write("Errores: {$errores}", $errores > 0 ? 'red' : 'white');
        CLI::write('Proceso completado.', 'green');
    }

    /**
     * Envía email consolidado con lista de clientes sin agendar
     */
    private function enviarRecordatorio(array $destinatarios, array $clientes, string $nombreConsultor, string $mes, int $anio): bool
    {
        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            CLI::write("  ERROR: SENDGRID_API_KEY no configurada", 'red');
            return false;
        }

        $totalPendientes = count($clientes);

        // Construir filas de la tabla
        $filas = '';
        foreach ($clientes as $i => $c) {
            $bg = ($i % 2 === 0) ? '#fff' : '#f8f9fa';
            $filas .= "
                <tr style='background:{$bg};'>
                    <td style='padding:8px 12px; border:1px solid #ddd;'>" . htmlspecialchars($c['nombre_cliente'] ?? '') . "</td>
                    <td style='padding:8px 12px; border:1px solid #ddd;'>" . htmlspecialchars($c['estandar'] ?? $c['estandares'] ?? '') . "</td>
                    <td style='padding:8px 12px; border:1px solid #ddd;'>" . htmlspecialchars($c['correo_cliente'] ?? '') . "</td>
                </tr>";
        }

        $html = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 650px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #e67e22, #f39c12); padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: #fff; margin: 0; font-size: 20px;'>RECORDATORIO: VISITAS SIN AGENDAR</h1>
                <p style='color: #fff; margin: 5px 0 0; font-size: 14px;'>{$mes} {$anio}</p>
            </div>
            <div style='padding: 25px; background: #f8f9fa; border-radius: 0 0 10px 10px;'>
                <p>Estimado(a) <strong>{$nombreConsultor}</strong>,</p>
                <p>Los siguientes <strong>{$totalPendientes} cliente(s)</strong> tienen visita programada para <strong>{$mes} {$anio}</strong> y aún <span style='color:#dc3545; font-weight:bold;'>no tienen fecha de visita agendada</span>:</p>

                <table style='width:100%; border-collapse:collapse; margin:15px 0;'>
                    <thead>
                        <tr style='background:#1c2437;'>
                            <th style='padding:10px 12px; color:#bd9751; text-align:left; border:1px solid #ddd;'>Cliente</th>
                            <th style='padding:10px 12px; color:#bd9751; text-align:left; border:1px solid #ddd;'>Estándar</th>
                            <th style='padding:10px 12px; color:#bd9751; text-align:left; border:1px solid #ddd;'>Correo</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$filas}
                    </tbody>
                </table>

                <p style='background:#fff3cd; padding:12px; border-radius:6px; border-left:4px solid #f39c12; font-size:13px;'>
                    Por favor agende las visitas a la brevedad posible. Este recordatorio se enviará diariamente hasta que todos los clientes tengan fecha de visita agendada.
                </p>

                <p style='color: #999; font-size: 11px; margin-top:20px;'>Generado automáticamente por SG-SST Cycloid Talent — " . date('d/m/Y H:i') . "</p>
            </div>
        </div>";

        $subject = "Recordatorio: {$totalPendientes} cliente(s) sin agendar — {$mes} {$anio}";

        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - Auditoría SST");
            $email->setSubject($subject);

            foreach ($destinatarios as $dest) {
                $email->addTo($dest['email'], $dest['name']);
            }

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
