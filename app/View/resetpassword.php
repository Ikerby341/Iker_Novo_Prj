<?php
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../Controller/controlador.php';
include_once __DIR__ . '/../Controller/crud_controller.php';
include_once __DIR__ . '/../Controller/auth_controller.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$message = '';
$error = '';
$token_valid = false;

// Validar el token
if (!empty($token)) {
    $user_id = validate_reset_token_controller($token);
    if ($user_id) {
        $token_valid = true;
    } else {
        $error = 'L\'enllaç de recuperació és invàlid o ha caducat.';
    }
}

// Procesar el formulari si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Si us plau, omple tots els camps.';
    } elseif (strlen($new_password) < 6) {
        $error = 'La contrasenya ha de tenir almenys 6 caràcters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Les contrasenyes no coincideixen.';
    } else {
        // Actualitzar la contrasenya
        if (reset_password_controller($token, $new_password)) {
            $message = 'Contrasenya restablerta correctament! Ja pots iniciar sessió amb la teva nova contrasenya.';
            $token_valid = false;
        } else {
            $error = 'Error en restablir la contrasenya.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablir contrasenya - GUARCAR</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
        </div>
    </header>
    <div class="site-content">
    <section class="form-container-adapted">
        <h1 style="text-align: center; margin-bottom: 30px;">Restablir contrasenya</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <div style="margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>app/View/login.php" class="btn btn-primary" style="display: inline-block; width: auto;">Anar a iniciar sessió</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <div style="margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>app/View/login.php" class="btn btn-primary" style="display: inline-block; width: auto;">Tornar al login</a>
            </div>
        <?php endif; ?>
        
        <?php if ($token_valid && empty($message)): ?>
            <form method="POST">
                <label for="new_password">Nova contrasenya:</label>
                <div class="password-container">
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="button" class="toggle-password" data-target="new_password">👁️</button>
                </div>
                
                <label for="confirm_password">Confirma la contrasenya:</label>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="toggle-password" data-target="confirm_password">👁️</button>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Restablir contrasenya</button>
                </div>
            </form>
        <?php endif; ?>
        
        <?php if (!$token_valid && empty($message) && empty($error)): ?>
            <div class="alert alert-error">
                Si us plau, accedeix al formulari correctament amb l'enllaç rebut al correu.
            </div>
            <div style="margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>app/View/login.php" class="btn btn-primary" style="display: inline-block; width: auto;">Tornar al login</a>
            </div>
        <?php endif; ?>
    </section>
    </div>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
        </div>
    </footer>
    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-password').forEach(function(button) {
                button.addEventListener('click', function() {
                    var targetId = this.getAttribute('data-target');
                    var input = document.getElementById(targetId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.textContent = '🙈';
                    } else {
                        input.type = 'password';
                        this.textContent = '👁️';
                    }
                });
            });
        });
    </script>
</body>
</html>
