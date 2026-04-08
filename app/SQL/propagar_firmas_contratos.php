<?php
/**
 * Consulta clientes con firma de protocolo alturas y propaga a contratos sin firma.
 *
 * Uso:
 *   php app/SQL/propagar_firmas_contratos.php              → LOCAL (solo consulta)
 *   php app/SQL/propagar_firmas_contratos.php production    → PRODUCCIÓN (solo consulta)
 *   php app/SQL/propagar_firmas_contratos.php local apply   → LOCAL (aplica cambios)
 *   php app/SQL/propagar_firmas_contratos.php production apply → PRODUCCIÓN (aplica cambios)
 */

$env = $argv[1] ?? 'local';
$apply = ($argv[2] ?? '') === 'apply';

if ($env === 'production') {
    $config = [
        'hostname' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'username' => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS'),
        'database' => 'propiedad_horizontal',
        'port'     => 25060,
        'ssl'      => true,
    ];
    if (empty($config['password'])) {
        die("ERROR: Variable DB_PROD_PASS no definida. Uso: DB_PROD_PASS=xxx php app/SQL/propagar_firmas_contratos.php production\n");
    }
} else {
    $config = [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'propiedad_horizontal',
        'port'     => 3306,
        'ssl'      => false,
    ];
}

echo "=== Propagar Firmas Protocolo Alturas a Contratos ===\n";
echo "Entorno: " . strtoupper($env) . ($apply ? " [APLICANDO]" : " [SOLO CONSULTA]") . "\n\n";

$mysqli = new mysqli(
    $config['hostname'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['port']
);

if ($config['ssl']) {
    $mysqli = mysqli_init();
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->real_connect(
        $config['hostname'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port'],
        null,
        MYSQLI_CLIENT_SSL
    );
}

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conectado a {$config['database']}@{$config['hostname']}\n\n";

// 1. Clientes con firma de protocolo alturas
$sql = "SELECT id_cliente, nombre_cliente, firma_representante_legal, firma_alturas_fecha
        FROM tbl_clientes
        WHERE protocolo_alturas_firmado = 1
        AND firma_representante_legal IS NOT NULL
        AND firma_representante_legal != ''";
$result = $mysqli->query($sql);

if (!$result || $result->num_rows === 0) {
    echo "No hay clientes con firma de protocolo alturas.\n";
    $mysqli->close();
    exit(0);
}

echo "Clientes con firma protocolo alturas: {$result->num_rows}\n";
echo str_repeat('-', 80) . "\n";

$totalPropagadas = 0;

while ($cliente = $result->fetch_assoc()) {
    $id = $cliente['id_cliente'];
    $nombre = $cliente['nombre_cliente'];
    $firma = $cliente['firma_representante_legal'];
    $fechaFirma = $cliente['firma_alturas_fecha'];

    echo "\n[Cliente #{$id}] {$nombre}\n";
    echo "  Firma: {$firma}\n";
    echo "  Fecha: {$fechaFirma}\n";

    // Contratos sin firma
    $sqlContratos = "SELECT id_contrato, numero_contrato, estado, firma_cliente_imagen
                     FROM tbl_contratos
                     WHERE id_cliente = {$id}
                     AND (firma_cliente_imagen IS NULL OR firma_cliente_imagen = '')";
    $contratos = $mysqli->query($sqlContratos);

    // Contratos que YA tienen firma
    $sqlConFirma = "SELECT id_contrato, numero_contrato, firma_cliente_imagen
                    FROM tbl_contratos
                    WHERE id_cliente = {$id}
                    AND firma_cliente_imagen IS NOT NULL
                    AND firma_cliente_imagen != ''";
    $conFirma = $mysqli->query($sqlConFirma);

    if ($conFirma && $conFirma->num_rows > 0) {
        echo "  Contratos CON firma: {$conFirma->num_rows}\n";
        while ($c = $conFirma->fetch_assoc()) {
            echo "    - {$c['numero_contrato']}: {$c['firma_cliente_imagen']}\n";
        }
    }

    if (!$contratos || $contratos->num_rows === 0) {
        echo "  Sin contratos pendientes de firma.\n";
        continue;
    }

    echo "  Contratos SIN firma: {$contratos->num_rows}\n";

    while ($contrato = $contratos->fetch_assoc()) {
        $idContrato = $contrato['id_contrato'];
        $numContrato = $contrato['numero_contrato'] ?? "ID:{$idContrato}";
        $estado = $contrato['estado'];

        if ($apply) {
            $firmaEsc = $mysqli->real_escape_string($firma);
            $fechaEsc = $mysqli->real_escape_string($fechaFirma);
            $sqlUpdate = "UPDATE tbl_contratos
                          SET firma_cliente_imagen = '{$firmaEsc}',
                              firma_cliente_fecha = '{$fechaEsc}'
                          WHERE id_contrato = {$idContrato}";
            if ($mysqli->query($sqlUpdate)) {
                echo "    ✓ Propagado a contrato {$numContrato} (estado: {$estado})\n";
                $totalPropagadas++;
            } else {
                echo "    ✗ ERROR en contrato {$numContrato}: {$mysqli->error}\n";
            }
        } else {
            echo "    → Propagaría a contrato {$numContrato} (estado: {$estado})\n";
            $totalPropagadas++;
        }
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "Total contratos " . ($apply ? "propagados" : "a propagar") . ": {$totalPropagadas}\n";

if (!$apply && $totalPropagadas > 0) {
    echo "\nPara aplicar cambios, ejecuta:\n";
    echo "  php app/SQL/propagar_firmas_contratos.php {$env} apply\n";
}

$mysqli->close();
echo "\nListo.\n";
