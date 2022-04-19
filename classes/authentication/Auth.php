<?php
require_once "classes/conection/Conection.php";
require_once "classes/Response.php";

class Auth extends Conection {
	
	public function login($json) {
		$_response = new Response;
		$data = json_decode($json,true);
		$params= array();
		$headers = getallheaders();
		$params['browser'] = $headers['User-Agent'];
		// Validamos que venga el user y el password.
		if(!isset($data['user']) || !isset($data['password'])){
			// Sí no viene alguno de los dos enviamos un error 400.
			return $_response->error_400();
		} else {
			// Si existen ambos campos entonces procedemos a almacenarlos en variables...
			$user = $data['user'];		
			$password = $data['password'];

			//preparamos los parámetros a pasar a la creación del token si no vienen, se definen como NULL, haciendo que el token sea valido por siempre
			if (isset($data['expiration'])) {
				$params['expiration'] = $data['expiration'];
			} else {
				$params['expiration'] = null;
			}

			$stmt = $this->getUser($user);

			//Validamos la existencia del usuario
			if ($stmt) {
				if(!($stmt[0]['state'] == 1)){
					return $_response->error_500("Usuario Eliminado. | No habilitado.");
				}
				// preparamos el id para pasarlo a la creación del token
				$params["id"] = $stmt[0]['id'];

				// Encriptamos para validar la contraseña...
				$passCrypt = parent::encrypt($password,'validate');
				//Validamos la contraseña
				if (password_verify($passCrypt,$stmt[0]['password'])) {
					//Se crea el token y lo validamos
					$token = $this->createToken($params);
					if ($token) {
						$res = array("token" => $token);
						return $_response->ok_200($res);
					} else {
						//Si falla el guardado del token
						return $_response->error_500('Generación del token fallida.');
					}
				} else {
					//Si la contraseña no es válida
					return $_response->error_200('Contraseña Inválida.');
				}
			} else {
				//si no existe el usuario
				return $_response->error_200('No existe el usuario.');
			}
		}
	}

	private function getUser($user){
		$query = "SELECT id, password, user, state from user where user.user = '$user' ";
		$res = parent::query($query);
		// valida si existe el atributo id y retorna los datos obtenidos, en caso contrario retorna false.
		if(isset($res[0]['id'])){
			return $res;
		} else {
			return false;
		}
	}


	private function createToken($params)
	{
		$act = true;
		$id = $params['id'];


		$pseudoBytes = openssl_random_pseudo_bytes(16, $act);
		$token = bin2hex($pseudoBytes);
		$date = date('Y-m-d H:i');

		$sqlstr = "INSERT INTO Token (user,token,generation_timestamp,expiration_date,browser) VALUES ('{$params['id']}','$token','$date', '{$params['expiration']}', '{$params['browser']}');";
		$res = parent::nonQuery($sqlstr);

		if ($res) {
			return $token;
		} else {
			return 0;
		}
	}









}

?>