<?php

/**
 * articles_controller.php
 * Gestiona la visualització i manipulació d'articles
 */

/**
 * mostrar_articles
 * Mostra els articles per la pàgina i per-pàgina indicats.
 */
function mostrar_articles($articlesPerPagina = 8) {
    $page = obtenir_pagina_actual();
    $perPage = obtenir_per_pagina($articlesPerPagina);
    
    // Llegim opcions d'ordenació de la query string i validem-les
    $allowedFields = ['ID', 'marca', 'model'];
    $sort = 'ID';
    $dir = 'ASC';
    
    if (isset($_GET['sort'])) {
        $candidate = trim($_GET['sort']);
        $map = ['id' => 'ID', 'marca' => 'marca', 'model' => 'model'];
        $lk = strtolower($candidate);
        if (isset($map[$lk])) $sort = $map[$lk];
    }
    
    if (isset($_GET['dir'])) {
        $d = strtoupper(trim($_GET['dir']));
        if (in_array($d, ['ASC','DESC'])) $dir = $d;
    }

    $articles = generar_articles($page, $perPage, $sort, $dir);
    
    if (is_array($articles)) {
        $sortida = '<div class="articles-grid">';
        foreach ($articles as $fila) {
            $id = isset($fila['ID']) ? (int)$fila['ID'] : 0;
            $marca = isset($fila['marca']) ? htmlspecialchars($fila['marca']) : '';
            $model = isset($fila['model']) ? htmlspecialchars($fila['model']) : '';
            $ruta_img = (defined('BASE_URL') ? BASE_URL : '/') . htmlspecialchars($fila['ruta_img']);
            
            $sortida .= '<div class="article-card">';
            $sortida .= '<div class="article-image">';
            $sortida .= '<img src="' . $ruta_img . '" alt="' . $marca . ' ' . $model . '" />';
            $sortida .= '</div>';
            $sortida .= '<div class="article-content">';
            $sortida .= "<h3>$marca</h3>";
            $sortida .= "<p>$model</p>";
            $sortida .= '</div>';
            
            // Si estem logats, mostrem els botons
            if (is_logged_in()) {
                $sortida .= '<div class="article-actions">';
                $sortida .= '<form method="post" action="app/View/update.php">';
                $sortida .= '<input type="hidden" name="id" value="' . $id . '">';
                $sortida .= '<button type="submit" class="edit-btn" title="Editar">✏️</button>';
                $sortida .= '</form>';
                $sortida .= '<form method="post" action="app/View/delete.php">';
                $sortida .= '<input type="hidden" name="id" value="' . $id . '">';
                $sortida .= "<button type=\"submit\" class=\"delete-btn\" title=\"Esborrar\" onclick=\"return confirm('Estàs segur que vols eliminar aquest article?')\">🗑️</button>";
                $sortida .= '</form>';
                $sortida .= '</div>';
            }
            $sortida .= '</div>';
        }
        $sortida .= '</div>';
        return $sortida;
    }
    return $articles;
}

/**
 * mostrar_paginacio
 * Retorna l'HTML de la paginació per la pàgina actual i per-pàgina.
 */
function mostrar_paginacio($articlesPerPagina = 8) {
    $page = obtenir_pagina_actual();
    $perPage = obtenir_per_pagina($articlesPerPagina);
    
    // Incorporem la mateixa lògica d'ordenació per mantenir els paràmetres en la paginació
    $sort = 'ID';
    $dir = 'ASC';
    
    if (isset($_GET['sort'])) {
        $map = ['id' => 'ID', 'marca' => 'marca', 'model' => 'model'];
        $lk = strtolower(trim($_GET['sort']));
        if (isset($map[$lk])) $sort = $map[$lk];
    }
    
    if (isset($_GET['dir'])) {
        $d = strtoupper(trim($_GET['dir']));
        if (in_array($d, ['ASC','DESC'])) $dir = $d;
    }

    return generar_paginacio($page, $perPage, $sort, $dir);
}

?>
