<?php
    // Code to Save and Retrieve data
//    include_once '../layouts/config.php';
   include_once 'travel_approval_form_edit.php';
    

?>
<div
    class="modal"
    id="travel_approval_form"
    tabindex="-1"
    role="dialog"
    aria-labelledby="travel_approval_form"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Client Application Form - By Sovratec</h4>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form method="post" id="saveclientform">
                <div class="modal-body">
                <div class="row">
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Request Date:</label>
                            <input class="form-control" type="date" name="clientAppReqDate" id="clientAppReqDate" value = "<?php echo isset($clientAppData["request_date"]) ? $clientAppData["request_date"] : "" ?>"/>
                        </div>
                </div>    
                <div class="row">
                    <input type="hidden" name="task_id" id="task_id" >
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">First Name</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppfname"
                                id="clientAppfname"
                                value = "<?php echo isset($clientAppData["fname"]) ? $clientAppData["fname"] : "" ?>"
                                />
                        </div>
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Last Name:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientApplname"
                                id="clientApplname"
                                value = "<?php echo isset($clientAppData["lname"]) ? $clientAppData["lname"] : "" ?>"
                                />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Email:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppEmail"
                                id="clientAppEmail"
                                value = "<?php echo isset($clientAppData["email"]) ? $clientAppData["email"] : "" ?>"
                                />
                        </div>
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Telephone:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppTelephone"
                                id="clientAppTelephone"
                                value = "<?php echo isset($clientAppData["telephone"]) ? $clientAppData["telephone"] : "" ?>"
                                />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Address 1:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppAddress1"
                                id="clientAppAddress1"
                                value = "<?php echo isset($clientAppData["address1"]) ? $clientAppData["address1"] : "" ?>"
                                />
                        </div>
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Address 2:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppAddress2"
                                id="clientAppAddress2"
                                value = "<?php echo isset($clientAppData["address2"]) ? $clientAppData["address2"] : "" ?>"
                                />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">City:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppCity"
                                id="clientAppCity"
                                value = "<?php echo isset($clientAppData["city"]) ? $clientAppData["city"] : "" ?>"
                                />
                        </div>
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">State:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppState"
                                id="clientAppState"
                                value = "<?php echo isset($clientAppData["state"]) ? $clientAppData["state"] : "" ?>"
                                />
                        </div>
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">ZipCode:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="clientAppZipCode"
                                id="clientAppZipCode"
                                value = "<?php echo isset($clientAppData["zipcode"]) ? $clientAppData["zipcode"] : "" ?>"
                                />
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <label for="agency" class="form-label">Comment:</label>
                            <textarea
                                class="form-control"
                                id="clientAppComment"
                                name="clientAppComment"
                                rows="3"
                                ><?php echo isset($clientAppData["comments"]) ? $clientAppData["comments"] : "" ?></textarea>
                        </div>
                    </div>
                    <input
                        type="hidden"
                        name="task_id"
                        id="task_id"
                        value="<?php 
                        if(isset($_GET['task_id'])) {
                        echo $_GET['task_id'];
                        }
                        ?>"/>
                    <div class="row">
                        <div class="col-sm-12 mt-3">
                            <input
                                type="button"
                                style="padding:5px 50px;margin-right:20px;"
                                id="savebtn"
                                class="btn btn-primary"
                                name="submitEngForm"
                                value="Save/Update"
                                onclick="saveClientAppform()"/>
                            <input
                                type="button"
                                style="padding:5px 50px;"
                                class="btn btn-primary"
                                name="cancelEngForm"
                                value="Cancel"
                                onclick="cancelClientAppform()"/>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function saveClientform(e) {
        // e.prevenDefault();
        var formData = $('#savetravelform').serialize();
        if ($('#travel_destination').val() === "") {
            $('#travel_destination').attr("required", "required");
            $("#travel_destination")
                .get(0)
                .setCustomValidity("Please enter travel destination");
            $("#travel_destination")
                .get(0)
                .reportValidity();
        } else if ($('#travel_date').val() === "") {
            $('#travel_date').attr("required", "required");
            $("#travel_date")
                .get(0)
                .setCustomValidity("Please enter travel date");
            $("#travel_date")
                .get(0)
                .reportValidity();
        } else if ($('#travel_first_name').val() === "") {
            $('#travel_first_name').attr("required", "required");
            $("#travel_first_name")
                .get(0)
                .setCustomValidity("Please enter first name");
            $("#travel_first_name")
                .get(0)
                .reportValidity();
        } else if ($('#travel_last_name').val() === "") {
            $('#travel_last_name').attr("required", "required");
            $("#travel_last_name")
                .get(0)
                .setCustomValidity("Please enter last name");
            $("#travel_last_name")
                .get(0)
                .reportValidity();
        } else {
            $.ajax({
                url: 'forms/travel_approval_form_save.php',
                type: 'POST',
                data: formData,
                dataType: "json",
                success: (res) => {
                    $("#travel_approval_form").modal("hide");
                }
            })
        }
    }
    function cancelTravelform() {
        $("#travel_approval_form").modal("hide");
    }
</script>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Plugins js-->
<script
    src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script
    src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>

</body>

</html>