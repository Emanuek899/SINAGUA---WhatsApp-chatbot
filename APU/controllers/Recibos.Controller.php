<?php

require_once "../reports_generator/report_generator.php";

class RecibosController{
	private $model;

	public function handleRequest($method, $num=null, $paymentId=null){
		switch($method){
			case 'POST':
				$this->generatePdf($num, $paymentId);
				break;
			case 'DELETE';
				$this->deleteAccountNumber($id);
				break;
		}
	}

	/*
	 * Method to get account numbers from the database.
	 * Return an http response and a JSON object with all the numbers.
	**/
	private function generatePdf($num, $paymentId){
		$data = generarPdf($num, $paymentId);
		$this->sendResponse(200, $data);
	}

	private function sendResponse($status, $data) {
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}	
}