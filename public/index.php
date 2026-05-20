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
        <link rel="stylesheet" href="assets/css/1230404.css">

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
            <!-- Logo e Nome -->
            <div>
                <img src="assets/img/MEDICORE_logotipo_branco.png" alt="Logo da MEDICORE">
            </div>

            <!-- Links centrais -->
            <div class="container-navegacao">
                <a href="#sobre">Sobre</a>
                <a href="#nossa-equipa">Nossa Equipa</a>
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#equipamentos">Equipamentos</a>
                <a href="#gestao-hospitalar">Gestão Hospitalar</a>
                <a href="#contacto">Contacto</a> <!-- NOVO LINK -->
            </div>

            <!-- Área Cliente -->
            <div class="nav-cliente">
                <a href="login.php" target="_blank">Área Restrita</a>
            </div>
        </nav>

        <!-- Seção "Conteudo da pagina" -->
        <!-- Seção "Sobre" -->
        <section class="container-texto-generico" id="sobre">
            <div class="sobre-content">
                <h1>Gestão Inteligente do Inventário Hospitalar</h1>
                <p>
                    O MEDICORE é uma aplicação web para registo, organização e 
                    acompanhamento de equipamentos médicos em contexto hospitalar.
                </p>

                <!-- Carrossel de imagens - alteração feita por mim -->
                <div id="carouselMedicore" class="carousel slide hero-carousel" data-bs-ride="carousel">
                    
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselMedicore" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#carouselMedicore" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#carouselMedicore" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>

                    <div class="carousel-inner">

                        <div class="carousel-item active">
                            <img src="assets/img/MEDICORE_Official_Logo.png" class="d-block w-100" alt="Inventário hospitalar">
                            <div class="carousel-caption">
                                <h2>Inventário Hospitalar Centralizado</h2>
                                <p>Organize equipamentos médicos, fornecedores, localizações e estados numa única plataforma.</p>
                            </div>
                        </div>

                        <div class="carousel-item">
                            <img src="assets/img/equipamentos_medicos.png" class="d-block w-100" alt="Equipamentos médicos">
                            <div class="carousel-caption">
                                <h2>Equipamentos Sempre Monitorizados</h2>
                                <p>Acompanhe o ciclo de vida dos equipamentos médicos de forma simples e eficiente.</p>
                            </div>
                        </div>

                        <div class="carousel-item">
                            <img src="assets/img/backoffice_hospitalar.png" class="d-block w-100" alt="Backoffice hospitalar">
                            <div class="carousel-caption">
                                <h2>Backoffice Administrativo</h2>
                                <p>Atualize conteúdos públicos e faça a gestão interna através de uma área reservada.</p>
                            </div>
                        </div>

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
            </div>
        </section>

        <!-- Seção "Conteúdo da página - Equipa" -->
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

        <!-- Seção "Serviços" -->
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

        <!-- Seção "Aulas de Grupo" -->
        <section id="equipamentos">
            <h2>Categorias de Equipamentos Médicos</h2>
            <div class="funcionalidades-container">

                <div class="servico">
                    <i class="fa-solid fa-heart-pulse fa-3x"></i>
                    <h3>Monitorização</h3>
                    <p>Equipamentos como monitores multiparamétricos, oxímetros e sensores clínicos.</p>
                </div>

                <div class="servico">
                    <i class="fa-solid fa-x-ray fa-3x"></i>
                    <h3>Imagiologia</h3>
                    <p>Equipamentos associados ao diagnóstico por imagem e apoio clínico.</p>
                </div>

                <div class="servico">
                    <i class="fa-solid fa-lungs fa-3x"></i>
                    <h3>Suporte de Vida</h3>
                    <p>Ventiladores, desfibrilhadores e outros equipamentos críticos hospitalares.</p>
                </div>

                <div class="servico">
                    <i class="fa-solid fa-vial-circle-check fa-3x"></i>
                    <h3>Laboratório</h3>
                    <p>Equipamentos utilizados em análises clínicas, testes e exames laboratoriais.</p>
                </div>

                <div class="servico">
                    <i class="fa-solid fa-bed-pulse fa-3x"></i>
                    <h3>Cuidados Clínicos</h3>
                    <p>Dispositivos de apoio ao acompanhamento e tratamento dos utentes.</p>
                </div>
            </div>
        </section>

        <!-- Seção "Conteúdo da página - Preçário" -->
        <section id="gestao-hospitalar">
            <h2>Gestão Hospitalar</h2>
            <div class="pacotes-container">

                <div class="pacote">
                    <h3>Inventário Organizado</h3>
                    <p class="preco">Controlo</p>

                    <ul>
                        <li>Registo de equipamentos médicos</li>
                        <li>Associação a categorias e fornecedores</li>
                    </ul>

                    <a href="#contacto" class="button">Saber Mais</a>
                </div>


                <div class="pacote">
                    <h3>Localização e Estado</h3>
                    <p class="preco">Rastreio</p>

                    <ul>
                        <li>Identificação da localização hospitalar</li>
                        <li>Consulta do estado operacional</li>
                        <li>Apoio à gestão de manutenção</li>
                    </ul>

                    <a href="#contacto" class="button">Saber Mais</a>
                </div>


                <div class="pacote">
                    <h3>Indicadores de Gestão</h3>
                    <p class="preco">Dashboard</p>

                    <ul>
                        <li>Total de equipamentos registados</li>
                        <li>Equipamentos por categoria</li>
                        <li>Equipamentos por localização</li>
                        <li>Equipamentos ativos e inativos</li>
                    </ul>

                    <a href="#contacto" class="button">Saber Mais</a>
                </div>
            </div>
        </section>

        <!-- Seção "Contacto" -->
        <section id="contacto">

            <h2>Contacto</h2>

            <p>
                Entre em contacto com a equipa responsável pela gestão do inventário hospitalar.
            </p>

            <form id="contactForm">

                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="mensagem">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" rows="4" required></textarea>

                <button type="submit">Enviar Mensagem</button>

            </form>

        </section>
        
        <!-- Rodapé -->
        <footer class="footer-container">

            <div class="footer-section">
                <strong>LOCALIZAÇÃO</strong>
                <p>
                Instituto Superior de Engenharia do Porto <br>
                Rua Dr. António Bernardino de Almeida <br>
                Porto, Portugal
                </p>
            </div>

            <div class="footer-section">
                <strong>HORÁRIO</strong>
                <p>2ª a 6ª Feira: 9h — 18h</p>
                <p>Sábado: Encerrado</p>
                <p>Domingo: Encerrado</p>
            </div>

            <div class="footer-section">
                <strong>CONTACTOS</strong>
                <p>Email: geral@medicore.pt</p>
                <p>Telefone: +351 9xx xxx xxx</p>
            </div>

        </footer>

        <!-- Bootstrap JS - alteração feita por mim para permitir o funcionamento do carrossel -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    </body>
</html>