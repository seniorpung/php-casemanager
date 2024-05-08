<?php
include 'vendor/autoload.php';
include 'db.php';
use Clickatell\ClickatellException;



$clickatell = new \Clickatell\Rest($api_key);



$query="select ope.pc_eid,ope.pc_pid,pd.title,pd.fname,pd.lname,pd.mname,pd.phone_cell,pd.email,pd.hipaa_allowsms,pd.hipaa_allowemail, ope.pc_title, ope.pc_hometext,ope.pc_eventDate,ope.pc_endDate, ope.pc_duration,ope.pc_alldayevent,ope.pc_startTime,ope.pc_endTime from openemr_postcalendar_events as ope ,patient_data as pd where ope.pc_pid=pd.pid and pd.hipaa_allowsms='YES' and pd.phone_cell<>'' and ope.pc_sendalertsms='NO' and (cast(concat(pc_eventdate,' ',pc_starttime) as datetime) between now() and (now() + interval 24 hour)) and (pc_apptstatus ='SMS Confirmed') order by ope.pc_eventDate,ope.pc_endDate,pd.pid";
    $results=mysqlQuery($query);

foreach($results as $row)
{ echo "<br>".$row['pc_eid']."<br>";
if($row['mname']!='')
$name=$row['fname']." ".$row['mname']." ".$row['lname'];
else
$name=$row['fname']." ".$row['lname'];
$starttime=$row['pc_startTime'];
$eventdate=$row['pc_eventDate'];
$provider="Dr. Sherry J. Gilmer, OD";
$business="Dr. Sherry J. Gilmer, OD LLC";
$cell=$row['phone_cell'];
$pid=$row['pc_pid'];
$eid= $row['pc_eid'];
$latefee="$45.00";
 
 // Clean up and Format cell number
$cell = preg_replace("/[^0-9]/", "", $cell); 
$cell= str_pad($cell, 11, '1', STR_PAD_LEFT); 

// Format Event date and time
$eventdate = date("m-d-Y", strtotime($eventdate));
$starttime = date("h:ia", strtotime($starttime));
 
$message="Hello ".$name.", This is a friendly reminder confirming your appointment with ".$provider." at ".$starttime." on ".$eventdate.". Warm regards, Dr. Sherry J. Gilmer, OD";
 

try {
    $result = $clickatell->sendMessage(['to' => [$cell],'from'=>$from_no,'content' => $message]);


echo $result[0]['apiMessageId'];
      $msg_id=$result[0]['apiMessageId'];

$now=date("Y-m-d h:m:s",time());
$query="INSERT INTO `notification_log` ( `pid`, `pc_eid`, `sms_gateway_type`, `smsgateway_info`, `message`, `email_sender`, `email_subject`, `type`, `patient_info`, `pc_eventDate`, `pc_endDate`, `pc_startTime`, `pc_endTime`, `dSentDateTime`) VALUES ($pid, $eid, 'http', '$msg_id', '$message', '$cell', 'Appointment', 'SMS', '$name', '$row[pc_eventDate]', '$row[pc_endDate]', '$row[pc_startTime]', '$row[pc_endTime]','$now')";
//echo $query;
    $results=mysqlQuery($query);

$query="update openemr_postcalendar_events set pc_apptstatus='SMS Confirmed*' where pc_eid=".$eid;
//echo $query;
    $results=mysqlQuery($query);

} catch (ClickatellException $e) {
    // Any API call error will be thrown and should be handled appropriately.
    // The API does not return error codes, so it's best to rely on error descriptions.
    var_dump($e->getMessage());
}

}