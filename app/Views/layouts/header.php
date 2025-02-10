<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta name="facebook-domain-verification" content="hmgpdjforybq3d41qxbf3thstnf3z4" />
    <meta charset="utf-8" />
    <title>AutoConX | Beta 1.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url('/assets/images/logo72x72.png'); ?>">

    <link rel="stylesheet" href="<?php echo base_url('/assets/libs/jsvectormap/css/jsvectormap.min.css'); ?>">

    <!-- App css -->
    <link href="<?php echo base_url('/assets/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('/assets/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('/assets/css/app.min.css'); ?>" rel="stylesheet" type="text/css" />

    <!-- Notyf -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="https://unpkg.com/@sjmc11/tourguidejs/dist/css/tour.min.css">

    <?php if (isset($css_critical)) {
        echo $css_critical;
    } ?>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <!-- เรียกใช้ Google Translate Element -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <style>
        /** BASE **/
        * {
            font-family: 'Kanit', sans-serif;
        }

        .disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ปุ่ม Gradient Animate */
        .gradient-animate-btn {
            display: inline-block;
            font-weight: bold;
            color: #fff !important;
            /* สีตัวอักษร */
            border: none;
            /* border-radius: 25px; */
            cursor: pointer;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            background: linear-gradient(90deg, #6a11cb, #2575fc, #6a85e6, #9d50bb);
            background-size: 300% 300%;
            box-shadow: 0 4px 8px rgba(101, 151, 253, 0.6);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: gradientAnimation 4s ease infinite;
            /* เพิ่มการ Animate */
        }

        /* เอฟเฟกต์ Hover */
        .gradient-animate-btn:hover {
            /* transform: scale(1.05); */
            /* ขยายขนาดเล็กน้อย */
            box-shadow: 0 8px 15px rgba(101, 151, 253, 0.8);
        }

        /* เอฟเฟกต์กดปุ่ม */
        .gradient-animate-btn:active {
            transform: scale(0.95);
            box-shadow: 0 4px 8px rgba(101, 151, 253, 0.6);
        }

        /* Animation สำหรับ Gradient */
        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .position-relative {
            position: relative;
        }

        .profile-thumb-md {
            width: 50px !important;
            height: 50px !important;
        }

        .profile-platform-icon {
            position: absolute;
            width: 16px;
            height: 16px;
            bottom: 0;
            right: 0;
            border-radius: 50%;
            background: white;
            padding: 2px;
        }
    </style>
    <style>
        .tg-backdrop,
        .tg-dialog {
            z-index: 9999 !important;
        }

        .tg-dialog .tg-dialog-footer button.tg-dialog-btn {
            background-color: #fff;
        }
    </style>
    <script>
        var serverUrl = '<?php echo base_url(); ?>'
        var userID = '<?php echo session()->get('userID'); ?>'
        var userOwnerID = '<?php echo session()->get('user_owner_id'); ?>'
        var APP_ID = '<?php echo getenv('APP_ID'); ?>'
        var IG_APP_ID = '<?php echo getenv('IG_APP_ID'); ?>'
        const wsUrl = window.location.hostname === "localhost" ? "ws://localhost:3000" : "wss://websocket.evxcars.com:8080";
        var subscriptionStatus = '<?php echo session()->get('subscription_status'); ?>'

        // สร้างการเชื่อมต่อกับ WebSocket Server
        const ws = new WebSocket(wsUrl);
        console.log(`WebSocket URL: ${wsUrl}`);
    </script>
</head>

<body>

    <!-- Top Bar Start -->
    <div class="topbar d-print-none">
        <div class="container-xxl">
            <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">


                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li>
                        <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                            <i class="iconoir-menu-scale"></i>
                        </button>
                    </li>
                    <li class="mx-3 welcome-text">
                        <h3 class="mb-0 fw-bold text-truncate">v.Beta 1.0</h3>
                        <!-- <h6 class="mb-0 fw-normal text-muted text-truncate fs-14">Here's your overview this week.</h6> -->
                    </li>
                </ul>
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">

                    <li class="topbar-item">
                        <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                            <i class="icofont-sun dark-mode"></i>
                            <i class="icofont-moon light-mode"></i>
                        </a>
                    </li>

                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon disabled" data-bs-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false">
                            <i class="icofont-bell-alt"></i>
                            <span class="alert-badge"></span>
                        </a>
                        <div class="dropdown-menu stop dropdown-menu-end dropdown-lg py-0">

                            <h5 class="dropdown-item-text m-0 py-3 d-flex justify-content-between align-items-center">
                                Notifications <a href="#" class="badge text-body-tertiary badge-pill">
                                    <i class="iconoir-plus-circle fs-4"></i>
                                </a>
                            </h5>
                            <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified mb-1" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mx-0 active" data-bs-toggle="tab" href="#All" role="tab" aria-selected="true">
                                        All <span class="badge bg-primary-subtle text-primary badge-pill ms-1">24</span>
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mx-0" data-bs-toggle="tab" href="#Projects" role="tab" aria-selected="false" tabindex="-1">
                                        Projects
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mx-0" data-bs-toggle="tab" href="#Teams" role="tab" aria-selected="false" tabindex="-1">
                                        Team
                                    </a>
                                </li>
                            </ul>
                            <div class="ms-0" style="max-height:230px;" data-simplebar>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="All" role="tabpanel" aria-labelledby="all-tab" tabindex="0">
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-wolf fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing and industry.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->

                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">10 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-apple-swift fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Meeting with designers</h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->

                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">40 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-birthday-cake fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">UX 3 Task complete.</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">1 hr ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-drone fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed</h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 hrs ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-user fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Payment Successfull</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                    </div>
                                    <div class="tab-pane fade" id="Projects" role="tabpanel" aria-labelledby="projects-tab" tabindex="0">
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">40 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-birthday-cake fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">UX 3 Task complete.</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">1 hr ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-drone fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed</h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 hrs ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-user fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Payment Successfull</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                    </div>
                                    <div class="tab-pane fade" id="Teams" role="tabpanel" aria-labelledby="teams-tab" tabindex="0">
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">1 hr ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-drone fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed</h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 hrs ago</small>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-user fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Payment Successfull</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                    </div>
                                </div>

                            </div>
                            <!-- All-->
                            <a href="pages-notifications.html" class="dropdown-item text-center text-dark fs-13 py-2">
                                View All <i class="fi-arrow-right"></i>
                            </a>
                        </div>
                    </li>

                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <img src="<?php echo session()->get('thumbnail') ?: base_url('/assets/images/conX.png'); ?>" alt="" class="thumb-lg rounded-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0">
                            <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                                <div class="position-relative">
                                    <!-- รูปโปรไฟล์ -->
                                    <img src="<?php echo session()->get('thumbnail') ?: base_url('/assets/images/conX.png'); ?>" alt="Profile Picture" class="thumb-md profile-thumb-md rounded-circle">
                                    <!-- ไอคอน -->
                                    <img src="<?php echo base_url('/assets/images/' . getPlatformIcon(session()->get('platform'))); ?>" alt="profile-platform Icon" class="profile-platform-icon">
                                </div>
                                <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                    <h6 class="my-0 fw-medium text-dark fs-13"><?php echo session()->get('name'); ?></h6>
                                    <?php if (session()->get('subscription_status') == 'active') { ?>
                                        <span class="badge rounded-pill bg-info-subtle text-info"><img style="margin-bottom: 2px;" width="14" src="https://cdn-icons-png.flaticon.com/512/5524/5524802.png" alt=""> อัพเกรดแล้ว</span>
                                    <?php } else { ?>
                                        <?php if (session()->get('user_owner_id') == '') { ?>
                                            <span class="badge rounded-pill bg-dark-subtle text-dark">Free</span>
                                        <?php } else if (session()->get('user_owner_id') != '') { ?>
                                            <span class="badge rounded-pill bg-warning-subtle text-warning">Team</span>
                                        <?php } ?>

                                    <?php } ?>
                                </div><!--end media-body-->
                            </div>

                            <div class="dropdown-divider mt-0"></div>
                            <small class="text-muted px-2 pb-1 d-block">Account</small>
                            <a class="dropdown-item" href="<?php echo base_url('/profile'); ?>"><i class="las la-user fs-18 me-1 align-text-bottom"></i> Profile</a>
                            <small class="text-muted px-2 py-1 d-block">Settings</small>
                            <a class="dropdown-item" href="<?php echo base_url('/profile'); ?>"><i class="las la-cog fs-18 me-1 align-text-bottom"></i>Account Settings</a>
                            <!-- <a class="dropdown-item disabled" href="<?php echo base_url('/profile'); ?>"><i class="las la-lock fs-18 me-1 align-text-bottom"></i> Security</a> -->
                            <a class="dropdown-item" href="<?php echo base_url('/help'); ?>"><i class="las la-question-circle fs-18 me-1 align-text-bottom"></i> Help Center</a>
                            <div class="dropdown-divider mb-0"></div>
                            <a class="dropdown-item text-danger" href="<?php echo base_url('/logout'); ?>"><i class="las la-power-off fs-18 me-1 align-text-bottom"></i> ออกจากระบบ</a>
                        </div>
                    </li>
                </ul><!--end topbar-nav-->
            </nav>
            <!-- end navbar-->
        </div>
    </div>
    <!-- Top Bar End -->
    <!-- leftbar-tab-menu -->
    <div class="startbar d-print-none">
        <!--start brand-->
        <div class="brand">
            <a href="<?php echo base_url(); ?>" class="logo">
                <span>
                    <img src="<?php echo base_url('/assets/images/logo72x72.png'); ?>" alt="logo-small" class="logo-sm" style="display: none;">
                </span>
                <span class="">
                    <img width="170" height="38" src="<?php echo base_url('/assets/images/conXx.png'); ?>" alt="logo-large" class="logo-lg logo-x">
                    <!-- <img width="170" height="38" src="<?php echo base_url('/assets/images/conXx.png'); ?>" alt="logo-large" class="logo-lg logo-dark"> -->
                </span>
            </a>
        </div>
        <!--end brand-->
        <!--start startbar-menu-->
        <div class="startbar-menu">
            <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                <div class="d-flex align-items-start flex-column w-100">
                    <!-- Navigation -->
                    <ul class="navbar-nav mb-auto w-100">
                        <li class="menu-label pt-0 mt-0">
                            <!-- <small class="label-border">
                                <div class="border_left hidden-xs"></div>
                                <div class="border_right"></div>
                            </small> -->
                            <span>Main Menu</span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('/'); ?>">
                                <i class="iconoir-home-simple menu-icon"></i>
                                <span>Dashboards</span>
                            </a>
                        </li><!--end nav-item-->

                        <li class="nav-item" data-tg-order="2" data-tg-tour='แชทจากแพลตฟอร์มต่าง ๆ ถูกรวมไว้ที่นี่ ทำให้สามารถจัดการได้ง่าย !' data-tg-title="2. แชทจากทุกแพลตฟอร์มถูกรวมไว้ที่เดียว">
                            <a class="nav-link" href="<?php echo base_url('/chat'); ?>">
                                <i class="iconoir-view-grid menu-icon"></i>
                                <span>Chat</span>
                            </a>
                        </li><!--end nav-item-->

                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('/team'); ?>">
                                <i class="iconoir-community menu-icon"></i>
                                <span>Team</span>
                            </a>
                        </li><!--end nav-item-->

                        <?php if (session()->get('user_owner_id') == '') { ?>

                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0);">
                                    <i class="iconoir-compact-disc menu-icon"></i>
                                    <span>Setting <span class="badge rounded-pill bg-success-subtle text-success">New</span></span>
                                </a>
                                <div class="collapse show" id="sidebarSetting">
                                    <ul class="nav flex-column">
                                        <li class="nav-item " data-tg-order="1" data-tg-tour='คุณสามารถเชื่อมต่อแพลตฟอร์มยอดนิยม เช่น Line, Facebook, Instagram, WhatsApp ได้ในเวลาไม่ถึง 1 นาที และสามารถใช้งานระบบได้ทันที! 🚀' data-tg-title="ขั้นตอนแรก สร้างการเชื่อมต่อ">
                                            <a class="nav-link" href="<?php echo base_url('/setting/connect'); ?>" class="menu-connect"> Connect</a>
                                        </li><!--end nav-item-->
                                        <li class="nav-item" data-tg-order="3" data-tg-tour='เรามีระบบ AutoConX AI ช่วยธุรกิจของคุณโดยการตอบคำถามลูกค้าอัตโนมัติ เพิ่มยอดขายผ่านการส่งโปรโมชั่น และลดเวลาในการทำงานด้วยระบบแชท AI ที่ทำงานตลอด 24 ชั่วโมง' data-tg-title="3. Training">
                                            <a class="nav-link" href="<?php echo base_url('/setting/message'); ?>"> Training</a>
                                        </li><!--end nav-item-->
                                    </ul><!--end nav-->
                                </div>
                            </li><!--end nav-item-->

                        <?php } ?>
                    </ul><!--end navbar-nav--->
                </div>

                <div class="col-12" style="display:none;" id="message-collapse">
                    <br />
                    <ul class="navbar-nav mb-auto w-100">
                        <li class="menu-label pt-0 mt-0">
                            <!-- <small class="label-border">
                                <div class="border_left hidden-xs"></div>
                                <div class="border_right"></div>
                            </small> -->
                            <span>Message</span>
                        </li>
                    </ul>
                    <div class="chat-box-left">
                        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link py-2 active" id="messages_chat_tab_menu" data-bs-toggle="tab" href="#messages_chat_menu" role="tab">Messages</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link py-2" id="active_chat_tab_menu" data-bs-toggle="tab" href="#active_chat_menu" role="tab">Active</a>
                            </li>
                        </ul>
                        <div class="chat-search p-3">
                            <div class="p-1 bg-light rounded rounded-pill">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <button id="button-addon2" type="submit" class="btn btn-link text-secondary"><i class="fa fa-search"></i></button>
                                    </div>
                                    <input type="search" placeholder="Searching.." aria-describedby="button-addon2" class="form-control border-0 bg-light">
                                </div>
                            </div>
                        </div><!--end chat-search-->

                        <?php if (isset($rooms)) { ?>
                            <div class="chat-body-left px-3" data-simplebar>
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade show active" id="messages_chat_menu">
                                        <div class="row">
                                            <div class="col">

                                                <div id="rooms-list-menu">
                                                    <?php foreach ($rooms as $room): ?>
                                                        <div class="room-item p-2 border-dashed border-theme-color rounded mb-2" data-room-id="<?php echo $room->id ?>" data-platform="<?php echo $room->platform ?>">
                                                            <a href="#" class="">
                                                                <div class="d-flex align-items-start">
                                                                    <div class="position-relative">
                                                                        <?php if (($room->profile == 0) || ($room->profile == null)) {
                                                                            $room->profile = '/assets/images/conX.png';
                                                                        } ?>
                                                                        <img src="<?php echo $room->profile; ?>" alt="" class="thumb-lg rounded-circle">
                                                                        <span class="position-absolute bottom-0 end-0">
                                                                            <img src="<?php echo base_url('/assets/images/' . $room->ic_platform); ?>" width="14">
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                                                        <h6 class="my-0 fw-medium text-dark fs-10"><?php echo $room->customer_name; ?>
                                                                            <!-- <small class="float-end text-muted fs-5"><?php if ($room->last_time != '') echo timeElapsed($room->last_time); ?></small> -->
                                                                        </h6>
                                                                        <p class="text-muted mb-0"><span class="text-primary"><?php echo $room->last_message; ?></span>
                                                                        </p>
                                                                    </div><!--end media-body-->
                                                                </div><!--end media-->
                                                            </a> <!--end-->
                                                        </div><!--end div-->
                                                    <?php endforeach; ?>

                                                </div>
                                            </div><!--end col-->
                                        </div><!--end row-->
                                    </div><!--end general chat-->

                                    <div class="tab-pane fade" id="active_chat_menu">
                                        <div class="p-2 border-dashed border-theme-color rounded mb-2">
                                            <a href="" class="">
                                                <div class="d-flex align-items-start">
                                                    <div class="position-relative">
                                                        <img src="assets/images/users/avatar-3.jpg" alt="" class="thumb-lg rounded-circle">
                                                        <span class="position-absolute bottom-0 end-0"><i class="fa-solid fa-circle text-success fs-10 border-2 border-theme-color"></i></span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                                        <h6 class="my-0 fw-medium text-dark fs-14">Shauna Jones
                                                            <small class="float-end text-muted fs-11">15 Feb</small>
                                                        </h6>
                                                        <p class="text-muted mb-0">Congratulations!</p>
                                                    </div><!--end media-body-->
                                                </div><!--end media-->
                                            </a> <!--end-->
                                        </div><!--end div-->
                                        <div class="p-2 border-dashed border-theme-color rounded mb-2">
                                            <a href="" class="">
                                                <div class="d-flex align-items-start">
                                                    <div class="position-relative">
                                                        <img src="assets/images/users/avatar-5.jpg" alt="" class="thumb-lg rounded-circle">
                                                        <span class="position-absolute bottom-0 end-0"><i class="fa-solid fa-circle text-success fs-10 border-2 border-theme-color"></i></span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                                        <h6 class="my-0 fw-medium text-dark fs-14">Frank Wei
                                                            <small class="float-end text-muted fs-11">2 Mar</small>
                                                        </h6>
                                                        <p class="text-muted mb-0"><i class="iconoir-microphone"></i> Voice message!</p>
                                                    </div><!--end media-body-->
                                                </div><!--end media-->
                                            </a> <!--end-->
                                        </div><!--end div-->
                                        <div class="p-2 border-dashed border-theme-color rounded mb-2">
                                            <a href="" class="">
                                                <div class="d-flex align-items-start">
                                                    <div class="position-relative">
                                                        <img src="assets/images/users/avatar-6.jpg" alt="" class="thumb-lg rounded-circle">
                                                        <span class="position-absolute bottom-0 end-0"><i class="fa-solid fa-circle text-success fs-10 border-2 border-theme-color"></i></span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                                        <h6 class="my-0 fw-medium text-dark fs-14">Carol Maier
                                                            <small class="float-end text-muted fs-11">14 Mar</small>
                                                        </h6>
                                                        <p class="text-muted mb-0">Send a pic.!</p>
                                                    </div><!--end media-body-->
                                                </div><!--end media-->
                                            </a> <!--end-->
                                        </div><!--end div-->
                                    </div><!--end group chat-->

                                </div><!--end tab-content-->
                            </div>
                        <?php } ?>

                    </div><!--end chat-box-left -->
                </div>

                <?php if (session()->get('user_owner_id') == '') { ?>
                    <?php if (session()->get('subscription_status') != 'active') { ?>
                        <div class="update-msg text-center">
                            <div class="d-flex justify-content-center align-items-center thumb-lg update-icon-box  rounded-circle mx-auto">
                                <img src="<?php echo base_url('/assets/images/conX.png'); ?>" alt="" class="thumb-lg rounded-circle">
                            </div>
                            <h5 class="mt-3">ใช้งานฟรี</h5>
                            <p class="text-muted mb-0">เชื่อมต่อ Social ได้ 5 Connects</p>
                            <p class="text-muted mb-3">ฟรี AI 10 Request / ต่อวัน</p>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 10%;" aria-valuenow="1" aria-valuemin="0" aria-valuemax="10">1</div>
                            </div>
                            <a data-tg-order="4" data-tg-tour='เพื่อปลดล็อคความสามารถ สามารถใช้ได้ทุกฟีเจอร์ โนลิมิต' data-tg-title="เพิ่มความสามารถ 🎉" href="javascript: void(0);" class="btn text-primary shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#upgradeYourPlan">อัพเกรด</a>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div><!--end startbar-collapse-->
    </div><!--end startbar-menu-->
    <div class="startbar-overlay d-print-none"></div>
    <!-- end leftbar-tab-menu-->
    <div class="page-wrapper">