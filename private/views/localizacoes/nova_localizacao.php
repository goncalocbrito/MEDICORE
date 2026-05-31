<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

    <!-- Conteúdo principal no mesmo formato do novo equipamento. -->
    <main class="conteudo-private ficha-equipamento-page novo-equipamento-page ficha-localizacao-page">

        <!-- Botões principais do formulário. -->
        <div class="form-actions">
            <a href="lista_localizacoes.html" class="btn btn-cancelar">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar
            </a>

            <button type="button" class="btn btn-limpar" id="btnLimparNovaLocalizacao">
                <i class="fa-solid fa-eraser me-2"></i> Limpar
            </button>

            <button type="submit" class="btn btn-guardar" form="formNovaLocalizacao">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Localização
            </button>
        </div>

        <!-- Formulário de nova localização organizado por separadores. -->
        <form class="form-equipamento form-ficha-equipamento"
              id="formNovaLocalizacao"
              action="processa_nova_localizacao.php"
              method="post">

            <input type="hidden" name="acao" value="inserir">

            <div class="ficha-area">
                <ul class="nav nav-tabs ficha-tabs" id="tabsNovaLocalizacao" role="tablist">
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
                        <button class="nav-link" id="observacoes-tab" data-bs-toggle="tab" data-bs-target="#observacoes-tab-pane" type="button" role="tab" aria-controls="observacoes-tab-pane" aria-selected="false">
                            <i class="fa-solid fa-clipboard-list me-2"></i> Observações
                        </button>
                    </li>
                </ul>

                <div class="tab-content ficha-tab-content" id="tabsNovaLocalizacaoContent">
                    <!-- Separador 1: identificação da localização. -->
                    <div class="tab-pane fade show active" id="identificacao" role="tabpanel" aria-labelledby="identificacao-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Identificação da Localização</h4>
                            <p>Preencha os dados principais do espaço hospitalar.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="departamentoLocalizacao" class="form-label">Departamento / Serviço *</label>
                                <select class="form-select" id="departamentoLocalizacao" name="departamentoLocalizacao" required>
                                    <option value="">Selecionar departamento</option>
                                    <option value="Urgência">Urgência</option>
                                    <option value="Unidade de Cuidados Intensivos">Unidade de Cuidados Intensivos</option>
                                    <option value="Bloco Operatório">Bloco Operatório</option>
                                    <option value="Cardiologia">Cardiologia</option>
                                    <option value="Radiologia">Radiologia</option>
                                    <option value="Laboratório Clínico">Laboratório Clínico</option>
                                    <option value="Consulta Externa">Consulta Externa</option>
                                    <option value="Pediatria">Pediatria</option>
                                    <option value="Neonatologia">Neonatologia</option>
                                    <option value="Medicina Interna">Medicina Interna</option>
                                    <option value="Cirurgia Geral">Cirurgia Geral</option>
                                    <option value="Central de Esterilização">Central de Esterilização</option>
                                    <option value="Armazém Técnico">Armazém Técnico</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="tipoEspaco" class="form-label">Tipo de Espaço *</label>
                                <select class="form-select" id="tipoEspaco" name="tipoEspaco" required>
                                    <option value="">Selecionar tipo de espaço</option>
                                    <option value="UCI">Unidade de Cuidados Intensivos</option>
                                    <option value="Urgência">Urgência</option>
                                    <option value="Bloco Operatório">Bloco Operatório</option>
                                    <option value="Laboratório">Laboratório</option>
                                    <option value="Consulta Externa">Consulta Externa</option>
                                    <option value="Armazém Técnico">Armazém Técnico</option>
                                    <option value="Sala de Equipamentos">Sala de Equipamentos</option>
                                    <option value="Esterilização">Esterilização</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="edificioLocalizacao" class="form-label">Edifício *</label>
                                <input type="text" class="form-control" id="edificioLocalizacao" name="edificioLocalizacao" placeholder="Ex: Edifício A" required>
                            </div>

                            <div class="col-md-2">
                                <label for="pisoLocalizacao" class="form-label">Piso *</label>
                                <input type="text" class="form-control" id="pisoLocalizacao" name="pisoLocalizacao" placeholder="Ex: 2" required>
                            </div>

                            <div class="col-md-3">
                                <label for="salaLocalizacao" class="form-label">Sala *</label>
                                <input type="text" class="form-control" id="salaLocalizacao" name="salaLocalizacao" placeholder="Ex: Sala 201" required>
                            </div>

                            <div class="col-md-3">
                                <label for="estadoLocalizacao" class="form-label">Estado *</label>
                                <select class="form-select" id="estadoLocalizacao" name="estadoLocalizacao" required>
                                    <option value="">Selecionar estado</option>
                                    <option value="Ativa">Ativa</option>
                                    <option value="Inativa">Inativa</option>
                                    <option value="Em manutenção">Em manutenção</option>
                                    <option value="Indisponível">Indisponível</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Separador 2: responsável e contacto. -->
                    <div class="tab-pane fade" id="responsavel" role="tabpanel" aria-labelledby="responsavel-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Responsável e Contacto Interno</h4>
                            <p>Indique o responsável pelo serviço ou espaço.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="responsavelLocalizacao" class="form-label">Responsável pelo Serviço</label>
                                <input type="text" class="form-control" id="responsavelLocalizacao" name="responsavelLocalizacao" placeholder="Ex: Enf. Maria Costa">
                            </div>

                            <div class="col-md-4">
                                <label for="funcaoResponsavelLocalizacao" class="form-label">Função / Cargo</label>
                                <input type="text" class="form-control" id="funcaoResponsavelLocalizacao" name="funcaoResponsavelLocalizacao" placeholder="Ex: Enfermeira Responsável">
                            </div>

                            <div class="col-md-4">
                                <label for="contactoInternoLocalizacao" class="form-label">Contacto Interno</label>
                                <input type="text" class="form-control" id="contactoInternoLocalizacao" name="contactoInternoLocalizacao" placeholder="Ex: Ext. 2201">
                            </div>

                            <div class="col-md-6">
                                <label for="emailResponsavelLocalizacao" class="form-label">Email do Responsável</label>
                                <input type="email" class="form-control" id="emailResponsavelLocalizacao" name="emailResponsavelLocalizacao" placeholder="Ex: responsavel@hospital.pt">
                            </div>

                            <div class="col-md-6">
                                <label for="observacaoContactoLocalizacao" class="form-label">Notas de Contacto</label>
                                <input type="text" class="form-control" id="observacaoContactoLocalizacao" name="observacaoContactoLocalizacao" placeholder="Ex: Contactar em horário de serviço">
                            </div>
                        </div>
                    </div>

                    <!-- Separador 3: características técnicas. -->
                    <div class="tab-pane fade" id="caracteristicas" role="tabpanel" aria-labelledby="caracteristicas-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Características da Localização</h4>
                            <p>Defina acesso, criticidade e possibilidade de alojar equipamentos críticos.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="acessoLocalizacao" class="form-label">Acesso à Localização</label>
                                <select class="form-select" id="acessoLocalizacao" name="acessoLocalizacao">
                                    <option value="">Selecionar acesso</option>
                                    <option value="Livre">Livre</option>
                                    <option value="Restrito">Restrito</option>
                                    <option value="Apenas pessoal autorizado">Apenas pessoal autorizado</option>
                                    <option value="Acesso técnico">Acesso técnico</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="criticidadeLocalizacao" class="form-label">Criticidade da Área</label>
                                <select class="form-select" id="criticidadeLocalizacao" name="criticidadeLocalizacao">
                                    <option value="">Selecionar criticidade</option>
                                    <option value="Baixa">Baixa</option>
                                    <option value="Média">Média</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Crítica">Crítica</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="capacidadeEquipamentos" class="form-label">Capacidade Estimada de Equipamentos</label>
                                <input type="number" class="form-control" id="capacidadeEquipamentos" name="capacidadeEquipamentos" min="0" placeholder="Ex: 10">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Permite equipamentos críticos?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permiteCriticos" id="permiteCriticosSim" value="Sim" checked>
                                    <label class="form-check-label" for="permiteCriticosSim">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permiteCriticos" id="permiteCriticosNao" value="Não">
                                    <label class="form-check-label" for="permiteCriticosNao">Não</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Área com suporte de vida?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="suporteVidaLocalizacao" id="suporteVidaSim" value="Sim">
                                    <label class="form-check-label" for="suporteVidaSim">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="suporteVidaLocalizacao" id="suporteVidaNao" value="Não" checked>
                                    <label class="form-check-label" for="suporteVidaNao">Não</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador 4: indicadores iniciais. -->
                    <div class="tab-pane fade" id="indicadores" role="tabpanel" aria-labelledby="indicadores-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Indicadores Iniciais</h4>
                            <p>Registe capacidade e número inicial de equipamentos previstos.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="equipamentosPrevistos" class="form-label">Nº Inicial de Equipamentos</label>
                                <input type="number" class="form-control" id="equipamentosPrevistos" name="equipamentosPrevistos" min="0" placeholder="Ex: 8">
                            </div>

                            <div class="col-md-4">
                                <label for="ocupacaoLocalizacao" class="form-label">Ocupação Inicial</label>
                                <input type="text" class="form-control" id="ocupacaoLocalizacao" name="ocupacaoLocalizacao" placeholder="Ex: 80%">
                            </div>
                        </div>
                    </div>

                    <!-- Separador 5: observações. -->
                    <div class="tab-pane fade" id="observacoes-tab-pane" role="tabpanel" aria-labelledby="observacoes-tab" tabindex="0">
                        <div class="secao-ficha-titulo">
                            <h4>Observações</h4>
                            <p>Campo livre para contexto, acessos, limitações ou condições técnicas.</p>
                        </div>

                        <textarea class="form-control" id="observacoesLocalizacao" name="observacoesLocalizacao" rows="7" placeholder="Indique observações relevantes sobre a localização, acessos, limitações, equipamentos previstos ou condições técnicas do espaço."></textarea>
                    </div>
                </div>
            </div>
        </form>
    </main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>