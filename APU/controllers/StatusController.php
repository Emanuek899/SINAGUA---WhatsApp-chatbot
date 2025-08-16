<?php
require_once "../models/StatusModel.php";

class StatusController{
	public $model;

	public function __construct(){
		$this->model = new StatusModel();
	}

	public function handleRequest($method, $num){
		switch($method){
			case "GET":
				if($num){
					$this->getStatusByNum($num);
					break;
				}else{
					$this->getAllStatus();
					break;
				}

			case "POST":
				$this->insertStatus();
				break;

			case "PUT":
				$this->updateStatus();
				break;

			case "DELETE":
				$this->deleteStatus($num);
				break;
		}
	}

	/**
	 * Send a JSON response to the client, with the http status code and the da-
	 * ta to be sent.
	 * 
	 * @param num $status The http status code.
	*/
	private function getAllStatus(){
		$data = $this->model->getAll();
		if($data === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => $this->model->getLastError()
			]);
			return;
		}
		if(!$data["success"]){
			$this->sendResponse(200,[
				"success" => false,
				"message" => $data["message"] 
			]);
			return;
		}
		$this->sendResponse(200, [
			"success" => true,
			"data" => $data["data"]
		]);
		return;
	}

	/**
	 * Send a JSON response to the client, with the http status code and the da-
	 * ta to be sent.
	 * 
	 * @param num $status The http status code.
	*/
	private function getStatusByNum($num){
		$data = $this->model->getByNum($num);
		if($data === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => $this->model->getLastError()
			]);
			return;
		}
		if(!$data["success"]){
			$this->sendResponse(200,[
				"success" => true,
				"message" => $data["message"] 
			]);
			return;
		}
		$this->sendResponse(200, [
			"success" => true,
			"data" => $data["data"]
		]);
		return;
	}

	private function insertStatus(){
		$data = json_decode(file_get_contents("php://input"), true);
		$status = $this->model->insert($data["num"], $data["status"]);
		if($status === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => $this->model->getLastError()
			]);
			return;
		}

		if($status["success"] === false){
			$this->sendResponse(400,[
				"success" => false,
				"message" => $status["message"]
			]);
		}

		$this->sendResponse(201,[
			"success" => true,
			"message" => $status["message"]
		]);
		return;
	}

	private function updateStatus(){
		$data = json_decode(file_get_contents("php://input"), true);
		$status = $this->model->update($data["num"], $data["status"]);
		if($status === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => $this->model->getLastError()
			]);
			return;
		}

		if($status["success"] === false){
			$this->sendResponse(400,[
				"success" => false,
				"message" => $status["message"]
			]);
		}

		$this->sendResponse(201,[
			"success" => true,
			"message" => $status["message"]
		]);
		return;
	}

	private function deleteStatus($num){
		$status = $this->model->delete($num);
		if($status === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => $this->model->getLastError()
			]);
			return;
		}

		if($status["success"] === false){
			$this->sendResponse(400,[
				"success" => false,
				"message" => $status["message"]
			]);
		}

		$this->sendResponse(201,[
			"success" => true,
			"message" => $status["message"]
		]);
		return;
	}

	/**
	 * Send a JSON response to the client, with the http status code and the da-
	 * ta to be sent.
	 * 
	 * @param int $status The http status code.
	 * 		  array $data The array that will be encoded as JSON.
	*/
	private function sendResponse($status, $data) {
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}	
}