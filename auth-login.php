<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to index page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}
// Include config file
require_once "layouts/config.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $status_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password, status, organization_id, permission_id FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $status, $organization_id, $permission_id);
                    if (mysqli_stmt_fetch($stmt)) {
                        if ($status==1) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["organization_id"] = $organization_id;
                            $_SESSION["permission_id"] = $permission_id;
                            // Redirect user to welcome page
                            header("location: index.php");
                        } else {
                            // Display an error message if password is not valid
                            $password_err = "The Password was not valid.";
                        }
                    } else {
                        // Display an error message if account has been disabled
                       $status_err = "Your account has been disabled"; 
                    }
                } else {
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($link);
}
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>

    <title>Login | Sovratec - Case Manager</title>
    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>
<div class="auth-page">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="auth-full-page-content d-flex p-sm-5 p-4">
                    <div class="w-100">
                        <div class="d-flex flex-column h-100">
                            <div class="mb-4 mb-md-5 text-center">
                                <a href="index.php" class="d-block auth-logo">
                                    <img src="assets/images/logo-sm.svg" alt="" height="28"> <span class="logo-txt">Case Manager</span>
                                </a>
                            </div>
                            <div class="auth-content my-auto">
                                <div class="text-center">
                                    <h5 class="mb-0">Welcome Back !</h5>
                                    <p class="text-muted mt-2">Sign in to continue to Case Manager.</p>
                                </div>
                                <form class="custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="mb-3 <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                                        <label class="form-label" for="username">Username</label>
                                        <input type="text" class="form-control" id="username" placeholder="Enter username" name="username" value="">
                                        <span class="text-danger"><?php echo $username_err; ?></span>
                                    </div>
                                    <div class="mb-3 <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <label class="form-label" for="password">Password</label>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="">
                                                    <a href="auth-recoverpw.php" class="text-muted">Forgot password?</a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control" placeholder="Enter password" name="password" value="" aria-label="Password" aria-describedby="password-addon">
                                            <button class="btn btn-light ms-0" type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                        <div>
                                            <span class="text-danger"><?php echo $password_err; ?></span>
                                        </div>
                                    </div>
                                    <span class="text-danger"><?php echo $status_err; ?></span>
                                    <div class="mb-3">
                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Log In</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end auth full page content -->
            </div>
            <!-- end col -->
            <div class="col-xxl-9 col-lg-8 col-md-7">
                <div class="auth-bg pt-md-5 p-4 d-flex">
                    <div class="bg-overlay bg-primary"></div>
                    <ul class="bg-bubbles">
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                    </ul>
                    <!-- end bubble effect -->
                    <div class="row justify-content-center align-items-center">
                        <div class="col-xl-7">
                            <div class="p-0 p-sm-4 px-xl-0">
                                <div id="reviewcarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-indicators carousel-indicators-rounded justify-content-start ms-0 mb-0">
                                        <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                        <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                        <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                    </div>
                                    <!-- end carouselIndicators -->
                                    <div class="carousel-inner">
                                        <div class="carousel-item active">
                                            <div class="testi-contain text-white">
                                                <i class="bx bxs-quote-alt-left text-success display-6"></i>

                                                <h4 class="mt-4 fw-medium lh-base text-white">“I feel confident
                                                    imposing change
                                                    on myself. It's a lot more progressing fun than looking back.
                                                    That's why
                                                    I ultricies enim
                                                    at malesuada nibh diam on tortor neaded to throw curve balls.”
                                                </h4>
                                                <div class="mt-4 pt-3 pb-5">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0">
                                                            <img src="assets/images/users/avatar-1.jpg" class="avatar-md img-fluid rounded-circle" alt="...">
                                                        </div>
                                                        <div class="flex-grow-1 ms-3 mb-4">
                                                            <h5 class="font-size-18 text-white">Richard Drews
                                                            </h5>
                                                            <p class="mb-0 text-white-50">Teacher</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="carousel-item">
                                            <div class="testi-contain text-white">
                                                <i class="bx bxs-quote-alt-left text-success display-6"></i>

                                                <h4 class="mt-4 fw-medium lh-base text-white">“Our task must be to
                                                    free ourselves by widening our circle of compassion to embrace
                                                    all living
                                                    creatures and
                                                    the whole of quis consectetur nunc sit amet semper justo. nature
                                                    and its beauty.”</h4>
                                                <div class="mt-4 pt-3 pb-5">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0">
                                                            <img src="assets/images/users/avatar-2.jpg" class="avatar-md img-fluid rounded-circle" alt="...">
                                                        </div>
                                                        <div class="flex-grow-1 ms-3 mb-4">
                                                            <h5 class="font-size-18 text-white">Rosanna French
                                                            </h5>
                                                            <p class="mb-0 text-white-50">Guidance Counselor</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="carousel-item">
                                            <div class="testi-contain text-white">
                                                <i class="bx bxs-quote-alt-left text-success display-6"></i>

                                                <h4 class="mt-4 fw-medium lh-base text-white">“I enjoy using this application.
                                                    It provides me with visibility into who is coming in and out of the school.
                                                    Educating, and safety is always top priority.
                                                    Case Manager app does a great job with this.”</h4>
                                                <div class="mt-4 pt-3 pb-5">
                                                    <div class="d-flex align-items-start">
                                                        <img src="assets/images/users/avatar-3.jpg" class="avatar-md img-fluid rounded-circle" alt="...">
                                                        <div class="flex-1 ms-3 mb-4">
                                                            <h5 class="font-size-18 text-white">Ilse R. Eaton</h5>
                                                            <p class="mb-0 text-white-50">Vice Principal
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end carousel-inner -->
                                </div>
                                <!-- end review carousel -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container fluid -->
</div>


<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>
<!-- password addon init -->
<script src="assets/js/pages/pass-addon.init.js"></script>

</body>

</html>