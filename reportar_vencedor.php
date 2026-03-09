<?php

require 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado");
}

$usuario_id = $_SESSION['usuario_id'];

$mesa_id = $_POST['mesa_id'] ?? 0;
$vencedor_id = $_POST['vencedor_id'] ?? 0;

if (!$mesa_id || !$vencedor_id) {
    die("Dados inválidos.");
}

/* BUSCAR PARTICIPANTES */

$stmt = $pdo->prepare("
SELECT usuario_id 
FROM participantes 
WHERE mesa_id=?
");

$stmt->execute([$mesa_id]);
$players = $stmt->fetchAll();

if (count($players) < 2) {
    die("Mesa precisa ter dois jogadores.");
}

$j1 = $players[0]['usuario_id'];
$j2 = $players[1]['usuario_id'];

/* SALVAR VOTO */

if ($usuario_id == $j1) {

    $pdo->prepare("
    UPDATE mesas
    SET vencedor_reportado_1=?
    WHERE id=?
    ")->execute([$vencedor_id, $mesa_id]);
} else {

    $pdo->prepare("
    UPDATE mesas
    SET vencedor_reportado_2=?
    WHERE id=?
    ")->execute([$vencedor_id, $mesa_id]);
}

/* VERIFICAR SE OS DOIS VOTARAM */

$stmt = $pdo->prepare("
SELECT vencedor_reportado_1, vencedor_reportado_2
FROM mesas
WHERE id=?
");

$stmt->execute([$mesa_id]);
$mesa = $stmt->fetch();

if ($mesa['vencedor_reportado_1'] && $mesa['vencedor_reportado_2']) {

    if ($mesa['vencedor_reportado_1'] == $mesa['vencedor_reportado_2']) {

        /* FINALIZA MESA */

        $pdo->prepare("
        UPDATE mesas
        SET status='finalizada',
        vencedor_id=?
        WHERE id=?
        ")->execute([$mesa['vencedor_reportado_1'], $mesa_id]);
    }
}

/* VOLTAR PARA MESA */

header("Location: mesa.php?id=" . $mesa_id);
exit;
