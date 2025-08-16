import axios from 'axios';
import plantillas from './plantillas.js';
import token from './token.js';
import api from '../api/status.js';
import fs from "fs";
import dotenv from 'dotenv';

dotenv.config();

const RETURN_URL = process.env.RETURN_URL;
const BASE_URL_BACK = process.env.BACKEND_URL;

async function createPayLink(from, accountNum){
	try{
		const accountResponse = await axios.get(`${BASE_URL_BACK}AccountNumbers.php?num=${accountNum}`);
		const accountId = accountResponse.data.id
		const debt = await axios.get(`${BASE_URL_BACK}Debts.php?accountId=${accountId}`);
		const quantityDebt = debt.data.data.quantity;
		const tokenKey = await token.getToken();
		const data = {
			"intent": "CAPTURE",
			"purchase_units": [{
				"custom_id": `from:${from};account:${accountNum}`,
				"amount": {
					"currency_code": "MXN",
					"value": quantityDebt
				}
			}],
			"application_context": {
				"return_url": RETURN_URL
			}
		}
		const responseLink = await axios.post("https://api.sandbox.paypal.com/v2/checkout/orders",
			data,
			{
				headers:{
					"Content-Type": 'application/json',
					"Authorization": `Bearer ${tokenKey}`
				}
			})
		const links = responseLink.data.links;
		const approvalLink = links.find(link => link.rel === "approve").href;
		return {"success": true, "link": approvalLink};
	}catch(error){
		console.log(`Error in creation of link, check the logs`);
		fs.appendFileSync("logs/pay_link_error_creation.txt",
			`${new Date().toISOString()} - User: ${from}\nError while creating a pay link\nData: ${error.response.data}\nMessage: ${error.message}\n Trace: ${error.stack}\n\n`);
	}
}

async function proccessPay(from, accountNum){
	try{
		const responseAccountId = await axios.get(`${BASE_URL_BACK}AccountNumbers.php?num=${accountNum}`);
		const accountId = responseAccountId.data.id;
		console.log(accountId);
		console.log(typeof accountId);
		const responseDebtId = await axios.get(`${BASE_URL_BACK}Debts.php?accountId=${accountId}`);
		const debtId = responseDebtId.data.data.id;
		const responsePayment = await axios.post(`${BASE_URL_BACK}Payments.php`,
			{
				"debt_id": String(debtId)
			},
			{
				headers:{
					"Content-Type": "application/json"
				}
			}
		);

		const paymentData = responsePayment.data;
		const report = await axios.post(`${BASE_URL_BACK}Recibos.php?num=${accountNum}&payment=${paymentData.id}`);
		const reportLink = report.data.link;
		const reportFilename = report.data.filename;
		if(paymentData.success === true){
			const debtModify = await axios.put(`${BASE_URL_BACK}Debts.php`,
				{
					"debt_id": String(debtId),
					"paid": "1"	
				},
				{
					headers:{
						"Content-Type": "application/json"
					}
				}
			);
			if(debtModify.data.success === true){
				await plantillas.showTemplate({
					template: "realizar_pago_3",
					to: from
				})
				api.updateStatus(from, "realizar_pago_3");
				await plantillas.showTemplate({
					template: "enviar_recibo",
					to: from,
					link: reportLink,
					filename: reportFilename 
				});
			}
		}
	}catch(error){
		console.log(`Error in proccessPay ${error} - check the logs`)
		fs.appendFileSync("logs/process_pay_error.txt",
			`${new Date().toISOString()} - Error while processing the payment\n  Message: ${error.message}n Trace: ${error.stack}\n\n`)
	}
}

export default {
	createPayLink,
	proccessPay
}