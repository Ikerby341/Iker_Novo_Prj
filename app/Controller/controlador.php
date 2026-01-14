<?php

/**
 * controlador.php
 * Archivo principal que orquesta l'inclusió de tots els controllers específics
 */

// Carregar la configuració de l'aplicació (BASE_URL, etc.)
if (file_exists(__DIR__ . '/../../config/app.php')) {
    include_once __DIR__ . '/../../config/app.php';
}

// Incluïm els models
include_once __DIR__ .'/../Model/users_model.php';
include_once __DIR__ .'/../Model/articles_model.php';

// Incluïm la configuració de reCAPTCHA
if (file_exists(__DIR__ . '/../../config/recaptcha.php')) {
    include_once __DIR__ . '/../../config/recaptcha.php';
}

// Incluïm els controllers específics
include_once __DIR__ .'/captcha_controller.php';
include_once __DIR__ .'/session_controller.php';
include_once __DIR__ .'/auth_controller.php';
include_once __DIR__ .'/pagination_controller.php';
include_once __DIR__ .'/articles_controller.php';
include_once __DIR__ .'/users_controller.php';

// Inicialitzar la sessió
initialize_session();

// Processem petició de tancament de sessió si s'indica a la query string
if (isset($_GET['logout']) && ($_GET['logout'] === '1' || $_GET['logout'] === 1)) {
    logout_user((defined('BASE_URL') ? BASE_URL : '/'));
    exit;
}

?>