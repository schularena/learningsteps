<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

session_start();

if (!empty($_POST['id'])) {
	try {
		$db = new PDO('sqlite:'. dirname(__FILE__) . '/digisteps.db');
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(Exception $e) {
		echo 'Erreur lors de la connexion à la base de données : ' . $e->getMessage();
		die();
	}
	$reponse = '';
	$id = $_POST['id'];
	if (isset($_SESSION['digisteps'][$id]['reponse'])) {
		$reponse = $_SESSION['digisteps'][$id]['reponse'];
	}
	$stmt = $db->prepare('SELECT * FROM digisteps_parcours WHERE url = :url');
	if ($stmt->execute(array('url' => $id))) {
		$parcours = $stmt->fetchAll();
		$admin = false;
		if (count($parcours, COUNT_NORMAL) > 0 && $parcours[0]['reponse'] === $reponse) {
			$admin = true;
		}
		$donnees = $parcours[0]['donnees'];
		if ($donnees !== '') {
			$donnees = json_decode($donnees);
		}
		echo json_encode(array('nom' => $parcours[0]['nom'], 'donnees' => $donnees, 'admin' =>  $admin));
	} else {
		echo 'erreur';
	}
	$db = null;
	exit();
} else {
	header('Location: /');
}

?>
