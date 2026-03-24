<?php
/**
 * Migración: sistema dinámico de temas, preguntas y opciones para Evaluaciones.
 * Crea:
 *   tbl_evaluacion_tema    — catálogo de temas (Inducción SST, Riesgo Locativo, etc.)
 *   tbl_evaluacion_pregunta — preguntas por tema con respuesta correcta
 *   tbl_evaluacion_opcion  — opciones a/b/c/d por pregunta
 * Agrega columna id_tema a tbl_evaluacion_induccion.
 * Siembra el tema "Inducción SST PH" con las 10 preguntas existentes.
 *
 * Uso: DB_PROD_PASS=xxx php app/SQL/migrate_evaluacion_preguntas.php [production]
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
} else {
    $host = '127.0.0.1'; $port = 3306;
    $db   = 'propiedad_horizontal'; $user = 'root'; $pass = ''; $ssl = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }

$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n";

// ── 1. tbl_evaluacion_tema ──────────────────────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tbl_evaluacion_tema (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre      VARCHAR(200) NOT NULL,
        descripcion TEXT NULL,
        estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
        created_at  DATETIME NULL,
        updated_at  DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK: tbl_evaluacion_tema\n";

// ── 2. tbl_evaluacion_pregunta ──────────────────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tbl_evaluacion_pregunta (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_tema     INT UNSIGNED NOT NULL,
        orden       TINYINT UNSIGNED NOT NULL DEFAULT 0,
        texto       TEXT NOT NULL,
        correcta    CHAR(1) NOT NULL COMMENT 'Letra de la opcion correcta: a,b,c,d',
        created_at  DATETIME NULL,
        updated_at  DATETIME NULL,
        KEY idx_tema_orden (id_tema, orden)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK: tbl_evaluacion_pregunta\n";

// ── 3. tbl_evaluacion_opcion ────────────────────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tbl_evaluacion_opcion (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_pregunta INT UNSIGNED NOT NULL,
        letra       CHAR(1) NOT NULL COMMENT 'a, b, c, d',
        texto       TEXT NOT NULL,
        KEY idx_pregunta (id_pregunta)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK: tbl_evaluacion_opcion\n";

// ── 4. Agregar id_tema a tbl_evaluacion_induccion ───────────────────────────
$cols = $pdo->query("SHOW COLUMNS FROM tbl_evaluacion_induccion LIKE 'id_tema'")->fetchAll();
if (empty($cols)) {
    $pdo->exec("ALTER TABLE tbl_evaluacion_induccion ADD COLUMN id_tema INT UNSIGNED NULL DEFAULT NULL AFTER id_cliente");
    echo "OK: columna id_tema agregada a tbl_evaluacion_induccion\n";
} else {
    echo "INFO: columna id_tema ya existe\n";
}

// ── 5. Sembrar tema "Inducción SST PH" si no existe ────────────────────────
$temaExiste = $pdo->query("SELECT id FROM tbl_evaluacion_tema WHERE nombre = 'Inducción SST PH' LIMIT 1")->fetch();

if ($temaExiste) {
    $idTema = $temaExiste['id'];
    echo "INFO: tema 'Inducción SST PH' ya existe (id={$idTema}), no se re-inserta\n";
} else {
    $pdo->exec("
        INSERT INTO tbl_evaluacion_tema (nombre, descripcion, estado, created_at, updated_at)
        VALUES ('Inducción SST PH',
                'Evaluación de conocimientos en Seguridad y Salud en el Trabajo para proveedores y contratistas de propiedades horizontales.',
                'activo', NOW(), NOW())
    ");
    $idTema = $pdo->lastInsertId();
    echo "OK: tema 'Inducción SST PH' creado (id={$idTema})\n";

    // ── 6. Sembrar las 10 preguntas históricas ──────────────────────────────
    $preguntas = [
        [
            'texto'   => '¿Cuál es el principal objetivo del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST)?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Maximizar los beneficios económicos.',
                'b' => 'Prevenir enfermedades en los residentes.',
                'c' => 'Minimizar los riesgos legales y de la propiedad horizontal en caso de un eventual accidente.',
                'd' => 'Fomentar el consumo de alcohol y tabaco en el trabajo.',
            ],
        ],
        [
            'texto'   => '¿Quiénes deben implementar el SG-SST en una propiedad horizontal?',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'Solo los residentes.',
                'b' => 'Solo los empleados.',
                'c' => 'Solo los contratistas.',
                'd' => 'Los contratantes de personal bajo modalidad de contrato civil, comercial o administrativo.',
            ],
        ],
        [
            'texto'   => '¿Qué es un "peligro" en el contexto de seguridad y salud en el trabajo?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Un evento inesperado.',
                'b' => 'Un acto inseguro.',
                'c' => 'Una fuente, situación o acto con potencial de daño.',
                'd' => 'Un accidente laboral.',
            ],
        ],
        [
            'texto'   => '¿Cuál es la diferencia entre un "peligro" y un "riesgo"?',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'No hay diferencia.',
                'b' => 'El riesgo es un acto inseguro.',
                'c' => 'El peligro es un evento inesperado.',
                'd' => 'El riesgo es la combinación de la probabilidad de que ocurra un peligro y la severidad de la lesión que puede causar.',
            ],
        ],
        [
            'texto'   => '¿Qué función desempeña la "Brigada de Emergencia" en la propiedad horizontal?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Mantener orden y limpieza en las áreas comunes.',
                'b' => 'Promover la cultura de la prevención y reaccionar en caso de emergencias como sismos o incendios.',
                'c' => 'Organizar fiestas y eventos.',
                'd' => 'Gestionar la seguridad en las zonas comunes.',
            ],
        ],
        [
            'texto'   => '¿Cuál es el propósito de un "FURAT" en el contexto de seguridad y salud en el trabajo?',
            'correcta' => 'b',
            'opciones' => [
                'a' => 'Registrar la asistencia de los residentes a cursos de seguridad.',
                'b' => 'Informar a la ARL sobre la ocurrencia de un accidente de trabajo.',
                'c' => 'Realizar pruebas de alcoholemia a los trabajadores.',
                'd' => 'Organizar simulacros de evacuación.',
            ],
        ],
        [
            'texto'   => '¿Qué debe exigir la copropiedad en cuanto a las dotaciones de proveedores y contratistas?',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'Equipos de oficina.',
                'b' => 'Programas de entretenimiento para residentes.',
                'c' => 'Programas de capacitación para empleados.',
                'd' => 'Equipos de protección personal (EPP) adecuados.',
            ],
        ],
        [
            'texto'   => '¿Cuál es la política sobre el consumo de alcohol, tabaco y drogas?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Prohibir el consumo solo para los residentes.',
                'b' => 'Permitir el consumo en áreas designadas.',
                'c' => 'Prohibir el consumo durante la prestación del servicio para proveedores y contratistas.',
                'd' => 'Promover el consumo de drogas en eventos sociales.',
            ],
        ],
        [
            'texto'   => '¿Cuál es el objetivo de la política de prevención, preparación y respuesta ante emergencias?',
            'correcta' => 'c',
            'opciones' => [
                'a' => 'Fomentar el uso de dispositivos móviles.',
                'b' => 'Proporcionar entretenimiento a los residentes.',
                'c' => 'Salvaguardar la salud y la seguridad de las personas en la propiedad.',
                'd' => 'Controlar el consumo de alimentos en la copropiedad.',
            ],
        ],
        [
            'texto'   => '¿Qué tipo de emergencia se menciona como ejemplo en la información proporcionada?',
            'correcta' => 'd',
            'opciones' => [
                'a' => 'Emergencia tecnológica.',
                'b' => 'Emergencia natural.',
                'c' => 'Emergencia social.',
                'd' => 'Todas las mencionadas.',
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
        $idPregunta = $pdo->lastInsertId();
        foreach ($p['opciones'] as $letra => $texto) {
            $stmtO->execute([$idPregunta, $letra, $texto]);
        }
        echo "  OK pregunta " . ($i + 1) . ": " . mb_substr($p['texto'], 0, 50) . "...\n";
    }
}

// ── 7. Actualizar evaluaciones existentes para que apunten al tema ──────────
$pdo->exec("
    UPDATE tbl_evaluacion_induccion
    SET id_tema = {$idTema}
    WHERE id_tema IS NULL
");
$actualizadas = $pdo->query("SELECT ROW_COUNT()")->fetchColumn();
echo "OK: {$actualizadas} evaluaciones actualizadas con id_tema={$idTema}\n";

echo "\nMigración completada.\n";
