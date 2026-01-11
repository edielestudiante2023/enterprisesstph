<?php
// Script de diagnóstico temporal para verificar accesos
// Este archivo se debe eliminar después del diagnóstico

// Conexión directa a la base de datos
$host = 'localhost';
$dbname = 'propiedad_horizontal';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    echo "<h1>Diagnóstico de Accesos y Estándares - Propiedad Horizontal</h1>";
    echo "<style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
    </style>";
    echo "<p><strong>Nota:</strong> En Propiedad Horizontal los estándares son: mensual, bimensual, trimestral, proyectos</p>";

    // 1. Ver todos los estándares
    echo "<h2>1. Tabla: estandares</h2>";
    $result = $conn->query("SELECT * FROM estandares ORDER BY id_estandar");
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>id_estandar</th><th>nombre</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id_estandar']}</td><td>{$row['nombre']}</td></tr>";
        }
        echo "</table>";
        echo "<p><strong>Total de estándares:</strong> " . $result->num_rows . "</p>";
    } else {
        echo "<p class='error'>❌ No hay datos en la tabla estandares</p>";
    }

    // 2. Ver todos los accesos
    echo "<h2>2. Tabla: accesos</h2>";
    $result = $conn->query("SELECT * FROM accesos ORDER BY dimension, id_acceso");
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>id_acceso</th><th>nombre</th><th>url</th><th>dimension</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            $count++;
            echo "<tr><td>{$row['id_acceso']}</td><td>{$row['nombre']}</td><td>{$row['url']}</td><td>" . ($row['dimension'] ?? 'N/A') . "</td></tr>";
            if ($count >= 10) {
                echo "<tr><td colspan='4'><em>... (mostrando solo las primeras 10 filas)</em></td></tr>";
                break;
            }
        }
        echo "</table>";
        $totalResult = $conn->query("SELECT COUNT(*) as total FROM accesos");
        $total = $totalResult->fetch_assoc();
        echo "<p><strong>Total de accesos:</strong> " . $total['total'] . "</p>";
    } else {
        echo "<p class='error'>❌ No hay datos en la tabla accesos</p>";
    }

    // 3. Ver relaciones estandares_accesos
    echo "<h2>3. Tabla: estandares_accesos (Relaciones)</h2>";
    $result = $conn->query("
        SELECT ea.id, ea.id_estandar, e.nombre as estandar_nombre, ea.id_acceso, a.nombre as acceso_nombre
        FROM estandares_accesos ea
        LEFT JOIN estandares e ON ea.id_estandar = e.id_estandar
        LEFT JOIN accesos a ON ea.id_acceso = a.id_acceso
        ORDER BY ea.id_estandar, ea.id_acceso
        LIMIT 20
    ");
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>id</th><th>id_estandar</th><th>Estándar</th><th>id_acceso</th><th>Acceso</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>{$row['id_estandar']}</td><td>{$row['estandar_nombre']}</td><td>{$row['id_acceso']}</td><td>{$row['acceso_nombre']}</td></tr>";
        }
        echo "</table>";
        $totalResult = $conn->query("SELECT COUNT(*) as total FROM estandares_accesos");
        $total = $totalResult->fetch_assoc();
        echo "<p><strong>Total de relaciones:</strong> " . $total['total'] . "</p>";
    } else {
        echo "<p class='error'>❌ PROBLEMA CRÍTICO: La tabla estandares_accesos está VACÍA - No hay relaciones entre estándares y accesos</p>";
        echo "<p class='warning'>Esto explica por qué no ves documentos en el dashboard.</p>";
    }

    // 4. Ver datos del cliente actual
    echo "<h2>4. Cliente en Sesión</h2>";
    session_start();
    if (isset($_SESSION['user_id'])) {
        $clientId = intval($_SESSION['user_id']);
        $stmt = $conn->prepare("SELECT id_cliente, nombre_cliente, estandares FROM tbl_clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_assoc();

        if ($client) {
            echo "<table><tr><th>id_cliente</th><th>nombre_cliente</th><th>estandares</th></tr>";
            echo "<tr><td>{$client['id_cliente']}</td><td>{$client['nombre_cliente']}</td><td><strong>{$client['estandares']}</strong></td></tr>";
            echo "</table>";

            // Verificar si el estándar del cliente existe
            $estandarCliente = $client['estandares'];
            $stmt = $conn->prepare("SELECT * FROM estandares WHERE nombre = ?");
            $stmt->bind_param("s", $estandarCliente);
            $stmt->execute();
            $result = $stmt->get_result();
            $estandar = $result->fetch_assoc();

            if ($estandar) {
                echo "<p class='success'>✅ El estándar '{$estandarCliente}' existe en la BD (id: {$estandar['id_estandar']})</p>";

                // Ver cuántos accesos tiene este estándar
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM estandares_accesos WHERE id_estandar = ?");
                $stmt->bind_param("i", $estandar['id_estandar']);
                $stmt->execute();
                $result = $stmt->get_result();
                $count = $result->fetch_assoc();

                echo "<p><strong>Total de accesos asignados a este estándar:</strong> {$count['total']}</p>";

                if ($count['total'] == 0) {
                    echo "<p class='error'>⚠️ PROBLEMA ENCONTRADO: El estándar '{$estandarCliente}' existe pero NO tiene accesos asignados en estandares_accesos</p>";
                    echo "<p class='warning'>SOLUCIÓN: Necesitas poblar la tabla estandares_accesos con las relaciones correctas.</p>";
                }
            } else {
                echo "<p class='error'>❌ El estándar '{$estandarCliente}' NO existe en la tabla estandares</p>";
                echo "<p class='warning'>SOLUCIÓN: Agrega '{$estandarCliente}' a la tabla estandares y luego crea las relaciones en estandares_accesos.</p>";
            }
        }
    } else {
        echo "<p class='warning'>⚠️ No hay sesión activa. <a href='/enterprisesstph/public/index.php/login'>Inicia sesión primero</a></p>";
    }

    // Resumen de diagnóstico
    echo "<hr>";
    echo "<h2>📊 Resumen del Diagnóstico</h2>";

    $totalEstandares = $conn->query("SELECT COUNT(*) as total FROM estandares")->fetch_assoc()['total'];
    $totalAccesos = $conn->query("SELECT COUNT(*) as total FROM accesos")->fetch_assoc()['total'];
    $totalRelaciones = $conn->query("SELECT COUNT(*) as total FROM estandares_accesos")->fetch_assoc()['total'];

    echo "<ul>";
    echo "<li><strong>Estándares en BD:</strong> {$totalEstandares}</li>";
    echo "<li><strong>Accesos en BD:</strong> {$totalAccesos}</li>";
    echo "<li><strong>Relaciones en estandares_accesos:</strong> {$totalRelaciones}</li>";
    echo "</ul>";

    if ($totalRelaciones == 0) {
        echo "<p class='error'><strong>❌ DIAGNÓSTICO:</strong> La tabla estandares_accesos está vacía. Este es el problema principal.</p>";
        echo "<p><strong>SOLUCIÓN:</strong> Necesitas un script de migración que llene estandares_accesos con las relaciones entre estándares y accesos.</p>";
    }

    $conn->close();

} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Diagnóstico completado. Elimina este archivo después de revisar.</em></p>";
