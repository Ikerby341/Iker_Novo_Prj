<?php
    // Incluïm la configuració i el controlador
    include_once __DIR__ . '/../../config/app.php';
    include_once __DIR__ . '/../Controller/controlador.php';

    // Inicialitzem les variables
    $missatge = '';

    // Comprovem si l'usuari està autenticat
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . 'index.php?action=login');
        exit;
    }

    // Comprovem si s'ha enviat el formulari (mètode POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Cridem la funció del controlador
        $result = change_password($_SESSION['user_id'], $current_password, $new_password, $confirm_password);
        $missatge = $result['message'];

        // Si l'actualització va bé, redirigim després de 2 segons
        if ($result['success']) {
            header('Refresh: 2; url=' . (defined('BASE_URL') ? BASE_URL : '/'));
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canviar contrasenya</title>
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
        </div>
    </header>
    <div class="site-content">
    <!-- Títol principal de la pàgina -->
    <h1 style="text-align: center;">Canviar contrasenya</h1>

    <section class="CRUD-section form-container-adapted">
            <!-- Contenidor per mostrar missatges de resposta -->    
            <?php if (!empty($missatge)): ?>
                <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($missatge); ?></span></div>
            <?php endif; ?>
            
            <!-- Formulari per canviar contrasenya -->
            <form method="POST" action="">
                <!-- Camp per la contrasenya actual -->
                <label for="current_password">Contrasenya actual:</label><br>
                <input type="password" name="current_password" id="current_password" required><br>

                <!-- Camp per la nova contrasenya -->
                <label for="new_password">Nova contrasenya:</label><br>
                <input type="password" name="new_password" id="new_password" required><br>

                <!-- Camp per confirmar la nova contrasenya -->
                <label for="confirm_password">Confirmar contrasenya:</label><br>
                <input type="password" name="confirm_password" id="confirm_password" required><br>

                <div class="button-row">
                    <!-- Botó per tornar a la pàgina principal -->
                    <button class="principalBox" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';" type="button">← Tornar enrere</button>
                    <!-- Botó per enviar el formulari -->
                    <button class="principalBox" type="submit">Canviar contrasenya</button>
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
