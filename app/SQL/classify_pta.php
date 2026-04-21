<?php
/**
 * Clasifica actividades del PTA contra el catalogo InspeccionTypes usando Claude Haiku.
 *
 * Uso:
 *   DB_PROD_PASS=xxx ANTHROPIC_API_KEY=xxx php classify_pta.php [local|production] [id_cliente|all] [--reclassify]
 *
 * Ejemplos:
 *   php classify_pta.php local 63
 *   php classify_pta.php local all
 *   DB_PROD_PASS=xxx ANTHROPIC_API_KEY=xxx php classify_pta.php production 63
 *
 * Por defecto NO reprocesa PTAs que ya tienen matches (salta). Con --reclassify, reemplaza.
 */

if (php_sapi_name() !== 'cli') {
    die("Solo CLI.\n");
}

require_once __DIR__ . '/../../vendor/autoload.php';

$env = $argv[1] ?? null;
$target = $argv[2] ?? null;
$reclassify = in_array('--reclassify', $argv, true);

if (!in_array($env, ['local', 'production'], true) || $target === null) {
    die("Uso: php classify_pta.php [local|production] [id_cliente|all] [--reclassify]\n");
}

// --- Cargar .env manualmente para CLI (CI4 CLIRequest no siempre lo carga) ---
$envFile = __DIR__ . '/../../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if ($k !== '' && getenv($k) === false) {
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
        }
    }
}

if (!getenv('ANTHROPIC_API_KEY')) {
    die("ERROR: ANTHROPIC_API_KEY no definida.\n");
}

// --- Conexion BD ---
if ($env === 'local') {
    $cfg = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} else {
    $cfg = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060, 'user' => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
}

echo "=== Clasificacion PTA con Claude Haiku 4.5 ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Target: " . ($target === 'all' ? 'TODOS los clientes' : "cliente {$target}") . "\n";
echo "Reclassify: " . ($reclassify ? 'SI (reemplaza matches existentes)' : 'NO (salta PTAs ya clasificados)') . "\n---\n";

$mysqli = mysqli_init();
if ($cfg['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

if (!@$mysqli->real_connect(
    $cfg['host'], $cfg['user'], $cfg['password'], $cfg['database'], $cfg['port'], null,
    $cfg['ssl'] ? MYSQLI_CLIENT_SSL : 0
)) {
    die("ERROR conexion: " . $mysqli->connect_error . "\n");
}

echo "Conexion OK.\n";

// --- Obtener actividades PTA ---
$sqlActs = "SELECT tbl_pta_cliente.id_cliente, tbl_pta_cliente.id_ptacliente,
                   tbl_pta_cliente.phva_plandetrabajo, tbl_pta_cliente.numeral_plandetrabajo,
                   tbl_pta_cliente.actividad_plandetrabajo
            FROM tbl_pta_cliente
            WHERE TRIM(tbl_pta_cliente.actividad_plandetrabajo) <> ''";
if ($target !== 'all') {
    $sqlActs .= " AND tbl_pta_cliente.id_cliente = " . (int) $target;
}
$sqlActs .= " ORDER BY tbl_pta_cliente.id_cliente, tbl_pta_cliente.id_ptacliente";

$rs = $mysqli->query($sqlActs);
$actividades = [];
while ($row = $rs->fetch_assoc()) {
    $actividades[] = $row;
}
$totalActs = count($actividades);
echo "Actividades PTA a procesar: {$totalActs}\n";

if ($totalActs === 0) {
    echo "Nada que clasificar.\n";
    exit(0);
}

// --- Si no --reclassify, filtrar los ya clasificados ---
if (!$reclassify) {
    $ya = [];
    $rs2 = $mysqli->query("SELECT DISTINCT id_cliente, id_ptacliente FROM tbl_pta_inspeccion_match");
    while ($r2 = $rs2->fetch_assoc()) {
        $ya[(int) $r2['id_cliente'] . ':' . (int) $r2['id_ptacliente']] = true;
    }
    $before = count($actividades);
    $actividades = array_values(array_filter($actividades, fn($a) =>
        !isset($ya[(int) $a['id_cliente'] . ':' . (int) $a['id_ptacliente']])
    ));
    $skipped = $before - count($actividades);
    if ($skipped > 0) echo "Saltados (ya clasificados): {$skipped}\n";
    if (count($actividades) === 0) {
        echo "No hay actividades nuevas. Usa --reclassify para reprocesar.\n";
        exit(0);
    }
    echo "Pendientes por clasificar: " . count($actividades) . "\n";
}

// --- Clasificar ---
$classifier = new \App\Libraries\PtaClassifier();
$model = $classifier->getModel();

$BATCH = 20;
$chunks = array_chunk($actividades, $BATCH);
$totalMatches = 0;
$processed = 0;
$ptasSinMatch = 0;

foreach ($chunks as $i => $chunk) {
    $n = count($chunk);
    echo "\n[" . ($i + 1) . "/" . count($chunks) . "] Lote de {$n} actividades... ";

    try {
        $result = $classifier->classifyBatch($chunk);
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        continue;
    }

    $matchesEnLote = 0;
    foreach ($chunk as $a) {
        $idPta = (int) $a['id_ptacliente'];
        $idCli = (int) $a['id_cliente'];
        $matches = $result[$idPta] ?? [];

        if ($reclassify) {
            $stmtDel = $mysqli->prepare("DELETE FROM tbl_pta_inspeccion_match WHERE id_cliente=? AND id_ptacliente=? AND method='ai'");
            $stmtDel->bind_param('ii', $idCli, $idPta);
            $stmtDel->execute();
            $stmtDel->close();
        }

        if (empty($matches)) {
            $ptasSinMatch++;
            continue;
        }

        foreach ($matches as $m) {
            $slug = $m['slug'];
            $score = $m['score'];
            $reason = $m['reasoning'];
            $stmt = $mysqli->prepare(
                "INSERT INTO tbl_pta_inspeccion_match
                 (id_cliente, id_ptacliente, slug_inspeccion, score, method, reasoning, ai_model, created_at)
                 VALUES (?, ?, ?, ?, 'ai', ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE score=VALUES(score), reasoning=VALUES(reasoning), ai_model=VALUES(ai_model), method='ai', updated_at=NOW()"
            );
            $stmt->bind_param('iisdss', $idCli, $idPta, $slug, $score, $reason, $model);
            if ($stmt->execute()) {
                $matchesEnLote++;
                $totalMatches++;
            }
            $stmt->close();
        }
    }
    $processed += $n;
    echo "OK ({$matchesEnLote} matches guardados)";
}

echo "\n\n=== RESUMEN ===\n";
echo "Actividades procesadas: {$processed}\n";
echo "Matches guardados: {$totalMatches}\n";
echo "Actividades sin match: {$ptasSinMatch}\n";
echo "Modelo: {$model}\n";

$mysqli->close();
