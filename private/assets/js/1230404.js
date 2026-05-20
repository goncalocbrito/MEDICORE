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
        botaoConfirmar.disabled = true;

        mostrarCardConfirmacaoRemocao(
            "Equipamento",
            equipamento.nome,
            "lista_equipamentos.html"
        );
    });

});

// Dados temporários dos fornecedores

const fornecedoresMEDICORE = {
    "FOR-001": {
        nome: "Philips Medical Systems",
        tipos: ["Fabricante"],
        nif: "509123456",
        email: "suporte@philips-med.pt",
        telefone: "+351 220 000 111",
        website: "https://www.philips.pt",
        contacto: "Carlos Almeida",
        cargo: "Suporte Técnico",
        emailContacto: "carlos.almeida@philips-med.pt",
        morada: "Rua da Tecnologia Médica, 45",
        codigoPostal: "4100-000",
        localidade: "Porto",
        pais: "Portugal",
        estado: "Ativo",
        estadoClasse: "estado-ativo",
        contrato: "Sim",
        inicioContrato: "2024-01-01",
        fimContrato: "2027-01-01",
        qtdEquipamentos: "12",
        area: "Fabrico e suporte técnico de equipamentos de monitorização clínica.",
        equipamentos: "Monitores multiparamétricos Philips IntelliVue.",
        observacoes: "Fornecedor associado a equipamentos de monitorização em unidades críticas.",

        equipamentosAssociados: [
            {
                codigo: "EQ-001",
                nome: "Monitor Multiparamétrico",
                categoria: "Monitorização",
                modelo: "IntelliVue MX450",
                serie: "SN-MX450-2024",
                relacao: "Fabricante",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-004",
                nome: "Monitor de Sinais Vitais",
                categoria: "Monitorização",
                modelo: "SureSigns VS4",
                serie: "SN-VS4-2025",
                relacao: "Fabricante",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-005",
                nome: "Bomba de Infusão",
                categoria: "Terapêutica",
                modelo: "InfusionCare P200",
                serie: "SN-P200-2024",
                relacao: "Fabricante",
                estado: "Em manutenção",
                estadoClasse: "estado-manutencao"
            }
        ]
    },

    "FOR-002": {
        nome: "MedSupply Portugal",
        tipos: ["Distribuidor"],
        nif: "514987321",
        email: "comercial@medsupply.pt",
        telefone: "+351 221 234 567",
        website: "https://www.medsupply.pt",
        contacto: "Ana Martins",
        cargo: "Gestora Comercial",
        emailContacto: "ana.martins@medsupply.pt",
        morada: "Avenida dos Dispositivos Médicos, 80",
        codigoPostal: "1000-000",
        localidade: "Lisboa",
        pais: "Portugal",
        estado: "Ativo",
        estadoClasse: "estado-ativo",
        contrato: "Sim",
        inicioContrato: "2024-03-01",
        fimContrato: "2026-03-01",
        qtdEquipamentos: "8",
        area: "Venda e distribuição de dispositivos e equipamentos médicos.",
        equipamentos: "Bombas de infusão, monitores e acessórios clínicos.",
        observacoes: "Fornecedor com boa resposta comercial e disponibilidade de stock.",

        equipamentosAssociados: [
            {
                codigo: "EQ-001",
                nome: "Monitor Multiparamétrico",
                categoria: "Monitorização",
                modelo: "IntelliVue MX450",
                relacao: "Distribuidor",
                serie: "SN-XT42-2024",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-006",
                nome: "Oxímetro de Pulso",
                categoria: "Monitorização",
                modelo: "OxiPro 300",
                relacao: "Distribuidor",
                serie: "SN-LOL9-2026",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            }
        ]
    },

    "FOR-003": {
        nome: "Biomedical Solutions",
        tipos: ["Manutenção"],
        nif: "507654789",
        email: "tecnica@biomedicalsolutions.pt",
        telefone: "+351 222 456 789",
        website: "https://www.biomedicalsolutions.pt",
        contacto: "Rui Oliveira",
        cargo: "Responsável Técnico",
        emailContacto: "rui.oliveira@biomedicalsolutions.pt",
        morada: "Rua da Engenharia Biomédica, 12",
        codigoPostal: "4470-000",
        localidade: "Maia",
        pais: "Portugal",
        estado: "Ativo",
        estadoClasse: "estado-ativo",
        contrato: "Sim",
        inicioContrato: "2025-01-01",
        fimContrato: "2027-12-31",
        qtdEquipamentos: "5",
        area: "Manutenção preventiva e corretiva de equipamentos hospitalares.",
        equipamentos: "Ventiladores, monitores e equipamentos de suporte clínico.",
        observacoes: "Fornecedor responsável por manutenções técnicas periódicas."
    },

    "FOR-004": {
        nome: "CalibraMed",
        tipos: ["Calibração"],
        nif: "515321987",
        email: "calibracao@calibramed.pt",
        telefone: "+351 223 987 654",
        website: "https://www.calibramed.pt",
        contacto: "Marta Costa",
        cargo: "Técnica de Calibração",
        emailContacto: "marta.costa@calibramed.pt",
        morada: "Parque Tecnológico de Braga",
        codigoPostal: "4700-000",
        localidade: "Braga",
        pais: "Portugal",
        estado: "Inativo",
        estadoClasse: "estado-inativo",
        contrato: "Não",
        inicioContrato: "2023-01-01",
        fimContrato: "2024-12-31",
        qtdEquipamentos: "3",
        area: "Calibração e emissão de certificados técnicos.",
        equipamentos: "Equipamentos de medição, monitores e dispositivos laboratoriais.",
        observacoes: "Fornecedor inativo, mantendo apenas histórico de calibrações anteriores."
    }
};


// Detalhes do fornecedor
document.addEventListener("DOMContentLoaded", function () {

    const detalheFornecedorNome = document.getElementById("detalheFornecedorNome");

    if (!detalheFornecedorNome) return;

    const parametros = new URLSearchParams(window.location.search);
    const idFornecedor = parametros.get("id");
    const fornecedor = fornecedoresMEDICORE[idFornecedor];

    if (!fornecedor) {
        alert("Fornecedor não encontrado.");
        window.location.href = "lista_fornecedores.html";
        return;
    }

    detalheFornecedorNome.textContent = fornecedor.nome;
    document.getElementById("detalheFornecedorResumo").textContent =
        `${fornecedor.nome} é uma entidade associada a ${fornecedor.qtdEquipamentos} equipamento(s), com estado ${fornecedor.estado}.`;

    const tiposContainer = document.getElementById("detalheFornecedorTipos");
    tiposContainer.innerHTML = "";

    fornecedor.tipos.forEach(function (tipo) {
        const span = document.createElement("span");
        span.className = "badge-detalhe";
        span.textContent = tipo;
        tiposContainer.appendChild(span);
    });

    document.getElementById("detalheFornecedorNif").textContent = fornecedor.nif;
    document.getElementById("detalheFornecedorEstado").textContent = fornecedor.estado;
    document.getElementById("detalheFornecedorPais").textContent = fornecedor.pais;
    document.getElementById("detalheFornecedorLocalidade").textContent = fornecedor.localidade;

    document.getElementById("detalheFornecedorEmail").textContent = fornecedor.email;
    document.getElementById("detalheFornecedorTelefone").textContent = fornecedor.telefone;
    document.getElementById("detalheFornecedorContacto").textContent = `${fornecedor.contacto} — ${fornecedor.cargo}`;
    document.getElementById("detalheFornecedorWebsite").textContent = fornecedor.website;

    document.getElementById("detalheFornecedorContrato").textContent = fornecedor.contrato;
    document.getElementById("detalheFornecedorInicioContrato").textContent = fornecedor.inicioContrato;
    document.getElementById("detalheFornecedorFimContrato").textContent = fornecedor.fimContrato;
    document.getElementById("detalheFornecedorQtdEquipamentos").textContent = fornecedor.qtdEquipamentos;

    document.getElementById("detalheFornecedorArea").textContent = fornecedor.area;
    document.getElementById("detalheFornecedorEquipamentos").textContent = fornecedor.equipamentos;
    document.getElementById("detalheFornecedorObservacoes").textContent = fornecedor.observacoes;

    const tabelaEquipamentosFornecedor = document.getElementById("tabelaEquipamentosFornecedor");
    const totalEquipamentosFornecedor = document.getElementById("totalEquipamentosFornecedor");

    if (tabelaEquipamentosFornecedor && totalEquipamentosFornecedor) {
        tabelaEquipamentosFornecedor.innerHTML = "";

        const equipamentosAssociados = fornecedor.equipamentosAssociados || [];

        totalEquipamentosFornecedor.textContent = `${equipamentosAssociados.length} equipamento(s)`;

        if (equipamentosAssociados.length === 0) {
            tabelaEquipamentosFornecedor.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Não existem equipamentos associados a este fornecedor.
                    </td>
                </tr>
            `;
        } else {
            equipamentosAssociados.forEach(function (equipamento) {
                const linha = document.createElement("tr");

                linha.innerHTML = `
                    <td>${equipamento.codigo}</td>
                    <td>${equipamento.nome}</td>
                    <td>${equipamento.categoria}</td>
                    <td>${equipamento.modelo}</td>
                    <td>${equipamento.serie}</td>
                    <td>
                        <span class="tipo-fornecedor tipo-distribuidor">${equipamento.relacao}</span>
                    </td>
                    <td>
                        <span class="estado ${equipamento.estadoClasse}">${equipamento.estado}</span>
                    </td>
                `;

                tabelaEquipamentosFornecedor.appendChild(linha);
            });
        }
    }

});


// Editar fornecedor
document.addEventListener("DOMContentLoaded", function () {

    const formEditarFornecedor = document.getElementById("formEditarFornecedor");

    if (!formEditarFornecedor) return;

    const parametros = new URLSearchParams(window.location.search);
    const idFornecedor = parametros.get("id");
    const fornecedor = fornecedoresMEDICORE[idFornecedor];

    if (!fornecedor) {
        alert("Fornecedor não encontrado.");
        window.location.href = "lista_fornecedores.html";
        return;
    }

    document.getElementById("nomeFornecedor").value = fornecedor.nome;
    document.getElementById("nifFornecedor").value = fornecedor.nif;
    document.getElementById("estadoFornecedor").value = fornecedor.estado;

    document.getElementById("tipoFabricante").checked = fornecedor.tipos.includes("Fabricante");
    document.getElementById("tipoDistribuidor").checked = fornecedor.tipos.includes("Distribuidor");
    document.getElementById("tipoManutencao").checked = fornecedor.tipos.includes("Manutenção");
    document.getElementById("tipoCalibracao").checked = fornecedor.tipos.includes("Calibração");

    document.getElementById("emailFornecedor").value = fornecedor.email;
    document.getElementById("telefoneFornecedor").value = fornecedor.telefone;
    document.getElementById("websiteFornecedor").value = fornecedor.website;
    document.getElementById("contactoResponsavel").value = fornecedor.contacto;
    document.getElementById("cargoContacto").value = fornecedor.cargo;
    document.getElementById("emailContacto").value = fornecedor.emailContacto;

    document.getElementById("moradaFornecedor").value = fornecedor.morada;
    document.getElementById("codigoPostalFornecedor").value = fornecedor.codigoPostal;
    document.getElementById("localidadeFornecedor").value = fornecedor.localidade;
    document.getElementById("paisFornecedor").value = fornecedor.pais;

    document.getElementById("contratoFornecedor").value = fornecedor.contrato;
    document.getElementById("inicioContratoFornecedor").value = fornecedor.inicioContrato;
    document.getElementById("fimContratoFornecedor").value = fornecedor.fimContrato;
    document.getElementById("areaAtuacaoFornecedor").value = fornecedor.area;
    document.getElementById("equipamentosAssociadosFornecedor").value = fornecedor.equipamentos;
    document.getElementById("observacoesFornecedor").value = fornecedor.observacoes;

    formEditarFornecedor.addEventListener("submit", function (event) {
        event.preventDefault();

        alert("Alterações do fornecedor registadas com sucesso.");
        window.location.href = "lista_fornecedores.html";
    });

});


// Novo fornecedor
document.addEventListener("DOMContentLoaded", function () {

    const formNovoFornecedor = document.getElementById("formNovoFornecedor");

    if (!formNovoFornecedor) return;

    formNovoFornecedor.addEventListener("submit", function (event) {
        event.preventDefault();

        alert("Fornecedor registado com sucesso.");
        window.location.href = "lista_fornecedores.html";
    });

});


// Remover fornecedor
document.addEventListener("DOMContentLoaded", function () {

    const botaoRemoverFornecedor = document.getElementById("btnConfirmarRemocaoFornecedor");

    if (!botaoRemoverFornecedor) return;

    const parametros = new URLSearchParams(window.location.search);
    const idFornecedor = parametros.get("id");
    const fornecedor = fornecedoresMEDICORE[idFornecedor];

    if (!fornecedor) {
        alert("Fornecedor não encontrado.");
        window.location.href = "lista_fornecedores.html";
        return;
    }

    document.getElementById("removerFornecedorNome").textContent = fornecedor.nome;
    document.getElementById("removerFornecedorTipo").textContent = fornecedor.tipos.join(", ");
    document.getElementById("removerFornecedorNif").textContent = fornecedor.nif;
    document.getElementById("removerFornecedorEmail").textContent = fornecedor.email;
    document.getElementById("removerFornecedorTelefone").textContent = fornecedor.telefone;
    document.getElementById("removerFornecedorLocalidade").textContent = fornecedor.localidade;
    document.getElementById("removerFornecedorEquipamentos").textContent = fornecedor.qtdEquipamentos;
    document.getElementById("removerFornecedorContrato").textContent = fornecedor.contrato;

    const estado = document.getElementById("removerFornecedorEstado");
    estado.textContent = fornecedor.estado;
    estado.className = `estado ${fornecedor.estadoClasse}`;

    const checkbox = document.getElementById("confirmarRemocaoFornecedor");

    checkbox.addEventListener("change", function () {
        botaoRemoverFornecedor.disabled = !checkbox.checked;
    });

    botaoRemoverFornecedor.addEventListener("click", function () {
        botaoRemoverFornecedor.disabled = true;

        mostrarCardConfirmacaoRemocao(
            "Fornecedor",
            fornecedor.nome,
            "lista_fornecedores.html"
        );
    });

});

// Card visual de confirmação de remoção

function mostrarCardConfirmacaoRemocao(tipo, nome, paginaDestino) {

    const overlay = document.createElement("div");
    overlay.classList.add("remocao-sucesso-overlay");

    overlay.innerHTML = `
        <div class="remocao-sucesso-card">
            <div class="remocao-sucesso-icone">
                <i class="fa-solid fa-check"></i>
            </div>

            <h3>${tipo} removido com sucesso</h3>

            <p>
                <strong>${nome}</strong> foi removido do sistema.
            </p>

            <p class="texto-redirecionar">
                A redirecionar para a lista...
            </p>

            <div class="barra-redirecionar">
                <span></span>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    setTimeout(function () {
        window.location.href = paginaDestino;
    }, 3000);
}

// Dashboard de Gestão MEDICORE
// Alteração feita por mim

document.addEventListener("DOMContentLoaded", function () {

    const graficoEstado = document.getElementById("graficoEstadoEquipamentos");

    if (!graficoEstado) return;

    if (typeof Chart === "undefined") {
        console.warn("Chart.js não foi carregado.");
        return;
    }

    const opcoesGraficos = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: "#123c46",
                    font: {
                        family: "Titillium Web",
                        weight: "600"
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: "#425466"
                },
                grid: {
                    color: "#e8f1f1"
                }
            },
            x: {
                ticks: {
                    color: "#425466"
                },
                grid: {
                    display: false
                }
            }
        }
    };

    // Equipamentos por estado
    new Chart(document.getElementById("graficoEstadoEquipamentos"), {
        type: "doughnut",
        data: {
            labels: ["Ativos", "Em manutenção", "Avariados", "Inativos", "Abatidos"],
            datasets: [{
                data: [95, 14, 7, 9, 3],
                backgroundColor: [
                    "#4fb3a4",
                    "#f2a65a",
                    "#c0392b",
                    "#9fbec4",
                    "#425466"
                ],
                borderWidth: 3,
                borderColor: "#ffffff"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        color: "#123c46",
                        font: {
                            family: "Titillium Web",
                            weight: "600"
                        }
                    }
                }
            }
        }
    });

    // Equipamentos por categoria
    new Chart(document.getElementById("graficoCategoriaEquipamentos"), {
        type: "bar",
        data: {
            labels: ["Monitorização", "Suporte de Vida", "Imagiologia", "Laboratório", "Cirurgia", "Diagnóstico"],
            datasets: [{
                label: "Nº de equipamentos",
                data: [30, 18, 12, 22, 16, 30],
                backgroundColor: "#4fb3a4",
                borderRadius: 10
            }]
        },
        options: opcoesGraficos
    });

    // Equipamentos por localização
    new Chart(document.getElementById("graficoLocalizacaoEquipamentos"), {
        type: "bar",
        data: {
            labels: ["UCI", "Urgência", "Bloco Operatório", "Radiologia", "Laboratório", "Consulta Externa"],
            datasets: [{
                label: "Nº de equipamentos",
                data: [18, 22, 15, 10, 20, 12],
                backgroundColor: "#123c46",
                borderRadius: 10
            }]
        },
        options: opcoesGraficos
    });

    // Equipamentos de suporte de vida por serviço
    new Chart(document.getElementById("graficoSuporteVida"), {
        type: "bar",
        data: {
            labels: ["UCI", "Urgência", "Bloco Operatório", "Neonatologia", "Cardiologia"],
            datasets: [{
                label: "Equipamentos de suporte de vida",
                data: [8, 6, 4, 3, 2],
                backgroundColor: "#f2a65a",
                borderRadius: 10
            }]
        },
        options: opcoesGraficos
    });

});