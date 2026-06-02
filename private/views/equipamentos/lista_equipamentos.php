<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

        <main class="conteudo-private">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="titulo-pagina">Gestão de Equipamentos</h2>
                <p class="subtitulo-pagina">
                    Consulta, registo e acompanhamento dos equipamentos médicos hospitalares.
                </p>
            </div>
            <a href="novo_equipamento.php" class="btn btn-adicionar">
                <i class="fa-solid fa-plus me-2"></i> Adicionar Equipamento
            </a>
        </div>
        <!-- Pesquisa e filtros da tabela de equipamentos. -->
        <section class="filtros-tabela" data-tabela=".tabela-equipamentos" aria-label="Pesquisa e filtros de equipamentos">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="pesquisaEquipamentos" class="form-label">Pesquisar</label>
                    <input type="search" class="form-control" id="pesquisaEquipamentos" data-filtro="texto" placeholder="Código, equipamento, categoria, localização ou estado">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroCategoriaEquipamentos" class="form-label">Categoria</label>
                    <select class="form-select" id="filtroCategoriaEquipamentos" data-filtro="coluna" data-coluna="2">
                        <option value="">Todas</option>
                        <option value="Monitorização">Monitorização</option>
                        <option value="Suporte de Vida">Suporte de Vida</option>
                        <option value="Emergência">Emergência</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroLocalizacaoEquipamentos" class="form-label">Localização</label>
                    <select class="form-select" id="filtroLocalizacaoEquipamentos" data-filtro="coluna" data-coluna="3">
                        <option value="">Todas</option>
                        <option value="UCI">UCI</option>
                        <option value="Urgência">Urgência</option>
                        <option value="Bloco Operatório">Bloco Operatório</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroEstadoEquipamentos" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstadoEquipamentos" data-filtro="coluna" data-coluna="4">
                        <option value="">Todos</option>
                        <option value="Ativo">Ativo</option>
                        <option value="Em manutenção">Em manutenção</option>
                        <option value="Avariado">Avariado</option>
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
            <table class="table table-hover align-middle tabela-equipamentos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Equipamento</th>
                        <th>Categoria</th>
                        <th>Localização</th>
                        <th>Estado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>EQ-001</td>
                        <td>Monitor Multiparamétrico</td>
                        <td>Monitorização</td>
                        <td>UCI - Sala 2</td>
                        <td>
                            <span class="estado estado-ativo">Ativo</span>
                        </td>
                        <td class="text-center">
                            <a href="ficha_equipamento.php?id=EQ-001" class="btn btn-sm btn-ficha" title="Abrir ficha do equipamento">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar"
                                    title="Eliminar equipamento"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarEquipamento"
                                    data-codigo="EQ-001"
                                    data-nome="Monitor Multiparamétrico"
                                    data-categoria="Monitorização"
                                    data-fabricante="Philips"
                                    data-modelo="IntelliVue MX450"
                                    data-serie="SN-MX450-2024"
                                    data-localizacao="UCI - Sala 2"
                                    data-estado="Ativo">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>EQ-002</td>
                        <td>Ventilador Pulmonar</td>
                        <td>Suporte de Vida</td>
                        <td>Urgência - Sala 1</td>
                        <td>
                            <span class="estado estado-manutencao">Em manutenção</span>
                        </td>
                        <td class="text-center">
                            <a href="ficha_equipamento.php?id=EQ-002" class="btn btn-sm btn-ficha" title="Abrir ficha do equipamento">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar"
                                    title="Eliminar equipamento"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarEquipamento"
                                    data-codigo="EQ-002"
                                    data-nome="Ventilador Pulmonar"
                                    data-categoria="Suporte de Vida"
                                    data-fabricante="Dräger"
                                    data-modelo="Evita V300"
                                    data-serie="SN-EV300-1198"
                                    data-localizacao="Urgência - Sala 1"
                                    data-estado="Em manutenção">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>EQ-003</td>
                        <td>Desfibrilhador</td>
                        <td>Emergência</td>
                        <td>Bloco Operatório</td>
                        <td>
                            <span class="estado estado-avariado">Avariado</span>
                        </td>
                        <td class="text-center">
                            <a href="ficha_equipamento.php?id=EQ-003" class="btn btn-sm btn-ficha" title="Abrir ficha do equipamento">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar"
                                    title="Eliminar equipamento"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarEquipamento"
                                    data-codigo="EQ-002"
                                    data-nome="Ventilador Pulmonar"
                                    data-categoria="Suporte de Vida"
                                    data-fabricante="Dräger"
                                    data-modelo="Evita V300"
                                    data-serie="SN-EV300-1198"
                                    data-localizacao="Urgência - Sala 1"
                                    data-estado="Em manutenção">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <!-- =========================================================
     MODAL PARA CONFIRMAR REMOÇÃO DO EQUIPAMENTO
     Abre ao clicar no botão eliminar da tabela.
     Mostra os dados principais e permite cancelar ou confirmar.
     ========================================================= -->
    <div class="modal fade"
        id="modalApagarEquipamento"
        tabindex="-1"
        aria-labelledby="modalApagarEquipamentoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
            <div class="modal-content modal-apagar-equipamento">
                <!-- Cabeçalho do modal -->
                <div class="modal-header modal-remocao-header">
                    <div>
                        <h5 class="modal-title" id="modalApagarEquipamentoLabel">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Confirmar remoção
                        </h5>
                        <p class="modal-remocao-subtitulo">
                            Confirme os dados antes de remover o equipamento.
                        </p>
                    </div>
                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                    </button>
                </div>
                <!-- Corpo do modal -->
                <div class="modal-body modal-remocao-body">
                    <!-- Informação principal do equipamento selecionado -->
                    <div class="modal-resumo-equipamento modal-resumo-remocao">
                        <div class="modal-linha">
                            <strong>Código</strong>
                            <span id="modalApagarCodigo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Equipamento</strong>
                            <span id="modalApagarNome">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Categoria</strong>
                            <span id="modalApagarCategoria">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Fabricante</strong>
                            <span id="modalApagarFabricante">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Modelo</strong>
                            <span id="modalApagarModelo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>N.º Série</strong>
                            <span id="modalApagarSerie">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Localização</strong>
                            <span id="modalApagarLocalizacao">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Estado</strong>
                            <span id="modalApagarEstado">---</span>
                        </div>
                    </div>
                    <!-- Campo escondido usado pelo JavaScript -->
                    <input type="hidden" id="modalApagarIdEquipamento">
                    <p class="texto-confirmacao-remocao">
                        Confirma que pretende remover este equipamento da lista?
                    </p>
                </div>
                <!-- Rodapé do modal com ações -->
                <div class="modal-footer modal-remocao-footer">
                    <button type="button"
                            class="btn btn-cancelar-modal"
                            data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Cancelar
                    </button>
                    <button type="button"
                            class="btn btn-confirmar-remocao"
                            id="btnConfirmarApagarEquipamento">
                        <i class="fa-solid fa-trash me-2"></i>
                        Guardar Alteração
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>

