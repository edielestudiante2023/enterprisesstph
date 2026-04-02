<?php

namespace App\Controllers;

use App\Models\DashboardItemModel;

class CustomDashboardController extends BaseController
{
    public function index()
    {
        $model = new DashboardItemModel();
        $items = $model->where('activo', 1)
            ->orderBy('categoria', 'ASC')
            ->orderBy('orden', 'ASC')
            ->findAll();

        // Agrupar por categoría
        $grouped = [];
        foreach ($items as $item) {
            $cat = $item['categoria'] ?? 'Sin categoría';
            $grouped[$cat][] = $item;
        }

        // Orden de las categorías en el dashboard admin
        $ordenCategorias = [
            'IA y Asistencia',
            'Operación Diaria',
            'Gestión Clientes',
            'Inspecciones y Auditoría',
            'Cumplimiento y Control',
            'Planeación SST',
            'Dashboards Analíticos',
            'Gestión Documental',
            'Carga Masiva CSV',
            'Usuarios y Accesos',
            'Configuración',
            'Administración',
        ];

        $sortedGroups = [];
        foreach ($ordenCategorias as $cat) {
            if (isset($grouped[$cat])) {
                $sortedGroups[$cat] = $grouped[$cat];
            }
        }
        foreach ($grouped as $cat => $catItems) {
            if (!isset($sortedGroups[$cat])) {
                $sortedGroups[$cat] = $catItems;
            }
        }

        $data['grouped'] = $sortedGroups;

        return view('consultant/admindashboard', $data);
    }
}
