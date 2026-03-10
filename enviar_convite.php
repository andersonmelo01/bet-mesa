<?php
require 'conexao.php';
session_start();

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado");
}

if (!isset($_POST['mesa_id']) || !isset($_POST['destino'])) {
    die("Dados inválidos.");
}

$mesa_id = intval($_POST['mesa_id']);
$destino = trim($_POST['destino']);

/* ==============================
   BUSCAR USUARIO (SE EXISTIR)
============================== */

$stmt = $pdo->prepare("
SELECT id,email 
FROM usuarios 
WHERE email=? OR nik=?
");

$stmt->execute([$destino, $destino]);
$user = $stmt->fetch();

$usuario_destino = null;
$email = $destino;

if ($user) {
    $usuario_destino = $user['id'];
    $email = $user['email'];
}

/* ==============================
   VERIFICAR SE JA ESTA NA MESA
============================== */

if ($usuario_destino) {

    $stmt = $pdo->prepare("
    SELECT id 
    FROM participantes 
    WHERE mesa_id=? AND usuario_id=?
    ");

    $stmt->execute([$mesa_id, $usuario_destino]);

    if ($stmt->fetch()) {

        $_SESSION['msg_erro'] = "⚠️ Este usuário já está participando da mesa.";

        header("Location: mesa.php?id=" . $mesa_id);
        exit;
    }
}

/* ==============================
   VERIFICAR SE JA EXISTE CONVITE
============================== */

if ($usuario_destino) {

    $stmt = $pdo->prepare("
    SELECT aceitou 
    FROM convites
    WHERE mesa_id=? AND usuario_id=?
    ORDER BY id DESC
    LIMIT 1
    ");

    $stmt->execute([$mesa_id, $usuario_destino]);
    $convite = $stmt->fetch();

    if ($convite) {

        if ($convite['aceitou'] == 0) {

            $_SESSION['msg_erro'] = "⚠️ Já existe um convite pendente para este jogador.";

            header("Location: mesa.php?id=" . $mesa_id);
            exit;
        }

        if ($convite['aceitou'] == 1) {

            $_SESSION['msg_erro'] = "⚠️ Este jogador já aceitou o convite da mesa.";

            header("Location: mesa.php?id=" . $mesa_id);
            exit;
        }

        /* Se recusou (2) pode reenviar */
    }
}

/* ==============================
   GERAR TOKEN
============================== */

$token = bin2hex(random_bytes(16));

/* ==============================
   SALVAR CONVITE
============================== */

$stmt = $pdo->prepare("
INSERT INTO convites (mesa_id, usuario_id, token, aceitou)
VALUES (?,?,?,0)
");

$stmt->execute([$mesa_id, $usuario_destino, $token]);

/* ==============================
   LINK DO CONVITE
============================== */

$linkAceitar = "http://localhost/apostas/aceitar_convite.php?token=" . $token;
$linkRecusar = "http://localhost/apostas/recusar_convite.php?token=" . $token;

/* ==============================
   ENVIAR EMAIL
============================== */

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    $mail->Username   = 'amssistemas95@gmail.com';
    $mail->Password   = 'ciqywjgfuuurcppk';

    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('amssistemas95@gmail.com', 'Plataforma de Mesas');

    $mail->addAddress($email);

    $mail->isHTML(true);

    $mail->Subject = 'Convite para participar da mesa';

    $mail->Body = "

    <h2>🎮 Convite para uma mesa</h2>

    <p>Você foi convidado para participar de uma mesa de apostas.</p>

    <p>Escolha uma opção:</p>

    <p>
    <a href='$linkAceitar' 
    style='padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;border-radius:5px;'>
    ACEITAR CONVITE
    </a>
    </p>

    <p>
    <a href='$linkRecusar'
    style='padding:10px 20px;background:#dc3545;color:#fff;text-decoration:none;border-radius:5px;'>
    RECUSAR CONVITE
    </a>
    </p>

    <p>Se você não possui cadastro, será solicitado ao acessar o link.</p>

    ";

    $mail->send();

    /* MENSAGEM DE SUCESSO */

    $_SESSION['msg_sucesso'] = "✅ Convite enviado com sucesso para <b>$email</b>.";
} catch (Exception $e) {

    $_SESSION['msg_erro'] = "❌ Erro ao enviar email: " . $mail->ErrorInfo;
}

/* ==============================
   REDIRECIONAR
============================== */

header("Location: mesa.php?id=" . $mesa_id);
exit;
