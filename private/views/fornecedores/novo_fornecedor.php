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
    'identificacao' => [
        'nomeFornecedor',
        'nifFornecedor',
        'tipoFornecedor',
        'estadoFornecedor'
    ],

    'contactos' => [
        'emailFornecedor',
        'telefoneFornecedor',
        'websiteFornecedor',
        'contactoResponsavel',
        'telefoneContacto',
        'emailContacto'
    ],

    'morada' => [
        'moradaFornecedor',
        'codigoPostalFornecedor',
        'localidadeFornecedor',
        'paisFornecedor'
    ],

    'contrato' => [
        'contratoFornecedor',
        'inicioContratoFornecedor',
        'fimContratoFornecedor',
        'areaAtuacaoFornecedor',
        'equipamentosAssociadosFornecedor'
    ],

    'observacoes' => [],

    'documentos' => []
];

$labelsCamposFornecedor = [
    'nomeFornecedor' => 'Nome do Fornecedor',
    'nifFornecedor' => 'NIF',
    'tipoFornecedor' => 'Tipo de Fornecedor',
    'estadoFornecedor' => 'Estado',

    'emailFornecedor' => 'Email Geral',
    'telefoneFornecedor' => 'Telefone',
    'websiteFornecedor' => 'Website',
    'contactoResponsavel' => 'Pessoa de Contacto',
    'telefoneContacto' => 'Telefone do Contacto',
    'emailContacto' => 'Email do Contacto',

    'moradaFornecedor' => 'Morada',
    'codigoPostalFornecedor' => 'Código Postal',
    'localidadeFornecedor' => 'Localidade',
    'paisFornecedor' => 'País',

    'contratoFornecedor' => 'Contrato Ativo',
    'inicioContratoFornecedor' => 'Início do Contrato',
    'fimContratoFornecedor' => 'Fim do Contrato',
    'areaAtuacaoFornecedor' => 'Área de Atuação',
    'equipamentosAssociadosFornecedor' => 'Equipamentos / Marcas Associadas'
];

$nomesEtapasFornecedor = [
    'identificacao' => 'Identificação',
    'contactos' => 'Contactos',
    'morada' => 'Morada',
    'contrato' => 'Serviços',
    'observacoes' => 'Observações',
    'documentos' => 'Documentos'
];

$chaveSessao = 'novo_fornecedor';
$ficheiroAtual = 'novo_fornecedor.php';

$etapas = $etapasFornecedor;
$camposPorEtapa = $camposPorEtapaFornecedor;
$camposObrigatorios = $camposObrigatoriosFornecedor;
$labelsCampos = $labelsCamposFornecedor;

$errosFornecedor = [];

if (isset($_GET['limpar'])) {
    unset($_SESSION[$chaveSessao]);
    header('Location: ' . $ficheiroAtual);
    exit;
}

$etapaAtual = $_GET['etapa'] ?? 'identificacao';
if (!in_array($etapaAtual, $etapasFornecedor, true)) {
    $etapaAtual = 'identificacao';
}

$errosFornecedor = [];

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

            $errosEtapa = validar_etapa_temporaria(
                $chaveSessao,
                $etapaValidar,
                $camposObrigatorios,
                $labelsCampos
            );

            if (!empty($errosEtapa)) {
                $errosFornecedor = $errosEtapa;
                $etapaAtual = $etapaValidar;
                break;
            }
        }

        if (empty($errosFornecedor)) {
            header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaDestino));
            exit;
        }
    }

    if (empty($errosFornecedor)) {
        $errosFornecedor = validar_etapa_temporaria(
            $chaveSessao,
            $etapaSubmetida,
            $camposObrigatorios,
            $labelsCampos
        );

        if (!empty($errosFornecedor)) {
            $etapaAtual = $etapaSubmetida;
        } else {
            $proximaEtapa = proxima_etapa($etapaSubmetida, $etapas);

            if ($proximaEtapa !== null) {
                header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($proximaEtapa));
                exit;
            }
        }
    }

    if (empty($errosFornecedor)) {
        foreach ($camposObrigatorios as $etapa => $campos) {
            $errosEtapa = validar_etapa_temporaria(
                $chaveSessao,
                $etapa,
                $camposObrigatorios,
                $labelsCampos
            );

            if (!empty($errosEtapa)) {
                $errosFornecedor = $errosEtapa;
                $etapaAtual = $etapa;
                break;
            }
        }
    }

    if (empty($errosFornecedor)) {
        $dadosFornecedor = $_SESSION[$chaveSessao] ?? [];

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

        unset($_SESSION[$chaveSessao]);

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

            <button type="submit"
                    class="btn btn-limpar"
                    name="acao_etapa"
                    value="limpar_etapa"
                    form="formNovoFornecedor"
                    formnovalidate>
                <i class="fa-solid fa-eraser me-2"></i> Limpar
            </button>

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
                            <?php echo htmlspecialchars($nomesEtapasFornecedor[$etapa]); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-step-heading">
                <h3>
                    Etapa <?php echo indice_etapa($etapaAtual, $etapas) + 1; ?>
                    de <?php echo count($etapas); ?>:
                    <?php echo htmlspecialchars($nomesEtapasFornecedor[$etapaAtual]); ?>
                </h3>
            </div>

           <?php if (!empty($errosFornecedor)): ?>
                <div class="form-alerta-erros" role="alert">
                    <strong>
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Não é possível avançar para essa etapa.
                    </strong>

                    <p class="mb-2 mt-2">
                        Preencha os campos obrigatórios antes de continuar.
                    </p>

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
                        <button type="submit"
                                class="<?php echo classe_tab('identificacao', $etapaAtual); ?>"
                                id="identificacao-tab"
                                name="etapa_destino"
                                value="identificacao"
                                formnovalidate
                                role="tab"
                                aria-controls="identificacao"
                                aria-selected="<?php echo aria_tab('identificacao', $etapaAtual); ?>">
                            <i class="fa-solid fa-building me-2"></i>
                            Identificação
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab('contactos', $etapaAtual); ?>"
                                id="contactos-tab"
                                name="etapa_destino"
                                value="contactos"
                                formnovalidate
                                role="tab"
                                aria-controls="contactos"
                                aria-selected="<?php echo aria_tab('contactos', $etapaAtual); ?>">
                            <i class="fa-solid fa-address-book me-2"></i>
                            Contactos
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab('morada', $etapaAtual); ?>"
                                id="morada-tab"
                                name="etapa_destino"
                                value="morada"
                                formnovalidate
                                role="tab"
                                aria-controls="morada"
                                aria-selected="<?php echo aria_tab('morada', $etapaAtual); ?>">
                            <i class="fa-solid fa-location-dot me-2"></i>
                            Morada
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab('contrato', $etapaAtual); ?>"
                                id="contrato-tab"
                                name="etapa_destino"
                                value="contrato"
                                formnovalidate
                                role="tab"
                                aria-controls="contrato"
                                aria-selected="<?php echo aria_tab('contrato', $etapaAtual); ?>">
                            <i class="fa-solid fa-file-contract me-2"></i>
                            Serviços
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
                            <i class="fa-solid fa-clipboard-list me-2"></i>
                            Observações
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button type="submit"
                                class="<?php echo classe_tab('documentos', $etapaAtual); ?>"
                                id="documentos-tab"
                                name="etapa_destino"
                                value="documentos"
                                formnovalidate
                                role="tab"
                                aria-controls="documentos"
                                aria-selected="<?php echo aria_tab('documentos', $etapaAtual); ?>">
                            <i class="fa-solid fa-folder-open me-2"></i>
                            Documentos
                        </button>
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
                    <div class="<?php echo classe_painel('identificacao', $etapaAtual); ?>"
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
                                       value="<?php echo valor_temporario($chaveSessao, 'nomeFornecedor'); ?>"
                                       placeholder="Ex: MedSupply Portugal"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="nifFornecedor" class="form-label">NIF *</label>
                                <input type="text"
                                       class="form-control"
                                       id="nifFornecedor"
                                       name="nifFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'nifFornecedor'); ?>"
                                       placeholder="Ex: 514987321"
                                       required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label d-block">Tipo de Fornecedor *</label>

                                <select class="form-select" id="tipoFornecedor" name="tipoFornecedor" required>
                                    <option value="">Selecionar tipo</option>

                                    <option value="Manutenção" <?php echo selected_temporario($chaveSessao, 'tipoFornecedor', 'Manutenção'); ?>>
                                        Manutenção
                                    </option>

                                    <option value="Comercial" <?php echo selected_temporario($chaveSessao, 'tipoFornecedor', 'Comercial'); ?>>
                                        Comercial
                                    </option>

                                    <option value="Fabricante" <?php echo selected_temporario($chaveSessao, 'tipoFornecedor', 'Fabricante'); ?>>
                                        Fabricante
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="estadoFornecedor" class="form-label">Estado *</label>
                                <select class="form-select" id="estadoFornecedor" name="estadoFornecedor" required>
                                    <option value="">Selecionar estado</option>

                                    <option value="Ativo" <?php echo selected_temporario($chaveSessao, 'estadoFornecedor', 'Ativo'); ?>>
                                        Ativo
                                    </option>

                                    <option value="Inativo" <?php echo selected_temporario($chaveSessao, 'estadoFornecedor', 'Inativo'); ?>>
                                        Inativo
                                    </option>

                                    <option value="Em avaliação" <?php echo selected_temporario($chaveSessao, 'estadoFornecedor', 'Em avaliação'); ?>>
                                        Em avaliação
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 2: CONTACTOS
                         Dados de contacto geral e contacto responsável.
                         ========================================= -->
                    <div class="<?php echo classe_painel('contactos', $etapaAtual); ?>"
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
                                       value="<?php echo valor_temporario($chaveSessao, 'emailFornecedor'); ?>"
                                       placeholder="Ex: comercial@fornecedor.pt"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneFornecedor" class="form-label">Telefone *</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneFornecedor"
                                       name="telefoneFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'telefoneFornecedor'); ?>"
                                       placeholder="Ex: +351 221 234 567"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="websiteFornecedor" class="form-label">Website </label>
                                <input type="url"
                                       class="form-control"
                                       id="websiteFornecedor"
                                       name="websiteFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'websiteFornecedor'); ?>"
                                       placeholder="Ex: https://www.fornecedor.pt">
                            </div>

                            <div class="col-md-4">
                                <label for="contactoResponsavel" class="form-label">Pessoa de Contacto</label>
                                <input type="text"
                                       class="form-control"
                                       id="contactoResponsavel"
                                       name="contactoResponsavel"
                                       value="<?php echo valor_temporario($chaveSessao, 'contactoResponsavel'); ?>"
                                       placeholder="Ex: Ana Martins">
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneContacto" class="form-label">Telefone do Contacto</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneContacto"
                                       name="telefoneContacto"
                                       value="<?php echo valor_temporario($chaveSessao, 'telefoneContacto'); ?>"
                                       placeholder="Ex: 912 345 678">
                            </div>

                            <div class="col-md-4">
                                <label for="emailContacto" class="form-label">Email do Contacto</label>
                                <input type="email"
                                       class="form-control"
                                       id="emailContacto"
                                       name="emailContacto"
                                       value="<?php echo valor_temporario($chaveSessao, 'emailContacto'); ?>"
                                       placeholder="Ex: tecnico@fornecedor.pt">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 3: MORADA
                         Morada e localização da entidade.
                         ========================================= -->
                    <div class="<?php echo classe_painel('morada', $etapaAtual); ?>"
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
                                       value="<?php echo valor_temporario($chaveSessao, 'moradaFornecedor'); ?>"
                                       placeholder="Ex: Rua da Tecnologia, nº 120">
                            </div>

                            <div class="col-md-2">
                                <label for="codigoPostalFornecedor" class="form-label">Código Postal</label>
                                <input type="text"
                                       class="form-control"
                                       id="codigoPostalFornecedor"
                                       name="codigoPostalFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'codigoPostalFornecedor'); ?>"
                                       placeholder="Ex: 4000-000">
                            </div>

                            <div class="col-md-2">
                                <label for="localidadeFornecedor" class="form-label">Localidade *</label>
                                <input type="text"
                                       class="form-control"
                                       id="localidadeFornecedor"
                                       name="localidadeFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'localidadeFornecedor'); ?>"
                                       placeholder="Ex: Porto"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label for="paisFornecedor" class="form-label">País</label>
                                <input type="text"
                                       class="form-control"
                                       id="paisFornecedor"
                                       name="paisFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'paisFornecedor', 'Portugal'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 4: SERVIÇOS E CONTRATO
                         Contrato ativo, datas e área de atuação.
                         ========================================= -->
                    <div class="<?php echo classe_painel('contrato', $etapaAtual); ?>"
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
                                <select class="form-select" id="contratoFornecedor" name="contratoFornecedor" required>
                                    <option value="">Selecionar opção</option>

                                    <option value="Sim" <?php echo selected_temporario($chaveSessao, 'contratoFornecedor', 'Sim'); ?>>
                                        Sim
                                    </option>

                                    <option value="Não" <?php echo selected_temporario($chaveSessao, 'contratoFornecedor', 'Não'); ?>>
                                        Não
                                    </option>

                                    <option value="Em análise" <?php echo selected_temporario($chaveSessao, 'contratoFornecedor', 'Em análise'); ?>>
                                        Em análise
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="inicioContratoFornecedor" class="form-label">Início do Contrato</label>
                                <input type="date"
                                       class="form-control"
                                       id="inicioContratoFornecedor"
                                       name="inicioContratoFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'inicioContratoFornecedor'); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="fimContratoFornecedor" class="form-label">Fim do Contrato</label>
                                <input type="date"
                                       class="form-control"
                                       id="fimContratoFornecedor"
                                       name="fimContratoFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'fimContratoFornecedor'); ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="areaAtuacaoFornecedor" class="form-label">Área de Atuação</label>
                                <textarea class="form-control"
                                          id="areaAtuacaoFornecedor"
                                          name="areaAtuacaoFornecedor"
                                          rows="5"
                                          placeholder="Ex: venda de equipamentos médicos, manutenção preventiva, calibração de dispositivos clínicos..."><?php echo valor_temporario($chaveSessao, 'areaAtuacaoFornecedor'); ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="equipamentosAssociadosFornecedor" class="form-label">Equipamentos / Marcas Associadas</label>
                                <textarea class="form-control"
                                          id="equipamentosAssociadosFornecedor"
                                          name="equipamentosAssociadosFornecedor"
                                          rows="5"
                                          placeholder="Ex: monitores multiparamétricos Philips, ventiladores Dräger, desfibrilhadores Zoll..."><?php echo valor_temporario($chaveSessao, 'equipamentosAssociadosFornecedor'); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 5: DOCUMENTOS
                         Permite adicionar contratos, certificados e
                         outros ficheiros associados ao fornecedor.
                         ========================================= -->
                    <div class="<?php echo classe_painel('documentos', $etapaAtual); ?>"
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
                    <div class="<?php echo classe_painel('observacoes', $etapaAtual); ?>"
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
                                  placeholder="Indique informações relevantes sobre o fornecedor, qualidade do serviço, tempos de resposta ou notas técnicas."><?php echo valor_temporario($chaveSessao, 'observacoesFornecedor'); ?></textarea>
                    </div>
                </div>
            </div>
        </form>

    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
