<?php
require_once __DIR__ . '/../../includes/funcoes.php';
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

function icone_permissao($codigo)
{
    return [
        'dashboard' => 'fa-chart-line',
        'equipamentos' => 'fa-stethoscope',
        'calibracoes' => 'fa-screwdriver-wrench',
        'localizacoes' => 'fa-location-dot',
        'fornecedores' => 'fa-truck-medical',
        'utilizadores' => 'fa-user',
        'acessorios' => 'fa-plug-circle-bolt',
        'consumiveis' => 'fa-boxes-stacked',
        'documentos' => 'fa-folder-open',
        'backoffice' => 'fa-pen-to-square'
    ][$codigo] ?? 'fa-lock';
}

$idUtilizador = id_from_request();
$mensagemSucesso = '';
$mensagemErro = '';

if ($idUtilizador <= 0) {
    header('Location: lista_utilizadores.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['passwordUtilizador'] ?? '');
    $confirmarPassword = trim($_POST['confirmarPasswordUtilizador'] ?? '');

    if (($password !== '' || $confirmarPassword !== '') && $password !== $confirmarPassword) {
        $mensagemErro = 'A password e a confirmação da password não coincidem.';
    } else {
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
                    numero_mecanografico = :numero_mecanografico,
                    email = :email,
                    telefone = :telefone,
                    extensao = :extensao,
                    morada = :morada,
                    codigo_postal = :codigo_postal,
                    localidade = :localidade,
                    username = :username,
                    perfil_acesso = :perfil_acesso,
                    data_ativacao = :data_ativacao,
                    validade_acesso = :validade_acesso,
                    departamento = :departamento,
                    funcao = :funcao,
                    superior_hierarquico = :superior_hierarquico,
                    edificio = :edificio,
                    piso = :piso,
                    data_admissao = :data_admissao,
                    observacoes = :observacoes,
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
                ':numero_mecanografico' => valor_nulo($_POST['numeroMecanograficoUtilizador'] ?? null),
                ':email' => trim($_POST['emailUtilizador'] ?? ''),
                ':telefone' => valor_nulo($_POST['telefoneUtilizador'] ?? null),
                ':extensao' => valor_nulo($_POST['extensaoUtilizador'] ?? null),
                ':morada' => valor_nulo($_POST['moradaUtilizador'] ?? null),
                ':codigo_postal' => valor_nulo($_POST['codigoPostalUtilizador'] ?? null),
                ':localidade' => valor_nulo($_POST['localidadeUtilizador'] ?? null),
                ':username' => trim($_POST['usernameUtilizador'] ?? ''),
                ':perfil_acesso' => valor_nulo($_POST['perfilAcessoUtilizador'] ?? null),
                ':data_ativacao' => valor_nulo($_POST['dataAtivacaoUtilizador'] ?? null),
                ':validade_acesso' => valor_nulo($_POST['validadeAcessoUtilizador'] ?? null),
                ':departamento' => valor_nulo($_POST['departamentoUtilizador'] ?? null),
                ':funcao' => valor_nulo($_POST['funcaoUtilizador'] ?? null),
                ':superior_hierarquico' => valor_nulo($_POST['superiorHierarquicoUtilizador'] ?? null),
                ':edificio' => valor_nulo($_POST['edificioUtilizador'] ?? null),
                ':piso' => valor_nulo($_POST['pisoUtilizador'] ?? null),
                ':data_admissao' => valor_nulo($_POST['dataAdmissaoUtilizador'] ?? null),
                ':observacoes' => valor_nulo($_POST['observacoesUtilizador'] ?? null),
                ':atualizado_por' => utilizador_sessao(),
                ':id_utilizador' => $idUtilizador
            ], $paramsPassword));

            $permissoesSelecionadas = $_POST['permissoesUtilizador'] ?? [];

            $stmtPermissoes = $pdo->query("SELECT id_permissao, codigo_permissao FROM permissoes_sistema WHERE isActive = 1");
            $permissoesSistema = $stmtPermissoes->fetchAll();

            $stmtPermissaoAtual = $pdo->prepare("
                SELECT id_utilizador_permissao
                FROM utilizadores_permissoes
                WHERE id_utilizador = :id_utilizador
                  AND id_permissao = :id_permissao
                LIMIT 1
            ");
            $stmtInserirPermissao = $pdo->prepare("
                INSERT INTO utilizadores_permissoes (id_utilizador, id_permissao, isActive, atualizado_por)
                VALUES (:id_utilizador, :id_permissao, :isActive, :atualizado_por)
            ");
            $stmtAtualizarPermissao = $pdo->prepare("
                UPDATE utilizadores_permissoes
                SET isActive = :isActive,
                    atualizado_por = :atualizado_por
                WHERE id_utilizador_permissao = :id_utilizador_permissao
            ");

            foreach ($permissoesSistema as $permissao) {
                $ativo = in_array($permissao['codigo_permissao'], $permissoesSelecionadas, true) ? 1 : 0;

                $stmtPermissaoAtual->execute([
                    ':id_utilizador' => $idUtilizador,
                    ':id_permissao' => $permissao['id_permissao']
                ]);
                $idUtilizadorPermissao = $stmtPermissaoAtual->fetchColumn();

                if ($idUtilizadorPermissao) {
                    $stmtAtualizarPermissao->execute([
                        ':isActive' => $ativo,
                        ':atualizado_por' => utilizador_sessao(),
                        ':id_utilizador_permissao' => $idUtilizadorPermissao
                    ]);
                } else {
                    $stmtInserirPermissao->execute([
                        ':id_utilizador' => $idUtilizador,
                        ':id_permissao' => $permissao['id_permissao'],
                        ':isActive' => $ativo,
                        ':atualizado_por' => utilizador_sessao()
                    ]);
                }
            }

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

            $mensagemErro = 'Erro ao atualizar utilizador: ' . $e->getMessage();
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

$stmt = $pdo->query("
    SELECT id_permissao, codigo_permissao, nome_permissao, descricao
    FROM permissoes_sistema
    WHERE isActive = 1
    ORDER BY id_permissao ASC
");
$permissoesSistema = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT ps.codigo_permissao
    FROM utilizadores_permissoes up
    INNER JOIN permissoes_sistema ps
        ON ps.id_permissao = up.id_permissao
    WHERE up.id_utilizador = :id
      AND up.isActive = 1
      AND ps.isActive = 1
");
$stmt->execute([':id' => $idUtilizador]);
$permissoesAtivas = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger rounded-4 fw-bold"><?php echo h($mensagemErro); ?></div>
    <?php endif; ?>

    <form class="form-equipamento form-ficha-equipamento modo-edicao"
          id="formFichaUtilizador"
          action="ficha_utilizador.php?ref=<?php echo url_ref($idUtilizador); ?>"
          method="post">

        <input type="hidden" id="modoFormularioUtilizador" name="modoFormularioUtilizador" value="editar">

        <div class="ficha-area">
            <ul class="nav nav-tabs ficha-tabs" id="tabsFichaUtilizador" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#identificacao" type="button">Identificação</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contactos" type="button">Contactos</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#acesso" type="button">Acesso</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#servico" type="button">Serviço</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#observacoes" type="button">Observações</button></li>
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
                            <label class="form-label" for="cartaoCidadaoUtilizador">N.º Cartão de Cidadão *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="cartaoCidadaoUtilizador" name="cartaoCidadaoUtilizador" value="<?php echo h($utilizador['cartao_cidadao']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="nifUtilizador">NIF</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="nifUtilizador" name="nifUtilizador" value="<?php echo h($utilizador['nif']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="dataNascimentoUtilizador">Data de nascimento</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataNascimentoUtilizador" name="dataNascimentoUtilizador" value="<?php echo h($utilizador['data_nascimento']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="numeroMecanograficoUtilizador">N.º mecanográfico</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="numeroMecanograficoUtilizador" name="numeroMecanograficoUtilizador" value="<?php echo h($utilizador['numero_mecanografico']); ?>">
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
                            <label class="form-label" for="telefoneUtilizador">Telefone</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="telefoneUtilizador" name="telefoneUtilizador" value="<?php echo h($utilizador['telefone']); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="extensaoUtilizador">Extensão</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="extensaoUtilizador" name="extensaoUtilizador" value="<?php echo h($utilizador['extensao']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="localidadeUtilizador">Localidade</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="localidadeUtilizador" name="localidadeUtilizador" value="<?php echo h($utilizador['localidade']); ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="moradaUtilizador">Morada</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="moradaUtilizador" name="moradaUtilizador" value="<?php echo h($utilizador['morada']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="codigoPostalUtilizador">Código postal</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="codigoPostalUtilizador" name="codigoPostalUtilizador" value="<?php echo h($utilizador['codigo_postal']); ?>">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="acesso" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Acesso ao Sistema</h4>
                        <p>Credenciais e menus disponíveis para o utilizador.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="usernameUtilizador">Nome de utilizador *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="usernameUtilizador" name="usernameUtilizador" value="<?php echo h($utilizador['username']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="passwordUtilizador">Nova password</label>
                            <input type="password" class="form-control campo-ficha campo-editavel" id="passwordUtilizador" name="passwordUtilizador" placeholder="Preencher apenas se quiser alterar">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="confirmarPasswordUtilizador">Confirmar nova password</label>
                            <input type="password" class="form-control campo-ficha campo-editavel" id="confirmarPasswordUtilizador" name="confirmarPasswordUtilizador" placeholder="Confirmar apenas se alterar">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="perfilAcessoUtilizador">Perfil de acesso</label>
                            <select class="form-select campo-ficha campo-editavel" id="perfilAcessoUtilizador" name="perfilAcessoUtilizador">
                                <option value="">Selecionar</option>
                                <option value="Acesso total" <?php echo selected_valor($utilizador['perfil_acesso'], 'Acesso total'); ?>>Acesso total</option>
                                <option value="Gestão técnica" <?php echo selected_valor($utilizador['perfil_acesso'], 'Gestão técnica'); ?>>Gestão técnica</option>
                                <option value="Consulta clínica" <?php echo selected_valor($utilizador['perfil_acesso'], 'Consulta clínica'); ?>>Consulta clínica</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="dataAtivacaoUtilizador">Data de ativação</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataAtivacaoUtilizador" name="dataAtivacaoUtilizador" value="<?php echo h($utilizador['data_ativacao']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="validadeAcessoUtilizador">Validade do acesso</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="validadeAcessoUtilizador" name="validadeAcessoUtilizador" value="<?php echo h($utilizador['validade_acesso']); ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Acessos aos menus do sistema</label>
                        <div class="permissoes-utilizador-opcoes">
                            <?php foreach ($permissoesSistema as $permissao): ?>
                                <div class="form-check permissao-utilizador-item">
                                    <input class="form-check-input permissao-utilizador campo-ficha campo-editavel"
                                           type="checkbox"
                                           id="permissao_<?php echo h($permissao['codigo_permissao']); ?>"
                                           name="permissoesUtilizador[]"
                                           value="<?php echo h($permissao['codigo_permissao']); ?>"
                                           <?php echo in_array($permissao['codigo_permissao'], $permissoesAtivas, true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="permissao_<?php echo h($permissao['codigo_permissao']); ?>">
                                        <i class="fa-solid <?php echo h(icone_permissao($permissao['codigo_permissao'])); ?>"></i>
                                        <?php echo h($permissao['nome_permissao']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="servico" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Serviço Hospitalar</h4>
                        <p>Serviço, função e posição interna.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="departamentoUtilizador">Serviço</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="departamentoUtilizador" name="departamentoUtilizador" value="<?php echo h($utilizador['departamento']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="funcaoUtilizador">Função</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="funcaoUtilizador" name="funcaoUtilizador" value="<?php echo h($utilizador['funcao']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="superiorHierarquicoUtilizador">Superior hierárquico</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="superiorHierarquicoUtilizador" name="superiorHierarquicoUtilizador" value="<?php echo h($utilizador['superior_hierarquico']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edificioUtilizador">Edifício</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="edificioUtilizador" name="edificioUtilizador" value="<?php echo h($utilizador['edificio']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pisoUtilizador">Piso</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="pisoUtilizador" name="pisoUtilizador" value="<?php echo h($utilizador['piso']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="dataAdmissaoUtilizador">Data de admissão</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataAdmissaoUtilizador" name="dataAdmissaoUtilizador" value="<?php echo h($utilizador['data_admissao']); ?>">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="observacoes" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Observações</h4>
                        <p>Notas administrativas ou técnicas sobre o utilizador.</p>
                    </div>

                    <textarea class="form-control campo-ficha campo-editavel" id="observacoesUtilizador" name="observacoesUtilizador" rows="7"><?php echo h($utilizador['observacoes']); ?></textarea>
                </div>
            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
