<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido.']);
    exit;
}

$tiposPermitidos = ['Fabricante', 'Comercial'];
$tipo = trim($_POST['tipo'] ?? '');

$obrigatorios = [
    'nome'          => 'Nome do Fornecedor',
    'nif'           => 'NIF',
    'tipo'          => 'Tipo de Fornecedor',
    'estado'        => 'Estado',
    'telefone'      => 'Telefone',
    'morada'        => 'Morada',
    'codigo_postal' => 'Código Postal',
    'localidade'    => 'Localidade',
    'pais'          => 'País',
];

$erros = [];

foreach ($obrigatorios as $campo => $label) {
    if (trim($_POST[$campo] ?? '') === '') {
        $erros[] = $label . ' é obrigatório.';
    }
}

if ($tipo !== '' && !in_array($tipo, $tiposPermitidos, true)) {
    $erros[] = 'Tipo de Fornecedor inválido.';
}

$emailContacto = trim($_POST['email_contacto'] ?? '');
if ($emailContacto !== '' && !filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'Email do Contacto inválido.';
}

if (!empty($erros)) {
    echo json_encode(['sucesso' => false, 'erros' => $erros]);
    exit;
}

try {
    require_once __DIR__ . '/../../../config/config.php';

    $pdo = new PDO(
        'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare("
        INSERT INTO fornecedores (
            nome_empresa,
            tipo_fornecedor,
            nif,
            telefone,
            email_fornecedor,
            pessoa_responsavel,
            telefone_contacto,
            email_contacto,
            morada,
            codigo_postal,
            localidade,
            pais,
            observacoes,
            isActive
        ) VALUES (
            :nome_empresa,
            :tipo_fornecedor,
            :nif,
            :telefone,
            :email_fornecedor,
            :pessoa_responsavel,
            :telefone_contacto,
            :email_contacto,
            :morada,
            :codigo_postal,
            :localidade,
            :pais,
            :observacoes,
            1
        )
    ");

    $stmt->execute([
        ':nome_empresa'       => trim($_POST['nome']),
        ':tipo_fornecedor'    => $tipo,
        ':nif'                => trim($_POST['nif']),
        ':telefone'           => trim($_POST['telefone']),
        ':email_fornecedor'   => trim($_POST['email_fornecedor'] ?? ''),
        ':pessoa_responsavel' => trim($_POST['contacto_responsavel'] ?? ''),
        ':telefone_contacto'  => trim($_POST['telefone_contacto'] ?? ''),
        ':email_contacto'     => $emailContacto,
        ':morada'             => trim($_POST['morada']),
        ':codigo_postal'      => trim($_POST['codigo_postal']),
        ':localidade'         => trim($_POST['localidade']),
        ':pais'               => trim($_POST['pais']),
        ':observacoes'        => trim($_POST['observacoes'] ?? ''),
    ]);

    echo json_encode([
        'sucesso' => true,
        'id'      => (int) $pdo->lastInsertId(),
        'nome'    => trim($_POST['nome']),
    ]);

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo json_encode(['sucesso' => false, 'erros' => ['Já existe um fornecedor com este NIF.']]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao guardar o fornecedor.']);
    }
} catch (Throwable $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro inesperado ao guardar.']);
}
