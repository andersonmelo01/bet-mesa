<?php
session_start();
require 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = trim($_POST['nome']);
    $nik = trim($_POST['nik']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    try {

        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, nik, email, senha) 
            VALUES (?,?,?,?)
        ");

        $stmt->execute([$nome, $nik, $email, $senha]);

        /* LOGIN AUTOMÁTICO */

        $usuario_id = $pdo->lastInsertId();

        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['nik'] = $nik;
        $_SESSION['nome'] = $nome;

        /* VERIFICAR CONVITE */

        if (isset($_SESSION['convite_token'])) {

            $token = $_SESSION['convite_token'];
            unset($_SESSION['convite_token']);

            header("Location: aceitar_convite.php?token=" . $token);
            exit;
        }

        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {

        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $erro = "Nik ou Email já cadastrado.";
        } else {
            $erro = "Erro ao cadastrar usuário.";
        }
    }
}
?>

<!doctype html>
<html lang="pt-br">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cadastro - Plataforma de Mesas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b, #020617);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* CARD */

        .card-cadastro {

            width: 100%;
            max-width: 420px;

            padding: 40px;

            border-radius: 15px;

            background: rgba(255, 255, 255, 0.08);

            backdrop-filter: blur(15px);

            border: 1px solid rgba(255, 255, 255, 0.15);

            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);

            color: #fff;

        }

        /* TITULO */

        .titulo {

            font-weight: 700;

            text-align: center;

            margin-bottom: 25px;

        }

        /* INPUT */

        .form-control {

            background: rgba(255, 255, 255, 0.08);

            border: 1px solid rgba(255, 255, 255, 0.2);

            color: #fff;

        }

        .form-control:focus {

            background: rgba(255, 255, 255, 0.12);

            border-color: #22c55e;

            box-shadow: none;

            color: #fff;

        }

        label {
            color: #cbd5e1;
        }

        /* BOTÃO */

        .btn-cadastro {

            background: #22c55e;

            border: none;

            font-weight: 600;

            padding: 12px;

            transition: 0.3s;

        }

        .btn-cadastro:hover {

            background: #16a34a;

        }

        /* LINK */

        .link-login {

            color: #22c55e;

            text-decoration: none;

            font-weight: 600;

        }

        .link-login:hover {

            text-decoration: underline;

        }

        /* LOGO */

        .logo {

            text-align: center;

            font-size: 28px;

            font-weight: 700;

            margin-bottom: 10px;

            color: #22c55e;

        }

        /* ALERT */

        .alert {
            font-size: 14px;
        }
    </style>

</head>

<body>

    <div class="card-cadastro">

        <div class="logo">
            🎲 Plataforma de Mesas
        </div>

        <h4 class="titulo">Criar Conta</h4>

        <?php if ($erro): ?>

            <div class="alert alert-danger">
                <?= $erro ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label>Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Nik</label>
                <input type="text" name="nik" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-cadastro">
                    Criar Conta
                </button>
            </div>

        </form>

        <p class="text-center mt-4">
            Já possui conta?
            <a href="login.php" class="link-login">Entrar</a>
        </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>