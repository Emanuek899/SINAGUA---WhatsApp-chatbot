<?php
require_once "../models/PaymentsModel.php";

class PaymentsController{
	private $model;

	public function __construct(){
		$this->model = new PaymentsModel();
	}

	public function handleRequest($method, $id = null){
		switch($method){
			case "GET":
				if($id){
					$this->getPaymentById($id);
				} else{
					$this->getAllPayments();
				}
				break;

			case "POST":
				$this->insertPayment();
				break;

			case "DELETE":
				$this->deletePayment($id);
				break;
		}
	}

	/**
	 * Connect to the database and get all the debts
	 * 
	 * Send a JSON response to the client with the list of debts or an apprpiate
	 * message if none exists.
	 * 
	 * @return void
	*/	
	private function getAllPayments(){
		$data = $this->model->getAll();
		if($data === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => $this->model->getLastError()
			]);
			return;
		}
		if(!$data["success"]){
			$this->sendResponse(200, [
				"success" => false,
				"message" => "There are no payments"
			]);
			return;
		}
		$this->sendResponse(200, $data);
		return;
	}

	/**
	 * Connect to the database and get an entry by id.
	 * 
	 * Send a JSON response to the client with the debt or an appropiate
	 * message if none exists.
	 * 
	 * @return void
	*/
	private function getPaymentById($id){
		$validation = $this->validateNumeric($id);
		if(!$validation["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $validation["message"]
			]);
			return;
		}
		$data = $this->model->getById($id);

		$this->sendResponse(200, $data);
		return;
	}

	/**
	 * Connect to the database en insert a new debt.
	 * 
	 * Send a JSON response to the client.
	 * 
	 * @return void
	*/
	private function insertPayment(){
		$data = json_decode(file_get_contents("php://input"), true);

		$validateDebtId = $this->validateField($data, "debt_id");
		if(!$validateDebtId["success"]){
			$this->sendResponse(400,[
				"success" => false,
				"message" => $validateDebtId["message"]
			]);
			return;
		}

		$numericDebtId = $this->validateNumeric($data["debt_id"], "debt_id");
		if(!$numericDebtId["success"]){
			$this->sendResponse(400,[
				"success" => false,
				"message" => $numericDebtId["message"]
			]);
			return;
		}

		$debtId = $data["debt_id"];
		$id = $this->model->insert($debtId);
		if($id === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => "internal server error",
			]);
			return;
		}
		if (!$id["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $id["message"]
			]);
			return;
		}
		$this->sendResponse(201,[
			"success" => true,
			"message" => "payment insert success",
			"id" => $id["id"]
		]);
		return;
	}

	/**
	 * Delete a debt by and id as reference.
	 * 
	 * Send a JSON response with an array to the client
	 * 
	 * @return void
	*/
	private function deletePayment($debtId){
		$validateDebtId = $this->validateNumeric($debtId, "debt_id");
		$result = $this->model->delete($debtId);
		if($result === null){
			$this->sendResponse(500, [
				"success" => true,
				"message" => "Internal server error"
			]);
			return;
		}
		if(!$result["success"]){
			$this->sendResponse(404, [
				"success" => false,
				"message" => "payment doesn't exist"]);
			return;
		}
		$this->sendResponse(200,
			["success" => true,
			"message" => "payment succesfully deleted"]);
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

	private function validateField($array, $field){
		if(!isset($array[$field])){
			return [
				"success" => false,
				"message" => "$field is required"];
		}
		return ["success" => true];
	}
}