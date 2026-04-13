<?php
/**
 * Recrea la vista v_tbl_evaluacion_induccion_respuesta apuntando a las tablas
 * renombradas (tbl_evaluaciones + tbl_evaluacion_respuestas).
 * Uso: DB_PROD_PASS=xxx php app/SQL/fix_view_evaluacion_induccion_respuesta.php
 */

$pass = getenv('DB_PROD_PASS');
if (!$pass) { fwrite(STDERR, "ERROR: define DB_PROD_PASS\n"); exit(1); }

$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal',
    'cycloid_userdb',
    $pass,
    [
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
);
echo "Conectado OK.\n";

$sql = "CREATE OR REPLACE SQL SECURITY DEFINER VIEW `v_tbl_evaluacion_induccion_respuesta` AS "
     . "SELECT `r`.`id` AS `id`, `r`.`id_evaluacion` AS `id_evaluacion`, "
     . "`r`.`nombre` AS `nombre`, `r`.`cedula` AS `cedula`, "
     . "`r`.`whatsapp` AS `whatsapp`, `r`.`empresa_contratante` AS `empresa_contratante`, "
     . "`r`.`cargo` AS `cargo`, `r`.`id_cliente_conjunto` AS `id_cliente_conjunto`, "
     . "`r`.`acepta_tratamiento` AS `acepta_tratamiento`, `r`.`respuestas` AS `respuestas`, "
     . "`r`.`calificacion` AS `calificacion`, `r`.`created_at` AS `created_at`, "
     . "`r`.`updated_at` AS `updated_at`, `ei`.`titulo` AS `titulo`, "
     . "`c`.`nombre_cliente` AS `nombre_cliente` "
     . "FROM `propiedad_horizontal`.`tbl_evaluacion_respuestas` `r` "
     . "JOIN `propiedad_horizontal`.`tbl_evaluaciones` `ei` ON `r`.`id_evaluacion` = `ei`.`id` "
     . "JOIN `propiedad_horizontal`.`tbl_clientes` `c` ON `ei`.`id_cliente` = `c`.`id_cliente`";

echo "Ejecutando CREATE OR REPLACE VIEW...\n";
$pdo->exec($sql);
echo "OK: vista recreada.\n\n";

// Verificación: SHOW FIELDS (lo mismo que hace mysqldump)
echo "Verificando con SHOW FIELDS...\n";
$rows = $pdo->query("SHOW FIELDS FROM `v_tbl_evaluacion_induccion_respuesta`")->fetchAll(PDO::FETCH_ASSOC);
echo "OK: " . count($rows) . " columnas.\n";
foreach ($rows as $r) {
    echo "  - {$r['Field']}  ({$r['Type']})\n";
}

// Verificación 2: SELECT ligero
echo "\nSELECT de prueba (1 fila)...\n";
$row = $pdo->query("SELECT * FROM `v_tbl_evaluacion_induccion_respuesta` LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    echo "  (vista vacía, pero la query corrió sin error)\n";
} else {
    echo "  id={$row['id']} nombre={$row['nombre']} titulo={$row['titulo']} cliente={$row['nombre_cliente']}\n";
}

echo "\nListo.\n";
