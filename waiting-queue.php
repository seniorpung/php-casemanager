<?php
    // Include config file
    require_once "layouts/config.php";
    include 'layouts/head-main.php';
    include 'layouts/session.php'; 
    include 'modules/api_services/calling_services.php';

    global $link;
    include 'class.crud.php';
    $org_id = Trim($_SESSION["organization_id"]);

    if(isset($_POST['confirm_btn']))
    {
        $assign_id=$_POST['id'];
        $this_task_id=$_POST['id'];
        $updatedByUserId=$_SESSION['id'];
        // if you have case_id, that's the best to pass the real caseId.  if you don't have caseId, just pass 0
        $input_caseId=0;
        $res=call_service_update_task_status ($input_caseId, $this_task_id, 2, $updatedByUserId);
        if ($res==1){
            $info="Successfully Updated,  Thank you";
        }else{
            $info="There is a error in updating.  Please see your admin";
        }
       
    }

    $crudObj = new CRUD('tasks', 'case_id');
    $crudObj->mysqli = $link;

    $colsData = array();
    $colsData[] = '*';
    $colsData[] = '(SELECT task_name FROM task_configuration WHERE task_configuration.task_configuration_id=tasks_view.task_cofiguration_id) AS task_name';
    $colsData[] = '(SELECT defaultSLA FROM task_configuration WHERE task_configuration.task_configuration_id=tasks_view.task_cofiguration_id) AS defaultSLA';
    
    $colsData[] = '(SELECT fname FROM cases_view WHERE cases_view.case_id=tasks_view.case_id) AS person_first_name';
    $colsData[] = '(SELECT mname FROM cases_view WHERE cases_view.case_id=tasks_view.case_id) AS person_middle_name';
    $colsData[] = '(SELECT lname FROM cases_view WHERE cases_view.case_id=tasks_view.case_id) AS person_last_name';
    $colsData[] = '(SELECT case_type_name FROM cases_view WHERE cases_view.case_id=tasks_view.case_id) AS case_type_name';
    $colsData[] = '(SELECT created_datetime FROM cases_view WHERE cases_view.case_id=tasks_view.case_id) AS case_created_datetime';
    $colsData[] = '(SELECT contact_name FROM cases_view WHERE cases_view.case_id=tasks_view.case_id) AS contact_name';
    
    //Check for system waiting group
    $group_id=mysqli_query($link,'select group_id from groups where group_name = "Waiting Queue Group" and organization_id ='.$org_id.';');
    $group_name=array();
    //echo "<script>alert('".$_SESSION['id']."');</script>";
    
    // $records = $crudObj->FindAll('tasks', $colsData, array('assigned_to_group='.$group_id['group_id'].'\'', 'task_status_id=\'1\''), 0, 0, array(array('task_id', 'DESC')));
    
    // $records = $crudObj->FindAll('tasks', $colsData, array('assigned_to_group='.$group_id['group_id'], 'task_status_id=\'1\''), 0, 0, array(array('task_id', 'DESC')));
    //'assigned_to_group='.$group_id['group_id']
    $str="(";
    while($ff=mysqli_fetch_array($group_id))
    {
        $str=$str.$ff['group_id'].',';
        $x=mysqli_fetch_array(mysqli_query($link,"select group_name from groups where group_id=".$ff['group_id']));
        $group_name[$ff['group_id']]=$x['group_name'];
    }
    if($str[strlen($str)-1]==',')
        $str=substr($str,0,-1);
    $str=$str.")";
    //echo "<script>alert('".$str."');</script>";
    // Zhiling: change to show assigned_to is 0 or assigned_to null
    $records = $crudObj->FindAll('tasks_view',$colsData, array('ifnull(assigned_to,0) = 0','assigned_to_group in '.$str, 'task_status_id=\'1\''), 0, 0, array(array('task_id', 'DESC')));
    
    ?><head>
        <title>Case Manager - Waiting Queue</title>
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
                                <h4 class="mb-sm-0 font-size-18">Waiting Queue</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                            <li class="breadcrumb-item active">Waiting Queue</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <div class="row">
                            <div class="col-12">
                                <table id="datatable" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">
                                    <thead>
                                        <tr>                                            
                                            <th></th>
                                            <th>Id</th>
                                            <th>Contact Name</th>
                                            <th>Appointment Time</th>
                                            <th>Task</th>
                                            <th>Process Type</th>
                                            <th>Requester Name</th>
                                                                              
                                        </tr>
                                    </thead>
                                    <tbody><?php
                                    if(isset($records) && count($records)>0){
                                        foreach($records as $record){
                                            $nameArr = array();
                                            if(isset($record['person_first_name']) && !empty($record['person_first_name']))
                                                $nameArr[] = $record['person_first_name'];
                                            if(isset($record['person_middle_name']) && !empty($record['person_middle_name']))
                                                $nameArr[] = $record['person_middle_name'];
                                            if(isset($record['person_last_name']) && !empty($record['person_last_name']))
                                                $nameArr[] = $record['person_last_name'];
                                            ?><tr>
                                                <td>
                                                    <i  title="click to check in" style="cursor:pointer;" onclick="assign(<?php echo $record['task_id'] ?>)" class="mdi mdi-alarm-check text-primary fa-lg "></i>
                                                </td>
                                                <td><?php echo $record['task_id']; ?></td>
                                                <td><?php echo $record['contact_name']; ?></td>
                                                <td><?php
                                                if(isset($record['start_datetime']))
                                                 echo date('m/d/Y h:i A', strtotime($record['start_datetime'])); 
                                                 ?>
                                                </td>
                                                <td><?php echo $record['task_name']; ?></td>
                                                <td><?php echo $record['case_type_name']; ?></td>
                                                <td><?php echo implode(' ', $nameArr); ?></td>
                                                                                                
                                            </tr><?php
                                        } 
                                    }
                                    ?></tbody>
                                </table>
                                <!-- end cardaa -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->       
                        
                        <!--Assign ModaL-->
                        <div class="modal" id="assignmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h4 class="modal-title">Confirmation</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                                    </div>
                                    <!-- Modal body -->
                                    <div class="modal-body">
                                        <h5 class="confirmation_msg"></h5>
                                        <div class="mb-3 mt-5">
                                            <form method="post">
                                                <input type="hidden" class="id" name="id">
                                                <button class="btn btn-primary waves-effect waves-light w-25 float-left" name="confirm_btn">Confirm</button>
                                                <button type="button" class="btn btn-secondary w-25 float-right" data-bs-dismiss="modal">No</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include 'layouts/footer.php'; ?>
            </div>
            <!-- end main content-->
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
        <!-- Session timeout js -->
<script src="assets/libs/@curiosityx/bootstrap-session-timeout/index.js"></script>

<!-- Session timeout init js -->
<script src="assets/js/pages/session-timeout.init.js"></script>

        <script>
            function assign(val)
            {
                
                $('#assignmodal').modal('toggle');
                $('#assignmodal').modal('show');
                $('.confirmation_msg').html("click Confirm button to check-in, Or click No to Cancel. ");
                $(".id").val(val);
            }
        </script>
    </body>
</html>