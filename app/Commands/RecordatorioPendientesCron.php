<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use SendGrid\Mail\Mail;

class RecordatorioPendientesCron extends BaseCommand
{
    protected $group       = 'Pendientes';
    protected $name        = 'pendientes:recordatorio';
    protected $description = 'Envía recordatorio quincenal (día 1 y 16) de pendientes ABIERTOS a consultores';
    protected $usage       = 'pendientes:recordatorio';

    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function run(array $params)
    {
        $dia = (int) date('j');

        // Solo ejecutar el día 1 y el día 16
        if ($dia !== 1 && $dia !== 16) {
            CLI::write("Día {$dia}: no es día 1 ni 16, no se envía recordatorio.", 'yellow');
            return;
        }

        CLI::write('Iniciando recordatorio de pendientes abiertos...', 'yellow');

        // Consultar todos los pendientes ABIERTOS con info de cliente y consultor
        $db = \Config\Database::connect();
        $pendientes = $db->table('tbl_pendientes p')
            ->select('p.*, c.nombre_cliente, c.correo_cliente, c.id_consultor, c.consultor_externo, c.email_consultor_externo, con.nombre_consultor, con.correo_consultor')
            ->join('tbl_clientes c', 'c.id_cliente = p.id_cliente')
            ->join('tbl_consultor con', 'con.id_consultor = c.id_consultor', 'left')
            ->where('p.estado', 'ABIERTA')
            ->orderBy('p.fecha_asignacion', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($pendientes)) {
            CLI::write('No hay pendientes abiertos. Nada que enviar.', 'green');
            return;
        }

        CLI::write("Pendientes abiertos encontrados: " . count($pendientes), 'white');

        $mesNombre  = self::MESES[(int) date('n')];
        $anio       = (int) date('Y');

        // Agrupar por consultor interno
        $porConsultor = [];
        foreach ($pendientes as $p) {
            $idCons = (int) ($p['id_consultor'] ?? 0);
            if ($idCons === 0) continue;
            $porConsultor[$idCons]['nombre'] = $p['nombre_consultor'] ?? 'Sin consultor';
            $porConsultor[$idCons]['correo'] = $p['correo_consultor'] ?? '';
            $porConsultor[$idCons]['pendientes'][] = $p;
        }

        // Agrupar por consultor externo
        $porExterno = [];
        foreach ($pendientes as $p) {
            $emailExt = trim($p['email_consultor_externo'] ?? '');
            if ($emailExt === '') continue;
            $porExterno[$emailExt]['nombre'] = $p['consultor_externo'] ?? 'Consultor Externo';
            $porExterno[$emailExt]['pendientes'][] = $p;
        }

        $enviados = 0;
        $errores  = 0;

        $ccFijos = [
            'edison.cuervo@cycloidtalent.com' => 'Edison Cuervo',
            'diana.cuestas@cycloidtalent.com'  => 'Diana Cuestas',
        ];

        // Enviar a cada consultor interno
        foreach ($porConsultor as $idCons => $info) {
            $destinatarios = [];
            $emailsUsados  = [];

            if ($info['correo']) {
                $destinatarios[] = ['email' => $info['correo'], 'name' => $info['nombre']];
                $emailsUsados[strtolower($info['correo'])] = true;
            }

            foreach ($ccFijos as $ccEmail => $ccName) {
                if (!isset($emailsUsados[strtolower($ccEmail)])) {
                    $destinatarios[] = ['email' => $ccEmail, 'name' => $ccName];
                    $emailsUsados[strtolower($ccEmail)] = true;
                }
            }

            if (empty($destinatarios)) continue;

            $ok = $this->enviarRecordatorio($destinatarios, $info['pendientes'], $info['nombre'], $mesNombre, $anio);

            if ($ok) {
                $enviados++;
                CLI::write("  Enviado a consultor: {$info['nombre']} ({$info['correo']}) — " . count($info['pendientes']) . " pendientes", 'green');
            } else {
                $errores++;
                CLI::write("  ERROR enviando a: {$info['nombre']}", 'red');
            }
        }

        // Enviar a cada consultor externo
        foreach ($porExterno as $emailExt => $info) {
            $destinatarios = [];
            $emailsUsados  = [];

            $destinatarios[] = ['email' => $emailExt, 'name' => $info['nombre']];
            $emailsUsados[strtolower($emailExt)] = true;

            foreach ($ccFijos as $ccEmail => $ccName) {
                if (!isset($emailsUsados[strtolower($ccEmail)])) {
                    $destinatarios[] = ['email' => $ccEmail, 'name' => $ccName];
                    $emailsUsados[strtolower($ccEmail)] = true;
                }
            }

            $ok = $this->enviarRecordatorio($destinatarios, $info['pendientes'], $info['nombre'] . ' (Externo)', $mesNombre, $anio);

            if ($ok) {
                $enviados++;
                CLI::write("  Enviado a externo: {$info['nombre']} ({$emailExt}) — " . count($info['pendientes']) . " pendientes", 'green');
            } else {
                $errores++;
                CLI::write("  ERROR enviando a externo: {$info['nombre']}", 'red');
            }
        }

        CLI::write('');
        CLI::write('=== RESULTADOS ===', 'green');
        CLI::write("Emails enviados: {$enviados}", 'green');
        CLI::write("Errores: {$errores}", $errores > 0 ? 'red' : 'white');
        CLI::write("Total pendientes abiertos: " . count($pendientes), 'white');
        CLI::write('Proceso completado.', 'green');
    }

    private function enviarRecordatorio(array $destinatarios, array $pendientes, string $nombreConsultor, string $mes, int $anio): bool
    {
        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            CLI::write("  ERROR: SENDGRID_API_KEY no configurada", 'red');
            return false;
        }

        $totalPendientes = count($pendientes);
        $hoy = new \DateTime();

        // Clasificar pendientes
        $vencidos  = [];
        $proximos  = [];
        $sinFecha  = [];

        foreach ($pendientes as $p) {
            if (empty($p['fecha_cierre'])) {
                $sinFecha[] = $p;
            } else {
                $fechaCierre = new \DateTime($p['fecha_cierre']);
                $diff = $hoy->diff($fechaCierre);
                $diasRestantes = $fechaCierre < $hoy ? -$diff->days : $diff->days;
                $p['dias_restantes'] = $diasRestantes;
                if ($diasRestantes < 0) {
                    $vencidos[] = $p;
                } else {
                    $proximos[] = $p;
                }
            }
        }

        // Ordenar vencidos por más antiguo primero
        usort($vencidos, function ($a, $b) { return $a['dias_restantes'] <=> $b['dias_restantes']; });
        // Ordenar próximos por más cercano primero
        usort($proximos, function ($a, $b) { return $a['dias_restantes'] <=> $b['dias_restantes']; });

        $countVencidos = count($vencidos);
        $countProximos = count($proximos);
        $countSinFecha = count($sinFecha);

        // Cards de resumen
        $cardsHtml = "
        <table style='width:100%; border-collapse:collapse; margin:15px 0;'>
            <tr>
                <td style='padding:8px; text-align:center; width:33%;'>
                    <div style='background:#dc3545; color:#fff; border-radius:8px; padding:15px;'>
                        <div style='font-size:28px; font-weight:bold;'>{$countVencidos}</div>
                        <div style='font-size:12px;'>VENCIDOS</div>
                    </div>
                </td>
                <td style='padding:8px; text-align:center; width:33%;'>
                    <div style='background:#ffc107; color:#333; border-radius:8px; padding:15px;'>
                        <div style='font-size:28px; font-weight:bold;'>{$countProximos}</div>
                        <div style='font-size:12px;'>CON FECHA</div>
                    </div>
                </td>
                <td style='padding:8px; text-align:center; width:33%;'>
                    <div style='background:#6c757d; color:#fff; border-radius:8px; padding:15px;'>
                        <div style='font-size:28px; font-weight:bold;'>{$countSinFecha}</div>
                        <div style='font-size:12px;'>SIN FECHA</div>
                    </div>
                </td>
            </tr>
        </table>";

        // Tabla de pendientes vencidos
        $tablaVencidos = '';
        if (!empty($vencidos)) {
            $tablaVencidos = "
            <h3 style='color:#dc3545; margin:20px 0 10px; font-size:16px;'>PENDIENTES VENCIDOS</h3>
            <table style='width:100%; border-collapse:collapse; margin-bottom:15px;'>
                <thead>
                    <tr style='background:#dc3545;'>
                        <th style='padding:8px 10px; color:#fff; text-align:left; border:1px solid #ddd; font-size:12px;'>Cliente</th>
                        <th style='padding:8px 10px; color:#fff; text-align:left; border:1px solid #ddd; font-size:12px;'>Responsable</th>
                        <th style='padding:8px 10px; color:#fff; text-align:left; border:1px solid #ddd; font-size:12px;'>Tarea / Actividad</th>
                        <th style='padding:8px 10px; color:#fff; text-align:center; border:1px solid #ddd; font-size:12px;'>Fecha Cierre</th>
                        <th style='padding:8px 10px; color:#fff; text-align:center; border:1px solid #ddd; font-size:12px;'>Días Vencido</th>
                    </tr>
                </thead>
                <tbody>";

            foreach ($vencidos as $i => $p) {
                $bg = ($i % 2 === 0) ? '#fff5f5' : '#ffe0e0';
                $diasV = abs($p['dias_restantes']);
                $tarea = htmlspecialchars(mb_substr($p['tarea_actividad'] ?? '', 0, 80));
                if (mb_strlen($p['tarea_actividad'] ?? '') > 80) $tarea .= '...';
                $tablaVencidos .= "
                    <tr style='background:{$bg};'>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>" . htmlspecialchars($p['nombre_cliente'] ?? '') . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>" . htmlspecialchars($p['responsable'] ?? '') . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>{$tarea}</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px; text-align:center;'>" . date('d/m/Y', strtotime($p['fecha_cierre'])) . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px; text-align:center; color:#dc3545; font-weight:bold;'>{$diasV} días</td>
                    </tr>";
            }
            $tablaVencidos .= "</tbody></table>";
        }

        // Tabla de pendientes con fecha
        $tablaProximos = '';
        if (!empty($proximos)) {
            $tablaProximos = "
            <h3 style='color:#ffc107; margin:20px 0 10px; font-size:16px;'>PENDIENTES CON FECHA DE CIERRE</h3>
            <table style='width:100%; border-collapse:collapse; margin-bottom:15px;'>
                <thead>
                    <tr style='background:#1c2437;'>
                        <th style='padding:8px 10px; color:#bd9751; text-align:left; border:1px solid #ddd; font-size:12px;'>Cliente</th>
                        <th style='padding:8px 10px; color:#bd9751; text-align:left; border:1px solid #ddd; font-size:12px;'>Responsable</th>
                        <th style='padding:8px 10px; color:#bd9751; text-align:left; border:1px solid #ddd; font-size:12px;'>Tarea / Actividad</th>
                        <th style='padding:8px 10px; color:#bd9751; text-align:center; border:1px solid #ddd; font-size:12px;'>Fecha Cierre</th>
                        <th style='padding:8px 10px; color:#bd9751; text-align:center; border:1px solid #ddd; font-size:12px;'>Días Restantes</th>
                    </tr>
                </thead>
                <tbody>";

            foreach ($proximos as $i => $p) {
                $bg = ($i % 2 === 0) ? '#fff' : '#f8f9fa';
                $dias = $p['dias_restantes'];
                $colorDias = $dias <= 7 ? '#e67e22' : '#28a745';
                $tarea = htmlspecialchars(mb_substr($p['tarea_actividad'] ?? '', 0, 80));
                if (mb_strlen($p['tarea_actividad'] ?? '') > 80) $tarea .= '...';
                $tablaProximos .= "
                    <tr style='background:{$bg};'>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>" . htmlspecialchars($p['nombre_cliente'] ?? '') . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>" . htmlspecialchars($p['responsable'] ?? '') . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>{$tarea}</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px; text-align:center;'>" . date('d/m/Y', strtotime($p['fecha_cierre'])) . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px; text-align:center; color:{$colorDias}; font-weight:bold;'>{$dias} días</td>
                    </tr>";
            }
            $tablaProximos .= "</tbody></table>";
        }

        // Tabla de pendientes sin fecha
        $tablaSinFecha = '';
        if (!empty($sinFecha)) {
            $tablaSinFecha = "
            <h3 style='color:#6c757d; margin:20px 0 10px; font-size:16px;'>PENDIENTES SIN FECHA DE CIERRE</h3>
            <table style='width:100%; border-collapse:collapse; margin-bottom:15px;'>
                <thead>
                    <tr style='background:#6c757d;'>
                        <th style='padding:8px 10px; color:#fff; text-align:left; border:1px solid #ddd; font-size:12px;'>Cliente</th>
                        <th style='padding:8px 10px; color:#fff; text-align:left; border:1px solid #ddd; font-size:12px;'>Responsable</th>
                        <th style='padding:8px 10px; color:#fff; text-align:left; border:1px solid #ddd; font-size:12px;'>Tarea / Actividad</th>
                        <th style='padding:8px 10px; color:#fff; text-align:center; border:1px solid #ddd; font-size:12px;'>Asignado</th>
                        <th style='padding:8px 10px; color:#fff; text-align:center; border:1px solid #ddd; font-size:12px;'>Días Abierto</th>
                    </tr>
                </thead>
                <tbody>";

            foreach ($sinFecha as $i => $p) {
                $bg = ($i % 2 === 0) ? '#fff' : '#f8f9fa';
                $diasAbierto = 0;
                if (!empty($p['fecha_asignacion'])) {
                    $diasAbierto = $hoy->diff(new \DateTime($p['fecha_asignacion']))->days;
                }
                $tarea = htmlspecialchars(mb_substr($p['tarea_actividad'] ?? '', 0, 80));
                if (mb_strlen($p['tarea_actividad'] ?? '') > 80) $tarea .= '...';
                $tablaSinFecha .= "
                    <tr style='background:{$bg};'>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>" . htmlspecialchars($p['nombre_cliente'] ?? '') . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>" . htmlspecialchars($p['responsable'] ?? '') . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px;'>{$tarea}</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px; text-align:center;'>" . (!empty($p['fecha_asignacion']) ? date('d/m/Y', strtotime($p['fecha_asignacion'])) : '-') . "</td>
                        <td style='padding:6px 10px; border:1px solid #ddd; font-size:12px; text-align:center;'>{$diasAbierto} días</td>
                    </tr>";
            }
            $tablaSinFecha .= "</tbody></table>";
        }

        $fechaHoy = date('d/m/Y');
        $urlPendientes = 'https://phorizontal.cycloidtalent.com/listPendientes';

        $html = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 700px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1c2437, #2c3e50); padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>RECORDATORIO DE PENDIENTES ABIERTOS</h1>
                <p style='color: #ccc; margin: 5px 0 0; font-size: 14px;'>{$mes} {$anio} — {$fechaHoy}</p>
            </div>
            <div style='padding: 25px; background: #f8f9fa; border-radius: 0 0 10px 10px;'>
                <p>Estimado(a) <strong>{$nombreConsultor}</strong>,</p>
                <p>Este es el recordatorio quincenal de <strong>{$totalPendientes} pendiente(s) abierto(s)</strong> asignados a sus clientes:</p>

                {$cardsHtml}
                {$tablaVencidos}
                {$tablaProximos}
                {$tablaSinFecha}

                <div style='text-align:center; margin:25px 0 15px;'>
                    <a href='{$urlPendientes}' style='display:inline-block; background:linear-gradient(135deg, #764ba2, #667eea); color:#fff; padding:12px 30px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:14px;'>Ver Pendientes en Plataforma</a>
                </div>

                <p style='background:#fff3cd; padding:12px; border-radius:6px; border-left:4px solid #f39c12; font-size:13px;'>
                    Este recordatorio se envía los días <strong>1</strong> y <strong>16</strong> de cada mes. Por favor gestione los pendientes vencidos con prioridad.
                </p>

                <p style='color: #999; font-size: 11px; margin-top:20px;'>Generado automáticamente por SG-SST Cycloid Talent — {$fechaHoy} " . date('H:i') . "</p>
            </div>
        </div>";

        $subject = "Recordatorio: {$totalPendientes} pendiente(s) abierto(s)";
        if ($countVencidos > 0) {
            $subject .= " ({$countVencidos} vencido(s))";
        }
        $subject .= " — {$mes} {$anio}";

        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
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
