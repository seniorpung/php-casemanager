<?php

// Include config file
require_once "layouts/config.php";
include 'layouts/session.php';
// Define variables and initialize with empty values
$fname = $mname = $lname = $phone = $useremail = $username = "";
$fname_err = $mname_err = $lname_err = $phone_err = $useremail_err = $username_err = $permission="";
$success = "0";
$Current_password = $New_password = $Confirm_password = "";
$Current_password_err = $New_password_err = $Confirm_password_err ="";
$pass = "0";
$last_updated_by = $_SESSION["id"];
// Processing form data when form is submitted
if(isset($_POST['update_btn'])){

    // Validate useremail
    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter a useremail.";
    } elseif (!filter_var($_POST["useremail"], FILTER_VALIDATE_EMAIL)) {
        $useremail_err = "Invalid email format";
    } else {
        $sql = "SELECT id FROM users where useremail='".trim($_POST["useremail"])."' and id!='".trim($last_updated_by)."'";
        $result = $link->query($sql);
        if ($result->num_rows > 0) {
            $useremail_err = "This useremail is already taken.";
            //$row = $result->fetch_assoc();
        } else {
            $useremail = trim($_POST["useremail"]);
        }
    }
    $permission = trim($_POST["permission"]);
    $regDate = trim($_POST["regDate"]);
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }
     // Validate fname
    if (empty(trim($_POST["fname"]))) {
        $fname_err = "Please enter a first name.";
    } else {
        $fname = trim($_POST["fname"]);
    }
     // Validate mname
    if (empty(trim($_POST["mname"]))) {
        $mname_err = "Please enter a middle name.";
    } else {
        $mname = trim($_POST["mname"]);
    }
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

    // Check input errors before inserting in database
    if (empty($fname_err) && empty($mname_err) && empty($lname_err) && empty($phone_err) && empty($useremail_err) && empty($username_err) && empty($New_password_err) && empty($Confirm_password_err) && empty($Current_password_err)){
        $sql = "UPDATE users SET fname='$fname',mname='$mname',lname='$lname',phone='$phone',useremail='$useremail',username='$username' WHERE id='".$last_updated_by."'";
        if ($link->query($sql) === TRUE) {
          $success = "1";
        } else {
          //echo "Error updating record: " . $link->error;
          echo "Something went wrong. Please try again later.";
        }
    }

    // Close connection
    //mysqli_close($link);
}
?>
<?php include 'layouts/head-main.php';
if ($result = $link -> query("SELECT * FROM users where id='".$last_updated_by."'")) {
  $row = $result -> fetch_assoc();
  $fname        =   $row['fname'];
  $lname        =   $row['lname'];
  $mname        =   $row['mname'];
  $phone        =   $row['phone'];
  $useremail    =   $row['useremail'];
  $username     =   $row['username'];
  $regDate      =   $row['created_datetime'];
  $permission   =   $row['permission_id'];
  $photo        =   $row['photo'];
    //echo "<pre>"; print_r($row); echo "</pre>";
}
?>

<?php

   if(isset($_POST['changepassword'])){
        $user_id= $_POST['id'];

         // Retrieve the user's entered current password from the UI form
         $Current_password = $_POST["Current_password"];

         // Retrieve the hashed password from the database based on the user's ID
         $userId = $last_updated_by;
         $sql = "SELECT password FROM users WHERE id = $last_updated_by";
         $result = mysqli_query($link, $sql);
         $row = mysqli_fetch_assoc($result);
         $hashedPasswordFromDB = $row['password'];

         // Verify the user-entered password against the hashed password
         if (password_verify($Current_password, $hashedPasswordFromDB)) {

            //Validate New Password
             if (empty(trim($_POST["password"]))) {
             $New_password_err = "Please enter a password.";
            } elseif (strlen(trim($_POST["password"])) < 6) {
             $New_password_err = "Password must have atleast 6 characters.";
            } else {
            $New_password = trim($_POST["password"]);
            }
          // Validate Confirm password
           if (empty(trim($_POST["Confirm_password"]))) {
            $Confirm_password_err = "Please enter a confirm password.";
           } else {
            $Confirm_password = trim($_POST["Confirm_password"]);
            if (empty($New_password_err) && ($New_password != $Confirm_password)) {
                $Confirm_password_err = "Password did not match.";
            }
            // Set parameters
            $param_Confirm_password = password_hash($Confirm_password, PASSWORD_DEFAULT); // Creates a password hash
            $param_token = bin2hex(random_bytes(50)); // generate unique token
        }
     // Check input errors before updating password
     if (empty($New_password_err) && empty($Confirm_password_err)) {
        // Prepare an insert statement
         $sql = "UPDATE `users` SET `password`='$param_Confirm_password', `token`='$param_token' WHERE `id`='$last_updated_by' ";
         //$res= mysqli_query($link, $sql);   
         if ($link->query($sql) === TRUE) {
            $pass = "1";
          }           
        
       // Attempt to execute the prepared statement

       else {
           //echo "Something went wrong. Please try again later.";
           $Confirm_password_err = "New Password is not equal to Confirm Password";
           print_r($New_password_err);
           print_r($Confirm_password_err);
       }

     }
    } else{
        $Current_password_err = "Current Password Doesn't match, Please Enter Valid Password!";
         echo ($Current_password_err);
         }

}
  



?>

<head>
    <title>Case Manager - User Profile</title>

    <?php include 'layouts/head.php'; ?>

    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <?php include 'layouts/head-style.php'; ?>
    <style>.wd-100{width:100px} .ht-100{height:100px}</style>
</head>

<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div id="overlay" style="display:none;"><div class="spinner"></div><br/>Please Wait...</div>
    <div class="main-content">
        
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">User Profile Edit</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">User Profile Edit</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <?php if($success=='1'){?>
                <div class="alert alert-success text-center h5" role="alert">
					Profile Updated Successfully
				</div>
				<?php } ?>

                <?php if($pass=='1'){?>
                <div class="alert alert-success text-center h5" role="alert">
					Password Changed Successfully
				</div>
				<?php } ?>
               
                <!-- end page title -->
                    <div class="auth-page">
                        <div class="container-fluid p-0">
                        <div class="row g-0">
                            <div class="offset-md-2 col-xxl-3 col-lg-3 col-md-3">
                                <?php if(isset($row) && !empty($row['photo'])){?>
                                <img class="rounded-circle header-profile-user wd-100 ht-100 user_profile_picture" src="assets/images/users/<?php echo $row['photo'];?>" alt="">
                                <?php } else{?>
                                <img class="rounded-circle header-profile-user wd-100 ht-100 user_profile_picture" src="assets/images/users/avatar-1.jpg" alt="">
                                <?php } ?>
                                <br><br>
                                <span><strong><?php echo $fname." ".$mname." ".$lname?></strong></span><br>
                                <span><?php echo $useremail;?></span><br>
                                <span><?php echo $phone;?></span><br><br>
                                <button class="btn btn-primary w-75 waves-effect waves-light" type="button" href="javascript:void(0)" onclick="editProfilePicture()">Edit Image</button>

                                <div class="mt-4">
                                <button class="btn btn-primary w-75 waves-effect waves-light" type="button" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
                                </div>
                            </div>
                        
                            <div class="col-xxl-4 col-lg-4 col-md-4">
                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="name-style mb-3 <?php echo (!empty($fname_err)) ? 'has-error' : ''; ?>">
                                        <label for="fname" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="fname" placeholder="Enter first name" required name="fname" value="<?php echo $fname; ?>">
                                        <span class="text-danger"><?php echo $fname_err; ?></span>
                                    </div>
                                    <div class="mb-3 name-style1 <?php echo (!empty($mname_err)) ? 'has-error' : ''; ?>">
                                        <label for="mname" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="mname" placeholder="Enter middle name" required name="mname" value="<?php echo $mname; ?>">
                                        <span class="text-danger"><?php echo $mname_err; ?></span>
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
                                    <!-- <div class="mb-3">
                                        <label for="username" class="form-label">Permission</label>
                                        <input type="text" class="form-control" id="permission" placeholder="Enter username" required name="permission" value="<?php echo $permission; ?>" readonly>
                                    </div> -->
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Reg Date</label>
                                        <input type="text" class="form-control" id="regDate" placeholder="Enter username" required name="regDate" value="<?php echo $regDate; ?>" readonly >
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit" name="update_btn">Update Profile</button>
                                    </div>
                                </form>
                                
                                   
                                <!-- end auth full page content -->
                            </div>
                        </div>
                        <!-- end row -->

                                 <!-- The Edit Modal -->
                                         <div class="modal" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
                                         <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                         <div class="modal-content">

                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                              <h4 class="modal-title">Change Password</h4>
                                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                                            </div>
                                           <!-- Modal body -->
                                          <div class="modal-body">
                                             <form class="needs-validation custom-form mt-2 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                              <input type="hidden" name="id" id="id">
                                            
                                          <div class="mb-3 <?php echo (!empty($Current_password_err)) ? 'has-error' : ''; ?>">
                                             <label class="form-label" for="userpassword">Current Password</label>
                                             <input type="password" class="form-control" id="Current_password" placeholder="Please Enter your Current Pssword" required name="Current_password" value="<?php echo $Current_password; ?>">
                                             <span class="text-danger"><?php echo $Current_password_err; ?></span>
                                          </div>
                                                   
                                          <div class="mb-3 <?php echo (!empty($New_password_err)) ? 'has-error' : ''; ?>">
                                             <label for="userpassword" class="form-label">New Password</label>
                                             <input type="password" class="form-control" id="userpassword" placeholder="Please Enter Your New Password" required name="password" value="<?php echo $New_password; ?>">
                                             <span class="text-danger"><?php echo $New_password_err; ?></span>
                                          </div>

                                          <div class="mb-3 <?php echo (!empty($Confirm_password_err)) ? 'has-error' : ''; ?>">
                                             <label class="form-label" for="userpassword">Confirm Password</label>
                                             <input type="password" class="form-control" id="Confirm_password" placeholder="Re-Confirm Your Password" required name="Confirm_password" value="<?php echo $Confirm_password; ?>">
                                             <span class="text-danger"><?php echo $Confirm_password_err; ?></span>
                                          </div>
                                                     
                                                    
                                           <div class="mt-4">
                                              <button class="btn btn-primary waves-effect waves-light" id="changepassword" name="changepassword" type="submit">Change Password</button>
                                              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                                           </div>
                                     </form>
                                    </div>

                                         </div>
                                      </div>
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
     <div class="modal fade" id="edit_profile_modal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-scrollable" style="width: 35%; height: 35%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileLabel">Select New Profile Picture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close" onclick="javascript:window.location.reload()"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="edit_photo" enctype="multipart/form-data">
                            <div class="output mt-1 mb-3 text-center text-danger h6" style="width: 100%; "></div>
                            <div class="modal-body pt-0">
                                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                            </div>
                            <div class="modal-footer justify-content-center">
                                <div class="hstack gap-2 justify-content-center">
                                    <input type="hidden" name="action" value="update_profile_picture">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success my_btn" id="add-btn">Upload</button>
                                </div>
                            </div>
                        </form>
                    </div>
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

<!-- App js -->
<script src="assets/js/app.js"></script>
<script>
    function editPassword(id){
        $('#changepasswordmodal').modal('show');
        $('#id').val(id);
    }
</script>

<script>
    function editProfilePicture(){
        $(".output").hide();
        $('#edit_profile_modal').modal('toggle');
        $('#edit_profile_modal').modal('show');
    }
    $("form#edit_photo").submit(function(e) {
        e.preventDefault();
        $('#overlay').show();
        var formData = new FormData(this);
        $.ajax({
            url: 'action.php',
            type: 'POST',
            data: formData,
            dataType: "json",
            success: function(opt) {
                $('#overlay').hide();
                if(opt['status']=='false'){
                    $(".output").removeClass("text-success");
                    $(".output").addClass("text-danger");
                    $(".output").html(opt['data']);
                } else if(opt['status']=='true'){
                    $(".output").removeClass("text-danger");
                    $(".output").addClass("text-success");
                    $(".output").html(opt['data']);
                    $('.user_profile_picture').attr('src','assets/images/users/'+opt['URL']);
                }
                
                //$('.output').html(data);
                setTimeout(function() {
                    $('#edit_profile_modal').modal('hide');
                }, 2000);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });
</script>
</body>

</html>