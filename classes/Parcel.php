<?php
require_once 'classes/conection/Conection.php';
require_once 'classes/Response.php';

class parcel extends Conection
{
	private Response $response;
	private $required = [
		'user',
		'origin',
		'destination',
		'volumen',
		'weight',
		'amount',
		'value'
	];

	function __construct(Response $response = new Response)
	{
		parent::__construct();
		$this->response = $response;
	}
	// Maneja las get request de envios
	public function handlerGet()
	{
		$_response = $this->response;
		$headers = getallheaders();
		// Autenticamos la transacción
		if (empty($headers['token'])) {
			return	$_response->error_401(('Acción no autorizada. Falta token de autenticación.'));
		}
		$token = parent::getToken($headers['token']);
		if (!$token) {
			return	$_response->error_401('No autorizado. Token inválido.');
		}

		if (isset($_GET['tracking'])) {
			$res = $this->getParcel($_GET['tracking']);
		} else {
			if (isset($_GET['page']))
				$res = $this->getAllParcel($_GET['page']);
			else
				$res = $this->getAllParcel();
		}

		if ($res) {
			return $_response->ok_200($res);
		} else {
			return $_response->error_200('No se encontró nada.');
		}
	}

	// Maneja las post request de envios
	public function handlerPost($json)
	{
		$_response = $this->response;
		$headers = getallheaders();
		// Autenticamos la transacción
		if (empty($headers['token'])) {
			return	$_response->error_401('Acción no autorizada. Falta token de autenticación.');
		}
		$token = parent::getToken($headers['token']);
		if (!$token) {
			return	$_response->error_401('No autorizado. Token inválido.');
		}
		$params = array();
		$data = json_decode($json, true);

		//Validamos que existan los campos obligatorios y los mapeamos.
		for ($i = 0; $i < count($this->required); $i++) {
			if (isset($data[$this->required[$i]])) {
				$params[$this->required[$i]] = $data[$this->required[$i]];
			} else {
				return $_response->error_400();
			}
		};
		// Convertimos en JSON el origen y el destino
		$params['origin'] = json_encode($params['origin']);
		$params['destination'] = json_encode($params['destination']);

		// creamos un número de tracking para este envio
		$params['tracking'] =	$this->createTracking();

		// recuperamos el id del usuario enviado
		$user = $this->getUserId($params['user']);
		if ($user) {
			$params['user'] = $user['id'];
		} else {
			return $_response->error_200('usuario no existente');
		}

		// insertamos el envío y devolvemos el id de este.
		$parcel = $this->insertParcel($params);

		if ($parcel) {
			// actualizamos el status de la encomienda a 'pendiente'
			$status = $this->updateStatus(array("parcel" => $parcel));
			if ($status) {
				$res = array("id" => $parcel, "msg" => "Envio guardado con éxito.", "status" => "Pendiente");
				return $_response->ok_200($res);
			} else {
				return $_response->error_500();
			}
		} else {
			return $_response->error_500();
		}
	}

	// Obtenemos TODOS los envios generados en la web... (por ahora, luego generados por el user)
	private function getAllParcel($page = 1)
	{
		$limit = parent::pagination($page);
		$query = "SELECT * FROM parcel LIMIT $limit";

		$stmt = parent::query($query);
		$meta = $this->prepareNextPreviousPage($page);
		if ($stmt) {
			foreach ($stmt as $index => $value) {
				// devolvemos el nombre y apellidos concatenados del usuario
				$stmt[$index]['name'] = $this->getUserNames($value['user']);
				//Devolvemos el status de la encomienda en modo json
				$stmt[$index]['status'] = json_encode($this->getStatus($value['id']));
			}
			return ["parcel" => $stmt, "meta" => $meta];
		} else {
			return false;
		}
	}
	// preparamos la siguiente pagina y la anterior si existen 
	private function prepareNextPreviousPage($current)
	{
		$query = "SELECT COUNT(*) as records FROM parcel;";
		$records = parent::query($query)[0]['records'];
		$pages = $records / 5;
		if ($current === $pages) {
			$next = NULL;
			$prev = $current- 1;
		} else if ($current === 1) {
			$prev = NULL;
			$next = $current+ 1;
		} else if ($current < $pages) {
			$next = $current+ 1;
			$prev = $current- 1;
		} else {
			$prev = NULL;
			$next = NULL;
		}
		$nextLink = $next ? "http://localhost/api-rest/parcel?page=$next" : $next;
		$prevLink = $prev ? "http://localhost/api-rest/parcel?page=$prev" : $prev;
		return ["next" => $nextLink, "previous" => $prevLink];
	}

	// Obtenemos un envio específico
	private function getParcel($tracking)
	{
		$query = "SELECT * FROM parcel WHERE tracking = '$tracking';";

		$stmt = parent::query($query);
		if ($stmt[0]) {
			// devolvemos el nombre y apellidos concatenados del usuario
			$stmt[0]['name'] = $this->getUserNames($stmt[0]['user']);
			//Devolvemos el status de la encomienda en modo json
			$stmt[0]['status'] = json_encode($this->getStatus($stmt[0]['id']));
			return $stmt[0];
		} else {
			return false;
		}
	}
	// Obtenemos el 'nombre apellido' del usuario que hizo el envio
	private function getUserNames($user)
	{
		$query = "SELECT CONCAT(firstname,' ', lastname) as name FROM person JOIN user ON person.id = user.person_id where user.id = $user;";

		$stmt = parent::query($query);
		if ($stmt) {
			return $stmt[0]['name'];
		} else {
			return false;
		}
	}

	private function getStatus($parcelId)
	{
		$query = "SELECT status.status, description, date FROM status JOIN parcel_status ps ON ps.parcel = $parcelId ORDER BY ps.date DESC LIMIT 1;";
		$stmt = parent::query($query);
		if ($stmt) {
			return $stmt[0];
		} else {
			return false;
		}
	}
	// Inserta un envio en la db 
	private function insertParcel($params)
	{
		$query = "INSERT INTO parcel(destination, origin, volumen, weight, value, amount, tracking, user) VALUES ('{$params["destination"]}', '{$params["origin"]}', {$params["volumen"]}, {$params["weight"]}, {$params["value"]}, {$params["amount"]}, '{$params["tracking"]}', {$params["user"]});";

		$stmt = parent::nonQueryId($query);
		if ($stmt) {
			return $stmt;
		} else {
			return false;
		}
	}
	// Validamos y actualizamos el estado de un envio
	private function updateStatus($params)
	{
		$_response = $this->response;
		// validamos que exista parcel en los params
		if (empty($params['parcel'])) {
			return $_response->error_400();
		} else {
			$parcel = $params['parcel'];
		}
		// validamos que exista status, caso contrario seteamos el status default ("pendiente")
		if (empty($params['status'])) {
			$status = '1';
		} else {
			$status = $params['status'];
		}
		//preparamos la query
		$query = "INSERT INTO parcel_status (parcel, status) VALUES ('$parcel', $status)";

		//insertamos la relación intermedia
		$stmt = parent::nonQueryId($query);
		if ($stmt) {
			return $stmt;
		} else {
			return 0;
		}
	}

	private function getUserId($user)
	{
		$query = "SELECT id FROM user WHERE user.user = '$user';";
		$stmt = parent::query($query);
		if ($stmt) {
			return $stmt[0];
		} else {
			return false;
		}
	}

	private function createTracking()
	{
		// preparamos estas 'constantes' podrían venir de otro lado sí, por ahora se quedan acá.
		$serviceIndicator = "LA";
		$countryCode = "VE";

		// generamos un Serial aleatorio en forma de array para crear el verificador
		$splitSerial = $this->genSerialNumber();

		// convertimos el array en un String
		$serialNumber = implode($splitSerial);
		// generamos el dígito de verificación del serial
		$checkDigit = $this->genCheckDigit($splitSerial);
		// generamos el tracking
		$tracking = $serviceIndicator . $serialNumber . $checkDigit . $countryCode;
		//retornamos el tracking creado :D en el formato  LAXXXXXXXXVE
		return $tracking;
	}
	private function genSerialNumber()
	{
		//para que no explote el openssl_random_pseudo_bytes... :v
		$act = true;
		do {
			// creamos un número pseudo aleatorio de 8 digitos en binario
			$randomBinary = openssl_random_pseudo_bytes(3, $act);
			// lo convertimos en hex
			$hex = bin2hex($randomBinary);
			// lo convertimos en decimal
			$dec = hexdec($hex);
			// hasta que su longitud sea de 8 dígitos
		} while (strlen($dec) != 8);
		return str_split($dec);
	}

	private function genCheckDigit(array $number)
	{
		// estos son los pesos del S10 (UPU standard)
		// más información en: https://en.wikipedia.org/wiki/S10_(UPU_standard)
		$weight = [8, 6, 4, 2, 3, 5, 9, 7];
		//inicializamos
		$sum = 0;
		//iteramos y acumulamos el resultado del producto de cada digito
		for ($i = 0; $i < count($weight); $i++) {
			$sum += $weight[$i] * $number[$i];
		}
		// realizamos el cálculo del dígito de verificación
		$checkSum = 11 - ($sum % 11);
		// validamos que el dígito devuelva un valor u otro
		return $checkSum === 10 ? 0 : ($checkSum === 11 ? 5 : $checkSum);
	}
}
