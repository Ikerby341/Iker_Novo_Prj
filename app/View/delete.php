<?php
// Incluïm el controlador que conté les funcions d'esborrat
include_once __DIR__ . '/../Controller/CRUDcontroller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenim l'ID del registre a esborrar
    $id = $_POST['id'] ?? '';
    // Cridem a la funció per esborrar la dada i guardem el resultat
    $missatge = esborrarDada($id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar artículo</title>
    <!-- Enllaç als estils CSS -->
    <link rel="stylesheet" href="./../../resources/styles/style.css">
</head>
<body>
    <!-- Títol principal de la pàgina -->
    <h1>Eliminar artículo</h1>

    <!-- Formulari per esborrar dades -->
    <form method="POST" action="">
        <!-- Camp per l'ID del registre a esborrar -->
        <label>Digues una ID per eliminar:</label><br>
        <input type="number" name="id" style="width: 50px; text-align: center;" required>
        <!-- Botó per enviar el formulari -->
        <button class="principalBox" type="submit" style="width: auto;">Eliminar 🗑️</button>
    </form>

    <!-- Contenidor per mostrar missatges de resposta -->
    <div>
        <?php echo $missatge; ?>
    </div>

    <!-- Botó per tornar a la pàgina principal -->
    <button class="box" style="width: auto;" onclick="location.href='./../../index.php';">← Tornar enrere</button>
</body>
</html>