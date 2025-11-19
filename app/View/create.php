<?php
// Incluïm el controlador que conté les funcions d'inserció
include_once __DIR__ . '/../Controller/CRUDcontroller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenim les dades del formulari
    $titol = $_POST['titol'] ?? '';
    $cos = $_POST['cos'] ?? '';
    // Cridem a la funció per inserir les dades i guardem el resultat
    $missatge = inserirDada($titol, $cos);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear artículo</title>
    <!-- Enllaç als estils CSS -->
    <link rel="stylesheet" href="./../../resources/styles/style.css">
</head>
<body>
    <!-- Títol principal de la pàgina -->
    <h1>Crear artículo</h1>
<section class="login-section">
    <!-- Formulari per inserir noves dades -->
    <form method="POST" action="">
        <!-- Camp pel títol -->
        <label>Marca:</label><br>
        <input type="text" name="titol" required><br>
        <!-- Camp pel cos del missatge -->
        <label>Modelo:</label><br>
        <input type="text" name="cos" required><br><br>
        <!-- Botó per enviar el formulari -->
        <button class="principalBox" type="submit" style="width: auto;">Insertar ⛳</button>
    </form>

    <!-- Contenidor per mostrar missatges de resposta -->
    <div>
        <?php echo $missatge; ?>
    </div>

    <!-- Botó per tornar a la pàgina principal -->
    <button class="box" style="width: auto;" onclick="location.href='./../../index.php';">← Tornar enrere</button>
</section>
</body>
</html>