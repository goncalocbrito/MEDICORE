<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_no_permission('mobilidade');

$pdo = medicore_pdo();
$mensagemSucesso = '';
$mensagemErro = '';

$idUtilizador = $_SESSION['id_utilizador'] ?? null;
$tipoUtilizador = $_SESSION['tipo_utilizador'] ?? '';
$isAdmin = $tipoUtilizador === 'Administrador';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $acao = $_POST['acao'] ?? 'criar';

        if ($acao === 'criar') {
            $idEquipamento = (int) ($_POST['id_equipamento'] ?? 0);
            $idLocalizacaoDestino = (int) ($_POST['id_localizacao_destino'] ?? 0);

            $motivo = trim($_POST['motivo'] ?? '');

            if ($idEquipamento <= 0 || $idLocalizacaoDestino <= 0) {
                throw new Exception('Selecione o equipamento e a localização de destino.');
            }

            if ($motivo === '') {
                throw new Exception('O campo Motivo é obrigatório.');
            }
            
            $stmtLocalizacaoAtual = $pdo->prepare("
                SELECT id_localizacao
                FROM equipamentos
                WHERE id_equipamento = :id_equipamento
                AND isActive = 1
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
                throw new Exception('A localização de destino não pode ser igual à localização atual.');
            }
            $stmt = $pdo->prepare("
                INSERT INTO transferencias_equipamentos (
                    codigo_transferencia,
                    id_equipamento,
                    id_localizacao_origem,
                    id_localizacao_destino,
                    id_utilizador_pedido,
                    motivo,
                    observacoes
                ) VALUES (
                    :codigo,
                    :id_equipamento,
                    :origem,
                    :destino,
                    :utilizador,
                    :motivo,
                    :observacoes
                )
            ");

            $stmt->execute([
                ':codigo' => 'TRF-' . date('Ymd-His'),
                ':id_equipamento' => $idEquipamento,
                ':origem' => $idLocalizacaoOrigem,
                ':destino' => $idLocalizacaoDestino,
                ':utilizador' => $idUtilizador,
                ':motivo' => $motivo,
                ':observacoes' => trim($_POST['observacoes'] ?? '')
            ]);

            $idTransferencia = (int) $pdo->lastInsertId();

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
                    'transferencia_pendente',
                    'transferencias_equipamentos',
                    :referencia,
                    :descricao,
                    NOW(),
                    1
                )
            ")->execute([
                ':equipamento' => $idEquipamento,
                ':localizacao' => $idLocalizacaoOrigem,
                ':origem' => $idLocalizacaoOrigem,
                ':destino' => $idLocalizacaoDestino,
                ':utilizador' => $idUtilizador,
                ':referencia' => $idTransferencia,
                ':descricao' => 'Pedido de transferência registado e a aguardar aprovação.'
            ]);

            $mensagemSucesso = 'Pedido de transferência registado com sucesso.';
        }

        if ($acao === 'aprovar' && $isAdmin) {
            $idTransferencia = (int) $_POST['id_transferencia'];

            $stmt = $pdo->prepare("
                SELECT *
                FROM transferencias_equipamentos
                WHERE id_transferencia = :id
                  AND estado = 'pendente'
                  AND isActive = 1
            ");
            $stmt->execute([':id' => $idTransferencia]);
            $transferencia = $stmt->fetch();

            if ($transferencia) {
                $pdo->beginTransaction();

                $pdo->prepare("
                    UPDATE transferencias_equipamentos
                    SET estado = 'aprovado',
                        id_utilizador_aprovacao = :utilizador,
                        data_aprovacao = NOW()
                    WHERE id_transferencia = :id
                ")->execute([
                    ':utilizador' => $idUtilizador,
                    ':id' => $idTransferencia
                ]);

                $pdo->prepare("
                    UPDATE equipamentos
                    SET id_localizacao = :destino
                    WHERE id_equipamento = :equipamento
                ")->execute([
                    ':destino' => $transferencia['id_localizacao_destino'],
                    ':equipamento' => $transferencia['id_equipamento']
                ]);

                $pdo->prepare("
                    UPDATE acessorios_equipamento
                    SET id_localizacao = :destino
                    WHERE id_equipamento = :equipamento
                      AND isActive = 1
                ")->execute([
                    ':destino' => $transferencia['id_localizacao_destino'],
                    ':equipamento' => $transferencia['id_equipamento']
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
                        'transferencia_aprovada',
                        'transferencias_equipamentos',
                        :referencia,
                        :descricao,
                        NOW(),
                        1
                    )
                ")->execute([
                    ':equipamento' => $transferencia['id_equipamento'],
                    ':localizacao' => $transferencia['id_localizacao_destino'],
                    ':origem' => $transferencia['id_localizacao_origem'],
                    ':destino' => $transferencia['id_localizacao_destino'],
                    ':utilizador' => $idUtilizador,
                    ':referencia' => $idTransferencia,
                    ':descricao' => 'Transferência aprovada. Equipamento, acessórios e consumíveis associados mudaram de localização.'
                ]);

                $pdo->commit();
                $mensagemSucesso = 'Transferência aprovada com sucesso.';
            }
        }

        if ($acao === 'rejeitar' && $isAdmin) {
            $idTransferencia = (int) ($_POST['id_transferencia'] ?? 0);

            $stmt = $pdo->prepare("
                SELECT *
                FROM transferencias_equipamentos
                WHERE id_transferencia = :id
                AND estado = 'pendente'
                AND isActive = 1
            ");
            $stmt->execute([
                ':id' => $idTransferencia
            ]);
            $transferencia = $stmt->fetch();

            if (!$transferencia) {
                throw new Exception('Pedido de transferência não encontrado ou já tratado.');
            }

            $pdo->beginTransaction();

            $pdo->prepare("
                UPDATE transferencias_equipamentos
                SET estado = 'rejeitado',
                    id_utilizador_aprovacao = :utilizador,
                    data_aprovacao = NOW()
                WHERE id_transferencia = :id
            ")->execute([
                ':utilizador' => $idUtilizador,
                ':id' => $idTransferencia
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
                    'transferencia_rejeitada',
                    'transferencias_equipamentos',
                    :referencia,
                    :descricao,
                    NOW(),
                    1
                )
            ")->execute([
                ':equipamento' => $transferencia['id_equipamento'],
                ':localizacao' => $transferencia['id_localizacao_origem'],
                ':origem' => $transferencia['id_localizacao_origem'],
                ':destino' => $transferencia['id_localizacao_destino'],
                ':utilizador' => $idUtilizador,
                ':referencia' => $idTransferencia,
                ':descricao' => 'Pedido de transferência rejeitado pelo administrador.'
            ]);

            $pdo->commit();

                        $mensagemSucesso = 'Transferência rejeitada.';
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $mensagemErro = 'Erro: ' . $e->getMessage();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $mensagemErro = $e->getMessage();
    }
}

$equipamentos = $pdo->query("
    SELECT
        e.id_equipamento,
        e.codigo_equipamento,
        e.designacao,
        e.id_localizacao,
        CONCAT(l.departamento_nome, ' - Sala ', l.sala) AS localizacao_atual
    FROM equipamentos e
    INNER JOIN localizacoes l
        ON l.id_localizacao = e.id_localizacao
    WHERE e.isActive = 1
    ORDER BY e.codigo_equipamento
")->fetchAll();

$localizacoes = $pdo->query("
    SELECT id_localizacao, codigo, departamento_nome, piso, sala
    FROM localizacoes
    WHERE isActive = 1
    ORDER BY codigo
")->fetchAll();

$transferencias = $pdo->query("
    SELECT t.*, e.codigo_equipamento, e.designacao,
           lo.codigo AS origem_codigo,
           CONCAT(lo.departamento_nome, ' - Sala ', lo.sala) AS origem_localizacao,
           ld.codigo AS destino_codigo,
           CONCAT(ld.departamento_nome, ' - Sala ', ld.sala) AS destino_localizacao,
           u.nome AS utilizador_pedido
    FROM transferencias_equipamentos t
    INNER JOIN equipamentos e ON e.id_equipamento = t.id_equipamento
    INNER JOIN localizacoes lo ON lo.id_localizacao = t.id_localizacao_origem
    INNER JOIN localizacoes ld ON ld.id_localizacao = t.id_localizacao_destino
    INNER JOIN utilizadores u ON u.id_utilizador = t.id_utilizador_pedido
    WHERE t.isActive = 1
    ORDER BY t.data_pedido DESC
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Transferências de Equipamentos</h2>
            <p class="subtitulo-pagina">
                Registo e aprovação de alterações definitivas de localização dos equipamentos.
            </p>
        </div>

        <button type="button" class="btn btn-adicionar" data-bs-toggle="modal" data-bs-target="#modalNovaTransferencia">
            <i class="fa-solid fa-plus me-2"></i> Nova Transferência
        </button>
    </div>

    <?php if ($mensagemSucesso): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensagemSucesso); ?></div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($mensagemErro); ?></div>
    <?php endif; ?>

    <div class="table-responsive tabela-container">
        <table id="tabela-transferencias" class="table table-hover align-middle tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Equipamento</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Pedido por</th>
                    <th>Estado</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $classeEstadoTransferencia = [
                        'pendente' => 'estado-manutencao',
                        'aprovado' => 'estado-ativo',
                        'rejeitado' => 'estado-avariado'
                    ];
                ?>

                <?php foreach ($transferencias as $transferencia): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transferencia['codigo_transferencia']); ?></td>
                        <td><?php echo htmlspecialchars($transferencia['codigo_equipamento'] . ' - ' . $transferencia['designacao']); ?></td>
                        <td><?php echo htmlspecialchars($transferencia['origem_localizacao']); ?></td>
                        <td><?php echo htmlspecialchars($transferencia['destino_localizacao']); ?></td>
                        <td><?php echo htmlspecialchars($transferencia['utilizador_pedido']); ?></td>
                        <td>
                            <span class="estado <?php echo $classeEstadoTransferencia[$transferencia['estado']] ?? 'estado-inativo'; ?>">
                                <?php echo htmlspecialchars($transferencia['estado']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                                <div class="acoes-operacao">

                                    <button type="button"
                                            class="btn-acao-circular btn-acao-detalhe"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalheTransferencia<?php echo (int) $transferencia['id_transferencia']; ?>"
                                            title="Ver detalhes">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <?php if ($isAdmin && $transferencia['estado'] === 'pendente'): ?>

                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="acao" value="aprovar">
                                            <input type="hidden" name="id_transferencia" value="<?php echo (int) $transferencia['id_transferencia']; ?>">

                                            <button type="submit" class="btn-acao-circular btn-acao-aprovar" title="Aprovar transferência">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>

                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="acao" value="rejeitar">
                                            <input type="hidden" name="id_transferencia" value="<?php echo (int) $transferencia['id_transferencia']; ?>">

                                            <button type="submit" class="btn-acao-circular btn-acao-rejeitar" title="Rejeitar transferência">
                                                <i class="fa-solid fa-xmark"></i>
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

<?php foreach ($transferencias as $transferencia): ?>
            <div class="modal fade" id="modalDetalheTransferencia<?php echo (int) $transferencia['id_transferencia']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content modal-acessorio">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-eye me-2"></i>
                                Detalhes da Transferência
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body">
                            <div class="info-grid">
                                <div>
                                    <label>Código</label>
                                    <p><?php echo htmlspecialchars($transferencia['codigo_transferencia']); ?></p>
                                </div>

                                <div>
                                    <label>Equipamento</label>
                                    <p><?php echo htmlspecialchars($transferencia['codigo_equipamento'] . ' - ' . $transferencia['designacao']); ?></p>
                                </div>

                                <div>
                                    <label>Origem</label>
                                    <p><?php echo htmlspecialchars($transferencia['origem_localizacao']); ?></p>
                                </div>

                                <div>
                                    <label>Destino</label>
                                    <p><?php echo htmlspecialchars($transferencia['destino_localizacao']); ?></p>
                                </div>

                                <div>
                                    <label>Pedido por</label>
                                    <p><?php echo htmlspecialchars($transferencia['utilizador_pedido']); ?></p>
                                </div>

                                <div>
                                    <label>Estado</label>
                                    <p><?php echo htmlspecialchars($transferencia['estado']); ?></p>
                                </div>

                                <div>
                                    <label>Motivo</label>
                                    <p><?php echo htmlspecialchars($transferencia['motivo'] ?? '---'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

<div class="modal fade" id="modalNovaTransferencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content modal-acessorio">
            <form method="post" id="formNovaTransferencia" novalidate>
                <input type="hidden" name="acao" value="criar">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-right-left me-2"></i>
                        Nova Transferência
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div id="erroNovaTransferencia" class="alert alert-danger d-none" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <strong>Erro no formulário</strong>
                        </div>
                        <ul class="mb-0 ps-3" id="listaErrosTransferencia"></ul>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="pesquisaEquipamentoTransferencia" class="form-label">Equipamento *</label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="pesquisaEquipamentoTransferencia"
                                    data-hidden-target="idEquipamentoTransferencia"
                                    data-lista-target="listaEquipamentosTransferencia"
                                    data-localizacao-target="localizacaoAtualTransferencia"
                                    placeholder="Pesquisar e selecionar equipamento"
                                    autocomplete="off"
                                    required>

                                <input type="hidden"
                                    id="idEquipamentoTransferencia"
                                    name="id_equipamento"
                                    required>

                                <div class="lista-registos-custom" id="listaEquipamentosTransferencia">
                                    <?php foreach ($equipamentos as $equipamento): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo htmlspecialchars($equipamento['id_equipamento']); ?>"
                                                data-texto="<?php echo htmlspecialchars($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>"
                                                data-localizacao-atual="<?php echo htmlspecialchars($equipamento['localizacao_atual']); ?>">
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
                            <label for="pesquisaLocalizacaoTransferencia" class="form-label">Nova localização *</label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="pesquisaLocalizacaoTransferencia"
                                    data-hidden-target="idLocalizacaoTransferencia"
                                    data-lista-target="listaLocalizacoesTransferencia"
                                    placeholder="Pesquisar e selecionar localização"
                                    autocomplete="off"
                                    required>

                                <input type="hidden"
                                    id="idLocalizacaoTransferencia"
                                    name="id_localizacao_destino"
                                    required>

                                <div class="lista-registos-custom" id="listaLocalizacoesTransferencia">
                                    <?php foreach ($localizacoes as $localizacao): ?>
                                        <?php $textoLoc = htmlspecialchars($localizacao['departamento_nome'] . ' - Sala ' . $localizacao['sala']); ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo htmlspecialchars($localizacao['id_localizacao']); ?>"
                                                data-texto="<?php echo $textoLoc; ?>">
                                            <span><?php echo $textoLoc; ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="motivoTransferencia" class="form-label">Motivo *</label>
                            <input type="text"
                                   id="motivoTransferencia"
                                   name="motivo"
                                   class="form-control"
                                   maxlength="255"
                                   placeholder="Indique o motivo da transferência">
                            <small class="texto-ajuda-form contador-caracteres" data-target="motivoTransferencia" data-max="255">0 / 255 caracteres</small>
                        </div>

                        <div class="col-12">
                            <label for="observacoesTransferencia" class="form-label">Observações</label>
                            <textarea id="observacoesTransferencia"
                                      name="observacoes"
                                      class="form-control"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Informações adicionais (opcional)"></textarea>
                            <small class="texto-ajuda-form contador-caracteres" data-target="observacoesTransferencia" data-max="500">0 / 500 caracteres</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-guardar">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Pedido
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>