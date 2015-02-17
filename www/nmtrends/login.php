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
<link rel="stylesheet" type="text/css" href="my.css">
<style type="text/css">
body{
	background-color: #3C4BE6;
	background-image: url('New-Mexico-Flag-icon.png');
	background-repeat: no-repeat;
	background-position: center 10%;
}
input{
	color: gray;
	font-size: 1.8em;
	/* border-radius: 10px; */
}
#login{
   position:fixed;
    top: 50%;
    left: 50%;
    width:20em;
    height:4.5em;
    margin-top: -5em; /*set to a negative number 1/2 of your height*/
    margin-left: -11em; /*set to a negative number 1/2 of your width*/
}
.login_form{
	padding: 1em;
}
h1 {
	color: white;
	font-family: sans-serif;
	text-align: center;
	padding:5em;
}
</style>
</head>
<body>
  <h1>New Mexico Data Sharing Project</h1>
  <div id="login"><div class="login_form">
  <form action="checklogin.php" method="POST">
  <input type="text" name="username" id="username" value=""></div>
  <div class="login_form"><input type="password" name="password" value=""></div>
  <div class="login_form"><input type="submit" value="Log In"></div>
  </form>
  </div>
</body>
</html>

<script type="text/javascript">
document.getElementById('username').focus();
</script>

<?php

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


define('NO_AUTH',true);
require_once('init.php');

$session = new Zend_Auth_Storage_Session(null,'last_uri');
$last_uri = $session->read();

