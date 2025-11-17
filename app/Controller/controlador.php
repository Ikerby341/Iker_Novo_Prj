<?php
include_once __DIR__ .'/../Model/modelo.php';

/**
 * Llegeix la pàgina actual de la query string. Si no existeix o és invàlida, retorna 1.
 * Assegura que el valor retornat sigui un integer >= 1.
 */
/**
 * obtenir_pagina_actual
 * Llegeix la pagina actual de la query string. Retorna 1 si no existeix o es invalida.
 */
function obtenir_pagina_actual() {
    if (isset($_GET['page'])) {
        $raw = trim($_GET['page']);
        if (is_numeric($raw)) {
            $val = (int)$raw;
            $perPage = obtenir_per_pagina();
            $totalPages = obtenir_total_pagines($perPage);
            if ($val > 0 && $val <= $totalPages) {
                return $val;
            }
            return 1;
        }
    }
    return 1;
}

/**
 * mostrar_articles
 * Mostra els articles per la pagina i per-pagina indicats.
 */
function mostrar_articles($articlesPerPagina = 3) {
    $page = obtenir_pagina_actual();
    $perPage = obtenir_per_pagina($articlesPerPagina);
    return generar_articles($page, $perPage);
}

/**
 * mostrar_paginacio
 * Retorna l'HTML de la paginacio per la pagina actual i per-pagina.
 */
function mostrar_paginacio($articlesPerPagina = 3) {
    $page = obtenir_pagina_actual();
    $perPage = obtenir_per_pagina($articlesPerPagina);
    return generar_paginacio($page, $perPage);
}

/**
 * Llegeix l'opció 'per_page' de la query string i la valida.
 * Retorna el valor vàlid (int) o el valor per defecte passat.
 */
/**
 * obtenir_per_pagina
 * Llegeix l'opcio 'per_page' de la query string i la valida.
 * Retorna el valor valid (int) o el valor per defecte.
 */
function obtenir_per_pagina($default = 3) {
    if (isset($_GET['per_page'])) {
        $raw = trim($_GET['per_page']);
        if (is_numeric($raw)) {
            $val = (int)$raw;
            if ($val >= 1 && $val <= 100) {
                return $val;
            }
        }
    }
    return $default;
}

/**
 * Valida la pàgina sol·licitada i redirigeix automàticament a page=1
 * si la pàgina no existeix o és invàlida. Fa servir header(Location) i exit().
 */
/**
 * validar_pagina_solicitada
 * Valida la pagina sol.licitada i redirigeix a page=1 si no es valida.
 */
function validar_pagina_solicitada() {
    // No fem res si no hi ha paràmetre page
    if (!isset($_GET['page'])) return;

    $raw = trim($_GET['page']);
    // Si no és numèric, redirigim a la pàgina 1
    if (!is_numeric($raw)) {
        $params = $_GET;
        $params['page'] = 1;
        $params['per_page'] = obtenir_per_pagina();
        $qs = http_build_query($params);
        $url = $_SERVER['PHP_SELF'] . '?' . $qs;
        header('Location: ' . $url);
        exit;
    }

    $val = (int)$raw;
    $perPage = obtenir_per_pagina();
    $totalPages = obtenir_total_pagines($perPage);
    if ($val < 1 || $val > $totalPages) {
        $params = $_GET;
        $params['page'] = 1;
        $params['per_page'] = $perPage;
        $qs = http_build_query($params);
        $url = $_SERVER['PHP_SELF'] . '?' . $qs;
        header('Location: ' . $url);
        exit;
    }
}

?>