<?php
/* =========================================================
   LOGIN
   Carrega configurações e recupera mensagens temporárias
   guardadas na sessão pelo processa_login.php.
   ========================================================= */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../private/includes/funcoes.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

if (!empty($_SESSION['autenticado']) && !empty($_SESSION['utilizador'])) {
    header('Location: ' . rota_inicial_utilizador());
    exit;
}

$validation_errors = [];

if (!empty($_SESSION['validation_errors'])) {
    $validation_errors = $_SESSION['validation_errors'];
    unset($_SESSION['validation_errors']);
}

$server_error = '';

if (!empty($_SESSION['server_error'])) {
    $server_error = $_SESSION['server_error'];
    unset($_SESSION['server_error']);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>

    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>/public/assets/img/MEDICORE_icon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/login.css?v=<?php echo filemtime(__DIR__ . '/assets/css/login.css'); ?>">
</head>
<body>
    <main class="login-container">
        <section class="login-form-area">
            <img src="<?php echo BASE_URL; ?>/public/assets/img/MEDICORE_Official_Logo.png" alt="Logótipo MEDICORE" class="login-card-logo">

            <h1>Iniciar Sessão</h1>
            <p>Introduza as suas credenciais para aceder à área privada.</p>

            <?php if (!empty($validation_errors)): ?>
                <div class="mensagem-erro mostrar">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php foreach ($validation_errors as $error): ?>
                        <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($server_error)): ?>
                <div class="mensagem-erro mostrar">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div><?php echo htmlspecialchars($server_error, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <form name="formulario" id="loginForm" action="<?php echo BASE_URL; ?>/private/processa_login.php" method="post">
                <div class="form-group">
                    <label for="email">Utilizador</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="email" name="text_username" placeholder="Ex: engenheiro" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="text_password" placeholder="Introduza a password" required>
                    </div>
                </div>

                <div class="login-options">
                    <label><input type="checkbox" name="lembrar"> Lembrar sessão</label>
                    <a href="#">Esqueceu-se da password?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Entrar na Área Técnica
                </button>

                <!-- Botões de preenchimento automático, seguindo a lógica usada na Ficha 14. -->
                <div class="login-credenciais-rapidas">
                    <button type="button" id="preencher_admin" class="btn-credencial-login">
                        <i class="fa-solid fa-user-shield"></i>
                        Administrador
                    </button>

                    <button type="button" id="preencher_engenheiro" class="btn-credencial-login">
                        <i class="fa-solid fa-user-gear"></i>
                        Engenheiro
                    </button>
                    
                </div>
            </form>

            <a href="<?php echo BASE_URL; ?>/public/index.php" class="voltar-publico">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Voltar à página pública
            </a>
        </section>
    </main>

    <script src="<?php echo BASE_URL; ?>/public/assets/js/1230404.js"></script>
    <script>
        // Preenchimento automático para testes, seguindo a estrutura da Ficha 14.
        document.querySelector('#preencher_admin').addEventListener('click', function () {
            const formulario = document.forms['formulario'];
            formulario['text_username'].value = 'admin';
            formulario['text_password'].value = 'admin123';
        });

        document.querySelector('#preencher_engenheiro').addEventListener('click', function () {
            const formulario = document.forms['formulario'];
            formulario['text_username'].value = 'jferreira';
            formulario['text_password'].value = 'engenheiro123';
        });

    </script>
</body>
</html>