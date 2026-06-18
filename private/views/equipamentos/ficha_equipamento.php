<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   LIGAÇÃO À BASE DE DADOS
   ========================================================= */
$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

/* =========================================================
   FUNÇÕES AUXILIARES
   ========================================================= */
function h_equipamento($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function selected_equipamento($valorAtual, $valorOpcao)
{
    return (string) $valorAtual === (string) $valorOpcao ? 'selected' : '';
}

function valor_data_equipamento($valor)
{
    return $valor ? h_equipamento($valor) : '';
}

function valor_decimal_equipamento($valor)
{
    return $valor !== null && $valor !== '' ? h_equipamento(number_format((float) $valor, 2, '.', '')) : '';
}

function formatar_custo_equipamento($valor, $cobertaPorGarantia = 0)
{
    if ((int) $cobertaPorGarantia === 1) {
        return 'Garantia';
    }

    if ($valor === null || $valor === '') {
        return '---';
    }

    return number_format((float) $valor, 2, ',', '.') . ' €';
}

function formatar_data_equipamento($data)
{
    if (empty($data)) {
        return '---';
    }

    $timestamp = strtotime($data);

    if (!$timestamp) {
        return $data;
    }

    return date('d/m/Y', $timestamp);
}

function data_post_equipamento($campo)
{
    $valor = trim($_POST[$campo] ?? '');
    return $valor !== '' ? $valor : null;
}

function decimal_post_equipamento($campo)
{
    $valor = trim($_POST[$campo] ?? '');
    return $valor !== '' ? (float) str_replace(',', '.', $valor) : null;
}

function obter_valor_array_post_equipamento($campo, $indice)
{
    return trim($_POST[$campo][$indice] ?? '');
}

function normalizar_nome_ficheiro_equipamento($nome)
{
    $nome = pathinfo($nome, PATHINFO_FILENAME);
    $nome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
    $nome = preg_replace('/[^A-Za-z0-9_-]+/', '_', $nome);
    $nome = trim($nome, '_');

    return $nome !== '' ? strtolower($nome) : 'documento';
}

function texto_estado_equipamento($estado)
{
    $estados = [
        'ativo' => 'Ativo',
        'avariado' => 'Avariado',
        'em_manutencao' => 'Em manutenção',
        'em_calibracao' => 'Em calibração',
        'inativo' => 'Inativo',
        'abatido' => 'Abatido'
    ];

    return $estados[$estado] ?? $estado;
}

function texto_criticidade_equipamento($criticidade)
{
    $criticidades = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'critica' => 'Crítica'
    ];

    return $criticidades[$criticidade] ?? $criticidade;
}

function texto_periodicidade_equipamento($periodicidade)
{
    $periodicidades = [
        'semestral' => 'Semestral',
        'anual' => 'Anual',
        'bienal' => 'Bienal',
        'trienal' => 'Trienal'
    ];

    return $periodicidades[$periodicidade] ?? ($periodicidade ?: '---');
}

function texto_tipo_documento_equipamento($tipo)
{
    $tipos = [
        'manual_instrucoes' => 'Manual de Instruções',
        'datasheet' => 'Datasheet',
        'contrato' => 'Contrato',
        'garantia' => 'Documento de Garantia',
        'certificado_calibracao' => 'Certificado de Calibração',
        'relatorio_calibracao' => 'Relatório de Calibração',
        'relatorio_manutencao' => 'Relatório de Manutenção',
        'ficha_tecnica' => 'Ficha Técnica',
        'declaracao_conformidade' => 'Declaração de Conformidade',
        'fotografia' => 'Fotografia',
        'outro' => 'Outro'
    ];

    return $tipos[$tipo] ?? $tipo;
}

function classe_estado_equipamento($estado)
{
    switch ($estado) {
        case 'ativo':
            return 'estado-ativo';
        case 'em_manutencao':
        case 'em_calibracao':
            return 'estado-manutencao';
        case 'avariado':
            return 'estado-avariado';
        case 'inativo':
            return 'estado-inativo';
        case 'abatido':
            return 'estado-abatido';
        default:
            return 'estado-inativo';
    }
}

function gerar_codigo_ficha_equipamento($pdo, $idFamilia)
{
    $stmtFamilia = $pdo->prepare("
        SELECT codigo_familia
        FROM familias_equipamento
        WHERE id_familia_equipamento = :id_familia
          AND isActive = 1
        LIMIT 1
    ");

    $stmtFamilia->execute([
        ':id_familia' => $idFamilia
    ]);

    $familia = $stmtFamilia->fetch();

    if (!$familia) {
        return null;
    }

    $stmtNumero = $pdo->prepare("
        SELECT COALESCE(MAX(numero_sequencial), 0) + 1 AS proximo_numero
        FROM equipamentos
        WHERE id_familia_equipamento = :id_familia
    ");

    $stmtNumero->execute([
        ':id_familia' => $idFamilia
    ]);

    $resultado = $stmtNumero->fetch();
    $proximoNumero = (int) $resultado['proximo_numero'];

    return [
        'numero_sequencial' => $proximoNumero,
        'codigo_equipamento' => $familia['codigo_familia'] . '.' . str_pad((string) $proximoNumero, 3, '0', STR_PAD_LEFT)
    ];
}

function guardar_documentos_ficha_equipamento($pdo, $idEquipamento, $codigoEquipamento, $idEquipamentoFornecedor)
{
    if (empty($_FILES['ficheiroDocumento']['name']) || !is_array($_FILES['ficheiroDocumento']['name'])) {
        return;
    }

    $extensoesPermitidas = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx'];
    $baseDir = __DIR__ . '/../../assets/documentos/equipamentos/' . $codigoEquipamento . '/';

    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0775, true);
    }

    $stmtDocumento = $pdo->prepare("
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
            isActive,
            atualizado_por
        ) VALUES (
            :id_equipamento,
            NULL,
            NULL,
            :id_equipamento_fornecedor,
            :tipo_documento,
            :nome_documento,
            :caminho_ficheiro,
            :data_documento,
            :data_validade,
            :observacoes,
            1,
            :atualizado_por
        )
    ");

    foreach ($_FILES['ficheiroDocumento']['name'] as $indice => $nomeOriginal) {
        if (trim($nomeOriginal) === '') {
            continue;
        }

        if ($_FILES['ficheiroDocumento']['error'][$indice] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erro ao carregar um dos documentos do equipamento.');
        }

        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            throw new RuntimeException('Formato de documento não permitido. Use PDF, PNG, JPG, JPEG, DOC ou DOCX.');
        }

        $tipoDocumento = obter_valor_array_post_equipamento('tipoDocumento', $indice);
        $nomeDocumento = obter_valor_array_post_equipamento('nomeDocumento', $indice);
        $dataDocumento = obter_valor_array_post_equipamento('dataDocumento', $indice);
        $dataValidade = obter_valor_array_post_equipamento('dataValidadeDocumento', $indice);

        if ($tipoDocumento === '') {
            $tipoDocumento = 'outro';
        }

        if ($nomeDocumento === '') {
            $nomeDocumento = pathinfo($nomeOriginal, PATHINFO_FILENAME);
        }

        $nomeBase = normalizar_nome_ficheiro_equipamento($nomeDocumento);
        $nomeFinal = $nomeBase . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $extensao;

        $destinoFisico = $baseDir . $nomeFinal;
        $caminhoRelativo = 'equipamentos/' . $codigoEquipamento . '/' . $nomeFinal;

        if (!move_uploaded_file($_FILES['ficheiroDocumento']['tmp_name'][$indice], $destinoFisico)) {
            throw new RuntimeException('Não foi possível guardar um dos documentos no servidor.');
        }

        $documentoFornecedor = in_array($tipoDocumento, ['contrato', 'garantia'], true)
            ? $idEquipamentoFornecedor
            : null;

        $stmtDocumento->execute([
            ':id_equipamento' => $idEquipamento,
            ':id_equipamento_fornecedor' => $documentoFornecedor,
            ':tipo_documento' => $tipoDocumento,
            ':nome_documento' => $nomeDocumento,
            ':caminho_ficheiro' => $caminhoRelativo,
            ':data_documento' => $dataDocumento !== '' ? $dataDocumento : null,
            ':data_validade' => $dataValidade !== '' ? $dataValidade : null,
            ':observacoes' => 'Documento adicionado na ficha do equipamento.',
            ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema'
        ]);
    }
}

/* =========================================================
   IDENTIFICAÇÃO DO EQUIPAMENTO
   ========================================================= */
$idEquipamento = (int) ($_POST['idEquipamento'] ?? id_from_request());

if ($idEquipamento <= 0) {
    header('Location: lista_equipamentos.php');
    exit;
}

$errosEquipamento = [];
$sucessoEquipamento = isset($_GET['criado']) ? 'Equipamento criado com sucesso.' : '';

/* =========================================================
   LISTAS AUXILIARES
   ========================================================= */
$familiasEquipamento = $pdo->query("
    SELECT id_familia_equipamento, codigo_familia, nome
    FROM familias_equipamento
    WHERE isActive = 1
    ORDER BY codigo_familia ASC
")->fetchAll();

$localizacoes = $pdo->query("
    SELECT id_localizacao, codigo, departamento_nome, edificio, piso, sala
    FROM localizacoes
    WHERE isActive = 1
    ORDER BY codigo ASC
")->fetchAll();

$fornecedoresFabricantes = $pdo->query("
    SELECT id_fornecedor, nome_empresa
    FROM fornecedores
    WHERE isActive = 1
      AND tipo_fornecedor = 'Fabricante'
    ORDER BY nome_empresa ASC
")->fetchAll();

$fornecedoresComerciais = $pdo->query("
    SELECT id_fornecedor, nome_empresa
    FROM fornecedores
    WHERE isActive = 1
      AND tipo_fornecedor = 'Comercial'
    ORDER BY nome_empresa ASC
")->fetchAll();

$fornecedoresGarantia = $pdo->query("
    SELECT id_fornecedor, nome_empresa, tipo_fornecedor
    FROM fornecedores
    WHERE isActive = 1
    ORDER BY nome_empresa ASC
")->fetchAll();

/* =========================================================
   ATUALIZAÇÃO DA FICHA
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    $camposObrigatorios = [
        'idFamiliaEquipamento' => 'Família do equipamento',
        'nomeEquipamento' => 'Designação do equipamento',
        'modelo' => 'Modelo',
        'numeroSerie' => 'Número de série',
        'idLocalizacao' => 'Localização',
        'estado' => 'Estado',
        'criticidade' => 'Criticidade',
        'idFornecedorFabricante' => 'Fornecedor fabricante',
        'idFornecedorComercial' => 'Fornecedor comercial'
    ];

    foreach ($camposObrigatorios as $campo => $label) {
        if (trim($_POST[$campo] ?? '') === '') {
            $errosEquipamento[] = 'O campo "' . $label . '" é obrigatório.';
        }
    }

    $idFornecedorFabricantePost = (int) ($_POST['idFornecedorFabricante'] ?? 0);
    $idFornecedorComercialPost = (int) ($_POST['idFornecedorComercial'] ?? 0);
    $idFornecedorGarantiaPost = trim($_POST['idFornecedorGarantia'] ?? '') !== ''
        ? (int) $_POST['idFornecedorGarantia']
        : null;

    if (
        $idFornecedorGarantiaPost !== null &&
        data_post_equipamento('dataFimGarantia') === null
    ) {
        $errosEquipamento[] = 'Se indicar um fornecedor de garantia, indique também a data de fim da garantia.';
    }

    if (
        $idFornecedorGarantiaPost !== null &&
        !in_array($idFornecedorGarantiaPost, [$idFornecedorFabricantePost, $idFornecedorComercialPost], true)
    ) {
        $errosEquipamento[] = 'O fornecedor da garantia só pode ser o fabricante ou o fornecedor comercial selecionado.';
    }

    if (empty($errosEquipamento)) {
        try {
            $pdo->beginTransaction();

            $stmtAtual = $pdo->prepare("
                SELECT id_familia_equipamento, numero_sequencial, codigo_equipamento
                FROM equipamentos
                WHERE id_equipamento = :id_equipamento
                  AND isActive = 1
                LIMIT 1
                FOR UPDATE
            ");

            $stmtAtual->execute([
                ':id_equipamento' => $idEquipamento
            ]);

            $equipamentoAtual = $stmtAtual->fetch();

            if (!$equipamentoAtual) {
                throw new RuntimeException('Equipamento não encontrado.');
            }

            $novaFamilia = (int) $_POST['idFamiliaEquipamento'];

            if ($novaFamilia !== (int) $equipamentoAtual['id_familia_equipamento']) {
                $novoCodigo = gerar_codigo_ficha_equipamento($pdo, $novaFamilia);

                if (!$novoCodigo) {
                    throw new RuntimeException('Família de equipamento inválida.');
                }

                $numeroSequencialGuardar = $novoCodigo['numero_sequencial'];
                $codigoGuardar = $novoCodigo['codigo_equipamento'];
            } else {
                $numeroSequencialGuardar = (int) $equipamentoAtual['numero_sequencial'];
                $codigoGuardar = $equipamentoAtual['codigo_equipamento'];
            }

            $stmtAtualizarEquipamento = $pdo->prepare("
                UPDATE equipamentos
                SET
                    id_familia_equipamento = :id_familia_equipamento,
                    numero_sequencial = :numero_sequencial,
                    codigo_equipamento = :codigo_equipamento,
                    designacao = :designacao,
                    modelo = :modelo,
                    numero_serie = :numero_serie,
                    tipo_entrada = :tipo_entrada,
                    valor_aquisicao = :valor_aquisicao,
                    id_localizacao = :id_localizacao,
                    estado = :estado,
                    criticidade = :criticidade,
                    periodicidade_manutencao = :periodicidade_manutencao,
                    periodicidade_calibracao = :periodicidade_calibracao,
                    data_fabrico = :data_fabrico,
                    data_aquisicao = :data_aquisicao,
                    data_instalacao = :data_instalacao,
                    responsavel_equipamento = :responsavel_equipamento,
                    observacoes = :observacoes,
                    atualizado_por = :atualizado_por
                WHERE id_equipamento = :id_equipamento
                  AND isActive = 1
            ");

            $stmtAtualizarEquipamento->execute([
                ':id_familia_equipamento' => $novaFamilia,
                ':numero_sequencial' => $numeroSequencialGuardar,
                ':codigo_equipamento' => $codigoGuardar,
                ':designacao' => trim($_POST['nomeEquipamento']),
                ':modelo' => trim($_POST['modelo']),
                ':numero_serie' => trim($_POST['numeroSerie']),
                ':tipo_entrada' => trim($_POST['tipoEntrada'] ?? '') ?: null,
                ':valor_aquisicao' => decimal_post_equipamento('valorAquisicao'),
                ':id_localizacao' => (int) $_POST['idLocalizacao'],
                ':estado' => trim($_POST['estado']),
                ':criticidade' => trim($_POST['criticidade']),
                ':periodicidade_manutencao' => trim($_POST['periodicidadeManutencao'] ?? '') ?: null,
                ':periodicidade_calibracao' => trim($_POST['periodicidadeCalibracao'] ?? '') ?: null,
                ':data_fabrico' => data_post_equipamento('dataFabrico'),
                ':data_aquisicao' => data_post_equipamento('dataAquisicao'),
                ':data_instalacao' => data_post_equipamento('dataInstalacao'),
                ':responsavel_equipamento' => trim($_POST['responsavelEquipamento'] ?? '') ?: null,
                ':observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
                ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema',
                ':id_equipamento' => $idEquipamento
            ]);

            $idFornecedorGarantia = $idFornecedorGarantiaPost;

            $stmtFornecedores = $pdo->prepare("
                INSERT INTO equipamentos_fornecedores (
                    id_equipamento,
                    id_fornecedor_fabricante,
                    id_fornecedor_comercial,
                    id_fornecedor_garantia,
                    data_inicio_garantia,
                    data_fim_garantia,
                    observacoes,
                    isActive,
                    atualizado_por
                ) VALUES (
                    :id_equipamento,
                    :id_fornecedor_fabricante,
                    :id_fornecedor_comercial,
                    :id_fornecedor_garantia,
                    :data_inicio_garantia,
                    :data_fim_garantia,
                    :observacoes,
                    1,
                    :atualizado_por
                )
                ON DUPLICATE KEY UPDATE
                    id_fornecedor_fabricante = VALUES(id_fornecedor_fabricante),
                    id_fornecedor_comercial = VALUES(id_fornecedor_comercial),
                    id_fornecedor_garantia = VALUES(id_fornecedor_garantia),
                    data_inicio_garantia = VALUES(data_inicio_garantia),
                    data_fim_garantia = VALUES(data_fim_garantia),
                    observacoes = VALUES(observacoes),
                    isActive = 1,
                    atualizado_por = VALUES(atualizado_por)
            ");

            $stmtFornecedores->execute([
                ':id_equipamento' => $idEquipamento,
                ':id_fornecedor_fabricante' => (int) $_POST['idFornecedorFabricante'],
                ':id_fornecedor_comercial' => (int) $_POST['idFornecedorComercial'],
                ':id_fornecedor_garantia' => $idFornecedorGarantia,
                ':data_inicio_garantia' => data_post_equipamento('dataInicioGarantia'),
                ':data_fim_garantia' => data_post_equipamento('dataFimGarantia'),
                ':observacoes' => trim($_POST['observacoesFornecedor'] ?? '') ?: null,
                ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema'
            ]);

            $stmtIdEquipamentoFornecedor = $pdo->prepare("
                SELECT id_equipamento_fornecedor
                FROM equipamentos_fornecedores
                WHERE id_equipamento = :id_equipamento
                LIMIT 1
            ");

            $stmtIdEquipamentoFornecedor->execute([
                ':id_equipamento' => $idEquipamento
            ]);

            $idEquipamentoFornecedor = (int) ($stmtIdEquipamentoFornecedor->fetchColumn() ?: 0);

            guardar_documentos_ficha_equipamento(
                $pdo,
                $idEquipamento,
                $codigoGuardar,
                $idEquipamentoFornecedor ?: null
            );

            $pdo->commit();

            header('Location: ficha_equipamento.php?ref=' . url_ref($idEquipamento) . '&guardado=1');
            exit;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($e->getCode() === '23000') {
                $errosEquipamento[] = 'Já existe outro equipamento com esse número de série, código ou associação de fornecedores.';
            } else {
                $errosEquipamento[] = 'Ocorreu um erro ao guardar as alterações.';
            }

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errosEquipamento[] = $e->getMessage() ?: 'Ocorreu um erro ao guardar as alterações.';
        }
    }
}

if (isset($_GET['guardado'])) {
    $sucessoEquipamento = 'Alterações guardadas com sucesso.';
}

/* =========================================================
   CONSULTA DA FICHA
   ========================================================= */
$stmtEquipamento = $pdo->prepare("
    SELECT
        e.*,
        fe.codigo_familia,
        fe.nome AS familia_nome,
        l.codigo AS codigo_localizacao,
        l.departamento_nome,
        l.departamento_sigla,
        l.edificio,
        l.piso,
        l.sala,
        ef.id_equipamento_fornecedor,
        ef.id_fornecedor_fabricante,
        ef.id_fornecedor_comercial,
        ef.id_fornecedor_garantia,
        ef.data_inicio_garantia,
        ef.data_fim_garantia,
        ef.observacoes AS observacoes_fornecedor,
        fabricante.nome_empresa AS fabricante_nome,
        comercial.nome_empresa AS comercial_nome,
        garantia.nome_empresa AS garantia_nome
    FROM equipamentos e
    INNER JOIN familias_equipamento fe
        ON fe.id_familia_equipamento = e.id_familia_equipamento
    INNER JOIN localizacoes l
        ON l.id_localizacao = e.id_localizacao
    LEFT JOIN equipamentos_fornecedores ef
        ON ef.id_equipamento = e.id_equipamento
       AND ef.isActive = 1
    LEFT JOIN fornecedores fabricante
        ON fabricante.id_fornecedor = ef.id_fornecedor_fabricante
    LEFT JOIN fornecedores comercial
        ON comercial.id_fornecedor = ef.id_fornecedor_comercial
    LEFT JOIN fornecedores garantia
        ON garantia.id_fornecedor = ef.id_fornecedor_garantia
    WHERE e.id_equipamento = :id_equipamento
      AND e.isActive = 1
    LIMIT 1
");

$stmtEquipamento->execute([
    ':id_equipamento' => $idEquipamento
]);

$equipamento = $stmtEquipamento->fetch();

if (!$equipamento) {
    header('Location: lista_equipamentos.php');
    exit;
}

$descricaoLocalizacao = $equipamento['codigo_localizacao'] . ' | ' .
    $equipamento['departamento_nome'] . ' - ' .
    $equipamento['edificio'] . ' - Piso ' .
    $equipamento['piso'] . ' - Sala ' .
    $equipamento['sala'];

/* =========================================================
   HISTÓRICO TÉCNICO E DOCUMENTOS
   ========================================================= */
$stmtManutencoes = $pdo->prepare("
    SELECT
        m.*,
        f.nome_empresa AS fornecedor_nome,
        (
            SELECT d.nome_documento
            FROM documentos_equipamentos d
            WHERE d.id_manutencao = m.id_manutencao
              AND d.tipo_documento = 'relatorio_manutencao'
              AND d.isActive = 1
            ORDER BY d.data_documento DESC, d.id_documento_equipamento DESC
            LIMIT 1
        ) AS nome_relatorio_manutencao,
        (
            SELECT d.caminho_ficheiro
            FROM documentos_equipamentos d
            WHERE d.id_manutencao = m.id_manutencao
              AND d.tipo_documento = 'relatorio_manutencao'
              AND d.isActive = 1
            ORDER BY d.data_documento DESC, d.id_documento_equipamento DESC
            LIMIT 1
        ) AS caminho_relatorio_manutencao
    FROM manutencoes_equipamento m
    LEFT JOIN fornecedores f
        ON f.id_fornecedor = m.id_fornecedor_responsavel
    WHERE m.id_equipamento = :id_equipamento
      AND m.isActive = 1
    ORDER BY m.data_manutencao DESC, m.id_manutencao DESC
");
$stmtManutencoes->execute([
    ':id_equipamento' => $idEquipamento
]);
$manutencoes = $stmtManutencoes->fetchAll();

$stmtCalibracoes = $pdo->prepare("
    SELECT
        c.*,
        f.nome_empresa AS fornecedor_nome,
        (
            SELECT d.nome_documento
            FROM documentos_equipamentos d
            WHERE d.id_calibracao = c.id_calibracao
              AND d.tipo_documento IN ('certificado_calibracao', 'relatorio_calibracao')
              AND d.isActive = 1
            ORDER BY d.data_documento DESC, d.id_documento_equipamento DESC
            LIMIT 1
        ) AS nome_documento_calibracao,
        (
            SELECT d.caminho_ficheiro
            FROM documentos_equipamentos d
            WHERE d.id_calibracao = c.id_calibracao
              AND d.tipo_documento IN ('certificado_calibracao', 'relatorio_calibracao')
              AND d.isActive = 1
            ORDER BY d.data_documento DESC, d.id_documento_equipamento DESC
            LIMIT 1
        ) AS caminho_documento_calibracao
    FROM calibracoes_equipamento c
    LEFT JOIN fornecedores f
        ON f.id_fornecedor = c.id_fornecedor_responsavel
    WHERE c.id_equipamento = :id_equipamento
      AND c.isActive = 1
    ORDER BY c.data_calibracao DESC, c.id_calibracao DESC
");
$stmtCalibracoes->execute([
    ':id_equipamento' => $idEquipamento
]);
$calibracoes = $stmtCalibracoes->fetchAll();

$stmtDocumentos = $pdo->prepare("
    SELECT *
    FROM documentos_equipamentos
    WHERE id_equipamento = :id_equipamento
      AND isActive = 1
    ORDER BY criado_em DESC, id_documento_equipamento DESC
");
$stmtDocumentos->execute([
    ':id_equipamento' => $idEquipamento
]);
$documentos = $stmtDocumentos->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private ficha-equipamento-page">

    <div class="d-none" aria-hidden="true">
        <h2 id="tituloPaginaEquipamento">Ficha do Equipamento - <?php echo h_equipamento($equipamento['codigo_equipamento']); ?></h2>
        <span id="resumoNomeEquipamento"><?php echo h_equipamento($equipamento['designacao']); ?></span>
        <span id="resumoDescricao"><?php echo h_equipamento($equipamento['codigo_equipamento'] . ' | ' . $equipamento['modelo'] . ' | ' . $descricaoLocalizacao); ?></span>
        <span id="badgeEstado"><?php echo h_equipamento(texto_estado_equipamento($equipamento['estado'])); ?></span>
        <span id="badgeCriticidade">Criticidade: <?php echo h_equipamento(texto_criticidade_equipamento($equipamento['criticidade'])); ?></span>
    </div>

    <div class="ficha-toolbar">
        <a href="lista_equipamentos.php" class="btn btn-voltar botao-consulta">
            <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
        </a>

        <button type="button" class="btn btn-editar-ficha botao-consulta" id="btnAtivarEdicao">
            <i class="fa-solid fa-pen me-2"></i> Editar
        </button>

        <button type="button" class="btn btn-cancelar botao-edicao d-none" id="btnCancelarEdicao">
            <i class="fa-solid fa-xmark me-2"></i> Cancelar
        </button>

        <button type="submit" class="btn btn-guardar botao-edicao d-none" form="formFichaEquipamento">
            <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
        </button>
    </div>

    <?php if ($sucessoEquipamento): ?>
        <div class="alert alert-success">
            <?php echo h_equipamento($sucessoEquipamento); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errosEquipamento)): ?>
        <div class="form-alerta-erros" role="alert">
            <strong>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Não foi possível guardar o equipamento.
            </strong>

            <ul>
                <?php foreach ($errosEquipamento as $erro): ?>
                    <li><?php echo h_equipamento($erro); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="form-equipamento form-ficha-equipamento"
          id="formFichaEquipamento"
          action="ficha_equipamento.php?ref=<?php echo url_ref($idEquipamento); ?>"
          method="post"
          enctype="multipart/form-data">

        <input type="hidden" id="idEquipamento" name="idEquipamento" value="<?php echo h_equipamento($equipamento['id_equipamento']); ?>">
        <input type="hidden" name="acao" value="atualizar">
        <input type="hidden" id="modoFormulario" name="modoFormulario" value="ver">

        <div class="ficha-area">

            <ul class="nav nav-tabs ficha-tabs" id="tabsFichaEquipamento" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="identificacao-tab" data-bs-toggle="tab" data-bs-target="#identificacao" type="button" role="tab" aria-controls="identificacao" aria-selected="true">
                        <i class="fa-solid fa-barcode me-2"></i> Identificação
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="estado-localizacao-tab" data-bs-toggle="tab" data-bs-target="#estado-localizacao" type="button" role="tab" aria-controls="estado-localizacao" aria-selected="false">
                        <i class="fa-solid fa-location-dot me-2"></i> Estado e Localização
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="aquisicao-tab" data-bs-toggle="tab" data-bs-target="#aquisicao" type="button" role="tab" aria-controls="aquisicao" aria-selected="false">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i> Aquisição
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fornecedores-tab" data-bs-toggle="tab" data-bs-target="#fornecedores" type="button" role="tab" aria-controls="fornecedores" aria-selected="false">
                        <i class="fa-solid fa-truck-medical me-2"></i> Fornecedores
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="historico-tab" data-bs-toggle="tab" data-bs-target="#historico" type="button" role="tab" aria-controls="historico" aria-selected="false">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i> Histórico
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab" aria-controls="documentos" aria-selected="false">
                        <i class="fa-solid fa-folder-open me-2"></i> Documentos
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="observacoes-tab" data-bs-toggle="tab" data-bs-target="#observacoes-tab-pane" type="button" role="tab" aria-controls="observacoes-tab-pane" aria-selected="false">
                        <i class="fa-solid fa-clipboard-list me-2"></i> Observações
                    </button>
                </li>

            </ul>

            <div class="tab-content ficha-tab-content" id="tabsFichaEquipamentoContent">

                <!-- IDENTIFICAÇÃO -->
                <div class="tab-pane fade show active" id="identificacao" role="tabpanel" aria-labelledby="identificacao-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Identificação do Equipamento</h4>
                        <p>Dados base que identificam o equipamento no inventário.</p>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-4">
                            <label for="codigoInventario" class="form-label">Código do Equipamento</label>
                            <input type="text" class="form-control campo-ficha campo-bloqueado" id="codigoInventario" name="codigoInventario" value="<?php echo h_equipamento($equipamento['codigo_equipamento']); ?>" readonly>
                            <small class="texto-ajuda-form">Código gerado automaticamente pelo sistema.</small>
                        </div>

                        <div class="col-md-4">
                            <label for="idFamiliaEquipamento" class="form-label">Família do Equipamento *</label>
                            <select class="form-select campo-ficha campo-editavel" id="idFamiliaEquipamento" name="idFamiliaEquipamento" required>
                                <option value="">Selecionar família</option>
                                <?php foreach ($familiasEquipamento as $familia): ?>
                                    <option value="<?php echo h_equipamento($familia['id_familia_equipamento']); ?>" <?php echo selected_equipamento($equipamento['id_familia_equipamento'], $familia['id_familia_equipamento']); ?>>
                                        <?php echo h_equipamento($familia['codigo_familia'] . ' - ' . $familia['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="nomeEquipamento" class="form-label">Designação *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="nomeEquipamento" name="nomeEquipamento" value="<?php echo h_equipamento($equipamento['designacao']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label">Modelo *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="modelo" name="modelo" value="<?php echo h_equipamento($equipamento['modelo']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="numeroSerie" class="form-label">Número de Série *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="numeroSerie" name="numeroSerie" value="<?php echo h_equipamento($equipamento['numero_serie']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoEntrada" class="form-label">Tipo de Entrada</label>
                            <select class="form-select campo-ficha campo-editavel" id="tipoEntrada" name="tipoEntrada">
                                <option value="">Selecionar tipo</option>
                                <option value="compra" <?php echo selected_equipamento($equipamento['tipo_entrada'], 'compra'); ?>>Compra</option>
                                <option value="doacao" <?php echo selected_equipamento($equipamento['tipo_entrada'], 'doacao'); ?>>Doação</option>
                                <option value="emprestimo" <?php echo selected_equipamento($equipamento['tipo_entrada'], 'emprestimo'); ?>>Empréstimo</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- ESTADO E LOCALIZAÇÃO -->
                <div class="tab-pane fade" id="estado-localizacao" role="tabpanel" aria-labelledby="estado-localizacao-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Estado, Criticidade e Localização</h4>
                        <p>Localização atual, estado de utilização e periodicidades de acompanhamento.</p>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-8">
                            <label for="idLocalizacao" class="form-label">Localização *</label>
                            <select class="form-select campo-ficha campo-editavel" id="idLocalizacao" name="idLocalizacao" required>
                                <option value="">Selecionar localização</option>
                                <?php foreach ($localizacoes as $localizacao): ?>
                                    <option value="<?php echo h_equipamento($localizacao['id_localizacao']); ?>" <?php echo selected_equipamento($equipamento['id_localizacao'], $localizacao['id_localizacao']); ?>>
                                        <?php echo h_equipamento($localizacao['codigo'] . ' | ' . $localizacao['departamento_nome'] . ' - ' . $localizacao['edificio'] . ' - Piso ' . $localizacao['piso'] . ' - Sala ' . $localizacao['sala']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select campo-ficha campo-editavel" id="estado" name="estado" required>
                                <option value="">Selecionar estado</option>
                                <option value="ativo" <?php echo selected_equipamento($equipamento['estado'], 'ativo'); ?>>Ativo</option>
                                <option value="avariado" <?php echo selected_equipamento($equipamento['estado'], 'avariado'); ?>>Avariado</option>
                                <option value="em_manutencao" <?php echo selected_equipamento($equipamento['estado'], 'em_manutencao'); ?>>Em manutenção</option>
                                <option value="em_calibracao" <?php echo selected_equipamento($equipamento['estado'], 'em_calibracao'); ?>>Em calibração</option>
                                <option value="inativo" <?php echo selected_equipamento($equipamento['estado'], 'inativo'); ?>>Inativo</option>
                                <option value="abatido" <?php echo selected_equipamento($equipamento['estado'], 'abatido'); ?>>Abatido</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="criticidade" class="form-label d-flex align-items-center">
                                Criticidade *
                                <button type="button"
                                        class="btn-ajuda-criticidade ms-2"
                                        data-bs-toggle="popover"
                                        data-bs-trigger="focus"
                                        data-bs-html="true"
                                        data-bs-placement="top"
                                        title="Tipos de criticidade"
                                        data-bs-content="<strong>Baixa:</strong> impacto reduzido.<br><strong>Média:</strong> afeta o serviço, mas existem alternativas.<br><strong>Alta:</strong> impacto significativo na prestação de cuidados.<br><strong>Crítica:</strong> essencial para suporte de vida ou emergência.">
                                    ?
                                </button>
                            </label>

                            <select class="form-select campo-ficha campo-editavel" id="criticidade" name="criticidade" required>
                                <option value="">Selecionar criticidade</option>
                                <option value="baixa" <?php echo selected_equipamento($equipamento['criticidade'], 'baixa'); ?>>Baixa</option>
                                <option value="media" <?php echo selected_equipamento($equipamento['criticidade'], 'media'); ?>>Média</option>
                                <option value="alta" <?php echo selected_equipamento($equipamento['criticidade'], 'alta'); ?>>Alta</option>
                                <option value="critica" <?php echo selected_equipamento($equipamento['criticidade'], 'critica'); ?>>Crítica</option>
                            </select>

                            <small id="descricaoCriticidade" class="texto-ajuda-form">
                                Selecione uma criticidade para ver a descrição.
                            </small>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeManutencao" class="form-label">Periodicidade de Manutenção</label>
                            <select class="form-select campo-ficha campo-editavel" id="periodicidadeManutencao" name="periodicidadeManutencao">
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'trienal'); ?>>Trienal</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeCalibracao" class="form-label">Periodicidade de Calibração</label>
                            <select class="form-select campo-ficha campo-editavel" id="periodicidadeCalibracao" name="periodicidadeCalibracao">
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'trienal'); ?>>Trienal</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="responsavelEquipamento" class="form-label">Responsável pelo Equipamento</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="responsavelEquipamento" name="responsavelEquipamento" value="<?php echo h_equipamento($equipamento['responsavel_equipamento']); ?>">
                        </div>

                    </div>
                </div>

                <!-- AQUISIÇÃO -->
                <div class="tab-pane fade" id="aquisicao" role="tabpanel" aria-labelledby="aquisicao-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Aquisição e Datas</h4>
                        <p>Dados administrativos de entrada, aquisição e instalação.</p>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-3">
                            <label for="valorAquisicao" class="form-label">Valor de Aquisição (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control campo-ficha campo-editavel" id="valorAquisicao" name="valorAquisicao" value="<?php echo valor_decimal_equipamento($equipamento['valor_aquisicao']); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFabrico" class="form-label">Data de Fabrico</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataFabrico" name="dataFabrico" value="<?php echo valor_data_equipamento($equipamento['data_fabrico']); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataAquisicao" class="form-label">Data de Aquisição</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataAquisicao" name="dataAquisicao" value="<?php echo valor_data_equipamento($equipamento['data_aquisicao']); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataInstalacao" class="form-label">Data de Instalação</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataInstalacao" name="dataInstalacao" value="<?php echo valor_data_equipamento($equipamento['data_instalacao']); ?>">
                        </div>

                    </div>
                </div>

                <!-- FORNECEDORES -->
                <div class="tab-pane fade" id="fornecedores" role="tabpanel" aria-labelledby="fornecedores-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Fornecedores e Garantia</h4>
                        <p>Fabricante, fornecedor comercial e eventual entidade responsável pela garantia.</p>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-4">
                            <label for="idFornecedorFabricante" class="form-label">Fornecedor Fabricante *</label>
                            <select class="form-select campo-ficha campo-editavel" id="idFornecedorFabricante" name="idFornecedorFabricante" required>
                                <option value="">Selecionar fabricante</option>
                                <?php foreach ($fornecedoresFabricantes as $fornecedor): ?>
                                    <option value="<?php echo h_equipamento($fornecedor['id_fornecedor']); ?>" <?php echo selected_equipamento($equipamento['id_fornecedor_fabricante'], $fornecedor['id_fornecedor']); ?>>
                                        <?php echo h_equipamento($fornecedor['nome_empresa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idFornecedorComercial" class="form-label">Fornecedor Comercial *</label>
                            <select class="form-select campo-ficha campo-editavel" id="idFornecedorComercial" name="idFornecedorComercial" required>
                                <option value="">Selecionar fornecedor comercial</option>
                                <?php foreach ($fornecedoresComerciais as $fornecedor): ?>
                                    <option value="<?php echo h_equipamento($fornecedor['id_fornecedor']); ?>" <?php echo selected_equipamento($equipamento['id_fornecedor_comercial'], $fornecedor['id_fornecedor']); ?>>
                                        <?php echo h_equipamento($fornecedor['nome_empresa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idFornecedorGarantia" class="form-label">Fornecedor da Garantia</label>

                            <?php
                                $opcoesGarantia = [];

                                if (!empty($equipamento['id_fornecedor_fabricante'])) {
                                    $opcoesGarantia[(int) $equipamento['id_fornecedor_fabricante']] = [
                                        'nome' => $equipamento['fabricante_nome'] ?: 'Fornecedor fabricante',
                                        'papel' => 'Fabricante'
                                    ];
                                }

                                if (!empty($equipamento['id_fornecedor_comercial'])) {
                                    $idComercialGarantia = (int) $equipamento['id_fornecedor_comercial'];

                                    if (isset($opcoesGarantia[$idComercialGarantia])) {
                                        $opcoesGarantia[$idComercialGarantia]['papel'] = 'Fabricante e Comercial';
                                    } else {
                                        $opcoesGarantia[$idComercialGarantia] = [
                                            'nome' => $equipamento['comercial_nome'] ?: 'Fornecedor comercial',
                                            'papel' => 'Comercial'
                                        ];
                                    }
                                }
                            ?>

                            <select class="form-select campo-ficha campo-editavel" id="idFornecedorGarantia" name="idFornecedorGarantia">
                                <option value="">Sem fornecedor de garantia</option>
                                <?php foreach ($opcoesGarantia as $idGarantiaOpcao => $opcaoGarantia): ?>
                                    <option value="<?php echo h_equipamento($idGarantiaOpcao); ?>" <?php echo selected_equipamento($equipamento['id_fornecedor_garantia'], $idGarantiaOpcao); ?>>
                                        <?php echo h_equipamento($opcaoGarantia['nome'] . ' (' . $opcaoGarantia['papel'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="texto-ajuda-form">A garantia só pode ser atribuída ao fabricante ou ao fornecedor comercial selecionado.</small>
                        </div>

                        <div class="col-md-3">
                            <label for="dataInicioGarantia" class="form-label">Início da Garantia</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataInicioGarantia" name="dataInicioGarantia" value="<?php echo valor_data_equipamento($equipamento['data_inicio_garantia']); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFimGarantia" class="form-label">Fim da Garantia</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataFimGarantia" name="dataFimGarantia" value="<?php echo valor_data_equipamento($equipamento['data_fim_garantia']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="observacoesFornecedor" class="form-label">Observações da Garantia/Fornecedores</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="observacoesFornecedor" name="observacoesFornecedor" value="<?php echo h_equipamento($equipamento['observacoes_fornecedor']); ?>">
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <strong>Resumo atual:</strong>
                                Fabricante: <?php echo h_equipamento($equipamento['fabricante_nome'] ?: '---'); ?> |
                                Comercial: <?php echo h_equipamento($equipamento['comercial_nome'] ?: '---'); ?> |
                                Garantia: <?php echo h_equipamento($equipamento['garantia_nome'] ?: 'sem fornecedor de garantia'); ?>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- HISTÓRICO -->
                <div class="tab-pane fade" id="historico" role="tabpanel" aria-labelledby="historico-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Histórico Técnico</h4>
                        <p>Consulta das manutenções e calibrações registadas para este equipamento, com os mesmos parâmetros de leitura.</p>
                    </div>

                    <h5 class="subtitulo-bloco-form">Manutenções</h5>
                    <div class="table-responsive tabela-container mb-4">
                        <table class="table table-hover align-middle tabela-calibracoes-manutencoes mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Processo</th>
                                    <th>Responsável</th>
                                    <th>Resultado</th>
                                    <th>Próxima data</th>
                                    <th>Garantia</th>
                                    <th>Custo</th>
                                    <th>Documento</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (empty($manutencoes)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Sem manutenções registadas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($manutencoes as $manutencao): ?>
                                        <?php
                                            $responsavelManutencao = $manutencao['fornecedor_nome']
                                                ?: ($manutencao['tecnico_interno'] ?: '---');

                                            $resultadoManutencao = !empty($manutencao['resultado'])
                                                ? ucfirst(str_replace('_', ' ', $manutencao['resultado']))
                                                : '---';

                                            $garantiaManutencao = ((int) ($manutencao['coberta_por_garantia'] ?? 0) === 1) ? 'Sim' : 'Não';
                                            $custoManutencao = formatar_custo_equipamento(
                                                $manutencao['custo'] ?? null,
                                                $manutencao['coberta_por_garantia'] ?? 0
                                            );

                                            $nomeDocumentoManutencao = $manutencao['nome_relatorio_manutencao'] ?? '';
                                            $caminhoDocumentoManutencao = $manutencao['caminho_relatorio_manutencao'] ?? '';
                                            $urlDocumentoManutencao = $caminhoDocumentoManutencao !== ''
                                                ? '../../assets/documentos/' . $caminhoDocumentoManutencao
                                                : '';

                                            $processoManutencao = !empty($manutencao['tipo_manutencao'])
                                                ? 'Manutenção ' . ucfirst($manutencao['tipo_manutencao'])
                                                : 'Manutenção';
                                        ?>

                                        <tr>
                                            <td><?php echo h_equipamento(formatar_data_equipamento($manutencao['data_manutencao'] ?? null)); ?></td>
                                            <td><?php echo h_equipamento($processoManutencao); ?></td>
                                            <td><?php echo h_equipamento($responsavelManutencao); ?></td>
                                            <td><?php echo h_equipamento($resultadoManutencao); ?></td>
                                            <td><?php echo h_equipamento(formatar_data_equipamento($manutencao['proxima_manutencao'] ?? null)); ?></td>
                                            <td>
                                                <?php if ($garantiaManutencao === 'Sim'): ?>
                                                    <span class="badge-detalhe">Sim</span>
                                                <?php else: ?>
                                                    Não
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo h_equipamento($custoManutencao); ?></td>
                                            <td>
                                                <?php if ($nomeDocumentoManutencao !== '' && $urlDocumentoManutencao !== ''): ?>
                                                    <a href="<?php echo h_equipamento($urlDocumentoManutencao); ?>" target="_blank" rel="noopener" class="btn-documento-ver">
                                                        <?php echo h_equipamento($nomeDocumentoManutencao); ?>
                                                    </a>
                                                <?php elseif ($nomeDocumentoManutencao !== ''): ?>
                                                    <?php echo h_equipamento($nomeDocumentoManutencao); ?>
                                                <?php else: ?>
                                                    ---
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="subtitulo-bloco-form">Calibrações</h5>
                    <div class="table-responsive tabela-container">
                        <table class="table table-hover align-middle tabela-calibracoes-manutencoes mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Processo</th>
                                    <th>Responsável</th>
                                    <th>Resultado</th>
                                    <th>Próxima data</th>
                                    <th>Garantia</th>
                                    <th>Custo</th>
                                    <th>Documento</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (empty($calibracoes)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Sem calibrações registadas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($calibracoes as $calibracao): ?>
                                        <?php
                                            $responsavelCalibracao = $calibracao['fornecedor_nome']
                                                ?: ($calibracao['tecnico_interno'] ?: '---');

                                            $resultadoCalibracao = !empty($calibracao['resultado'])
                                                ? ucfirst(str_replace('_', ' ', $calibracao['resultado']))
                                                : '---';

                                            $garantiaCalibracao = ((int) ($calibracao['coberta_por_garantia'] ?? 0) === 1) ? 'Sim' : 'Não';
                                            $custoCalibracao = formatar_custo_equipamento(
                                                $calibracao['custo'] ?? null,
                                                $calibracao['coberta_por_garantia'] ?? 0
                                            );

                                            $nomeDocumentoCalibracao = $calibracao['nome_documento_calibracao'] ?? '';
                                            $caminhoDocumentoCalibracao = $calibracao['caminho_documento_calibracao'] ?? '';
                                            $urlDocumentoCalibracao = $caminhoDocumentoCalibracao !== ''
                                                ? '../../assets/documentos/' . $caminhoDocumentoCalibracao
                                                : '';

                                            $processoCalibracao = !empty($calibracao['numero_certificado'])
                                                ? 'Calibração - ' . $calibracao['numero_certificado']
                                                : 'Calibração';
                                        ?>

                                        <tr>
                                            <td><?php echo h_equipamento(formatar_data_equipamento($calibracao['data_calibracao'] ?? null)); ?></td>
                                            <td><?php echo h_equipamento($processoCalibracao); ?></td>
                                            <td><?php echo h_equipamento($responsavelCalibracao); ?></td>
                                            <td><?php echo h_equipamento($resultadoCalibracao); ?></td>
                                            <td><?php echo h_equipamento(formatar_data_equipamento($calibracao['proxima_calibracao'] ?? null)); ?></td>
                                            <td>
                                                <?php if ($garantiaCalibracao === 'Sim'): ?>
                                                    <span class="badge-detalhe">Sim</span>
                                                <?php else: ?>
                                                    Não
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo h_equipamento($custoCalibracao); ?></td>
                                            <td>
                                                <?php if ($nomeDocumentoCalibracao !== '' && $urlDocumentoCalibracao !== ''): ?>
                                                    <a href="<?php echo h_equipamento($urlDocumentoCalibracao); ?>" target="_blank" rel="noopener" class="btn-documento-ver">
                                                        <?php echo h_equipamento($nomeDocumentoCalibracao); ?>
                                                    </a>
                                                <?php elseif ($nomeDocumentoCalibracao !== ''): ?>
                                                    <?php echo h_equipamento($nomeDocumentoCalibracao); ?>
                                                <?php else: ?>
                                                    ---
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- DOCUMENTOS -->
                <div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab" tabindex="0">

                    <div class="secao-ficha-titulo d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4>Documentos do Equipamento</h4>
                            <p>Manuais, datasheets, contratos, garantias e certificados associados ao equipamento.</p>
                        </div>

                        <button type="button" class="btn btn-adicionar-documento botao-edicao d-none" id="btnAdicionarDocumento">
                            <i class="fa-solid fa-plus me-2"></i> Adicionar Documento
                        </button>
                    </div>

                    <div class="documentos-lista mb-4">
                        <?php if (empty($documentos)): ?>
                            <p class="texto-ajuda-form mb-0">Ainda não existem documentos registados para este equipamento.</p>
                        <?php else: ?>
                            <?php foreach ($documentos as $documento): ?>
                                <?php $urlDocumento = '../../assets/documentos/' . $documento['caminho_ficheiro']; ?>
                                <div class="documento-item">
                                    <div class="documento-info">
                                        <span class="documento-icone"><i class="fa-solid fa-file-lines"></i></span>
                                        <div>
                                            <h5><?php echo h_equipamento($documento['nome_documento']); ?></h5>
                                            <p>
                                                <?php echo h_equipamento(texto_tipo_documento_equipamento($documento['tipo_documento'])); ?>
                                                <?php if (!empty($documento['data_documento'])): ?>
                                                    | Data: <?php echo h_equipamento($documento['data_documento']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($documento['data_validade'])): ?>
                                                    | Validade: <?php echo h_equipamento($documento['data_validade']); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="documento-acoes">
                                        <a class="btn-documento-ver" href="<?php echo h_equipamento($urlDocumento); ?>" target="_blank" rel="noopener">
                                            Ver
                                        </a>
                                        <a class="btn-documento-download" href="<?php echo h_equipamento($urlDocumento); ?>" download>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div id="listaDocumentosNovos">
                        <div class="documento-form-item botao-edicao d-none">
                            <div class="row g-4 align-items-end">

                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Documento</label>
                                    <select class="form-select campo-ficha campo-editavel" name="tipoDocumento[]">
                                        <option value="">Selecionar tipo</option>
                                        <option value="manual_instrucoes">Manual de Instruções</option>
                                        <option value="datasheet">Datasheet</option>
                                        <option value="contrato">Contrato</option>
                                        <option value="garantia">Documento de Garantia</option>
                                        <option value="certificado_calibracao">Certificado de Calibração</option>
                                        <option value="relatorio_calibracao">Relatório de Calibração</option>
                                        <option value="relatorio_manutencao">Relatório de Manutenção</option>
                                        <option value="ficha_tecnica">Ficha Técnica</option>
                                        <option value="declaracao_conformidade">Declaração de Conformidade</option>
                                        <option value="fotografia">Fotografia</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Nome do Documento</label>
                                    <input type="text" class="form-control campo-ficha campo-editavel" name="nomeDocumento[]" placeholder="Ex: Manual técnico">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Data do Documento</label>
                                    <input type="date" class="form-control campo-ficha campo-editavel" name="dataDocumento[]">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Validade <span class="text-muted fw-normal">(opcional)</span></label>
                                    <input type="date" class="form-control campo-ficha campo-editavel" name="dataValidadeDocumento[]">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Ficheiro</label>
                                    <input type="file" class="form-control campo-ficha campo-editavel" name="ficheiroDocumento[]" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                                </div>

                                <div class="col-md-12 text-end">
                                    <button type="button" class="btn btn-remover-documento" title="Remover documento">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- OBSERVAÇÕES -->
                <div class="tab-pane fade" id="observacoes-tab-pane" role="tabpanel" aria-labelledby="observacoes-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Observações Técnicas</h4>
                        <p>Notas relevantes sobre utilização, limitações, condição física ou contexto do equipamento.</p>
                    </div>

                    <textarea class="form-control campo-ficha campo-editavel" id="observacoes" name="observacoes" rows="7" placeholder="Indique observações relevantes sobre o equipamento."><?php echo h_equipamento($equipamento['observacoes']); ?></textarea>
                </div>

            </div>
        </div>

    </form>
</main>



<script>
document.addEventListener("DOMContentLoaded", function () {
    const fornecedorFabricante = document.getElementById("idFornecedorFabricante");
    const fornecedorComercial = document.getElementById("idFornecedorComercial");
    const fornecedorGarantia = document.getElementById("idFornecedorGarantia");

    if (!fornecedorFabricante || !fornecedorComercial || !fornecedorGarantia) {
        return;
    }

    function textoOpcaoSelecionada(select) {
        const opcao = select.options[select.selectedIndex];
        return opcao ? opcao.textContent.trim() : "";
    }

    function adicionarOpcao(valor, texto) {
        const opcao = document.createElement("option");
        opcao.value = valor;
        opcao.textContent = texto;
        fornecedorGarantia.appendChild(opcao);
    }

    function atualizarOpcoesGarantia() {
        const valorAtual = fornecedorGarantia.value;
        const fabricanteId = fornecedorFabricante.value;
        const comercialId = fornecedorComercial.value;
        const opcoes = [];

        if (fabricanteId) {
            opcoes.push({
                valor: fabricanteId,
                texto: textoOpcaoSelecionada(fornecedorFabricante) + " (Fabricante)"
            });
        }

        if (comercialId) {
            const opcaoExistente = opcoes.find(function (opcao) {
                return opcao.valor === comercialId;
            });

            if (opcaoExistente) {
                opcaoExistente.texto = textoOpcaoSelecionada(fornecedorComercial) + " (Fabricante e Comercial)";
            } else {
                opcoes.push({
                    valor: comercialId,
                    texto: textoOpcaoSelecionada(fornecedorComercial) + " (Comercial)"
                });
            }
        }

        fornecedorGarantia.innerHTML = "";
        adicionarOpcao("", "Sem fornecedor de garantia");

        opcoes.forEach(function (opcao) {
            adicionarOpcao(opcao.valor, opcao.texto);
        });

        const valorAindaValido = opcoes.some(function (opcao) {
            return opcao.valor === valorAtual;
        });

        fornecedorGarantia.value = valorAindaValido ? valorAtual : "";
    }

    fornecedorFabricante.addEventListener("change", atualizarOpcoesGarantia);
    fornecedorComercial.addEventListener("change", atualizarOpcoesGarantia);

    atualizarOpcoesGarantia();
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
