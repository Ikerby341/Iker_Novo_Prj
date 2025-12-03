<?php
    include_once __DIR__ . '/../Controller/controlador.php';
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
        <form method="POST" action="">
            <label for="pfname">Nom de perfil:</label><br>
            <input type="text" name="pfname" id="pfname" required><br>
            <div class="button-row">
                <!-- Botó per tornar a la pàgina principal -->
                <button class="box" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
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