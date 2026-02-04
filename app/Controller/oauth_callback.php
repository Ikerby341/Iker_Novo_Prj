<?php
require_once __DIR__ . '/../../config/mailer.php'; // Load env
require_once __DIR__ . '/../Model/users_model.php';
require_once __DIR__ . '/auth_controller.php'; // Per a les funcions d'inici de sessió
require_once 'session_controller.php'; // Assuming session handling

$client_id = getenv('DISCORD_CLIENT_ID');
$client_secret = getenv('DISCORD_CLIENT_SECRET');
$redirect_uri = getenv('DISCORD_REDIRECT_URI');

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Intercanviar codi per token
    $token_url = 'https://discord.com/api/oauth2/token';
    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect_uri
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];

        // Obtenir informació de l'usuari
        $user_url = 'https://discord.com/api/users/@me';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);

        $user_response = curl_exec($ch);
        curl_close($ch);

        $user_data = json_decode($user_response, true);

        if (isset($user_data['id'])) {
            $discord_id = $user_data['id'];
            $discord_username = $user_data['username'];
            $email = $user_data['email'] ?? null;

            // Comprovar si l'usuari existeix
            $user = get_user_by_discord_id($discord_id);
            if ($user) {
                // Login
                login_user_oauth($user['id']);
                header('Location: /Practiques/Backend/Iker_Novo_Prj/app/View/vista.php');
                exit;
            } else {
                // Registre: redirigir al registre amb les dades de Discord
                session_start();
                $_SESSION['discord_data'] = [
                    'discord_id' => $discord_id,
                    'username' => $discord_username,
                    'email' => $email
                ];
                header('Location: /Practiques/Backend/Iker_Novo_Prj/app/View/register_discord.php');
                exit;
            }
        }
    }
}

// Error
header('Location: /Practiques/Backend/Iker_Novo_Prj/app/View/login.php?error=oauth_failed');
exit;
?>