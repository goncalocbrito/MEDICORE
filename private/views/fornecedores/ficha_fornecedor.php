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

$id_fornecedor = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE fornecedores
        SET
            nome_empresa = :nome_empresa,
            tipo_fornecedor = :tipo_fornecedor,
            nif = :nif,
            email = :email,
            telefone = :telefone,
            website = :website,
            pessoa_contacto = :pessoa_contacto,
            telefone_contacto = :telefone_contacto,
            email_contacto = :email_contacto,
            morada = :morada,
            codigo_postal = :codigo_postal,
            localidade = :localidade,
            pais = :pais
        WHERE id_fornecedor = :id_fornecedor
          AND isActive = 1
    ");

    $stmt->execute([
        ':nome_empresa' => trim($_POST['nomeFornecedor'] ?? ''),
        ':tipo_fornecedor' => trim($_POST['tipoFornecedor'] ?? ''),
        ':nif' => trim($_POST['nifFornecedor'] ?? ''),
        ':email' => trim($_POST['emailFornecedor'] ?? ''),
        ':telefone' => trim($_POST['telefoneFornecedor'] ?? ''),
        ':website' => trim($_POST['websiteFornecedor'] ?? ''),
        ':pessoa_contacto' => trim($_POST['contactoResponsavel'] ?? ''),
        ':telefone_contacto' => trim($_POST['telefoneContacto'] ?? ''),
        ':email_contacto' => trim($_POST['emailContacto'] ?? ''),
        ':morada' => trim($_POST['moradaFornecedor'] ?? ''),
        ':codigo_postal' => trim($_POST['codigoPostalFornecedor'] ?? ''),
        ':localidade' => trim($_POST['localidadeFornecedor'] ?? ''),
        ':pais' => trim($_POST['paisFornecedor'] ?? 'Portugal'),
        ':id_fornecedor' => $id_fornecedor
    ]);

    header('Location: ficha_fornecedor.php?id=' . urlencode($id_fornecedor));
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
            <span id="badgeContratoFornecedor">Contrato</span>
        </div>

        <!-- =====================================================
             BARRA DE AÇÕES DA FICHA
             Em modo consulta mostra Voltar + Editar.
             Em modo edição mostra Cancelar + Guardar Alterações.
             ===================================================== -->
        <div class="ficha-toolbar">
            <a href="lista_fornecedores.php" class="btn btn-voltar botao-consulta">
                <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
            </a>

            <button type="button" class="btn btn-editar-ficha botao-consulta" id="btnAtivarEdicaoFornecedor">
                <i class="fa-solid fa-pen me-2"></i> Editar
            </button>

            <button type="button" class="btn btn-cancelar botao-edicao d-none" id="btnCancelarEdicaoFornecedor">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar
            </button>

            <button type="submit" class="btn btn-guardar botao-edicao d-none" form="formFichaFornecedor">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
            </button>
        </div>

        <!-- =====================================================
             FORMULÁRIO ÚNICO DA FICHA DO FORNECEDOR
             Serve para consulta e edição. O JavaScript bloqueia ou
             liberta os campos conforme o modo ativo.
             ===================================================== -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formFichaFornecedor"
              action="ficha_fornecedor.php?id=<?php echo urlencode($fornecedor['id_fornecedor']); ?>"
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

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="contrato-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#contrato"
                                type="button"
                                role="tab"
                                aria-controls="contrato"
                                aria-selected="false">
                            <i class="fa-solid fa-file-contract me-2"></i>
                            Serviços
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="documentos-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#documentos"
                                type="button"
                                role="tab"
                                aria-controls="documentos"
                                aria-selected="false">
                            <i class="fa-solid fa-folder-open me-2"></i>
                            Documentos
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
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="nifFornecedor" class="form-label">NIF *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="nifFornecedor"
                                       name="nifFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['nif']); ?>"
                                       required>
                            </div>

                            <div class="col-md-8">
                                <label for="tipoFornecedor" class="form-label">Tipo de Fornecedor *</label>
                                <select class="form-select campo-ficha campo-editavel"
                                        id="tipoFornecedor"
                                        name="tipoFornecedor"
                                        required>
                                    <option value="">Selecionar tipo</option>
                                    <option value="Manuten��o" <?php echo $fornecedor['tipo_fornecedor'] === 'Manuten��o' ? 'selected' : ''; ?>>Manuten��o</option>
                                    <option value="Comercial" <?php echo $fornecedor['tipo_fornecedor'] === 'Comercial' ? 'selected' : ''; ?>>Comercial</option>
                                    <option value="Fabricante" <?php echo $fornecedor['tipo_fornecedor'] === 'Fabricante' ? 'selected' : ''; ?>>Fabricante</option>
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
                                <label for="emailFornecedor" class="form-label">Email Geral *</label>
                                <input type="email"
                                       class="form-control campo-ficha campo-editavel"
                                       id="emailFornecedor"
                                       name="emailFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['email']); ?>"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneFornecedor" class="form-label">Telefone *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="telefoneFornecedor"
                                       name="telefoneFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['telefone']); ?>"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="websiteFornecedor" class="form-label">Website</label>
                                <input type="url"
                                       class="form-control campo-ficha campo-editavel"
                                       id="websiteFornecedor"
                                       name="websiteFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['website'] ?? ''); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="contactoResponsavel" class="form-label">Pessoa de Contacto</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="contactoResponsavel"
                                       name="contactoResponsavel"
                                       value="<?php echo htmlspecialchars($fornecedor['pessoa_contacto'] ?? ''); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneContacto" class="form-label">Telefone do Contacto</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="telefoneContacto"
                                       name="telefoneContacto"
                                       value="<?php echo htmlspecialchars($fornecedor['telefone_contacto'] ?? ''); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="emailContacto" class="form-label">Email do Contacto</label>
                                <input type="email"
                                       class="form-control campo-ficha campo-editavel"
                                       id="emailContacto"
                                       name="emailContacto"
                                       value="<?php echo htmlspecialchars($fornecedor['email_contacto'] ?? ''); ?>">
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
                                <label for="moradaFornecedor" class="form-label">Morada</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="moradaFornecedor"
                                       name="moradaFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['morada'] ?? ''); ?>">
                            </div>

                            <div class="col-md-2">
                                <label for="codigoPostalFornecedor" class="form-label">Código Postal</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="codigoPostalFornecedor"
                                       name="codigoPostalFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['codigo_postal'] ?? ''); ?>">
                            </div>

                            <div class="col-md-2">
                                <label for="localidadeFornecedor" class="form-label">Localidade *</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="localidadeFornecedor"
                                       name="localidadeFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['localidade'] ?? ''); ?>"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label for="paisFornecedor" class="form-label">País</label>
                                <input type="text"
                                       class="form-control campo-ficha campo-editavel"
                                       id="paisFornecedor"
                                       name="paisFornecedor"
                                       value="<?php echo htmlspecialchars($fornecedor['pais'] ?? 'Portugal'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 4: SERVIÇOS E CONTRATO
                         Relação contratual, área de atuação e marcas.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="contrato"
                         role="tabpanel"
                         aria-labelledby="contrato-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Serviços, Contrato e Associação Técnica</h4>
                            <p>Âmbito de atuação, contrato ativo e equipamentos ou marcas associados.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="contratoFornecedor" class="form-label">Contrato Ativo?</label>
                                <select class="form-select campo-ficha campo-editavel"
                                        id="contratoFornecedor"
                                        name="contratoFornecedor">
                                    <option value="">Selecionar opção</option>
                                    <option value="Sim" selected>Sim</option>
                                    <option value="Não">Não</option>
                                    <option value="Em análise">Em análise</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="inicioContratoFornecedor" class="form-label">Início do Contrato</label>
                                <input type="date"
                                       class="form-control campo-ficha campo-editavel"
                                       id="inicioContratoFornecedor"
                                       name="inicioContratoFornecedor"
                                       value="2024-01-01">
                            </div>

                            <div class="col-md-4">
                                <label for="fimContratoFornecedor" class="form-label">Fim do Contrato</label>
                                <input type="date"
                                       class="form-control campo-ficha campo-editavel"
                                       id="fimContratoFornecedor"
                                       name="fimContratoFornecedor"
                                       value="2027-01-01">
                            </div>

                            <div class="col-md-6">
                                <label for="areaAtuacaoFornecedor" class="form-label">Área de Atuação</label>
                                <textarea class="form-control campo-ficha campo-editavel"
                                          id="areaAtuacaoFornecedor"
                                          name="areaAtuacaoFornecedor"
                                          rows="5">Fabrico e suporte técnico de equipamentos de monitorização clínica.</textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="equipamentosAssociadosFornecedor" class="form-label">Equipamentos / Marcas Associadas</label>
                                <textarea class="form-control campo-ficha campo-editavel"
                                          id="equipamentosAssociadosFornecedor"
                                          name="equipamentosAssociadosFornecedor"
                                          rows="5">Monitores multiparamétricos Philips IntelliVue.</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 5: DOCUMENTOS
                         Lista documentos existentes e permite adicionar
                         novos ficheiros quando a ficha está em edição.
                         ========================================= -->
                    <div class="tab-pane fade"
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
                                    class="btn btn-adicionar-documento botao-edicao d-none"
                                    id="btnAdicionarDocumento">
                                <i class="fa-solid fa-plus me-2"></i> Adicionar Documento
                            </button>
                        </div>

                        <div class="documentos-lista mb-4">
                            <div class="documento-item">
                                <div class="documento-info">
                                    <i class="fa-solid fa-file-contract documento-icone"></i>
                                    <div>
                                        <h5>Contrato de Fornecimento</h5>
                                        <p>Contrato ativo com condições comerciais e técnicas do fornecedor.</p>
                                    </div>
                                </div>

                                <div class="documento-acoes">
                                    <a href="#" class="btn-documento-ver">
                                        <i class="fa-solid fa-eye me-1"></i> Ver
                                    </a>
                                    <a href="#" class="btn-documento-download">
                                        <i class="fa-solid fa-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>

                            <div class="documento-item">
                                <div class="documento-info">
                                    <i class="fa-solid fa-file-shield documento-icone"></i>
                                    <div>
                                        <h5>Certificado Técnico</h5>
                                        <p>Certificado de conformidade ou qualificação técnica do fornecedor.</p>
                                    </div>
                                </div>

                                <div class="documento-acoes">
                                    <a href="#" class="btn-documento-ver">
                                        <i class="fa-solid fa-eye me-1"></i> Ver
                                    </a>
                                    <a href="#" class="btn-documento-download">
                                        <i class="fa-solid fa-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div id="listaDocumentosNovos">
                            <div class="documento-form-item botao-edicao d-none">
                                <div class="row g-4 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo de Documento</label>
                                        <select class="form-select campo-ficha campo-editavel" name="tipoDocumento[]">
                                            <option value="">Selecionar tipo</option>
                                            <option value="contrato">Contrato</option>
                                            <option value="certificado">Certificado técnico</option>
                                            <option value="catalogo">Catálogo</option>
                                            <option value="comprovativo">Comprovativo fiscal</option>
                                            <option value="relatorio">Relatório técnico</option>
                                            <option value="outro">Outro</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Nome do Documento</label>
                                        <input type="text"
                                               class="form-control campo-ficha campo-editavel"
                                               name="nomeDocumento[]"
                                               placeholder="Ex: Contrato de manutenção 2026">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Ficheiro</label>
                                        <input type="file"
                                               class="form-control campo-ficha campo-editavel"
                                               name="ficheiroDocumento[]"
                                               accept=".pdf,.png,.jpg,.jpeg">
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
                    <div class="tab-pane fade"
                         id="observacoes-tab-pane"
                         role="tabpanel"
                         aria-labelledby="observacoes-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Observações</h4>
                            <p>Notas sobre qualidade do serviço, tempos de resposta, histórico ou acompanhamento técnico.</p>
                        </div>

                        <textarea class="form-control campo-ficha campo-editavel"
                                  id="observacoesFornecedor"
                                  name="observacoesFornecedor"
                                  rows="7">Fornecedor associado a equipamentos de monitorização em unidades críticas.</textarea>
                    </div>
                </div>
            </div>
        </form>

    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
