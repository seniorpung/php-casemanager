<?php
// Include config file
require_once "layouts/config.php";

include 'layouts/session.php';

if(isset($_POST['update_is_read']) && $_POST['update_is_read'] == 'disableuser'){
    $id = $_POST['id'];
  
     $sql = "UPDATE notifications SET is_read = 'No' WHERE id=$id";
    if ($link->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }
    exit();
  }
  if(isset($_POST['update_is_read']) && $_POST['update_is_read'] == 'enableuser'){
    $id = $_POST['id'];
  
     $sql = "UPDATE notifications SET is_read = 'Yes' WHERE id=$id";
    if ($link->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }
    exit();
  }

// Close the database connection
mysqli_close($link);
?>
