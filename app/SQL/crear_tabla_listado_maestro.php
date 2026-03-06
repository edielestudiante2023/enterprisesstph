<?php
/**
 * Migración: Crear tabla tbl_listado_maestro_documentos + seed 28 documentos
 *
 * Uso LOCAL:      php crear_tabla_listado_maestro.php
 * Uso PRODUCCIÓN: DB_PROD_PASS=xxx php crear_tabla_listado_maestro.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) { die("ERROR: Variable DB_PROD_PASS no definida.\n"); }
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $label = 'PRODUCCIÓN (DigitalOcean)';
    $sslOpts = [
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $label = 'LOCAL';
    $sslOpts = [];
}

echo "=== {$label} ===\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, array_merge([
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ], $sslOpts));
    echo "Conectado a: {$host}:{$port}/{$db}\n\n";
} catch (PDOException $e) {
    die("=== {$label} ===\nERROR: " . $e->getMessage() . "\n");
}

// ── Crear tabla ──
echo "[1/1] Creando tbl_listado_maestro_documentos... ";
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tbl_listado_maestro_documentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_documento VARCHAR(50) NOT NULL COMMENT 'FORMATO, MANUAL, POLITICA, PROCEDIMIENTO, PROGRAMA, REGLAMENTO, MATRICES',
        codigo VARCHAR(30) NOT NULL,
        nombre_documento VARCHAR(255) NOT NULL,
        version VARCHAR(10) DEFAULT '001',
        ubicacion VARCHAR(255) DEFAULT 'Dashboard Enterprisesst',
        fecha DATE NULL,
        estado ENUM('Vigente','Obsoleto','En revision') DEFAULT 'Vigente',
        control_cambios TEXT NULL,
        orden INT DEFAULT 0,
        activo TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_codigo (codigo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "OK\n\n";

// ── Seed documentos ──
echo "Insertando documentos maestros... ";

$documentos = [
    ['FORMATO',        'FT-SST-001',  'FORMATO DE ASIGNACION DE RESPONSABLE SG-SST',                                                          'Dashboard Enterprisesst', 1],
    ['FORMATO',        'FT-SST-002',  'ASIGNACION DE RESPONSABILIDADES EN SG-SST',                                                             'Dashboard Enterprisesst', 2],
    ['FORMATO',        'FT-SST-003',  'ASIGNACION DE VIGIA',                                                                                    'Dashboard Enterprisesst', 3],
    ['FORMATO',        'FT-SST-004',  'EXONERACION DE COMITE DE CONVIVENCIA LABORAL',                                                          'Dashboard Enterprisesst', 4],
    ['FORMATO',        'FT-SST-005',  'REGISTRO DE ASISTENCIA',                                                                                 'Dashboard Enterprisesst', 5],
    ['FORMATO',        'FT-SST-012',  'FORMATO DE EVALUACION DE LA INDUCCION Y/O REINDUCCION',                                                 'Dashboard Enterprisesst', 6],
    ['FORMATO',        'FT-SST-013',  'OBJETIVOS DEL SISTEMA DE GESTION DEL SEGURIDAD Y SALUD EN EL TRABAJO',                                  'Dashboard Enterprisesst', 7],
    ['MANUAL',         'MAN-SST-002', 'MANUAL DE CONTRATATISTAS/PROVEEDORES',                                                                   'Dashboard Enterprisesst', 8],
    ['POLITICA',       'PL-SST-001',  'POLITICA DE SEGURIDAD Y SALUD EN EL TRABAJO',                                                           'Dashboard Enterprisesst', 9],
    ['POLITICA',       'PL-SST-002',  'POLITICA DE NO ALCOHOL DROGAS NI TABACO',                                                               'Dashboard Enterprisesst', 10],
    ['POLITICA',       'PL-SST-003',  'POLITICA DE PREVENCION, PREPARACION Y RESPUESTA ANTE EMERGENCIAS',                                      'Dashboard Enterprisesst', 11],
    ['POLITICA',       'PL-SST-004',  'POLITICA USO ELEMENTOS DE PROTECCION PERSONAL',                                                         'Dashboard Enterprisesst', 12],
    ['PROCEDIMIENTO',  'PRC-SST-002', 'PROCEDIMIENTO DE RENDICION DE CUENTAS EN SEGURIDAD Y SALUD EN EL TRABAJO',                              'Dashboard Enterprisesst', 13],
    ['PROCEDIMIENTO',  'PRC-SST-004', 'PLAN DE SANEAMIENTO BASICO',                                                                             'Dashboard Enterprisesst', 14],
    ['PROCEDIMIENTO',  'PRC-SST-006', 'PROCEDIMIENTO DE REPORTE DE ACCIDENTES E INCIDENTES DE TRABAJO',                                        'Dashboard Enterprisesst', 15],
    ['PROCEDIMIENTO',  'PRC-SST-008', 'PROCEDIMIENTO PARA LA IDENTIFICACION DE PELIGROS, VALORACION DE RIESGOS',                               'Dashboard Enterprisesst', 16],
    ['PROGRAMA',       'PRG-SST-001', 'PROGRAMA DE CAPACITACION Y ENTRENAMIENTO',                                                               'Dashboard Enterprisesst', 17],
    ['PROGRAMA',       'PRG-SST-002', 'PROCESO DE INDUCCION Y REINDUCCION DE PERSONAL',                                                        'Dashboard Enterprisesst', 18],
    ['REGLAMENTO',     'REG-SST-001', 'REGLAMENTO DE HIGIENE Y SEGURIDAD INDUSTRIAL',                                                          'Dashboard Enterprisesst', 19],
    ['FORMATO',        'FT-SST-020',  'FORMATO LISTADO MAESTRO DE DOCUMENTOS Y REGISTROS',                                                     'Matrices Interactivas',   20],
    ['FORMATO',        'FT-SST-021',  'ACTA ASIGNACION DE FUNCIONES Y RESPONSABILIDADES',                                                      'Matrices Interactivas',   21],
    ['FORMATO',        'FT-SST-022',  'ASIGNACION DE RECURSOS PARA EL SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO',                  'Matrices Interactivas',   22],
    ['FORMATO',        'FT-SST-023',  'MATRIZ DE GESTION DE CONTRATISTAS Y PROVEEDORES',                                                       'Matrices Interactivas',   23],
    ['FORMATO',        'FT-SST-024',  'FORMATO DE CRONOGRAMA DE MANTENIMIENTO INSTALACIONES MAQUINAS Y EQUIPOS',                               'Matrices Interactivas',   24],
    ['FORMATO',        'FT-SST-025',  'INVESTIGACION DE ACCIDENTES',                                                                            'Matrices Interactivas',   25],
    ['FORMATO',        'FT-SST-026',  'HOJA DE VIDA BRIGADISTAS',                                                                               'Matrices Interactivas',   26],
    ['MATRICES',       'MT-SST-001',  'MATRIZ DE IDENTIFICACION DE PELIGROS, EVALUACION Y VALORACION DE RIESGOS',                               'Matrices Interactivas',   27],
    ['MATRICES',       'MT-SST-002',  'MATRIZ DE ELEMENTOS DE PROTECCION PERSONAL',                                                             'Matrices Interactivas',   28],
];

$stmt = $pdo->prepare("
    INSERT IGNORE INTO tbl_listado_maestro_documentos
    (tipo_documento, codigo, nombre_documento, version, ubicacion, fecha, estado, orden)
    VALUES (?, ?, ?, '001', ?, '2025-03-01', 'Vigente', ?)
");

foreach ($documentos as $doc) {
    $stmt->execute($doc);
}
echo "OK\n\n";

// ── Verificación ──
$count = $pdo->query("SELECT COUNT(*) FROM tbl_listado_maestro_documentos")->fetchColumn();
echo "Documentos en BD: {$count}\n\n";

$exists = $pdo->query("SHOW TABLES LIKE 'tbl_listado_maestro_documentos'")->rowCount();
echo "Verificación: tbl_listado_maestro_documentos: " . ($exists ? 'EXISTS' : 'MISSING') . "\n\n";

echo "=== MIGRACIÓN COMPLETADA EXITOSAMENTE ===\n";
