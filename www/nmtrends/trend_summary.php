<h2>Case Trend Report for <?php echo date('F jS, Y'); ?></h2>
<p>In the last 45 days...</p>
<table>
<?php 


/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


require_once('init.php');




// AMW 2013-12-03 - Fix missing zero values on graphs with a calendar table.
$sql = "CREATE TEMPORARY TABLE cal (stat_date DATE);";
//echo $sql . "<br>";
$result = mysql_query($sql) or trigger_error(mysql_error());

for($i =0; $i < 36; $i++)
{
	$sql = "INSERT INTO cal (stat_date) VALUES 
		(DATE_SUB(NOW(), INTERVAL {$i} MONTH));";
	//echo $sql . "<br>";
	$result = mysql_query($sql) or trigger_error(mysql_error());
}

$sql = "update cal set stat_date = CONCAT(YEAR(stat_date), '-', MONTH(stat_date), '-01');";
//echo $sql . "<br>";
$result = mysql_query($sql) or trigger_error(mysql_error());


/*
$sql = "SELECT * FROM cal";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result))
{
	var_dump($row);
}
*/

$sql = "SELECT case_trend, problem, label, IF(case_trend > 0, case_trend, ABS(case_trend/50)) AS trend_weight FROM stats LEFT JOIN menu_problem_2008 ON stats.problem=menu_problem_2008.value WHERE problem != '00' AND stat_year=" .
	date('Y') . "  AND stat_month=" . date('n') . " ORDER by trend_weight DESC";
	//echo $sql . "<br>";
$result = mysql_query($sql) or trigger_error(mysql_error());

$keep_going = true;
$i = 0;
while (true == $keep_going && $row = mysql_fetch_assoc($result))
{
	$chart_elements = array();
	
	$sql = "CREATE TEMPORARY TABLE stats_{$row['problem']} SELECT stat_date, current, stat_year, stat_month, problem " 
		. "FROM stats "
		. "WHERE (stat_year != " . date('Y') . " OR stat_month <= " . date('n')
		. ") AND problem='{$row['problem']}' ORDER BY stat_id DESC LIMIT 36;";
	$result2 = mysql_query($sql) or trigger_error("SQL: " . $sql . ' Error: ' . mysql_error());
	
	//echo $sql . "<br>";

	$sql = "SELECT COALESCE(current, 0) AS currentb, YEAR(stat_date) AS stat_yearb, MONTH(stat_date) AS stat_monthb " 
		. "FROM cal LEFT JOIN stats_{$row['problem']} USING (stat_date)"
		. "ORDER BY stat_date DESC LIMIT 36;";
	$result2 = mysql_query($sql) or trigger_error("SQL: " . $sql . ' Error: ' . mysql_error());
	
	//echo $sql . "<br>";

	while ($row2 = mysql_fetch_assoc($result2))
	{
		if ($row['problem'] == '83')
		{
			//var_dump($row2);
		}
		array_push($chart_elements, "['" . $row2['stat_yearb'] . "-" . str_pad($row2['stat_monthb'], 2, "0", STR_PAD_LEFT) . "-01'," . $row2['currentb'] . "]");
	}
	
	$chart_elements = array_reverse($chart_elements);
	$chart_data = implode(",", $chart_elements);
		
	if (abs($row['case_trend']) > 0 && $i < 15)
	{
		if ($row['case_trend'] > 10)
		{
			$trend_label = "up significantly";
		}
		
		else if ($row['case_trend'] > 2)
		{
			$trend_label = "up moderately";
		}
		
		else if ($row['case_trend'] > 0)
		{
			$trend_label = "up slightly";
		}
		
		else
		{
			$trend_label = "trending lower";
		}
		
		echo "<tr><td>New problem code {$row['label']} cases are {$trend_label}.</td><td align=\"right\"><a href=\"reporting.php?problem={$row['problem']}\" class=\"btn btn-default btn-lg\">See cases <img src=\"glyphicons/png/glyphicons_119_table.png\"></a>&nbsp;</td></tr>\n";
		
		echo "<tr><td colspan=\"2\">";
		
		echo "<div id=\"chart{$i}\" style=\"height:200px;width:800px;\"></div>\n";
		echo "<script type=\"text/javascript\">\n";
		echo "	$(document).ready(function(){ \n";
		echo "	  var plot{$i} = $.jqplot ('chart{$i}', [[{$chart_data}]], {
    title:'" .$row['label'] ."',
    axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer}},
    series:[{lineWidth:4, markerOptions:{style:'square'}}]
  }); \n";
		echo "}); \n";
		echo "</script>\n<br><br><br></td></tr>";
		$i++;
	}
	
	else 
	{
		$keep_going = false;
	}
}
/*
<tr><td>State and Local Income Maintenance cases (Problem Code 78) are up significantly statewide.</td><td><a href="reporting.html#1">See&nbsp;cases&nbsp<img src="glyphicons/png/glyphicons_119_table.png"></a>&nbsp;</td><td><a href="#">See&nbsp;graph&nbsp<img src="glyphicons/png/glyphicons_040_stats.png"></a></td></tr>
<tr><td>Wage Claims and other FLSA (Fair Labor Standards Act) Issues (Problem Code 22) are up slightly statewide.</td><td><a href="reporting.html#2">See&nbsp;cases&nbsp<img src="glyphicons/png/glyphicons_119_table.png"></a>&nbsp;</td><td><a href="#">See&nbsp;graph&nbsp<img src="glyphicons/png/glyphicons_040_stats.png"></a></td></tr>
<tr><td>Cases for "60 and over" clients are up slightly in Bernalillo County.</td><td><a href="reporting.html#3">See&nbsp;cases&nbsp<img src="glyphicons/png/glyphicons_119_table.png"></a>&nbsp;</td><td><a href="#">See&nbsp;graph&nbsp<img src="glyphicons/png/glyphicons_040_stats.png"></a></td></tr>
<tr><td>Benefits cases for ESL clients are up slightly in Sante Fe County.</td>
	<td><a href="reporting.html#4">See&nbsp;cases&nbsp<img src="glyphicons/png/glyphicons_119_table.png"></a>&nbsp;</td><td><a href="#">See&nbsp;graph&nbsp<img src="glyphicons/png/glyphicons_040_stats.png"></a></td></tr>
*/
?>
</table>


    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>