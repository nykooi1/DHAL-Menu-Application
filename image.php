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

function resize_image($file, $w, $h, $crop=FALSE) {
    $imgsize = getimagesize($file);
    
    $mime = $imgsize['mime'];
    
    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
    
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 8;
            break;
    
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
    
        default:
            return false;
            break;
    }
    
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $src = $image_create($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}

//resize and crop image by center
function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];

    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;

        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 8;
            break;

        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 60;
            break;

        default:
            return false;
            break;
    }

    $dst_img = imagecreatetruecolor($max_width, $max_height);
    $src_img = $image_create($source_file);

    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if($width_new > $width){
        //cut point by height
        $h_point = (($height - $height_new) / 2);
        //copy image
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    }else{
        //cut point by width
        $w_point = (($width - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }

    $image($dst_img, $dst_dir, $quality);

    if($dst_img)imagedestroy($dst_img);
    if($src_img)imagedestroy($src_img);
}



//usage example

if($_GET["imageQuery"] == true){
    //returns array of images in order (already pre-sorted by unix time)
    $directory = 'vData/rowImages/';

    if (!is_dir($directory)) {
        exit('Invalid directory path');
    }

    $filenames = array();
    $counter = 0;
    
    $allFiles = scandir($directory);
    
    for($i = (sizeof($allFiles) - 1); $i > 0 ; $i--){
        if ($allFiles[$i] !== '.' && $allFiles[$i] !== '..') {
            $filenames[$counter] = $allFiles[$i];
            $counter++;
        }
    }
    
    //if there is more than 90 images
    if(sizeof($filenames) > 90){
        //get rid of all images that are over 90
        for($i = (sizeof($filenames) - 1); $i > 90; $i--){
            //delete the current image
            unlink('vData/rowImages/' . $filenames[$i]);    
        }  
    }
    
    //generates a guid and stores the guid
    $GUID = getGUID();
    
    $slideDate = $_GET["date"];
    $category = $_GET["category"];
    $categoryNum = "";
    if($category == "Breakfast"){
        $categoryNum = "01";    
    }
    if($category == "Lunch"){
        $categoryNum = "02";
    }
    if($category == "Dinner"){
        $categoryNum = "03";
    }
    
    // Management Console
    $Filename = basename($_FILES["uploadfile"]["name"]);
    $Path = "vData/rowImages/";
    $PathFile = $Path . $_GET["date"] . "-" . $categoryNum . strstr($Filename, ".");
    $imageFilename = $_GET["date"] . "-" . $categoryNum . strstr($Filename, ".");
    if(move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $PathFile)) {
        
        resize_crop_image(900, 300, $PathFile, $PathFile);
        
        $filename = "vData/json/" . $slideDate . ".json";
        
        if(file_exists($filename)){
            //get the object bitch
            $vRetrievedObject = json_decode(file_get_contents($filename));
            //edit the image url of the object
            $vRetrievedObject->$category->image = $imageFilename;
            //lets index.php know the file has changed
            $vRetrievedObject->guid = $GUID;
            //put the object back into the file with the updated image url
            file_put_contents($filename, json_encode($vRetrievedObject));
        } else {
            //if the file does not exist
            $openFile = fopen($filename, 'w') or die('Cannot open file:  ' . $filename);
            
            //create secondary object keys
            $vEmptyObject = new stdClass();
            $vEmptyObject->Breakfast;
            $vEmptyObject->Lunch;
            $vEmptyObject->Dinner;
            
            $vEmptyObject->Breakfast->desc = "";
            $vEmptyObject->Breakfast->color = "#ffffff";
            $vEmptyObject->Breakfast->image = "";
            
            $vEmptyObject->Lunch->desc = "";
            $vEmptyObject->Lunch->color = "#ffffff";
            $vEmptyObject->Lunch->image = "";
            
            $vEmptyObject->Dinner->desc = "";
            $vEmptyObject->Dinner->color = "#ffffff";
            $vEmptyObject->Dinner->image = "";
            
            //Breakfast
            if($category == "Breakfast"){
                $vEmptyObject->Breakfast->image = $imageFilename;  
            }
            //Lunch
            else if($category == "Lunch"){
                $vEmptyObject->Lunch->image = $imageFilename;  
            }
            //Dinner (By default)
            else {
                $vEmptyObject->Dinner->image = $imageFilename;    
            }
            
            $vEmptyObject->guid = $GUID;
            
            $vEmptyObjectEncoded = json_encode($vEmptyObject);
            fwrite($openFile, $vEmptyObjectEncoded);
        }
        
    }   
    else {
        // Upload Failed
    }
} else {
    // Management Console
    if(file_exists("vData/" . $_POST["date"] . ".jpg")){
        unlink("vData/" . $_POST["date"] . ".jpg");
    }
    if(file_exists("vData/" . $_POST["date"] . ".jpeg")){
        unlink("vData/" . $_POST["date"] . ".jpeg");
    }
    if(file_exists("vData/" . $_POST["date"] . ".png")){
        unlink("vData/" . $_POST["date"] . ".png");
    }
    if(file_exists("vData/" . $_POST["date"] . ".gif")){
        unlink("vData/" . $_POST["date"] . ".gif");
    }
    $Filename = basename($_FILES["uploadfile"]["name"]);
    $Path = "vData/";
    $PathFile = $Path . $_POST["date"]. strstr($Filename, ".");
    if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $PathFile)) {
        $image = resize_image($PathFile, 1600, 1200);
        //compress image
        if(strstr($Filename, ".") == ".jpg"){
            imagejpeg($image, $PathFile, 40); 
        }else if(strstr($Filename, ".") == ".jpeg"){
            imagejpeg($image, $PathFile, 40);
        }else if(strstr($Filename, ".") == ".png"){
            imagepng($image, $PathFile, 8); 
        }else if(strstr($Filename, ".") == ".gif"){
            imagegif($image, $PathFile); 
        }
    }
    else {
        // Upload Failed
    }      
}
?>
