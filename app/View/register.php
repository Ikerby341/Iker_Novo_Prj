<?php
    include_once __DIR__ . '/../Controller/controlador.php';
    
    $errors = [];
    $oldUser = '';
    $oldEmail = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordC = $_POST['passwordC'] ?? '';
        
        $result = register_user($username, $email, $password, $passwordC);
        if ($result['success']) {
            header('Location: /practiques/backend/Iker_Novo_Prj/app/View/login.php');
            exit;
        } else {
            $errors = $result['errors'];
            $oldUser = $username;
            $oldEmail = $email;
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
                <img src="./../../public/assets/img/GT3RSrec.png" alt="GT3RSrec" class="login-image">
            </div>
            <div class="col-right">
                <h2 class="login-title">Registrarse</h2>
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
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($oldUser); ?>" required><br><br>
                    
                    <label for="email">Correu electronic:</label>
                    <br>
                    <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($oldEmail); ?>" required><br><br>

                    <label for="password">Contrasenya:</label>
                    <br>
                    <input type="password" id="password" name="password" required><br><br>

                    <label for="passwordC">Confirma la contrasenya:</label>
                    <br>
                    <input type="password" id="passwordC" name="passwordC" required><br><br>

                    <button type="submit">Registrar-se</button>
                </form>
                <p>Ja tens compte? <a style="color: blue;" href="login.php">Inicia sessió aquí</a></p>
            </div>
            
        </div>
    </section>
</body>
</html>