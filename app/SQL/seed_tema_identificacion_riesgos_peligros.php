<?php
/**
 * Seed: tema "Identificación de Riesgos y Peligros" con 7 preguntas. Idempotente.
 *
 * Uso:
 *   LOCAL      : php app/SQL/seed_tema_identificacion_riesgos_peligros.php
 *   PRODUCCIÓN : DB_PROD_PASS=xxx php app/SQL/seed_tema_identificacion_riesgos_peligros.php production
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

$NOMBRE_TEMA = 'Identificación de Riesgos y Peligros';

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
        'Evaluación de conocimientos sobre conceptos básicos de peligro y riesgo en el SG-SST: identificación de condiciones inseguras, valoración de la probabilidad de daño y acciones coherentes ante peligros detectados.',
    ]);
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema '{$NOMBRE_TEMA}' creado (id={$idTema})\n";

    $preguntas = [
        [
            'texto'    => 'Un peligro se entiende como:',
            'correcta' => 'a',
            'opciones' => [
                'a' => 'Una condición que puede generar daño en una actividad.',
                'b' => 'Una situación que ocurre después de un incidente.',
                'c' => 'Una acción que corrige una falla en el proceso.',
                'd' => 'Un resultado derivado de una lesión.',
            ],
        ],
        [
            'texto'    => 'El concepto de riesgo está relacionado con:',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'La presencia de una condición insegura en el área.',
                'b' => 'La posibilidad de que ocurra un daño ante un peligro.',
                'c' => 'La actividad realizada dentro de la jornada laboral.',
                'd' => 'La consecuencia generada por un accidente previo.',
            ],
        ],
        [
            'texto'    => 'En una zona con piso húmedo, la condición identificada corresponde a:',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'Un control aplicado en la actividad.',
                'b' => 'Una consecuencia de la operación.',
                'c' => 'Una acción preventiva ejecutada.',
                'd' => 'Un peligro presente en el entorno.',
            ],
        ],
        [
            'texto'    => 'La posibilidad de perder el equilibrio en esa misma zona corresponde a:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Un evento posterior al accidente.',
                'b' => 'Una condición propia del área.',
                'c' => 'Una probabilidad asociada al daño.',
                'd' => 'Una acción derivada del control.',
            ],
        ],
        [
            'texto'    => 'Cuando un peligro no es identificado durante una actividad, es posible que:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Se mantenga la condición sin cambios visibles.',
                'b' => 'Se reduzca la exposición en la tarea.',
                'c' => 'Se incremente la probabilidad de ocurrencia.',
                'd' => 'Se modifique la forma de ejecución.',
            ],
        ],
        [
            'texto'    => 'Ante la identificación de una condición insegura, una acción coherente sería:',
            'correcta' => 'a',
            'opciones' => [
                'a' => 'Ajustar la actividad según el entorno.',
                'b' => 'Continuar la tarea bajo las mismas condiciones.',
                'c' => 'Ignorar la situación si no hay incidentes previos.',
                'd' => 'Postergar la actividad sin intervención.',
            ],
        ],
        [
            'texto'    => 'Un trabajador detecta una herramienta con falla visible y decide utilizarla. En esta situación se presenta:',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'Una reducción del peligro en la tarea.',
                'b' => 'Una variación sin impacto en la actividad.',
                'c' => 'Una mejora en la ejecución del trabajo.',
                'd' => 'Un aumento en la exposición al daño.',
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
