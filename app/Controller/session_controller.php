<?php

/**
 * session_controller.php
 * Gestiona el cicle de vida de sessions i restauració amb remember-me
 */

// Temps d'expiració de la sessió en segons (40 minuts)
define('SESSION_TIMEOUT_SECONDS', 40 * 60);

/**
 * initialize_session
 * Inicia la sessió, aplica timeout i restaura des de remember-me token si cal
 */
function initialize_session() {
    // Iniciar sessió si no està activa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Si existeix 'last_activity' i ha superat el timeout, destruir la sessió
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    // Si la sessió no està activa intentar restaurar des de la cookie 'remember-me'
    if ((!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) && isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        $cookieToken = $_COOKIE['remember_token'];
        $user = find_user_by_remember_token($cookieToken);
        if ($user) {
            // Restaurar sessió
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['created'] = time();
            $_SESSION['last_activity'] = time();

            // Rotar token per seguretat
            try {
                $newToken = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                $newToken = bin2hex(openssl_random_pseudo_bytes(32));
            }
            set_remember_token($user['id'], $newToken);
            setcookie('remember_token', $newToken, time() + (30*24*60*60), '/', '', false, true);
        } else {
            // Token invàlid: esborrar la cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
    }

    // Actualitzar l'última activitat per a sessions actives
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * is_logged_in
 * Retorna true si hi ha una sessió vàlida
 */
function is_logged_in() {
    return (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']));
}

/**
 * logout_user
 * Destrueix la sessió i (opcional) redirigeix
 */
function logout_user($redirect = null) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
    // Si hi ha un usuari logat, netejar els seus tokens
    $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if ($uid) {
        if (function_exists('clear_remember_token')) {
            clear_remember_token($uid);
        }
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    if ($redirect) {
        header('Location: ' . $redirect);
        exit;
    }
}

?>
