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
    <section style="max-width:40%; margin:40px auto; padding:20px; border:1px solid #ccc; border-radius:8px; text-align:center;">
        <h2 style="margin-top:0;">Iniciar sessió</h2>
        <form action="/practiques/backend/Iker_Novo_Prj/app/Controller/login_controller.php" method="post">
            <label for="username">Nom d'usuari:</label>
            <br>
            <input type="text" id="username" name="username" required><br><br>
            
            <label for="password">Contrasenya:</label>
            <br>
            <input type="password" id="password" name="password" required><br><br>
            
            <button type="submit">Iniciar sessió</button>
        </form>
    </section>
</body>
</html>