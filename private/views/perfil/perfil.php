<?php
require_once __DIR__ . '/../../includes/funcoes.php';

redirect_if_not_logged();

$pdo = medicore_pdo();
$idUtilizador = (int) ($_SESSION['id_utilizador'] ?? 0);

$mensagemSucesso = '';
$mensagemErro = '';

$stmt = $pdo->prepare("
    SELECT id_utilizador, nome, username, email, password_hash, foto_perfil
    FROM utilizadores
    WHERE id_utilizador = :id_utilizador
      AND isActive = 1
    LIMIT 1
");
$stmt->execute([
    ':id_utilizador' => $idUtilizador
]);
$utilizador = $stmt->fetch();

if (!$utilizador) {
    header('Location: ' . BASE_URL . '/public/logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $passwordAtual = trim($_POST['password_atual'] ?? '');
    $novaPassword = trim($_POST['nova_password'] ?? '');
    $confirmarPassword = trim($_POST['confirmar_password'] ?? '');
    $fotoPerfil = $utilizador['foto_perfil'];

    if ($username === '') {
        $mensagemErro = 'O username é obrigatório.';
    } elseif (strlen($username) < 3) {
        $mensagemErro = 'O username deve ter pelo menos 3 caracteres.';
    }

    if ($mensagemErro === '') {
        $stmtUsername = $pdo->prepare("
            SELECT COUNT(*)
            FROM utilizadores
            WHERE username = :username
              AND id_utilizador <> :id_utilizador
              AND isActive = 1
        ");
        $stmtUsername->execute([
            ':username' => $username,
            ':id_utilizador' => $idUtilizador
        ]);

        if ((int) $stmtUsername->fetchColumn() > 0) {
            $mensagemErro = 'Já existe um utilizador com esse username.';
        }
    }

    $alterarPassword = $novaPassword !== '' || $confirmarPassword !== '' || $passwordAtual !== '';

    if ($mensagemErro === '' && $alterarPassword) {
        if ($passwordAtual === '') {
            $mensagemErro = 'Indique a password atual para alterar a password.';
        } elseif (!password_verify($passwordAtual, $utilizador['password_hash'])) {
            $mensagemErro = 'A password atual está incorreta.';
        } elseif (strlen($novaPassword) < 6) {
            $mensagemErro = 'A nova password deve ter pelo menos 6 caracteres.';
        } elseif ($novaPassword !== $confirmarPassword) {
            $mensagemErro = 'A confirmação da password não corresponde.';
        } elseif (password_verify($novaPassword, $utilizador['password_hash'])) {
            $mensagemErro = 'A nova password não pode ser igual à password atual.';
        }
    }

    if ($mensagemErro === '' && !empty($_FILES['foto_perfil']['name'])) {
        $ficheiro = $_FILES['foto_perfil'];

        if ($ficheiro['error'] !== UPLOAD_ERR_OK) {
            $mensagemErro = 'Erro ao carregar a fotografia.';
        } else {
            $extensao = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                $mensagemErro = 'A fotografia deve ser JPG, PNG ou WEBP.';
            } elseif ($ficheiro['size'] > 2 * 1024 * 1024) {
                $mensagemErro = 'A fotografia não pode ultrapassar 2MB.';
            } else {
                $pastaUpload = __DIR__ . '/../../assets/uploads/perfis';

                if (!is_dir($pastaUpload)) {
                    mkdir($pastaUpload, 0775, true);
                }

                $nomeFicheiro = 'perfil_' . $idUtilizador . '_' . time() . '.' . $extensao;
                $destino = $pastaUpload . '/' . $nomeFicheiro;

                if (!move_uploaded_file($ficheiro['tmp_name'], $destino)) {
                    $mensagemErro = 'Não foi possível guardar a fotografia.';
                } else {
                    $fotoPerfil = 'uploads/perfis/' . $nomeFicheiro;
                }
            }
        }
    }

    if ($mensagemErro === '') {
        $sql = "
            UPDATE utilizadores
            SET username = :username,
                foto_perfil = :foto_perfil
        ";

        $params = [
            ':username' => $username,
            ':foto_perfil' => $fotoPerfil,
            ':id_utilizador' => $idUtilizador
        ];

        if ($alterarPassword) {
            $sql .= ", password_hash = :password_hash";
            $params[':password_hash'] = password_hash($novaPassword, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id_utilizador = :id_utilizador";

        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute($params);

        $_SESSION['utilizador'] = $username;
        $_SESSION['username'] = $username;
        $_SESSION['foto_perfil'] = $fotoPerfil;

        $mensagemSucesso = 'Perfil atualizado com sucesso.';

        $stmt->execute([
            ':id_utilizador' => $idUtilizador
        ]);
        $utilizador = $stmt->fetch();
    }
}

$fotoAtual = !empty($utilizador['foto_perfil'])
    ? PRIVATE_ASSETS_URL . '/' . ltrim($utilizador['foto_perfil'], '/')
    : PRIVATE_ASSETS_URL . '/img/MEDICORE_icon.png';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Perfil</h2>
            <p class="subtitulo-pagina">
                Atualize a fotografia, o username e a password de acesso.
            </p>
        </div>

        <button type="submit" form="formPerfil" class="btn btn-guardar">
            <i class="fa-solid fa-floppy-disk me-2"></i>
            Guardar Alterações
        </button>
    </div>

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?php echo htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($mensagemSucesso): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="formPerfil" class="formulario-ficha">
        <div class="tabela-container">
            <div class="ficha-card-conteudo">
                <section>
                    <h3 class="titulo-seccao">Dados do Perfil</h3>
                    <p class="texto-ajuda">
                        A fotografia será apresentada no botão do perfil no topo da aplicação.
                    </p>

                    <hr>

                    <div class="row g-4 align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="perfil-preview">
                                <img id="previewFotoPerfil"
                                     src="<?php echo htmlspecialchars($fotoAtual, ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="Pré-visualização da fotografia">
                            </div>

                            <label for="fotoPerfil" class="form-label mt-3">Fotografia de perfil</label>
                            <input type="file"
                                   class="form-control"
                                   id="fotoPerfil"
                                   name="foto_perfil"
                                   accept="image/png,image/jpeg,image/webp">
                        </div>

                        <div class="col-md-8">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nome</label>
                                    <input type="text"
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($utilizador['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                           readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email"
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($utilizador['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                           readonly>
                                </div>

                                <div class="col-md-12">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text"
                                           class="form-control"
                                           id="username"
                                           name="username"
                                           value="<?php echo htmlspecialchars($utilizador['username'], ENT_QUOTES, 'UTF-8'); ?>"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label for="passwordAtual" class="form-label">Password atual</label>
                                    <input type="password"
                                           class="form-control"
                                           id="passwordAtual"
                                           name="password_atual">
                                </div>

                                <div class="col-md-4">
                                    <label for="novaPassword" class="form-label">Nova password</label>
                                    <input type="password"
                                           class="form-control"
                                           id="novaPassword"
                                           name="nova_password">
                                </div>

                                <div class="col-md-4">
                                    <label for="confirmarPassword" class="form-label">Confirmar password</label>
                                    <input type="password"
                                           class="form-control"
                                           id="confirmarPassword"
                                           name="confirmar_password">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const inputFoto = document.getElementById("fotoPerfil");
    const previewFoto = document.getElementById("previewFotoPerfil");

    if (!inputFoto || !previewFoto) return;

    inputFoto.addEventListener("change", function () {
        const ficheiro = inputFoto.files && inputFoto.files[0];

        if (!ficheiro) return;

        previewFoto.src = URL.createObjectURL(ficheiro);
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>