<?php
    require_once "layouts/config.php";
    include 'layouts/head-main.php'; 
    include 'layouts/session.php'; 
    include 'modules/api_services/calling_services.php';

    $created_by         =   $_SESSION["id"];
    $created_datetime   =   date('Y-m-d H:i:s');
    $current_date       =   date('Y-m-d');

    global $link;
    include 'class.crud.php';

    $crudObj = new CRUD('cases', 'case_id');
    $crudObj->mysqli = $link;

    $crudObjAtt = new CRUD('contacts', 'contact_id');
    $crudObjAtt->mysqli = $link;

    $LoggedUserData = $crudObj->FindRow('users', array('useremail', 'CONCAT(fname,\' \', mname,\' \', lname) AS name'), array('id='.$created_by));

    //Processing form data when form is submitted
    if(isset($_POST['AddCaseBtn'])) {
        $whereConditionTaskConfig   =   array();
        $whereConditionTaskConfig[] =   'case_type_id='.$_POST['case_type_id'];
        
        $TaskConfiguration = $crudObj->FindRow('task_configuration', array(), $whereConditionTaskConfig, array(array('task_configuration_id', 'ASC'))); 

        $data = array();
        
        $case_number    =   1001;
        $CaseNumberMax  =   $crudObj->FindRow('cases', array('MAX(case_number) as max_case_number'), array());
        if(isset($CaseNumberMax['max_case_number']) && $CaseNumberMax['max_case_number']>0)
            $case_number = $CaseNumberMax['max_case_number'] + 1;
        if(!empty($_POST['case_initial_file_date']))
            $_POST['case_initial_file_date'] = date('Y-m-d', strtotime($_POST['case_initial_file_date']));
       
       //Remove unused fields//     
       // if(!empty($_POST['date_to_nurse']))
       //     $_POST['date_to_nurse'] = date('Y-m-d', strtotime($_POST['date_to_nurse']));
            
       // if(!empty($_POST['packet_sent_date']))
       //     $_POST['packet_sent_date'] = date('Y-m-d', strtotime($_POST['packet_sent_date']));
       // Field remove end //     
        
        $saveData = array();
        $saveData['case_id']                  =   '';
        $saveData['case_number']              =   $case_number;
        $saveData['case_type_id']             =   $_POST['case_type_id'];
        $saveData['case_status_id']           =   $_POST['case_status_id'];
        $saveData['case_initial_file_date']   =   $_POST['case_initial_file_date'];
        //$saveData['type_of_referral']         =   $_POST['type_of_referral'];
        //$saveData['CLTC_number']              =   $_POST['CLTC_number'];
        //$saveData['packet_number']            =   $_POST['packet_number'];
        //$saveData['packet_sent_date']         =   $_POST['packet_sent_date'];
        $saveData['case_manager_name']        =   $_POST['case_manager_name'];
        //$saveData['nurse_id_name']            =   $_POST['nurse_id_name'];
        //$saveData['date_to_nurse']            =   $_POST['date_to_nurse'];
        $saveData['case_subject']             =   $_POST['case_subject'];
        $saveData['descriptions']             =   $_POST['descriptions'];
        $saveData['contact_id']             =   $_POST['contact_id'];
        //$saveData['file_type_id']             =   $_POST['file_type_id'];
        $saveData['created_by']               =   $created_by;
        $saveData['created_datetime']         =   $created_datetime;
        $inserted = $crudObj->save($saveData);
        if($inserted>0){
            if($_POST['contact_id']==''){
                $saveDataAtt = array();
                $saveDataAtt['contact_id ']           =   '';
                $saveDataAtt['contact_last_4_ssn']                 =   $_POST['contact_last_4_ssn'];
                $saveDataAtt['contact_fname']           =   $_POST['contact_fname'];
                $saveDataAtt['contact_lname']            =   $_POST['contact_lname'];
                $saveDataAtt['contact_address1']            =   $_POST['contact_address1'];
                $saveDataAtt['contact_address2']            =   $_POST['contact_address2'];
                $saveDataAtt['contact_city']                =   $_POST['contact_city'];
                $saveDataAtt['contact_state']               =   $_POST['contact_state'];
                $saveDataAtt['contact_zip']                 =   $_POST['contact_zip'];
                $saveDataAtt['contact_phone1']              =   $_POST['contact_phone1'];
                $saveDataAtt['contact_phone2']              =   $_POST['contact_phone2'];
                $contact_id = $crudObjAtt->save($saveDataAtt);
                $crudObjAtt->run_sql_query('UPDATE cases SET contact_id='.$contact_id. ' WHERE case_id='.$inserted);
            }

            if(isset($_POST['attachments']) && count($_POST['attachments'])>0){
                $crudAttachObj = new CRUD('case_attachments', 'case_attachment_id');
                $crudAttachObj->mysqli = $link;
                foreach($_POST['attachments'] as $key => $attachment){                    
                    $sourcePath =   ROOT_PATH.'/uploads/tmp-case-attachements/'.$attachment['file_name'];
                    $targetPath =   ROOT_PATH.'/uploads/case-attachements/'.$attachment['file_name'];
                    copy($sourcePath, $targetPath);
                    
                    unlink($sourcePath);

                    $saveAttachData = array();
                    $saveAttachData['case_attachment_id']   =   '';
                    $saveAttachData['original_filename']    =   $attachment['original_filename'];
                    $saveAttachData['file_name']            =   $attachment['file_name'];
                    $saveAttachData['file_ext']             =   $attachment['file_ext'];
                    $saveAttachData['attachment_type_id']   =   $attachment['attachment_type_id'];
                    $saveAttachData['case_id']              =   $inserted;
                    $saveAttachData['created_by']           =   $attachment['created_by'];
                    $saveAttachData['created_datetime']     =   $attachment['created_datetime'];
                    $crudAttachObj->save($saveAttachData);
                }  
            }

            $cols = array();
            $cols[] = '*';
            $cols[] = '(SELECT case_status_name FROM case_status WHERE cases.case_status_id = case_status.case_status_id) AS case_status';
            $cols[] = '(SELECT case_name FROM case_type_definition WHERE cases.case_type_id = case_type_definition.case_type_id) AS case_type';
            $record = $crudObj->FindRow('cases', $cols, array('case_id='.$inserted));

            $nameArr = array();
           
/* -- Disable Email notifications. Moved to module sov_email
            $mailTrail  =   '<br/><br/><br/><strong>Thanks & Regards,</strong><br/>';
            $subject    =   'A New Case added as Case Number: '.$record['case_number'];
            $message    =   'Dear '.$LoggedUserData['name'].',<br/><br/>'. $subject.'<br/><br/>Please check below Case details:<br/>';
            $message   .=   '<table width="100%" cellspacing="3" cellpadding="3">';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Case Number</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$record['case_number'].'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Initial Complaint Date</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.date('m/d/Y h:i A', strtotime($record['case_initial_file_date'])).'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Case Status</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$record['case_status'].'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Case Type</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$record['case_type'].'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Requester Name</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.implode(' ', $nameArr).'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Contact Email</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$record['contact_email'].'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Contact Phone</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$record['contactnumber'].'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td colspan="2" width="100%" style="background-color:#EFEFEF;"><strong>Case Description</strong></td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td colspan="2" width="100%" style="background-color:#EFEFEF;">'.nl2br($record['descriptions']).'</td>';
                $message   .=   '</tr>';
            $message   .=   '</table>';

            $to         =  $LoggedUserData['useremail'];
            $subject    =  $subject;
            $headers    =  "From: " . strip_tags(MAILER_ID) . "\r\n";
            $headers   .=  "Reply-To: " . strip_tags(MAILER_ID) . "\r\n";
            $headers   .=  "MIME-Version: 1.0\r\n";
            $headers   .=  "Content-Type: text/html; charset=UTF-8\r\n";
            $message    =  $message.$mailTrail;
            $mailAlertMessage = '';
            if(mail($to, $subject, $message, $headers)) $mailAlertMessage = 'Details successfully sent to email address on '.$to;

*/
            $crudTaskObj = new CRUD('tasks', 'task_id');
            $crudTaskObj->mysqli = $link;            
            $saveTaskData                           =   array();
            $saveTaskData['task_id']                =   '';
            $saveTaskData['case_id']                =   $inserted;
            $saveTaskData['task_cofiguration_id']   =   $TaskConfiguration['task_configuration_id'];
            $saveTaskData['task_status_id']         =   1;
            // echo "<script>alert('".$TaskConfiguration['task_assigned_to']."');</script>";
            if($TaskConfiguration['task_assigned_to_group']==null)
                $saveTaskData['assigned_to']            =   $TaskConfiguration['task_assigned_to'];
            else
                $saveTaskData['assigned_to_group']            =   $TaskConfiguration['task_assigned_to_group'];
            // echo "<script>alert('".$saveTaskData['assigned_to_group']."');</script>";
            $saveTaskData['form_template_id']       =   $TaskConfiguration['task_form_template_id'];
            $saveTaskData['assigned_by']            =   $created_by;
            $saveTaskData['assigned_datetime']      =   $created_datetime;
            $saveTaskData['created_by']             =   $created_by;
            $saveTaskData['created_datetime']       =   $created_datetime;
            $saveTaskData['isEndTask']              =   $TaskConfiguration['isEndTask'];
            $saveTaskData['is_decision_task']       =   $TaskConfiguration['is_decision_task'];
            $saveTaskData['isCalendarTask']         =   $TaskConfiguration['isCalendarTask'];
            $task_id = $crudTaskObj->save($saveTaskData);

            // 11/26/2023- Zhiling: save calendar list for this task if the task is calendarTask
            if ($TaskConfiguration['isCalendarTask'] && !is_null($_POST['calendar_start_datetime'])){
                $crudCalendarObj = new CRUD('schedule_list', 'id');
                $crudCalendarObj->mysqli = $link;            
                $saveCalendarData                   =   array();
                $saveCalendarData['id']             =   '';
                
                if ($_POST['calendar_title']==''){
                    $saveCalendarData['title']      =   $_POST['contact_lname']. ' Appointment';
                }else{
                    $saveCalendarData['title']      =   $_POST['calendar_title'];
                }
                
                $saveCalendarData['description']    =   $_POST['calendar_description'];
                $saveCalendarData['start_datetime'] =   $_POST['calendar_start_datetime'];
                $saveCalendarData['end_datetime']   =   $_POST['calendar_end_datetime'];
                $saveCalendarData['task_id']        =   $task_id;
                $saveCalendarData['case_id']        =   $inserted;
                $calendar_id = $crudCalendarObj->save($saveCalendarData);
            }
            /* --Disable Email feature --
            $AssignedUserData = $crudObj->FindRow('users', array('useremail', 'CONCAT(fname,\' \', mname,\' \', lname) AS name'), array('id='.$TaskConfiguration['task_assigned_to']));
            $mailTrail  =   '<br/><br/><br/><strong>Thanks & Regards,</strong><br/>';
            $subject    =   $TaskConfiguration['task_name'].' Assigned';
            $message    =   'Dear '.$AssignedUserData['name'].',<br/><br/>'. $subject.'<br/><br/>Please check below Case details:<br/>';
            $message   .=   '<table width="100%" cellspacing="3" cellpadding="3">';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Case Number</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$record['case_number'].'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Task Name</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$TaskConfiguration['task_name'].'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Assigned Date</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.date('m/d/Y h:i A', strtotime($created_datetime)).'</td>';
                $message   .=   '</tr>';
                $message   .=   '<tr>';
                    $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Assigned By</strong></td>';
                    $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$LoggedUserData['name'].'</td>';
                $message   .=   '</tr>';
            $message   .=   '</table>';

            $to         =  $AssignedUserData['useremail'];
            $subject    =  $subject;
            $headers    =  "From: " . strip_tags(MAILER_ID) . "\r\n";
            $headers   .=  "Reply-To: " . strip_tags(MAILER_ID) . "\r\n";
            $headers   .=  "MIME-Version: 1.0\r\n";
            $headers   .=  "Content-Type: text/html; charset=UTF-8\r\n";
            $message    =  $message.$mailTrail;
            mail($to, $subject, $message, $headers);
            */
            $crudNotificationObj = new CRUD('notifications', 'id');
            $crudNotificationObj->mysqli = $link;
            $saveNotificationData               =   array();
            $saveNotificationData['title']      =   'New Case Number: '.$case_number. ' Added';
            $saveNotificationData['description']=   '';
            $saveNotificationData['case_id']    =   $inserted;
            $saveNotificationData['user_id']    =   $created_by;
            $saveNotificationData['created_at'] =   $created_datetime;
            $crudNotificationObj->save($saveNotificationData);

            $saveNotificationData               =   array();
            $saveNotificationData['title']      =   $TaskConfiguration['task_name'];
            $saveNotificationData['description']=   '';
            $saveNotificationData['task_id']    =   $task_id;
            $saveNotificationData['case_id']    =   $inserted;
            $saveNotificationData['user_id']    =   $TaskConfiguration['task_assigned_to'];
            $saveNotificationData['created_at'] =   $created_datetime;
            $crudNotificationObj->save($saveNotificationData);

            $data = [
                'status'                =>  true,
                'message'               =>  'Saved Successfully'
            ];
               
        }
        else{
            $data = [
                'status' => false,
                'message' => 'Something went wrong. Please try again later.'
            ];
        }
        
        if(isset($data['status']) && $data['status'] == true) {
            ?><script>
                window.location.href='<?php echo ROOT_URL; ?>/cases.php';
            </script><?php
            exit;
        }
    }

    $attachement_type = $crudObj->FindAll('attachement_type', array(), array(), 0, 0, array(array('attachment_type_id', 'ASC')));
    
    ?><head>
        <title>Case Manager - Add Case</title>
        <?php include 'layouts/head.php'; ?>
        <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="assets/css/style.css">
        <?php include 'layouts/head-style.php'; ?>
    </head>
    <?php  include 'layouts/body.php'; ?>
        <div id="layout-wrapper">
            <?php include 'layouts/menu.php'; ?>
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Add New Case</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                            <li class="breadcrumb-item active">Add New Case</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php  include 'layouts/alert-messages.php'; ?>
                        <div class="auth-page">
                            <div class="container-fluid p-0">
                                <div class="row g-0">
                                    <div class="col-xxl-12 col-lg-12 col-md-12">
                                        <form class="needs-validation custom-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data"> 
                                            <div class="card">
                                                <div class="card-body"> 
                                            
                                                  <div class="row mb-2">
                                                    <label for="case_type_id" class="col-sm-2 col-form-label">Case Type:<strong class="text-danger"> *</strong></label>
                                                    <div class="col-sm-2">
                                                      <select name="case_type_id" id="case_type_id" class="form-select form-control-sm" required onchange="getFirstTaskIsCalendar(this)">
                                                                <option value="">Please Select</option><?php 
                                                                $selectquery = 'SELECT * FROM case_type_definition WHERE effective_begin_date<=\''.$current_date.'\' AND effective_end_date>=\''.$current_date.'\'';
                                                                $qn= mysqli_query($link, $selectquery);
                                                                $nums = mysqli_num_rows($qn);
                                                                while($res = mysqli_fetch_array($qn)){
                                                                    ?><option value="<?php echo $res['case_type_id'] ?>"><?php echo $res['case_name'] ?></option><?php 
                                                                } 
                                                            ?></select>
                                                    </div>
                                                    <label for="case_initial_file_date" class="offset-1 col-sm-3 col-form-label"><b>Initial Requested Date:<strong class="text-danger"> *</strong></b></label>
                                                    <div class="col-sm-2">
                                                        <input type="date" class="form-control form-control-sm" id="case_initial_file_date" placeholder="" name="case_initial_file_date" value="" required>
                                                    </div>
                                                </div>
                                                    
 
                                                <div class="row mb-2">
                                                    <label for="contact_ssn" class="col-sm-2 col-form-label">SSN:<strong class="text-danger"> *</strong></label>
                                                    <div class="col-sm-2">
                                                      <input type="hidden" class="form-control form-control-sm" id="contact_id" placeholder="" name="contact_id" value="" >
                                                      <input type="hidden" class="form-control form-control-sm" id="case_status_id" placeholder="" name="case_status_id" value="4" >
                                                      
                                                      <input type="text" class="form-control form-control-sm" id="contact_last_4_ssn" placeholder="" name="contact_last_4_ssn" value="" required onblur="SearchSSNBtnTrigger();">
                                                    </div>
                                                    <div class="col-sm-1">
                                                       <input type="button" id="SearchSSNBtn" value="Search" class="btn btn-sm btn-info"/>
                                                    </div> 
                                                    <label for="case_subject" class=" col-sm-3 col-form-label">Subject:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="case_subject" placeholder="" name="case_subject" value="" required>
                                                    </div>
                                                    
                                                </div>
                                                 <div class="row mb-2">
                                                    <label for="contact_fname" class="col-sm-2 col-form-label">First Name:<strong class="text-danger"> *</strong></label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="contact_fname" placeholder="" name="contact_fname" value="" required>
                                                    </div>
                                                    <label for="contact_lname" class="offset-2 col-sm-2 col-form-label">Last Name:<strong class="text-danger"> *</strong></label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="contact_lname" placeholder="" name="contact_lname" value="" required>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_address1" class="col-sm-2 col-form-label"> Address 1:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="contact_address1" placeholder="" name="contact_address1" value="" >
                                                    </div>
                                                    
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_address2" class="col-sm-2 col-form-label"> Address 2:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="contact_address2" placeholder="" name="contact_address2" value="" >
                                                    </div>
                                                    <label for="case_manager" class="offset-2 col-sm-2 col-form-label">Case Manager:</label>
                                                    <div class="col-sm-2">
                                                        <input type="text" class="form-control form-control-sm" id="case_manager_name" placeholder="" name="case_manager_name" value="" >
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <label for="city" class="col-sm-2 col-form-label">City:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="contact_city" placeholder="" name="contact_city" value="" >
                                                    </div>
                                            
                                                    <label for="state" class="col-sm-2 col-form-label">State:</label>
                                                    <div class="col-sm-1">
                                                        <input type="text" class="form-control form-control-sm" id="contact_state" placeholder="" name="contact_state" value="" >
                                                    </div>
                                                    <label for="zip" class="col-sm-1 col-form-label">ZIP:</label>
                                                    <div class="col-sm-1">
                                                        <input type="text" class="form-control form-control-sm" maxlength="10" id="contact_zip" placeholder="" name="contact_zip" value="" >
                                                    </div>
                                                    
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_phone1" class="col-sm-2 col-form-label">Primary Phone:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="contact_phone1" placeholder="" maxlength="15" name="contact_phone1" value="" >
                                                    </div>
                                                    <label for="contact_phone2" class="col-sm-2 col-form-label">Alternate Phone:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="contact_phone2" placeholder="" maxlength="15" name="contact_phone2" value="" >
                                                    </div>
                                                    
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="descriptions" class="col-sm-2 col-form-label">Case Description:</label>
                                                    <div class="col-sm-10">
                                                      <textarea class="form-control form-control-sm" name="descriptions" maxlength="255" id="descriptions" rows="3" placeholder="" value=""></textarea>
                                                    </div>
                                                    
                                                </div>
                                                    
                                                    <hr>
                                                    
                                                    <div class="row mt-2">
                                                        <div class="col-xxl-10 col-lg-10 col-md-10">
                                                            <label for="case_type_id" class="form-label h5">Upload Attachements</label>   
                                                        </div>
                                                        <div class="col-xxl-2 col-lg-2 col-md-2 text-right">
                                                            <button class="btn btn-primary btn-sm w-100 waves-effect waves-light" type="button" onclick="return CaseAttachementUploadModal();">Add Attachement</button> 
                                                        </div>
                                                        <div class="col-xxl-12 col-lg-12 col-md-12 mt-2">                                                
                                                            <table class="table table-bordered dt-responsive nowrap w-100 mb-2">
                                                                <thead>
                                                                    <tr>         
                                                                        <th width="25%">Attachement Type</th>
                                                                        <th width="35%">File Name</th>
                                                                        <th width="20%">Created by</th>
                                                                        <th width="20%">Created On</th>                                        
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="AttachementData"></tbody>
                                                            </table>
                                                        </div>          
                                                    </div>
                                                </div>

                                                <!-- start calendar-->
                                                <div class="card-body" id="embedCalendar" style="display: none;">
                                                    <div class="card-header bg-gradient bg-primary text-light">
                                                    <h5 class="card-title">Schedule Appointment</h5>
                                                    </div>           
                                                    <div class="container-fluid">
                                                        
                                                            <input type="hidden" name="id" value="">
                                                            <div class="form-group mb-2">
                                                                <label for="title" class="control-label">Title</label>
                                                                <input type="text" class="form-control form-control-sm rounded-0" name="calendar_title" id="calendar_title" value='' >
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label for="description" class="control-label">Description</label>
                                                                <textarea rows="3" class="form-control form-control-sm rounded-0" name="calendar_description" id="calendar_description" value='' ></textarea>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label for="start_datetime" class="control-label">Start</label>
                                                                <input type="datetime-local" class="form-control form-control-sm rounded-0" name="calendar_start_datetime" id="calendar_start_datetime" value='' >
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label for="end_datetime" class="control-label">End</label>
                                                                <input type="datetime-local" class="form-control form-control-sm rounded-0" name="calendar_end_datetime" id="calendar_end_datetime" value='' >
                                                            </div>
                                                       
                                                    </div>
                                                </div>
                                                <!-- end calendar-->
                                                
                                                <div class="card-footer">
                                                    <button class="btn btn-primary w-25 waves-effect waves-light" name="AddCaseBtn" id="AddCaseBtn" type="submit" disabled="disabled" onclick="return valid_form();">Add New Case</button>    
                                                </div>
                                            </div>
                                        </form>
                                        
                                    </div>
                                </div>
                                <!-- end row -->
                           
                            </div>
                            <!-- end container fluid -->
                        </div>                
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include 'layouts/footer.php'; ?>
            </div>
            <!-- end main content-->
        </div>
        <div class="modal" id="CaseAttachedDocViewModal" tabindex="-1" role="dialog" aria-labelledby="CaseAttachedDocViewModal" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">View Case Attached Document</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="CaseAttachedDocView"></div>
                </div>
            </div>  
        </div>
        <div class="modal" id="CaseAttachementUploadModal" tabindex="-1" role="dialog" aria-labelledby="CaseAttachementUploadModal" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form class="needs-validation custom-form" id="AddAttachementForm" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h4 class="modal-title">Add Attachement</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mt-2 mb-4">
                                <div class="col-xxl-12 col-lg-12 col-md-12">
                                    <label for="person_first_name" class="form-label">Attachement Type <strong class="text-danger">*</strong></label>
                                    <select class="form-control attachment_type_id" name="attachment_type_id" id="attachment_type_id" required>
                                        <option value="">Select Attachement Type</option><?php 
                                    if(isset($attachement_type) && count($attachement_type)>0){
                                        foreach($attachement_type as $res){ 
                                            ?><option value="<?php echo $res['attachment_type_id']; ?>"><?php echo ucwords(strtolower($res['type_name'])); ?></option><?php
                                        }
                                    }
                                    ?></select>
                                </div>
                            </div>
                            <div class="row mt-4 mb-2">
                                <div class="col-xxl-12 col-lg-12 col-md-12">
                                    <label for="person_first_name" class="form-label">Upload Attachement <strong class="text-danger">*</strong></label> 
                                    <input type="file" required class="form-control attachement_file" name="attachement_file" id="attachement_file" accept=".jpg,.png,.bmp,.jpeg,.docx,.doc,.pdf,.pptx,.ppt" onchange="validate_fileupload(this);"/>   
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <img src="<?php echo ROOT_URL.'/assets/images/loading.gif'; ?>" width="25px;" class="d-none loading-image"/>
                            <strong class="text-success alert-section"></strong>
                            <button class="btn btn-primary w-50 waves-effect waves-light" id="AddAttachementBtn" type="submit">Add Attachement</button>    
                        </div>
                    </form>
                </div>
            </div>  
        </div>

        <div class="modal" id="AttSSNModal" tabindex="-1" role="dialog" aria-labelledby="AttSSNModal" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">    
                    <div class="modal-header">
                        <h4 class="modal-title">Results</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="AttSSNModalContent"></div>
                </div>
            </div>  
        </div>
        <!-- END layout-wrapper -->
        <!-- Right Sidebar -->
        <?php include 'layouts/right-sidebar.php'; ?>
        <!-- /Right-bar -->
        <!-- JAVASCRIPT -->
        <?php include 'layouts/vendor-scripts.php'; ?>
        <!-- apexcharts -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
        <!-- Plugins js-->
        <script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
        <!-- dashboard init -->
        <script src="assets/js/pages/dashboard.init.js"></script>
        <!-- App js -->
        <script src="assets/js/app.js"></script>
        <!-- Session timeout js -->
<script src="assets/libs/@curiosityx/bootstrap-session-timeout/index.js"></script>

<!-- Session timeout init js -->
<script src="assets/js/pages/session-timeout.init.js"></script>
        <script>
            var ROOT_URL = '<?php echo ROOT_URL; ?>';
            function CaseAttachedDocViewModal(file_name, file_ext){
                var imageFileExt = ["jpg", "jpeg", "png", "gif", "bmp"];
                var docFileExt = ["docx", "pdf","pptx", "doc", "ppt"];

                var htmlData = '<div class="row">';
                    htmlData += '<div class="col-xxl-12 col-lg-12 col-md-12 text-center">';
                        if(imageFileExt.includes(file_ext))
                            htmlData += '<img src="'+ROOT_URL+'/uploads/tmp-case-attachements/'+file_name+'" class="img-fluid border rounded"/>';
                        else {
                            if(docFileExt.includes(file_ext)){
                                htmlData += '<iframe src="https://docs.google.com/viewer?url='+ROOT_URL+'/uploads/tmp-case-attachements/'+file_name+'&embedded=true" frameborder="0" width="95%" height="400" style="border:1px solid black;"></iframe>';
                            }
                            else if(file_ext == 'pdf')
                                htmlData += '<iframe src="'+ROOT_URL+'/uploads/tmp-case-attachements/'+file_name+'" width="95%" height="400" style="border:1px solid black;" frameborder="0"></iframe>';
                            else if(file_ext == 'txt')
                                htmlData += '<iframe src="'+ROOT_URL+'/view-txt-file.php?file_name='+file_name+'&is_dir=tmp" width="95%" height="400" style="border:1px solid black;" frameborder="0"></iframe>';
                            else if(file_ext == 'tiff' || file_ext == 'tif')
                                htmlData += '<iframe src="https://docs.google.com/viewer?url='+ROOT_URL+'/uploads/tmp-case-attachements/'+file_name+'&embedded=true" frameborder="0" width="95%" height="400" style="border:1px solid black;"></iframe>';
                        }
                    htmlData += '</div>';
                htmlData += '</div>';   
                $('#CaseAttachedDocViewModal').modal('show');
                $('#CaseAttachedDocView').html(htmlData);
            }
            function validate_fileupload(input_element) {
                var fileName = input_element.value;
                var allowed_extensions = new Array("jpg", "png", "gif", "jpeg", "bmp", "pdf", "pptx", "ppt", "docx", "doc", "txt", "xls", "xlsx", "tiff", "tif");
                var file_extension = fileName.split('.').pop(); 
                for(var i = 0; i < allowed_extensions.length; i++){
                    if(allowed_extensions[i]==file_extension) {
                        valid = true; // valid file extension
                        return;
                    }
                }
                alert("Invalid file");
                valid = false;
            }
            function valid_form() {
                return valid;
            }
            function CaseAttachementUploadModal(){
                $('#CaseAttachementUploadModal').modal('show');    
            }
            var attachement_counter = 0;
            $('#AddAttachementBtn').on('click', function(e) {
                e.preventDefault();
                var file_data = $('#attachement_file').prop('files')[0]; 
                var attachment_type_id = $('#attachment_type_id').val();  
                
                var form_data = new FormData();                  
                form_data.append('attachement_file', file_data);
                form_data.append('attachment_type_id', attachment_type_id); 
                form_data.append('action', 'AddAttachement');                           
                $.ajax({
                    url         :   'action.php',
                    dataType    :   'json',
                    cache       :   false,
                    contentType :   false,
                    processData :   false,
                    data        :   form_data,                         
                    type        :   'post',
                    beforeSend: function (xhr){ 
                        $('.loading-image').removeClass('d-none');
                        $('#AddAttachementBtn').attr('disabled', 'disabled');
                    },
                    success     :   function(data){
                        if(data['status']==true){
                            if(data['attachments']){
                                var htmlData = '<tr>';
                                    htmlData += '<td>';
                                        htmlData += data['attachments']['attachment_type'];
                                    htmlData += '</td>';
                                    htmlData += '<td>';
                                        htmlData += '<i style="cursor:pointer; margin-right:15px;" onclick="CaseAttachedDocViewModal(\''+data['attachments']['file_name']+'\', \''+data['attachments']['file_ext']+'\');" class="mdi mdi-eye text-primary"></i>';
                                        htmlData += '<strong>';
                                            htmlData += data['attachments']['original_filename'];
                                        htmlData += '</strong>';
                                    htmlData += '</td>';
                                    htmlData += '<td>';
                                        htmlData += data['attachments']['users_data'];
                                    htmlData += '</td>';
                                    htmlData += '<td>';
                                        htmlData +=  data['attachments']['created_datetime_frm'];
                                        htmlData +=  '<input type="hidden" name="attachments['+attachement_counter+'][original_filename]" value="'+data['attachments']['original_filename']+'">';
                                        htmlData +=  '<input type="hidden" name="attachments['+attachement_counter+'][file_name]" value="'+data['attachments']['file_name']+'">';
                                        htmlData +=  '<input type="hidden" name="attachments['+attachement_counter+'][file_ext]" value="'+data['attachments']['file_ext']+'">';
                                        htmlData +=  '<input type="hidden" name="attachments['+attachement_counter+'][attachment_type_id]" value="'+data['attachments']['attachment_type_id']+'">';
                                        htmlData +=  '<input type="hidden" name="attachments['+attachement_counter+'][created_by]" value="'+data['attachments']['created_by']+'">';
                                        htmlData +=  '<input type="hidden" name="attachments['+attachement_counter+'][created_datetime]" value="'+data['attachments']['created_datetime']+'">';
                                        
                                    htmlData += '</td>';
                                htmlData += '</tr>';
                                $('#AttachementData').append(htmlData);
                                //$('.alert-section').html('File Successfully Added!!');
                                attachement_counter =  parseInt(attachement_counter) + 1;
                            }
                            $('.attachment_type_id').val('');
                            $('.attachement_file').val('');
                            $('#CaseAttachementUploadModal').modal('hide'); 
                        }  
                        else{
                            $('.attachment_type_id').addClass('border-danger');
                            $('.attachement_file').addClass('border-danger');    
                        }
                        $('.loading-image').addClass('d-none');
                        $('#AddAttachementBtn').removeAttr('disabled');
                    }
                });
                return false;
            });

            $('#SearchSSNBtn').click(function(){
                if($('#contact_last_4_ssn').val()==''){
                    $('#AttSSNModal').modal('show');
                    $('.modal-dialog').removeClass('modal-xl');
                    $('.modal-dialog').addClass('modal-md');
                    $('#AttSSNModalContent').addClass('p-4');
                    $('#AttSSNModalContent').addClass('text-center');
                    $('#AttSSNModalContent').html('<strong style="font-size:22px;" class="text-danger">Please Enter SSN</strong>');
                    $('#contact_last_4_ssn').addClass('border-danger');
                    $('#contact_last_4_ssn').focus();
                }
                else{
                    var contact_last_4_ssn = $('#contact_last_4_ssn').val();
                    $.ajax({
                        url         :   'action.php',
                        dataType    :   'json',
                        data        :   {
                            'contact_last_4_ssn' : contact_last_4_ssn,
                            'action' : 'FindAttSSN',
                        },                         
                        type        :   'post',
                        beforeSend: function (xhr){ 

                        },
                        success     :   function(data){
                            if(data['status']==true){
                                $('#AttSSNModal').modal('show');
                                
                                $('.modal-dialog').removeClass('modal-md');
                                $('.modal-dialog').addClass('modal-xl');
                                
                                $('#AttSSNModalContent').removeClass('p-4');
                                $('#AttSSNModalContent').removeClass('text-center');
                                $('#AttSSNModalContent').html(data['htmlData']);
                                $('#AddCaseBtn').removeAttr('disabled');
                            }  
                            else{
                                if(data['case_exists']==true){
                                    $('#AddCaseBtn').attr('disabled', 'disabled');
                                }
                                else  $('#AddCaseBtn').removeAttr('disabled');
                                
                                $('#AttSSNModal').modal('show');
                                
                                $('.modal-dialog').removeClass('modal-xl');
                                $('.modal-dialog').addClass('modal-md');
                                
                                $('#AttSSNModalContent').addClass('p-4');
                                $('#AttSSNModalContent').addClass('text-center');
                                $('#AttSSNModalContent').html(data['htmlData']);
                            }
                        }
                    });             
                }
            });
            
            function SearchSSNBtnTrigger(){
                $('#SearchSSNBtn').trigger('click');
            }

            function getSelectedContactRecords(contact_id){
                $.ajax({
                    url         :   'action.php',
                    dataType    :   'json',
                    data        :   {
                        'contact_id' : contact_id,
                        'action' : 'getSelectedContactRecords',
                    },                         
                    type        :   'post',
                    beforeSend: function (xhr){ },
                    success     :   function(data){    
                        if(data['status']==true){
                            $('#contact_id').val(data['data']['contact_id']);
                            $('#contact_last_4_ssn').val(data['data']['contact_last_4_ssn']);
                            $('#contact_fname').val(data['data']['contact_fname']);
                            $('#contact_lname').val(data['data']['contact_lname']);
                            $('#contact_address1').val(data['data']['contact_address1']);
                            $('#contact_address2').val(data['data']['contact_address2']);
                            $('#contact_city').val(data['data']['contact_city']);
                            $('#contact_state').val(data['data']['contact_state']);
                            $('#contact_zip').val(data['data']['contact_zip']);
                            $('#contact_phone1').val(data['data']['contact_phone1']);
                            $('#contact_phone2').val(data['data']['contact_phone2']);
                        }  
                        else{
                            $('#contact_id').val('');
                            $('#contact_last_4_ssn').val('');
                            $('#contact_fname').val('');
                            $('#contact_lname').val('');
                            $('#contact_address1').val('');
                            $('#contact_address2').val('');
                            $('#contact_city').val('');
                            $('#contact_state').val('');
                            $('#contact_zip').val('');
                            $('#contact_phone1').val('');
                            $('#contact_phone2').val('');
                        }
                        $('#AttSSNModal').modal('hide');
                    }
                });
            }

            function getFirstTaskIsCalendar(selectCaseTypeObj){

                $.ajax({
                    url         :   'action.php',
                    dataType    :   'json',
                    data        :   {
                        'selected_case_type_id' : selectCaseTypeObj.value,
                        'action' : 'getFirstTaskIsCalendar',
                    },                         
                    type        :   'post',
                    beforeSend: function (xhr){ },
                    success     :   function(data){  
                        
                        if(data['isCalendar_status']==true){
                            
                            document.getElementById("embedCalendar").style.display="";
                        }  
                        else{
                            document.getElementById("embedCalendar").style.display="none";
                        }
                       
                    }
                });
            }

            function toggleCalendarDiv(selectCaseTypeObj){
                
                if (selectCaseTypeObj.value.length>0){
                    var xmlhttpRequest = new XMLHttpRequest();
                    xmlhttpRequest.open("GET", "modules/api_services/calling_services.php?methodName=checkIfFirstTaskIsCalendar&caseTypeId=" +selectCaseTypeObj.value, true);
                     
                    xmlhttpRequest.onreadystatechange = function() {
                        
                        if (xmlhttpRequest.readyState == 4 && xmlhttpRequest.status == 200) {
                            
                            
                            callback(xmlhttpRequest.responseText);
                            var data = xmlhttpRequest.responseText;
                            
                            if (data==='1'){
                               
                                document.getElementById("embedCalendar").style.display="";
                            }else{
                                

                                document.getElementById("embedCalendar").style.display="none";
                            }
                        }
                     };
                     xmlhttpRequest.send(null);
                     //return xmlhttpRequest.response;
                     //xmlhttpRequest.send("methodName=checkIfFirstTaskIsCalendar&caseTypeId="+selectCaseTypeObj.value);
                    
                }else{
                    document.getElementById("embedCalendar").style.display="none";
                }
                
                /* this wouldn't work 
                if (selectCaseTypeObj.value.length>0){
                    $inputCaseTypeId=selectCaseTypeObj.value;
                    alert("calling service with caseTypeId="+$inputCaseTypeId);
                    
                    $res=call_service_check_first_task_calendar ($inputCaseTypeId);
                    alert("res="+$res);
                    if ($res==1){
                        document.getElementById("embedCalendar").style.display="";
                    }else{
                        document.getElementById("embedCalendar").style.display="none";
                    }
                  
                }else{
                    document.getElementById("embedCalendar").style.display="none";
                }
                 */    
                 
              
                
            }
        </script>
    </body>
</html>