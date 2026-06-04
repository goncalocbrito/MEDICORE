<?php
/* =========================================================
   LOGIN
   Carrega configuracoes e recupera mensagens temporarias
   guardadas na sessao pelo processa_login.php.
   ========================================================= */

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

    <!-- favicon -->
    <link rel="shortcut icon" href="assets/img/MEDICORE_icon.png" type="image/png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/fontawesome/all.min.css">

    <!-- estilos da página -->
    <link rel="stylesheet" href="assets/css/login.css?v=4">
</head>

<body>

    <main class="login-container">

        <section class="login-form-area">
            <img src="assets/img/MEDICORE_Official_Logo.png"
                 alt="Logótipo MEDICORE"
                 class="login-card-logo">

            <h1>Iniciar Sessão</h1>

            <p>
                Introduza as suas credenciais para aceder à área privada.
            </p>

            <?php if (!empty($validation_errors)): ?>
                <div class="mensagem-erro mostrar">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php foreach ($validation_errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($server_error)): ?>
                <div class="mensagem-erro mostrar">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div><?php echo htmlspecialchars($server_error); ?></div>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="../private/processa_login.php" method="post">

                <div class="form-group">
                    <label for="email">Email / Utilizador</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input 
                            type="text" 
                            id="email" 
                            name="text_username" 
                            placeholder="Ex: engenheiro@medicore.pt" 
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="text_password" 
                            placeholder="Introduza a password" 
                            required
                        >
                    </div>
                </div>

                <div class="login-options">
                    <label>
                        <input type="checkbox" name="lembrar">
                        Lembrar sessão
                    </label>

                    <a href="#">Esqueceu-se da password?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Entrar na Área Técnica
                </button>

            </form>

            <a href="index.php" class="voltar-publico">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Voltar à página pública
            </a>
        </section>

    </main>

    <script src="assets/js/1230404.js"></script>

</body>
</html>
