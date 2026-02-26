<?php
// Force TCP connection by using 127.0.0.1 and explicit port
$c = @new mysqli('127.0.0.1', 'root', '', 'propiedad_horizontal', 3306);
if ($c->connect_error) {
    echo 'ERR: ' . $c->connect_error . "\n";
    exit(1);
}
echo "Connected OK\n";
$r = $c->query('DESCRIBE tbl_detail_report');
if (!$r) { echo 'Query error: ' . $c->error . "\n"; exit(1); }
while ($row = $r->fetch_assoc()) echo $row['Field'] . ' (' . $row['Type'] . ")\n";
echo "\n--- Existing rows ---\n";
$r2 = $c->query('SELECT * FROM tbl_detail_report ORDER BY id_detailreport');
if ($r2) { while ($row = $r2->fetch_assoc()) { foreach($row as $k=>$v) echo "$k=$v "; echo "\n"; } }
$c->close();
echo "Done\n";
