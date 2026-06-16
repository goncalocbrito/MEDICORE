<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   CONSUMÍVEIS - MEDICORE
   Gestão de catálogo, stock, associações a equipamentos/acessórios
   e descontinuação lógica com isActive.
   ========================================================= */

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function selected_option($valorAtual, $valorOpcao)
{
    return ((string) $valorAtual === (string) $valorOpcao) ? 'selected' : '';
}

function checked_option($valorAtual, $valorOpcao)
{
    return ((string) $valorAtual === (string) $valorOpcao) ? 'checked' : '';
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

function decimal_obrigatorio($valor, $padrao = 0)
{
    $valor = trim((string) ($valor ?? ''));
    if ($valor === '') {
        return (float) $padrao;
    }

    return (float) str_replace(',', '.', $valor);
}

function formatar_moeda($valor)
{
    if ($valor === null || $valor === '') {
        return '---';
    }

    return number_format((float) $valor, 2, ',', '.') . ' €';
}

function formatar_quantidade($valor, $unidade)
{
    return number_format((float) $valor, 2, ',', '.') . ' ' . ($unidade ?: 'unidades');
}

function texto_categoria_consumivel($categoria)
{
    $categorias = [
        'eletrodos' => 'Elétrodos',
        'papel_tecnico' => 'Papel técnico',
        'filtros' => 'Filtros',
        'circuitos_descartaveis' => 'Circuitos descartáveis',
        'gel_contacto' => 'Gel de contacto',
        'sensores_descartaveis' => 'Sensores descartáveis',
        'reagente_calibracao' => 'Reagente de calibração',
        'material_calibracao' => 'Material de calibração',
        'outro' => 'Outro'
    ];

    return $categorias[$categoria] ?? $categoria;
}

function classe_estado_stock($estado)
{
    switch ($estado) {
        case 'Disponível':
            return 'estado-ativo';
        case 'Stock baixo':
            return 'estado-manutencao';
        case 'Sem stock':
            return 'estado-avariado';
        case 'Descontinuado':
            return 'estado-inativo';
        default:
            return 'estado-inativo';
    }
}

function gerar_codigo_consumivel(PDO $pdo)
{
    $stmt = $pdo->query("\n        SELECT CONCAT(\n            'CON-',\n            LPAD(\n                COALESCE(\n                    MAX(CAST(SUBSTRING(codigo_consumivel, 5) AS UNSIGNED)),\n                    0\n                ) + 1,\n                3,\n                '0'\n            )\n        ) AS proximo_codigo\n        FROM consumiveis\n        WHERE codigo_consumivel LIKE 'CON-%'\n    ");

    return $stmt->fetchColumn() ?: 'CON-001';
}

$pdo = null;
$erro_bd = '';
$mensagem_sucesso = '';
$consumiveis = [];
$equipamentos = [];
$acessorios = [];
$localizacoes = [];
$fornecedores = [];
$proximoCodigoConsumivel = 'CON-001';
$mostrar = $_GET['mostrar'] ?? 'ativos';

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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';
        $utilizadorAtual = $_SESSION['nome'] ?? $_SESSION['username'] ?? 'admin';

        if ($acao === 'guardar_consumivel') {
            $idConsumivel = (int) ($_POST['id_consumivel'] ?? 0);
            $nome = valor_ou_null($_POST['nomeConsumivel'] ?? null);
            $categoria = $_POST['categoriaConsumivel'] ?? 'outro';
            $unidade = valor_ou_null($_POST['unidadeConsumivel'] ?? null) ?: 'unidades';
            $idLocalizacao = (int) ($_POST['idLocalizacaoConsumivel'] ?? 0);
            $idFornecedor = (int) ($_POST['idFornecedorPreferencial'] ?? 0);
            $idFornecedor = $idFornecedor > 0 ? $idFornecedor : null;

            if (empty($nome)) {
                throw new RuntimeException('Indique o nome do consumível.');
            }

            if ($idLocalizacao <= 0) {
                throw new RuntimeException('Selecione a localização do consumível.');
            }

            $stockMinimo = decimal_obrigatorio($_POST['stockMinimoConsumivel'] ?? 0);
            $stockMaximo = decimal_ou_null($_POST['stockMaximoConsumivel'] ?? null);
            $precoUnitario = decimal_ou_null($_POST['precoUnitarioConsumivel'] ?? null);

            if ($idConsumivel > 0) {
                $stmtAtualizar = $pdo->prepare("\n                    UPDATE consumiveis\n                    SET\n                        nome = :nome,\n                        categoria = :categoria,\n                        unidade = :unidade,\n                        stock_minimo = :stock_minimo,\n                        stock_maximo = :stock_maximo,\n                        preco_unitario = :preco_unitario,\n                        id_localizacao = :id_localizacao,\n                        referencia_fabricante = :referencia_fabricante,\n                        id_fornecedor_preferencial = :id_fornecedor_preferencial,\n                        observacoes = :observacoes,\n                        atualizado_por = :atualizado_por\n                    WHERE id_consumivel = :id_consumivel\n                ");

                $stmtAtualizar->execute([
                    ':id_consumivel' => $idConsumivel,
                    ':nome' => $nome,
                    ':categoria' => $categoria,
                    ':unidade' => $unidade,
                    ':stock_minimo' => $stockMinimo,
                    ':stock_maximo' => $stockMaximo,
                    ':preco_unitario' => $precoUnitario,
                    ':id_localizacao' => $idLocalizacao,
                    ':referencia_fabricante' => valor_ou_null($_POST['referenciaFabricanteConsumivel'] ?? null),
                    ':id_fornecedor_preferencial' => $idFornecedor,
                    ':observacoes' => valor_ou_null($_POST['observacoesConsumivel'] ?? null),
                    ':atualizado_por' => $utilizadorAtual
                ]);

                header('Location: consumiveis.php?editado=1');
                exit;
            }

            $codigo = valor_ou_null($_POST['codigoConsumivel'] ?? null) ?: gerar_codigo_consumivel($pdo);
            $stockInicial = decimal_obrigatorio($_POST['stockInicialConsumivel'] ?? 0);

            $pdo->beginTransaction();

            $stmtInserir = $pdo->prepare("\n                INSERT INTO consumiveis (\n                    codigo_consumivel,\n                    nome,\n                    categoria,\n                    unidade,\n                    stock_atual,\n                    stock_minimo,\n                    stock_maximo,\n                    preco_unitario,\n                    id_localizacao,\n                    referencia_fabricante,\n                    id_fornecedor_preferencial,\n                    observacoes,\n                    atualizado_por\n                ) VALUES (\n                    :codigo_consumivel,\n                    :nome,\n                    :categoria,\n                    :unidade,\n                    :stock_atual,\n                    :stock_minimo,\n                    :stock_maximo,\n                    :preco_unitario,\n                    :id_localizacao,\n                    :referencia_fabricante,\n                    :id_fornecedor_preferencial,\n                    :observacoes,\n                    :atualizado_por\n                )\n            ");

            $stmtInserir->execute([
                ':codigo_consumivel' => $codigo,
                ':nome' => $nome,
                ':categoria' => $categoria,
                ':unidade' => $unidade,
                ':stock_atual' => $stockInicial,
                ':stock_minimo' => $stockMinimo,
                ':stock_maximo' => $stockMaximo,
                ':preco_unitario' => $precoUnitario,
                ':id_localizacao' => $idLocalizacao,
                ':referencia_fabricante' => valor_ou_null($_POST['referenciaFabricanteConsumivel'] ?? null),
                ':id_fornecedor_preferencial' => $idFornecedor,
                ':observacoes' => valor_ou_null($_POST['observacoesConsumivel'] ?? null),
                ':atualizado_por' => $utilizadorAtual
            ]);

            $idNovoConsumivel = (int) $pdo->lastInsertId();

            if ($stockInicial > 0) {
                $stmtMovimentoInicial = $pdo->prepare("\n                    INSERT INTO movimentos_stock_consumiveis (\n                        id_consumivel,\n                        tipo_movimento,\n                        quantidade,\n                        stock_anterior,\n                        stock_posterior,\n                        motivo,\n                        observacoes,\n                        atualizado_por\n                    ) VALUES (\n                        :id_consumivel,\n                        'entrada',\n                        :quantidade,\n                        0,\n                        :stock_posterior,\n                        'Stock inicial',\n                        'Registo inicial do consumível',\n                        :atualizado_por\n                    )\n                ");

                $stmtMovimentoInicial->execute([
                    ':id_consumivel' => $idNovoConsumivel,
                    ':quantidade' => $stockInicial,
                    ':stock_posterior' => $stockInicial,
                    ':atualizado_por' => $utilizadorAtual
                ]);
            }

            $pdo->commit();

            header('Location: consumiveis.php?criado=1');
            exit;
        }

        if ($acao === 'movimentar_stock') {
            $idConsumivel = (int) ($_POST['id_consumivel'] ?? 0);
            $tipoMovimento = $_POST['tipoMovimentoStock'] ?? '';
            $quantidade = decimal_obrigatorio($_POST['quantidadeMovimentoStock'] ?? 0);
            $idEquipamento = (int) ($_POST['idEquipamentoMovimentoStock'] ?? 0);
            $idAcessorio = (int) ($_POST['idAcessorioMovimentoStock'] ?? 0);

            $idEquipamento = $idEquipamento > 0 ? $idEquipamento : null;
            $idAcessorio = $idAcessorio > 0 ? $idAcessorio : null;

            $tiposPermitidos = ['entrada', 'saida', 'ajuste', 'consumo_calibracao', 'devolucao'];

            if ($idConsumivel <= 0) {
                throw new RuntimeException('Consumível inválido.');
            }

            if (!in_array($tipoMovimento, $tiposPermitidos, true)) {
                throw new RuntimeException('Tipo de movimento inválido.');
            }

            if ($quantidade < 0) {
                throw new RuntimeException('A quantidade não pode ser negativa.');
            }

            $pdo->beginTransaction();

            $stmtStock = $pdo->prepare("\n                SELECT stock_atual\n                FROM consumiveis\n                WHERE id_consumivel = :id_consumivel\n                FOR UPDATE\n            ");

            $stmtStock->execute([':id_consumivel' => $idConsumivel]);
            $stockAnterior = $stmtStock->fetchColumn();

            if ($stockAnterior === false) {
                throw new RuntimeException('Consumível não encontrado.');
            }

            $stockAnterior = (float) $stockAnterior;

            if ($tipoMovimento === 'entrada' || $tipoMovimento === 'devolucao') {
                if ($quantidade <= 0) {
                    throw new RuntimeException('A quantidade tem de ser superior a zero.');
                }
                $stockPosterior = $stockAnterior + $quantidade;
            } elseif ($tipoMovimento === 'saida' || $tipoMovimento === 'consumo_calibracao') {
                if ($quantidade <= 0) {
                    throw new RuntimeException('A quantidade tem de ser superior a zero.');
                }
                if ($stockAnterior < $quantidade) {
                    throw new RuntimeException('Stock insuficiente para esta saída.');
                }
                $stockPosterior = $stockAnterior - $quantidade;
            } else {
                $stockPosterior = $quantidade;
            }

            $stmtMovimento = $pdo->prepare("\n                INSERT INTO movimentos_stock_consumiveis (\n                    id_consumivel,\n                    tipo_movimento,\n                    quantidade,\n                    stock_anterior,\n                    stock_posterior,\n                    id_equipamento,\n                    id_acessorio,\n                    id_calibracao,\n                    motivo,\n                    observacoes,\n                    atualizado_por\n                ) VALUES (\n                    :id_consumivel,\n                    :tipo_movimento,\n                    :quantidade,\n                    :stock_anterior,\n                    :stock_posterior,\n                    :id_equipamento,\n                    :id_acessorio,\n                    NULL,\n                    :motivo,\n                    :observacoes,\n                    :atualizado_por\n                )\n            ");

            $stmtMovimento->execute([
                ':id_consumivel' => $idConsumivel,
                ':tipo_movimento' => $tipoMovimento,
                ':quantidade' => $quantidade,
                ':stock_anterior' => $stockAnterior,
                ':stock_posterior' => $stockPosterior,
                ':id_equipamento' => $idEquipamento,
                ':id_acessorio' => $idAcessorio,
                ':motivo' => valor_ou_null($_POST['motivoMovimentoStock'] ?? null),
                ':observacoes' => valor_ou_null($_POST['observacoesMovimentoStock'] ?? null),
                ':atualizado_por' => $utilizadorAtual
            ]);

            $stmtAtualizarStock = $pdo->prepare("\n                UPDATE consumiveis\n                SET\n                    stock_atual = :stock_atual,\n                    atualizado_por = :atualizado_por\n                WHERE id_consumivel = :id_consumivel\n            ");

            $stmtAtualizarStock->execute([
                ':stock_atual' => $stockPosterior,
                ':atualizado_por' => $utilizadorAtual,
                ':id_consumivel' => $idConsumivel
            ]);

            $pdo->commit();

            header('Location: consumiveis.php?stock=1');
            exit;
        }

        if ($acao === 'associar_consumivel') {
            $idConsumivel = (int) ($_POST['id_consumivel'] ?? 0);
            $tipoAssociacao = $_POST['tipoAssociacaoConsumivel'] ?? '';
            $necessarioUtilizacao = (int) ($_POST['necessarioUtilizacao'] ?? 0);
            $necessarioCalibracao = (int) ($_POST['necessarioCalibracao'] ?? 0);
            $quantidadePrevista = decimal_ou_null($_POST['quantidadePrevistaAssociacao'] ?? null);
            $observacoes = valor_ou_null($_POST['observacoesAssociacao'] ?? null);

            if ($idConsumivel <= 0) {
                throw new RuntimeException('Consumível inválido.');
            }

            if ($tipoAssociacao === 'equipamento') {
                $idEquipamento = (int) ($_POST['idEquipamentoAssociacao'] ?? 0);

                if ($idEquipamento <= 0) {
                    throw new RuntimeException('Selecione o equipamento associado.');
                }

                $stmtAssociar = $pdo->prepare("\n                    INSERT INTO consumiveis_equipamentos (\n                        id_consumivel,\n                        id_equipamento,\n                        necessario_utilizacao,\n                        necessario_calibracao,\n                        quantidade_prevista,\n                        observacoes,\n                        isActive,\n                        atualizado_por\n                    ) VALUES (\n                        :id_consumivel,\n                        :id_equipamento,\n                        :necessario_utilizacao,\n                        :necessario_calibracao,\n                        :quantidade_prevista,\n                        :observacoes,\n                        1,\n                        :atualizado_por\n                    )\n                    ON DUPLICATE KEY UPDATE\n                        necessario_utilizacao = VALUES(necessario_utilizacao),\n                        necessario_calibracao = VALUES(necessario_calibracao),\n                        quantidade_prevista = VALUES(quantidade_prevista),\n                        observacoes = VALUES(observacoes),\n                        isActive = 1,\n                        atualizado_por = VALUES(atualizado_por)\n                ");

                $stmtAssociar->execute([
                    ':id_consumivel' => $idConsumivel,
                    ':id_equipamento' => $idEquipamento,
                    ':necessario_utilizacao' => $necessarioUtilizacao,
                    ':necessario_calibracao' => $necessarioCalibracao,
                    ':quantidade_prevista' => $quantidadePrevista,
                    ':observacoes' => $observacoes,
                    ':atualizado_por' => $utilizadorAtual
                ]);
            } elseif ($tipoAssociacao === 'acessorio') {
                $idAcessorio = (int) ($_POST['idAcessorioAssociacao'] ?? 0);

                if ($idAcessorio <= 0) {
                    throw new RuntimeException('Selecione o acessório associado.');
                }

                $stmtAssociar = $pdo->prepare("\n                    INSERT INTO consumiveis_acessorios (\n                        id_consumivel,\n                        id_acessorio,\n                        necessario_utilizacao,\n                        necessario_calibracao,\n                        quantidade_prevista,\n                        observacoes,\n                        isActive,\n                        atualizado_por\n                    ) VALUES (\n                        :id_consumivel,\n                        :id_acessorio,\n                        :necessario_utilizacao,\n                        :necessario_calibracao,\n                        :quantidade_prevista,\n                        :observacoes,\n                        1,\n                        :atualizado_por\n                    )\n                    ON DUPLICATE KEY UPDATE\n                        necessario_utilizacao = VALUES(necessario_utilizacao),\n                        necessario_calibracao = VALUES(necessario_calibracao),\n                        quantidade_prevista = VALUES(quantidade_prevista),\n                        observacoes = VALUES(observacoes),\n                        isActive = 1,\n                        atualizado_por = VALUES(atualizado_por)\n                ");

                $stmtAssociar->execute([
                    ':id_consumivel' => $idConsumivel,
                    ':id_acessorio' => $idAcessorio,
                    ':necessario_utilizacao' => $necessarioUtilizacao,
                    ':necessario_calibracao' => $necessarioCalibracao,
                    ':quantidade_prevista' => $quantidadePrevista,
                    ':observacoes' => $observacoes,
                    ':atualizado_por' => $utilizadorAtual
                ]);
            } else {
                throw new RuntimeException('Tipo de associação inválido.');
            }

            header('Location: consumiveis.php?associado=1');
            exit;
        }

        if ($acao === 'descontinuar_consumivel') {
            $idConsumivel = (int) ($_POST['id_consumivel'] ?? 0);

            if ($idConsumivel > 0) {
                $stmtDescontinuar = $pdo->prepare("\n                    UPDATE consumiveis\n                    SET\n                        isActive = 0,\n                        atualizado_por = :atualizado_por\n                    WHERE id_consumivel = :id_consumivel\n                ");

                $stmtDescontinuar->execute([
                    ':id_consumivel' => $idConsumivel,
                    ':atualizado_por' => $utilizadorAtual
                ]);
            }

            header('Location: consumiveis.php?descontinuado=1');
            exit;
        }

        if ($acao === 'reativar_consumivel') {
            $idConsumivel = (int) ($_POST['id_consumivel'] ?? 0);

            if ($idConsumivel > 0) {
                $stmtReativar = $pdo->prepare("\n                    UPDATE consumiveis\n                    SET\n                        isActive = 1,\n                        atualizado_por = :atualizado_por\n                    WHERE id_consumivel = :id_consumivel\n                ");

                $stmtReativar->execute([
                    ':id_consumivel' => $idConsumivel,
                    ':atualizado_por' => $utilizadorAtual
                ]);
            }

            header('Location: consumiveis.php?mostrar=todos&reativado=1');
            exit;
        }
    }

    $proximoCodigoConsumivel = gerar_codigo_consumivel($pdo);

    $stmtEquipamentos = $pdo->query("\n        SELECT id_equipamento, codigo_equipamento, designacao\n        FROM equipamentos\n        WHERE isActive = 1\n        ORDER BY codigo_equipamento ASC\n    ");
    $equipamentos = $stmtEquipamentos->fetchAll();

    $stmtAcessorios = $pdo->query("\n        SELECT\n            a.id_acessorio,\n            CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0')) AS codigo_acessorio,\n            a.designacao AS acessorio_nome,\n            e.codigo_equipamento,\n            e.designacao AS equipamento_nome\n        FROM acessorios_equipamento a\n        INNER JOIN equipamentos e\n            ON e.id_equipamento = a.id_equipamento\n        WHERE a.isActive = 1\n        ORDER BY e.codigo_equipamento ASC, a.numero_sequencial ASC\n    ");
    $acessorios = $stmtAcessorios->fetchAll();

    $stmtLocalizacoes = $pdo->query("\n        SELECT id_localizacao, codigo, departamento_nome, edificio, piso, sala\n        FROM localizacoes\n        WHERE isActive = 1\n        ORDER BY departamento_nome ASC, edificio ASC, piso ASC, sala ASC\n    ");
    $localizacoes = $stmtLocalizacoes->fetchAll();

    $stmtFornecedores = $pdo->query("\n        SELECT id_fornecedor, nome_empresa, tipo_fornecedor\n        FROM fornecedores\n        WHERE isActive = 1\n        ORDER BY nome_empresa ASC\n    ");
    $fornecedores = $stmtFornecedores->fetchAll();

    $whereAtivos = $mostrar === 'todos' ? '' : 'WHERE c.isActive = 1';

    $stmtConsumiveis = $pdo->query("\n        SELECT\n            c.*,\n            l.codigo AS codigo_localizacao,\n            l.departamento_nome,\n            l.edificio,\n            l.piso,\n            l.sala,\n            f.nome_empresa AS fornecedor_preferencial_nome,\n            (c.stock_atual * c.preco_unitario) AS valor_total_stock,\n\n            CASE\n                WHEN c.isActive = 0 THEN 'Descontinuado'\n                WHEN c.stock_atual = 0 THEN 'Sem stock'\n                WHEN c.stock_atual <= c.stock_minimo THEN 'Stock baixo'\n                ELSE 'Disponível'\n            END AS estado_stock,\n\n            (\n                SELECT GROUP_CONCAT(CONCAT(e.codigo_equipamento, ' - ', e.designacao) SEPARATOR ' | ')\n                FROM consumiveis_equipamentos ce\n                INNER JOIN equipamentos e\n                    ON e.id_equipamento = ce.id_equipamento\n                WHERE ce.id_consumivel = c.id_consumivel\n                  AND ce.isActive = 1\n            ) AS equipamentos_associados,\n\n            (\n                SELECT GROUP_CONCAT(CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0'), ' - ', a.designacao) SEPARATOR ' | ')\n                FROM consumiveis_acessorios ca\n                INNER JOIN acessorios_equipamento a\n                    ON a.id_acessorio = ca.id_acessorio\n                INNER JOIN equipamentos e\n                    ON e.id_equipamento = a.id_equipamento\n                WHERE ca.id_consumivel = c.id_consumivel\n                  AND ca.isActive = 1\n            ) AS acessorios_associados\n\n        FROM consumiveis c\n        INNER JOIN localizacoes l\n            ON l.id_localizacao = c.id_localizacao\n        LEFT JOIN fornecedores f\n            ON f.id_fornecedor = c.id_fornecedor_preferencial\n        $whereAtivos\n        ORDER BY c.codigo_consumivel ASC\n    ");

    $consumiveis = $stmtConsumiveis->fetchAll();

} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $erro_bd = $e->getMessage();
}

if (isset($_GET['criado'])) {
    $mensagem_sucesso = 'Consumível registado com sucesso.';
} elseif (isset($_GET['editado'])) {
    $mensagem_sucesso = 'Consumível atualizado com sucesso.';
} elseif (isset($_GET['stock'])) {
    $mensagem_sucesso = 'Movimento de stock registado com sucesso.';
} elseif (isset($_GET['associado'])) {
    $mensagem_sucesso = 'Associação do consumível registada com sucesso.';
} elseif (isset($_GET['descontinuado'])) {
    $mensagem_sucesso = 'Consumível descontinuado com sucesso.';
} elseif (isset($_GET['reativado'])) {
    $mensagem_sucesso = 'Consumível reativado com sucesso.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<style>
    /* =========================================================
       MODAIS DE CONSUMÍVEIS
       Garante largura útil e scroll interno nos formulários.
       ========================================================= */
    #modalConsumivelBD .modal-dialog {
        width: min(1180px, calc(100vw - 32px));
        max-width: min(1180px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modalStockConsumivelBD .modal-dialog,
    #modalAssociarConsumivelBD .modal-dialog {
        width: min(960px, calc(100vw - 32px));
        max-width: min(960px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modalConsumivelBD .modal-content,
    #modalStockConsumivelBD .modal-content,
    #modalAssociarConsumivelBD .modal-content {
        border: 0;
        border-radius: 18px;
        max-height: calc(100vh - 32px);
        overflow: hidden;
    }

    #modalConsumivelBD .modal-content > form,
    #modalStockConsumivelBD .modal-content > form,
    #modalAssociarConsumivelBD .modal-content > form {
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 32px);
        min-height: 0;
    }

    #modalConsumivelBD .modal-header,
    #modalConsumivelBD .modal-footer,
    #modalStockConsumivelBD .modal-header,
    #modalStockConsumivelBD .modal-footer,
    #modalAssociarConsumivelBD .modal-header,
    #modalAssociarConsumivelBD .modal-footer {
        flex: 0 0 auto;
    }

    #modalConsumivelBD .modal-body,
    #modalStockConsumivelBD .modal-body,
    #modalAssociarConsumivelBD .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        max-height: calc(100vh - 190px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1.5rem;
    }

    #modalConsumivelBD .modal-body .row,
    #modalStockConsumivelBD .modal-body .row,
    #modalAssociarConsumivelBD .modal-body .row {
        row-gap: 1rem;
    }

    #modalConsumivelBD input,
    #modalConsumivelBD select,
    #modalConsumivelBD textarea,
    #modalStockConsumivelBD input,
    #modalStockConsumivelBD select,
    #modalStockConsumivelBD textarea,
    #modalAssociarConsumivelBD input,
    #modalAssociarConsumivelBD select,
    #modalAssociarConsumivelBD textarea {
        min-width: 0;
    }

    @media (max-width: 768px) {
        #modalConsumivelBD .modal-dialog,
        #modalStockConsumivelBD .modal-dialog,
        #modalAssociarConsumivelBD .modal-dialog {
            width: calc(100vw - 16px);
            max-width: calc(100vw - 16px);
            margin: .5rem auto;
        }

        #modalConsumivelBD .modal-body,
        #modalStockConsumivelBD .modal-body,
        #modalAssociarConsumivelBD .modal-body {
            max-height: calc(100vh - 170px);
            padding: 1rem;
        }
    }
</style>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Gestão de Consumíveis</h2>
            <p class="subtitulo-pagina">
                Controle o catálogo de consumíveis, stock, preço unitário, localização e associação a equipamentos ou acessórios.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-adicionar"
            id="btnNovoConsumivelBD"
            data-bs-toggle="modal"
            data-bs-target="#modalConsumivelBD"
            data-codigo-preview="<?php echo h($proximoCodigoConsumivel); ?>">
            <i class="fa-solid fa-plus me-2"></i>
            Adicionar Consumível
        </button>
    </div>

    <?php if (!empty($erro_bd)): ?>
        <div class="alert alert-danger mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?php echo h($erro_bd); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert alert-success mb-4">
            <i class="fa-solid fa-check-circle me-2"></i>
            <?php echo h($mensagem_sucesso); ?>
        </div>
    <?php endif; ?>

    <section class="filtros-tabela" aria-label="Filtros de consumíveis">
        <div class="row g-3 align-items-end">
            <div class="col-lg-6 col-md-6">
                <label for="pesquisaConsumiveisBD" class="form-label">Pesquisar consumíveis</label>
                <input
                    type="search"
                    class="form-control"
                    id="pesquisaConsumiveisBD"
                    placeholder="Código, nome, categoria, localização, fornecedor ou estado">
            </div>

            <div class="col-lg-3 col-md-3">
                <label for="filtroEstadoConsumiveisBD" class="form-label">Estado</label>
                <select class="form-select" id="filtroEstadoConsumiveisBD">
                    <option value="">Todos</option>
                    <option value="disponível">Disponível</option>
                    <option value="stock baixo">Stock baixo</option>
                    <option value="sem stock">Sem stock</option>
                    <option value="descontinuado">Descontinuado</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-3">
                <label class="form-label">Visualização</label>
                <div class="d-flex gap-2">
                    <a class="btn btn-limpar-filtros flex-fill <?php echo $mostrar !== 'todos' ? 'active' : ''; ?>" href="consumiveis.php">
                        Ativos
                    </a>
                    <a class="btn btn-limpar-filtros flex-fill <?php echo $mostrar === 'todos' ? 'active' : ''; ?>" href="consumiveis.php?mostrar=todos">
                        Todos
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="tabela-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle tabela-equipamentos" id="tabelaConsumiveisBD">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Consumível</th>
                        <th>Categoria</th>
                        <th>Stock</th>
                        <th>Localização</th>
                        <th>Preço/un.</th>
                        <th>Valor stock</th>
                        <th>Fornecedor</th>
                        <th>Associações</th>
                        <th>Estado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($consumiveis)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted">
                                Não existem consumíveis registados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($consumiveis as $consumivel): ?>
                            <?php
                                $localizacaoTexto = $consumivel['codigo_localizacao'] . ' - Sala ' . $consumivel['sala'];
                                $associacoes = [];

                                if (!empty($consumivel['equipamentos_associados'])) {
                                    $associacoes[] = 'Eq.: ' . $consumivel['equipamentos_associados'];
                                }

                                if (!empty($consumivel['acessorios_associados'])) {
                                    $associacoes[] = 'Ac.: ' . $consumivel['acessorios_associados'];
                                }

                                $textoAssociacoes = !empty($associacoes) ? implode(' | ', $associacoes) : '---';
                            ?>

                            <tr data-estado-stock="<?php echo h(strtolower($consumivel['estado_stock'])); ?>">
                                <td><?php echo h($consumivel['codigo_consumivel']); ?></td>
                                <td>
                                    <strong><?php echo h($consumivel['nome']); ?></strong>
                                    <?php if (!empty($consumivel['referencia_fabricante'])): ?>
                                        <small class="d-block text-muted">Ref.: <?php echo h($consumivel['referencia_fabricante']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo h(texto_categoria_consumivel($consumivel['categoria'])); ?></td>
                                <td>
                                    <?php echo h(formatar_quantidade($consumivel['stock_atual'], $consumivel['unidade'])); ?>
                                    <small class="d-block text-muted">Mín.: <?php echo h(formatar_quantidade($consumivel['stock_minimo'], $consumivel['unidade'])); ?></small>
                                </td>
                                <td><?php echo h($localizacaoTexto); ?></td>
                                <td><?php echo h(formatar_moeda($consumivel['preco_unitario'])); ?></td>
                                <td><?php echo h(formatar_moeda($consumivel['valor_total_stock'])); ?></td>
                                <td><?php echo h($consumivel['fornecedor_preferencial_nome'] ?: '---'); ?></td>
                                <td>
                                    <small><?php echo h($textoAssociacoes); ?></small>
                                </td>
                                <td>
                                    <span class="estado <?php echo h(classe_estado_stock($consumivel['estado_stock'])); ?>">
                                        <?php echo h($consumivel['estado_stock']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-editar btn-editar-consumivel-bd"
                                        title="Editar consumível"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalConsumivelBD"
                                        data-id-consumivel="<?php echo h($consumivel['id_consumivel']); ?>"
                                        data-codigo="<?php echo h($consumivel['codigo_consumivel']); ?>"
                                        data-nome="<?php echo h($consumivel['nome']); ?>"
                                        data-categoria="<?php echo h($consumivel['categoria']); ?>"
                                        data-unidade="<?php echo h($consumivel['unidade']); ?>"
                                        data-stock-atual="<?php echo h($consumivel['stock_atual']); ?>"
                                        data-stock-minimo="<?php echo h($consumivel['stock_minimo']); ?>"
                                        data-stock-maximo="<?php echo h($consumivel['stock_maximo']); ?>"
                                        data-preco-unitario="<?php echo h($consumivel['preco_unitario']); ?>"
                                        data-id-localizacao="<?php echo h($consumivel['id_localizacao']); ?>"
                                        data-referencia-fabricante="<?php echo h($consumivel['referencia_fabricante']); ?>"
                                        data-id-fornecedor-preferencial="<?php echo h($consumivel['id_fornecedor_preferencial']); ?>"
                                        data-observacoes="<?php echo h($consumivel['observacoes']); ?>">
                                        <i class="fa-solid fa-file-pen"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-detalhes btn-stock-consumivel-bd"
                                        title="Movimentar stock"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalStockConsumivelBD"
                                        data-id-consumivel="<?php echo h($consumivel['id_consumivel']); ?>"
                                        data-codigo="<?php echo h($consumivel['codigo_consumivel']); ?>"
                                        data-nome="<?php echo h($consumivel['nome']); ?>"
                                        data-stock-atual="<?php echo h($consumivel['stock_atual']); ?>"
                                        data-unidade="<?php echo h($consumivel['unidade']); ?>">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-detalhes btn-associar-consumivel-bd"
                                        title="Associar a equipamento/acessório"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAssociarConsumivelBD"
                                        data-id-consumivel="<?php echo h($consumivel['id_consumivel']); ?>"
                                        data-codigo="<?php echo h($consumivel['codigo_consumivel']); ?>"
                                        data-nome="<?php echo h($consumivel['nome']); ?>">
                                        <i class="fa-solid fa-link"></i>
                                    </button>

                                    <?php if ((int) $consumivel['isActive'] === 1): ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-eliminar btn-descontinuar-consumivel-bd"
                                            title="Descontinuar consumível"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDescontinuarConsumivelBD"
                                            data-id-consumivel="<?php echo h($consumivel['id_consumivel']); ?>"
                                            data-codigo="<?php echo h($consumivel['codigo_consumivel']); ?>"
                                            data-nome="<?php echo h($consumivel['nome']); ?>">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    <?php else: ?>
                                        <form action="consumiveis.php?mostrar=todos" method="post" class="d-inline">
                                            <input type="hidden" name="acao" value="reativar_consumivel">
                                            <input type="hidden" name="id_consumivel" value="<?php echo h($consumivel['id_consumivel']); ?>">
                                            <button type="submit" class="btn btn-sm btn-detalhes" title="Reativar consumível">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL ADICIONAR / EDITAR CONSUMÍVEL -->
<div class="modal fade" id="modalConsumivelBD" tabindex="-1" aria-labelledby="modalConsumivelBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-acessorio-dialog">
        <div class="modal-content modal-acessorio">
            <form action="consumiveis.php" method="post" id="formConsumivelBD">
                <input type="hidden" name="acao" value="guardar_consumivel">
                <input type="hidden" name="id_consumivel" id="idConsumivelBD" value="">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalConsumivelBDLabel">
                            <i class="fa-solid fa-box me-2"></i>
                            Adicionar Consumível
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="codigoConsumivelBD" class="form-label">Código</label>
                            <input type="text" class="form-control campo-bloqueado" id="codigoConsumivelBD" name="codigoConsumivel" value="<?php echo h($proximoCodigoConsumivel); ?>" readonly>
                        </div>

                        <div class="col-md-9">
                            <label for="nomeConsumivelBD" class="form-label">Nome do consumível *</label>
                            <input type="text" class="form-control" id="nomeConsumivelBD" name="nomeConsumivel" placeholder="Ex: Elétrodos descartáveis ECG" required>
                        </div>

                        <div class="col-md-4">
                            <label for="categoriaConsumivelBD" class="form-label">Categoria *</label>
                            <select class="form-select" id="categoriaConsumivelBD" name="categoriaConsumivel" required>
                                <option value="">Selecionar</option>
                                <option value="eletrodos">Elétrodos</option>
                                <option value="papel_tecnico">Papel técnico</option>
                                <option value="filtros">Filtros</option>
                                <option value="circuitos_descartaveis">Circuitos descartáveis</option>
                                <option value="gel_contacto">Gel de contacto</option>
                                <option value="sensores_descartaveis">Sensores descartáveis</option>
                                <option value="reagente_calibracao">Reagente de calibração</option>
                                <option value="material_calibracao">Material de calibração</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="unidadeConsumivelBD" class="form-label">Unidade *</label>
                            <input type="text" class="form-control" id="unidadeConsumivelBD" name="unidadeConsumivel" value="unidades" required>
                        </div>

                        <div class="col-md-4">
                            <label for="precoUnitarioConsumivelBD" class="form-label">Preço por unidade (€)</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="precoUnitarioConsumivelBD" name="precoUnitarioConsumivel" placeholder="Ex: 0.18">
                        </div>

                        <div class="col-md-4 campo-stock-inicial-consumivel">
                            <label for="stockInicialConsumivelBD" class="form-label">Stock inicial</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="stockInicialConsumivelBD" name="stockInicialConsumivel" value="0">
                        </div>

                        <div class="col-md-4">
                            <label for="stockMinimoConsumivelBD" class="form-label">Stock mínimo *</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="stockMinimoConsumivelBD" name="stockMinimoConsumivel" value="0" required>
                        </div>

                        <div class="col-md-4">
                            <label for="stockMaximoConsumivelBD" class="form-label">Stock máximo</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="stockMaximoConsumivelBD" name="stockMaximoConsumivel">
                        </div>

                        <div class="col-md-6">
                            <label for="idLocalizacaoConsumivelBD" class="form-label">Localização do consumível *</label>
                            <select class="form-select" id="idLocalizacaoConsumivelBD" name="idLocalizacaoConsumivel" required>
                                <option value="">Selecionar localização</option>
                                <?php foreach ($localizacoes as $localizacao): ?>
                                    <option value="<?php echo h($localizacao['id_localizacao']); ?>">
                                        <?php echo h($localizacao['codigo'] . ' | ' . $localizacao['departamento_nome'] . ' - ' . $localizacao['edificio'] . ' - Piso ' . $localizacao['piso'] . ' - Sala ' . $localizacao['sala']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="idFornecedorPreferencialBD" class="form-label">Fornecedor preferencial</label>
                            <select class="form-select" id="idFornecedorPreferencialBD" name="idFornecedorPreferencial">
                                <option value="">Sem fornecedor preferencial</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?php echo h($fornecedor['id_fornecedor']); ?>">
                                        <?php echo h($fornecedor['nome_empresa'] . ' (' . $fornecedor['tipo_fornecedor'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="referenciaFabricanteConsumivelBD" class="form-label">Referência do fabricante</label>
                            <input type="text" class="form-control" id="referenciaFabricanteConsumivelBD" name="referenciaFabricanteConsumivel" placeholder="Ex: REF-ECG-001">
                        </div>

                        <div class="col-md-12">
                            <label for="observacoesConsumivelBD" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoesConsumivelBD" name="observacoesConsumivel" rows="3" placeholder="Notas sobre reposição, utilização ou validade"></textarea>
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
                        Guardar Consumível
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL MOVIMENTAR STOCK -->
<div class="modal fade" id="modalStockConsumivelBD" tabindex="-1" aria-labelledby="modalStockConsumivelBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-acessorio">
            <form action="consumiveis.php" method="post" id="formStockConsumivelBD">
                <input type="hidden" name="acao" value="movimentar_stock">
                <input type="hidden" name="id_consumivel" id="idConsumivelStockBD" value="">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalStockConsumivelBDLabel">
                            <i class="fa-solid fa-boxes-stacked me-2"></i>
                            Movimento de Stock
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Consumível</label>
                            <input type="text" class="form-control campo-bloqueado" id="nomeConsumivelStockBD" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Stock atual</label>
                            <input type="text" class="form-control campo-bloqueado" id="stockAtualConsumivelBD" readonly>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoMovimentoStockBD" class="form-label">Tipo de movimento *</label>
                            <select class="form-select" id="tipoMovimentoStockBD" name="tipoMovimentoStock" required>
                                <option value="entrada">Entrada</option>
                                <option value="saida">Saída</option>
                                <option value="consumo_calibracao">Consumo em calibração</option>
                                <option value="devolucao">Devolução</option>
                                <option value="ajuste">Ajuste manual</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="quantidadeMovimentoStockBD" class="form-label">Quantidade *</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="quantidadeMovimentoStockBD" name="quantidadeMovimentoStock" required>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoAlvoMovimentoStockBD" class="form-label">Associar movimento a</label>
                            <select class="form-select" id="tipoAlvoMovimentoStockBD">
                                <option value="">Nenhum</option>
                                <option value="equipamento">Equipamento</option>
                                <option value="acessorio">Acessório</option>
                            </select>
                        </div>

                        <div class="col-md-6 bloco-movimento-equipamento d-none">
                            <label for="idEquipamentoMovimentoStockBD" class="form-label">Equipamento</label>
                            <select class="form-select" id="idEquipamentoMovimentoStockBD" name="idEquipamentoMovimentoStock">
                                <option value="">Selecionar equipamento</option>
                                <?php foreach ($equipamentos as $equipamento): ?>
                                    <option value="<?php echo h($equipamento['id_equipamento']); ?>">
                                        <?php echo h($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 bloco-movimento-acessorio d-none">
                            <label for="idAcessorioMovimentoStockBD" class="form-label">Acessório</label>
                            <select class="form-select" id="idAcessorioMovimentoStockBD" name="idAcessorioMovimentoStock">
                                <option value="">Selecionar acessório</option>
                                <?php foreach ($acessorios as $acessorio): ?>
                                    <option value="<?php echo h($acessorio['id_acessorio']); ?>">
                                        <?php echo h($acessorio['codigo_acessorio'] . ' - ' . $acessorio['acessorio_nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="motivoMovimentoStockBD" class="form-label">Motivo</label>
                            <input type="text" class="form-control" id="motivoMovimentoStockBD" name="motivoMovimentoStock" placeholder="Ex: Reposição, calibração, ajuste de inventário">
                        </div>

                        <div class="col-md-12">
                            <label for="observacoesMovimentoStockBD" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoesMovimentoStockBD" name="observacoesMovimentoStock" rows="3"></textarea>
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
                        Registar Movimento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ASSOCIAR CONSUMÍVEL -->
<div class="modal fade" id="modalAssociarConsumivelBD" tabindex="-1" aria-labelledby="modalAssociarConsumivelBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-acessorio">
            <form action="consumiveis.php" method="post" id="formAssociarConsumivelBD">
                <input type="hidden" name="acao" value="associar_consumivel">
                <input type="hidden" name="id_consumivel" id="idConsumivelAssociacaoBD" value="">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalAssociarConsumivelBDLabel">
                            <i class="fa-solid fa-link me-2"></i>
                            Associar Consumível
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Consumível</label>
                            <input type="text" class="form-control campo-bloqueado" id="nomeConsumivelAssociacaoBD" readonly>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoAssociacaoConsumivelBD" class="form-label">Associar a *</label>
                            <select class="form-select" id="tipoAssociacaoConsumivelBD" name="tipoAssociacaoConsumivel" required>
                                <option value="equipamento">Equipamento</option>
                                <option value="acessorio">Acessório</option>
                            </select>
                        </div>

                        <div class="col-md-8 bloco-associacao-equipamento">
                            <label for="idEquipamentoAssociacaoBD" class="form-label">Equipamento</label>
                            <select class="form-select" id="idEquipamentoAssociacaoBD" name="idEquipamentoAssociacao">
                                <option value="">Selecionar equipamento</option>
                                <?php foreach ($equipamentos as $equipamento): ?>
                                    <option value="<?php echo h($equipamento['id_equipamento']); ?>">
                                        <?php echo h($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8 bloco-associacao-acessorio d-none">
                            <label for="idAcessorioAssociacaoBD" class="form-label">Acessório</label>
                            <select class="form-select" id="idAcessorioAssociacaoBD" name="idAcessorioAssociacao">
                                <option value="">Selecionar acessório</option>
                                <?php foreach ($acessorios as $acessorio): ?>
                                    <option value="<?php echo h($acessorio['id_acessorio']); ?>">
                                        <?php echo h($acessorio['codigo_acessorio'] . ' - ' . $acessorio['acessorio_nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="quantidadePrevistaAssociacaoBD" class="form-label">Quantidade prevista</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="quantidadePrevistaAssociacaoBD" name="quantidadePrevistaAssociacao">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block">Necessário para utilização?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="necessarioUtilizacao" id="necessarioUtilizacaoSimBD" value="1" checked>
                                <label class="form-check-label" for="necessarioUtilizacaoSimBD">Sim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="necessarioUtilizacao" id="necessarioUtilizacaoNaoBD" value="0">
                                <label class="form-check-label" for="necessarioUtilizacaoNaoBD">Não</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block">Necessário para calibração?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="necessarioCalibracao" id="necessarioCalibracaoSimBD" value="1">
                                <label class="form-check-label" for="necessarioCalibracaoSimBD">Sim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="necessarioCalibracao" id="necessarioCalibracaoNaoBD" value="0" checked>
                                <label class="form-check-label" for="necessarioCalibracaoNaoBD">Não</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="observacoesAssociacaoBD" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoesAssociacaoBD" name="observacoesAssociacao" rows="3"></textarea>
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
                        Guardar Associação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DESCONTINUAR CONSUMÍVEL -->
<div class="modal fade" id="modalDescontinuarConsumivelBD" tabindex="-1" aria-labelledby="modalDescontinuarConsumivelBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-remocao-dialog">
        <div class="modal-content modal-apagar-equipamento">
            <div class="modal-header modal-remocao-header">
                <div>
                    <h5 class="modal-title" id="modalDescontinuarConsumivelBDLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Descontinuar consumível
                    </h5>
                    <p class="modal-remocao-subtitulo">O registo fica guardado, mas deixa de aparecer na lista de ativos.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body modal-remocao-body">
                <div class="modal-resumo-remocao">
                    <div class="modal-linha">
                        <strong>Código</strong>
                        <span id="codigoDescontinuarConsumivelBD">---</span>
                    </div>
                    <div class="modal-linha">
                        <strong>Consumível</strong>
                        <span id="nomeDescontinuarConsumivelBD">---</span>
                    </div>
                </div>

                <p class="texto-confirmacao-remocao">
                    Confirma que pretende descontinuar este consumível?
                </p>
            </div>

            <div class="modal-footer modal-remocao-footer">
                <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i>
                    Cancelar
                </button>

                <form action="consumiveis.php" method="post">
                    <input type="hidden" name="acao" value="descontinuar_consumivel">
                    <input type="hidden" name="id_consumivel" id="idDescontinuarConsumivelBD" value="">

                    <button type="submit" class="btn btn-confirmar-remocao">
                        <i class="fa-solid fa-ban me-2"></i>
                        Descontinuar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = function (id) {
        return document.getElementById(id);
    };

    function setValue(id, valor) {
        const campo = $(id);
        if (campo) campo.value = valor ?? '';
    }

    function setText(id, valor) {
        const campo = $(id);
        if (campo) campo.textContent = valor || '---';
    }

    function normalizarTexto(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    const pesquisa = $('pesquisaConsumiveisBD');
    const filtroEstado = $('filtroEstadoConsumiveisBD');
    const tabela = $('tabelaConsumiveisBD');

    function filtrarConsumiveis() {
        if (!tabela) return;

        const termo = normalizarTexto(pesquisa?.value || '');
        const estado = normalizarTexto(filtroEstado?.value || '');

        tabela.querySelectorAll('tbody tr').forEach(function (linha) {
            const textoLinha = normalizarTexto(linha.textContent);
            const estadoLinha = normalizarTexto(linha.dataset.estadoStock || '');
            const passaPesquisa = !termo || textoLinha.includes(termo);
            const passaEstado = !estado || estadoLinha === estado;

            linha.classList.toggle('d-none', !(passaPesquisa && passaEstado));
        });
    }

    if (pesquisa) pesquisa.addEventListener('input', filtrarConsumiveis);
    if (filtroEstado) filtroEstado.addEventListener('change', filtrarConsumiveis);

    const modalConsumivel = $('modalConsumivelBD');
    const formConsumivel = $('formConsumivelBD');
    const tituloConsumivel = $('modalConsumivelBDLabel');
    const stockInicialWrapper = document.querySelector('.campo-stock-inicial-consumivel');

    if (modalConsumivel) {
        modalConsumivel.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            formConsumivel?.reset();

            if (botao && botao.classList.contains('btn-editar-consumivel-bd')) {
                setValue('idConsumivelBD', botao.dataset.idConsumivel || '');
                setValue('codigoConsumivelBD', botao.dataset.codigo || '');
                setValue('nomeConsumivelBD', botao.dataset.nome || '');
                setValue('categoriaConsumivelBD', botao.dataset.categoria || '');
                setValue('unidadeConsumivelBD', botao.dataset.unidade || 'unidades');
                setValue('stockInicialConsumivelBD', botao.dataset.stockAtual || '0');
                setValue('stockMinimoConsumivelBD', botao.dataset.stockMinimo || '0');
                setValue('stockMaximoConsumivelBD', botao.dataset.stockMaximo || '');
                setValue('precoUnitarioConsumivelBD', botao.dataset.precoUnitario || '');
                setValue('idLocalizacaoConsumivelBD', botao.dataset.idLocalizacao || '');
                setValue('referenciaFabricanteConsumivelBD', botao.dataset.referenciaFabricante || '');
                setValue('idFornecedorPreferencialBD', botao.dataset.idFornecedorPreferencial || '');
                setValue('observacoesConsumivelBD', botao.dataset.observacoes || '');

                const campoStockInicial = $('stockInicialConsumivelBD');
                if (campoStockInicial) campoStockInicial.readOnly = true;
                if (stockInicialWrapper) stockInicialWrapper.classList.add('d-none');

                if (tituloConsumivel) {
                    tituloConsumivel.innerHTML = '<i class="fa-solid fa-file-pen me-2"></i>Editar Consumível';
                }
            } else {
                setValue('idConsumivelBD', '');
                setValue('codigoConsumivelBD', botao?.dataset.codigoPreview || '<?php echo h($proximoCodigoConsumivel); ?>');
                setValue('unidadeConsumivelBD', 'unidades');
                setValue('stockInicialConsumivelBD', '0');
                setValue('stockMinimoConsumivelBD', '0');

                const campoStockInicial = $('stockInicialConsumivelBD');
                if (campoStockInicial) campoStockInicial.readOnly = false;
                if (stockInicialWrapper) stockInicialWrapper.classList.remove('d-none');

                if (tituloConsumivel) {
                    tituloConsumivel.innerHTML = '<i class="fa-solid fa-box me-2"></i>Adicionar Consumível';
                }
            }
        });
    }

    const modalStock = $('modalStockConsumivelBD');
    if (modalStock) {
        modalStock.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            if (!botao) return;

            setValue('idConsumivelStockBD', botao.dataset.idConsumivel || '');
            setValue('nomeConsumivelStockBD', (botao.dataset.codigo || '') + ' - ' + (botao.dataset.nome || ''));
            setValue('stockAtualConsumivelBD', (botao.dataset.stockAtual || '0') + ' ' + (botao.dataset.unidade || ''));
            setValue('quantidadeMovimentoStockBD', '');
            setValue('motivoMovimentoStockBD', '');
            setValue('observacoesMovimentoStockBD', '');
            setValue('tipoAlvoMovimentoStockBD', '');
            atualizarAlvoMovimento();
        });
    }

    function atualizarAlvoMovimento() {
        const tipo = $('tipoAlvoMovimentoStockBD')?.value || '';
        document.querySelectorAll('.bloco-movimento-equipamento').forEach(el => el.classList.toggle('d-none', tipo !== 'equipamento'));
        document.querySelectorAll('.bloco-movimento-acessorio').forEach(el => el.classList.toggle('d-none', tipo !== 'acessorio'));

        if (tipo !== 'equipamento') setValue('idEquipamentoMovimentoStockBD', '');
        if (tipo !== 'acessorio') setValue('idAcessorioMovimentoStockBD', '');
    }

    $('tipoAlvoMovimentoStockBD')?.addEventListener('change', atualizarAlvoMovimento);

    const modalAssociar = $('modalAssociarConsumivelBD');
    if (modalAssociar) {
        modalAssociar.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            if (!botao) return;

            setValue('idConsumivelAssociacaoBD', botao.dataset.idConsumivel || '');
            setValue('nomeConsumivelAssociacaoBD', (botao.dataset.codigo || '') + ' - ' + (botao.dataset.nome || ''));
            setValue('tipoAssociacaoConsumivelBD', 'equipamento');
            setValue('idEquipamentoAssociacaoBD', '');
            setValue('idAcessorioAssociacaoBD', '');
            setValue('quantidadePrevistaAssociacaoBD', '');
            setValue('observacoesAssociacaoBD', '');
            atualizarTipoAssociacao();
        });
    }

    function atualizarTipoAssociacao() {
        const tipo = $('tipoAssociacaoConsumivelBD')?.value || 'equipamento';
        document.querySelectorAll('.bloco-associacao-equipamento').forEach(el => el.classList.toggle('d-none', tipo !== 'equipamento'));
        document.querySelectorAll('.bloco-associacao-acessorio').forEach(el => el.classList.toggle('d-none', tipo !== 'acessorio'));

        if (tipo !== 'equipamento') setValue('idEquipamentoAssociacaoBD', '');
        if (tipo !== 'acessorio') setValue('idAcessorioAssociacaoBD', '');
    }

    $('tipoAssociacaoConsumivelBD')?.addEventListener('change', atualizarTipoAssociacao);

    const modalDescontinuar = $('modalDescontinuarConsumivelBD');
    if (modalDescontinuar) {
        modalDescontinuar.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            if (!botao) return;

            setValue('idDescontinuarConsumivelBD', botao.dataset.idConsumivel || '');
            setText('codigoDescontinuarConsumivelBD', botao.dataset.codigo || '---');
            setText('nomeDescontinuarConsumivelBD', botao.dataset.nome || '---');
        });
    }
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
