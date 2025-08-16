<?php
require_once "../config/config.php";

class StatusModel {
	private $conn;
	public $lastError;

	public function __construct(){
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$db = new Database();
		$this->conn = $db->connection;
	}
	/**
	 * Returns all the status in the database.
	 * 
	 * @return array|null Returns an array with true, no matter
	 * 		   if this is empty. Returns null if there was a 
	 * 		   problem with the database
	 * 
	 * @throws mysqli_sql_exception In case of database error
	*/
	public function getAll(){
		try{
			$data = [];
			$stmt = $this->conn->query("SELECT * FROM status");
			if($stmt->num_rows > 0){
				while($row = $stmt->fetch_assoc()){
					$data[] = $row;
				}
			}
			$stmt->free_result();
			return ["success" => true, "data" => $data];
		}catch(mysqli_sql_exception $error){
			$this->lastError = $error->getMessage();
			return null;
		}
	}

	/**
	 * This function returns the status of a user, identified by
	 * a phone number
	 * 
	 * @return array|null Returns an array if there is an user, if there is an
	 * error this return null.
	 * */
	public function getByNum($number){
		try{
			$stmt = $this->conn->prepare("SELECT * FROM status WHERE num = ?");
			$stmt->bind_param("s", $number);
			$stmt->execute();
			$result = $stmt->get_result();
			$data = $result->fetch_assoc();
			if(!$data){
				$stmt->close();
				return ["success" => false, "message" => "No status found for $number"];
			}
			$stmt->close();
			return ["success" => true, "data" => $data];
		}catch(mysqli_sql_exception $error){
			$this->lastError = $error->getMessage();
			return null;
		}
	}

	/**
	 * Insert a new status in the database
	 * 
	 * When a user starts his chat, a status register is made it
	 * to take a record and localize what flow of the chart is.
	 * 
	 * @param number (String) The cellphone number of the user.
	 * 		  status (String) The status of the user.
	 * 
	 * @return array|null Returns an array with 'success' => true if
	 * 		   the operation was successfull, but it will return null
	 * 		   if there is an error.
	 * 
	 * @throws mysqli_sql_exception In case of database error
	 * */
	public function insert($number, $status){
		try{
			$stmt = $this->conn->prepare("INSERT INTO status(num, status) VALUES (?,?)");
			$stmt->bind_param("ss", $number, $status);
			$stmt->execute();
			return ["success" => true, "message" => "Sucessfully insert"];
		}catch(mysqli_sql_exception $error){
			if($error->getCode() === 1062){
				return [
					"success" => false,
					"message" => "User $number already has a status"
				];
			}
			$this->lastError = $error->getMessage();
			return null;
		}
	}

	/**
	 * Update a status in the database.
	 * 
	 * Update the 'status' field in the database
	 * 
	 * @param num (string) the cellphone number of the user.
	 * 		  status (string) The new status of the user.
	 * 
	 * @return array|null Return and array with 'success' => true. In case there
	 * 					  is an error in the update it will return
	 * 					  'success' => false. For errors in the database will
	 * 					  return null.
	 * 
	 * @throws mysqli_sql_exception In case of a database error. 
	*/
	public function update($num, $status){		
		try{
			$stmt = $this->conn->prepare("UPDATE status SET status = ? WHERE num =?");
			$stmt->bind_param("ss", $status, $num);
			$stmt->execute();
			if($stmt->affected_rows === 0){
				$stmt->close();
				return ["success" => false, "message" => "user doesn't have status"];
			}
			$stmt->close();
			return ["success" => true, "message" => "status succesfully updated"];
		}catch(mysqli_sql_exception $e){
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	/**
	 * Delete a staus register from the database.
	 * 
	 * When a user finish the chat, his status will be erased.
	 * 
	 * @param num (String) The cellphone number of the user.
	 * 
	 * @return array|null Return an array with 'success' => true if the register
	 * 					  was succesfully deleted. In case the user did not exi-
	 * 					  st anymore, it returns array with 'success' => false.
	 * 					  Returns null if there are database errors.
	 * 
	 * @throws mysqli_sql_exception If there is an error in the database. 
	*/
	public function delete($num){
		try{
			$stmt = $this->conn->prepare("DELETE FROM status WHERE num = ?");
			$stmt->bind_param("s", $num);
			$stmt->execute();
			if($stmt->affected_rows === 0){
				$stmt->close();
				return ["success" => false, "message" => "user doesn't have status"];
			}
			$stmt->close();
			return ["success" => true, "message" => "status deleted"];
		}catch(mysqli_mysql_exception $e){
			$this->lastError = $e->getMessage();
			return null;
		}
	}

	private function validateNumeric($value, $field = "value"){
		if(!isset($value) || !ctype_digit($value)){
			return [
				"success" => false,
				"message" => "'$field' must be a valid number"
			];
		}
		return ["success" => true];	
	}

	/**
	 * Return the last error happened in the database.
	 * 
	 * @return string Return a string with the last error in the query.
	*/
	public function getLastError(){
		return $this->lastError;
	}
}