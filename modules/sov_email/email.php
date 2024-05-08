<?php
require 'PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;

include 'db.php';

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.ionos.com';                       // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'irisBPM@sovratec.com';             // SMTP username
$mail->Password = 'iBPMAug2023!';                      // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to
$mail->setFrom('iRisBPM@sovratec.com', 'iRis BPM');
$mail->addReplyTo('iRisBPM@sovratec.com', 'iRis BPM');
$mail->isHTML(true);


$query="select * from notifications_view where type = 'email' and (status is null or status ='')  and (contact_email is not null) order by created_date,id";
$results=mysqlQuery($query);

foreach($results as $row)
{ echo "<br>".$row['id']."<br>";
if($row['customer_mname']!='')
$name=$row['customer_fname']." ".$row['customer_mname']." ".$row['customer_lname'];
else
$name=$row['customer_fname']." ".$row['customer_lname'];
$starttime=$row['pc_startTime'];
$created_date=$row['created_date'];
$created_time=$row['created_date'];
$email=$row['contact_email'];
$case_id=$row['case_id'];
$task_id=$row['task_id'];
$Organization = 'University Of Affiliate Program';
$id=$row['id'];
$sla= $row['sla'];
$type = $row['type'];
$subject = "iRis BPM - Task" .$task_id. " Assignment";

// Format Event date and time
$created_date = date("m-d-Y", strtotime($created_date));
$created_time = date("h:ia", strtotime($created_time));
 
// $message="Hello ".$name.",  This is to remind you that you have an appointment with ".$provider." at ".$starttime." on ".$eventdate.". ";

$message="Hello ".$name.",<br><br>  A new iRis BPM task for ".$Organization." has been received at ".$created_time." on ".$created_date.".<br><br>A reminder, the SLA for your task is ".$sla.".<br><br>If you have any questions or you need to assistant, please contact your supervisor.<br><br> Have a wonderful day!";
 
 $mail->Body    = $message;
 $mail->addAddress($email, $name);     //Add a recipient
 $mail->addReplyTo('iRisBPM@sovratec.com', $Organization);
 $mail->Subject = "iRis BPM - Task" .$task_id. " Assignment";

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
	// $msg_id=$result[0]['apiMessageId'];
	$msg_id="";
	$now=date("Y-m-d h:m:s",time());
		$query="INSERT INTO `notification_log` ( `id`, `task_id`,`case_id`, `gateway_type`, `gateway_info`, `message`, `sender`,`subject`, `type`, `SentDateTime`)
		 VALUES ($id, $task_id, $case_id, 'http', '$msg_id', '$message', '$email', $subject, $type,'$now')";
	//echo $query;
    $results=mysqlQuery($query);

	$query="update notifications set status='S' where id=".$id;
	//echo $query;
    $results=mysqlQuery($query);
}
}
