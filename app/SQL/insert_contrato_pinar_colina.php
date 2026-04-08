<?php
/**
 * Script CLI para insertar cliente + contrato de Pinar de la Colina 1
 * Replica el flujo normal del sistema (store → saveAndGeneratePDF) sin enviar emails.
 *
 * Uso: php app/SQL/insert_contrato_pinar_colina.php [production]
 */

// ── Detectar entorno ──────────────────────────────────────────────
$env = ($argv[1] ?? '') === 'production' ? 'production' : 'local';

if ($env === 'production') {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        fwrite(STDERR, "ERROR: Variable DB_PROD_PASS no definida.\n");
        fwrite(STDERR, "Uso: DB_PROD_PASS=xxx php app/SQL/insert_contrato_pinar_colina.php production\n");
        exit(1);
    }
    $dsn = [
        'host'   => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'   => 25060,
        'dbname' => 'propiedad_horizontal',
        'user'   => 'cycloid_userdb',
        'pass'   => $password,
        'ssl'    => true,
    ];
} else {
    $dsn = [
        'host'   => '127.0.0.1',
        'port'   => 3306,
        'dbname' => 'propiedad_horizontal',
        'user'   => 'root',
        'pass'   => '',
        'ssl'    => false,
    ];
}

// ── Conexión PDO ──────────────────────────────────────────────────
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
if ($dsn['ssl']) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO(
        "mysql:host={$dsn['host']};port={$dsn['port']};dbname={$dsn['dbname']};charset=utf8mb4",
        $dsn['user'],
        $dsn['pass'],
        $options
    );
    echo "[OK] Conectado a {$env} ({$dsn['host']}:{$dsn['port']})\n";
} catch (PDOException $e) {
    fwrite(STDERR, "ERROR conexión: " . $e->getMessage() . "\n");
    exit(1);
}

// ── Datos del contrato (del documento firmado) ────────────────────
$NIT           = '900072254';
$NOMBRE        = 'CONJUNTO RESIDENCIAL PINAR DE LA COLINA 1';
$DIRECCION     = 'CRA 55 N. 149-20';
$CIUDAD        = 'Bogotá';
$EMAIL         = 'pinarcolina1@gmail.com';
$REP_LEGAL     = 'GLADYS OVALLE CHAIN';
$CEDULA_REP    = '63.335.719';

$FECHA_INICIO  = '2026-03-01';
$FECHA_FIN     = '2027-03-01';
$VALOR_TOTAL   = 1552500;
$VALOR_CUOTA   = 388125;
$NUM_CUOTAS    = 4;
$FRECUENCIA    = 'TRIMESTRAL';

// Consultor asignado: Edison Ernesto Cuervo Salazar
$CONSULTOR_NOMBRE  = 'EDISON ERNESTO CUERVO SALAZAR';
$CONSULTOR_CEDULA  = '80039147';
$CONSULTOR_LIC     = '4241';
$CONSULTOR_EMAIL   = 'Edison.cuervo@cycloidtalent.com';

// Contratista (Cycloid)
$CONTRATISTA_NOMBRE = 'DIANA PATRICIA CUESTAS NAVIA';
$CONTRATISTA_CEDULA = '52.425.982';
$CONTRATISTA_EMAIL  = 'diana.cuestas@cycloidtalent.com';

// Cláusula cuarta
$CLAUSULA_CUARTA = 'La duración de este contrato es de 12 meses contados a partir de la fecha de la firma y con finalización 01 de marzo de 2027. No obstante, el contrato podrá ser terminado de forma anticipada por parte de EL CONTRATANTE, en cualquier momento previa comunicación escrita o verbal.';

// ── PASO 1: Buscar consultor Edison ───────────────────────────────
$stmt = $pdo->prepare("SELECT id_consultor FROM tbl_consultor WHERE cedula_consultor = ? LIMIT 1");
$stmt->execute([$CONSULTOR_CEDULA]);
$consultor = $stmt->fetch();

if (!$consultor) {
    fwrite(STDERR, "ERROR: No se encontró consultor con cédula {$CONSULTOR_CEDULA}\n");
    exit(1);
}
$idConsultor = $consultor['id_consultor'];
echo "[OK] Consultor encontrado: id_consultor={$idConsultor}\n";

// ── PASO 2: Verificar si el cliente ya existe (por NIT) ──────────
$stmt = $pdo->prepare("SELECT id_cliente, nombre_cliente, estado FROM tbl_clientes WHERE nit_cliente = ? LIMIT 1");
$stmt->execute([$NIT]);
$cliente = $stmt->fetch();

if ($cliente) {
    $idCliente = $cliente['id_cliente'];
    echo "[INFO] Cliente ya existe: id_cliente={$idCliente} - {$cliente['nombre_cliente']} (estado: {$cliente['estado']})\n";
} else {
    // Insertar cliente nuevo
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO tbl_clientes (
            datetime, fecha_ingreso, nit_cliente, nombre_cliente,
            correo_cliente, direccion_cliente, ciudad_cliente,
            nombre_rep_legal, cedula_rep_legal,
            id_consultor, estado, estandares
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', 'Trimestral')
    ");
    $stmt->execute([
        $now, $now, $NIT, $NOMBRE,
        $EMAIL, $DIRECCION, $CIUDAD,
        $REP_LEGAL, $CEDULA_REP,
        $idConsultor
    ]);
    $idCliente = $pdo->lastInsertId();
    echo "[OK] Cliente creado: id_cliente={$idCliente}\n";
}

// ── PASO 3: Verificar si ya tiene contrato activo con estas fechas ─
$stmt = $pdo->prepare("
    SELECT id_contrato, numero_contrato FROM tbl_contratos
    WHERE id_cliente = ? AND fecha_inicio = ? AND fecha_fin = ? AND estado = 'activo'
    LIMIT 1
");
$stmt->execute([$idCliente, $FECHA_INICIO, $FECHA_FIN]);
$contratoExistente = $stmt->fetch();

if ($contratoExistente) {
    echo "[SKIP] Ya existe contrato activo: {$contratoExistente['numero_contrato']} (id={$contratoExistente['id_contrato']})\n";
    echo "No se realizaron cambios.\n";
    exit(0);
}

// ── PASO 4: Desactivar contratos activos previos del cliente ──────
$stmt = $pdo->prepare("UPDATE tbl_contratos SET estado = 'renovado' WHERE id_cliente = ? AND estado = 'activo'");
$stmt->execute([$idCliente]);
$desactivados = $stmt->rowCount();
if ($desactivados > 0) {
    echo "[INFO] Desactivados {$desactivados} contrato(s) previo(s)\n";
}

// ── PASO 5: Generar número de contrato ────────────────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM tbl_contratos WHERE id_cliente = ?");
$stmt->execute([$idCliente]);
$count = $stmt->fetch()['cnt'];
$numeroContrato = 'CONT-' . str_pad($idCliente, 6, '0', STR_PAD_LEFT) . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

echo "[INFO] Número de contrato: {$numeroContrato}\n";

// ── PASO 6: Insertar contrato ─────────────────────────────────────
$now = date('Y-m-d H:i:s');
$tipoContrato = ($count > 0) ? 'renovacion' : 'inicial';

$stmt = $pdo->prepare("
    INSERT INTO tbl_contratos (
        id_cliente, numero_contrato, fecha_inicio, fecha_fin,
        valor_contrato, valor_mensual, numero_cuotas, frecuencia_visitas,
        tipo_contrato, estado,
        nombre_rep_legal_cliente, cedula_rep_legal_cliente,
        direccion_cliente, email_cliente,
        nombre_rep_legal_contratista, cedula_rep_legal_contratista, email_contratista,
        id_consultor_responsable,
        nombre_responsable_sgsst, cedula_responsable_sgsst,
        licencia_responsable_sgsst, email_responsable_sgsst,
        clausula_cuarta_duracion,
        contrato_generado, fecha_generacion_contrato,
        contrato_enviado, fecha_envio_contrato, email_envio_contrato,
        estado_firma,
        created_at, updated_at
    ) VALUES (
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, 'activo',
        ?, ?,
        ?, ?,
        ?, ?, ?,
        ?,
        ?, ?,
        ?, ?,
        ?,
        1, ?,
        1, ?, 'diana.cuestas@cycloidtalent.com, edison.cuervo@cycloidtalent.com',
        'sin_enviar',
        ?, ?
    )
");

$stmt->execute([
    $idCliente, $numeroContrato, $FECHA_INICIO, $FECHA_FIN,
    $VALOR_TOTAL, $VALOR_CUOTA, $NUM_CUOTAS, $FRECUENCIA,
    $tipoContrato,
    $REP_LEGAL, $CEDULA_REP,
    $DIRECCION, $EMAIL,
    $CONTRATISTA_NOMBRE, $CONTRATISTA_CEDULA, $CONTRATISTA_EMAIL,
    $idConsultor,
    $CONSULTOR_NOMBRE, $CONSULTOR_CEDULA,
    $CONSULTOR_LIC, $CONSULTOR_EMAIL,
    $CLAUSULA_CUARTA,
    $now,
    $now,
    $now, $now
]);

$idContrato = $pdo->lastInsertId();
echo "[OK] Contrato creado: id_contrato={$idContrato}, numero={$numeroContrato}\n";

// ── PASO 7: Actualizar fecha_fin_contrato en tbl_clientes ─────────
$stmt = $pdo->prepare("UPDATE tbl_clientes SET fecha_fin_contrato = ? WHERE id_cliente = ?");
$stmt->execute([$FECHA_FIN, $idCliente]);
echo "[OK] tbl_clientes.fecha_fin_contrato actualizado a {$FECHA_FIN}\n";

// ── PASO 8: Sincronizar estandares ────────────────────────────────
$mapEstandares = [
    'MENSUAL'    => 'Mensual',
    'BIMENSUAL'  => 'Bimensual',
    'TRIMESTRAL' => 'Trimestral',
    'PROYECTO'   => 'Proyecto',
];
$estandar = $mapEstandares[$FRECUENCIA] ?? null;
if ($estandar) {
    $stmt = $pdo->prepare("UPDATE tbl_clientes SET estandares = ? WHERE id_cliente = ?");
    $stmt->execute([$estandar, $idCliente]);
    echo "[OK] tbl_clientes.estandares = '{$estandar}'\n";
}

// ── PASO 9: Activar cliente si estaba inactivo ────────────────────
$stmt = $pdo->prepare("UPDATE tbl_clientes SET estado = 'activo' WHERE id_cliente = ? AND estado != 'activo'");
$stmt->execute([$idCliente]);
if ($stmt->rowCount() > 0) {
    echo "[OK] Cliente activado\n";
}

// ── Resumen ───────────────────────────────────────────────────────
echo "\n========================================\n";
echo "RESUMEN:\n";
echo "  Cliente:  {$NOMBRE} (id={$idCliente})\n";
echo "  NIT:      {$NIT}\n";
echo "  Contrato: {$numeroContrato} (id={$idContrato})\n";
echo "  Tipo:     {$tipoContrato}\n";
echo "  Período:  {$FECHA_INICIO} → {$FECHA_FIN}\n";
echo "  Valor:    $" . number_format($VALOR_TOTAL, 0, ',', '.') . " ({$NUM_CUOTAS} cuotas de $" . number_format($VALOR_CUOTA, 0, ',', '.') . ")\n";
echo "  Visitas:  {$FRECUENCIA}\n";
echo "  Consultor: {$CONSULTOR_NOMBRE} (id={$idConsultor})\n";
echo "  Estado:   contrato_generado=1, contrato_enviado=1\n";
echo "  Email:    NO enviado (solo BD)\n";
echo "========================================\n";
