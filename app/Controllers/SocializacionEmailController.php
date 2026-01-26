<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;
use App\Models\PtaClienteNuevaModel;
use App\Models\CronogcapacitacionModel;
use App\Models\EvaluationModel;
use SendGrid\Mail\Mail;

/**
 * Controlador para el envío de emails de socialización del Decreto 1072
 * Maneja el envío de: Plan de Trabajo, Cronograma de Capacitaciones y Evaluación de Estándares Mínimos
 */
class SocializacionEmailController extends BaseController
{
    // Emails que siempre van en copia (vacío - solo se envía a cliente y consultor)
    private $ccEmails = [];

    /**
     * Enviar email con el Plan de Trabajo Anual (PTA)
     */
    public function sendPlanTrabajo()
    {
        $idCliente = $this->request->getPost('id_cliente');

        if (!$idCliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de cliente no proporcionado'
            ]);
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $contractModel = new ContractModel();
        $ptaModel = new PtaClienteNuevaModel();

        // Obtener datos del cliente
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }

        // Obtener consultor asignado al cliente
        $consultor = null;
        if (!empty($cliente['id_consultor'])) {
            $consultor = $consultantModel->find($cliente['id_consultor']);
        }

        // Obtener contrato activo
        $contrato = $contractModel->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$contrato) {
            $contrato = $contractModel->where('id_cliente', $idCliente)
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        // Obtener actividades del plan de trabajo SOLO del año en curso
        $anioActual = date('Y');
        $actividades = $ptaModel->where('id_cliente', $idCliente)
            ->where("YEAR(fecha_propuesta)", $anioActual)
            ->orderBy('fecha_propuesta', 'ASC')
            ->findAll();

        if (empty($actividades)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No hay actividades registradas en el Plan de Trabajo para el año {$anioActual}"
            ]);
        }

        // Validar email del cliente
        if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El cliente no tiene un correo electrónico válido registrado'
            ]);
        }

        // Construir contenido del email
        $htmlContent = $this->buildPlanTrabajoEmailContent($cliente, $consultor, $contrato, $actividades, $anioActual);

        // Preparar destinatarios
        $destinatarios = [$cliente['correo_cliente']];
        if ($consultor && !empty($consultor['correo_consultor']) && filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
            $destinatarios[] = $consultor['correo_consultor'];
        }

        // Enviar email
        $result = $this->sendSocializacionEmail(
            $destinatarios,
            "Socialización Plan de Trabajo Anual SG-SST {$anioActual} - {$cliente['nombre_cliente']}",
            $htmlContent
        );

        return $this->response->setJSON($result);
    }

    /**
     * Enviar email con el Cronograma de Capacitaciones
     */
    public function sendCronogramaCapacitaciones()
    {
        $idCliente = $this->request->getPost('id_cliente');

        if (!$idCliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de cliente no proporcionado'
            ]);
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $contractModel = new ContractModel();
        $cronogModel = new CronogcapacitacionModel();

        // Obtener datos del cliente
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }

        // Obtener consultor asignado al cliente
        $consultor = null;
        if (!empty($cliente['id_consultor'])) {
            $consultor = $consultantModel->find($cliente['id_consultor']);
        }

        // Obtener contrato activo
        $contrato = $contractModel->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$contrato) {
            $contrato = $contractModel->where('id_cliente', $idCliente)
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        // Obtener cronograma de capacitaciones SOLO del año en curso
        $anioActual = date('Y');
        $capacitaciones = $cronogModel->where('id_cliente', $idCliente)
            ->where("YEAR(fecha_programada)", $anioActual)
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        if (empty($capacitaciones)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No hay capacitaciones registradas en el Cronograma para el año {$anioActual}"
            ]);
        }

        // Validar email del cliente
        if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El cliente no tiene un correo electrónico válido registrado'
            ]);
        }

        // Construir contenido del email
        $htmlContent = $this->buildCronogramaCapacitacionesEmailContent($cliente, $consultor, $contrato, $capacitaciones, $anioActual);

        // Preparar destinatarios
        $destinatarios = [$cliente['correo_cliente']];
        if ($consultor && !empty($consultor['correo_consultor']) && filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
            $destinatarios[] = $consultor['correo_consultor'];
        }

        // Enviar email
        $result = $this->sendSocializacionEmail(
            $destinatarios,
            "Socialización Cronograma de Capacitaciones SG-SST {$anioActual} - {$cliente['nombre_cliente']}",
            $htmlContent
        );

        return $this->response->setJSON($result);
    }

    /**
     * Enviar email con la Evaluación de Estándares Mínimos
     */
    public function sendEvaluacionEstandares()
    {
        $idCliente = $this->request->getPost('id_cliente');

        if (!$idCliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de cliente no proporcionado'
            ]);
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $contractModel = new ContractModel();
        $evaluationModel = new EvaluationModel();

        // Obtener datos del cliente
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }

        // Obtener consultor asignado al cliente
        $consultor = null;
        if (!empty($cliente['id_consultor'])) {
            $consultor = $consultantModel->find($cliente['id_consultor']);
        }

        // Obtener contrato activo
        $contrato = $contractModel->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$contrato) {
            $contrato = $contractModel->where('id_cliente', $idCliente)
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        // Obtener todas las evaluaciones del cliente (no se filtra por fecha)
        $anioActual = date('Y');
        $evaluaciones = $evaluationModel->where('id_cliente', $idCliente)
            ->orderBy('ciclo', 'ASC')
            ->orderBy('estandar', 'ASC')
            ->findAll();

        if (empty($evaluaciones)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No hay evaluaciones registradas para este cliente"
            ]);
        }

        // Calcular indicadores
        $indicadores = $this->calcularIndicadoresEvaluacion($evaluaciones);

        // Validar email del cliente
        if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El cliente no tiene un correo electrónico válido registrado'
            ]);
        }

        // Construir contenido del email
        $htmlContent = $this->buildEvaluacionEmailContent($cliente, $consultor, $contrato, $evaluaciones, $indicadores, $anioActual);

        // Preparar destinatarios
        $destinatarios = [$cliente['correo_cliente']];
        if ($consultor && !empty($consultor['correo_consultor']) && filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
            $destinatarios[] = $consultor['correo_consultor'];
        }

        // Enviar email
        $result = $this->sendSocializacionEmail(
            $destinatarios,
            "Socialización Evaluación de Estándares Mínimos SG-SST {$anioActual} - {$cliente['nombre_cliente']}",
            $htmlContent
        );

        return $this->response->setJSON($result);
    }

    /**
     * Construir contenido HTML del email de Plan de Trabajo
     */
    private function buildPlanTrabajoEmailContent($cliente, $consultor, $contrato, $actividades, $anio)
    {
        $nombreConsultor = $consultor ? $consultor['nombre_consultor'] : 'Consultor SST';
        $frecuenciaVisitas = $contrato ? ($contrato['frecuencia_visitas'] ?? 'No definida') : 'No definida';
        $fechaActual = date('d/m/Y');

        // Contar actividades por estado
        $conteoEstados = [
            'ABIERTA' => 0,
            'CERRADA' => 0,
            'GESTIONANDO' => 0,
            'CERRADA SIN EJECUCIÓN' => 0
        ];
        foreach ($actividades as $act) {
            $estado = $act['estado_actividad'] ?? 'ABIERTA';
            if (isset($conteoEstados[$estado])) {
                $conteoEstados[$estado]++;
            }
        }
        $totalActividades = count($actividades);

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background-color: #f8f9fc; border-left: 4px solid #4e73df; padding: 15px; margin: 15px 0; }
                .stats-container { display: flex; justify-content: space-around; margin: 20px 0; }
                .stat-box { text-align: center; padding: 15px; border-radius: 8px; min-width: 100px; }
                .stat-abierta { background-color: #cce5ff; }
                .stat-cerrada { background-color: #f8d7da; }
                .stat-gestionando { background-color: #fff3cd; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                th { background-color: #4e73df; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #f8f9fc; }
                .footer { background-color: #f8f9fc; padding: 20px; text-align: center; margin-top: 30px; }
                .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
                .badge-abierta { background-color: #007bff; color: white; }
                .badge-cerrada { background-color: #dc3545; color: white; }
                .badge-gestionando { background-color: #ffc107; color: black; }
                .badge-sin-ejecucion { background-color: #343a40; color: white; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Plan de Trabajo Anual SG-SST - {$anio}</h1>
                <p>Socialización según Decreto 1072 de 2015</p>
            </div>

            <div class='content'>
                <div class='info-box'>
                    <p><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p><strong>NIT:</strong> {$cliente['nit_cliente']}</p>
                    <p><strong>Consultor Asignado:</strong> {$nombreConsultor}</p>
                    <p><strong>Frecuencia de Visitas:</strong> {$frecuenciaVisitas}</p>
                    <p><strong>Año del Plan:</strong> {$anio}</p>
                    <p><strong>Fecha de Socialización:</strong> {$fechaActual}</p>
                </div>

                <h2>Resumen de Actividades - Año {$anio}</h2>
                <table>
                    <tr>
                        <td style='text-align: center; background-color: #cce5ff;'><strong>Abiertas</strong><br><span style='font-size: 24px;'>{$conteoEstados['ABIERTA']}</span></td>
                        <td style='text-align: center; background-color: #d4edda;'><strong>Cerradas</strong><br><span style='font-size: 24px;'>{$conteoEstados['CERRADA']}</span></td>
                        <td style='text-align: center; background-color: #fff3cd;'><strong>Gestionando</strong><br><span style='font-size: 24px;'>{$conteoEstados['GESTIONANDO']}</span></td>
                        <td style='text-align: center; background-color: #e2e3e5;'><strong>Total</strong><br><span style='font-size: 24px;'>{$totalActividades}</span></td>
                    </tr>
                </table>

                <h2>Detalle del Plan de Trabajo</h2>
                <table>
                    <thead>
                        <tr>
                            <th>PHVA</th>
                            <th>Numeral</th>
                            <th>Actividad</th>
                            <th>Responsable</th>
                            <th>Fecha Propuesta</th>
                            <th>Estado</th>
                            <th>Avance</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($actividades as $act) {
            $estado = $act['estado_actividad'] ?? 'ABIERTA';
            $badgeClass = 'badge-abierta';
            if ($estado === 'CERRADA') $badgeClass = 'badge-cerrada';
            elseif ($estado === 'GESTIONANDO') $badgeClass = 'badge-gestionando';
            elseif ($estado === 'CERRADA SIN EJECUCIÓN') $badgeClass = 'badge-sin-ejecucion';

            $fechaPropuesta = !empty($act['fecha_propuesta']) ? date('d/m/Y', strtotime($act['fecha_propuesta'])) : 'N/A';
            $porcentaje = $act['porcentaje_avance'] ?? '0';

            $html .= "
                        <tr>
                            <td>{$act['phva_plandetrabajo']}</td>
                            <td>{$act['numeral_plandetrabajo']}</td>
                            <td>{$act['actividad_plandetrabajo']}</td>
                            <td>{$act['responsable_sugerido_plandetrabajo']}</td>
                            <td>{$fechaPropuesta}</td>
                            <td><span class='badge {$badgeClass}'>{$estado}</span></td>
                            <td>{$porcentaje}%</td>
                        </tr>";
        }

        $html .= "
                    </tbody>
                </table>

                <div class='footer'>
                    <p><strong>Cycloid Talent SAS</strong></p>
                    <p>NIT: 901.653.912</p>
                    <p>Este documento hace parte de la socialización del Plan de Trabajo Anual del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) conforme al Decreto 1072 de 2015.</p>
                    <p><a href='https://phorizontal.cycloidtalent.com'>Acceder a la Plataforma</a></p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Construir contenido HTML del email de Cronograma de Capacitaciones
     */
    private function buildCronogramaCapacitacionesEmailContent($cliente, $consultor, $contrato, $capacitaciones, $anio)
    {
        $nombreConsultor = $consultor ? $consultor['nombre_consultor'] : 'Consultor SST';
        $frecuenciaVisitas = $contrato ? ($contrato['frecuencia_visitas'] ?? 'No definida') : 'No definida';
        $fechaActual = date('d/m/Y');

        // Contar capacitaciones por estado
        $conteoEstados = [
            'PROGRAMADA' => 0,
            'EJECUTADA' => 0,
            'CANCELADA POR EL CLIENTE' => 0,
            'REPROGRAMADA' => 0
        ];
        foreach ($capacitaciones as $cap) {
            $estado = $cap['estado'] ?? 'PROGRAMADA';
            if (isset($conteoEstados[$estado])) {
                $conteoEstados[$estado]++;
            }
        }
        $totalCapacitaciones = count($capacitaciones);

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background-color: #f8f9fc; border-left: 4px solid #1cc88a; padding: 15px; margin: 15px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                th { background-color: #1cc88a; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #f8f9fc; }
                .footer { background-color: #f8f9fc; padding: 20px; text-align: center; margin-top: 30px; }
                .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
                .badge-programada { background-color: #007bff; color: white; }
                .badge-ejecutada { background-color: #28a745; color: white; }
                .badge-cancelada { background-color: #dc3545; color: white; }
                .badge-reprogramada { background-color: #ffc107; color: black; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Cronograma de Capacitaciones SG-SST - {$anio}</h1>
                <p>Socialización según Decreto 1072 de 2015</p>
            </div>

            <div class='content'>
                <div class='info-box'>
                    <p><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p><strong>NIT:</strong> {$cliente['nit_cliente']}</p>
                    <p><strong>Consultor Asignado:</strong> {$nombreConsultor}</p>
                    <p><strong>Frecuencia de Visitas:</strong> {$frecuenciaVisitas}</p>
                    <p><strong>Año del Cronograma:</strong> {$anio}</p>
                    <p><strong>Fecha de Socialización:</strong> {$fechaActual}</p>
                </div>

                <h2>Resumen de Capacitaciones - Año {$anio}</h2>
                <table>
                    <tr>
                        <td style='text-align: center; background-color: #cce5ff;'><strong>Programadas</strong><br><span style='font-size: 24px;'>{$conteoEstados['PROGRAMADA']}</span></td>
                        <td style='text-align: center; background-color: #d4edda;'><strong>Ejecutadas</strong><br><span style='font-size: 24px;'>{$conteoEstados['EJECUTADA']}</span></td>
                        <td style='text-align: center; background-color: #fff3cd;'><strong>Reprogramadas</strong><br><span style='font-size: 24px;'>{$conteoEstados['REPROGRAMADA']}</span></td>
                        <td style='text-align: center; background-color: #e2e3e5;'><strong>Total</strong><br><span style='font-size: 24px;'>{$totalCapacitaciones}</span></td>
                    </tr>
                </table>

                <h2>Detalle del Cronograma de Capacitaciones</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Capacitación</th>
                            <th>Perfil Asistentes</th>
                            <th>Fecha Programada</th>
                            <th>Fecha Realización</th>
                            <th>Estado</th>
                            <th>Capacitador</th>
                            <th>Horas</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($capacitaciones as $cap) {
            $estado = $cap['estado'] ?? 'PROGRAMADA';
            $badgeClass = 'badge-programada';
            if ($estado === 'EJECUTADA') $badgeClass = 'badge-ejecutada';
            elseif ($estado === 'CANCELADA POR EL CLIENTE') $badgeClass = 'badge-cancelada';
            elseif ($estado === 'REPROGRAMADA') $badgeClass = 'badge-reprogramada';

            $fechaProgramada = !empty($cap['fecha_programada']) ? date('d/m/Y', strtotime($cap['fecha_programada'])) : 'N/A';
            $fechaRealizacion = !empty($cap['fecha_de_realizacion']) ? date('d/m/Y', strtotime($cap['fecha_de_realizacion'])) : 'Pendiente';

            $html .= "
                        <tr>
                            <td>{$cap['nombre_capacitacion']}</td>
                            <td>{$cap['perfil_de_asistentes']}</td>
                            <td>{$fechaProgramada}</td>
                            <td>{$fechaRealizacion}</td>
                            <td><span class='badge {$badgeClass}'>{$estado}</span></td>
                            <td>{$cap['nombre_del_capacitador']}</td>
                            <td>{$cap['horas_de_duracion_de_la_capacitacion']}</td>
                        </tr>";
        }

        $html .= "
                    </tbody>
                </table>

                <div class='footer'>
                    <p><strong>Cycloid Talent SAS</strong></p>
                    <p>NIT: 901.653.912</p>
                    <p>Este documento hace parte de la socialización del Cronograma de Capacitaciones del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) conforme al Decreto 1072 de 2015.</p>
                    <p><a href='https://phorizontal.cycloidtalent.com'>Acceder a la Plataforma</a></p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Calcular indicadores de evaluación
     */
    private function calcularIndicadoresEvaluacion($evaluaciones)
    {
        $sumPuntaje = 0;
        $sumValor = 0;
        $countCumple = 0;
        $countNoCumple = 0;
        $countNoAplica = 0;

        foreach ($evaluaciones as $ev) {
            $sumPuntaje += floatval($ev['puntaje_cuantitativo'] ?? 0);
            $sumValor += floatval($ev['valor'] ?? 0);

            $evaluacion = $ev['evaluacion_inicial'] ?? '';
            if ($evaluacion === 'CUMPLE TOTALMENTE') {
                $countCumple++;
            } elseif ($evaluacion === 'NO CUMPLE') {
                $countNoCumple++;
            } elseif ($evaluacion === 'NO APLICA') {
                $countNoAplica++;
            }
        }

        $indicadorGeneral = $sumValor > 0 ? round(($sumPuntaje / $sumValor) * 100, 2) : 0;

        return [
            'sum_puntaje' => $sumPuntaje,
            'sum_valor' => $sumValor,
            'indicador_general' => $indicadorGeneral,
            'count_cumple' => $countCumple,
            'count_no_cumple' => $countNoCumple,
            'count_no_aplica' => $countNoAplica,
            'total_items' => count($evaluaciones)
        ];
    }

    /**
     * Construir contenido HTML del email de Evaluación de Estándares
     */
    private function buildEvaluacionEmailContent($cliente, $consultor, $contrato, $evaluaciones, $indicadores, $anio)
    {
        $nombreConsultor = $consultor ? $consultor['nombre_consultor'] : 'Consultor SST';
        $frecuenciaVisitas = $contrato ? ($contrato['frecuencia_visitas'] ?? 'No definida') : 'No definida';
        $fechaActual = date('d/m/Y');

        // Agrupar por estándar para el resumen
        $porEstandar = [];
        foreach ($evaluaciones as $ev) {
            $estandar = $ev['estandar'] ?? 'Sin categoría';
            if (!isset($porEstandar[$estandar])) {
                $porEstandar[$estandar] = [
                    'total' => 0,
                    'cumple' => 0,
                    'no_cumple' => 0,
                    'no_aplica' => 0
                ];
            }
            $porEstandar[$estandar]['total']++;
            $evaluacion = $ev['evaluacion_inicial'] ?? '';
            if ($evaluacion === 'CUMPLE TOTALMENTE') {
                $porEstandar[$estandar]['cumple']++;
            } elseif ($evaluacion === 'NO CUMPLE') {
                $porEstandar[$estandar]['no_cumple']++;
            } elseif ($evaluacion === 'NO APLICA') {
                $porEstandar[$estandar]['no_aplica']++;
            }
        }

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); color: #333; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background-color: #f8f9fc; border-left: 4px solid #f6c23e; padding: 15px; margin: 15px 0; }
                .indicator-box { background-color: #e8f4f8; border: 2px solid #4e73df; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center; }
                .indicator-value { font-size: 48px; font-weight: bold; color: #4e73df; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                th { background-color: #f6c23e; color: #333; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #f8f9fc; }
                .footer { background-color: #f8f9fc; padding: 20px; text-align: center; margin-top: 30px; }
                .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
                .badge-cumple { background-color: #28a745; color: white; }
                .badge-no-cumple { background-color: #dc3545; color: white; }
                .badge-no-aplica { background-color: #6c757d; color: white; }
                .summary-table td { text-align: center; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Evaluación de Estándares Mínimos SG-SST - {$anio}</h1>
                <p>Socialización según Decreto 1072 de 2015 y Resolución 0312 de 2019</p>
            </div>

            <div class='content'>
                <div class='info-box'>
                    <p><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p><strong>NIT:</strong> {$cliente['nit_cliente']}</p>
                    <p><strong>Consultor Asignado:</strong> {$nombreConsultor}</p>
                    <p><strong>Frecuencia de Visitas:</strong> {$frecuenciaVisitas}</p>
                    <p><strong>Año de la Evaluación:</strong> {$anio}</p>
                    <p><strong>Fecha de Socialización:</strong> {$fechaActual}</p>
                </div>

                <div class='indicator-box'>
                    <h2>Indicador de Cumplimiento General - Año {$anio}</h2>
                    <div class='indicator-value'>{$indicadores['indicador_general']}%</div>
                    <p>Puntaje Obtenido: {$indicadores['sum_puntaje']} / {$indicadores['sum_valor']}</p>
                </div>

                <h2>Resumen de Evaluación</h2>
                <table class='summary-table'>
                    <tr>
                        <td style='background-color: #d4edda;'><strong>Cumple Totalmente</strong><br><span style='font-size: 24px;'>{$indicadores['count_cumple']}</span></td>
                        <td style='background-color: #f8d7da;'><strong>No Cumple</strong><br><span style='font-size: 24px;'>{$indicadores['count_no_cumple']}</span></td>
                        <td style='background-color: #e2e3e5;'><strong>No Aplica</strong><br><span style='font-size: 24px;'>{$indicadores['count_no_aplica']}</span></td>
                        <td style='background-color: #cce5ff;'><strong>Total Items</strong><br><span style='font-size: 24px;'>{$indicadores['total_items']}</span></td>
                    </tr>
                </table>

                <h2>Resumen por Estándar</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Estándar</th>
                            <th>Cumple</th>
                            <th>No Cumple</th>
                            <th>No Aplica</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($porEstandar as $estandar => $datos) {
            $html .= "
                        <tr>
                            <td>{$estandar}</td>
                            <td style='text-align: center; color: #28a745;'><strong>{$datos['cumple']}</strong></td>
                            <td style='text-align: center; color: #dc3545;'><strong>{$datos['no_cumple']}</strong></td>
                            <td style='text-align: center; color: #6c757d;'><strong>{$datos['no_aplica']}</strong></td>
                            <td style='text-align: center;'><strong>{$datos['total']}</strong></td>
                        </tr>";
        }

        $html .= "
                    </tbody>
                </table>

                <h2>Detalle de la Evaluación</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Ciclo</th>
                            <th>Estándar</th>
                            <th>Item</th>
                            <th>Evaluación</th>
                            <th>Valor</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($evaluaciones as $ev) {
            $evaluacion = $ev['evaluacion_inicial'] ?? '-';
            $badgeClass = 'badge-no-aplica';
            if ($evaluacion === 'CUMPLE TOTALMENTE') $badgeClass = 'badge-cumple';
            elseif ($evaluacion === 'NO CUMPLE') $badgeClass = 'badge-no-cumple';

            $html .= "
                        <tr>
                            <td>{$ev['ciclo']}</td>
                            <td>{$ev['estandar']}</td>
                            <td>{$ev['item_del_estandar']}</td>
                            <td><span class='badge {$badgeClass}'>{$evaluacion}</span></td>
                            <td>{$ev['valor']}</td>
                            <td>{$ev['puntaje_cuantitativo']}</td>
                        </tr>";
        }

        $html .= "
                    </tbody>
                </table>

                <div class='footer'>
                    <p><strong>Cycloid Talent SAS</strong></p>
                    <p>NIT: 901.653.912</p>
                    <p>Este documento hace parte de la socialización de la Evaluación de Estándares Mínimos del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) conforme al Decreto 1072 de 2015 y la Resolución 0312 de 2019.</p>
                    <p><a href='https://phorizontal.cycloidtalent.com'>Acceder a la Plataforma</a></p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Enviar email de socialización usando SendGrid
     */
    private function sendSocializacionEmail($destinatarios, $subject, $htmlContent)
    {
        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject($subject);

        // Agregar destinatarios principales (sin duplicados)
        $destinatarios = array_unique($destinatarios);
        foreach ($destinatarios as $correo) {
            $email->addTo($correo);
        }

        // Agregar emails en copia (CC)
        foreach ($this->ccEmails as $ccEmail) {
            // Evitar duplicados si el CC ya está en los destinatarios
            if (!in_array($ccEmail, $destinatarios)) {
                $email->addCc($ccEmail);
            }
        }

        $email->addContent("text/html", $htmlContent);

        // Obtener la clave API de SendGrid
        $sendgridApiKey = getenv('SENDGRID_API_KEY');

        if (!$sendgridApiKey) {
            log_message('error', 'SocializacionEmail: Clave API de SendGrid no configurada.');
            return [
                'success' => false,
                'message' => 'Error de configuración del servicio de correo'
            ];
        }

        $sendgrid = new \SendGrid($sendgridApiKey);

        try {
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "SocializacionEmail: Correo enviado exitosamente. Asunto: {$subject}. Destinatarios: " . implode(', ', $destinatarios));
                return [
                    'success' => true,
                    'message' => 'Email de socialización enviado correctamente a ' . implode(', ', $destinatarios)
                ];
            } else {
                log_message('error', "SocializacionEmail: Error al enviar correo. Status: {$response->statusCode()}. Body: {$response->body()}");
                return [
                    'success' => false,
                    'message' => 'Error al enviar el correo electrónico'
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'SocializacionEmail: Excepción al enviar correo: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ];
        }
    }
}
