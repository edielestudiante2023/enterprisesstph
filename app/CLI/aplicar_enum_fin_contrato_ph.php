<?php
/**
 * Script CLI: Agregar CERRADA POR FIN CONTRATO a los ENUMs de propiedad_horizontal.
 *
 * Uso:
 *   php app/CLI/aplicar_enum_fin_contrato_ph.php local
 *   php app/CLI/aplicar_enum_fin_contrato_ph.php prod TU_PASSWORD
 */

$entorno = $argv[1] ?? null;

if (!in_array($entorno, ['local', 'prod'])) {
    echo "\n[ERROR] Debes especificar el entorno: local o prod\n";
    echo "Ejemplo: php app/CLI/aplicar_enum_fin_contrato_ph.php local\n\n";
    exit(1);
}

if ($entorno === 'local') {
    $host   = '127.0.0.1';
    $port   = '3306';
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
} else {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = '25060';
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = $argv[2] ?? getenv('PROD_DB_PASS') ?: '';
    $ssl    = true;
    if (empty($pass)) {
        echo "\n[ERROR] Para prod debes pasar la contraseña como 2do argumento.\n\n";
        exit(1);
    }
}

$dsn     = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
if ($ssl) {
    $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opciones[PDO::MYSQL_ATTR_SSL_CA]                 = true;
}

echo "\n======================================================\n";
echo "  APLICAR ENUM: CERRADA POR FIN CONTRATO (PH)\n";
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

// Desactivar restricciones de fechas para no romper filas con 0000-00-00
$pdo->exec("SET SESSION sql_mode = (SELECT REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE,', ''), ',NO_ZERO_DATE', ''))");
$pdo->exec("SET SESSION sql_mode = (SELECT REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_IN_DATE,', ''), ',NO_ZERO_IN_DATE', ''))");

$alteraciones = [
    [
        'tabla'       => 'tbl_pta_cliente',
        'campo'       => 'estado_actividad',
        'descripcion' => 'Plan de Trabajo Anual',
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

$errores = 0;

foreach ($alteraciones as $item) {
    echo "→ [{$item['tabla']}.{$item['campo']}] ({$item['descripcion']}) ...\n";
    try {
        $pdo->exec($item['sql']);
        echo "  [OK] ALTER TABLE ejecutado correctamente.\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            echo "  [AVISO] Tabla no existe en este entorno — se omite.\n\n";
        } else {
            echo "  [ERROR] " . $e->getMessage() . "\n\n";
            $errores++;
        }
    }
}

echo "======================================================\n";
if ($errores === 0) {
    echo "  RESULTADO: ÉXITO TOTAL (" . count($alteraciones) . "/" . count($alteraciones) . " tablas)\n";
    echo "  'CERRADA POR FIN CONTRATO' ya disponible en BD.\n";
} else {
    echo "  RESULTADO: {$errores} ERROR(ES).\n";
}
echo "======================================================\n\n";

exit($errores > 0 ? 1 : 0);
