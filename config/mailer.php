<?php

/**
 * mailer.php
 * Configuració per a l'enviament de correus
 */

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

return [
    'smtp' => [
        'host' => getenv('SMTP_HOST'),           // Servidor SMTP
        'port' => (int) getenv('SMTP_PORT'),     // Port SMTP
        'encryption' => getenv('SMTP_ENCRYPTION'), // Encriptació: 'tls' o 'ssl'
    ],
    'auth' => [
        'username' => getenv('SMTP_USERNAME'),  // Correu electrònic (cambiar)
        'password' => getenv('SMTP_PASSWORD'),  // Contrasenya d'aplicació (cambiar)
    ],
    'from' => [
        'email' => getenv('FROM_EMAIL'),         // Correu electrònic del remitent (cambiar)
        'name' => getenv('FROM_NAME'),           // Nom de remitent
    ],
    'password_reset' => [
        'subject' => getenv('PASSWORD_RESET_SUBJECT'),
        'token_expiration' => getenv('TOKEN_EXPIRATION'), // Durada del token
    ]
];

?>
