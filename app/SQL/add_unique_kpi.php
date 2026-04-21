<?php
/**
 * Agrega UNIQUE KEY (id_cliente, fecha_inspeccion, indicador) a tbl_kpi_limpieza y tbl_kpi_residuos.
 * Debe correrse DESPUÉS de cleanup_kpi_duplicados.php --execute.
 *
 * Uso: DB_PROD_PASS="xxx" php app/SQL/add_unique_kpi.php
 */

$pass = getenv('DB_PROD_PASS');
if (!$pass) {
    fwrite(STDERR, "ERROR: falta variable DB_PROD_PASS\n");
    exit(1);
}

$dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal;charset=utf8mb4';
$opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

$pdo = new PDO($dsn, 'cycloid_userdb', $pass, $opts);
echo "Conectado a propiedad_horizontal\n";

$changes = [
    'tbl_kpi_limpieza' => 'uq_kpi_limp',
    'tbl_kpi_residuos' => 'uq_kpi_res',
];

foreach ($changes as $tabla => $idxName) {
    // Chequear si ya existe
    $st = $pdo->prepare("SELECT COUNT(*) c FROM information_schema.statistics
                         WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?");
    $st->execute([$tabla, $idxName]);
    $exists = (int)$st->fetch()['c'] > 0;
    if ($exists) {
        echo "  [$tabla] índice $idxName ya existe — skip\n";
        continue;
    }

    $sql = "ALTER TABLE $tabla ADD UNIQUE KEY $idxName (id_cliente, fecha_inspeccion, indicador)";
    echo "  [$tabla] ejecutando: $sql\n";
    $pdo->exec($sql);
    echo "  [$tabla] ✓ UNIQUE creado\n";
}

echo "\n✅ Migración completada\n";
