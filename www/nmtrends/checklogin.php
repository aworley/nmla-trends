<?php

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


define('NO_AUTH',true);
require_once('init.php');

$username = $password = null;
isset($_POST['username']) ? $username = $_POST['username'] : null;
isset($_POST['password']) ? $password = $_POST['password'] : null;
$result = Zend_Auth::getInstance()->authenticate(new MyAuthAdapter($username,$password));

$session = new Zend_Auth_Storage_Session(null,'last_uri');
$last_uri = $session->read();
if(strlen($last_uri) < 1)
{
	$last_uri = 'index.php';
}

//echo $last_uri;
//echo Zend_Auth::getInstance()->getIdentity();

header("Location: " . $last_uri);
exit();
