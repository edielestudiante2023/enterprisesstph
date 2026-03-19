<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\NotificadorVisita;

/**
 * Comando spark para enviar recordatorios de visita 3 días antes.
 *
 * Uso manual:
 *   php spark visitas:recordatorio
 *
 * Cron (diario a las 7:00 AM en el servidor):
 *   0 7 * * * cd /www/wwwroot/phorizontal/enterprisesstph && php spark visitas:recordatorio >> writable/logs/cron_visitas.log 2>&1
 */
class RecordatorioVisitas extends BaseCommand
{
    protected $group       = 'Visitas';
    protected $name        = 'visitas:recordatorio';
    protected $description = 'Envía recordatorio de visita SST a consultores 3 días antes de la visita';
    protected $usage       = 'visitas:recordatorio';

    public function run(array $params)
    {
        // Acepta fecha como primer argumento posicional: php spark visitas:recordatorio 2026-03-19
        $fechaParam    = $params[0] ?? null;
        $fechaObjetivo = ($fechaParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaParam))
            ? $fechaParam
            : date('Y-m-d', strtotime('+3 days'));

        CLI::write('');
        CLI::write('=== Recordatorio Visitas SST ===', 'yellow');
        if ($fechaParam) {
            CLI::write("⚠ Modo prueba — fecha forzada: {$fechaObjetivo}", 'red');
        }
        CLI::write("Buscando visitas para: {$fechaObjetivo}", 'white');
        CLI::write('');

        $notificador = new NotificadorVisita();
        $resultados  = $notificador->enviarRecordatorios($fechaObjetivo);

        CLI::write('=== RESULTADOS ===', 'green');
        CLI::write("Emails enviados:        {$resultados['enviados']}", 'green');
        CLI::write("Sin pendientes (omit.): {$resultados['sin_datos']}", 'white');
        CLI::write(
            "Errores:                {$resultados['errores']}",
            $resultados['errores'] > 0 ? 'red' : 'white'
        );
        CLI::write('');
        CLI::write('Proceso completado — ' . date('Y-m-d H:i:s'), 'cyan');
        CLI::write('');
    }
}
