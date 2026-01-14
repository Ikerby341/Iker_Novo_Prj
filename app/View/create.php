<?php
// Incloure el controlador que conté les funcions de creació
include_once __DIR__ . '/../Controller/crud_controller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenim les dades del formulari
    $titol = $_POST['titol'] ?? '';
    $cos = $_POST['cos'] ?? '';

    // Processar la imatge si s'ha pujat
    $ruta_db = null; // per defecte no passem ruta i deixem la BD aplicar el default
    if (isset($_FILES['imagen']) && isset($_FILES['imagen']['tmp_name']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
        $file = $_FILES['imagen'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif', 'image/webp' => '.webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (array_key_exists($mime, $allowed)) {
                $ext = $allowed[$mime];
                // Generar nom segur per a la imatge
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $unique = $safeName . '_' . time() . bin2hex(random_bytes(4)) . $ext;
                // Destí: carpeta public/assets/img
                $destDir = realpath(__DIR__ . '/../../public/assets/img');
                if ($destDir === false) {
                    // crear carpeta si no existeix
                    $destDir = __DIR__ . '/../../public/assets/img';
                    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                    $destDir = realpath($destDir);
                }
                $destPath = $destDir . DIRECTORY_SEPARATOR . $unique;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    // Guardem la ruta tal com vols: començar per public/
                    $ruta_db = 'public/assets/img/' . $unique;
                }
            }
        }
    }

    // Cridem a la funció per inserir les dades i guardem el resultat
    $missatge = inserirDada($titol, $cos, $ruta_db);
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
