<?php
/**
 * Limpiar tbl_reporte — conservar solo módulos nativos, borrar recarga corrupta
 *
 * Uso: DB_PROD_PASS=xxx php tools/limpiar_reporte_takeout.php
 */

$pass = getenv('DB_PROD_PASS');
if (!$pass) {
    die("ERROR: Variable DB_PROD_PASS no definida.\n");
}

$db = new mysqli();
$db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    $pass,
    'propiedad_horizontal',
    25060,
    null,
    MYSQLI_CLIENT_SSL
);

if ($db->connect_error) {
    die("ERROR conexión: {$db->connect_error}\n");
}

echo "=== Conectado a producción ===\n\n";

// Paso 1: Conteo actual por observaciones
echo "--- ANTES: Conteo por observaciones ---\n";
$result = $db->query("SELECT observaciones, COUNT(*) as cnt FROM tbl_reporte GROUP BY observaciones ORDER BY cnt DESC");
$total_antes = 0;
while ($row = $result->fetch_assoc()) {
    $obs = $row['observaciones'] ?? '(NULL)';
    if ($obs === '') $obs = '(vacío)';
    printf("  [%4d] %s\n", $row['cnt'], $obs);
    $total_antes += $row['cnt'];
}
echo "  TOTAL: {$total_antes}\n\n";

// Paso 2: Contar lo que se va a borrar
$count_takeout = $db->query("SELECT COUNT(*) as c FROM tbl_reporte WHERE observaciones LIKE 'Recargado desde Takeout%'")->fetch_assoc()['c'];
$count_huerfanos = $db->query("SELECT COUNT(*) as c FROM tbl_reporte WHERE observaciones LIKE 'Registrado v2 huerfano+email%'")->fetch_assoc()['c'];
$total_borrar = $count_takeout + $count_huerfanos;

echo "--- A BORRAR ---\n";
echo "  Recargado desde Takeout%:        {$count_takeout}\n";
echo "  Registrado v2 huerfano+email%:   {$count_huerfanos}\n";
echo "  TOTAL A BORRAR:                  {$total_borrar}\n\n";

if ($total_borrar === 0) {
    echo "Nada que borrar. Saliendo.\n";
    $db->close();
    exit(0);
}

// Paso 3: Borrar
echo "Borrando...\n";

$db->query("DELETE FROM tbl_reporte WHERE observaciones LIKE 'Recargado desde Takeout%'");
$deleted_takeout = $db->affected_rows;
echo "  Eliminados Takeout: {$deleted_takeout}\n";

$db->query("DELETE FROM tbl_reporte WHERE observaciones LIKE 'Registrado v2 huerfano+email%'");
$deleted_huerfanos = $db->affected_rows;
echo "  Eliminados huérfanos: {$deleted_huerfanos}\n";

$total_eliminados = $deleted_takeout + $deleted_huerfanos;
echo "  TOTAL ELIMINADOS: {$total_eliminados}\n\n";

// Paso 4: Verificación final
echo "--- DESPUÉS: Conteo por observaciones ---\n";
$result = $db->query("SELECT observaciones, COUNT(*) as cnt FROM tbl_reporte GROUP BY observaciones ORDER BY cnt DESC");
$total_despues = 0;
while ($row = $result->fetch_assoc()) {
    $obs = $row['observaciones'] ?? '(NULL)';
    if ($obs === '') $obs = '(vacío)';
    printf("  [%4d] %s\n", $row['cnt'], $obs);
    $total_despues += $row['cnt'];
}
echo "  TOTAL RESTANTE: {$total_despues}\n\n";

echo "=== Limpieza completada ===\n";
echo "  Antes: {$total_antes} | Borrados: {$total_eliminados} | Después: {$total_despues}\n";

$db->close();
