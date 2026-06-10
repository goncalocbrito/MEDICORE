<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

require_once __DIR__ . '/../../../config/config.php';

$fornecedores = [];

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$stmt = $pdo->prepare("
    SELECT *
    FROM fornecedores
    WHERE isActive = 1
    ORDER BY id_fornecedor ASC
");
$stmt->execute();
$fornecedores = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
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
            <a href="novo_fornecedor.php" class="btn btn-adicionar">
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
                    <select class="form-select" id="filtroTipoFornecedores" data-filtro="coluna" data-coluna="1">
                        <option value="">Todos</option>
                        <option value="Fabricante">Fabricante</option>
                        <option value="Comercial">Comercial</option>
                        <option value="Manutenção">Manutenção</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroLocalidadeFornecedores" class="form-label">Localidade</label>
                    <select class="form-select" id="filtroLocalidadeFornecedores" data-filtro="coluna" data-coluna="2">
                        <option value="">Todas</option>
                        <option value="Porto">Porto</option>
                        <option value="Lisboa">Lisboa</option>
                        <option value="Coimbra">Coimbra</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="filtroEstadoFornecedores" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstadoFornecedores" data-filtro="coluna" data-coluna="3">
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
                        <th>Fornecedor</th>
                        <th>Tipo</th>
                        <th>Localidade</th>
                        <th>Estado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php if (empty($fornecedores)): ?>
                    <tr class="linha-sem-resultados">
                        <td colspan="5" class="text-center text-muted">
                            Nenhum fornecedor ativo encontrado.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($fornecedores as $fornecedor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fornecedor['nome_empresa']); ?></td>
                        <td><?php echo htmlspecialchars($fornecedor['tipo_fornecedor']); ?></td>
                        <td><?php echo htmlspecialchars($fornecedor['localidade']); ?></td>
                        <td><span class="estado estado-ativo">Ativo</span></td>
                        <td class="text-center">
                            <a href="ficha_fornecedor.php?id=<?php echo $fornecedor['id_fornecedor']; ?>"
                            class="btn btn-sm btn-ficha">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>

                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-fornecedor"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarFornecedor"
                                    data-id="<?php echo $fornecedor['id_fornecedor']; ?>"
                                    data-codigo="<?php echo htmlspecialchars($fornecedor['id_fornecedor']); ?>"
                                    data-nome="<?php echo htmlspecialchars($fornecedor['nome_empresa']); ?>"
                                    data-tipo="<?php echo htmlspecialchars($fornecedor['tipo_fornecedor']); ?>"
                                    data-nif="<?php echo htmlspecialchars($fornecedor['nif']); ?>"
                                    data-email="<?php echo htmlspecialchars($fornecedor['email']); ?>"
                                    data-telefone="<?php echo htmlspecialchars($fornecedor['telefone']); ?>"
                                    data-localidade="<?php echo htmlspecialchars($fornecedor['localidade']); ?>"
                                    data-estado="Ativo">
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
         MODAL PARA CONFIRMAR REMOCAO DO FORNECEDOR
         Envia um POST para apagar_fornecedor.php, que muda isActive para 0.
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
                            Confirmar remocao
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
                    </div>

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

                    <form action="apagar_fornecedor.php" method="post">
                        <input type="hidden" name="id_fornecedor" id="modalApagarIdFornecedor">

                        <button type="submit" class="btn btn-confirmar-remocao">
                            <i class="fa-solid fa-trash me-2"></i>
                            Guardar Alteracao
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
