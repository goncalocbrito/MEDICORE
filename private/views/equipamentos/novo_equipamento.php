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
function h_novo_equipamento($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function valor_novo_equipamento($campo, $padrao = '')
{
    return h_novo_equipamento($_POST[$campo] ?? $padrao);
}

function selected_novo_equipamento($campo, $valor, $padrao = '')
{
    return (string) ($_POST[$campo] ?? $padrao) === (string) $valor ? 'selected' : '';
}

function data_novo_equipamento($campo)
{
    $valor = trim($_POST[$campo] ?? '');
    return $valor !== '' ? $valor : null;
}

function decimal_novo_equipamento($campo)
{
    $valor = trim($_POST[$campo] ?? '');
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

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            throw new RuntimeException('Formato de documento não permitido. Use PDF, PNG, JPG, JPEG, DOC ou DOCX.');
        }

        $tipoDocumento = obter_valor_array_post('tipoDocumento', $indice);
        $nomeDocumento = obter_valor_array_post('nomeDocumento', $indice);
        $dataDocumento = obter_valor_array_post('dataDocumento', $indice);
        $dataValidade = obter_valor_array_post('dataValidadeDocumento', $indice);

        if ($tipoDocumento === '') {
            $tipoDocumento = 'outro';
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


$errosEquipamento = [];

/* =========================================================
   PROCESSAMENTO DO NOVO EQUIPAMENTO
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'inserir') {
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

    $opcaoFornecedorGarantia = trim($_POST['fornecedorGarantia'] ?? '');

    if (!in_array($opcaoFornecedorGarantia, ['', 'fabricante', 'comercial'], true)) {
        $errosEquipamento[] = 'O fornecedor da garantia só pode ser o fabricante, o fornecedor comercial ou ficar vazio.';
    }

    if (
        $opcaoFornecedorGarantia !== '' &&
        data_novo_equipamento('dataFimGarantia') === null
    ) {
        $errosEquipamento[] = 'Se indicar um fornecedor de garantia, indique também a data de fim da garantia.';
    }

    if (empty($errosEquipamento)) {
        try {
            $pdo->beginTransaction();

            $codigoEquipamento = gerar_codigo_novo_equipamento(
                $pdo,
                (int) $_POST['idFamiliaEquipamento']
            );

            if (!$codigoEquipamento) {
                throw new RuntimeException('Família de equipamento inválida.');
            }

            $stmtInserirEquipamento = $pdo->prepare("
                INSERT INTO equipamentos (
                    id_familia_equipamento,
                    numero_sequencial,
                    codigo_equipamento,
                    designacao,
                    modelo,
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
                    responsavel_equipamento,
                    observacoes,
                    isActive,
                    atualizado_por
                ) VALUES (
                    :id_familia_equipamento,
                    :numero_sequencial,
                    :codigo_equipamento,
                    :designacao,
                    :modelo,
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
                    :responsavel_equipamento,
                    :observacoes,
                    1,
                    :atualizado_por
                )
            ");

            $stmtInserirEquipamento->execute([
                ':id_familia_equipamento' => (int) $_POST['idFamiliaEquipamento'],
                ':numero_sequencial' => $codigoEquipamento['numero_sequencial'],
                ':codigo_equipamento' => $codigoEquipamento['codigo_equipamento'],
                ':designacao' => trim($_POST['nomeEquipamento']),
                ':modelo' => trim($_POST['modelo']),
                ':numero_serie' => trim($_POST['numeroSerie']),
                ':tipo_entrada' => trim($_POST['tipoEntrada'] ?? '') ?: null,
                ':valor_aquisicao' => decimal_novo_equipamento('valorAquisicao'),
                ':id_localizacao' => (int) $_POST['idLocalizacao'],
                ':estado' => trim($_POST['estado']),
                ':criticidade' => trim($_POST['criticidade']),
                ':periodicidade_manutencao' => trim($_POST['periodicidadeManutencao'] ?? '') ?: null,
                ':periodicidade_calibracao' => trim($_POST['periodicidadeCalibracao'] ?? '') ?: null,
                ':data_fabrico' => data_novo_equipamento('dataFabrico'),
                ':data_aquisicao' => data_novo_equipamento('dataAquisicao'),
                ':data_instalacao' => data_novo_equipamento('dataInstalacao'),
                ':responsavel_equipamento' => trim($_POST['responsavelEquipamento'] ?? '') ?: null,
                ':observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
                ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema'
            ]);

            $idEquipamento = (int) $pdo->lastInsertId();

            $stmtInserirFornecedores = $pdo->prepare("
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
            ");

            $idFornecedorFabricante = (int) $_POST['idFornecedorFabricante'];
            $idFornecedorComercial = (int) $_POST['idFornecedorComercial'];
            $opcaoFornecedorGarantia = trim($_POST['fornecedorGarantia'] ?? '');

            $idFornecedorGarantia = null;

            if ($opcaoFornecedorGarantia === 'fabricante') {
                $idFornecedorGarantia = $idFornecedorFabricante;
            } elseif ($opcaoFornecedorGarantia === 'comercial') {
                $idFornecedorGarantia = $idFornecedorComercial;
            }

            $stmtInserirFornecedores->execute([
                ':id_equipamento' => $idEquipamento,
                ':id_fornecedor_fabricante' => $idFornecedorFabricante,
                ':id_fornecedor_comercial' => $idFornecedorComercial,
                ':id_fornecedor_garantia' => $idFornecedorGarantia,
                ':data_inicio_garantia' => data_novo_equipamento('dataInicioGarantia'),
                ':data_fim_garantia' => data_novo_equipamento('dataFimGarantia'),
                ':observacoes' => trim($_POST['observacoesFornecedor'] ?? '') ?: null,
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

            header('Location: ficha_equipamento.php?id=' . urlencode((string) $idEquipamento) . '&criado=1');
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

<main class="conteudo-private novo-equipamento-page">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="titulo-pagina">Novo Equipamento</h2>
            <p class="subtitulo-pagina">
                Registe os dados principais do equipamento, os fornecedores associados e a documentação inicial.
            </p>
        </div>

        <div class="form-actions">
            <a href="lista_equipamentos.php" class="btn btn-voltar">
                <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
            </a>

            <button type="button" class="btn btn-limpar" id="btnLimparNovoEquipamento">
                <i class="fa-solid fa-eraser me-2"></i> Limpar
            </button>

            <button type="submit" class="btn btn-guardar" form="formNovoEquipamento">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Equipamento
            </button>
        </div>
    </div>

    <?php if (!empty($errosEquipamento)): ?>
        <div class="form-alerta-erros" role="alert">
            <strong>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Não foi possível guardar o equipamento.
            </strong>

            <ul>
                <?php foreach ($errosEquipamento as $erro): ?>
                    <li><?php echo h_novo_equipamento($erro); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="form-equipamento form-ficha-equipamento"
          id="formNovoEquipamento"
          action="novo_equipamento.php"
          method="post"
          enctype="multipart/form-data">

        <input type="hidden" name="acao" value="inserir">

        <div class="ficha-area">

            <ul class="nav nav-tabs ficha-tabs" id="tabsNovoEquipamento" role="tablist">

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

            <div class="tab-content ficha-tab-content" id="tabsNovoEquipamentoContent">

                <!-- IDENTIFICAÇÃO -->
                <div class="tab-pane fade show active" id="identificacao" role="tabpanel" aria-labelledby="identificacao-tab" tabindex="0">

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
                            <input type="text" class="form-control" id="nomeEquipamento" name="nomeEquipamento" value="<?php echo valor_novo_equipamento('nomeEquipamento'); ?>" required placeholder="Ex: Bomba Infusora Volumétrica">
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label">Modelo *</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo valor_novo_equipamento('modelo'); ?>" required placeholder="Ex: Infusomat Space">
                        </div>

                        <div class="col-md-4">
                            <label for="numeroSerie" class="form-label">Número de Série *</label>
                            <input type="text" class="form-control" id="numeroSerie" name="numeroSerie" value="<?php echo valor_novo_equipamento('numeroSerie'); ?>" required placeholder="Ex: SN-INF-001">
                        </div>

                        <div class="col-md-4">
                            <label for="tipoEntrada" class="form-label">Tipo de Entrada</label>
                            <select class="form-select" id="tipoEntrada" name="tipoEntrada">
                                <option value="">Selecionar tipo</option>
                                <option value="compra" <?php echo selected_novo_equipamento('tipoEntrada', 'compra'); ?>>Compra</option>
                                <option value="doacao" <?php echo selected_novo_equipamento('tipoEntrada', 'doacao'); ?>>Doação</option>
                                <option value="emprestimo" <?php echo selected_novo_equipamento('tipoEntrada', 'emprestimo'); ?>>Empréstimo</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- ESTADO E LOCALIZAÇÃO -->
                <div class="tab-pane fade" id="estado-localizacao" role="tabpanel" aria-labelledby="estado-localizacao-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Estado, Criticidade e Localização</h4>
                        <p>Defina onde o equipamento se encontra e o seu grau de impacto clínico.</p>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-8">
                            <label for="idLocalizacao" class="form-label">Localização *</label>
                            <select class="form-select" id="idLocalizacao" name="idLocalizacao" required>
                                <option value="">Selecionar localização</option>

                                <?php foreach ($localizacoes as $localizacao): ?>
                                    <option value="<?php echo h_novo_equipamento($localizacao['id_localizacao']); ?>" <?php echo selected_novo_equipamento('idLocalizacao', $localizacao['id_localizacao']); ?>>
                                        <?php echo h_novo_equipamento($localizacao['codigo'] . ' | ' . $localizacao['departamento_nome'] . ' - ' . $localizacao['edificio'] . ' - Piso ' . $localizacao['piso'] . ' - Sala ' . $localizacao['sala']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

                            <select class="form-select" id="criticidade" name="criticidade" required>
                                <option value="">Selecionar criticidade</option>
                                <option value="baixa" <?php echo selected_novo_equipamento('criticidade', 'baixa'); ?>>Baixa</option>
                                <option value="media" <?php echo selected_novo_equipamento('criticidade', 'media'); ?>>Média</option>
                                <option value="alta" <?php echo selected_novo_equipamento('criticidade', 'alta'); ?>>Alta</option>
                                <option value="critica" <?php echo selected_novo_equipamento('criticidade', 'critica'); ?>>Crítica</option>
                            </select>

                            <small id="descricaoCriticidade" class="texto-ajuda-form">
                                Selecione uma criticidade para ver a descrição.
                            </small>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeManutencao" class="form-label">Periodicidade de Manutenção</label>
                            <select class="form-select" id="periodicidadeManutencao" name="periodicidadeManutencao">
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_novo_equipamento('periodicidadeManutencao', 'trienal'); ?>>Trienal</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidadeCalibracao" class="form-label">Periodicidade de Calibração</label>
                            <select class="form-select" id="periodicidadeCalibracao" name="periodicidadeCalibracao">
                                <option value="">Selecionar periodicidade</option>
                                <option value="semestral" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'semestral'); ?>>Semestral</option>
                                <option value="anual" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'anual'); ?>>Anual</option>
                                <option value="bienal" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'bienal'); ?>>Bienal</option>
                                <option value="trienal" <?php echo selected_novo_equipamento('periodicidadeCalibracao', 'trienal'); ?>>Trienal</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="responsavelEquipamento" class="form-label">Responsável pelo Equipamento</label>
                            <input type="text" class="form-control" id="responsavelEquipamento" name="responsavelEquipamento" value="<?php echo valor_novo_equipamento('responsavelEquipamento'); ?>" placeholder="Ex: Eng. Gonçalo">
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
                            <input type="number" step="0.01" min="0" class="form-control" id="valorAquisicao" name="valorAquisicao" value="<?php echo valor_novo_equipamento('valorAquisicao'); ?>" placeholder="Ex: 2450.00">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFabrico" class="form-label">Data de Fabrico</label>
                            <input type="date" class="form-control" id="dataFabrico" name="dataFabrico" value="<?php echo valor_novo_equipamento('dataFabrico'); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataAquisicao" class="form-label">Data de Aquisição</label>
                            <input type="date" class="form-control" id="dataAquisicao" name="dataAquisicao" value="<?php echo valor_novo_equipamento('dataAquisicao'); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataInstalacao" class="form-label">Data de Instalação</label>
                            <input type="date" class="form-control" id="dataInstalacao" name="dataInstalacao" value="<?php echo valor_novo_equipamento('dataInstalacao'); ?>">
                        </div>

                    </div>
                </div>

                <!-- FORNECEDORES -->
                <div class="tab-pane fade" id="fornecedores" role="tabpanel" aria-labelledby="fornecedores-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Fornecedores e Garantia</h4>
                        <p>Associe o fabricante, o fornecedor comercial e, se existir, o fornecedor responsável pela garantia.</p>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-4">
                            <label for="idFornecedorFabricante" class="form-label">Fornecedor Fabricante *</label>
                            <select class="form-select" id="idFornecedorFabricante" name="idFornecedorFabricante" required>
                                <option value="">Selecionar fabricante</option>

                                <?php foreach ($fornecedoresFabricantes as $fornecedor): ?>
                                    <option value="<?php echo h_novo_equipamento($fornecedor['id_fornecedor']); ?>" <?php echo selected_novo_equipamento('idFornecedorFabricante', $fornecedor['id_fornecedor']); ?>>
                                        <?php echo h_novo_equipamento($fornecedor['nome_empresa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idFornecedorComercial" class="form-label">Fornecedor Comercial *</label>
                            <select class="form-select" id="idFornecedorComercial" name="idFornecedorComercial" required>
                                <option value="">Selecionar fornecedor comercial</option>

                                <?php foreach ($fornecedoresComerciais as $fornecedor): ?>
                                    <option value="<?php echo h_novo_equipamento($fornecedor['id_fornecedor']); ?>" <?php echo selected_novo_equipamento('idFornecedorComercial', $fornecedor['id_fornecedor']); ?>>
                                        <?php echo h_novo_equipamento($fornecedor['nome_empresa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="fornecedorGarantia" class="form-label">Fornecedor da Garantia</label>
                            <select class="form-select" id="fornecedorGarantia" name="fornecedorGarantia">
                                <option value="" <?php echo selected_novo_equipamento('fornecedorGarantia', ''); ?>>Sem fornecedor de garantia</option>
                                <option value="fabricante" <?php echo selected_novo_equipamento('fornecedorGarantia', 'fabricante'); ?>>Fornecedor fabricante selecionado</option>
                                <option value="comercial" <?php echo selected_novo_equipamento('fornecedorGarantia', 'comercial'); ?>>Fornecedor comercial selecionado</option>
                            </select>
                            <small class="texto-ajuda-form">A garantia só pode ficar associada ao fabricante ou ao fornecedor comercial escolhidos acima.</small>
                        </div>

                        <div class="col-md-3">
                            <label for="dataInicioGarantia" class="form-label">Início da Garantia</label>
                            <input type="date" class="form-control" id="dataInicioGarantia" name="dataInicioGarantia" value="<?php echo valor_novo_equipamento('dataInicioGarantia'); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dataFimGarantia" class="form-label">Fim da Garantia</label>
                            <input type="date" class="form-control" id="dataFimGarantia" name="dataFimGarantia" value="<?php echo valor_novo_equipamento('dataFimGarantia'); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="observacoesFornecedor" class="form-label">Observações da Garantia/Fornecedores</label>
                            <input type="text" class="form-control" id="observacoesFornecedor" name="observacoesFornecedor" value="<?php echo valor_novo_equipamento('observacoesFornecedor'); ?>" placeholder="Ex: Garantia assegurada pelo fornecedor comercial durante 3 anos.">
                        </div>

                    </div>
                </div>

                <!-- DOCUMENTOS -->
                <div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab" tabindex="0">

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
                                    <input type="text" class="form-control" name="nomeDocumento[]" placeholder="Ex: Manual técnico">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Data do Documento</label>
                                    <input type="date" class="form-control" name="dataDocumento[]">
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
                </div>

                <!-- OBSERVAÇÕES -->
                <div class="tab-pane fade" id="observacoes-tab-pane" role="tabpanel" aria-labelledby="observacoes-tab" tabindex="0">

                    <div class="secao-ficha-titulo">
                        <h4>Observações Técnicas</h4>
                        <p>Registe notas relevantes sobre utilização, limitações, condição física ou contexto do equipamento.</p>
                    </div>

                    <textarea class="form-control" id="observacoes" name="observacoes" rows="7" placeholder="Indique observações relevantes sobre o equipamento."><?php echo valor_novo_equipamento('observacoes'); ?></textarea>
                </div>

            </div>
        </div>

    </form>
</main>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const fabricante = document.getElementById('idFornecedorFabricante');
    const comercial = document.getElementById('idFornecedorComercial');
    const garantia = document.getElementById('fornecedorGarantia');

    if (!fabricante || !comercial || !garantia) return;

    function textoSelecionado(select) {
        const opcao = select.options[select.selectedIndex];
        return opcao && opcao.value ? opcao.textContent.trim() : '';
    }

    function atualizarOpcoesGarantia() {
        const opcaoFabricante = garantia.querySelector('option[value="fabricante"]');
        const opcaoComercial = garantia.querySelector('option[value="comercial"]');

        const nomeFabricante = textoSelecionado(fabricante);
        const nomeComercial = textoSelecionado(comercial);

        if (opcaoFabricante) {
            opcaoFabricante.textContent = nomeFabricante
                ? 'Fabricante — ' + nomeFabricante
                : 'Fornecedor fabricante selecionado';
            opcaoFabricante.disabled = !nomeFabricante;
        }

        if (opcaoComercial) {
            opcaoComercial.textContent = nomeComercial
                ? 'Comercial — ' + nomeComercial
                : 'Fornecedor comercial selecionado';
            opcaoComercial.disabled = !nomeComercial;
        }

        if (garantia.value === 'fabricante' && !nomeFabricante) {
            garantia.value = '';
        }

        if (garantia.value === 'comercial' && !nomeComercial) {
            garantia.value = '';
        }
    }

    fabricante.addEventListener('change', atualizarOpcoesGarantia);
    comercial.addEventListener('change', atualizarOpcoesGarantia);
    atualizarOpcoesGarantia();
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
