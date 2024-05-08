<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php'; 
include 'layouts/session.php';
include 'modules/api_services/calling_services.php';

global $link;
if(!class_exists('CRUD'))    include 'class.crud.php';
$crudObj = new CRUD();
$crudObj->mysqli = $link;

$roles = $crudObj->FindAll('roles', array(), array(), 0, 0, array(array('id', 'ASC')));

// Define variables and initialize with empty values
$gname = $organization_id = $gdesc = "";
$gname_err = $organization_id_err = $gdesc_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate gname
    if (empty(trim($_POST["gname"]))) {
        $gname_err = "Please enter a group name.";
    } else {
        $gname = trim($_POST["gname"]);
    }
     // Validate gdesc
    if (empty(trim($_POST["gdesc"]))) {
        $gdesc_err = "Please enter group description ";
    } else {
        $gdesc = trim($_POST["gdesc"]);
    }
      // Read Organization from current user session organization id
    $organization_id = trim($_SESSION["organization_id"]);
   
    
    // Check input errors before inserting in database
    if (empty($gname_err) && empty($gdesc_err) && empty($organization_id_err)) {

        // Prepare an insert statement
        // echo "<script>alert('".$gdesc."');</script>";
        $sql = "INSERT INTO `groups` (`group_name`,`group_desc`,`organization_id`) VALUES (?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sss", $param_gname, $param_gdesc, $organization_id);

            // Set parameters
            $param_gname = $gname;
            $param_gdesc = $gdesc;
            $param_organization_id = $organization_id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {

                //11/06/2023-Zhiling: added this line to get newly generated key.  activitylog needs the newly generated key 
                $insertedNewGroupId=$stmt->insert_id;
                
             //after insert to group, Call saveLog, first premeter is id in Activity Action, second premeter is the table_name
                $input_actionByUserId = ($_SESSION["id"]);
		        call_service_save_activityLog (1, "groups", $insertedNewGroupId, $input_actionByUserId );

                // Redirect to dashboard page
                header("location: manage_group.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    // Close connection
    //mysqli_close($link);
}

?><head>
    <title>Case Manager - Add Group</title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
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
                            <h4 class="mb-sm-0 font-size-18">Add Group</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Add Group</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                    <div class="auth-page">
                        <div class="container-fluid p-0">
                        <div class="row g-0">
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                
                                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                    <div class="mb-3 <?php echo (!empty($gname_err)) ? 'has-error' : ''; ?>">
                                                        <label for="gname" class="form-label">Group Name</label>
                                                        <input type="text" class="form-control form-control-sm" id="gname" placeholder="Enter group name" required name="gname" value="<?php echo $gname; ?>">
                                                        <span class="text-danger"><?php echo $gname_err; ?></span>
                                                    </div>

                                                    <div class="mb-3 <?php echo (!empty($gdesc_err)) ? 'has-error' : ''; ?>">
                                                        <label for="groupdescription" class="form-label">Group Description</label>
                                                        <input type="text" class="form-control form-control-sm" id="gdesc" placeholder="Enter group Description" required name="gdesc" value="<?php echo $gdesc; ?>">
                                                        <span class="text-danger"><?php echo $gdesc_err; ?></span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Add Group</button>
                                                    </div>
                                                </form>
                                   
                                <!-- end auth full page content -->
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
<!-- Keep Nav Menu Open and Highlight link -->    
<script>
        $('#mm-admin').attr('aria-expanded', true);
        $('#mm-admin').addClass('mm-show')
        $('#mm-admin').css('height', 'auto')
        $('#mm-admin').parent().addClass('mm-active');
        $($('#mm-admin').children()[2]).children().eq(0).css('color', '#1c84ee');
    </script>
</body>

</html>