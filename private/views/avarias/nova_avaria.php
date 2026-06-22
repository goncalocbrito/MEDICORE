<?php
require_once __DIR__ . '/../../includes/funcoes.php';

redirect_if_not_logged();

/* =========================================================
   NOVA AVARIA
   Permite ao engenheiro reportar uma avaria num equipamento
   ou num acessório associado ao equipamento.
   ========================================================= */

if (($_SESSION['tipo_utilizador'] ?? '') !== 'Engenheiro') {
    header('Location: ' . rota_inicial_utilizador());
    exit;
}

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function gerar_codigo_avaria(PDO $pdo)
{
    $prefixo = 'AVA-' . date('Ymd') . '-';

    $stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 AS proximo
        FROM avarias_reportadas
        WHERE codigo_avaria LIKE :prefixo
    ");

    $stmt->execute([
        ':prefixo' => $prefixo . '%'
    ]);

    $proximo = (int) ($stmt->fetchColumn() ?: 1);

    return $prefixo . str_pad((string) $proximo, 3, '0', STR_PAD_LEFT);
}

$pdo = medicore_pdo();

$mensagemErro = '';
$equipamentos = [];
$acessorios = [];

try {
    $stmtEquipamentos = $pdo->query("
        SELECT
            e.id_equipamento,
            e.codigo_equipamento,
            e.designacao,
            COALESCE(l.codigo, 'Sem localização') AS localizacao_atual
        FROM equipamentos e
        LEFT JOIN localizacoes l
            ON l.id_localizacao = e.id_localizacao
        WHERE e.isActive = 1
        ORDER BY e.codigo_equipamento ASC
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
} catch (Throwable $e) {
    $mensagemErro = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEquipamento = (int) ($_POST['id_equipamento'] ?? 0);
    $idAcessorio = trim($_POST['id_acessorio'] ?? '') !== ''
        ? (int) $_POST['id_acessorio']
        : null;
    $descricaoAvaria = trim($_POST['descricao_avaria'] ?? '');

    if ($idEquipamento <= 0) {
        $mensagemErro = 'Selecione o equipamento onde ocorreu a avaria.';
    } elseif ($descricaoAvaria === '') {
        $mensagemErro = 'Indique o motivo/descrição da avaria.';
    } else {
        try {
            $codigoAvaria = gerar_codigo_avaria($pdo);

            $stmt = $pdo->prepare("
                INSERT INTO avarias_reportadas (
                    codigo_avaria,
                    id_equipamento,
                    id_acessorio,
                    id_utilizador_reportou,
                    descricao_avaria,
                    estado,
                    data_reporte,
                    isActive
                ) VALUES (
                    :codigo_avaria,
                    :id_equipamento,
                    :id_acessorio,
                    :id_utilizador_reportou,
                    :descricao_avaria,
                    'reportada',
                    NOW(),
                    1
                )
            ");

            $stmt->execute([
                ':codigo_avaria' => $codigoAvaria,
                ':id_equipamento' => $idEquipamento,
                ':id_acessorio' => $idAcessorio,
                ':id_utilizador_reportou' => $_SESSION['id_utilizador'],
                ':descricao_avaria' => $descricaoAvaria
            ]);

            if ($idAcessorio !== null) {
                $stmtEstado = $pdo->prepare("UPDATE acessorios_equipamento SET estado = 'avariado' WHERE id_acessorio = :id");
                $stmtEstado->execute([':id' => $idAcessorio]);
            } else {
                $stmtEstado = $pdo->prepare("UPDATE equipamentos SET estado = 'avariado' WHERE id_equipamento = :id");
                $stmtEstado->execute([':id' => $idEquipamento]);
            }

            header('Location: lista_avarias.php?criada=1');
            exit;
        } catch (Throwable $e) {
            $mensagemErro = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h2 class="titulo-pagina">Reportar Avaria</h2>
            <p class="subtitulo-pagina">
                Registe uma avaria num equipamento ou num acessório associado.
            </p>
        </div>

        <div class="d-flex gap-2 flex-shrink-0">
            <a href="lista_avarias.php" class="btn btn-voltar">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Voltar à Lista
            </a>

            <button type="submit" form="formNovaAvaria" class="btn btn-guardar">
                <i class="fa-solid fa-floppy-disk me-2"></i>
                Guardar Avaria
            </button>
        </div>
    </div>

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger">
            <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Erro</strong>
            <ul class="mb-0 mt-1"><li><?php echo h($mensagemErro); ?></li></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="nova_avaria.php" class="formulario-ficha" id="formNovaAvaria" novalidate>
        <div class="tabela-container">
            <div class="ficha-card-conteudo">
                <section>
                    <div class="secao-ficha-titulo">
                        <h4>Dados da Avaria</h4>
                        <p>Selecione o equipamento, indique se a avaria pertence a um acessório e descreva o problema identificado.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="pesquisaEquipamentoAvaria" class="form-label">Equipamento *</label>

                            <div class="campo-pesquisa-registo">
                                <input type="text"
                                       class="form-control pesquisa-registo-custom"
                                       id="pesquisaEquipamentoAvaria"
                                       data-hidden-target="idEquipamentoAvaria"
                                       data-lista-target="listaEquipamentosAvaria"
                                       data-localizacao-target="localizacaoAtualAvaria"
                                       data-filtra-lista="listaAcessoriosAvaria"
                                       data-filtra-campo="equipamento"
                                       placeholder="Pesquisar e selecionar equipamento"
                                       autocomplete="off"
                                       required>

                                <input type="hidden"
                                       id="idEquipamentoAvaria"
                                       name="id_equipamento"
                                       required>

                                <div class="lista-registos-custom" id="listaEquipamentosAvaria">
                                    <?php foreach ($equipamentos as $equipamento): ?>
                                        <button type="button"
                                                class="opcao-registo-custom"
                                                data-id="<?php echo h($equipamento['id_equipamento']); ?>"
                                                data-texto="<?php echo h($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>"
                                                data-localizacao-atual="<?php echo h($equipamento['localizacao_atual']); ?>">
                                            <span>
                                                <?php echo h($equipamento['codigo_equipamento'] . ' - ' . $equipamento['designacao']); ?>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="localizacao-atual-box mt-2 d-none" id="localizacaoAtualAvaria">
                                <span>Localização atual</span>
                                <strong></strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="pesquisaAcessorioAvaria" class="form-label">Acessório associado</label>

                            <input type="text"
                                   class="form-control pesquisa-checkbox-custom"
                                   id="pesquisaAcessorioAvaria"
                                   data-lista-target="listaAcessoriosAvaria"
                                   placeholder="Pesquisar acessório do equipamento"
                                   autocomplete="off">

                            <div class="lista-checkbox-custom mt-2" id="listaAcessoriosAvaria">
                                <?php foreach ($acessorios as $acessorio): ?>
                                    <div class="opcao-checkbox-custom"
                                         data-equipamento="<?php echo h($acessorio['id_equipamento']); ?>"
                                         data-texto="<?php echo h($acessorio['codigo_acessorio'] . ' - ' . $acessorio['designacao']); ?>"
                                         data-visivel-filtro-pai="0"
                                         hidden>
                                        <label>
                                            <input type="radio"
                                                   name="id_acessorio"
                                                   value="<?php echo h($acessorio['id_acessorio']); ?>">
                                            <span>
                                                <?php echo h($acessorio['codigo_acessorio'] . ' - ' . $acessorio['designacao']); ?>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <small class="text-muted">
                                Deixe vazio se a avaria for do equipamento principal.
                            </small>
                        </div>

                        <div class="col-12">
                            <label for="descricaoAvaria" class="form-label">Motivo / descrição da avaria *</label>
                            <textarea id="descricaoAvaria"
                                      name="descricao_avaria"
                                      class="form-control"
                                      rows="5"
                                      maxlength="500"
                                      placeholder="Descreva o problema observado, sintomas, mensagens de erro ou contexto da avaria."
                                      required><?php echo h($_POST['descricao_avaria'] ?? ''); ?></textarea>
                            <small class="texto-ajuda-form contador-caracteres" data-target="descricaoAvaria" data-max="500">0 / 500 caracteres</small>
                        </div>
                    </div>
                </section>
            </div>
        </div>

    </form>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>