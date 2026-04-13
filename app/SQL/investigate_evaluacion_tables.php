<?php
/**
 * Investiga tablas tbl_evaluacion* en propiedad_horizontal.
 * Uso: DB_PROD_PASS=xxx php app/SQL/investigate_evaluacion_tables.php
 * SOLO LECTURA.
 */

$pass = getenv('DB_PROD_PASS');
if (!$pass) { fwrite(STDERR, "ERROR: define DB_PROD_PASS\n"); exit(1); }

$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$db   = 'propiedad_horizontal';
$user = 'cycloid_userdb';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db",
    $user, $pass,
    [
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
);
echo "Conectado OK.\n\n";

// 1. Tablas que empiecen con tbl_evaluacion
$stmt = $pdo->prepare(
    "SELECT TABLE_NAME, TABLE_TYPE
       FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = :db
        AND TABLE_NAME LIKE 'tbl_evaluacion%'
      ORDER BY TABLE_NAME"
);
$stmt->execute([':db' => $db]);
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== TABLAS tbl_evaluacion* ===\n";
foreach ($tables as $t) {
    echo " - {$t['TABLE_NAME']}  ({$t['TABLE_TYPE']})\n";
}
echo "\n";

// 2. Columnas de cada una
foreach ($tables as $t) {
    $name = $t['TABLE_NAME'];
    echo str_repeat('-', 70) . "\n";
    echo "### $name\n";
    echo str_repeat('-', 70) . "\n";
    $cs = $pdo->prepare(
        "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
           FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :t
          ORDER BY ORDINAL_POSITION"
    );
    $cs->execute([':db' => $db, ':t' => $name]);
    foreach ($cs->fetchAll(PDO::FETCH_ASSOC) as $c) {
        printf("  %-30s %-25s %-5s %s\n",
            $c['COLUMN_NAME'], $c['COLUMN_TYPE'],
            $c['IS_NULLABLE'], $c['COLUMN_KEY']);
    }
    // Conteo de filas (solo lectura)
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `$name`")->fetchColumn();
        echo "  -> filas: $cnt\n";
    } catch (Exception $e) {
        echo "  -> count fallo: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// 3. Columnas que la vista rota necesita
$needed = ['id','id_evaluacion','nombre','cedula','whatsapp','empresa_contratante',
           'cargo','id_cliente_conjunto','acepta_tratamiento','respuestas',
           'calificacion','created_at','updated_at','titulo'];
echo "=== Columnas requeridas por la vista vieja ===\n";
echo implode(', ', $needed) . "\n";

// 4. Buscar en qué tabla actual vive cada columna requerida
echo "\n=== Dónde vive cada columna requerida (entre tbl_evaluacion*) ===\n";
$in = implode(',', array_fill(0, count($needed), '?'));
$params = array_merge([$db], $needed);
$q = $pdo->prepare(
    "SELECT TABLE_NAME, COLUMN_NAME
       FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = ?
        AND TABLE_NAME LIKE 'tbl_evaluacion%'
        AND COLUMN_NAME IN ($in)
      ORDER BY COLUMN_NAME, TABLE_NAME"
);
$q->execute($params);
$found = [];
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $found[$row['COLUMN_NAME']][] = $row['TABLE_NAME'];
}
foreach ($needed as $col) {
    if (isset($found[$col])) {
        echo sprintf("  %-22s -> %s\n", $col, implode(', ', $found[$col]));
    } else {
        echo sprintf("  %-22s -> (NO encontrada en tbl_evaluacion*)\n", $col);
    }
}

echo "\nListo.\n";
