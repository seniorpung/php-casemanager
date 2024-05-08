<?php 
    require_once "layouts/config.php";
    include 'layouts/session.php';
    include 'layouts/head-main.php'; 
    
    $_SESSION['fx'] = 0;
    global $link;
    include 'class.crud.php';
    $crudObj = new CRUD();
    $crudObj->mysqli = $link;
    
    $created_by         =   $_SESSION["id"];
    $org_id             =   $_SESSION["organization_id"];

    $total_cases        =   $crudObj->FindRecordsCount('cases_view', array('organization_id='.$org_id));
    $total_open_cases   =   $crudObj->FindRecordsCount('cases_view', array('case_status_id in (1,4)', 'organization_id='.$org_id));
    $total_closed_cases =   $crudObj->FindRecordsCount('cases_view', array('case_status_id=\'2\'', 'organization_id='.$org_id));
    $OutofSLA           =   $crudObj->FindRecordsCount('tasks_view', array('datediff(current_time(), created_datetime) > defaultSLA','task_status_id in (1,3)','organization_id='.$org_id));
   

    $crudObj = new CRUD('tasks', 'case_id');
    $crudObj->mysqli = $link;

    $colsData = array();
    $colsData[] = '*';
    $colsData[] = '(SELECT task_name FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS task_name';
    $colsData[] = '(SELECT defaultSLA FROM task_configuration WHERE task_configuration.task_configuration_id=tasks.task_cofiguration_id) AS defaultSLA';
    
    $colsData[] = '(SELECT task_status_name FROM task_status WHERE task_status.task_status_id=tasks.task_cofiguration_id) AS task_status_name';
    $colsData[] = '(SELECT case_number FROM cases_view WHERE cases_view.case_id=tasks.case_id) AS case_number';
    $colsData[] = '(SELECT assigned_datetime FROM tasks_view WHERE tasks_view.task_id=tasks.case_id) AS task_created_datetime';

    //$records = $crudObj->FindAll('tasks_view', $colsData, array('task_status_id=\'1\''), 0, 0, array(array('task_id', 'DESC')));
    $records = $crudObj->FindAll('tasks_view', array(), array('datediff(current_time(), created_datetime) > defaultSLA','task_status_id in (1,3)','organization_id='.$org_id), 0, 0, array(array('task_id', 'DESC')));

    ?><head>
        <title><?php echo $language["Dashboard"]; ?> | Case Manager | Admin Dashboard</title>
        <?php include 'layouts/head.php'; ?>
        <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />    
        <?php include 'layouts/head-style.php'; ?>
    </head><?php 
    include 'layouts/body.php'; 
        ?><div id="layout-wrapper">
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
                                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);"> Case Manager</a></li>
                                            <li class="breadcrumb-item active">Dashboard</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">   
                                        <div class="row align-items-center">
                                            <div class="col-6">
                                                <a href="manage-cases.php" class="stretched-link">
                                                <span class="text-truncate mb-3 d-block"><strong>Total Cases</strong></span>
                                                <h4 class="mb-3"><?php echo $total_cases; ?> 
                                                </h4>
                                                </a>
                                            </div>
        
                                            <div class="col-6">
                                                <div id="mini-chart1" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                                            </div>
                                        </div>
                                        <div class="text-nowrap">
                                            <span class="badge bg-soft-success text-success">+0.5%</span>
                                            <span class="ms-1 text-primary font-size-13">Since last week</span>
                                        </div>
                                    
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
        
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-6">
                                                <a href="#" class="stretched-link">
                                                <span class="text-truncate mb-3 d-block"><strong>Open Cases</strong></span>
                                                <h4 class="mb-3"><?php echo $total_open_cases; ?> 
                                                </h4>
                                            </a>
                                            </div>
                                            <div class="col-6">
                                                <div id="mini-chart2" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                                            </div>
                                        </div>
                                        <div class="text-nowrap">
                                            <span class="badge bg-soft-danger text-danger">-11 Cases</span>
                                            <span class="ms-1 text-primary font-size-13">Since last week</span>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col-->
        
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-6">
                                                <a href="#" class="stretched-link">
                                                <span class="text-truncate mb-3 d-block"><strong>Closed Cases</strong></span>
                                                <h4 class="mb-3"><?php echo $total_closed_cases; ?> 
                                                </h4>
                                            </a>
                                            </div>
                                            <div class="col-6">
                                                <div id="mini-chart3" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                                            </div>
                                        </div>
                                        <div class="text-nowrap">
                                            <span class="badge bg-soft-success text-success">+0.2%</span>
                                            <span class="ms-1 text-primary font-size-13">Since last week</span>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
        
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-6">
                                                <a href="#" class="stretched-link">
                                                <span class="text-truncate mb-3 d-block"><strong>Tasks Out of SLA</strong></span>
                                                <h4 class="mb-3"><?php echo $OutofSLA; ?> 
                                                </h4>
                                            </a>
                                            </div>
                                            <div class="col-6">
                                                <div id="mini-chart4" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                                            </div>
                                        </div>
                                        <div class="text-nowrap">
                                            <span class="badge bg-soft-success text-success">+0.1%</span>
                                            <span class="ms-1 font-size-13 text-primary">Since last week</span>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->    
                        </div><!-- end row-->


                        <div class="row column1">
                            <div class="col-md-6 col-lg-6">
                                <div class="card">
                                    <div class="card-body"><?php
                                        $monthArr = array(
                                            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul',
                                            8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                                        );
                                        $monthArrs = array(
                                            0 => 'Jan', 2 => 'Feb', 2 => 'Mar', 3 => 'Apr', 4 => 'May', 5 => 'Jun', 6 => 'Jul',
                                            7 => 'Aug', 8 => 'Sep', 9 => 'Oct', 10 => 'Nov', 11 => 'Dec',
                                        );

                                        $dataPoints1    =   array();
                                        $dataPoints2    =   array();
                                        $start_date = date("Y-01-01");
                                    $end_date = date("Y-12-t", strtotime($start_date));
                                    $s_date = date("Y-01-01", strtotime('-1 year'));
                                    $e_date = date("Y-12-t", strtotime('-1 year'));
                                        $open_cases     =   $crudObj->fetch_records("SELECT COUNT(1) as ttl, MONTH(case_initial_file_date) AS months FROM cases where (`case_initial_file_date` between  '$start_date' and '$end_date') GROUP BY MONTH(case_initial_file_date)");
                                        $closed_cases   =   $crudObj->fetch_records("SELECT COUNT(1) as ttl, MONTH(case_initial_file_date) AS months FROM cases where (`case_initial_file_date` between  '$s_date' and '$e_date') GROUP BY MONTH(case_initial_file_date)");

                                        
                                        if(isset($open_cases) && is_array($open_cases) && count($open_cases)>0){
                                            foreach($open_cases as $dts){
                                                $dataPoints1[$dts['months']] = $dts['ttl'];
                                            }
                                        }
                                        if(isset($closed_cases) && is_array($closed_cases) && count($closed_cases)>0){
                                            foreach($closed_cases as $dts){
                                                $dataPoints2[$dts['months']] = $dts['ttl'];
                                            }
                                        }

                                        foreach($monthArr as $mk=>$mv){
                                            if(!isset($dataPoints1[$mk]))   $dataPoints1[$mk] =  0;
                                            if(!isset($dataPoints2[$mk]))   $dataPoints2[$mk] =  0; 
                                        }

                                        ksort($dataPoints1);
                                        ksort($dataPoints2);

                                        $i = 0;
                                        foreach($dataPoints1 as $k=>$dts){
                                            $dataPoints1[$i] = $dts;
                                            $i++;
                                        }

                                        $i = 0;
                                        foreach($dataPoints2 as $k=>$dts){
                                            $dataPoints2[$i] = $dts;
                                            $i++;
                                        }

                                        ksort($dataPoints1);
                                        ksort($dataPoints2);                                    
                                        ?><canvas id="bar" height="300" data-colors='["#2ab57d", "#5156be"]'></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-center mb-4">
                                            <h5 class="card-title me-2">Cases</h5>
                                            <div class="ms-auto">
                                                <div>
                                                    <button type="button" class="btn btn-soft-secondary btn-sm">
                                                        ALL
                                                    </button>
                                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                                        1M
                                                    </button>
                                                    <button type="button" class="btn btn-soft-secondary btn-sm">
                                                        6M
                                                    </button>
                                                    <button type="button" class="btn btn-soft-secondary btn-sm active">
                                                        1Y
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                            <?php

                            $start_date = date("Y-01-01");
                            $end_date = date("Y-12-t", strtotime($start_date));
                         
                            $InprogressCounter  =   $crudObj->FindRecordsCount('cases', array("YEAR(`case_initial_file_date`) between '$start_date' and '$end_date'", 'case_status_id=1'));
                            $CompletedCounter  =   $crudObj->FindRecordsCount('cases', array("YEAR(`case_initial_file_date`) between '$start_date' and '$end_date'", 'case_status_id=2'));
                           
                            $CancelledCounter  =  $crudObj->FindRecordsCount('cases', array("YEAR(`case_initial_file_date`) between '$start_date' and '$end_date'", 'case_status_id=3'));
                            $OpenCounter  =  $crudObj->FindRecordsCount('cases', array("YEAR(`case_initial_file_date`) between '$start_date' and '$end_date'", 'case_status_id=4'));
                             
                                        ?>
                                        <div class="row align-items-center">
                                        
                                        <div class="col-sm">
                                                <div id="pie-chart" data-colors='["#2ab57d", "#5156be", "#a8aada", "#ffc107"]' class="apex-charts" dir="ltr"></div>
                                            </div>
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="row column1">
                        <div class="col-md-12 col-lg-12">
                            <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Task Out of SLA</h4>
                                <div class="flex-shrink-0">
                                    <select id="top-ten" class="form-select form-select-sm mb-0 my-n1">
                                        <option value="7">Last 7 Days</option>
                                        <option value="30">Last 30 Days</option>
                                        <option value="90">Last 3 Months</option>
                                        <option value="180">Last 6 Months</option>
                                        <option value="365" selected="All-time">All Time</option>
                                    </select>
                                </div>
                            </div><!-- end card header -->

                            <div id="select_value" class="card-body px-0 pt-2">
                            <div class="table-responsive px-3" data-simplebar style="max-height: 395px;">
                                    <table class="table table-hover table-bordered border-primary table-striped align-middle table-nowrap">
                                        <thead>
                                        <tr class="table-info">
                                            <th></th>
                                            <th>Id</th>
                                            <th>Case Number</th>
                                            <th>Name</th>
                                            <th>Created Date</th>
                                            <th>Assigned To</th>
                                            <th>Assigned Group</th>
                                            <th>Status</th>
                                            <th>SLA</th>  
                                              
                                        </tr>
                                    </thead>
                                        <tbody><?php
                                    if(isset($records) && count($records)>0){
                                        foreach($records as $record){
                                            
                                            ?><tr>
                                                <td><a href="cases-view.php?case_id=<?php echo $record['case_id']; ?>&task_id=<?php echo $record['task_id'];?>">
                                                    <i style="cursor:pointer;" class="mdi mdi-eye text-primary"></i>
                                                </a></td>
                                                <td><?php echo $record['task_id']; ?></td>
                                                <td><?php echo $record['case_number']; ?></td>
                                                <td><?php echo $record['task_name']; ?></td>
                                                <td><?php echo date('m/d/Y h:i A', strtotime($record['created_datetime'])); ?></td>
                                                <td><?php echo $record['assigned_person']; ?></td>
                                                <td><?php echo $record['assigned_group']; ?></td>
                                                <td><?php echo $record['task_status_name']; ?></td>
                                                <td><?php echo $record['defaultSLA']; ?></td>
                                            </tr><?php
                                        } 
                                    }
                                    ?></tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                    </div>

                        <!-- end page title -->   
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

        <!-- Plugins js-->
        <script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

        <!-- apexcharts -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>


        <!-- dashboard init -->
        <script src="assets/js/pages/dashboard.init.js"></script>
        <!-- chartjs init -->
        <script src="assets/js/pages/chartjs.init.js"></script>

        <!-- Chart JS -->
        <script src="assets/libs/chart.js/Chart.bundle.min.js"></script>

        <!-- chartjs init -->
        <script>
            function getChartColorsArray(chartId) {
                var colors = $(chartId).attr('data-colors');
                var colors = JSON.parse(colors);
                return colors.map(function(value){
                    var newValue = value.replace(' ', '');
                    if(newValue.indexOf('--') != -1) {
                        var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                        if(color) return color;
                    } else {
                        return newValue;
                    }
                })
            }
            !function($) {
                "use strict";
                var ChartJs = function() {};
                ChartJs.prototype.respChart = function(selector,type,data, options) {
                    Chart.defaults.global.defaultFontColor="#858d98",
                    Chart.defaults.scale.gridLines.color="rgba(133, 141, 152, 0.1)";
                    // get selector by context
                    var ctx = selector.get(0).getContext("2d");
                    // pointing parent container to make chart js inherit its width
                    var container = $(selector).parent();

                    // enable resizing matter
                    $(window).resize( generateChart );

                    // this function produce the responsive Chart JS
                    function generateChart(){
                        // make chart width fit with its container
                        var ww = selector.attr('width', $(container).width() );
                        switch(type){
                            case 'Line':
                                new Chart(ctx, {type: 'line', data: data, options: options});
                                break;
                            case 'Doughnut':
                                new Chart(ctx, {type: 'doughnut', data: data, options: options});
                                break;
                            case 'Pie':
                                new Chart(ctx, {type: 'pie', data: data, options: options});
                                break;
                            case 'Bar':
                                new Chart(ctx, {type: 'bar', data: data, options: options});
                                break;
                            case 'Radar':
                                new Chart(ctx, {type: 'radar', data: data, options: options});
                                break;
                            case 'PolarArea':
                                new Chart(ctx, {data: data, type: 'polarArea', options: options});
                                break;
                        }
                        // Initiate new chart or Redraw

                    };
                    // run function - render chart at first load
                    generateChart();
                },
                //init
                ChartJs.prototype.init = function() {
                    //barchart
                    var barChartColors = getChartColorsArray("#bar");
                    var barChart = {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [
                            {
                                label: "Current Year Cases",
                                backgroundColor: barChartColors[0],
                                borderColor: barChartColors[0],
                                borderWidth: 1,
                                hoverBackgroundColor: barChartColors[0],
                                hoverBorderColor: barChartColors[0],
                                data: [<?php echo implode(', ', $dataPoints1); ?>]
                            },
                            {
                                label: "Previous Year Cases",
                                backgroundColor: barChartColors[1],
                                borderColor: barChartColors[1],
                                borderWidth: 1,
                                hoverBackgroundColor: barChartColors[1],
                                hoverBorderColor: barChartColors[1],
                                data: [<?php echo implode(', ', $dataPoints2); ?>]
                            }
                        ]
                    };
                    var barOpts = {
                        scales: {
                            xAxes: [{
                                barPercentage: 0.4
                            }]
                        }
                    }
                    this.respChart($("#bar"),'Bar', barChart, barOpts);


                    // pie chart

                    var InprogressCounter=<?php echo $InprogressCounter; ?>;
                    var CompletedCounter=<?php echo $CompletedCounter; ?>;
                    var CancelledCounter=<?php echo $CancelledCounter; ?>; 
                    var OpenCounter=<?php echo $OpenCounter; ?>;

                        var pieColors = getChartColorsArray("#pie-chart");
                        var options = {
                          chart: {
                              height: 240,
                              type: 'pie',
                          }, 
                          series: [InprogressCounter, CompletedCounter, CancelledCounter, OpenCounter],
                          labels: ['In-Progress', 'Completed', 'Cancelled', 'Open'],
                          colors: pieColors,
                          legend: {
                              show: true,
                              position: 'right',
                              horizontalAlign: 'left',
                              verticalAlign: 'right',
                              floating: false,
                              fontSize: '14px',
                              offsetX: 0,
                          },
                          responsive: [{
                              breakpoint: 600,
                              options: {
                                  chart: {
                                      height: 240
                                  },
                                  legend: {
                                      show: false
                                  },
                              }
                          }]

                        }

                        var chart = new ApexCharts(
                          document.querySelector("#pie-chart"),
                          options
                        );

                        chart.render();
                        },
                        $.ChartJs = new ChartJs, $.ChartJs.Constructor = ChartJs
                        }(window.jQuery),

                //initializing
                function($) {
                    "use strict";
                    $.ChartJs.init()
                }(window.jQuery);
        </script>

        <!-- App js -->
        <script src="assets/js/app.js"></script>
        <!-- Session timeout js -->
<script src="assets/libs/@curiosityx/bootstrap-session-timeout/index.js"></script>

<!-- Session timeout init js -->
<script src="assets/js/pages/session-timeout.init.js"></script>
    </body>
</html>