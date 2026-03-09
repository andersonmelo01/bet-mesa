<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Plataforma de Mesas de Apostas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .main-card {
            background: white;
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
        }

        .btn-main {
            padding: 12px 25px;
            font-size: 18px;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-8">

                <div class="main-card text-center">

                    <div class="logo mb-4">
                        🎲 Plataforma de Mesas
                    </div>

                    <h2 class="mb-3">
                        Crie e gerencie mesas de apostas
                    </h2>

                    <p class="text-muted mb-4">
                        Convide amigos, registre apostas e acompanhe quem ganhou de forma simples e organizada.
                    </p>

                    <div class="d-flex justify-content-center gap-3">

                        <a href="login.php" class="btn btn-primary btn-main">
                            Entrar
                        </a>

                        <a href="cadastro.php" class="btn btn-outline-primary btn-main">
                            Criar Conta
                        </a>

                    </div>

                    <hr class="my-4">

                    <div class="row text-center">

                        <div class="col-md-4">
                            <h5>👥 Mesas</h5>
                            <p class="text-muted">Crie mesas privadas para apostas.</p>
                        </div>

                        <div class="col-md-4">
                            <h5>📨 Convites</h5>
                            <p class="text-muted">Convide participantes facilmente.</p>
                        </div>

                        <div class="col-md-4">
                            <h5>🏆 Resultados</h5>
                            <p class="text-muted">Controle quem ganhou.</p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>