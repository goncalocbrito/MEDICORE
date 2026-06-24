<?php
require_once __DIR__ . '/../../includes/funcoes.php';

function classe_tipo_fornecedor($tipo)
{
    $tipoNormalizado = strtolower(trim($tipo));

    $tipoNormalizado = str_replace(
        ['ç', 'ã', 'á', 'à', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú'],
        ['c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u'],
        $tipoNormalizado
    );

    switch ($tipoNormalizado) {
        case 'fabricante':
            return 'tipo-fabricante';

        case 'comercial':
            return 'tipo-comercial';

        case 'manutencao':
            return 'tipo-manutencao';

        default:
            return '';
    }
}

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

        <div class="table-responsive tabela-container">
            <table id="tabela-fornecedores" class="table table-hover align-middle tabela-fornecedores">
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
                    <?php foreach ($fornecedores as $fornecedor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fornecedor['nome_empresa']); ?></td>
                            <td>
                                <span class="tipo-fornecedor <?php echo classe_tipo_fornecedor($fornecedor['tipo_fornecedor']); ?>">
                                    <?php echo htmlspecialchars($fornecedor['tipo_fornecedor']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($fornecedor['localidade']); ?></td>
                            <td><span class="estado estado-ativo">Ativo</span></td>
                            <td class="text-center">
                                <a href="ficha_fornecedor.php?ref=<?php echo url_ref($fornecedor['id_fornecedor']); ?>"
                                class="btn btn-sm btn-ficha">
                                    <i class="fa-solid fa-file-lines"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-eliminar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalApagarFornecedor<?php echo (int)$fornecedor['id_fornecedor']; ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                                <!-- Modal inline por fornecedor -->
                                <div class="modal fade" id="modalApagarFornecedor<?php echo (int)$fornecedor['id_fornecedor']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-remocao-dialog">
                                        <div class="modal-content modal-apagar-equipamento">
                                            <div class="modal-header modal-remocao-header">
                                                <div>
                                                    <h5 class="modal-title">
                                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                                        Confirmar remoção
                                                    </h5>
                                                    <p class="modal-remocao-subtitulo">Confirme os dados antes de remover o fornecedor.</p>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body modal-remocao-body">
                                                <div class="modal-resumo-equipamento modal-resumo-remocao">
                                                    <div class="modal-linha"><strong>Fornecedor</strong><span><?php echo htmlspecialchars($fornecedor['nome_empresa']); ?></span></div>
                                                    <div class="modal-linha"><strong>Tipo</strong><span><?php echo htmlspecialchars($fornecedor['tipo_fornecedor']); ?></span></div>
                                                    <div class="modal-linha"><strong>NIF</strong><span><?php echo htmlspecialchars($fornecedor['nif']); ?></span></div>
                                                    <div class="modal-linha"><strong>Email</strong><span><?php echo htmlspecialchars($fornecedor['email_fornecedor'] ?? '---'); ?></span></div>
                                                    <div class="modal-linha"><strong>Localidade</strong><span><?php echo htmlspecialchars($fornecedor['localidade'] ?? '---'); ?></span></div>
                                                </div>
                                                <p class="texto-confirmacao-remocao">Confirma que pretende remover este fornecedor da lista?</p>
                                            </div>
                                            <div class="modal-footer modal-remocao-footer">
                                                <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                                                    <i class="fa-solid fa-xmark me-2"></i> Cancelar
                                                </button>
                                                <form action="apagar_fornecedor.php" method="post">
                                                    <input type="hidden" name="id_fornecedor" value="<?php echo (int)$fornecedor['id_fornecedor']; ?>">
                                                    <button type="submit" class="btn btn-confirmar-remocao">
                                                        <i class="fa-solid fa-trash me-2"></i> Guardar Alteração
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
