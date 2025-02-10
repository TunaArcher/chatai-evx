<style>
    .selectr-options-container {
        position: relative !important;
    }

    .selectr-selected img {
        border-radius: 50%;
        margin-right: 5px;
    }
</style>
<style>
    /* ปุ่ม Gradient Animate */
    .gradient-animate-btn {
        display: inline-block;
        font-weight: bold;
        color: #fff !important;
        /* สีตัวอักษร */
        border: none;
        border-radius: 25px;
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
</style>
<style>
    #form-add-team-preloader {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    #form-edit-team-preloader {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    #preloader {
        width: 50px;
        height: 50px;
    }
</style>
<style>
    /* ไอคอนลบ */
    .btn-close-card {
        position: absolute;
        top: 10px;
        /* ระยะห่างจากด้านบน */
        right: 10px;
        /* ระยะห่างจากด้านขวา */
        color: #777;
        /* สีของไอคอน */
        font-size: 12px;
        /* ขนาดของไอคอน */
        border: none;
        /* ไม่มีเส้นขอบ */
        background: none;
        /* ไม่มีสีพื้นหลัง */
        cursor: pointer;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    /* เอฟเฟกต์กดปุ่ม */
    .btn-close-card:active {
        transform: scale(0.95);
    }
</style>
<div class="page-content">
    <div class="container-fluid">

        <?php if (session()->get('user_owner_id') == '') { ?>
            <div class="row my-3">
                <div class="col-12">
                    <div class="">
                        <div class="card-body">
                            <div class="d-block d-md-flex justify-content-between align-items-center ">
                                <div class="d-flex align-self-center mb-2 mb-md-0">
                                    <div class="img-group d-inline-flex justify-content-center">
                                        <?php if ($members) { ?>
                                            <style>
                                                .user-avatar {
                                                    position: relative;
                                                }

                                                .camera-icon {
                                                    bottom: 5px;
                                                    right: 5px;
                                                    width: 12px;
                                                    height: 12px;
                                                    background-color: rgba(255, 199, 40, .8) !important;
                                                    /* สีพื้นหลังเขียว */
                                                    border-radius: 50%;
                                                    /* ให้เป็นวงกลม */
                                                    display: flex;
                                                    justify-content: center;
                                                    align-items: center;
                                                    position: absolute;
                                                    transform: translate(50%, 50%);
                                                    color: #fff;
                                                    /* สีไอคอน */
                                                    font-size: 12px;
                                                    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
                                                }
                                            </style>
                                            <?php foreach ($members as $key => $member) { ?>
                                                <a class="user-avatar position-relative d-inline-block <?php if ($key > 0) {
                                                                                                            echo 'ms-n2';
                                                                                                        } ?>" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo "$member->email $member->status"; ?>">
                                                    <img src="<?php echo $member->picture; ?>" alt="avatar" class="thumb-md shadow-sm rounded-circle">
                                                    <?php if ($member->status !== '') { ?>
                                                        <span class="camera-icon position-absolute">
                                                            <i class="iconoir-warning-circle"></i>
                                                        </span>
                                                    <?php } ?>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (count($members) > 5) { ?>
                                            <a href="#" class="user-avatar position-relative d-inline-block ms-1">
                                                <span class="thumb-md shadow-sm justify-content-center d-flex align-items-center bg-info-subtle rounded-circle fw-semibold fs-6">+ <?php echo count($members) - 5; ?></span>
                                            </a>
                                        <?php } ?>
                                    </div>
                                    <button type="button" class="gradient-animate-btn btn card-bg text-primary shadow-sm ms-2" data-bs-toggle="modal" data-bs-target="#inviteToTeamMember"><i class="fa-solid fa-plus me-1"></i> เพิ่มสมาชิก</button>
                                </div>
                                <div class="align-self-center">
                                    <form class="row g-2">
                                        <!-- <div class="col-auto">
                                                    <label for="inputsearch" class="visually-hidden">Search</label>
                                                    <input type="search" class="form-control" id="inputsearch" placeholder="Search">
                                                </div> -->
                                        <div class="col-auto">
                                            <!-- <a class="btn card-bg text-primary shadow-sm dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" data-bs-auto-close="outside">
                                                        <i class="iconoir-filter-alt"></i> Filter
                                                    </a> -->
                                            <div class="dropdown-menu dropdown-menu-start">
                                                <div class="p-2">
                                                    <div class="form-check mb-2">
                                                        <input type="checkbox" class="form-check-input" checked id="filter-all">
                                                        <label class="form-check-label" for="filter-all">
                                                            All
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input type="checkbox" class="form-check-input" checked id="filter-one">
                                                        <label class="form-check-label" for="filter-one">
                                                            Design
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input type="checkbox" class="form-check-input" checked id="filter-two">
                                                        <label class="form-check-label" for="filter-two">
                                                            UI/UX
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" checked id="filter-three">
                                                        <label class="form-check-label" for="filter-three">
                                                            Backend
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end col-->

                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeam"> <i class="fa-solid fa-plus me-1"></i> สร้างทีม</button>
                                        </div><!--end col-->
                                    </form>
                                </div>
                            </div>
                        </div><!--end card-body-->
                    </div><!--end card-->
                </div> <!-- end col -->
            </div> <!-- end row -->

        <?php } ?>

        <div class="row">

            <?php if ($teams) { ?>
                <?php foreach ($teams as $team) { ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card position-relative">
                            <!-- Icon ลบ -->
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="card-title"><?php echo $team->name; ?></h4>
                                    </div>
                                    <?php if (session()->get('user_owner_id') == '') { ?>
                                        <!--end col-->
                                        <div class="col-auto">
                                            <div class="dropdown">
                                                <a href="#" class="btn bt btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="icofont-settings fs-5 me-1"></i>
                                                    จัดการทีม<i class="las la-angle-down ms-1"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <button class="dropdown-item btn-edit-team" data-team-id="<?php echo hashidsEncrypt($team->id); ?>" data-bs-toggle="modal" data-bs-target="#editTeam"><i class="iconoir-community fs-14 me-1"></i> จัดการทีม</button>
                                                    <a class="dropdown-item btnRemoveTeam" data-team-id="<?php echo hashidsEncrypt($team->id); ?>"><i class="iconoir-trash fs-14 me-1"></i> ลบ</a>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end col-->
                                    <?php } ?>
                                </div>
                                <!--end row-->
                            </div>
                            <div class="card-body pt-0">
                                <div class="text-center border-dashed-bottom pb-3">
                                    <img src="<?php echo $team->icon; ?>" alt="" height="80" class="rounded-circle d-inline-block">
                                    <h5 class="m-0 fw-bold mt-2 fs-20"><?php echo $team->name; ?></h5>
                                    <p class="text-muted mb-0"><?php echo $team->note; ?></p>
                                    <div class="img-group d-flex justify-content-center mt-3">
                                        <?php if ($team->socials) { ?>
                                            <?php foreach ($team->socials as $social) { ?>
                                                <?php if ($social->platform == 'Facebook') { ?>
                                                    <div class="position-relative">
                                                        <a href="<?php echo base_url('/setting/connect'); ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo "$social->platform | $social->name"; ?>">
                                                            <img src="<?php echo $social->src; ?>" alt="Profile Picture" class="thumb-md rounded-circle">
                                                            <img src="<?php echo base_url('assets/images/' . getPlatformIcon($social->platform)); ?>" alt="profile-platform Icon" class="profile-platform-icon">
                                                        </a>
                                                    </div>
                                                <?php } else { ?>
                                                    <a class="user-avatar position-relative d-inline-block" href="<?php echo base_url('/setting/connect'); ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo "$social->platform | $social->name"; ?>">
                                                        <img src="assets/images/<?php echo getPlatformIcon($social->platform); ?>" alt="avatar" class="thumb-md shadow-sm rounded-circle">
                                                    </a>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>

                                </div>
                                <div class="row mt-3 align-items-center">
                                    <div class="col-auto col-md-6">
                                        <?php
                                        $leader = array_slice($team->members, 0, 1);
                                        $others = array_slice($team->members, 1);
                                        ?>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $leader[0]->picture; ?>" class="me-2 align-self-center thumb-lg rounded" alt="...">
                                            <div class="flex-grow-1 text-truncate">
                                                <h6 class="m-0 text-truncate fs-14 fw-bold"><?php echo $leader[0]->email; ?></h6>
                                                <p class="font-12 mb-0 text-muted">Team Leader</p>
                                            </div><!--end media body-->
                                        </div>
                                    </div>
                                    <div class="col col-md-6 text-end align-self-center">
                                        <div class="img-group d-flex justify-content-center">

                                            <?php if ($team->members) { ?>
                                                <?php foreach ($others as $member) { ?>
                                                    <a class="user-avatar position-relative d-inline-block ms-n2" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo "$member->email"; ?>">
                                                        <img src="<?php echo $member->picture; ?>" alt="avatar" class="thumb-md shadow-sm rounded-circle">
                                                    </a>
                                                <?php } ?>
                                            <?php } ?>

                                            <?php if ($team->members) { ?>
                                                <?php if (count($others) >= 3) { ?>
                                                    <a href="" class="user-avatar position-relative d-inline-block ms-n1">
                                                        <span class="thumb-md shadow-sm justify-content-center d-flex align-items-center bg-info-subtle rounded-circle fw-semibold fs-6">+<?php echo (count($others) - 3); ?></span>
                                                    </a>
                                                <?php } ?>
                                            <?php } ?>

                                        </div>
                                    </div><!--end col-->
                                </div> <!--end row-->
                                <!-- <div class="mt-3 text-center">
                                    <button data-team-id="<?php echo hashidsEncrypt($team->id); ?>" class="btn-edit-team btn btn-outline-primary px-2 d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#editTeam"><i class="iconoir-community fs-14 me-1"></i>จัดการทีม</button>
                                </div> -->
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div>
                <?php } ?>
            <?php } ?>
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

    <?php if (session()->get('user_owner_id') == '') { ?>

        <div class="modal fade" id="inviteToTeamMember" tabindex="-1" role="dialog" aria-labelledby="inviteToTeamMember" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title m-0">เพิ่มสมาชิก</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!--end modal-header-->
                    <div class="modal-body">
                        <div class="row">
                            <label for="" class="col-sm-3 col-form-label text-end fw-medium">Email :</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="emailInput" placeholder="ระบุ Email บุคคลที่คุณต้องการเชิญเข้าเป็นสมาชิก">
                            </div><!--end col-->
                        </div><!--end row-->
                    </div><!--end modal-body-->
                    <div class="modal-footer">
                        <button id="btnSendInviteToTeamMember" type="button" class="btn btn-primary w-100">ยืนยัน</button>
                    </div><!--end modal-footer-->
                </div><!--end modal-content-->
            </div><!--end modal-dialog-->
        </div><!--end modal-->

        <div class="modal fade" id="addTeam" tabindex="-1" role="dialog" aria-labelledby="addTeam" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title m-0">สร้างทีม</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!--end modal-header-->
                    <div class="modal-body">
                        <div id="form-add-team">
                            <div class="text-center">
                                <img id="teamLogo" src="assets/images/logos/lang-logo/reactjs.png" alt="" height="160" class="rounded-circle d-inline-block">
                            </div>
                            <div class="text-center">
                                <button type="button" class="btn btn-secondary btn-sm mt-2" id="randomLogoButton">สุ่มรูป</button>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">ชื่อทีม *</label>
                                <input type="text" class="form-control" id="" placeholder="ชื่อทีม *">
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Note</label>
                                <input type="text" class="form-control" id="" placeholder="Note">
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="" class="form-label">Connect *</label>
                                <select id="multiSelectSocial" class="selectr">
                                    <?php if ($userSocials) { ?>
                                        <?php foreach ($userSocials as $social): ?>
                                            <option value="<?= htmlspecialchars($social->id) ?>" data-image="<?php echo base_url('assets/images/' . getPlatformIcon($social->platform)); ?>">
                                                <?= htmlspecialchars($social->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php } else { ?>
                                        <option disabled>--- คุณยังไม่มี Connect กรุณาไปเพิ่ม ---</option>
                                    <?php } ?>
                                </select>
                            </div> <!-- end col -->

                            <div class="mb-3">
                                <label for="" class="form-label">สมาชิก *</label>
                                <select id="multiSelectMember" class="selectr">
                                    <?php if ($members) { ?>
                                        <?php foreach ($members as $member): ?>
                                            <?php if ($member->accept_invite == 'waiting') { ?>
                                                <option disabled data-image="<?= htmlspecialchars($member->picture) ?>">
                                                    <?= htmlspecialchars($member->email) ?> (รอการตอบรับ)
                                                </option>
                                            <?php } else { ?>
                                                <option value="<?= htmlspecialchars($member->id) ?>" data-image="<?= htmlspecialchars($member->picture) ?>">
                                                    <?= htmlspecialchars($member->email) ?>
                                                </option>
                                            <?php } ?>
                                        <?php endforeach; ?>
                                    <?php } else { ?>
                                        <option disabled>--- คุณยังไม่มีสมาชิก ---</option>
                                    <?php } ?>
                                </select>
                            </div> <!-- end col -->
                        </div>
                        <div id="wrapper-form-add-team-preloader" class="" style="display: none;">
                            <div id="form-add-team-preloader" style="display: flex; justify-content: center; align-items: center; height: 200px;">
                                <div id="preloader" class="spinner-grow thumb-md text-secondary" role="status"></div>
                            </div>
                        </div>
                    </div><!--end modal-body-->
                    <div class="modal-footer">
                        <button id="btnSaveTeam" type="button" class="btn btn-primary w-100">ยืนยัน</button>
                    </div><!--end modal-footer-->
                </div><!--end modal-content-->
            </div><!--end modal-dialog-->
        </div><!--end modal-->

        <div class="modal fade" id="editTeam" tabindex="-1" role="dialog" aria-labelledby="editTeam" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title m-0">แก้ไขทีม</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!--end modal-header-->
                    <div class="modal-body">
                        <div id="form-edit-team">

                            <div class="text-center">
                                <img id="editTeamLogo" src="" alt="Team Logo" height="160" class="rounded-circle d-inline-block">
                            </div>
                            <!-- <div class="text-center">
                            <button type="button" class="btn btn-secondary btn-sm mt-2" id="editRandomLogoButton">สุ่มรูป</button>
                        </div> -->
                            <div class="mb-3">
                                <label for="editTeamName" class="form-label">ชื่อทีม *</label>
                                <input type="text" class="form-control" id="editTeamName" placeholder="ชื่อทีม *" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="editTeamNote" class="form-label">Note</label>
                                <input type="text" class="form-control" id="editTeamNote" placeholder="Note">
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="editMultiSelectSocial" class="form-label">Connect *</label>
                                <select id="editMultiSelectSocial" class="selectr">
                                    <?php if ($userSocials) { ?>
                                        <?php foreach ($userSocials as $social): ?>
                                            <option value="<?= htmlspecialchars($social->id) ?>"
                                                data-image="<?php echo base_url('assets/images/' . getPlatformIcon($social->platform)); ?>">
                                                <?= htmlspecialchars($social->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php } else { ?>
                                        <option disabled>--- คุณยังไม่มี Connect กรุณาไปเพิ่ม ---</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editMultiSelectMember" class="form-label">สมาชิก *</label>
                                <select id="editMultiSelectMember" class="selectr">
                                    <?php if ($members) { ?>
                                        <?php foreach ($members as $member): ?>
                                            <option value="<?= htmlspecialchars($member->id) ?>"
                                                data-image="<?= htmlspecialchars($member->picture) ?>">
                                                <?= htmlspecialchars($member->email) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php } else { ?>
                                        <option disabled>--- คุณยังไม่มีสมาชิก ---</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div id="wrapper-form-edit-team-preloader" class="" style="display: none;">
                            <div id="form-edit-team-preloader" style="display: flex; justify-content: center; align-items: center; height: 200px;">
                                <div id="preloader" class="spinner-grow thumb-md text-secondary" role="status"></div>
                            </div>
                        </div>
                    </div><!--end modal-body-->
                    <div class="modal-footer">
                        <input type="hidden" id="editTeamID">
                        <button id="btnUpdateTeam" type="button" class="btn btn-primary w-100">อัพเดท</button>
                    </div><!--end modal-footer-->
                </div><!--end modal-content-->
            </div><!--end modal-dialog-->
        </div><!--end modal-->

    <?php } ?>

    <?php echo view('/partials/copyright'); ?>
</div>
<!-- end page content -->