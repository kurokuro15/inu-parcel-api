<?php
require_once 'classes/Parcel.php';
require_once 'classes/Response.php';

$_parcel = new Parcel;
$_response = new Response;

$method = $_SERVER['REQUEST_METHOD'];

//Si el método es GET
if ($method == 'GET') {
	$res = $_parcel->handlerGet();

	//respondemos
	if (isset($res["result"])) {
		header('Content-Type: application/json');
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res["result"]);

	//Si el método es POST
} else if ($method == 'POST') {
	//tomamos los datos
	$json = file_get_contents('php://input');
	// Manejamos el post
	$res = $_parcel->handlerPost($json);

	//respondemos
	if (isset($res["result"])) {
		header('Content-Type: application/json');
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
		echo json_encode($res["result"]);
	}
} else {
	// Si el método no es admitido.  Generamos un error 405
	header('Content-Type: application/json');
	$res = $_response->error_405();
	echo json_encode($res["result"]);
}
