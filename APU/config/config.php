<?php
require_once "global.php";

class Database{
	public $connection;

	/*
	* Create a connection with the database 
	**/
	public function __construct(){
		$this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		if($this->connection->connect_error){
			die("Error en la conexion" . $this->connection->connect_error);
		}

	}
}
?>