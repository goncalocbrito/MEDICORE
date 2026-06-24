<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   CONSUMÍVEIS — por equipamento
   ========================================================= */

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function selected_option($valorAtual, $valorOpcao)
{
    return ((string) $valorAtual === (string) $valorOpcao) ? 'selected' : '';
}

function valor_ou_null($valor)
{
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : $valor;
}

function decimal_ou_null($valor)
{
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : (float) str_replace(',', '.', $valor);
}

function decimal_obrigatorio($valor, $padrao = 0)
{
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? (float) $padrao : (float) str_replace(',', '.', $valor);
}

function formatar_moeda($valor)
{
    if ($valor === null || $valor === '') return '---';
    return number_format((float) $valor, 2, ',', '.') . ' €';
}

function texto_categoria_consumivel($categoria)
{
    $categorias = [
        'eletrodos'              => 'Elétrodos',
        'papel_tecnico'          => 'Papel técnico',
        'filtros'                => 'Filtros',
        'circuitos_descartaveis' => 'Circuitos descartáveis',
        'gel_contacto'           => 'Gel de contacto',
        'sensores_descartaveis'  => 'Sensores descartáveis',
        'reagente_calibracao'    => 'Reagente de calibração',
        'material_calibracao'    => 'Material de calibração',
        'outro'                  => 'Outro',
    ];
    return $categorias[$categoria] ?? $categoria;
}

function estado_stock($consumivel)
{
    if (!(int) $consumivel['isActive']) return 'Descontinuado';
    $atual  = (float) $consumivel['stock_atual'];
    $minimo = (float) ($consumivel['stock_minimo'] ?? 0);
    if ($atual == 0)           return 'Sem stock';
    if ($atual <= $minimo)     return 'Stock baixo';
    return 'Disponível';
}

function classe_estado_stock($estado)
{
    switch ($estado) {
        case 'Disponível':     return 'estado-ativo';
        case 'Stock baixo':    return 'estado-manutencao';
        case 'Sem stock':      return 'estado-avariado';
        case 'Descontinuado':  return 'estado-inativo';
        default:               return 'estado-inativo';
    }
}

function gerar_codigo_consumivel(PDO $pdo)
{
    $stmt = $pdo->query("
        SELECT CONCAT('CON-', LPAD(COALESCE(MAX(CAST(SUBSTRING(codigo_consumivel, 5) AS UNSIGNED)), 0) + 1, 3, '0'))
        FROM consumiveis
        WHERE codigo_consumivel LIKE 'CON-%'
    ");
    return $stmt->fetchColumn() ?: 'CON-001';
}

$isEngenheiro = ($_SESSION['tipo_utilizador'] ?? '') === 'Engenheiro';

$pdo              = null;
$erro_bd          = '';
$erro_modal       = '';
$mensagem_sucesso = '';
$equipamentos     = [];
$fornecedores     = [];
$consumiveis      = [];
$equipamentoSelecionado    = null;
$idEquipamentoSelecionado  = 0;
$dadosModalErro   = [];
$proximoCodigoConsumivel   = 'CON-001';

try {
    $pdo = new PDO(
        'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    /* =========================================================
       AÇÕES POST
       ========================================================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao              = $_POST['acao'] ?? '';
        $idEquipamentoPost = (int) ($_POST['id_equipamento'] ?? 0);
        $utilizadorAtual   = $_SESSION['nome'] ?? $_SESSION['username'] ?? 'admin';

        if ($acao === 'criar' || $acao === 'editar') {
            $dadosModalErro = $_POST;
        }

        if ($acao === 'criar') {
            if ($idEquipamentoPost <= 0) {
                $erro_modal = 'Selecione um equipamento válido.';
            }

            $nome      = valor_ou_null($_POST['nomeConsumivel'] ?? null);
            $categoria = $_POST['categoriaConsumivel'] ?? '';
            $idFornecedor = (int) ($_POST['idFornecedorConsumivel'] ?? 0);

            $errosValidacao = [];
            if (empty($nome))       $errosValidacao[] = 'Nome do consumível é obrigatório.';
            if (empty($categoria))  $errosValidacao[] = 'Categoria é obrigatória.';
            if ($idFornecedor <= 0) $errosValidacao[] = 'Fornecedor é obrigatório.';

            $stockInicial = decimal_obrigatorio($_POST['stockInicialConsumivel'] ?? 0);
            if ($stockInicial <= 0) $errosValidacao[] = 'O stock inicial deve ser maior que 0.';

            if (!empty($errosValidacao)) {
                $erro_modal = implode('|', $errosValidacao);
            }

            if (empty($erro_modal)) {
            $idFornecedor = $idFornecedor > 0 ? $idFornecedor : null;

            $stockInicial  = decimal_obrigatorio($_POST['stockInicialConsumivel'] ?? 0);
            $stockMinimo   = !$isEngenheiro ? decimal_obrigatorio($_POST['stockMinimoConsumivel'] ?? 0) : 0;
            $stockMaximo   = !$isEngenheiro ? decimal_ou_null($_POST['stockMaximoConsumivel'] ?? null) : null;
            $precoUnitario = !$isEngenheiro ? decimal_ou_null($_POST['precoUnitarioConsumivel'] ?? null) : null;

            $codigo = gerar_codigo_consumivel($pdo);

            $pdo->beginTransaction();

            $stmtInserir = $pdo->prepare("
                INSERT INTO consumiveis (
                    id_equipamento,
                    codigo_consumivel,
                    nome,
                    categoria,
                    stock_atual,
                    stock_minimo,
                    stock_maximo,
                    preco_unitario,
                    id_fornecedor_preferencial,
                    observacoes,
                    atualizado_por
                ) VALUES (
                    :id_equipamento,
                    :codigo_consumivel,
                    :nome,
                    :categoria,
                    :stock_atual,
                    :stock_minimo,
                    :stock_maximo,
                    :preco_unitario,
                    :id_fornecedor_preferencial,
                    :observacoes,
                    :atualizado_por
                )
            ");

            $stmtInserir->execute([
                ':id_equipamento'          => $idEquipamentoPost,
                ':codigo_consumivel'       => $codigo,
                ':nome'                    => $nome,
                ':categoria'               => $categoria,
                ':stock_atual'             => $stockInicial,
                ':stock_minimo'            => $stockMinimo,
                ':stock_maximo'            => $stockMaximo,
                ':preco_unitario'          => $precoUnitario,
                ':id_fornecedor_preferencial' => $idFornecedor,
                ':observacoes'             => valor_ou_null($_POST['observacoesConsumivel'] ?? null),
                ':atualizado_por'          => $utilizadorAtual,
            ]);

            $idNovo = (int) $pdo->lastInsertId();

            if ($stockInicial > 0) {
                $pdo->prepare("
                    INSERT INTO movimentos_stock_consumiveis
                        (id_consumivel, tipo_movimento, quantidade, stock_anterior, stock_posterior, motivo, observacoes, atualizado_por)
                    VALUES
                        (:id, 'entrada', :qty, 0, :qty, 'Stock inicial', 'Registo inicial do consumível', :user)
                ")->execute([':id' => $idNovo, ':qty' => $stockInicial, ':user' => $utilizadorAtual]);
            }

            $pdo->commit();
            header('Location: consumiveis.php?ref_equipamento=' . url_ref($idEquipamentoPost) . '&criado=1');
            exit;
            } // fim if empty erro_modal criar
        }

        if ($acao === 'editar') {
            $idConsumivel = (int) ($_POST['id_consumivel'] ?? 0);

            $nome      = valor_ou_null($_POST['nomeConsumivel'] ?? null);
            $categoria = $_POST['categoriaConsumivel'] ?? '';
            $idFornecedor = (int) ($_POST['idFornecedorConsumivel'] ?? 0);

            $errosValidacao = [];
            if ($idConsumivel <= 0)  $errosValidacao[] = 'Consumível inválido.';
            if (empty($nome))        $errosValidacao[] = 'Nome do consumível é obrigatório.';
            if (empty($categoria))   $errosValidacao[] = 'Categoria é obrigatória.';
            if ($idFornecedor <= 0)  $errosValidacao[] = 'Fornecedor é obrigatório.';

            if (!empty($errosValidacao)) {
                $erro_modal = implode('|', $errosValidacao);
            }

            if (empty($erro_modal)) {
            $idFornecedor = $idFornecedor > 0 ? $idFornecedor : null;

            $stockMinimo   = !$isEngenheiro ? decimal_obrigatorio($_POST['stockMinimoConsumivel'] ?? 0) : null;
            $stockMaximo   = !$isEngenheiro ? decimal_ou_null($_POST['stockMaximoConsumivel'] ?? null) : null;
            $precoUnitario = !$isEngenheiro ? decimal_ou_null($_POST['precoUnitarioConsumivel'] ?? null) : null;

            $setCusto = !$isEngenheiro
                ? ', stock_minimo = :stock_minimo, stock_maximo = :stock_maximo, preco_unitario = :preco_unitario'
                : '';

            $stmt = $pdo->prepare("
                UPDATE consumiveis SET
                    nome = :nome,
                    categoria = :categoria,
                    id_fornecedor_preferencial = :id_fornecedor,
                    observacoes = :observacoes,
                    atualizado_por = :atualizado_por
                    $setCusto
                WHERE id_consumivel = :id_consumivel
            ");

            $params = [
                ':id_consumivel'  => $idConsumivel,
                ':nome'           => $nome,
                ':categoria'      => $categoria,
                ':id_fornecedor'  => $idFornecedor,
                ':observacoes'    => valor_ou_null($_POST['observacoesConsumivel'] ?? null),
                ':atualizado_por' => $utilizadorAtual,
            ];

            if (!$isEngenheiro) {
                $params[':stock_minimo']   = $stockMinimo;
                $params[':stock_maximo']   = $stockMaximo;
                $params[':preco_unitario'] = $precoUnitario;
            }

            $stmt->execute($params);

            header('Location: consumiveis.php?ref_equipamento=' . url_ref($idEquipamentoPost) . '&editado=1');
            exit;
            } // fim if empty erro_modal editar
        }

        if ($acao === 'movimentar') {
            $idConsumivel   = (int) ($_POST['id_consumivel'] ?? 0);
            $tipoMovimento  = $_POST['tipoMovimento'] ?? '';
            $quantidade     = (int) ($_POST['quantidadeMovimento'] ?? 0);
            $motivo         = valor_ou_null($_POST['motivoMovimento'] ?? null) ?: 'Ajuste manual';

            $errosMovimento = [];
            if ($idConsumivel <= 0)                          $errosMovimento[] = 'Consumível inválido.';
            if (!in_array($tipoMovimento, ['entrada', 'saida'])) $errosMovimento[] = 'Tipo de movimento inválido.';
            if ($quantidade <= 0)                            $errosMovimento[] = 'A quantidade deve ser maior que 0.';

            if (empty($errosMovimento)) {
                $stmtStock = $pdo->prepare("SELECT stock_atual FROM consumiveis WHERE id_consumivel = :id AND isActive = 1");
                $stmtStock->execute([':id' => $idConsumivel]);
                $stockAtual = (int) $stmtStock->fetchColumn();

                if ($tipoMovimento === 'saida' && $quantidade > $stockAtual) {
                    $errosMovimento[] = 'Quantidade de saída superior ao stock disponível (' . $stockAtual . ').';
                }
            }

            if (empty($errosMovimento)) {
                $stmtStock = $pdo->prepare("SELECT stock_atual FROM consumiveis WHERE id_consumivel = :id AND isActive = 1");
                $stmtStock->execute([':id' => $idConsumivel]);
                $stockAnterior = (int) $stmtStock->fetchColumn();
                $stockPosterior = $tipoMovimento === 'entrada'
                    ? $stockAnterior + $quantidade
                    : $stockAnterior - $quantidade;

                $pdo->beginTransaction();
                $pdo->prepare("UPDATE consumiveis SET stock_atual = :s, atualizado_por = :u WHERE id_consumivel = :id")
                    ->execute([':s' => $stockPosterior, ':u' => $utilizadorAtual, ':id' => $idConsumivel]);
                $pdo->prepare("
                    INSERT INTO movimentos_stock_consumiveis
                        (id_consumivel, tipo_movimento, quantidade, stock_anterior, stock_posterior, motivo, atualizado_por)
                    VALUES (:id, :tipo, :qty, :antes, :depois, :motivo, :user)
                ")->execute([
                    ':id'     => $idConsumivel,
                    ':tipo'   => $tipoMovimento,
                    ':qty'    => $quantidade,
                    ':antes'  => $stockAnterior,
                    ':depois' => $stockPosterior,
                    ':motivo' => $motivo,
                    ':user'   => $utilizadorAtual,
                ]);
                $pdo->commit();
                header('Location: consumiveis.php?ref_equipamento=' . url_ref($idEquipamentoPost) . '&stock=1');
                exit;
            }

            $erro_modal    = implode('|', $errosMovimento);
            $dadosModalErro = $_POST;
            $dadosModalErro['acao'] = 'movimentar';
        }

        if ($acao === 'apagar') {
            $idConsumivel = (int) ($_POST['id_consumivel'] ?? 0);
            if ($idConsumivel > 0) {
                $pdo->prepare("UPDATE consumiveis SET isActive = 0, atualizado_por = :u WHERE id_consumivel = :id")
                    ->execute([':u' => $utilizadorAtual, ':id' => $idConsumivel]);
            }
            header('Location: consumiveis.php?ref_equipamento=' . url_ref($idEquipamentoPost) . '&removido=1');
            exit;
        }
    }

    /* =========================================================
       CARREGAMENTO DE DADOS
       ========================================================= */
    $equipamentos = $pdo->query("
        SELECT id_equipamento, codigo_equipamento, designacao
        FROM equipamentos
        WHERE isActive = 1
        ORDER BY codigo_equipamento ASC
    ")->fetchAll();

    $idEquipamentoSelecionado = id_from_request('id_equipamento', 'ref_equipamento');

    if ($idEquipamentoSelecionado <= 0 && !empty($equipamentos)) {
        $idEquipamentoSelecionado = (int) $equipamentos[0]['id_equipamento'];
    }

    foreach ($equipamentos as $eq) {
        if ((int) $eq['id_equipamento'] === $idEquipamentoSelecionado) {
            $equipamentoSelecionado = $eq;
            break;
        }
    }

    $fornecedores = $pdo->query("
        SELECT id_fornecedor, nome_empresa, tipo_fornecedor
        FROM fornecedores
        WHERE isActive = 1
        ORDER BY nome_empresa ASC
    ")->fetchAll();

    if ($idEquipamentoSelecionado > 0) {
        $stmt = $pdo->prepare("
            SELECT
                c.*,
                f.nome_empresa AS fornecedor_nome,
                CASE
                    WHEN c.isActive = 0             THEN 'Descontinuado'
                    WHEN c.stock_atual = 0           THEN 'Sem stock'
                    WHEN c.stock_atual <= c.stock_minimo THEN 'Stock baixo'
                    ELSE 'Disponível'
                END AS estado_stock
            FROM consumiveis c
            LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor_preferencial
            WHERE c.id_equipamento = :id_equipamento
              AND c.isActive = 1
            ORDER BY c.codigo_consumivel ASC
        ");
        $stmt->execute([':id_equipamento' => $idEquipamentoSelecionado]);
        $consumiveis = $stmt->fetchAll();

        $proximoCodigoConsumivel = gerar_codigo_consumivel($pdo);
    }

} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    $erro_bd = $e->getMessage();
}

if (isset($_GET['criado']))       $mensagem_sucesso = 'Consumível registado com sucesso.';
elseif (isset($_GET['editado']))  $mensagem_sucesso = 'Consumível atualizado com sucesso.';
elseif (isset($_GET['removido'])) $mensagem_sucesso = 'Consumível removido com sucesso.';
elseif (isset($_GET['stock']))    $mensagem_sucesso = 'Stock atualizado com sucesso.';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<style>
    #modalConsumivelBD .modal-dialog {
        width: min(1100px, calc(100vw - 32px));
        max-width: min(1100px, calc(100vw - 32px));
        margin: 1rem auto;
    }
    #modalConsumivelBD .modal-content {
        border: 0; border-radius: 18px;
        max-height: calc(100vh - 32px); overflow: hidden;
    }
    #modalConsumivelBD .modal-content > form {
        display: flex; flex-direction: column;
        max-height: calc(100vh - 32px); min-height: 0;
    }
    #modalConsumivelBD .modal-header, #modalConsumivelBD .modal-footer { flex: 0 0 auto; }
    #modalConsumivelBD .modal-body {
        flex: 1 1 auto; min-height: 0;
        max-height: calc(100vh - 190px);
        overflow-y: auto; overflow-x: hidden; padding: 1.5rem;
    }
    #modalConsumivelBD .modal-body .row { row-gap: 1rem; }
    #modalConsumivelBD input, #modalConsumivelBD select, #modalConsumivelBD textarea { min-width: 0; }
    @media (max-width: 768px) {
        #modalConsumivelBD .modal-dialog { width: calc(100vw - 16px); max-width: calc(100vw - 16px); margin: .5rem auto; }
        #modalConsumivelBD .modal-body { max-height: calc(100vh - 170px); padding: 1rem; }
    }
    .btn-stock-consumivel {
        background: #0d6efd1a;
        color: #0d6efd;
        border: 1px solid #0d6efd40;
        border-radius: 8px;
        padding: 5px 9px;
        transition: background .2s;
    }
    .btn-stock-consumivel:hover { background: #0d6efd30; color: #0a58ca; }
    .tipo-movimento-btn { cursor: pointer; }
    .tipo-movimento-btn input[type="radio"]:checked + .btn-tipo-movimento { border-color: var(--cor-principal); background: var(--cor-principal); color: #fff; }
    .btn-tipo-movimento { transition: all .2s; }
</style>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Gestão de Consumíveis</h2>
            <p class="subtitulo-pagina">Controle o catálogo de consumíveis por equipamento.</p>
        </div>
    </div>

    <?php if (!empty($erro_bd) && empty($erro_modal)): ?>
        <div class="alert alert-danger mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo h($erro_bd); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert alert-success mb-4">
            <i class="fa-solid fa-check-circle me-2"></i><?php echo h($mensagem_sucesso); ?>
        </div>
    <?php endif; ?>

    <section class="filtros-tabela" aria-label="Seleção de equipamento">
        <div class="row g-3 align-items-end">
            <div class="col-lg-12">
                <label class="form-label">Equipamento</label>
                <div class="seletor-equipamento-pesquisa position-relative" id="seletorEquipamentoConsumiveis">
                    <div class="input-group">
                        <span class="input-group-text" style="background:#fff;border-right:0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" class="form-control seletor-eq-input" style="border-left:0"
                            placeholder="Pesquisar equipamento pelo nome ou código…"
                            value="<?php echo h($equipamentoSelecionado ? $equipamentoSelecionado['codigo_equipamento'] . ' - ' . $equipamentoSelecionado['designacao'] : ''); ?>"
                            autocomplete="off">
                    </div>
                    <input type="hidden" class="seletor-eq-hidden">
                    <div class="seletor-eq-dropdown d-none position-absolute w-100 bg-white border rounded shadow-sm" style="z-index:500;max-height:260px;overflow-y:auto;top:calc(100% + 4px)">
                        <?php if (empty($equipamentos)): ?>
                            <div class="px-3 py-2 text-muted">Sem equipamentos registados</div>
                        <?php else: ?>
                            <?php foreach ($equipamentos as $eq):
                                $label = h($eq['codigo_equipamento'] . ' - ' . $eq['designacao']);
                                $ref   = url_ref($eq['id_equipamento']);
                                $url   = 'consumiveis.php?ref_equipamento=' . $ref;
                                $ativo = (int) $eq['id_equipamento'] === $idEquipamentoSelecionado;
                            ?>
                                <div class="seletor-eq-item px-3 py-2"
                                    style="cursor:pointer<?php echo $ativo ? ';background:#f0faf7;font-weight:600' : ''; ?>"
                                    data-label="<?php echo $label; ?>"
                                    data-ref="<?php echo h($ref); ?>"
                                    data-url="<?php echo h($url); ?>"
                                    onmouseover="this.style.background='#f8f9fa'"
                                    onmouseout="this.style.background='<?php echo $ativo ? '#f0faf7' : ''; ?>'">
                                    <?php echo $label; ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="seletor-eq-vazio px-3 py-2 text-muted d-none">Nenhum resultado encontrado.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="tabela-container">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <div>
                <h5 class="subtitulo-bloco-form mb-1">Consumíveis associados</h5>
                <?php if ($equipamentoSelecionado): ?>
                    <small class="texto-ajuda-form">
                        Equipamento selecionado:
                        <strong><?php echo h($equipamentoSelecionado['codigo_equipamento'] . ' - ' . $equipamentoSelecionado['designacao']); ?></strong>
                    </small>
                <?php endif; ?>
            </div>

            <button
                type="button"
                class="btn btn-adicionar"
                id="btnAbrirModalNovoConsumivel"
                data-bs-toggle="modal"
                data-bs-target="#modalConsumivelBD"
                <?php echo empty($equipamentoSelecionado) ? 'disabled' : ''; ?>>
                <i class="fa-solid fa-plus me-2"></i>
                Adicionar Consumível
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle tabela-equipamentos tabela-datatables-medicore" id="tabelaConsumiveis">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Consumível</th>
                        <th>Categoria</th>
                        <th>Stock</th>
                        <?php if (!$isEngenheiro): ?>
                            <th>Preço/un.</th>
                        <?php endif; ?>
                        <th>Fornecedor</th>
                        <th>Estado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consumiveis as $c): ?>
                        <tr>
                            <td><strong><?php echo h($c['codigo_consumivel']); ?></strong></td>
                            <td><?php echo h($c['nome']); ?></td>
                            <td><?php echo h(texto_categoria_consumivel($c['categoria'])); ?></td>
                            <td>
                                <?php echo h((int) $c['stock_atual']); ?>
                                <?php if (!$isEngenheiro && $c['stock_minimo'] > 0): ?>
                                    <small class="d-block text-muted">Mín.: <?php echo h((int) $c['stock_minimo']); ?></small>
                                <?php endif; ?>
                            </td>
                            <?php if (!$isEngenheiro): ?>
                                <td><?php echo h(formatar_moeda($c['preco_unitario'])); ?></td>
                            <?php endif; ?>
                            <td><?php echo h($c['fornecedor_nome'] ?: '---'); ?></td>
                            <td>
                                <span class="estado <?php echo h(classe_estado_stock($c['estado_stock'])); ?>">
                                    <?php echo h($c['estado_stock']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button"
                                    class="btn btn-sm btn-stock-consumivel"
                                    title="Movimentar stock"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalMovimentarStock"
                                    data-id-consumivel="<?php echo h($c['id_consumivel']); ?>"
                                    data-codigo="<?php echo h($c['codigo_consumivel']); ?>"
                                    data-nome="<?php echo h($c['nome']); ?>"
                                    data-unidade=""
                                    data-stock-atual="<?php echo h((int) $c['stock_atual']); ?>">
                                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                </button>

                                <button type="button"
                                    class="btn btn-sm btn-editar btn-editar-consumivel"
                                    title="Editar consumível"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalConsumivelBD"
                                    data-id-consumivel="<?php echo h($c['id_consumivel']); ?>"
                                    data-codigo="<?php echo h($c['codigo_consumivel']); ?>"
                                    data-nome="<?php echo h($c['nome']); ?>"
                                    data-categoria="<?php echo h($c['categoria']); ?>"
                                    data-unidade=""
                                    data-stock-minimo="<?php echo h($c['stock_minimo']); ?>"
                                    data-stock-maximo="<?php echo h($c['stock_maximo']); ?>"
                                    data-preco-unitario="<?php echo h($c['preco_unitario']); ?>"
                                    data-id-fornecedor="<?php echo h($c['id_fornecedor_preferencial']); ?>"
                                    data-fornecedor-nome="<?php echo h($c['fornecedor_nome'] ?? ''); ?>"
                                    data-observacoes="<?php echo h($c['observacoes']); ?>">
                                    <i class="fa-solid fa-file-pen"></i>
                                </button>

                                <button type="button"
                                    class="btn btn-sm btn-eliminar btn-apagar-consumivel"
                                    title="Remover consumível"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarConsumivel"
                                    data-id-consumivel="<?php echo h($c['id_consumivel']); ?>"
                                    data-codigo="<?php echo h($c['codigo_consumivel']); ?>"
                                    data-nome="<?php echo h($c['nome']); ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<!-- =========================================================
     MODAL ADICIONAR / EDITAR CONSUMÍVEL
     ========================================================= -->
<div class="modal fade" id="modalConsumivelBD" tabindex="-1" aria-labelledby="modalConsumivelBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-acessorio-dialog">
        <div class="modal-content modal-acessorio">

            <form action="consumiveis.php?ref_equipamento=<?php echo url_ref($idEquipamentoSelecionado); ?>" method="post" id="formConsumivelBD" novalidate>
                <input type="hidden" name="acao" id="acaoConsumivelBD" value="criar">
                <input type="hidden" name="id_consumivel" id="idConsumivelBD" value="">
                <input type="hidden" name="id_equipamento" value="<?php echo h($idEquipamentoSelecionado); ?>">

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

                    <div id="erroModalConsumivel" class="alert alert-danger d-none mb-3">
                        <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Erro</strong>
                        <ul id="listaErrosModalConsumivel" class="mb-0 mt-1"></ul>
                    </div>

                    <div class="row g-3">

                        <div class="col-md-12">
                            <label class="form-label">Equipamento principal</label>
                            <input type="text" class="form-control campo-bloqueado"
                                value="<?php echo h($equipamentoSelecionado ? $equipamentoSelecionado['codigo_equipamento'] . ' - ' . $equipamentoSelecionado['designacao'] : '---'); ?>"
                                readonly>
                        </div>

                        <div class="col-md-3">
                            <label for="codigoConsumivelBD" class="form-label">Código</label>
                            <input type="text" class="form-control campo-bloqueado" id="codigoConsumivelBD"
                                value="<?php echo h($proximoCodigoConsumivel); ?>" readonly>
                        </div>

                        <div class="col-md-9">
                            <label for="nomeConsumivelBD" class="form-label">Nome do consumível *</label>
                            <input type="text" class="form-control" id="nomeConsumivelBD" name="nomeConsumivel"
                                placeholder="Ex: Elétrodos descartáveis ECG" maxlength="255">
                            <small class="texto-ajuda-form contador-caracteres" data-target="nomeConsumivelBD" data-max="255">0 / 255 caracteres</small>
                        </div>

                        <div class="col-md-5">
                            <label for="categoriaConsumivelBD" class="form-label">Categoria *</label>
                            <select class="form-select" id="categoriaConsumivelBD" name="categoriaConsumivel">
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

                        <div class="col-md-2 campo-stock-inicial-consumivel">
                            <label for="stockInicialConsumivelBD" class="form-label">Stock inicial</label>
                            <input type="number" min="1" step="1" class="form-control" id="stockInicialConsumivelBD"
                                name="stockInicialConsumivel" value="1">
                        </div>

                        <?php if (!$isEngenheiro): ?>
                        <div class="col-md-2">
                            <label for="stockMinimoConsumivelBD" class="form-label">Stock mínimo</label>
                            <input type="number" min="0" step="1" class="form-control" id="stockMinimoConsumivelBD"
                                name="stockMinimoConsumivel" value="0">
                        </div>

                        <div class="col-md-2">
                            <label for="stockMaximoConsumivelBD" class="form-label">Stock máximo</label>
                            <input type="number" min="0" step="1" class="form-control" id="stockMaximoConsumivelBD"
                                name="stockMaximoConsumivel">
                        </div>

                        <div class="col-md-3">
                            <label for="precoUnitarioConsumivelBD" class="form-label">Preço/un. (€)</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="precoUnitarioConsumivelBD"
                                name="precoUnitarioConsumivel" placeholder="0.00">
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6 position-relative">
                            <label for="fornecedorConsumivelPesquisaBD" class="form-label">Fornecedor *</label>
                            <input
                                type="text"
                                class="form-control"
                                id="fornecedorConsumivelPesquisaBD"
                                name="fornecedorConsumivelTexto"
                                placeholder="Pesquisar fornecedor comercial ou fabricante"
                                autocomplete="off"
                                maxlength="180">
                            <input type="hidden" id="idFornecedorConsumivelBD" name="idFornecedorConsumivel">
                            <div class="lista-fornecedores-custom" id="listaFornecedoresConsumivelBD">
                                <?php foreach ($fornecedores as $forn): ?>
                                    <button
                                        type="button"
                                        class="opcao-fornecedor-custom"
                                        data-id="<?php echo h($forn['id_fornecedor']); ?>"
                                        data-texto="<?php echo h($forn['nome_empresa'] . ' (' . $forn['tipo_fornecedor'] . ')'); ?>">
                                        <span><?php echo h($forn['nome_empresa']); ?></span>
                                        <small><?php echo h($forn['tipo_fornecedor']); ?></small>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="observacoesConsumivelBD" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoesConsumivelBD" name="observacoesConsumivel"
                                rows="3" maxlength="1000"
                                placeholder="Notas sobre reposição, utilização ou validade"></textarea>
                            <small class="texto-ajuda-form contador-caracteres" data-target="observacoesConsumivelBD" data-max="1000">0 / 1000 caracteres</small>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-dados-teste" onclick="dadosTeste_novoConsumivel()">
                        <i class="fa-solid fa-flask me-2"></i> Dados de Teste
                    </button>
                    <button type="submit" class="btn btn-guardar">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Consumível
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- =========================================================
     MODAL MOVIMENTAR STOCK
     ========================================================= -->
<div class="modal fade" id="modalMovimentarStock" tabindex="-1" aria-labelledby="modalMovimentarStockLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:500px">
        <div class="modal-content" style="border:0;border-radius:18px">

            <form action="consumiveis.php?ref_equipamento=<?php echo url_ref($idEquipamentoSelecionado); ?>" method="post" id="formMovimentarStock" novalidate>
                <input type="hidden" name="acao" value="movimentar">
                <input type="hidden" name="id_equipamento" value="<?php echo h($idEquipamentoSelecionado); ?>">
                <input type="hidden" name="id_consumivel" id="movStockIdConsumivel" value="">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalMovimentarStockLabel">
                            <i class="fa-solid fa-arrow-right-arrow-left me-2"></i>
                            Movimentar Stock
                        </h5>
                        <small id="movStockSubtitulo" class="texto-ajuda-form"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">

                    <div id="erroModalMovStock" class="alert alert-danger d-none mb-3">
                        <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Erro</strong>
                        <ul id="listaErrosMovStock" class="mb-0 mt-1"></ul>
                    </div>

                    <div class="mb-3 p-3 rounded" style="background:#f8f9fa;border:1px solid #dee2e6">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Stock atual</span>
                            <strong id="movStockAtualTexto">---</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de movimento *</label>
                        <div class="d-flex gap-2">
                            <label class="tipo-movimento-btn flex-fill text-center">
                                <input type="radio" name="tipoMovimento" value="entrada" class="d-none" checked>
                                <div class="btn btn-outline-success w-100 btn-tipo-movimento">
                                    <i class="fa-solid fa-plus me-1"></i> Entrada
                                </div>
                            </label>
                            <label class="tipo-movimento-btn flex-fill text-center">
                                <input type="radio" name="tipoMovimento" value="saida" class="d-none">
                                <div class="btn btn-outline-danger w-100 btn-tipo-movimento">
                                    <i class="fa-solid fa-minus me-1"></i> Saída
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quantidadeMovimentoBD" class="form-label">Quantidade *</label>
                        <input type="number" class="form-control" id="quantidadeMovimentoBD"
                            name="quantidadeMovimento" min="1" step="1" value="1">
                    </div>

                    <div class="mb-3">
                        <label for="motivoMovimentoBD" class="form-label">Motivo</label>
                        <input type="text" class="form-control" id="motivoMovimentoBD"
                            name="motivoMovimento" maxlength="255"
                            placeholder="Ex: Reposição, utilização em procedimento…">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-guardar">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Movimento
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- =========================================================
     MODAL REMOVER CONSUMÍVEL
     ========================================================= -->
<div class="modal fade" id="modalApagarConsumivel" tabindex="-1" aria-labelledby="modalApagarConsumivelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
        <div class="modal-content modal-apagar-equipamento">

            <div class="modal-header modal-remocao-header">
                <div>
                    <h5 class="modal-title" id="modalApagarConsumivelLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Confirmar remoção
                    </h5>
                    <p class="modal-remocao-subtitulo">Confirme os dados antes de remover o consumível.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body modal-remocao-body">
                <div class="modal-resumo-equipamento modal-resumo-remocao">
                    <div class="modal-linha">
                        <strong>Código</strong>
                        <span id="modalApagarConsumivelCodigo">---</span>
                    </div>
                    <div class="modal-linha">
                        <strong>Consumível</strong>
                        <span id="modalApagarConsumivelNome">---</span>
                    </div>
                    <div class="modal-linha">
                        <strong>Equipamento</strong>
                        <span><?php echo h($equipamentoSelecionado ? $equipamentoSelecionado['codigo_equipamento'] . ' - ' . $equipamentoSelecionado['designacao'] : '---'); ?></span>
                    </div>
                </div>
                <p class="texto-confirmacao-remocao">Confirma que pretende remover este consumível?</p>
            </div>

            <div class="modal-footer modal-remocao-footer">
                <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i> Cancelar
                </button>
                <form action="consumiveis.php?ref_equipamento=<?php echo url_ref($idEquipamentoSelecionado); ?>" method="post">
                    <input type="hidden" name="acao" value="apagar">
                    <input type="hidden" name="id_equipamento" value="<?php echo h($idEquipamentoSelecionado); ?>">
                    <input type="hidden" name="id_consumivel" id="modalApagarConsumivelId" value="">
                    <button type="submit" class="btn btn-confirmar-remocao">
                        <i class="fa-solid fa-trash me-2"></i> Remover Consumível
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = id => document.getElementById(id);

    /* Seletor de equipamento — gerido por .seletor-equipamento-pesquisa em 1230404.js */

    /* Modal adicionar/editar */
    const modal     = $('modalConsumivelBD');
    const form      = $('formConsumivelBD');
    const titulo    = $('modalConsumivelBDLabel');
    const wrapperStockInicial = document.querySelector('.campo-stock-inicial-consumivel');

    function limparErros() {
        const el = $('erroModalConsumivel');
        if (el) el.classList.add('d-none');
    }

    if (modal) {
        modal.addEventListener('show.bs.modal', function (e) {
            form?.reset();
            limparErros();

            const btn = e.relatedTarget;

            if (btn && btn.classList.contains('btn-editar-consumivel')) {
                $('acaoConsumivelBD').value  = 'editar';
                $('idConsumivelBD').value    = btn.dataset.idConsumivel || '';
                $('codigoConsumivelBD').value = btn.dataset.codigo || '';
                $('nomeConsumivelBD').value   = btn.dataset.nome || '';
                $('categoriaConsumivelBD').value = btn.dataset.categoria || '';
                if ($('stockMinimoConsumivelBD')) $('stockMinimoConsumivelBD').value = btn.dataset.stockMinimo || '0';
                if ($('stockMaximoConsumivelBD')) $('stockMaximoConsumivelBD').value = btn.dataset.stockMaximo || '';
                if ($('precoUnitarioConsumivelBD')) $('precoUnitarioConsumivelBD').value = btn.dataset.precoUnitario || '';
                /* Fornecedor pesquisável */
                $('idFornecedorConsumivelBD').value = btn.dataset.idFornecedor || '';
                if ($('fornecedorConsumivelPesquisaBD')) $('fornecedorConsumivelPesquisaBD').value = btn.dataset.fornecedorNome || '';
                $('observacoesConsumivelBD').value  = btn.dataset.observacoes || '';

                if (wrapperStockInicial) wrapperStockInicial.classList.add('d-none');
                if (titulo) titulo.innerHTML = '<i class="fa-solid fa-file-pen me-2"></i>Editar Consumível';
            } else {
                $('acaoConsumivelBD').value = 'criar';
                $('idConsumivelBD').value   = '';
                if ($('stockInicialConsumivelBD')) $('stockInicialConsumivelBD').value = '1';
                if ($('stockMinimoConsumivelBD')) $('stockMinimoConsumivelBD').value = '0';

                if (wrapperStockInicial) wrapperStockInicial.classList.remove('d-none');
                if (titulo) titulo.innerHTML = '<i class="fa-solid fa-box me-2"></i>Adicionar Consumível';
            }
        });
    }

    /* Modal movimentar stock */
    const modalMovStock = $('modalMovimentarStock');
    if (modalMovStock) {
        modalMovStock.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            if (!btn) return;

            $('movStockIdConsumivel').value = btn.dataset.idConsumivel || '';
            $('quantidadeMovimentoBD').value = '1';
            $('motivoMovimentoBD').value = '';
            $('erroModalMovStock')?.classList.add('d-none');

            const nome     = btn.dataset.nome || '---';
            const codigo   = btn.dataset.codigo || '';
            const stockAtual = btn.dataset.stockAtual || '0';

            const sub = $('movStockSubtitulo');
            if (sub) sub.textContent = codigo + ' — ' + nome;

            const txt = $('movStockAtualTexto');
            if (txt) txt.textContent = stockAtual;

            /* reset radio para entrada */
            const radios = modalMovStock.querySelectorAll('input[name="tipoMovimento"]');
            radios.forEach(r => { r.checked = r.value === 'entrada'; });
        });

        /* Estilo visual dos botões de tipo */
        modalMovStock.addEventListener('change', function (e) {
            if (e.target.name !== 'tipoMovimento') return;
            modalMovStock.querySelectorAll('.btn-tipo-movimento').forEach(btn => {
                btn.classList.remove('btn-success', 'btn-danger', 'text-white');
                if (btn.closest('label').querySelector('input').checked) {
                    btn.classList.add(e.target.value === 'entrada' ? 'btn-success' : 'btn-danger', 'text-white');
                }
            });
        });
    }

    /* Pesquisa de fornecedor no modal consumível */
    iniciarPesquisaFornecedor('fornecedorConsumivelPesquisaBD', 'idFornecedorConsumivelBD', 'listaFornecedoresConsumivelBD', 'modalConsumivelBD');

    /* Modal apagar */
    const modalApagar = $('modalApagarConsumivel');
    if (modalApagar) {
        modalApagar.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            if (!btn) return;
            $('modalApagarConsumivelId').value         = btn.dataset.idConsumivel || '';
            $('modalApagarConsumivelCodigo').textContent = btn.dataset.codigo || '---';
            $('modalApagarConsumivelNome').textContent   = btn.dataset.nome || '---';
        });
    }

    <?php if (!empty($erro_modal)): ?>
    /* Reabrir modal com erros após falha de validação server-side */
    (function () {
        const erros = <?php echo json_encode(explode('|', $erro_modal)); ?>;
        const dados  = <?php echo json_encode($dadosModalErro); ?>;
        const acao   = dados.acao || 'criar';

        const blocoErros = $('erroModalConsumivel');
        const listaErros = $('listaErrosModalConsumivel');
        if (blocoErros && listaErros && erros.length) {
            listaErros.innerHTML = erros.map(function (e) { return '<li>' + e + '</li>'; }).join('');
            blocoErros.classList.remove('d-none');
        }

        const wrapperStockInicial = document.querySelector('.campo-stock-inicial-consumivel');
        const titulo = $('modalConsumivelBDLabel');

        if (acao === 'editar') {
            $('acaoConsumivelBD').value   = 'editar';
            $('idConsumivelBD').value     = dados.id_consumivel || '';
            $('codigoConsumivelBD').value = dados.codigo_consumivel || '';
            if (wrapperStockInicial) wrapperStockInicial.classList.add('d-none');
            if (titulo) titulo.innerHTML  = '<i class="fa-solid fa-file-pen me-2"></i>Editar Consumível';
        } else {
            $('acaoConsumivelBD').value = 'criar';
            $('idConsumivelBD').value   = '';
            if (wrapperStockInicial) wrapperStockInicial.classList.remove('d-none');
            if (titulo) titulo.innerHTML = '<i class="fa-solid fa-box me-2"></i>Adicionar Consumível';
        }

        if ($('nomeConsumivelBD'))       $('nomeConsumivelBD').value       = dados.nomeConsumivel || '';
        if ($('categoriaConsumivelBD'))  $('categoriaConsumivelBD').value  = dados.categoriaConsumivel || '';
        if ($('stockInicialConsumivelBD')) $('stockInicialConsumivelBD').value = dados.stockInicialConsumivel || '0';
        if ($('stockMinimoConsumivelBD')) $('stockMinimoConsumivelBD').value = dados.stockMinimoConsumivel || '0';
        if ($('stockMaximoConsumivelBD')) $('stockMaximoConsumivelBD').value = dados.stockMaximoConsumivel || '';
        if ($('precoUnitarioConsumivelBD')) $('precoUnitarioConsumivelBD').value = dados.precoUnitarioConsumivel || '';
        if ($('idFornecedorConsumivelBD')) $('idFornecedorConsumivelBD').value = dados.idFornecedorConsumivel || '';
        if ($('observacoesConsumivelBD')) $('observacoesConsumivelBD').value = dados.observacoesConsumivel || '';

        var modalEl = document.getElementById('modalConsumivelBD');
        if (modalEl && typeof bootstrap !== 'undefined') {
            var modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInst.show();
        }
    })();
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
