<?php
    
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

                <form action="/practiques/backend/Iker_Novo_Prj/app/Controller/login_controller.php" method="post">
                    <label for="username">Nom d'usuari:</label>
                    <br>
                    <input type="text" id="username" name="username" required><br><br>
                    
                    <label for="email">Correu electronic:</label>
                    <br>
                    <input type="text" id="email" name="email" required><br><br>

                    <label for="password">Contrasenya:</label>
                    <br>
                    <input type="password" id="password" name="password" required><br><br>

                    <label for="passwordC">Confirma la contrasenya:</label>
                    <br>
                    <input type="passwordC" id="passwordC" name="passwordC" required><br><br>

                    <button type="submit">Iniciar sessió</button>
                </form>
                <p>Ja tens compte? <a style="color: blue;" href="login.php">Inicia sessió aquí</a></p>
            </div>
            
        </div>
    </section>
</body>
</html>