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

function sql_to_csv($sql, $redact_column = null)
{
	/* The output of this function should exactly match the output of:
	
	select * from cases into outfile '/tmp/cases.csv' 
	fields terminated by ',' enclosed by '"' lines terminated by '\n';
	*/
	
	$output = fopen('php://output', 'w');
	$rows = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($rows))
	{
		foreach ($row as $key => $val)
		{
			if ($val === null)
			{
				$row[$key] = '\N';
			}
			
			else 
			{
				$row[$key] = str_replace('"', '\"', $val);
			}
		}
		
		if ($redact_column !== null)
		{
			$row[$redact_column] = "[redacted]";
		}
		$x = '"' . implode($row, '","') . '"' . "\n";
		$x = str_replace('"\N"', '\N', $x);
		
		fwrite($output, $x);
		flush();
	}
	
	fclose($output);
}

/*	AMW - I added this function to allow browsers to successfully download
	very large (300MB+) files.  However browser downloads still failed with it
	in place.  wget works fine with either method.  I believe the browser is the
	failure point.  I'm going to leave the code in, in case it's useful later.
	*/
function chunk_table($table, $key)
{
	$chunk_size = 10000;  // When this was set to 100, it ran quite slow.
	$safe_key = mysql_real_escape_string($key);
	$safe_table = mysql_real_escape_string($table);
	$result = mysql_query("SELECT MAX({$safe_key}) FROM {$safe_table}");
	$row = mysql_fetch_array($result);
	$max = $row[0];
	
	for ($i = 0; $i < $max; $i = $i + $chunk_size)
	{
		sql_to_csv("SELECT * FROM {$safe_table} ORDER BY {$safe_key} DESC LIMIT {$i}, {$chunk_size}");
	}
}

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
