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
?>