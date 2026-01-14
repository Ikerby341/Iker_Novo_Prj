<?php
// Incluïm el controlador que conté les funcions de modificació
include_once __DIR__ . '/../Controller/crud_controller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';
$id = null;

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si ve del botó d'iniciar modificació només tindrem 'id' i mostrarem el formulari
    if (isset($_POST['id']) && !isset($_POST['confirm_modify'])) {
        $id = (int)($_POST['id'] ?? 0);
    } elseif (isset($_POST['confirm_modify'])) {
        // Aquest POST és l'enviament final del formulari amb camp i dada nova
        $id = (int)($_POST['id'] ?? 0);
        $camp = $_POST['camp'] ?? '';
        $dadaN = $_POST['dadaN'] ?? '';

        // Validacions bàsiques
        if ($id <= 0) {
            $missatge = 'ID invàlida';
        } elseif (trim($camp) === '') {
            $missatge = 'Camp no pot estar buit';
        } elseif ($camp !== 'ruta_img' && trim($dadaN) === '') {
            // Quan s'actualitza la imatge no cal que dadaN estigui omplert
            $missatge = 'Dada nova no pot estar buida';
        } else {
            // Verificar propietat abans de modificar
                try {
                global $connexio;
                $stmt = $connexio->prepare('SELECT owner_id, ruta_img FROM coches WHERE ID = ? LIMIT 1');
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $missatge = 'Article no trobat';
                } elseif ((int)$row['owner_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
                    $missatge = 'No tens permís per modificar aquest article';
                } else {
                    // Si s'està actualitzant la imatge
                    if ($camp === 'ruta_img') {
                        // Verifiquem que s'ha pujat un fitxer
                        if (!isset($_FILES['imagen_update']) || !is_uploaded_file($_FILES['imagen_update']['tmp_name'])) {
                            $missatge = 'No s\'ha pujat cap imatge.';
                        } else {
                            $file = $_FILES['imagen_update'];
                            if ($file['error'] === UPLOAD_ERR_OK) {
                                $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif', 'image/webp' => '.webp'];
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mime = finfo_file($finfo, $file['tmp_name']);
                                finfo_close($finfo);
                                if (array_key_exists($mime, $allowed)) {
                                    $ext = $allowed[$mime];
                                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                                    $unique = $safeName . '_' . time() . bin2hex(random_bytes(4)) . $ext;
                                    $destDir = realpath(__DIR__ . '/../../public/assets/img');
                                    if ($destDir === false) {
                                        $destDir = __DIR__ . '/../../public/assets/img';
                                        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                                        $destDir = realpath($destDir);
                                    }
                                    $destPath = $destDir . DIRECTORY_SEPARATOR . $unique;
                                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                                        $ruta_db = 'public/assets/img/' . $unique;
                                        // Actualitzar la BD
                                        $missatge = modificarDada($id, 'ruta_img', $ruta_db);
                                        // Si s'ha actualitzat amb èxit, esborrar la imatge anterior si no és la default
                                        if (strpos($missatge, 'correctament') !== false || strpos($missatge, 'actualitzat') !== false) {
                                            $prevRuta = $row['ruta_img'] ?? null;
                                            $default = 'public/assets/img/default.webp';
                                            if (!empty($prevRuta) && $prevRuta !== $default) {
                                                $prevPath = realpath(__DIR__ . '/../../' . $prevRuta);
                                                if ($prevPath && file_exists($prevPath)) {
                                                    @unlink($prevPath);
                                                }
                                            }
                                            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                                            $_SESSION['flash'] = $missatge;
                                            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
                                            exit;
                                        }
                                    } else {
                                        $missatge = 'Error al desar la imatge.';
                                    }
                                } else {
                                    $missatge = 'Tipus de fitxer no permès.';
                                }
                            } else {
                                $missatge = 'Error en la pujada de la imatge.';
                            }
                        }
                    } else {
                        // Actualització d'un camp normal (marca/model)
                        if (trim($camp) === '' || trim($dadaN) === '') {
                            $missatge = 'Camp o dada nova no poden estar buits';
                        } else {
                            $missatge = modificarDada($id, $camp, $dadaN);
                            // Redirigir si la modificació va ser exitosa
                            if (strpos($missatge, 'correctament') !== false || strpos($missatge, 'actualitzat') !== false) {
                                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                                $_SESSION['flash'] = $missatge;
                                header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
                                exit;
                            }
                        }
                    }
                }
            } catch (PDOException $e) {
                $missatge = 'Error a la base de dades: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualitzar article</title>
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
    <h1 style="text-align: center;">Actualitzar article</h1>

    <section class="CRUD-section form-container-adapted">
            <!-- Contenidor per mostrar missatges de resposta -->    
            <?php if (!empty($missatge)): ?>
                <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($missatge); ?></span></div>
            <?php endif; ?>
            <!-- Formulari per modificar dades existents -->
            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Camp per l'ID del registre a modificar -->
                <?php if ($id !== null && $id > 0): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES); ?>">
                    <p>ID: <?php echo htmlspecialchars($id, ENT_QUOTES); ?></p>
                <?php else: ?>
                    <p>Selecciona un article per modificar des de la llista (usa el botó ✏️ al costat de l'article).</p>
                    <p><a style="color: #65a6fc" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">← Volver a la lista</a></p>
                    <?php
                        // No mostramos el formulario si no hay id: salir para evitar que el usuario modifique ID manualmente
                        exit;
                    ?>
                <?php endif; ?>
                <!-- Camp per especificar quin camp es vol modificar -->
                <label for="camp">Camp:</label><br>
                <select type="text" name="camp" id="camp" required>
                    <option value="marca">Marca</option>
                    <option value="model">Model</option>
                    <option value="ruta_img">Imatge</option>
                </select>
                    <!-- Camp per la nova dada que s'inserirà -->
                    <div class="field-dadaN">
                        <label for="dadaN">Dada nova:</label><br>
                        <input type="text" name="dadaN" id="dadaN">
                    </div>

                    <!-- Input per pujar la nova imatge (només s'usa si selecciones 'Imatge') -->
                    <div class="field-imagen" style="display:none;">
                        <label for="imagen_update">Pujar nova imatge:</label><br>
                        <div class="imageinput">
                            <input type="file" name="imagen_update" id="imagen_update" accept="image/*">
                        </div>
                    </div>
                <div class="button-row">
                    <!-- Botó per tornar a la pàgina principal -->
                    <button class="principalBox" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                    <!-- Botó per enviar el formulari -->
                    <button class="principalBox" type="submit" name="confirm_modify" value="1">Modificar ⚙️</button>
                </div>                
            </form>
    </section>
    </div>
    <script>
            (function(){
            const campSel = document.getElementById('camp');
            const fieldDada = document.querySelector('.field-dadaN');
            const fieldImagen = document.querySelector('.field-imagen');
            const dadaInput = document.getElementById('dadaN');
            const imagenInput = document.getElementById('imagen_update');

            function updateVisibility(){
                const v = campSel.value;
                if (v === 'ruta_img') {
                    if(fieldDada) fieldDada.style.display = 'none';
                    if(fieldImagen) fieldImagen.style.display = 'block';
                    if(imagenInput) imagenInput.required = true;
                    if(dadaInput) dadaInput.required = false;
                } else {
                    if(fieldDada) fieldDada.style.display = 'block';
                    if(fieldImagen) fieldImagen.style.display = 'none';
                    if(imagenInput) imagenInput.required = false;
                    if(dadaInput) dadaInput.required = true;
                }
            }

            // initialize
            if(campSel){
                campSel.addEventListener('change', updateVisibility);
                updateVisibility();
            }
        })();
    </script>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
        </div>
    </footer>
</body>
</html>
