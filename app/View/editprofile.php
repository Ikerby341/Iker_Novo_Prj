<?php
    include_once __DIR__ . '/../../config/app.php';
    include_once __DIR__ . '/../Controller/controlador.php';
    include_once __DIR__ . '/../Controller/crud_controller.php';

    // Obtenir email actual per comparacions
    $currentEmail = '';
    $isApiKeyGenerated = false;
    if (isset($_SESSION['username']) && function_exists('get_user_by_username')) {
        $uinfo = get_user_by_username($_SESSION['username']);
        if ($uinfo && isset($uinfo['email'])) $currentEmail = $uinfo['email'];
        $isApiKeyGenerated = !empty($uinfo['api_key']);
    }

    $edit_msg = '';

    function is_ajax_request() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    // Procesar POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['generate'])) {
            // Generar nova API key
            $uid = $_SESSION['user_id'] ?? null;
            if ($uid) {
                $result = regenerate_api_key($uid);
                $edit_msg = implode(' ', $result['messages']);
                if (isset($result['new_api_key'])) {
                    $uinfo['api_key'] = $result['new_api_key'];
                }
            } else {
                $edit_msg = 'Usuari no identificat.';
            }

            if (is_ajax_request()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => isset($result['success']) ? (bool)$result['success'] : false,
                    'new_api_key' => $result['new_api_key'] ?? null,
                    'message' => $edit_msg,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } else {
            // Si és un POST normal, processem l'edició del perfil
            $uid = $_SESSION['user_id'] ?? null;
            $newName = trim($_POST['pfname'] ?? '');
            $newEmail = trim($_POST['pfemail'] ?? '');

            // Cridem a la funció del controlador
            $result = process_edit_profile($uid, $newName, $newEmail);
            $edit_msg = implode(' ', $result['messages']);
            $currentEmail = $result['updated_data']['email'] ?? $currentEmail;
        }
    }
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
        <div class="header-container">
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
        </div>
    </header>
    <div class="site-content">
    <h1 style="text-align: center;">Editar perfil</h1>
    <section class="CRUD-section form-container-adapted">
        <?php if (!empty($edit_msg)): ?>
            <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($edit_msg); ?></span></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="pfname">Nom de perfil:</label><br>
            <input type="text" name="pfname" id="pfname" required value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>"><br>
            <label for="pfemail">Correu electrònic:</label><br>
            <input type="email" name="pfemail" id="pfemail" value="<?php echo htmlspecialchars($currentEmail ?? ''); ?>"><br>
            <label for="pfapikey">API key (no es pot editar):</label><br>
            <input type="text" name="pfapikey" id="pfapikey" value="<?php echo $isApiKeyGenerated ? 'Generada' : ''; ?>" readonly>
            <button class="principalBox" type="submit" id="generateApiKeyBtn" name="generate" value="1">Generar nova API key</button><br><br>
            <div id="apiKeyMessage" class="form-info" style="display:none;">
                <span class="info-icon">ℹ️</span>
                <span class="info-text" id="apiKeyMessageText"></span>
            </div>
            <div class="button-row">
                <!-- Botó per tornar a la pàgina principal -->
                <button class="principalBox" type="button" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                <!-- Botó per enviar el formulari -->
                <button class="principalBox" type="submit">Editar ✏️</button>
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
            var generateBtn = document.getElementById('generateApiKeyBtn');
            var apiKeyInput = document.getElementById('pfapikey');
            var messageContainer = document.getElementById('apiKeyMessage');
            var messageText = document.getElementById('apiKeyMessageText');
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
                            if (apiKeyInput) apiKeyInput.value = data.new_api_key || '';
                            showMessage(data.message || 'API key regenerada correctament.', false);
                        } else {
                            showMessage(data.message || 'No s\'ha pogut regenerar la API key.', true);
                        }
                    }).catch(function() {
                        showMessage('Error de xarxa. Torna a provar-ho.', true);
                    }).finally(function() {
                        generateBtn.disabled = false;
                        generateBtn.textContent = 'Generar nova API key';
                    });
                });
            }
        })();
    </script>
</body>
</html>