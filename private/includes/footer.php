<?php
require_once __DIR__ . '/../../config/config.php';
?>

    <!-- jQuery -->
    <script src="<?php echo PRIVATE_ASSETS_URL; ?>/jquery/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <script src="<?php echo PRIVATE_ASSETS_URL; ?>/datatables/DataTables-1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo PRIVATE_ASSETS_URL; ?>/datatables/DataTables-1.13.1/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="<?php echo PRIVATE_ASSETS_URL; ?>/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- JavaScript do projeto -->
    <script src="<?php echo PRIVATE_ASSETS_URL; ?>/js/1230404.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/1230404.js'); ?>"></script>

</body>
</html>
