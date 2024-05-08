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
$email = $att_firstname = $att_lastname = $att_address1  = $att_address2 = $att_city =  $att_state = $att_zip = $att_pin = $att_phone1 = $att_phone2 = $att_dob =  "";

$email_err = $att_firstname_err = $att_lastname_err =  "";


// Processing form data when form is submitted
if(isset($_POST['update'])) {
    $uid= $_POST['attendant_id'];

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter Email.";
    } else {
        $email = trim($_POST["email"]);
    }
     // Validate firstname
    if (empty(trim($_POST["att_firstname"]))) {
        $att_firstname_err = "Please enter Att first name.";
    } else {
        $att_firstname = trim($_POST["att_firstname"]);
    }
     // Validate lastname
    if (empty(trim($_POST["att_lastname"]))) {
        $att_lastname_err = "Please enter Att last name.";
    } else {
        $att_lastname = trim($_POST["att_lastname"]);
    }
     // Validate address1
        $att_address1 = trim($_POST["att_address1"]);

     // Validate address2
    $att_address2 = trim($_POST["att_address2"]);

     // Validate city
        $att_city = trim($_POST["att_city"]);
  
        $att_state = trim($_POST["att_state"]);
 
     // Validate zip
        $att_zip = trim($_POST["att_zip"]);
 
     // Validate pin
        $att_pin = trim($_POST["att_pin"]);
  

     // Validate dob
 
        $att_dob = date('Y-m-d',strtotime(trim($_POST['att_dob'])));
     // Validate phone1
   
        $att_phone1 = trim($_POST["att_phone1"]);
 
   // Validate phone2
        $att_phone2 = trim($_POST["att_phone2"]);
  
     
    // Check input errors before inserting in database
    if (empty($email_err) && empty($att_firstname_err) && empty($att_lastname_err) && empty($att_address1_err) && empty($att_address2_err) && empty($att_city_err) && empty($att_state_err) && empty($att_zip_err) && empty($att_pin_err) && empty($att_phone1_err) && empty($att_phone2_err) && empty($att_dob_err)) {

        $sql = "UPDATE `attendants` SET `email`='$email', `att_firstname`='$att_firstname', `att_lastname`='$att_lastname', `att_address1`='$att_address1', `att_address2`='$att_address2', `att_city`='$att_city', `att_state`='$att_state', `att_zip`='$att_zip', `att_pin`='$att_pin', `att_phone1`='$att_phone1', `att_phone2`='$att_phone2', `att_dob`='$att_dob' WHERE `attendant_id`='$uid' ";

        $re= mysqli_query($link, $sql);

            // Attempt to execute the prepared statement
            if ($re) {
                // Redirect to dashboard page
                header("location: manage-customers.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }

        
    }
}



?>

<?php
        //select query statement
        $sq = "SELECT * FROM attendants";

        $result = mysqli_query($link, $sq);
?>


<head>
    <title>Case Manager - Manage Customers</title>

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
                            <h4 class="mb-sm-0 font-size-18">Manage Customers</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Manage Customers</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <a href="add-customer.php"> <button class="btn btn-sm btn-primary waves-effect waves-light mb-3"> Add New Customer</button></a>
                                <table id="datatable" class="table table-bordered dt-responsive nowrap w-100 mt-3 mb-3">

                                    <thead>
                                        <tr>
                                            <th>Customer ID</th>
                                            <th>First name</th>
                                            <th>Last Name</th>
                                            <th>Email</th>
                                            <th>Address1</th>
                                            <th>Address2</th>
                                            <th>Phone</th>
                                            <th>Alt Phone</th>
                                            <th>Update</th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        <?php 

                                        while($res = mysqli_fetch_array($result)){

                                            

                                    ?>
                                        <tr>

                                            <td><?php echo($res['attendant_id']); ?></td>
                                            <td><?php echo($res['att_firstname']); ?></td>
                                            <td><?php echo($res['att_lastname']); ?></td>
                                            <td><?php echo($res['email']); ?></td>
                                            <td><?php echo($res['att_address1']); ?></td>
                                            <td><?php echo($res['att_address2']); ?></td>
                                            <td><?php echo($res['att_phone1']); ?></td>
                                            <td><?php echo($res['att_phone2']); ?></td>
                                           
                                                  <td>
                                         <button type="submit" class="btn btn-sm btn-success editbtn" onclick="editDetails(<?php echo $res['attendant_id'];?>,'<?php echo $res['email']?>','<?php echo $res['att_firstname']?>','<?php echo $res['att_lastname']?>','<?php echo $res['att_address1']?>','<?php echo $res['att_address2']?>','<?php echo $res['att_city']?>','<?php echo $res['att_state']?>','<?php echo $res['att_zip']?>','<?php echo $res['att_pin']?>','<?php echo $res['att_phone1']?>','<?php echo $res['att_phone2']?>')">
                                                    <em class="fas fa-pen"></em>
                                                </button>
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
                                <h4 class="modal-title">Edit Customer Details</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                              </div>
                              <!-- Modal body -->
                              <div class="modal-body">
                                <form class="needs-validation custom-form mt-2 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                    <input type="hidden" name="attendant_id" id="attendant_id">


                                                <div class="row mb-2">
                                                    <label for="email" class="col-sm-3 col-form-label <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">Email:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="email" placeholder="" name="email" value="<?php echo $email; ?>" required>
                                                      <span class="text-danger"><?php echo $email_err; ?></span>
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <label for="att_firstname" class="col-sm-3 col-form-label <?php echo (!empty($att_firstname_err)) ? 'has-error' : ''; ?>">First Name:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="att_firstname" placeholder="" name="att_firstname" value="<?php echo $att_firstname; ?>" required>
                                                      <span class="text-danger"><?php echo $att_firstname_err; ?></span>
                                                    </div>
                                                   <label for="att_lastname" class="offset-1 col-sm-3 col-form-label <?php echo (!empty($att_lastname_err)) ? 'has-error' : ''; ?>">Last Name:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="att_lastname" placeholder="" name="att_lastname" value="<?php echo $att_lastname; ?>" required>
                                                      <span class="text-danger"><?php echo $att_lastname_err; ?></span>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="att_address1" class="col-sm-3 col-form-label">Address1:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="att_address1" placeholder="" name="att_address1" value="<?php echo $att_address1; ?>">
                                                     
                                                    </div>
                                                     <label for="att_address2" class="offset-1 col-sm-3 col-form-label">Address2:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="att_address2" placeholder="" name="att_address2" value="<?php echo $att_address2; ?>">
                                                      
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="att_city" class="col-sm-1 col-form-label">City:</label>
                                                    <div class="col-sm-1">
                                                      <input type="text" class="form-control form-control-sm" id="att_city" placeholder="" name="att_city" value="<?php echo $att_city; ?>">
                                                    </div>
                                            
                                                    <label for="att_state" class="col-sm-1 col-form-label">State:</label>
                                                    <div class="col-sm-1">
                                                        <input type="text" class="form-control form-control-sm" id="att_state" placeholder="" name="att_state" value="<?php echo $att_state; ?>">
                                                        
                                                    </div>
                                                    <label for="att_zip" class="col-sm-1 col-form-label">ZIP:</label>
                                                    <div class="col-sm-1">
                                                        <input type="text" class="form-control form-control-sm" maxlength="10" id="att_zip" placeholder="" name="att_zip" value="<?php echo $att_zip; ?>">
                                                    </div>
                                                    <label for="att_pin" class="offset-1 col-sm-2 col-form-label">PIN:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="att_pin" placeholder="" name="att_pin" value="<?php echo $att_pin; ?>">
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="att_phone1" class="col-sm-3 col-form-label">Phone1:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="att_phone1" placeholder="" maxlength="25" name="att_phone1" value="<?php echo $att_phone1; ?>">
                                                    </div>
                                                    <label for="att_phone2" class="offset-1 col-sm-3 col-form-label">Phone2:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="att_phone2" placeholder="" maxlength="25" name="att_phone2" value="<?php echo $att_phone2; ?>">
                                                      
                                                    </div>
                                                    
                                                </div>
                                                
                
                                             
                                                    <div class="mt-4 mb-3">
                                                        <button class="btn btn-primary waves-effect waves-light" id="update" name="update" type="submit">Update Customer Details</button>
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
    function editDetails(attendant_id, email, att_firstname, att_lastname, att_address1, att_address2, att_city, att_state, att_zip, att_pin, att_phone1, att_phone2){
        $('#editmodal').modal('show');
        $('#attendant_id').val(attendant_id);
        $('#email').val(email);
        $('#att_firstname').val(att_firstname);
        $('#att_lastname').val(att_lastname);
        $('#att_address1').val(att_address1);
        $('#att_address2').val(att_address2);
        $('#att_city').val(att_city);
        $('#att_state').val(att_state);
        $('#att_zip').val(att_zip);
        $('#att_pin').val(att_pin);
        $('#att_phone1').val(att_phone1);
        $('#att_phone2').val(att_phone2);
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