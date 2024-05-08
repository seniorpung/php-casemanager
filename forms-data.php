<?php
require_once "layouts/config.php";
include 'layouts/session.php';

if(isset($_POST['task_id'])){

$taskId = $_POST['task_id'];

$query1 = "SELECT * FROM `employee_training_registration` WHERE `task_id` = '$taskId'";
$result1 = mysqli_query($link, $query1);

// Check if the data exists
if ($result1 && mysqli_num_rows($result1) > 0) {
    $row1 = mysqli_fetch_assoc($result1);
    $response1 = array(
        'success' => true,
        'employee_fname' => $row1['employee_fname'],
        'employee_lname' => $row1['employee_lname'],
        'employee_email' => $row1['employee_email'],
        'training_company_name' => $row1['training_company_name'],
        'training_start_date' => $row1['training_start_date'],
        'training_cost' => $row1['training_cost'],
        'training_class_name' => $row1['training_class_name'],
        'detail_instructions' => $row1['detail_instructions'],
    );
} else {
    $response1 = array('success' => false);
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response1);

}


?>