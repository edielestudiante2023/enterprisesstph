<?php
$conn = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060
);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

// Ver estructura
$r = $conn->query('SHOW COLUMNS FROM tbl_contratos');
echo '--- tbl_contratos COLUMNS ---' . PHP_EOL;
while ($col = $r->fetch_assoc()) {
    echo $col['Field'] . ' | ' . $col['Type'] . ' | NULL:' . $col['Null'] . ' | Default:' . $col['Default'] . PHP_EOL;
}

// Ver un contrato de referencia (cliente 17)
$r2 = $conn->query('SELECT * FROM tbl_contratos WHERE id_cliente = 17 LIMIT 1');
$row = $r2->fetch_assoc();
if ($row) {
    echo PHP_EOL . '--- Ejemplo contrato cliente 17 ---' . PHP_EOL;
    foreach ($row as $k => $v) {
        echo "$k: $v" . PHP_EOL;
    }
}

// Verificar si cliente 73 ya tiene contrato
$r3 = $conn->query('SELECT * FROM tbl_contratos WHERE id_cliente = 73');
echo PHP_EOL . '--- Contratos cliente 73 ---' . PHP_EOL;
$found = false;
while ($row = $r3->fetch_assoc()) {
    $found = true;
    foreach ($row as $k => $v) echo "$k: $v" . PHP_EOL;
}
if (!$found) echo 'Ninguno' . PHP_EOL;

$conn->close();
