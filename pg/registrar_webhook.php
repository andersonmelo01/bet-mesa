<?php

$client_id = "Client_Id_6515b26ca6ec3b145dd0cda9f2e168538f58c10e";
$client_secret = "Client_Secret_ce20fae771ae056ff857a1989cfaa32b8bc21793";

$certificado = __DIR__ . "/../producao-880829-bet-prod.p12";
$senha_certificado = "";

$pix_key = "23c3c302-52b9-4949-b31f-d372d1d41e8b";

$webhook = "https://lanchup.ct.ws/bet-mesa/pg/webhook_pg.php";

/* GERAR TOKEN */

$curl = curl_init();

curl_setopt_array($curl, [

    CURLOPT_URL => "https://pix.api.efipay.com.br/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,

    CURLOPT_SSLCERT => $certificado,
    CURLOPT_SSLCERTTYPE => "P12",

    CURLOPT_USERPWD => $client_id . ":" . $client_secret,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],

    CURLOPT_POSTFIELDS => json_encode([
        "grant_type" => "client_credentials"
    ])

]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

$token = $data["access_token"];


/* REGISTRAR WEBHOOK */

$body = [
    "webhookUrl" => $webhook
];

$curl = curl_init();

curl_setopt_array($curl, [

    CURLOPT_URL => "https://pix.api.efipay.com.br/v2/webhook/" . $pix_key,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_CUSTOMREQUEST => "PUT",

    CURLOPT_SSLCERT => $certificado,
    CURLOPT_SSLCERTTYPE => "P12",

    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ],

    CURLOPT_POSTFIELDS => json_encode($body)

]);

$response = curl_exec($curl);
curl_close($curl);

echo $response;
