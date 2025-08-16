import axios from 'axios';
const BASE_URL = "http://localhost/SINAGUA/APU/ajax";

async function saveStatus(from, status){
	try{
		const statusResponse = await axios.post(`${BASE_URL}/Status.php`,
		{"num": from, "status": status},
		{
			headers: {
				"Content-Type": "application/json"
			}
		});
	} catch(error){
		console.log(`Error aqui ${error}`)
	}
}

async function updateStatus(from, status){
	const statusResponse = await axios.put(`${BASE_URL}/Status.php`,
	{"num": from, "status": status},
	{
		headers: {
			"Content-Type": "application/json"
		}
	});
	const statusData = statusResponse.data;
	if(statusResponse === 400){
			return 400;
	} else if(statusResponse === 500){
			return 500;
	}
}

async function deleteStatus(from){
	const statusResponse = await axios.delete(`${BASE_URL}/Status.php?num=${from}`);
	const statusData = statusResponse.data;
	if(statusResponse === 400){
			return 400;
	} else if(statusResponse === 500){
			return 500;
	}
}

async function userData(accountNum){
	const accountResponse = await axios.get(`${BASE_URL}/AccountNumbers.php?num=${accountNum}`);
	const accountData = accountResponse.data;
	const debtResponse = await axios.get(`${BASE_URL}/Debts.php?accountId=${accountData.id}`);
	const debtData = debtResponse.data;
	const userResponse = await axios.get(`${BASE_URL}/Users.php?account=${accountData.id}`);
	const username = userResponse.data.name1;
	if(debtData.success === false && debtData.message === "User has no debts"){
		return {"success": false, "username": username};
	}
	return {
		"username": username,
		"quantity": debtData.data.quantity.toLocaleString('es-MX', {
			style: 'currency',
			currency: 'MXN'
		})
	};
}

export default{
	saveStatus,
	updateStatus,
	deleteStatus,
	userData,
}