<?php

// Calcula la base URL hasta la carpeta raíz del proyecto.
// Intenta localizar el nombre de la carpeta del proyecto en $_SERVER['SCRIPT_NAME']
// y devolver la ruta hasta esa carpeta, p. ej. '/practiques/Iker_Novo_PrJ/'.
if (!defined('BASE_URL')) {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    // Nombre de la carpeta del proyecto (config está en PROJECT_ROOT/config)
    $projectDir = basename(dirname(__DIR__));

    $base = '/';
    if ($scriptName !== '' && $projectDir !== '') {
        $needle = '/' . $projectDir;
        $pos = strpos($scriptName, $needle);
        if ($pos !== false) {
            $base = substr($scriptName, 0, $pos + strlen($needle)) . '/';
        } else {
            // Fallback: usar el dirname dos niveles (puede dar /practiques/Iker_Novo_PrJ/app)
            $path = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            if ($path === '' || $path === '.') {
                $base = '/';
            } else {
                // Si el path contiene el nombre del proyecto en algún punto, recortamos hasta ahí
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
