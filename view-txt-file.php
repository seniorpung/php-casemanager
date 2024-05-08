<?php
    // Include config file
    require_once "layouts/config.php";
    include 'layouts/head-main.php';
    include 'layouts/session.php'; 
    
    if(isset($_GET['file_name'])){
        if(isset($_GET['is_dir']))  $file   =   ROOT_PATH.'/uploads/tmp-case-attachements/'.$_GET['file_name']; 
        else $file   =   ROOT_PATH.'/uploads/case-attachements/'.$_GET['file_name'];
        $fh     =   fopen($file, 'r');
        while($line=fgets($fh)) {
            echo $line;
            echo '<br/>';
        }
        fclose($fh);    
    }
?>