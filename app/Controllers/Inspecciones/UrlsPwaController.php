<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\UrlModel;

class UrlsPwaController extends BaseController
{
    protected UrlModel $model;

    public function __construct()
    {
        $this->model = new UrlModel();
    }

    /**
     * Listado de URLs agrupadas por tipo
     */
    public function list()
    {
        $grouped = $this->model->getGroupedByTipo();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/urls/list', [
                'title'   => 'Accesos Rápidos',
                'grouped' => $grouped,
            ]),
            'title' => 'Accesos Rápidos',
        ]);
    }

    /**
     * Formulario nuevo URL
     */
    public function create()
    {
        $tipos = $this->model->getTipos();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/urls/form', [
                'title' => 'Nuevo Acceso Rápido',
                'url'   => null,
                'tipos' => array_column($tipos, 'tipo'),
            ]),
            'title' => 'Nuevo Acceso Rápido',
        ]);
    }

    /**
     * Guardar nuevo URL
     */
    public function store()
    {
        if (!$this->validate([
            'tipo'   => 'required|max_length[100]',
            'nombre' => 'required|max_length[255]',
            'url'    => 'required|max_length[1000]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tipo = $this->request->getPost('tipo');
        // Si eligió "OTRO", usar el campo tipo_nuevo
        if ($tipo === '__OTRO__') {
            $tipo = strtoupper(trim($this->request->getPost('tipo_nuevo') ?? ''));
            if (empty($tipo)) {
                return redirect()->back()->withInput()->with('error', 'Debe especificar el tipo.');
            }
        }

        $this->model->insert([
            'tipo'   => $tipo,
            'nombre' => $this->request->getPost('nombre'),
            'url'    => $this->request->getPost('url'),
        ]);

        return redirect()->to('/inspecciones/urls')->with('msg', 'Acceso rápido creado.');
    }

    /**
     * Formulario editar URL
     */
    public function edit($id)
    {
        $url = $this->model->find($id);
        if (!$url) {
            return redirect()->to('/inspecciones/urls')->with('error', 'No encontrado.');
        }

        $tipos = $this->model->getTipos();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/urls/form', [
                'title' => 'Editar Acceso Rápido',
                'url'   => $url,
                'tipos' => array_column($tipos, 'tipo'),
            ]),
            'title' => 'Editar Acceso Rápido',
        ]);
    }

    /**
     * Actualizar URL
     */
    public function update($id)
    {
        $url = $this->model->find($id);
        if (!$url) {
            return redirect()->to('/inspecciones/urls')->with('error', 'No encontrado.');
        }

        if (!$this->validate([
            'tipo'   => 'required|max_length[100]',
            'nombre' => 'required|max_length[255]',
            'url'    => 'required|max_length[1000]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tipo = $this->request->getPost('tipo');
        if ($tipo === '__OTRO__') {
            $tipo = strtoupper(trim($this->request->getPost('tipo_nuevo') ?? ''));
            if (empty($tipo)) {
                return redirect()->back()->withInput()->with('error', 'Debe especificar el tipo.');
            }
        }

        $this->model->update($id, [
            'tipo'   => $tipo,
            'nombre' => $this->request->getPost('nombre'),
            'url'    => $this->request->getPost('url'),
        ]);

        return redirect()->to('/inspecciones/urls')->with('msg', 'Acceso rápido actualizado.');
    }

    /**
     * Eliminar URL
     */
    public function delete($id)
    {
        $url = $this->model->find($id);
        if (!$url) {
            return redirect()->to('/inspecciones/urls')->with('error', 'No encontrado.');
        }

        $this->model->delete($id);

        return redirect()->to('/inspecciones/urls')->with('msg', 'Acceso rápido eliminado.');
    }
}
