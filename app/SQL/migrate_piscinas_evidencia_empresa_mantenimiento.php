<?php
/**
 * Script CLI — Amplía ENUM `campo` de tbl_piscina_evidencia_maestro
 * Uso: php migrate_piscinas_evidencia_empresa_mantenimiento.php [local|production]
 *
 * Agrega el valor 'empresa_mantenimiento' al ENUM para permitir evidencias
 * multi-foto del contrato/certificados de la empresa contratista.
 *
 * Operación ALTER COLUMN MODIFY: segura si no hay datos con 'empresa_mantenimiento'
 * (no existía antes, imposible que haya).
 */

if (php_sapi_name() !== 'cli') die('Solo CLI.');
$env = $argv[1] ?? 'local';
if ($env === 'local')          $cfg = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'propiedad_horizontal','ssl'=>false];
elseif ($env === 'production') $cfg = ['host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','port'=>25060,'user'=>'cycloid_userdb','password'=>getenv('DB_PROD_PASS') ?: '','database'=>'propiedad_horizontal','ssl'=>true];
else die("Uso: [local|production]\n");

echo "=== Amplia ENUM `campo` de tbl_piscina_evidencia_maestro ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$m = mysqli_init();
if ($cfg['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false); }
if (!@$m->real_connect($cfg['host'],$cfg['user'],$cfg['password'],$cfg['database'],$cfg['port'],null,$cfg['ssl']?MYSQLI_CLIENT_SSL:0)) die("ERR: " . $m->connect_error . "\n");
echo "Conectado.\n";

$sql = "ALTER TABLE tbl_piscina_evidencia_maestro MODIFY COLUMN `campo` ENUM(
    'concepto_sanitario','dea','operador_cert','doc_art15',
    'plan_saneamiento','manejo_quimicos','area_residuos',
    'contenedores_color','tablero_publico','empresa_mantenimiento'
) NOT NULL";

if ($m->query($sql)) echo "[OK] ENUM ampliado con 'empresa_mantenimiento'\n";
else                 echo "[ERR]: " . $m->error . "\n";

$m->close();
echo "DONE.\n";
