<?php
require 'conexao.php';
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel'] != 'admin') die("Acesso negado");

$mesa_id = intval($_POST['mesa_id'] ?? 0);
$vencedor_id = intval($_POST['vencedor_id'] ?? 0);

if ($mesa_id > 0 && $vencedor_id > 0) {
    $stmt = $pdo->prepare("UPDATE mesas SET vencedor_id=?, status='finalizada' WHERE id=?");
    $stmt->execute([$vencedor_id, $mesa_id]);
    $_SESSION['msg_sucesso'] = "Mesa finalizada com vencedor definido.";
}

header("Location: admin_mesas.php");
exit;
