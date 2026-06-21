<?php
require_once __DIR__ . '/../../config/config.php';
?>

<!-- Navbar superior -->
<header class="container-fluid top-header">
    <div class="row align-items-center">

        <!-- Logo e nome da aplicacao -->
        <div class="col-md-4 d-flex align-items-center p-3">
            <a href="<?php echo BASE_URL; ?>/private/index.php">
                <img src="<?php echo PRIVATE_ASSETS_URL; ?>/img/MEDICORE_logotipo_branco.png"
                     alt="Logo da MEDICORE"
                     class="logo-private">
            </a>
        </div>

        <!-- Mensagem central com o nome do utilizador autenticado -->
        <div class="col-md-4 text-center p-3">
            <div class="mensagem-topo">
                <i class="fa-solid fa-user-doctor me-2"></i>
                <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome_utilizador'] ?? 'Utilizador'); ?></span>
            </div>
        </div>

        <!-- Menu de utilizador -->
        <div class="col-md-4 text-md-end p-3">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle user-button"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <i class="fa-regular fa-user me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['tipo_utilizador'] ?? 'Utilizador'); ?>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="perfil.php">
                            <i class="fa-solid fa-user-gear me-2"></i> Perfil tecnico
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="alterar_password.php">
                            <i class="fa-solid fa-key me-2"></i> Alterar password
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/public/logout.php">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Terminar sessao
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</header>

<?php if (!empty($_SESSION['erro_acesso'])): ?>
    <div class="container-fluid px-5 mt-3">
        <div class="alert alert-danger alerta-acesso-negado">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?php echo htmlspecialchars($_SESSION['erro_acesso'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    </div>

    <?php unset($_SESSION['erro_acesso']); ?>
<?php endif; ?>
