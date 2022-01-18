<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

$_POST = json_decode(file_get_contents('php://input'), true);

if (!empty($_POST['parcours']) && !empty($_POST['donnees'])) {
	try {
		$db = new PDO('sqlite:'. dirname(__FILE__) . '/digisteps.db');
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(Exception $e) {
		echo 'Erreur lors de la connexion à la base de données : ' . $e->getMessage();
		die();
	}
	$parcours = $_POST['parcours'];
	$donnees = $_POST['donnees'];
	$stmt = $db->prepare('UPDATE digisteps_parcours SET donnees = :donnees WHERE url = :url');
	if ($stmt->execute(array('donnees' => json_encode($donnees), 'url' => $parcours))) {
		if (!empty($_POST['fichier']) && $_POST['fichier'] !== '') {
			if (file_exists('../fichiers/' . $parcours . '/' . $_POST['fichier'])) {
				unlink('../fichiers/' . $parcours . '/' . $_POST['fichier']);
			}
		}
		echo 'travail_depose';
	} else {
		echo 'erreur';
	}
	$db = null;
	exit();
} else {
	header('Location: /');
}

?>
