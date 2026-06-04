<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>


    <!-- Conteúdo principal dos processos finalizados. -->
    <main class="conteudo-private">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Processos Finalizados</h2>
                <p class="subtitulo-pagina">
                    Histórico de calibrações e manutenções concluídas nos equipamentos do hospital.
                </p>
            </div>
        </div>
        <!-- Pesquisa e filtros da tabela de processos finalizados. -->
        <section class="filtros-tabela" data-tabela=".tabela-equipamentos" aria-label="Pesquisa e filtros de processos finalizados">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="pesquisaProcessosFinalizados" class="form-label">Pesquisar</label>
                    <input type="search" class="form-control" id="pesquisaProcessosFinalizados" data-filtro="texto" placeholder="Código, equipamento, procedimento ou estado">
                </div>
                                <div class="col-lg-2 col-md-6">
                    <label for="filtroProcedimentoProcessosFinalizados" class="form-label">Procedimento</label>
                    <select class="form-select" id="filtroProcedimentoProcessosFinalizados" data-filtro="coluna" data-coluna="3">
                        <option value="">Todos</option>
                        <option value="Calibração">Calibração</option>
                        <option value="Manutenção preventiva">Preventiva</option>
                        <option value="Manutenção corretiva">Corretiva</option>
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
                        <th>Alvo</th>
                        <th>Associado a</th>
                        <th>Procedimento</th>
                        <th>Data de Conclusão</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tabelaProcessosFinalizados">
                    <tr>
                        <td>EQ-004</td>
                        <td>Monitor de Sinais Vitais</td>
                        <td>Equipamento principal</td>
                        <td><span class="tipo-fornecedor tipo-manutencao">Manutenção preventiva</span></td>
                        <td>15/05/2026</td>
                        <td><span class="estado estado-ativo">Efetuada</span></td>
                    </tr>
                    <tr>
                        <td>EQ-006</td>
                        <td>Oxímetro de Pulso</td>
                        <td>Equipamento principal</td>
                        <td><span class="tipo-localizacao tipo-urgencia">Manutenção corretiva</span></td>
                        <td>20/05/2026</td>
                        <td><span class="estado estado-ativo">Efetuada</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
