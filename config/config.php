<?php
// Caminho publico base da aplicacao no browser.
define('BASE_URL', '/MEDICORE');

// Configurações globais da aplicação
define('APP_NAME', 'MEDICORE');
define('APP_VERSION', '1.0.0');
define('APP_COPYRIGHT', '© 2026 ISEP');

// Caminho publico para os assets da area privada.
define('PRIVATE_ASSETS_URL', BASE_URL . '/private/assets');

define('MYSQL_HOST', 'vsgate-s1.dei.isep.ipp.pt');
define('MYSQL_PORT', '10464');
define('MYSQL_DATABASE', 'db1230404');
define('MYSQL_USERNAME', '1230404');
define('MYSQL_PASSWORD', 'brito_404');

// Configuracao usada para esconder identificadores internos nas URLs.
define('OPENSSL_METHOD', 'AES-256-CBC');
define('OPENSSL_KEY', 'MEDICORE_1230404_KEY_PRIVATE_32X');
define('OPENSSL_IV', 'MEDICORE_IV_2026');
?>
