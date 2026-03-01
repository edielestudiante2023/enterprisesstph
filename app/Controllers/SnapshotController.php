<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class SnapshotController extends Controller
{
    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        return view('admin/snapshots/index');
    }

    public function ejecutar()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        $db = \Config\Database::connect();

        try {
            $sql1 = "INSERT INTO historial_resumen_estandares (
                id_cliente, nombre_cliente, estandares, nombre_consultor, correo_consultor,
                total_valor, total_puntaje, porcentaje_cumplimiento, fecha_extraccion
            )
            SELECT
                r.id_cliente, r.nombre_cliente, r.estandares, r.nombre_consultor, r.correo_consultor,
                r.total_valor, r.total_puntaje, r.porcentaje_cumplimiento, NOW()
            FROM resumen_estandares_cliente AS r
            ON DUPLICATE KEY UPDATE
                total_valor = r.total_valor,
                total_puntaje = r.total_puntaje,
                porcentaje_cumplimiento = r.porcentaje_cumplimiento,
                nombre_cliente = r.nombre_cliente,
                nombre_consultor = r.nombre_consultor,
                correo_consultor = r.correo_consultor,
                estandares = r.estandares,
                fecha_extraccion = NOW()";

            $db->query($sql1);
            $affected1 = $db->affectedRows();

            $sql2 = "INSERT INTO historial_resumen_plan_trabajo (
                id_cliente, nombre_cliente, estandares, nombre_consultor, correo_consultor,
                total_actividades, actividades_abiertas, porcentaje_abiertas, fecha_extraccion
            )
            SELECT
                r.id_cliente, r.nombre_cliente, r.estandares, r.nombre_consultor, r.correo_consultor,
                r.total_actividades, r.actividades_abiertas, r.porcentaje_abiertas, NOW()
            FROM resumen_mensual_plan_trabajo AS r
            ON DUPLICATE KEY UPDATE
                total_actividades = r.total_actividades,
                actividades_abiertas = r.actividades_abiertas,
                porcentaje_abiertas = r.porcentaje_abiertas,
                nombre_cliente = r.nombre_cliente,
                nombre_consultor = r.nombre_consultor,
                correo_consultor = r.correo_consultor,
                estandares = r.estandares,
                fecha_extraccion = NOW()";

            $db->query($sql2);
            $affected2 = $db->affectedRows();

            return $this->response->setJSON([
                'success'            => true,
                'estandares_rows'    => $affected1,
                'plan_trabajo_rows'  => $affected2,
                'timestamp'          => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}
