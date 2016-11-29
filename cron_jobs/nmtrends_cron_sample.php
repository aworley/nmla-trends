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

$skip_extras = true;  
/* Veteran in household, primary language,
 children in household, persons helped,
 poverty level, and client age at intake. */

// End USER SETTINGS section
include('nmtrends_upload_v1_1.php');
?>
