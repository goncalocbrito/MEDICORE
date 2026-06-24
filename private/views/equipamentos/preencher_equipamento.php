<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$ehAdministrador = ($_SESSION['tipo_utilizador'] ?? '') === 'Administrador';

if (!$ehAdministrador) {
    header('Location: lista_equipamentos.php');
    exit;
}

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function textoEstadoEquipamento($estado)
{
    $estados = [
        'ativo'          => 'Ativo',
        'avariado'       => 'Avariado',
        'em_manutencao'  => 'Em manutenção',
        'em_calibracao'  => 'Em calibração',
        'inativo'        => 'Inativo',
        'abatido'        => 'Abatido'
    ];
    return $estados[$estado] ?? $estado;
}

function classeEstadoEquipamento($estado)
{
    switch ($estado) {
        case 'ativo':          return 'estado-ativo';
        case 'em_manutencao':
        case 'em_calibracao':  return 'estado-manutencao';
        case 'avariado':       return 'estado-avariado';
        case 'inativo':        return 'estado-inativo';
        case 'abatido':        return 'estado-abatido';
        default:               return 'estado-inativo';
    }
}

$equipamentos = [];
$erro_bd = '';

try {
    $pdo = medicore_pdo();

    $stmt = $pdo->query("
        SELECT
            e.id_equipamento,
            e.codigo_equipamento,
            e.designacao,
            e.estado,
            e.valor_aquisicao,

            l.departamento_sigla,
            l.sala,

            (SELECT COUNT(*) FROM documentos_equipamentos d
             WHERE d.id_equipamento = e.id_equipamento
               AND d.tipo_documento = 'contrato'
               AND d.isActive = 1) AS tem_contrato,

            (SELECT COUNT(*) FROM documentos_equipamentos d
             WHERE d.id_equipamento = e.id_equipamento
               AND d.tipo_documento = 'garantia'
               AND d.isActive = 1) AS tem_garantia

        FROM equipamentos e

        INNER JOIN localizacoes l
            ON l.id_localizacao = e.id_localizacao

        WHERE e.isActive = 1

        ORDER BY e.codigo_equipamento ASC
    ");

    $equipamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    $erro_bd = 'Erro ao carregar equipamentos da base de dados.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="titulo-pagina">Preencher Equipamentos</h2>
            <p class="subtitulo-pagina">
                Verifique quais os equipamentos com custo de aquisição, contrato e garantia por preencher.
            </p>
        </div>
    </div>

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?php echo h($erro_bd); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive tabela-container">
        <table id="tabela-preencher-equipamento" class="table table-hover align-middle tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Equipamento</th>
                    <th>Localização</th>
                    <th>Estado</th>
                    <th class="text-center">Custo</th>
                    <th class="text-center">Contrato Aquisição</th>
                    <th class="text-center">Garantia</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipamentos as $eq):
                    $localizacao   = h($eq['departamento_sigla']) . ' - Sala ' . h($eq['sala']);
                    $estadoTexto   = textoEstadoEquipamento($eq['estado']);
                    $temCusto      = $eq['valor_aquisicao'] !== null;
                    $temContrato   = (int)$eq['tem_contrato'] > 0;
                    $temGarantia   = (int)$eq['tem_garantia'] > 0;
                ?>
                <tr>
                    <td><?php echo h($eq['codigo_equipamento']); ?></td>
                    <td><?php echo h($eq['designacao']); ?></td>
                    <td><?php echo $localizacao; ?></td>
                    <td>
                        <span class="estado <?php echo h(classeEstadoEquipamento($eq['estado'])); ?>">
                            <?php echo h($estadoTexto); ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($temCusto): ?>
                            <span class="badge" style="background-color:#27ae60; font-size:0.78rem;">
                                <i class="fa-solid fa-check me-1"></i>Preenchido
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background-color:#c0392b; font-size:0.78rem;">
                                <i class="fa-solid fa-xmark me-1"></i>Por preencher
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($temContrato): ?>
                            <span class="badge" style="background-color:#27ae60; font-size:0.78rem;">
                                <i class="fa-solid fa-check me-1"></i>Preenchido
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background-color:#c0392b; font-size:0.78rem;">
                                <i class="fa-solid fa-xmark me-1"></i>Por preencher
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($temGarantia): ?>
                            <span class="badge" style="background-color:#27ae60; font-size:0.78rem;">
                                <i class="fa-solid fa-check me-1"></i>Preenchido
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background-color:#c0392b; font-size:0.78rem;">
                                <i class="fa-solid fa-xmark me-1"></i>Por preencher
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a
                            href="ficha_equipamento.php?ref=<?php echo url_ref($eq['id_equipamento']); ?>"
                            class="btn btn-sm btn-ficha"
                            title="Abrir ficha do equipamento">
                            <i class="fa-solid fa-file-lines"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
