<?php
require_once __DIR__ . '/../../includes/funcoes.php';

redirect_if_not_logged();

if (($_SESSION['tipo_utilizador'] ?? '') !== 'Administrador') {
    header('Location: ' . rota_inicial_utilizador());
    exit;
}

$pdo = medicore_pdo();

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function texto_estado_processo($estado)
{
    $estados = [
        'aguarda_decisao' => 'À espera da decisão',
        'aprovado' => 'Aprovado',
        'reprovado' => 'Reprovado',
        'cancelado' => 'Cancelado',
        'aguarda_recolha' => 'Aguarda recolha',
        'procedimento_a_decorrer' => 'Procedimento a decorrer',
        'procedimento_efetuado' => 'Procedimento efetuado',
        'emissao_relatorio' => 'Emissão do relatório',
        'devolucao_equipamento' => 'Devolução do equipamento',
        'processo_finalizado' => 'Processo finalizado'
    ];

    return $estados[$estado] ?? $estado;
}

function decimal_ou_null($valor)
{
    $valor = trim((string) ($valor ?? ''));

    if ($valor === '') {
        return null;
    }

    return (float) str_replace(',', '.', $valor);
}

$erro = null;
$sucesso = null;
$utilizadorAtual = $_SESSION['nome'] ?? $_SESSION['username'] ?? 'Administrador';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipoProcesso = $_POST['tipo_processo'] ?? '';
        $idProcesso = (int) ($_POST['id_processo'] ?? 0);
        $decisao = $_POST['decisao'] ?? '';
        $motivoDecisao = trim($_POST['motivo_decisao'] ?? '');

        if (!in_array($tipoProcesso, ['manutencao', 'calibracao'], true)) {
            throw new Exception('Tipo de processo inválido.');
        }

        if ($idProcesso <= 0) {
            throw new Exception('Processo inválido.');
        }

        if (!in_array($decisao, ['aprovado', 'reprovado'], true)) {
            throw new Exception('Decisão inválida.');
        }

        if ($decisao === 'reprovado' && $motivoDecisao === '') {
            throw new Exception('Indique o motivo da reprovação.');
        }

        // Obter coberta_por_garantia da BD para este processo
        $tabelaConsulta = $tipoProcesso === 'manutencao' ? 'manutencoes_equipamento' : 'calibracoes_equipamento';
        $campoIdConsulta = $tipoProcesso === 'manutencao' ? 'id_manutencao' : 'id_calibracao';
        $stmtGar = $pdo->prepare("SELECT coberta_por_garantia FROM {$tabelaConsulta} WHERE {$campoIdConsulta} = :id AND isActive = 1 LIMIT 1");
        $stmtGar->execute([':id' => $idProcesso]);
        $cobertaPorGarantia = (int) ($stmtGar->fetchColumn() ?? 0);

        $custo = null;
        if ($decisao === 'aprovado' && $cobertaPorGarantia === 0) {
            $custo = decimal_ou_null($_POST['custo'] ?? null);
            if ($custo === null) {
                throw new Exception('Indique o custo do processo — obrigatório quando não está coberto por garantia.');
            }
        }

        $tabela = $tipoProcesso === 'manutencao'
            ? 'manutencoes_equipamento'
            : 'calibracoes_equipamento';

        $campoId = $tipoProcesso === 'manutencao'
            ? 'id_manutencao'
            : 'id_calibracao';

        $estadoNovo = $decisao === 'aprovado'
            ? 'aguarda_recolha'
            : 'reprovado';

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE {$tabela}
            SET
                estado_processo = :estado_processo,
                decisao_admin = :decisao_admin,
                id_admin_decisao = :id_admin_decisao,
                data_decisao = NOW(),
                motivo_decisao = :motivo_decisao,
                custo = :custo,
                atualizado_por = :atualizado_por
            WHERE {$campoId} = :id
              AND estado_processo = 'aguarda_decisao'
              AND isActive = 1
        ");

        $stmt->execute([
            ':estado_processo' => $estadoNovo,
            ':decisao_admin' => $decisao,
            ':id_admin_decisao' => $_SESSION['id_utilizador'] ?? null,
            ':motivo_decisao' => $motivoDecisao !== '' ? $motivoDecisao : null,
            ':custo' => $custo,
            ':atualizado_por' => $utilizadorAtual,
            ':id' => $idProcesso
        ]);

        $pdo->commit();

        $sucesso = $decisao === 'aprovado'
            ? 'Processo aprovado com sucesso.'
            : 'Processo reprovado com sucesso.';
    }

    $stmtProcessos = $pdo->query("
        SELECT
            'manutencao' AS tipo_processo,
            m.id_manutencao AS id_processo,
            CONCAT('MAN-', YEAR(COALESCE(m.data_abertura, m.criado_em)), '-', LPAD(m.id_manutencao, 4, '0')) AS codigo,
            CASE
                WHEN m.tipo_manutencao = 'preventiva' THEN 'Manutenção preventiva'
                ELSE 'Manutenção corretiva'
            END AS procedimento,
            m.estado_processo,
            m.data_abertura,
            m.data_prevista,
            m.coberta_por_garantia,
            m.custo,
            m.tipo_execucao,
            e.codigo_equipamento,
            e.designacao AS equipamento,
            f.nome_empresa AS fornecedor
        FROM manutencoes_equipamento m
        INNER JOIN equipamentos e ON e.id_equipamento = m.id_equipamento
        LEFT JOIN fornecedores f ON f.id_fornecedor = m.id_fornecedor_responsavel
        WHERE m.isActive = 1
          AND m.estado_processo = 'aguarda_decisao'

        UNION ALL

        SELECT
            'calibracao' AS tipo_processo,
            c.id_calibracao AS id_processo,
            CONCAT('CAL-', YEAR(COALESCE(c.data_abertura, c.criado_em)), '-', LPAD(c.id_calibracao, 4, '0')) AS codigo,
            'Calibração' AS procedimento,
            c.estado_processo,
            c.data_abertura,
            c.data_prevista,
            c.coberta_por_garantia,
            c.custo,
            c.tipo_execucao,
            e.codigo_equipamento,
            e.designacao AS equipamento,
            f.nome_empresa AS fornecedor
        FROM calibracoes_equipamento c
        INNER JOIN equipamentos e ON e.id_equipamento = c.id_equipamento
        LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor_responsavel
        WHERE c.isActive = 1
          AND c.estado_processo = 'aguarda_decisao'

        ORDER BY data_abertura DESC, codigo DESC
    ");

    $processos = $stmtProcessos->fetchAll();

    $stmtParaEncerrar = $pdo->query("
        SELECT
            'manutencao' AS tipo_processo,
            m.id_manutencao AS id_processo,
            CONCAT('MAN-', YEAR(COALESCE(m.data_abertura, m.criado_em)), '-', LPAD(m.id_manutencao, 4, '0')) AS codigo,
            CASE
                WHEN m.tipo_manutencao = 'preventiva' THEN 'Manutenção preventiva'
                ELSE 'Manutenção corretiva'
            END AS procedimento,
            m.estado_processo,
            m.data_abertura,
            m.data_prevista,
            e.codigo_equipamento,
            e.designacao AS equipamento,
            f.nome_empresa AS fornecedor
        FROM manutencoes_equipamento m
        INNER JOIN equipamentos e ON e.id_equipamento = m.id_equipamento
        LEFT JOIN fornecedores f ON f.id_fornecedor = m.id_fornecedor_responsavel
        WHERE m.isActive = 1
          AND m.estado_processo = 'devolucao_equipamento'

        UNION ALL

        SELECT
            'calibracao' AS tipo_processo,
            c.id_calibracao AS id_processo,
            CONCAT('CAL-', YEAR(COALESCE(c.data_abertura, c.criado_em)), '-', LPAD(c.id_calibracao, 4, '0')) AS codigo,
            'Calibração' AS procedimento,
            c.estado_processo,
            c.data_abertura,
            c.data_prevista,
            e.codigo_equipamento,
            e.designacao AS equipamento,
            f.nome_empresa AS fornecedor
        FROM calibracoes_equipamento c
        INNER JOIN equipamentos e ON e.id_equipamento = c.id_equipamento
        LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor_responsavel
        WHERE c.isActive = 1
          AND c.estado_processo = 'devolucao_equipamento'

        ORDER BY data_abertura DESC, codigo DESC
    ");
    $processosParaEncerrar = $stmtParaEncerrar->fetchAll();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $erro = $e->getMessage();
    $processos = $processos ?? [];
    $processosParaEncerrar = $processosParaEncerrar ?? [];
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="pagina-topo">
        <div>
            <h1>Aprovação de Processos</h1>
            <p>Validação administrativa dos pedidos de calibração e manutenção antes da execução técnica.</p>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?php echo h($erro); ?>
        </div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?php echo h($sucesso); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive tabela-container">
    <table id="tabela-aprovacao-processos"
           class="table table-hover align-middle tabela-equipamentos tabela-calibracoes-manutencoes tabela-datatables-medicore">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Processo</th>
                        <th>Equipamento</th>
                        <th>Fornecedor</th>
                        <th>Data prevista</th>
                        <th>Estado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processos as $processo): ?>
                        <tr>
                            <td><?php echo h($processo['codigo']); ?></td>
                            <td><?php echo h($processo['procedimento']); ?></td>
                            <td>
                                <?php echo h($processo['codigo_equipamento']); ?> -
                                <?php echo h($processo['equipamento']); ?>
                            </td>
                            <td><?php echo h($processo['fornecedor'] ?: '---'); ?></td>
                            <td><?php echo h($processo['data_prevista'] ?: '---'); ?></td>
                            <td>
                                <span class="estado estado-manutencao">
                                    <?php echo h(texto_estado_processo($processo['estado_processo'])); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="acoes-operacao">
                                    <button type="button"
                                            class="btn-acao-circular btn-acao-aprovar"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAprovar<?php echo h($processo['tipo_processo'] . $processo['id_processo']); ?>"
                                            title="Aprovar">
                                        <i class="fa-solid fa-check"></i>
                                    </button>

                                    <button type="button"
                                            class="btn-acao-circular btn-acao-rejeitar"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalRejeitar<?php echo h($processo['tipo_processo'] . $processo['id_processo']); ?>"
                                            title="Reprovar">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalAprovar<?php echo h($processo['tipo_processo'] . $processo['id_processo']); ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content modal-confirmacao">
                                    <form method="post">
                                        <div class="modal-header modal-header-sucesso">
                                            <h5 class="modal-title">
                                                <i class="fa-solid fa-check me-2"></i>
                                                Aprovar processo
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="alert alert-danger d-none alerta-custo-aprovar" role="alert">
                                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                                O custo do processo é obrigatório quando não está coberto por garantia.
                                            </div>

                                            <input type="hidden" name="tipo_processo" value="<?php echo h($processo['tipo_processo']); ?>">
                                            <input type="hidden" name="id_processo" value="<?php echo (int) $processo['id_processo']; ?>">
                                            <input type="hidden" name="decisao" value="aprovado">

                                            <p class="mb-3">
                                                Pretende aprovar o processo
                                                <strong><?php echo h($processo['codigo']); ?></strong>?
                                            </p>

                                            <div class="mb-3">
                                                <label class="form-label">Coberto por garantia</label>
                                                <div>
                                                    <?php if ((int) ($processo['coberta_por_garantia'] ?? 0) === 1): ?>
                                                        <span class="estado estado-ativo">Sim</span>
                                                    <?php else: ?>
                                                        <span class="estado estado-inativo">Não</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if ((int) ($processo['coberta_por_garantia'] ?? 0) === 0): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Custo do processo (€) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" min="0" name="custo" class="form-control input-custo-aprovar" placeholder="Ex: 120.00">
                                            </div>
                                            <?php endif; ?>

                                            <div class="mb-3">
                                                <label class="form-label">Observação</label>
                                                <textarea name="motivo_decisao" rows="3" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-guardar">Aprovar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalRejeitar<?php echo h($processo['tipo_processo'] . $processo['id_processo']); ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content modal-confirmacao">
                                    <form method="post">
                                        <div class="modal-header modal-header-remocao">
                                            <h5 class="modal-title">
                                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                                Reprovar processo
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <input type="hidden" name="tipo_processo" value="<?php echo h($processo['tipo_processo']); ?>">
                                            <input type="hidden" name="id_processo" value="<?php echo (int) $processo['id_processo']; ?>">
                                            <input type="hidden" name="decisao" value="reprovado">

                                            <p>
                                                Indique o motivo para reprovar o processo
                                                <strong><?php echo h($processo['codigo']); ?></strong>.
                                            </p>

                                            <textarea name="motivo_decisao" rows="4" class="form-control" required></textarea>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-eliminar">Reprovar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <div class="pagina-topo mt-4">
        <div>
            <h1>Processos para Encerrar</h1>
            <p>Processos em fase de devolução do equipamento aguardando encerramento pelo administrador.</p>
        </div>
    </div>

    <div class="table-responsive tabela-container">
        <table id="tabela-processos-encerrar"
               class="table table-hover align-middle tabela-equipamentos tabela-calibracoes-manutencoes tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Processo</th>
                    <th>Equipamento</th>
                    <th>Fornecedor</th>
                    <th>Data prevista</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($processosParaEncerrar as $pe): ?>
                    <tr>
                        <td><?php echo h($pe['codigo']); ?></td>
                        <td><?php echo h($pe['procedimento']); ?></td>
                        <td>
                            <?php echo h($pe['codigo_equipamento']); ?> -
                            <?php echo h($pe['equipamento']); ?>
                        </td>
                        <td><?php echo h($pe['fornecedor'] ?: '---'); ?></td>
                        <td><?php echo h($pe['data_prevista'] ?: '---'); ?></td>
                        <td class="text-center">
                            <div class="acoes-operacao">
                                <a href="detalhe_processo.php?tipo=<?php echo urlencode($pe['tipo_processo']); ?>&id=<?php echo (int) $pe['id_processo']; ?>"
                                   class="btn-acao-circular btn-acao-ver"
                                   title="Ver e encerrar processo">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.addEventListener('submit', function (e) {
    const form = e.target;
    const modal = form.closest('.modal-content');
    if (!modal) return;

    const inputCusto = modal.querySelector('.input-custo-aprovar');
    const alerta     = modal.querySelector('.alerta-custo-aprovar');

    if (!inputCusto || !alerta) return;

    if (!inputCusto.value || parseFloat(inputCusto.value) <= 0) {
        e.preventDefault();
        alerta.classList.remove('d-none');
        inputCusto.focus();
    } else {
        alerta.classList.add('d-none');
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>