<?php
require_once('init.php');

$sql = "CREATE TABLE calendar (year INT, month TINYINT)";
//mysql_query($sql);

for($i = 1950; $i < 2100; $i++)
{
	for($j = 1; $j < 13; $j++)
	{
		$sql = "INSERT INTO calendar SET year=$i, month=$j";
		mysql_query($sql);
	}
}

//echo "Done.";
?>