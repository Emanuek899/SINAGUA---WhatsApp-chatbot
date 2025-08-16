<?php
require_once "../config/config.php";

class AccountNumbersModel {
	public $conn;
	public $lastError;

	public function __construct(){
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$db = new Database();
		$this->conn = $db->connection;
	}

	/*
	 * Get all the account numbers of the users.
	**/
	public function getAll(){
		try{
			$stmt = $this->conn->query("SELECT * FROM account_numbers");
			if(!$stmt){
				$this->lastError = $this->conn->error;
				return null;
			}		
			$nums = [];
			if($stmt->num_rows > 0){
				while($row = $stmt->fetch_assoc()){
					$nums[] = $row;
				}
			}
			$stmt->free_result();
			return $nums;
		}catch(mysqli_sql_exception $e){
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/*
	 *	Get only an account number of the user, by ID.
	**/
	public function getByNum($num){
		if(empty($num) || !is_numeric($num)){
			return [
				"success" => false,
				"message" => "invalid id"
			];
		}
		try{
			$stmt = $this->conn->prepare("SELECT * FROM account_numbers WHERE num = ?");
			if(!$stmt){
				$this->lastError = $this->conn->error;
				return null;
			}
			if(!$stmt->bind_param("i", $num)){
				$this->lastError = $stmt->error;
				return null;
			}
			if(!$stmt->execute()){
				$this->lastError = $stmt->error;
				return null;
			} 
			$result = $stmt->get_result();
			$data = $result->fetch_assoc();
			if(!$data){
				$stmt->close();
				return [
					"success" => false,
					"message" => "account number not found"
				];
			}
			$stmt->close();
			return ["success" => true, "data" => $data];			
		}catch(mysqli_mysql_exception $e){
			$this->lastError = $e->getMessage();
			return;
		}
		
	}


	/**
	 * Insert a new account number ready for his use.
	*/
	public function insert($account_num){
		if(empty($account_num) || !ctype_digit($account_num)){
			return ["success" => false, "message" => "invalid account number"];
		}
		if(strlen($account_num) != 16){
			return ["success" => false, "message" => "invalid account number lenght"];
		}		
		try{
			$stmt = $this->conn->prepare("INSERT INTO account_numbers (num) VALUES (?)");
			if(!$stmt){
				$this->lastError = $this->conn->error;
				return null;
			}
			if(!$stmt->bind_param("s", $account_num)){
				$this->lastError = $stmt->error;
				return null;
			}
			if(!$stmt->execute()){
				$this->lastError = $stmt->error;
				return null;
			}
			$id = $stmt->insert_id;
			$stmt->close();
			return ["success" => true, "id" => $id];
		}catch(mysqli_sql_exception $e){
			if($e->getCode() === 1062){
				return [
					"success" => false,
					"message" => "account number existing"
				];
			}
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/*
	 *
	**/
	public function delete($id){
		if(empty($id) || !is_numeric($id)){
			return ["success" => false, "message" => "invalid id"];
		}
		try{
			$stmt = $this->conn->prepare("DELETE FROM account_numbers WHERE id = ?");
			if(!$stmt){
				$this->lastError = $this->conn->error;
				return null;
			}
			if(!$stmt->bind_param("i", $id)){
				$this->lastError = $stmt->error;
				return null;
			}
			if(!$stmt->execute()){
				$this->lastError = $stmt->error;
				return null;
			} 
			if($stmt->affected_rows === 0){
				$stmt->close();
				return ["success" => false, "message" => "account num doesn't exist"];
			}
			$stmt->close();
			return ["success" => true, "message" => "account number deleted"];
		}catch(mysqli_mysql_exception $e){
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	public function getLastError(){
		return $this->lastError;
	}
}