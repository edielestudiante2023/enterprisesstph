<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ContractModel;
use SendGrid\Mail\Mail;

class ResumenContratosSemanal extends BaseCommand
{
    protected $group       = 'Contratos';
    protected $name        = 'contratos:resumen-semanal';
    protected $description = 'Envía reporte semanal de contratos vencidos y próximos a vencer a diana.cuestas@cycloidtalent.com';
    protected $usage       = 'contratos:resumen-semanal';

    public function run(array $params)
    {
        CLI::write('Iniciando envío de resumen semanal de contratos...', 'yellow');

        $contractModel = new ContractModel();

        // Obtener contratos vencidos (activos con fecha_fin pasada)
        $expiredContracts = $contractModel->getExpiredActiveContracts();
        CLI::write("Contratos vencidos encontrados: " . count($expiredContracts), 'white');

        // Obtener contratos próximos a vencer en 30 días
        $expiringContracts = $contractModel->getExpiringContracts(30);
        CLI::write("Contratos próximos a vencer (30 días): " . count($expiringContracts), 'white');

        // Si no hay contratos en ninguna categoría, no enviar email
        if (empty($expiredContracts) && empty($expiringContracts)) {
            CLI::write('No hay contratos vencidos ni próximos a vencer. No se envió email.', 'green');
            return;
        }

        // Construir HTML del email
        $htmlContent = $this->buildHtml($expiredContracts, $expiringContracts);

        // Enviar email vía SendGrid
        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
            $email->setSubject("Reporte Semanal de Contratos - " . date('d/m/Y'));
            $email->addTo("diana.cuestas@cycloidtalent.com", "Diana Cuestas");
            $email->addContent("text/html", $htmlContent);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            CLI::write('');
            CLI::write('=== RESULTADOS ===', 'green');

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                CLI::write("Email enviado exitosamente. Código: " . $response->statusCode(), 'green');
                CLI::write("Contratos vencidos: " . count($expiredContracts), 'white');
                CLI::write("Contratos próximos a vencer: " . count($expiringContracts), 'white');
            } else {
                CLI::write("Error al enviar email. Código: " . $response->statusCode(), 'red');
                CLI::write("Body: " . $response->body(), 'red');
            }
        } catch (\Exception $e) {
            CLI::write("Excepción al enviar email: " . $e->getMessage(), 'red');
        }

        CLI::write('Proceso completado.', 'green');
    }

    /**
     * Construye el HTML del reporte semanal
     */
    private function buildHtml(array $expiredContracts, array $expiringContracts): string
    {
        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;'>
                <h2 style='color: #667eea;'>Reporte Semanal de Contratos</h2>
                <p style='color: #666;'>Generado el " . date('d/m/Y H:i:s') . "</p>
                <hr style='border: 1px solid #ddd;'>";

        // Sección: Contratos vencidos
        $html .= "<h3 style='color: #e53e3e; margin-top: 25px;'>Contratos Vencidos (" . count($expiredContracts) . ")</h3>";

        if (!empty($expiredContracts)) {
            $html .= "
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <thead>
                        <tr style='background-color: #e53e3e; color: white;'>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Cliente</th>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>N° Contrato</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Inicio</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Fin</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Días Vencido</th>
                            <th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Valor</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach ($expiredContracts as $contract) {
                $fechaFin = new \DateTime($contract['fecha_fin']);
                $hoy = new \DateTime();
                $diasVencido = $hoy->diff($fechaFin)->days;

                $html .= "
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['nombre_cliente']) . "</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['numero_contrato']) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_inicio'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_fin'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd; color: #e53e3e; font-weight: bold;'>" . $diasVencido . " días</td>
                            <td style='padding: 8px; text-align: right; border: 1px solid #ddd;'>$" . number_format($contract['valor_contrato'] ?? 0, 0, ',', '.') . "</td>
                        </tr>";
            }

            $html .= "</tbody></table>";
        } else {
            $html .= "<p style='color: #38a169;'>No hay contratos vencidos actualmente.</p>";
        }

        // Sección: Contratos próximos a vencer
        $html .= "<h3 style='color: #dd6b20; margin-top: 25px;'>Contratos Próximos a Vencer - 30 días (" . count($expiringContracts) . ")</h3>";

        if (!empty($expiringContracts)) {
            $html .= "
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <thead>
                        <tr style='background-color: #dd6b20; color: white;'>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Cliente</th>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>N° Contrato</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Inicio</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Fin</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Días Restantes</th>
                            <th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Valor</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach ($expiringContracts as $contract) {
                $fechaFin = new \DateTime($contract['fecha_fin']);
                $hoy = new \DateTime();
                $diasRestantes = (int)$hoy->diff($fechaFin)->format('%r%a');

                $colorDias = $diasRestantes <= 7 ? '#e53e3e' : ($diasRestantes <= 15 ? '#dd6b20' : '#38a169');

                $html .= "
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['nombre_cliente']) . "</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['numero_contrato']) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_inicio'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_fin'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd; color: " . $colorDias . "; font-weight: bold;'>" . $diasRestantes . " días</td>
                            <td style='padding: 8px; text-align: right; border: 1px solid #ddd;'>$" . number_format($contract['valor_contrato'] ?? 0, 0, ',', '.') . "</td>
                        </tr>";
            }

            $html .= "</tbody></table>";
        } else {
            $html .= "<p style='color: #38a169;'>No hay contratos próximos a vencer en los siguientes 30 días.</p>";
        }

        $html .= "
                <p style='color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;'>
                    Este es un reporte automático generado cada lunes por el sistema de gestión de contratos de Cycloid Talent.<br>
                    Para más detalles, ingrese a <a href='https://phorizontal.cycloidtalent.com/contracts'>phorizontal.cycloidtalent.com/contracts</a>
                </p>
            </div>";

        return $html;
    }
}
