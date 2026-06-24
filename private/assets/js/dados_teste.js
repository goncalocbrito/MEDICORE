// Preenchimento automático de formulários para testes — apenas ambiente de desenvolvimento

(function () {

    function set(id, val) {
        var el = document.getElementById(id);
        if (!el || el.readOnly || el.disabled) return;
        el.value = val;
        el.dispatchEvent(new Event('input',  { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setName(name, val) {
        var el = document.querySelector('[name="' + name + '"]');
        if (!el || el.readOnly || el.disabled) return;
        el.value = val;
        el.dispatchEvent(new Event('input',  { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setSelect(id, val) {
        var el = document.getElementById(id) || document.querySelector('[name="' + id + '"]');
        if (!el) return;
        el.value = val;
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setRadio(name, val) {
        var el = document.querySelector('input[type="radio"][name="' + name + '"][value="' + val + '"]');
        if (el) { el.checked = true; el.dispatchEvent(new Event('change', { bubbles: true })); }
    }

    // Seleciona o primeiro item visível numa lista de pesquisa custom (pesquisa-registo-custom)
    function clickPrimeiraOpcao(listaId) {
        var lista = document.getElementById(listaId);
        if (!lista) return;
        var opcao = lista.querySelector('.opcao-registo-custom');
        if (opcao) opcao.click();
    }

    // Data relativa a hoje + N dias, formato YYYY-MM-DD
    function dataRelativa(dias) {
        var d = new Date();
        d.setDate(d.getDate() + dias);
        return d.toISOString().split('T')[0];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NOVO FORNECEDOR (multi-etapa: identificacao → contactos → morada)
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novoFornecedor = function () {
        var etapa = (document.querySelector('[name="etapa_atual"]') || {}).value || 'identificacao';

        if (etapa === 'identificacao') {
            setName('nomeFornecedor',  'Philips Healthcare Portugal, Lda.');
            setName('nifFornecedor',   '508123456');
            setSelect('tipoFornecedor',   'Fabricante');
            setSelect('estadoFornecedor', 'Ativo');
        } else if (etapa === 'contactos') {
            setName('telefoneFornecedor',      '214123400');
            setName('emailEmpresaFornecedor',  'geral@philips-healthcare.pt');
            setName('contactoResponsavel',     'Miguel Ferreira');
            setName('telefoneContacto',        '912345678');
            setName('emailContacto',           'miguel.ferreira@philips-healthcare.pt');
        } else if (etapa === 'morada') {
            setName('moradaFornecedor',       'Rua da Quinta do Paizinho, 7');
            setName('codigoPostalFornecedor', '2790-155');
            setName('localidadeFornecedor',   'Carnaxide');
            setName('paisFornecedor',         'Portugal');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVO UTILIZADOR (multi-etapa: identificacao → contactos → acesso)
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novoUtilizador = function () {
        var etapa = (document.querySelector('[name="etapa_atual"]') || {}).value || 'identificacao';

        if (etapa === 'identificacao') {
            set('nomeUtilizador',           'Ana Cristina Rodrigues');
            setSelect('tipoUtilizador',     'Engenheiro');
            setSelect('estadoUtilizador',   'Ativo');
            set('cartaoCidadaoUtilizador',  '12345678 9 ZY5');
            set('nifUtilizador',            '253412871');
            set('dataNascimentoUtilizador', '1988-03-14');
        } else if (etapa === 'contactos') {
            set('emailUtilizador',       'ana.rodrigues@medicore.pt');
            set('telefoneUtilizador',    '918765432');
            set('localidadeUtilizador',  'Lisboa');
            set('moradaUtilizador',      'Avenida da República, 45, 3.º Dto.');
            set('codigoPostalUtilizador','1050-187');
        } else if (etapa === 'acesso') {
            set('usernameUtilizador',           'ana.rodrigues');
            set('passwordUtilizador',           'Medicore@2024');
            set('confirmarPasswordUtilizador',  'Medicore@2024');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVA LOCALIZAÇÃO (multi-etapa: identificacao → caracteristicas → observacoes)
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novaLocalizacao = function () {
        var etapa = (document.querySelector('[name="etapa_atual"]') || {}).value || 'identificacao';

        if (etapa === 'identificacao') {
            setName('departamentoNome',    'Bloco Operatório Central');
            setName('departamentoSigla',   'BOC');
            setName('codigoLocalizacao',   'BOC-01');
            setName('edificioLocalizacao', 'Edifício Principal');
            setName('pisoLocalizacao',     '2');
            setName('salaLocalizacao',     '201');
            setSelect('estadoLocalizacao', 'Ativa');
        } else if (etapa === 'caracteristicas') {
            setSelect('tipoEspaco', 'Bloco Operatório');
            set('capacidadeEquipamentos', '12');
            setRadio('permiteCriticos', '1');
        } else if (etapa === 'observacoes') {
            setName('observacoesLocalizacao', 'Sala de bloco operatório com controlo de temperatura e ambiente estéril. Capacidade para equipamentos críticos de monitorização e suporte de vida.');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVA FAMÍLIA DE EQUIPAMENTOS
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novaFamilia = function () {
        set('codigoFamilia', '04');
        set('nomeFamilia',   'Monitores de Sinais Vitais');
        set('descricaoFamilia', 'Família destinada a monitores multiparamétricos, monitores de sinais vitais e sistemas de monitorização clínica contínua de doentes internados e em bloco operatório.');
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVO EQUIPAMENTO (multi-etapa: identificacao → estado_localizacao → aquisicao → fornecedores → observacoes)
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novoEquipamento = function () {
        var etapa = (document.querySelector('[name="etapa_atual"]') || {}).value || 'identificacao';

        if (etapa === 'identificacao') {
            var selFam = document.getElementById('idFamiliaEquipamento');
            if (selFam && !selFam.value) {
                for (var i = 0; i < selFam.options.length; i++) {
                    if (selFam.options[i].value) { selFam.value = selFam.options[i].value; selFam.dispatchEvent(new Event('change')); break; }
                }
            }
            set('nomeEquipamento', 'Monitor de Sinais Vitais');
            set('modelo',          'IntelliVue MX450');
            set('marca',           'Philips');
            set('numeroSerie',     'SN-MX450-2024-001');
        } else if (etapa === 'estado_localizacao') {
            clickPrimeiraOpcao('listaLocalizacoes');
            setSelect('estado',                 'ativo');
            setSelect('criticidade',            'critica');
            setSelect('periodicidadeManutencao','semestral');
            setSelect('periodicidadeCalibracao','anual');
        } else if (etapa === 'aquisicao') {
            clickPrimeiraOpcao('listaResponsaveis');
            set('dataFabrico',    '2021-06-15');
            set('dataAquisicao',  '2022-01-20');
            set('dataInstalacao', '2022-02-01');
            var valEl = document.querySelector('[name="valorAquisicao"]');
            if (valEl && valEl.type !== 'hidden' && !valEl.disabled) valEl.value = '18500.00';
        } else if (etapa === 'fornecedores') {
            clickPrimeiraOpcao('listaFornecedores');
            set('dataInicioGarantia',   '2022-02-01');
            set('dataFimGarantia',      '2025-01-31');
        } else if (etapa === 'observacoes') {
            set('observacoes', 'Equipamento de monitorização contínua de sinais vitais. Instalado no Bloco Operatório Central para uso intraoperatório. Requer calibração anual pelo fabricante e manutenção preventiva semestral.');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVA AVARIA
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novaAvaria = function () {
        clickPrimeiraOpcao('listaEquipamentosAvaria');
        setName('descricao_avaria', 'O equipamento apresenta alarme persistente de falha de sensor. O ecrã tátil não responde corretamente na zona inferior e o sinal ECG aparece com artefactos. Situação detetada durante ronda de verificação matinal pelo técnico de engenharia biomédica.');
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVO PROCESSO (calibração / manutenção) — modal
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novoProcesso = function () {
        clickPrimeiraOpcao('listaEquipamentosProcesso');
        setSelect('tipoProcesso',   'manutencao_preventiva');
        setSelect('tipoExecucao',   'externa');
        set('dataPrevista', dataRelativa(30));
        setName('observacoesProcesso', 'Manutenção preventiva semestral programada. Incluir verificação de cabos, sensores e teste de alarmes. Equipamento disponível para recolha às 08h00.');
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVO EMPRÉSTIMO — modal
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novoEmprestimo = function () {
        clickPrimeiraOpcao('listaEquipamentosEmprestimo');
        set('dataInicioEmprestimo',    dataRelativa(0));
        set('dataDevolucaoEmprestimo', dataRelativa(14));
        set('motivoEmprestimo',    'Substituição temporária para o serviço de urgência enquanto o equipamento principal se encontra em manutenção preventiva programada.');
        set('observacoesEmprestimo','Equipamento entregue com todos os acessórios e cabos. Devolver no mesmo estado de conservação.');
    };

    // ─────────────────────────────────────────────────────────────────────────
    // NOVA TRANSFERÊNCIA — modal
    // ─────────────────────────────────────────────────────────────────────────
    window.dadosTeste_novaTransferencia = function () {
        clickPrimeiraOpcao('listaEquipamentosTransferencia');
        setName('motivo',     'Reorganização de equipamentos entre serviços por necessidade clínica. O serviço de destino necessita de reforço de meios de monitorização.');
        setName('observacoes','Transferência aprovada pela coordenação de engenharia biomédica. Registar saída e entrada com verificação de estado do equipamento.');
    };

    // -------------------------------------------------------------------------
    // ADICIONAR ACESSÓRIO — modal em acessorios.php
    // -------------------------------------------------------------------------
    window.dadosTeste_novoAcessorio = function () {
        set('designacaoAcessorioBD',    'Sensor SpO2 adulto reutilizável');
        set('dataAquisicaoAcessorioBD', dataRelativa(-30));
        setSelect('tipoAcessorioBD',    'sensor');

        set('modeloAcessorioBD',       'OXI-MAX MAX-A');
        set('numeroSerieAcessorioBD',  'SN-SPO2-2024-001');
        setSelect('estadoAcessorioBD', 'ativo');
        set('dataInicioGarantiaBD',    dataRelativa(-30));
        set('dataFimGarantiaBD',       dataRelativa(335));
        set('observacoesAcessorioBD',  'Sensor de oximetria de pulso para adulto. Compatível com monitor IntelliVue. Substituir ao fim de 1 ano de uso contínuo.');
    };

    // -------------------------------------------------------------------------
    // ADICIONAR CONSUMÍVEL — modal em consumiveis.php
    // -------------------------------------------------------------------------
    window.dadosTeste_novoConsumivel = function () {
        set('nomeConsumivelBD',            'Elétrodos descartáveis ECG');
        setSelect('categoriaConsumivelBD', 'eletrodos');
        set('stockInicialConsumivelBD',    '50');
        set('stockMinimoConsumivelBD',     '10');
        set('stockMaximoConsumivelBD',     '100');
        set('precoUnitarioConsumivelBD',   '0.35');

        var primeiraOpcaoCon = document.querySelector('#listaFornecedoresConsumivelBD .opcao-fornecedor-custom');
        if (primeiraOpcaoCon) primeiraOpcaoCon.click();

        set('observacoesConsumivelBD', 'Elétrodos descartáveis para monitorização ECG contínua. Validade de 2 anos. Repor stock quando atingir o mínimo de 10 unidades.');
    };

})();
