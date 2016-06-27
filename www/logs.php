<?php 

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/

require_once('init.php');
?>
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
</head>
<body>
<h1>New Mexico Data Sharing Project</h1> 
<ul class="nav nav-pills">
	<li><a href="index.php">Top Trends</a></li>
  <li><a href="index.php?all=1">Problem Code Trends</a>
	<li> <a href="reporting.php">Reporting</a> </li>
	<li class="active"><a href="logs.php">Data Sharing Logs</a></li>
	<li> <a href="logout.php">Logout</a></li>
</ul>
<h2>Data Merge Activity Logs</h2>
<table class="table table-striped">
<tr><th>Organization</th><th>Date</th><th>Time</th><th>Description</th><th>Case Record Count</th><th>Success Code</th></tr>

<?php 
require_once('init.php');
require_once('pl.php');

// AMW 2013-11-23 - The old query method no longer works with the new v1.1
// uploads.  Added a "logs" table so we can continue to track upload activity.
//$sql = "SELECT username, created, UNIX_TIMESTAMP(created) AS time_of_day, 'Appending new case records.' AS description, count(*) AS case_count, 'Y' AS success_code FROM cases LEFT JOIN organizations using (organization_id) GROUP BY organization_id, created ORDER by created DESC, username ASC LIMIT 100";
$sql = "SELECT username, start_datetime, end_datetime, 'Uploading case records.' AS description, case_record_count, success_code 
		FROM logs LEFT JOIN organizations using (organization_id) 
		ORDER by start_datetime DESC, username ASC LIMIT 10000";
//echo $sql;
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result))
{
	//$row['created'] = pl_date_unmogrify($row['created']);
	//$row['time_of_day'] = date("H:i:s", $row['time_of_day']);
	
	echo "<tr>";
	
	foreach($row as $u => $v)
	{
		echo "<td nowrap>{$v}</td>";
	}

	echo "</tr>";
}

?>

</table>

</body>
</html>
