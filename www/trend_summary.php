<?php
/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/

function trend_graph($problem, $case_trend, $label, $chart_id, $base_url)
{
	$chart_elements = array();
	$o = '';
	
	$sql = "CREATE TEMPORARY TABLE stats_{$problem} SELECT stat_date, current, stat_year, stat_month, problem " 
		. "FROM stats "
		. "WHERE (stat_year != " . date('Y') . " OR stat_month <= " . date('n')
		. ") AND problem='{$problem}' ORDER BY stat_id DESC LIMIT 36;";
	$result2 = mysql_query($sql) or trigger_error("SQL: " . $sql . ' Error: ' . mysql_error());

	$sql = "SELECT COALESCE(current, 0) AS currentb, YEAR(stat_date) AS stat_yearb, MONTH(stat_date) AS stat_monthb " 
		. "FROM cal LEFT JOIN stats_{$problem} USING (stat_date)"
		. "ORDER BY stat_date DESC LIMIT 36;";
	$result2 = mysql_query($sql) or trigger_error("SQL: " . $sql . ' Error: ' . mysql_error());

	while ($row2 = mysql_fetch_assoc($result2))
	{
		array_push($chart_elements, "['" . $row2['stat_yearb'] . "-" . str_pad($row2['stat_monthb'], 2, "0", STR_PAD_LEFT) . "-01'," . $row2['currentb'] . "]");
	}
	
	$chart_elements = array_reverse($chart_elements);
	$chart_data = implode(",", $chart_elements);
	
	if ($case_trend > 10)
	{
		$trend_label = "up significantly";
	}
	
	else if ($case_trend > 2)
	{
		$trend_label = "up moderately";
	}
	
	else if ($case_trend > 0)
	{
		$trend_label = "up slightly";
	}
	
	else
	{
		$trend_label = "trending lower";
	}
	
	$o .= "<tr><td>New problem code {$label} cases are {$trend_label}.</td><td align=\"right\"><a href=\"{$base_url}/reporting.php?problem={$problem}\" class=\"btn btn-default btn-lg\">See cases <img src=\"{$base_url}/glyphicons/png/glyphicons_119_table.png\"></a>&nbsp;</td></tr>\n";
	
	$o .= "<tr><td colspan=\"2\">";
	
	$o .= "<div id=\"chart{$chart_id}\" style=\"height:200px;width:800px;\"></div>\n";
	$o .= "<script type=\"text/javascript\">\n";
	$o .= "	$(document).ready(function(){ \n";
	$o .= "	  var plot{$chart_id} = $.jqplot ('chart{$chart_id}', [[{$chart_data}]], {
	title:'" .$label ."',
	axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer}},
	series:[{lineWidth:4, markerOptions:{style:'square'}}]
}); \n";
	$o .= "}); \n";
	$o .= "</script>\n<br><br><br></td></tr>";
	
	return $o;
}

function trend_summary($base_url = '', $mode = 'www') 
{
	$trend_email_min_cutoff = 2;
	$sample_size_min_cutoff = 9;
	$top_trends_max_size = 10;
	$output = '';
	
	if (isset($_GET['all']))  // Trends by problem code.
	{
		$output .= "
		<h2>Case Trend Report by Legal Problem Code for ". date('F jS, Y') . "</h2>
		<p>Intake Trend Report for the past 45 days by Legal Problem Code for " . date('F jS, Y') . "</p>
		<table>
		";
	}
	
	else if ('www' == $mode)  // Top trends.
	{
		$output .= "
		<h2>Case Trend Report for ". date('F jS, Y') . "</h2>
		<p>In the past 45 days, the legal problem codes with the largest changes in intake volume are...</p>
		<table>
		";		
	}
	
	else // Email alert.
	{
		$output .= "
		<h2>Case Trend Alert for ". date('F jS, Y') . "</h2>
		<p>In the last 45 days, the legal problem codes with the largest changes in 
		case volume are...</p>
		<table>
		";
	}

// AMW 2013-12-03 - Fix missing zero values on graphs with a calendar table.
$sql = "CREATE TEMPORARY TABLE cal (stat_date DATE);";
$result = mysql_query($sql) or trigger_error(mysql_error());

for($i =0; $i < 36; $i++)
{
	$sql = "INSERT INTO cal (stat_date) VALUES 
		(DATE_SUB(NOW(), INTERVAL {$i} MONTH));";
	$result = mysql_query($sql) or trigger_error(mysql_error());
}

$sql = "update cal set stat_date = CONCAT(YEAR(stat_date), '-', MONTH(stat_date), '-01');";
$result = mysql_query($sql) or trigger_error(mysql_error());

$i = 0;

if (isset($_GET['all']))
{
	$sql = "SELECT case_trend, problem, label, IF(case_trend > 0, case_trend, ABS(case_trend/50)) AS trend_weight FROM stats LEFT JOIN menu_problem_2008 ON stats.problem=menu_problem_2008.value WHERE problem != '00' AND stat_year=" .
		date('Y') . "  AND stat_month=" . date('n') . " ORDER by problem ASC";
	$result = mysql_query($sql) or trigger_error(mysql_error());

	while ($row = mysql_fetch_assoc($result))
	{
		$output .= trend_graph($row['problem'], $row['case_trend'], $row['label'], $i, $base_url);
		$i++;
	}	
}

else 
{
	$sql = "SELECT case_trend, problem, label, IF(case_trend > 0, case_trend, ABS(case_trend/50)) AS trend_weight FROM stats LEFT JOIN menu_problem_2008 ON stats.problem=menu_problem_2008.value WHERE problem != '00' AND stat_year=" .
		date('Y') . "  AND stat_month=" . date('n') . " AND current > {$sample_size_min_cutoff} ORDER by trend_weight DESC";
	$result = mysql_query($sql) or trigger_error(mysql_error());

	$keep_going = true;
	
	while ($row = mysql_fetch_assoc($result))
	{
		if ('email' == $mode)
		{
			if ($row['case_trend'] > $trend_email_min_cutoff)
			{
				$output .= "<tr><td>New problem code {$row['label']} cases are higher.</td>
				<td align=\"right\">
				<a href=\"{$base_url}/reporting.php?problem={$row['problem']}\" class=\"btn btn-default\">
				View These Cases</a></td></tr>";
			}
			
			else 
			{
				break;
			}
		}
		
		else
		{
			// WWW trend cutoff is higher or lower than 0.
			if (abs($row['case_trend']) > 0)
			{
				$output .= trend_graph($row['problem'], $row['case_trend'], $row['label'], $i, $base_url);
			}
			
			else 
			{
				break;
			}
		}
		
		$i++;

		if ($i >= $top_trends_max_size)
		{
			break;
		}
	}
}

$output .= '
</table>
<table>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="' . $base_url . '/js/bootstrap.min.js"></script>
';
	return $output;
}