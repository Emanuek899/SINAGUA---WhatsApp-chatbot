import axios from 'axios';
import dotenv from 'dotenv';

dotenv.config();

const SECRET = process.env.PAYPAL_SECRET;
const CLIENT_ID = process.env.PAYPAL_CLIENT_ID;
let expiresAt = null;
let token = null

async function getToken(){
	try{
		const now = Date.now();
		if(token && expiresAt && now < expiresAt){
			return token;
		}
		const credentials = Buffer.from(`${CLIENT_ID}:${SECRET}`).toString('base64');
		const headers = {
		  'Authorization': `Basic ${credentials}`,
		  'Content-Type': 'application/x-www-form-urlencoded'
		};

		const data = new URLSearchParams();
		data.append('grant_type', 'client_credentials');

		try {
		  const response = await axios.post(
		    'https://api-m.sandbox.paypal.com/v1/oauth2/token',
		    data,
		    { headers }
		  );
		  token = response.data.access_token;
		  expiresAt = now + response.data.expires_in * 1000 // Miliseconds
		  return token;
		} catch (error) {
		  console.error('Error:', error.response?.data || error.message);
		}
	}catch(error){
		console.log(`Error in getToken ${error}`);
	}
}

export default {
	getToken
}