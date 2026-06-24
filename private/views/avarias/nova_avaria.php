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

    // Agrupar acessórios por equipamento para o JS
    $acessoriosPorEquipamento = [];
    foreach ($acessorios as $ac) {
        $acessoriosPorEquipamento[$ac['id_equipamento']][] = $ac;
    }
} catch (Throwable $e) {
    $mensagemErro = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEquipamento   = (int) ($_POST['id_equipamento'] ?? 0);
    $idsAcessorios   = array_filter(array_map('intval', (array) ($_POST['id_acessorio'] ?? [])));
    $descricaoAvaria = trim($_POST['descricao_avaria'] ?? '');

    if ($idEquipamento <= 0) {
        $mensagemErro = 'Selecione o equipamento onde ocorreu a avaria.';
    } elseif ($descricaoAvaria === '') {
        $mensagemErro = 'Indique o motivo/descrição da avaria.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmtInsert = $pdo->prepare("
                INSERT INTO avarias_reportadas (
                    codigo_avaria, id_equipamento, id_acessorio,
                    id_utilizador_reportou, descricao_avaria,
                    estado, data_reporte, isActive
                ) VALUES (
                    :codigo_avaria, :id_equipamento, :id_acessorio,
                    :id_utilizador_reportou, :descricao_avaria,
                    'reportada', NOW(), 1
                )
            ");

            if (empty($idsAcessorios)) {
                // Avaria no equipamento principal
                $stmtInsert->execute([
                    ':codigo_avaria'          => gerar_codigo_avaria($pdo),
                    ':id_equipamento'         => $idEquipamento,
                    ':id_acessorio'           => null,
                    ':id_utilizador_reportou' => $_SESSION['id_utilizador'],
                    ':descricao_avaria'       => $descricaoAvaria,
                ]);
                $pdo->prepare("UPDATE equipamentos SET estado = 'avariado' WHERE id_equipamento = :id")
                    ->execute([':id' => $idEquipamento]);
            } else {
                // Uma avaria por acessório selecionado
                $stmtEstadoAc = $pdo->prepare("UPDATE acessorios_equipamento SET estado = 'avariado' WHERE id_acessorio = :id");
                foreach ($idsAcessorios as $idAc) {
                    $stmtInsert->execute([
                        ':codigo_avaria'          => gerar_codigo_avaria($pdo),
                        ':id_equipamento'         => $idEquipamento,
                        ':id_acessorio'           => $idAc,
                        ':id_utilizador_reportou' => $_SESSION['id_utilizador'],
                        ':descricao_avaria'       => $descricaoAvaria,
                    ]);
                    $stmtEstadoAc->execute([':id' => $idAc]);
                }
            }

            $pdo->commit();
            header('Location: lista_avarias.php?criada=1');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
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
                                       data-filtra-acessorios-avaria="listaAcessoriosAvaria"
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
                            <label class="form-label">Acessórios avariados</label>

                            <div id="avisoSelecionarEquipamentoAvaria" class="text-muted" style="font-size:0.9rem; margin-top:6px;">
                                Selecione primeiro um equipamento.
                            </div>
                            <div id="avisoSemAcessoriosAvaria" class="text-muted d-none" style="font-size:0.9rem; margin-top:6px;">
                                Este equipamento não tem acessórios.
                            </div>

                            <div class="lista-selecao-equipamento d-none" id="listaAcessoriosAvaria">
                                <?php foreach ($acessorios as $acessorio): ?>
                                    <div class="opcao-selecao-equipamento"
                                         data-equipamento="<?php echo h($acessorio['id_equipamento']); ?>"
                                         style="display:none;">
                                        <label class="selecao-equipamento-label">
                                            <input type="checkbox"
                                                   name="id_acessorio[]"
                                                   value="<?php echo h($acessorio['id_acessorio']); ?>">
                                            <?php echo h($acessorio['codigo_acessorio'] . ' - ' . $acessorio['designacao']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <small class="text-muted mt-1 d-block">
                                Deixe sem seleção se a avaria for do equipamento principal.
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

<script>
(function () {
    const listaAcessorios    = document.getElementById('listaAcessoriosAvaria');
    const avisoSelecionar    = document.getElementById('avisoSelecionarEquipamentoAvaria');
    const avisoSemAcessorios = document.getElementById('avisoSemAcessoriosAvaria');

    function filtrarAcessoriosPorEquipamento(idEquipamento) {
        const itens = listaAcessorios ? listaAcessorios.querySelectorAll('.opcao-selecao-equipamento') : [];

        itens.forEach(function (item) {
            var cb = item.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = false;
            item.style.display = 'none';
        });

        if (!idEquipamento) {
            if (listaAcessorios) listaAcessorios.classList.add('d-none');
            if (avisoSelecionar) avisoSelecionar.classList.remove('d-none');
            if (avisoSemAcessorios) avisoSemAcessorios.classList.add('d-none');
            return;
        }

        if (avisoSelecionar) avisoSelecionar.classList.add('d-none');

        var visiveis = 0;
        itens.forEach(function (item) {
            if (item.getAttribute('data-equipamento') === String(idEquipamento)) {
                item.style.display = '';
                visiveis++;
            }
        });

        if (listaAcessorios) listaAcessorios.classList.toggle('d-none', visiveis === 0);
        if (avisoSemAcessorios) avisoSemAcessorios.classList.toggle('d-none', visiveis > 0);
    }

    // Interceta o click nas opções do campo de pesquisa de equipamento
    document.addEventListener('click', function (e) {
        var opcao = e.target.closest('#listaEquipamentosAvaria .opcao-registo-custom');
        if (!opcao) return;
        setTimeout(function () {
            var hidden = document.getElementById('idEquipamentoAvaria');
            filtrarAcessoriosPorEquipamento(hidden ? hidden.value : '');
        }, 0);
    });
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>