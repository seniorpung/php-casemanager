<?php
include 'vendor/autoload.php';
include 'db.php';
use Clickatell\ClickatellException;



$clickatell = new \Clickatell\Rest($api_key);



//$query="select ope.pc_eid,ope.pc_pid,pd.title,pd.fname,pd.lname,pd.mname,pd.phone_cell,pd.email,pd.hipaa_allowsms,pd.hipaa_allowemail, ope.pc_title, ope.pc_hometext,ope.pc_eventDate,ope.pc_endDate, ope.pc_duration,ope.pc_alldayevent,ope.pc_startTime,ope.pc_endTime from openemr_postcalendar_events as ope ,patient_data as pd where ope.pc_pid=pd.pid and pd.hipaa_allowsms='YES' and pd.phone_cell<>'' and ope.pc_sendalertsms='NO' and (cast(concat(pc_eventdate,' ',pc_starttime) as datetime) between now() and (now() + interval 48 hour)) and (pc_apptstatus is null or pc_apptstatus ='-') order by ope.pc_eventDate,ope.pc_endDate,pd.pid";
$query="select rec.r_ID, rec.r_pid, rec.r_facility, rec.r_provider, pd.title,pd.fname,pd.lname,pd.mname,pd.phone_cell,pd.email,pd.hipaa_allowsms,pd.hipaa_allowemail, "
."rec.r_eventDate, rec.r_apptstatus, ((now() + interval 30 day))  as tevent "
."from medex_recalls as rec ,patient_data as pd where rec.r_pid=pd.pid and pd.hipaa_allowsms='YES' and pd.phone_cell<>''  and (rec.r_apptstatus is null or rec.r_apptstatus = '') "
."and (r_eventdate between now() and (now() + interval 30 day)) order by rec.r_eventDate,pd.pid";

$results=mysqlQuery($query);

foreach($results as $row)
{ echo "<br>".$row['r_ID']."<br>";
if($row['mname']!='')
$name=$row['fname']." ".$row['mname']." ".$row['lname'];
else
$name=$row['fname']." ".$row['lname'];
//$starttime=$row['pc_startTime'];
$eventdate=$row['r_eventDate'];
$provider="Dr. Sherry J. Gilmer, OD";
$business="Dr. Sherry J. Gilmer, OD LLC";
$cell=$row['phone_cell'];
$pid=$row['r_pid'];
$eid= $row['r_ID'];
 
 // Clean up and Format cell number
$cell = preg_replace("/[^0-9]/", "", $cell); 
$cell= str_pad($cell, 11, '1', STR_PAD_LEFT); 

// Format Event date and time
$eventdate = date("m-d-Y", strtotime($eventdate));
//$starttime = date("h:ia", strtotime($starttime));
 
$message="Hello ".$name.",  This is your yearly appointment reminder from the office of  ".$provider.". It's been one year since your last eye exam.   

Please call 864-234-8786 to schedule your appointment. Thank you.  

Type STOP to stop SMS message from ".$business;


try {
    $result = $clickatell->sendMessage(['to' => [$cell],'from'=>$from_no,'content' => $message]);


echo $result[0]['apiMessageId'];
      $msg_id=$result[0]['apiMessageId'];

$now=date("Y-m-d h:m:s",time());
$query="INSERT INTO `notification_log` ( `pid`, `pc_eid`, `sms_gateway_type`, `smsgateway_info`, `message`, `email_sender`, `email_subject`, `type`, `patient_info`, `pc_eventDate`, `pc_endDate`, `dSentDateTime`) VALUES ($pid, $eid, 'http', '$msg_id', '$message', '$cell', 'Recall', 'SMS', '$name', '$row[r_eventDate]', '$row[r_eventDate]', '$now')";
//echo $query;
    $results=mysqlQuery($query);

$query="update medex_recalls set r_apptstatus='SMS Sent' where r_ID=".$eid;
//echo $query;
    $results=mysqlQuery($query);

} catch (ClickatellException $e) {
    // Any API call error will be thrown and should be handled appropriately.
    // The API does not return error codes, so it's best to rely on error descriptions.
    var_dump($e->getMessage());
}

}