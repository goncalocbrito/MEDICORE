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
   PROCESSAMENTO DO NOVO FORNECEDOR
   Se a página receber POST, insere o fornecedor na base de dados.
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        ':observacoes' => trim($_POST['observacoesFornecedor'] ?? '')
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

    header('Location: lista_fornecedores.php');
    exit;
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
             Cancelar volta à lista, Limpar repõe os campos e Guardar
             dispara o pop-up visual de sucesso via JavaScript.
             ===================================================== -->
        <div class="form-actions">
            <a href="lista_fornecedores.php" class="btn btn-cancelar">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar
            </a>

            <button type="button" class="btn btn-limpar" id="btnLimparNovoFornecedor">
                <i class="fa-solid fa-eraser me-2"></i> Limpar
            </button>

            <button type="submit" class="btn btn-guardar" form="formNovoFornecedor">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Fornecedor
            </button>
        </div>

        <!-- =====================================================
             FORMULÁRIO DE NOVO FORNECEDOR
             Recolhe os dados necessários para criar uma nova entidade
             fornecedora e permite anexar documentos.
             ===================================================== -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formNovoFornecedor"
              action="novo_fornecedor.php"
              method="post"
              enctype="multipart/form-data">


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
                     Cada secção agrupa uma área funcional do fornecedor.
                     ============================================= -->
                <div class="tab-content ficha-tab-content" id="tabsNovoFornecedorContent">
                    <!-- =========================================
                         SEPARADOR 1: IDENTIFICAÇÃO
                         Dados principais e classificação do fornecedor.
                         ========================================= -->
                    <div class="tab-pane fade show active"
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
                                       placeholder="Ex: MedSupply Portugal"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="nifFornecedor" class="form-label">NIF *</label>
                                <input type="text"
                                       class="form-control"
                                       id="nifFornecedor"
                                       name="nifFornecedor"
                                       placeholder="Ex: 514987321"
                                       required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label d-block">Tipo de Fornecedor *</label>

                                <div class="tipos-fornecedor-opcoes">
                                    <select class="form-select" id="tipoFornecedor" name="tipoFornecedor" required>
                                        <option value="">Selecionar tipo</option>
                                        <option value="Manutenção">Manutenção</option>
                                        <option value="Comercial">Comercial</option>
                                        <option value="Fabricante">Fabricante</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="estadoFornecedor" class="form-label">Estado *</label>
                                <select class="form-select" id="estadoFornecedor" name="estadoFornecedor" required>
                                    <option value="">Selecionar estado</option>
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                    <option value="Em avaliação">Em avaliação</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 2: CONTACTOS
                         Dados de contacto geral e contacto responsável.
                         ========================================= -->
                    <div class="tab-pane fade"
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
                                       placeholder="Ex: comercial@fornecedor.pt"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneFornecedor" class="form-label">Telefone *</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneFornecedor"
                                       name="telefoneFornecedor"
                                       placeholder="Ex: +351 221 234 567"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="websiteFornecedor" class="form-label">Website</label>
                                <input type="url"
                                       class="form-control"
                                       id="websiteFornecedor"
                                       name="websiteFornecedor"
                                       placeholder="Ex: https://www.fornecedor.pt">
                            </div>

                            <div class="col-md-4">
                                <label for="contactoResponsavel" class="form-label">Pessoa de Contacto</label>
                                <input type="text"
                                       class="form-control"
                                       id="contactoResponsavel"
                                       name="contactoResponsavel"
                                       placeholder="Ex: Ana Martins">
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneContacto" class="form-label">Telefone do Contacto</label>
                                <input type="text"
                                       class="form-control"
                                       id="telefoneContacto"
                                       name="telefoneContacto"
                                       placeholder="Ex: 912 345 678">
                            </div>

                            <div class="col-md-4">
                                <label for="emailContacto" class="form-label">Email do Contacto</label>
                                <input type="email"
                                       class="form-control"
                                       id="emailContacto"
                                       name="emailContacto"
                                       placeholder="Ex: tecnico@fornecedor.pt">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 3: MORADA
                         Morada e localização da entidade.
                         ========================================= -->
                    <div class="tab-pane fade"
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
                                       placeholder="Ex: Rua da Tecnologia, nº 120">
                            </div>

                            <div class="col-md-2">
                                <label for="codigoPostalFornecedor" class="form-label">Código Postal</label>
                                <input type="text"
                                       class="form-control"
                                       id="codigoPostalFornecedor"
                                       name="codigoPostalFornecedor"
                                       placeholder="Ex: 4000-000">
                            </div>

                            <div class="col-md-2">
                                <label for="localidadeFornecedor" class="form-label">Localidade *</label>
                                <input type="text"
                                       class="form-control"
                                       id="localidadeFornecedor"
                                       name="localidadeFornecedor"
                                       placeholder="Ex: Porto"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label for="paisFornecedor" class="form-label">País</label>
                                <input type="text"
                                       class="form-control"
                                       id="paisFornecedor"
                                       name="paisFornecedor"
                                       value="Portugal">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 4: SERVIÇOS E CONTRATO
                         Contrato ativo, datas e área de atuação.
                         ========================================= -->
                    <div class="tab-pane fade"
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
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                    <option value="Em análise">Em análise</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="inicioContratoFornecedor" class="form-label">Início do Contrato</label>
                                <input type="date"
                                       class="form-control"
                                       id="inicioContratoFornecedor"
                                       name="inicioContratoFornecedor">
                            </div>

                            <div class="col-md-4">
                                <label for="fimContratoFornecedor" class="form-label">Fim do Contrato</label>
                                <input type="date"
                                       class="form-control"
                                       id="fimContratoFornecedor"
                                       name="fimContratoFornecedor">
                            </div>

                            <div class="col-md-6">
                                <label for="areaAtuacaoFornecedor" class="form-label">Área de Atuação</label>
                                <textarea class="form-control"
                                          id="areaAtuacaoFornecedor"
                                          name="areaAtuacaoFornecedor"
                                          rows="5"
                                          placeholder="Ex: venda de equipamentos médicos, manutenção preventiva, calibração de dispositivos clínicos..."></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="equipamentosAssociadosFornecedor" class="form-label">Equipamentos / Marcas Associadas</label>
                                <textarea class="form-control"
                                          id="equipamentosAssociadosFornecedor"
                                          name="equipamentosAssociadosFornecedor"
                                          rows="5"
                                          placeholder="Ex: monitores multiparamétricos Philips, ventiladores Dräger, desfibrilhadores Zoll..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR 5: DOCUMENTOS
                         Permite adicionar contratos, certificados e
                         outros ficheiros associados ao fornecedor.
                         ========================================= -->
                    <div class="tab-pane fade"
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
                    <div class="tab-pane fade"
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
                                  placeholder="Indique informações relevantes sobre o fornecedor, qualidade do serviço, tempos de resposta ou notas técnicas."></textarea>
                    </div>
                </div>
            </div>
        </form>

    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
