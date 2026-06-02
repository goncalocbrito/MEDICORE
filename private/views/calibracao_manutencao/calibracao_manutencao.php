<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>


    <!-- =========================================================
         CONTEÚDO PRINCIPAL
         Página de consulta operacional para acompanhar procedimentos
         de calibração e manutenção dos equipamentos hospitalares.
         ========================================================= -->
    <main class="conteudo-private">
        <!-- =====================================================
             TÍTULO DA PÁGINA
             Descreve o objetivo da listagem e segue o padrão das
             páginas de equipamentos, fornecedores e localizações.
             ===================================================== -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Calibrações e Manutenções</h2>
                <p class="subtitulo-pagina">
                    Acompanhamento dos procedimentos técnicos em curso ou efetuados nos equipamentos do hospital.
                </p>
            </div>
            <!-- Botão para abrir o modal de novo pedido. -->
            <button type="button"
                    class="btn btn-adicionar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNovoPedidoCalibracaoManutencao">
                <i class="fa-solid fa-plus me-2"></i> Novo Pedido
            </button>
        </div>
        <!-- =====================================================
             TABELA DE PROCEDIMENTOS
             Mostra o equipamento, o tipo de intervenção, fornecedor
             responsável, estado atual da operação e datas relevantes.
             ===================================================== -->
        <!-- Pesquisa e filtros da tabela de processos a decorrer. -->
        <section class="filtros-tabela" data-tabela=".tabela-calibracoes-manutencoes" aria-label="Pesquisa e filtros de calibrações e manutenções">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="pesquisaCalibracoes" class="form-label">Pesquisar</label>
                    <input type="search" class="form-control" id="pesquisaCalibracoes" data-filtro="texto" placeholder="Código, equipamento, procedimento ou estado">
                </div>
                                <div class="col-lg-2 col-md-6">
                    <label for="filtroProcedimentoCalibracoes" class="form-label">Procedimento</label>
                    <select class="form-select" id="filtroProcedimentoCalibracoes" data-filtro="coluna" data-coluna="3">
                        <option value="">Todos</option>
                        <option value="Calibração">Calibração</option>
                        <option value="Manutenção preventiva">Preventiva</option>
                        <option value="Manutenção corretiva">Corretiva</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroEstadoCalibracoes" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstadoCalibracoes" data-filtro="coluna" data-coluna="5">
                        <option value="">Todos</option>
                        <option value="Aguarda fornecedor">Aguarda fornecedor</option>
                        <option value="Em manutenção">Em manutenção</option>
                        <option value="Em calibração">Em calibração</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12">
                    <button type="button" class="btn btn-limpar-filtros w-100" data-limpar-filtros>
                        <i class="fa-solid fa-rotate-left me-2"></i> Limpar
                    </button>
                </div>
            </div>
        </section>
        <div class="table-responsive tabela-container">
            <table class="table table-hover align-middle tabela-equipamentos tabela-calibracoes-manutencoes">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Alvo</th>
                        <th>Associado a</th>
                        <th>Procedimento</th>
                        <th>Data Prevista</th>
                        <th>Estado da Operação</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaPedidosCalibracaoManutencao">
                    <!-- Linha 1: manutenção preventiva programada -->
                    <tr data-codigo="EQ-001"
                        data-equipamento="Monitor Multiparamétrico"
                        data-tipo-alvo="Equipamento"
                        data-equipamento-associado="Equipamento principal"
                        data-categoria="Monitorização"
                        data-localizacao="UCI - Sala 2"
                        data-procedimento="Manutenção preventiva"
                        data-fornecedor="MedSupply Portugal"
                        data-data="2026-09-12"
                        data-estado="Aguarda fornecedor"
                        data-observacoes="Pedido enviado para confirmação de agenda.">
                        <td>EQ-001</td>
                        <td>Monitor Multiparamétrico</td>
                        <td>Equipamento principal</td>
                        <td>
                            <span class="tipo-fornecedor tipo-manutencao">Manutenção preventiva</span>
                        </td>
                        <td>12/09/2026</td>
                        <td>
                            <span class="estado estado-manutencao">Aguarda fornecedor</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-ficha btn-ver-editar-pedido" title="Ver/editar pedido">
                                <i class="fa-solid fa-file-lines"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-editar btn-finalizar-pedido" title="Finalizar pedido">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-eliminar btn-eliminar-pedido" title="Eliminar pedido">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <!-- Linha 2: manutenção corretiva em curso -->
                    <tr data-codigo="EQ-002"
                        data-equipamento="Ventilador Pulmonar"
                        data-tipo-alvo="Equipamento"
                        data-equipamento-associado="Equipamento principal"
                        data-categoria="Suporte de Vida"
                        data-localizacao="Urgência - Sala 1"
                        data-procedimento="Manutenção corretiva"
                        data-fornecedor="Biomedical Solutions"
                        data-data="2026-05-28"
                        data-estado="Em manutenção"
                        data-observacoes="Intervenção técnica por falha no sistema de ventilação.">
                        <td>EQ-002</td>
                        <td>Ventilador Pulmonar</td>
                        <td>Equipamento principal</td>
                        <td>
                            <span class="tipo-localizacao tipo-urgencia">Manutenção corretiva</span>
                        </td>
                        <td>28/05/2026</td>
                        <td>
                            <span class="estado estado-manutencao">Em manutenção</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-ficha btn-ver-editar-pedido" title="Ver/editar pedido">
                                <i class="fa-solid fa-file-lines"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-editar btn-finalizar-pedido" title="Finalizar pedido">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-eliminar btn-eliminar-pedido" title="Eliminar pedido">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <!-- Linha 3: calibração em curso -->
                    <tr data-codigo="EQ-003"
                        data-equipamento="Desfibrilhador"
                        data-tipo-alvo="Equipamento"
                        data-equipamento-associado="Equipamento principal"
                        data-categoria="Emergência"
                        data-localizacao="Bloco Operatório"
                        data-procedimento="Calibração"
                        data-fornecedor="CalibraMed"
                        data-data="2026-05-30"
                        data-estado="Em calibração"
                        data-observacoes="A aguardar emissão do certificado de calibração.">
                        <td>EQ-003</td>
                        <td>Desfibrilhador</td>
                        <td>Equipamento principal</td>
                        <td>
                            <span class="tipo-fornecedor tipo-calibracao">Calibração</span>
                        </td>
                        <td>30/05/2026</td>
                        <td>
                            <span class="estado estado-manutencao">Em calibração</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-ficha btn-ver-editar-pedido" title="Ver/editar pedido">
                                <i class="fa-solid fa-file-lines"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-editar btn-finalizar-pedido" title="Finalizar pedido">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-eliminar btn-eliminar-pedido" title="Eliminar pedido">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <!-- Linha 4: calibração a aguardar entidade externa -->
                    <tr data-codigo="EQ-005"
                        data-equipamento="Bomba de Infusão"
                        data-tipo-alvo="Equipamento"
                        data-equipamento-associado="Equipamento principal"
                        data-categoria="Terapia"
                        data-localizacao="Medicina Interna"
                        data-procedimento="Calibração"
                        data-fornecedor="CalibraMed"
                        data-data="2026-06-05"
                        data-estado="Aguarda fornecedor"
                        data-observacoes="Fornecedor contactado para recolha do equipamento.">
                        <td>EQ-005</td>
                        <td>Bomba de Infusão</td>
                        <td>Equipamento principal</td>
                        <td>
                            <span class="tipo-fornecedor tipo-calibracao">Calibração</span>
                        </td>
                        <td>05/06/2026</td>
                        <td>
                            <span class="estado estado-manutencao">Aguarda fornecedor</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-ficha btn-ver-editar-pedido" title="Ver/editar pedido">
                                <i class="fa-solid fa-file-lines"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-editar btn-finalizar-pedido" title="Finalizar pedido">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-eliminar btn-eliminar-pedido" title="Eliminar pedido">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <!-- =========================================================
         MODAL DE DETALHES / EDIÇÃO DO PEDIDO
         Abre ao clicar no botão de ficha. Permite consultar os dados
         do pedido e alterar procedimento, fornecedor, data, estado
         e observações.
         ========================================================= -->
    <div class="modal fade"
         id="modalEditarPedidoCalibracaoManutencao"
         tabindex="-1"
         aria-labelledby="modalEditarPedidoCalibracaoManutencaoLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalEditarPedidoCalibracaoManutencaoLabel">
                            <i class="fa-solid fa-file-lines me-2"></i>
                            Detalhes do Pedido
                        </h5>
                        <p class="modal-remocao-subtitulo text-muted">
                            Consulte ou altere os dados do pedido selecionado.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="formEditarPedidoCalibracaoManutencao">
                    <div class="modal-body">
                        <input type="hidden" id="editarPedidoLinhaIndice">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label for="editarPedidoCodigo" class="form-label">Código</label>
                                <input type="text" class="form-control campo-bloqueado" id="editarPedidoCodigo" readonly>
                            </div>
                            <div class="col-md-5">
                                <label for="editarPedidoEquipamento" class="form-label">Alvo</label>
                                <input type="text" class="form-control campo-bloqueado" id="editarPedidoEquipamento" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="editarPedidoEquipamentoAssociado" class="form-label">Associado a</label>
                                <input type="text" class="form-control campo-bloqueado" id="editarPedidoEquipamentoAssociado" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="editarPedidoCategoria" class="form-label">Categoria</label>
                                <input type="text" class="form-control campo-bloqueado" id="editarPedidoCategoria" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="editarPedidoLocalizacao" class="form-label">Localização</label>
                                <input type="text" class="form-control campo-bloqueado" id="editarPedidoLocalizacao" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="editarPedidoProcedimento" class="form-label">Procedimento *</label>
                                <select class="form-select" id="editarPedidoProcedimento" required>
                                    <option value="Calibração">Calibração</option>
                                    <option value="Manutenção preventiva">Manutenção preventiva</option>
                                    <option value="Manutenção corretiva">Manutenção corretiva</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editarPedidoFornecedor" class="form-label">Fornecedor / Técnico *</label>
                                <select class="form-select" id="editarPedidoFornecedor" required>
                                    <option value="MedSupply Portugal">MedSupply Portugal</option>
                                    <option value="Biomedical Solutions">Biomedical Solutions</option>
                                    <option value="CalibraMed">CalibraMed</option>
                                    <option value="Eng. Gonçalo Brito">Eng. Gonçalo Brito</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editarPedidoData" class="form-label">Data Prevista *</label>
                                <input type="date" class="form-control" id="editarPedidoData" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editarPedidoEstado" class="form-label">Estado *</label>
                                <select class="form-select" id="editarPedidoEstado" required>
                                    <option value="Aguarda fornecedor">Aguarda fornecedor</option>
                                    <option value="Em manutenção">Em manutenção</option>
                                    <option value="Em calibração">Em calibração</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="editarPedidoObservacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="editarPedidoObservacoes" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-2"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-guardar">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- =========================================================
         MODAL DE REMOÇÃO DO PEDIDO
         Confirma a eliminação visual do pedido selecionado.
         ========================================================= -->
    <div class="modal fade"
         id="modalEliminarPedidoCalibracaoManutencao"
         tabindex="-1"
         aria-labelledby="modalEliminarPedidoCalibracaoManutencaoLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
            <div class="modal-content modal-apagar-equipamento">
                <div class="modal-header modal-remocao-header">
                    <div>
                        <h5 class="modal-title" id="modalEliminarPedidoCalibracaoManutencaoLabel">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Confirmar remoção
                        </h5>
                        <p class="modal-remocao-subtitulo">
                            Confirme os dados antes de remover o pedido.
                        </p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body modal-remocao-body">
                    <div class="modal-resumo-equipamento modal-resumo-remocao">
                        <div class="modal-linha"><strong>Código</strong><span id="modalEliminarPedidoCodigo">---</span></div>
                        <div class="modal-linha"><strong>Equipamento</strong><span id="modalEliminarPedidoEquipamento">---</span></div>
                        <div class="modal-linha"><strong>Procedimento</strong><span id="modalEliminarPedidoProcedimento">---</span></div>
                        <div class="modal-linha"><strong>Estado</strong><span id="modalEliminarPedidoEstado">---</span></div>
                    </div>
                    <p class="texto-confirmacao-remocao">
                        Confirma que pretende remover este pedido de calibração/manutenção da lista?
                    </p>
                </div>
                <div class="modal-footer modal-remocao-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-confirmar-remocao" id="btnConfirmarEliminarPedidoCalibracaoManutencao">
                        <i class="fa-solid fa-trash me-2"></i> Guardar Alteração
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- =========================================================
         MODAL DE NOVO PEDIDO DE CALIBRAÇÃO/MANUTENÇÃO
         Permite registar rapidamente uma nova intervenção técnica
         sem sair da página principal deste módulo.
         ========================================================= -->
    <div class="modal fade"
         id="modalNovoPedidoCalibracaoManutencao"
         tabindex="-1"
         aria-labelledby="modalNovoPedidoCalibracaoManutencaoLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <!-- Cabeçalho do modal -->
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalNovoPedidoCalibracaoManutencaoLabel">
                            <i class="fa-solid fa-screwdriver-wrench me-2"></i>
                            Novo Pedido
                        </h5>
                        <p class="modal-remocao-subtitulo text-muted">
                            Registe um pedido de calibração ou manutenção para um equipamento.
                        </p>
                    </div>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                    </button>
                </div>
                <!-- Formulário do novo pedido -->
                <form id="formNovoPedidoCalibracaoManutencao">
                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Tipo de alvo selecionado para intervenção -->
                            <div class="col-md-4">
                                <label for="pedidoTipoAlvo" class="form-label">Tipo de alvo *</label>
                                <select class="form-select"
                                        id="pedidoTipoAlvo"
                                        name="pedidoTipoAlvo"
                                        required>
                                    <option value="Equipamento">Equipamento</option>
                                    <option value="Acessório">Acessório</option>
                                </select>
                            </div>
                            <!-- Equipamento ou acessório selecionado para intervenção -->
                            <div class="col-md-8">
                                <label for="pedidoEquipamento" class="form-label">Equipamento / Acessório *</label>
                                <select class="form-select"
                                        id="pedidoEquipamento"
                                        name="pedidoEquipamento"
                                        required>
                                    <option value="">Selecionar alvo</option>
                                    <optgroup label="Equipamentos">
                                    <option value="EQ-001"
                                            data-tipo-alvo="Equipamento"
                                            data-nome="Monitor Multiparamétrico"
                                            data-categoria="Monitorização"
                                            data-localizacao="UCI - Sala 2"
                                            data-associado="Equipamento principal">
                                        EQ-001 | Monitor Multiparamétrico
                                    </option>
                                    <option value="EQ-002"
                                            data-tipo-alvo="Equipamento"
                                            data-nome="Ventilador Pulmonar"
                                            data-categoria="Suporte de Vida"
                                            data-localizacao="Urgência - Sala 1"
                                            data-associado="Equipamento principal">
                                        EQ-002 | Ventilador Pulmonar
                                    </option>
                                    <option value="EQ-003"
                                            data-tipo-alvo="Equipamento"
                                            data-nome="Desfibrilhador"
                                            data-categoria="Emergência"
                                            data-localizacao="Bloco Operatório"
                                            data-associado="Equipamento principal">
                                        EQ-003 | Desfibrilhador
                                    </option>
                                    <option value="EQ-004"
                                            data-tipo-alvo="Equipamento"
                                            data-nome="Monitor de Sinais Vitais"
                                            data-categoria="Monitorização"
                                            data-localizacao="Pediatria - Sala 3"
                                            data-associado="Equipamento principal">
                                        EQ-004 | Monitor de Sinais Vitais
                                    </option>
                                    <option value="EQ-005"
                                            data-tipo-alvo="Equipamento"
                                            data-nome="Bomba de Infusão"
                                            data-categoria="Terapia"
                                            data-localizacao="Medicina Interna"
                                            data-associado="Equipamento principal">
                                        EQ-005 | Bomba de Infusão
                                    </option>
                                    <option value="EQ-006"
                                            data-tipo-alvo="Equipamento"
                                            data-nome="Oxímetro de Pulso"
                                            data-categoria="Monitorização"
                                            data-localizacao="Urgência - Triagem"
                                            data-associado="Equipamento principal">
                                        EQ-006 | Oxímetro de Pulso
                                    </option>
                                    </optgroup>
                                    <optgroup label="Acessórios">
                                    <option value="ACC-002"
                                            data-tipo-alvo="Acessório"
                                            data-nome="Sensor SpO2"
                                            data-categoria="Sensor"
                                            data-localizacao="UCI - Sala 2"
                                            data-associado="EQ-001 | Monitor Multiparamétrico">
                                        ACC-002 | Sensor SpO2
                                    </option>
                                    <option value="ACC-003"
                                            data-tipo-alvo="Acessório"
                                            data-nome="Braçadeira NIBP adulto"
                                            data-categoria="Consumível reutilizável"
                                            data-localizacao="UCI - Sala 2"
                                            data-associado="EQ-001 | Monitor Multiparamétrico">
                                        ACC-003 | Braçadeira NIBP adulto
                                    </option>
                                    <option value="ACC-004"
                                            data-tipo-alvo="Acessório"
                                            data-nome="Circuito respiratório reutilizável"
                                            data-categoria="Módulo"
                                            data-localizacao="Urgência - Sala 1"
                                            data-associado="EQ-002 | Ventilador Pulmonar">
                                        ACC-004 | Circuito respiratório reutilizável
                                    </option>
                                    </optgroup>
                                </select>
                            </div>
                            <!-- Tipo de procedimento técnico -->
                            <div class="col-md-6">
                                <label for="pedidoProcedimento" class="form-label">Procedimento *</label>
                                <select class="form-select"
                                        id="pedidoProcedimento"
                                        name="pedidoProcedimento"
                                        required>
                                    <option value="">Selecionar procedimento</option>
                                    <option value="Calibração">Calibração</option>
                                    <option value="Manutenção preventiva">Manutenção preventiva</option>
                                    <option value="Manutenção corretiva">Manutenção corretiva</option>
                                </select>
                            </div>
                            <!-- Entidade responsável pelo pedido -->
                            <div class="col-md-6">
                                <label for="pedidoFornecedor" class="form-label">Fornecedor / Técnico *</label>
                                <select class="form-select"
                                        id="pedidoFornecedor"
                                        name="pedidoFornecedor"
                                        required>
                                    <option value="">Selecionar responsável</option>
                                    <option value="MedSupply Portugal">MedSupply Portugal</option>
                                    <option value="Biomedical Solutions">Biomedical Solutions</option>
                                    <option value="CalibraMed">CalibraMed</option>
                                    <option value="Eng. Gonçalo Brito">Eng. Gonçalo Brito</option>
                                </select>
                            </div>
                            <!-- Data prevista para execução -->
                            <div class="col-md-3">
                                <label for="pedidoDataPrevista" class="form-label">Data Prevista *</label>
                                <input type="date"
                                       class="form-control"
                                       id="pedidoDataPrevista"
                                       name="pedidoDataPrevista"
                                       required>
                            </div>
                            <!-- Estado inicial da operação -->
                            <div class="col-md-3">
                                <label for="pedidoEstadoOperacao" class="form-label">Estado *</label>
                                <select class="form-select"
                                        id="pedidoEstadoOperacao"
                                        name="pedidoEstadoOperacao"
                                        required>
                                    <option value="">Selecionar estado</option>
                                    <option value="Aguarda fornecedor">Aguarda fornecedor</option>
                                    <option value="Em manutenção">Em manutenção</option>
                                    <option value="Em calibração">Em calibração</option>
                                </select>
                            </div>
                            <!-- Observações ou motivo do pedido -->
                            <div class="col-12">
                                <label for="pedidoObservacoes" class="form-label">Observações</label>
                                <textarea class="form-control"
                                          id="pedidoObservacoes"
                                          name="pedidoObservacoes"
                                          rows="4"
                                          placeholder="Indique o motivo do pedido, sintomas, contexto clínico ou informação relevante para o fornecedor/técnico."></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Ações do modal -->
                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-cancelar"
                                data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-2"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-guardar">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
