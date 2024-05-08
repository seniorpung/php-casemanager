<?php
    // Code to Save and Retrieve data
    

?>
<div class="modal" id="employee_training_registration" tabindex="-1" role="dialog" aria-labelledby="employee_training_registration" aria-hidden="true">
                                                    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Employee Training Registration- By Sovratec</h4>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="post">
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                <input type="hidden" name="task_id" id="task_id" >
                                                                    <div class="col-sm-6">
                                                                        <label for="agency" class="form-label">Employee First Name</label>
                                                                        <input class="form-control" type="text" name="employee_fname" id="employee_fname"/>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="agency" class="form-label">Employee Last Name:</label>
                                                                        <input class="form-control" type="text" name="employee_lname" id="employee_lname"/>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <label for="agency" class="form-label">Employee Email:</label>
                                                                        <input class="form-control" type="text" name="employee_email" id="employee_email" />
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="agency" class="form-label">Training Company Name:</label>
                                                                        <input class="form-control" type="text" name="training_company_name" id="training_company_name"/>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <label for="agency" class="form-label">Training Start Date:</label>
                                                                        <input class="form-control" type="date" name="training_start_date" id="training_start_date"/>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="agency" class="form-label">Training Cost:</label>
                                                                        <input class="form-control" type="number" step="any" name="training_cost" id="training_cost" />
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <label for="agency" class="form-label">Training Class Name:</label>
                                                                        <input class="form-control" type="text" name="training_class_name" id="training_class_name"/>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-12">
                                                                        <label for="agency" class="form-label">Detail Instructions:</label>
                                                                            <textarea class="form-control" id="detail_instructions" name="detail_instructions" rows="4"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-12 mt-3">
                                                                            <input type="button"  style="padding:5px 50px;margin-right:20px;" id="savebtn2" class="btn btn-primary" name="submitEngForm" value="Save/Update" onclick="saveEmpform()" />
                                                                            <input type="button"  style="padding:5px 50px;" class="btn btn-primary" name="cancelEngForm" value="Cancel" onclick="cancelEmpform()"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            </form>

                                                            <div class="modal-footer">
                                                                <!-- <input type="button" class="btn btn-success" name="submitEngForm" value="Save"/> -->
                                                            </div>
                                                        </div>
                                                    </div>  
                                                </div>



<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Plugins js-->
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>
<script>
           
            
          

            function cancelEmpform()
            {
               
             
                $('#employee_training_registration').modal('hide');
            }
           function saveEmpform()
            {
        const task_id = $("#task_id").val();
        const employee_fname = $("#employee_fname").val();
        const employee_lname = $("#employee_lname").val();
        const employee_email = $("#employee_email").val();
        const training_company_name = $("#training_company_name").val();
        const training_start_date = $("#training_start_date").val();
        const training_cost = $("#training_cost").val();
        const training_class_name = $("#training_class_name").val();
        const detail_instructions = $("#detail_instructions").val();

                $.ajax({ 
        url: "action.php", 
        method: "POST", 
        data: {
            task_id: task_id,
            employee_fname: employee_fname,
            employee_lname: employee_lname,
            employee_email: employee_email,
            training_company_name: training_company_name,
            training_start_date: training_start_date,
            training_cost: training_cost,
            training_class_name: training_class_name,
            detail_instructions: detail_instructions,
        }, 
        success:function(data){ 
            $('#employee_training_registration').modal('hide');
            document.getElementById("employee_fname").value="";
            document.getElementById("employee_lname").value="";
            document.getElementById("employee_email").value="";
            document.getElementById("training_company_name").value="";
            document.getElementById("training_start_date").value="";
            document.getElementById("training_cost").value="";
            document.getElementById("training_class_name").value="";
            document.getElementById("detail_instructions").value="";
        } 
    }) 

}
        
        </script>
</body>

</html>