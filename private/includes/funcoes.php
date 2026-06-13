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

function indice_etapa($etapa, $etapas)
{
    $indice = array_search($etapa, $etapas, true);
    return $indice === false ? 0 : $indice;
}

function proxima_etapa($etapa, $etapas)
{
    $indiceAtual = indice_etapa($etapa, $etapas);
    return $etapas[$indiceAtual + 1] ?? null;
}

function etapa_anterior($etapa, $etapas)
{
    $indiceAtual = indice_etapa($etapa, $etapas);
    return $etapas[$indiceAtual - 1] ?? $etapas[0];
}

function guardar_etapa_temporaria($chaveSessao, $etapa, $camposPorEtapa)
{
    if (!isset($_SESSION[$chaveSessao])) {
        $_SESSION[$chaveSessao] = [];
    }

    foreach ($camposPorEtapa[$etapa] ?? [] as $campo) {
        if (isset($_POST[$campo])) {
            $_SESSION[$chaveSessao][$campo] = trim($_POST[$campo]);
        }
    }
}

function limpar_etapa_temporaria($chaveSessao, $etapa, $camposPorEtapa)
{
    if (!isset($camposPorEtapa[$etapa])) {
        return;
    }

    foreach ($camposPorEtapa[$etapa] as $campo) {
        unset($_SESSION[$chaveSessao][$campo]);
    }
}

function valor_temporario($chaveSessao, $campo, $valorPadrao = '')
{
    return htmlspecialchars($_SESSION[$chaveSessao][$campo] ?? $valorPadrao);
}

function selected_temporario($chaveSessao, $campo, $valor)
{
    return ($_SESSION[$chaveSessao][$campo] ?? '') === $valor ? 'selected' : '';
}

function checked_temporario($chaveSessao, $campo, $valor)
{
    return ($_SESSION[$chaveSessao][$campo] ?? '') === $valor ? 'checked' : '';
}

function validar_etapa_temporaria($chaveSessao, $etapa, $camposObrigatorios, $labelsCampos)
{
    $erros = [];
    $camposEmFalta = [];

    foreach ($camposObrigatorios[$etapa] ?? [] as $campo) {
        if (trim($_SESSION[$chaveSessao][$campo] ?? '') === '') {
            $camposEmFalta[] = $labelsCampos[$campo] ?? $campo;
        }
    }

    if (empty($camposEmFalta)) {
        return [];
    }

    $totalObrigatorios = count($camposObrigatorios[$etapa] ?? []);

    if (count($camposEmFalta) === $totalObrigatorios) {
        $erros[] = 'Preencha todos os campos obrigatórios desta etapa para poder avançar.';
    } else {
        foreach ($camposEmFalta as $campo) {
            $erros[] = 'O campo "' . $campo . '" é obrigatório.';
        }
    }

    return $erros;
}

function primeira_etapa_incompleta($chaveSessao, $etapaAtual, $etapas, $camposObrigatorios, $labelsCampos)
{
    $indiceAtual = indice_etapa($etapaAtual, $etapas);

    for ($i = 0; $i < $indiceAtual; $i++) {
        $etapa = $etapas[$i];

        $erros = validar_etapa_temporaria(
            $chaveSessao,
            $etapa,
            $camposObrigatorios,
            $labelsCampos
        );

        if (!empty($erros)) {
            return $etapa;
        }
    }

    return null;
}

function classe_tab($etapa, $etapaAtual)
{
    return $etapa === $etapaAtual ? 'nav-link active' : 'nav-link';
}

function aria_tab($etapa, $etapaAtual)
{
    return $etapa === $etapaAtual ? 'true' : 'false';
}

function classe_painel($etapa, $etapaAtual)
{
    return $etapa === $etapaAtual
        ? 'tab-pane fade show active'
        : 'tab-pane fade';
}

function classe_stepper($etapa, $etapaAtual, $etapas)
{
    $indiceEtapa = indice_etapa($etapa, $etapas);
    $indiceAtual = indice_etapa($etapaAtual, $etapas);

    if ($indiceEtapa < $indiceAtual) {
        return 'form-step concluido';
    }

    if ($indiceEtapa === $indiceAtual) {
        return 'form-step atual';
    }

    return 'form-step pendente';
}

?>