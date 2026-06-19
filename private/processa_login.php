<?php
/* =========================================================
   PROCESSAMENTO DO LOGIN
   Estrutura baseada na Ficha 14: recebe o formulário por POST,
   valida os dados, confirma o utilizador na base de dados,
   guarda a sessão e redireciona para a área privada.
   ========================================================= */

require_once 'includes/funcoes.php';

start_session();

// --------------------------------------------------------------------
// SEGURANÇA: impede acesso direto a este script.
// Este ficheiro deve ser acedido apenas por submissão do formulário.
// --------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// --------------------------------------------------------------------
// RECOLHA DOS DADOS DO FORMULÁRIO
// --------------------------------------------------------------------
$username = isset($_POST['text_username']) ? trim($_POST['text_username']) : '';
$password = isset($_POST['text_password']) ? trim($_POST['text_password']) : '';
$usernameNormalizado = strtolower($username);

// --------------------------------------------------------------------
// VALIDAÇÃO DOS DADOS
// --------------------------------------------------------------------
$validation_errors = [];

if ($username === '') {
    $validation_errors[] = 'Introduza o email ou nome de utilizador.';
}

if ($username !== '' && strlen($username) < 3) {
    $validation_errors[] = 'O utilizador deve ter pelo menos 3 caracteres.';
}

if ($password === '') {
    $validation_errors[] = 'Introduza a password.';
}

if ($password !== '' && (strlen($password) < 6 || strlen($password) > 30)) {
    $validation_errors[] = 'A password deve ter entre 6 e 30 caracteres.';
}

// Se existirem erros, guarda-os na sessão e volta ao login.
if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// --------------------------------------------------------------------
// VALIDAÇÃO DO UTILIZADOR NA BASE DE DADOS
// --------------------------------------------------------------------
try {
    $ligacao = medicore_pdo();

    $comando = $ligacao->prepare("
        SELECT *
        FROM utilizadores
        WHERE isActive = 1
          AND estado = 'Ativo'
          AND (LOWER(username) = :username OR LOWER(email) = :username)
        LIMIT 1
    ");

    $comando->execute([
        ':username' => $usernameNormalizado
    ]);

    $utilizador = $comando->fetch();

    // A password é sempre validada contra o hash guardado na base de dados.
    // As credenciais de teste, quando usadas, são preenchidas apenas no login.php.
    if (!$utilizador || !password_verify($password, $utilizador['password_hash'])) {
        $_SESSION['server_error'] = 'Login inválido.';
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }

    // --------------------------------------------------------------------
    // LOGIN BEM-SUCEDIDO: guardar dados essenciais na sessão
    // --------------------------------------------------------------------
    $_SESSION = [];
    session_regenerate_id(true);

    $_SESSION['autenticado'] = true;
    $_SESSION['utilizador'] = $utilizador['username'];
    $_SESSION['username'] = $utilizador['username'];
    $_SESSION['id_utilizador'] = $utilizador['id_utilizador'];
    $_SESSION['codigo_utilizador'] = $utilizador['codigo_utilizador'];
    $_SESSION['nome'] = $utilizador['nome'];
    $_SESSION['nome_utilizador'] = $utilizador['nome'];
    $_SESSION['tipo_utilizador'] = $utilizador['tipo_utilizador'];
    $_SESSION['email_utilizador'] = $utilizador['email'];
    $_SESSION['permissoes_utilizador'] = permissoes_por_tipo_utilizador($utilizador['tipo_utilizador']);

    // Atualiza a data/hora do último login.
    $stmt = $ligacao->prepare("
        UPDATE utilizadores
        SET ultimo_login = NOW()
        WHERE id_utilizador = :id_utilizador
    ");
    $stmt->execute([
        ':id_utilizador' => $utilizador['id_utilizador']
    ]);
} catch (PDOException $e) {
    $_SESSION['server_error'] = 'Erro ao ligar à base de dados.';
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// Redireciona para a página principal privada, tal como na Ficha 14.
header('Location: ' . BASE_URL . '/private/home.php');
exit;
?>