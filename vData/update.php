<?php

//gets the date posted from the update button
$date = file_get_contents('php://input');

$filename = "json/" . $date.".json";

if(file_exists($filename)){
    $vRetrievedObject = file_get_contents($filename);
    echo $vRetrievedObject;
} else {
    echo "File Doesn't Exist";
}

?>
