<?php
// Incloem el controlador que inicia la sessió i el model
include_once __DIR__ . '/../Controller/controlador.php';
include_once __DIR__ . '/../Controller/crud_controller.php';

// Protegim l'accés: aquesta pàgina només es pot accedir via POST per esborrar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirigim a la vista principal (no permetem GET)
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
    exit;
}

// Ara processem la petició POST
// Verifiquem que l'usuari està identificat
    if (!is_logged_in()) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL . 'app/View/login.php' : '/app/View/login.php'));
    exit;
}

$missatge = '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $missatge = 'ID invàlida';
    $_SESSION['flash'] = $missatge;
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
    exit;
}

// Verifiquem que l'article pertany a l'usuari abans d'esborrar
    try {
    global $connexio;
    $stmt = $connexio->prepare('SELECT owner_id FROM coches WHERE ID = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $missatge = 'Article no trobat';
        $_SESSION['flash'] = $missatge;
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
        exit;
    }
    if ((int)$row['owner_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
        $missatge = 'No tens permís per esborrar aquest article';
        $_SESSION['flash'] = $missatge;
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
        exit;
    }

    // Esborrar
    $missatge = esborrarDada($id);
    // Guardar missatge en sessió i redirigir a la vista principal
    $_SESSION['flash'] = $missatge;
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
    exit;
} catch (PDOException $e) {
    $_SESSION['flash'] = 'Error en la base de dades: ' . $e->getMessage();
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
    exit;
}
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
            <div class="footer-small">Gràcies per visitar · 2025</div>
        </div>
    </footer>
</body>
</html>