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
        'emissao_relatorio'       => 'data_emissao_relatorio',
        'processo_finalizado'     => 'data_finalizacao',
        'cancelado'               => 'data_finalizacao'
    ];

    return $mapa[$estado] ?? null;
}

function proxima_etapa_processo($estadoAtual)
{
    $fluxo = [
        'aguarda_recolha'          => 'procedimento_a_decorrer',
        'procedimento_a_decorrer'  => 'emissao_relatorio',
        'emissao_relatorio'        => 'devolucao_equipamento',
        'devolucao_equipamento'    => 'processo_finalizado',
    ];

    return $fluxo[$estadoAtual] ?? null;
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

    if ($resultado === 'reprovado') {
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
            SELECT c.codigo_consumivel, c.nome, c.stock_atual, mc.quantidade_utilizada
            FROM manutencoes_consumiveis mc
            INNER JOIN consumiveis c ON c.id_consumivel = mc.id_consumivel
            WHERE mc.id_manutencao = :id
              AND mc.isActive = 1
            ORDER BY c.nome ASC
        "
        : "
            SELECT c.codigo_consumivel, c.nome, c.stock_atual, cc.quantidade_utilizada
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

    $ehEncerrado = in_array($processo['estado_processo'] ?? '', ['processo_finalizado', 'cancelado', 'reprovado'], true);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'decidir_processo') {
        if (!$ehAdministrador) {
            throw new Exception('Apenas o administrador pode aprovar ou reprovar processos.');
        }

        if (($processo['estado_processo'] ?? '') !== 'aguarda_decisao') {
            throw new Exception('Este processo já não se encontra à espera de decisão.');
        }

        $decisao = $_POST['decisaoAdmin'] ?? '';
        $motivoDecisao = valor_ou_null($_POST['motivoDecisao'] ?? null);
        // Garantia lida da BD — não alterável manualmente
        $cobertaPorGarantia = (int) ($processo['coberta_por_garantia'] ?? 0);
        $ehInternoProcesso  = ($processo['tipo_execucao'] ?? 'externa') === 'interna';
        $custo = null;

        if (!in_array($decisao, ['aprovado', 'reprovado'], true)) {
            throw new Exception('Decisão inválida.');
        }

        if ($decisao === 'reprovado' && empty($motivoDecisao)) {
            throw new Exception('Indique o motivo da reprovação.');
        }

        if ($decisao === 'aprovado' && $cobertaPorGarantia === 0 && !$ehInternoProcesso) {
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

    /* ---- Guardar dados finais (resultado, descrição, observações, data fecho) ---- */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'guardar_dados_finais') {
        if ($ehAdministrador) {
            throw new Exception('O administrador não tem permissão para editar os dados do processo.');
        }
        $descricao   = valor_ou_null($_POST['descricaoProcedimento'] ?? null);
        $observacoes = valor_ou_null($_POST['observacoesProcesso'] ?? null);

        if (!$ehEncerrado && ($processo['estado_processo'] ?? '') === 'devolucao_equipamento') {
            $resultado       = valor_ou_null($_POST['resultadoProcesso'] ?? null);
            $dataFinalizacao = data_ou_null($_POST['dataFinalizacao'] ?? null);

            $errosCampos = [];
            if (!$dataFinalizacao) $errosCampos[] = 'Data de finalização';
            if (!$resultado)       $errosCampos[] = 'Resultado';
            if (!$descricao)       $errosCampos[] = 'Descrição do procedimento';

            if (!empty($errosCampos)) {
                throw new Exception('Os seguintes campos são obrigatórios: ' . implode(', ', $errosCampos) . '.');
            }
        }

        $pdo->beginTransaction();

        if ($ehEncerrado) {
            /* Processo encerrado: só permite alterar descrição e observações */
            if ($tipo === 'manutencao') {
                $stmt = $pdo->prepare("
                    UPDATE manutencoes_equipamento
                    SET descricao_procedimento = :descricao,
                        observacoes = :observacoes,
                        atualizado_por = :atualizado_por
                    WHERE id_manutencao = :id
                ");
            } else {
                $stmt = $pdo->prepare("
                    UPDATE calibracoes_equipamento
                    SET procedimento = :descricao,
                        observacoes = :observacoes,
                        atualizado_por = :atualizado_por
                    WHERE id_calibracao = :id
                ");
            }
            $stmt->execute([
                ':descricao'    => $descricao,
                ':observacoes'  => $observacoes,
                ':atualizado_por' => $utilizadorAtual,
                ':id'           => $id
            ]);
        } else {
            $resultado       = valor_ou_null($_POST['resultadoProcesso'] ?? null);
            $dataFinalizacao = data_ou_null($_POST['dataFinalizacao'] ?? null);

            if ($tipo === 'manutencao') {
                $stmt = $pdo->prepare("
                    UPDATE manutencoes_equipamento
                    SET resultado = :resultado,
                        descricao_procedimento = :descricao,
                        observacoes = :observacoes,
                        data_finalizacao = :data_finalizacao,
                        atualizado_por = :atualizado_por
                    WHERE id_manutencao = :id
                ");
            } else {
                $stmt = $pdo->prepare("
                    UPDATE calibracoes_equipamento
                    SET resultado = :resultado,
                        procedimento = :descricao,
                        observacoes = :observacoes,
                        data_finalizacao = :data_finalizacao,
                        atualizado_por = :atualizado_por
                    WHERE id_calibracao = :id
                ");
            }
            $stmt->execute([
                ':resultado'        => $resultado,
                ':descricao'        => $descricao,
                ':observacoes'      => $observacoes,
                ':data_finalizacao' => $dataFinalizacao,
                ':atualizado_por'   => $utilizadorAtual,
                ':id'               => $id
            ]);
        }

        /* Upload de relatório/certificado */
        if (!empty($_FILES['ficheiroRelatorio']['name']) && $_FILES['ficheiroRelatorio']['error'] === UPLOAD_ERR_OK) {
            $processoAtualizado = obter_processo($pdo, $tipo, $id);
            $codigoEquipamento  = $processoAtualizado['codigo_equipamento'] ?? 'equipamento';
            $codigoProcesso     = $processoAtualizado['codigo_processo'] ?? 'processo';

            $pastaFisica = __DIR__ . '/../../assets/documentos/equipamentos/' . $codigoEquipamento . '/processos/';
            if (!is_dir($pastaFisica)) {
                mkdir($pastaFisica, 0775, true);
            }

            $extensao = strtolower(pathinfo($_FILES['ficheiroRelatorio']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                throw new Exception('Tipo de ficheiro não permitido para o relatório/certificado.');
            }

            $baseNome  = normalizar_nome_ficheiro($_FILES['ficheiroRelatorio']['name']);
            $nomeFinal = $codigoProcesso . '_' . $baseNome . '_' . time() . '.' . $extensao;
            $destino   = $pastaFisica . $nomeFinal;

            if (!move_uploaded_file($_FILES['ficheiroRelatorio']['tmp_name'], $destino)) {
                throw new Exception('Não foi possível guardar o ficheiro do relatório/certificado.');
            }

            $tipoDocumento  = $tipo === 'manutencao' ? 'relatorio_manutencao' : 'certificado_calibracao';
            $nomeDocumento  = valor_ou_null($_POST['nomeDocumento'] ?? null) ?: ($tipo === 'manutencao' ? 'Relatório de manutenção' : 'Certificado de calibração');
            $caminhoRelativo = 'equipamentos/' . $codigoEquipamento . '/processos/' . $nomeFinal;

            $stmtDoc = $pdo->prepare("
                INSERT INTO documentos_equipamentos (
                    id_equipamento, id_acessorio, id_manutencao, id_calibracao,
                    id_equipamento_fornecedor, tipo_documento, nome_documento,
                    caminho_ficheiro, data_documento, data_validade, observacoes, atualizado_por
                ) VALUES (
                    :id_equipamento, :id_acessorio, :id_manutencao, :id_calibracao,
                    NULL, :tipo_documento, :nome_documento,
                    :caminho_ficheiro, :data_documento, NULL, :observacoes, :atualizado_por
                )
            ");
            $stmtDoc->execute([
                ':id_equipamento'  => $processoAtualizado['id_equipamento'],
                ':id_acessorio'    => null,
                ':id_manutencao'   => $tipo === 'manutencao' ? $id : null,
                ':id_calibracao'   => $tipo === 'calibracao' ? $id : null,
                ':tipo_documento'  => $tipoDocumento,
                ':nome_documento'  => $nomeDocumento,
                ':caminho_ficheiro'=> $caminhoRelativo,
                ':data_documento'  => date('Y-m-d'),
                ':observacoes'     => 'Documento associado ao processo ' . $codigoProcesso,
                ':atualizado_por'  => $utilizadorAtual
            ]);
        } elseif (!$ehEncerrado && ($processo['tipo_execucao'] ?? 'externa') === 'externa' && empty($documentos)) {
            throw new Exception('Para processos externos é obrigatório carregar o relatório/certificado.');
        }

        /* Registo no histórico */
        $stmtH = $pdo->prepare("
            INSERT INTO historico_etapas_processos (
                tipo_processo, id_manutencao, id_calibracao,
                estado_anterior, estado_novo, responsavel_etapa,
                tipo_responsavel, id_fornecedor_responsavel, observacoes, atualizado_por
            ) VALUES (
                :tipo_processo, :id_manutencao, :id_calibracao,
                :estado_anterior, :estado_novo, :responsavel_etapa,
                'interno', NULL, :observacoes, :atualizado_por
            )
        ");
        $stmtH->execute([
            ':tipo_processo'     => $tipo,
            ':id_manutencao'     => $tipo === 'manutencao' ? $id : null,
            ':id_calibracao'     => $tipo === 'calibracao'  ? $id : null,
            ':estado_anterior'   => $processo['estado_processo'],
            ':estado_novo'       => $processo['estado_processo'],
            ':responsavel_etapa' => $utilizadorAtual,
            ':observacoes'       => 'Dados finais atualizados por ' . $utilizadorAtual . '.',
            ':atualizado_por'    => $utilizadorAtual
        ]);

        $pdo->commit();
        $mensagem_sucesso = 'Dados do processo guardados com sucesso.';
        $processo = obter_processo($pdo, $tipo, $id);
    }

    /* ---- Avançar etapa ---- */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'avancar_etapa') {
        $estadoParaFinalizar = ($processo['estado_processo'] ?? '') === 'devolucao_equipamento';
        if ($ehAdministrador && !$estadoParaFinalizar) {
            throw new Exception('O administrador não tem permissão para avançar etapas do processo.');
        }
        if (!$ehAdministrador && $estadoParaFinalizar) {
            throw new Exception('Apenas o administrador pode encerrar o processo nesta etapa.');
        }

        $estadoAtual  = $processo['estado_processo'] ?? '';
        $estadoNovo   = proxima_etapa_processo($estadoAtual);
        $hoje         = date('Y-m-d');
        $dataEtapa    = data_ou_null($_POST['dataEtapa'] ?? null) ?: $hoje;
        $obsEtapa     = valor_ou_null($_POST['observacoesEtapa'] ?? null);

        if (!$estadoNovo) {
            throw new Exception('Não é possível avançar a partir desta etapa.');
        }

        /* Validação de dados finais antes de finalizar */
        if ($estadoNovo === 'processo_finalizado') {
            $processoAtual = obter_processo($pdo, $tipo, $id);
            if (empty($processoAtual['resultado'])) {
                throw new Exception('Preencha o resultado nos Dados Finais antes de finalizar o processo.');
            }
            if ($tipo === 'manutencao' && empty($processoAtual['descricao_procedimento'])) {
                throw new Exception('Preencha a descrição do procedimento nos Dados Finais antes de finalizar.');
            }
            if ($tipo === 'calibracao' && empty($processoAtual['procedimento'])) {
                throw new Exception('Preencha o procedimento nos Dados Finais antes de finalizar.');
            }
        }

        /* Validação da sequência de datas entre etapas */
        $dataAnteriorRef = null;
        $labelAnterior   = null;

        if ($estadoNovo === 'procedimento_a_decorrer') {
            /* Data de início não pode ser anterior à data de abertura */
            if (!empty($processo['data_abertura']) && $dataEtapa < $processo['data_abertura']) {
                throw new Exception('A data de início do procedimento não pode ser anterior à data de abertura do processo (' . formatar_data($processo['data_abertura']) . ').');
            }
        } elseif ($estadoNovo === 'emissao_relatorio') {
            $dataAnteriorRef = $processo['data_inicio_procedimento'] ?? null;
            $labelAnterior   = 'início do procedimento';
        } elseif ($estadoNovo === 'devolucao_equipamento') {
            $dataAnteriorRef = $processo['data_emissao_relatorio'] ?? null;
            $labelAnterior   = 'emissão do relatório';
        } elseif ($estadoNovo === 'processo_finalizado') {
            $dataAnteriorRef = $processo['data_devolucao'] ?? null;
            $labelAnterior   = 'devolução do equipamento';
            /* Data de finalização não pode ser anterior à data de devolução */
            if ($dataAnteriorRef && $dataEtapa < $dataAnteriorRef) {
                throw new Exception('A data de finalização (' . formatar_data($dataEtapa) . ') não pode ser anterior à data de devolução do equipamento (' . formatar_data($dataAnteriorRef) . ').');
            }
        }

        if ($dataAnteriorRef && $dataEtapa < $dataAnteriorRef) {
            throw new Exception('A data introduzida (' . formatar_data($dataEtapa) . ') não pode ser anterior à data de ' . $labelAnterior . ' (' . formatar_data($dataAnteriorRef) . ').');
        }

        $pdo->beginTransaction();

        /* Campos de data automática por etapa */
        $camposData = [
            'procedimento_a_decorrer' => ['data_inicio_procedimento' => $dataEtapa],
            'emissao_relatorio'        => ['data_emissao_relatorio'   => $dataEtapa],
            'devolucao_equipamento'    => ['data_devolucao'           => $dataEtapa],
            'processo_finalizado'      => ['data_finalizacao'         => $dataEtapa],
        ];

        $setCols = 'estado_processo = :estado_processo, atualizado_por = :atualizado_por';
        $params  = [':estado_processo' => $estadoNovo, ':atualizado_por' => $utilizadorAtual, ':id' => $id];

        foreach ($camposData[$estadoNovo] ?? [] as $col => $val) {
            $setCols .= ", $col = :$col";
            $params[":$col"] = $val;
        }

        $tabela = $tipo === 'manutencao' ? 'manutencoes_equipamento' : 'calibracoes_equipamento';
        $pk     = $tipo === 'manutencao' ? 'id_manutencao'           : 'id_calibracao';
        $pdo->prepare("UPDATE $tabela SET $setCols WHERE $pk = :id")->execute($params);

        $stmtHistorico = $pdo->prepare("
            INSERT INTO historico_etapas_processos (
                tipo_processo, id_manutencao, id_calibracao,
                estado_anterior, estado_novo, responsavel_etapa,
                tipo_responsavel, id_fornecedor_responsavel, observacoes, atualizado_por
            ) VALUES (
                :tipo_processo, :id_manutencao, :id_calibracao,
                :estado_anterior, :estado_novo, :responsavel_etapa,
                'interno', NULL, :observacoes, :atualizado_por
            )
        ");
        $stmtHistorico->execute([
            ':tipo_processo'    => $tipo,
            ':id_manutencao'    => $tipo === 'manutencao' ? $id : null,
            ':id_calibracao'    => $tipo === 'calibracao'  ? $id : null,
            ':estado_anterior'  => $estadoAtual,
            ':estado_novo'      => $estadoNovo,
            ':responsavel_etapa'=> $utilizadorAtual,
            ':observacoes'      => $obsEtapa,
            ':atualizado_por'   => $utilizadorAtual
        ]);

        $processoHistorico = obter_processo($pdo, $tipo, $id);
        registar_historico_equipamento_processo(
            $pdo, $processoHistorico, $tipo, $estadoNovo,
            'Etapa avançada: ' . texto_estado_processo($estadoAtual) . ' → ' . texto_estado_processo($estadoNovo),
            $utilizadorAtual
        );

        if ($estadoNovo === 'processo_finalizado') {
            $processoFinal = obter_processo($pdo, $tipo, $id);
            $acessoriosProcessoFinal = obter_acessorios_processo($pdo, $tipo, $id);
            definir_estado_alvo_final($pdo, $processoFinal, $tipo, $processoFinal['resultado'] ?? null, $acessoriosProcessoFinal);

            // Calcular automaticamente a próxima data com base na periodicidade do equipamento
            $mesesPorPeriodicidade = ['semestral' => 6, 'anual' => 12, 'bienal' => 24, 'trienal' => 36];

            $infoEquip = $pdo->prepare("
                SELECT periodicidade_manutencao, periodicidade_calibracao
                FROM equipamentos WHERE id_equipamento = :id
            ");
            $infoEquip->execute([':id' => $processoFinal['id_equipamento']]);
            $equip = $infoEquip->fetch(PDO::FETCH_ASSOC);

            if ($tipo === 'manutencao') {
                $periodicidade = $equip['periodicidade_manutencao'] ?? null;
                if ($periodicidade && isset($mesesPorPeriodicidade[$periodicidade])) {
                    $meses = $mesesPorPeriodicidade[$periodicidade];
                    $baseData = $dataEtapa ?: date('Y-m-d');
                    $proximaData = date('Y-m-d', strtotime("+{$meses} months", strtotime($baseData)));
                    $pdo->prepare("UPDATE manutencoes_equipamento SET proxima_manutencao = :proxima WHERE id_manutencao = :id")
                        ->execute([':proxima' => $proximaData, ':id' => $id]);
                }
            } else {
                $periodicidade = $equip['periodicidade_calibracao'] ?? null;
                if ($periodicidade && isset($mesesPorPeriodicidade[$periodicidade])) {
                    $meses = $mesesPorPeriodicidade[$periodicidade];
                    $baseData = $dataEtapa ?: date('Y-m-d');
                    $proximaData = date('Y-m-d', strtotime("+{$meses} months", strtotime($baseData)));
                    $pdo->prepare("UPDATE calibracoes_equipamento SET proxima_calibracao = :proxima WHERE id_calibracao = :id")
                        ->execute([':proxima' => $proximaData, ':id' => $id]);
                }
            }
        }

        $pdo->commit();
        $mensagem_sucesso = 'Etapa avançada para: ' . texto_estado_processo($estadoNovo);
        $processo = obter_processo($pdo, $tipo, $id);
    }

    /* ---- Carregar fatura (processo encerrado) ---- */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'carregar_fatura') {
        if (!$ehAdministrador) {
            throw new Exception('Apenas o administrador pode carregar faturas.');
        }

        if (!$ehEncerrado) {
            throw new Exception('Só é possível carregar faturas em processos encerrados.');
        }

        if (empty($_FILES['ficheiroFatura']['name']) || $_FILES['ficheiroFatura']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Selecione um ficheiro para carregar.');
        }

        $processoFatura = obter_processo($pdo, $tipo, $id);
        $codigoEquipamentoFatura = $processoFatura['codigo_equipamento'] ?? 'equipamento';
        $codigoProcessoFatura    = $processoFatura['codigo_processo'] ?? 'processo';

        $pastaFatura = __DIR__ . '/../../assets/documentos/equipamentos/' . $codigoEquipamentoFatura . '/processos/';
        if (!is_dir($pastaFatura)) {
            mkdir($pastaFatura, 0775, true);
        }

        $extFatura = strtolower(pathinfo($_FILES['ficheiroFatura']['name'], PATHINFO_EXTENSION));
        if (!in_array($extFatura, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            throw new Exception('Tipo de ficheiro não permitido. Use PDF, JPG ou PNG.');
        }

        $baseNomeFatura = normalizar_nome_ficheiro($_FILES['ficheiroFatura']['name']);
        $nomeFinalFatura = $codigoProcessoFatura . '_fatura_' . $baseNomeFatura . '_' . time() . '.' . $extFatura;
        $destinoFatura   = $pastaFatura . $nomeFinalFatura;

        if (!move_uploaded_file($_FILES['ficheiroFatura']['tmp_name'], $destinoFatura)) {
            throw new Exception('Não foi possível guardar a fatura.');
        }

        $tipoDocFatura  = $tipo === 'manutencao' ? 'fatura_manutencao' : 'fatura_calibracao';
        $nomeDocFatura  = valor_ou_null($_POST['nomeFatura'] ?? null) ?: ($tipo === 'manutencao' ? 'Fatura de manutenção' : 'Fatura de calibração');
        $caminhoFatura  = 'equipamentos/' . $codigoEquipamentoFatura . '/processos/' . $nomeFinalFatura;

        $pdo->beginTransaction();

        $stmtFatura = $pdo->prepare("
            INSERT INTO documentos_equipamentos (
                id_equipamento, id_acessorio, id_manutencao, id_calibracao,
                id_equipamento_fornecedor, tipo_documento, nome_documento,
                caminho_ficheiro, data_documento, data_validade, observacoes, atualizado_por
            ) VALUES (
                :id_equipamento, NULL, :id_manutencao, :id_calibracao,
                NULL, :tipo_documento, :nome_documento,
                :caminho_ficheiro, :data_documento, NULL, :observacoes, :atualizado_por
            )
        ");
        $stmtFatura->execute([
            ':id_equipamento'   => $processoFatura['id_equipamento'],
            ':id_manutencao'    => $tipo === 'manutencao' ? $id : null,
            ':id_calibracao'    => $tipo === 'calibracao'  ? $id : null,
            ':tipo_documento'   => $tipoDocFatura,
            ':nome_documento'   => $nomeDocFatura,
            ':caminho_ficheiro' => $caminhoFatura,
            ':data_documento'   => date('Y-m-d'),
            ':observacoes'      => 'Fatura carregada para o processo ' . $codigoProcessoFatura,
            ':atualizado_por'   => $utilizadorAtual
        ]);

        $pdo->commit();
        $mensagem_sucesso = 'Fatura carregada com sucesso.';
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
if (($_GET['from'] ?? '') === 'encerrados') {
    $urlVoltar = 'processos_encerrados.php';
} elseif ($ehAdministrador) {
    $urlVoltar = 'aprovacao_processos.php';
} else {
    $urlVoltar = 'calibracao_manutencao.php';
}
?>

<main class="conteudo-private ficha-equipamento-page">
    <?php if (!empty($erroAcessoFlash)): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?php echo htmlspecialchars($erroAcessoFlash, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="ficha-topo-acoes mb-4">
        <div>
            <h2 class="titulo-pagina">Detalhe do Processo</h2>
            <p class="subtitulo-pagina">
                <?php echo h($processo['codigo_processo'] ?? '---'); ?> · <?php echo h(texto_tipo_processo($processo['tipo_processo'] ?? '', $tipo)); ?> · <?php echo h($alvoCodigo . ' - ' . $alvoNome); ?>
            </p>
        </div>

        <div class="ficha-toolbar mb-0">
            <a href="<?php echo h($urlVoltar); ?>" class="btn btn-voltar">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Voltar à Lista
            </a>

            <?php
            $estadoAtualTopo = $processo['estado_processo'] ?? '';
            $adminPodeEncerrar = $ehAdministrador && $estadoAtualTopo === 'devolucao_equipamento';
            ?>
            <?php if (!empty($processo) && $estadoAtualTopo !== 'aguarda_decisao' && !$adminPodeEncerrar): ?>
                <button type="submit" form="formDadosFinais" id="btnGuardarTopo" class="btn btn-guardar">
                    <i class="fa-solid fa-floppy-disk me-2"></i>
                    Guardar Processo
                </button>
            <?php endif; ?>
            <?php if ($adminPodeEncerrar): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="acao" value="avancar_etapa">
                    <input type="hidden" name="tipo" value="<?php echo h($tipo); ?>">
                    <input type="hidden" name="id" value="<?php echo h($id); ?>">
                    <input type="hidden" name="dataEtapa" value="<?php echo date('Y-m-d'); ?>">
                    <input type="hidden" name="observacoesEtapa" value="">
                    <button type="submit" class="btn btn-guardar">
                        <i class="fa-solid fa-flag-checkered me-2"></i>
                        Encerrar Processo
                    </button>
                </form>
            <?php endif; ?>
        </div>
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
                    <label class="form-label">Coberto por garantia</label>
                    <?php $garantiaAtual = (int) ($processo['coberta_por_garantia'] ?? 0); ?>
                    <div class="campo-visualizacao">
                        <?php if ($garantiaAtual === 1): ?>
                            <span class="estado estado-ativo">Sim</span>
                        <?php else: ?>
                            <span class="estado estado-inativo">Não</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $ehInternoDetalhe  = ($processo['tipo_execucao'] ?? 'externa') === 'interna';
                $mostrarCustoAdmin = !$ehInternoDetalhe && $garantiaAtual === 0;
                ?>
                <div class="col-md-4" id="campoCustoDecisao" <?php echo $mostrarCustoAdmin ? '' : 'style="display:none;"'; ?>>
                    <label for="custoDecisao" class="form-label">Custo previsto (€) <span class="text-danger">*</span></label>
                    <input type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        id="custoDecisao"
                        name="custoDecisao"
                        placeholder="Ex: 125.00">
                    <small class="text-muted">Obrigatório quando não coberto por garantia.</small>
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
    <div class="form-ficha-equipamento">


        <div class="ficha-area">
            <ul class="nav nav-tabs ficha-tabs" id="tabsDetalheProcesso" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-resumo" data-bs-toggle="tab" data-bs-target="#conteudo-resumo" type="button" role="tab">
                        <i class="fa-solid fa-circle-info me-2"></i>Resumo
                    </button>
                </li>
                <?php if (!$ehEncerrado && !$ehAdministrador): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-etapa" data-bs-toggle="tab" data-bs-target="#conteudo-etapa" type="button" role="tab">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Etapa
                    </button>
                </li>
                <?php endif; ?>
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
                <?php if ($ehEncerrado && $ehAdministrador): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-fatura" data-bs-toggle="tab" data-bs-target="#conteudo-fatura" type="button" role="tab">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Fatura
                    </button>
                </li>
                <?php endif; ?>
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
                                            <?php echo h('Quantidade usada: ' . $consumivel['quantidade_utilizada']); ?>
                                                <small class="d-block text-muted">
                                                    Stock atual: <?php echo h($consumivel['stock_atual']); ?>
                                                </small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                        <h4>Etapa do processo</h4>
                        <p>Avança para a próxima etapa e regista a data e observações da mudança.</p>
                    </div>

                    <?php $proximaEtapa = proxima_etapa_processo($processo['estado_processo'] ?? ''); ?>
                    <?php $engenheiroAguardaEncerramento = !$ehAdministrador && ($processo['estado_processo'] ?? '') === 'devolucao_equipamento'; ?>

                    <?php if ($engenheiroAguardaEncerramento): ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Etapa atual</label>
                                <div class="campo-visualizacao"><?php echo h(texto_estado_processo($processo['estado_processo'] ?? '')); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Próxima etapa</label>
                                <div class="campo-visualizacao"><?php echo h(texto_estado_processo('processo_finalizado')); ?></div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fa-solid fa-circle-info me-2"></i>
                                    O equipamento foi devolvido. Preencha os <strong>Dados finais</strong> (resultado e descrição do procedimento) e aguarde que o <strong>Administrador</strong> encerre o processo.
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                    <form id="formAvancarEtapa" method="post">
                        <input type="hidden" name="acao" value="avancar_etapa">
                        <input type="hidden" name="tipo" value="<?php echo h($tipo); ?>">
                        <input type="hidden" name="id" value="<?php echo h($id); ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Etapa atual</label>
                                <div class="campo-visualizacao"><?php echo h(texto_estado_processo($processo['estado_processo'] ?? '')); ?></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Próxima etapa</label>
                                <div class="campo-visualizacao">
                                    <?php echo $proximaEtapa ? h(texto_estado_processo($proximaEtapa)) : '<span class="text-muted">Sem próxima etapa</span>'; ?>
                                </div>
                            </div>

                            <?php if ($proximaEtapa): ?>
                                <div class="col-md-4">
                                    <label for="dataEtapa" class="form-label">Data da mudança de etapa</label>
                                    <input type="date" class="form-control" id="dataEtapa" name="dataEtapa" value="<?php echo date('Y-m-d'); ?>">
                                </div>

                                <div class="col-12">
                                    <label for="observacoesEtapa" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoesEtapa" name="observacoesEtapa" rows="3" maxlength="500" placeholder="Ex: Equipamento recolhido pelo fornecedor."></textarea>
                                    <small class="texto-ajuda-form contador-caracteres" data-target="observacoesEtapa" data-max="500">0 / 500 caracteres</small>
                                </div>

                                <div class="col-12">
                                    <button type="submit" form="formAvancarEtapa" class="btn btn-guardar" id="btnAvancarEtapaInterno" data-finalizar="<?php echo $proximaEtapa === 'processo_finalizado' ? '1' : '0'; ?>">
                                        <?php if ($proximaEtapa === 'processo_finalizado'): ?>
                                            <i class="fa-solid fa-flag-checkered me-2"></i>Encerrar Processo
                                        <?php else: ?>
                                            <i class="fa-solid fa-arrow-right me-2"></i>Avançar Etapa
                                        <?php endif; ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="conteudo-dados" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4>Dados finais</h4>
                        <p>
                            <?php if ($ehEncerrado): ?>
                                Podes editar a descrição do procedimento e as observações. Os restantes campos são apenas de leitura.
                            <?php else: ?>
                                Resultado, descrição do procedimento e data de fecho do processo.
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php
                        $estadoDevolucao = !$ehEncerrado && ($processo['estado_processo'] ?? '') === 'devolucao_equipamento';
                        $asterisco = $estadoDevolucao && !$ehAdministrador ? ' <span class="text-danger">*</span>' : '';
                    ?>

                    <?php if ($estadoDevolucao && !$ehAdministrador && ($processo['tipo_execucao'] ?? 'externa') === 'externa'): ?>
                        <div class="alert alert-warning mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Não te esqueças de carregar o relatório/certificado no separador <strong>Documentos</strong>.
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Data de finalização<?php echo $asterisco; ?></label>
                            <?php if ($ehEncerrado || $ehAdministrador): ?>
                                <div class="campo-visualizacao"><?php echo h(formatar_data($processo['data_finalizacao'] ?? null)); ?></div>
                            <?php else: ?>
                                <input type="date" class="form-control" id="dataFinalizacao" name="dataFinalizacao" form="formDadosFinais" value="<?php echo valor_data($processo['data_finalizacao'] ?? null); ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Resultado <?php echo $tipo === 'manutencao' ? 'da manutenção' : 'da calibração'; ?><?php echo $asterisco; ?></label>
                            <?php if ($ehEncerrado || $ehAdministrador): ?>
                                <div class="campo-visualizacao">
                                    <?php
                                        $textoResultados = [
                                            'aprovado'              => 'Aprovado',
                                            'aprovado_com_restricoes' => 'Aprovado com restrições',
                                            'reprovado'             => 'Reprovado',
                                        ];
                                        echo h($textoResultados[$processo['resultado'] ?? ''] ?? '---');
                                    ?>
                                </div>
                            <?php else: ?>
                                <select class="form-select" id="resultadoProcesso" name="resultadoProcesso" form="formDadosFinais">
                                    <option value="">Selecionar</option>
                                    <option value="aprovado" <?php echo selected_option($processo['resultado'], 'aprovado'); ?>>Aprovado</option>
                                    <option value="aprovado_com_restricoes" <?php echo selected_option($processo['resultado'], 'aprovado_com_restricoes'); ?>>Aprovado com restrições</option>
                                    <option value="reprovado" <?php echo selected_option($processo['resultado'], 'reprovado'); ?>>Reprovado</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descrição do procedimento<?php echo $asterisco; ?></label>
                            <?php if ($ehAdministrador): ?>
                                <div class="campo-visualizacao"><?php echo h($descricaoProcedimento ?: '---'); ?></div>
                            <?php else: ?>
                                <textarea class="form-control" id="descricaoProcedimento" name="descricaoProcedimento" rows="4" maxlength="2000" form="formDadosFinais"><?php echo h($descricaoProcedimento); ?></textarea>
                                <small class="texto-ajuda-form contador-caracteres" data-target="descricaoProcedimento" data-max="2000">0 / 2000 caracteres</small>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <?php if ($ehAdministrador): ?>
                                <div class="campo-visualizacao"><?php echo h($processo['observacoes'] ?? '---'); ?></div>
                            <?php else: ?>
                                <textarea class="form-control" id="observacoesProcesso" name="observacoesProcesso" rows="3" maxlength="1000" form="formDadosFinais"><?php echo h($processo['observacoes'] ?? ''); ?></textarea>
                                <small class="texto-ajuda-form contador-caracteres" data-target="observacoesProcesso" data-max="1000">0 / 1000 caracteres</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="conteudo-documentos" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4>Relatório ou certificado</h4>
                        <p>
                            Podes anexar o relatório da manutenção ou o certificado de calibração.
                            <?php if (($processo['tipo_execucao'] ?? 'externa') === 'externa'): ?>
                                <strong class="text-danger">Obrigatório para processos externos.</strong>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if (!$ehAdministrador): ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="nomeDocumento" class="form-label">Nome do documento</label>
                            <input type="text" class="form-control" id="nomeDocumento" name="nomeDocumento" form="formDadosFinais" maxlength="255" placeholder="Ex: Relatório de Manutenção Preventiva">
                            <small class="texto-ajuda-form contador-caracteres" data-target="nomeDocumento" data-max="255">0 / 255 caracteres</small>
                        </div>
                        <div class="col-md-6">
                            <label for="ficheiroRelatorio" class="form-label">
                                Ficheiro
                                <?php if (($processo['tipo_execucao'] ?? 'externa') === 'externa' && empty($documentos)): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="file" class="form-control" id="ficheiroRelatorio" name="ficheiroRelatorio" form="formDadosFinais" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </div>
                    </div>
                    <?php endif; ?>

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
                                    <?php
                                    $tiposDocLabel = [
                                        'relatorio_manutencao'   => 'Relatório',
                                        'certificado_calibracao' => 'Certificado',
                                        'contrato_manutencao'    => 'Contrato',
                                        'contrato_calibracao'    => 'Contrato',
                                        'fatura_manutencao'      => 'Fatura',
                                        'fatura_calibracao'      => 'Fatura',
                                    ];
                                    foreach ($documentos as $documento):
                                        $labelTipo = $tiposDocLabel[$documento['tipo_documento']] ?? str_replace('_', ' ', ucfirst($documento['tipo_documento']));
                                    ?>
                                        <tr>
                                            <td><?php echo h($labelTipo); ?></td>
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

                <?php if ($ehEncerrado && $ehAdministrador): ?>
                <div class="tab-pane fade" id="conteudo-fatura" role="tabpanel">
                    <div class="secao-ficha-titulo">
                        <h4><i class="fa-solid fa-file-invoice-dollar me-2"></i>Fatura do processo</h4>
                        <p>Carregue a fatura associada a este processo encerrado.</p>
                    </div>

                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="acao" value="carregar_fatura">
                        <input type="hidden" name="tipo" value="<?php echo h($tipo); ?>">
                        <input type="hidden" name="id" value="<?php echo h($id); ?>">

                        <div class="col-md-6">
                            <label for="nomeFatura" class="form-label">Nome da fatura</label>
                            <input type="text" class="form-control" id="nomeFatura" name="nomeFatura"
                                   maxlength="255" placeholder="Ex: Fatura n.º 2026/001">
                        </div>
                        <div class="col-md-6">
                            <label for="ficheiroFatura" class="form-label">Ficheiro <span class="text-danger">*</span> <small class="text-muted">(PDF, JPG, PNG)</small></label>
                            <input type="file" class="form-control" id="ficheiroFatura" name="ficheiroFatura"
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-guardar">
                                <i class="fa-solid fa-upload me-2"></i> Carregar Fatura
                            </button>
                        </div>
                    </form>

                    <?php
                    $faturas = array_filter($documentos, fn($d) => in_array($d['tipo_documento'], ['fatura_manutencao', 'fatura_calibracao'], true));
                    ?>
                    <?php if (!empty($faturas)): ?>
                        <h5 class="subtitulo-bloco-form mt-4">Faturas associadas</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle tabela-equipamentos">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($faturas as $fatura): ?>
                                        <tr>
                                            <td><?php echo h($fatura['nome_documento']); ?></td>
                                            <td><?php echo h(formatar_data($fatura['data_documento'])); ?></td>
                                            <td>
                                                <a class="btn btn-sm btn-documento-ver"
                                                   href="../../assets/documentos/<?php echo h($fatura['caminho_ficheiro']); ?>"
                                                   target="_blank">
                                                    <i class="fa-solid fa-eye me-1"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mt-3">Ainda não existe nenhuma fatura associada a este processo.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

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

    </div>

    <form id="formDadosFinais" method="post" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="guardar_dados_finais">
        <input type="hidden" name="tipo" value="<?php echo h($tipo); ?>">
        <input type="hidden" name="id" value="<?php echo h($id); ?>">
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


<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
