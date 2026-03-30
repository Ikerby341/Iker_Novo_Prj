<?php
    include_once __DIR__ . '/../../config/app.php';
    include_once __DIR__ . '/../Controller/controlador.php';
    include_once __DIR__ . '/../Controller/crud_controller.php';

    // Verificar que sea admin
    if (!is_logged_in() || !is_admin()) {
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
        exit;
    }

    // Obtener user_id de GET
    $user_id = (int)($_GET['user_id'] ?? 0);

    // Obtenir dades de la pàgina edit_user
    $edit_data = edit_user_page_controller($user_id);
    $user = $edit_data['user'];
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuari</title>
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
        </div>
    </header>
    <div class="site-content">
    <h1 style="text-align: center;">Editar Usuari: <?php echo htmlspecialchars($user['username']); ?></h1>
    <section class="CRUD-section form-container-adapted">
        <form method="post" action="">
            <label for="username">Nom d'usuari:</label><br>
            <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($user['username']); ?>"><br>
            <label for="email">Correu electrònic:</label><br>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br>
            <label for="admin">Admin:</label><br>
            <select name="admin" id="admin">
                <option value="0" <?php echo $user['admin'] ? '' : 'selected'; ?>>No</option>
                <option value="1" <?php echo $user['admin'] ? 'selected' : ''; ?>>Sí</option>
            </select><br><br>
            <div class="button-row">
                <button class="principalBox" type="button" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL . 'app/View/admin.php' : '/app/View/admin.php'); ?>';">← Tornar enrere</button>
                <button class="principalBox" type="submit">Guardar Canvis ✏️</button>
            </div>
        </form>
    </section>
    </div>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
        </div>
    </footer>
</body>
</html>