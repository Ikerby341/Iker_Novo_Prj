<?php

/**
 * auth_controller.php
 * Gestiona autenticació, registre i validació de contrasenyes
 */

/**
 * validar_contrasenya
 * Retorna array de errors (buit si és vàlida)
 */
function validar_contrasenya($password) {
    $errors = [];
    if (strlen($password) < 7) {
        $errors[] = 'La contrasenya ha de tenir almenys 7 caràcters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'La contrasenya ha de contenir almenys una majúscula.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'La contrasenya ha de contenir almenys una minúscula.';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'La contrasenya ha de contenir almenys un símbol.';
    }
    return $errors;
}

/**
 * register_user
 * Procesa dades de registre; retorna array('success'=>bool,'errors'=>array)
 */
function register_user($username, $email, $password, $password_confirm) {
    $username = trim($username);
    $email = trim($email);
    $errors = [];

    if ($username === '') $errors[] = 'El nom d\'usuari és obligatori.';
    if ($email === '') $errors[] = 'L\'email és obligatori.';

    // Username únic
    if (user_exists_by_username($username)) {
        $errors[] = 'Ja existeix un usuari amb aquest nom d\'usuari.';
    }

    // Contrasenya i confirm
    if ($password === '') $errors[] = 'La contrasenya és obligatòria.';
    if ($password !== $password_confirm) $errors[] = 'Les contrasenyes no coincideixen.';

    // Validar força contrasenya
    $pwErrors = validar_contrasenya($password);
    if (!empty($pwErrors)) $errors = array_merge($errors, $pwErrors);

    // Verificar reCAPTCHA (server-side)
    $recToken = $_POST['g-recaptcha-response'] ?? null;
    if (empty($recToken)) {
        $errors[] = 'ReCAPTCHA requerit. Si us plau, marca la casella i torna-ho a provar.';
    } else {
        if (!function_exists('verify_recaptcha') || !verify_recaptcha($recToken)) {
            $errors[] = 'Verificació reCAPTCHA fallida. Si us plau, torna-ho a provar.';
        }
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_BCRYPT);
    if ($hash === false) {
        return ['success' => false, 'errors' => ['Error al encriptar la contrasenya.']];
    }

    $created = create_user($username, $email, $hash);
    if ($created) {
        return ['success' => true, 'errors' => []];
    }
    return ['success' => false, 'errors' => ['Error en crear l\'usuari a la base de dades.']];
}

/**
 * login_user
 * Verifica credencials. Retorna array('success'=>bool,'errors'=>array)
 * En cas d'èxit, inicia sessió i posa $_SESSION['user_id'] i $_SESSION['username']
 */
function login_user($username, $password, $remember = false) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    $username = trim($username);
    $errors = [];

    if ($username === '') $errors[] = 'El nom d\'usuari és obligatori.';
    if ($password === '') $errors[] = 'La contrasenya és obligatòria.';

    if (!empty($errors)) return ['success' => false, 'errors' => $errors];

    // Número d'intents fallits (per sessió)
    $attempts = isset($_SESSION['login_attempts']) ? (int)$_SESSION['login_attempts'] : 0;

    // Si ja hi ha hagut 3 intents fallits o més, requerim reCAPTCHA abans de continuar
    if ($attempts >= 3) {
        $recToken = $_POST['g-recaptcha-response'] ?? null;
        if (empty($recToken)) {
            return ['success' => false, 'errors' => ['ReCAPTCHA requerit. Si us plau, marca la casella i torna-ho a provar.']];
        }
        if (!function_exists('verify_recaptcha') || !verify_recaptcha($recToken)) {
            // Incrementar intents fallits
            $_SESSION['login_attempts'] = $attempts + 1;
            return ['success' => false, 'errors' => ['Verificació reCAPTCHA fallida. Si us plau, torna-ho a provar.']];
        }
    }

    $user = get_user_by_username($username);
    if (!$user) {
        // Incrementar intents fallits
        $_SESSION['login_attempts'] = $attempts + 1;
        return ['success' => false, 'errors' => ['Aquest usuari no existeix.']];
    }

    // Verificar contrasenya
    $stored = $user['password'];
    $verified = false;

    // Intentem verificació amb password_verify si és un hash conegut
    if (!empty($stored) && password_get_info($stored)['algo'] !== 0) {
        if (password_verify($password, $stored)) {
            $verified = true;
        }
    } else {
        // Si no sembla un hash (pot ser text pla a la BD), fem comparació directa
        if ($password === $stored) {
            $verified = true;
            // Rehash i actualitza la BD perquè la contrasenya ja no quedi en text pla
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            if ($newHash !== false) {
                update_user_password_hash($user['id'], $newHash);
            }
        }
    }

    if (!$verified) {
        // Incrementar intents fallits
        $_SESSION['login_attempts'] = $attempts + 1;
        return ['success' => false, 'errors' => ['Contrasenya incorrecta, si us plau intenta-ho de nou.']];
    }

    // Iniciar sessió
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    // Resetar intents fallits
    $_SESSION['login_attempts'] = 0;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['created'] = time();
    $_SESSION['last_activity'] = time();

    // Si l'usuari vol que el sistema el recordi, generem un token i l'emmagatzemem
    if ($remember) {
        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
        }
        // Desar token a la BD i a la cookie (30 dies)
        set_remember_token($user['id'], $token);
        setcookie('remember_token', $token, time() + (30*24*60*60), '/', '', false, true);
    }

    return ['success' => true, 'errors' => []];
}

?>
