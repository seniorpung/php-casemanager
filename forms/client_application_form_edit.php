<?php 
//include_once '../layouts/config.php';
if(isset($_GET['task_id'])) {

$sql = "SELECT * FROM `client_application_form` WHERE task_id = ".$_GET['task_id'];

$result = mysqli_query($link, $sql);


$clientAppData = mysqli_fetch_assoc($result);
}