<?php
class Conection {
	private $conection;
	private $server;
	private $user;
	private $password;
	private $database;
	private $port;

	function __construct(){
		$credential = $this->getFileData();
		foreach ($credential as $key => $value){
			$this->server = $value['server'];
			$this->user = $value['user'];
			$this->password = $value['password'];
			$this->database = $value['database'];
			$this->port = $value['port'];
		}
		try{
			$this->conection = new mysqli($this->server,$this->user,$this->password,$this->database,$this->port);
			if (mysqli_connect_errno()) {
				throw new Exception("No se pudo conectar a la base de datos.");   
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());   
		}
}

	private function getFileData($file = "config"){
		$dir = dirname(__FILE__);
		$json = file_get_contents($dir . "/" . $file);
		return json_decode($json, true);
	}

	private function toUTF8($array) {
		array_walk_recursive($array,function(&$item,$key){
			if(!mb_detect_encoding($item,'utf-8',true)){
				$item = utf8_encode($item);
			}
		});
		return $array;
	}

	public function query($sqlstr){
		$results = $this->conection->query($sqlstr);
		$resArr = array();
		foreach($results as $key){
			$resArr[] = $key;
		}
		return $this->toUTF8($resArr);
	}

	public function nonQuery($sqlstr){
		$this->conection->query($sqlstr);
		return $this->conection->affected_rows;
	}

	public function nonQueryId($sqlstr){
		$results = $this->conection->query($sqlstr);
		$filas = $this->conection->affected_rows;
		if($filas >= 1) { 
			return $this->conection->insert_id;
		} else {
			return 0;
		}
	}

	public function encrypt($str,$operation='create') {
		$json = $this->getFileData("secret");
		$secret = $json['secret'];
		$vaulted = hash_hmac("sha256",$str,$secret);
		
		if($operation == 'create'){
			return password_hash($vaulted,PASSWORD_ARGON2ID);
		} else if($operation == 'validate'){
			return $vaulted;
		}
	}

	protected function pagination($page) {
		$start = 0; 
		$end = 20;
		if($page > 1) {
			$start = ($end * ($page -1)) + 1;
			$end  = $end * $page;
		}

		return "$start,$end";
	}

	protected function getToken($token) {
		$sqlstr = "SELECT user, token, expiration_date FROM token WHERE token = '$token'";
		$stmt = $this->query($sqlstr);
		if(isset($stmt)) {
			$date = $stmt[0]['expiration_date'];
			if($date > date('Y-m-d H:i') || $date === '0000-00-00 00:00:00') {
				return $stmt;
			} else {
				return 0;
			}

		};
	}

}
