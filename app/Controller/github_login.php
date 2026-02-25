<?php
// Inicia flujo Hybridauth -> GitHub (usa vendor/autoload.php)
require_once __DIR__ . '/../../config/mailer.php'; // carrega .env
require_once __DIR__ . '/../../vendor/autoload.php';

use Hybridauth\Hybridauth;

    $config = require __DIR__ . '/../../config/hybridauth.php';

try {
    $hybridauth = new Hybridauth($config);
    // Esto iniciará el flujo de autenticación y redirigirá a GitHub
    $adapter = $hybridauth->authenticate('GitHub');
    // Normalmente Hybridauth redirige y no se llega aquí, pero dejamos control
} catch (Exception $e) {
    error_log('HybridAuth login error: ' . $e->getMessage());
    header('Location: ../app/View/login.php?error=oauth_error');
    exit;
}

?>
