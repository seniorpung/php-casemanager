<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php'; 
include 'layouts/session.php'; 

global $link;
include 'class.crud.php';

$crudObj = new CRUD('email_templates', 'email_template_id');
$crudObj->mysqli = $link;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $template_name = $_POST["template_name"];
    $template_description = $_POST["template_description"];
    $email_subject = $_POST["email_subject"];
    $email_from = $_POST["email_from"];
    $email_to = $_POST["email_to"];
   $email_content = $_POST["email_content"];
   $created_datetime = date('Y-m-d H:i:s');
   $updated_datetime = date('Y-m-d H:i:s');

    // Prepare an insert statement
        $sql = "INSERT INTO email_templates (template_name, template_description, email_subject, email_from, email_to, email_content, created_by, created_datetime, updated_by, updated_datetime) VALUES ('$template_name', '$template_description', '$email_subject', '$email_from', '$email_to', '$email_content', '1', '$created_datetime', '1', '$updated_datetime')";


        if(mysqli_query($link, $sql)){

    header("location: manage-email-template.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }


}
?>

<?php

//select query statement
$sq = "SELECT * FROM tags";
$result = mysqli_query($link, $sq);

?>


<head>
    <title>Case Manager - Add Email Template</title>

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
                            <h4 class="mb-sm-0 font-size-18">New Email Template</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                    <li class="breadcrumb-item active">New Email Template</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row g-0">
                            <div class="col-xxl-12 col-lg-12 col-md-12">
                                <div class="card">
                                <div class="card-body">
                                
                                              <form class="needs-validation custom-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                                 <div class="col-sm-3" style="float: right;">
                                                <table class="table table-responsive table-bordered">
                                                      <tr>
                                                        <th>Tag Name</th>
                                                        <th>Tag Value</th>
                                                      </tr>

                                                       <?php
                                        while ($res = mysqli_fetch_array($result)) 

                                        {
                                            ?>
                                                      <tr>
                                                        <td><?php echo ($res['tag_name']); ?></td>
                                                        <td><?php echo ($res['tag_value']); ?></td>
                                                      </tr>

                                                            <?php
                                        }
                                    ?>
                                                      
                                                    </table>
                                                </div>


                                                <div class="row mb-2">
                                                    <label for="template_name" class="col-sm-3 col-form-label">Template Name:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" maxlength="50" id="template_name" placeholder="" name="template_name" value="" required>
                                                </div>

                                            </div>

                                            
                                                <div class="row mb-2">
                                                    <label for="template_description" class="col-sm-3 col-form-label">Template Desc:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" maxlength="250" id="template_description" placeholder="" name="template_description" value="">
                                                </div>
                                            </div>

                                                <div class="row mb-2">
                                                    <label for="email_subject" class="col-sm-3 col-form-label">Email Subject:</label>
                                                    <div class="col-sm-4">
                                                      <input type="text" class="form-control form-control-sm" maxlength="250" id="email_subject" placeholder="" name="email_subject" value="" required>
                                                </div>

                                            </div>
                                             <div class="row mb-2">
                                                    <label for="email_from" class="col-sm-3 col-form-label">From:</label>
                                                  <div class="col-sm-4">
                                                    <input type="text" class="form-control form-control-sm" name="email_from" placeholder="" value="" required>
                                                    </div>
                                                    <label for="email_to" class="col-sm-1 col-form-label">To:</label>
                                                 <div class="col-sm-4">
                                                    <input type="text" class="form-control form-control-sm" name="email_to" placeholder="" value="" required>
                                                    </div>

                                            </div>
                                           <hr>
                                                <div class="row mb-2">
                                                    <div class="col-sm-12 mb-3">
                                                        <textarea id="ckeditor-classic" name="email_content" >
                                                            
                                                        </textarea>
                                                    
                                                    </div>
                                            
                                                
                                                </div>
                                           <div class="mt-2 mb-2">
                                                        <button class="btn btn-primary waves-effect waves-light" id="save" name="save" type="submit">Save Email Template</button>
                                                        <button type="button" class="btn btn-danger"> <a href="manage-email-template.php"> Cancel</a></button>
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                   
                                <!-- end auth full page content -->
                            </div>
                        </div>
                        <!-- end row -->  
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
<!-- ckeditor -->
<script>
 CKEDITOR.replace( 'ckeditor-classic', {
 });
</script>
<script src="assets/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js"></script>
<!-- init js -->
<script src="assets/js/pages/form-editor.init.js"></script>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Plugins js-->
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>
<!-- Keep Nav Menu Open and Highlight link -->    
<script>
        $('#mm-admin').attr('aria-expanded', true);
        $('#mm-admin').addClass('mm-show')
        $('#mm-admin').css('height', 'auto')
        $('#mm-admin').parent().addClass('mm-active');
        $($('#mm-admin').children()[9]).children().eq(0).css('color', '#1c84ee');
    </script>
</body>

</html>