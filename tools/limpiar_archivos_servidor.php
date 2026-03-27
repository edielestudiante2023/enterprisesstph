<?php
/**
 * Limpiar BD y archivos corruptos del servidor
 *
 * Paso 1: Extraer lista de archivos a conservar
 * Paso 2: Verificar BD (los DELETE ya se hicieron)
 * Paso 3: Generar script SSH para borrar archivos físicos corruptos
 *
 * Uso: DB_PROD_PASS=xxx php tools/limpiar_archivos_servidor.php
 */

$pass = getenv('DB_PROD_PASS');
if (!$pass) die("ERROR: Variable DB_PROD_PASS no definida.\n");

$db = new mysqli();
$db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb', $pass,
    'propiedad_horizontal', 25060, null, MYSQLI_CLIENT_SSL
);
if ($db->connect_error) die("ERROR conexión: {$db->connect_error}\n");

echo "=== PASO 1: Extraer archivos a conservar ===\n\n";

$result = $db->query("SELECT id_reporte, enlace, observaciones FROM tbl_reporte ORDER BY id_reporte");
$enlaces_conservar = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $total++;
    $enlace = $row['enlace'];
    $obs = $row['observaciones'] ?? '(NULL)';
    if ($obs === '') $obs = '(vacío)';
    echo "  ID:{$row['id_reporte']} | {$enlace} | {$obs}\n";

    // Extraer la ruta relativa del enlace
    // Los enlaces tienen formato: serve-file/NIT/archivo.pdf
    // Los archivos físicos están en: writable/soportes-clientes/NIT/archivo.pdf
    if (preg_match('#serve-file/(.+)$#', $enlace, $m)) {
        $enlaces_conservar[] = $m[1]; // NIT/archivo.pdf
    }
}
echo "\n  Total registros en BD: {$total}\n";
echo "  Enlaces con ruta serve-file: " . count($enlaces_conservar) . "\n\n";

// Guardar lista
file_put_contents('/tmp/archivos_conservar.txt', implode("\n", $enlaces_conservar) . "\n");
echo "  Lista guardada en /tmp/archivos_conservar.txt\n\n";

echo "=== PASO 2: Verificar BD ===\n\n";
$result = $db->query("SELECT observaciones, COUNT(*) as cnt FROM tbl_reporte GROUP BY observaciones ORDER BY cnt DESC");
while ($row = $result->fetch_assoc()) {
    $obs = $row['observaciones'] ?? '(NULL)';
    if ($obs === '') $obs = '(vacío)';
    printf("  [%4d] %s\n", $row['cnt'], $obs);
}

// Verificar que no quedan corruptos
$check = $db->query("SELECT COUNT(*) as c FROM tbl_reporte WHERE observaciones LIKE 'Recargado desde Takeout%' OR observaciones LIKE 'Registrado v2 huerfano+email%'");
$corruptos = $check->fetch_assoc()['c'];
echo "\n  Registros corruptos restantes: {$corruptos}\n";
if ($corruptos > 0) {
    echo "  ADVERTENCIA: Aún hay registros corruptos. Ejecutar DELETE.\n";
}

$db->close();

echo "\n=== PASO 3: Generar script de limpieza para servidor ===\n\n";

// Generar script bash que se ejecutará en el servidor
$script = "#!/bin/bash\n";
$script .= "# Script de limpieza de archivos corruptos en writable/soportes-clientes/\n";
$script .= "# Generado automáticamente el " . date('Y-m-d H:i:s') . "\n";
$script .= "# SOLO borra archivos en writable/soportes-clientes/ que NO están en la lista de conservar\n\n";
$script .= "cd /www/wwwroot/phorizontal/enterprisesstph\n\n";
$script .= "echo '=== Archivos a CONSERVAR ==='\n";

// Crear array de archivos a conservar para el script
$script .= "declare -A CONSERVAR\n";
foreach ($enlaces_conservar as $ruta) {
    $script .= "CONSERVAR[\"writable/soportes-clientes/{$ruta}\"]=1\n";
}

$script .= "\necho '=== Escaneando writable/soportes-clientes/ ==='\n";
$script .= "TOTAL=0\n";
$script .= "BORRADOS=0\n";
$script .= "CONSERVADOS=0\n\n";

$script .= "while IFS= read -r archivo; do\n";
$script .= "    TOTAL=\$((TOTAL+1))\n";
$script .= "    if [[ -n \"\${CONSERVAR[\$archivo]}\" ]]; then\n";
$script .= "        echo \"  CONSERVAR: \$archivo\"\n";
$script .= "        CONSERVADOS=\$((CONSERVADOS+1))\n";
$script .= "    else\n";
$script .= "        echo \"  BORRAR: \$archivo\"\n";
$script .= "        rm -f \"\$archivo\"\n";
$script .= "        BORRADOS=\$((BORRADOS+1))\n";
$script .= "    fi\n";
$script .= "done < <(find writable/soportes-clientes/ -type f 2>/dev/null)\n\n";

$script .= "echo ''\n";
$script .= "echo '=== Resumen ==='\n";
$script .= "echo \"Total archivos encontrados: \$TOTAL\"\n";
$script .= "echo \"Conservados: \$CONSERVADOS\"\n";
$script .= "echo \"Borrados: \$BORRADOS\"\n\n";

$script .= "# Limpiar directorios vacíos\n";
$script .= "find writable/soportes-clientes/ -type d -empty -delete 2>/dev/null\n";
$script .= "echo 'Directorios vacíos eliminados.'\n";

file_put_contents('/tmp/limpiar_servidor.sh', $script);
echo "  Script generado en /tmp/limpiar_servidor.sh\n";
echo "  Contiene " . count($enlaces_conservar) . " archivos en lista de conservar\n\n";
echo "  Para ejecutar:\n";
echo "    1. Copiar al servidor: scp /tmp/limpiar_servidor.sh root@66.29.154.174:/tmp/\n";
echo "    2. Ejecutar: ssh root@66.29.154.174 'bash /tmp/limpiar_servidor.sh'\n";
