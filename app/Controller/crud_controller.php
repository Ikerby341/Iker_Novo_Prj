<?php
    // Incluïm el controlador principal que inicia la sessió
    include_once __DIR__ .'/controlador.php';
    
    // Incluïm el model d'articles que conté la lògica de negoci i accés a dades
    include_once __DIR__ .'/../Model/articles_model.php';

    /**
     * Funció per inserir una nova dada
     * @param string $marca - La marca del nou article
     * @param string $model - El model del nou article
     * @return mixed - Retorna el resultat de l'operació d'inserció
     */
    function inserirDada($marca, $model, $ruta_img = null) {
        return inserir($marca, $model, $ruta_img);
    }

    /**
     * Funció per modificar una dada existent
     * @param int $id - ID del registre a modificar
     * @param string $camp - Nom del camp que es vol modificar
     * @param string $dadaN - Nova dada que s'inserirà
     * @return mixed - Retorna el resultat de l'operació de modificació
     */
    function modificarDada($id, $camp, $dadaN) {
        return modificar($id, $camp, $dadaN);
    }

    /**
     * Funció per esborrar una dada
     * @param int $id - ID del registre a esborrar
     * @return mixed - Retorna el resultat de l'operació d'esborrat
     */
    function esborrarDada($id) {
        return esborrar($id);
    }

    function modificarUsername($id, $newUsername) {
        return modificarUsernameInDB($id, $newUsername);
    }
    
    /**
     * Modifica l'email d'un usuari
     */
    function modificarEmail($id, $newEmail) {
        return modificarEmailInDB($id, $newEmail);
    }

    /**
     * Procesa la creació d'un nou article (POST de create.php)
     * Retorna el missatge d'error o èxit
     */
    function process_create_article($titol, $cos, $image_file = null) {
        $titol = trim($titol);
        $cos = trim($cos);

        // Validacions bàsiques
        if (empty($titol)) {
            return 'La marca no pot estar buida';
        }
        if (empty($cos)) {
            return 'El model no pot estar buit';
        }

        $ruta_db = null;

        // Processar la imatge si s'ha pujat
        if ($image_file && isset($image_file['tmp_name']) && is_uploaded_file($image_file['tmp_name'])) {
            $file = $image_file;
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif', 'image/webp' => '.webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (array_key_exists($mime, $allowed)) {
                    $ext = $allowed[$mime];
                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                    $unique = $safeName . '_' . time() . bin2hex(random_bytes(4)) . $ext;
                    $destDir = __DIR__ . '/../../public/assets/img';
                    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                    $destDir = realpath($destDir);
                    $destPath = $destDir . DIRECTORY_SEPARATOR . $unique;
                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $ruta_db = 'public/assets/img/' . $unique;
                    } else {
                        return 'Error al desar la imatge.';
                    }
                } else {
                    return 'Tipus de fitxer de imatge no permès.';
                }
            }
        }

        // Inserir article
        return inserirDada($titol, $cos, $ruta_db);
    }

    /**
     * Procesa la modificació d'un article (POST de update.php)
     * Retorna array('success'=>bool, 'message'=>string)
     */
    function process_update_article($id, $camp, $dadaN, $image_file = null) {
        $id = (int)$id;
        $camp = trim($camp);
        $dadaN = trim($dadaN);

        // Validacions bàsiques
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID invàlida'];
        }
        if (empty($camp)) {
            return ['success' => false, 'message' => 'Camp no pot estar buit'];
        }
        if ($camp !== 'ruta_img' && empty($dadaN)) {
            return ['success' => false, 'message' => 'Dada nova no pot estar buida'];
        }

        try {
            require_once __DIR__ . '/../../config/db-connection.php';
            global $connexio;
            $stmt = $connexio->prepare('SELECT owner_id, ruta_img FROM coches WHERE ID = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'message' => 'Article no trobat'];
            }
            if ((int)$row['owner_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
                return ['success' => false, 'message' => 'No tens permís per modificar aquest article'];
            }

            // Si s'està actualitzant la imatge
            if ($camp === 'ruta_img') {
                if (!$image_file || !isset($image_file['tmp_name']) || !is_uploaded_file($image_file['tmp_name'])) {
                    return ['success' => false, 'message' => 'No s\'ha pujat cap imatge.'];
                }

                $file = $image_file;
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    return ['success' => false, 'message' => 'Error en la pujada de la imatge.'];
                }

                $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif', 'image/webp' => '.webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!array_key_exists($mime, $allowed)) {
                    return ['success' => false, 'message' => 'Tipus de fitxer no permès.'];
                }

                $ext = $allowed[$mime];
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $unique = $safeName . '_' . time() . bin2hex(random_bytes(4)) . $ext;
                $destDir = __DIR__ . '/../../public/assets/img';
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                $destDir = realpath($destDir);
                $destPath = $destDir . DIRECTORY_SEPARATOR . $unique;

                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    return ['success' => false, 'message' => 'Error al desar la imatge.'];
                }

                $ruta_db = 'public/assets/img/' . $unique;
                $message = modificarDada($id, 'ruta_img', $ruta_db);

                // Esborrar imatge anterior si no és la default
                if (strpos($message, 'correctament') !== false || strpos($message, 'actualitzat') !== false) {
                    $prevRuta = $row['ruta_img'] ?? null;
                    $default = 'public/assets/img/default.webp';
                    if (!empty($prevRuta) && $prevRuta !== $default) {
                        $prevPath = realpath(__DIR__ . '/../../' . $prevRuta);
                        if ($prevPath && file_exists($prevPath)) {
                            @unlink($prevPath);
                        }
                    }
                    return ['success' => true, 'message' => $message];
                }
                return ['success' => false, 'message' => $message];
            } else {
                // Actualització d'un camp normal
                $message = modificarDada($id, $camp, $dadaN);
                if (strpos($message, 'correctament') !== false || strpos($message, 'actualitzat') !== false) {
                    return ['success' => true, 'message' => $message];
                }
                return ['success' => false, 'message' => $message];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error a la base de dades: ' . $e->getMessage()];
        }
    }

    /**
     * Procesa l'esborrat d'un article (POST de delete.php)
     * Retorna array('success'=>bool, 'message'=>string)
     */
    function process_delete_article($id) {
        $id = (int)$id;

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID invàlida'];
        }

        try {
            require_once __DIR__ . '/../../config/db-connection.php';
            global $connexio;
            $stmt = $connexio->prepare('SELECT owner_id FROM coches WHERE ID = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'message' => 'Article no trobat'];
            }
            if ((int)$row['owner_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
                return ['success' => false, 'message' => 'No tens permís per esborrar aquest article'];
            }

            $message = esborrarDada($id);
            if (strpos($message, 'correctament') !== false || strpos($message, 'esborrat') !== false) {
                return ['success' => true, 'message' => $message];
            }
            return ['success' => false, 'message' => $message];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error en la base de dades: ' . $e->getMessage()];
        }
    }

    /**
     * Procesa l'edició del perfil d'usuari (POST de editprofile.php)
     * Retorna array('messages'=>array, 'updated_data'=>array)
     */
    function process_edit_profile($user_id, $new_name, $new_email) {
        $new_name = trim($new_name);
        $new_email = trim($new_email);
        $msgs = [];

        if (!$user_id) {
            $msgs[] = 'ID d\'usuari no disponible.';
            return ['messages' => $msgs, 'updated_data' => ['name' => null, 'email' => null]];
        }

        $current_name = $_SESSION['username'] ?? '';
        $current_user = get_user_by_username($current_name);
        $current_email = $current_user['email'] ?? '';

        // Validar i actualitzar nom d'usuari
        if ($new_name !== '' && $new_name !== $current_name) {
            if (user_exists_by_username($new_name)) {
                $msgs[] = 'Aquest nom d\'usuari ja existeix. Tria un altre.';
            } else {
                if (modificarUsername($user_id, $new_name)) {
                    $msgs[] = 'Nom d\'usuari actualitzat correctament.';
                    $_SESSION['username'] = $new_name;
                } else {
                    $msgs[] = 'Error al actualitzar el nom d\'usuari.';
                }
            }
        }

        // Validar i actualitzar email
        if ($new_email !== '' && $new_email !== $current_email) {
            if (modificarEmail($user_id, $new_email)) {
                $msgs[] = 'Email actualitzat correctament.';
            } else {
                $msgs[] = 'Error al actualitzar l\'email.';
            }
        }

        if (empty($msgs)) {
            $msgs[] = 'No s\'ha realitzat cap canvi.';
        }

        return [
            'messages' => $msgs,
            'updated_data' => ['name' => $new_name, 'email' => $new_email]
        ];
    }

    /**
     * Procesa l'edició d'usuari per admin (POST de edit_user.php)
     * Retorna array('messages'=>array)
     */
    function process_edit_user_admin($user_id, $new_name, $new_email, $new_admin) {
        $new_name = trim($new_name);
        $new_email = trim($new_email);
        $new_admin = (int)$new_admin;
        $msgs = [];

        // Obtenir dades actuals
        $users = get_all_users();
        $user = null;
        foreach ($users as $u) {
            if ($u['id'] == $user_id) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            $msgs[] = 'Usuari no trobat.';
            return ['messages' => $msgs];
        }

        // Actualitzar nom d'usuari
        if ($new_name !== '' && $new_name !== $user['username']) {
            if (user_exists_by_username($new_name)) {
                $msgs[] = 'Aquest nom d\'usuari ja existeix.';
            } else {
                if (modificarUsername($user_id, $new_name)) {
                    $msgs[] = 'Nom d\'usuari actualitzat.';
                } else {
                    $msgs[] = 'Error al actualitzar el nom d\'usuari.';
                }
            }
        }

        // Actualitzar email
        if ($new_email !== '' && $new_email !== $user['email']) {
            if (modificarEmail($user_id, $new_email)) {
                $msgs[] = 'Email actualitzat.';
            } else {
                $msgs[] = 'Error al actualitzar l\'email.';
            }
        }

        // Actualitzar estat admin
        if ($new_admin !== (int)$user['admin']) {
            if (update_user_admin($user_id, $new_admin)) {
                $msgs[] = 'Estat admin actualitzat.';
            } else {
                $msgs[] = 'Error al actualitzar l\'estat admin.';
            }
        }

        if (empty($msgs)) {
            $msgs[] = 'No s\'ha realitzat cap canvi.';
        }

        return ['messages' => $msgs];
    }

    /**
     * Procesa l'esborrat d'usuari per admin (POST de admin.php)
     * Retorna array('success'=>bool, 'message'=>string)
     */
    function process_delete_user_admin($user_id) {
        $user_id = (int)$user_id;

        if ($user_id <= 0) {
            return ['success' => false, 'message' => 'ID d\'usuari invàlida'];
        }

        if (delete_user($user_id)) {
            return ['success' => true, 'message' => 'Usuari esborrat correctament.'];
        } else {
            return ['success' => false, 'message' => 'Error a l\'esborrar l\'usuari.'];
        }
    }
?>