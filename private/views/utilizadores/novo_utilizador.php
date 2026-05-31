<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

    <!-- =========================================================
         CONTEÚDO PRINCIPAL DO NOVO UTILIZADOR
         Usa ações no topo e formulário por separadores.
         ========================================================= -->
    <main class="conteudo-private ficha-equipamento-page novo-equipamento-page ficha-utilizador-page">

        <!-- =====================================================
             BOTÕES PRINCIPAIS DO FORMULÁRIO
             Cancelar volta à lista, Limpar repõe os campos e Guardar
             mostra o pop-up de confirmação.
             ===================================================== -->
        <div class="form-actions">
            <a href="lista_utilizadores.html" class="btn btn-cancelar">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar
            </a>

            <button type="button" class="btn btn-limpar" id="btnLimparNovoUtilizador">
                <i class="fa-solid fa-eraser me-2"></i> Limpar
            </button>

            <button type="submit" class="btn btn-guardar" form="formNovoUtilizador">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Utilizador
            </button>
        </div>

        <!-- =====================================================
             FORMULÁRIO DE NOVO UTILIZADOR
             Recolhe identificação, contactos, permissões e serviço.
             ===================================================== -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formNovoUtilizador"
              action="processa_novo_utilizador.php"
              method="post">

            <input type="hidden" name="acao" value="inserir">

            <div class="ficha-area">
                <!-- =============================================
                     SEPARADORES DO FORMULÁRIO
                     Organizam os dados em grupos lógicos.
                     ============================================= -->
                <ul class="nav nav-tabs ficha-tabs" id="tabsNovoUtilizador" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="identificacao-tab" data-bs-toggle="tab" data-bs-target="#identificacao" type="button" role="tab" aria-controls="identificacao" aria-selected="true">
                            <i class="fa-solid fa-id-card me-2"></i> Identificação
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contactos-tab" data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab" aria-controls="contactos" aria-selected="false">
                            <i class="fa-solid fa-address-book me-2"></i> Contactos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="acesso-tab" data-bs-toggle="tab" data-bs-target="#acesso" type="button" role="tab" aria-controls="acesso" aria-selected="false">
                            <i class="fa-solid fa-lock me-2"></i> Acesso
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="servico-tab" data-bs-toggle="tab" data-bs-target="#servico" type="button" role="tab" aria-controls="servico" aria-selected="false">
                            <i class="fa-solid fa-hospital me-2"></i> Serviço
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="observacoes-tab" data-bs-toggle="tab" data-bs-target="#observacoes" type="button" role="tab" aria-controls="observacoes" aria-selected="false">
                            <i class="fa-solid fa-note-sticky me-2"></i> Observações
                        </button>
                    </li>
                </ul>

                <div class="tab-content ficha-tab-content" id="tabsNovoUtilizadorConteudo">

                    <!-- =========================================
                         SEPARADOR IDENTIFICAÇÃO
                         Dados pessoais e categoria do utilizador.
                         ========================================= -->
                    <div class="tab-pane fade show active" id="identificacao" role="tabpanel" aria-labelledby="identificacao-tab">
                        <h3 class="secao-ficha-titulo">Identificação do utilizador</h3>

                        <div class="row g-4">
                            <div class="col-md-3">
                                <label for="codigoUtilizador" class="form-label">Código interno</label>
                                <input type="text" class="form-control" id="codigoUtilizador" name="codigoUtilizador" placeholder="USR-005" required>
                            </div>

                            <div class="col-md-5">
                                <label for="nomeUtilizador" class="form-label">Nome completo</label>
                                <input type="text" class="form-control" id="nomeUtilizador" name="nomeUtilizador" placeholder="Nome do utilizador" required>
                            </div>

                            <div class="col-md-2">
                                <label for="tipoUtilizador" class="form-label">Tipo</label>
                                <select class="form-select" id="tipoUtilizador" name="tipoUtilizador" required>
                                    <option value="">Selecionar</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Engenheiro">Engenheiro</option>
                                    <option value="Enfermeiro">Enfermeiro</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="estadoUtilizador" class="form-label">Estado</label>
                                <select class="form-select" id="estadoUtilizador" name="estadoUtilizador" required>
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                    <option value="Pendente">Pendente</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="cartaoCidadaoUtilizador" class="form-label">Nº cartão de cidadão</label>
                                <input type="text" class="form-control" id="cartaoCidadaoUtilizador" name="cartaoCidadaoUtilizador" placeholder="12345678" required>
                            </div>

                            <div class="col-md-3">
                                <label for="nifUtilizador" class="form-label">NIF</label>
                                <input type="text" class="form-control" id="nifUtilizador" name="nifUtilizador" placeholder="123456789">
                            </div>

                            <div class="col-md-3">
                                <label for="dataNascimentoUtilizador" class="form-label">Data de nascimento</label>
                                <input type="date" class="form-control" id="dataNascimentoUtilizador" name="dataNascimentoUtilizador">
                            </div>

                            <div class="col-md-3">
                                <label for="numeroMecanograficoUtilizador" class="form-label">Nº mecanográfico</label>
                                <input type="text" class="form-control" id="numeroMecanograficoUtilizador" name="numeroMecanograficoUtilizador" placeholder="MEC-0001">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR CONTACTOS
                         Informação de contacto profissional.
                         ========================================= -->
                    <div class="tab-pane fade" id="contactos" role="tabpanel" aria-labelledby="contactos-tab">
                        <h3 class="secao-ficha-titulo">Contactos</h3>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="emailUtilizador" class="form-label">Email institucional</label>
                                <input type="email" class="form-control" id="emailUtilizador" name="emailUtilizador" placeholder="nome@medicore.pt" required>
                            </div>

                            <div class="col-md-4">
                                <label for="telefoneUtilizador" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="telefoneUtilizador" name="telefoneUtilizador" placeholder="+351 220 000 000">
                            </div>

                            <div class="col-md-4">
                                <label for="extensaoUtilizador" class="form-label">Extensão interna</label>
                                <input type="text" class="form-control" id="extensaoUtilizador" name="extensaoUtilizador" placeholder="Ext. 2200">
                            </div>

                            <div class="col-md-6">
                                <label for="moradaUtilizador" class="form-label">Morada</label>
                                <input type="text" class="form-control" id="moradaUtilizador" name="moradaUtilizador" placeholder="Rua, avenida ou localidade">
                            </div>

                            <div class="col-md-3">
                                <label for="codigoPostalUtilizador" class="form-label">Código postal</label>
                                <input type="text" class="form-control" id="codigoPostalUtilizador" name="codigoPostalUtilizador" placeholder="0000-000">
                            </div>

                            <div class="col-md-3">
                                <label for="localidadeUtilizador" class="form-label">Localidade</label>
                                <input type="text" class="form-control" id="localidadeUtilizador" name="localidadeUtilizador" placeholder="Porto">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR ACESSO
                         Credenciais e permissões aplicadas no sistema.
                         ========================================= -->
                    <div class="tab-pane fade" id="acesso" role="tabpanel" aria-labelledby="acesso-tab">
                        <h3 class="secao-ficha-titulo">Acesso ao sistema</h3>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="usernameUtilizador" class="form-label">Nome de utilizador</label>
                                <input type="text" class="form-control" id="usernameUtilizador" name="usernameUtilizador" placeholder="ana.martins" required>
                            </div>

                            <div class="col-md-4">
                                <label for="passwordUtilizador" class="form-label">Password temporária</label>
                                <input type="password" class="form-control" id="passwordUtilizador" name="passwordUtilizador" required>
                            </div>

                            <div class="col-md-4">
                                <label for="confirmarPasswordUtilizador" class="form-label">Confirmar password</label>
                                <input type="password" class="form-control" id="confirmarPasswordUtilizador" name="confirmarPasswordUtilizador" required>
                            </div>

                            <div class="col-md-4">
                                <label for="perfilAcessoUtilizador" class="form-label">Perfil de permissões</label>
                                <select class="form-select" id="perfilAcessoUtilizador" name="perfilAcessoUtilizador">
                                    <option value="">Selecionar</option>
                                    <option value="Acesso total">Acesso total</option>
                                    <option value="Gestão técnica">Gestão técnica</option>
                                    <option value="Consulta clínica">Consulta clínica</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="dataAtivacaoUtilizador" class="form-label">Data de ativação</label>
                                <input type="date" class="form-control" id="dataAtivacaoUtilizador" name="dataAtivacaoUtilizador">
                            </div>

                            <div class="col-md-4">
                                <label for="validadeAcessoUtilizador" class="form-label">Validade do acesso</label>
                                <input type="date" class="form-control" id="validadeAcessoUtilizador" name="validadeAcessoUtilizador">
                            </div>

                            <!-- =================================
                                 PERMISSÕES POR MENU
                                 Define que módulos aparecem para este utilizador.
                                 ================================= -->
                            <div class="col-12">
                                <label class="form-label">Acessos aos menus do sistema</label>

                                <div class="permissoes-utilizador-opcoes">
                                    <div class="form-check permissao-utilizador-item">
                                        <input class="form-check-input permissao-utilizador" type="checkbox" id="acessoDashboardUtilizador" name="permissoesUtilizador[]" value="dashboard">
                                        <label class="form-check-label" for="acessoDashboardUtilizador">
                                            <i class="fa-solid fa-chart-line me-2"></i> Dashboard Técnico
                                        </label>
                                    </div>

                                    <div class="form-check permissao-utilizador-item">
                                        <input class="form-check-input permissao-utilizador" type="checkbox" id="acessoEquipamentosUtilizador" name="permissoesUtilizador[]" value="equipamentos">
                                        <label class="form-check-label" for="acessoEquipamentosUtilizador">
                                            <i class="fa-solid fa-stethoscope me-2"></i> Equipamentos
                                        </label>
                                    </div>

                                    <div class="form-check permissao-utilizador-item">
                                        <input class="form-check-input permissao-utilizador" type="checkbox" id="acessoCalibracoesUtilizador" name="permissoesUtilizador[]" value="calibracoes">
                                        <label class="form-check-label" for="acessoCalibracoesUtilizador">
                                            <i class="fa-solid fa-screwdriver-wrench me-2"></i> Calibrações/Manutenções
                                        </label>
                                    </div>

                                    <div class="form-check permissao-utilizador-item">
                                        <input class="form-check-input permissao-utilizador" type="checkbox" id="acessoLocalizacoesUtilizador" name="permissoesUtilizador[]" value="localizacoes">
                                        <label class="form-check-label" for="acessoLocalizacoesUtilizador">
                                            <i class="fa-solid fa-location-dot me-2"></i> Localizações
                                        </label>
                                    </div>

                                    <div class="form-check permissao-utilizador-item">
                                        <input class="form-check-input permissao-utilizador" type="checkbox" id="acessoFornecedoresUtilizador" name="permissoesUtilizador[]" value="fornecedores">
                                        <label class="form-check-label" for="acessoFornecedoresUtilizador">
                                            <i class="fa-solid fa-truck-medical me-2"></i> Fornecedores
                                        </label>
                                    </div>

                                    <div class="form-check permissao-utilizador-item">
                                        <input class="form-check-input permissao-utilizador" type="checkbox" id="acessoUtilizadoresUtilizador" name="permissoesUtilizador[]" value="utilizadores">
                                        <label class="form-check-label" for="acessoUtilizadoresUtilizador">
                                            <i class="fa-solid fa-user me-2"></i> Utilizadores
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR SERVIÇO
                         Local de trabalho e responsabilidade interna.
                         ========================================= -->
                    <div class="tab-pane fade" id="servico" role="tabpanel" aria-labelledby="servico-tab">
                        <h3 class="secao-ficha-titulo">Serviço hospitalar</h3>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="departamentoUtilizador" class="form-label">Departamento / serviço</label>
                                <input type="text" class="form-control" id="departamentoUtilizador" name="departamentoUtilizador" placeholder="Engenharia Biomédica">
                            </div>

                            <div class="col-md-4">
                                <label for="funcaoUtilizador" class="form-label">Função</label>
                                <input type="text" class="form-control" id="funcaoUtilizador" name="funcaoUtilizador" placeholder="Responsável técnico">
                            </div>

                            <div class="col-md-4">
                                <label for="superiorUtilizador" class="form-label">Responsável hierárquico</label>
                                <input type="text" class="form-control" id="superiorUtilizador" name="superiorUtilizador" placeholder="Nome do responsável">
                            </div>

                            <div class="col-md-4">
                                <label for="edificioUtilizador" class="form-label">Edifício</label>
                                <input type="text" class="form-control" id="edificioUtilizador" name="edificioUtilizador" placeholder="Edifício A">
                            </div>

                            <div class="col-md-4">
                                <label for="pisoUtilizador" class="form-label">Piso</label>
                                <input type="text" class="form-control" id="pisoUtilizador" name="pisoUtilizador" placeholder="2">
                            </div>

                            <div class="col-md-4">
                                <label for="dataAdmissaoUtilizador" class="form-label">Data de admissão</label>
                                <input type="date" class="form-control" id="dataAdmissaoUtilizador" name="dataAdmissaoUtilizador">
                            </div>
                        </div>
                    </div>

                    <!-- =========================================
                         SEPARADOR OBSERVAÇÕES
                         Notas internas sobre o utilizador.
                         ========================================= -->
                    <div class="tab-pane fade" id="observacoes" role="tabpanel" aria-labelledby="observacoes-tab">
                        <h3 class="secao-ficha-titulo">Observações internas</h3>

                        <div class="row g-4">
                            <div class="col-12">
                                <label for="observacoesUtilizador" class="form-label">Observações</label>
                                <textarea class="form-control" id="observacoesUtilizador" name="observacoesUtilizador" rows="6" placeholder="Notas internas, restrições de acesso ou informação relevante."></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
