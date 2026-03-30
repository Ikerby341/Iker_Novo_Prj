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

// Incluïm els controllers específics
include_once __DIR__ .'/captcha_controller.php';
include_once __DIR__ .'/session_controller.php';
include_once __DIR__ .'/auth_controller.php';
include_once __DIR__ .'/pagination_controller.php';
include_once __DIR__ .'/articles_controller.php';
include_once __DIR__ .'/users_controller.php';

// Inicialitzar la sessió
initialize_session();

// Processem petició de tancament de sessió només via POST amb token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && isset($_POST['csrf_token'])) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        logout_user((defined('BASE_URL') ? BASE_URL : '/'));
        exit;
    }
}

?>