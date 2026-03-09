<?php

require 'conexao.php';

/* LOG DEBUG */

file_put_contents(
    "log_webhook.txt",
    date("Y-m-d H:i:s") . " | " . file_get_contents("php://input") . PHP_EOL,
    FILE_APPEND
);

$body = file_get_contents("php://input");
$data = json_decode($body, true);

/* validar webhook */

if (!isset($data["pix"])) {
    http_response_code(400);
    exit;
}

/* percorrer pagamentos */

foreach ($data["pix"] as $pix) {

    $txid = $pix["txid"] ?? null;

    if (!$txid) {
        continue;
    }

    /* buscar transação */

    $stmt = $pdo->prepare("
SELECT * FROM transacoes
WHERE pagbank_id=?
");

    $stmt->execute([$txid]);

    $transacao = $stmt->fetch();

    if (!$transacao) {
        continue;
    }

    /* evitar crédito duplicado */

    if ($transacao["status"] == "pago") {
        continue;
    }

    try {

        $pdo->beginTransaction();

        /* atualizar status */

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

        file_put_contents(
            "log_webhook.txt",
            "ERRO: " . $e->getMessage() . PHP_EOL,
            FILE_APPEND
        );
    }
}

http_response_code(200);
echo "OK";
