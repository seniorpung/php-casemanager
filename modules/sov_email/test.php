<?php
require 'PHPMailer/PHPMailerAutoload.php';

$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.ionos.com';                       // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'qa1@sovratec.com';                 // SMTP username
$mail->Password = 'QAMar2022!';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$eventdate = '2022-03-30';
$starttime = '15:00:00';
$eventdate = date("m-d-Y", strtotime($eventdate));
$starttime = date("h:i:sa", strtotime($starttime));

$mail->setFrom('qa1@sovratec.com', 'Sovratec QA');
$mail->addAddress('legrand@sovratec.com','LeGrand Decius');     // Add a recipient
//$mail->addAddress('ellen@example.com');               // Name is optional
$mail->addReplyTo('info@example.com', 'Information');
// $mail->addCC('cc@example.com');
// $mail->addBCC('bcc@example.com');

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Test Email';
$message = "Dear [CUSTOMER-NAME],<br><br>This is a friendly reminder confirming your appointment with [PROVIDER-NAME] at ".$starttime." on ".$eventdate.".<br><br>A reminder to wear a mask, and a 24 hour notice to cancel is required or there will be a $45.00 charge to reschedule.
.<br><br>If you have any questions or you need to reschedule, please call our office at [BUSINESS-PHONE].<br><br> We look forward to seeing you on [DATE-TIME]. Have a wonderful day!<br><br>Warm regards,<br><br>
Dr. Sherry Gilemer";
$mail->Body    = $message;
// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}