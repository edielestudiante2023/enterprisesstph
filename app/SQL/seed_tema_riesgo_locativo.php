<?php
/**
 * Seed: tema "Riesgo Locativo en Propiedad Horizontal" con 7 preguntas.
 * Idempotente.
 *
 * Uso:
 *   LOCAL      : php app/SQL/seed_tema_riesgo_locativo.php
 *   PRODUCCIÓN : DB_PROD_PASS=xxx php app/SQL/seed_tema_riesgo_locativo.php production
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

$NOMBRE_TEMA = 'Riesgo Locativo en Propiedad Horizontal';

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
        'Evaluación de conocimientos sobre identificación y control del riesgo locativo en propiedades horizontales: condiciones inseguras en áreas comunes, señalización, inspecciones preventivas y gestión desde el SG-SST.',
    ]);
    $idTema = (int) $pdo->lastInsertId();
    echo "OK: tema '{$NOMBRE_TEMA}' creado (id={$idTema})\n";

    $preguntas = [
        [
            'texto'    => 'En una zona común recién trapeada, el personal de aseo instala señalización pero la retira antes de que el piso esté completamente seco. ¿Qué falla principal se está presentando?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Falta de mantenimiento locativo.',
                'b' => 'Falla en la identificación del riesgo.',
                'c' => 'Control insuficiente del riesgo generado.',
                'd' => 'Ausencia de reporte a la administración.',
            ],
        ],
        [
            'texto'    => 'Durante la ronda, un vigilante detecta baldosas sueltas en una escalera de uso frecuente. ¿Cuál debería ser la gestión MÁS adecuada considerando el riesgo locativo?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Reportar en la minuta y continuar la ronda.',
                'b' => 'Restringir el acceso si es posible y reportar de inmediato.',
                'c' => 'Esperar a que mantenimiento programe revisión.',
                'd' => 'Informar verbalmente al relevo.',
            ],
        ],
        [
            'texto'    => '¿Cuál de las siguientes situaciones evidencia una gestión reactiva y no preventiva del riesgo locativo?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Señalizar zonas húmedas durante limpieza.',
                'b' => 'Realizar inspecciones periódicas de áreas comunes.',
                'c' => 'Corregir una condición insegura después de un accidente.',
                'd' => 'Reportar daños estructurales oportunamente.',
            ],
        ],
        [
            'texto'    => 'En un conjunto residencial se presentan constantes obstáculos en los pasillos (bicicletas, cajas, muebles). ¿Cuál es la causa raíz más probable del riesgo locativo?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Falta de señalización.',
                'b' => 'Comportamiento inseguro de usuarios y falta de control.',
                'c' => 'Deficiencia en iluminación.',
                'd' => 'Riesgo eléctrico.',
            ],
        ],
        [
            'texto'    => 'Un cuarto de residuos presenta humedad constante, iluminación deficiente y acumulación de elementos. ¿Qué combinación de riesgos se está generando principalmente?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Biológico y químico exclusivamente.',
                'b' => 'Locativo con potencial de accidentes múltiples.',
                'c' => 'Mecánico únicamente.',
                'd' => 'Administrativo.',
            ],
        ],
        [
            'texto'    => 'Desde el enfoque del SG-SST, el riesgo locativo debe gestionarse principalmente a través de:',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Acciones correctivas posteriores al incidente.',
                'b' => 'Control del comportamiento del trabajador únicamente.',
                'c' => 'Identificación, control y seguimiento de condiciones inseguras.',
                'd' => 'Sanciones disciplinarias.',
            ],
        ],
        [
            'texto'    => 'Un trabajador de aseo reporta constantemente pisos húmedos en zonas comunes, pero no hay accidentes registrados. ¿Qué análisis es más adecuado?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'No existe riesgo porque no han ocurrido accidentes.',
                'b' => 'El riesgo está controlado completamente.',
                'c' => 'Existe una condición insegura con potencial de materialización.',
                'd' => 'El riesgo es irrelevante.',
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
