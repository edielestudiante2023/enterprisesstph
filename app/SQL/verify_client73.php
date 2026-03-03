<?php
$conn = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060
);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

$r = $conn->query("SELECT COUNT(*) as total FROM client_policies WHERE client_id = 73");
echo "client_policies (73): " . $r->fetch_assoc()['total'] . PHP_EOL;

$r = $conn->query("SELECT COUNT(*) as total FROM document_versions WHERE client_id = 73");
echo "document_versions (73): " . $r->fetch_assoc()['total'] . PHP_EOL;

// Verificar el policy_type_id=1 específicamente
$r = $conn->query("SELECT cp.id, cp.policy_type_id, dv.acronym, dv.document_type FROM client_policies cp JOIN document_versions dv ON dv.client_id = cp.client_id AND dv.policy_type_id = cp.policy_type_id WHERE cp.client_id = 73 AND cp.policy_type_id = 1");
echo PHP_EOL . "policy_type_id=1 para cliente 73:" . PHP_EOL;
$row = $r->fetch_assoc();
if ($row) echo "  cp.id={$row['id']}, dv.acronym={$row['acronym']}, dv.doc_type={$row['document_type']}" . PHP_EOL;
else echo "  NO ENCONTRADO" . PHP_EOL;

$conn->close();
