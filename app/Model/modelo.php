<?php 
require_once __DIR__ . '/../../config/db-connection.php';

/**
 * genera_articles
 * Genera els articles segons la pagina actual i articles per pagina.
 * Retorna un string HTML amb els articles.
 * params: $page (int), $articlesPerPagina (int)
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
            return "<p> No hi ha articles disponibles. </p>";
        }

        $sortida = '';
        foreach ($articles as $fila) {
            $id = isset($fila['ID']) ? (int)$fila['ID'] : 0;
            $marca = isset($fila['marca']) ? htmlspecialchars($fila['marca']) : '';
            $model = isset($fila['model']) ? htmlspecialchars($fila['model']) : '';
            $ownerId = isset($fila['owner_id']) ? (int)$fila['owner_id'] : null;

            $sortida .= '<section class="article-row">';
            $sortida .= '<div class="article-content">';
            $sortida .= "<h3>$marca</h3><p>$model</p>";
            $sortida .= '</div>';

            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id']) && $ownerId !== null && $_SESSION['user_id'] === $ownerId) {
                $sortida .= '<div class="article-actions">';
                $sortida .= '<form method="post" action="/practiques/backend/Iker_Novo_PrJ/app/View/update.php">';
                $sortida .= '<input type="hidden" name="id" value="' . $id . '">';
                $sortida .= '<button type="submit" class="edit-btn" title="Editar">✏️</button>';
                $sortida .= '</form>';
                $sortida .= '<form method="post" action="/practiques/backend/Iker_Novo_PrJ/app/View/delete.php">';
                $sortida .= '<input type="hidden" name="id" value="' . $id . '">';
                $sortida .= '<button type="submit" class="delete-btn" title="Esborrar">🗑️</button>';
                $sortida .= '</form>';
                $sortida .= '</div>';
            }

            $sortida .= '</section>';
        }

        return $sortida;

    } catch (PDOException $e) {
        return "<h1> Error en la consulta: " . htmlspecialchars($e->getMessage()) . "</h1>";
    }
}

/**
 * generar_paginacio
 * Genera els enllaços de paginacio (Anterior / Numeros / Seguent)
 * params: $currentPage (int), $articlesPerPagina (int)
 */
function generar_paginacio($currentPage = 1, $articlesPerPagina = 3, $sort = 'ID', $dir = 'ASC') {
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

        // Parametre per mantenir articles per pagina i ordenació en els enllaços
        $perParam = '&per_page=' . urlencode((int)$articlesPerPagina);
        $sortParam = '&sort=' . urlencode($sort);
        $dirParam = '&dir=' . urlencode($dir);

        // Botó Anterior (només si hi ha pagina anterior)
        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $sortida .= '<a class="btn prev" href="index.php?page=' . $prevPage . $perParam . $sortParam . $dirParam . '" rel="prev"><button type="button">◀</button></a> ';
        }

        // Números de pagina
        for ($i = 1; $i <= $totalPagines; $i++) {
            if ($i == $currentPage) {
                $sortida .= '<span class="page-number active">' . $i . '</span> ';
            } else {
                $sortida .= '<a class="page-number" href="index.php?page=' . $i . $perParam . $sortParam . $dirParam . '">' . $i . '</a> ';
            }
        }

        // Botó Següent (només si hi ha pagina següent)
        if ($currentPage < $totalPagines) {
            $nextPage = $currentPage + 1;
            $sortida .= '<a class="btn next" href="index.php?page=' . $nextPage . $perParam . $sortParam . $dirParam . '" rel="next"><button type="button">▶</button></a>';
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

/**
 * Insereix un nou article a la base de dades
 * 
 * @param string $marca - Marca del cotxe
 * @param string $model - Model del cotxe
 * @return string - Missatge de confirmació o error
 */
function inserir($marca,$model) {
    global $connexio;

    try {
        // Preparar i executar la inserció amb paràmetres
        $query = "INSERT INTO coches (marca, model, owner_id) VALUES (:marca, :model, :owner_id)";
        $stmt = $connexio->prepare($query);
        $stmt->bindValue(':marca', $marca, PDO::PARAM_STR);
        $stmt->bindValue(':model', $model, PDO::PARAM_STR);
        $stmt->bindValue(':owner_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

            return "Article creat correctament!";
    } catch (PDOException $e) {
        return "Error en la creació: " . $e->getMessage();
    }
}

/**
 * Modifica un article existent a la base de dades
 * 
 * @param int $id - ID de l'article a modificar
 * @param string $camp - Nom del camp a modificar (titol o cos)
 * @param string $dadaN - Nova dada a inserir
 * @return string - Missatge de confirmació o error
 */
function modificar($id,$camp,$dadaN) {
    global $connexio;

        try {
            // Preparar i executar l'actualització amb paràmetres (columna ID en majúscules)
            $stmt = $connexio->prepare("UPDATE coches SET $camp = ? WHERE ID = ?");
            $stmt->execute([$dadaN,$id]);

        return "Artícle actualitzat correctament!";
            return "Article actualitzat correctament!";
    } catch (PDOException $e) {
        return "Error en la actualització: " . $e->getMessage();
    }
}

/**
 * Esborra un article de la base de dades
 * 
 * @param int $id - ID de l'article a esborrar
 * @return string - Missatge de confirmació o error
 */
function esborrar($id) {
    global $connexio;

        try {
            // Preparar i executar l'eliminació amb paràmetres (columna ID en majúscules)
            $stmt = $connexio->prepare("DELETE FROM coches WHERE ID = ?");
            $stmt->execute([$id]);

        // Comprovar si s'ha esborrat algun registre
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