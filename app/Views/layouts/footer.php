</div>
<!-- end page-wrapper -->

<!-- Javascript  -->
<!-- vendor js -->
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>

<!-- <script src="assets/libs/apexcharts/apexcharts.min.js"></script> -->
<script src="assets/data/stock-prices.js"></script>
<script src="assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
<script src="assets/libs/jsvectormap/maps/world.js"></script>
<!-- <script src="assets/js/pages/index.init.js"></script> -->
<script src="assets/js/app.js"></script>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<?php if (isset($js_critical)) {
    echo $js_critical;
}; ?>

<script>
    let bodyElement = document.body;
    let bodySize = bodyElement.getAttribute("data-sidebar-size");
    const messagecollapse = document.getElementById("message-collapse");
    if (bodySize == "collapsed") {
        messagecollapse.style.display = "none";
    } 
</script>
</body>
<!--end body-->

</html>