<?php
/**
 * Script CLI: Renovar token de firma de un contrato
 * Uso: php app/Scripts/renovar_token_contrato.php <id_contrato> [production]
 */

$idContrato = $argv[1] ?? null;
$env = $argv[2] ?? 'local';

if (!$idContrato || !is_numeric($idContrato)) {
    echo "Uso: php app/Scripts/renovar_token_contrato.php <id_contrato> [production]\n";
    exit(1);
}

// Conexión DB
if ($env === 'production') {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $user     = 'cycloid_userdb';
    $pass     = getenv('DB_PROD_PASS');
    $dbname   = 'propiedad_horizontal';
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $user   = 'root';
    $pass   = '';
    $dbname = 'propiedad_horizontal';
}

$mysqli = new mysqli($host, $user, $pass, $dbname, $port);
if ($env === 'production') {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

if ($mysqli->connect_error) {
    echo "Error de conexión: " . $mysqli->connect_error . "\n";
    exit(1);
}

// Ver estado actual
$stmt = $mysqli->prepare("
    SELECT id_contrato, numero_contrato, estado_firma, token_firma,
           token_firma_expiracion, NOW() as ahora
    FROM tbl_contratos WHERE id_contrato = ?
");
$stmt->bind_param('i', $idContrato);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo "Contrato #$idContrato no encontrado.\n";
    exit(1);
}

echo "\n=== CONTRATO #{$row['id_contrato']} ===\n";
echo "Número:      {$row['numero_contrato']}\n";
echo "Estado firma: {$row['estado_firma']}\n";
echo "Token actual: " . ($row['token_firma'] ? substr($row['token_firma'], 0, 16) . '...' : 'NULL') . "\n";
echo "Expira:      " . ($row['token_firma_expiracion'] ?? 'NULL') . "\n";
echo "Ahora:       {$row['ahora']}\n";

$expirado = !empty($row['token_firma_expiracion'])
    && strtotime($row['token_firma_expiracion']) < time();
echo "¿Expirado?   " . ($expirado ? 'SÍ' : 'NO') . "\n";

// Generar nuevo token
$nuevoToken   = bin2hex(random_bytes(32));
$nuevaExpiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

$upd = $mysqli->prepare("
    UPDATE tbl_contratos
    SET token_firma = ?, token_firma_expiracion = ?, estado_firma = 'pendiente_firma'
    WHERE id_contrato = ?
");
$upd->bind_param('ssi', $nuevoToken, $nuevaExpiracion, $idContrato);
$upd->execute();

echo "\n✓ Token renovado exitosamente.\n";
echo "Nuevo token:   " . substr($nuevoToken, 0, 16) . "...\n";
echo "Nueva exp.:    $nuevaExpiracion\n";
echo "URL firma:     https://phorizontal.cycloidtalent.com/contrato/firmar/$nuevoToken\n";
echo "\nNOTA: El email NO fue re-enviado. Usa el panel admin para reenviar.\n\n";
