<?php

/**
 * phpmailer.php
 * Configuració de PHPMailer per a l'enviament de correus
 */

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',           // Servidor SMTP
        'port' => 587,                         // Port SMTP
        'encryption' => 'tls',                 // Encriptació: 'tls' o 'ssl'
    ],
    'auth' => [
        'username' => 'i.novo@sapalomera.cat',  // Correu electrònic (cambiar)
        'password' => 'atgl tmkv dgld kavo',     // Contrasenya d'aplicació (cambiar)
    ],
    'from' => [
        'email' => 'i.novo@sapalomera.cat',     // Correu electrònic del remitent (cambiar)
        'name' => 'GUARCAR',                   // Nom de remitent
    ],
    'password_reset' => [
        'subject' => 'Recuperació de contrasenya - GUARCAR',
        'token_expiration' => '+1 hour',       // Durada del token
    ]
];

?>
