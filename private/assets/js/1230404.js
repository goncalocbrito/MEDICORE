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