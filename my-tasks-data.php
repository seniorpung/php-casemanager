<?php
require_once "layouts/config.php";
include 'layouts/session.php';

$s_date = $_POST['select_date'];

$start_date = date("Y-01-01", strtotime('-1 year'));
$end_date = date("Y-12-t", strtotime('-1 year'));

if($s_date=="last_year"){

     $sq = "SELECT task_id, case_number, task_name, created_datetime, task_status_name, defaultSLA FROM casemanager.tasks_view
    WHERE `created_datetime` between '$start_date' and '$end_date' ";

}else{
 //select query statement
        $sq = "SELECT task_id, case_number, task_name, created_datetime, task_status_name, defaultSLA FROM casemanager.tasks_view
    WHERE `created_datetime` >= CURDATE() - INTERVAL $s_date DAY ";
    }
        $res = mysqli_query($link, $sq);

?>

<div class="table-responsive px-3" data-simplebar style="max-height: 395px;">
                                    <table class="table table-hover align-middle table-nowrap">
                                        <thead>
                                        <tr class="table-success">
                                            <th>ID</th>
                                             <th>Case Number</th>
                                             <th>Task Name</th>
                                             <th>Created Date</th>
                                             <th>Status</th>
                                             <th>SLA</th>
                                              
                                        </tr>
                                    </thead>
                                        <tbody>
                                            <?php    

                                        while($rest = mysqli_fetch_array($res)){
                                    ?>
                                           <tr>
                                          <td><?php echo($rest['task_id']); ?></td>
                                          <td><?php echo($rest['case_number']); ?></td>
                                          <td><?php echo($rest['task_name']); ?></td>
                                          <td><?php echo($rest['created_datetime']); ?></td>
                                          <td><?php echo($rest['task_status_name']); ?></td>
                                          <td><?php echo($rest['defaultSLA']); ?></td>
                                        

                                           
                                            </tr>
                                                   <?php
                                }

                                ?>

                                        </tbody>
                                    </table>
                                </div>