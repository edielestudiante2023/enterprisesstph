<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use SendGrid\Mail\Mail;

class RecordatorioCapacitacionesCron extends BaseCommand
{
    protected $group       = 'Capacitaciones';
    protected $name        = 'capacitaciones:recordatorio-semanal';
    protected $description = 'Envía cada martes a las 8 AM un resumen de capacitaciones PROGRAMADAS del mes actual y siguiente a cada consultor';
    protected $usage       = 'capacitaciones:recordatorio-semanal';

    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function run(array $params)
    {
        // Verificar que sea martes (2 = Tuesday)
        if ((int) date('N') !== 2) {
            CLI::write('Hoy no es martes. No se envía recordatorio.', 'yellow');
            return;
        }

        CLI::write('Iniciando recordatorio semanal de capacitaciones programadas...', 'yellow');

        $db = \Config\Database::connect();

        $mesActual    = (int) date('n');
        $anioActual   = (int) date('Y');
        $mesSiguiente = $mesActual === 12 ? 1 : $mesActual + 1;
        $anioSiguiente = $mesActual === 12 ? $anioActual + 1 : $anioActual;

        $primerDiaMesActual = date('Y-m-01');
        $ultimoDiaMesSig    = date('Y-m-t', strtotime("{$anioSiguiente}-{$mesSiguiente}-01"));

        // Consultar capacitaciones PROGRAMADAS del mes actual y siguiente
        $capacitaciones = $db->table('tbl_cronog_capacitacion cap')
            ->select('cap.*, c.nombre_cliente, c.id_consultor, con.nombre_consultor, con.correo_consultor')
            ->join('tbl_clientes c', 'c.id_cliente = cap.id_cliente')
            ->join('tbl_consultor con', 'con.id_consultor = c.id_consultor', 'left')
            ->where('cap.estado', 'PROGRAMADA')
            ->where('cap.fecha_programada >=', $primerDiaMesActual)
            ->where('cap.fecha_programada <=', $ultimoDiaMesSig)
            ->orderBy('cap.fecha_programada', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($capacitaciones)) {
            CLI::write('No hay capacitaciones programadas para el período. Nada que enviar.', 'green');
            return;
        }

        CLI::write("Capacitaciones programadas encontradas: " . count($capacitaciones), 'white');

        // Agrupar por consultor
        $porConsultor = [];
        foreach ($capacitaciones as $cap) {
            $idCons = (int) ($cap['id_consultor'] ?? 0);
            if ($idCons === 0) continue;
            $porConsultor[$idCons]['nombre'] = $cap['nombre_consultor'] ?? 'Sin consultor';
            $porConsultor[$idCons]['correo'] = $cap['correo_consultor'] ?? '';
            $porConsultor[$idCons]['capacitaciones'][] = $cap;
        }

        $nombreMesActual = self::MESES[$mesActual];
        $nombreMesSig    = self::MESES[$mesSiguiente];

        $enviados = 0;
        $errores  = 0;

        foreach ($porConsultor as $idCons => $data) {
            $correo = trim($data['correo'] ?? '');
            if (!$correo) {
                CLI::write("  ⚠ Consultor #{$idCons} ({$data['nombre']}) sin correo — omitido.", 'yellow');
                continue;
            }

            $caps = $data['capacitaciones'];

            // Separar por mes
            $capsMesActual = array_filter($caps, function ($c) use ($mesActual, $anioActual) {
                return (int) date('n', strtotime($c['fecha_programada'])) === $mesActual
                    && (int) date('Y', strtotime($c['fecha_programada'])) === $anioActual;
            });
            $capsMesSig = array_filter($caps, function ($c) use ($mesSiguiente, $anioSiguiente) {
                return (int) date('n', strtotime($c['fecha_programada'])) === $mesSiguiente
                    && (int) date('Y', strtotime($c['fecha_programada'])) === $anioSiguiente;
            });

            $html = $this->buildHtml(
                $data['nombre'],
                $capsMesActual, $nombreMesActual, $anioActual,
                $capsMesSig, $nombreMesSig, $anioSiguiente
            );

            $subject = "🎓 Capacitaciones programadas — {$nombreMesActual} y {$nombreMesSig} {$anioActual}";

            $ok = $this->enviarEmail($correo, $data['nombre'], $subject, $html);

            if ($ok) {
                CLI::write("  ✓ Enviado a {$data['nombre']} ({$correo}) — " . count($caps) . " capacitaciones.", 'green');
                $enviados++;
            } else {
                CLI::write("  ✗ Error enviando a {$data['nombre']} ({$correo}).", 'red');
                $errores++;
            }
        }

        CLI::write("Resultado: {$enviados} enviados, {$errores} errores.", $errores > 0 ? 'red' : 'green');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTML
    // ─────────────────────────────────────────────────────────────────────────

    private function buildHtml(
        string $nombreConsultor,
        array $capsMesActual, string $nombreMesActual, int $anioActual,
        array $capsMesSig, string $nombreMesSig, int $anioSiguiente
    ): string {
        $totalActual = count($capsMesActual);
        $totalSig    = count($capsMesSig);
        $totalGlobal = $totalActual + $totalSig;

        $html = "
<!DOCTYPE html>
<html lang='es'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#f0f2f5;font-family:Segoe UI,Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f2f5;padding:24px 0;'>
<tr><td align='center'>
<table width='640' cellpadding='0' cellspacing='0' style='max-width:640px;width:100%;'>

  <!-- HEADER -->
  <tr><td style='background:linear-gradient(135deg,#1c2437 0%,#2c3e50 100%);
                 border-radius:12px 12px 0 0;padding:32px 36px;text-align:center;'>
    <div style='font-size:13px;color:#10b981;font-weight:600;letter-spacing:2px;
                text-transform:uppercase;margin-bottom:8px;'>Recordatorio Semanal</div>
    <div style='font-size:24px;font-weight:700;color:#ffffff;margin-bottom:4px;'>🎓 Capacitaciones Programadas</div>
    <div style='font-size:14px;color:#94a3b8;'>Hola, <strong style='color:#bd9751;'>" . htmlspecialchars($nombreConsultor) . "</strong></div>
  </td></tr>

  <!-- RESUMEN -->
  <tr><td style='background:#ffffff;padding:24px 36px 8px;'>
    <div style='font-size:12px;color:#64748b;text-transform:uppercase;font-weight:600;letter-spacing:1px;margin-bottom:16px;'>
      Resumen
    </div>
    <table width='100%' cellpadding='0' cellspacing='0'>
      <tr>
        <td width='33%' style='text-align:center;padding:0 4px 16px;'>
          <div style='background:#f0fdf4;border:1px solid #10b98133;border-radius:8px;padding:12px 8px;'>
            <div style='font-size:28px;font-weight:700;color:#10b981;line-height:1;'>{$totalGlobal}</div>
            <div style='font-size:11px;color:#10b981;font-weight:600;margin-top:4px;'>Total</div>
          </div>
        </td>
        <td width='33%' style='text-align:center;padding:0 4px 16px;'>
          <div style='background:#eff6ff;border:1px solid #3b82f633;border-radius:8px;padding:12px 8px;'>
            <div style='font-size:28px;font-weight:700;color:#3b82f6;line-height:1;'>{$totalActual}</div>
            <div style='font-size:11px;color:#3b82f6;font-weight:600;margin-top:4px;'>{$nombreMesActual}</div>
          </div>
        </td>
        <td width='33%' style='text-align:center;padding:0 4px 16px;'>
          <div style='background:#fefce8;border:1px solid #f59e0b33;border-radius:8px;padding:12px 8px;'>
            <div style='font-size:28px;font-weight:700;color:#f59e0b;line-height:1;'>{$totalSig}</div>
            <div style='font-size:11px;color:#f59e0b;font-weight:600;margin-top:4px;'>{$nombreMesSig}</div>
          </div>
        </td>
      </tr>
    </table>
  </td></tr>

  <!-- DETALLE -->
  <tr><td style='background:#ffffff;padding:8px 36px 32px;'>";

        // Mes actual
        if (!empty($capsMesActual)) {
            $html .= $this->sectionBlock($capsMesActual, "📅 {$nombreMesActual} {$anioActual}", '#3b82f6', '#eff6ff');
        }

        // Mes siguiente
        if (!empty($capsMesSig)) {
            $html .= $this->sectionBlock($capsMesSig, "📅 {$nombreMesSig} {$anioSiguiente}", '#f59e0b', '#fffbeb');
        }

        $html .= "
  </td></tr>

  <!-- FOOTER -->
  <tr><td style='background:#1c2437;border-radius:0 0 12px 12px;padding:20px 36px;text-align:center;'>
    <div style='color:#64748b;font-size:12px;'>
      Este es un mensaje automático del sistema SG-SST · Cycloid Talent<br>
      Enviado cada martes · Por favor no responder a este correo.
    </div>
  </td></tr>

</table>
</td></tr></table>
</body></html>";

        return $html;
    }

    private function sectionBlock(array $caps, string $title, string $color, string $bg): string
    {
        $count = count($caps);
        $html = "
        <div style='margin:20px 0 0;'>
          <div style='background:{$bg};border-left:4px solid {$color};
                      padding:10px 16px;border-radius:0 6px 6px 0;'>
            <span style='font-weight:700;color:{$color};font-size:14px;'>{$title}</span>
            <span style='background:{$color};color:#fff;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;margin-left:8px;'>{$count}</span>
          </div>
        </div>
        <table width='100%' cellpadding='0' cellspacing='0'
               style='border-collapse:collapse;margin:8px 0 4px;font-size:13px;'>
          <thead><tr>
            <th style='background:#f8fafc;color:#475569;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:left;'>Cliente</th>
            <th style='background:#f8fafc;color:#475569;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:left;'>Capacitación</th>
            <th style='background:#f8fafc;color:#475569;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:left;'>Fecha</th>
            <th style='background:#f8fafc;color:#475569;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:left;'>Perfil</th>
            <th style='background:#f8fafc;color:#475569;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:left;'>Duración</th>
          </tr></thead><tbody>";

        foreach ($caps as $row) {
            $dias = (strtotime($row['fecha_programada']) - time()) / 86400;
            $rowBg = '#ffffff';
            if ($dias < 0) $rowBg = '#fff5f5';
            elseif ($dias <= 7) $rowBg = '#fffbeb';

            $html .= "<tr style='background:{$rowBg};'>
                <td style='padding:7px 10px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:top;'>" . htmlspecialchars($row['nombre_cliente'] ?? '') . "</td>
                <td style='padding:7px 10px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:top;'>" . htmlspecialchars($row['nombre_capacitacion'] ?? '') . "</td>
                <td style='padding:7px 10px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:top;'>" . ($row['fecha_programada'] ? date('d/m/Y', strtotime($row['fecha_programada'])) : '') . "</td>
                <td style='padding:7px 10px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:top;'>" . htmlspecialchars($row['perfil_de_asistentes'] ?? '') . "</td>
                <td style='padding:7px 10px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:top;'>" . ($row['horas_de_duracion_de_la_capacitacion'] ?? '') . "h</td>
            </tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENVÍO VÍA SENDGRID
    // ─────────────────────────────────────────────────────────────────────────

    private function enviarEmail(string $to, string $nombre, string $subject, string $html): bool
    {
        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) {
            CLI::write('SENDGRID_API_KEY no configurada.', 'red');
            return false;
        }

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new Mail();
        $email->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'Cycloid Talent - SG-SST');
        $email->setSubject($subject);
        $email->addTo($to, $nombre);
        $email->addCc('diana.cuestas@cycloidtalent.com', 'Diana Cuestas');
        $email->addContent('text/html', $html);

        try {
            $sg = new \SendGrid($apiKey);
            $response = $sg->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return true;
            }

            log_message('error', 'RecordatorioCapacitaciones: SendGrid status ' . $response->statusCode() . ' — ' . $response->body());
            return false;
        } catch (\Exception $e) {
            log_message('error', 'RecordatorioCapacitaciones: Exception — ' . $e->getMessage());
            return false;
        }
    }
}
