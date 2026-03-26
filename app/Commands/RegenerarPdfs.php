<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RegenerarPdfs extends BaseCommand
{
    protected $group       = 'Mantenimiento';
    protected $name        = 'regenerar:pdfs';
    protected $description = 'Regenera todos los PDFs de inspecciones, informes y contratos desde la BD';
    protected $usage       = 'regenerar:pdfs [--modulo=extintores] [--id=5] [--dry-run]';

    /**
     * Mapeo de módulos: slug => [controllerClass, modelClass, estadoField, estadoValue]
     * estadoField/estadoValue: para filtrar solo registros finalizados
     */
    private function getModulos(): array
    {
        return [
            'extintores' => [
                'controller' => \App\Controllers\Inspecciones\InspeccionExtintoresController::class,
                'table'      => 'tbl_inspeccion_extintores',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'locativa' => [
                'controller' => \App\Controllers\Inspecciones\InspeccionLocativaController::class,
                'table'      => 'tbl_inspeccion_locativa',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'senalizacion' => [
                'controller' => \App\Controllers\Inspecciones\InspeccionSenalizacionController::class,
                'table'      => 'tbl_inspeccion_senalizacion',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'botiquin' => [
                'controller' => \App\Controllers\Inspecciones\InspeccionBotiquinController::class,
                'table'      => 'tbl_inspeccion_botiquin',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'gabinete' => [
                'controller' => \App\Controllers\Inspecciones\InspeccionGabineteController::class,
                'table'      => 'tbl_inspeccion_gabinetes',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'comunicacion' => [
                'controller' => \App\Controllers\Inspecciones\InspeccionComunicacionController::class,
                'table'      => 'tbl_inspeccion_comunicaciones',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'recursos-seguridad' => [
                'controller' => \App\Controllers\Inspecciones\InspeccionRecursosSeguridadController::class,
                'table'      => 'tbl_inspeccion_recursos_seguridad',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'acta-visita' => [
                'controller' => \App\Controllers\Inspecciones\ActaVisitaController::class,
                'table'      => 'tbl_acta_visita',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'reporte-capacitacion' => [
                'controller' => \App\Controllers\Inspecciones\ReporteCapacitacionController::class,
                'table'      => 'tbl_reporte_capacitacion',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'asistencia-induccion' => [
                'controller' => \App\Controllers\Inspecciones\AsistenciaInduccionController::class,
                'table'      => 'tbl_asistencia_induccion',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'dotacion-vigilante' => [
                'controller' => \App\Controllers\Inspecciones\DotacionVigilanteController::class,
                'table'      => 'tbl_dotacion_vigilante',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'dotacion-todero' => [
                'controller' => \App\Controllers\Inspecciones\DotacionToderoController::class,
                'table'      => 'tbl_dotacion_todero',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'dotacion-aseadora' => [
                'controller' => \App\Controllers\Inspecciones\DotacionAseadoraController::class,
                'table'      => 'tbl_dotacion_aseadora',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'evaluacion-simulacro' => [
                'controller' => \App\Controllers\Inspecciones\EvaluacionSimulacroController::class,
                'table'      => 'tbl_evaluacion_simulacro',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'preparacion-simulacro' => [
                'controller' => \App\Controllers\Inspecciones\PreparacionSimulacroController::class,
                'table'      => 'tbl_preparacion_simulacro',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'hv-brigadista' => [
                'controller' => \App\Controllers\Inspecciones\HvBrigadistaController::class,
                'table'      => 'tbl_hv_brigadista',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'carta-vigia' => [
                'controller' => \App\Controllers\Inspecciones\CartaVigiaPwaController::class,
                'table'      => 'tbl_carta_vigia',
                'estado'     => null, // No tiene campo estado; regenera si tiene ruta_pdf
                'filtro_sql' => "ruta_pdf IS NOT NULL AND ruta_pdf != ''",
            ],
            'probabilidad-peligros' => [
                'controller' => \App\Controllers\Inspecciones\ProbabilidadPeligrosController::class,
                'table'      => 'tbl_probabilidad_peligros',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'matriz-vulnerabilidad' => [
                'controller' => \App\Controllers\Inspecciones\MatrizVulnerabilidadController::class,
                'table'      => 'tbl_matriz_vulnerabilidad',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'plan-emergencia' => [
                'controller' => \App\Controllers\Inspecciones\PlanEmergenciaController::class,
                'table'      => 'tbl_plan_emergencia',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'kpi-residuos' => [
                'controller' => \App\Controllers\Inspecciones\KpiResiduosController::class,
                'table'      => 'tbl_kpi_residuos',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'kpi-plagas' => [
                'controller' => \App\Controllers\Inspecciones\KpiPlagasController::class,
                'table'      => 'tbl_kpi_plagas',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'kpi-limpieza' => [
                'controller' => \App\Controllers\Inspecciones\KpiLimpiezaController::class,
                'table'      => 'tbl_kpi_limpieza',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'kpi-agua-potable' => [
                'controller' => \App\Controllers\Inspecciones\KpiAguaPotableController::class,
                'table'      => 'tbl_kpi_agua_potable',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'programa-residuos' => [
                'controller' => \App\Controllers\Inspecciones\ProgramaResiduosController::class,
                'table'      => 'tbl_programa_residuos',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'programa-plagas' => [
                'controller' => \App\Controllers\Inspecciones\ProgramaPlagasController::class,
                'table'      => 'tbl_programa_plagas',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'programa-limpieza' => [
                'controller' => \App\Controllers\Inspecciones\ProgramaLimpiezaController::class,
                'table'      => 'tbl_programa_limpieza',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'programa-agua-potable' => [
                'controller' => \App\Controllers\Inspecciones\ProgramaAguaPotableController::class,
                'table'      => 'tbl_programa_agua_potable',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'plan-saneamiento' => [
                'controller' => \App\Controllers\Inspecciones\PlanSaneamientoController::class,
                'table'      => 'tbl_plan_saneamiento',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'contingencia-plagas' => [
                'controller' => \App\Controllers\Inspecciones\PlanContingenciaPlagasController::class,
                'table'      => 'tbl_plan_contingencia_plagas',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'contingencia-basura' => [
                'controller' => \App\Controllers\Inspecciones\PlanContingenciaBasuraController::class,
                'table'      => 'tbl_plan_contingencia_basura',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'contingencia-agua' => [
                'controller' => \App\Controllers\Inspecciones\PlanContingenciaAguaController::class,
                'table'      => 'tbl_plan_contingencia_agua',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
            'auditoria-zona-residuos' => [
                'controller' => \App\Controllers\Inspecciones\AuditoriaZonaResiduosController::class,
                'table'      => 'tbl_auditoria_zona_residuos',
                'estado'     => ['campo' => 'estado', 'valor' => 'completo'],
            ],
        ];
    }

    public function run(array $params)
    {
        $moduloFiltro = CLI::getOption('modulo') ?: ($params['modulo'] ?? null);
        $idFiltro     = CLI::getOption('id') ?: ($params['id'] ?? null);
        $dryRun       = CLI::getOption('dry-run') !== null || isset($params['dry-run']);

        if ($moduloFiltro) {
            CLI::write("Filtrando por módulo: {$moduloFiltro}", 'white');
        }
        if ($dryRun) {
            CLI::write('=== DRY RUN — no se generarán PDFs ===', 'yellow');
        }

        $db = \Config\Database::connect();
        $totalOk = 0;
        $totalError = 0;
        $resumen = [];

        // ─── 1. INSPECCIONES ───
        $modulos = $this->getModulos();

        if ($moduloFiltro && $moduloFiltro !== 'contratos' && $moduloFiltro !== 'informes') {
            if (!isset($modulos[$moduloFiltro])) {
                CLI::error("Módulo '{$moduloFiltro}' no encontrado. Disponibles: " . implode(', ', array_keys($modulos)));
                return;
            }
            $modulos = [$moduloFiltro => $modulos[$moduloFiltro]];
        }

        if (!$moduloFiltro || ($moduloFiltro !== 'contratos' && $moduloFiltro !== 'informes')) {
            foreach ($modulos as $slug => $config) {
                CLI::write("\n══ {$slug} ══", 'cyan');

                // Reconectar si la conexión se perdió
                try {
                    $db->query('SELECT 1');
                } catch (\Throwable $e) {
                    try {
                        $db->close();
                        $db->initialize();
                    } catch (\Throwable $e2) {
                        CLI::error("  No se pudo reconectar a la BD: " . $e2->getMessage());
                        break;
                    }
                }

                try {
                    // Obtener IDs
                    $builder = $db->table($config['table'])->select('id');

                    if ($idFiltro) {
                        $builder->where('id', $idFiltro);
                    } elseif (!empty($config['estado'])) {
                        $builder->where($config['estado']['campo'], $config['estado']['valor']);
                    } elseif (!empty($config['filtro_sql'])) {
                        $builder->where($config['filtro_sql']);
                    }

                    $rows = $builder->get()->getResultArray();
                } catch (\Throwable $e) {
                    CLI::write("  ERROR consultando tabla: " . $e->getMessage(), 'red');
                    $resumen[$slug] = ['ok' => 0, 'error' => 1, 'total' => 0];
                    $totalError++;
                    continue;
                }

                $total = count($rows);

                if ($total === 0) {
                    CLI::write("  Sin registros para regenerar.", 'light_gray');
                    $resumen[$slug] = ['ok' => 0, 'error' => 0, 'total' => 0];
                    continue;
                }

                CLI::write("  {$total} registros encontrados", 'white');

                if ($dryRun) {
                    $resumen[$slug] = ['ok' => 0, 'error' => 0, 'total' => $total];
                    continue;
                }

                $ctrl = new $config['controller']();
                // Inyectar request mínimo para que el controlador funcione
                $ctrl->initController(
                    \Config\Services::request(),
                    \Config\Services::response(),
                    \Config\Services::logger()
                );

                $ok = 0;
                $errors = 0;
                foreach ($rows as $i => $row) {
                    $id = $row['id'];
                    $pos = $i + 1;
                    try {
                        $ctrl->regenerarPdf($id);
                        $ok++;
                        CLI::write("  [{$slug} {$pos}/{$total}] ID {$id} OK", 'green');
                    } catch (\Throwable $e) {
                        $errors++;
                        CLI::write("  [{$slug} {$pos}/{$total}] ID {$id} ERROR: " . $e->getMessage(), 'red');
                    }
                }

                $totalOk += $ok;
                $totalError += $errors;
                $resumen[$slug] = ['ok' => $ok, 'error' => $errors, 'total' => $total];
            }
        }

        // ─── 2. CONTRATOS ───
        if (!$moduloFiltro || $moduloFiltro === 'contratos') {
            CLI::write("\n══ contratos ══", 'cyan');

            try {
                $db->query('SELECT 1');
            } catch (\Throwable $e) {
                try { $db->close(); $db->initialize(); } catch (\Throwable $e2) {
                    CLI::error("  No se pudo reconectar a la BD");
                }
            }

            try {
                $builder = $db->table('tbl_contratos')
                    ->select('id_contrato')
                    ->where('contrato_generado', 1);

                if ($idFiltro) {
                    $builder->where('id_contrato', $idFiltro);
                }

                $rows = $builder->get()->getResultArray();
            } catch (\Throwable $e) {
                CLI::write("  ERROR consultando tabla: " . $e->getMessage(), 'red');
                $resumen['contratos'] = ['ok' => 0, 'error' => 1, 'total' => 0];
                $totalError++;
                $rows = [];
            }

            $total = count($rows);

            if ($total === 0) {
                CLI::write("  Sin contratos para regenerar.", 'light_gray');
            } else {
                CLI::write("  {$total} contratos encontrados", 'white');

                if (!$dryRun) {
                    $contractLib = new \App\Libraries\ContractLibrary();
                    $contractModel = new \App\Models\ContractModel();
                    $ok = 0;
                    $errors = 0;

                    foreach ($rows as $i => $row) {
                        $id = $row['id_contrato'];
                        $pos = $i + 1;
                        try {
                            $contract = $contractLib->getContractWithClient($id);
                            if (!$contract) {
                                throw new \RuntimeException('Contrato no encontrado');
                            }

                            $pdfGenerator = new \App\Libraries\ContractPDFGenerator();
                            $pdfGenerator->generateContract($contract);

                            $uploadDir = UPLOADS_PATH . 'contratos' . DIRECTORY_SEPARATOR;
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0775, true);
                            }

                            $fileName = 'contrato_' . $contract['numero_contrato'] . '_' . date('Ymd_His') . '.pdf';
                            $filePath = $uploadDir . $fileName;
                            $pdfGenerator->save($filePath);

                            $contractModel->update($id, [
                                'ruta_pdf_contrato' => UPLOADS_URL_PREFIX . '/contratos/' . $fileName,
                            ]);

                            $ok++;
                            CLI::write("  [contratos {$pos}/{$total}] ID {$id} OK", 'green');
                        } catch (\Throwable $e) {
                            $errors++;
                            CLI::write("  [contratos {$pos}/{$total}] ID {$id} ERROR: " . $e->getMessage(), 'red');
                        }
                    }

                    $totalOk += $ok;
                    $totalError += $errors;
                    $resumen['contratos'] = ['ok' => $ok, 'error' => $errors, 'total' => $total];
                }
            }
        }

        // ─── 3. INFORMES DE AVANCE ───
        if (!$moduloFiltro || $moduloFiltro === 'informes') {
            CLI::write("\n══ informes-avance ══", 'cyan');

            try {
                $db->query('SELECT 1');
            } catch (\Throwable $e) {
                try { $db->close(); $db->initialize(); } catch (\Throwable $e2) {
                    CLI::error("  No se pudo reconectar a la BD");
                }
            }

            try {
                $builder = $db->table('tbl_informe_avances')
                    ->select('id')
                    ->where('estado', 'completo');

                if ($idFiltro) {
                    $builder->where('id', $idFiltro);
                }

                $rows = $builder->get()->getResultArray();
            } catch (\Throwable $e) {
                CLI::write("  ERROR consultando tabla: " . $e->getMessage(), 'red');
                $resumen['informes-avance'] = ['ok' => 0, 'error' => 1, 'total' => 0];
                $totalError++;
                $rows = [];
            }

            $total = count($rows);

            if ($total === 0) {
                CLI::write("  Sin informes para regenerar.", 'light_gray');
            } else {
                CLI::write("  {$total} informes encontrados", 'white');

                if (!$dryRun) {
                    $ctrl = new \App\Controllers\InformeAvancesController();
                    $ctrl->initController(
                        \Config\Services::request(),
                        \Config\Services::response(),
                        \Config\Services::logger()
                    );

                    $ok = 0;
                    $errors = 0;

                    foreach ($rows as $i => $row) {
                        $id = $row['id'];
                        $pos = $i + 1;
                        try {
                            $ctrl->regenerarPdf($id);
                            $ok++;
                            CLI::write("  [informes {$pos}/{$total}] ID {$id} OK", 'green');
                        } catch (\Throwable $e) {
                            $errors++;
                            CLI::write("  [informes {$pos}/{$total}] ID {$id} ERROR: " . $e->getMessage(), 'red');
                        }
                    }

                    $totalOk += $ok;
                    $totalError += $errors;
                    $resumen['informes-avance'] = ['ok' => $ok, 'error' => $errors, 'total' => $total];
                }
            }
        }

        // ─── RESUMEN ───
        CLI::write("\n════════════════════════════════════", 'yellow');
        CLI::write("RESUMEN DE REGENERACIÓN", 'yellow');
        CLI::write("════════════════════════════════════", 'yellow');

        foreach ($resumen as $modulo => $datos) {
            $color = $datos['error'] > 0 ? 'red' : 'green';
            CLI::write(sprintf(
                "  %-30s %d/%d OK, %d errores",
                $modulo,
                $datos['ok'],
                $datos['total'],
                $datos['error']
            ), $color);
        }

        CLI::write("", 'white');
        CLI::write("Total regenerados: {$totalOk}", 'green');
        if ($totalError > 0) {
            CLI::write("Total errores: {$totalError}", 'red');
        }
    }
}
