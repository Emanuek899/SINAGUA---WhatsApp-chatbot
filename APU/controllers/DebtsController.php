<?php
require_once "../models/DebtsModel.php";

class DebtsController{
	private $model;

	public function __construct(){
		$this->model = new DebtsModel();
	}

	public function handleRequest($method, $id = null, $accountId = null){
		switch($method){
			case "GET":
				if($id){
					$this->getDebtById($id);
				} else if($accountId){
					$this->getDebtByAccountId($accountId);
				} else{
					$this->getAllDebts();
				}
				break;

			case "POST":
				$this->createDebt();
				break;

			case "PUT":
				$this->updateDebt();
				break;

			case "DELETE":
				$this->deleteDebt($accountId);
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
	private function getAllDebts(){
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
				"message" => "There are no debts"
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
	private function getDebtById($id){
		$validation = $this->validateNumeric($id);
		if(!$validation["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $validation["message"]
			]);
			return;
		}
		$data = $this->model->getById($id);

		$this->sendResponse(200, $data["data"]);
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
	private function getDebtByAccountId($id){
		$validation = $this->validateNumeric($id);
		if(!$validation["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $validation["message"]
			]);
			return;
		}
		$data = $this->model->getByAccountId($id);
		// Returns false if there are no debt
		if($data["success"] == false){
			$this->sendResponse(200, [
				"success" => false,
				"message" => "User has no debts"
			]);
		}

		$this->sendResponse(200, [
			"success" => true, 
			"data" => $data["data"]
		]);
		return;
	}

	/**
	 * Connect to the database en insert a new debt.
	 * 
	 * Send a JSON response to the client.
	 * 
	 * @return void
	*/
	private function createDebt(){
		$data = json_decode(file_get_contents("php://input"), true);

		$validateQuantity = $this->validateField($data, "quantity");
		$validateAccountId = $this->validateField($data, "account_num_id");
		$validatePaid = $this->validateField($data, "paid");
		if(!$validateQuantity["success"]){
			$this->sendResponse(400,[
				"success" => false,
				"message" => $validateQuantity["message"]
			]);
			return;
		}
		if(!$validateAccountId["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $validateAccountId["message"]
			]);
			return;
		}
		if(!$validatePaid["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $validatePaid["message"]
			]);
			return;
		}

		$quantity = $data["quantity"];
		$accountNumId = $data["account_num_id"];
		$paid = $data["paid"];
		$id = $this->model->insert($quantity, $accountNumId, $paid);
		if($id === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => "internal server error",
				"error" => $this->model->getLastError() // Development
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
			"message" => "Debt insert success",
		]);
		return;
	}

	private function updateDebt(){
		$data = json_decode(file_get_contents("php://input"), true);

		$validateDebtId = $this->validateField($data, "debt_id");
		$validatePaid = $this->validateField($data, "paid");
		if(!$validateDebtId["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $validateDebtId["message"]
			]);
			return;
		}
		if(!$validatePaid["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $validatePaid["message"]
			]);
			return;
		}

		$numericDebtId = $this->validateNumeric($data["debt_id"], "debt_id");
		$numericPaid = $this->validateNumeric($data["paid"], "paid");
		if(!$numericDebtId["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $numericDebtId["message"]
			]);
			return;
		}
		if(!$numericPaid["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $numericPaid["message"]
			]);
			return;
		}

		$debtId = $data["debt_id"];
		$paid = $data["paid"];
		$result = $this->model->update($debtId ,$paid);
		if($result === null){
			$this->sendResponse(500, [
				"success" => false,
				"message" => "internal server error"
			]);
			return;
		}
		if(!$result["success"]){
			$this->sendResponse(400, [
				"success" => false,
				"message" => $result["message"]
			]);
			return;
		}
		$this->sendResponse(200, 
			["success" => true, 
			"message" => "Debt succesfully modified"
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
	private function deleteDebt($accountId){
		$validateId = $this->validateNumeric($accountId, "debt_id");
		$result = $this->model->delete($id);
		if($result === null){
			$this->sendResponse(500, [
				"success" => true,
				"message" => "Internal server error",
				"error" => $this->model->getLastError()
			]);
			return;
		}
		if(!$result["success"]){
			$this->sendResponse(404, [
				"success" => false,
				"message" => "user doesn't exist"]);
			return;
		}
		$this->sendResponse(200,
			["success" => true,
			"message" => "debt succesfully deleted"]);
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