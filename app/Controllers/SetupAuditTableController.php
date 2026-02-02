<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Controlador para crear la tabla de auditoría del PTA
 * Acceder vía: /setup-audit-table (requiere autenticación de superadmin)
 */
class SetupAuditTableController extends Controller
{
    /**
     * Página principal con opciones para crear tabla
     */
    public function index()
    {
        // Verificar que sea superadmin
        $session = session();
        if ($session->get('rol') !== 'superadmin') {
            return redirect()->to('/login')->with('error', 'Acceso denegado. Solo superadmin puede ejecutar esta acción.');
        }

        return view('admin/setup_audit_table');
    }

    /**
     * Crear tabla en la base de datos LOCAL (XAMPP)
     */
    public function createLocal()
    {
        $session = session();
        if ($session->get('rol') !== 'superadmin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            // Usar la configuración por defecto (local)
            $db = \Config\Database::connect();
            $result = $this->createAuditTable($db);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tabla de auditoría creada exitosamente en LOCAL',
                'details' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al crear tabla: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Crear tabla en la base de datos de PRODUCCIÓN (DigitalOcean)
     */
    public function createProduction()
    {
        $session = session();
        if ($session->get('rol') !== 'superadmin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            // Si estamos en producción, usar la conexión por defecto
            if (env('CI_ENVIRONMENT') === 'production') {
                $db = \Config\Database::connect();
            } else {
                // Configuración de producción desde variables de entorno (para localhost)
                $customConfig = [
                    'DSN'          => '',
                    'hostname'     => env('database.production.hostname', ''),
                    'username'     => env('database.production.username', ''),
                    'password'     => env('database.production.password', ''),
                    'database'     => env('database.production.database', ''),
                    'DBDriver'     => env('database.production.DBDriver', 'MySQLi'),
                    'DBPrefix'     => '',
                    'pConnect'     => false,
                    'DBDebug'      => true,
                    'charset'      => 'utf8mb4',
                    'DBCollat'     => 'utf8mb4_unicode_ci',
                    'swapPre'      => '',
                    'encrypt'      => [
                        'ssl_key'    => null,
                        'ssl_cert'   => null,
                        'ssl_ca'     => null,
                        'ssl_capath' => null,
                        'ssl_cipher' => null,
                        'ssl_verify' => false,
                    ],
                    'failover'     => [],
                    'port'         => (int) env('database.production.port', 25060),
                    'strictOn'     => false,
                ];

                // Verificar que las credenciales estén configuradas
                if (empty($customConfig['hostname']) || empty($customConfig['password'])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Credenciales de producción no configuradas. Agregue las variables database.production.* en el archivo .env'
                    ]);
                }

                $db = \Config\Database::connect($customConfig);
            }

            $result = $this->createAuditTable($db);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tabla de auditoría creada exitosamente en PRODUCCIÓN',
                'details' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al crear tabla en producción: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Crear la tabla de auditoría
     */
    private function createAuditTable($db)
    {
        $tableName = 'tbl_pta_cliente_audit';

        // Verificar si la tabla ya existe
        if ($db->tableExists($tableName)) {
            return "La tabla '$tableName' ya existe. No se realizaron cambios.";
        }

        $sql = "CREATE TABLE `$tableName` (
            `id_audit` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID único de auditoría',
            `id_ptacliente` INT(11) NOT NULL COMMENT 'ID del registro modificado en tbl_pta_cliente',
            `id_cliente` INT(11) NULL DEFAULT NULL COMMENT 'ID del cliente al que pertenece el registro',
            `accion` ENUM('INSERT','UPDATE','DELETE','BULK_UPDATE') NOT NULL COMMENT 'Tipo de acción realizada',
            `campo_modificado` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Nombre del campo que fue modificado',
            `valor_anterior` TEXT NULL DEFAULT NULL COMMENT 'Valor antes del cambio',
            `valor_nuevo` TEXT NULL DEFAULT NULL COMMENT 'Valor después del cambio',
            `id_usuario` INT(11) NOT NULL COMMENT 'ID del usuario que realizó el cambio',
            `nombre_usuario` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nombre del usuario para referencia rápida',
            `email_usuario` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Email del usuario',
            `rol_usuario` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Rol del usuario al momento del cambio',
            `ip_address` VARCHAR(45) NULL DEFAULT NULL COMMENT 'Dirección IP desde donde se realizó el cambio',
            `user_agent` VARCHAR(500) NULL DEFAULT NULL COMMENT 'Navegador/dispositivo del usuario',
            `metodo` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Método del controlador que realizó el cambio',
            `descripcion` TEXT NULL DEFAULT NULL COMMENT 'Descripción legible del cambio realizado',
            `fecha_accion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del cambio',
            PRIMARY KEY (`id_audit`),
            INDEX `idx_ptacliente` (`id_ptacliente`),
            INDEX `idx_cliente` (`id_cliente`),
            INDEX `idx_usuario` (`id_usuario`),
            INDEX `idx_fecha` (`fecha_accion`),
            INDEX `idx_accion` (`accion`),
            INDEX `idx_campo` (`campo_modificado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoría de cambios en Plan de Trabajo Anual (PTA)'";

        $db->query($sql);

        return "Tabla '$tableName' creada exitosamente con todos los índices.";
    }

    /**
     * Verificar estado de la tabla en ambas bases de datos
     */
    public function checkStatus()
    {
        $session = session();
        if ($session->get('rol') !== 'superadmin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $isProduction = (env('CI_ENVIRONMENT') === 'production');

        $status = [
            'local' => false,
            'production' => false,
            'local_count' => 0,
            'production_count' => 0,
            'is_production_env' => $isProduction,
        ];

        // En producción, solo verificamos la conexión por defecto
        if ($isProduction) {
            try {
                $db = \Config\Database::connect();
                $status['production'] = $db->tableExists('tbl_pta_cliente_audit');
                if ($status['production']) {
                    $status['production_count'] = $db->table('tbl_pta_cliente_audit')->countAllResults();
                }
                // En producción, local = production (es la misma base de datos)
                $status['local'] = $status['production'];
                $status['local_count'] = $status['production_count'];
            } catch (\Exception $e) {
                $status['production_error'] = $e->getMessage();
            }
        } else {
            // En desarrollo, verificar LOCAL
            try {
                $dbLocal = \Config\Database::connect();
                $status['local'] = $dbLocal->tableExists('tbl_pta_cliente_audit');
                if ($status['local']) {
                    $status['local_count'] = $dbLocal->table('tbl_pta_cliente_audit')->countAllResults();
                }
            } catch (\Exception $e) {
                $status['local_error'] = $e->getMessage();
            }

            // En desarrollo, verificar PRODUCCIÓN con credenciales del .env
            try {
                $customConfig = [
                    'DSN'          => '',
                    'hostname'     => env('database.production.hostname', ''),
                    'username'     => env('database.production.username', ''),
                    'password'     => env('database.production.password', ''),
                    'database'     => env('database.production.database', ''),
                    'DBDriver'     => env('database.production.DBDriver', 'MySQLi'),
                    'DBPrefix'     => '',
                    'pConnect'     => false,
                    'DBDebug'      => false,
                    'charset'      => 'utf8mb4',
                    'DBCollat'     => 'utf8mb4_unicode_ci',
                    'swapPre'      => '',
                    'encrypt'      => [
                        'ssl_verify' => false,
                    ],
                    'failover'     => [],
                    'port'         => (int) env('database.production.port', 25060),
                    'strictOn'     => false,
                ];

                // Si no hay credenciales de producción configuradas, saltar verificación
                if (empty($customConfig['hostname']) || empty($customConfig['password'])) {
                    $status['production_error'] = 'Credenciales de producción no configuradas en .env';
                } else {
                    $dbProd = \Config\Database::connect($customConfig);
                    $status['production'] = $dbProd->tableExists('tbl_pta_cliente_audit');
                    if ($status['production']) {
                        $status['production_count'] = $dbProd->table('tbl_pta_cliente_audit')->countAllResults();
                    }
                }
            } catch (\Exception $e) {
                $status['production_error'] = $e->getMessage();
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'status' => $status
        ]);
    }
}
