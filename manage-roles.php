<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php';
include 'layouts/session.php';

$permission_err = '';

global $link;
include 'class.crud.php';

$crudObj = new CRUD('roles', 'id');
$crudObj->mysqli = $link;

$crudRPObj = new CRUD('role_permissions', 'id');
$crudRPObj->mysqli = $link;

if(isset($_POST['update'])) {
    $role_id = $_POST['role_id'];
    if($crudObj->FindRecordsCount('roles', array('role=\''.$_POST["role"].'\'', 'id<>\''.$role_id.'\''))){
        $data = [
            'status' => false,
            'message' => 'Role "'.$_POST['role'].'" already exists, please try again'
        ];
    }
    else{
        $saveData = array();
        $saveData['id']     =   $role_id;
        $saveData['role']   =   $_POST['role'];
        $crudObj->save($saveData);
        $crudObj->run_sql_query('DELETE FROM role_permissions WHERE role_id='.$role_id);
        if(isset($_POST['role_ids']) && is_array($_POST['role_ids']) && count($_POST['role_ids'])>0){
            foreach($_POST['role_ids'] as $key=>$val){
                $saveRPData                 =   array();
                $saveRPData['id']           =   '';
                $saveRPData['role_id']      =   $role_id;
                $saveRPData['role_def_id']  =   $val;
                $crudRPObj->save($saveRPData);
            }
        }
        $data = [
            'status'                =>  true,
            'message'               =>  'Saved Successfully'
        ];
    }
}

//select query statement
$sq = "SELECT * FROM roles";
$result = mysqli_query($link, $sq);

$sq = "SELECT * FROM role_definition";
$results = mysqli_query($link, $sq);

?><head>
    <title>Case Manager - Manage Roles</title>
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
                            <h4 class="mb-sm-0 font-size-18">Manage Roles</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Manage Roles</li>
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
                                <a href="add-roles.php"> <button
                                        class="btn btn-primary waves-effect waves-light btn-sm mb-3"> Add
                                        Role</button></a>
                                <table id="datatable" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">
                                    <thead>
                                        <tr>
                                            <th>Roles</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody><?php
                                        while ($res = mysqli_fetch_array($result)) {
                                            ?><tr>
                                                <td><?php echo ($res['role']); ?></td>
                                                <td width="5%">
                                                    <button type="submit" class="btn btn-sm btn-success editbtn"
                                                        onclick="editDetails(<?php echo $res['id']; ?>, '<?php echo $res['role'] ?>')">
                                                        <em class="fas fa-pen"></em>
                                                    </button>
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
                <!-- The Edit Modal -->
                <div class="modal" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Role & Permissions</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span>></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <form class="needs-validation custom-form pt-2"
                                    action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="row g-0">
                                        <div class="col-xxl-12 col-lg-12 col-md-12">
                                            <div class="mb-4">
                                                <label for="permission" class="form-label">Role</label>
                                                <input type="text" class="form-control" id="role" placeholder="Enter Role"
                                                    required name="role" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-0">
                                        <div class="col-xxl-12 col-lg-12 col-md-12">
                                            <table class="table table-bordered dt-responsive nowrap w-100 mb-3">
                                                <tr>
                                                    <th>Role Definition</th>
                                                    <th class="text-center">Assign</th>
                                                </tr><?php
                                                while($res = mysqli_fetch_array($results)){
                                                    ?><tr>
                                                        <td><?php echo $res['role_definition_name']; ?></td>
                                                        <td class="text-center">
                                                            <input type="checkbox" id="role-definition-id-<?php echo $res['role_definition_id']; ?>" value="<?php echo $res['role_definition_id']; ?>" name="role_ids[]" />
                                                        </td>
                                                    </tr><?php
                                                }
                                            ?></table>
                                        </div>
                                    </div>
                                    <div class="row g-0">
                                        <div class="col-xxl-12 col-lg-12 col-md-12">
                                            <div class="mb-3">
                                                <input type="hidden" name="role_id" id="role_id" value=""/>
                                                <button class="btn btn-primary w-25 waves-effect waves-light" name="update" type="submit">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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

    <script>
        function editDetails(role_id, role) {
            $.ajax({
                type: 'POST',
                dataType:'json',
                data: {
                    action:'getRoleDefinationOnRole',
                    role_id:role_id,
                },
                url: 'action.php',
                beforeSend: function (xhr){ 
                    $('#editmodal').modal('show');   
                },
                success : function(data) {
                    if(data['status']==true){
                        $('#role_id').val(role_id);
                        $('#role').val(role);

                        for(k in data['data']){
                            $('#role-definition-id-'+data['data'][k]).attr('checked', 'checked');
                        }
                    }
                    else{
                        $('#role_id').val('');
                        $('#role').val('');     
                    }
                }
            });
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

    <!-- App js -->
    <script src="assets/js/app.js"></script>
    </body>
</html>