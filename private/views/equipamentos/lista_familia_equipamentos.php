<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

require_once __DIR__ . '/../../../config/config.php';

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$mensagemErro = '';

/* =========================================================
   EDITAR FAMÍLIA
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'editar') {
    $idFamilia = $_POST['idFamilia'] ?? null;
    $codigoFamilia = trim($_POST['codigoFamilia'] ?? '');
    $nomeFamilia = trim($_POST['nomeFamilia'] ?? '');
    $descricaoFamilia = trim($_POST['descricaoFamilia'] ?? '');

    if ($codigoFamilia !== '' && is_numeric($codigoFamilia)) {
        $codigoFamilia = str_pad((int) $codigoFamilia, 2, '0', STR_PAD_LEFT);
    }

    if ($idFamilia && is_numeric($idFamilia) && $codigoFamilia !== '' && $nomeFamilia !== '') {
        try {
            $stmtEditar = $pdo->prepare("
                UPDATE familias_equipamento
                SET
                    codigo_familia = :codigo_familia,
                    nome = :nome,
                    descricao = :descricao
                WHERE id_familia_equipamento = :id_familia
                  AND isActive = 1
            ");

            $stmtEditar->execute([
                ':codigo_familia' => $codigoFamilia,
                ':nome' => $nomeFamilia,
                ':descricao' => $descricaoFamilia !== '' ? $descricaoFamilia : null,
                ':id_familia' => $idFamilia
            ]);

            header('Location: lista_familia_equipamentos.php?atualizado=1');
            exit;

        } catch (PDOException $e) {
            $mensagemErro = 'Não foi possível atualizar a família. Verifica se o código já existe.';
        }
    } else {
        $mensagemErro = 'Preenche todos os campos obrigatórios da família.';
    }
}

/* =========================================================
   APAGAR FAMÍLIA — DELETE LÓGICO
   Só permite apagar se não existirem equipamentos ativos
   associados a essa família.
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'apagar') {
    $idFamilia = $_POST['idFamilia'] ?? null;

    if ($idFamilia && is_numeric($idFamilia)) {
        $stmtContar = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM equipamentos
            WHERE id_familia_equipamento = :id_familia
              AND isActive = 1
        ");

        $stmtContar->execute([
            ':id_familia' => $idFamilia
        ]);

        $resultado = $stmtContar->fetch();

        if ((int) $resultado['total'] > 0) {
            $mensagemErro = 'Não é possível remover esta família porque existem equipamentos ativos associados.';
        } else {
            $stmtApagar = $pdo->prepare("
                UPDATE familias_equipamento
                SET isActive = 0
                WHERE id_familia_equipamento = :id_familia
            ");

            $stmtApagar->execute([
                ':id_familia' => $idFamilia
            ]);

            header('Location: lista_familia_equipamentos.php?apagado=1');
            exit;
        }
    }
}

/* =========================================================
   LISTAGEM DAS FAMÍLIAS
   Conta quantos equipamentos ativos pertencem a cada família.
   ========================================================= */
$stmt = $pdo->prepare("
    SELECT
        f.id_familia_equipamento,
        f.codigo_familia,
        f.nome,
        f.descricao,
        f.isActive,
        COUNT(e.id_equipamento) AS total_equipamentos
    FROM familias_equipamento f
    LEFT JOIN equipamentos e
        ON e.id_familia_equipamento = f.id_familia_equipamento
       AND e.isActive = 1
    WHERE f.isActive = 1
    GROUP BY
        f.id_familia_equipamento,
        f.codigo_familia,
        f.nome,
        f.descricao,
        f.isActive
    ORDER BY f.codigo_familia ASC
");

$stmt->execute();
$familias = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Famílias de Equipamentos</h2>
            <p class="subtitulo-pagina">
                Gestão das famílias usadas para gerar automaticamente os códigos dos equipamentos.
            </p>
        </div>

        <a href="nova_familia_equipamentos.php" class="btn btn-adicionar">
            <i class="fa-solid fa-plus me-2"></i> Adicionar Família
        </a>
    </div>

    <?php if (isset($_GET['criado'])): ?>
        <div class="form-alerta-sucesso" role="alert">
            <strong>
                <i class="fa-solid fa-circle-check me-2"></i>
                Família criada com sucesso.
            </strong>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['apagado'])): ?>
        <div class="form-alerta-sucesso" role="alert">
            <strong>
                <i class="fa-solid fa-circle-check me-2"></i>
                Família removida com sucesso.
            </strong>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['atualizado'])): ?>
        <div class="form-alerta-sucesso" role="alert">
            <strong>
                <i class="fa-solid fa-circle-check me-2"></i>
                Família atualizada com sucesso.
            </strong>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagemErro)): ?>
        <div class="form-alerta-erros" role="alert">
            <strong>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Não foi possível remover a família.
            </strong>

            <p class="mb-0 mt-2">
                <?php echo htmlspecialchars($mensagemErro); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="table-responsive tabela-container">
        <table class="table table-hover align-middle tabela-familias-equipamentos">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Família</th>
                    <th>Descrição</th>
                    <th>Equipamentos Associados</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($familias)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Não existem famílias de equipamentos registadas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($familias as $familia): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($familia['codigo_familia']); ?>
                                </strong>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($familia['nome']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($familia['descricao'] ?? '-'); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($familia['total_equipamentos']); ?>
                            </td>

                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-ficha btn-editar-familia"
                                        title="Editar família"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditarFamilia"
                                        data-id="<?php echo htmlspecialchars($familia['id_familia_equipamento']); ?>"
                                        data-codigo="<?php echo htmlspecialchars($familia['codigo_familia']); ?>"
                                        data-nome="<?php echo htmlspecialchars($familia['nome']); ?>"
                                        data-descricao="<?php echo htmlspecialchars($familia['descricao'] ?? ''); ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <form action="lista_familia_equipamentos.php"
                                      method="post"
                                      class="d-inline"
                                      onsubmit="return confirm('Tem a certeza que pretende remover esta família?');">

                                    <input type="hidden" name="acao" value="apagar">
                                    <input type="hidden"
                                           name="idFamilia"
                                           value="<?php echo htmlspecialchars($familia['id_familia_equipamento']); ?>">

                                    <button type="submit"
                                            class="btn btn-sm btn-eliminar"
                                            title="Eliminar família">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade"
        id="modalEditarFamilia"
        tabindex="-1"
        aria-labelledby="modalEditarFamiliaLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-medicore">

                <form action="lista_familia_equipamentos.php" method="post">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="idFamilia" id="modalIdFamilia">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarFamiliaLabel">
                            <i class="fa-solid fa-layer-group me-2"></i>
                            Editar Família de Equipamentos
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modalCodigoFamilia" class="form-label">Código da Família *</label>
                            <input type="text"
                                class="form-control"
                                id="modalCodigoFamilia"
                                name="codigoFamilia"
                                maxlength="2"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="modalNomeFamilia" class="form-label">Nome da Família *</label>
                            <input type="text"
                                class="form-control"
                                id="modalNomeFamilia"
                                name="nomeFamilia"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="modalDescricaoFamilia" class="form-label">Descrição</label>
                            <textarea class="form-control"
                                    id="modalDescricaoFamilia"
                                    name="descricaoFamilia"
                                    rows="5"></textarea>
                        </div>

                        <small class="text-muted">
                            Esta família será usada para gerar automaticamente os códigos dos equipamentos.
                        </small>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-cancelar"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-guardar">
                            <i class="fa-solid fa-floppy-disk me-2"></i>
                            Guardar Alterações
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>