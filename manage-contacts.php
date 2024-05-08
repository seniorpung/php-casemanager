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
$contact_email = $contact_last_4_ssn = $contact_fname = $contact_lname = $contact_address1  = $contact_address2 = $contact_city =  $contact_state = $contact_zip = $contact_pin = $contact_phone1 = $contact_phone2 = $contact_dob =  "";

$contact_email_err = $contact_last_4_ssn_err = $contact_fname_err = $contact_lname_err =  "";


// Processing form data when form is submitted
if(isset($_POST['update'])) {
    $uid= $_POST['contact_id'];

    // Validate email
    if (empty(trim($_POST["contact_email"]))) {
        $contact_email_err = "Please enter Email.";
    } else {
        $contact_email = trim($_POST["contact_email"]);
    }
    // Validate ssn
    if (empty(trim($_POST["contact_last_4_ssn"]))) {
        $contact_last_4_ssn_err = "Please enter SSN.";
    } else {
        $contact_last_4_ssn = trim($_POST["contact_last_4_ssn"]);
    }
     // Validate firstname
    if (empty(trim($_POST["contact_fname"]))) {
        $contact_fname_err = "Please enter first name.";
    } else {
        $contact_fname = trim($_POST["contact_fname"]);
    }
     // Validate lastname
    if (empty(trim($_POST["contact_lname"]))) {
        $contact_lname_err = "Please enter last name.";
    } else {
        $contact_lname = trim($_POST["contact_lname"]);
    }
     // Validate address1
        $contact_address1 = trim($_POST["contact_address1"]);

     // Validate address2
    $contact_address2 = trim($_POST["contact_address2"]);

     // Validate city
        $contact_city = trim($_POST["contact_city"]);
  
        $contact_state = trim($_POST["contact_state"]);
 
     // Validate zip
        $contact_zip = trim($_POST["contact_zip"]);
 
     // Validate pin
        $contact_pin = trim($_POST["contact_pin"]);
  

     // Validate dob
 
        $contact_dob = date('Y-m-d',strtotime(trim($_POST['contact_dob'])));
     // Validate phone1
   
        $contact_phone1 = trim($_POST["contact_phone1"]);
 
   // Validate phone2
        $contact_phone2 = trim($_POST["contact_phone2"]);
  
     
    // Check input errors before inserting in database
    if (empty($contact_email_err) && empty($contact_last_4_ssn_err) && empty($contact_fname_err) && empty($contact_lname_err) && empty($contact_address1_err) && empty($contact_address2_err) && empty($contact_city_err) && empty($contact_state_err) && empty($contact_zip_err) && empty($contact_pin_err) && empty($contact_phone1_err) && empty($contact_phone2_err) && empty($contact_dob_err)) {

        $sql = "UPDATE `contacts` SET `contact_email`='$contact_email',`contact_last_4_ssn`='$contact_last_4_ssn', `contact_fname`='$contact_fname', `contact_lname`='$contact_lname', `contact_address1`='$contact_address1', `contact_address2`='$contact_address2', `contact_city`='$contact_city', `contact_state`='$contact_state', `contact_zip`='$contact_zip', `contact_pin`='$contact_pin', `contact_phone1`='$contact_phone1', `contact_phone2`='$contact_phone2', `contact_dob`='$contact_dob' WHERE `contact_id`='$uid' ";

        $re= mysqli_query($link, $sql);

            // Attempt to execute the prepared statement
            if ($re) {
                // Redirect to dashboard page
                header("location: manage-contacts.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }

        
    }
}



?>

<?php
        //select query statement
        $sq = "SELECT * FROM casemanager.contacts";

        $result = mysqli_query($link, $sq);
?>


<head>
    <title>Case Manager - Manage Contacts</title>

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
                            <h4 class="mb-sm-0 font-size-18">Manage Contacts</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Manage Contacts</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                        <div class="row">
                            <div class="col-12">
                                <a href="add-contact.php"> <button class="btn btn-sm btn-primary waves-effect waves-light mb-3"> Add New Contact</button></a>
                                <table id="datatable-buttons" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">
                                    <thead>
                                        <tr>
                                            <th>Contact ID</th>
                                            <th>First name</th>
                                            <th>Last Name</th>
                                            <th>Email</th>
                                            <th>SSN</th>
                                            <th>Address 1</th>
                                            <th>Address 2</th>
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
                                            <?php
                                             $ssn=$res['contact_last_4_ssn'];
                                             $ssn_4_digit = substr("$ssn",-4);
                                            ?>

                                            <td><?php echo($res['contact_id']); ?></td>
                                            <td><?php echo($res['contact_fname']); ?></td>
                                            <td><?php echo($res['contact_lname']); ?></td>
                                            <td><?php echo($res['contact_email']); ?></td>
                                            <td><?php echo $ssn_4_digit; ?></td>
                                            <td><?php echo($res['contact_address1']); ?></td>
                                            <td><?php echo($res['contact_address2']); ?></td>
                                            <td><?php echo($res['contact_phone1']); ?></td>
                                            <td><?php echo($res['contact_phone2']); ?></td>
                                           
                                                  <td>
                                         <button type="submit" class="btn btn-sm btn-success editbtn" onclick="editDetails(<?php echo $res['contact_id'];?>,'<?php echo $res['contact_email']?>','<?php echo $res['contact_last_4_ssn']?>','<?php echo $res['contact_fname']?>','<?php echo $res['contact_lname']?>','<?php echo $res['contact_address1']?>','<?php echo $res['contact_address2']?>','<?php echo $res['contact_city']?>','<?php echo $res['contact_state']?>','<?php echo $res['contact_zip']?>','<?php echo $res['contact_pin']?>','<?php echo $res['contact_phone1']?>','<?php echo $res['contact_phone2']?>')">
                                                    <em class="fas fa-pen"></em>
                                                </button>
                                    </td>
                                        </tr>
                                        <?php
                                }

                                ?>
                                    </tbody>
                                </table>
                        <!-- end cardaa -->
                    </div> <!-- end col -->
                </div> <!-- end row -->




                             <!-- The Edit Modal -->
                        <div class="modal" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">

                              <!-- Modal Header -->
                              <div class="modal-header">
                                <h4 class="modal-title">Edit Contact Details</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                              <!-- Modal body -->
                              <div class="modal-body">
                                <form class="needs-validation custom-form mt-2 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                    <input type="hidden" name="contact_id" id="contact_id">


                                                <div class="row mb-2">
                                                    <label for="contact_email" class=" lbl col-sm-2 col-form-label <?php echo (!empty($contact_email_err)) ? 'has-error' : ''; ?>">Email:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_email" placeholder="" name="contact_email" value="<?php echo $contact_email; ?>" required>
                                                      <span class="text-danger"><?php echo $contact_email_err; ?></span>
                                                    </div>
                                                    <label for="contact_last_4_ssn" class=" lbl col-sm-2 col-form-label <?php echo (!empty($contact_last_4_ssn_err)) ? 'has-error' : ''; ?>">SSN:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_last_4_ssn" placeholder="" name="contact_last_4_ssn" value="<?php echo $contact_last_4_ssn; ?>" required>
                                                      <span class="text-danger"><?php echo $contact_last_4_ssn_err; ?></span>
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <label for="contact_fname" class="col-sm-2 lbl col-form-label <?php echo (!empty($contact_fname_err)) ? 'has-error' : ''; ?>">First Name:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_fname" placeholder="" name="contact_fname" value="<?php echo $contact_fname; ?>" required>
                                                      <span class="text-danger"><?php echo $contact_fname_err; ?></span>
                                                    </div>
                                                   <label for="contact_lname" class="lbl col-sm-2 col-form-label <?php echo (!empty($contact_lname_err)) ? 'has-error' : ''; ?>">Last Name:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_lname" placeholder="" name="contact_lname" value="<?php echo $contact_lname; ?>" required>
                                                      <span class="text-danger"><?php echo $contact_lname_err; ?></span>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_address1" class="col-sm-2 lbl col-form-label">Address 1:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_address1" placeholder="" name="contact_address1" value="<?php echo $contact_address1; ?>">
                                                     
                                                    </div>
                                                     <label for="contact_address2" class="col-sm-2 lbl col-form-label">Address 2:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_address2" placeholder="" name="contact_address2" value="<?php echo $contact_address2; ?>">
                                                      
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_city" class="col-sm-2 lbl col-form-label">City:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_city" placeholder="" name="contact_city" value="<?php echo $contact_city; ?>">
                                                      
                                                    </div>
                                                     <label for="contact_state" class="col-sm-2 lbl col-form-label">State:</label>
                                                    <div class="col-sm-4">
                                                        <input type="text" class="form-control form-control-sm" id="contact_state" placeholder="" name="contact_state" value="<?php echo $contact_state; ?>">
                                                        
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_zip" class="col-sm-2 lbl col-form-label">ZIP:</label>
                                                    <div class="col-sm-4">
                                                        <input type="text" class="form-control form-control-sm" maxlength="10" id="contact_zip" placeholder="" name="contact_zip" value="<?php echo $contact_zip; ?>">
                                                        
                                                    </div>
                                                    <label for="contact_pin" class="col-sm-2 lbl col-form-label">PIN:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_pin" placeholder="" name="contact_pin" value="<?php echo $contact_pin; ?>">
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <label for="contact_phone1" class="col-sm-2 lbl col-form-label">Phone 1:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_phone1" placeholder="" maxlength="25" name="contact_phone1" value="<?php echo $contact_phone1; ?>">
                                                    </div>
                                                    <label for="contact_phone2" class="lbl col-sm-2 col-form-label">Phone 2:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_phone2" placeholder="" maxlength="25" name="contact_phone2" value="<?php echo $contact_phone2; ?>">
                                                      
                                                    </div>
                                                    
                                                </div>
                                                
                
                                             
                                                    <div class="mt-4 mb-3">
                                                        <button class="btn btn-primary waves-effect waves-light" id="update" name="update" type="submit">Update Contact Details</button>
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
    function editDetails(contact_id, contact_email, contact_last_4_ssn,  contact_fname, contact_lname, contact_address1, contact_address2, contact_city, contact_state, contact_zip, contact_pin, contact_phone1, contact_phone2){
        $('#editmodal').modal('show');
        $('#contact_id').val(contact_id);
        $('#contact_email').val(contact_email);
        $('#contact_last_4_ssn').val(contact_last_4_ssn);
        $('#contact_fname').val(contact_fname);
        $('#contact_lname').val(contact_lname);
        $('#contact_address1').val(contact_address1);
        $('#contact_address2').val(contact_address2);
        $('#contact_city').val(contact_city);
        $('#contact_state').val(contact_state);
        $('#contact_zip').val(contact_zip);
        $('#contact_pin').val(contact_pin);
        $('#contact_phone1').val(contact_phone1);
        $('#contact_phone2').val(contact_phone2);
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
<!-- Session timeout js -->
<script src="assets/libs/@curiosityx/bootstrap-session-timeout/index.js"></script>

<!-- Session timeout init js -->
<script src="assets/js/pages/session-timeout.init.js"></script>
<script>
    
    $(document).ready(function() {
   // DataTables initialisation
        $('#datatable-buttons').DataTable();
        //Buttons examples
        var manage_tasks = $('#datatable-buttons').DataTable({
            destroy: true,
            lengthChange: true,
            "pageLength": 100
            //buttons: ['copy', 'excel', 'pdf', 'colvis']
        });

        manage_tasks.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');

        $(".dataTables_length select").addClass('form-select form-select-sm');

        // Refilter the table
        //$('#min, #max').on('change', function () {
            //table_order_history.draw();
        //});
  });

    
</script>

</body>

</html>