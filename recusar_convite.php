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
    die("Convite inválido ou já utilizado");
}

/* MARCAR COMO RECUSADO */

$pdo->prepare("
UPDATE convites
SET aceitou=2
WHERE id=?
")->execute([$convite['id']]);

/* REMOVER PARTICIPANTE SE EXISTIR */

$pdo->prepare("
DELETE FROM participantes
WHERE mesa_id=? AND usuario_id=?
")->execute([$convite['mesa_id'], $usuario_id]);

echo "<h2>Convite recusado.</h2>";
echo "<a href='lobby.php'>Voltar para o lobby</a>";
