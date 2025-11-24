<?php
    include_once __DIR__ . '/../Controller/controlador.php';

    // Procesar login si es POST
    $errors = [];
    $oldUser = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $oldUser = $username;

        $result = login_user($username, $password);
        if ($result['success']) {
            // Login correcto: redirige a la vista principal
            header('Location: /practiques/backend/Iker_Novo_Prj/');
            exit;
        } else {
            // Errores: guardar para mostrar
            $errors = $result['errors'];
        }
    }
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sessió</title>
    <link rel="stylesheet" href="./../../resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="./../../index.php" class="menu">🏠 Home</a>
        </div>
    </header>
    
    <section class="login-section">
        <div class="login-columns">
            <div class="col-left">
                <img src="./../../public/assets/img/GT3RSrec.png" alt="Porsche GT3 RS" class="login-image">
            </div>
            <div class="col-right">
                <h2 class="login-title">Iniciar sessió</h2>
                <?php
                    // Mostrar errors si existen
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
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($oldUser); ?>" required><br><br>

                    <label for="password">Contrasenya:</label>
                    <br>
                    <input type="password" id="password" name="password" required><br><br>

                    <button type="submit">Iniciar sessió</button>
                </form>
            </div>
        </div>
        <div class="login-footer">
            <span class="login-text">No tens compte? <a style="color: blue;" href="register.php">Registra't aquí</a></span>
            <span>&nbsp;&nbsp;&nbsp;</span>
            <input type="checkbox" id="rememberMe" name="rememberMe">
            <span class="login-text"> Remember Me</span>
        </div>
    </section>
</body>
</html>