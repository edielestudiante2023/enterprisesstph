<?php
$conn = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
$result = $conn->query('DESCRIBE accesos');

echo "<h2>Estructura de tabla accesos:</h2>";
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

// Ver un ejemplo de la tabla policy_types para entender la relación
echo "<h2>Ejemplo de documentos de DocumentLibrary (primeros 5):</h2>";
echo "<pre>";
require_once __DIR__ . '/../vendor/autoload.php';
$docs = \App\Libraries\DocumentLibrary::getAllDocuments();
$count = 0;
foreach ($docs as $id => $doc) {
    echo "ID: {$id}\n";
    echo "  Nombre: {$doc['type_name']}\n";
    echo "  Acrónimo: {$doc['acronym']}\n";
    echo "  Tipo: {$doc['document_type']}\n\n";
    $count++;
    if ($count >= 5) break;
}
echo "</pre>";
