<?php
/**
 * users_controller.php
 * Gestiona l'administració d'usuaris: llistar, editar (admin), esborrar
 */

include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/session_controller.php';
include_once __DIR__ . '/../Model/users_model.php';

include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/session_controller.php';
include_once __DIR__ . '/../Model/users_model.php';

// Solo ejecutar si se accede directamente
if (__FILE__ === $_SERVER['SCRIPT_FILENAME']) {
    // Verificar que el usuario esté logueado y sea admin
    if (!is_logged_in() || !is_admin()) {
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
        exit;
    }

    // Processar accions POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $user_id = (int)($_POST['user_id'] ?? 0);

        if ($action === 'delete' && $user_id > 0) {
            // Esborrar usuari
            if (delete_user($user_id)) {
                $_SESSION['message'] = 'Usuari eliminat correctament.';
            } else {
                $_SESSION['error'] = 'Error a l\'esborrar l\'usuari.';
            }
        } elseif ($action === 'toggle_admin' && $user_id > 0) {
            // Cambiar estado admin
            $current_admin = (bool)($_POST['current_admin'] ?? false);
            $new_admin = !$current_admin;
            if (update_user_admin($user_id, $new_admin)) {
                $_SESSION['message'] = 'Estat admin actualitzat.';
            } else {
                $_SESSION['error'] = 'Error en actualitzar l\'estat d\'admin.';
            }
        }

        // Redirigir para evitar reenvío de formulario
        header('Location: ' . (defined('BASE_URL') ? BASE_URL . 'app/View/admin.php' : '/app/View/admin.php'));
        exit;
    }

    // Obtener todos los usuarios
    $users = get_all_users();

    // Incluir la vista
    include_once __DIR__ . '/../View/admin.php';
}
?>