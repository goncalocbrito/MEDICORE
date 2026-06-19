<?php
/* =========================================================
   ENTRADA PRINCIPAL DO PROJETO
   Permite abrir o projeto através do endereço obrigatório:
   /sibdas/1230404/medicore
   ========================================================= */

require_once __DIR__ . '/config/config.php';

header('Location: ' . BASE_URL . '/public/index.php');
exit;
?>