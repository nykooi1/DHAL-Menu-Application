<?php

include "/var/www/dhalkc_com/vfirewall.php";

//function - generates a guid
function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
}

//generates a guid and stores the guid
$GUID = getGUID();

$date = $_POST["date"];
$category = $_POST["category"];

//create filename
$filename = "json/" . $date . ".json";

if(file_exists($filename)){
    //get the contents of json and decode it
    $vRetrievedObject = json_decode(file_get_contents($filename));
    //delete the current image
    unlink("rowImages/" . $vRetrievedObject->$category->image);
    
    //override the image url and set it equal to blank
    $vRetrievedObject->$category->image = "";
    
    //generate new GUID so that during vFillOutTable it will know to REFRESH
    $vRetrievedObject->guid = $GUID;
    
    //overrite the current json with the new JSON (with the deleted image url)
    $openFile = fopen($filename, 'w') or die('Cannot open file:  ' . $filename);
    fwrite($openFile, json_encode($vRetrievedObject));
}
