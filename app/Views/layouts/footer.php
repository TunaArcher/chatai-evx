<?php if (session()->get('user_owner_id') == '') { ?>
    <style>
        .plan-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .plan-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .selected-plan {
            border: 2px solid #00c7e6 !important;
        }

        .btn-add-plan {
            font-weight: bold;
            background: #f8f9fa;
            color: #00c7e6;
            border: 1px solid #00c7e6;
            border-radius: 20px;
        }

        .btn-add-plan:hover {
            background: #00c7e6;
            color: #fff;
        }

        .order-summary {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
        }

        .total-price {
            font-size: 24px;
            font-weight: bold;
            color: #00c7e6;
        }

        .payment-icons img {
            max-height: 20px;
            margin-right: 10px;
        }
    </style>
    <div class="modal fade" id="upgradeYourPlan" tabindex="-1" role="dialog" aria-labelledby="editTeam" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title m-0">Upgrade your plan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <div class="container mt-3">
                        <h1 class="text-center font-weight-bold mb-4">Upgrade your plan</h1>

                        <style>
                            .plan-card ul {
                                list-style: none;
                                /* เอาจุดหน้ารายการออก */
                                padding: 0;
                                /* ปรับระยะขอบ */
                            }

                            .plan-card ul li {
                                margin-bottom: 8px;
                                /* เพิ่มช่องว่างระหว่างแต่ละรายการ */
                            }

                            .plan-card ul li i {
                                margin-right: 8px;
                                /* เพิ่มระยะห่างระหว่างไอคอนกับข้อความ */
                            }
                        </style>
                        
                        <div class="row">
                            <!-- Left Section: Plans -->
                            <div class="col-md-8">
                                <!-- Marketing Automation -->
                                <div class="plan-card" id="plan-marketing-automation">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="font-weight-bold">AutoCon X เริ่มต้น</h4>
                                        <span class="font-weight-bold text-primary" style="font-size: 18px;">$19/เดือน</span>
                                    </div>
                                    <ul>
                                        <li>สามารถเชื่อมต่อ Social ทุก ๆ Platform ได้ 5 Platform</li>
                                        <li>ด้วย AutoCon X 's Flow Builder คุณสามารถดึงดูด ตรวจสอบ และดูแลลูกค้าได้อย่างมีประสิทธิภาพ ผ่านการสร้างบทสนทนาอัตโนมัติที่ช่วยเพิ่มการมีส่วนร่วมและขยายธุรกิจของคุณ</li>
                                    </ul>
                                    <!-- <button class="btn btn-add-plan" id="plan-basic" data-plan-id="1" data-plan-name="AutoCon X Plan เริ่มต้น" data-price="19">เลือก</button> -->
                                </div>

                                <!-- Manychat AI -->
                                <div class="plan-card" id="plan-manychat-ai">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="font-weight-bold">AutoCon X Pro <span class="badge rounded-pill bg-success">Hot แนะนำ</span></h4>
                                        <span class="font-weight-bold text-primary" style="font-size: 18px;">$9/เดือน</span>
                                    </div>
                                    <ul>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> สามารถเชื่อมต่อ Social ทุก ๆ Platform ได้ไม่จำกัด</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> สิทธิการใช้งาน AutoCon X AI</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> เพิ่มความแม่นยำในการตอบกลับด้วย AutoCon X AI ที่สามารถตรวจจับเจตนาของข้อความโดยอัตโนมัติ เพื่อการตอบสนองที่ดีที่สุด</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> ให้ AI Step ช่วยจัดระเบียบการสนทนา เพิ่มยอดขาย และกระตุ้นลูกค้าที่ไม่ตอบกลับ</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> หากคุณลำบากกับการเขียนข้อความ ให้ AI Text Improver ช่วยเขียน ปรับแต่ง และจัดสไตล์ข้อความให้คุณอย่างง่ายดาย</li>
                                    </ul>
                                    <button class="btn btn-add-plan handleBtnPlan" id="plan-pro" data-plan-id="2" data-plan-name="AutoCon X Pro" data-price="9">เลือก</button>
                                </div>

                                <!-- Inbox -->
                                <!-- <div class="plan-card" id="plan-inbox">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="font-weight-bold">AutoCon X Plan Enterprise</h4>
                                        <span class="font-weight-bold text-primary"></span>
                                    </div>
                                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Iusto doloribus molestiae aliquid deleniti, fugit consectetur. Maxime omnis sint delectus at nihil reprehenderit, minus sapiente debitis optio vitae cupiditate non voluptate!</p>
                                    <button class="btn btn-add-plan handleBtnPlan disabled">ติดต่อผู้ให้บริการ</button>
                                </div> -->

                                <div class="plan-card" id="plan-manychat-ai">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="font-weight-bold">AutoCon X Pro โปรรายปี <span class="badge rounded-pill gradient-animate-btn ">ประหยัดเพิ่ม 17%</span></h4>
                                        <span class="font-weight-bold text-primary" style="font-size: 18px;">$90/ปี</span>
                                    </div>
                                    <ul>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> สามารถเชื่อมต่อ Social ทุก ๆ Platform ได้ไม่จำกัด</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> สิทธิการใช้งาน AutoCon X AI</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> เพิ่มความแม่นยำในการตอบกลับด้วย AutoCon X AI ที่สามารถตรวจจับเจตนาของข้อความโดยอัตโนมัติ เพื่อการตอบสนองที่ดีที่สุด</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> ให้ AI Step ช่วยจัดระเบียบการสนทนา เพิ่มยอดขาย และกระตุ้นลูกค้าที่ไม่ตอบกลับ</li>
                                        <li><i class="fa-solid fa-check-circle" style="color: #00c7e6;"></i> หากคุณลำบากกับการเขียนข้อความ ให้ AI Text Improver ช่วยเขียน ปรับแต่ง และจัดสไตล์ข้อความให้คุณอย่างง่ายดาย</li>
                                    </ul>
                                    <button class="btn btn-add-plan handleBtnPlan" id="plan-proyear" data-plan-id="3" data-plan-name="AutoCon X Pro โปรรายปี" data-price="90">เลือก</button>
                                </div>
                            </div>

                            <!-- Right Section: Order Summary -->
                            <div class="col-md-4">
                                <div class="order-summary">
                                    <h4 class="font-weight-bold">รายการ</h4>
                                    <ul id="selected-plans" class="list-unstyled">
                                        <!-- Selected plans will be added dynamically -->
                                    </ul>
                                    <hr>
                                    <p class="d-flex justify-content-between">
                                        <span>ยอดรวม:</span>
                                        <span class="total-price" id="total-price">$0</span>
                                    </p>
                                    <button class="btn btn-primary btn-block w-100 btnPayment">ชำระเงินทันที</button>
                                    <p class="text-muted mt-3" style="font-size: 12px;">
                                        AutoCon X ไม่สามารถใช้งานได้ในช่วงทดลองใช้งานฟรี คุณจะถูกเรียกเก็บเงินทันทีหากคุณคลิก 'สมัครสมาชิก' คุณสามารถยกเลิกแผนของคุณได้ทุกเมื่อ โดยการดำเนินการต่อ <a href="#">คุณยอมรับเงื่อนไขเพิ่มเติมของ AutoCon X</a>
                                    </p>
                                    <hr>
                                    <div class="mt-4">
                                        <div class="row">
                                            <div class="col-12">
                                                <p>รองรับการชำระเงินทุกแบบ</p>
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col payment-icons">
                                                <img src="<?php echo base_url('/assets/images/Visa-Logo-2014.png'); ?>" alt="Visa and Mastercard">
                                                <img src="<?php echo base_url('/assets/images/amex-american-express-logo.png'); ?>" alt="American Express">
                                                <img src="<?php echo base_url('/assets/images/Discover-Card-Logo-1985.png'); ?>" alt="Discover">
                                                <img src="<?php echo base_url('/assets/images/Mastercard-Logo-2016-2020.png'); ?>" alt="UnionPay">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end modal-body-->
            </div><!--end modal-content-->
        </div><!--end modal-dialog-->
    </div><!--end modal-->
<?php } ?>
</div>
<!-- end page-wrapper -->

<!-- Javascript  -->
<!-- vendor js -->
<script src="<?php echo base_url('/assets/libs/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo base_url('/assets/libs/simplebar/simplebar.min.js'); ?>"></script>
<script src="<?php echo base_url('/assets/js/app.js'); ?>"></script>

<!-- My CDN -->
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="https://unpkg.com/@sjmc11/tourguidejs/dist/tour.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<?php if (isset($js_critical)) {
    echo $js_critical;
}; ?>

<script>
    // ดึงปุ่ม Toggle
    var themeColorToggle = document.getElementById("light-dark-mode");

    // โหลดธีมที่บันทึกไว้ใน Local Storage (ถ้ามี)
    var savedTheme = localStorage.getItem("theme");
    if (savedTheme) {
        document.documentElement.setAttribute("data-bs-theme", savedTheme); // ตั้งค่าธีมตามค่าที่บันทึกไว้
    } else {
        // ถ้าไม่มีค่า ให้ตั้งค่าเริ่มต้นเป็น "light"
        document.documentElement.setAttribute("data-bs-theme", "light");
    }

    // เพิ่ม Event Listener ให้ปุ่ม Toggle
    themeColorToggle &&
        themeColorToggle.addEventListener("click", function() {
            // เช็คธีมปัจจุบัน
            var currentTheme = document.documentElement.getAttribute("data-bs-theme");

            // สลับธีมระหว่าง "light" และ "dark"
            var newTheme = currentTheme === "light" ? "dark" : "light";
            document.documentElement.setAttribute("data-bs-theme", newTheme);

            // บันทึกธีมลงใน Local Storage
            localStorage.setItem("theme", newTheme);
        });
</script>
<script>
    let _bodyElement = document.body;
    let _bodySize = _bodyElement.getAttribute("data-sidebar-size");
    let _messagecollapse = document.getElementById("message-collapse");
    if (_bodySize == "collapsed") {
        _messagecollapse.style.display = "none";
    }
</script>
<script>
    $(document).ready(function() {
        let selectedPlan = null; // To track selected plan

        // Function to handle plan selection
        $('.handleBtnPlan').click(function() {
            // Get plan details
            const planID = $(this).data('plan-id');
            const planName = $(this).data('plan-name');
            const planPrice = parseInt($(this).data('price'));

            // Deselect previous plan
            $('.btn-add-plan').removeClass('selected');

            // Mark the current button as selected
            $(this).addClass('selected');

            // Update selectedPlan object
            selectedPlan = {
                id: planID,
                name: planName,
                price: planPrice
            };

            // Update Order Summary
            $('#selected-plans').html(`
                <li class="d-flex justify-content-between">
                    <span>${planName}</span>
                    <span>$ ${planPrice}</span>
                </li>
            `);

            // Update total price
            $('#total-price').text(`$ ${planPrice}`);
        });

        // Handle payment button click
        $('.btnPayment').click(function() {

            let $me = $(this)

            if (!selectedPlan) {
                alert('กรุณาเลือกแผนก่อนทำการชำระเงิน');
                return;
            }


            $me.prop("disabled", true);

            // Send selected plan to the server via AJAX
            $.ajax({
                url: `${serverUrl}/subscription/selectPlan`, // Replace with your server endpoint
                type: 'POST',
                data: JSON.stringify({
                    userID: `${window.userID}`,
                    planID: selectedPlan.id,
                    planName: selectedPlan.name,
                    planPrice: selectedPlan.price
                }),
                contentType: "application/json; charset=utf-8",
                success: function(response) {
                    $me.prop("disabled", false);
                    location.href = response.url
                },
                error: function(xhr, status, error) {
                    console.error('Payment Error:', error);
                    alert('เกิดข้อผิดพลาดในการชำระเงิน กรุณาลองอีกครั้ง');
                }
            });
        });

        if (!window.subscriptionStatus != 'active') {

            function updateProgressBar(freeRequestLimit) {
                // Calculate the width percentage (assuming max is 10)
                let widthPercentage = (freeRequestLimit / 10) * 100;

                // Update the progress bar attributes and style
                let progressBar = $(".progress-bar");
                progressBar.css("width", widthPercentage + "%");
                progressBar.attr("aria-valuenow", freeRequestLimit);

                // Change text based on freeRequestLimit value
                if (freeRequestLimit >= 10) {
                    progressBar.text("ครบจำนวน Request แล้ว");
                } else {
                    progressBar.text(freeRequestLimit);
                }
            }

            // AJAX request to fetch free_request_limit
            $.ajax({
                url: `${serverUrl}/profile/get-free-request-limit`,
                method: "GET",
                dataType: "json",
                success: function(response) {
                    console.log(response)
                    if (response.free_request_limit !== undefined) {
                        updateProgressBar(response.free_request_limit);
                    } else {
                        console.error("Response does not contain free_request_limit");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data: ", error);
                }
            });
        }

    });
</script>
<script>
    if (window.userOwnerID == '') {
        const hasSeenTour = localStorage.getItem('hasSeenTour');

        if (!hasSeenTour && isDesktop()) {
            const tg = new tourguide.TourGuideClient({
                keyboardControls: true,
                rememberStep: true
            });

            tg.addSteps([{
                title: '👋 ยินดีต้อนรับสู่ AutoConX',
                content: '<p class="mb-3 text-center">สวัสดีครับ ผมเป็น Ai🤖 ของ AutoConX</p><hr><p class="text-center">🤖 คุณเป็นมือใหม่ใช่มั้ยล่ะ ? ไม่ต้องกังวลไป ผมจะพาคุณใช้งานให้เป็น เพียงใน <b>3</b> ขั้นตอน</p><p>ใช้คีย์บอร์ดของคุณหรือคลิกปุ่มถัดไปเพื่อเริ่มต้นใช้งาน!</p>',
                order: 0, // Add to start
            }]);

            tg.start();

            // บันทึกว่าเคยดู Tour แล้ว
            localStorage.setItem('hasSeenTour', 'true');
        }

        function isDesktop() {
            return window.innerWidth > 1024; // เช็คว่าหน้าจอขนาดใหญ่กว่า 1024px
        }
    }
</script>
</body>
<!--end body-->

</html>