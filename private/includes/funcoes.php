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

    $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $permissao = permissao_por_caminho($caminho);

    if ($permissao !== null && !user_has_permission($permissao)) {
        header('Location: ' . rota_inicial_utilizador());
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

/* Cria uma ligacao PDO reutilizavel com a base de dados configurada. */
function medicore_pdo()
{
    return new PDO(
        'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}

/* Encripta valores internos para evitar mostrar IDs simples na barra do browser. */
function aes_encrypt($value)
{
    return bin2hex(openssl_encrypt(
        (string) $value,
        OPENSSL_METHOD,
        OPENSSL_KEY,
        OPENSSL_RAW_DATA,
        OPENSSL_IV
    ));
}

/* Desencripta valores recebidos pela URL. Se falhar, devolve false. */
function aes_decrypt($value)
{
    if (!is_string($value) || $value === '' || strlen($value) % 2 !== 0 || !ctype_xdigit($value)) {
        return false;
    }

    return openssl_decrypt(
        hex2bin($value),
        OPENSSL_METHOD,
        OPENSSL_KEY,
        OPENSSL_RAW_DATA,
        OPENSSL_IV
    );
}

/* Gera uma referencia segura para usar em links. */
function url_ref($value)
{
    return urlencode(aes_encrypt($value));
}

/* Le uma referencia segura da URL, mantendo compatibilidade com o parametro id antigo. */
function id_from_request($fallbackParam = 'id', $refParam = 'ref')
{
    if (!empty($_GET[$refParam])) {
        $valor = aes_decrypt($_GET[$refParam]);
        return is_numeric($valor) ? (int) $valor : 0;
    }

    return isset($_GET[$fallbackParam]) && is_numeric($_GET[$fallbackParam])
        ? (int) $_GET[$fallbackParam]
        : 0;
}

/* Junta tipo e ID de processo numa referencia unica. */
function processo_ref($tipo, $id)
{
    return url_ref($tipo . '|' . (int) $id);
}

/* Le a referencia de processo, mantendo compatibilidade com tipo/id antigos. */
function processo_from_request()
{
    if (!empty($_GET['ref'])) {
        $valor = aes_decrypt($_GET['ref']);
        $partes = explode('|', (string) $valor);

        if (count($partes) === 2 && in_array($partes[0], ['manutencao', 'calibracao'], true) && is_numeric($partes[1])) {
            return [$partes[0], (int) $partes[1]];
        }
    }

    return [
        $_GET['tipo'] ?? $_POST['tipo'] ?? '',
        (int) ($_GET['id'] ?? $_POST['id'] ?? 0)
    ];
}

/* Define as permissoes reais por tipo de utilizador. */
function permissoes_por_tipo_utilizador($tipo)
{
    $permissoes = [
        'Administrador' => [
            'dashboard',
            'localizacoes',
            'utilizadores',
            'backoffice',
            'familias_equipamentos'
        ],
        'Engenheiro' => [
            'equipamentos',
            'acessorios',
            'consumiveis',
            'fornecedores',
            'calibracoes'
        ],
        'Enfermeiro' => [
            'equipamentos',
            'acessorios',
            'consumiveis'
        ]
    ];

    return $permissoes[$tipo] ?? [];
}

/* Verifica se o utilizador autenticado pode aceder a um modulo. */
function user_has_permission($permissao)
{
    start_session();

    $permissoes = $_SESSION['permissoes_utilizador']
        ?? permissoes_por_tipo_utilizador($_SESSION['tipo_utilizador'] ?? '');

    return in_array($permissao, $permissoes, true);
}

/* Rota inicial mais adequada para cada perfil. */
function rota_inicial_utilizador($tipo = null)
{
    $tipo = $tipo ?? ($_SESSION['tipo_utilizador'] ?? '');

    switch ($tipo) {
        case 'Administrador':
            return BASE_URL . '/private/index.php';

        case 'Engenheiro':
            return BASE_URL . '/private/views/equipamentos/dashboard_engenheiro.php';

        case 'Enfermeiro':
            return BASE_URL . '/private/views/equipamentos/lista_equipamentos.php';

        default:
            return BASE_URL . '/public/login.php';
    }
}

/* Bloqueia acesso direto a paginas privadas sem permissao. */
function redirect_if_no_permission($permissao, $redirect_to = null)
{
    redirect_if_not_logged();

    if (!user_has_permission($permissao)) {
        header('Location: ' . ($redirect_to ?? rota_inicial_utilizador()));
        exit;
    }
}

/* Identifica o modulo atual pelo caminho da pagina. */
function permissao_por_caminho($caminho)
{
    if (strpos($caminho, '/private/index.php') !== false || strpos($caminho, '/private/home.php') !== false) {
        return 'dashboard';
    }

    if (strpos($caminho, '/private/views/backoffice/') !== false) {
        return 'backoffice';
    }

    if (strpos($caminho, '/private/views/localizacoes/') !== false) {
        return 'localizacoes';
    }

    if (strpos($caminho, '/private/views/utilizadores/') !== false) {
        return 'utilizadores';
    }

    if (strpos($caminho, '/private/views/fornecedores/') !== false) {
        return 'fornecedores';
    }

    if (strpos($caminho, '/private/views/calibracao_manutencao/') !== false) {
        return 'calibracoes';
    }

    if (strpos($caminho, '/private/views/equipamentos/lista_familia_equipamentos.php') !== false
        || strpos($caminho, '/private/views/equipamentos/nova_familia_equipamentos.php') !== false) {
        return 'familias_equipamentos';
    }

    if (strpos($caminho, '/private/views/equipamentos/acessorios.php') !== false) {
        return 'acessorios';
    }

    if (strpos($caminho, '/private/views/equipamentos/consumiveis.php') !== false) {
        return 'consumiveis';
    }

    if (strpos($caminho, '/private/views/equipamentos/') !== false) {
        return 'equipamentos';
    }

    return null;
}

/* Aplica a permissao automaticamente nas paginas que usam o header privado. */
function proteger_pagina_atual()
{
    $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $permissao = permissao_por_caminho($caminho);

    if ($permissao !== null) {
        redirect_if_no_permission($permissao);
    }
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
