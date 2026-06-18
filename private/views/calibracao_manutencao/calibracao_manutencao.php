<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   MEDICORE - Processos abertos de manutenção e calibração
   Usa:
   - manutencoes_equipamento
   - calibracoes_equipamento
   - historico_etapas_processos
   ========================================================= */

if (!function_exists('h')) {
    function h($valor)
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

function valor_ou_null($valor)
{
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : $valor;
}

function decimal_ou_null($valor)
{
    $valor = trim((string) ($valor ?? ''));
    if ($valor === '') {
        return null;
    }

    return (float) str_replace(',', '.', $valor);
}

function data_ou_null($valor)
{
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : $valor;
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

function texto_estado_processo($estado)
{
    $estados = [
        'aguarda_recolha' => 'Aguarda recolha',
        'procedimento_a_decorrer' => 'Procedimento a decorrer',
        'procedimento_efetuado' => 'Procedimento efetuado',
        'emissao_relatorio' => 'Emissão do relatório',
        'processo_finalizado' => 'Processo finalizado',
        'cancelado' => 'Cancelado'
    ];

    return $estados[$estado] ?? $estado;
}

function classe_estado_processo($estado)
{
    switch ($estado) {
        case 'aguarda_recolha':
            return 'estado-manutencao';
        case 'procedimento_a_decorrer':
            return 'estado-manutencao';
        case 'procedimento_efetuado':
            return 'estado-ativo';
        case 'emissao_relatorio':
            return 'estado-manutencao';
        case 'processo_finalizado':
            return 'estado-ativo';
        case 'cancelado':
            return 'estado-inativo';
        default:
            return 'estado-inativo';
    }
}

function texto_tipo_processo($origem, $tipo)
{
    if ($origem === 'calibracao') {
        return 'Calibração';
    }

    $tipos = [
        'preventiva' => 'Manutenção preventiva',
        'corretiva' => 'Manutenção corretiva',
        'manutencao_preventiva' => 'Manutenção preventiva',
        'manutencao_corretiva' => 'Manutenção corretiva'
    ];

    return $tipos[$tipo] ?? $tipo;
}

function classe_tipo_processo($origem, $tipo)
{
    if ($origem === 'calibracao') {
        return 'tipo-comercial';
    }

    return $tipo === 'corretiva' || $tipo === 'manutencao_corretiva'
        ? 'tipo-manutencao'
        : 'tipo-fabricante';
}

function validar_tipo_execucao($tipo)
{
    return in_array($tipo, ['interna', 'externa'], true) ? $tipo : 'externa';
}

function texto_tipo_execucao($tipo)
{
    return $tipo === 'interna' ? 'Interna' : 'Externa';
}

function obter_nome_fornecedor(PDO $pdo, $idFornecedor)
{
    if (empty($idFornecedor)) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT nome_empresa FROM fornecedores WHERE id_fornecedor = :id LIMIT 1");
    $stmt->execute([':id' => $idFornecedor]);
    return $stmt->fetchColumn() ?: null;
}

function definir_estado_alvo(PDO $pdo, $idEquipamento, $idAcessorio, $estado)
{
    if (!empty($idAcessorio)) {
        $stmt = $pdo->prepare("UPDATE acessorios_equipamento SET estado = :estado WHERE id_acessorio = :id_acessorio");
        $stmt->execute([
            ':estado' => $estado,
            ':id_acessorio' => $idAcessorio
        ]);
        return;
    }

    $stmt = $pdo->prepare("UPDATE equipamentos SET estado = :estado WHERE id_equipamento = :id_equipamento");
    $stmt->execute([
        ':estado' => $estado,
        ':id_equipamento' => $idEquipamento
    ]);
}


function render_tabela_processos_abertos($processos, $tituloTabela, $idTabela)
{
    ?>
    <div class="secao-ficha-titulo">
        <h4><?php echo h($tituloTabela); ?></h4>
        <p>Processos ainda não finalizados nem cancelados.</p>
    </div>

    <div class="table-responsive tabela-container p-0">
        <table id="<?php echo h($idTabela); ?>" class="table table-hover align-middle tabela-equipamentos tabela-processos-abertos tabela-calibracoes-manutencoes tabela-datatables-medicore">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Alvo</th>
                    <th>Associado a</th>
                    <th>Procedimento</th>
                    <th>Execução</th>
                    <th>Data prevista</th>
                    <th>Etapa atual</th>
                    <th>Custo</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($processos)): ?>
                    <?php foreach ($processos as $processo): ?>
                        <?php
                            $alvoCodigo = !empty($processo['id_acessorio'])
                                ? ($processo['codigo_acessorio'] ?? '---')
                                : ($processo['codigo_equipamento'] ?? '---');

                            $alvoNome = !empty($processo['id_acessorio'])
                                ? ($processo['acessorio_nome'] ?? 'Acessório')
                                : ($processo['equipamento_nome'] ?? 'Equipamento');

                            $associadoA = !empty($processo['id_acessorio'])
                                ? (($processo['codigo_equipamento'] ?? '---') . ' - ' . ($processo['equipamento_nome'] ?? '---'))
                                : 'Equipamento principal';
                        ?>
                        <tr>
                            <td><strong><?php echo h($processo['codigo_processo'] ?: '---'); ?></strong></td>
                            <td>
                                <strong><?php echo h($alvoCodigo); ?></strong><br>
                                <small class="text-muted"><?php echo h($alvoNome); ?></small>
                            </td>
                            <td><?php echo h($associadoA); ?></td>
                            <td>
                                <span class="tipo-fornecedor <?php echo h(classe_tipo_processo($processo['origem'], $processo['tipo_processo'])); ?>">
                                    <?php echo h(texto_tipo_processo($processo['origem'], $processo['tipo_processo'])); ?>
                                </span>
                            </td>
                            <td><?php echo h(texto_tipo_execucao($processo['tipo_execucao'] ?? 'externa')); ?></td>
                            <td><?php echo h(formatar_data($processo['data_prevista'])); ?></td>
                            <td>
                                <span class="estado <?php echo h(classe_estado_processo($processo['estado_processo'])); ?>">
                                    <?php echo h(texto_estado_processo($processo['estado_processo'])); ?>
                                </span>
                            </td>
                            <td><?php echo h(formatar_moeda($processo['custo'], $processo['coberta_por_garantia'])); ?></td>
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
    <?php
}

$pdo = null;
$erro_bd = '';
$mensagem_sucesso = '';
$processosManutencao = [];
$processosCalibracao = [];
$equipamentos = [];
$acessorios = [];
$fornecedores = [];

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

    $utilizadorAtual = $_SESSION['nome'] ?? $_SESSION['username'] ?? 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'criar_processo') {
            $tipoPedido = $_POST['tipoProcesso'] ?? '';
            $idEquipamento = (int) ($_POST['idEquipamento'] ?? 0);
            $idAcessorio = !empty($_POST['idAcessorio']) ? (int) $_POST['idAcessorio'] : null;
            $tipoExecucao = validar_tipo_execucao($_POST['tipoExecucao'] ?? 'externa');
            $idFornecedor = !empty($_POST['idFornecedorResponsavel']) ? (int) $_POST['idFornecedorResponsavel'] : null;
            $tecnicoInterno = valor_ou_null($_POST['tecnicoInterno'] ?? null);
            $dataPrevista = data_ou_null($_POST['dataPrevista'] ?? null);
            $cobertaPorGarantia = (int) ($_POST['cobertaPorGarantia'] ?? 0);
            $custo = $cobertaPorGarantia === 1 ? 0.00 : decimal_ou_null($_POST['custoProcesso'] ?? null);
            $observacoes = valor_ou_null($_POST['observacoesProcesso'] ?? null);

            $erros = [];

            if ($idEquipamento <= 0) {
                $erros[] = 'Deve selecionar o equipamento associado ao processo.';
            }

            if (!in_array($tipoPedido, ['manutencao_preventiva', 'manutencao_corretiva', 'calibracao'], true)) {
                $erros[] = 'Tipo de processo inválido.';
            }

            if ($tipoExecucao === 'externa' && empty($idFornecedor)) {
                $erros[] = 'Nos processos externos deve indicar o fornecedor responsável.';
            }

            if ($tipoExecucao === 'interna' && empty($tecnicoInterno)) {
                $erros[] = 'Nos processos internos deve indicar o técnico interno responsável.';
            }

            if (!empty($erros)) {
                throw new Exception(implode(' ', $erros));
            }

            $pdo->beginTransaction();

            $estadoInicial = 'aguarda_recolha';
            $hoje = date('Y-m-d');
            $responsavelEtapa = $tecnicoInterno;
            $tipoResponsavel = null;
            $idFornecedorEtapa = null;

            if ($tipoExecucao === 'interna') {
                $tipoResponsavel = 'interno';
            } elseif ($tipoExecucao === 'externa') {
                $responsavelEtapa = obter_nome_fornecedor($pdo, $idFornecedor);
                $tipoResponsavel = 'fornecedor';
                $idFornecedorEtapa = $idFornecedor;
            }

            if ($tipoPedido === 'calibracao') {
                $stmt = $pdo->prepare("
                    INSERT INTO calibracoes_equipamento (
                        id_equipamento,
                        id_acessorio,
                        id_fornecedor_responsavel,
                        tipo_execucao,
                        estado_processo,
                        data_abertura,
                        data_prevista,
                        tecnico_interno,
                        data_calibracao,
                        proxima_calibracao,
                        numero_certificado,
                        resultado,
                        procedimento,
                        coberta_por_garantia,
                        custo,
                        observacoes,
                        atualizado_por
                    ) VALUES (
                        :id_equipamento,
                        :id_acessorio,
                        :id_fornecedor_responsavel,
                        :tipo_execucao,
                        :estado_processo,
                        :data_abertura,
                        :data_prevista,
                        :tecnico_interno,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        :coberta_por_garantia,
                        :custo,
                        :observacoes,
                        :atualizado_por
                    )
                ");

                $stmt->execute([
                    ':id_equipamento' => $idEquipamento,
                    ':id_acessorio' => $idAcessorio,
                    ':id_fornecedor_responsavel' => $idFornecedor,
                    ':tipo_execucao' => $tipoExecucao,
                    ':estado_processo' => $estadoInicial,
                    ':data_abertura' => $hoje,
                    ':data_prevista' => $dataPrevista,
                    ':tecnico_interno' => $tecnicoInterno,
                    ':coberta_por_garantia' => $cobertaPorGarantia,
                    ':custo' => $custo,
                    ':observacoes' => $observacoes,
                    ':atualizado_por' => $utilizadorAtual
                ]);

                $idProcesso = (int) $pdo->lastInsertId();
                $codigoProcesso = 'CAL-' . date('Y') . '-' . str_pad((string) $idProcesso, 4, '0', STR_PAD_LEFT);

                $stmtCodigo = $pdo->prepare("UPDATE calibracoes_equipamento SET codigo_processo = :codigo WHERE id_calibracao = :id");
                $stmtCodigo->execute([
                    ':codigo' => $codigoProcesso,
                    ':id' => $idProcesso
                ]);

                $stmtHistorico = $pdo->prepare("
                    INSERT INTO historico_etapas_processos (
                        tipo_processo,
                        id_manutencao,
                        id_calibracao,
                        estado_anterior,
                        estado_novo,
                        responsavel_etapa,
                        tipo_responsavel,
                        id_fornecedor_responsavel,
                        observacoes,
                        atualizado_por
                    ) VALUES (
                        'calibracao',
                        NULL,
                        :id_calibracao,
                        NULL,
                        :estado_novo,
                        :responsavel_etapa,
                        :tipo_responsavel,
                        :id_fornecedor_responsavel,
                        :observacoes,
                        :atualizado_por
                    )
                ");
                $stmtHistorico->execute([
                    ':id_calibracao' => $idProcesso,
                    ':estado_novo' => $estadoInicial,
                    ':responsavel_etapa' => $responsavelEtapa,
                    ':tipo_responsavel' => $tipoResponsavel,
                    ':id_fornecedor_responsavel' => $idFornecedorEtapa,
                    ':observacoes' => 'Processo de calibração aberto.',
                    ':atualizado_por' => $utilizadorAtual
                ]);

                definir_estado_alvo($pdo, $idEquipamento, $idAcessorio, 'em_calibracao');
                $mensagem_sucesso = 'Processo de calibração aberto com sucesso.';
            } else {
                $tipoManutencao = $tipoPedido === 'manutencao_corretiva' ? 'corretiva' : 'preventiva';

                $stmt = $pdo->prepare("
                    INSERT INTO manutencoes_equipamento (
                        id_equipamento,
                        id_acessorio,
                        tipo_manutencao,
                        tipo_execucao,
                        estado_processo,
                        data_abertura,
                        data_prevista,
                        id_fornecedor_responsavel,
                        tecnico_interno,
                        data_manutencao,
                        proxima_manutencao,
                        numero_relatorio,
                        descricao_procedimento,
                        resultado,
                        coberta_por_garantia,
                        custo,
                        observacoes,
                        atualizado_por
                    ) VALUES (
                        :id_equipamento,
                        :id_acessorio,
                        :tipo_manutencao,
                        :tipo_execucao,
                        :estado_processo,
                        :data_abertura,
                        :data_prevista,
                        :id_fornecedor_responsavel,
                        :tecnico_interno,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        :coberta_por_garantia,
                        :custo,
                        :observacoes,
                        :atualizado_por
                    )
                ");

                $stmt->execute([
                    ':id_equipamento' => $idEquipamento,
                    ':id_acessorio' => $idAcessorio,
                    ':tipo_manutencao' => $tipoManutencao,
                    ':tipo_execucao' => $tipoExecucao,
                    ':estado_processo' => $estadoInicial,
                    ':data_abertura' => $hoje,
                    ':data_prevista' => $dataPrevista,
                    ':id_fornecedor_responsavel' => $idFornecedor,
                    ':tecnico_interno' => $tecnicoInterno,
                    ':coberta_por_garantia' => $cobertaPorGarantia,
                    ':custo' => $custo,
                    ':observacoes' => $observacoes,
                    ':atualizado_por' => $utilizadorAtual
                ]);

                $idProcesso = (int) $pdo->lastInsertId();
                $codigoProcesso = 'MAN-' . date('Y') . '-' . str_pad((string) $idProcesso, 4, '0', STR_PAD_LEFT);

                $stmtCodigo = $pdo->prepare("UPDATE manutencoes_equipamento SET codigo_processo = :codigo WHERE id_manutencao = :id");
                $stmtCodigo->execute([
                    ':codigo' => $codigoProcesso,
                    ':id' => $idProcesso
                ]);

                $stmtHistorico = $pdo->prepare("
                    INSERT INTO historico_etapas_processos (
                        tipo_processo,
                        id_manutencao,
                        id_calibracao,
                        estado_anterior,
                        estado_novo,
                        responsavel_etapa,
                        tipo_responsavel,
                        id_fornecedor_responsavel,
                        observacoes,
                        atualizado_por
                    ) VALUES (
                        'manutencao',
                        :id_manutencao,
                        NULL,
                        NULL,
                        :estado_novo,
                        :responsavel_etapa,
                        :tipo_responsavel,
                        :id_fornecedor_responsavel,
                        :observacoes,
                        :atualizado_por
                    )
                ");
                $stmtHistorico->execute([
                    ':id_manutencao' => $idProcesso,
                    ':estado_novo' => $estadoInicial,
                    ':responsavel_etapa' => $responsavelEtapa,
                    ':tipo_responsavel' => $tipoResponsavel,
                    ':id_fornecedor_responsavel' => $idFornecedorEtapa,
                    ':observacoes' => 'Processo de manutenção aberto.',
                    ':atualizado_por' => $utilizadorAtual
                ]);

                definir_estado_alvo($pdo, $idEquipamento, $idAcessorio, 'em_manutencao');
                $mensagem_sucesso = 'Processo de manutenção aberto com sucesso.';
            }

            $pdo->commit();
        }
    }

    $stmtEquipamentos = $pdo->query("
        SELECT id_equipamento, codigo_equipamento, designacao
        FROM equipamentos
        WHERE isActive = 1
        ORDER BY codigo_equipamento ASC
    ");
    $equipamentos = $stmtEquipamentos->fetchAll();

    $stmtAcessorios = $pdo->query("
        SELECT
            a.id_acessorio,
            a.id_equipamento,
            a.designacao,
            CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0')) AS codigo_acessorio
        FROM acessorios_equipamento a
        INNER JOIN equipamentos e
            ON e.id_equipamento = a.id_equipamento
        WHERE a.isActive = 1
        ORDER BY e.codigo_equipamento ASC, a.numero_sequencial ASC
    ");
    $acessorios = $stmtAcessorios->fetchAll();

    $stmtFornecedores = $pdo->query("
        SELECT id_fornecedor, nome_empresa, tipo_fornecedor
        FROM fornecedores
        WHERE isActive = 1
        ORDER BY nome_empresa ASC
    ");
    $fornecedores = $stmtFornecedores->fetchAll();

    $sqlManutencoes = "
        SELECT
            m.id_manutencao AS id_processo,
            m.codigo_processo,
            'manutencao' AS origem,
            m.tipo_manutencao AS tipo_processo,
            m.tipo_execucao,
            m.estado_processo,
            m.data_abertura,
            m.data_prevista,
            m.coberta_por_garantia,
            m.custo,
            m.id_equipamento,
            m.id_acessorio,
            e.codigo_equipamento,
            e.designacao AS equipamento_nome,
            a.designacao AS acessorio_nome,
            CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0')) AS codigo_acessorio,
            f.nome_empresa AS fornecedor_nome,
            l.codigo AS codigo_localizacao,
            l.departamento_nome,
            l.edificio,
            l.piso,
            l.sala
        FROM manutencoes_equipamento m
        INNER JOIN equipamentos e
            ON e.id_equipamento = m.id_equipamento
        LEFT JOIN acessorios_equipamento a
            ON a.id_acessorio = m.id_acessorio
        LEFT JOIN fornecedores f
            ON f.id_fornecedor = m.id_fornecedor_responsavel
        LEFT JOIN localizacoes l
            ON l.id_localizacao = COALESCE(a.id_localizacao, e.id_localizacao)
        WHERE m.isActive = 1
          AND m.estado_processo NOT IN ('processo_finalizado', 'cancelado')
        ORDER BY m.data_prevista ASC, m.criado_em DESC
    ";
    $processosManutencao = $pdo->query($sqlManutencoes)->fetchAll();

    $sqlCalibracoes = "
        SELECT
            c.id_calibracao AS id_processo,
            c.codigo_processo,
            'calibracao' AS origem,
            'calibracao' AS tipo_processo,
            c.tipo_execucao,
            c.estado_processo,
            c.data_abertura,
            c.data_prevista,
            c.coberta_por_garantia,
            c.custo,
            c.id_equipamento,
            c.id_acessorio,
            e.codigo_equipamento,
            e.designacao AS equipamento_nome,
            a.designacao AS acessorio_nome,
            CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0')) AS codigo_acessorio,
            f.nome_empresa AS fornecedor_nome,
            l.codigo AS codigo_localizacao,
            l.departamento_nome,
            l.edificio,
            l.piso,
            l.sala
        FROM calibracoes_equipamento c
        INNER JOIN equipamentos e
            ON e.id_equipamento = c.id_equipamento
        LEFT JOIN acessorios_equipamento a
            ON a.id_acessorio = c.id_acessorio
        LEFT JOIN fornecedores f
            ON f.id_fornecedor = c.id_fornecedor_responsavel
        LEFT JOIN localizacoes l
            ON l.id_localizacao = COALESCE(a.id_localizacao, e.id_localizacao)
        WHERE c.isActive = 1
          AND c.estado_processo NOT IN ('processo_finalizado', 'cancelado')
        ORDER BY c.data_prevista ASC, c.criado_em DESC
    ";
    $processosCalibracao = $pdo->query($sqlCalibracoes)->fetchAll();
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $erro_bd = $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Calibrações e Manutenções</h2>
            <p class="subtitulo-pagina">
                Gestão dos processos técnicos abertos, com separação entre manutenção e calibração.
            </p>
        </div>

        <button type="button"
                class="btn btn-adicionar"
                data-bs-toggle="modal"
                data-bs-target="#modalNovoProcesso">
            <i class="fa-solid fa-plus me-2"></i>
            Novo Processo
        </button>
    </div>

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?php echo h($erro_bd); ?>
        </div>
    <?php endif; ?>

    <?php if ($mensagem_sucesso): ?>
        <div class="alert alert-success">
            <?php echo h($mensagem_sucesso); ?>
        </div>
    <?php endif; ?>
<div class="ficha-area mt-3">
        <ul class="nav nav-tabs ficha-tabs" id="tabsProcessos" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-manutencoes" data-bs-toggle="tab" data-bs-target="#conteudo-manutencoes" type="button" role="tab">
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i>
                    Manutenções abertas
                    <span class="badge bg-light text-dark ms-1"><?php echo count($processosManutencao); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-calibracoes" data-bs-toggle="tab" data-bs-target="#conteudo-calibracoes" type="button" role="tab">
                    <i class="fa-solid fa-gauge-high me-2"></i>
                    Calibrações abertas
                    <span class="badge bg-light text-dark ms-1"><?php echo count($processosCalibracao); ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content ficha-tab-content">
            <div class="tab-pane fade show active" id="conteudo-manutencoes" role="tabpanel">
                <?php render_tabela_processos_abertos($processosManutencao, 'Processos de manutenção', 'tabela-manutencoes-abertas'); ?>
            </div>

            <div class="tab-pane fade" id="conteudo-calibracoes" role="tabpanel">
                <?php render_tabela_processos_abertos($processosCalibracao, 'Processos de calibração', 'tabela-calibracoes-abertas'); ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal novo processo -->
<div class="modal fade" id="modalNovoProcesso" tabindex="-1" aria-labelledby="modalNovoProcessoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content modal-acessorio">
            <form method="post" action="calibracao_manutencao.php" id="formNovoProcesso">
                <input type="hidden" name="acao" value="criar_processo">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalNovoProcessoLabel">
                            <i class="fa-solid fa-circle-plus me-2"></i>
                            Novo Processo Técnico
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="tipoProcesso" class="form-label">Tipo de processo *</label>
                            <select class="form-select" id="tipoProcesso" name="tipoProcesso" required>
                                <option value="">Selecionar</option>
                                <option value="manutencao_preventiva">Manutenção preventiva</option>
                                <option value="manutencao_corretiva">Manutenção corretiva</option>
                                <option value="calibracao">Calibração</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idEquipamento" class="form-label">Equipamento *</label>
                            <select class="form-select" id="idEquipamento" name="idEquipamento" required>
                                <option value="">Selecionar equipamento</option>
                                <?php foreach ($equipamentos as $equipamento): ?>
                                    <option value="<?php echo h($equipamento['id_equipamento']); ?>">
                                        <?php echo h($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idAcessorio" class="form-label">Acessório associado</label>
                            <select class="form-select" id="idAcessorio" name="idAcessorio">
                                <option value="">Equipamento principal</option>
                                <?php foreach ($acessorios as $acessorio): ?>
                                    <option value="<?php echo h($acessorio['id_acessorio']); ?>" data-equipamento="<?php echo h($acessorio['id_equipamento']); ?>">
                                        <?php echo h($acessorio['codigo_acessorio'] . ' - ' . $acessorio['designacao']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoExecucao" class="form-label">Tipo de execução *</label>
                            <select class="form-select" id="tipoExecucao" name="tipoExecucao" required>
                                <option value="externa">Externa</option>
                                <option value="interna">Interna</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idFornecedorResponsavel" class="form-label">Fornecedor responsável</label>
                            <select class="form-select" id="idFornecedorResponsavel" name="idFornecedorResponsavel">
                                <option value="">Não aplicável</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?php echo h($fornecedor['id_fornecedor']); ?>">
                                        <?php echo h($fornecedor['nome_empresa'] . ' (' . $fornecedor['tipo_fornecedor'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="tecnicoInterno" class="form-label">Técnico interno</label>
                            <input type="text" class="form-control" id="tecnicoInterno" name="tecnicoInterno" placeholder="Ex: Eng. Gonçalo">
                        </div>

                        <div class="col-md-4">
                            <label for="dataPrevista" class="form-label">Data prevista</label>
                            <input type="date" class="form-control" id="dataPrevista" name="dataPrevista">
                        </div>

                        <div class="col-md-4">
                            <label for="cobertaPorGarantia" class="form-label">Coberta por garantia?</label>
                            <select class="form-select" id="cobertaPorGarantia" name="cobertaPorGarantia">
                                <option value="0">Não</option>
                                <option value="1">Sim</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="custoProcesso" class="form-label">Custo previsto (€)</label>
                            <input type="number" class="form-control" id="custoProcesso" name="custoProcesso" min="0" step="0.01" placeholder="Ex: 85.00">
                        </div>

                        <div class="col-12">
                            <label for="observacoesProcesso" class="form-label">Observações iniciais</label>
                            <textarea class="form-control" id="observacoesProcesso" name="observacoesProcesso" rows="3" placeholder="Notas sobre a abertura do processo"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-adicionar">
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Abrir Processo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const equipamentoSelect = document.getElementById('idEquipamento');
    const acessorioSelect = document.getElementById('idAcessorio');
    const garantiaSelect = document.getElementById('cobertaPorGarantia');
    const custoInput = document.getElementById('custoProcesso');
    const tipoExecucao = document.getElementById('tipoExecucao');
    const fornecedorSelect = document.getElementById('idFornecedorResponsavel');
    const tecnicoInput = document.getElementById('tecnicoInterno');

    function filtrarAcessorios() {
        if (!equipamentoSelect || !acessorioSelect) return;
        const idEquipamento = equipamentoSelect.value;

        Array.from(acessorioSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = option.dataset.equipamento !== idEquipamento;
        });

        if (acessorioSelect.selectedOptions[0] && acessorioSelect.selectedOptions[0].hidden) {
            acessorioSelect.value = '';
        }
    }

    function atualizarCustoGarantia() {
        if (!garantiaSelect || !custoInput) return;

        if (garantiaSelect.value === '1') {
            custoInput.value = '0.00';
            custoInput.readOnly = true;
            custoInput.classList.add('campo-bloqueado');
        } else {
            custoInput.readOnly = false;
            custoInput.classList.remove('campo-bloqueado');
            if (custoInput.value === '0.00') {
                custoInput.value = '';
            }
        }
    }

    function atualizarTipoExecucao() {
        if (!tipoExecucao || !fornecedorSelect || !tecnicoInput) return;

        const tipo = tipoExecucao.value;

        fornecedorSelect.required = tipo === 'externa';
        tecnicoInput.required = tipo === 'interna';
    }

    equipamentoSelect?.addEventListener('change', filtrarAcessorios);
    garantiaSelect?.addEventListener('change', atualizarCustoGarantia);
    tipoExecucao?.addEventListener('change', atualizarTipoExecucao);

    filtrarAcessorios();
    atualizarCustoGarantia();
    atualizarTipoExecucao();
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
