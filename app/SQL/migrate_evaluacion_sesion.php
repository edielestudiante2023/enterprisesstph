<?php
/**
 * Crea tabla tbl_evaluacion_sesion para agrupar respuestas por cliente+fecha
 * Uso: php app/SQL/migrate_evaluacion_sesion.php [production]
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
} else {
    $host = '127.0.0.1'; $port = 3306;
    $db   = 'propiedad_horizontal'; $user = 'root'; $pass = ''; $ssl = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }

$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n";

$pdo->exec("
    CREATE TABLE IF NOT EXISTS tbl_evaluacion_sesion (
        id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_evaluacion   INT UNSIGNED NOT NULL,
        id_cliente      INT UNSIGNED NOT NULL,
        fecha_sesion    DATE NOT NULL,
        codigo          VARCHAR(20) NOT NULL,
        created_at      DATETIME NULL,
        updated_at      DATETIME NULL,
        UNIQUE KEY uk_codigo (codigo),
        UNIQUE KEY uk_sesion (id_evaluacion, id_cliente, fecha_sesion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "OK: tbl_evaluacion_sesion creada\n";

// Generar sesiones retroactivas desde respuestas existentes
$rows = $pdo->query("
    SELECT DISTINCT id_evaluacion, id_cliente_conjunto, DATE(created_at) as fecha
    FROM tbl_evaluacion_induccion_respuesta
    WHERE id_cliente_conjunto IS NOT NULL AND id_cliente_conjunto > 0
")->fetchAll(PDO::FETCH_ASSOC);

$insert = $pdo->prepare("
    INSERT IGNORE INTO tbl_evaluacion_sesion (id_evaluacion, id_cliente, fecha_sesion, codigo, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

foreach ($rows as $r) {
    // Generar código único: EV-YYYY-NNNN
    $anio = date('Y', strtotime($r['fecha']));
    $seq  = $pdo->query("SELECT COUNT(*)+1 FROM tbl_evaluacion_sesion WHERE codigo LIKE 'EV-{$anio}-%'")->fetchColumn();
    $codigo = 'EV-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    $insert->execute([$r['id_evaluacion'], $r['id_cliente_conjunto'], $r['fecha'], $codigo]);
    echo "Sesión retroactiva: eval={$r['id_evaluacion']} cliente={$r['id_cliente_conjunto']} fecha={$r['fecha']} → {$codigo}\n";
}

echo "\nMigración completada.\n";
