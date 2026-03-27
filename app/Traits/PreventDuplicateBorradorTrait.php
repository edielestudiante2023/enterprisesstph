<?php

namespace App\Traits;

/**
 * Previene duplicados de borradores en inspecciones.
 * Si ya existe un borrador para el mismo cliente + fecha + consultor,
 * redirige al existente en vez de crear uno nuevo.
 *
 * Uso en store():
 *   $existing = $this->reuseExistingBorrador($this->model, 'fecha_inspeccion', '/inspecciones/tipo/edit/');
 *   if ($existing) return $existing;
 */
trait PreventDuplicateBorradorTrait
{
    /**
     * Busca un borrador existente para el mismo cliente+fecha+consultor.
     * Si existe, retorna redirect al edit. Si no, retorna null.
     *
     * @param object $model       Modelo CI4 con tabla y métodos find/where
     * @param string $dateField   Nombre del campo fecha en la tabla (ej: 'fecha_inspeccion')
     * @param string $editUrlBase URL base para editar (ej: '/inspecciones/locativa/edit/')
     * @return \CodeIgniter\HTTP\RedirectResponse|array|null
     *         - RedirectResponse para submit normal
     *         - array JSON para autosave
     *         - null si no hay duplicado
     */
    protected function reuseExistingBorrador($model, string $dateField, string $editUrlBase)
    {
        $idCliente  = $this->request->getPost('id_cliente');
        $fecha      = $this->request->getPost($dateField);
        $idConsultor = session()->get('user_id');

        if (!$idCliente || !$fecha) {
            return null;
        }

        $existing = $model->where('id_cliente', $idCliente)
            ->where($dateField, $fecha)
            ->where('id_consultor', $idConsultor)
            ->where('estado', 'borrador')
            ->first();

        if (!$existing) {
            return null;
        }

        $existingId = $existing['id'] ?? $existing['id_inspeccion'] ?? null;
        if (!$existingId) {
            // Intentar con la primary key del modelo
            $pk = $model->primaryKey ?? 'id';
            $existingId = $existing[$pk] ?? null;
        }

        if (!$existingId) {
            return null;
        }

        // Si es autosave, retornar JSON con el ID existente
        if ($this->isAutosaveRequest()) {
            return $this->response->setJSON([
                'success'  => true,
                'id'       => $existingId,
                'saved_at' => date('H:i:s'),
                'reused'   => true,
            ]);
        }

        // Submit normal: redirigir al borrador existente
        return redirect()->to($editUrlBase . $existingId)
            ->with('msg', 'Ya existe un borrador para este cliente y fecha. Continuando edición.');
    }
}
