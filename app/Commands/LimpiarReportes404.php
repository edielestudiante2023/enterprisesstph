<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ReporteModel;
use App\Models\ContractModel;
use App\Models\MatrizModel;
use App\Models\InformeAvancesModel;

class LimpiarReportes404 extends BaseCommand
{
    protected $group       = 'Mantenimiento';
    protected $name        = 'reportes:limpiar-404';
    protected $description = 'Elimina registros de tbl_reporte cuyos archivos no existen y limpia enlaces rotos en contratos, matrices e informes';
    protected $usage       = 'reportes:limpiar-404 [--dry-run] [--verbose]';

    public function run(array $params)
    {
        // Fallback: definir UPLOADS_PATH si no existe en Constants.php (producción legacy)
        if (!defined('UPLOADS_PATH')) {
            define('UPLOADS_PATH', getenv('UPLOADS_PATH') ?: ROOTPATH . '../soportes-clientes/');
        }

        $dryRun  = CLI::getOption('dry-run') !== null;
        $verbose = CLI::getOption('verbose') !== null;

        if ($dryRun) {
            CLI::write('=== MODO DRY-RUN (no se modificará nada) ===', 'yellow');
            CLI::newLine();
        }

        $this->limpiarReportes($dryRun, $verbose);
        CLI::newLine();
        $this->limpiarContratos($dryRun, $verbose);
        CLI::newLine();
        $this->limpiarMatrices($dryRun, $verbose);
        CLI::newLine();
        $this->limpiarInformesAvances($dryRun, $verbose);
    }

    // ─── tbl_reporte ───────────────────────────────────────────────
    private function limpiarReportes(bool $dryRun, bool $verbose): void
    {
        CLI::write('── tbl_reporte (campo: enlace) ──', 'light_cyan');

        $model    = new ReporteModel();
        $reportes = $model->where('enlace IS NOT NULL')->where('enlace !=', '')->findAll();

        $total = count($reportes);
        $ok = $missing = $deleted = 0;

        CLI::write("Verificando $total reportes...", 'yellow');

        foreach ($reportes as $i => $r) {
            $ruta = $this->extraerRutaRelativa($r['enlace']);

            if (!$ruta) {
                if ($verbose) CLI::write("  [{$r['id_reporte']}] SKIP - No se pudo parsear: {$r['enlace']}", 'light_gray');
                continue;
            }

            if ($this->archivoExiste($ruta)) {
                $ok++;
                if ($verbose) CLI::write("  [{$r['id_reporte']}] OK: $ruta", 'green');
            } else {
                $missing++;
                if ($verbose) CLI::write("  [{$r['id_reporte']}] 404: $ruta", 'red');
                if (!$dryRun) {
                    $model->delete($r['id_reporte']);
                    $deleted++;
                }
            }

            if (($i + 1) % 100 === 0) CLI::write("  Progreso: " . ($i + 1) . "/$total", 'light_gray');
        }

        $this->resumen('tbl_reporte', $total, $ok, $missing, $deleted, $dryRun);
    }

    // ─── tbl_contratos ─────────────────────────────────────────────
    private function limpiarContratos(bool $dryRun, bool $verbose): void
    {
        CLI::write('── tbl_contratos (campos: ruta_pdf_contrato, firma_cliente_imagen) ──', 'light_cyan');

        $model     = new ContractModel();
        $contratos = $model->findAll();

        $total = count($contratos);
        $pdfOk = $pdfMissing = $firmaOk = $firmaMissing = 0;

        CLI::write("Verificando $total contratos...", 'yellow');

        foreach ($contratos as $c) {
            $id = $c['id_contrato'];

            // ruta_pdf_contrato
            if (!empty($c['ruta_pdf_contrato'])) {
                $ruta = $this->extraerRutaRelativa($c['ruta_pdf_contrato']);
                if ($ruta && $this->archivoExiste($ruta)) {
                    $pdfOk++;
                    if ($verbose) CLI::write("  [$id] PDF OK: $ruta", 'green');
                } else {
                    $pdfMissing++;
                    if ($verbose) CLI::write("  [$id] PDF 404: {$c['ruta_pdf_contrato']}", 'red');
                    if (!$dryRun) {
                        $model->update($id, ['ruta_pdf_contrato' => null, 'contrato_generado' => 0]);
                    }
                }
            }

            // firma_cliente_imagen
            if (!empty($c['firma_cliente_imagen'])) {
                $ruta = $this->extraerRutaRelativa($c['firma_cliente_imagen']);
                if ($ruta && $this->archivoExiste($ruta)) {
                    $firmaOk++;
                    if ($verbose) CLI::write("  [$id] Firma OK: $ruta", 'green');
                } else {
                    $firmaMissing++;
                    if ($verbose) CLI::write("  [$id] Firma 404: {$c['firma_cliente_imagen']}", 'red');
                    if (!$dryRun) {
                        $model->update($id, ['firma_cliente_imagen' => null]);
                    }
                }
            }
        }

        CLI::newLine();
        CLI::write("  PDFs:   $pdfOk OK / $pdfMissing 404", $pdfMissing ? 'red' : 'green');
        CLI::write("  Firmas: $firmaOk OK / $firmaMissing 404", $firmaMissing ? 'red' : 'green');
        if ($dryRun) CLI::write("  (DRY RUN - no se modificó nada)", 'yellow');
    }

    // ─── tbl_matrices ──────────────────────────────────────────────
    private function limpiarMatrices(bool $dryRun, bool $verbose): void
    {
        CLI::write('── tbl_matrices (campo: enlace) ──', 'light_cyan');

        $model    = new MatrizModel();
        $matrices = $model->where('enlace IS NOT NULL')->where('enlace !=', '')->findAll();

        $total = count($matrices);
        $ok = $missing = $deleted = 0;

        CLI::write("Verificando $total matrices...", 'yellow');

        foreach ($matrices as $m) {
            $ruta = $this->extraerRutaRelativa($m['enlace']);

            if (!$ruta) {
                if ($verbose) CLI::write("  [{$m['id_matriz']}] SKIP: {$m['enlace']}", 'light_gray');
                continue;
            }

            if ($this->archivoExiste($ruta)) {
                $ok++;
                if ($verbose) CLI::write("  [{$m['id_matriz']}] OK: $ruta", 'green');
            } else {
                $missing++;
                if ($verbose) CLI::write("  [{$m['id_matriz']}] 404: $ruta", 'red');
                if (!$dryRun) {
                    $model->delete($m['id_matriz']);
                    $deleted++;
                }
            }
        }

        $this->resumen('tbl_matrices', $total, $ok, $missing, $deleted, $dryRun);
    }

    // ─── tbl_informe_avances ───────────────────────────────────────
    private function limpiarInformesAvances(bool $dryRun, bool $verbose): void
    {
        CLI::write('── tbl_informe_avances (campos de imágenes) ──', 'light_cyan');

        $camposImg = [
            'img_cumplimiento_estandares',
            'img_indicador_plan_trabajo',
            'img_indicador_capacitacion',
            'soporte_1_imagen',
            'soporte_2_imagen',
            'soporte_3_imagen',
            'soporte_4_imagen',
        ];

        $model    = new InformeAvancesModel();
        $informes = $model->findAll();

        $total = count($informes);
        $okCount = $missingCount = 0;

        CLI::write("Verificando $total informes ({$this->count($camposImg)} campos c/u)...", 'yellow');

        foreach ($informes as $inf) {
            $id      = $inf['id'];
            $nullear = [];

            foreach ($camposImg as $campo) {
                if (empty($inf[$campo])) continue;

                $ruta = $this->extraerRutaRelativa($inf[$campo]);
                if ($ruta && $this->archivoExiste($ruta)) {
                    $okCount++;
                    if ($verbose) CLI::write("  [$id] $campo OK: $ruta", 'green');
                } else {
                    $missingCount++;
                    $nullear[$campo] = null;
                    if ($verbose) CLI::write("  [$id] $campo 404: {$inf[$campo]}", 'red');
                }
            }

            if (!empty($nullear) && !$dryRun) {
                $model->update($id, $nullear);
            }
        }

        CLI::newLine();
        CLI::write("  Imágenes OK:  $okCount", 'green');
        CLI::write("  Imágenes 404: $missingCount", $missingCount ? 'red' : 'green');
        if ($dryRun) CLI::write("  (DRY RUN - no se modificó nada)", 'yellow');
    }

    // ─── Helpers ───────────────────────────────────────────────────

    private function extraerRutaRelativa(string $enlace): ?string
    {
        // URL completa: https://phorizontal.cycloidtalent.com/uploads/... o /serve-file/...
        if (preg_match('#/(?:uploads|serve-file)/(.+)$#', $enlace, $m)) {
            return $m[1];
        }
        // Ruta relativa: uploads/... o serve-file/...
        if (preg_match('#^(?:uploads|serve-file)/(.+)$#', $enlace, $m)) {
            return $m[1];
        }
        return null;
    }

    private function archivoExiste(string $rutaRelativa): bool
    {
        return file_exists(UPLOADS_PATH . $rutaRelativa)
            || file_exists(FCPATH . 'uploads/' . $rutaRelativa);
    }

    private function resumen(string $tabla, int $total, int $ok, int $missing, int $deleted, bool $dryRun): void
    {
        CLI::newLine();
        CLI::write("  Total:   $total", 'white');
        CLI::write("  OK:      $ok", 'green');
        CLI::write("  404:     $missing", $missing ? 'red' : 'green');
        if ($dryRun) {
            CLI::write("  (DRY RUN - no se eliminó nada)", 'yellow');
        } else {
            CLI::write("  Eliminados: $deleted", 'red');
        }
    }

    private function count(array $arr): int
    {
        return \count($arr);
    }
}
