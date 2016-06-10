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
<?php include('trend_summary.php'); ?>
</body>
</html>
