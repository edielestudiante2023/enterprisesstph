<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaVisitaModel;
use App\Models\ActaVisitaIntegranteModel;
use App\Models\ActaVisitaTemaModel;
use App\Models\ActaVisitaFotoModel;
use App\Models\PendientesModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\InspeccionEmailNotifier;
use App\Models\VencimientosMantenimientoModel;
use App\Models\CicloVisitaModel;
use App\Models\ActaVisitaPtaModel;
use App\Models\PtaClienteNuevaModel;
use App\Services\PtaAuditService;
use App\Services\PtaTransicionesService;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class ActaVisitaController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    protected ActaVisitaModel $actaModel;
    protected ActaVisitaIntegranteModel $integranteModel;
    protected ActaVisitaTemaModel $temaModel;

    public function __construct()
    {
        $this->actaModel = new ActaVisitaModel();
        $this->integranteModel = new ActaVisitaIntegranteModel();
        $this->temaModel = new ActaVisitaTemaModel();
    }

    /**
     * Listado de actas del consultor
     */
    public function list()
    {
        $actas = $this->actaModel->select('tbl_acta_visita.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_visita.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_visita.id_consultor', 'left')
            ->orderBy('tbl_acta_visita.fecha_visita', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Actas de Visita',
            'actas' => $actas,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/list', $data),
            'title'   => 'Actas de Visita',
        ]);
    }

    /**
     * Formulario de creación. Opcionalmente recibe id_cliente pre-seleccionado.
     */
    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Acta de Visita',
            'acta'       => null,
            'idCliente'  => $idCliente,
            'integrantes' => [],
            'temas'      => [],
            'fotos'      => [],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/form', $data),
            'title'   => 'Nueva Acta de Visita',
        ]);
    }

    /**
     * Guardar nueva acta (siempre como borrador)
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        // Validar campos mínimos
        if (!$isAutosave) {
            $rules = [
                'id_cliente'   => 'required|integer',
                'fecha_visita' => 'required|valid_date',
                'hora_visita'  => 'required',
                'motivo'       => 'required|min_length[3]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        // Insertar acta
        $actaData = [
            'id_cliente'            => $this->request->getPost('id_cliente'),
            'id_consultor'          => $userId,
            'fecha_visita'          => $this->request->getPost('fecha_visita'),
            'hora_visita'           => $this->request->getPost('hora_visita'),
            'ubicacion_gps'         => $this->request->getPost('ubicacion_gps'),
            'motivo'                => $this->request->getPost('motivo'),
            'modalidad'             => $this->request->getPost('modalidad') ?: 'Presencial',
            'cartera'               => $this->request->getPost('cartera'),
            'observaciones'         => $this->request->getPost('observaciones'),
            'proxima_reunion_fecha' => $this->request->getPost('proxima_reunion_fecha') ?: null,
            'proxima_reunion_hora'  => $this->request->getPost('proxima_reunion_hora') ?: null,
            'estado'                => 'borrador',
        ];

        $this->actaModel->insert($actaData);
        $idActa = $this->actaModel->getInsertID();

        // Guardar integrantes
        $this->saveIntegrantes($idActa);

        // Guardar temas
        $this->saveTemas($idActa);

        // Guardar compromisos como pendientes
        $this->saveCompromisos($idActa);

        // Guardar fotos
        $this->saveFotos($idActa);

        // Guardar actividades PTA (solo en submit real, no autosave)
        if (!$isAutosave) {
            $this->savePtaActividades($idActa);
        }

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idActa);
        }

        return redirect()->to('/inspecciones/acta-visita/edit/' . $idActa)
            ->with('msg', 'Acta guardada como borrador');
    }

    /**
     * Formulario de edición (solo borradores y pendiente_firma)
     */
    public function edit($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }
        $data = [
            'title'       => 'Editar Acta de Visita',
            'acta'        => $acta,
            'idCliente'   => $acta['id_cliente'],
            'integrantes' => $this->integranteModel->getByActa($id),
            'temas'       => $this->temaModel->getByActa($id),
            'fotos'       => (new ActaVisitaFotoModel())->getByActa($id),
            'compromisos' => (new PendientesModel())->where('id_acta_visita', $id)->findAll(),
            'ptaLinks'    => (new ActaVisitaPtaModel())->getByActa($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/form', $data),
            'title'   => 'Editar Acta',
        ]);
    }

    /**
     * Actualizar acta existente (mantiene estado borrador)
     */
    public function update($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'No se puede editar esta acta');
        }

        $actaData = [
            'id_cliente'            => $this->request->getPost('id_cliente'),
            'fecha_visita'          => $this->request->getPost('fecha_visita'),
            'hora_visita'           => $this->request->getPost('hora_visita'),
            'ubicacion_gps'         => $this->request->getPost('ubicacion_gps'),
            'motivo'                => $this->request->getPost('motivo'),
            'modalidad'             => $this->request->getPost('modalidad') ?: 'Presencial',
            'cartera'               => $this->request->getPost('cartera'),
            'observaciones'         => $this->request->getPost('observaciones'),
            'proxima_reunion_fecha' => $this->request->getPost('proxima_reunion_fecha') ?: null,
            'proxima_reunion_hora'  => $this->request->getPost('proxima_reunion_hora') ?: null,
        ];

        $this->actaModel->update($id, $actaData);

        // Reemplazar integrantes, temas, compromisos
        $this->saveIntegrantes($id);
        $this->saveTemas($id);
        $this->saveCompromisos($id);
        $this->saveFotos($id);

        // Guardar actividades PTA (solo en submit real, no autosave)
        if (!$this->isAutosaveRequest()) {
            $this->savePtaActividades($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        $redirect = $this->request->getPost('ir_a_firmas') ? '/inspecciones/acta-visita/firma/' . $id : '/inspecciones/acta-visita/edit/' . $id;

        return redirect()->to($redirect)->with('msg', 'Acta actualizada');
    }

    /**
     * Vista de solo lectura (actas completas)
     */
    public function view($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }

        $clientModel = new ClientModel();

        $data = [
            'title'       => 'Ver Acta de Visita',
            'acta'        => $acta,
            'cliente'     => $clientModel->find($acta['id_cliente']),
            'integrantes' => $this->integranteModel->getByActa($id),
            'temas'       => $this->temaModel->getByActa($id),
            'fotos'       => (new ActaVisitaFotoModel())->getByActa($id),
            'compromisos'                => (new PendientesModel())->where('id_acta_visita', $id)->findAll(),
            'ptaActividades'             => (new ActaVisitaPtaModel())->getByActa($id),
            'pendientesCerradosEnVisita' => (new PendientesModel())
                ->where('id_cliente', $acta['id_cliente'])
                ->where('estado', 'CERRADA')
                ->where('fecha_cierre', $acta['fecha_visita'])
                ->where('(id_acta_visita IS NULL OR id_acta_visita != ' . $id . ')', null, false)
                ->findAll(),
            'mantenimientosEnVisita'     => (new \App\Models\VencimientosMantenimientoModel())
                ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
                ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
                ->where('tbl_vencimientos_mantenimientos.id_cliente', $acta['id_cliente'])
                ->where('estado_actividad', 'ejecutado')
                ->where('fecha_realizacion', $acta['fecha_visita'])
                ->findAll(),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/view', $data),
            'title'   => 'Ver Acta',
        ]);
    }

    /**
     * Pantalla de firmas (paso a paso)
     */
    public function firma($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }
        // Cambiar estado a pendiente_firma
        if ($acta['estado'] === 'borrador') {
            $this->actaModel->update($id, ['estado' => 'pendiente_firma']);
        }

        $integrantes = $this->integranteModel->getByActa($id);

        // Determinar qué firmas se necesitan — exclusivamente desde integrantes del acta
        $firmantes = [];
        foreach ($integrantes as $integrante) {
            $rol = strtoupper($integrante['rol']);
            if ($rol === 'ADMINISTRADOR') {
                $firmantes[] = ['tipo' => 'administrador', 'nombre' => $integrante['nombre'], 'firmado' => !empty($acta['firma_administrador'])];
            } elseif (stripos($rol, 'VIG') !== false) {
                $firmantes[] = ['tipo' => 'vigia', 'nombre' => $integrante['nombre'], 'firmado' => !empty($acta['firma_vigia'])];
            } elseif (stripos($rol, 'CONSULTOR') !== false) {
                $firmantes[] = ['tipo' => 'consultor', 'nombre' => $integrante['nombre'], 'firmado' => !empty($acta['firma_consultor'])];
            }
        }

        $data = [
            'title'    => 'Firmas del Acta',
            'acta'     => $acta,
            'firmantes' => $firmantes,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_visita/firma', $data),
            'title'   => 'Firmas',
        ]);
    }

    /**
     * Guardar firma individual (AJAX)
     */
    public function saveFirma($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        $tipo = $this->request->getPost('tipo'); // administrador, vigia, consultor
        $firmaBase64 = $this->request->getPost('firma_imagen');

        if (!in_array($tipo, ['administrador', 'vigia', 'consultor'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tipo de firma no válido']);
        }

        // Decodificar base64 a PNG
        $firmaData = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));

        // Guardar archivo
        $dir = FCPATH . 'uploads/inspecciones/firmas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombreArchivo = "firma_{$tipo}_{$id}_" . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        // Guardar ruta en BD
        $campo = "firma_{$tipo}";
        $this->actaModel->update($id, [
            $campo => "uploads/inspecciones/firmas/{$nombreArchivo}",
        ]);

        return $this->response->setJSON(['success' => true, 'campo' => $campo]);
    }

    /**
     * Finalizar acta: generar PDF + cargar a reportes
     */
    public function finalizar($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        // Verificar que tiene firma del consultor (obligatoria)
        if (empty($acta['firma_consultor'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Falta la firma del consultor']);
        }

        // Verificar firma del administrador si hay integrante con rol ADMINISTRADOR
        $integrantes = $this->integranteModel->getByActa($id);
        foreach ($integrantes as $integrante) {
            if (strtoupper($integrante['rol']) === 'ADMINISTRADOR' && empty($acta['firma_administrador'])) {
                return $this->response->setJSON(['success' => false, 'error' => 'Falta la firma del administrador']);
            }
        }

        // Generar PDF
        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error al generar PDF']);
        }

        // Actualizar acta
        $this->actaModel->update($id, [
            'ruta_pdf' => $pdfPath,
            'estado'   => 'completo',
        ]);

        // Auto-upload a tbl_reporte
        $acta = $this->actaModel->find($id); // Re-read with updated data
        $this->uploadToReportes($acta, $pdfPath);

        // Enviar email con PDF adjunto
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $acta['id_cliente'],
            (int) $acta['id_consultor'],
            'ACTA DE VISITA',
            $acta['fecha_visita'],
            $pdfPath,
            (int) $acta['id'],
            'ActaVisita'
        );
        $emailMsg = '';
        if ($emailResult['success']) {
            $emailMsg = $emailResult['message'];
        } else {
            $emailMsg = '(Email no enviado: ' . $emailResult['error'] . ')';
        }

        // ─── Hook: Actualizar ciclo de visita en tbl_ciclos_visita ───
        $this->actualizarCicloVisita($acta);

        // Email al consultor con enlace de evaluaciones rápidas
        $this->enviarEmailEvaluacionesRapidas($acta);

        return $this->response->setJSON([
            'success'   => true,
            'pdf_url'   => base_url($pdfPath),
            'email_msg' => $emailMsg,
        ]);
    }

    /**
     * Finalizar acta sin firma del cliente (administrador/vigía)
     */
    public function finalizarSinFirma($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        $motivo = trim($this->request->getJSON(true)['motivo'] ?? '');
        if (!$motivo) {
            return $this->response->setJSON(['success' => false, 'error' => 'Debes indicar el motivo']);
        }

        if (empty($acta['firma_consultor'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Falta la firma del consultor']);
        }

        $this->actaModel->update($id, ['motivo_sin_firma' => $motivo]);

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error al generar PDF']);
        }

        $this->actaModel->update($id, ['ruta_pdf' => $pdfPath, 'estado' => 'completo']);

        $acta = $this->actaModel->find($id);
        $this->uploadToReportes($acta, $pdfPath);

        InspeccionEmailNotifier::enviar(
            (int) $acta['id_cliente'],
            (int) $acta['id_consultor'],
            'ACTA DE VISITA',
            $acta['fecha_visita'],
            $pdfPath,
            (int) $acta['id'],
            'ActaVisita'
        );

        $this->actualizarCicloVisita($acta);
        $this->enviarEmailEvaluacionesRapidas($acta);

        return $this->response->setJSON([
            'success' => true,
            'pdf_url' => base_url($pdfPath),
        ]);
    }

    /**
     * Ver/descargar PDF - siempre regenera desde el template actual
     */
    public function generatePdf($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }

        // Regenerar PDF desde template actual
        $pdfRelPath = $this->generarPdfInterno($id);
        if (!$pdfRelPath) {
            return redirect()->back()->with('error', 'Error generando PDF');
        }
        $this->actaModel->update($id, ['ruta_pdf' => $pdfRelPath]);

        $pdfPath = FCPATH . $pdfRelPath;

        $this->servirPdf($pdfPath, 'acta_visita_' . $id . '.pdf');
    }

    /**
     * Eliminar acta (solo borradores)
     */
    public function delete($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Acta no encontrada');
        }
        // Eliminar fotos del disco
        $fotos = (new ActaVisitaFotoModel())->getByActa($id);
        foreach ($fotos as $foto) {
            $path = FCPATH . $foto['ruta_archivo'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Eliminar firmas del disco
        foreach (['firma_administrador', 'firma_vigia', 'firma_consultor'] as $campo) {
            if (!empty($acta[$campo]) && file_exists(FCPATH . $acta[$campo])) {
                unlink(FCPATH . $acta[$campo]);
            }
        }

        // CASCADE eliminará integrantes, temas, fotos automáticamente
        $this->actaModel->delete($id);

        return redirect()->to('/inspecciones/acta-visita')->with('msg', 'Acta eliminada');
    }

    /**
     * API: Obtener actividades PTA abiertas para un cliente/mes (AJAX)
     */
    public function getPtaActividades()
    {
        $idCliente  = (int) $this->request->getGet('id_cliente');
        $fechaVisita = $this->request->getGet('fecha_visita');

        if (!$idCliente || !$fechaVisita) {
            return $this->response->setJSON([]);
        }

        $ptaModel = new PtaClienteNuevaModel();
        $actividades = $ptaModel->getAbiertosByClienteYMes($idCliente, $fechaVisita);

        // Si es edición, cargar estado previo de checkboxes
        $idActa = (int) $this->request->getGet('id_acta');
        $prevLinks = [];
        if ($idActa) {
            $linkModel = new ActaVisitaPtaModel();
            $links = $linkModel->where('id_acta_visita', $idActa)->findAll();
            foreach ($links as $link) {
                $prevLinks[$link['id_ptacliente']] = [
                    'cerrada'       => (bool) $link['cerrada'],
                    'justificacion' => $link['justificacion_no_cierre'] ?? '',
                ];
            }

            // También incluir actividades que ya fueron cerradas en esta acta
            // (ya no aparecen como ABIERTA pero deben mostrarse checked+disabled)
            $cerradasEnActa = $linkModel->select('tbl_acta_visita_pta.*, tbl_pta_cliente.actividad_plandetrabajo, tbl_pta_cliente.numeral_plandetrabajo, tbl_pta_cliente.fecha_propuesta, tbl_pta_cliente.estado_actividad')
                ->join('tbl_pta_cliente', 'tbl_pta_cliente.id_ptacliente = tbl_acta_visita_pta.id_ptacliente', 'left')
                ->where('tbl_acta_visita_pta.id_acta_visita', $idActa)
                ->where('tbl_acta_visita_pta.cerrada', 1)
                ->findAll();

            // Agregar las cerradas que ya no están en la lista de abiertas
            $idsAbiertas = array_column($actividades, 'id_ptacliente');
            foreach ($cerradasEnActa as $ca) {
                if (!in_array($ca['id_ptacliente'], $idsAbiertas)) {
                    $actividades[] = [
                        'id_ptacliente'            => $ca['id_ptacliente'],
                        'actividad_plandetrabajo'  => $ca['actividad_plandetrabajo'],
                        'numeral_plandetrabajo'    => $ca['numeral_plandetrabajo'],
                        'fecha_propuesta'          => $ca['fecha_propuesta'],
                        'estado_actividad'         => $ca['estado_actividad'],
                        '_ya_cerrada'              => true,
                    ];
                }
            }
        }

        return $this->response->setJSON([
            'actividades' => $actividades,
            'prevLinks'   => $prevLinks,
        ]);
    }

    // ========== FIRMA REMOTA (WhatsApp) ==========

    /**
     * AJAX (auth): genera token y devuelve URL para compartir
     */
    public function generarTokenFirma(int $id)
    {
        $tipo = $this->request->getPost('tipo');
        if (!in_array($tipo, ['administrador', 'vigia', 'consultor'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tipo inválido']);
        }

        $acta = $this->actaModel->find($id);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        $token     = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->actaModel->update($id, [
            'token_firma_remota'     => $token,
            'token_firma_tipo'       => $tipo,
            'token_firma_expiracion' => $expiracion,
        ]);

        $url = base_url("acta-visita/firmar-remoto/{$token}");
        return $this->response->setJSON(['success' => true, 'url' => $url, 'tipo' => $tipo]);
    }

    /**
     * Página pública: canvas de firma para el firmante remoto
     */
    public function firmarRemoto(string $token)
    {
        $acta = $this->actaModel->where('token_firma_remota', $token)->first();

        if (!$acta) {
            return view('inspecciones/acta_visita/firma_remota_error', ['mensaje' => 'Este enlace no es válido o ya fue usado.']);
        }

        if (strtotime($acta['token_firma_expiracion']) < time()) {
            return view('inspecciones/acta_visita/firma_remota_error', ['mensaje' => 'Este enlace ha expirado. Pida uno nuevo al consultor.']);
        }

        $campoFirma = 'firma_' . $acta['token_firma_tipo'];
        if (!empty($acta[$campoFirma])) {
            return view('inspecciones/acta_visita/firma_remota_error', ['mensaje' => 'Esta firma ya fue registrada.']);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);

        // Nombre del firmante desde integrantes
        $integrantes = $this->integranteModel->getByActa($acta['id']);
        $nombreFirmante = '';
        foreach ($integrantes as $integrante) {
            $rol = strtoupper($integrante['rol']);
            $tipo = $acta['token_firma_tipo'];
            if ($tipo === 'administrador' && strpos($rol, 'ADMIN') !== false) {
                $nombreFirmante = $integrante['nombre'];
                break;
            }
            if ($tipo === 'vigia' && strpos($rol, 'VIG') !== false) {
                $nombreFirmante = $integrante['nombre'];
                break;
            }
            if ($tipo === 'consultor' && strpos($rol, 'CONSULTOR') !== false) {
                $nombreFirmante = $integrante['nombre'];
                break;
            }
        }

        $temas       = $this->temaModel->getByActa($acta['id']);
        $compromisos = (new PendientesModel())->where('id_acta_visita', $acta['id'])->findAll();

        // Pendientes abiertos del cliente (excluyendo los de esta acta)
        $pendientesAbiertos = (new PendientesModel())
            ->where('id_cliente', $acta['id_cliente'])
            ->where('estado', 'ABIERTA')
            ->groupStart()
                ->where('id_acta_visita IS NULL', null, false)
                ->orWhere('id_acta_visita !=', $acta['id'])
            ->groupEnd()
            ->findAll();

        // Mantenimientos vencidos o próximos a vencer
        $dateThreshold = date('Y-m-d', strtotime('+30 days'));
        $mantenimientos = (new VencimientosMantenimientoModel())
            ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->where('tbl_vencimientos_mantenimientos.id_cliente', $acta['id_cliente'])
            ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
            ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $dateThreshold)
            ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
            ->findAll();

        return view('inspecciones/acta_visita/firma_remota', [
            'token'              => $token,
            'acta'               => $acta,
            'cliente'            => $cliente,
            'tipo'               => $acta['token_firma_tipo'],
            'nombreFirmante'     => $nombreFirmante,
            'integrantes'        => $integrantes,
            'temas'              => $temas,
            'compromisos'        => $compromisos,
            'pendientesAbiertos' => $pendientesAbiertos,
            'mantenimientos'     => $mantenimientos,
        ]);
    }

    /**
     * AJAX público: recibe y guarda la firma remota
     */
    public function procesarFirmaRemota()
    {
        $token       = $this->request->getPost('token');
        $firmaBase64 = $this->request->getPost('firma_imagen');

        $acta = $this->actaModel->where('token_firma_remota', $token)->first();
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace inválido']);
        }

        if (strtotime($acta['token_firma_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace expirado']);
        }

        $tipo = $acta['token_firma_tipo'];
        $firmaData = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));

        $dir = FCPATH . 'uploads/inspecciones/firmas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombreArchivo = "firma_{$tipo}_{$acta['id']}_" . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        $campo = "firma_{$tipo}";
        $this->actaModel->update($acta['id'], [
            $campo                   => "uploads/inspecciones/firmas/{$nombreArchivo}",
            'token_firma_remota'     => null,
            'token_firma_tipo'       => null,
            'token_firma_expiracion' => null,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    // ========== MÉTODOS PRIVADOS ==========

    /**
     * Guardar/reemplazar integrantes del POST
     */
        public function regenerarPdf($id)
    {
        $inspeccion = $this->actaModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/acta-visita')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->actaModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->actaModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function saveIntegrantes(int $idActa): void
    {
        $nombres = $this->request->getPost('integrante_nombre') ?? [];
        $roles = $this->request->getPost('integrante_rol') ?? [];

        $integrantes = [];
        foreach ($nombres as $i => $nombre) {
            if (!empty(trim($nombre))) {
                $integrantes[] = [
                    'nombre' => trim($nombre),
                    'rol'    => $roles[$i] ?? '',
                ];
            }
        }

        $this->integranteModel->replaceForActa($idActa, $integrantes);
    }

    /**
     * Guardar/reemplazar temas del POST
     */
    private function saveTemas(int $idActa): void
    {
        $temas = $this->request->getPost('tema') ?? [];
        $temasLimpios = array_filter(array_map('trim', $temas));

        $this->temaModel->replaceForActa($idActa, array_values($temasLimpios));
    }

    /**
     * Guardar compromisos como pendientes en tbl_pendientes
     */
    private function saveCompromisos(int $idActa): void
    {
        $actividades = $this->request->getPost('compromiso_actividad') ?? [];
        $fechas = $this->request->getPost('compromiso_fecha') ?? [];
        $responsables = $this->request->getPost('compromiso_responsable') ?? [];

        $acta = $this->actaModel->find($idActa);
        $pendientesModel = new PendientesModel();

        // Eliminar pendientes anteriores de esta acta
        $pendientesModel->where('id_acta_visita', $idActa)->delete();

        foreach ($actividades as $i => $actividad) {
            if (!empty(trim($actividad))) {
                $pendientesModel->insert([
                    'id_cliente'      => $acta['id_cliente'],
                    'tarea_actividad' => trim($actividad),
                    'fecha_asignacion' => date('Y-m-d'),
                    'fecha_cierre'    => $fechas[$i] ?? null,
                    'responsable'     => $responsables[$i] ?? '',
                    'estado'          => 'ABIERTA',
                    'id_acta_visita'  => $idActa,
                ]);
            }
        }
    }

    /**
     * Guardar fotos subidas
     */
    private function saveFotos(int $idActa): void
    {
        $files = $this->request->getFiles();
        if (empty($files['fotos'])) {
            return;
        }

        $fotoModel = new ActaVisitaFotoModel();
        $dir = FCPATH . 'uploads/inspecciones/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($files['fotos'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);

                $fotoModel->insert([
                    'id_acta_visita' => $idActa,
                    'ruta_archivo'   => 'uploads/inspecciones/fotos/' . $fileName,
                    'tipo'           => 'foto',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Guardar actividades PTA marcadas/no marcadas desde el formulario
     */
    private function savePtaActividades(int $idActa): void
    {
        $ptaIds           = $this->request->getPost('pta_actividad_id') ?? [];
        $ptaCheckedIds    = $this->request->getPost('pta_actividad_checked') ?? [];
        $ptaJustificaciones = $this->request->getPost('pta_justificacion') ?? [];

        if (empty($ptaIds)) {
            return;
        }

        $linkModel = new ActaVisitaPtaModel();
        $ptaModel  = new PtaClienteNuevaModel();
        $acta      = $this->actaModel->find($idActa);
        $fechaVisita = $acta['fecha_visita'];
        $idClienteActa = (int) $acta['id_cliente'];

        // Eliminar links anteriores de esta acta
        $linkModel->where('id_acta_visita', $idActa)->delete();

        foreach ($ptaIds as $idPta) {
            $idPta    = (int) $idPta;
            $isCerrada = in_array((string) $idPta, $ptaCheckedIds);
            $justificacion = $ptaJustificaciones[$idPta] ?? '';

            // Validar que la actividad PTA pertenece al cliente del acta (seguridad)
            $ptaRecord = $ptaModel->find($idPta);
            if (!$ptaRecord || (int) $ptaRecord['id_cliente'] !== $idClienteActa) {
                continue;
            }

            // Insertar link
            $linkModel->insert([
                'id_acta_visita'          => $idActa,
                'id_ptacliente'           => $idPta,
                'cerrada'                 => $isCerrada ? 1 : 0,
                'justificacion_no_cierre' => $isCerrada ? null : ($justificacion ?: null),
                'created_at'              => date('Y-m-d H:i:s'),
            ]);

            if ($isCerrada && $ptaRecord['estado_actividad'] === 'ABIERTA') {
                // Cerrar la actividad en el PTA
                $ptaModel->update($idPta, [
                    'estado_actividad'  => 'CERRADA',
                    'porcentaje_avance' => 100,
                    'fecha_cierre'      => $fechaVisita,
                ]);

                // Auditoría
                PtaAuditService::log($idPta, 'UPDATE', 'estado_actividad', 'ABIERTA', 'CERRADA', 'ActaVisitaController::savePtaActividades', (int) $acta['id_cliente']);
                PtaAuditService::log($idPta, 'UPDATE', 'porcentaje_avance', $ptaRecord['porcentaje_avance'], '100', 'ActaVisitaController::savePtaActividades', (int) $acta['id_cliente']);
                PtaAuditService::log($idPta, 'UPDATE', 'fecha_cierre', $ptaRecord['fecha_cierre'], $fechaVisita, 'ActaVisitaController::savePtaActividades', (int) $acta['id_cliente']);
                PtaTransicionesService::registrar($idPta, (int) $acta['id_cliente'], 'ABIERTA', 'CERRADA');
            } elseif (!$isCerrada && !empty($justificacion)) {
                // Concatenar justificación a observaciones existentes
                $obsActual = $ptaRecord['observaciones'] ?? '';
                $separador = $obsActual ? "\n" : '';
                $append = $separador . "[Acta Visita #{$idActa} - {$fechaVisita}] No cerrada: {$justificacion}";
                $nuevaObs = $obsActual . $append;

                $ptaModel->update($idPta, [
                    'observaciones' => $nuevaObs,
                ]);

                PtaAuditService::log($idPta, 'UPDATE', 'observaciones', $obsActual, $nuevaObs, 'ActaVisitaController::savePtaActividades', (int) $acta['id_cliente']);
            }
        }
    }

    /**
     * Generar PDF con DOMPDF y guardarlo en disco
     */
    private function generarPdfInterno(int $id): ?string
    {
        $acta = $this->actaModel->find($id);
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        $integrantes = $this->integranteModel->getByActa($id);
        $temas = $this->temaModel->getByActa($id);
        $compromisos = (new PendientesModel())->where('id_acta_visita', $id)->findAll();

        // Pendientes abiertos del cliente
        $pendientesAbiertos = (new PendientesModel())
            ->where('id_cliente', $acta['id_cliente'])
            ->where('estado', 'ABIERTA')
            ->groupStart()
                ->where('id_acta_visita IS NULL', null, false)
                ->orWhere('id_acta_visita !=', $id)
            ->groupEnd()
            ->findAll();

        // Mantenimientos por vencer
        $dateThreshold = date('Y-m-d', strtotime('+30 days'));
        $mantenimientos = (new VencimientosMantenimientoModel())
            ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->where('tbl_vencimientos_mantenimientos.id_cliente', $acta['id_cliente'])
            ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
            ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $dateThreshold)
            ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
            ->findAll();

        // Cargar firmas como base64 para incrustar en DOMPDF
        $firmas = [];
        foreach (['administrador', 'vigia', 'consultor'] as $tipo) {
            $campo = "firma_{$tipo}";
            if (!empty($acta[$campo])) {
                $path = FCPATH . $acta[$campo];
                if (file_exists($path)) {
                    $firmas[$tipo] = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
                }
            }
        }

        // Logo del cliente
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        // Consultor
        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($acta['id_consultor']);

        // Nombre del firmante consultor: siempre desde integrantes
        $nombreConsultorFirma = '';
        foreach ($integrantes as $integrante) {
            if (stripos($integrante['rol'], 'CONSULTOR') !== false) {
                $nombreConsultorFirma = $integrante['nombre'];
                break;
            }
        }

        // Fotos del acta convertidas a base64 para DOMPDF
        $fotosBase64 = [];
        $fotosModel = new ActaVisitaFotoModel();
        $fotos = $fotosModel->getByActa($id);
        foreach ($fotos as $foto) {
            $fotoPath = FCPATH . $foto['ruta_archivo'];
            if (file_exists($fotoPath)) {
                $fotosBase64[] = [
                    'data'        => $this->fotoABase64ParaPdf($fotoPath),
                    'descripcion' => $foto['descripcion'] ?? '',
                    'tipo'        => $foto['tipo'] ?? 'foto',
                ];
            }
        }

        // Actividades PTA (todas las revisadas en esta acta)
        $ptaActividades = (new ActaVisitaPtaModel())->getByActa($id);

        // Pendientes cerrados durante la visita y mantenimientos ejecutados
        $pendientesCerradosEnVisita = (new PendientesModel())
            ->where('id_cliente', $acta['id_cliente'])
            ->where('estado', 'CERRADA')
            ->where('fecha_cierre', $acta['fecha_visita'])
            ->where('(id_acta_visita IS NULL OR id_acta_visita != ' . $id . ')', null, false)
            ->findAll();
        $mantenimientosEnVisita = (new \App\Models\VencimientosMantenimientoModel())
            ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->where('tbl_vencimientos_mantenimientos.id_cliente', $acta['id_cliente'])
            ->where('estado_actividad', 'ejecutado')
            ->where('fecha_realizacion', $acta['fecha_visita'])
            ->findAll();

        $data = [
            'acta'                       => $acta,
            'cliente'                    => $cliente,
            'consultor'                  => $consultor,
            'nombreConsultorFirma'       => $nombreConsultorFirma,
            'integrantes'                => $integrantes,
            'temas'                      => $temas,
            'compromisos'                => $compromisos,
            'pendientesAbiertos'         => $pendientesAbiertos,
            'mantenimientos'             => $mantenimientos,
            'firmas'                     => $firmas,
            'logoBase64'                 => $logoBase64,
            'fotos'                      => $fotosBase64,
            'ptaActividades'             => $ptaActividades,
            'pendientesCerradosEnVisita' => $pendientesCerradosEnVisita,
            'mantenimientosEnVisita'     => $mantenimientosEnVisita,
        ];

        $html = view('inspecciones/acta_visita/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Guardar PDF
        $pdfDir = 'uploads/inspecciones/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'acta_visita_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Eliminar PDF anterior si existe
        if (!empty($acta['ruta_pdf']) && file_exists(FCPATH . $acta['ruta_pdf'])) {
            unlink(FCPATH . $acta['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    /**
     * Registra el PDF en tbl_reporte para que aparezca en reportes del cliente
     */
    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta || $acta['estado'] !== 'completo' || empty($acta['ruta_pdf'])) {
            return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $acta['id_cliente'],
            (int) $acta['id_consultor'],
            'ACTA DE VISITA',
            $acta['fecha_visita'],
            $acta['ruta_pdf'],
            (int) $acta['id'],
            'ActaVisita'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/acta-visita/view/{$id}")->with('error', $result['error']);
    }

    private function uploadToReportes(array $acta, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();

        $cliente = $clientModel->find($acta['id_cliente']);
        if (!$cliente) {
            return false;
        }

        $nitCliente = $cliente['nit_cliente'];

        // Verificar si ya existe un reporte para esta acta
        $existente = $reporteModel
            ->where('id_cliente', $acta['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 9)
            ->like('observaciones', 'acta_id:' . $acta['id'])
            ->first();

        // Copiar a uploads/{nit_cliente}/
        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'acta_visita_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'ACTA DE VISITA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_visita'],
            'id_detailreport' => 9,
            'id_report_type'  => 6,
            'id_cliente'      => $acta['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. acta_id:' . $acta['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * Actualizar ciclo de visita al completar un acta
     */
    private function actualizarCicloVisita(array $acta): void
    {
        $cicloModel = new CicloVisitaModel();

        $mesActa  = (int)date('n', strtotime($acta['fecha_visita']));
        $anioActa = (int)date('Y', strtotime($acta['fecha_visita']));

        // Buscar ciclo pendiente para este cliente en el mes del acta
        $ciclo = $cicloModel->where('id_cliente', $acta['id_cliente'])
            ->where('mes_esperado', $mesActa)
            ->where('anio', $anioActa)
            ->first();

        if (!$ciclo) {
            return; // No hay ciclo registrado para este periodo
        }

        // Determinar estatus de agenda
        $estatusAgenda = 'cumple';
        if ($ciclo['fecha_agendada'] && $acta['fecha_visita'] !== $ciclo['fecha_agendada']) {
            $estatusAgenda = 'incumple'; // Fue en otro día del agendado
        }

        $cicloModel->update($ciclo['id'], [
            'fecha_acta'      => $acta['fecha_visita'],
            'id_acta'         => $acta['id'],
            'estatus_agenda'  => $estatusAgenda,
            'estatus_mes'     => 'cumple', // Si hay acta en el mes, el mes cumple
        ]);

        // Auto-generar siguiente ciclo
        $estandar = $ciclo['estandar'] ?? '';
        if ($estandar) {
            $cicloModel->generarSiguienteCiclo(
                (int)$acta['id_cliente'],
                $acta['fecha_visita'],
                $estandar,
                (int)$acta['id_consultor']
            );
        }
    }

    // ============================================================
    // EVALUACIONES RÁPIDAS POST-VISITA
    // ============================================================

    private function generarTokenEvaluacion(int $actaId, int $clienteId): string
    {
        return substr(hash('sha256', $actaId . '|' . $clienteId . '|evvisita2026'), 0, 24);
    }

    /**
     * Página pública de evaluaciones rápidas (acceso por token)
     */
    public function evaluacionesVisita(int $actaId, string $token)
    {
        $acta = $this->actaModel->find($actaId);
        if (!$acta || $acta['estado'] !== 'completo') {
            return view('inspecciones/acta_visita/evaluaciones_visita_error', [
                'mensaje' => 'Este enlace no es válido o el acta aún no ha sido finalizada.',
            ]);
        }

        if (!hash_equals($this->generarTokenEvaluacion($actaId, (int)$acta['id_cliente']), $token)) {
            return view('inspecciones/acta_visita/evaluaciones_visita_error', [
                'mensaje' => 'Enlace inválido.',
            ]);
        }

        $cliente = (new ClientModel())->find($acta['id_cliente']);

        $evaluacionModel = new \App\Models\EvaluationModel();
        $evaluaciones = $evaluacionModel
            ->where('id_cliente', $acta['id_cliente'])
            ->groupStart()
                ->where('evaluacion_inicial IS NULL', null, false)
                ->orWhere('evaluacion_inicial', '')
                ->orWhere('evaluacion_inicial', '-')
                ->orWhere('evaluacion_inicial', 'NO CUMPLE')
            ->groupEnd()
            ->orderBy('estandar', 'ASC')
            ->orderBy('numeral', 'ASC')
            ->findAll();

        return view('inspecciones/acta_visita/evaluaciones_visita', [
            'acta'        => $acta,
            'cliente'     => $cliente,
            'evaluaciones' => $evaluaciones,
            'token'       => $token,
        ]);
    }

    /**
     * API pública: actualizar evaluación desde página de evaluaciones rápidas (token)
     */
    public function updateEvaluacionPublica()
    {
        log_message('info', '[EvalRapida] === REQUEST RECIBIDO ===');
        log_message('info', '[EvalRapida] Method: ' . $this->request->getMethod());
        log_message('info', '[EvalRapida] POST raw: ' . json_encode($this->request->getPost()));
        log_message('info', '[EvalRapida] Content-Type: ' . ($this->request->header('Content-Type') ?? 'NULL'));
        log_message('info', '[EvalRapida] User-Agent: ' . ($this->request->getUserAgent()->getAgentString() ?? 'NULL'));

        $actaId = (int) $this->request->getPost('acta_id');
        $token  = $this->request->getPost('token');
        $id     = $this->request->getPost('id');

        log_message('info', "[EvalRapida] actaId={$actaId}, token={$token}, id={$id}");

        $acta = $this->actaModel->find($actaId);
        if (!$acta || $acta['estado'] !== 'completo') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acta inválida']);
        }

        if (!$token || !hash_equals($this->generarTokenEvaluacion($actaId, (int)$acta['id_cliente']), $token)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Token inválido']);
        }

        $model = new \App\Models\EvaluationModel();
        $evaluation = $model->find($id);

        if (!$evaluation || (int)$evaluation['id_cliente'] !== (int)$acta['id_cliente']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Evaluación no corresponde al cliente']);
        }

        $valor = isset($evaluation['valor']) ? $evaluation['valor'] : 0;
        $updateData = [
            'evaluacion_inicial'    => 'CUMPLE TOTALMENTE',
            'puntaje_cuantitativo'  => $valor,
        ];

        if ($model->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar']);
    }

    /**
     * Enviar email al consultor con enlace de evaluaciones rápidas
     */
    private function enviarEmailEvaluacionesRapidas(array $acta): void
    {
        $consultor = (new ConsultantModel())->find($acta['id_consultor']);
        if (!$consultor || empty($consultor['correo_consultor'])) return;

        $apiKey = env('SENDGRID_API_KEY');
        if (!$apiKey) return;

        $cliente  = (new ClientModel())->find($acta['id_cliente']);
        $token    = $this->generarTokenEvaluacion((int)$acta['id'], (int)$acta['id_cliente']);
        $url      = base_url("acta-visita/evaluaciones-visita/{$acta['id']}/{$token}");
        $fecha    = date('d/m/Y', strtotime($acta['fecha_visita']));
        $nomCli   = htmlspecialchars($cliente['nombre_cliente'] ?? '');
        $nomCons  = htmlspecialchars($consultor['nombre_consultor'] ?? 'Consultor');
        $urlEsc   = htmlspecialchars($url);

        $html = "
        <div style='font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:#1c2437;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
                <h1 style='color:#bd9751;margin:0;font-size:20px;'>Evaluación Rápida Post-Visita</h1>
            </div>
            <div style='padding:25px;background:#f8f9fa;border-radius:0 0 10px 10px;'>
                <p>Hola <strong>{$nomCons}</strong>,</p>
                <p>El acta de visita del <strong>{$fecha}</strong> para <strong>{$nomCli}</strong> ha sido finalizada.</p>
                <p>Usa este enlace para marcar los ítems de cumplimiento que se cerraron en esta visita:</p>
                <div style='text-align:center;margin:24px 0;'>
                    <a href='{$urlEsc}' style='background:#bd9751;color:white;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;'>
                        ✔ Actualizar Evaluaciones
                    </a>
                </div>
                <p style='font-size:12px;color:#999;word-break:break-all;'>Enlace directo: {$urlEsc}</p>
                <p style='color:#999;font-size:11px;margin-top:20px;'>Generado por SG-SST Cycloid Talent.</p>
            </div>
        </div>";

        $payload = json_encode([
            'personalizations' => [['to' => [['email' => $consultor['correo_consultor'], 'name' => $nomCons]], 'subject' => "Evaluaciones rápidas — {$nomCli} — {$fecha}"]],
            'from'    => ['email' => 'notificacion.cycloidtalent@cycloidtalent.com', 'name' => 'Cycloid Talent - SG-SST'],
            'content' => [['type' => 'text/html', 'value' => $html]],
            'tracking_settings' => [
                'click_tracking' => ['enable' => false, 'enable_text' => false],
            ],
        ]);

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
