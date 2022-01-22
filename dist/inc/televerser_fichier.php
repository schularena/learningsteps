<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (!empty($_FILES['blob']) && !empty($_POST['parcours'])) {
	$fichier = $_POST['fichier'];
	$ancienfichier = $_POST['ancienfichier'];
	$parcours = $_POST['parcours'];
	$extension = pathinfo($_FILES['blob']['name'], PATHINFO_EXTENSION);
	if (!file_exists('../fichiers/' . $parcours)) {
		mkdir('../fichiers/' . $parcours, 0775, true);
	}
	$nom = hash('md5', $_FILES['blob']['tmp_name']) . time() . '.' . $extension;
	$chemin = '../fichiers/' . $parcours . '/' . $nom;
	if (move_uploaded_file($_FILES['blob']['tmp_name'], $chemin)) {
		if ($ancienfichier !== '' && $fichier !== $ancienfichier) {
			if (file_exists('../fichiers/' . $parcours . '/' . $ancienfichier)) {
				unlink('../fichiers/' . $parcours . '/' . $ancienfichier);
			}
		}
		echo $nom;
	} else {
		echo 'erreur';
	}
	exit();
} else {
	header('Location: /');
}

?>
