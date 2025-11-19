<?php
// Procesa formularis de login i registre
include_once __DIR__ . '/controlador.php';

// Si no és POST, redirigim
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /practiques/backend/Iker_Novo_PrJ/');
    exit;
}

// Distingeix registre vs login per la presència de passwordC o email
if (isset($_POST['passwordC']) || isset($_POST['email'])) {
    // registre
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['passwordC'] ?? '';

    $res = register_user($username, $email, $password, $password_confirm);
    if ($res['success']) {
        // redirigim al login amb missatge opcional
        header('Location: /practiques/backend/Iker_Novo_PrJ/');
        exit;
    } else {
        // Guardem errors a session i tornem a la pàgina de registre
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['form_errors'] = $res['errors'];
        $_SESSION['old'] = ['username' => $username, 'email' => $email];
        header('Location: /practiques/backend/Iker_Novo_PrJ/');
        exit;
    }
} else {
    // login
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = login_user($username, $password);
    if ($res['success']) {
        // exitoso: redirigimos a la vista principal
        header('Location: /practiques/backend/Iker_Novo_PrJ/');
        exit;
    } else {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['form_errors'] = $res['errors'];
        header('Location: /practiques/backend/Iker_Novo_PrJ/');
        exit;
    }
}
