<?php
    // Include config file
    require_once "layouts/config.php";
    include 'layouts/head-main.php';
    include 'layouts/session.php'; 

    global $link;
    include 'class.crud.php';
    ?>
    <head>
        <title>Case Manager - Scan Files</title>
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
        <!-- start page title -->
        <h4 class="mb-sm-0 font-size-18">Document Scan</h4>
        
        <!-- end page title -->
        <link
      rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
    />
    <link
      rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"
    />
    <meta charset="utf-8" />
    <style>
        table,
        textarea {
        width: 100%;
        height: 200px;
        padding: 3px;
        }

        a {
        cursor: pointer;
        }

        .githubIcon {
        margin-right: 10px;
        color: #fff;
        font-size: 32px;
        }

        .jspmStatus {
        color: #fff;
        margin-left: 10px;
        }

        .round {
        display: inline-block;
        height: 40px;
        width: 40px;
        line-height: 40px;
        -moz-border-radius: 20px;
        border-radius: 20px;
        background-color: #cd2122;
        color: #fff;
        text-align: center;
        }

        .content {
        padding-top: 80px;
        padding-bottom: 20px;
        }

        .fileFormats {
        background-color: #ececec;
        font-weight: bold;
        color: #666;
        }

        .topMost {
        z-index: 1080;
        }

        .white {
        color: #fff;
        }

        .iconDemo {
        color: #cd2122;
        }

        .tab-content,
        .nav-tabs {
            border-bottom: 2px solid #cd2122;
        }

            .nav-tabs .nav-item.show .nav-link, 
            .nav-tabs .nav-link.active {
                background: #cd2122;
                border-bottom: 2px solid #cd2122;
                color: #fff;
            }

        .nav-link {
            color: #cd2122;
        }

        .terminal {
            background-color: #171a1d;
            color: #fff;
            font-family: 'Courier New', Courier, monospace;
        }

        .text-monospace {
            font-family: 'Courier New', Courier, monospace;
        }

        .myDropDown, .myDropDown:hover{
          display: inline-block;
          width: 100%;
          height: calc(1.5em + .75rem + 2px);
          padding: .375rem 1.75rem .375rem .75rem;
          font-size: 1rem;
          font-weight: 400;
          line-height: 1.5;
          color: #495057;
          vertical-align: middle;
          background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e") right .75rem center/8px 10px no-repeat;
          border: 1px solid #ced4da;
          border-radius: .25rem;
          -webkit-appearance: none;
          -moz-appearance: none;
          appearance: none;
          text-decoration: none;
        }
        .dropdown-menu {
          width: 100%;
        }
            
    </style>
    </head>
    <?php include 'layouts/body.php'; ?>
    <!-- Begin page -->
        <div id="layout-wrapper">
            <?php include 'layouts/menu.php'; ?>
            <!-- start page title -->
            <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Document Scan</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                            <li class="breadcrumb-item active">Document Scan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <body>
      <div id="root"></div>

      <file-formats-modal id="files-requeriments"></file-formats-modal>
      <script src="modules/jsPrint/ie11.js"></script>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"></script>
      <script src="/jquery-3.2.1.slim.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>

      <!--JSPrintManager scripts-->
      <script src="modules/jsPrint/JSPrintManager.js"></script>
      <!--<script src="https://cdn.jsdelivr.net/gh/gildas-lormeau/zip.js/dist/zip-full.min.js"></script>-->
      <!--end JSPrintManager scripts-->
      <!--Encoding tables scripts https://github.com/SheetJS/js-codepage  -->
      <script src="https://cdn.jsdelivr.net/gh/SheetJS/js-codepage/dist/cptable.js"></script>
      <script src="https://cdn.jsdelivr.net/gh/SheetJS/js-codepage/dist/cputils.js"></script>
      <!--end Encoding tables scripts-->

      <script src="https://unpkg.com/react@16/umd/react.development.js"></script>
      <script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js"></script>
      <script src="https://unpkg.com/babel-standalone@6.15.0/babel.min.js"></script>

      <script type="text/babel" src="modules/jsPrint/ScanningSample.js">
      </script>
      
        
           
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

        <script>
            function assign(val)
            {
                $('#assignmodal').modal('toggle');
                $('#assignmodal').modal('show');
                $('.confirmation_msg').html("Are you sure you want to pull the task and assign to yourself?");
                $(".id").val(val);
            }
        </script>
    </body>
</html>