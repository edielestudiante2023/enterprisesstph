<?php
$conn = new mysqli(
  'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
  'cycloid_userdb',
  getenv('DB_PROD_PASS'),
  'propiedad_horizontal',
  25060
);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

// Estructura de client_policies (muestra 3 rows de cliente 17)
$r = $conn->query('SELECT * FROM client_policies WHERE client_id = 17 LIMIT 3');
echo '--- client_policies columns ---' . PHP_EOL;
$row = $r->fetch_assoc();
echo implode(' | ', array_keys($row)) . PHP_EOL;
echo implode(' | ', array_values($row)) . PHP_EOL;
$row2 = $r->fetch_assoc();
echo implode(' | ', array_values($row2)) . PHP_EOL;

// Estructura de document_versions
$r2 = $conn->query('SELECT * FROM document_versions WHERE client_id = 17 LIMIT 3');
echo PHP_EOL . '--- document_versions columns ---' . PHP_EOL;
$row3 = $r2->fetch_assoc();
echo implode(' | ', array_keys($row3)) . PHP_EOL;
echo implode(' | ', array_values($row3)) . PHP_EOL;
$row4 = $r2->fetch_assoc();
echo implode(' | ', array_values($row4)) . PHP_EOL;

// Todos los policy_types (columnas reales)
$r3 = $conn->query('SHOW COLUMNS FROM policy_types');
echo PHP_EOL . '--- policy_types COLUMNS ---' . PHP_EOL;
while ($col = $r3->fetch_assoc()) {
    echo $col['Field'] . PHP_EOL;
}

$r4 = $conn->query('SELECT * FROM policy_types ORDER BY id LIMIT 10');
echo PHP_EOL . '--- policy_types data ---' . PHP_EOL;
$first = $r4->fetch_assoc();
if ($first) {
    echo implode(' | ', array_keys($first)) . PHP_EOL;
    echo implode(' | ', array_values($first)) . PHP_EOL;
    while ($row = $r4->fetch_assoc()) {
        echo implode(' | ', array_values($row)) . PHP_EOL;
    }
}

// Cuantos policy_types existen
$r5 = $conn->query('SELECT COUNT(*) as total FROM policy_types');
echo PHP_EOL . 'Total policy_types: ' . $r5->fetch_assoc()['total'] . PHP_EOL;

// policy_types que tiene cliente 17 en client_policies
$r6 = $conn->query('SELECT DISTINCT policy_type_id FROM client_policies WHERE client_id = 17 ORDER BY policy_type_id');
echo PHP_EOL . 'policy_type_ids de cliente 17: ';
$ids = [];
while ($row = $r6->fetch_assoc()) $ids[] = $row['policy_type_id'];
echo implode(', ', $ids) . PHP_EOL;

$conn->close();
