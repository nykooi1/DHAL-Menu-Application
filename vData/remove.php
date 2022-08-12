<?php 

include "/var/www/dhalkc_com/vfirewall.php";

$subtractDays = '1';
date_default_timezone_set('America/Los_Angeles');
$previousDate =  date('Ymd', strtotime('-' . $subtractDays .'days'));

//removes previous days files
unlink($previousDate . ".jpg"); 
unlink($previousDate . ".png"); 
unlink("json/" . $previousDate . ".json"); 

?>
