<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'modules/api_services/calling_services.php'; ?>


<head>

    <title>Case Manager | Checkin</title>
    <?php include 'layouts/head.php'; ?>
    <script src="assets/libs/sweetalert2/sweetalert.min.js"></script>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>


<div class="coming-content min-vh-100 py-4 px-3 py-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center py-4 py-sm-5">

                    <div class="mb-5">
                        <a href="">
                            <img src="assets/images/logo-sm.svg" alt="" height="30" class="me-1"><span class="logo-txt text-black font-size-22">Case Manager</span>
                        </a>
                    </div>
                    <h2 class="text-black mt-5">Welcome Visitor</h2>
                    
                      
                        <div class="container-fluid p-0">
                        <div class="row row justify-content-center g-0">
                            <div class=" mt-3 col-xxl-6 col-lg-6 col-md-6">
                                
                                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                                    <div class="mb-3 name-style">
                                                        <label for="lname" class="form-label text-black">Last Name</label>
                                                        <input type="text" class="form-control" id="lname" placeholder="Last Name" required name="lname" value="">
                                                    </div>


                                                    <div class="mb-3 name-style ">
                                                        <label for="ssn" class="form-label text-black">Last 4 Digit SSN</label>
                                                        <input type="text" class="form-control" id="lastFourSSN" placeholder="Enter Last 4 SSN" required name="lastFourSSN" value="">
                                                    </div>
                                                  <div class="mb-3">
                                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit" id='confirm_btn' >Check In</button>
                                                  </div>
                                                </form>
                                                
                                <!-- end auth full page content -->
                            </div>
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container fluid -->
                </div>
                                <div class="row justify-content-center mt-3">
                                    <div class="col-xl-5 col-lg-8">
                                        <div class="text-black text-center">
                                            <h5 class="text-black text-center">Can't find your name?</h5>
                                            <p class="text-black">If you cannot locate your name during checkin, please see the front desk.</p>
                                            <div class="auth-page">                            
                                        </div>
                                </div>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</div>
<!-- coming-content -->

        <!-- End Page-content -->
<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>


</body>

</html>
<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {
            $lastName=$_POST['lname'];
            $lastFourSSN=$_POST['lastFourSSN'];
            $updatedByUserId=$_SESSION['id'];    
            $res=call_service_customer_checkin($lastName, $lastFourSSN);
            
            if ($res==1){
                //$info="You are Successfully Checked In. <br>Thank you";
?>
                <script>
                    swal({
                        position: "top-end",
                        icon: "success",
                        title: "Checkin Success",
                        text: "You have Successfully Checked In. Please wait to be called by the Front Desk.",
                        //button: false,
                        timer: 8000
                            });
                </script>
<?php
                
            }else{
                //$info="There is a error in check in.  Please see your admin";
?>
                <script>
                    swal({
                        position: "top-end",
                        icon: "warning",
                        title: "Appointment Not Found!",
                        text: "I was unable to located your appointment. Please try again or see the Front Desk for assistance",
                        //button: false,
                        timer: 10000
                            });
                </script>
<?php                

            }
        
        }

?>