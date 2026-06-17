<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

/* =========================================================
   LISTA DE EQUIPAMENTOS - MEDICORE
   Compatível com a nova estrutura:
   equipamentos
   equipamentos_fornecedores
   fornecedores
   familias_equipamento
   localizacoes
   ========================================================= */

$equipamentos = [];
$erro_bd = '';

function h($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function textoEstadoEquipamento($estado)
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

function textoCriticidadeEquipamento($criticidade)
{
    $criticidades = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'critica' => 'Crítica'
    ];

    return $criticidades[$criticidade] ?? $criticidade;
}

function classeEstadoEquipamento($estado)
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

function valoresUnicos($array, $campo)
{
    $valores = [];

    foreach ($array as $linha) {
        if (!empty($linha[$campo])) {
            $valores[] = $linha[$campo];
        }
    }

    $valores = array_unique($valores);
    sort($valores);

    return $valores;
}

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
       REMOÇÃO LÓGICA DO EQUIPAMENTO
       O equipamento deixa de aparecer na lista, mas continua na BD.
       ========================================================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'apagar') {
        $idEquipamentoApagar = (int) ($_POST['id_equipamento'] ?? 0);

        if ($idEquipamentoApagar > 0) {
            $stmtApagar = $pdo->prepare("
                UPDATE equipamentos
                SET 
                    isActive = 0,
                    estado = 'inativo',
                    atualizado_por = :atualizado_por
                WHERE id_equipamento = :id_equipamento
            ");

            $stmtApagar->execute([
                ':id_equipamento' => $idEquipamentoApagar,
                ':atualizado_por' => $_SESSION['nome'] ?? $_SESSION['username'] ?? 'sistema'
            ]);
        }

        header('Location: lista_equipamentos.php?removido=1');
        exit;
    }

    /* =========================================================
       CONSULTA DOS EQUIPAMENTOS
       Nota:
       - fabricante vem da tabela fornecedores através de equipamentos_fornecedores
       - família vem de familias_equipamento
       - localização vem de localizacoes
       ========================================================= */
    $sql = "
        SELECT
            e.id_equipamento,
            e.codigo_equipamento,
            e.designacao,
            e.modelo,
            e.numero_serie,
            e.estado,
            e.criticidade,

            fe.nome AS familia,
            fe.codigo_familia,

            l.codigo AS codigo_localizacao,
            l.departamento_nome,
            l.departamento_sigla,
            l.edificio,
            l.piso,
            l.sala,

            f_fabricante.nome_empresa AS fabricante

        FROM equipamentos e

        INNER JOIN familias_equipamento fe
            ON fe.id_familia_equipamento = e.id_familia_equipamento

        INNER JOIN localizacoes l
            ON l.id_localizacao = e.id_localizacao

        LEFT JOIN equipamentos_fornecedores ef
            ON ef.id_equipamento = e.id_equipamento
            AND ef.isActive = 1

        LEFT JOIN fornecedores f_fabricante
            ON f_fabricante.id_fornecedor = ef.id_fornecedor_fabricante

        WHERE e.isActive = 1

        ORDER BY e.codigo_equipamento ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $equipamentos = $stmt->fetchAll();

} catch (PDOException $e) {
    $erro_bd = 'Erro ao carregar equipamentos da base de dados.';
}

$familiasFiltro = valoresUnicos($equipamentos, 'familia');
$localizacoesFiltro = valoresUnicos($equipamentos, 'departamento_sigla');
$estadosFiltro = valoresUnicos($equipamentos, 'estado');
$criticidadesFiltro = valoresUnicos($equipamentos, 'criticidade');

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="titulo-pagina">Gestão de Equipamentos</h2>
            <p class="subtitulo-pagina">
                Consulta, registo e acompanhamento dos equipamentos médicos hospitalares.
            </p>
        </div>

        <a href="novo_equipamento.php" class="btn btn-adicionar">
            <i class="fa-solid fa-plus me-2"></i> Adicionar Equipamento
        </a>
    </div>

    <?php if (isset($_GET['removido'])): ?>
        <div class="alert alert-success mb-4">
            <i class="fa-solid fa-check-circle me-2"></i>
            Equipamento removido da lista com sucesso.
        </div>
    <?php endif; ?>

    <!-- Pesquisa e filtros da tabela de equipamentos -->
    <div class="table-responsive tabela-container">
        <table id="tabela-equipamentos" class="table table-hover align-middle tabela-equipamentos tabela-datatables-medicore">

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Equipamento</th>
                    <th>Família</th>
                    <th>Modelo</th>
                    <th>Localização</th>
                    <th>Estado</th>
                    <th>Criticidade</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($erro_bd) && !empty($equipamentos)): ?>

                    <?php foreach ($equipamentos as $equipamento): ?>

                        <?php
                            $localizacaoCompleta = 
                                ($equipamento['codigo_localizacao'] ?? '') . ' | ' .
                                ($equipamento['departamento_sigla'] ?? '') . ' - Sala ' .
                                ($equipamento['sala'] ?? '');

                            $fabricante = $equipamento['fabricante'] ?: '---';
                            $estadoTexto = textoEstadoEquipamento($equipamento['estado']);
                            $criticidadeTexto = textoCriticidadeEquipamento($equipamento['criticidade']);
                        ?>

                        <tr>
                            <td>
                                <?php echo h($equipamento['codigo_equipamento']); ?>
                            </td>

                            <td>
                                <?php echo h($equipamento['designacao']); ?>
                            </td>

                            <td>
                                <?php echo h($equipamento['familia']); ?>
                            </td>

                            <td>
                                <?php echo h($equipamento['modelo']); ?>
                            </td>

                            <td>
                                <?php echo h($localizacaoCompleta); ?>
                            </td>

                            <td>
                                <span class="estado <?php echo h(classeEstadoEquipamento($equipamento['estado'])); ?>">
                                    <?php echo h($estadoTexto); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo h($criticidadeTexto); ?>
                            </td>

                            <td class="text-center">

                                <a 
                                    href="ficha_equipamento.php?id=<?php echo urlencode($equipamento['id_equipamento']); ?>"
                                    class="btn btn-sm btn-ficha"
                                    title="Abrir ficha do equipamento">
                                    <i class="fa-solid fa-file-lines"></i>
                                </a>

                                <button 
                                    type="button"
                                    class="btn btn-sm btn-eliminar btn-abrir-modal-apagar"
                                    title="Eliminar equipamento"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApagarEquipamento"
                                    data-id="<?php echo h($equipamento['id_equipamento']); ?>"
                                    data-codigo="<?php echo h($equipamento['codigo_equipamento']); ?>"
                                    data-nome="<?php echo h($equipamento['designacao']); ?>"
                                    data-categoria="<?php echo h($equipamento['familia']); ?>"
                                    data-fabricante="<?php echo h($fabricante); ?>"
                                    data-modelo="<?php echo h($equipamento['modelo']); ?>"
                                    data-serie="<?php echo h($equipamento['numero_serie']); ?>"
                                    data-localizacao="<?php echo h($localizacaoCompleta); ?>"
                                    data-estado="<?php echo h($estadoTexto); ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>

        </table>
    </div>

</main>

<!-- =========================================================
     MODAL PARA CONFIRMAR REMOÇÃO DO EQUIPAMENTO
     ========================================================= -->
<div 
    class="modal fade"
    id="modalApagarEquipamento"
    tabindex="-1"
    aria-labelledby="modalApagarEquipamentoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
        <div class="modal-content modal-apagar-equipamento">

            <div class="modal-header modal-remocao-header">
                <div>
                    <h5 class="modal-title" id="modalApagarEquipamentoLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Confirmar remoção
                    </h5>
                    <p class="modal-remocao-subtitulo">
                        Confirme os dados antes de remover o equipamento.
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
                        <strong>Código</strong>
                        <span id="modalApagarCodigo">---</span>
                    </div>

                    <div class="modal-linha">
                        <strong>Equipamento</strong>
                        <span id="modalApagarNome">---</span>
                    </div>

                    <div class="modal-linha">
                        <strong>Família</strong>
                        <span id="modalApagarCategoria">---</span>
                    </div>

                    <div class="modal-linha">
                        <strong>Fabricante</strong>
                        <span id="modalApagarFabricante">---</span>
                    </div>

                    <div class="modal-linha">
                        <strong>Modelo</strong>
                        <span id="modalApagarModelo">---</span>
                    </div>

                    <div class="modal-linha">
                        <strong>N.º Série</strong>
                        <span id="modalApagarSerie">---</span>
                    </div>

                    <div class="modal-linha">
                        <strong>Localização</strong>
                        <span id="modalApagarLocalizacao">---</span>
                    </div>

                    <div class="modal-linha">
                        <strong>Estado</strong>
                        <span id="modalApagarEstado">---</span>
                    </div>

                </div>

                <p class="texto-confirmacao-remocao">
                    Confirma que pretende remover este equipamento da lista?
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

                <form action="lista_equipamentos.php" method="post">
                    <input type="hidden" name="acao" value="apagar">
                    <input type="hidden" name="id_equipamento" id="modalApagarIdEquipamento">

                    <button 
                        type="submit"
                        class="btn btn-confirmar-remocao"
                        id="btnConfirmarApagarEquipamento">
                        <i class="fa-solid fa-trash me-2"></i>
                        Guardar Alteração
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
