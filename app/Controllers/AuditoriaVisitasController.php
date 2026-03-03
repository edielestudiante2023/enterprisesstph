<?php

namespace App\Controllers;

use App\Models\CicloVisitaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;

class AuditoriaVisitasController extends BaseController
{
    protected CicloVisitaModel $model;

    public function __construct()
    {
        $this->model = new CicloVisitaModel();
    }

    /**
     * Vista principal — tabla de auditoría de visitas
     */
    public function index()
    {
        $ciclos = $this->model->getAllConJoins();

        // Lista de consultores para el filtro
        $consultantModel = new ConsultantModel();
        $consultores = $consultantModel->orderBy('nombre_consultor')->findAll();

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return view('consultant/auditoria_visitas/list', [
            'ciclos'      => $ciclos,
            'consultores' => $consultores,
            'meses'       => $meses,
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $ciclo = $this->model->find($id);
        if (!$ciclo) {
            return redirect()->to('/consultant/auditoria-visitas')->with('error', 'Registro no encontrado');
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($ciclo['id_cliente']);

        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($ciclo['id_consultor']);

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return view('consultant/auditoria_visitas/edit', [
            'ciclo'     => $ciclo,
            'cliente'   => $cliente,
            'consultor' => $consultor,
            'meses'     => $meses,
        ]);
    }

    /**
     * Actualizar registro
     */
    public function update($id)
    {
        $ciclo = $this->model->find($id);
        if (!$ciclo) {
            return redirect()->to('/consultant/auditoria-visitas')->with('error', 'Registro no encontrado');
        }

        $data = [
            'mes_esperado'    => $this->request->getPost('mes_esperado'),
            'anio'            => $this->request->getPost('anio'),
            'estandar'        => $this->request->getPost('estandar'),
            'fecha_agendada'  => $this->request->getPost('fecha_agendada') ?: null,
            'fecha_acta'      => $this->request->getPost('fecha_acta') ?: null,
            'estatus_agenda'  => $this->request->getPost('estatus_agenda'),
            'estatus_mes'     => $this->request->getPost('estatus_mes'),
            'observaciones'   => $this->request->getPost('observaciones') ?: null,
        ];

        $this->model->update($id, $data);

        return redirect()->to('/consultant/auditoria-visitas')->with('msg', 'Registro actualizado');
    }

    /**
     * Eliminar registro
     */
    public function delete($id)
    {
        $ciclo = $this->model->find($id);
        if (!$ciclo) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
        }

        $this->model->delete($id);

        return $this->response->setJSON(['success' => true, 'message' => 'Registro eliminado']);
    }
}
