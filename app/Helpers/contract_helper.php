<?php

use App\Models\ContractModel;
use App\Libraries\ContractLibrary;

if (!function_exists('get_active_contract')) {
    /**
     * Obtiene el contrato activo de un cliente
     */
    function get_active_contract($idCliente)
    {
        $contractModel = new ContractModel();
        return $contractModel->getActiveContract($idCliente);
    }
}

if (!function_exists('get_client_renewals')) {
    /**
     * Obtiene el número de renovaciones de un cliente
     */
    function get_client_renewals($idCliente)
    {
        $contractModel = new ContractModel();
        return $contractModel->countRenewals($idCliente);
    }
}

if (!function_exists('get_client_antiquity')) {
    /**
     * Obtiene la antigüedad del cliente en meses
     */
    function get_client_antiquity($idCliente)
    {
        $contractModel = new ContractModel();
        return $contractModel->getClientAntiquity($idCliente);
    }
}

if (!function_exists('get_first_contract_date')) {
    /**
     * Obtiene la fecha del primer contrato de un cliente
     */
    function get_first_contract_date($idCliente)
    {
        $contractModel = new ContractModel();
        return $contractModel->getFirstContractDate($idCliente);
    }
}

if (!function_exists('format_contract_status')) {
    /**
     * Formatea el estado del contrato con badge HTML
     */
    function format_contract_status($estado)
    {
        $badges = [
            'activo' => '<span class="badge bg-success">Activo</span>',
            'vencido' => '<span class="badge bg-danger">Vencido</span>',
            'cancelado' => '<span class="badge bg-secondary">Cancelado</span>'
        ];

        return $badges[$estado] ?? '<span class="badge bg-warning">Desconocido</span>';
    }
}

if (!function_exists('format_contract_type')) {
    /**
     * Formatea el tipo de contrato con badge HTML
     */
    function format_contract_type($tipo)
    {
        $badges = [
            'inicial' => '<span class="badge bg-primary">Inicial</span>',
            'renovacion' => '<span class="badge bg-info">Renovación</span>',
            'ampliacion' => '<span class="badge bg-warning">Ampliación</span>'
        ];

        return $badges[$tipo] ?? '<span class="badge bg-secondary">Otro</span>';
    }
}

if (!function_exists('days_until_expiration')) {
    /**
     * Calcula los días hasta el vencimiento de un contrato
     */
    function days_until_expiration($fechaFin)
    {
        $fechaFin = new DateTime($fechaFin);
        $hoy = new DateTime();
        $diferencia = $hoy->diff($fechaFin);

        return (int)$diferencia->format('%r%a');
    }
}

if (!function_exists('is_contract_expiring_soon')) {
    /**
     * Verifica si un contrato está próximo a vencer (30 días o menos)
     */
    function is_contract_expiring_soon($fechaFin, $days = 30)
    {
        $diasRestantes = days_until_expiration($fechaFin);
        return $diasRestantes >= 0 && $diasRestantes <= $days;
    }
}

if (!function_exists('is_contract_expired')) {
    /**
     * Verifica si un contrato está vencido
     */
    function is_contract_expired($fechaFin)
    {
        return days_until_expiration($fechaFin) < 0;
    }
}

if (!function_exists('get_contract_alert_class')) {
    /**
     * Obtiene la clase CSS para alertas según días restantes
     */
    function get_contract_alert_class($fechaFin)
    {
        $diasRestantes = days_until_expiration($fechaFin);

        if ($diasRestantes < 0) {
            return 'danger'; // Vencido
        } elseif ($diasRestantes <= 7) {
            return 'danger'; // Crítico
        } elseif ($diasRestantes <= 15) {
            return 'warning'; // Advertencia
        } elseif ($diasRestantes <= 30) {
            return 'info'; // Información
        }

        return 'success'; // Normal
    }
}

if (!function_exists('format_contract_dates')) {
    /**
     * Formatea el rango de fechas de un contrato
     */
    function format_contract_dates($fechaInicio, $fechaFin)
    {
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);

        return $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y');
    }
}

if (!function_exists('get_contract_duration')) {
    /**
     * Calcula la duración del contrato en meses
     */
    function get_contract_duration($fechaInicio, $fechaFin)
    {
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $diferencia = $inicio->diff($fin);

        return ($diferencia->y * 12) + $diferencia->m;
    }
}

if (!function_exists('get_expiring_contracts_count')) {
    /**
     * Obtiene el número de contratos próximos a vencer para un consultor
     */
    function get_expiring_contracts_count($idConsultor = null, $days = 30)
    {
        $contractLibrary = new ContractLibrary();
        $alerts = $contractLibrary->getContractAlerts($idConsultor, $days);

        return count($alerts);
    }
}

if (!function_exists('format_money')) {
    /**
     * Formatea un valor monetario
     */
    function format_money($valor, $currency = 'COP')
    {
        if ($valor === null || $valor === '') {
            return 'N/A';
        }

        $formatted = number_format($valor, 0, ',', '.');

        return '$' . $formatted . ' ' . $currency;
    }
}

if (!function_exists('get_contract_history_summary')) {
    /**
     * Obtiene un resumen del historial de contratos de un cliente
     */
    function get_contract_history_summary($idCliente)
    {
        $contractLibrary = new ContractLibrary();
        return $contractLibrary->getClientContractHistory($idCliente);
    }
}

if (!function_exists('sync_client_contract_dates')) {
    /**
     * Sincroniza las fechas de contrato en tbl_clientes
     */
    function sync_client_contract_dates($idCliente)
    {
        $contractLibrary = new ContractLibrary();
        return $contractLibrary->updateClientDates($idCliente);
    }
}
