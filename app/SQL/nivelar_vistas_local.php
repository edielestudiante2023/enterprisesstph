<?php
/**
 * Nivela vistas locales con producción:
 *  - Recrea v_tbl_evaluacion_induccion_respuesta con los nombres correctos.
 *  - Dropea 5 vistas residuales que no existen en producción.
 * Uso: php app/SQL/nivelar_vistas_local.php
 */

$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3306;dbname=propiedad_horizontal',
    'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
echo "Conectado a LOCAL.\n\n";

// 1. Recrear la vista buena
$sql = "CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_tbl_evaluacion_induccion_respuesta` AS "
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

echo "1) Recreando v_tbl_evaluacion_induccion_respuesta ...\n";
$pdo->exec($sql);
$cnt = count($pdo->query("SHOW FIELDS FROM v_tbl_evaluacion_induccion_respuesta")->fetchAll());
echo "   OK ($cnt columnas)\n\n";

// 2. Dropear vistas residuales (no existen en prod)
$drop = [
    'v_tbl_evaluacion_induccion',
    'v_actividad_reciente',
    'v_permisos_usuario',
    'v_reporte_uso_clientes',
    'v_reporte_uso_usuarios',
];
echo "2) Dropeando vistas residuales...\n";
foreach ($drop as $v) {
    $pdo->exec("DROP VIEW IF EXISTS `$v`");
    echo "   DROP $v\n";
}

// 3. Verificar
echo "\n3) Verificando estado final...\n";
$views = $pdo->query(
    "SELECT TABLE_NAME FROM information_schema.VIEWS
     WHERE TABLE_SCHEMA='propiedad_horizontal' ORDER BY TABLE_NAME"
)->fetchAll(PDO::FETCH_COLUMN);

$broken = 0;
foreach ($views as $v) {
    try { $pdo->query("SHOW FIELDS FROM `$v`"); }
    catch (Exception $e) {
        $broken++;
        echo "   BROKEN: $v\n";
    }
}
echo "   Total vistas: " . count($views) . "  |  Rotas: $broken\n";
echo "\nListo.\n";
