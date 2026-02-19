<?php
/**
 * Script CLI: Agregar CERRADA POR FIN CONTRATO a los ENUMs de las tablas de actividades.
 *
 * Uso:
 *   php app/CLI/aplicar_enum_fin_contrato.php local
 *   php app/CLI/aplicar_enum_fin_contrato.php prod
 */

$entorno = $argv[1] ?? null;

if (!in_array($entorno, ['local', 'prod'])) {
    echo "\n[ERROR] Debes especificar el entorno: local o prod\n";
    echo "Ejemplo: php app/CLI/aplicar_enum_fin_contrato.php local\n\n";
    exit(1);
}

// ─── Credenciales ────────────────────────────────────────────────────────────
if ($entorno === 'local') {
    $host   = '127.0.0.1';
    $port   = '3306';
    $dbname = 'empresas_sst';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
} else {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = '25060';
    $dbname = 'empresas_sst';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS') ?: '';
    $ssl    = true;
}

// ─── Conexión PDO ─────────────────────────────────────────────────────────────
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

if ($ssl) {
    $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opciones[PDO::MYSQL_ATTR_SSL_CA]                 = true;
}

echo "\n======================================================\n";
echo "  APLICAR ENUM: CERRADA POR FIN CONTRATO\n";
echo "  Entorno: " . strtoupper($entorno) . "\n";
echo "  Host:    {$host}:{$port}\n";
echo "  BD:      {$dbname}\n";
echo "======================================================\n\n";

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
    echo "[OK] Conexión establecida con {$host}\n\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(1);
}

// ─── Alteraciones ─────────────────────────────────────────────────────────────
$alteraciones = [
    [
        'tabla'       => 'tbl_pta_cliente',
        'campo'       => 'estado_actividad',
        'descripcion' => 'Plan de Trabajo Anual del Cliente',
        'sql'         => "ALTER TABLE tbl_pta_cliente
                          MODIFY COLUMN estado_actividad
                          ENUM('ABIERTA','CERRADA','GESTIONANDO','CERRADA SIN EJECUCIÓN','CERRADA POR FIN CONTRATO')
                          NOT NULL DEFAULT 'ABIERTA'",
    ],
    [
        'tabla'       => 'tbl_cronog_capacitacion',
        'campo'       => 'estado',
        'descripcion' => 'Cronograma de Capacitación',
        'sql'         => "ALTER TABLE tbl_cronog_capacitacion
                          MODIFY COLUMN estado
                          ENUM('PROGRAMADA','EJECUTADA','CANCELADA POR EL CLIENTE','REPROGRAMADA','CERRADA POR FIN CONTRATO')
                          NOT NULL DEFAULT 'PROGRAMADA'",
    ],
    [
        'tabla'       => 'tbl_vencimientos_mantenimientos',
        'campo'       => 'estado_actividad',
        'descripcion' => 'Vencimientos y Mantenimientos',
        'sql'         => "ALTER TABLE tbl_vencimientos_mantenimientos
                          MODIFY COLUMN estado_actividad
                          ENUM('sin ejecutar','ejecutado','CERRADA POR FIN CONTRATO')
                          NOT NULL DEFAULT 'sin ejecutar'",
    ],
    [
        'tabla'       => 'tbl_pendientes',
        'campo'       => 'estado',
        'descripcion' => 'Pendientes',
        'sql'         => "ALTER TABLE tbl_pendientes
                          MODIFY COLUMN estado
                          ENUM('ABIERTA','CERRADA','SIN RESPUESTA DEL CLIENTE','CERRADA POR FIN CONTRATO')
                          NOT NULL DEFAULT 'ABIERTA'",
    ],
];

// Desactivar NO_ZERO_DATE y STRICT para permitir fechas 0000-00-00 en datos existentes
$pdo->exec("SET SESSION sql_mode = (SELECT REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE,', ''), ',NO_ZERO_DATE', ''))");
$pdo->exec("SET SESSION sql_mode = (SELECT REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_IN_DATE,', ''), ',NO_ZERO_IN_DATE', ''))");

$errores = 0;

foreach ($alteraciones as $item) {
    echo "→ Modificando [{$item['tabla']}.{$item['campo']}] ({$item['descripcion']}) ...\n";
    try {
        $pdo->exec($item['sql']);
        echo "  [OK] ALTER TABLE ejecutado correctamente.\n\n";
    } catch (PDOException $e) {
        // Si la tabla no existe en este entorno, es advertencia (no error)
        if (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
            echo "  [AVISO] Tabla no existe en este entorno — se omite (puede estar solo en producción).\n\n";
        } else {
            echo "  [ERROR] Falló: " . $e->getMessage() . "\n\n";
            $errores++;
        }
    }
}

// ─── Resultado ────────────────────────────────────────────────────────────────
echo "======================================================\n";
if ($errores === 0) {
    echo "  RESULTADO: ÉXITO TOTAL (" . count($alteraciones) . "/" . count($alteraciones) . " tablas modificadas)\n";
    echo "  El valor 'CERRADA POR FIN CONTRATO' ya está disponible.\n";
} else {
    echo "  RESULTADO: {$errores} ERROR(ES) — revisa los mensajes anteriores.\n";
}
echo "======================================================\n\n";

exit($errores > 0 ? 1 : 0);
