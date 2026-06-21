<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

function h_dashboard_engenheiro($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function texto_estado_dashboard($estado)
{
    $estados = [
        'ativo' => 'Ativo',
        'avariado' => 'Avariado',
        'em_manutencao' => 'Em manutenção',
        'em_calibracao' => 'Em calibração',
        'inativo' => 'Inativo',
        'abatido' => 'Abatido'
    ];

    return $estados[$estado] ?? $estado;
}

function classe_estado_dashboard($estado)
{
    switch ($estado) {
        case 'ativo':
            return 'estado-ativo';

        case 'avariado':
            return 'estado-avariado';

        case 'em_manutencao':
        case 'em_calibracao':
            return 'estado-manutencao';

        default:
            return 'estado-inativo';
    }
}

$erro_bd = '';
$totalEquipamentos = 0;
$totalAvariados = 0;
$totalManutencao = 0;
$totalCalibracao = 0;
$equipamentosAvariados = [];
$totalEmprestimosAmanha = 0;
$totalEmprestimosAtrasados = 0;
$emprestimosAmanha = [];
$emprestimosAtrasados = [];

try {
    $pdo = medicore_pdo();

    $stmtResumo = $pdo->query("
        SELECT
            COUNT(*) AS total_equipamentos,
            SUM(CASE WHEN estado = 'avariado' THEN 1 ELSE 0 END) AS total_avariados,
            SUM(CASE WHEN estado = 'em_manutencao' THEN 1 ELSE 0 END) AS total_manutencao,
            SUM(CASE WHEN estado = 'em_calibracao' THEN 1 ELSE 0 END) AS total_calibracao
        FROM equipamentos
        WHERE isActive = 1
    ");
    $resumo = $stmtResumo->fetch();

    $totalEquipamentos = (int) ($resumo['total_equipamentos'] ?? 0);
    $totalAvariados = (int) ($resumo['total_avariados'] ?? 0);
    $totalManutencao = (int) ($resumo['total_manutencao'] ?? 0);
    $totalCalibracao = (int) ($resumo['total_calibracao'] ?? 0);

    $stmtAvariados = $pdo->query("
        SELECT
            e.id_equipamento,
            e.codigo_equipamento,
            e.designacao,
            e.modelo,
            e.numero_serie,
            e.estado,
            e.criticidade,
            l.codigo AS codigo_localizacao,
            l.departamento_nome,
            l.piso,
            l.sala,
            fe.nome AS familia
        FROM equipamentos e
        INNER JOIN localizacoes l
            ON l.id_localizacao = e.id_localizacao
        INNER JOIN familias_equipamento fe
            ON fe.id_familia_equipamento = e.id_familia_equipamento
        WHERE e.isActive = 1
          AND e.estado = 'avariado'
        ORDER BY
            FIELD(e.criticidade, 'critica', 'alta', 'media', 'baixa'),
            e.codigo_equipamento ASC
        LIMIT 10
    ");
    $equipamentosAvariados = $stmtAvariados->fetchAll();
    
    $stmtEmprestimosAmanha = $pdo->query("
        SELECT
            emp.id_emprestimo,
            emp.codigo_emprestimo,
            emp.responsavel_emprestimo,
            emp.data_prevista_devolucao,
            e.codigo_equipamento,
            e.designacao,
            ld.codigo AS destino_codigo
        FROM emprestimos_equipamentos emp
        INNER JOIN equipamentos e
            ON e.id_equipamento = emp.id_equipamento
        INNER JOIN localizacoes ld
            ON ld.id_localizacao = emp.id_localizacao_destino
        WHERE emp.isActive = 1
        AND emp.estado = 'ativo'
        AND emp.data_prevista_devolucao = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ORDER BY emp.data_prevista_devolucao ASC
    ");
    $emprestimosAmanha = $stmtEmprestimosAmanha->fetchAll();
    $totalEmprestimosAmanha = count($emprestimosAmanha);

    $stmtEmprestimosAtrasados = $pdo->query("
        SELECT
            emp.id_emprestimo,
            emp.codigo_emprestimo,
            emp.responsavel_emprestimo,
            emp.data_prevista_devolucao,
            e.codigo_equipamento,
            e.designacao,
            ld.codigo AS destino_codigo
        FROM emprestimos_equipamentos emp
        INNER JOIN equipamentos e
            ON e.id_equipamento = emp.id_equipamento
        INNER JOIN localizacoes ld
            ON ld.id_localizacao = emp.id_localizacao_destino
        WHERE emp.isActive = 1
        AND emp.estado = 'ativo'
        AND emp.data_prevista_devolucao < CURDATE()
        ORDER BY emp.data_prevista_devolucao ASC
    ");
    $emprestimosAtrasados = $stmtEmprestimosAtrasados->fetchAll();
    $totalEmprestimosAtrasados = count($emprestimosAtrasados);
    } catch (Throwable $e) {
    $erro_bd = 'Erro ao carregar os indicadores do dashboard.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <section class="dashboard-header dashboard-engenheiro-header">
        <div>
            <h2>Dashboard do Engenheiro</h2>
            <p>Acompanhamento rápido dos equipamentos que precisam de intervenção técnica.</p>
        </div>
    </section>

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger rounded-4 fw-bold">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?php echo h_dashboard_engenheiro($erro_bd); ?>
        </div>
    <?php endif; ?>

    <section class="dashboard-indicadores dashboard-engenheiro-indicadores">
        <div class="indicador-card indicador-card-alerta">
            <div class="indicador-icone indicador-vermelho">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <div>
                <span>Equipamentos Avariados</span>
                <h3><?php echo h_dashboard_engenheiro($totalAvariados); ?></h3>
                <p>Necessitam de avaliação técnica</p>
            </div>
        </div>

        <div class="indicador-card">
            <div class="indicador-icone indicador-amarelo">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>

            <div>
                <span>Em Manutenção</span>
                <h3><?php echo h_dashboard_engenheiro($totalManutencao); ?></h3>
                <p>Processos técnicos em curso</p>
            </div>
        </div>

        <div class="indicador-card">
            <div class="indicador-icone indicador-azul">
                <i class="fa-solid fa-gauge-high"></i>
            </div>

            <div>
                <span>Em Calibração</span>
                <h3><?php echo h_dashboard_engenheiro($totalCalibracao); ?></h3>
                <p>Verificação metrológica ativa</p>
            </div>
        </div>

        <div class="indicador-card">
            <div class="indicador-icone">
                <i class="fa-solid fa-stethoscope"></i>
            </div>

            <div>
                <span>Total de Equipamentos</span>
                <h3><?php echo h_dashboard_engenheiro($totalEquipamentos); ?></h3>
                <p>Registos ativos no inventário</p>
            </div>
        </div>
    </section>

    <section class="dashboard-alertas-emprestimos">
        <div class="row g-4">
            <div class="col-md-6">
                <a href="<?php echo BASE_URL; ?>/private/views/mobilidade/emprestimo.php" class="alerta-emprestimo-card alerta-emprestimo-aviso text-decoration-none">
                    <div>
                        <span>Empréstimos a terminar amanhã</span>
                        <h3><?php echo h_dashboard_engenheiro($totalEmprestimosAmanha); ?></h3>
                        <p>Equipamentos que devem ser devolvidos no próximo dia.</p>
                    </div>
                    <i class="fa-solid fa-calendar-day"></i>
                </a>
            </div>

            <div class="col-md-6">
                <a href="<?php echo BASE_URL; ?>/private/views/mobilidade/emprestimo.php" class="alerta-emprestimo-card alerta-emprestimo-atrasado text-decoration-none">
                    <div>
                        <span>Empréstimos em atraso</span>
                        <h3><?php echo h_dashboard_engenheiro($totalEmprestimosAtrasados); ?></h3>
                        <p>Equipamentos com devolução ultrapassada.</p>
                    </div>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </a>
            </div>
        </div>

        <?php if (!empty($emprestimosAmanha) || !empty($emprestimosAtrasados)): ?>
            <div class="table-responsive tabela-container mt-4">
                <table class="table table-hover align-middle tabela-dashboard">
                    <thead>
                        <tr>
                            <th>Alerta</th>
                            <th>Empréstimo</th>
                            <th>Equipamento</th>
                            <th>Destino</th>
                            <th>Responsável</th>
                            <th>Data devolução</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emprestimosAtrasados as $emprestimo): ?>
                            <tr>
                                <td>
                                    <span class="estado estado-avariado">Atrasado</span>
                                </td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['codigo_emprestimo']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['codigo_equipamento'] . ' - ' . $emprestimo['designacao']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['destino_codigo']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['responsavel_emprestimo']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['data_prevista_devolucao']); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php foreach ($emprestimosAmanha as $emprestimo): ?>
                            <tr>
                                <td>
                                    <span class="estado estado-manutencao">Termina amanhã</span>
                                </td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['codigo_emprestimo']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['codigo_equipamento'] . ' - ' . $emprestimo['designacao']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['destino_codigo']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['responsavel_emprestimo']); ?></td>
                                <td><?php echo h_dashboard_engenheiro($emprestimo['data_prevista_devolucao']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="dashboard-alertas">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4>
                    <i class="fa-solid fa-bell me-2"></i>
                    Equipamentos Avariados
                </h4>
                <p>Lista dos equipamentos indisponíveis ordenada por criticidade.</p>
            </div>

            <a href="lista_equipamentos.php" class="btn btn-voltar">
                <i class="fa-solid fa-list me-2"></i>
                Ver lista completa
            </a>
        </div>

        <div class="table-responsive tabela-container p-0">
            <table class="table table-hover align-middle tabela-dashboard">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Equipamento</th>
                        <th>Família</th>
                        <th>Localização</th>
                        <th>Criticidade</th>
                        <th>Estado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equipamentosAvariados)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Não existem equipamentos avariados registados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipamentosAvariados as $equipamento): ?>
                            <tr>
                                <td><strong><?php echo h_dashboard_engenheiro($equipamento['codigo_equipamento']); ?></strong></td>
                                <td>
                                    <?php echo h_dashboard_engenheiro($equipamento['designacao']); ?><br>
                                    <small class="text-muted"><?php echo h_dashboard_engenheiro($equipamento['modelo']); ?> · <?php echo h_dashboard_engenheiro($equipamento['numero_serie']); ?></small>
                                </td>
                                <td><?php echo h_dashboard_engenheiro($equipamento['familia']); ?></td>
                                <td>
                                    <?php echo h_dashboard_engenheiro($equipamento['codigo_localizacao']); ?><br>
                                    <small class="text-muted">
                                        <?php echo h_dashboard_engenheiro($equipamento['departamento_nome']); ?> · Piso <?php echo h_dashboard_engenheiro($equipamento['piso']); ?> · Sala <?php echo h_dashboard_engenheiro($equipamento['sala']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="tipo-fornecedor <?php echo $equipamento['criticidade'] === 'critica' ? 'tipo-manutencao' : 'tipo-calibracao'; ?>">
                                        <?php echo h_dashboard_engenheiro(ucfirst($equipamento['criticidade'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="estado <?php echo h_dashboard_engenheiro(classe_estado_dashboard($equipamento['estado'])); ?>">
                                        <?php echo h_dashboard_engenheiro(texto_estado_dashboard($equipamento['estado'])); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/ficha_equipamento.php?ref=<?php echo url_ref($equipamento['id_equipamento']); ?>"
                                        class="btn btn-sm btn-ficha"
                                        title="Abrir ficha">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
