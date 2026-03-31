<?php
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../Controller/controlador.php';
include_once __DIR__ . '/../Controller/crud_controller.php';

$missatge = '';
$marca_generada = '';
$model_generat = '';

// Comprovem si s'ha demanat generar vehicle aleatori (POST)
if (isset($_POST['generate'])) {
    $vehicle = get_random_vehicle_data();
    if ($vehicle) {
        $marca_generada = $vehicle['marca'];
        $model_generat = $vehicle['model'];
    }
}

// Comprovem si s'ha enviat el formulari d'inserció (POST, sense generate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['generate'])) {
    $titol = $_POST['titol'] ?? '';
    $cos   = $_POST['cos']   ?? '';
    $image_file = $_FILES['imagen'] ?? null;

    $missatge = process_create_article($titol, $cos, $image_file);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear article</title>
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1 style="color: #ffffff;">
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a>
            </h1>
        </div>
    </header>
    <div class="site-content">
        <h1 style="text-align: center;">Crear article</h1>
        <section class="CRUD-section form-container-adapted">
            <?php if (!empty($missatge)): ?>
                <div class="form-info">
                    <span class="info-icon">ℹ️</span>
                    <span class="info-text"><?php echo htmlspecialchars($missatge); ?></span>
                </div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="imagen">Imatge (opcional):</label><br>
                <div class="imageinput">
                    <input type="file" name="imagen" id="imagen" accept="image/*">
                </div>
                <label for="titol">Marca:</label><br>
                <input type="text" name="titol" id="titol" value="<?php echo htmlspecialchars($marca_generada); ?>" ><br>
                <label for="cos">Model:</label><br>
                <input type="text" name="cos" id="cos" value="<?php echo htmlspecialchars($model_generat); ?>" ><br>
                <!-- Ara envia el formulari per POST amb name="generate" -->
                <button type="submit" name="generate" value="1" class="principalBox">Generar Vehicle Aleatori des de API</button><br><br>
                <div class="button-row">
                    <button type="button" class="principalBox" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                    <button type="submit" class="principalBox">Insertar ⛳</button>
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