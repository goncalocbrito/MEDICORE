// JavaScript GonÃ§alo Brito 1230404
// Funcionalidades da Ã¡rea privada MEDICORE

// Dados temporÃ¡rios dos equipamentos.
// Quando o backend estiver implementado, estes dados passam a vir da base de dados MySQL.
const equipamentosMEDICORE = {
    "EQ-001": {
        codigo: "EQ-001",
        nome: "Monitor MultiparamÃ©trico",
        categoria: "MonitorizaÃ§Ã£o",
        fabricante: "Philips",
        modelo: "IntelliVue MX450",
        serie: "SN-MX450-2024",
        anoFabrico: "2023",
        tipoEntrada: "Compra",
        departamento: "Unidade de Cuidados Intensivos",
        edificio: "EdifÃ­cio A",
        piso: "2",
        sala: "Sala 2",
        localizacao: "UCI - Sala 2",
        estado: "Ativo",
        criticidade: "CrÃ­tica",
        operacional: "Operacional",
        fornecedor: "MedSupply Portugal",
        dataFabrico: "2023-11-10",
        dataAquisicao: "2024-01-15",
        dataInstalacao: "2024-01-20",
        valorAquisicao: "3500.00",
        inicioGarantia: "2024-01-20",
        fimGarantia: "2027-01-20",
        contratoManutencao: "Sim",
        tipoContrato: "ManutenÃ§Ã£o preventiva anual",
        entidadeContrato: "MedSupply Portugal",
        ultimaManutencao: "2026-03-12",
        proximaManutencao: "2026-09-12",
        ultimaCalibracao: "2026-03-12",
        proximaCalibracao: "2026-09-12",
        periodicidade: "Semestral",
        responsavelTecnico: "Eng. GonÃ§alo Brito",
        acessorios: [
            {
                codigo: "ACC-001",
                nome: "Cabo ECG 5 derivaÃ§Ãµes",
                tipo: "Cabo",
                serie: "ECG-5D-2024",
                estado: "Ativo",
                intervencao: "não",
                proximaIntervencao: "Por definir"
            },
            {
                codigo: "ACC-002",
                nome: "Sensor SpO2",
                tipo: "Sensor",
                serie: "SPO2-4482",
                estado: "Ativo",
                intervencao: "Sim",
                proximaIntervencao: "2026-09-12"
            },
            {
                codigo: "ACC-003",
                nome: "BraÃ§adeira NIBP adulto",
                tipo: "ConsumÃ­vel reutilizÃ¡vel",
                serie: "NIBP-1120",
                estado: "Ativo",
                intervencao: "Sim",
                proximaIntervencao: "2026-09-12"
            }
        ],
        observacoes: "Equipamento essencial para monitorizaÃ§Ã£o contÃ­nua de parÃ¢metros vitais em contexto de cuidados intensivos."
    },

    "EQ-002": {
        codigo: "EQ-002",
        nome: "Ventilador Pulmonar",
        categoria: "Suporte de Vida",
        fabricante: "DrÃ¤ger",
        modelo: "Evita V300",
        serie: "SN-EV300-1198",
        anoFabrico: "2022",
        tipoEntrada: "Compra",
        departamento: "UrgÃªncia",
        edificio: "EdifÃ­cio B",
        piso: "0",
        sala: "Sala 1",
        localizacao: "UrgÃªncia - Sala 1",
        estado: "Em manutenÃ§Ã£o",
        criticidade: "CrÃ­tica",
        operacional: "não operacional",
        fornecedor: "Biomedical Solutions",
        dataFabrico: "2022-12-05",
        dataAquisicao: "2023-06-10",
        dataInstalacao: "2023-06-18",
        valorAquisicao: "12500.00",
        inicioGarantia: "2023-06-18",
        fimGarantia: "2026-06-18",
        contratoManutencao: "Sim",
        tipoContrato: "ManutenÃ§Ã£o preventiva e corretiva",
        entidadeContrato: "Biomedical Solutions",
        ultimaManutencao: "2026-02-28",
        proximaManutencao: "2026-08-28",
        ultimaCalibracao: "2026-02-28",
        proximaCalibracao: "2026-08-28",
        periodicidade: "Semestral",
        responsavelTecnico: "Eng. GonÃ§alo Brito",
        acessorios: [
            {
                codigo: "ACC-004",
                nome: "Circuito respiratÃ³rio reutilizÃ¡vel",
                tipo: "MÃ³dulo",
                serie: "CIR-2201",
                estado: "Ativo",
                intervencao: "Sim",
                proximaIntervencao: "2026-08-28"
            }
        ],
        observacoes: "Equipamento em manutenÃ§Ã£o preventiva. Deve ser validado antes de regressar ao serviÃ§o clÃ­nico."
    },

    "EQ-003": {
        codigo: "EQ-003",
        nome: "Desfibrilhador",
        categoria: "EmergÃªncia",
        fabricante: "Zoll",
        modelo: "R Series",
        serie: "SN-ZOLL-8821",
        anoFabrico: "2021",
        tipoEntrada: "Compra",
        departamento: "Bloco OperatÃ³rio",
        edificio: "EdifÃ­cio C",
        piso: "1",
        sala: "Bloco OperatÃ³rio",
        localizacao: "Bloco OperatÃ³rio",
        estado: "Avariado",
        criticidade: "CrÃ­tica",
        operacional: "não operacional",
        fornecedor: "ClinicalTech Equipamentos",
        dataFabrico: "2021-05-20",
        dataAquisicao: "2022-09-02",
        dataInstalacao: "2022-09-08",
        valorAquisicao: "8900.00",
        inicioGarantia: "2022-09-08",
        fimGarantia: "2025-09-08",
        contratoManutencao: "Em anÃ¡lise",
        tipoContrato: "Por definir",
        entidadeContrato: "ClinicalTech Equipamentos",
        ultimaManutencao: "2026-01-05",
        proximaManutencao: "",
        ultimaCalibracao: "2026-01-05",
        proximaCalibracao: "",
        periodicidade: "Anual",
        responsavelTecnico: "Eng. GonÃ§alo Brito",
        acessorios: [
            {
                codigo: "ACC-005",
                nome: "PÃ¡s adulto",
                tipo: "MÃ³dulo",
                serie: "PAS-8821",
                estado: "Avariado",
                intervencao: "Sim",
                proximaIntervencao: "Por definir"
            }
        ],
        observacoes: "Equipamento sinalizado como avariado. Deve permanecer indisponÃ­vel até avaliaÃ§Ã£o tÃ©cnica e reparaÃ§Ã£o."
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
        "Em manutenÃ§Ã£o": "estado-manutencao",
        "Avariado": "estado-avariado",
        "Inativo": "estado-inativo",
        "Inativa": "estado-inativo",
        "IndisponÃ­vel": "estado-inativo",
        "Em calibraÃ§Ã£o": "estado-manutencao",
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

function criarLinhaAcessorioEquipamento(acessorio) {
    // Cria uma linha visual para a tabela de acessÃ³rios da ficha.
    // O cÃ³digo do acessÃ³rio Ã© independente, mas a associaÃ§Ã£o fica no contexto do equipamento aberto.
    const linha = document.createElement("tr");

    linha.innerHTML = `
        <td>${escaparTextoPedido(acessorio.codigo || "---")}</td>
        <td>${escaparTextoPedido(acessorio.nome || "---")}</td>
        <td>${escaparTextoPedido(acessorio.tipo || "---")}</td>
        <td>${escaparTextoPedido(acessorio.serie || "---")}</td>
        <td><span class="estado ${classeEstado(acessorio.estado || "Ativo")}">${escaparTextoPedido(acessorio.estado || "Ativo")}</span></td>
        <td>${escaparTextoPedido(acessorio.intervencao || "não")}</td>
        <td>${formatarDataPT(acessorio.proximaIntervencao || "")}</td>
    `;

    return linha;
}

function preencherAcessoriosEquipamento(equipamento) {
    // Preenche a tabela de acessÃ³rios da ficha do equipamento selecionado.
    const tabela = $("tabelaAcessoriosEquipamento");
    if (!tabela) return;

    tabela.innerHTML = "";

    const acessorios = equipamento.acessorios || [];

    if (!acessorios.length) {
        const linhaVazia = document.createElement("tr");
        linhaVazia.innerHTML = `<td colspan="7" class="text-center text-muted">Sem acessÃ³rios associados a este equipamento.</td>`;
        tabela.appendChild(linhaVazia);
        return;
    }

    acessorios.forEach(function (acessorio) {
        tabela.appendChild(criarLinhaAcessorioEquipamento(acessorio));
    });
}

function atualizarResumoFicha() {
    const codigo = $("codigoInventario")?.value || "---";
    const nome = $("nomeEquipamento")?.value || "Equipamento MÃ©dico";
    const fabricante = $("fabricante")?.value || "";
    const modelo = $("modelo")?.value || "";
    const localizacao = $("sala")?.value || $("departamento")?.value || "localizaÃ§Ã£o por definir";
    const estado = $("estado")?.value || "Estado";
    const criticidade = $("criticidade")?.value || "Criticidade";
    const operacional = $("operacionalSim")?.checked ? "Operacional" : "não operacional";

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

        novoDocumento.querySelectorAll("input, select, textarea").forEach(function (campo) {
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
   Usado para confirmaÃ§Ãµes como guardar alteraÃ§Ãµes, registos, etc.
   ========================================================= */

function mostrarPopupSucesso(titulo, mensagem, paginaDestino) {

    // Cria um overlay visual reutilizÃ¡vel para confirmaÃ§Ãµes de sucesso.
    // O destino recebido define tambÃ©m o texto da lista para onde o utilizador serÃ¡ redirecionado.
    const textoListaDestino = paginaDestino.includes("fornecedores")
        ? "de fornecedores"
        : paginaDestino.includes("localizacoes")
            ? "de localizaÃ§Ãµes"
            : paginaDestino.includes("utilizadores")
                ? "de utilizadores"
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

// Dados temporÃ¡rios dos fornecedores

const fornecedoresMEDICORE = {
    "FOR-001": {
        nome: "Philips Medical Systems",
        tipos: ["Fabricante"],
        nif: "509123456",
        email: "suporte@philips-med.pt",
        telefone: "+351 220 000 111",
        website: "https://www.philips.pt",
        contacto: "Carlos Almeida",
        cargo: "Suporte TÃ©cnico",
        emailContacto: "carlos.almeida@philips-med.pt",
        morada: "Rua da Tecnologia MÃ©dica, 45",
        codigoPostal: "4100-000",
        localidade: "Porto",
        pais: "Portugal",
        estado: "Ativo",
        estadoClasse: "estado-ativo",
        contrato: "Sim",
        inicioContrato: "2024-01-01",
        fimContrato: "2027-01-01",
        qtdEquipamentos: "12",
        area: "Fabrico e suporte tÃ©cnico de equipamentos de monitorizaÃ§Ã£o clÃ­nica.",
        equipamentos: "Monitores multiparamÃ©tricos Philips IntelliVue.",
        observacoes: "Fornecedor associado a equipamentos de monitorizaÃ§Ã£o em unidades crÃ­ticas.",

        equipamentosAssociados: [
            {
                codigo: "EQ-001",
                nome: "Monitor MultiparamÃ©trico",
                categoria: "MonitorizaÃ§Ã£o",
                modelo: "IntelliVue MX450",
                serie: "SN-MX450-2024",
                relacao: "Fabricante",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-004",
                nome: "Monitor de Sinais Vitais",
                categoria: "MonitorizaÃ§Ã£o",
                modelo: "SureSigns VS4",
                serie: "SN-VS4-2025",
                relacao: "Fabricante",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-005",
                nome: "Bomba de InfusÃ£o",
                categoria: "TerapÃªutica",
                modelo: "InfusionCare P200",
                serie: "SN-P200-2024",
                relacao: "Fabricante",
                estado: "Em manutenÃ§Ã£o",
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
        morada: "Avenida dos Dispositivos MÃ©dicos, 80",
        codigoPostal: "1000-000",
        localidade: "Lisboa",
        pais: "Portugal",
        estado: "Ativo",
        estadoClasse: "estado-ativo",
        contrato: "Sim",
        inicioContrato: "2024-03-01",
        fimContrato: "2026-03-01",
        qtdEquipamentos: "8",
        area: "Venda e distribuiÃ§Ã£o de dispositivos e equipamentos mÃ©dicos.",
        equipamentos: "Bombas de infusÃ£o, monitores e acessÃ³rios clÃ­nicos.",
        observacoes: "Fornecedor com boa resposta comercial e disponibilidade de stock.",

        equipamentosAssociados: [
            {
                codigo: "EQ-001",
                nome: "Monitor MultiparamÃ©trico",
                categoria: "MonitorizaÃ§Ã£o",
                modelo: "IntelliVue MX450",
                relacao: "Distribuidor",
                serie: "SN-XT42-2024",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-006",
                nome: "OxÃ­metro de Pulso",
                categoria: "MonitorizaÃ§Ã£o",
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
        tipos: ["ManutenÃ§Ã£o"],
        nif: "507654789",
        email: "tecnica@biomedicalsolutions.pt",
        telefone: "+351 222 456 789",
        website: "https://www.biomedicalsolutions.pt",
        contacto: "Rui Oliveira",
        cargo: "ResponsÃ¡vel TÃ©cnico",
        emailContacto: "rui.oliveira@biomedicalsolutions.pt",
        morada: "Rua da Engenharia BiomÃ©dica, 12",
        codigoPostal: "4470-000",
        localidade: "Maia",
        pais: "Portugal",
        estado: "Ativo",
        estadoClasse: "estado-ativo",
        contrato: "Sim",
        inicioContrato: "2025-01-01",
        fimContrato: "2027-12-31",
        qtdEquipamentos: "5",
        area: "ManutenÃ§Ã£o preventiva e corretiva de equipamentos hospitalares.",
        equipamentos: "Ventiladores, monitores e equipamentos de suporte clÃ­nico.",
        observacoes: "Fornecedor responsÃ¡vel por manutenÃ§Ãµes tÃ©cnicas periÃ³dicas."
    },

    "FOR-004": {
        nome: "CalibraMed",
        tipos: ["CalibraÃ§Ã£o"],
        nif: "515321987",
        email: "calibracao@calibramed.pt",
        telefone: "+351 223 987 654",
        website: "https://www.calibramed.pt",
        contacto: "Marta Costa",
        cargo: "TÃ©cnica de CalibraÃ§Ã£o",
        emailContacto: "marta.costa@calibramed.pt",
        morada: "Parque Tecnológico de Braga",
        codigoPostal: "4700-000",
        localidade: "Braga",
        pais: "Portugal",
        estado: "Inativo",
        estadoClasse: "estado-inativo",
        contrato: "não",
        inicioContrato: "2023-01-01",
        fimContrato: "2024-12-31",
        qtdEquipamentos: "3",
        area: "Calibração e emissão de certificados técnicos.",
        equipamentos: "Equipamentos de medições, monitores e dispositivos laboratoriais.",
        observacoes: "Fornecedor inativo, mantendo apenas histórico de calibraÃ§Ãµes anteriores."
    }
};

// Card visual de confirmaÃ§Ã£o de remoÃ§Ã£o

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

// Dashboard de GestÃ£o MEDICORE
// AlteraÃ§Ã£o feita por mim

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
            labels: ["Ativos", "Em manutenÃ§Ã£o", "Avariados", "Inativos", "Abatidos"],
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
            labels: ["MonitorizaÃ§Ã£o", "Suporte de Vida", "Imagiologia", "LaboratÃ³rio", "Cirurgia", "DiagnÃ³stico"],
            datasets: [{
                label: "NÂº de equipamentos",
                data: [30, 18, 12, 22, 16, 30],
                backgroundColor: "#4fb3a4",
                borderRadius: 10
            }]
        },
        options: opcoesGraficos
    });

    // Equipamentos por localizaÃ§Ã£o
    new Chart(document.getElementById("graficoLocalizacaoEquipamentos"), {
        type: "bar",
        data: {
            labels: ["UCI", "UrgÃªncia", "Bloco OperatÃ³rio", "Radiologia", "LaboratÃ³rio", "Consulta Externa"],
            datasets: [{
                label: "NÂº de equipamentos",
                data: [18, 22, 15, 10, 20, 12],
                backgroundColor: "#123c46",
                borderRadius: 10
            }]
        },
        options: opcoesGraficos
    });

    // Equipamentos de suporte de vida por serviÃ§o
    new Chart(document.getElementById("graficoSuporteVida"), {
        type: "bar",
        data: {
            labels: ["UCI", "UrgÃªncia", "Bloco OperatÃ³rio", "Neonatologia", "Cardiologia"],
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

// Nova localizaÃ§Ã£o

/* =========================================================
   NOVA LOCALIZAÃ‡ÃƒO / FICHA LOCALIZAÃ‡ÃƒO
   GeraÃ§Ã£o automÃ¡tica do cÃ³digo da localizaÃ§Ã£o
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    const departamentoSigla = document.getElementById("departamentoSigla");
    const pisoLocalizacao = document.getElementById("pisoLocalizacao");
    const salaLocalizacao = document.getElementById("salaLocalizacao");
    const codigoLocalizacao = document.getElementById("codigoLocalizacao");

    if (!departamentoSigla || !pisoLocalizacao || !salaLocalizacao || !codigoLocalizacao) {
        return;
    }

    function limparTextoCodigo(texto) {
        return texto
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-zA-Z0-9-]/g, "")
            .toUpperCase();
    }

    function normalizarPisoParaCodigo(piso) {
        const pisoLimpo = piso.trim();

        if (pisoLimpo === "-1") {
            return "M1";
        }

        if (pisoLimpo.startsWith("-")) {
            return "M" + pisoLimpo.replace("-", "");
        }

        return limparTextoCodigo(pisoLimpo);
    }

    function gerarCodigoLocalizacao() {
        const sigla = limparTextoCodigo(departamentoSigla.value.trim());
        const piso = normalizarPisoParaCodigo(pisoLocalizacao.value.trim());
        const sala = limparTextoCodigo(salaLocalizacao.value.trim());

        if (sigla === "" || piso === "" || sala === "") {
            codigoLocalizacao.value = "";
            return;
        }

        codigoLocalizacao.value = sigla + "-P" + piso + "-S" + sala;
    }

    departamentoSigla.addEventListener("input", gerarCodigoLocalizacao);
    pisoLocalizacao.addEventListener("input", gerarCodigoLocalizacao);
    salaLocalizacao.addEventListener("input", gerarCodigoLocalizacao);

    gerarCodigoLocalizacao();
});

/* =========================================================
   BOTÃƒO LIMPAR - NOVA LOCALIZAÃ‡ÃƒO
   Limpa o formulário sem impedir o POST normal do PHP
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    const formNovaLocalizacao = document.getElementById("formNovaLocalizacao");
    const btnLimparNovaLocalizacao = document.getElementById("btnLimparNovaLocalizacao");

    if (!formNovaLocalizacao || !btnLimparNovaLocalizacao) {
        return;
    }

    btnLimparNovaLocalizacao.addEventListener("click", function () {
        formNovaLocalizacao.querySelectorAll("input, select, textarea").forEach(function (campo) {
            if (campo.type === "hidden") {
                return;
            }

            if (campo.type === "radio" || campo.type === "checkbox") {
                campo.checked = false;
                return;
            }

            if (campo.tagName === "SELECT") {
                campo.selectedIndex = 0;
                return;
            }

            campo.value = "";
        });

        const permiteCriticosNao = document.getElementById("permiteCriticosNao");

        if (permiteCriticosNao) {
            permiteCriticosNao.checked = true;
        }
    });
});


// Dados temporÃ¡rios das localizaÃ§Ãµes
// AlteraÃ§Ã£o feita por mim

const localizacoesMEDICORE = {
    "LOC-001": {
        departamento: "Unidade de Cuidados Intensivos",
        edificio: "EdifÃ­cio A",
        piso: "2",
        sala: "Sala 201",
        tipoEspaco: "UCI",
        estado: "Ativa",
        estadoClasse: "estado-ativo",
        responsavel: "Enf. Maria Costa",
        funcao: "Enfermeira ResponsÃ¡vel",
        contacto: "Ext. 2201",
        email: "maria.costa@hospital.pt",
        notasContacto: "Contactar preferencialmente durante o turno da manhÃ£.",
        acesso: "Apenas pessoal autorizado",
        criticidade: "CrÃ­tica",
        permiteCriticos: "Sim",
        suporteVida: "Sim",
        capacidade: "10 equipamentos",
        qtdEquipamentos: 8,
        equipamentosAtivos: 7,
        equipamentosManutencao: 1,
        equipamentosAvariados: 0,
        ocupacao: "80%",
        observacoes: "Ãrea crÃ­tica com equipamentos de suporte de vida e monitorizaÃ§Ã£o contÃ­nua.",
        equipamentosAssociados: [
            {
                codigo: "EQ-001",
                nome: "Monitor MultiparamÃ©trico",
                categoria: "MonitorizaÃ§Ã£o",
                modelo: "IntelliVue MX450",
                serie: "SN-MX450-2024",
                criticidade: "CrÃ­tica",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            },
            {
                codigo: "EQ-002",
                nome: "Ventilador Pulmonar",
                categoria: "Suporte de Vida",
                modelo: "Evita V300",
                serie: "SN-EV300-1198",
                criticidade: "CrÃ­tica",
                estado: "Em manutenÃ§Ã£o",
                estadoClasse: "estado-manutencao"
            }
        ]
    },

    "LOC-002": {
        departamento: "UrgÃªncia",
        edificio: "EdifÃ­cio B",
        piso: "0",
        sala: "Sala 1",
        tipoEspaco: "UrgÃªncia",
        estado: "Ativa",
        estadoClasse: "estado-ativo",
        responsavel: "Dr. JoÃ£o Martins",
        funcao: "Coordenador de ServiÃ§o",
        contacto: "Ext. 1101",
        email: "joao.martins@hospital.pt",
        notasContacto: "ServiÃ§o com funcionamento permanente.",
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
        observacoes: "Ãrea de elevada rotaÃ§Ã£o com necessidade de resposta tÃ©cnica rÃ¡pida.",
        equipamentosAssociados: [
            {
                codigo: "EQ-006",
                nome: "OxÃ­metro de Pulso",
                categoria: "MonitorizaÃ§Ã£o",
                modelo: "OxiPro 300",
                serie: "SN-OXI-300-2025",
                criticidade: "MÃ©dia",
                estado: "Ativo",
                estadoClasse: "estado-ativo"
            }
        ]
    },

    "LOC-003": {
        departamento: "Bloco OperatÃ³rio",
        edificio: "EdifÃ­cio C",
        piso: "1",
        sala: "BO-02",
        tipoEspaco: "Bloco OperatÃ³rio",
        estado: "Ativa",
        estadoClasse: "estado-ativo",
        responsavel: "Enf. Ricardo Silva",
        funcao: "ResponsÃ¡vel de Bloco",
        contacto: "Ext. 3102",
        email: "ricardo.silva@hospital.pt",
        notasContacto: "Evitar contacto durante perÃ­odos cirÃºrgicos.",
        acesso: "Apenas pessoal autorizado",
        criticidade: "CrÃ­tica",
        permiteCriticos: "Sim",
        suporteVida: "Sim",
        capacidade: "8 equipamentos",
        qtdEquipamentos: 6,
        equipamentosAtivos: 5,
        equipamentosManutencao: 0,
        equipamentosAvariados: 1,
        ocupacao: "75%",
        observacoes: "LocalizaÃ§Ã£o com equipamentos de anestesia, emergÃªncia e suporte intraoperatÃ³rio.",
        equipamentosAssociados: [
            {
                codigo: "EQ-003",
                nome: "Desfibrilhador",
                categoria: "EmergÃªncia",
                modelo: "R Series",
                serie: "SN-ZOLL-8821",
                criticidade: "CrÃ­tica",
                estado: "Avariado",
                estadoClasse: "estado-avariado"
            }
        ]
    },

    "LOC-004": {
        departamento: "LaboratÃ³rio ClÃ­nico",
        edificio: "EdifÃ­cio D",
        piso: "1",
        sala: "Lab-105",
        tipoEspaco: "LaboratÃ³rio",
        estado: "Em manutenÃ§Ã£o",
        estadoClasse: "estado-manutencao",
        responsavel: "TÃ©c. Ana Ferreira",
        funcao: "TÃ©cnica Coordenadora",
        contacto: "Ext. 4105",
        email: "ana.ferreira@hospital.pt",
        notasContacto: "Contactar em horÃ¡rio laboral.",
        acesso: "Acesso tÃ©cnico",
        criticidade: "Alta",
        permiteCriticos: "Sim",
        suporteVida: "não",
        capacidade: "12 equipamentos",
        qtdEquipamentos: 10,
        equipamentosAtivos: 8,
        equipamentosManutencao: 2,
        equipamentosAvariados: 0,
        ocupacao: "83%",
        observacoes: "Zona laboratorial com equipamentos sujeitos a calibraÃ§Ã£o periÃ³dica.",
        equipamentosAssociados: []
    },

    "LOC-005": {
        departamento: "ArmazÃ©m TÃ©cnico",
        edificio: "EdifÃ­cio TÃ©cnico",
        piso: "-1",
        sala: "ARM-01",
        tipoEspaco: "ArmazÃ©m TÃ©cnico",
        estado: "Inativa",
        estadoClasse: "estado-inativo",
        responsavel: "Eng. GonÃ§alo Brito",
        funcao: "Engenheiro BiomÃ©dico",
        contacto: "Ext. 5001",
        email: "g.brito@hospital.pt",
        notasContacto: "EspaÃ§o reservado a equipamentos em stock ou abatidos.",
        acesso: "Acesso tÃ©cnico",
        criticidade: "Baixa",
        permiteCriticos: "não",
        suporteVida: "não",
        capacidade: "20 equipamentos",
        qtdEquipamentos: 4,
        equipamentosAtivos: 0,
        equipamentosManutencao: 0,
        equipamentosAvariados: 0,
        ocupacao: "20%",
        observacoes: "LocalizaÃ§Ã£o destinada a armazenamento tÃ©cnico e equipamentos fora de utilizaÃ§Ã£o.",
        equipamentosAssociados: []
    }
};


// Detalhes da localizaÃ§Ã£o
// AlteraÃ§Ã£o feita por mim

document.addEventListener("DOMContentLoaded", function () {

    const paginaDetalhesLocalizacao = document.getElementById("detalheLocalizacaoTitulo");

    if (!paginaDetalhesLocalizacao) return;

    const parametros = new URLSearchParams(window.location.search);
    const idLocalizacao = parametros.get("id");
    const localizacao = localizacoesMEDICORE[idLocalizacao];

    if (!localizacao) {
        alert("LocalizaÃ§Ã£o não encontrada.");
        window.location.href = "lista_localizacoes.html";
        return;
    }

    const titulo = `${localizacao.departamento} â€” ${localizacao.edificio}, Piso ${localizacao.piso}, ${localizacao.sala}`;

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
                    não existem equipamentos associados a esta localizaÃ§Ã£o.
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
// Preencher dados da localizaÃ§Ã£o e confirmar remoÃ§Ã£o
// AlteraÃ§Ã£o feita por mim

document.addEventListener("DOMContentLoaded", function () {

    const botaoRemoverLocalizacao = document.getElementById("btnConfirmarRemocaoLocalizacao");

    if (!botaoRemoverLocalizacao) return;

    const parametros = new URLSearchParams(window.location.search);
    const idLocalizacao = parametros.get("id");
    const localizacao = localizacoesMEDICORE[idLocalizacao];

    if (!localizacao) {
        alert("LocalizaÃ§Ã£o não encontrada.");
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
            "LocalizaÃ§Ã£o",
            `${localizacao.departamento} â€” ${localizacao.edificio}, Piso ${localizacao.piso}, ${localizacao.sala}`,
            "lista_localizacoes.html"
        );
    });

});

/* =========================================================
   FICHA DA LOCALIZAÇÃO
   Atualiza os dados/resumos da ficha enquanto os campos são editados.
   ========================================================= */

function obterLocalizacaoSelecionada() {
    // LÃª o id da localizaÃ§Ã£o na query string.
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
    // Copia os dados temporÃ¡rios de localizacoesMEDICORE para a ficha.
    // Quando existir backend, esta funÃ§Ã£o pode receber dados vindos da base de dados.
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
    // Preenche a tabela de equipamentos associados dentro da ficha da localizaÃ§Ã£o.
    const tabela = document.getElementById("tabelaEquipamentosFichaLocalizacao");
    if (!tabela) return;

    const equipamentos = localizacao.equipamentosAssociados || [];
    tabela.innerHTML = "";

    if (equipamentos.length === 0) {
        tabela.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    não existem equipamentos associados a esta localizaÃ§Ã£o.
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

    // Inicializa apenas a Página ficha_localizacao.html.
    if (!window.location.pathname.endsWith("ficha_localizacao.html")) return;

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

    function atualizarResumoLocalizacao() {
        // Atualiza os elementos ocultos de resumo/badges sempre que a ficha muda.
        const codigo = document.getElementById("codigoLocalizacao")?.value || "";
        const departamento = document.getElementById("departamentoLocalizacao")?.value || "LocalizaÃ§Ã£o";
        const edificio = document.getElementById("edificioLocalizacao")?.value || "EdifÃ­cio";
        const piso = document.getElementById("pisoLocalizacao")?.value || "Piso";
        const sala = document.getElementById("salaLocalizacao")?.value || "Sala";
        const estado = document.getElementById("estadoLocalizacao")?.value || "Estado";
        const tipo = document.getElementById("tipoEspaco")?.value || "Tipo";
        const criticidade = document.getElementById("criticidadeLocalizacao")?.value || "Criticidade";

        definirTexto("tituloPaginaLocalizacao", `Ficha da LocalizaÃ§Ã£o - ${codigo}`);
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


    formFicha.addEventListener("input", atualizarResumoLocalizacao);
    formFicha.addEventListener("change", atualizarResumoLocalizacao);

    atualizarResumoLocalizacao();
});

/* =========================================================
   MODAL DE REMOCAO DE UTILIZADOR
   Preenche o modal com os dados da linha e deixa o PHP fazer a remocao logica.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const modalApagar = document.getElementById("modalApagarUtilizador");

    if (!modalApagar) return;

    modalApagar.addEventListener("show.bs.modal", function (event) {
        const botao = event.relatedTarget;

        if (!botao) return;

        const id = botao.getAttribute("data-id") || "";
        const codigo = botao.getAttribute("data-codigo") || "---";
        const nome = botao.getAttribute("data-nome") || "---";
        const tipo = botao.getAttribute("data-tipo") || "---";
        const cartao = botao.getAttribute("data-cartao") || "---";
        const email = botao.getAttribute("data-email") || "---";
        const telefone = botao.getAttribute("data-telefone") || "---";
        const servico = botao.getAttribute("data-servico") || "---";
        const estado = botao.getAttribute("data-estado") || "---";

        definirValor("modalApagarIdUtilizador", id);
        definirTexto("modalApagarUtilizadorCodigo", codigo);
        definirTexto("modalApagarUtilizadorNome", nome);
        definirTexto("modalApagarUtilizadorTipo", tipo);
        definirTexto("modalApagarUtilizadorCartao", cartao);
        definirTexto("modalApagarUtilizadorEmail", email);
        definirTexto("modalApagarUtilizadorTelefone", telefone);
        definirTexto("modalApagarUtilizadorServico", servico);
        definirTexto("modalApagarUtilizadorEstado", estado);
    });

});

/* =========================================================
   FICHA DO UTILIZADOR
   Atualiza os resumos da ficha enquanto os campos são editados.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    // Inicializa apenas a Página ficha_utilizador.html.
    // Se o formulário da ficha não existir, esta funÃ§Ã£o termina sem afetar outras Páginas.
    const formFicha = document.getElementById("formFichaUtilizador");

    if (!formFicha) return;

    function atualizarResumoUtilizador() {
        // Atualiza os textos ocultos de resumo para manter o padrÃ£o das outras fichas.
        // TambÃ©m prepara badges caso sejam usados visualmente no futuro.
        const codigo = document.getElementById("codigoUtilizador")?.value || "";
        const nome = document.getElementById("nomeUtilizador")?.value || "Utilizador";
        const tipo = document.getElementById("tipoUtilizador")?.value || "Tipo";
        const servico = document.getElementById("departamentoUtilizador")?.value || "serviÃ§o por definir";
        const estado = document.getElementById("estadoUtilizador")?.value || "Estado";

        definirTexto("tituloPaginaUtilizador", `Ficha do Utilizador - ${codigo}`);
        definirTexto("resumoNomeUtilizador", nome);
        definirTexto("resumoDescricaoUtilizador", `${codigo} | ${tipo} | ${servico}`);
        definirTexto("badgeEstadoUtilizador", estado);
        definirTexto("badgeTipoUtilizador", tipo);

        const badgeEstado = document.getElementById("badgeEstadoUtilizador");
        if (badgeEstado) {
            badgeEstado.className = `estado ${classeEstado(estado)}`;
        }
    }


    formFicha.addEventListener("input", atualizarResumoUtilizador);
    formFicha.addEventListener("change", atualizarResumoUtilizador);

    formFicha.addEventListener("submit", function (event) {
        const password = document.getElementById("passwordUtilizador")?.value || "";
        const confirmarPassword = document.getElementById("confirmarPasswordUtilizador")?.value || "";

        if ((password || confirmarPassword) && password !== confirmarPassword) {
            event.preventDefault();
            alert("A password e a confirmação da password não coincidem.");
            return;
        }

    });

    atualizarResumoUtilizador();

});

if (typeof carregarProcessosFinalizados === "function") {
    document.addEventListener("DOMContentLoaded", carregarProcessosFinalizados);
}

/* =========================================================
   PÃGINA DE CONSUMÃVEIS
   Gere visualmente entradas e saÃ­das de stock por equipamento.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const tabelaConsumiveis = document.getElementById("tabelaConsumiveisEquipamento");
    const corpoConsumiveis = document.getElementById("corpoConsumiveisEquipamento");
    const formNovoConsumivel = document.getElementById("formNovoConsumivelEquipamento");
    const modalNovoConsumivel = document.getElementById("modalNovoConsumivel");

    if (!tabelaConsumiveis || !corpoConsumiveis) return;

    function separarQuantidade(texto) {
        const resultado = String(texto || "").trim().match(/^(\d+)\s*(.*)$/);

        return {
            numero: resultado ? Number(resultado[1]) : 0,
            unidade: resultado && resultado[2] ? resultado[2].trim() : "unidades"
        };
    }

    function textoQuantidade(numero, unidade) {
        return `${Math.max(0, Number(numero) || 0)} ${unidade || "unidades"}`;
    }

    function definirEstadoStock(linha) {
        const quantidade = separarQuantidade(linha.cells[4]?.textContent);
        const minimo = separarQuantidade(linha.cells[5]?.textContent);
        const celulaEstado = linha.cells[6];

        if (!celulaEstado) return;

        if (quantidade.numero <= 0) {
            celulaEstado.innerHTML = `<span class="estado estado-inativo">Esgotado</span>`;
            return;
        }

        if (quantidade.numero <= minimo.numero) {
            celulaEstado.innerHTML = `<span class="estado estado-manutencao">Stock baixo</span>`;
            return;
        }

        celulaEstado.innerHTML = `<span class="estado estado-ativo">DisponÃ­vel</span>`;
    }

    function criarAcoesConsumivel() {
        return `
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-editar btn-aumentar-stock" title="Adicionar uma unidade ao stock">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button type="button" class="btn btn-sm btn-eliminar btn-remover-stock" title="Remover uma unidade do stock">
                    <i class="fa-solid fa-minus"></i>
                </button>
            </td>
        `;
    }

    function prepararLinhaConsumivel(linha) {
        if (linha.classList.contains("linha-sem-resultados")) return;

        if (linha.cells.length === 7) {
            linha.insertAdjacentHTML("beforeend", criarAcoesConsumivel());
        }

        definirEstadoStock(linha);
    }

    function alterarStock(linha, diferenca) {
        const quantidade = separarQuantidade(linha.cells[4]?.textContent);
        const novoValor = Math.max(0, quantidade.numero + diferenca);

        linha.cells[4].textContent = textoQuantidade(novoValor, quantidade.unidade);
        definirEstadoStock(linha);
    }

    function criarLinhaNovoConsumivel(dados) {
        const linha = document.createElement("tr");
        linha.innerHTML = `
            <td>${escaparTextoPedido(dados.codigo)}</td>
            <td>${escaparTextoPedido(dados.item)}</td>
            <td>${escaparTextoPedido(dados.equipamento)}</td>
            <td>${escaparTextoPedido(dados.categoria)}</td>
            <td>${textoQuantidade(dados.quantidade, dados.unidade)}</td>
            <td>${textoQuantidade(dados.stockMinimo, dados.unidade)}</td>
            <td><span class="estado estado-ativo">DisponÃ­vel</span></td>
            ${criarAcoesConsumivel()}
        `;

        definirEstadoStock(linha);
        return linha;
    }

    Array.from(corpoConsumiveis.rows).forEach(prepararLinhaConsumivel);

    tabelaConsumiveis.addEventListener("click", function (event) {
        const linha = event.target.closest("tr");
        if (!linha || linha.classList.contains("linha-sem-resultados")) return;

        if (event.target.closest(".btn-aumentar-stock")) {
            alterarStock(linha, 1);
        }

        if (event.target.closest(".btn-remover-stock")) {
            alterarStock(linha, -1);
        }
    });

    if (formNovoConsumivel) {
        formNovoConsumivel.addEventListener("submit", function (event) {
            event.preventDefault();

            const dados = {
                codigo: document.getElementById("novoConsumivelCodigo")?.value.trim(),
                item: document.getElementById("novoConsumivelItem")?.value.trim(),
                equipamento: document.getElementById("novoConsumivelEquipamento")?.value,
                categoria: document.getElementById("novoConsumivelCategoria")?.value,
                quantidade: Number(document.getElementById("novoConsumivelQuantidade")?.value || 0),
                unidade: document.getElementById("novoConsumivelUnidade")?.value.trim() || "unidades",
                stockMinimo: Number(document.getElementById("novoConsumivelStockMinimo")?.value || 0),
                observacoes: document.getElementById("novoConsumivelObservacoes")?.value.trim()
            };

            if (!dados.codigo || !dados.item || !dados.equipamento || !dados.categoria) {
                alert("Preencha o cÃ³digo, consumÃ­vel, equipamento e categoria.");
                return;
            }

            corpoConsumiveis.appendChild(criarLinhaNovoConsumivel(dados));

            const modalBootstrap = bootstrap.Modal.getInstance(modalNovoConsumivel);
            if (modalBootstrap) modalBootstrap.hide();

            formNovoConsumivel.reset();
            definirValor("novoConsumivelQuantidade", "1");
            definirValor("novoConsumivelUnidade", "unidades");
            definirValor("novoConsumivelStockMinimo", "1");
        });
    }

});

/* =========================================================
   PESQUISA E FILTROS DAS TABELAS
   Aplica pesquisa livre e filtros por coluna nas listas principais.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    // Normaliza o texto para tornar a pesquisa indiferente a maiÃºsculas e acentos.
    function normalizarTextoFiltro(texto) {
        return String(texto || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    // Devolve o texto de uma cÃ©lula da linha da tabela.
    function textoCelula(linha, indiceColuna) {
        const celula = linha.cells[indiceColuna];
        return celula ? normalizarTextoFiltro(celula.textContent) : "";
    }

    // Cria ou reaproveita uma linha informativa quando não existem resultados visÃ­veis.
    function obterLinhaSemResultados(tabela) {
        const corpo = tabela.tBodies[0];
        let linha = corpo.querySelector(".linha-sem-resultados");

        if (!linha) {
            linha = document.createElement("tr");
            linha.className = "linha-sem-resultados d-none";
            linha.innerHTML = `<td colspan="${tabela.tHead.rows[0].cells.length}" class="text-center">não foram encontrados resultados para os filtros aplicados.</td>`;
            corpo.appendChild(linha);
        }

        return linha;
    }

    // Aplica todos os campos de pesquisa e filtros associados ao bloco atual.
    function aplicarFiltros(blocoFiltros) {
        const tabela = document.querySelector(blocoFiltros.dataset.tabela);
        if (!tabela || !tabela.tBodies.length) return;

        const pesquisa = normalizarTextoFiltro(blocoFiltros.querySelector("[data-filtro='texto']")?.value || "");
        const filtrosColuna = Array.from(blocoFiltros.querySelectorAll("[data-filtro='coluna']"));
        const linhaSemResultados = obterLinhaSemResultados(tabela);
        let totalVisivel = 0;

        Array.from(tabela.tBodies[0].rows).forEach(function (linha) {
            if (linha.classList.contains("linha-sem-resultados")) return;

            const textoLinha = normalizarTextoFiltro(linha.textContent);
            const correspondePesquisa = !pesquisa || textoLinha.includes(pesquisa);

            const correspondeFiltros = filtrosColuna.every(function (campo) {
                const valorFiltro = normalizarTextoFiltro(campo.value);
                if (!valorFiltro) return true;

                return textoCelula(linha, Number(campo.dataset.coluna)).includes(valorFiltro);
            });

            const visivel = correspondePesquisa && correspondeFiltros;
            linha.classList.toggle("d-none", !visivel);

            if (visivel) totalVisivel += 1;
        });

        linhaSemResultados.classList.toggle("d-none", totalVisivel > 0);
    }

    document.querySelectorAll(".filtros-tabela").forEach(function (blocoFiltros) {
        const campos = blocoFiltros.querySelectorAll("[data-filtro]");
        const botaoLimpar = blocoFiltros.querySelector("[data-limpar-filtros]");

        campos.forEach(function (campo) {
            campo.addEventListener("input", function () {
                aplicarFiltros(blocoFiltros);
            });

            campo.addEventListener("change", function () {
                aplicarFiltros(blocoFiltros);
            });
        });

        if (botaoLimpar) {
            botaoLimpar.addEventListener("click", function () {
                campos.forEach(function (campo) {
                    campo.value = "";
                });

                aplicarFiltros(blocoFiltros);
            });
        }

        aplicarFiltros(blocoFiltros);
    });

});

/* =========================================================
   PÃGINA DE ACESSÃ“RIOS
   Pesquisa equipamento, lista acessÃ³rios e permite adicionar, editar ou apagar.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const seletorEquipamento = document.getElementById("seletorEquipamentoAcessorios");
    const tabelaAcessorios = document.getElementById("tabelaGestaoAcessorios");
    const pesquisaAcessorios = document.getElementById("pesquisaAcessoriosEquipamento");
    const btnLimparPesquisa = document.getElementById("btnLimparPesquisaAcessorios");
    const btnAdicionar = document.getElementById("btnAbrirModalNovoAcessorio");
    const modalAcessorio = document.getElementById("modalAcessorio");
    const modalEliminarAcessorio = document.getElementById("modalEliminarAcessorio");
    const btnGuardarAcessorio = document.getElementById("btnGuardarAcessorioModal");
    const btnConfirmarEliminarAcessorio = document.getElementById("btnConfirmarEliminarAcessorio");

    if (!seletorEquipamento || !tabelaAcessorios) return;

    const acessoriosPorEquipamento = {};

    function obterAcessoriosEditaveis(codigoEquipamento) {
        if (!acessoriosPorEquipamento[codigoEquipamento]) {
            const equipamento = equipamentosMEDICORE[codigoEquipamento];
            acessoriosPorEquipamento[codigoEquipamento] = JSON.parse(JSON.stringify(equipamento?.acessorios || []));
        }

        return acessoriosPorEquipamento[codigoEquipamento];
    }

    function textoNormalizado(texto) {
        return String(texto || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    function equipamentoAtual() {
        return equipamentosMEDICORE[seletorEquipamento.value];
    }

    function textoEquipamentoAtual() {
        const equipamento = equipamentoAtual();
        if (!equipamento) return seletorEquipamento.value;

        return `${seletorEquipamento.value} - ${equipamento.nome}`;
    }

    function valorDataParaInput(valor) {
        return /^\d{4}-\d{2}-\d{2}$/.test(valor || "") ? valor : "";
    }

    function definirTituloModalAcessorio(texto) {
        const titulo = document.getElementById("modalAcessorioLabel");
        if (!titulo) return;

        titulo.innerHTML = `<i class="fa-solid fa-plug-circle-bolt me-2"></i>${escaparTextoPedido(texto)}`;
    }

    function criarLinhaAcessorioGestao(acessorio, indice) {
        const linha = document.createElement("tr");
        linha.dataset.indice = indice;

        linha.innerHTML = `
            <td>${escaparTextoPedido(acessorio.codigo || "---")}</td>
            <td>${escaparTextoPedido(acessorio.nome || "---")}</td>
            <td>${escaparTextoPedido(acessorio.tipo || "---")}</td>
            <td>${escaparTextoPedido(acessorio.serie || "---")}</td>
            <td><span class="estado ${classeEstado(acessorio.estado || "Ativo")}">${escaparTextoPedido(acessorio.estado || "Ativo")}</span></td>
            <td>${escaparTextoPedido(acessorio.intervencao || "não")}</td>
            <td>${formatarDataPT(acessorio.proximaIntervencao || "")}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-editar btn-editar-acessorio" title="Observar/editar acessÃ³rio">
                    <i class="fa-solid fa-file-pen"></i>
                </button>
                <button type="button" class="btn btn-sm btn-eliminar btn-apagar-acessorio" title="Apagar acessÃ³rio">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;

        return linha;
    }

    function renderizarAcessorios() {
        const acessorios = obterAcessoriosEditaveis(seletorEquipamento.value);
        const pesquisa = textoNormalizado(pesquisaAcessorios?.value || "");
        tabelaAcessorios.innerHTML = "";

        const acessoriosFiltrados = acessorios.filter(function (acessorio) {
            if (!pesquisa) return true;

            return textoNormalizado(Object.values(acessorio).join(" ")).includes(pesquisa);
        });

        if (!acessoriosFiltrados.length) {
            tabelaAcessorios.innerHTML = `<tr><td colspan="8" class="text-center text-muted">Sem acessÃ³rios encontrados para este equipamento.</td></tr>`;
            return;
        }

        acessoriosFiltrados.forEach(function (acessorio) {
            const indiceOriginal = acessorios.indexOf(acessorio);
            tabelaAcessorios.appendChild(criarLinhaAcessorioGestao(acessorio, indiceOriginal));
        });
    }

    function prepararModalNovoAcessorio() {
        definirTituloModalAcessorio("Adicionar AcessÃ³rio");
        definirValor("modalAcessorioIndice", "");
        definirValor("modalAcessorioEquipamento", textoEquipamentoAtual());
        definirValor("modalAcessorioCodigo", "");
        definirValor("modalAcessorioNome", "");
        definirValor("modalAcessorioTipo", "");
        definirValor("modalAcessorioFabricante", "");
        definirValor("modalAcessorioModelo", "");
        definirValor("modalAcessorioSerie", "");
        definirValor("modalAcessorioEstado", "Ativo");
        definirValor("modalAcessorioVerificacao", "não");
        definirValor("modalAcessorioProximaIntervencao", "");
        definirValor("modalAcessorioObservacoes", "");
    }

    function preencherModalEditarAcessorio(acessorio, indice) {
        definirTituloModalAcessorio("Detalhes do AcessÃ³rio");
        definirValor("modalAcessorioIndice", String(indice));
        definirValor("modalAcessorioEquipamento", acessorio.equipamentoPrincipal || textoEquipamentoAtual());
        definirValor("modalAcessorioCodigo", acessorio.codigo || "");
        definirValor("modalAcessorioNome", acessorio.nome || "");
        definirValor("modalAcessorioTipo", acessorio.tipo || "");
        definirValor("modalAcessorioFabricante", acessorio.fabricante || "");
        definirValor("modalAcessorioModelo", acessorio.modelo || "");
        definirValor("modalAcessorioSerie", acessorio.serie || "");
        definirValor("modalAcessorioEstado", acessorio.estado || "Ativo");
        definirValor("modalAcessorioVerificacao", acessorio.intervencao || "não");
        definirValor("modalAcessorioProximaIntervencao", valorDataParaInput(acessorio.proximaIntervencao));
        definirValor("modalAcessorioObservacoes", acessorio.observacoes || "");
    }

    function obterDadosModalAcessorio() {
        return {
            codigo: document.getElementById("modalAcessorioCodigo")?.value.trim() || "",
            nome: document.getElementById("modalAcessorioNome")?.value.trim() || "",
            tipo: document.getElementById("modalAcessorioTipo")?.value || "Outro",
            fabricante: document.getElementById("modalAcessorioFabricante")?.value.trim() || "---",
            modelo: document.getElementById("modalAcessorioModelo")?.value.trim() || "---",
            serie: document.getElementById("modalAcessorioSerie")?.value.trim() || "---",
            estado: document.getElementById("modalAcessorioEstado")?.value || "Ativo",
            intervencao: document.getElementById("modalAcessorioVerificacao")?.value || "não",
            proximaIntervencao: document.getElementById("modalAcessorioProximaIntervencao")?.value || "Por definir",
            observacoes: document.getElementById("modalAcessorioObservacoes")?.value.trim() || "",
            equipamentoCodigo: seletorEquipamento.value,
            equipamentoPrincipal: textoEquipamentoAtual(),
            localizacao: equipamentoAtual()?.localizacao || "---"
        };
    }

    function guardarAcessorioModal() {
        const dados = obterDadosModalAcessorio();
        const indice = document.getElementById("modalAcessorioIndice")?.value;
        const acessorios = obterAcessoriosEditaveis(seletorEquipamento.value);

        if (!dados.codigo || !dados.nome) {
            alert("Indique pelo menos o cÃ³digo e o nome do acessÃ³rio.");
            return;
        }

        if (indice === "") {
            acessorios.push(dados);
        } else {
            acessorios[Number(indice)] = dados;
        }

        const modalBootstrap = bootstrap.Modal.getInstance(modalAcessorio);
        if (modalBootstrap) modalBootstrap.hide();

        renderizarAcessorios();
    }

    function preencherModalEliminarAcessorio(acessorio, indice) {
        definirValor("modalEliminarAcessorioIndice", String(indice));
        definirTexto("modalEliminarAcessorioCodigo", acessorio.codigo || "---");
        definirTexto("modalEliminarAcessorioNome", acessorio.nome || "---");
        definirTexto("modalEliminarAcessorioEquipamento", acessorio.equipamentoPrincipal || textoEquipamentoAtual());
        definirTexto("modalEliminarAcessorioTipo", acessorio.tipo || "---");
        definirTexto("modalEliminarAcessorioSerie", acessorio.serie || "---");
        definirTexto("modalEliminarAcessorioEstado", acessorio.estado || "Ativo");
    }

    tabelaAcessorios.addEventListener("click", function (event) {
        const linha = event.target.closest("tr[data-indice]");
        if (!linha) return;

        const indice = Number(linha.dataset.indice);
        const acessorios = obterAcessoriosEditaveis(seletorEquipamento.value);
        const acessorio = acessorios[indice];

        if (event.target.closest(".btn-apagar-acessorio")) {
            preencherModalEliminarAcessorio(acessorio, indice);
            new bootstrap.Modal(modalEliminarAcessorio).show();
            return;
        }

        if (event.target.closest(".btn-editar-acessorio")) {
            preencherModalEditarAcessorio(acessorio, indice);
            new bootstrap.Modal(modalAcessorio).show();
        }
    });

    seletorEquipamento.addEventListener("change", function () {
        renderizarAcessorios();
    });

    if (pesquisaAcessorios) {
        pesquisaAcessorios.addEventListener("input", renderizarAcessorios);
    }

    if (btnLimparPesquisa) {
        btnLimparPesquisa.addEventListener("click", function () {
            if (pesquisaAcessorios) pesquisaAcessorios.value = "";
            renderizarAcessorios();
        });
    }

    if (btnAdicionar) {
        btnAdicionar.addEventListener("click", prepararModalNovoAcessorio);
    }

    if (btnGuardarAcessorio) {
        btnGuardarAcessorio.addEventListener("click", guardarAcessorioModal);
    }

    if (btnConfirmarEliminarAcessorio) {
        btnConfirmarEliminarAcessorio.addEventListener("click", function () {
            const indice = Number(document.getElementById("modalEliminarAcessorioIndice")?.value);
            const acessorios = obterAcessoriosEditaveis(seletorEquipamento.value);

            if (!Number.isNaN(indice)) {
                acessorios.splice(indice, 1);
            }

            const modalBootstrap = bootstrap.Modal.getInstance(modalEliminarAcessorio);
            if (modalBootstrap) modalBootstrap.hide();

            renderizarAcessorios();
        });
    }

    renderizarAcessorios();

});

/* =========================================================
   SUBMENUS NO MENU COLAPSADO
   Em ecrÃ£s menores, abre e fecha cada submenu com clique no menu pai.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const itensComSubmenu = document.querySelectorAll(
        ".menu-dropdown-hover, .menu-dropdown-hover-calibracoes, .menu-dropdown-hover-localizacoes, .menu-dropdown-hover-fornecedores, .menu-dropdown-hover-utilizadores"
    );

    itensComSubmenu.forEach(function (item) {
        const linkPrincipal = item.querySelector(":scope > .nav-link");

        if (!linkPrincipal) return;

        linkPrincipal.addEventListener("click", function (event) {
            if (!window.matchMedia("(max-width: 991px)").matches) return;

            event.preventDefault();

            itensComSubmenu.forEach(function (outroItem) {
                if (outroItem !== item) {
                    outroItem.classList.remove("submenu-aberto");
                }
            });

            item.classList.toggle("submenu-aberto");
        });
    });

});

/* =========================================================
   BACKOFFICE DA PÃGINA PÃšBLICA
   PrÃ©-visualizaÃ§Ã£o simples e simulaÃ§Ã£o de guardar conteÃºdos.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    // Inicializa apenas a Página private/views/backoffice/backoffice.html.
    const formBackoffice = document.getElementById("formBackofficePublico");
    const btnPreVisualizar = document.getElementById("btnPreVisualizarIndex");

    if (!formBackoffice) return;

    function atualizarPreviewPublico() {
        // Atualiza a prÃ©-visualizaÃ§Ã£o rÃ¡pida com os campos principais.
        definirTexto("previewTituloHero", document.getElementById("tituloHeroPublico")?.value || "TÃ­tulo por definir");
        definirTexto("previewTextoHero", document.getElementById("textoHeroPublico")?.value || "Texto por definir.");
        definirTexto("previewEmailRodape", document.getElementById("emailRodape")?.value || "email por definir");
        definirTexto("previewTelefoneRodape", document.getElementById("telefoneRodape")?.value || "telefone por definir");
    }

    function obterDadosBackoffice() {
        // Converte o formulário num objeto simples para simular persistÃªncia.
        // No backend real, estes dados devem ir para MySQL ou para um ficheiro de configuraÃ§Ã£o.
        const dados = {};
        const formData = new FormData(formBackoffice);

        formData.forEach(function (valor, chave) {
            if (valor instanceof File) {
                dados[chave] = valor.name || "";
                return;
            }

            dados[chave] = valor;
        });

        return dados;
    }

    formBackoffice.addEventListener("input", atualizarPreviewPublico);
    formBackoffice.addEventListener("change", atualizarPreviewPublico);

    if (btnPreVisualizar) {
        btnPreVisualizar.addEventListener("click", function () {
            // Abre a Página pÃºblica atual. Quando o backend existir, esta prÃ©-visualizaÃ§Ã£o pode usar dados temporÃ¡rios.
            window.open("../../../public/index.php", "_blank");
        });
    }

    formBackoffice.addEventListener("submit", function (event) {
        event.preventDefault();

        localStorage.setItem("medicoreConteudoPublico", JSON.stringify(obterDadosBackoffice()));

        mostrarPopupPedido(
            "ConteÃºdos guardados",
            "As alteraÃ§Ãµes da Página pÃºblica foram guardadas no backoffice."
        );
    });

    atualizarPreviewPublico();

});

document.addEventListener("DOMContentLoaded", function () {
    if (typeof jQuery === "undefined" || !jQuery.fn.DataTable) {
        return;
    }

    const tabelasMedicore = [
        { id: "tabela-fornecedores", entidade: "fornecedores", vazio: "não existem fornecedores registados." },
        { id: "tabela-equipamentos", entidade: "equipamentos", vazio: "não existem equipamentos registados." },
        { id: "tabela-localizacoes", entidade: "localizações", vazio: "não existem localizações registadas." },
        { id: "tabela-utilizadores", entidade: "utilizadores", vazio: "não existem utilizadores registados." },
        { id: "tabela-manutencoes-abertas", entidade: "processos", vazio: "não existem processos de manutenção abertos." },
        { id: "tabela-calibracoes-abertas", entidade: "processos", vazio: "não existem processos de calibração abertos." },
        { id: "tabela-processos-finalizados", entidade: "processos", vazio: "não existem processos finalizados." },
        { id: "tabelaAcessoriosBD", entidade: "acessórios", vazio: "não existem acessórios registados para este equipamento." }
    ];

    const idiomaBaseDataTables = {
        decimal: "",
        thousands: ",",
        loadingRecords: "A carregar...",
        processing: "A processar...",
        search: "Pesquisar:",
        paginate: {
            first: "Primeira",
            last: "Última",
            next: "Seguinte",
            previous: "Anterior"
        },
        aria: {
            sortAscending: ": ativar para ordenar a coluna de forma crescente.",
            sortDescending: ": ativar para ordenar a coluna de forma decrescente."
        }
    };

    tabelasMedicore.forEach(function (config) {
        const tabela = document.getElementById(config.id);
        if (!tabela) return;

        const ultimaColuna = tabela.tHead?.rows[0]?.cells.length
            ? tabela.tHead.rows[0].cells.length - 1
            : -1;

        jQuery("#" + config.id).DataTable({
            pageLength: 5,
            pagingType: "full_numbers",
            autoWidth: false,
            columnDefs: ultimaColuna >= 0
                ? [{
                    targets: ultimaColuna,
                    orderable: false,
                    searchable: false
                }]
                : [],
            language: {
                ...idiomaBaseDataTables,
                emptyTable: config.vazio,
                info: "Mostrando _START_ até _END_ de _TOTAL_ " + config.entidade,
                infoEmpty: "Mostrando 0 até 0 de 0 " + config.entidade,
                infoFiltered: "(filtrado de _MAX_ " + config.entidade + " no total)",
                lengthMenu: "Mostrar _MENU_ " + config.entidade + " por Página",
                zeroRecords: "Nenhum resultado encontrado."
            }
        });
    });

    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (tab) {
        tab.addEventListener("shown.bs.tab", function () {
            jQuery.fn.dataTable
                .tables({ visible: true, api: true })
                .columns.adjust();
        });
    });
});

/* =========================================================
   MODAL EDITAR FAMÃLIA DE EQUIPAMENTOS
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    const botoesEditarFamilia = document.querySelectorAll(".btn-editar-familia");

    const campoId = document.getElementById("modalIdFamilia");
    const campoCodigo = document.getElementById("modalCodigoFamilia");
    const campoNome = document.getElementById("modalNomeFamilia");
    const campoDescricao = document.getElementById("modalDescricaoFamilia");

    if (!botoesEditarFamilia.length || !campoId || !campoCodigo || !campoNome || !campoDescricao) {
        return;
    }

    botoesEditarFamilia.forEach(function (botao) {
        botao.addEventListener("click", function () {
            campoId.value = botao.dataset.id || "";
            campoCodigo.value = botao.dataset.codigo || "";
            campoNome.value = botao.dataset.nome || "";
            campoDescricao.value = botao.dataset.descricao || "";
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const campoGarantia = document.getElementById("cobertaPorGarantia");
    const campoCusto = document.getElementById("custoManutencao");

    if (!campoGarantia || !campoCusto) return;

    function atualizarCustoManutencao() {
        if (campoGarantia.value === "1") {
            campoCusto.value = "0.00";
            campoCusto.readOnly = true;
            campoCusto.classList.add("campo-bloqueado");
        } else {
            campoCusto.readOnly = false;
            campoCusto.classList.remove("campo-bloqueado");

            if (campoCusto.value === "0.00") {
                campoCusto.value = "";
            }
        }
    }

    campoGarantia.addEventListener("change", atualizarCustoManutencao);
    atualizarCustoManutencao();
});


document.addEventListener('DOMContentLoaded', function () {
    const $ = function (id) {
        return document.getElementById(id);
    };

    const seletorEquipamento = $('seletorEquipamentoAcessoriosBD');
    const pesquisa = $('pesquisaAcessoriosBD');
    const btnLimpar = $('btnLimparPesquisaAcessoriosBD');
    const tabela = $('tabelaAcessoriosBD');

    const modalAcessorio = $('modalAcessorioBD');
    const form = $('formAcessorioBD');
    const tituloModal = $('modalAcessorioBDLabel');

    const periodicidadeManutencao = $('periodicidadeManutencaoBD');
    const periodicidadeCalibracao = $('periodicidadeCalibracaoBD');

    function setValue(id, valor) {
        const campo = $(id);
        if (campo) {
            campo.value = valor ?? '';
        }
    }

    function setText(id, valor) {
        const campo = $(id);
        if (campo) {
            campo.textContent = valor || '---';
        }
    }

    function setChecked(id, ativo) {
        const campo = $(id);
        if (campo) {
            campo.checked = Boolean(ativo);
        }
    }

    function normalizarTexto(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function filtrarTabela() {
        if (!tabela || !pesquisa) return;

        const termo = normalizarTexto(pesquisa.value);
        const linhas = tabela.querySelectorAll('tbody tr');

        linhas.forEach(function (linha) {
            if (linha.classList.contains('linha-sem-acessorios')) return;
            linha.classList.toggle('d-none', Boolean(termo) && !normalizarTexto(linha.textContent).includes(termo));
        });
    }

    function atualizarPeriodicidades() {
        const manutencaoSim = $('requerManutencaoSimBD')?.checked === true;
        const calibracaoSim = $('requerCalibracaoSimBD')?.checked === true;

        if (periodicidadeManutencao) {
            periodicidadeManutencao.disabled = !manutencaoSim;
            if (!manutencaoSim) periodicidadeManutencao.value = '';
        }

        if (periodicidadeCalibracao) {
            periodicidadeCalibracao.disabled = !calibracaoSim;
            if (!calibracaoSim) periodicidadeCalibracao.value = '';
        }
    }

    function prepararModalCriacao(botao) {
        if (form) form.reset();

        setValue('acaoAcessorioBD', 'criar');
        setValue('idAcessorioBD', '');
        setValue('codigoAcessorioBD', botao?.dataset.codigoPreview || 'Gerado automaticamente');

        if (tituloModal) {
            tituloModal.innerHTML = '<i class="fa-solid fa-plug-circle-bolt me-2"></i>Adicionar AcessÃ³rio';
        }

        setChecked('requerManutencaoNaoBD', true);
        setChecked('requerManutencaoSimBD', false);
        setChecked('requerCalibracaoNaoBD', true);
        setChecked('requerCalibracaoSimBD', false);
        atualizarPeriodicidades();
    }

    function prepararModalEdicao(botao) {
        if (form) form.reset();
        if (!botao) return;

        setValue('acaoAcessorioBD', 'editar');
        setValue('idAcessorioBD', botao.dataset.idAcessorio || '');
        setValue('codigoAcessorioBD', botao.dataset.codigo || '---');
        setValue('designacaoAcessorioBD', botao.dataset.designacao || '');
        setValue('tipoAcessorioBD', botao.dataset.tipo || '');
        setValue('fabricanteAcessorioBD', botao.dataset.fabricante || '');
        setValue('modeloAcessorioBD', botao.dataset.modelo || '');
        setValue('numeroSerieAcessorioBD', botao.dataset.numeroSerie || '');
        setValue('estadoAcessorioBD', botao.dataset.estado || 'ativo');
        setValue('idFornecedorGarantiaBD', botao.dataset.idFornecedorGarantia || '');
        setValue('dataInicioGarantiaBD', botao.dataset.dataInicioGarantia || '');
        setValue('dataFimGarantiaBD', botao.dataset.dataFimGarantia || '');
        setValue('observacoesAcessorioBD', botao.dataset.observacoes || '');

        const requerManutencao = botao.dataset.requerManutencao === '1';
        const requerCalibracao = botao.dataset.requerCalibracao === '1';

        setChecked('requerManutencaoSimBD', requerManutencao);
        setChecked('requerManutencaoNaoBD', !requerManutencao);
        setChecked('requerCalibracaoSimBD', requerCalibracao);
        setChecked('requerCalibracaoNaoBD', !requerCalibracao);

        atualizarPeriodicidades();

        if (periodicidadeManutencao && requerManutencao) {
            periodicidadeManutencao.value = botao.dataset.periodicidadeManutencao || '';
        }

        if (periodicidadeCalibracao && requerCalibracao) {
            periodicidadeCalibracao.value = botao.dataset.periodicidadeCalibracao || '';
        }

        if (tituloModal) {
            tituloModal.innerHTML = '<i class="fa-solid fa-file-pen me-2"></i>Editar AcessÃ³rio';
        }
    }

    if (modalAcessorio) {
        modalAcessorio.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;

            if (botao && botao.classList.contains('btn-editar-acessorio-bd')) {
                prepararModalEdicao(botao);
            } else {
                prepararModalCriacao(botao);
            }
        });
    }

    if (seletorEquipamento) {
        seletorEquipamento.addEventListener('change', function () {
            if (this.value) {
                window.location.href = 'acessorios.php?id_equipamento=' + encodeURIComponent(this.value);
            }
        });
    }

    if (pesquisa) {
        pesquisa.addEventListener('input', filtrarTabela);
    }

    if (btnLimpar) {
        btnLimpar.addEventListener('click', function () {
            if (pesquisa) pesquisa.value = '';
            filtrarTabela();
        });
    }

    document.querySelectorAll('input[name="requerManutencao"]').forEach(function (campo) {
        campo.addEventListener('change', atualizarPeriodicidades);
    });

    document.querySelectorAll('input[name="requerCalibracao"]').forEach(function (campo) {
        campo.addEventListener('change', atualizarPeriodicidades);
    });

    const modalEliminar = $('modalEliminarAcessorioBD');

    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            if (!botao) return;

            setValue('idAcessorioEliminarBD', botao.dataset.idAcessorio || '');
            setText('modalEliminarAcessorioCodigoBD', botao.dataset.codigo || '---');
            setText('modalEliminarAcessorioNomeBD', botao.dataset.designacao || '---');
            setText('modalEliminarAcessorioTipoBD', botao.dataset.tipo || '---');
            setText('modalEliminarAcessorioSerieBD', botao.dataset.serie || '---');
            setText('modalEliminarAcessorioEstadoBD', botao.dataset.estado || '---');
        });
    }

    atualizarPeriodicidades();
});

/* =========================================================
   FICHAS EDITÁVEIS COM CONFIRMAÇÃO AO SAIR
   Deteta alterações por formulário e mostra modal ao voltar.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    const formularios = Array.from(document.querySelectorAll(
        "#formFichaEquipamento, #formFichaFornecedor, #formFichaLocalizacao, #formFichaUtilizador"
    ));

    formularios.forEach(function (formulario) {
        formulario.dataset.alterado = "0";

        formulario.querySelectorAll(".campo-editavel").forEach(function (campo) {
            if (
                campo.tagName === "SELECT" ||
                campo.type === "radio" ||
                campo.type === "checkbox" ||
                campo.type === "file"
            ) {
                campo.disabled = false;
            } else {
                campo.readOnly = false;
            }
        });

        formulario.querySelectorAll(".campo-bloqueado").forEach(function (campo) {
            campo.readOnly = true;
            campo.disabled = false;
        });

        formulario.querySelectorAll('input[type="hidden"][id^="modoFormulario"], input[type="hidden"][name^="modoFormulario"]').forEach(function (campo) {
            campo.value = "editar";
        });

        formulario.querySelectorAll("input, select, textarea").forEach(function (campo) {
            campo.addEventListener("input", function () {
                formulario.dataset.alterado = "1";
            });

            campo.addEventListener("change", function () {
                formulario.dataset.alterado = "1";
            });
        });

        formulario.addEventListener("submit", function () {
            formulario.dataset.alterado = "0";
        });
    });

    document.querySelectorAll(".btn-voltar-lista-com-confirmacao").forEach(function (botao) {
        botao.addEventListener("click", function (event) {
            const formulario = formularios.find(function (form) {
                return document.body.contains(form);
            });

            if (!formulario || formulario.dataset.alterado !== "1") {
                return;
            }

            event.preventDefault();

            const destino = botao.getAttribute("href");
            const botaoConfirmar = document.getElementById("btnConfirmarSairSemGuardar");
            const modalElemento = document.getElementById("modalSairSemGuardar");

            if (!botaoConfirmar || !modalElemento || typeof bootstrap === "undefined") {
                window.location.href = destino;
                return;
            }

            botaoConfirmar.setAttribute("href", destino);
            bootstrap.Modal.getOrCreateInstance(modalElemento).show();
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".pesquisa-select").forEach(function (input) {
        const select = document.getElementById(input.dataset.targetSelect);

        if (!select) return;

        const opcoesOriginais = Array.from(select.options).map(function (opcao) {
            return {
                value: opcao.value,
                text: opcao.textContent,
                search: (opcao.dataset.search || opcao.textContent).toLowerCase(),
                selected: opcao.selected
            };
        });

        input.addEventListener("input", function () {
            const termo = input.value.trim().toLowerCase();
            const valorAtual = select.value;

            select.innerHTML = "";

            opcoesOriginais.forEach(function (opcao, index) {
                if (index !== 0 && termo && !opcao.search.includes(termo)) return;

                const novaOpcao = document.createElement("option");
                novaOpcao.value = opcao.value;
                novaOpcao.textContent = opcao.text;

                if (opcao.value === valorAtual) {
                    novaOpcao.selected = true;
                }

                select.appendChild(novaOpcao);
            });
        });
    });
});


document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".pesquisa-registo-custom").forEach(function (input) {
        const hidden = document.getElementById(input.dataset.hiddenTarget);
        const lista = document.getElementById(input.dataset.listaTarget);

        if (!hidden || !lista) return;

        const opcoes = Array.from(lista.querySelectorAll(".opcao-registo-custom"));

        input.addEventListener("input", function () {
            const termo = input.value.toLowerCase().trim();
            lista.classList.add("ativo");
            hidden.value = "";

            opcoes.forEach(function (opcao) {
                const texto = (opcao.dataset.texto || "").toLowerCase();
                opcao.style.display = texto.includes(termo) ? "" : "none";
            });
        });

        input.addEventListener("focus", function () {
            lista.classList.add("ativo");
        });

        opcoes.forEach(function (opcao) {
            opcao.addEventListener("click", function () {
                input.value = opcao.dataset.texto;
                hidden.value = opcao.dataset.id;
                lista.classList.remove("ativo");
                
                const formulario = input.closest("form");
                if (formulario) {
                    formulario.dataset.alterado = "1";
                }

                const listaFiltradaId = input.dataset.filtraLista;
                const campoFiltro = input.dataset.filtraCampo;

                if (listaFiltradaId && campoFiltro) {
                    const pesquisaFiltrada = document.querySelector(
                        `.pesquisa-checkbox-custom[data-lista-target="${listaFiltradaId}"]`
                    );

                    if (pesquisaFiltrada) {
                        pesquisaFiltrada.value = "";
                    }

                    document.querySelectorAll(`#${listaFiltradaId} .opcao-checkbox-custom`).forEach(function (item) {
                        const pertenceAoRegisto = item.dataset[campoFiltro] === opcao.dataset.id;

                        item.dataset.visivelFiltroPai = pertenceAoRegisto ? "1" : "0";
                        item.hidden = true;
                        item.style.display = "none";

                        const checkbox = item.querySelector('input[type="checkbox"]');
                        if (checkbox && !pertenceAoRegisto) {
                            checkbox.checked = false;
                        }
                    });
                }
            });
        });

        document.addEventListener("click", function (event) {
            if (!input.contains(event.target) && !lista.contains(event.target)) {
                lista.classList.remove("ativo");
            }
        });
    });

    document.querySelectorAll(".pesquisa-checkbox-custom").forEach(function (input) {
        const lista = document.getElementById(input.dataset.listaTarget);
        if (!lista) return;

        const opcoes = Array.from(lista.querySelectorAll(".opcao-checkbox-custom"));

        lista.classList.remove("ativo");

        opcoes.forEach(function (opcao) {
            opcao.hidden = true;
            opcao.style.display = "none";
        });

        input.addEventListener("input", function () {
            const termo = input.value.toLowerCase().trim();
            const deveMostrarLista = termo.length > 0;

            lista.classList.toggle("ativo", deveMostrarLista);

            opcoes.forEach(function (opcao) {
                const texto = (opcao.dataset.texto || "").toLowerCase();
                const visivelPeloFiltroPai = opcao.dataset.visivelFiltroPai !== "0";
                const mostrar = deveMostrarLista && texto.includes(termo) && visivelPeloFiltroPai;

                opcao.hidden = !mostrar;
                opcao.style.display = mostrar ? "" : "none";
            });
        });

        input.addEventListener("focus", function () {
            if (input.value.trim() !== "") {
                lista.classList.add("ativo");
            }
        });

        document.addEventListener("click", function (event) {
            if (!input.contains(event.target) && !lista.contains(event.target)) {
                lista.classList.remove("ativo");
            }
        });
    });
});

