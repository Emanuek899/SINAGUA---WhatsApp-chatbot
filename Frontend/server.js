import express from 'express';
import cors from 'cors';
// const token = require("./token");
import path from 'path';
import axios from 'axios';
import fs from 'fs';
import pagos from './tools/pagos.js';
import token from './tools/token.js';
import ngrok from 'ngrok';
import manejador from './tools/manejador.js';
import dotenv from 'dotenv';

dotenv.config();

const PORT = process.env.PORT;
const VERIFY_TOKEN = process.env.VERIFY_WEBHOOK_META;
const authToken = `Bearer ${VERIFY_TOKEN}`;
const BASEURL = process.env.BACKEND_URL;
const app = express();

app.use(express.json());
app.use(
    cors({
        origin: "http://localhost:3000",
        methods: ["GET", "POST", "PUT", "DELETE"],
        allowedHeaders: ["Content-Type", "Authorization"],
    })
);

// Manejo de verificaciones (GET)
app.get("/webhook/meta", (req, res) =>{
    console.log("Meta verification request received");
    
    console.log(`Body ${JSON.stringify(req.body, null, 2)}`);

    const mode = req.query["hub.mode"];
    const token = req.query["hub.verify_token"];
    const challenge = req.query["hub.challenge"];

    if(mode === "subscribe" && token == VERIFY_TOKEN){
        console.log("Verification succesfully");
        res.status(200).send(challenge);
    } else {
        console.warn("Verification failed");
        fs.appendFileSync("logs/webhook_verification.txt", 
            `${new Date().toISOString()} - Webhook verification failed, please check Meta configurationn\n${JSON.stringify(req.body, null, 2)}`);
        res.sendStatus(403);
    }
});

app.post("/webhook/meta", async (req, res) =>{
    try{
        res.sendStatus(200);
        const entry = req.body.entry?.[0];          
        const changes = entry?.changes?.[0];        
        const value = changes?.value;                
        const messages = value?.messages; 

        if(messages && messages.length > 0){
            console.log("Webhook Meta recibio informacion para su uso");
            const message = messages[0];
            const from = messages[0].from;
            const text = messages[0].text?.body?.toLowerCase() || "";
            const buttonReply = message?.interactive?.button_reply?.id?.toLowerCase() || message?.button?.payload?.toLowerCase() || "";
            const statusResponse = await axios.get(`${BASEURL}Status.php?num=${from}`);
            const statusData = statusResponse.data;
            console.log(`Mensaje de: ${from}, con texto ${text}`);
            if(statusData.message === `No status found for ${from || buttonReply}`){
                await manejador.startConversation(text, from);
            } else {
                await manejador.manager(text, from, buttonReply, statusData.data.status);
            }
        }

        return;
    }catch(error){
        console.log(`Error ${error}`);
        fs.appendFileSync("logs/webhook_meta_post.txt",
            `${new Date().toISOString()} - Webhook meta post error: ${error}\n${JSON.stringify(req.body, null, 2)}`);
    }
});



 /**==================== Paypal Webhook ====================*/
app.post('/webhook/paypal', async (req, res) =>{
    try{
        console.log("Webhook Paypal Recibio informacion")
        const data = req.body;
        const eventType = data.event_type;
        const status = data.resource.status;
        const customId = data.resource.custom_id;
        res.sendStatus(200);
        if(eventType === "PAYMENT.CAPTURE.COMPLETED"){
            if(status === "COMPLETED"){
                const parsedData = {};
                customId.split(";").forEach(pair => {
                  const [key, value] = pair.split(":");
                  parsedData[key] = value;
                });
                await pagos.proccessPay(parsedData.from, parsedData.account);
            }
        }
    }catch(error){
        console.log(`Error en webhook paypal ${error}`);
        res.sendStatus(500);
    }
});

app.get('/webhook/paypal/capture', async (req, res) =>{
    try{
        const accessToken = await token.getToken(); // asegúrate de que sea el string del token
        const orderId = req.query.token;
        const response = await axios.post(
          `https://api.sandbox.paypal.com/v2/checkout/orders/${orderId}/capture`,
          {},
          {
            headers: {
              Authorization: `Bearer ${accessToken}`,
              'Content-Type': 'application/json'
            }
          }
        );
        res.send("✅ Pago capturado correctamente. Gracias.");
    }catch(error){
        console.log(`Error en webhook paypal captura${error}`);
        res.sendStatus(500);
    }
});

app.listen(PORT, async () => {
    console.log(`servidor corriendo en ${PORT}`);
});
