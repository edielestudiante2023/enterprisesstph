<?php
/**
 * Migración: convertir tbl_inspeccion_frecuencia_cliente
 * de columna ENUM `frecuencia` a INT UNSIGNED `veces_anio`.
 *
 * El consultor prefiere expresar "cuántas veces al año" como un entero crudo
 * (más flexible que las categorías estándar mensual/bimensual/etc.).
 *
 * Como la tabla no tiene aún datos productivos, recreamos limpia.
 *
 * USO:
 *   Local:        php app/SQL/alter_inspeccion_frecuencia_to_veces_anio.php
 *   Producción:   php app/SQL/alter_inspeccion_frecuencia_to_veces_anio.php production
 *
 * Idempotente.
 */

require __DIR__ . '/../../vendor/autoload.php';

$entorno = $argv[1] ?? 'local';

if ($entorno === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $database = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: define DB_PROD_PASS en el entorno antes de ejecutar.\n";
        exit(1);
    }
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $opts = [
        PDO::MYSQL_ATTR_SSL_CA => null,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $database = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a {$entorno} ({$database}@{$host})\n";

    // ¿Ya tiene veces_anio?
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=:db AND table_name='tbl_inspeccion_frecuencia_cliente' AND column_name='veces_anio'");
    $stmt->execute([':db' => $database]);
    if ((int) $stmt->fetchColumn() > 0) {
        echo "OK: columna veces_anio ya existe — nada que hacer.\n";
        exit(0);
    }

    // ¿Hay datos en la tabla actual?
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_inspeccion_frecuencia_cliente");
    $rowCount = (int) $stmt->fetchColumn();
    echo "Filas actuales en la tabla: {$rowCount}\n";

    if ($rowCount > 0) {
        echo "⚠ Existen datos. Aborto para evitar pérdida. Limpia manualmente o coordina migración.\n";
        exit(1);
    }

    $pdo->exec("DROP TABLE tbl_inspeccion_frecuencia_cliente");
    echo "OK: tabla anterior eliminada.\n";

    $sql = "
        CREATE TABLE tbl_inspeccion_frecuencia_cliente (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT UNSIGNED NOT NULL,
            slug_inspeccion VARCHAR(80) NOT NULL,
            veces_anio INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = puntual / sin frecuencia fija',
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            UNIQUE KEY uniq_cliente_slug (id_cliente, slug_inspeccion),
            INDEX idx_cliente (id_cliente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($sql);
    echo "OK: tabla recreada con columna veces_anio.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
