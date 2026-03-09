<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mesa_id = $_POST['mesa_id'] ?? null;
$resultado = $_POST['resultado'] ?? null;

if (!$mesa_id || $resultado === null) {
    die("Dados inválidos.");
}

/* =========================
VERIFICAR PARTICIPANTE
========================= */

$stmt = $pdo->prepare("
SELECT * FROM participantes
WHERE mesa_id=? AND usuario_id=? AND aceitou=1
");

$stmt->execute([$mesa_id, $usuario_id]);
$participante = $stmt->fetch();

if (!$participante) {
    die("Você não participa desta mesa.");
}

/* =========================
EVITAR RESULTADO DUPLICADO
========================= */

if ($participante["ganhou"] !== null) {
    die("Resultado já enviado.");
}

/* =========================
SALVAR RESULTADO
========================= */

$stmt = $pdo->prepare("
UPDATE participantes
SET ganhou=?
WHERE mesa_id=? AND usuario_id=?
");

$stmt->execute([$resultado, $mesa_id, $usuario_id]);

/* =========================
VERIFICAR SE TODOS ENVIARAM
========================= */

$stmt = $pdo->prepare("
SELECT COUNT(*) total,
SUM(CASE WHEN ganhou IS NOT NULL THEN 1 ELSE 0 END) enviados
FROM participantes
WHERE mesa_id=?
");

$stmt->execute([$mesa_id]);
$dados = $stmt->fetch();

/* =========================
FINALIZAR MESA
========================= */

if ($dados['total'] == $dados['enviados']) {

    /* buscar vencedor */

    $stmt = $pdo->prepare("
    SELECT usuario_id
    FROM participantes
    WHERE mesa_id=? AND ganhou=1
    LIMIT 1
    ");

    $stmt->execute([$mesa_id]);
    $vencedor = $stmt->fetch();

    if ($vencedor) {

        /* buscar valor aposta */

        $stmt = $pdo->prepare("
        SELECT valor_aposta
        FROM mesas
        WHERE id=?
        ");

        $stmt->execute([$mesa_id]);
        $mesa = $stmt->fetch();

        /* total jogadores */

        $stmt = $pdo->prepare("
        SELECT COUNT(*) total
        FROM participantes
        WHERE mesa_id=?
        ");

        $stmt->execute([$mesa_id]);
        $total = $stmt->fetch()["total"];

        $valor_total = $mesa["valor_aposta"] * $total;

        /* comissão casa 10% */

        $comissao = $valor_total * 0.10;

        /* premio */

        $premio = $valor_total - $comissao;

        /* pagar vencedor */

        $pdo->prepare("
        UPDATE carteira
        SET saldo = saldo + ?
        WHERE usuario_id=?
        ")->execute([$premio, $vencedor["usuario_id"]]);

        /* registrar histórico */

        $pdo->prepare("
        INSERT INTO transacoes
        (usuario_id,tipo,valor,descricao)
        VALUES (?,?,?,?)
        ")->execute([
            $vencedor["usuario_id"],
            "premio",
            $premio,
            "Premiação mesa " . $mesa_id
        ]);

        /* finalizar mesa */

        $pdo->prepare("
        UPDATE mesas
        SET status='finalizada', vencedor_id=?
        WHERE id=?
        ")->execute([
            $vencedor["usuario_id"],
            $mesa_id
        ]);
    }
}

header("Location: mesa.php?id=" . $mesa_id);
exit;
