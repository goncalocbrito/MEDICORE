<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   MEDICORE - Processos finalizados
   Junta manutenções e calibrações concluídas.
   ========================================================= */

if (!function_exists('h')) {
    function h($valor)
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

function formatar_data($data)
{
    if (empty($data)) {
        return '---';
    }

    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y', $timestamp) : $data;
}

function formatar_moeda($valor, $garantia = 0)
{
    if ((int) $garantia === 1) {
        return 'Garantia';
    }

    if ($valor === null || $valor === '') {
        return '---';
    }

    return number_format((float) $valor, 2, ',', '.') . ' €';
}

function texto_tipo_processo($origem, $tipo)
{
    if ($origem === 'calibracao') {
        return 'Calibração';
    }

    $tipos = [
        'preventiva' => 'Manutenção preventiva',
        'corretiva' => 'Manutenção corretiva'
    ];

    return $tipos[$tipo] ?? $tipo;
}

function classe_tipo_processo($origem, $tipo)
{
    if ($origem === 'calibracao') {
        return 'tipo-comercial';
    }

    return $tipo === 'corretiva' ? 'tipo-manutencao' : 'tipo-fabricante';
}

function texto_tipo_execucao($tipo)
{
    return $tipo === 'interna' ? 'Interna' : 'Externa';
}

function texto_resultado($origem, $resultado)
{
    if (empty($resultado)) {
        return '---';
    }

    $resultados = [
        'realizada' => 'Realizada',
        'realizada_com_observacoes' => 'Realizada com observações',
        'nao_realizada' => 'Não realizada',
        'aprovado' => 'Aprovado',
        'aprovado_com_restricoes' => 'Aprovado com restrições',
        'reprovado' => 'Reprovado'
    ];

    return $resultados[$resultado] ?? $resultado;
}

function classe_resultado($resultado)
{
    if (in_array($resultado, ['realizada', 'aprovado'], true)) {
        return 'estado-ativo';
    }

    if (in_array($resultado, ['realizada_com_observacoes', 'aprovado_com_restricoes'], true)) {
        return 'estado-manutencao';
    }

    if (in_array($resultado, ['nao_realizada', 'reprovado'], true)) {
        return 'estado-avariado';
    }

    return 'estado-inativo';
}

$pdo = null;
$erro_bd = '';
$processosFinalizados = [];

try {
    $pdo = new PDO(
        'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $sql = "
        SELECT * FROM (
            SELECT
                m.id_manutencao AS id_processo,
                m.codigo_processo,
                'manutencao' AS origem,
                m.tipo_manutencao AS tipo_processo,
                m.tipo_execucao,
                m.estado_processo,
                m.data_finalizacao,
                m.resultado,
                m.custo,
                m.coberta_por_garantia,
                m.id_equipamento,
                e.codigo_equipamento,
                e.designacao AS equipamento_nome,
                (
                    SELECT GROUP_CONCAT(CONCAT(e2.codigo_equipamento, '.', LPAD(a2.numero_sequencial, 3, '0'), ' - ', a2.designacao) SEPARATOR ' || ')
                    FROM manutencoes_acessorios ma2
                    INNER JOIN acessorios_equipamento a2 ON a2.id_acessorio = ma2.id_acessorio
                    INNER JOIN equipamentos e2 ON e2.id_equipamento = a2.id_equipamento
                    WHERE ma2.id_manutencao = m.id_manutencao
                    AND ma2.isActive = 1
                ) AS acessorios_associados,
                f.nome_empresa AS fornecedor_nome,
                l.codigo AS codigo_localizacao,
                l.departamento_nome,
                (
                    SELECT d.nome_documento
                    FROM documentos_equipamentos d
                    WHERE d.isActive = 1
                      AND d.id_manutencao = m.id_manutencao
                      AND d.tipo_documento = 'relatorio_manutencao'
                    ORDER BY d.criado_em DESC
                    LIMIT 1
                ) AS documento_nome,
                (
                    SELECT d.caminho_ficheiro
                    FROM documentos_equipamentos d
                    WHERE d.isActive = 1
                      AND d.id_manutencao = m.id_manutencao
                      AND d.tipo_documento = 'relatorio_manutencao'
                    ORDER BY d.criado_em DESC
                    LIMIT 1
                ) AS documento_caminho
            FROM manutencoes_equipamento m
            INNER JOIN equipamentos e
                ON e.id_equipamento = m.id_equipamento
            LEFT JOIN fornecedores f
                ON f.id_fornecedor = m.id_fornecedor_responsavel
            LEFT JOIN localizacoes l
                ON l.id_localizacao = e.id_localizacao
            WHERE m.isActive = 1
              AND m.estado_processo = 'processo_finalizado'

            UNION ALL

            SELECT
                c.id_calibracao AS id_processo,
                c.codigo_processo,
                'calibracao' AS origem,
                'calibracao' AS tipo_processo,
                c.tipo_execucao,
                c.estado_processo,
                c.data_finalizacao,
                c.resultado,
                c.custo,
                c.coberta_por_garantia,
                c.id_equipamento,
                e.codigo_equipamento,
                e.designacao AS equipamento_nome,
                (
                    SELECT GROUP_CONCAT(CONCAT(e2.codigo_equipamento, '.', LPAD(a2.numero_sequencial, 3, '0'), ' - ', a2.designacao) SEPARATOR ' || ')
                    FROM calibracoes_acessorios ca2
                    INNER JOIN acessorios_equipamento a2 ON a2.id_acessorio = ca2.id_acessorio
                    INNER JOIN equipamentos e2 ON e2.id_equipamento = a2.id_equipamento
                    WHERE ca2.id_calibracao = c.id_calibracao
                    AND ca2.isActive = 1
                ) AS acessorios_associados,
                f.nome_empresa AS fornecedor_nome,
                l.codigo AS codigo_localizacao,
                l.departamento_nome,
                (
                    SELECT d.nome_documento
                    FROM documentos_equipamentos d
                    WHERE d.isActive = 1
                      AND d.id_calibracao = c.id_calibracao
                      AND d.tipo_documento = 'certificado_calibracao'
                    ORDER BY d.criado_em DESC
                    LIMIT 1
                ) AS documento_nome,
                (
                    SELECT d.caminho_ficheiro
                    FROM documentos_equipamentos d
                    WHERE d.isActive = 1
                      AND d.id_calibracao = c.id_calibracao
                      AND d.tipo_documento = 'certificado_calibracao'
                    ORDER BY d.criado_em DESC
                    LIMIT 1
                ) AS documento_caminho
            FROM calibracoes_equipamento c
            INNER JOIN equipamentos e
                ON e.id_equipamento = c.id_equipamento
            LEFT JOIN fornecedores f
                ON f.id_fornecedor = c.id_fornecedor_responsavel
            LEFT JOIN localizacoes l
                ON l.id_localizacao = e.id_localizacao
            WHERE c.isActive = 1
              AND c.estado_processo = 'processo_finalizado'
        ) processos
        ORDER BY data_finalizacao DESC, codigo_processo DESC
    ";

    $processosFinalizados = $pdo->query($sql)->fetchAll();
} catch (Throwable $e) {
    $erro_bd = $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Processos Finalizados</h2>
            <p class="subtitulo-pagina">
                Histórico de manutenções e calibrações concluídas nos equipamentos e acessórios.
            </p>
        </div>

    </div>

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?php echo h($erro_bd); ?>
        </div>
    <?php endif; ?>
<div class="table-responsive tabela-container">
        <table id="tabela-processos-finalizados" class="table table-hover align-middle tabela-equipamentos tabela-processos-finalizados tabela-calibracoes-manutencoes tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Alvo</th>
                    <th>Associado a</th>
                    <th>Procedimento</th>
                    <th>Execução</th>
                    <th>Responsável</th>
                    <th>Conclusão</th>
                    <th>Resultado</th>
                    <th>Custo</th>
                    <th>Documento</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($processosFinalizados)): ?>
                    <?php foreach ($processosFinalizados as $processo): ?>
                        <?php
                            $alvoCodigo = $processo['codigo_equipamento'] ?? '---';
                            $alvoNome = $processo['equipamento_nome'] ?? 'Equipamento';

                            $acessoriosAssociados = !empty($processo['acessorios_associados'])
                                ? explode(' || ', $processo['acessorios_associados'])
                                : [];

                            $associadoA = empty($acessoriosAssociados) ? 'Equipamento principal' : null;
                        ?>
                        <tr>
                            <td><strong><?php echo h($processo['codigo_processo'] ?: '---'); ?></strong></td>
                            <td>
                                <strong><?php echo h($alvoCodigo); ?></strong><br>
                                <small class="text-muted"><?php echo h($alvoNome); ?></small>
                            </td>
                            <td>
                                <?php if (empty($acessoriosAssociados)): ?>
                                    <?php echo h($associadoA); ?>
                                <?php else: ?>
                                    <?php foreach ($acessoriosAssociados as $acessorioAssociado): ?>
                                        <div><?php echo h($acessorioAssociado); ?></div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="tipo-fornecedor <?php echo h(classe_tipo_processo($processo['origem'], $processo['tipo_processo'])); ?>">
                                    <?php echo h(texto_tipo_processo($processo['origem'], $processo['tipo_processo'])); ?>
                                </span>
                            </td>
                            <td><?php echo h(texto_tipo_execucao($processo['tipo_execucao'] ?? 'externa')); ?></td>
                            <td><?php echo h($processo['fornecedor_nome'] ?? '---'); ?></td>
                            <td><?php echo h(formatar_data($processo['data_finalizacao'])); ?></td>
                            <td>
                                <span class="estado <?php echo h(classe_resultado($processo['resultado'])); ?>">
                                    <?php echo h(texto_resultado($processo['origem'], $processo['resultado'])); ?>
                                </span>
                            </td>
                            <td><?php echo h(formatar_moeda($processo['custo'], $processo['coberta_por_garantia'])); ?></td>
                            <td>
                                <?php if (!empty($processo['documento_caminho'])): ?>
                                    <a href="../../assets/documentos/<?php echo h($processo['documento_caminho']); ?>" target="_blank" class="btn btn-sm btn-documento-ver">
                                        <i class="fa-solid fa-file-lines me-1"></i>
                                        <?php echo h($processo['documento_nome'] ?: 'Documento'); ?>
                                    </a>
                                <?php else: ?>
                                    ---
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-ficha" title="Abrir detalhe"
                                   href="detalhe_processo.php?ref=<?php echo processo_ref($processo['origem'], $processo['id_processo']); ?>">
                                    <i class="fa-solid fa-file-lines"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
