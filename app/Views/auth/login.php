<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>AutoConX | เข้าสู่ระบบ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo72x72.png">

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
                                        <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Let's Get Started AutoConX</h4>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <form class="my-4" action="index.html">
                                        <div class="form-group mb-2">
                                            <label class="form-label" for="username">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
                                        </div><!--end form-group-->

                                        <div class="form-group">
                                            <label class="form-label" for="userpassword">Password</label>
                                            <input type="password" class="form-control" name="password" id="userpassword" placeholder="Enter password">
                                        </div><!--end form-group-->

                                        <div class="form-group row mt-3">
                                            <div class="col-sm-6">

                                            </div><!--end col-->
                                            <div class="col-sm-6 text-end">
                                                <a href="auth-recover-pw.html" class="text-muted font-13"><i class="dripicons-lock"></i> Forgot password?</a>
                                            </div><!--end col-->
                                        </div><!--end form-group-->

                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <div class="d-grid mt-3">
                                                    <button id="btn-login" class="btn btn-primary" type="button">เข้าสู่ระบบ <i class="fas fa-sign-in-alt ms-1"></i></button>
                                                </div>
                                            </div><!--end col-->
                                        </div> <!--end form-group-->
                                    </form><!--end form-->
                                    <div class="text-center  mb-2">
                                        <p class="text-muted">Don't have an account ? <a href="<?php echo base_url('/auth-register'); ?>" class="text-primary ms-2">สมัครสมาชิก</a></p>
                                        <h6 class="px-3 d-inline-block">Or Login With</h6>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <a href="<?php echo base_url('auth/login/facebook') ?>" class="d-flex justify-content-center align-items-center thumb-md bg-blue-subtle text-blue rounded-circle me-2">
                                            <i class="fab fa-facebook align-self-center"></i>
                                        </a>
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

<!-- Script -->
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {
        $('#btn-login').on('click', function(e) {
            e.preventDefault()
            const $btnLogin = $(this)

            $btnLogin.prop('disabled', true)

            let username = $('input[name="username"]').val()
            let password = $('input[name="password"]').val()

            let dataObj = {
                username,
                password
            }

            $.ajax({
                type: 'POST',
                url: `${serverUrl}/login`,
                contentType: 'application/json; charset=utf-8;',
                processData: false,
                data: JSON.stringify(dataObj),
                success: function(res) {
                    if (res.success === 1) {

                        $btnLogin.prop('disabled', false)

                        Swal.fire({
                            icon: 'success',
                            text: `${res.message}`,
                            timer: '2000',
                            heightAuto: false
                        });

                        window.location.href = res.redirect_to;
                    } else {
                        $btnLogin.prop('disabled', false)
                    }
                },
                error: function(res) {

                    $btnLogin.prop('disabled', false)

                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สามารถเข้าสู่ระบบได้',
                        text: `${res.responseJSON.message}`,
                        timer: '2000',
                        heightAuto: false
                    });
                }
            })

        });
        $(".toggle-password").click(function() {
            $(this).toggleClass("fa-eye fa-eye-slash");
            var input = $($(this).attr("toggle"));
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    });
</script>

</html>