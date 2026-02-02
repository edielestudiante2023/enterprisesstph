<?php

namespace App\Controllers;

use App\Models\PtaClienteAuditModel;
use App\Models\ClientModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

/**
 * Controlador para gestionar la auditoría del Plan de Trabajo Anual (PTA)
 */
class AuditPtaController extends Controller
{
    protected $auditModel;
    protected $clientModel;

    public function __construct()
    {
        $this->auditModel  = new PtaClienteAuditModel();
        $this->clientModel = new ClientModel();
    }

    /**
     * Vista principal del listado de auditoría
     */
    public function index()
    {
        // Verificar permisos (solo superadmin y consultant)
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        // Verificar si la tabla de auditoría existe
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_pta_cliente_audit')) {
            return redirect()->to('/setup-audit-table')->with('warning', 'La tabla de auditoría no existe. Por favor, créela primero.');
        }

        // Obtener filtros
        $request     = service('request');
        $idCliente   = $request->getGet('cliente');
        $idUsuario   = $request->getGet('usuario');
        $accion      = $request->getGet('accion');
        $campo       = $request->getGet('campo');
        $fechaDesde  = $request->getGet('fecha_desde');
        $fechaHasta  = $request->getGet('fecha_hasta');

        // Preparar filtros
        $filters = [];
        if (!empty($idCliente))  $filters['id_cliente']       = $idCliente;
        if (!empty($idUsuario))  $filters['id_usuario']       = $idUsuario;
        if (!empty($accion))     $filters['accion']           = $accion;
        if (!empty($campo))      $filters['campo_modificado'] = $campo;
        if (!empty($fechaDesde)) $filters['fecha_desde']      = $fechaDesde;
        if (!empty($fechaHasta)) $filters['fecha_hasta']      = $fechaHasta;

        // Obtener registros
        $records = null;
        $stats   = null;

        if (!empty($filters)) {
            $records = $this->auditModel->getAuditWithFilters($filters);
            $stats   = $this->auditModel->getStats($filters);
        }

        // Obtener clientes para el selector
        $clients = $this->clientModel->findAll();

        // Obtener lista de usuarios únicos que han hecho cambios
        $usuarios = $this->auditModel->builder()
                         ->select('id_usuario, nombre_usuario')
                         ->groupBy('id_usuario')
                         ->orderBy('nombre_usuario', 'ASC')
                         ->get()
                         ->getResultArray();

        // Obtener campos únicos que han sido modificados
        $campos = $this->auditModel->builder()
                       ->select('campo_modificado')
                       ->where('campo_modificado IS NOT NULL')
                       ->groupBy('campo_modificado')
                       ->orderBy('campo_modificado', 'ASC')
                       ->get()
                       ->getResultArray();

        $data = [
            'clients'  => $clients,
            'usuarios' => $usuarios,
            'campos'   => $campos,
            'records'  => $records,
            'stats'    => $stats,
            'filters'  => [
                'cliente'     => $idCliente,
                'usuario'     => $idUsuario,
                'accion'      => $accion,
                'campo'       => $campo,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ],
        ];

        return view('admin/audit_pta_list', $data);
    }

    /**
     * Ver detalle de un registro de auditoría específico
     */
    public function view($idAudit)
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder.');
        }

        $record = $this->auditModel->find($idAudit);

        if (!$record) {
            return redirect()->to('/audit-pta')->with('error', 'Registro de auditoría no encontrado.');
        }

        // Obtener información del cliente
        $cliente = null;
        if (!empty($record['id_cliente'])) {
            $cliente = $this->clientModel->find($record['id_cliente']);
        }

        // Obtener historial completo del mismo registro PTA
        $historial = $this->auditModel->getHistoryByPtaId($record['id_ptacliente']);

        $data = [
            'record'    => $record,
            'cliente'   => $cliente,
            'historial' => $historial,
        ];

        return view('admin/audit_pta_view', $data);
    }

    /**
     * Ver historial de un registro PTA específico
     */
    public function historyPta($idPtaCliente)
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder.');
        }

        $historial = $this->auditModel->getHistoryByPtaId($idPtaCliente);

        // Obtener datos del registro PTA
        $ptaModel = new \App\Models\PtaClienteNuevaModel();
        $registro = $ptaModel->find($idPtaCliente);

        // Obtener cliente
        $cliente = null;
        if ($registro && !empty($registro['id_cliente'])) {
            $cliente = $this->clientModel->find($registro['id_cliente']);
        }

        $data = [
            'historial'    => $historial,
            'registro'     => $registro,
            'cliente'      => $cliente,
            'idPtaCliente' => $idPtaCliente,
        ];

        return view('admin/audit_pta_history', $data);
    }

    /**
     * Exportar auditoría a Excel/CSV
     */
    public function export()
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para exportar.');
        }

        // Obtener filtros
        $request = service('request');
        $filters = [
            'id_cliente'       => $request->getGet('cliente'),
            'id_usuario'       => $request->getGet('usuario'),
            'accion'           => $request->getGet('accion'),
            'campo_modificado' => $request->getGet('campo'),
            'fecha_desde'      => $request->getGet('fecha_desde'),
            'fecha_hasta'      => $request->getGet('fecha_hasta'),
        ];

        // Limpiar filtros vacíos
        $filters = array_filter($filters);

        // Obtener datos para exportar
        $records = $this->auditModel->getForExport($filters);

        // Preparar la descarga como Excel (CSV con tab)
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment;filename="auditoria_pta_' . date('Y-m-d_His') . '.xls"');
        header('Cache-Control: max-age=0');

        // BOM para UTF-8
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Encabezados
        $header = [
            'ID Auditoría',
            'ID Registro PTA',
            'ID Cliente',
            'Acción',
            'Campo Modificado',
            'Valor Anterior',
            'Valor Nuevo',
            'Usuario',
            'Email Usuario',
            'Rol',
            'IP',
            'Descripción',
            'Fecha y Hora'
        ];
        fputcsv($output, $header, "\t");

        // Datos
        foreach ($records as $row) {
            $data = [
                $row['id_audit'],
                $row['id_ptacliente'],
                $row['id_cliente'],
                $row['accion_legible'] ?? $row['accion'],
                $row['campo_modificado_legible'] ?? $row['campo_modificado'],
                $row['valor_anterior'],
                $row['valor_nuevo'],
                $row['nombre_usuario'],
                $row['email_usuario'],
                $row['rol_usuario'],
                $row['ip_address'],
                $row['descripcion'],
                $row['fecha_accion'],
            ];
            fputcsv($output, $data, "\t");
        }

        fclose($output);
        exit;
    }

    /**
     * API: Obtener cambios recientes (para widgets/dashboards)
     */
    public function apiRecentChanges()
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $limit = $this->request->getGet('limit') ?? 10;
        $records = $this->auditModel->getRecentChanges((int) $limit);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $records,
            'count'   => count($records),
        ]);
    }

    /**
     * API: Obtener estadísticas
     */
    public function apiStats()
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $idCliente = $this->request->getGet('cliente');
        $filters = [];

        if (!empty($idCliente)) {
            $filters['id_cliente'] = $idCliente;
        }

        $stats = $this->auditModel->getStats($filters);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    /**
     * Dashboard resumen de auditoría
     */
    public function dashboard()
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder.');
        }

        // Estadísticas generales
        $stats = $this->auditModel->getStats([]);

        // Cambios recientes
        $recentChanges = $this->auditModel->getRecentChanges(20);

        // Clientes con más cambios
        $clientesConCambios = $this->auditModel->builder()
            ->select('id_cliente, COUNT(*) as total_cambios')
            ->groupBy('id_cliente')
            ->orderBy('total_cambios', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Agregar nombre del cliente
        foreach ($clientesConCambios as &$item) {
            $cliente = $this->clientModel->find($item['id_cliente']);
            $item['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'N/A';
        }

        $data = [
            'stats'               => $stats,
            'recentChanges'       => $recentChanges,
            'clientesConCambios'  => $clientesConCambios,
        ];

        return view('admin/audit_pta_dashboard', $data);
    }
}
