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

$garantiasVencer  = [];
$garantiasExpiradas = [];
$erro_bd = '';

try {
    $pdo = medicore_pdo();

    $sqlBase = "
        SELECT
            e.id_equipamento,
            e.codigo_equipamento,
            e.designacao,
            e.estado,
            l.departamento_sigla,
            l.sala,
            ef.data_inicio_garantia,
            ef.data_fim_garantia,
            f.nome_empresa AS fornecedor_garantia,
            DATEDIFF(ef.data_fim_garantia, CURDATE()) AS dias_restantes
        FROM equipamentos e
        INNER JOIN localizacoes l ON l.id_localizacao = e.id_localizacao
        INNER JOIN (
            SELECT id_equipamento, MAX(data_fim_garantia) AS data_fim_garantia, data_inicio_garantia, id_fornecedor_garantia
            FROM equipamentos_fornecedores
            WHERE isActive = 1 AND data_fim_garantia IS NOT NULL
            GROUP BY id_equipamento
        ) ef ON ef.id_equipamento = e.id_equipamento
        LEFT JOIN fornecedores f ON f.id_fornecedor = ef.id_fornecedor_garantia
        WHERE e.isActive = 1
    ";

    $garantiasVencer = $pdo->query($sqlBase . "
        AND ef.data_fim_garantia >= CURDATE()
        AND ef.data_fim_garantia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY ef.data_fim_garantia ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $garantiasExpiradas = $pdo->query($sqlBase . "
        AND ef.data_fim_garantia < CURDATE()
        ORDER BY ef.data_fim_garantia DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    $erro_bd = 'Erro ao carregar dados da base de dados.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="titulo-pagina">Garantias de Equipamentos</h2>
            <p class="subtitulo-pagina">Acompanhamento das garantias ativas e expiradas dos equipamentos.</p>
        </div>
    </div>

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?php echo h($erro_bd); ?></div>
    <?php endif; ?>

    <div class="ficha-area">
        <ul class="nav nav-tabs ficha-tabs" id="tabsGarantias" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-vencer" type="button" role="tab">
                    <i class="fa-solid fa-clock me-2"></i>
                    A expirar
                    <?php if (!empty($garantiasVencer)): ?>
                        <span class="badge rounded-pill text-bg-warning ms-2"><?php echo count($garantiasVencer); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-expiradas" type="button" role="tab">
                    <i class="fa-solid fa-shield-halved me-2"></i>
                    Expiradas
                    <?php if (!empty($garantiasExpiradas)): ?>
                        <span class="badge rounded-pill text-bg-danger ms-2"><?php echo count($garantiasExpiradas); ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <div class="tab-content ficha-tab-content">

            <!-- TAB: A EXPIRAR -->
            <div class="tab-pane fade show active" id="tab-vencer" role="tabpanel">
                <?php if (empty($garantiasVencer)): ?>
                    <p class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-check me-2" style="color:#27ae60;"></i>
                        Nenhuma garantia a expirar nos próximos 30 dias.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="tabela-garantias-vencer" class="table table-hover align-middle tabela-datatables-medicore">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Equipamento</th>
                                    <th>Localização</th>
                                    <th>Estado</th>
                                    <th>Fornecedor Garantia</th>
                                    <th>Início Garantia</th>
                                    <th>Fim Garantia</th>
                                    <th class="text-center">Dias Restantes</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($garantiasVencer as $eq): ?>
                                <tr>
                                    <td><?php echo h($eq['codigo_equipamento']); ?></td>
                                    <td><?php echo h($eq['designacao']); ?></td>
                                    <td><?php echo h($eq['departamento_sigla'] . ' - Sala ' . $eq['sala']); ?></td>
                                    <td>
                                        <span class="estado <?php echo h(classeEstado($eq['estado'])); ?>">
                                            <?php echo h(textoEstado($eq['estado'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo h($eq['fornecedor_garantia'] ?: '---'); ?></td>
                                    <td><?php echo $eq['data_inicio_garantia'] ? h(date('d/m/Y', strtotime($eq['data_inicio_garantia']))) : '---'; ?></td>
                                    <td><?php echo h(date('d/m/Y', strtotime($eq['data_fim_garantia']))); ?></td>
                                    <td class="text-center">
                                        <?php $dias = (int)$eq['dias_restantes']; ?>
                                        <span class="estado <?php echo $dias <= 7 ? 'estado-avariado' : 'estado-manutencao'; ?>">
                                            <?php echo $dias === 0 ? 'Hoje' : ($dias === 1 ? '1 dia' : $dias . ' dias'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="ficha_equipamento.php?ref=<?php echo url_ref($eq['id_equipamento']); ?>"
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
                <?php if (empty($garantiasExpiradas)): ?>
                    <p class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-check me-2" style="color:#27ae60;"></i>
                        Nenhuma garantia expirada.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="tabela-garantias-expiradas" class="table table-hover align-middle tabela-datatables-medicore">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Equipamento</th>
                                    <th>Localização</th>
                                    <th>Estado</th>
                                    <th>Fornecedor Garantia</th>
                                    <th>Início Garantia</th>
                                    <th>Fim Garantia</th>
                                    <th class="text-center">Dias Expirado</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($garantiasExpiradas as $eq): ?>
                                <tr>
                                    <td><?php echo h($eq['codigo_equipamento']); ?></td>
                                    <td><?php echo h($eq['designacao']); ?></td>
                                    <td><?php echo h($eq['departamento_sigla'] . ' - Sala ' . $eq['sala']); ?></td>
                                    <td>
                                        <span class="estado <?php echo h(classeEstado($eq['estado'])); ?>">
                                            <?php echo h(textoEstado($eq['estado'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo h($eq['fornecedor_garantia'] ?: '---'); ?></td>
                                    <td><?php echo $eq['data_inicio_garantia'] ? h(date('d/m/Y', strtotime($eq['data_inicio_garantia']))) : '---'; ?></td>
                                    <td><?php echo h(date('d/m/Y', strtotime($eq['data_fim_garantia']))); ?></td>
                                    <td class="text-center">
                                        <span class="estado estado-avariado">
                                            <?php echo abs((int)$eq['dias_restantes']); ?> dias
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="ficha_equipamento.php?ref=<?php echo url_ref($eq['id_equipamento']); ?>"
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
