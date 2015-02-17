<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<meta name="author" content="">
<meta http-equiv="Reply-to" content="@.com">
<meta name="generator" content="PhpED 6.0">
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="creation-date" content="06/01/2011">
<meta name="revisit-after" content="15 days">
<title>New Mexico Data Sharing Project</title>

<script language="javascript" type="text/javascript" src="js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.dateAxisRenderer.min.js"></script>
<link rel="stylesheet" type="text/css" href="js/jquery.jqplot.min.css" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

<link rel="stylesheet" type="text/css" href="css/trends.css">
<style type="text/css">

/*
input{
	color: gray;
	font-size: 1.8em;
	// border-radius: 10px; 
}
#nav{
	padding: 1em;
}
#nav a{
	color:black;
	border-color: #aaa;
	border-style: solid;
	border-width: 1px;
	padding: 0.5em;
	background-color: #ddd;
	margin-right: 1em;
}
#login{
   position:fixed;
    top: 50%;
    left: 50%;
    width:20em;
    height:4.5em;
    margin-top: -5em; //set to a negative number 1/2 of your height
    margin-left: -11em; //set to a negative number 1/2 of your width
}
.login_form{
	padding: 1em;
}
th{
	background-color: #eee;
}
td{
	text-align: center;
	padding: 3px;
}
*/
</style>
</head>
<body>

<h1>New Mexico Data Sharing Project</h1> 
<ul class="nav nav-pills">
	<li class="active"><a href="index.php">Trends</a></li>
	<li> <a href="reporting.php">Reporting</a> </li>
	<li><a href="logs.php">Data Sharing Logs</a></li>
	<li> <a href="logout.php">Logout</a></li>
</ul>
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
</body>
</html>
