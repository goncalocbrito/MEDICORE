<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

    <!-- Página de gestão de acessórios por equipamento. -->
    <main class="conteudo-private">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Gestão de Acessórios</h2>
                <p class="subtitulo-pagina">Pesquise um equipamento, consulte os acessórios associados e faça a gestão dos acessórios necessários.</p>
            </div>
        </div>

        <!-- Pesquisa do equipamento usado para carregar os acessórios. -->
        <section class="filtros-tabela" aria-label="Pesquisa de equipamento para acessórios">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label for="seletorEquipamentoAcessorios" class="form-label">Equipamento</label>
                    <select class="form-select" id="seletorEquipamentoAcessorios">
                        <option value="EQ-001">EQ-001 - Monitor Multiparamétrico</option>
                        <option value="EQ-002">EQ-002 - Ventilador Pulmonar</option>
                        <option value="EQ-003">EQ-003 - Desfibrilhador</option>
                    </select>
                </div>

                <div class="col-lg-5 col-md-6">
                    <label for="pesquisaAcessoriosEquipamento" class="form-label">Pesquisar acessórios</label>
                    <input type="search" class="form-control" id="pesquisaAcessoriosEquipamento" placeholder="Código, nome, tipo, série ou intervenção">
                </div>

                <div class="col-lg-2 col-md-12">
                    <button type="button" class="btn btn-limpar-filtros w-100" id="btnLimparPesquisaAcessorios">
                        <i class="fa-solid fa-rotate-left me-2"></i> Limpar
                    </button>
                </div>
            </div>
        </section>

        <!-- Tabela dos acessórios associados ao equipamento pesquisado. -->
        <div class="tabela-container">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <h5 class="subtitulo-bloco-form mb-0">Acessórios associados</h5>

                <button type="button"
                        class="btn btn-adicionar"
                        id="btnAbrirModalNovoAcessorio"
                        data-bs-toggle="modal"
                        data-bs-target="#modalAcessorio">
                    <i class="fa-solid fa-plus me-2"></i>
                    Adicionar Acessório
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle tabela-acessorios">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Acessório</th>
                            <th>Tipo</th>
                            <th>N.º Série</th>
                            <th>Estado</th>
                            <th>Verificação metrológica</th>
                            <th>Próxima Intervenção</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaGestaoAcessorios">
                        <!-- Preenchido pelo JavaScript de acordo com o equipamento selecionado. -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal usado para adicionar, observar e editar acessórios do equipamento selecionado. -->
    <div class="modal fade" id="modalAcessorio" tabindex="-1" aria-labelledby="modalAcessorioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalAcessorioLabel">
                            <i class="fa-solid fa-plug-circle-bolt me-2"></i>
                            Adicionar Acessório
                        </h5>
                        <p class="modal-remocao-subtitulo text-muted mb-0">O acessório fica associado ao equipamento atualmente selecionado.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="modalAcessorioIndice">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="modalAcessorioEquipamento" class="form-label">Equipamento principal</label>
                            <input type="text" class="form-control" id="modalAcessorioEquipamento" readonly>
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioCodigo" class="form-label">Código do acessório</label>
                            <input type="text" class="form-control" id="modalAcessorioCodigo" placeholder="ACC-005">
                        </div>

                        <div class="col-md-8">
                            <label for="modalAcessorioNome" class="form-label">Nome do acessório</label>
                            <input type="text" class="form-control" id="modalAcessorioNome" placeholder="Sensor SpO2">
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioTipo" class="form-label">Tipo</label>
                            <select class="form-select" id="modalAcessorioTipo">
                                <option value="">Selecionar</option>
                                <option value="Sensor">Sensor</option>
                                <option value="Cabo">Cabo</option>
                                <option value="Módulo">Módulo</option>
                                <option value="Consumível reutilizável">Consumível reutilizável</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioFabricante" class="form-label">Fabricante</label>
                            <input type="text" class="form-control" id="modalAcessorioFabricante" placeholder="Philips">
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioModelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modalAcessorioModelo" placeholder="Modelo do acessório">
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioSerie" class="form-label">N.º Série</label>
                            <input type="text" class="form-control" id="modalAcessorioSerie" placeholder="SN-ACC-0001">
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioEstado" class="form-label">Estado</label>
                            <select class="form-select" id="modalAcessorioEstado">
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                                <option value="Avariado">Avariado</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioVerificacao" class="form-label">Requer verificação metrológica</label>
                            <select class="form-select" id="modalAcessorioVerificacao">
                                <option value="Não">Não</option>
                                <option value="Sim">Sim</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="modalAcessorioProximaIntervencao" class="form-label">Próxima intervenção</label>
                            <input type="date" class="form-control" id="modalAcessorioProximaIntervencao">
                        </div>

                        <div class="col-md-8">
                            <label for="modalAcessorioObservacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="modalAcessorioObservacoes" rows="3" placeholder="Notas relevantes sobre o acessório"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-adicionar" id="btnGuardarAcessorioModal">
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Guardar Acessório
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação para remover acessórios, seguindo o padrão de remoção dos equipamentos. -->
    <div class="modal fade" id="modalEliminarAcessorio" tabindex="-1" aria-labelledby="modalEliminarAcessorioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
            <div class="modal-content modal-apagar-equipamento">
                <div class="modal-header modal-remocao-header">
                    <div>
                        <h5 class="modal-title" id="modalEliminarAcessorioLabel">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Confirmar remoção
                        </h5>
                        <p class="modal-remocao-subtitulo">Confirme os dados antes de remover o acessório.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body modal-remocao-body">
                    <div class="modal-resumo-equipamento modal-resumo-remocao">
                        <div class="modal-linha">
                            <strong>Código</strong>
                            <span id="modalEliminarAcessorioCodigo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Acessório</strong>
                            <span id="modalEliminarAcessorioNome">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Equipamento principal</strong>
                            <span id="modalEliminarAcessorioEquipamento">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Tipo</strong>
                            <span id="modalEliminarAcessorioTipo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>N.º Série</strong>
                            <span id="modalEliminarAcessorioSerie">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Estado</strong>
                            <span id="modalEliminarAcessorioEstado">---</span>
                        </div>
                    </div>

                    <input type="hidden" id="modalEliminarAcessorioIndice">

                    <p class="texto-confirmacao-remocao">
                        Confirma que pretende remover este acessório da lista?
                    </p>
                </div>

                <div class="modal-footer modal-remocao-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-confirmar-remocao" id="btnConfirmarEliminarAcessorio">
                        <i class="fa-solid fa-trash me-2"></i>
                        Remover Acessório
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

