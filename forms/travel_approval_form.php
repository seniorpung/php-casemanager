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
                <h4 class="modal-title">Travel Approval Form - By Sovratec</h4>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form method="post" id="savetravelform">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="task_id" id="task_id">
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Travel Destination:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="travel_destination"
                                id="travel_destination"
                                value = "<?php echo isset($travelData["travel_destination"]) ? $travelData["travel_destination"]: "" ?>"
                                />
                        </div>
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Travel Date:</label>
                            <input class="form-control" type="date" name="travel_date" id="travel_date" value = "<?php echo isset($travelData["travel_date"]) ? $travelData["travel_date"] : "" ?>"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Traveler First Name:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="travel_first_name"
                                id="travel_first_name"
                                value = "<?php echo isset($travelData["travel_first_name"]) ? $travelData["travel_first_name"] : "" ?>"
                                />
                        </div>
                        <div class="col-sm-6">
                            <label for="agency" class="form-label">Traveler Last Name:</label>
                            <input
                                class="form-control"
                                type="text"
                                name="travel_last_name"
                                id="travel_last_name"
                                value = "<?php echo isset($travelData["travel_last_name"]) ? $travelData["travel_last_name"] : ""?>"
                                />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <label for="agency" class="form-label">Travel Purpose:</label>
                            <textarea
                                class="form-control"
                                id="travel_purpose"
                                name="travel_purpose"
                                rows="3"
                                ><?php echo isset($travelData["travel_purpose"]) ? $travelData["travel_purpose"] : "" ?></textarea>
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
                                onclick="saveTravelform()"/>
                            <input
                                type="button"
                                style="padding:5px 50px;"
                                class="btn btn-primary"
                                name="cancelEngForm"
                                value="Cancel"
                                onclick="cancelTravelform()"/>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function saveTravelform(e) {
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