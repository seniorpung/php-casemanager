<?php
require_once "layouts/config.php";
include 'layouts/session.php';


global $link;
include 'class.crud.php';

$crudObj = new CRUD();
$crudObj->mysqli = $link;

if(isset($_POST['action']) && !empty($_POST['action'])){

    $action = $_POST['action'];
    $returnData = array();
    switch($action){
        case 'ViewCaseDetails':
            $returnData = ViewCaseDetails();
            break;
        case 'AddAttachement':
            $returnData = AddAttachement();
            break;
        case 'ViewCaseTaskDetails':
            $returnData = ViewCaseTaskDetails();
            break;
        case 'getRoleDefinationOnRole':
            $returnData = getRoleDefinationOnRole();
            break;
        case 'FindAttSSN':
            $returnData = FindAttSSN();
            break;
        case 'getSelectedContactRecords':
            $returnData = getSelectedContactRecords();
            break;
        case 'FindSSN':
            $returnData = FindSSN();
            break;
        case 'getFirstTaskIsCalendar':
            $returnData=getFirstTaskIsCalendar();
            break;
    }
    echo json_encode($returnData);
}

function ViewCaseTaskDetails(){
    global $crudObj;
    
    $colsData = array();
    $colsData[] = '*';
    $colsData[] = '(SELECT task_name FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS task_name';
    $colsData[] = '(SELECT defaultSLA FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS defaultSLA';
    $colsData[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = tasks.completed_by) AS completed_by';
    
    $colsData[] = '(SELECT fname FROM cases_view WHERE cases_view.case_id=tasks.case_id) AS fname';
    $colsData[] = '(SELECT mname FROM cases_view WHERE cases_view.case_id=tasks.case_id) AS mname';
    $colsData[] = '(SELECT lname FROM cases_view WHERE cases_view.case_id=tasks.case_id) AS lname';
    $colsData[] = '(SELECT assigned_datetime FROM tasks_view WHERE tasks_view.task_id=tasks.task_id) AS task_created_datetime';
    $colsData[] = '(SELECT created_datetime FROM cases WHERE cases.case_id=tasks.case_id) AS case_created_datetime';
    
    $colsData[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = tasks.assigned_to) AS assigned_to';

    $tasks = $crudObj->FindRow('tasks', $colsData, array('task_id='.$_POST['task_id']));    
    $htmlData = '';

$htmlData .= '<div class="row mb-2 g-0">';
$htmlData .= '<div class="col-xxl-12 col-lg-12 col-md-12">';
$htmlData .= '<div class="row g-0">';
$htmlData .= '<label class="col-sm-2 col-form-label lbl">Task ID </label>';
$htmlData .= '<div class="col-sm-2">';
$htmlData .= '<input type="text" class="form-control form-control-sm" value="' . $tasks['task_id'] . '">'; 
$htmlData .= '</div>';
$htmlData .= '<label class="offset-1 col-sm-2 col-form-label lbl">Task Name </label>';
$htmlData .= '<div class="col-sm-5">';
$htmlData .= '<input type="text" class="form-control form-control-sm" value="' . $tasks['task_name'] . '">'; 
$htmlData .= '</div>';
$htmlData .= '</div>';
$htmlData .= '<div class="row g-0">';
$htmlData .= '<label class="col-sm-2 col-form-label lbl">Assigned To </label>';
$htmlData .= '<div class="col-sm-3">';
$htmlData .= '<input type="text" class="form-control form-control-sm" value="' . $tasks['assigned_to'] . '">'; 
$htmlData .= '</div>';
$htmlData .= '<label class="offset-1 col-sm-2 col-form-label lbl">Assigned Time</label>';
$htmlData .= '<div class="col-sm-4">';
$htmlData .= '<input type="text" class="form-control form-control-sm" value="' . $tasks['task_created_datetime'] . '">'; 
$htmlData .= '</div>';
$htmlData .= '</div>';
$htmlData .= '<div class="row g-0">';
$htmlData .= '<label class="col-sm-2 col-form-label lbl">Completed By </label>';
$htmlData .= '<div class="col-sm-3">';
$htmlData .= '<input type="text" class="form-control form-control-sm" value="' . $tasks['completed_by'] . '">'; 
$htmlData .= '</div>';
$htmlData .= '<label class="offset-1 col-sm-2 col-form-label lbl">Completed Time</label>';
$htmlData .= '<div class="col-sm-4">';
$htmlData .= '<input type="text" class="form-control form-control-sm" value="' . $tasks['completed_datetime'] . '">'; 
$htmlData .= '</div>';
$htmlData .= '</div>';
$htmlData .= '<div class="row g-0">';
$htmlData .= '<label class="col-sm-2 col-form-label lbl">Comments </label>';
$htmlData .= '<div class="col-sm-10">';
$htmlData .= '<textarea class="form-control form-control-sm" rows="3" name="comments">' . $tasks['Comments'] . '</textarea>';
$htmlData .= '</div>';
$htmlData .= '</div>';
$htmlData .= '</div>';
$htmlData .= '</div>';


    $data = [
        'status'        =>  true,
        'data'          =>  $htmlData 
    ];
    return $data;
}

function ViewCaseDetails(){
    global $crudObj;
    $cols = array();
    $cols[] = '*';
    $cols[] = '(SELECT case_status_name FROM case_status WHERE cases.case_status_id = case_status.case_status_id) AS case_status';
    $cols[] = '(SELECT case_name FROM case_type_definition WHERE cases.case_type_id = case_type_definition.case_type_id) AS case_type';
    $record = $crudObj->FindRow('cases', $cols, array('case_id='.$_POST['case_id']));

    $record['case_initial_file_date'] = date('m/d/Y', strtotime($record['case_initial_file_date'])); 

    $cols = array();
    $cols[] = '*';
    $cols[] = '(SELECT type_name FROM attachement_type WHERE attachement_type.attachment_type_id = case_attachments.attachment_type_id) AS attachement_type_name';
    $cols[] = '(SELECT CONCAT(fname,\' \', mname,\' \', lname) FROM users WHERE users.id = case_attachments.created_by) AS created_by';
    $attachments = $crudObj->FindAll('case_attachments', $cols, array('case_id='.$_POST['case_id']));

    $data = [
        'status'        =>  true,
        'data'          =>  $record,
        'attachments'   =>  $attachments,  
    ];
    return $data;
}

function AddAttachement(){
    global $link;
    $requiredData = array();
    if(!isset($_FILES['attachement_file']['tmp_name']))
        $requiredData['attachement_file'] = 'Please Choose Attachement';
    
    if(isset($_POST['attachment_type_id']) && empty($_POST['attachment_type_id']))
        $requiredData['attachment_type_id'] = 'Please Select Attachement Type'; 

    $data = array();
    if(isset($requiredData) && count($requiredData)>0){
        $data = [
            'status'    =>  false,
            'data'      =>  $requiredData
        ];
    }
    else{
        if(isset($_FILES['attachement_file']['tmp_name']) && !empty($_FILES['attachement_file']['tmp_name'])){
            $crudAttachObj = new CRUD('case_attachments', 'case_attachment_id');
            $crudAttachObj->mysqli = $link;
            
            $attachement_type =  $crudAttachObj->FindRow('attachement_type', array('type_name'), array('attachment_type_id='.$_POST['attachment_type_id']));

            $nameArr = explode('.', $_FILES['attachement_file']['name']);
            $ext = end($nameArr);

            $image      =   strtolower(str_replace(' ', '_', $attachement_type['type_name']).'_'.date('YmdHis').'.'.$ext);                
            $sourcePath =   $_FILES['attachement_file']['tmp_name'];
            $targetPath =   ROOT_PATH.'/uploads/tmp-case-attachements/'.$image;
            move_uploaded_file($sourcePath, $targetPath);

            $created_by = $_SESSION["id"];
            $created_datetime = date('Y-m-d H:i:s');

            $users_data =  $crudAttachObj->FindRow('users', array('CONCAT(fname,\' \', mname,\' \', lname) AS created_by'), array('id='.$_SESSION["id"]));

            $saveAttachData = array();
            $saveAttachData['case_attachment_id']   =   '';
            $saveAttachData['original_filename']    =   $_FILES['attachement_file']['name'];
            $saveAttachData['file_name']            =   $image;
            $saveAttachData['file_ext']             =   strtolower($ext);
            $saveAttachData['attachment_type_id']   =   $_POST['attachment_type_id'];
            $saveAttachData['attachment_type']      =   ucwords(strtolower($attachement_type['type_name']));
            $saveAttachData['created_by']           =   $created_by;
            $saveAttachData['users_data']           =   $users_data['created_by'];
            $saveAttachData['created_datetime']     =   $created_datetime;
            $saveAttachData['created_datetime_frm'] =   date('m/d/Y H:i A', strtotime($created_datetime));
            
            $data = [
                'status'        =>  true,
                'attachments'   =>  $saveAttachData,
                'message'       =>  'Attachement Successfully Added'
            ];
        }   
    }
    return $data;
}

function getRoleDefinationOnRole(){
    global $crudObj; 
    $role_permissions = $crudObj->FindAll('role_permissions', array(), array('role_id='.$_POST['role_id']));
    $record = array();
    if(isset($role_permissions) && isset($role_permissions) && count($role_permissions)>0){
        foreach($role_permissions as $dt){
            $record[$dt['role_def_id']] = $dt['role_def_id'];    
        }
    }
    
    $data = [
        'status'        =>  true,
        'data'          =>  $record,
    ];
    return $data;
}

function FindAttSSN(){
    global $crudObj;
    
    $cont = $crudObj->FindRow('contacts', array(), array('contact_last_4_ssn=\''.$_POST['contact_last_4_ssn'].'\''));
    if(isset($cont['contact_id']) && $cont['contact_id']>0){
        if ($crudObj->FindRecordsCount('cases', array('case_status_id=1 OR case_status_id=4', 'contact_id=' . $cont['contact_id'])) > 0){
            $htmlData = '<strong style="font-size:22px;" class="text-danger">Record found by SSN, It also has a case found.<br/>New case can not be created.</strong>';
            $data = [
                'status'        =>  false,
                'case_exists'   =>  true,
                'htmlData'      =>  $htmlData
            ];    
        }
        else{
            $contacts     =   $crudObj->FindAll('contacts', array(), array('contact_last_4_ssn=\''.$_POST['contact_last_4_ssn'].'\''), 0, 0, array(array('contact_id', 'ASC')), false); 
            if(isset($contacts) && is_array($contacts) && count($contacts)>0){
                $htmlData = '<div class="row">';
                    $htmlData .= '<div class="col-xxl-12 col-lg-12 col-md-12">';
                        $htmlData .= '<strong style="font-size:22px;" class="text-success">Record ('.$contacts[0]['contact_fname'].' '.$contacts[0]['contact_lname'].') found by SSN, No case found.</strong>';
                    $htmlData .= '</div>';
                    $htmlData .= '<div class="col-xxl-12 col-lg-12 col-md-12">';
                        $htmlData .= '<table id="datatable" class="table table-bordered dt-responsive nowrap w-100">';
                            $htmlData .= '<thead>';
                                $htmlData .= '<tr>';         
                                    $htmlData .= '<th>SSN</th>';
                                    $htmlData .= '<th>Name</th>';
                                    $htmlData .= '<th>City</th>';
                                    $htmlData .= '<th>State</th>';
                                    $htmlData .= '<th>Zip</th>';
                                    $htmlData .= '<th>Phone 1</th>';
                                    $htmlData .= '<th>Phone 2</th>';
                                    $htmlData .= '<th></th>';                                        
                                $htmlData .= '</tr>';
                            $htmlData .= '</thead>';
                            $htmlData .= '<tbody>';
                            foreach($contacts as $dts){
                                $htmlData .= '<tr>';
                                    $htmlData .= '<td>'.$dts['contact_last_4_ssn'].'</td>';
                                    $htmlData .= '<td>'.$dts['contact_fname'].' '.$dts['contact_lname'].'</td>'; 
                                    $htmlData .= '<td>'.$dts['contact_city'].'</td>';
                                    $htmlData .= '<td>'.$dts['contact_state'].'</td>';
                                    $htmlData .= '<td>'.$dts['contact_zip'].'</td>';
                                    $htmlData .= '<td>'.$dts['contact_phone1'].'</td>';
                                    $htmlData .= '<td>'.$dts['contact_phone2'].'</td>'; 
                                    $htmlData .= '<td class="text-center"><input type="button" class="btn btn-sm btn-info" value="Choose" onclick="getSelectedContactRecords('.$dts['contact_id'].');"/></td>';  
                                $htmlData .= '</tr>';
                            }
                            $htmlData .= '</tbody>';
                        $htmlData .= '</table>';
                    $htmlData .= '</div>';          
                $htmlData .= '</div>';
        
                $data = [
                    'status'        =>  true,
                    'case_exists'   =>  false,
                    'htmlData'      =>  $htmlData  
                ];
            }
        }
    }
    else{
        $htmlData = '<strong style="font-size:22px;" class="text-danger">No record found by SSN</strong>';
        $data = [
            'status'        =>  false,
            'case_exists'   =>  false,
            'htmlData'      =>  $htmlData
        ];
    }
    return $data;
}

function getSelectedContactRecords(){
    global $crudObj;  
    $contacts = $crudObj->FindRow('contacts', array(), array('contact_id='.$_POST['contact_id']));
    if(isset($contacts['contact_id']) && $contacts['contact_id']>0){
        $data = [
            'status'    =>  true,
            'data'      =>  $contacts  
        ];
    }   
    else{
        $data = [
            'status'    =>  false
        ];
    }
    return $data;
}

function getFirstTaskIsCalendar(){

    global $crudObj;  
    $taskConfig = $crudObj->FindRow('task_configuration', array(), array('case_type_id='.$_POST['selected_case_type_id'], ' isStartingTask=1'));

    if(isset($taskConfig['isCalendarTask']) && $taskConfig['isCalendarTask']>0){
        $data = [
            'isCalendar_status'    =>  true,
            'data'      =>  $taskConfig  
        ];
    }   
    else{
        $data = [
            'isCalendar_status'    =>  false
        ];
    }
    return $data;
}

function FindSSN(){
    global $crudObj;
    
    $cont = $crudObj->FindRow('contacts', array(), array('contact_last_4_ssn=\''.$_POST['contact_last_4_ssn'].'\''));
    if(isset($cont['contact_id']) && $cont['contact_id']>0){
        
            $htmlData = '<strong style="font-size:22px;" class="text-danger">SSN ('.$cont['contact_last_4_ssn'].') already exist. The name : ('.$cont['contact_fname'].' '.$cont['contact_lname'].') has this SSN</strong>';
            $data = [
                'status'        =>  false,
                'case_exists'   =>  true,
                'htmlData'      =>  $htmlData
            ];    
        }
    else{
        $htmlData = '<strong style="font-size:22px;" class="text-danger">No record found by SSN</strong>';
        $data = [
            'status'        =>  false,
            'case_exists'   =>  false,
            'htmlData'      =>  $htmlData
        ];
    }
    return $data;
}
if(isset($_POST['action']) && $_POST['action'] == 'disableuser'){
    $id = $_POST['id'];
  
     $sql = "UPDATE users SET status = '3' WHERE id=$id";
    if ($link->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }
    exit();
  }
  if(isset($_POST['action']) && $_POST['action'] == 'enableuser'){
    $id = $_POST['id'];
  
     $sql = "UPDATE users SET status = '1' WHERE id=$id";
    if ($link->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }
    exit();
  }
  if(isset($_POST['action']) && $_POST['action'] == 'copyworkflow'){
    $id = $_POST['id'];
  
     $sql = "call CloneWorkflowById($id)";
    if ($link->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }
    exit();
  }
  
  if(isset($_POST['action']) && $_POST['action'] == 'update_profile_picture'){
    $UploadFolder = "assets/images/users/";
    $temp = $_FILES["profile_picture"]["tmp_name"];
    $name = $_FILES["profile_picture"]["name"];
    $name = uniqid() . $name;
    if (move_uploaded_file($temp, $UploadFolder.'/'.$name)){
        $sql = "UPDATE users SET photo='$name' WHERE id='".$_SESSION["id"]."'";
        if ($link->query($sql) === TRUE) {
          $success = "1";
          echo json_encode(["status"=>"true","data"=>"Profile picture updated successfully.","URL"=>$name]);
        } else {
          //echo "Error updating record: " . $link->error;
          echo json_encode(["status"=>"false","data"=>"Something went wrong. Please try again later."]);
        }
    } else {
        echo json_encode(["status"=>"false","data"=>"Something went wrong. Please try again later."]);
    }
    exit();
}
?>