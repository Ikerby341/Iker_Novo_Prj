<?php
// Incloure el controlador que conté les funcions de creació
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../Controller/controlador.php';
include_once __DIR__ . '/../Controller/crud_controller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titol = $_POST['titol'] ?? '';
    $cos = $_POST['cos'] ?? '';
    $image_file = $_FILES['imagen'] ?? null;

    // Cridem a la funció del controlador
    $missatge = process_create_article($titol, $cos, $image_file);
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
    <h1 style="text-align: center;">Crear article</h1>
    <section class="CRUD-section form-container-adapted">
        <!-- Contenidor per mostrar missatges de resposta -->
        <?php if (!empty($missatge)): ?>
            <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($missatge); ?></span></div>
        <?php endif; ?>
        <!-- Formulari per inserir noves dades -->
        <form method="POST" action="" enctype="multipart/form-data">
            <!-- Camp per pujar imatge -->
            <label for="imagen">Imatge (opcional):</label><br>
            <div class="imageinput">
                <input type="file" name="imagen" id="imagen" accept="image/*">
            </div>
            <!-- Camp pel títol -->
            <label for="titol">Marca:</label><br>
            <input type="text" name="titol" id="titol" required><br>
            <!-- Camp pel cos del missatge -->
            <label for="cos">Model:</label><br>
            <input type="text" name="cos" id="cos" required><br>
            <div class="button-row">
                <!-- Botó per tornar a la pàgina principal -->
                <button class="principalBox" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                <!-- Botó per enviar el formulari -->
                <button class="principalBox" type="submit">Insertar ⛳</button>
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
