<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php';
include 'layouts/session.php'; 
// Define variables and initialize with empty values
$location_name = $location_address1 = $location_address2 = $city = $state = $zip = "";

$last_updated_by = $_SESSION["id"];
$last_updated_datetime = date('Y-m-d H:i:s');

$location_name_err = $location_address1_err = $location_address2_err = $city_err = $state_err = $zip_err = "";

// Processing form data when form is submitted
if (isset($_POST['update'])) {


    $uid= $_POST['location_id'];

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

        $sql = "UPDATE location SET location_name='$location_name',location_address1='$location_address1',location_address2='$location_address2',city='$city',state='$state',zip='$zip',last_updated_by='$last_updated_by',last_updated_datetime='$last_updated_datetime' WHERE location_id='$uid'";

        $res= mysqli_query($link, $sql);

            // Attempt to execute the prepared statement
            if ($res) {
                // Redirect to dashboard page
                header("location: edit_location.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }
    }
?>


<?php
        //select query statement
        $sq = "SELECT * FROM location";

        $result = mysqli_query($link, $sq);
?>


<head>
    <title>Case Manager - Locations</title>

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
                            <h4 class="mb-sm-0 font-size-18">Manage Locations</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);"> Management</a></li>
                                    <li class="breadcrumb-item active">Manage Locations</li>
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
                               <a href="add_location.php"> <button class="btn btn-primary waves-effect waves-light btn-lg mb-3"> Add Location</button></a>
                                <table id="datatable" class="table table-bordered dt-responsive nowrap w-100 mt-3 mb-3">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Location Name</th>
                                            <th>Address 1</th>
                                            <th>Address 2</th>
                                            <th>City</th>
                                            <th>State</th>
                                            <th>Zip</th>
                                            <th>Created_datetime</th>
                                            <th>Update</th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        <?php 

                                        while($res = mysqli_fetch_array($result)){

                                            

                                    ?>
                                        <tr>

                                            <td><?php echo($res['location_id']); ?></td>
                                            <td><?php echo($res['location_name']); ?></td>
                                            <td><?php echo($res['location_address1']); ?></td>
                                            <td><?php echo($res['location_address2']); ?></td>
                                            <td><?php echo($res['city']); ?></td>
                                            <td><?php echo($res['state']); ?></td>
                                            <td><?php echo($res['zip']); ?></td>
                                            <td><?php echo($res['created_datetime']); ?></td>
                                                  <td>
                                        <button type="submit" class="btn btn-success editbtn">
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
                                <h4 class="modal-title">Update Location</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                              </div>
                              <!-- Modal body -->
                              <div class="modal-body">
                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                    <input type="hidden" name="location_id" id="location_id">

                                    <div class="mb-3 <?php echo (!empty($location_name_err)) ? 'has-error' : ''; ?>">
                                                        <label for="locationname" class="form-label">Location Name</label>
                                                        <input type="text" class="form-control" id="locationname" placeholder="Enter location name" required name="location_name" value="<?php echo $location_name; ?>">
                                                        <span class="text-danger"><?php echo $location_name_err; ?></span>
                                                    </div>
                                                      <div class="mb-3 <?php echo (!empty($location_address1_err)) ? 'has-error' : ''; ?>">
                                                        <label for="address1" class="form-label">Location Address 1</label>
                                                        <input type="text" class="form-control" id="address1" placeholder="Enter Location Address 1" required name="location_address1" value="<?php echo $location_address1; ?>">
                                                        <span class="text-danger"><?php echo $location_address1_err; ?></span>
                                                    </div>
                                                     <div class="mb-3 <?php echo (!empty($location_address2_err)) ? 'has-error' : ''; ?>">
                                                        <label for="address2" class="form-label">Location Address 2</label>
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
                                        <option id="selectedval" selected></option>
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
                                                        <button class="btn btn-primary waves-effect waves-light" id="update" name="update" type="submit">Update Location</button>
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
        $(document).ready(function () {

            $('.editbtn').on('click', function () {

                $('#editmodal').modal('show');

                 $tr = $(this).closest('tr');

                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();

                console.log(data);

                $('#location_id').val(data[0]);
                $('#locationname').val(data[1]);
                $('#address1').val(data[2]);
                 $('#address2').val(data[3]);
                $('#city').val(data[4]);
                $('#selectedval').val(data[5]);
                 $('#selectedval').text(data[5]);
                $('#zip').val(data[6]);
                

               

            });
        });



    </script>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Plugins js-->
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>
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