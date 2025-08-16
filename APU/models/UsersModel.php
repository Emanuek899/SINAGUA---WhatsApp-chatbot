<?php
require_once "../config/config.php";

class UsersModel {
	private $conn;

	public function __construct(){
		$db = new Database();
		$this->conn = $db->connection;
	}

	/*
	* Insert a new user in the database
	**/

	public function getAll(){
		$result = $this->conn->query("SELECT * FROM users");
		$users = [];
		if($result && $result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$users[] = $row;

			}
		}
		return $users;
	}

	public function getById($id){
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		return $result->fetch_assoc();
	}

	public function getByAccount($id){
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE account_num_id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		return $result->fetch_assoc();
	}

	public function insertUsers($data){
		$userData = [
			"account_num_id" => $data["account_num_id"],
			"name1"      => $data["name1"],
			"name2"		 => $data["name2"],
			"lastname1"  => $data["lastname1"],
			"lastname2"  => $data["lastname2"],
			"address"    => $data["address"]
 		];
		$stmt = $this->conn->prepare("INSERT INTO users (
			account_num_id,
			name1,
			name2,
			lastname1,
			lastname2,
			address)
			VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("isssss", 
			$userData["account_num_id"], 
			$userData["name1"], 
			$userData["name2"], 
			$userData["lastname1"], 
			$userData["lastname2"], 
			$userData["address"]);
		$stmt->execute();
		return $stmt->insert_id;
	}

	public function updateAddress($newAddress, $userId){
		$stmt = $this->conn->prepare("UPDATE users SET address = ? WHERE id = ?");
		$stmt->bind_param("si", $newAddress, $userId);
		return $stmt->execute();
	}

	public function delUser($id){
		$stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
		$stmt->bind_param("i", $id);
		return $stmt->execute();
	}

}