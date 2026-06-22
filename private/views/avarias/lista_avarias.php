<?php
require_once __DIR__ . '/../../includes/funcoes.php';

redirect_if_not_logged();

/* =========================================================
   LISTA DE AVARIAS REPORTADAS
   Mostra as avarias criadas pelo engenheiro e permite
   acompanhar se já foram convertidas em manutenção corretiva.
   ========================================================= */

if (($_SESSION['tipo_utilizador'] ?? '') !== 'Engenheiro') {
    header('Location: ' . rota_inicial_utilizador());
    exit;
}

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function texto_estado_avaria($estado)
{
    switch ($estado) {
        case 'reportada':
            return 'Reportada';
        case 'em_analise':
            return 'Em análise';
        case 'convertida_manutencao':
            return 'Convertida em manutenção';
        case 'cancelada':
            return 'Cancelada';
        default:
            return '---';
    }
}

function classe_estado_avaria($estado)
{
    switch ($estado) {
        case 'reportada':
            return 'estado-manutencao';
        case 'em_analise':
            return 'estado-calibracao';
        case 'convertida_manutencao':
            return 'estado-ativo';
        case 'cancelada':
            return 'estado-avariado';
        default:
            return 'estado-inativo';
    }
}

$pdo = medicore_pdo();
$avarias = [];
$mensagemErro = '';

try {
    $stmt = $pdo->prepare("
        SELECT
            a.id_avaria,
            a.codigo_avaria,
            a.descricao_avaria,
            a.estado,
            a.id_manutencao,
            a.data_reporte,

            e.codigo_equipamento,
            e.designacao AS equipamento_nome,

            ac.id_acessorio,
            ac.designacao AS acessorio_nome,
            CONCAT(e.codigo_equipamento, '.', LPAD(ac.numero_sequencial, 3, '0')) AS codigo_acessorio,

            u.nome AS utilizador_nome
        FROM avarias_reportadas a
        INNER JOIN equipamentos e
            ON e.id_equipamento = a.id_equipamento
        LEFT JOIN acessorios_equipamento ac
            ON ac.id_acessorio = a.id_acessorio
        INNER JOIN utilizadores u
            ON u.id_utilizador = a.id_utilizador_reportou
        WHERE a.isActive = 1
        ORDER BY a.data_reporte DESC, a.id_avaria DESC
    ");

    $stmt->execute();
    $avarias = $stmt->fetchAll();
} catch (Throwable $e) {
    $mensagemErro = $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Avarias Reportadas</h2>
            <p class="subtitulo-pagina">
                Consulta das avarias reportadas nos equipamentos e acessórios hospitalares.
            </p>
        </div>

        <a href="nova_avaria.php" class="btn btn-adicionar">
            <i class="fa-solid fa-plus me-2"></i>
            Nova Avaria
        </a>
    </div>

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?php echo h($mensagemErro); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive tabela-container">
        <table id="tabela-avarias" class="table table-hover align-middle tabela-equipamentos tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Equipamento</th>
                    <th>Acessório</th>
                    <th>Reportado por</th>
                    <th>Data</th>
                    <th>Estado</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($avarias as $avaria): ?>
                    <tr>
                        <td>
                            <strong><?php echo h($avaria['codigo_avaria']); ?></strong>
                        </td>

                        <td>
                            <strong><?php echo h($avaria['codigo_equipamento']); ?></strong><br>
                            <small class="text-muted"><?php echo h($avaria['equipamento_nome']); ?></small>
                        </td>

                        <td>
                            <?php if (!empty($avaria['id_acessorio'])): ?>
                                <strong><?php echo h($avaria['codigo_acessorio']); ?></strong><br>
                                <small class="text-muted"><?php echo h($avaria['acessorio_nome']); ?></small>
                            <?php else: ?>
                                Equipamento principal
                            <?php endif; ?>
                        </td>

                        <td><?php echo h($avaria['utilizador_nome']); ?></td>

                        <td><?php echo h(date('d/m/Y H:i', strtotime($avaria['data_reporte']))); ?></td>

                        <td>
                            <span class="estado <?php echo h(classe_estado_avaria($avaria['estado'])); ?>">
                                <?php echo h(texto_estado_avaria($avaria['estado'])); ?>
                            </span>
                        </td>

                        <td class="text-center">
                            <div class="acoes-operacao">
                                <?php if ($avaria['estado'] === 'reportada'): ?>
                                    <a href="../calibracao_manutencao/calibracao_manutencao.php?avaria=<?php echo url_ref($avaria['id_avaria']); ?>"
                                       class="btn-acao-circular btn-acao-aprovar"
                                       title="Criar manutenção corretiva">
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                    </a>
                                <?php elseif (!empty($avaria['id_manutencao'])): ?>
                                    <a href="../calibracao_manutencao/detalhe_processo.php?ref=<?php echo processo_ref('manutencao', $avaria['id_manutencao']); ?>"
                                       class="btn-acao-circular btn-acao-detalhe"
                                       title="Ver manutenção associada">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </a>
                                <?php else: ?>
                                    ---
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>