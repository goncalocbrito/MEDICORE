<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

    <!-- =========================================================
         CONTEÚDO PRINCIPAL DA LISTA DE FORNECEDORES
         Mostra a tabela de fornecedores e o botão para criar novo registo.
         ========================================================= -->
    <main class="conteudo-private">
        <!-- =====================================================
             TÍTULO E AÇÃO PRINCIPAL
             Mantém o mesmo padrão visual da lista de equipamentos.
             ===================================================== -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Gestão de Fornecedores</h2>
                <p class="subtitulo-pagina">
                    Consulta, registo e acompanhamento dos fornecedores associados aos equipamentos médicos.
                </p>
            </div>
            <a href="novo_fornecedor.html" class="btn btn-adicionar">
                <i class="fa-solid fa-plus me-2"></i> Adicionar Fornecedor
            </a>
        </div>
        <!-- =====================================================
             TABELA DE FORNECEDORES
             Cada linha tem um botão para abrir a ficha e outro para
             abrir o modal de remoção com os dados preenchidos.
             ===================================================== -->
        <!-- Pesquisa e filtros da tabela de fornecedores. -->
        <section class="filtros-tabela" data-tabela=".tabela-fornecedores" aria-label="Pesquisa e filtros de fornecedores">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="pesquisaFornecedores" class="form-label">Pesquisar</label>
                    <input type="search" class="form-control" id="pesquisaFornecedores" data-filtro="texto" placeholder="Código, fornecedor, tipo, localidade ou estado">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroTipoFornecedores" class="form-label">Tipo</label>
                    <select class="form-select" id="filtroTipoFornecedores" data-filtro="coluna" data-coluna="2">
                        <option value="">Todos</option>
                        <option value="Fabricante">Fabricante</option>
                        <option value="Distribuidor">Distribuidor</option>
                        <option value="Manutenção">Manutenção</option>
                        <option value="Calibração">Calibração</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroLocalidadeFornecedores" class="form-label">Localidade</label>
                    <select class="form-select" id="filtroLocalidadeFornecedores" data-filtro="coluna" data-coluna="3">
                        <option value="">Todas</option>
                        <option value="Porto">Porto</option>
                        <option value="Lisboa">Lisboa</option>
                        <option value="Coimbra">Coimbra</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroEstadoFornecedores" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstadoFornecedores" data-filtro="coluna" data-coluna="4">
                        <option value="">Todos</option>
                        <option value="Ativo">Ativo</option>
                        <option value="Inativo">Inativo</option>
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
            <table class="table table-hover align-middle tabela-fornecedores">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fornecedor</th>
                        <th>Tipo</th>
                        <th>Localidade</th>
                        <th>Estado</th>
                        <th>Equipamentos</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>FOR-001</td>
                        <td>Philips Medical Systems</td>
                        <td><span class="tipo-fornecedor tipo-fabricante">Fabricante</span></td>
                        <td>Porto</td>
                        <td><span class="estado estado-ativo">Ativo</span></td>
                        <td>12</td>
                        <td class="text-center">
                            <a href="ficha_fornecedor.html?id=FOR-001" class="btn btn-sm btn-ficha" title="Abrir ficha do fornecedor">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-fornecedor"
                                    title="Eliminar fornecedor"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarFornecedor"
                                    data-codigo="FOR-001"
                                    data-nome="Philips Medical Systems"
                                    data-tipo="Fabricante"
                                    data-nif="509123456"
                                    data-email="suporte@philips-med.pt"
                                    data-telefone="+351 220 000 111"
                                    data-localidade="Porto"
                                    data-estado="Ativo"
                                    data-equipamentos="12">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>FOR-002</td>
                        <td>MedSupply Portugal</td>
                        <td><span class="tipo-fornecedor tipo-distribuidor">Distribuidor</span></td>
                        <td>Lisboa</td>
                        <td><span class="estado estado-ativo">Ativo</span></td>
                        <td>8</td>
                        <td class="text-center">
                            <a href="ficha_fornecedor.html?id=FOR-002" class="btn btn-sm btn-ficha" title="Abrir ficha do fornecedor">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-fornecedor"
                                    title="Eliminar fornecedor"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarFornecedor"
                                    data-codigo="FOR-002"
                                    data-nome="MedSupply Portugal"
                                    data-tipo="Distribuidor"
                                    data-nif="514987321"
                                    data-email="comercial@medsupply.pt"
                                    data-telefone="+351 221 234 567"
                                    data-localidade="Lisboa"
                                    data-estado="Ativo"
                                    data-equipamentos="8">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>FOR-003</td>
                        <td>Biomedical Solutions</td>
                        <td><span class="tipo-fornecedor tipo-manutencao">Manutenção</span></td>
                        <td>Maia</td>
                        <td><span class="estado estado-ativo">Ativo</span></td>
                        <td>5</td>
                        <td class="text-center">
                            <a href="ficha_fornecedor.html?id=FOR-003" class="btn btn-sm btn-ficha" title="Abrir ficha do fornecedor">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-fornecedor"
                                    title="Eliminar fornecedor"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarFornecedor"
                                    data-codigo="FOR-003"
                                    data-nome="Biomedical Solutions"
                                    data-tipo="Manutenção"
                                    data-nif="507654789"
                                    data-email="tecnica@biomedicalsolutions.pt"
                                    data-telefone="+351 222 456 789"
                                    data-localidade="Maia"
                                    data-estado="Ativo"
                                    data-equipamentos="5">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>FOR-004</td>
                        <td>CalibraMed</td>
                        <td><span class="tipo-fornecedor tipo-calibracao">Calibração</span></td>
                        <td>Braga</td>
                        <td><span class="estado estado-inativo">Inativo</span></td>
                        <td>3</td>
                        <td class="text-center">
                            <a href="ficha_fornecedor.html?id=FOR-004" class="btn btn-sm btn-ficha" title="Abrir ficha do fornecedor">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-fornecedor"
                                    title="Eliminar fornecedor"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarFornecedor"
                                    data-codigo="FOR-004"
                                    data-nome="CalibraMed"
                                    data-tipo="Calibração"
                                    data-nif="515321987"
                                    data-email="calibracao@calibramed.pt"
                                    data-telefone="+351 223 987 654"
                                    data-localidade="Braga"
                                    data-estado="Inativo"
                                    data-equipamentos="3">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <!-- =========================================================
         MODAL PARA CONFIRMAR REMOÇÃO DO FORNECEDOR
         Abre ao clicar no botão eliminar e mostra os dados principais
         antes de confirmar a remoção visual da linha.
         ========================================================= -->
    <div class="modal fade"
         id="modalApagarFornecedor"
         tabindex="-1"
         aria-labelledby="modalApagarFornecedorLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
            <div class="modal-content modal-apagar-equipamento">
                <div class="modal-header modal-remocao-header">
                    <div>
                        <h5 class="modal-title" id="modalApagarFornecedorLabel">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Confirmar remoção
                        </h5>
                        <p class="modal-remocao-subtitulo">
                            Confirme os dados antes de remover o fornecedor.
                        </p>
                    </div>
                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                    </button>
                </div>
                <div class="modal-body modal-remocao-body">
                    <div class="modal-resumo-equipamento modal-resumo-remocao">
                        <div class="modal-linha">
                            <strong>Código</strong>
                            <span id="modalApagarFornecedorCodigo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Fornecedor</strong>
                            <span id="modalApagarFornecedorNome">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Tipo</strong>
                            <span id="modalApagarFornecedorTipo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>NIF</strong>
                            <span id="modalApagarFornecedorNif">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Email</strong>
                            <span id="modalApagarFornecedorEmail">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Telefone</strong>
                            <span id="modalApagarFornecedorTelefone">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Localidade</strong>
                            <span id="modalApagarFornecedorLocalidade">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Estado</strong>
                            <span id="modalApagarFornecedorEstado">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Equipamentos</strong>
                            <span id="modalApagarFornecedorEquipamentos">---</span>
                        </div>
                    </div>
                    <input type="hidden" id="modalApagarIdFornecedor">
                    <p class="texto-confirmacao-remocao">
                        Confirma que pretende remover este fornecedor da lista?
                    </p>
                </div>
                <div class="modal-footer modal-remocao-footer">
                    <button type="button"
                            class="btn btn-cancelar-modal"
                            data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Cancelar
                    </button>
                    <button type="button"
                            class="btn btn-confirmar-remocao"
                            id="btnConfirmarApagarFornecedor">
                        <i class="fa-solid fa-trash me-2"></i>
                        Guardar Alteração
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>