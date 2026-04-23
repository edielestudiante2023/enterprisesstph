<?php
/**
 * Script CLI — categoria de evidencia por piscina: ENUM -> VARCHAR(60)
 * Uso: php migrate_piscina_detalle_evidencia_categoria_varchar.php [local|production]
 *
 * Cambia tbl_piscina_detalle_evidencia.categoria de ENUM fijo a VARCHAR(60)
 * para permitir categorias personalizadas por parte del consultor.
 *
 * ALTER MODIFY COLUMN: seguro, los valores existentes se preservan
 * (los valores del ENUM eran strings de todas formas).
 */

if (php_sapi_name() !== 'cli') die('Solo CLI.');
$env = $argv[1] ?? 'local';
if ($env === 'local')          $cfg = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'propiedad_horizontal','ssl'=>false];
elseif ($env === 'production') $cfg = ['host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','port'=>25060,'user'=>'cycloid_userdb','password'=>getenv('DB_PROD_PASS') ?: '','database'=>'propiedad_horizontal','ssl'=>true];
else die("Uso: [local|production]\n");

echo "=== ALTER tbl_piscina_detalle_evidencia.categoria -> VARCHAR(60) ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$m = mysqli_init();
if ($cfg['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false); }
if (!@$m->real_connect($cfg['host'],$cfg['user'],$cfg['password'],$cfg['database'],$cfg['port'],null,$cfg['ssl']?MYSQLI_CLIENT_SSL:0)) die("ERR: " . $m->connect_error . "\n");
echo "Conectado.\n";

$sql = "ALTER TABLE tbl_piscina_detalle_evidencia MODIFY COLUMN `categoria` VARCHAR(60) NOT NULL DEFAULT 'OTRA'";
if ($m->query($sql)) echo "[OK] categoria ahora es VARCHAR(60)\n";
else                 echo "[ERR]: " . $m->error . "\n";

$m->close();
echo "DONE.\n";
