<?php
/**
 * DRY-RUN — Reclasificación de documentos históricos en tbl_reporte.
 *
 * SOLO LECTURA. No modifica nada.
 *
 * Mira los tags únicos en observaciones (acta_id:, dot_vig_id:, etc.) y reporta
 * cuántos docs quedarían movidos a qué id_detailreport — usando la tabla de
 * mapeo ya alineada con los controladores post-fix.
 *
 * Uso:
 *   DB_PROD_PASS=xxx php tools/reclasificar_tbl_reporte_dryrun.php [production|local]
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
 * Mapeo tag_prefix => id_detailreport correcto (post-fix).
 * Cada tag identifica de forma única qué controlador generó el PDF.
 */
$mapeo = [
    'acta_id:'                          => ['id' => 52, 'desc' => 'Acta de Visita'],
    'acta_cap_vinculo_id:'              => ['id' => 18, 'desc' => 'Capacitaciones (acta vínculo)'],
    'acta_capacitacion_id:'             => ['id' => 18, 'desc' => 'Capacitaciones (acta legacy)'],
    'acta_cap_resp_vinculo_id:'         => ['id' => 35, 'desc' => 'Responsabilidades SST (vínculo)'],
    'acta_cap_resp_id:'                 => ['id' => 35, 'desc' => 'Responsabilidades SST (legacy)'],
    'aud_res_id:'                       => ['id' => 62, 'desc' => 'Auditoría Zona Residuos'],
    'carta_vigia_id:'                   => ['id' => 30, 'desc' => 'Carta Vigía'],
    'lavado-tanques_id:'                => ['id' => 40, 'desc' => 'Lavado de Tanques'],
    'fumigacion_id:'                    => ['id' => 41, 'desc' => 'Fumigación'],
    'desratizacion_id:'                 => ['id' => 42, 'desc' => 'Desratización'],
    'dot_ase_id:'                       => ['id' => 7,  'desc' => 'Inspección Dotación Aseadora'],
    'dot_tod_id:'                       => ['id' => 6,  'desc' => 'Inspección Dotación Todero'],
    'dot_vig_id:'                       => ['id' => 8,  'desc' => 'Inspección Dotación Vigilante'],
    'eval_sim_id:'                      => ['id' => 13, 'desc' => 'Evidencias de Simulacro'],
    'hv_brig_id:'                       => ['id' => 14, 'desc' => 'Brigadistas de la Copropiedad'],
    'insp_asc_id:'                      => ['id' => 45, 'desc' => 'Inspección Ascensores'],
    'insp_bot_id:'                      => ['id' => 3,  'desc' => 'Inspección Botiquines'],
    'insp_brig_id:'                     => ['id' => 48, 'desc' => 'Brigada y Simulacros'],
    'insp_com_id:'                      => ['id' => 54, 'desc' => 'Inspección Comunicaciones'],
    'insp_ext_id:'                      => ['id' => 2,  'desc' => 'Inspección Extintores'],
    'insp_gab_id:'                      => ['id' => 4,  'desc' => 'Inspección Gabinetes'],
    'insp_gym_id:'                      => ['id' => 49, 'desc' => 'Inspección Gimnasio'],
    'insp_locativa_id:'                 => ['id' => 16, 'desc' => 'Inspección Locativa'],
    'insp_piscinero_id:'                => ['id' => 47, 'desc' => 'Inspección Piscinero'], // más largo, evaluar antes que insp_pis_id
    'insp_pis_id:'                      => ['id' => 46, 'desc' => 'Inspección Piscinas'],
    'insp_pq_id:'                       => ['id' => 55, 'desc' => 'Inspección Productos Químicos'],
    'insp_rec_id:'                      => ['id' => 5,  'desc' => 'Inspección Recursos Seguridad'],
    'insp_senal_id:'                    => ['id' => 53, 'desc' => 'Inspección Señalización'],
    'insp_tsj_id:'                      => ['id' => 50, 'desc' => 'Inspección Turco/Sauna/Jacuzzi'],
    'insp_bbq_id:'                      => ['id' => 51, 'desc' => 'Inspección Zona BBQ'],
    'mat_vul_id:'                       => ['id' => 11, 'desc' => 'Matriz de Vulnerabilidad'],
    'cont_agua_id:'                     => ['id' => 38, 'desc' => 'Plan Contingencia Sin Agua'],
    'cont_basura_id:'                   => ['id' => 39, 'desc' => 'Plan Contingencia Basura'],
    'cont_limpieza_desinfeccion_id:'    => ['id' => 59, 'desc' => 'Plan Contingencia Limpieza y Desinfección'],
    'cont_plagas_id:'                   => ['id' => 36, 'desc' => 'Plan Contingencia Plagas'],
    'plan_emg_id:'                      => ['id' => 10, 'desc' => 'Plan de Emergencia'],
    'plan_san_id:'                      => ['id' => 25, 'desc' => 'Plan Saneamiento'],
    'prep_sim_id:'                      => ['id' => 61, 'desc' => 'Preparación Simulacro'],
    'prob_pel_id:'                      => ['id' => 12, 'desc' => 'Ocurrencia de Peligros'],
    'proc_em_area_id:'                  => ['id' => 60, 'desc' => 'Procedimiento Emergencia por Área'],
    'prog_agua_id:'                     => ['id' => 24, 'desc' => 'Agua Potable'],
    'prog_limp_id:'                     => ['id' => 56, 'desc' => 'Programa Limpieza y Desinfección'],
    'prog_plag_id:'                     => ['id' => 57, 'desc' => 'Programa Control Integrado de Plagas'],
    'prog_res_id:'                      => ['id' => 58, 'desc' => 'Programa Manejo Integral de Residuos'],
    'rep_cap_id:'                       => ['id' => 18, 'desc' => 'Capacitaciones (reporte)'],
    'protocolo_alturas_cliente:'        => ['id' => 44, 'desc' => 'Protocolo Alturas'],
    'inf_avance_id:'                    => ['id' => 37, 'desc' => 'INFORME DE AVANCES'],
    'planilla_ss_id:'                   => ['id' => 43, 'desc' => 'Planilla Seg. Social'],
    'asist_ind_id:'                     => ['id' => 34, 'desc' => 'Asistencia Inducción'],
    'asist_ind_resp_id:'                => ['id' => 35, 'desc' => 'Responsabilidades SST (asistencia)'],
    'kpi_agua_id:'                      => ['id' => 29, 'desc' => 'KPI Agua Potable'],
    'kpi_limp_id:'                      => ['id' => 26, 'desc' => 'KPI Limpieza'],
    'kpi_plag_id:'                      => ['id' => 28, 'desc' => 'KPI Plagas'],
    'kpi_res_id:'                       => ['id' => 27, 'desc' => 'KPI Residuos'],
];

// Cargar catálogo para mostrar nombres
$catalogo = [];
$r = $mysqli->query("SELECT id_detailreport, detail_report FROM detail_report ORDER BY id_detailreport");
while ($row = $r->fetch_assoc()) $catalogo[(int)$row['id_detailreport']] = $row['detail_report'];

echo "==========================================================\n";
echo "DRY-RUN — Reclasificación de tbl_reporte\n";
echo "==========================================================\n\n";

$totalReporte = (int)$mysqli->query("SELECT COUNT(*) c FROM tbl_reporte")->fetch_assoc()['c'];
echo "Total registros en tbl_reporte: $totalReporte\n\n";

$totalMover = 0;
$totalYaOK  = 0;
$totalIdentificados = 0;
$resumenPorTag = [];

echo "--- Por tag (controlador identificable) ---\n";
echo str_pad('TAG', 38) . " | " . str_pad('id→', 4) . " | "
   . str_pad('grupo destino', 38) . " | "
   . str_pad('total', 7) . " | "
   . str_pad('ya OK', 7) . " | "
   . str_pad('a mover', 8) . " | desglose actual\n";
echo str_repeat('-', 200) . "\n";

foreach ($mapeo as $tag => $info) {
    $idDestino = $info['id'];
    $like = '%' . $tag . '%';

    $stmt = $mysqli->prepare("SELECT id_detailreport, COUNT(*) c FROM tbl_reporte WHERE observaciones LIKE ? GROUP BY id_detailreport ORDER BY c DESC");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();

    $total = 0; $yaOK = 0; $aMover = 0; $desglose = [];
    while ($row = $res->fetch_assoc()) {
        $idActual = (int)$row['id_detailreport'];
        $cnt = (int)$row['c'];
        $total += $cnt;
        if ($idActual === $idDestino) {
            $yaOK += $cnt;
        } else {
            $aMover += $cnt;
            $nombreActual = $catalogo[$idActual] ?? '???';
            $desglose[] = '[' . $idActual . ']' . $nombreActual . ' x' . $cnt;
        }
    }

    if ($total > 0) {
        $totalIdentificados += $total;
        $totalYaOK   += $yaOK;
        $totalMover  += $aMover;
        $nombreDestino = $catalogo[$idDestino] ?? '???';
        printf("%-38s | %4d | %-38s | %7d | %7d | %8d | %s\n",
            $tag,
            $idDestino,
            substr($nombreDestino, 0, 38),
            $total, $yaOK, $aMover,
            implode(' ', $desglose) ?: '-'
        );
        $resumenPorTag[$tag] = ['total' => $total, 'yaOK' => $yaOK, 'aMover' => $aMover];
    }
}

echo "\n=== RESUMEN ===\n";
echo "Docs identificados por tag:   $totalIdentificados\n";
echo "  Ya en grupo correcto:       $totalYaOK\n";
echo "  A mover en la migración:    $totalMover\n";
echo "Docs SIN tag reconocible:     " . ($totalReporte - $totalIdentificados) . " (NO se tocarán — uploads manuales, Takeout, etc.)\n";

// Categorías de los docs sin tag (informativo)
echo "\n--- Docs sin tag — categorías informativas (no se tocan) ---\n";
$consultas = [
    "Recuperado desde Takeout" => "observaciones LIKE '%Recuperado desde Takeout%'",
    "Recargado desde Takeout"  => "observaciones LIKE '%Recargado desde Takeout%'",
    "Generado automaticamente al firmar protocolo" => "observaciones LIKE '%Generado automaticamente al firmar protocolo%' AND observaciones NOT LIKE '%protocolo_alturas_cliente:%'",
    "Sin observaciones o NULL" => "(observaciones IS NULL OR observaciones = '')",
    "Otros (probablemente uploads manuales)" => null, // calculado al final
];
$tagsLikePart = [];
foreach ($mapeo as $tag => $_) $tagsLikePart[] = "observaciones LIKE '%" . $tag . "%'";
$tagsNotLike = "NOT (" . implode(' OR ', $tagsLikePart) . ")";
foreach ($consultas as $nombre => $where) {
    if ($where === null) continue;
    $q = "SELECT COUNT(*) c FROM tbl_reporte WHERE $tagsNotLike AND ($where)";
    $r = $mysqli->query($q);
    if ($r) {
        $c = (int)$r->fetch_assoc()['c'];
        printf("  %-50s : %d\n", $nombre, $c);
    }
}
$r = $mysqli->query("SELECT COUNT(*) c FROM tbl_reporte WHERE $tagsNotLike");
$cTotal = (int)$r->fetch_assoc()['c'];
echo "  (Total sin tag): $cTotal\n";

echo "\nDry-run completado. NADA se modificó.\n";
$mysqli->close();
