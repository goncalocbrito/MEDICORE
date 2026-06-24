<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_fornecedores.php');
    exit;
}

$id_fornecedor = (int) ($_POST['id_fornecedor'] ?? 0);

if ($id_fornecedor <= 0) {
    header('Location: lista_fornecedores.php?erro=id_invalido');
    exit;
}

try {
    $pdo = medicore_pdo();

    $stmt = $pdo->prepare("UPDATE fornecedores SET isActive = 0 WHERE id_fornecedor = :id");
    $stmt->execute([':id' => $id_fornecedor]);

    header('Location: lista_fornecedores.php?apagado=1');
    exit;
} catch (Throwable $e) {
    header('Location: lista_fornecedores.php?erro=' . urlencode($e->getMessage()));
    exit;
}
