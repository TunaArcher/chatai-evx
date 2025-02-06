<!DOCTYPE html>
<html lang="th">

<head>
    <meta name="facebook-domain-verification" content="hmgpdjforybq3d41qxbf3thstnf3z4" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoConX | Policy</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
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
            background-color: #00c7e6 !important;
            border: none;
        }

        .btn-success:hover {
            background-color: #008599 !important;
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
        /* Floating Label Container */
        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        /* Input Field */
        .form-control {
            height: 48px;
            font-size: 16px;
            padding: 10px 12px;
            border: 1px solid #6f7780;
            border-radius: 6px;
            transition: all 0.3s ease-in-out;
        }

        /* Floating Label */
        .form-label {
            position: absolute;
            top: 12px;
            left: 12px;
            font-size: 14px;
            color: #6f7780;
            background: #ffffff;
            padding: 0 5px;
            transition: all 0.3s ease-in-out;
            pointer-events: none;
            /* ทำให้ไม่สามารถคลิกที่ Label ได้ */
        }

        /* เมื่อ Input ถูก Focus หรือมีข้อความ */
        .form-control:focus+.form-label,
        .form-control:not(:placeholder-shown)+.form-label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #00c7e6;
            /* เปลี่ยนสีเมื่อ Active */
        }

        /* เพิ่มเงาเมื่อ Focus */
        .form-control:focus {
            border: 2px solid #00c7e6;
            box-shadow: 0 0 5px rgba(16, 163, 127, 0.5);
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
</head>

<body>
    <!-- โลโก้อยู่ด้านบนสุด -->
    <div class="logo">
        <img src="<?php echo base_url('/assets/images/conXx.png'); ?>" alt="Logo">
    </div>

    <!-- Login Container -->
    <div class="container">
        <h1>AutoConX - Privacy Policy</h1>
        <p><strong>Effective Date:</strong> 1 Jan 2025</p>

        <h2>Introduction</h2>
        <p>AutoConX (<a href="https://autoconx.app">https://autoconx.app</a>) is a chatbot automation platform designed to facilitate customer interactions through Facebook Messenger. To provide seamless functionality, AutoConX requests specific Meta Developer Permissions to access and manage Facebook Pages on behalf of users.</p>

        <h2>Requested Permissions</h2>
        <p>AutoConX requests the following permissions from Meta:</p>
        <ul>
            <li><strong>pages_show_list:</strong> Allows AutoConX to display a list of pages the user manages.</li>
            <li><strong>pages_manage_metadata:</strong> Enables AutoConX to access metadata for managed pages.</li>
            <li><strong>pages_messaging:</strong> Grants AutoConX the ability to send and receive messages on behalf of a Facebook Page.</li>
        </ul>

        <h2>How We Use These Permissions</h2>
        <p>AutoConX uses these permissions solely to enhance the chatbot experience by enabling automated messaging, retrieving relevant page metadata, and ensuring a smooth user experience. We do not share, sell, or misuse the collected data.</p>

        <h2>Data Privacy & Security</h2>
        <p>We are committed to protecting user privacy and adhere to Meta's data protection policies. Any data accessed via these permissions is securely stored and only used for its intended purpose. Users can revoke permissions at any time via Facebook settings.</p>

        <h2>Contact Information</h2>
        <p>For any inquiries regarding this policy or data privacy concerns, please contact us at:</p>
        <p><strong>Email:</strong> <a href="mailto:account@unityx.group">account@unityx.group</a></p>
        <p><strong>Website:</strong> <a href="https://autoconx.app">https://autoconx.app</a></p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p title="UnityX Co.,Ltd.">
            &copy; <script>
                document.write(new Date().getFullYear())
            </script> บริษัท ยูนิตี้เอ็กซ์ จํากัด
            <br>
            <a href="#">Terms of Service</a> | <a href="<?php echo base_url('/policy'); ?>">Privacy Policy</a>
        </p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // เมื่อกดปุ่ม submit
            $("#login-form").on("submit", function(e) {
                e.preventDefault(); // ป้องกันการรีเฟรชหน้าเว็บ
                const email = $("#email").val().trim(); // ดึงค่า email ที่ผู้ใช้ป้อน

                if (email) {
                    // หากกรอก email แล้ว ส่งค่าไปยัง URL หน้าถัดไป
                    const targetUrl = `password?email=${encodeURIComponent(email)}`;
                    window.location.href = targetUrl; // Redirect ไปยัง URL พร้อมค่า email
                } else {
                    // หากช่อง email ว่าง
                    alert("กรุณากรอกที่อยู่อีเมล");
                }
            });
        });
    </script>
</body>

</html>