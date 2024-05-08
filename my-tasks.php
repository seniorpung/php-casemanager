<?php
    // Include config file
    require_once "layouts/config.php";
    include 'layouts/head-main.php';
    include 'layouts/session.php'; 
    $_SESSION['fx'] = 0;
    global $link;
    include 'class.crud.php';
   
    // Define variables 
    $whereConditionStr = '';
    

    //Build query 
    $whereConditionStr = 'Where assigned_to= '.$_SESSION["id"].' And task_status_id In (1,3)';      
    $sql = "SELECT case_id, task_id, case_number, task_name, requester_name, created_datetime, assigned_person, task_status_name, defaultSLA FROM casemanager.tasks_view ". $whereConditionStr;
    $result = mysqli_query($link, $sql);

    ?><head>
        <title>Case Manager - My Tasks</title>
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
                                    <h4 class="mb-sm-0 font-size-18">My Tasks</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                            <li class="breadcrumb-item active">My Tasks</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                <div class="col-4 mb-2 align-items-center d-flex w-25">
                                    <h6 class=" mb-0 flex-grow-1 ">Show Status</h6>
                                    <div class="flex-grow-1">
                                    <select class="form-select form-select-sm mb-0" id="filterbystatus" name="status">
                                        <option value="" selected>All</option>
                                        <option value="Assigned">Assigned</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                        
                                    </select>
                                    </div>
                                </div>
                            </div>
                                <table id="datatable-buttons" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">
                                    <thead>
                                        <tr>                                            
                                            <th></th>
                                            <th>Task Id</th>
                                            <th>Case Number</th>
                                            <th>Task Name</th>
                                            <th>Requester Name</th>
                                            <th>Created Date</th>
                                            <th>Assigned Person</th>
                                            <th>Task Status</th>
                                            <th>SLA</th>                                        
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    while ($res = mysqli_fetch_array($result)){
                                    ?>
                                        <tr>
                                            
                                                <td>
                                                    <a href="cases-view.php?case_id=<?php echo $res['case_id']; ?>&task_id=<?php echo $res['task_id'];?>">
                                                    <i style="cursor:pointer;" class="mdi mdi-eye text-primary"></i>
                                                    </a>
                                                </td>
                                                <td><?php echo $res['task_id']; ?></td>
                                                <td><?php echo $res['case_number']; ?></td>
                                                <td><?php echo $res['task_name']; ?></td>
                                                <td><?php echo $res['requester_name']; ?></td>
                                                <td><?php echo date('m/d/Y h:i A', strtotime($res['created_datetime'])); ?></td>
                                                <td><?php echo $res['assigned_person']; ?></td>
                                                <td><?php echo $res['task_status_name']; ?></td>
                                                <td><?php echo $res['defaultSLA']; ?></td>
                                            </tr><?php
                                    }
                                    
                                    ?></tbody>
                                    
                                </table>
                                <!-- end cardaa -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->                        
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include 'layouts/footer.php'; ?>
            </div>
            <!-- end main content-->
        </div>
        <!-- END layout-wrapper -->

        <style>
            @keyframes placeHolderShimmer{
                0%{
                    background-position: -268px 0
                }
                100%{
                    background-position: 368px 0
                }
            }
            .linear-background {
                animation-duration: 1s;
                animation-fill-mode: forwards;
                animation-iteration-count: infinite;
                animation-name: placeHolderShimmer;
                animation-timing-function: linear;
                background: #f6f7f8;
                background: linear-gradient(to right, #CCC 36%, #dddddd 60%, #CCC 40%);
                background-size: 700px 90px;
                height: 25px;
                position: relative;
                overflow: hidden;
            }
            </style>

        <div class="modal" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="linear-background text-center">
                        <h6 style="margin-top:5px;">Loading, Please wait....</h6>
                    </div>
                </div>
            </div>  
        </div>

        <div class="modal" id="viewCaseModal" tabindex="-1" role="dialog" aria-labelledby="viewCaseModal" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">View Case Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">Case Number &nbsp;&nbsp;&nbsp;&nbsp; <strong class="h4" id="case_number"></strong></label>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">Case Type</label>
                                <input type="text" readonly disabled class="form-control" id="case_type" value="">
                            </div>
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">Case Status</label>
                                <input type="text" readonly disabled class="form-control" id="case_status" value="">
                            </div>
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">Initial Complaint Date</label>
                                <input type="text" readonly disabled class="form-control" id="case_initial_file_date" value="">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">First Name</label>
                                <input type="text" readonly disabled class="form-control" id="person_first_name" value="">
                            </div>                            
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">Middle Initial</label>
                                <input type="text" readonly disabled class="form-control" id="person_middle_name" value="">
                            </div>  
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">Last Name</label>
                                <input type="text" readonly disabled class="form-control" id="person_last_name" value="">
                            </div>          
                        </div>
                        <div class="row mt-3">
                            <div class="col-xxl-8 col-lg-8 col-md-8">
                                <label for="case_type_id" class="form-label">Contact Email</label>
                                <input type="text" readonly disabled class="form-control" id="contactemail" value="">
                            </div>                            
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <label for="case_type_id" class="form-label">Contact Phone</label>
                                <input type="text" readonly disabled class="form-control" id="contactnumber" value="">
                            </div>         
                        </div>
                        <div class="row mt-3">
                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                <label for="case_type_id" class="form-label">Agency</label>
                                <input type="text" readonly disabled class="form-control" id="agency" value="">
                            </div>           
                        </div>
                        <div class="row mt-3">
                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                <label for="case_type_id" class="form-label">Discriptions</label>
                                <textarea class="form-control" readonly disabled id="descriptions" rows="3"></textarea>
                            </div>           
                        </div>
                        <div class="row mt-4">
                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                <label for="case_type_id" class="form-label h5">Upload Attachements</label>
                                <table class="table table-bordered dt-responsive nowrap w-100 mb-2">
                                    <thead>
                                        <tr>         
                                            <th width="25%">Attachement Type</th>
                                            <th width="25%">File Name</th>
                                            <th width="25%">Created by</th>
                                            <th width="25%">Created On</th>                                        
                                        </tr>
                                    </thead>
                                    <tbody id="AttachementData"></tbody>
                                </table>
                            </div>
                            <div style="position:absolute; bottom:15%; width:95%;" id="CaseAttachedDocViewDivPanel" class="d-none">
                                <div style="position:relative;" id="CaseAttachedDocView"></div>
                            </div>           
                        </div>
                    </div>
                    <div class="modal-footer"></div>
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
        <!-- Session timeout js -->
<script src="assets/libs/@curiosityx/bootstrap-session-timeout/index.js"></script>

<!-- Session timeout init js -->
<script src="assets/js/pages/session-timeout.init.js"></script>

        <script>
            var ROOT_URL = '<?php echo ROOT_URL; ?>';
            function CaseAttachedDocViewModal(file_name, file_ext){
                var imageFileExt = ["jpg", "jpeg", "png", "gif", "bmp"];
                var docFileExt = ["docx", "pptx", "doc", "ppt"];

                var htmlData  = '<div class="card">';
                    htmlData += '<div class="card-body">';
                        htmlData += '<div class="row">';
                            htmlData += '<div class="col-xxl-12 col-lg-12 col-md-12 text-center">';
                                if(imageFileExt.includes(file_ext))
                                    htmlData += '<img src="'+ROOT_URL+'/uploads/case-attachements/'+file_name+'" class="img-fluid border rounded"/>';
                                else {
                                    if(docFileExt.includes(file_ext)){
                                        htmlData += '<iframe src="https://docs.google.com/viewer?url='+ROOT_URL+'/uploads/case-attachements/'+file_name+'&embedded=true" frameborder="0" width="95%" height="400" style="border:1px solid black;"></iframe>';
                                    }
                                    else if(file_ext == 'pdf')
                                        htmlData += '<iframe src="'+ROOT_URL+'/uploads/case-attachements/'+file_name+'" width="95%" height="400" style="border:1px solid black;" frameborder="0"></iframe>';
                                }
                            htmlData += '</div>';
                        htmlData += '</div>';
                    htmlData += '</div>';
                    htmlData += '<div class="card-footer">';
                        htmlData += '<button class="btn btn-md btn-success w-25 waves-effect waves-light" onclick="CaseAttachedDocViewClose();" type="button">Close</button>';
                    htmlData += '</div>';
                htmlData += '</div>';    
                $('#CaseAttachedDocViewDivPanel').removeClass('d-none');
                $('#CaseAttachedDocView').html(htmlData);
            }
            function OpenCaseModal(case_id){
                $.ajax({
                    type: 'POST',
                    dataType:'json',
                    data: {
                        action:'ViewCaseDetails',
                        case_id:case_id,
                    },
                    url: 'action.php',
                    beforeSend: function (xhr){ 
                        $('#loadingModal').modal('show');   
                    },
                    success : function(data) {
                        if(data['status']==true){
                            $('#case_number').html(data['data']['case_number']);
                            $('#case_status').val(data['data']['case_status']);
                            $('#case_type').val(data['data']['case_type']);
                            $('#case_initial_file_date').val(data['data']['case_initial_file_date']);

                            $('#person_first_name').val(data['data']['person_first_name']);
                            $('#person_middle_name').val(data['data']['person_middle_name']);
                            $('#person_last_name').val(data['data']['person_last_name']);

                            $('#contactemail').val(data['data']['contactemail']);
                            $('#contactnumber').val(data['data']['contactnumber']);

                            $('#agency').val(data['data']['agency']);
                            $('#descriptions').val(data['data']['descriptions']);
                            $('#loadingModal').modal('hide');
                            if(data['attachments']){
                                var htmlData = '';
                                for(k in data['attachments']){
                                    htmlData += '<tr>';
                                        htmlData += '<td>';
                                            htmlData += toTitleCase(data['attachments'][k]['attachement_type_name']);
                                        htmlData += '</td>';
                                        htmlData += '<td><strong onclick="CaseAttachedDocViewModal(\''+data['attachments'][k]['file_name']+'\', \''+data['attachments'][k]['file_ext']+'\');">';
                                            htmlData += data['attachments'][k]['file_name'];
                                        htmlData += '</strong></td>';
                                        htmlData += '<td>';
                                            htmlData += data['attachments'][k]['created_by'];
                                        htmlData += '</td>';
                                        htmlData += '<td>';
                                            htmlData += data['attachments'][k]['created_datetime'];
                                        htmlData += '</td>';
                                    htmlData += '</tr>';
                                }
                                $('#AttachementData').html(htmlData);
                            }
                            $('#viewCaseModal').modal('show');
                        }
                    }
                });                
            }
            function toTitleCase(str) {
                return str.replace(/(?:^|\s)\w/g, function(match) {
                    return match.toUpperCase();
                });
            }
            function CaseAttachedDocViewClose(){
                $('#CaseAttachedDocView').html('');
                $('#CaseAttachedDocViewDivPanel').addClass('d-none');   
            }
        </script>
        <script>
    
    $(document).ready(function() {
   // DataTables initialisation
        $('#datatable-buttons').DataTable();
        //Buttons examples
        var manage_tasks = $('#datatable-buttons').DataTable({
            destroy: true,
            lengthChange: true,
            "pageLength": 100
            //buttons: ['copy', 'excel', 'pdf', 'colvis']
        });

        manage_tasks.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');

        $(".dataTables_length select").addClass('form-select form-select-sm');

        var table = $('#datatable-buttons').dataTable();

$("#filterbystatus").on('change', function() {
    //filter by selected value on second column
    table.fnFilter($(this).val(), 7);
});  
  });

    
</script>
    </body>
</html>