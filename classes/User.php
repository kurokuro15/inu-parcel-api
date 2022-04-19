<?php
require_once 'classes/conection/Conection.php';

class User extends Conection

{
	private $required = [
		'user',
		'password',
		'phone',
		'email',
		'firstname',
		'lastname',
		'dni',
		'sex',
		'birthday',
		'country',
		'state',
		'municipality',
		'parish',
		'zipcode',
		'numberhouse',
		'street'
	];
	private $nonRequired = [
		'secondname',
		'secondlastname',
		'reference',
	];

	public function handleGet()
	{
		$_response = new Response;
		$headers = getallheaders();
		// Validación por token...
		if (empty($headers['token'])) {
			return	$_response->error_401('Acción no autorizada. Falta token de autenticación.');
		}
		$headers['token'];
		$token = parent::getToken($headers['token']);
		if (!$token) {
			return	$_response->error_401('No autorizado. Token inválido.');
		}
		//Si se busca un usuario
		if (isset($_GET["id"])) {
			$id = $_GET["id"];
			$res = $this->retrieveUser($id);
			return $_response->ok_200($res);
		}

		//si se busca un listado de usuario
		if (isset($_GET["page"])) {
			$page = $_GET["page"];
			$res = $this->listUser($page);
			return $_response->ok_200($res);
		}

		//si se hace solo una llamada vacía
		$res = $this->listUser();
		return $_response->ok_200($res);
	}
	//Listar todos los usuarios
	public function listUser($page = 1)
	{
		$limit = parent::pagination($page);
		$sqlstr =  "SELECT user.id as id, firstname, lastname , dni FROM user JOIN person ON person.id = user.id LIMIT $limit";
		$result = parent::query($sqlstr);
		return $result;
	}

	//Mostrar un solo usuario
	public function retrieveUser($id)
	{
		$sqlstr = "SELECT user.id, user, phone, email, firstname, secondname, lastname, secondlastname, dni, sex, birthday, country, person.state, municipality, parish, zipcode, numberhouse, street, reference FROM user INNER JOIN person ON user.id = person.id WHERE user.id = $id";
		$result = parent::query($sqlstr);
		return $result;
	}

	// Manejador del método Post
	public function handlePost($json)
	{
		$params = array();
		$_response = new Response;
		$data = json_decode($json, true);

		//Validamos que existan los campos obligatorios y los mapeamos.
		for ($i = 0; $i < count($this->required); $i++) {
			if (isset($data[$this->required[$i]])) {
				$params[$this->required[$i]] = $data[$this->required[$i]];
			} else {
				return $_response->error_400();
			}
		};
		//Mapeamos los campos no obligatorios si existen
		for ($i = 0; $i < count($this->nonRequired); $i++) {
			if (isset($data[$this->nonRequired[$i]])) {
				$params[$this->nonRequired[$i]] = $data[$this->nonRequired[$i]];
			} else {
				$params[$this->nonRequired[$i]] = '';
			}
		};
		//Insertamos el usuario y lo devolvemos
		$userId = $this->insertUser($params);
		if ($userId) {
			$res = array("id" => $userId);
			return $_response->ok_200($res);
		} else {
			return $_response->error_500();
		}
	}
	// Inserta un Usuario a la DB
	public function insertUser($params)
	{
		$sqlstrPerson = "INSERT INTO Person  (firstname,secondname,lastname,secondlastname,dni,sex,birthday,country,state,municipality,parish,zipcode,numberhouse,street,reference) VALUES ('{$params["firstname"]}',
		'{$params["secondname"]}',
		'{$params["lastname"]}',
		'{$params["secondlastname"]}',
		'{$params["dni"]}',
		'{$params["sex"]}',
		'{$params["birthday"]}',
		'{$params["country"]}',
		'{$params["state"]}',
		'{$params["municipality"]}',
		'{$params["parish"]}',
		'{$params["zipcode"]}',
		'{$params["numberhouse"]}',
		'{$params["street"]}',
		'{$params["reference"]}');";

		$stmt = parent::nonQueryId($sqlstrPerson);

		if ($stmt) {
			$params["password"] = parent::encrypt($params["password"]);
			$state = true;
			$sqlstrUser = "INSERT INTO User (user, password, phone, email, person_id, state) VALUES ('{$params["user"]}','{$params["password"]}','{$params["phone"]}','{$params["email"]}','$stmt', $state)";
			$st = parent::nonQueryId($sqlstrUser);

			if ($st) {
				return $st;
			} else {
				return 0;
			}
		}
	}

	// Maneja el método PUT
	public function handlePut($json)
	{
		$params = array();
		$_response = new Response;
		$data = json_decode($json, true);
		$headers = getallheaders();
		// Validación por token...
		if (empty($data['token'])) {
			return	$_response->error_401('Acción no autorizada. Falta token de autenticación.');
		}

		$token = parent::getToken($headers['token']);
		if (!$token) {
			return	$_response->error_401('No autorizado. Token inválido.');
		}

		// Validamos que exista un id
		if (isset($data['id'])) {
			$params['id'] = $data['id'];
		} else {
			return $_response->error_400();
		}
		//Mapeamos los campos requeridos...
		for ($i = 0; $i < count($this->required); $i++) {
			if (isset($data[$this->required[$i]])) {
				$params[$this->required[$i]] = $data[$this->required[$i]];
			}
		};
		//Mapeamos los campos no requeridos...
		for ($i = 0; $i < count($this->nonRequired); $i++) {
			if (isset($data[$this->nonRequired[$i]])) {
				$params[$this->nonRequired[$i]] = $data[$this->nonRequired[$i]];
			}
		};

		//Actualizamos el usuario y devolvemos el id
		$affected = $this->updateUser($params);
		if (isset($affected)) {
			$res = array("id" => $params['id'], "affected_row" => $affected);
			return $_response->ok_200($res);
		} else {
			return $_response->error_500();
		}
	}
	// Modifica solo datos de la persona
	private function updateUser($params)
	{
		$sqlstrPerson = "UPDATE Person SET firstname ='{$params["firstname"]}',
		secondname ='{$params["secondname"]}',
		lastname ='{$params["lastname"]}',
		secondlastname ='{$params["secondlastname"]}',
		dni ='{$params["dni"]}',
		sex ='{$params["sex"]}',
		birthday ='{$params["birthday"]}',
		country ='{$params["country"]}',
		state ='{$params["state"]}',
		municipality ='{$params["municipality"]}',
		parish ='{$params["parish"]}',
		zipcode ='{$params["zipcode"]}',
		numberhouse ='{$params["numberhouse"]}',
		street ='{$params["street"]}',
		reference ='{$params["reference"]}'
		WHERE id = {$params['id']};";

		$stmt = parent::nonQuery($sqlstrPerson);

		if (isset($stmt)) {
			return $stmt;
		} else {
			return 0;
		}
	}
	// Manejo del método DELETE
	public function handleDelete($json)
	{
		$params = array();
		$_response = new Response;
		$data = json_decode($json, true);
		$headers = getallheaders();
		// Validación por token...
		if (empty($data['token'])) {
			return	$_response->error_401('Acción no autorizada. Falta token de autenticación.');
		}
		$params['token'] = $data['token'];
		$token = parent::getToken($headers['token']);
		if (!$token) {
			return	$_response->error_401('No autorizado. Token inválido.');
		}

		// Validamos que exista un id
		if (isset($data['id'])) {
			$params['id'] = $data['id'];
		} else {
			return $_response->error_400();
		}
		$affected = $this->deleteUser($data);
		if (isset($affected)) {
			$res = array("id" => $params['id'], "affected_row" => $affected);
			return $_response->ok_200($res);
		} else {
			return $_response->error_500();
		}
	}
	// Desactiva el usuario. 
	private function deleteUser($params)
	{

		$sqlstr = "UPDATE User SET state = 0 WHERE id = {$params['id']};";
		$stmt = parent::nonQuery($sqlstr);

		if (isset($stmt)) {
			return $stmt;
		} else {
			return 0;
		}
	}
}


// USER_TABLE
// (!isset($data['user']) ||
// !isset($data['password']) ||
// !isset($data['phone']) ||
// !isset($data['email']) ||
// !isset($data['firstname']) ||
// !isset($data['secondname']) ||
// !isset($data['lastname']) ||
// !isset($data['secondlastname']) ||
// !isset($data['dni']) ||
// !isset($data['sex']) ||
// !isset($data['birthday']) ||
// !isset($data['country']) ||
// !isset($data['state']) ||
// !isset($data['municipality']) ||
// !isset($data['parish']) ||
// !isset($data['zipcode']) ||
// !isset($data['numberhouse']) ||
// !isset($data['street']) ||
// !isset($data['reference']))
// id_person
// PERSON_TABLE