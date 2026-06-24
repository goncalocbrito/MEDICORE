<?php
require_once 'includes/funcoes.php';

start_session();
redirect_if_not_logged();

header('Location: ' . rota_inicial_utilizador());
exit;
?>

<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';
?>

<?php if (!empty($success_message)) : ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
        <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Conteúdo Principal -->
<main class="conteudo-private">
    <section class="dashboard-header">
        <div>
            <h2>Área Técnica MEDICORE</h2>
            <p>Escolha uma opção no menu para continuar.</p>
        </div>
    </section>

    <section class="dashboard-indicadores">
        <div class="indicador-card">
            <div class="indicador-icone">
                <i class="fa-solid fa-user-doctor"></i>
            </div>

            <div>
                <span>Utilizador autenticado</span>
                <h3><?php echo htmlspecialchars($_SESSION['nome_utilizador'] ?? 'Utilizador', ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($_SESSION['tipo_utilizador'] ?? 'Perfil', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>

        <div class="indicador-card">
            <div class="indicador-icone indicador-verde">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div>
                <span>Sessão ativa</span>
                <h3>OK</h3>
                <p>Acesso validado por sessão PHP.</p>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>