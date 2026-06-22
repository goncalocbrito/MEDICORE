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
    'morada'
];

$camposPorEtapaFornecedor = [
    'identificacao' => ['nomeFornecedor', 'nifFornecedor', 'tipoFornecedor', 'estadoFornecedor'],
    'contactos' => ['emailFornecedor', 'telefoneFornecedor', 'emailEmpresaFornecedor', 'contactoResponsavel', 'telefoneContacto', 'emailContacto'],
    'morada' => ['moradaFornecedor', 'codigoPostalFornecedor', 'localidadeFornecedor', 'paisFornecedor'],
];

$camposObrigatoriosFornecedor = [
    'identificacao' => [
        'nomeFornecedor',
        'nifFornecedor',
        'tipoFornecedor',
        'estadoFornecedor'
    ],

    'contactos' => [
        'telefoneFornecedor',
        'contactoResponsavel',
        'emailContacto'
    ],

    'morada' => [
        'moradaFornecedor',
        'codigoPostalFornecedor',
        'localidadeFornecedor',
        'paisFornecedor'
    ],
];

$labelsCamposFornecedor = [
    'nomeFornecedor'          => 'Nome do Fornecedor',
    'nifFornecedor'           => 'NIF',
    'tipoFornecedor'          => 'Tipo de Fornecedor',
    'estadoFornecedor'        => 'Estado',

    'telefoneFornecedor'      => 'Telefone',
    'emailEmpresaFornecedor'  => 'Email do Fornecedor',
    'contactoResponsavel'     => 'Pessoa Responsável',
    'telefoneContacto'        => 'Telefone do Contacto',
    'emailContacto'           => 'Email do Contacto',

    'moradaFornecedor'        => 'Morada',
    'codigoPostalFornecedor'  => 'Código Postal',
    'localidadeFornecedor'    => 'Localidade',
    'paisFornecedor'          => 'País',
];

$nomesEtapasFornecedor = [
    'identificacao' => 'Identificação',
    'contactos'     => 'Contactos',
    'morada'        => 'Morada',
];

$chaveSessao = 'novo_fornecedor';
$ficheiroAtual = 'novo_fornecedor.php';

function validar_formato_identificacao_fornecedor($chaveSessao)
{
    $dados = $_SESSION[$chaveSessao] ?? [];
    $erros = [];

    $nif = $dados['nifFornecedor'] ?? '';
    if (!preg_match('/^\d{9}$/', $nif)) {
        $erros[] = 'O NIF deve ter exatamente 9 dígitos numéricos.';
    }

    return $erros;
}

function validar_formato_contactos_fornecedor($chaveSessao)
{
    $dados = $_SESSION[$chaveSessao] ?? [];
    $erros = [];

    $tel = preg_replace('/\D/', '', $dados['telefoneFornecedor'] ?? '');
    if (strlen($tel) !== 9) {
        $erros[] = 'O Telefone deve ter exatamente 9 dígitos.';
    }

    $telContacto = $dados['telefoneContacto'] ?? '';
    if ($telContacto !== '') {
        if (strlen(preg_replace('/\D/', '', $telContacto)) !== 9) {
            $erros[] = 'O Telefone do Contacto deve ter exatamente 9 dígitos.';
        }
    }

    foreach (['emailContacto' => 'Email do Contacto'] as $campo => $label) {
        $val = $dados[$campo] ?? '';
        if ($val !== '' && !str_contains($val, '@')) {
            $erros[] = 'O campo "' . $label . '" deve conter o carácter @.';
        }
    }

    $emailEmpresa = $dados['emailEmpresaFornecedor'] ?? '';
    if ($emailEmpresa !== '' && !str_contains($emailEmpresa, '@')) {
        $erros[] = 'O campo "Email do Fornecedor" deve conter o carácter @.';
    }

    return $erros;
}

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

            if (empty($errosEtapa) && $etapaValidar === 'identificacao') {
                $errosEtapa = validar_formato_identificacao_fornecedor($chaveSessao);
            }

            if (empty($errosEtapa) && $etapaValidar === 'contactos') {
                $errosEtapa = validar_formato_contactos_fornecedor($chaveSessao);
            }

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

        if (empty($errosFornecedor) && $etapaSubmetida === 'identificacao') {
            $errosFornecedor = validar_formato_identificacao_fornecedor($chaveSessao);
        }

        if (empty($errosFornecedor) && $etapaSubmetida === 'contactos') {
            $errosFornecedor = validar_formato_contactos_fornecedor($chaveSessao);
        }

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
        foreach ($etapas as $etapa) {
            $errosEtapa = validar_etapa_temporaria(
                $chaveSessao,
                $etapa,
                $camposObrigatorios,
                $labelsCampos
            );

            if (empty($errosEtapa) && $etapa === 'identificacao') {
                $errosEtapa = validar_formato_identificacao_fornecedor($chaveSessao);
            }

            if (empty($errosEtapa) && $etapa === 'contactos') {
                $errosEtapa = validar_formato_contactos_fornecedor($chaveSessao);
            }

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
                telefone,
                email_fornecedor,
                pessoa_responsavel,
                telefone_contacto,
                email_contacto,
                morada,
                codigo_postal,
                localidade,
                pais,
                isActive
            ) VALUES (
                :nome_empresa,
                :tipo_fornecedor,
                :nif,
                :telefone,
                :email_fornecedor,
                :pessoa_responsavel,
                :telefone_contacto,
                :email_contacto,
                :morada,
                :codigo_postal,
                :localidade,
                :pais,
                1
            )
        ");

        $stmt->execute([
            ':nome_empresa'      => $dadosFornecedor['nomeFornecedor'] ?? '',
            ':tipo_fornecedor'   => $dadosFornecedor['tipoFornecedor'] ?? '',
            ':nif'               => $dadosFornecedor['nifFornecedor'] ?? '',
            ':telefone'          => $dadosFornecedor['telefoneFornecedor'] ?? '',
            ':email_fornecedor'  => $dadosFornecedor['emailEmpresaFornecedor'] ?? '',
            ':pessoa_responsavel' => $dadosFornecedor['contactoResponsavel'] ?? '',
            ':telefone_contacto' => $dadosFornecedor['telefoneContacto'] ?? '',
            ':email_contacto'    => $dadosFornecedor['emailContacto'] ?? '',
            ':morada'            => $dadosFornecedor['moradaFornecedor'] ?? '',
            ':codigo_postal'     => $dadosFornecedor['codigoPostalFornecedor'] ?? '',
            ':localidade'        => $dadosFornecedor['localidadeFornecedor'] ?? '',
            ':pais'              => $dadosFornecedor['paisFornecedor'] ?? 'Portugal',
        ]);

        $id_fornecedor = $pdo->lastInsertId();

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
                    value="<?php echo $etapaAtual === 'morada' ? 'finalizar' : 'continuar'; ?>"
                    form="formNovoFornecedor"
                    formnovalidate>
                <i class="fa-solid <?php echo $etapaAtual === 'morada' ? 'fa-floppy-disk' : 'fa-arrow-right'; ?> me-2"></i>
                <?php echo $etapaAtual === 'morada' ? 'Guardar Fornecedor' : 'Guardar e Continuar'; ?>
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
                                       maxlength="9"
                                       pattern="\d{9}"
                                       inputmode="numeric"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="nifFornecedor" data-max="9">0 / 9 caracteres</small>
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
                                <label for="telefoneFornecedor" class="form-label">Telefone *</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneFornecedor"
                                       name="telefoneFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'telefoneFornecedor'); ?>"
                                       placeholder="Ex: 221234567"
                                       maxlength="9"
                                       pattern="\d{9}"
                                       inputmode="numeric"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="telefoneFornecedor" data-max="9">0 / 9 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="emailEmpresaFornecedor" class="form-label">Email do Fornecedor</label>
                                <input type="email"
                                       class="form-control"
                                       id="emailEmpresaFornecedor"
                                       name="emailEmpresaFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'emailEmpresaFornecedor'); ?>"
                                       placeholder="Ex: geral@fornecedor.pt"
                                       maxlength="255">
                                <small class="texto-ajuda-form contador-caracteres" data-target="emailEmpresaFornecedor" data-max="255">0 / 255 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="contactoResponsavel" class="form-label">Pessoa Responsável *</label>
                                <input type="text"
                                       class="form-control"
                                       id="contactoResponsavel"
                                       name="contactoResponsavel"
                                       value="<?php echo valor_temporario($chaveSessao, 'contactoResponsavel'); ?>"
                                       placeholder="Ex: Ana Martins"
                                       maxlength="150"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="contactoResponsavel" data-max="150">0 / 150 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneContacto" class="form-label">Telefone do Contacto</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneContacto"
                                       name="telefoneContacto"
                                       value="<?php echo valor_temporario($chaveSessao, 'telefoneContacto'); ?>"
                                       placeholder="Ex: 912345678"
                                       maxlength="9"
                                       pattern="\d{9}"
                                       inputmode="numeric">
                                <small class="texto-ajuda-form contador-caracteres" data-target="telefoneContacto" data-max="9">0 / 9 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="emailContacto" class="form-label">Email do Contacto *</label>
                                <input type="email"
                                       class="form-control"
                                       id="emailContacto"
                                       name="emailContacto"
                                       value="<?php echo valor_temporario($chaveSessao, 'emailContacto'); ?>"
                                       placeholder="Ex: tecnico@fornecedor.pt"
                                       maxlength="255"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="emailContacto" data-max="255">0 / 255 caracteres</small>
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
                                <label for="moradaFornecedor" class="form-label">Morada *</label>
                                <input type="text"
                                       class="form-control"
                                       id="moradaFornecedor"
                                       name="moradaFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'moradaFornecedor'); ?>"
                                       placeholder="Ex: Rua da Tecnologia, nº 120"
                                       maxlength="255"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="moradaFornecedor" data-max="255">0 / 255 caracteres</small>
                            </div>

                            <div class="col-md-2">
                                <label for="codigoPostalFornecedor" class="form-label">Código Postal *</label>
                                <input type="text"
                                       class="form-control"
                                       id="codigoPostalFornecedor"
                                       name="codigoPostalFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'codigoPostalFornecedor'); ?>"
                                       placeholder="Ex: 4000-000"
                                       maxlength="20"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="codigoPostalFornecedor" data-max="20">0 / 20 caracteres</small>
                            </div>

                            <div class="col-md-2">
                                <label for="localidadeFornecedor" class="form-label">Localidade *</label>
                                <input type="text"
                                       class="form-control"
                                       id="localidadeFornecedor"
                                       name="localidadeFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'localidadeFornecedor'); ?>"
                                       placeholder="Ex: Porto"
                                       maxlength="100"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="localidadeFornecedor" data-max="100">0 / 100 caracteres</small>
                            </div>

                            <div class="col-md-2">
                                <label for="paisFornecedor" class="form-label">País *</label>
                                <input type="text"
                                       class="form-control"
                                       id="paisFornecedor"
                                       name="paisFornecedor"
                                       value="<?php echo valor_temporario($chaveSessao, 'paisFornecedor', 'Portugal'); ?>"
                                       maxlength="100"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="paisFornecedor" data-max="100">0 / 100 caracteres</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
