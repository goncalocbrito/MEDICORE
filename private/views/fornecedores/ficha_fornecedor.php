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

$id_fornecedor = id_from_request();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $camposObrigatorios = [
        'nomeFornecedor'         => 'Nome do Fornecedor',
        'nifFornecedor'          => 'NIF',
        'tipoFornecedor'         => 'Tipo de Fornecedor',
        'telefoneFornecedor'     => 'Telefone do Fornecedor',
        'emailEmpresaFornecedor' => 'Email do Fornecedor',
        'contactoResponsavel'    => 'Pessoa Responsável',
        'telefoneContacto'       => 'Telefone do Contacto',
        'emailContacto'          => 'Email do Contacto',
        'moradaFornecedor'       => 'Morada',
        'codigoPostalFornecedor' => 'Código Postal',
        'localidadeFornecedor'   => 'Localidade',
        'paisFornecedor'         => 'País',
    ];

    $erros = [];
    foreach ($camposObrigatorios as $campo => $label) {
        if (trim($_POST[$campo] ?? '') === '') {
            $erros[] = 'O campo "' . $label . '" é obrigatório.';
        }
    }

    $nif = trim($_POST['nifFornecedor'] ?? '');
    if ($nif !== '' && !preg_match('/^\d{9}$/', $nif)) {
        $erros[] = 'O NIF deve ter exatamente 9 dígitos.';
    }
    $tel = trim($_POST['telefoneFornecedor'] ?? '');
    if ($tel !== '' && !preg_match('/^\d{9}$/', $tel)) {
        $erros[] = 'O Telefone do Fornecedor deve ter exatamente 9 dígitos.';
    }
    $telC = trim($_POST['telefoneContacto'] ?? '');
    if ($telC !== '' && !preg_match('/^\d{9}$/', $telC)) {
        $erros[] = 'O Telefone do Contacto deve ter exatamente 9 dígitos.';
    }
    $emailF = trim($_POST['emailEmpresaFornecedor'] ?? '');
    if ($emailF !== '' && !filter_var($emailF, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email do Fornecedor inválido.';
    }
    $emailC = trim($_POST['emailContacto'] ?? '');
    if ($emailC !== '' && !filter_var($emailC, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email do Contacto inválido.';
    }

    $nifVal = trim($_POST['nifFornecedor'] ?? '');
    if ($nifVal !== '') {
        $stmtNif = $pdo->prepare("SELECT COUNT(*) FROM fornecedores WHERE nif = :nif AND isActive = 1 AND id_fornecedor != :id");
        $stmtNif->execute([':nif' => $nifVal, ':id' => $id_fornecedor]);
        if ((int) $stmtNif->fetchColumn() > 0) {
            $erros[] = 'Já existe um fornecedor registado com o NIF "' . htmlspecialchars($nifVal) . '". O NIF deve ser único.';
        }
    }

    if (!empty($erros)) {
        // Recarrega a página com erros na sessão
        $_SESSION['erros_ficha_fornecedor'] = $erros;
        header('Location: ficha_fornecedor.php?ref=' . url_ref($id_fornecedor));
        exit;
    }
    $stmt = $pdo->prepare("
        UPDATE fornecedores
        SET
            nome_empresa = :nome_empresa,
            tipo_fornecedor = :tipo_fornecedor,
            nif = :nif,
            telefone = :telefone,
            email_fornecedor = :email_fornecedor,
            pessoa_responsavel = :pessoa_responsavel,
            telefone_contacto = :telefone_contacto,
            email_contacto = :email_contacto,
            morada = :morada,
            codigo_postal = :codigo_postal,
            localidade = :localidade,
            pais = :pais,
            observacoes = :observacoes
        WHERE id_fornecedor = :id_fornecedor
          AND isActive = 1
    ");

    $stmt->execute([
        ':nome_empresa'       => trim($_POST['nomeFornecedor'] ?? ''),
        ':tipo_fornecedor'    => trim($_POST['tipoFornecedor'] ?? ''),
        ':nif'                => trim($_POST['nifFornecedor'] ?? ''),
        ':telefone'           => trim($_POST['telefoneFornecedor'] ?? ''),
        ':email_fornecedor'   => trim($_POST['emailEmpresaFornecedor'] ?? ''),
        ':pessoa_responsavel' => trim($_POST['contactoResponsavel'] ?? ''),
        ':telefone_contacto'  => trim($_POST['telefoneContacto'] ?? ''),
        ':email_contacto'     => trim($_POST['emailContacto'] ?? ''),
        ':morada'             => trim($_POST['moradaFornecedor'] ?? ''),
        ':codigo_postal'      => trim($_POST['codigoPostalFornecedor'] ?? ''),
        ':localidade'         => trim($_POST['localidadeFornecedor'] ?? ''),
        ':pais'               => trim($_POST['paisFornecedor'] ?? 'Portugal'),
        ':observacoes'        => trim($_POST['observacoesFornecedor'] ?? ''),
        ':id_fornecedor'      => $id_fornecedor
    ]);

    header('Location: ficha_fornecedor.php?ref=' . url_ref($id_fornecedor) . '&guardado=1');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM fornecedores
    WHERE id_fornecedor = :id_fornecedor
      AND isActive = 1
");

$stmt->execute([
    ':id_fornecedor' => $id_fornecedor
]);

$fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fornecedor) {
    header('Location: lista_fornecedores.php');
    exit;
}

$stmtDocs = $pdo->prepare("
    SELECT
        d.id_documento_equipamento,
        d.tipo_documento,
        d.nome_documento,
        d.caminho_ficheiro,
        d.data_documento,
        d.data_validade,
        e.codigo_equipamento,
        e.designacao AS equipamento
    FROM documentos_equipamentos d
    INNER JOIN equipamentos_fornecedores ef
        ON ef.id_equipamento_fornecedor = d.id_equipamento_fornecedor
    INNER JOIN equipamentos e
        ON e.id_equipamento = d.id_equipamento
    WHERE ef.id_fornecedor_garantia = :id_fornecedor
      AND d.isActive = 1
      AND d.tipo_documento IN ('contrato', 'garantia')
    ORDER BY d.data_documento DESC, d.id_documento_equipamento DESC
");

$stmtDocs->execute([
    ':id_fornecedor' => $id_fornecedor
]);

$documentosFornecedor = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>


    <!-- =========================================================
         CONTEÚDO PRINCIPAL DA FICHA DO FORNECEDOR
         Usa as mesmas classes visuais da ficha do equipamento para
         manter largura, separadores, botões e comportamento consistentes.
         ========================================================= -->
    <main class="conteudo-private ficha-equipamento-page ficha-fornecedor-page">

        <!-- =====================================================
             ELEMENTOS OCULTOS PARA COMPATIBILIDADE COM O JS
             Estes campos guardam textos de resumo/badges que podem
             ser atualizados dinamicamente sem aparecerem no ecrã.
             ===================================================== -->
        <div class="d-none" aria-hidden="true">
            <h2 id="tituloPaginaFornecedor">Ficha do Fornecedor</h2>
            <span id="resumoNomeFornecedor">Fornecedor</span>
            <span id="resumoDescricaoFornecedor">NIF | Localidade | Contacto</span>
            <span id="badgeEstadoFornecedor">Estado</span>
            <span id="badgeTiposFornecedor">Tipo</span>
        </div>

        <!-- =====================================================
             BARRA DE AÇÕES DA FICHA
             ===================================================== -->
        <div class="ficha-toolbar">
            <a href="lista_fornecedores.php" class="btn btn-voltar btn-voltar-lista-com-confirmacao">
                <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
            </a>

            <button type="submit" class="btn btn-guardar" form="formFichaFornecedor">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
            </button>
        </div>

        <?php if (!empty($_SESSION['erros_ficha_fornecedor'])): ?>
            <div class="alert alert-danger" role="alert">
                <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Erro</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($_SESSION['erros_ficha_fornecedor'] as $erro): ?>
                        <li><?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['erros_ficha_fornecedor']); ?>
        <?php endif; ?>

        <?php if (isset($_GET['criado'])): ?>
            <div class="form-alerta-sucesso" role="alert">
                <strong>
                    <i class="fa-solid fa-circle-check me-2"></i>
                    Fornecedor criado com sucesso.
                </strong>
            </div>
        <?php elseif (isset($_GET['guardado'])): ?>
            <div class="form-alerta-sucesso" role="alert">
                <strong>
                    <i class="fa-solid fa-circle-check me-2"></i>
                    Alteração do fornecedor guardada.
                </strong>
            </div>
        <?php endif; ?>

        <!-- =====================================================
             FORMULÁRIO ÚNICO DA FICHA DO FORNECEDOR
             Serve para consulta e edição. O JavaScript bloqueia ou
             liberta os campos conforme o modo ativo.
             ===================================================== -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formFichaFornecedor"
              action="ficha_fornecedor.php?ref=<?php echo url_ref($fornecedor['id_fornecedor']); ?>"
              method="post"
              enctype="multipart/form-data">

            <input type="hidden" id="idFornecedor" name="idFornecedor" value="<?php echo htmlspecialchars($fornecedor['id_fornecedor']); ?>">
            
            <!-- =================================================
                 ÁREA PRINCIPAL DA FICHA
                 Caixa que contém os separadores Bootstrap e o conteúdo.
                 ================================================= -->
            <div class="ficha-area">
                <!-- =============================================
                     SEPARADORES DA FICHA
                     Organizam a ficha numa única página.
                     ============================================= -->
                <ul class="nav nav-tabs ficha-tabs" id="tabsFichaFornecedor" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                                id="identificacao-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#identificacao"
                                type="button"
                                role="tab"
                                aria-controls="identificacao"
                                aria-selected="true">
                            <i class="fa-solid fa-building me-2"></i>
                            Identificação
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="contactos-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#contactos"
                                type="button"
                                role="tab"
                                aria-controls="contactos"
                                aria-selected="false">
                            <i class="fa-solid fa-address-book me-2"></i>
                            Contactos
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="morada-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#morada"
                                type="button"
                                role="tab"
                                aria-controls="morada"
                                aria-selected="false">
                            <i class="fa-solid fa-location-dot me-2"></i>
                            Morada
                        </button>
                    </li>

                </ul>

                <!-- =============================================
                     CONTEÚDO DOS SEPARADORES
                     Cada tab-pane corresponde a uma secção do fornecedor.
                     ============================================= -->
                <div class="tab-content ficha-tab-content" id="tabsFichaFornecedorContent">
                    <!-- =========================================
                         SEPARADOR 1: IDENTIFICAÇÃO
                         Dados principais da entidade fornecedora.
                         ========================================= -->
                    <div class="tab-pane fade show active"
                         id="identificacao"
                         role="tabpanel"
                         aria-labelledby="identificacao-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Identificacao do Fornecedor</h4>
                            <p>Dados principais da entidade fornecedora e respetiva classificacao.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-8">
                                <label for="nomeFornecedor" class="form-label">Nome do Fornecedor *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="nomeFornecedor"
                                       name="nomeFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['nome_empresa']); ?>"
                                       maxlength="255"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="nomeFornecedor" data-max="255">0 / 255 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="nifFornecedor" class="form-label">NIF *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="nifFornecedor"
                                       name="nifFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['nif']); ?>"
                                       maxlength="9"
                                       inputmode="numeric"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="nifFornecedor" data-max="9">0 / 9 caracteres</small>
                            </div>

                            <div class="col-md-8">
                                <label for="tipoFornecedor" class="form-label">Tipo de Fornecedor *</label>
                                <select class="form-select campo-ficha campo-editavel"
                                        id="tipoFornecedor"
                                        name="tipoFornecedor"
                                        required>
                                    <option value="">Selecionar tipo</option>
                                    <option value="Manutenção" <?php echo $fornecedor['tipo_fornecedor'] === 'Manutenção' ? 'selected' : ''; ?>>Manutenção</option>
                                    <option value="Comercial" <?php echo $fornecedor['tipo_fornecedor'] === 'Comercial' ? 'selected' : ''; ?>>Comercial</option>
                                    <option value="Fabricante" <?php echo $fornecedor['tipo_fornecedor'] === 'Fabricante' ? 'selected' : ''; ?>>Fabricante</option>
                                    <option value="Calibração" <?php echo $fornecedor['tipo_fornecedor'] === 'Calibração' ? 'selected' : ''; ?>>Calibração</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 2: CONTACTOS
                         Contactos gerais e pessoa responsável.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="contactos"
                         role="tabpanel"
                         aria-labelledby="contactos-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Contactos do Fornecedor</h4>
                            <p>Contactos gerais e pessoa responsável para acompanhamento técnico ou comercial.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="telefoneFornecedor" class="form-label">Telefone do Fornecedor *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="telefoneFornecedor"
                                       name="telefoneFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['telefone']); ?>"
                                       placeholder="Ex: 221234567"
                                       maxlength="9"
                                       inputmode="numeric"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="telefoneFornecedor" data-max="9">0 / 9 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="emailEmpresaFornecedor" class="form-label">Email do Fornecedor *</label>
                                <input type="email"
                                       class="form-control campo-ficha campo-editavel"
                                       id="emailEmpresaFornecedor"
                                       name="emailEmpresaFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['email_fornecedor'] ?? ''); ?>"
                                       placeholder="Ex: geral@fornecedor.pt"
                                       maxlength="255"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="emailEmpresaFornecedor" data-max="255">0 / 255 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="contactoResponsavel" class="form-label">Pessoa Responsável *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="contactoResponsavel"
                                       name="contactoResponsavel"
                                       value="<?php echo htmlspecialchars($fornecedor['pessoa_responsavel'] ?? ''); ?>"
                                       placeholder="Ex: Ana Martins"
                                       maxlength="150"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="contactoResponsavel" data-max="150">0 / 150 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneContacto" class="form-label">Telefone do Contacto *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="telefoneContacto"
                                       name="telefoneContacto"
                                       value="<?php echo htmlspecialchars($fornecedor['telefone_contacto'] ?? ''); ?>"
                                       placeholder="Ex: 912345678"
                                       maxlength="9"
                                       inputmode="numeric"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="telefoneContacto" data-max="9">0 / 9 caracteres</small>
                            </div>

                            <div class="col-md-4">
                                <label for="emailContacto" class="form-label">Email do Contacto *</label>
                                <input type="email"
                                       class="form-control campo-ficha campo-editavel"
                                       id="emailContacto"
                                       name="emailContacto"
                                       value="<?php echo htmlspecialchars($fornecedor['email_contacto'] ?? ''); ?>"
                                       placeholder="Ex: tecnico@fornecedor.pt"
                                       maxlength="255"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="emailContacto" data-max="255">0 / 255 caracteres</small>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 3: MORADA
                         Informação postal e localização do fornecedor.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="morada"
                         role="tabpanel"
                         aria-labelledby="morada-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Morada e Localização</h4>
                            <p>Informação postal da entidade fornecedora.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="moradaFornecedor" class="form-label">Morada *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="moradaFornecedor"
                                       name="moradaFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['morada'] ?? ''); ?>"
                                       placeholder="Ex: Rua da Tecnologia, nº 120"
                                       maxlength="255"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="moradaFornecedor" data-max="255">0 / 255 caracteres</small>
                            </div>

                            <div class="col-md-2">
                                <label for="codigoPostalFornecedor" class="form-label">Código Postal *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="codigoPostalFornecedor"
                                       name="codigoPostalFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['codigo_postal'] ?? ''); ?>"
                                       placeholder="Ex: 4000-000"
                                       maxlength="20"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="codigoPostalFornecedor" data-max="20">0 / 20 caracteres</small>
                            </div>

                            <div class="col-md-2">
                                <label for="localidadeFornecedor" class="form-label">Localidade *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="localidadeFornecedor"
                                       name="localidadeFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['localidade'] ?? ''); ?>"
                                       placeholder="Ex: Porto"
                                       maxlength="100"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="localidadeFornecedor" data-max="100">0 / 100 caracteres</small>
                            </div>

                            <div class="col-md-2">
                                <label for="paisFornecedor" class="form-label">País *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="paisFornecedor"
                                       name="paisFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['pais'] ?? 'Portugal'); ?>"
                                       maxlength="100"
                                       required>
                                <small class="texto-ajuda-form contador-caracteres" data-target="paisFornecedor" data-max="100">0 / 100 caracteres</small>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 5: DOCUMENTOS
                         Lista documentos existentes e permite adicionar
                         novos ficheiros quando a ficha está em edição.
                         ========================================= -->
                    <div class="tab-pane fade d-none"
                         id="documentos"
                         role="tabpanel"
                         aria-labelledby="documentos-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h4>Documentos do Fornecedor</h4>
                                <p>Contratos, certificados, catálogos, comprovativos e documentação técnica associada.</p>
                            </div>

                            <button type="button"
                                    class="btn btn-adicionar-documento"
                                    id="btnAdicionarDocumento">
                                <i class="fa-solid fa-plus me-2"></i> Adicionar Documento
                            </button>
                        </div>

                        <div class="documentos-lista mb-4">
                            <?php if (empty($documentosFornecedor)): ?>
                                <div class="documento-item">
                                    <div class="documento-info">
                                        <i class="fa-solid fa-file-circle-plus documento-icone"></i>
                                        <div>
                                            <h5>Sem documentos associados</h5>
                                            <p>Este fornecedor ainda nao tem contratos, certificados ou comprovativos registados.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($documentosFornecedor as $documento): ?>
                                    <div class="documento-item">
                                        <div class="documento-info">
                                            <i class="fa-solid fa-file-contract documento-icone"></i>
                                            <div>
                                                <h5><?php echo htmlspecialchars($documento['nome_documento']); ?></h5>
                                                <p>
                                                    <?php echo htmlspecialchars($documento['tipo_documento']); ?>
                                                    | Numero: <?php echo htmlspecialchars($documento['numero_documento']); ?>
                                                    <?php if (!empty($documento['data_validade'])): ?>
                                                        | Validade: <?php echo htmlspecialchars($documento['data_validade']); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="documento-acoes">
                                            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($documento['caminho_ficheiro']); ?>" class="btn-documento-ver" target="_blank">
                                                <i class="fa-solid fa-eye me-1"></i> Ver
                                            </a>
                                            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($documento['caminho_ficheiro']); ?>" class="btn-documento-download" download>
                                                <i class="fa-solid fa-download me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div id="listaDocumentosNovos">
                            <div class="documento-form-item d-none">
                                <div class="row g-4 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Tipo de Documento</label>
                                        <select class="form-select campo-ficha campo-editavel" name="tipoDocumento[]">
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
                                               class="form-control campo-ficha campo-editavel"
                                               name="numeroDocumento[]"
                                               maxlength="30"
                                               placeholder="Ex: DOC-2026-001">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Nome do Documento</label>
                                        <input type="text"
                                               class="form-control campo-ficha campo-editavel"
                                               name="nomeDocumento[]"
                                               placeholder="Ex: Contrato de Manutenção 2026">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Ficheiro</label>
                                        <input type="file"
                                               class="form-control campo-ficha campo-editavel"
                                               name="ficheiroDocumento[]"
                                               accept=".pdf,.png,.jpg,.jpeg">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Data do Documento</label>
                                        <input type="date"
                                               class="form-control campo-ficha campo-editavel"
                                               name="dataDocumento[]">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Data de Validade</label>
                                        <input type="date"
                                               class="form-control campo-ficha campo-editavel"
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
                         Campo livre para notas técnicas ou administrativas.
                         ========================================= -->
                </div>
            </div>
        </form>

    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
