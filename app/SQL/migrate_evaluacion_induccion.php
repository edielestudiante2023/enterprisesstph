<?php
/**
 * Migración: crear tablas tbl_evaluacion_induccion y tbl_evaluacion_induccion_respuesta
 * Uso: php app/SQL/migrate_evaluacion_induccion.php [production]
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $name = 'propiedad_horizontal';
    $port = 25060;

    if (!$pass) {
        echo "ERROR: DB_PROD_PASS env var not set\n";
        exit(1);
    }

    $db = mysqli_init();
    $db->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $db->real_connect($host, $user, $pass, $name, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    $db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
}

if ($db->connect_error) {
    echo "Connection failed: {$db->connect_error}\n";
    exit(1);
}

echo "Connected to [{$env}]\n";

$queries = [
    'tbl_evaluacion_induccion' => "CREATE TABLE IF NOT EXISTS tbl_evaluacion_induccion (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_asistencia_induccion INT UNSIGNED NULL,
        id_cliente INT UNSIGNED NULL,
        titulo VARCHAR(255) NOT NULL DEFAULT 'Evaluación Inducción SST',
        token VARCHAR(64) NOT NULL,
        estado ENUM('activa','cerrada') NOT NULL DEFAULT 'activa',
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        UNIQUE KEY uk_token (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'tbl_evaluacion_induccion_respuesta' => "CREATE TABLE IF NOT EXISTS tbl_evaluacion_induccion_respuesta (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_evaluacion INT UNSIGNED NOT NULL,
        nombre VARCHAR(255) NOT NULL,
        cedula VARCHAR(20) NOT NULL,
        whatsapp VARCHAR(20) NULL,
        empresa_contratante VARCHAR(255) NULL,
        cargo VARCHAR(255) NULL,
        id_cliente_conjunto INT UNSIGNED NULL,
        acepta_tratamiento TINYINT(1) NOT NULL DEFAULT 0,
        respuestas JSON NULL,
        calificacion DECIMAL(5,2) NOT NULL DEFAULT 0,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        KEY idx_evaluacion (id_evaluacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
];

foreach ($queries as $table => $sql) {
    if ($db->query($sql)) {
        echo "{$table}: OK\n";
    } else {
        echo "{$table}: ERROR - {$db->error}\n";
    }
}

$db->close();
echo "Done.\n";
