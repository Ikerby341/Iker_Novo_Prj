<?php
    include_once __DIR__ . '/../Controller/controlador.php';

    // Load .env file
    $envFile = __DIR__ . '/../../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
            }
        }
    }

    // Processar l'inici de sessió si és POST
    $errors = [];
    $oldUser = '';
    $firstErrorField = null; // Per al focus JavaScript
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['rememberMe']) && $_POST['rememberMe'] ? true : false;
        $oldUser = $username;

        $result = login_user($username, $password, $remember);
        if ($result['success']) {
            // Inici de sessió correcte: redirigir a la vista principal
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
            exit;
        } else {
            // Errors: desar per mostrar
            $errors = $result['errors'];
            // Determinar quin camp té error per fer focus
            if (!empty($errors)) {
                foreach ($errors as $err) {
                    if (stripos($err, 'usuari') !== false) {
                        $firstErrorField = 'username';
                        break;
                    } elseif (stripos($err, 'contrasenya') !== false || stripos($err, 'password') !== false) {
                        $firstErrorField = 'password';
                        break;
                    } elseif (stripos($err, 'reCAPTCHA') !== false) {
                        $firstErrorField = 'recaptcha';
                        break;
                    }
                }
            }
        }
    }
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sessió</title>
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <header>
        <div class="header-container">
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
        </div>
    </header>
    <div class="site-content">

    <section class="login-section">
        <div class="login-columns">
                <div class="col-left">
                <img src="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>public/assets/img/GT3RSrec.png" alt="Porsche GT3 RS" class="login-image">
            </div>
            <div class="col-right">
                <h2 class="login-title">Iniciar sessió</h2>
                <?php
                    // Mostrar errors si existeixen
                ?>

                <div class="form-inner">
                <?php
                    if (!empty($errors)) {
                        echo '<div class="form-errors"><ul>';
                        foreach ($errors as $err) {
                            echo '<li>' . htmlspecialchars($err) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                ?>                

                <form method="post">
                    <label for="username">Nom d'usuari:</label>
                    <br>
                    <input class="nice-input" type="text" id="username" name="username" value="<?php echo htmlspecialchars($oldUser); ?>" required><br><br>

                    <label for="password">Contrasenya:</label>
                    <br>
                    <div class="password-container">
                        <input class="nice-input" type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" data-target="password">👁️</button>
                    </div>
                    <span class="login-pass-text"><a href="forgotpassword.php">Contrasenya oblidada?</a></span><br>
                    <?php if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                          $attempts = isset($_SESSION['login_attempts']) ? (int)$_SESSION['login_attempts'] : 0;
                    ?>
                    <?php if ($attempts >= 3): ?>
                        <div aria-label="captcha" class="g-recaptcha" data-sitekey="6LeULxksAAAAAOp7haCI_UB1FyVg2gG2QbhN71Cu"></div>
                        <br>
                    <?php endif; ?>
                    <button type="submit">Iniciar sessió</button>
                    
                    <label style="display:inline-flex; align-items:center; gap:6px;">
                        <input type="checkbox" id="rememberMe" name="rememberMe" value="1">
                        <span class="login-text"> Recorda'm</span>
                    </label>
                </form>

                <br><br>--------------------- O bé ---------------------<br><br>
                <div class="oauth-buttons">
                    <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>public/index.php?action=github_login" class="github-btn" aria-label="Inicia sesión con GitHub">
                        <img src="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>public/assets/img/github.webp" alt="GitHub" class="github-icon">
                    </a>
                    <a href="https://discord.com/api/oauth2/authorize?client_id=<?php echo getenv('DISCORD_CLIENT_ID'); ?>&redirect_uri=<?php echo urlencode(getenv('DISCORD_REDIRECT_URI')); ?>&response_type=code&scope=identify%20email" class="discord-btn" aria-label="Inicia sesión con Discord">
                        <img src="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>public/assets/img/discord.webp" alt="Discord" class="discord-icon">
                    </a>
                </div>

                </div>
            </div>
        </div>
        <div class="login-footer">
            <span class="login-text">No tens compte? <a style="color: blue;" href="register.php">Registra't aquí</a></span>
        </div>
    </section>
    </div>
        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
                <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
            </div>
        </footer>
    </body>
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

        // Auto-focus i select en el camp amb error
        (function(){
            var errorField = '<?php echo $firstErrorField; ?>';
            if (errorField && errorField !== 'null') {
                var field = document.getElementById(errorField);
                if (field) {
                    field.focus();
                    if (field.type === 'text' || field.type === 'password' || field.type === 'email') {
                        field.select();
                    }
                }
            }
        })();
    </script>
    </html>
