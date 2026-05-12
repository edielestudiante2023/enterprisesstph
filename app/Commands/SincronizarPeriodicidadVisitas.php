<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CicloVisitaModel;

/**
 * Sincroniza tbl_ciclos_visita.estandar con la frecuencia_visitas del
 * contrato más reciente de cada cliente. Red de seguridad — Capa 3.
 *
 * Uso:
 *   php spark sincronizar:periodicidad-visitas
 *
 * Configurar como cron diario:
 *   0 4 * * * cd /www/wwwroot/phorizontal/enterprisesstph && php spark sincronizar:periodicidad-visitas
 */
class SincronizarPeriodicidadVisitas extends BaseCommand
{
    protected $group       = 'Auditoria';
    protected $name        = 'sincronizar:periodicidad-visitas';
    protected $description = 'Sincroniza la periodicidad de los ciclos de visita con la frecuencia del contrato más reciente de cada cliente';
    protected $usage       = 'sincronizar:periodicidad-visitas';

    public function run(array $params)
    {
        CLI::write('Sincronizando periodicidad ciclos visita ↔ contrato más reciente...', 'yellow');

        $model = new CicloVisitaModel();
        try {
            $afectados = $model->sincronizarPeriodicidadDesdeContratos();
            if ($afectados > 0) {
                CLI::write("OK: {$afectados} ciclo(s) actualizado(s).", 'green');
            } else {
                CLI::write('OK: todos los ciclos ya estaban sincronizados.', 'white');
            }
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage());
            log_message('error', '[SincronizarPeriodicidadVisitas] ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
