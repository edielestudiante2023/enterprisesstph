<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Libraries\InspeccionTypes;
use App\Models\ClientModel;
use App\Models\InspeccionNoAplicaModel;
use App\Models\PtaInspeccionMatchModel;

class MatrizInspeccionesController extends BaseController
{
    protected ClientModel $clienteModel;
    protected InspeccionNoAplicaModel $noAplicaModel;
    protected PtaInspeccionMatchModel $matchModel;

    public function __construct()
    {
        $this->clienteModel = new ClientModel();
        $this->noAplicaModel = new InspeccionNoAplicaModel();
        $this->matchModel = new PtaInspeccionMatchModel();
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

        // Precargar matches del cliente cuyo PTA cae en el anio seleccionado
        $matchesBySlug = [];
        if ($db->tableExists('tbl_pta_inspeccion_match')) {
            $matches = $db->table('tbl_pta_inspeccion_match m')
                ->select('m.slug_inspeccion, m.id_ptacliente, m.method, m.score, p.fecha_propuesta, p.fecha_cierre, p.numeral_plandetrabajo, p.actividad_plandetrabajo, p.estado_actividad')
                ->join('tbl_pta_cliente p', 'p.id_ptacliente = m.id_ptacliente', 'inner')
                ->where('m.id_cliente', $idCliente)
                ->where('YEAR(p.fecha_propuesta)', $anio)
                ->orderBy('p.fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($matches as $m) {
                $matchesBySlug[$m['slug_inspeccion']][] = $m;
            }
        }

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

            // Analizar PTAs vinculados para determinar 'atrasada' y proxima fecha
            $ptaVincs = $matchesBySlug[$tipo['slug']] ?? [];
            $hoy = date('Y-m-d');
            $proxima = null; $ultimaVencida = null;
            $hayFuturas = false; $hayPasadas = false;
            foreach ($ptaVincs as $v) {
                $fp = $v['fecha_propuesta'] ?? null;
                if (!$fp) continue;
                if ($fp > $hoy) {
                    $hayFuturas = true;
                    if ($proxima === null || $fp < $proxima) $proxima = $fp;
                } else {
                    $hayPasadas = true;
                    if ($ultimaVencida === null || $fp > $ultimaVencida) $ultimaVencida = $fp;
                }
            }

            if ($na) {
                $estado = 'no_aplica';
            } elseif ($total > 0) {
                $estado = 'hecha';
            } elseif ($hayPasadas && !$hayFuturas) {
                $estado = 'atrasada';
            } else {
                $estado = 'pendiente';
            }

            $filas[] = [
                'slug'           => $tipo['slug'],
                'label'          => $tipo['label'],
                'group'          => $tipo['group'] ?? 'Otros',
                'icon'           => $tipo['icon'],
                'list_route'     => $tipo['list_route'],
                'create_route'   => $tipo['create_route'],
                'view_route'     => $tipo['view_route'],
                'inspecciones'   => $inspecciones,
                'ultima'         => $ultima,
                'total'          => $total,
                'estado'         => $estado,
                'no_aplica'      => $na,
                'pta_vinculados' => $ptaVincs,
                'proxima_planeada' => $proxima,
                'ultima_vencida'   => $ultimaVencida,
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

    /**
     * Devuelve las actividades PTA del cliente (por defecto abiertas) + los ya vinculados al slug.
     * Usado por el modal "Vincular PTA" en la vista detalle.
     */
    public function listarPtaPorSlug(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Cliente no encontrado.']);
        }

        $slug = trim((string) $this->request->getGet('slug'));
        if (!InspeccionTypes::bySlug($slug)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Tipo de inspección inválido.']);
        }

        $incluirCerradas = (bool) $this->request->getGet('cerradas');
        $anio = (int) ($this->request->getGet('anio') ?: date('Y'));

        $db = \Config\Database::connect();
        $builder = $db->table('tbl_pta_cliente')
            ->select('id_ptacliente, phva_plandetrabajo, numeral_plandetrabajo, actividad_plandetrabajo, fecha_propuesta, fecha_cierre, estado_actividad')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->orderBy('fecha_propuesta', 'ASC');

        if (!$incluirCerradas) {
            $builder->whereIn('estado_actividad', ['ABIERTA', 'GESTIONANDO']);
        }

        $ptas = $builder->get()->getResultArray();

        $ptaIds = array_map('intval', array_column($ptas, 'id_ptacliente'));

        // Solo pre-marcar los que aparecen en la lista del anio (asi no se borran los de otros anios al guardar)
        $vinculadosIds = [];
        if (!empty($ptaIds)) {
            $vinculados = $this->matchModel->where('id_cliente', $idCliente)
                ->where('slug_inspeccion', $slug)
                ->whereIn('id_ptacliente', $ptaIds)
                ->findAll();
            $vinculadosIds = array_map('intval', array_column($vinculados, 'id_ptacliente'));
        }

        return $this->response->setJSON([
            'ok'         => true,
            'ptas'       => $ptas,
            'vinculados' => $vinculadosIds,
        ]);
    }

    /**
     * Sincroniza los vinculos PTA <-> slug para un cliente.
     * Body: id_cliente, slug_inspeccion, ids_ptacliente[] (array)
     * Valida que todos los ids pertenezcan al cliente antes de escribir.
     */
    public function vincularPta()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $slug = trim((string) $this->request->getPost('slug_inspeccion'));
        $anio = (int) $this->request->getPost('anio');
        $idsRaw = $this->request->getPost('ids_ptacliente');
        $ids = is_array($idsRaw) ? array_values(array_unique(array_map('intval', $idsRaw))) : [];

        if (!$idCliente || !$slug || !$anio || !InspeccionTypes::bySlug($slug)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $db = \Config\Database::connect();

        // Scope: IDs de PTAs del cliente en el anio (base para sincronizar sin tocar otros anios)
        $scopeRows = $db->table('tbl_pta_cliente')
            ->select('id_ptacliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->get()
            ->getResultArray();
        $scopeIds = array_map('intval', array_column($scopeRows, 'id_ptacliente'));

        // Validacion: los seleccionados deben estar dentro del scope (implica pertenecer al cliente)
        $invalid = array_values(array_diff($ids, $scopeIds));
        if (!empty($invalid)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['ok' => false, 'msg' => 'Algunas actividades no pertenecen al cliente o al año.', 'invalid' => $invalid]);
        }

        // Matches actuales restringidos al scope del anio
        $actuales = empty($scopeIds) ? [] : $this->matchModel
            ->where('id_cliente', $idCliente)
            ->where('slug_inspeccion', $slug)
            ->whereIn('id_ptacliente', $scopeIds)
            ->findAll();
        $actualesIds = array_map('intval', array_column($actuales, 'id_ptacliente'));

        $toAdd = array_diff($ids, $actualesIds);
        $toRemove = array_diff($actualesIds, $ids);

        foreach ($toAdd as $idPta) {
            $this->matchModel->upsert([
                'id_cliente'      => $idCliente,
                'id_ptacliente'   => $idPta,
                'slug_inspeccion' => $slug,
                'score'           => 1.000,
                'method'          => 'manual',
                'reasoning'       => 'Vinculado manualmente desde Matriz de Inspecciones.',
                'ai_model'        => null,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        foreach ($toRemove as $idPta) {
            $this->matchModel->deleteMatch($idCliente, $idPta, $slug);
        }

        return $this->response->setJSON([
            'ok'      => true,
            'added'   => count($toAdd),
            'removed' => count($toRemove),
        ]);
    }

    /**
     * Desvincula una sola actividad PTA de un slug (accion rapida desde la fila).
     */
    public function desvincularPta()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $slug = trim((string) $this->request->getPost('slug_inspeccion'));
        $idPta = (int) $this->request->getPost('id_ptacliente');

        if (!$idCliente || !$slug || !$idPta) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $ok = $this->matchModel->deleteMatch($idCliente, $idPta, $slug);
        return $this->response->setJSON(['ok' => (bool) $ok]);
    }
}
