<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

session_start();

if (!empty($_POST['parcours']) && !empty($_POST['nouveaunom'])) {
	try {
		$db = new PDO('sqlite:'. dirname(__FILE__) . '/digisteps.db');
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(Exception $e) {
		echo 'Erreur lors de la connexion à la base de données : ' . $e->getMessage();
		die();
	}
	$reponse = '';
	$parcours = $_POST['parcours'];
	if (isset($_SESSION['digisteps'][$parcours]['reponse'])) {
		$reponse = $_SESSION['digisteps'][$parcours]['reponse'];
	}
	$stmt = $db->prepare('SELECT reponse FROM digisteps_parcours WHERE url = :url');
	if ($stmt->execute(array('url' => $parcours))) {
		$resultat = $stmt->fetchAll();
		if ($resultat[0]['reponse'] === $reponse) {
			$nouveaunom = $_POST['nouveaunom'];
			$stmt = $db->prepare('UPDATE digisteps_parcours SET nom = :nouveaunom WHERE url = :url');
			if ($stmt->execute(array('nouveaunom' => $nouveaunom, 'url' => $parcours))) {
				echo 'nom_modifie';
			} else {
				echo 'erreur';
			}
		} else {
			echo 'non_autorise';
		}
	} else {
		echo 'erreur';
	}
	$db = null;
	exit();
} else {
	header('Location: /');
}

?>
