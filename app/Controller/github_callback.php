<?php
// Retorn de Hybridauth per GitHub (usa vendor/autoload.php)
require_once __DIR__ . '/../../config/mailer.php'; // carrega .env
require_once __DIR__ . '/../../vendor/autoload.php';

use Hybridauth\Hybridauth;

$config = require __DIR__ . '/../../config/hybridauth.php';

try {
    $hybridauth = new Hybridauth($config);
    $adapter = $hybridauth->authenticate('GitHub');
    $userProfile = $adapter->getUserProfile();
} catch (Exception $e) {
    error_log('HybridAuth callback error: ' . $e->getMessage());
    header('Location: ../app/View/login.php?error=oauth_error');
    exit;
}

// Procesamiento del perfil: buscar/crear usuario i iniciar sessió
require_once __DIR__ . '/../Model/users_model.php';
require_once __DIR__ . '/auth_controller.php';

$githubId = $userProfile->identifier ?? null;
$email = $userProfile->email ?? null;
$username = $userProfile->displayName ?: ($userProfile->username ?? null);

if (!$githubId) {
    header('Location: ../app/View/login.php?error=oauth_no_id');
    exit;
}

$user = false;
if (function_exists('get_user_by_github_id')) {
    $user = get_user_by_github_id($githubId);
}

// Si no existe por github_id, intentar por email usando el modelo
if (!$user && $email) {
    $user = get_user_by_email($email);
}

if ($user) {
    // Si existe, enlazar github_id si no estaba y loguear
    if (empty($user['github_id']) && function_exists('update_user_github_link')) {
        update_user_github_link($user['id'], $githubId);
    }
    login_user_oauth($user['id']);
    header('Location: ../app/View/vista.php');
    exit;
} else {
    // Crear un usuario nuevo
    if (!$username) {
        // intentar derivar username desde email
        if ($email) {
            $parts = explode('@', $email);
            $username = $parts[0];
        } else {
            $username = 'gh_user_' . substr($githubId, 0, 8);
        }
    }

    // Normalizar username para evitar conflictos
    $baseUsername = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $username);
    $finalUsername = $baseUsername;
    $i = 1;
    while (user_exists_by_username($finalUsername)) {
        $finalUsername = $baseUsername . '_' . $i;
        $i++;
    }

    // Crear usuari OAuth GitHub usant el modelo
    try {
        if (create_user_oauth_github($finalUsername, $email, $githubId)) {
            $user = get_user_by_github_id($githubId);
            if ($user) {
                login_user_oauth($user['id']);
                header('Location: ../app/View/vista.php');
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('DB create github user error: ' . $e->getMessage());
    }
    header('Location: ../app/View/login.php?error=oauth_create_failed');
    exit;

}

?>
