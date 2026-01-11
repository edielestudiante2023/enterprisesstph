<?php
$conn = new mysqli('localhost', 'root', '', 'propiedad_horizontal');

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h2>Contenido completo de la tabla accesos</h2>";
echo "<style>table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #4CAF50; color: white; }</style>";

$result = $conn->query("SELECT * FROM accesos ORDER BY id_acceso");

if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>id_acceso</th><th>nombre</th><th>url</th><th>dimension</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id_acceso']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td><strong>{$row['url']}</strong></td>";
        echo "<td>{$row['dimension']}</td>";
        echo "</tr>";
    }

    echo "</table>";
    echo "<p><strong>Total:</strong> " . $result->num_rows . " registros</p>";
} else {
    echo "<p style='color: red;'>❌ La tabla accesos está vacía o no existe</p>";
}

$conn->close();
