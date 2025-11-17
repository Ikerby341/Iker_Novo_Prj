<?php 

try {
	//crearem nou objecte PDO (connexió,base_de_dades,usuari,password);
	$connexio = new PDO('mysql:host=localhost;dbname=bbdd_projecte', 'root', '');
} catch(PDOException $e){ //
	// mostrarem els errors
	echo "Error: " . $e->getMessage();
}

?>