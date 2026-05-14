<?php
/**
 * Sincroniza los inventarios de choque EXISTENTES con el catálogo actualizado:
 *   - Elimina la categoría "BRIGADA" (ya no es un ítem estructural fotografiable).
 *   - Agrega a "RECURSOS PARA LA SEGURIDAD" los ítems "Detectores de humo" y
 *     "Aspersores de control de extincion de fuego".
 *
 * Idempotente: puede correrse N veces sin duplicar ni borrar de más.
 * Solo toca tbl_inventario_choque_items; no altera el catálogo de código.
 *
 * Uso:
 *   php app/SQL/sync_inventario_choque_items.php local
 *   DB_PROD_PASS=xxx php app/SQL/sync_inventario_choque_items.php production
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} elseif ($env === 'production') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060, 'user' => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
    if ($config['password'] === '') {
        die("ERROR: DB_PROD_PASS no definido en el entorno.\n");
    }
} else {
    die("Uso: php sync_inventario_choque_items.php [local|production]\n");
}

$NEW_CAT   = 'RECURSOS PARA LA SEGURIDAD';
$NEW_ITEMS = ['Detectores de humo', 'Aspersores de control de extincion de fuego'];

echo "=== Sync Inventario de Choque - catálogo actualizado ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
if (!@$mysqli->real_connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port'], null, $config['ssl'] ? MYSQLI_CLIENT_SSL : 0)) {
    die("ERROR de conexion: " . $mysqli->connect_error . "\n");
}
echo "Conexion OK.\n\n";

// ── 1. Eliminar categoría BRIGADA de los inventarios existentes ─────────────
$res = $mysqli->query("SELECT COUNT(*) AS c FROM tbl_inventario_choque_items WHERE categoria = 'BRIGADA'");
$brigadaCount = (int) $res->fetch_assoc()['c'];

if ($brigadaCount > 0) {
    $mysqli->query("DELETE FROM tbl_inventario_choque_items WHERE categoria = 'BRIGADA'");
    echo "BRIGADA: {$brigadaCount} filas eliminadas.\n";
} else {
    echo "BRIGADA: 0 filas (ya estaba limpio).\n";
}

// ── 2. Agregar los 2 ítems nuevos a cada inventario existente ───────────────
$inventarios = [];
$res = $mysqli->query("SELECT id FROM tbl_inventario_choque ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
    $inventarios[] = (int) $row['id'];
}
echo "Inventarios existentes: " . count($inventarios) . "\n";

$stmtExiste = $mysqli->prepare(
    "SELECT COUNT(*) AS c FROM tbl_inventario_choque_items
     WHERE id_inventario = ? AND categoria = ? AND item = ?"
);
$stmtMaxOrden = $mysqli->prepare(
    "SELECT COALESCE(MAX(orden), 0) AS m FROM tbl_inventario_choque_items WHERE id_inventario = ?"
);
$stmtInsert = $mysqli->prepare(
    "INSERT INTO tbl_inventario_choque_items (id_inventario, categoria, item, orden, marcado)
     VALUES (?, ?, ?, ?, 0)"
);

$insertados = 0;
$yaExistian = 0;

foreach ($inventarios as $idInv) {
    foreach ($NEW_ITEMS as $item) {
        $stmtExiste->bind_param('iss', $idInv, $NEW_CAT, $item);
        $stmtExiste->execute();
        $existe = (int) $stmtExiste->get_result()->fetch_assoc()['c'];

        if ($existe > 0) {
            $yaExistian++;
            continue;
        }

        $stmtMaxOrden->bind_param('i', $idInv);
        $stmtMaxOrden->execute();
        $orden = (int) $stmtMaxOrden->get_result()->fetch_assoc()['m'] + 1;

        $stmtInsert->bind_param('issi', $idInv, $NEW_CAT, $item, $orden);
        $stmtInsert->execute();
        $insertados++;
    }
}

echo "Ítems nuevos insertados: {$insertados}\n";
echo "Ítems que ya existían (sin cambio): {$yaExistian}\n";

// ── 3. Verificación ─────────────────────────────────────────────────────────
$res = $mysqli->query("SELECT COUNT(*) AS c FROM tbl_inventario_choque_items WHERE categoria = 'BRIGADA'");
$brigadaRestante = (int) $res->fetch_assoc()['c'];

$res = $mysqli->query(
    "SELECT COUNT(DISTINCT id_inventario) AS c FROM tbl_inventario_choque_items
     WHERE categoria = '{$NEW_CAT}' AND item IN ('Detectores de humo', 'Aspersores de control de extincion de fuego')"
);
$invConItems = (int) $res->fetch_assoc()['c'];

echo "\nVerificación:\n";
echo ($brigadaRestante === 0 ? "  ✓" : "  ⚠") . " Filas BRIGADA restantes: {$brigadaRestante}\n";
echo ($invConItems === count($inventarios) ? "  ✓" : "  ⚠")
    . " Inventarios con los 2 ítems nuevos: {$invConItems} / " . count($inventarios) . "\n";

echo "\nFin.\n";

$mysqli->close();
