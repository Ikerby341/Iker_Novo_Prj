<?php 
require_once __DIR__ . '/../../config/db-connection.php';

/**
 * genera_articles
 * Genera els articles segons la pagina actual i articles per pagina.
 * Retorna un string HTML amb els articles.
 * params: $page (int), $articlesPerPagina (int)
 */
function generar_articles($page = 1, $articlesPerPagina = 3) {
    global $connexio;
    try {
        $offset = ($page - 1) * $articlesPerPagina;

        // Generar SELECT condicionat al login de l'usuari
        if (is_logged_in()) {
            $query = "SELECT * FROM coches WHERE owner_id = :owner_id ORDER BY id ASC LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT * FROM coches ORDER BY id ASC LIMIT :limit OFFSET :offset";
        }

        // Afegim ORDER BY per assegurar un ordre consistent entre pagines
        $stmt = $connexio->prepare($query);
        $stmt->bindValue(':limit', (int)$articlesPerPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        if (is_logged_in()) {
            $stmt->bindValue(':owner_id', $_SESSION['user_id'], PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($articles) > 0) {
            $sortida = '';
            foreach ($articles as $fila) {
                $marca = isset($fila['marca']) ? htmlspecialchars($fila['marca']) : '';
                $model = isset($fila['model']) ? htmlspecialchars($fila['model']) : '';
                $sortida .= "<section> <h3>$marca</h3><p>$model</p> </section>";
            }
            return $sortida;
        } else {
            return "<p> No hi ha articles disponibles. </p>";
        }
    } catch (PDOException $e) {
        return "<h1> Error en la consulta: " . $e->getMessage() . "</h1>";
    }
}

/**
 * generar_paginacio
 * Genera els enllaços de paginacio (Anterior / Numeros / Seguent)
 * params: $currentPage (int), $articlesPerPagina (int)
 */
function generar_paginacio($currentPage = 1, $articlesPerPagina = 3) {
    global $connexio;
    try {
        // Generar SELECT condicionat al login de l'usuari
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

        // Clamp de la pagina actual perquè no sobrepassi els límits
        $currentPage = max(1, min((int)$currentPage, $totalPagines));

        $sortida = '<div class="paginacio">';

        // Parametre per mantenir articles per pagina en els enllaços
        $perParam = '&per_page=' . urlencode((int)$articlesPerPagina);

        // Botó Anterior (només si hi ha pagina anterior)
        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $sortida .= '<a class="btn prev" href="index.php?page=' . $prevPage . $perParam . '" rel="prev"><button type="button">Anterior</button></a> ';
        }

        // Números de pagina
        for ($i = 1; $i <= $totalPagines; $i++) {
            if ($i == $currentPage) {
                $sortida .= '<span class="page-number active">' . $i . '</span> ';
            } else {
                $sortida .= '<a class="page-number" href="index.php?page=' . $i . $perParam . '">' . $i . '</a> ';
            }
        }

        // Botó Següent (només si hi ha pagina següent)
        if ($currentPage < $totalPagines) {
            $nextPage = $currentPage + 1;
            $sortida .= '<a class="btn next" href="index.php?page=' . $nextPage . $perParam . '" rel="next"><button type="button">Següent</button></a>';
        }

        $sortida .= '</div>';

        return $sortida;
    } catch (PDOException $e) {
        return "<h1> Error en la consulta: " . $e->getMessage() . "</h1>";
    }
}

/**
 * obtenir_total_pagines
 * Retorna el nombre total de pagines per a un determinat articlesPerPagina
 * params: $articlesPerPagina (int)
 */
function obtenir_total_pagines($articlesPerPagina = 3) {
    global $connexio;

    try {
        // Generar SELECT condicionat al login de l'usuari
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
        // En cas d'error, assumim 1 pàgina per evitar fallades a la vista
        return 1;
    }
}

/* ----------------------------
   User helper functions
   ---------------------------- */
/**
 * get_user_by_username
 * Retorna un array amb les dades de l'usuari o false si no existeix
 */
function get_user_by_username($username) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT * FROM usuarios WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * user_exists_by_username
 * Retorna true si existeix l'usuari amb aquest username
 */
function user_exists_by_username($username) {
    return (bool)get_user_by_username($username);
}

/**
 * create_user
 * Inserta un nou usuari, la contrasenya s'ha d'enviar ja hashejada
 * Retorna true si s'ha creat, o false en cas de error
 */
function create_user($username, $email, $passwordHash) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('INSERT INTO usuarios (username, email, password) VALUES (:u, :e, :p)');
        return $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':p' => $passwordHash
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * update_user_password_hash
 * Actualitza la contrasenya hash d'un usuari (per migracions quan la BD tenia contrasenya en text pla)
 */
function update_user_password_hash($userId, $newHash) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('UPDATE usuarios SET password = :p WHERE id = :id');
        return $stmt->execute([':p' => $newHash, ':id' => $userId]);
    } catch (PDOException $e) {
        return false;
    }
}


?>