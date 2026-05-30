<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDICORE | Ficha da Localização</title>

    <link rel="shortcut icon" href="../../assets/img/MEDICORE_icon.png" type="image/png">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/1230404.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/fontawesome/all.min.css">
</head>

<body>

    <!-- =========================================================
         CABEÇALHO SUPERIOR DA ÁREA PRIVADA
         Mantém o mesmo topo usado nos módulos de equipamentos e fornecedores.
         ========================================================= -->
    <header class="container-fluid top-header">
        <div class="row align-items-center">
            <div class="col-md-4 d-flex align-items-center p-3">
                <a href="../../index.html">
                    <img src="../../assets/img/MEDICORE_logotipo_branco.png"
                         alt="Logo da MEDICORE"
                         class="logo-private">
                </a>
            </div>

            <div class="col-md-4 text-center p-3">
                <div class="mensagem-topo">
                    <i class="fa-solid fa-user-doctor me-2"></i>
                    <span>Bem-vindo, Sr. Engenheiro Gonçalo</span>
                </div>
            </div>

            <div class="col-md-4 text-md-end p-3">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle user-button"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="fa-regular fa-user me-2"></i> Eng. Biomédico
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="perfil.php"><i class="fa-solid fa-user-gear me-2"></i> Perfil técnico</a></li>
                        <li><a class="dropdown-item" href="alterar_password.php"><i class="fa-solid fa-key me-2"></i> Alterar password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- =========================================================
         MENU HORIZONTAL DA ÁREA PRIVADA
         O menu Localizações fica ativo porque esta ficha pertence a esse módulo.
         ========================================================= -->
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
                        <a href="../../index.html" class="nav-link">
                            <i class="fa-solid fa-chart-line me-2"></i> Dashboard Técnico
                        </a>
                    </li>

                    <li class="nav-item menu-dropdown-hover">
                        <a href="../equipamentos/lista_equipamentos.html" class="nav-link">
                            <i class="fa-solid fa-stethoscope me-2"></i> Equipamentos
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>
                        <ul class="submenu-equipamentos">
                            <li>
                                <a href="../equipamentos/lista_equipamentos.html">
                                    <i class="fa-solid fa-list me-2"></i> Lista de Equipamentos
                                </a>
                            </li>
                            <li>
                                <a href="../equipamentos/novo_equipamento.html">
                                    <i class="fa-solid fa-plus me-2"></i> Adicionar Equipamento
                                </a>
                            </li>
                            <li>
                                <a href="../equipamentos/acessorios.html">
                                    <i class="fa-solid fa-plug-circle-bolt me-2"></i> Acessórios
                                </a>
                            </li>
                            <li>
                                <a href="../equipamentos/consumiveis.html">
                                    <i class="fa-solid fa-boxes-stacked me-2"></i> Consumíveis
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item menu-dropdown-hover-calibracoes">
                        <a href="../calibracao_manutencao/calibracao_manutencao.html" class="nav-link">
                            <i class="fa-solid fa-screwdriver-wrench me-2"></i> Calibrações/Manutenções
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-calibracoes">
                            <li>
                                <a href="../calibracao_manutencao/calibracao_manutencao.html">
                                    <i class="fa-solid fa-list-check me-2"></i> Processos a Decorrer
                                </a>
                            </li>
                            <li>
                                <a href="../calibracao_manutencao/processos_finalizados.html">
                                    <i class="fa-solid fa-circle-check me-2"></i> Processos Finalizados
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item menu-dropdown-hover-localizacoes">
                        <a href="lista_localizacoes.html" class="nav-link active">
                            <i class="fa-solid fa-location-dot me-2"></i> Localizações
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>
                        <ul class="submenu-localizacoes">
                            <li><a href="lista_localizacoes.html"><i class="fa-solid fa-list me-2"></i> Lista de Localizações</a></li>
                            <li><a href="nova_localizacao.html"><i class="fa-solid fa-plus me-2"></i> Adicionar Localização</a></li>
                        </ul>
                    </li>

                    <li class="nav-item menu-dropdown-hover-fornecedores">
                        <a href="../fornecedores/lista_fornecedores.html" class="nav-link">
                            <i class="fa-solid fa-truck-medical me-2"></i> Fornecedores
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>
                        <ul class="submenu-fornecedores">
                            <li><a href="../fornecedores/lista_fornecedores.html"><i class="fa-solid fa-list me-2"></i> Lista de Fornecedores</a></li>
                            <li><a href="../fornecedores/novo_fornecedor.html"><i class="fa-solid fa-plus me-2"></i> Adicionar Fornecedor</a></li>
                        </ul>
                    </li>

                    <li class="nav-item menu-dropdown-hover-utilizadores">
                        <a href="../utilizadores/lista_utilizadores.html" class="nav-link">
                            <i class="fa-solid fa-user me-2"></i> Utilizadores
                            <i class="fa-solid fa-chevron-down ms-1 small"></i>
                        </a>

                        <ul class="submenu-utilizadores">
                            <li>
                                <a href="../utilizadores/lista_utilizadores.html">
                                    <i class="fa-solid fa-list me-2"></i> Lista de Utilizadores
                                </a>
                            </li>

                            <li>
                                <a href="../utilizadores/novo_utilizador.html">
                                    <i class="fa-solid fa-plus me-2"></i> Adicionar Utilizador
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Menu Backoffice -->
                    <li class="nav-item">
                        <a href="../backoffice/backoffice.html" class="nav-link">
                            <i class="fa-solid fa-pen-to-square me-2"></i> Backoffice
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- =========================================================
         CONTEÚDO PRINCIPAL DA FICHA DA LOCALIZAÇÃO
         Usa as mesmas classes da ficha do equipamento para herdar estilo,
         largura, separadores e modo consulta/edição.
         ========================================================= -->
    <main class="conteudo-private ficha-equipamento-page ficha-localizacao-page">

        <!-- Elementos ocultos atualizados pelo JS para resumo/badges futuros. -->
        <div class="d-none" aria-hidden="true">
            <h2 id="tituloPaginaLocalizacao">Ficha da Localização</h2>
            <span id="resumoNomeLocalizacao">Localização Hospitalar</span>
            <span id="resumoDescricaoLocalizacao">Departamento | Edifício | Sala</span>
            <span id="badgeEstadoLocalizacao">Estado</span>
            <span id="badgeTipoLocalizacao">Tipo</span>
            <span id="badgeCriticidadeLocalizacao">Criticidade</span>
        </div>

        <!-- Barra de ações: consulta mostra Voltar/Editar; edição mostra Cancelar/Guardar. -->
        <div class="ficha-toolbar">
            <a href="lista_localizacoes.html" class="btn btn-voltar botao-consulta">
                <i class="fa-solid fa-arrow-left me-2"></i> Voltar à Lista
            </a>

            <button type="button" class="btn btn-editar-ficha botao-consulta" id="btnAtivarEdicaoLocalizacao">
                <i class="fa-solid fa-pen me-2"></i> Editar
            </button>

            <button type="button" class="btn btn-cancelar botao-edicao d-none" id="btnCancelarEdicaoLocalizacao">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar
            </button>

            <button type="submit" class="btn btn-guardar botao-edicao d-none" form="formFichaLocalizacao">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Alterações
            </button>
        </div>

        <!-- Formulário único da ficha, usado em consulta e edição. -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formFichaLocalizacao"
              action="processa_localizacao.php"
              method="post">

            <input type="hidden" id="idLocalizacao" name="idLocalizacao" value="LOC-001">
            <input type="hidden" id="modoFormularioLocalizacao" name="modoFormulario" value="ver">

            <div class="ficha-area">
                <!-- Separadores Bootstrap da ficha da localização. -->
                <ul class="nav nav-tabs ficha-tabs" id="tabsFichaLocalizacao" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="identificacao-tab" data-bs-toggle="tab" data-bs-target="#identificacao" type="button" role="tab" aria-controls="identificacao" aria-selected="true">
                            <i class="fa-solid fa-location-dot me-2"></i> Identificação
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="responsavel-tab" data-bs-toggle="tab" data-bs-target="#responsavel" type="button" role="tab" aria-controls="responsavel" aria-selected="false">
                            <i class="fa-solid fa-user-gear me-2"></i> Responsável
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="caracteristicas-tab" data-bs-toggle="tab" data-bs-target="#caracteristicas" type="button" role="tab" aria-controls="caracteristicas" aria-selected="false">
                            <i class="fa-solid fa-hospital me-2"></i> Características
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="indicadores-tab" data-bs-toggle="tab" data-bs-target="#indicadores" type="button" role="tab" aria-controls="indicadores" aria-selected="false">
                            <i class="fa-solid fa-chart-simple me-2"></i> Indicadores
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="equipamentos-tab" data-bs-toggle="tab" data-bs-target="#equipamentos" type="button" role="tab" aria-controls="equipamentos" aria-selected="false">
                            <i class="fa-solid fa-stethoscope me-2"></i> Equipamentos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="observacoes-tab" data-bs-toggle="tab" data-bs-target="#observacoes-tab-pane" type="button" role="tab" aria-controls="observacoes-tab-pane" aria-selected="false">
                            <i class="fa-solid fa-clipboard-list me-2"></i> Observações
                        </button>
                    </li>
                </ul>

                <div class="tab-content ficha-tab-content" id="tabsFichaLocalizacaoContent">
                    <!-- Separador 1: identificação física da localização. -->
                    <div class="tab-pane fade show active" id="identificacao" role="tabpanel" aria-labelledby="identificacao-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Identificação da Localização</h4>
                            <p>Dados principais do espaço hospitalar onde podem estar associados equipamentos.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="codigoLocalizacao" class="form-label">Código da Localização *</label>
                                <input type="text" class="form-control campo-ficha campo-bloqueado" id="codigoLocalizacao" name="codigoLocalizacao" value="LOC-001" readonly>
                            </div>

                            <div class="col-md-4">
                                <label for="departamentoLocalizacao" class="form-label">Departamento / Serviço *</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="departamentoLocalizacao" name="departamentoLocalizacao" value="Unidade de Cuidados Intensivos" required>
                            </div>

                            <div class="col-md-4">
                                <label for="tipoEspaco" class="form-label">Tipo de Espaço *</label>
                                <select class="form-select campo-ficha campo-editavel" id="tipoEspaco" name="tipoEspaco" required>
                                    <option value="">Selecionar tipo</option>
                                    <option value="UCI" selected>UCI</option>
                                    <option value="Urgência">Urgência</option>
                                    <option value="Bloco Operatório">Bloco Operatório</option>
                                    <option value="Laboratório">Laboratório</option>
                                    <option value="Armazém Técnico">Armazém Técnico</option>
                                    <option value="Consulta Externa">Consulta Externa</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="edificioLocalizacao" class="form-label">Edifício *</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="edificioLocalizacao" name="edificioLocalizacao" value="Edifício A" required>
                            </div>

                            <div class="col-md-2">
                                <label for="pisoLocalizacao" class="form-label">Piso *</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="pisoLocalizacao" name="pisoLocalizacao" value="2" required>
                            </div>

                            <div class="col-md-3">
                                <label for="salaLocalizacao" class="form-label">Sala *</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="salaLocalizacao" name="salaLocalizacao" value="Sala 201" required>
                            </div>

                            <div class="col-md-3">
                                <label for="estadoLocalizacao" class="form-label">Estado *</label>
                                <select class="form-select campo-ficha campo-editavel" id="estadoLocalizacao" name="estadoLocalizacao" required>
                                    <option value="">Selecionar estado</option>
                                    <option value="Ativa" selected>Ativa</option>
                                    <option value="Inativa">Inativa</option>
                                    <option value="Em manutenção">Em manutenção</option>
                                    <option value="Indisponível">Indisponível</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Separador 2: responsável e contactos internos. -->
                    <div class="tab-pane fade" id="responsavel" role="tabpanel" aria-labelledby="responsavel-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Responsável e Contacto Interno</h4>
                            <p>Dados da pessoa ou serviço responsável pelo espaço.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="responsavelLocalizacao" class="form-label">Responsável pelo Serviço</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="responsavelLocalizacao" name="responsavelLocalizacao" value="Enf. Maria Costa">
                            </div>
                            <div class="col-md-4">
                                <label for="funcaoResponsavelLocalizacao" class="form-label">Função / Cargo</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="funcaoResponsavelLocalizacao" name="funcaoResponsavelLocalizacao" value="Enfermeira Responsável">
                            </div>
                            <div class="col-md-4">
                                <label for="contactoInternoLocalizacao" class="form-label">Contacto Interno</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="contactoInternoLocalizacao" name="contactoInternoLocalizacao" value="Ext. 2201">
                            </div>
                            <div class="col-md-6">
                                <label for="emailResponsavelLocalizacao" class="form-label">Email do Responsável</label>
                                <input type="email" class="form-control campo-ficha campo-editavel" id="emailResponsavelLocalizacao" name="emailResponsavelLocalizacao" value="maria.costa@hospital.pt">
                            </div>
                            <div class="col-md-6">
                                <label for="observacaoContactoLocalizacao" class="form-label">Notas de Contacto</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="observacaoContactoLocalizacao" name="observacaoContactoLocalizacao" value="Contactar preferencialmente durante o turno da manhã.">
                            </div>
                        </div>
                    </div>

                    <!-- Separador 3: características técnicas do espaço. -->
                    <div class="tab-pane fade" id="caracteristicas" role="tabpanel" aria-labelledby="caracteristicas-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Características da Localização</h4>
                            <p>Condições de acesso, criticidade e suporte a equipamentos críticos.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="acessoLocalizacao" class="form-label">Acesso à Localização</label>
                                <select class="form-select campo-ficha campo-editavel" id="acessoLocalizacao" name="acessoLocalizacao">
                                    <option value="">Selecionar acesso</option>
                                    <option value="Livre">Livre</option>
                                    <option value="Restrito">Restrito</option>
                                    <option value="Apenas pessoal autorizado" selected>Apenas pessoal autorizado</option>
                                    <option value="Acesso técnico">Acesso técnico</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="criticidadeLocalizacao" class="form-label">Criticidade da Área</label>
                                <select class="form-select campo-ficha campo-editavel" id="criticidadeLocalizacao" name="criticidadeLocalizacao">
                                    <option value="">Selecionar criticidade</option>
                                    <option value="Baixa">Baixa</option>
                                    <option value="Média">Média</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Crítica" selected>Crítica</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="capacidadeEquipamentos" class="form-label">Capacidade Estimada</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="capacidadeEquipamentos" name="capacidadeEquipamentos" value="10 equipamentos">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Permite equipamentos críticos?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input campo-ficha campo-editavel" type="radio" name="permiteCriticos" id="permiteCriticosSim" value="Sim" checked>
                                    <label class="form-check-label" for="permiteCriticosSim">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input campo-ficha campo-editavel" type="radio" name="permiteCriticos" id="permiteCriticosNao" value="Não">
                                    <label class="form-check-label" for="permiteCriticosNao">Não</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Área com suporte de vida?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input campo-ficha campo-editavel" type="radio" name="suporteVidaLocalizacao" id="suporteVidaSim" value="Sim" checked>
                                    <label class="form-check-label" for="suporteVidaSim">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input campo-ficha campo-editavel" type="radio" name="suporteVidaLocalizacao" id="suporteVidaNao" value="Não">
                                    <label class="form-check-label" for="suporteVidaNao">Não</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador 4: indicadores operacionais da localização. -->
                    <div class="tab-pane fade" id="indicadores" role="tabpanel" aria-labelledby="indicadores-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Indicadores da Localização</h4>
                            <p>Resumo operacional de capacidade e estado dos equipamentos associados.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-3">
                                <label for="qtdEquipamentosLocalizacao" class="form-label">Total de Equipamentos</label>
                                <input type="number" class="form-control campo-ficha campo-editavel" id="qtdEquipamentosLocalizacao" name="qtdEquipamentosLocalizacao" min="0" value="8">
                            </div>
                            <div class="col-md-3">
                                <label for="equipamentosAtivosLocalizacao" class="form-label">Ativos</label>
                                <input type="number" class="form-control campo-ficha campo-editavel" id="equipamentosAtivosLocalizacao" name="equipamentosAtivosLocalizacao" min="0" value="7">
                            </div>
                            <div class="col-md-3">
                                <label for="equipamentosManutencaoLocalizacao" class="form-label">Em Manutenção</label>
                                <input type="number" class="form-control campo-ficha campo-editavel" id="equipamentosManutencaoLocalizacao" name="equipamentosManutencaoLocalizacao" min="0" value="1">
                            </div>
                            <div class="col-md-3">
                                <label for="equipamentosAvariadosLocalizacao" class="form-label">Avariados</label>
                                <input type="number" class="form-control campo-ficha campo-editavel" id="equipamentosAvariadosLocalizacao" name="equipamentosAvariadosLocalizacao" min="0" value="0">
                            </div>
                            <div class="col-md-3">
                                <label for="ocupacaoLocalizacao" class="form-label">Ocupação</label>
                                <input type="text" class="form-control campo-ficha campo-editavel" id="ocupacaoLocalizacao" name="ocupacaoLocalizacao" value="80%">
                            </div>
                        </div>
                    </div>

                    <!-- Separador 5: tabela de equipamentos associados. -->
                    <div class="tab-pane fade" id="equipamentos" role="tabpanel" aria-labelledby="equipamentos-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Equipamentos Associados</h4>
                            <p>Lista de equipamentos atualmente associados a esta localização.</p>
                        </div>

                        <div class="table-responsive tabela-container p-0">
                            <table class="table table-hover align-middle tabela-equipamentos-associados">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Equipamento</th>
                                        <th>Categoria</th>
                                        <th>Modelo</th>
                                        <th>Nº Série</th>
                                        <th>Criticidade</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaEquipamentosFichaLocalizacao">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">A carregar equipamentos associados...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Separador 6: observações gerais. -->
                    <div class="tab-pane fade" id="observacoes-tab-pane" role="tabpanel" aria-labelledby="observacoes-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Observações</h4>
                            <p>Notas sobre acessos, limitações, condições técnicas ou contexto do espaço.</p>
                        </div>

                        <textarea class="form-control campo-ficha campo-editavel" id="observacoesLocalizacao" name="observacoesLocalizacao" rows="7">Área crítica com equipamentos de suporte de vida e monitorização contínua.</textarea>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/1230404.js"></script>

</body>
</html>
