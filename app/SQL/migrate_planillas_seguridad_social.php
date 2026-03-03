<?php
/**
 * Migración: Crear tabla tbl_planillas_seguridad_social
 *
 * Uso:
 *   Local:      php app/SQL/migrate_planillas_seguridad_social.php
 *   Producción: DB_PROD_PASS=xxx php app/SQL/migrate_planillas_seguridad_social.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: DB_PROD_PASS no está definida.\n";
        exit(1);
    }
    $db = mysqli_init();
    mysqli_ssl_set($db, NULL, NULL, NULL, NULL, NULL);
    $db->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb', $pass, 'propiedad_horizontal', 25060,
        NULL, MYSQLI_CLIENT_SSL
    );
    echo "Conectado a PRODUCCIÓN\n";
} else {
    $db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
    echo "Conectado a LOCAL\n";
}

if ($db->connect_error) {
    echo "Error de conexión: " . $db->connect_error . "\n";
    exit(1);
}

// Verificar si la tabla ya existe
$result = $db->query("SHOW TABLES LIKE 'tbl_planillas_seguridad_social'");
if ($result->num_rows > 0) {
    echo "La tabla tbl_planillas_seguridad_social ya existe. No se realizan cambios.\n";
} else {
    $sql = "CREATE TABLE tbl_planillas_seguridad_social (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        mes_aportes VARCHAR(20) NOT NULL COMMENT 'Período YYYY-MM',
        archivo_pdf VARCHAR(255) NOT NULL COMMENT 'Nombre del archivo PDF subido',
        fecha_cargue DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de carga del archivo',
        cantidad_envios INT NOT NULL DEFAULT 0 COMMENT 'Cantidad de clientes a los que se envió',
        fecha_envio DATETIME NULL COMMENT 'Fecha del último envío masivo',
        estado_envio ENUM('sin_enviar','enviado') NOT NULL DEFAULT 'sin_enviar' COMMENT 'Estado del envío',
        notas TEXT NULL COMMENT 'Observaciones opcionales'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    if ($db->query($sql)) {
        echo "Tabla tbl_planillas_seguridad_social creada exitosamente.\n";
    } else {
        echo "Error al crear tabla: " . $db->error . "\n";
        exit(1);
    }
}

$db->close();
echo "Listo.\n";
