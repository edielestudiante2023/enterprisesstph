<?php
/**
 * Reset contrato id=20 (CONT-2025-0039 — CONJUNTO RESIDENCIAL SAN SEBASTIAN PRIMERA ETAPA)
 * para poder regenerarlo y enviarlo a firma del cliente.
 *
 * Uso: DB_PROD_PASS='xxx' php app/SQL/reset_contrato_20.php
 */

$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$db   = 'propiedad_horizontal';
$user = 'cycloid_userdb';
$pass = getenv('DB_PROD_PASS');
if (!$pass) { fwrite(STDERR, "ERROR: DB_PROD_PASS no seteado\n"); exit(1); }

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$id = 20;

$before = $pdo->query("SELECT id_contrato, numero_contrato, estado, estado_firma, firma_cliente_imagen, codigo_verificacion FROM tbl_contratos WHERE id_contrato={$id}")->fetch(PDO::FETCH_ASSOC);
if (!$before) { fwrite(STDERR, "Contrato {$id} no existe\n"); exit(1); }

$backupFile = __DIR__ . '/backup_contrato_20_' . date('Ymd_His') . '.json';
file_put_contents($backupFile, json_encode($before, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Backup escrito en: {$backupFile}\n";
echo "ANTES: " . json_encode($before, JSON_UNESCAPED_UNICODE) . "\n";

$stmt = $pdo->prepare("UPDATE tbl_contratos
    SET estado = 'activo',
        estado_firma = 'sin_enviar',
        firma_cliente_imagen = NULL
    WHERE id_contrato = :id");
$stmt->execute([':id' => $id]);
echo "Filas afectadas: " . $stmt->rowCount() . "\n";

$after = $pdo->query("SELECT id_contrato, numero_contrato, estado, estado_firma, firma_cliente_imagen, codigo_verificacion FROM tbl_contratos WHERE id_contrato={$id}")->fetch(PDO::FETCH_ASSOC);
echo "DESPUES: " . json_encode($after, JSON_UNESCAPED_UNICODE) . "\n";
