<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

    <!-- Página de registo dos consumíveis associados a equipamentos. -->
    <main class="conteudo-private">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Consumíveis por Equipamento</h2>
                <p class="subtitulo-pagina">Registo dos materiais consumíveis utilizados diretamente nos equipamentos médicos.</p>
            </div>
        </div>
        <!-- Filtros da tabela por equipamento, categoria e texto livre. -->
        <section class="filtros-tabela" data-tabela="#tabelaConsumiveisEquipamento">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="pesquisaConsumiveis" class="form-label">Pesquisar</label>
                    <input type="search" class="form-control" id="pesquisaConsumiveis" data-filtro="texto" placeholder="Pesquisar por consumível, equipamento ou código">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="filtroEquipamentoConsumiveis" class="form-label">Equipamento</label>
                    <select class="form-select" id="filtroEquipamentoConsumiveis" data-filtro="coluna" data-coluna="2">
                        <option value="">Todos</option>
                        <option value="EQ-001 - Monitor Multiparamétrico">EQ-001 - Monitor Multiparamétrico</option>
                        <option value="EQ-002 - Ventilador Pulmonar">EQ-002 - Ventilador Pulmonar</option>
                        <option value="EQ-003 - Desfibrilhador">EQ-003 - Desfibrilhador</option>
                        <option value="EQ-004 - Eletrocardiógrafo">EQ-004 - Eletrocardiógrafo</option>
                        <option value="EQ-005 - Ecógrafo">EQ-005 - Ecógrafo</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="filtroCategoriaConsumiveis" class="form-label">Categoria</label>
                    <select class="form-select" id="filtroCategoriaConsumiveis" data-filtro="coluna" data-coluna="3">
                        <option value="">Todas</option>
                        <option value="Elétrodos">Elétrodos</option>
                        <option value="Papel técnico">Papel técnico</option>
                        <option value="Filtros">Filtros</option>
                        <option value="Circuitos descartáveis">Circuitos descartáveis</option>
                        <option value="Gel e contacto">Gel e contacto</option>
                        <option value="Sensores descartáveis">Sensores descartáveis</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <button type="button" class="btn btn-limpar-filtros w-100" data-limpar-filtros>
                        <i class="fa-solid fa-eraser me-2"></i> Limpar
                    </button>
                </div>
            </div>
        </section>
        <!-- Tabela principal com os consumíveis registados por equipamento. -->
        <div class="tabela-container">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <h5 class="subtitulo-bloco-form mb-0">Consumíveis associados a equipamentos</h5>
                <button type="button"
                        class="btn btn-adicionar"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNovoConsumivel">
                    <i class="fa-solid fa-plus me-2"></i>
                    Adicionar Consumível
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle tabela-equipamentos" id="tabelaConsumiveisEquipamento">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Consumível</th>
                            <th>Equipamento</th>
                            <th>Categoria</th>
                            <th>Quantidade</th>
                            <th>Stock Mínimo</th>
                            <th>Estado</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoConsumiveisEquipamento">
                        <tr>
                            <td>CON-001</td>
                            <td>Elétrodos descartáveis ECG</td>
                            <td>EQ-004 - Eletrocardiógrafo</td>
                            <td>Elétrodos</td>
                            <td>120 unidades</td>
                            <td>30 unidades</td>
                            <td><span class="estado estado-ativo">Disponível</span></td>
                        </tr>
                        <tr>
                            <td>CON-002</td>
                            <td>Papel térmico ECG</td>
                            <td>EQ-004 - Eletrocardiógrafo</td>
                            <td>Papel técnico</td>
                            <td>8 rolos</td>
                            <td>4 rolos</td>
                            <td><span class="estado estado-ativo">Disponível</span></td>
                        </tr>
                        <tr>
                            <td>CON-003</td>
                            <td>Filtros bacterianos descartáveis</td>
                            <td>EQ-002 - Ventilador Pulmonar</td>
                            <td>Filtros</td>
                            <td>16 unidades</td>
                            <td>10 unidades</td>
                            <td><span class="estado estado-ativo">Disponível</span></td>
                        </tr>
                        <tr>
                            <td>CON-004</td>
                            <td>Circuito respiratório descartável</td>
                            <td>EQ-002 - Ventilador Pulmonar</td>
                            <td>Circuitos descartáveis</td>
                            <td>5 unidades</td>
                            <td>6 unidades</td>
                            <td><span class="estado estado-manutencao">Stock baixo</span></td>
                        </tr>
                        <tr>
                            <td>CON-005</td>
                            <td>Gel condutor para ecografia</td>
                            <td>EQ-005 - Ecógrafo</td>
                            <td>Gel e contacto</td>
                            <td>2 frascos</td>
                            <td>3 frascos</td>
                            <td><span class="estado estado-manutencao">Stock baixo</span></td>
                        </tr>
                        <tr>
                            <td>CON-006</td>
                            <td>Sensores SpO2 descartáveis</td>
                            <td>EQ-001 - Monitor Multiparamétrico</td>
                            <td>Sensores descartáveis</td>
                            <td>6 unidades</td>
                            <td>3 unidades</td>
                            <td><span class="estado estado-ativo">Disponível</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <!-- Modal para registar novos consumíveis associados a equipamentos. -->
    <div class="modal fade" id="modalNovoConsumivel" tabindex="-1" aria-labelledby="modalNovoConsumivelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalNovoConsumivelLabel">
                            <i class="fa-solid fa-boxes-stacked me-2"></i>
                            Adicionar Consumível
                        </h5>
                        <p class="modal-remocao-subtitulo text-muted mb-0">Registe um consumível utilizado diretamente por um equipamento.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="formNovoConsumivelEquipamento">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="novoConsumivelCodigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="novoConsumivelCodigo" placeholder="CON-007" required>
                            </div>
                            <div class="col-md-8">
                                <label for="novoConsumivelItem" class="form-label">Consumível</label>
                                <input type="text" class="form-control" id="novoConsumivelItem" placeholder="Elétrodos descartáveis ECG" required>
                            </div>
                            <div class="col-md-6">
                                <label for="novoConsumivelEquipamento" class="form-label">Equipamento associado</label>
                                <select class="form-select" id="novoConsumivelEquipamento" required>
                                    <option value="">Selecionar equipamento</option>
                                    <option value="EQ-001 - Monitor Multiparamétrico">EQ-001 - Monitor Multiparamétrico</option>
                                    <option value="EQ-002 - Ventilador Pulmonar">EQ-002 - Ventilador Pulmonar</option>
                                    <option value="EQ-003 - Desfibrilhador">EQ-003 - Desfibrilhador</option>
                                    <option value="EQ-004 - Eletrocardiógrafo">EQ-004 - Eletrocardiógrafo</option>
                                    <option value="EQ-005 - Ecógrafo">EQ-005 - Ecógrafo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="novoConsumivelCategoria" class="form-label">Categoria</label>
                                <select class="form-select" id="novoConsumivelCategoria" required>
                                    <option value="">Selecionar categoria</option>
                                    <option value="Elétrodos">Elétrodos</option>
                                    <option value="Papel técnico">Papel técnico</option>
                                    <option value="Filtros">Filtros</option>
                                    <option value="Circuitos descartáveis">Circuitos descartáveis</option>
                                    <option value="Gel e contacto">Gel e contacto</option>
                                    <option value="Sensores descartáveis">Sensores descartáveis</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="novoConsumivelQuantidade" class="form-label">Quantidade</label>
                                <input type="number" min="0" class="form-control" id="novoConsumivelQuantidade" value="1" required>
                            </div>
                            <div class="col-md-4">
                                <label for="novoConsumivelUnidade" class="form-label">Unidade</label>
                                <input type="text" class="form-control" id="novoConsumivelUnidade" placeholder="unidades" value="unidades" required>
                            </div>
                            <div class="col-md-4">
                                <label for="novoConsumivelStockMinimo" class="form-label">Stock mínimo</label>
                                <input type="number" min="0" class="form-control" id="novoConsumivelStockMinimo" value="1" required>
                            </div>
                            <div class="col-md-12">
                                <label for="novoConsumivelObservacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="novoConsumivelObservacoes" rows="3" placeholder="Notas sobre reposição, validade ou utilização"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-adicionar">
                            <i class="fa-solid fa-floppy-disk me-2"></i>
                            Guardar Consumível
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>

