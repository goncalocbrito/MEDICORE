<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_once __DIR__ . '/../../../config/config.php';

// Só aceita POST com a ação correta
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['acao'] ?? '') !== 'atualizar_pagina_publica') {
    header('Location: backoffice.php');
    exit;
}

// ---------------------------------------------------------
// Ligação à base de dados
// ---------------------------------------------------------
$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Pasta onde ficam as imagens da página pública
$pasta_img = __DIR__ . '/../../../public/assets/img/';

// ---------------------------------------------------------
// Função auxiliar: mover upload para pasta pública
// Devolve o caminho relativo (assets/img/...) ou null se não houve upload.
// ---------------------------------------------------------
function guardar_imagem(string $campo_file, string $nome_destino, string $pasta_img): ?string
{
    if (empty($_FILES[$campo_file]['tmp_name'])) return null;
    if ($_FILES[$campo_file]['error'] !== UPLOAD_ERR_OK) return null;

    $ext = strtolower(pathinfo($_FILES[$campo_file]['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    if (!in_array($ext, $permitidos, true)) return null;

    $nome_final = $nome_destino . '.' . $ext;
    $destino    = $pasta_img . $nome_final;

    if (!move_uploaded_file($_FILES[$campo_file]['tmp_name'], $destino)) return null;

    return 'assets/img/' . $nome_final;
}

try {
    $pdo->beginTransaction();

    // -------------------------------------------------------
    // 1. Configuração geral (navbar, sobre, contacto, rodapé)
    // -------------------------------------------------------

    // Logótipo da navbar: pode ser substituído por ficheiro
    $navbar_logo = trim($_POST['navbar_logo'] ?? '');
    $novo_logo   = guardar_imagem('navbar_logo_ficheiro', 'MEDICORE_logotipo_branco', $pasta_img);
    if ($novo_logo) $navbar_logo = $novo_logo;

    $stmt = $pdo->prepare("
        UPDATE pagina_publica_config SET
            navbar_logo           = :navbar_logo,
            navbar_link_sobre     = :navbar_link_sobre,
            navbar_link_equipa    = :navbar_link_equipa,
            navbar_link_funcional = :navbar_link_funcional,
            navbar_link_hospitais = :navbar_link_hospitais,
            navbar_link_contacto  = :navbar_link_contacto,
            navbar_btn_restrita   = :navbar_btn_restrita,
            sobre_titulo          = :sobre_titulo,
            sobre_texto           = :sobre_texto,
            contacto_texto        = :contacto_texto,
            rodape_localizacao    = :rodape_localizacao,
            rodape_horario_semana = :rodape_horario_semana,
            rodape_email          = :rodape_email,
            rodape_telefone       = :rodape_telefone,
            atualizado_por        = :atualizado_por
        LIMIT 1
    ");
    $stmt->execute([
        ':navbar_logo'           => $navbar_logo,
        ':navbar_link_sobre'     => trim($_POST['navbar_link_sobre']     ?? 'Sobre'),
        ':navbar_link_equipa'    => trim($_POST['navbar_link_equipa']    ?? 'Nossa Equipa'),
        ':navbar_link_funcional' => trim($_POST['navbar_link_funcional'] ?? 'Funcionalidades'),
        ':navbar_link_hospitais' => trim($_POST['navbar_link_hospitais'] ?? 'Hospitais e Clínicas'),
        ':navbar_link_contacto'  => trim($_POST['navbar_link_contacto']  ?? 'Contacto'),
        ':navbar_btn_restrita'   => trim($_POST['navbar_btn_restrita']   ?? 'Área Restrita'),
        ':sobre_titulo'          => trim($_POST['sobre_titulo']          ?? ''),
        ':sobre_texto'           => trim($_POST['sobre_texto']           ?? ''),
        ':contacto_texto'        => trim($_POST['contacto_texto']        ?? ''),
        ':rodape_localizacao'    => trim($_POST['rodape_localizacao']    ?? ''),
        ':rodape_horario_semana' => trim($_POST['rodape_horario_semana'] ?? ''),
        ':rodape_email'          => trim($_POST['rodape_email']          ?? ''),
        ':rodape_telefone'       => trim($_POST['rodape_telefone']       ?? ''),
        ':atualizado_por'        => $_SESSION['utilizador'] ?? 'admin',
    ]);

    // -------------------------------------------------------
    // 2. Slides do carrossel
    // -------------------------------------------------------
    $slide_ids      = $_POST['slide_id']       ?? [];
    $slide_imagens  = $_POST['slide_imagem']   ?? [];
    $slide_titulos  = $_POST['slide_titulo']   ?? [];
    $slide_descricoes = $_POST['slide_descricao'] ?? [];

    $stmt_slide = $pdo->prepare("
        UPDATE pagina_publica_slides SET
            imagem    = :imagem,
            titulo    = :titulo,
            descricao = :descricao,
            ordem     = :ordem
        WHERE id_slide = :id
    ");

    foreach ($slide_ids as $idx => $id_slide) {
        $id_slide = (int)$id_slide;
        if ($id_slide <= 0) continue;

        // Verificar se foi feito upload de ficheiro para este slide
        $campo_file = 'slide_ficheiro_' . $id_slide;
        $nome_slug  = 'slide_' . $id_slide;
        $novo_path  = guardar_imagem($campo_file, $nome_slug, $pasta_img);

        $imagem = $novo_path ?? trim($slide_imagens[$idx] ?? '');

        $stmt_slide->execute([
            ':imagem'    => $imagem,
            ':titulo'    => trim($slide_titulos[$idx]    ?? ''),
            ':descricao' => trim($slide_descricoes[$idx] ?? ''),
            ':ordem'     => $idx + 1,
            ':id'        => $id_slide,
        ]);
    }

    // -------------------------------------------------------
    // 3. Hospitais e Clínicas
    // -------------------------------------------------------

    // 3a. Remover hospitais marcados pelo utilizador
    $remover_raw = trim($_POST['hospitais_remover'] ?? '');
    if ($remover_raw !== '') {
        $ids_remover = array_filter(array_map('intval', explode(',', $remover_raw)));
        if (!empty($ids_remover)) {
            $placeholders = implode(',', array_fill(0, count($ids_remover), '?'));
            $pdo->prepare("DELETE FROM pagina_publica_hospitais WHERE id_hospital IN ($placeholders)")
                ->execute($ids_remover);
        }
    }

    // 3b. Atualizar hospitais existentes e inserir novos
    $h_ids       = $_POST['hospital_id']       ?? [];
    $h_ordens    = $_POST['hospital_ordem']    ?? [];
    $h_nomes     = $_POST['hospital_nome']     ?? [];
    $h_descricoes = $_POST['hospital_descricao'] ?? [];
    $h_imagens   = $_POST['hospital_imagem']   ?? [];

    $stmt_upd = $pdo->prepare("
        UPDATE pagina_publica_hospitais SET
            ordem     = :ordem,
            nome      = :nome,
            descricao = :descricao,
            imagem    = :imagem,
            isActive  = :isActive
        WHERE id_hospital = :id
    ");

    $stmt_ins = $pdo->prepare("
        INSERT INTO pagina_publica_hospitais (ordem, nome, descricao, imagem, isActive)
        VALUES (:ordem, :nome, :descricao, :imagem, :isActive)
    ");

    foreach ($h_ids as $idx => $h_id) {
        $h_id      = (int)$h_id;
        $nome      = trim($h_nomes[$idx]      ?? '');
        $descricao = trim($h_descricoes[$idx] ?? '');
        $imagem_base = trim($h_imagens[$idx]  ?? '');
        $ordem     = (int)($h_ordens[$idx]    ?? ($idx + 1));

        if ($h_id > 0) {
            // Hospital existente
            $isActive   = isset($_POST['hospital_visivel_' . $h_id]) ? 1 : 0;
            $campo_file = 'hospital_ficheiro_' . $h_id;
            $novo_img   = guardar_imagem($campo_file, $imagem_base, $pasta_img);
            // Se subiu ficheiro, o nome base vem do nome original do ficheiro
            if ($novo_img) {
                $imagem_base = pathinfo($_FILES[$campo_file]['name'], PATHINFO_FILENAME);
            }

            $stmt_upd->execute([
                ':ordem'     => $ordem,
                ':nome'      => $nome,
                ':descricao' => $descricao,
                ':imagem'    => $imagem_base,
                ':isActive'  => $isActive,
                ':id'        => $h_id,
            ]);
        } else {
            // Novo hospital (id = 0)
            // Descobrir o índice do novo (para o campo de ficheiro correto)
            // O índice dos novos é calculado relativamente ao número de hospitais existentes
            $novo_idx   = $idx - count(array_filter($h_ids, fn($x) => (int)$x > 0)) + 1;
            $campo_file = 'hospital_ficheiro_novo_' . $novo_idx;
            $isActive   = isset($_POST['hospital_visivel_novo_' . $novo_idx]) ? 1 : 0;

            if (empty($nome)) continue; // ignora linhas vazias

            $novo_img = guardar_imagem($campo_file, $imagem_base ?: 'hospital_' . time(), $pasta_img);
            if ($novo_img) {
                $imagem_base = pathinfo($_FILES[$campo_file]['name'], PATHINFO_FILENAME);
            }

            $stmt_ins->execute([
                ':ordem'     => $ordem,
                ':nome'      => $nome,
                ':descricao' => $descricao,
                ':imagem'    => $imagem_base,
                ':isActive'  => $isActive,
            ]);
        }
    }

    $pdo->commit();
    header('Location: backoffice.php?sucesso=1');
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[backoffice] Erro ao guardar: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    header('Location: backoffice.php?erro=' . urlencode($e->getMessage()));
    exit;
}