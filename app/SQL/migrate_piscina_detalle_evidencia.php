<?php
/**
 * Script CLI — Agrega tabla multi-foto para evidencia por piscina detalle
 * Uso: php migrate_piscina_detalle_evidencia.php [local|production]
 *
 * Crea tbl_piscina_detalle_evidencia para N fotos por piscina con una
 * categoría opcional (infraestructura, avisos, emergencia, higiene, agua, otra).
 *
 * NO toca la columna `foto` existente de tbl_piscina_detalle (sigue funcionando
 * como "foto principal/portada"). Las fotos adicionales van aquí.
 */

if (php_sapi_name() !== 'cli') die('Solo CLI.');
$env = $argv[1] ?? 'local';
if ($env === 'local')          $cfg = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'propiedad_horizontal','ssl'=>false];
elseif ($env === 'production') $cfg = ['host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','port'=>25060,'user'=>'cycloid_userdb','password'=>getenv('DB_PROD_PASS') ?: '','database'=>'propiedad_horizontal','ssl'=>true];
else die("Uso: [local|production]\n");

echo "=== CREATE tbl_piscina_detalle_evidencia ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$m = mysqli_init();
if ($cfg['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false); }
if (!@$m->real_connect($cfg['host'],$cfg['user'],$cfg['password'],$cfg['database'],$cfg['port'],null,$cfg['ssl']?MYSQLI_CLIENT_SSL:0)) die("ERR: " . $m->connect_error . "\n");
echo "Conectado.\n";

$sql = "CREATE TABLE IF NOT EXISTS `tbl_piscina_detalle_evidencia` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_piscina_detalle` INT NOT NULL,
    `categoria` ENUM('INFRAESTRUCTURA','AVISOS','EMERGENCIA','HIGIENE','AGUA','OTRA') NOT NULL DEFAULT 'OTRA',
    `orden` SMALLINT NOT NULL DEFAULT 0,
    `foto_path` VARCHAR(255) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_pis_det_evid`
        FOREIGN KEY (`id_piscina_detalle`) REFERENCES `tbl_piscina_detalle`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_pis_det_evid_detalle` (`id_piscina_detalle`),
    INDEX `idx_pis_det_evid_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($m->query($sql)) echo "[OK] tabla creada (o ya existia)\n";
else                  echo "[ERR]: " . $m->error . "\n";

$m->close();
echo "DONE.\n";
