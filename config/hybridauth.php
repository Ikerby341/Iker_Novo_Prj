<?php
// Càrrega de .env i retorn de la configuració mínima per Hybridauth
require_once __DIR__ . '/mailer.php'; // carrega .env via putenv()

return [
    'callback' => getenv('GITHUB_REDIRECT_URI') ?: 'http://localhost/Practiques/Backend/Iker_Novo_Prj/public/index.php?action=github_callback',
    'providers' => [
        'GitHub' => [
            'enabled' => true,
            'keys' => [
                'id' => getenv('GITHUB_CLIENT_ID') ?: '',
                'secret' => getenv('GITHUB_CLIENT_SECRET') ?: ''
            ],
            'scope' => 'user:email'
        ]
    ],
    'debug_mode' => false,
    'debug_file' => __DIR__ . '/../hybridauth.log',
];

?>
