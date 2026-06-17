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

function gerar_codigo_utilizador(PDO $pdo)
{
    $stmt = $pdo->query("
        SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_utilizador, 5) AS UNSIGNED)), 0) + 1
        FROM utilizadores
        WHERE codigo_utilizador LIKE 'USR-%'
    ");

    return 'USR-' . str_pad((string) $stmt->fetchColumn(), 3, '0', STR_PAD_LEFT);
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

function permissoes_padrao($tipo)
{
    return [
        'Administrador' => ['dashboard', 'equipamentos', 'calibracoes', 'localizacoes', 'fornecedores', 'utilizadores', 'acessorios', 'consumiveis', 'documentos', 'backoffice'],
        'Engenheiro' => ['dashboard', 'equipamentos', 'calibracoes', 'localizacoes', 'fornecedores', 'acessorios', 'consumiveis', 'documentos'],
        'Enfermeiro' => ['dashboard', 'equipamentos', 'localizacoes', 'acessorios', 'consumiveis']
    ][$tipo] ?? [];
}

$chaveSessao = 'novo_utilizador';
$ficheiroAtual = 'novo_utilizador.php';

$etapas = ['identificacao', 'contactos', 'acesso', 'servico', 'observacoes'];

$nomesEtapas = [
    'identificacao' => 'Identificação',
    'contactos' => 'Contactos',
    'acesso' => 'Acesso',
    'servico' => 'Serviço',
    'observacoes' => 'Observações'
];

$camposPorEtapa = [
    'identificacao' => ['codigoUtilizador', 'nomeUtilizador', 'tipoUtilizador', 'estadoUtilizador', 'cartaoCidadaoUtilizador', 'nifUtilizador', 'dataNascimentoUtilizador', 'numeroMecanograficoUtilizador'],
    'contactos' => ['emailUtilizador', 'telefoneUtilizador', 'extensaoUtilizador', 'moradaUtilizador', 'codigoPostalUtilizador', 'localidadeUtilizador'],
    'acesso' => ['usernameUtilizador', 'passwordUtilizador', 'confirmarPasswordUtilizador', 'perfilAcessoUtilizador', 'dataAtivacaoUtilizador', 'validadeAcessoUtilizador'],
    'servico' => ['departamentoUtilizador', 'funcaoUtilizador', 'superiorHierarquicoUtilizador', 'edificioUtilizador', 'pisoUtilizador', 'dataAdmissaoUtilizador'],
    'observacoes' => ['observacoesUtilizador']
];

$camposObrigatorios = [
    'identificacao' => ['nomeUtilizador', 'tipoUtilizador', 'estadoUtilizador', 'cartaoCidadaoUtilizador'],
    'contactos' => ['emailUtilizador'],
    'acesso' => ['usernameUtilizador', 'passwordUtilizador', 'confirmarPasswordUtilizador', 'perfilAcessoUtilizador'],
    'servico' => ['departamentoUtilizador', 'funcaoUtilizador'],
    'observacoes' => []
];

$labelsCampos = [
    'nomeUtilizador' => 'Nome',
    'tipoUtilizador' => 'Tipo de utilizador',
    'estadoUtilizador' => 'Estado',
    'cartaoCidadaoUtilizador' => 'N.º Cartão de Cidadão',
    'emailUtilizador' => 'Email',
    'usernameUtilizador' => 'Nome de utilizador',
    'passwordUtilizador' => 'Password temporária',
    'confirmarPasswordUtilizador' => 'Confirmar password',
    'perfilAcessoUtilizador' => 'Perfil de acesso',
    'departamentoUtilizador' => 'Serviço',
    'funcaoUtilizador' => 'Função'
];

$stmtPermissoes = $pdo->query("
    SELECT id_permissao, codigo_permissao, nome_permissao, descricao
    FROM permissoes_sistema
    WHERE isActive = 1
    ORDER BY id_permissao ASC
");
$permissoesSistema = $stmtPermissoes->fetchAll();

if (isset($_GET['limpar'])) {
    unset($_SESSION[$chaveSessao]);
    header('Location: ' . $ficheiroAtual);
    exit;
}

if (!isset($_SESSION[$chaveSessao]['codigoUtilizador'])) {
    $_SESSION[$chaveSessao]['codigoUtilizador'] = gerar_codigo_utilizador($pdo);
}

$etapaAtual = $_GET['etapa'] ?? $etapas[0];
if (!in_array($etapaAtual, $etapas, true)) {
    $etapaAtual = $etapas[0];
}

$errosUtilizador = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etapaSubmetida = $_POST['etapa_atual'] ?? $etapaAtual;
    $acaoEtapa = $_POST['acao_etapa'] ?? '';

    if (!in_array($etapaSubmetida, $etapas, true)) {
        $etapaSubmetida = $etapas[0];
    }

    if ($acaoEtapa === 'limpar_etapa') {
        limpar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposPorEtapa);
        header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaSubmetida));
        exit;
    }

    guardar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposPorEtapa);

    if ($etapaSubmetida === 'acesso') {
        $_SESSION[$chaveSessao]['permissoesUtilizador'] = $_POST['permissoesUtilizador'] ?? [];
    }

    if ($acaoEtapa === 'anterior') {
        header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode(etapa_anterior($etapaSubmetida, $etapas)));
        exit;
    }

    if (isset($_POST['etapa_destino'])) {
        $etapaDestino = $_POST['etapa_destino'];
        $indiceDestino = indice_etapa($etapaDestino, $etapas);
        $indiceSubmetida = indice_etapa($etapaSubmetida, $etapas);

        if ($indiceDestino <= $indiceSubmetida) {
            header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaDestino));
            exit;
        }

        for ($i = 0; $i < $indiceDestino; $i++) {
            $errosEtapa = validar_etapa_temporaria($chaveSessao, $etapas[$i], $camposObrigatorios, $labelsCampos);

            if (!empty($errosEtapa)) {
                $errosUtilizador = $errosEtapa;
                $etapaAtual = $etapas[$i];
                break;
            }
        }

        if (empty($errosUtilizador)) {
            header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaDestino));
            exit;
        }
    }

    if (empty($errosUtilizador)) {
        $errosUtilizador = validar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposObrigatorios, $labelsCampos);

        if (!empty($errosUtilizador)) {
            $etapaAtual = $etapaSubmetida;
        } else {
            $proximaEtapa = proxima_etapa($etapaSubmetida, $etapas);

            if ($proximaEtapa !== null) {
                header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($proximaEtapa));
                exit;
            }
        }
    }

    if (empty($errosUtilizador)) {
        foreach ($etapas as $etapa) {
            $errosEtapa = validar_etapa_temporaria($chaveSessao, $etapa, $camposObrigatorios, $labelsCampos);

            if (!empty($errosEtapa)) {
                $errosUtilizador = $errosEtapa;
                $etapaAtual = $etapa;
                break;
            }
        }
    }

    $dados = $_SESSION[$chaveSessao] ?? [];

    if (empty($errosUtilizador) && ($dados['passwordUtilizador'] ?? '') !== ($dados['confirmarPasswordUtilizador'] ?? '')) {
        $errosUtilizador[] = 'A password e a confirmação da password não coincidem.';
        $etapaAtual = 'acesso';
    }

    if (empty($errosUtilizador)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO utilizadores (
                    codigo_utilizador, nome, tipo_utilizador, estado,
                    cartao_cidadao, nif, data_nascimento, numero_mecanografico,
                    email, telefone, extensao, morada, codigo_postal, localidade,
                    username, password_hash, perfil_acesso, data_ativacao, validade_acesso,
                    departamento, funcao, superior_hierarquico, edificio, piso, data_admissao,
                    observacoes, isActive, atualizado_por
                ) VALUES (
                    :codigo_utilizador, :nome, :tipo_utilizador, :estado,
                    :cartao_cidadao, :nif, :data_nascimento, :numero_mecanografico,
                    :email, :telefone, :extensao, :morada, :codigo_postal, :localidade,
                    :username, :password_hash, :perfil_acesso, :data_ativacao, :validade_acesso,
                    :departamento, :funcao, :superior_hierarquico, :edificio, :piso, :data_admissao,
                    :observacoes, 1, :atualizado_por
                )
            ");

            $stmt->execute([
                ':codigo_utilizador' => $dados['codigoUtilizador'],
                ':nome' => $dados['nomeUtilizador'],
                ':tipo_utilizador' => $dados['tipoUtilizador'],
                ':estado' => $dados['estadoUtilizador'],
                ':cartao_cidadao' => $dados['cartaoCidadaoUtilizador'],
                ':nif' => valor_nulo($dados['nifUtilizador'] ?? null),
                ':data_nascimento' => valor_nulo($dados['dataNascimentoUtilizador'] ?? null),
                ':numero_mecanografico' => valor_nulo($dados['numeroMecanograficoUtilizador'] ?? null),
                ':email' => $dados['emailUtilizador'],
                ':telefone' => valor_nulo($dados['telefoneUtilizador'] ?? null),
                ':extensao' => valor_nulo($dados['extensaoUtilizador'] ?? null),
                ':morada' => valor_nulo($dados['moradaUtilizador'] ?? null),
                ':codigo_postal' => valor_nulo($dados['codigoPostalUtilizador'] ?? null),
                ':localidade' => valor_nulo($dados['localidadeUtilizador'] ?? null),
                ':username' => $dados['usernameUtilizador'],
                ':password_hash' => password_hash($dados['passwordUtilizador'], PASSWORD_DEFAULT),
                ':perfil_acesso' => $dados['perfilAcessoUtilizador'],
                ':data_ativacao' => valor_nulo($dados['dataAtivacaoUtilizador'] ?? null),
                ':validade_acesso' => valor_nulo($dados['validadeAcessoUtilizador'] ?? null),
                ':departamento' => $dados['departamentoUtilizador'],
                ':funcao' => $dados['funcaoUtilizador'],
                ':superior_hierarquico' => valor_nulo($dados['superiorHierarquicoUtilizador'] ?? null),
                ':edificio' => valor_nulo($dados['edificioUtilizador'] ?? null),
                ':piso' => valor_nulo($dados['pisoUtilizador'] ?? null),
                ':data_admissao' => valor_nulo($dados['dataAdmissaoUtilizador'] ?? null),
                ':observacoes' => valor_nulo($dados['observacoesUtilizador'] ?? null),
                ':atualizado_por' => utilizador_sessao()
            ]);

            $idUtilizador = (int) $pdo->lastInsertId();

            $permissoesSelecionadas = $dados['permissoesUtilizador'] ?? permissoes_padrao($dados['tipoUtilizador']);
            $stmtPermissao = $pdo->prepare("SELECT id_permissao FROM permissoes_sistema WHERE codigo_permissao = :codigo AND isActive = 1 LIMIT 1");
            $stmtInserirPermissao = $pdo->prepare("
                INSERT INTO utilizadores_permissoes (id_utilizador, id_permissao, isActive, atualizado_por)
                VALUES (:id_utilizador, :id_permissao, 1, :atualizado_por)
            ");

            foreach ($permissoesSelecionadas as $codigoPermissao) {
                $stmtPermissao->execute([':codigo' => $codigoPermissao]);
                $idPermissao = $stmtPermissao->fetchColumn();

                if ($idPermissao) {
                    $stmtInserirPermissao->execute([
                        ':id_utilizador' => $idUtilizador,
                        ':id_permissao' => $idPermissao,
                        ':atualizado_por' => utilizador_sessao()
                    ]);
                }
            }

            $stmtHistorico = $pdo->prepare("
                INSERT INTO historico_utilizadores (
                    id_utilizador_alvo, codigo_utilizador, acao, observacoes, realizado_por
                ) VALUES (
                    :id_utilizador_alvo, :codigo_utilizador, 'criacao_utilizador', :observacoes, :realizado_por
                )
            ");
            $stmtHistorico->execute([
                ':id_utilizador_alvo' => $idUtilizador,
                ':codigo_utilizador' => $dados['codigoUtilizador'],
                ':observacoes' => 'Utilizador criado no formulário de novo utilizador.',
                ':realizado_por' => utilizador_sessao()
            ]);

            $pdo->commit();
            unset($_SESSION[$chaveSessao]);

            header('Location: ficha_utilizador.php?id=' . urlencode((string) $idUtilizador) . '&criado=1');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errosUtilizador[] = 'Erro ao criar utilizador: ' . $e->getMessage();
        }
    }
}

$permissoesAtivas = $_SESSION[$chaveSessao]['permissoesUtilizador']
    ?? permissoes_padrao($_SESSION[$chaveSessao]['tipoUtilizador'] ?? '');

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private ficha-equipamento-page novo-equipamento-page">
    <div class="form-actions">
        <a href="lista_utilizadores.php" class="btn btn-cancelar">
            <i class="fa-solid fa-xmark me-2"></i> Cancelar
        </a>

        <button type="submit" class="btn btn-limpar" name="acao_etapa" value="limpar_etapa" form="formNovoUtilizador" formnovalidate>
            <i class="fa-solid fa-eraser me-2"></i> Limpar
        </button>

        <?php if ($etapaAtual !== $etapas[0]): ?>
            <button type="submit" class="btn btn-limpar" name="acao_etapa" value="anterior" form="formNovoUtilizador" formnovalidate>
                <i class="fa-solid fa-arrow-left me-2"></i> Anterior
            </button>
        <?php endif; ?>

        <button type="submit" class="btn btn-guardar" name="acao_etapa" value="continuar" form="formNovoUtilizador" formnovalidate>
            <i class="fa-solid <?php echo $etapaAtual === 'observacoes' ? 'fa-floppy-disk' : 'fa-arrow-right'; ?> me-2"></i>
            <?php echo $etapaAtual === 'observacoes' ? 'Guardar Utilizador' : 'Guardar e Continuar'; ?>
        </button>
    </div>

    <form class="form-equipamento form-ficha-equipamento"
          id="formNovoUtilizador"
          action="novo_utilizador.php?etapa=<?php echo urlencode($etapaAtual); ?>"
          method="post">

        <input type="hidden" name="etapa_atual" value="<?php echo h($etapaAtual); ?>">

        <div class="form-stepper" aria-label="Progresso do registo do utilizador">
            <?php foreach ($etapas as $indice => $etapa): ?>
                <div class="<?php echo classe_stepper($etapa, $etapaAtual, $etapas); ?>">
                    <span class="form-step-numero">
                        <?php echo indice_etapa($etapa, $etapas) < indice_etapa($etapaAtual, $etapas) ? '<i class="fa-solid fa-check"></i>' : $indice + 1; ?>
                    </span>
                    <span class="form-step-label"><?php echo h($nomesEtapas[$etapa]); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-step-heading">
            <h3>Etapa <?php echo indice_etapa($etapaAtual, $etapas) + 1; ?> de <?php echo count($etapas); ?>: <?php echo h($nomesEtapas[$etapaAtual]); ?></h3>
        </div>

        <?php if (!empty($errosUtilizador)): ?>
            <div class="form-alerta-erros" role="alert">
                <strong><i class="fa-solid fa-triangle-exclamation me-2"></i> Não é possível avançar.</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errosUtilizador as $erro): ?>
                        <li><?php echo h($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="ficha-area">
            <ul class="nav nav-tabs ficha-tabs" id="tabsNovoUtilizador" role="tablist">
                <?php foreach ($etapas as $etapa): ?>
                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab($etapa, $etapaAtual); ?>"
                                name="etapa_destino"
                                value="<?php echo h($etapa); ?>"
                                formnovalidate
                                role="tab"
                                aria-selected="<?php echo aria_tab($etapa, $etapaAtual); ?>">
                            <?php echo h($nomesEtapas[$etapa]); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content ficha-tab-content">
                <div class="<?php echo classe_painel('identificacao', $etapaAtual); ?>" id="identificacao" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Identificação do Utilizador</h4>
                        <p>Dados base que identificam o utilizador no sistema.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label" for="codigoUtilizador">Código interno</label>
                            <input type="text" class="form-control" id="codigoUtilizador" name="codigoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'codigoUtilizador'); ?>" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="nomeUtilizador">Nome *</label>
                            <input type="text" class="form-control" id="nomeUtilizador" name="nomeUtilizador" value="<?php echo valor_temporario($chaveSessao, 'nomeUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="tipoUtilizador">Tipo *</label>
                            <select class="form-select" id="tipoUtilizador" name="tipoUtilizador" required>
                                <option value="">Selecionar</option>
                                <option value="Administrador" <?php echo selected_temporario($chaveSessao, 'tipoUtilizador', 'Administrador'); ?>>Administrador</option>
                                <option value="Engenheiro" <?php echo selected_temporario($chaveSessao, 'tipoUtilizador', 'Engenheiro'); ?>>Engenheiro</option>
                                <option value="Enfermeiro" <?php echo selected_temporario($chaveSessao, 'tipoUtilizador', 'Enfermeiro'); ?>>Enfermeiro</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="estadoUtilizador">Estado *</label>
                            <select class="form-select" id="estadoUtilizador" name="estadoUtilizador" required>
                                <option value="Ativo" <?php echo selected_temporario($chaveSessao, 'estadoUtilizador', 'Ativo'); ?>>Ativo</option>
                                <option value="Pendente" <?php echo selected_temporario($chaveSessao, 'estadoUtilizador', 'Pendente'); ?>>Pendente</option>
                                <option value="Inativo" <?php echo selected_temporario($chaveSessao, 'estadoUtilizador', 'Inativo'); ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="cartaoCidadaoUtilizador">N.º Cartão de Cidadão *</label>
                            <input type="text" class="form-control" id="cartaoCidadaoUtilizador" name="cartaoCidadaoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'cartaoCidadaoUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="nifUtilizador">NIF</label>
                            <input type="text" class="form-control" id="nifUtilizador" name="nifUtilizador" value="<?php echo valor_temporario($chaveSessao, 'nifUtilizador'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="dataNascimentoUtilizador">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimentoUtilizador" name="dataNascimentoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'dataNascimentoUtilizador'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="numeroMecanograficoUtilizador">N.º mecanográfico</label>
                            <input type="text" class="form-control" id="numeroMecanograficoUtilizador" name="numeroMecanograficoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'numeroMecanograficoUtilizador'); ?>">
                        </div>
                    </div>
                </div>

                <div class="<?php echo classe_painel('contactos', $etapaAtual); ?>" id="contactos" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Contactos</h4>
                        <p>Contactos profissionais e morada do utilizador.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="emailUtilizador">Email *</label>
                            <input type="email" class="form-control" id="emailUtilizador" name="emailUtilizador" value="<?php echo valor_temporario($chaveSessao, 'emailUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="telefoneUtilizador">Telefone</label>
                            <input type="text" class="form-control" id="telefoneUtilizador" name="telefoneUtilizador" value="<?php echo valor_temporario($chaveSessao, 'telefoneUtilizador'); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="extensaoUtilizador">Extensão</label>
                            <input type="text" class="form-control" id="extensaoUtilizador" name="extensaoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'extensaoUtilizador'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="localidadeUtilizador">Localidade</label>
                            <input type="text" class="form-control" id="localidadeUtilizador" name="localidadeUtilizador" value="<?php echo valor_temporario($chaveSessao, 'localidadeUtilizador'); ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="moradaUtilizador">Morada</label>
                            <input type="text" class="form-control" id="moradaUtilizador" name="moradaUtilizador" value="<?php echo valor_temporario($chaveSessao, 'moradaUtilizador'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="codigoPostalUtilizador">Código postal</label>
                            <input type="text" class="form-control" id="codigoPostalUtilizador" name="codigoPostalUtilizador" value="<?php echo valor_temporario($chaveSessao, 'codigoPostalUtilizador'); ?>">
                        </div>
                    </div>
                </div>

                <div class="<?php echo classe_painel('acesso', $etapaAtual); ?>" id="acesso" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Acesso ao Sistema</h4>
                        <p>Defina credenciais e permissões de acesso aos menus.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="usernameUtilizador">Nome de utilizador *</label>
                            <input type="text" class="form-control" id="usernameUtilizador" name="usernameUtilizador" value="<?php echo valor_temporario($chaveSessao, 'usernameUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="passwordUtilizador">Password temporária *</label>
                            <input type="password" class="form-control" id="passwordUtilizador" name="passwordUtilizador" value="<?php echo valor_temporario($chaveSessao, 'passwordUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="confirmarPasswordUtilizador">Confirmar password *</label>
                            <input type="password" class="form-control" id="confirmarPasswordUtilizador" name="confirmarPasswordUtilizador" value="<?php echo valor_temporario($chaveSessao, 'confirmarPasswordUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="perfilAcessoUtilizador">Perfil de acesso *</label>
                            <select class="form-select" id="perfilAcessoUtilizador" name="perfilAcessoUtilizador" required>
                                <option value="">Selecionar</option>
                                <option value="Acesso total" <?php echo selected_temporario($chaveSessao, 'perfilAcessoUtilizador', 'Acesso total'); ?>>Acesso total</option>
                                <option value="Gestão técnica" <?php echo selected_temporario($chaveSessao, 'perfilAcessoUtilizador', 'Gestão técnica'); ?>>Gestão técnica</option>
                                <option value="Consulta clínica" <?php echo selected_temporario($chaveSessao, 'perfilAcessoUtilizador', 'Consulta clínica'); ?>>Consulta clínica</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="dataAtivacaoUtilizador">Data de ativação</label>
                            <input type="date" class="form-control" id="dataAtivacaoUtilizador" name="dataAtivacaoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'dataAtivacaoUtilizador'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="validadeAcessoUtilizador">Validade do acesso</label>
                            <input type="date" class="form-control" id="validadeAcessoUtilizador" name="validadeAcessoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'validadeAcessoUtilizador'); ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Acessos aos menus do sistema</label>
                        <div class="permissoes-utilizador-opcoes">
                            <?php foreach ($permissoesSistema as $permissao): ?>
                                <div class="form-check permissao-utilizador-item">
                                    <input class="form-check-input permissao-utilizador"
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

                <div class="<?php echo classe_painel('servico', $etapaAtual); ?>" id="servico" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Serviço Hospitalar</h4>
                        <p>Associe o utilizador ao serviço e função interna.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="departamentoUtilizador">Serviço *</label>
                            <input type="text" class="form-control" id="departamentoUtilizador" name="departamentoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'departamentoUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="funcaoUtilizador">Função *</label>
                            <input type="text" class="form-control" id="funcaoUtilizador" name="funcaoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'funcaoUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="superiorHierarquicoUtilizador">Superior hierárquico</label>
                            <input type="text" class="form-control" id="superiorHierarquicoUtilizador" name="superiorHierarquicoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'superiorHierarquicoUtilizador'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edificioUtilizador">Edifício</label>
                            <input type="text" class="form-control" id="edificioUtilizador" name="edificioUtilizador" value="<?php echo valor_temporario($chaveSessao, 'edificioUtilizador'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pisoUtilizador">Piso</label>
                            <input type="text" class="form-control" id="pisoUtilizador" name="pisoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'pisoUtilizador'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="dataAdmissaoUtilizador">Data de admissão</label>
                            <input type="date" class="form-control" id="dataAdmissaoUtilizador" name="dataAdmissaoUtilizador" value="<?php echo valor_temporario($chaveSessao, 'dataAdmissaoUtilizador'); ?>">
                        </div>
                    </div>
                </div>

                <div class="<?php echo classe_painel('observacoes', $etapaAtual); ?>" id="observacoes" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Observações</h4>
                        <p>Notas administrativas ou técnicas sobre o utilizador.</p>
                    </div>

                    <textarea class="form-control" id="observacoesUtilizador" name="observacoesUtilizador" rows="7"><?php echo valor_temporario($chaveSessao, 'observacoesUtilizador'); ?></textarea>
                </div>
            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
