<?php
require_once __DIR__ . '/funcoes.php';

$paginaAtual = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$tipoUtilizadorAtual = $_SESSION['tipo_utilizador'] ?? '';
$tipoUtilizadorNormalizado = strtolower(trim($tipoUtilizadorAtual));
$isAdministrador = strpos($tipoUtilizadorNormalizado, 'administrador') !== false;

function menu_ativo($caminho)
{
    global $paginaAtual;

    return strpos($paginaAtual, $caminho) === 0 ? ' active' : '';
}

function submenu_ativo($caminho)
{
    global $paginaAtual;

    return $paginaAtual === $caminho ? 'submenu-active' : '';
}

function menu_equipamentos_ativo()
{
    global $paginaAtual;

    $paginasEquipamentos = [
        BASE_URL . '/private/views/equipamentos/lista_equipamentos.php',
        BASE_URL . '/private/views/equipamentos/ficha_equipamento.php',
        BASE_URL . '/private/views/equipamentos/novo_equipamento.php',
        BASE_URL . '/private/views/equipamentos/acessorios.php',
        BASE_URL . '/private/views/equipamentos/consumiveis.php',
        BASE_URL . '/private/views/equipamentos/lista_familia_equipamentos.php',
        BASE_URL . '/private/views/equipamentos/nova_familia_equipamentos.php',
        BASE_URL . '/private/views/equipamentos/preencher_equipamento.php',
        BASE_URL . '/private/views/equipamentos/garantias_equipamentos.php'
    ];

    return in_array($paginaAtual, $paginasEquipamentos, true) ? ' active' : '';
}

function pode_ver($permissao)
{
    return user_has_permission($permissao);
}

$podeVerEquipamentos = pode_ver('equipamentos') || pode_ver('acessorios') || pode_ver('consumiveis') || pode_ver('familias_equipamentos');
$isEngenheiro = strpos($tipoUtilizadorNormalizado, 'engenheiro') !== false;
?>

<!-- Menu horizontal da area privada -->
<nav class="navbar navbar-expand-lg menu-horizontal">
    <div class="container-fluid">
        <button class="navbar-toggler bg-light"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrivado"
                aria-controls="menuPrivado"
                aria-expanded="false"
                aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="menuPrivado">
            <ul class="navbar-nav">

                <?php if (pode_ver('dashboard')): ?>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>/private/index.php"
                           class="nav-link<?php echo menu_ativo(BASE_URL . '/private/index.php'); ?>">
                            <i class="fa-solid fa-chart-line me-2"></i> Administrador
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($isEngenheiro): ?>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>/private/views/dashboard/dashboard_engenheiro.php"
                           class="nav-link<?php echo menu_ativo(BASE_URL . '/private/views/dashboard/dashboard_engenheiro.php'); ?>">
                            <i class="fa-solid fa-gauge-high me-2"></i> Dashboard
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($podeVerEquipamentos): ?>
                    <li class="nav-item menu-dropdown-hover">
                        <a href="<?php echo pode_ver('equipamentos') ? BASE_URL . '/private/views/equipamentos/lista_equipamentos.php' : BASE_URL . '/private/views/equipamentos/lista_familia_equipamentos.php'; ?>"
                           class="nav-link<?php echo menu_equipamentos_ativo(); ?>">
                            <i class="fa-solid fa-stethoscope me-2"></i> Equipamentos
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-private">
                            <?php if (pode_ver('equipamentos')): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/lista_equipamentos.php"
                                       class="<?php echo submenu_ativo(BASE_URL . '/private/views/equipamentos/lista_equipamentos.php'); ?>">
                                        <i class="fa-solid fa-list me-2"></i> Lista de Equipamentos
                                    </a>
                                </li>

                                    <?php if ($isEngenheiro): ?>
                                    <li>
                                        <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/novo_equipamento.php"
                                           class="<?php echo submenu_ativo(BASE_URL . '/private/views/equipamentos/novo_equipamento.php'); ?>">
                                            <i class="fa-solid fa-plus me-2"></i> Adicionar Equipamento
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($isAdministrador): ?>
                                    <li>
                                        <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/preencher_equipamento.php"
                                           class="<?php echo submenu_ativo(BASE_URL . '/private/views/equipamentos/preencher_equipamento.php'); ?>">
                                            <i class="fa-solid fa-pen-to-square me-2"></i> Preencher Equipamentos
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <li>
                                    <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/garantias_equipamentos.php"
                                       class="<?php echo submenu_ativo(BASE_URL . '/private/views/equipamentos/garantias_equipamentos.php'); ?>">
                                        <i class="fa-solid fa-shield me-2"></i> Garantias
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (pode_ver('acessorios')): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/acessorios.php"
                                       class="<?php echo submenu_ativo(BASE_URL . '/private/views/equipamentos/acessorios.php'); ?>">
                                        <i class="fa-solid fa-plug-circle-bolt me-2"></i> Acessorios
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (pode_ver('consumiveis')): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/consumiveis.php"
                                       class="<?php echo submenu_ativo(BASE_URL . '/private/views/equipamentos/consumiveis.php'); ?>">
                                        <i class="fa-solid fa-boxes-stacked me-2"></i> Consumiveis
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (pode_ver('familias_equipamentos')): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/private/views/equipamentos/lista_familia_equipamentos.php"
                                       class="<?php echo submenu_ativo(BASE_URL . '/private/views/equipamentos/lista_familia_equipamentos.php'); ?>">
                                        <i class="fa-solid fa-layer-group me-2"></i> Familias de Equipamentos
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isEngenheiro): ?>
                    <li class="nav-item menu-dropdown-hover">
                        <a href="<?php echo BASE_URL; ?>/private/views/avarias/lista_avarias.php"
                        class="nav-link <?php echo menu_ativo(BASE_URL . '/private/views/avarias'); ?>">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Reportar Avaria
                            <i class="fa-solid fa-chevron-down ms-2"></i>
                        </a>

                        <ul class="submenu-private">
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/avarias/lista_avarias.php"
                                class="<?php echo submenu_ativo(BASE_URL . '/private/views/avarias/lista_avarias.php'); ?>">
                                    <i class="fa-solid fa-list-check me-2"></i> Avarias Reportadas
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/avarias/nova_avaria.php"
                                class="<?php echo submenu_ativo(BASE_URL . '/private/views/avarias/nova_avaria.php'); ?>">
                                    <i class="fa-solid fa-plus me-2"></i> Nova Avaria
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (pode_ver('calibracoes')): ?>
                    <li class="nav-item menu-dropdown-hover">
                        <a href="<?php echo $isAdministrador
                            ? BASE_URL . '/private/views/calibracao_manutencao/aprovacao_processos.php'
                            : BASE_URL . '/private/views/calibracao_manutencao/calibracao_manutencao.php'; ?>"
                        class="nav-link<?php echo menu_ativo(BASE_URL . '/private/views/calibracao_manutencao/'); ?>">
                            <i class="fa-solid fa-screwdriver-wrench me-2"></i> Calibrações/Manutenções
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-private">
                            <?php if (!$isAdministrador): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/calibracao_manutencao.php"
                                    class="<?php echo submenu_ativo(BASE_URL . '/private/views/calibracao_manutencao/calibracao_manutencao.php'); ?>">
                                        <i class="fa-solid fa-list-check me-2"></i> Processos a Decorrer
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if ($isAdministrador): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/aprovacao_processos.php"
                                    class="<?php echo submenu_ativo(BASE_URL . '/private/views/calibracao_manutencao/aprovacao_processos.php'); ?>">
                                        <i class="fa-solid fa-clipboard-check me-2"></i> Aprovação de Pedidos
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/processos_encerrados.php"
                                class="<?php echo submenu_ativo(BASE_URL . '/private/views/calibracao_manutencao/processos_encerrados.php'); ?>">
                                    <i class="fa-solid fa-box-archive me-2"></i> Processos Encerrados
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/calibracao_manutencao/periodicidade.php"
                                class="<?php echo submenu_ativo(BASE_URL . '/private/views/calibracao_manutencao/periodicidade.php'); ?>">
                                    <i class="fa-solid fa-calendar-check me-2"></i> Periodicidade
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (pode_ver('localizacoes')): ?>
                    <li class="nav-item menu-dropdown-hover">
                        <a href="<?php echo BASE_URL; ?>/private/views/localizacoes/lista_localizacoes.php"
                           class="nav-link<?php echo menu_ativo(BASE_URL . '/private/views/localizacoes/'); ?>">
                            <i class="fa-solid fa-location-dot me-2"></i> Localizacoes
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-private">
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/localizacoes/lista_localizacoes.php"
                                   class="<?php echo submenu_ativo(BASE_URL . '/private/views/localizacoes/lista_localizacoes.php'); ?>">
                                    <i class="fa-solid fa-list me-2"></i> Lista de Localizacoes
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/localizacoes/nova_localizacao.php"
                                   class="<?php echo submenu_ativo(BASE_URL . '/private/views/localizacoes/nova_localizacao.php'); ?>">
                                    <i class="fa-solid fa-plus me-2"></i> Adicionar Localizacao
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (pode_ver('mobilidade')): ?>
                    <li class="nav-item menu-dropdown-hover">
                        <a href="<?php echo BASE_URL; ?>/private/views/mobilidade/transferencia.php"
                        class="nav-link<?php echo menu_ativo(BASE_URL . '/private/views/mobilidade/'); ?>">
                            <i class="fa-solid fa-route me-2"></i> Mobilidade
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-private">
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/mobilidade/transferencia.php"
                                class="<?php echo submenu_ativo(BASE_URL . '/private/views/mobilidade/transferencia.php'); ?>">
                                    <i class="fa-solid fa-right-left me-2"></i> Transferências
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/mobilidade/emprestimo.php"
                                class="<?php echo submenu_ativo(BASE_URL . '/private/views/mobilidade/emprestimo.php'); ?>">
                                    <i class="fa-solid fa-handshake me-2"></i> Empréstimos
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (pode_ver('fornecedores') && !$isAdministrador): ?>
                    <li class="nav-item menu-dropdown-hover">
                        <a href="<?php echo BASE_URL; ?>/private/views/fornecedores/lista_fornecedores.php"
                           class="nav-link<?php echo menu_ativo(BASE_URL . '/private/views/fornecedores/'); ?>">
                            <i class="fa-solid fa-truck-medical me-2"></i> Fornecedores
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-private">
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/fornecedores/lista_fornecedores.php"
                                   class="<?php echo submenu_ativo(BASE_URL . '/private/views/fornecedores/lista_fornecedores.php'); ?>">
                                    <i class="fa-solid fa-list me-2"></i> Lista de Fornecedores
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/fornecedores/novo_fornecedor.php"
                                   class="<?php echo submenu_ativo(BASE_URL . '/private/views/fornecedores/novo_fornecedor.php'); ?>">
                                    <i class="fa-solid fa-plus me-2"></i> Adicionar Fornecedor
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (pode_ver('utilizadores')): ?>
                    <li class="nav-item menu-dropdown-hover">
                        <a href="<?php echo BASE_URL; ?>/private/views/utilizadores/lista_utilizadores.php"
                           class="nav-link<?php echo menu_ativo(BASE_URL . '/private/views/utilizadores/'); ?>">
                            <i class="fa-solid fa-user me-2"></i> Utilizadores
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-private">
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/utilizadores/lista_utilizadores.php"
                                   class="<?php echo submenu_ativo(BASE_URL . '/private/views/utilizadores/lista_utilizadores.php'); ?>">
                                    <i class="fa-solid fa-list me-2"></i> Lista de Utilizadores
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/private/views/utilizadores/novo_utilizador.php"
                                   class="<?php echo submenu_ativo(BASE_URL . '/private/views/utilizadores/novo_utilizador.php'); ?>">
                                    <i class="fa-solid fa-plus me-2"></i> Adicionar Utilizador
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (pode_ver('backoffice')): ?>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>/private/views/backoffice/backoffice.php"
                           class="nav-link<?php echo menu_ativo(BASE_URL . '/private/views/backoffice/'); ?>">
                            <i class="fa-solid fa-pen-to-square me-2"></i> Backoffice
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>
