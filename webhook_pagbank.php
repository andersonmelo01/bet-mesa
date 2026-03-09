<?php

require 'conexao.php';

/* log para debug */

file_put_contents("log_webhook.txt", file_get_contents("php://input") . PHP_EOL, FILE_APPEND);

$body = file_get_contents("php://input");
$data = json_decode($body, true);

/* validar dados */

if (!isset($data["id"])) {
    http_response_code(400);
    exit;
}

$order_id = $data["id"];
$status = $data["charges"][0]["status"] ?? null;

/* apenas pagamento confirmado */

if ($status !== "PAID") {
    exit;
}

/* buscar transação */

$stmt = $pdo->prepare("
SELECT * FROM transacoes
WHERE pagbank_id=?
");

$stmt->execute([$order_id]);
$transacao = $stmt->fetch();

if (!$transacao) {
    exit;
}

/* evitar crédito duplicado */

if ($transacao["status"] == "pago") {
    exit;
}

try {

    $pdo->beginTransaction();

    /* marcar como pago */

    $pdo->prepare("
    UPDATE transacoes
    SET status='pago'
    WHERE id=?
    ")->execute([$transacao["id"]]);

    /* adicionar saldo */

    $pdo->prepare("
    UPDATE carteira
    SET saldo = saldo + ?
    WHERE usuario_id=?
    ")->execute([
        $transacao["valor"],
        $transacao["usuario_id"]
    ]);

    $pdo->commit();
} catch (Exception $e) {

    $pdo->rollBack();

    file_put_contents("log_webhook.txt", $e->getMessage() . PHP_EOL, FILE_APPEND);
}

http_response_code(200);
echo "OK";
