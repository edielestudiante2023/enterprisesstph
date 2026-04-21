<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use SendGrid\Mail\Mail;

class ReclasificarSinRespuestaCron extends BaseCommand
{
    protected $group       = 'Pendientes';
    protected $name        = 'pendientes:reclasificar-sin-respuesta';
    protected $description = 'Reclasifica ABIERTAS con >90 dias post-plazo a SIN RESPUESTA DEL CLIENTE y notifica.';
    protected $usage       = 'pendientes:reclasificar-sin-respuesta [--dry-run]';

    public function run(array $params)
    {
        $dryRun = in_array('--dry-run', $params, true);
        if ($dryRun) CLI::write('[DRY-RUN] No se hara UPDATE ni envio real.', 'yellow');

        $db = \Config\Database::connect();

        $pendientes = $db->table('tbl_pendientes p')
            ->select('p.*, c.nombre_cliente, c.correo_cliente, c.id_consultor, c.consultor_externo, c.email_consultor_externo, con.nombre_consultor, con.correo_consultor')
            ->join('tbl_clientes c', 'c.id_cliente = p.id_cliente')
            ->join('tbl_consultor con', 'con.id_consultor = c.id_consultor', 'left')
            ->where('p.estado', 'ABIERTA')
            ->where('p.fecha_plazo IS NOT NULL', null, false)
            ->where("CAST(p.fecha_plazo AS CHAR) <> '0000-00-00'", null, false)
            ->where("p.fecha_plazo >= '2000-01-01'", null, false)
            ->where('p.fecha_plazo < DATE_SUB(CURDATE(), INTERVAL 90 DAY)', null, false)
            ->where('p.fecha_reclasificacion_auto IS NULL', null, false)
            ->orderBy('p.fecha_plazo', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($pendientes)) {
            CLI::write('No hay pendientes para reclasificar.', 'green');
            log_message('info', '[reclasificar-sin-respuesta] 0 candidatos hoy ' . date('Y-m-d'));
            return;
        }

        CLI::write('Pendientes candidatos: ' . count($pendientes), 'yellow');
        log_message('info', '[reclasificar-sin-respuesta] ' . count($pendientes) . ' candidatos hoy ' . date('Y-m-d'));
        foreach ($pendientes as $p) {
            log_message('info', "[reclasificar-sin-respuesta] candidato id={$p['id_pendientes']} cliente={$p['id_cliente']} plazo={$p['fecha_plazo']} asignacion={$p['fecha_asignacion']} tarea=\"" . mb_substr($p['tarea_actividad'] ?? '', 0, 60) . "\"");
        }

        $porCliente = [];
        foreach ($pendientes as $p) {
            $idCli = (int) $p['id_cliente'];
            if (!isset($porCliente[$idCli])) {
                $porCliente[$idCli]['cliente'] = $p;
                $porCliente[$idCli]['pendientes'] = [];
            }
            $porCliente[$idCli]['pendientes'][] = $p;
        }

        $enviados = 0;
        $reclasificados = 0;
        $errores = 0;

        foreach ($porCliente as $idCli => $info) {
            $pend    = $info['pendientes'];
            $cliente = $info['cliente'];
            $nombre  = $cliente['nombre_cliente'] ?? "cliente #{$idCli}";
            $n       = count($pend);

            $ok = $dryRun ? true : $this->enviarEmail($cliente, $pend);

            if (!$ok) {
                $errores++;
                CLI::write("  ERROR email: {$nombre}", 'red');
                continue;
            }

            $enviados++;

            if (!$dryRun) {
                $ids = array_column($pend, 'id_pendientes');
                $db->table('tbl_pendientes')
                    ->whereIn('id_pendientes', $ids)
                    ->update([
                        'estado' => 'SIN RESPUESTA DEL CLIENTE',
                        'fecha_cierre' => date('Y-m-d'),
                        'fecha_reclasificacion_auto' => date('Y-m-d'),
                    ]);
                $reclasificados += $n;
                log_message('warning', "[reclasificar-sin-respuesta] RECLASIFICADOS ids=" . implode(',', $ids) . " cliente={$cliente['id_cliente']} nombre=\"{$nombre}\"");
            }

            CLI::write("  OK: {$nombre} - {$n} pendientes", 'green');
        }

        CLI::write('');
        CLI::write('=== RESULTADOS ===', 'green');
        CLI::write("Clientes notificados: {$enviados}", 'white');
        CLI::write("Pendientes reclasificados: {$reclasificados}", 'white');
        CLI::write("Errores: {$errores}", $errores > 0 ? 'red' : 'white');
    }

    private function enviarEmail(array $cliente, array $pendientes): bool
    {
        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) {
            CLI::write('  ERROR: SENDGRID_API_KEY no configurada', 'red');
            return false;
        }

        $correoCliente   = trim($cliente['correo_cliente']   ?? '');
        $correoConsultor = trim($cliente['correo_consultor'] ?? '');
        $correoExterno   = trim($cliente['email_consultor_externo'] ?? '');
        $nombreCliente   = $cliente['nombre_cliente'] ?? 'Cliente';
        $nombreConsultor = $cliente['nombre_consultor'] ?? ($cliente['consultor_externo'] ?? 'Consultor');

        $destinatarios = [];
        $usados = [];
        $agregar = function ($email, $nombre) use (&$destinatarios, &$usados) {
            $e = strtolower(trim($email));
            if ($e && filter_var($e, FILTER_VALIDATE_EMAIL) && !isset($usados[$e])) {
                $destinatarios[] = ['email' => $email, 'name' => $nombre];
                $usados[$e] = true;
            }
        };
        $agregar($correoCliente,   $nombreCliente);
        $agregar($correoConsultor, $nombreConsultor);
        $agregar($correoExterno,   $cliente['consultor_externo'] ?? 'Consultor Externo');
        $agregar('edison.cuervo@cycloidtalent.com', 'Edison Cuervo');
        $agregar('diana.cuestas@cycloidtalent.com', 'Diana Cuestas');

        if (empty($destinatarios)) {
            CLI::write("  SKIP: {$nombreCliente} sin emails validos", 'yellow');
            return false;
        }

        $totalPend = count($pendientes);
        $fechaHoy  = date('d/m/Y');
        $tablaHtml = $this->construirTabla($pendientes);

        $subject = "Actividades reclasificadas por ausencia de gestion ({$totalPend}) - {$nombreCliente}";

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:700px;margin:0 auto;padding:15px;'>
            <div style='background:#1c2437;padding:15px;text-align:center;border-radius:8px 8px 0 0;'>
                <h2 style='color:#bd9751;margin:0;'>Reclasificacion Automatica de Actividades</h2>
            </div>
            <div style='background:#fff;padding:20px;border:1px solid #ddd;border-top:none;border-radius:0 0 8px 8px;'>
                <p style='margin:0 0 10px;'>Estimado(a) <strong>{$nombreCliente}</strong>,</p>
                <p>Las siguientes <strong>{$totalPend} actividad(es)</strong> han transcurrido mas de <strong>90 dias</strong> despues de su plazo de cierre sin gestion registrada.</p>
                <p style='background:#fff3cd;border-left:4px solid #ffc107;padding:10px 15px;margin:15px 0;'>Por esta razon, fueron <strong>reclasificadas automaticamente al estado SIN RESPUESTA DEL CLIENTE</strong>.</p>
                <p>Si usted cuenta con <strong>soportes que corroboren la gestion correspondiente</strong>, por favor remitalos via email a la brevedad para reabrir el seguimiento.</p>
                {$tablaHtml}
                <p style='color:#999;font-size:11px;margin-top:20px;border-top:1px solid #eee;padding-top:10px;'>Mensaje automatico generado el {$fechaHoy}. No responda a este correo.</p>
            </div>
        </div>";

        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
            $email->setSubject($subject);
            foreach ($destinatarios as $d) {
                $email->addTo($d['email'], $d['name']);
            }
            $email->addContent("text/html", $html);

            $sendgrid = new \SendGrid($apiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return true;
            }
            CLI::write("  ERROR SendGrid status {$response->statusCode()}: {$response->body()}", 'red');
            return false;
        } catch (\Exception $e) {
            CLI::write("  EXCEPTION: " . $e->getMessage(), 'red');
            return false;
        }
    }

    private function construirTabla(array $pendientes): string
    {
        $rows = '';
        foreach ($pendientes as $i => $p) {
            $bg     = ($i % 2 === 0) ? '#fff9e6' : '#fff3cd';
            $tarea  = htmlspecialchars(mb_substr($p['tarea_actividad'] ?? '', 0, 120));
            if (mb_strlen($p['tarea_actividad'] ?? '') > 120) $tarea .= '...';
            $resp   = htmlspecialchars($p['responsable'] ?? '');
            $plazo  = !empty($p['fecha_plazo']) ? date('d/m/Y', strtotime($p['fecha_plazo'])) : '-';
            $diasV  = !empty($p['fecha_plazo']) ? (int) ((time() - strtotime($p['fecha_plazo'])) / 86400) : 0;
            $rows .= "
                <tr style='background:{$bg};'>
                    <td style='padding:6px 10px;border:1px solid #ddd;font-size:12px;'>{$tarea}</td>
                    <td style='padding:6px 10px;border:1px solid #ddd;font-size:12px;'>{$resp}</td>
                    <td style='padding:6px 10px;border:1px solid #ddd;font-size:12px;text-align:center;'>{$plazo}</td>
                    <td style='padding:6px 10px;border:1px solid #ddd;font-size:12px;text-align:center;color:#dc3545;font-weight:bold;'>{$diasV} d</td>
                </tr>";
        }
        return "
        <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
            <thead>
                <tr style='background:#ffc107;'>
                    <th style='padding:8px 10px;text-align:left;border:1px solid #ddd;font-size:12px;color:#333;'>Tarea / Actividad</th>
                    <th style='padding:8px 10px;text-align:left;border:1px solid #ddd;font-size:12px;color:#333;'>Responsable</th>
                    <th style='padding:8px 10px;text-align:center;border:1px solid #ddd;font-size:12px;color:#333;'>Plazo original</th>
                    <th style='padding:8px 10px;text-align:center;border:1px solid #ddd;font-size:12px;color:#333;'>Dias vencido</th>
                </tr>
            </thead>
            <tbody>{$rows}</tbody>
        </table>";
    }
}
