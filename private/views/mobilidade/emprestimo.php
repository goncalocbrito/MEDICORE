<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_no_permission('mobilidade');

$pdo = medicore_pdo();
$mensagemSucesso = '';
$mensagemErro = '';
$idUtilizador = $_SESSION['id_utilizador'] ?? null;
$tipoUtilizador = $_SESSION['tipo_utilizador'] ?? '';
$ehAdministrador = $tipoUtilizador === 'Administrador';
$ehEngenheiro = $tipoUtilizador === 'Engenheiro';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $acao = $_POST['acao'] ?? 'criar';

        if ($acao === 'criar') {
            $pdo->beginTransaction();

            $idEquipamento       = (int) ($_POST['id_equipamento'] ?? 0);
            $idLocalizacaoDestino = (int) ($_POST['id_localizacao_destino'] ?? 0);
            $idResponsavel       = (int) ($_POST['id_responsavel_emprestimo'] ?? 0);
            $motivo              = trim($_POST['motivo'] ?? '');
            $dataInicio          = $_POST['data_inicio'] ?? '';
            $dataDevolucao       = $_POST['data_prevista_devolucao'] ?? '';

            if ($idEquipamento <= 0 || $idLocalizacaoDestino <= 0) {
                throw new Exception('Selecione o equipamento e a localização temporária.');
            }

            if ($idResponsavel <= 0) {
                throw new Exception('Selecione o responsável pelo empréstimo.');
            }

            if ($motivo === '') {
                throw new Exception('O campo Motivo é obrigatório.');
            }

            /* Buscar data_aquisicao do equipamento para validação */
            $stmtAq = $pdo->prepare("SELECT data_aquisicao FROM equipamentos WHERE id_equipamento = :id AND isActive = 1");
            $stmtAq->execute([':id' => $idEquipamento]);
            $rowAq = $stmtAq->fetch();
            $dataAquisicao = $rowAq['data_aquisicao'] ?? null;

            if ($dataAquisicao && $dataInicio < $dataAquisicao) {
                throw new Exception('A data de início não pode ser anterior à data de aquisição do equipamento (' . date('d/m/Y', strtotime($dataAquisicao)) . ').');
            }

            if ($dataDevolucao && $dataInicio && $dataDevolucao < $dataInicio) {
                throw new Exception('A data prevista de devolução não pode ser anterior à data de início.');
            }

            /* Buscar nome do responsável */
            $stmtResp = $pdo->prepare("SELECT nome FROM utilizadores WHERE id_utilizador = :id AND tipo_utilizador = 'Engenheiro' AND isActive = 1");
            $stmtResp->execute([':id' => $idResponsavel]);
            $rowResp = $stmtResp->fetch();
            if (!$rowResp) {
                throw new Exception('Responsável não encontrado.');
            }
            $nomeResponsavel = $rowResp['nome'];

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
                    observacoes,
                    estado
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
                    :observacoes,
                    'pendente'
                )
            ");

            $stmt->execute([
                ':codigo' => 'EMP-' . date('Ymd-His'),
                ':equipamento' => $idEquipamento,
                ':origem' => $idLocalizacaoOrigem,
                ':destino' => $idLocalizacaoDestino,
                ':utilizador' => $idUtilizador,
                ':responsavel' => $nomeResponsavel,
                ':motivo' => $motivo,
                ':data_inicio' => $dataInicio,
                ':data_prevista' => $dataDevolucao,
                ':observacoes' => trim($_POST['observacoes'] ?? '')
            ]);

            $idEmprestimo = $pdo->lastInsertId();

            $stmtHistorico = $pdo->prepare("
                INSERT INTO historico_equipamentos (
                    id_equipamento,
                    id_localizacao,
                    id_localizacao_origem,
                    id_localizacao_destino,
                    id_utilizador,
                    tipo_evento,
                    referencia_tabela,
                    referencia_id,
                    descricao,
                    data_evento,
                    isActive
                ) VALUES (
                    :equipamento,
                    :localizacao,
                    :origem,
                    :destino,
                    :utilizador,
                    'emprestimo_pendente',
                    'emprestimos_equipamentos',
                    :referencia,
                    :descricao,
                    NOW(),
                    1
                )
            ");

            $stmtHistorico->execute([
                ':equipamento' => $idEquipamento,
                ':localizacao' => $idLocalizacaoOrigem,
                ':origem' => $idLocalizacaoOrigem,
                ':destino' => $idLocalizacaoDestino,
                ':utilizador' => $idUtilizador,
                ':referencia' => $idEmprestimo,
                ':descricao' => 'Pedido de empréstimo registado e a aguardar aprovação do administrador.'
            ]);

            $pdo->commit();

            $mensagemSucesso = 'Empréstimo registado com sucesso.';
        }

        if ($acao === 'aprovar' && $ehAdministrador) {
            $idEmprestimo = (int) ($_POST['id_emprestimo'] ?? 0);

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT *
                FROM emprestimos_equipamentos
                WHERE id_emprestimo = :id
                AND estado = 'pendente'
                FOR UPDATE
            ");
            $stmt->execute([':id' => $idEmprestimo]);
            $emprestimo = $stmt->fetch();

            if (!$emprestimo) {
                throw new Exception('Pedido de empréstimo não encontrado ou já tratado.');
            }

            $stmt = $pdo->prepare("
                UPDATE emprestimos_equipamentos
                SET estado = 'ativo',
                    id_utilizador_aprovacao = :utilizador,
                    data_aprovacao = NOW()
                WHERE id_emprestimo = :id
            ");
            $stmt->execute([
                ':utilizador' => $_SESSION['id_utilizador'],
                ':id' => $idEmprestimo
            ]);

            $stmt = $pdo->prepare("
                UPDATE equipamentos
                SET id_localizacao = :destino
                WHERE id_equipamento = :id_equipamento
            ");
            $stmt->execute([
                ':destino' => $emprestimo['id_localizacao_destino'],
                ':id_equipamento' => $emprestimo['id_equipamento']
            ]);

            $stmt = $pdo->prepare("
                UPDATE acessorios_equipamento
                SET id_localizacao = :destino
                WHERE id_equipamento = :id_equipamento
                AND isActive = 1
            ");
            $stmt->execute([
                ':destino' => $emprestimo['id_localizacao_destino'],
                ':id_equipamento' => $emprestimo['id_equipamento']
            ]);

            $stmtHistorico = $pdo->prepare("
                INSERT INTO historico_equipamentos (
                    id_equipamento,
                    id_utilizador,
                    tipo_evento,
                    id_localizacao_origem,
                    id_localizacao_destino,
                    descricao
                ) VALUES (
                    :id_equipamento,
                    :id_utilizador,
                    'emprestimo_iniciado',
                    :origem,
                    :destino,
                    :descricao
                )
            ");
            $stmtHistorico->execute([
                ':id_equipamento' => $emprestimo['id_equipamento'],
                ':id_utilizador' => $_SESSION['id_utilizador'],
                ':origem' => $emprestimo['id_localizacao_origem'],
                ':destino' => $emprestimo['id_localizacao_destino'],
                ':descricao' => 'Empréstimo aprovado pelo administrador.'
            ]);

            $pdo->commit();

            header('Location: emprestimo.php?sucesso=aprovado');
            exit;
        }

        if ($acao === 'rejeitar' && $ehAdministrador) {
            $idEmprestimo = (int) ($_POST['id_emprestimo'] ?? 0);

            $stmt = $pdo->prepare("
                UPDATE emprestimos_equipamentos
                SET estado = 'rejeitado',
                    id_utilizador_aprovacao = :utilizador,
                    data_aprovacao = NOW()
                WHERE id_emprestimo = :id
                AND estado = 'pendente'
            ");
            $stmt->execute([
                ':utilizador' => $_SESSION['id_utilizador'],
                ':id' => $idEmprestimo
            ]);

            header('Location: emprestimo.php?sucesso=rejeitado');
            exit;
        }

        if ($acao === 'terminar') {
            if (!$ehEngenheiro) {
                throw new Exception('Apenas o engenheiro pode finalizar empréstimos.');
            }
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
                    INSERT INTO historico_equipamentos (
                        id_equipamento,
                        id_localizacao,
                        id_localizacao_origem,
                        id_localizacao_destino,
                        id_utilizador,
                        tipo_evento,
                        referencia_tabela,
                        referencia_id,
                        descricao,
                        data_evento,
                        isActive
                    ) VALUES (
                        :equipamento,
                        :localizacao,
                        :origem,
                        :destino,
                        :utilizador,
                        'emprestimo_terminado',
                        'emprestimos_equipamentos',
                        :referencia,
                        :descricao,
                        NOW(),
                        1
                    )
                ")->execute([
                    ':equipamento' => $emprestimo['id_equipamento'],
                    ':localizacao' => $emprestimo['id_localizacao_origem'],
                    ':origem' => $emprestimo['id_localizacao_destino'],
                    ':destino' => $emprestimo['id_localizacao_origem'],
                    ':utilizador' => $idUtilizador,
                    ':referencia' => $idEmprestimo,
                    ':descricao' => 'Empréstimo terminado. Equipamento, acessórios e consumíveis associados devolvidos à localização original.'
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
    SELECT
        e.id_equipamento,
        e.codigo_equipamento,
        e.designacao,
        e.id_localizacao,
        e.data_aquisicao,
        CONCAT(l.departamento_nome, ' - Sala ', l.sala) AS localizacao_atual
    FROM equipamentos e
    INNER JOIN localizacoes l
        ON l.id_localizacao = e.id_localizacao
    WHERE e.isActive = 1
    ORDER BY e.codigo_equipamento
")->fetchAll();

$localizacoes = $pdo->query("
    SELECT id_localizacao, departamento_nome, sala
    FROM localizacoes
    WHERE isActive = 1
    ORDER BY departamento_nome, sala
")->fetchAll();

$engenheiros = $pdo->query("
    SELECT id_utilizador, nome, email
    FROM utilizadores
    WHERE isActive = 1
      AND tipo_utilizador = 'Engenheiro'
    ORDER BY nome
")->fetchAll();

$emprestimos = $pdo->query("
    SELECT emp.*, e.codigo_equipamento, e.designacao,
           CONCAT(lo.departamento_nome, ' - Sala ', lo.sala) AS origem_localizacao,
           CONCAT(ld.departamento_nome, ' - Sala ', ld.sala) AS destino_localizacao
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
                <?php
                    $classeEstadoEmprestimo = [
                        'pendente' => 'estado-manutencao',
                        'ativo' => 'estado-ativo',
                        'rejeitado' => 'estado-avariado',
                        'terminado' => 'estado-inativo',
                        'atrasado' => 'estado-avariado'
                    ];
                ?>

                <?php foreach ($emprestimos as $emprestimo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emprestimo['codigo_emprestimo']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['codigo_equipamento'] . ' - ' . $emprestimo['designacao']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['destino_localizacao']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['responsavel_emprestimo']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['data_prevista_devolucao']); ?></td>
                        <td>
                            <span class="estado <?php echo $classeEstadoEmprestimo[$emprestimo['estado']] ?? 'estado-inativo'; ?>">
                                <?php echo htmlspecialchars($emprestimo['estado']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="acoes-operacao">

                                <button type="button"
                                        class="btn-acao-circular btn-acao-detalhe"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalheEmprestimo<?php echo (int) $emprestimo['id_emprestimo']; ?>"
                                        title="Ver detalhes">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <?php if ($ehAdministrador && $emprestimo['estado'] === 'pendente'): ?>

                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="acao" value="aprovar">
                                        <input type="hidden" name="id_emprestimo" value="<?php echo (int) $emprestimo['id_emprestimo']; ?>">

                                        <button type="submit" class="btn-acao-circular btn-acao-aprovar" title="Aprovar empréstimo">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>

                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="acao" value="rejeitar">
                                        <input type="hidden" name="id_emprestimo" value="<?php echo (int) $emprestimo['id_emprestimo']; ?>">

                                        <button type="submit" class="btn-acao-circular btn-acao-rejeitar" title="Reprovar empréstimo">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>

                                <?php elseif ($ehEngenheiro && $emprestimo['estado'] === 'ativo'): ?>

                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="acao" value="terminar">
                                        <input type="hidden" name="id_emprestimo" value="<?php echo (int) $emprestimo['id_emprestimo']; ?>">

                                        <button type="submit" class="btn-acao-circular btn-acao-aprovar" title="Terminar empréstimo">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>

                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>


        <?php foreach ($emprestimos as $emprestimo): ?>
            <div class="modal fade" id="modalDetalheEmprestimo<?php echo (int) $emprestimo['id_emprestimo']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content modal-acessorio">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-eye me-2"></i>
                                Detalhes do Empréstimo
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body">
                            <div class="info-grid">
                                <div>
                                    <label>Código</label>
                                    <p><?php echo htmlspecialchars($emprestimo['codigo_emprestimo']); ?></p>
                                </div>

                                <div>
                                    <label>Equipamento</label>
                                    <p><?php echo htmlspecialchars($emprestimo['codigo_equipamento'] . ' - ' . $emprestimo['designacao']); ?></p>
                                </div>

                                <div>
                                    <label>Origem</label>
                                    <p><?php echo htmlspecialchars($emprestimo['origem_localizacao']); ?></p>
                                </div>

                                <div>
                                    <label>Destino</label>
                                    <p><?php echo htmlspecialchars($emprestimo['destino_localizacao']); ?></p>
                                </div>

                                <div>
                                    <label>Responsável</label>
                                    <p><?php echo htmlspecialchars($emprestimo['responsavel_emprestimo']); ?></p>
                                </div>

                                <div>
                                    <label>Data prevista de devolução</label>
                                    <p><?php echo htmlspecialchars($emprestimo['data_prevista_devolucao']); ?></p>
                                </div>

                                <div>
                                    <label>Estado</label>
                                    <p><?php echo htmlspecialchars($emprestimo['estado']); ?></p>
                                </div>

                                <div>
                                    <label>Motivo</label>
                                    <p><?php echo htmlspecialchars($emprestimo['motivo'] ?? '---'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

<div class="modal fade" id="modalNovoEmprestimo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content modal-acessorio">
            <form method="post" id="formNovoEmprestimo" novalidate>
                <input type="hidden" name="acao" value="criar">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-handshake me-2"></i>
                        Novo Empréstimo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div id="erroNovoEmprestimo" class="alert alert-danger d-none">
                        <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Erro</strong>
                        <ul id="listaErrosEmprestimo" class="mb-0 mt-1"></ul>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="pesquisaEquipamentoEmprestimo" class="form-label">Equipamento *</label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="pesquisaEquipamentoEmprestimo"
                                    data-hidden-target="idEquipamentoEmprestimo"
                                    data-lista-target="listaEquipamentosEmprestimo"
                                    data-localizacao-target="localizacaoAtualEmprestimo"
                                    placeholder="Pesquisar e selecionar equipamento"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idEquipamentoEmprestimo"
                                    name="id_equipamento">

                                <div class="lista-registos-custom" id="listaEquipamentosEmprestimo">
                                    <?php foreach ($equipamentos as $equipamento): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo (int) $equipamento['id_equipamento']; ?>"
                                                data-texto="<?php echo htmlspecialchars($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>"
                                                data-localizacao-atual="<?php echo htmlspecialchars($equipamento['localizacao_atual']); ?>"
                                                data-aquisicao="<?php echo htmlspecialchars($equipamento['data_aquisicao'] ?? ''); ?>">
                                            <span>
                                                <?php echo htmlspecialchars($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="localizacao-atual-box mt-2 d-none" id="localizacaoAtualEmprestimo">
                                <span>Localização atual</span>
                                <strong></strong>
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
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idLocalizacaoEmprestimo"
                                    name="id_localizacao_destino">

                                <div class="lista-registos-custom" id="listaLocalizacoesEmprestimo">
                                    <?php foreach ($localizacoes as $localizacao): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo (int) $localizacao['id_localizacao']; ?>"
                                                data-texto="<?php echo htmlspecialchars($localizacao['departamento_nome'] . ' - Sala ' . $localizacao['sala']); ?>">
                                            <span>
                                                <?php echo htmlspecialchars($localizacao['departamento_nome'] . ' - Sala ' . $localizacao['sala']); ?>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="pesquisaResponsavelEmprestimo" class="form-label">Responsável pelo empréstimo *</label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="pesquisaResponsavelEmprestimo"
                                    data-hidden-target="idResponsavelEmprestimo"
                                    data-lista-target="listaResponsaveisEmprestimo"
                                    placeholder="Pesquisar engenheiro responsável"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idResponsavelEmprestimo"
                                    name="id_responsavel_emprestimo">

                                <div class="lista-registos-custom" id="listaResponsaveisEmprestimo">
                                    <?php foreach ($engenheiros as $eng): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo (int) $eng['id_utilizador']; ?>"
                                                data-texto="<?php echo htmlspecialchars($eng['nome']); ?>">
                                            <span><?php echo htmlspecialchars($eng['nome']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($eng['email']); ?></small>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data início *</label>
                            <input type="date" id="dataInicioEmprestimo" name="data_inicio" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data prevista de devolução *</label>
                            <input type="date" id="dataDevolucaoEmprestimo" name="data_prevista_devolucao" class="form-control">
                        </div>

                        <div class="col-12">
                            <label for="motivoEmprestimo" class="form-label">Motivo *</label>
                            <input type="text" id="motivoEmprestimo" name="motivo" class="form-control" maxlength="255" placeholder="Indique o motivo do empréstimo">
                            <small class="texto-ajuda-form contador-caracteres" data-target="motivoEmprestimo" data-max="255">0 / 255 caracteres</small>
                        </div>

                        <div class="col-12">
                            <label for="observacoesEmprestimo" class="form-label">Observações</label>
                            <textarea id="observacoesEmprestimo" name="observacoes" class="form-control" rows="3" maxlength="500" placeholder="Informações adicionais (opcional)"></textarea>
                            <small class="texto-ajuda-form contador-caracteres" data-target="observacoesEmprestimo" data-max="500">0 / 500 caracteres</small>
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