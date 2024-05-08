<?php
include 'vendor/autoload.php';
include 'db.php';
use Clickatell\ClickatellException;


$clickatell = new \Clickatell\Rest($api_key);



//$integrationName =$_GET['integrationName'];
$replyMessageId =$_GET['replyMessageId'];
$messageId =$_GET['messageId'];
$fromNumber =$_GET['fromNumber'];
$toNumber =$_GET['toNumber'];
$timestamp =$_GET['timestamp'];
$text =strtoupper(trim($_GET['text']));
//$charset =$_GET['charset'];
//$udh =$_GET['udh'];
//$network =$_GET['network'];
//$keyword =$_GET['keyword'];

$query="INSERT INTO `reply_logs` (`replytoId`, `messageId`, `sms_from`, `sms_to`, `text`, `timestamp`, `receivetime`) VALUES ('$replyMessageId', '$messageId', '$fromNumber', '$toNumber', '$text', '$timestamp', current_timestamp())";
mysqlQuery($query);
$log_id=$mysql_Connection->insert_id;

//http://localhost/receiveReply.php?replyMessageId=123&messageId=fe596cebe2e948789e96682bc132bb16&fromNumber=19542908962&toNumber=18643329897&timestamp=ttt&text=Y

$query="select * from notification_log where email_sender='$fromNumber' and smsgateway_info='$messageId'";
$result=mysqlQuery($query);
//echo $query;
if(mysqli_num_rows($result)>0)
{$data=$result->fetch_assoc();
$eid=$data['pc_eid'];
$pid=$data['pid'];
$starttime=$data['pc_startTime'];
$eventdate=$data['pc_eventDate'];
 
// Format Event date and time
$eventdate = date("m-d-Y", strtotime($eventdate));
$starttime = date("h:ia", strtotime($starttime));
 
$query='';
$message='';
if($text=='Y')
{
$query='SMS Confirmed';
$message="Thanks for your reply. Your appointment is confirmed at ".$starttime." on  ".$eventdate.".";
}

else if ($text=='N')
{

$query='x';
$message="Thanks for your reply. Your appointment is cancelled at ".$starttime." on  ".$eventdate.".";
}

else if ($text=='R')
{

$query='Callback';
$message="Thanks for your reply. Your callback is scheduled, we will call you soon";
}

if($query!='' and $message!='')
{
try {
    $result = $clickatell->sendMessage(['to' => [$fromNumber],'from'=>$from_no,'content' => $message]);

$query="update `openemr_postcalendar_events` set pc_apptstatus='$query' where pc_eid='$eid' and pc_pid='$pid'";
//echo $query;
mysqlQuery($query);

} catch (ClickatellException $e) {
    // Any API call error will be thrown and should be handled appropriately.
    // The API does not return error codes, so it's best to rely on error descriptions.
    var_dump($e->getMessage());
}
}

if ($text=='STOP'){
$query="update `patient_data` set hipaa_allowsms='No' where pid='$pid'";
mysqlQuery($query);
}
//send sms code reply code

$query="update `reply_logs` set eid='$eid',pid='$pid' where id='$log_id'";
mysqlQuery($query);

echo $text." REPLY DATA PROCESSED SUCCESSFULLY";
}