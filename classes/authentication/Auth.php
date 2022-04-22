<?php
require_once "classes/conection/Conection.php";
require_once "classes/Response.php";

class Auth extends Conection
{

	public function login($json)
	{
		$_response = new Response;
		$data = json_decode($json, true);
		$params = array();
		$headers = getallheaders();
		$params['browser'] = $headers['User-Agent'];
		// Validamos que venga el user y el password.
		if (!isset($data['user']) || !isset($data['password'])) {
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
			//obtenemos al usuario para posteriormente validarlo
			$stmt = $this->getUser($user);

			//Validamos la existencia del usuario
			if ($stmt) {
				if (!($stmt[0]['state'] == 1)) {
					return $_response->error_500("Usuario Eliminado. | No habilitado.");
				}
				// preparamos el id para pasarlo a la creación del token
				$params["id"] = $stmt[0]['id'];

				// Encriptamos para validar la contraseña...
				$passCrypt = parent::encrypt($password, 'validate');
				//Validamos la contraseña
				if (password_verify($passCrypt, $stmt[0]['password'])) {
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

	// Manejador del método GET devuelve las preguntas de seguridad del usuario para resetear la contraseña
	public function forget($headers)
	{
		$_response = new Response;
		$data = $headers;
		//Validamos si existe email o user como headers
		if (!empty($data['email'])) {
			//hacemos la búsqueda por email
			$email = $data['email'];
			$sqlstr = "SELECT id, user, email, questionOne, questionTwo, state FROM user WHERE user.email = '$email'";;
		} else if (!empty($data['user'])) {
			//Hacemos la búsqueda por usuario
			$user = $data['user'];
			$sqlstr = "SELECT id, user, email, questionOne, questionTwo, state  FROM user WHERE user.user = '$user'";;
		} else {
			return $_response->error_400();
		}
		//hacemos la búsqueda del usuario/correo
		$res = parent::query($sqlstr);
		//validamos que no esté vacío
		if (($res[0]['state'] !== '1')) {
			return $_response->error_500("Usuario Eliminado. | No habilitado.");
		}
		if (isset($res[0]['id'])) {
			return $_response->ok_200($res[0]);
		} else {
			return $_response->error_200('Usuario o Correo electrónico no existentes.');
		}
	}

	// Manejador del método POST devuelve token de validación para generar la nueva contraseña al usuario
	public function answer($json)
	{
		$_response = new Response;
		//obtenemos los datos
		$data = json_decode($json, true);
		$params = array();

		//obtenemos la cabecera
		$headers = getallheaders();
		$params['browser'] = $headers['User-Agent'];

		// Validamos que venga el user y las respuestas.
		if (!(isset($data['user']) || isset($data['email'])) || !isset($data['answerOne']) || !isset($data['answerTwo'])) {
			// Sí no viene alguno de los tres enviamos un error 400.
			return $_response->error_400();
		} else {
			// Si existen los campos entonces procedemos a almacenarlos en variables...
			// Y encriptamos las respuestas para validar.
			$params['answerOne'] = parent::encrypt($data['answerOne'], 'validate');
			$params['answerTwo'] = parent::encrypt($data['answerTwo'], 'validate');

			//preparamos 5 minutos de validación para su posterior expiración
			$date = new DateTime();
			$date->modify('+5 minutes');
			$params['expiration'] = $date->format('Y-m-d H:i');

			//obtenemos las respuestas anteriores para posteriormente validarlo
			if (!empty($data['email'])) {
				$params['email'] = $data['email'];
				$stmt = $this->getAnswer(null, $params['email']);
			}
			if (!empty($data['user'])) {
				$params['user'] = $data['user'];
				$stmt = $this->getAnswer($params['user'], null);
			}

			//Validamos si está activo el usuario
			if ($stmt) {
				if (!($stmt[0]['state'] == 1)) {
					return $_response->error_500("Usuario Eliminado. | No habilitado.");
				}
				if (!isset($stmt[0]['answerOne']) || !isset($stmt[0]['answerTwo'])) {
					return $_response->error_401("No se definieron respuestas de seguridad.");
				}
				// preparamos el id para pasarlo a la creación del token
				$params["id"] = $stmt[0]['id'];

				//Validamos las respuestas
				if (password_verify($params['answerOne'], $stmt[0]['answerOne']) && password_verify($params['answerTwo'], $stmt[0]['answerTwo'])) {
					//Se crea el token y lo validamos
					$token = $this->createToken($params);
					// validamos la existencia del token para retornar la respuesta
					if ($token) {
						$res = array("token" => $token);
						return $_response->ok_200($res);
					} else {
						//Si falla el guardado del token
						return $_response->error_500('Generación del token fallida.');
					}
				} else {
					//Si las respuestas son inválidas
					return $_response->error_200('Respuestas inválidas, inténtelo de nuevo.');
				}
			} else {
				//si no existe el usuario
				return $_response->error_200('No existe el usuario.');
			}
		}
	}

	//función para manejar la petición PUT para  cambio de contraseña
	public function reset($json)
	{
		$_response = new Response;
		$headers = getallheaders();
		$data = json_decode($json, true);
		$params = array();

		// Validamos que venga el token en el header
		if (empty($headers['token'])) {
			return	$_response->error_401('Acción no autorizada. Falta token de autenticación.');
		}

		// Validamos que el token esté sea válido y existente
		$token = parent::getToken($headers['token']);
		if (!$token) {
			return	$_response->error_401('No autorizado. Token inválido.');
		}

		// Ahora validamos que existe la nueva contraseña en la petición
		if (!isset($data['password'])) {
			// Sí no viene enviamos un error 400.
			return $_response->error_400();
		} else {
			// Si existe entonces procedemos a almacenarlo ...
			$password = $data['password'];

			//obtenemos al usuario para validar
			$stmt = $this->getUser($token[0]['user']);

			//Validamos la existencia del usuario
			if ($stmt) {
				//verificamos que esté activo
				if (!($stmt[0]['state'] == 1)) {
					return $_response->error_500("Usuario Eliminado. | No habilitado.");
				}

				// Encriptamos la contraseña nueva y mapeamos al usuario...
				$params["password"] = parent::encrypt($password);
				$params["user"] = $stmt[0]['user'];
				$params["id"] = $stmt[0]['id'];
				//Actualizamos la contraseña y devolvemos el id y si fue o no afectado.
				$affected = $this->updatePassword($params);
				if (isset($affected)) {
					$res = array("id" => $params['id'], "affected_row" => $affected);
					return $_response->ok_200($res);
				} else {
					return $_response->error_500();
				}
			} else {
				//Si algo no se ejecutó la query exitosamente
				return $_response->error_400();
			}
		}
	}

	// método para obtener un usuario y devolverlo
	private function getUser($user)
	{
		$query = "SELECT id, password, user, state from user where user.user = '$user' OR user.id ='$user'";
		$res = parent::query($query);
		// valida si existe el atributo id y retorna los datos obtenidos, en caso contrario retorna false.
		if (isset($res[0]['id'])) {
			return $res;
		} else {
			return false;
		}
	}

	// método para obtener las respuestas de un usuario.
	private function getAnswer($user, $email)
	{
		if (isset($email))
			$query = "SELECT id, user, answerOne, answerTwo, state from user where user.email = '$email'";
		if (isset($user))
			$query = "SELECT id, user, answerOne, answerTwo, state from user where user.user = '$user'";
		$res = parent::query($query);
		// valida si existe el atributo id y retorna los datos obtenidos, en caso contrario retorna false.
		if (isset($res[0]['id'])) {
			return $res;
		} else {
			return false;
		}
	}

	// método para crear token en db y devolverlo
	private function createToken($params)
	{
		$act = true;
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

	// método para camabio de contraseña en db y devolverlo
	private function updatePassword($params)
	{
		$query = "UPDATE user SET password = '{$params['password']}' WHERE user.id = '{$params['id']}' AND user.user = '{$params['user']}';";
		$stmt = parent::nonQuery($query);

		if (isset($stmt)) {
			return $stmt;
		} else {
			return 0;
		}
	}
}
