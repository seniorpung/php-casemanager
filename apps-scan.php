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

            <div style="text-align:center">
                <h1>Scan Docs and Images</h1>
                <hr />
                <label>Scanner:</label>
                <select id="scannerName"></select>
                <label>Resolution (DPI):</label>
                <input type="text" id="resolution" value="200" />
                <label>Pixel Mode:</label>
                <select id="pixelMode">
                    <option>Grayscale</option>
                    <option selected>Color</option>
                </select>
                <label>Image Format:</label>
                <select id="imageFormat">
                    <option selected>JPG</option>
                    <option>PNG</option>
                </select>
        
                <hr />
                <div>
                    <button onclick="doScanning();">Scan Now...</button>
                </div>
                <br />
        
                <img id="scanOutput" />
        
            </div>
        
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"></script>
            <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
            
        
            <!--IMPORTANT: BE SURE YOU HONOR THIS JS LOAD ORDER-->
            
            <script src="modules/jsPrint/JSPrintManager.js"></script>
            
            
            
        
            <script>
        
                var scannerDevices = null;
                var _this = this;
        
                //JSPrintManager WebSocket settings
                JSPM.JSPrintManager.auto_reconnect = true;
                JSPM.JSPrintManager.start();
                JSPM.JSPrintManager.WS.onStatusChanged = function () {
                    if (jspmWSStatus()) {
                        //get scanners
                        JSPM.JSPrintManager.getScanners().then(function (scannersList) {
                            scannerDevices = scannersList;
                            var options = '';
                            for (var i = 0; i < scannerDevices.length; i++) {
                                options += '<option>' + scannerDevices[i] + '</option>';
                            }
                            $('#scannerName').html(options);
                        });
                    }
                };
        
                //Check JSPM WebSocket status
                function jspmWSStatus() {
                    if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Open)
                        return true;
                    else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Closed) {
                        console.warn('JSPrintManager (JSPM) is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm');
                        return false;
                    }
                    else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Blocked) {
                        alert('JSPM has blocked this website!');
                        return false;
                    }
                }
        
                //Do scanning...
                function doScanning() {
                    if (jspmWSStatus()) {
        
                        //create ClientScanJob
                        var csj = new JSPM.ClientScanJob();
                        //scanning settings
                        csj.scannerName = $('#scannerName').val();
                        csj.pixelMode = JSPM.PixelMode[$('#pixelMode').val()];
                        csj.resolution = parseInt($('#resolution').val());
                        csj.imageFormat = JSPM.ScannerImageFormatOutput[$('#imageFormat').val()];
        
                        let _this = this;
                        //get output image
                        csj.onUpdate = (data, last) => {
                            if (!(data instanceof Blob)) {
                                console.info(data);
                                return;
                            }
                            var imgBlob = new Blob([data]);
        
                            if (imgBlob.size == 0) return;
                            
                            var data_type = 'image/jpg';
                            if (csj.imageFormat == JSPM.ScannerImageFormatOutput.PNG) data_type = 'image/png';
                            //create html image obj from scan output
                            var img = URL.createObjectURL(imgBlob, { type: data_type });
                            //scale original image to be screen size friendly
                            var imgScale = { width: Math.round(96.0 / csj.resolution * 100.0) + "%", height: 'auto' };
                            $('#scanOutput').css(imgScale);
                            $('#scanOutput').attr("src", img);
                        }
        
                        csj.onError = function (data, is_critical) {
                            console.error(data);
                        };
        
                        //Send scan job to scanner!
                        csj.sendToClient().then(data => console.info(data));
        
                    }
                }
        
        
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