<?php
require_once __DIR__ . '/../../config/db-connection.php';

/* ----------------------------
    Funcions d'ajuda d'usuari
   ---------------------------- */
/** Retorna tots els usuaris */
function get_all_users() {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT * FROM usuarios ORDER BY id ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

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
 * Actualitza la contrasenya hash d'un usuari
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
 * Remember-me token helpers
 */
function set_remember_token($userId, $token) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('UPDATE usuarios SET remember_token = :t WHERE id = :id');
        return $stmt->execute([':t' => $token, ':id' => $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function find_user_by_remember_token($token) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT * FROM usuarios WHERE remember_token = :t LIMIT 1');
        $stmt->execute([':t' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : false;
    } catch (PDOException $e) {
        return false;
    }
}

function clear_remember_token($userId) {
    return set_remember_token($userId, null);
}

function modificarUsernameInDB($id, $newUsername) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('UPDATE usuarios SET username = :username WHERE id = :id');
        return $stmt->execute([':username' => $newUsername, ':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * modificarEmailInDB
 * Actualitza l'email d'un usuari. Pot ser repetit en la BD.
 */
function modificarEmailInDB($id, $newEmail) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('UPDATE usuarios SET email = :email WHERE id = :id');
        return $stmt->execute([':email' => $newEmail, ':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * delete_user
 * Elimina un usuario por ID, amb comprovacions de seguretat
 * @param int $id ID de l'usuari a eliminar
 * @param int $current_user_id ID de l'usuari que fa la petició
 * @param bool $is_admin Si l'usuari que fa la petició és admin
 * @return bool True si s'ha eliminat, false en cas d'error o falta de permisos
 */
function delete_user($id, $current_user_id, $is_admin) {
    // Comprovacions de seguretat
    if (!$is_admin) {
        return false; // Només admins poden eliminar
    }
    if ($id == $current_user_id) {
        return false; // No es pot eliminar a si mateix
    }

    global $connexio;
    try {
        $stmt = $connexio->prepare('DELETE FROM usuarios WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * update_user_admin
 * Actualiza el estado admin de un usuario
 */
function update_user_admin($id, $is_admin) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('UPDATE usuarios SET admin = :admin WHERE id = :id');
        return $stmt->execute([':admin' => $is_admin, ':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * get_user_by_id
 * Retorna un array amb les dades de l'usuari o false si no existeix
 */
function get_user_by_id($id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * get_user_by_discord_id
 * Retorna un array amb les dades de l'usuari o false si no existeix
 */
function get_user_by_discord_id($discord_id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT * FROM usuarios WHERE discord_id = :d LIMIT 1');
        $stmt->execute([':d' => $discord_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * create_user_oauth
 * Crea un usuari amb OAuth
 */
function create_user_oauth($username, $email, $discord_id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('INSERT INTO usuarios (username, email, discord_id) VALUES (:u, :e, :d)');
        return $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':d' => $discord_id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * get_user_by_github_id
 * Retorna un array amb les dades de l'usuari o false si no existeix
 */
function get_user_by_github_id($github_id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT * FROM usuarios WHERE github_id = :g LIMIT 1');
        $stmt->execute([':g' => $github_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * create_user_oauth_github
 * Crea un usuari amb dades d'OAuth de GitHub
 */
function create_user_oauth_github($username, $email, $github_id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('INSERT INTO usuarios (username, email, github_id) VALUES (:u, :e, :g)');
        return $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':g' => $github_id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * update_user_github_link
 * Enlaza un usuario existente con GitHub (guarda github_id)
 */
function update_user_github_link($userId, $github_id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('UPDATE usuarios SET github_id = :g WHERE id = :id');
        return $stmt->execute([':g' => $github_id, ':id' => $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * validate_reset_token
 * Valida si un token de reset és vàlid i retorna l'ID de l'usuari si ho és
 */
function validate_reset_token($token) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1');
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user['id'] : false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * reset_user_password
 * Restableix la contrasenya d'un usuari utilitzant el token de reset
 */
function reset_user_password($token, $new_password) {
    global $connexio;
    try {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $connexio->prepare('UPDATE usuarios SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ? LIMIT 1');
        return $stmt->execute([$hashed_password, $token]);
    } catch (PDOException $e) {
        return false;
    }
}

function regenerate_user_api_key($userId) {
    global $connexio;
    try {
        $maxAttempts = 10;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $newApiKey = bin2hex(random_bytes(16)); // Genera una nueva API key de 32 caracteres hexadecimales
            $hashedApiKey = hash('sha256', $newApiKey); // Hashea la API key para mayor seguridad
            
            // Comprobar si el hash ya existe en la base de datos
            $checkStmt = $connexio->prepare('SELECT id FROM usuarios WHERE api_key = :k LIMIT 1');
            $checkStmt->execute([':k' => $hashedApiKey]);
            if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
                // No existe, proceder a guardar
                $stmt = $connexio->prepare('UPDATE usuarios SET api_key = :k WHERE id = :id');
                if ($stmt->execute([':k' => $hashedApiKey, ':id' => $userId])) {
                    return ['success' => true, 'new_api_key' => $newApiKey, 'messages' => ['API key regenerada correctament.']];
                } else {
                    return ['success' => false, 'messages' => ['Error en regenerar la API key.']];
                }
            }
        }
        // Si después de 10 intentos no se encuentra una única, devolver error
        return ['success' => false, 'messages' => ['No se pudo generar una API key única después de varios intentos.']];
    } catch (PDOException $e) {
        return ['success' => false, 'messages' => ['Excepció en regenerar la API key: ' . $e->getMessage()]];
    }
}

/**
 * get_user_password
 * Retorna la contrasenya hasheada d'un usuari
 */
function get_user_password($user_id) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT password FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['password'] : null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * update_reset_token
 * Actualiza el token de reset y su fecha de expiración
 */
function update_reset_token($user_id, $token, $expires_at) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('UPDATE usuarios SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
        return $stmt->execute([$token, $expires_at, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * get_user_by_email
 * Retorna un array amb les dades de l'usuari o false si no existeix
 */
function get_user_by_email($email) {
    global $connexio;
    try {
        $stmt = $connexio->prepare('SELECT id, username FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : false;
    } catch (PDOException $e) {
        return false;
    }
}

function is_valid_api_key($apiKey) {
    global $connexio;
    try {
        $hashedApiKey = hash('sha256', $apiKey);
        $stmt = $connexio->prepare('SELECT id FROM usuarios WHERE api_key = :k LIMIT 1');
        $stmt->execute([':k' => $hashedApiKey]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

?>
