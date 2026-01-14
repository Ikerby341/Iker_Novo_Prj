<?php
    // Incloure el controlador al principi per permetre redireccions amb headers
    include_once __DIR__ . '/../Controller/controlador.php';
    // Validar i possiblement redirigir si la pagina sol.licitada no existeix
    validar_pagina_solicitada();

    // Processar accions POST si és admin
    if (is_logged_in() && is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $user_id = (int)($_POST['user_id'] ?? 0);

        if ($action === 'delete' && $user_id > 0) {
            // Esborrar usuari
            if (delete_user($user_id)) {
                $_SESSION['message'] = 'Usuari esborrat correctament.';
            } else {
                $_SESSION['error'] = 'Error a l\'esborrar l\'usuari.';
            }
        }

        // Redirigir para evitar reenvío de formulario
        header('Location: ' . (defined('BASE_URL') ? BASE_URL . 'app/View/admin.php' : '/app/View/admin.php'));
        exit;
    }
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projecte Iker Novo</title>
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
        </div>
    </header>
    <?php if (is_admin()): ?>
        <div class="site-content">
        <!-- Títol principal de la pàgina -->
        <h1 style="text-align: center;">Gestionar usuaris</h1>
        <section class="CRUD-section form-container-adapted">
                <!-- Contenidor per mostrar missatges de resposta -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></span></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="form-info"><span class="info-icon">⚠️</span><span class="info-text"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span></div>
                <?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom d'usuari</th>
                            <th>Correu electrònic</th>
                            <th>Admin</th>
                            <th>Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = get_all_users();
                        $current_user_id = $_SESSION['user_id'] ?? null;
                        $filtered_users = array_filter($users, function($user) use ($current_user_id) {
                            return $user['id'] != $current_user_id;
                        });
                        foreach ($filtered_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['admin'] ? 'Sí' : 'No'; ?></td>
                                <td>
                                    <a href="edit_user.php?user_id=<?php echo $user['id']; ?>"><button style="background-color: #2196F3; color: white;">Editar</button></a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('¿Esborrar usuari?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="action" value="delete">Esborrar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="button-row">
                    <!-- Botó per tornar a la pàgina principal -->
                    <button class="principalBox" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                </div>
        </section>
        </div>
    <?php else: ?>
        <div class="site-content">
            <h2 style="text-align: center; color: red;">Accés denegat. No tens permisos d'administrador.</h2>
        </div>
    <?php endif; ?>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
        </div>
    </footer>
</body>
