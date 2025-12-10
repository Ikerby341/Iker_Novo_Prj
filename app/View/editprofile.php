<?php
    include_once __DIR__ . '/../Controller/controlador.php';

    // Processem el POST per actualitzar el nom d'usuari i l'email
    $edit_msg = '';
    // obtenir email actual per comparacions
    $currentEmail = '';
    if (isset($_SESSION['username']) && function_exists('get_user_by_username')) {
        $uinfo = get_user_by_username($_SESSION['username']);
        if ($uinfo && isset($uinfo['email'])) $currentEmail = $uinfo['email'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Assegurar que hi ha sessió i user_id
        $uid = $_SESSION['user_id'] ?? null;
        $newName = trim($_POST['pfname'] ?? '');
        $newEmail = trim($_POST['pfemail'] ?? '');
        $msgs = [];
        if (!$uid) {
            $msgs[] = 'ID d\'usuari no disponible.';
        } else {
            // Incloem el controlador CRUD per tenir accés a les funcions de modificació
            include_once __DIR__ . '/../Controller/crud_controller.php';

            // Username: si ha canviat, validar unicitat
            $currentName = $_SESSION['username'] ?? '';
            if ($newName !== '' && $newName !== $currentName) {
                if (function_exists('user_exists_by_username') && user_exists_by_username($newName)) {
                    $msgs[] = 'Aquest nom d\'usuari ja existeix. Tria un altre.';
                } else {
                    $r1 = modificarUsername($uid, $newName);
                    if ($r1) {
                        $msgs[] = 'Nom d\'usuari actualitzat correctament.';
                        $_SESSION['username'] = $newName;
                    } else {
                        $msgs[] = 'Error al actualitzar el nom d\'usuari.';
                    }
                }
            }

            // Email: si ha canviat, actualitzar (pot ser repetit)
            if ($newEmail !== '' && $newEmail !== $currentEmail) {
                $r2 = modificarEmail($uid, $newEmail);
                if ($r2) {
                    $msgs[] = 'Email actualitzat correctament.';
                } else {
                    $msgs[] = 'Error al actualitzar l\'email.';
                }
            }
        }

        if (empty($msgs)) {
            $edit_msg = 'No s\'ha realitzat cap canvi.';
        } else {
            $edit_msg = implode(' ', $msgs);
        }
        // Actualitzar currentEmail per a la vista immediata
        if (isset($newEmail) && $newEmail !== '') $currentEmail = $newEmail;
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
        <div class="header-inner">
            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>" class="menu">🏠 Home</a>
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
                <button class="box" type="button" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                <!-- Botó per enviar el formulari -->
                <button class="principalBox" type="submit">Editar ✏️</button>
            </div>
        </form>
    </section>
    </div>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · 2025</div>
        </div>
    </footer>
</body>
</html>