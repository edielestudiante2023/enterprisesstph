<?php
/**
 * Diagnóstico: verificar foto de extintores en DB y disco
 * Uso: php diag_ext_fotos.php [local|production] [id_inspeccion]
 */

$env = $argv[1] ?? 'local';
$idInspeccion = (int)($argv[2] ?? 1);

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $dbname = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $dbname = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $opts);
} catch (Exception $e) {
    echo "ERROR conectando: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Cabecera de la inspección
$stmt = $pdo->prepare("SELECT id, estado, ruta_pdf FROM tbl_inspeccion_extintores WHERE id = ?");
$stmt->execute([$idInspeccion]);
$insp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$insp) {
    echo "No existe inspección con id=$idInspeccion\n";
    exit(1);
}

echo "=== Inspección extintores ID={$idInspeccion} ===\n";
echo "estado   : {$insp['estado']}\n";
echo "ruta_pdf : " . ($insp['ruta_pdf'] ?? '(null)') . "\n\n";

// 2. Filas detalle
$stmt = $pdo->prepare(
    "SELECT id, orden, foto FROM tbl_extintor_detalle WHERE id_inspeccion = ? ORDER BY orden"
);
$stmt->execute([$idInspeccion]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Detalle extintores (" . count($rows) . " filas) ===\n";

// FCPATH en producción
$fcpath = '/www/wwwroot/phorizontal/enterprisesstph/public/';
if ($env === 'local') {
    $fcpath = 'c:/xampp/htdocs/enterprisesstph/public/';
}

foreach ($rows as $row) {
    $fotoDb = $row['foto'] ?? null;
    $exists = false;
    $fullPath = '';
    if ($fotoDb) {
        $fullPath = $fcpath . $fotoDb;
        $exists = file_exists($fullPath);
    }
    echo sprintf(
        "ID=%-6d orden=%-3d foto=%-60s archivo=%s\n",
        $row['id'],
        $row['orden'],
        $fotoDb ?? '(null)',
        $fotoDb ? ($exists ? 'EXISTE' : 'NO EXISTE en ' . $fullPath) : 'N/A'
    );
}

// 3. Listar archivos en el directorio de fotos
echo "\n=== Archivos en uploads/inspecciones/extintores/fotos/ ===\n";
$dir = $fcpath . 'uploads/inspecciones/extintores/fotos/';
if (is_dir($dir)) {
    $files = scandir($dir);
    $files = array_filter($files, fn($f) => !in_array($f, ['.', '..']));
    foreach ($files as $f) {
        $size = filesize($dir . $f);
        $mtime = date('Y-m-d H:i:s', filemtime($dir . $f));
        echo sprintf("  %-50s %8d bytes  %s\n", $f, $size, $mtime);
    }
} else {
    echo "  Directorio NO existe: $dir\n";
}
