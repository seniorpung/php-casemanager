<?php
include_once '../layouts/config.php';
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$email = $_POST['email'];
$telephone = $_POST['telephone'];
$request_date = $_POST['request_date'];
$task_id = $_POST['task_id'];
$address1 = $_POST['address1'];
$address2 = $_POST['address2'];
$city = $_POST['city'];
$state = $_POST['state'];
$zipcode = $_POST['zipcode'];
$comments = $_POST['comments'];
$created_by = $_POST['created_by'];

$sql = "SELECT id FROM `client_application_form` WHERE task_id = ".$_POST['task_id'];

$result = mysqli_query($link, $sql);
$row_result = mysqli_num_rows($result);

if ($row_result > 0){
        // Check if record with the same task_id exists
        $sql = "UPDATE `client_application_form` SET fname='$fname', lname='$lname', email='$email', telephone='$telephone', request_date='$request_date', 
        address1='$address1', address2='$address2', city='$city', state='$state', zipcode='$zipcode', comments='$comments', 
        last_updated_by='$created_by', last_updated_datetime=NOW() WHERE task_id='$task_id'";
        $result = mysqli_query($link, $sql);
}
else {
        // Record with task_id does not exist, insert new record
        $sql = "INSERT INTO `travel_approval_form`(fname, lname, email, telephone, request_date, task_id, address1, address2, city, state, 
        zipcode, comments, created_by, created_datetime, last_updated_by, last_updated_datetime)
       VALUES ('$fname', '$lname', '$email', '$telephone', '$request_date', '$task_id', '$address1', '$address2', '$city', 
               '$state', '$zipcode', '$comments', '$created_by', NOW(), '$created_by', NOW())";
        $result = mysqli_query($link, $sql);
}

echo json_encode('success');



?>