<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

session_start();

if (!empty($_POST['parcours']) && !empty($_POST['question']) && !empty($_POST['reponse']) && !empty($_POST['nouvellequestion']) && !empty($_POST['nouvellereponse'])) {
	try {
		$db = new PDO('sqlite:'. dirname(__FILE__) . '/digisteps.db');
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(Exception $e) {
		echo 'Erreur lors de la connexion à la base de données : ' . $e->getMessage();
		die();
	}
	$parcours = $_POST['parcours'];
	$question = $_POST['question'];
	$reponse = strtolower($_POST['reponse']);
	$stmt = $db->prepare('SELECT question, reponse FROM digisteps_parcours WHERE url = :url');
	if ($stmt->execute(array('url' => $parcours))) {
		$resultat = $stmt->fetchAll();
		if ($question === $resultat[0]['question'] && password_verify($reponse, $resultat[0]['reponse'])) {
			$nouvellequestion = $_POST['nouvellequestion'];
			$nouvellereponse = password_hash(strtolower($_POST['nouvellereponse']), PASSWORD_DEFAULT);
			$stmt = $db->prepare('UPDATE digisteps_parcours SET question = :nouvellequestion, reponse = :nouvellereponse WHERE url = :url');
			if ($stmt->execute(array('nouvellequestion' => $nouvellequestion, 'nouvellereponse' => $nouvellereponse, 'url' => $parcours))) {
				$_SESSION['digisteps'][$parcours]['reponse'] = $nouvellereponse;
				echo 'acces_modifie';
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
