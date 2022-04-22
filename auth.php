<?php
require_once 'classes/authentication/Auth.php';
require_once 'classes/Response.php';

$_auth = new Auth;
$_response = new Response;
$res = array();

//Iniciamos el proceso de reseteo de clave con GET
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	//tomamos los headers (donde deben venir user o email)
	$headers = getallheaders();
	// enviamos los datos al handler de reseteo de contraseña
	$res = $_auth->forget($headers);
	//devolvemos una respuesta
	header('Content-Type: application/json');
	if (isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res['result']);

	// Autenticamos usuarios con POST 
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
	//recibimos los datos del body
	$req = file_get_contents('php://input');
	$json = json_decode($req, true);
	// Si existe una contraseña en el objeto enviado, significa que autenticaremos la entrada
	// al sistema
	if (isset($json['password'])) {
		// enviamos los datos al handler de login
		$res = $_auth->login($req);
	} else
	// Caso contrario, crearemos un token para las respuestas enviadas para reset de contraseña
	// si estas son válidas, claro está
	if (isset($json['answerOne']) && isset($json['answerTwo'])) {
		// enviamos los datos al handler de answer
		$res = $_auth->answer($req);
	} else

	if (!isset($json['password']) || !(isset($json['answerOne']) && isset($json['answerTwo']))){
		$res = $_response->error_400();
	}
	//devolvemos una respuesta
	header('Content-Type: application/json');
	if (isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res["result"]);

	//Actualizamos la contraseña con PUT
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
	//Tomamos la contraseña del body
	$req = file_get_contents('php://input');
	// debe contener el id del usuario, la contraseña nueva y el token de validación.
	// enviamos los datos al handler de resetpasword
	$res = $_auth->reset($req);

	//devolvemos una respuesta
	header('Content-Type: application/json');
	if (isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res["result"]);
} else {
	// si el método es diferente a POST.  Generamos un error 405
	header('Content-Type: application/json');
	$res = $_response->error_405();
	echo json_encode($res["result"]);
}
