#!/usr/bin/php
<?php 

// Start USER SETTINGS section 

define('NMTRENDS_REST_URI','https://server-name/nmtrends/services/trends/v1.1/nm/');
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

$skip_client_id = true;
$skip_close_code = true;
$skip_sclo = true;

// End USER SETTINGS section
include('nmtrends_upload_v1_1.php');
?>
