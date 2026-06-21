<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   MEDICORE - Detalhe do processo técnico
   Permite editar etapas, responsável por etapa, custo, resultado
   e relatório/certificado.
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

function valor_data($data)
{
    return empty($data) ? '' : h($data);
}

function formatar_data_hora($data)
{
    if (empty($data)) {
        return '---';
    }

    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $data;
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

function valor_decimal($valor)
{
    return $valor !== null && $valor !== '' ? h(number_format((float) $valor, 2, '.', '')) : '';
}

function selected_option($valorAtual, $valorOpcao)
{
    return (string) $valorAtual === (string) $valorOpcao ? 'selected' : '';
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

function classe_estado_processo($estado)
{
    switch ($estado) {
        case 'aguarda_decisao':
        case 'aguarda_recolha':
        case 'procedimento_a_decorrer':
        case 'emissao_relatorio':
        case 'devolucao_equipamento':
            return 'estado-manutencao';

        case 'aprovado':
        case 'procedimento_efetuado':
        case 'processo_finalizado':
            return 'estado-ativo';

        case 'reprovado':
        case 'cancelado':
            return 'estado-inativo';

        default:
            return 'estado-inativo';
    }
}

function etapas_visuais_processo($estadoAtual)
{
    $etapas = [
        'aguarda_decisao' => 'À espera da decisão',
        'aguarda_recolha' => 'Aguarda recolha',
        'procedimento_a_decorrer' => 'Procedimento a decorrer',
        'procedimento_efetuado' => 'Procedimento efetuado',
        'emissao_relatorio' => 'Emissão do relatório',
        'devolucao_equipamento' => 'Devolução do equipamento',
        'processo_finalizado' => 'Processo finalizado'
    ];

    if ($estadoAtual === 'cancelado') {
        $etapas['cancelado'] = 'Cancelado';
    }

    if ($estadoAtual === 'reprovado') {
        $etapas['reprovado'] = 'Reprovado';
    }

    return $etapas;
}

function classe_etapa_visual_processo($estadoEtapa, $estadoAtual)
{
    if ($estadoAtual === 'cancelado') {
        return $estadoEtapa === 'cancelado' ? 'processo-step cancelado' : 'processo-step pendente';
    }

    $ordem = array_keys(etapas_visuais_processo($estadoAtual));
    $indiceEtapa = array_search($estadoEtapa, $ordem, true);
    $indiceAtual = array_search($estadoAtual, $ordem, true);

    if ($indiceEtapa === false || $indiceAtual === false) {
        return 'processo-step pendente';
    }

    if ($indiceEtapa < $indiceAtual) {
        return 'processo-step concluido';
    }

    if ($indiceEtapa === $indiceAtual) {
        return 'processo-step atual';
    }

    return 'processo-step pendente';
}

function render_progresso_visual_processo($estadoAtual)
{
    $etapasVisuais = etapas_visuais_processo($estadoAtual);
    ?>
    <div class="processo-stepper" aria-label="Progresso visual do processo">
        <?php foreach ($etapasVisuais as $codigoEtapa => $nomeEtapa): ?>
            <?php
                $classeEtapa = classe_etapa_visual_processo($codigoEtapa, $estadoAtual);
                $numeroEtapa = array_search($codigoEtapa, array_keys($etapasVisuais), true) + 1;
            ?>
            <div class="<?php echo h($classeEtapa); ?>">
                <span class="processo-step-numero">
                    <?php if ($classeEtapa === 'processo-step concluido'): ?>
                        <i class="fa-solid fa-check"></i>
                    <?php elseif ($codigoEtapa === 'cancelado'): ?>
                        <i class="fa-solid fa-xmark"></i>
                    <?php else: ?>
                        <?php echo h((string) $numeroEtapa); ?>
                    <?php endif; ?>
                </span>
                <span class="processo-step-label"><?php echo h($nomeEtapa); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function texto_tipo_processo($tipo, $origem)
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

function normalizar_nome_ficheiro($nome)
{
    $nome = pathinfo($nome, PATHINFO_FILENAME);
    $nome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
    $nome = preg_replace('/[^A-Za-z0-9_-]+/', '_', $nome);
    $nome = trim($nome, '_');

    return $nome !== '' ? strtolower($nome) : 'documento';
}

function registar_historico_equipamento_processo(PDO $pdo, array $processo, $tipo, $estadoNovo, $descricao, $utilizadorAtual)
{
    $tipoEvento = $tipo === 'manutencao' ? 'manutencao' : 'calibracao';

    $stmt = $pdo->prepare("
        INSERT INTO historico_equipamentos (
            id_equipamento,
            id_localizacao,
            id_utilizador,
            tipo_evento,
            referencia_tabela,
            referencia_id,
            descricao,
            data_evento,
            isActive
        ) VALUES (
            :id_equipamento,
            :id_localizacao,
            :id_utilizador,
            :tipo_evento,
            :referencia_tabela,
            :referencia_id,
            :descricao,
            NOW(),
            1
        )
    ");

    $stmt->execute([
        ':id_equipamento' => $processo['id_equipamento'],
        ':id_localizacao' => $processo['id_localizacao'] ?? null,
        ':id_utilizador' => $_SESSION['id_utilizador'] ?? null,
        ':tipo_evento' => $tipoEvento,
        ':referencia_tabela' => $tipo === 'manutencao' ? 'manutencoes_equipamento' : 'calibracoes_equipamento',
        ':referencia_id' => $tipo === 'manutencao'
            ? $processo['id_manutencao']
            : $processo['id_calibracao'],
        ':descricao' => $descricao
    ]);
}

function campo_data_estado($estado)
{
    $mapa = [
        'procedimento_a_decorrer' => 'data_inicio_procedimento',
        'procedimento_efetuado' => 'data_fim_procedimento',
        'emissao_relatorio' => 'data_emissao_relatorio',
        'processo_finalizado' => 'data_finalizacao',
        'cancelado' => 'data_finalizacao'
    ];

    return $mapa[$estado] ?? null;
}

function validar_tipo_execucao($tipo)
{
    return in_array($tipo, ['interna', 'externa'], true) ? $tipo : 'externa';
}

function texto_tipo_execucao($tipo)
{
    return $tipo === 'interna' ? 'Interna' : 'Externa';
}

function texto_tipo_responsavel($tipo)
{
    return $tipo === 'fornecedor' ? 'Fornecedor' : 'Interno';
}

function definir_estado_alvo_final(PDO $pdo, $processo, $origem, $resultado, array $acessoriosProcesso = [])
{
    $estadoFinal = 'ativo';

    if ($origem === 'calibracao' && $resultado === 'reprovado') {
        $estadoFinal = 'avariado';
    }

    if ($origem === 'manutencao' && $resultado === 'nao_realizada') {
        $estadoFinal = 'avariado';
    }

    if (($processo['estado_processo'] ?? '') === 'cancelado') {
        $estadoFinal = 'ativo';
    }

    if (!empty($acessoriosProcesso)) {
        $stmt = $pdo->prepare("
            UPDATE acessorios_equipamento
            SET estado = :estado
            WHERE id_acessorio = :id_acessorio
        ");

        foreach ($acessoriosProcesso as $acessorio) {
            $stmt->execute([
                ':estado' => $estadoFinal,
                ':id_acessorio' => $acessorio['id_acessorio']
            ]);
        }

        return;
    }

    $stmt = $pdo->prepare("
        UPDATE equipamentos
        SET estado = :estado
        WHERE id_equipamento = :id_equipamento
    ");

    $stmt->execute([
        ':estado' => $estadoFinal,
        ':id_equipamento' => $processo['id_equipamento']
    ]);
}

function obter_processo(PDO $pdo, $tipo, $id)
{
    if ($tipo === 'manutencao') {
        $stmt = $pdo->prepare("
            SELECT
                m.*,
                'manutencao' AS origem,
                m.id_manutencao AS id_processo,
                m.tipo_manutencao AS tipo_processo,
                e.codigo_equipamento,
                e.designacao AS equipamento_nome,
                e.id_localizacao,
                f.nome_empresa AS fornecedor_nome,
                l.codigo AS codigo_localizacao,
                l.departamento_nome,
                l.edificio,
                l.piso,
                l.sala
            FROM manutencoes_equipamento m
            INNER JOIN equipamentos e
                ON e.id_equipamento = m.id_equipamento
            LEFT JOIN fornecedores f
                ON f.id_fornecedor = m.id_fornecedor_responsavel
            LEFT JOIN localizacoes l
                ON l.id_localizacao = e.id_localizacao
            WHERE m.id_manutencao = :id
              AND m.isActive = 1
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    $stmt = $pdo->prepare("
        SELECT
            c.*,
            'calibracao' AS origem,
            c.id_calibracao AS id_processo,
            'calibracao' AS tipo_processo,
            e.codigo_equipamento,
            e.designacao AS equipamento_nome,
            e.id_localizacao,
            f.nome_empresa AS fornecedor_nome,
            l.codigo AS codigo_localizacao,
            l.departamento_nome,
            l.edificio,
            l.piso,
            l.sala
        FROM calibracoes_equipamento c
        INNER JOIN equipamentos e
            ON e.id_equipamento = c.id_equipamento
        LEFT JOIN fornecedores f
            ON f.id_fornecedor = c.id_fornecedor_responsavel
        LEFT JOIN localizacoes l
            ON l.id_localizacao = e.id_localizacao
        WHERE c.id_calibracao = :id
          AND c.isActive = 1
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function obter_acessorios_processo(PDO $pdo, $tipo, $id)
{
    $sql = $tipo === 'manutencao'
        ? "
            SELECT a.id_acessorio,
                   CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0')) AS codigo_acessorio,
                   a.designacao
            FROM manutencoes_acessorios ma
            INNER JOIN acessorios_equipamento a ON a.id_acessorio = ma.id_acessorio
            INNER JOIN equipamentos e ON e.id_equipamento = a.id_equipamento
            WHERE ma.id_manutencao = :id
              AND ma.isActive = 1
            ORDER BY a.numero_sequencial ASC
        "
        : "
            SELECT a.id_acessorio,
                   CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0')) AS codigo_acessorio,
                   a.designacao
            FROM calibracoes_acessorios ca
            INNER JOIN acessorios_equipamento a ON a.id_acessorio = ca.id_acessorio
            INNER JOIN equipamentos e ON e.id_equipamento = a.id_equipamento
            WHERE ca.id_calibracao = :id
              AND ca.isActive = 1
            ORDER BY a.numero_sequencial ASC
        ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    return $stmt->fetchAll();
}

function obter_consumiveis_processo(PDO $pdo, $tipo, $id)
{
    $sql = $tipo === 'manutencao'
        ? "
            SELECT c.codigo_consumivel, c.nome, c.unidade, mc.quantidade_utilizada
            FROM manutencoes_consumiveis mc
            INNER JOIN consumiveis c ON c.id_consumivel = mc.id_consumivel
            WHERE mc.id_manutencao = :id
              AND mc.isActive = 1
            ORDER BY c.nome ASC
        "
        : "
            SELECT c.codigo_consumivel, c.nome, c.unidade, cc.quantidade_utilizada
            FROM calibracoes_consumiveis cc
            INNER JOIN consumiveis c ON c.id_consumivel = cc.id_consumivel
            WHERE cc.id_calibracao = :id
              AND cc.isActive = 1
            ORDER BY c.nome ASC
        ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    return $stmt->fetchAll();
}

[$tipo, $id] = processo_from_request();

if (!in_array($tipo, ['manutencao', 'calibracao'], true) || $id <= 0) {
    die('Processo inválido.');
}

$pdo = null;
$erro_bd = '';
$mensagem_sucesso = '';
$processo = null;
$historico = [];
$documentos = [];
$fornecedores = [];
$ehAdministrador = false;

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
    $tipoUtilizadorAtual = $_SESSION['tipo_utilizador'] ?? '';
    $ehAdministrador = $tipoUtilizadorAtual === 'Administrador';
    $processo = obter_processo($pdo, $tipo, $id);
    $acessoriosProcesso = obter_acessorios_processo($pdo, $tipo, $id);
    $consumiveisProcesso = obter_consumiveis_processo($pdo, $tipo, $id);

    if (!$processo) {
        throw new Exception('O processo indicado não foi encontrado.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'decidir_processo') {
        if (!$ehAdministrador) {
            throw new Exception('Apenas o administrador pode aprovar ou reprovar processos.');
        }

        if (($processo['estado_processo'] ?? '') !== 'aguarda_decisao') {
            throw new Exception('Este processo já não se encontra à espera de decisão.');
        }

        $decisao = $_POST['decisaoAdmin'] ?? '';
        $motivoDecisao = valor_ou_null($_POST['motivoDecisao'] ?? null);
        $cobertaPorGarantia = (int) ($_POST['cobertaPorGarantiaDecisao'] ?? ($processo['coberta_por_garantia'] ?? 0));
        $custo = null;

        if (!in_array($decisao, ['aprovado', 'reprovado'], true)) {
            throw new Exception('Decisão inválida.');
        }

        if ($decisao === 'reprovado' && empty($motivoDecisao)) {
            throw new Exception('Indique o motivo da reprovação.');
        }

        if ($decisao === 'aprovado' && $cobertaPorGarantia === 0) {
            $custo = decimal_ou_null($_POST['custoDecisao'] ?? null);

            if ($custo === null) {
                throw new Exception('Indique o custo do processo quando não está coberto por garantia.');
            }
        }

        $estadoAnterior = $processo['estado_processo'];
        $estadoNovo = $decisao === 'aprovado' ? 'aguarda_recolha' : 'reprovado';

        $pdo->beginTransaction();

        if ($tipo === 'manutencao') {
            $stmt = $pdo->prepare("
                UPDATE manutencoes_equipamento
                SET
                    estado_processo = :estado_processo,
                    decisao_admin = :decisao_admin,
                    id_admin_decisao = :id_admin_decisao,
                    data_decisao = NOW(),
                    motivo_decisao = :motivo_decisao,
                    coberta_por_garantia = :coberta_por_garantia,
                    custo = :custo,
                    atualizado_por = :atualizado_por
                WHERE id_manutencao = :id
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE calibracoes_equipamento
                SET
                    estado_processo = :estado_processo,
                    decisao_admin = :decisao_admin,
                    id_admin_decisao = :id_admin_decisao,
                    data_decisao = NOW(),
                    motivo_decisao = :motivo_decisao,
                    coberta_por_garantia = :coberta_por_garantia,
                    custo = :custo,
                    atualizado_por = :atualizado_por
                WHERE id_calibracao = :id
            ");
        }

        $stmt->execute([
            ':estado_processo' => $estadoNovo,
            ':decisao_admin' => $decisao,
            ':id_admin_decisao' => $_SESSION['id_utilizador'] ?? null,
            ':motivo_decisao' => $motivoDecisao,
            ':coberta_por_garantia' => $cobertaPorGarantia,
            ':custo' => $custo,
            ':atualizado_por' => $utilizadorAtual,
            ':id' => $id
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
                :tipo_processo,
                :id_manutencao,
                :id_calibracao,
                :estado_anterior,
                :estado_novo,
                :responsavel_etapa,
                'interno',
                NULL,
                :observacoes,
                :atualizado_por
            )
        ");

        $stmtHistorico->execute([
            ':tipo_processo' => $tipo,
            ':id_manutencao' => $tipo === 'manutencao' ? $id : null,
            ':id_calibracao' => $tipo === 'calibracao' ? $id : null,
            ':estado_anterior' => $estadoAnterior,
            ':estado_novo' => $estadoNovo,
            ':responsavel_etapa' => $utilizadorAtual,
            ':observacoes' => $decisao === 'aprovado'
                ? 'Processo aprovado pelo administrador.'
                : 'Processo reprovado pelo administrador. ' . $motivoDecisao,
            ':atualizado_por' => $utilizadorAtual
        ]);

        $descricaoHistoricoEquipamento = $decisao === 'aprovado'
            ? 'Processo aprovado pelo administrador e enviado para aguardar recolha.'
            : 'Processo reprovado pelo administrador. ' . $motivoDecisao;

        $processoHistorico = obter_processo($pdo, $tipo, $id);

        registar_historico_equipamento_processo(
            $pdo,
            $processoHistorico,
            $tipo,
            $estadoNovo,
            $descricaoHistoricoEquipamento,
            $utilizadorAtual
        );

        if (
            $decisao === 'aprovado'
            && !empty($_FILES['ficheiroContratoDecisao']['name'])
            && $_FILES['ficheiroContratoDecisao']['error'] === UPLOAD_ERR_OK
        ) {
            $processoAtualizado = obter_processo($pdo, $tipo, $id);

            $codigoEquipamento = $processoAtualizado['codigo_equipamento'] ?? 'equipamento';
            $codigoProcesso = $processoAtualizado['codigo_processo'] ?? 'processo';

            $pastaFisica = __DIR__ . '/../../assets/documentos/equipamentos/' . $codigoEquipamento . '/processos/';

            if (!is_dir($pastaFisica)) {
                mkdir($pastaFisica, 0775, true);
            }

            $extensao = strtolower(pathinfo($_FILES['ficheiroContratoDecisao']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                throw new Exception('Tipo de ficheiro não permitido para o contrato.');
            }

            $baseNome = normalizar_nome_ficheiro($_FILES['ficheiroContratoDecisao']['name']);
            $nomeFinal = $codigoProcesso . '_contrato_' . $baseNome . '_' . time() . '.' . $extensao;
            $destino = $pastaFisica . $nomeFinal;

            if (!move_uploaded_file($_FILES['ficheiroContratoDecisao']['tmp_name'], $destino)) {
                throw new Exception('Não foi possível guardar o contrato do processo.');
            }

            $tipoDocumento = $tipo === 'manutencao'
                ? 'contrato_manutencao'
                : 'contrato_calibracao';

            $nomeDocumento = valor_ou_null($_POST['nomeContratoDecisao'] ?? null)
                ?: ($tipo === 'manutencao' ? 'Contrato de manutenção' : 'Contrato de calibração');

            $caminhoRelativo = 'equipamentos/' . $codigoEquipamento . '/processos/' . $nomeFinal;

            $stmtDoc = $pdo->prepare("
                INSERT INTO documentos_equipamentos (
                    id_equipamento,
                    id_manutencao,
                    id_calibracao,
                    id_equipamento_fornecedor,
                    tipo_documento,
                    nome_documento,
                    caminho_ficheiro,
                    data_documento,
                    data_validade,
                    observacoes,
                    atualizado_por
                ) VALUES (
                    :id_equipamento,
                    :id_manutencao,
                    :id_calibracao,
                    NULL,
                    :tipo_documento,
                    :nome_documento,
                    :caminho_ficheiro,
                    :data_documento,
                    NULL,
                    :observacoes,
                    :atualizado_por
                )
            ");

            $stmtDoc->execute([
                ':id_equipamento' => $processoAtualizado['id_equipamento'],
                ':id_manutencao' => $tipo === 'manutencao' ? $id : null,
                ':id_calibracao' => $tipo === 'calibracao' ? $id : null,
                ':tipo_documento' => $tipoDocumento,
                ':nome_documento' => $nomeDocumento,
                ':caminho_ficheiro' => $caminhoRelativo,
                ':data_documento' => date('Y-m-d'),
                ':observacoes' => 'Contrato associado à aprovação do processo ' . $codigoProcesso,
                ':atualizado_por' => $utilizadorAtual
            ]);
        }

        $pdo->commit();

        $mensagem_sucesso = $decisao === 'aprovado'
            ? 'Processo aprovado com sucesso.'
            : 'Processo reprovado com sucesso.';

        $processo = obter_processo($pdo, $tipo, $id);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'guardar_processo') {
        $estadoNovo = $_POST['estadoProcesso'] ?? $processo['estado_processo'];
        
        $transicoesPermitidas = [
            'aguarda_recolha' => ['aguarda_recolha', 'procedimento_a_decorrer', 'cancelado'],
            'procedimento_a_decorrer' => ['procedimento_a_decorrer', 'procedimento_efetuado', 'cancelado'],
            'procedimento_efetuado' => ['procedimento_efetuado', 'emissao_relatorio', 'cancelado'],
            'emissao_relatorio' => ['emissao_relatorio', 'devolucao_equipamento', 'cancelado'],
            'devolucao_equipamento' => ['devolucao_equipamento', 'processo_finalizado', 'cancelado'],
            'processo_finalizado' => ['processo_finalizado'],
            'cancelado' => ['cancelado'],
            'reprovado' => ['reprovado']
        ];

        $estadoAtual = $processo['estado_processo'] ?? '';

        if (
            isset($transicoesPermitidas[$estadoAtual])
            && !in_array($estadoNovo, $transicoesPermitidas[$estadoAtual], true)
        ) {
            throw new Exception('A alteração de etapa selecionada não é permitida.');
        }

        $estadosValidos = [
            'aguarda_recolha',
            'procedimento_a_decorrer',
            'procedimento_efetuado',
            'emissao_relatorio',
            'devolucao_equipamento',
            'processo_finalizado',
            'cancelado'
        ];

        if (!in_array($estadoNovo, $estadosValidos, true)) {
            throw new Exception('Estado do processo inválido.');
        }

        $tipoExecucao = validar_tipo_execucao($_POST['tipoExecucao'] ?? $processo['tipo_execucao']);
        $idFornecedor = !empty($_POST['idFornecedorResponsavel']) ? (int) $_POST['idFornecedorResponsavel'] : null;
        $tecnicoInterno = valor_ou_null($_POST['tecnicoInterno'] ?? null);
        $dataPrevista = data_ou_null($_POST['dataPrevista'] ?? null);
        $dataRecolha = data_ou_null($_POST['dataRecolha'] ?? null);
        $dataInicioProcedimento = data_ou_null($_POST['dataInicioProcedimento'] ?? null);
        $dataFimProcedimento = data_ou_null($_POST['dataFimProcedimento'] ?? null);
        $dataEmissaoRelatorio = data_ou_null($_POST['dataEmissaoRelatorio'] ?? null);
        $dataFinalizacao = data_ou_null($_POST['dataFinalizacao'] ?? null);
        $proximaIntervencao = data_ou_null($_POST['proximaIntervencao'] ?? null);
        $cobertaPorGarantia = (int) ($_POST['cobertaPorGarantia'] ?? 0);
        $custo = $processo['custo'] ?? null;
        $observacoes = valor_ou_null($_POST['observacoesProcesso'] ?? null);
        $observacoesEtapa = valor_ou_null($_POST['observacoesEtapa'] ?? null);
        $responsavelEtapa = valor_ou_null($_POST['responsavelEtapa'] ?? null);
        $tipoResponsavel = valor_ou_null($_POST['tipoResponsavel'] ?? null);
        $idFornecedorResponsavelEtapa = !empty($_POST['idFornecedorResponsavelEtapa']) ? (int) $_POST['idFornecedorResponsavelEtapa'] : null;

        if (!in_array($tipoResponsavel, ['interno', 'fornecedor', null], true)) {
            $tipoResponsavel = null;
        }

        if ($tipoExecucao === 'externa' && empty($idFornecedor)) {
            throw new Exception('Nos processos externos deve indicar o fornecedor responsável.');
        }

        if ($tipoExecucao === 'interna' && empty($tecnicoInterno)) {
            throw new Exception('Nos processos internos deve indicar o técnico interno responsável.');
        }

        $campoDataAutomatica = campo_data_estado($estadoNovo);
        $hoje = date('Y-m-d');

        if ($estadoNovo === 'procedimento_a_decorrer' && empty($dataInicioProcedimento)) {
            $dataInicioProcedimento = $hoje;
        }

        if ($estadoNovo === 'procedimento_efetuado' && empty($dataFimProcedimento)) {
            $dataFimProcedimento = $hoje;
        }

        if ($estadoNovo === 'emissao_relatorio' && empty($dataEmissaoRelatorio)) {
            $dataEmissaoRelatorio = $hoje;
        }

        if (in_array($estadoNovo, ['processo_finalizado', 'cancelado'], true) && empty($dataFinalizacao)) {
            $dataFinalizacao = $hoje;
        }

        $pdo->beginTransaction();

        $estadoAnterior = $processo['estado_processo'];

        if ($tipo === 'manutencao') {
            $resultado = valor_ou_null($_POST['resultadoManutencao'] ?? null);
            $descricao = valor_ou_null($_POST['descricaoProcedimento'] ?? null);
            $numeroRelatorio = valor_ou_null($_POST['numeroRelatorio'] ?? null);
            $dataManutencao = data_ou_null($_POST['dataIntervencao'] ?? null);

            if ($estadoNovo === 'processo_finalizado') {
                if (($processo['estado_processo'] ?? '') !== 'devolucao_equipamento') {
                    throw new Exception('O processo só pode ser finalizado depois da devolução do equipamento.');
                }
                if (empty($resultado)) {
                    throw new Exception('Para finalizar uma manutenção deve indicar o resultado.');
                }
                if (empty($descricao)) {
                    throw new Exception('Para finalizar uma manutenção deve preencher a descrição do procedimento.');
                }
                if (empty($dataManutencao)) {
                    $dataManutencao = $dataFinalizacao ?: $hoje;
                }
            }

            $stmt = $pdo->prepare("
                UPDATE manutencoes_equipamento
                SET
                    tipo_execucao = :tipo_execucao,
                    estado_processo = :estado_processo,
                    data_prevista = :data_prevista,
                    data_recolha = :data_recolha,
                    data_inicio_procedimento = :data_inicio_procedimento,
                    data_fim_procedimento = :data_fim_procedimento,
                    data_emissao_relatorio = :data_emissao_relatorio,
                    data_finalizacao = :data_finalizacao,
                    id_fornecedor_responsavel = :id_fornecedor_responsavel,
                    tecnico_interno = :tecnico_interno,
                    data_manutencao = :data_manutencao,
                    proxima_manutencao = :proxima_intervencao,
                    numero_relatorio = :numero_relatorio,
                    descricao_procedimento = :descricao,
                    resultado = :resultado,
                    coberta_por_garantia = :coberta_por_garantia,
                    observacoes = :observacoes,
                    atualizado_por = :atualizado_por
                WHERE id_manutencao = :id
            ");

            $stmt->execute([
                ':tipo_execucao' => $tipoExecucao,
                ':estado_processo' => $estadoNovo,
                ':data_prevista' => $dataPrevista,
                ':data_recolha' => $dataRecolha,
                ':data_inicio_procedimento' => $dataInicioProcedimento,
                ':data_fim_procedimento' => $dataFimProcedimento,
                ':data_emissao_relatorio' => $dataEmissaoRelatorio,
                ':data_finalizacao' => $dataFinalizacao,
                ':id_fornecedor_responsavel' => $idFornecedor,
                ':tecnico_interno' => $tecnicoInterno,
                ':data_manutencao' => $dataManutencao,
                ':proxima_intervencao' => $proximaIntervencao,
                ':numero_relatorio' => $numeroRelatorio,
                ':descricao' => $descricao,
                ':resultado' => $resultado,
                ':coberta_por_garantia' => $cobertaPorGarantia,
                ':observacoes' => $observacoes,
                ':atualizado_por' => $utilizadorAtual,
                ':id' => $id
            ]);
        } else {
            $resultado = valor_ou_null($_POST['resultadoCalibracao'] ?? null);
            $procedimento = valor_ou_null($_POST['descricaoProcedimento'] ?? null);
            $numeroCertificado = valor_ou_null($_POST['numeroCertificado'] ?? null);
            $dataCalibracao = data_ou_null($_POST['dataIntervencao'] ?? null);

            if ($estadoNovo === 'processo_finalizado') {
                if (($processo['estado_processo'] ?? '') !== 'devolucao_equipamento') {
                    throw new Exception('O processo só pode ser finalizado depois da devolução do equipamento.');
                }
                if (empty($resultado)) {
                    throw new Exception('Para finalizar uma calibração deve indicar o resultado.');
                }
                if (empty($procedimento)) {
                    throw new Exception('Para finalizar uma calibração deve preencher o procedimento.');
                }
                if (empty($dataCalibracao)) {
                    $dataCalibracao = $dataFinalizacao ?: $hoje;
                }
            }

            $stmt = $pdo->prepare("
                UPDATE calibracoes_equipamento
                SET
                    tipo_execucao = :tipo_execucao,
                    estado_processo = :estado_processo,
                    data_prevista = :data_prevista,
                    data_recolha = :data_recolha,
                    data_inicio_procedimento = :data_inicio_procedimento,
                    data_fim_procedimento = :data_fim_procedimento,
                    data_emissao_relatorio = :data_emissao_relatorio,
                    data_finalizacao = :data_finalizacao,
                    id_fornecedor_responsavel = :id_fornecedor_responsavel,
                    tecnico_interno = :tecnico_interno,
                    data_calibracao = :data_calibracao,
                    proxima_calibracao = :proxima_intervencao,
                    numero_certificado = :numero_certificado,
                    procedimento = :procedimento,
                    resultado = :resultado,
                    coberta_por_garantia = :coberta_por_garantia,
                    observacoes = :observacoes,
                    atualizado_por = :atualizado_por
                WHERE id_calibracao = :id
            ");

            $stmt->execute([
                ':tipo_execucao' => $tipoExecucao,
                ':estado_processo' => $estadoNovo,
                ':data_prevista' => $dataPrevista,
                ':data_recolha' => $dataRecolha,
                ':data_inicio_procedimento' => $dataInicioProcedimento,
                ':data_fim_procedimento' => $dataFimProcedimento,
                ':data_emissao_relatorio' => $dataEmissaoRelatorio,
                ':data_finalizacao' => $dataFinalizacao,
                ':id_fornecedor_responsavel' => $idFornecedor,
                ':tecnico_interno' => $tecnicoInterno,
                ':data_calibracao' => $dataCalibracao,
                ':proxima_intervencao' => $proximaIntervencao,
                ':numero_certificado' => $numeroCertificado,
                ':procedimento' => $procedimento,
                ':resultado' => $resultado,
                ':coberta_por_garantia' => $cobertaPorGarantia,
                ':observacoes' => $observacoes,
                ':atualizado_por' => $utilizadorAtual,
                ':id' => $id
            ]);
        }

        if ($estadoAnterior !== $estadoNovo || $observacoesEtapa || $responsavelEtapa || $tipoResponsavel || $idFornecedorResponsavelEtapa) {
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
                    :tipo_processo,
                    :id_manutencao,
                    :id_calibracao,
                    :estado_anterior,
                    :estado_novo,
                    :responsavel_etapa,
                    :tipo_responsavel,
                    :id_fornecedor_responsavel,
                    :observacoes,
                    :atualizado_por
                )
            ");

            $stmtHistorico->execute([
                ':tipo_processo' => $tipo,
                ':id_manutencao' => $tipo === 'manutencao' ? $id : null,
                ':id_calibracao' => $tipo === 'calibracao' ? $id : null,
                ':estado_anterior' => $estadoAnterior,
                ':estado_novo' => $estadoNovo,
                ':responsavel_etapa' => $responsavelEtapa,
                ':tipo_responsavel' => $tipoResponsavel,
                ':id_fornecedor_responsavel' => $idFornecedorResponsavelEtapa,
                ':observacoes' => $observacoesEtapa,
                ':atualizado_por' => $utilizadorAtual
            ]);
        }

        if ($estadoAnterior !== $estadoNovo) {
            $processoHistorico = obter_processo($pdo, $tipo, $id);

            $descricaoHistoricoEquipamento = 'Processo atualizado: '
                . texto_estado_processo($estadoAnterior)
                . ' → '
                . texto_estado_processo($estadoNovo)
                . '.';

            registar_historico_equipamento_processo(
                $pdo,
                $processoHistorico,
                $tipo,
                $estadoNovo,
                $descricaoHistoricoEquipamento,
                $utilizadorAtual
            );
        }

        /* Upload opcional de relatório/certificado */
        if (!empty($_FILES['ficheiroRelatorio']['name']) && $_FILES['ficheiroRelatorio']['error'] === UPLOAD_ERR_OK) {
            $processoAtualizado = obter_processo($pdo, $tipo, $id);
            $codigoEquipamento = $processoAtualizado['codigo_equipamento'] ?? 'equipamento';
            $codigoProcesso = $processoAtualizado['codigo_processo'] ?? 'processo';

            $pastaFisica = __DIR__ . '/../../assets/documentos/equipamentos/' . $codigoEquipamento . '/processos/';
            if (!is_dir($pastaFisica)) {
                mkdir($pastaFisica, 0775, true);
            }

            $extensao = strtolower(pathinfo($_FILES['ficheiroRelatorio']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                throw new Exception('Tipo de ficheiro não permitido para o relatório/certificado.');
            }

            $baseNome = normalizar_nome_ficheiro($_FILES['ficheiroRelatorio']['name']);
            $nomeFinal = $codigoProcesso . '_' . $baseNome . '_' . time() . '.' . $extensao;
            $destino = $pastaFisica . $nomeFinal;

            if (!move_uploaded_file($_FILES['ficheiroRelatorio']['tmp_name'], $destino)) {
                throw new Exception('Não foi possível guardar o ficheiro do relatório/certificado.');
            }

            $tipoDocumento = $tipo === 'manutencao' ? 'relatorio_manutencao' : 'certificado_calibracao';
            $nomeDocumento = valor_ou_null($_POST['nomeDocumento'] ?? null) ?: ($tipo === 'manutencao' ? 'Relatório de manutenção' : 'Certificado de calibração');
            $caminhoRelativo = 'equipamentos/' . $codigoEquipamento . '/processos/' . $nomeFinal;

            $stmtDoc = $pdo->prepare("
                INSERT INTO documentos_equipamentos (
                    id_equipamento,
                    id_acessorio,
                    id_manutencao,
                    id_calibracao,
                    id_equipamento_fornecedor,
                    tipo_documento,
                    nome_documento,
                    caminho_ficheiro,
                    data_documento,
                    data_validade,
                    observacoes,
                    atualizado_por
                ) VALUES (
                    :id_equipamento,
                    :id_acessorio,
                    :id_manutencao,
                    :id_calibracao,
                    NULL,
                    :tipo_documento,
                    :nome_documento,
                    :caminho_ficheiro,
                    :data_documento,
                    NULL,
                    :observacoes,
                    :atualizado_por
                )
            ");
            $stmtDoc->execute([
                ':id_equipamento' => $processoAtualizado['id_equipamento'],
                ':id_acessorio' => null,
                ':id_manutencao' => $tipo === 'manutencao' ? $id : null,
                ':id_calibracao' => $tipo === 'calibracao' ? $id : null,
                ':tipo_documento' => $tipoDocumento,
                ':nome_documento' => $nomeDocumento,
                ':caminho_ficheiro' => $caminhoRelativo,
                ':data_documento' => date('Y-m-d'),
                ':observacoes' => 'Documento associado ao processo ' . $codigoProcesso,
                ':atualizado_por' => $utilizadorAtual
            ]);
        }

        $processoDepois = obter_processo($pdo, $tipo, $id);
        if (in_array($estadoNovo, ['processo_finalizado', 'cancelado'], true)) {
            $acessoriosProcessoDepois = obter_acessorios_processo($pdo, $tipo, $id);
            definir_estado_alvo_final($pdo, $processoDepois, $tipo, $resultado ?? null, $acessoriosProcessoDepois);
        }

        $pdo->commit();
        $mensagem_sucesso = 'Processo atualizado com sucesso.';
        $processo = obter_processo($pdo, $tipo, $id);
    }

    $stmtFornecedores = $pdo->query("
        SELECT id_fornecedor, nome_empresa, tipo_fornecedor
        FROM fornecedores
        WHERE isActive = 1
        ORDER BY nome_empresa ASC
    ");
    $fornecedores = $stmtFornecedores->fetchAll();

    $stmtHistorico = $pdo->prepare("
        SELECT h.*, f.nome_empresa AS fornecedor_etapa_nome
        FROM historico_etapas_processos h
        LEFT JOIN fornecedores f
            ON f.id_fornecedor = h.id_fornecedor_responsavel
        WHERE h.tipo_processo = :tipo
          AND (
                (:tipo = 'manutencao' AND h.id_manutencao = :id)
             OR (:tipo = 'calibracao' AND h.id_calibracao = :id)
          )
        ORDER BY h.data_registo ASC
    ");
    $stmtHistorico->execute([
        ':tipo' => $tipo,
        ':id' => $id
    ]);
    $historico = $stmtHistorico->fetchAll();

    $stmtDocumentos = $pdo->prepare("
        SELECT *
        FROM documentos_equipamentos
        WHERE isActive = 1
          AND (
                (:tipo = 'manutencao' AND id_manutencao = :id)
             OR (:tipo = 'calibracao' AND id_calibracao = :id)
          )
        ORDER BY criado_em DESC
    ");
    $stmtDocumentos->execute([
        ':tipo' => $tipo,
        ':id' => $id
    ]);
    $documentos = $stmtDocumentos->fetchAll();
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $erro_bd = $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$alvoCodigo = $processo['codigo_equipamento'] ?? '---';
$alvoNome = $processo['equipamento_nome'] ?? 'Equipamento';
$proximaIntervencao = $tipo === 'manutencao' ? ($processo['proxima_manutencao'] ?? null) : ($processo['proxima_calibracao'] ?? null);
$dataIntervencao = $tipo === 'manutencao' ? ($processo['data_manutencao'] ?? null) : ($processo['data_calibracao'] ?? null);
$descricaoProcedimento = $tipo === 'manutencao' ? ($processo['descricao_procedimento'] ?? null) : ($processo['procedimento'] ?? null);
?>

<main class="conteudo-private ficha-equipamento-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Detalhe do Processo</h2>
            <p class="subtitulo-pagina">
                <?php echo h($processo['codigo_processo'] ?? '---'); ?> · <?php echo h(texto_tipo_processo($processo['tipo_processo'] ?? '', $tipo)); ?> · <?php echo h($alvoCodigo . ' - ' . $alvoNome); ?>
            </p>
        </div>

        <a href="calibracao_manutencao.php" class="btn btn-voltar">
            <i class="fa-solid fa-arrow-left me-2"></i>
            Voltar à Lista
        </a>
    </div>

    <?php if ($erro_bd): ?>
        <div class="alert alert-danger"><strong>Erro:</strong> <?php echo h($erro_bd); ?></div>
    <?php endif; ?>

    <?php if ($mensagem_sucesso): ?>
        <div class="alert alert-success"><?php echo h($mensagem_sucesso); ?></div>
    <?php endif; ?>

    <?php if (!empty($processo)): ?>
        <section class="processo-progresso-topo" aria-label="Progresso do processo">
            <div class="processo-progresso-topo-header">
                <h3>Progresso do processo</h3>
            </div>

            <?php render_progresso_visual_processo($processo['estado_processo'] ?? ''); ?>
        </section>
    <?php endif; ?>

    <?php
    $processoAguardaDecisao = ($processo['estado_processo'] ?? '') === 'aguarda_decisao';
    $mostrarFormularioTecnico = !$processoAguardaDecisao;
    ?>

    <?php if ($ehAdministrador && $processoAguardaDecisao): ?>
        <div class="card-formulario mb-4">
            <div class="secao-ficha-titulo">
                <h4>Decisão do administrador</h4>
                <p>Aprove ou reprove o pedido antes de avançar para a recolha do equipamento.</p>
            </div>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="acao" value="decidir_processo">

                <div class="col-md-4">
                    <label for="cobertaPorGarantiaDecisao" class="form-label">Coberto por garantia?</label>
                    <select class="form-select" id="cobertaPorGarantiaDecisao" name="cobertaPorGarantiaDecisao">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="custoDecisao" class="form-label">Custo previsto</label>
                    <input type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        id="custoDecisao"
                        name="custoDecisao"
                        placeholder="Ex: 125.00">
                </div>

                <div class="col-md-4">
                    <label for="motivoDecisao" class="form-label">Motivo / observação</label>
                    <input type="text"
                        class="form-control"
                        id="motivoDecisao"
                        name="motivoDecisao"
                        placeholder="Obrigatório se reprovar">
                </div>

                <div class="col-md-6">
                    <label for="nomeContratoDecisao" class="form-label">Nome do contrato</label>
                    <input type="text"
                        class="form-control"
                        id="nomeContratoDecisao"
                        name="nomeContratoDecisao"
                        placeholder="Ex: Contrato de manutenção preventiva">
                </div>

                <div class="col-md-6">
                    <label for="ficheiroContratoDecisao" class="form-label">Contrato / documento</label>
                    <input type="file"
                        class="form-control"
                        id="ficheiroContratoDecisao"
                        name="ficheiroContratoDecisao"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                </div>

                <div class="col-12 form-actions mt-3">
                    <button type="submit"
                            name="decisaoAdmin"
                            value="reprovado"
                            class="btn btn-cancelar">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Reprovar
                    </button>

                    <button type="submit"
                            name="decisaoAdmin"
                            value="aprovado"
                            class="btn btn-guardar">
                        <i class="fa-solid fa-check me-2"></i>
                        Aprovar Processo
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($mostrarFormularioTecnico): ?>
    <form method="post" enctype="multipart/form-data" class="form-ficha-equipamento">
        <input type="hidden" name="acao" value="guardar_processo">
        <input type="hidden" name="tipo" value="<?php echo h($tipo); ?>">
        <input type="hidden" name="id" value="<?php echo h($id); ?>">

        <div class="ficha-area">
            <ul class="nav nav-tabs ficha-tabs" id="tabsDetalheProcesso" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-resumo" data-bs-toggle="tab" data-bs-target="#conteudo-resumo" type="button" role="tab">
                        <i class="fa-solid fa-circle-info me-2"></i>Resumo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-etapa" data-bs-toggle="tab" data-bs-target="#conteudo-etapa" type="button" role="tab">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Etapa
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-dados" data-bs-toggle="tab" data-bs-target="#conteudo-dados" type="button" role="tab">
                        <i class="fa-solid fa-clipboard-list me-2"></i>Dados finais
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-documentos" data-bs-toggle="tab" data-bs-target="#conteudo-documentos" type="button" role="tab">
                        <i class="fa-solid fa-file-lines me-2"></i>Documentos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-historico" data-bs-toggle="tab" data-bs-target="#conteudo-historico" type="button" role="tab">
                        <i class="fa-solid fa-timeline me-2"></i>Histórico
                    </button>
                </li>
            </ul>

            <div class="tab-content ficha-tab-content">
                <div class="tab-pane fade show active" id="conteudo-resumo" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4>Resumo do processo</h4>
                        <p>Consulta rápida dos dados principais, estado atual e enquadramento do processo técnico.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Código</label>
                            <div class="campo-visualizacao"><?php echo h($processo['codigo_processo'] ?? '---'); ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Procedimento</label>
                            <div class="campo-visualizacao"><?php echo h(texto_tipo_processo($processo['tipo_processo'] ?? '', $tipo)); ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Alvo</label>
                            <div class="campo-visualizacao"><?php echo h($alvoCodigo . ' - ' . $alvoNome); ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Acessórios associados</label>
                            <div class="campo-visualizacao">
                                <?php if (empty($acessoriosProcesso)): ?>
                                    Equipamento principal
                                <?php else: ?>
                                    <?php foreach ($acessoriosProcesso as $acessorio): ?>
                                        <div><?php echo h($acessorio['codigo_acessorio'] . ' - ' . $acessorio['designacao']); ?></div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Consumíveis utilizados</label>
                            <div class="campo-visualizacao">
                                <?php if (empty($consumiveisProcesso)): ?>
                                    ---
                                <?php else: ?>
                                    <?php foreach ($consumiveisProcesso as $consumivel): ?>
                                        <div>
                                            <?php echo h($consumivel['codigo_consumivel'] . ' - ' . $consumivel['nome']); ?>
                                            <?php echo h($consumivel['quantidade_utilizada'] . ' ' . ($consumivel['unidade'] ?? '')); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Localização</label>
                            <div class="campo-visualizacao">
                                <?php echo h(($processo['codigo_localizacao'] ?? '---') . ' · ' . ($processo['departamento_nome'] ?? '---') . ' · Piso ' . ($processo['piso'] ?? '---') . ' · Sala ' . ($processo['sala'] ?? '---')); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo de execução</label>
                            <div class="campo-visualizacao"><?php echo h(texto_tipo_execucao($processo['tipo_execucao'] ?? 'externa')); ?></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Responsável</label>
                            <div class="campo-visualizacao">
                                <?php echo h(($processo['tipo_execucao'] ?? '') === 'interna' ? ($processo['tecnico_interno'] ?? '---') : ($processo['fornecedor_nome'] ?? '---')); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Custo</label>
                            <div class="campo-visualizacao"><?php echo h(formatar_moeda($processo['custo'] ?? null, $processo['coberta_por_garantia'] ?? 0)); ?></div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Abertura</label>
                            <div class="campo-visualizacao"><?php echo h(formatar_data($processo['data_abertura'] ?? null)); ?></div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Data prevista</label>
                            <div class="campo-visualizacao"><?php echo h(formatar_data($processo['data_prevista'] ?? null)); ?></div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Intervenção</label>
                            <div class="campo-visualizacao"><?php echo h(formatar_data($dataIntervencao)); ?></div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Finalização</label>
                            <div class="campo-visualizacao"><?php echo h(formatar_data($processo['data_finalizacao'] ?? null)); ?></div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="conteudo-etapa" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4>Atualização da etapa</h4>
                        <p>Altera a etapa atual e regista o responsável por essa etapa no histórico.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="estadoProcesso" class="form-label">Etapa atual *</label>
                            <select class="form-select" id="estadoProcesso" name="estadoProcesso" required>
                                <option value="aguarda_decisao" <?php echo selected_option($processo['estado_processo'], 'aguarda_decisao'); ?>>À espera da decisão</option>
                                <option value="aguarda_recolha" <?php echo selected_option($processo['estado_processo'], 'aguarda_recolha'); ?>>Aguarda recolha</option>
                                <option value="procedimento_a_decorrer" <?php echo selected_option($processo['estado_processo'], 'procedimento_a_decorrer'); ?>>Procedimento a decorrer</option>
                                <option value="procedimento_efetuado" <?php echo selected_option($processo['estado_processo'], 'procedimento_efetuado'); ?>>Procedimento efetuado</option>
                                <option value="emissao_relatorio" <?php echo selected_option($processo['estado_processo'], 'emissao_relatorio'); ?>>Emissão do relatório</option>
                                <option value="devolucao_equipamento" <?php echo selected_option($processo['estado_processo'], 'devolucao_equipamento'); ?>>Devolução do equipamento</option>
                                <option value="processo_finalizado" <?php echo selected_option($processo['estado_processo'], 'processo_finalizado'); ?>>Processo finalizado</option>
                                <option value="cancelado" <?php echo selected_option($processo['estado_processo'], 'cancelado'); ?>>Cancelado</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="responsavelEtapa" class="form-label">Responsável pela etapa</label>
                            <input type="text" class="form-control" id="responsavelEtapa" name="responsavelEtapa" placeholder="Ex: CalibraMed / Eng. Gonçalo">
                        </div>

                        <div class="col-md-4">
                            <label for="tipoResponsavel" class="form-label">Tipo de responsável</label>
                            <select class="form-select" id="tipoResponsavel" name="tipoResponsavel">
                                <option value="">Selecionar</option>
                                <option value="interno">Interno</option>
                                <option value="fornecedor">Fornecedor</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idFornecedorResponsavelEtapa" class="form-label">Fornecedor associado à etapa</label>
                            <select class="form-select" id="idFornecedorResponsavelEtapa" name="idFornecedorResponsavelEtapa">
                                <option value="">Não aplicável</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?php echo h($fornecedor['id_fornecedor']); ?>">
                                        <?php echo h($fornecedor['nome_empresa'] . ' (' . $fornecedor['tipo_fornecedor'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label for="observacoesEtapa" class="form-label">Observações da etapa</label>
                            <input type="text" class="form-control" id="observacoesEtapa" name="observacoesEtapa" placeholder="Ex: Equipamento recolhido pelo fornecedor.">
                        </div>

                        <div class="col-md-3">
                            <label for="dataRecolha" class="form-label">Data de recolha</label>
                            <input type="date" class="form-control" id="dataRecolha" name="dataRecolha" value="<?php echo valor_data($processo['data_recolha'] ?? null); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataInicioProcedimento" class="form-label">Início do procedimento</label>
                            <input type="date" class="form-control" id="dataInicioProcedimento" name="dataInicioProcedimento" value="<?php echo valor_data($processo['data_inicio_procedimento'] ?? null); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFimProcedimento" class="form-label">Fim do procedimento</label>
                            <input type="date" class="form-control" id="dataFimProcedimento" name="dataFimProcedimento" value="<?php echo valor_data($processo['data_fim_procedimento'] ?? null); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataEmissaoRelatorio" class="form-label">Emissão do relatório</label>
                            <input type="date" class="form-control" id="dataEmissaoRelatorio" name="dataEmissaoRelatorio" value="<?php echo valor_data($processo['data_emissao_relatorio'] ?? null); ?>">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="conteudo-dados" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4>Dados gerais e resultado</h4>
                        <p>Define execução interna/externa, custo, resultado e próxima intervenção.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="tipoExecucao" class="form-label">Tipo de execução *</label>
                            <select class="form-select" id="tipoExecucao" name="tipoExecucao" required>
                                <option value="externa" <?php echo selected_option($processo['tipo_execucao'], 'externa'); ?>>Externa</option>
                                <option value="interna" <?php echo selected_option($processo['tipo_execucao'], 'interna'); ?>>Interna</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idFornecedorResponsavel" class="form-label">Fornecedor responsável</label>
                            <select class="form-select" id="idFornecedorResponsavel" name="idFornecedorResponsavel">
                                <option value="">Não aplicável</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?php echo h($fornecedor['id_fornecedor']); ?>" <?php echo selected_option($processo['id_fornecedor_responsavel'], $fornecedor['id_fornecedor']); ?>>
                                        <?php echo h($fornecedor['nome_empresa'] . ' (' . $fornecedor['tipo_fornecedor'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="tecnicoInterno" class="form-label">Técnico interno</label>
                            <input type="text" class="form-control" id="tecnicoInterno" name="tecnicoInterno" value="<?php echo h($processo['tecnico_interno'] ?? ''); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataPrevista" class="form-label">Data prevista</label>
                            <input type="date" class="form-control" id="dataPrevista" name="dataPrevista" value="<?php echo valor_data($processo['data_prevista'] ?? null); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataIntervencao" class="form-label">Data da intervenção</label>
                            <input type="date" class="form-control" id="dataIntervencao" name="dataIntervencao" value="<?php echo valor_data($dataIntervencao); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="proximaIntervencao" class="form-label">Próxima intervenção</label>
                            <input type="date" class="form-control" id="proximaIntervencao" name="proximaIntervencao" value="<?php echo valor_data($proximaIntervencao); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFinalizacao" class="form-label">Data de finalização</label>
                            <input type="date" class="form-control" id="dataFinalizacao" name="dataFinalizacao" value="<?php echo valor_data($processo['data_finalizacao'] ?? null); ?>">
                        </div>

                        <?php if ($tipo === 'manutencao'): ?>
                            <div class="col-md-4">
                                <label for="resultadoManutencao" class="form-label">Resultado da manutenção</label>
                                <select class="form-select" id="resultadoManutencao" name="resultadoManutencao">
                                    <option value="">Selecionar</option>
                                    <option value="realizada" <?php echo selected_option($processo['resultado'], 'realizada'); ?>>Realizada</option>
                                    <option value="realizada_com_observacoes" <?php echo selected_option($processo['resultado'], 'realizada_com_observacoes'); ?>>Realizada com observações</option>
                                    <option value="nao_realizada" <?php echo selected_option($processo['resultado'], 'nao_realizada'); ?>>Não realizada</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="numeroRelatorio" class="form-label">Número do relatório</label>
                                <input type="text" class="form-control" id="numeroRelatorio" name="numeroRelatorio" value="<?php echo h($processo['numero_relatorio'] ?? ''); ?>">
                            </div>
                        <?php else: ?>
                            <div class="col-md-4">
                                <label for="resultadoCalibracao" class="form-label">Resultado da calibração</label>
                                <select class="form-select" id="resultadoCalibracao" name="resultadoCalibracao">
                                    <option value="">Selecionar</option>
                                    <option value="aprovado" <?php echo selected_option($processo['resultado'], 'aprovado'); ?>>Aprovado</option>
                                    <option value="aprovado_com_restricoes" <?php echo selected_option($processo['resultado'], 'aprovado_com_restricoes'); ?>>Aprovado com restrições</option>
                                    <option value="reprovado" <?php echo selected_option($processo['resultado'], 'reprovado'); ?>>Reprovado</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="numeroCertificado" class="form-label">Número do certificado</label>
                                <input type="text" class="form-control" id="numeroCertificado" name="numeroCertificado" value="<?php echo h($processo['numero_certificado'] ?? ''); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="col-md-2">
                            <label for="cobertaPorGarantia" class="form-label">Garantia?</label>
                            <select class="form-select" id="cobertaPorGarantia" name="cobertaPorGarantia">
                                <option value="0" <?php echo selected_option($processo['coberta_por_garantia'], 0); ?>>Não</option>
                                <option value="1" <?php echo selected_option($processo['coberta_por_garantia'], 1); ?>>Sim</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="descricaoProcedimento" class="form-label"><?php echo $tipo === 'manutencao' ? 'Descrição do procedimento' : 'Procedimento de calibração'; ?></label>
                            <textarea class="form-control" id="descricaoProcedimento" name="descricaoProcedimento" rows="4"><?php echo h($descricaoProcedimento); ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="observacoesProcesso" class="form-label">Observações gerais</label>
                            <textarea class="form-control" id="observacoesProcesso" name="observacoesProcesso" rows="3"><?php echo h($processo['observacoes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="conteudo-documentos" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4>Relatório ou certificado</h4>
                        <p>Podes anexar o relatório da manutenção ou o certificado de calibração.</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="nomeDocumento" class="form-label">Nome do documento</label>
                            <input type="text" class="form-control" id="nomeDocumento" name="nomeDocumento" placeholder="Ex: Relatório de Manutenção Preventiva">
                        </div>
                        <div class="col-md-6">
                            <label for="ficheiroRelatorio" class="form-label">Ficheiro</label>
                            <input type="file" class="form-control" id="ficheiroRelatorio" name="ficheiroRelatorio" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </div>
                    </div>

                    <h5 class="subtitulo-bloco-form">Documentos associados</h5>
                    <?php if (empty($documentos)): ?>
                        <p class="text-muted">Ainda não existem documentos associados a este processo.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle tabela-equipamentos">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Nome</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos as $documento): ?>
                                        <tr>
                                            <td><?php echo h(str_replace('_', ' ', ucfirst($documento['tipo_documento']))); ?></td>
                                            <td><?php echo h($documento['nome_documento']); ?></td>
                                            <td><?php echo h(formatar_data($documento['data_documento'])); ?></td>
                                            <td>
                                                <a class="btn btn-sm btn-documento-ver" href="../../assets/documentos/<?php echo h($documento['caminho_ficheiro']); ?>" target="_blank">
                                                    <i class="fa-solid fa-eye me-1"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="conteudo-historico" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4>Histórico das etapas</h4>
                        <p>Registo cronológico de todas as alterações de etapa e respetivos responsáveis.</p>
                    </div>

                    <?php if (empty($historico)): ?>
                        <p class="text-muted">Ainda não existe histórico para este processo.</p>
                    <?php else: ?>
                        <div class="processo-timeline">
                            <?php foreach ($historico as $item): ?>
                                <div class="processo-timeline-item">
                                    <div class="processo-timeline-ponto"></div>
                                    <div class="processo-timeline-conteudo">
                                        <h5><?php echo h(texto_estado_processo($item['estado_novo'])); ?></h5>
                                        <p class="mb-1">
                                            <?php echo h(formatar_data_hora($item['data_registo'])); ?>
                                            · registado por <?php echo h($item['atualizado_por'] ?: '---'); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Responsável:</strong>
                                            <?php echo h($item['responsavel_etapa'] ?: ($item['fornecedor_etapa_nome'] ?: '---')); ?>
                                            <?php if (!empty($item['tipo_responsavel'])): ?>
                                                <span class="badge-detalhe ms-2"><?php echo h(texto_tipo_responsavel($item['tipo_responsavel'])); ?></span>
                                            <?php endif; ?>
                                        </p>
                                        <?php if (!empty($item['observacoes'])): ?>
                                            <p class="text-muted mb-0"><?php echo h($item['observacoes']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-actions mt-4">
            <a href="calibracao_manutencao.php" class="btn btn-voltar">
                <i class="fa-solid fa-xmark me-2"></i>
                Voltar
            </a>

            <button type="submit" class="btn btn-guardar">
                <i class="fa-solid fa-floppy-disk me-2"></i>
                Guardar Processo
            </button>
        </div>
    </form>

    <?php else: ?>
        <div class="card-formulario">
            <div class="secao-ficha-titulo">
                <h4>Processo à espera da decisão</h4>
                <p>Este pedido ainda não foi aprovado pelo administrador. A edição técnica ficará disponível depois da aprovação.</p>
            </div>

            <div class="alerta-info-processo">
                <i class="fa-solid fa-circle-info me-2"></i>
                Aguarde a aprovação ou reprovação do administrador para avançar com a intervenção.
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
.processo-timeline {
    position: relative;
    display: grid;
    gap: 18px;
    margin-left: 10px;
}
.processo-timeline-item {
    position: relative;
    padding-left: 34px;
}
.processo-timeline-item::before {
    content: "";
    position: absolute;
    left: 9px;
    top: 18px;
    bottom: -22px;
    width: 2px;
    background: #d6ece8;
}
.processo-timeline-item:last-child::before {
    display: none;
}
.processo-timeline-ponto {
    position: absolute;
    left: 0;
    top: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--cor-secundaria);
    box-shadow: 0 0 0 5px rgba(79, 179, 164, 0.16);
}
.processo-timeline-conteudo {
    background: #f8fbfb;
    border: 1px solid #d6ece8;
    border-radius: 16px;
    padding: 16px 18px;
}
.processo-timeline-conteudo h5 {
    color: var(--cor-principal);
    font-weight: 900;
    margin-bottom: 6px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const garantia = document.getElementById('cobertaPorGarantia');
    const tipoExecucao = document.getElementById('tipoExecucao');
    const fornecedor = document.getElementById('idFornecedorResponsavel');
    const tecnico = document.getElementById('tecnicoInterno');

    function atualizarExecucao() {
        if (!tipoExecucao || !fornecedor || !tecnico) return;
        fornecedor.required = tipoExecucao.value === 'externa';
        tecnico.required = tipoExecucao.value === 'interna';
    }

    tipoExecucao?.addEventListener('change', atualizarExecucao);
    atualizarExecucao();
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
