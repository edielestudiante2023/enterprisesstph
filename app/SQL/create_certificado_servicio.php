<?php
/**
 * Migración: crear tablas para módulos de certificados de servicio y planilla SS
 *
 * Uso:
 *   php app/SQL/create_certificado_servicio.php            (local)
 *   DB_PROD_PASS=xxx php app/SQL/create_certificado_servicio.php production
 */

$isProduction = (isset($argv[1]) && $argv[1] === 'production');

if ($isProduction) {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = 25060;
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    $ssl    = true;
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $dbname = 'empresas_sst';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $opts);

    // Tabla tbl_certificado_servicio
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_certificado_servicio (
            id                INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente        INT NOT NULL,
            id_mantenimiento  INT NOT NULL COMMENT '2=Lavado Tanques, 3=Fumigacion, 4=Desratizacion',
            fecha_servicio    DATE NOT NULL,
            archivo           VARCHAR(500) NULL,
            observaciones     TEXT NULL,
            id_consultor      INT NULL,
            id_vencimiento    INT NULL COMMENT 'FK tbl_vencimientos_mantenimientos, nullable',
            created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cliente_mant (id_cliente, id_mantenimiento),
            INDEX idx_fecha (fecha_servicio)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK: tbl_certificado_servicio creada (o ya existía)\n";

    // Tabla tbl_planilla_ss_inspeccion
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_planilla_ss_inspeccion (
            id             INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente     INT NOT NULL,
            periodo        VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
            archivo        VARCHAR(500) NULL,
            observaciones  TEXT NULL,
            id_consultor   INT NULL,
            created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cliente (id_cliente),
            INDEX idx_periodo (periodo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK: tbl_planilla_ss_inspeccion creada (o ya existía)\n";

    echo "\nMigración completada exitosamente.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
