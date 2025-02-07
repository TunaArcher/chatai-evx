<!-- Page Content-->
<div class="page-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 align-self-center mb-3 mb-lg-0">
                                <div class="d-flex align-items-center flex-row flex-wrap">
                                    <div class="position-relative me-3">
                                        <img src="<?php echo session()->get('thumbnail') ?: base_url('/assets/images/conX.png'); ?>" alt="" height="120" class="rounded-circle">
                                        <a href="#" class="thumb-md justify-content-center d-flex align-items-center bg-primary text-white rounded-circle position-absolute end-0 bottom-0 border border-3 border-card-bg">
                                            <img src="<?php echo base_url('/assets/images/' . getPlatformIcon(session()->get('platform'))); ?>" alt="profile-platform Icon" width="100%">
                                        </a>
                                    </div>
                                    <div class="">
                                        <h5 class="fw-semibold fs-22 mb-1"><?php echo session()->get('name'); ?></h5>
                                        <?php if (session()->get('subscription_status') == 'active') { ?>
                                            <span class="badge rounded-pill bg-info-subtle text-info"><img style="margin-bottom: 2px;" width="14" src="https://cdn-icons-png.flaticon.com/512/5524/5524802.png" alt=""> อัพเกรดแล้ว</span>
                                        <?php } else { ?>
                                            <?php if (session()->get('user_owner_id') == '') { ?>
                                                <span class="badge rounded-pill bg-dark-subtle text-dark">Free</span>
                                            <?php } else if (session()->get('user_owner_id') != '') { ?>
                                                <span class="badge rounded-pill bg-warning-subtle text-warning">Team</span>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div><!--end col-->
                            <?php if (session()->get('user_owner_id') == '') { ?>
                                <div class="col-lg-6 ms-auto align-self-center">
                                    <div class="d-flex justify-content-center">
                                        <?php if (isset($userSocials)) { ?>
                                            <div class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                <h5 class="fw-semibold fs-22 mb-1"><?php echo count($userSocials); ?></h5>
                                                <p class="text-muted mb-0 fw-medium">การเชื่อมต่อ</p>
                                            </div>
                                        <?php } ?>
                                        <?php if (isset($userSocials)) { ?>
                                            <div class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                <h5 class="fw-semibold fs-22 mb-1"><?php echo $counterMessages['all']; ?></h5>
                                                <p class="text-muted mb-0 fw-medium">Message ทั้งหมด</p>
                                            </div>
                                        <?php } ?>
                                        <?php if (isset($userSocials)) { ?>
                                            <div class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                <h5 class="fw-semibold fs-22 mb-1"><?php echo $counterMessages['reply_by_manual']; ?></h5>
                                                <p class="text-muted mb-0 fw-medium">ตอบด้วยตนเอง</p>
                                            </div>
                                        <?php } ?>
                                        <?php if (isset($userSocials)) { ?>
                                            <div class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                <h5 class="fw-semibold fs-22 mb-1"><?php echo $counterMessages['replay_by_ai']; ?></h5>
                                                <p class="text-muted mb-0 fw-medium">ตอบโดย AI</p>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div><!--end col-->
                                <div class="col-lg-2 align-self-center">
                                    <div class="row row-cols-2">
                                        <div class="col text-end">
                                            <div id="complete" class="apex-charts"></div>
                                        </div>
                                        <div class="col align-self-center">
                                            <button type="button" class="btn btn-primary d-inline-block" onclick="alert('development')">ผูกบัญชี</button>
                                            <!-- <button type="button" class="btn btn-light  d-inline-block">Development</button> -->
                                        </div>
                                    </div>
                                </div><!--end col-->
                            <?php } ?>
                        </div><!--end row-->
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->

        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Personal Information</h4>
                            </div><!--end col-->
                            <div class="col-auto">
                                <a href="#" class="float-end text-muted d-inline-flex text-decoration-underline" onclick="alert('development')"><i class="iconoir-edit-pencil fs-18 me-1"></i>Edit</a>
                            </div><!--end col-->
                        </div> <!--end row-->
                    </div><!--end card-header-->
                    <div class="card-body pt-0">
                        <p class="text-muted fw-medium mb-3">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nam ipsam quas, exercitationem sint iste temporibus enim ex quam delectus aliquam totam eum commodi sunt iusto nobis iure hic repellat. Reprehenderit.</p>
                        <div class="mb-3">
                            <?php if (isset($teams)) { ?>
                                <?php foreach ($teams as $team) { ?>
                                    <span class="badge bg-transparent border border-light text-gray-700 fs-12 fw-medium mb-1">ทีม <img width="20" src="<?php echo base_url($team->icon); ?>"><?php echo $team->name; ?></span>
                                <?php } ?>
                            <?php } ?>
                        </div>
                        <?php if ($subscription) { ?>
                            <?php if ($subscription->status == 'active') { ?>
                                <div class="p-3  border-info border-dashed bg-info-subtle  mt-3 rounded">
                                    <div class="row d-flex justify-content-center">
                                        <div class="col">
                                            <div class=" ">
                                                <a href="#" class="fw-bold me-1 text-info">Active Subscription</a>
                                                <hr>
                                                <?php echo $subscription->name; ?>
                                                <?php if ($subscription->cancel_at_period_end) { ?>
                                                    <p class="text-warning btnHandlePlan">คุณจะยังสามารถใช้งานได้จนหมดอายุ</p>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php if (session()->get('user_owner_id') == '') { ?>
                                            <div class="col-auto align-self-center text-center">
                                                <p class="text-muted fw-semibold fs-13">หมดอายุ <?php echo date('Y-m-d H:i', $subscription->current_period_end); ?></p>
                                                <?php if ($subscription->cancel_at_period_end) { ?>
                                                    <button class="btn btn-light text-right btn-sm btnHandlePlan">ยกเลิกแล้ว</button>
                                                <?php } else { ?>
                                                    <button class="btn btn-light text-right btn-sm btnHandlePlan">ยกเลิก Subscription</button>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="p-3  border-warning border-dashed bg-warning-subtle  mt-3 rounded">
                                    <div class="row d-flex justify-content-center">
                                        <div class="col">
                                            <div class=" ">
                                                <a href="#" class="fw-bold me-1 text-warning">Active Subscription (ชำระเงินไม่สำเร็จ)</a>
                                                <hr>
                                                <?php echo $subscription->name; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <span class="badge rounded text-warning bg-transparent border border-warning mb-2 p-1"><?php echo strtoupper($subscription->status); ?></span>
                                            <p class="text-muted fw-semibold fs-13">หมดอายุ <?php echo date('Y-m-d H:i', $subscription->current_period_end); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if (session()->get('user_owner_id') == '') { ?>
                                <div class="p-3  border-dark border-dashed bg-dark-subtle  mt-3 rounded">
                                    <div class="row d-flex justify-content-center">
                                        <div class="col">
                                            <div class=" ">
                                                <a href="#" class="fw-bold me-1 text-muted">No Active Subscription</a>
                                                <hr>
                                                อัพเกรดเพื่อเข้าถึงคุณสมบัติเพิ่มเติม
                                            </div>
                                        </div><!--end col-->

                                    </div><!--end row-->
                                </div>
                            <?php } ?>
                        <?php } ?>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
            <div class="col-md-8">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <!-- <li class="nav-item">
                        <a class="nav-link fw-medium active" data-bs-toggle="tab" href="#post" role="tab" aria-selected="true">Bills</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link fw-medium active" data-bs-toggle="tab" href="#security" role="tab" aria-selected="false">ตั้งค่า Security</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" data-bs-toggle="tab" href="#security" role="tab" aria-selected="false">ตั้งค่า อื่น ๆ</a>
                    </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <!-- <div class="tab-pane active" id="post" role="tabpanel">
                        Bills
                    </div> -->
                    <div class="tab-pane active" id="security" role="tabpanel">
                        <div class="container">In development ....</div>
                    </div>
                </div>
            </div> <!--end col-->
        </div><!--end row-->


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

    <?php echo view('/partials/copyright'); ?>
</div>
<!-- end page content -->