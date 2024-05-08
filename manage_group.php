<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php';
include 'layouts/session.php'; 
include 'modules/api_services/calling_services.php';

global $link;
if(!class_exists('CRUD'))    include 'class.crud.php';
$crudObj = new CRUD();
$crudObj->mysqli = $link;

$roles = $crudObj->FindAll('roles', array(), array(), 0, 0, array(array('id', 'ASC')));

// Define variables and initialize with empty values
$gname = $organization_id = $gdesc = "";
$gname_err = $organization_id_err = $gdesc_err = "";
$org_id = Trim($_SESSION["organization_id"]);


// Processing form data when form is submitted
if(isset($_POST['update'])) {
    $gid= $_POST['group_id'];
    
    // Validate gname
    if (empty(trim($_POST["gname"]))) {
        $gname_err = "Please enter a group name.";
    } else {
        $gname = trim($_POST["gname"]);
    }
    // Validate gdesc
    if (empty(trim($_POST["gdesc"]))) {
        $gdesc_err = "Please enter a group description.";
    } else {
        $gdesc = trim($_POST["gdesc"]);
    }
    

    // Check input errors before inserting in database
    if (empty($gname_err) && empty($gdesc_err)) {
        $sql = "UPDATE `groups` SET `group_name`='$gname', `group_desc`='$gdesc' WHERE `group_id`=$gid ";
        
        $res= mysqli_query($link, $sql);

        //11/08/2023-zhiling added below lines to call saveLog service
        $input_actionByUserId = ($_SESSION["id"]);
        // call the saveLog service with premeters of actionId, context, context primary Id, actionByUserId)
        $resFromLog=call_service_save_activityLog (2, "groups", $gid, $input_actionByUserId );
        
    }
}


?>


<?php
        //select query statement
         $sq = "SELECT  g.*, o.organization_id, o.organization_name FROM `groups` g inner join `organization` o on g.organization_id = o.organization_id where g.organization_id ='$org_id' ";

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
                            <h4 class="mb-sm-0 font-size-18">Manage Groups</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">Manage Groups</li>
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
                                <a href="add_group.php"> <button class="btn btn-primary waves-effect waves-light btn-sm mb-3"> Add Group</button></a>
                                <table id="datatable" class="table table-sm table-striped table-bordered dt-responsive nowrap w-100 mt-3 mb-3">

                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Group Name</th>
                                            <th>Group Description</th>
                                            <th>Organization</th>
                                            <th>Update</th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        <?php 

                                        while($res = mysqli_fetch_array($result)){

                                            

                                    ?>
                                        <tr>

                                            <td><?php echo($res['group_id']); ?></td>
                                            <td><?php echo($res['group_name']); ?></td>
                                            <td><?php echo($res['group_desc']); ?></td>
                                            <td><?php echo($res['organization_name']); ?></td>
                                                  <td>
                                         <button type="submit" class="btn btn-sm btn-success editbtn" onclick="editDetails(<?php echo $res['group_id'];?>,'<?php echo $res['organization_name'];?>','<?php echo $res['group_name']?>','<?php echo $res['group_desc']?>')">
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
                                <h4 class="modal-title">Edit Group Info</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>></button>
                              </div>
                              <!-- Modal body -->
                              <div class="modal-body">
                                <form class="needs-validation custom-form mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                    <input type="hidden" name="group_id" id="group_id">
                                                     <div class="mb-6 <?php echo (!empty($gname_err)) ? 'has-error' : ''; ?>">
                                                        <label for="gname" class="form-label">Group Name</label>
                                                        <input type="text" class="form-control form-control-sm" id="gname" placeholder="Enter group name" required name="gname" value="<?php echo $gname; ?>">
                                                        <span class="text-danger"><?php echo $gname_err; ?></span>
                                                    </div>
                                                     <div class="mb-6 <?php echo (!empty($gdesc_err)) ? 'has-error' : ''; ?>">
                                                        <label for="gdesc" class="form-label">Group Description</label>
                                                        <input type="text" class="form-control form-control-sm" id="gdesc" placeholder="Enter group description" required name="gdesc" value="<?php echo $gdesc; ?>">
                                                        <span class="text-danger"><?php echo $gdesc_err; ?></span>
                                                    </div>

                                                    <div class="mt-4">
                                                        <button class="btn btn-primary waves-effect waves-light" id="update" name="update" type="submit">Update Group</button>
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
    function editDetails(group_id, organization_id, gname, gdesc){
        $('#editmodal').modal('show');
        $('#group_id').val(group_id);
        $('#selectedval').val(organization_id);
        $('#selectedval').text(organization_id);
        $('#gname').val(gname);
        $('#gdesc').val(gdesc);
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