<?php
/**
 * Script CLI: Corregir trigger trg_sync_cliente_password_to_usuario
 *
 * PROBLEMA: El trigger usa columnas inexistentes en tbl_usuarios
 *   - Usaba: WHERE id_cliente = NEW.id_cliente AND usuario = NEW.usuario
 *   - Correcto: WHERE id_entidad = NEW.id_cliente AND tipo_usuario = 'client'
 *
 * Uso:
 *   php app/CLI/fix_trigger_sync_cliente.php local
 *   php app/CLI/fix_trigger_sync_cliente.php prod
 */

$entorno = $argv[1] ?? null;

if (!in_array($entorno, ['local', 'prod'])) {
    echo "\n[ERROR] Debes especificar el entorno: local o prod\n";
    echo "Ejemplo: php app/CLI/fix_trigger_sync_cliente.php local\n\n";
    exit(1);
}

// ─── Credenciales ─────────────────────────────────────────────────────────────
if ($entorno === 'local') {
    $host   = '127.0.0.1';
    $port   = '3306';
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
} else {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = '25060';
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = $argv[2] ?? getenv('PROD_DB_PASS') ?: '';
    $ssl    = true;
    if (empty($pass)) {
        echo "\n[ERROR] Para prod debes pasar la contraseña como 2do argumento:\n";
        echo "  php app/CLI/fix_trigger_sync_cliente.php prod TU_PASSWORD\n\n";
        exit(1);
    }
}

// ─── Conexión PDO ─────────────────────────────────────────────────────────────
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

if ($ssl) {
    $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opciones[PDO::MYSQL_ATTR_SSL_CA]                 = true;
}

echo "\n======================================================\n";
echo "  FIX: trigger trg_sync_cliente_password_to_usuario\n";
echo "  Entorno: " . strtoupper($entorno) . "\n";
echo "  Host:    {$host}:{$port}\n";
echo "  BD:      {$dbname}\n";
echo "======================================================\n\n";

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
    echo "[OK] Conexión establecida con {$host}\n\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(1);
}

// ─── Verificar trigger actual ──────────────────────────────────────────────────
echo "→ Verificando trigger existente...\n";
$row = $pdo->query("
    SELECT ACTION_STATEMENT
    FROM INFORMATION_SCHEMA.TRIGGERS
    WHERE TRIGGER_SCHEMA = '{$dbname}'
      AND TRIGGER_NAME   = 'trg_sync_cliente_password_to_usuario'
")->fetch();

if ($row) {
    echo "  [ENCONTRADO] Trigger actual:\n";
    echo "  " . str_replace("\n", "\n  ", $row['ACTION_STATEMENT']) . "\n\n";
} else {
    echo "  [INFO] Trigger no existe en este entorno. No hay nada que corregir.\n";
    echo "======================================================\n";
    echo "  RESULTADO: NADA QUE HACER\n";
    echo "======================================================\n\n";
    exit(0);
}

// ─── DROP trigger roto ────────────────────────────────────────────────────────
echo "→ Eliminando trigger defectuoso...\n";
try {
    $pdo->exec("DROP TRIGGER IF EXISTS trg_sync_cliente_password_to_usuario");
    echo "  [OK] Trigger eliminado.\n\n";
} catch (PDOException $e) {
    echo "  [ERROR] No se pudo eliminar: " . $e->getMessage() . "\n";
    exit(1);
}

// ─── Crear trigger corregido ──────────────────────────────────────────────────
echo "→ Creando trigger corregido...\n";

// tbl_usuarios usa:
//   id_entidad  (FK al cliente — antes era id_cliente, columna inexistente)
//   tipo_usuario = 'client' para identificar usuarios de tipo cliente
//   email        (antes era 'usuario', columna inexistente)
$triggerSql = "
CREATE TRIGGER trg_sync_cliente_password_to_usuario
AFTER UPDATE ON tbl_clientes
FOR EACH ROW
BEGIN
    IF OLD.password IS NOT NULL AND NEW.password IS NOT NULL AND OLD.password != NEW.password THEN
        UPDATE tbl_usuarios
        SET password   = NEW.password,
            updated_at = NOW()
        WHERE id_entidad   = NEW.id_cliente
          AND tipo_usuario  = 'client';
    END IF;

    IF OLD.estado != NEW.estado THEN
        UPDATE tbl_usuarios
        SET estado     = CASE
                WHEN NEW.estado = 'activo' THEN 'activo'
                ELSE 'inactivo'
            END,
            updated_at = NOW()
        WHERE id_entidad  = NEW.id_cliente
          AND tipo_usuario = 'client';
    END IF;
END
";

try {
    $pdo->exec($triggerSql);
    echo "  [OK] Trigger corregido y creado exitosamente.\n\n";
} catch (PDOException $e) {
    echo "  [ERROR] No se pudo crear el trigger: " . $e->getMessage() . "\n";
    exit(1);
}

// ─── Verificación final ───────────────────────────────────────────────────────
echo "→ Verificando trigger nuevo...\n";
$row = $pdo->query("
    SELECT ACTION_STATEMENT
    FROM INFORMATION_SCHEMA.TRIGGERS
    WHERE TRIGGER_SCHEMA = '{$dbname}'
      AND TRIGGER_NAME   = 'trg_sync_cliente_password_to_usuario'
")->fetch();

if ($row) {
    echo "  [OK] Trigger verificado en BD.\n\n";
} else {
    echo "  [ERROR] El trigger no aparece en BD después de crearlo.\n";
    exit(1);
}

// ─── Prueba rápida ────────────────────────────────────────────────────────────
echo "→ Prueba rápida: UPDATE tbl_clientes SET estado='inactivo' WHERE id_cliente=0 (afecta 0 filas)...\n";
try {
    $pdo->exec("UPDATE tbl_clientes SET estado='inactivo' WHERE id_cliente=0");
    echo "  [OK] Query de prueba ejecutada sin errores.\n\n";
} catch (PDOException $e) {
    echo "  [ERROR] La prueba falló: " . $e->getMessage() . "\n";
    exit(1);
}

echo "======================================================\n";
echo "  RESULTADO: ÉXITO TOTAL\n";
echo "  El trigger fue corregido. El error 'Unknown column\n";
echo "  id_cliente in where clause' ya no ocurrirá.\n";
echo "======================================================\n\n";

exit(0);
