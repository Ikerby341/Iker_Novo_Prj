<?php

/**
 * captcha_controller.php
 * Gestiona la verificació de Google reCAPTCHA v2 (servidor)
 */

/**
 * verify_recaptcha
 * Verifica el token de Google reCAPTCHA (capa servidor).
 * Retorna true si la verificació és correcta.
 */
function verify_recaptcha($token) {
    $secret = getenv('RECAPTCHA_SECRET');
    if (empty($secret)) return false;

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = http_build_query([
        'secret' => $secret,
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

?>
