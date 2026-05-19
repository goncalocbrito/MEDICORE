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

// Temporário
// Adicionar vários documentos ao formulário de novo equipamento

document.addEventListener("DOMContentLoaded", function () {

    const btnAdicionarDocumento = document.getElementById("btnAdicionarDocumento");
    const listaDocumentos = document.getElementById("listaDocumentos");

    function atualizarBotoesRemoverDocumento() {
        if (!listaDocumentos) return;

        const documentos = listaDocumentos.querySelectorAll(".documento-form-item");
        const botoesRemover = listaDocumentos.querySelectorAll(".btn-remover-documento");

        botoesRemover.forEach(function (botao) {
            if (documentos.length <= 1) {
                botao.style.visibility = "hidden";
                botao.disabled = true;
            } else {
                botao.style.visibility = "visible";
                botao.disabled = false;
            }
        });
    }

    if (btnAdicionarDocumento && listaDocumentos) {

        atualizarBotoesRemoverDocumento();

        btnAdicionarDocumento.addEventListener("click", function () {

            const novoDocumento = document.createElement("div");
            novoDocumento.classList.add("documento-form-item");

            novoDocumento.innerHTML = `
                <div class="row g-4 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label">Tipo de Documento</label>
                        <select class="form-select" name="tipoDocumento[]">
                            <option value="">Selecionar tipo</option>
                            <option value="fotografia">Fotografia do Equipamento</option>
                            <option value="manual">Manual de Instruções</option>
                            <option value="certificado_calibracao">Certificado de Calibração</option>
                            <option value="certificado_manutencao">Certificado de Manutenção</option>
                            <option value="ficha_tecnica">Ficha Técnica</option>
                            <option value="garantia">Documento de Garantia</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nome do Documento</label>
                        <input type="text"
                               class="form-control"
                               name="nomeDocumento[]"
                               placeholder="Ex: Certificado de calibração 2026">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Ficheiro</label>
                        <input type="file"
                               class="form-control"
                               name="ficheiroDocumento[]">
                    </div>

                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-remover-documento" title="Remover documento">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>

                </div>
            `;

            listaDocumentos.appendChild(novoDocumento);
            atualizarBotoesRemoverDocumento();
        });


        listaDocumentos.addEventListener("click", function (event) {

            const botaoRemover = event.target.closest(".btn-remover-documento");

            if (botaoRemover) {
                const documento = botaoRemover.closest(".documento-form-item");
                documento.remove();
                atualizarBotoesRemoverDocumento();
            }

        });

    }

});

// Preencher formulário de edição de equipamento
// Alteração feita por mim

document.addEventListener("DOMContentLoaded", function () {

    const formEditar = document.getElementById("formEditarEquipamento");

    if (!formEditar) return;

    const equipamentosEditar = {
        "EQ-001": {
            codigo: "EQ-001",
            nome: "Monitor Multiparamétrico",
            categoria: "Monitorização",
            fabricante: "Philips",
            modelo: "IntelliVue MX450",
            serie: "SN-MX450-2024",
            departamento: "Unidade de Cuidados Intensivos",
            edificio: "Edifício A",
            piso: "2",
            sala: "Sala 2",
            estado: "Ativo",
            criticidade: "Crítica",
            operacional: "Operacional",
            fornecedor: "MedSupply Portugal",
            dataFabrico: "2023-11-10",
            dataAquisicao: "2024-01-15",
            dataInstalacao: "2024-01-20",
            valorAquisicao: "3500.00",
            fimGarantia: "2027-01-20",
            contratoManutencao: "Sim",
            ultimaManutencao: "2026-03-12",
            ultimaCalibracao: "2026-03-12",
            proximaCalibracao: "2026-09-12",
            periodicidade: "Semestral",
            responsavelTecnico: "Eng. Gonçalo Brito",
            observacoes: "Equipamento em funcionamento normal, localizado na UCI."
        },

        "EQ-002": {
            codigo: "EQ-002",
            nome: "Ventilador Pulmonar",
            categoria: "Suporte de Vida",
            fabricante: "Dräger",
            modelo: "Evita V300",
            serie: "SN-EV300-1198",
            departamento: "Urgência",
            edificio: "Edifício B",
            piso: "0",
            sala: "Sala 1",
            estado: "Em manutenção",
            criticidade: "Crítica",
            operacional: "Não operacional",
            fornecedor: "Biomedical Solutions",
            dataFabrico: "2022-12-05",
            dataAquisicao: "2023-06-10",
            dataInstalacao: "2023-06-18",
            valorAquisicao: "12500.00",
            fimGarantia: "2026-06-18",
            contratoManutencao: "Sim",
            ultimaManutencao: "2026-02-28",
            ultimaCalibracao: "2026-02-28",
            proximaCalibracao: "2026-08-28",
            periodicidade: "Semestral",
            responsavelTecnico: "Eng. Gonçalo Brito",
            observacoes: "Equipamento em manutenção preventiva."
        },

        "EQ-003": {
            codigo: "EQ-003",
            nome: "Desfibrilhador",
            categoria: "Emergência",
            fabricante: "Zoll",
            modelo: "R Series",
            serie: "SN-ZOLL-8821",
            departamento: "Bloco Operatório",
            edificio: "Edifício C",
            piso: "1",
            sala: "Bloco Operatório",
            estado: "Avariado",
            criticidade: "Crítica",
            operacional: "Não operacional",
            fornecedor: "ClinicalTech Equipamentos",
            dataFabrico: "2021-05-20",
            dataAquisicao: "2022-09-02",
            dataInstalacao: "2022-09-08",
            valorAquisicao: "8900.00",
            fimGarantia: "2025-09-08",
            contratoManutencao: "Em análise",
            ultimaManutencao: "2026-01-05",
            ultimaCalibracao: "2026-01-05",
            proximaCalibracao: "",
            periodicidade: "Anual",
            responsavelTecnico: "Eng. Gonçalo Brito",
            observacoes: "Equipamento sinalizado como avariado. Deve permanecer indisponível até avaliação técnica."
        }
    };

    const parametros = new URLSearchParams(window.location.search);
    const idEquipamento = parametros.get("id");
    const equipamento = equipamentosEditar[idEquipamento];

    if (!equipamento) {
        alert("Equipamento não encontrado.");
        window.location.href = "lista_equipamentos.html";
        return;
    }

    document.getElementById("codigoInventario").value = equipamento.codigo;
    document.getElementById("nomeEquipamento").value = equipamento.nome;
    document.getElementById("categoria").value = equipamento.categoria;
    document.getElementById("fabricante").value = equipamento.fabricante;
    document.getElementById("modelo").value = equipamento.modelo;
    document.getElementById("numeroSerie").value = equipamento.serie;

    document.getElementById("departamento").value = equipamento.departamento;
    document.getElementById("edificio").value = equipamento.edificio;
    document.getElementById("piso").value = equipamento.piso;
    document.getElementById("sala").value = equipamento.sala;
    document.getElementById("estado").value = equipamento.estado;
    document.getElementById("criticidade").value = equipamento.criticidade;

    if (equipamento.operacional === "Operacional") {
        document.getElementById("operacionalSim").checked = true;
    } else {
        document.getElementById("operacionalNao").checked = true;
    }

    document.getElementById("fornecedor").value = equipamento.fornecedor;
    document.getElementById("dataFabrico").value = equipamento.dataFabrico;
    document.getElementById("dataAquisicao").value = equipamento.dataAquisicao;
    document.getElementById("dataInstalacao").value = equipamento.dataInstalacao;
    document.getElementById("valorAquisicao").value = equipamento.valorAquisicao;
    document.getElementById("fimGarantia").value = equipamento.fimGarantia;
    document.getElementById("contratoManutencao").value = equipamento.contratoManutencao;

    document.getElementById("ultimaManutencao").value = equipamento.ultimaManutencao;
    document.getElementById("ultimaCalibracao").value = equipamento.ultimaCalibracao;
    document.getElementById("proximaCalibracao").value = equipamento.proximaCalibracao;
    document.getElementById("periodicidade").value = equipamento.periodicidade;
    document.getElementById("responsavelTecnico").value = equipamento.responsavelTecnico;
    document.getElementById("observacoes").value = equipamento.observacoes;

    formEditar.addEventListener("submit", function (event) {
        event.preventDefault();

        alert("Alterações registadas com sucesso.");

        window.location.href = "lista_equipamentos.html";
    });

});

// Página apagar_equipamento.html
// Preencher dados do equipamento e confirmar remoção
// TEMPORÁRIO

document.addEventListener("DOMContentLoaded", function () {

    const paginaRemover = document.getElementById("btnConfirmarRemocao");

    if (!paginaRemover) return;

    const equipamentosRemover = {
        "EQ-001": {
            codigo: "EQ-001",
            nome: "Monitor Multiparamétrico",
            categoria: "Monitorização",
            fabricante: "Philips",
            modelo: "IntelliVue MX450",
            serie: "SN-MX450-2024",
            localizacao: "UCI - Sala 2",
            estado: "Ativo",
            estadoClasse: "estado-ativo",
            criticidade: "Crítica",
            ultimaManutencao: "12/03/2026"
        },

        "EQ-002": {
            codigo: "EQ-002",
            nome: "Ventilador Pulmonar",
            categoria: "Suporte de Vida",
            fabricante: "Dräger",
            modelo: "Evita V300",
            serie: "SN-EV300-1198",
            localizacao: "Urgência - Sala 1",
            estado: "Em manutenção",
            estadoClasse: "estado-manutencao",
            criticidade: "Crítica",
            ultimaManutencao: "28/02/2026"
        },

        "EQ-003": {
            codigo: "EQ-003",
            nome: "Desfibrilhador",
            categoria: "Emergência",
            fabricante: "Zoll",
            modelo: "R Series",
            serie: "SN-ZOLL-8821",
            localizacao: "Bloco Operatório",
            estado: "Avariado",
            estadoClasse: "estado-avariado",
            criticidade: "Crítica",
            ultimaManutencao: "05/01/2026"
        }
    };

    const parametros = new URLSearchParams(window.location.search);
    const idEquipamento = parametros.get("id");
    const equipamento = equipamentosRemover[idEquipamento];

    if (!equipamento) {
        alert("Equipamento não encontrado.");
        window.location.href = "lista_equipamentos.html";
        return;
    }

    document.getElementById("removerCodigo").textContent = equipamento.codigo;
    document.getElementById("removerNome").textContent = equipamento.nome;
    document.getElementById("removerCategoria").textContent = equipamento.categoria;
    document.getElementById("removerFabricante").textContent = equipamento.fabricante;
    document.getElementById("removerModelo").textContent = equipamento.modelo;
    document.getElementById("removerSerie").textContent = equipamento.serie;
    document.getElementById("removerLocalizacao").textContent = equipamento.localizacao;
    document.getElementById("removerCriticidade").textContent = equipamento.criticidade;
    document.getElementById("removerUltimaManutencao").textContent = equipamento.ultimaManutencao;

    const estado = document.getElementById("removerEstado");
    estado.textContent = equipamento.estado;
    estado.className = `estado ${equipamento.estadoClasse}`;

    const checkbox = document.getElementById("confirmarRemocao");
    const botaoConfirmar = document.getElementById("btnConfirmarRemocao");

    checkbox.addEventListener("change", function () {
        botaoConfirmar.disabled = !checkbox.checked;
    });

    botaoConfirmar.addEventListener("click", function () {
        alert("Equipamento removido com sucesso.");

        window.location.href = "lista_equipamentos.html";
    });

});