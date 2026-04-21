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
     * Dashboard ejecutivo consolidado: todas las copropiedades con su semaforo.
     */
    public function dashboard()
    {
        $anio = (int) ($this->request->getGet('anio') ?: date('Y'));
        $db = \Config\Database::connect();

        $clientes = $db->table('tbl_clientes')
            ->select('id_cliente, nombre_cliente, nit_cliente')
            ->orderBy('nombre_cliente', 'ASC')
            ->get()
            ->getResultArray();

        $clienteStats = [];
        $totalVerde = 0; $totalAmarillo = 0; $totalRojo = 0; $totalHuerfana = 0;
        $clientesConPta = 0;

        foreach ($clientes as $c) {
            $idCli = (int) $c['id_cliente'];
            $stats = $this->computeClienteStats($idCli, $anio);

            if ($stats['total_pta'] === 0 && $stats['huerfanas'] === 0) {
                continue;
            }
            $clientesConPta++;

            $clienteStats[] = [
                'id_cliente'      => $idCli,
                'nombre_cliente'  => $c['nombre_cliente'],
                'nit_cliente'     => $c['nit_cliente'],
                'verde'           => $stats['verde'],
                'amarillo'        => $stats['amarillo'],
                'rojo'            => $stats['rojo'],
                'huerfanas'       => $stats['huerfanas'],
                'total_pta'       => $stats['total_pta'],
                'pct_cumplimiento'=> $stats['pct_cumplimiento'],
                'pct_atraso'      => $stats['pct_atraso'],
            ];

            $totalVerde    += $stats['verde'];
            $totalAmarillo += $stats['amarillo'];
            $totalRojo     += $stats['rojo'];
            $totalHuerfana += $stats['huerfanas'];
        }

        $totalPta = $totalVerde + $totalAmarillo + $totalRojo;
        $pctGlobalCump = $totalPta > 0 ? round(($totalVerde / $totalPta) * 100, 1) : 0;
        $pctGlobalAtraso = $totalPta > 0 ? round(($totalRojo / $totalPta) * 100, 1) : 0;

        $topRojo = $clienteStats;
        usort($topRojo, fn($a, $b) => ($b['rojo'] - $a['rojo']));
        $topRojo = array_slice($topRojo, 0, 10);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pta_semaforo/dashboard', [
                'anio'              => $anio,
                'aniosDisponibles'  => range((int) date('Y'), (int) date('Y') - 4),
                'clienteStats'      => $clienteStats,
                'clientesConPta'    => $clientesConPta,
                'totalVerde'        => $totalVerde,
                'totalAmarillo'     => $totalAmarillo,
                'totalRojo'         => $totalRojo,
                'totalHuerfana'     => $totalHuerfana,
                'pctGlobalCump'     => $pctGlobalCump,
                'pctGlobalAtraso'   => $pctGlobalAtraso,
                'topRojo'           => $topRojo,
            ]),
            'title' => 'Semáforo PTA',
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
     * Calcula stats rapidas por cliente para el dashboard global.
     * Solo cuenta actividades con al menos un match mapeado (ignora sin_match para no inflar rojos).
     */
    private function computeClienteStats(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $ptas = $db->table('tbl_pta_cliente')
            ->select('id_ptacliente, fecha_propuesta, fecha_cierre')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getResultArray();

        if (empty($ptas)) {
            return ['verde'=>0,'amarillo'=>0,'rojo'=>0,'huerfanas'=>0,'total_pta'=>0,'pct_cumplimiento'=>0,'pct_atraso'=>0];
        }

        $mapByPta = $this->matchModel->getMapByCliente($idCliente);
        $inspCount = $this->getInspeccionesCount($idCliente, $anio);

        $hoy = date('Y-m-d');
        $radar = date('Y-m-d', strtotime('+30 days'));
        $verde = $amar = $rojo = 0;
        $slugsCubiertos = [];

        foreach ($ptas as $p) {
            $idPta = (int) $p['id_ptacliente'];
            $matches = $mapByPta[$idPta] ?? [];
            if (empty($matches)) continue;

            $tiene = false;
            foreach ($matches as $m) {
                if (($inspCount[$m['slug_inspeccion']] ?? 0) > 0) {
                    $tiene = true;
                    $slugsCubiertos[$m['slug_inspeccion']] = true;
                }
            }
            $fechaRef = $p['fecha_cierre'] ?: ($p['fecha_propuesta'] ?? null);
            if ($tiene) $verde++;
            elseif ($fechaRef && $fechaRef < $hoy) $rojo++;
            elseif ($fechaRef && $fechaRef <= $radar) $amar++;
            else $amar++;
        }

        $huerfanas = 0;
        foreach ($inspCount as $slug => $count) {
            if ($count > 0 && !isset($slugsCubiertos[$slug])) $huerfanas++;
        }

        $total = $verde + $amar + $rojo;
        return [
            'verde' => $verde, 'amarillo' => $amar, 'rojo' => $rojo,
            'huerfanas' => $huerfanas, 'total_pta' => $total,
            'pct_cumplimiento' => $total > 0 ? round(($verde / $total) * 100, 1) : 0,
            'pct_atraso'       => $total > 0 ? round(($rojo / $total) * 100, 1) : 0,
        ];
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
