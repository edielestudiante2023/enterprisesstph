<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Libraries\InspeccionTypes;
use App\Models\ClientModel;
use App\Models\InspeccionNoAplicaModel;

class MatrizInspeccionesController extends BaseController
{
    protected ClientModel $clienteModel;
    protected InspeccionNoAplicaModel $noAplicaModel;

    public function __construct()
    {
        $this->clienteModel = new ClientModel();
        $this->noAplicaModel = new InspeccionNoAplicaModel();
    }

    /**
     * Pantalla 1: selector de cliente (Select2).
     */
    public function index()
    {
        $clientes = $this->clienteModel
            ->select('id_cliente, nombre_cliente, nit_cliente')
            ->orderBy('nombre_cliente', 'ASC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/matriz/selector', [
                'clientes' => $clientes,
                'anio'     => (int) date('Y'),
            ]),
            'title'   => 'Matriz de Inspecciones',
        ]);
    }

    /**
     * Pantalla 2: detalle de inspecciones de un cliente en un año.
     */
    public function detalle(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to(base_url('inspecciones/matriz'))
                ->with('error', 'Cliente no encontrado.');
        }

        $anio = (int) ($this->request->getGet('anio') ?: date('Y'));
        $tipos = InspeccionTypes::all();
        $noAplica = $this->noAplicaModel->getByCliente($idCliente);

        $db = \Config\Database::connect();
        $filas = [];

        foreach ($tipos as $tipo) {
            $inspecciones = [];
            $ultima = null;
            $total = 0;

            $estadoCol = array_key_exists('estado_col', $tipo) ? $tipo['estado_col'] : 'estado';
            $estadoValue = $tipo['estado_value'] ?? 'completo';
            $extraWhere = $tipo['extra_where'] ?? [];

            if ($db->tableExists($tipo['table'])) {
                $fields = $db->getFieldNames($tipo['table']);
                $dateCol = in_array($tipo['date_col'], $fields, true) ? $tipo['date_col'] : null;
                $pkCol = in_array('id', $fields, true) ? 'id' : ($fields[0] ?? 'id');

                if ($dateCol !== null && in_array('id_cliente', $fields, true)) {
                    $builder = $db->table($tipo['table'])
                        ->select("{$pkCol} AS id, {$dateCol} AS fecha")
                        ->where('id_cliente', $idCliente)
                        ->where("YEAR({$dateCol})", $anio)
                        ->orderBy($dateCol, 'DESC');

                    if ($estadoCol !== null && in_array($estadoCol, $fields, true)) {
                        $builder->where($estadoCol, $estadoValue);
                    }

                    foreach ($extraWhere as $col => $val) {
                        if (in_array($col, $fields, true)) {
                            $builder->where($col, $val);
                        }
                    }

                    $rows = $builder->get()->getResultArray();
                    $inspecciones = $rows;
                    $total = count($rows);
                    $ultima = $rows[0]['fecha'] ?? null;
                }
            }

            $na = $noAplica[$tipo['slug']] ?? null;

            if ($na) {
                $estado = 'no_aplica';
            } elseif ($total > 0) {
                $estado = 'hecha';
            } else {
                $estado = 'pendiente';
            }

            $filas[] = [
                'slug'          => $tipo['slug'],
                'label'         => $tipo['label'],
                'group'         => $tipo['group'] ?? 'Otros',
                'icon'          => $tipo['icon'],
                'list_route'    => $tipo['list_route'],
                'create_route'  => $tipo['create_route'],
                'view_route'    => $tipo['view_route'],
                'inspecciones'  => $inspecciones,
                'ultima'        => $ultima,
                'total'         => $total,
                'estado'        => $estado,
                'no_aplica'     => $na,
            ];
        }

        usort($filas, function ($a, $b) {
            if ($a['estado'] === 'no_aplica' && $b['estado'] !== 'no_aplica') return 1;
            if ($b['estado'] === 'no_aplica' && $a['estado'] !== 'no_aplica') return -1;
            return 0;
        });

        $aniosDisponibles = range((int) date('Y'), (int) date('Y') - 4);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/matriz/detalle', [
                'cliente'          => $cliente,
                'anio'             => $anio,
                'aniosDisponibles' => $aniosDisponibles,
                'filas'            => $filas,
            ]),
            'title'   => 'Matriz - ' . ($cliente['nombre_cliente'] ?? 'Cliente'),
        ]);
    }

    public function marcarNoAplica()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $tipo = trim((string) $this->request->getPost('tipo_inspeccion'));
        $motivo = trim((string) $this->request->getPost('motivo')) ?: null;

        if (!$idCliente || !$tipo || !InspeccionTypes::bySlug($tipo)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $idConsultor = (int) (session('id_consultor') ?: session('id_usuario') ?: 0) ?: null;
        $ok = $this->noAplicaModel->marcar($idCliente, $tipo, $motivo, $idConsultor);

        return $this->response->setJSON(['ok' => (bool) $ok]);
    }

    public function quitarNoAplica()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $tipo = trim((string) $this->request->getPost('tipo_inspeccion'));

        if (!$idCliente || !$tipo) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $ok = $this->noAplicaModel->quitar($idCliente, $tipo);
        return $this->response->setJSON(['ok' => (bool) $ok]);
    }
}
