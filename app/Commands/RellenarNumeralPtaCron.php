<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\IADocumentacionService;

/**
 * Cron diario: llena con IA (OpenAI) la columna numeral_plandetrabajo de
 * tbl_pta_cliente en las actividades que aún no tienen numeral.
 *
 * No sobrescribe numerales existentes. Procesa en lotes para acotar el costo
 * y el tiempo de cada corrida.
 *
 * Uso:
 *   php spark pta:rellenar-numeral
 *   php spark pta:rellenar-numeral --dry-run
 *   php spark pta:rellenar-numeral --limit=50
 */
class RellenarNumeralPtaCron extends BaseCommand
{
    protected $group       = 'PTA';
    protected $name        = 'pta:rellenar-numeral';
    protected $description = 'Llena con IA (OpenAI) el numeral del Decreto 1072 en actividades PTA sin numeral.';
    protected $usage       = 'pta:rellenar-numeral [--dry-run] [--limit=150]';

    /** Actividades por llamada a OpenAI. */
    private const BATCH = 15;

    public function run(array $params)
    {
        $dryRun = CLI::getOption('dry-run') !== null || isset($params['dry-run']);

        // Soporta tanto "--limit 50" (CI4 nativo) como "--limit=50".
        $limit    = 150;
        $optLimit = CLI::getOption('limit');
        if (is_numeric($optLimit)) {
            $limit = (int) $optLimit;
        } else {
            foreach (($_SERVER['argv'] ?? []) as $a) {
                if (preg_match('/^--limit=(\d+)$/', (string) $a, $m)) {
                    $limit = (int) $m[1];
                    break;
                }
            }
        }
        if ($limit < 1) {
            $limit = 150;
        }

        CLI::write('=== Rellenar numeral PTA con IA (OpenAI) ===', 'yellow');
        CLI::write('Hora: ' . date('Y-m-d H:i:s'), 'white');
        CLI::write('Modo: ' . ($dryRun ? 'DRY-RUN (no escribe)' : 'REAL') . ' | Límite: ' . $limit, 'white');
        CLI::write('');

        $db = \Config\Database::connect();

        // Filas objetivo: numeral vacío (NULL, '' o '-') y con actividad escrita.
        $rows = $db->table('tbl_pta_cliente')
            ->select('id_ptacliente, id_cliente, phva_plandetrabajo, actividad_plandetrabajo')
            ->groupStart()
                ->where('numeral_plandetrabajo IS NULL', null, false)
                ->orWhere("TRIM(numeral_plandetrabajo) = ''", null, false)
                ->orWhere("TRIM(numeral_plandetrabajo) = '-'", null, false)
            ->groupEnd()
            ->where("TRIM(actividad_plandetrabajo) <> ''", null, false)
            ->orderBy('id_ptacliente', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $total = count($rows);
        CLI::write("Actividades sin numeral a procesar: {$total}", 'white');
        if ($total === 0) {
            CLI::write('Nada que hacer.', 'green');
            return;
        }

        try {
            $ia = new IADocumentacionService();
        } catch (\Throwable $e) {
            CLI::error('No se pudo iniciar el servicio de IA: ' . $e->getMessage());
            log_message('error', 'RellenarNumeralPtaCron init: ' . $e->getMessage());
            return;
        }

        $chunks       = array_chunk($rows, self::BATCH);
        $actualizadas = 0;
        $sinNumeral   = 0;
        $errores      = 0;

        foreach ($chunks as $idx => $chunk) {
            CLI::write('[' . ($idx + 1) . '/' . count($chunks) . '] Lote de ' . count($chunk) . '... ', 'white', false);

            try {
                $mapa = $this->numeralesIA($ia, $chunk);
            } catch (\Throwable $e) {
                CLI::write('ERROR: ' . $e->getMessage(), 'red');
                log_message('error', 'RellenarNumeralPtaCron lote ' . ($idx + 1) . ': ' . $e->getMessage());
                $errores += count($chunk);
                continue;
            }

            $okLote = 0;
            foreach ($chunk as $i => $r) {
                $idPta   = (int) $r['id_ptacliente'];
                $numeral = $mapa[$i + 1] ?? '';

                if ($numeral === '') {
                    $sinNumeral++;
                    log_message('info', "RellenarNumeralPtaCron: sin numeral id_ptacliente={$idPta} actividad=\""
                        . mb_substr($r['actividad_plandetrabajo'], 0, 80) . '"');
                    continue;
                }

                if ($dryRun) {
                    CLI::write('');
                    CLI::write("  [DRY] id_ptacliente={$idPta} → {$numeral}  ("
                        . mb_substr($r['actividad_plandetrabajo'], 0, 60) . ')', 'cyan');
                } else {
                    $db->table('tbl_pta_cliente')
                        ->where('id_ptacliente', $idPta)
                        ->update([
                            'numeral_plandetrabajo' => $numeral,
                            'updated_at'            => date('Y-m-d H:i:s'),
                        ]);
                }
                $actualizadas++;
                $okLote++;
            }
            CLI::write('OK (' . $okLote . ' con numeral)', 'green');
        }

        CLI::write('');
        CLI::write('=== RESUMEN ===', 'yellow');
        CLI::write(($dryRun ? 'Se llenarían' : 'Actualizadas') . ": {$actualizadas}", 'green');
        CLI::write("Sin numeral (IA no determinó): {$sinNumeral}", 'white');
        CLI::write("Con error: {$errores}", $errores > 0 ? 'red' : 'white');
        CLI::write('Modelo: ' . env('OPENAI_MODEL', 'gpt-4o-mini'), 'white');
    }

    /**
     * Envía un lote de actividades a OpenAI y devuelve [indice1based => numeral].
     * El numeral se valida contra el formato del Decreto 1072 (dígitos y puntos).
     */
    private function numeralesIA(IADocumentacionService $ia, array $chunk): array
    {
        $lista = '';
        foreach ($chunk as $i => $r) {
            $phva = trim((string) ($r['phva_plandetrabajo'] ?? '')) ?: 'N/D';
            $act  = trim((string) $r['actividad_plandetrabajo']);
            $lista .= ($i + 1) . ". [PHVA: {$phva}] {$act}\n";
        }

        $prompt = "Eres experto en SG-SST Decreto 1072 de 2015 para copropiedades (propiedad horizontal) en Colombia.\n\n"
            . "A continuación una lista de actividades del Plan de Trabajo Anual (PTA). "
            . "Para cada una determina el numeral del Decreto 1072 de 2015 más apropiado.\n\n"
            . "Actividades:\n" . $lista . "\n"
            . "Devuelve ÚNICAMENTE un JSON array. Cada elemento debe tener:\n"
            . "- \"i\": número de la actividad (entero, igual al de la lista)\n"
            . "- \"numeral\": string con el numeral del Decreto 1072 (solo dígitos y puntos, ej \"1.2.3\"). "
            . "Si no puedes determinarlo con confianza, usa cadena vacía \"\".\n\n"
            . "Responde SOLO el JSON válido, sin markdown ni texto adicional.";

        $maxTokens = 80 * count($chunk) + 200;
        $raw = trim($ia->generarContenido($prompt, $maxTokens));

        if (str_starts_with($raw, '```')) {
            $raw = trim(preg_replace('/^```(json)?\s*|\s*```\s*$/i', '', $raw));
        }

        $parsed = json_decode($raw, true);
        if (!is_array($parsed)) {
            throw new \RuntimeException('Respuesta de IA no es JSON válido.');
        }

        $mapa = [];
        foreach ($parsed as $item) {
            if (!is_array($item) || !isset($item['i'])) {
                continue;
            }
            $i       = (int) $item['i'];
            $numeral = trim((string) ($item['numeral'] ?? ''));
            // Solo dígitos y puntos; cualquier otra cosa se descarta.
            if ($numeral !== '' && !preg_match('/^[0-9]+(\.[0-9]+)*$/', $numeral)) {
                $numeral = '';
            }
            $mapa[$i] = mb_substr($numeral, 0, 60);
        }
        return $mapa;
    }
}
