<?php

namespace App\Libraries;

/**
 * NotificadorVisita
 * Envía email de recordatorio 3 días antes de cada visita agendada.
 *
 * Destinatarios:
 *   TO  → consultor interno + consultor externo (si existe)
 *   CC  → correo_cliente + diana.cuestas@cycloidtalent.com
 *
 * Contenido: 4 secciones de pendientes filtradas por el último día
 * del mes en que cae la visita.
 */
class NotificadorVisita
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENTRY POINT — llamado desde el comando spark
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Busca visitas en 3 días y envía recordatorio para cada una.
     * Retorna array con resultados para logging.
     */
    public function enviarRecordatorios(string $fechaObjetivo = ''): array
    {
        if (!$fechaObjetivo) {
            $fechaObjetivo = date('Y-m-d', strtotime('+3 days'));
        }

        $visitas = $this->db->table('tbl_agendamientos a')
            ->select('a.*, c.nombre_cliente, c.correo_cliente, c.id_consultor,
                      c.consultor_externo, c.email_consultor_externo,
                      con.nombre_consultor, con.correo_consultor')
            ->join('tbl_clientes c', 'c.id_cliente = a.id_cliente', 'left')
            ->join('tbl_consultor con', 'con.id_consultor = c.id_consultor', 'left')
            ->where('a.fecha_visita', $fechaObjetivo)
            ->whereIn('a.estado', ['pendiente', 'confirmado'])
            ->get()->getResultArray();

        $resultados = ['enviados' => 0, 'errores' => 0, 'sin_datos' => 0];

        foreach ($visitas as $visita) {
            $resultado = $this->procesarVisita($visita);
            if ($resultado === 'enviado') {
                $resultados['enviados']++;
            } elseif ($resultado === 'sin_datos') {
                $resultados['sin_datos']++;
            } else {
                $resultados['errores']++;
            }
        }

        return $resultados;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROCESAMIENTO DE UNA VISITA
    // ─────────────────────────────────────────────────────────────────────────

    private function procesarVisita(array $visita): string
    {
        $idCliente     = (int) $visita['id_cliente'];
        $fechaVisita   = $visita['fecha_visita'];
        $ultimoDiaMes  = date('Y-m-t', strtotime($fechaVisita));
        $ultimoDiaMes30 = date('Y-m-d', strtotime($ultimoDiaMes . ' +30 days'));

        // ── Consultar las 4 fuentes ──────────────────────────────────────────
        $planTrabajo   = $this->getPlanTrabajo($idCliente, $ultimoDiaMes);
        $pendientes    = $this->getPendientes($idCliente, $ultimoDiaMes);
        $vencimientos  = $this->getVencimientos($idCliente, $ultimoDiaMes30);
        $capacitaciones = $this->getCapacitaciones($idCliente, $ultimoDiaMes);

        $totalItems = count($planTrabajo) + count($pendientes) + count($vencimientos) + count($capacitaciones);

        if ($totalItems === 0) {
            log_message('info', "NotificadorVisita: Visita #{$visita['id']} cliente {$visita['nombre_cliente']} — sin pendientes, email omitido.");
            return 'sin_datos';
        }

        // ── Construir destinatarios ──────────────────────────────────────────
        $correoConsultor       = trim($visita['correo_consultor'] ?? '');
        $nombreConsultor       = trim($visita['nombre_consultor'] ?? 'Consultor SST');
        $emailConsultorExterno = trim($visita['email_consultor_externo'] ?? '');
        $nombreExterno         = trim($visita['consultor_externo'] ?? 'Consultor Externo');
        $correoCliente         = trim($visita['correo_cliente'] ?? '');
        $nombreCliente         = trim($visita['nombre_cliente'] ?? 'Cliente');

        if (!$correoConsultor && !$emailConsultorExterno) {
            log_message('warning', "NotificadorVisita: Visita #{$visita['id']} — sin correo de consultor, email omitido.");
            return 'error';
        }

        // ── Enviar ───────────────────────────────────────────────────────────
        $html    = $this->buildHtml($visita, $planTrabajo, $pendientes, $vencimientos, $capacitaciones, $ultimoDiaMes);
        $subject = "⚠️ Recordatorio visita SST en 3 días — {$nombreCliente} — " . date('d/m/Y', strtotime($fechaVisita));

        $enviado = $this->enviarEmail($subject, $html, [
            'to_principal'   => $correoConsultor,
            'nombre_principal' => $nombreConsultor,
            'to_externo'     => $emailConsultorExterno,
            'nombre_externo' => $nombreExterno,
            'cc_cliente'     => $correoCliente,
            'nombre_cliente' => $nombreCliente,
        ]);

        if ($enviado) {
            log_message('info', "NotificadorVisita: Email enviado para visita #{$visita['id']} ({$nombreCliente}).");
            return 'enviado';
        }

        return 'error';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS A LAS 4 VISTAS
    // ─────────────────────────────────────────────────────────────────────────

    private function getPlanTrabajo(int $idCliente, string $ultimoDia): array
    {
        return $this->db->table('v_tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('estado_actividad', 'ABIERTA')
            ->where('fecha_propuesta <=', $ultimoDia)
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()->getResultArray();
    }

    private function getPendientes(int $idCliente, string $ultimoDia): array
    {
        return $this->db->table('v_tbl_pendientes')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'ABIERTA')
            ->where('fecha_cierre <=', $ultimoDia)
            ->orderBy('fecha_cierre', 'ASC')
            ->get()->getResultArray();
    }

    private function getVencimientos(int $idCliente, string $hasta): array
    {
        return $this->db->table('v_tbl_vencimientos_mantenimientos')
            ->where('id_cliente', $idCliente)
            ->where('estado_actividad', 'sin ejecutar')
            ->where('fecha_vencimiento <=', $hasta)
            ->orderBy('fecha_vencimiento', 'ASC')
            ->get()->getResultArray();
    }

    private function getCapacitaciones(int $idCliente, string $ultimoDia): array
    {
        return $this->db->table('v_tbl_cronog_capacitacion')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'PROGRAMADA')
            ->where('fecha_programada <=', $ultimoDia)
            ->orderBy('fecha_programada', 'ASC')
            ->get()->getResultArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSTRUCCIÓN DEL HTML
    // ─────────────────────────────────────────────────────────────────────────

    private function buildHtml(array $visita, array $plan, array $pendientes, array $vencimientos, array $capacitaciones, string $ultimoDia): string
    {
        $nombreCliente   = htmlspecialchars($visita['nombre_cliente'] ?? '');
        $fechaVisita     = date('d/m/Y', strtotime($visita['fecha_visita']));
        $horaVisita      = !empty($visita['hora_visita']) ? date('g:i A', strtotime($visita['hora_visita'])) : '';
        $mesAnio         = date('F Y', strtotime($visita['fecha_visita']));
        $nombreConsultor = htmlspecialchars($visita['nombre_consultor'] ?? '');

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
    <div style='font-size:13px;color:#bd9751;font-weight:600;letter-spacing:2px;
                text-transform:uppercase;margin-bottom:8px;'>Recordatorio de Visita SST</div>
    <div style='font-size:26px;font-weight:700;color:#ffffff;margin-bottom:4px;'>{$nombreCliente}</div>
    <div style='font-size:15px;color:#94a3b8;'>Visita programada en <strong style='color:#bd9751;'>3 días</strong></div>
  </td></tr>

  <!-- INFO VISITA -->
  <tr><td style='background:#1e293b;padding:20px 36px;'>
    <table width='100%' cellpadding='0' cellspacing='0'>
      <tr>
        <td style='color:#94a3b8;font-size:13px;padding:6px 0;'>📅 <strong style='color:#e2e8f0;'>Fecha de visita:</strong></td>
        <td style='color:#bd9751;font-size:14px;font-weight:600;text-align:right;padding:6px 0;'>{$fechaVisita}" . ($horaVisita ? " · {$horaVisita}" : "") . "</td>
      </tr>
      <tr>
        <td style='color:#94a3b8;font-size:13px;padding:6px 0;'>👤 <strong style='color:#e2e8f0;'>Consultor:</strong></td>
        <td style='color:#e2e8f0;font-size:13px;text-align:right;padding:6px 0;'>{$nombreConsultor}</td>
      </tr>
      <tr>
        <td style='color:#94a3b8;font-size:13px;padding:6px 0;'>📋 <strong style='color:#e2e8f0;'>Corte de pendientes:</strong></td>
        <td style='color:#e2e8f0;font-size:13px;text-align:right;padding:6px 0;'>Hasta el " . date('d/m/Y', strtotime($ultimoDia)) . " ({$mesAnio})</td>
      </tr>
    </table>
  </td></tr>

  <!-- RESUMEN CONTEOS -->
  <tr><td style='background:#ffffff;padding:24px 36px 8px;'>
    <div style='font-size:12px;color:#64748b;text-transform:uppercase;font-weight:600;letter-spacing:1px;margin-bottom:16px;'>
      Resumen de pendientes para la visita
    </div>
    <table width='100%' cellpadding='0' cellspacing='0'>
      <tr>
        " . $this->badgeCount(count($plan), 'Plan de Trabajo', '#3b82f6', '#eff6ff') . "
        " . $this->badgeCount(count($pendientes), 'Compromisos', '#f59e0b', '#fffbeb') . "
        " . $this->badgeCount(count($vencimientos), 'Vencimientos', '#ef4444', '#fef2f2') . "
        " . $this->badgeCount(count($capacitaciones), 'Capacitaciones', '#10b981', '#f0fdf4') . "
      </tr>
    </table>
  </td></tr>

  <!-- CUERPO SECCIONES -->
  <tr><td style='background:#ffffff;padding:8px 36px 32px;'>
";

        // SECCIÓN 1 — Plan de Trabajo
        if (!empty($plan)) {
            $html .= $this->sectionHeader('📌 Plan de Trabajo Abierto', '#3b82f6', '#eff6ff', count($plan));
            $html .= $this->tableStart(['Actividad', 'Responsable', 'Fecha Propuesta', 'Avance']);
            foreach ($plan as $row) {
                $html .= $this->tableRow([
                    htmlspecialchars($row['actividad_plandetrabajo'] ?? ''),
                    htmlspecialchars($row['responsable_definido_paralaactividad'] ?? $row['responsable_sugerido_plandetrabajo'] ?? ''),
                    $row['fecha_propuesta'] ? date('d/m/Y', strtotime($row['fecha_propuesta'])) : '',
                    ($row['porcentaje_avance'] ?? '0') . '%',
                ], $this->rowBg($row['fecha_propuesta']));
            }
            $html .= $this->tableEnd();
        }

        // SECCIÓN 2 — Compromisos/Pendientes
        if (!empty($pendientes)) {
            $html .= $this->sectionHeader('⏳ Compromisos Abiertos', '#f59e0b', '#fffbeb', count($pendientes));
            $html .= $this->tableStart(['Tarea / Actividad', 'Responsable', 'Fecha Cierre', 'Días']);
            foreach ($pendientes as $row) {
                $html .= $this->tableRow([
                    htmlspecialchars($row['tarea_actividad'] ?? ''),
                    htmlspecialchars($row['responsable'] ?? ''),
                    $row['fecha_cierre'] ? date('d/m/Y', strtotime($row['fecha_cierre'])) : '',
                    ($row['conteo_dias'] ?? '') . ' días',
                ], $this->rowBg($row['fecha_cierre']));
            }
            $html .= $this->tableEnd();
        }

        // SECCIÓN 3 — Vencimientos
        if (!empty($vencimientos)) {
            $html .= $this->sectionHeader('🔧 Vencimientos / Mantenimientos', '#ef4444', '#fef2f2', count($vencimientos));
            $html .= $this->tableStart(['Mantenimiento', 'Fecha Vencimiento', 'Responsable']);
            foreach ($vencimientos as $row) {
                $html .= $this->tableRow([
                    htmlspecialchars($row['nombre_mantenimiento'] ?? ''),
                    $row['fecha_vencimiento'] ? date('d/m/Y', strtotime($row['fecha_vencimiento'])) : '',
                    htmlspecialchars($row['nombre_consultor'] ?? ''),
                ], $this->rowBg($row['fecha_vencimiento']));
            }
            $html .= $this->tableEnd();
        }

        // SECCIÓN 4 — Capacitaciones
        if (!empty($capacitaciones)) {
            $html .= $this->sectionHeader('🎓 Capacitaciones Programadas', '#10b981', '#f0fdf4', count($capacitaciones));
            $html .= $this->tableStart(['Capacitación', 'Fecha Programada', 'Perfil Asistentes', 'Duración']);
            foreach ($capacitaciones as $row) {
                $html .= $this->tableRow([
                    htmlspecialchars($row['nombre_capacitacion'] ?? $row['nombre_capacitacion_catalogo'] ?? ''),
                    $row['fecha_programada'] ? date('d/m/Y', strtotime($row['fecha_programada'])) : '',
                    htmlspecialchars($row['perfil_de_asistentes'] ?? ''),
                    ($row['horas_de_duracion_de_la_capacitacion'] ?? '') . 'h',
                ], $this->rowBg($row['fecha_programada']));
            }
            $html .= $this->tableEnd();
        }

        $html .= "
  </td></tr>

  <!-- FOOTER -->
  <tr><td style='background:#1c2437;border-radius:0 0 12px 12px;padding:20px 36px;text-align:center;'>
    <div style='color:#64748b;font-size:12px;'>
      Este es un mensaje automático del sistema SG-SST · Cycloid Talent<br>
      Por favor no responder a este correo.
    </div>
  </td></tr>

</table>
</td></tr></table>
</body></html>";

        return $html;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS DE HTML
    // ─────────────────────────────────────────────────────────────────────────

    private function badgeCount(int $n, string $label, string $color, string $bg): string
    {
        return "<td width='25%' style='text-align:center;padding:0 4px 16px;'>
            <div style='background:{$bg};border:1px solid {$color}33;border-radius:8px;padding:12px 8px;'>
              <div style='font-size:28px;font-weight:700;color:{$color};line-height:1;'>{$n}</div>
              <div style='font-size:11px;color:{$color};font-weight:600;margin-top:4px;'>{$label}</div>
            </div>
          </td>";
    }

    private function sectionHeader(string $title, string $color, string $bg, int $count): string
    {
        return "<div style='margin:20px 0 0;'>
          <div style='background:{$bg};border-left:4px solid {$color};
                      padding:10px 16px;border-radius:0 6px 6px 0;
                      display:flex;justify-content:space-between;align-items:center;'>
            <span style='font-weight:700;color:{$color};font-size:14px;'>{$title}</span>
            <span style='background:{$color};color:#fff;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;'>{$count}</span>
          </div>
        </div>";
    }

    private function tableStart(array $headers): string
    {
        $ths = '';
        foreach ($headers as $h) {
            $ths .= "<th style='background:#f8fafc;color:#475569;font-size:11px;font-weight:600;
                                text-transform:uppercase;letter-spacing:.5px;padding:8px 10px;
                                border-bottom:2px solid #e2e8f0;text-align:left;'>{$h}</th>";
        }
        return "<table width='100%' cellpadding='0' cellspacing='0'
                       style='border-collapse:collapse;margin:8px 0 4px;font-size:13px;'>
                  <thead><tr>{$ths}</tr></thead><tbody>";
    }

    private function tableRow(array $cells, string $bg = '#ffffff'): string
    {
        $tds = '';
        foreach ($cells as $c) {
            $tds .= "<td style='padding:7px 10px;border-bottom:1px solid #f1f5f9;
                                color:#334155;vertical-align:top;'>{$c}</td>";
        }
        return "<tr style='background:{$bg};'>{$tds}</tr>";
    }

    private function tableEnd(): string
    {
        return "</tbody></table>";
    }

    /** Fondo amarillo si vence en ≤7 días, rojo si ya venció */
    private function rowBg(?string $fecha): string
    {
        if (!$fecha) return '#ffffff';
        $diff = (strtotime($fecha) - time()) / 86400;
        if ($diff < 0)  return '#fff5f5';   // vencido — rojo suave
        if ($diff <= 7) return '#fffbeb';   // próximo — amarillo suave
        return '#ffffff';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENVÍO VÍA SENDGRID
    // ─────────────────────────────────────────────────────────────────────────

    private function enviarEmail(string $subject, string $html, array $dest): bool
    {
        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) {
            log_message('error', 'NotificadorVisita: SENDGRID_API_KEY no configurada.');
            return false;
        }

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'Cycloid Talent - SG-SST');
        $email->setSubject($subject);

        // TO — consultor interno
        if (!empty($dest['to_principal'])) {
            $email->addTo($dest['to_principal'], $dest['nombre_principal']);
        }
        // TO — consultor externo
        if (!empty($dest['to_externo'])) {
            $email->addTo($dest['to_externo'], $dest['nombre_externo']);
        }

        // CC — cliente
        if (!empty($dest['cc_cliente'])) {
            $email->addCc($dest['cc_cliente'], $dest['nombre_cliente']);
        }
        // CC — fijo
        $email->addCc('diana.cuestas@cycloidtalent.com', 'Diana Cuestas');

        $email->addContent('text/html', $html);

        try {
            $sg       = new \SendGrid($apiKey);
            $response = $sg->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return true;
            }

            log_message('error', 'NotificadorVisita: SendGrid status ' . $response->statusCode() . ' — ' . $response->body());
            return false;
        } catch (\Exception $e) {
            log_message('error', 'NotificadorVisita: Exception — ' . $e->getMessage());
            return false;
        }
    }
}
