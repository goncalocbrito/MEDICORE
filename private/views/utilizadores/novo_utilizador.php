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

function gerar_codigo_utilizador(PDO $pdo)
{
    $stmt = $pdo->query("
        SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_utilizador, 5) AS UNSIGNED)), 0) + 1
        FROM utilizadores
        WHERE codigo_utilizador LIKE 'USR-%'
    ");

    return 'USR-' . str_pad((string) $stmt->fetchColumn(), 3, '0', STR_PAD_LEFT);
}

function validar_formato_etapa(string $chaveSessao, string $etapa): array
{
    $dados = $_SESSION[$chaveSessao] ?? [];
    $erros = [];

    if ($etapa === 'identificacao') {
        if ($erro = validar_nif($dados['nifUtilizador'] ?? '')) {
            $erros[] = $erro;
        }
        if ($erro = validar_cartao_cidadao($dados['cartaoCidadaoUtilizador'] ?? '')) {
            $erros[] = $erro;
        }
    }

    if ($etapa === 'contactos') {
        if ($erro = validar_email($dados['emailUtilizador'] ?? '', true)) {
            $erros[] = $erro;
        }
        if ($erro = validar_telefone($dados['telefoneUtilizador'] ?? '')) {
            $erros[] = $erro;
        }
        if ($erro = validar_codigo_postal($dados['codigoPostalUtilizador'] ?? '')) {
            $erros[] = $erro;
        }
    }

    return $erros;
}

$chaveSessao = 'novo_utilizador';
$ficheiroAtual = 'novo_utilizador.php';

$etapas = ['identificacao', 'contactos', 'acesso'];

$nomesEtapas = [
    'identificacao' => 'Identificação',
    'contactos'     => 'Contactos',
    'acesso'        => 'Acesso',
];

$camposPorEtapa = [
    'identificacao' => ['codigoUtilizador', 'nomeUtilizador', 'tipoUtilizador', 'estadoUtilizador', 'cartaoCidadaoUtilizador', 'nifUtilizador', 'dataNascimentoUtilizador'],
    'contactos'     => ['emailUtilizador', 'telefoneUtilizador', 'moradaUtilizador', 'codigoPostalUtilizador', 'localidadeUtilizador'],
    'acesso'        => ['usernameUtilizador', 'passwordUtilizador', 'confirmarPasswordUtilizador'],
];

$camposObrigatorios = [
    'identificacao' => ['nomeUtilizador', 'tipoUtilizador', 'estadoUtilizador', 'cartaoCidadaoUtilizador', 'nifUtilizador', 'dataNascimentoUtilizador'],
    'contactos'     => ['emailUtilizador', 'telefoneUtilizador', 'moradaUtilizador', 'codigoPostalUtilizador', 'localidadeUtilizador'],
    'acesso'        => ['usernameUtilizador', 'passwordUtilizador', 'confirmarPasswordUtilizador'],
];

$labelsCampos = [
    'nomeUtilizador'             => 'Nome',
    'tipoUtilizador'             => 'Tipo de utilizador',
    'estadoUtilizador'           => 'Estado',
    'cartaoCidadaoUtilizador'    => 'N.º Cartão de Cidadão',
    'nifUtilizador'              => 'NIF',
    'dataNascimentoUtilizador'   => 'Data de nascimento',
    'emailUtilizador'            => 'Email',
    'telefoneUtilizador'         => 'Telefone',
    'moradaUtilizador'           => 'Morada',
    'codigoPostalUtilizador'     => 'Código postal',
    'localidadeUtilizador'       => 'Localidade',
    'usernameUtilizador'         => 'Nome de utilizador',
    'passwordUtilizador'         => 'Password temporária',
    'confirmarPasswordUtilizador'=> 'Confirmar password',
];

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
            $errosEtapa = array_merge($errosEtapa, validar_formato_etapa($chaveSessao, $etapas[$i]));

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
        $errosUtilizador = array_merge($errosUtilizador, validar_formato_etapa($chaveSessao, $etapaSubmetida));

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
            $errosEtapa = array_merge($errosEtapa, validar_formato_etapa($chaveSessao, $etapa));

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
        $ccVal = trim($dados['cartaoCidadaoUtilizador'] ?? '');
        if ($ccVal !== '') {
            $stmtCC = $pdo->prepare("SELECT COUNT(*) FROM utilizadores WHERE cartao_cidadao = :cc AND isActive = 1");
            $stmtCC->execute([':cc' => $ccVal]);
            if ((int) $stmtCC->fetchColumn() > 0) {
                $errosUtilizador[] = 'Já existe um utilizador registado com o N.º Cartão de Cidadão "' . htmlspecialchars($ccVal) . '". O número deve ser único.';
                $etapaAtual = 'identificacao';
            }
        }
    }

    if (empty($errosUtilizador)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO utilizadores (
                    codigo_utilizador, nome, tipo_utilizador, estado,
                    cartao_cidadao, nif, data_nascimento,
                    email, telefone, morada, codigo_postal, localidade,
                    username, password_hash,
                    isActive, atualizado_por
                ) VALUES (
                    :codigo_utilizador, :nome, :tipo_utilizador, :estado,
                    :cartao_cidadao, :nif, :data_nascimento,
                    :email, :telefone, :morada, :codigo_postal, :localidade,
                    :username, :password_hash,
                    1, :atualizado_por
                )
            ");

            $stmt->execute([
                ':codigo_utilizador' => $dados['codigoUtilizador'],
                ':nome'              => $dados['nomeUtilizador'],
                ':tipo_utilizador'   => $dados['tipoUtilizador'],
                ':estado'            => $dados['estadoUtilizador'],
                ':cartao_cidadao'    => $dados['cartaoCidadaoUtilizador'],
                ':nif'               => valor_nulo($dados['nifUtilizador'] ?? null),
                ':data_nascimento'   => valor_nulo($dados['dataNascimentoUtilizador'] ?? null),
                ':email'             => $dados['emailUtilizador'],
                ':telefone'          => valor_nulo($dados['telefoneUtilizador'] ?? null),
                ':morada'            => valor_nulo($dados['moradaUtilizador'] ?? null),
                ':codigo_postal'     => valor_nulo($dados['codigoPostalUtilizador'] ?? null),
                ':localidade'        => valor_nulo($dados['localidadeUtilizador'] ?? null),
                ':username'          => $dados['usernameUtilizador'],
                ':password_hash'     => password_hash($dados['passwordUtilizador'], PASSWORD_DEFAULT),
                ':atualizado_por'    => utilizador_sessao()
            ]);

            $idUtilizador = (int) $pdo->lastInsertId();

            $stmtHistorico = $pdo->prepare("
                INSERT INTO historico_utilizadores (
                    id_utilizador_alvo, codigo_utilizador, acao, observacoes, realizado_por
                ) VALUES (
                    :id_utilizador_alvo, :codigo_utilizador, 'criacao_utilizador', :observacoes, :realizado_por
                )
            ");
            $stmtHistorico->execute([
                ':id_utilizador_alvo' => $idUtilizador,
                ':codigo_utilizador'  => $dados['codigoUtilizador'],
                ':observacoes'        => 'Utilizador criado no formulário de novo utilizador.',
                ':realizado_por'      => utilizador_sessao()
            ]);

            $pdo->commit();
            unset($_SESSION[$chaveSessao]);

            header('Location: ficha_utilizador.php?ref=' . url_ref($idUtilizador) . '&criado=1');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errosUtilizador[] = 'Erro ao criar utilizador: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private ficha-equipamento-page novo-equipamento-page">
    <div class="form-actions">
        <a href="lista_utilizadores.php" class="btn btn-cancelar">
            <i class="fa-solid fa-xmark me-2"></i> Cancelar
        </a>

        <button type="button" class="btn btn-dados-teste" onclick="dadosTeste_novoUtilizador()">
            <i class="fa-solid fa-flask me-2"></i> Dados de Teste
        </button>

        <button type="submit" class="btn btn-limpar" name="acao_etapa" value="limpar_etapa" form="formNovoUtilizador" formnovalidate>
            <i class="fa-solid fa-eraser me-2"></i> Limpar
        </button>

        <?php if ($etapaAtual !== $etapas[0]): ?>
            <button type="submit" class="btn btn-limpar" name="acao_etapa" value="anterior" form="formNovoUtilizador" formnovalidate>
                <i class="fa-solid fa-arrow-left me-2"></i> Anterior
            </button>
        <?php endif; ?>

        <button type="submit" class="btn btn-guardar" name="acao_etapa" value="continuar" form="formNovoUtilizador" formnovalidate>
            <i class="fa-solid <?php echo $etapaAtual === 'acesso' ? 'fa-floppy-disk' : 'fa-arrow-right'; ?> me-2"></i>
            <?php echo $etapaAtual === 'acesso' ? 'Guardar Utilizador' : 'Guardar e Continuar'; ?>
        </button>
    </div>

    <form class="form-equipamento form-ficha-equipamento"
          id="formNovoUtilizador"
          action="novo_utilizador.php?etapa=<?php echo urlencode($etapaAtual); ?>"
          method="post"
          novalidate>

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
            <div class="alert alert-danger" role="alert">
                <strong><i class="fa-solid fa-triangle-exclamation me-2"></i> Erro</strong>
                <ul class="mb-0 mt-1">
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

                <!-- ETAPA 1: IDENTIFICAÇÃO -->
                <div class="<?php echo classe_painel('identificacao', $etapaAtual); ?>" id="identificacao" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Identificação do Utilizador</h4>
                        <p>Dados base que identificam o utilizador no sistema.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label" for="codigoUtilizador">Código interno</label>
                            <input type="text" class="form-control" id="codigoUtilizador" name="codigoUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'codigoUtilizador'); ?>" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="nomeUtilizador">Nome *</label>
                            <input type="text" class="form-control" id="nomeUtilizador" name="nomeUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'nomeUtilizador'); ?>" maxlength="150" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="nomeUtilizador" data-max="150"></small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="tipoUtilizador">Tipo *</label>
                            <select class="form-select" id="tipoUtilizador" name="tipoUtilizador" required>
                                <option value="">Selecionar</option>
                                <option value="Administrador" <?php echo selected_temporario($chaveSessao, 'tipoUtilizador', 'Administrador'); ?>>Administrador</option>
                                <option value="Engenheiro" <?php echo selected_temporario($chaveSessao, 'tipoUtilizador', 'Engenheiro'); ?>>Engenheiro</option>
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
                            <label class="form-label" for="cartaoCidadaoUtilizador">N.º Cartão de Cidadão * <small class="text-muted">(8 dígitos)</small></label>
                            <input type="text" class="form-control" id="cartaoCidadaoUtilizador" name="cartaoCidadaoUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'cartaoCidadaoUtilizador'); ?>"
                                   maxlength="8" oninput="this.value=this.value.replace(/\D/g,'')" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="cartaoCidadaoUtilizador" data-max="8"></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="nifUtilizador">NIF * <small class="text-muted">(9 dígitos)</small></label>
                            <input type="text" class="form-control" id="nifUtilizador" name="nifUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'nifUtilizador'); ?>"
                                   maxlength="9" oninput="this.value=this.value.replace(/\D/g,'')" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="nifUtilizador" data-max="9"></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="dataNascimentoUtilizador">Data de nascimento *</label>
                            <input type="date" class="form-control" id="dataNascimentoUtilizador" name="dataNascimentoUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'dataNascimentoUtilizador'); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- ETAPA 2: CONTACTOS -->
                <div class="<?php echo classe_painel('contactos', $etapaAtual); ?>" id="contactos" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Contactos</h4>
                        <p>Contactos profissionais e morada do utilizador.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-5">
                            <label class="form-label" for="emailUtilizador">Email * <small class="text-muted">(@, .com ou .pt)</small></label>
                            <input type="email" class="form-control" id="emailUtilizador" name="emailUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'emailUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="telefoneUtilizador">Telefone * <small class="text-muted">(9 dígitos)</small></label>
                            <input type="text" class="form-control" id="telefoneUtilizador" name="telefoneUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'telefoneUtilizador'); ?>"
                                   maxlength="9" oninput="this.value=this.value.replace(/\D/g,'')" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="telefoneUtilizador" data-max="9"></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="localidadeUtilizador">Localidade *</label>
                            <input type="text" class="form-control" id="localidadeUtilizador" name="localidadeUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'localidadeUtilizador'); ?>"
                                   maxlength="100" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="localidadeUtilizador" data-max="100"></small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="moradaUtilizador">Morada *</label>
                            <input type="text" class="form-control" id="moradaUtilizador" name="moradaUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'moradaUtilizador'); ?>" maxlength="255" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="moradaUtilizador" data-max="255"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="codigoPostalUtilizador">Código postal * <small class="text-muted">(ex: 1234-567)</small></label>
                            <input type="text" class="form-control" id="codigoPostalUtilizador" name="codigoPostalUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'codigoPostalUtilizador'); ?>"
                                   placeholder="1234-567" maxlength="8" required>
                        </div>
                    </div>
                </div>

                <!-- ETAPA 3: ACESSO -->
                <div class="<?php echo classe_painel('acesso', $etapaAtual); ?>" id="acesso" role="tabpanel" tabindex="0">
                    <div class="secao-ficha-titulo">
                        <h4>Acesso ao Sistema</h4>
                        <p>Defina as credenciais de acesso ao sistema.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="usernameUtilizador">Nome de utilizador *</label>
                            <input type="text" class="form-control" id="usernameUtilizador" name="usernameUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'usernameUtilizador'); ?>" maxlength="80" required>
                            <small class="texto-ajuda-form contador-caracteres" data-target="usernameUtilizador" data-max="80"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="passwordUtilizador">Password temporária *</label>
                            <input type="password" class="form-control" id="passwordUtilizador" name="passwordUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'passwordUtilizador'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="confirmarPasswordUtilizador">Confirmar password *</label>
                            <input type="password" class="form-control" id="confirmarPasswordUtilizador" name="confirmarPasswordUtilizador"
                                   value="<?php echo valor_temporario($chaveSessao, 'confirmarPasswordUtilizador'); ?>" required>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
