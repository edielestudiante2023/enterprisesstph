<?php
/**
 * Llave de seguridad tipográfica para evaluaciones de capacitación.
 *
 * Regla: en cada pregunta la opción CORRECTA termina con "." y las opciones
 * INCORRECTAS no terminan con ".". Sirve como ayuda silenciosa para el
 * consultor durante la aplicación de la evaluación.
 *
 * Idempotente: puede ejecutarse N veces sin efectos secundarios.
 * No altera la columna `correcta` ni la lógica de calificación.
 *
 * Uso:
 *   php app/SQL/apply_llave_punto_evaluacion.php
 *   DB_PROD_PASS=xxx php app/SQL/apply_llave_punto_evaluacion.php production
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
        fwrite(STDERR, "ERROR: DB_PROD_PASS no definido en el entorno.\n");
        exit(1);
    }
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n\n";

// ── 1. Cargar opciones con la letra correcta de su pregunta y el tema ───────
$rows = $pdo->query("
    SELECT o.id            AS id_opcion,
           o.id_pregunta,
           o.letra,
           o.texto,
           p.correcta,
           p.id_tema,
           p.orden          AS orden_pregunta,
           t.nombre         AS nombre_tema
    FROM tbl_evaluacion_opcion o
    INNER JOIN tbl_evaluacion_pregunta p ON p.id = o.id_pregunta
    LEFT  JOIN tbl_evaluacion_tema     t ON t.id = p.id_tema
    ORDER BY p.id_tema, p.orden, o.letra
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "No hay opciones de evaluación cargadas. Nada que hacer.\n";
    exit(0);
}

echo "Total opciones encontradas: " . count($rows) . "\n";

// ── 2. Detectar preguntas SIN opción para la letra 'correcta' ───────────────
$preguntasMap = [];
foreach ($rows as $r) {
    $preguntasMap[$r['id_pregunta']][$r['letra']] = $r;
}
$preguntasCorruptas = [];
foreach ($preguntasMap as $idP => $opcs) {
    $correcta = reset($opcs)['correcta'];
    if (!isset($opcs[$correcta])) {
        $preguntasCorruptas[] = "id={$idP} (correcta='{$correcta}' inexistente)";
    }
}
echo "Preguntas únicas: " . count($preguntasMap) . "\n\n";

// ── 3. Aplicar la regla ─────────────────────────────────────────────────────
$stmt = $pdo->prepare("UPDATE tbl_evaluacion_opcion SET texto = ? WHERE id = ?");

$actualizadas        = 0;
$sinCambio           = 0;
$correctasArregladas = 0;
$incorrectasLimpiadas = 0;
$cambiosPorTema      = [];

foreach ($rows as $r) {
    $esCorrecta = ($r['letra'] === $r['correcta']);
    $orig       = $r['texto'];

    // rtrim removes any combination of trailing spaces and dots
    $base   = rtrim($orig, " \t\n\r\0\x0B.");
    $nuevo  = $esCorrecta ? $base . '.' : $base;

    if ($nuevo === $orig) {
        $sinCambio++;
        continue;
    }

    $stmt->execute([$nuevo, $r['id_opcion']]);
    $actualizadas++;
    if ($esCorrecta) {
        $correctasArregladas++;
    } else {
        $incorrectasLimpiadas++;
    }

    $tema = $r['nombre_tema'] ?: ('tema#' . $r['id_tema']);
    $cambiosPorTema[$tema] = ($cambiosPorTema[$tema] ?? 0) + 1;
}

// ── 4. Reporte ──────────────────────────────────────────────────────────────
echo "────────────────────────────────────────\n";
echo "Resumen de cambios:\n";
echo "  Total actualizadas         : {$actualizadas}\n";
echo "    · Correctas con punto    : {$correctasArregladas}\n";
echo "    · Incorrectas sin punto  : {$incorrectasLimpiadas}\n";
echo "  Sin cambio                 : {$sinCambio}\n";

if (!empty($cambiosPorTema)) {
    echo "\nCambios por tema:\n";
    foreach ($cambiosPorTema as $tema => $cnt) {
        echo "  - {$tema}: {$cnt}\n";
    }
}

if (!empty($preguntasCorruptas)) {
    echo "\n⚠ Preguntas con datos corruptos (SIN opción que coincida con 'correcta'):\n";
    foreach ($preguntasCorruptas as $msg) {
        echo "  · {$msg}\n";
    }
}

// ── 5. Verificación final ──────────────────────────────────────────────────
$verif = $pdo->query("
    SELECT p.id                AS id_pregunta,
           p.id_tema,
           p.orden,
           p.correcta,
           COUNT(*)             AS total_opc,
           SUM(CASE WHEN o.texto LIKE '%.' THEN 1 ELSE 0 END) AS con_punto,
           SUM(CASE WHEN o.letra = p.correcta AND o.texto LIKE '%.' THEN 1 ELSE 0 END) AS correcta_con_punto
    FROM tbl_evaluacion_pregunta p
    INNER JOIN tbl_evaluacion_opcion o ON o.id_pregunta = p.id
    GROUP BY p.id, p.id_tema, p.orden, p.correcta
    HAVING con_punto <> 1 OR correcta_con_punto <> 1
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nVerificación final:\n";
if (empty($verif)) {
    echo "  ✓ Cada pregunta tiene exactamente 1 opción terminada en '.' y es la marcada como correcta.\n";
} else {
    echo "  ⚠ Inconsistencias detectadas:\n";
    foreach ($verif as $v) {
        echo "    preg={$v['id_pregunta']} tema={$v['id_tema']} orden={$v['orden']} correcta='{$v['correcta']}'"
           . " total_opc={$v['total_opc']} con_punto={$v['con_punto']} correcta_con_punto={$v['correcta_con_punto']}\n";
    }
}

echo "\nFin.\n";
