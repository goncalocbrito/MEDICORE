<?php
require_once __DIR__ . '/../../config/config.php';
?>

<!-- Navbar superior -->
    <header class="container-fluid top-header">

        <div class="row align-items-center">

            <!-- Logo e Nome -->
            <div class="col-md-4 d-flex align-items-center p-3">
                <a href="index.html">
                    <img src="<?php echo PRIVATE_ASSETS_URL; ?>/img/MEDICORE_logotipo_branco.png"
                        alt="Logo da MEDICORE"
                        class="logo-private">
                </a>

            </div>

            <!-- Mensagem central -->
            <div class="col-md-4 text-center p-3">
                <div class="mensagem-topo">
                    <i class="fa-solid fa-user-doctor me-2"></i>
                    <span>Bem-vindo, Sr. Engenheiro Gonçalo</span>
                </div>
            </div>
            
            <!-- Utilizador -->
            <div class="col-md-4 text-md-end p-3">

                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle user-button"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="fa-regular fa-user me-2"></i> Eng. Biomédico
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>
                            <a class="dropdown-item" href="perfil.php">
                                <i class="fa-solid fa-user-gear me-2"></i> Perfil técnico
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
                            <a class="dropdown-item" href="logout.php">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Sair
                            </a>
                        </li>

                    </ul>

                </div>
            </div>
        </div>

    </header>