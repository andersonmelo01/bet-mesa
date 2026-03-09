<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_logado = $_SESSION['usuario_id'];
$mesa_id = $_GET['mesa'] ?? null;
$usuario_remover = $_GET['usuario'] ?? null;

if (!$mesa_id || !$usuario_remover) {
    die("Dados inválidos.");
}

/* =========================
   VERIFICAR CRIADOR DA MESA
========================= */

$stmt = $pdo->prepare("
SELECT criador_id 
FROM mesas 
WHERE id=?
");

$stmt->execute([$mesa_id]);
$mesa = $stmt->fetch();

if (!$mesa) {
    die("Mesa não encontrada.");
}

/* =========================
   SOMENTE CRIADOR PODE REMOVER
========================= */

if ($mesa['criador_id'] != $usuario_logado) {
    die("Você não tem permissão para remover participantes.");
}

/* =========================
   REMOVER PARTICIPANTE
========================= */

$delete = $pdo->prepare("
DELETE FROM participantes
WHERE mesa_id=? AND usuario_id=?
");

$delete->execute([$mesa_id, $usuario_remover]);

header("Location: mesa.php?id=" . $mesa_id);
exit;
