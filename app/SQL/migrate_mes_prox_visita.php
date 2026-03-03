<?php
/**
 * Migración: Agregar columna mes_prox_visita a tbl_clientes
 *
 * Uso:
 *   Local:      php app/SQL/migrate_mes_prox_visita.php
 *   Producción: DB_PROD_PASS=xxx php app/SQL/migrate_mes_prox_visita.php production
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

// 1. Agregar columna si no existe
$result = $db->query("SHOW COLUMNS FROM tbl_clientes LIKE 'mes_prox_visita'");
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE tbl_clientes ADD COLUMN mes_prox_visita INT NULL AFTER email_consultor_externo";
    if ($db->query($sql)) {
        echo "Columna mes_prox_visita agregada.\n";
    } else {
        echo "Error al agregar columna: " . $db->error . "\n";
        exit(1);
    }
} else {
    echo "Columna mes_prox_visita ya existe.\n";
}

// 2. Cargar datos SOLO en producción
if ($isProduction) {
    $data = [
        71 => 3, 37 => 3, 22 => 3, 36 => 3, 65 => 3, 70 => 3, 61 => 3,
        54 => 3, 73 => 3, 55 => 3, 18 => 3, 35 => 3, 49 => 3, 63 => 3,
        31 => 3, 44 => 3, 67 => 3, 17 => 3, 20 => 3, 53 => 3, 39 => 3,
        24 => 3, 64 => 3, 50 => 3, 23 => 3, 30 => 3, 34 => 3, 25 => 3,
        59 => 4, 60 => 4, 32 => 4, 21 => 4, 46 => 4, 42 => 4, 29 => 4,
        62 => 4, 33 => 5, 56 => 5, 58 => 5, 57 => 5,
    ];

    $updated = 0;
    $errors = 0;
    foreach ($data as $id => $mes) {
        $sql = "UPDATE tbl_clientes SET mes_prox_visita = {$mes} WHERE id_cliente = {$id}";
        if ($db->query($sql)) {
            if ($db->affected_rows > 0) {
                $updated++;
            } else {
                echo "  AVISO: id_cliente={$id} no encontrado o ya tiene mes={$mes}\n";
            }
        } else {
            echo "  ERROR en id_cliente={$id}: " . $db->error . "\n";
            $errors++;
        }
    }
    echo "Datos cargados: {$updated} actualizados, {$errors} errores de " . count($data) . " registros.\n";
} else {
    echo "Modo local: no se cargan datos. Ejecute con 'production' para cargar.\n";
}

$db->close();
echo "Listo.\n";
