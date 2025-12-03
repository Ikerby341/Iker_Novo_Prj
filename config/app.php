<?php

// Calcula la BASE_URL fins a la carpeta arrel del projecte.
// Intenta localitzar el nom de la carpeta del projecte a $_SERVER['SCRIPT_NAME']
// i retornar la ruta fins a aquesta carpeta, p. ex. '/practiques/Iker_Novo_PrJ/'.
if (!defined('BASE_URL')) {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    // Nom de la carpeta del projecte (config està a PROJECT_ROOT/config)
    $projectDir = basename(dirname(__DIR__));

    $base = '/';
    if ($scriptName !== '' && $projectDir !== '') {
        $needle = '/' . $projectDir;
        $pos = strpos($scriptName, $needle);
        if ($pos !== false) {
            $base = substr($scriptName, 0, $pos + strlen($needle)) . '/';
        } else {
            // Fallback: usar dirname de dos nivells (pot donar /practiques/Iker_Novo_PrJ/app)
            $path = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            if ($path === '' || $path === '.') {
                $base = '/';
            } else {
                // Si el path conté el nom del projecte en algun punt, retallem fins allà
                $pos2 = strpos($path, $needle);
                if ($pos2 !== false) {
                    $base = substr($path, 0, $pos2 + strlen($needle)) . '/';
                } else {
                    $base = $path . '/';
                }
            }
        }
    }
    define('BASE_URL', $base);
}

?>
