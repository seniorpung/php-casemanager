<?php 
ob_start();
require_once "layouts/config.php";
$presult = $link->query("SELECT photo FROM users where id='".trim($_SESSION["id"])."'");
if ($presult->num_rows > 0) {
    $prow = $presult -> fetch_assoc();
    $user_photo = $prow['photo'];
} else {
    $user_photo = "";
}
?>
<style>
#overlay {background: #ffffff;color: #666666;position: fixed;height: 100%;width: 100%;z-index: 5000;top: 0;left: 0;float: left;text-align: center;padding-top: 25%;opacity: .92;}
.spinner {margin: 0 auto;height: 64px;width: 64px;animation: rotate 0.8s infinite linear;border: 5px solid #222222;border-right-color: transparent;border-radius: 50%;}
@keyframes rotate { 0% {transform: rotate(0deg);} 100% {transform: rotate(360deg);} }
</style> 
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="index.php" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.svg" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-sm.svg" alt="" height="24"> <span class="logo-txt">Case Manager</span>
                    </span>
                </a>
                <a href="index.php" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.svg" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-sm.svg" alt="" height="24"> <span class="logo-txt">Case Manager</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" class="form-control" placeholder="<?php echo $language["Search"]; ?>">
                    <button class="btn btn-primary" type="button"><i class="bx bx-search-alt align-middle"></i></button>
                </div>
            </form>
        </div>

        <div class="d-flex">
            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item" id="page-header-search-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">
        
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="<?php echo $language["Search"]; ?>" aria-label="Search Result">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php  
            $created_by = $_SESSION["id"];
            $created_datetime = date('Y-m-d H:i:s');
        
            global $link;
            if(!class_exists('CRUD'))    include $_SERVER['DOCUMENT_ROOT'].'/class.crud.php';
        
            $crudObj = new CRUD('cases', 'case_id');
            $crudObj->mysqli = $link;        

            $colsData   = array();
            $colsData[] = '*';
            $colsData[] = '(SELECT photo FROM users WHERE notifications.user_id=users.id) AS user_photo';
            $UnreadNotifications = 0;
            $Notifications = $crudObj->FindAll('notifications', $colsData, array('user_id='.$created_by, 'is_read=\'No\''), 0, 0, array(array('id', 'DESC')), false); 
            if(isset($Notifications) && is_array($Notifications) && count($Notifications)>0){
                $UnreadNotifications = count($Notifications);
            }
            ?>
           
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end" id="page-header-user-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php if(!empty($user_photo)){?>
                    <img class="rounded-circle header-profile-user user_profile_picture" src="assets/images/users/<?php echo $user_photo;?>" alt="">
                    <?php } else {?>
                    <img class="rounded-circle header-profile-user user_profile_picture" src="assets/images/users/default.jpg" alt="">
                    <?php } ?>
                    <span class="d-none d-xl-inline-block ms-1 fw-medium"><?php echo $_SESSION["username"]; ?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="user-profile.php?id=<?php echo $_SESSION["id"]; ?> "><i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i> Profile</a>
                    <a class="dropdown-item" href="auth-lock-screen.php"><i class="mdi mdi-lock font-size-16 align-middle me-1"></i> <?php echo $language["Lock_screen"]; ?> </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="mdi mdi-logout font-size-16 align-middle me-1"></i> <?php echo $language["Logout"]; ?></a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu"><?php echo $language["Menu"]; ?></li>
                <li>
                    <a href="index.php">
                        <i data-feather="home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="addcase.php">
                        <i class="mdi mdi-file-document-edit-outline"></i>
                        <span>New Case</span>
                    </a>
                </li>
                <li>
                    <a href="cases.php">
                        <i class="mdi mdi-file-document-multiple-outline"></i>
                        <span>My Cases</span>
                    </a>
                </li>
                <li>
                    <a href="my-tasks.php">
                        <i class="mdi mdi-file-table-outline"></i>
                        <span>My Tasks</span>
                    </a>
                </li>
                <li>
                    <a href="manage-contacts.php">
                       <i class="bx bxs-user-detail"></i>
                       <span>Contacts</span>
                    </a>
                </li>
                <li>
                    <a href="calendar.php">
                       <i class="mdi mdi-calendar-month"></i>
                       <span>Calendar</span>
                    </a>
                </li>
                <li>
                    <a href="waiting-queue.php">
                        <i class="mdi mdi-account-switch-outline"></i>
                        <span>Waiting Queue</span>
                    </a>
                </li>
                <li>
                    <a href="my-group-queue.php">
                        <i class="bx bx-group"></i>
                        <span>Group Queue</span>
                    </a>
                </li>
                <?php if($_SESSION["permission_id"] == '2') : ?>
                <li class="active">
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="mdi mdi-application-cog"></i>
                                <span> Admin</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false" id="mm-admin">  
                            <li>
                                    <a href="admin-dashboard.php">
                                    <i class="bx bxs-dashboard"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>                  
                                <li>
                                    <a href="manage-users.php">
                                        <i data-feather="users"></i>
                                        <span> Manage Users</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="manage_group.php">
                                        <i data-feather="users"></i>
                                        <span> Manage Groups</span>
                                    </a>
                                </li>
                                <!--
                                <li>
                                    <a href="manage_notification.php?filter=all">
                                        <i data-feather="users"></i>
                                        <span> Manage Notifications</span>
                                    </a>
                                </li>
                                -->
                                <li>
                                    <a href="manage-roles.php">
                                        <i class="bx bx-user-circle"></i>
                                        <span> Manage Roles</span>
                                    </a>
                                </li>                                
                                <li>
                                    <a href="manage-workflow.php">
                                        <i class="mdi mdi-page-next-outline"></i>
                                        <span> Manage Workflow</span>
                                    </a>
                                </li>
                                <li>
                                <a href="manage-cases.php" data-key="t-manage-cases">
                                    <i class="mdi mdi-file-document-multiple-outline"></i>
                                    
                                    <span> Manage Cases</span>
                                </a>
                                </li>                                   
                                <li>
                                <a href="manage-tasks.php">
                                    <i class="mdi mdi-file-table-outline"></i>
                                    <span> Manage Tasks</span>
                                </a>
                                </li> 

                                <li>
                                <a href="manage-email-template.php">
                                    <i class="bx bx-mail-send"></i>
                                    <span> Email Templates</span>
                                </a>
                                </li> 
                                <li>
                                <a href="form-templates.php">
                                    <i class="mdi mdi-form-select"></i>
                                    <span> Form Templates</span>
                                </a>
                                </li> 
                                <li>
                                <a href="rpt-activity-log.php">
                                    <i class="bx bxs-user-detail"></i>
                                    <span> Activity Log</span>
                                </a>
                                </li> 
                                <li>
                                <a href="kiosk-checkin.php">
                                    <i class="bx bx-desktop"></i>
                                    <span> Start Kiosk</span>
                                </a>
                                </li> 
                                
                            </ul>
                        </li>
                <!-- Admin Side Bar Menu End -->
                <?php endif; ?>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="mdi mdi-file-chart-outline"></i>
                        <span>Reports</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        <li>
                            <a href="rpt-open-cases.php">
                                <i class="bx bxs-report"></i>
                                <span>Open Cases</span>
                            </a>
                        </li>
                        <li>
                            <a href="rpt-all-open-tasks.php">
                                <i class="bx bxs-report"></i>
                                <span>All Open Tasks</span>
                            </a>
                        </li>
                        <li>
                            <a href="rpt-all-overdue-tasks.php">
                                <i class="bx bxs-report"></i>
                                <span>All Overdue Tasks</span>
                            </a>
                        </li>
                        <li>
                            <a href="rpt-contact-activities.php">
                                <i class="bx bxs-report"></i>
                                <span>Contact Activities</span>
                            </a>
                        </li>
                        <li>
                            <a href="rpt-customer-service-all-status.php">
                                <i class="bx bxs-report"></i>
                                <span>Customer Service</span>
                            </a>
                        </li>
                        <li>
                            <a href="staff-assignment-detail.php">
                                <i class="bx bxs-report"></i>
                                <span>Staff Assignment</span>
                            </a>
                        </li> 
                        <li>
                            <a href="rpt-notification-events.php">
                                <i class="bx bxs-report"></i>
                                <span>Notification Events</span>
                            </a>
                        </li>                        
                    </ul>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->