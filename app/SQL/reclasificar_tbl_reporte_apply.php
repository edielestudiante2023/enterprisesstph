<?php
/**
 * APPLY — Reclasificación de documentos históricos en tbl_reporte.
 *
 * Mueve los 313 docs identificados por el dry-run al id_detailreport correcto.
 * Crea backup completo antes de cualquier UPDATE. Idempotente.
 *
 * Uso:
 *   DB_PROD_PASS=xxx php app/SQL/reclasificar_tbl_reporte_apply.php [production|local]
 */

$entorno = $argv[1] ?? 'local';
if ($entorno === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'propiedad_horizontal';
    $port = 25060;
    $ssl  = true;
} else {
    $host = 'localhost'; $user = 'root'; $pass = ''; $db = 'propiedad_horizontal'; $port = 3306; $ssl = false;
}
if (!$pass && $entorno === 'production') die("FALTA DB_PROD_PASS\n");

$mysqli = new mysqli();
if ($ssl) {
    $mysqli->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
    $mysqli->real_connect($host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
} else {
    $mysqli->real_connect($host, $user, $pass, $db, $port);
}
if ($mysqli->connect_error) die("Error: " . $mysqli->connect_error . "\n");
$mysqli->set_charset('utf8mb4');

/**
 * Mismo mapeo que el dry-run: tag_prefix => id_detailreport correcto.
 */
$mapeo = [
    'acta_id:'                          => 52,
    'acta_cap_vinculo_id:'              => 18,
    'acta_capacitacion_id:'             => 18,
    'acta_cap_resp_vinculo_id:'         => 35,
    'acta_cap_resp_id:'                 => 35,
    'aud_res_id:'                       => 62,
    'carta_vigia_id:'                   => 30,
    'lavado-tanques_id:'                => 40,
    'fumigacion_id:'                    => 41,
    'desratizacion_id:'                 => 42,
    'dot_ase_id:'                       => 7,
    'dot_tod_id:'                       => 6,
    'dot_vig_id:'                       => 8,
    'eval_sim_id:'                      => 13,
    'hv_brig_id:'                       => 14,
    'insp_asc_id:'                      => 45,
    'insp_bot_id:'                      => 3,
    'insp_brig_id:'                     => 48,
    'insp_com_id:'                      => 54,
    'insp_ext_id:'                      => 2,
    'insp_gab_id:'                      => 4,
    'insp_gym_id:'                      => 49,
    'insp_locativa_id:'                 => 16,
    'insp_piscinero_id:'                => 47,
    'insp_pis_id:'                      => 46,
    'insp_pq_id:'                       => 55,
    'insp_rec_id:'                      => 5,
    'insp_senal_id:'                    => 53,
    'insp_tsj_id:'                      => 50,
    'insp_bbq_id:'                      => 51,
    'mat_vul_id:'                       => 11,
    'cont_agua_id:'                     => 38,
    'cont_basura_id:'                   => 39,
    'cont_limpieza_desinfeccion_id:'    => 59,
    'cont_plagas_id:'                   => 36,
    'plan_emg_id:'                      => 10,
    'plan_san_id:'                      => 25,
    'prep_sim_id:'                      => 61,
    'prob_pel_id:'                      => 12,
    'proc_em_area_id:'                  => 60,
    'prog_agua_id:'                     => 24,
    'prog_limp_id:'                     => 56,
    'prog_plag_id:'                     => 57,
    'prog_res_id:'                      => 58,
    'rep_cap_id:'                       => 18,
    'protocolo_alturas_cliente:'        => 44,
    'inf_avance_id:'                    => 37,
    'planilla_ss_id:'                   => 43,
    'asist_ind_id:'                     => 34,
    'asist_ind_resp_id:'                => 35,
    'kpi_agua_id:'                      => 29,
    'kpi_limp_id:'                      => 26,
    'kpi_plag_id:'                      => 28,
    'kpi_res_id:'                       => 27,
];

echo "==========================================================\n";
echo "APPLY — Reclasificación tbl_reporte ($entorno)\n";
echo "==========================================================\n";
echo "Inicio: " . date('Y-m-d H:i:s') . "\n\n";

// --- 1. Crear tabla de backup si no existe ---
$backupName = 'tbl_reporte_backup_pre_reclas_' . date('Ymd');
echo "--- 1. Backup snapshot en $backupName ---\n";

$r = $mysqli->query("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='$backupName'");
$existe = $r && $r->num_rows > 0;

if ($existe) {
    echo "  Tabla $backupName ya existe — verificando contenido...\n";
    $c = (int)$mysqli->query("SELECT COUNT(*) c FROM $backupName")->fetch_assoc()['c'];
    echo "  Filas en backup existente: $c\n";
    if ($c === 0) {
        // tabla vacía, sigue siendo idempotente — repoblará abajo
        $mysqli->query("DROP TABLE $backupName");
        $existe = false;
    }
}

if (!$existe) {
    if (!$mysqli->query("CREATE TABLE $backupName LIKE tbl_reporte")) {
        die("ERROR creando backup: " . $mysqli->error . "\n");
    }
    echo "  Tabla $backupName creada.\n";

    // Insertar solo los registros que VAN A CAMBIAR (id_detailreport diferente al destino)
    $insertedTotal = 0;
    foreach ($mapeo as $tag => $idDestino) {
        $like = '%' . $mysqli->real_escape_string($tag) . '%';
        $stmt = $mysqli->prepare("INSERT INTO $backupName SELECT * FROM tbl_reporte WHERE observaciones LIKE ? AND id_detailreport != ?");
        $stmt->bind_param('si', $like, $idDestino);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $insertedTotal += $affected;
        if ($affected > 0) {
            printf("  · backup %-38s : %d filas\n", $tag, $affected);
        }
    }
    echo "  TOTAL en backup: $insertedTotal filas\n\n";
}

// --- 2. Aplicar UPDATEs por tag ---
echo "--- 2. Aplicando UPDATEs ---\n";
$totalMovidos = 0;
foreach ($mapeo as $tag => $idDestino) {
    $like = '%' . $mysqli->real_escape_string($tag) . '%';
    $stmt = $mysqli->prepare("UPDATE tbl_reporte SET id_detailreport = ?, updated_at = NOW() WHERE observaciones LIKE ? AND id_detailreport != ?");
    $stmt->bind_param('isi', $idDestino, $like, $idDestino);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $totalMovidos += $affected;
    if ($affected > 0) {
        printf("  · %-38s → id=%-3d : %d movidos\n", $tag, $idDestino, $affected);
    }
}
echo "\n  TOTAL MOVIDOS: $totalMovidos\n\n";

// --- 3. Verificación post ---
echo "--- 3. Verificación post (debería quedar 0 en 'a mover') ---\n";
$pendientes = 0;
foreach ($mapeo as $tag => $idDestino) {
    $like = '%' . $mysqli->real_escape_string($tag) . '%';
    $stmt = $mysqli->prepare("SELECT COUNT(*) c FROM tbl_reporte WHERE observaciones LIKE ? AND id_detailreport != ?");
    $stmt->bind_param('si', $like, $idDestino);
    $stmt->execute();
    $res = $stmt->get_result();
    $pendientes += (int)$res->fetch_assoc()['c'];
}
echo "  Docs pendientes de mover (debería ser 0): $pendientes\n\n";

// --- 4. Validación específica mirto2 ---
echo "--- 4. Validación mirto2 (id_cliente=57) — últimos docs con tag ---\n";
$r = $mysqli->query("
    SELECT r.id_reporte, r.created_at, r.titulo_reporte, r.id_detailreport, d.detail_report
    FROM tbl_reporte r
    LEFT JOIN detail_report d ON d.id_detailreport = r.id_detailreport
    WHERE r.id_cliente = 57
      AND (r.observaciones LIKE '%acta_id:%' OR r.observaciones LIKE '%plan_san_id:%'
           OR r.observaciones LIKE '%lavado-tanques_id:%' OR r.observaciones LIKE '%fumigacion_id:%'
           OR r.observaciones LIKE '%desratizacion_id:%' OR r.observaciones LIKE '%acta_cap_vinculo_id:%'
           OR r.observaciones LIKE '%aud_res_id:%')
    ORDER BY r.created_at DESC
    LIMIT 15
");
while ($row = $r->fetch_assoc()) {
    printf("  ID %5d | %s | [%2d] %-30s | %s\n",
        $row['id_reporte'], $row['created_at'], $row['id_detailreport'],
        substr($row['detail_report'] ?? '???', 0, 30), substr($row['titulo_reporte'], 0, 70));
}

echo "\nFin: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================================\n";
echo "Migración completada. Backup: $backupName (rollback disponible)\n";
echo "==========================================================\n";

$mysqli->close();
