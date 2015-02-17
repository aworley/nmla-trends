#!/usr/bin/php
<?php 

// Start USER SETTINGS section 

define('NMTRENDS_USERNAME','trends_server_username');
define('NMTRENDS_PASSWORD','trends_server_password');

define('DB_HOST','localhost');
define('DB_NAME','cms_database');
define('DB_USER','mysql_username');
define('DB_PASS','mysql_password');

$skip_opposing = false;
$skip_race = false;

// End USER SETTINGS section
include('nmtrends_upload_v1_1.php');
?>
