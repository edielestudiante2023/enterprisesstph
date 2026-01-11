<?php
/**
 * Script de Prueba para Generación de Contratos
 *
 * Este script verifica que todos los componentes necesarios estén disponibles
 * para la generación de contratos en PDF.
 *
 * Acceder desde: http://localhost/enterprisesstph/public/test_contract_generation.php
 */

echo "<h1>Verificación del Sistema de Generación de Contratos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f5f5f5; }
</style>";

// 1. Verificar PHP y extensiones
echo "<div class='section'>";
echo "<h2>1. Verificación de PHP</h2>";
echo "<table>";
echo "<tr><th>Componente</th><th>Estado</th><th>Detalles</th></tr>";

// Versión de PHP
echo "<tr><td>PHP Versión</td>";
if (version_compare(PHP_VERSION, '8.1.0') >= 0) {
    echo "<td class='success'>✓ OK</td><td>" . PHP_VERSION . "</td></tr>";
} else {
    echo "<td class='error'>✗ ERROR</td><td>" . PHP_VERSION . " (Se requiere PHP 8.1+)</td></tr>";
}

// Extensión GD
echo "<tr><td>Extensión GD (para imágenes)</td>";
if (extension_loaded('gd')) {
    echo "<td class='success'>✓ OK</td><td>Instalada</td></tr>";
} else {
    echo "<td class='warning'>⚠ ADVERTENCIA</td><td>No instalada (opcional para logos)</td></tr>";
}

// Extensión mbstring
echo "<tr><td>Extensión mbstring</td>";
if (extension_loaded('mbstring')) {
    echo "<td class='success'>✓ OK</td><td>Instalada</td></tr>";
} else {
    echo "<td class='error'>✗ ERROR</td><td>Requerida para TCPDF</td></tr>";
}

echo "</table>";
echo "</div>";

// 2. Verificar Composer y paquetes
echo "<div class='section'>";
echo "<h2>2. Verificación de Composer y Paquetes</h2>";
echo "<table>";
echo "<tr><th>Paquete</th><th>Estado</th><th>Detalles</th></tr>";

// Autoload de Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
echo "<tr><td>Autoload de Composer</td>";
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    echo "<td class='success'>✓ OK</td><td>$autoloadPath</td></tr>";
} else {
    echo "<td class='error'>✗ ERROR</td><td>No encontrado. Ejecutar: composer install</td></tr>";
}

// TCPDF
echo "<tr><td>TCPDF</td>";
if (class_exists('TCPDF')) {
    try {
        $tcpdf = new TCPDF();
        echo "<td class='success'>✓ OK</td><td>Instalado correctamente</td></tr>";
    } catch (Exception $e) {
        echo "<td class='warning'>⚠ ADVERTENCIA</td><td>Clase existe pero hay error: " . $e->getMessage() . "</td></tr>";
    }
} else {
    echo "<td class='error'>✗ ERROR</td><td>No instalado. Ejecutar: composer require tecnickcom/tcpdf</td></tr>";
}

// SendGrid
echo "<tr><td>SendGrid</td>";
if (class_exists('SendGrid')) {
    echo "<td class='success'>✓ OK</td><td>Instalado</td></tr>";
} else {
    echo "<td class='error'>✗ ERROR</td><td>No instalado. Ejecutar: composer require sendgrid/sendgrid</td></tr>";
}

echo "</table>";
echo "</div>";

// 3. Verificar directorios
echo "<div class='section'>";
echo "<h2>3. Verificación de Directorios</h2>";
echo "<table>";
echo "<tr><th>Directorio</th><th>Estado</th><th>Permisos</th></tr>";

$uploadDir = __DIR__ . '/uploads/contratos/';
echo "<tr><td>/public/uploads/contratos/</td>";
if (is_dir($uploadDir)) {
    $perms = substr(sprintf('%o', fileperms($uploadDir)), -4);
    if (is_writable($uploadDir)) {
        echo "<td class='success'>✓ OK</td><td>Permisos: $perms (Escribible)</td></tr>";
    } else {
        echo "<td class='warning'>⚠ ADVERTENCIA</td><td>Permisos: $perms (No escribible)</td></tr>";
    }
} else {
    // Intentar crear
    if (@mkdir($uploadDir, 0755, true)) {
        echo "<td class='success'>✓ OK</td><td>Creado automáticamente</td></tr>";
    } else {
        echo "<td class='error'>✗ ERROR</td><td>No existe y no se pudo crear</td></tr>";
    }
}

echo "</table>";
echo "</div>";

// 4. Verificar archivos del sistema
echo "<div class='section'>";
echo "<h2>4. Verificación de Archivos del Sistema</h2>";
echo "<table>";
echo "<tr><th>Archivo</th><th>Estado</th><th>Ubicación</th></tr>";

$files = [
    'ContractPDFGenerator' => __DIR__ . '/../app/Libraries/ContractPDFGenerator.php',
    'ContractController' => __DIR__ . '/../app/Controllers/ContractController.php',
    'ContractModel' => __DIR__ . '/../app/Models/ContractModel.php',
    'ContractLibrary' => __DIR__ . '/../app/Libraries/ContractLibrary.php',
    'Vista edit_contract_data' => __DIR__ . '/../app/Views/contracts/edit_contract_data.php',
    'Vista view contrato' => __DIR__ . '/../app/Views/contracts/view.php'
];

foreach ($files as $name => $path) {
    echo "<tr><td>$name</td>";
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<td class='success'>✓ OK</td><td>" . number_format($size) . " bytes</td></tr>";
    } else {
        echo "<td class='error'>✗ ERROR</td><td>No encontrado</td></tr>";
    }
}

echo "</table>";
echo "</div>";

// 5. Verificar variables de entorno
echo "<div class='section'>";
echo "<h2>5. Verificación de Variables de Entorno</h2>";
echo "<table>";
echo "<tr><th>Variable</th><th>Estado</th><th>Valor</th></tr>";

// Cargar .env si existe
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);

    echo "<tr><td>.env file</td><td class='success'>✓ OK</td><td>Encontrado</td></tr>";

    // Verificar SENDGRID_API_KEY
    if (preg_match('/SENDGRID_API_KEY\s*=\s*(.+)/', $envContent, $matches)) {
        $apiKey = trim($matches[1]);
        if (!empty($apiKey) && $apiKey !== 'your_api_key_here') {
            echo "<tr><td>SENDGRID_API_KEY</td><td class='success'>✓ OK</td><td>Configurada (". substr($apiKey, 0, 10) . "...)</td></tr>";
        } else {
            echo "<tr><td>SENDGRID_API_KEY</td><td class='warning'>⚠ ADVERTENCIA</td><td>No configurada o valor por defecto</td></tr>";
        }
    } else {
        echo "<tr><td>SENDGRID_API_KEY</td><td class='error'>✗ ERROR</td><td>No encontrada en .env</td></tr>";
    }
} else {
    echo "<tr><td>.env file</td><td class='error'>✗ ERROR</td><td>No encontrado</td></tr>";
}

echo "</table>";
echo "</div>";

// 6. Prueba de conexión a base de datos
echo "<div class='section'>";
echo "<h2>6. Verificación de Base de Datos</h2>";
echo "<table>";
echo "<tr><th>Componente</th><th>Estado</th><th>Detalles</th></tr>";

try {
    // Usar configuración de Database.php (valores por defecto de CodeIgniter)
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'propiedad_horizontal';

    $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($mysqli->connect_error) {
        echo "<tr><td>Conexión MySQL</td><td class='error'>✗ ERROR</td><td>" . $mysqli->connect_error . "</td></tr>";
    } else {
        echo "<tr><td>Conexión MySQL</td><td class='success'>✓ OK</td><td>Conectado a " . $dbName . "</td></tr>";

        // Verificar tabla tbl_contratos
        $result = $mysqli->query("SHOW TABLES LIKE 'tbl_contratos'");
        if ($result && $result->num_rows > 0) {
            echo "<tr><td>Tabla tbl_contratos</td><td class='success'>✓ OK</td><td>Existe</td></tr>";

            // Verificar campos nuevos
            $result = $mysqli->query("SHOW COLUMNS FROM tbl_contratos LIKE 'contrato_generado'");
            if ($result && $result->num_rows > 0) {
                echo "<tr><td>Campos de generación PDF</td><td class='success'>✓ OK</td><td>Migración aplicada</td></tr>";

                // Contar contratos existentes
                $result = $mysqli->query("SELECT COUNT(*) as total FROM tbl_contratos");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<tr><td>Contratos en BD</td><td class='success'>✓ OK</td><td>" . $row['total'] . " contratos registrados</td></tr>";
                }
            } else {
                echo "<tr><td>Campos de generación PDF</td><td class='error'>✗ ERROR</td><td>Ejecutar migración: add_contract_generation_fields.sql</td></tr>";
            }
        } else {
            echo "<tr><td>Tabla tbl_contratos</td><td class='error'>✗ ERROR</td><td>No existe</td></tr>";
        }

        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<tr><td>Base de datos</td><td class='error'>✗ ERROR</td><td>" . $e->getMessage() . "</td></tr>";
}

echo "</table>";
echo "</div>";

// Resumen final
echo "<div class='section'>";
echo "<h2>7. Resumen y Acciones Recomendadas</h2>";

$errors = substr_count(ob_get_contents(), "class='error'");
$warnings = substr_count(ob_get_contents(), "class='warning'");

if ($errors == 0 && $warnings == 0) {
    echo "<p class='success'>✓ SISTEMA COMPLETAMENTE FUNCIONAL</p>";
    echo "<p>Puede proceder a generar contratos desde: <a href='/enterprisesstph/public/contracts'>/contracts</a></p>";
} elseif ($errors == 0) {
    echo "<p class='warning'>⚠ SISTEMA FUNCIONAL CON ADVERTENCIAS</p>";
    echo "<p>El sistema funcionará pero se recomienda resolver las advertencias.</p>";
} else {
    echo "<p class='error'>✗ ERRORES DETECTADOS</p>";
    echo "<p>Por favor, resolver los errores marcados en rojo antes de usar el sistema.</p>";

    echo "<h3>Acciones Recomendadas:</h3>";
    echo "<ol>";
    echo "<li>Ejecutar: <code>composer install</code></li>";
    echo "<li>Ejecutar: <code>composer require tecnickcom/tcpdf</code></li>";
    echo "<li>Aplicar migración: <code>database/migrations/add_contract_generation_fields.sql</code></li>";
    echo "<li>Configurar <code>SENDGRID_API_KEY</code> en archivo <code>.env</code></li>";
    echo "<li>Verificar permisos de escritura en <code>/public/uploads/contratos/</code></li>";
    echo "</ol>";
}

echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666; font-size: 12px;'>";
echo "Script de verificación generado el " . date('d/m/Y H:i:s') . "<br>";
echo "Cycloid Talent - Sistema de Gestión de Contratos";
echo "</p>";
