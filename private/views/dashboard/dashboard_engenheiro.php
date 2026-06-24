<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function texto_estado_dashboard($estado)
{
    $mapa = [
        'ativo'          => 'Ativo',
        'avariado'       => 'Avariado',
        'em_manutencao'  => 'Em manutenção',
        'em_calibracao'  => 'Em calibração',
        'inativo'        => 'Inativo',
        'abatido'        => 'Abatido',
    ];
    return $mapa[$estado] ?? $estado;
}

function classe_estado_dashboard($estado)
{
    switch ($estado) {
        case 'ativo':          return 'estado-ativo';
        case 'avariado':       return 'estado-avariado';
        case 'em_manutencao':
        case 'em_calibracao':  return 'estado-manutencao';
        default:               return 'estado-inativo';
    }
}

function dias_label(int $dias): string
{
    if ($dias < 0)  return 'Vencida há ' . abs($dias) . ' dia' . (abs($dias) !== 1 ? 's' : '');
    if ($dias === 0) return 'Vence hoje';
    return 'Vence em ' . $dias . ' dia' . ($dias !== 1 ? 's' : '');
}

function classe_dias(int $dias): string
{
    if ($dias < 0)  return 'estado-avariado';
    if ($dias <= 7) return 'estado-manutencao';
    return 'estado-calibracao';
}

/* =========================================================
   DADOS
   ========================================================= */
$erro_bd = '';

$totalEquipamentos  = 0;
$totalAvariados     = 0;
$totalManutencao    = 0;
$totalCalibracao    = 0;

$equipamentosAvariados     = [];
$emprestimosAmanha         = [];
$emprestimosAtrasados      = [];
$totalEmprestimosAmanha    = 0;
$totalEmprestimosAtrasados = 0;

$alertasManutencao  = [];
$alertasCalibracao  = [];
$consumiveisBaixoStock = [];
$equipamentosPorDepartamento = [];

try {
    $pdo = medicore_pdo();

    /* --- Resumo indicadores --- */
    $resumo = $pdo->query("
        SELECT
            COUNT(*) AS total,
            SUM(estado = 'avariado')      AS avariados,
            SUM(estado = 'em_manutencao') AS manutencao,
            SUM(estado = 'em_calibracao') AS calibracao
        FROM equipamentos WHERE isActive = 1
    ")->fetch();

    $totalEquipamentos = (int) ($resumo['total']      ?? 0);
    $totalAvariados    = (int) ($resumo['avariados']  ?? 0);
    $totalManutencao   = (int) ($resumo['manutencao'] ?? 0);
    $totalCalibracao   = (int) ($resumo['calibracao'] ?? 0);

    /* --- Equipamentos por departamento --- */
    $equipamentosPorDepartamento = $pdo->query("
        SELECT l.departamento_nome, COUNT(e.id_equipamento) AS total
        FROM equipamentos e
        INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
        WHERE e.isActive = 1
        GROUP BY l.departamento_nome
        ORDER BY total DESC
    ")->fetchAll();

    /* --- Equipamentos avariados --- */
    $equipamentosAvariados = $pdo->query("
        SELECT e.id_equipamento, e.codigo_equipamento, e.designacao,
               e.modelo, e.numero_serie, e.estado, e.criticidade,
               l.codigo AS codigo_localizacao, l.departamento_nome, l.piso, l.sala,
               fe.nome AS familia
        FROM equipamentos e
        INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
        INNER JOIN familias_equipamento fe ON fe.id_familia_equipamento = e.id_familia_equipamento
        WHERE e.isActive = 1 AND e.estado = 'avariado'
        ORDER BY FIELD(e.criticidade, 'critica', 'alta', 'media', 'baixa'), e.codigo_equipamento
        LIMIT 10
    ")->fetchAll();

    /* --- Alertas de manutenção (próxima manutenção até 30 dias ou já vencida) --- */
    $alertasManutencao = $pdo->query("
        SELECT id_equipamento, codigo_equipamento, designacao, departamento_nome, proxima_manutencao AS proxima,
               DATEDIFF(proxima_manutencao, CURDATE()) AS dias_restantes
        FROM (
            -- Equipamentos com processo encerrado
            SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, l.departamento_nome, m.proxima_manutencao
            FROM (SELECT id_equipamento, MAX(data_manutencao) AS ultima FROM manutencoes_equipamento WHERE isActive = 1 GROUP BY id_equipamento) ult
            INNER JOIN manutencoes_equipamento m ON m.id_equipamento = ult.id_equipamento AND m.data_manutencao = ult.ultima AND m.isActive = 1
            INNER JOIN equipamentos e ON e.id_equipamento = m.id_equipamento AND e.isActive = 1
            INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
            WHERE m.proxima_manutencao IS NOT NULL
              AND m.proxima_manutencao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION
            -- Equipamentos sem processos: calcula a partir da aquisição
            SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, l.departamento_nome,
                   DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_manutencao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) AS proxima_manutencao
            FROM equipamentos e
            INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
            WHERE e.isActive = 1
              AND e.periodicidade_manutencao IS NOT NULL
              AND e.data_aquisicao IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM manutencoes_equipamento m2 WHERE m2.id_equipamento = e.id_equipamento AND m2.isActive = 1)
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_manutencao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ) t
        ORDER BY proxima_manutencao ASC
        LIMIT 15
    ")->fetchAll();

    // Normalizar campo de data para o template
    foreach ($alertasManutencao as &$a) { $a['proxima_manutencao'] = $a['proxima']; }
    unset($a);

    /* --- Alertas de calibração (próxima calibração até 30 dias ou já vencida) --- */
    $alertasCalibracao = $pdo->query("
        SELECT id_equipamento, codigo_equipamento, designacao, departamento_nome, proxima_calibracao AS proxima,
               DATEDIFF(proxima_calibracao, CURDATE()) AS dias_restantes
        FROM (
            SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, l.departamento_nome, c.proxima_calibracao
            FROM (SELECT id_equipamento, MAX(data_calibracao) AS ultima FROM calibracoes_equipamento WHERE isActive = 1 GROUP BY id_equipamento) ult
            INNER JOIN calibracoes_equipamento c ON c.id_equipamento = ult.id_equipamento AND c.data_calibracao = ult.ultima AND c.isActive = 1
            INNER JOIN equipamentos e ON e.id_equipamento = c.id_equipamento AND e.isActive = 1
            INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
            WHERE c.proxima_calibracao IS NOT NULL
              AND c.proxima_calibracao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION
            SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, l.departamento_nome,
                   DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_calibracao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) AS proxima_calibracao
            FROM equipamentos e
            INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
            WHERE e.isActive = 1
              AND e.periodicidade_calibracao IS NOT NULL
              AND e.data_aquisicao IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM calibracoes_equipamento c2 WHERE c2.id_equipamento = e.id_equipamento AND c2.isActive = 1)
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_calibracao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ) t
        ORDER BY proxima_calibracao ASC
        LIMIT 15
    ")->fetchAll();

    foreach ($alertasCalibracao as &$a) { $a['proxima_calibracao'] = $a['proxima']; }
    unset($a);

    /* --- Consumíveis com stock baixo --- */
    $consumiveisBaixoStock = $pdo->query("
        SELECT
            c.codigo_consumivel,
            c.nome,
            c.stock_atual,
            c.stock_minimo,
            e.codigo_equipamento,
            e.designacao AS equipamento_nome,
            l.departamento_nome
        FROM consumiveis c
        INNER JOIN equipamentos e ON e.id_equipamento = c.id_equipamento
        INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
        WHERE c.isActive = 1
          AND c.stock_minimo > 0
          AND c.stock_atual <= c.stock_minimo
        ORDER BY (c.stock_atual / c.stock_minimo) ASC
        LIMIT 10
    ")->fetchAll();

    /* --- Empréstimos --- */
    $emprestimosAmanha = $pdo->query("
        SELECT emp.codigo_emprestimo, emp.responsavel_emprestimo,
               emp.data_prevista_devolucao,
               e.codigo_equipamento, e.designacao,
               ld.codigo AS destino_codigo
        FROM emprestimos_equipamentos emp
        INNER JOIN equipamentos e ON e.id_equipamento = emp.id_equipamento
        INNER JOIN localizacoes ld ON ld.id_localizacao = emp.id_localizacao_destino
        WHERE emp.isActive = 1 AND emp.estado = 'ativo'
          AND emp.data_prevista_devolucao = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ORDER BY emp.data_prevista_devolucao ASC
    ")->fetchAll();
    $totalEmprestimosAmanha = count($emprestimosAmanha);

    $emprestimosAtrasados = $pdo->query("
        SELECT emp.codigo_emprestimo, emp.responsavel_emprestimo,
               emp.data_prevista_devolucao,
               e.codigo_equipamento, e.designacao,
               ld.codigo AS destino_codigo
        FROM emprestimos_equipamentos emp
        INNER JOIN equipamentos e ON e.id_equipamento = emp.id_equipamento
        INNER JOIN localizacoes ld ON ld.id_localizacao = emp.id_localizacao_destino
        WHERE emp.isActive = 1 AND emp.estado = 'ativo'
          AND emp.data_prevista_devolucao < CURDATE()
        ORDER BY emp.data_prevista_devolucao ASC
    ")->fetchAll();
    $totalEmprestimosAtrasados = count($emprestimosAtrasados);

    /* --- Alertas de garantia / manutenção / calibração --- */
    $totalGarantiaVencer = (int) $pdo->query("
        SELECT COUNT(DISTINCT e.id_equipamento)
        FROM equipamentos e
        INNER JOIN equipamentos_fornecedores ef ON ef.id_equipamento = e.id_equipamento AND ef.isActive = 1
        WHERE e.isActive = 1
          AND ef.data_fim_garantia IS NOT NULL
          AND ef.data_fim_garantia >= CURDATE()
          AND ef.data_fim_garantia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ")->fetchColumn();

    $totalSemGarantiaData = (int) $pdo->query("
        SELECT COUNT(*) FROM equipamentos e
        WHERE e.isActive = 1
          AND NOT EXISTS (
              SELECT 1 FROM equipamentos_fornecedores ef
              WHERE ef.id_equipamento = e.id_equipamento
                AND ef.isActive = 1
                AND ef.data_fim_garantia IS NOT NULL
          )
    ")->fetchColumn();

    $totalManutencaoVencer = (int) $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT m.id_equipamento
            FROM (SELECT id_equipamento, MAX(data_manutencao) AS ultima FROM manutencoes_equipamento WHERE isActive = 1 GROUP BY id_equipamento) ult
            INNER JOIN manutencoes_equipamento m ON m.id_equipamento = ult.id_equipamento AND m.data_manutencao = ult.ultima AND m.isActive = 1
            INNER JOIN equipamentos e ON e.id_equipamento = m.id_equipamento AND e.isActive = 1
            WHERE m.proxima_manutencao IS NOT NULL
              AND m.proxima_manutencao >= CURDATE()
              AND m.proxima_manutencao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION
            SELECT e.id_equipamento
            FROM equipamentos e
            WHERE e.isActive = 1
              AND e.periodicidade_manutencao IS NOT NULL
              AND e.data_aquisicao IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM manutencoes_equipamento m2 WHERE m2.id_equipamento = e.id_equipamento AND m2.isActive = 1)
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_manutencao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) >= CURDATE()
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_manutencao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ) t
    ")->fetchColumn();

    $totalManutencaoExpirada = (int) $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT m.id_equipamento
            FROM (SELECT id_equipamento, MAX(data_manutencao) AS ultima FROM manutencoes_equipamento WHERE isActive = 1 GROUP BY id_equipamento) ult
            INNER JOIN manutencoes_equipamento m ON m.id_equipamento = ult.id_equipamento AND m.data_manutencao = ult.ultima AND m.isActive = 1
            INNER JOIN equipamentos e ON e.id_equipamento = m.id_equipamento AND e.isActive = 1
            WHERE m.proxima_manutencao IS NOT NULL
              AND m.proxima_manutencao < CURDATE()
            UNION
            SELECT e.id_equipamento
            FROM equipamentos e
            WHERE e.isActive = 1
              AND e.periodicidade_manutencao IS NOT NULL
              AND e.data_aquisicao IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM manutencoes_equipamento m2 WHERE m2.id_equipamento = e.id_equipamento AND m2.isActive = 1)
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_manutencao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) < CURDATE()
        ) t
    ")->fetchColumn();

    $totalCalibracaoVencer = (int) $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT c.id_equipamento
            FROM (SELECT id_equipamento, MAX(data_calibracao) AS ultima FROM calibracoes_equipamento WHERE isActive = 1 GROUP BY id_equipamento) ult
            INNER JOIN calibracoes_equipamento c ON c.id_equipamento = ult.id_equipamento AND c.data_calibracao = ult.ultima AND c.isActive = 1
            INNER JOIN equipamentos e ON e.id_equipamento = c.id_equipamento AND e.isActive = 1
            WHERE c.proxima_calibracao IS NOT NULL
              AND c.proxima_calibracao >= CURDATE()
              AND c.proxima_calibracao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION
            SELECT e.id_equipamento
            FROM equipamentos e
            WHERE e.isActive = 1
              AND e.periodicidade_calibracao IS NOT NULL
              AND e.data_aquisicao IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM calibracoes_equipamento c2 WHERE c2.id_equipamento = e.id_equipamento AND c2.isActive = 1)
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_calibracao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) >= CURDATE()
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_calibracao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ) t
    ")->fetchColumn();

    $totalCalibracaoExpirada = (int) $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT c.id_equipamento
            FROM (SELECT id_equipamento, MAX(data_calibracao) AS ultima FROM calibracoes_equipamento WHERE isActive = 1 GROUP BY id_equipamento) ult
            INNER JOIN calibracoes_equipamento c ON c.id_equipamento = ult.id_equipamento AND c.data_calibracao = ult.ultima AND c.isActive = 1
            INNER JOIN equipamentos e ON e.id_equipamento = c.id_equipamento AND e.isActive = 1
            WHERE c.proxima_calibracao IS NOT NULL
              AND c.proxima_calibracao < CURDATE()
            UNION
            SELECT e.id_equipamento
            FROM equipamentos e
            WHERE e.isActive = 1
              AND e.periodicidade_calibracao IS NOT NULL
              AND e.data_aquisicao IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM calibracoes_equipamento c2 WHERE c2.id_equipamento = e.id_equipamento AND c2.isActive = 1)
              AND DATE_ADD(e.data_aquisicao, INTERVAL CASE e.periodicidade_calibracao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END MONTH) < CURDATE()
        ) t
    ")->fetchColumn();

} catch (Throwable $e) {
    $erro_bd = 'Erro ao carregar os dados do dashboard.';
}

/* Dados para o gráfico */
$chartLabels = array_column($equipamentosPorDepartamento, 'departamento_nome');
$chartData   = array_map('intval', array_column($equipamentosPorDepartamento, 'total'));
$chartColors = ['#0d9e7e','#1cbfa0','#38d9bb','#6ee7d0','#a5f3e8','#c7f9f0','#e0fdf9','#0a7a63','#085e4d','#053d32'];

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger rounded-4 fw-bold mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?php echo h($erro_bd); ?>
        </div>
    <?php endif; ?>

    <!-- =====================================================
         ALERTAS DE GARANTIA / MANUTENÇÃO / CALIBRAÇÃO
         ===================================================== -->
    <section class="dashboard-indicadores" style="flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">

        <?php if ($totalGarantiaVencer > 0): ?>
        <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/garantias_equipamentos.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo"><i class="fa-solid fa-shield"></i></div>
            <div>
                <span>Garantia a expirar</span>
                <h3><?php echo $totalGarantiaVencer; ?></h3>
                <p>Expira em menos de 30 dias</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalSemGarantiaData > 0): ?>
        <div class="indicador-card indicador-card-aviso" style="border-left:4px solid #c0392b;">
            <div class="indicador-icone indicador-vermelho"><i class="fa-solid fa-shield-halved"></i></div>
            <div>
                <span>Sem garantia registada</span>
                <h3><?php echo $totalSemGarantiaData; ?></h3>
                <p>Sem data de fim de garantia</p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($totalManutencaoVencer > 0): ?>
        <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div>
                <span>Manutenção a vencer</span>
                <h3><?php echo $totalManutencaoVencer; ?></h3>
                <p>Prazo nos próximos 30 dias</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalManutencaoExpirada > 0): ?>
        <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #c0392b;">
            <div class="indicador-icone indicador-vermelho"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div>
                <span>Manutenção expirada</span>
                <h3><?php echo $totalManutencaoExpirada; ?></h3>
                <p>Prazo já ultrapassado</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalCalibracaoVencer > 0): ?>
        <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo"><i class="fa-solid fa-gauge-high"></i></div>
            <div>
                <span>Calibração a vencer</span>
                <h3><?php echo $totalCalibracaoVencer; ?></h3>
                <p>Prazo nos próximos 30 dias</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalCalibracaoExpirada > 0): ?>
        <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #c0392b;">
            <div class="indicador-icone indicador-vermelho"><i class="fa-solid fa-gauge-high"></i></div>
            <div>
                <span>Calibração expirada</span>
                <h3><?php echo $totalCalibracaoExpirada; ?></h3>
                <p>Prazo já ultrapassado</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalGarantiaVencer === 0 && $totalSemGarantiaData === 0 && $totalManutencaoVencer === 0 && $totalManutencaoExpirada === 0 && $totalCalibracaoVencer === 0 && $totalCalibracaoExpirada === 0): ?>
        <div class="indicador-card" style="border-left:4px solid #27ae60;">
            <div class="indicador-icone indicador-verde"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <span>Garantias e intervenções</span>
                <h3>OK</h3>
                <p>Sem alertas de garantia, manutenção ou calibração</p>
            </div>
        </div>
        <?php endif; ?>

    </section>

    <!-- =====================================================
         INDICADORES
         ===================================================== -->
    <section class="dashboard-indicadores dashboard-engenheiro-indicadores">
        <a href="<?php echo BASE_URL; ?>/private/views/avarias/lista_avarias.php" class="indicador-card indicador-card-alerta text-decoration-none">
            <div class="indicador-icone indicador-vermelho">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span>Equipamentos Avariados</span>
                <h3><?php echo h($totalAvariados); ?></h3>
                <p>Necessitam de avaliação técnica</p>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/calibracao_manutencao.php" class="indicador-card text-decoration-none">
            <div class="indicador-icone indicador-amarelo">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div>
                <span>Em Manutenção</span>
                <h3><?php echo h($totalManutencao); ?></h3>
                <p>Processos técnicos em curso</p>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/calibracao_manutencao.php" class="indicador-card text-decoration-none">
            <div class="indicador-icone indicador-azul">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <span>Em Calibração</span>
                <h3><?php echo h($totalCalibracao); ?></h3>
                <p>Verificação metrológica ativa</p>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/lista_equipamentos.php" class="indicador-card text-decoration-none">
            <div class="indicador-icone">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <div>
                <span>Total de Equipamentos</span>
                <h3><?php echo h($totalEquipamentos); ?></h3>
                <p>Registos ativos no inventário</p>
            </div>
        </a>
    </section>

    <!-- =====================================================
         GRÁFICO + ALERTAS RÁPIDOS
         ===================================================== -->
    <div class="row g-4 mb-4">

        <!-- Gráfico por departamento -->
        <div class="col-lg-5">
            <div class="tabela-container h-100 d-flex flex-column">
                <h5 class="subtitulo-bloco-form mb-1">
                    <i class="fa-solid fa-building me-2"></i>Equipamentos por Departamento
                </h5>
                <p class="texto-ajuda-form mb-3">Distribuição do parque ativo por localização.</p>
                <?php if (empty($equipamentosPorDepartamento)): ?>
                    <p class="text-muted text-center py-4">Sem dados de localização.</p>
                <?php else: ?>
                    <div class="d-flex justify-content-center align-items-center" style="flex:1;min-height:260px">
                        <canvas id="graficoDeptos" style="max-height:260px"></canvas>
                    </div>
                    <ul class="list-unstyled mt-3 mb-0" style="font-size:.85rem">
                        <?php foreach ($equipamentosPorDepartamento as $i => $depto): ?>
                            <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span>
                                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo h($chartColors[$i % count($chartColors)]); ?>;margin-right:6px"></span>
                                    <?php echo h($depto['departamento_nome']); ?>
                                </span>
                                <strong><?php echo h($depto['total']); ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alertas de stock + empréstimos -->
        <div class="col-lg-7 d-flex flex-column gap-4">

            <!-- Empréstimos -->
            <div class="row g-3">
                <div class="col-6">
                    <a href="<?php echo BASE_URL; ?>/private/views/mobilidade/emprestimo.php"
                        class="alerta-emprestimo-card alerta-emprestimo-aviso text-decoration-none">
                        <div>
                            <span>Empréstimos a terminar amanhã</span>
                            <h3><?php echo h($totalEmprestimosAmanha); ?></h3>
                            <p>Devolução esperada no próximo dia.</p>
                        </div>
                        <i class="fa-solid fa-calendar-day"></i>
                    </a>
                </div>
                <div class="col-6">
                    <a href="<?php echo BASE_URL; ?>/private/views/mobilidade/emprestimo.php"
                        class="alerta-emprestimo-card alerta-emprestimo-atrasado text-decoration-none">
                        <div>
                            <span>Empréstimos em atraso</span>
                            <h3><?php echo h($totalEmprestimosAtrasados); ?></h3>
                            <p>Devolução ultrapassada.</p>
                        </div>
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>


</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('graficoDeptos');
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($chartLabels, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: <?php echo json_encode(array_slice($chartColors, 0, count($chartData))); ?>,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = Math.round(ctx.parsed / total * 100);
                            return ' ' + ctx.parsed + ' equipamento' + (ctx.parsed !== 1 ? 's' : '') + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
