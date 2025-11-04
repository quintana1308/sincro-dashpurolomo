<?php


class Conexion{
	private $conect;
	private $conectEnterprise;

	public function __construct($conectEnterprise){

		$this->conectEnterprise = $conectEnterprise;

		require_once("Config/".$this->conectEnterprise.".php");

		$connectionString = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
		try{
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_PERSISTENT => true,  // <-- Aquí la conexión persistente
			];

			$this->conect = new PDO($connectionString, DB_USER, DB_PASSWORD, $options);
			//$this->conect = new PDO($connectionString, DB_USER, DB_PASSWORD);
			//$this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    //echo "conexión exitosa";
		}catch(PDOException $e){
			$this->conect = 'Error de conexión';
			echo "ERROR: " . $e->getMessage();
		}
	}

	public function conect(){
		return $this->conect;
	}
}

?>