<?php 
require_once('init.php');

$to = 'test@abc.org';
$from = $to;

$subject = 'CMS Trends for ' . date('l, F jS Y');

$headers = "From: {$from}\r\n";
$headers .= "Reply-To: {$from}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

ob_start();
echo "<html>\n<head>\n";
include('html_header.php');
echo "</head><body>";

if (true)
{
  echo "This email alert software is currently in <strong>testing mode.</strong>\n";
  echo "It may send out multiple, duplicate emails on the same day, \n";
  echo "and the threshold for triggering an alert has be significantly lowered \n";
  echo "to aid the testing process.<hr>\n";
}

include('trend_summary.php');
echo trend_summary($base_url, 'email');
echo "</body></html>";
$message = ob_get_contents();
ob_end_clean();

if (defined('STDIN'))
{
  mail($to, $subject, $message, $headers);
}

else 
{
  echo $message;
}

exit();