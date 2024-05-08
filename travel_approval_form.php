<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php'; 
include 'layouts/session.php'; 

global $link;
include 'class.crud.php';

$crudObj = new CRUD('travel_approval_form', 'travel_approval_form_id');
$crudObj->mysqli = $link;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $travel_destination = $_POST["travel_destination"];
    $travel_date = $_POST["travel_date"];
    $travel_first_name = $_POST["travel_first_name"];
    $travel_last_name = $_POST["travel_last_name"];
    $travel_purpose = $_POST["travel_purpose"];

    // Prepare an insert statement
        $sql = "INSERT INTO `travel_approval_form` (`travel_destination`, `travel_date`, `travel_first_name`, `travel_last_name`, `travel_purpose`, `task_id`) VALUES ('$travel_destination','$travel_date','$travel_first_name','$travel_last_name','$travel_purpose','1')";


        if(mysqli_query($link, $sql)){

            //header("location: index.php");

            } else {
                echo "Something went wrong. Please try again later.";
            }


}
?>

<head>
    <title>Case Manager - Travel Approval Form</title>
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
                            <h4 class="mb-sm-0 font-size-18">Travel Approval Form</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Travel Approval Form</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row g-0">
                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                <div class="card">
                                <div class="card-body">
                                
                                              <form class="needs-validation custom-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                                <div class="row mb-2 mt-4">
                                                    <label for="travel_destination" class="col-sm-2 col-form-label">Travel Destination:</label>
                                                    <div class="col-sm-3">
                                                      <input type="text" class="form-control form-control-sm" maxlength="50" id="travel_destination" placeholder="" name="travel_destination" value="" required>
                                                </div>

                                            </div>
                                            <div class="row mb-2">
                                                    <label for="travel_date" class="col-sm-2 col-form-label">Travel Date:</label>
                                                    <div class="col-sm-3">
                                                        <input type="date" class="form-control form-control-sm" id="travel_date" pattern="\d{2}/\d{2}/\d{4}" placeholder="mm/dd/yyyy" name="travel_date" value="" />
                                                </div>
                                            </div>
                                             <div class="row mb-2">
                                                    <label for="travel_first_name" class="col-sm-2 col-form-label">Traveller First Name:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="travel_first_name" placeholder="" name="travel_first_name" value="" required>
                                                    </div>
                                                    <label for="travel_last_name" class="col-sm-2 col-form-label">Traveller Last Name:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="travel_last_name" placeholder="" name="travel_last_name" value="" required>
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <label for="travel_purpose" class="col-sm-2 col-form-label">Travel Purpose:</label>
                                                    <div class="col-sm-3">
                                                      <textarea class="form-control form-control-sm" name="travel_purpose" maxlength="255" id="travel_purpose" rows="2" placeholder="" value=""></textarea>
                                                </div>

                                            </div>
                                         
                                           <div class="mt-4 mb-4 offset-2">
                                                        <button class="btn btn-primary waves-effect waves-light" id="save" name="save" type="submit"> Save </button>
                                                        <button type="button" class="offset-1 btn btn-danger"> <a href="manage-email-template.php">  Cancel </a></button>
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                   
                                <!-- end auth full page content -->
                            </div>
                        </div>
                        <!-- end row -->  
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
</body>

</html>