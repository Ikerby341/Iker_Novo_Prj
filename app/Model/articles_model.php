<?php
require_once __DIR__ . '/../../config/db-connection.php';

/**
 * genera_articles
 * Genera els articles segons la pagina actual i articles per pagina.
 * Retorna un array amb els articles.
 * paràmetres: $page (int), $articlesPerPagina (int)
 */
function generar_articles($page = 1, $articlesPerPagina = 3, $sort = 'ID', $dir = 'ASC') {
    global $connexio;
    try {
        $offset = ($page - 1) * $articlesPerPagina;

        // Validar i normalitzar els paràmetres d'ordenació (llista blanca)
        $allowed = ['ID','marca','model'];
        $sort = in_array($sort, $allowed, true) ? $sort : 'ID';
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $orderClause = "ORDER BY $sort $dir";

        // Construir la consulta segons si l'usuari està autenticat
        if (is_logged_in()) {
            $query = "SELECT * FROM coches WHERE owner_id = :owner_id $orderClause LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT * FROM coches $orderClause LIMIT :limit OFFSET :offset";
        }

        $stmt = $connexio->prepare($query);
        $stmt->bindValue(':limit', (int)$articlesPerPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        if (is_logged_in()) {
            $stmt->bindValue(':owner_id', $_SESSION['user_id'], PDO::PARAM_INT);
        }

        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($articles) === 0) {
            return [];
        }

        return $articles;

    } catch (PDOException $e) {
        return [];
    }
}

/**
 * generar_paginacio
 * Genera els enllaços de paginacio (Anterior / Numeros / Seguent)
 */
function generar_paginacio($currentPage = 1, $articlesPerPagina = 3, $sort = 'ID', $dir = 'ASC') {
    global $connexio;
    try {
        if (is_logged_in()) {
            $query = "SELECT COUNT(*) AS total FROM coches WHERE owner_id = :owner_id";
            $stmt = $connexio->prepare($query);
            $stmt->bindValue(':owner_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $query = "SELECT COUNT(*) AS total FROM coches";
            $stmt = $connexio->query($query);
        }
        
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalArticles = isset($resultat['total']) ? (int)$resultat['total'] : 0;

        $totalPagines = ($articlesPerPagina > 0) ? ceil($totalArticles / $articlesPerPagina) : 1;

        if ($totalPagines <= 1) {
            return '';
        }

        $currentPage = max(1, min((int)$currentPage, $totalPagines));

        $sortida = '<div class="paginacio">';

        $perParam = '&per_page=' . urlencode((int)$articlesPerPagina);
        $sortParam = '&sort=' . urlencode($sort);
        $dirParam = '&dir=' . urlencode($dir);

        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $sortida .= '<a class="btn prev" href="index.php?page=' . $prevPage . $perParam . $sortParam . $dirParam . '" rel="prev"><button type="button">◀</button></a> ';
        }

        for ($i = 1; $i <= $totalPagines; $i++) {
            if ($i == $currentPage) {
                $sortida .= '<span class="page-number active">' . $i . '</span> ';
            } else {
                $sortida .= '<a class="page-number" href="index.php?page=' . $i . $perParam . $sortParam . $dirParam . '">' . $i . '</a> ';
            }
        }

        if ($currentPage < $totalPagines) {
            $nextPage = $currentPage + 1;
            $sortida .= '<a class="btn next" href="index.php?page=' . $nextPage . $perParam . $sortParam . $dirParam . '" rel="next"><button type="button">▶</button></a>';
        }

        $sortida .= '</div>';

        return $sortida;
    } catch (PDOException $e) {
        return "";
    }
}

/**
 * obtenir_total_pagines
 */
function obtenir_total_pagines($articlesPerPagina = 3) {
    global $connexio;

    try {
        if (is_logged_in()) {
            $query = "SELECT COUNT(*) AS total FROM coches WHERE owner_id = :owner_id";
            $stmt = $connexio->prepare($query);
            $stmt->bindValue(':owner_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $query = "SELECT COUNT(*) AS total FROM coches";
            $stmt = $connexio->query($query);
        }
        
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalArticles = isset($resultat['total']) ? (int)$resultat['total'] : 0;
        if ($articlesPerPagina <= 0) return 1;
        return ($totalArticles > 0) ? (int)ceil($totalArticles / $articlesPerPagina) : 1;
    } catch (PDOException $e) {
        return 1;
    }
}

/**
 * Insereix un nou article a la base de dades
 */
function inserir($marca,$model, $ruta_img = null) {
    global $connexio;

    try {
        if ($ruta_img !== null && $ruta_img !== '') {
            $query = "INSERT INTO coches (marca, model, owner_id, ruta_img) VALUES (:marca, :model, :owner_id, :ruta_img)";
            $stmt = $connexio->prepare($query);
            $stmt->bindValue(':marca', $marca, PDO::PARAM_STR);
            $stmt->bindValue(':model', $model, PDO::PARAM_STR);
            $stmt->bindValue(':owner_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':ruta_img', $ruta_img, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $query = "INSERT INTO coches (marca, model, owner_id) VALUES (:marca, :model, :owner_id)";
            $stmt = $connexio->prepare($query);
            $stmt->bindValue(':marca', $marca, PDO::PARAM_STR);
            $stmt->bindValue(':model', $model, PDO::PARAM_STR);
            $stmt->bindValue(':owner_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
        }

        return "Article creat correctament!";
    } catch (PDOException $e) {
        return "Error en la creació: " . $e->getMessage();
    }
}

/**
 * Modifica un article existent a la base de dades
 */
function modificar($id,$camp,$dadaN) {
    global $connexio;

    try {
        $stmt = $connexio->prepare("UPDATE coches SET $camp = ? WHERE ID = ?");
        $stmt->execute([$dadaN,$id]);

        return "Article actualitzat correctament!";
    } catch (PDOException $e) {
        return "Error en la actualització: " . $e->getMessage();
    }
}

/**
 * Esborra un article de la base de dades i la seva imatge associada
 */
function esborrar($id) {
    global $connexio;

    try {
        // Obtenir la ruta de la imatge
        $stmt2 = $connexio->prepare("SELECT ruta_img FROM coches WHERE ID = ?");
        $stmt2->execute([$id]);
        $result = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        // Eliminar la imatge del servidor si existeix i no és la imatge per defecte
        if ($result && $result['ruta_img']) {
            $ruta_img = $result['ruta_img'];
            // No eliminar si és la imatge per defecte
            $defaultImage = ['public/assets/img/default.webp'];
            if (!in_array($ruta_img, $defaultImage, true)) {
                $imagePath = __DIR__ . '/../../' . $ruta_img;
                if (file_exists($imagePath) && is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }
        }
        
        // Eliminar el registre de la base de dades
        $stmt = $connexio->prepare("DELETE FROM coches WHERE ID = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            return "Article esborrat correctament!";
        } else {
            return "No s'ha trobat l'article amb la ID especificada.";
        }
    } catch (PDOException $e) {
        return "Error en la eliminació: " . $e->getMessage();
    }
}

?>
