import path from 'path';
import axios from 'axios';
import fs from 'fs';
import plantillas from './plantillas.js';
import api from '../api/status.js';
import pagos from './pagos.js';
import dotenv from 'dotenv';

dotenv.config();

const BACKEND_URL = process.env.BACKEND_URL;

async function startConversation(text, from){
	const keywords = [
		"hola", "ola", "ola k ase", "holi", "holaa", "holaaa", "hoola", "h0la", "holiwi", "holis", "holis :3", "buenos días", "buenos dias", "buenas tardes", "buenas tardez", "buenas noches", "buenas nochess", "buen dia", "buen día", "buenas", "buenas!", "hi", "hello",
		"que onda", "qué onda", "k onda", "k ondaa", "q onda", "q ondaaa", "qué tal", "que tal", "q tal", "qtal", "saludos", "saluditos", "salu2", "saludoz", "ey", "hey", "ei", "eyy", "wola", "wenas", "wenas tardes", "wenas noxes", "ke hay", "k hay",
		"como estas", "cómo estás", "como estas?", "cómo estás?", "kmo estas", "kmo tas", "komo estas", "komo stas", "todo bien", "todo bn", "todobien", "holaa ke tal", "ey que tal", "que hay", "qué hay", "q hay", "qai",
  		"que paso", "qué pasó", "q pasó", "q paso", "holaaa :D", "hola!!", "holaa!!", "holaaa uwu"
	];

	if(keywords.some((saludo) => text.includes(saludo))){
		const saveStatus = api.saveStatus(from, "options")
		const statusData = saveStatus.data;
		if(saveStatus === 400){
			const log = `[${new Date().toISOString()}] | ${statusData.message}\n`;
  			fs.appendFileSync('logs/errorStatus.txt', log);
  			return;
		} else if(saveStatus === 500){
			const log = `[${new Date().toISOString()}] | ${statusData.message}\n`;
  			fs.appendFileSync('logs/errorStatus.txt', log);
  			return;
		}
		
		await plantillas.showTemplate({
			template: "sin_agua_template_welcome",
			to: from
		});
		await plantillas.showTemplate({
			template: "options",
			to: from
		});
		await api.saveStatus(from, "options");
		return;
	}		
}

async function manager(text, from, buttonReply, status){
	// console.log("========= Debug Manager Data =========")
	// console.log(`text: ${text}`);
	// console.log(`from: ${from}`);
	// console.log(`buttonRepply: ${buttonReply}`);
	// console.log(`status: ${status}`);
	// console.log("======================================")

	let action = "";
	if(status === "options"){

		if(["1", "consultar saldo"].includes(text) || ["1", "consultar saldo"].includes(buttonReply)){
			action = "consultar saldo";
		} else if (text === "realizar pago" || buttonReply === "realizar pago"){
			action = "realizar pago";
		} else if (["3", "salir"].includes(text) ||
			["3", "salir"].includes(buttonReply)){
			action = "salir";
		}else{
			action = "opcion invalida";
		}

	}else if(status === "consulta_saldo"){

		if(text.length === 16){
			if(/^\d+$/.test(text)){
				let validAccount = await axios.get(`${BACKEND_URL}AccountNumbers.php?num${text}`);
				if (["account number not found", "invalid num"].includes(validAccount.data.message)){
					action = "numero invalido";
				}
				action = "espera numero consulta";
			}else{
				action = "numero invalido";
			}
		}else{
			if(["si", "1"].includes(text) ||
				["si", "1"].includes(buttonReply)){
				action = "options";
			}else if(["no", "2"].includes(text) ||
				["no", "2"].includes(buttonReply)){
				action = "salir";
			}else{
				action = "opcion invalida";
			}
		}

	}else if(status === "consulta_saldo_2"){

		if(["1", "si"].includes(text) ||
			["1", "si"].includes(buttonReply)){
			action = "realizar pago";
		}else if(["2", "no"].includes(text) ||
			["2", "no"].includes(buttonReply)){
			action = "options";
		}else{
			action = "opcion invalida";
		}

	}else if(status === "realizar_pago"){
		if(/^\d+$/.test(text) && text.length === 16){
			action = "espera numero pago";
		} else{
			action = "numero invalido";
		}

	}else if(status === "realizar_pago_1"){

		if(["1", "si"].includes(text) ||
			["1", "si"].includes(buttonReply)){
			action = "proceder pago";
		}else if(["2", "no"].includes(text) ||
			["2", "no"].includes(buttonReply)){
			action = "options";
		}else{
			action = "opcion invalida";
		}

	}else if(status === "confirmar_pago"){

		if(/^\d+$/.test(text) && text.length === 16){
			action = "transaccion";
		}

		// if(["1", "si"].includes(text) ||
		// 	["1", "si"].includes(buttonReply)){
		// 	action = "realizar pago";
		// }else if(["2", "no"].includes(text) ||
		// 	["2", "no"].includes(buttonReply)){
		// 	action = "options";
		// }else{
		// 	action = "opcion invalida";
		// }

	}else if(status === "pago_terminado"){

		action = "pago terminado";

	}else if(status === "menu_finalizar"){

		if(["1", "continar"].includes(text) ||
			["1", "continuar"].includes(buttonReply)){
			action = "options";
		}else if(["2", "salir"].includes(text) ||
			["2", "salir"].includes(buttonReply)){
			action = "salir";
		}else{
			action = "opcion invalida";
		}

	}else if(status === "options" &&
			(["3", "salir"].includes(text) ||
			["3", "salir"].includes(buttonReply))){
		action = "salir";

	}else if(status === "realizar_pago_3"){

		if(["1", "si"].includes(text) ||
			["1", "si"].includes(buttonReply)){
			action = "options";
		}else if(["2", "no"].includes(text) ||
			["2", "no"].includes(buttonReply)){
			action = "salir";
		}else{
			action = "opcion invalida";
		}
	}

	switch(action){
		case "options":
			await plantillas.showTemplate({
				template: "options",
				to: from
			});
			await api.updateStatus(from, "options");
			break;

		case "consultar saldo":
			await plantillas.showTemplate({
				template: "consulta_saldo",
				to: from
			});
			await api.updateStatus(from, "consulta_saldo");
			break;

		case "realizar pago":
			await plantillas.showTemplate({
				template: "realizar_pago",
				to: from
			});
			await api.updateStatus(from, "realizar_pago");
			break;

		case "espera numero consulta":
			let debtToCheck = await api.userData(text);
			if(debtToCheck.success === false){
				const checkResponse =  plantillas.showTemplate({
					template: "consulta_saldo_1",
					to: from,
					username: debtToCheck.username

				});
				break;
			}

			await plantillas.showTemplate({
				template: "consulta_saldo_2",
				to: from,
				username: debtToCheck.username,
				quantity: debtToCheck.quantity
			});
			api.updateStatus(from, "consulta_saldo_2");
			break;

		case "espera numero pago":
			let debtToPay = await api.userData(text);
			if(debtToPay.success === false){
				await plantillas.showTemplate({
					template: "consulta_saldo_1",
					to: from,
					username: debtToPay.username
				});
				await api.updateStatus(from, "consulta_saldo");
				break;
			}
			await plantillas.showTemplate({
				template: "realizar_pago_1",
				to: from,
				username: debtToPay.username,
				quantity: debtToPay.quantity
			});
			await api.updateStatus(from, "realizar_pago_1");
			break;

		case "proceder pago":
			await plantillas.showTemplate({
				template: "realizar_pago",
				to: from
			});
			await api.updateStatus(from, "confirmar_pago");			
			break;

		case "transaccion":
			const link = await pagos.createPayLink(from, text);
			await plantillas.showTemplate({
				template: "realizar_pago_paypal",
				to: from, 
				link: link.link
			});
			break;

		case "opcion invalida":
			await plantillas.showTemplate({
				template: "opcion_incorrecta",
				to: from
			});
			await plantillas.showTemplate({
				template: status,
				to: from
			});
			break;

		case "salir":
			await plantillas.showTemplate({
				template: "fin",
				to: from
			});
			await api.deleteStatus(from);
			break;

	}
}

export default {
	startConversation,
	manager
}