<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AutoConX | สมัครสมาชิก</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
  <link rel="shortcut icon" href="assets/images/logo72x72.png">
  <style>
    /** BASE **/
    * {
      font-family: 'Kanit', sans-serif;
    }

    body {
      margin: 0;
      padding: 0;
      background-color: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .logo {
      position: absolute;
      top: 20px;
      /* ระยะห่างจากขอบบน */
      left: 50%;
      transform: translateX(-50%);
    }

    .logo img {
      width: 120px;
      /* กำหนดขนาดโลโก้ */
    }

    .login-container {
      width: 100%;
      max-width: 360px;
      text-align: center;
      margin-top: 100px;
      /* เว้นระยะจากโลโก้ */
    }

    h3 {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .form-control {
      height: 48px;
      font-size: 14px;
      border-radius: 6px;
    }

    .btn-success {
      height: 48px;
      font-size: 16px;
      border-radius: 6px;
      background-color: #00c7e6;
      border: none;
    }

    .btn-success:hover {
      background-color: #008599;
    }

    .btn-social {
      height: 48px;
      border-radius: 6px;
      font-size: 14px;
      width: 100%;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-social img {
      width: 20px;
      margin-right: 10px;
    }

    hr {
      margin: 30px 0;
    }

    .footer {
      font-size: 12px;
      color: #6c757d;
      text-align: center;
      width: 100%;
      position: absolute;
      bottom: 20px;
    }

    .footer a {
      color: #6c757d;
      text-decoration: none;
    }

    .footer a:hover {
      text-decoration: underline;
    }
  </style>
  <style>
    .btn-success:not(:disabled):not(.disabled).active,
    .btn-success:not(:disabled):not(.disabled):active,
    .show>.btn-success.dropdown-toggle {
      color: #fff;
      background-color: #00b1cc;
      border-color: #00b1cc;
    }

    .btn-success:not(:disabled):not(.disabled).active,
    .btn-success:not(:disabled):not(.disabled):active,
    .show>.btn-success.dropdown-toggle {
      color: #fff;
      background-color: #00b1cc;
      border-color: #00b1cc;
    }

    .btn-success:not(:disabled):not(.disabled).active:focus,
    .btn-success:not(:disabled):not(.disabled):active:focus,
    .show>.btn-success.dropdown-toggle:focus {
      box-shadow: 0 0 0 .2rem rgba(0, 177, 204, .5);
    }
  </style>
  <script>
    var serverUrl = '<?php echo base_url(); ?>'
  </script>
</head>

<body>
  <!-- โลโก้อยู่ด้านบนสุด -->
  <div class="logo">
    <img src="<?php echo base_url('/assets/images/conXx.png'); ?>" alt="Logo">
  </div>

  <!-- Login Container -->
  <div class="login-container">
    <h3>ตั้งค่าบัญชีของคุณ</h3>
    <p>ใส่อีเมล์และรหัสผ่านของคุณเพื่อสมัครสมาชิก</p>
    <form id="registration-form">
      <div class="form-group">
        <input name="email" value="<?php echo $email; ?>" type="email" class="form-control" placeholder="ที่อยู่อีเมล*" readonly>
      </div>
      <div class="form-group">
        <input name="password" name="password" type="password" class="form-control" placeholder="รหัสผ่าน*" required>
      </div>
      <div class="form-group">
        <input name="Confirmpassword" type="password" class="form-control" placeholder="ยืนยันรหัสผ่าน*" required>
      </div>
      <input type="hidden" name="user_owner_id" value="<?php echo $userOwnerID; ?>">
      <button type="submit" class="btn btn-success btn-block">ยืนยัน</button>
    </form>
    <p class="mt-3"> <a href="<?php echo base_url('/login'); ?>">กลับไป</a></p>
  </div>

  <!-- Footer -->
  <div class="footer">
    <a href="#">เงื่อนไขการใช้งาน</a> | <a href="#">นโยบายความเป็นส่วนตัว</a>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <!-- Script -->
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

  <script>
    const notyf = new Notyf({
      position: {
        x: "right",
        y: "top",
      },
    });

    $("#registration-form").on("submit", function(e) {
      e.preventDefault(); // ป้องกันการรีเฟรชหน้าเว็บ

      // ดึงค่าข้อมูลจากฟอร์ม
      const formData = {
        email: $('input[name="email"]').val(),
        password: $('input[name="password"]').val(),
        confirm_password: $('input[name="Confirmpassword"]').val(),
        user_owner_id: $('input[name="user_owner_id"]').val()
      };

      // ตรวจสอบข้อมูลก่อนส่ง (เช่น เช็คว่า password กับ confirm_password ตรงกัน)
      if (formData.password !== formData.confirm_password) {

        notyf.error('ยืนยันรหัสผ่านไม่ตรงกัน');

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

              const notyf = new Notyf({
                position: {
                  x: "right",
                  y: "top",
                },
              });

              notyf.success('สำเร็จ');

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

</body>

</html>