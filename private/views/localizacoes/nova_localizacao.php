<?php
require_once __DIR__ . '/../../includes/funcoes.php';
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

/* =========================================================
   FORMULARIO POR ETAPAS DA NOVA LOCALIZACAO
   Usa as funcoes genericas de funcoes.php para guardar dados
   temporarios em sessao ate ao momento final de criacao.
   ========================================================= */
$chaveSessao = 'nova_localizacao';
$ficheiroAtual = 'nova_localizacao.php';

$etapas = [
    'identificacao',
    'caracteristicas',
    'observacoes'
];

$nomesEtapasLocalizacao = [
    'identificacao' => 'Identificação',
    'caracteristicas' => 'Características',
    'observacoes' => 'Observações'
];

$camposPorEtapa = [
    'identificacao' => [
        'departamentoNome',
        'departamentoSigla',
        'codigoLocalizacao',
        'edificioLocalizacao',
        'pisoLocalizacao',
        'salaLocalizacao',
        'estadoLocalizacao'
    ],
    'caracteristicas' => [
        'tipoEspaco',
        'capacidadeEquipamentos',
        'permiteCriticos'
    ],
    'observacoes' => [
        'observacoesLocalizacao'
    ]
];

$camposObrigatorios = [
    'identificacao' => [
        'departamentoNome',
        'departamentoSigla',
        'edificioLocalizacao',
        'pisoLocalizacao',
        'salaLocalizacao',
        'estadoLocalizacao'
    ],
    'caracteristicas' => [
        'tipoEspaco'
    ],
    'observacoes' => []
];

$labelsCampos = [
    'departamentoNome' => 'Nome do Departamento / Serviço',
    'departamentoSigla' => 'Sigla',
    'edificioLocalizacao' => 'Edifício',
    'pisoLocalizacao' => 'Piso',
    'salaLocalizacao' => 'Sala',
    'estadoLocalizacao' => 'Estado',
    'tipoEspaco' => 'Tipo de Espaço'
];

$errosLocalizacao = [];

if (isset($_GET['limpar'])) {
    unset($_SESSION[$chaveSessao]);
    header('Location: ' . $ficheiroAtual);
    exit;
}

$etapaAtual = $_GET['etapa'] ?? $etapas[0];
if (!in_array($etapaAtual, $etapas, true)) {
    $etapaAtual = $etapas[0];
}

function normalizar_codigo_localizacao($texto)
{
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    $texto = preg_replace('/[^a-zA-Z0-9]/', '', $texto);

    return strtoupper($texto);
}

function gerar_codigo_localizacao_temporario($chaveSessao)
{
    $departamentoSigla = normalizar_codigo_localizacao($_SESSION[$chaveSessao]['departamentoSigla'] ?? '');
    $piso = normalizar_codigo_localizacao($_SESSION[$chaveSessao]['pisoLocalizacao'] ?? '');
    $sala = normalizar_codigo_localizacao($_SESSION[$chaveSessao]['salaLocalizacao'] ?? '');

    if ($departamentoSigla === '' || $piso === '' || $sala === '') {
        return '';
    }

    return $departamentoSigla . '-P' . $piso . '-S' . $sala;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etapaSubmetida = $_POST['etapa_atual'] ?? $etapaAtual;

    if (!in_array($etapaSubmetida, $etapas, true)) {
        $etapaSubmetida = $etapas[0];
    }

    $acaoEtapa = $_POST['acao_etapa'] ?? '';

    if ($acaoEtapa === 'limpar_etapa') {
        limpar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposPorEtapa);

        header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaSubmetida));
        exit;
    }

    guardar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposPorEtapa);
    $_SESSION[$chaveSessao]['codigoLocalizacao'] = gerar_codigo_localizacao_temporario($chaveSessao);

    if ($acaoEtapa === 'anterior') {
        header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode(etapa_anterior($etapaSubmetida, $etapas)));
        exit;
    }

    if (isset($_POST['etapa_destino'])) {
        $etapaDestino = $_POST['etapa_destino'];

        if (!in_array($etapaDestino, $etapas, true)) {
            $etapaDestino = $etapaSubmetida;
        }

        $indiceSubmetida = indice_etapa($etapaSubmetida, $etapas);
        $indiceDestino = indice_etapa($etapaDestino, $etapas);

        if ($indiceDestino <= $indiceSubmetida) {
            header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaDestino));
            exit;
        }

        for ($i = 0; $i < $indiceDestino; $i++) {
            $etapaValidar = $etapas[$i];
            $errosEtapa = validar_etapa_temporaria($chaveSessao, $etapaValidar, $camposObrigatorios, $labelsCampos);

            if (!empty($errosEtapa)) {
                $errosLocalizacao = $errosEtapa;
                $etapaAtual = $etapaValidar;
                break;
            }
        }

        if (empty($errosLocalizacao)) {
            header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaDestino));
            exit;
        }
    }

    if (empty($errosLocalizacao)) {
        $errosLocalizacao = validar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposObrigatorios, $labelsCampos);

        if (!empty($errosLocalizacao)) {
            $etapaAtual = $etapaSubmetida;
        } else {
            $proximaEtapa = proxima_etapa($etapaSubmetida, $etapas);

            if ($proximaEtapa !== null) {
                header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($proximaEtapa));
                exit;
            }
        }
    }

    if (empty($errosLocalizacao)) {
        foreach ($etapas as $etapa) {
            $errosEtapa = validar_etapa_temporaria($chaveSessao, $etapa, $camposObrigatorios, $labelsCampos);

            if (!empty($errosEtapa)) {
                $errosLocalizacao = $errosEtapa;
                $etapaAtual = $etapa;
                break;
            }
        }
    }

    if (empty($errosLocalizacao)) {
        $dadosLocalizacao = $_SESSION[$chaveSessao] ?? [];
        $codigo = gerar_codigo_localizacao_temporario($chaveSessao);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO localizacoes (
                    codigo,
                    departamento_nome,
                    departamento_sigla,
                    edificio,
                    piso,
                    sala,
                    tipo_espaco,
                    estado,
                    capacidade_equipamentos,
                    permite_equipamentos_criticos,
                    observacoes,
                    isActive
                ) VALUES (
                    :codigo,
                    :departamento_nome,
                    :departamento_sigla,
                    :edificio,
                    :piso,
                    :sala,
                    :tipo_espaco,
                    :estado,
                    :capacidade_equipamentos,
                    :permite_equipamentos_criticos,
                    :observacoes,
                    1
                )
            ");

            $stmt->execute([
                ':codigo' => $codigo,
                ':departamento_nome' => $dadosLocalizacao['departamentoNome'] ?? '',
                ':departamento_sigla' => normalizar_codigo_localizacao($dadosLocalizacao['departamentoSigla'] ?? ''),
                ':edificio' => $dadosLocalizacao['edificioLocalizacao'] ?? '',
                ':piso' => $dadosLocalizacao['pisoLocalizacao'] ?? '',
                ':sala' => $dadosLocalizacao['salaLocalizacao'] ?? '',
                ':tipo_espaco' => $dadosLocalizacao['tipoEspaco'] ?? '',
                ':estado' => $dadosLocalizacao['estadoLocalizacao'] ?? 'Ativa',
                ':capacidade_equipamentos' => ($dadosLocalizacao['capacidadeEquipamentos'] ?? '') !== '' ? (int) $dadosLocalizacao['capacidadeEquipamentos'] : null,
                ':permite_equipamentos_criticos' => (int) ($dadosLocalizacao['permiteCriticos'] ?? 0),
                ':observacoes' => $dadosLocalizacao['observacoesLocalizacao'] ?? ''
            ]);

            unset($_SESSION[$chaveSessao]);
            header('Location: lista_localizacoes.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errosLocalizacao[] = 'Já existe uma localização com esse código.';
                $etapaAtual = 'identificacao';
            } else {
                $errosLocalizacao[] = 'Ocorreu um erro ao guardar a localização.';
            }
        }
    }
}

if (!isset($_SESSION[$chaveSessao]['permiteCriticos'])) {
    $_SESSION[$chaveSessao]['permiteCriticos'] = '0';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

    <!-- Conteudo principal no mesmo formato do novo fornecedor. -->
    <main class="conteudo-private ficha-equipamento-page novo-equipamento-page ficha-localizacao-page">

        <!-- Botoes principais do formulario por etapas. -->
        <div class="form-actions">
            <a href="lista_localizacoes.php" class="btn btn-cancelar">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar
            </a>

            <button type="submit"
                    class="btn btn-limpar"
                    name="acao_etapa"
                    value="limpar_etapa"
                    form="formNovaLocalizacao"
                    formnovalidate>
                <i class="fa-solid fa-eraser me-2"></i> Limpar
            </button>

            <?php if ($etapaAtual !== $etapas[0]): ?>
                <button type="submit"
                        class="btn btn-limpar"
                        name="acao_etapa"
                        value="anterior"
                        form="formNovaLocalizacao"
                        formnovalidate>
                    <i class="fa-solid fa-arrow-left me-2"></i> Anterior
                </button>
            <?php endif; ?>

            <button type="submit"
                    class="btn btn-guardar"
                    name="acao_etapa"
                    value="<?php echo $etapaAtual === 'observacoes' ? 'finalizar' : 'continuar'; ?>"
                    form="formNovaLocalizacao"
                    formnovalidate>
                <i class="fa-solid <?php echo $etapaAtual === 'observacoes' ? 'fa-floppy-disk' : 'fa-arrow-right'; ?> me-2"></i>
                <?php echo $etapaAtual === 'observacoes' ? 'Guardar Localização' : 'Guardar e Continuar'; ?>
            </button>
        </div>

        <!-- Formulario de nova localizacao organizado por etapas. -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formNovaLocalizacao"
              action="nova_localizacao.php?etapa=<?php echo urlencode($etapaAtual); ?>"
              method="post">

            <input type="hidden" name="etapa_atual" value="<?php echo htmlspecialchars($etapaAtual); ?>">

            <div class="form-stepper"
                 style="grid-template-columns: repeat(<?php echo count($etapas); ?>, minmax(0, 1fr));"
                 aria-label="Progresso do registo da localização">
                <?php foreach ($etapas as $indice => $etapa): ?>
                    <div class="<?php echo classe_stepper($etapa, $etapaAtual, $etapas); ?>">
                        <span class="form-step-numero">
                            <?php if (indice_etapa($etapa, $etapas) < indice_etapa($etapaAtual, $etapas)): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <?php echo $indice + 1; ?>
                            <?php endif; ?>
                        </span>

                        <span class="form-step-label">
                            <?php echo htmlspecialchars($nomesEtapasLocalizacao[$etapa]); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-step-heading">
                <h3>
                    Etapa <?php echo indice_etapa($etapaAtual, $etapas) + 1; ?>
                    de <?php echo count($etapas); ?>:
                    <?php echo htmlspecialchars($nomesEtapasLocalizacao[$etapaAtual]); ?>
                </h3>
            </div>

            <?php if (!empty($errosLocalizacao)): ?>
                <div class="form-alerta-erros" role="alert">
                    <strong>
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Não é possível avançar para essa etapa.
                    </strong>

                    <p class="mb-2 mt-2">
                        Preencha os campos obrigatórios antes de continuar.
                    </p>

                    <ul>
                        <?php foreach ($errosLocalizacao as $erro): ?>
                            <li><?php echo htmlspecialchars($erro); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="ficha-area">
                <ul class="nav nav-tabs ficha-tabs" id="tabsNovaLocalizacao" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab('identificacao', $etapaAtual); ?>"
                                id="identificacao-tab"
                                name="etapa_destino"
                                value="identificacao"
                                formnovalidate
                                role="tab"
                                aria-controls="identificacao"
                                aria-selected="<?php echo aria_tab('identificacao', $etapaAtual); ?>">
                            <i class="fa-solid fa-location-dot me-2"></i> Identificação
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab('caracteristicas', $etapaAtual); ?>"
                                id="caracteristicas-tab"
                                name="etapa_destino"
                                value="caracteristicas"
                                formnovalidate
                                role="tab"
                                aria-controls="caracteristicas"
                                aria-selected="<?php echo aria_tab('caracteristicas', $etapaAtual); ?>">
                            <i class="fa-solid fa-hospital me-2"></i> Características
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab('observacoes', $etapaAtual); ?>"
                                id="observacoes-tab"
                                name="etapa_destino"
                                value="observacoes"
                                formnovalidate
                                role="tab"
                                aria-controls="observacoes-tab-pane"
                                aria-selected="<?php echo aria_tab('observacoes', $etapaAtual); ?>">
                            <i class="fa-solid fa-clipboard-list me-2"></i> Observações
                        </button>
                    </li>
                </ul>

                <div class="tab-content ficha-tab-content" id="tabsNovaLocalizacaoContent">
                    <div class="<?php echo classe_painel('identificacao', $etapaAtual); ?>"
                         id="identificacao"
                         role="tabpanel"
                         aria-labelledby="identificacao-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Identificação da Localização</h4>
                            <p>Preencha os dados principais da sala hospitalar.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-5">
                                <label for="departamentoNome" class="form-label">Nome do Departamento / Serviço *</label>
                                <input type="text"
                                       class="form-control"
                                       id="departamentoNome"
                                       name="departamentoNome"
                                       value="<?php echo valor_temporario($chaveSessao, 'departamentoNome'); ?>"
                                       placeholder="Ex: Unidade de Cuidados Intensivos"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label for="departamentoSigla" class="form-label">Sigla *</label>
                                <input type="text"
                                       class="form-control"
                                       id="departamentoSigla"
                                       name="departamentoSigla"
                                       value="<?php echo valor_temporario($chaveSessao, 'departamentoSigla'); ?>"
                                       placeholder="Ex: UCI"
                                       maxlength="20"
                                       required>
                            </div>

                            <div class="col-md-5">
                                <label for="codigoLocalizacao" class="form-label">Código da Localização *</label>
                                <input type="text"
                                       class="form-control"
                                       id="codigoLocalizacao"
                                       name="codigoLocalizacao"
                                       value="<?php echo valor_temporario($chaveSessao, 'codigoLocalizacao'); ?>"
                                       placeholder="Gerado automaticamente"
                                       readonly
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="edificioLocalizacao" class="form-label">Edifício *</label>
                                <input type="text"
                                       class="form-control"
                                       id="edificioLocalizacao"
                                       name="edificioLocalizacao"
                                       value="<?php echo valor_temporario($chaveSessao, 'edificioLocalizacao'); ?>"
                                       placeholder="Ex: Edifício A"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label for="pisoLocalizacao" class="form-label">Piso *</label>
                                <input type="text"
                                       class="form-control"
                                       id="pisoLocalizacao"
                                       name="pisoLocalizacao"
                                       value="<?php echo valor_temporario($chaveSessao, 'pisoLocalizacao'); ?>"
                                       placeholder="Ex: 2"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label for="salaLocalizacao" class="form-label">Sala *</label>
                                <input type="text"
                                       class="form-control"
                                       id="salaLocalizacao"
                                       name="salaLocalizacao"
                                       value="<?php echo valor_temporario($chaveSessao, 'salaLocalizacao'); ?>"
                                       placeholder="Ex: 201"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label for="estadoLocalizacao" class="form-label">Estado *</label>
                                <select class="form-select"
                                        id="estadoLocalizacao"
                                        name="estadoLocalizacao"
                                        required>
                                    <option value="">Selecionar estado</option>
                                    <option value="Ativa" <?php echo selected_temporario($chaveSessao, 'estadoLocalizacao', 'Ativa'); ?>>Ativa</option>
                                    <option value="Inativa" <?php echo selected_temporario($chaveSessao, 'estadoLocalizacao', 'Inativa'); ?>>Inativa</option>
                                    <option value="Em manutenção" <?php echo selected_temporario($chaveSessao, 'estadoLocalizacao', 'Em manutenção'); ?>>Em manutenção</option>
                                    <option value="Indisponível" <?php echo selected_temporario($chaveSessao, 'estadoLocalizacao', 'Indisponível'); ?>>Indisponível</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="<?php echo classe_painel('caracteristicas', $etapaAtual); ?>"
                         id="caracteristicas"
                         role="tabpanel"
                         aria-labelledby="caracteristicas-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Características da Localização</h4>
                            <p>Defina o tipo de espaço, capacidade e possibilidade de alojar equipamentos críticos.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="tipoEspaco" class="form-label">Tipo de Espaço *</label>
                                <select class="form-select"
                                        id="tipoEspaco"
                                        name="tipoEspaco"
                                        required>
                                    <option value="">Selecionar tipo de espaço</option>
                                    <option value="UCI" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'UCI'); ?>>UCI</option>
                                    <option value="Urgência" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Urgência'); ?>>Urgência</option>
                                    <option value="Bloco Operatório" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Bloco Operatório'); ?>>Bloco Operatório</option>
                                    <option value="Laboratório" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Laboratório'); ?>>Laboratório</option>
                                    <option value="Consulta Externa" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Consulta Externa'); ?>>Consulta Externa</option>
                                    <option value="Armazém Técnico" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Armazém Técnico'); ?>>Armazém Técnico</option>
                                    <option value="Sala de Equipamentos" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Sala de Equipamentos'); ?>>Sala de Equipamentos</option>
                                    <option value="Esterilização" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Esterilização'); ?>>Esterilização</option>
                                    <option value="Outro" <?php echo selected_temporario($chaveSessao, 'tipoEspaco', 'Outro'); ?>>Outro</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="capacidadeEquipamentos" class="form-label">Capacidade de Equipamentos</label>
                                <input type="number"
                                       class="form-control"
                                       id="capacidadeEquipamentos"
                                       name="capacidadeEquipamentos"
                                       min="0"
                                       value="<?php echo valor_temporario($chaveSessao, 'capacidadeEquipamentos'); ?>"
                                       placeholder="Ex: 10">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Permite equipamentos críticos?</label>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="permiteCriticos"
                                           id="permiteCriticosSim"
                                           value="1"
                                           <?php echo checked_temporario($chaveSessao, 'permiteCriticos', '1'); ?>>
                                    <label class="form-check-label" for="permiteCriticosSim">Sim</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="permiteCriticos"
                                           id="permiteCriticosNao"
                                           value="0"
                                           <?php echo checked_temporario($chaveSessao, 'permiteCriticos', '0'); ?>>
                                    <label class="form-check-label" for="permiteCriticosNao">Não</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="<?php echo classe_painel('observacoes', $etapaAtual); ?>"
                         id="observacoes-tab-pane"
                         role="tabpanel"
                         aria-labelledby="observacoes-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Observações</h4>
                            <p>Campo livre para contexto, limitações ou condições técnicas do espaço.</p>
                        </div>

                        <textarea class="form-control"
                                  id="observacoesLocalizacao"
                                  name="observacoesLocalizacao"
                                  rows="7"
                                  placeholder="Indique observações relevantes sobre a localização."><?php echo valor_temporario($chaveSessao, 'observacoesLocalizacao'); ?></textarea>
                    </div>
                </div>
            </div>
        </form>
    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
