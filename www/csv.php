<?php

/********************************/
/* (C) 2016 Pika Software, LLC. */
/* http://pikasoftware.com      */
/********************************/

set_time_limit(900);
ini_set('memory_limit', '512M');
ini_set("zlib.output_compression", "On");
ini_set("zlib.output_compression_level", 9);

include('init.php');

$action = 'cases';
$download_name = 'case-trends-' . date('M-j-Y-H-i-s');
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename={$download_name}.csv");

$columns = array();
$result = mysql_query("DESCRIBE " . $action);

while ($row= mysql_fetch_assoc($result))
{
	$columns[] = $row['Field'];
}

$output = fopen('php://output', 'w');
fputcsv($output, $columns);
flush();
fclose($output);
sql_to_csv('SELECT * FROM ' . $action);

exit();
