<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

require_once __DIR__ . '/../../../config/config.php';

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'apagar_localizacao') {
    $idApagar = (int) ($_POST['id_localizacao'] ?? 0);
    if ($idApagar > 0) {
        $pdo->prepare("UPDATE localizacoes SET isActive = 0 WHERE id_localizacao = :id")
            ->execute([':id' => $idApagar]);
    }
    header('Location: lista_localizacoes.php?apagado=1');
    exit;
}

$idLocalizacao = id_from_request();

if (!$idLocalizacao) {
    header('Location: lista_localizacoes.php');
    exit;
}

function normalizar_codigo_localizacao($texto)
{
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    $texto = preg_replace('/[^a-zA-Z0-9]/', '', $texto);

    return strtoupper($texto);
}

$errosLocalizacao = [];

$idLocalizacao = (int) ($_POST['idLocalizacao'] ?? $idLocalizacao);

if (!$idLocalizacao) {
    header('Location: lista_localizacoes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departamentoNome = trim($_POST['departamentoNome'] ?? '');
    $departamentoSigla = normalizar_codigo_localizacao($_POST['departamentoSigla'] ?? '');
    $edificio = trim($_POST['edificioLocalizacao'] ?? '');
    $piso = trim($_POST['pisoLocalizacao'] ?? '');
    $sala = trim($_POST['salaLocalizacao'] ?? '');
    $tipoEspaco = trim($_POST['tipoEspaco'] ?? '');
    $estado = trim($_POST['estadoLocalizacao'] ?? '');
    $capacidadeEquipamentos = trim($_POST['capacidadeEquipamentos'] ?? '');
    $permiteCriticos = (int) ($_POST['permiteCriticos'] ?? 0);
    $observacoes = trim($_POST['observacoesLocalizacao'] ?? '');

    $pisoCodigo = normalizar_codigo_localizacao($piso);
    $salaCodigo = normalizar_codigo_localizacao($sala);

    $codigo = $departamentoSigla . '-P' . $pisoCodigo . '-S' . $salaCodigo;

    if ($departamentoNome === '') {
        $errosLocalizacao[] = 'O campo "Nome do Departamento / Serviço" é obrigatório.';
    }

    if ($departamentoSigla === '') {
        $errosLocalizacao[] = 'O campo "Sigla" é obrigatório.';
    } elseif ($erro = validar_sigla($departamentoSigla)) {
        $errosLocalizacao[] = $erro;
    }

    if ($edificio === '') {
        $errosLocalizacao[] = 'O campo "Edifício" é obrigatório.';
    } elseif ($erro = validar_apenas_letras($edificio, 'Edifício')) {
        $errosLocalizacao[] = $erro;
    }

    if ($piso === '') {
        $errosLocalizacao[] = 'O campo "Piso" é obrigatório.';
    } elseif (!preg_match('/^-?\d{1,2}$/', $piso)) {
        $errosLocalizacao[] = '"Piso" deve ser um número (positivo ou negativo) com no máximo 2 dígitos. Ex: -1, 0, 2.';
    }

    if ($sala === '') {
        $errosLocalizacao[] = 'O campo "Sala" é obrigatório.';
    } elseif ($erro = validar_apenas_digitos($sala, 'Sala', 1, 3)) {
        $errosLocalizacao[] = $erro;
    }

    if ($tipoEspaco === '') {
        $errosLocalizacao[] = 'O campo "Tipo de Espaço" é obrigatório.';
    }

    if ($estado === '') {
        $errosLocalizacao[] = 'O campo "Estado" é obrigatório.';
    }

    if ($capacidadeEquipamentos === '') {
        $errosLocalizacao[] = 'O campo "Capacidade de Equipamentos" é obrigatório.';
    }

    if (empty($errosLocalizacao)) {
        $stmtDup = $pdo->prepare("SELECT COUNT(*) FROM localizacoes WHERE codigo = :codigo AND isActive = 1 AND id_localizacao != :id");
        $stmtDup->execute([':codigo' => $codigo, ':id' => $idLocalizacao]);
        if ((int) $stmtDup->fetchColumn() > 0) {
            $errosLocalizacao[] = 'Já existe uma localização com o código "' . htmlspecialchars($codigo) . '". Altere a sigla, o piso ou a sala para gerar um código único.';
        }
    }

    if (empty($errosLocalizacao)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE localizacoes
                SET
                    codigo = :codigo,
                    departamento_nome = :departamento_nome,
                    departamento_sigla = :departamento_sigla,
                    edificio = :edificio,
                    piso = :piso,
                    sala = :sala,
                    tipo_espaco = :tipo_espaco,
                    estado = :estado,
                    capacidade_equipamentos = :capacidade_equipamentos,
                    permite_equipamentos_criticos = :permite_equipamentos_criticos,
                    observacoes = :observacoes
                WHERE id_localizacao = :id_localizacao
                  AND isActive = 1
            ");

            $stmt->execute([
                ':codigo' => $codigo,
                ':departamento_nome' => $departamentoNome,
                ':departamento_sigla' => $departamentoSigla,
                ':edificio' => $edificio,
                ':piso' => $piso,
                ':sala' => $sala,
                ':tipo_espaco' => $tipoEspaco,
                ':estado' => $estado,
                ':capacidade_equipamentos' => $capacidadeEquipamentos !== '' ? (int) $capacidadeEquipamentos : null,
                ':permite_equipamentos_criticos' => $permiteCriticos,
                ':observacoes' => $observacoes,
                ':id_localizacao' => $idLocalizacao
            ]);

            header('Location: ficha_localizacao.php?ref=' . url_ref($idLocalizacao) . '&atualizado=1');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errosLocalizacao[] = 'Já existe uma localização com esse código.';
            } else {
                $errosLocalizacao[] = 'Ocorreu um erro ao guardar as alterações.';
            }
        }
    }
}

$stmt = $pdo->prepare("
    SELECT *
    FROM localizacoes
    WHERE id_localizacao = :id_localizacao
      AND isActive = 1
    LIMIT 1
");

$stmt->execute([
    ':id_localizacao' => $idLocalizacao
]);

$localizacao = $stmt->fetch();

if (!$localizacao) {
    header('Location: lista_localizacoes.php?erro=localizacao_nao_encontrada');
    exit;
}

function selected_localizacao($valorAtual, $valorOpcao)
{
    return $valorAtual === $valorOpcao ? 'selected' : '';
}

function checked_localizacao($valorAtual, $valorOpcao)
{
    return (string)$valorAtual === (string)$valorOpcao ? 'checked' : '';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private ficha-equipamento-page ficha-localizacao-page">

    <div class="d-none" aria-hidden="true">
        <h2 id="tituloPaginaLocalizacao">Ficha da Localização</h2>
        <span id="resumoNomeLocalizacao">
            <?php echo htmlspecialchars($localizacao['codigo']); ?>
        </span>
        <span id="resumoDescricaoLocalizacao">
            <?php echo htmlspecialchars($localizacao['departamento_nome']); ?>
            |
            <?php echo htmlspecialchars($localizacao['edificio']); ?>
            |
            <?php echo htmlspecialchars($localizacao['sala']); ?>
        </span>
        <span id="badgeEstadoLocalizacao">
            <?php echo htmlspecialchars($localizacao['estado']); ?>
        </span>
        <span id="badgeTipoLocalizacao">
            <?php echo htmlspecialchars($localizacao['tipo_espaco']); ?>
        </span>
    </div>

    <div class="ficha-toolbar">
        <a href="lista_localizacoes.php" class="btn btn-voltar btn-voltar-lista-com-confirmacao">
            <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
        </a>

        <button type="submit" class="btn btn-guardar" form="formFichaLocalizacao">
            <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
        </button>
    </div>

    <?php if (isset($_GET['atualizado'])): ?>
        <div class="form-alerta-sucesso" role="alert">
            <strong>
                <i class="fa-solid fa-circle-check me-2"></i>
                Localização atualizada com sucesso.
            </strong>
        </div>
    <?php endif; ?>

    <?php if (!empty($errosLocalizacao)): ?>
        <div class="alert alert-danger" role="alert">
            <strong><i class="fa-solid fa-triangle-exclamation me-2"></i> Erro</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errosLocalizacao as $erro): ?>
                    <li><?php echo htmlspecialchars($erro); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="form-equipamento form-ficha-equipamento"
      id="formFichaLocalizacao"
      action="ficha_localizacao.php?ref=<?php echo url_ref($localizacao['id_localizacao']); ?>"
      method="post"
      novalidate>

        <input type="hidden"
               id="idLocalizacao"
               name="idLocalizacao"
               value="<?php echo htmlspecialchars($localizacao['id_localizacao']); ?>">

        <input type="hidden" id="modoFormularioLocalizacao" name="modoFormulario" value="editar">

        <div class="ficha-area">

            <ul class="nav nav-tabs ficha-tabs" id="tabsFichaLocalizacao" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active"
                            id="identificacao-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#identificacao"
                            type="button"
                            role="tab"
                            aria-controls="identificacao"
                            aria-selected="true">
                        <i class="fa-solid fa-location-dot me-2"></i>
                        Identificação
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            id="caracteristicas-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#caracteristicas"
                            type="button"
                            role="tab"
                            aria-controls="caracteristicas"
                            aria-selected="false">
                        <i class="fa-solid fa-hospital me-2"></i>
                        Características
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            id="observacoes-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#observacoes-tab-pane"
                            type="button"
                            role="tab"
                            aria-controls="observacoes-tab-pane"
                            aria-selected="false">
                        <i class="fa-solid fa-clipboard-list me-2"></i>
                        Observações
                    </button>
                </li>
            </ul>

            <div class="tab-content ficha-tab-content" id="tabsFichaLocalizacaoContent">

                <div class="tab-pane fade show active"
                     id="identificacao"
                     role="tabpanel"
                     aria-labelledby="identificacao-tab"
                     tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Identificação da Localização</h4>
                        <p>Dados principais da sala/localização hospitalar.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="codigoLocalizacao" class="form-label">Código da Localização *</label>
                            <input type="text"
                                   class="form-control campo-ficha campo-bloqueado"
                                   id="codigoLocalizacao"
                                   name="codigoLocalizacao"
                                   value="<?php echo htmlspecialchars($localizacao['codigo']); ?>"
                                   readonly>
                        </div>

                        <div class="col-md-5">
                            <label for="departamentoNome" class="form-label">Nome do Departamento / Serviço *</label>
                            <input type="text"
                                   class="form-control campo-ficha campo-editavel"
                                   id="departamentoNome"
                                   name="departamentoNome"
                                   value="<?php echo htmlspecialchars($localizacao['departamento_nome']); ?>"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label for="departamentoSigla" class="form-label">Sigla * <small class="text-muted">(máx. 3 letras)</small></label>
                            <input type="text"
                                   class="form-control campo-ficha campo-editavel"
                                   id="departamentoSigla"
                                   name="departamentoSigla"
                                   value="<?php echo htmlspecialchars($localizacao['departamento_sigla']); ?>"
                                   maxlength="3"
                                   oninput="this.value=this.value.toUpperCase().replace(/[^A-Za-z]/g,'')"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label for="edificioLocalizacao" class="form-label">Edifício *</label>
                            <input type="text"
                                   class="form-control campo-ficha campo-editavel"
                                   id="edificioLocalizacao"
                                   name="edificioLocalizacao"
                                   value="<?php echo htmlspecialchars($localizacao['edificio']); ?>"
                                   required>
                        </div>

                        <div class="col-md-2">
                            <label for="pisoLocalizacao" class="form-label">Piso * <small class="text-muted">(ex: -1, 0, 2)</small></label>
                            <input type="text"
                                   class="form-control campo-ficha campo-editavel"
                                   id="pisoLocalizacao"
                                   name="pisoLocalizacao"
                                   value="<?php echo htmlspecialchars($localizacao['piso']); ?>"
                                   maxlength="3"
                                   oninput="this.value=this.value.replace(/[^0-9-]/g,'').replace(/(?!^)-/g,'')"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label for="salaLocalizacao" class="form-label">Sala * <small class="text-muted">(máx. 3 dígitos)</small></label>
                            <input type="text"
                                   class="form-control campo-ficha campo-editavel"
                                   id="salaLocalizacao"
                                   name="salaLocalizacao"
                                   value="<?php echo htmlspecialchars($localizacao['sala']); ?>"
                                   maxlength="3"
                                   oninput="this.value=this.value.replace(/\D/g,'')"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label for="estadoLocalizacao" class="form-label">Estado *</label>
                            <select class="form-select campo-ficha campo-editavel"
                                    id="estadoLocalizacao"
                                    name="estadoLocalizacao"
                                    required>
                                <option value="">Selecionar estado</option>
                                <option value="Ativa" <?php echo selected_localizacao($localizacao['estado'], 'Ativa'); ?>>
                                    Ativa
                                </option>
                                <option value="Inativa" <?php echo selected_localizacao($localizacao['estado'], 'Inativa'); ?>>
                                    Inativa
                                </option>
                                <option value="Em manutenção" <?php echo selected_localizacao($localizacao['estado'], 'Em manutenção'); ?>>
                                    Em manutenção
                                </option>
                                <option value="Indisponível" <?php echo selected_localizacao($localizacao['estado'], 'Indisponível'); ?>>
                                    Indisponível
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade"
                     id="caracteristicas"
                     role="tabpanel"
                     aria-labelledby="caracteristicas-tab"
                     tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Características da Localização</h4>
                        <p>Tipo de espaço, capacidade e possibilidade de alojar equipamentos críticos.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="tipoEspaco" class="form-label">Tipo de Espaço *</label>
                            <select class="form-select campo-ficha campo-editavel"
                                    id="tipoEspaco"
                                    name="tipoEspaco"
                                    required>
                                <option value="">Selecionar tipo</option>
                                <option value="UCI" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'UCI'); ?>>
                                    UCI
                                </option>
                                <option value="Urgência" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Urgência'); ?>>
                                    Urgência
                                </option>
                                <option value="Bloco Operatório" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Bloco Operatório'); ?>>
                                    Bloco Operatório
                                </option>
                                <option value="Laboratório" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Laboratório'); ?>>
                                    Laboratório
                                </option>
                                <option value="Consulta Externa" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Consulta Externa'); ?>>
                                    Consulta Externa
                                </option>
                                <option value="Armazém Técnico" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Armazém Técnico'); ?>>
                                    Armazém Técnico
                                </option>
                                <option value="Sala de Equipamentos" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Sala de Equipamentos'); ?>>
                                    Sala de Equipamentos
                                </option>
                                <option value="Esterilização" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Esterilização'); ?>>
                                    Esterilização
                                </option>
                                <option value="Outro" <?php echo selected_localizacao($localizacao['tipo_espaco'], 'Outro'); ?>>
                                    Outro
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="capacidadeEquipamentos" class="form-label">Capacidade de Equipamentos *</label>
                            <input type="number"
                                   class="form-control campo-ficha campo-editavel"
                                   id="capacidadeEquipamentos"
                                   name="capacidadeEquipamentos"
                                   min="0"
                                   value="<?php echo htmlspecialchars($localizacao['capacidade_equipamentos'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block">Permite equipamentos críticos?</label>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input campo-ficha campo-editavel"
                                       type="radio"
                                       name="permiteCriticos"
                                       id="permiteCriticosSim"
                                       value="1"
                                       <?php echo checked_localizacao($localizacao['permite_equipamentos_criticos'], 1); ?>>
                                <label class="form-check-label" for="permiteCriticosSim">
                                    Sim
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input campo-ficha campo-editavel"
                                       type="radio"
                                       name="permiteCriticos"
                                       id="permiteCriticosNao"
                                       value="0"
                                       <?php echo checked_localizacao($localizacao['permite_equipamentos_criticos'], 0); ?>>
                                <label class="form-check-label" for="permiteCriticosNao">
                                    Não
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade"
                     id="observacoes-tab-pane"
                     role="tabpanel"
                     aria-labelledby="observacoes-tab"
                     tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Observações</h4>
                        <p>Notas sobre limitações, condições técnicas ou contexto do espaço.</p>
                    </div>

                    <textarea class="form-control campo-ficha campo-editavel"
                              id="observacoesLocalizacao"
                              name="observacoesLocalizacao"
                              rows="7"
                              maxlength="1000"
                              placeholder="Indique observações relevantes sobre a localização."><?php echo htmlspecialchars($localizacao['observacoes'] ?? ''); ?></textarea>
                    <small class="texto-ajuda-form contador-caracteres" data-target="observacoesLocalizacao" data-max="1000">0 / 1000 caracteres</small>
                </div>
            </div>
        </div>
    </form>
</main>


<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
