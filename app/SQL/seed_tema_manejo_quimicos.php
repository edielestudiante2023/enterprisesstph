<?php
/**
 * Seed: tema "Manejo Seguro de Sustancias Químicas" con 6 preguntas.
 *
 * Idempotente: si el tema ya existe, no re-inserta.
 *
 * Uso:
 *   LOCAL       : php app/SQL/seed_tema_manejo_quimicos.php
 *   PRODUCCIÓN  : DB_PROD_PASS=xxx php app/SQL/seed_tema_manejo_quimicos.php production
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
    if (!$pass) {
        fwrite(STDERR, "ERROR: DB_PROD_PASS no definida.\n");
        exit(1);
    }
} else {
    $host = '127.0.0.1'; $port = 3306;
    $db   = 'propiedad_horizontal'; $user = 'root'; $pass = ''; $ssl = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }

$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n";

$NOMBRE_TEMA = 'Manejo Seguro de Sustancias Químicas';

// ── Verificar si el tema ya existe ──────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM tbl_evaluacion_tema WHERE nombre = ? LIMIT 1");
$stmt->execute([$NOMBRE_TEMA]);
$temaExiste = $stmt->fetch(PDO::FETCH_ASSOC);

if ($temaExiste) {
    echo "INFO: tema '{$NOMBRE_TEMA}' ya existe (id={$temaExiste['id']}), no se re-inserta.\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    // ── 1. Insertar tema ────────────────────────────────────────────────────
    $pdo->prepare("
        INSERT INTO tbl_evaluacion_tema (nombre, descripcion, estado, created_at, updated_at)
        VALUES (?, ?, 'activo', NOW(), NOW())
    ")->execute([
        $NOMBRE_TEMA,
        'Evaluación de conocimientos sobre el manejo seguro de sustancias químicas: etiquetado, hojas de seguridad (SDS), mezclas peligrosas y uso de elementos de protección personal.',
    ]);
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema '{$NOMBRE_TEMA}' creado (id={$idTema})\n";

    // ── 2. Preguntas + opciones ────────────────────────────────────────────
    $preguntas = [
        [
            'texto'    => 'El principal riesgo de una sustancia química está en:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Su color.',
                'b' => 'Su uso incorrecto.',
                'c' => 'Su tamaño.',
                'd' => 'Su olor.',
            ],
        ],
        [
            'texto'    => 'Mezclar productos químicos puede generar:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Mejor limpieza.',
                'b' => 'Reacciones peligrosas o gases tóxicos.',
                'c' => 'Ahorro.',
                'd' => 'Ningún riesgo.',
            ],
        ],
        [
            'texto'    => 'Un producto sin etiqueta representa:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Orden.',
                'b' => 'Uso seguro.',
                'c' => 'Riesgo por desconocimiento del contenido.',
                'd' => 'Ahorro.',
            ],
        ],
        [
            'texto'    => 'La hoja de seguridad permite:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Saber el precio.',
                'b' => 'Conocer riesgos y uso seguro.',
                'c' => 'Saber el proveedor.',
                'd' => 'Reducir consumo.',
            ],
        ],
        [
            'texto'    => 'Antes de usar un químico se debe:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Mezclarlo.',
                'b' => 'Usarlo directamente.',
                'c' => 'Leer la etiqueta.',
                'd' => 'Olerlo.',
            ],
        ],
        [
            'texto'    => 'Trabajar sin protección con químicos puede generar:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Mayor rapidez.',
                'b' => 'Afectaciones a la salud.',
                'c' => 'Ningún riesgo.',
                'd' => 'Solo incomodidad.',
            ],
        ],
    ];

    $stmtP = $pdo->prepare("
        INSERT INTO tbl_evaluacion_pregunta (id_tema, orden, texto, correcta, created_at, updated_at)
        VALUES (?, ?, ?, ?, NOW(), NOW())
    ");
    $stmtO = $pdo->prepare("
        INSERT INTO tbl_evaluacion_opcion (id_pregunta, letra, texto) VALUES (?, ?, ?)
    ");

    foreach ($preguntas as $i => $p) {
        $stmtP->execute([$idTema, $i + 1, $p['texto'], $p['correcta']]);
        $idPregunta = (int) $pdo->lastInsertId();
        foreach ($p['opciones'] as $letra => $texto) {
            $stmtO->execute([$idPregunta, $letra, $texto]);
        }
        echo "  OK pregunta " . ($i + 1) . " (correcta={$p['correcta']}): " . mb_substr($p['texto'], 0, 60) . "...\n";
    }

    $pdo->commit();
    echo "\nSeed completado. id_tema={$idTema}\n";
} catch (\Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
