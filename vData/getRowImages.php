<?php

//returns array of images in order (already pre-sorted by unix time)
    $directory = 'rowImages/';

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
        for($i = (sizeof($filenames) - 1); $i > 90 ; $i--){
            //delete the current image
            unlink('rowImages/' . $filenames[$i]);    
        }  
    }
    
    echo json_encode($filenames);
?>
