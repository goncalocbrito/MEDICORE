<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_once __DIR__ . '/../../../config/config.php';

// ---------------------------------------------------------
// Ligação à base de dados
// ---------------------------------------------------------
$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ---------------------------------------------------------
// Carregar configuração geral
// ---------------------------------------------------------
$config = $pdo->query('SELECT * FROM pagina_publica_config LIMIT 1')->fetch(PDO::FETCH_ASSOC);

// ---------------------------------------------------------
// Carregar slides
// ---------------------------------------------------------
$slides = $pdo->query('SELECT * FROM pagina_publica_slides ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------------------
// Carregar hospitais (todos, incluindo inativos, para gestão)
// ---------------------------------------------------------
$hospitais = $pdo->query('SELECT * FROM pagina_publica_hospitais ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

    <main class="conteudo-private ficha-equipamento-page backoffice-publico-page">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="titulo-pagina">Backoffice da Página Pública</h2>
                <p class="subtitulo-pagina">
                    Edite textos, imagens e cartões apresentados na página pública <strong>index.php</strong>.
                </p>
            </div>

            <div class="form-actions backoffice-actions">
                <button type="button" class="btn btn-limpar" data-bs-toggle="modal" data-bs-target="#modalPreview">
                    <i class="fa-solid fa-eye me-2"></i> Pré-visualizar
                </button>
                <button type="submit" class="btn btn-guardar" form="formBackofficePublico">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Conteúdos
                </button>
            </div>
        </div>

        <?php if (!empty($_GET['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> Conteúdos atualizados com sucesso.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Erro ao guardar:</strong> <?php echo htmlspecialchars($_GET['erro']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- =====================================================
             FORMULÁRIO PRINCIPAL
             ===================================================== -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formBackofficePublico"
              action="processa_backoffice_publico.php"
              method="post"
              enctype="multipart/form-data">

            <input type="hidden" name="acao" value="atualizar_pagina_publica">

            <div class="ficha-area">
                <ul class="nav nav-tabs ficha-tabs" id="tabsBackofficePublico" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#navbar" type="button" role="tab">
                            <i class="fa-solid fa-bars me-2"></i> Navbar
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sobre-publico" type="button" role="tab">
                            <i class="fa-solid fa-image me-2"></i> Sobre e Slides
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#hospitais-publicos" type="button" role="tab">
                            <i class="fa-solid fa-hospital me-2"></i> Hospitais e Clínicas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contactos-publicos" type="button" role="tab">
                            <i class="fa-solid fa-address-book me-2"></i> Contactos e Rodapé
                        </button>
                    </li>
                </ul>

                <div class="tab-content ficha-tab-content" id="tabsBackofficePublicoConteudo">

                    <!-- =========================================
                         NAVBAR
                         ========================================= -->
                    <div class="tab-pane fade show active" id="navbar" role="tabpanel">
                        <h3 class="secao-ficha-titulo">Navegação pública</h3>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <input type="hidden" name="navbar_logo" value="<?= htmlspecialchars($config['navbar_logo']) ?>">
                                <label class="form-label">Logótipo (ficheiro)</label>
                                <input type="file" class="form-control" name="navbar_logo_ficheiro" accept=".png,.jpg,.jpeg,.webp,.svg">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Botão "Área Restrita"</label>
                                <input type="text" class="form-control campo-preview-publico" name="navbar_btn_restrita"
                                       value="<?= htmlspecialchars($config['navbar_btn_restrita']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Link 1</label>
                                <input type="text" class="form-control" name="navbar_link_sobre"
                                       value="<?= htmlspecialchars($config['navbar_link_sobre']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Link 2</label>
                                <input type="text" class="form-control" name="navbar_link_equipa"
                                       value="<?= htmlspecialchars($config['navbar_link_equipa']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Link 3</label>
                                <input type="text" class="form-control" name="navbar_link_funcional"
                                       value="<?= htmlspecialchars($config['navbar_link_funcional']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Link 4</label>
                                <input type="text" class="form-control" name="navbar_link_hospitais"
                                       value="<?= htmlspecialchars($config['navbar_link_hospitais']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Link 5</label>
                                <input type="text" class="form-control" name="navbar_link_contacto"
                                       value="<?= htmlspecialchars($config['navbar_link_contacto']) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SOBRE E SLIDES
                         ========================================= -->
                    <div class="tab-pane fade" id="sobre-publico" role="tabpanel">
                        <h3 class="secao-ficha-titulo">Sobre e carrossel principal</h3>

                        <div class="row g-4 mb-4">
                            <div class="col-md-5">
                                <label class="form-label">Título principal</label>
                                <input type="text" class="form-control campo-preview-publico" name="sobre_titulo"
                                       value="<?= htmlspecialchars($config['sobre_titulo']) ?>">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Texto introdutório</label>
                                <textarea class="form-control campo-preview-publico" name="sobre_texto" rows="3"><?= htmlspecialchars($config['sobre_texto']) ?></textarea>
                            </div>
                        </div>

                        <h4 class="secao-ficha-titulo mt-2">Slides do carrossel</h4>
                        <div class="backoffice-grid-cards">
                            <?php foreach ($slides as $i => $slide): ?>
                            <div class="backoffice-editor-card">
                                <h4>Slide <?= $i + 1 ?></h4>
                                <input type="hidden" name="slide_id[]"     value="<?= $slide['id_slide'] ?>">
                                <input type="hidden" name="slide_imagem[]" value="<?= htmlspecialchars($slide['imagem']) ?>">

                                <label class="form-label">Imagem (ficheiro)</label>
                                <input type="file" class="form-control mb-2" name="slide_ficheiro_<?= $slide['id_slide'] ?>" accept=".png,.jpg,.jpeg,.webp">

                                <label class="form-label">Título</label>
                                <input type="text" class="form-control mb-2" name="slide_titulo[]"
                                       value="<?= htmlspecialchars($slide['titulo']) ?>">

                                <label class="form-label">Descrição</label>
                                <textarea class="form-control" name="slide_descricao[]" rows="2"><?= htmlspecialchars($slide['descricao']) ?></textarea>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- =========================================
                         HOSPITAIS E CLÍNICAS (dinâmico)
                         ========================================= -->
                    <div class="tab-pane fade" id="hospitais-publicos" role="tabpanel">
                        <h3 class="secao-ficha-titulo">Hospitais e Clínicas</h3>
                        <p class="text-muted mb-4">
                            Adicione, edite ou remova os cartões apresentados na secção "Hospitais e Clínicas" da página pública.
                            A ordem é definida pelo campo <strong>Ordem</strong>.
                        </p>

                        <div id="hospitais-lista">
                            <?php foreach ($hospitais as $h): ?>
                            <div class="backoffice-editor-card hospital-card-editor mb-3" id="hospital-row-<?= $h['id_hospital'] ?>">
                                <input type="hidden" name="hospital_id[]"     value="<?= $h['id_hospital'] ?>">
                                <input type="hidden" name="hospital_ativo[]"  value="<?= $h['isActive'] ?>">

                                <div class="row g-3 align-items-start">
                                    <div class="col-md-1">
                                        <label class="form-label">Ordem</label>
                                        <input type="number" class="form-control" name="hospital_ordem[]"
                                               value="<?= (int)$h['ordem'] ?>" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Nome</label>
                                        <input type="text" class="form-control" name="hospital_nome[]"
                                               value="<?= htmlspecialchars($h['nome']) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Descrição</label>
                                        <textarea class="form-control" name="hospital_descricao[]" rows="2"><?= htmlspecialchars($h['descricao']) ?></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="hidden" name="hospital_imagem[]" value="<?= htmlspecialchars($h['imagem']) ?>">
                                        <label class="form-label">Imagem (ficheiro)</label>
                                        <input type="file" class="form-control" name="hospital_ficheiro_<?= $h['id_hospital'] ?>" accept=".png,.jpg,.jpeg,.webp">
                                    </div>
                                    <div class="col-md-1 d-flex flex-column gap-2 pt-4">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="hospital_visivel_<?= $h['id_hospital'] ?>"
                                                   id="visivel_<?= $h['id_hospital'] ?>" <?= $h['isActive'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="visivel_<?= $h['id_hospital'] ?>">Visível</label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger btn-remover-hospital"
                                                data-id="<?= $h['id_hospital'] ?>"
                                                title="Remover permanentemente">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Novos hospitais adicionados dinamicamente -->
                        <div id="hospitais-novos"></div>

                        <button type="button" class="btn btn-limpar mt-3" id="btn-adicionar-hospital">
                            <i class="fa-solid fa-plus me-2"></i> Adicionar Hospital / Clínica
                        </button>
                    </div>

                    <!-- =========================================
                         CONTACTOS E RODAPÉ
                         ========================================= -->
                    <div class="tab-pane fade" id="contactos-publicos" role="tabpanel">
                        <h3 class="secao-ficha-titulo">Contacto e rodapé</h3>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Texto da secção contacto</label>
                                <textarea class="form-control campo-preview-publico" name="contacto_texto" rows="3"><?= htmlspecialchars($config['contacto_texto']) ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Localização (rodapé)</label>
                                <textarea class="form-control" name="rodape_localizacao" rows="3"><?= htmlspecialchars($config['rodape_localizacao']) ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Horário semanal</label>
                                <input type="text" class="form-control" name="rodape_horario_semana"
                                       value="<?= htmlspecialchars($config['rodape_horario_semana']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control campo-preview-publico" name="rodape_email"
                                       value="<?= htmlspecialchars($config['rodape_email']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control campo-preview-publico" name="rodape_telefone"
                                       value="<?= htmlspecialchars($config['rodape_telefone']) ?>">
                            </div>
                        </div>
                    </div>

                </div><!-- /.tab-content -->
            </div><!-- /.ficha-area -->

            <!-- IDs de hospitais a remover (preenchido por JS) -->
            <input type="hidden" name="hospitais_remover" id="hospitais_remover" value="">

        </form>

    </main>

    <!-- Modal de Pré-visualização -->
    <div class="modal fade" id="modalPreview" tabindex="-1" aria-labelledby="modalPreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPreviewLabel"><i class="fa-solid fa-eye me-2"></i> Pré-visualização da Página Pública</h5>
                    <div class="ms-auto d-flex gap-2">
                        <a href="<?= BASE_URL ?>/public/index.php" target="_blank" class="btn btn-sm btn-limpar">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir em nova aba
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <iframe id="iframePreview" src="" style="width:100%;height:100%;border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

<script>
// ---------------------------------------------------------
// Modal de pré-visualização — carrega o iframe ao abrir
// ---------------------------------------------------------
document.getElementById('modalPreview').addEventListener('show.bs.modal', function () {
    const iframe = document.getElementById('iframePreview');
    if (!iframe.src || iframe.src === window.location.href) {
        iframe.src = '<?= BASE_URL ?>/public/index.php';
    }
});

// ---------------------------------------------------------
// Adicionar novo hospital dinamicamente
// ---------------------------------------------------------
let novoIdx = 0;
document.getElementById('btn-adicionar-hospital').addEventListener('click', function () {
    novoIdx++;
    const container = document.getElementById('hospitais-novos');
    const div = document.createElement('div');
    div.className = 'backoffice-editor-card hospital-card-editor mb-3';
    div.id = 'hospital-novo-' + novoIdx;
    div.innerHTML = `
        <input type="hidden" name="hospital_id[]"    value="0">
        <input type="hidden" name="hospital_ativo[]" value="1">
        <div class="row g-3 align-items-start">
            <div class="col-md-1">
                <label class="form-label">Ordem</label>
                <input type="number" class="form-control" name="hospital_ordem[]" value="${novoIdx + 10}" min="1">
            </div>
            <div class="col-md-3">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" name="hospital_nome[]" required placeholder="Nome do hospital / clínica">
            </div>
            <div class="col-md-4">
                <label class="form-label">Descrição</label>
                <textarea class="form-control" name="hospital_descricao[]" rows="2" placeholder="Breve descrição..."></textarea>
            </div>
            <div class="col-md-3">
                <input type="hidden" name="hospital_imagem[]" value="">
                <label class="form-label">Imagem (ficheiro)</label>
                <input type="file" class="form-control" name="hospital_ficheiro_novo_${novoIdx}" accept=".png,.jpg,.jpeg,.webp">
            </div>
            <div class="col-md-1 d-flex flex-column gap-2 pt-4">
                <div class="form-check form-switch mt-1">
                    <input class="form-check-input" type="checkbox" name="hospital_visivel_novo_${novoIdx}" id="visivel_novo_${novoIdx}" checked>
                    <label class="form-check-label" for="visivel_novo_${novoIdx}">Visível</label>
                </div>
                <button type="button" class="btn btn-sm btn-danger btn-cancelar-novo" data-alvo="hospital-novo-${novoIdx}" title="Cancelar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>`;
    container.appendChild(div);

    // Remover novo (cancelar)
    div.querySelector('.btn-cancelar-novo').addEventListener('click', function () {
        document.getElementById(this.dataset.alvo).remove();
    });
});

// ---------------------------------------------------------
// Remover hospital existente (marca para remoção via hidden input)
// ---------------------------------------------------------
const removidos = new Set();

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-remover-hospital');
    if (!btn) return;

    if (!confirm('Tem a certeza que pretende remover este hospital / clínica?')) return;

    const id  = btn.dataset.id;
    const row = document.getElementById('hospital-row-' + id);
    if (row) row.remove();

    removidos.add(id);
    document.getElementById('hospitais_remover').value = [...removidos].join(',');
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>