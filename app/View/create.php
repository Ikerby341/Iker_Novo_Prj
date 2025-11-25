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
    <title>Crear article</title>
    <!-- Enllaç als estils CSS -->
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <!-- Títol principal de la pàgina -->
    <h1>Crear article</h1>
<section class="CRUD-section form-container-adapted">
        <!-- Contenidor per mostrar missatges de resposta -->
        <div>
            <?php echo $missatge; ?>
        </div>
        <!-- Formulari per inserir noves dades -->
        <form method="POST" action="">
            <!-- Camp pel títol -->
            <label>Marca:</label><br>
            <input type="text" name="titol" required><br>
            <!-- Camp pel cos del missatge -->
            <label>Model:</label><br>
            <input type="text" name="cos" required><br>
            <div class="button-row">
                <!-- Botó per tornar a la pàgina principal -->
                <button class="box" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                <!-- Botó per enviar el formulari -->
                <button class="principalBox" type="submit">Insertar ⛳</button>
            </div>
        </form>
</section>
</body>
</html>
