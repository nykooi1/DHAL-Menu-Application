<?php

include "/var/www/dhalkc_com/vfirewall.php";

//gets the json object string from the xhr call and decodes it
$standardObject = json_decode(file_get_contents('php://input'));

//----- Object from modal -----

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

$date = $standardObject->date;
$category = $standardObject->category;
$desc = $standardObject->desc;
$color = $standardObject->color;

//----- Object from file of current date user is on -----

$filename = "json/" . $date .".json";

//if the file does not exist
if(!file_exists($filename)){
    $openFile = fopen($filename, 'w') or die('Cannot open file:  ' . $filename);
    
    $vEmptyObject = new stdClass();
    $vEmptyObject->Breakfast;
    $vEmptyObject->Lunch;
    $vEmptyObject->Dinner;
    
    $vEmptyObject->Breakfast->date = $date;
    $vEmptyObject->Breakfast->desc = "";
    $vEmptyObject->Breakfast->color = "#ffffff";
    $vEmptyObject->Breakfast->image = "";

    $vEmptyObject->Lunch->date = $date;
    $vEmptyObject->Lunch->desc = "";
    $vEmptyObject->Lunch->color = "#ffffff";
    $vEmptyObject->Lunch->image = "";

    $vEmptyObject->Dinner->date = $date;
    $vEmptyObject->Dinner->desc = "";
    $vEmptyObject->Dinner->color = "#ffffff";
    $vEmptyObject->Dinner->image = "";
    
    
    $vEmptyObjectEncoded = json_encode($vEmptyObject);
    
    fwrite($openFile, $vEmptyObjectEncoded);
}

$vFileObject = json_decode(file_get_contents($filename), true);

//overriding existing values

$vFileObject[$category][date] = $date;
$vFileObject[$category][desc] = $desc;
$vFileObject[$category][color] = $color;
$vFileObject["guid"] = $GUID;


$vFileObjectEncoded = json_encode($vFileObject);

print_r($vFileObjectEncoded);

$openFile = fopen($filename, 'w') or die('Cannot open file:  '.$filename);

fwrite($openFile, $vFileObjectEncoded);

?>
