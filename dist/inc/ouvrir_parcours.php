<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

session_start();

if (!empty($_POST['parcours']) && !empty($_POST['question']) && !empty($_POST['reponse'])) {
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
	$question = $_POST['question'];
	$reponse = strtolower($_POST['reponse']);
	$stmt = $db->prepare('SELECT question, reponse FROM digisteps_parcours WHERE url = :url');
	if ($stmt->execute(array('url' => $parcours))) {
		$resultat = $stmt->fetchAll();
		if ($question === $resultat[0]['question'] && password_verify($reponse, $resultat[0]['reponse'])) {
			$_SESSION['digisteps'][$parcours]['reponse'] = $resultat[0]['reponse'];
			echo 'parcours_debloque';
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
