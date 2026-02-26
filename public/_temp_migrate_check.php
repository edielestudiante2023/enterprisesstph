<?php
// Temporary script to check tbl_detail_report structure
// DELETE THIS FILE after use
header('Content-Type: text/plain');

$c = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
if ($c->connect_error) {
    echo 'ERR: ' . $c->connect_error . "\n";
    exit(1);
}
echo "Connected OK\n\n";

echo "=== tbl_detail_report structure ===\n";
$r = $c->query('DESCRIBE tbl_detail_report');
if (!$r) { echo 'Query error: ' . $c->error . "\n"; exit(1); }
while ($row = $r->fetch_assoc()) echo $row['Field'] . ' (' . $row['Type'] . ")\n";

echo "\n=== Existing rows ===\n";
$r2 = $c->query('SELECT * FROM tbl_detail_report ORDER BY id_detailreport');
if ($r2) {
    while ($row = $r2->fetch_assoc()) {
        foreach($row as $k => $v) echo "$k=$v ";
        echo "\n";
    }
}

echo "\n=== Check if tbl_informe_avances exists ===\n";
$r3 = $c->query("SHOW TABLES LIKE 'tbl_informe_avances'");
echo ($r3 && $r3->num_rows > 0) ? "YES - table exists\n" : "NO - table does not exist\n";

$c->close();
echo "\nDone\n";
