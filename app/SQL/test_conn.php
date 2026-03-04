<?php
$pass = getenv('DB_PROD_PASS');
if (!$pass) die("Set DB_PROD_PASS\n");

echo "Testing connection...\n";
try {
    $pdo = new PDO(
        'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal',
        'cycloid_userdb',
        $pass,
        [
            PDO::MYSQL_ATTR_SSL_CA => true,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_TIMEOUT => 30,
        ]
    );
    echo "Connected OK!\n";
    $stmt = $pdo->query("SELECT 1 as test");
    echo "Query OK: " . $stmt->fetch(PDO::FETCH_ASSOC)['test'] . "\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
