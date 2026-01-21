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
    require_once __DIR__ . '/../../config/db-connection.php';
    global $connexio;
    
    try {
        $stmt = $connexio->prepare('SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1');
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $token_valid = true;
        } else {
            $error = 'L\'enllaç de recuperació és invàlid o ha caducat.';
        }
    } catch (PDOException $e) {
        $error = 'Error en validar el token.';
        error_log('Database Error: ' . $e->getMessage());
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
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $connexio->prepare('UPDATE usuarios SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ? LIMIT 1');
            $update_stmt->execute([$hashed_password, $token]);
            
            $message = 'Contrasenya restablerta correctament! Ja pots iniciar sessió amb la teva nova contrasenya.';
            $token_valid = false;
        } catch (PDOException $e) {
            $error = 'Error en restablir la contrasenya.';
            error_log('Database Error: ' . $e->getMessage());
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
                <input type="password" id="new_password" name="new_password" required>
                
       L'enllaç de recuperació és invàlid o ha caduca         <label for="confirm_password">Confirma la contrasenya:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                
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
</body>
</html>
