<?php
/**
 * Migración: Crear tablas para módulo Presupuesto SST
 *
 * Uso:
 *   LOCAL:       php crear_tablas_presupuesto_sst.php
 *   PRODUCCIÓN:  DB_PROD_PASS=xxx php crear_tablas_presupuesto_sst.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php crear_tablas_presupuesto_sst.php production\n";
        exit(1);
    }
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'dbname'   => 'propiedad_horizontal',
        'user'     => 'cycloid_userdb',
        'password' => $password,
        'ssl'      => true,
    ];
    echo "=== PRODUCCIÓN (DigitalOcean) ===\n";
} else {
    $config = [
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'propiedad_horizontal',
        'user'     => 'root',
        'password' => '',
        'ssl'      => false,
    ];
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    if ($config['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
    echo "Conectado a: {$config['host']}:{$config['port']}/{$config['dbname']}\n\n";

    // =====================================================
    // 1. Tabla de categorías maestras
    // =====================================================
    echo "[1/4] Creando tbl_presupuesto_categorias... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_categorias (
            id_categoria INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(10) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            orden INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_codigo (codigo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";

    // =====================================================
    // 2. Tabla principal de presupuesto por cliente/año
    // =====================================================
    echo "[2/4] Creando tbl_presupuesto_sst... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_sst (
            id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT NOT NULL,
            anio INT NOT NULL,
            mes_inicio INT DEFAULT 1 COMMENT '1=Enero, 2=Febrero, etc.',
            estado ENUM('borrador', 'aprobado', 'cerrado') DEFAULT 'borrador',
            observaciones TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_cliente_anio (id_cliente, anio),
            INDEX idx_cliente (id_cliente),
            INDEX idx_anio (anio)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";

    // =====================================================
    // 3. Tabla de ítems del presupuesto
    // =====================================================
    echo "[3/4] Creando tbl_presupuesto_items... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_items (
            id_item INT AUTO_INCREMENT PRIMARY KEY,
            id_presupuesto INT NOT NULL,
            id_categoria INT NOT NULL,
            codigo_item VARCHAR(10) NOT NULL COMMENT 'Ej: 1.1, 3.2, 4.1',
            actividad VARCHAR(200) NOT NULL,
            descripcion TEXT NULL,
            orden INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_presupuesto) REFERENCES tbl_presupuesto_sst(id_presupuesto) ON DELETE CASCADE,
            FOREIGN KEY (id_categoria) REFERENCES tbl_presupuesto_categorias(id_categoria),
            INDEX idx_presupuesto (id_presupuesto),
            INDEX idx_categoria (id_categoria)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";

    // =====================================================
    // 4. Tabla de detalle mensual
    // =====================================================
    echo "[4/4] Creando tbl_presupuesto_detalle... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_detalle (
            id_detalle INT AUTO_INCREMENT PRIMARY KEY,
            id_item INT NOT NULL,
            mes INT NOT NULL COMMENT '1-12 para meses del año',
            anio INT NOT NULL,
            presupuestado DECIMAL(15,2) DEFAULT 0.00,
            ejecutado DECIMAL(15,2) DEFAULT 0.00,
            notas VARCHAR(255) NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_item) REFERENCES tbl_presupuesto_items(id_item) ON DELETE CASCADE,
            UNIQUE KEY uk_item_mes_anio (id_item, mes, anio),
            INDEX idx_item (id_item),
            INDEX idx_mes_anio (mes, anio)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";

    // =====================================================
    // 5. Datos iniciales - 7 categorías (Decreto 1072/2015)
    // =====================================================
    echo "\nInsertando categorías maestras... ";
    $pdo->exec("
        INSERT INTO tbl_presupuesto_categorias (codigo, nombre, orden) VALUES
        ('1', 'Talento Humano SST', 1),
        ('2', 'Capacitación y Formación', 2),
        ('3', 'Medicina Preventiva y del Trabajo', 3),
        ('4', 'Promoción y Prevención', 4),
        ('5', 'Seguridad Industrial e Higiene', 5),
        ('6', 'Gestión de Emergencias', 6),
        ('7', 'Otros Gastos SST', 7)
        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), orden = VALUES(orden)
    ");
    echo "OK\n";

    // Verificar
    $count = $pdo->query("SELECT COUNT(*) FROM tbl_presupuesto_categorias")->fetchColumn();
    echo "\nCategorías en BD: {$count}\n";

    $tables = ['tbl_presupuesto_categorias', 'tbl_presupuesto_sst', 'tbl_presupuesto_items', 'tbl_presupuesto_detalle'];
    echo "\nVerificación de tablas:\n";
    foreach ($tables as $t) {
        $exists = $pdo->query("SHOW TABLES LIKE '{$t}'")->rowCount();
        echo "  {$t}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }

    echo "\n=== MIGRACIÓN COMPLETADA EXITOSAMENTE ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
