<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

$_POST = json_decode(file_get_contents('php://input'), true);

if (!empty($_POST['session']) && $_POST['session'] !== '') {
	session_id($_POST['session']);
}
session_start();
$reponse = '';
if (isset($_SESSION['reponse'])) {
	$reponse = $_SESSION['reponse'];
}

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
	$stmt = $db->prepare('SELECT reponse FROM digisteps_parcours WHERE url = :url');
	if ($stmt->execute(array('url' => $parcours))) {
		$resultat = $stmt->fetchAll();
		if ($resultat[0]['reponse'] === $reponse) {
			$donnees = $_POST['donnees'];
			$stmt = $db->prepare('UPDATE digisteps_parcours SET donnees = :donnees WHERE url = :url');
			if ($stmt->execute(array('donnees' => json_encode($donnees), 'url' => $parcours))) {
				if (!empty($_POST['fichiers'])) {
					$fichiers = json_decode($_POST['fichiers'], true);
					foreach ($fichiers as $fichier) {
						if (file_exists('../fichiers/' . $parcours . '/' . $fichier)) {
							unlink('../fichiers/' . $parcours . '/' . $fichier);
						}
					}
				}
				echo 'parcours_modifie';
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
