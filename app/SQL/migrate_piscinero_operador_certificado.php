<?php
/**
 * Script CLI — Alinea tbl_inspeccion_piscinero con Res 234/2026 Minsalud
 * Uso: php migrate_piscinero_operador_certificado.php [local|production]
 *
 * Agrega:
 *   - certificacion_operador_piscinas (Art. 11 num 7 Res 234)
 *   - operador_entidad_certificadora
 *   - operador_vigencia
 *   - foto_certificado_operador
 *   - capacitacion_dosificacion_quimica (Art. 5 Res 234)
 *
 * ALTER TABLE (seguro, sin drop de datos).
 */

if (php_sapi_name() !== 'cli') die('Solo CLI.');
$env = $argv[1] ?? 'local';
if ($env === 'local')      $cfg = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'propiedad_horizontal','ssl'=>false];
elseif ($env === 'production') $cfg = ['host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','port'=>25060,'user'=>'cycloid_userdb','password'=>getenv('DB_PROD_PASS') ?: '','database'=>'propiedad_horizontal','ssl'=>true];
else die("Uso: [local|production]\n");

echo "=== ALTER tbl_inspeccion_piscinero — alineacion Res 234/2026 ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$m = mysqli_init();
if ($cfg['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false); }
if (!@$m->real_connect($cfg['host'],$cfg['user'],$cfg['password'],$cfg['database'],$cfg['port'],null,$cfg['ssl']?MYSQLI_CLIENT_SSL:0)) die("ERR: " . $m->connect_error . "\n");
echo "Conectado.\n";

$alters = [
    'certificacion_operador_piscinas'   => "ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'Art. 11 num 7 Res 234/2026'",
    'operador_entidad_certificadora'    => "VARCHAR(200) NULL COMMENT 'SENA/IDEAM/universidad/autoridad municipal'",
    'operador_vigencia'                 => "DATE NULL",
    'foto_certificado_operador'         => "VARCHAR(255) NULL",
    'capacitacion_dosificacion_quimica' => "ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'Art. 5 Res 234/2026'",
];

$ok=0; $skip=0; $err=0;
foreach ($alters as $col => $ddl) {
    $r = $m->query("SHOW COLUMNS FROM tbl_inspeccion_piscinero LIKE '$col'");
    if ($r && $r->num_rows > 0) { echo "  [skip] $col (ya existe)\n"; $skip++; continue; }
    if ($m->query("ALTER TABLE tbl_inspeccion_piscinero ADD COLUMN `$col` $ddl")) { echo "  [ok]   $col\n"; $ok++; }
    else { echo "  [ERR] $col: " . $m->error . "\n"; $err++; }
}

echo "\nAgregadas: $ok | Saltadas: $skip | Errores: $err\n";
echo $err === 0 ? "MIGRACION OK.\n" : "REVISAR ERRORES.\n";
$m->close();
