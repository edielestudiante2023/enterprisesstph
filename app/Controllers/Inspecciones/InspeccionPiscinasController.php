<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionPiscinasModel;
use App\Models\PiscinaDetalleModel;
use App\Models\PiscinaEnsayoLaboratorioModel;
use App\Models\PiscinaEvidenciaMaestroModel;
use App\Models\PiscinaDetalleEvidenciaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\BotiquinCatalogo;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use Dompdf\Dompdf;

class InspeccionPiscinasController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionPiscinasModel $inspeccionModel;
    protected PiscinaDetalleModel $detalleModel;
    protected PiscinaEnsayoLaboratorioModel $ensayoModel;
    protected PiscinaEvidenciaMaestroModel $evidenciaModel;
    protected PiscinaDetalleEvidenciaModel $evidenciaDetalleModel;

    public const MARCO_NORMATIVO = 'Ley 1209 de 2008, Decreto Reglamentario 554 de 2015 y Resolución 234 de 2026 del Ministerio de Salud y Protección Social — Seguridad, adecuación sanitaria y parámetros de calidad del agua en estanques de piscinas y estructuras similares. Esta inspección SST verifica documentos del operador (planilla diaria, ensayo microbiológico trimestral) y condiciones de infraestructura, emergencia y avisos; NO reemplaza el concepto sanitario de la Secretaría de Salud competente, la certificación del operador por entidad acreditada ni los ensayos de laboratorio (Art. 6) que son responsabilidad del laboratorio privado acreditado.';

    public function __construct()
    {
        $this->inspeccionModel       = new InspeccionPiscinasModel();
        $this->detalleModel          = new PiscinaDetalleModel();
        $this->ensayoModel           = new PiscinaEnsayoLaboratorioModel();
        $this->evidenciaModel        = new PiscinaEvidenciaMaestroModel();
        $this->evidenciaDetalleModel = new PiscinaDetalleEvidenciaModel();
    }

    // ==================================================================
    // CRUD principal
    // ==================================================================

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinas.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_piscinas.fecha_inspeccion', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_detalles'] = $this->detalleModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/list', [
                'title'        => 'Inspección de Piscinas',
                'inspecciones' => $inspecciones,
            ]),
            'title' => 'Piscinas',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/form', [
                'title'                  => 'Nueva Inspección de Piscinas',
                'inspeccion'             => null,
                'idCliente'              => $idCliente,
                'piscinas'               => [],
                'ensayos'                => [],
                'evidenciasDetMap'       => [],
                'evidenciasMap'          => [],
                'camposEvidenciaMaestro' => self::CAMPOS_EVIDENCIA_MAESTRO,
            ]),
            'title' => 'Nueva Piscinas',
        ]);
    }

    /** Códigos de campo del bloque maestro con evidencia fotográfica multi-foto */
    public const CAMPOS_EVIDENCIA_MAESTRO = [
        'planilla_diaria'       => 'Planilla diaria de operaciones (Art. 16)',
        'ensayo_microbiologico' => 'Último ensayo microbiológico (Art. 6)',
        'empresa_mantenimiento' => 'Empresa de mantenimiento (contrato, idoneidad)',
        'concepto_sanitario'    => 'Concepto sanitario Secretaría de Salud',
        'dea'                   => 'DEA (Desfibrilador Externo Automático)',
        'operador_cert'         => 'Operador de piscinas certificado',
        'doc_art15'             => 'Documentación Art. 15 (8 procedimientos)',
        'plan_saneamiento'      => 'Plan Saneamiento Básico Art. 17 (5 programas)',
        'manejo_quimicos'       => 'Manejo seguro de químicos (Art. 13)',
        'area_residuos'         => 'Área de residuos (Art. 14)',
        'contenedores_color'    => 'Contenedores codificados por color',
        'tablero_publico'       => 'Tablero público con resultados mensuales',
    ];

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/piscinas/edit/');
        if ($existing) return $existing;

        $isAutosave = $this->isAutosaveRequest();

        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum()) return $err;
        } else {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $userId = session()->get('user_id');
        $idInspeccion = 0;
        $detailIds = [];

        $txResult = $this->runTransactional(function () use ($userId, &$idInspeccion, &$detailIds) {
            $data = $this->collectMasterFields($userId, true);
            $this->inspeccionModel->insert($data);
            $idInspeccion = $this->inspeccionModel->getInsertID();
            $this->saveEvidenciasMaestro($idInspeccion);
            $this->saveEnsayosMaestro($idInspeccion);
            $detailIds = $this->savePiscinas($idInspeccion);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/piscinas/edit/' . $idInspeccion)
            ->with('msg', 'Inspección guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'Inspección no encontrada');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/piscinas/view/' . $id)) return $r;

        $piscinas = $this->detalleModel->getByInspeccion($id);
        $evidenciasDetMap = $this->hijasPorPiscina($piscinas);
        $evidenciasMap = $this->evidenciaModel->mapaPorCampo((int)$id);
        $ensayos = $this->ensayoModel->getByInspeccion((int)$id);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/form', [
                'title'                  => 'Editar Inspección de Piscinas',
                'inspeccion'             => $inspeccion,
                'idCliente'              => $inspeccion['id_cliente'],
                'piscinas'               => $piscinas,
                'ensayos'                => $ensayos,
                'evidenciasDetMap'       => $evidenciasDetMap,
                'evidenciasMap'          => $evidenciasMap,
                'camposEvidenciaMaestro' => self::CAMPOS_EVIDENCIA_MAESTRO,
            ]),
            'title' => 'Editar Piscinas',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/piscinas')->with('error', 'No se puede editar');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/piscinas/view/' . $id)) return $r;

        $isAutosave = $this->isAutosaveRequest();
        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum()) return $err;
        }

        $userId = $inspeccion['id_consultor'];
        $detailIds = [];

        $txResult = $this->runTransactional(function () use ($id, $userId, &$detailIds) {
            $this->inspeccionModel->update($id, $this->collectMasterFields($userId, false));
            $this->saveEvidenciasMaestro((int)$id);
            $this->saveEnsayosMaestro((int)$id);
            $detailIds = $this->savePiscinas($id);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);

        if ($isAutosave) return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);

        return redirect()->to('/inspecciones/piscinas/edit/' . $id)->with('msg', 'Inspección actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $piscinas = $this->detalleModel->getByInspeccion($id);
        $evidenciasDetMap = $this->hijasPorPiscina($piscinas);
        $evidenciasMap = $this->evidenciaModel->mapaPorCampo((int)$id);
        $ensayos = $this->ensayoModel->getByInspeccion((int)$id);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/view', [
                'title'                  => 'Ver Inspección de Piscinas',
                'inspeccion'             => $inspeccion,
                'cliente'                => $clientModel->find($inspeccion['id_cliente']),
                'consultor'              => $consultantModel->find($inspeccion['id_consultor']),
                'piscinas'               => $piscinas,
                'ensayos'                => $ensayos,
                'evidenciasDetMap'       => $evidenciasDetMap,
                'evidenciasMap'          => $evidenciasMap,
                'camposEvidenciaMaestro' => self::CAMPOS_EVIDENCIA_MAESTRO,
            ]),
            'title' => 'Ver Piscinas',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/piscinas/view/' . $id)) return $r;

        $total = $this->detalleModel->where('id_inspeccion', $id)->countAllResults();
        $this->inspeccionModel->update($id, [
            'marco_normativo' => self::MARCO_NORMATIVO,
            'total_piscinas'  => $total,
        ]);

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->inspeccionModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN PISCINAS',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionPiscinas'
        );

        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/piscinas/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) return redirect()->back()->with('error', 'PDF no encontrado');

        $this->servirPdf($fullPath, 'piscinas_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/piscinas/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');

        $piscinas = $this->detalleModel->getByInspeccion($id);
        foreach ($piscinas as $p) {
            if (!empty($p['foto']) && file_exists(FCPATH . $p['foto'])) unlink(FCPATH . $p['foto']);
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        // Hijas se borran en cascada por FK
        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/piscinas')->with('msg', 'Inspección eliminada');
    }

    /**
     * AJAX POST: extrae los campos de un ensayo de laboratorio desde el PDF
     * subido, usando IA. Devuelve JSON con los campos a rellenar en el form.
     *
     * Input:
     *   - tipo (MICROBIOLOGICO|FISICOQUIMICO)
     *   - pdf  (archivo)
     */
    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/piscinas/view/{$id}")
                ->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN PISCINAS',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionPiscinas'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/piscinas/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/piscinas/view/{$id}")->with('error', $result['error']);
    }

    // ==================================================================
    // PRIVADOS
    // ==================================================================

    private function collectMasterFields($userId, bool $isInsert): array
    {
        $data = [
            'id_cliente'                         => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'                   => $this->request->getPost('fecha_inspeccion'),
            'empresa_mantenimiento'              => $this->request->getPost('empresa_mantenimiento'),
            'nit_empresa_mantenimiento'          => $this->request->getPost('nit_empresa_mantenimiento'),
            'contacto_empresa_mantenimiento'     => $this->request->getPost('contacto_empresa_mantenimiento'),
            'superficie_total_establecimiento_m2'=> $this->request->getPost('superficie_total_establecimiento_m2') ?: null,
            'concepto_sanitario'                 => $this->request->getPost('concepto_sanitario') ?: 'no_emitido',
            'concepto_sanitario_fecha'           => $this->request->getPost('concepto_sanitario_fecha') ?: null,
            'concepto_sanitario_observaciones'   => $this->request->getPost('concepto_sanitario_observaciones'),
            'dea_presente'                       => $this->request->getPost('dea_presente') ?: 'NA',
            'dea_ubicacion_senalizada'           => $this->request->getPost('dea_ubicacion_senalizada') ?: 'NA',
            'dea_personal_capacitado_cantidad'   => (int)$this->request->getPost('dea_personal_capacitado_cantidad'),
            'operador_certificado_nombre'        => $this->request->getPost('operador_certificado_nombre'),
            'operador_certificado_entidad'       => $this->request->getPost('operador_certificado_entidad'),
            'operador_certificado_vigencia'      => $this->request->getPost('operador_certificado_vigencia') ?: null,
            'documentacion_art15_completa'       => $this->request->getPost('documentacion_art15_completa') ?: 'NA',
            'documentacion_art15_observaciones'  => $this->request->getPost('documentacion_art15_observaciones'),
            'plan_saneamiento_completo'          => $this->request->getPost('plan_saneamiento_completo') ?: 'NA',
            'plan_saneamiento_observaciones'     => $this->request->getPost('plan_saneamiento_observaciones'),
            'manejo_quimicos_conforme'           => $this->request->getPost('manejo_quimicos_conforme') ?: 'NA',
            'area_residuos_conforme'             => $this->request->getPost('area_residuos_conforme') ?: 'NA',
            'contenedores_codificados_color'     => $this->request->getPost('contenedores_codificados_color') ?: 'NA',
            'tablero_publico_resultados'         => $this->request->getPost('tablero_publico_resultados') ?: 'NA',
            'total_piscinas'                     => (int)$this->request->getPost('total_piscinas'),
            'recomendaciones_generales'          => $this->request->getPost('recomendaciones_generales'),
        ];

        if ($isInsert) {
            $data['id_consultor'] = $userId;
            $data['estado']       = 'borrador';
        }

        return $data;
    }

    /**
     * Validaciones cruzadas entre campos del maestro y detalles.
     * Devuelve un array de strings con advertencias humanas.
     * Si no hay advertencias, el array está vacío.
     *
     * Reglas:
     *   - Si hay piscina climatizada, ventilación_adecuada debe ser SI (no NA/NO)
     *   - Si m² establecimiento > 0, botiquin_tipo de cada piscina debe coincidir
     *     con el umbral (<500 A, 500-2000 B, >2000 C)
     *   - Si cualquier microbiológico > 0 (coliformes, E.coli, pseudomonas),
     *     concepto_sanitario no puede ser 'favorable'
     *   - DEA presente=SI pero personal_capacitado=0 => inconsistente
     *   - Operador certificado nombre lleno pero vigencia vencida => advertencia
     */
    /**
     * Procesa las evidencias multi-foto del bloque maestro.
     * Patrón: input name="item_foto_<campo>[]" (N archivos por campo).
     *
     * Comportamiento:
     *  - Agrega las fotos nuevas al final (append), preservando las existentes.
     *  - Si se envía `evidencia_borrar_ids[]` con IDs, esas filas se eliminan
     *    (tanto el archivo como la fila en BD).
     */
    private function saveEvidenciasMaestro(int $idInspeccion): void
    {
        $dir = FCPATH . 'uploads/inspecciones/piscinas/fotos/maestro/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // 1. Eliminar evidencias marcadas
        $borrarIds = $this->request->getPost('evidencia_borrar_ids') ?? [];
        if (is_array($borrarIds)) {
            foreach ($borrarIds as $idBorrar) {
                $idBorrar = (int)$idBorrar;
                if ($idBorrar <= 0) continue;
                $row = $this->evidenciaModel->find($idBorrar);
                if (!$row || (int)$row['id_inspeccion'] !== $idInspeccion) continue;
                if (!empty($row['foto_path']) && file_exists(FCPATH . $row['foto_path'])) {
                    @unlink(FCPATH . $row['foto_path']);
                }
                $this->evidenciaModel->delete($idBorrar);
            }
        }

        // 2. Agregar fotos nuevas (append)
        $files = $this->request->getFiles();
        foreach (self::CAMPOS_EVIDENCIA_MAESTRO as $campo => $_) {
            $inputName = 'item_foto_' . $campo;
            if (!isset($files[$inputName])) continue;
            $fileList = $files[$inputName];
            if (!is_array($fileList)) $fileList = [$fileList];

            $maxOrden = (int)($this->evidenciaModel
                ->selectMax('orden')
                ->where('id_inspeccion', $idInspeccion)
                ->where('campo', $campo)
                ->first()['orden'] ?? 0);

            $descripciones = $this->request->getPost('item_foto_' . $campo . '_desc') ?? [];
            if (!is_array($descripciones)) $descripciones = [];

            foreach ($fileList as $i => $file) {
                if (!$file || !$file->isValid() || $file->hasMoved()) continue;
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $maxOrden++;
                $this->evidenciaModel->insert([
                    'id_inspeccion' => $idInspeccion,
                    'campo'         => $campo,
                    'orden'         => $maxOrden,
                    'foto_path'     => 'uploads/inspecciones/piscinas/fotos/maestro/' . $fileName,
                    'descripcion'   => $descripciones[$i] ?? null,
                ]);
            }
        }
    }

    /**
     * Persiste N piscinas + sus hijas (parámetros, ensayos, botiquín, evidencias).
     *
     * Patrón delete-and-reinsert: borra todas las piscinas (cascada borra hijas)
     * y las reinserta desde el POST. Esto funciona bien para parametros/ensayos/
     * botiquin porque sus valores ESTÁN en el POST.
     *
     * Para las evidencias multi-foto (que solo tienen foto_path en DB, no en el
     * POST) se hace un SNAPSHOT antes del delete y se reinsertan post-delete,
     * mapeadas por `orden` de piscina. Así las fotos existentes se preservan a
     * través del cycle de autosave.
     */
    private function savePiscinas(int $idInspeccion): array
    {
        $identificadores = $this->request->getPost('item_identificador') ?? [];
        $itemIds         = $this->request->getPost('item_id') ?? [];

        $existentes = [];
        $existentesPorOrden = [];
        foreach ($this->detalleModel->getByInspeccion($idInspeccion) as $row) {
            $existentes[$row['id']] = $row;
            $existentesPorOrden[(int)$row['orden']] = $row;
        }

        // SNAPSHOT evidencias multi-foto antes del cascade delete.
        // Clave: orden de piscina => [filas de evidencia]. Se reinsertan luego.
        $evidSnapshotPorOrden = [];
        foreach ($existentes as $pisRow) {
            $ordenPis = (int)$pisRow['orden'];
            $evidSnapshotPorOrden[$ordenPis] = $this->evidenciaDetalleModel->getByPiscina((int)$pisRow['id']);
        }

        // Procesar IDs marcados para borrado ANTES del cascade (para limpiar archivos físicos
        // y excluirlos del snapshot que se reinserta)
        $borrarIdsDet = $this->request->getPost('detalle_evidencia_borrar_ids') ?? [];
        $borrarIdsDetSet = [];
        if (is_array($borrarIdsDet)) {
            foreach ($borrarIdsDet as $idBorrar) {
                $idBorrar = (int)$idBorrar;
                if ($idBorrar > 0) $borrarIdsDetSet[$idBorrar] = true;
            }
        }
        if (!empty($borrarIdsDetSet)) {
            foreach ($evidSnapshotPorOrden as $ordenKey => $rows) {
                $filtered = [];
                foreach ($rows as $ev) {
                    $evId = (int)$ev['id'];
                    if (isset($borrarIdsDetSet[$evId])) {
                        if (!empty($ev['foto_path']) && file_exists(FCPATH . $ev['foto_path'])) {
                            @unlink(FCPATH . $ev['foto_path']);
                        }
                        continue; // no preservar
                    }
                    $filtered[] = $ev;
                }
                $evidSnapshotPorOrden[$ordenKey] = $filtered;
            }
        }

        // Borrar piscinas previas (sus hijas caen por cascada, incluyendo evidencias)
        $this->detalleModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/piscinas/fotos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $files  = $this->request->getFiles();
        $newIds = [];

        // Campos SI/NO/NA y numéricos a recolectar por fila
        $simpleEnumFields = [
            'cerramiento_perimetral', 'puerta_control_acceso',
            'alarma_inmersion_80db', 'boton_parada_emergencia',
            'drenaje_antiatrapamiento', 'minimo_dos_drenajes', 'sistema_liberacion_vacio',
            'senalizacion_profundidad', 'baldosas_cambio_profundidad',
            'escaleras_acceso_antideslizantes', 'baranda_escaleras',
            'iluminacion_adecuada', 'ventilacion_adecuada',
            'aviso_menores_12', 'aviso_reglamento', 'aviso_horario',
            'aviso_ducharse_antes', 'aviso_prohibido_zapatos',
            'aviso_telefonos_emergencia', 'aviso_aforo_visible',
            'camilla_rescate', 'flotadores_circulares_min_2', 'baston_con_gancho', 'citofono_24h',
            'duchas_previas_obligatorias', 'baranda_apoyo_duchas', 'lavapies_funcional',
            'dosificacion_independiente', 'sistema_seguridad_flujo', 'no_dosificacion_manual_con_publico',
            'equipo_bombeo_operativo', 'filtros_operativos',
            'libro_registro_existe',
        ];

        foreach ($identificadores as $i => $identificador) {
            $existenteId = $itemIds[$i] ?? null;
            $existente   = $existenteId ? ($existentes[$existenteId] ?? null) : null;
            if (!$existente) $existente = $existentesPorOrden[$i + 1] ?? null;

            $fotoPath = $existente['foto'] ?? null;
            if (isset($files['item_foto'][$i]) && $files['item_foto'][$i]->isValid() && !$files['item_foto'][$i]->hasMoved()) {
                $file = $files['item_foto'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $fotoPath = 'uploads/inspecciones/piscinas/fotos/' . $fileName;
            }

            $row = [
                'id_inspeccion'         => $idInspeccion,
                'orden'                 => $i + 1,
                'identificador'         => $identificador ?: ('Piscina ' . ($i + 1)),
                'tipo'                  => $this->pickIdx('item_tipo', $i, 'ADULTOS'),
                'uso'                   => $this->pickIdx('item_uso', $i, 'RESTRINGIDO'),
                'climatizada'           => $this->pickIdx('item_climatizada', $i, 'NO'),
                'superficie_piscina_m2' => $this->pickIdx('item_superficie_piscina_m2', $i) ?: null,
                'volumen_agua_m3'       => $this->pickIdx('item_volumen_agua_m3', $i) ?: null,
                'perfil_profundidad'    => $this->pickIdx('item_perfil_profundidad', $i, 'UNIFORME'),
                'profundidad_max_m'     => $this->pickIdx('item_profundidad_max_m', $i) ?: null,
                'profundidad_min_m'     => $this->pickIdx('item_profundidad_min_m', $i) ?: null,
                'aforo_piscina_max'     => $this->pickIdx('item_aforo_piscina_max', $i) ?: null,
                'aforo_deck_max'        => $this->pickIdx('item_aforo_deck_max', $i) ?: null,
                'botiquin_tipo'         => $this->pickIdx('item_botiquin_tipo', $i, 'NINGUNO'),
                'botiquin_observaciones_faltantes' => $this->pickIdx('item_botiquin_observaciones_faltantes', $i),
                'cubiculos_duchas_mujeres' => (int)$this->pickIdx('item_cubiculos_duchas_mujeres', $i, 0),
                'cubiculos_duchas_hombres' => (int)$this->pickIdx('item_cubiculos_duchas_hombres', $i, 0),
                'libro_ultima_semana_fecha' => $this->pickIdx('item_libro_ultima_semana_fecha', $i) ?: null,
                'libro_observaciones'   => $this->pickIdx('item_libro_observaciones', $i),
                'estado_general'        => $this->pickIdx('item_estado_general', $i) ?: null,
                'foto'                  => $fotoPath,
                'observaciones'         => $this->pickIdx('item_observaciones', $i),
            ];

            foreach ($simpleEnumFields as $f) {
                $row[$f] = $this->pickIdx('item_' . $f, $i, 'NA');
            }

            $this->detalleModel->insert($row);
            $idDetalle = $this->detalleModel->getInsertID();
            $newIds[] = $idDetalle;

            // Subir foto del botiquín (1 sola por piscina, reemplaza la anterior si se vuelve a subir)
            $fotoBotiquinPath = $this->procesarFotoBotiquin($i, $existente['foto_botiquin'] ?? null);
            if ($fotoBotiquinPath !== null) {
                $this->detalleModel->update($idDetalle, ['foto_botiquin' => $fotoBotiquinPath]);
            }

            // Reinsertar evidencias previas (del snapshot, mapeadas por orden)
            $ordenPisc = $i + 1;
            $evidPrevias = $evidSnapshotPorOrden[$ordenPisc] ?? [];
            foreach ($evidPrevias as $ev) {
                $this->evidenciaDetalleModel->insert([
                    'id_piscina_detalle' => $idDetalle,
                    'categoria'          => $ev['categoria'] ?? '',
                    'orden'              => (int)($ev['orden'] ?? 0),
                    'foto_path'          => $ev['foto_path'],
                    'descripcion'        => $ev['descripcion'] ?? null,
                ]);
            }

            // Append de NUEVAS fotos multi-foto desde el POST (pueden ser varias)
            $this->savePiscinaEvidencias($idDetalle, $i);
        }

        return $newIds;
    }

    /**
     * Procesa fotos adicionales por piscina detalle (N fotos, append).
     *
     * Form envía tres arrays paralelos (uno por fila de "+ Agregar foto"):
     *   item_evidencia_<i>[]             (archivo)
     *   item_evidencia_categoria_<i>[]   (texto libre, con datalist de sugerencias)
     *   item_evidencia_descripcion_<i>[] (texto opcional)
     *
     * donde <i> es el índice (0-based) de la piscina en el form.
     */
    private function savePiscinaEvidencias(int $idDetalle, int $i): void
    {
        $dir = FCPATH . 'uploads/inspecciones/piscinas/fotos/detalle/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $files = $this->request->getFiles();
        $inputName = 'item_evidencia_' . $i;
        if (!isset($files[$inputName])) return;

        $fileList = $files[$inputName];
        if (!is_array($fileList)) $fileList = [$fileList];

        $categorias    = $this->request->getPost('item_evidencia_categoria_' . $i);
        $descripciones = $this->request->getPost('item_evidencia_descripcion_' . $i);
        if (!is_array($categorias))    $categorias    = [];
        if (!is_array($descripciones)) $descripciones = [];

        $maxOrden = (int)($this->evidenciaDetalleModel
            ->selectMax('orden')
            ->where('id_piscina_detalle', $idDetalle)
            ->first()['orden'] ?? 0);

        foreach ($fileList as $idx => $file) {
            if (!$file || !$file->isValid() || $file->hasMoved()) continue;
            $fileName = $file->getRandomName();
            $file->move($dir, $fileName);
            $this->comprimirImagen($dir . $fileName);
            $maxOrden++;
            $categoria = trim((string)($categorias[$idx] ?? ''));
            if (strlen($categoria) > 60) $categoria = substr($categoria, 0, 60);
            $descripcion = $descripciones[$idx] ?? null;
            if ($descripcion !== null && strlen($descripcion) > 255) {
                $descripcion = substr($descripcion, 0, 255);
            }

            $this->evidenciaDetalleModel->insert([
                'id_piscina_detalle' => $idDetalle,
                'categoria'          => $categoria,
                'orden'              => $maxOrden,
                'foto_path'          => 'uploads/inspecciones/piscinas/fotos/detalle/' . $fileName,
                'descripcion'        => $descripcion,
            ]);
        }
    }

    private function pickIdx(string $fieldName, int $i, $default = null)
    {
        $arr = $this->request->getPost($fieldName);
        if (!is_array($arr)) return $default;
        $v = $arr[$i] ?? null;
        return ($v === null || $v === '') ? $default : $v;
    }

    /**
     * Procesa la foto del botiquín de una piscina.
     * Input: item_foto_botiquin[] (uno por piscina).
     * Retorna la nueva ruta, la previa si no se subió nueva, o null si hay que dejar el default.
     */
    private function procesarFotoBotiquin(int $i, ?string $previaPath): ?string
    {
        $files = $this->request->getFiles();
        if (!isset($files['item_foto_botiquin'][$i])) return $previaPath;
        $file = $files['item_foto_botiquin'][$i];
        if (!$file || !$file->isValid() || $file->hasMoved()) return $previaPath;

        $dir = FCPATH . 'uploads/inspecciones/piscinas/fotos/botiquines/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fileName = $file->getRandomName();
        $file->move($dir, $fileName);
        $this->comprimirImagen($dir . $fileName);

        // Eliminar foto previa si existe
        if (!empty($previaPath) && file_exists(FCPATH . $previaPath)) {
            @unlink(FCPATH . $previaPath);
        }

        return 'uploads/inspecciones/piscinas/fotos/botiquines/' . $fileName;
    }

    /**
     * Guarda los ensayos de laboratorio a NIVEL MAESTRO (no por piscina).
     * Patrón: el form trae arrays paralelos (micro + fisicoquímico en índices 0 y 1).
     * Alcance SST: consultor solo verifica que exista ensayo vigente; no interpreta UFC.
     */
    private function saveEnsayosMaestro(int $idInspeccion): void
    {
        // Wipe and reinsert (simpler for delete+insert pattern)
        $this->ensayoModel->deleteByInspeccion($idInspeccion);

        foreach (['MICROBIOLOGICO', 'FISICOQUIMICO'] as $tipo) {
            $prefix = strtolower($tipo);
            $fecha  = $this->request->getPost('ensayo_' . $prefix . '_fecha');
            $lab    = $this->request->getPost('ensayo_' . $prefix . '_lab');
            $acred  = $this->request->getPost('ensayo_' . $prefix . '_acreditado');
            $cumple = $this->request->getPost('ensayo_' . $prefix . '_cumplimiento');
            $obs    = $this->request->getPost('ensayo_' . $prefix . '_obs');
            $conf   = $this->request->getPost('ensayo_' . $prefix . '_conforme');

            if (!$fecha && !$lab && !$obs) continue;

            $this->ensayoModel->insert([
                'id_inspeccion'          => $idInspeccion,
                'tipo'                   => $tipo,
                'fecha_toma'             => $fecha ?: null,
                'laboratorio'            => $lab,
                'laboratorio_acreditado' => $acred ?: 'NA',
                'reporta_cumplimiento'   => $cumple ?: 'NA',
                'conforme_global'        => $conf ?: 'NA',
                'observaciones'          => $obs,
            ]);
        }
    }

    /**
     * Devuelve el mapa de evidencias multi-foto por piscina_detalle id.
     */
    private function hijasPorPiscina(array $piscinas): array
    {
        $evidenciasDetMap = [];
        foreach ($piscinas as $p) {
            $id = (int)$p['id'];
            $evidenciasDetMap[$id] = $this->evidenciaDetalleModel->getByPiscina($id);
        }
        return $evidenciasDetMap;
    }

    // ==================================================================
    // PDF
    // ==================================================================

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);
        $piscinas = $this->detalleModel->getByInspeccion($id);
        $evidenciasDetMap = $this->hijasPorPiscina($piscinas);
        $ensayos = $this->ensayoModel->getByInspeccion((int)$id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
        }

        foreach ($piscinas as &$p) {
            $p['foto_base64'] = '';
            $p['foto_botiquin_base64'] = '';
            if (!empty($p['foto'])) {
                $fotoPath = FCPATH . $p['foto'];
                if (file_exists($fotoPath)) $p['foto_base64'] = $this->fotoABase64ParaPdf($fotoPath);
            }
            if (!empty($p['foto_botiquin'])) {
                $fpBot = FCPATH . $p['foto_botiquin'];
                if (file_exists($fpBot)) $p['foto_botiquin_base64'] = $this->fotoABase64ParaPdf($fpBot);
            }
        }
        unset($p);

        // Cargar evidencias multi-foto del bloque maestro y convertir a base64
        $evidenciasMap = $this->evidenciaModel->mapaPorCampo((int)$id);
        $evidenciasMaestroB64 = [];
        foreach ($evidenciasMap as $campo => $rows) {
            $evidenciasMaestroB64[$campo] = [];
            foreach ($rows as $r) {
                $b64 = '';
                if (!empty($r['foto_path'])) {
                    $fp = FCPATH . $r['foto_path'];
                    if (file_exists($fp)) $b64 = $this->fotoABase64ParaPdf($fp);
                }
                $evidenciasMaestroB64[$campo][] = [
                    'foto_b64'    => $b64,
                    'descripcion' => $r['descripcion'] ?? '',
                ];
            }
        }

        // Convertir evidencias multi-foto de piscina detalle a base64
        $evidenciasDetB64 = [];
        foreach ($evidenciasDetMap as $idDet => $rows) {
            $evidenciasDetB64[$idDet] = [];
            foreach ($rows as $r) {
                $b64 = '';
                if (!empty($r['foto_path'])) {
                    $fp = FCPATH . $r['foto_path'];
                    if (file_exists($fp)) $b64 = $this->fotoABase64ParaPdf($fp);
                }
                $evidenciasDetB64[$idDet][] = [
                    'foto_b64'    => $b64,
                    'categoria'   => !empty($r['categoria']) ? $r['categoria'] : '',
                    'descripcion' => $r['descripcion'] ?? '',
                ];
            }
        }

        $data = [
            'inspeccion'              => $inspeccion,
            'cliente'                 => $cliente,
            'consultor'               => $consultor,
            'piscinas'                => $piscinas,
            'ensayos'                 => $ensayos,
            'evidenciasDetMap'        => $evidenciasDetMap,
            'logoBase64'              => $logoBase64,
            'evidenciasMaestroB64'    => $evidenciasMaestroB64,
            'camposEvidenciaMaestro'  => self::CAMPOS_EVIDENCIA_MAESTRO,
            'evidenciasDetB64'        => $evidenciasDetB64,
            'marcoNormativo'          => self::MARCO_NORMATIVO,
        ];

        $html = view('inspecciones/piscinas/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/piscinas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'piscinas_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        return $pdfPath;
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 46)
            ->like('observaciones', 'insp_pis_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'piscinas_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION PISCINAS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 46,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_pis_id:' . $inspeccion['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
