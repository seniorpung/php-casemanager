<?php
    // Include config file
    require_once "layouts/config.php";
    include 'layouts/head-main.php';
    include 'layouts/session.php'; 

    global $link;
    include 'class.crud.php';

    
    
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

        $casesData = $crudTaskObj->FindRow('cases', array(), array('case_id='.$_POST['case_id']));

        $LoggedUserData = $crudTaskObj->FindRow('users', array('useremail', 'CONCAT(fname,\' \', mname,\' \', lname) AS name'), array('id='.$created_by));

        $updateData     =   array();
        $updateData[]   =   'Comments=\''.$_POST['Comments'].'\'';
        if(isset($_POST['task_status_id']))  
            $updateData[]   =   'task_status_id=\''.$_POST['task_status_id'].'\'';

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
            $updateData[]   =   'task_status_id=\'2\'';
            $updateData[]   =   'completed_by=\''.$created_by.'\'';
            $updateData[]   =   'completed_datetime=\''.$created_datetime.'\'';
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

            $crudTaskObj->run_sql_query('UPDATE cases SET case_status_id=\'2\' WHERE case_id='.$_POST['case_id']); 

            $data = [
                'status'  =>  true,
                'message' =>  'Task Updated Successfully'
            ];
        }
        else{
            $TaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['task_configuration_id']), array(array('task_configuration_id', 'ASC')));
            
            $NextTaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id>'.$_POST['task_configuration_id'], 'case_type_id='.$casesData['case_type_id']), array(array('task_configuration_id', 'ASC')));
            $_POST['task_configuration_id'] = $NextTaskConfiguration['task_configuration_id'];
            
            //echo '<pre>';    print_r($TaskConfiguration); print_r($NextTaskConfiguration); print_r($_POST);   echo '</pre>'; exit;

            if($crudTaskObj->FindRecordsCount('tasks', array('case_id='.$_POST['case_id'], 'task_cofiguration_id='.$_POST['task_configuration_id']))==0){
                $TaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['task_configuration_id']));
                $cases = $crudTaskObj->FindRow('cases', array(), array('case_id='.$_POST['case_id']));
    
                if(isset($_POST['task_status_id']) && $_POST['task_status_id']==2) {
                    $saveTaskData                           =   array();
                    $saveTaskData['task_id']                =   '';
                    $saveTaskData['case_id']                =   $_POST['case_id'];
                    $saveTaskData['task_cofiguration_id']   =   $_POST['task_configuration_id'];
                    $saveTaskData['task_status_id']         =   1;
                    $saveTaskData['assigned_to']            =   $TaskConfiguration['task_assigned_to'];
                    $saveTaskData['assigned_by']            =   $created_by;
                    $saveTaskData['created_by']             =   $created_by;
                    $saveTaskData['created_datetime']       =   $created_datetime;
                    $saveTaskData['assigned_datetime']      =   $created_datetime;
                    $saveTaskData['Comments']               =   '';        
                    $saveTaskData['isEndTask']              =   $TaskConfiguration['isEndTask'];
                    $saveTaskData['is_decision_task']       =   $TaskConfiguration['is_decision_task'];
                    $saveTaskData['parent_task_id']         =   $_POST['task_id'];
                    $inserted = $crudTaskObj->save($saveTaskData);
    
                    if($inserted>0){
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
                        $DecisionTaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['decision_task_id']));
                        $updateData     =   array();
                        $updateData[]   =   'task_status_id=\'2\'';
                        $updateData[]   =   'completed_by=\''.$created_by.'\'';
                        $updateData[]   =   'completed_datetime=\''.$created_datetime.'\'';
                        $crudTaskObj->run_sql_query('UPDATE tasks SET '.implode(', ', $updateData).' WHERE task_id='.$_POST['task_id']);
                        
                        if(isset($DecisionTaskConfiguration['isEndTask']) && $DecisionTaskConfiguration['isEndTask']==1){
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
                            $DecisionTaskConfiguration = $crudTaskObj->FindRow('task_configuration', array(), array('task_configuration_id='.$_POST['decision_task_id']));
                            $saveTaskData                           =   array();
                            $saveTaskData['task_id']                =   '';
                            $saveTaskData['case_id']                =   $_POST['case_id'];
                            $saveTaskData['task_cofiguration_id']   =   $DecisionTaskConfiguration['task_configuration_id'];
                            $saveTaskData['task_status_id']         =   2;
                            $saveTaskData['assigned_to']            =   $DecisionTaskConfiguration['task_assigned_to'];
                            $saveTaskData['assigned_by']            =   $created_by;
                            $saveTaskData['created_by']             =   $created_by;
                            $saveTaskData['completed_by']           =   $created_by;
                            $saveTaskData['created_datetime']       =   $created_datetime;
                            $saveTaskData['assigned_datetime']      =   $created_datetime;
                            $saveTaskData['Comments']               =   $_POST['Comments']; 
                            $saveTaskData['completed_datetime']     =   $created_datetime;
                            $saveTaskData['isEndTask']              =   $DecisionTaskConfiguration['isEndTask'];
                            $saveTaskData['is_decision_task']       =   $DecisionTaskConfiguration['is_decision_task'];
                            $saveTaskData['parent_task_id']         =   $_POST['task_id'];
                            $inserted = $crudTaskObj->save($saveTaskData);
    
                            if($inserted>0){                                
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
        
                                $saveTaskData                           =   array();
                                $saveTaskData['task_id']                =   '';
                                $saveTaskData['case_id']                =   $_POST['case_id'];
                                $saveTaskData['task_cofiguration_id']   =   $DecisionTaskConfiguration['task_configuration_id'];
                                $saveTaskData['task_status_id']         =   1;
                                $saveTaskData['assigned_to']            =   $DecisionTaskConfiguration['task_assigned_to'];
                                $saveTaskData['assigned_by']            =   $created_by;
                                $saveTaskData['created_by']             =   $created_by;
                                $saveTaskData['created_datetime']       =   $created_datetime;
                                $saveTaskData['assigned_datetime']      =   $created_datetime;
                                $saveTaskData['Comments']               =   '';        
                                $saveTaskData['isEndTask']              =   $DecisionTaskConfiguration['isEndTask'];
                                $saveTaskData['is_decision_task']       =   $DecisionTaskConfiguration['is_decision_task'];
                                $saveTaskData['parent_task_id']         =   $inserted;
                                $inserted = $crudTaskObj->save($saveTaskData);
        
                                if($inserted>0){
                                    
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

    $crudObj = new CRUD('tasks', 'task_id');
    $crudObj->mysqli = $link;

    $AllUsers = $crudObj->FindAll('users', array('id', 'CONCAT(fname, \' \', mname, \' \', lname) AS username'), array('status=1'), 0, 0, array(array('id', 'ASC')));

    $colsData = array();
    $colsData[] = '*';
    $colsData[] = '(SELECT task_name FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS task_name';
    $colsData[] = '(SELECT defaultSLA FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS defaultSLA';
    
    $colsData[] = '(SELECT person_first_name FROM cases WHERE cases.case_id=tasks.case_id) AS person_first_name';
    $colsData[] = '(SELECT person_middle_name FROM cases WHERE cases.case_id=tasks.case_id) AS person_middle_name';
    $colsData[] = '(SELECT person_last_name FROM cases WHERE cases.case_id=tasks.case_id) AS person_last_name';
    $colsData[] = '(SELECT created_datetime FROM cases WHERE cases.case_id=tasks.case_id) AS case_created_datetime';

    //$tasks = $crudObj->FindRow('tasks', $colsData, array('assigned_to='.$_SESSION['id'], 'task_id='.$_GET['task_id']));
    $tasks = $crudObj->FindRow('tasks', $colsData, array('assigned_to != ""', 'task_id='.$_GET['task_id']));

    $cols = array();
    $cols[] = '*';
    $cols[] = '(SELECT case_status_name FROM case_status WHERE cases.case_status_id = case_status.case_status_id) AS case_status';
    $cols[] = '(SELECT case_name FROM case_type_definition WHERE cases.case_type_id = case_type_definition.case_type_id) AS case_type';
    $record = $crudObj->FindRow('cases', $cols, array('case_id='.$tasks['case_id']));

    $record['case_initial_file_date'] = date('m/d/Y', strtotime($record['case_initial_file_date'])); 

    $cols = array();
    $cols[] = '*';
    $cols[] = '(SELECT type_name FROM attachement_type WHERE attachement_type.attachment_type_id = case_attachments.attachment_type_id) AS attachement_type_name';
    $cols[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = case_attachments.created_by) AS created_by';
    $attachments = $crudObj->FindAll('case_attachments', $cols, array('case_id='.$tasks['case_id']));

    $attachement_type = $crudObj->FindAll('attachement_type', array(), array(), 0, 0, array(array('attachment_type_id', 'ASC')));
    $TaskConfiguration = $crudObj->FindAll('task_configuration', array('task_configuration_id', 'task_name'), array(), 0, 0, array(array('task_configuration_id', 'ASC')));
    $TaskStatus = $crudObj->FindAll('task_status', array('task_status_id', 'task_status_name'), array(), 0, 0, array(array('task_status_id', 'ASC')));


    $colsData = array();
    $colsData[] = '*';
    $colsData[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = tasks.completed_by) AS completed_by';
    $colsData[] = '(SELECT task_name FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS task_name';
    $TaskData = $crudObj->FindAll('tasks', $colsData, array('case_id='.$tasks['case_id']), 0, 0, array(array('task_id', 'ASC')), false);
    
    $colsData = array('task_configuration_id', 'task_name');
    $DecisionTask = $crudObj->FindAll('task_configuration', $colsData, array('parent_task_configuration_id='.$tasks['task_cofiguration_id']), 0, 0, array(array('task_configuration_id', 'ASC')), false);

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
        
        <?php include 'layouts/head-style.php'; ?>
    </head>
    <?php include 'layouts/body.php'; ?>
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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                            <li class="breadcrumb-item active">Case: <?php echo $record['case_number']; ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <?php  include 'layouts/alert-messages.php'; ?>
                        <div class="row g-0">
                            <div class="offset-xxl-1 offset-lg-1 offset-md-1 col-xxl-10 col-lg-10 col-md-10">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">Case Number &nbsp;&nbsp;&nbsp;&nbsp; <strong class="h4" id="case_number"><?php echo $record['case_number']; ?></strong></label>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">Case Type</label>
                                                <input type="text" readonly disabled class="form-control" id="case_type" value="<?php echo $record['case_type']; ?>">
                                            </div>
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">Case Status</label>
                                                <input type="text" readonly disabled class="form-control" id="case_status" value="<?php echo $record['case_status']; ?>">
                                            </div>
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">Initial Complaint Date</label>
                                                <input type="text" readonly disabled class="form-control" id="case_initial_file_date" value="<?php echo date('m/d/Y', strtotime($record['case_initial_file_date'])); ?>">
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">First Name</label>
                                                <input type="text" readonly disabled class="form-control" id="person_first_name" value="<?php echo $record['person_first_name']; ?>">
                                            </div>                            
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">Middle Initial</label>
                                                <input type="text" readonly disabled class="form-control" id="person_middle_name" value="<?php echo $record['person_middle_name']; ?>">
                                            </div>  
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">Last Name</label>
                                                <input type="text" readonly disabled class="form-control" id="person_last_name" value="<?php echo $record['person_last_name']; ?>">
                                            </div>          
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-xxl-8 col-lg-8 col-md-8">
                                                <label for="case_type_id" class="form-label">Contact Email</label>
                                                <input type="text" readonly disabled class="form-control" id="contactemail" value="<?php echo $record['contactemail']; ?>">
                                            </div>                            
                                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                                <label for="case_type_id" class="form-label">Contact Phone</label>
                                                <input type="text" readonly disabled class="form-control" id="contactnumber" value="<?php echo $record['contactnumber']; ?>">
                                            </div>         
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                                <label for="case_type_id" class="form-label">Agency</label>
                                                <input type="text" readonly disabled class="form-control" id="agency" value="<?php echo $record['agency']; ?>">
                                            </div>           
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                                <label for="case_type_id" class="form-label">Descriptions</label>
                                                <textarea class="form-control" readonly disabled id="descriptions" rows="3"><?php echo $record['descriptions']; ?></textarea>
                                            </div>           
                                        </div>
                                        <div class="row mt-4">
                                            <div class="offset-xxl-10 offset-lg-10 offset-md-10 col-xxl-2 col-lg-2 col-md-2 text-right">
                                                <button class="btn btn-primary btn-sm w-100 waves-effect waves-light" type="button" onclick="return CaseAttachementUploadModal();">Upload Attachement</button> 
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
                                                                    ?><tr>
                                                                        <td><?php echo ucwords(strtolower($attachment['attachement_type_name'])); ?></td>
                                                                        <td>
                                                                            <i style="cursor:pointer; margin-right:15px;" onclick="CaseAttachedDocViewModal('<?php echo $attachment['file_name']; ?>', '<?php echo $attachment['file_ext']; ?>');" class="mdi mdi-eye text-primary"></i>
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
                                                                    <th width="60%">Task Name</th>
                                                                    <th width="20%">Completed By</th>
                                                                    <th width="20%">Completed Time</th>                                        
                                                                </tr>
                                                            </thead>
                                                            <tbody id="TaskData"><?php 
                                                        if(isset($TaskData) && count($TaskData)>0){
                                                            foreach($TaskData as $tds){
                                                                ?><tr>
                                                                    <td>
                                                                        <i style="cursor:pointer; margin-right:15px;" onclick="ViewCaseTaskDetails(<?php echo $tds['task_id']; ?>);" class="mdi mdi-eye text-primary"></i>
                                                                        <strong><?php echo $tds['task_name']; ?></strong></td>
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
                        </div>                        
                        <div class="row g-0">
                            <div class="offset-xxl-1 offset-lg-1 offset-md-1 col-xxl-10 col-lg-10 col-md-10">
                                <form class="needs-validation custom-form" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-xxl-4 col-lg-4 col-md-4">
                                                    <label for="case_type_id" class="form-label">Task Created Date </label>
                                                    <input type="text" class="form-control" id="created_datetime" value="<?php echo date('m/d/Y h:i A', strtotime($tasks['assigned_datetime'])); ?>" readonly disabled>
                                                </div>
                                                <div class="col-xxl-4 col-lg-4 col-md-4">
                                                    <label for="person_first_name" class="form-label">Task Name </label>
                                                    <input type="text" class="form-control" id="task_name" value="<?php echo $tasks['task_name']; ?>" readonly disabled>
                                                </div>
                                                <?php if(isset($tasks['is_decision_task']) && $tasks['is_decision_task']!=1){ ?>
                                                <div class="col-xxl-4 col-lg-4 col-md-4">
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
                                                <div class="col-xxl-4 col-lg-4 col-md-4 mt-4">
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
                                                    if(isset($DecisionTask) && count($DecisionTask)>0){
                                                        foreach($DecisionTask as $res){ 
                                                            ?><option value="<?php echo $res['task_configuration_id']; ?>"><?php echo ucwords(strtolower($res['task_name'])); ?></option><?php
                                                        }
                                                    }
                                                    ?></select>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <div class="card-footer">
                                            <input type="hidden" id="task_id" name="task_id" value="<?php echo $_GET['task_id']; ?>">
                                            <input type="hidden" id="case_id" name="case_id" value="<?php echo $tasks['case_id']; ?>">
                                            <input type="hidden" id="task_configuration_id" name="task_configuration_id" value="<?php echo $tasks['task_cofiguration_id']; ?>">
                                            <button class="btn btn-primary w-25 waves-effect waves-light" name="SaveTaskDetailsBtn" type="submit">Save</button> 
                                        </div>
                                    </div>   
                                </form> 
                            </div> <!-- end col -->
                        </div>
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
                        <h4 class="modal-title">Task Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="ViewCaseTaskDetails"></div>
                </div>
            </div>  
        </div>

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
                                    <select class="form-control" name="attachment_type_id" id="attachment_type_id" required>
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
                                    <input type="file" required class="form-control" name="attachement_file" id="attachement_file" accept=".jpg,.png,.bmp,.jpeg,.docx,.doc,.pdf,.pptx,.ppt" onchange="validate_fileupload(this);"/>   
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="case_id" name="case_id" value="<?php echo $tasks['case_id']; ?>">
                            <img src="<?php echo ROOT_URL.'/assets/images/loading.gif'; ?>" width="25px;" class="d-none loading-image"/>
                            <button class="btn btn-primary w-50 waves-effect waves-light" name="AddAttachementBtn" id="AddAttachementBtn" type="submit" onclick="return valid_form();">Add Attachement</button>    
                        </div>
                    </form>
                </div>
            </div>  
        </div>

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
        <!-- Required datatable js -->
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
                var imageFileExt = ["jpg", "jpeg", "png", "gif", "bmp", "tiff"];
                var docFileExt = ["docx", "pdf", "pptx", "doc", "ppt", "xls", "xlsx"];

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
            function ViewCaseTaskDetails(task_id){
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
            }

        </script>
    </body>
</html>