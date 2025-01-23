<!-- Page Content-->
<div class="page-content">
    <div class="container">
        <div class="card" style="background-color:#000;">
            <div class="card-body">
                <div class="row">
                    <div class="col-auto align-self-center">
                        <img src="https://cdn.pixabay.com/photo/2021/02/14/22/00/tik-tok-6016006_1280.png" alt="" height="90" class="rounded">
                    </div>
                    <div class="col">
                        <h6 class="mb-2 mt-1 fw-medium text-white fs-18">TikTok x AutoConx. Now we're talking</h6>
                        <p class="text-white fs-14 ">Discover new opportunities for your audience in TikTok</p>
                    </div>
                    <div class="col-auto align-self-center">
                        <button class="btn btn-info" onclick="alert('in develop')">DISCOVER <i class="iconoir-arrow-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
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
        <div class="row my-2 justify-content-between align-items-center">
            <div class="col-auto text-left">
                <h2>เริ่มต้น</h2>
            </div>
            <div class="col-auto text-right"><a href="#" onclick="alert('in develop')">Explore all Templates</a></div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center border-dashed-bottom pb-3">
                            <div class="col-9">
                                <h3 class="mb-2 mb-0 fw-bold">1. สร้างการเชื่อมต่อ</h3>
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
                                <p class="mb-0 text-truncate text-muted">⚙️ Flow Builder</p>
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
            <div class="col-md-6 col-lg-4">
                <div class="card">
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
                                <p class="mb-0 text-truncate text-muted">⚙️ Flow Builder</p>
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
            <div class="col-md-6 col-lg-4">
                <div class="card">
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
                                <p class="mb-0 text-truncate text-muted">⚙️ Flow Builder</p>
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
    </div>
</div><!-- container -->
</div>
<!-- end page content -->