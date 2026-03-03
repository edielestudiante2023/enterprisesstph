<?php
$conn = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060
);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

// Datos del cliente 73 (ya verificados)
$rep_legal     = $conn->real_escape_string('EFREN ALEXANDER ROJAS RODRIGUEZ');
$cedula        = $conn->real_escape_string('7165619');
$direccion     = $conn->real_escape_string('Calle 165 # 52-54');
$telefono      = $conn->real_escape_string('313 2107147');
$email         = $conn->real_escape_string('albaceteph@gmail.com');
$now           = date('Y-m-d H:i:s');

$sql = "INSERT INTO tbl_contratos (
    id_cliente, numero_contrato, fecha_inicio, fecha_fin,
    tipo_contrato, estado, observaciones,
    nombre_rep_legal_cliente, cedula_rep_legal_cliente,
    direccion_cliente, telefono_cliente, email_cliente,
    nombre_rep_legal_contratista, cedula_rep_legal_contratista, email_contratista,
    nombre_responsable_sgsst, cedula_responsable_sgsst, licencia_responsable_sgsst, email_responsable_sgsst,
    id_consultor_responsable, frecuencia_visitas,
    numero_cuotas, cuenta_bancaria, banco, tipo_cuenta,
    estado_firma, created_at, updated_at
) VALUES (
    73, 'CONT-2026-0073', '2026-03-01', '2026-12-30',
    'inicial', 'activo', 'Contrato creado manualmente',
    '$rep_legal', '$cedula', '$direccion', '$telefono', '$email',
    'DIANA PATRICIA CUESTAS NAVIA', '52.425.982', 'Diana.cuestas@cycloidtalent.com',
    'Edison Ernesto Cuervo Salazar', '80.039.147', '4241', 'Edison.cuervo@cycloidtalent.com',
    17, 'TRIMESTRAL',
    10, '108900260762', 'Davivienda', 'Ahorros',
    'sin_enviar', '$now', '$now'
)";

if ($conn->query($sql)) {
    $newId = $conn->insert_id;
    echo "OK: Contrato creado" . PHP_EOL;
    echo "  id_contrato    : $newId" . PHP_EOL;
    echo "  numero_contrato: CONT-2026-0073" . PHP_EOL;
    echo "  id_cliente     : 73 (CONJUNTO RESIDENCIAL ALBACETE)" . PHP_EOL;
    echo "  fecha_inicio   : 2026-03-01" . PHP_EOL;
    echo "  fecha_fin      : 2026-12-30" . PHP_EOL;
    echo "  estado         : activo" . PHP_EOL;
} else {
    echo "ERROR: " . $conn->error . PHP_EOL;
}

$conn->close();
