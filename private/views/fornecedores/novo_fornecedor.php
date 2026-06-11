<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
   FORMULARIO POR ETAPAS DO NOVO FORNECEDOR
   Guarda temporariamente os dados textuais em sessao e so cria
   o fornecedor na base de dados no ultimo separador.
   ========================================================= */
$etapasFornecedor = [
    'identificacao',
    'contactos',
    'morada',
    'contrato',
    'observacoes',
    'documentos'
];

$camposPorEtapaFornecedor = [
    'identificacao' => ['nomeFornecedor', 'nifFornecedor', 'tipoFornecedor', 'estadoFornecedor'],
    'contactos' => ['emailFornecedor', 'telefoneFornecedor', 'websiteFornecedor', 'contactoResponsavel', 'telefoneContacto', 'emailContacto'],
    'morada' => ['moradaFornecedor', 'codigoPostalFornecedor', 'localidadeFornecedor', 'paisFornecedor'],
    'contrato' => ['contratoFornecedor', 'inicioContratoFornecedor', 'fimContratoFornecedor', 'areaAtuacaoFornecedor', 'equipamentosAssociadosFornecedor'],
    'observacoes' => ['observacoesFornecedor'],
    'documentos' => []
];

$camposObrigatoriosFornecedor = [
    'identificacao' => ['nomeFornecedor', 'nifFornecedor', 'tipoFornecedor', 'estadoFornecedor'],
    'contactos' => ['emailFornecedor', 'telefoneFornecedor'],
    'morada' => ['localidadeFornecedor'],
    'contrato' => [],
    'observacoes' => [],
    'documentos' => []
];

$labelsCamposFornecedor = [
    'nomeFornecedor' => 'Nome do Fornecedor',
    'nifFornecedor' => 'NIF',
    'tipoFornecedor' => 'Tipo de Fornecedor',
    'estadoFornecedor' => 'Estado',
    'emailFornecedor' => 'Email',
    'telefoneFornecedor' => 'Telefone',
    'localidadeFornecedor' => 'Localidade'
];

$nomesEtapasFornecedor = [
    'identificacao' => 'Identificação',
    'contactos' => 'Contactos',
    'morada' => 'Morada',
    'contrato' => 'Serviços',
    'observacoes' => 'Observações',
    'documentos' => 'Documentos'
];

if (isset($_GET['limpar'])) {
    unset($_SESSION['novo_fornecedor']);
    header('Location: novo_fornecedor.php');
    exit;
}

$etapaAtual = $_GET['etapa'] ?? 'identificacao';
if (!in_array($etapaAtual, $etapasFornecedor, true)) {
    $etapaAtual = 'identificacao';
}

function indice_etapa_fornecedor($etapa, $etapas)
{
    $indice = array_search($etapa, $etapas, true);
    return $indice === false ? 0 : $indice;
}

function proxima_etapa_fornecedor($etapa, $etapas)
{
    $indice = indice_etapa_fornecedor($etapa, $etapas);
    return $etapas[min($indice + 1, count($etapas) - 1)];
}

function etapa_anterior_fornecedor($etapa, $etapas)
{
    $indice = indice_etapa_fornecedor($etapa, $etapas);
    return $etapas[max($indice - 1, 0)];
}

function guardar_etapa_fornecedor($etapa, $camposPorEtapa)
{
    if (!isset($camposPorEtapa[$etapa])) {
        return;
    }

    foreach ($camposPorEtapa[$etapa] as $campo) {
        $_SESSION['novo_fornecedor'][$campo] = trim($_POST[$campo] ?? '');
    }
}

function valor_fornecedor_temp($campo, $padrao = '')
{
    return htmlspecialchars($_SESSION['novo_fornecedor'][$campo] ?? $padrao);
}

function selected_fornecedor_temp($campo, $valor, $padrao = '')
{
    $valorAtual = $_SESSION['novo_fornecedor'][$campo] ?? $padrao;
    return $valorAtual === $valor ? 'selected' : '';
}

function classe_tab_fornecedor($etapa, $etapaAtual)
{
    return $etapa === $etapaAtual ? 'nav-link active' : 'nav-link';
}

function aria_tab_fornecedor($etapa, $etapaAtual)
{
    return $etapa === $etapaAtual ? 'true' : 'false';
}

function classe_painel_fornecedor($etapa, $etapaAtual)
{
    return $etapa === $etapaAtual ? 'tab-pane fade show active' : 'tab-pane fade';
}

function classe_stepper_fornecedor($etapa, $etapaAtual, $etapas)
{
    $indiceEtapa = indice_etapa_fornecedor($etapa, $etapas);
    $indiceAtual = indice_etapa_fornecedor($etapaAtual, $etapas);

    if ($indiceEtapa < $indiceAtual) {
        return 'form-step concluido';
    }

    if ($indiceEtapa === $indiceAtual) {
        return 'form-step atual';
    }

    return 'form-step pendente';
}

$errosFornecedor = [];

function validar_etapa_fornecedor($etapa, $camposObrigatorios, $labelsCampos)
{
    $erros = [];

    foreach ($camposObrigatorios[$etapa] ?? [] as $campo) {
        if (trim($_SESSION['novo_fornecedor'][$campo] ?? '') === '') {
            $erros[] = 'Preencha o campo ' . ($labelsCampos[$campo] ?? $campo) . '.';
        }
    }

    return $erros;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etapaSubmetida = $_POST['etapa_atual'] ?? $etapaAtual;
    if (!in_array($etapaSubmetida, $etapasFornecedor, true)) {
        $etapaSubmetida = 'identificacao';
    }

    guardar_etapa_fornecedor($etapaSubmetida, $camposPorEtapaFornecedor);

    if (($_POST['acao_etapa'] ?? '') === 'anterior') {
        header('Location: novo_fornecedor.php?etapa=' . etapa_anterior_fornecedor($etapaSubmetida, $etapasFornecedor));
        exit;
    }

    $errosFornecedor = validar_etapa_fornecedor($etapaSubmetida, $camposObrigatoriosFornecedor, $labelsCamposFornecedor);

    if (!empty($errosFornecedor)) {
        $etapaAtual = $etapaSubmetida;
    } elseif ($etapaSubmetida !== 'documentos') {
        header('Location: novo_fornecedor.php?etapa=' . proxima_etapa_fornecedor($etapaSubmetida, $etapasFornecedor));
        exit;
    }

    if (empty($errosFornecedor)) {
        foreach ($camposObrigatoriosFornecedor as $etapa => $campos) {
            $errosEtapa = validar_etapa_fornecedor($etapa, $camposObrigatoriosFornecedor, $labelsCamposFornecedor);
            if (!empty($errosEtapa)) {
                $errosFornecedor = array_merge($errosFornecedor, $errosEtapa);
                $etapaAtual = $etapa;
                break;
            }
        }
    }

    if (empty($errosFornecedor)) {
        $dadosFornecedor = $_SESSION['novo_fornecedor'] ?? [];

    $stmt = $pdo->prepare("
        INSERT INTO fornecedores (
            nome_empresa,
            tipo_fornecedor,
            nif,
            email,
            telefone,
            website,
            pessoa_contacto,
            telefone_contacto,
            email_contacto,
            morada,
            codigo_postal,
            localidade,
            pais,
            observacoes,
            isActive
        ) VALUES (
            :nome_empresa,
            :tipo_fornecedor,
            :nif,
            :email,
            :telefone,
            :website,
            :pessoa_contacto,
            :telefone_contacto,
            :email_contacto,
            :morada,
            :codigo_postal,
            :localidade,
            :pais,
            :observacoes,
            1
        )
    ");

    $stmt->execute([
        ':nome_empresa' => $dadosFornecedor['nomeFornecedor'] ?? '',
        ':tipo_fornecedor' => $dadosFornecedor['tipoFornecedor'] ?? '',
        ':nif' => $dadosFornecedor['nifFornecedor'] ?? '',
        ':email' => $dadosFornecedor['emailFornecedor'] ?? '',
        ':telefone' => $dadosFornecedor['telefoneFornecedor'] ?? '',
        ':website' => $dadosFornecedor['websiteFornecedor'] ?? '',
        ':pessoa_contacto' => $dadosFornecedor['contactoResponsavel'] ?? '',
        ':telefone_contacto' => $dadosFornecedor['telefoneContacto'] ?? '',
        ':email_contacto' => $dadosFornecedor['emailContacto'] ?? '',
        ':morada' => $dadosFornecedor['moradaFornecedor'] ?? '',
        ':codigo_postal' => $dadosFornecedor['codigoPostalFornecedor'] ?? '',
        ':localidade' => $dadosFornecedor['localidadeFornecedor'] ?? '',
        ':pais' => $dadosFornecedor['paisFornecedor'] ?? 'Portugal',
        ':observacoes' => $dadosFornecedor['observacoesFornecedor'] ?? ''
    ]);

    $id_fornecedor = $pdo->lastInsertId();

    /* Processa documentos opcionais associados ao fornecedor criado. */
    if (!empty($_FILES['ficheiroDocumento']['name'][0])) {
        $pastaDestino = __DIR__ . '/../../uploads/fornecedores/' . $id_fornecedor . '/';

        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        foreach ($_FILES['ficheiroDocumento']['name'] as $index => $nomeOriginal) {
            if (empty($nomeOriginal)) {
                continue;
            }

            $tipoDocumento = trim($_POST['tipoDocumento'][$index] ?? '');
            $numeroDocumento = trim($_POST['numeroDocumento'][$index] ?? '');
            $nomeDocumento = trim($_POST['nomeDocumento'][$index] ?? '');

            if ($tipoDocumento === '' || $numeroDocumento === '' || $nomeDocumento === '') {
                continue;
            }

            $nomeSeguro = date('YmdHis') . '_' . $index . '_' . basename($nomeOriginal);
            $caminhoFisico = $pastaDestino . $nomeSeguro;
            $caminhoBD = 'private/uploads/fornecedores/' . $id_fornecedor . '/' . $nomeSeguro;

            if (!move_uploaded_file($_FILES['ficheiroDocumento']['tmp_name'][$index], $caminhoFisico)) {
                continue;
            }

            $stmtDoc = $pdo->prepare("
                INSERT INTO documentos_fornecedores (
                    id_fornecedor,
                    tipo_documento,
                    numero_documento,
                    nome_documento,
                    caminho_ficheiro,
                    data_documento,
                    data_validade
                ) VALUES (
                    :id_fornecedor,
                    :tipo_documento,
                    :numero_documento,
                    :nome_documento,
                    :caminho_ficheiro,
                    :data_documento,
                    :data_validade
                )
            ");

            $stmtDoc->execute([
                ':id_fornecedor' => $id_fornecedor,
                ':tipo_documento' => $tipoDocumento,
                ':numero_documento' => $numeroDocumento,
                ':nome_documento' => $nomeDocumento,
                ':caminho_ficheiro' => $caminhoBD,
                ':data_documento' => ($_POST['dataDocumento'][$index] ?? '') ?: null,
                ':data_validade' => ($_POST['dataValidadeDocumento'][$index] ?? '') ?: null
            ]);
        }
    }

    unset($_SESSION['novo_fornecedor']);
    header('Location: lista_fornecedores.php');
    exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

    <!-- =========================================================
         CONTEÚDO PRINCIPAL DO NOVO FORNECEDOR
         Usa a mesma base visual do novo equipamento: ações no topo,
         formulário em separadores e área de documentos dinâmica.
         ========================================================= -->
    <main class="conteudo-private ficha-equipamento-page novo-equipamento-page ficha-fornecedor-page">

        <!-- =====================================================
             BOTÕES PRINCIPAIS DO FORMULÁRIO
             Cancelar volta à lista, Limpar apaga os dados temporários
             e Guardar/Continuar avança entre as etapas do formulário.
             ===================================================== -->
        <div class="form-actions">
            <a href="lista_fornecedores.php" class="btn btn-cancelar">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar
            </a>

            <a href="novo_fornecedor.php?limpar=1" class="btn btn-limpar">
                <i class="fa-solid fa-eraser me-2"></i> Limpar
            </a>

            <?php if ($etapaAtual !== 'identificacao'): ?>
                <button type="submit"
                        class="btn btn-limpar"
                        name="acao_etapa"
                        value="anterior"
                        form="formNovoFornecedor"
                        formnovalidate>
                    <i class="fa-solid fa-arrow-left me-2"></i> Anterior
                </button>
            <?php endif; ?>

            <button type="submit"
                    class="btn btn-guardar"
                    name="acao_etapa"
                    value="<?php echo $etapaAtual === 'documentos' ? 'finalizar' : 'continuar'; ?>"
                    form="formNovoFornecedor"
                    formnovalidate>
                <i class="fa-solid <?php echo $etapaAtual === 'documentos' ? 'fa-floppy-disk' : 'fa-arrow-right'; ?> me-2"></i>
                <?php echo $etapaAtual === 'documentos' ? 'Guardar Fornecedor' : 'Guardar e Continuar'; ?>
            </button>
        </div>

        <!-- =====================================================
             FORMULARIO DE NOVO FORNECEDOR
             Recolhe os dados necessarios para criar uma nova entidade
             fornecedora e permite anexar documentos.
             ===================================================== -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formNovoFornecedor"
              action="novo_fornecedor.php?etapa=<?php echo urlencode($etapaAtual); ?>"
              method="post"
              enctype="multipart/form-data">

            <input type="hidden" name="etapa_atual" value="<?php echo htmlspecialchars($etapaAtual); ?>">

            <div class="form-stepper" aria-label="Progresso do registo do fornecedor">
                <?php foreach ($etapasFornecedor as $indice => $etapa): ?>
                    <div class="<?php echo classe_stepper_fornecedor($etapa, $etapaAtual, $etapasFornecedor); ?>">
                        <span class="form-step-numero">
                            <?php if (indice_etapa_fornecedor($etapa, $etapasFornecedor) < indice_etapa_fornecedor($etapaAtual, $etapasFornecedor)): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <?php echo $indice + 1; ?>
                            <?php endif; ?>
                        </span>
                        <span class="form-step-label"><?php echo htmlspecialchars($nomesEtapasFornecedor[$etapa]); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-step-heading">
                <h3>
                    Etapa <?php echo indice_etapa_fornecedor($etapaAtual, $etapasFornecedor) + 1; ?>
                    de <?php echo count($etapasFornecedor); ?>:
                    <?php echo htmlspecialchars($nomesEtapasFornecedor[$etapaAtual]); ?>
                </h3>
            </div>

            <?php if (!empty($errosFornecedor)): ?>
                <div class="form-alerta-erros" role="alert">
                    <strong>Antes de avançar, confirme:</strong>
                    <ul>
                        <?php foreach ($errosFornecedor as $erro): ?>
                            <li><?php echo htmlspecialchars($erro); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

<!-- =================================================
                 ÁREA PRINCIPAL DO FORMULÁRIO
                 Caixa que contém os separadores e respetivos campos.
                 ================================================= -->
            <div class="ficha-area">
                <!-- =============================================
                     SEPARADORES DO FORMULÁRIO
                     Mantêm o registo organizado numa única página.
                     ============================================= -->
                <ul class="nav nav-tabs ficha-tabs" id="tabsNovoFornecedor" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="<?php echo classe_tab_fornecedor('identificacao', $etapaAtual); ?>"
                           id="identificacao-tab"
                           href="novo_fornecedor.php?etapa=identificacao"
                           role="tab"
                           aria-controls="identificacao"
                           aria-selected="<?php echo aria_tab_fornecedor('identificacao', $etapaAtual); ?>">
                            <i class="fa-solid fa-building me-2"></i>
                            Identificacao
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="<?php echo classe_tab_fornecedor('contactos', $etapaAtual); ?>"
                           id="contactos-tab"
                           href="novo_fornecedor.php?etapa=contactos"
                           role="tab"
                           aria-controls="contactos"
                           aria-selected="<?php echo aria_tab_fornecedor('contactos', $etapaAtual); ?>">
                            <i class="fa-solid fa-address-book me-2"></i>
                            Contactos
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="<?php echo classe_tab_fornecedor('morada', $etapaAtual); ?>"
                           id="morada-tab"
                           href="novo_fornecedor.php?etapa=morada"
                           role="tab"
                           aria-controls="morada"
                           aria-selected="<?php echo aria_tab_fornecedor('morada', $etapaAtual); ?>">
                            <i class="fa-solid fa-location-dot me-2"></i>
                            Morada
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="<?php echo classe_tab_fornecedor('contrato', $etapaAtual); ?>"
                           id="contrato-tab"
                           href="novo_fornecedor.php?etapa=contrato"
                           role="tab"
                           aria-controls="contrato"
                           aria-selected="<?php echo aria_tab_fornecedor('contrato', $etapaAtual); ?>">
                            <i class="fa-solid fa-file-contract me-2"></i>
                            Servicos
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="<?php echo classe_tab_fornecedor('observacoes', $etapaAtual); ?>"
                           id="observacoes-tab"
                           href="novo_fornecedor.php?etapa=observacoes"
                           role="tab"
                           aria-controls="observacoes-tab-pane"
                           aria-selected="<?php echo aria_tab_fornecedor('observacoes', $etapaAtual); ?>">
                            <i class="fa-solid fa-clipboard-list me-2"></i>
                            Observacoes
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="<?php echo classe_tab_fornecedor('documentos', $etapaAtual); ?>"
                           id="documentos-tab"
                           href="novo_fornecedor.php?etapa=documentos"
                           role="tab"
                           aria-controls="documentos"
                           aria-selected="<?php echo aria_tab_fornecedor('documentos', $etapaAtual); ?>">
                            <i class="fa-solid fa-folder-open me-2"></i>
                            Documentos
                        </a>
                    </li>
                </ul>

                <!-- =============================================
                     CONTEÚDO DOS SEPARADORES
                     Cada secção agrupa uma área funcional do fornecedor.
                     ============================================= -->
                <div class="tab-content ficha-tab-content" id="tabsNovoFornecedorContent">
                    <!-- =========================================
                         SEPARADOR 1: IDENTIFICAÇÃO
                         Dados principais e classificação do fornecedor.
                         ========================================= -->
                    <div class="<?php echo classe_painel_fornecedor('identificacao', $etapaAtual); ?>"
                         id="identificacao"
                         role="tabpanel"
                         aria-labelledby="identificacao-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Identificação do Fornecedor</h4>
                            <p>Preencha os dados principais da entidade fornecedora.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-8">
                                <label for="nomeFornecedor" class="form-label">Nome do Fornecedor *</label>
                                <input type="text"
                                       class="form-control"
                                       id="nomeFornecedor"
                                       name="nomeFornecedor"
                                       value="<?php echo valor_fornecedor_temp('nomeFornecedor'); ?>"
                                       placeholder="Ex: MedSupply Portugal"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="nifFornecedor" class="form-label">NIF *</label>
                                <input type="text"
                                       class="form-control"
                                       id="nifFornecedor"
                                       name="nifFornecedor"
                                       value="<?php echo valor_fornecedor_temp('nifFornecedor'); ?>"
                                       placeholder="Ex: 514987321"
                                       required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label d-block">Tipo de Fornecedor *</label>

                                <div class="tipos-fornecedor-opcoes">
                                    <select class="form-select" id="tipoFornecedor" name="tipoFornecedor" required>
                                        <option value="">Selecionar tipo</option>
                                        <option value="Manuten&ccedil;&atilde;o" <?php echo selected_fornecedor_temp('tipoFornecedor', html_entity_decode('Manuten&ccedil;&atilde;o', ENT_QUOTES, 'UTF-8')); ?>>Manuten&ccedil;&atilde;o</option>
                                        <option value="Comercial" <?php echo selected_fornecedor_temp('tipoFornecedor', 'Comercial'); ?>>Comercial</option>
                                        <option value="Fabricante" <?php echo selected_fornecedor_temp('tipoFornecedor', 'Fabricante'); ?>>Fabricante</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="estadoFornecedor" class="form-label">Estado *</label>
                                <select class="form-select" id="estadoFornecedor" name="estadoFornecedor" required>
                                    <option value="">Selecionar estado</option>
                                    <option value="Ativo" <?php echo selected_fornecedor_temp('estadoFornecedor', 'Ativo'); ?>>Ativo</option>
                                    <option value="Inativo" <?php echo selected_fornecedor_temp('estadoFornecedor', 'Inativo'); ?>>Inativo</option>
                                    <option value="Em avalia&ccedil;&atilde;o" <?php echo selected_fornecedor_temp('estadoFornecedor', html_entity_decode('Em avalia&ccedil;&atilde;o', ENT_QUOTES, 'UTF-8')); ?>>Em avalia&ccedil;&atilde;o</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 2: CONTACTOS
                         Dados de contacto geral e contacto responsável.
                         ========================================= -->
                    <div class="<?php echo classe_painel_fornecedor('contactos', $etapaAtual); ?>"
                         id="contactos"
                         role="tabpanel"
                         aria-labelledby="contactos-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Contactos</h4>
                            <p>Indique os contactos gerais e o responsável preferencial.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="emailFornecedor" class="form-label">Email Geral *</label>
                                <input type="email"
                                       class="form-control"
                                       id="emailFornecedor"
                                       name="emailFornecedor"
                                       value="<?php echo valor_fornecedor_temp('emailFornecedor'); ?>"
                                       placeholder="Ex: comercial@fornecedor.pt"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneFornecedor" class="form-label">Telefone *</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneFornecedor"
                                       name="telefoneFornecedor"
                                       value="<?php echo valor_fornecedor_temp('telefoneFornecedor'); ?>"
                                       placeholder="Ex: +351 221 234 567"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="websiteFornecedor" class="form-label">Website</label>
                                <input type="url"
                                       class="form-control"
                                       id="websiteFornecedor"
                                       name="websiteFornecedor"
                                       value="<?php echo valor_fornecedor_temp('websiteFornecedor'); ?>"
                                       placeholder="Ex: https://www.fornecedor.pt">
                            </div>

                            <div class="col-md-4">
                                <label for="contactoResponsavel" class="form-label">Pessoa de Contacto</label>
                                <input type="text"
                                       class="form-control"
                                       id="contactoResponsavel"
                                       name="contactoResponsavel"
                                       value="<?php echo valor_fornecedor_temp('contactoResponsavel'); ?>"
                                       placeholder="Ex: Ana Martins">
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneContacto" class="form-label">Telefone do Contacto</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneContacto"
                                       name="telefoneContacto"
                                       value="<?php echo valor_fornecedor_temp('telefoneContacto'); ?>"
                                       placeholder="Ex: 912 345 678">
                            </div>

                            <div class="col-md-4">
                                <label for="emailContacto" class="form-label">Email do Contacto</label>
                                <input type="email"
                                       class="form-control"
                                       id="emailContacto"
                                       name="emailContacto"
                                       value="<?php echo valor_fornecedor_temp('emailContacto'); ?>"
                                       placeholder="Ex: tecnico@fornecedor.pt">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 3: MORADA
                         Morada e localização da entidade.
                         ========================================= -->
                    <div class="<?php echo classe_painel_fornecedor('morada', $etapaAtual); ?>"
                         id="morada"
                         role="tabpanel"
                         aria-labelledby="morada-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Morada</h4>
                            <p>Registe a morada principal da entidade fornecedora.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="moradaFornecedor" class="form-label">Morada</label>
                                <input type="text"
                                       class="form-control"
                                       id="moradaFornecedor"
                                       name="moradaFornecedor"
                                       value="<?php echo valor_fornecedor_temp('moradaFornecedor'); ?>"
                                       placeholder="Ex: Rua da Tecnologia, nº 120">
                            </div>

                            <div class="col-md-2">
                                <label for="codigoPostalFornecedor" class="form-label">Código Postal</label>
                                <input type="text"
                                       class="form-control"
                                       id="codigoPostalFornecedor"
                                       name="codigoPostalFornecedor"
                                       value="<?php echo valor_fornecedor_temp('codigoPostalFornecedor'); ?>"
                                       placeholder="Ex: 4000-000">
                            </div>

                            <div class="col-md-2">
                                <label for="localidadeFornecedor" class="form-label">Localidade *</label>
                                <input type="text"
                                       class="form-control"
                                       id="localidadeFornecedor"
                                       name="localidadeFornecedor"
                                       value="<?php echo valor_fornecedor_temp('localidadeFornecedor'); ?>"
                                       placeholder="Ex: Porto"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label for="paisFornecedor" class="form-label">País</label>
                                <input type="text"
                                       class="form-control"
                                       id="paisFornecedor"
                                       name="paisFornecedor"
                                       value="<?php echo valor_fornecedor_temp('paisFornecedor', 'Portugal'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 4: SERVIÇOS E CONTRATO
                         Contrato ativo, datas e área de atuação.
                         ========================================= -->
                    <div class="<?php echo classe_painel_fornecedor('contrato', $etapaAtual); ?>"
                         id="contrato"
                         role="tabpanel"
                         aria-labelledby="contrato-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Serviços, Contrato e Associação Técnica</h4>
                            <p>Registe o âmbito da relação técnica e contratual com o fornecedor.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="contratoFornecedor" class="form-label">Contrato Ativo?</label>
                                <select class="form-select" id="contratoFornecedor" name="contratoFornecedor">
                                    <option value="">Selecionar opção</option>
                                    <option value="Sim" <?php echo selected_fornecedor_temp('contratoFornecedor', 'Sim'); ?>>Sim</option>
                                    <option value="N&atilde;o" <?php echo selected_fornecedor_temp('contratoFornecedor', html_entity_decode('N&atilde;o', ENT_QUOTES, 'UTF-8')); ?>>N&atilde;o</option>
                                    <option value="Em an&aacute;lise" <?php echo selected_fornecedor_temp('contratoFornecedor', html_entity_decode('Em an&aacute;lise', ENT_QUOTES, 'UTF-8')); ?>>Em an&aacute;lise</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="inicioContratoFornecedor" class="form-label">Início do Contrato</label>
                                <input type="date"
                                       class="form-control"
                                       id="inicioContratoFornecedor"
                                       name="inicioContratoFornecedor"
                                       value="<?php echo valor_fornecedor_temp('inicioContratoFornecedor'); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="fimContratoFornecedor" class="form-label">Fim do Contrato</label>
                                <input type="date"
                                       class="form-control"
                                       id="fimContratoFornecedor"
                                       name="fimContratoFornecedor"
                                       value="<?php echo valor_fornecedor_temp('fimContratoFornecedor'); ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="areaAtuacaoFornecedor" class="form-label">Área de Atuação</label>
                                <textarea class="form-control"
                                          id="areaAtuacaoFornecedor"
                                          name="areaAtuacaoFornecedor"
                                          rows="5"
                                          placeholder="Ex: venda de equipamentos médicos, manutenção preventiva, calibração de dispositivos clínicos..."><?php echo valor_fornecedor_temp('areaAtuacaoFornecedor'); ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="equipamentosAssociadosFornecedor" class="form-label">Equipamentos / Marcas Associadas</label>
                                <textarea class="form-control"
                                          id="equipamentosAssociadosFornecedor"
                                          name="equipamentosAssociadosFornecedor"
                                          rows="5"
                                          placeholder="Ex: monitores multiparamétricos Philips, ventiladores Dräger, desfibrilhadores Zoll..."><?php echo valor_fornecedor_temp('equipamentosAssociadosFornecedor'); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 5: DOCUMENTOS
                         Permite adicionar contratos, certificados e
                         outros ficheiros associados ao fornecedor.
                         ========================================= -->
                    <div class="<?php echo classe_painel_fornecedor('documentos', $etapaAtual); ?>"
                         id="documentos"
                         role="tabpanel"
                         aria-labelledby="documentos-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h4>Documentos do Fornecedor</h4>
                                <p>Associe contratos, certificados, catálogos ou outros ficheiros relevantes.</p>
                            </div>

                            <button type="button"
                                    class="btn btn-adicionar-documento"
                                    id="btnAdicionarDocumento">
                                <i class="fa-solid fa-plus me-2"></i> Adicionar Documento
                            </button>
                        </div>

                        <div id="listaDocumentos">
                            <div class="documento-form-item">
                                <div class="row g-4 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Tipo de Documento</label>
                                        <select class="form-select" name="tipoDocumento[]">
                                            <option value="">Selecionar tipo</option>
                                            <option value="Contrato de Fornecimento">Contrato de Fornecimento</option>
                                            <option value="Contrato de Manutenção">Contrato de Manutenção</option>
                                            <option value="Contrato de Calibração">Contrato de Calibração</option>
                                            <option value="Certificado Técnico">Certificado Técnico</option>
                                            <option value="Comprovativo fiscal">Comprovativo fiscal</option>
                                            <option value="Outro">Outro</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Numero do Documento</label>
                                        <input type="text"
                                               class="form-control"
                                               name="numeroDocumento[]"
                                               maxlength="30"
                                               placeholder="Ex: DOC-2026-001">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Nome do Documento</label>
                                        <input type="text"
                                               class="form-control"
                                               name="nomeDocumento[]"
                                               placeholder="Ex: Contrato de Fornecimento 2026">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Ficheiro</label>
                                        <input type="file"
                                               class="form-control"
                                               name="ficheiroDocumento[]"
                                               accept=".pdf,.png,.jpg,.jpeg">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Data do Documento</label>
                                        <input type="date"
                                               class="form-control"
                                               name="dataDocumento[]">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Data de Validade</label>
                                        <input type="date"
                                               class="form-control"
                                               name="dataValidadeDocumento[]">
                                    </div>


                                    <div class="col-md-1 text-end">
                                        <button type="button"
                                                class="btn btn-remover-documento"
                                                title="Remover documento">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 6: OBSERVAÇÕES
                         Notas livres sobre o fornecedor.
                         ========================================= -->
                    <div class="<?php echo classe_painel_fornecedor('observacoes', $etapaAtual); ?>"
                         id="observacoes-tab-pane"
                         role="tabpanel"
                         aria-labelledby="observacoes-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Observações</h4>
                            <p>Registe notas relevantes sobre qualidade do serviço, tempos de resposta ou histórico técnico.</p>
                        </div>

                        <textarea class="form-control"
                                  id="observacoesFornecedor"
                                  name="observacoesFornecedor"
                                  rows="7"
                                  placeholder="Indique informações relevantes sobre o fornecedor, qualidade do serviço, tempos de resposta ou notas técnicas."><?php echo valor_fornecedor_temp('observacoesFornecedor'); ?></textarea>
                    </div>
                </div>
            </div>
        </form>

    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
