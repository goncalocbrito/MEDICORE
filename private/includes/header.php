<?php
require_once __DIR__ . '/../../config/config.php';

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>

    <!-- favicon -->
    <link rel="shortcut icon" href="<?php echo PRIVATE_ASSETS_URL; ?>/img/MEDICORE_icon.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <!-- estilos CSS -->
    <link rel="stylesheet" href="/MEDICORE/private/assets/css/1230404.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo PRIVATE_ASSETS_URL; ?>/fontawesome/all.min.css">

</head>
<body>
