<?php
/* =========================================================
   LOGOUT
   Termina a sessao do utilizador e volta ao login.
   ========================================================= */

require_once __DIR__ . '/../private/includes/funcoes.php';

logout_and_redirect(BASE_URL . '/public/login.php');
?>
