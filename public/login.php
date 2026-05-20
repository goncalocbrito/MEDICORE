<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDICORE | Login</title>

    <!-- favicon -->
    <link rel="shortcut icon" href="assets/img/MEDICORE_icon.png" type="image/png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/fontawesome/all.min.css">

    <!-- estilos da página -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <main class="login-container">

        <section class="login-info">
            <img src="assets/img/MEDICORE_logo_white.png"
                 alt="Logótipo MEDICORE"
                 class="login-logo">

            <h1>Área Restrita MEDICORE</h1>

            <p>
                Acesso reservado à equipa técnica para gestão de equipamentos médicos,
                fornecedores, localizações, documentos e indicadores hospitalares.
            </p>

            <ul class="login-beneficios">
                <li>
                    <i class="fa-solid fa-check-circle"></i>
                    Gestão técnica de equipamentos hospitalares
                </li>

                <li>
                    <i class="fa-solid fa-check-circle"></i>
                    Consulta de estados, criticidade e documentação
                </li>

                <li>
                    <i class="fa-solid fa-check-circle"></i>
                    Backoffice para acompanhamento do inventário
                </li>
            </ul>
        </section>

        <section class="login-form-area">
            <h2>Iniciar Sessão</h2>

            <p>
                Introduza as suas credenciais para aceder à área privada.
            </p>

            <div class="mensagem-erro" id="mensagemErro">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Credenciais inválidas. Tente novamente.
            </div>

            <form id="loginForm">

                <div class="form-group">
                    <label for="email">Email / Utilizador</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text"
                               id="email"
                               name="email"
                               placeholder="Ex: engenheiro@medicore.pt"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Introduza a password"
                               required>
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

            <div class="credenciais-teste">
                <strong>Credenciais temporárias:</strong><br>
                Utilizador: <strong>admin</strong><br>
                Password: <strong>1234</strong>
            </div>
        </section>

    </main>

    <script src="assets/js/1230404.js"></script>

</body>
</html>