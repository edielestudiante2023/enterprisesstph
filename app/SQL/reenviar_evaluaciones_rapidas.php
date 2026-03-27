#!/usr/bin/env php
<?php
/**
 * Reenvía emails de "Evaluación Rápida Post-Visita" para todas las actas completadas.
 * Uso: php reenviar_evaluaciones_rapidas.php
 *
 * Este script es de un solo uso — eliminar después de ejecutar.
 */

// --- Config DB producción ---
$dbHost = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$dbPort = 25060;
$dbName = 'propiedad_horizontal';
$dbUser = 'cycloid_userdb';
$dbPass = getenv('DB_PROD_PASS');

if (!$dbPass) {
    die("ERROR: Falta variable DB_PROD_PASS. Uso: DB_PROD_PASS=xxx php reenviar_evaluaciones_rapidas.php\n");
}

$apiKey = getenv('SG_KEY');
if (!$apiKey) {
    die("ERROR: Falta variable SG_KEY. Uso: SG_KEY=SG.xxx DB_PROD_PASS=xxx php reenviar_evaluaciones_rapidas.php\n");
}

// --- Conexión ---
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_SSL_CA => '/www/ca/ca-certificate_cycloid.crt',
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
];
$pdo = new PDO($dsn, $dbUser, $dbPass, $opts);
echo "Conectado a BD.\n";

// --- Obtener actas completadas ---
$sql = "
    SELECT a.id, a.fecha_visita, a.id_cliente, a.id_consultor,
           c.nombre_cliente, co.nombre_consultor, co.correo_consultor
    FROM tbl_acta_visita a
    LEFT JOIN tbl_clientes c ON c.id_cliente = a.id_cliente
    LEFT JOIN tbl_consultor co ON co.id_consultor = a.id_consultor
    WHERE a.estado = 'completo'
    ORDER BY a.fecha_visita DESC
";
$actas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo count($actas) . " actas completadas encontradas.\n\n";

$baseUrl = 'https://phorizontal.cycloidtalent.com';
$enviados = 0;
$errores  = 0;

foreach ($actas as $acta) {
    $actaId    = (int) $acta['id'];
    $clienteId = (int) $acta['id_cliente'];
    $token     = substr(hash('sha256', $actaId . '|' . $clienteId . '|evvisita2026'), 0, 24);
    $url       = "{$baseUrl}/acta-visita/evaluaciones-visita/{$actaId}/{$token}";
    $fecha     = date('d/m/Y', strtotime($acta['fecha_visita']));
    $nomCli    = htmlspecialchars($acta['nombre_cliente'] ?? '');
    $nomCons   = htmlspecialchars($acta['nombre_consultor'] ?? 'Consultor');
    $correo    = $acta['correo_consultor'] ?? '';
    $urlEsc    = htmlspecialchars($url);

    if (empty($correo)) {
        echo "  SKIP acta #{$actaId} — sin correo consultor\n";
        continue;
    }

    $html = "
    <div style='font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:#1c2437;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
            <h1 style='color:#bd9751;margin:0;font-size:20px;'>Evaluación Rápida Post-Visita</h1>
        </div>
        <div style='padding:25px;background:#f8f9fa;border-radius:0 0 10px 10px;'>
            <p>Hola <strong>{$nomCons}</strong>,</p>
            <p>El acta de visita del <strong>{$fecha}</strong> para <strong>{$nomCli}</strong> ha sido finalizada.</p>
            <p>Usa este enlace para marcar los ítems de cumplimiento que se cerraron en esta visita:</p>
            <div style='text-align:center;margin:24px 0;'>
                <a href='{$urlEsc}' style='background:#bd9751;color:white;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;'>
                    ✔ Actualizar Evaluaciones
                </a>
            </div>
            <p style='font-size:12px;color:#999;word-break:break-all;'>Enlace directo: {$urlEsc}</p>
            <p style='color:#999;font-size:11px;margin-top:20px;'>Generado por SG-SST Cycloid Talent.</p>
        </div>
    </div>";

    $payload = json_encode([
        'personalizations' => [[
            'to'      => [['email' => $correo, 'name' => $nomCons]],
            'subject' => "Evaluaciones rápidas — {$nomCli} — {$fecha}",
        ]],
        'from'    => ['email' => 'notificacion.cycloidtalent@cycloidtalent.com', 'name' => 'Cycloid Talent - SG-SST'],
        'content' => [['type' => 'text/html', 'value' => $html]],
        'tracking_settings' => [
            'click_tracking' => ['enable' => false, 'enable_text' => false],
        ],
    ]);

    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        echo "  OK  acta #{$actaId} → {$correo} ({$nomCli} — {$fecha})\n";
        $enviados++;
    } else {
        echo "  ERR acta #{$actaId} → HTTP {$httpCode}: {$response}\n";
        $errores++;
    }

    // Pausa 200ms para no saturar SendGrid
    usleep(200000);
}

echo "\n=== RESUMEN ===\n";
echo "Enviados: {$enviados}\n";
echo "Errores:  {$errores}\n";
echo "Total:    " . count($actas) . "\n";
