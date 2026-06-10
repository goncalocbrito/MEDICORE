<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

require_once __DIR__ . '/../../../config/config.php';

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_fornecedores.php');
    exit;
}

$id_fornecedor = $_POST['id_fornecedor'] ?? 0;

$stmt = $pdo->prepare("
    UPDATE fornecedores
    SET isActive = 0
    WHERE id_fornecedor = :id_fornecedor
");

$stmt->execute([
    ':id_fornecedor' => $id_fornecedor
]);

header('Location: lista_fornecedores.php');
exit;
?>
