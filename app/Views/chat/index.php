<style>
    .btn-gradient {
        font-weight: bold;
        color: white;
        text-transform: uppercase;
        padding: 10px 20px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        background: linear-gradient(90deg, #8e44ad, #c0392b, #f39c12, #8e44ad);
        background-size: 300% 300%;
        animation: gradient-animation 3s ease infinite;
        transition: transform 0.2s ease-in-out;
        overflow: hidden;
    }

    .btn-gradient:hover {
        transform: scale(1.05);
    }

    @keyframes gradient-animation {
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
</style>

<!-- Page Content-->
<div class="page-content">
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="chat-box-left">
                    <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link py-2 active" id="messages_chat_tab" data-bs-toggle="tab" href="#messages_chat" role="tab">Messages</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link py-2" id="active_chat_tab" data-bs-toggle="tab" href="#active_chat" role="tab">Active</a>
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

                    <div class="chat-body-left px-3" data-simplebar>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="messages_chat">
                                <div class="row">
                                    <div class="col">

                                        <div id="rooms-list">

                                            <?php foreach ($rooms as $room): ?>
                                                <div class="room-item p-2 border-dashed border-theme-color rounded mb-2" data-room-id="<?php echo $room->id ?>" data-platform="<?php echo $room->platform ?>">
                                                    <a href="#" class="">
                                                        <div class="d-flex align-items-start">
                                                            <div class="position-relative">
                                                                <img src="<?php echo $room->profile; ?>" alt="" class="thumb-lg rounded-circle">
                                                                <span class="position-absolute bottom-0 end-0">
                                                                    <img src="<?php echo base_url('/assets/images/' . $room->ic_platform); ?>" width="14">
                                                                </span>
                                                            </div>
                                                            <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                                                <h6 class="my-0 fw-medium text-dark fs-14"><?php echo $room->customer_name; ?>
                                                                    <small class="float-end text-muted fs-11"><?php if ($room->last_time != '') echo timeElapsed($room->last_time); ?></small>
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

                            <div class="tab-pane fade" id="active_chat">
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
                </div><!--end chat-box-left -->

                <div id="chat-box-right" class="chat-box-right" style="display: none;">
                    <div class="p-3 d-flex justify-content-between align-items-center card-bg rounded">
                        <a href="" class="d-flex align-self-center">
                            <div class="flex-shrink-0">
                                <img id="chat-box-profile" alt="user" class="rounded-circle thumb-lg">
                            </div><!-- media-left -->
                            <div class="flex-grow-1 ms-2 align-self-center">
                                <div>
                                    <h6 class="my-0 fw-medium text-dark fs-14" id="chat-box-username"></h6>
                                </div>
                            </div><!-- end media-body -->
                        </a><!--end media-->
                        <button type="button" class="btn-gradient btn rounded-pill btn-outline-primary btnAI"><a href="<?php echo base_url('/setting'); ?>" class="text-white">คุณกำลังเปิดใช้ฟังก์ชั่นให้ AI ช่วยตอบอยู่</a></button>
                        <div class="d-none d-sm-inline-block align-self-center disabled">
                            <a href="javascript:void(0)" class="fs-22 me-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Call" data-bs-custom-class="tooltip-primary"><i class="iconoir-phone"></i></a>
                            <a href="javascript:void(0)" class="fs-22 me-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Video call" data-bs-custom-class="tooltip-primary"><i class="iconoir-video-camera"></i></a>
                            <a href="javascript:void(0)" class="fs-22 me-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" data-bs-custom-class="tooltip-primary"><i class="iconoir-trash"></i></a>
                            <a href="javascript:void(0)" class="fs-22 text-muted"><i class="iconoir-menu-scale"></i></a>
                        </div>
                    </div><!-- end chat-header -->
                    <div class="chat-body" data-simplebar>
                        <div id="chat-detail" class="chat-detail"></div> <!-- end chat-detail -->
                    </div><!-- end chat-body -->
                    <div class="chat-footer">
                        <div class="row">
                            <div class="col-10 col-md-8">
                                <input type="text" class="form-control" placeholder="Type something here..." id="chat-input">
                            </div><!-- col-8 -->
                            <div class="col-2 col-md-4 text-end">
                                <div class="chat-features">
                                    <div class="d-none d-sm-inline-block disabled">
                                        <a href=""><i class="iconoir-camera"></i></a>
                                        <a href=""><i class="iconoir-attachment"></i></a>
                                        <a href=""><i class="iconoir-microphone"></i></a>
                                    </div>
                                    <a href="#" class="text-primary" id="send-btn"><i class="iconoir-send-solid"></i></a>
                                </div>
                            </div><!-- end col -->
                        </div><!-- end row -->
                    </div><!-- end chat-footer -->
                </div><!--end chat-box-right -->

                <style>
                    .cn {
                        position: relative;
                    }

                    .inner {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        width: 200px;
                        height: 200px;
                    }
                </style>
                <div id="chat-box-emtry" class="chat-box-right cn">
                    <div class="container-xxl">

                        <div class="row justify-content-center inner">
                            <div class="col-12">
                                <div class="">
                                    <div class="card-header text-center">
                                        <h4 class="card-title pt-2 fw-semibold mb-2 fs-18">ไม่มีข้อความจากลูกค้าของคุณในการเชื่อมต่อ</h4>
                                        <p> <i class="la la-grip-lines text-primary fs-18"></i> <i class="la la-question-circle text-primary fs-18"></i> <i class="la la-grip-lines text-primary fs-18"></i></p>
                                    </div><!--end card-header-->
                                </div><!--end card-->
                            </div> <!--end col-->
                        </div><!--end row-->

                    </div>
                </div>

                <div id="chat-box-preloader" class="chat-box-right cn" style="display: none;">
                    <div class="container-xxl">

                        <div class="row justify-content-center inner">
                            <div class="col-12">
                                <div class="" style="height: 710px;">
                                    <div id="preloader" class="spinner-grow thumb-md text-secondary ms-1" role="status"></div>
                                </div><!--end card-->
                            </div> <!--end col-->
                        </div><!--end row-->

                    </div>
                </div>
            </div> <!-- end col -->
        </div><!-- end row -->
    </div><!-- container -->

    <!--Start Rightbar-->
    <!--Start Rightbar/offcanvas-->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="Appearance" aria-labelledby="AppearanceLabel">
        <div class="offcanvas-header border-bottom justify-content-between">
            <h5 class="m-0 font-14" id="AppearanceLabel">Appearance</h5>
            <button type="button" class="btn-close text-reset p-0 m-0 align-self-center" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <h6>Account Settings</h6>
            <div class="p-2 text-start mt-3">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="settings-switch1">
                    <label class="form-check-label" for="settings-switch1">Auto updates</label>
                </div><!--end form-switch-->
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="settings-switch2" checked>
                    <label class="form-check-label" for="settings-switch2">Location Permission</label>
                </div><!--end form-switch-->
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="settings-switch3">
                    <label class="form-check-label" for="settings-switch3">Show offline Contacts</label>
                </div><!--end form-switch-->
            </div><!--end /div-->
            <h6>General Settings</h6>
            <div class="p-2 text-start mt-3">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="settings-switch4">
                    <label class="form-check-label" for="settings-switch4">Show me Online</label>
                </div><!--end form-switch-->
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="settings-switch5" checked>
                    <label class="form-check-label" for="settings-switch5">Status visible to all</label>
                </div><!--end form-switch-->
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="settings-switch6">
                    <label class="form-check-label" for="settings-switch6">Notifications Popup</label>
                </div><!--end form-switch-->
            </div><!--end /div-->
        </div><!--end offcanvas-body-->
    </div>
    <!--end Rightbar/offcanvas-->
    <!--end Rightbar-->
    <!--Start Footer-->

    <!--end footer-->
</div>
<!-- end page content -->