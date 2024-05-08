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
$status_x = $crudObj->FindAll('status', array(), array(), 0, 0, array(array('id', 'ASC')));

// Define variables and initialize with empty values
$gp = "";
$fname = $mname = $lname = $phone = $useremail = $primary_location_id = $permission_id = $username = $status =  $password = $confirm_password = $organization_id = "";
$fname_err = $lname_err = $phone_err = $useremail_err = $primary_location_id_err = $permission_id_err = $username_err = $status_err = $password_err = $confirm_password_err = $permission_id_err = $organization_id_err = "";
$last_updated_by = $_SESSION["id"];
$org_id = Trim($_SESSION["organization_id"]);

$pass = "0";
$last_updated_datetime = date('Y-m-d H:i:s');
// Processing form data when form is submitted
if(isset($_POST['update'])) {
    $uid= $_POST['id'];

    // Validate useremail
    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter a useremail.";
    } elseif (!filter_var($_POST["useremail"], FILTER_VALIDATE_EMAIL)) {
        $useremail_err = "Invalid email format";
    } else {

        $useremail = trim($_POST["useremail"]);
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
      // Validate permission
    if (empty(trim($_POST["permission_id"]))) {
        $permission_id_err = "Please select permission.";
    } else {
        $permission_id = trim($_POST["permission_id"]);
    }
     // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter phone no.";
    } elseif (strlen(trim($_POST["phone"])) < 10) {
        $phone_err = "Phone no. must have atleast 10 characters.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validate status
    //$status_id = trim($_POST["status_id"]);
   //$status_id = isset($_POST["status_id"]) ? (int)$_POST["status_id"] : -1;
 

   // Step 3: Handle form submission
   if ($_SERVER["REQUEST_METHOD"] == "POST") {
       // Retrieve other form fields
       $status = isset($_POST["status_id"]) ? (int)$_POST["status_id"] : -1;
       // Check if the status field is posted and not empty
       if (isset($_POST["status_id"]) && $_POST["status_id"] !== "Active || Inactive") {
           // Get the selected status description from the form 
           $selected_status_desc = $_POST["status_id"];
   
           // Map the selected status description to the corresponding numeric value
           if ($selected_status_desc === "Active") {
               $status = 1;
           } elseif ($selected_status_desc === "Inactive") {
               $status = 3;
           }
         //  print_r($status); die();
       } else {
           // Handle a case where the status field is empty if necessary
           $status = -1; // Default to -1 (Pending) if the field is empty
       }
   
       // Retrieve the selected status for display purposes
       //$status = isset($_POST["status_id"]) ? (int)$_POST["status_id"] : -1;
       //print_r($status); die();
    }
   
   

    // Check input errors before inserting in database
    if (empty($fname_err) && empty($lname_err) && empty($phone_err) && empty($status_err) && empty($useremail_err) && empty($username_err) && empty($permission_id_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "UPDATE users SET fname='$fname', mname='$mname', lname='$lname', phone='$phone', useremail='$useremail', permission_id='$permission_id', status='$status', updated_by='$last_updated_by', updated_datetime='$last_updated_datetime' WHERE id='$uid'";
        //print_r($sql); die();
        $res= mysqli_query($link, $sql);
        mysqli_query($link,"delete from group_user where user_id='$uid'; ");
        if($_POST['groupassign']!=null)
        {

            foreach($_POST['groupassign'] as $key=>$value)
            {
                mysqli_query($link,"insert into group_user  (user_id,group_id) values ($uid,".$_POST['groupassign'][$key]."); ");
                // echo "<script>alert('".$uid." ".$_POST['groupassign'][$key]."');</script>";
            }
        }
        // Attempt to execute the prepared statement
        if ($res) {
            // Redirect to dashboard page
            //header("location: registered_users.php");
        } 
        else {
            echo "Something went wrong. Please try again later.";
        }
    }
}

if(isset($_POST['changepassword'])) {
     $user_id= $_POST['id'];
     $last_updated_by = $_SESSION["id"];

     //$password = $_POST["password"];

     // Validate password
      if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
        
    }

     // Validate confirm password
     if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please enter a confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
        // Set parameters
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
        $param_token = bin2hex(random_bytes(50)); // generate unique token
    }
    

     // Check input errors before updating password
    if (empty($password_err) && empty($confirm_password_err)) {
         // Prepare an insert statement
          $sql = "UPDATE `users` SET `password`='$param_password', `token`='$param_token' WHERE `id`='$user_id' ";
          //print_r($sql);  die ();

         $res= mysqli_query($link, $sql);             
         $pass = "1";
        // Attempt to execute the prepared statement
        if ($re) {
            // Redirect to dashboard page
            header("location: registered_users.php");
        } 
        else {
            //echo "Something went wrong. Please try again later.";
            echo $password_err;
            echo $confirm_password_err;
        }
    }

 
}

?>


<?php
        //select query statement
        $sq = "SELECT  u.*, s.status_id, s.status_desc, o.organization_id, o.organization_name FROM `users` u Left Outer join `status` s on u.status = s.status_id inner join `organization` o on u.organization_id = o.organization_id where u.organization_id ='$org_id' ";

        $result = mysqli_query($link, $sq);
?>


<head>
    <title>Case Manager - Registered Users List</title>

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
                            <h4 class="mb-sm-0 font-size-18">Manage Users</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Manage Users</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <?php if($pass=='1'){?>
                <div class="alert alert-success text-center h5" role="alert">
					Password Changed Successfully
				</div>
				<?php } ?>
                <!-- end page title -->


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <a href="register.php"> <button class="btn btn-primary waves-effect waves-light btn-sm mb-3"> Add User</button></a>
                                <table id="datatable" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">

                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>First name</th>
                                            <th>Middle Name</th>
                                            <th>Last Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Update</th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        <?php 

                                        while($res = mysqli_fetch_array($result)){

                                            

                                    ?>
                                        <tr>

                                            <td><?php echo($res['id']); ?></td>
                                            <td><?php echo($res['fname']); ?></td>
                                            <td><?php echo($res['mname']); ?></td>
                                            <td><?php echo($res['lname']); ?></td>
                                            <td><?php echo($res['username']); ?></td>
                                            <td><?php echo($res['useremail']); ?></td>
                                            <td><?php echo($res['phone']); ?></td>
                                            <td><?php echo($res['status_desc']); ?></td>
                                            
                                            <?php

                                            ?>
                                                  <td>
                                         <button type="submit" class="btn btn-sm btn-success editbtn" onclick="editDetails(<?php echo $res['id'];?>,'<?php echo $res['fname']?>','<?php echo $res['mname']?>','<?php echo $res['lname']?>','<?php echo $res['useremail']?>','<?php echo $res['phone']?>','<?php echo $res['primary_location_id']?>','<?php echo $res['permission_id']; ?>','<?php echo $res['status_desc']?>','<?php echo $res['organization_name'];?>')">
                                                    <em class="fas fa-pen"></em>
                                                </button>
                                                <button type="submit" class="btn btn-sm btn-info" onclick="editPassword(<?php echo $res['id'];?>)">Change Password

                                                </button>
                                                <?php 
                                                if($res['status'] == "1"){
                                                ?><button type="submit" class="btn btn-sm btn-danger disablebtn waves-effect waves-light" name="disable" value="disable" onclick="disablemodal('<?php echo $res['id'];?>')">
                                            <em class="bx bx-block font-size-12 align-middle me-2"></em>Disable
                                        </button>

                                        <?php 
                                            }
                                            else if($res['status'] == "3"){
                                                ?><button type="submit" class="btn btn-sm btn-success enablebtn waves-effect waves-light" name="enable" value="enable" onclick="enablemodal('<?php echo $res['id'];?>')">
                                            <em class="bx bxs-right-arrow-circle font-size-12 align-middle me-2"></em>Enable
                                        </button><?php 
                                        } 
                                        ?>
                                                
                                    </td>
                                        </tr>
                                        <?php
                                }

                                ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- end cardaa -->
                    </div> <!-- end col -->
                </div> <!-- end row -->



                             <!-- The Edit Modal -->
                        <div class="modal" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">

                              <!-- Modal Header -->
                              <div class="modal-header">
                                <h4 class="modal-title">Edit User Info</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                              </div>
                              <!-- Modal body -->
                              <div class="modal-body">
                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                    <input type="hidden" name="id" id="user_id" >

                                                     <div class="name-style mb-3 <?php echo (!empty($fname_err)) ? 'has-error' : ''; ?>">
                                                        <label for="fname" class="form-label">First Name</label>
                                                        <input type="text" class="form-control" id="fname" placeholder="Enter first name" required name="fname" value="<?php echo $fname; ?>">
                                                        <span class="text-danger"><?php echo $fname_err; ?></span>
                                                    </div>
                                                     <div class="mb-3 name-style1">
                                                        <label for="mname" class="form-label">Middle Name</label>
                                                        <input type="text" class="form-control" id="middlename" placeholder="Enter middle name" name="mname" value="<?php echo $mname; ?>">
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

                                                    <div class="mb-3 phone-style <?php echo (!empty($permission_id_err)) ? 'has-error' : ''; ?>">
                                                        <label for="permission_id" class="form-label">Role</label>
                                                        <select name="permission_id" id="permission_id" class="form-select">
                                                            <option value="-1">Please Select Role</option><?php
                                                            foreach($roles as $res){
                                                                ?><option value="<?php echo $res['id'] ?>"><?php echo $res['role'] ?></option><?php 
                                                            } 
                                                        ?></select>
                                                        <span class="text-danger"><?php echo $permission_id_err; ?></span>
                                                    </div>
                                                    <div class="mb-3 email-style<?php echo (!empty($status_err)) ? 'has-error' : ''; ?>">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select name="status_id" class="form-select">
                                                        <option value="-1">Please Select</option>
                                                        <option id="selectedval" selected></option>
                                                    <?php
                                                    $selectquery = " select * from status ";
                                                    $qn= mysqli_query($link, $selectquery);
                                                    $nums = mysqli_num_rows($qn);
                                                    while ($res = mysqli_fetch_array($qn)) {
                                                        $status_id = (int)$res['status_id'];
                                                        $status_desc = $res['status_desc'];
                                            
                                                        // Output the option with the selected attribute if it matches
                                                        echo "<option value='$status_id'>$status_desc</option>";
                                                    } 
                                                    ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo $status_err; ?></span>
                                                </div>

                                                    <div class="mb-3">
                                                        <label for="groups" class="form-label">Select Groups</label>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <td>Group</td><td>Assign</td>
                                                            </tr>
                                                            <?php
                                                                // echo "<script>alert('".$fx."');</script>";
                                                                $qq = "select * from groups where organization_id ='$org_id' ";
                                                                $rx=mysqli_query($link,$qq);
                                                                while($xx=mysqli_fetch_array($rx))
                                                                {
                                                                    ?>  
                                                                    <tr>
                                                                        <td><?php echo $xx['group_name'] ?></td><td><input class="checkbox" type="checkbox" value="<?php echo $xx['group_id'] ?>" id="group_id<?php echo $xx['group_id'] ?>" name="groupassign[]" ></td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </table>
                                                    </div>
                                                    

                                                    <div class="name-style2 mt-4">
                                                        <button class="btn btn-primary waves-effect waves-light" id="update" name="update" type="submit">Update User</button>
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                              </div>

                            </div>
                          </div>
               
            </div>

            <div class="modal" id="disablemodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                            <button class="btn btn-primary waves-effect waves-light w-25 float-left" onclick="disableuser()">Yes !</button>
                                            <button type="button" class="btn btn-secondary w-25 float-right" data-bs-dismiss="modal">No</button>
                                        </div>
                                    </div>
                                </div>
                          </div>
               
                        </div>
                        <div class="modal" id="enablemodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                            <button class="btn btn-primary waves-effect waves-light w-25 float-left" onclick="enableuser()">Yes !</button>
                                            <button type="button" class="btn btn-secondary w-25 float-right" data-bs-dismiss="modal">No</button>
                                        </div>
                                    </div>
                                </div>
                          </div>
               
                        </div>


                <!-- The Edit Modal -->
                        <div class="modal" id="passwordmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                                     
                                                    
                                                    <div class="mt-4">
                                                        <button class="btn btn-primary waves-effect waves-light" id="changepassword" name="changepassword" type="submit">Change Password</button>
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
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
    function editDetails(user_id, fname, middlename, lname, useremail, phone, primary_location_id, permission_id, status_id, organization_id){
        $('#editmodal').modal('show');
        $('#user_id').val(user_id);
        $('#fname').val(fname);
        $('#middlename').val(middlename);
        $('#lname').val(lname);
        $('#useremail').val(useremail);
        $('#phone').val(phone);
        $('#selectedval').val(primary_location_id);
        $('#permission_id').val(permission_id);
        $('#selectedval').val(status_id);
        $('#selectedval').text(status_id);
        $('#selectedva').val(organization_id);
        $('#selectedva').text(organization_id);
        
        <?php
            $axex = mysqli_query($link,"select * from groups");
            while($xe=mysqli_fetch_array($axex))
            {
        ?> 
            $('#group_id<?php echo $xe['group_id']; ?>').prop("checked",false);
        <?php
            }
        ?>

        <?php
            $ff=mysqli_query($link,"select * from group_user");
            while($xx=mysqli_fetch_array($ff))
            {
        ?>
            uid = <?php echo $xx['user_id'] ?>;
            if(uid==user_id)
            {
                $('#group_id'+<?php echo $xx['group_id'] ?>).prop("checked", true);
            }
                
        <?php
            }
        ?>
    }
</script>
<script>
    function editPassword(id){
        $('#passwordmodal').modal('show');
        $('#id').val(id);
    }
</script>
<script>
    function disablemodal(id) {
        $('#disablemodal').modal('toggle');
        $('#disablemodal').modal('show');
        $('.confirmation_msg').html("Would you like to Disable?");
        $(".id").val(id);
    }
    function disableuser(){
        $('#overlay').show();
        var id = $(".id").val();
        $.ajax({
            url: 'action.php',
            type: 'POST',
            data: 'action=disableuser&id='+id,
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
</script>
<script>
    function enablemodal(id) {
        $('#enablemodal').modal('toggle');
        $('#enablemodal').modal('show');
        $('.confirmation_msg').html("Would you like to Enable?");
        $(".id").val(id);
    }
    function enableuser(){
        $('#overlay').show();
        var id = $(".id").val();
        $.ajax({
            url: 'action.php',
            type: 'POST',
            data: 'action=enableuser&id='+id,
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