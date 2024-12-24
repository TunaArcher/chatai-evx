<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>AutoConX | สมัครสมาชิก</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo72x72.png">

    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <style>
        /** BASE **/
        * {
            font-family: 'Kanit', sans-serif;
        }
    </style>
    <script>
        var serverUrl = '<?php echo base_url(); ?>'
    </script>

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
                                        <a href="index.html" class="logo logo-admin">
                                            <img src="<?php echo base_url('/assets/images/conXx.png'); ?>" height="50" alt="logo" class="auth-logo">
                                        </a>
                                        <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Create an account</h4>
                                        <p class="text-muted fw-medium mb-0">Enter your detail to Create your account today.</p>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <form class="my-4" id="registration-form">
                                        <div class="form-group mb-2">
                                            <label class="form-label" for="username">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
                                        </div><!--end form-group-->

                                        <div class="form-group mb-2">
                                            <label class="form-label" for="useremail">Email</label>
                                            <input type="email" class="form-control" id="useremail" name="user email" placeholder="Enter email">
                                        </div><!--end form-group-->

                                        <div class="form-group mb-2">
                                            <label class="form-label" for="userpassword">Password</label>
                                            <input type="password" class="form-control" name="password" id="userpassword" placeholder="Enter password">
                                        </div><!--end form-group-->

                                        <div class="form-group mb-2">
                                            <label class="form-label" for="Confirmpassword">ConfirmPassword</label>
                                            <input type="password" class="form-control" name="password" id="Confirmpassword" placeholder="Enter Confirm password">
                                        </div><!--end form-group-->

                                        <div class="form-group row mt-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch form-switch-success">
                                                    <input class="form-check-input" type="checkbox" id="customSwitchSuccess">
                                                    <label class="form-check-label" for="customSwitchSuccess">By registering you agree to the Rizz <a href="#" class="text-primary">Terms of Use</a></label>
                                                </div>
                                            </div><!--end col-->
                                        </div><!--end form-group-->

                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <div class="d-grid mt-3">
                                                    <button class="btn btn-primary" type="submit">สมัครสมาชิก <i class="fas fa-sign-in-alt ms-1"></i></button>
                                                </div>
                                            </div><!--end col-->
                                        </div> <!--end form-group-->
                                    </form><!--end form-->
                                    <div class="text-center">
                                        <p class="text-muted">Already have an account ? <a href="<?php echo base_url('/login'); ?>" class="text-primary ms-2">เข้าสู่ระบบ</a></p>
                                    </div>
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end card-body-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container -->
</body>
<!--end body-->

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.all.min.js"></script>

<script>
    $("#registration-form").on("submit", function(e) {
        e.preventDefault(); // ป้องกันการรีเฟรชหน้าเว็บ

        // ดึงค่าข้อมูลจากฟอร์ม
        const formData = {
            username: $("#username").val(),
            email: $("#useremail").val(),
            password: $("#userpassword").val(),
            confirm_password: $("#Confirmpassword").val(),
            terms: $("#customSwitchSuccess").is(":checked") ? 1 : 0, // แปลง checkbox เป็นค่าตัวเลข
        };

        // ตรวจสอบข้อมูลก่อนส่ง (เช่น เช็คว่า password กับ confirm_password ตรงกัน)
        if (formData.password !== formData.confirm_password) {
            alert("Passwords do not match!");
            return;
        }

        // ส่งข้อมูลด้วย AJAX
        $.ajax({
            url: `${serverUrl}/register`, // เปลี่ยนเป็น URL Endpoint ของคุณ
            type: "POST",
            data: JSON.stringify(formData),
            contentType: "application/json; charset=utf-8",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: "Registration Successful!",
                        text: response.message,
                        icon: "success",
                        confirmButtonText: "OK",
                    }).then(() => {
                        window.location.href = "<?= base_url('/') ?>"; // เปลี่ยนเส้นทางหลังสำเร็จ
                    });
                } else {
                    Swal.fire({
                        title: "Registration Failed",
                        text: response.message,
                        icon: "error",
                        confirmButtonText: "OK",
                    });
                }
            },
            error: function(xhr, status, error) {
                const message = xhr.responseJSON?.message || "An unexpected error occurred.";
                Swal.fire({
                    title: "Error",
                    text: message,
                    icon: "error",
                    confirmButtonText: "OK",
                });
            },
        });
    });
</script>

</html>