<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   CONSULTA DOS EQUIPAMENTOS
   Vai buscar os equipamentos ativos à base de dados.
   ========================================================= */

$equipamentos = [];
$erro_bd = '';

function classeEstadoEquipamento($estado)
{
    $estado = strtolower($estado);

    if (str_contains($estado, 'manut')) {
        return 'estado-manutencao';
    }

    if (str_contains($estado, 'avari')) {
        return 'estado-avariado';
    }

    return 'estado-ativo';
}

try {
    $pdo = new PDO(
        'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $sql = "
        SELECT
            e.id_equipamento,
            e.codigo,
            e.designacao,
            e.fabricante,
            e.modelo,
            e.numero_serie,
            ce.nome AS categoria,
            ee.nome AS estado,
            CONCAT(l.departamento, ' - ', l.sala) AS localizacao
        FROM equipamentos e
        INNER JOIN categorias_equipamento ce
            ON ce.id_categoria_equipamento = e.id_categoria_equipamento
        INNER JOIN estados_equipamento ee
            ON ee.id_estado_equipamento = e.id_estado_equipamento
        INNER JOIN localizacoes l
            ON l.id_localizacao = e.id_localizacao
        WHERE e.isActive = 1
        ORDER BY e.codigo ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $equipamentos = $stmt->fetchAll();

} catch (PDOException $e) {
    $erro_bd = 'Erro ao carregar equipamentos da base de dados.';
}

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
                    <?php if (!empty($erro_bd)): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                <?php echo htmlspecialchars($erro_bd); ?>
                            </td>
                        </tr>

                    <?php elseif (empty($equipamentos)): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                Não existem equipamentos registados.
                            </td>
                        </tr>

                    <?php else: ?>
                        <?php foreach ($equipamentos as $equipamento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($equipamento['codigo']); ?></td>

                                <td><?php echo htmlspecialchars($equipamento['designacao']); ?></td>

                                <td><?php echo htmlspecialchars($equipamento['categoria']); ?></td>

                                <td><?php echo htmlspecialchars($equipamento['localizacao']); ?></td>

                                <td>
                                    <span class="estado <?php echo classeEstadoEquipamento($equipamento['estado']); ?>">
                                        <?php echo htmlspecialchars($equipamento['estado']); ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a href="ficha_equipamento.php?id=<?php echo urlencode($equipamento['id_equipamento']); ?>"
                                    class="btn btn-sm btn-ficha"
                                    title="Abrir ficha do equipamento">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-sm btn-eliminar btn-abrir-modal-apagar"
                                            title="Eliminar equipamento"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalApagarEquipamento"
                                            data-codigo="<?php echo htmlspecialchars($equipamento['codigo']); ?>"
                                            data-nome="<?php echo htmlspecialchars($equipamento['designacao']); ?>"
                                            data-categoria="<?php echo htmlspecialchars($equipamento['categoria']); ?>"
                                            data-fabricante="<?php echo htmlspecialchars($equipamento['fabricante']); ?>"
                                            data-modelo="<?php echo htmlspecialchars($equipamento['modelo']); ?>"
                                            data-serie="<?php echo htmlspecialchars($equipamento['numero_serie']); ?>"
                                            data-localizacao="<?php echo htmlspecialchars($equipamento['localizacao']); ?>"
                                            data-estado="<?php echo htmlspecialchars($equipamento['estado']); ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

