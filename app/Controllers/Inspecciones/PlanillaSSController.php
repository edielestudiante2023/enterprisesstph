<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\PlanillaSSModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;

class PlanillaSSController extends BaseController
{
    const DETAIL_REPORT = 43;

    public function list()
    {
        $model    = new PlanillaSSModel();
        $registros = $model
            ->select('tbl_planilla_ss_inspeccion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_planilla_ss_inspeccion.id_cliente', 'left')
            ->orderBy('tbl_planilla_ss_inspeccion.periodo', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'title'   => 'Planilla Seg. Social',
            'content' => view('inspecciones/planilla-ss/list', ['registros' => $registros]),
        ]);
    }

    public function create(?int $idCliente = null)
    {
        $cliente = $idCliente ? (new ClientModel())->find($idCliente) : null;

        return view('inspecciones/layout_pwa', [
            'title'   => 'Registrar Planilla SS',
            'content' => view('inspecciones/planilla-ss/form', [
                'idCliente' => $idCliente,
                'cliente'   => $cliente,
            ]),
        ]);
    }

    public function store()
    {
        $idCliente    = $this->request->getPost('id_cliente');
        $periodo      = $this->request->getPost('periodo');   // YYYY-MM
        $observaciones = $this->request->getPost('observaciones');

        if (!$idCliente || !$periodo) {
            session()->setFlashdata('error', 'Cliente y período son obligatorios.');
            return redirect()->back();
        }

        // Subir archivo
        $archivo = $this->request->getFile('archivo');
        $archivoPath = null;
        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $dir = 'uploads/inspecciones/planillas-ss/';
            if (!is_dir(FCPATH . $dir)) {
                mkdir(FCPATH . $dir, 0755, true);
            }
            $newName = 'planilla_ss_' . $idCliente . '_' . date('Ymd_His') . '.' . $archivo->getExtension();
            $archivo->move(FCPATH . $dir, $newName);
            $archivoPath = $dir . $newName;
        }

        $model = new PlanillaSSModel();
        $id = $model->insert([
            'id_cliente'    => $idCliente,
            'periodo'       => $periodo,
            'archivo'       => $archivoPath,
            'observaciones' => $observaciones,
            'id_consultor'  => session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        if ($archivoPath) {
            $this->subirReporte($idCliente, $periodo, $archivoPath, $id);
        }

        session()->setFlashdata('msg', 'Planilla registrada.');
        return redirect()->to('/inspecciones/planilla-seg-social');
    }

    public function delete(int $id)
    {
        $model = new PlanillaSSModel();
        $reg   = $model->find($id);
        if (!$reg) {
            session()->setFlashdata('error', 'No encontrado.');
            return redirect()->to('/inspecciones/planilla-seg-social');
        }

        if (!empty($reg['archivo']) && file_exists(FCPATH . $reg['archivo'])) {
            unlink(FCPATH . $reg['archivo']);
        }

        $model->delete($id);

        session()->setFlashdata('msg', 'Registro eliminado.');
        return redirect()->to('/inspecciones/planilla-seg-social');
    }

    private function subirReporte(int $idCliente, string $periodo, string $archivoPath, int $registroId): void
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) return;

        $reporteModel = new ReporteModel();
        $nitCliente   = $cliente['nit_cliente'];

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $ext      = pathinfo($archivoPath, PATHINFO_EXTENSION);
        $fileName = 'planilla_ss_' . $registroId . '_' . date('Ymd_His') . '.' . $ext;
        $destPath = $destDir . '/' . $fileName;

        if (file_exists(FCPATH . $archivoPath)) {
            copy(FCPATH . $archivoPath, $destPath);
        }

        $tag = 'planilla_ss_id:' . $registroId;
        $existente = $reporteModel
            ->where('id_cliente', $idCliente)
            ->where('id_report_type', 6)
            ->where('id_detailreport', self::DETAIL_REPORT)
            ->like('observaciones', $tag)
            ->first();

        $data = [
            'titulo_reporte'  => 'PLANILLA SEGURIDAD SOCIAL - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $periodo,
            'id_detailreport' => self::DETAIL_REPORT,
            'id_report_type'  => 6,
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
