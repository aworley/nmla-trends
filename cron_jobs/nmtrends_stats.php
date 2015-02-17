#!/usr/bin/php
<?php

/**********************************/
/* New Mexico Trends Database     */
/* Pika Software, LLC. (C) 2013   */
/* http://pikasoftware.com        */
/**********************************/


define('DB_HOST','localhost');
define('DB_NAME','trends_database');
define('DB_USER','mysql_username');
define('DB_PASS','mysql_password');

set_time_limit(1200);
ini_set('display_errors','Off');
ini_set('memory_limit','256M');

mysql_connect(DB_HOST,DB_USER,DB_PASS);
mysql_select_db(DB_NAME);

if (PHP_SAPI != "cli") 
{
	echo "Program must be run from CLI";
	exit(1);
}


function run_month($y, $m) 
{
	$start_year = $y - 15;
	$sql = "DROP TEMPORARY TABLE IF EXISTS problem_by_month;";
	$result = mysql_query($sql);
	$sql = "create temporary table problem_by_month
	select problem, ROUND(count(*)/15) as case_count from cases
	where open_date >= '{$start_year}-01-01' and open_date < '{$y}-01-01' and MONTH(open_date) = {$m}
	group by problem order by problem asc;";
	//echo $sql; 
	$result = mysql_query($sql);
	//echo mysql_num_rows($result);
	//echo mysql_error();

	// build recent numbers
	$current_date = "{$y}-{$m}-01";
	$start_date = strtotime ( '-6 month' , strtotime ( $current_date ) ) ;
	$start_date = date ( 'Y-m-j' , $start_date );
	$end_date = strtotime ( '-3 month' , strtotime ( $current_date ) ) ;
	$end_date = date ( 'Y-m-j' , $end_date );
	 
	$sql = "DROP TEMPORARY TABLE IF EXISTS problem_recent;";
	$result = mysql_query($sql);
	$sql = "create temporary table problem_recent
	select problem, ROUND(count(*)/3) as case_count from cases
	where open_date >= '{$start_date}' and open_date < '{$end_date}'
	group by problem order by problem asc;";
	//echo $sql; 
	$result = mysql_query($sql);
	//echo mysql_num_rows($result);
	//echo mysql_error();

	// build current numbers
	$start_date = strtotime ( '-6 week' , strtotime ( $current_date ) ) ;
	$start_date = date ( 'Y-m-j' , $start_date );
	$sql = "DROP TEMPORARY TABLE IF EXISTS current_month;";
	$result = mysql_query($sql);
	$sql = "create temporary table current_month
	select problem, {$m} as case_month, round(count(*)/2) as case_count from cases
	where open_date >= '{$start_date}' and open_date < '{$y}-{$m}-01'
	group by problem order by problem asc;";
	//echo $sql; 
	$result = mysql_query($sql);
	//echo mysql_num_rows($result);
	//echo mysql_error();

	// compare this month to average over past 15 years
	$sql = "select c.case_count - (IF(m.case_count > r.case_count, m.case_count, r.case_count)) as case_trend, c.problem, '{$y}' as year, '{$m}' as month,
	c.case_count as current, m.case_count as 'average for this month', r.case_count as 'recent average',
	CONCAT('{$y}', '-', LPAD('{$m}', 2, '0'), '-01') AS stat_date
	from current_month as c 
	left join problem_by_month as m using (problem)
	left join problem_recent as r using (problem);";
	
	//echo $sql;
	$result = mysql_query($sql);
	//echo mysql_error();
	//echo mysql_num_rows($result);
	
	while($row = mysql_fetch_assoc($result))
	{
		//echo $row['stat_date'] . "\n";
		//echo implode("','", $row) . "\n";
		$z = implode("','", $row);
		$sql = "INSERT INTO stats VALUES ('NULL','{$z}')";
		//echo $sql;
		mysql_query($sql) or trigger_error('Error');
	}
}

function run_year($y)
{
	for ($i = 1; $i < 13; $i++)
	{
		run_month($y, $i);
	}
}

				
// AMW 2013-11-23 - Fix for cases with '0000-00-00' values in close_code.
mysql_query("UPDATE cases SET close_date = NULL WHERE close_date = '0000-00-00'");

//echo "Trends Database Rebuild.\n";
//echo "Trend,ProblemCode,Year,Month,CurrentCaseCount,AverageForThisMonth,RecentAverage\n";

mysql_query("TRUNCATE TABLE stats");

for ($j = 1980; $j <= date('Y'); $j++)
{
	run_year($j);
	//echo "{$j} completed.\n";
}

//run_month(2010, 1);
//run_month(2012, 6);
?>
