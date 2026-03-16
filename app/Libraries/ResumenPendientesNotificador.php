<?php

namespace App\Libraries;

use App\Models\ConsultantModel;
use App\Models\ClientModel;
use App\Models\ActaVisitaModel;
use App\Models\InspeccionLocativaModel;
use App\Models\InspeccionSenalizacionModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\InspeccionBotiquinModel;
use App\Models\InspeccionGabineteModel;
use App\Models\InspeccionComunicacionModel;
use App\Models\InspeccionRecursosSeguridadModel;
use App\Models\ProbabilidadPeligrosModel;
use App\Models\MatrizVulnerabilidadModel;
use App\Models\PlanEmergenciaModel;
use App\Models\EvaluacionSimulacroModel;
use App\Models\HvBrigadistaModel;
use App\Models\DotacionVigilanteModel;
use App\Models\DotacionAseadoraModel;
use App\Models\DotacionToderoModel;
use App\Models\AuditoriaZonaResiduosModel;
use App\Models\ReporteCapacitacionModel;
use App\Models\PreparacionSimulacroModel;
use App\Models\AsistenciaInduccionModel;
use App\Models\ProgramaLimpiezaModel;
use App\Models\ProgramaResiduosModel;
use App\Models\ProgramaPlagasModel;
use App\Models\ProgramaAguaPotableModel;
use App\Models\PlanSaneamientoModel;
use App\Models\KpiLimpiezaModel;
use App\Models\KpiResiduosModel;
use App\Models\KpiPlagasModel;
use App\Models\KpiAguaPotableModel;

class ResumenPendientesNotificador
{
    /**
     * Mapa de tipos de documentos: etiqueta => getAllPendientes()
     * Los modelos ya retornan id_consultor, nombre_cliente, updated_at
     */
    private function getTipos(): array
    {
        return [
            'Acta de Visita'           => (new ActaVisitaModel())->getAllPendientes(),
            'Insp. Locativa'           => (new InspeccionLocativaModel())->getAllPendientes(),
            'Señalización'             => (new InspeccionSenalizacionModel())->getAllPendientes(),
            'Extintores'               => (new InspeccionExtintoresModel())->getAllPendientes(),
            'Botiquín'                 => (new InspeccionBotiquinModel())->getAllPendientes(),
            'Gabinetes'                => (new InspeccionGabineteModel())->getAllPendientes(),
            'Comunicaciones'           => (new InspeccionComunicacionModel())->getAllPendientes(),
            'Rec. Seguridad'           => (new InspeccionRecursosSeguridadModel())->getAllPendientes(),
            'Prob. Peligros'           => (new ProbabilidadPeligrosModel())->getAllPendientes(),
            'Matriz Vulnerabilidad'    => (new MatrizVulnerabilidadModel())->getAllPendientes(),
            'Plan Emergencia'          => (new PlanEmergenciaModel())->getAllPendientes(),
            'Ev. Simulacro'            => (new EvaluacionSimulacroModel())->getAllPendientes(),
            'HV Brigadista'            => (new HvBrigadistaModel())->getAllPendientes(),
            'Dot. Vigilante'           => (new DotacionVigilanteModel())->getAllPendientes(),
            'Dot. Aseadora'            => (new DotacionAseadoraModel())->getAllPendientes(),
            'Dot. Todero'              => (new DotacionToderoModel())->getAllPendientes(),
            'Zona Residuos'            => (new AuditoriaZonaResiduosModel())->getAllPendientes(),
            'Capacitación'             => (new ReporteCapacitacionModel())->getAllPendientes(),
            'Prep. Simulacro'          => (new PreparacionSimulacroModel())->getAllPendientes(),
            'Asistencia Inducción'     => (new AsistenciaInduccionModel())->getAllPendientes(),
            'Limpieza/Desinfección'    => (new ProgramaLimpiezaModel())->getAllPendientes(),
            'Residuos Sólidos'         => (new ProgramaResiduosModel())->getAllPendientes(),
            'Control de Plagas'        => (new ProgramaPlagasModel())->getAllPendientes(),
            'Agua Potable'             => (new ProgramaAguaPotableModel())->getAllPendientes(),
            'Plan Saneamiento'         => (new PlanSaneamientoModel())->getAllPendientes(),
            'KPI Limpieza'             => (new KpiLimpiezaModel())->getAllPendientes(),
            'KPI Residuos'             => (new KpiResiduosModel())->getAllPendientes(),
            'KPI Plagas'               => (new KpiPlagasModel())->getAllPendientes(),
            'KPI Agua Potable'         => (new KpiAguaPotableModel())->getAllPendientes(),
        ];
    }

    /**
     * Agrupa todos los pendientes por id_consultor
     * Retorna: [ id_consultor => [ 'tipo' => [...docs], ... ] ]
     */
    private function agruparPorConsultor(): array
    {
        $agrupado = [];
        foreach ($this->getTipos() as $etiqueta => $docs) {
            foreach ($docs as $doc) {
                $idCons = $doc['id_consultor'] ?? null;
                if (!$idCons) continue;
                if (!isset($agrupado[$idCons])) {
                    $agrupado[$idCons] = [];
                }
                if (!isset($agrupado[$idCons][$etiqueta])) {
                    $agrupado[$idCons][$etiqueta] = [];
                }
                $agrupado[$idCons][$etiqueta][] = $doc;
            }
        }
        return $agrupado;
    }

    /**
     * Envía resumen diario a cada consultor con pendientes
     */
    public function enviarResumenDiario(): array
    {
        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) {
            return ['enviados' => 0, 'errores' => 0, 'sin_pendientes' => 0, 'error' => 'SENDGRID_API_KEY no configurada'];
        }

        $consultantModel = new ConsultantModel();
        $clientModel     = new ClientModel();
        $agrupado        = $this->agruparPorConsultor();

        $enviados       = 0;
        $errores        = 0;
        $sinPendientes  = 0;

        $todosConsultores = $consultantModel->findAll();

        foreach ($todosConsultores as $consultor) {
            $idCons = $consultor['id_consultor'];
            $correo = $consultor['correo_consultor'] ?? '';
            $nombre = $consultor['nombre_consultor'] ?? 'Consultor';

            if (empty($correo)) {
                $sinPendientes++;
                continue;
            }

            if (!isset($agrupado[$idCons]) || empty($agrupado[$idCons])) {
                $sinPendientes++;
                continue;
            }

            $tiposConDocs = $agrupado[$idCons];
            $total = array_sum(array_map('count', $tiposConDocs));

            // Recolectar emails de consultores externos únicos de los clientes involucrados
            $emailsExternos = [];
            $idsClientes = [];
            foreach ($tiposConDocs as $docs) {
                foreach ($docs as $doc) {
                    if (!empty($doc['id_cliente'])) {
                        $idsClientes[] = $doc['id_cliente'];
                    }
                }
            }
            foreach (array_unique($idsClientes) as $idCliente) {
                $cliente = $clientModel->find($idCliente);
                if (!empty($cliente['email_consultor_externo'])) {
                    $emailsExternos[$cliente['email_consultor_externo']] = $cliente['consultor_externo'] ?? 'Consultor Externo';
                }
            }

            $html = $this->buildHtml($nombre, $tiposConDocs, $total);
            $fecha = date('d/m/Y');

            $resultado = $this->enviarEmail(
                $apiKey,
                $correo,
                $nombre,
                "[$fecha] Tienes $total documento(s) pendiente(s) - Inspecciones SST",
                $html,
                $emailsExternos
            );

            if ($resultado) {
                $enviados++;
            } else {
                $errores++;
            }
        }

        return [
            'enviados'      => $enviados,
            'errores'       => $errores,
            'sin_pendientes' => $sinPendientes,
        ];
    }

    private function buildHtml(string $nombreConsultor, array $tiposConDocs, int $total): string
    {
        $fecha = date('d \d\e F \d\e Y');
        $baseUrl = rtrim(getenv('app.baseURL') ?: 'https://phorizontal.cycloidtalent.com', '/');

        $filas = '';
        foreach ($tiposConDocs as $etiqueta => $docs) {
            $estado = (count($docs) === 1 && isset($docs[0]['estado']))
                ? $docs[0]['estado']
                : '';
            foreach ($docs as $doc) {
                $cliente = htmlspecialchars($doc['nombre_cliente'] ?? 'Sin cliente');
                $estadoDoc = $doc['estado'] ?? 'borrador';
                $fecha_doc = !empty($doc['updated_at'])
                    ? date('d/m/Y', strtotime($doc['updated_at']))
                    : '';
                $badgeColor = ($estadoDoc === 'pendiente_firma') ? '#e67e22' : '#e74c3c';
                $badgeText  = ($estadoDoc === 'pendiente_firma') ? 'Pend. Firma' : 'Borrador';

                $filas .= "
                <tr>
                    <td style='padding:8px 10px; border-bottom:1px solid #f0f0f0; font-size:13px; color:#333;'>
                        <strong style='color:#1c2437;'>{$etiqueta}</strong>
                    </td>
                    <td style='padding:8px 10px; border-bottom:1px solid #f0f0f0; font-size:13px; color:#555;'>{$cliente}</td>
                    <td style='padding:8px 10px; border-bottom:1px solid #f0f0f0; font-size:12px;'>
                        <span style='background:{$badgeColor}; color:#fff; padding:2px 8px; border-radius:10px; font-size:11px;'>{$badgeText}</span>
                    </td>
                    <td style='padding:8px 10px; border-bottom:1px solid #f0f0f0; font-size:12px; color:#999;'>{$fecha_doc}</td>
                </tr>";
            }
        }

        return "<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4; padding:20px 0;'>
<tr><td align='center'>
<table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);'>

  <!-- Header -->
  <tr>
    <td style='background:linear-gradient(135deg,#1c2437 0%,#2d3b55 100%); padding:28px 32px; text-align:center;'>
      <h1 style='color:#bd9751; margin:0; font-size:20px; letter-spacing:1px;'>ENTERPRISES SST</h1>
      <p style='color:#ccc; margin:6px 0 0; font-size:13px;'>Resumen de Pendientes &mdash; {$fecha}</p>
    </td>
  </tr>

  <!-- Saludo -->
  <tr>
    <td style='padding:24px 32px 12px;'>
      <p style='font-size:15px; color:#333; margin:0;'>Hola, <strong>" . htmlspecialchars($nombreConsultor) . "</strong></p>
      <p style='font-size:13px; color:#666; margin:8px 0 0;'>
        Tienes <strong style='color:#e74c3c;'>{$total} documento(s)</strong> en borrador o pendiente de firma.
        Por favor complétalos a la brevedad.
      </p>
    </td>
  </tr>

  <!-- Tabla de pendientes -->
  <tr>
    <td style='padding:8px 32px 24px;'>
      <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e8e8e8; border-radius:6px; overflow:hidden;'>
        <thead>
          <tr style='background:#f8f9fa;'>
            <th style='padding:10px; text-align:left; font-size:12px; color:#666; font-weight:600; border-bottom:2px solid #e8e8e8;'>TIPO</th>
            <th style='padding:10px; text-align:left; font-size:12px; color:#666; font-weight:600; border-bottom:2px solid #e8e8e8;'>CLIENTE</th>
            <th style='padding:10px; text-align:left; font-size:12px; color:#666; font-weight:600; border-bottom:2px solid #e8e8e8;'>ESTADO</th>
            <th style='padding:10px; text-align:left; font-size:12px; color:#666; font-weight:600; border-bottom:2px solid #e8e8e8;'>ACTUALIZADO</th>
          </tr>
        </thead>
        <tbody>{$filas}</tbody>
      </table>
    </td>
  </tr>

  <!-- Botón -->
  <tr>
    <td style='padding:0 32px 32px; text-align:center;'>
      <a href='{$baseUrl}/inspecciones'
         style='display:inline-block; background:#bd9751; color:#fff; padding:12px 32px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:bold; letter-spacing:0.5px;'>
        Ir a Inspecciones SST
      </a>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style='background:#f8f9fa; padding:16px 32px; text-align:center; border-top:1px solid #e8e8e8;'>
      <p style='font-size:11px; color:#aaa; margin:0;'>Este es un correo automático generado por el sistema de Inspecciones SST.<br>Por favor no responda este mensaje.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>";
    }

    private function enviarEmail(
        string $apiKey,
        string $toEmail,
        string $toName,
        string $subject,
        string $html,
        array $emailsExternos = []
    ): bool {
        $personalizations = [
            [
                'to' => [['email' => $toEmail, 'name' => $toName]],
            ]
        ];

        // CC a consultores externos
        if (!empty($emailsExternos)) {
            $cc = [];
            foreach ($emailsExternos as $email => $nombre) {
                $cc[] = ['email' => $email, 'name' => $nombre];
            }
            $personalizations[0]['cc'] = $cc;
        }

        $payload = [
            'personalizations' => $personalizations,
            'from'             => ['email' => 'noreply@cycloidtalent.com', 'name' => 'Enterprises SST'],
            'subject'          => $subject,
            'content'          => [['type' => 'text/html', 'value' => $html]],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}
