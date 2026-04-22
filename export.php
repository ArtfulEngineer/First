<?php
include "auth.php"; include "db_conn.php";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="data.csv"');
$out=fopen("php://output","w");
$res=$conn->query("SELECT * FROM inquiries");
while($r=$res->fetch_assoc()){ fputcsv($out,$r); }
fclose($out);
?>