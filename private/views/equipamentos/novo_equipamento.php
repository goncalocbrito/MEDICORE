<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

    <!-- =========================================================
         CONTEÚDO PRINCIPAL
         Usa a mesma estrutura visual da ficha_equipamento.html:
         - página em largura total
         - barra superior com botão voltar
         - formulário organizado por separadores Bootstrap
         ========================================================= -->
    <main class="conteudo-private ficha-equipamento-page">

            <!-- =================================================
                 BOTÕES FINAIS DO FORMULÁRIO
                 - Cancelar: volta à lista sem guardar
                 - Limpar: repõe o formulário vazio
                 - Guardar: envia o formulário
                 ================================================= -->
            <div class="form-actions">
                <a href="lista_equipamentos.html" class="btn btn-cancelar">
                    <i class="fa-solid fa-xmark me-2"></i> Cancelar
                </a>

                <button type="button" class="btn btn-limpar" id="btnLimparNovoEquipamento">
                    <i class="fa-solid fa-eraser me-2"></i> Limpar
                </button>

                <button type="submit" class="btn btn-guardar" form="formNovoEquipamento">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Equipamento
                </button>
            </div>

        <!-- =====================================================
             FORMULÁRIO DE NOVO EQUIPAMENTO
             Função principal:
             Registar um novo equipamento no inventário.

             Notas:
             - action deve ser alterado quando criares o PHP real.
             - enctype permite futuramente fazer upload de documentos.
             - id="formNovoEquipamento" é usado pelo JavaScript para simular o registo.
             ===================================================== -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formNovoEquipamento"
              action="processa_novo_equipamento.php"
              method="post"
              enctype="multipart/form-data">

            <!-- Campo oculto para o backend saber que a operação é inserir -->
            <input type="hidden" name="acao" value="inserir">


            <!-- =================================================
                 ÁREA PRINCIPAL DO FORMULÁRIO
                 Contém os separadores e o conteúdo de cada separador.
                 ================================================= -->
            <div class="ficha-area">

                <!-- =============================================
                     SEPARADORES BOOTSTRAP
                     Organizam o formulário numa única página,
                     mantendo o mesmo estilo da ficha do equipamento.
                     ============================================= -->
                <ul class="nav nav-tabs ficha-tabs" id="tabsNovoEquipamento" role="tablist">

                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                                id="identificacao-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#identificacao"
                                type="button"
                                role="tab"
                                aria-controls="identificacao"
                                aria-selected="true">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            Identificação
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="localizacao-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#localizacao"
                                type="button"
                                role="tab"
                                aria-controls="localizacao"
                                aria-selected="false">
                            <i class="fa-solid fa-location-dot me-2"></i>
                            Localização e Estado
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="aquisicao-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#aquisicao"
                                type="button"
                                role="tab"
                                aria-controls="aquisicao"
                                aria-selected="false">
                            <i class="fa-solid fa-truck-medical me-2"></i>
                            Aquisição
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="manutencao-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#manutencao"
                                type="button"
                                role="tab"
                                aria-controls="manutencao"
                                aria-selected="false">
                            <i class="fa-solid fa-screwdriver-wrench me-2"></i>
                            Manutenção
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
                     Cada tab-pane corresponde a uma secção do formulário.
                     ============================================= -->
                <div class="tab-content ficha-tab-content" id="tabsNovoEquipamentoContent">

                    <!-- =========================================
                         SEPARADOR 1: IDENTIFICAÇÃO
                         Dados principais e rastreabilidade do equipamento.
                         ========================================= -->
                    <div class="tab-pane fade show active"
                         id="identificacao"
                         role="tabpanel"
                         aria-labelledby="identificacao-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Identificação do Equipamento</h4>
                            <p>Preencha os dados principais do equipamento. O código interno deve ser único.</p>
                        </div>

                        <div class="row g-4">

                            <div class="col-md-4">
                                <label for="codigoInventario" class="form-label">Código de Inventário *</label>
                                <input type="text"
                                       class="form-control"
                                       id="codigoInventario"
                                       name="codigoInventario"
                                       placeholder="Ex: EQ-001"
                                       required>
                            </div>

                            <div class="col-md-8">
                                <label for="nomeEquipamento" class="form-label">Designação do Equipamento *</label>
                                <input type="text"
                                       class="form-control"
                                       id="nomeEquipamento"
                                       name="nomeEquipamento"
                                       placeholder="Ex: Monitor multiparamétrico"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="categoria" class="form-label">Categoria *</label>
                                <select class="form-select"
                                        id="categoria"
                                        name="categoria"
                                        required>
                                    <option value="">Selecionar categoria</option>
                                    <option value="Monitorização">Monitorização</option>
                                    <option value="Suporte de Vida">Suporte de Vida</option>
                                    <option value="Terapia">Terapia</option>
                                    <option value="Diagnóstico">Diagnóstico</option>
                                    <option value="Laboratório">Laboratório</option>
                                    <option value="Esterilização">Esterilização</option>
                                    <option value="Reabilitação">Reabilitação</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="fabricante" class="form-label">Fabricante / Marca *</label>
                                <input type="text"
                                       class="form-control"
                                       id="fabricante"
                                       name="fabricante"
                                       placeholder="Ex: Philips, Siemens, Dräger"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="modelo" class="form-label">Modelo *</label>
                                <input type="text"
                                       class="form-control"
                                       id="modelo"
                                       name="modelo"
                                       placeholder="Ex: IntelliVue MX450"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="numeroSerie" class="form-label">Número de Série *</label>
                                <input type="text"
                                       class="form-control"
                                       id="numeroSerie"
                                       name="numeroSerie"
                                       placeholder="Ex: SN-MX450-2026"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="tipoEntrada" class="form-label">Tipo de Entrada</label>
                                <select class="form-select"
                                        id="tipoEntrada"
                                        name="tipoEntrada">
                                    <option value="">Selecionar tipo</option>
                                    <option value="Compra">Compra</option>
                                    <option value="Doação">Doação</option>
                                    <option value="Aluguer">Aluguer</option>
                                    <option value="Empréstimo">Empréstimo</option>
                                </select>
                            </div>

                        </div>
                    </div>


                    <!-- =========================================
                         SEPARADOR 2: LOCALIZAÇÃO E ESTADO
                         Define a localização física e estado atual.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="localizacao"
                         role="tabpanel"
                         aria-labelledby="localizacao-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Localização e Estado</h4>
                            <p>Indique onde o equipamento se encontra e o seu estado operacional inicial.</p>
                        </div>

                        <div class="row g-4">

                            <div class="col-md-4">
                                <label for="departamento" class="form-label">Departamento / Serviço *</label>
                                <select class="form-select"
                                        id="departamento"
                                        name="departamento"
                                        required>
                                    <option value="">Selecionar departamento</option>
                                    <option value="Urgência">Urgência</option>
                                    <option value="Unidade de Cuidados Intensivos">Unidade de Cuidados Intensivos</option>
                                    <option value="Bloco Operatório">Bloco Operatório</option>
                                    <option value="Cardiologia">Cardiologia</option>
                                    <option value="Radiologia">Radiologia</option>
                                    <option value="Laboratório">Laboratório</option>
                                    <option value="Consulta Externa">Consulta Externa</option>
                                    <option value="Pediatria">Pediatria</option>
                                    <option value="Neonatologia">Neonatologia</option>
                                    <option value="Medicina Interna">Medicina Interna</option>
                                    <option value="Cirurgia Geral">Cirurgia Geral</option>
                                    <option value="Central de Esterilização">Central de Esterilização</option>
                                    <option value="Medicina Física e Reabilitação">Medicina Física e Reabilitação</option>
                                    <option value="Farmácia Hospitalar">Farmácia Hospitalar</option>
                                    <option value="Hospital de Dia">Hospital de Dia</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="edificio" class="form-label">Edifício *</label>
                                <select class="form-select"
                                        id="edificio"
                                        name="edificio"
                                        required>
                                    <option value="">Selecionar edifício</option>
                                    <option value="Edifício A">Edifício A</option>
                                    <option value="Edifício B">Edifício B</option>
                                    <option value="Edifício C">Edifício C</option>
                                    <option value="Edifício D">Edifício D</option>
                                    <option value="Edifício Técnico">Edifício Técnico</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="piso" class="form-label">Piso *</label>
                                <select class="form-select"
                                        id="piso"
                                        name="piso"
                                        required>
                                    <option value="">Selecionar piso</option>
                                    <option value="-1">Piso -1</option>
                                    <option value="0">Piso 0</option>
                                    <option value="1">Piso 1</option>
                                    <option value="2">Piso 2</option>
                                    <option value="3">Piso 3</option>
                                    <option value="4">Piso 4</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="sala" class="form-label">Sala / Gabinete *</label>
                                <input type="text"
                                       class="form-control"
                                       id="sala"
                                       name="sala"
                                       placeholder="Ex: Sala 2, BO-02, Lab-105"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label for="estado" class="form-label">Estado do Equipamento *</label>
                                <select class="form-select"
                                        id="estado"
                                        name="estado"
                                        required>
                                    <option value="">Selecionar estado</option>
                                    <option value="Ativo">Ativo</option>
                                    <option value="Em manutenção">Em manutenção</option>
                                    <option value="Inativo">Inativo</option>
                                    <option value="Em calibração">Em calibração</option>
                                    <option value="Em quarentena">Em quarentena</option>
                                    <option value="Abatido">Abatido</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="criticidade" class="form-label d-flex align-items-center gap-2">
                                    <span>Criticidade *</span>

                                    <!-- Popover Bootstrap com ajuda para o utilizador -->
                                    <button type="button"
                                            class="btn-ajuda-criticidade"
                                            data-bs-toggle="popover"
                                            data-bs-trigger="hover focus"
                                            data-bs-placement="right"
                                            data-bs-html="true"
                                            title="Critérios de Criticidade"
                                            data-bs-content="
                                                <strong>Baixa:</strong> falha com impacto reduzido.<br>
                                                <em>Ex:</em> balança clínica, termómetro digital, otoscópio.<br><br>

                                                <strong>Média:</strong> pode atrasar o serviço, mas existem alternativas.<br>
                                                <em>Ex:</em> eletrocardiógrafo, aspirador portátil, fisioterapia.<br><br>

                                                <strong>Alta:</strong> impacto significativo na prestação de cuidados.<br>
                                                <em>Ex:</em> monitor multiparamétrico, ecógrafo, incubadora neonatal.<br><br>

                                                <strong>Crítica:</strong> equipamento essencial para suporte de vida ou emergência.<br>
                                                <em>Ex:</em> ventilador pulmonar, desfibrilhador, máquina de anestesia.
                                            ">
                                        <i class="fa-solid fa-circle-question"></i>
                                    </button>
                                </label>

                                <select class="form-select"
                                        id="criticidade"
                                        name="criticidade"
                                        required>
                                    <option value="">Selecionar criticidade</option>
                                    <option value="baixa">Baixa</option>
                                    <option value="media">Média</option>
                                    <option value="alta">Alta</option>
                                    <option value="critica">Crítica</option>
                                </select>

                                <!-- O JavaScript atualiza esta descrição automaticamente -->
                                <small id="descricaoCriticidade" class="texto-ajuda-form">
                                    Selecione uma criticidade para ver a descrição.
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Equipamento Operacional?</label>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="operacional"
                                           id="operacionalSim"
                                           value="sim"
                                           checked>
                                    <label class="form-check-label" for="operacionalSim">Sim</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="operacional"
                                           id="operacionalNao"
                                           value="nao">
                                    <label class="form-check-label" for="operacionalNao">Não</label>
                                </div>
                            </div>

                        </div>
                    </div>


                    <!-- =========================================
                         SEPARADOR 3: AQUISIÇÃO
                         Dados administrativos, fornecedor, garantia e contrato.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="aquisicao"
                         role="tabpanel"
                         aria-labelledby="aquisicao-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Aquisição, Fornecedor e Garantia</h4>
                            <p>Registe informação administrativa sobre aquisição, fornecedor e garantia.</p>
                        </div>

                        <div class="row g-4">

                            <div class="col-md-4">
                                <label for="fornecedor" class="form-label">Fornecedor</label>
                                <select class="form-select"
                                        id="fornecedor"
                                        name="fornecedor">
                                    <option value="">Selecionar fornecedor</option>
                                    <option value="MedSupply Portugal">MedSupply Portugal</option>
                                    <option value="Biomedical Solutions">Biomedical Solutions</option>
                                    <option value="ClinicalTech Equipamentos">ClinicalTech Equipamentos</option>
                                    <option value="Philips Medical Systems">Philips Medical Systems</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="dataFabrico" class="form-label">Data de Fabrico</label>
                                <input type="date"
                                       class="form-control"
                                       id="dataFabrico"
                                       name="dataFabrico">
                            </div>

                            <div class="col-md-4">
                                <label for="dataAquisicao" class="form-label">Data de Aquisição</label>
                                <input type="date"
                                       class="form-control"
                                       id="dataAquisicao"
                                       name="dataAquisicao">
                            </div>

                            <div class="col-md-4">
                                <label for="dataInstalacao" class="form-label">Data de Instalação</label>
                                <input type="date"
                                       class="form-control"
                                       id="dataInstalacao"
                                       name="dataInstalacao">
                            </div>

                            <div class="col-md-4">
                                <label for="valorAquisicao" class="form-label">Custo de Aquisição (€)</label>
                                <input type="number"
                                       class="form-control"
                                       id="valorAquisicao"
                                       name="valorAquisicao"
                                       min="0"
                                       step="0.01"
                                       placeholder="Ex: 3500.00">
                            </div>

                            <div class="col-md-4">
                                <label for="fimGarantia" class="form-label">Fim da Garantia</label>
                                <input type="date"
                                       class="form-control"
                                       id="fimGarantia"
                                       name="fimGarantia">
                            </div>

                            <div class="col-md-4">
                                <label for="contratoManutencao" class="form-label">Contrato de Manutenção</label>
                                <select class="form-select"
                                        id="contratoManutencao"
                                        name="contratoManutencao">
                                    <option value="">Selecionar opção</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                    <option value="Em análise">Em análise</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="tipoContrato" class="form-label">Tipo de Contrato</label>
                                <select class="form-select"
                                        id="tipoContrato"
                                        name="tipoContrato">
                                    <option value="">Selecionar tipo</option>
                                    <option value="Preventivo">Preventivo</option>
                                    <option value="Corretivo">Corretivo</option>
                                    <option value="Preventivo e Corretivo">Preventivo e Corretivo</option>
                                    <option value="Calibração">Calibração</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="entidadeResponsavel" class="form-label">Entidade Responsável</label>
                                <input type="text"
                                       class="form-control"
                                       id="entidadeResponsavel"
                                       name="entidadeResponsavel"
                                       placeholder="Ex: Biomedical Solutions">
                            </div>

                        </div>
                    </div>


                    <!-- =========================================
                         SEPARADOR 4: MANUTENÇÃO
                         Datas de manutenção, calibração e técnico responsável.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="manutencao"
                         role="tabpanel"
                         aria-labelledby="manutencao-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Manutenção, Calibração e Acompanhamento Técnico</h4>
                            <p>Indique datas de manutenção/calibração e o responsável técnico pelo equipamento.</p>
                        </div>

                        <div class="row g-4">

                            <div class="col-md-4">
                                <label for="ultimaManutencao" class="form-label">Última Manutenção</label>
                                <input type="date"
                                       class="form-control"
                                       id="ultimaManutencao"
                                       name="ultimaManutencao">
                            </div>

                            <div class="col-md-4">
                                <label for="proximaManutencao" class="form-label">Próxima Manutenção</label>
                                <input type="date"
                                       class="form-control"
                                       id="proximaManutencao"
                                       name="proximaManutencao">
                            </div>

                            <div class="col-md-4">
                                <label for="periodicidade" class="form-label">Periodicidade de Manutenção</label>
                                <select class="form-select"
                                        id="periodicidade"
                                        name="periodicidade">
                                    <option value="">Selecionar periodicidade</option>
                                    <option value="Mensal">Mensal</option>
                                    <option value="Trimestral">Trimestral</option>
                                    <option value="Semestral">Semestral</option>
                                    <option value="Anual">Anual</option>
                                    <option value="Sob pedido">Sob pedido</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="ultimaCalibracao" class="form-label">Última Calibração</label>
                                <input type="date"
                                       class="form-control"
                                       id="ultimaCalibracao"
                                       name="ultimaCalibracao">
                            </div>

                            <div class="col-md-4">
                                <label for="proximaCalibracao" class="form-label">Próxima Calibração</label>
                                <input type="date"
                                       class="form-control"
                                       id="proximaCalibracao"
                                       name="proximaCalibracao">
                            </div>

                            <div class="col-md-4">
                                <label for="responsavelTecnico" class="form-label">Responsável Técnico</label>
                                <input type="text"
                                       class="form-control"
                                       id="responsavelTecnico"
                                       name="responsavelTecnico"
                                       placeholder="Ex: Eng. Gonçalo Brito">
                            </div>

                        </div>
                    </div>


                    <!-- =========================================
                         SEPARADOR 5: DOCUMENTOS
                         Permite associar fotografia, manuais e certificados.
                         O botão Adicionar Documento é usado pelo JS atual.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="documentos"
                         role="tabpanel"
                         aria-labelledby="documentos-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h4>Documentos do Equipamento</h4>
                                <p>Adicione fotografia, manuais, certificados, faturas ou outros documentos técnicos.</p>
                            </div>

                            <button type="button"
                                    class="btn btn-adicionar-documento"
                                    id="btnAdicionarDocumento">
                                <i class="fa-solid fa-plus me-2"></i> Adicionar Documento
                            </button>
                        </div>

                        <!--
                            O JavaScript procura o id="listaDocumentos".
                            Por isso este id foi mantido para não perder a funcionalidade existente.
                        -->
                        <div id="listaDocumentos">

                            <div class="documento-form-item">
                                <div class="row g-4 align-items-end">

                                    <div class="col-md-4">
                                        <label class="form-label">Tipo de Documento</label>
                                        <select class="form-select" name="tipoDocumento[]">
                                            <option value="">Selecionar tipo</option>
                                            <option value="fotografia">Fotografia do Equipamento</option>
                                            <option value="manual">Manual de Instruções</option>
                                            <option value="certificado_calibracao">Certificado de Calibração</option>
                                            <option value="certificado_manutencao">Certificado de Manutenção</option>
                                            <option value="ficha_tecnica">Ficha Técnica</option>
                                            <option value="garantia">Documento de Garantia</option>
                                            <option value="fatura">Fatura / Guia de Aquisição</option>
                                            <option value="conformidade">Declaração de Conformidade</option>
                                            <option value="relatorio">Relatório Técnico</option>
                                            <option value="outro">Outro</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Nome do Documento</label>
                                        <input type="text"
                                               class="form-control"
                                               name="nomeDocumento[]"
                                               placeholder="Ex: Manual Philips MX450">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Ficheiro</label>
                                        <input type="file"
                                               class="form-control"
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
                         Campo livre para notas técnicas.
                         ========================================= -->
                    <div class="tab-pane fade"
                         id="observacoes-tab-pane"
                         role="tabpanel"
                         aria-labelledby="observacoes-tab"
                         tabindex="0">

                        <div class="secao-ficha-titulo">
                            <h4>Observações Técnicas</h4>
                            <p>Registe notas relevantes sobre acessórios, limitações, estado físico ou contexto de utilização.</p>
                        </div>

                        <textarea class="form-control"
                                  id="observacoes"
                                  name="observacoes"
                                  rows="7"
                                  placeholder="Indique observações relevantes sobre o equipamento, estado físico, acessórios, limitações ou notas de manutenção."></textarea>

                    </div>

                </div>
            </div>
        </form>

    </main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

