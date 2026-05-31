<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

    <!-- Conteúdo Principal -->
    <main class="conteudo-private">

        <!-- Cabeçalho do Dashboard -->
        <section class="dashboard-header">
            <div>
                <h2>Dashboard de Gestão MEDICORE</h2>
                <p>
                    Visualização geral dos indicadores associados aos equipamentos médicos,
                    fornecedores, documentos, criticidade e localização hospitalar.
                </p>
            </div>
        </section>

        <!-- Indicadores principais -->
        <section class="dashboard-indicadores">

            <div class="indicador-card">
                <div class="indicador-icone">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>

                <div>
                    <span>Total de Equipamentos</span>
                    <h3>128</h3>
                    <p>Equipamentos registados</p>
                </div>
            </div>

            <div class="indicador-card">
                <div class="indicador-icone indicador-verde">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div>
                    <span>Equipamentos Ativos</span>
                    <h3>95</h3>
                    <p>Operacionais no hospital</p>
                </div>
            </div>

            <div class="indicador-card">
                <div class="indicador-icone indicador-amarelo">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>

                <div>
                    <span>Em Manutenção</span>
                    <h3>14</h3>
                    <p>A aguardar intervenção técnica</p>
                </div>
            </div>

            <div class="indicador-card">
                <div class="indicador-icone indicador-vermelho">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div>
                    <span>Avariados</span>
                    <h3>7</h3>
                    <p>Equipamentos indisponíveis</p>
                </div>
            </div>

        </section>

        <!-- Indicadores adicionais valorizados -->
        <section class="dashboard-indicadores dashboard-indicadores-secundarios">

            <div class="indicador-card">
                <div class="indicador-icone indicador-vermelho">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>

                <div>
                    <span>Garantia a Expirar</span>
                    <h3>5</h3>
                    <p>Nos próximos 30 dias</p>
                </div>
            </div>

            <div class="indicador-card">
                <div class="indicador-icone indicador-roxo">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div>
                    <span>Criticidade Elevada</span>
                    <h3>38</h3>
                    <p>Alta ou crítica</p>
                </div>
            </div>

            <div class="indicador-card">
                <div class="indicador-icone">
                    <i class="fa-solid fa-truck-medical"></i>
                </div>

                <div>
                    <span>Fornecedores</span>
                    <h3>24</h3>
                    <p>Entidades registadas</p>
                </div>
            </div>

            <div class="indicador-card">
                <div class="indicador-icone indicador-azul">
                    <i class="fa-solid fa-folder-open"></i>
                </div>

                <div>
                    <span>Documentos</span>
                    <h3>86</h3>
                    <p>Associados a equipamentos</p>
                </div>
            </div>

        </section>

        <!-- Gráficos -->
        <section class="dashboard-graficos">

            <div class="grafico-card">
                <h4>
                    <i class="fa-solid fa-chart-pie me-2"></i>
                    Equipamentos por Estado
                </h4>

                <canvas id="graficoEstadoEquipamentos"></canvas>
            </div>

            <div class="grafico-card">
                <h4>
                    <i class="fa-solid fa-chart-column me-2"></i>
                    Equipamentos por Categoria
                </h4>

                <canvas id="graficoCategoriaEquipamentos"></canvas>
            </div>

            <div class="grafico-card">
                <h4>
                    <i class="fa-solid fa-location-dot me-2"></i>
                    Equipamentos por Localização
                </h4>

                <canvas id="graficoLocalizacaoEquipamentos"></canvas>
            </div>

            <div class="grafico-card">
                <h4>
                    <i class="fa-solid fa-lungs me-2"></i>
                    Suporte de Vida por Serviço
                </h4>

                <canvas id="graficoSuporteVida"></canvas>
            </div>

        </section>

        <!-- Alertas técnicos -->
        <section class="dashboard-alertas">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>
                        <i class="fa-solid fa-bell me-2"></i>
                        Alertas Técnicos Recentes
                    </h4>
                    <p>
                        Indicadores relevantes para acompanhamento do engenheiro biomédico.
                    </p>
                </div>
            </div>

            <div class="table-responsive tabela-container">
                <table class="table table-hover align-middle tabela-dashboard">
                    <thead>
                        <tr>
                            <th>Tipo de Alerta</th>
                            <th>Equipamento</th>
                            <th>Localização</th>
                            <th>Criticidade</th>
                            <th>Data Limite</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Garantia a expirar</td>
                            <td>Desfibrilhador Zoll R Series</td>
                            <td>Bloco Operatório</td>
                            <td>
                                <span class="tipo-fornecedor tipo-calibracao">Crítica</span>
                            </td>
                            <td>25/06/2026</td>
                            <td>
                                <span class="estado estado-manutencao">Acompanhar</span>
                            </td>
                        </tr>

                        <tr>
                            <td>Calibração próxima</td>
                            <td>Monitor Multiparamétrico</td>
                            <td>UCI - Sala 2</td>
                            <td>
                                <span class="tipo-fornecedor tipo-calibracao">Crítica</span>
                            </td>
                            <td>12/06/2026</td>
                            <td>
                                <span class="estado estado-ativo">Planeada</span>
                            </td>
                        </tr>

                        <tr>
                            <td>Equipamento avariado</td>
                            <td>Bomba de Infusão</td>
                            <td>Urgência</td>
                            <td>
                                <span class="tipo-fornecedor tipo-manutencao">Alta</span>
                            </td>
                            <td>Urgente</td>
                            <td>
                                <span class="estado estado-avariado">Avariado</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </section>

    </main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>