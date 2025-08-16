<?php
require_once __DIR__ . "/../config/config.php";

class DebtsModel{
	public $conn;
	public $lastError;

	/*
	 * Initialize an instance with the connection to the database.
	**/
	public function __construct(){
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$this->conn = (new Database())->connection;
	}

	/**
	 * Get all the debts in the database
	 * 
	 * @return array|null Return an array with 'success' and 'debts' if this was
	 * 					  succesfull or return null when there is a database
	 * 					  error. Return an empty array if there is n data.
	 * 
	 * @throws mysqli_sql_exception if there is an error in the query.
	*/
	public function getAll(){
		try{
			$debts = [];
			$stmt = $this->conn->query("SELECT * FROM debts");
			if($stmt->num_rows > 0){
				while($row = $stmt->fetch_assoc()){
					$debts[] = $row;
				}
			}
			$stmt->free_result();
			return ["success" => true, "debts" => $debts];
		}catch(mysqli_sql_exception $e){
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/**
	 * Return a debt from the database, based on an id.
	 * 
	 * @return array|null Returns and array with 'success' and the debt from the
	 * 					  database. array => false when there is a error with
	 * 					  the data. Return null when there is a error in the
	 * 					  database.
	 * 
	 * @throws mysqli_sql_exception if there is an error in the database.
	*/
	public function getById($id){
		$validation = $this->validateNumeric($id, "id");
		if(!$validation["success"]) return $validation;
		try{
			$stmt = $this->conn->prepare("SELECT * FROM debts WHERE id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
			$data = $result->fetch_assoc();
			if(!$data){
				$stmt->close();
				return [
					"success" => false,
					"message" => "debt not found"
				];
			}
			$stmt->close();
			return ["success" => true, "data" => $data];			
		}catch(mysqli_sql_exception $e){
			$this->lastError = $e->getMessage();
			return;
		}
		
	}

	/**
	 * Return a debt from the database, based on an id.
	 * 
	 * @return array|null Returns and array with 'success' and the debt from the
	 * 					  database. array => false when there is a error with
	 * 					  the data. Return null when there is a error in the
	 * 					  database.
	 * 
	 * @throws mysqli_sql_exception if there is an error in the database.
	*/
	public function getByAccountId($id){
		$validation = $this->validateNumeric($id, "account_id");
		if(!$validation["success"]) return $validation;
		try{
			$stmt = $this->conn->prepare("SELECT * FROM debts WHERE account_num_id = ? AND paid = 0");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
			$data = $result->fetch_assoc();
			if(!$data){
				$stmt->close();
				return [
					"success" => false,
					"message" => "debt not found"
				];
			}
			$stmt->close();
			return ["success" => true, "data" => $data];			
		}catch(mysqli_sql_exception $e){
			$this->lastError = $e->getMessage();
			return;
		}
		
	}

	/**
	 * Insert a new debt to the database
	 * 
	 * @return array|null Returns an array with 'success' => true and 'id'. in
	 * 				 	  case the data is invalid return 'success' => false.
	 * 					  If there is an error in the database returns null.
	 * 
	 * @throws mysqli_sql_exception If there is an error in the database.
	*/
	public function insert($quantity, $accountNumId, $paid){
		if(!isset($quantity) || 
			!preg_match('/^\d+(\.\d{1,2})?$/', $quantity) || 
			(float)$quantity <= 0){
			return [
				"success" => false,
				"message" => "'quantity' field is invalid"
			];
		}
		$validateAccountNum = $this->validateNumeric($accountNumId, "account_num");
		if(!$validateAccountNum["success"]) return $validateAccountNum;
		$validatePaid = $this->validateNumeric($paid, "paid");
		if(!$validatePaid["success"]) return $validatePaid;		
		try{
			$quantity = (float)$quantity;
			$accountNumId = (int)$accountNumId;
			$paid = (int)$paid;
			$stmt = $this->conn->prepare("INSERT INTO debts (
				quantity,
				account_num_id,
				paid) 
				VALUES (?, ?, ?)");
			$stmt->bind_param("dii", $quantity, $accountNumId, $paid);
			$stmt->execute();
			$id = $stmt->insert_id;
			$stmt->close();
			return ["success" => true, "id" => $id];
		}catch(mysqli_sql_exception $e){
			if($e->getCode() === 1062){
				return [
					"success" => false,
					"message" => "user already has a debt existing"
				];
			}
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/**
	 * Update a debt in the database.
	 * 
	 * Update the field 'paid' in when a user pay his debt, 0 is for false and
	 * 1 is for true.
	 * 
	 * @return array|null Return and array with 'success' => true. In case there
	 * 					  is an error with the data (validations) it will return
	 * 					  'success' => false. For errors in the database will
	 * 					  return null.
	 * 
	 * @throws mysqli_sql_exception In case of a database error. 
	*/
	public function update($id, $paid){
		$validation = $this->validateNumeric($id, "id");
		if(!$validation["success"]) return $validation;	
		if(!isset($paid) || !ctype_digit($paid) || !in_array((int)$paid, [0, 1])){
			return [
				"success" => false,
				"message" => "'paid' field is invalid"
			];
		}		
		try{
			$paidInt = (int)$paid;
			$idInt = (int)$id;
			$stmt = $this->conn->prepare("UPDATE debts SET paid = ?
				WHERE id = ? and paid = 0");
			$stmt->bind_param("ii", $paidInt, $idInt);
			$stmt->execute();
			if($stmt->affected_rows === 0){
				$stmt->close();
				return ["success" => false, "message" => "debt doesn't exist"];
			}
			$stmt->close();
			return ["success" => true, "message" => "debt succesfully updated"];
		}catch(mysqli_sql_exception $e){
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/**
	 * Delete a debt register from the database.
	 * 
	 * When a user pay all his debt, the debt will be eraser from the table.
	 * 
	 * @return array|null Return an array with 'success' => true if the register
	 * 					  was succesfully deleted. In case the debt did not exi-
	 * 					  st anymore, it returns array with 'success' => false.
	 * 					  Returns null if there are database errors.
	 * 
	 * @throws mysqli_sql_exception If there is an error in the database. 
	*/
	public function delete($accountId){
		$validation = $this->validateNumeric($accountId, "id");
		if(!$validation["success"]) return $validation;
		try{
			$stmt = $this->conn->prepare("DELETE FROM debts WHERE account_num_id = ?");
			$stmt->bind_param("i", $accountId);
			$stmt->execute();
			if($stmt->affected_rows === 0){
				$stmt->close();
				return ["success" => false, "message" => "debt doesn't exist"];
			}
			$stmt->close();
			return ["success" => true, "message" => "debt deleted"];
		}catch(mysqli_mysql_exception $e){
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/**
	 * Return the last error happened in the database.
	 * 
	 * @return string Return a string with the last error in the query.
	*/
	public function getLastError(){
		return $this->lastError;
	}

	/**
	 * Validate if the value is a valid number
	 * 
	 * Validate if the value is a valid number, and int with no alphabetic char-
	 * acters.
	 * 
	 * @param string $value A string with the number.
	 * 		  string $field The name of the field.
	 * @return array Return an array with 'success' => if the number is not val-
	 * 		   id, in the case is valid return 'success' => true.
	*/ 
	private function validateNumeric($value, $field = "value"){
		if(!isset($value) || !ctype_digit((string)$value)){
			return [
				"success" => false,
				"message" => "'$field' must be a valid number"
			];
		}
		return ["success" => true];	
	}
}