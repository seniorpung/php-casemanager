<?php 
//include_once '../layouts/config.php';
if(isset($_GET['task_id'])) {

$sql = "SELECT * FROM `travel_approval_form` WHERE task_id = ".$_GET['task_id'];

$result = mysqli_query($link, $sql);


$travelData = mysqli_fetch_assoc($result);
}