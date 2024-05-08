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

            <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
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
                <button type="button" class="btn header-item" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">

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

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="grid" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <div class="p-2">
                    <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="apps-calendar.php">
                                    <img src="assets/images/brands/calendar.png" alt="Github">
                                    <span>Calendar</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="apps-email-inbox.php">
                                    <img src="assets/images/brands/Mail.png" alt="bitbucket">
                                    <span>Email</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="apps-chat.php">
                                    <img src="assets/images/brands/11.png" alt="dribbble">
                                    <span>Chat</span>
                                </a>
                            </div>
                        </div>
                    </div>
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
            ?><div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon position-relative" id="page-header-notifications-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="bell" class="icon-lg"></i>
                    <span class="badge bg-danger rounded-pill"><?php echo $UnreadNotifications; ?></span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0"> <?php echo $language["Notifications"]; ?> </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small text-reset text-decoration-underline"> <?php echo $language["Unread"]; ?> (<?php echo $UnreadNotifications; ?>)</a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;"><?php
                    if(isset($Notifications) && is_array($Notifications) && count($Notifications)>0){
                        foreach($Notifications as $dts){
                            ?><a href="#!" class="text-reset notification-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="assets/images/users/avatar-3.jpg" class="rounded-circle avatar-sm" alt="user-pic">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $dts['title']; ?></h6>
                                        <div class="font-size-13 text-muted">
                                            <p class="mb-1"><?php echo $dts['description']; ?></p>
                                            <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span><?php echo date('jS M, Y h:i:s A', strtotime($dts['created_at'])); ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </a><?php
                        }
                    } 
                    ?></div><?php 
                    if(isset($Notifications) && is_array($Notifications) && count($Notifications)>0){
                        ?><div class="p-2 border-top d-grid">
                            <a class="btn btn-sm btn-link font-size-14 text-center" href="manage_notification.php?filter=user">
                                <i class="mdi mdi-arrow-right-circle me-1"></i> <span><?php echo $language["View_More"]; ?></span> 
                            </a>
                        </div><?php
                    }
                ?></div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item right-bar-toggle me-2">
                    <i data-feather="settings" class="icon-lg"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="assets/images/users/avatar-1.jpg" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium"><?php echo $_SESSION["username"]; ?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="apps-contacts-profile.php"><i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i> <?php echo $language["Profile"]; ?></a>
                   <a class="dropdown-item" href="auth-lock-screen.php"><i class="mdi mdi-lock font-size-16 align-middle me-1"></i> <?php echo $language["Lock_screen"]; ?></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="mdi mdi-logout font-size-16 align-middle me-1"></i> <?php echo $language["Logout"]; ?></a>
                </div>
            </div>

        </div>
    </div>
</header>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="layouts-horizontal.php" id="topnav-dashboard" role="button">
                            <i data-feather="home"></i><span> Home</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="addcase.php" role="button">
                            <i class="mdi mdi-file-document-edit-outline"></i><span> New Case</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="cases.php" role="button">
                            <i class="mdi mdi-file-document-multiple-outline"></i><span> My Cases</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="my-tasks.php" role="button">
                        <i class="mdi mdi-file-table-outline"></i><span> My Tasks</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="manage-contacts.php" role="button">
                        <i class="mdi mdi-file-table-outline"></i><span> Contacts</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="my-group-queue.php" role="button">
                            <i class="bx bx-group"></i><span> Group Queue</span>
                        </a>
                    </li>

     
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                            <i class="mdi mdi-application-cog"></i><span> Admin</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="admin-dashboard.php" class="dropdown-item">Dashboard</a>
                            <a href="manage-users.php" class="dropdown-item"> Manage Users</a>
                            <a href="manage_group.php" class="dropdown-item"> Manage Groups</a>
                            <a href="manage_notification.php?filter=all" class="dropdown-item"> Manage Notifications</a>
                            <a href="manage-roles.php" class="dropdown-item"> Manage Roles</a>
                            <a href="manage-workflow.php" class="dropdown-item"> Manage Workflows</a>
                            <a href="manage-cases.php" class="dropdown-item"> Manage Cases</a>
                            <a href="manage-tasks.php" class="dropdown-item"> Manage Tasks</a>
                            <a href="manage-contacts.php" class="dropdown-item"> Manage Contacts</a>
                            <a href="manage-email-template.php" class="dropdown-item"> Email Templates</a>
                            <a href="form-templates.php" class="dropdown-item"> Form Templates</a>
                            <a href="rpt-activity-log.php" class="dropdown-item"> Activity log</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                            <i class="mdi mdi-file-chart-outline"></i><span> Reports</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="report-case-sla.php" class="dropdown-item"> Task Aging Report</a>
                            <a href="rpt-customer-service-all-status.php" class="dropdown-item"> Customer Service</a>
                            <a href="rpt-contact-activities.php" class="dropdown-item"> Contact Activities</a>
                            <a href="staff-assignment-detail.php" class="dropdown-item"> Staff Assignment</a>
                            <a href="rpt-all-open-tasks.php" class="dropdown-item"> All Open Tasks</a>
                            <a href="rpt-all-overdue-tasks.php" class="dropdown-item"> All Overdue Tasks</a>
                            <a href="rpt-notification-events.php" class="dropdown-item"> Notification Events</a>
                            
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>