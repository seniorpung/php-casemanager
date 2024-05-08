<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php';
include 'layouts/session.php';

global $link;
include 'class.crud.php';

$crudObj = new CRUD('roles', 'id');
$crudObj->mysqli = $link;

$crudRPObj = new CRUD('role_permissions', 'id');
$crudRPObj->mysqli = $link;

// Define variables and initialize with empty values
$permission = $permission_err = "";
$created_by = $_SESSION["id"];
$created_datetime = date('Y-m-d H:i:s');

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if($crudObj->FindRecordsCount('roles', array('role=\''.$_POST["permission"].'\''))){
        $data = [
            'status' => false,
            'message' => 'Role "'.$_POST['permission'].'" already exists, please try again'
        ];
    }
    else{
        $saveData = array();
        $saveData['id']     =   '';
        $saveData['role']   =   $_POST['permission'];
        $id = $crudObj->save($saveData); 
        if($id>0){
            if(isset($_POST['role_ids']) && is_array($_POST['role_ids']) && count($_POST['role_ids'])>0){
                foreach($_POST['role_ids'] as $val){
                    $saveRPData                 = array();
                    $saveData['id']             =   '';
                    $saveData['role_id']        =   $id;
                    $saveData['role_def_id']    =   $val;
                    $crudRPObj->save($saveRPData);
                }
            }
            $data = [
                'status'                =>  true,
                'message'               =>  'Saved Successfully'
            ];
        }  
        else {
            $data = [
                'status' => false,
                'message' => 'Something went wrong. Please try again later.'
            ];
        }
    }
}

$sq = "SELECT * FROM role_definition";
$result = mysqli_query($link, $sq);

?><head>
    <title>Case Manager - Manage Roles</title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
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
                <div class="auth-page">
                    <div class="container-fluid p-0">
                    <div class="row">
                        <div class="offset-4 col-4">
                            <div class="card">
                                <div class="card-body">
                                    <form class="needs-validation custom-form pt-2"
                                        action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="row g-0">
                                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                                <div class="mb-4 <?php echo (!empty($permission_err)) ? 'has-error' : ''; ?>">
                                                    <label for="permission" class="form-label">Role</label>
                                                    <input type="text" class="form-control" id="permission" placeholder="Enter Role"
                                                        required name="permission" value="<?php echo $permission; ?>">
                                                    <span class="text-danger">
                                                        <?php echo $permission_err; ?>
                                                    </span>
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
                                                    while($res = mysqli_fetch_array($result)){
                                                        ?><tr>
                                                            <td><?php echo $res['role_definition_name']; ?></td>
                                                            <td class="text-center">
                                                                <input type="checkbox" value="<?php echo $res['role_definition_id']; ?>" name="role_ids[]" />
                                                            </td>
                                                        </tr><?php
                                                    }
                                                ?></table>
                                            </div>
                                        </div>
                                        <div class="row g-0">
                                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                                <div class="mb-3">
                                                    <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
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

<!-- App js -->
<script src="assets/js/app.js"></script>
<!-- Keep Nav Menu Open and Highlight link -->    
<script>
        $('#mm-admin').attr('aria-expanded', true);
        $('#mm-admin').addClass('mm-show')
        $('#mm-admin').css('height', 'auto')
        $('#mm-admin').parent().addClass('mm-active');
        $($('#mm-admin').children()[4]).children().eq(0).css('color', '#1c84ee');
    </script>
</body>

</html>