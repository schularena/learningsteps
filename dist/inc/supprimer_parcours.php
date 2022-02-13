<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

session_start();

if (!empty($_POST['parcours'])) {
	try {
		$db = new PDO('sqlite:'. dirname(__FILE__) . '/digisteps.db');
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(Exception $e) {
		echo 'Erreur lors de la connexion à la base de données : ' . $e->getMessage();
		die();
	}
	$parcours = $_POST['parcours'];
	$stmt = $db->prepare('DELETE FROM digisteps_parcours WHERE url = :url');
	if ($stmt->execute(array('url' => $parcours))) {
		if (file_exists('../fichiers/' . $parcours)) {
			supprimer('../fichiers/' . $parcours);
		}
		echo 'parcours_supprime';
	} else {
		echo 'erreur';
	}
	$db = null;
	exit();
} else {
	header('Location: /');
}

function supprimer($path) {
	if (is_dir($path) === true) {
		$files = array_diff(scandir($path), array('.', '..'));
		foreach ($files as $file) {
			supprimer(realpath($path) . '/' . $file);
		}
		return rmdir($path);
	} else if (is_file($path) === true) {
		return unlink($path);
	}
	return false;
}

?>
