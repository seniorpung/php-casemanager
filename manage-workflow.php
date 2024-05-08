<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php';
include 'layouts/session.php';

$permission_err = '';
$org_id = Trim($_SESSION["organization_id"]);

global $link;
include 'class.crud.php';

$crudObj = new CRUD('case_type_definition', 'id');
$crudObj->mysqli = $link;

//select query statement
$sq = "SELECT * FROM workflow_type_view where organization_id ='$org_id' order by created_datetime desc";
$result = mysqli_query($link, $sq);

?><head>
    <title>Case Manager - Manage Workflow</title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />

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
                            <h4 class="mb-sm-0 font-size-18">Manage Workflow</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Manage Workflow</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <?php  include 'layouts/alert-messages.php'; ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <a href="workflow.php"> <button class="btn btn-primary waves-effect waves-light btn-sm mb-3"> Add Workflow</button></a>
                                <table id="datatable-buttons" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Workflow Name</th>
                                            <th>Number Of Requests</th>
                                            <th>Begin Date</th>
                                            <th>End Date</th>
                                            <th>Created Date</th>
                                            <th>Created By</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody><?php
                                        while ($res = mysqli_fetch_array($result)) {
                                            ?><tr>
                                                <td><?php echo ($res['id']); ?></td>
                                                <td><?php echo ($res['name']); ?></td>
                                                <td><?php echo ($res['request_cnt']); ?></td>
                                                <td><?php echo ($res['effective_begin_date']); ?></td>
                                                <td><?php echo ($res['effective_end_date']); ?></td>
                                                <td><?php echo ($res['created_datetime']); ?></td>
                                                <td><?php echo ($res['created_by']); ?></td>
                                                <td>
                                                   <a href="edit-workflow.php?id=<?php echo $res['id']; ?>" class="btn btn-sm btn-primary edit-link">
                                                        <em class="fas fa-pen"></em>
                                                    </a>
                                                    <a href="view-workflow.php?id=<?php echo $res['id']; ?>" class="btn btn-sm btn-info" onclick="">
                                                        <em class="fas fa-eye"></em>
                                                    </a>
                                                    <!-- copy feature begin -->
                                                    <button type="submit" class="btn btn-sm btn-info" name="copy" value="copy" onclick="copyWorkflowModal('<?php echo $res['id'];?>')">
                                                    <em class="bx bx-check-double font-size-12 align-middle me-2"></em>Copy
                                                    </button>
                                                    <!-- copy feature end -->
                                                </td>
                                            </tr><?php
                                        }
                                    ?></tbody>
                                </table>
                            </div>
                        </div>
                        <!-- end cardaa -->
                    </div> <!-- end col -->
                </div> <!-- end row -->
                <!-- Copy Workflow Confirmation Modal -->
                <div class="modal" id="copyWorkflowModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                            <input type="hidden" class="id" name="id" value="id">
                                            <button class="btn btn-primary waves-effect waves-light w-25 float-left" onclick="copyworkflow()">Yes !</button>
                                            <button type="button" class="btn btn-secondary w-25 float-right" data-bs-dismiss="modal">No</button>
                                        </div>
                                    </div>
                                </div>
                          </div>
               
                        </div>

                <!-- Copy Workflow Confirmation Modal -->
                <!--  Workflow Confirmation Modal when there is outstanding request -->

                <div class="modal" id="myeditmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h4 class="modal-title">Confirmation!</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                                    </div>
                                    <!-- Modal body -->
                                    <div class="modal-body">
                                        <h5>The selected workflow has outstanding request and cannot be edited.</h5>
                                        <div class="mb-3 mt-4">
                                            
                                            <button type="button" class="btn btn-primary w-25 float-left" data-bs-dismiss="modal">Ok</button>
                                        </div>
                                    </div>
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

    <!-- Right Sidebar -->
    <?php include 'layouts/right-sidebar.php'; ?>
    <!-- /Right-bar -->

    <!-- JAVASCRIPT -->
    <?php include 'layouts/vendor-scripts.php'; ?>
    <!-- Copy Workflow Script -->
    <script>
    function copyWorkflowModal(id) {
        $('#copyWorkflowModal').modal('toggle');
        $('#copyWorkflowModal').modal('show');
        $('.confirmation_msg').html("Would you like to Copy Workflow?");
        $(".id").val(id);
    }
    function copyworkflow(){
        $('#overlay').show();
        var id = $(".id").val();
        $.ajax({
            url: 'action.php',
            type: 'POST',
            data: 'action=copyworkflow&id='+id,
            success: function(data) {
                $('#overlay').hide();
                if(data=='1'){
                    //alert("Checkout time updated");
                    location.reload(true);
                } else if(data=='0'){
                    alert("Something went wrong please try again");
                }
            },
        });
    }
    $(document).ready(function(){
        var table=$('#datatable').DataTable();
        table.order([5,'desc']).draw();
    });
</script>
<script>
        function openModal() {
            $('#myeditmodal').modal('show'); 
        }
    </script>
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
    <script>
            $(document).ready(function () { 
                var ii = 0;
                $('.mytable').each(function(i, el) {
                    var dtTable = $(this).DataTable({
                        "order": [[ 2, "desc" ]],
                       
                
            }); 
         </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <!-- Session timeout js -->
<script src="assets/libs/@curiosityx/bootstrap-session-timeout/index.js"></script>

<!-- Session timeout init js -->
<script src="assets/js/pages/session-timeout.init.js"></script>
<script>
    
    $(document).ready(function() {
   // DataTables initialisation
        $('#datatable-buttons').DataTable();
        //Buttons examples
        var manage_tasks = $('#datatable-buttons').DataTable({
            destroy: true,
            lengthChange: true,
            "pageLength": 100,
            buttons: ['excel','pdf','colvis']
        });

        manage_tasks.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');

        $(".dataTables_length select").addClass('form-select form-select-sm');

        // Refilter the table
        //$('#min, #max').on('change', function () {
            //table_order_history.draw();
        //});
  });

    
</script>
    </body>
</html>
