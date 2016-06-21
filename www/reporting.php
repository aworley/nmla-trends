<!DOCTYPE html>
<html lang="en">
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
<?php 

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


require_once('init.php');
require_once('pl.php');

function draw_menu($column, $default_value)
{
	$x = mysql_real_escape_string($column);
	$y = mysql_real_escape_string(pl_grab_get($column));
	// AMW It'd be nice to have a check against a whitelist in here.
	
	echo "<label>{$x}<select class=\"form-control input-sm\" name=\"{$x}\">\n";
	
	if($y == '' || strlen($y) < 1)
	{
		$selected = " selected";
	}
	
	else
	{
		$selected = "";
	}
	
	echo "<option value=\"\"{$selected}>Show All</option>/n";
	
	if ($x == 'disabled' || $x == 'age_over_60')
	{
                if($y == 1)
                {
                        $selected = " selected";
                }

                else
                {
                        $selected = "";
                }

                echo "<option value=\"1\"{$selected}>Yes</option>/n";

                if($y == 0 && strlen($y) == 1)
                {
                        $selected = " selected";
                }

                else
                {
                        $selected = "";
                }

                echo "<option value=\"0\"{$selected}>No</option>/n";
	}

	else
	{
	if ($x == 'zip')
	{
		$sql = "SELECT DISTINCT SUBSTRING(zip, 1, 5) AS zip FROM cases GROUP BY zip ORDER BY zip ASC";
	}
	
	else
	{
		$sql = "SELECT {$x} FROM cases GROUP BY {$x} ORDER BY {$x} ASC";
	}
	
	//echo $sql;
	$result = mysql_query($sql);
	
	while($row = mysql_fetch_assoc($result))
	{
		if($y == $row[$x])
		{
			$selected = " selected";
		}
		
		else
		{
			$selected = "";
		}
		
		if (strlen(trim($row[$x])) > 0)
		{
			echo "<option value=\"{$row[$x]}\"{$selected}>{$row[$x]}</option>/n";
		}
	}
	}

	echo "</select></label>\n";
}

function build_where($column)
{
	$x = mysql_real_escape_string($column);
	$y = mysql_real_escape_string(pl_grab_get($column));
	
	if ($y != '')
	{
		return " AND {$x}='{$y}'";
	}
}

$days = pl_grab_get('days', 30);
$selected_30 = '';
$selected_90 = '';
$selected_365 = '';
$selected_1826 = '';

if ($days == '90')
{
	$selected_90 = ' checked';
}

else if($days == '365')
{
	$selected_365 = ' checked';
}

else if($days == '1826')
{
	$selected_1826 = ' checked';
}

else
{
	$selected_30 = ' checked';
}

$start_date = mysql_real_escape_string(pl_grab_get('start_date'));
$end_date = mysql_real_escape_string(pl_grab_get('end_date'));
$sort = pl_grab_get('sort', 'open_date');
$sort_order = pl_grab_get('sort_order', 'desc');

switch ($sort_order)
{
	case 'asc':
	$sql_sort_order = 'ASC';
	break;
	
	default:
	$sql_sort_order = 'DESC';
	break;
}

?>
<div> <!-- style="position:fixed; background-color: white;" -->
<h1>New Mexico Data Sharing Project</h1> 
<ul class="nav nav-pills">
	<li><a href="index.php">Trends</a></li>
	<li class="active"> <a href="#">Reporting</a> </li>
	<li><a href="logs.php">Data Sharing Logs</a></li>
	<li> <a href="logout.php">Logout</a></li>
  <li> <a href="csv.php">Export Entire Database</a></li>
</ul>
<!--
<div>
  <form action="csv.php" method="GET">
    <input type="submit" class="btn" value="Export Entire Database">
  </form>
</div>
-->
<form action="reporting.php" method="GET">
<h2>Reporting</h2>

<p>
Show records from&nbsp;
<?php
	$org = pl_grab_get('org', 'all');
	$sql = "SELECT organization_id, username FROM organizations ORDER BY username ASC";
	$result = mysql_query($sql);
	
	while($row = mysql_fetch_assoc($result))
	{
		if($row['username'] != 'aaron')
		{
			if($row['organization_id'] == $org)
			{
				$checked = ' checked';
			}
			
			else
			{
				$checked = '';
			}
			
			echo "<label class=\"radio-inline\"><input type=\"radio\" name=\"org\" value=\"{$row['organization_id']}\"{$checked}>{$row['username']}</label>\n";
		}
	}
	
	if($org == 'all')
	{
		$checked = ' checked';
	}
	
	else
	{
		$checked = '';
	}
	echo "<label class=\"radio-inline\"><input type=\"radio\" name=\"org\" value=\"all\"{$checked}>All partners</label>"
?>


</p>

<p>
<?php
	draw_menu('gender', '');
	draw_menu('race', '');
	draw_menu('hispanic', '');
	draw_menu('disabled', '');
	draw_menu('age_over_60', '');
	draw_menu('zip', '');
	draw_menu('county', '');
	//draw_menu('court_name', '');
	//draw_menu('judge_name', '');
	draw_menu('problem', '');
	draw_menu('outcome', '');
?>
</p>

<p>
Show records for the last&nbsp;
<label class="radio-inline"><input type="radio" name="days" value="30"<?php echo $selected_30; ?>> 30 days</label>
<label class="radio-inline"><input type="radio" name="days" value="90"<?php echo $selected_90; ?>> 90 days</label>
<label class="radio-inline"><input type="radio" name="days" value="365"<?php echo $selected_365; ?>> 1 year</label>
<label class="radio-inline"><input type="radio" name="days" value="1826"<?php echo $selected_1826; ?>> 5 years</label>
</p>
<p>
Date range (Overrides the previous selection)<br>
<label>Start Date
<input class="form-control input-sm" type="text" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
</label>
<label>End Date
<input class="form-control input-sm" type="text" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
</label>
<label>Opposing Party <input type="text" class="form-control input-sm" name="opposing_party" value="<?php echo htmlentities(pl_grab_get('opposing_party'));?>"></label>
<label>Sort by <select class="form-control input-sm" name="sort" id="sort">
		<option value="username">Organization</option>
		<option value="open_date" selected>Open Date</option>
		<option value="close_date">Close Date</option>
		<option value="gender">Gender</option>
		<option value="race">Race</option>
		<option value="hispanic">Hispanic</option>
		<option value="disabled">Disabled</option>
		<option value="age_over_60">Over 60</option>
		<option value="zip">ZIP Code</option>
		<option value="county">County</option>
		<option value="opposing_party">Opposing Party</option>
		<option value="court_name">Court</option>
		<option value="judge_name">Judge</option>
		<option value="problem">Problem Code</option>
		<option value="outcome">Outcome</option>
		</select></label>
<label>Report order
<select class="form-control input-sm" name="sort_order" id="sort_order">
		<option value="asc">Ascending</option>
		<option value="desc" selected>Descending</option>
		</select></label></p>

<input type="submit" class="btn btn-success" value="Run Report">
</form>
</div>
<div> <!-- style="padding-top: 31.5em;" -->
<h2> Report Results</h2>
<table class="table table-striped">
<tr><th>Organization</th><th>Open&nbsp;Date</th><th>Close&nbsp;Date</th><th>Gender</th><th>Race</th><th>Hispanic</th><th>Disabled</th><th>Older&nbsp;than&nbsp;60&nbsp;yrs</th><th>ZIP&nbsp;Code</th><th>County</th><th>Opposing&nbsp;Party</th><th>Court</th><th>Judge</th><th>LSC&nbsp;Problem&nbsp;Code</th><th>Outcome&nbsp;Code</th></tr>
<?php 

$sql = "SELECT cases.*, username, site_url FROM cases LEFT JOIN organizations using (organization_id) WHERE 1";
$sql .= build_where('gender');
$sql .= build_where('race');
$sql .= build_where('hispanic');
$sql .= build_where('disabled');
$sql .= build_where('age_over_60');
$sql .= build_where('problem');
$sql .= build_where('outcome');
$sql .= build_where('zip');
$sql .= build_where('county');

$org = mysql_escape_string(pl_grab_get('org'));
if ($org && $org !='all')
{
	$sql .= " AND organization_id='{$org}'";
}

$start_date = pl_date_mogrify($start_date);
$end_date = pl_date_mogrify($end_date);

if ($start_date || $end_date)
{
	if ($start_date)
	{
		$sql .= " AND open_date >= '{$start_date}'";
	}

	if ($end_date)
	{
		$sql .= " AND open_date <= '{$end_date}'";
	}
}

else
{
	$days = mysql_escape_string(pl_grab_get('days', 30));
	$cut_off_date = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-$days,   date("Y")));
	$sql .= " AND open_date > '{$cut_off_date}'";
}

$opposing_party = mysql_escape_string(pl_grab_get('opposing_party'));
if ($opposing_party != '')
{
	$sql .= " AND (opp_first_name LIKE '%{$opposing_party}%' OR opp_last_name LIKE '%{$opposing_party}%')";
}

$sql .= " ORDER by {$sort} {$sql_sort_order} LIMIT 10000";
//echo $sql;
$result = mysql_query($sql);
$i = 0;
while ($row = mysql_fetch_assoc($result))
{
	if(strlen($row['site_url']) > 0)
	{
		// MDF 20131031 - Fix for case_id url problem
		//$row['cms_case_id'] = "<a href=\"{$row['site_url']}case.php?case_id={$row['case_id']}\">{$row['username']}</a>";	
		$row['cms_case_id'] = "<a href=\"{$row['site_url']}case.php?case_id={$row['cms_case_id']}\">{$row['username']}</a>";	
	}
	
	else
	{
		$row['cms_case_id'] = $row['username'];
	}
	
	$row['open_date'] = pl_date_unmogrify($row['open_date']);
	$row['close_date'] = pl_date_unmogrify($row['close_date']);
	/*
	$row['opp_first_name'] .= "&nbsp;";
	$row['opp_first_name'] .= $row['opp_last_name'];

	// 2013-10-31 AMW - Temp change; opp party names are redacted globally.
	$row['opp_first_name'] = "[Redacted]";
	*/
	unset($row['opp_first_name']);
	unset($row['opp_last_name']);

	unset($row['username']);
	unset($row['site_url']);
	unset($row['case_id']);
	unset($row['organization_id']);
	unset($row['cms_case_created']);
	unset($row['created']);
	
	echo "<tr>";
	
	foreach($row as $u => $v)
	{
		echo "<td nowrap>{$v}</td>";
	}

	echo "</tr>";
	$i++;
}

?>
</table>
</div>
<?php echo "<p>" . $i . " records found.</p>"; ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

<script>
$('[name=sort]').val('<?php echo $sort; ?>');
$('[name=sort_order]').val('<?php echo $sort_order; ?>');
</script>
<!--
<script>
$(function() {
$( "#start_date" ).datepicker();
$( "#end_date" ).datepicker();
});
</script>
-->
</body>
</html>
