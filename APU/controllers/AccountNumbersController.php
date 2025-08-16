<?php
require_once "../models/AccountNumbersModel.php";

class AccountNumbersController{
	private $model;

	public function __construct(){
		$this->model = new AccountNumbersModel();
	}

	public function handleRequest($method, $num=null){
		switch($method){
			case 'GET':
				if($num){
					$this->getAccountNumberByNum($num);
					break;
				}else{
					$this->getAllAccountNumbers();
					break;
				}

			case 'POST':
				$this->insertAccountNumber();
				break;
			case 'DELETE';
				$this->deleteAccountNumber();
				break;
		}
	}

	/*
	 * Method to get account numbers from the database.
	 * Return an http response and a JSON object with all the numbers.
	**/
	private function getAllAccountNumbers(){
		$data = $this->model->getAll();
		if($data === null){
			$e = $this->model->getLastError();
			$this->sendResponse(500, [
				"success" => false,
				"message" => "Internal server error",
				"error" => $e
			]);
		}
		$this->sendResponse(200, $data);
	}

	/*
	 * Method to get the account nuber of an user from the database.
	 * Return an http response and a JSON object with all the numbers.
	**/
	private function getAccountNumberByNum($num){
		if(empty($num) || !is_numeric($num)){
			return [
				"success" => false,
				"message" => "invalid ID",
			];
		}
		$data = $this->model->getByNum($num);
		if($data === null){
			$e = $this->model->getLastError();
			$this->sendResponse(500, [
				"success" => false,
				"message" => "Internal server error",
				"error" => $e
			]);
			return;
		}
		if($data['success'] === false){
			$message = $data['message'];
			if($message === "invalid num"){
				$this->sendResponse(400, [
					"success" => false,
					"message" => $message
				]);
				return;
			}elseif($message === "account number not found"){
				$this->sendResponse(404, [
					"success" => false,
					"message" => $message
				]);
				return;
			}
		}		
		$this->sendResponse(200, $data["data"]);
		return;
	}


	private function insertAccountNumber(){
		$data = json_decode(file_get_contents("php://input"), true);
		if(!isset($data["num"])){
			$this->sendResponse(400, ["success" => false,"message" => "'num' field is required"]);
			return;
		}
		$id = $this->model->insert($data["num"]);
		if($id === null){
			$e = $this->model->getLastError();
			$this->sendResponse(500, [
				"success" => false,
				"message" => "internal server error",
				"error" => $e
			]);
			return;
		}
		if($id["success"] === false){
			$message = $id["message"];
			if($message === "invalid account number lenght"){
				$this->sendResponse(400,[
					"success" => false,
					"message" => $message,
				]);
				return;
			}elseif($message === "account number existing"){
				$this->sendResponse(409, [
					"success" => false,
					"message" => $message
				]);
				return;
			}elseif($message === "invalid account number"){
				$this->sendResponse(400,[
					"success" => false,
					"message" => $message
				]);
				return;
			}
		}		
		$this->sendResponse(201, ["success" => true, "message" => "account numm successfuly insert", "id" => $id["id"]]);
		return;
	}


	private function deleteAccountNumber(){
		$data = json_decode(file_get_contents("php://input"), true);
		if(!isset($data["id"])){
			$this->sendResponse(404, ["success" => false,"message" => "'id' field is required"]);
			return;
		}
		if(!is_numeric($data["id"])){
			$this->sendResponse(400, ["success" => false, "message" => "id must be an Int"]);
			return;
		}
		$ok = $this->model->delete($data["id"]);
		if($ok === null){
			$e = $this->model->getLastError();
			$this->sendResponse(500, [
				"success" => false,
				"message" => "Internal server error",
				"error" => $e
			]);
			return;
		}
		if($ok["success"] === false){
			$message = $ok["message"];
			if($message === "invalid id"){
				$this->sendResponse(400, ["message" => $message]);
				return;
			}elseif($message === "account num doesn't exist"){
				$this->sendResponse(404, ["message" => $message]);
				return;
			}
		} 
		$this->sendResponse(200,["success" => true, "message" => "Account num successfully deleted"]);
	}

	private function sendResponse($status, $data){
		http_response_code($status);
		header("Content-Type: application/json");
		echo json_encode($data);
	}
}