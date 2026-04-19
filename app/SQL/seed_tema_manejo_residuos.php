<?php
/**
 * Seed: tema "Manejo de Residuos en Propiedad Horizontal" con 6 preguntas.
 *
 * Idempotente: si el tema ya existe, no re-inserta.
 *
 * Uso:
 *   LOCAL       : php app/SQL/seed_tema_manejo_residuos.php
 *   PRODUCCIÓN  : DB_PROD_PASS=xxx php app/SQL/seed_tema_manejo_residuos.php production
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

$NOMBRE_TEMA = 'Manejo de Residuos en Propiedad Horizontal';

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
        'Evaluación de conocimientos sobre clasificación y manejo seguro de residuos en propiedades horizontales: separación en la fuente, riesgos sanitarios, cortes y condiciones inseguras en cuartos de residuos.',
    ]);
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema '{$NOMBRE_TEMA}' creado (id={$idTema})\n";

    $preguntas = [
        [
            'texto'    => 'Un residuo mal clasificado (por ejemplo, vidrio en bolsa ordinaria) puede generar principalmente:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Solo desorden.',
                'b' => 'Pérdida de espacio.',
                'c' => 'Riesgos de corte y manejo inadecuado.',
                'd' => 'Ningún riesgo.',
            ],
        ],
        [
            'texto'    => '¿Cuál es el principal problema de mezclar residuos aprovechables con orgánicos?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Se reduce el volumen.',
                'b' => 'Se pierde el aprovechamiento y aumenta el riesgo sanitario.',
                'c' => 'Se facilita la recolección.',
                'd' => 'No genera impacto.',
            ],
        ],
        [
            'texto'    => 'Un derrame en el cuarto de residuos puede generar:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Solo mal olor.',
                'b' => 'Riesgo de caída y condiciones inseguras.',
                'c' => 'Ningún riesgo.',
                'd' => 'Solo suciedad.',
            ],
        ],
        [
            'texto'    => 'En un accidente por corte con residuos, la causa más adecuada es:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Mala suerte.',
                'b' => 'Uso de guantes.',
                'c' => 'Fallas en la separación y manejo previo del residuo.',
                'd' => 'El tamaño del residuo.',
            ],
        ],
        [
            'texto'    => 'Manipular residuos sin elementos de protección personal puede generar:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Solo incomodidad.',
                'b' => 'Exposición a cortes y contaminación.',
                'c' => 'Mayor rapidez.',
                'd' => 'Ningún riesgo.',
            ],
        ],
        [
            'texto'    => 'Un cuarto de residuos desordenado genera:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Solo mal aspecto.',
                'b' => 'Riesgos sanitarios y posibles accidentes.',
                'c' => 'Ahorro de tiempo.',
                'd' => 'Ningún impacto.',
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
