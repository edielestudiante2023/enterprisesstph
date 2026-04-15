<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InventarioChoqueModel;
use App\Models\InventarioChoqueItemModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use Config\InventarioChoqueItems;

class InventarioChoqueController extends BaseController
{
    protected InventarioChoqueModel $invModel;
    protected InventarioChoqueItemModel $itemModel;

    public function __construct()
    {
        $this->invModel  = new InventarioChoqueModel();
        $this->itemModel = new InventarioChoqueItemModel();
    }

    public function list()
    {
        $rows = $this->invModel
            ->select('tbl_inventario_choque.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inventario_choque.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inventario_choque.id_consultor', 'left')
            ->orderBy('tbl_inventario_choque.fecha_captura', 'DESC')
            ->findAll();

        $total = InventarioChoqueItems::total();
        foreach ($rows as &$r) {
            $r['marcados'] = $this->itemModel->contarMarcados((int)$r['id']);
            $r['total_items'] = $total;
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inventario_choque/list', [
                'inventarios' => $rows,
            ]),
            'title' => 'Inventario Fotos de Choque',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inventario_choque/form', [
                'inventario' => null,
                'idCliente'  => $idCliente,
            ]),
            'title' => 'Nuevo Inventario de Choque',
        ]);
    }

    public function store()
    {
        $rules = [
            'id_cliente'    => 'required|integer',
            'fecha_captura' => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = session()->get('user_id');

        $data = [
            'id_cliente'    => $this->request->getPost('id_cliente'),
            'id_consultor'  => $userId,
            'fecha_captura' => $this->request->getPost('fecha_captura'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        $this->invModel->insert($data);
        $id = $this->invModel->getInsertID();

        $rows = InventarioChoqueItems::rowsFor((int)$id);
        if (!empty($rows)) {
            $this->itemModel->insertBatch($rows);
        }

        return redirect()->to('/inspecciones/inventario-choque/view/' . $id)
            ->with('msg', 'Inventario creado. Marca los items segun vayas tomando las fotos.');
    }

    public function view($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/inventario-choque')->with('error', 'Inventario no encontrado');
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inv['id_cliente']);

        $items = $this->itemModel->getByInventario((int)$id);

        $grouped = [];
        foreach ($items as $it) {
            $grouped[$it['categoria']][] = $it;
        }

        $marcados = 0;
        foreach ($items as $it) {
            if ((int)$it['marcado'] === 1) $marcados++;
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inventario_choque/view', [
                'inv'      => $inv,
                'cliente'  => $cliente,
                'grouped'  => $grouped,
                'total'    => count($items),
                'marcados' => $marcados,
            ]),
            'title' => 'Inventario de Choque',
        ]);
    }

    public function edit($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/inventario-choque')->with('error', 'Inventario no encontrado');
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inventario_choque/form', [
                'inventario' => $inv,
                'idCliente'  => $inv['id_cliente'],
            ]),
            'title' => 'Editar Inventario de Choque',
        ]);
    }

    public function update($id)
    {
        $inv = $this->invModel->find($id);
        if (!$inv) {
            return redirect()->to('/inspecciones/inventario-choque')->with('error', 'No encontrado');
        }

        $this->invModel->update($id, [
            'id_cliente'    => $this->request->getPost('id_cliente'),
            'fecha_captura' => $this->request->getPost('fecha_captura'),
            'observaciones' => $this->request->getPost('observaciones'),
        ]);

        return redirect()->to('/inspecciones/inventario-choque/view/' . $id)
            ->with('msg', 'Inventario actualizado');
    }

    public function toggleItem()
    {
        $idItem = (int)$this->request->getPost('id_item');
        $marcado = (int)$this->request->getPost('marcado');

        if (!$idItem) {
            return $this->response->setJSON(['ok' => false, 'error' => 'id_item requerido']);
        }

        $item = $this->itemModel->find($idItem);
        if (!$item) {
            return $this->response->setJSON(['ok' => false, 'error' => 'item no encontrado']);
        }

        $this->itemModel->update($idItem, ['marcado' => $marcado ? 1 : 0]);

        $total = $this->itemModel->where('id_inventario', $item['id_inventario'])->countAllResults();
        $marcados = $this->itemModel->contarMarcados((int)$item['id_inventario']);

        return $this->response->setJSON([
            'ok'       => true,
            'marcados' => $marcados,
            'total'    => $total,
        ]);
    }

    public function delete($id)
    {
        $this->invModel->delete($id);
        return redirect()->to('/inspecciones/inventario-choque')->with('msg', 'Inventario eliminado');
    }
}
