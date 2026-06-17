<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   ACESSÓRIOS - MEDICORE
   Estrutura compatível com:
   - equipamentos
   - acessorios_equipamento
   - fornecedores
   - manutencoes_equipamento com id_acessorio
   - calibracoes_equipamento com id_acessorio
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

function formatar_data_pt($data)
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

function texto_tipo_acessorio($tipo)
{
    $tipos = [
        'sensor' => 'Sensor',
        'cabo' => 'Cabo',
        'modulo' => 'Módulo',
        'consumivel_reutilizavel' => 'Consumível reutilizável',
        'adaptador' => 'Adaptador',
        'bateria' => 'Bateria',
        'outro' => 'Outro'
    ];

    return $tipos[$tipo] ?? $tipo;
}

function texto_estado_acessorio($estado)
{
    $estados = [
        'ativo' => 'Ativo',
        'inativo' => 'Inativo',
        'avariado' => 'Avariado',
        'em_manutencao' => 'Em manutenção',
        'em_calibracao' => 'Em calibração',
        'abatido' => 'Abatido'
    ];

    return $estados[$estado] ?? $estado;
}

function classe_estado_acessorio($estado)
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

function texto_periodicidade($periodicidade)
{
    $periodicidades = [
        'semestral' => 'Semestral',
        'anual' => 'Anual',
        'bienal' => 'Bienal',
        'trienal' => 'Trienal'
    ];

    return $periodicidades[$periodicidade] ?? '---';
}

function proxima_intervencao_acessorio($proximaManutencao, $proximaCalibracao)
{
    if (empty($proximaManutencao) && empty($proximaCalibracao)) {
        return '---';
    }

    if (empty($proximaManutencao)) {
        return formatar_data_pt($proximaCalibracao);
    }

    if (empty($proximaCalibracao)) {
        return formatar_data_pt($proximaManutencao);
    }

    return strtotime($proximaManutencao) <= strtotime($proximaCalibracao)
        ? formatar_data_pt($proximaManutencao)
        : formatar_data_pt($proximaCalibracao);
}

function valor_ou_null($valor)
{
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : $valor;
}

$pdo = null;
$erro_bd = '';
$mensagem_sucesso = '';
$equipamentos = [];
$fornecedoresGarantia = [];
$localizacoes = [];
$acessorios = [];
$equipamentoSelecionado = null;
$idEquipamentoSelecionado = 0;
$proximoCodigoAcessorio = 'Gerado automaticamente';

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

    /* =========================================================
       AÇÕES POST
       ========================================================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';
        $idEquipamentoPost = (int) ($_POST['id_equipamento'] ?? 0);
        $utilizadorAtual = $_SESSION['nome'] ?? $_SESSION['username'] ?? 'admin';

        if ($acao === 'criar') {
            if ($idEquipamentoPost <= 0) {
                throw new RuntimeException('Selecione um equipamento válido.');
            }

            $designacao = valor_ou_null($_POST['designacaoAcessorio'] ?? null);
            $tipo = $_POST['tipoAcessorio'] ?? 'outro';
            $estado = $_POST['estadoAcessorio'] ?? 'ativo';

            if (empty($designacao)) {
                throw new RuntimeException('Indique o nome/designação do acessório.');
            }

            $requerManutencao = (int) ($_POST['requerManutencao'] ?? 0);
            $requerCalibracao = (int) ($_POST['requerCalibracao'] ?? 0);

            $periodicidadeManutencao = $requerManutencao === 1
                ? valor_ou_null($_POST['periodicidadeManutencao'] ?? null)
                : null;

            $periodicidadeCalibracao = $requerCalibracao === 1
                ? valor_ou_null($_POST['periodicidadeCalibracao'] ?? null)
                : null;

            $idFornecedorGarantia = (int) ($_POST['idFornecedorGarantia'] ?? 0);
            $idFornecedorGarantia = $idFornecedorGarantia > 0 ? $idFornecedorGarantia : null;

            $idLocalizacaoAcessorio = (int) ($_POST['idLocalizacaoAcessorio'] ?? 0);

            if ($idLocalizacaoAcessorio <= 0) {
                throw new RuntimeException('Selecione a localização do acessório.');
            }

            $stmtProximoNumero = $pdo->prepare(" 
                SELECT COALESCE(MAX(numero_sequencial), 0) + 1 AS proximo_numero
                FROM acessorios_equipamento
                WHERE id_equipamento = :id_equipamento
            ");

            $stmtProximoNumero->execute([
                ':id_equipamento' => $idEquipamentoPost
            ]);

            $proximoNumero = (int) $stmtProximoNumero->fetchColumn();

            $stmtInserir = $pdo->prepare("
                INSERT INTO acessorios_equipamento (
                    id_equipamento,
                    id_localizacao,
                    numero_sequencial,
                    designacao,
                    tipo,
                    fabricante,
                    modelo,
                    numero_serie,
                    estado,
                    requer_manutencao,
                    periodicidade_manutencao,
                    requer_calibracao,
                    periodicidade_calibracao,
                    id_fornecedor_garantia,
                    data_inicio_garantia,
                    data_fim_garantia,
                    observacoes,
                    atualizado_por
                ) VALUES (
                    :id_equipamento,
                    :id_localizacao,
                    :numero_sequencial,
                    :designacao,
                    :tipo,
                    :fabricante,
                    :modelo,
                    :numero_serie,
                    :estado,
                    :requer_manutencao,
                    :periodicidade_manutencao,
                    :requer_calibracao,
                    :periodicidade_calibracao,
                    :id_fornecedor_garantia,
                    :data_inicio_garantia,
                    :data_fim_garantia,
                    :observacoes,
                    :atualizado_por
                )
            ");

            $stmtInserir->execute([
                ':id_equipamento' => $idEquipamentoPost,
                ':id_localizacao' => $idLocalizacaoAcessorio,
                ':numero_sequencial' => $proximoNumero,
                ':designacao' => $designacao,
                ':tipo' => $tipo,
                ':fabricante' => valor_ou_null($_POST['fabricanteAcessorio'] ?? null),
                ':modelo' => valor_ou_null($_POST['modeloAcessorio'] ?? null),
                ':numero_serie' => valor_ou_null($_POST['numeroSerieAcessorio'] ?? null),
                ':estado' => $estado,
                ':requer_manutencao' => $requerManutencao,
                ':periodicidade_manutencao' => $periodicidadeManutencao,
                ':requer_calibracao' => $requerCalibracao,
                ':periodicidade_calibracao' => $periodicidadeCalibracao,
                ':id_fornecedor_garantia' => $idFornecedorGarantia,
                ':data_inicio_garantia' => valor_ou_null($_POST['dataInicioGarantia'] ?? null),
                ':data_fim_garantia' => valor_ou_null($_POST['dataFimGarantia'] ?? null),
                ':observacoes' => valor_ou_null($_POST['observacoesAcessorio'] ?? null),
                ':atualizado_por' => $utilizadorAtual
            ]);

            header('Location: acessorios.php?id_equipamento=' . $idEquipamentoPost . '&criado=1');
            exit;
        }

        if ($acao === 'editar') {
            $idAcessorio = (int) ($_POST['id_acessorio'] ?? 0);

            if ($idAcessorio <= 0) {
                throw new RuntimeException('Acessório inválido.');
            }

            $designacao = valor_ou_null($_POST['designacaoAcessorio'] ?? null);
            $tipo = $_POST['tipoAcessorio'] ?? 'outro';
            $estado = $_POST['estadoAcessorio'] ?? 'ativo';

            if (empty($designacao)) {
                throw new RuntimeException('Indique o nome/designação do acessório.');
            }

            $requerManutencao = (int) ($_POST['requerManutencao'] ?? 0);
            $requerCalibracao = (int) ($_POST['requerCalibracao'] ?? 0);

            $periodicidadeManutencao = $requerManutencao === 1
                ? valor_ou_null($_POST['periodicidadeManutencao'] ?? null)
                : null;

            $periodicidadeCalibracao = $requerCalibracao === 1
                ? valor_ou_null($_POST['periodicidadeCalibracao'] ?? null)
                : null;

            $idFornecedorGarantia = (int) ($_POST['idFornecedorGarantia'] ?? 0);
            $idFornecedorGarantia = $idFornecedorGarantia > 0 ? $idFornecedorGarantia : null;

            $idLocalizacaoAcessorio = (int) ($_POST['idLocalizacaoAcessorio'] ?? 0);

            if ($idLocalizacaoAcessorio <= 0) {
                throw new RuntimeException('Selecione a localização do acessório.');
            }

            $stmtAtualizar = $pdo->prepare("
                UPDATE acessorios_equipamento
                SET
                    id_localizacao = :id_localizacao,
                    designacao = :designacao,
                    tipo = :tipo,
                    fabricante = :fabricante,
                    modelo = :modelo,
                    numero_serie = :numero_serie,
                    estado = :estado,
                    requer_manutencao = :requer_manutencao,
                    periodicidade_manutencao = :periodicidade_manutencao,
                    requer_calibracao = :requer_calibracao,
                    periodicidade_calibracao = :periodicidade_calibracao,
                    id_fornecedor_garantia = :id_fornecedor_garantia,
                    data_inicio_garantia = :data_inicio_garantia,
                    data_fim_garantia = :data_fim_garantia,
                    observacoes = :observacoes,
                    atualizado_por = :atualizado_por
                WHERE id_acessorio = :id_acessorio
                  AND isActive = 1
            ");

            $stmtAtualizar->execute([
                ':id_acessorio' => $idAcessorio,
                ':id_localizacao' => $idLocalizacaoAcessorio,
                ':designacao' => $designacao,
                ':tipo' => $tipo,
                ':fabricante' => valor_ou_null($_POST['fabricanteAcessorio'] ?? null),
                ':modelo' => valor_ou_null($_POST['modeloAcessorio'] ?? null),
                ':numero_serie' => valor_ou_null($_POST['numeroSerieAcessorio'] ?? null),
                ':estado' => $estado,
                ':requer_manutencao' => $requerManutencao,
                ':periodicidade_manutencao' => $periodicidadeManutencao,
                ':requer_calibracao' => $requerCalibracao,
                ':periodicidade_calibracao' => $periodicidadeCalibracao,
                ':id_fornecedor_garantia' => $idFornecedorGarantia,
                ':data_inicio_garantia' => valor_ou_null($_POST['dataInicioGarantia'] ?? null),
                ':data_fim_garantia' => valor_ou_null($_POST['dataFimGarantia'] ?? null),
                ':observacoes' => valor_ou_null($_POST['observacoesAcessorio'] ?? null),
                ':atualizado_por' => $utilizadorAtual
            ]);

            $idEquipamentoRedirecionar = $idEquipamentoPost > 0 ? $idEquipamentoPost : (int) ($_GET['id_equipamento'] ?? 0);
            header('Location: acessorios.php?id_equipamento=' . $idEquipamentoRedirecionar . '&editado=1');
            exit;
        }

        if ($acao === 'apagar') {
            $idAcessorio = (int) ($_POST['id_acessorio'] ?? 0);

            if ($idAcessorio > 0) {
                $stmtApagar = $pdo->prepare("
                    UPDATE acessorios_equipamento
                    SET
                        isActive = 0,
                        estado = 'inativo',
                        atualizado_por = :atualizado_por
                    WHERE id_acessorio = :id_acessorio
                ");

                $stmtApagar->execute([
                    ':id_acessorio' => $idAcessorio,
                    ':atualizado_por' => $utilizadorAtual
                ]);
            }

            $idEquipamentoRedirecionar = $idEquipamentoPost > 0 ? $idEquipamentoPost : (int) ($_GET['id_equipamento'] ?? 0);
            header('Location: acessorios.php?id_equipamento=' . $idEquipamentoRedirecionar . '&removido=1');
            exit;
        }
    }

    /* =========================================================
       CARREGAMENTO DE DADOS
       ========================================================= */
    $stmtEquipamentos = $pdo->query("
        SELECT id_equipamento, codigo_equipamento, designacao, id_localizacao
        FROM equipamentos
        WHERE isActive = 1
        ORDER BY codigo_equipamento ASC
    ");

    $equipamentos = $stmtEquipamentos->fetchAll();

    $idEquipamentoSelecionado = (int) ($_GET['id_equipamento'] ?? 0);

    if ($idEquipamentoSelecionado <= 0 && !empty($equipamentos)) {
        $idEquipamentoSelecionado = (int) $equipamentos[0]['id_equipamento'];
    }

    foreach ($equipamentos as $equipamento) {
        if ((int) $equipamento['id_equipamento'] === $idEquipamentoSelecionado) {
            $equipamentoSelecionado = $equipamento;
            break;
        }
    }

    $stmtFornecedores = $pdo->query("
        SELECT id_fornecedor, nome_empresa, tipo_fornecedor
        FROM fornecedores
        WHERE isActive = 1
        ORDER BY nome_empresa ASC
    ");

    $fornecedoresGarantia = $stmtFornecedores->fetchAll();

    $stmtLocalizacoes = $pdo->query("
        SELECT id_localizacao, codigo, departamento_nome, edificio, piso, sala
        FROM localizacoes
        WHERE isActive = 1
        ORDER BY departamento_nome ASC, edificio ASC, piso ASC, sala ASC
    ");

    $localizacoes = $stmtLocalizacoes->fetchAll();

    if ($idEquipamentoSelecionado > 0) {
        $stmtAcessorios = $pdo->prepare("
            SELECT
                a.*,
                e.codigo_equipamento,
                e.designacao AS equipamento_nome,
                CONCAT(e.codigo_equipamento, '.', LPAD(a.numero_sequencial, 3, '0')) AS codigo_acessorio,
                f.nome_empresa AS fornecedor_garantia_nome,
                l.codigo AS codigo_localizacao,
                l.departamento_nome,
                l.edificio,
                l.piso,
                l.sala,

                (
                    SELECT MAX(m.proxima_manutencao)
                    FROM manutencoes_equipamento m
                    WHERE m.id_acessorio = a.id_acessorio
                      AND m.isActive = 1
                ) AS proxima_manutencao,

                (
                    SELECT MAX(c.proxima_calibracao)
                    FROM calibracoes_equipamento c
                    WHERE c.id_acessorio = a.id_acessorio
                      AND c.isActive = 1
                ) AS proxima_calibracao

            FROM acessorios_equipamento a

            INNER JOIN equipamentos e
                ON e.id_equipamento = a.id_equipamento

            LEFT JOIN fornecedores f
                ON f.id_fornecedor = a.id_fornecedor_garantia

            LEFT JOIN localizacoes l
                ON l.id_localizacao = a.id_localizacao

            WHERE a.id_equipamento = :id_equipamento
              AND a.isActive = 1

            ORDER BY a.numero_sequencial ASC
        ");

        $stmtAcessorios->execute([
            ':id_equipamento' => $idEquipamentoSelecionado
        ]);

        $acessorios = $stmtAcessorios->fetchAll();

        $stmtProximo = $pdo->prepare("
            SELECT COALESCE(MAX(numero_sequencial), 0) + 1 AS proximo_numero
            FROM acessorios_equipamento
            WHERE id_equipamento = :id_equipamento
        ");

        $stmtProximo->execute([
            ':id_equipamento' => $idEquipamentoSelecionado
        ]);

        $proximoNumero = (int) $stmtProximo->fetchColumn();

        if ($equipamentoSelecionado) {
            $proximoCodigoAcessorio = $equipamentoSelecionado['codigo_equipamento'] . '.' . str_pad((string) $proximoNumero, 3, '0', STR_PAD_LEFT);
        }
    }

} catch (Throwable $e) {
    $erro_bd = $e->getMessage();
}

if (isset($_GET['criado'])) {
    $mensagem_sucesso = 'Acessório registado com sucesso.';
} elseif (isset($_GET['editado'])) {
    $mensagem_sucesso = 'Acessório atualizado com sucesso.';
} elseif (isset($_GET['removido'])) {
    $mensagem_sucesso = 'Acessório removido da lista com sucesso.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<style>
    /* =========================================================
       MODAL DE ACESSÓRIOS
       Mantém o formulário largo e com scroll interno, sem cortar campos.
       ========================================================= */
    #modalAcessorioBD .modal-dialog {
        width: min(1180px, calc(100vw - 32px));
        max-width: min(1180px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modalAcessorioBD .modal-content {
        border: 0;
        border-radius: 18px;
        max-height: calc(100vh - 32px);
        overflow: hidden;
    }

    #modalAcessorioBD .modal-content > form {
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 32px);
        min-height: 0;
    }

    #modalAcessorioBD .modal-header,
    #modalAcessorioBD .modal-footer {
        flex: 0 0 auto;
    }

    #modalAcessorioBD .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        max-height: calc(100vh - 190px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1.5rem;
    }

    #modalAcessorioBD .modal-body .row {
        row-gap: 1rem;
    }

    #modalAcessorioBD input,
    #modalAcessorioBD select,
    #modalAcessorioBD textarea {
        min-width: 0;
    }

    @media (max-width: 768px) {
        #modalAcessorioBD .modal-dialog {
            width: calc(100vw - 16px);
            max-width: calc(100vw - 16px);
            margin: .5rem auto;
        }

        #modalAcessorioBD .modal-body {
            max-height: calc(100vh - 170px);
            padding: 1rem;
        }
    }
</style>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Gestão de Acessórios</h2>
            <p class="subtitulo-pagina">
                Associe acessórios aos equipamentos e acompanhe a sua necessidade de manutenção ou calibração.
            </p>
        </div>
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

    <section class="filtros-tabela" aria-label="Seleção de equipamento para acessórios">
        <div class="row g-3 align-items-end">

            <div class="col-lg-12">
                <label for="seletorEquipamentoAcessoriosBD" class="form-label">Equipamento</label>
                <select class="form-select" id="seletorEquipamentoAcessoriosBD">
                    <?php if (empty($equipamentos)): ?>
                        <option value="">Sem equipamentos registados</option>
                    <?php else: ?>
                        <?php foreach ($equipamentos as $equipamento): ?>
                            <option
                                value="<?php echo h($equipamento['id_equipamento']); ?>"
                                data-id-localizacao="<?php echo h($equipamento['id_localizacao']); ?>"
                                <?php echo selected_option($idEquipamentoSelecionado, $equipamento['id_equipamento']); ?>>
                                <?php echo h($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

        </div>
    </section>

    <div class="tabela-container">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <div>
                <h5 class="subtitulo-bloco-form mb-1">Acessórios associados</h5>

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
                id="btnAbrirModalNovoAcessorioBD"
                data-bs-toggle="modal"
                data-bs-target="#modalAcessorioBD"
                data-codigo-preview="<?php echo h($proximoCodigoAcessorio); ?>"
                <?php echo empty($equipamentoSelecionado) ? 'disabled' : ''; ?>>
                <i class="fa-solid fa-plus me-2"></i>
                Adicionar Acessório
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle tabela-acessorios tabela-datatables-medicore" id="tabelaAcessoriosBD">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Acessório</th>
                        <th>Tipo</th>
                        <th>N.º Série</th>
                        <th>Localização</th>
                        <th>Estado</th>
                        <th>Manutenção</th>
                        <th>Calibração</th>
                        <th>Próxima intervenção</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($acessorios)): ?>
                        <?php foreach ($acessorios as $acessorio): ?>
                            <?php
                                $codigoAcessorio = $acessorio['codigo_acessorio'];
                                $proximaIntervencao = proxima_intervencao_acessorio(
                                    $acessorio['proxima_manutencao'] ?? null,
                                    $acessorio['proxima_calibracao'] ?? null
                                );
                            ?>

                            <tr>
                                <td><?php echo h($codigoAcessorio); ?></td>
                                <td><?php echo h($acessorio['designacao']); ?></td>
                                <td><?php echo h(texto_tipo_acessorio($acessorio['tipo'])); ?></td>
                                <td><?php echo h($acessorio['numero_serie'] ?: '---'); ?></td>
                                <td>
                                    <?php
                                        $textoLocalizacao = !empty($acessorio['codigo_localizacao'])
                                            ? $acessorio['codigo_localizacao'] . ' - Sala ' . $acessorio['sala']
                                            : '---';
                                    ?>
                                    <?php echo h($textoLocalizacao); ?>
                                </td>
                                <td>
                                    <span class="estado <?php echo h(classe_estado_acessorio($acessorio['estado'])); ?>">
                                        <?php echo h(texto_estado_acessorio($acessorio['estado'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int) $acessorio['requer_manutencao'] === 1): ?>
                                        <span class="badge-detalhe">Sim</span>
                                        <small class="d-block text-muted"><?php echo h(texto_periodicidade($acessorio['periodicidade_manutencao'])); ?></small>
                                    <?php else: ?>
                                        Não
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) $acessorio['requer_calibracao'] === 1): ?>
                                        <span class="badge-detalhe">Sim</span>
                                        <small class="d-block text-muted"><?php echo h(texto_periodicidade($acessorio['periodicidade_calibracao'])); ?></small>
                                    <?php else: ?>
                                        Não
                                    <?php endif; ?>
                                </td>
                                <td><?php echo h($proximaIntervencao); ?></td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-editar btn-editar-acessorio-bd"
                                        title="Editar acessório"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAcessorioBD"
                                        data-id-acessorio="<?php echo h($acessorio['id_acessorio']); ?>"
                                        data-codigo="<?php echo h($codigoAcessorio); ?>"
                                        data-designacao="<?php echo h($acessorio['designacao']); ?>"
                                        data-tipo="<?php echo h($acessorio['tipo']); ?>"
                                        data-fabricante="<?php echo h($acessorio['fabricante']); ?>"
                                        data-modelo="<?php echo h($acessorio['modelo']); ?>"
                                        data-numero-serie="<?php echo h($acessorio['numero_serie']); ?>"
                                        data-id-localizacao="<?php echo h($acessorio['id_localizacao']); ?>"
                                        data-estado="<?php echo h($acessorio['estado']); ?>"
                                        data-requer-manutencao="<?php echo h($acessorio['requer_manutencao']); ?>"
                                        data-periodicidade-manutencao="<?php echo h($acessorio['periodicidade_manutencao']); ?>"
                                        data-requer-calibracao="<?php echo h($acessorio['requer_calibracao']); ?>"
                                        data-periodicidade-calibracao="<?php echo h($acessorio['periodicidade_calibracao']); ?>"
                                        data-id-fornecedor-garantia="<?php echo h($acessorio['id_fornecedor_garantia']); ?>"
                                        data-data-inicio-garantia="<?php echo h($acessorio['data_inicio_garantia']); ?>"
                                        data-data-fim-garantia="<?php echo h($acessorio['data_fim_garantia']); ?>"
                                        data-observacoes="<?php echo h($acessorio['observacoes']); ?>">
                                        <i class="fa-solid fa-file-pen"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-eliminar btn-apagar-acessorio-bd"
                                        title="Remover acessório"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminarAcessorioBD"
                                        data-id-acessorio="<?php echo h($acessorio['id_acessorio']); ?>"
                                        data-codigo="<?php echo h($codigoAcessorio); ?>"
                                        data-designacao="<?php echo h($acessorio['designacao']); ?>"
                                        data-tipo="<?php echo h(texto_tipo_acessorio($acessorio['tipo'])); ?>"
                                        data-serie="<?php echo h($acessorio['numero_serie'] ?: '---'); ?>"
                                        data-estado="<?php echo h(texto_estado_acessorio($acessorio['estado'])); ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<!-- =========================================================
     MODAL ADICIONAR / EDITAR ACESSÓRIO
     ========================================================= -->
<div class="modal fade" id="modalAcessorioBD" tabindex="-1" aria-labelledby="modalAcessorioBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-acessorio-dialog">
        <div class="modal-content modal-acessorio">

            <form action="acessorios.php?id_equipamento=<?php echo h($idEquipamentoSelecionado); ?>" method="post" id="formAcessorioBD">
                <input type="hidden" name="acao" id="acaoAcessorioBD" value="criar">
                <input type="hidden" name="id_acessorio" id="idAcessorioBD" value="">
                <input type="hidden" name="id_equipamento" value="<?php echo h($idEquipamentoSelecionado); ?>">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalAcessorioBDLabel">
                            <i class="fa-solid fa-plug-circle-bolt me-2"></i>
                            Adicionar Acessório
                        </h5>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label for="equipamentoAcessorioBD" class="form-label">Equipamento principal</label>
                            <input
                                type="text"
                                class="form-control campo-bloqueado"
                                id="equipamentoAcessorioBD"
                                value="<?php echo h($equipamentoSelecionado ? $equipamentoSelecionado['codigo_equipamento'] . ' - ' . $equipamentoSelecionado['designacao'] : '---'); ?>"
                                readonly>
                        </div>

                        <div class="col-md-3">
                            <label for="codigoAcessorioBD" class="form-label">Código do acessório</label>
                            <input
                                type="text"
                                class="form-control campo-bloqueado"
                                id="codigoAcessorioBD"
                                value="<?php echo h($proximoCodigoAcessorio); ?>"
                                readonly>
                        </div>

                        <div class="col-md-9">
                            <label for="designacaoAcessorioBD" class="form-label">Nome do acessório *</label>
                            <input
                                type="text"
                                class="form-control"
                                id="designacaoAcessorioBD"
                                name="designacaoAcessorio"
                                placeholder="Ex: Sensor SpO2 adulto"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="idLocalizacaoAcessorioBD" class="form-label">Localização do acessório *</label>
                            <select class="form-select" id="idLocalizacaoAcessorioBD" name="idLocalizacaoAcessorio" required>
                                <option value="">Selecionar localização</option>
                                <?php foreach ($localizacoes as $localizacao): ?>
                                    <option value="<?php echo h($localizacao['id_localizacao']); ?>">
                                        <?php echo h($localizacao['codigo'] . ' | ' . $localizacao['departamento_nome'] . ' - ' . $localizacao['edificio'] . ' - Piso ' . $localizacao['piso'] . ' - Sala ' . $localizacao['sala']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoAcessorioBD" class="form-label">Tipo *</label>
                            <select class="form-select" id="tipoAcessorioBD" name="tipoAcessorio" required>
                                <option value="">Selecionar</option>
                                <option value="sensor">Sensor</option>
                                <option value="cabo">Cabo</option>
                                <option value="modulo">Módulo</option>
                                <option value="consumivel_reutilizavel">Consumível reutilizável</option>
                                <option value="adaptador">Adaptador</option>
                                <option value="bateria">Bateria</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="fabricanteAcessorioBD" class="form-label">Fabricante</label>
                            <input
                                type="text"
                                class="form-control"
                                id="fabricanteAcessorioBD"
                                name="fabricanteAcessorio"
                                placeholder="Ex: Philips">
                        </div>

                        <div class="col-md-4">
                            <label for="modeloAcessorioBD" class="form-label">Modelo</label>
                            <input
                                type="text"
                                class="form-control"
                                id="modeloAcessorioBD"
                                name="modeloAcessorio"
                                placeholder="Modelo do acessório">
                        </div>

                        <div class="col-md-4">
                            <label for="numeroSerieAcessorioBD" class="form-label">N.º Série</label>
                            <input
                                type="text"
                                class="form-control"
                                id="numeroSerieAcessorioBD"
                                name="numeroSerieAcessorio"
                                placeholder="SN-ACC-0001">
                        </div>

                        <div class="col-md-4">
                            <label for="estadoAcessorioBD" class="form-label">Estado</label>
                            <select class="form-select" id="estadoAcessorioBD" name="estadoAcessorio">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="avariado">Avariado</option>
                                <option value="em_manutencao">Em manutenção</option>
                                <option value="em_calibracao">Em calibração</option>
                                <option value="abatido">Abatido</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="idFornecedorGarantiaBD" class="form-label">Fornecedor da garantia</label>
                            <select class="form-select" id="idFornecedorGarantiaBD" name="idFornecedorGarantia">
                                <option value="">Sem fornecedor de garantia</option>

                                <?php foreach ($fornecedoresGarantia as $fornecedor): ?>
                                    <option value="<?php echo h($fornecedor['id_fornecedor']); ?>">
                                        <?php echo h($fornecedor['nome_empresa'] . ' (' . $fornecedor['tipo_fornecedor'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="dataInicioGarantiaBD" class="form-label">Início da garantia</label>
                            <input
                                type="date"
                                class="form-control"
                                id="dataInicioGarantiaBD"
                                name="dataInicioGarantia">
                        </div>

                        <div class="col-md-6">
                            <label for="dataFimGarantiaBD" class="form-label">Fim da garantia</label>
                            <input
                                type="date"
                                class="form-control"
                                id="dataFimGarantiaBD"
                                name="dataFimGarantia">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block">Requer manutenção?</label>

                            <div class="d-flex gap-4 align-items-center">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="requerManutencao"
                                        id="requerManutencaoNaoBD"
                                        value="0"
                                        checked>
                                    <label class="form-check-label" for="requerManutencaoNaoBD">
                                        Não
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="requerManutencao"
                                        id="requerManutencaoSimBD"
                                        value="1">
                                    <label class="form-check-label" for="requerManutencaoSimBD">
                                        Sim
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="periodicidadeManutencaoBD" class="form-label">Periodicidade de manutenção</label>
                            <select class="form-select" id="periodicidadeManutencaoBD" name="periodicidadeManutencao" disabled>
                                <option value="">Não aplicável</option>
                                <option value="semestral">Semestral</option>
                                <option value="anual">Anual</option>
                                <option value="bienal">Bienal</option>
                                <option value="trienal">Trienal</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block">Requer calibração?</label>

                            <div class="d-flex gap-4 align-items-center">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="requerCalibracao"
                                        id="requerCalibracaoNaoBD"
                                        value="0"
                                        checked>
                                    <label class="form-check-label" for="requerCalibracaoNaoBD">
                                        Não
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="requerCalibracao"
                                        id="requerCalibracaoSimBD"
                                        value="1">
                                    <label class="form-check-label" for="requerCalibracaoSimBD">
                                        Sim
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="periodicidadeCalibracaoBD" class="form-label">Periodicidade de calibração</label>
                            <select class="form-select" id="periodicidadeCalibracaoBD" name="periodicidadeCalibracao" disabled>
                                <option value="">Não aplicável</option>
                                <option value="semestral">Semestral</option>
                                <option value="anual">Anual</option>
                                <option value="bienal">Bienal</option>
                                <option value="trienal">Trienal</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="observacoesAcessorioBD" class="form-label">Observações</label>
                            <textarea
                                class="form-control"
                                id="observacoesAcessorioBD"
                                name="observacoesAcessorio"
                                rows="3"
                                placeholder="Notas relevantes sobre o acessório"></textarea>
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
                        Guardar Acessório
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- =========================================================
     MODAL REMOVER ACESSÓRIO
     ========================================================= -->
<div class="modal fade" id="modalEliminarAcessorioBD" tabindex="-1" aria-labelledby="modalEliminarAcessorioBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
        <div class="modal-content modal-apagar-equipamento">

            <div class="modal-header modal-remocao-header">
                <div>
                    <h5 class="modal-title" id="modalEliminarAcessorioBDLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Confirmar remoção
                    </h5>
                    <p class="modal-remocao-subtitulo">Confirme os dados antes de remover o acessório.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body modal-remocao-body">
                <div class="modal-resumo-equipamento modal-resumo-remocao">
                    <div class="modal-linha">
                        <strong>Código</strong>
                        <span id="modalEliminarAcessorioCodigoBD">---</span>
                    </div>
                    <div class="modal-linha">
                        <strong>Acessório</strong>
                        <span id="modalEliminarAcessorioNomeBD">---</span>
                    </div>
                    <div class="modal-linha">
                        <strong>Equipamento principal</strong>
                        <span><?php echo h($equipamentoSelecionado ? $equipamentoSelecionado['codigo_equipamento'] . ' - ' . $equipamentoSelecionado['designacao'] : '---'); ?></span>
                    </div>
                    <div class="modal-linha">
                        <strong>Tipo</strong>
                        <span id="modalEliminarAcessorioTipoBD">---</span>
                    </div>
                    <div class="modal-linha">
                        <strong>N.º Série</strong>
                        <span id="modalEliminarAcessorioSerieBD">---</span>
                    </div>
                    <div class="modal-linha">
                        <strong>Estado</strong>
                        <span id="modalEliminarAcessorioEstadoBD">---</span>
                    </div>
                </div>

                <p class="texto-confirmacao-remocao">
                    Confirma que pretende remover este acessório da lista?
                </p>
            </div>

            <div class="modal-footer modal-remocao-footer">
                <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i>
                    Cancelar
                </button>

                <form action="acessorios.php?id_equipamento=<?php echo h($idEquipamentoSelecionado); ?>" method="post">
                    <input type="hidden" name="acao" value="apagar">
                    <input type="hidden" name="id_equipamento" value="<?php echo h($idEquipamentoSelecionado); ?>">
                    <input type="hidden" name="id_acessorio" id="idAcessorioEliminarBD" value="">

                    <button type="submit" class="btn btn-confirmar-remocao">
                        <i class="fa-solid fa-trash me-2"></i>
                        Remover Acessório
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

    const seletorEquipamento = $('seletorEquipamentoAcessoriosBD');
    const pesquisa = $('pesquisaAcessoriosBD');
    const btnLimpar = $('btnLimparPesquisaAcessoriosBD');
    const tabela = $('tabelaAcessoriosBD');

    const modalAcessorio = $('modalAcessorioBD');
    const form = $('formAcessorioBD');
    const tituloModal = $('modalAcessorioBDLabel');

    const periodicidadeManutencao = $('periodicidadeManutencaoBD');
    const periodicidadeCalibracao = $('periodicidadeCalibracaoBD');

    function setValue(id, valor) {
        const campo = $(id);
        if (campo) {
            campo.value = valor ?? '';
        }
    }

    function setText(id, valor) {
        const campo = $(id);
        if (campo) {
            campo.textContent = valor || '---';
        }
    }

    function setChecked(id, ativo) {
        const campo = $(id);
        if (campo) {
            campo.checked = Boolean(ativo);
        }
    }

    function normalizarTexto(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function filtrarTabela() {
        if (!tabela || !pesquisa) return;

        const termo = normalizarTexto(pesquisa.value);
        const linhas = tabela.querySelectorAll('tbody tr');

        linhas.forEach(function (linha) {
            if (linha.classList.contains('linha-sem-acessorios')) return;
            linha.classList.toggle('d-none', Boolean(termo) && !normalizarTexto(linha.textContent).includes(termo));
        });
    }

    function atualizarPeriodicidades() {
        const manutencaoSim = $('requerManutencaoSimBD')?.checked === true;
        const calibracaoSim = $('requerCalibracaoSimBD')?.checked === true;

        if (periodicidadeManutencao) {
            periodicidadeManutencao.disabled = !manutencaoSim;
            if (!manutencaoSim) periodicidadeManutencao.value = '';
        }

        if (periodicidadeCalibracao) {
            periodicidadeCalibracao.disabled = !calibracaoSim;
            if (!calibracaoSim) periodicidadeCalibracao.value = '';
        }
    }

    function prepararModalCriacao(botao) {
        if (form) form.reset();

        setValue('acaoAcessorioBD', 'criar');
        setValue('idAcessorioBD', '');
        setValue('codigoAcessorioBD', botao?.dataset.codigoPreview || 'Gerado automaticamente');

        const opcaoEquipamento = seletorEquipamento?.selectedOptions?.[0];
        setValue('idLocalizacaoAcessorioBD', opcaoEquipamento?.dataset.idLocalizacao || '');

        if (tituloModal) {
            tituloModal.innerHTML = '<i class="fa-solid fa-plug-circle-bolt me-2"></i>Adicionar Acessório';
        }

        setChecked('requerManutencaoNaoBD', true);
        setChecked('requerManutencaoSimBD', false);
        setChecked('requerCalibracaoNaoBD', true);
        setChecked('requerCalibracaoSimBD', false);
        atualizarPeriodicidades();
    }

    function prepararModalEdicao(botao) {
        if (form) form.reset();
        if (!botao) return;

        setValue('acaoAcessorioBD', 'editar');
        setValue('idAcessorioBD', botao.dataset.idAcessorio || '');
        setValue('codigoAcessorioBD', botao.dataset.codigo || '---');
        setValue('designacaoAcessorioBD', botao.dataset.designacao || '');
        setValue('tipoAcessorioBD', botao.dataset.tipo || '');
        setValue('fabricanteAcessorioBD', botao.dataset.fabricante || '');
        setValue('modeloAcessorioBD', botao.dataset.modelo || '');
        setValue('numeroSerieAcessorioBD', botao.dataset.numeroSerie || '');
        setValue('idLocalizacaoAcessorioBD', botao.dataset.idLocalizacao || '');
        setValue('estadoAcessorioBD', botao.dataset.estado || 'ativo');
        setValue('idFornecedorGarantiaBD', botao.dataset.idFornecedorGarantia || '');
        setValue('dataInicioGarantiaBD', botao.dataset.dataInicioGarantia || '');
        setValue('dataFimGarantiaBD', botao.dataset.dataFimGarantia || '');
        setValue('observacoesAcessorioBD', botao.dataset.observacoes || '');

        const requerManutencao = botao.dataset.requerManutencao === '1';
        const requerCalibracao = botao.dataset.requerCalibracao === '1';

        setChecked('requerManutencaoSimBD', requerManutencao);
        setChecked('requerManutencaoNaoBD', !requerManutencao);
        setChecked('requerCalibracaoSimBD', requerCalibracao);
        setChecked('requerCalibracaoNaoBD', !requerCalibracao);

        atualizarPeriodicidades();

        if (periodicidadeManutencao && requerManutencao) {
            periodicidadeManutencao.value = botao.dataset.periodicidadeManutencao || '';
        }

        if (periodicidadeCalibracao && requerCalibracao) {
            periodicidadeCalibracao.value = botao.dataset.periodicidadeCalibracao || '';
        }

        if (tituloModal) {
            tituloModal.innerHTML = '<i class="fa-solid fa-file-pen me-2"></i>Editar Acessório';
        }
    }

    if (modalAcessorio) {
        modalAcessorio.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;

            if (botao && botao.classList.contains('btn-editar-acessorio-bd')) {
                prepararModalEdicao(botao);
            } else {
                prepararModalCriacao(botao);
            }
        });
    }

    if (seletorEquipamento) {
        seletorEquipamento.addEventListener('change', function () {
            if (this.value) {
                window.location.href = 'acessorios.php?id_equipamento=' + encodeURIComponent(this.value);
            }
        });
    }

    if (pesquisa) {
        pesquisa.addEventListener('input', filtrarTabela);
    }

    if (btnLimpar) {
        btnLimpar.addEventListener('click', function () {
            if (pesquisa) pesquisa.value = '';
            filtrarTabela();
        });
    }

    document.querySelectorAll('input[name="requerManutencao"]').forEach(function (campo) {
        campo.addEventListener('change', atualizarPeriodicidades);
    });

    document.querySelectorAll('input[name="requerCalibracao"]').forEach(function (campo) {
        campo.addEventListener('change', atualizarPeriodicidades);
    });

    const modalEliminar = $('modalEliminarAcessorioBD');

    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            if (!botao) return;

            setValue('idAcessorioEliminarBD', botao.dataset.idAcessorio || '');
            setText('modalEliminarAcessorioCodigoBD', botao.dataset.codigo || '---');
            setText('modalEliminarAcessorioNomeBD', botao.dataset.designacao || '---');
            setText('modalEliminarAcessorioTipoBD', botao.dataset.tipo || '---');
            setText('modalEliminarAcessorioSerieBD', botao.dataset.serie || '---');
            setText('modalEliminarAcessorioEstadoBD', botao.dataset.estado || '---');
        });
    }

    atualizarPeriodicidades();
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
