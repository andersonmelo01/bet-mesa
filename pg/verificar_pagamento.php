<?php
session_start();
require __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

if (!isset($_GET['txid'])) {
    echo json_encode(["status" => "erro"]);
    exit;
}

$txid = $_GET['txid'];

$stmt = $pdo->prepare("
SELECT status 
FROM transacoes 
WHERE pagbank_id = ?
");

$stmt->execute([$txid]);

$tx = $stmt->fetch();

if (!$tx) {
    echo json_encode(["status" => "erro"]);
    exit;
}

echo json_encode([
    "status" => $tx["status"]
]);
