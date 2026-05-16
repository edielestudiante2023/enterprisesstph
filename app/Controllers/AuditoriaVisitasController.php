<?php

namespace App\Controllers;

use App\Models\CicloVisitaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\InformeAvancesModel;
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
        // Sincronizar periodicidad de cada ciclo con la frecuencia_visitas
        // del contrato más reciente del cliente (idempotente: solo toca si difiere).
        $this->model->sincronizarPeriodicidadDesdeContratos();

        $ciclos = $this->model->getAllConJoins();

        // Cruce con informes de avance: ¿existe un informe completo de ese cliente
        // cuyo mes/año de fecha_hasta (fin del periodo) coincida con el mes/año de
        // la fecha_acta del ciclo? → 'cumple' / 'no_cumple' / 'sin_acta'.
        $informeModel = new InformeAvancesModel();
        $informes = $informeModel
            ->select('id_cliente, fecha_hasta')
            ->where('estado', 'completo')
            ->where('fecha_hasta IS NOT NULL')
            ->findAll();

        $informesIndex = [];
        foreach ($informes as $inf) {
            $ts = strtotime($inf['fecha_hasta']);
            if ($ts === false) {
                continue;
            }
            $informesIndex[$inf['id_cliente'] . '-' . date('Y-n', $ts)] = true;
        }

        foreach ($ciclos as &$c) {
            if (empty($c['fecha_acta'])) {
                $c['cumple_informe'] = 'sin_acta';
                continue;
            }
            $ts = strtotime($c['fecha_acta']);
            $key = $c['id_cliente'] . '-' . date('Y-n', $ts);
            $c['cumple_informe'] = isset($informesIndex[$key]) ? 'cumple' : 'no_cumple';
        }
        unset($c);

        // Cargar TODAS las actas de visita completas (NO borrador) agrupadas por
        // cliente, ordenadas DESC. Una sola query para luego buscar in-memory la
        // última acta anterior al punto de referencia de cada ciclo.
        // Trae también el id para poder enlazar al documento (ícono ojito en la vista).
        $db = \Config\Database::connect();
        $actasRows = $db->table('tbl_acta_visita')
            ->select('id, id_cliente, fecha_visita')
            ->where('estado', 'completo')
            ->where('fecha_visita IS NOT NULL', null, false)
            ->orderBy('fecha_visita', 'DESC')
            ->get()->getResultArray();

        $actasPorCliente = [];
        foreach ($actasRows as $a) {
            $idC = (int) $a['id_cliente'];
            $actasPorCliente[$idC][] = ['id' => (int) $a['id'], 'fecha' => $a['fecha_visita']];
        }

        // Para cada ciclo: ref = fecha_acta si existe, sino hoy. Última acta anterior
        // = primer elemento (ya está DESC) estrictamente menor a ref.
        $hoy = date('Y-m-d');
        foreach ($ciclos as &$c) {
            $ref = !empty($c['fecha_acta']) ? $c['fecha_acta'] : $hoy;
            $ultimaFecha = null;
            $ultimaId    = null;
            foreach (($actasPorCliente[(int) $c['id_cliente']] ?? []) as $a) {
                if ($a['fecha'] < $ref) { $ultimaFecha = $a['fecha']; $ultimaId = $a['id']; break; }
            }
            $c['ultima_acta_anterior']    = $ultimaFecha;
            $c['ultima_acta_anterior_id'] = $ultimaId;
        }
        unset($c);

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
        $consultor = !empty($cliente['id_consultor'])
            ? $consultantModel->find($cliente['id_consultor'])
            : null;

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
