<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado");
}

if (!isset($_POST['mesa_id'])) {
    die("Mesa inválida");
}

$usuario_id = $_SESSION['usuario_id'];
$mesa_id = intval($_POST['mesa_id']);

try {

    $pdo->beginTransaction();

    /* VERIFICAR SE USUÁRIO ESTÁ NA MESA */

    $stmt = $pdo->prepare("
SELECT * FROM participantes
WHERE mesa_id=? AND usuario_id=?
");
    $stmt->execute([$mesa_id, $usuario_id]);
    $participante = $stmt->fetch();

    if (!$participante) {
        throw new Exception("Usuário não está nessa mesa");
    }

    if ($participante["pagou"] == 1) {
        throw new Exception("Pagamento já realizado");
    }

    /* BUSCAR MESA */

    $stmt = $pdo->prepare("
SELECT valor_aposta,status
FROM mesas
WHERE id=?
");
    $stmt->execute([$mesa_id]);
    $mesa = $stmt->fetch();

    if (!$mesa) {
        throw new Exception("Mesa não encontrada");
    }

    if ($mesa["status"] != "aguardando") {
        throw new Exception("Mesa já iniciada");
    }

    $valor = $mesa["valor_aposta"];

    /* BUSCAR SALDO */

    $stmt = $pdo->prepare("
SELECT saldo
FROM carteira
WHERE usuario_id=?
");
    $stmt->execute([$usuario_id]);
    $carteira = $stmt->fetch();

    if (!$carteira) {
        throw new Exception("Carteira não encontrada");
    }

    if ($carteira["saldo"] < $valor) {
        throw new Exception("Saldo insuficiente");
    }

    /* DESCONTAR SALDO */

    $stmt = $pdo->prepare("
UPDATE carteira
SET saldo = saldo - ?
WHERE usuario_id=?
");
    $stmt->execute([$valor, $usuario_id]);

    /* REGISTRAR TRANSAÇÃO */

    $stmt = $pdo->prepare("
INSERT INTO transacoes
(usuario_id,valor,tipo,status)
VALUES (?,?,?,?)
");

    $stmt->execute([
        $usuario_id,
        $valor,
        "aposta",
        "pago"
    ]);

    /* CONFIRMAR PAGAMENTO */

    $stmt = $pdo->prepare("
UPDATE participantes
SET pagou=1
WHERE mesa_id=? AND usuario_id=?
");
    $stmt->execute([$mesa_id, $usuario_id]);

    /* VERIFICAR PAGAMENTOS */

    $stmt = $pdo->prepare("
SELECT 
COUNT(*) as total,
COALESCE(SUM(pagou),0) as pagos
FROM participantes
WHERE mesa_id=?
");

    $stmt->execute([$mesa_id]);
    $dados = $stmt->fetch();

    /* ATIVAR MESA */

    if ($dados["total"] == $dados["pagos"]) {

        $stmt = $pdo->prepare("
UPDATE mesas
SET status='ativa'
WHERE id=?
");

        $stmt->execute([$mesa_id]);
    }

    $pdo->commit();

    header("Location: mesa.php?id=" . $mesa_id);
    exit;
} catch (Exception $e) {

    $pdo->rollBack();

    echo "Erro: " . $e->getMessage();
}
