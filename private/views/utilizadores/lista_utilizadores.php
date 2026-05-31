<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

    <!-- =========================================================
         CONTEÚDO PRINCIPAL DA LISTA DE UTILIZADORES
         Mostra a equipa registada no sistema e as ações disponíveis.
         ========================================================= -->
    <main class="conteudo-private">
        <!-- =====================================================
             TÍTULO E AÇÃO PRINCIPAL
             Mantém o mesmo padrão usado nas listas dos outros módulos.
             ===================================================== -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Gestão de Utilizadores</h2>
                <p class="subtitulo-pagina">
                    Consulta, criação e gestão dos acessos dos administradores, engenheiros e enfermeiros.
                </p>
            </div>
            <a href="novo_utilizador.html" class="btn btn-adicionar">
                <i class="fa-solid fa-plus me-2"></i> Adicionar Utilizador
            </a>
        </div>
        <!-- =====================================================
             TABELA DE UTILIZADORES
             Cada linha apresenta a identificação essencial e permite
             editar ou abrir o modal de remoção do utilizador.
             ===================================================== -->
        <!-- Pesquisa e filtros da tabela de utilizadores. -->
        <section class="filtros-tabela" data-tabela=".tabela-utilizadores" aria-label="Pesquisa e filtros de utilizadores">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="pesquisaUtilizadores" class="form-label">Pesquisar</label>
                    <input type="search" class="form-control" id="pesquisaUtilizadores" data-filtro="texto" placeholder="Código, nome, tipo, serviço ou estado">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroTipoUtilizadores" class="form-label">Tipo</label>
                    <select class="form-select" id="filtroTipoUtilizadores" data-filtro="coluna" data-coluna="2">
                        <option value="">Todos</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Engenheiro">Engenheiro</option>
                        <option value="Enfermeiro">Enfermeiro</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroServicoUtilizadores" class="form-label">Serviço</label>
                    <select class="form-select" id="filtroServicoUtilizadores" data-filtro="coluna" data-coluna="3">
                        <option value="">Todos</option>
                        <option value="Administração">Administração</option>
                        <option value="Engenharia Biomédica">Engenharia Biomédica</option>
                        <option value="Unidade de Cuidados Intensivos">UCI</option>
                        <option value="Bloco Operatório">Bloco Operatório</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroEstadoUtilizadores" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstadoUtilizadores" data-filtro="coluna" data-coluna="4">
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
            <table class="table table-hover align-middle tabela-utilizadores">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Serviço</th>
                        <th>Estado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>USR-001</td>
                        <td>Ana Martins</td>
                        <td><span class="tipo-utilizador tipo-administrador">Administrador</span></td>
                        <td>Administração</td>
                        <td><span class="estado estado-ativo">Ativo</span></td>
                        <td class="text-center">
                            <a href="ficha_utilizador.html?id=USR-001" class="btn btn-sm btn-ficha" title="Abrir ficha do utilizador">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-utilizador" title="Eliminar utilizador" data-bs-toggle="modal" data-bs-target="#modalApagarUtilizador" data-codigo="USR-001" data-nome="Ana Martins" data-tipo="Administrador" data-cartao="12345678" data-email="ana.martins@medicore.pt" data-telefone="+351 220 000 100" data-servico="Administração" data-estado="Ativo">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>USR-002</td>
                        <td>Gonçalo Brito</td>
                        <td><span class="tipo-utilizador tipo-engenheiro">Engenheiro</span></td>
                        <td>Engenharia Biomédica</td>
                        <td><span class="estado estado-ativo">Ativo</span></td>
                        <td class="text-center">
                            <a href="ficha_utilizador.html?id=USR-002" class="btn btn-sm btn-ficha" title="Abrir ficha do utilizador">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-utilizador" title="Eliminar utilizador" data-bs-toggle="modal" data-bs-target="#modalApagarUtilizador" data-codigo="USR-002" data-nome="Gonçalo Brito" data-tipo="Engenheiro" data-cartao="87654321" data-email="g.brito@medicore.pt" data-telefone="+351 220 000 200" data-servico="Engenharia Biomédica" data-estado="Ativo">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>USR-003</td>
                        <td>Maria Costa</td>
                        <td><span class="tipo-utilizador tipo-enfermeiro">Enfermeiro</span></td>
                        <td>Unidade de Cuidados Intensivos</td>
                        <td><span class="estado estado-ativo">Ativo</span></td>
                        <td class="text-center">
                            <a href="ficha_utilizador.html?id=USR-003" class="btn btn-sm btn-ficha" title="Abrir ficha do utilizador">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-utilizador" title="Eliminar utilizador" data-bs-toggle="modal" data-bs-target="#modalApagarUtilizador" data-codigo="USR-003" data-nome="Maria Costa" data-tipo="Enfermeiro" data-cartao="23456789" data-email="maria.costa@medicore.pt" data-telefone="+351 220 000 300" data-servico="Unidade de Cuidados Intensivos" data-estado="Ativo">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>USR-004</td>
                        <td>Ricardo Silva</td>
                        <td><span class="tipo-utilizador tipo-enfermeiro">Enfermeiro</span></td>
                        <td>Bloco Operatório</td>
                        <td><span class="estado estado-inativo">Inativo</span></td>
                        <td class="text-center">
                            <a href="ficha_utilizador.html?id=USR-004" class="btn btn-sm btn-ficha" title="Abrir ficha do utilizador">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-utilizador" title="Eliminar utilizador" data-bs-toggle="modal" data-bs-target="#modalApagarUtilizador" data-codigo="USR-004" data-nome="Ricardo Silva" data-tipo="Enfermeiro" data-cartao="34567890" data-email="ricardo.silva@medicore.pt" data-telefone="+351 220 000 301" data-servico="Bloco Operatório" data-estado="Inativo">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <!-- =========================================================
         MODAL DE REMOÇÃO DE UTILIZADOR
         Confirma a eliminação antes de remover visualmente a linha.
         ========================================================= -->
    <div class="modal fade" id="modalApagarUtilizador" tabindex="-1" aria-labelledby="modalApagarUtilizadorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
            <div class="modal-content modal-apagar-equipamento">
                <div class="modal-header modal-remocao-header">
                    <div>
                        <h5 class="modal-title" id="modalApagarUtilizadorLabel">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> Remover utilizador
                        </h5>
                        <p class="modal-remocao-subtitulo">
                            Confirme os dados antes de remover o utilizador.
                        </p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body modal-remocao-body">
                    <input type="hidden" id="modalApagarIdUtilizador">
                    <div class="modal-resumo-remocao">
                        <div class="modal-linha">
                            <strong>Código</strong>
                            <span id="modalApagarUtilizadorCodigo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Nome</strong>
                            <span id="modalApagarUtilizadorNome">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Tipo</strong>
                            <span id="modalApagarUtilizadorTipo">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Nº CC</strong>
                            <span id="modalApagarUtilizadorCartao">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Email</strong>
                            <span id="modalApagarUtilizadorEmail">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Telefone</strong>
                            <span id="modalApagarUtilizadorTelefone">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Serviço</strong>
                            <span id="modalApagarUtilizadorServico">---</span>
                        </div>
                        <div class="modal-linha">
                            <strong>Estado</strong>
                            <span id="modalApagarUtilizadorEstado">---</span>
                        </div>
                    </div>
                    <p class="texto-confirmacao-remocao">
                        Confirma que pretende remover este utilizador da lista?
                    </p>
                </div>
                <div class="modal-footer modal-remocao-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-confirmar-remocao" id="btnConfirmarApagarUtilizador">
                        <i class="fa-solid fa-trash me-2"></i> Remover Utilizador
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
