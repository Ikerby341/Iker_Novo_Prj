<?php

// Carregar la configuració de l'aplicació (BASE_URL, etc.)
if (file_exists(__DIR__ . '/../../config/app.php')) {
    include_once __DIR__ . '/../../config/app.php';
}

/**
 * verify_recaptcha
 * Verifica el token de Google reCAPTCHA (capa servidor).
 * Retorna true si la verificació és correcta.
 */
function verify_recaptcha($token) {
    if (!defined('RECAPTCHA_SECRET') || empty(RECAPTCHA_SECRET)) return false;

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = http_build_query([
        'secret' => RECAPTCHA_SECRET,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => $data,
            'timeout' => 5
        ]
    ];

    $context  = stream_context_create($opts);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) return false;

    $json = json_decode($result, true);
    return isset($json['success']) && $json['success'] === true;
}

include_once __DIR__ .'/../Model/modelo.php';
// configuració reCAPTCHA (secret)
if (file_exists(__DIR__ . '/../../config/recaptcha.php')) {
    include_once __DIR__ . '/../../config/recaptcha.php';
}

// Gestió de la sessió: iniciar sessió i aplicar el timeout
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Temps d'expiració de la sessió en segons (40 minuts)
define('SESSION_TIMEOUT_SECONDS', 40 * 60);

// Si existeix 'last_activity' i ha superat el timeout, destruir la sessió
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
    // Caducada
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

// Si la sessió no està activa (o s'ha destruït) intentar restaurar des de la cookie 'remember-me'
if ((!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) && isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
    $cookieToken = $_COOKIE['remember_token'];
    $user = find_user_by_remember_token($cookieToken);
    if ($user) {
        // restaurar sessió
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['created'] = time();
        $_SESSION['last_activity'] = time();

        // rotar token per seguretat
        try {
            $newToken = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $newToken = bin2hex(openssl_random_pseudo_bytes(32));
        }
        set_remember_token($user['id'], $newToken);
        setcookie('remember_token', $newToken, time() + (30*24*60*60), '/', '', false, true);
    } else {
        // token invàlid: esborrar la cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}

// Actualitzar l'última activitat per a sessions actives
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

// Processem petició de tancament de sessió si s'indica a la query string
if (isset($_GET['logout']) && ($_GET['logout'] === '1' || $_GET['logout'] === 1)) {
    // cridem a la funció que neteja la sessió i redirigeix
    logout_user((defined('BASE_URL') ? BASE_URL : '/'));
    exit;
}

/**
 * Llegeix la pàgina actual de la query string. Si no existeix o és invàlida, retorna 1.
 * Assegura que el valor retornat sigui un integer >= 1.
 */
/**
 * obtenir_pagina_actual
 * Llegeix la pagina actual de la query string. Retorna 1 si no existeix o es invalida.
 */
function obtenir_pagina_actual() {
    if (isset($_GET['page'])) {
        $raw = trim($_GET['page']);
        if (is_numeric($raw)) {
            $val = (int)$raw;
            $perPage = obtenir_per_pagina();
            $totalPages = obtenir_total_pagines($perPage);
            if ($val > 0 && $val <= $totalPages) {
                return $val;
            }
            return 1;
        }
    }
    return 1;
}

/**
 * mostrar_articles
 * Mostra els articles per la pagina i per-pagina indicats.
 */
function mostrar_articles($articlesPerPagina = 8) {
    $page = obtenir_pagina_actual();
    $perPage = obtenir_per_pagina($articlesPerPagina);
    // Llegim opcions d'ordenació de la query string i validem-les
    $allowedFields = ['ID', 'marca', 'model'];
    $sort = 'ID';
    $dir = 'ASC';
    if (isset($_GET['sort'])) {
        $candidate = trim($_GET['sort']);
        // Normalitzem a minuscules i comparem amb llista d'equivalències
        $map = ['id' => 'ID', 'marca' => 'marca', 'model' => 'model'];
        $lk = strtolower($candidate);
        if (isset($map[$lk])) $sort = $map[$lk];
    }
    if (isset($_GET['dir'])) {
        $d = strtoupper(trim($_GET['dir']));
        if (in_array($d, ['ASC','DESC'])) $dir = $d;
    }

    $articles = generar_articles($page, $perPage, $sort, $dir);
    
    if (is_array($articles)) {
        $sortida = '<div class="articles-grid">';
        foreach ($articles as $fila) {
            $id = isset($fila['ID']) ? (int)$fila['ID'] : 0;
            $marca = isset($fila['marca']) ? htmlspecialchars($fila['marca']) : '';
            $model = isset($fila['model']) ? htmlspecialchars($fila['model']) : '';
            $ruta_img = (defined('BASE_URL') ? BASE_URL : '/') . htmlspecialchars($fila['ruta_img']);
            $sortida .= '<div class="article-card">';
            $sortida .= '<div class="article-image">';
            $sortida .= '<img src="' . $ruta_img . '" alt="' . $marca . ' ' . $model . '" />';
            $sortida .= '</div>';
            $sortida .= '<div class="article-content">';
            $sortida .= "<h3>$marca</h3>";
            $sortida .= "<p>$model</p>";
            $sortida .= '</div>';
            // Si estem logats, mostrem els botons
            if (is_logged_in()) {
                $sortida .= '<div class="article-actions">';
                $sortida .= '<form method="post" action="app/View/update.php">';
                $sortida .= '<input type="hidden" name="id" value="' . $id . '">';
                $sortida .= '<button type="submit" class="edit-btn" title="Editar">✏️</button>';
                $sortida .= '</form>';
                $sortida .= '<form method="post" action="app/View/delete.php">';
                $sortida .= '<input type="hidden" name="id" value="' . $id . '">';
                $sortida .= '<button type="submit" class="delete-btn" title="Esborrar">🗑️</button>';
                $sortida .= '</form>';
                $sortida .= '</div>';
            }
            $sortida .= '</div>';
        }
        $sortida .= '</div>';
        return $sortida;
    }
    return $articles;
}

/**
 * mostrar_paginacio
 * Retorna l'HTML de la paginacio per la pagina actual i per-pagina.
 */
function mostrar_paginacio($articlesPerPagina = 8) {
    $page = obtenir_pagina_actual();
    $perPage = obtenir_per_pagina($articlesPerPagina);
    // Incorporem la mateixa lògica d'ordenació per mantenir els paràmetres en la paginació
    $sort = 'ID';
    $dir = 'ASC';
    if (isset($_GET['sort'])) {
        $map = ['id' => 'ID', 'marca' => 'marca', 'model' => 'model'];
        $lk = strtolower(trim($_GET['sort']));
        if (isset($map[$lk])) $sort = $map[$lk];
    }
    if (isset($_GET['dir'])) {
        $d = strtoupper(trim($_GET['dir']));
        if (in_array($d, ['ASC','DESC'])) $dir = $d;
    }

    return generar_paginacio($page, $perPage, $sort, $dir);
}

/**
 * Llegeix l'opció 'per_page' de la query string i la valida.
 * Retorna el valor vàlid (int) o el valor per defecte passat.
 */
/**
 * obtenir_per_pagina
 * Llegeix l'opcio 'per_page' de la query string i la valida.
 * Retorna el valor valid (int) o el valor per defecte.
 */
function obtenir_per_pagina($default = 8) {
    if (isset($_GET['per_page'])) {
        $raw = trim($_GET['per_page']);
        if (is_numeric($raw)) {
            $val = (int)$raw;
            if ($val >= 1 && $val <= 100) {
                return $val;
            }
        }
    }
    return $default;
}

/**
 * Valida la pàgina sol·licitada i redirigeix automàticament a page=1
 * si la pàgina no existeix o és invàlida. Fa servir header(Location) i exit().
 */
/**
 * validar_pagina_solicitada
 * Valida la pagina sol.licitada i redirigeix a page=1 si no es valida.
 */
function validar_pagina_solicitada() {
    // No fem res si no hi ha paràmetre page
    if (!isset($_GET['page'])) return;

    $raw = trim($_GET['page']);
    // Si no és numèric, redirigim a la pàgina 1
    if (!is_numeric($raw)) {
        $params = $_GET;
        $params['page'] = 1;
        $params['per_page'] = obtenir_per_pagina();
        $qs = http_build_query($params);
        $url = $_SERVER['PHP_SELF'] . '?' . $qs;
        header('Location: ' . $url);
        exit;
    }

    $val = (int)$raw;
    $perPage = obtenir_per_pagina();
    $totalPages = obtenir_total_pagines($perPage);
    if ($val < 1 || $val > $totalPages) {
        $params = $_GET;
        $params['page'] = 1;
        $params['per_page'] = $perPage;
        $qs = http_build_query($params);
        $url = $_SERVER['PHP_SELF'] . '?' . $qs;
        header('Location: ' . $url);
        exit;
    }
}

/* ----------------------------
    Ajuda d'autenticació
    ---------------------------- */

/**
 * validar_contrasenya
 * Retorna array de errors (vacío si és vàlida)
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

    // username únic
    if (user_exists_by_username($username)) {
        $errors[] = 'Ja existeix un usuari amb aquest nom d\'usuari.';
    }

    // contrasenya i confirm
    if ($password === '') $errors[] = 'La contrasenya és obligatòria.';
    if ($password !== $password_confirm) $errors[] = 'Les contrasenyes no coincideixen.';

    // validar força contrasenya
    $pwErrors = validar_contrasenya($password);
    if (!empty($pwErrors)) $errors = array_merge($errors, $pwErrors);

    // verificar reCAPTCHA (server-side)
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

    // hash password
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
    $username = trim($username);
    $errors = [];

    if ($username === '') $errors[] = 'El nom d\'usuari és obligatori.';
    if ($password === '') $errors[] = 'La contrasenya és obligatòria.';

    if (!empty($errors)) return ['success' => false, 'errors' => $errors];

    $user = get_user_by_username($username);
    if (!$user) return ['success' => false, 'errors' => ['Aquest usuari no existeix.']];

    // verificar reCAPTCHA (si s'envia el token des del formulari)
    $recToken = $_POST['g-recaptcha-response'] ?? null;
    if (empty($recToken)) {
        return ['success' => false, 'errors' => ['ReCAPTCHA requerit. Si us plau, marca la casella i torna-ho a provar.']];
    }
    // verify server-side
    if (!function_exists('verify_recaptcha') || !verify_recaptcha($recToken)) {
        return ['success' => false, 'errors' => ['Verificació reCAPTCHA fallida. Si us plau, torna-ho a provar.']];
    }

    // verificar contrasenya
    $stored = $user['password'];
    $verified = false;

    // intentem verificació amb password_verify si és un hash conegut
    if (!empty($stored) && password_get_info($stored)['algo'] !== 0) {
        if (password_verify($password, $stored)) {
            $verified = true;
        }
    } else {
        // Si no sembla un hash (pot ser text pla a la BD), fem comparació directa
        if ($password === $stored) {
            $verified = true;
            // rehash i actualitza la BD perquè la contrasenya ja no quedi en text pla
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            if ($newHash !== false) {
                update_user_password_hash($user['id'], $newHash);
            }
        }
    }

    if (!$verified) {
        return ['success' => false, 'errors' => ['Contrasenya incorrecta, si us plau intenta-ho de nou.']];
    }

    // Iniciar sessió
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
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
    // If there is a logged user, clear their remember token in DB and cookie
    $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if ($uid) {
        // try to clear token in DB
        if (function_exists('clear_remember_token')) {
            clear_remember_token($uid);
        }
        // clear cookie
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