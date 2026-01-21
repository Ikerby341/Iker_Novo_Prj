<?php
    include_once __DIR__ . '/../../config/app.php';
    include_once __DIR__ . '/../Controller/controlador.php';
    include_once __DIR__ . '/../Controller/crud_controller.php';

    // Obtenir email actual per comparacions
    $currentEmail = '';
    if (isset($_SESSION['username']) && function_exists('get_user_by_username')) {
        $uinfo = get_user_by_username($_SESSION['username']);
        if ($uinfo && isset($uinfo['email'])) $currentEmail = $uinfo['email'];
    }

    $edit_msg = '';

    // Procesar POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $uid = $_SESSION['user_id'] ?? null;
        $newName = trim($_POST['pfname'] ?? '');
        $newEmail = trim($_POST['pfemail'] ?? '');

        // Cridem a la funció del controlador
        $result = process_edit_profile($uid, $newName, $newEmail);
        $edit_msg = implode(' ', $result['messages']);
        $currentEmail = $result['updated_data']['email'] ?? $currentEmail;
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
    <div class="site-content">
    <h1 style="text-align: center;">Editar perfil</h1>
    <section class="CRUD-section form-container-adapted">
        <?php if (!empty($edit_msg)): ?>
            <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($edit_msg); ?></span></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="pfname">Nom de perfil:</label><br>
            <input type="text" name="pfname" id="pfname" required value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>"><br>
            <label for="pfemail">Correu electrònic:</label><br>
            <input type="email" name="pfemail" id="pfemail" value="<?php echo htmlspecialchars($currentEmail ?? ''); ?>"><br>
            <div class="button-row">
                <!-- Botó per tornar a la pàgina principal -->
                <button class="principalBox" type="button" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                <!-- Botó per enviar el formulari -->
                <button class="principalBox" type="submit">Editar ✏️</button>
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