<?php
require_once 'classes/authentication/Auth.php';
require_once 'classes/Response.php';

$_auth = new Auth;
$_response = new Response;


if($_SERVER['REQUEST_METHOD'] == "POST"){
	//recibimos los datos
	$req = file_get_contents('php://input');
	
	// enviamos los datos al handler de login
	$res = $_auth->login($req);

	//devolvemos una respuesta
	header('Content-Type: application/json');
	if(isset($res["result"])) {
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

?>