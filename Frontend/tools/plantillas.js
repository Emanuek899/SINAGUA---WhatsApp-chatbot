import axios from 'axios';
import dotenv from 'dotenv';

dotenv.config();

const BASE_URL = process.env.META_URL;
const VERIFY_TOKEN = process.env.VERIFY_TOKEN_META;

async function showTemplate({
	template = null,
	to = null,
	username = null,
	quantity = null,
	link = null,
	userStatus = null,
	filename = null
	} = {}){
	switch(template){
		case "sin_agua_template_welcome":
			return await onlyTextTemplate(to, template);
		case "options":
			return await onlyTextTemplate(to, template);
		case "consulta_saldo":
			return await onlyTextTemplate(to, template);
		case "consulta_saldo_1":
			return await consultaSaldo1(to, username);
		case "consulta_saldo_2":
			return await consultaSaldo2(to, username, String(quantity));
		case "realizar_pago":
			return await onlyTextTemplate(to, template);
		case "realizar_pago_1":
			return await realizarPago1(to, username, String(quantity));
		case "realizar_pago_paypal":
			return await realizarPagoPaypal(to, link);
		case "realizar_pago_3":
			return await onlyTextTemplate(to, template);
		case "realizar_pago_4":
			return await onlyTextTemplate(to, template);
		case "opcion_incorrecta":
			await onlyTextTemplate(to, template);
			await showTemplate({
				template: userStatus,
				to: to
			});
			return;
		case "enviar_recibo":
			return await enviarRecibo(to, link, filename);
		case "numero_invalido":
			await onlyTextTemplate(to, template);
			await showTemplate({
				template: userStatus,
				to:to
			});
			return;
		case "error_interno":
			return await onlyTextTemplate(to, template);
		case "fin":
			return await onlyTextTemplate(to, template);
	}
}

async function onlyTextTemplate(to, template){
	try{
		const response = await axios.post(
			BASE_URL,
			{
			  "messaging_product": "whatsapp",
			  "recipient_type": "individual",
			  "to": to,
			  "type": "template",
			  "template": {
			    "name": template,
			    "language": {
			      "code": "es_MX"
			    }
			  }
			},
			{
				headers:{
					"Authorization": `Bearer ${VERIFY_TOKEN}`,
					"Content-Type": "application/json"
				}
			}
		);
		console.log(`plantilla ${template} enviada exitosamente`)
		return response;
	}catch(error){
		console.error(`Error al enviar plantilla: ${template}`, error.response?.data || error.message);
		console.error(to);
	}
}

async function consultaSaldo1(to, username){
	try{
		const response = await axios.post(
			BASE_URL,
			{
			  "messaging_product": "whatsapp",
			  "recipient_type": "individual",
			  "to": to,
			  "type": "template",
			  "template": {
			    "name": "consulta_saldo_1",
			    "language": {
			      "code": "es_MX"
			   },
			   "components": [
			   		{
			   			"type": "header",
			   			"parameters": [
			   				{
			   					"type": "text",
			   					"text": username
			   				}
			   			]
			   		}
			   ]
			  }
			},
			{
				headers:{
					"Authorization": `Bearer ${VERIFY_TOKEN}`,
					"Content-Type": "application/json"
				}
			}
		);
		console.log("plantilla enviada exitosamente")
		return true;
	}catch(error){
		console.error("Error al enviar plantilla consulta_saldo_1:", error.response?.data || error.message);
		console.error(to);
		return false;
	}
}


/**
 * Send the template for the case of the user has a debt in the system
 * 
 * @param {string} to - Destonatary phone number
 * @param {string} templateName - Name of the template
 * @param {list} parameters - A list with the template parameters
 * */
async function consultaSaldo2(to, username, quantity){
	try{
		const response = await axios.post(
			BASE_URL,
			{
			  "messaging_product": "whatsapp",
			  "recipient_type": "individual",
			  "to": to,
			  "type": "template",
			  "template": {
			    "name": "consulta_saldo_2",
			    "language": {
			      "code": "es_MX"
			   },
			   "components": [
			   		{
			   			"type": "header",
			   			"parameters": [
			   				{
			   					"type": "text",
			   					"text": username
			   				}
			   			]
			   		},
			   		{
			   			"type": "body",
			   			"parameters": [
			   				{
			   					"type": "text",
			   					"text": quantity
			   				}
			   			]
			   		}
			   ]
			  }
			},
			{
				headers:{
					"Authorization": `Bearer ${VERIFY_TOKEN}`,
					"Content-Type": "application/json"
				}
			}
		);
		console.log("plantilla enviada exitosamente")
	}catch(error){
		console.error("Error al enviar plantilla: consulta_saldo_2", error.response?.data || error.message);
		console.error(to);
	}
}

async function realizarPago1(to, username, quantity){
	try{
		const response = await axios.post(
			BASE_URL,
			{
			  "messaging_product": "whatsapp",
			  "recipient_type": "individual",
			  "to": to,
			  "type": "template",
			  "template": {
			    "name": "realizar_pago_1",
			    "language": {
			      "code": "es_MX"
			   },
			   "components": [
			   		{
			   			"type": "header",
			   			"parameters": [
			   				{
			   					"type": "text",
			   					"text": username
			   				}
			   			]
			   		},
			   		{
			   			"type": "body",
			   			"parameters": [
			   				{
			   					"type": "text",
			   					"text": quantity
			   				}
			   			]
			   		}
			   ]
			  }
			},
			{
				headers:{
					"Authorization": `Bearer ${VERIFY_TOKEN}`,
					"Content-Type": "application/json"
				}
			}
		);
		console.log("plantilla enviada exitosamente")
	}catch(error){
		console.error("Error al enviar plantilla realizar_pago_1:", error.response?.data || error.message);
		console.error(to);
	}
}

async function realizarPagoPaypal(to, link){
	try{
		const response = await axios.post(
			BASE_URL,
			{
			  "messaging_product": "whatsapp",
			  "recipient_type": "individual",
			  "to": to,
			  "type": "template",
			  "template": {
			    "name": "realizar_pago_paypal",
			    "language": {
			      "code": "es_MX"
			   },
			   "components": [
			   		{
			   			"type": "body",
			   			"parameters": [
			   				{
			   					"type": "text",
			   					"text": link
			   				}
			   			]
			   		}
			   ]
			  }
			},
			{
				headers:{
					"Authorization": `Bearer ${VERIFY_TOKEN}`,
					"Content-Type": "application/json"
				}
			}
		);
		console.log("plantilla enviada exitosamente")
	}catch(error){
		console.error("Error al enviar plantilla realizar_pago_paypal:", error.response?.data || error.message);
		console.error(to);
	}
}

async function enviarRecibo(to, link, filename){
	try{
		const body = {
			"messaging_product": "whatsapp",
			"recipient_type": "individual",
			"to": to,
			"type": "document",
			"document": { 
				"link": link, 
				"caption": "Recibo de pago",
				"filename": filename
			}
		}

		const response = await fetch(BASE_URL, {
			method: "POST",
			headers: {
				"Authorization": `Bearer ${VERIFY_TOKEN}`,
				"Content-Type": "application/json"
			},
			body: JSON.stringify(body)
		});
	} catch(error){
		console.error(`Error al enviar recibo de pago`);
		console.error(error);
	}
}

export default{
	showTemplate,
};