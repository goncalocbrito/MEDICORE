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

    <!-- Dados de teste (remover em produção) -->
    <script src="<?php echo PRIVATE_ASSETS_URL; ?>/js/dados_teste.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/dados_teste.js'); ?>"></script>

    <div class="modal fade" id="modalSairSemGuardar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-remocao">
                <div class="modal-header modal-header-remocao">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Alterações não guardadas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-0">
                        Existem alterações nesta ficha que ainda não foram guardadas.
                        Se voltar à lista, essas alterações serão perdidas.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Cancelar
                    </button>

                    <a href="#" class="btn btn-guardar" id="btnConfirmarSairSemGuardar">
                        <i class="fa-solid fa-arrow-left me-2"></i>
                        Confirmar
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
