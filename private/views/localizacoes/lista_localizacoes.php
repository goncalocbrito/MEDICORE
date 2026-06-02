<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>


    <!-- Conteúdo principal da lista de localizações. -->
    <main class="conteudo-private">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Gestão de Localizações</h2>
                <p class="subtitulo-pagina">
                    Consulta, registo e acompanhamento das localizações hospitalares associadas aos equipamentos médicos.
                </p>
            </div>
            <a href="nova_localizacao.php" class="btn btn-adicionar">
                <i class="fa-solid fa-plus me-2"></i> Adicionar Localização
            </a>
        </div>
        <!-- Tabela principal. Cada linha abre ficha ou modal de remoção. -->
        <!-- Pesquisa e filtros da tabela de localizações. -->
        <section class="filtros-tabela" data-tabela=".tabela-localizacoes" aria-label="Pesquisa e filtros de localizações">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="pesquisaLocalizacoes" class="form-label">Pesquisar</label>
                    <input type="search" class="form-control" id="pesquisaLocalizacoes" data-filtro="texto" placeholder="Código, serviço, edifício, piso ou sala">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroEdificioLocalizacoes" class="form-label">Edifício</label>
                    <select class="form-select" id="filtroEdificioLocalizacoes" data-filtro="coluna" data-coluna="2">
                        <option value="">Todos</option>
                        <option value="Edifício A">Edifício A</option>
                        <option value="Edifício B">Edifício B</option>
                        <option value="Edifício C">Edifício C</option>
                        <option value="Edifício D">Edifício D</option>
                        <option value="Edifício Técnico">Edifício Técnico</option>
                    </select>
                </div>
                                <div class="col-lg-2 col-md-6">
                    <label for="filtroEstadoLocalizacoes" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstadoLocalizacoes" data-filtro="coluna" data-coluna="5">
                        <option value="">Todos</option>
                        <option value="Ativa">Ativa</option>
                        <option value="Em manutenção">Em manutenção</option>
                        <option value="Inativa">Inativa</option>
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
            <table class="table table-hover align-middle tabela-localizacoes">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Departamento / Serviço</th>
                        <th>Edifício</th>
                        <th>Piso</th>
                        <th>Sala</th>
                        <th>Estado</th>
                        <th>Equipamentos</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>LOC-001</td>
                        <td>Unidade de Cuidados Intensivos</td>
                        <td>Edifício A</td>
                        <td>2</td>
                        <td>Sala 201</td>
                        <td><span class="estado estado-ativo">Ativa</span></td>
                        <td>8</td>
                        <td class="text-center">
                            <a href="ficha_localizacao.php?id=LOC-001" class="btn btn-sm btn-ficha" title="Abrir ficha da localização">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-localizacao" title="Eliminar localização" data-bs-toggle="modal" data-bs-target="#modalApagarLocalizacao" data-codigo="LOC-001" data-departamento="Unidade de Cuidados Intensivos" data-edificio="Edifício A" data-piso="2" data-sala="Sala 201" data-tipo="UCI" data-responsavel="Enf. Maria Costa" data-estado="Ativa" data-equipamentos="8">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>LOC-002</td>
                        <td>Urgência</td>
                        <td>Edifício B</td>
                        <td>0</td>
                        <td>Sala 1</td>
                        <td><span class="estado estado-ativo">Ativa</span></td>
                        <td>12</td>
                        <td class="text-center">
                            <a href="ficha_localizacao.php?id=LOC-002" class="btn btn-sm btn-ficha" title="Abrir ficha da localização">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-localizacao" title="Eliminar localização" data-bs-toggle="modal" data-bs-target="#modalApagarLocalizacao" data-codigo="LOC-002" data-departamento="Urgência" data-edificio="Edifício B" data-piso="0" data-sala="Sala 1" data-tipo="Urgência" data-responsavel="Dr. João Martins" data-estado="Ativa" data-equipamentos="12">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>LOC-003</td>
                        <td>Bloco Operatório</td>
                        <td>Edifício C</td>
                        <td>1</td>
                        <td>BO-02</td>
                        <td><span class="estado estado-ativo">Ativa</span></td>
                        <td>6</td>
                        <td class="text-center">
                            <a href="ficha_localizacao.php?id=LOC-003" class="btn btn-sm btn-ficha" title="Abrir ficha da localização">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-localizacao" title="Eliminar localização" data-bs-toggle="modal" data-bs-target="#modalApagarLocalizacao" data-codigo="LOC-003" data-departamento="Bloco Operatório" data-edificio="Edifício C" data-piso="1" data-sala="BO-02" data-tipo="Bloco Operatório" data-responsavel="Enf. Ricardo Silva" data-estado="Ativa" data-equipamentos="6">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>LOC-004</td>
                        <td>Laboratório Clínico</td>
                        <td>Edifício D</td>
                        <td>1</td>
                        <td>Lab-105</td>
                        <td><span class="estado estado-manutencao">Em manutenção</span></td>
                        <td>10</td>
                        <td class="text-center">
                            <a href="ficha_localizacao.php?id=LOC-004" class="btn btn-sm btn-ficha" title="Abrir ficha da localização">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-localizacao" title="Eliminar localização" data-bs-toggle="modal" data-bs-target="#modalApagarLocalizacao" data-codigo="LOC-004" data-departamento="Laboratório Clínico" data-edificio="Edifício D" data-piso="1" data-sala="Lab-105" data-tipo="Laboratório" data-responsavel="Téc. Ana Ferreira" data-estado="Em manutenção" data-equipamentos="10">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>LOC-005</td>
                        <td>Armazém Técnico</td>
                        <td>Edifício Técnico</td>
                        <td>-1</td>
                        <td>ARM-01</td>
                        <td><span class="estado estado-inativo">Inativa</span></td>
                        <td>4</td>
                        <td class="text-center">
                            <a href="ficha_localizacao.php?id=LOC-005" class="btn btn-sm btn-ficha" title="Abrir ficha da localização">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-localizacao" title="Eliminar localização" data-bs-toggle="modal" data-bs-target="#modalApagarLocalizacao" data-codigo="LOC-005" data-departamento="Armazém Técnico" data-edificio="Edifício Técnico" data-piso="-1" data-sala="ARM-01" data-tipo="Armazém Técnico" data-responsavel="Eng. Gonçalo Brito" data-estado="Inativa" data-equipamentos="4">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <!-- Modal de confirmação de remoção da localização. -->
    <div class="modal fade" id="modalApagarLocalizacao" tabindex="-1" aria-labelledby="modalApagarLocalizacaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
            <div class="modal-content modal-apagar-equipamento">
                <div class="modal-header modal-remocao-header">
                    <div>
                        <h5 class="modal-title" id="modalApagarLocalizacaoLabel">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Confirmar remoção
                        </h5>
                        <p class="modal-remocao-subtitulo">Confirme os dados antes de remover a localização.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body modal-remocao-body">
                    <div class="modal-resumo-equipamento modal-resumo-remocao">
                        <div class="modal-linha"><strong>Código</strong><span id="modalApagarLocalizacaoCodigo">---</span></div>
                        <div class="modal-linha"><strong>Departamento</strong><span id="modalApagarLocalizacaoDepartamento">---</span></div>
                        <div class="modal-linha"><strong>Edifício</strong><span id="modalApagarLocalizacaoEdificio">---</span></div>
                        <div class="modal-linha"><strong>Piso</strong><span id="modalApagarLocalizacaoPiso">---</span></div>
                        <div class="modal-linha"><strong>Sala</strong><span id="modalApagarLocalizacaoSala">---</span></div>
                        <div class="modal-linha"><strong>Tipo</strong><span id="modalApagarLocalizacaoTipo">---</span></div>
                        <div class="modal-linha"><strong>Responsável</strong><span id="modalApagarLocalizacaoResponsavel">---</span></div>
                        <div class="modal-linha"><strong>Estado</strong><span id="modalApagarLocalizacaoEstado">---</span></div>
                        <div class="modal-linha"><strong>Equipamentos</strong><span id="modalApagarLocalizacaoEquipamentos">---</span></div>
                    </div>
                    <input type="hidden" id="modalApagarIdLocalizacao">
                    <p class="texto-confirmacao-remocao">
                        Confirma que pretende remover esta localização da lista?
                    </p>
                </div>
                <div class="modal-footer modal-remocao-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-confirmar-remocao" id="btnConfirmarApagarLocalizacao">
                        <i class="fa-solid fa-trash me-2"></i> Guardar Alteração
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
