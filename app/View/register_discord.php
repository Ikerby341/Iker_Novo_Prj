<?php
session_start();
include_once __DIR__ . '/../Controller/controlador.php';

// Load .env file
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

if (!isset($_SESSION['discord_data'])) {
    header('Location: login.php');
    exit;
}

$discord_data = $_SESSION['discord_data'];
$errors = [];
$oldUser = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        $errors[] = 'El nom d\'usuari és obligatori.';
    }

    if (user_exists_by_username($username)) {
        $errors[] = 'Ja existeix un usuari amb aquest nom d\'usuari.';
    }

    if (empty($errors)) {
        $created = create_user_oauth($username, $discord_data['email'], $discord_data['discord_id']);
        if ($created) {
            $user = get_user_by_discord_id($discord_data['discord_id']);
            login_user_oauth($user['id']);
            unset($_SESSION['discord_data']);
            header('Location: vista.php');
            exit;
        } else {
            $errors[] = 'Error en crear l\'usuari.';
        }
    }

    $oldUser = $username;
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre amb Discord - GUARCAR</title>
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <div class="container">
        <h1>Completa el registre amb Discord</h1>
        <p>Benvingut, <?php echo htmlspecialchars($discord_data['username']); ?>! Si us plau, tria un nom d'usuari per al teu compte.</p>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'usuari:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($oldUser); ?>" required>
            </div>

            <button type="submit" class="btn">Crear compte</button>
        </form>

        <p><a href="login.php">Tornar al login</a></p>
    </div>
</body>
</html>