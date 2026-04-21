<?php
/**
 * Backfill: cierra actividades de tbl_pta_cliente para clientes que YA
 * firmaron el Protocolo de Alturas (protocolo_alturas_firmado = 1).
 *
 * - fecha_cierre = firma_alturas_fecha del cliente
 * - estado_actividad = 'CERRADA'
 * - porcentaje_avance = 100
 * - observaciones se le append: "Cerrada automaticamente al firmar protocolo YYYY-MM-DD HH:MM:SS"
 *
 * Ejecutar:
 *   LOCAL:      php app/SQL/backfill_cierre_pta_alturas.php
 *   PRODUCCION: DB_PROD_PASS=xxx php app/SQL/backfill_cierre_pta_alturas.php production
 */

$isProd = in_array('production', $argv ?? []);

if ($isProd) {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = 25060;
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    if (!$pass) { die("ERROR: falta DB_PROD_PASS\n"); }
    $ssl = [PDO::MYSQL_ATTR_SSL_CA => false, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
    $ssl    = [];
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, $ssl + [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "Entorno: " . ($isProd ? 'PRODUCCION' : 'LOCAL') . "\n";

$preview = $pdo->query("
    SELECT p.id_ptacliente, p.id_cliente, p.estado_actividad, c.firma_alturas_fecha, c.nombre_cliente
    FROM tbl_pta_cliente p
    INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
    WHERE c.protocolo_alturas_firmado = 1
      AND c.firma_alturas_fecha IS NOT NULL
      AND p.actividad_plandetrabajo LIKE '%Protocolo%Notificaci%n de Trabajo en Alturas%'
      AND p.estado_actividad = 'ABIERTA'
")->fetchAll(PDO::FETCH_ASSOC);

echo "Actividades a cerrar: " . count($preview) . "\n";
foreach ($preview as $r) {
    echo "  - pta#{$r['id_ptacliente']} | cli#{$r['id_cliente']} {$r['nombre_cliente']} | firma={$r['firma_alturas_fecha']}\n";
}

if (empty($preview)) {
    echo "Nada que actualizar.\n";
    exit(0);
}

$sql = "UPDATE tbl_pta_cliente p
        INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
        SET p.estado_actividad = 'CERRADA',
            p.fecha_cierre = DATE(c.firma_alturas_fecha),
            p.porcentaje_avance = 100,
            p.observaciones = CONCAT(
                COALESCE(NULLIF(p.observaciones,''), ''),
                CASE WHEN p.observaciones IS NULL OR p.observaciones = '' THEN '' ELSE ' | ' END,
                'Cerrada automaticamente al firmar protocolo ',
                c.firma_alturas_fecha
            ),
            p.updated_at = NOW()
        WHERE c.protocolo_alturas_firmado = 1
          AND c.firma_alturas_fecha IS NOT NULL
          AND p.actividad_plandetrabajo LIKE '%Protocolo%Notificaci%n de Trabajo en Alturas%'
          AND p.estado_actividad = 'ABIERTA'";

$afected = $pdo->exec($sql);
echo "Filas actualizadas: {$afected}\n";
echo "OK\n";
