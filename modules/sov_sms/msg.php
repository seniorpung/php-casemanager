<?php
include 'vendor/autoload.php';
include 'db.php';
use Clickatell\ClickatellException;



$clickatell = new \Clickatell\Rest($api_key);



$query="select * from notifications_view where type = 'sms' and (status is null or status ='') and (assigned_to_allowsms = 'Y') and (assigned_to_cellphone is not null) order by created_date,id";
$results=mysqlQuery($query);

foreach($results as $row)
{ echo "<br>".$row['id']."<br>";
    $name=$row['assigned_person'];
    $starttime=$row['pc_startTime'];
    $created_date=$row['created_date'];
    $created_time=$row['created_date'];
    $email=$row['assigned_to_email'];
    $cell=$row['assigned_to_cellphone'];
    $case_id=$row['case_id'];
    $task_id=$row['task_id'];
    $organization = 'University Of Affiliate Program';
    $id=$row['id'];
    $sla= $row['sla'];
    $type = $row['type'];
    $subject = "iRis BPM - Task #" .$task_id. " Assignment";
 
 // Clean up and Format cell number
$cell = preg_replace("/[^0-9]/", "", $cell); 
$cell= str_pad($cell, 11, '1', STR_PAD_LEFT); 

// Format Event date and time
// Format Event date and time
$created_date = date("m-d-Y", strtotime($created_date));
$created_time = date("h:ia", strtotime($created_time));
 
$message="Hello ".$name.", A new ".$subject." for ".$organization." has been assigned to you at ".$created_time." on ".$created_date.". A reminder, the SLA for your task is ".$sla.".  

Type Y to confirm, type N to cancel, type R to request call back.  

Type STOP to stop SMS message from ".$organization;

try {
    $result = $clickatell->sendMessage(['to' => [$cell],'from'=>$from_no,'content' => $message]);


echo $result[0]['apiMessageId'];
      $msg_id=$result[0]['apiMessageId'];

$now=date("Y-m-d h:m:s",time());
//$query="INSERT INTO `notification_log` ( `pid`, `pc_eid`, `sms_gateway_type`, `smsgateway_info`, `message`, `email_sender`, `email_subject`, `type`, `patient_info`, `pc_eventDate`, `pc_endDate`, `pc_startTime`, `pc_endTime`, `dSentDateTime`) VALUES ($pid, $eid, 'http', '$msg_id', '$message', '$cell', 'Appointment', 'SMS', '$name', '$row[pc_eventDate]', '$row[pc_endDate]', '$row[pc_startTime]', '$row[pc_endTime]','$now')";
$query="INSERT INTO `notification_log` ( `id`, `task_id`,`case_id`, `gateway_type`, `gateway_info`, `message`, `sender`,`subject`, `type`, `SentDateTime`)
		 VALUES ($id, $task_id, $case_id, 'http', '$msg_id', '$message', '$email', $subject, $type,'$now')";
//echo $query;
    $results=mysqlQuery($query);

    $query="update notifications set status='S' where id=".$id;
//echo $query;
    $results=mysqlQuery($query);

} catch (ClickatellException $e) {
    // Any API call error will be thrown and should be handled appropriately.
    // The API does not return error codes, so it's best to rely on error descriptions.
    var_dump($e->getMessage());
}

}