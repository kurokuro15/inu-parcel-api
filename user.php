<?php
require_once 'classes/User.php';
require_once 'classes/Response.php';

$_user = new User;
$_response = new Response;

$method = $_SERVER['REQUEST_METHOD'];

//Si el método es GET
if ($method == "GET") {

	$res = $_user->handleGet();
	//Si queremos listar todos de forma paginada (de 20 en 20)
	// if(isset($_GET["page"])) {
		
	// 	$page = $_GET["page"];
	// 	$res = $_user->listUser($page);
	// 	header("Content-Type: application/json");
	// 	echo json_encode($res);
	// 	http_response_code(200);
	// 	return;

	header('Content-Type: application/json');
	if(isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
		
	}
	echo json_encode($res)
	;

	//por defecto devolverá los primeros 20 usuarios
	// $res = $_user->listUser();
	// echo json_encode($res);

} else 

//Si el método es POST
if ($method == "POST") {
	//tomamos los datos
	$json = file_get_contents('php://input');
	// Manejamos el post
	$res = $_user->handlePost($json);
	
	//respondemos
	header('Content-Type: application/json');
	if(isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res);
	
} else 

//Si el método es PUT
if ($method == "PUT") {
	//tomamos los datos
	$json = file_get_contents('php://input');
	// Manejamos el post
	$res = $_user->handlePut($json);
	
	//respondemos
	header('Content-Type: application/json');
	if(isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res);
	
} else 

//Si el método es DELETE
if ($method == "DELETE") {
	//tomamos los datos
	$json = file_get_contents('php://input');
	// Manejamos el post
	$res = $_user->handleDelete($json);
	
	//respondemos
	header('Content-Type: application/json');
	if(isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res);
} else {
	// Si el método no es admitido.  Generamos un error 405
	header('Content-Type: application/json');
	$res = $_response->error_405();
	echo json_encode($res);
}

