<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$pdo = medicore_pdo();

function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }

/* ── Avisos ── */

// Processos (manutenção + calibração) a aguardar aprovação
$totalProcessos = (int) $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT id_manutencao FROM manutencoes_equipamento WHERE estado_processo = 'aguarda_decisao' AND isActive = 1
        UNION ALL
        SELECT id_calibracao FROM calibracoes_equipamento WHERE estado_processo = 'aguarda_decisao' AND isActive = 1
    ) t
")->fetchColumn();

// Transferências pendentes
$totalTransferencias = (int) $pdo->query("
    SELECT COUNT(*) FROM transferencias_equipamentos WHERE estado = 'pendente' AND isActive = 1
")->fetchColumn();

// Empréstimos atrasados (data prevista de devolução ultrapassada e ainda ativos)
$totalEmprestimosAtrasados = (int) $pdo->query("
    SELECT COUNT(*) FROM emprestimos_equipamentos
    WHERE estado IN ('ativo','atrasado') AND data_prevista_devolucao < CURDATE() AND isActive = 1
")->fetchColumn();

// Equipamentos sem custo de aquisição
$totalSemCusto = (int) $pdo->query("
    SELECT COUNT(*) FROM equipamentos WHERE valor_aquisicao IS NULL AND isActive = 1
")->fetchColumn();

// Equipamentos sem documento de contrato de aquisição
$totalSemContrato = (int) $pdo->query("
    SELECT COUNT(*) FROM equipamentos e
    WHERE e.isActive = 1
      AND NOT EXISTS (
          SELECT 1 FROM documentos_equipamentos d
          WHERE d.id_equipamento = e.id_equipamento AND d.tipo_documento = 'contrato' AND d.isActive = 1
      )
")->fetchColumn();

// Equipamentos sem documento de garantia
$totalSemGarantia = (int) $pdo->query("
    SELECT COUNT(*) FROM equipamentos e
    WHERE e.isActive = 1
      AND NOT EXISTS (
          SELECT 1 FROM documentos_equipamentos d
          WHERE d.id_equipamento = e.id_equipamento AND d.tipo_documento = 'garantia' AND d.isActive = 1
      )
")->fetchColumn();

/* ── Alertas de garantia / manutenção / calibração ── */

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
        -- Equipamentos com processo encerrado: usa proxima_manutencao calculada
        SELECT m.id_equipamento
        FROM (SELECT id_equipamento, MAX(data_manutencao) AS ultima FROM manutencoes_equipamento WHERE isActive = 1 GROUP BY id_equipamento) ult
        INNER JOIN manutencoes_equipamento m ON m.id_equipamento = ult.id_equipamento AND m.data_manutencao = ult.ultima AND m.isActive = 1
        INNER JOIN equipamentos e ON e.id_equipamento = m.id_equipamento AND e.isActive = 1
        WHERE m.proxima_manutencao IS NOT NULL
          AND m.proxima_manutencao >= CURDATE()
          AND m.proxima_manutencao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        UNION
        -- Equipamentos sem nenhum processo: usa data_aquisicao + periodicidade
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

/* ── Gráficos ── */

// Gastos anuais de aquisição
$rowsAquisicao = $pdo->query("
    SELECT YEAR(data_aquisicao) AS ano, SUM(valor_aquisicao) AS total
    FROM equipamentos
    WHERE isActive = 1 AND valor_aquisicao IS NOT NULL AND data_aquisicao IS NOT NULL
    GROUP BY YEAR(data_aquisicao)
    ORDER BY ano ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Gastos anuais de manutenção (processos aprovados com custo)
$rowsManutencao = $pdo->query("
    SELECT YEAR(COALESCE(data_manutencao, data_prevista)) AS ano, SUM(custo) AS total
    FROM manutencoes_equipamento
    WHERE isActive = 1 AND custo IS NOT NULL AND decisao_admin = 'aprovado'
    GROUP BY ano
    ORDER BY ano ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Gastos anuais de calibração (processos aprovados com custo)
$rowsCalibracao = $pdo->query("
    SELECT YEAR(COALESCE(data_calibracao, data_prevista)) AS ano, SUM(custo) AS total
    FROM calibracoes_equipamento
    WHERE isActive = 1 AND custo IS NOT NULL AND decisao_admin = 'aprovado'
    GROUP BY ano
    ORDER BY ano ASC
")->fetchAll(PDO::FETCH_ASSOC);

function prepararGrafico(array $rows): array {
    $anos  = array_column($rows, 'ano');
    $totais = array_map(fn($r) => round((float)$r['total'], 2), $rows);
    return ['anos' => $anos, 'totais' => $totais];
}

$dadosAquisicao  = prepararGrafico($rowsAquisicao);
$dadosManutencao = prepararGrafico($rowsManutencao);
$dadosCalibracao = prepararGrafico($rowsCalibracao);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="conteudo-private">

    <!-- ── ALERTAS DE GARANTIA / MANUTENÇÃO / CALIBRAÇÃO ── -->
    <section class="dashboard-indicadores" style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">

        <?php if ($totalGarantiaVencer > 0): ?>
        <a href="views/equipamentos/garantias_equipamentos.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo"><i class="fa-solid fa-shield"></i></div>
            <div>
                <span>Garantia a expirar</span>
                <h3><?php echo $totalGarantiaVencer; ?></h3>
                <p>Expira em menos de 30 dias</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalSemGarantiaData > 0): ?>
        <a href="views/equipamentos/garantias_equipamentos.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #c0392b;">
            <div class="indicador-icone indicador-vermelho"><i class="fa-solid fa-shield-halved"></i></div>
            <div>
                <span>Sem garantia registada</span>
                <h3><?php echo $totalSemGarantiaData; ?></h3>
                <p>Sem data de fim de garantia</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalManutencaoVencer > 0): ?>
        <a href="views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div>
                <span>Manutenção a vencer</span>
                <h3><?php echo $totalManutencaoVencer; ?></h3>
                <p>Prazo nos próximos 30 dias</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalManutencaoExpirada > 0): ?>
        <a href="views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #c0392b;">
            <div class="indicador-icone indicador-vermelho"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div>
                <span>Manutenção expirada</span>
                <h3><?php echo $totalManutencaoExpirada; ?></h3>
                <p>Prazo já ultrapassado</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalCalibracaoVencer > 0): ?>
        <a href="views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo"><i class="fa-solid fa-gauge-high"></i></div>
            <div>
                <span>Calibração a vencer</span>
                <h3><?php echo $totalCalibracaoVencer; ?></h3>
                <p>Prazo nos próximos 30 dias</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalCalibracaoExpirada > 0): ?>
        <a href="views/calibracao_manutencao/periodicidade.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #c0392b;">
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

    <!-- ── AVISOS ── -->
    <section class="dashboard-indicadores" style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem;">

        <?php if ($totalProcessos > 0): ?>
        <a href="views/calibracao_manutencao/aprovacao_processos.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <span>Processos para aprovar</span>
                <h3><?php echo $totalProcessos; ?></h3>
                <p>Calibrações e manutenções pendentes</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalTransferencias > 0): ?>
        <a href="views/mobilidade/transferencia.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #e67e22;">
            <div class="indicador-icone indicador-amarelo">
                <i class="fa-solid fa-right-left"></i>
            </div>
            <div>
                <span>Transferências pendentes</span>
                <h3><?php echo $totalTransferencias; ?></h3>
                <p>A aguardar aprovação</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalEmprestimosAtrasados > 0): ?>
        <a href="views/mobilidade/emprestimo.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #c0392b;">
            <div class="indicador-icone indicador-vermelho">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <span>Empréstimos em atraso</span>
                <h3><?php echo $totalEmprestimosAtrasados; ?></h3>
                <p>Prazo de devolução ultrapassado</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalSemCusto > 0): ?>
        <a href="views/equipamentos/preencher_equipamento.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #8e44ad;">
            <div class="indicador-icone indicador-roxo">
                <i class="fa-solid fa-euro-sign"></i>
            </div>
            <div>
                <span>Sem custo de aquisição</span>
                <h3><?php echo $totalSemCusto; ?></h3>
                <p>Equipamentos sem valor preenchido</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalSemContrato > 0): ?>
        <a href="views/equipamentos/preencher_equipamento.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #8e44ad;">
            <div class="indicador-icone indicador-roxo">
                <i class="fa-solid fa-file-contract"></i>
            </div>
            <div>
                <span>Sem contrato de aquisição</span>
                <h3><?php echo $totalSemContrato; ?></h3>
                <p>Documento em falta</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalSemGarantia > 0): ?>
        <a href="views/equipamentos/preencher_equipamento.php" class="indicador-card indicador-card-aviso text-decoration-none" style="border-left:4px solid #8e44ad;">
            <div class="indicador-icone indicador-roxo">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <span>Sem contrato de garantia</span>
                <h3><?php echo $totalSemGarantia; ?></h3>
                <p>Documento em falta</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($totalProcessos === 0 && $totalTransferencias === 0 && $totalEmprestimosAtrasados === 0 && $totalSemCusto === 0 && $totalSemContrato === 0 && $totalSemGarantia === 0): ?>
        <div class="indicador-card" style="border-left:4px solid #27ae60;">
            <div class="indicador-icone indicador-verde">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>Tudo em ordem</span>
                <h3>0</h3>
                <p>Sem avisos pendentes</p>
            </div>
        </div>
        <?php endif; ?>

    </section>

    <!-- ── GRÁFICOS ── -->
    <section class="dashboard-graficos" style="grid-template-columns: repeat(auto-fit, minmax(340px,1fr));">

        <div class="grafico-card">
            <h4><i class="fa-solid fa-chart-column me-2"></i>Gastos Anuais de Aquisição (€)</h4>
            <?php if (empty($dadosAquisicao['anos'])): ?>
                <p class="text-muted mt-3">Sem dados de aquisição registados.</p>
            <?php else: ?>
                <canvas id="graficoAquisicao"></canvas>
            <?php endif; ?>
        </div>

        <div class="grafico-card">
            <h4><i class="fa-solid fa-chart-column me-2"></i>Gastos Anuais de Manutenção (€)</h4>
            <?php if (empty($dadosManutencao['anos'])): ?>
                <p class="text-muted mt-3">Sem custos de manutenção registados.</p>
            <?php else: ?>
                <canvas id="graficoManutencao"></canvas>
            <?php endif; ?>
        </div>

        <div class="grafico-card">
            <h4><i class="fa-solid fa-chart-column me-2"></i>Gastos Anuais de Calibração (€)</h4>
            <?php if (empty($dadosCalibracao['anos'])): ?>
                <p class="text-muted mt-3">Sem custos de calibração registados.</p>
            <?php else: ?>
                <canvas id="graficoCalibracao"></canvas>
            <?php endif; ?>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
const opcoesGrafico = (label, cor) => ({
    responsive: true,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: ctx => ' ' + ctx.parsed.y.toLocaleString('pt-PT', { minimumFractionDigits: 2 }) + ' €'
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: v => v.toLocaleString('pt-PT') + ' €'
            }
        }
    }
});

<?php if (!empty($dadosAquisicao['anos'])): ?>
new Chart(document.getElementById('graficoAquisicao'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dadosAquisicao['anos']); ?>,
        datasets: [{
            label: 'Aquisição',
            data: <?php echo json_encode($dadosAquisicao['totais']); ?>,
            backgroundColor: 'rgba(26,115,232,0.7)',
            borderColor: 'rgba(26,115,232,1)',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: opcoesGrafico('Aquisição', '#1a73e8')
});
<?php endif; ?>

<?php if (!empty($dadosManutencao['anos'])): ?>
new Chart(document.getElementById('graficoManutencao'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dadosManutencao['anos']); ?>,
        datasets: [{
            label: 'Manutenção',
            data: <?php echo json_encode($dadosManutencao['totais']); ?>,
            backgroundColor: 'rgba(230,126,34,0.7)',
            borderColor: 'rgba(230,126,34,1)',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: opcoesGrafico('Manutenção', '#e67e22')
});
<?php endif; ?>

<?php if (!empty($dadosCalibracao['anos'])): ?>
new Chart(document.getElementById('graficoCalibracao'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dadosCalibracao['anos']); ?>,
        datasets: [{
            label: 'Calibração',
            data: <?php echo json_encode($dadosCalibracao['totais']); ?>,
            backgroundColor: 'rgba(39,174,96,0.7)',
            borderColor: 'rgba(39,174,96,1)',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: opcoesGrafico('Calibração', '#27ae60')
});
<?php endif; ?>
</script>
