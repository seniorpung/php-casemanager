<?php
    // Include config file
    require_once "layouts/config.php";
    include 'layouts/head-main.php';
    include 'layouts/session.php';
    
    include 'ecrypt.php';
    $fxd = 1; //flag to display task update feature (fxd=1 will display section; fxd=0 will hide section)
    $ref_name = basename($_SERVER['HTTP_REFERER']);
    global $link;
    include 'class.crud.php';
        
    $efname="";
    $elname="";
    $eemail="";
    $tcompany="";
    $tdate=date("Y/m/d");
    $tcost="";
    $tclassname="";
    $detailinst="";
    $calendar_msg="";
    
   if ($ref_name == 'my-tasks.php' || $ref_name == 'manage-tasks.php') {
    $fxd = 0;
   }
    
   //echo "<script>alert('$ref_name');</script>";
   //echo "<script>alert('$fxd');</script>";
    
    if(isset($_POST['AddAttachementBtn'])) {
        $crudAttachObj = new CRUD('case_attachments', 'case_attachment_id');
        $crudAttachObj->mysqli = $link;
        $created_by = $_SESSION["id"];
        $created_datetime = date('Y-m-d H:i:s');

        if(isset($_FILES['attachement_file']['tmp_name']) && !empty($_FILES['attachement_file']['tmp_name'])){
            $attachement_type =  $crudAttachObj->FindRow('attachement_type', array('type_name'), array('attachment_type_id='.$_POST['attachment_type_id']));

            $nameArr = explode('.', $_FILES['attachement_file']['name']);
            $ext = end($nameArr);

            $image      =   strtolower(str_replace(' ', '_', $attachement_type['type_name']).'_'.date('YmdHis').'.'.$ext);                
            $sourcePath =   $_FILES['attachement_file']['tmp_name'];
            $targetPath =   ROOT_PATH.'/uploads/case-attachements/'.$image;
            move_uploaded_file($sourcePath, $targetPath);

            $saveAttachData = array();
            $saveAttachData['case_attachment_id']   =   '';
            $saveAttachData['original_filename']    =   $_FILES['attachement_file']['name'];
            $saveAttachData['file_name']            =   $image;
            $saveAttachData['file_ext']             =   strtolower($ext);
            $saveAttachData['attachment_type_id']   =   $_POST['attachment_type_id'];
            $saveAttachData['case_id']              =   $_POST['case_id'];
            $saveAttachData['created_by']           =   $created_by;
            $saveAttachData['created_datetime']     =   $created_datetime;
            $inserted = $crudAttachObj->save($saveAttachData);
            if($inserted>0){
                $data = [
                    'status'    =>  true,
                    'message'   =>  'Attachement Successfully Added'
                ];
            }
            else{
                $data = [
                    'status'    =>  false,
                    'message'   =>  'Something went wrong. Please try again later.'
                ];
            }
        }
    }
    if(isset($_POST['SaveTaskDetailsBtn'])) {
        $data = array();
        
        $created_by         =   $_SESSION["id"];
        $created_datetime   =   date('Y-m-d H:i:s');

        $crudTaskObj = new CRUD('tasks', 'task_id');
        $crudTaskObj->mysqli = $link;
        // echo "<script>alert('".$_GET['task_id']."');</script>";
        $form_data = (array)json_decode($_POST['flow_content']);
        // echo "<script>alert('adf');</script>";
        $form_pres = 1;
        foreach($form_data as $k=>$val)
        {
            if($k=="efname" && $val=="")
            {
                $form_pres=0;
                break;
            }
            // echo "<script>alert('".($k=="efname" && $val=="")."');</script>";
        }
        //echo "<script>alert('$form_pres');</script>";

        $casesData = $crudTaskObj->FindRow('cases_view', array(), array('case_id='.$_POST['case_id']));

        $LoggedUserData = $crudTaskObj->FindRow('users', array('useremail', 'CONCAT(fname,\' \', mname,\' \', lname) AS name'), array('id='.$created_by));

        $updateData     =   array();
        $updateData[]   =   'Comments=\''.$_POST['Comments'].'\'';
        if(isset($_POST['task_status_id']))  
            $updateData[]   =   'task_status_id=\''.$_POST['task_status_id'].'\'';
            $crudTaskObj->run_sql_query('UPDATE cases SET case_status_id=\'1\' WHERE case_id='.$_POST['case_id']); 

        if(isset($_POST['task_status_id']) && $_POST['task_status_id']==2) {
            $updateData[]   =   'completed_by=\''.$created_by.'\'';
            $updateData[]   =   'completed_datetime=\''.$created_datetime.'\'';
        }

        if(isset($_POST['task_status_id']) && $_POST['task_status_id']==1) {
            $updateData[]   =   'assigned_to=\''.$_POST['assigned_to'].'\'';
        }
        $updateData[]   =   'last_updated_by=\''.$created_by.'\'';
        $updateData[]   =   'last_updated_datetime=\''.$created_datetime.'\'';
        
        $crudTaskObj->run_sql_query('UPDATE tasks SET '.implode(', ', $updateData).' WHERE task_id='.$_POST['task_id']);

        $TaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['task_configuration_id']));
        if(isset($TaskConfiguration['isEndTask']) && $TaskConfiguration['isEndTask']==1){
            $updateData     =   array();
            //$updateData[]   =   'task_status_id=\'2\'';
            $updateData[]   =   'task_status_id='.$_POST['task_status_id'];
            
            if ($_POST['task_status_id']==2){
                $updateData[]   =   'completed_by=\''.$created_by.'\'';
                $updateData[]   =   'completed_datetime=\''.$created_datetime.'\'';
            }
            
            $crudTaskObj->run_sql_query('UPDATE tasks SET '.implode(', ', $updateData).' WHERE task_id='.$_POST['task_id']);

            $crudNotificationObj = new CRUD('notifications', 'id');
            $crudNotificationObj->mysqli = $link;

            $saveNotificationData               =   array();
            $saveNotificationData['title']      =   $TaskConfiguration['task_name'];
            $saveNotificationData['description']=   '';
            $saveNotificationData['task_id']    =   $_POST['task_id'];
            $saveNotificationData['case_id']    =   $_POST['case_id'];
            $saveNotificationData['user_id']    =   $created_by;
            $saveNotificationData['created_at'] =   $created_datetime;
            $crudNotificationObj->save($saveNotificationData);
            //zhiling (08/31/2023): update case status to 2 (completed) only if the task status is updated to "completed"
            if ($_POST['task_status_id']==2){
                $crudTaskObj->run_sql_query('UPDATE cases SET case_status_id=\'2\' WHERE case_id='.$_POST['case_id']); 
            }
           
            $data = [
                'status'  =>  true,
                'message' =>  'Task Updated Successfully'
            ];
        }
        else{ // if it's not end task
            //Zhiling: should we get rid of below line, because we already done fetch the same data a few lines above for  $TaskConfiguration
            $TaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['task_configuration_id']), array(array('task_configuration_id', 'ASC')));
            
            //zhiling added for future usage
            $current_task_config_id=$_POST['task_configuration_id'];
            $current_task_configuration=$TaskConfiguration;
            
            
            $NextTaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id>'.$_POST['task_configuration_id'], 'case_type_id='.$casesData['case_type_id']), array(array('task_configuration_id', 'ASC')));
            $_POST['task_configuration_id'] = $NextTaskConfiguration['task_configuration_id'];


            //echo '<pre>';    print_r($TaskConfiguration); print_r($NextTaskConfiguration); print_r($_POST);   echo '</pre>'; exit;
            
            //Zhiling: 11/13/2023 - I change to >=0 to force all logic going to this block.  Because the count can be greater than 0, we still need to create task
            
            if($crudTaskObj->FindRecordsCount('tasks', array('case_id='.$_POST['case_id'], 'task_cofiguration_id='.$_POST['task_configuration_id']))>=0){
                $TaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['task_configuration_id']));
                $cases = $crudTaskObj->FindRow('cases', array(), array('case_id='.$_POST['case_id']));
                

                if(isset($_POST['task_status_id']) && $_POST['task_status_id']==2) {
                    $saveTaskData                           =   array();
                    $saveTaskData['task_id']                =   '';
                    $saveTaskData['case_id']                =   $_POST['case_id'];
                    $saveTaskData['task_cofiguration_id']   =   $_POST['task_configuration_id'];
                    $saveTaskData['task_status_id']         =   1;
                    // echo "<script>alert('".$TaskConfiguration['task_configuration_id']."a".$TaskConfiguration['task_assigned_to_group']."');</script>";
                    if($TaskConfiguration['task_assigned_to_group']==null)
                        $saveTaskData['assigned_to']        =   $TaskConfiguration['task_assigned_to'];
                    else
                        $saveTaskData['assigned_to_group']        =   $TaskConfiguration['task_assigned_to_group'];
                    $saveTaskData['form_template_id']        =   $TaskConfiguration['task_form_template_id'];
                    $saveTaskData['assigned_by']            =   $created_by;
                    $saveTaskData['created_by']             =   $created_by;
                    $saveTaskData['created_datetime']       =   $created_datetime;
                    $saveTaskData['assigned_datetime']      =   $created_datetime;
                    $saveTaskData['Comments']               =   '';        
                    $saveTaskData['isEndTask']              =   $TaskConfiguration['isEndTask'];
                    $saveTaskData['is_decision_task']       =   $TaskConfiguration['is_decision_task'];
                    $saveTaskData['parent_task_id']         =   $_POST['task_id'];
                    $saveTaskData['isCalendarTask']       =   $TaskConfiguration['isCalendarTask'];
                    $inserted = $crudTaskObj->save($saveTaskData);
    
                    if($inserted>0){
                        /*--Comment Message (Moved to Notification service)
                        $AssignedUserData = $crudTaskObj->FindRow('users', array('useremail', 'CONCAT(fname,\' \', mname,\' \', lname) AS name'), array('id='.$TaskConfiguration['task_assigned_to']));
                        $mailTrail  =   '<br/><br/><br/><strong>Thanks & Regards,</strong><br/>';
                        $subject    =   $TaskConfiguration['task_name'].' Assigned';
                        
                        $message    =   'Dear '.$AssignedUserData['name'].',<br/><br/>'. $subject.'<br/><br/>Please check below Case details:<br/>';
                        $message   .=   '<table width="100%" cellspacing="3" cellpadding="3">';
                            $message   .=   '<tr>';
                                $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Case Number</strong></td>';
                                $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$cases['case_number'].'</td>';
                            $message   .=   '</tr>';
                            $message   .=   '<tr>';
                                $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Task Name</strong></td>';
                                $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$TaskConfiguration['task_name'].'</td>';
                            $message   .=   '</tr>';
                            $message   .=   '<tr>';
                                $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Assigned Date</strong></td>';
                                $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.date('d-m-Y h:i A', strtotime($created_datetime)).'</td>';
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
                        $saveNotificationData['title']      =   $TaskConfiguration['task_name'];
                        $saveNotificationData['description']=   '';
                        $saveNotificationData['task_id']    =   $_POST['task_id'];
                        $saveNotificationData['case_id']    =   $_POST['case_id'];
                        $saveNotificationData['user_id']    =   $created_by;
                        $saveNotificationData['created_at'] =   $created_datetime;
                        $crudNotificationObj->save($saveNotificationData);
    
                        $data = [
                            'status'  =>  true,
                            'message' =>  'Task Updated Successfully.'
                        ];
                    }
                    else{
                        $data = [
                            'status'    =>  false,
                            'message'   =>  'Something went wrong. Please try again later. '.implode(', ', $crudTaskObj->error)
                        ];
                    }
                }
                else{
                   
                    if(isset($_POST['decision_task_id']) && $_POST['decision_task_id']>0){

                        //Zhiling echo here
                        //echo "<script>alert('updated task status to 2, decision task configuration is ');</script>";
                        //echo "<script>alert('".$_POST['decision_task_id']."');</script>";
                        
                        $DecisionTaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['decision_task_id']));
                        
                        $updateData     =   array();
                        $updateData[]   =   'task_status_id=\'2\'';
                        $updateData[]   =   'completed_by=\''.$created_by.'\'';
                        $updateData[]   =   'completed_datetime=\''.$created_datetime.'\'';
                        $crudTaskObj->run_sql_query('UPDATE tasks SET '.implode(', ', $updateData).' WHERE task_id='.$_POST['task_id']);
                        
                        if(isset($DecisionTaskConfiguration['isEndTask']) && $DecisionTaskConfiguration['isEndTask']==1){
                            
                            //11/15/2023 -zhiling added below lines to insert the actual "decision text" task. we still have to create the actual decision text first, and end the case
                            $saveTaskData                           =   array();
                            $saveTaskData['task_id']                =   '';
                            $saveTaskData['case_id']                =   $_POST['case_id'];
                            $saveTaskData['task_cofiguration_id']   =   $DecisionTaskConfiguration['task_configuration_id'];
                            $saveTaskData['task_status_id']         =   2;
                            if($DecisionTaskConfiguration['task_assigned_to_group']==null)
                                $saveTaskData['assigned_to']            =   $DecisionTaskConfiguration['task_assigned_to'];
                            else
                                $saveTaskData['assigned_to_group']      =   $DecisionTaskConfiguration['task_assigned_to_group'];
                            $saveTaskData['form_template_id']       =   $DecisionTaskConfiguration['task_form_template_id'];
                            $saveTaskData['assigned_by']            =   $created_by;
                            $saveTaskData['created_by']             =   $created_by;
                            $saveTaskData['completed_by']           =   $created_by;
                            $saveTaskData['created_datetime']       =   $created_datetime;
                            $saveTaskData['assigned_datetime']      =   $created_datetime;
                            $saveTaskData['Comments']               =   $_POST['Comments']; 
                            $saveTaskData['completed_datetime']     =   $created_datetime;
                            $saveTaskData['isEndTask']              =   $DecisionTaskConfiguration['isEndTask'];
                            $saveTaskData['is_decision_task']       =   $DecisionTaskConfiguration['is_decision_task'];
                            $saveTaskData['isCalendarTask']         =   $DecisionTaskConfiguration['isCalendarTask'];
                            $saveTaskData['parent_task_id']         =   $_POST['task_id'];
                            $insertedDecisionTextTaskId = $crudTaskObj->save($saveTaskData);
                            // 11/15/2023 - end insertion of "decision text" task


                            $crudNotificationObj = new CRUD('notifications', 'id');
                            $crudNotificationObj->mysqli = $link;
    
                            $saveNotificationData               =   array();
                            $saveNotificationData['title']      =   $DecisionTaskConfiguration['task_name'];
                            $saveNotificationData['description']=   '';
                            $saveNotificationData['task_id']    =   $_POST['task_id'];
                            $saveNotificationData['case_id']    =   $_POST['case_id'];
                            $saveNotificationData['user_id']    =   $created_by;
                            $saveNotificationData['created_at'] =   $created_datetime;
                            $crudNotificationObj->save($saveNotificationData);
    
                            $crudTaskObj->run_sql_query('UPDATE cases SET case_status_id=\'2\' WHERE case_id='.$_POST['case_id']);                    
    
                            $data = [
                                'status'  =>  true,
                                'message' =>  'Task Closed Successfully'
                            ];
                        }
                        else{
                            //Zhiling: 11/15/2023 - comment out below database call, because we have the same line right above If clause
                            //$DecisionTaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['decision_task_id']));
                            $saveTaskData                           =   array();
                            $saveTaskData['task_id']                =   '';
                            $saveTaskData['case_id']                =   $_POST['case_id'];
                            $saveTaskData['task_cofiguration_id']   =   $DecisionTaskConfiguration['task_configuration_id'];
                            $saveTaskData['task_status_id']         =   2;
                            if($DecisionTaskConfiguration['task_assigned_to_group']==null)
                                $saveTaskData['assigned_to']            =   $DecisionTaskConfiguration['task_assigned_to'];
                            else
                                $saveTaskData['assigned_to_group']      =   $DecisionTaskConfiguration['task_assigned_to_group'];
                            $saveTaskData['form_template_id']       =   $DecisionTaskConfiguration['task_form_template_id'];
                            $saveTaskData['assigned_by']            =   $created_by;
                            $saveTaskData['created_by']             =   $created_by;
                            $saveTaskData['completed_by']           =   $created_by;
                            $saveTaskData['created_datetime']       =   $created_datetime;
                            $saveTaskData['assigned_datetime']      =   $created_datetime;
                            $saveTaskData['Comments']               =   $_POST['Comments']; 
                            $saveTaskData['completed_datetime']     =   $created_datetime;
                            $saveTaskData['isEndTask']              =   $DecisionTaskConfiguration['isEndTask'];
                            $saveTaskData['is_decision_task']       =   $DecisionTaskConfiguration['is_decision_task'];
                            $saveTaskData['isCalendarTask']         =   $DecisionTaskConfiguration['isCalendarTask'];
                            $saveTaskData['parent_task_id']         =   $_POST['task_id'];
                            $inserted = $crudTaskObj->save($saveTaskData);
    
                            if($inserted>0){   

                                /* (Comment message - Moved to Communication service)
                                $AssignedUserData = $crudTaskObj->FindRow('users', array('useremail', 'CONCAT(fname,\' \', mname,\' \', lname) AS name'), array('id='.$DecisionTaskConfiguration['task_assigned_to']));
                                $mailTrail  =   '<br/><br/><br/><strong>Thanks & Regards,</strong><br/>';
                                $subject    =   $DecisionTaskConfiguration['task_name'].' Assigned';
                              
                                $message    =   'Dear '.$AssignedUserData['name'].',<br/><br/>'. $subject.'<br/><br/>Please check below Case details:<br/>';
                                $message   .=   '<table width="100%" cellspacing="3" cellpadding="3">';
                                    $message   .=   '<tr>';
                                        $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Case Number</strong></td>';
                                        $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$cases['case_number'].'</td>';
                                    $message   .=   '</tr>';
                                    $message   .=   '<tr>';
                                        $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Task Name</strong></td>';
                                        $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$TaskConfiguration['task_name'].'</td>';
                                    $message   .=   '</tr>';
                                    $message   .=   '<tr>';
                                        $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Assigned Date</strong></td>';
                                        $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.date('d-m-Y h:i A', strtotime($created_datetime)).'</td>';
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
                                $saveNotificationData['title']      =   $DecisionTaskConfiguration['task_name'];
                                $saveNotificationData['description']=   '';
                                $saveNotificationData['task_id']    =   $_POST['task_id'];
                                $saveNotificationData['case_id']    =   $_POST['case_id'];
                                $saveNotificationData['user_id']    =   $created_by;
                                $saveNotificationData['created_at'] =   $created_datetime;
                                $crudNotificationObj->save($saveNotificationData);                                
                                
                                $DecisionTaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('parent_task_configuration_id='.$_POST['decision_task_id']));
                                
                                $updateData     =   array();
                                $updateData[]   =   'task_status_id=\'2\'';
                                $crudTaskObj->run_sql_query('UPDATE tasks SET '.implode(', ', $updateData).' WHERE task_id='.$_POST['task_id']);

                                // zhiling: create next task if this is not end workflow
                                $saveTaskData                           =   array();
                                $saveTaskData['task_id']                =   '';
                                $saveTaskData['case_id']                =   $_POST['case_id'];
                                $saveTaskData['task_cofiguration_id']   =   $DecisionTaskConfiguration['task_configuration_id'];
                                $saveTaskData['task_status_id']         =   1;
                                if($DecisionTaskConfiguration['task_assigned_to_group']==null)
                                    $saveTaskData['assigned_to']        =   $DecisionTaskConfiguration['task_assigned_to'];
                                else
                                    $saveTaskData['assigned_to_group']        =   $DecisionTaskConfiguration['task_assigned_to_group'];
                                $saveTaskData['form_template_id']       =   $DecisionTaskConfiguration['task_form_template_id'];
                                $saveTaskData['assigned_by']            =   $created_by;
                                $saveTaskData['created_by']             =   $created_by;
                                $saveTaskData['created_datetime']       =   $created_datetime;
                                $saveTaskData['assigned_datetime']      =   $created_datetime;
                                $saveTaskData['Comments']               =   '';        
                                $saveTaskData['isEndTask']              =   $DecisionTaskConfiguration['isEndTask'];
                                $saveTaskData['is_decision_task']       =   $DecisionTaskConfiguration['is_decision_task'];
                                $saveTaskData['isCalendarTask']       =   $DecisionTaskConfiguration['isCalendarTask'];
                                $saveTaskData['parent_task_id']         =   $inserted;
                                $inserted = $crudTaskObj->save($saveTaskData);
        
                                if($inserted>0){
                                    
                                    /* Zhiling: comment out unneeded email, move to service
                                    $AssignedUserData = $crudTaskObj->FindRow('users', array('useremail', 'CONCAT(fname,\' \', mname,\' \', lname) AS name'), array('id='.$DecisionTaskConfiguration['task_assigned_to']));
                                    $mailTrail  =   '<br/><br/><br/><strong>Thanks & Regards,</strong><br/>';
                                    $subject    =   $DecisionTaskConfiguration['task_name'].' Assigned';
                                    $message    =   'Dear '.$AssignedUserData['name'].',<br/><br/>'. $subject.'<br/><br/>Please check below Case details:<br/>';
                                    $message   .=   '<table width="100%" cellspacing="3" cellpadding="3">';
                                        $message   .=   '<tr>';
                                            $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Case Number</strong></td>';
                                            $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$cases['case_number'].'</td>';
                                        $message   .=   '</tr>';
                                        $message   .=   '<tr>';
                                            $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Task Name</strong></td>';
                                            $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.$TaskConfiguration['task_name'].'</td>';
                                        $message   .=   '</tr>';
                                        $message   .=   '<tr>';
                                            $message   .=   '<td width="30%" style="background-color:#EFEFEF;"><strong>Assigned Date</strong></td>';
                                            $message   .=   '<td width="70%" style="background-color:#FFFFFF;">'.date('d-m-Y h:i A', strtotime($created_datetime)).'</td>';
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
                                    $saveNotificationData['title']      =   $DecisionTaskConfiguration['task_name'];
                                    $saveNotificationData['description']=   '';
                                    $saveNotificationData['task_id']    =   $_POST['task_id'];
                                    $saveNotificationData['case_id']    =   $_POST['case_id'];
                                    $saveNotificationData['user_id']    =   $created_by;
                                    $saveNotificationData['created_at'] =   $created_datetime;
                                    $crudNotificationObj->save($saveNotificationData);
        
                                }
                                
                                $data = [
                                    'status'  =>  true,
                                    'message' =>  'Task Updated Successfully'
                                ];
                            }
                            else{
                                $data = [
                                    'status'    =>  false,
                                    'message'   =>  'Something went wrong. Please try again later. '.implode(', ', $crudTaskObj->error)
                                ];
                            }
                        }
                    }
                    else{
                        $crudNotificationObj = new CRUD('notifications', 'id');
                        $crudNotificationObj->mysqli = $link;
    
                        $saveNotificationData               =   array();
                        $saveNotificationData['title']      =   $TaskConfiguration['task_name'];
                        $saveNotificationData['description']=   '';
                        $saveNotificationData['task_id']    =   $_POST['task_id'];
                        $saveNotificationData['case_id']    =   $_POST['case_id'];
                        $saveNotificationData['user_id']    =   $created_by;
                        $saveNotificationData['created_at'] =   $created_datetime;
                        $crudNotificationObj->save($saveNotificationData);
    
                        $data = [
                            'status'  =>  true,
                            'message' =>  'Task Updated Successfully'
                        ];
                    }   
                }
            }
            else{
                $data = [
                    'status'  =>  true,
                    'message' =>  'Task Updated Successfully'
                ];
            }
        }
        
        if(isset($data['status']) && $data['status'] == true) {
            ?><script>
                window.location.href='<?php echo ROOT_URL; ?>/my-tasks.php';
            </script><?php
            exit;
        }
    }

    $crudObj = new CRUD('cases', 'case_id');
    $crudObj->mysqli = $link;

     $AllUsers = $crudObj->FindAll('users', array('id', 'CONCAT(fname, \' \', mname, \' \', lname) AS username'), array('status=1'), 0, 0, array(array('id', 'ASC')));

    $cols = array();
    $cols[] = '*';
    $cols[] = '(SELECT case_status_name FROM case_status WHERE cases.case_status_id = case_status.case_status_id) AS case_status';
    $cols[] = '(SELECT case_name FROM case_type_definition WHERE cases.case_type_id = case_type_definition.case_type_id) AS case_type';
    //$cols[] = '(SELECT referral_type_name FROM referral_type WHERE cases.type_of_referral = referral_type.referral_type_id) AS referral_type';
    //$cols[] = '(SELECT file_type_name FROM file_type WHERE cases.file_type_id = file_type.file_type_id) AS file_type';
    $record = $crudObj->FindRow('cases', $cols, array('case_id='.$_GET['case_id']));

    //$record['case_initial_file_date'] = date('m/d/Y', strtotime($record['case_initial_file_date']));
    //if(!empty($record['packet_sent_date']))  $record['packet_sent_date'] = date('m/d/Y', strtotime($record['packet_sent_date']));
    //else $record['packet_sent_date'] = '';
    
    //if(!empty($record['date_to_nurse']))  $record['date_to_nurse'] = date('m/d/Y', strtotime($record['date_to_nurse']));
    //else $record['date_to_nurse'] = '';
    
    $cont = $crudObj->FindRow('contacts', array(), array('contact_id='.$record['contact_id']));

    $cols = array();
    $cols[] = '*';
    $cols[] = '(SELECT type_name FROM attachement_type WHERE attachement_type.attachment_type_id = case_attachments.attachment_type_id) AS attachement_type_name';
    $cols[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = case_attachments.created_by) AS created_by';
    $attachments = $crudObj->FindAll('case_attachments', $cols, array('case_id='.$_GET['case_id']));

    $colsData = array();
    $colsData[] = '*';
    $colsData[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = tasks.completed_by) AS completed_by';
    $colsData[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = tasks.assigned_to) AS assigned_person';
    $colsData[] = '(SELECT task_name FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS task_name';
    $colsData[] = '(SELECT task_form_template_id FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS task_form_template_id';
    $TaskData = $crudObj->FindAll('tasks', $colsData, array('case_id='.$_GET['case_id']), 0, 0, array(array('task_id', 'ASC')), false);
    $attachement_type = $crudObj->FindAll('attachement_type', array(), array(), 0, 0, array(array('attachment_type_id', 'ASC')));
    $TaskConfiguration = $crudObj->FindAll('task_configuration', array('task_configuration_id', 'task_name'), array(), 0, 0, array(array('task_configuration_id', 'ASC')));
    $TaskStatus = $crudObj->FindAll('task_status', array('task_status_id', 'task_status_name'), array(), 0, 0, array(array('task_status_id', 'ASC')));
    
    if($fxd==0)
    $tasks = $crudObj->FindRow('tasks', $colsData, array('task_id='.$_GET['task_id']));
else
    $tasks = $crudObj->FindRow('tasks', $colsData, array('case_id='.$_GET['case_id']));

    // to test task configuration and parent id query
    //$tpid = $tasks['task_cofiguration_id'];
    //echo "<script>alert($tpid);</script>";

    $colsData = array();
    $colsData[] = '*';
    $colsData[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = tasks.completed_by) AS completed_by';
    $colsData[] = '(SELECT task_name FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS task_name';

    
    $colsData = array('task_configuration_id', 'task_name');
    $attachement_type = $crudObj->FindAll('attachement_type', array(), array(), 0, 0, array(array('attachment_type_id', 'ASC')));

    ?><head>
        <title>Case Manager - Case Number <?php echo $record['case_number']; ?></title>
        <?php include 'layouts/head.php'; ?>
        <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="assets/css/style.css">

        <!-- DataTables -->
        <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="assets/css/style.css">

        <!-- Responsive datatable examples -->
        <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- following are for calendar modal window-->
        <link rel="stylesheet" href="assets/calendar/css/all.css">
       <link rel="stylesheet" href="assets/calendar/css/bootstrap.min.css">
       <link rel="stylesheet" href="assets/calendar/fullcalendar/lib/main.min.css"> 
       <script src="assets/calendar/js/jquery-3.6.0.min.js"></script>
       <script src="assets/calendar/js/bootstrap.min.js"></script>
       <script src="assets/calendar/fullcalendar/lib/main.min.js"></script>
      <!-- end calendar modal window-->
        
        <?php include 'layouts/head-style.php'; ?>
    </head>
    <?php 
    include 'layouts/body.php'; 
    include './forms/travel_approval_form.php';
    include './forms/employee_training_registration.php';
    ?>
    <!-- Begin page -->
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
                                    <h4 class="mb-sm-0 font-size-18">View Case Number <?php echo $record['case_number']; ?> Details</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript:history.go(-1);">Case List</a></li>
                                            <li class="breadcrumb-item active">Case: <?php echo $record['case_number']; ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <?php  include 'layouts/alert-messages.php'; ?>
                        <div class="row g-0">
                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <label for="case_type_id" class="col-sm-2 col-form-label">Case Type:</label>
                                            <div class="col-sm-2">
                                                <input type="text" class="form-control form-control-sm" readonly disabled value="<?php echo $record['case_type']; ?>">
                                            </div>
                                            <label for="case_initial_file_date" class="offset-1 col-sm-3 col-form-label">Initial Requested Date:</label>
                                            <div class="col-sm-2">
                                                <input type="text" class="form-control form-control-sm" readonly disabled value="<?php echo $record['case_initial_file_date']; ?>">
                                            </div>
                                        </div>
                                                    
                                                
                                                <div class="row mb-2">
                                                    <label for="contact_ssn" class="col-sm-2 col-form-label">Email:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="email" name="email" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_email']; ?>" >
                                                    </div>
                                                    <div class="col-sm-1"></div> 
                                                    <label for="case_subject" class=" col-sm-3 col-form-label">Subject:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="case_subject" name="case_subject" value="<?php echo $record['case_subject']; ?>" >
                                                    </div>
                                                </div>
                                                 <div class="row mb-2">
                                                    <label for="contact_fname" class="col-sm-2 col-form-label">First Name:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="contact_fname" name="contact_fname" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_fname']; ?>" >
                                                    </div>
                                                   <label for="contact_lname" class="offset-2 col-sm-2 col-form-label">Last Name:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="contact_lname" name="contact_lname" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_lname']; ?>" >
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_address1" class="col-sm-2 col-form-label">address1:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="contact_address1" name="contact_address1" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_address1']; ?>" >
                                                    </div>
  
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_address2" class="col-sm-2 col-form-label">address2:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="contact_address2" name="contact_address2" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_address2']; ?>" >
                                                    </div>
                                                    
                                                    <label for="case_manager" class="offset-2 col-sm-2 col-form-label">Case Manager:</label>
                                                    <div class="col-sm-2">
                                                        <input type="text" class="form-control form-control-sm" readonly disabled id="case_manager_name" name="case_manager_name" value="<?php echo $record['case_manager_name']; ?>" >
                                                    </div>
                                                </div>
                             
                                                <div class="row mb-2">
                                                    <label for="city" class="col-sm-2 col-form-label">City:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="contact_city" name="contact_city" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_city']; ?>" >
                                                    </div>
                                            
                                                    <label for="state" class="col-sm-2 col-form-label">State:</label>
                                                    <div class="col-sm-1">
                                                        <input type="text" class="form-control form-control-sm" readonly disabled id="contact_state" name="contact_state" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_state']; ?>" >
                                                    </div>
                                                    <label for="zip" class="col-sm-1 col-form-label">ZIP:</label>
                                                    <div class="col-sm-1">
                                                        <input type="text" class="form-control form-control-sm" readonly disabled maxlength="10" id="contact_zip" name="contact_zip" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_zip']; ?>" >
                                                    </div>
                                                   
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_phone1" class="col-sm-2 col-form-label">Primary Phone:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="contact_phone1" name="contact_phone1" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_phone1']; ?>" >
                                                    </div>
                                                    <label for="contact_phone2" class="col-sm-2 col-form-label">Alternate Phone:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" readonly disabled id="contact_phone2" name="contact_phone2" value="<?php if(isset($cont['contact_email'])) echo $cont['contact_phone2']; ?>" >
                                                    </div>
                                                    
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="descriptions" class="col-sm-2 col-form-label">Case Description:</label>
                                                    <div class="col-sm-10">
                                                        <textarea class="form-control form-control-sm" readonly disabled name="descriptions" id="descriptions" rows="3"><?php echo $record['descriptions']; ?></textarea>
                                                    </div>
                                                </div>
                                        
                                         
                                        <div class="d-print-none row mt-4">
                                            <div class="float-end">
                                                 <a href="javascript:window.print()" class="btn btn-success  btn-sm waves-effect waves-light mr-1"><i class="fa fa-print"></i></a>
                                                <button class="btn btn-primary mdi mdi-scanner btn-sm w-30 waves-effect waves-light" type="button" onclick="return DocumentScanModal();">Scan Dcument</button> 
                                                <button class="btn btn-primary mdi mdi-cloud-upload-outline btn-sm w-40 waves-effect waves-light" type="button" onclick="return CaseAttachementUploadModal();">Upload Attachement</button> 
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="Attachements-tab" data-bs-toggle="tab" data-bs-target="#Attachements" type="button" role="tab" aria-controls="Attachements" aria-selected="true">Attachements</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="TaskHistory-tab" data-bs-toggle="tab" data-bs-target="#TaskHistory" type="button" role="tab" aria-controls="TaskHistory" aria-selected="false">Task History</button>
                                                    </li>
                                                </ul> 
                                                <div class="tab-content" id="myTabContent">
                                                    <div class="tab-pane fade show active" id="Attachements" role="tabpanel" aria-labelledby="Attachements-tab">
                                                        <table class="table table-bordered dt-responsive nowrap w-100 mb-2">
                                                            <thead>
                                                                <tr>         
                                                                    <th width="25%">Attachement Type</th>
                                                                    <th width="35%">File Name</th>
                                                                    <th width="20%">Created by</th>
                                                                    <th width="20%">Created On</th>                                        
                                                                </tr>
                                                            </thead>
                                                            <tbody id="AttachementData"><?php
                                                            if(isset($attachments) && count($attachments)>0){
                                                                foreach($attachments as $attachment){
                                                                    $caseAttachmentId = ___encryption_openssl('CaseManagerSalt'.$attachment['case_attachment_id']);
                                                                    ?><tr>
                                                                        <td><?php echo ucwords(strtolower($attachment['attachement_type_name'])); ?></td>
                                                                        <td>
                                                                            <i style="cursor:pointer; margin-right:7px;" onclick="CaseAttachedDocViewModal('<?php echo $attachment['file_name']; ?>', '<?php echo $attachment['file_ext']; ?>');" title="Click to View" class="mdi mdi-eye text-primary"></i>
                                                                            <a title="Click to Download" target="_parent" href="<?php echo ROOT_URL; ?>/download.php?case_attachment_id=<?php echo $caseAttachmentId; ?>"><i style="cursor:pointer; margin-right:7px;" class="mdi mdi-download text-danger"></i></a>
                                                                            
                                                                            <strong><?php 
                                                                                if(isset($attachment['original_filename']) && !empty($attachment['original_filename']))   
                                                                                    echo $attachment['original_filename']; 
                                                                                else echo $attachment['file_name'];
                                                                            ?></strong>
                                                                        </td>
                                                                        <td><?php echo $attachment['created_by']; ?></td>
                                                                        <td><?php echo date('m/d/Y h:i A', strtotime($attachment['created_datetime'])); ?></td>
                                                                    </tr><?php    
                                                                }
                                                            }
                                                            ?></tbody>
                                                        </table>
                                                    </div>
                                                    <div class="tab-pane fade" id="TaskHistory" role="tabpanel" aria-labelledby="TaskHistory-tab">
                                                        <table class="table table-bordered dt-responsive nowrap w-100 mb-2">
                                                            <thead>
                                                                <tr>         
                                                                    <th width="10%">Task Id</th>
                                                                    <th width="30%">Name</th>
                                                                    <th width="20%">Assigned To</th>
                                                                    <th width="20%">Completed By</th>
                                                                    <th width="20%">Completed Time</th>                                        
                                                                </tr>
                                                            </thead>
                                                            <tbody id="TaskData"><?php 
                                                        if(isset($TaskData) && count($TaskData)>0){
                                                            foreach($TaskData as $tds){
                                                                ?><tr>
                                                                    <td>
                                                                        <i style="cursor:pointer; margin-right:15px;" onclick="ViewCaseTaskDetails(<?php echo $tds['task_id']; ?>, <?php echo $tds['isCalendarTask']; ?>)" title="Click to View" class="mdi mdi-eye text-primary"></i>
                                                                        <strong><?php echo $tds['task_id']; ?></strong>
                                                                        </td>
                                                                        <td>
                                                                        <?php echo $tds['task_name']; ?>

                                                                        
                                                                        <?php
                                                                            if($tds['form_template_id']>=1)
                                                                            $internal_form_name  = mysqli_fetch_array(mysqli_query($link,"select form_template_internal_name from form_template where form_template_id=".$tds['form_template_id']));
                                                                            $form_name  = mysqli_fetch_array(mysqli_query($link,"select form_template_name from form_template where form_template_id=".$tds['form_template_id']));
                                                                            
                                                                         ?>
                                                                         <?php
                                                                            if($tds['form_template_id']>=1)

                                                                        {
                                                                        ?>
                                                                         <i  style="cursor:pointer; margin-right:7px;" type="button" onclick="OpenformtemplateInmodal(1, <?php echo $tds['task_id']; ?>, <?php echo $tds['form_template_id']; ?>, '<?php echo $internal_form_name['form_template_internal_name']; ?>' );"   id="viewFormDatabtn" title="View <?php echo $form_name['form_template_name']; ?>" class="mdi mdi-file-document-outline"></i>

                                                                          <?php
                                                                         }
                                                                         ?> 
                                                                          
                                                                          <?php
                                                                             $calendar_msg='';
                                                                            if($tds['isCalendarTask']>=1)
                                                                            $appointmentStartDateTime  = mysqli_fetch_array(mysqli_query($link,"select * from schedule_list where task_id=".$tds['task_id']));
                                                                            if(isset($appointmentStartDateTime['start_datetime'])){
                                                                                $currentAppointmentMsg=$appointmentStartDateTime['start_datetime'];
                                                                                $calendar_msg=' - Appointment Date/Time: '.$currentAppointmentMsg;
                                                                            }else{
                                                                                $currentAppointmentMsg='not scheduled';
                                                                                $calendar_msg='';
                                                                            }
                                                                            
                                                                            
                                                                         ?>
                                                                         <?php
                                                                            if($tds['isCalendarTask']>=1)

                                                                        {
                                                                        ?>
                                                                         <i  style="cursor:pointer; margin-right:7px;" type="button" onclick="openCalendarForThisTask(<?php echo $tds['task_id']; ?>, <?php echo $tds['case_id']; ?>);"   id="viewCalendarbtn" title="view/edit appointment"   class="mdi mdi-calendar-edit text-primary fa-lg "></i>
                                                                         <?php echo $currentAppointmentMsg; ?>

                                                                          <?php
                                                                         }
                                                                         ?> 
                                                                          
                                                                          
                                                                        </td>
                                                                        <td><?php if(isset($tds['assigned_to']) && !empty($tds['assigned_to']))   echo $tds['assigned_person']; else if(isset($tds['assigned_to_group']) && !empty($tds['assigned_to_group'])) { $grop_name = mysqli_fetch_array(mysqli_query($link,"select group_name from groups where group_id=".$tds['assigned_to_group'])); echo $grop_name['group_name'];  } else echo '--'; ?></td>
                                                                    <td><?php if(isset($tds['completed_by']) && !empty($tds['completed_by']))   echo $tds['completed_by']; else echo '--'; ?></td>
                                                                    <td><?php 
                                                                        if(isset($tds['completed_datetime']) && !empty($tds['completed_datetime']))   
                                                                            echo date('m/d/Y h:i A', strtotime($tds['completed_datetime'])); 
                                                                        else echo '--'; 
                                                                    ?></td>    
                                                                </tr><?php   
                                                            }
                                                        }    
                                                            ?></tbody>
                                                        </table>
                                                    </div>
                                                </div> 
                                            </div>         
                                        </div>
                                    </div>
                                </div>    
                            </div> <!-- end col -->
                        </div> <!-- end row --> 

                        <?php
                        if($fxd==0){

                        ?>
                        <div class="row g-0">
                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                <form class="needs-validation custom-form" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                            <div class="col-xxl-1 col-lg-1 col-md-1">
                                                    <label for="task_id" class="form-label">Task Id </label>
                                                    <input type="text" class="form-control" id="task_name" value="<?php echo $tasks['task_id']; ?>" readonly disabled>
                                                </div>
                                                <div class="col-xxl-3 col-lg-3 col-md-3">
                                                    <label for="case_type_id" class="form-label">Task Created Date </label>
                                                    <input type="text" class="form-control" id="created_datetime" value="<?php echo date('m/d/Y h:i A', strtotime($tasks['assigned_datetime'])); ?>" readonly disabled>
                                                </div>
                                                <div class="col-xxl-3 col-lg-3 col-md-3">
                                                    <label for="person_first_name" class="form-label">Task Name </label>
                                                    <input type="text" class="form-control" id="task_name" value="<?php echo $tasks['task_name']; ?>" readonly disabled>
                                                </div>
                                                <?php if(isset($tasks['is_decision_task']) && $tasks['is_decision_task']!=1){ ?>
                                                <div class="col-xxl-2 col-lg-2 col-md-2">
                                                    <label for="person_first_name" class="form-label">Task Status <strong class="text-danger">*</strong></label>
                                                    <select class="form-control" name="task_status_id" id="task_status_id" required>
                                                        <option value="">Select Task Status</option><?php 
                                                    if(isset($TaskStatus) && count($TaskStatus)>0){
                                                        foreach($TaskStatus as $res){ 
                                                            ?><option value="<?php echo $res['task_status_id']; ?>" <?php if(isset($tasks['task_status_id']) && $tasks['task_status_id']==$res['task_status_id']) echo 'selected="selected"'; ?>><?php echo ucwords(strtolower($res['task_status_name'])); ?></option><?php
                                                        }
                                                    }
                                                    ?></select>
                                                </div>
                                                <?php } ?>
                                                <div class="col-xxl-3 col-lg-3 col-md-3">
                                                    <label for="assigned_to" class="form-label">Assigned To <strong class="text-danger">*</strong></label>
                                                    <select class="form-control" name="assigned_to" id="assigned_to">
                                                        <option value="">Select Person</option><?php 
                                                    if(isset($AllUsers) && count($AllUsers)>0){
                                                        foreach($AllUsers as $res){ 
                                                            ?><option value="<?php echo $res['id']; ?>" <?php if(isset($tasks['assigned_to']) && $tasks['assigned_to']==$res['id']) echo 'selected="selected"'; ?>><?php echo $res['username']; ?></option><?php
                                                        }
                                                    }
                                                    ?></select>
                                                </div>
                                            </div>
                                            <?php
                                                if($tasks['form_template_id']>=1)

                                                {
                                            ?>
                                                <div class="row mt-4">
                                                    <div class="col-xxl-12 col-lg-12 col-md-12">
                                                        <?php
                                                            $form_name = mysqli_fetch_array(mysqli_query($link,"select form_template_name from form_template where form_template_id=".$tasks['form_template_id']));
                                                            $internal_form_name  = mysqli_fetch_array(mysqli_query($link,"select form_template_internal_name from form_template where form_template_id=".$tasks['form_template_id']));
                                                       ?>
                                                        <label for="case_type_id" class="form-label"><?php echo $form_name['form_template_name'] ?>:</label>
                                                        <button data-id="<?php echo $tasks['task_id']; ?>" class="btn btn-primary btn-sm w-10  waves-effect waves-light" onclick="OpenformtemplateInmodal(0, <?php echo $tasks['task_id']; ?>, <?php echo $tasks['form_template_id']; ?>, '<?php echo $internal_form_name['form_template_internal_name']; ?>' ); " id='editFormDataBtn' type="button">Click button to view/edit</button> 
                                                    </div>           
                                                </div>

                                                
                                            <?php
                                                }
                                            ?>
                                            <div class="row mt-4">
                                                <div class="col-xxl-12 col-lg-12 col-md-12">
                                                    <label for="case_type_id" class="form-label">Comments</label>
                                                    <textarea class="form-control" id="Comments" name="Comments" rows="4"><?php if(isset($tasks['Comments']) && !empty($tasks['Comments'])) echo $tasks['Comments']; else echo ''; ?></textarea>
                                                </div>           
                                            </div>
                                            <?php if(isset($tasks['is_decision_task']) && $tasks['is_decision_task']==1){ ?>
                                            <div class="row mt-4">
                                                <div class="col-xxl-6 col-lg-6 col-md-5">
                                                    <label for="person_first_name" class="form-label">Decision <strong class="text-danger">*</strong></label>
                                                    <select class="form-control" name="decision_task_id" id="decision_task_id" required>
                                                        <option value="">Select </option><?php
                                                        $DecisionTask = mysqli_query($link,"SELECT * FROM task_configuration where parent_task_configuration_id=".$tasks['task_cofiguration_id']."; ");
                                                        foreach($DecisionTask as $res){ 
                                                            ?>
                                                            <option value="<?php echo $res['task_configuration_id']; ?>"><?php echo ucwords(strtolower($res['task_name'])); ?></option><?php
                                                        }
                                                    ?></select>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <div class="card-footer">
                                            <input type="hidden" id="task_id" name="task_id" value="<?php echo $tasks['task_id']; ?>">
                                            <input type="hidden" id="case_id" name="case_id" value="<?php echo $tasks['case_id']; ?>">
                                            <input type="hidden" id="task_configuration_id" name="task_configuration_id" value="<?php echo $tasks['task_cofiguration_id']; ?>">
                                            <div><textarea hidden id="flowchart_data" name="flow_content"></textarea></div>
                                            <button class="btn btn-primary w-25 waves-effect waves-light" name="SaveTaskDetailsBtn" type="submit">Save</button> 
                                        </div>
                                    </div>   
                                </form> 
                            </div> <!-- end col -->
                        </div>     
                        
                        <?php
                    }

                        ?>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include 'layouts/footer.php'; ?>
            </div>
            <!-- end main content-->
        </div>
        <!-- END layout-wrapper -->

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

        <div class="modal" id="ViewCaseTaskDetailsModal" tabindex="-1" role="dialog" aria-labelledby="ViewCaseTaskDetailsModal" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-primary">Task Details  </h4>
                        <h8 class="modal-title text-primary" id="calendar_msg_title"> <?php  echo $calendar_msg; ?> </h8>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="ViewCaseTaskDetails"></div>
                </div>
            </div>  
        </div>

        <!--  calendar modal -->
        <div class="modal" id="viewTaskCalendarDetail" tabindex="-1" role="dialog" aria-labelledby="viewTaskCalendarDetail" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                     <div class="modal-header">
                        <h4 class="modal-title text-primary">Calendar </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                   
                    <div class="modal-body "  id="calendar-content-php"></div>
                </div>    
                
            
            </div>
        </div>
           
        <!--  end calendar modal -->

        <div class="modal" id="CaseAttachementUploadModal" tabindex="-1" role="dialog" aria-labelledby="CaseAttachementUploadModal" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form class="needs-validation custom-form" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h4 class="modal-title">Add Attachement</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mt-2 mb-4">
                                <div class="col-xxl-12 col-lg-12 col-md-12">
                                    <label for="person_first_name" class="form-label">Attachement Type <strong class="text-danger">*</strong></label>
                                    <select class="form-control" name="attachment_type_id" id="attachment_type_id" >
                                        <?php 
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
                                    <input type="file"  class="form-control" name="attachement_file" id="attachement_file" accept=".jpg,.png,.bmp,.jpeg,.docx,.doc,.pdf,.pptx,.ppt" onchange="validate_fileupload(this);"/>   
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="case_id" name="case_id" value="<?php echo $_GET['case_id']; ?>">
                            <img src="<?php echo ROOT_URL.'/assets/images/loading.gif'; ?>" width="25px;" class="d-none loading-image"/>
                            <button class="btn btn-primary w-50 waves-effect waves-light" name="AddAttachementBtn" id="AddAttachementBtn" type="submit" onclick="return valid_form();">Add Attachement</button>    
                        </div>
                    </form>
                </div>
            </div>  
        </div>
        <!-- Document Scan Modal - Placeholder -->
        <div class="modal" id="DocumentScanModal" style="display:none;" tabindex="-1" role="dialog" aria-labelledby="DocumentScanModal" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <body>

            <div>
                <div class="modal-header">
                   <h4 class="modal-title">Scan Document</h4>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mt-2 mb-4">
                        <div class="col-xxl-12 col-lg-12 col-md-12">

                        <label class="form-label">Scanner:</label>
                        <select class="form-control" id="scannerName"></select>
                        <label class="form-label">Resolution (DPI):</label>
                        <input type="text" class="form-control" id="resolution" value="200" />
                        <label class="form-label">Pixel Mode:</label>
                        <select class="form-control" id="pixelMode">
                            <option>Grayscale</option>
                            <option selected>Color</option>
                        </select>
                        <label class="form-label">Image Format:</label>
                        <select class="form-control" id="imageFormat">
                            <option selected>JPG</option>
                            <option>PNG</option>
                        </select>
                        </div>
                    </div>
                </div>                        
                <div  style="text-align:center">
                    <button class="btn btn-primary w-50 waves-effect waves-light"  onclick="doScanning();">Scan Now...</button>
                </div>
                <br />
                <div  style="text-align:center">
                    <img id="scanOutput"/>
                </div>
            </div>
        
            <!--IMPORTANT: Javascripts to call JSCan has been moved under Modal Show-->
                    
                </div>
            </div>  
        </div>                               
        <!-- Right Sidebar -->
        <?php include 'layouts/right-sidebar.php'; ?>
        <!-- /Right-bar -->

        <!-- JAVASCRIPT -->
        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- apexcharts -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></scri>

        <!-- Plugins js-->
        <script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
        
        <!-- dashboard init -->
        <script src="assets/js/pages/dashboard.init.js"></script>
        
        <!--  datatable js -->
        <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/jszip/jszip.min.js"></script>
        <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <!-- Responsive examples -->
        <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="assets/js/pages/datatables.init.js"></script>

        <!-- App js -->
        <script src="assets/js/app.js"></script>
       

        <script>
            var ROOT_URL = '<?php echo ROOT_URL; ?>';
            function CaseAttachedDocViewModal(file_name, file_ext){
                var imageFileExt = ["jpg", "jpeg", "png", "gif", "bmp"];
                var docFileExt = ["docx", "pptx", "doc", "pdf", "ppt", "xls", "xlsx"];

                var htmlData = '<div class="row">';
                    htmlData += '<div class="col-xxl-12 col-lg-12 col-md-12 text-center">';
                        if(imageFileExt.includes(file_ext))
                            htmlData += '<img src="'+ROOT_URL+'/uploads/case-attachements/'+file_name+'" class="img-fluid border rounded"/>';
                        else {
                            if(docFileExt.includes(file_ext)){
                                htmlData += '<iframe src="https://docs.google.com/viewer?url='+ROOT_URL+'/uploads/case-attachements/'+file_name+'&embedded=true" frameborder="0" width="95%" height="400" style="border:1px solid black;"></iframe>';
                            }
                            else if(file_ext == 'pdf')
                                htmlData += '<iframe src="'+ROOT_URL+'/uploads/case-attachements/'+file_name+'" width="95%" height="400" style="border:1px solid black;" frameborder="0"></iframe>';
                            else if(file_ext == 'txt')
                                htmlData += '<iframe src="'+ROOT_URL+'/view-txt-file.php?file_name='+file_name+'" width="95%" height="400" style="border:1px solid black;" frameborder="0"></iframe>';
                            else if(file_ext == 'tiff' || file_ext == 'tif')
                                htmlData += '<iframe src="https://docs.google.com/viewer?url='+ROOT_URL+'/uploads/case-attachements/'+file_name+'&embedded=true" frameborder="0" width="95%" height="400" style="border:1px solid black;"></iframe>';
                        }
                    htmlData += '</div>';
                htmlData += '</div>';   
                $('#CaseAttachedDocViewModal').modal('show');
                $('#CaseAttachedDocView').html(htmlData);
            }
            function toTitleCase(str) {
                return str.replace(/(?:^|\s)\w/g, function(match) {
                    return match.toUpperCase();
                });
            }
            function CaseAttachedDocViewClose(){
                $('#CaseAttachedDocView').html('');
                $('#CaseAttachedDocViewModal').modal('hide');                   
            }
            function CaseAttachementUploadModal(){
                $('#CaseAttachementUploadModal').modal('show');    
            }
            
            function DocumentScanModal(){
                
              // Load JSPrintManager.js
                var jsPrintManagerScript = document.createElement('script');
                jsPrintManagerScript.src = 'modules/jsPrint/JSPrintManager.js';
                jsPrintManagerScript.onload = function() {
                // Load JsScanOptions.js after JSPrintManager.js has loaded
                var jsScanOptionsScript = document.createElement('script');
                jsScanOptionsScript.src = 'modules/jsPrint/JsScanOptions.js';
                jsScanOptionsScript.onload = function() {
                    // Both scripts have loaded, now show the modal
                    $('#DocumentScanModal').modal('show');
                }
                document.body.appendChild(jsScanOptionsScript);
            }
            document.body.appendChild(jsPrintManagerScript);
            }
            function DocumentScanModalClose(){
               // Hide the modal
                $('#DocumentScanModal').hide();

                // Remove the scripts
                $('script[src="modules/jsPrint/JSPrintManager.js"]').remove();
                $('script[src="modules/jsPrint/JsScanOptions.js"]').remove();                 
            }
            function validate_fileupload(input_element) {
                var fileName = input_element.value;
                var allowed_extensions = new Array("jpg", "png", "gif", "jpeg", "bmp", "pdf", "pptx", "ppt", "docx", "doc", "txt", "xls", "xlsx", "tiff", "tif");
                var file_extension = fileName.split('.').pop(); 
                for(var i = 0; i < allowed_extensions.length; i++){
                    if(allowed_extensions[i]==file_extension) {
                        valid = true;
                        return;
                    }
                }
                alert("Invalid file");
                valid = false;
            }
            function valid_form() {
               
                return valid;
            }
            function ViewCaseTaskDetails(task_id, isCalendar){
                var calValue=0;
                $.ajax({
                    url         :   'action.php',
                    dataType    :   'json',
                    data: {
                        action:'ViewCaseTaskDetails',
                        task_id:task_id,
                    },                        
                    type        :   'post',
                    success     :   function(data){
                        $('#ViewCaseTaskDetails').html(data['data']);
                        $('#ViewCaseTaskDetailsModal').modal('show'); 
                    }
                });
                
                if (calValue==isCalendar){
                    document.getElementById("calendar_msg_title").style.display="none";
                }else{
                    document.getElementById("calendar_msg_title").style.display="";
                }
            }
        </script>

        <script>
        function OpenformtemplateInmodal(view_id, taskId, form_template_id, internalFormName)
            {   
                var taskIdObj = document.getElementById("task_id");
                taskIdObj.value=taskId;
                let modalName='#'+internalFormName;
                $(modalName).modal('show'); 
                 if(view_id==1){
                 document.getElementById("savebtn").disabled=true;
                 document.getElementById("savebtn2").disabled=true;
                }
    
}
        </script>
        
        <script>
        function openCalendarForThisTask( task_id, case_id)
            {   
                $("#calendar-content-php").load("calendar-modal.php");
                $('#viewTaskCalendarDetail').modal('show'); 
            }
    

        </script>
    <!-- Keep Nav Menu Open and Highlight link -->    
    <script>
        $('#mm-admin').attr('aria-expanded', true);
        $('#mm-admin').addClass('mm-show')
        $('#mm-admin').css('height', 'auto')
        $('#mm-admin').parent().addClass('mm-active');
        $($('#mm-admin').children()[6]).children().eq(0).css('color', '#1c84ee');
    </script>
        
     
    </body>
</html>