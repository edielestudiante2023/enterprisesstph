<?php
/**
 * Seed: tema "Riesgo Público" con 7 preguntas. Idempotente.
 *
 * Uso:
 *   LOCAL      : php app/SQL/seed_tema_riesgo_publico.php
 *   PRODUCCIÓN : DB_PROD_PASS=xxx php app/SQL/seed_tema_riesgo_publico.php production
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060; $db = 'propiedad_horizontal'; $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS'); $ssl = true;
    if (!$pass) { fwrite(STDERR, "ERROR: DB_PROD_PASS no definida.\n"); exit(1); }
} else {
    $host = '127.0.0.1'; $port = 3306; $db = 'propiedad_horizontal'; $user = 'root'; $pass = ''; $ssl = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n";

$NOMBRE_TEMA = 'Riesgo Público';

$stmt = $pdo->prepare("SELECT id FROM tbl_evaluacion_tema WHERE nombre = ? LIMIT 1");
$stmt->execute([$NOMBRE_TEMA]);
$temaExiste = $stmt->fetch(PDO::FETCH_ASSOC);
if ($temaExiste) {
    echo "INFO: tema '{$NOMBRE_TEMA}' ya existe (id={$temaExiste['id']}), no se re-inserta.\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    $pdo->prepare("
        INSERT INTO tbl_evaluacion_tema (nombre, descripcion, estado, created_at, updated_at)
        VALUES (?, ?, 'activo', NOW(), NOW())
    ")->execute([
        $NOMBRE_TEMA,
        'Evaluación de conocimientos sobre identificación y manejo del riesgo público en propiedad horizontal: interacciones con personas, señales de alerta, comunicación en situaciones de tensión y prevención del escalamiento de conflictos.',
    ]);
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema '{$NOMBRE_TEMA}' creado (id={$idTema})\n";

    $preguntas = [
        [
            'texto'    => 'El riesgo público está relacionado principalmente con:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Condiciones físicas del entorno.',
                'b' => 'Interacciones con personas en el entorno laboral.',
                'c' => 'Uso de herramientas en el trabajo.',
                'd' => 'Exposición a sustancias químicas.',
            ],
        ],
        [
            'texto'    => 'Una situación de riesgo público puede aumentar cuando:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Se mantiene la calma en la interacción.',
                'b' => 'Se aplican los protocolos establecidos.',
                'c' => 'Se responde de forma impulsiva ante el usuario.',
                'd' => 'Se controla el acceso de manera adecuada.',
            ],
        ],
        [
            'texto'    => 'Una señal de alerta en una interacción puede ser:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Cumplimiento de normas.',
                'b' => 'Actitud colaborativa.',
                'c' => 'Cambio de comportamiento o tono de voz.',
                'd' => 'Registro adecuado del visitante.',
            ],
        ],
        [
            'texto'    => 'Ante una situación de tensión, una acción adecuada sería:',
            'correcta' => 'a',
            'opciones' => [
                'a' => 'Mantener la comunicación clara y controlada.',
                'b' => 'Elevar el tono para imponer autoridad.',
                'c' => 'Ignorar la situación.',
                'd' => 'Discutir con el usuario.',
            ],
        ],
        [
            'texto'    => 'Según lo visto en la capacitación, el riesgo público se caracteriza porque:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Depende únicamente de las condiciones físicas del lugar.',
                'b' => 'Se mantiene igual durante toda la situación.',
                'c' => 'Puede cambiar según el comportamiento de las personas involucradas.',
                'd' => 'Solo aparece en situaciones previamente identificadas.',
            ],
        ],
        [
            'texto'    => 'Una mala gestión del riesgo público puede generar:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Reducción de incidentes.',
                'b' => 'Mejora en la comunicación.',
                'c' => 'Escalamiento del conflicto o incidente.',
                'd' => 'Mayor control operativo.',
            ],
        ],
        [
            'texto'    => 'Un visitante se muestra molesto ante un control de acceso. Si el vigilante responde de forma confrontativa, es probable que:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'La situación se mantenga estable.',
                'b' => 'Se reduzca la tensión.',
                'c' => 'El conflicto aumente en intensidad.',
                'd' => 'El usuario acepte la norma.',
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
