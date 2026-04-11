<?php
require_once __DIR__ . '/../../config/db-connection.php';

/**
 * genera_articles
 * Genera els articles segons la pagina actual i articles per pagina.
 * Retorna un array amb els articles.
 * paràmetres: $page (int), $articlesPerPagina (int), $is_logged_in (bool), $user_id (int|null)
 */
function generar_articles($page = 1, $articlesPerPagina = 3, $sort = 'ID', $dir = 'ASC', $is_logged_in = false, $user_id = null) {
    global $connexio;
    try {
        $offset = ($page - 1) * $articlesPerPagina;

        // Validar i normalitzar els paràmetres d'ordenació (llista blanca)
        $allowed = ['ID','marca','model'];
        $sort = in_array($sort, $allowed, true) ? $sort : 'ID';
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $orderClause = "ORDER BY $sort $dir";

        // Construir la consulta segons si l'usuari està autenticat
        if ($is_logged_in && $user_id) {
            $query = "SELECT * FROM coches WHERE owner_id = :owner_id $orderClause LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT * FROM coches $orderClause LIMIT :limit OFFSET :offset";
        }

        $stmt = $connexio->prepare($query);
        $stmt->bindValue(':limit', (int)$articlesPerPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        if ($is_logged_in && $user_id) {
            $stmt->bindValue(':owner_id', $user_id, PDO::PARAM_INT);
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
 * paràmetres: $currentPage (int), $articlesPerPagina (int), $sort (string), $dir (string), $is_logged_in (bool), $user_id (int|null)
 */
function generar_paginacio($currentPage = 1, $articlesPerPagina = 3, $sort = 'ID', $dir = 'ASC', $is_logged_in = false, $user_id = null) {
    global $connexio;
    try {
        if ($is_logged_in && $user_id) {
            $query = "SELECT COUNT(*) AS total FROM coches WHERE owner_id = :owner_id";
            $stmt = $connexio->prepare($query);
            $stmt->bindValue(':owner_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $query = "SELECT COUNT(*) AS total FROM coches";
            $stmt = $connexio->query($query);
        }
        
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalArticles = isset($resultat['total']) ? (int)$resultat['total'] : 0;

        $totalPagines = ($articlesPerPagina > 0) ? ceil($totalArticles / $articlesPerPagina) : 1;

        $currentPage = max(1, min((int)$currentPage, $totalPagines));

        return [
            'totalPages' => $totalPagines,
            'currentPage' => $currentPage,
            'perPage' => $articlesPerPagina,
            'sort' => $sort,
            'dir' => $dir
        ];
    } catch (PDOException $e) {
        return [
            'totalPages' => 1,
            'currentPage' => 1,
            'perPage' => $articlesPerPagina,
            'sort' => $sort,
            'dir' => $dir
        ];
    }
}

/**
 * obtenir_total_pagines
 * paràmetres: $articlesPerPagina (int), $is_logged_in (bool), $user_id (int|null)
 */
function obtenir_total_pagines($articlesPerPagina = 3, $is_logged_in = false, $user_id = null) {
    global $connexio;

    try {
        if ($is_logged_in && $user_id) {
            $query = "SELECT COUNT(*) AS total FROM coches WHERE owner_id = :owner_id";
            $stmt = $connexio->prepare($query);
            $stmt->bindValue(':owner_id', $user_id, PDO::PARAM_INT);
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
 * listar_tots_articles
 * Retorna tots els articles (filtrats per usuari si cal). S'utilitza per carregar totes les dades al client.
 * paràmetres: $onlyOwner (bool), $user_id (int|null)
 */
function listar_tots_articles($onlyOwner = false, $user_id = null) {
    global $connexio;

    try {
        if ($onlyOwner && $user_id) {
            $query = "SELECT ID, marca, model, ruta_img FROM coches WHERE owner_id = :owner_id ORDER BY ID ASC";
            $stmt = $connexio->prepare($query);
            $stmt->bindValue(':owner_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $query = "SELECT ID, marca, model, ruta_img FROM coches ORDER BY ID ASC";
            $stmt = $connexio->query($query);
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    } catch (PDOException $e) {
        return [];
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

/**
 * Obté un vehicle aleatori des d'una API externa
 * Retorna un array amb 'marca' i 'model' o false en cas d'error
 */
function get_random_vehicle() {
    $marcasJson = @file_get_contents("https://vpic.nhtsa.dot.gov/api/vehicles/getallmakes?format=json");
    if ($marcasJson === false) {
        return false;
    }
    $marcas = json_decode($marcasJson, true);
    if ($marcas === null || empty($marcas['Results'])) {
        return false;
    }

    $marcasArray = $marcas['Results'];

    // Filtrar marques comunes d'automòbils a Espanya
    $common_makes = ['AUDI', 'VOLKSWAGEN', 'SEAT', 'BMW', 'SKODA', 'MERCEDES-BENZ', 'OPEL', 'FORD', 'RENAULT', 'PEUGEOT', 'CITROEN', 'KIA', 'HYUNDAI', 'TOYOTA', 'NISSAN', 'FIAT', 'VOLVO', 'MAZDA', 'HONDA', 'LEXUS'];
    $filtered_makes = array_filter($marcasArray, function($make) use ($common_makes) {
        return in_array(strtoupper($make['Make_Name']), $common_makes);
    });

    if (empty($filtered_makes)) {
        $filtered_makes = $marcasArray;
    }

    $marcaAleatoria = $filtered_makes[array_rand($filtered_makes)]['Make_Name'];

    $urlModelos = "https://vpic.nhtsa.dot.gov/api/vehicles/getmodelsformake/" . urlencode($marcaAleatoria) . "?format=json";
    $modelosJson = @file_get_contents($urlModelos);
    if ($modelosJson === false) {
        $modeloAleatorio = 'Model ' . rand(1, 10);
    } else {
        $modelos = json_decode($modelosJson, true);
        if ($modelos === null || empty($modelos['Results'])) {
            $modeloAleatorio = 'Model ' . rand(1, 10);
        } else {
            $modelosArray = $modelos['Results'];
            $modeloAleatorio = $modelosArray[array_rand($modelosArray)]['Model_Name'];
        }
    }

    return [
        'marca' => $marcaAleatoria,
        'model' => $modeloAleatorio
    ];
}

/**
 * get_article_owner_and_image
 * Retorna l'owner_id i ruta_img d'un article
 */
function get_article_owner_and_image($id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT owner_id, ruta_img FROM coches WHERE ID = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * get_article_owner
 * Retorna l'owner_id d'un article
 */
function get_article_owner($id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT owner_id FROM coches WHERE ID = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['owner_id'] : false;
    } catch (PDOException $e) {
        return false;
    }
}

?>
