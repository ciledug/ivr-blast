<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a>

<!-- Vendor JS Files -->
<script src="{{ url('vendor/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ url('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!--
<script src="{{ url('vendor/chart.js/chart.umd.js') }}"></script>
-->
<script src="{{ url('vendor/echarts/echarts.min.js') }}"></script>
<script src="{{ url('vendor/quill/quill.min.js') }}"></script>
<!-- <script src="{{ url('vendor/simple-datatables/simple-datatables.js') }}"></script> -->
<script src="{{ url('vendor/tinymce/tinymce.min.js') }}"></script>
<script src="{{ url('vendor/php-email-form/validate.js') }}"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="//cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
<script src="//cdn.sheetjs.com/xlsx-0.19.1/package/dist/xlsx.full.min.js"></script>

<!-- Template Main JS File -->
<script src="{{ url('js/main.js') }}"></script>

<script type="text/javascript">
    function submitLogout(event) {
        event.preventDefault();
        document.getElementById('logout-form').submit();
    }
</script>

@stack('javascript')

</body>
</html>