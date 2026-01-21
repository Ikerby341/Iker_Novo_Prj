<?php
// Incloem el controlador que inicia la sessió i el model
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../Controller/controlador.php';
include_once __DIR__ . '/../Controller/crud_controller.php';

// Protegim l'accés: aquesta pàgina només es pot accedir via POST per esborrar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
    exit;
}

// Verificar que l'usuari està identificat
if (!is_logged_in()) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL . 'app/View/login.php' : '/app/View/login.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$result = process_delete_article($id);

// Guardar missatge en sessió i redirigir a la vista principal
$_SESSION['flash'] = $result['message'];
header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
exit;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esborrar article</title>
    <!-- Enllaç als estils CSS -->
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <div class="site-content">
    <!-- Títol principal de la pàgina -->
    <h1>Esborrar article</h1>

    <!-- Formulari per esborrar dades -->
    <form method="POST" action="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">
        <!-- Camp per l'ID del registre a esborrar -->
        <label hidden>Digues una ID per eliminar:</label><br>
        <input type="number" name="id" style="width: 50px; text-align: center;" required hidden>
        <!-- Botó per enviar el formulari -->
        <button class="principalBox" type="submit" style="width: auto;">Esborrar 🗑️</button>
    </form>

    <!-- Contenidor per mostrar missatges de resposta -->
    <div>
        <?php echo $missatge; ?>
    </div>

    <!-- Botó per tornar a la pàgina principal -->
    <button class="box" style="width: auto;" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
    </div>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
        </div>
    </footer>
</body>
</html>