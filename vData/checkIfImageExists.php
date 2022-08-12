<?php 

$date = file_get_contents('php://input');

/*
//if the image file does not exist, tell javascript to hide the image
if(file_exists($date . ".jpg") == false){
    echo "Image File Doesn't Exist";   
} else {
    echo "Image Exists";
}
*/

if(file_exists($date . ".jpg") == true){
    echo $date . ".jpg";    
} else if(file_exists($date . ".png") == true){
    echo $date . ".png"; 
} else if(file_exists($date . ".gif") == true){
    echo $date . ".gif"; 
} else {
    echo "Image File Doesn't Exist";
}

//else do nothing

?>
