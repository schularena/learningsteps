<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (!empty($_FILES['fichier']) && !empty($_POST['parcours'])) {
	$parcours = $_POST['parcours'];
	$extension = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
	if (!file_exists('../fichiers/' . $parcours)) {
		mkdir('../fichiers/' . $parcours, 0775, true);
	}
	$nom = hash('md5', $_FILES['fichier']['tmp_name']) . time() . '.' . $extension;
	$fichier = '../fichiers/' . $parcours . '/' . $nom;
	if (move_uploaded_file($_FILES['fichier']['tmp_name'], $fichier)) {
		echo $nom;
	} else {
		echo 'erreur';
	}
	exit();
} else {
	header('Location: /');
}

?>
