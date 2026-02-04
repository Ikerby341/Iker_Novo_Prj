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
 * Elimina un usuario por ID
 */
function delete_user($id) {
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
 * Crea un usuari amb OAuth, sense contrasenya
 */
function create_user_oauth($username, $email, $discord_id, $oauth_provider = 'discord') {
    global $connexio;
    try {
        $stmt = $connexio->prepare('INSERT INTO usuarios (username, email, discord_id, oauth_provider) VALUES (:u, :e, :d, :p)');
        return $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':d' => $discord_id,
            ':p' => $oauth_provider
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

?>
