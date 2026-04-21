<?php
/**
 * Diagnóstico de duplicados en tbl_kpi_limpieza y tbl_kpi_residuos.
 * Uso: DB_PROD_PASS="xxx" php app/SQL/diag_kpi_duplicados.php
 * Solo lectura: SELECT + SHOW INDEX / SHOW CREATE TABLE.
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
    PDO::MYSQL_ATTR_SSL_CA       => '',
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    $pdo = new PDO($dsn, 'cycloid_userdb', $pass, $opts);
} catch (PDOException $e) {
    fwrite(STDERR, "Conexión fallida: " . $e->getMessage() . "\n");
    exit(1);
}

echo "✅ Conectado a propiedad_horizontal\n";

$tablas = ['tbl_kpi_limpieza', 'tbl_kpi_residuos'];

foreach ($tablas as $tabla) {
    echo "\n================================================================\n";
    echo "  $tabla\n";
    echo "================================================================\n";

    // 1. Total filas
    $total = $pdo->query("SELECT COUNT(*) AS c FROM $tabla")->fetch();
    echo "Total filas: " . $total['c'] . "\n";

    // 2. Índices (para ver si existe UNIQUE)
    echo "\n-- Índices --\n";
    $idx = $pdo->query("SHOW INDEX FROM $tabla")->fetchAll();
    foreach ($idx as $i) {
        echo sprintf("  %s  unique=%s  col=%s\n", $i['Key_name'], $i['Non_unique'] == 0 ? 'YES' : 'no', $i['Column_name']);
    }

    // 3. Duplicados por (id_cliente, fecha_inspeccion, indicador)
    echo "\n-- Duplicados por (id_cliente, fecha_inspeccion, indicador) --\n";
    $sql = "SELECT id_cliente, fecha_inspeccion, indicador, COUNT(*) AS veces,
                   GROUP_CONCAT(id ORDER BY id) AS ids,
                   GROUP_CONCAT(estado ORDER BY id) AS estados
            FROM $tabla
            GROUP BY id_cliente, fecha_inspeccion, indicador
            HAVING veces > 1
            ORDER BY veces DESC, fecha_inspeccion DESC
            LIMIT 30";
    $dups = $pdo->query($sql)->fetchAll();

    if (!$dups) {
        echo "  (sin duplicados)\n";
    } else {
        echo sprintf("  %-4s %-12s %-60s %-4s %-30s %s\n", 'CLI', 'FECHA', 'INDICADOR', 'N', 'IDS', 'ESTADOS');
        foreach ($dups as $d) {
            echo sprintf("  %-4s %-12s %-60s %-4s %-30s %s\n",
                $d['id_cliente'],
                $d['fecha_inspeccion'],
                mb_substr($d['indicador'], 0, 58),
                $d['veces'],
                $d['ids'],
                $d['estados']
            );
        }
    }

    // 4. Zoom: detalle de los 3 peores casos con created_at/updated_at
    echo "\n-- Detalle temporal de duplicados (top 3 casos) --\n";
    $top = array_slice($dups, 0, 3);
    foreach ($top as $d) {
        echo "  Cliente=" . $d['id_cliente'] . "  Fecha=" . $d['fecha_inspeccion'] . "  Indicador=" . mb_substr($d['indicador'], 0, 50) . "\n";
        $sqlDet = "SELECT id, id_consultor, estado, valor_numerador, valor_denominador, cumplimiento, created_at, updated_at
                   FROM $tabla
                   WHERE id_cliente = :c AND fecha_inspeccion = :f AND indicador = :i
                   ORDER BY id";
        $st = $pdo->prepare($sqlDet);
        $st->execute([':c' => $d['id_cliente'], ':f' => $d['fecha_inspeccion'], ':i' => $d['indicador']]);
        foreach ($st->fetchAll() as $r) {
            echo sprintf("    id=%-5s cons=%-4s estado=%-10s num=%-5s den=%-5s cump=%-6s created=%s updated=%s\n",
                $r['id'], $r['id_consultor'], $r['estado'],
                $r['valor_numerador'] ?? 'NULL',
                $r['valor_denominador'] ?? 'NULL',
                $r['cumplimiento'] ?? 'NULL',
                $r['created_at'], $r['updated_at']);
        }
        echo "\n";
    }

    // 5. Distribución de deltas de tiempo entre duplicados (pista de autosave)
    echo "-- Deltas (segundos) entre primer y último created_at por grupo duplicado --\n";
    $sqlDelta = "SELECT id_cliente, fecha_inspeccion, indicador,
                        COUNT(*) veces,
                        TIMESTAMPDIFF(SECOND, MIN(created_at), MAX(created_at)) delta_seg
                 FROM $tabla
                 GROUP BY id_cliente, fecha_inspeccion, indicador
                 HAVING veces > 1
                 ORDER BY veces DESC
                 LIMIT 10";
    $deltas = $pdo->query($sqlDelta)->fetchAll();
    foreach ($deltas as $dl) {
        echo sprintf("  cli=%-4s fecha=%-12s veces=%-3s delta=%ss  ind=%s\n",
            $dl['id_cliente'], $dl['fecha_inspeccion'], $dl['veces'],
            $dl['delta_seg'], mb_substr($dl['indicador'], 0, 50));
    }
}

echo "\n✅ Diagnóstico terminado\n";
