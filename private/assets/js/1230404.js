// JavaScript Gonçalo Brito 1230404
// Funcionalidades da área privada MEDICORE

document.addEventListener("DOMContentLoaded", function () {

    // Ativar popovers do Bootstrap
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');

    popoverTriggerList.forEach(function (popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl, {
            container: "body"
        });
    });

});

document.addEventListener("DOMContentLoaded", function () {

    // Ativar popovers do Bootstrap
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');

    popoverTriggerList.forEach(function (popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl, {
            container: "body"
        });
    });


    // Descrição automática da criticidade
    const campoCriticidade = document.getElementById("criticidade");
    const descricaoCriticidade = document.getElementById("descricaoCriticidade");

    if (campoCriticidade && descricaoCriticidade) {

        campoCriticidade.addEventListener("change", function () {

            const valor = campoCriticidade.value;

            if (valor === "baixa") {
                descricaoCriticidade.textContent = "Baixa: falha com impacto reduzido. Exemplos: balança clínica, termómetro digital ou otoscópio.";
            } 
            else if (valor === "media") {
                descricaoCriticidade.textContent = "Média: pode atrasar o serviço, mas existem alternativas. Exemplos: eletrocardiógrafo de rotina, aspirador portátil ou equipamento de fisioterapia.";
            } 
            else if (valor === "alta") {
                descricaoCriticidade.textContent = "Alta: impacto significativo na prestação de cuidados. Exemplos: monitor multiparamétrico de urgência, ecógrafo ou incubadora neonatal.";
            } 
            else if (valor === "critica") {
                descricaoCriticidade.textContent = "Crítica: equipamento essencial para suporte de vida ou emergência. Exemplos: ventilador pulmonar, desfibrilhador ou máquina de anestesia.";
            } 
            else {
                descricaoCriticidade.textContent = "Selecione uma criticidade para ver a descrição.";
            }

        });
    }

});

// Dados temporários dos equipamentos
// Alteração feita por mim: permite preencher a página detalhes_equipamento.html através do parâmetro id no URL

document.addEventListener("DOMContentLoaded", function () {

    const equipamentos = {
        "EQ-001": {
            nome: "Monitor Multiparamétrico",
            codigo: "EQ-001",
            categoria: "Monitorização",
            fabricante: "Philips",
            modelo: "IntelliVue MX450",
            serie: "SN-MX450-2024",
            departamento: "Unidade de Cuidados Intensivos",
            localizacao: "UCI - Sala 2",
            estado: "Ativo",
            estadoClasse: "estado-ativo",
            criticidade: "Crítica",
            operacional: "Operacional",
            fornecedor: "MedSupply Portugal",
            aquisicao: "15/01/2024",
            instalacao: "20/01/2024",
            garantia: "20/01/2027",
            ultimaManutencao: "12/03/2026",
            proximaManutencao: "12/09/2026",
            periodicidade: "Semestral",
            responsavel: "Eng. Gonçalo Brito",
            observacoes: "Equipamento essencial para monitorização contínua de parâmetros vitais em contexto de cuidados intensivos."
        },

        "EQ-002": {
            nome: "Ventilador Pulmonar",
            codigo: "EQ-002",
            categoria: "Suporte de Vida",
            fabricante: "Dräger",
            modelo: "Evita V300",
            serie: "SN-EV300-1198",
            departamento: "Urgência",
            localizacao: "Urgência - Sala 1",
            estado: "Em manutenção",
            estadoClasse: "estado-manutencao",
            criticidade: "Crítica",
            operacional: "Não operacional",
            fornecedor: "Biomedical Solutions",
            aquisicao: "10/06/2023",
            instalacao: "18/06/2023",
            garantia: "18/06/2026",
            ultimaManutencao: "28/02/2026",
            proximaManutencao: "28/08/2026",
            periodicidade: "Semestral",
            responsavel: "Eng. Gonçalo Brito",
            observacoes: "Equipamento em manutenção preventiva. Deve ser validado antes de regressar ao serviço clínico."
        },

        "EQ-003": {
            nome: "Desfibrilhador",
            codigo: "EQ-003",
            categoria: "Emergência",
            fabricante: "Zoll",
            modelo: "R Series",
            serie: "SN-ZOLL-8821",
            departamento: "Bloco Operatório",
            localizacao: "Bloco Operatório",
            estado: "Avariado",
            estadoClasse: "estado-avariado",
            criticidade: "Crítica",
            operacional: "Não operacional",
            fornecedor: "ClinicalTech Equipamentos",
            aquisicao: "02/09/2022",
            instalacao: "08/09/2022",
            garantia: "08/09/2025",
            ultimaManutencao: "05/01/2026",
            proximaManutencao: "Por definir",
            periodicidade: "Anual",
            responsavel: "Eng. Gonçalo Brito",
            observacoes: "Equipamento sinalizado como avariado. Deve permanecer indisponível até avaliação técnica e reparação."
        }
    };

    const paginaDetalhes = document.getElementById("detalheNome");

    if (paginaDetalhes) {
        const parametros = new URLSearchParams(window.location.search);
        const idEquipamento = parametros.get("id");

        const equipamento = equipamentos[idEquipamento];

        if (!equipamento) {
            document.getElementById("detalheNome").textContent = "Equipamento não encontrado";
            document.getElementById("detalheTituloEquipamento").textContent = "Sem dados disponíveis";
            document.getElementById("detalheResumo").textContent = "Não foi possível encontrar dados para o equipamento selecionado.";
            return;
        }

        document.getElementById("detalheNome").textContent = "Detalhes do Equipamento";
        document.getElementById("detalheTituloEquipamento").textContent = equipamento.nome;
        document.getElementById("detalheResumo").textContent = `${equipamento.nome} localizado em ${equipamento.localizacao}, atualmente com estado ${equipamento.estado}.`;

        document.getElementById("detalheCodigo").textContent = equipamento.codigo;
        document.getElementById("detalheCategoria").textContent = equipamento.categoria;
        document.getElementById("detalheFabricante").textContent = equipamento.fabricante;
        document.getElementById("detalheModelo").textContent = equipamento.modelo;
        document.getElementById("detalheSerie").textContent = equipamento.serie;

        document.getElementById("detalheDepartamento").textContent = equipamento.departamento;
        document.getElementById("detalheLocalizacao").textContent = equipamento.localizacao;
        document.getElementById("detalheEstadoTexto").textContent = equipamento.estado;
        document.getElementById("detalheCriticidadeTexto").textContent = equipamento.criticidade;

        document.getElementById("detalheFornecedor").textContent = equipamento.fornecedor;
        document.getElementById("detalheAquisicao").textContent = equipamento.aquisicao;
        document.getElementById("detalheInstalacao").textContent = equipamento.instalacao;
        document.getElementById("detalheGarantia").textContent = equipamento.garantia;

        document.getElementById("detalheUltimaManutencao").textContent = equipamento.ultimaManutencao;
        document.getElementById("detalheProximaManutencao").textContent = equipamento.proximaManutencao;
        document.getElementById("detalhePeriodicidade").textContent = equipamento.periodicidade;
        document.getElementById("detalheResponsavel").textContent = equipamento.responsavel;

        document.getElementById("detalheObservacoes").textContent = equipamento.observacoes;

        const estadoBadge = document.getElementById("detalheEstado");
        estadoBadge.textContent = equipamento.estado;
        estadoBadge.className = `estado ${equipamento.estadoClasse}`;

        document.getElementById("detalheCriticidade").textContent = `Criticidade: ${equipamento.criticidade}`;
        document.getElementById("detalheOperacional").textContent = equipamento.operacional;
    }

});