<div class="page-content">
    <div class="container-xxl">
        <div class="row my-3">
            <div class="col-12">
                <div class="">
                    <div class="card-body">
                        <div class="d-block d-md-flex justify-content-between align-items-center ">
                            <div class="d-flex align-self-center mb-2 mb-md-0">

                            </div>
                            <div class="align-self-center">
                                <form class="row g-2">
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalDefault"><i class="fa-solid fa-plus me-1"></i> เพิ่มการเชื่อมต่อ</button>
                                    </div><!--end col-->
                                </form>
                            </div>
                        </div>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!-- end col -->
        </div> <!-- end row -->
        <div class="row">
            <?php foreach ($user_socials as $user_social) { ?>
                <div class="col-md-4" id="userSocialWrapper-<?php echo $user_social->id; ?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-absolute  end-0 me-3 userSocialStatus" data-user-social-id="<?php echo $user_social->id; ?>">
                                <?php if ($user_social->is_connect == '1') { ?>
                                    <span class="badge rounded text-success bg-transparent border border-primary ms-1 p-1">เชื่อมต่อแล้ว</span>
                                <?php } else { ?>
                                    <span class="badge rounded text-danger bg-transparent border border-danger ms-1 p-1">หลุดการเชื่อมต่อ</span>
                                <?php } ?>
                            </div>
                            <div class="text-center border-dashed-bottom pb-3">
                                <img src="<?php echo base_url('assets/images/' . getPlatformIcon($user_social->platform)); ?>" alt="" height="80" class="rounded-circle d-inline-block">
                                <h5 class="fw-bold my-2 fs-20"><?php echo $user_social->name; ?></h5>
                                <p class="text-dark  fs-13 fw-semibold"><span class="text-muted">URL Webhook : </span><?php echo base_url() . '/webhook/' . hashidsEncrypt($user_social->id); ?> <i class="far fa-copy" onclick="copyToClipboard('<?= base_url() . '/webhook/' . hashidsEncrypt($user_social->id); ?>')" style="cursor: pointer;;"></i></p>
                                <div style="height: 96px;">
                                    <?php if ($user_social->platform == 'Line') { ?>
                                        <p class="text-muted mt-0 mb-0">1. คัดลอก URL Webhook ไปตั้งค่าใน <a href="https://manager.line.biz/" target="_blank">https://manager.line.biz/</a></p>
                                        <p class="text-muted mt-0 mb-0">2. ทดสอบโดยการ กดปุ่มเชื่อมต่อ</p>
                                    <?php } else if ($user_social->platform == 'Facebook') { ?>
                                        <p class="text-muted mt-0 mb-0">1. คัดลอก URL Webhook ไปตั้งค่าใน Meta Developer (หากต้องยืนยันให้ใส่คำว่า HAPPY) </p>
                                        <p class="text-muted mt-0 mb-0">2. แล้วจะได้ Token จาก Facebook ให้นำมาใส่ที่ ปุ่มระบุ Token</p>
                                        <p class="text-danger mt-0 mb-0">3. ต้องยื่นเรื่องขอ Permission กับ Meta</p>
                                        <p class="text-muted mt-0 mb-0">4. ทดสอบโดยการ กดปุ่มเชื่อมต่อ</p>
                                    <?php } else if ($user_social->platform == 'WhatsApp') { ?>
                                        <p class="text-muted mt-0 mb-0">1. คัดลอก URL Webhook ไปตั้งค่าใน Meta Developer</p>
                                        <p class="text-muted mt-0 mb-0">2. หากต้องยืนยันให้ใส่คำว่า HAPPY</p>
                                        <p class="text-muted mt-0 mb-0">3. ทดสอบโดยการ กดปุ่มเชื่อมต่อ</p>
                                    <?php } else if ($user_social->platform == 'Instagram') { ?>
                                        <p class="text-muted mt-0 mb-0">1. คัดลอก URL Webhook ไปตั้งค่าใน Meta Developer (หากต้องยืนยันให้ใส่คำว่า HAPPY) </p>
                                        <p class="text-danger mt-0 mb-0">2. ต้องยื่นเรื่องขอ Permission (instagram_business_basic, instagram_business_manage_messages) กับ Meta เพื่อใช้ในการตอบข้อความ</p>
                                        <p class="text-muted mt-0 mb-0">3. ทดสอบโดยการ กดปุ่มเชื่อมต่อ</p>
                                    <?php } else if ($user_social->platform == 'Tiktok') { ?>
                                        <p class="text-muted mt-0 mb-0">1. คัดลอก URL Webhook ไปตั้งค่าใน Tiktok Developer</p>
                                        <p class="text-muted mt-0 mb-0">2. ทดสอบโดยการ กดปุ่มเชื่อมต่อ</p>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between fw-semibold align-items-center  mt-3">
                                <div>
                                    <?php if ($user_social->platform == 'Facebook') { ?>
                                        <button type="button" class="btn bg-info-subtle text-dark btn-sm px-3 btnInputToken" data-platform="<?php echo $user_social->platform; ?>" data-user-social-id="<?php echo $user_social->id; ?>" data-bs-toggle="modal" data-bs-target="#formModalDefault">ระบุ Token</button>
                                    <?php } ?>
                                    <button type="button" class="btn btn-sm btn-warning px-2 d-inline-flex align-items-center btnCheckConnect" data-platform="<?php echo $user_social->platform; ?>" data-user-social-id="<?php echo $user_social->id; ?>"><i class="fab fa-connectdevelop me-1"></i> เชื่อมต่อ</button>
                                    <?php if ($user_social->ai == 'on') { ?>
                                        <button type="button" class="btn btn-sm btn-primary px-2 d-inline-flex align-items-center btnAI" data-platform="<?php echo $user_social->platform; ?>" data-user-social-id="<?php echo $user_social->id; ?>"><i class="fas fa-robot me-1"></i> กำลังใช้งาน AI</button>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-sm btn-primary px-2 d-inline-flex align-items-center btnAI" data-platform="<?php echo $user_social->platform; ?>" data-user-social-id="<?php echo $user_social->id; ?>"><i class="fas fa-robot me-1"></i> เปิดใช้ AI</button>
                                    <?php } ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger px-2 d-inline-flex align-items-center btnDelete" data-platform="<?php echo $user_social->platform; ?>" data-user-social-id="<?php echo $user_social->id; ?>"><i class="fas fa-trash me-1"></i>*</button>
                            </div>
                        </div><!--end card-body-->
                    </div>
                </div>
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
                    <input class="form-check-input" type="checkbox" id="settings-switch2" checked="">
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
                    <input class="form-check-input" type="checkbox" id="settings-switch5" checked="">
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
</div>

<div class="modal fade" id="exampleModalDefault" tabindex="-1" role="dialog" aria-labelledby="exampleModalDefaultLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title m-0" id="exampleModalDefaultLabel">เพิ่มการเชื่อมต่อใหม่</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div><!--end modal-header-->
            <div class="modal-body">
                <form action="" method="post" id="custom-step">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab">
                            <a class="nav-link py-2 active" id="step1-tab" data-bs-toggle="tab" href="#step1">ขั้นตอนที่ 1 เลือกแฟลตฟอร์ม</a>
                            <a class="nav-link py-2" id="step2-tab" data-bs-toggle="tab" href="#step2">ขั้นตอนที่ 2 ตรวจสอบการใช้งาน Messaging API</a>
                            <a class="nav-link py-2" id="step3-tab" data-bs-toggle="tab" href="#step3">ขั้นตอนที่ 3 กรอกข้อมูลการเชื่อมต่อ</a>
                        </div>
                    </nav>
                    <div class="tab-content mt-3" id="nav-tabContent">
                        <!-- tab 1 -->
                        <div class="tab-pane active" id="step1">
                            <!-- <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked="" value="Facebook">
                                <label class="btn btn-outline-info" for="btnradio1">Facebook</label>

                                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off" value="Line">
                                <label class="btn btn-outline-primary" for="btnradio2">Line</label>

                                <input type="radio" class="btn-check" name="btnradio" id="btnradio3" autocomplete="off" value="WhatsApp">
                                <label class="btn btn-outline-primary" for="btnradio3">Whats App</label>

                                <input type="radio" class="btn-check disabled" name="btnradio" id="btnradio4" autocomplete="off" value="Instagram">
                                <label class="btn btn-outline-secondary disabled" for="btnradio4">Instagram</label>

                                <input type="radio" class="btn-check disabled" name="btnradio" id="btnradio5" autocomplete="off" value="Tiktok">
                                <label class="btn btn-outline-secondary disabled" for="btnradio5">Tiktok</label>
                            </div> -->
                            <style>
                                .radio-group {
                                    display: flex;
                                    justify-content: center;
                                    gap: 30px;
                                    margin-top: 20px;
                                }

                                .radio-item {
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    cursor: pointer;
                                }

                                .radio-icon {
                                    width: 60px;
                                    height: 60px;
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    border-radius: 50%;
                                    position: relative;
                                    border: 2px solid transparent;
                                    transition: border-color 0.3s ease;
                                }

                                .radio-icon.selected {
                                    border-color: #007bff;
                                }

                                .radio-icon img {
                                    width: 40px;
                                    height: 40px;
                                }

                                .radio-item span {
                                    margin-top: 10px;
                                    font-size: 14px;
                                    color: #333;
                                }

                                .radio-icon .checkmark {
                                    position: absolute;
                                    bottom: -5px;
                                    right: -5px;
                                    background-color: #007bff;
                                    color: white;
                                    font-size: 12px;
                                    width: 20px;
                                    height: 20px;
                                    border-radius: 50%;
                                    display: none;
                                    justify-content: center;
                                    align-items: center;
                                }

                                .radio-icon.selected .checkmark {
                                    display: flex;
                                }
                            </style>
                            <div class="radio-group">
                                <!-- LINE -->
                                <div class="radio-item" data-value="Line">
                                    <div class="radio-icon">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/LINE_logo.svg" alt="Line">
                                        <div class="checkmark"><i class="fas fa-check"></i></div>
                                    </div>
                                    <span>LINE</span>
                                </div>
                                <!-- Facebook -->
                                <div class="radio-item" data-value="Facebook">
                                    <div class="radio-icon">
                                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/0/04/Facebook_f_logo_%282021%29.svg/512px-Facebook_f_logo_%282021%29.svg.png?20210818083032" alt="Facebook">
                                        <div class="checkmark"><i class="fas fa-check"></i></div>
                                    </div>
                                    <span>Facebook</span>
                                </div>
                                <!-- WhatsApp -->
                                <div class="radio-item" data-value="WhatsApp">
                                    <div class="radio-icon">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/800px-WhatsApp.svg.png" alt="WhatsApp">
                                        <div class="checkmark"><i class="fas fa-check"></i></div>
                                    </div>
                                    <span>WhatsApp</span>
                                </div>
                                <!-- Instagram -->
                                <div class="radio-item" data-value="Instagram">
                                    <div class="radio-icon">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg" alt="Instagram">
                                        <div class="checkmark"><i class="fas fa-check"></i></div>
                                    </div>
                                    <span>Instagram</span>
                                </div>
                                <!-- Tiktok -->
                                <div class="radio-item disabled" data-value="Tiktok">
                                    <div class="radio-icon">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Ionicons_logo-tiktok.svg/512px-Ionicons_logo-tiktok.svg.png" alt="Tiktok">
                                        <div class="checkmark"><i class="fas fa-check"></i></div>
                                    </div>
                                    <span>Tiktok (beta)</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" id="step1Next" class="btn btn-primary float-end">Next</button>
                            </div>
                        </div>

                        <!-- tab 2 -->
                        <div class="tab-pane" id="step2">
                            <!-- Facebook -->
                            <div class="step2-facebook-wrapper" style="display: none;">
                                <img src="https://i0.wp.com/saixiii.com/wp-content/uploads/2017/04/messaging-api.png?fit=720%2C346&ssl=1" alt="" class="img-fluid rounded w-100">
                                <hr>
                                <p class="mb-0">1. เข้าไปจัดการที่ Meta Developer <a href="https://developers.facebook.com/" target="_blank">https://developers.facebook.com/</a></p>
                                <p class="mb-0">2. เลือก Facebook Messaging API</p>
                                <p class="mb-0">3. หลังจากใส่ข้อมูลเสร็จ ให้เอา Token มาใส่ที่ระบบ</p>
                            </div>
                            <!-- Line -->
                            <div class="step2-line-wrapper" style="display: none;">
                                <img src="https://cdn6.aptoide.com/imgs/6/c/b/6cb90ef28865cb7d4dcc94450cb24c6a_fgraphic.png" alt="" class="img-fluid rounded">
                                <hr>
                                <p class="mb-0">1. เปิด <a href="https://manager.line.biz" target="_blank">https://manager.line.biz</a></p>
                                <p class="mb-0">2. เลือกบัญชี LINE OA ที่คุณต้องการเชื่อมต่อ และไปที่หน้าการตั้งค่า</p>
                                <p class="mb-0">3. เลือก Messaging API ในเมนูด้านซ้ายมือ</p>
                                <p class="mb-0">4. กดปุ่ม Messaging API</p>
                                <hr>
                                <ul class="nav nav-pills nav-justified" role="tablist">
                                    <li class="nav-item waves-effect waves-light" role="presentation">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#home-1" role="tab" aria-selected="true">มีปุ่ม Messaging API</a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#profile-1" role="tab" aria-selected="false" tabindex="-1">ไม่มีปุ่ม Messaging API</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane py-3 active show" id="home-1" role="tabpanel">
                                        <p class="mb-0">5. หากธุรกิจมีโพรไวเดอร์อยู่แล้ว ให้เลือก โพรไวเดอร์ (Provider) หากยังไม่มี ให้สร้าง โพรไวเดอร์ (Provider) ขึ้นใหม่</p>
                                        <p class="mb-0">6. เพิ่ม Privacy policy หรือ Term of Use (กดข้ามได้)</p>
                                        <p class="mb-0">7. ตรวจสอบข้อมูล และกด ตกลง</p>
                                        <p class="mb-0">8. กลับมา แล้วกดปุ่ม ‘ถัดไป’</p>
                                    </div>
                                    <div class="tab-pane py-3" id="profile-1" role="tabpanel">
                                        <p class="mb-0">5. ตรวจสอบว่า Messaging API มีข้อมูลแชนแนล ID (Channel ID) และ ความลับแชนแนล (Channel Secret) หรือไม่</p>
                                        <img src="https://chat.bloxchats.com/messaging-api.png" alt="" class="img-fluid">
                                        <p class="mb-0">6. หากมีข้อมูลครบถ้วน กดปุ่ม ‘ถัดไป’</p>
                                    </div>

                                </div>
                            </div>
                            <!-- WhatsApp -->
                            <div class="step2-whatsapp-wrapper" style="display: none;">
                                <img src="https://www.zenvia.com/wp-content/uploads/2022/02/API20oficial20de20Whatsapp.jpgwidth600nameAPI20oficial20de20Whatsapp.jpg" alt="" class="img-fluid rounded w-100">
                                <hr>
                                <p class="mb-0">1. เข้าไปจัดการที่ Meta Developer <a href="https://developers.facebook.com/" target="_blank">https://developers.facebook.com/</a></p>
                                <p class="mb-0">2. เลือก Whats App API</p>
                                <p class="mb-0">3. หลังจากใส่ข้อมูลเสร็จ ให้เอา Token มาใส่ที่ระบบ</p>
                            </div>
                            <!-- Instagram -->
                            <div>
                                <div class="step2-instagram-wrapper" style="display: none;">
                                    <img src="https://kait.ai/static/images/website/blog/instagram-api-banner.webp" alt="" class="img-fluid rounded w-100">
                                    <hr>
                                    <p class="mb-0">1. เข้าไปจัดการที่ Meta Developer <a href="https://developers.facebook.com/" target="_blank">https://developers.facebook.com/</a></p>
                                    <p class="mb-0">2. เลือก Instagram Messaging API</p>
                                    <p class="mb-0">3. หลังจากใส่ข้อมูลเสร็จ ให้เอา Token มาใส่ที่ระบบ</p>
                                </div>
                            </div>
                            <!-- Tiktok -->
                            <div>
                                <div class="step2-tiktok-wrapper" style="display: none;">
                                    <img src="https://media.bazaarvoice.com/Shutterstock_1757132165-1030x541.png" alt="" class="img-fluid rounded w-100">
                                    <hr>
                                    <p class="mb-0">1. เข้าไปจัดการที่ TikA API <a href="https://tikapi.io/" target="_blank">https://tikapi.io/</a></p>
                                    <p class="mb-0">2. เลือก Tiktok Messaging API</p>
                                    <p class="mb-0">3. หลังจากใส่ข้อมูลเสร็จ ให้เอา Token มาใส่ที่ระบบ</p>
                                </div>
                            </div>
                            <div>
                                <button type="button" id="step2Prev" class="btn btn-secondary float-start mt-2">Previous</button>
                                <button type="button" id="step2Next" class="btn btn-primary float-end mt-2">Next</button>
                            </div>
                        </div>

                        <!-- tab 3 -->
                        <div class="tab-pane" id="step3">
                            <!-- Facebook -->
                            <div class="step3-facebook-wrapper">
                                <div class="mb-3">
                                    <label for="" class="form-label">ชื่อ (ไม่มีผลกับในระบบ ตั้งเพื่อโน้ตไว้ใช้งาน)<span class="text-denger">*</span></label>
                                    <input type="text" name="facebook_social_name" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                                <!-- <div class="mb-3">
                                    <label for="" class="form-label">Token <span class="text-denger">*</span></label>
                                    <input type="text" name="fb_token" class="form-control" id="" aria-describedby="" placeholder="">
                                </div> -->
                            </div>
                            <!-- Line -->
                            <div class="step3-line-wrapper">
                                <div class="mb-3">
                                    <label for="" class="form-label">ชื่อ (ไม่มีผลกับในระบบ ตั้งเพื่อโน้ตไว้ใช้งาน)<span class="text-denger">*</span></label>
                                    <input type="text" name="line_social_name" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Channal ID <span class="text-denger">*</span></label>
                                    <input type="text" name="line_channel_id" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Channal Secret <span class="text-denger">*</span></label>
                                    <input type="text" name="line_channel_secret" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                            </div>
                            <!-- WhatsApp -->
                            <div class="step3-whatsapp-wrapper">
                                <div class="mb-3">
                                    <label for="" class="form-label">ชื่อ (ไม่มีผลกับในระบบ ตั้งเพื่อโน้ตไว้ใช้งาน)<span class="text-denger">*</span></label>
                                    <input type="text" name="whatsapp_social_name" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Token <span class="text-denger">*</span></label>
                                    <input type="text" name="whatsapp_token" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                                <!-- <div class="mb-3">
                                    <label for="" class="form-label">Phone Number ID <span class="text-denger">*</span></label>
                                    <input type="text" name="whatsapp_phone_number_id" class="form-control" id="" aria-describedby="" placeholder="">
                                </div> -->
                            </div>
                            <!-- Instagram -->
                            <div class="step3-instagram-wrapper">
                                <div class="mb-3">
                                    <label for="" class="form-label">ชื่อ (ไม่มีผลกับในระบบ ตั้งเพื่อโน้ตไว้ใช้งาน)<span class="text-denger">*</span></label>
                                    <input type="text" name="instagram_social_name" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Token <span class="text-denger">*</span></label>
                                    <input type="text" name="instagram_token" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                            </div>
                            <!-- Tiktok -->
                            <div class="step3-tiktok-wrapper">
                                <div class="mb-3">
                                    <label for="" class="form-label">ชื่อ (ไม่มีผลกับในระบบ ตั้งเพื่อโน้ตไว้ใช้งาน)<span class="text-denger">*</span></label>
                                    <input type="text" name="tiktok_social_name" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Token <span class="text-denger">*</span></label>
                                    <input type="text" name="tiktok_token" class="form-control" id="" aria-describedby="" placeholder="">
                                </div>
                            </div>
                            <div>
                                <button type="button" id="step3Prev" class="btn btn-secondary float-start mt-2">Previous</button>
                                <button type="button" id="step3Finish" class="btn btn-danger float-end mt-2">Finish</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div><!--end modal-body-->
        </div><!--end modal-content-->
    </div><!--end modal-dialog-->
</div><!--end modal-->

<div class="modal fade" id="formModalDefault" tabindex="-1" role="dialog" aria-labelledby="exampleModalDefaultLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title m-0" id="formModalDefaultLabel">ระบุ Token</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div><!--end modal-header-->
            <div class="modal-body">
                <form action="" method="post" id="form-fb-token">
                    <input type="hidden" name="user_social_id" value="">
                    <div class="mb-3">
                        <label for="" class="form-label">Token FB</label>
                        <input type="text" class="form-control" id="" placeholder="" name="fb_token">
                    </div>
                    <button class="btn btn-primary w-100" id="btnSaveFbToken">บันทึก</button>
                </form>
            </div><!--end modal-body-->
        </div><!--end modal-content-->
    </div><!--end modal-dialog-->
</div><!--end modal-->