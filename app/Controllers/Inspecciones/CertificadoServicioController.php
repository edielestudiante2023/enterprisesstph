<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\CertificadoServicioModel;
use App\Models\VencimientosMantenimientoModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;

class CertificadoServicioController extends BaseController
{
    /** Configuración por id_mantenimiento */
    private static function config(int $tipo): array
    {
        $map = [
            2 => ['nombre' => 'Lavado de Tanques',  'slug' => 'lavado-tanques',  'icon' => 'fa-water',      'detailreport' => 40],
            3 => ['nombre' => 'Fumigación',          'slug' => 'fumigacion',      'icon' => 'fa-bug',        'detailreport' => 41],
            4 => ['nombre' => 'Desratización',       'slug' => 'desratizacion',   'icon' => 'fa-mouse',      'detailreport' => 42],
        ];
        return $map[$tipo] ?? [];
    }

    public function list(int $tipo)
    {
        $cfg = self::config($tipo);
        if (!$cfg) return redirect()->to('/inspecciones');

        $model = new CertificadoServicioModel();
        $registros = $model
            ->select('tbl_certificado_servicio.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_certificado_servicio.id_cliente', 'left')
            ->where('tbl_certificado_servicio.id_mantenimiento', $tipo)
            ->orderBy('tbl_certificado_servicio.fecha_servicio', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'title'   => $cfg['nombre'],
            'content' => view('inspecciones/certificado-servicio/list', [
                'tipo'      => $tipo,
                'cfg'       => $cfg,
                'registros' => $registros,
            ]),
        ]);
    }

    public function create(int $tipo, int $idCliente = null)
    {
        $cfg = self::config($tipo);
        if (!$cfg) return redirect()->to('/inspecciones');

        $clientModel = new ClientModel();
        $cliente = $idCliente ? $clientModel->find($idCliente) : null;

        // Buscar vencimiento pendiente para este cliente y tipo
        $vencimiento = null;
        if ($idCliente) {
            $vencimiento = (new VencimientosMantenimientoModel())
                ->where('id_cliente', $idCliente)
                ->where('id_mantenimiento', $tipo)
                ->where('estado_actividad', 'sin ejecutar')
                ->orderBy('fecha_vencimiento', 'ASC')
                ->first();
        }

        return view('inspecciones/layout_pwa', [
            'title'   => 'Registrar ' . $cfg['nombre'],
            'content' => view('inspecciones/certificado-servicio/form', [
                'tipo'        => $tipo,
                'cfg'         => $cfg,
                'idCliente'   => $idCliente,
                'cliente'     => $cliente,
                'vencimiento' => $vencimiento,
            ]),
        ]);
    }

    public function store(int $tipo)
    {
        $cfg = self::config($tipo);
        if (!$cfg) return redirect()->to('/inspecciones');

        $idCliente    = $this->request->getPost('id_cliente');
        $fechaServicio = $this->request->getPost('fecha_servicio');
        $observaciones = $this->request->getPost('observaciones');
        $cerrarVenc   = $this->request->getPost('cerrar_vencimiento');
        $idVenc       = $this->request->getPost('id_vencimiento') ?: null;

        if (!$idCliente || !$fechaServicio) {
            session()->setFlashdata('error', 'Cliente y fecha son obligatorios.');
            return redirect()->back();
        }

        // Subir archivo
        $archivo = $this->request->getFile('archivo');
        $archivoPath = null;
        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $dir = 'uploads/inspecciones/certificados/';
            if (!is_dir(FCPATH . $dir)) {
                mkdir(FCPATH . $dir, 0755, true);
            }
            $newName = $cfg['slug'] . '_' . $idCliente . '_' . date('Ymd_His') . '.' . $archivo->getExtension();
            $archivo->move(FCPATH . $dir, $newName);
            $archivoPath = $dir . $newName;
        }

        $model = new CertificadoServicioModel();
        $id = $model->insert([
            'id_cliente'       => $idCliente,
            'id_mantenimiento' => $tipo,
            'fecha_servicio'   => $fechaServicio,
            'archivo'          => $archivoPath,
            'observaciones'    => $observaciones,
            'id_consultor'     => session()->get('user_id'),
            'id_vencimiento'   => null,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        // Cerrar vencimiento si aplica
        if ($cerrarVenc && $idVenc) {
            (new VencimientosMantenimientoModel())->update($idVenc, [
                'estado_actividad'  => 'ejecutado',
                'fecha_realizacion' => $fechaServicio,
            ]);
            $model->update($id, ['id_vencimiento' => $idVenc]);
        }

        // Subir a tbl_reporte
        if ($archivoPath) {
            $this->subirReporte($idCliente, $tipo, $cfg, $fechaServicio, $archivoPath, $id);
        }

        session()->setFlashdata('msg', $cfg['nombre'] . ' registrado.');
        return redirect()->to('/inspecciones/' . $cfg['slug']);
    }

    public function view(int $id)
    {
        $model = new CertificadoServicioModel();
        $registro = $model->find($id);
        if (!$registro) {
            return redirect()->to('/inspecciones')->with('error', 'Registro no encontrado.');
        }

        $cfg = self::config($registro['id_mantenimiento']);
        $cliente = (new ClientModel())->find($registro['id_cliente']);

        return view('inspecciones/layout_pwa', [
            'title'   => 'Ver ' . $cfg['nombre'],
            'content' => view('inspecciones/certificado-servicio/view', [
                'cfg'      => $cfg,
                'registro' => $registro,
                'cliente'  => $cliente,
            ]),
        ]);
    }

    public function delete(int $id)
    {
        $model = new CertificadoServicioModel();
        $reg = $model->find($id);
        if (!$reg) {
            session()->setFlashdata('error', 'No encontrado.');
            return redirect()->to('/inspecciones');
        }

        $cfg = self::config($reg['id_mantenimiento']);

        // Eliminar archivo físico
        if (!empty($reg['archivo']) && file_exists(FCPATH . $reg['archivo'])) {
            unlink(FCPATH . $reg['archivo']);
        }

        $model->delete($id);

        session()->setFlashdata('msg', 'Registro eliminado.');
        return redirect()->to('/inspecciones/' . $cfg['slug']);
    }

    /**
     * AJAX: obtener vencimiento pendiente para cliente+tipo
     */
    public function apiVencimientoPendiente(int $tipo)
    {
        $idCliente = $this->request->getGet('id_cliente');
        if (!$idCliente) {
            return $this->response->setJSON(['vencimiento' => null]);
        }

        $venc = (new VencimientosMantenimientoModel())
            ->where('id_cliente', $idCliente)
            ->where('id_mantenimiento', $tipo)
            ->where('estado_actividad', 'sin ejecutar')
            ->orderBy('fecha_vencimiento', 'ASC')
            ->first();

        return $this->response->setJSON(['vencimiento' => $venc]);
    }

    private function subirReporte(int $idCliente, int $tipo, array $cfg, string $fecha, string $archivoPath, int $registroId): void
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) return;

        $reporteModel = new ReporteModel();
        $nitCliente = $cliente['nit_cliente'];

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $ext      = pathinfo($archivoPath, PATHINFO_EXTENSION);
        $fileName = $cfg['slug'] . '_' . $registroId . '_' . date('Ymd_His') . '.' . $ext;
        $destPath = $destDir . '/' . $fileName;

        if (file_exists(FCPATH . $archivoPath)) {
            copy(FCPATH . $archivoPath, $destPath);
        }

        $tag = $cfg['slug'] . '_id:' . $registroId;
        $existente = $reporteModel
            ->where('id_cliente', $idCliente)
            ->where('id_report_type', 13)
            ->where('id_detailreport', 20)
            ->like('observaciones', $tag)
            ->first();

        $data = [
            'titulo_reporte'  => strtoupper($cfg['nombre']) . ' - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $fecha,
            'id_detailreport' => 20,
            'id_report_type'  => 13,
            'id_cliente'      => $idCliente,
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente. ' . $tag,
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            $reporteModel->update($existente['id_reporte'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $reporteModel->save($data);
        }
    }
}
