<?php 
require_once "../../layouts/config.php";
if(!isset($_GET['id'])){
    echo "<script> alert('Undefined Schedule ID.'); location.replace('./') </script>";
    //$conn->close();
    exit;
}

$sql = "DELETE FROM schedule_list where id = '{$_GET['id']}'";
$delete = mysqli_query($link, $sql);
if($delete){
    echo "<script> alert('Event has deleted successfully.'); location.replace('../../calendar.php') </script>";
}else{
    echo "<pre>";
    echo "An Error occured.<br>";
    //echo "Error: ".$conn->error."<br>";
    echo "SQL: ".$sql."<br>";
    echo "</pre>";
}
//$conn->close();

?>