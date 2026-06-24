<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
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

$tipoUtilizador   = $_SESSION['tipo_utilizador'] ?? '';
$isEngenheiro     = ($tipoUtilizador === 'Engenheiro');
$ehAdministrador  = ($tipoUtilizador === 'Administrador');

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

        $tipoUtilizadorDoc = $_SESSION['tipo_utilizador'] ?? '';
        if ($tipoUtilizadorDoc === 'Engenheiro' && in_array($tipoDocumento, ['contrato', 'garantia'], true)) {
            continue;
        }

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
    SELECT id_localizacao, codigo, departamento_nome, departamento_sigla, edificio, piso, sala
    FROM localizacoes
    WHERE isActive = 1
    ORDER BY codigo ASC
")->fetchAll();

$utilizadoresEngenheiros = $pdo->query("
    SELECT id_utilizador, nome
    FROM utilizadores
    WHERE isActive = 1
      AND tipo_utilizador = 'Engenheiro'
    ORDER BY nome ASC
")->fetchAll();

$fornecedoresGarantia = $pdo->query("
    SELECT id_fornecedor, nome_empresa, tipo_fornecedor
    FROM fornecedores
    WHERE isActive = 1
      AND tipo_fornecedor IN ('Fabricante', 'Comercial')
    ORDER BY nome_empresa ASC
")->fetchAll();

/* =========================================================
   ATUALIZAÇÃO DA FICHA
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'remover_documento') {
    $idDocumento = (int) ($_POST['idDocumento'] ?? 0);
    if ($idDocumento > 0 && $idEquipamento > 0) {
        $pdo->prepare("UPDATE documentos_equipamentos SET isActive = 0 WHERE id_documento_equipamento = :id AND id_equipamento = :id_eq")
            ->execute([':id' => $idDocumento, ':id_eq' => $idEquipamento]);
    }
    header('Location: ficha_equipamento.php?ref=' . url_ref($idEquipamento) . '#documentos');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    // Administrador só pode atualizar o valor de aquisição
    if ($ehAdministrador) {
        try {
            $pdo->prepare("UPDATE equipamentos SET valor_aquisicao = :valor, tipo_entrada = :tipo, atualizado_por = :por WHERE id_equipamento = :id AND isActive = 1")
                ->execute([
                    ':valor' => decimal_post_equipamento('valorAquisicao'),
                    ':tipo'  => trim($_POST['tipoEntrada'] ?? '') ?: null,
                    ':por'   => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema',
                    ':id'    => $idEquipamento
                ]);

            $codigoGuardarAdmin = $pdo->query("SELECT codigo_equipamento FROM equipamentos WHERE id_equipamento = $idEquipamento LIMIT 1")->fetchColumn();
            $idEqFornAdmin = $pdo->query("SELECT id_equipamento_fornecedor FROM equipamentos_fornecedores WHERE id_equipamento = $idEquipamento LIMIT 1")->fetchColumn() ?: null;
            guardar_documentos_ficha_equipamento($pdo, $idEquipamento, $codigoGuardarAdmin, $idEqFornAdmin);

            header('Location: ficha_equipamento.php?ref=' . url_ref($idEquipamento) . '&guardado=1');
            exit;
        } catch (Throwable $e) {
            $errosEquipamento[] = 'Erro ao guardar o valor de aquisição.';
        }
    }

    $camposObrigatorios = [
        'idFamiliaEquipamento'    => 'Família do equipamento',
        'nomeEquipamento'         => 'Designação do equipamento',
        'modelo'                  => 'Modelo',
        'marca'                   => 'Marca',
        'numeroSerie'             => 'Número de série',

        'idLocalizacao'           => 'Localização',
        'estado'                  => 'Estado',
        'criticidade'             => 'Criticidade',
        'periodicidadeManutencao' => 'Periodicidade de manutenção',
        'periodicidadeCalibracao' => 'Periodicidade de calibração',
        'idResponsavel'           => 'Responsável pelo equipamento',
        'dataAquisicao'           => 'Data de aquisição',
        'dataInstalacao'          => 'Data de instalação',
        'idFornecedorGarantia'    => 'Fornecedor',
        'dataInicioGarantia'      => 'Início da garantia',
        'dataFimGarantia'         => 'Fim da garantia',
    ];

    foreach ($camposObrigatorios as $campo => $label) {
        if (trim($_POST[$campo] ?? '') === '') {
            $errosEquipamento[] = 'O campo "' . $label . '" é obrigatório.';
        }
    }

    if (empty($errosEquipamento)) {
        $dataFabrico        = trim($_POST['dataFabrico'] ?? '');
        $dataAquisicao      = trim($_POST['dataAquisicao'] ?? '');
        $dataInstalacao     = trim($_POST['dataInstalacao'] ?? '');
        $dataInicioGarantia = trim($_POST['dataInicioGarantia'] ?? '');
        $dataFimGarantia    = trim($_POST['dataFimGarantia'] ?? '');

        if ($erro = validar_ordem_datas($dataFabrico, $dataAquisicao, 'A data de aquisição não pode ser anterior à data de fabrico.')) {
            $errosEquipamento[] = $erro;
        }
        if ($erro = validar_ordem_datas($dataAquisicao, $dataInstalacao, 'A data de instalação não pode ser anterior à data de aquisição.')) {
            $errosEquipamento[] = $erro;
        }
        if ($erro = validar_ordem_datas($dataFabrico, $dataInicioGarantia, 'O início da garantia não pode ser anterior à data de fabrico.')) {
            $errosEquipamento[] = $erro;
        }
        if ($erro = validar_ordem_datas($dataInicioGarantia, $dataFimGarantia, 'O fim da garantia não pode ser anterior ao início da garantia.')) {
            $errosEquipamento[] = $erro;
        }
    }

    $idFornecedorGarantiaPost = trim($_POST['idFornecedorGarantia'] ?? '') !== ''
        ? (int) $_POST['idFornecedorGarantia']
        : null;

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

            $nsSerie = trim($_POST['numeroSerie'] ?? '');
            if ($nsSerie !== '') {
                $stmtNs = $pdo->prepare("SELECT COUNT(*) FROM equipamentos WHERE numero_serie = :ns AND id_equipamento != :id");
                $stmtNs->execute([':ns' => $nsSerie, ':id' => $idEquipamento]);
                if ((int) $stmtNs->fetchColumn() > 0) {
                    throw new RuntimeException('Já existe um equipamento registado com o número de série "' . htmlspecialchars($nsSerie) . '". O número de série deve ser único.');
                }
            }

            $stmtAtualizarEquipamento = $pdo->prepare("
                UPDATE equipamentos
                SET
                    id_familia_equipamento = :id_familia_equipamento,
                    numero_sequencial = :numero_sequencial,
                    codigo_equipamento = :codigo_equipamento,
                    designacao = :designacao,
                    modelo = :modelo,
                    marca = :marca,
                    numero_serie = :numero_serie,
                    valor_aquisicao = :valor_aquisicao,
                    id_localizacao = :id_localizacao,
                    estado = :estado,
                    criticidade = :criticidade,
                    periodicidade_manutencao = :periodicidade_manutencao,
                    periodicidade_calibracao = :periodicidade_calibracao,
                    data_fabrico = :data_fabrico,
                    data_aquisicao = :data_aquisicao,
                    data_instalacao = :data_instalacao,
                    id_responsavel = :id_responsavel,
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
                ':marca' => trim($_POST['marca'] ?? '') ?: null,
                ':numero_serie' => trim($_POST['numeroSerie']),
                ':valor_aquisicao' => $isEngenheiro
                    ? ($equipamento['valor_aquisicao'] ?? null)
                    : decimal_post_equipamento('valorAquisicao'),
                ':id_localizacao' => (int) $_POST['idLocalizacao'],
                ':estado' => trim($_POST['estado']),
                ':criticidade' => trim($_POST['criticidade']),
                ':periodicidade_manutencao' => trim($_POST['periodicidadeManutencao'] ?? '') ?: null,
                ':periodicidade_calibracao' => trim($_POST['periodicidadeCalibracao'] ?? '') ?: null,
                ':data_fabrico' => data_post_equipamento('dataFabrico'),
                ':data_aquisicao' => data_post_equipamento('dataAquisicao'),
                ':data_instalacao' => data_post_equipamento('dataInstalacao'),
                ':id_responsavel' => !empty($_POST['idResponsavel']) ? (int) $_POST['idResponsavel'] : null,
                ':observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
                ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema',
                ':id_equipamento' => $idEquipamento
            ]);

            $stmtFornecedores = $pdo->prepare("
                INSERT INTO equipamentos_fornecedores (
                    id_equipamento,
                    id_fornecedor_garantia,
                    data_inicio_garantia,
                    data_fim_garantia,
                    observacoes,
                    isActive,
                    atualizado_por
                ) VALUES (
                    :id_equipamento,
                    :id_fornecedor_garantia,
                    :data_inicio_garantia,
                    :data_fim_garantia,
                    :observacoes,
                    1,
                    :atualizado_por
                )
                ON DUPLICATE KEY UPDATE
                    id_fornecedor_garantia = VALUES(id_fornecedor_garantia),
                    data_inicio_garantia = VALUES(data_inicio_garantia),
                    data_fim_garantia = VALUES(data_fim_garantia),
                    observacoes = VALUES(observacoes),
                    isActive = 1,
                    atualizado_por = VALUES(atualizado_por)
            ");

            $stmtFornecedores->execute([
                ':id_equipamento' => $idEquipamento,
                ':id_fornecedor_garantia' => $idFornecedorGarantiaPost,
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
    $sucessoEquipamento = 'Equipamento guardado com sucesso.';
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
        ef.id_fornecedor_garantia,
        ef.data_inicio_garantia,
        ef.data_fim_garantia,
        ef.observacoes AS observacoes_fornecedor,
        garantia.nome_empresa AS garantia_nome,
        u.nome AS responsavel_nome
    FROM equipamentos e
    INNER JOIN familias_equipamento fe
        ON fe.id_familia_equipamento = e.id_familia_equipamento
    INNER JOIN localizacoes l
        ON l.id_localizacao = e.id_localizacao
    LEFT JOIN equipamentos_fornecedores ef
        ON ef.id_equipamento = e.id_equipamento
       AND ef.isActive = 1
    LEFT JOIN fornecedores garantia
        ON garantia.id_fornecedor = ef.id_fornecedor_garantia
    LEFT JOIN utilizadores u
        ON u.id_utilizador = e.id_responsavel
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

$stmtHistoricoEquipamento = $pdo->prepare("
    SELECT
        h.id_historico_equipamento,
        h.tipo_evento,
        h.descricao,
        h.data_evento,
        h.referencia_tabela,
        h.referencia_id,
        l.codigo AS codigo_localizacao,
        l.departamento_nome,
        l.edificio,
        l.piso,
        l.sala,
        u.nome AS utilizador_nome
    FROM historico_equipamentos h
    LEFT JOIN localizacoes l
        ON l.id_localizacao = h.id_localizacao
    LEFT JOIN utilizadores u
        ON u.id_utilizador = h.id_utilizador
    WHERE h.id_equipamento = :id_equipamento
      AND h.isActive = 1
    ORDER BY h.data_evento DESC, h.id_historico_equipamento DESC
");

$stmtHistoricoEquipamento->execute([
    ':id_equipamento' => $idEquipamento
]);

$historicoEquipamento = $stmtHistoricoEquipamento->fetchAll();

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
        <a href="lista_equipamentos.php" class="btn btn-voltar btn-voltar-lista-com-confirmacao">
            <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
        </a>

        <button type="submit" class="btn btn-guardar" form="formFichaEquipamento">
            <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
        </button>
    </div>

    <?php if ($sucessoEquipamento): ?>
        <div class="form-alerta-sucesso" role="alert">
            <strong>
                <i class="fa-solid fa-circle-check me-2"></i>
                <?php echo h_equipamento($sucessoEquipamento); ?>
            </strong>
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
          enctype="multipart/form-data"
          novalidate>

        <input type="hidden" id="idEquipamento" name="idEquipamento" value="<?php echo h_equipamento($equipamento['id_equipamento']); ?>">
        <input type="hidden" name="acao" value="atualizar">
        <input type="hidden" id="modoFormulario" name="modoFormulario" value="editar">

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
                    <button class="nav-link" id="observacoes-tab" data-bs-toggle="tab" data-bs-target="#observacoes-tab-pane" type="button" role="tab" aria-controls="observacoes-tab-pane" aria-selected="false">
                        <i class="fa-solid fa-clipboard-list me-2"></i> Observações
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab" aria-controls="documentos" aria-selected="false">
                        <i class="fa-solid fa-folder-open me-2"></i> Documentos
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
                            <?php if ($ehAdministrador): ?>
                                <div class="form-control campo-ficha campo-bloqueado"><?php echo h_equipamento($equipamento['codigo_familia'] . ' - ' . $equipamento['familia_nome']); ?></div>
                                <input type="hidden" name="idFamiliaEquipamento" value="<?php echo h_equipamento($equipamento['id_familia_equipamento']); ?>">
                            <?php else: ?>
                            <select class="form-select campo-ficha campo-editavel" id="idFamiliaEquipamento" name="idFamiliaEquipamento">
                                <option value="">Selecionar família</option>
                                <?php foreach ($familiasEquipamento as $familia): ?>
                                    <option value="<?php echo h_equipamento($familia['id_familia_equipamento']); ?>" <?php echo selected_equipamento($equipamento['id_familia_equipamento'], $familia['id_familia_equipamento']); ?>>
                                        <?php echo h_equipamento($familia['codigo_familia'] . ' - ' . $familia['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="nomeEquipamento" class="form-label">Designação *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="nomeEquipamento" name="nomeEquipamento" value="<?php echo h_equipamento($equipamento['designacao']); ?>" required maxlength="150">
                            <small class="texto-ajuda-form contador-caracteres" data-target="nomeEquipamento" data-max="150">0 / 150 caracteres</small>
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label">Modelo *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="modelo" name="modelo" value="<?php echo h_equipamento($equipamento['modelo']); ?>" required maxlength="120">
                            <small class="texto-ajuda-form contador-caracteres" data-target="modelo" data-max="120">0 / 120 caracteres</small>
                        </div>

                        <div class="col-md-4">
                            <label for="marca" class="form-label">Marca *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="marca" name="marca" value="<?php echo h_equipamento($equipamento['marca'] ?? ''); ?>" required maxlength="30">
                            <small class="texto-ajuda-form contador-caracteres" data-target="marca" data-max="30">0 / 30 caracteres</small>
                        </div>

                        <div class="col-md-4">
                            <label for="numeroSerie" class="form-label">Número de Série *</label>
                            <input type="text" class="form-control campo-ficha campo-editavel" id="numeroSerie" name="numeroSerie" value="<?php echo h_equipamento($equipamento['numero_serie']); ?>" required maxlength="120">
                            <small class="texto-ajuda-form contador-caracteres" data-target="numeroSerie" data-max="120">0 / 120 caracteres</small>
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

                        <?php
                        $localizacaoFichaTexto = ($equipamento['departamento_sigla'] ?? '') . ' - Sala ' . ($equipamento['sala'] ?? '');
                        ?>
                        <div class="col-md-8">
                            <label for="localizacaoPesquisa" class="form-label">Localização *</label>
                            <?php if ($ehAdministrador): ?>
                                <div class="form-control campo-ficha campo-bloqueado"><?php echo h_equipamento($localizacaoFichaTexto); ?></div>
                                <input type="hidden" name="idLocalizacao" value="<?php echo h_equipamento($equipamento['id_localizacao'] ?? ''); ?>">
                            <?php else: ?>
                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control campo-ficha campo-editavel pesquisa-registo-custom"
                                    id="localizacaoPesquisa"
                                    data-hidden-target="idLocalizacao"
                                    data-lista-target="listaLocalizacoesFicha"
                                    value="<?php echo h_equipamento($localizacaoFichaTexto); ?>"
                                    placeholder="Pesquisar localização"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idLocalizacao"
                                    name="idLocalizacao"
                                    value="<?php echo h_equipamento($equipamento['id_localizacao'] ?? ''); ?>">

                                <div class="lista-registos-custom" id="listaLocalizacoesFicha">
                                    <?php foreach ($localizacoes as $loc): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo h_equipamento($loc['id_localizacao']); ?>"
                                                data-texto="<?php echo h_equipamento($loc['departamento_sigla'] . ' - Sala ' . $loc['sala']); ?>">
                                            <span><?php echo h_equipamento($loc['departamento_sigla'] . ' - Sala ' . $loc['sala']); ?></span>
                                            <small><?php echo h_equipamento($loc['departamento_nome']); ?></small>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado *</label>
                            <?php if ($ehAdministrador): ?>
                                <div class="form-control campo-ficha campo-bloqueado"><?php echo h_equipamento(texto_estado_equipamento($equipamento['estado'])); ?></div>
                                <input type="hidden" name="estado" value="<?php echo h_equipamento($equipamento['estado']); ?>">
                            <?php else: ?>
                            <select class="form-select campo-ficha campo-editavel" id="estado" name="estado" required>
                                <option value="">Selecionar estado</option>
                                <option value="ativo" <?php echo selected_equipamento($equipamento['estado'], 'ativo'); ?>>Ativo</option>
                                <option value="avariado" <?php echo selected_equipamento($equipamento['estado'], 'avariado'); ?>>Avariado</option>
                                <option value="em_manutencao" <?php echo selected_equipamento($equipamento['estado'], 'em_manutencao'); ?>>Em manutenção</option>
                                <option value="em_calibracao" <?php echo selected_equipamento($equipamento['estado'], 'em_calibracao'); ?>>Em calibração</option>
                                <option value="inativo" <?php echo selected_equipamento($equipamento['estado'], 'inativo'); ?>>Inativo</option>
                                <option value="abatido" <?php echo selected_equipamento($equipamento['estado'], 'abatido'); ?>>Abatido</option>
                            </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="idCriticidade" class="form-label">Criticidade *</label>
                            <?php if ($ehAdministrador): ?>
                                <div class="form-control campo-ficha campo-bloqueado"><?php echo h_equipamento(texto_criticidade_equipamento($equipamento['criticidade'])); ?></div>
                                <input type="hidden" name="criticidade" value="<?php echo h_equipamento($equipamento['criticidade']); ?>">
                            <?php else: ?>
                            <select class="form-select campo-ficha campo-editavel" id="criticidade" name="criticidade" required>
                                <option value="">Selecionar criticidade</option>
                                <option value="baixa" <?php echo selected_equipamento($equipamento['criticidade'], 'baixa'); ?>>Baixa</option>
                                <option value="media" <?php echo selected_equipamento($equipamento['criticidade'], 'media'); ?>>Média</option>
                                <option value="alta" <?php echo selected_equipamento($equipamento['criticidade'], 'alta'); ?>>Alta</option>
                                <option value="critica" <?php echo selected_equipamento($equipamento['criticidade'], 'critica'); ?>>Crítica</option>
                            </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeManutencao" class="form-label">Periodicidade de Manutenção *</label>
                            <?php if ($ehAdministrador): ?>
                                <div class="form-control campo-ficha campo-bloqueado"><?php echo h_equipamento(texto_periodicidade_equipamento($equipamento['periodicidade_manutencao'])); ?></div>
                                <input type="hidden" name="periodicidadeManutencao" value="<?php echo h_equipamento($equipamento['periodicidade_manutencao']); ?>">
                            <?php else: ?>
                            <select class="form-select campo-ficha campo-editavel" id="periodicidadeManutencao" name="periodicidadeManutencao" required>
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_equipamento($equipamento['periodicidade_manutencao'], 'trienal'); ?>>Trienal</option>
                            </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeCalibracao" class="form-label">Periodicidade de Calibração *</label>
                            <?php if ($ehAdministrador): ?>
                                <div class="form-control campo-ficha campo-bloqueado"><?php echo h_equipamento(texto_periodicidade_equipamento($equipamento['periodicidade_calibracao'])); ?></div>
                                <input type="hidden" name="periodicidadeCalibracao" value="<?php echo h_equipamento($equipamento['periodicidade_calibracao']); ?>">
                            <?php else: ?>
                            <select class="form-select campo-ficha campo-editavel" id="periodicidadeCalibracao" name="periodicidadeCalibracao" required>
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_equipamento($equipamento['periodicidade_calibracao'], 'trienal'); ?>>Trienal</option>
                            </select>
                            <?php endif; ?>
                        </div>

                        <?php
                        $idResponsavelFicha = $equipamento['id_responsavel'] ?? '';
                        $responsavelNomeFicha = $equipamento['responsavel_nome'] ?? '';
                        ?>
                        <div class="col-md-4">
                            <label for="responsavelPesquisa" class="form-label">Responsável pelo Equipamento *</label>
                            <?php if ($ehAdministrador): ?>
                                <div class="form-control campo-ficha campo-bloqueado"><?php echo h_equipamento($responsavelNomeFicha ?: '---'); ?></div>
                                <input type="hidden" name="idResponsavel" value="<?php echo h_equipamento($idResponsavelFicha); ?>">
                            <?php else: ?>
                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control campo-ficha campo-editavel pesquisa-registo-custom"
                                    id="responsavelPesquisa"
                                    data-hidden-target="idResponsavel"
                                    data-lista-target="listaResponsaveisFicha"
                                    value="<?php echo h_equipamento($responsavelNomeFicha); ?>"
                                    placeholder="Pesquisar engenheiro responsável"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idResponsavel"
                                    name="idResponsavel"
                                    value="<?php echo h_equipamento($idResponsavelFicha); ?>">

                                <div class="lista-registos-custom" id="listaResponsaveisFicha">
                                    <?php foreach ($utilizadoresEngenheiros as $eng): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo h_equipamento($eng['id_utilizador']); ?>"
                                                data-texto="<?php echo h_equipamento($eng['nome']); ?>">
                                            <span><?php echo h_equipamento($eng['nome']); ?></span>
                                            <small>Engenheiro</small>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
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

                        <?php if (!$isEngenheiro): ?>
                            <div class="col-md-4">
                                <label for="valorAquisicao" class="form-label">Custo de Aquisição</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control"
                                    id="valorAquisicao"
                                    name="valorAquisicao"
                                    value="<?php echo htmlspecialchars($equipamento['valor_aquisicao'] ?? ''); ?>"
                                    placeholder="Ex: 2500.00"
                                >
                            </div>
                        <?php endif; ?>

                        <?php if ($ehAdministrador): ?>
                        <div class="col-md-4">
                            <label for="tipoEntrada" class="form-label">Tipo de Entrada *</label>
                            <select class="form-select campo-ficha" id="tipoEntrada" name="tipoEntrada">
                                <option value="">Selecionar tipo</option>
                                <option value="compra" <?php echo selected_equipamento($equipamento['tipo_entrada'], 'compra'); ?>>Compra</option>
                                <option value="doacao" <?php echo selected_equipamento($equipamento['tipo_entrada'], 'doacao'); ?>>Doação</option>
                                <option value="emprestimo" <?php echo selected_equipamento($equipamento['tipo_entrada'], 'emprestimo'); ?>>Empréstimo</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-3">
                            <label for="dataFabrico" class="form-label">Data de Fabrico</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataFabrico" name="dataFabrico" value="<?php echo valor_data_equipamento($equipamento['data_fabrico']); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataAquisicao" class="form-label">Data de Aquisição *</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataAquisicao" name="dataAquisicao" value="<?php echo valor_data_equipamento($equipamento['data_aquisicao']); ?>" required autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label for="dataInstalacao" class="form-label">Data de Instalação *</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataInstalacao" name="dataInstalacao" value="<?php echo valor_data_equipamento($equipamento['data_instalacao']); ?>" required autocomplete="off">
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

                        <?php
                        $fornecedorGarantiaTexto = '';

                        foreach ($fornecedoresGarantia as $fornecedor) {
                            if ((string) ($equipamento['id_fornecedor_garantia'] ?? '') === (string) $fornecedor['id_fornecedor']) {
                                $fornecedorGarantiaTexto = $fornecedor['nome_empresa'] . ' (' . $fornecedor['tipo_fornecedor'] . ')';
                                break;
                            }
                        }
                        ?>

                        <div class="col-md-6">
                            <label for="fornecedorGarantiaPesquisaFicha" class="form-label">
                                Fornecedor *
                            </label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control campo-ficha campo-editavel pesquisa-registo-custom"
                                    id="fornecedorGarantiaPesquisaFicha"
                                    data-hidden-target="idFornecedorGarantia"
                                    data-lista-target="listaFornecedoresGarantiaFicha"
                                    value="<?php echo h_equipamento($fornecedorGarantiaTexto); ?>"
                                    placeholder="Pesquisar e selecionar fornecedor"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idFornecedorGarantia"
                                    name="idFornecedorGarantia"
                                    value="<?php echo h_equipamento($equipamento['id_fornecedor_garantia'] ?? ''); ?>">

                                <div class="lista-registos-custom" id="listaFornecedoresGarantiaFicha">
                                    <?php foreach ($fornecedoresGarantia as $fornecedor): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo h_equipamento($fornecedor['id_fornecedor']); ?>"
                                                data-texto="<?php echo h_equipamento($fornecedor['nome_empresa'] . ' (' . $fornecedor['tipo_fornecedor'] . ')'); ?>">
                                            <span><?php echo h_equipamento($fornecedor['nome_empresa']); ?></span>
                                            <small><?php echo h_equipamento($fornecedor['tipo_fornecedor']); ?></small>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>  
                        
                        <div class="col-md-3">
                            <label for="dataInicioGarantia" class="form-label">Início da Garantia *</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataInicioGarantia" name="dataInicioGarantia" value="<?php echo valor_data_equipamento($equipamento['data_inicio_garantia']); ?>" required autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFimGarantia" class="form-label">Fim da Garantia *</label>
                            <input type="date" class="form-control campo-ficha campo-editavel" id="dataFimGarantia" name="dataFimGarantia" value="<?php echo valor_data_equipamento($equipamento['data_fim_garantia']); ?>" required autocomplete="off">
                        </div>

                    </div>
                </div>

                <!-- HISTÓRICO -->
                <div class="tab-pane fade" id="historico" role="tabpanel" aria-labelledby="historico-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Histórico do Equipamento</h4>
                        <p>Consulta cronológica dos eventos técnicos, localizações e alterações associadas ao equipamento.</p>
                    </div>

                        <div class="historico-equipamento-lista">
                            <?php if (empty($historicoEquipamento)): ?>
                                <p class="texto-secundario">Ainda não existem registos no histórico deste equipamento.</p>
                            <?php else: ?>
                                <?php foreach ($historicoEquipamento as $item): ?>
                                    <div class="historico-equipamento-item">
                                        <div class="historico-equipamento-icone">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </div>

                                        <div class="historico-equipamento-conteudo">
                                            <div class="historico-equipamento-topo">
                                                <strong>
                                                    <?php echo h_equipamento(ucfirst(str_replace('_', ' ', $item['tipo_evento']))); ?>
                                                </strong>

                                                <span>
                                                    <?php echo !empty($item['data_evento'])
                                                        ? h_equipamento(date('d/m/Y H:i', strtotime($item['data_evento'])))
                                                        : '---'; ?>
                                                </span>
                                            </div>

                                            <p><?php echo h_equipamento($item['descricao'] ?? 'Sem descrição.'); ?></p>

                                            <?php if (!empty($item['codigo_localizacao'])): ?>
                                                <small>
                                                    <?php echo h_equipamento($item['codigo_localizacao']); ?> -
                                                    <?php echo h_equipamento($item['departamento_nome'] ?? ''); ?>

                                                    <?php if (!empty($item['edificio'])): ?>
                                                        | <?php echo h_equipamento($item['edificio']); ?>
                                                    <?php endif; ?>

                                                    <?php if (!empty($item['piso'])): ?>
                                                        | Piso <?php echo h_equipamento($item['piso']); ?>
                                                    <?php endif; ?>

                                                    <?php if (!empty($item['sala'])): ?>
                                                        | Sala <?php echo h_equipamento($item['sala']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>

                                            <?php if (!empty($item['utilizador_nome'])): ?>
                                                <small>
                                                    Registado por: <?php echo h_equipamento($item['utilizador_nome']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                </div>

                <!-- DOCUMENTOS -->
                <div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab" tabindex="0">

                    <div class="secao-ficha-titulo d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4>Documentos do Equipamento</h4>
                            <p>Manuais, datasheets, contratos, garantias e certificados associados ao equipamento.</p>
                        </div>

                        <button type="button" class="btn btn-adicionar-documento" id="btnAdicionarDocumento">
                            <i class="fa-solid fa-plus me-2"></i> Adicionar Documento
                        </button>
                    </div>

                    <div class="documentos-lista mb-4">
                        <?php if (empty($documentos)): ?>
                            <p class="texto-ajuda-form mb-0">Ainda não existem documentos registados para este equipamento.</p>
                        <?php else: ?>
                            <?php foreach ($documentos as $documento): ?>
                                <?php if ($isEngenheiro && in_array($documento['tipo_documento'], ['contrato', 'garantia'], true)): continue; endif; ?>
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
                                        <button
                                            type="button"
                                            class="btn-documento-remover"
                                            title="Remover documento"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalRemoverDocumento"
                                            data-id="<?php echo (int) $documento['id_documento_equipamento']; ?>"
                                            data-nome="<?php echo h_equipamento($documento['nome_documento']); ?>"
                                            data-ref="<?php echo url_ref($idEquipamento); ?>"
                                            data-id-equipamento="<?php echo $idEquipamento; ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div id="listaDocumentosNovos">
                        <div class="documento-form-item d-none">
                            <div class="row g-4 align-items-end">

                                <div class="col-md-5">
                                    <label class="form-label">Tipo de Documento</label>
                                    <select class="form-select campo-ficha campo-editavel" name="tipoDocumento[]">
                                        <option value="">Selecionar tipo</option>
                                        <?php if ($ehAdministrador): ?>
                                            <option value="contrato">Contrato de Aquisição</option>
                                            <option value="garantia">Contrato de Garantia</option>
                                        <?php else: ?>
                                            <option value="manual_instrucoes">Manual de Instruções</option>
                                            <option value="datasheet">Datasheet</option>
                                            <option value="certificado_calibracao">Certificado de Calibração</option>
                                            <option value="relatorio_calibracao">Relatório de Calibração</option>
                                            <option value="relatorio_manutencao">Relatório de Manutenção</option>
                                            <option value="ficha_tecnica">Ficha Técnica</option>
                                            <option value="declaracao_conformidade">Declaração de Conformidade</option>
                                            <option value="fotografia">Fotografia</option>
                                            <option value="outro">Outro</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-7">
                                    <label class="form-label">Nome do Documento</label>
                                    <input type="text" class="form-control campo-ficha campo-editavel" name="nomeDocumento[]" placeholder="Ex: Manual técnico">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Data do Documento</label>
                                    <input type="date" class="form-control campo-ficha campo-editavel doc-data-documento" name="dataDocumento[]" max="<?php echo date('Y-m-d'); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Validade <span class="text-muted fw-normal">(opcional)</span></label>
                                    <input type="date" class="form-control campo-ficha campo-editavel doc-data-validade" name="dataValidadeDocumento[]">
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label">Ficheiro</label>
                                    <input type="file" class="form-control campo-ficha campo-editavel" name="ficheiroDocumento[]" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                                </div>

                                <div class="col-md-1 text-end d-flex align-items-end justify-content-end">
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

                    <textarea class="form-control campo-ficha campo-editavel" id="observacoes" name="observacoes" rows="7" maxlength="1000" placeholder="Indique observações relevantes sobre o equipamento."><?php echo h_equipamento($equipamento['observacoes']); ?></textarea>
                    <small class="texto-ajuda-form contador-caracteres" data-target="observacoes" data-max="1000">0 / 1000 caracteres</small>
                </div>

            </div>
        </div>

    </form>
</main>

<?php if ($ehAdministrador): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.campo-editavel').forEach(function (el) {
        el.disabled = true;
    });
    document.querySelectorAll('.opcao-registo-custom').forEach(function (el) {
        el.disabled = true;
    });
    document.querySelectorAll('.lista-registos-custom').forEach(function (el) {
        el.style.display = 'none';
        el.style.pointerEvents = 'none';
    });
});
</script>
<?php endif; ?>

<!-- =========================================================
     MODAL PARA CONFIRMAR REMOÇÃO DO DOCUMENTO
     ========================================================= -->
<div
    class="modal fade"
    id="modalRemoverDocumento"
    tabindex="-1"
    aria-labelledby="modalRemoverDocumentoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-remocao-dialog">
        <div class="modal-content modal-apagar-equipamento">

            <div class="modal-header modal-remocao-header">
                <div>
                    <h5 class="modal-title" id="modalRemoverDocumentoLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Confirmar remoção
                    </h5>
                    <p class="modal-remocao-subtitulo">
                        Confirme os dados antes de remover o documento.
                    </p>
                </div>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Fechar">
                </button>
            </div>

            <div class="modal-body modal-remocao-body">
                <div class="modal-resumo-equipamento modal-resumo-remocao">
                    <div class="modal-linha">
                        <strong>Documento</strong>
                        <span id="modalRemoverDocumentoNome">---</span>
                    </div>
                </div>
                <p class="texto-confirmacao-remocao">
                    Confirma que pretende remover este documento do equipamento?
                </p>
            </div>

            <div class="modal-footer modal-remocao-footer">
                <button
                    type="button"
                    class="btn btn-cancelar-modal"
                    data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i>
                    Cancelar
                </button>

                <form id="formModalRemoverDocumento" method="post" action="">
                    <input type="hidden" name="acao" value="remover_documento">
                    <input type="hidden" name="idDocumento" id="modalRemoverDocumentoId">
                    <input type="hidden" name="idEquipamento" id="modalRemoverDocumentoIdEquipamento">
                    <button type="submit" class="btn btn-confirmar-remocao">
                        <i class="fa-solid fa-trash me-2"></i>
                        Remover Documento
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
