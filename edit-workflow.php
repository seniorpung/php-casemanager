<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php';
include 'layouts/session.php';

    global $link;
    include 'class.crud.php';

    $created_by         =   $_SESSION["id"];
    $created_datetime   =   date('Y-m-d H:i:s');

    $crudObj = new CRUD();
    $crudObj->mysqli = $link;

    $crudCTDObj = new CRUD('case_type_definition', 'case_type_id');
    $crudCTDObj->mysqli = $link;

    $crudTCObj = new CRUD('task_configuration', 'task_configuration_id');
    $crudTCObj->mysqli = $link;

    $assignUsers = $crudObj->FindAll('users', array('id', 'fname', 'mname', 'lname'), array(), 0, 0, array(array('id', 'ASC')));

    if(isset($_POST['submitFormBtn'])){ 

        $flow_content = (array)json_decode($_POST['flow_content']);
        $links = (array)$flow_content['links'];
        $old_links = $links;
        unset($links[0]);
        $dataAman = (array)$flow_content['operators'];
        $inDecisonTask  =   array();
        $dataForSave    =   array();
        if(isset($links) && is_array($links) && count($links)>0){
            foreach($links as $k=>$val){
                // echo "<script>alert('".$val->fromOperator." ".$val->toOperator."');</script>";
                $fromOperator   =   str_replace('created_operator_', '', $val->fromOperator);
                $toOperator     =   str_replace('created_operator_', '', $val->toOperator);
                $dataForSave[$fromOperator][$toOperator] = $toOperator;         
            }     
        }

        if(isset($dataAman) && is_array($dataAman) && count($dataAman)>0)
        {
            $end=array();
            $is_End=array();
            $is_Des=array();
            $des_val=array();
            foreach($dataAman as $k=>$val){
                if($k!="operator1")
                {
                    $index = substr($k,17);
                    // echo "<script>alert('".$k."! ".$val->properties->title."! ".$val->properties->dataclass."');</script>";
                    if($val->properties->isEditable==false)
                        array_push($end,$index);
                    else if(!isset($_POST['taskdata'][$index]))
                        array_push($des_val,$index);
                    if($val->properties->dataclass=="bg-info text-white")
                        array_push($is_Des,$index);
                }
            }
            
            foreach($links as $k=>$val){
                // echo "<script>alert('".$val->fromOperator." ".$val->toOperator."');</script>";
                $fromOperator   =   str_replace('created_operator_', '', $val->fromOperator);
                $toOperator     =   str_replace('created_operator_', '', $val->toOperator);
                if(in_array($toOperator,$end))
                    array_push($is_End,$fromOperator);
            }

            $CTDSaveData = array();
            $CTDInsertId = $_POST['case_type_id'];

            $CTDSaveData['case_type_id']            =   $_POST['case_type_id'];
            $CTDSaveData['case_name']               =   $_POST['name'];
            $CTDSaveData['case_type_description']   =   $_POST['name'];
            $CTDSaveData['effective_begin_date']    =   $_POST['begin_date'];
            $CTDSaveData['effective_end_date']      =   $_POST['end_date'];
            if($CTDInsertId>0){
                $CTDSaveData['created_by']              =   $_POST['created_by'];
                $CTDSaveData['created_datetime']        =   $_POST['created_datetime'];
            }else{
                $CTDSaveData['created_by']              =   $created_by;
                $CTDSaveData['created_datetime']        =   $created_datetime;
            }
            $CTDSaveData['last_updated_by']              =   $_SESSION["id"];
            $CTDSaveData['last_updated_datetime']        =   date_create()->format('Y-m-d H:i:s');
            


            $CTDSaveData['flow_content']            =   $_POST['flow_content'];
            $crudCTDObj->save($CTDSaveData);
            
            
            $val_assign  = array();

            
            if($CTDInsertId>0){
                //09/06/2023 - Zhiling: we allow user to update labels and not delete the taskConfiguration if this is existing case type
                //$crudObj->run_sql_query('DELETE FROM task_configuration WHERE case_type_id='.$_POST['case_type_id']);
                foreach($dataAman as $k=>$val){
                    if($k!="operator1" && $val->properties->isEditable!=false)
                    {
                        $index = substr($k,17);
                        if(isset($_POST['taskdata'][$index]))
                        {
                            // echo "<script>alert('fcc".$index.$_POST['taskdata'][$index]['task_name']."');</script>";
                            $TCSaveData                                 =   array();
                            $TCSaveData['task_configuration_id']        =   $_POST['taskdata'][$index]['task_configuration_id'];
                            $TCSaveData['case_type_id']                 =   $CTDInsertId;
                            $TCSaveData['parent_task_configuration_id'] =   $_POST['taskdata'][$index]['parent_task_configuration_id'];
                            $TCSaveData['task_name']                    =   $_POST['taskdata'][$index]['task_name'];
                            $TCSaveData['is_decision_task']             =   (in_array($index,$is_Des)==true?1:0);
                            $TCSaveData['isCalendarTask']=0;
                            if($_POST['taskdata'][$index]['assign_to']=="group"){
                                $TCSaveData['task_assigned_to_group']   =   $_POST['taskdata'][$index]['task_assigned_to'];
                                //if task_assiged_to_group is "waiting queue group", set calendar 
                                $group_name  = mysqli_fetch_array(mysqli_query($link,"select group_name from groups where group_id=".$TCSaveData['task_assigned_to_group']));
                                $TCSaveData['isCalendarTask']=0;
                                if ($group_name['group_name']=='Waiting Queue Group')  {
                                    $TCSaveData['isCalendarTask']=1; 
                                } 
                            }else{
                                $TCSaveData['task_assigned_to']         =   $_POST['taskdata'][$index]['task_assigned_to'];
                            }   
                                
                           
                            $TCSaveData['task_form_template_id']        =   $_POST['taskdata'][$index]['form_template'];
                            $TCSaveData['defaultSLA']                   =   $_POST['taskdata'][$index]['defaultSLA'];
                            $TCSaveData['task_descriptions']            =   $_POST['taskdata'][$index]['task_descriptions'];
                            $TCSaveData['isEndTask']                    =   (in_array($index,$is_End)==true?1:0);

                            //zhiling correct created_by, last_updated_by
                            /*
                            if ($_POST['taskdata'][$index]['task_configuration_id']>0){
                                $TCSaveData['created_by']                   =   $_POST['taskdata'][$index]['created_by'];
                                $TCSaveData['created_datetime']             =   $_POST['taskdata'][$index]['created_datetime'];
                            }else{
                                $TCSaveData['created_by']                   =   $created_by;
                                $TCSaveData['created_datetime']             =   $created_datetime;
                            }*/
                            
                            $TCSaveData['last_updated_by']              =   $_SESSION["id"];
                            $TCSaveData['last_updated_datetime']        =   date_create()->format('Y-m-d H:i:s');
                            
                            
                            $val_assign[$index] = $crudTCObj->save($TCSaveData); 
                        }
                        else
                        {
                            // echo "<script>alert('fss".$index.$val->properties->title."');</script>";
                            // echo "<script>alert('".$val->properties->title."');</script>";
                            /*
                            $TCSaveData                                 =   array();
                            $TCSaveData['task_configuration_id']        =   '';
                            $TCSaveData['case_type_id']                 =   $CTDInsertId;
                            $TCSaveData['parent_task_configuration_id'] =   '';
                            $TCSaveData['task_name']                    =   $val->properties->title;
                            $TCSaveData['is_decision_task']             =   0;
                            $TCSaveData['task_form_template_id']        =   0;
                            $TCSaveData['defaultSLA']                   =   0;
                            $TCSaveData['task_descriptions']            =   '';
                            $TCSaveData['isEndTask']                    =   (in_array($index,$is_End)==true?1:0);
                            $TCSaveData['created_by']                   =   $created_by;
                            $TCSaveData['created_datetime']             =   $created_datetime;
                            $val_assign[$index] = $crudTCObj->save($TCSaveData); 
                            */
                        }
                    }
                }
                
                
                foreach($links as $k=>$val){
                    $fromOperator   =   str_replace('created_operator_', '', $val->fromOperator);
                    $toOperator     =   str_replace('created_operator_', '', $val->toOperator);
                    if(!in_array($fromOperator,$is_End))
                    {
                        if($CTDInsertId>0){
                        }else{
                            mysqli_query($link,"update task_configuration set parent_task_configuration_id =".$val_assign[$fromOperator]." where task_configuration_id=".$val_assign[$toOperator]."; ");
                        }
                        
                    }
                }

                foreach($dataAman as $k=>$val){
                    if($k!="operator1" && $val->properties->isEditable!=false){
                        $index = substr($k,17);
                        if(!isset($_POST['taskdata'][$index])){
                            // echo "<script>alert('".$val_assign[$index]."');</script>";
                            //$result = mysqli_query($link,"select * from task_configuration where task_configuration_id=".$val_assign[$index].";");
                            //$res = mysqli_fetch_array($result);
                            // echo "<script>alert('".$res['parent_task_configuration_id']."');</script>";
                            //$result = mysqli_query($link,"select * from task_configuration where task_configuration_id=".$res['parent_task_configuration_id'].";");
                            //$res = mysqli_fetch_array($result);
                            // echo "<script>alert('".($res['task_assigned_to']==null)."');</script>";
                            /*
                            if($res['task_assigned_to_group']==null)
                                mysqli_query($link,"update task_configuration set task_assigned_to =".$res['task_assigned_to']." where task_configuration_id=".$val_assign[$index].";");
                            else
                                mysqli_query($link,"update task_configuration set task_assigned_to_group =".$res['task_assigned_to_group']." where task_configuration_id=".$val_assign[$index].";");
                            mysqli_query($link,"update task_configuration set task_form_template_id =".$res['task_form_template_id']." where task_configuration_id=".$val_assign[$index].";");
                            */
                        }
                    }
                }
                
                $data = [
                    'status'    =>  true,
                    'message'   =>  'Saved Successfully'
                ];
            }
            else{
                $data = [
                    'status'    =>  false,
                    'message'   =>  'Something went wrong. Please try again later.'
                ];    
        }

    }
        // echo "<script>alert('".$_POST['submitFormBtn']."');</script>";
        /*
        $flow_content = (array)json_decode($_POST['flow_content']);
        $links = (array)$flow_content['links'];
        unset($links[0]);

        $inDecisonTask  =   array();
        $dataForSave    =   array();
        if(isset($links) && is_array($links) && count($links)>0){
            foreach($links as $k=>$val){
                $fromOperator   =   str_replace('created_operator_', '', $val->fromOperator);
                $toOperator     =   str_replace('created_operator_', '', $val->toOperator);
                $dataForSave[$fromOperator][$toOperator] = $toOperator;         
            }     
        }
        $dataSet    =   array();
        if(isset($dataForSave) && is_array($dataForSave) && count($dataForSave)>0){
            foreach($dataForSave as $k=>$dt){
                // echo "<script>alert('".sizeof($_POST['taskdata'][$k]).$_POST['taskdata'][$k]['assign_to']."');</>";
                // foreach($_POST['taskdata'][$k] as $zz=>$xx)
                // {
                //     echo "<script>alert('".$zz.$xx."');</script>";
                // }
                // echo "<script>alert('aman');</script>";
                if(isset($_POST['taskdata'][$k]))
                    $dataSet[$k] = $_POST['taskdata'][$k];
                
                if(isset($dt) && is_array($dt) && count($dt)>0){
                    foreach($dt as $kt=>$dtt){ 
                        if(isset($_POST['decisiontaskdata'][$dtt])){
                            $dataSet[$k]['sub'][$dtt] =  $_POST['decisiontaskdata'][$dtt];
                            if(isset($dataForSave[$dtt])){
                                foreach($dataForSave[$dtt] as $ktt=>$dttt){
                                    if(isset($_POST['endtaskdata']['created_operator_'.$dttt]))
                                        $dataSet[$k]['sub'][$dtt]['end'] = 1;
                                    if(isset($_POST['taskdata'][$dttt])){
                                        $dataSet[$k]['sub'][$dtt]['sub'][$dttt] = $_POST['taskdata'][$dttt];
                                        if(isset($dataForSave[$dttt])){
                                            foreach($dataForSave[$dttt] as $kttt=>$dtttt){
                                                if(isset($_POST['endtaskdata']['created_operator_'.$dtttt])){
                                                    $dataSet[$k]['sub'][$dtt]['sub'][$dttt]['end'] = 1;
                                                }
                                            }
                                        }                                    
                                    }
                                }                            
                            }
                        }  
                        if(isset($_POST['endtaskdata']['created_operator_'.$dtt])){
                            $dataSet[$k]['end'] = 1;
                        }                      
                    }   
                }
            }
        }
        if(isset($dataSet) && is_array($dataSet) && count($dataSet)>0){
            $CTDSaveData = array();
            $CTDSaveData['case_type_id']            =   $_POST['case_type_id'];
            $CTDSaveData['case_name']               =   $_POST['name'];
            $CTDSaveData['case_type_description']   =   $_POST['name'];
            $CTDSaveData['effective_begin_date']    =   $_POST['begin_date'];
            $CTDSaveData['effective_end_date']      =   $_POST['end_date'];
            $CTDSaveData['created_by']              =   $created_by;
            $CTDSaveData['created_datetime']        =   $created_datetime;
            $CTDSaveData['flow_content']            =   $_POST['flow_content'];
            
            $CTDInsertId = $_POST['case_type_id'];
            $crudCTDObj->save($CTDSaveData);
            if($CTDInsertId>0){
                $crudObj->run_sql_query('DELETE FROM task_configuration WHERE case_type_id='.$_POST['case_type_id']);
                $parent_task_configuration_id = 0;
                foreach($dataSet as $k=>$d){   
                    $isEndTask = 0;
                    if(isset($d['end']))    $isEndTask = 1;   
                    
                    $task_name = '';
                    if(isset($d['decision_task_name']))    $task_name = $d['decision_task_name'];
                    else if(isset($d['task_name']))        $task_name = $d['task_name'];
                    
                    $TCSaveData                                 =   array();
                    $TCSaveData['task_configuration_id']        =   '';
                    $TCSaveData['case_type_id']                 =   $CTDInsertId;
                    $TCSaveData['parent_task_configuration_id'] =   $parent_task_configuration_id;
                    $TCSaveData['task_name']                    =   $d['task_name'];
                    $TCSaveData['is_decision_task']             =   $d['is_decision_task'];
                    // echo "<script>alert('".$d['task_name'].$d['assign_to']."');</script>";
                    if($d['assign_to']=="group")
                        $TCSaveData['task_assigned_to_group']   =   $d['task_assigned_to'];
                    else
                        $TCSaveData['task_assigned_to']         =   $d['task_assigned_to'];
                    $TCSaveData['defaultSLA']                   =   $d['defaultSLA'];
                    $TCSaveData['task_descriptions']            =   $d['task_descriptions'];
                    $TCSaveData['isEndTask']                    =   $isEndTask;
                    $TCSaveData['created_by']                   =   $created_by;
                    $TCSaveData['created_datetime']             =   $created_datetime;
                    $parent_task_configuration_id = $crudTCObj->save($TCSaveData); 

                    if(isset($d['sub']) && is_array($d['sub'])){
                        foreach($d['sub'] as $kt=>$dt){ 
                            $isEndTask = 0;
                            if(isset($dt['end']))    $isEndTask = 1; 

                            $is_decision_task = 0;
                            if(isset($dtt['is_decision_task']))    $is_decision_task = 1;

                            $task_name = '';
                            if(isset($dt['decision_task_name']))    $task_name = $dt['decision_task_name'];
                            else if(isset($dt['task_name']))        $task_name = $dt['task_name'];

                            $TCSaveData                                 =   array();
                            $TCSaveData['task_configuration_id']        =   '';
                            $TCSaveData['case_type_id']                 =   $CTDInsertId;
                            $TCSaveData['parent_task_configuration_id'] =   $parent_task_configuration_id;
                            $TCSaveData['task_name']                    =   $dt['decision_task_name'];
                            $TCSaveData['is_decision_task']             =   $is_decision_task;
                            if($d['assign_to']=="group")
                                $TCSaveData['task_assigned_to']         =   $dt['task_assigned_to_group'];
                            else
                                $TCSaveData['task_assigned_to']         =   $dt['task_assigned_to'];
                            $TCSaveData['isEndTask']                    =   $isEndTask;
                            $TCSaveData['created_by']                   =   $created_by;
                            $TCSaveData['created_datetime']             =   $created_datetime;  
                            $parent_task_configuration_id = $crudTCObj->save($TCSaveData);  
                            
                            if(isset($dt['sub']) && is_array($dt['sub'])){
                                foreach($dt['sub'] as $ktt=>$dtt){ 
                                    $isEndTask = 0;
                                    if(isset($dtt['end']))    $isEndTask = 1;
                                    
                                    $is_decision_task = 0;
                                    if(isset($dtt['is_decision_task']))    $is_decision_task = 1;
                                    
                                    $task_name = '';
                                    if(isset($dtt['decision_task_name']))    $task_name = $dtt['decision_task_name'];
                                    else if(isset($dtt['task_name']))        $task_name = $dtt['task_name'];

                                    $TCSaveData                                 =   array();
                                    $TCSaveData['task_configuration_id']        =   '';
                                    $TCSaveData['case_type_id']                 =   $CTDInsertId;
                                    $TCSaveData['parent_task_configuration_id'] =   $parent_task_configuration_id;
                                    $TCSaveData['task_name']                    =   $task_name;
                                    $TCSaveData['is_decision_task']             =   $is_decision_task;
                                    $TCSaveData['task_assigned_to']             =   $dtt['task_assigned_to'];
                                    $TCSaveData['isEndTask']                    =   $isEndTask;
                                    $TCSaveData['created_by']                   =   $created_by;
                                    $TCSaveData['created_datetime']             =   $created_datetime;  
                                    $crudTCObj->save($TCSaveData);                                
                                    if(isset($dataSet[$ktt]))   unset($dataSet[$ktt]);
                                }
                            }
                        }   
                    }
                }
                $data = [
                    'status'    =>  true,
                    'message'   =>  'Saved Successfully'
                ];
            }
            else{
                $data = [
                    'status'    =>  false,
                    'message'   =>  'Something went wrong. Please try again later.'
                ];
            }
        }*/
    }


    $case_type_definition   =   $crudObj->FindRow('case_type_definition', array(), array('case_type_id='.$_GET["id"]));
    $task_configuration     =   $crudObj->FindAll('task_configuration', array(), array('case_type_id='.$_GET["id"]), 0, 0, array(array('task_configuration_id', 'ASC')));

    ?><head>
        <title>Case Manager - Workflow</title>
        <?php include 'layouts/head.php'; ?>
        <link rel="stylesheet" type="text/css" href="assets/css/style.css">
        <!-- Responsive datatable examples -->
        <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="assets/flowchart/jquery.flowchart.css" type="text/css">
        <?php include 'layouts/head-style.php'; ?>
        <style>
            .flowchart-example-container {
                width: 100%;
                height: 400px;
                background: white;
                border: 1px solid #CCC;
                margin-bottom: 10px;
            }
            #flowchart_data{
                display:none;
                width: 100%;
                height: 400px;
                background: white;
                border: 1px solid #CCC;
                margin-bottom: 10px;
            }
        </style>
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
                                    <h4 class="mb-sm-0 font-size-18">Workflow</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript:history.go(-1)">Manage Workflow</a></li>
                                            <li class="breadcrumb-item active">Workflow</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php  include 'layouts/alert-messages.php'; ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-sm-0">Add Task Workflow</h5>
                            </div>
                            <div class="card-body">
                                <form class="needs-validation custom-form" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data"> 
                                
                                    <input class="form-control" type="hidden" name="case_type_id" id="case_type_id"  value="<?php echo $case_type_definition['case_type_id']; ?>" placeholder="Title" />
                                    <input class="form-control" type="hidden" name="created_by" id="created_by"  value="<?php echo $case_type_definition['created_by']; ?>" placeholder="created_by" />
                                    <input class="form-control" type="hidden" name="created_datetime" id="created_datetime"  value="<?php echo $case_type_definition['created_datetime']; ?>" placeholder="created_datetime" />
                                    
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="agency" class="form-label">Name</label>
                                            <input class="form-control" type="text" name="name" id="name"  value="<?php echo $case_type_definition['case_name']; ?>" placeholder="Title" />
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="begin_date" class="form-label">Begin Date</label>
                                            <input class="form-control" type="date" name="begin_date"  value="<?php echo $case_type_definition['effective_begin_date']; ?>" id="begin_date"
                                                placeholder="Title" />
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input class="form-control" type="date" name="end_date"  value="<?php echo $case_type_definition['effective_end_date']; ?>" id="end_date"
                                                placeholder="Title" />
                                        </div>
                                    </div>
                                    <label class="form-label mt-4">Task Work Flow</label>
                                    <div id="chart_container">
                                        <div class="flowchart-example-container" id="flowchartworkspace"></div>
                                    </div>

                                    <input type="button" onclick="OpenCreateOperator();" class="btn btn-primary" value="Add Task" />
                                    <input type="button" onclick="OpenDecisionCreateOperator();" class="btn btn-info" value="Add Decision Task" />
                                    <input type="button" onclick="OpenSubDecisionCreateOperator();" class="btn btn-secondary" value="Add Decision Text" />
                                    <input type="button" class="end_create_operator btn btn-warning" value="End Task" />
                                    <input type="button" class="delete_selected_button btn btn-danger" value="Delete Selected Task / Connection">
                                    <input type="submit" class="btn btn-success" name="submitFormBtn" value="Submit" />
                                    
                                    <?php
                                        $fx = $case_type_definition['flow_content'];
                                    ?>

                                    <div><textarea id="flowchart_data" name="flow_content"><?php echo $fx ?></textarea></div>
                                    <div id="task-and-decision-work-flow"><?php    
    if(isset($task_configuration) && is_array($task_configuration) && count($task_configuration)>0){
        $ar = array();
        $old_database = (array)json_decode($fx);
        $oper_data = (array)$old_database['operators'];
        $link_data = (array)$old_database['links'];
        foreach($oper_data as $k=>$val)
        {
            if($k!="operator1"){
                $index = substr($k,17);
                if($val->properties->isEditable!=false)
                {
                    // echo "<script>alert('".$val->properties->dataclass."');</script>";
                    array_push($ar,$index);                    
                    // echo "<script>alert('".$index."');</script>";   
                }

            }
        }
        // echo "<script>alert('".sizeof($ar)."');</script>";     
        $i=0;
        foreach($task_configuration as $tc){
            // echo "<script>alert('".($oper_data['created_operator_'.$ar[$i]]->properties->dataclass)."');</script>";
            //echo "<script>alert($ar[$i]);</script>";
            $task_operator_id=$ar[$i];
            $i++;
            // echo "<script>alert('".$task_operator_id." ".$tc['task_name']."');</scri>";
            // $task_operator_id=$task_operator_id>=4?$task_operator_id+1:$task_operator_id;
            // echo "<script>alert('".($tc['task_assigned_to_group']==null?$tc['task_assigned_to']:$tc['task_assigned_to_group'])."');</script>";
            // $tc['task_assign']=($tc['task_assigned_to_group']==null?"user":"group");
            if($oper_data['created_operator_'.$task_operator_id]->properties->dataclass!="bg-secondary text-white editable")
            {
                // echo "<script>alert('".$task_operator_id."');</script>";
            ?><div id="task-operator-id-<?php echo $task_operator_id; ?>">
                
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="is_decision_task" name="taskdata[<?php echo $task_operator_id; ?>][is_decision_task]" value="<?php echo $tc['is_decision_task']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="task_name" name="taskdata[<?php echo $task_operator_id; ?>][task_name]" value="<?php echo $tc['task_name']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="assign_to" name="taskdata[<?php echo $task_operator_id; ?>][assign_to]" value="<?php echo ($tc['task_assigned_to_group']==null?"user":"group") ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="task_assigned_to" name="taskdata[<?php echo $task_operator_id; ?>][task_assigned_to]" value="<?php echo ($tc['task_assigned_to_group']==null?$tc['task_assigned_to']:$tc['task_assigned_to_group']); ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="defaultSLA" name="taskdata[<?php echo $task_operator_id; ?>][defaultSLA]" value="<?php echo $tc['defaultSLA']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="task_descriptions" name="taskdata[<?php echo $task_operator_id; ?>][task_descriptions]" value="<?php echo $tc['task_descriptions']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="form_template" name="taskdata[<?php echo $task_operator_id; ?>][form_template]" value="<?php echo $tc['task_form_template_id']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="task_configuration_id" name="taskdata[<?php echo $task_operator_id; ?>][task_configuration_id]" value="<?php echo $tc['task_configuration_id']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="parent_task_configuration_id" name="taskdata[<?php echo $task_operator_id; ?>][parent_task_configuration_id]" value="<?php echo $tc['parent_task_configuration_id']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="created_by" name="taskdata[<?php echo $task_operator_id; ?>][created_by]" value="<?php echo $tc['created_by']; ?>"/>
                <input type="hidden" class="taskdata_<?php echo $task_operator_id; ?>" data-index="created_datetime" name="taskdata[<?php echo $task_operator_id; ?>][created_datetime]" value="<?php echo $tc['created_datetime']; ?>"/>
                
                <?php 
            ?></div><?php
            }
            else
            {
                // echo "<script>alert('sdf');</script>";
                ?>
                <!-- <div id="task-operator-id-'+decision_task_operator_id+'">
                <input type="hidden" name="decisiontaskdata['+decision_task_operator_id+'][decision_task_name]" id="aman_task_name'+decision_task_operator_id+'" value="'+decision_task_name+'"/>
                </div> -->
                <?php
            }
        }
    }
                                    ?></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Page-content -->
                <?php include 'layouts/footer.php'; ?>
            </div>
            <!-- end main content-->
        </div>
        <!-- END layout-wrapper -->

        <div class="modal" id="AddTaskModal" tabindex="-1" role="dialog" aria-labelledby="AddTaskModal" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Task Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12 mb-4">
                                <label for="agency" class="form-label">Task Name</label>
                                <input class="form-control" type="hidden" name="is_decision_task" id="is_decision_task" value="0" />
                                <input class="form-control" type="text" name="task_name" id="task_name" placeholder="Enter Task Name" />
                            </div>
                            <div class="col-sm-6 mb-4">
                                <label for="agency" class="form-label">Assign To</label>
                                <input type="radio" value="user" name="assign_to" checked id="user_assign">User
                                <input type="radio" value="group" name="assign_to" id="group_assign"> Group
                                <select class="form-control" name="task_assigned_to" id="task_assigned_to">
                                    <option value="">Select User</option><?php
                                if(isset($assignUsers) && is_array($assignUsers) && count($assignUsers)>0){
                                    foreach($assignUsers as $usr){
                                        $uname      =   '';
                                        $uname_arr  =   array();
                                        if(!empty($usr['fname']))   $uname_arr[] = $usr['fname'];
                                        if(!empty($usr['mname']))   $uname_arr[] = $usr['mname'];
                                        if(!empty($usr['lname']))   $uname_arr[] = $usr['lname'];
                                        if(isset($uname_arr) && is_array($uname_arr) && count($uname_arr)>0)
                                            $uname = implode(' ', $uname_arr);
                                        ?><option value="<?php echo $usr['id']; ?>"><?php echo $uname; ?></option><?php
                                    }
                                }
                                ?></select>
                            </div>
                            <div class="col-sm-6 mb-4">
                                <label for="agency" class="form-label">Default SLA</label>
                                <input class="form-control" type="text" name="defaultSLA" id="defaultSLA" placeholder="Enter Default SLA" />
                            </div>
                            <div class="col-sm-12 mb-4">
                                <label for="agency" class="form-label">Task Descriptions</label>
                                <input class="form-control" type="text" name="task_descriptions" id="task_descriptions" placeholder="Enter Task Descriptions" />
                            </div>
                            <div class="col-sm-12 mb-4">
                                <label for="agency" class="form-label">This task requires to fill form:</label>
                                <select class="form-control" name="form_template" id="form_template" required>
                                    <option value="0">Select Form Template</option><?php
                                    $form_template_data = mysqli_query($link,"select * from form_template where organization_id in (select organization_id from users where id='".$_SESSION['id']."') ");
                                    while($ans = mysqli_fetch_array($form_template_data)){
                                        ?>
                                        <option value="<?php echo $ans['form_template_id']; ?>"><?php echo $ans['form_template_name']; ?></option>
                                        <?php
                                    }
                                ?></select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="task_operator_id" id="task_operator_id" />
                        <input type="hidden" id="for_edit" value="2"/>
                        <input type="button" class="btn btn-success" name="submitFormBtn" value="Save Task" onclick="setTaskDetailsData();"/>
                    </div>
                </div>
            </div>  
        </div>

        <div class="modal" id="AddDecisionTaskModal" tabindex="-1" role="dialog" aria-labelledby="AddDecisionTaskModal" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Decision Task Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label for="agency" class="form-label">Decision Task Name</label>
                                <input class="form-control" type="text" name="decision_task_name" id="decision_task_name" placeholder="Enter Decision Task Name" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="decision_task_operator_id" id="decision_task_operator_id" />
                        <input type="hidden" id="for_edit_decision" value="2"/>
                        <input type="button" class="btn btn-success" name="submitFormBtn" value="Save Decision Task" onclick="setDecisionTaskDetailsData();"/>
                    </div>
                </div>
            </div>  
        </div>

        <!-- Right Sidebar -->
        <?php include 'layouts/right-sidebar.php'; ?>
        <!-- /Right-bar -->

        <!-- JAVASCRIPT -->
        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- App js -->
        <script src="assets/js/app.js"></script>


        <link rel="stylesheet" type="text/css"
            href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="assets/flowchart/jquery.flowchart.js"></script>
        <script type="text/javascript">
            /* global $ */
            // var operatorI = <?php //echo $task_operator_id; ?>;
            
            var operatorI = <?php echo max($ar) ?>+1;
            var dtd = [];
            var link_aman=[];
            var $flowchart = $('#flowchartworkspace');
            var $container = $flowchart.parent();
            $(document).ready(function () {              

                fx_a_data = JSON.parse($('#flowchart_data').val());
                for(key in fx_a_data["operators"])
                {
                    if(key!="operator1" && fx_a_data["operators"][key]["properties"]["dataclass"]=="bg-secondary text-white editable")
                    {
                        // alert(fx_a_data["operators"][key]["properties"]["title"]);
                        // alert(key.slice(17));
                        dtd.push(key.slice(17));
                    }
                }

                // Apply the plugin on a standard, empty div...
                $flowchart.flowchart({
                    data: defaultFlowchartData,
                    defaultSelectedLinkColor: '#000055',
                    grid: 10,
                    multipleLinksOnInput: true,
                    multipleLinksOnOutput: true
                });
                function getOperatorData($element) {
                    var nbInputs = parseInt($element.data('nb-inputs'), 10);
                    var nbOutputs = parseInt($element.data('nb-outputs'), 10);
                    var data = {
                        properties: {
                            title: $element.text(),
                            inputs: {},
                            outputs: {}
                        }
                    };
                    var i = 0;
                    for (i = 0; i < nbInputs; i++) {
                        data.properties.inputs['input_' + i] = {
                            label: 'Input ' + (i + 1)
                        };
                    }
                    for (i = 0; i < nbOutputs; i++) {
                        data.properties.outputs['output_' + i] = {
                            label: 'Output ' + (i + 1)
                        };
                    }
                    return data;
                }

                //-----------------------------------------
                //--- operator and link properties
                //--- start
                var $operatorProperties = $('#operator_properties');
                $operatorProperties.hide();
                var $linkProperties = $('#link_properties');
                $linkProperties.hide();
                var $operatorTitle = $('#operator_title');
                var $linkColor = $('#link_color');
                $flowchart.flowchart({
                    onOperatorSelect: function (operatorId) {
                        $operatorProperties.show();
                        $operatorTitle.val($flowchart.flowchart('getOperatorTitle', operatorId));
                        return true;
                    },
                    onOperatorUnselect: function () {
                        $operatorProperties.hide();
                        return true;
                    },
                    onLinkSelect: function (linkId) {
                        $linkProperties.show();
                        $linkColor.val($flowchart.flowchart('getLinkMainColor', linkId));
                        return true;
                    },
                    onLinkUnselect: function () {
                        $linkProperties.hide();
                        return true;
                    }
                });
                $operatorTitle.keyup(function () {
                    var selectedOperatorId = $flowchart.flowchart('getSelectedOperatorId');
                    if (selectedOperatorId != null) {
                        $flowchart.flowchart('setOperatorTitle', selectedOperatorId, $operatorTitle.val());
                    }
                });
                $linkColor.change(function () {
                    var selectedLinkId = $flowchart.flowchart('getSelectedLinkId');
                    if (selectedLinkId != null) {
                        $flowchart.flowchart('setLinkMainColor', selectedLinkId, $linkColor.val());
                    }
                });
                //--- end
                //--- operator and link properties
                //-----------------------------------------

                //-----------------------------------------
                //--- delete operator / link button
                //--- start
                $flowchart.parent().siblings('.delete_selected_button').click(function () {
                    $flowchart.flowchart('deleteSelected');
                    $('#task-operator-id-'+operatorI).remove();
                    Flow2Text();
                });
                //--- end
                //--- delete operator / link button
                //-----------------------------------------


                $flowchart.parent().siblings('.end_create_operator').click(function () {
                    operatorI++;
                    var operatorId = 'created_operator_' + operatorI;
                    var operatorData = {
                        top: ($flowchart.height()/2)-30,
                        left: ($flowchart.width()/2)-100+(operatorI*10),
                        properties: {
                            title: 'End Point',
                            dataindex: operatorId,
                            isEditable:false,
                            dataclass: 'border-white bg-warning text-white border-white',
                            borderclass: 'border-warning bg-white text-black customwidth ',
                            inputs: {
                                input_1: {
                                    label: 'End',
                                }
                            }
                        }
                    };            
                    $flowchart.flowchart('createOperator', operatorId, operatorData);

                    htmldata  = '<div id="task-operator-id-'+operatorId+'">';
                    htmldata += '<input type="hidden" name="endtaskdata['+operatorId+']" value="end"/>';
                    htmldata += '</div>';
                    $('#task-and-decision-work-flow').append(htmldata); 
                });
                //--- end
                //--- create operator button
                //-----------------------------------------

                //-----------------------------------------
                //--- draggable operators
                //--- start
                //var operatorId = 0;
                var $draggableOperators = $('.draggable_operator');
                $draggableOperators.draggable({
                    cursor: "move",
                    opacity: 0.7,
                    appendTo: 'body',
                    zIndex: 1000,

                    helper: function (e) {
                        var $this = $(this);
                        var data = getOperatorData($this);
                        return $flowchart.flowchart('getOperatorElement', data);
                    },
                    stop: function (e, ui) {
                        var $this = $(this);
                        var elOffset = ui.offset;
                        var containerOffset = $container.offset();
                        if(elOffset.left > containerOffset.left &&
                            elOffset.top > containerOffset.top &&
                            elOffset.left < containerOffset.left + $container.width() &&
                            elOffset.top < containerOffset.top + $container.height()){

                            var flowchartOffset = $flowchart.offset();

                            var relativeLeft = elOffset.left - flowchartOffset.left;
                            var relativeTop = elOffset.top - flowchartOffset.top;

                            var positionRatio = $flowchart.flowchart('getPositionRatio');
                            relativeLeft /= positionRatio;
                            relativeTop /= positionRatio;

                            var data = getOperatorData($this);
                            data.left = relativeLeft;
                            data.top = relativeTop;

                            $flowchart.flowchart('addOperator', data);
                        }
                    }
                });
                //--- end
                //--- draggable operators
                //-----------------------------------------

                //-----------------------------------------
                //--- save and load
                //--- start
                function Flow2Text() {
                    var data = $flowchart.flowchart('getData');
                    console.log(data);
                    console.log("fx");
                    $('#flowchart_data').val(JSON.stringify(data, null, 2));

                    temp_dtd=[]
                    for(x of dtd)
                    {
                        if(data["operators"].hasOwnProperty("created_operator_"+x))
                            temp_dtd.push(x);
                    }
                    dtd=temp_dtd;
                    
                    for(key in data["links"])
                    {
                        // console.log(key+" "+data["links"][key]["toOperator"]);
                        var id=data["links"][key]["toOperator"].slice(17);
                        var main_id = data["links"][key]["fromOperator"].slice(17);
                        if(dtd.includes(id) && link_aman.indexOf(key)==-1)
                        {
                            // console.log(link_aman.indexOf(key));
                            var fx = JSON.parse($('#flowchart_data').val());
                            console.log(fx["operators"]["created_operator_"+main_id]["properties"]["body"]);
                            fx["operators"]["created_operator_"+id]["properties"]["body"]=fx["operators"]["created_operator_"+main_id]["properties"]["body"];
                            link_aman.push(key);
                            $flowchart.flowchart('setData', fx);
                        }
                    }

                }
                $('#get_data').click(Flow2Text);

                function Text2Flow() {
                    var data = JSON.parse($('#flowchart_data').val());
                    console.log(data);
                    console.log("xx");
                    $flowchart.flowchart('setData', data);
                }
                $('#set_data').click(Text2Flow);

                //--- end
                //--- save and load
                //-----------------------------------------

                $('#chart_container').click(Flow2Text);
                $('#chart_container').mouseover(Flow2Text);

                Text2Flow();

            });
            var defaultFlowchartData = {
                operators: {
                    operator1: {
                        top: 20,
                        left: 20,
                        properties: {
                            title: 'Start Point',
                            dataindex: 'StartPoint',
                            dataclass: 'bg-success text-white',
                            borderclass: 'border-success bg-white',
                            isEditable:false,
                            inputs: {},
                            outputs: {
                                output_1: {
                                    label: 'Start',
                                }
                            }
                        }
                    },
                },
                links: {
                }
            };
            if(false) console.log('remove lint unused warning', defaultFlowchartData);

            function OpenCreateOperator(){
                $('#for_edit').val('2');
                operatorI++;
                $('#AddTaskModal').modal('show');
                $('#task_name').val('');
                $('#task_assigned_to').val('');
                $('#defaultSLA').val('');
                $('#task_descriptions').val(''); 
                $('#task_operator_id').val(operatorI); 
                $('#form_template').val('0');
                $('#is_decision_task').val('0');   
            }
            function OpenDecisionCreateOperator(){
                operatorI++;
                $('#for_edit').val('2');
                $('#AddTaskModal').modal('show');
                $('#task_name').val('');
                $('#task_assigned_to').val('');
                $('#defaultSLA').val('');
                $('#task_descriptions').val(''); 
                $('#form_template').val('0'); 

                $('#task_operator_id').val(operatorI);
                $('#is_decision_task').val('1');    
            }
            function OpenSubDecisionCreateOperator(){
                operatorI++;
                $('#for_edit_decision').val('2');
                $('#AddDecisionTaskModal').modal('show');
                $('#decision_task_name').val('');
                $('#decision_task_operator_id').val(operatorI);   
            }
            function setTaskDetailsData(){
                if($('#task_name').val()==''){
                    $('#task_name').addClass('border-danger');
                    return true;
                }
                if($('#task_assigned_to').val()==''){
                    $('#task_assigned_to').addClass('border-danger');
                    return true;
                }
                var htmldata = '';
                var task_operator_id    =   $('#task_operator_id').val();
                var is_decision_task    =   $('#is_decision_task').val();
                var task_name           =   $('#task_name').val();
                var task_assigned_to    =   $('#task_assigned_to').val();
                var assign_to           =   $("input[name='assign_to']:checked").val();
                var form_template       =   $('#form_template').val();
                var defaultSLA          =   $('#defaultSLA').val();
                var task_descriptions   =   $('#task_descriptions').val();        
                htmldata  = '<div id="task-operator-id-'+task_operator_id+'">';
                    htmldata += '<input type="hidden" class="taskdata_'+task_operator_id+'" data-index="is_decision_task" name="taskdata['+task_operator_id+'][is_decision_task]" value="'+is_decision_task+'"/>';
                    htmldata += '<input type="hidden" class="taskdata_'+task_operator_id+'" data-index="task_name" name="taskdata['+task_operator_id+'][task_name]" value="'+task_name+'"/>';
                    htmldata += '<input type="hidden" class="taskdata_'+task_operator_id+'" data-index="assign_to" name="taskdata['+task_operator_id+'][assign_to]" value="'+assign_to+'"/>';
                    htmldata += '<input type="hidden" class="taskdata_'+task_operator_id+'" data-index="task_assigned_to" name="taskdata['+task_operator_id+'][task_assigned_to]" value="'+task_assigned_to+'"/>';
                    htmldata += '<input type="hidden" class="taskdata_'+task_operator_id+'" data-index="defaultSLA" name="taskdata['+task_operator_id+'][defaultSLA]" value="'+defaultSLA+'"/>';
                    htmldata += '<input type="hidden" class="taskdata_'+task_operator_id+'" data-index="task_descriptions" name="taskdata['+task_operator_id+'][task_descriptions]" value="'+task_descriptions+'"/>';
                    htmldata += '<input type="hidden" class="taskdata_'+task_operator_id+'" data-index="form_template" name="taskdata['+task_operator_id+'][form_template]" value="'+form_template+'"/>';
                htmldata += '</div>';
                // console.log(htmldata);
                $('#task-and-decision-work-flow').append(htmldata); 
                $('#AddTaskModal').modal('hide');
                
                if(is_decision_task == '0')  {
                    var dataclass = 'bg-primary text-white';
                    var borderclass = 'border-primary bg-white';
                }
                else{
                    var dataclass = 'bg-info text-white';
                    var borderclass = 'border-info bg-white';
                }
                // alert($('#for_edit').val());
                if($('#for_edit').val()=='2'){
                    // alert('aman');
                    var operatorId = 'created_operator_' + operatorI;
                    var operatorData = {
                        top: ($flowchart.height()/2)-30,
                        left: ($flowchart.width()/2)-100+(operatorI*10),
                        properties: {
                            title: task_name,
                            dataindex: operatorId,
                            dataclass: dataclass,
                            borderclass: borderclass,
                            isEditable: true,
                            body: 'Assigned To: '+$('#task_assigned_to option:selected').text(),
                            inputs: {
                                input_1: {
                                    label: 'IN',
                                }
                            },
                            outputs: {
                                output_1: {
                                    label: 'OUT',
                                }
                            }
                        }
                    };        
                    $flowchart.flowchart('createOperator', operatorId, operatorData);
                }
                else{
                    $('#created_operator_'+task_operator_id).html(task_name);
                    $('.created_operator_'+task_operator_id+'_assigned').html('Assigned To: '+$('#task_assigned_to option:selected').text()); 
                    console.log("aman");

                    data = JSON.parse($('#flowchart_data').val());
                    data["operators"]["created_operator_"+task_operator_id]["properties"]["body"]="Assigned To: "+$('#task_assigned_to option:selected').text();
                    data["operators"]["created_operator_"+task_operator_id]["properties"]["title"]=task_name;
                    
                    for(key in data["links"])
                    {
                        console.log(key+" "+data["links"][key]["toOperator"]);
                        var id=data["links"][key]["toOperator"].slice(17);
                        if(dtd.includes(id) && task_operator_id==data["links"][key]["fromOperator"].slice(17))
                        {
                            data["operators"]["created_operator_"+id]["properties"]["body"]="Assigned To: "+$('#task_assigned_to option:selected').text();
                            console.log(data["operators"]["created_operator_"+id]["properties"]["body"]);
                            // alert('aman');
                        }
                    }

                    $flowchart.flowchart('setData', data); 
                }                
            }
            function setDecisionTaskDetailsData(){
                var htmldata = '';        
                var decision_task_operator_id   =   $('#decision_task_operator_id').val();
                var decision_task_name          =   $('#decision_task_name').val();
                
                if($('#for_edit_decision').val()=='2')
                {
                    htmldata  = '<div id="task-operator-id-'+decision_task_operator_id+'">';
                    htmldata += '<input type="hidden" name="decisiontaskdata['+decision_task_operator_id+'][decision_task_name]" value="'+decision_task_name+'"/>';
                    htmldata += '</div>';
                    $('#task-and-decision-work-flow').append(htmldata); 
                    $('#AddDecisionTaskModal').modal('hide'); 
                    dtd.push(decision_task_operator_id);
                    
                    var operatorId = 'created_operator_' + operatorI;
                    var operatorData = {
                        top: ($flowchart.height()/2)-30,
                        left: ($flowchart.width()/2)-100+(operatorI*10),
                        properties: {
                            title: decision_task_name,
                            dataindex: operatorId,
                            dataclass: 'bg-secondary text-white editable',
                            borderclass: 'border-secondary bg-white',
                            isEditable: true,
                            body: 'Assigned To: None',
                            inputs: {
                                input_1: {
                                    label: 'IN',
                                }
                            },
                            outputs: {
                                output_1: {
                                    label: 'OUT',
                                }
                            }
                        }
                    };            
                    $flowchart.flowchart('createOperator', operatorId, operatorData);
                }
                else{
                    var data = JSON.parse($('#flowchart_data').val());
                    // alert(decision_task_operator_id);
                    data["operators"]["created_operator_"+decision_task_operator_id]["properties"]["title"]=$('#decision_task_name').val();
                    // alert();
                    $flowchart.flowchart('setData', data);
                }
                $('#AddDecisionTaskModal').modal('hide');
            }
            function isEditable(dataindex){
                var operator_id = dataindex.replace('created_operator_', '');
                if(dtd.includes(operator_id))
                {
                    $('#AddDecisionTaskModal').modal('show');
                    $('#decision_task_operator_id').val(operator_id);
                    var data = JSON.parse($('#flowchart_data').val());
                    $('#decision_task_name').val(data["operators"]["created_operator_"+operator_id]["properties"]["title"]);
                    $('#for_edit_decision').val('1');
                }
                else
                {
                    $('#AddTaskModal').modal('show');
                    $('#task_operator_id').val(operator_id);
                    $('#for_edit').val('1');
                    // alert(operator_id);
                    $('.taskdata_'+operator_id).each(function(){
                        var dataSetIndex = $(this).attr('data-index');
                        if(dataSetIndex=="assign_to")
                        {
                            if($(this).val()=="group")
                            {
                                $('#user_assign').attr('checked',false);
                                $('#group_assign').attr('checked',true);
                                select_group();
                            }
                            else
                            {
                                $('#group_assign').attr('checked',false);
                                $('#user_assign').attr('checked',true);
                                select_user();
                            }
                        }
                        $('#'+dataSetIndex).val($(this).val());
                    });
                }
                    // alert('for edit'+$('#for_edit').val());
            }
        </script>

<script>
            $('#user_assign').click(function(){
                $('#task_assigned_to').find('option').remove().end();
                $('#task_assigned_to').append(new Option("Select User",""));
                <?php
                    $fx = mysqli_query($link,"select * from users");
                    while($xx=mysqli_fetch_array($fx))
                    {
                        ?>
                        $('#task_assigned_to').append(new Option("<?php echo $xx['fname']." ".$xx['mname']." ".$xx['lname'] ?>","<?php echo $xx['id'] ?>"));
                        <?php
                    }
                ?>
            })
            $('#group_assign').click(function(){
                $('#task_assigned_to').find('option').remove().end();
                $('#task_assigned_to').append(new Option("Select Group",""));
                <?php
                    $fx = mysqli_query($link,"select * from groups");
                    while($xx=mysqli_fetch_array($fx))
                    {
                        ?>
                        $('#task_assigned_to').append(new Option("<?php echo $xx['group_name'] ?>","<?php echo $xx['group_id'] ?>"));
                        <?php
                    }
                ?>
            })


            function select_user()
            {
                $('#task_assigned_to').find('option').remove().end();
                $('#task_assigned_to').append(new Option("Select User",""));
                <?php
                    $fx = mysqli_query($link,"select * from users");
                    while($xx=mysqli_fetch_array($fx))
                    {
                        ?>
                        $('#task_assigned_to').append(new Option("<?php echo $xx['fname']." ".$xx['mname']." ".$xx['lname'] ?>","<?php echo $xx['id'] ?>"));
                        <?php
                    }
                ?>
            }
            function select_group()
            {
                $('#task_assigned_to').find('option').remove().end();
                $('#task_assigned_to').append(new Option("Select Group",""));
                <?php
                    $fx = mysqli_query($link,"select * from groups");
                    while($xx=mysqli_fetch_array($fx))
                    {
                        ?>
                        $('#task_assigned_to').append(new Option("<?php echo $xx['group_name'] ?>","<?php echo $xx['group_id'] ?>"));
                        <?php
                    }
                ?>
            }
        </script>
<!-- Keep Nav Menu Open and Highlight link -->    
<script>
        $('#mm-admin').attr('aria-expanded', true);
        $('#mm-admin').addClass('mm-show')
        $('#mm-admin').css('height', 'auto')
        $('#mm-admin').parent().addClass('mm-active');
        $($('#mm-admin').children()[5]).children().eq(0).css('color', '#1c84ee');
    </script>
    </body>
</html>