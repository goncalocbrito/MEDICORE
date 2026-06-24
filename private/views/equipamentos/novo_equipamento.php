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

$tipoUtilizador = $_SESSION['tipo_utilizador'] ?? '';
$isEngenheiro = ($tipoUtilizador === 'Engenheiro');

/* =========================================================
   FUNÇÕES AUXILIARES
   ========================================================= */
function h_novo_equipamento($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function valor_novo_equipamento($campo, $padrao = '')
{
    global $chaveSessao;

    return valor_temporario($chaveSessao, $campo, $padrao);
}

function selected_novo_equipamento($campo, $valor, $padrao = '')
{
    global $chaveSessao;

    $valorAtual = $_SESSION[$chaveSessao][$campo] ?? $padrao;

    return (string) $valorAtual === (string) $valor ? 'selected' : '';
}

function data_novo_equipamento($campo)
{
    global $chaveSessao;

    $valor = trim($_SESSION[$chaveSessao][$campo] ?? $_POST[$campo] ?? '');
    return $valor !== '' ? $valor : null;
}

function decimal_novo_equipamento($campo)
{
    global $chaveSessao;

    $valor = trim($_SESSION[$chaveSessao][$campo] ?? $_POST[$campo] ?? '');
    return $valor !== '' ? (float) str_replace(',', '.', $valor) : null;
}

function obter_valor_array_post($campo, $indice)
{
    return trim($_POST[$campo][$indice] ?? '');
}

function normalizar_nome_ficheiro($nome)
{
    $nome = pathinfo($nome, PATHINFO_FILENAME);
    $nome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
    $nome = preg_replace('/[^A-Za-z0-9_-]+/', '_', $nome);
    $nome = trim($nome, '_');

    return $nome !== '' ? strtolower($nome) : 'documento';
}

function gerar_codigo_novo_equipamento($pdo, $idFamilia)
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

function guardar_documentos_equipamento($pdo, $idEquipamento, $codigoEquipamento, $idEquipamentoFornecedor)
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

        if ($erro = validar_extensao_ficheiro($extensao)) {
            throw new RuntimeException($erro);
        }

        $tipoDocumento = obter_valor_array_post('tipoDocumento', $indice);
        $nomeDocumento = obter_valor_array_post('nomeDocumento', $indice);
        $dataDocumento = obter_valor_array_post('dataDocumento', $indice);
        $dataValidade = obter_valor_array_post('dataValidadeDocumento', $indice);

        $tiposEngenheiro = ['manual_instrucoes', 'ficha_tecnica', 'certificado_calibracao', 'declaracao_conformidade'];
        $tiposProibidos  = ['fotografia'];

        if (in_array($tipoDocumento, $tiposProibidos, true)) {
            continue;
        }

        $tipoUtilizadorDoc = $_SESSION['tipo_utilizador'] ?? '';
        if ($tipoUtilizadorDoc === 'Engenheiro' && $tipoDocumento !== '' && !in_array($tipoDocumento, $tiposEngenheiro, true)) {
            continue;
        }

        if ($tipoDocumento === '') {
            $tipoDocumento = 'outro';
        }

        if ($dataDocumento === '') {
            throw new RuntimeException('A data do documento é obrigatória em todos os documentos submetidos.');
        }

        if ($nomeDocumento === '') {
            $nomeDocumento = pathinfo($nomeOriginal, PATHINFO_FILENAME);
        }

        $nomeBase = normalizar_nome_ficheiro($nomeDocumento);
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
            ':observacoes' => 'Documento inserido no registo inicial do equipamento.',
            ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema'
        ]);
    }
}

/* =========================================================
   LISTAS PARA OS SELECTS
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

$fornecedoresGarantia = $pdo->query("
    SELECT id_fornecedor, nome_empresa, tipo_fornecedor
    FROM fornecedores
    WHERE isActive = 1
      AND tipo_fornecedor IN ('Fabricante', 'Comercial', 'Manutenção')
    ORDER BY nome_empresa ASC
")->fetchAll();

$fornecedores = $pdo->query("
    SELECT id_fornecedor, nome_empresa, tipo_fornecedor
    FROM fornecedores
    WHERE isActive = 1
      AND tipo_fornecedor IN ('Fabricante', 'Comercial')
    ORDER BY nome_empresa ASC
")->fetchAll();

$utilizadoresEngenheiros = $pdo->query("
    SELECT id_utilizador, nome
    FROM utilizadores
    WHERE isActive = 1
      AND tipo_utilizador = 'Engenheiro'
    ORDER BY nome ASC
")->fetchAll();


/* =========================================================
   FORMULÁRIO POR ETAPAS DO NOVO EQUIPAMENTO
   Usa as funções genéricas de funcoes.php para guardar dados
   temporários em sessão antes da criação final.
   ========================================================= */
$chaveSessao = 'novo_equipamento';
$ficheiroAtual = 'novo_equipamento.php';

$etapas = [
    'identificacao',
    'estado_localizacao',
    'aquisicao',
    'fornecedores',
    'observacoes',
    'documentos'
];

$nomesEtapasEquipamento = [
    'identificacao' => 'Identificação',
    'estado_localizacao' => 'Estado e Localização',
    'aquisicao' => 'Aquisição',
    'fornecedores' => 'Fornecedores',
    'observacoes' => 'Observações',
    'documentos' => 'Documentos'
];

$camposPorEtapa = [
    'identificacao' => [
        'idFamiliaEquipamento',
        'nomeEquipamento',
        'modelo',
        'marca',
        'numeroSerie',
    ],
    'estado_localizacao' => [
        'idLocalizacao',
        'estado',
        'criticidade',
        'periodicidadeManutencao',
        'periodicidadeCalibracao',
        'idResponsavel'
    ],
    'aquisicao' => [
        'valorAquisicao',
        'dataFabrico',
        'dataAquisicao',
        'dataInstalacao'
    ],
    'fornecedores' => [
        'idFornecedorGarantia',
        'dataInicioGarantia',
        'dataFimGarantia',
    ],
    'observacoes' => [
        'observacoes'
    ],
    'documentos' => []
];

$camposObrigatorios = [
    'identificacao' => [
        'idFamiliaEquipamento',
        'nomeEquipamento',
        'modelo',
        'marca',
        'numeroSerie',
    ],
    'estado_localizacao' => [
        'idLocalizacao',
        'estado',
        'criticidade',
        'periodicidadeManutencao',
        'periodicidadeCalibracao',
        'idResponsavel'
    ],
    'aquisicao' => [
        'dataFabrico',
        'dataAquisicao',
        'dataInstalacao'
    ],
    'fornecedores' => [
        'idFornecedorGarantia',
        'dataInicioGarantia',
        'dataFimGarantia',
    ],
    'observacoes' => [],
    'documentos' => []
];

$labelsCampos = [
    'idFamiliaEquipamento' => 'Família do equipamento',
    'nomeEquipamento' => 'Designação do equipamento',
    'modelo' => 'Modelo',
    'numeroSerie' => 'Número de série',
    'marca' => 'Marca',

    'idLocalizacao' => 'Localização',
    'estado' => 'Estado',
    'criticidade' => 'Criticidade',
    'periodicidadeManutencao' => 'Periodicidade de manutenção',
    'periodicidadeCalibracao' => 'Periodicidade de calibração',
    'idResponsavel' => 'Responsável pelo equipamento',
    'dataFabrico' => 'Data de fabrico',
    'dataAquisicao' => 'Data de aquisição',
    'dataInstalacao' => 'Data de instalação',
    'idFornecedorGarantia' => 'Fornecedor',
    'dataInicioGarantia' => 'Início da garantia',
    'dataFimGarantia' => 'Fim da garantia',
];

function validar_ordem_datas_garantia($chaveSessao)
{
    $erros = [];
    $dados = $_SESSION[$chaveSessao] ?? [];

    $dataFabrico        = $dados['dataFabrico'] ?? '';
    $dataInicioGarantia = $dados['dataInicioGarantia'] ?? '';
    $dataFimGarantia    = $dados['dataFimGarantia'] ?? '';

    if ($erro = validar_ordem_datas($dataFabrico, $dataInicioGarantia, 'O início da garantia não pode ser anterior à data de fabrico.')) {
        $erros[] = $erro;
    }
    if ($erro = validar_ordem_datas($dataInicioGarantia, $dataFimGarantia, 'O fim da garantia não pode ser anterior ao início da garantia.')) {
        $erros[] = $erro;
    }

    return $erros;
}

function validar_ordem_datas_aquisicao($chaveSessao)
{
    $erros = [];
    $dados = $_SESSION[$chaveSessao] ?? [];

    $dataFabrico    = $dados['dataFabrico'] ?? '';
    $dataAquisicao  = $dados['dataAquisicao'] ?? '';
    $dataInstalacao = $dados['dataInstalacao'] ?? '';

    if ($erro = validar_ordem_datas($dataFabrico, $dataAquisicao, 'A data de aquisição não pode ser anterior à data de fabrico.')) {
        $erros[] = $erro;
    }
    if ($erro = validar_ordem_datas($dataAquisicao, $dataInstalacao, 'A data de instalação não pode ser anterior à data de aquisição.')) {
        $erros[] = $erro;
    }

    return $erros;
}

$errosEquipamento = [];

if (isset($_GET['limpar'])) {
    unset($_SESSION[$chaveSessao]);
    header('Location: ' . $ficheiroAtual);
    exit;
}

$etapaAtual = $_GET['etapa'] ?? $etapas[0];

if (!in_array($etapaAtual, $etapas, true)) {
    $etapaAtual = $etapas[0];
}

if (!isset($_SESSION[$chaveSessao]['estado'])) {
    $_SESSION[$chaveSessao]['estado'] = 'ativo';
}

/* =========================================================
   PROCESSAMENTO DO NOVO EQUIPAMENTO POR ETAPAS
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etapaSubmetida = $_POST['etapa_atual'] ?? $etapaAtual;

    if (!in_array($etapaSubmetida, $etapas, true)) {
        $etapaSubmetida = $etapas[0];
    }

    $acaoEtapa = $_POST['acao_etapa'] ?? '';

    if ($acaoEtapa === 'limpar_etapa') {
        limpar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposPorEtapa);

        header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaSubmetida));
        exit;
    }

    guardar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposPorEtapa);

    if ($acaoEtapa === 'anterior') {
        header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode(etapa_anterior($etapaSubmetida, $etapas)));
        exit;
    }

    if (isset($_POST['etapa_destino'])) {
        $etapaDestino = $_POST['etapa_destino'];

        if (!in_array($etapaDestino, $etapas, true)) {
            $etapaDestino = $etapaSubmetida;
        }

        $indiceSubmetida = indice_etapa($etapaSubmetida, $etapas);
        $indiceDestino = indice_etapa($etapaDestino, $etapas);

        if ($indiceDestino <= $indiceSubmetida) {
            header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaDestino));
            exit;
        }

        for ($i = 0; $i < $indiceDestino; $i++) {
            $etapaValidar = $etapas[$i];
            $errosEtapa = validar_etapa_temporaria($chaveSessao, $etapaValidar, $camposObrigatorios, $labelsCampos);

            if (!empty($errosEtapa)) {
                $errosEquipamento = $errosEtapa;
                $etapaAtual = $etapaValidar;
                break;
            }
        }

        if (empty($errosEquipamento)) {
            header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($etapaDestino));
            exit;
        }
    }

    if (empty($errosEquipamento)) {
        $errosEquipamento = validar_etapa_temporaria($chaveSessao, $etapaSubmetida, $camposObrigatorios, $labelsCampos);

        if (empty($errosEquipamento) && $etapaSubmetida === 'aquisicao') {
            $errosEquipamento = validar_ordem_datas_aquisicao($chaveSessao);
        }

        if (empty($errosEquipamento) && $etapaSubmetida === 'fornecedores') {
            $errosEquipamento = validar_ordem_datas_garantia($chaveSessao);
        }

        if (!empty($errosEquipamento)) {
            $etapaAtual = $etapaSubmetida;
        } else {
            $proximaEtapa = proxima_etapa($etapaSubmetida, $etapas);

            if ($proximaEtapa !== null) {
                header('Location: ' . $ficheiroAtual . '?etapa=' . urlencode($proximaEtapa));
                exit;
            }
        }
    }

    if (empty($errosEquipamento)) {
        foreach ($etapas as $etapa) {
            $errosEtapa = validar_etapa_temporaria($chaveSessao, $etapa, $camposObrigatorios, $labelsCampos);

            if (!empty($errosEtapa)) {
                $errosEquipamento = $errosEtapa;
                $etapaAtual = $etapa;
                break;
            }
        }

        if (empty($errosEquipamento)) {
            $errosEquipamento = validar_ordem_datas_aquisicao($chaveSessao);
            if (!empty($errosEquipamento)) {
                $etapaAtual = 'aquisicao';
            }
        }

        if (empty($errosEquipamento)) {
            $errosEquipamento = validar_ordem_datas_garantia($chaveSessao);
            if (!empty($errosEquipamento)) {
                $etapaAtual = 'fornecedores';
            }
        }
    }

    if (empty($errosEquipamento)) {
        $dadosEquipamento = $_SESSION[$chaveSessao] ?? [];
    }

    if (empty($errosEquipamento)) {
        try {
            $pdo->beginTransaction();

            $codigoEquipamento = gerar_codigo_novo_equipamento(
                $pdo,
                (int) $dadosEquipamento['idFamiliaEquipamento']
            );

            if (!$codigoEquipamento) {
                throw new RuntimeException('Família de equipamento inválida.');
            }

            $nsSerie = trim($dadosEquipamento['numeroSerie'] ?? '');
            if ($nsSerie !== '') {
                $stmtNs = $pdo->prepare("SELECT COUNT(*) FROM equipamentos WHERE numero_serie = :ns");
                $stmtNs->execute([':ns' => $nsSerie]);
                if ((int) $stmtNs->fetchColumn() > 0) {
                    throw new RuntimeException('Já existe um equipamento registado com o número de série "' . htmlspecialchars($nsSerie) . '". O número de série deve ser único.');
                }
            }

            $stmtInserirEquipamento = $pdo->prepare("
                INSERT INTO equipamentos (
                    id_familia_equipamento,
                    numero_sequencial,
                    codigo_equipamento,
                    designacao,
                    modelo,
                    marca,
                    numero_serie,
                    tipo_entrada,
                    valor_aquisicao,
                    id_localizacao,
                    estado,
                    criticidade,
                    periodicidade_manutencao,
                    periodicidade_calibracao,
                    data_fabrico,
                    data_aquisicao,
                    data_instalacao,
                    id_responsavel,
                    observacoes,
                    isActive,
                    atualizado_por
                ) VALUES (
                    :id_familia_equipamento,
                    :numero_sequencial,
                    :codigo_equipamento,
                    :designacao,
                    :modelo,
                    :marca,
                    :numero_serie,
                    :tipo_entrada,
                    :valor_aquisicao,
                    :id_localizacao,
                    :estado,
                    :criticidade,
                    :periodicidade_manutencao,
                    :periodicidade_calibracao,
                    :data_fabrico,
                    :data_aquisicao,
                    :data_instalacao,
                    :id_responsavel,
                    :observacoes,
                    1,
                    :atualizado_por
                )
            ");

            $stmtInserirEquipamento->execute([
                ':id_familia_equipamento' => (int) $dadosEquipamento['idFamiliaEquipamento'],
                ':numero_sequencial' => $codigoEquipamento['numero_sequencial'],
                ':codigo_equipamento' => $codigoEquipamento['codigo_equipamento'],
                ':designacao' => trim($dadosEquipamento['nomeEquipamento'] ?? ''),
                ':modelo' => trim($dadosEquipamento['modelo'] ?? ''),
                ':marca' => trim($dadosEquipamento['marca'] ?? '') ?: null,
                ':numero_serie' => trim($dadosEquipamento['numeroSerie'] ?? ''),
                ':tipo_entrada' => null,
                ':valor_aquisicao' => $isEngenheiro ? null : decimal_novo_equipamento('valorAquisicao'),
                ':id_localizacao' => (int) $dadosEquipamento['idLocalizacao'],
                ':estado' => trim($dadosEquipamento['estado'] ?? 'ativo'),
                ':criticidade' => trim($dadosEquipamento['criticidade'] ?? ''),
                ':periodicidade_manutencao' => trim($dadosEquipamento['periodicidadeManutencao'] ?? '') ?: null,
                ':periodicidade_calibracao' => trim($dadosEquipamento['periodicidadeCalibracao'] ?? '') ?: null,
                ':data_fabrico' => data_novo_equipamento('dataFabrico'),
                ':data_aquisicao' => data_novo_equipamento('dataAquisicao'),
                ':data_instalacao' => data_novo_equipamento('dataInstalacao'),
                ':id_responsavel' => !empty($dadosEquipamento['idResponsavel']) ? (int) $dadosEquipamento['idResponsavel'] : null,
                ':observacoes' => trim($dadosEquipamento['observacoes'] ?? '') ?: null,
                ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema'
            ]);

            $idEquipamento = (int) $pdo->lastInsertId();

            $valorAquisicao = null;

            $stmtInserirFornecedores = $pdo->prepare("
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
            ");

            $stmtInserirFornecedores->execute([
                ':id_equipamento' => $idEquipamento,
                ':id_fornecedor_garantia' => trim($dadosEquipamento['idFornecedorGarantia'] ?? '') !== ''
                    ? (int) $dadosEquipamento['idFornecedorGarantia']
                    : null,
                ':data_inicio_garantia' => data_novo_equipamento('dataInicioGarantia'),
                ':data_fim_garantia' => data_novo_equipamento('dataFimGarantia'),
                ':observacoes' => null,
                ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema'
            ]);

            $idEquipamentoFornecedor = (int) $pdo->lastInsertId();

            guardar_documentos_equipamento(
                $pdo,
                $idEquipamento,
                $codigoEquipamento['codigo_equipamento'],
                $idEquipamentoFornecedor
            );

            $pdo->commit();

            unset($_SESSION[$chaveSessao]);
            header('Location: ficha_equipamento.php?ref=' . url_ref($idEquipamento) . '&criado=1');
            exit;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($e->getCode() === '23000') {
                $errosEquipamento[] = 'Já existe um equipamento com esse número de série, código ou associação de fornecedores.';
            } else {
                $errosEquipamento[] = 'Ocorreu um erro ao guardar o equipamento.';
            }

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errosEquipamento[] = $e->getMessage() ?: 'Ocorreu um erro ao guardar o equipamento.';
        }
    }
}
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private ficha-equipamento-page novo-equipamento-page">

    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h2 class="titulo-pagina mb-0" style="font-size:1.5rem;">Novo Equipamento</h2>

            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="lista_equipamentos.php" class="btn btn-cancelar">
                    <i class="fa-solid fa-xmark me-2"></i> Cancelar
                </a>

                <button type="button" class="btn btn-dados-teste" onclick="dadosTeste_novoEquipamento()">
                    <i class="fa-solid fa-flask me-2"></i> Dados de Teste
                </button>

                <button type="submit"
                        class="btn btn-limpar"
                        name="acao_etapa"
                        value="limpar_etapa"
                        form="formNovoEquipamento"
                        formnovalidate>
                    <i class="fa-solid fa-eraser me-2"></i> Limpar
                </button>

                <?php if ($etapaAtual !== $etapas[0]): ?>
                    <button type="submit"
                            class="btn btn-limpar"
                            name="acao_etapa"
                            value="anterior"
                            form="formNovoEquipamento"
                            formnovalidate>
                        <i class="fa-solid fa-arrow-left me-2"></i> Anterior
                    </button>
                <?php endif; ?>

                <button type="submit"
                        class="btn btn-guardar"
                        name="acao_etapa"
                        value="<?php echo $etapaAtual === 'documentos' ? 'finalizar' : 'continuar'; ?>"
                        form="formNovoEquipamento"
                        formnovalidate>
                    <i class="fa-solid <?php echo $etapaAtual === 'documentos' ? 'fa-floppy-disk' : 'fa-arrow-right'; ?> me-2"></i>
                    <?php echo $etapaAtual === 'documentos' ? 'Guardar Equipamento' : 'Guardar e Continuar'; ?>
                </button>
            </div>
        </div>
        <p class="subtitulo-pagina mt-1 mb-0">
            Registe os dados principais do equipamento, os fornecedores associados e a documentação inicial.
        </p>
    </div>

    <?php if (!empty($errosEquipamento)): ?>
        <div class="alert alert-danger" role="alert">
            <strong><i class="fa-solid fa-triangle-exclamation me-2"></i> Erro</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errosEquipamento as $erro): ?>
                    <li><?php echo h_novo_equipamento($erro); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="form-equipamento form-ficha-equipamento"
          id="formNovoEquipamento"
          action="novo_equipamento.php?etapa=<?php echo urlencode($etapaAtual); ?>"
          method="post"
          enctype="multipart/form-data">

        <input type="hidden" name="etapa_atual" value="<?php echo htmlspecialchars($etapaAtual); ?>">

        <div class="form-stepper"
             style="grid-template-columns: repeat(<?php echo count($etapas); ?>, minmax(0, 1fr));"
             aria-label="Progresso do registo do equipamento">
            <?php foreach ($etapas as $indice => $etapa): ?>
                <div class="<?php echo classe_stepper($etapa, $etapaAtual, $etapas); ?>">
                    <span class="form-step-numero">
                        <?php if (indice_etapa($etapa, $etapas) < indice_etapa($etapaAtual, $etapas)): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php else: ?>
                            <?php echo $indice + 1; ?>
                        <?php endif; ?>
                    </span>

                    <span class="form-step-label">
                        <?php echo htmlspecialchars($nomesEtapasEquipamento[$etapa]); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-step-heading">
            <h3>
                Etapa <?php echo indice_etapa($etapaAtual, $etapas) + 1; ?>
                de <?php echo count($etapas); ?>:
                <?php echo htmlspecialchars($nomesEtapasEquipamento[$etapaAtual]); ?>
            </h3>
        </div>

        <div class="ficha-area">

            <ul class="nav nav-tabs ficha-tabs" id="tabsNovoEquipamento" role="tablist">

                <li class="nav-item" role="presentation">
                    <button type="submit" class="<?php echo classe_tab('identificacao', $etapaAtual); ?>" id="identificacao-tab" name="etapa_destino" value="identificacao" formnovalidate role="tab" aria-controls="identificacao" aria-selected="<?php echo aria_tab('identificacao', $etapaAtual); ?>">
                        <i class="fa-solid fa-barcode me-2"></i> Identificação
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button type="submit" class="<?php echo classe_tab('estado_localizacao', $etapaAtual); ?>" id="estado-localizacao-tab" name="etapa_destino" value="estado_localizacao" formnovalidate role="tab" aria-controls="estado-localizacao" aria-selected="<?php echo aria_tab('estado_localizacao', $etapaAtual); ?>">
                        <i class="fa-solid fa-location-dot me-2"></i> Estado e Localização
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button type="submit" class="<?php echo classe_tab('aquisicao', $etapaAtual); ?>" id="aquisicao-tab" name="etapa_destino" value="aquisicao" formnovalidate role="tab" aria-controls="aquisicao" aria-selected="<?php echo aria_tab('aquisicao', $etapaAtual); ?>">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i> Aquisição
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button type="submit" class="<?php echo classe_tab('fornecedores', $etapaAtual); ?>" id="fornecedores-tab" name="etapa_destino" value="fornecedores" formnovalidate role="tab" aria-controls="fornecedores" aria-selected="<?php echo aria_tab('fornecedores', $etapaAtual); ?>">
                        <i class="fa-solid fa-truck-medical me-2"></i> Fornecedores
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button type="submit" class="<?php echo classe_tab('observacoes', $etapaAtual); ?>" id="observacoes-tab" name="etapa_destino" value="observacoes" formnovalidate role="tab" aria-controls="observacoes-tab-pane" aria-selected="<?php echo aria_tab('observacoes', $etapaAtual); ?>">
                        <i class="fa-solid fa-clipboard-list me-2"></i> Observações
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button type="submit" class="<?php echo classe_tab('documentos', $etapaAtual); ?>" id="documentos-tab" name="etapa_destino" value="documentos" formnovalidate role="tab" aria-controls="documentos" aria-selected="<?php echo aria_tab('documentos', $etapaAtual); ?>">
                        <i class="fa-solid fa-folder-open me-2"></i> Documentos
                    </button>
                </li>

            </ul>

            <div class="tab-content ficha-tab-content" id="tabsNovoEquipamentoContent">

                <!-- IDENTIFICAÇÃO -->
                <div class="<?php echo classe_painel('identificacao', $etapaAtual); ?>" id="identificacao" role="tabpanel" aria-labelledby="identificacao-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Identificação do Equipamento</h4>
                        <p>Dados base que identificam o equipamento no inventário.</p>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-4">
                            <label for="idFamiliaEquipamento" class="form-label">Família do Equipamento *</label>
                            <select class="form-select" id="idFamiliaEquipamento" name="idFamiliaEquipamento" required>
                                <option value="">Selecionar família</option>

                                <?php foreach ($familiasEquipamento as $familia): ?>
                                    <option value="<?php echo h_novo_equipamento($familia['id_familia_equipamento']); ?>" <?php echo selected_novo_equipamento('idFamiliaEquipamento', $familia['id_familia_equipamento']); ?>>
                                        <?php echo h_novo_equipamento($familia['codigo_familia'] . ' - ' . $familia['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="texto-ajuda-form">O código será gerado automaticamente com base nesta família.</small>
                        </div>

                        <div class="col-md-4">
                            <label for="nomeEquipamento" class="form-label">Designação *</label>
                            <input type="text" class="form-control" id="nomeEquipamento" name="nomeEquipamento" value="<?php echo valor_novo_equipamento('nomeEquipamento'); ?>" required placeholder="Ex: Bomba Infusora Volumétrica" maxlength="150">
                            <small class="texto-ajuda-form contador-caracteres" data-target="nomeEquipamento" data-max="150">0 / 150 caracteres</small>
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label">Modelo *</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo valor_novo_equipamento('modelo'); ?>" required placeholder="Ex: Infusomat Space" maxlength="120">
                            <small class="texto-ajuda-form contador-caracteres" data-target="modelo" data-max="120">0 / 120 caracteres</small>
                        </div>

                        <div class="col-md-4">
                            <label for="marca" class="form-label">Marca *</label>
                            <input type="text" class="form-control" id="marca" name="marca" value="<?php echo valor_novo_equipamento('marca'); ?>" required placeholder="Ex: Philips" maxlength="30">
                            <small class="texto-ajuda-form contador-caracteres" data-target="marca" data-max="30">0 / 30 caracteres</small>
                        </div>

                        <div class="col-md-4">
                            <label for="numeroSerie" class="form-label">Número de Série *</label>
                            <input type="text" class="form-control" id="numeroSerie" name="numeroSerie" value="<?php echo valor_novo_equipamento('numeroSerie'); ?>" required placeholder="Ex: SN-INF-001" maxlength="120">
                            <small class="texto-ajuda-form contador-caracteres" data-target="numeroSerie" data-max="120">0 / 120 caracteres</small>
                        </div>


                    </div>
                </div>

                <!-- ESTADO E LOCALIZAÇÃO -->
                <div class="<?php echo classe_painel('estado_localizacao', $etapaAtual); ?>" id="estado-localizacao" role="tabpanel" aria-labelledby="estado-localizacao-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Estado, Criticidade e Localização</h4>
                        <p>Defina onde o equipamento se encontra e o seu grau de impacto clínico.</p>
                    </div>

                    <div class="row g-4">

                        <?php
                        $localizacaoTexto = '';
                        $idLocalizacaoAtual = valor_novo_equipamento('idLocalizacao');
                        foreach ($localizacoes as $loc) {
                            if ((string) $idLocalizacaoAtual === (string) $loc['id_localizacao']) {
                                $localizacaoTexto = $loc['departamento_nome'] . ' - Sala ' . $loc['sala'];
                                break;
                            }
                        }
                        ?>
                        <div class="col-md-8">
                            <label for="localizacaoPesquisa" class="form-label">Localização *</label>
                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="localizacaoPesquisa"
                                    data-hidden-target="idLocalizacao"
                                    data-lista-target="listaLocalizacoes"
                                    value="<?php echo h_novo_equipamento($localizacaoTexto); ?>"
                                    placeholder="Pesquisar localização"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idLocalizacao"
                                    name="idLocalizacao"
                                    value="<?php echo h_novo_equipamento($idLocalizacaoAtual); ?>">

                                <div class="lista-registos-custom" id="listaLocalizacoes">
                                    <?php foreach ($localizacoes as $loc): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo h_novo_equipamento($loc['id_localizacao']); ?>"
                                                data-texto="<?php echo h_novo_equipamento($loc['departamento_nome'] . ' - Sala ' . $loc['sala']); ?>">
                                            <span><?php echo h_novo_equipamento($loc['departamento_nome'] . ' - Sala ' . $loc['sala']); ?></span>
                                            <small><?php echo h_novo_equipamento($loc['departamento_sigla']); ?></small>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="">Selecionar estado</option>
                                <option value="ativo" <?php echo selected_novo_equipamento('estado', 'ativo', 'ativo'); ?>>Ativo</option>
                                <option value="avariado" <?php echo selected_novo_equipamento('estado', 'avariado'); ?>>Avariado</option>
                                <option value="em_manutencao" <?php echo selected_novo_equipamento('estado', 'em_manutencao'); ?>>Em manutenção</option>
                                <option value="em_calibracao" <?php echo selected_novo_equipamento('estado', 'em_calibracao'); ?>>Em calibração</option>
                                <option value="inativo" <?php echo selected_novo_equipamento('estado', 'inativo'); ?>>Inativo</option>
                                <option value="abatido" <?php echo selected_novo_equipamento('estado', 'abatido'); ?>>Abatido</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="criticidade" class="form-label">
                                Criticidade *
                                <span class="tooltip-ajuda" tabindex="0">
                                    ?
                                    <span class="tooltip-ajuda-texto">
                                        <strong>Baixa:</strong> existem alternativas disponíveis.<br>
                                        <strong>Média:</strong> pode atrasar o serviço, mas existem alternativas.<br>
                                        <strong>Alta:</strong> impacto direto no funcionamento clínico.<br>
                                        <strong>Crítica:</strong> equipamento essencial para prestação de cuidados.
                                    </span>
                                </span>
                            </label>

                            <select class="form-select" id="criticidade" name="criticidade" required>
                                <option value="">Selecionar criticidade</option>
                                <option value="baixa" <?php echo selected_novo_equipamento('criticidade', 'baixa'); ?>>Baixa</option>
                                <option value="media" <?php echo selected_novo_equipamento('criticidade', 'media'); ?>>Média</option>
                                <option value="alta" <?php echo selected_novo_equipamento('criticidade', 'alta'); ?>>Alta</option>
                                <option value="critica" <?php echo selected_novo_equipamento('criticidade', 'critica'); ?>>Crítica</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeManutencao" class="form-label">Periodicidade de Manutenção *</label>
                            <select class="form-select" id="periodicidadeManutencao" name="periodicidadeManutencao" required>
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'trienal'); ?>>Trienal</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeCalibracao" class="form-label">Periodicidade de Calibração *</label>
                            <select class="form-select" id="periodicidadeCalibracao" name="periodicidadeCalibracao" required>
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'trienal'); ?>>Trienal</option>
                            </select>
                        </div>

                        <?php
                        $idResponsavelAtual = valor_novo_equipamento('idResponsavel');
                        $responsavelTexto = '';
                        foreach ($utilizadoresEngenheiros as $eng) {
                            if ((string) $idResponsavelAtual === (string) $eng['id_utilizador']) {
                                $responsavelTexto = $eng['nome'];
                                break;
                            }
                        }
                        ?>
                        <div class="col-md-4">
                            <label for="responsavelPesquisa" class="form-label">Responsável pelo Equipamento *</label>
                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="responsavelPesquisa"
                                    data-hidden-target="idResponsavel"
                                    data-lista-target="listaResponsaveis"
                                    value="<?php echo h_novo_equipamento($responsavelTexto); ?>"
                                    placeholder="Pesquisar engenheiro responsável"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idResponsavel"
                                    name="idResponsavel"
                                    value="<?php echo h_novo_equipamento($idResponsavelAtual); ?>">

                                <div class="lista-registos-custom" id="listaResponsaveis">
                                    <?php foreach ($utilizadoresEngenheiros as $eng): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo h_novo_equipamento($eng['id_utilizador']); ?>"
                                                data-texto="<?php echo h_novo_equipamento($eng['nome']); ?>">
                                            <span><?php echo h_novo_equipamento($eng['nome']); ?></span>
                                            <small>Engenheiro</small>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- AQUISIÇÃO -->
                <div class="<?php echo classe_painel('aquisicao', $etapaAtual); ?>" id="aquisicao" role="tabpanel" aria-labelledby="aquisicao-tab" tabindex="0">

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
                                    value="<?php echo valor_novo_equipamento('valorAquisicao'); ?>"
                                    placeholder="Ex: 2500.00"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="col-md-3">
                            <label for="dataFabrico" class="form-label">Data de Fabrico *</label>
                            <input type="date" class="form-control" id="dataFabrico" name="dataFabrico" value="<?php echo valor_novo_equipamento('dataFabrico'); ?>" required autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label for="dataAquisicao" class="form-label">Data de Aquisição *</label>
                            <input type="date" class="form-control" id="dataAquisicao" name="dataAquisicao" value="<?php echo valor_novo_equipamento('dataAquisicao'); ?>" required autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label for="dataInstalacao" class="form-label">Data de Instalação *</label>
                            <input type="date" class="form-control" id="dataInstalacao" name="dataInstalacao" value="<?php echo valor_novo_equipamento('dataInstalacao'); ?>" required autocomplete="off">
                        </div>

                    </div>
                </div>

                <!-- FORNECEDORES -->
                <div class="<?php echo classe_painel('fornecedores', $etapaAtual); ?>" id="fornecedores" role="tabpanel" aria-labelledby="fornecedores-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Fornecedores e Garantia</h4>
                        <p>Associe o fornecedor responsável pela garantia.</p>
                    </div>

                    <div class="row g-4">

                        <?php
                        $fornecedorTexto = '';
                        foreach ($fornecedores as $forn) {
                            if ((string) valor_novo_equipamento('idFornecedorGarantia') === (string) $forn['id_fornecedor']) {
                                $fornecedorTexto = $forn['nome_empresa'] . ' (' . $forn['tipo_fornecedor'] . ')';
                                break;
                            }
                        }
                        ?>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="fornecedorPesquisa" class="form-label mb-0">Fornecedor *</label>
                                <button type="button"
                                        class="btn btn-sm btn-adicionar py-0 px-2"
                                        title="Adicionar novo fornecedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalNovoFornecedor">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                    class="form-control pesquisa-registo-custom"
                                    id="fornecedorPesquisa"
                                    data-hidden-target="idFornecedorGarantia"
                                    data-lista-target="listaFornecedores"
                                    value="<?php echo h_novo_equipamento($fornecedorTexto); ?>"
                                    placeholder="Pesquisar fabricante ou comercial"
                                    autocomplete="off">

                                <input type="hidden"
                                    id="idFornecedorGarantia"
                                    name="idFornecedorGarantia"
                                    value="<?php echo valor_novo_equipamento('idFornecedorGarantia'); ?>">

                                <div class="lista-registos-custom" id="listaFornecedores">
                                    <?php foreach ($fornecedores as $forn): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo h_novo_equipamento($forn['id_fornecedor']); ?>"
                                                data-texto="<?php echo h_novo_equipamento($forn['nome_empresa'] . ' (' . $forn['tipo_fornecedor'] . ')'); ?>">
                                            <span><?php echo h_novo_equipamento($forn['nome_empresa']); ?></span>
                                            <small><?php echo h_novo_equipamento($forn['tipo_fornecedor']); ?></small>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label for="dataInicioGarantia" class="form-label">Início da Garantia *</label>
                            <input type="date" class="form-control" id="dataInicioGarantia" name="dataInicioGarantia" value="<?php echo valor_novo_equipamento('dataInicioGarantia'); ?>" required autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFimGarantia" class="form-label">Fim da Garantia *</label>
                            <input type="date" class="form-control" id="dataFimGarantia" name="dataFimGarantia" value="<?php echo valor_novo_equipamento('dataFimGarantia'); ?>" required autocomplete="off">
                        </div>

                    </div>
                </div>

                <!-- OBSERVAÇÕES -->
                <div class="<?php echo classe_painel('observacoes', $etapaAtual); ?>" id="observacoes-tab-pane" role="tabpanel" aria-labelledby="observacoes-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Observações Técnicas</h4>
                        <p>Registe notas relevantes sobre utilização, limitações, condição física ou contexto do equipamento.</p>
                    </div>

                    <textarea class="form-control" id="observacoes" name="observacoes" rows="7" maxlength="1000" placeholder="Indique observações relevantes sobre o equipamento."><?php echo valor_novo_equipamento('observacoes'); ?></textarea>
                    <small class="texto-ajuda-form contador-caracteres" data-target="observacoes" data-max="1000">0 / 1000 caracteres</small>
                </div>

            

                <!-- DOCUMENTOS -->
                <div class="<?php echo classe_painel('documentos', $etapaAtual); ?>" id="documentos" role="tabpanel" aria-labelledby="documentos-tab" tabindex="0">

                    <div class="secao-ficha-titulo d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4>Documentos do Equipamento</h4>
                            <p>Adicione manuais, datasheets, contratos, garantias ou outros ficheiros iniciais.</p>
                        </div>

                        <button type="button" class="btn btn-adicionar-documento" id="btnAdicionarDocumento">
                            <i class="fa-solid fa-plus me-2"></i> Adicionar Documento
                        </button>
                    </div>

                    <div id="listaDocumentos">
                        <div class="documento-form-item">
                            <div class="row g-4 align-items-end">

                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Documento</label>
                                    <select class="form-select" name="tipoDocumento[]">
                                        <option value="">Selecionar tipo</option>
                                        <option value="manual_instrucoes">Manual de Instruções</option>
                                        <?php if (!$isEngenheiro): ?>
                                        <option value="datasheet">Datasheet</option>
                                        <option value="contrato">Contrato</option>
                                        <option value="garantia">Documento de Garantia</option>
                                        <?php endif; ?>
                                        <option value="certificado_calibracao">Certificado de Calibração</option>
                                        <?php if (!$isEngenheiro): ?>
                                        <option value="relatorio_calibracao">Relatório de Calibração</option>
                                        <option value="relatorio_manutencao">Relatório de Manutenção</option>
                                        <?php endif; ?>
                                        <option value="ficha_tecnica">Ficha Técnica</option>
                                        <option value="declaracao_conformidade">Declaração de Conformidade</option>
                                        <?php if (!$isEngenheiro): ?>
                                        <option value="outro">Outro</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Nome do Documento</label>
                                    <input type="text" class="form-control" name="nomeDocumento[]" placeholder="Ex: Manual técnico">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Data do Documento *</label>
                                    <input type="date" class="form-control" name="dataDocumento[]" autocomplete="off">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Validade (opcional)</label>
                                    <input type="date" class="form-control" name="dataValidadeDocumento[]">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Ficheiro</label>
                                    <input type="file" class="form-control" name="ficheiroDocumento[]" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                                </div>

                                <div class="col-md-12 text-end">
                                    <button type="button" class="btn btn-remover-documento" title="Remover documento">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div></div>
        </div>

    </form>
</main>

<!-- =========================================================
     MODAL NOVO FORNECEDOR
     ========================================================= -->
<div class="modal fade" id="modalNovoFornecedor" tabindex="-1" aria-labelledby="modalNovoFornecedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoFornecedorLabel">
                    <i class="fa-solid fa-truck-medical me-2"></i> Novo Fornecedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <div id="modalFornecedorErros" class="form-alerta-erros d-none" role="alert">
                    <strong><i class="fa-solid fa-triangle-exclamation me-2"></i> Corrija os erros antes de guardar.</strong>
                    <ul id="modalFornecedorListaErros" class="mb-0 mt-2"></ul>
                </div>

                <!-- IDENTIFICAÇÃO -->
                <h6 class="secao-ficha-titulo mt-2">Identificação</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Nome do Fornecedor *</label>
                        <input type="text" class="form-control" id="mf_nome" placeholder="Ex: Philips Healthcare" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIF *</label>
                        <input type="text" class="form-control" id="mf_nif" placeholder="Ex: 514987321" maxlength="9" inputmode="numeric" autocomplete="off">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Tipo de Fornecedor *</label>
                        <select class="form-select" id="mf_tipo">
                            <option value="">Selecionar tipo</option>
                            <option value="Fabricante">Fabricante</option>
                            <option value="Comercial">Comercial</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado *</label>
                        <select class="form-select" id="mf_estado">
                            <option value="">Selecionar estado</option>
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                            <option value="Em avaliação">Em avaliação</option>
                        </select>
                    </div>
                </div>

                <!-- CONTACTOS -->
                <h6 class="secao-ficha-titulo">Contactos</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Telefone *</label>
                        <input type="text" class="form-control" id="mf_telefone" placeholder="Ex: 221234567" maxlength="9" inputmode="numeric" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email do Fornecedor</label>
                        <input type="email" class="form-control" id="mf_email_fornecedor" placeholder="Ex: geral@fornecedor.pt" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pessoa Responsável</label>
                        <input type="text" class="form-control" id="mf_contacto_responsavel" placeholder="Ex: Ana Martins" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Telefone do Contacto</label>
                        <input type="text" class="form-control" id="mf_telefone_contacto" placeholder="Ex: 912345678" maxlength="9" inputmode="numeric" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email do Contacto</label>
                        <input type="email" class="form-control" id="mf_email_contacto" placeholder="Ex: tecnico@fornecedor.pt" autocomplete="off">
                    </div>
                </div>

                <!-- MORADA -->
                <h6 class="secao-ficha-titulo">Morada</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Morada *</label>
                        <input type="text" class="form-control" id="mf_morada" placeholder="Ex: Rua da Tecnologia, nº 120" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Código Postal *</label>
                        <input type="text" class="form-control" id="mf_codigo_postal" placeholder="Ex: 4000-000" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Localidade *</label>
                        <input type="text" class="form-control" id="mf_localidade" placeholder="Ex: Porto" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">País *</label>
                        <input type="text" class="form-control" id="mf_pais" value="Portugal" autocomplete="off">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i> Cancelar
                </button>
                <button type="button" class="btn btn-guardar" id="btnGuardarFornecedor">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Fornecedor
                </button>
            </div>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
