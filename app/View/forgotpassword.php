<?php
// Incloure el controlador que conté les funcions de creació
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../Controller/controlador.php';
include_once __DIR__ . '/../Controller/auth_controller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // Cridem a la funció del controlador
    $result = process_forgot_password($email);
    $missatge = $result['message'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear article</title>
    <!-- Enllaç als estils CSS -->
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
    <h1 style="text-align: center;">Recuperar contrasenya</h1>
    <section class="CRUD-section form-container-adapted">
        <h2 style="margin-top: 0; margin-bottom: 0px;">Has oblidat la teva contrasenya?</h2>
        <p style="font-size: 14px;">Introdueix el teu correu electrònic per rebre un enllaç de recuperació de contrasenya</p>
        <!-- Contenidor per mostrar missatges de resposta -->
        <?php if (!empty($missatge)): ?>
            <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($missatge); ?></span></div>
        <?php endif; ?>
        <!-- Formulari per inserir noves dades -->
        <form method="POST" action="">
            <!-- Camp per l'email -->
            <label for="email">Correu electrònic:</label><br>
            <input type="email" name="email" id="email" required><br>
            <div class="button-row">
                <!-- Botó per tornar a la pàgina principal -->
                <button class="principalBox" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/login.php';">← Tornar enrere</button>
                <!-- Botó per enviar el formulari -->
                <button type="submit" class="principalBox">Enviar correu de recuperació</button>
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