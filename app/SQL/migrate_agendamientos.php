<?php
/**
 * Migración: Crear tabla tbl_agendamientos
 *
 * Uso:
 *   LOCAL:       php app/SQL/migrate_agendamientos.php
 *   PRODUCCIÓN:  DB_PROD_PASS=xxx php app/SQL/migrate_agendamientos.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'propiedad_horizontal';

    if (!$pass) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=tu_password php app/SQL/migrate_agendamientos.php production\n";
        exit(1);
    }

    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    // SSL requerido en DigitalOcean
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli = new mysqli();
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->real_connect($host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
} else {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'propiedad_horizontal';
    $mysqli = new mysqli($host, $user, $pass, $db);
}

if ($mysqli->connect_error) {
    echo "ERROR de conexión: " . $mysqli->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$env} ({$host}:{$db})\n";

// ── Crear tabla tbl_agendamientos ──
$sql = "
CREATE TABLE IF NOT EXISTS tbl_agendamientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_visita DATE NOT NULL,
    hora_visita TIME NOT NULL,
    frecuencia ENUM('mensual','bimensual','trimestral') NOT NULL DEFAULT 'mensual',
    estado ENUM('pendiente','confirmado','completado','cancelado') NOT NULL DEFAULT 'pendiente',
    confirmacion_calendar VARCHAR(255) NULL COMMENT 'ID evento calendar o texto de confirmacion',
    preparacion_cliente TEXT NULL COMMENT 'Notas de preparacion del cliente',
    observaciones TEXT NULL,
    email_enviado TINYINT(1) NOT NULL DEFAULT 0,
    fecha_email_enviado DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_cliente (id_cliente),
    INDEX idx_consultor (id_consultor),
    INDEX idx_fecha (fecha_visita),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($mysqli->query($sql)) {
    echo "OK: tbl_agendamientos creada (o ya existía).\n";
} else {
    echo "ERROR: " . $mysqli->error . "\n";
    exit(1);
}

// Verificar estructura
$result = $mysqli->query("DESCRIBE tbl_agendamientos");
echo "\nEstructura de tbl_agendamientos:\n";
echo str_pad("Campo", 30) . str_pad("Tipo", 25) . "Null\n";
echo str_repeat("-", 65) . "\n";
while ($row = $result->fetch_assoc()) {
    echo str_pad($row['Field'], 30) . str_pad($row['Type'], 25) . $row['Null'] . "\n";
}

$mysqli->close();
echo "\nMigración completada en {$env}.\n";
