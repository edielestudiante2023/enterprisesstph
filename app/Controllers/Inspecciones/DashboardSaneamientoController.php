<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\KpiLimpiezaModel;
use App\Models\KpiResiduosModel;
use App\Models\KpiPlagasModel;
use App\Models\KpiAguaPotableModel;
use App\Models\ClientModel;

class DashboardSaneamientoController extends BaseController
{
    /**
     * Mapa maestro de los 8 indicadores del Plan de Saneamiento.
     * Cada entrada vincula: programa, tabla-modelo, indicador, meta.
     */
    private const INDICADORES = [
        [
            'programa'   => 'Limpieza y Desinfección',
            'indicador'  => 'Cumplimiento de actividades de limpieza y desinfección',
            'meta'       => 95,
            'meta_texto' => '≥ 95%',
            'model'      => 'limpieza',
        ],
        [
            'programa'   => 'Limpieza y Desinfección',
            'indicador'  => 'Estado de los elementos de limpieza',
            'meta'       => 90,
            'meta_texto' => '≥ 90%',
            'model'      => 'limpieza',
        ],
        [
            'programa'   => 'Manejo Integral de Residuos Sólidos',
            'indicador'  => 'Cumplimiento de condiciones higiénico–sanitarias del cuarto de residuos',
            'meta'       => 100,
            'meta_texto' => '100%',
            'model'      => 'residuos',
        ],
        [
            'programa'   => 'Manejo Integral de Residuos Sólidos',
            'indicador'  => 'Cumplimiento en separación adecuada de residuos sólidos',
            'meta'       => 90,
            'meta_texto' => '≥ 90%',
            'model'      => 'residuos',
        ],
        [
            'programa'   => 'Control Integrado de Plagas',
            'indicador'  => 'Ejecución de fumigación semestral',
            'meta'       => 100,
            'meta_texto' => '100%',
            'model'      => 'plagas',
        ],
        [
            'programa'   => 'Control Integrado de Plagas',
            'indicador'  => 'Ejecución de desratización semestral',
            'meta'       => 100,
            'meta_texto' => '100%',
            'model'      => 'plagas',
        ],
        [
            'programa'   => 'Abastecimiento de Agua Potable',
            'indicador'  => 'Continuidad del servicio de agua potable en situaciones de suspensión',
            'meta'       => 100,
            'meta_texto' => '100%',
            'model'      => 'agua',
        ],
        [
            'programa'   => 'Abastecimiento de Agua Potable',
            'indicador'  => 'Ejecución de limpieza y desinfección de tanques de agua potable (semestral)',
            'meta'       => 100,
            'meta_texto' => '100%',
            'model'      => 'agua',
        ],
    ];

    public function index($idCliente = null)
    {
        // Acepta tanto segmento de URL como query param ?id=X
        if (!$idCliente) {
            $idCliente = $this->request->getGet('id');
        }

        $clientes = (new ClientModel())->orderBy('nombre_cliente', 'ASC')->findAll();

        $resultados = null;
        $clienteSeleccionado = null;

        if ($idCliente) {
            $clienteSeleccionado = (new ClientModel())->find($idCliente);
            if ($clienteSeleccionado) {
                $resultados = $this->consolidar((int) $idCliente);
            }
        }

        return view('inspecciones/layout_pwa', [
            'title'   => 'Dashboard Saneamiento',
            'content' => view('inspecciones/dashboard-saneamiento/index', [
                'clientes'             => $clientes,
                'clienteSeleccionado'  => $clienteSeleccionado,
                'resultados'           => $resultados,
                'idCliente'            => $idCliente,
            ]),
        ]);
    }

    /**
     * Consolida los 8 indicadores para un cliente dado.
     * Para cada indicador busca el último registro completo.
     */
    public static function consolidar(int $idCliente): array
    {
        $models = [
            'limpieza' => new KpiLimpiezaModel(),
            'residuos' => new KpiResiduosModel(),
            'plagas'   => new KpiPlagasModel(),
            'agua'     => new KpiAguaPotableModel(),
        ];

        $resultados = [];

        foreach (self::INDICADORES as $def) {
            $model = $models[$def['model']];
            $ultimo = $model
                ->where('id_cliente', $idCliente)
                ->where('estado', 'completo')
                ->where('indicador', $def['indicador'])
                ->orderBy('fecha_inspeccion', 'DESC')
                ->first();

            $resultados[] = [
                'programa'      => $def['programa'],
                'indicador'     => $def['indicador'],
                'meta_texto'    => $def['meta_texto'],
                'meta'          => $def['meta'],
                'cumplimiento'  => $ultimo ? (float) $ultimo['cumplimiento'] : null,
                'calificacion'  => $ultimo['calificacion_cualitativa'] ?? null,
                'fecha'         => $ultimo ? $ultimo['fecha_inspeccion'] : null,
                'observaciones' => $ultimo['observaciones'] ?? null,
            ];
        }

        return $resultados;
    }
}
