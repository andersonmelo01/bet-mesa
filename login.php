<?php
session_start();
require 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $usuario = trim($_POST['usuario']); // nik ou email
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("
        SELECT * FROM usuarios 
        WHERE nik=? OR email=? 
        LIMIT 1
    ");

    $stmt->execute([$usuario, $usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {

        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nik'] = $user['nik'];
        $_SESSION['nome'] = $user['nome'];

        /* VERIFICAR CONVITE */

        if (isset($_SESSION['convite_token'])) {

            $token = $_SESSION['convite_token'];
            unset($_SESSION['convite_token']);

            header("Location: aceitar_convite.php?token=" . $token);
            exit;
        }

        header("Location: dashboard.php");
        exit;
    } else {

        $erro = "Usuário ou senha inválidos.";
    }
}
?>

<!doctype html>
<html lang="pt-br">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login - Plataforma de Mesas</title>

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

        .card-login {

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

        /* LOGO */

        .logo {

            text-align: center;

            font-size: 28px;

            font-weight: 700;

            margin-bottom: 10px;

            color: #22c55e;

        }

        /* TITULO */

        .titulo {

            text-align: center;

            margin-bottom: 25px;

            font-weight: 600;

        }

        /* INPUT */

        .form-control {

            background: rgba(255, 255, 255, 0.08);

            border: 1px solid rgba(255, 255, 255, 0.2);

            color: #fff;

        }

        .form-control::placeholder {
            color: #cbd5e1;
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

        .btn-login {

            background: #22c55e;

            border: none;

            font-weight: 600;

            padding: 12px;

            transition: 0.3s;

        }

        .btn-login:hover {

            background: #16a34a;

        }

        /* LINK */

        .link-cadastro {

            color: #22c55e;

            text-decoration: none;

            font-weight: 600;

        }

        .link-cadastro:hover {
            text-decoration: underline;
        }

        /* ALERT */

        .alert {
            font-size: 14px;
        }
    </style>

</head>

<body>

    <div class="card-login">

        <div class="logo">
            🎲 Plataforma de Mesas
        </div>

        <h4 class="titulo">
            Entrar na Plataforma
        </h4>

        <?php if ($erro): ?>

            <div class="alert alert-danger">
                <?= $erro ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">

                <label>Usuário ou Email</label>

                <input
                    type="text"
                    class="form-control"
                    name="usuario"
                    placeholder="Seu Nik ou Email"
                    required>

            </div>

            <div class="mb-3">

                <label>Senha</label>

                <input
                    type="password"
                    class="form-control"
                    name="senha"
                    placeholder="Sua senha"
                    required>

            </div>

            <div class="d-grid mt-4">

                <button type="submit" class="btn btn-login">
                    Entrar
                </button>

            </div>

        </form>

        <p class="text-center mt-4">

            Não tem conta?
            <a href="cadastro.php" class="link-cadastro">Cadastre-se</a>

        </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>