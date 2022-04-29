<?php
require_once 'classes/User.php';
require_once 'classes/Response.php';

$_user = new User;
$_response = new Response;

$method = $_SERVER['REQUEST_METHOD'];

//Si el método es GET
if ($method == "GET") {

	//Manejamos el metodo GET
	$res = $_user->handleGet();
 //Enviamos los resultados...
	header('Content-Type: application/json');
	if (isset($res["result"])) {
		$statusCode = $res['status_code'];
		http_response_code(($statusCode));
	}
	echo json_encode($res);
} else

	//Si el método es POST
	if ($method == "POST") {
		//tomamos los datos
		$json = file_get_contents('php://input');
		// Manejamos el post
		$res = $_user->handlePost($json);

		//respondemos
		header('Content-Type: application/json');
		if (isset($res["result"])) {
			$statusCode = $res['status_code'];
			http_response_code(($statusCode));
		}
		echo json_encode($res["result"]);
	} else

		//Si el método es PUT
		if ($method == "PUT") {
			//tomamos los datos
			$json = file_get_contents('php://input');
			// Manejamos el post
			$res = $_user->handlePut($json);

			//respondemos
			header('Content-Type: application/json');
			if (isset($res["result"])) {
				$statusCode = $res['status_code'];
				http_response_code(($statusCode));
			}
			echo json_encode($res["result"]);
		} else

			//Si el método es DELETE
			if ($method == "DELETE") {
				//tomamos los datos
				$json = file_get_contents('php://input');
				// Manejamos el post
				$res = $_user->handleDelete($json);

				//respondemos
				header('Content-Type: application/json');
				if (isset($res["result"])) {
					$statusCode = $res['status_code'];
					http_response_code(($statusCode));
				}
				echo json_encode($res["result"]);
			} else {
				// Si el método no es admitido.  Generamos un error 405
				header('Content-Type: application/json');
				$res = $_response->error_405();
				echo json_encode($res["result"]);
			}
