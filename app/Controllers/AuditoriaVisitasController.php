<?php

namespace App\Controllers;

use App\Models\CicloVisitaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Libraries\NotificadorVisita;

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

    /**
     * Enviar recordatorio de visita manualmente (AJAX)
     */
    public function enviarRecordatorio()
    {
        $json = $this->request->getJSON(true);

        $idCliente = (int) ($json['id_cliente'] ?? 0);
        $fecha     = $json['fecha'] ?? date('Y-m-d');
        $destinos  = $json['destinatarios'] ?? [];

        if (!$idCliente) {
            return $this->response->setJSON(['ok' => false, 'mensaje' => 'Cliente no especificado.']);
        }
        if (empty($destinos)) {
            return $this->response->setJSON(['ok' => false, 'mensaje' => 'Seleccione al menos un destinatario.']);
        }

        // Armar array de destinatarios TO
        $to = [];
        foreach ($destinos as $d) {
            $email = trim($d['email'] ?? '');
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $to[] = ['email' => $email, 'nombre' => $d['nombre'] ?? ''];
            }
        }

        if (empty($to)) {
            return $this->response->setJSON(['ok' => false, 'mensaje' => 'Ningún email válido para enviar.']);
        }

        $notificador = new NotificadorVisita();
        $resultado = $notificador->enviarManual($idCliente, $fecha, [
            'to' => $to,
            'cc' => [['email' => 'diana.cuestas@cycloidtalent.com', 'nombre' => 'Diana Cuestas']],
        ]);

        return $this->response->setJSON($resultado);
    }
}
