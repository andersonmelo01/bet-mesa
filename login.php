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

        /* =========================
           VERIFICAR SE EXISTE CONVITE
        ========================= */

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

    <title>Login - Plataforma de Apostas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(120deg, #6a11cb, #2575fc);
            height: 100vh;
        }

        .card-login {
            max-width: 400px;
            margin: auto;
            margin-top: 10%;
            padding: 2rem;
            border-radius: 1rem;
            background: #ffffffcc;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #2575fc;
        }

        .btn-primary {
            background: #2575fc;
            border: none;
        }

        .btn-primary:hover {
            background: #6a11cb;
        }
    </style>

</head>

<body>

    <div class="card card-login">

        <h3 class="text-center mb-4">Entrar na Plataforma</h3>

        <?php if ($erro): ?>

            <div class="alert alert-danger">
                <?= $erro ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">Usuário ou Email</label>

                <input
                    type="text"
                    class="form-control"
                    name="usuario"
                    placeholder="Seu Nik ou Email"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">Senha</label>

                <input
                    type="password"
                    class="form-control"
                    name="senha"
                    placeholder="Sua senha"
                    required>

            </div>

            <div class="d-grid">

                <button type="submit" class="btn btn-primary btn-lg">
                    Entrar
                </button>

            </div>

        </form>

        <p class="text-center mt-3">
            Não tem conta? <a href="cadastro.php">Cadastre-se</a>
        </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>