<?php
/* =========================================================
   FUNCOES DE SESSAO
   Centraliza as funcoes usadas para iniciar sessoes,
   validar acessos privados e terminar sessao.
   ========================================================= */

/* Carrega as constantes globais da aplicacao, como BASE_URL. */
require_once __DIR__ . '/../../config/config.php';

/* Inicia a sessao apenas se ainda nao estiver iniciada. */
function start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/* Verifica se existe um utilizador autenticado na sessao. */
function check_session()
{
    start_session();

    return !empty($_SESSION['autenticado']) || !empty($_SESSION['utilizador']);
}

/* Redireciona para o login quando o utilizador nao tem sessao ativa. */
function redirect_if_not_logged($redirect_to = null)
{
    start_session();

    if ($redirect_to === null) {
        $redirect_to = BASE_URL . '/public/login.php';
    }

    if (!check_session()) {
        header("Location: $redirect_to");
        exit;
    }
}

/* Termina a sessao atual e redireciona o utilizador. */
function logout_and_redirect($redirect_to = null)
{
    start_session();

    if ($redirect_to === null) {
        $redirect_to = BASE_URL . '/public/login.php';
    }

    session_unset();
    session_destroy();

    header("Location: $redirect_to");
    exit;
}
?>
