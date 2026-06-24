<?php
require_once __DIR__ . '/../../config/config.php';

$fotoPerfil = $_SESSION['foto_perfil'] ?? '';

$fotoPerfilUrl = $fotoPerfil !== ''
    ? PRIVATE_ASSETS_URL . '/' . ltrim($fotoPerfil, '/')
    : '';
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
                <button class="btn dropdown-toggle user-avatar-button"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        title="Abrir perfil">
                    <?php if ($fotoPerfilUrl !== ''): ?>
                        <img src="<?php echo htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="Fotografia de perfil">
                    <?php else: ?>
                        <i class="fa-regular fa-user"></i>
                    <?php endif; ?>
                </button>

                <ul class="dropdown-menu dropdown-menu-end perfil-dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/private/views/perfil/perfil.php">
                            <i class="fa-solid fa-user-gear me-2"></i> Perfil
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/public/logout.php">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Terminar sessão
                        </a>
                    </li>
                </ul>                
            </div>
        </div>

    </div>
</header>

<?php
$erroAcessoFlash = $_SESSION['erro_acesso'] ?? '';
unset($_SESSION['erro_acesso']);
?>
