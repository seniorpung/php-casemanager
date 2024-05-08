<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php'; 
include 'layouts/session.php'; 

// Define variables and initialize with empty values
$location_name = $location_address1 = $location_address2 = $city = $state = $zip = "";

$created_by = $_SESSION["id"];
$created_datetime = date('Y-m-d H:i:s');

$location_name_err = $location_address1_err = $location_address2_err = $city_err = $state_err = $zip_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate location name
     if (empty(trim($_POST["location_name"]))) {
        $location_name_err = "Please enter location name.";
    } else {
        $location_name = trim($_POST["location_name"]);
    }
    // Validate city
    if (empty(trim($_POST["city"]))) {
        $city_err = "Please enter city name.";
    } else {
        $city = trim($_POST["city"]);
    }
     // Validate state
    if (empty(trim($_POST["state"]))) {
        $state_err = "Please select state.";
    } else {
        $state = trim($_POST["state"]);
    }
     // Validate ZIP
    if (empty(trim($_POST["zip"]))) {
        $zip_err = "Please enter Zip code.";
    } else {
        $zip = trim($_POST["zip"]);
    }
     // Validate address1
    if (empty(trim($_POST["location_address1"]))) {
        $location_address1_err = "Please enter location address 1.";
    } else {
        $location_address1 = trim($_POST["location_address1"]);
    }
     // Validate address2
    if (empty(trim($_POST["location_address2"]))) {
        $location_address2_err = "Please enter location address 2.";
    } else {
        $location_address2 = trim($_POST["location_address2"]);
    }


    // Check input errors before inserting in database
    if (empty($location_name_err) && empty($city_err) && empty($state_err) && empty($zip_err) && empty($location_address1_err) && empty($location_address2_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO location (location_name, city, state, zip, location_address1, location_address2,created_by,created_datetime) VALUES (?, ?, ?, ?, ?, ?,?,?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssssss", $param_location_name, $param_city, $param_state, $param_zip, $param_location_address1, $param_location_address2, $created_by,$created_datetime);

            // Set parameters
             $param_location_name = $location_name;
              $param_city = $city;
               $param_state = $state;
                $param_zip = $zip;
            $param_location_address1 = $location_address1;
            $param_location_address2 = $location_address2;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to dashboard page
                header("location: edit_location.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}
?>


<head>
    <title>Case Manager - Add Location</title>

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
                            <h4 class="mb-sm-0 font-size-18">Add Location</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Add Location</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                    <div class="auth-page">
                        <div class="container-fluid p-0">
                        <div class="row g-0">
                            <div class="col-xxl-7 col-lg-7 col-md-7">
                                
                                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                                     <div class="mb-3 <?php echo (!empty($location_name_err)) ? 'has-error' : ''; ?>">
                                                        <label for="locationname" class="form-label">Location Name</label>
                                                        <input type="text" class="form-control" id="locationname" placeholder="Enter location name" required name="location_name" value="<?php echo $location_name; ?>">
                                                        <span class="text-danger"><?php echo $location_name_err; ?></span>
                                                    </div>
                                                     <div class="mb-3 <?php echo (!empty($location_address1_err)) ? 'has-error' : ''; ?>">
                                                        <label for="address1" class="form-label">Address 1</label>
                                                        <input type="text" class="form-control" id="address1" placeholder="Enter Location Address 1" required name="location_address1" value="<?php echo $location_address1; ?>">
                                                        <span class="text-danger"><?php echo $location_address1_err; ?></span>
                                                    </div>
                                                     <div class="mb-3 <?php echo (!empty($location_address2_err)) ? 'has-error' : ''; ?>">
                                                        <label for="address2" class="form-label">Address 2</label>
                                                        <input type="text" class="form-control" id="address2" placeholder="Enter Location Address 2" required name="location_address2" value="<?php echo $location_address2; ?>">
                                                        <span class="text-danger"><?php echo $location_address2_err; ?></span>
                                                    </div>

                                                     <div class="name-style mb-3 <?php echo (!empty($city_err)) ? 'has-error' : ''; ?>">
                                                        <label for="city" class="form-label">City</label>
                                                        <input type="text" class="form-control" id="city" placeholder="Enter City name" required name="city" value="<?php echo $city; ?>">
                                                        <span class="text-danger"><?php echo $city_err; ?></span>
                                                    </div>
                                                     <div class="name-style1 mb-3 <?php echo (!empty($state_err)) ? 'has-error' : ''; ?>">
                                                        <label for="state" class="form-label">Select State</label>

                                                         <select name="state" class="form-select">
                                        <option value="-1">Please Select State</option>
                                        <?php
                               
                               $selectquery = " select * from states ";

                                $qn= mysqli_query($link, $selectquery);
                                $nums = mysqli_num_rows($qn);

                                while($res = mysqli_fetch_array($qn)){
                                    ?>
                                     <option value="<?php echo $res['state_code'] ?>"><?php echo $res['state'] ?></option>
                         
                                 <?php } ?>
                                   </select>
                                                        <span class="text-danger"><?php echo $state_err; ?></span>
                                                    </div>

                                                     <div class="mb-3 name-style2 <?php echo (!empty($zip_err)) ? 'has-error' : ''; ?>">
                                                        <label for="zip" class="form-label">ZIP</label>
                                                        <input type="text" class="form-control" id="zip" placeholder="Enter Zip" required name="zip" value="<?php echo $zip; ?>">
                                                        <span class="text-danger"><?php echo $zip; ?></span>
                                                    </div>

                                                   
                                                    <div class="mb-3">
                                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Add Location</button>
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

</body>

</html>