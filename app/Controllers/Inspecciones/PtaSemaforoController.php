<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Libraries\InspeccionTypes;
use App\Models\ClientModel;
use App\Models\PtaInspeccionMatchModel;
use App\Models\InspeccionNoAplicaModel;

class PtaSemaforoController extends BaseController
{
    protected ClientModel $clienteModel;
    protected PtaInspeccionMatchModel $matchModel;
    protected InspeccionNoAplicaModel $noAplicaModel;

    public function __construct()
    {
        $this->clienteModel = new ClientModel();
        $this->matchModel = new PtaInspeccionMatchModel();
        $this->noAplicaModel = new InspeccionNoAplicaModel();
    }

    /**
     * Pantalla 1: selector de cliente (Select2) + filtro de anio.
     * Evita la carga de analizar 56 clientes x 43 tipos (~2400 queries) en una sola vista.
     */
    public function index()
    {
        $clientes = $this->clienteModel
            ->select('id_cliente, nombre_cliente, nit_cliente')
            ->orderBy('nombre_cliente', 'ASC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pta_semaforo/selector', [
                'clientes' => $clientes,
                'anio'     => (int) date('Y'),
            ]),
            'title'   => 'Semáforo PTA',
        ]);
    }

    /**
     * Drill-down de un cliente: todas sus actividades PTA con semaforo + inspecciones cruzadas.
     */
    public function cliente(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to(base_url('inspecciones/pta-semaforo'))->with('error', 'Cliente no encontrado.');
        }

        $anio = (int) ($this->request->getGet('anio') ?: date('Y'));
        $db = \Config\Database::connect();

        $ptas = $db->table('tbl_pta_cliente')
            ->select('id_ptacliente, phva_plandetrabajo, numeral_plandetrabajo, actividad_plandetrabajo, responsable_definido_paralaactividad, fecha_propuesta, fecha_cierre, estado_actividad, porcentaje_avance')
            ->where('id_cliente', $idCliente)
            ->orderBy('numeral_plandetrabajo', 'ASC')
            ->orderBy('id_ptacliente', 'ASC')
            ->get()
            ->getResultArray();

        $mapByPta = $this->matchModel->getMapByCliente($idCliente);
        $inspCount = $this->getInspeccionesCount($idCliente, $anio);

        $slugsCubiertos = [];
        $hoy = date('Y-m-d');
        $radarLimite = date('Y-m-d', strtotime('+30 days'));

        $filas = [];
        foreach ($ptas as $p) {
            $idPta = (int) $p['id_ptacliente'];
            $matches = $mapByPta[$idPta] ?? [];

            $inspeccionesVinculadas = 0;
            $slugsMatched = [];
            foreach ($matches as $m) {
                $slug = $m['slug_inspeccion'];
                $slugsMatched[] = $slug;
                $inspeccionesVinculadas += $inspCount[$slug] ?? 0;
                if (($inspCount[$slug] ?? 0) > 0) {
                    $slugsCubiertos[$slug] = true;
                }
            }

            $fechaCierre = $p['fecha_cierre'] ?? null;
            $fechaPropuesta = $p['fecha_propuesta'] ?? null;
            $fechaRef = $fechaCierre ?: $fechaPropuesta;

            if (empty($matches)) {
                $semaforo = 'sin_match';
            } elseif ($inspeccionesVinculadas > 0) {
                $semaforo = 'verde';
            } elseif ($fechaRef && $fechaRef < $hoy) {
                $semaforo = 'rojo';
            } elseif ($fechaRef && $fechaRef <= $radarLimite) {
                $semaforo = 'amarillo';
            } else {
                $semaforo = 'amarillo';
            }

            $filas[] = [
                'pta'           => $p,
                'matches'       => $matches,
                'slugs'         => $slugsMatched,
                'inspecciones'  => $inspeccionesVinculadas,
                'semaforo'      => $semaforo,
            ];
        }

        $huerfanas = [];
        foreach ($inspCount as $slug => $count) {
            if ($count > 0 && !isset($slugsCubiertos[$slug])) {
                $t = InspeccionTypes::bySlug($slug);
                if ($t) {
                    $huerfanas[] = [
                        'slug'  => $slug,
                        'label' => $t['label'],
                        'group' => $t['group'] ?? 'Otros',
                        'icon'  => $t['icon'],
                        'count' => $count,
                    ];
                }
            }
        }

        $kpi = [
            'total'     => count($filas),
            'verde'     => count(array_filter($filas, fn($f) => $f['semaforo'] === 'verde')),
            'amarillo'  => count(array_filter($filas, fn($f) => $f['semaforo'] === 'amarillo')),
            'rojo'      => count(array_filter($filas, fn($f) => $f['semaforo'] === 'rojo')),
            'sin_match' => count(array_filter($filas, fn($f) => $f['semaforo'] === 'sin_match')),
            'huerfanas' => count($huerfanas),
        ];
        $aplicables = $kpi['verde'] + $kpi['amarillo'] + $kpi['rojo'];
        $kpi['pct_cumplimiento'] = $aplicables > 0 ? round(($kpi['verde'] / $aplicables) * 100, 1) : 0;

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pta_semaforo/cliente', [
                'cliente'          => $cliente,
                'anio'             => $anio,
                'aniosDisponibles' => range((int) date('Y'), (int) date('Y') - 4),
                'filas'            => $filas,
                'huerfanas'        => $huerfanas,
                'kpi'              => $kpi,
                'catalog'          => InspeccionTypes::all(),
            ]),
            'title' => 'Semáforo PTA - ' . ($cliente['nombre_cliente'] ?? ''),
        ]);
    }

    public function agregarMatch()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $idPta = (int) $this->request->getPost('id_ptacliente');
        $slug = trim((string) $this->request->getPost('slug_inspeccion'));

        if (!$idCliente || !$idPta || !InspeccionTypes::bySlug($slug)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $ok = $this->matchModel->upsert([
            'id_cliente'      => $idCliente,
            'id_ptacliente'   => $idPta,
            'slug_inspeccion' => $slug,
            'score'           => 1.000,
            'method'          => 'manual',
            'reasoning'       => 'Añadido manualmente por el consultor.',
            'ai_model'        => null,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['ok' => (bool) $ok]);
    }

    public function quitarMatch()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $idPta = (int) $this->request->getPost('id_ptacliente');
        $slug = trim((string) $this->request->getPost('slug_inspeccion'));

        if (!$idCliente || !$idPta || !$slug) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $ok = $this->matchModel->deleteMatch($idCliente, $idPta, $slug);
        return $this->response->setJSON(['ok' => (bool) $ok]);
    }

    /**
     * Devuelve [slug => count_inspecciones_del_anio] para un cliente.
     * Respeta estado_col, estado_value, extra_where, y descarta No Aplica.
     */
    private function getInspeccionesCount(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();
        $na = $this->noAplicaModel->getByCliente($idCliente);
        $out = [];

        foreach (InspeccionTypes::all() as $tipo) {
            if (isset($na[$tipo['slug']])) {
                $out[$tipo['slug']] = 0;
                continue;
            }
            if (!$db->tableExists($tipo['table'])) {
                $out[$tipo['slug']] = 0;
                continue;
            }
            $fields = $db->getFieldNames($tipo['table']);
            $dateCol = in_array($tipo['date_col'], $fields, true) ? $tipo['date_col'] : null;
            if ($dateCol === null || !in_array('id_cliente', $fields, true)) {
                $out[$tipo['slug']] = 0;
                continue;
            }

            $builder = $db->table($tipo['table'])
                ->where('id_cliente', $idCliente)
                ->where("YEAR({$dateCol})", $anio);

            $estadoCol = array_key_exists('estado_col', $tipo) ? $tipo['estado_col'] : 'estado';
            $estadoValue = $tipo['estado_value'] ?? 'completo';
            if ($estadoCol !== null && in_array($estadoCol, $fields, true)) {
                $builder->where($estadoCol, $estadoValue);
            }
            foreach ($tipo['extra_where'] ?? [] as $col => $val) {
                if (in_array($col, $fields, true)) {
                    $builder->where($col, $val);
                }
            }
            $out[$tipo['slug']] = (int) $builder->countAllResults();
        }
        return $out;
    }
}
