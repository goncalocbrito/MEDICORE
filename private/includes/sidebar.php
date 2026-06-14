<?php
require_once __DIR__ . '/../../config/config.php';

$paginaAtual = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

?>

    <!-- Menu horizontal da área privada -->
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

                        <li class="nav-item">
                            <a href="/MEDICORE/private/index.php" class="nav-link<?php echo menu_ativo('/MEDICORE/private/index.php'); ?>">
                                <i class="fa-solid fa-chart-line me-2"></i> Dashboard Técnico
                            </a>
                        </li>

                        <li class="nav-item menu-dropdown-hover">
                            <a href="/MEDICORE/private/views/equipamentos/lista_equipamentos.php" class="nav-link<?php echo menu_ativo('/MEDICORE/private/views/equipamentos/'); ?>">
                                <i class="fa-solid fa-stethoscope me-2"></i> Equipamentos
                                <i class="fa-solid fa-chevron-down ms-1 small"></i>
                            </a>

                            <ul class="submenu-equipamentos">
                                <li>
                                    <a href="/MEDICORE/private/views/equipamentos/lista_equipamentos.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/equipamentos/lista_equipamentos.php'); ?>">
                                        <i class="fa-solid fa-list me-2"></i> Lista de Equipamentos
                                    </a>
                                </li>

                                <li>
                                    <a href="/MEDICORE/private/views/equipamentos/novo_equipamento.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/equipamentos/novo_equipamento.php'); ?>">
                                        <i class="fa-solid fa-plus me-2"></i> Adicionar Equipamento
                                    </a>
                                </li>

                                <li>
                                    <a href="/MEDICORE/private/views/equipamentos/acessorios.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/equipamentos/acessorios.php'); ?>">
                                        <i class="fa-solid fa-plug-circle-bolt me-2"></i> Acessórios
                                    </a>
                                </li>

                                <li>
                                    <a href="/MEDICORE/private/views/equipamentos/consumiveis.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/equipamentos/consumiveis.php'); ?>">
                                        <i class="fa-solid fa-boxes-stacked me-2"></i> Consumíveis
                                    </a>
                                </li>

                                <li>
                                    <a href="/MEDICORE/private/views/equipamentos/lista_familias_equipamentos.php">
                                        <i class="fa-solid fa-layer-group me-2"></i> Famílias de Equipamentos
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <li class="nav-item menu-dropdown-hover-calibracoes">
                        <a href="/MEDICORE/private/views/calibracao_manutencao/calibracao_manutencao.php" class="nav-link<?php echo menu_ativo('/MEDICORE/private/views/calibracao_manutencao/'); ?>">
                            <i class="fa-solid fa-screwdriver-wrench me-2"></i> Calibrações/Manutenções
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-calibracoes">
                            <li>
                                <a href="/MEDICORE/private/views/calibracao_manutencao/calibracao_manutencao.php" class="<?php echo submenu_ativo('/MEDICORE/private/views/calibracao_manutencao/calibracao_manutencao.php'); ?>">
                                    <i class="fa-solid fa-list-check me-2"></i> Processos a Decorrer
                                </a>
                            </li>
                            <li>
                                <a href="/MEDICORE/private/views/calibracao_manutencao/processos_finalizados.php" class="<?php echo submenu_ativo('/MEDICORE/private/views/calibracao_manutencao/processos_finalizados.php'); ?>">
                                    <i class="fa-solid fa-circle-check me-2"></i> Processos Finalizados
                                </a>
                            </li>
                        </ul>
                    </li>

                        <li class="nav-item menu-dropdown-hover-localizacoes">
                            <a href="/MEDICORE/private/views/localizacoes/lista_localizacoes.php" class="nav-link<?php echo menu_ativo('/MEDICORE/private/views/localizacoes/'); ?>">
                                <i class="fa-solid fa-location-dot me-2"></i> Localizações
                                <i class="fa-solid fa-chevron-down ms-1 small"></i>
                            </a>

                            <ul class="submenu-localizacoes">
                                <li>
                                    <a href="/MEDICORE/private/views/localizacoes/lista_localizacoes.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/localizacoes/lista_localizacoes.php'); ?>">
                                        <i class="fa-solid fa-list me-2"></i> Lista de Localizações
                                    </a>
                                </li>

                                <li>
                                    <a href="/MEDICORE/private/views/localizacoes/nova_localizacao.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/localizacoes/nova_localizacao.php'); ?>">
                                        <i class="fa-solid fa-plus me-2"></i> Adicionar Localização
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item menu-dropdown-hover-fornecedores">
                            <a href="/MEDICORE/private/views/fornecedores/lista_fornecedores.php" class="nav-link<?php echo menu_ativo('/MEDICORE/private/views/fornecedores/'); ?>">
                                <i class="fa-solid fa-truck-medical me-2"></i> Fornecedores
                                <i class="fa-solid fa-chevron-down ms-1 small"></i>
                            </a>

                            <ul class="submenu-fornecedores">
                                <li>
                                    <a href="/MEDICORE/private/views/fornecedores/lista_fornecedores.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/fornecedores/lista_fornecedores.php'); ?>">
                                        <i class="fa-solid fa-list me-2"></i> Lista de Fornecedores
                                    </a>
                                </li>

                                <li>
                                    <a href="/MEDICORE/private/views/fornecedores/novo_fornecedor.php"
                                    class="<?php echo submenu_ativo('/MEDICORE/private/views/fornecedores/novo_fornecedor.php'); ?>">
                                        <i class="fa-solid fa-plus me-2"></i> Adicionar Fornecedor
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <li class="nav-item menu-dropdown-hover-utilizadores">
                        <a href="/MEDICORE/private/views/utilizadores/lista_utilizadores.php" class="nav-link<?php echo menu_ativo('/MEDICORE/private/views/utilizadores/'); ?>">
                            <i class="fa-solid fa-user me-2"></i> Utilizadores
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-utilizadores">
                            <li>
                                <a href="/MEDICORE/private/views/utilizadores/lista_utilizadores.php"
                                class="<?php echo submenu_ativo('/MEDICORE/private/views/utilizadores/lista_utilizadores.php'); ?>">
                                    <i class="fa-solid fa-list me-2"></i> Lista de Utilizadores
                                </a>
                            </li>

                            <li>
                                <a href="/MEDICORE/private/views/utilizadores/novo_utilizador.php"
                                class="<?php echo submenu_ativo('/MEDICORE/private/views/utilizadores/novo_utilizador.php'); ?>">
                                    <i class="fa-solid fa-plus me-2"></i> Adicionar Utilizador
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Menu Backoffice -->
                    <li class="nav-item">
                        <a href="/MEDICORE/private/views/backoffice/backoffice.php" class="nav-link<?php echo menu_ativo('/MEDICORE/private/views/backoffice/'); ?>">
                            <i class="fa-solid fa-pen-to-square me-2"></i> Backoffice
                        </a>
                    </li>

                    </ul>
                </div>
            </div>
        </nav>