<?php
    include_once __DIR__ . '/../Controller/controlador.php';
    
    $errors = [];
    $oldUser = '';
    $oldEmail = '';
    $firstErrorField = null; // Per al focus JavaScript
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordC = $_POST['passwordC'] ?? '';
        
        $result = register_user($username, $email, $password, $passwordC);
        if ($result['success']) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL . 'app/View/login.php' : '/app/View/login.php'));
            exit;
        } else {
            $errors = $result['errors'];
            // Determinar quin camp té error per fer focus
            if (!empty($errors)) {
                foreach ($errors as $err) {
                    if (stripos($err, 'usuari') !== false) {
                        $firstErrorField = 'username';
                        break;
                    } elseif (stripos($err, 'email') !== false) {
                        $firstErrorField = 'email';
                        break;
                    } elseif (stripos($err, 'contrasenya') !== false || stripos($err, 'password') !== false) {
                        $firstErrorField = 'password';
                        break;
                    } elseif (stripos($err, 'confirma') !== false) {
                        $firstErrorField = 'passwordC';
                        break;
                    } elseif (stripos($err, 'reCAPTCHA') !== false) {
                        $firstErrorField = 'recaptcha';
                        break;
                    }
                }
            }
            $oldUser = $username;
            $oldEmail = $email;
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
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">Guarcar</a></h1>
        </div>
    </header>
    <div class="site-content">
    
    <section class="login-section">
        <div class="login-columns">
            <div class="col-left">
                <img src="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>public/assets/img/UrusREC.png" alt="Lambroghini Urus" class="login-image">
            </div>
            <div class="col-right">
                <h2 class="login-title">Registrar-se</h2>
                <?php
                    if (!empty($errors)) {
                        // errors will be shown inside the form wrapper below
                    }
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
                    
                    <label for="email">Correu electronic:</label>
                    <br>
                    <input class="nice-input" type="email" id="email" name="email" value="<?php echo htmlspecialchars($oldEmail); ?>" required><br><br>

                    <label for="password">Contrasenya:</label>
                    <br>
                    <input class="nice-input" type="password" id="password" name="password" required><br><br>

                    <label for="passwordC">Confirma la contrasenya:</label>
                    <br>
                    <input class="nice-input" type="password" id="passwordC" name="passwordC" required><br><br>

                    <div aria-label="captcha" class="g-recaptcha" data-sitekey="6LeULxksAAAAAOp7haCI_UB1FyVg2gG2QbhN71Cu"></div>
                    <br>
                    <button type="submit">Registrar-se</button>
                </form>
                </div>
            </div> 
        </div>
        <div class="login-footer">
            <p class="login-text">Ja tens compte? <a style="color: blue;" href="login.php">Inicia sessió aquí</a></p>
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