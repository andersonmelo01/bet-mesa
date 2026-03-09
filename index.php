<?php
session_start();

// Redirecionar se já estiver logado
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
        /* ======================
           ESTILOS GERAIS
        ====================== */
        body {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Arial', sans-serif;
            font-style: italic;
            color: #f1f5f9;
        }

        .main-card {
            background: rgba(30, 41, 59, 0.95);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
            color: #f1f5f9;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #22c55e;
            font-style: italic;
        }

        h2,
        h5,
        p {
            font-style: italic;
        }

        .btn-main {
            padding: 12px 25px;
            font-size: 18px;
            font-style: italic;
            border-radius: 8px;
        }

        .btn-primary {
            background: #22c55e;
            border: none;
        }

        .btn-primary:hover {
            background: #16a34a;
        }

        .btn-outline-primary {
            color: #22c55e;
            border-color: #22c55e;
        }

        .btn-outline-primary:hover {
            background: #22c55e;
            color: #fff;
        }

        hr {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .text-muted {
            color: rgba(241, 245, 249, 0.7) !important;
        }

        @media (max-width: 576px) {
            .main-card {
                padding: 30px 20px;
            }

            .btn-main {
                width: 100%;
                margin-bottom: 10px;
            }

            .d-flex.justify-content-center.gap-3 {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-8 col-lg-6">

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

                    <div class="d-flex justify-content-center gap-3 flex-wrap">

                        <a href="login.php" class="btn btn-primary btn-main">
                            Entrar
                        </a>

                        <a href="cadastro.php" class="btn btn-outline-primary btn-main">
                            Criar Conta
                        </a>

                    </div>

                    <hr class="my-4">

                    <div class="row text-center">

                        <div class="col-12 col-md-4 mb-3">
                            <h5>👥 Mesas</h5>
                            <p class="text-muted">Crie mesas privadas para apostas.</p>
                        </div>

                        <div class="col-12 col-md-4 mb-3">
                            <h5>📨 Convites</h5>
                            <p class="text-muted">Convide participantes facilmente.</p>
                        </div>

                        <div class="col-12 col-md-4 mb-3">
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