<?php
/**
 * Seed: tema "Riesgo Mecánico" con 7 preguntas.
 * Idempotente.
 *
 * Uso:
 *   LOCAL      : php app/SQL/seed_tema_riesgo_mecanico.php
 *   PRODUCCIÓN : DB_PROD_PASS=xxx php app/SQL/seed_tema_riesgo_mecanico.php production
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

$NOMBRE_TEMA = 'Riesgo Mecánico';

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
        'Evaluación de conocimientos sobre identificación y control del riesgo mecánico: uso seguro de herramientas, almacenamiento, intervención en equipos, EPP y controles preventivos vs correctivos.',
    ]);
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema '{$NOMBRE_TEMA}' creado (id={$idTema})\n";

    $preguntas = [
        [
            'texto'    => 'Un trabajador utiliza una herramienta en buen estado, sin EPP, en un área con tránsito de personas. ¿Cuál es la evaluación MÁS completa del riesgo?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Solo existe un acto inseguro.',
                'b' => 'Solo existe una condición insegura.',
                'c' => 'Existe un acto inseguro con posible afectación a terceros.',
                'd' => 'No hay riesgo si la herramienta está en buen estado.',
            ],
        ],
        [
            'texto'    => 'En un área de mantenimiento, las herramientas están en buen estado pero almacenadas en altura sin sujeción. ¿Cuál es el riesgo principal?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Riesgo locativo.',
                'b' => 'Riesgo mecánico por caída de objetos.',
                'c' => 'Riesgo químico.',
                'd' => 'No hay riesgo si no se usan.',
            ],
        ],
        [
            'texto'    => 'Un trabajador decide ajustar una máquina en funcionamiento para ahorrar tiempo. ¿Cuál es el factor de riesgo MÁS crítico?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Estado de la máquina.',
                'b' => 'Acto inseguro por intervención en equipo en movimiento.',
                'c' => 'Falta de señalización.',
                'd' => 'Tipo de herramienta.',
            ],
        ],
        [
            'texto'    => 'En el uso de una guadaña, ¿Qué combinación de controles es MÁS efectiva para reducir el riesgo?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Solo experiencia del trabajador.',
                'b' => 'Uso de EPP + distancia de seguridad + revisión del equipo.',
                'c' => 'Trabajar rápido.',
                'd' => 'Evitar usar la herramienta.',
            ],
        ],
        [
            'texto'    => 'Un trabajador sufre un golpe con una herramienta porque estaba distraído y además la herramienta estaba en mal estado. ¿Cuál es la causa más adecuada del accidente?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Solo la distracción del trabajador.',
                'b' => 'Solo el mal estado de la herramienta.',
                'c' => 'La combinación de distracción y herramienta en mal estado.',
                'd' => 'Evento inevitable.',
            ],
        ],
        [
            'texto'    => '¿Cuál de las siguientes acciones es un control PREVENTIVO y no correctivo?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Reparar herramienta dañada después del accidente.',
                'b' => 'Reportar incidente ocurrido.',
                'c' => 'Inspeccionar herramientas antes de su uso.',
                'd' => 'Atender lesión.',
            ],
        ],
        [
            'texto'    => 'Un trabajador realiza mantenimiento con una herramienta, sin protección personal, en un área húmeda y con personas cerca. ¿Cuál es el principal problema de esta situación?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'No hay problema si el trabajador tiene experiencia.',
                'b' => 'Solo hay riesgo por el ambiente húmedo.',
                'c' => 'Existe un alto riesgo de accidente por el uso inseguro de la herramienta y posible afectación a otras personas.',
                'd' => 'El riesgo solo existe si ocurre un accidente.',
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
