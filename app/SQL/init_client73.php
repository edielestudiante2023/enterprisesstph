<?php
/**
 * Inicializa los documentos del cliente 73 copiando la estructura del cliente 17.
 * Crea registros en client_policies y document_versions para cada policy_type.
 * Uso: php init_client73.php
 */

$conn = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060
);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

$target_client_id = 73;
$source_client_id = 17;
$now = date('Y-m-d H:i:s');

echo "=== Inicializando documentos para cliente $target_client_id ===" . PHP_EOL;

// Verificar que el cliente 73 exista
$r = $conn->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE id_cliente = $target_client_id");
$client = $r->fetch_assoc();
if (!$client) {
    die("Error: Cliente $target_client_id no existe." . PHP_EOL);
}
echo "Cliente: {$client['nombre_cliente']}" . PHP_EOL . PHP_EOL;

// Verificar que no tenga ya registros (evitar duplicados)
$r = $conn->query("SELECT COUNT(*) as total FROM client_policies WHERE client_id = $target_client_id");
$existing = $r->fetch_assoc()['total'];
if ($existing > 0) {
    die("El cliente $target_client_id ya tiene $existing registros en client_policies. Abortando." . PHP_EOL);
}

// Obtener todos los document_versions de cliente 17 (estructura a copiar)
$r = $conn->query("SELECT * FROM document_versions WHERE client_id = $source_client_id ORDER BY policy_type_id");
$source_versions = [];
while ($row = $r->fetch_assoc()) {
    $source_versions[$row['policy_type_id']] = $row;
}

// Obtener todos los client_policies de cliente 17
$r = $conn->query("SELECT * FROM client_policies WHERE client_id = $source_client_id ORDER BY policy_type_id");
$source_policies = [];
while ($row = $r->fetch_assoc()) {
    $source_policies[$row['policy_type_id']] = $row;
}

echo "Policy types a inicializar: " . count($source_policies) . PHP_EOL . PHP_EOL;

$conn->begin_transaction();
try {
    $policies_created = 0;
    $versions_created = 0;

    foreach ($source_policies as $policy_type_id => $src_policy) {
        // INSERT client_policies
        $stmt = $conn->prepare("INSERT INTO client_policies (client_id, policy_type_id, policy_content, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iisss', $target_client_id, $policy_type_id, $src_policy['policy_content'], $now, $now);
        $stmt->execute();
        $policies_created++;

        // INSERT document_versions si existe para este policy_type
        if (isset($source_versions[$policy_type_id])) {
            $sv = $source_versions[$policy_type_id];
            $stmt2 = $conn->prepare("INSERT INTO document_versions (client_id, policy_type_id, version_number, created_at, updated_at, document_type, acronym, location, status, change_control) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $change_control = "Elaborado por Cycloid Talent el " . date('j \d\e F \d\e Y', strtotime($now));
            $stmt2->bind_param('iiisssssss',
                $target_client_id,
                $policy_type_id,
                $sv['version_number'],
                $now,
                $now,
                $sv['document_type'],
                $sv['acronym'],
                $sv['location'],
                $sv['status'],
                $change_control
            );
            $stmt2->execute();
            $versions_created++;
        }
    }

    $conn->commit();
    echo "OK: $policies_created registros en client_policies" . PHP_EOL;
    echo "OK: $versions_created registros en document_versions" . PHP_EOL;
    echo PHP_EOL . "Cliente $target_client_id inicializado correctamente." . PHP_EOL;

} catch (Exception $e) {
    $conn->rollback();
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}

$conn->close();
