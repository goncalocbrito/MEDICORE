<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }

function textoEstado($estado) {
    $m = ['ativo'=>'Ativo','avariado'=>'Avariado','em_manutencao'=>'Em manutenção','em_calibracao'=>'Em calibração','inativo'=>'Inativo','abatido'=>'Abatido'];
    return $m[$estado] ?? $estado;
}

function classeEstado($estado) {
    switch ($estado) {
        case 'ativo':          return 'estado-ativo';
        case 'em_manutencao':
        case 'em_calibracao':  return 'estado-manutencao';
        case 'avariado':       return 'estado-avariado';
        default:               return 'estado-inativo';
    }
}

$intervencoesPendentes = [];
$intervencoesExpiradas = [];
$erro_bd = '';

$mesesCase = "CASE e.periodicidade_manutencao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END";
$mesesCaseC = "CASE e.periodicidade_calibracao WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 WHEN 'bienal' THEN 24 WHEN 'trienal' THEN 36 END";

try {
    $pdo = medicore_pdo();

    // Subquery base para manutenção
    $sqlManutComProcesso = "
        SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, e.estado,
               l.departamento_sigla, l.sala,
               'Manutenção' AS tipo,
               m.proxima_manutencao AS proxima_data
        FROM (SELECT id_equipamento, MAX(data_manutencao) AS ultima FROM manutencoes_equipamento WHERE isActive=1 GROUP BY id_equipamento) ult
        INNER JOIN manutencoes_equipamento m ON m.id_equipamento=ult.id_equipamento AND m.data_manutencao=ult.ultima AND m.isActive=1
        INNER JOIN equipamentos e ON e.id_equipamento=m.id_equipamento AND e.isActive=1
        INNER JOIN localizacoes l ON l.id_localizacao=e.id_localizacao
        WHERE m.proxima_manutencao IS NOT NULL
    ";

    $sqlManutSemProcesso = "
        SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, e.estado,
               l.departamento_sigla, l.sala,
               'Manutenção' AS tipo,
               DATE_ADD(e.data_aquisicao, INTERVAL $mesesCase MONTH) AS proxima_data
        FROM equipamentos e
        INNER JOIN localizacoes l ON l.id_localizacao=e.id_localizacao
        WHERE e.isActive=1
          AND e.periodicidade_manutencao IS NOT NULL
          AND e.data_aquisicao IS NOT NULL
          AND NOT EXISTS (SELECT 1 FROM manutencoes_equipamento m2 WHERE m2.id_equipamento=e.id_equipamento AND m2.isActive=1)
    ";

    $sqlCalibComProcesso = "
        SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, e.estado,
               l.departamento_sigla, l.sala,
               'Calibração' AS tipo,
               c.proxima_calibracao AS proxima_data
        FROM (SELECT id_equipamento, MAX(data_calibracao) AS ultima FROM calibracoes_equipamento WHERE isActive=1 GROUP BY id_equipamento) ult
        INNER JOIN calibracoes_equipamento c ON c.id_equipamento=ult.id_equipamento AND c.data_calibracao=ult.ultima AND c.isActive=1
        INNER JOIN equipamentos e ON e.id_equipamento=c.id_equipamento AND e.isActive=1
        INNER JOIN localizacoes l ON l.id_localizacao=e.id_localizacao
        WHERE c.proxima_calibracao IS NOT NULL
    ";

    $sqlCalibSemProcesso = "
        SELECT e.id_equipamento, e.codigo_equipamento, e.designacao, e.estado,
               l.departamento_sigla, l.sala,
               'Calibração' AS tipo,
               DATE_ADD(e.data_aquisicao, INTERVAL $mesesCaseC MONTH) AS proxima_data
        FROM equipamentos e
        INNER JOIN localizacoes l ON l.id_localizacao=e.id_localizacao
        WHERE e.isActive=1
          AND e.periodicidade_calibracao IS NOT NULL
          AND e.data_aquisicao IS NOT NULL
          AND NOT EXISTS (SELECT 1 FROM calibracoes_equipamento c2 WHERE c2.id_equipamento=e.id_equipamento AND c2.isActive=1)
    ";

    // A vencer: 0-30 dias
    $intervencoesPendentes = $pdo->query("
        SELECT *, DATEDIFF(proxima_data, CURDATE()) AS dias_restantes FROM (
            $sqlManutComProcesso AND m.proxima_manutencao >= CURDATE() AND m.proxima_manutencao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            $sqlManutSemProcesso AND DATE_ADD(e.data_aquisicao, INTERVAL $mesesCase MONTH) >= CURDATE() AND DATE_ADD(e.data_aquisicao, INTERVAL $mesesCase MONTH) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            $sqlCalibComProcesso AND c.proxima_calibracao >= CURDATE() AND c.proxima_calibracao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            $sqlCalibSemProcesso AND DATE_ADD(e.data_aquisicao, INTERVAL $mesesCaseC MONTH) >= CURDATE() AND DATE_ADD(e.data_aquisicao, INTERVAL $mesesCaseC MONTH) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ) t ORDER BY proxima_data ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Expiradas: proxima_data < hoje
    $intervencoesExpiradas = $pdo->query("
        SELECT *, DATEDIFF(proxima_data, CURDATE()) AS dias_restantes FROM (
            $sqlManutComProcesso AND m.proxima_manutencao < CURDATE()
            UNION ALL
            $sqlManutSemProcesso AND DATE_ADD(e.data_aquisicao, INTERVAL $mesesCase MONTH) < CURDATE()
            UNION ALL
            $sqlCalibComProcesso AND c.proxima_calibracao < CURDATE()
            UNION ALL
            $sqlCalibSemProcesso AND DATE_ADD(e.data_aquisicao, INTERVAL $mesesCaseC MONTH) < CURDATE()
        ) t ORDER BY proxima_data DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    $erro_bd = 'Erro ao carregar dados da base de dados.';
    error_log('[periodicidade] ' . $e->getMessage());
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="titulo-pagina">Periodicidade de Intervenções</h2>
            <p class="subtitulo-pagina">Manutenções e calibrações pendentes ou com prazo ultrapassado.</p>
        </div>
    </div>

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?php echo h($erro_bd); ?></div>
    <?php endif; ?>

    <div class="ficha-area">
        <ul class="nav nav-tabs ficha-tabs" id="tabsPeriodicidade" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pendentes" type="button" role="tab">
                    <i class="fa-solid fa-clock me-2"></i>
                    A vencer (30 dias)
                    <?php if (!empty($intervencoesPendentes)): ?>
                        <span class="badge rounded-pill text-bg-warning ms-2"><?php echo count($intervencoesPendentes); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-expiradas" type="button" role="tab">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Expiradas
                    <?php if (!empty($intervencoesExpiradas)): ?>
                        <span class="badge rounded-pill text-bg-danger ms-2"><?php echo count($intervencoesExpiradas); ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <div class="tab-content ficha-tab-content">

            <!-- TAB: A VENCER -->
            <div class="tab-pane fade show active" id="tab-pendentes" role="tabpanel">
                <?php if (empty($intervencoesPendentes)): ?>
                    <p class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-check me-2" style="color:#27ae60;"></i>
                        Nenhuma intervenção a vencer nos próximos 30 dias.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="tabela-pendentes" class="table table-hover align-middle tabela-datatables-medicore">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Equipamento</th>
                                    <th>Localização</th>
                                    <th>Estado</th>
                                    <th class="text-center">Tipo</th>
                                    <th>Data Limite</th>
                                    <th class="text-center">Dias Restantes</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($intervencoesPendentes as $row): ?>
                                <tr>
                                    <td><?php echo h($row['codigo_equipamento']); ?></td>
                                    <td><?php echo h($row['designacao']); ?></td>
                                    <td><?php echo h($row['departamento_sigla'] . ' - Sala ' . $row['sala']); ?></td>
                                    <td>
                                        <span class="estado <?php echo h(classeEstado($row['estado'])); ?>">
                                            <?php echo h(textoEstado($row['estado'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="tipo-fornecedor <?php echo $row['tipo'] === 'Manutenção' ? 'tipo-manutencao' : 'tipo-calibracao'; ?>">
                                            <?php echo h($row['tipo']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo h(date('d/m/Y', strtotime($row['proxima_data']))); ?></td>
                                    <td class="text-center">
                                        <?php $dias = (int)$row['dias_restantes']; ?>
                                        <span class="estado <?php echo $dias <= 7 ? 'estado-avariado' : 'estado-manutencao'; ?>">
                                            <?php echo $dias === 0 ? 'Hoje' : ($dias === 1 ? '1 dia' : $dias . ' dias'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/ficha_equipamento.php?ref=<?php echo url_ref($row['id_equipamento']); ?>"
                                           class="btn btn-sm btn-ficha" title="Abrir ficha">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: EXPIRADAS -->
            <div class="tab-pane fade" id="tab-expiradas" role="tabpanel">
                <?php if (empty($intervencoesExpiradas)): ?>
                    <p class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-check me-2" style="color:#27ae60;"></i>
                        Nenhuma intervenção com prazo expirado.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="tabela-expiradas" class="table table-hover align-middle tabela-datatables-medicore">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Equipamento</th>
                                    <th>Localização</th>
                                    <th>Estado</th>
                                    <th class="text-center">Tipo</th>
                                    <th>Data Limite</th>
                                    <th class="text-center">Dias Expirado</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($intervencoesExpiradas as $row): ?>
                                <tr>
                                    <td><?php echo h($row['codigo_equipamento']); ?></td>
                                    <td><?php echo h($row['designacao']); ?></td>
                                    <td><?php echo h($row['departamento_sigla'] . ' - Sala ' . $row['sala']); ?></td>
                                    <td>
                                        <span class="estado <?php echo h(classeEstado($row['estado'])); ?>">
                                            <?php echo h(textoEstado($row['estado'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="tipo-fornecedor <?php echo $row['tipo'] === 'Manutenção' ? 'tipo-manutencao' : 'tipo-calibracao'; ?>">
                                            <?php echo h($row['tipo']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo h(date('d/m/Y', strtotime($row['proxima_data']))); ?></td>
                                    <td class="text-center">
                                        <span class="estado estado-avariado">
                                            <?php echo abs((int)$row['dias_restantes']); ?> dias
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/ficha_equipamento.php?ref=<?php echo url_ref($row['id_equipamento']); ?>"
                                           class="btn btn-sm btn-ficha" title="Abrir ficha">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
