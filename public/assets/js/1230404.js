const loginForm = document.getElementById("loginForm");
const mensagemErro = document.getElementById("mensagemErro");

loginForm.addEventListener("submit", function (event) {
    event.preventDefault();

const utilizador = document.getElementById("email").value.trim();
const password = document.getElementById("password").value.trim();

if (
    (utilizador === "admin" && password === "1234") ||
    (utilizador === "engenheiro@medicore.pt" && password === "1234")
) {
    window.location.href = "../private/index.html";
} else {
    mensagemErro.style.display = "block";
}
});