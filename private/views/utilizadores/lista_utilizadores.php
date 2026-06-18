<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function utilizador_sessao()
{
    return $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema';
}

function classe_tipo_utilizador($tipo)
{
    return [
        'Administrador' => 'tipo-administrador',
        'Engenheiro' => 'tipo-engenheiro',
        'Enfermeiro' => 'tipo-enfermeiro'
    ][$tipo] ?? 'tipo-engenheiro';
}

function classe_estado_utilizador($estado)
{
    return $estado === 'Ativo'
        ? 'estado-ativo'
        : ($estado === 'Pendente' ? 'estado-manutencao' : 'estado-inativo');
}

$mensagemSucesso = '';
$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'desativar_utilizador') {
    $idUtilizador = (int) ($_POST['id_utilizador'] ?? 0);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE id_utilizador = :id LIMIT 1");
        $stmt->execute([':id' => $idUtilizador]);
        $utilizador = $stmt->fetch();

        if (!$utilizador) {
            throw new Exception('Utilizador não encontrado.');
        }

        $stmt = $pdo->prepare("
            UPDATE utilizadores
            SET isActive = 0,
                estado = 'Inativo',
                atualizado_por = :atualizado_por
            WHERE id_utilizador = :id
        ");
        $stmt->execute([
            ':atualizado_por' => utilizador_sessao(),
            ':id' => $idUtilizador
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO historico_utilizadores (
                id_utilizador_alvo, codigo_utilizador, acao, campo_alterado,
                valor_anterior, valor_novo, observacoes, realizado_por
            ) VALUES (
                :id_utilizador_alvo, :codigo_utilizador, 'remocao_utilizador', 'isActive',
                '1', '0', :observacoes, :realizado_por
            )
        ");
        $stmt->execute([
            ':id_utilizador_alvo' => $idUtilizador,
            ':codigo_utilizador' => $utilizador['codigo_utilizador'],
            ':observacoes' => 'Utilizador desativado logicamente na lista de utilizadores.',
            ':realizado_por' => utilizador_sessao()
        ]);

        $pdo->commit();
        $mensagemSucesso = 'Utilizador removido da lista com sucesso.';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $mensagemErro = 'Erro ao remover utilizador: ' . $e->getMessage();
    }
}

$stmt = $pdo->query("
    SELECT
        u.*,
        (
            SELECT COUNT(*)
            FROM utilizadores_permissoes up
            WHERE up.id_utilizador = u.id_utilizador
              AND up.isActive = 1
        ) AS total_permissoes_ativas
    FROM utilizadores u
    WHERE u.isActive = 1
    ORDER BY u.nome ASC
");
$utilizadores = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Gestão de Utilizadores</h2>
            <p class="subtitulo-pagina">Consulta, criação e gestão dos acessos dos utilizadores do sistema.</p>
        </div>

        <a href="novo_utilizador.php" class="btn btn-adicionar">
            <i class="fa-solid fa-plus me-2"></i> Adicionar Utilizador
        </a>
    </div>

    <?php if ($mensagemSucesso): ?>
        <div class="alert alert-success rounded-4 fw-bold"><?php echo h($mensagemSucesso); ?></div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger rounded-4 fw-bold"><?php echo h($mensagemErro); ?></div>
    <?php endif; ?>

    <div class="table-responsive tabela-container">
        <table id="tabela-utilizadores" class="table table-hover align-middle tabela-utilizadores tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Utilizador</th>
                    <th>Tipo</th>
                    <th>Serviço</th>
                    <th>Email</th>
                    <th>Acessos</th>
                    <th>Estado</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($utilizadores as $utilizador): ?>
                    <tr>
                        <td>
                            <strong><?php echo h($utilizador['nome']); ?></strong><br>
                            <small class="text-muted"><?php echo h($utilizador['codigo_utilizador']); ?></small>
                        </td>
                        <td>
                            <span class="tipo-utilizador <?php echo h(classe_tipo_utilizador($utilizador['tipo_utilizador'])); ?>">
                                <?php echo h($utilizador['tipo_utilizador']); ?>
                            </span>
                        </td>
                        <td><?php echo h($utilizador['departamento'] ?: '---'); ?></td>
                        <td><?php echo h($utilizador['email']); ?></td>
                        <td><?php echo h($utilizador['total_permissoes_ativas']); ?></td>
                        <td>
                            <span class="estado <?php echo h(classe_estado_utilizador($utilizador['estado'])); ?>">
                                <?php echo h($utilizador['estado']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="ficha_utilizador.php?ref=<?php echo url_ref($utilizador['id_utilizador']); ?>" class="btn btn-sm btn-ficha" title="Abrir ficha">
                                <i class="fa-solid fa-file-lines"></i>
                            </a>

                            <button type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-utilizador"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarUtilizador"
                                    data-id="<?php echo h($utilizador['id_utilizador']); ?>"
                                    data-codigo="<?php echo h($utilizador['codigo_utilizador']); ?>"
                                    data-nome="<?php echo h($utilizador['nome']); ?>"
                                    data-tipo="<?php echo h($utilizador['tipo_utilizador']); ?>"
                                    data-cartao="<?php echo h($utilizador['cartao_cidadao']); ?>"
                                    data-email="<?php echo h($utilizador['email']); ?>"
                                    data-telefone="<?php echo h($utilizador['telefone']); ?>"
                                    data-servico="<?php echo h($utilizador['departamento']); ?>"
                                    data-estado="<?php echo h($utilizador['estado']); ?>"
                                    title="Remover utilizador">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal fade" id="modalApagarUtilizador" tabindex="-1" aria-labelledby="modalApagarUtilizadorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
        <div class="modal-content modal-apagar-equipamento">
            <div class="modal-header modal-remocao-header">
                <div>
                    <h5 class="modal-title" id="modalApagarUtilizadorLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Remover utilizador
                    </h5>
                    <p class="modal-remocao-subtitulo">Confirme os dados antes de remover o utilizador.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body modal-remocao-body">
                <div class="modal-resumo-equipamento modal-resumo-remocao">
                    <div class="modal-linha"><strong>Código</strong><span id="modalApagarUtilizadorCodigo">---</span></div>
                    <div class="modal-linha"><strong>Nome</strong><span id="modalApagarUtilizadorNome">---</span></div>
                    <div class="modal-linha"><strong>Tipo</strong><span id="modalApagarUtilizadorTipo">---</span></div>
                    <div class="modal-linha"><strong>N.º CC</strong><span id="modalApagarUtilizadorCartao">---</span></div>
                    <div class="modal-linha"><strong>Email</strong><span id="modalApagarUtilizadorEmail">---</span></div>
                    <div class="modal-linha"><strong>Telefone</strong><span id="modalApagarUtilizadorTelefone">---</span></div>
                    <div class="modal-linha"><strong>Serviço</strong><span id="modalApagarUtilizadorServico">---</span></div>
                    <div class="modal-linha"><strong>Estado</strong><span id="modalApagarUtilizadorEstado">---</span></div>
                </div>

                <p class="texto-confirmacao-remocao">Confirma que pretende remover este utilizador da lista?</p>
            </div>

            <div class="modal-footer modal-remocao-footer">
                <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i> Cancelar
                </button>

                <form method="post" action="lista_utilizadores.php">
                    <input type="hidden" name="acao" value="desativar_utilizador">
                    <input type="hidden" name="id_utilizador" id="modalApagarIdUtilizador">

                    <button type="submit" class="btn btn-confirmar-remocao">
                        <i class="fa-solid fa-trash me-2"></i> Guardar Alteração
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
