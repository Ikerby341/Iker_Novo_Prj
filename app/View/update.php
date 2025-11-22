<?php
// Incluïm el controlador que conté les funcions de modificació
include_once __DIR__ . '/../Controller/CRUDcontroller.php';

// Inicialitzem la variable que contindrà el missatge de resposta
$missatge = '';
$id = null;

// Comprovem si s'ha enviat el formulari (mètode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si ve del botó d'iniciar modificació només tindrem 'id' i mostrarem el formulari
    if (isset($_POST['id']) && !isset($_POST['confirm_modify'])) {
        $id = (int)($_POST['id'] ?? 0);
    } elseif (isset($_POST['confirm_modify'])) {
        // Aquest POST és l'enviament final del formulari amb camp i dada nova
        $id = (int)($_POST['id'] ?? 0);
        $camp = $_POST['camp'] ?? '';
        $dadaN = $_POST['dadaN'] ?? '';

        // Validacions bàsiques
        if ($id <= 0) {
            $missatge = 'ID invàlida';
        } elseif (trim($camp) === '' || trim($dadaN) === '') {
            $missatge = 'Camp o dada nova no poden estar buits';
        } else {
            // Verificar propietat abans de modificar
            try {
                global $connexio;
                $stmt = $connexio->prepare('SELECT owner_id FROM coches WHERE ID = ? LIMIT 1');
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $missatge = 'Article no trobat';
                } elseif ((int)$row['owner_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
                    $missatge = 'No tens permís per modificar aquest article';
                } else {
                    $missatge = modificarDada($id, $camp, $dadaN);
                }
            } catch (PDOException $e) {
                $missatge = 'Error a la base de dades: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualitzar article</title>
    <!-- Enllaç als estils CSS -->
    <link rel="stylesheet" href="./../../resources/styles/style.css">
</head>
<body>
    <!-- Títol principal de la pàgina -->
    <h1>Actualitzar article</h1>

    <section class="CRUD-section">
        <!-- Contenidor per mostrar missatges de resposta -->    
        <div>
            <?php echo $missatge; ?>
        </div>
        <!-- Formulari per modificar dades existents -->
        <form method="POST" action="">
            <!-- Camp per l'ID del registre a modificar -->
            <?php if ($id !== null && $id > 0): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES); ?>">
                <p>ID: <?php echo htmlspecialchars($id, ENT_QUOTES); ?></p>
            <?php else: ?>
                <p>Selecciona un article per modificar des de la llista (usa el botó ✏️ al costat de l'article).</p>
                <p><a style="color: #65a6fc" href="./../../index.php">← Volver a la lista</a></p>
                <?php
                    // No mostramos el formulario si no hay id: salir para evitar que el usuario modifique ID manualmente
                    exit;
                ?>
            <?php endif; ?>
            <!-- Camp per especificar quin camp es vol modificar -->
            <label>Nom del camp:</label><br>
            <input type="text" name="camp" required><br>
            <!-- Camp per la nova dada que s'inserirà -->
            <label>Dada nova:</label><br>
            <input type="text" name="dadaN" required><br><br>
            <!-- Botó per enviar el formulari -->
            <button class="principalBox" type="submit" name="confirm_modify" value="1" style="width: auto;">Modificar ⚙️</button>
        </form>
        <br>
        <!-- Botó per tornar a la pàgina principal -->
        <button class="box" style="width: auto;" onclick="location.href='./../../index.php';">← Tornar enrere</button>
    </section>
</body>
</html>