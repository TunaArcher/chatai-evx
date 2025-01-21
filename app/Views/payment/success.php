<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>AutoCon X | Payment Success</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/css/icons.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/css/app.min.css'); ?>" rel="stylesheet" type="text/css" />

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <style>
        /** BASE **/
        * {
            font-family: 'Kanit', sans-serif;
        }
    </style>
</head>

<body>

    <div class="container-xxl">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 bg-black auth-header-box rounded-top">
                                    <div class="text-center p-3">
                                        <a href="#" class="logo logo-admin">
                                            <img src="<?php echo base_url('/assets/images/conX.png'); ?>" alt="" class="thumb-lg rounded-circle">
                                        </a>
                                        <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">ชำระเงินสำเร็จ</h4>
                                        <p class="text-muted fw-medium mb-0">เราจะพาคุณกลับสู่ระบบในอีก 5 วิ</p>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="ex-page-content text-center">
                                        <img src="<?php echo base_url('/assets/images/conX.png'); ?>" alt="0" class="" height="170">
                                        <!-- <h1 class="my-2">404!</h1> -->
                                        <h5 class="fs-16 text-muted mb-3">ชำระเงินสำเร็จ ขอบคุณที่ให้ความไว้วางใจเรา</h5>
                                    </div>
                                    <a class="btn btn-primary w-100" href="<?php echo base_url('/profile'); ?>">Back to Dashboard <i class="fas fa-redo ms-1"></i></a>
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end card-body-->
            </div><!--end col-->
        </div><!--end row-->
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            // ตั้งค่าเวลา 5000 มิลลิวินาที (5 วินาที)
            setTimeout(function() {
                // Redirect ไปที่หน้าโปรไฟล์
                window.location.href = "/profile"; // เปลี่ยน URL เป็นหน้าที่ต้องการ
            }, 5000);
        });
    </script>
</body>
<!--end body-->

</html>