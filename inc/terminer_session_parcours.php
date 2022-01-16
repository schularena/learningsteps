<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (!empty($_POST['session']) && $_POST['session'] !== '') {
	session_id($_POST['session']);
}
session_start();

if (!empty($_POST['session'])) {
	$_SESSION = array();
	session_destroy();
	echo 'session_terminee';
	exit();
} else {
	header('Location: /');
}

?>
