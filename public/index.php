<?php
// ---------------------------------------------------------
// Ligação à base de dados para carregar conteúdos dinâmicos
// ---------------------------------------------------------
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $config   = $pdo->query('SELECT * FROM pagina_publica_config LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $slides   = $pdo->query('SELECT * FROM pagina_publica_slides   WHERE isActive = 1 ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);
    $hospitais = $pdo->query('SELECT * FROM pagina_publica_hospitais WHERE isActive = 1 ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    // Se a BD falhar, usa valores de fallback para não quebrar a página
    $config = [
        'navbar_logo'           => 'assets/img/MEDICORE_logotipo_branco.png',
        'navbar_link_sobre'     => 'Sobre',
        'navbar_link_equipa'    => 'Nossa Equipa',
        'navbar_link_funcional' => 'Funcionalidades',
        'navbar_link_hospitais' => 'Hospitais e Clínicas',
        'navbar_link_contacto'  => 'Contacto',
        'navbar_btn_restrita'   => 'Área Restrita',
        'sobre_titulo'          => 'Gestão Inteligente do Inventário Hospitalar',
        'sobre_texto'           => 'O MEDICORE é uma aplicação web para registo, organização e acompanhamento de equipamentos médicos em contexto hospitalar.',
        'contacto_texto'        => 'Entre em contacto com a equipa responsável pela gestão do inventário hospitalar.',
        'rodape_localizacao'    => "Instituto Superior de Engenharia do Porto\nRua Dr. António Bernardino de Almeida\nPorto, Portugal",
        'rodape_horario_semana' => '2ª a 6ª Feira: 9h — 18h',
        'rodape_email'          => 'geral@medicore.pt',
        'rodape_telefone'       => '+351 919 323 121',
    ];
    $slides    = [];
    $hospitais = [];
}

// ---------------------------------------------------------
// Função: devolve o caminho da imagem encontrada (.jpg ou .png)
// ---------------------------------------------------------
function imagem(string $nome): string {
    $pasta = __DIR__ . '/assets/img/';
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (file_exists($pasta . $nome . '.' . $ext)) {
            return 'assets/img/' . $nome . '.' . $ext;
        }
    }
    return 'assets/img/' . $nome . '.jpg'; // fallback (mostra imagem partida)
}

// ---------------------------------------------------------
// Helpers de escape
// ---------------------------------------------------------
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function nl(string $str): string {
    return nl2br(e($str));
}
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MEDICORE</title>

        <!-- favicon -->
        <link rel="shortcut icon" href="assets/img/MEDICORE_icon.png" type="image/png">

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- estilos da página -->
        <link rel="stylesheet" href="assets/css/1230404.css?v=5">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">

        <!-- Font Awesome (local) -->
        <link rel="stylesheet" href="assets/fontawesome/all.min.css">
    </head>

    <body>
        <!-- Navegação -->
        <nav class="bng-navbar">
            <div>
                <img src="<?= e($config['navbar_logo']) ?>" alt="Logo da MEDICORE">
            </div>

            <div class="container-navegacao">
                <a href="#sobre"><?= e($config['navbar_link_sobre']) ?></a>
                <a href="#nossa-equipa"><?= e($config['navbar_link_equipa']) ?></a>
                <a href="#funcionalidades"><?= e($config['navbar_link_funcional']) ?></a>
                <a href="#equipamentos"><?= e($config['navbar_link_hospitais']) ?></a>
                <a href="#contacto"><?= e($config['navbar_link_contacto']) ?></a>
            </div>

            <div class="nav-cliente">
                <a href="login.php" target="_blank"><?= e($config['navbar_btn_restrita']) ?></a>
            </div>
        </nav>

        <!-- Seção "Sobre" -->
        <section class="container-texto-generico" id="sobre">
            <div class="sobre-content">
                <h1><?= e($config['sobre_titulo']) ?></h1>
                <p><?= e($config['sobre_texto']) ?></p>

                <?php if (!empty($slides)): ?>
                <!-- Carrossel de imagens -->
                <div id="carouselMedicore" class="carousel slide hero-carousel" data-bs-ride="carousel">

                    <div class="carousel-indicators">
                        <?php foreach ($slides as $i => $slide): ?>
                        <button type="button" data-bs-target="#carouselMedicore"
                                data-bs-slide-to="<?= $i ?>"
                                <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?>
                                aria-label="Slide <?= $i + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="carousel-inner">
                        <?php foreach ($slides as $i => $slide): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <img src="<?= e($slide['imagem']) ?>" class="d-block w-100" alt="<?= e($slide['titulo']) ?>">
                            <div class="carousel-caption">
                                <h2><?= e($slide['titulo']) ?></h2>
                                <p><?= e($slide['descricao']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselMedicore" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselMedicore" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Seguinte</span>
                    </button>

                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Seção "A Nossa Equipa" (estática — gerida no código) -->
        <section id="nossa-equipa">
            <h2>A Nossa Equipa</h2>
            <div class="equipa-container">
                <div class="pessoa">
                    <i class="fa-solid fa-user-shield fa-3x"></i>
                    <h3>Administrador</h3>
                    <p>Responsável pela gestão dos conteúdos públicos e pelo acesso à área reservada.</p>
                </div>
                <div class="pessoa">
                    <i class="fa-solid fa-user-gear fa-3x"></i>
                    <h3>Técnico Biomédico</h3>
                    <p>Acompanha o estado, localização e funcionamento dos equipamentos médicos.</p>
                </div>
                <div class="pessoa">
                    <i class="fa-solid fa-hospital-user fa-3x"></i>
                    <h3>Gestor Hospitalar</h3>
                    <p>Consulta indicadores do inventário e apoia a tomada de decisão na unidade hospitalar.</p>
                </div>
            </div>
        </section>

        <!-- Seção "Funcionalidades" (estática — gerida no código) -->
        <section id="funcionalidades">
            <h2>Funcionalidades</h2>
            <div class="funcionalidades-container">
                <div class="servico">
                    <i class="fa-solid fa-boxes-stacked fa-3x"></i>
                    <h3>Gestão de Equipamentos</h3>
                    <p>Registo, edição, consulta e organização dos equipamentos médicos existentes.</p>
                </div>
                <div class="servico">
                    <i class="fa-solid fa-truck-medical fa-3x"></i>
                    <h3>Gestão de Fornecedores</h3>
                    <p>Armazenamento de dados dos fornecedores associados aos equipamentos hospitalares.</p>
                </div>
                <div class="servico">
                    <i class="fa-solid fa-location-dot fa-3x"></i>
                    <h3>Localizações Hospitalares</h3>
                    <p>Associação dos equipamentos a departamentos, pisos, salas e unidades clínicas.</p>
                </div>
                <div class="servico">
                    <i class="fa-solid fa-screwdriver-wrench fa-3x"></i>
                    <h3>Estados dos Equipamentos</h3>
                    <p>Controlo de equipamentos ativos, inativos, avariados ou em manutenção.</p>
                </div>
                <div class="servico">
                    <i class="fa-solid fa-chart-line fa-3x"></i>
                    <h3>Dashboard</h3>
                    <p>Visualização de indicadores estatísticos sobre o inventário hospitalar.</p>
                </div>
            </div>
        </section>

        <!-- Seção "Hospitais e Clínicas" (dinâmica — gerida pelo backoffice) -->
        <section id="equipamentos">
            <h2>Hospitais e Clínicas</h2>
            <p class="subtitulo-secao">Algumas das unidades de saúde que confiam no MEDICORE para gerir o seu inventário de equipamentos.</p>

            <?php if (!empty($hospitais)): ?>
            <div class="hospitais-container">
                <?php foreach ($hospitais as $h): ?>
                <div class="hospital-card">
                    <div class="hospital-img-wrap">
                        <img src="<?= e(imagem($h['imagem'])) ?>" alt="<?= e($h['nome']) ?>">
                    </div>
                    <div class="hospital-card-info">
                        <h3><?= e($h['nome']) ?></h3>
                        <p><?= e($h['descricao']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">Nenhuma unidade de saúde disponível de momento.</p>
            <?php endif; ?>
        </section>

        <!-- Seção "Contacto" -->
        <section id="contacto">
            <h2>Contacto</h2>
            <p><?= e($config['contacto_texto']) ?></p>

            <form id="contactForm">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="mensagem">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" rows="4" required></textarea>

                <hr class="contacto-divisor">
                <button type="submit">Enviar Mensagem</button>
            </form>
        </section>

        <!-- Rodapé -->
        <footer class="footer-container">
            <div class="footer-section">
                <strong>LOCALIZAÇÃO</strong>
                <p><?= nl($config['rodape_localizacao']) ?></p>
            </div>
            <div class="footer-section">
                <strong>HORÁRIO</strong>
                <p><?= e($config['rodape_horario_semana']) ?></p>
                <p>Sábado: Encerrado</p>
                <p>Domingo: Encerrado</p>
            </div>
            <div class="footer-section">
                <strong>CONTACTOS</strong>
                <p>Email: <?= e($config['rodape_email']) ?></p>
                <p>Telefone: <?= e($config['rodape_telefone']) ?></p>
            </div>
        </footer>

        <!-- Bootstrap JS (necessário para o carrossel) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>