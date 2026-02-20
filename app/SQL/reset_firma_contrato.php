<?php
/**
 * Script: Resetear firma de prueba de un contrato
 * Uso: DB_PROD_PASS=xxx php app/SQL/reset_firma_contrato.php production 74
 */

$env = $argv[1] ?? 'local';
$idContrato = $argv[2] ?? null;

if (!$idContrato) {
    echo "Uso: php reset_firma_contrato.php [local|production] <id_contrato>\n";
    echo "Ejemplo: DB_PROD_PASS=xxx php app/SQL/reset_firma_contrato.php production 74\n";
    exit(1);
}

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
        'db'   => 'propiedad_horizontal',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Entorno no válido. Usa: local | production\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Reset firma contrato #{$idContrato} - Entorno: {$env} ===\n\n";

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

// Verificar contrato actual
$stmt = $conn->prepare("SELECT id_contrato, estado_firma, firma_cliente_nombre, firma_cliente_cedula, firma_cliente_imagen, firma_cliente_fecha, codigo_verificacion FROM tbl_contratos WHERE id_contrato = ?");
$stmt->bind_param('i', $idContrato);
$stmt->execute();
$result = $stmt->get_result();
$contrato = $result->fetch_assoc();
$stmt->close();

if (!$contrato) {
    echo "ERROR: Contrato #{$idContrato} no encontrado\n";
    $conn->close();
    exit(1);
}

echo "Estado actual del contrato:\n";
echo "  - estado_firma: {$contrato['estado_firma']}\n";
echo "  - firma_nombre: {$contrato['firma_cliente_nombre']}\n";
echo "  - firma_cedula: {$contrato['firma_cliente_cedula']}\n";
echo "  - firma_imagen: {$contrato['firma_cliente_imagen']}\n";
echo "  - firma_fecha:  {$contrato['firma_cliente_fecha']}\n";
echo "  - codigo_verif: {$contrato['codigo_verificacion']}\n\n";

// Resetear campos de firma
$sql = "UPDATE tbl_contratos SET
    estado_firma = 'pendiente',
    firma_cliente_nombre = NULL,
    firma_cliente_cedula = NULL,
    firma_cliente_imagen = NULL,
    firma_cliente_ip = NULL,
    firma_cliente_fecha = NULL,
    codigo_verificacion = NULL
    WHERE id_contrato = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idContrato);

if ($stmt->execute()) {
    echo "[OK] Firma del contrato #{$idContrato} reseteada correctamente\n";
    echo "     Filas afectadas: " . $stmt->affected_rows . "\n";
} else {
    echo "[ERROR] " . $conn->error . "\n";
}

$stmt->close();
$conn->close();
