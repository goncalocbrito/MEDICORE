<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

require_once __DIR__ . '/../../../config/config.php';

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$stmt = $pdo->prepare("
    SELECT *
    FROM localizacoes
    WHERE isActive = 1
    ORDER BY id_localizacao ASC
");

$stmt->execute();
$localizacoes = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>


    <!-- Conteúdo principal da lista de localizações. -->
    <main class="conteudo-private">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Gestão de Localizações</h2>
                <p class="subtitulo-pagina">
                    Consulta, registo e acompanhamento das localizações hospitalares associadas aos equipamentos médicos.
                </p>
            </div>
            <a href="nova_localizacao.php" class="btn btn-adicionar">
                <i class="fa-solid fa-plus me-2"></i> Adicionar Localização
            </a>
        </div>
        <!-- Tabela principal. Cada linha abre ficha ou modal de remoção. -->
        <!-- Pesquisa e filtros da tabela de localizações. -->
        <div class="table-responsive tabela-container">
            <table id="tabela-localizacoes" class="table table-hover align-middle tabela-localizacoes tabela-datatables-medicore">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Departamento / Serviço</th>
                        <th>Edifício</th>
                        <th>Piso</th>
                        <th>Sala</th>
                        <th>Estado</th>
                        <th>Equipamentos</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($localizacoes)): ?>
                        <?php foreach ($localizacoes as $localizacao): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($localizacao['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($localizacao['departamento_nome']); ?></td>
                                <td><?php echo htmlspecialchars($localizacao['edificio']); ?></td>
                                <td><?php echo htmlspecialchars($localizacao['piso']); ?></td>
                                <td><?php echo htmlspecialchars($localizacao['sala']); ?></td>
                                <td>
                                    <span class="estado <?php echo $localizacao['estado'] === 'Ativa' ? 'estado-ativo' : ($localizacao['estado'] === 'Em manutenção' ? 'estado-manutencao' : 'estado-inativo'); ?>">
                                        <?php echo htmlspecialchars($localizacao['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($localizacao['capacidade_equipamentos'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <a href="ficha_localizacao.php?id=<?php echo urlencode($localizacao['id_localizacao']); ?>"
                                    class="btn btn-sm btn-ficha"
                                    title="Abrir ficha da localização">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-sm btn-eliminar btn-abrir-modal-apagar-localizacao"
                                            title="Eliminar localização"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalApagarLocalizacao"
                                            data-id="<?php echo htmlspecialchars($localizacao['id_localizacao']); ?>"
                                            data-codigo="<?php echo htmlspecialchars($localizacao['codigo']); ?>"
                                            data-departamento="<?php echo htmlspecialchars($localizacao['departamento_nome']); ?>"
                                            data-edificio="<?php echo htmlspecialchars($localizacao['edificio']); ?>"
                                            data-piso="<?php echo htmlspecialchars($localizacao['piso']); ?>"
                                            data-sala="<?php echo htmlspecialchars($localizacao['sala']); ?>"
                                            data-tipo="<?php echo htmlspecialchars($localizacao['tipo_espaco']); ?>"
                                            data-estado="<?php echo htmlspecialchars($localizacao['estado']); ?>"
                                            data-equipamentos="<?php echo htmlspecialchars($localizacao['capacidade_equipamentos'] ?? '-'); ?>">
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
    <!-- Modal de confirmação de remoção da localização. -->
    <div class="modal fade" id="modalApagarLocalizacao" tabindex="-1" aria-labelledby="modalApagarLocalizacaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-remocao-dialog">
            <div class="modal-content modal-apagar-equipamento">
                <div class="modal-header modal-remocao-header">
                    <div>
                        <h5 class="modal-title" id="modalApagarLocalizacaoLabel">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Confirmar remoção
                        </h5>
                        <p class="modal-remocao-subtitulo">Confirme os dados antes de remover a localização.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body modal-remocao-body">
                    <div class="modal-resumo-equipamento modal-resumo-remocao">
                        <div class="modal-linha"><strong>Código</strong><span id="modalApagarLocalizacaoCodigo">---</span></div>
                        <div class="modal-linha"><strong>Departamento</strong><span id="modalApagarLocalizacaoDepartamento">---</span></div>
                        <div class="modal-linha"><strong>Edifício</strong><span id="modalApagarLocalizacaoEdificio">---</span></div>
                        <div class="modal-linha"><strong>Piso</strong><span id="modalApagarLocalizacaoPiso">---</span></div>
                        <div class="modal-linha"><strong>Sala</strong><span id="modalApagarLocalizacaoSala">---</span></div>
                        <div class="modal-linha"><strong>Tipo</strong><span id="modalApagarLocalizacaoTipo">---</span></div>
                        <div class="modal-linha"><strong>Responsável</strong><span id="modalApagarLocalizacaoResponsavel">---</span></div>
                        <div class="modal-linha"><strong>Estado</strong><span id="modalApagarLocalizacaoEstado">---</span></div>
                        <div class="modal-linha"><strong>Equipamentos</strong><span id="modalApagarLocalizacaoEquipamentos">---</span></div>
                    </div>
                    <input type="hidden" id="modalApagarIdLocalizacao">
                    <p class="texto-confirmacao-remocao">
                        Confirma que pretende remover esta localização da lista?
                    </p>
                </div>
                <div class="modal-footer modal-remocao-footer">
                    <button type="button" class="btn btn-cancelar-modal" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-confirmar-remocao" id="btnConfirmarApagarLocalizacao">
                        <i class="fa-solid fa-trash me-2"></i> Guardar Alteração
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
