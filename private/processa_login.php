<?php
/* =========================================================
   PROCESSAMENTO DO LOGIN
   Valida as credenciais submetidas no login.php contra a
   tabela utilizadores e cria a sessao privada do MEDICORE.
   ========================================================= */

require_once __DIR__ . '/includes/funcoes.php';

start_session();

/* Este ficheiro so deve receber dados por POST. */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

/* Recolhe os campos do formulario de login. */
$username = trim($_POST['text_username'] ?? '');
$password = trim($_POST['text_password'] ?? '');
$validation_errors = [];

/* Permite login por username ou email. */
if ($username === '') {
    $validation_errors[] = 'Introduza o email ou nome de utilizador.';
}

if ($password === '') {
    $validation_errors[] = 'Introduza a password.';
}

if ($password !== '' && strlen($password) < 6) {
    $validation_errors[] = 'A password deve ter pelo menos 6 caracteres.';
}

if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

try {
    $pdo = medicore_pdo();

    /* Procura apenas utilizadores ativos. */
    $stmt = $pdo->prepare("
        SELECT *
        FROM utilizadores
        WHERE isActive = 1
          AND estado = 'Ativo'
          AND (username = :username OR email = :username)
        LIMIT 1
    ");
    $stmt->execute([':username' => $username]);
    $utilizador = $stmt->fetch();

    /* Credenciais rápidas usadas apenas para demonstração com os utilizadores da BD. */
    $credenciaisDemo = [
        'admin' => 'admin123',
        'admin@medicore.pt' => 'admin123',
        'jferreira' => 'engenheiro123',
        'joao.ferreira@medicore.pt' => 'engenheiro123',
        'amartins' => 'enfermeiro123',
        'ana.martins@medicore.pt' => 'enfermeiro123'
    ];

    $passwordValida = $utilizador
        && (
            password_verify($password, $utilizador['password_hash'])
            || (
                isset($credenciaisDemo[$username])
                && hash_equals($credenciaisDemo[$username], $password)
                && in_array($utilizador['username'], ['admin', 'jferreira', 'amartins'], true)
            )
        );

    if (!$passwordValida) {
        $_SESSION['server_error'] = 'Credenciais invalidas.';
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }

    session_regenerate_id(true);

    /* Guarda na sessao apenas dados essenciais ao funcionamento privado. */
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

    $stmt = $pdo->prepare("
        UPDATE utilizadores
        SET ultimo_login = NOW()
        WHERE id_utilizador = :id_utilizador
    ");
    $stmt->execute([':id_utilizador' => $utilizador['id_utilizador']]);

    header('Location: ' . rota_inicial_utilizador($utilizador['tipo_utilizador']));
    exit;
} catch (Throwable $e) {
    $_SESSION['server_error'] = 'Erro ao validar o login na base de dados.';
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}
?>
