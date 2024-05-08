<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php'; 
include 'layouts/session.php';

global $link;
if(!class_exists('CRUD'))    include 'class.crud.php';
$crudObj = new CRUD();
$crudObj->mysqli = $link;

$roles = $crudObj->FindAll('roles', array(), array(), 0, 0, array(array('id', 'ASC')));

// Define variables and initialize with empty values
$fname = $mname = $lname = $phone = $useremail = $username =  $password = $confirm_password = $organization_id = "";
$fname_err = $lname_err = $phone_err = $useremail_err = $username_err = $password_err = $confirm_password_err = $organization_id_err = $permission_id_err = "";

$created_by = $_SESSION["id"];
$created_datetime = date('Y-m-d H:i:s');

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate useremail
    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter a useremail.";
    } elseif (!filter_var($_POST["useremail"], FILTER_VALIDATE_EMAIL)) {
        $useremail_err = "Invalid email format";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE useremail = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_useremail);

            // Set parameters
            $param_useremail = trim($_POST["useremail"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $useremail_err = "This useremail is already taken.";
                } else {
                    $useremail = trim($_POST["useremail"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
            
        }
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
            
        }
    }

     // Validate fname
    if (empty(trim($_POST["fname"]))) {
        $fname_err = "Please enter a first name.";
    } else {
        $fname = trim($_POST["fname"]);
    }
   // Validate mname
    $mname = trim($_POST["mname"]);
     // Validate lname
    if (empty(trim($_POST["lname"]))) {
        $lname_err = "Please enter a last name.";
    } else {
        $lname = trim($_POST["lname"]);
    }
     // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter phone no.";
    } elseif (strlen(trim($_POST["phone"])) < 10) {
        $phone_err = "Phone no. must have atleast 10 characters.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
      // Get Organization Id from session
    $organization_id = trim($_SESSION["organization_id"]);
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please enter a confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

     // Validate fname
    if (empty(trim($_POST["permission_id"]))) {
        $permission_id_err = "Please select role.";
    } else {
        $permission_id = trim($_POST["permission_id"]);
    }

    // Check input errors before inserting in database
    if (empty($fname_err) && empty($mname_err) && empty($lname_err) && empty($phone_err) && empty($useremail_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($organization_id_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (fname, mname, lname, phone, useremail, username, password, token,created_by,created_datetime, permission_id, organization_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssssssssss", $param_fname, $param_mname, $param_lname, $param_phone, $param_useremail, $param_username, $param_password, $param_token, $created_by, $created_datetime, $permission_id, $organization_id);

            // Set parameters
            $param_fname = $fname;
            $param_mname = $mname;
            $param_lname = $lname;
            $param_phone = $phone;
            $param_useremail = $useremail;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_token = bin2hex(random_bytes(50)); // generate unique token
            $param_organization_id = $organization_id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to dashboard page
                header("location: manage-users.php");
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
    <title>Case Manager - Register User</title>
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
                            <h4 class="mb-sm-0 font-size-18">Register User</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Register User</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                    <div class="auth-page">
                        <div class="container-fluid p-0">
                        <div class="row g-0">
                            <div class="col-xxl-6 col-lg-6 col-md-6">
                                
                                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                                     <div class="name-style mb-3 <?php echo (!empty($fname_err)) ? 'has-error' : ''; ?>">
                                                        <label for="fname" class="form-label">First Name</label>
                                                        <input type="text" class="form-control" id="fname" placeholder="Enter first name" required name="fname" value="<?php echo $fname; ?>">
                                                        <span class="text-danger"><?php echo $fname_err; ?></span>
                                                    </div>
                                                     <div class="mb-3 name-style1">
                                                        <label for="mname" class="form-label">Middle Name</label>
                                                        <input type="text" class="form-control" id="mname" placeholder="Enter middle name" name="mname" value="<?php echo $mname; ?>">
                                                        
                                                    </div>

                                                     <div class="mb-3 name-style2 <?php echo (!empty($lname_err)) ? 'has-error' : ''; ?>">
                                                        <label for="lname" class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" id="lname" placeholder="Enter last name" required name="lname" value="<?php echo $lname; ?>">
                                                        <span class="text-danger"><?php echo $lname_err; ?></span>
                                                    </div>

                                                    <div class="mb-3 phone-style <?php echo (!empty($phone_err)) ? 'has-error' : ''; ?>">
                                                        <label for="phone" class="form-label">Phone</label>
                                                        <input type="tel" class="form-control" id="phone" placeholder="Enter phone no." required name="phone" value="<?php echo $phone; ?>">
                                                        <span class="text-danger"><?php echo $phone_err; ?></span>
                                                    </div>



                                                    <div class="mb-3 email-style <?php echo (!empty($useremail_err)) ? 'has-error' : ''; ?>">
                                                        <label for="useremail" class="form-label">Email</label>
                                                        <input type="email" class="form-control" id="useremail" placeholder="Enter email" required name="useremail" value="<?php echo $useremail; ?>">
                                                        <span class="text-danger"><?php echo $useremail_err; ?></span>
                                                    </div>

                                                    <div class="mb-3 <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                                                        <label for="username" class="form-label">Username</label>
                                                        <input type="text" class="form-control" id="username" placeholder="Enter username" required name="username" value="<?php echo $username; ?>">
                                                        <span class="text-danger"><?php echo $username_err; ?></span>
                                                    </div>

                                                    <div class="mb-3 <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                                                        <label for="userpassword" class="form-label">Password</label>
                                                        <input type="password" class="form-control" id="userpassword" placeholder="Enter password" required name="password" value="<?php echo $password; ?>">
                                                        <span class="text-danger"><?php echo $password_err; ?></span>
                                                    </div>

                                                    <div class="mb-3 <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                                                        <label class="form-label" for="userpassword">Confirm Password</label>
                                                        <input type="password" class="form-control" id="confirm_password" placeholder="Enter confirm password" name="confirm_password" value="<?php echo $confirm_password; ?>">
                                                        <span class="text-danger"><?php echo $confirm_password_err; ?></span>
                                                    </div>

                                                    <div class="mb-3 <?php echo (!empty($permission_id_err)) ? 'has-error' : ''; ?>">
                                                        <label for="permission_id" class="form-label">Role</label>
                                                        <select name="permission_id" class="form-select">
                                                            <option value="-1">Please Select Role</option><?php
                                                            foreach($roles as $res){
                                                                ?><option value="<?php echo $res['id'] ?>"><?php echo $res['role'] ?></option><?php 
                                                            } 
                                                        ?></select>
                                                        <span class="text-danger"><?php echo $permission_id_err; ?></span>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Register</button>
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
        $($('#mm-admin').children()[1]).children().eq(0).css('color', '#1c84ee');
    </script>
</body>

</html>