<?php
require_once __DIR__ . '/../../config/config.php';
?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- JavaScript do projeto -->
    <script src="<?php echo PRIVATE_ASSETS_URL; ?>/js/1230404.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/1230404.js'); ?>"></script>

</body>
</html>
