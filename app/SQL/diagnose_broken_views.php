<?php
/**
 * Diagnóstico de vistas rotas en propiedad_horizontal.
 * Uso: DB_PROD_PASS=xxx php app/SQL/diagnose_broken_views.php
 *
 * Recorre todas las vistas del esquema e intenta SHOW FIELDS en cada una.
 * Reporta cuáles fallan (el mismo error que rompe mysqldump) y la causa.
 * SOLO LECTURA: no modifica nada.
 */

$pass = getenv('DB_PROD_PASS');
if (!$pass) {
    fwrite(STDERR, "ERROR: define DB_PROD_PASS en el entorno.\n");
    exit(1);
}

$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$db   = 'propiedad_horizontal';
$user = 'cycloid_userdb';

echo "Conectando a $host:$port/$db ...\n";
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db",
        $user,
        $pass,
        [
            PDO::MYSQL_ATTR_SSL_CA => true,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
    echo "Conectado OK.\n\n";
} catch (Exception $e) {
    fwrite(STDERR, "FAILED conexion: " . $e->getMessage() . "\n");
    exit(2);
}

// 1. Listar todas las vistas
$sql = "SELECT TABLE_NAME
          FROM information_schema.VIEWS
         WHERE TABLE_SCHEMA = :db
         ORDER BY TABLE_NAME";
$stmt = $pdo->prepare($sql);
$stmt->execute([':db' => $db]);
$views = $stmt->fetchAll(PDO::FETCH_COLUMN);

$total = count($views);
echo "Total de vistas encontradas: $total\n";
echo str_repeat('-', 70) . "\n";

$ok = [];
$broken = [];

foreach ($views as $i => $view) {
    $n = $i + 1;
    try {
        // SHOW FIELDS es exactamente lo que hace mysqldump y lo que falla.
        $pdo->query("SHOW FIELDS FROM `$view`");
        $ok[] = $view;
        echo sprintf("[%3d/%d] OK     %s\n", $n, $total, $view);
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $broken[$view] = $msg;
        echo sprintf("[%3d/%d] BROKEN %s\n         -> %s\n", $n, $total, $view, $msg);
    }
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "RESUMEN\n";
echo str_repeat('=', 70) . "\n";
echo "Vistas OK:     " . count($ok) . "\n";
echo "Vistas ROTAS:  " . count($broken) . "\n\n";

if (!empty($broken)) {
    echo "--- VISTAS ROTAS (detalle) ---\n";
    foreach ($broken as $view => $msg) {
        echo "* $view\n  $msg\n\n";
    }

    // 2. Para cada vista rota, traer su definición (para ver qué tabla/columna referencia)
    echo str_repeat('=', 70) . "\n";
    echo "DEFINICIONES SQL de las vistas rotas\n";
    echo str_repeat('=', 70) . "\n";
    $defStmt = $pdo->prepare(
        "SELECT VIEW_DEFINITION, DEFINER, SECURITY_TYPE
           FROM information_schema.VIEWS
          WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :v"
    );
    foreach (array_keys($broken) as $view) {
        $defStmt->execute([':db' => $db, ':v' => $view]);
        $row = $defStmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "\n### $view\n";
            echo "DEFINER: {$row['DEFINER']}  SECURITY: {$row['SECURITY_TYPE']}\n";
            echo "SQL:\n" . $row['VIEW_DEFINITION'] . "\n";
        }
    }

    // 3. Sugerencia: statements DROP listos para copiar/pegar si decide quitarlas
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "DROP statements (NO ejecutados — copia/pega si decides dropearlas)\n";
    echo str_repeat('=', 70) . "\n";
    foreach (array_keys($broken) as $view) {
        echo "DROP VIEW IF EXISTS `$view`;\n";
    }
}

echo "\nListo.\n";
