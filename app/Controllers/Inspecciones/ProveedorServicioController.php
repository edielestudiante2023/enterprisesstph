<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ProveedorServicioModel;
use App\Models\ClientModel;

class ProveedorServicioController extends BaseController
{
    public function list()
    {
        $model = new ProveedorServicioModel();
        $registros = $model
            ->select('tbl_proveedor_servicio.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_proveedor_servicio.id_cliente', 'left')
            ->orderBy('tbl_proveedor_servicio.estado', 'ASC')
            ->orderBy('tbl_proveedor_servicio.created_at', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'title'   => 'Proveedores de Servicio',
            'content' => view('inspecciones/proveedor-servicio/list', ['registros' => $registros]),
        ]);
    }

    public function create()
    {
        return view('inspecciones/layout_pwa', [
            'title'   => 'Nuevo Proveedor',
            'content' => view('inspecciones/proveedor-servicio/form', ['registro' => null]),
        ]);
    }

    public function store()
    {
        $model = new ProveedorServicioModel();

        $tipo = $this->request->getPost('tipo_servicio');
        $tipoOtro = $tipo === 'Otro' ? trim($this->request->getPost('tipo_servicio_otro') ?? '') : null;

        if (!$this->request->getPost('id_cliente') || !$this->request->getPost('razon_social') || !$tipo) {
            session()->setFlashdata('error', 'Cliente, razón social y tipo de servicio son obligatorios.');
            return redirect()->back()->withInput();
        }

        $model->insert([
            'id_cliente'             => $this->request->getPost('id_cliente'),
            'tipo_servicio'          => $tipo,
            'tipo_servicio_otro'     => $tipoOtro,
            'estado'                 => 'activo',
            'razon_social'           => $this->request->getPost('razon_social'),
            'nit'                    => $this->request->getPost('nit'),
            'email_empresa'          => $this->request->getPost('email_empresa'),
            'telefono_empresa'       => $this->request->getPost('telefono_empresa'),
            'nombre_responsable_sst' => $this->request->getPost('nombre_responsable_sst'),
            'email_responsable_sst'  => $this->request->getPost('email_responsable_sst'),
            'cargo_responsable_sst'  => $this->request->getPost('cargo_responsable_sst'),
            'telefono_responsable_sst' => $this->request->getPost('telefono_responsable_sst'),
            'id_consultor'           => session()->get('user_id'),
        ]);

        session()->setFlashdata('msg', 'Proveedor registrado.');
        return redirect()->to('/inspecciones/proveedor-servicio');
    }

    public function edit(int $id)
    {
        $model = new ProveedorServicioModel();
        $registro = $model
            ->select('tbl_proveedor_servicio.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_proveedor_servicio.id_cliente', 'left')
            ->find($id);
        if (!$registro) {
            return redirect()->to('/inspecciones/proveedor-servicio')->with('error', 'No encontrado.');
        }

        return view('inspecciones/layout_pwa', [
            'title'   => 'Editar Proveedor',
            'content' => view('inspecciones/proveedor-servicio/form', ['registro' => $registro]),
        ]);
    }

    public function update(int $id)
    {
        $model = new ProveedorServicioModel();
        $registro = $model->find($id);
        if (!$registro) {
            return redirect()->to('/inspecciones/proveedor-servicio')->with('error', 'No encontrado.');
        }

        $tipo = $this->request->getPost('tipo_servicio');
        $tipoOtro = $tipo === 'Otro' ? trim($this->request->getPost('tipo_servicio_otro') ?? '') : null;

        $model->update($id, [
            'id_cliente'             => $this->request->getPost('id_cliente'),
            'tipo_servicio'          => $tipo,
            'tipo_servicio_otro'     => $tipoOtro,
            'estado'                 => $this->request->getPost('estado'),
            'razon_social'           => $this->request->getPost('razon_social'),
            'nit'                    => $this->request->getPost('nit'),
            'email_empresa'          => $this->request->getPost('email_empresa'),
            'telefono_empresa'       => $this->request->getPost('telefono_empresa'),
            'nombre_responsable_sst' => $this->request->getPost('nombre_responsable_sst'),
            'email_responsable_sst'  => $this->request->getPost('email_responsable_sst'),
            'cargo_responsable_sst'  => $this->request->getPost('cargo_responsable_sst'),
            'telefono_responsable_sst' => $this->request->getPost('telefono_responsable_sst'),
        ]);

        session()->setFlashdata('msg', 'Proveedor actualizado.');
        return redirect()->to('/inspecciones/proveedor-servicio');
    }

    public function toggleEstado(int $id)
    {
        $model = new ProveedorServicioModel();
        $registro = $model->find($id);
        if (!$registro) {
            return $this->response->setJSON(['error' => 'No encontrado'])->setStatusCode(404);
        }

        $nuevoEstado = $registro['estado'] === 'activo' ? 'inactivo' : 'activo';
        $model->update($id, ['estado' => $nuevoEstado]);

        return $this->response->setJSON(['success' => true, 'estado' => $nuevoEstado]);
    }

    public function delete(int $id)
    {
        $model = new ProveedorServicioModel();
        $registro = $model->find($id);
        if (!$registro) {
            session()->setFlashdata('error', 'No encontrado.');
            return redirect()->to('/inspecciones/proveedor-servicio');
        }

        $model->delete($id);
        session()->setFlashdata('msg', 'Proveedor eliminado.');
        return redirect()->to('/inspecciones/proveedor-servicio');
    }
}
