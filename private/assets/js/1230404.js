// JavaScript Gonçalo Brito 1230404
// Funcionalidades da área privada MEDICORE

// Dados temporários dos equipamentos.
// Quando o backend estiver implementado, estes dados passam a vir da base de dados MySQL.
const equipamentosMEDICORE = {
    "EQ-001": {
        codigo: "EQ-001",
        nome: "Monitor Multiparamétrico",
        categoria: "Monitorização",
        fabricante: "Philips",
        modelo: "IntelliVue MX450",
        serie: "SN-MX450-2024",
        anoFabrico: "2023",
        tipoEntrada: "Compra",
        departamento: "Unidade de Cuidados Intensivos",
        edificio: "Edifício A",
        piso: "2",
        sala: "Sala 2",
        localizacao: "UCI - Sala 2",
        estado: "Ativo",
        criticidade: "Crítica",
        operacional: "Operacional",
        fornecedor: "MedSupply Portugal",
        dataFabrico: "2023-11-10",
        dataAquisicao: "2024-01-15",
        dataInstalacao: "2024-01-20",
        valorAquisicao: "3500.00",
        inicioGarantia: "2024-01-20",
        fimGarantia: "2027-01-20",
        contratoManutencao: "Sim",
        tipoContrato: "Manutenção preventiva anual",
        entidadeContrato: "MedSupply Portugal",
        ultimaManutencao: "2026-03-12",
        proximaManutencao: "2026-09-12",
        ultimaCalibracao: "2026-03-12",
        proximaCalibracao: "2026-09-12",
        periodicidade: "Semestral",
        responsavelTecnico: "Eng. Gonçalo Brito",
        observacoes: "Equipamento essencial para monitorização contínua de parâmetros vitais em contexto de cuidados intensivos."
    },

    "EQ-002": {
        codigo: "EQ-002",
        nome: "Ventilador Pulmonar",
        categoria: "Suporte de Vida",
        fabricante: "Dräger",
        modelo: "Evita V300",
        serie: "SN-EV300-1198",
        anoFabrico: "2022",
        tipoEntrada: "Compra",
        departamento: "Urgência",
        edificio: "Edifício B",
        piso: "0",
        sala: "Sala 1",
        localizacao: "Urgência - Sala 1",
        estado: "Em manutenção",
        criticidade: "Crítica",
        operacional: "Não operacional",
        fornecedor: "Biomedical Solutions",
        dataFabrico: "2022-12-05",
        dataAquisicao: "2023-06-10",
        dataInstalacao: "2023-06-18",
        valorAquisicao: "12500.00",
        inicioGarantia: "2023-06-18",
        fimGarantia: "2026-06-18",
        contratoManutencao: "Sim",
        tipoContrato: "Manutenção preventiva e corretiva",
        entidadeContrato: "Biomedical Solutions",
        ultimaManutencao: "2026-02-28",
        proximaManutencao: "2026-08-28",
        ultimaCalibracao: "2026-02-28",
        proximaCalibracao: "2026-08-28",
        periodicidade: "Semestral",
        responsavelTecnico: "Eng. Gonçalo Brito",
        observacoes: "Equipamento em manutenção preventiva. Deve ser validado antes de regressar ao serviço clínico."
    },

    "EQ-003": {
        codigo: "EQ-003",
        nome: "Desfibrilhador",
        categoria: "Emergência",
        fabricante: "Zoll",
        modelo: "R Series",
        serie: "SN-ZOLL-8821",
        anoFabrico: "2021",
        tipoEntrada: "Compra",
        departamento: "Bloco Operatório",
        edificio: "Edifício C",
        piso: "1",
        sala: "Bloco Operatório",
        localizacao: "Bloco Operatório",
        estado: "Avariado",
        criticidade: "Crítica",
        operacional: "Não operacional",
        fornecedor: "ClinicalTech Equipamentos",
        dataFabrico: "2021-05-20",
        dataAquisicao: "2022-09-02",
        dataInstalacao: "2022-09-08",
        valorAquisicao: "8900.00",
        inicioGarantia: "2022-09-08",
        fimGarantia: "2025-09-08",
        contratoManutencao: "Em análise",
        tipoContrato: "Por definir",
        entidadeContrato: "ClinicalTech Equipamentos",
        ultimaManutencao: "2026-01-05",
        proximaManutencao: "",
        ultimaCalibracao: "2026-01-05",
        proximaCalibracao: "",
        periodicidade: "Anual",
        responsavelTecnico: "Eng. Gonçalo Brito",
        observacoes: "Equipamento sinalizado como avariado. Deve permanecer indisponível até avaliação técnica e reparação."
    }
};

function $(id) {
    return document.getElementById(id);
}

function obterParametroURL(nome) {
    return new URLSearchParams(window.location.search).get(nome);
}

function obterEquipamentoSelecionado() {
    const id = obterParametroURL("id") || "EQ-001";
    return equipamentosMEDICORE[id] || null;
}

function classeEstado(estado) {
    const classes = {
        "Ativo": "estado-ativo",
        "Ativa": "estado-ativo",
        "Em manutenção": "estado-manutencao",
        "Avariado": "estado-avariado",
        "Inativo": "estado-inativo",
        "Inativa": "estado-inativo",
        "Indisponível": "estado-inativo",
        "Em calibração": "estado-manutencao",
        "Em quarentena": "estado-manutencao",
        "Abatido": "estado-abatido"
    };

    return classes[estado] || "estado-inativo";
}

function formatarDataPT(dataISO) {
    if (!dataISO) return "Por definir";

    const partes = dataISO.split("-");
    if (partes.length !== 3) return dataISO;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function definirTexto(id, valor) {
    const elemento = $(id);
    if (elemento) elemento.textContent = valor || "---";
}

function definirValor(id, valor) {
    const campo = $(id);
    if (!campo) return;

    if (campo.tagName === "SELECT") {
        selecionarOpcao(campo, valor);
        return;
    }

    campo.value = valor ?? "";
}

function selecionarOpcao(select, valor) {
    const valorFinal = valor || "";
    const opcoes = Array.from(select.options);

    const opcaoPorValor = opcoes.find(function (opcao) {
        return opcao.value === valorFinal;
    });

    if (opcaoPorValor) {
        select.value = valorFinal;
        return;
    }

    const opcaoPorTexto = opcoes.find(function (opcao) {
        return opcao.textContent.trim() === valorFinal;
    });

    if (opcaoPorTexto) {
        select.value = opcaoPorTexto.value;
        return;
    }

    if (valorFinal !== "") {
        const novaOpcao = new Option(valorFinal, valorFinal, true, true);
        select.add(novaOpcao);
    }
}

function definirEstadoRadio(operacional) {
    const sim = $("operacionalSim");
    const nao = $("operacionalNao");

    if (!sim || !nao) return;

    sim.checked = operacional === "Operacional";
    nao.checked = operacional !== "Operacional";
}

function preencherCamposEquipamento(equipamento) {
    if (!equipamento) return;

    definirValor("idEquipamento", equipamento.codigo);
    definirValor("codigoInventario", equipamento.codigo);
    definirValor("nomeEquipamento", equipamento.nome);
    definirValor("categoria", equipamento.categoria);
    definirValor("fabricante", equipamento.fabricante);
    definirValor("modelo", equipamento.modelo);
    definirValor("numeroSerie", equipamento.serie);
    definirValor("anoFabrico", equipamento.anoFabrico);
    definirValor("tipoEntrada", equipamento.tipoEntrada);

    definirValor("departamento", equipamento.departamento);
    definirValor("edificio", equipamento.edificio);
    definirValor("piso", equipamento.piso);
    definirValor("sala", equipamento.sala);
    definirValor("estado", equipamento.estado);
    definirValor("criticidade", equipamento.criticidade);
    definirEstadoRadio(equipamento.operacional);

    definirValor("fornecedor", equipamento.fornecedor);
    definirValor("dataFabrico", equipamento.dataFabrico);
    definirValor("dataAquisicao", equipamento.dataAquisicao);
    definirValor("dataInstalacao", equipamento.dataInstalacao);
    definirValor("valorAquisicao", equipamento.valorAquisicao);
    definirValor("inicioGarantia", equipamento.inicioGarantia);
    definirValor("fimGarantia", equipamento.fimGarantia);
    definirValor("contratoManutencao", equipamento.contratoManutencao);
    definirValor("tipoContrato", equipamento.tipoContrato);
    definirValor("entidadeContrato", equipamento.entidadeContrato);

    definirValor("ultimaManutencao", equipamento.ultimaManutencao);
    definirValor("proximaManutencao", equipamento.proximaManutencao);
    definirValor("ultimaCalibracao", equipamento.ultimaCalibracao);
    definirValor("proximaCalibracao", equipamento.proximaCalibracao);
    definirValor("periodicidade", equipamento.periodicidade);
    definirValor("responsavelTecnico", equipamento.responsavelTecnico);
    definirValor("observacoes", equipamento.observacoes);
}

function atualizarResumoFicha() {
    const codigo = $("codigoInventario")?.value || "---";
    const nome = $("nomeEquipamento")?.value || "Equipamento Médico";
    const fabricante = $("fabricante")?.value || "";
    const modelo = $("modelo")?.value || "";
    const localizacao = $("sala")?.value || $("departamento")?.value || "localização por definir";
    const estado = $("estado")?.value || "Estado";
    const criticidade = $("criticidade")?.value || "Criticidade";
    const operacional = $("operacionalSim")?.checked ? "Operacional" : "Não operacional";

    definirTexto("resumoNomeEquipamento", nome);
    definirTexto("tituloPaginaEquipamento", `Ficha do Equipamento - ${codigo}`);
    definirTexto("badgeEstado", estado);
    definirTexto("badgeCriticidade", `Criticidade: ${criticidade}`);
    definirTexto("badgeOperacional", operacional);
    definirTexto("resumoDescricao", `${codigo} | ${fabricante} ${modelo} | ${localizacao}`);

    const badgeEstado = $("badgeEstado");
    if (badgeEstado) {
        badgeEstado.className = `estado ${classeEstado(estado)}`;
    }
}

function inicializarDocumentosEquipamento() {
    const btnAdicionarDocumento = $("btnAdicionarDocumento");
    const listaDocumentos = $("listaDocumentosNovos") || $("listaDocumentos");

    if (!btnAdicionarDocumento || !listaDocumentos) return;

    function atualizarBotoesRemoverDocumento() {
        const documentos = listaDocumentos.querySelectorAll(".documento-form-item");
        const botoesRemover = listaDocumentos.querySelectorAll(".btn-remover-documento");

        botoesRemover.forEach(function (botao) {
            const bloquear = documentos.length <= 1;
            botao.style.visibility = bloquear ? "hidden" : "visible";
            botao.disabled = bloquear;
        });
    }

    btnAdicionarDocumento.addEventListener("click", function () {
        const primeiroDocumento = listaDocumentos.querySelector(".documento-form-item");
        if (!primeiroDocumento) return;

        const novoDocumento = primeiroDocumento.cloneNode(true);

        novoDocumento.querySelectorAll("input, select").forEach(function (campo) {
            if (campo.tagName === "SELECT") {
                campo.selectedIndex = 0;
            } else {
                campo.value = "";
            }

            campo.disabled = false;
            campo.readOnly = false;
        });

        listaDocumentos.appendChild(novoDocumento);
        atualizarBotoesRemoverDocumento();
    });

    listaDocumentos.addEventListener("click", function (event) {
        const botaoRemover = event.target.closest(".btn-remover-documento");
        if (!botaoRemover) return;

        const documentos = listaDocumentos.querySelectorAll(".documento-form-item");
        if (documentos.length <= 1) return;

        botaoRemover.closest(".documento-form-item").remove();
        atualizarBotoesRemoverDocumento();
    });

    atualizarBotoesRemoverDocumento();
}


/* =========================================================
   POP-UP VISUAL DE SUCESSO
   Usado para confirmações como guardar alterações, registos, etc.
   ========================================================= */

function mostrarPopupSucesso(titulo, mensagem, paginaDestino) {

    // Cria um overlay visual reutilizável para confirmações de sucesso.
    // O destino recebido define também o texto da lista para onde o utilizador será redirecionado.
    const textoListaDestino = paginaDestino.includes("fornecedores")
        ? "de fornecedores"
        : paginaDestino.includes("localizacoes")
            ? "de localizações"
            : "de equipamentos";

    const overlay = document.createElement("div");
    overlay.classList.add("popup-sucesso-overlay");

    overlay.innerHTML = `
        <div class="popup-sucesso-card">

            <div class="popup-sucesso-icone">
                <i class="fa-solid fa-check"></i>
            </div>

            <h3>${titulo}</h3>

            <p>${mensagem}</p>

            <p class="popup-sucesso-redirecionar">
                A redirecionar para a lista ${textoListaDestino}...
            </p>

            <div class="popup-sucesso-barra">
                <span></span>
            </div>

        </div>
    `;

    document.body.appendChild(overlay);

    setTimeout(function () {
        window.location.href = paginaDestino;
    }, 2600);
}

function inicializarCriticidade() {
    const campoCriticidade = $("criticidade");
    const descricaoCriticidade = $("descricaoCriticidade");

    if (!campoCriticidade || !descricaoCriticidade) return;

    const descricoes = {
        baixa: "Baixa: falha com impacto reduzido. Exemplos: balança clínica, termómetro digital ou otoscópio.",
        media: "Média: pode atrasar o serviço, mas existem alternativas. Exemplos: eletrocardiógrafo de rotina, aspirador portátil ou equipamento de fisioterapia.",
        média: "Média: pode atrasar o serviço, mas existem alternativas. Exemplos: eletrocardiógrafo de rotina, aspirador portátil ou equipamento de fisioterapia.",
        alta: "Alta: impacto significativo na prestação de cuidados. Exemplos: monitor multiparamétrico de urgência, ecógrafo ou incubadora neonatal.",
        critica: "Crítica: equipamento essencial para suporte de vida ou emergência. Exemplos: ventilador pulmonar, desfibrilhador ou máquina de anestesia.",
        crítica: "Crítica: equipamento essencial para suporte de vida ou emergência. Exemplos: ventilador pulmonar, desfibrilhador ou máquina de anestesia.",
        "suporte de vida": "Suporte de vida: falha pode colocar em risco imediato a vida do doente. Exemplos: ventilador pulmonar ou desfibrilhador."
    };

    function atualizarDescricao() {
        const chave = campoCriticidade.value.toLowerCase();
        descricaoCriticidade.textContent = descricoes[chave] || "Selecione uma criticidade para ver a descrição.";
    }

    campoCriticidade.addEventListener("change", atualizarDescricao);
    atualizarDescricao();
}

function inicializarPopovers() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');

    popoverTriggerList.forEach(function (popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl, {
            container: "body"
        });
    });
}

document.addEventListener("DOMContentLoaded", function () {
    inicializarPopovers();
    inicializarCriticidade();
    inicializarDocumentosEquipamento();
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

    // Inicializa apenas a página novo_fornecedor.html.
    // Se o formulário não existir na página atual, esta função termina sem fazer nada.
    const formNovoFornecedor = document.getElementById("formNovoFornecedor");
    const btnLimparNovoFornecedor = document.getElementById("btnLimparNovoFornecedor");

    if (!formNovoFornecedor) return;

    // Limpa manualmente todos os campos do novo fornecedor.
    // Também repõe Portugal como país por defeito e remove documentos extra clonados.
    if (btnLimparNovoFornecedor) {
        btnLimparNovoFornecedor.addEventListener("click", function () {
            formNovoFornecedor.querySelectorAll("input, select, textarea").forEach(function (campo) {
                if (campo.type === "radio" || campo.type === "checkbox") {
                    campo.checked = false;
                } else if (campo.type === "file") {
                    campo.value = "";
                } else if (campo.tagName === "SELECT") {
                    campo.selectedIndex = 0;
                } else {
                    campo.value = "";
                }
            });

            const paisFornecedor = document.getElementById("paisFornecedor");
            if (paisFornecedor) {
                paisFornecedor.value = "Portugal";
            }

            const listaDocumentos = document.getElementById("listaDocumentos");
            if (listaDocumentos) {
                const documentos = listaDocumentos.querySelectorAll(".documento-form-item");

                documentos.forEach(function (documento, index) {
                    if (index > 0) {
                        documento.remove();
                    }
                });
            }
        });
    }

    // Interceta o submit para mostrar o mesmo pop-up visual usado nos equipamentos.
    // Quando o backend existir, esta zona pode ser ligada ao processamento real.
    formNovoFornecedor.addEventListener("submit", function (event) {
        event.preventDefault();

        mostrarPopupSucesso(
            "Novo fornecedor guardado",
            "O novo fornecedor foi registado com sucesso.",
            "lista_fornecedores.html"
        );
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

//Temporário
// Nova localização

document.addEventListener("DOMContentLoaded", function () {

    const formNovaLocalizacao = document.getElementById("formNovaLocalizacao");
    const btnLimparNovaLocalizacao = document.getElementById("btnLimparNovaLocalizacao");

    if (!formNovaLocalizacao) return;

    if (btnLimparNovaLocalizacao) {
        btnLimparNovaLocalizacao.addEventListener("click", function () {
            formNovaLocalizacao.querySelectorAll("input, select, textarea").forEach(function (campo) {
                if (campo.type === "radio" || campo.type === "checkbox") {
                    campo.checked = false;
                } else if (campo.tagName === "SELECT") {
                    campo.selectedIndex = 0;
                } else {
                    campo.value = "";
                }
            });

            const permiteCriticosSim = document.getElementById("permiteCriticosSim");
            const suporteVidaNao = document.getElementById("suporteVidaNao");

            if (permiteCriticosSim) permiteCriticosSim.checked = true;
            if (suporteVidaNao) suporteVidaNao.checked = true;
        });
    }

    formNovaLocalizacao.addEventListener("submit", function (event) {
        event.preventDefault();

        mostrarPopupSucesso(
            "Nova localização guardada",
            "A nova localização foi registada com sucesso.",
            "lista_localizacoes.html"
        );
    });

});

// Dados temporários das localizações
// Alteração feita por mim

const localizacoesMEDICORE = {
    "LOC-001": {
        departamento: "Unidade de Cuidados Intensivos",
        edificio: "Edifício A",
        piso: "2",
        sala: "Sala 201",
        tipoEspaco: "UCI",
        estado: "Ativa",
        estadoClasse: "estado-ativo",
        responsavel: "Enf. Maria Costa",
        funcao: "Enfermeira Responsável",
        contacto: "Ext. 2201",
        email: "maria.costa@hospital.pt",
        notasContacto: "Contactar preferencialmente durante o turno da manhã.",
        acesso: "Apenas pessoal autorizado",
        criticidade: "Crítica",
        permiteCriticos: "Sim",
        suporteVida: "Sim",
        capacidade: "10 equipamentos",
        qtdEquipamentos: 8,
        equipamentosAtivos: 7,
        equipamentosManutencao: 1,
        equipamentosAvariados: 0,
        ocupacao: "80%",
        observacoes: "Área crítica com equipamentos de suporte de vida e monitorização contínua.",
        equipamentosAssociados: [
            {
                codigo: "EQ-001",
                nome: "Monitor Multiparamétrico",
                categoria: "Monitorização",
                modelo: "IntelliVue MX450",
                serie: "SN-MX450-2024",
                criticidade: "Crítica",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-002",
                nome: "Ventilador Pulmonar",
                categoria: "Suporte de Vida",
                modelo: "Evita V300",
                serie: "SN-EV300-1198",
                criticidade: "Crítica",
                estado: "Em manutenção",
                estadoClasse: "estado-manutencao"
            }
        ]
    },

    "LOC-002": {
        departamento: "Urgência",
        edificio: "Edifício B",
        piso: "0",
        sala: "Sala 1",
        tipoEspaco: "Urgência",
        estado: "Ativa",
        estadoClasse: "estado-ativo",
        responsavel: "Dr. João Martins",
        funcao: "Coordenador de Serviço",
        contacto: "Ext. 1101",
        email: "joao.martins@hospital.pt",
        notasContacto: "Serviço com funcionamento permanente.",
        acesso: "Restrito",
        criticidade: "Alta",
        permiteCriticos: "Sim",
        suporteVida: "Sim",
        capacidade: "14 equipamentos",
        qtdEquipamentos: 12,
        equipamentosAtivos: 10,
        equipamentosManutencao: 1,
        equipamentosAvariados: 1,
        ocupacao: "86%",
        observacoes: "Área de elevada rotação com necessidade de resposta técnica rápida.",
        equipamentosAssociados: [
            {
                codigo: "EQ-006",
                nome: "Oxímetro de Pulso",
                categoria: "Monitorização",
                modelo: "OxiPro 300",
                serie: "SN-OXI-300-2025",
                criticidade: "Média",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            }
        ]
    },

    "LOC-003": {
        departamento: "Bloco Operatório",
        edificio: "Edifício C",
        piso: "1",
        sala: "BO-02",
        tipoEspaco: "Bloco Operatório",
        estado: "Ativa",
        estadoClasse: "estado-ativo",
        responsavel: "Enf. Ricardo Silva",
        funcao: "Responsável de Bloco",
        contacto: "Ext. 3102",
        email: "ricardo.silva@hospital.pt",
        notasContacto: "Evitar contacto durante períodos cirúrgicos.",
        acesso: "Apenas pessoal autorizado",
        criticidade: "Crítica",
        permiteCriticos: "Sim",
        suporteVida: "Sim",
        capacidade: "8 equipamentos",
        qtdEquipamentos: 6,
        equipamentosAtivos: 5,
        equipamentosManutencao: 0,
        equipamentosAvariados: 1,
        ocupacao: "75%",
        observacoes: "Localização com equipamentos de anestesia, emergência e suporte intraoperatório.",
        equipamentosAssociados: [
            {
                codigo: "EQ-003",
                nome: "Desfibrilhador",
                categoria: "Emergência",
                modelo: "R Series",
                serie: "SN-ZOLL-8821",
                criticidade: "Crítica",
                estado: "Avariado",
                estadoClasse: "estado-avariado"
            }
        ]
    },

    "LOC-004": {
        departamento: "Laboratório Clínico",
        edificio: "Edifício D",
        piso: "1",
        sala: "Lab-105",
        tipoEspaco: "Laboratório",
        estado: "Em manutenção",
        estadoClasse: "estado-manutencao",
        responsavel: "Téc. Ana Ferreira",
        funcao: "Técnica Coordenadora",
        contacto: "Ext. 4105",
        email: "ana.ferreira@hospital.pt",
        notasContacto: "Contactar em horário laboral.",
        acesso: "Acesso técnico",
        criticidade: "Alta",
        permiteCriticos: "Sim",
        suporteVida: "Não",
        capacidade: "12 equipamentos",
        qtdEquipamentos: 10,
        equipamentosAtivos: 8,
        equipamentosManutencao: 2,
        equipamentosAvariados: 0,
        ocupacao: "83%",
        observacoes: "Zona laboratorial com equipamentos sujeitos a calibração periódica.",
        equipamentosAssociados: []
    },

    "LOC-005": {
        departamento: "Armazém Técnico",
        edificio: "Edifício Técnico",
        piso: "-1",
        sala: "ARM-01",
        tipoEspaco: "Armazém Técnico",
        estado: "Inativa",
        estadoClasse: "estado-inativo",
        responsavel: "Eng. Gonçalo Brito",
        funcao: "Engenheiro Biomédico",
        contacto: "Ext. 5001",
        email: "g.brito@hospital.pt",
        notasContacto: "Espaço reservado a equipamentos em stock ou abatidos.",
        acesso: "Acesso técnico",
        criticidade: "Baixa",
        permiteCriticos: "Não",
        suporteVida: "Não",
        capacidade: "20 equipamentos",
        qtdEquipamentos: 4,
        equipamentosAtivos: 0,
        equipamentosManutencao: 0,
        equipamentosAvariados: 0,
        ocupacao: "20%",
        observacoes: "Localização destinada a armazenamento técnico e equipamentos fora de utilização.",
        equipamentosAssociados: []
    }
};


// Detalhes da localização
// Alteração feita por mim

document.addEventListener("DOMContentLoaded", function () {

    const paginaDetalhesLocalizacao = document.getElementById("detalheLocalizacaoTitulo");

    if (!paginaDetalhesLocalizacao) return;

    const parametros = new URLSearchParams(window.location.search);
    const idLocalizacao = parametros.get("id");
    const localizacao = localizacoesMEDICORE[idLocalizacao];

    if (!localizacao) {
        alert("Localização não encontrada.");
        window.location.href = "lista_localizacoes.html";
        return;
    }

    const titulo = `${localizacao.departamento} — ${localizacao.edificio}, Piso ${localizacao.piso}, ${localizacao.sala}`;

    document.getElementById("detalheLocalizacaoTitulo").textContent = titulo;
    document.getElementById("detalheLocalizacaoResumo").textContent =
        `${localizacao.departamento}, localizada em ${localizacao.edificio}, piso ${localizacao.piso}, ${localizacao.sala}, com ${localizacao.qtdEquipamentos} equipamento(s) associado(s).`;

    const estado = document.getElementById("detalheLocalizacaoEstado");
    estado.textContent = localizacao.estado;
    estado.className = `estado ${localizacao.estadoClasse}`;

    document.getElementById("detalheLocalizacaoTipo").textContent = localizacao.tipoEspaco;
    document.getElementById("detalheLocalizacaoCriticidade").textContent = `Criticidade: ${localizacao.criticidade}`;

    document.getElementById("detalheDepartamento").textContent = localizacao.departamento;
    document.getElementById("detalheEdificio").textContent = localizacao.edificio;
    document.getElementById("detalhePiso").textContent = localizacao.piso;
    document.getElementById("detalheSala").textContent = localizacao.sala;
    document.getElementById("detalheTipoEspaco").textContent = localizacao.tipoEspaco;

    document.getElementById("detalheResponsavel").textContent = localizacao.responsavel;
    document.getElementById("detalheFuncao").textContent = localizacao.funcao;
    document.getElementById("detalheContacto").textContent = localizacao.contacto;
    document.getElementById("detalheEmail").textContent = localizacao.email;
    document.getElementById("detalheNotasContacto").textContent = localizacao.notasContacto;

    document.getElementById("detalheAcesso").textContent = localizacao.acesso;
    document.getElementById("detalheCriticidadeArea").textContent = localizacao.criticidade;
    document.getElementById("detalhePermiteCriticos").textContent = localizacao.permiteCriticos;
    document.getElementById("detalheSuporteVida").textContent = localizacao.suporteVida;
    document.getElementById("detalheCapacidade").textContent = localizacao.capacidade;

    document.getElementById("detalheQtdEquipamentos").textContent = localizacao.qtdEquipamentos;
    document.getElementById("detalheEquipamentosAtivos").textContent = localizacao.equipamentosAtivos;
    document.getElementById("detalheEquipamentosManutencao").textContent = localizacao.equipamentosManutencao;
    document.getElementById("detalheEquipamentosAvariados").textContent = localizacao.equipamentosAvariados;
    document.getElementById("detalheOcupacao").textContent = localizacao.ocupacao;

    document.getElementById("detalheObservacoesLocalizacao").textContent = localizacao.observacoes;

    const tabela = document.getElementById("tabelaEquipamentosLocalizacao");
    const total = document.getElementById("totalEquipamentosLocalizacao");

    const equipamentos = localizacao.equipamentosAssociados || [];

    total.textContent = `${equipamentos.length} equipamento(s)`;
    tabela.innerHTML = "";

    if (equipamentos.length === 0) {
        tabela.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Não existem equipamentos associados a esta localização.
                </td>
            </tr>
        `;
    } else {
        equipamentos.forEach(function (equipamento) {
            const linha = document.createElement("tr");

            linha.innerHTML = `
                <td>${equipamento.codigo}</td>
                <td>${equipamento.nome}</td>
                <td>${equipamento.categoria}</td>
                <td>${equipamento.modelo}</td>
                <td>${equipamento.serie}</td>
                <td>${equipamento.criticidade}</td>
                <td>
                    <span class="estado ${equipamento.estadoClasse}">${equipamento.estado}</span>
                </td>
            `;

            tabela.appendChild(linha);
        });
    }

});

// Página apagar_localizacao.html
// Preencher dados da localização e confirmar remoção
// Alteração feita por mim

document.addEventListener("DOMContentLoaded", function () {

    const botaoRemoverLocalizacao = document.getElementById("btnConfirmarRemocaoLocalizacao");

    if (!botaoRemoverLocalizacao) return;

    const parametros = new URLSearchParams(window.location.search);
    const idLocalizacao = parametros.get("id");
    const localizacao = localizacoesMEDICORE[idLocalizacao];

    if (!localizacao) {
        alert("Localização não encontrada.");
        window.location.href = "lista_localizacoes.html";
        return;
    }

    document.getElementById("removerLocalizacaoDepartamento").textContent = localizacao.departamento;
    document.getElementById("removerLocalizacaoEdificio").textContent = localizacao.edificio;
    document.getElementById("removerLocalizacaoPiso").textContent = localizacao.piso;
    document.getElementById("removerLocalizacaoSala").textContent = localizacao.sala;
    document.getElementById("removerLocalizacaoTipo").textContent = localizacao.tipoEspaco;
    document.getElementById("removerLocalizacaoResponsavel").textContent = localizacao.responsavel;
    document.getElementById("removerLocalizacaoContacto").textContent = localizacao.contacto;
    document.getElementById("removerLocalizacaoEquipamentos").textContent = localizacao.qtdEquipamentos;
    document.getElementById("removerLocalizacaoCriticidade").textContent = localizacao.criticidade;
    document.getElementById("removerLocalizacaoSuporteVida").textContent = localizacao.suporteVida;

    const estado = document.getElementById("removerLocalizacaoEstado");
    estado.textContent = localizacao.estado;
    estado.className = `estado ${localizacao.estadoClasse}`;

    const checkbox = document.getElementById("confirmarRemocaoLocalizacao");

    checkbox.addEventListener("change", function () {
        botaoRemoverLocalizacao.disabled = !checkbox.checked;
    });

    botaoRemoverLocalizacao.addEventListener("click", function () {
        botaoRemoverLocalizacao.disabled = true;

        mostrarCardConfirmacaoRemocao(
            "Localização",
            `${localizacao.departamento} — ${localizacao.edificio}, Piso ${localizacao.piso}, ${localizacao.sala}`,
            "lista_localizacoes.html"
        );
    });

});

/* =========================================================
   FICHA DA LOCALIZAÇÃO
   Modo consulta por defeito + modo edição ao clicar em Editar.
   ========================================================= */

function obterLocalizacaoSelecionada() {
    // Lê o id da localização na query string.
    // Se não existir id, usa LOC-001 como exemplo para a ficha abrir preenchida.
    const id = obterParametroURL("id") || "LOC-001";
    return {
        id: id,
        dados: localizacoesMEDICORE[id] || null
    };
}

function definirRadioPorNome(nome, valor) {
    // Marca o radio button correspondente ao valor recebido.
    const radio = document.querySelector(`input[name="${nome}"][value="${valor}"]`);
    if (radio) radio.checked = true;
}

function preencherCamposLocalizacao(idLocalizacao, localizacao) {
    // Copia os dados temporários de localizacoesMEDICORE para a ficha.
    // Quando existir backend, esta função pode receber dados vindos da base de dados.
    if (!localizacao) return;

    definirValor("idLocalizacao", idLocalizacao);
    definirValor("codigoLocalizacao", idLocalizacao);
    definirValor("departamentoLocalizacao", localizacao.departamento);
    definirValor("tipoEspaco", localizacao.tipoEspaco);
    definirValor("edificioLocalizacao", localizacao.edificio);
    definirValor("pisoLocalizacao", localizacao.piso);
    definirValor("salaLocalizacao", localizacao.sala);
    definirValor("estadoLocalizacao", localizacao.estado);

    definirValor("responsavelLocalizacao", localizacao.responsavel);
    definirValor("funcaoResponsavelLocalizacao", localizacao.funcao);
    definirValor("contactoInternoLocalizacao", localizacao.contacto);
    definirValor("emailResponsavelLocalizacao", localizacao.email);
    definirValor("observacaoContactoLocalizacao", localizacao.notasContacto);

    definirValor("acessoLocalizacao", localizacao.acesso);
    definirValor("criticidadeLocalizacao", localizacao.criticidade);
    definirValor("capacidadeEquipamentos", localizacao.capacidade);
    definirRadioPorNome("permiteCriticos", localizacao.permiteCriticos);
    definirRadioPorNome("suporteVidaLocalizacao", localizacao.suporteVida);

    definirValor("qtdEquipamentosLocalizacao", localizacao.qtdEquipamentos);
    definirValor("equipamentosAtivosLocalizacao", localizacao.equipamentosAtivos);
    definirValor("equipamentosManutencaoLocalizacao", localizacao.equipamentosManutencao);
    definirValor("equipamentosAvariadosLocalizacao", localizacao.equipamentosAvariados);
    definirValor("ocupacaoLocalizacao", localizacao.ocupacao);
    definirValor("observacoesLocalizacao", localizacao.observacoes);
}

function preencherTabelaEquipamentosFichaLocalizacao(localizacao) {
    // Preenche a tabela de equipamentos associados dentro da ficha da localização.
    const tabela = document.getElementById("tabelaEquipamentosFichaLocalizacao");
    if (!tabela) return;

    const equipamentos = localizacao.equipamentosAssociados || [];
    tabela.innerHTML = "";

    if (equipamentos.length === 0) {
        tabela.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Não existem equipamentos associados a esta localização.
                </td>
            </tr>
        `;
        return;
    }

    equipamentos.forEach(function (equipamento) {
        const linha = document.createElement("tr");

        linha.innerHTML = `
            <td>${equipamento.codigo}</td>
            <td>${equipamento.nome}</td>
            <td>${equipamento.categoria}</td>
            <td>${equipamento.modelo}</td>
            <td>${equipamento.serie}</td>
            <td>${equipamento.criticidade}</td>
            <td><span class="estado ${equipamento.estadoClasse}">${equipamento.estado}</span></td>
        `;

        tabela.appendChild(linha);
    });
}

document.addEventListener("DOMContentLoaded", function () {

    // Inicializa apenas a página ficha_localizacao.html.
    const formFicha = document.getElementById("formFichaLocalizacao");

    if (!formFicha) return;

    const localizacaoSelecionada = obterLocalizacaoSelecionada();
    const localizacao = localizacaoSelecionada.dados;

    if (!localizacao) {
        alert("Localização não encontrada.");
        window.location.href = "lista_localizacoes.html";
        return;
    }

    preencherCamposLocalizacao(localizacaoSelecionada.id, localizacao);
    preencherTabelaEquipamentosFichaLocalizacao(localizacao);

    const btnAtivarEdicao = document.getElementById("btnAtivarEdicaoLocalizacao");
    const btnCancelarEdicao = document.getElementById("btnCancelarEdicaoLocalizacao");
    const botoesEdicao = document.querySelectorAll(".botao-edicao");
    const botoesConsulta = document.querySelectorAll(".botao-consulta");
    const camposFicha = formFicha.querySelectorAll(".campo-ficha");
    const camposEditaveis = formFicha.querySelectorAll(".campo-editavel");

    let valoresOriginais = {};

    function guardarValoresOriginais() {
        // Guarda os valores antes de editar para permitir cancelar alterações.
        valoresOriginais = {};

        camposFicha.forEach(function (campo) {
            if (!campo.id) return;

            if (campo.type === "radio" || campo.type === "checkbox") {
                valoresOriginais[campo.id] = campo.checked;
            } else {
                valoresOriginais[campo.id] = campo.value;
            }
        });
    }

    function restaurarValoresOriginais() {
        // Repõe os valores guardados quando o utilizador cancela a edição.
        camposFicha.forEach(function (campo) {
            if (!campo.id || !(campo.id in valoresOriginais)) return;

            if (campo.type === "radio" || campo.type === "checkbox") {
                campo.checked = valoresOriginais[campo.id];
            } else {
                campo.value = valoresOriginais[campo.id];
            }
        });

        atualizarResumoLocalizacao();
    }

    function aplicarModoConsulta() {
        // Bloqueia os campos editáveis e mostra Voltar + Editar.
        camposEditaveis.forEach(function (campo) {
            if (campo.tagName === "SELECT" || campo.type === "radio" || campo.type === "checkbox") {
                campo.disabled = true;
            } else {
                campo.readOnly = true;
            }
        });

        document.querySelectorAll(".campo-bloqueado").forEach(function (campo) {
            campo.readOnly = true;
            campo.disabled = false;
        });

        botoesEdicao.forEach(function (elemento) {
            elemento.classList.add("d-none");
        });

        botoesConsulta.forEach(function (elemento) {
            elemento.classList.remove("d-none");
        });

        formFicha.classList.remove("modo-edicao");
        formFicha.classList.add("modo-consulta");

        document.getElementById("modoFormularioLocalizacao").value = "ver";
    }

    function aplicarModoEdicao() {
        // Liberta os campos editáveis e mostra Cancelar + Guardar.
        camposEditaveis.forEach(function (campo) {
            if (campo.tagName === "SELECT" || campo.type === "radio" || campo.type === "checkbox") {
                campo.disabled = false;
            } else {
                campo.readOnly = false;
            }
        });

        document.querySelectorAll(".campo-bloqueado").forEach(function (campo) {
            campo.readOnly = true;
        });

        botoesEdicao.forEach(function (elemento) {
            elemento.classList.remove("d-none");
        });

        botoesConsulta.forEach(function (elemento) {
            elemento.classList.add("d-none");
        });

        formFicha.classList.remove("modo-consulta");
        formFicha.classList.add("modo-edicao");

        document.getElementById("modoFormularioLocalizacao").value = "editar";
    }

    function atualizarResumoLocalizacao() {
        // Atualiza os elementos ocultos de resumo/badges sempre que a ficha muda.
        const codigo = document.getElementById("codigoLocalizacao")?.value || "";
        const departamento = document.getElementById("departamentoLocalizacao")?.value || "Localização";
        const edificio = document.getElementById("edificioLocalizacao")?.value || "Edifício";
        const piso = document.getElementById("pisoLocalizacao")?.value || "Piso";
        const sala = document.getElementById("salaLocalizacao")?.value || "Sala";
        const estado = document.getElementById("estadoLocalizacao")?.value || "Estado";
        const tipo = document.getElementById("tipoEspaco")?.value || "Tipo";
        const criticidade = document.getElementById("criticidadeLocalizacao")?.value || "Criticidade";

        definirTexto("tituloPaginaLocalizacao", `Ficha da Localização - ${codigo}`);
        definirTexto("resumoNomeLocalizacao", departamento);
        definirTexto("resumoDescricaoLocalizacao", `${edificio} | Piso ${piso} | ${sala}`);
        definirTexto("badgeEstadoLocalizacao", estado);
        definirTexto("badgeTipoLocalizacao", tipo);
        definirTexto("badgeCriticidadeLocalizacao", `Criticidade: ${criticidade}`);

        const badgeEstado = document.getElementById("badgeEstadoLocalizacao");
        if (badgeEstado) {
            badgeEstado.className = `estado ${classeEstado(estado)}`;
        }
    }

    if (btnAtivarEdicao) {
        btnAtivarEdicao.addEventListener("click", function () {
            guardarValoresOriginais();
            aplicarModoEdicao();
        });
    }

    if (btnCancelarEdicao) {
        btnCancelarEdicao.addEventListener("click", function () {
            restaurarValoresOriginais();
            aplicarModoConsulta();
        });
    }

    formFicha.addEventListener("input", atualizarResumoLocalizacao);
    formFicha.addEventListener("change", atualizarResumoLocalizacao);

    formFicha.addEventListener("submit", function (event) {
        event.preventDefault();

        mostrarPopupSucesso(
            "Alterações registadas",
            "As alterações na localização foram registadas com sucesso.",
            "lista_localizacoes.html"
        );
    });

    guardarValoresOriginais();
    atualizarResumoLocalizacao();
    aplicarModoConsulta();
});

/* =========================================================
   MODAL DE REMOÇÃO DE LOCALIZAÇÃO
   Preenche o modal da lista e remove a linha visualmente após confirmação.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const modalApagar = document.getElementById("modalApagarLocalizacao");
    const btnConfirmarApagar = document.getElementById("btnConfirmarApagarLocalizacao");

    let linhaLocalizacaoSelecionada = null;

    if (!modalApagar || !btnConfirmarApagar) return;

    modalApagar.addEventListener("show.bs.modal", function (event) {
        const botao = event.relatedTarget;
        if (!botao) return;

        linhaLocalizacaoSelecionada = botao.closest("tr");

        const codigo = botao.getAttribute("data-codigo");
        const departamento = botao.getAttribute("data-departamento");
        const edificio = botao.getAttribute("data-edificio");
        const piso = botao.getAttribute("data-piso");
        const sala = botao.getAttribute("data-sala");
        const tipo = botao.getAttribute("data-tipo");
        const responsavel = botao.getAttribute("data-responsavel");
        const estado = botao.getAttribute("data-estado");
        const equipamentos = botao.getAttribute("data-equipamentos");

        document.getElementById("modalApagarIdLocalizacao").value = codigo;
        document.getElementById("modalApagarLocalizacaoCodigo").textContent = codigo;
        document.getElementById("modalApagarLocalizacaoDepartamento").textContent = departamento;
        document.getElementById("modalApagarLocalizacaoEdificio").textContent = edificio;
        document.getElementById("modalApagarLocalizacaoPiso").textContent = piso;
        document.getElementById("modalApagarLocalizacaoSala").textContent = sala;
        document.getElementById("modalApagarLocalizacaoTipo").textContent = tipo;
        document.getElementById("modalApagarLocalizacaoResponsavel").textContent = responsavel;
        document.getElementById("modalApagarLocalizacaoEstado").textContent = estado;
        document.getElementById("modalApagarLocalizacaoEquipamentos").textContent = equipamentos;
    });

    btnConfirmarApagar.addEventListener("click", function () {
        const codigo = document.getElementById("modalApagarIdLocalizacao").value;
        const departamento = document.getElementById("modalApagarLocalizacaoDepartamento").textContent;
        const modalBootstrap = bootstrap.Modal.getInstance(modalApagar);

        modalBootstrap.hide();

        if (linhaLocalizacaoSelecionada) {
            linhaLocalizacaoSelecionada.remove();
        }

        mostrarPopupSucesso(
            "Localização removida",
            `A localização ${codigo} - ${departamento} foi removida com sucesso.`,
            "lista_localizacoes.html"
        );
    });
});

/* =========================================================
   FICHA DO FORNECEDOR
   Mesmo comportamento da ficha do equipamento:
   consulta por defeito + edição controlada pelo botão Editar.
   ========================================================= */

function obterFornecedorSelecionado() {
    // Lê o id do fornecedor na query string.
    // Se não existir id, usa FOR-001 como exemplo para a página não ficar vazia.
    const id = obterParametroURL("id") || "FOR-001";
    return {
        id: id,
        dados: fornecedoresMEDICORE[id] || null
    };
}

function definirCheckboxFornecedor(id, tipos, valor) {
    // Marca ou desmarca uma checkbox de tipo de fornecedor conforme os dados carregados.
    const campo = document.getElementById(id);
    if (campo) {
        campo.checked = tipos.includes(valor);
    }
}

function obterTiposFornecedorSelecionados() {
    // Recolhe todos os tipos atualmente selecionados nas checkboxes do formulário.
    // É usado para atualizar o resumo oculto da ficha.
    const tipos = [];

    document.querySelectorAll('input[name="tipoFornecedor[]"]:checked').forEach(function (campo) {
        tipos.push(campo.value);
    });

    return tipos;
}

function preencherCamposFornecedor(idFornecedor, fornecedor) {
    // Copia os dados temporários de fornecedoresMEDICORE para os campos da ficha.
    // Mantém a página preparada para receber dados reais vindos do backend no futuro.
    if (!fornecedor) return;

    definirValor("idFornecedor", idFornecedor);
    definirValor("codigoFornecedor", idFornecedor);
    definirValor("nomeFornecedor", fornecedor.nome);
    definirValor("nifFornecedor", fornecedor.nif);
    definirValor("estadoFornecedor", fornecedor.estado);
    definirValor("qtdEquipamentosFornecedor", fornecedor.qtdEquipamentos);

    definirCheckboxFornecedor("tipoFabricante", fornecedor.tipos, "Fabricante");
    definirCheckboxFornecedor("tipoDistribuidor", fornecedor.tipos, "Distribuidor");
    definirCheckboxFornecedor("tipoManutencao", fornecedor.tipos, "Manutenção");
    definirCheckboxFornecedor("tipoCalibracao", fornecedor.tipos, "Calibração");

    definirValor("emailFornecedor", fornecedor.email);
    definirValor("telefoneFornecedor", fornecedor.telefone);
    definirValor("websiteFornecedor", fornecedor.website);
    definirValor("contactoResponsavel", fornecedor.contacto);
    definirValor("cargoContacto", fornecedor.cargo);
    definirValor("emailContacto", fornecedor.emailContacto);

    definirValor("moradaFornecedor", fornecedor.morada);
    definirValor("codigoPostalFornecedor", fornecedor.codigoPostal);
    definirValor("localidadeFornecedor", fornecedor.localidade);
    definirValor("paisFornecedor", fornecedor.pais);

    definirValor("contratoFornecedor", fornecedor.contrato);
    definirValor("inicioContratoFornecedor", fornecedor.inicioContrato);
    definirValor("fimContratoFornecedor", fornecedor.fimContrato);
    definirValor("areaAtuacaoFornecedor", fornecedor.area);
    definirValor("equipamentosAssociadosFornecedor", fornecedor.equipamentos);
    definirValor("observacoesFornecedor", fornecedor.observacoes);
}

document.addEventListener("DOMContentLoaded", function () {

    // Inicializa apenas a página ficha_fornecedor.html.
    // Esta ficha começa em modo consulta e só permite edição após clicar em Editar.
    const formFicha = document.getElementById("formFichaFornecedor");

    if (!formFicha) return;

    const fornecedorSelecionado = obterFornecedorSelecionado();
    const fornecedor = fornecedorSelecionado.dados;

    if (!fornecedor) {
        alert("Fornecedor não encontrado.");
        window.location.href = "lista_fornecedores.html";
        return;
    }

    preencherCamposFornecedor(fornecedorSelecionado.id, fornecedor);

    const btnAtivarEdicao = document.getElementById("btnAtivarEdicaoFornecedor");
    const btnCancelarEdicao = document.getElementById("btnCancelarEdicaoFornecedor");
    const botoesEdicao = document.querySelectorAll(".botao-edicao");
    const botoesConsulta = document.querySelectorAll(".botao-consulta");
    const camposFicha = formFicha.querySelectorAll(".campo-ficha");
    const camposEditaveis = formFicha.querySelectorAll(".campo-editavel");

    let valoresOriginais = {};

    function guardarValoresOriginais() {
        // Guarda uma cópia dos valores atuais antes de entrar em modo edição.
        // Assim o botão Cancelar consegue restaurar tudo como estava.
        valoresOriginais = {};

        camposFicha.forEach(function (campo) {
            if (!campo.id) return;

            if (campo.type === "radio" || campo.type === "checkbox") {
                valoresOriginais[campo.id] = campo.checked;
            } else {
                valoresOriginais[campo.id] = campo.value;
            }
        });
    }

    function restaurarValoresOriginais() {
        // Repõe os valores guardados quando o utilizador cancela a edição.
        // Campos de ficheiro não são restaurados por segurança do browser.
        camposFicha.forEach(function (campo) {
            if (!campo.id || !(campo.id in valoresOriginais)) return;

            if (campo.type === "radio" || campo.type === "checkbox") {
                campo.checked = valoresOriginais[campo.id];
            } else if (campo.type !== "file") {
                campo.value = valoresOriginais[campo.id];
            }
        });

        atualizarResumoFornecedor();
    }

    function aplicarModoConsulta() {
        // Bloqueia todos os campos editáveis e mostra apenas os botões de consulta.
        // Este é o modo inicial da ficha, igual ao comportamento da ficha de equipamento.
        camposEditaveis.forEach(function (campo) {
            if (campo.tagName === "SELECT" || campo.type === "radio" || campo.type === "checkbox" || campo.type === "file") {
                campo.disabled = true;
            } else {
                campo.readOnly = true;
            }
        });

        document.querySelectorAll(".campo-bloqueado").forEach(function (campo) {
            campo.readOnly = true;
            campo.disabled = false;
        });

        botoesEdicao.forEach(function (elemento) {
            elemento.classList.add("d-none");
        });

        botoesConsulta.forEach(function (elemento) {
            elemento.classList.remove("d-none");
        });

        formFicha.classList.remove("modo-edicao");
        formFicha.classList.add("modo-consulta");

        document.getElementById("modoFormularioFornecedor").value = "ver";
    }

    function aplicarModoEdicao() {
        // Liberta os campos editáveis e troca os botões para Cancelar/Guardar.
        // Campos bloqueados, como o código do fornecedor, continuam só de leitura.
        camposEditaveis.forEach(function (campo) {
            if (campo.tagName === "SELECT" || campo.type === "radio" || campo.type === "checkbox" || campo.type === "file") {
                campo.disabled = false;
            } else {
                campo.readOnly = false;
            }
        });

        document.querySelectorAll(".campo-bloqueado").forEach(function (campo) {
            campo.readOnly = true;
        });

        botoesEdicao.forEach(function (elemento) {
            elemento.classList.remove("d-none");
        });

        botoesConsulta.forEach(function (elemento) {
            elemento.classList.add("d-none");
        });

        formFicha.classList.remove("modo-consulta");
        formFicha.classList.add("modo-edicao");

        document.getElementById("modoFormularioFornecedor").value = "editar";
    }

    function atualizarResumoFornecedor() {
        // Atualiza textos auxiliares da ficha sempre que algum campo muda.
        // Estes elementos estão ocultos no HTML, mas ficam prontos para badges/resumos futuros.
        const codigo = document.getElementById("codigoFornecedor")?.value || "";
        const nome = document.getElementById("nomeFornecedor")?.value || "Fornecedor";
        const nif = document.getElementById("nifFornecedor")?.value || "NIF por definir";
        const localidade = document.getElementById("localidadeFornecedor")?.value || "localidade por definir";
        const telefone = document.getElementById("telefoneFornecedor")?.value || "contacto por definir";
        const estado = document.getElementById("estadoFornecedor")?.value || "Estado";
        const contrato = document.getElementById("contratoFornecedor")?.value || "Contrato";
        const tipos = obterTiposFornecedorSelecionados();

        definirTexto("tituloPaginaFornecedor", `Ficha do Fornecedor - ${codigo}`);
        definirTexto("resumoNomeFornecedor", nome);
        definirTexto("resumoDescricaoFornecedor", `${nif} | ${localidade} | ${telefone}`);
        definirTexto("badgeEstadoFornecedor", estado);
        definirTexto("badgeTiposFornecedor", tipos.length ? tipos.join(", ") : "Tipo por definir");
        definirTexto("badgeContratoFornecedor", contrato ? `Contrato: ${contrato}` : "Contrato");

        const badgeEstado = document.getElementById("badgeEstadoFornecedor");
        if (badgeEstado) {
            badgeEstado.className = `estado ${classeEstado(estado)}`;
        }
    }

    if (btnAtivarEdicao) {
        btnAtivarEdicao.addEventListener("click", function () {
            guardarValoresOriginais();
            aplicarModoEdicao();
        });
    }

    if (btnCancelarEdicao) {
        btnCancelarEdicao.addEventListener("click", function () {
            restaurarValoresOriginais();
            aplicarModoConsulta();
        });
    }

    formFicha.addEventListener("input", atualizarResumoFornecedor);
    formFicha.addEventListener("change", atualizarResumoFornecedor);

    formFicha.addEventListener("submit", function (event) {
        event.preventDefault();

        mostrarPopupSucesso(
            "Alterações registadas",
            "As alterações no fornecedor foram registadas com sucesso.",
            "lista_fornecedores.html"
        );
    });

    guardarValoresOriginais();
    atualizarResumoFornecedor();
    aplicarModoConsulta();

});

/* =========================================================
   MODAL DE REMOÇÃO DE FORNECEDOR
   Usa o mesmo padrão visual e funcional da lista de equipamentos.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    // Inicializa apenas a lista_fornecedores.html.
    // O modal usa os data-attributes do botão eliminar para mostrar o fornecedor escolhido.
    const modalApagar = document.getElementById("modalApagarFornecedor");
    const btnConfirmarApagar = document.getElementById("btnConfirmarApagarFornecedor");

    let linhaFornecedorSelecionada = null;

    if (!modalApagar || !btnConfirmarApagar) return;

    // Antes do modal abrir, preenche cada linha do resumo com os dados do botão clicado.
    modalApagar.addEventListener("show.bs.modal", function (event) {
        const botao = event.relatedTarget;

        if (!botao) return;

        linhaFornecedorSelecionada = botao.closest("tr");

        const codigo = botao.getAttribute("data-codigo");
        const nome = botao.getAttribute("data-nome");
        const tipo = botao.getAttribute("data-tipo");
        const nif = botao.getAttribute("data-nif");
        const email = botao.getAttribute("data-email");
        const telefone = botao.getAttribute("data-telefone");
        const localidade = botao.getAttribute("data-localidade");
        const estado = botao.getAttribute("data-estado");
        const equipamentos = botao.getAttribute("data-equipamentos");

        document.getElementById("modalApagarIdFornecedor").value = codigo;
        document.getElementById("modalApagarFornecedorCodigo").textContent = codigo;
        document.getElementById("modalApagarFornecedorNome").textContent = nome;
        document.getElementById("modalApagarFornecedorTipo").textContent = tipo;
        document.getElementById("modalApagarFornecedorNif").textContent = nif;
        document.getElementById("modalApagarFornecedorEmail").textContent = email;
        document.getElementById("modalApagarFornecedorTelefone").textContent = telefone;
        document.getElementById("modalApagarFornecedorLocalidade").textContent = localidade;
        document.getElementById("modalApagarFornecedorEstado").textContent = estado;
        document.getElementById("modalApagarFornecedorEquipamentos").textContent = equipamentos;
    });

    // Confirma a remoção: fecha o modal, remove a linha da tabela e mostra o pop-up.
    // Atualmente é uma remoção visual; no backend será substituída por DELETE/UPDATE real.
    btnConfirmarApagar.addEventListener("click", function () {
        const codigo = document.getElementById("modalApagarIdFornecedor").value;
        const nome = document.getElementById("modalApagarFornecedorNome").textContent;
        const modalBootstrap = bootstrap.Modal.getInstance(modalApagar);

        modalBootstrap.hide();

        if (linhaFornecedorSelecionada) {
            linhaFornecedorSelecionada.remove();
        }

        mostrarPopupSucesso(
            "Fornecedor removido",
            `O fornecedor ${codigo} - ${nome} foi removido com sucesso.`,
            "lista_fornecedores.html"
        );
    });

});

/* =========================================================
   FICHA DO EQUIPAMENTO
   Modo consulta por defeito + modo edição ao clicar em Editar
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const formFicha = document.getElementById("formFichaEquipamento");

    if (!formFicha) return;

    const equipamento = obterEquipamentoSelecionado();

    if (!equipamento) {
        alert("Equipamento não encontrado.");
        window.location.href = "lista_equipamentos.html";
        return;
    }

    preencherCamposEquipamento(equipamento);

    const btnAtivarEdicao = document.getElementById("btnAtivarEdicao");
    const btnCancelarEdicao = document.getElementById("btnCancelarEdicao");
    const botoesEdicao = document.querySelectorAll(".botao-edicao");

    const camposFicha = formFicha.querySelectorAll(".campo-ficha");
    const camposEditaveis = formFicha.querySelectorAll(".campo-editavel");
    const botoesConsulta = document.querySelectorAll(".botao-consulta");

    let valoresOriginais = {};

    function guardarValoresOriginais() {
        valoresOriginais = {};

        camposFicha.forEach(function (campo) {
            if (campo.type === "radio" || campo.type === "checkbox") {
                valoresOriginais[campo.id] = campo.checked;
            } else {
                valoresOriginais[campo.id] = campo.value;
            }
        });
    }

    function restaurarValoresOriginais() {
        camposFicha.forEach(function (campo) {
            if (!(campo.id in valoresOriginais)) return;

            if (campo.type === "radio" || campo.type === "checkbox") {
                campo.checked = valoresOriginais[campo.id];
            } else if (campo.type !== "file") {
                campo.value = valoresOriginais[campo.id];
            }
        });

        atualizarResumoFicha();
    }

    function aplicarModoConsulta() {
        camposEditaveis.forEach(function (campo) {
            if (campo.tagName === "SELECT" || campo.type === "radio" || campo.type === "checkbox" || campo.type === "file") {
                campo.disabled = true;
            } else {
                campo.readOnly = true;
            }
        });

        document.querySelectorAll(".campo-bloqueado").forEach(function (campo) {
            campo.readOnly = true;
            campo.disabled = false;
        });

        // Esconde botões de edição: Cancelar e Guardar
        botoesEdicao.forEach(function (elemento) {
            elemento.classList.add("d-none");
        });

        // Mostra botões de consulta: Voltar à Lista e Editar
        botoesConsulta.forEach(function (elemento) {
            elemento.classList.remove("d-none");
        });

        formFicha.classList.remove("modo-edicao");
        formFicha.classList.add("modo-consulta");

        document.getElementById("modoFormulario").value = "ver";
    }

    function aplicarModoEdicao() {
        camposEditaveis.forEach(function (campo) {
            if (campo.tagName === "SELECT" || campo.type === "radio" || campo.type === "checkbox" || campo.type === "file") {
                campo.disabled = false;
            } else {
                campo.readOnly = false;
            }
        });

        document.querySelectorAll(".campo-bloqueado").forEach(function (campo) {
            campo.readOnly = true;
        });

        // Mostra botões de edição: Cancelar e Guardar
        botoesEdicao.forEach(function (elemento) {
            elemento.classList.remove("d-none");
        });

        // Esconde botões de consulta: Voltar à Lista e Editar
        document.querySelectorAll(".botao-consulta").forEach(function (elemento) {
            elemento.classList.add("d-none");
        });

        formFicha.classList.remove("modo-consulta");
        formFicha.classList.add("modo-edicao");

        document.getElementById("modoFormulario").value = "editar";
    }

    function atualizarResumoFicha() {
        const codigo = document.getElementById("codigoInventario")?.value || "";
        const nome = document.getElementById("nomeEquipamento")?.value || "Equipamento Médico";
        const fabricante = document.getElementById("fabricante")?.value || "";
        const modelo = document.getElementById("modelo")?.value || "";
        const sala = document.getElementById("sala")?.value || "";

        const titulo = document.getElementById("tituloPaginaEquipamento");
        const resumoNome = document.getElementById("resumoNomeEquipamento");
        const resumoDescricao = document.getElementById("resumoDescricao");

        if (titulo) {
            titulo.textContent = `Ficha do Equipamento - ${codigo}`;
        }

        if (resumoNome) {
            resumoNome.textContent = nome;
        }

        if (resumoDescricao) {
            resumoDescricao.textContent = `${codigo} | ${fabricante} ${modelo} | ${sala}`;
        }

        const estado = document.getElementById("estado");
        const criticidade = document.getElementById("criticidade");
        const operacionalSim = document.getElementById("operacionalSim");

        const badgeEstado = document.getElementById("badgeEstado");
        const badgeCriticidade = document.getElementById("badgeCriticidade");
        const badgeOperacional = document.getElementById("badgeOperacional");

        if (badgeEstado && estado) {
            badgeEstado.textContent = estado.value || "Estado";
        }

        if (badgeCriticidade && criticidade) {
            badgeCriticidade.textContent = criticidade.value ? `Criticidade: ${criticidade.value}` : "Criticidade";
        }

        if (badgeOperacional && operacionalSim) {
            badgeOperacional.textContent = operacionalSim.checked ? "Operacional" : "Não operacional";
        }
    }

    if (btnAtivarEdicao) {
        btnAtivarEdicao.addEventListener("click", function () {
            guardarValoresOriginais();
            aplicarModoEdicao();
        });
    }

    if (btnCancelarEdicao) {
        btnCancelarEdicao.addEventListener("click", function () {
            restaurarValoresOriginais();
            aplicarModoConsulta();
        });
    }

    formFicha.addEventListener("input", atualizarResumoFicha);
    formFicha.addEventListener("change", atualizarResumoFicha);

    formFicha.addEventListener("submit", function (event) {
        event.preventDefault();

        mostrarPopupSucesso(
            "Alterações registadas",
            "As alterações no equipamento foram registadas com sucesso.",
            "lista_equipamentos.html"
        );
    });

    guardarValoresOriginais();
    atualizarResumoFicha();
    aplicarModoConsulta();

});

/* =========================================================
   MODAL DE REMOÇÃO DE EQUIPAMENTO
   Preenche o modal com os dados do equipamento selecionado
   e confirma a remoção sem usar uma página separada.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const modalApagar = document.getElementById("modalApagarEquipamento");
    const btnConfirmarApagar = document.getElementById("btnConfirmarApagarEquipamento");

    let linhaEquipamentoSelecionada = null;

    if (!modalApagar || !btnConfirmarApagar) return;

    // Quando o modal abre, vai buscar os dados ao botão clicado
    modalApagar.addEventListener("show.bs.modal", function (event) {

        const botao = event.relatedTarget;

        if (!botao) return;

        linhaEquipamentoSelecionada = botao.closest("tr");

        const codigo = botao.getAttribute("data-codigo");
        const nome = botao.getAttribute("data-nome");
        const categoria = botao.getAttribute("data-categoria");
        const fabricante = botao.getAttribute("data-fabricante");
        const modelo = botao.getAttribute("data-modelo");
        const serie = botao.getAttribute("data-serie");
        const localizacao = botao.getAttribute("data-localizacao");
        const estado = botao.getAttribute("data-estado");

        document.getElementById("modalApagarIdEquipamento").value = codigo;

        document.getElementById("modalApagarCodigo").textContent = codigo;
        document.getElementById("modalApagarNome").textContent = nome;
        document.getElementById("modalApagarCategoria").textContent = categoria;
        document.getElementById("modalApagarFabricante").textContent = fabricante;
        document.getElementById("modalApagarModelo").textContent = modelo;
        document.getElementById("modalApagarSerie").textContent = serie;
        document.getElementById("modalApagarLocalizacao").textContent = localizacao;
        document.getElementById("modalApagarEstado").textContent = estado;
    });

    // Quando o utilizador confirma a remoção
    btnConfirmarApagar.addEventListener("click", function () {

        const codigo = document.getElementById("modalApagarIdEquipamento").value;
        const nome = document.getElementById("modalApagarNome").textContent;

        // Fecha o modal Bootstrap
        const modalBootstrap = bootstrap.Modal.getInstance(modalApagar);
        modalBootstrap.hide();

        // Remove a linha da tabela apenas visualmente
        // Mais tarde, em PHP/MySQL, esta parte será substituída pelo UPDATE/DELETE na base de dados
        if (linhaEquipamentoSelecionada) {
            linhaEquipamentoSelecionada.remove();
        }

        // Mostra o pop-up visual de sucesso, se já tiveres esta função criada
        if (typeof mostrarPopupSucesso === "function") {
            mostrarPopupSucesso(
                "Equipamento removido",
                `O equipamento ${codigo} — ${nome} foi removido com sucesso.`,
                "lista_equipamentos.html"
            );
        }
    });

});

/* =========================================================
   NOVO EQUIPAMENTO
   Limpar formulário e guardar com pop-up visual
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const formNovoEquipamento = document.getElementById("formNovoEquipamento");
    const btnLimparNovoEquipamento = document.getElementById("btnLimparNovoEquipamento");

    if (!formNovoEquipamento) return;

    // Limpa manualmente todos os campos do formulário
    if (btnLimparNovoEquipamento) {
        btnLimparNovoEquipamento.addEventListener("click", function () {

            // Limpar inputs, selects e textareas
            formNovoEquipamento.querySelectorAll("input, select, textarea").forEach(function (campo) {

                if (campo.type === "radio" || campo.type === "checkbox") {
                    campo.checked = false;
                } 
                else if (campo.type === "file") {
                    campo.value = "";
                } 
                else if (campo.tagName === "SELECT") {
                    campo.selectedIndex = 0;
                } 
                else {
                    campo.value = "";
                }

            });

            // Repor opção "Sim" do campo operacional, se existir
            const operacionalSim = document.getElementById("operacionalSim");
            if (operacionalSim) {
                operacionalSim.checked = true;
            }

            // Repor descrição da criticidade
            const descricaoCriticidade = document.getElementById("descricaoCriticidade");
            if (descricaoCriticidade) {
                descricaoCriticidade.textContent = "Selecione uma criticidade para ver a descrição.";
            }

            // Se existirem documentos dinâmicos, deixa apenas o primeiro
            const listaDocumentos = document.getElementById("listaDocumentos");
            if (listaDocumentos) {
                const documentos = listaDocumentos.querySelectorAll(".documento-form-item");

                documentos.forEach(function (documento, index) {
                    if (index > 0) {
                        documento.remove();
                    }
                });
            }
        });
    }

    // Guardar novo equipamento com pop-up visual
    formNovoEquipamento.addEventListener("submit", function (event) {
        event.preventDefault();

        mostrarPopupSucesso(
            "Novo equipamento guardado",
            "O novo equipamento foi registado com sucesso no inventário.",
            "lista_equipamentos.html"
        );
    });

});

/* =========================================================
   NOVO PEDIDO DE CALIBRAÇÃO / MANUTENÇÃO
   Regista visualmente um pedido criado no modal da página
   calibracao_manutencao.html.
   ========================================================= */

function classeProcedimentoPedido(procedimento) {
    const procedimentoNormalizado = (procedimento || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");

    if (procedimentoNormalizado === "calibracao") {
        return "tipo-fornecedor tipo-calibracao";
    }

    if (procedimentoNormalizado === "manutencao corretiva") {
        return "tipo-localizacao tipo-urgencia";
    }

    // Define a cor visual do badge conforme o tipo de procedimento.
    if (procedimento === "Calibração") {
        return "tipo-fornecedor tipo-calibracao";
    }

    if (procedimento === "Manutenção corretiva") {
        return "tipo-localizacao tipo-urgencia";
    }

    return "tipo-fornecedor tipo-manutencao";
}

function classeEstadoPedido(estado) {
    // Procedimentos efetuados usam verde; os restantes ficam como operação em curso.
    return estado === "Efetuada" ? "estado estado-ativo" : "estado estado-manutencao";
}

function mostrarPopupPedidoRegistado() {
    // Mostra uma confirmação visual sem redirecionar nem perder a linha adicionada.
    const overlay = document.createElement("div");
    overlay.classList.add("popup-sucesso-overlay");

    overlay.innerHTML = `
        <div class="popup-sucesso-card">
            <div class="popup-sucesso-icone">
                <i class="fa-solid fa-check"></i>
            </div>

            <h3>Pedido registado</h3>

            <p>O pedido de calibração/manutenção foi adicionado à tabela.</p>

            <div class="popup-sucesso-barra">
                <span></span>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    setTimeout(function () {
        overlay.remove();
    }, 2400);
}

function escaparTextoPedido(valor) {
    // Evita inserir HTML vindo do campo de observações diretamente na tabela.
    const div = document.createElement("div");
    div.textContent = valor;
    return div.innerHTML;
}

function criarBotoesPedido() {
    // Cria os três botões usados em cada pedido: ver/editar, finalizar e eliminar.
    return `
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-ficha btn-ver-editar-pedido" title="Ver/editar pedido">
                <i class="fa-solid fa-file-lines"></i>
            </button>
            <button type="button" class="btn btn-sm btn-editar btn-finalizar-pedido" title="Finalizar pedido">
                <i class="fa-solid fa-check"></i>
            </button>
            <button type="button" class="btn btn-sm btn-eliminar btn-eliminar-pedido" title="Eliminar pedido">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    `;
}

function atualizarDadosLinhaPedido(linha, dados) {
    // Guarda os dados do pedido na própria linha para os modais conseguirem reutilizá-los.
    linha.dataset.codigo = dados.codigo;
    linha.dataset.equipamento = dados.equipamento;
    linha.dataset.categoria = dados.categoria;
    linha.dataset.localizacao = dados.localizacao;
    linha.dataset.procedimento = dados.procedimento;
    linha.dataset.fornecedor = dados.fornecedor;
    linha.dataset.data = dados.data;
    linha.dataset.estado = dados.estado;
    linha.dataset.observacoes = dados.observacoes;
}

function atualizarConteudoLinhaPedido(linha, dados) {
    // Atualiza o conteúdo visível da linha depois de criar ou editar um pedido.
    linha.innerHTML = `
        <td>${dados.codigo}</td>
        <td>${dados.equipamento}</td>
        <td>${dados.categoria}</td>
        <td>${dados.localizacao}</td>
        <td>
            <span class="${classeProcedimentoPedido(dados.procedimento)}">${dados.procedimento}</span>
        </td>
        <td>${dados.fornecedor}</td>
        <td>${formatarDataPT(dados.data)}</td>
        <td>
            <span class="${classeEstadoPedido(dados.estado)}">${dados.estado}</span>
        </td>
        <td>${escaparTextoPedido(dados.observacoes)}</td>
        ${criarBotoesPedido()}
    `;
}

function mostrarPopupPedido(titulo, mensagem) {
    // Mostra uma confirmação curta sem redirecionar.
    const overlay = document.createElement("div");
    overlay.classList.add("popup-sucesso-overlay");

    overlay.innerHTML = `
        <div class="popup-sucesso-card">
            <div class="popup-sucesso-icone">
                <i class="fa-solid fa-check"></i>
            </div>

            <h3>${titulo}</h3>
            <p>${mensagem}</p>

            <div class="popup-sucesso-barra">
                <span></span>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    setTimeout(function () {
        overlay.remove();
    }, 2200);
}

function guardarPedidoFinalizado(dados) {
    // Guarda pedidos finalizados no navegador para simular a página de histórico sem backend.
    const chave = "medicoreProcessosFinalizados";
    const processos = JSON.parse(localStorage.getItem(chave) || "[]");

    processos.unshift({
        ...dados,
        estado: "Efetuada",
        dataConclusao: new Date().toISOString().slice(0, 10)
    });

    localStorage.setItem(chave, JSON.stringify(processos));
}

function carregarProcessosFinalizados() {
    // Carrega para a página processos_finalizados.html os pedidos finalizados nesta sessão/navegador.
    const tabela = document.getElementById("tabelaProcessosFinalizados");
    if (!tabela) return;

    const processos = JSON.parse(localStorage.getItem("medicoreProcessosFinalizados") || "[]");

    processos.forEach(function (processo) {
        const linha = document.createElement("tr");

        linha.innerHTML = `
            <td>${processo.codigo}</td>
            <td>${processo.equipamento}</td>
            <td>${processo.categoria}</td>
            <td>${processo.localizacao}</td>
            <td><span class="${classeProcedimentoPedido(processo.procedimento)}">${processo.procedimento}</span></td>
            <td>${processo.fornecedor}</td>
            <td>${formatarDataPT(processo.dataConclusao)}</td>
            <td><span class="estado estado-ativo">Efetuada</span></td>
            <td>${escaparTextoPedido(processo.observacoes || "Sem observações adicionais.")}</td>
        `;

        tabela.prepend(linha);
    });
}

document.addEventListener("DOMContentLoaded", function () {

    const formNovoPedido = document.getElementById("formNovoPedidoCalibracaoManutencao");
    const tabelaPedidos = document.getElementById("tabelaPedidosCalibracaoManutencao");
    const formEditarPedido = document.getElementById("formEditarPedidoCalibracaoManutencao");
    const btnConfirmarEliminarPedido = document.getElementById("btnConfirmarEliminarPedidoCalibracaoManutencao");

    if (!formNovoPedido || !tabelaPedidos) return;

    let linhaPedidoSelecionada = null;

    formNovoPedido.addEventListener("submit", function (event) {
        event.preventDefault();

        const equipamentoSelect = document.getElementById("pedidoEquipamento");
        const equipamentoSelecionado = equipamentoSelect.selectedOptions[0];
        const procedimento = document.getElementById("pedidoProcedimento").value;
        const fornecedor = document.getElementById("pedidoFornecedor").value;
        const dataPrevista = document.getElementById("pedidoDataPrevista").value;
        const estado = document.getElementById("pedidoEstadoOperacao").value;
        const observacoes = document.getElementById("pedidoObservacoes").value.trim() || "Sem observações adicionais.";

        const dados = {
            codigo: equipamentoSelect.value,
            equipamento: equipamentoSelecionado.dataset.nome,
            categoria: equipamentoSelecionado.dataset.categoria,
            localizacao: equipamentoSelecionado.dataset.localizacao,
            procedimento: procedimento,
            fornecedor: fornecedor,
            data: dataPrevista,
            estado: estado,
            observacoes: observacoes
        };

        const linha = document.createElement("tr");

        atualizarDadosLinhaPedido(linha, dados);
        atualizarConteudoLinhaPedido(linha, dados);

        tabelaPedidos.prepend(linha);

        const modal = bootstrap.Modal.getInstance(document.getElementById("modalNovoPedidoCalibracaoManutencao"));
        if (modal) {
            modal.hide();
        }

        formNovoPedido.reset();
        mostrarPopupPedidoRegistado();
    });

    tabelaPedidos.addEventListener("click", function (event) {
        const botaoEditar = event.target.closest(".btn-ver-editar-pedido");
        const botaoFinalizar = event.target.closest(".btn-finalizar-pedido");
        const botaoEliminar = event.target.closest(".btn-eliminar-pedido");

        if (botaoEditar) {
            linhaPedidoSelecionada = botaoEditar.closest("tr");

            document.getElementById("editarPedidoCodigo").value = linhaPedidoSelecionada.dataset.codigo;
            document.getElementById("editarPedidoEquipamento").value = linhaPedidoSelecionada.dataset.equipamento;
            document.getElementById("editarPedidoCategoria").value = linhaPedidoSelecionada.dataset.categoria;
            document.getElementById("editarPedidoLocalizacao").value = linhaPedidoSelecionada.dataset.localizacao;
            document.getElementById("editarPedidoProcedimento").value = linhaPedidoSelecionada.dataset.procedimento;
            document.getElementById("editarPedidoFornecedor").value = linhaPedidoSelecionada.dataset.fornecedor;
            document.getElementById("editarPedidoData").value = linhaPedidoSelecionada.dataset.data;
            document.getElementById("editarPedidoEstado").value = linhaPedidoSelecionada.dataset.estado;
            document.getElementById("editarPedidoObservacoes").value = linhaPedidoSelecionada.dataset.observacoes;

            new bootstrap.Modal(document.getElementById("modalEditarPedidoCalibracaoManutencao")).show();
        }

        if (botaoFinalizar) {
            const linha = botaoFinalizar.closest("tr");
            const codigo = linha.dataset.codigo;
            const equipamento = linha.dataset.equipamento;

            guardarPedidoFinalizado({
                codigo: linha.dataset.codigo,
                equipamento: linha.dataset.equipamento,
                categoria: linha.dataset.categoria,
                localizacao: linha.dataset.localizacao,
                procedimento: linha.dataset.procedimento,
                fornecedor: linha.dataset.fornecedor,
                data: linha.dataset.data,
                estado: "Efetuada",
                observacoes: linha.dataset.observacoes
            });

            linha.remove();

            mostrarPopupPedido(
                "Pedido finalizado",
                `O pedido do equipamento ${codigo} - ${equipamento} foi marcado como finalizado.`
            );
        }

        if (botaoEliminar) {
            linhaPedidoSelecionada = botaoEliminar.closest("tr");

            document.getElementById("modalEliminarPedidoCodigo").textContent = linhaPedidoSelecionada.dataset.codigo;
            document.getElementById("modalEliminarPedidoEquipamento").textContent = linhaPedidoSelecionada.dataset.equipamento;
            document.getElementById("modalEliminarPedidoProcedimento").textContent = linhaPedidoSelecionada.dataset.procedimento;
            document.getElementById("modalEliminarPedidoEstado").textContent = linhaPedidoSelecionada.dataset.estado;

            new bootstrap.Modal(document.getElementById("modalEliminarPedidoCalibracaoManutencao")).show();
        }
    });

    if (formEditarPedido) {
        formEditarPedido.addEventListener("submit", function (event) {
            event.preventDefault();

            if (!linhaPedidoSelecionada) return;

            const dados = {
                codigo: linhaPedidoSelecionada.dataset.codigo,
                equipamento: linhaPedidoSelecionada.dataset.equipamento,
                categoria: linhaPedidoSelecionada.dataset.categoria,
                localizacao: linhaPedidoSelecionada.dataset.localizacao,
                procedimento: document.getElementById("editarPedidoProcedimento").value,
                fornecedor: document.getElementById("editarPedidoFornecedor").value,
                data: document.getElementById("editarPedidoData").value,
                estado: document.getElementById("editarPedidoEstado").value,
                observacoes: document.getElementById("editarPedidoObservacoes").value.trim() || "Sem observações adicionais."
            };

            atualizarDadosLinhaPedido(linhaPedidoSelecionada, dados);
            atualizarConteudoLinhaPedido(linhaPedidoSelecionada, dados);

            const modal = bootstrap.Modal.getInstance(document.getElementById("modalEditarPedidoCalibracaoManutencao"));
            if (modal) modal.hide();

            mostrarPopupPedido("Pedido atualizado", "As alterações do pedido foram guardadas com sucesso.");
        });
    }

    if (btnConfirmarEliminarPedido) {
        btnConfirmarEliminarPedido.addEventListener("click", function () {
            if (!linhaPedidoSelecionada) return;

            linhaPedidoSelecionada.remove();

            const modal = bootstrap.Modal.getInstance(document.getElementById("modalEliminarPedidoCalibracaoManutencao"));
            if (modal) modal.hide();

            mostrarPopupPedido("Pedido removido", "O pedido foi removido da lista de processos a decorrer.");
        });
    }

});

document.addEventListener("DOMContentLoaded", carregarProcessosFinalizados);
