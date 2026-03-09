<?php

session_start();
require 'conexao.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Convite inválido");
}

/* LOGIN */

if (!isset($_SESSION['usuario_id'])) {

    $_SESSION['convite_token'] = $token;

    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/* BUSCAR CONVITE */

$stmt = $pdo->prepare("
SELECT * FROM convites
WHERE token=? AND aceitou=0
");

$stmt->execute([$token]);
$convite = $stmt->fetch();

if (!$convite) {
    die("Convite inválido ou já usado");
}

/* VERIFICAR PARTICIPANTE */

$check = $pdo->prepare("
SELECT id FROM participantes
WHERE mesa_id=? AND usuario_id=?
");

$check->execute([$convite['mesa_id'], $usuario_id]);

if ($check->rowCount()) {

    $update = $pdo->prepare("
UPDATE participantes
SET aceitou=1
WHERE mesa_id=? AND usuario_id=?
");

    $update->execute([$convite['mesa_id'], $usuario_id]);
} else {

    $insert = $pdo->prepare("
INSERT INTO participantes (mesa_id,usuario_id,aceitou)
VALUES (?,?,1)
");

    $insert->execute([$convite['mesa_id'], $usuario_id]);
}

/* MARCAR CONVITE */

$pdo->prepare("
UPDATE convites
SET aceitou=1
WHERE id=?
")->execute([$convite['id']]);

/* REDIRECIONAR */

header("Location: mesa.php?id=" . $convite['mesa_id']);
exit;
