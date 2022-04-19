<?php
class Response {

	private $response = [
		"status"=> "ok",
		"status_code" => "200",
		"result"=> array()
	];

	public function ok_200($results) {
		$this->response['status'] = "ok";
		$this->response['status_code'] = 200;
		$this->response['result'] = $results;
		return $this->response;
	}


	public function error_200($string = 'Datos incorrectos'){
		$this->response['status'] = "error";
		$this->response['status_code'] = 200;
		$this->response['result'] = array(
			"error_id" => "200",
			"error_msg" => $string
		);
		return $this->response;
	}

	public function error_405(){
		$this->response['status'] = "error";
		$this->response['status_code'] = 405;
		$this->response['result'] = array(
			"error_id" => "405",
			"error_msg" => "Metodo no permitido"
		);
		return $this->response;
	}

	public function error_400(){
		$this->response['status'] = "error";
		$this->response['status_code'] = 400;
		$this->response['result'] = array(
			"error_id" => "400",
			"error_msg" => "Solicitud erronea, datos incompletos o formato invalido"
		);
		return $this->response;
	}
	public function error_401($string = 'No autorizado.'){
		$this->response['status'] = "error";
		$this->response['status_code'] = 500;
		$this->response['result'] = array(
			"error_id" => "401",
			"error_msg" => $string
		);
		return $this->response;
	}

	public function error_500($string = 'Error interno del Servidor.'){
		$this->response['status'] = "error";
		$this->response['status_code'] = 500;
		$this->response['result'] = array(
			"error_id" => "500",
			"error_msg" => $string
		);
		return $this->response;
	}
}