<?php
require_once "../config/config.php";

class PaymentsModel{
	public $conn;
	public $lastError;

	public function __construct(){
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 
		$this->conn = (new Database())->connection;
	}

	/**
	 * Get all the payments in the database
	 * 
	 * @return array|null Return an array with 'success' and 'payments' if this 
	 * 					  was succesfull or return null when there is a database
	 * 					  error. Return an empty array if there is no data.
	 * 
	 * @throws mysqli_sql_exception if there is an error in the query.
	*/
	public function getAll(){
		try{
			$payments = [];
			$stmt = $this->conn->query("SELECT * FROM payments");
			if($stmt->num_rows > 0){
				while($row = $stmt->fetch_assoc()){
					$payments[] = $row;
				}
			}
			$stmt->free_result();
			return ["success" => true, "Payments" => $payments];
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
			$stmt = $this->conn->prepare("SELECT * FROM payments WHERE id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
			$data = $result->fetch_assoc();
			if(!$data){
				$stmt->close();
				return [
					"success" => false,
					"message" => "payment not found"
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
	public function insert($debtId){
		$validateDebtId = $this->validateNumeric($debtId, "debt_id");
		if(!$validateDebtId["success"]) return $validateAccountNum;	
		try{
			$debtIdInt= (int)$debtId;
			$stmt = $this->conn->prepare("INSERT INTO payments (debt_id) 
				VALUES (?)");
			$stmt->bind_param("i", $debtIdInt);
			$stmt->execute();
			$id = $stmt->insert_id;
			$stmt->close();
			return ["success" => true, "id" => $id];
		}catch(mysqli_sql_exception $e){
			if($e->getCode() === 1062){
				return [
					"success" => false,
					"message" => "payment already exists"
				];
			}
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
	public function delete($id){
		$validation = $this->validateNumeric($id, "id");
		if(!$validation["success"]) return $validation;
		try{
			$stmt = $this->conn->prepare("DELETE FROM payments WHERE id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			if($stmt->affected_rows === 0){
				$stmt->close();
				return ["success" => false, "message" => "payment doesn't exist"];
			}
			$stmt->close();
			return ["success" => true, "message" => "payment deleted"];
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
		if(!isset($value) || !ctype_digit($value)){
			return [
				"success" => false,
				"message" => "'$field' must be a valid number"
			];
		}
		return ["success" => true];	
	}
}