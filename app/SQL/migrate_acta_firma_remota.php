<?php
/**
 * Agrega columnas de token para firma remota por WhatsApp en tbl_acta_visita
 * Uso: php app/SQL/migrate_acta_firma_remota.php [production]
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
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a [{$env}] {$db}\n";

    $sqls = [
        "ALTER TABLE tbl_acta_visita ADD COLUMN IF NOT EXISTS token_firma_remota VARCHAR(64) NULL DEFAULT NULL AFTER firma_consultor",
        "ALTER TABLE tbl_acta_visita ADD COLUMN IF NOT EXISTS token_firma_tipo VARCHAR(20) NULL DEFAULT NULL AFTER token_firma_remota",
        "ALTER TABLE tbl_acta_visita ADD COLUMN IF NOT EXISTS token_firma_expiracion DATETIME NULL DEFAULT NULL AFTER token_firma_tipo",
    ];

    foreach ($sqls as $sql) {
        $pdo->exec($sql);
        echo "OK: " . substr($sql, 0, 70) . "...\n";
    }

    echo "\nMigración completada.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
