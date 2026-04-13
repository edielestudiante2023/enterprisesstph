<?php

/**
 * test_plan_emergencia_ia_ping.php
 * --------------------------------------------------------
 * Smoke test del PlanEmergenciaIAService.
 * Hace una llamada minima al API de Claude (50 tokens out)
 * para verificar conectividad, autenticacion y configuracion.
 *
 * Uso:
 *   php app/SQL/test_plan_emergencia_ia_ping.php
 *
 * Requiere:
 *   - ANTHROPIC_API_KEY en .env
 *   - ANTHROPIC_MODEL en .env (opcional, default claude-sonnet-4-6)
 */

if (PHP_SAPI !== 'cli') {
    exit("Solo se ejecuta por CLI\n");
}

// Cargar bootstrap CodeIgniter para que .env se cargue y autoload funcione.
$rootPath = dirname(__DIR__, 2);
require $rootPath . '/vendor/autoload.php';

// Cargar .env manualmente
$envFile = $rootPath . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if ($k && !getenv($k)) {
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
        }
    }
}

echo "==========================================\n";
echo "Smoke test PlanEmergenciaIAService\n";
echo "==========================================\n\n";

echo "ANTHROPIC_API_KEY: " . (getenv('ANTHROPIC_API_KEY') ? '[OK presente]' : '[FALTA]') . "\n";
echo "ANTHROPIC_MODEL:   " . (getenv('ANTHROPIC_MODEL') ?: '(default)') . "\n\n";

require_once $rootPath . '/app/Libraries/PlanEmergenciaIAService.php';

$svc = new \App\Libraries\PlanEmergenciaIAService();

echo "Llamando ping()...\n";
$inicio = microtime(true);
$resp = $svc->ping();
$dur = round((microtime(true) - $inicio) * 1000);

echo "Duracion: {$dur} ms\n\n";

if (!$resp['ok']) {
    echo "ERROR: " . ($resp['error'] ?? 'desconocido') . "\n";
    if (isset($resp['raw'])) {
        echo "Raw: " . $resp['raw'] . "\n";
    }
    exit(1);
}

echo "OK\n";
echo "Modelo:    " . $resp['data']['modelo'] . "\n";
echo "Respuesta: " . $resp['data']['respuesta'] . "\n";
echo "Tokens in:  " . $resp['data']['tokens_in'] . "\n";
echo "Tokens out: " . $resp['data']['tokens_out'] . "\n";
echo "\n==> Servicio IA funcionando correctamente.\n";
exit(0);
