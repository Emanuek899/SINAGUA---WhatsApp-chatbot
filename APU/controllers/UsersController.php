<?php
require_once "../models/UsersModel.php";

class UserController{
	private $model;

	public function __construct(){
		$this->model = new UsersModel();
	}

	public function handleRequest($method, $id = null, $accountId = null){
		switch($method){
			case "GET":
				if($id){
					$this->getUserById($id);
				} else if($accountId){
					$this->getUserByAccountId($accountId);
				}else{
					$this->getAllUsers();
				}
				break;

			case "POST":
				$this->create();
				break;

			case "PUT":
				$this->update();
				break;

			case "DELETE":
				$this->delete($id);
				break;
		}
	}

	private function getAllUsers(){
		$data = $this->model->getAll();
		$this->sendResponse(200, $data);
	}

	private function getUserById($id){
		$data = $this->model->getById($id);
		$this->sendResponse(200, $data);
	}

	private function getUserByAccountId($accountId){
		$data = $this->model->getByAccount($accountId);
		$this->sendResponse(200, $data);
	}

	private function create(){
		$data = json_decode(file_get_contents("php://input"), true);

		if(!isset($data["account_num_id"])){
			$this->sendResponse(400, ["success" => false, "message" => "'account_num_field' is required"]);
			return;
		} elseif (!isset($data["name1"])) {
			$this->sendResponse(400, ["success" => false, "message" => "'name1' field is required"]);
			return;
		} elseif (!isset($data["lastname1"])){
			$this->sendResponse(400, ["success" => false, "message" => "'lastname1' field is required"]);
			return;
		} elseif (!isset($data["address"])){
			$this->sendResponse(400, ["success" => false, "message" => "'address' field is required"]);
			return;
		}
		$id = $this->model->insertUsers($data);
		$this->sendResponse(201, ["success" => true, "message" => "User insert success"]);
	}

	private function update(){
		$data = json_decode(file_get_contents("php://input"), true);
		if(!isset($data["address"])){
			$this->sendResponse(400, ["success" => false, "message" => "'address' field is required"]);
			return;
		}
		$result = $this->model->updateAddress($data["address"], $data["id"]);
		if($result === true){
			$this->sendResponse(200, ["success" => true, "message" => "address succesfully changed"]);
			return;
		} else {
			$this->sendResponse(500, ["success" => false, "message" => "internal server error"]);
			return;
		}
	}

	private function delete($id){
		if(!isset($id)){
			$this->sendResoonse(400, ["success" => false, "message" => "'id' field is required"]);
		}
		$result = $this->model->delUser($id);
		if($result === true){
			$this->sendResponse(200, ["success" => true, "message" => "user succesfully deleted"]);
			return;
		} elseif($result === false){
			$this->sendResponse(404, ["success" => false, "message" => "user doesn't exist"]);
			return;
		} else{
			$this->sendResponse(500, ["success" => false, "message" => "internal server error"]);
			return;
		}
	}

	private function sendResponse($status, $data) {
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($data);
	}

}