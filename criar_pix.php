<?php
session_start();
require 'conexao.php';

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

$token = "876c0e7e-6c40-4bab-b2cb-74c9b7ebb38a1b2b5e31474ebd38338005d3341beb0d83b4-0521-49e7-8221-782ef5a11f1e";

/* REFERÊNCIA */

$reference = "dep_" . time() . "_" . $usuario_id;

/* PEDIDO PIX */

$body = [
    "reference_id" => $reference,
    "items" => [
        [
            "name" => "Deposito carteira",
            "quantity" => 1,
            "unit_amount" => intval($total * 100)
        ]
    ],
    "qr_codes" => [
        [
            "amount" => [
                "value" => intval($total * 100)
            ]
        ]
    ]
];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://sandbox.api.pagseguro.com/orders",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($body)
]);

$response = curl_exec($curl);

$http = curl_getinfo($curl, CURLINFO_HTTP_CODE);

file_put_contents("debug_pagbank.json", $http . "\n" . $response);

curl_close($curl);

$data = json_decode($response, true);


/* validar retorno */

if (!isset($data["id"])) {
    echo json_encode($data);
    exit;
}

/* dados PIX */

$order_id = $data["id"];
$pix_qr = $data["qr_codes"][0]["links"][1]["href"] ?? null;
$pix_code = $data["qr_codes"][0]["text"] ?? "";

/* salvar transação */

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
    $order_id
]);

/* resposta */

echo json_encode([
    "qrcode" => $pix_qr,
    "pix_code" => $pix_code,
    "pedido" => $order_id
]);
