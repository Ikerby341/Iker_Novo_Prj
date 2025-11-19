<?php
// Incluïm el controlador que conté les funcions de modificació
include_once __DIR__ . '/../Controller/CRUDcontroller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenim les dades del formulari amb l'operador de coalescència nul·la
    $id = $_POST['id'] ?? '';        // ID del registre a modificar
    $camp = $_POST['camp'] ?? '';    // Nom del camp que es vol modificar
    $dadaN = $_POST['dadaN'] ?? '';  // Nova dada que s'inserirà
    // Cridem a la funció per modificar la dada i guardem el resultat
    $missatge = modificarDada($id,$camp, $dadaN);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar artículo</title>
    <!-- Enllaç als estils CSS -->
    <link rel="stylesheet" href="./../../resources/styles/style.css">
</head>
<body>
    <!-- Títol principal de la pàgina -->
    <h1>Actualizar artículo</h1>

    <!-- Formulari per modificar dades existents -->
    <form method="POST" action="">
        <!-- Camp per l'ID del registre a modificar -->
        <label>ID:</label><br>
        <input type="number" name="id" required><br>
        <!-- Camp per especificar quin camp es vol modificar -->
        <label>Nom del camp:</label><br>
        <input type="text" name="camp" required><br>
        <!-- Camp per la nova dada que s'inserirà -->
        <label>Dada nova:</label><br>
        <input type="text" name="dadaN" required><br><br>
        <!-- Botó per enviar el formulari -->
        <button class="principalBox" type="submit" style="width: auto;">Modificar ⚙️</button>
    </form>

    <!-- Contenidor per mostrar missatges de resposta -->
    <div>
        <?php echo $missatge; ?>
    </div>

    <!-- Botó per tornar a la pàgina principal -->
    <button class="box" style="width: auto;" onclick="location.href='./../../index.php';">← Tornar enrere</button>
</body>
</html>