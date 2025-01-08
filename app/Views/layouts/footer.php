</div>
<!-- end page-wrapper -->

<!-- Javascript  -->
<!-- vendor js -->
<script src="<?php echo base_url('/assets/libs/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo base_url('/assets/libs/simplebar/simplebar.min.js'); ?>"></script>

<!-- <script src="assets/libs/apexcharts/apexcharts.min.js"></script> -->
<script src="<?php echo base_url('/assets/data/stock-prices.js'); ?>"></script>
<script src="<?php echo base_url('/assets/libs/jsvectormap/js/jsvectormap.min.js'); ?>"></script>
<script src="<?php echo base_url('/assets/libs/jsvectormap/maps/world.js'); ?>"></script>
<!-- <script src="assets/js/pages/index.init.js"></script> -->
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
<script src="<?php echo base_url('/assets/js/app.js'); ?>"></script>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<?php if (isset($js_critical)) {
    echo $js_critical;
}; ?>

<script>
    let _bodyElement = document.body;
    let _bodySize = _bodyElement.getAttribute("data-sidebar-size");
    let _messagecollapse = document.getElementById("message-collapse");
    if (_bodySize == "collapsed") {
        _messagecollapse.style.display = "none";
    }
</script>
</body>
<!--end body-->

</html>