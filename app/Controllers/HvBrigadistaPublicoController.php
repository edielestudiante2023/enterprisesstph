<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HvBrigadistaModel;
use App\Models\ClientModel;

class HvBrigadistaPublicoController extends BaseController
{
    protected HvBrigadistaModel $hvModel;

    public function __construct()
    {
        $this->hvModel = new HvBrigadistaModel();
    }

    /**
     * GET /hv-brigadista — Formulario público single-page (standalone, sin layout_pwa)
     */
    public function form()
    {
        return view('hv-brigadista/form_publico');
    }

    /**
     * GET /hv-brigadista/api/clientes — Clientes activos con contrato activo (AJAX, público)
     */
    public function getClientesActivos()
    {
        $clientModel = new ClientModel();

        $clientes = $clientModel
            ->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente')
            ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'")
            ->where('tbl_clientes.estado', 'activo')
            ->orderBy('tbl_clientes.nombre_cliente', 'ASC')
            ->groupBy('tbl_clientes.id_cliente')
            ->findAll();

        return $this->response->setJSON($clientes);
    }

    /**
     * POST /hv-brigadista/store — Envío único del formulario completo (AJAX)
     * FormData con campos + foto (File) + firma (base64)
     */
    public function store()
    {
        // Validar campos requeridos
        $idCliente = (int)$this->request->getPost('id_cliente');
        $nombreCompleto = trim($this->request->getPost('nombre_completo') ?? '');
        $documento = trim($this->request->getPost('documento_identidad') ?? '');

        $errores = [];
        if (!$idCliente) $errores[] = 'Copropiedad';
        if (empty($nombreCompleto)) $errores[] = 'Nombre completo';
        if (empty($documento)) $errores[] = 'Documento de identidad';

        if (!empty($errores)) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Campos obligatorios faltantes: ' . implode(', ', $errores),
            ])->setStatusCode(400);
        }

        // Mapear estudios (JSON string -> columnas flat)
        $estudiosJson = $this->request->getPost('estudios');
        $estudios = $estudiosJson ? json_decode($estudiosJson, true) : [];

        // Guardar foto
        $fotoPath = $this->guardarFoto();

        // Guardar firma (base64 -> archivo)
        $firmaPath = $this->guardarFirma();

        // Construir datos para insertar
        $saveData = [
            'id_cliente'            => $idCliente,
            'fecha_registro'        => date('Y-m-d H:i:s'),
            'fecha_inscripcion'     => $this->request->getPost('fecha_inscripcion') ?: date('Y-m-d'),
            'foto_brigadista'       => $fotoPath,
            'nombre_completo'       => $nombreCompleto,
            'documento_identidad'   => $documento,
            'f_nacimiento'          => $this->request->getPost('f_nacimiento') ?: null,
            'email'                 => trim($this->request->getPost('email') ?? ''),
            'telefono'              => trim($this->request->getPost('telefono') ?? ''),
            'direccion_residencia'  => trim($this->request->getPost('direccion_residencia') ?? ''),
            'edad'                  => $this->request->getPost('edad') ? (int)$this->request->getPost('edad') : null,
            'eps'                   => trim($this->request->getPost('eps') ?? ''),
            'peso'                  => $this->request->getPost('peso') ?: null,
            'estatura'              => $this->request->getPost('estatura') ?: null,
            'rh'                    => $this->request->getPost('rh') ?: null,
            // Estudios flat (max 3)
            'estudios_1'            => $estudios[0]['nombre'] ?? null,
            'lugar_estudio_1'       => $estudios[0]['institucion'] ?? null,
            'anio_estudio_1'        => !empty($estudios[0]['anio']) ? (int)$estudios[0]['anio'] : null,
            'estudios_2'            => $estudios[1]['nombre'] ?? null,
            'lugar_estudio_2'       => $estudios[1]['institucion'] ?? null,
            'anio_estudio_2'        => !empty($estudios[1]['anio']) ? (int)$estudios[1]['anio'] : null,
            'estudios_3'            => $estudios[2]['nombre'] ?? null,
            'lugar_estudio_3'       => $estudios[2]['institucion'] ?? null,
            'anio_estudio_3'        => !empty($estudios[2]['anio']) ? (int)$estudios[2]['anio'] : null,
            // Salud
            'enfermedades_importantes' => trim($this->request->getPost('enfermedades_importantes') ?? ''),
            'medicamentos'             => trim($this->request->getPost('medicamentos') ?? ''),
            // 14 preguntas SI/NO
            'cardiaca'              => $this->request->getPost('cardiaca') ?: null,
            'pechoactividad'        => $this->request->getPost('pechoactividad') ?: null,
            'dolorpecho'            => $this->request->getPost('dolorpecho') ?: null,
            'conciencia'            => $this->request->getPost('conciencia') ?: null,
            'huesos'                => $this->request->getPost('huesos') ?: null,
            'medicamentos_bool'     => $this->request->getPost('medicamentos_bool') ?: null,
            'actividadfisica'       => $this->request->getPost('actividadfisica') ?: null,
            'convulsiones'          => $this->request->getPost('convulsiones') ?: null,
            'vertigo'               => $this->request->getPost('vertigo') ?: null,
            'oidos'                 => $this->request->getPost('oidos') ?: null,
            'lugarescerrados'       => $this->request->getPost('lugarescerrados') ?: null,
            'miedoalturas'          => $this->request->getPost('miedoalturas') ?: null,
            'haceejercicio'         => $this->request->getPost('haceejercicio') ?: null,
            'miedo_ver_sangre'      => $this->request->getPost('miedo_ver_sangre') ?: null,
            // Extras
            'restricciones_medicas' => trim($this->request->getPost('restricciones_medicas') ?? ''),
            'deporte_semana'        => trim($this->request->getPost('deporte_semana') ?? ''),
            // Firma
            'firma'                 => $firmaPath,
            // Estado
            'estado'                => 'completo',
        ];

        $this->hvModel->insert($saveData);
        $id = $this->hvModel->getInsertID();

        return $this->response->setJSON(['success' => true, 'id' => $id, 'message' => 'Hoja de vida enviada exitosamente']);
    }

    // ===== MÉTODOS PRIVADOS =====

    /**
     * Guarda la foto del brigadista desde el FormData
     */
    private function guardarFoto(): ?string
    {
        $file = $this->request->getFile('foto_brigadista');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $validTypes)) {
            return null;
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return null;
        }

        $dir = 'uploads/inspecciones/hv-brigadista/fotos/';
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);

        return $dir . $fileName;
    }

    /**
     * Decodifica la firma base64 y la guarda como imagen PNG
     */
    private function guardarFirma(): ?string
    {
        $firmaB64 = $this->request->getPost('firma_imagen');
        if (empty($firmaB64)) {
            return null;
        }

        // Extraer datos base64 (quitar prefijo data:image/png;base64,)
        $parts = explode(',', $firmaB64);
        $data = base64_decode(end($parts));
        if ($data === false) {
            return null;
        }

        $dir = 'uploads/inspecciones/hv-brigadista/firmas/';
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $fileName = 'firma_' . uniqid() . '_' . date('Ymd_His') . '.png';
        $path = $dir . $fileName;

        file_put_contents(FCPATH . $path, $data);

        return $path;
    }
}
