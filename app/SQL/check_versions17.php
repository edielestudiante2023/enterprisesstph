<?php
$conn = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060
);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

// Ver todos los document_versions de cliente 17 con su acronym
$r = $conn->query("SELECT dv.policy_type_id, pt.type_name, dv.document_type, dv.acronym, dv.version_number, dv.location, dv.status FROM document_versions dv JOIN policy_types pt ON pt.id = dv.policy_type_id WHERE dv.client_id = 17 ORDER BY dv.policy_type_id");
echo "policy_type_id | type_name | doc_type | acronym | version | location | status" . PHP_EOL;
echo str_repeat('-', 100) . PHP_EOL;
while ($row = $r->fetch_assoc()) {
    echo "{$row['policy_type_id']} | {$row['type_name']} | {$row['document_type']} | {$row['acronym']} | {$row['version_number']} | {$row['location']} | {$row['status']}" . PHP_EOL;
}

$conn->close();
