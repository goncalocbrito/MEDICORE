<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_no_permission('mobilidade');

$pdo = medicore_pdo();
$mensagemSucesso = '';
$mensagemErro = '';
$idUtilizador = $_SESSION['id_utilizador'] ?? null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $acao = $_POST['acao'] ?? 'criar';

        if ($acao === 'criar') {
            $pdo->beginTransaction();

            $idEquipamento = (int) ($_POST['id_equipamento'] ?? 0);
            $idLocalizacaoDestino = (int) ($_POST['id_localizacao_destino'] ?? 0);

            if ($idEquipamento <= 0 || $idLocalizacaoDestino <= 0) {
                throw new Exception('Selecione o equipamento e a localização temporária.');
            }

            $stmtLocalizacaoAtual = $pdo->prepare("
                SELECT id_localizacao
                FROM equipamentos
                WHERE id_equipamento = :id_equipamento
                AND isActive = 1
                FOR UPDATE
            ");
            $stmtLocalizacaoAtual->execute([
                ':id_equipamento' => $idEquipamento
            ]);

            $equipamentoAtual = $stmtLocalizacaoAtual->fetch();

            if (!$equipamentoAtual) {
                throw new Exception('Equipamento não encontrado.');
            }

            $idLocalizacaoOrigem = (int) $equipamentoAtual['id_localizacao'];

            if ($idLocalizacaoOrigem === $idLocalizacaoDestino) {
                throw new Exception('A localização temporária não pode ser igual à localização atual.');
            }

            $stmt = $pdo->prepare("
                INSERT INTO emprestimos_equipamentos (
                    codigo_emprestimo,
                    id_equipamento,
                    id_localizacao_origem,
                    id_localizacao_destino,
                    id_utilizador_pedido,
                    responsavel_emprestimo,
                    motivo,
                    data_inicio,
                    data_prevista_devolucao,
                    observacoes
                ) VALUES (
                    :codigo,
                    :equipamento,
                    :origem,
                    :destino,
                    :utilizador,
                    :responsavel,
                    :motivo,
                    :data_inicio,
                    :data_prevista,
                    :observacoes
                )
            ");

            $stmt->execute([
                ':codigo' => 'EMP-' . date('Ymd-His'),
                ':equipamento' => $idEquipamento,
                ':origem' => $idLocalizacaoOrigem,
                ':destino' => $idLocalizacaoDestino,
                ':utilizador' => $idUtilizador,
                ':responsavel' => trim($_POST['responsavel_emprestimo'] ?? ''),
                ':motivo' => trim($_POST['motivo'] ?? ''),
                ':data_inicio' => $_POST['data_inicio'],
                ':data_prevista' => $_POST['data_prevista_devolucao'],
                ':observacoes' => trim($_POST['observacoes'] ?? '')
            ]);

            $idEmprestimo = $pdo->lastInsertId();

            $pdo->prepare("
                UPDATE equipamentos
                SET id_localizacao = :destino
                WHERE id_equipamento = :equipamento
            ")->execute([
                ':destino' => $idLocalizacaoDestino,
                ':equipamento' => $idEquipamento
            ]);

            $pdo->prepare("
                UPDATE acessorios_equipamento
                SET id_localizacao = :destino
                WHERE id_equipamento = :equipamento
                AND isActive = 1
            ")->execute([
                ':destino' => $idLocalizacaoDestino,
                ':equipamento' => $idEquipamento
            ]);

            $pdo->prepare("
                UPDATE consumiveis
                SET id_localizacao = :destino
                WHERE id_equipamento = :equipamento
                AND isActive = 1
            ")->execute([
                ':destino' => $idLocalizacaoDestino,
                ':equipamento' => $idEquipamento
            ]);

            $pdo->prepare("
                INSERT INTO historico_equipamentos (
                    id_equipamento,
                    id_localizacao,
                    id_utilizador,
                    tipo_evento,
                    referencia_tabela,
                    referencia_id,
                    descricao
                ) VALUES (
                    :equipamento,
                    :localizacao,
                    :utilizador,
                    'emprestimo_iniciado',
                    'emprestimos_equipamentos',
                    :referencia,
                    'Empréstimo iniciado. Equipamento, acessórios e consumíveis associados deslocados temporariamente.'
                )
            ")->execute([
                ':equipamento' => $idEquipamento,
                ':localizacao' => $idLocalizacaoDestino,
                ':utilizador' => $idUtilizador,
                ':referencia' => $idEmprestimo
            ]);

            $pdo->commit();

            $mensagemSucesso = 'Empréstimo registado com sucesso.';
        }

        if ($acao === 'terminar') {
            $idEmprestimo = (int) $_POST['id_emprestimo'];

            $stmt = $pdo->prepare("
                SELECT *
                FROM emprestimos_equipamentos
                WHERE id_emprestimo = :id
                  AND estado = 'ativo'
            ");
            $stmt->execute([':id' => $idEmprestimo]);
            $emprestimo = $stmt->fetch();

            if ($emprestimo) {
                $pdo->beginTransaction();

                $pdo->prepare("
                    UPDATE emprestimos_equipamentos
                    SET estado = 'terminado',
                        data_termino = CURDATE(),
                        id_utilizador_termino = :utilizador
                    WHERE id_emprestimo = :id
                ")->execute([
                    ':utilizador' => $idUtilizador,
                    ':id' => $idEmprestimo
                ]);

                $pdo->prepare("
                    UPDATE equipamentos
                    SET id_localizacao = :origem
                    WHERE id_equipamento = :equipamento
                ")->execute([
                    ':origem' => $emprestimo['id_localizacao_origem'],
                    ':equipamento' => $emprestimo['id_equipamento']
                ]);

                $pdo->prepare("
                    UPDATE acessorios_equipamento
                    SET id_localizacao = :origem
                    WHERE id_equipamento = :equipamento
                    AND isActive = 1
                ")->execute([
                    ':origem' => $emprestimo['id_localizacao_origem'],
                    ':equipamento' => $emprestimo['id_equipamento']
                ]);

                $pdo->prepare("
                    UPDATE consumiveis
                    SET id_localizacao = :origem
                    WHERE id_equipamento = :equipamento
                    AND isActive = 1
                ")->execute([
                    ':origem' => $emprestimo['id_localizacao_origem'],
                    ':equipamento' => $emprestimo['id_equipamento']
                ]);

                $pdo->prepare("
                    INSERT INTO historico_equipamentos (
                        id_equipamento,
                        id_localizacao,
                        id_utilizador,
                        tipo_evento,
                        referencia_tabela,
                        referencia_id,
                        descricao
                    ) VALUES (
                        :equipamento,
                        :localizacao,
                        :utilizador,
                        'emprestimo_terminado',
                        'emprestimos_equipamentos',
                        :referencia,
                        'Empréstimo terminado. Equipamento, acessórios e consumíveis associados devolvidos à localização original.'
                    )
                ")->execute([
                    ':equipamento' => $emprestimo['id_equipamento'],
                    ':localizacao' => $emprestimo['id_localizacao_origem'],
                    ':utilizador' => $idUtilizador,
                    ':referencia' => $idEmprestimo
                ]);

                $pdo->commit();
                $mensagemSucesso = 'Empréstimo terminado com sucesso.';
            }
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $mensagemErro = 'Erro: ' . $e->getMessage();
    }
}


$equipamentos = $pdo->query("
    SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, e.id_localizacao
    FROM equipamentos e
    WHERE e.isActive = 1
    ORDER BY e.codigo_equipamento
")->fetchAll();

$localizacoes = $pdo->query("
    SELECT id_localizacao, codigo, departamento_nome, piso, sala
    FROM localizacoes
    WHERE isActive = 1
    ORDER BY codigo
")->fetchAll();

$emprestimos = $pdo->query("
    SELECT emp.*, e.codigo_equipamento, e.designacao,
           lo.codigo AS origem_codigo,
           ld.codigo AS destino_codigo
    FROM emprestimos_equipamentos emp
    INNER JOIN equipamentos e ON e.id_equipamento = emp.id_equipamento
    INNER JOIN localizacoes lo ON lo.id_localizacao = emp.id_localizacao_origem
    INNER JOIN localizacoes ld ON ld.id_localizacao = emp.id_localizacao_destino
    WHERE emp.isActive = 1
    ORDER BY emp.data_prevista_devolucao ASC
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Empréstimos de Equipamentos</h2>
            <p class="subtitulo-pagina">
                Gestão de deslocações temporárias dos equipamentos hospitalares.
            </p>
        </div>

        <button type="button" class="btn btn-adicionar" data-bs-toggle="modal" data-bs-target="#modalNovoEmprestimo">
            <i class="fa-solid fa-plus me-2"></i> Novo Empréstimo
        </button>
    </div>

    <?php if ($mensagemSucesso): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensagemSucesso); ?></div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($mensagemErro); ?></div>
    <?php endif; ?>

    <div class="table-responsive tabela-container">
        <table id="tabela-emprestimos" class="table table-hover align-middle tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Equipamento</th>
                    <th>Destino</th>
                    <th>Responsável</th>
                    <th>Data devolução</th>
                    <th>Estado</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprestimos as $emprestimo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emprestimo['codigo_emprestimo']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['codigo_equipamento'] . ' - ' . $emprestimo['designacao']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['destino_codigo']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['responsavel_emprestimo']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['data_prevista_devolucao']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['estado']); ?></td>
                        <td class="text-center">
                            <?php if ($emprestimo['estado'] === 'ativo'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="acao" value="terminar">
                                    <input type="hidden" name="id_emprestimo" value="<?php echo $emprestimo['id_emprestimo']; ?>">
                                    <button type="submit" class="btn btn-sm btn-ficha">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                ---
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal fade" id="modalNovoEmprestimo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content modal-acessorio">
            <form method="post">
                <input type="hidden" name="acao" value="criar">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-handshake me-2"></i>
                        Novo Empréstimo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="pesquisaEquipamentoEmprestimo" class="form-label">Equipamento *</label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="pesquisaEquipamentoEmprestimo"
                                    data-hidden-target="idEquipamentoEmprestimo"
                                    data-lista-target="listaEquipamentosEmprestimo"
                                    placeholder="Pesquisar e selecionar equipamento"
                                    autocomplete="off"
                                    required>

                                <input type="hidden"
                                    id="idEquipamentoEmprestimo"
                                    name="id_equipamento"
                                    required>

                                <div class="lista-registos-custom" id="listaEquipamentosEmprestimo">
                                    <?php foreach ($equipamentos as $equipamento): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo htmlspecialchars($equipamento['id_equipamento']); ?>"
                                                data-texto="<?php echo htmlspecialchars($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>">
                                            <span>
                                                <?php echo htmlspecialchars($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="pesquisaLocalizacaoEmprestimo" class="form-label">Localização temporária *</label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="pesquisaLocalizacaoEmprestimo"
                                    data-hidden-target="idLocalizacaoEmprestimo"
                                    data-lista-target="listaLocalizacoesEmprestimo"
                                    placeholder="Pesquisar e selecionar localização"
                                    autocomplete="off"
                                    required>

                                <input type="hidden"
                                    id="idLocalizacaoEmprestimo"
                                    name="id_localizacao_destino"
                                    required>

                                <div class="lista-registos-custom" id="listaLocalizacoesEmprestimo">
                                    <?php foreach ($localizacoes as $localizacao): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo htmlspecialchars($localizacao['id_localizacao']); ?>"
                                                data-texto="<?php echo htmlspecialchars($localizacao['codigo'] . ' - ' . $localizacao['departamento_nome'] . ' - Piso ' . $localizacao['piso'] . ' - Sala ' . $localizacao['sala']); ?>">
                                            <span>
                                                <?php echo htmlspecialchars($localizacao['codigo'] . ' - ' . $localizacao['departamento_nome'] . ' - Piso ' . $localizacao['piso'] . ' - Sala ' . $localizacao['sala']); ?>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Responsável pelo empréstimo *</label>
                            <input type="text" name="responsavel_emprestimo" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data início *</label>
                            <input type="date" name="data_inicio" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data prevista de devolução *</label>
                            <input type="date" name="data_prevista_devolucao" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Motivo</label>
                            <input type="text" name="motivo" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-guardar">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Empréstimo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>