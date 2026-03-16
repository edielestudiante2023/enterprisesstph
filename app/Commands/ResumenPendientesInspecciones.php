<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\ResumenPendientesNotificador;

class ResumenPendientesInspecciones extends BaseCommand
{
    protected $group       = 'Inspecciones';
    protected $name        = 'inspecciones:resumen-pendientes';
    protected $description = 'Envia resumen diario de documentos en borrador o pendiente de firma a cada consultor';
    protected $usage       = 'inspecciones:resumen-pendientes';

    public function run(array $params)
    {
        CLI::write('Iniciando resumen de pendientes de inspecciones...', 'yellow');
        CLI::write('Fecha: ' . date('Y-m-d H:i:s'), 'white');
        CLI::write('');

        $notificador = new ResumenPendientesNotificador();
        $resultados  = $notificador->enviarResumenDiario();

        if (isset($resultados['error'])) {
            CLI::write('ERROR: ' . $resultados['error'], 'red');
            return;
        }

        CLI::write('=== RESULTADOS ===', 'green');
        CLI::write("Emails enviados:        {$resultados['enviados']}", 'green');
        CLI::write("Errores de envio:       {$resultados['errores']}", $resultados['errores'] > 0 ? 'red' : 'white');
        CLI::write("Sin pendientes:         {$resultados['sin_pendientes']}", 'white');
        CLI::write('');
        CLI::write('Proceso completado.', 'green');
    }
}
