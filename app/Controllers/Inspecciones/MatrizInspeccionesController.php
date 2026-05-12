<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Libraries\InspeccionTypes;
use App\Models\ClientModel;
use App\Models\ContractModel;
use App\Models\InspeccionFrecuenciaClienteModel;
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
     * Pantalla 2: detalle de inspecciones de un cliente.
     * Filtra por rango fecha_desde / fecha_hasta. Acepta ?anio=YYYY como atajo (back-compat)
     * y mapea a 01/01–31/12 del año indicado. Default: año actual completo.
     */
    public function detalle(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to(base_url('inspecciones/matriz'))
                ->with('error', 'Cliente no encontrado.');
        }

        $isValidDate = static fn($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;

        $fechaDesde = trim((string) $this->request->getGet('fecha_desde'));
        $fechaHasta = trim((string) $this->request->getGet('fecha_hasta'));
        $anioParam  = trim((string) $this->request->getGet('anio'));

        if ($isValidDate($fechaDesde) && $isValidDate($fechaHasta)) {
            // Modo rango explícito
            if ($fechaDesde > $fechaHasta) {
                [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
            }
        } else {
            // Modo año (back-compat o default)
            $anio = (int) ($anioParam ?: date('Y'));
            $fechaDesde = $anio . '-01-01';
            $fechaHasta = $anio . '-12-31';
        }

        // Año "activo" (para resaltar card y filtrar meses): año de fecha_desde si coincide con fecha_hasta
        $anioInicio = (int) substr($fechaDesde, 0, 4);
        $anioFin    = (int) substr($fechaHasta, 0, 4);
        $anio       = $anioInicio === $anioFin ? $anioInicio : (int) date('Y');

        // Mes "activo" si el rango es justo un mes calendario completo
        $mesActivo = null;
        if ($anioInicio === $anioFin) {
            $mesIni = (int) substr($fechaDesde, 5, 2);
            $mesFin = (int) substr($fechaHasta, 5, 2);
            $diaIni = (int) substr($fechaDesde, 8, 2);
            $diaFin = (int) substr($fechaHasta, 8, 2);
            $ultDia = (int) date('t', strtotime($fechaDesde));
            if ($mesIni === $mesFin && $diaIni === 1 && $diaFin === $ultDia) {
                $mesActivo = $mesIni;
            }
        }

        $tipos    = InspeccionTypes::all();
        $noAplica = $this->noAplicaModel->getByCliente($idCliente);

        // Frecuencias configuradas por el consultor para este cliente
        $frecuenciasMap = (new InspeccionFrecuenciaClienteModel())->getByCliente($idCliente);

        // Contrato activo del cliente (para mostrar frecuencia)
        $contractModel = new ContractModel();
        $lastContract = $contractModel->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('fecha_fin', 'DESC')
            ->first();
        if (!$lastContract) {
            $lastContract = $contractModel->where('id_cliente', $idCliente)
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        $db = \Config\Database::connect();

        // Precargar matches del cliente cuyo PTA cae en el rango
        $matchesBySlug = [];
        if ($db->tableExists('tbl_pta_inspeccion_match')) {
            $matches = $db->table('tbl_pta_inspeccion_match m')
                ->select('m.slug_inspeccion, m.id_ptacliente, m.method, m.score, p.fecha_propuesta, p.fecha_cierre, p.numeral_plandetrabajo, p.actividad_plandetrabajo, p.estado_actividad')
                ->join('tbl_pta_cliente p', 'p.id_ptacliente = m.id_ptacliente', 'inner')
                ->where('m.id_cliente', $idCliente)
                ->where('p.fecha_propuesta >=', $fechaDesde)
                ->where('p.fecha_propuesta <=', $fechaHasta)
                ->orderBy('p.fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($matches as $m) {
                $matchesBySlug[$m['slug_inspeccion']][] = $m;
            }
        }

        // Conteos por año/mes (initial fallback — JS los recalcula client-side en cada draw).
        // Cuenta TODAS las inspecciones del cliente (cualquier estado), no filtra por estado=completo.
        // Suma fechas planeadas (PTAs vinculadas) y fechas realizadas (inspecciones en cada tabla).
        $inspeccionesPorAnio = [];
        $inspeccionesPorMes  = array_fill(1, 12, 0);
        foreach ($tipos as $tipo) {
            if (!$db->tableExists($tipo['table'])) continue;
            $fieldsT = $db->getFieldNames($tipo['table']);
            $dateColT = in_array($tipo['date_col'], $fieldsT, true) ? $tipo['date_col'] : null;
            if (!$dateColT || !in_array('id_cliente', $fieldsT, true)) continue;

            $b = $db->table($tipo['table'])
                ->select("YEAR({$dateColT}) AS y, MONTH({$dateColT}) AS m, COUNT(*) AS c")
                ->where('id_cliente', $idCliente)
                ->where("{$dateColT} IS NOT NULL", null, false)
                ->groupBy(["YEAR({$dateColT})", "MONTH({$dateColT})"]);

            foreach (($tipo['extra_where'] ?? []) as $col => $val) {
                if (in_array($col, $fieldsT, true)) $b->where($col, $val);
            }

            foreach ($b->get()->getResultArray() as $r) {
                $y = (int) $r['y']; $m = (int) $r['m']; $c = (int) $r['c'];
                if ($y > 0) $inspeccionesPorAnio[$y] = ($inspeccionesPorAnio[$y] ?? 0) + $c;
                if ($y === $anio && $m >= 1 && $m <= 12) {
                    $inspeccionesPorMes[$m] += $c;
                }
            }
        }

        // Sumar PTAs vinculadas (programadas) por año/mes al pool de conteos
        if ($db->tableExists('tbl_pta_inspeccion_match')) {
            $rowsPta = $db->table('tbl_pta_inspeccion_match m')
                ->select("YEAR(p.fecha_propuesta) AS y, MONTH(p.fecha_propuesta) AS mm, COUNT(*) AS c")
                ->join('tbl_pta_cliente p', 'p.id_ptacliente = m.id_ptacliente', 'inner')
                ->where('m.id_cliente', $idCliente)
                ->where('p.fecha_propuesta IS NOT NULL', null, false)
                ->groupBy(["YEAR(p.fecha_propuesta)", "MONTH(p.fecha_propuesta)"])
                ->get()->getResultArray();
            foreach ($rowsPta as $r) {
                $y = (int) $r['y']; $m = (int) $r['mm']; $c = (int) $r['c'];
                if ($y > 0) $inspeccionesPorAnio[$y] = ($inspeccionesPorAnio[$y] ?? 0) + $c;
                if ($y === $anio && $m >= 1 && $m <= 12) {
                    $inspeccionesPorMes[$m] += $c;
                }
            }
        }
        krsort($inspeccionesPorAnio);

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
                        ->where("{$dateCol} >=", $fechaDesde)
                        ->where("{$dateCol} <=", $fechaHasta)
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

            // Frecuencia configurada por el consultor (veces al año) para este combo cliente+slug
            $vecesAnio       = $frecuenciasMap[$tipo['slug']] ?? null; // null = sin definir, 0 = puntual
            $realizadasAnio  = 0;
            $ultimaGlobal    = null;
            if ($vecesAnio !== null && $db->tableExists($tipo['table'])) {
                $fieldsT  = $db->getFieldNames($tipo['table']);
                $dateColT = in_array($tipo['date_col'], $fieldsT, true) ? $tipo['date_col'] : null;
                if ($dateColT && in_array('id_cliente', $fieldsT, true)) {
                    // Aplica los MISMOS filtros que el query principal de inspecciones para no mezclar
                    // tipos que comparten tabla (ej. tbl_certificado_servicio: Desratización/Fumigación/Lavado).
                    $estadoColT2   = array_key_exists('estado_col', $tipo) ? $tipo['estado_col'] : 'estado';
                    $estadoValueT2 = $tipo['estado_value'] ?? 'completo';
                    $extraWhereT2  = $tipo['extra_where'] ?? [];

                    $b2 = $db->table($tipo['table'])
                        ->select("COUNT(*) AS c, MAX({$dateColT}) AS u")
                        ->where('id_cliente', $idCliente)
                        ->where("YEAR({$dateColT})", $anio);

                    if ($estadoColT2 !== null && in_array($estadoColT2, $fieldsT, true)) {
                        $b2->where($estadoColT2, $estadoValueT2);
                    }
                    foreach ($extraWhereT2 as $colW => $valW) {
                        if (in_array($colW, $fieldsT, true)) $b2->where($colW, $valW);
                    }

                    $row = $b2->get()->getRowArray();
                    $realizadasAnio = (int) ($row['c'] ?? 0);
                    $ultimaGlobal   = $row['u'] ?? null;
                }
            }

            if ($na) {
                $estado = 'no_aplica';
            } elseif ($vecesAnio !== null && $vecesAnio > 0 && $realizadasAnio >= $vecesAnio) {
                // Cumple meta anual configurada → al día
                $estado = 'al_dia';
            } elseif ($total > 0) {
                $estado = 'hecha';
            } elseif ($hayPasadas && !$hayFuturas) {
                $estado = 'atrasada';
            } else {
                $estado = 'pendiente';
            }

            $filas[] = [
                'slug'             => $tipo['slug'],
                'label'            => $tipo['label'],
                'group'            => $tipo['group'] ?? 'Otros',
                'icon'             => $tipo['icon'],
                'list_route'       => $tipo['list_route'],
                'create_route'     => $tipo['create_route'],
                'view_route'       => $tipo['view_route'],
                'inspecciones'     => $inspecciones,
                'ultima'           => $ultima,
                'total'            => $total,
                'estado'           => $estado,
                'no_aplica'        => $na,
                'pta_vinculados'   => $ptaVincs,
                'proxima_planeada' => $proxima,
                'ultima_vencida'   => $ultimaVencida,
                'veces_anio'       => $vecesAnio,
                'realizadas_anio'  => $realizadasAnio,
                'ultima_global'    => $ultimaGlobal,
            ];
        }

        usort($filas, function ($a, $b) {
            if ($a['estado'] === 'no_aplica' && $b['estado'] !== 'no_aplica') return 1;
            if ($b['estado'] === 'no_aplica' && $a['estado'] !== 'no_aplica') return -1;
            return 0;
        });

        // Si el rango es un subset (no año completo), ocultar filas sin actividad
        // (ni inspecciones realizadas ni PTAs vinculadas en el rango) para no confundir al consultor.
        $rangoEsAnioCompleto = ($fechaDesde === $anio . '-01-01' && $fechaHasta === $anio . '-12-31');
        if (!$rangoEsAnioCompleto) {
            $filas = array_values(array_filter($filas, static function ($f) {
                return count($f['inspecciones']) > 0 || count($f['pta_vinculados']) > 0;
            }));
        }

        // Años disponibles: año actual en adelante (sin años pasados).
        // Si el cliente tiene PTAs/inspecciones programadas en años futuros, aparecen también.
        $anioActual = (int) date('Y');
        $aniosFuturos = array_filter(array_keys($inspeccionesPorAnio), static fn($y) => (int) $y >= $anioActual);
        $aniosUnion = array_unique(array_merge($aniosFuturos, [$anioActual]));
        rsort($aniosUnion);
        $aniosDisponibles = $aniosUnion;

        // Limpiar inspeccionesPorAnio para que no muestre conteos de años pasados
        foreach (array_keys($inspeccionesPorAnio) as $yKey) {
            if ((int) $yKey < $anioActual) unset($inspeccionesPorAnio[$yKey]);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/matriz/detalle', [
                'cliente'              => $cliente,
                'lastContract'         => $lastContract,
                'anio'                 => $anio,
                'fechaDesde'           => $fechaDesde,
                'fechaHasta'           => $fechaHasta,
                'mesActivo'            => $mesActivo,
                'aniosDisponibles'     => $aniosDisponibles,
                'inspeccionesPorAnio'  => $inspeccionesPorAnio,
                'inspeccionesPorMes'   => $inspeccionesPorMes,
                'filas'                => $filas,
            ]),
            'title'   => 'Matriz - ' . ($cliente['nombre_cliente'] ?? 'Cliente'),
        ]);
    }

    /**
     * Define / actualiza la frecuencia (veces por año) de un tipo de inspección para un cliente.
     * POST: id_cliente, slug_inspeccion, veces_anio (0..365)
     */
    public function setFrecuencia()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $slug      = trim((string) $this->request->getPost('slug_inspeccion'));
        $veces     = (int) $this->request->getPost('veces_anio');

        if (!$idCliente || !$slug || $veces < 0 || $veces > 365 || !InspeccionTypes::bySlug($slug)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $ok = (new InspeccionFrecuenciaClienteModel())->setVecesAnio($idCliente, $slug, $veces);
        return $this->response->setJSON(['ok' => (bool) $ok]);
    }

    /**
     * Cierra PTAs vinculadas al slug del cliente sincronizándolas con las inspecciones realizadas.
     * - Si hay PTAs abiertas: cierra las primeras N en orden por fecha_propuesta, asignando
     *   fecha_propuesta = fecha_cierre = fecha real de la inspección correspondiente.
     * - Si NO hay PTAs vinculadas pero sí inspecciones: crea N PTAs ya CERRADAS con la fecha real
     *   de cada inspección y las vincula al slug (registro retroactivo).
     *
     * POST: id_cliente, slug_inspeccion
     */
    public function cerrarPtaPorMatriz()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $slug      = trim((string) $this->request->getPost('slug_inspeccion'));

        $tipo = InspeccionTypes::bySlug($slug);
        if (!$idCliente || !$slug || !$tipo) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }

        $db = \Config\Database::connect();

        if (!$db->tableExists($tipo['table'])) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Tabla de inspección no existe.']);
        }
        $fields  = $db->getFieldNames($tipo['table']);
        $dateCol = in_array($tipo['date_col'], $fields, true) ? $tipo['date_col'] : null;
        if (!$dateCol || !in_array('id_cliente', $fields, true)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Tabla sin columnas necesarias.']);
        }

        $estadoCol   = array_key_exists('estado_col', $tipo) ? $tipo['estado_col'] : 'estado';
        $estadoValue = $tipo['estado_value'] ?? 'completo';
        $extraWhere  = $tipo['extra_where'] ?? [];

        // Inspecciones reales realizadas (ordenadas por fecha ASC, aplicando todos los filtros del catálogo)
        $b = $db->table($tipo['table'])
            ->select("{$dateCol} AS fecha")
            ->where('id_cliente', $idCliente)
            ->where("{$dateCol} IS NOT NULL", null, false)
            ->orderBy($dateCol, 'ASC');
        if ($estadoCol !== null && in_array($estadoCol, $fields, true)) {
            $b->where($estadoCol, $estadoValue);
        }
        foreach ($extraWhere as $col => $val) {
            if (in_array($col, $fields, true)) $b->where($col, $val);
        }
        $inspecciones = array_column($b->get()->getResultArray(), 'fecha');

        if (empty($inspecciones)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'No hay inspecciones realizadas para cerrar.']);
        }

        // PTAs vinculadas al slug, no cerradas
        $ptasAbiertas = $db->table('tbl_pta_cliente p')
            ->select('p.id_ptacliente, p.estado_actividad')
            ->join('tbl_pta_inspeccion_match m', 'm.id_ptacliente = p.id_ptacliente', 'inner')
            ->where('m.id_cliente', $idCliente)
            ->where('m.slug_inspeccion', $slug)
            ->where('p.estado_actividad !=', 'CERRADA')
            ->orderBy('p.fecha_propuesta', 'ASC')
            ->get()->getResultArray();

        $now      = date('Y-m-d H:i:s');
        $cerradas = 0;
        $creadas  = 0;

        if (!empty($ptasAbiertas)) {
            // Caso 1: cerrar UNA SOLA PTA (la más antigua abierta) con la fecha de la
            // inspección MÁS RECIENTE. Permite al consultor sincronizar de a una.
            $fechaMasReciente = end($inspecciones); // ordenadas ASC → última es la más reciente
            $idPta = (int) $ptasAbiertas[0]['id_ptacliente']; // primera abierta = más antigua
            $db->table('tbl_pta_cliente')
                ->where('id_ptacliente', $idPta)
                ->update([
                    'estado_actividad'  => 'CERRADA',
                    'fecha_propuesta'   => $fechaMasReciente,
                    'fecha_cierre'      => $fechaMasReciente,
                    'porcentaje_avance' => 100,
                    'updated_at'        => $now,
                ]);
            $cerradas = 1;
        } else {
            // Caso 2: no hay PTAs vinculadas → crear N PTAs CERRADAS retroactivamente
            foreach ($inspecciones as $fecha) {
                $db->table('tbl_pta_cliente')->insert([
                    'id_cliente'                           => $idCliente,
                    'phva_plandetrabajo'                   => 'HACER',
                    'numeral_plandetrabajo'                => '-',
                    'actividad_plandetrabajo'              => 'Inspección de ' . $tipo['label'] . ' (registrada retroactivamente desde la matriz)',
                    'responsable_sugerido_plandetrabajo'   => 'CONSULTOR CYCLOID',
                    'responsable_definido_paralaactividad' => '-',
                    'fecha_propuesta'                      => $fecha,
                    'fecha_cierre'                         => $fecha,
                    'estado_actividad'                     => 'CERRADA',
                    'porcentaje_avance'                    => 100,
                    'created_at'                           => $now,
                    'updated_at'                           => $now,
                ]);
                $idPta = (int) $db->insertID();
                if ($idPta > 0) {
                    $this->matchModel->upsert([
                        'id_cliente'      => $idCliente,
                        'id_ptacliente'   => $idPta,
                        'slug_inspeccion' => $slug,
                        'score'           => 1.000,
                        'method'          => 'manual',
                        'reasoning'       => 'Creada retroactivamente desde la matriz al cerrar inspección hecha.',
                        'ai_model'        => null,
                        'created_at'      => $now,
                    ]);
                    $creadas++;
                }
            }
        }

        return $this->response->setJSON([
            'ok'       => true,
            'cerradas' => $cerradas,
            'creadas'  => $creadas,
            'inspecciones_total' => count($inspecciones),
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
     * Crea una nueva actividad en tbl_pta_cliente y la auto-vincula al slug.
     * Pensado para tipos de inspeccion huerfanas (sin PTA asociado) — el consultor
     * dispara la creacion desde la matriz con una fecha indicada.
     */
    public function crearPta()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $slug = trim((string) $this->request->getPost('slug_inspeccion'));
        $fecha = trim((string) $this->request->getPost('fecha_propuesta'));
        $actividad = trim((string) $this->request->getPost('actividad'));
        $phva = strtoupper(trim((string) $this->request->getPost('phva'))) ?: 'HACER';
        $numeral = trim((string) $this->request->getPost('numeral')) ?: '-';
        $responsable = trim((string) $this->request->getPost('responsable_sugerido')) ?: 'CONSULTOR CYCLOID';
        $observaciones = trim((string) $this->request->getPost('observaciones')) ?: null;

        $phvaValidos = ['PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR'];
        if (!$idCliente || !$slug || !InspeccionTypes::bySlug($slug) || !$fecha || !$actividad) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Parámetros inválidos.']);
        }
        if (!in_array($phva, $phvaValidos, true)) $phva = 'HACER';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Fecha inválida (YYYY-MM-DD).']);
        }

        if (!$this->clienteModel->find($idCliente)) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'msg' => 'Cliente no encontrado.']);
        }

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->table('tbl_pta_cliente')->insert([
            'id_cliente'                            => $idCliente,
            'phva_plandetrabajo'                    => $phva,
            'numeral_plandetrabajo'                 => $numeral,
            'actividad_plandetrabajo'               => $actividad,
            'responsable_sugerido_plandetrabajo'    => mb_substr($responsable, 0, 255),
            'responsable_definido_paralaactividad'  => '-',
            'fecha_propuesta'                       => $fecha,
            'estado_actividad'                      => 'ABIERTA',
            'porcentaje_avance'                     => 0,
            'observaciones'                         => $observaciones,
            'created_at'                            => $now,
            'updated_at'                            => $now,
        ]);
        $idPta = (int) $db->insertID();

        if (!$idPta) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'No se pudo crear la actividad.']);
        }

        $this->matchModel->upsert([
            'id_cliente'      => $idCliente,
            'id_ptacliente'   => $idPta,
            'slug_inspeccion' => $slug,
            'score'           => 1.000,
            'method'          => 'manual',
            'reasoning'       => 'Creada desde la Matriz de Inspecciones.',
            'ai_model'        => null,
            'created_at'      => $now,
        ]);

        return $this->response->setJSON([
            'ok'            => true,
            'id_ptacliente' => $idPta,
        ]);
    }

    /**
     * Genera detalles sugeridos (numeral, PHVA, responsable, observaciones, semana)
     * para una actividad PTA usando Claude Haiku 4.5. Stateless: no toca BD.
     */
    public function generarDetallesPta()
    {
        $actividad = trim((string) $this->request->getPost('actividad'));
        $slug = trim((string) $this->request->getPost('slug_inspeccion'));

        if (!$actividad || !$slug) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Falta actividad o slug.']);
        }
        $tipo = InspeccionTypes::bySlug($slug);
        if (!$tipo) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Tipo inválido.']);
        }

        $apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
        if (!$apiKey) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'msg' => 'ANTHROPIC_API_KEY no configurada.']);
        }

        $prompt = "Eres experto en SG-SST Decreto 1072 de 2015 para copropiedades en Colombia.\n\n"
            . "Actividad PTA: \"" . $actividad . "\"\n"
            . "Tipo de inspeccion asociada: " . $tipo['label'] . " (grupo: " . ($tipo['group'] ?? 'Otros') . ")\n\n"
            . "Devuelve UN SOLO JSON estricto con estos campos:\n"
            . "- numeral: string con el numeral Decreto 1072 mas apropiado (ej 1.2.3 o 4.1.1). Usa solo digitos y puntos.\n"
            . "- phva: PLANEAR | HACER | VERIFICAR | ACTUAR\n"
            . "- responsable_sugerido: string max 80 chars, cargos separados por coma. Incluye CONSULTOR CYCLOID.\n"
            . "- observaciones: string max 120 chars, una frase breve de contexto u objetivo.\n\n"
            . "Responde SOLO el JSON valido, sin markdown ni texto adicional.";

        $payload = [
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 500,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'msg' => 'Error de red: ' . $err]);
        }
        if ($httpCode !== 200) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'msg' => 'Anthropic HTTP ' . $httpCode]);
        }

        $data = json_decode($resp, true);
        $content = $data['content'][0]['text'] ?? '';
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(json)?\s*|\s*```\s*$/i', '', $content);
            $content = trim($content);
        }
        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'IA respondió JSON inválido.']);
        }

        $phva = strtoupper((string) ($parsed['phva'] ?? 'HACER'));
        if (!in_array($phva, ['PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR'], true)) $phva = 'HACER';

        return $this->response->setJSON([
            'ok'                   => true,
            'numeral'              => mb_substr((string) ($parsed['numeral'] ?? ''), 0, 60),
            'phva'                 => $phva,
            'responsable_sugerido' => mb_substr((string) ($parsed['responsable_sugerido'] ?? 'CONSULTOR CYCLOID'), 0, 255),
            'observaciones'        => mb_substr((string) ($parsed['observaciones'] ?? ''), 0, 255),
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
