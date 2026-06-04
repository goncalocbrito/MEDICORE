<?php
/* =========================================================
   PROCESSAMENTO DO LOGIN
   Recebe os dados enviados pelo formulario, valida os campos,
   simula a autenticacao e cria a sessao do utilizador.
   ========================================================= */

/* Carrega as funcoes de sessao usadas em toda a area privada. */
require_once __DIR__ . '/includes/funcoes.php';

/* Inicia a sessao para guardar mensagens e dados do utilizador. */
start_session();

/* Garante que este ficheiro so e executado apos submissao por POST. */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

/* =========================================================
   RECOLHA DOS DADOS DO FORMULARIO
   Os nomes devem coincidir com os atributos name do login.php.
   ========================================================= */
$username = isset($_POST['text_username']) ? trim($_POST['text_username']) : '';
$password = isset($_POST['text_password']) ? trim($_POST['text_password']) : '';

/* =========================================================
   VALIDACAO DOS DADOS
   Guarda mensagens de erro para apresentar novamente no login.
   ========================================================= */
$validation_errors = [];

if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    $validation_errors[] = 'O username tem que ser um email valido.';
}

if (strlen($username) < 5 || strlen($username) > 50) {
    $validation_errors[] = 'O username deve ter entre 5 e 50 caracteres.';
}

if (strlen($password) < 6 || strlen($password) > 12) {
    $validation_errors[] = 'A password deve ter entre 6 e 12 caracteres.';
}

/* Se existirem erros, guarda-os na sessao e volta ao formulario. */
if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;

    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

/* =========================================================
   SIMULACAO DE LOGIN
   Mais tarde esta parte sera substituida por consulta a base de dados.
   Neste momento, qualquer login com dados validos entra.
   ========================================================= */
$result = [];
$result['status'] = 1;

/* Se o login for invalido, guarda uma mensagem de erro de servidor. */
if (!$result['status']) {
    $_SESSION['server_error'] = 'Login invalido.';

    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

/* =========================================================
   LOGIN BEM-SUCEDIDO
   Guarda os dados essenciais do utilizador na sessao.
   ========================================================= */
$_SESSION['autenticado'] = true;
$_SESSION['utilizador'] = $username;
$_SESSION['nome_utilizador'] = 'Administrador MEDICORE';
$_SESSION['tipo_utilizador'] = 'Administrador';
$_SESSION['success_message'] = 'Sessao iniciada com sucesso.';

/* Redireciona para a pagina inicial privada. */
header('Location: ' . BASE_URL . '/private/index.php');
exit;
?>
