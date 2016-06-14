<?php

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


define('NO_AUTH',true);
require_once('init.php');
Zend_Auth::getInstance()->clearIdentity();
$session = new Zend_Auth_Storage_Session(null,'last_uri');
$last_uri = $session->read();
if(strlen($last_uri) < 1)
{
	$last_uri = 'index.php';
}
header("Location: " . $last_uri);
