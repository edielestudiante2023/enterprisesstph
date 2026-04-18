<?php
/**
 * Seed: tema "Riesgo Biomecánico" con 7 preguntas de evaluación.
 * Fuente: PDF "EVALUACIÓN RIESGO BIOMECÁNICO" (Google Forms).
 *
 * Idempotente: si el tema ya existe, no re-inserta.
 *
 * Uso:
 *   LOCAL       : php app/SQL/seed_tema_riesgo_biomecanico.php
 *   PRODUCCIÓN  : DB_PROD_PASS=xxx php app/SQL/seed_tema_riesgo_biomecanico.php production
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

// ── Verificar si el tema ya existe ──────────────────────────────────────────
$temaExiste = $pdo->query("SELECT id FROM tbl_evaluacion_tema WHERE nombre = 'Riesgo Biomecánico' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if ($temaExiste) {
    echo "INFO: tema 'Riesgo Biomecánico' ya existe (id={$temaExiste['id']}), no se re-inserta.\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    // ── 1. Insertar tema ────────────────────────────────────────────────────
    $pdo->exec("
        INSERT INTO tbl_evaluacion_tema (nombre, descripcion, estado, created_at, updated_at)
        VALUES ('Riesgo Biomecánico',
                'Evaluación de conocimientos sobre identificación, prevención y control del riesgo biomecánico en el trabajo: posturas, manipulación de cargas, movimientos repetitivos y pausas activas.',
                'activo', NOW(), NOW())
    ");
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema 'Riesgo Biomecánico' creado (id={$idTema})\n";

    // ── 2. Preguntas + opciones ────────────────────────────────────────────
    $preguntas = [
        [
            'texto'   => 'El riesgo biomecánico se relaciona principalmente con:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'La exposición a productos químicos.',
                'b' => 'La calidad del aire en el entorno.',
                'c' => 'La forma en que se realizan los movimientos y posturas.',
                'd' => 'El nivel de iluminación del área.',
            ],
        ],
        [
            'texto'   => 'Levantar cargas con la espalda inclinada puede generar:',
            'correcta' => 'a',
            'opciones' => [
                'a' => 'Sobrecarga en la zona lumbar y posible lesión.',
                'b' => 'Aumento de la velocidad en la tarea.',
                'c' => 'Reducción del esfuerzo físico.',
                'd' => 'Mejor control del movimiento.',
            ],
        ],
        [
            'texto'   => 'Permanecer en una misma posición durante largos periodos puede causar:',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'Mejora en la concentración.',
                'b' => 'Mayor estabilidad en el trabajo.',
                'c' => 'Reducción del esfuerzo corporal.',
                'd' => 'Fatiga muscular y sobrecarga física.',
            ],
        ],
        [
            'texto'   => 'Una técnica adecuada para levantar cargas consiste en:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Inclinar el tronco y usar la espalda.',
                'b' => 'Flexionar las rodillas y mantener el cuerpo alineado.',
                'c' => 'Girar el torso durante el levantamiento.',
                'd' => 'Levantar rápidamente para reducir esfuerzo.',
            ],
        ],
        [
            'texto'   => 'Una señal temprana de sobrecarga física puede ser:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Aumento de energía durante la jornada.',
                'b' => 'Dolor o molestia persistente en músculos o articulaciones.',
                'c' => 'Mayor rapidez en la ejecución de tareas.',
                'd' => 'Disminución del tiempo de trabajo.',
            ],
        ],
        [
            'texto'   => 'Las pausas activas permiten:',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'Reducir el tiempo productivo.',
                'b' => 'Aumentar la carga de trabajo.',
                'c' => 'Evitar el uso de herramientas.',
                'd' => 'Disminuir la fatiga y recuperar la función muscular.',
            ],
        ],
        [
            'texto'   => 'Un trabajador realiza levantamientos repetitivos sin técnica adecuada ni pausas. Esto puede generar:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Adaptación del cuerpo sin consecuencias.',
                'b' => 'Acumulación de carga física y riesgo de lesión.',
                'c' => 'Mejora en la resistencia muscular.',
                'd' => 'Disminución del esfuerzo requerido.',
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
