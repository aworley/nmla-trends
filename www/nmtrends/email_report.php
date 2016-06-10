<?php 
$to = 'test@abc.org';
$from = $to;

$subject = 'CMS Trends for ' . date('l, F jS Y');

$headers = "From: {$from}\r\n";
$headers .= "Reply-To: {$from}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

ob_start();
include('trend_summary.php');
$message = ob_get_contents();
ob_end_clean();

mail($to, $subject, $message, $headers);

// echo "<pre>$headers</pre>";
// echo $message;