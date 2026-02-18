<?php
/**
 * Migración: Agregar campo codigo_verificacion a tbl_contratos
 * Uso: php app/SQL/migrate_codigo_verificacion.php [local|production]
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => false,
    ],
    'production' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => 'empresas_sst',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php migrate_codigo_verificacion.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración codigo_verificacion - Entorno: {$env} ===\n\n";

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);

if ($cfg['ssl'] ?? false) {
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
}

if ($conn->connect_error) {
    echo "ERROR de conexión: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

// Verificar si la columna ya existe
$result = $conn->query("DESCRIBE tbl_contratos");
$columnas = [];
while ($row = $result->fetch_assoc()) {
    $columnas[] = $row['Field'];
}

$ok = 0;
$errors = 0;

if (!in_array('codigo_verificacion', $columnas)) {
    $sql = "ALTER TABLE tbl_contratos ADD COLUMN codigo_verificacion VARCHAR(12) NULL AFTER firma_cliente_fecha";
    if ($conn->query($sql)) {
        echo "[OK] Columna codigo_verificacion agregada\n";
        $ok++;
    } else {
        echo "[ERROR] " . $conn->error . "\n";
        $errors++;
    }
} else {
    echo "[SKIP] Columna codigo_verificacion ya existe\n";
    $ok++;
}

// Generar códigos para contratos ya firmados que no tengan código
$firmados = $conn->query("SELECT id_contrato, firma_cliente_cedula FROM tbl_contratos WHERE estado_firma='firmado' AND (codigo_verificacion IS NULL OR codigo_verificacion='')");
$count = 0;
if ($firmados && $firmados->num_rows > 0) {
    while ($row = $firmados->fetch_assoc()) {
        // Generar código basado en id_contrato + cédula (sin token porque ya se consumió)
        $hash = hash('sha256', 'contrato_legacy|' . $row['id_contrato'] . '|' . ($row['firma_cliente_cedula'] ?? ''));
        $codigo = strtoupper(substr($hash, 0, 12));
        $stmt = $conn->prepare("UPDATE tbl_contratos SET codigo_verificacion=? WHERE id_contrato=?");
        $stmt->bind_param('si', $codigo, $row['id_contrato']);
        if ($stmt->execute()) {
            $count++;
        }
        $stmt->close();
    }
    echo "[OK] Códigos generados para {$count} contratos previamente firmados\n";
    $ok++;
} else {
    echo "[SKIP] No hay contratos firmados sin código de verificación\n";
}

echo "\n=== Resultado: {$ok} OK, {$errors} errores ===\n";
$conn->close();
