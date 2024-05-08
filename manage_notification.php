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
$title = $created_at = $type = $status = $user=  "";
$title_err = $type_err = $status_err = $name_err=  "";

// Processing form data when form is submitted
if(isset($_POST['update'])) {
    $gid= $_POST['id'];
    
    // Validate title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    // Validate type
    if (empty(trim($_POST["type"]))) {
        $type_err = "Please enter a type.";
    } else {
        $type = trim($_POST["type"]);
    }
    
    // Status is not required / Do not Validate status
        $status = trim($_POST["nstatus"]);
    
    
    // Validate created date
    
        $created_at = trim($_POST["created_at"]);
    //Name 
    $user = trim($_POST["user"]);
    

    // Check input errors before inserting in database
    if (empty($title_err) && empty($type_err)) {
        echo $sql = "UPDATE `notifications` SET `title`='$title', `type`='$type', `status`='$status' WHERE `id`=$gid ";

        $res= mysqli_query($link, $sql);

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


?>


<?php
//$uid=$_SESSION["id"]; 
 //select query statement
         //$sql = "SELECT id, title, description, created_at as created_date, type, status FROM casemanager.notifications ";
        // $result = mysqli_query($link, $sql);
// Check the filter parameter in the URL (default to "all" if not set)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

 //Fetch tasks based on the filter
if ($filter === 'all') {
    $sql ="SELECT  n.*, CONCAT(u.fname ,' ', u.mname ,' ', u.lname) AS user FROM `notifications` n Left Outer join `users` u on n.user_id = u.id";

} elseif ($filter === 'user') {
    $uid=$_SESSION["id"]; 
    $sql = "SELECT  n.*, CONCAT(u.fname ,' ', u.mname ,' ', u.lname) AS user FROM `notifications` n Left Outer join `users` u on n.user_id = u.id where user_id= '$uid'";

} else {
    // Handle invalid filter values
    echo "Invalid filter value.";
    exit();
}
 
 
$result = mysqli_query($link, $sql);
?>

<?php
        //select query statement
        //$sq = "SELECT  n.*, CONCAT(u.fname ,' ', u.mname ,' ', u.lname) AS user FROM `notifications` n Left Outer join `users` u on n.user_id = u.id";

       // $result = mysqli_query($link, $sq);

?>

<head>
    <title>Case Manager - Notifications List</title>

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
                            <h4 class="mb-sm-0 font-size-18">Manage Notification</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Manage Notification</li>
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
                                <table id="datatable-buttons" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">

                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Created Date</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Is Read</th>
                                            <th>User</th>
                                            <th>Update</th>
                                            
                                        </tr>
                                    </thead>


                                    <tbody>
                                        <?php 

                                        while($res = mysqli_fetch_array($result)){

                                            

                                    ?>
                                        <tr>

                                            <td><?php echo($res['id']); ?></td>
                                            <td><?php echo($res['title']); ?></td>
                                            <td><?php echo($res['description']); ?></td>
                                            <td><?php echo($res['created_at']); ?></td>
                                            <td><?php echo($res['type']); ?></td>
                                            <td><?php echo($res['status']); ?></td>
                                            <td><?php echo($res['is_read']); ?></td>
                                            <td><?php echo($res['user']); ?></td>
                                     <td>

                                    <button type="submit" class="btn btn-sm btn-success editbtn" onclick="editDetails(<?php echo $res['id'];?>,'<?php echo $res['title'];?>','<?php echo $res['type']?>','<?php echo $res['status']?>','<?php echo $res['created_at']?>','<?php echo $res['user']?>')">
                                                    <em class="fas fa-pen"></em>
                                                </button>

                                     
                                    <?php 
                                                if($res['is_read'] == "Yes"){
                                                ?><button type="submit" class="btn btn-sm btn-danger disablebtn waves-effect waves-light" name="disable" value="disable" onclick="disablemodal('<?php echo $res['id'];?>')">
                                            <em class="bx bx-block font-size-12 align-middle me-2"></em>No
                                        </button>

                                        <?php 
                                            }
                                            else if($res['is_read'] == "No"){
                                                ?><button type="submit" class="btn btn-sm btn-success enablebtn waves-effect waves-light" name="enable" value="enable" onclick="enablemodal('<?php echo $res['id'];?>')">
                                            <em class="bx bxs-right-arrow-circle font-size-12 align-middle me-2"></em>Yes
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
                                            <button type="button" class="btn btn-secondary w-25 float-right" data-bs-dismiss="modal">No !</button>
                                        </div>
                                    </div>
                                </div>
                          </div>
               
                        </div>
                        

                             <!-- The Edit Modal -->
                        <div class="modal" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">

                              <!-- Modal Header -->
                              <div class="modal-header">
                                <h4 class="modal-title">Edit Notification Info</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                              </div>
                              <!-- Modal body -->
                              <div class="modal-body">
                                <form class="needs-validation custom-form mt-2 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                    <input type="hidden" name="id" id="id">


                                                   <div class="row mb-2">
                                                    <label for="title" class="col-sm-3 col-form-label <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">Title:</label>
                                                    <div class="col-sm-5">
                                                      <input type="text" class="form-control form-control-sm" maxlength="50" id="title" placeholder="" name="title" value="<?php echo $title; ?>" required>
                                                      <span class="text-danger"><?php echo $title_err; ?></span>
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <label for="type" class="col-sm-3 col-form-label <?php echo (!empty($type_err)) ? 'has-error' : ''; ?>">Type:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="type" placeholder="" name="type" value="<?php echo $type; ?>" required>
                                                      <span class="text-danger"><?php echo $type_err; ?></span>
                                                    </div>
                                                    <label for="status" class="col-sm-3 col-form-label <?php echo (!empty($status_err)) ? 'has-error' : ''; ?>">Status:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="nstatus" placeholder="" name="nstatus" value="<?php echo $status; ?>">
                                                      <span class="text-danger"><?php echo $status_err; ?></span>
                                                    </div>
                                                </div>

                                                    <div class="row mt-3 ">
                                                        <label for="created_at" class="col-sm-3 form-label">Created Date</label>
                                                        <div class="col-auto">
                                                        <input type="text" class="form-control form-control-sm" id="created_at" placeholder="" name="created_at" value="<?php echo $created_at; ?>" readonly>
                                                        </div> 
                                                    
                                                        <label for="user" class="col-sm-3 col-form-label <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">User:</label>
                                                    <div class="col-sm-2">
                                                      <input type="text" class="form-control form-control-sm" id="user" placeholder="" name="user" value="<?php echo $user; ?>" readonly>
                                                      <span class="text-danger"><?php echo $name_err; ?></span>
                                                    </div>
                                                    </div>
                                                   

                                                    <div class="mt-4">
                                                        <button class="btn btn-primary waves-effect waves-light" id="update" name="update" type="submit">Update Notification</button>
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
    function editDetails(id, title, type, status, created_at, user){
        $('#editmodal').modal('show');
        $('#id').val(id);
        $('#title').val(title);
        $('#type').val(type);
        $('#nstatus').val(status);
        $('#created_at').val(created_at);
        $('#user').val(user);
    }
</script>

<script>
    function disablemodal(id) {
        $('#disablemodal').modal('toggle');
        $('#disablemodal').modal('show');
        $('.confirmation_msg').html("Are you sure You want to change it to NO ?");
        $(".id").val(id);
    }
    function disableuser(){
        $('#overlay').show();
        var id = $(".id").val();
        var newValue = $(this).data('value');
        $.ajax({
            url: 'update_is_read.php',
            type: 'POST',
            data: 'update_is_read=disableuser&id='+id,
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
        $('.confirmation_msg').html("Are you sure You want to change it to YES ?");
        $(".id").val(id);
    }
    function enableuser(){
        $('#overlay').show();
        var id = $(".id").val();
        $.ajax({
            url: 'update_is_read.php',
            type: 'POST',
            data: 'update_is_read=enableuser&id='+id,
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
   $(document).ready(function(){
       var table=$('#datatable-buttons').DataTable();
       table.order([0,'desc']).draw();
   });
</script>
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
<!-- Keep Nav Menu Open and Highlight link -->    
<script>
        $('#mm-admin').attr('aria-expanded', true);
        $('#mm-admin').addClass('mm-show')
        $('#mm-admin').css('height', 'auto')
        $('#mm-admin').parent().addClass('mm-active');
        $($('#mm-admin').children()[3]).children().eq(0).css('color', '#1c84ee');
    </script>
</body>

</html>