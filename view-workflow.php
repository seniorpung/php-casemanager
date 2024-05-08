<?php
// Include config file
require_once "layouts/config.php";
include 'layouts/head-main.php';
include 'layouts/session.php';

    global $link;
    include 'class.crud.php';

    $created_by         =   $_SESSION["id"];
    $created_datetime   =   date('Y-m-d H:i:s');

    $crudObj = new CRUD();
    $crudObj->mysqli = $link;

    $case_type_definition   =   $crudObj->FindRow('case_type_definition', array(), array('case_type_id='.$_GET["id"]));
    $task_configuration     =   $crudObj->FindAll('task_configuration', array(), array('case_type_id='.$_GET["id"]), 0, 0, array(array('id', 'ASC')));

    ?><head>
        <title>Case Manager - View Workflow</title>
        <?php include 'layouts/head.php'; ?>
        <link rel="stylesheet" type="text/css" href="assets/css/style.css">
        <!-- Responsive datatable examples -->
        <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="assets/flowchart/jquery.flowchart.css?v=1.2.3" type="text/css">
        <?php include 'layouts/head-style.php'; ?>
        <style>
            .flowchart-example-container {
                width: 100%;
                height: 400px;
                background: white;
                border: 1px solid #CCC;
                margin-bottom: 10px;
            }
            #flowchart_data{
                display:none;
                width: 100%;
                height: 400px;
                background: white;
                border: 1px solid #CCC;
                margin-bottom: 10px;
            }
        </style>
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
                                    <h4 class="mb-sm-0 font-size-18">Workflow</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Case Manager</a></li>
                                            <li class="breadcrumb-item active">View Workflow</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php  include 'layouts/alert-messages.php'; ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-sm-0">View Task Workflow</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label for="agency" class="form-label">Name</label>
                                        <input class="form-control" type="text" name="name" id="name" placeholder="Title" value="<?php echo $case_type_definition['case_name']; ?>" readonly/>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="begin_date" class="form-label">Begin Date</label>
                                        <input class="form-control" type="date" name="begin_date" id="begin_date"
                                            placeholder="Title" value="<?php echo $case_type_definition['effective_begin_date']; ?>" readonly/>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input class="form-control" type="date" name="end_date" id="end_date"
                                            placeholder="Title" value="<?php echo $case_type_definition['effective_end_date']; ?>" readonly/>
                                    </div>
                                </div>
                                <label class="form-label mt-4">Task Work Flow</label>
                                <div id="chart_container">
                                    <div class="flowchart-example-container" id="flowchartworkspace"></div>
                                </div>

                                <div><textarea id="flowchart_data" name="flow_content"><?php echo $case_type_definition['flow_content']; ?></textarea></div>
                                <div id="task-and-decision-work-flow"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Page-content -->
                <?php include 'layouts/footer.php'; ?>
            </div>
            <!-- end main content-->
        </div>
        <!-- END layout-wrapper -->

        <div class="modal" id="AddTaskModal" tabindex="-1" role="dialog" aria-labelledby="AddTaskModal" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Task Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12 mb-4">
                                <label for="agency" class="form-label">Task Name</label>
                                <input class="form-control" type="hidden" name="is_decision_task" id="is_decision_task" value="0" />
                                <input class="form-control" type="text" name="task_name" id="task_name" placeholder="Enter Task Name" />
                            </div>
                            <div class="col-sm-6 mb-4">
                                <label for="agency" class="form-label">Assign To</label>
                                <select class="form-control" name="task_assigned_to" id="task_assigned_to">
                                    <option value="">Select User</option><?php
                                if(isset($assignUsers) && is_array($assignUsers) && count($assignUsers)>0){
                                    foreach($assignUsers as $usr){
                                        $uname      =   '';
                                        $uname_arr  =   array();
                                        if(!empty($usr['fname']))   $uname_arr[] = $usr['fname'];
                                        if(!empty($usr['mname']))   $uname_arr[] = $usr['mname'];
                                        if(!empty($usr['lname']))   $uname_arr[] = $usr['lname'];
                                        if(isset($uname_arr) && is_array($uname_arr) && count($uname_arr)>0)
                                            $uname = implode(' ', $uname_arr);
                                        ?><option value="<?php echo $usr['id']; ?>"><?php echo $uname; ?></option><?php
                                    }
                                }
                                ?></select>
                            </div>
                            <div class="col-sm-6 mb-4">
                                <label for="agency" class="form-label">Default SLA</label>
                                <input class="form-control" type="text" name="defaultSLA" id="defaultSLA" placeholder="Enter Default SLA" />
                            </div>
                            <div class="col-sm-12 mb-4">
                                <label for="agency" class="form-label">Task Descriptions</label>
                                <input class="form-control" type="text" name="task_descriptions" id="task_descriptions" placeholder="Enter Task Descriptions" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="task_operator_id" id="task_operator_id" />
                        <input type="button" class="btn btn-success" name="submitFormBtn" value="Save Task" onclick="setTaskDetailsData();"/>
                    </div>
                </div>
            </div>  
        </div>

        <div class="modal" id="AddDecisionTaskModal" tabindex="-1" role="dialog" aria-labelledby="AddDecisionTaskModal" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Decision Task Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label for="agency" class="form-label">Decision Task Name</label>
                                <input class="form-control" type="text" name="decision_task_name" id="decision_task_name" placeholder="Enter Decision Task Name" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="decision_task_operator_id" id="decision_task_operator_id" />
                        <input type="button" class="btn btn-success" name="submitFormBtn" value="Save Decision Task" onclick="setDecisionTaskDetailsData();"/>
                    </div>
                </div>
            </div>  
        </div>

        <!-- Right Sidebar -->
        <?php include 'layouts/right-sidebar.php'; ?>
        <!-- /Right-bar -->

        <!-- JAVASCRIPT -->
        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- App js -->
        <script src="assets/js/app.js"></script>


        <link rel="stylesheet" type="text/css"
            href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="assets/flowchart/jquery.flowchart.js?v=1.2.3"></script>
        <script type="text/javascript">
            /* global $ */
            var operatorI = 0;
            var $flowchart = $('#flowchartworkspace');
            var $container = $flowchart.parent();
            $(document).ready(function () {
                // Apply the plugin on a standard, empty div...
                $flowchart.flowchart({
                    data: defaultFlowchartData,
                    defaultSelectedLinkColor: '#000055',
                    grid: 10,
                    multipleLinksOnInput: true,
                    multipleLinksOnOutput: true
                });
                function getOperatorData($element) {
                    var nbInputs = parseInt($element.data('nb-inputs'), 10);
                    var nbOutputs = parseInt($element.data('nb-outputs'), 10);
                    var data = {
                        properties: {
                            title: $element.text(),
                            inputs: {},
                            outputs: {}
                        }
                    };
                    var i = 0;
                    for (i = 0; i < nbInputs; i++) {
                        data.properties.inputs['input_' + i] = {
                            label: 'Input ' + (i + 1)
                        };
                    }
                    for (i = 0; i < nbOutputs; i++) {
                        data.properties.outputs['output_' + i] = {
                            label: 'Output ' + (i + 1)
                        };
                    }
                    return data;
                }

                //-----------------------------------------
                //--- operator and link properties
                //--- start
                var $operatorProperties = $('#operator_properties');
                $operatorProperties.hide();
                var $linkProperties = $('#link_properties');
                $linkProperties.hide();
                var $operatorTitle = $('#operator_title');
                var $linkColor = $('#link_color');

                $flowchart.flowchart({
                    onOperatorSelect: function (operatorId) {
                        $operatorProperties.show();
                        $operatorTitle.val($flowchart.flowchart('getOperatorTitle', operatorId));
                        return true;
                    },
                    onOperatorUnselect: function () {
                        $operatorProperties.hide();
                        return true;
                    },
                    onLinkSelect: function (linkId) {
                        $linkProperties.show();
                        $linkColor.val($flowchart.flowchart('getLinkMainColor', linkId));
                        return true;
                    },
                    onLinkUnselect: function () {
                        $linkProperties.hide();
                        return true;
                    }
                });

                $operatorTitle.keyup(function () {
                    var selectedOperatorId = $flowchart.flowchart('getSelectedOperatorId');
                    if (selectedOperatorId != null) {
                        $flowchart.flowchart('setOperatorTitle', selectedOperatorId, $operatorTitle.val());
                    }
                });

                $linkColor.change(function () {
                    var selectedLinkId = $flowchart.flowchart('getSelectedLinkId');
                    if (selectedLinkId != null) {
                        $flowchart.flowchart('setLinkMainColor', selectedLinkId, $linkColor.val());
                    }
                });
                //--- end
                //--- operator and link properties
                //-----------------------------------------

                //-----------------------------------------
                //--- delete operator / link button
                //--- start
                $flowchart.parent().siblings('.delete_selected_button').click(function () {
                    $flowchart.flowchart('deleteSelected');
                    $('#task-operator-id-'+operatorId).remove();
                    Flow2Text();
                });
                //--- end
                //--- delete operator / link button
                //-----------------------------------------

                //-----------------------------------------
                //--- create operator button
                //--- start

                $flowchart.parent().siblings('.end_create_operator').click(function () {
                    operatorI++;
                    var operatorId = 'created_operator_' + operatorI;
                    var operatorData = {
                        top: ($flowchart.height()/2)-30,
                        left: ($flowchart.width()/2)-100+(operatorI*10),
                        properties: {
                            title: '',
                            dataindex: operatorId,
                            dataclass: 'bg-warning text-white',
                            borderclass: 'border-warning bg-white',
                            inputs: {
                                input_1: {
                                    label: 'END TASK',
                                }
                            }
                        }
                    };            
                    $flowchart.flowchart('createOperator', operatorId, operatorData);

                    htmldata  = '<div id="task-operator-id-'+operatorId+'">';
                        htmldata += '<input type="hidden" name="endtaskdata['+operatorId+']" value="end"/>';
                    htmldata += '</div>';
                    $('#task-and-decision-work-flow').append(htmldata); 
                });
                //--- end
                //--- create operator button
                //-----------------------------------------

                //-----------------------------------------
                //--- draggable operators
                //--- start
                //var operatorId = 0;
                var $draggableOperators = $('.draggable_operator');
                $draggableOperators.draggable({
                    cursor: "move",
                    opacity: 0.7,

                    // helper: 'clone',
                    appendTo: 'body',
                    zIndex: 1000,

                    helper: function (e) {
                        var $this = $(this);
                        var data = getOperatorData($this);
                        return $flowchart.flowchart('getOperatorElement', data);
                    },
                    stop: function (e, ui) {
                        var $this = $(this);
                        var elOffset = ui.offset;
                        var containerOffset = $container.offset();
                        if (elOffset.left > containerOffset.left &&
                            elOffset.top > containerOffset.top &&
                            elOffset.left < containerOffset.left + $container.width() &&
                            elOffset.top < containerOffset.top + $container.height()) {

                            var flowchartOffset = $flowchart.offset();

                            var relativeLeft = elOffset.left - flowchartOffset.left;
                            var relativeTop = elOffset.top - flowchartOffset.top;

                            var positionRatio = $flowchart.flowchart('getPositionRatio');
                            relativeLeft /= positionRatio;
                            relativeTop /= positionRatio;

                            var data = getOperatorData($this);
                            data.left = relativeLeft;
                            data.top = relativeTop;

                            $flowchart.flowchart('addOperator', data);
                        }
                    }
                });
                //--- end
                //--- draggable operators
                //-----------------------------------------

                //-----------------------------------------
                //--- save and load
                //--- start
                function Flow2Text() {
                    var data = $flowchart.flowchart('getData');
                    $('#flowchart_data').val(JSON.stringify(data, null, 2));
                }
                $('#get_data').click(Flow2Text);

                function Text2Flow() {
                    var data = JSON.parse($('#flowchart_data').val());
                    $flowchart.flowchart('setData', data);
                }
                $('#set_data').click(Text2Flow);

                /*global localStorage*/
                function SaveToLocalStorage() {
                    if (typeof localStorage !== 'object') {
                        alert('local storage not available');
                        return;
                    }
                    Flow2Text();
                    localStorage.setItem("stgLocalFlowChart", $('#flowchart_data').val());
                }
                $('#save_local').click(SaveToLocalStorage);

                function LoadFromLocalStorage() {
                    if (typeof localStorage !== 'object') {
                        alert('local storage not available');
                        return;
                    }
                    var s = localStorage.getItem("stgLocalFlowChart");
                    if (s != null) {
                        $('#flowchart_data').val(s);
                        Text2Flow();
                    }
                    else {
                        alert('local storage empty');
                    }
                }
                $('#load_local').click(LoadFromLocalStorage);
                //--- end
                //--- save and load
                //-----------------------------------------

                $('#chart_container').click(Flow2Text);
                $('#chart_container').mouseover(Flow2Text);

                Text2Flow();
            });
            var defaultFlowchartData = {
                operators: {
                    operator1: {
                        top: 20,
                        left: 20,
                        properties: {
                            title: 'Start Point',
                            dataindex: 'StartPoint',
                            dataclass: 'bg-success text-white',
                            borderclass: 'border-success bg-white',
                            inputs: {},
                            outputs: {
                                output_1: {
                                    label: 'Start Workflow',
                                }
                            }
                        }
                    },
                },
                links: {
                }
            };
            if(false) console.log('remove lint unused warning', defaultFlowchartData);

            function OpenCreateOperator(){
                operatorI++;
                $('#AddTaskModal').modal('show');
                $('#task_name').val('');
                $('#task_assigned_to').val('');
                $('#defaultSLA').val('');
                $('#task_descriptions').val(''); 

                $('#task_operator_id').val(operatorI);
                $('#is_decision_task').val('0');   
            }
            function OpenDecisionCreateOperator(){
                operatorI++;
                $('#AddTaskModal').modal('show');
                $('#task_name').val('');
                $('#task_assigned_to').val('');
                $('#defaultSLA').val('');
                $('#task_descriptions').val(''); 

                $('#task_operator_id').val(operatorI);
                $('#is_decision_task').val('1');    
            }
            function OpenSubDecisionCreateOperator(){
                operatorI++;
                $('#AddDecisionTaskModal').modal('show');
                $('#decision_task_name').val('');
                $('#decision_task_operator_id').val(operatorI);   
            }
            function setTaskDetailsData(){
                var htmldata = '';
                var task_operator_id    =   $('#task_operator_id').val();
                var is_decision_task    =   $('#is_decision_task').val();
                var task_name           =   $('#task_name').val();
                var task_assigned_to    =   $('#task_assigned_to').val();
                var defaultSLA          =   $('#defaultSLA').val();
                var task_descriptions   =   $('#task_descriptions').val();        
                htmldata  = '<div id="task-operator-id-'+task_operator_id+'">';
                    htmldata += '<input type="hidden" name="taskdata['+task_operator_id+'][is_decision_task]" value="'+is_decision_task+'"/>';
                    htmldata += '<input type="hidden" name="taskdata['+task_operator_id+'][task_name]" value="'+task_name+'"/>';
                    htmldata += '<input type="hidden" name="taskdata['+task_operator_id+'][task_assigned_to]" value="'+task_assigned_to+'"/>';
                    htmldata += '<input type="hidden" name="taskdata['+task_operator_id+'][defaultSLA]" value="'+defaultSLA+'"/>';
                    htmldata += '<input type="hidden" name="taskdata['+task_operator_id+'][task_descriptions]" value="'+task_descriptions+'"/>';
                htmldata += '</div>';
                
                $('#task-and-decision-work-flow').append(htmldata); 
                $('#AddTaskModal').modal('hide');

                if(is_decision_task == '0')  {
                    var dataclass = 'bg-primary text-white';
                    var borderclass = 'border-primary bg-white';
                }
                else{
                    var dataclass = 'bg-info text-white';
                    var borderclass = 'border-info bg-white';
                }

                var operatorId = 'created_operator_' + operatorI;
                var operatorData = {
                    top: ($flowchart.height()/2)-30,
                    left: ($flowchart.width()/2)-100+(operatorI*10),
                    properties: {
                        title: task_name,
                        dataindex: operatorId,
                        dataclass: dataclass,
                        borderclass: borderclass,
                        inputs: {
                            input_1: {
                                label: 'IN',
                            }
                        },
                        outputs: {
                            output_1: {
                                label: 'OUT',
                            }
                        }
                    }
                };        
                $flowchart.flowchart('createOperator', operatorId, operatorData);
                
            }
            function setDecisionTaskDetailsData(){
                var htmldata = '';        
                var decision_task_operator_id   =   $('#decision_task_operator_id').val();
                var decision_task_name          =   $('#decision_task_name').val();
                htmldata  = '<div id="task-operator-id-'+decision_task_operator_id+'">';
                    htmldata += '<input type="hidden" name="decisiontaskdata['+decision_task_operator_id+'][decision_task_name]" value="'+decision_task_name+'"/>';
                htmldata += '</div>';
                $('#task-and-decision-work-flow').append(htmldata); 
                $('#AddDecisionTaskModal').modal('hide'); 
                
                var operatorId = 'created_operator_' + operatorI;
                var operatorData = {
                    top: ($flowchart.height()/2)-30,
                    left: ($flowchart.width()/2)-100+(operatorI*10),
                    properties: {
                        title: decision_task_name,
                        dataindex: operatorId,
                        dataclass: 'bg-secondary text-white',
                        borderclass: 'border-secondary bg-white',
                        inputs: {
                            input_1: {
                                label: 'IN',
                            }
                        },
                        outputs: {
                            output_1: {
                                label: 'OUT',
                            }
                        }
                    }
                };            
                $flowchart.flowchart('createOperator', operatorId, operatorData);
            }
        </script>
        <!-- Keep Nav Menu Open and Highlight link -->    
    <script>
        $('#mm-admin').attr('aria-expanded', true);
        $('#mm-admin').addClass('mm-show')
        $('#mm-admin').css('height', 'auto')
        $('#mm-admin').parent().addClass('mm-active');
        $($('#mm-admin').children()[5]).children().eq(0).css('color', '#1c84ee');
    </script>
    </body>
</html>