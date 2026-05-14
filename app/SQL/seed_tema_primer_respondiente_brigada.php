<?php
/**
 * Seed: tema "Primer Respondiente y Brigada de Emergencias" con 10 preguntas. Idempotente.
 *
 * Uso:
 *   LOCAL      : php app/SQL/seed_tema_primer_respondiente_brigada.php
 *   PRODUCCIÓN : DB_PROD_PASS=xxx php app/SQL/seed_tema_primer_respondiente_brigada.php production
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

$NOMBRE_TEMA = 'Primer Respondiente y Brigada de Emergencias';

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
        'Evaluación de conocimientos para primer respondiente y brigada de emergencias: conducta PAS, atención de heridas y quemaduras, seguridad de la escena, clases de fuego, técnica PASS, simulacros y plan de evacuación.',
    ]);
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema '{$NOMBRE_TEMA}' creado (id={$idTema})\n";

    $preguntas = [
        [
            'texto'    => 'Durante la conducta PAS, la acción "Socorrer" debe realizarse:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Inmediatamente al llegar a la escena.',
                'b' => 'Después de verificar que el lugar sea seguro y solicitar ayuda.',
                'c' => 'Solo cuando llegue la ambulancia.',
                'd' => 'Únicamente si la víctima está consciente.',
            ],
        ],
        [
            'texto'    => 'Un trabajador encuentra una persona inconsciente cerca de un cable energizado. ¿Cuál debe ser su primera acción?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Mover rápidamente a la víctima.',
                'b' => 'Verificar respiración.',
                'c' => 'Asegurar que no exista riesgo eléctrico en la escena.',
                'd' => 'Iniciar maniobras de reanimación.',
            ],
        ],
        [
            'texto'    => '¿Cuál de las siguientes situaciones corresponde a una herida grave?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Raspón superficial sin sangrado.',
                'b' => 'Cortadura leve controlada con agua.',
                'c' => 'Lesión con sangrado abundante y compromiso de tejidos.',
                'd' => 'Enrojecimiento leve de la piel.',
            ],
        ],
        [
            'texto'    => 'En una crisis convulsiva, una actuación incorrecta puede generar:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Disminución del episodio.',
                'b' => 'Mayor seguridad para la víctima.',
                'c' => 'Lesiones adicionales en la persona afectada.',
                'd' => 'Recuperación inmediata.',
            ],
        ],
        [
            'texto'    => '¿Por qué no debe retirarse un objeto incrustado en una herida?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Porque dificulta el traslado.',
                'b' => 'Porque puede aumentar la hemorragia y el daño interno.',
                'c' => 'Porque genera dolor al auxiliador.',
                'd' => 'Porque impide identificar la lesión.',
            ],
        ],
        [
            'texto'    => 'Una quemadura con ampollas y dolor intenso corresponde a:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Primer grado.',
                'b' => 'Segundo grado.',
                'c' => 'Tercer grado.',
                'd' => 'Quemadura química.',
            ],
        ],
        [
            'texto'    => '¿Cuál es uno de los principales beneficios de realizar simulacros de emergencia?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Eliminar completamente los riesgos.',
                'b' => 'Evitar futuras capacitaciones.',
                'c' => 'Mejorar la capacidad de respuesta del personal ante emergencias reales.',
                'd' => 'Reemplazar los planes de evacuación.',
            ],
        ],
        [
            'texto'    => '¿Cuál es la principal finalidad del plan de evacuación?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Reducir daños materiales exclusivamente.',
                'b' => 'Garantizar la continuidad de la operación.',
                'c' => 'Proteger la vida de las personas ante una emergencia.',
                'd' => 'Mantener el orden administrativo.',
            ],
        ],
        [
            'texto'    => 'Un fuego originado en un tablero eléctrico energizado corresponde a la clasificación:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Tipo A.',
                'b' => 'Tipo B.',
                'c' => 'Tipo C.',
                'd' => 'Tipo K.',
            ],
        ],
        [
            'texto'    => 'En la técnica PASS, realizar movimiento de barrido tiene como objetivo:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Reducir el peso del extintor.',
                'b' => 'Expandir el humo.',
                'c' => 'Cubrir uniformemente la base del fuego.',
                'd' => 'Disminuir la presión interna.',
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
