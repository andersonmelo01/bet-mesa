<?php

session_start();

require __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["erro" => "Usuário não logado"]);
    exit;
}

if (!isset($_POST['valor'])) {
    echo json_encode(["erro" => "Valor inválido"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$total = floatval($_POST['valor']);

if ($total <= 0) {
    echo json_encode(["erro" => "Valor inválido"]);
    exit;
}

/* CREDENCIAIS EFI */

$client_id = "Client_Id_6515b26ca6ec3b145dd0cda9f2e168538f58c10e";
$client_secret = "Client_Secret_ce20fae771ae056ff857a1989cfaa32b8bc21793";
$pix_key = "23c3c302-52b9-4949-b31f-d372d1d41e8b";

/* CERTIFICADO */

$certificado = __DIR__ . "/../producao-880829-bet-prod.p12";
$senha_certificado = "";

/* GERAR TOKEN */

$curl = curl_init();

curl_setopt_array($curl, [

    CURLOPT_URL => "https://pix.api.efipay.com.br/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,

    CURLOPT_SSLCERT => $certificado,
    CURLOPT_SSLCERTTYPE => "P12",
    CURLOPT_SSLCERTPASSWD => $senha_certificado,

    CURLOPT_USERPWD => $client_id . ":" . $client_secret,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],

    CURLOPT_POSTFIELDS => json_encode([
        "grant_type" => "client_credentials"
    ])

]);

$response = curl_exec($curl);

$http = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if (curl_errno($curl)) {
    echo json_encode([
        "erro" => "Erro CURL TOKEN",
        "detalhe" => curl_error($curl)
    ]);
    exit;
}

curl_close($curl);

$data = json_decode($response, true);

if (!isset($data["access_token"])) {

    echo json_encode([
        "erro" => "Erro gerar token",
        "http" => $http,
        "resposta" => $data
    ]);

    exit;
}

$token = $data["access_token"];


/* GERAR TXID */

//$txid = "dep_" . time() . "_" . $usuario_id;
//$txid = "dep" . time() . $usuario_id
//$txid = substr("dep" . time() . $usuario_id, 0, 35);
$txid = substr(bin2hex(random_bytes(16)), 0, 32);


/* CRIAR COBRANÇA PIX */
$body = [

    "calendario" => [
        "expiracao" => 3600
    ],

    "valor" => [
        "original" => number_format($total, 2, '.', '')
    ],

    "chave" => $pix_key,

    "solicitacaoPagador" => "Deposito carteira"

];

$curl = curl_init();

curl_setopt_array($curl, [

    CURLOPT_URL => "https://pix.api.efipay.com.br/v2/cob/" . $txid,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "PUT",

    CURLOPT_SSLCERT => $certificado,
    CURLOPT_SSLCERTTYPE => "P12",
    CURLOPT_SSLCERTPASSWD => $senha_certificado,

    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ],

    CURLOPT_POSTFIELDS => json_encode($body)

]);

$response = curl_exec($curl);

$http = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if (curl_errno($curl)) {
    echo json_encode([
        "erro" => "Erro CURL COB",
        "detalhe" => curl_error($curl)
    ]);
    exit;
}

curl_close($curl);

$cob = json_decode($response, true);

if (!isset($cob["txid"])) {

    echo json_encode([
        "erro" => "Erro criar cobrança",
        "http" => $http,
        "resposta" => $cob
    ]);

    exit;
}

/* GERAR QR CODE */

$curl = curl_init();

curl_setopt_array($curl, [

    CURLOPT_URL => "https://pix.api.efipay.com.br/v2/loc/" . $cob["loc"]["id"] . "/qrcode",
    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_SSLCERT => $certificado,
    CURLOPT_SSLCERTTYPE => "P12",
    CURLOPT_SSLCERTPASSWD => $senha_certificado,

    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $token
    ]

]);

$response = curl_exec($curl);

$http = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if (curl_errno($curl)) {
    echo json_encode([
        "erro" => "Erro CURL QRCODE",
        "detalhe" => curl_error($curl)
    ]);
    exit;
}

curl_close($curl);

$qrcode = json_decode($response, true);

if (!isset($qrcode["qrcode"])) {

    echo json_encode([
        "erro" => "Erro gerar QRCode",
        "http" => $http,
        "resposta" => $qrcode
    ]);

    exit;
}


/* SALVAR TRANSAÇÃO */

$stmt = $pdo->prepare("
INSERT INTO transacoes
(usuario_id,valor,tipo,status,pagbank_id)
VALUES (?,?,?,?,?)
");

$stmt->execute([

    $usuario_id,
    $total,
    'deposito',
    'pendente',
    $txid

]);


/* RESPOSTA */

echo json_encode([

    "imagem" => $qrcode["imagemQrcode"],
    "codigo" => $qrcode["qrcode"],
    "txid" => $txid

]);
