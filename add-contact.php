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
if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
    if (empty($contact_last_4_ssn_err) && empty($contact_email_err) && empty($contact_fname_err) && empty($contact_lname_err) && empty($contact_address1_err) && empty($contact_address2_err) && empty($contact_city_err) && empty($contact_state_err) && empty($contact_zip_err) && empty($contact_pin_err) && empty($contact_phone1_err) && empty($contact_phone2_err) && empty($contact_dob_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO contacts (contact_last_4_ssn, contact_email, contact_fname, contact_lname, contact_address1, contact_address2, contact_city, contact_state, contact_zip, contact_pin, contact_phone1, contact_phone2, contact_dob) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssssssssss", $param_contact_last_4_ssn, $param_contact_email, $param_contact_fname, $param_contact_lname, $param_contact_address1, $param_contact_address2, $param_contact_city, $param_contact_state, $param_contact_zip, $param_contact_pin, $param_contact_phone1, $param_contact_phone2, $param_contact_dob);

            // Set parameters
            $param_contact_last_4_ssn = $contact_last_4_ssn;
            $param_contact_email = $contact_email;
            $param_contact_fname = $contact_fname;
            $param_contact_lname = $contact_lname;
            $param_contact_address1 = $contact_address1;
            $param_contact_address2 = $contact_address2;
            $param_contact_city = $contact_city;
            $param_contact_state = $contact_state;
            $param_contact_zip = $contact_zip;
            $param_contact_pin = $contact_pin;
            $param_contact_phone1 = $contact_phone1;
            $param_contact_phone2 = $contact_phone2;
            $param_contact_dob = $contact_dob;
           

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to dashboard page
                header("location: manage-contacts.php");
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
    <title>Case Manager - Add New Contact</title>
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
                            <h4 class="mb-sm-0 font-size-18">Add New Contact</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Add New Contact</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                    <div class="auth-page">
                        <div class="container-fluid p-0">
                        <div class="row g-0">
                            <div class="col-xxl-10 col-lg-10 col-md-12">
                                <div class="card">
                                <div class="card-body">
                                
                                              <form class="needs-validation custom-form mt-2 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">


                                                <div class="row mb-2">
                                                    <label for="contact_last_4_ssn" class=" lbl col-sm-2 col-form-label <?php echo (!empty($contact_last_4_ssn_err)) ? 'has-error' : ''; ?>">SSN:</label>
                                                    <div class="col-sm-3">
                                                      <input type="hidden" class="form-control form-control-sm" id="contact_id" placeholder="" name="contact_id" value="" >
                                                      <input type="text" class="form-control form-control-sm" id="contact_last_4_ssn" placeholder="" name="contact_last_4_ssn" value="" required onblur="SearchSSNBtnTrigger();">
                                                    
                                                    </div>
                                                    <div class="col-sm-1">
                                                       <input type="button" id="SearchSSNBtn" value="Search" class="btn btn-sm btn-info"/>
                                                    </div> 
                                                    <label for="contact_email" class="col-sm-2 lbl col-form-label <?php echo (!empty($contact_email_err)) ? 'has-error' : ''; ?>">Email:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_email" placeholder="" name="contact_email" value="<?php echo $contact_email; ?>" required>
                                                      <span class="text-danger"><?php echo $contact_email_err; ?></span>
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
                                                     <label for="contact_address2" class="lbl col-sm-2 col-form-label">Address 2:</label>
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
                                                    <label for="contact_phone2" class="col-sm-2 lbl col-form-label">Phone 2:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" id="contact_phone2" placeholder="" maxlength="25" name="contact_phone2" value="<?php echo $contact_phone2; ?>" >
                                                    </div>
                                                    
                                                </div>
                                                
                                                 <div class="row mb-2">
                                                    <label for="contact_dob" class="col-sm-2 lbl col-form-label">DOB:</label>
                                                    <div class="col-sm-4">
                                                        <input type="date" class="form-control form-control-sm" id="contact_dob" placeholder="" name="contact_dob" value="<?php echo $contact_dob; ?>">
                                                    </div>
                                                </div>
                                             
                                                    <div class="mt-4 mb-3">
                                                        <button class="btn btn-primary waves-effect waves-light" id="AddContact" name="AddContact" type="submit"disabled="disabled" onclick="return valid_form();">Add New Contact</button>
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"> <a href="manage-contacts.php"> Cancel</a></button>
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                   
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
    <div class="modal" id="ContactSSNModal" tabindex="-1" role="dialog" aria-labelledby="ContactSSNModal" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">    
                    <div class="modal-header">
                        <h4 class="modal-title">Contact Result</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="ContactSSNModalContent"></div>
                </div>
            </div>  
        </div>

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
<script type="text/javascript">
$('#SearchSSNBtn').click(function(){
                if($('#contact_last_4_ssn').val()==''){
                    $('#ContactSSNModal').modal('show');
                    $('.modal-dialog').removeClass('modal-xl');
                    $('.modal-dialog').addClass('modal-md');
                    $('#ContactSSNModalContent').addClass('p-4');
                    $('#ContactSSNModalContent').addClass('text-center');
                    $('#ContactSSNModalContent').html('<strong style="font-size:22px;" class="text-danger">Please Enter SSN</strong>');
                    $('#contact_last_4_ssn').addClass('border-danger');
                    $('#contact_last_4_ssn').focus();
                }
                else{
                    var contact_last_4_ssn = $('#contact_last_4_ssn').val();
                    $.ajax({
                        url         :   'action.php',
                        dataType    :   'json',
                        data        :   {
                             'contact_last_4_ssn' : contact_last_4_ssn,
                            'action' : 'FindAttSSN',
                        },                         
                        type        :   'post',
                        beforeSend: function (xhr){ 

                        },
                        success     :   function(data){
                            if(data['status']==true){
                                $('#ContactSSNModal').modal('show');
                                
                                $('.modal-dialog').removeClass('modal-md');
                                $('.modal-dialog').addClass('modal-xl');
                                
                                $('#ContactSSNModalContent').removeClass('p-4');
                                $('#ContactSSNModalContent').removeClass('text-center');
                                $('#ContactSSNModalContent').html(data['htmlData']);
                                $('#AddContact').removeAttr('disabled');
                            }  
                            else{
                                if(data['case_exists']==true){
                                    $('#AddContact').attr('disabled', 'disabled');
                                }
                                else  $('#AddContact').removeAttr('disabled');
                                
                                $('#ContactSSNModal').modal('show');
                                
                                $('.modal-dialog').removeClass('modal-xl');
                                $('.modal-dialog').addClass('modal-md');
                                
                                $('#ContactSSNModalContent').addClass('p-4');
                                $('#ContactSSNModalContent').addClass('text-center');
                                $('#ContactSSNModalContent').html(data['htmlData']);
                            }
                        }
                    });             
                }
            });
            
            function SearchSSNBtnTrigger(){
                $('#SearchSSNBtn').trigger('click');
            }
            function getSelectedContactRecords(contact_id){
                $.ajax({
                    url         :   'action.php',
                    dataType    :   'json',
                    data        :   {
                        'contact_id' : contact_id,
                        'action' : 'getSelectedContactRecords',
                    },                         
                    type        :   'post',
                    beforeSend: function (xhr){ },
                    success     :   function(data){    
                        if(data['status']==true){
                            $('#contact_id').val(data['data']['contact_id']);
                            $('#contact_last_4_ssn').val(data['data']['contact_last_4_ssn']);
                            $('#contact_fname').val(data['data']['contact_fname']);
                            $('#contact_lname').val(data['data']['contact_lname']);
                            $('#contact_address1').val(data['data']['contact_address1']);
                            $('#contact_address2').val(data['data']['contact_address2']);
                            $('#contact_city').val(data['data']['contact_city']);
                            $('#contact_state').val(data['data']['contact_state']);
                            $('#contact_zip').val(data['data']['contact_zip']);
                            $('#contact_phone1').val(data['data']['contact_phone1']);
                            $('#contact_phone2').val(data['data']['contact_phone2']);
                        }  
                        else{
                            $('#contact_id').val('');
                            $('#contact_last_4_ssn').val('');
                            $('#contact_fname').val('');
                            $('#contact_lname').val('');
                            $('#contact_address1').val('');
                            $('#contact_address2').val('');
                            $('#contact_city').val('');
                            $('#contact_state').val('');
                            $('#contact_zip').val('');
                            $('#contact_phone1').val('');
                            $('#contact_phone2').val('');
                        }
                        $('#AttSSNModal').modal('hide');
                    }
                });
            }

        </script>
</body>

</html>