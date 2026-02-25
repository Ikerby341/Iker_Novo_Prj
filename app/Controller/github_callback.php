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

// Si no existe por github_id, intentar por email
if (!$user && $email) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT * FROM usuarios WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $user = false;
    }
}

if ($user) {
    // Si existe, enlazar github_id si no estaba y loguear
    if (empty($user['github_id']) && function_exists('update_user_github_link')) {
        update_user_github_link($user['id'], $githubId, 'github');
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

    // Crear usuario con función específica (si existe) o inserción directa
    $created = false;
    if (function_exists('create_user_oauth_github')) {
        $created = create_user_oauth_github($finalUsername, $email, $githubId, 'github');
        if ($created) {
            // obtener id del nuevo usuario
            global $connexio;
            $stmt = $connexio->prepare('SELECT * FROM usuarios WHERE github_id = :g LIMIT 1');
            $stmt->execute([':g' => $githubId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        // Intentar inserción directa (tabla debe tener columnas github_id, hybridauth_provider)
        try {
            global $connexio;
            $stmt = $connexio->prepare('INSERT INTO usuarios (username, email, github_id, hybridauth_provider) VALUES (:u, :e, :g, :p)');
            $created = $stmt->execute([':u' => $finalUsername, ':e' => $email, ':g' => $githubId, ':p' => 'github']);
            if ($created) {
                $id = $connexio->lastInsertId();
                $user = get_user_by_id($id);
            }
        } catch (Exception $e) {
            error_log('DB create github user error: ' . $e->getMessage());
            $created = false;
        }
    }

    if ($user) {
        login_user_oauth($user['id']);
        header('Location: ../app/View/vista.php');
        exit;
    }

    // Alternativa: intentar crear usuari mínim sense columnes específiques
    try {
        global $connexio;
        $stmt = $connexio->prepare('INSERT INTO usuarios (username, email) VALUES (:u, :e)');
        $ok = $stmt->execute([':u' => $finalUsername, ':e' => $email]);
        if ($ok) {
            $id = $connexio->lastInsertId();
            // Intentar actualizar github_id/hybridauth_provider si existen
            try {
                $stmt = $connexio->prepare('UPDATE usuarios SET github_id = :g, hybridauth_provider = :p WHERE id = :id');
                $stmt->execute([':g' => $githubId, ':p' => 'github', ':id' => $id]);
            } catch (Exception $e) {
                // Columnas no presentes o fallo: ignorar
                error_log('Github column update failed (likely missing columns): ' . $e->getMessage());
            }
            $user = get_user_by_id($id);
            if ($user) {
                login_user_oauth($user['id']);
                header('Location: ../app/View/vista.php');
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('Fallback create user error: ' . $e->getMessage());
    }

    header('Location: ../app/View/login.php?error=oauth_create_failed');
    exit;

}

?>
