<?php
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../Controller/controlador.php';
include_once __DIR__ . '/../Controller/crud_controller.php';

$missatge = '';
$marca_generada = '';
$model_generat = '';

function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Comprovem si s'ha demanat generar vehicle aleatori (POST)
if (isset($_POST['generate'])) {
    $vehicle = get_random_vehicle_data();
    if ($vehicle) {
        $marca_generada = $vehicle['marca'];
        $model_generat = $vehicle['model'];
    }

    if (is_ajax_request()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => (bool)$vehicle,
            'marca' => $marca_generada,
            'model' => $model_generat,
            'message' => $vehicle ? 'Vehicle generat correctament.' : 'No s\'ha pogut generar el vehicle. Torna a provar-ho.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
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
                <button type="submit" id="generateVehicleBtn" name="generate" value="1" class="principalBox">Generar Vehicle Aleatori des de API</button><br><br>
                <div id="generateMessage" class="form-info" style="display:none;">
                    <span class="info-icon">ℹ️</span>
                    <span class="info-text" id="generateMessageText"></span>
                </div>
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
    <script>
        (function() {
            var generateBtn = document.getElementById('generateVehicleBtn');
            var marcaInput = document.getElementById('titol');
            var modelInput = document.getElementById('cos');
            var messageContainer = document.getElementById('generateMessage');
            var messageText = document.getElementById('generateMessageText');
            var form = generateBtn ? generateBtn.closest('form') : null;

            function showMessage(text, isError) {
                if (!messageContainer || !messageText) return;
                messageText.textContent = text;
                messageContainer.style.display = 'block';
                messageContainer.classList.toggle('form-error', !!isError);
                messageContainer.classList.toggle('form-info', !isError);
            }

            function clearMessage() {
                if (!messageContainer || !messageText) return;
                messageText.textContent = '';
                messageContainer.style.display = 'none';
                messageContainer.classList.remove('form-error');
            }

            if (generateBtn && form) {
                generateBtn.addEventListener('click', function(event) {
                    if (!window.fetch) {
                        return; // si no hay fetch, dejamos el submit normal
                    }

                    event.preventDefault();
                    clearMessage();
                    generateBtn.disabled = true;
                    generateBtn.textContent = 'Generant...';

                    var formData = new FormData();
                    formData.append('generate', '1');

                    fetch('', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        if (data && data.success) {
                            if (marcaInput) marcaInput.value = data.marca || '';
                            if (modelInput) modelInput.value = data.model || '';
                            showMessage(data.message || 'Vehicle generat correctament.', false);
                        } else {
                            showMessage(data.message || 'No s\'ha pogut generar el vehicle.', true);
                        }
                    }).catch(function() {
                        showMessage('Error de xarxa. Torna a provar-ho.', true);
                    }).finally(function() {
                        generateBtn.disabled = false;
                        generateBtn.textContent = 'Generar Vehicle Aleatori des de API';
                    });
                });
            }
        })();
    </script>
</body>
</html>