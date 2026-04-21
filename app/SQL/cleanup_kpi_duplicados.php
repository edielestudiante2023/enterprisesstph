<?php
/**
 * Limpieza de duplicados en tbl_kpi_limpieza y tbl_kpi_residuos.
 *
 * Uso:
 *   DB_PROD_PASS="xxx" php app/SQL/cleanup_kpi_duplicados.php           # preview (default)
 *   DB_PROD_PASS="xxx" php app/SQL/cleanup_kpi_duplicados.php --execute # borra de verdad
 *
 * Estrategia:
 *   Por cada grupo (id_cliente, fecha_inspeccion, indicador), se conserva la fila
 *   con id MÁS ALTO (última escritura del autosave). Antes de borrar, se verifica
 *   que la fila a conservar no pierda fotos: si alguna fila anterior tiene
 *   registro_formato_N no nulo y la conservada lo tiene nulo, se copia la foto
 *   hacia la fila conservada antes de borrar.
 */

$execute = in_array('--execute', $argv, true);

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
echo ($execute ? "🟥 MODO EXECUTE" : "🟦 MODO PREVIEW (usa --execute para borrar)") . "\n";
echo "Conectado a propiedad_horizontal\n\n";

$tablas = ['tbl_kpi_limpieza', 'tbl_kpi_residuos'];
$fotoCols = ['registro_formato_1', 'registro_formato_2', 'registro_formato_3', 'registro_formato_4'];
$totalBorrar = 0;
$totalCopyFotos = 0;

foreach ($tablas as $tabla) {
    echo "================================================================\n";
    echo "  $tabla\n";
    echo "================================================================\n";

    $sql = "SELECT id_cliente, fecha_inspeccion, indicador,
                   GROUP_CONCAT(id ORDER BY id) AS ids
            FROM $tabla
            GROUP BY id_cliente, fecha_inspeccion, indicador
            HAVING COUNT(*) > 1
            ORDER BY id_cliente, fecha_inspeccion";
    $grupos = $pdo->query($sql)->fetchAll();

    if (!$grupos) {
        echo "  (sin duplicados)\n\n";
        continue;
    }

    foreach ($grupos as $g) {
        $ids = array_map('intval', explode(',', $g['ids']));
        $keepId = max($ids);
        $deleteIds = array_values(array_diff($ids, [$keepId]));

        echo sprintf("[cli=%s fecha=%s ind=%s] keep=%d, delete=[%s]\n",
            $g['id_cliente'], $g['fecha_inspeccion'],
            mb_substr($g['indicador'], 0, 40), $keepId, implode(',', $deleteIds));

        // Fetch todas las filas del grupo para comparar fotos
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $st = $pdo->prepare("SELECT id, " . implode(',', $fotoCols) . " FROM $tabla WHERE id IN ($placeholders)");
        $st->execute($ids);
        $filas = [];
        foreach ($st->fetchAll() as $r) $filas[$r['id']] = $r;

        // Detectar fotos que solo existen en filas a borrar
        $updatesKeep = [];
        foreach ($fotoCols as $col) {
            if (!empty($filas[$keepId][$col])) continue; // keep ya tiene
            foreach ($deleteIds as $did) {
                if (!empty($filas[$did][$col])) {
                    $updatesKeep[$col] = $filas[$did][$col];
                    echo "   ↳ copiará $col de id=$did a id=$keepId ('" . $filas[$did][$col] . "')\n";
                    break;
                }
            }
        }

        if ($updatesKeep) {
            $totalCopyFotos++;
            if ($execute) {
                $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($updatesKeep)));
                $st2 = $pdo->prepare("UPDATE $tabla SET $set WHERE id = ?");
                $st2->execute([...array_values($updatesKeep), $keepId]);
                echo "   ✓ fotos copiadas a id=$keepId\n";
            }
        }

        // Borrar las filas duplicadas
        $totalBorrar += count($deleteIds);
        if ($execute) {
            $ph = implode(',', array_fill(0, count($deleteIds), '?'));
            $st3 = $pdo->prepare("DELETE FROM $tabla WHERE id IN ($ph)");
            $st3->execute($deleteIds);
            echo "   ✓ borradas: " . implode(',', $deleteIds) . "\n";
        }
    }
    echo "\n";
}

echo "================================================================\n";
echo "RESUMEN\n";
echo "================================================================\n";
echo ($execute ? "Ejecutado" : "Preview") . ": borraría/borró $totalBorrar filas; $totalCopyFotos copia(s) de foto necesaria(s).\n";
