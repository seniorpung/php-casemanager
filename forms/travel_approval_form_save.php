<?php
include_once '../layouts/config.php';
$travel_destination = $_POST['travel_destination'];
$travel_date = $_POST['travel_date'];
$travel_first_name  = $_POST['travel_first_name'];
$travel_last_name = $_POST['travel_last_name'];
$travel_purpose = $_POST['travel_purpose'];
$task_id = $_POST['task_id'];

$sql = "SELECT * FROM `travel_approval_form` WHERE task_id = ".$_POST['task_id'];

$result = mysqli_query($link, $sql);
$row_result = mysqli_num_rows($result);

if ($row_result > 0){
        $sql = "UPDATE `travel_approval_form` SET `travel_destination`='".$travel_destination."',`travel_date`='".$travel_date."',`travel_first_name`='".$travel_first_name."',`travel_last_name`='".$travel_last_name."',`travel_purpose`='".$travel_purpose."' WHERE `task_id`='".$task_id."'";
        $result = mysqli_query($link, $sql);
}
else {
        $sql = "INSERT INTO `travel_approval_form`(`travel_destination`, `travel_date`, `travel_first_name`, `travel_last_name`, `travel_purpose`, `task_id`) 
        VALUES ('".$travel_destination."','".$travel_date."','".$travel_first_name."','".$travel_last_name."','".$travel_purpose."','".$task_id."')";
        $result = mysqli_query($link, $sql);
}

echo json_encode('success');



?>