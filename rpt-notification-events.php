<?php

// Include config file
require_once "layouts/config.php";
 include 'layouts/session.php'; 
 include 'layouts/head-main.php'; 
 

  global $link;
include 'class.crud.php';

// Define variables 
$min = '';
$max = '';
$whereConditionStr = '';


$min = date('Y-m-d', strtotime('-90 days'));
$max = date('Y-m-d');

//select query statement
if (isset($_GET['filter_btn'])) {
    $whereCondition = array();
    if (isset($_GET['min']) && !empty($_GET['min'])) {
        $whereCondition[] = 'DATE(`created_datetime`)>=\'' . date('Y-m-d', strtotime($_GET['min'])) . '\'';
        $min = $_GET['min'];
    }
    if (isset($_GET['max']) && !empty($_GET['max'])) {
        $whereCondition[] = ' DATE(`created_datetime`) <=\'' . date('Y-m-d', strtotime($_GET['max'])) . '\'';
        $max = $_GET['max'];
    }
    if (isset($_GET['filter_btn']) && !empty($_GET['max']))
        $whereConditionStr = ' AND ' . implode(' AND ', $whereCondition);
        $whereConditionStr = str_replace("''","'", $whereConditionStr);
        //$whereConditionStr = 'Where `created_datetime` >= CURDATE() - INTERVAL 30 DAY';
        
} else {
    //limit view to most current 90 days when no condition is provided
    $whereConditionStr = 'AND `created_datetime` >= CURDATE() - INTERVAL 365 DAY';
}

//Build query       
$sql = "SELECT id, title, task_id, created_date, case_id, case_number, assigned_person, SLA,task_status,contact_email, assigned_to_cellphone, type, is_read, status FROM casemanager.notifications_view";
$result = mysqli_query($link, $sql);

?>
<head>

<title>CaseManager - Notification Events</title>

    <?php include 'layouts/head.php'; ?>
    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/datetime/1.2.0/css/dataTables.dateTime.min.css" rel="stylesheet" type="text/css" />

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
                                    <h4 class="mb-sm-0 font-size-18">Notification Events</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                            <li class="breadcrumb-item active">Notification Events</li>
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
                                <form class="needs-validation" novalidate method="get" action="rpt-notification-events.php">
                                    <div class="row mb-2">
                                        <div class="col-4">
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="text" class="form-control" id="min" name="min" value="<?php echo $min; ?>" placeholder="Date From">
                                                </div>
                                                <div class="col-6">
                                                    <input type="text" class="form-control" id="max" name="max" value="<?php echo $max; ?>" placeholder="Date To">
                                                </div> 
                                            </div>    
                                        </div>
                                        <div class="col-4">
                                            <button id="filter_btn" type="submit" class="btn btn-dark btn-md w-50" name="filter_btn">Filter</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                <table id="datatable-buttons" class="table table-bordered dt-responsive mytable nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID </th>
                                            <th>Title </th>
                                            <th>Task ID </th>
                                            <th>Created Date </th>
                                            <th>Case ID </th>
                                            <th>Case Number </th>
                                            <th>Assigned Person </th>
                                            <th>SLA </th>
                                            <th>Task Status </th>
                                            <th>Contact Email</th>
                                            <th>Assigned to Cellphone </th>
                                            <th>Type </th>
                                            <th>Is Read </th>
                                            <th>Status </th>
                                           
                                            
                                        </tr>
                                    </thead>


                                    <tbody>
                                                 <?php

                                while ($res = mysqli_fetch_array($result)) {


?>
                                       <tr>
                                            <td><?php echo($res['id']); ?></td>
                                            <td><?php echo($res['title']); ?></td>
                                            <td><?php echo($res['task_id']); ?></td>
                                            <td><?php echo($res['created_date']); ?></td>
                                            <td><?php echo($res['case_id']); ?></td>
                                            <td><?php echo($res['case_number']); ?></td>
                                            <td><?php echo($res['assigned_person']); ?></td>
                                            <td><?php echo($res['SLA']); ?></td>
                                            <td><?php echo($res['task_status']); ?></td>
                                            <td><?php echo($res['contact_email']); ?></td>
                                            <td><?php echo($res['assigned_to_cellphone']); ?></td>
                                            <td><?php echo($res['type']); ?></td>
                                            <td><?php echo($res['is_read']); ?></td>
                                            <td><?php echo($res['status']); ?></td>
                                            
                                        </tr>
                                        <?php
}

?>
                                        
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                        <!-- end cardaa -->
                    </div> <!-- end col -->
                </div> <!-- end row -->



            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include 'layouts/footer.php'; ?>
    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include 'layouts/vendor-scripts.php'; ?>
<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
<script src="https://cdn.datatables.net/datetime/1.2.0/js/dataTables.dateTime.min.js"></script>

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
<script src="assets/js/app.js"></script>

<script>
    //var minDate, maxDate;
    // Custom filtering function which will search data in column four between two values
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var min = minDate.val();
            var max = maxDate.val();
            var date = new Date(data[3]);    
            if((min === null && max === null) || (min === null && date <= max) || (min <= date && max === null) || (min <= date && date <= max)) 
                return true;
            return false;
        }
    );
    $(document).ready(function() {
        // Create date inputs
        minDate = new DateTime($('#min'));
        maxDate = new DateTime($('#max'));
    
        // DataTables initialisation
        $('#datatable-buttons').DataTable();

   
        //Buttons examples
        var table_order_history = $('#datatable-buttons').DataTable({
            destroy: true,
            lengthChange: true,
            "pageLength": 100,
            buttons: ['excel','pdf','colvis']
        });

        table_order_history.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');

        $(".dataTables_length select").addClass('form-select form-select-sm');


        // Refilter the table
        //$('#min, #max').on('change', function () {
            //table_order_history.draw();
        //});
    });
</script>
</body>

</html>