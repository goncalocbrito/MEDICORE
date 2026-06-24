<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function valor_nulo($valor)
{
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : $valor;
}

function utilizador_sessao()
{
    return $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema';
}

function selected_valor($atual, $valor)
{
    return (string) $atual === (string) $valor ? 'selected' : '';
}

$idUtilizador = id_from_request();
$mensagemSucesso = isset($_GET['criado']) ? 'Utilizador criado com sucesso.' : '';
$errosUtilizador = [];

if ($idUtilizador <= 0) {
    header('Location: lista_utilizadores.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = '';
    $confirmarPassword = '';

    // Campos obrigatórios
    if (trim($_POST['nomeUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "Nome" é obrigatório.';
    }
    if (trim($_POST['cartaoCidadaoUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "N.º Cartão de Cidadão" é obrigatório.';
    }
    if (trim($_POST['nifUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "NIF" é obrigatório.';
    }
    if (trim($_POST['dataNascimentoUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "Data de nascimento" é obrigatório.';
    }
    if (trim($_POST['emailUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "Email" é obrigatório.';
    }
    if (trim($_POST['telefoneUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "Telefone" é obrigatório.';
    }
    if (trim($_POST['moradaUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "Morada" é obrigatório.';
    }
    if (trim($_POST['codigoPostalUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "Código postal" é obrigatório.';
    }
    if (trim($_POST['localidadeUtilizador'] ?? '') === '') {
        $errosUtilizador[] = 'O campo "Localidade" é obrigatório.';
    }

    // Validações de formato
    if ($erro = validar_cartao_cidadao($_POST['cartaoCidadaoUtilizador'] ?? '')) {
        $errosUtilizador[] = $erro;
    }
    if ($erro = validar_nif($_POST['nifUtilizador'] ?? '')) {
        $errosUtilizador[] = $erro;
    }
    if ($erro = validar_email($_POST['emailUtilizador'] ?? '', true)) {
        $errosUtilizador[] = $erro;
    }
    if ($erro = validar_telefone($_POST['telefoneUtilizador'] ?? '')) {
        $errosUtilizador[] = $erro;
    }
    if ($erro = validar_codigo_postal($_POST['codigoPostalUtilizador'] ?? '')) {
        $errosUtilizador[] = $erro;
    }

    // Duplicado cartão cidadão
    $ccVal = trim($_POST['cartaoCidadaoUtilizador'] ?? '');
    if ($ccVal !== '' && empty($errosUtilizador)) {
        $stmtCC = $pdo->prepare("SELECT COUNT(*) FROM utilizadores WHERE cartao_cidadao = :cc AND isActive = 1 AND id_utilizador != :id");
        $stmtCC->execute([':cc' => $ccVal, ':id' => $idUtilizador]);
        if ((int) $stmtCC->fetchColumn() > 0) {
            $errosUtilizador[] = 'Já existe um utilizador registado com o N.º Cartão de Cidadão "' . htmlspecialchars($ccVal) . '". O número deve ser único.';
        }
    }


    if (empty($errosUtilizador)) {
        try {
            $pdo->beginTransaction();

            $passwordSql = '';
            $paramsPassword = [];

            if ($password !== '') {
                $passwordSql = ', password_hash = :password_hash';
                $paramsPassword[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $stmt = $pdo->prepare("
                UPDATE utilizadores
                SET
                    nome = :nome,
                    tipo_utilizador = :tipo_utilizador,
                    estado = :estado,
                    cartao_cidadao = :cartao_cidadao,
                    nif = :nif,
                    data_nascimento = :data_nascimento,
                    email = :email,
                    telefone = :telefone,
                    morada = :morada,
                    codigo_postal = :codigo_postal,
                    localidade = :localidade,
                    username = :username,
                    atualizado_por = :atualizado_por
                    $passwordSql
                WHERE id_utilizador = :id_utilizador
                  AND isActive = 1
            ");

            $stmt->execute(array_merge([
                ':nome' => trim($_POST['nomeUtilizador'] ?? ''),
                ':tipo_utilizador' => trim($_POST['tipoUtilizador'] ?? ''),
                ':estado' => trim($_POST['estadoUtilizador'] ?? 'Ativo'),
                ':cartao_cidadao' => trim($_POST['cartaoCidadaoUtilizador'] ?? ''),
                ':nif' => valor_nulo($_POST['nifUtilizador'] ?? null),
                ':data_nascimento' => valor_nulo($_POST['dataNascimentoUtilizador'] ?? null),
                ':email' => trim($_POST['emailUtilizador'] ?? ''),
                ':telefone' => valor_nulo($_POST['telefoneUtilizador'] ?? null),
                ':morada' => valor_nulo($_POST['moradaUtilizador'] ?? null),
                ':codigo_postal' => valor_nulo($_POST['codigoPostalUtilizador'] ?? null),
                ':localidade' => valor_nulo($_POST['localidadeUtilizador'] ?? null),
                ':username' => trim($_POST['usernameUtilizador'] ?? ''),
                ':atualizado_por' => utilizador_sessao(),
                ':id_utilizador' => $idUtilizador
            ], $paramsPassword));

            $stmt = $pdo->prepare("
                INSERT INTO historico_utilizadores (
                    id_utilizador_alvo, codigo_utilizador, acao, observacoes, realizado_por
                ) VALUES (
                    :id_utilizador_alvo, :codigo_utilizador, 'edicao_utilizador', :observacoes, :realizado_por
                )
            ");
            $stmt->execute([
                ':id_utilizador_alvo' => $idUtilizador,
                ':codigo_utilizador' => $_POST['codigoUtilizador'] ?? null,
                ':observacoes' => 'Ficha do utilizador atualizada.',
                ':realizado_por' => utilizador_sessao()
            ]);

            $pdo->commit();
            $mensagemSucesso = 'Utilizador atualizado com sucesso.';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errosUtilizador[] = 'Erro ao atualizar utilizador: ' . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE id_utilizador = :id AND isActive = 1 LIMIT 1");
$stmt->execute([':id' => $idUtilizador]);
$utilizador = $stmt->fetch();

if (!$utilizador) {
    header('Location: lista_utilizadores.php');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private ficha-equipamento-page">
    <div class="ficha-toolbar">
        <a href="lista_utilizadores.php" class="btn btn-voltar btn-voltar-lista-com-confirmacao">
            <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
        </a>

        <button type="submit" class="btn btn-guardar" form="formFichaUtilizador">
            <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
        </button>
    </div>

    <?php if ($mensagemSucesso): ?>
        <div class="form-alerta-sucesso" role="alert">
            <strong>
                <i class="fa-solid fa-circle-check me-2"></i>
                <?php echo h($mensagemSucesso); ?>
            </strong>
        </div>
    <?php endif; ?>

    <?php if (!empty($errosUtilizador)): ?>
        <div class="alert alert-danger" role="alert">
            <strong><i class="fa-solid fa-triangle-exclamation me-2"></i> Erro</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errosUtilizador as $erro): ?>
                    <li><?php echo h($erro); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="form-equipamento form-ficha-equipamento modo-edicao"
          id="formFichaUtilizador"
          action="ficha_utilizador.php?ref=<?php echo url_ref($idUtilizador); ?>"
          method="post"
          novalidate>

        <input type="hidden" id="modoFormularioUtilizador" name="modoFormularioUtilizador" value="editar">

        <div class="ficha-area">
            <ul class="nav nav-tabs ficha-tabs" id="tabsFichaUtilizador" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#identificacao" type="button">Identificação</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contactos" type="button">Contactos</button></li>
            </ul>

            <div class="tab-content ficha-tab-content">
                <div class="tab-pane fade show active" id="identificacao" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Identificação do Utilizador</h4>
                        <p>Dados principais do utilizador.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label" for="codigoUtilizador">Código interno</label>
                            <input type="text" class="form-control campo-ficha campo-bloqueado" id="codigoUtilizador" name="codigoUtilizador" value="<?php echo h($utilizador['codigo_utilizador']); ?>" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="nomeUtilizador">Nome *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="nomeUtilizador" name="nomeUtilizador" value="<?php echo h($utilizador['nome']); ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="tipoUtilizador">Tipo *</label>
                            <select class="form-select campo-ficha campo-editavel" id="tipoUtilizador" name="tipoUtilizador" required>
                                <option value="Administrador" <?php echo selected_valor($utilizador['tipo_utilizador'], 'Administrador'); ?>>Administrador</option>
                                <option value="Engenheiro" <?php echo selected_valor($utilizador['tipo_utilizador'], 'Engenheiro'); ?>>Engenheiro</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="estadoUtilizador">Estado *</label>
                            <select class="form-select campo-ficha campo-editavel" id="estadoUtilizador" name="estadoUtilizador" required>
                                <option value="Ativo" <?php echo selected_valor($utilizador['estado'], 'Ativo'); ?>>Ativo</option>
                                <option value="Pendente" <?php echo selected_valor($utilizador['estado'], 'Pendente'); ?>>Pendente</option>
                                <option value="Inativo" <?php echo selected_valor($utilizador['estado'], 'Inativo'); ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="cartaoCidadaoUtilizador">N.º Cartão de Cidadão * <small class="text-muted">(8 dígitos)</small></label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="cartaoCidadaoUtilizador" name="cartaoCidadaoUtilizador" value="<?php echo h($utilizador['cartao_cidadao']); ?>" maxlength="8" oninput="this.value=this.value.replace(/\D/g,'')" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="nifUtilizador">NIF * <small class="text-muted">(9 dígitos)</small></label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="nifUtilizador" name="nifUtilizador" value="<?php echo h($utilizador['nif']); ?>" maxlength="9" oninput="this.value=this.value.replace(/\D/g,'')" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="dataNascimentoUtilizador">Data de nascimento *</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataNascimentoUtilizador" name="dataNascimentoUtilizador" value="<?php echo h($utilizador['data_nascimento']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="contactos" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Contactos</h4>
                        <p>Contactos profissionais e morada do utilizador.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="emailUtilizador">Email *</label>
                            <input type="email" class="form-control campo-ficha campo-editavel" id="emailUtilizador" name="emailUtilizador" value="<?php echo h($utilizador['email']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="telefoneUtilizador">Telefone * <small class="text-muted">(9 dígitos)</small></label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="telefoneUtilizador" name="telefoneUtilizador" value="<?php echo h($utilizador['telefone']); ?>" maxlength="9" oninput="this.value=this.value.replace(/\D/g,'')" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="localidadeUtilizador">Localidade *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="localidadeUtilizador" name="localidadeUtilizador" value="<?php echo h($utilizador['localidade']); ?>" maxlength="100" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="localidadeUtilizador" data-max="100">0 / 100 caracteres</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="moradaUtilizador">Morada *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="moradaUtilizador" name="moradaUtilizador" value="<?php echo h($utilizador['morada']); ?>" maxlength="200" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="moradaUtilizador" data-max="200">0 / 200 caracteres</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="codigoPostalUtilizador">Código postal * <small class="text-muted">(ex: 1234-567)</small></label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="codigoPostalUtilizador" name="codigoPostalUtilizador" value="<?php echo h($utilizador['codigo_postal']); ?>" maxlength="8" placeholder="1234-567" required>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="usernameUtilizador" value="<?php echo h($utilizador['username']); ?>">

            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
