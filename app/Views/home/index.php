<style>
    /* Fade-in Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Card Animation */
    .card-dashboard-animate {
        opacity: 0;
        /* เริ่มต้นเป็นโปร่งใส */
        transform: translateY(20px);
        /* เริ่มต้นขยับลง */
        transition: all 0.3s ease-in-out;
        /* เพิ่ม transition */
    }

    .card-dashboard-animate.visible {
        opacity: 1;
        /* ทำให้การ์ดปรากฏ */
        transform: translateY(0);
        /* การ์ดเลื่อนขึ้น */
    }

    /* Hover Effect for Card */
    .card-dashboard-animate:hover {
        transform: scale(1.05);
        /* การ์ดขยาย */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        /* เพิ่มเงา */
    }

    /* Button Hover Effect */
    button.btn:hover {
        transform: scale(1.1);
        /* ปุ่มขยายเล็กน้อย */
        transition: transform 0.2s ease;
    }
</style>
<style>
    /* Custom Styles for Modal */
    .custom-modal .modal-dialog {
        /* max-width: 900px; */
    }

    .custom-modal .modal-content {
        border: none;
        border-radius: 10px;
        overflow: hidden;
    }

    .custom-modal .modal-body {
        display: flex;
        flex-wrap: wrap;
        padding: 0;
    }

    /* Close Button */
    .custom-modal .close {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 24px;
        color: #6c757d;
        z-index: 10;
        cursor: pointer;
    }

    /* Left Section */
    .custom-modal .left-section {
        flex: 1;
        padding: 30px;
        /* background-color: #f8f9fa; */
    }

    .custom-modal .left-section h4 {
        font-weight: bold;
    }

    .custom-modal .left-section p {
        font-size: 16px;
        color: #6c757d;
    }

    .custom-modal .left-section .business-value {
        margin-top: 20px;
        font-weight: bold;
    }

    .custom-modal .left-section .steps {
        margin-top: 15px;
        list-style: none;
        padding: 0;
    }

    .custom-modal .left-section .steps li {
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
    }

    .custom-modal .left-section .steps li span {
        font-size: 16px;
        font-weight: bold;
        color: #007bff;
        margin-right: 10px;
    }

    .custom-modal .left-section .btn-primary {
        margin-top: 20px;
        font-weight: bold;
    }

    /* Right Section (Phone Mockup with Grid Background) */
    .custom-modal .right-section {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #e9ecef;
        background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
        background-size: 20px 20px;
        /* Adjust grid size */
        padding: 20px;
    }

    .custom-modal .phone-mockup {
        background: #212529;
        border-radius: 20px;
        width: 300px;
        height: 600px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        padding: 20px;
    }

    .custom-modal .phone-mockup::before {
        content: "";
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        background: #212529;
        width: 120px;
        height: 10px;
        border-radius: 10px;
    }

    .custom-modal .chat-bubble {
        background: #343a40;
        color: #fff;
        border-radius: 10px;
        padding: 15px;
        font-size: 16px;
        max-width: 80%;
        text-align: left;
        margin-bottom: 15px;
    }

    .custom-modal .chat-bubble span {
        color: #007bff;
    }

    .custom-modal .chat-options {
        display: flex;
        justify-content: space-between;
        width: 100%;
        margin-top: 10px;
    }

    .custom-modal .chat-options button {
        flex: 1;
        background: #007bff;
        color: #fff;
        border: none;
        padding: 10px;
        margin: 0 5px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: bold;
    }

    .steps>li {
        font-size: 16px;
    }
</style>
<style>
    .get-started-scene {
        transform: translate3d(0, 0, 0);
        -webkit-transform: translate3d(0, 0, 0);
        -moz-transform: translate3d(0, 0, 0);
        -ms-transform: translate3d(0, 0, 0);
        -o-transform: translate3d(0, 0, 0);
        /* position: absolute;
        bottom: -101px;
        left: 50%;
        margin-left: -140px; */
    }

    .get-started-scene .scene-01 {
        position: absolute;
        left: 118px;
        /* right: 117px; */
        /* top: 33px; */
        bottom: 139px;
        overflow: hidden;
    }

    @media (max-width: 767px) {
        .get-started-scene {
            transform: translate3d(0, 0, 0) scale(0.35);
            -webkit-transform: translate3d(0, 0, 0) scale(0.35);
            -moz-transform: translate3d(0, 0, 0) scale(0.35);
            -ms-transform: translate3d(0, 0, 0) scale(0.35);
            -o-transform: translate3d(0, 0, 0) scale(0.35);
            transform-origin: bottom center;
            -webkit-transform-origin: bottom center;
            -moz-transform-origin: bottom center;
            -ms-transform-origin: bottom center;
            -o-transform-origin: bottom center;
            bottom: -48px;
        }
    }

    @media (max-width: 991px) {
        .get-started-scene {
            transform-origin: center;
            -webkit-transform-origin: center;
            -moz-transform-origin: center;
            -ms-transform-origin: center;
            -o-transform-origin: center;
            /* margin-left: -400px; */
        }
    }

    @media (max-width: 1239px) {
        .get-started-scene {
            /* margin-left: -140px; */
            transform: translate3d(0, 0, 0) scale(0.8);
            -webkit-transform: translate3d(0, 0, 0) scale(0.8);
            -moz-transform: translate3d(0, 0, 0) scale(0.8);
            -ms-transform: translate3d(0, 0, 0) scale(0.8);
            -o-transform: translate3d(0, 0, 0) scale(0.8);
            transform-origin: left;
            -webkit-transform-origin: left;
            -moz-transform-origin: left;
            -ms-transform-origin: left;
            -o-transform-origin: left;
        }
    }
</style>
<!-- Page Content-->
<div class="page-content">
    <div class="container">
        <?php if (session()->get('user_owner_id') == '') { ?>

            <?php echo view('/partials/home/laytous_top_single'); ?>

        <?php } else { ?>

            <?php echo view('/partials/home/laytous_top_team'); ?>

        <?php } ?>

        <div class="row my-5">
            <h1 id="typed-text-container" data-username="<?php echo session()->get('name'); ?>">
                <span id="typed-text"></span><span class="typed-cursor"></span>
            </h1>
            <p>
                <?php if (isset($userSocials)) { ?>
                    <?php echo count($userSocials); ?> การเชื่อมต่อ ⚡
                <?php } ?>
                <?php if (isset($counterMessages)) { ?>
                    <?php echo $counterMessages['all']; ?> ข้อความ 📑
                <?php } ?>
                <a href="<?php echo base_url('/chat'); ?>"><u>See Insights</u></a>
            </p>
        </div>

        <?php if (session()->get('user_owner_id') == '') { ?>
            <div class="row my-2 justify-content-between align-items-center">
                <div class="col-auto text-left">
                    <h2>เริ่มต้น</h2>
                </div>
                <div class="col-auto text-right"><a href="#" onclick="alert('in develop')">Explore all Templates</a></div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4" data-bs-toggle="modal" data-bs-target="#startModal1">
                    <div class="card card-dashboard-animate">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center border-dashed-bottom pb-3">
                                <div class="col-9">
                                    <h4 class="mb-2 mb-0 fw-bold">1. สร้างการเชื่อมต่อ</h4>
                                    <p class="text-muted mb-0 fw-semibold fs-14">เชื่อมต่อแพลตฟอร์มยอดนิยม เช่น Line, Facebook, Instagram, WhatsApp ได้ในเวลาไม่ถึง 1 นาที และสามารถใช้งานระบบได้ทันที! 🚀 </p>
                                </div>
                                <!--end col-->
                                <div class="col-3 align-self-center">
                                    <div class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                        <i class="iconoir-hexagon-dice h1 align-self-center mb-0 text-secondary"></i>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                            <div class="row mt-3 align-items-center">
                                <!-- ⚙️ Flow Builder ด้านซ้าย -->
                                <div class="col text-start">
                                    <p class="mb-0 text-truncate text-muted"><a href="#">⚙️ Flow Builder</a></p>
                                </div>

                                <!-- ปุ่ม ด้านขวา -->
                                <!-- <div class="col-auto text-end">
                                    <button type="button" class="btn btn-dark btn-sm">
                                        AI
                                    </button>
                                </div> -->
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->
                <div class="col-md-6 col-lg-4" data-bs-toggle="modal" data-bs-target="#startModal2">
                    <div class="card card-dashboard-animate">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center border-dashed-bottom pb-3">
                                <div class="col-9">
                                    <h4 class="mb-2 mb-0 fw-bold">2. แชทจากทุกแพลตฟอร์มถูกรวมไว้ที่เดียว</h4>
                                    <p class="text-muted mb-0 fw-semibold fs-14">แชทจากแพลตฟอร์มต่าง ๆ ถูกรวมไว้ที่นี่ ทำให้สามารถจัดการได้ง่าย !</p>
                                </div>
                                <!--end col-->
                                <div class="col-3 align-self-center">
                                    <div class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                        <i class="iconoir-percentage-circle h1 align-self-center mb-0 text-secondary"></i>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                            <div class="row mt-3 align-items-center">
                                <!-- ⚙️ Flow Builder ด้านซ้าย -->
                                <div class="col text-start">
                                    <p class="mb-0 text-truncate text-muted"><a href="#">⚙️ Flow Builder</a></p>
                                </div>

                                <!-- ปุ่ม ด้านขวา -->
                                <!-- <div class="col-auto text-end">
                                    <button type="button" class="btn btn-dark btn-sm">
                                        AI
                                    </button>
                                </div> -->
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->
                <div class="col-md-6 col-lg-4" data-bs-toggle="modal" data-bs-target="#startModal3">
                    <div class="card card-dashboard-animate">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center border-dashed-bottom pb-3">
                                <div class="col-9">
                                    <h4 class="mb-2 mb-0 fw-bold">Automate conversations with AI</h4>
                                    <p class="text-muted mb-0 fw-semibold fs-14">เพิ่มยอดขายผ่านการส่งโปรโมชั่น และลดเวลาในการทำงานด้วยระบบแชท AI ที่ทำงานตลอด 24/7</p>
                                </div>
                                <!--end col-->
                                <div class="col-3 align-self-center">
                                    <div class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">

                                        <i class="iconoir-clock h1 align-self-center mb-0 text-secondary"></i>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                            <!-- Row ใหม่สำหรับ ⚙️ Flow Builder และปุ่ม OK -->
                            <div class="row mt-3 align-items-center">
                                <!-- ⚙️ Flow Builder ด้านซ้าย -->
                                <div class="col text-start">
                                    <p class="mb-0 text-truncate text-muted"><a href="#">⚙️ Flow Builder</a></p>
                                </div>

                                <!-- ปุ่ม OK ด้านขวา -->
                                <div class="col-auto text-end">
                                    <button type="button" class="btn btn-dark btn-sm py-0">
                                        AI
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->
            </div>
        <?php } ?>

    </div>

    <?php if (session()->get('user_owner_id') == '') { ?>

        <div class="modal fade custom-modal" id="startModal1" tabindex="-1" role="dialog" aria-labelledby="startModal1Label" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <!-- Close Button -->
                    <span class="close" data-bs-dismiss="modal" aria-label="Close">&times;</span>
                    <div class="modal-body">
                        <!-- Left Section -->
                        <div class="left-section">
                            <h4>สร้างการเชื่อมต่อ</h4>
                            <p>เชื่อมต่อแพลตฟอร์มยอดนิยม เช่น Line, Facebook, Instagram, WhatsApp ได้ในเวลาไม่ถึง 1 นาที และสามารถใช้งานระบบได้ทันที! 🚀</p>
                            <p class="business-value">เมนู Setting > Connect</p>
                            <p>ขั้นตอน</p>
                            <ul class="steps">
                                <li><span>1.</span> คลิกปุ่ม เพิ่มการเชื่อมต่อ</li>
                                <li><span>2.</span> เลือกแพลตฟอร์ม</li>
                                <li><span>3.</span> ทำตามคำแนะนำ</li>
                                <li><span>4.</span> ใช้งานระบบได้ทันที!</li>
                            </ul>
                            <a href="<?php echo base_url('/setting/connect'); ?>" class="btn btn-primary btn-block">Go Set Up</a>
                        </div>

                        <!-- Right Section -->
                        <div class="right-section">
                            <!-- <div class="phone-mockup">
                            <div class="chat-bubble">Let's start with an easy one: which wine would you pick for fish: <span>red</span> or <span>white</span>?</div>
                            <div class="chat-options">
                                <button>Red 🍷</button>
                                <button>White ❤️</button>
                            </div>
                        </div> -->

                            <div class="get-started-scene">
                                <picture class="top-new-macbook">
                                    <source srcset="<?php echo base_url('assets/images/top-new-macbook-xs.png'); ?> 1x, <?php echo base_url('assets/images/top-new-macbook-xs@2x.png'); ?> 2x" media="(max-width: 767px)">
                                    <source srcset="<?php echo base_url('assets/images/top-new-macbook.png'); ?> 1x, <?php echo base_url('assets/images/top-new-macbook@2x.png'); ?> 2x" media="(min-width: 768px)">
                                    <img src="<?php echo base_url('/assets/images/top-new-macbook-xs@2x.png'); ?>" width="806" height="534">
                                </picture>
                                <div class="scene-01 play">
                                    <picture>
                                        <!-- <source srcset="<?php echo base_url('assets/images/screen-hero-1-xs.jpg'); ?> 1x, <?php echo base_url('assets/images/screen-hero-1-xs@2x.jpg'); ?> 2x" media="(max-width: 767px)">
                                    <source srcset="<?php echo base_url('assets/images/screen-hero-1.jpg'); ?> 1x, <?php echo base_url('assets/images/screen-hero-1@2x.jpg'); ?> 2x" media="(min-width: 768px)">
                                    <img src="<?php echo base_url('assets/images/screen-hero-1-xs@2x.jpg'); ?>" width="571" height="367"> -->

                                        <source srcset="<?php echo base_url('assets/images/start1.gif'); ?> 1x, <?php echo base_url('assets/images/start1.gif'); ?> 2x" media="(max-width: 767px)">
                                        <source srcset="<?php echo base_url('assets/images/start1.gif'); ?> 1x, <?php echo base_url('assets/images/start1.gif'); ?> 2x" media="(min-width: 768px)">
                                        <img src="<?php echo base_url('assets/images/start1.gif'); ?>" width="571" height="367">
                                    </picture>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade custom-modal" id="startModal2" tabindex="-1" role="dialog" aria-labelledby="startModal2Label" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <!-- Close Button -->
                    <span class="close" data-bs-dismiss="modal" aria-label="Close">&times;</span>
                    <div class="modal-body">
                        <!-- Left Section -->
                        <div class="left-section">
                            <h4>แชทจากทุกแพลตฟอร์มถูกรวมไว้ที่เดียว</h4>
                            <p>แชทจากแพลตฟอร์มต่าง ๆ ถูกรวมไว้ที่นี่ ทำให้สามารถจัดการได้ง่าย !</p>
                            <p class="business-value">เมนู Chat</p>
                            <p>แชทจากแพลตฟอร์มต่าง ๆ ถูกรวมไว้ที่นี่ ทำให้สามารถจัดการได้ง่าย !</p>
                            <a href="<?php echo base_url('/setting/connect'); ?>" class="btn btn-primary btn-block">Go Set Up</a>
                        </div>

                        <!-- Right Section -->
                        <div class="right-section">
                            <!-- <div class="phone-mockup">
                            <div class="chat-bubble">Let's start with an easy one: which wine would you pick for fish: <span>red</span> or <span>white</span>?</div>
                            <div class="chat-options">
                                <button>Red 🍷</button>
                                <button>White ❤️</button>
                            </div>
                        </div> -->

                            <div class="get-started-scene">
                                <picture class="top-new-macbook">
                                    <source srcset="<?php echo base_url('assets/images/top-new-macbook-xs.png'); ?> 1x, <?php echo base_url('assets/images/top-new-macbook-xs@2x.png'); ?> 2x" media="(max-width: 767px)">
                                    <source srcset="<?php echo base_url('assets/images/top-new-macbook.png'); ?> 1x, <?php echo base_url('assets/images/top-new-macbook@2x.png'); ?> 2x" media="(min-width: 768px)">
                                    <img src="<?php echo base_url('/assets/images/top-new-macbook-xs@2x.png'); ?>" width="806" height="534">
                                </picture>
                                <div class="scene-01 play">
                                    <picture>
                                        <!-- <source srcset="<?php echo base_url('assets/images/screen-hero-1-xs.jpg'); ?> 1x, <?php echo base_url('assets/images/screen-hero-1-xs@2x.jpg'); ?> 2x" media="(max-width: 767px)">
                                    <source srcset="<?php echo base_url('assets/images/screen-hero-1.jpg'); ?> 1x, <?php echo base_url('assets/images/screen-hero-1@2x.jpg'); ?> 2x" media="(min-width: 768px)">
                                    <img src="<?php echo base_url('assets/images/screen-hero-1-xs@2x.jpg'); ?>" width="571" height="367"> -->

                                        <source srcset="<?php echo base_url('assets/images/start2.gif'); ?> 1x, <?php echo base_url('assets/images/start1.gif'); ?> 2x" media="(max-width: 767px)">
                                        <source srcset="<?php echo base_url('assets/images/start2.gif'); ?> 1x, <?php echo base_url('assets/images/start1.gif'); ?> 2x" media="(min-width: 768px)">
                                        <img src="<?php echo base_url('assets/images/start2.gif'); ?>" width="571" height="367">
                                    </picture>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade custom-modal" id="startModal3" tabindex="-1" role="dialog" aria-labelledby="startModal3Label" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <!-- Close Button -->
                    <span class="close" data-bs-dismiss="modal" aria-label="Close">&times;</span>
                    <div class="modal-body">
                        <!-- Left Section -->
                        <div class="left-section">
                            <h4>Automate conversations with AI</h4>
                            <p>เพิ่มยอดขายผ่านการส่งโปรโมชั่น และลดเวลาในการทำงานด้วยระบบแชท AI ที่ทำงานตลอด 24/7</p>
                            <p class="business-value">เมนู Setting > Training</p>
                            <p>ขั้นตอน</p>
                            <ul class="steps">
                                <li><span>1.</span> คลิกปุ่ม เทรน</li>
                                <li><span>2.</span> เลือกแพลตฟอร์ม</li>
                                <li><span>3.</span> ทำตามคำแนะนำ</li>
                                <li><span>4.</span> ใช้งานระบบได้ทันที!</li>
                            </ul>
                            <a href="<?php echo base_url('/setting/message'); ?>" class="btn btn-primary btn-block">Go Set Up</a>
                        </div>

                        <!-- Right Section -->
                        <div class="right-section">
                            <!-- <div class="phone-mockup">
                            <div class="chat-bubble">Let's start with an easy one: which wine would you pick for fish: <span>red</span> or <span>white</span>?</div>
                            <div class="chat-options">
                                <button>Red 🍷</button>
                                <button>White ❤️</button>
                            </div>
                        </div> -->

                            <div class="get-started-scene">
                                <picture class="top-new-macbook">
                                    <source srcset="<?php echo base_url('assets/images/top-new-macbook-xs.png'); ?> 1x, <?php echo base_url('assets/images/top-new-macbook-xs@2x.png'); ?> 2x" media="(max-width: 767px)">
                                    <source srcset="<?php echo base_url('assets/images/top-new-macbook.png'); ?> 1x, <?php echo base_url('assets/images/top-new-macbook@2x.png'); ?> 2x" media="(min-width: 768px)">
                                    <img src="<?php echo base_url('/assets/images/top-new-macbook-xs@2x.png'); ?>" width="806" height="534">
                                </picture>
                                <div class="scene-01 play">
                                    <picture>
                                        <!-- <source srcset="<?php echo base_url('assets/images/screen-hero-1-xs.jpg'); ?> 1x, <?php echo base_url('assets/images/screen-hero-1-xs@2x.jpg'); ?> 2x" media="(max-width: 767px)">
                                    <source srcset="<?php echo base_url('assets/images/screen-hero-1.jpg'); ?> 1x, <?php echo base_url('assets/images/screen-hero-1@2x.jpg'); ?> 2x" media="(min-width: 768px)">
                                    <img src="<?php echo base_url('assets/images/screen-hero-1-xs@2x.jpg'); ?>" width="571" height="367"> -->

                                        <source srcset="<?php echo base_url('assets/images/start3.gif'); ?> 1x, <?php echo base_url('assets/images/start1.gif'); ?> 2x" media="(max-width: 767px)">
                                        <source srcset="<?php echo base_url('assets/images/start3.gif'); ?> 1x, <?php echo base_url('assets/images/start1.gif'); ?> 2x" media="(min-width: 768px)">
                                        <img src="<?php echo base_url('assets/images/start3.gif'); ?>" width="571" height="367">
                                    </picture>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php echo view('/partials/copyright'); ?>
    
</div><!-- container -->
<!-- end page content -->