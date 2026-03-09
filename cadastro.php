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

        /* =========================
           LOGIN AUTOMÁTICO
        ========================= */

        $usuario_id = $pdo->lastInsertId();

        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['nik'] = $nik;
        $_SESSION['nome'] = $nome;

        /* =========================
           VERIFICAR CONVITE
        ========================= */

        if (isset($_SESSION['convite_token'])) {

            $token = $_SESSION['convite_token'];
            unset($_SESSION['convite_token']);

            header("Location: aceitar_convite.php?token=" . $token);
            exit;
        }

        /* =========================
           REDIRECIONAR NORMAL
        ========================= */

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

    <title>Cadastro - Plataforma de Apostas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(120deg, #6a11cb, #2575fc);
            height: 100vh;
        }

        .card-cadastro {
            max-width: 450px;
            margin: auto;
            margin-top: 8%;
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

    <div class="card card-cadastro">

        <h3 class="text-center mb-4">Cadastro de Usuário</h3>

        <?php if ($erro): ?>

            <div class="alert alert-danger">
                <?= $erro ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nik</label>
                <input type="text" name="nik" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    Cadastrar
                </button>
            </div>

        </form>

        <p class="text-center mt-3">
            Já tem conta? <a href="login.php">Entrar</a>
        </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>