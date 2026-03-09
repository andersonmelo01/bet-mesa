<?php
session_start();
require 'conexao.php';

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$erro = '';

/* =========================
   CRIAR MESA
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $valor_aposta = floatval($_POST['valor_aposta']);
    $max_participantes = intval($_POST['max_participantes']);

    if ($titulo === '' || $valor_aposta <= 0 || $max_participantes < 2) {
        $erro = "Por favor, preencha todos os campos corretamente.";
    } else {
        // Criar mesa
        $stmt = $pdo->prepare("
            INSERT INTO mesas
            (titulo, descricao, valor_aposta, max_participantes, criador_id)
            VALUES (?,?,?,?,?)
        ");

        $stmt->execute([
            $titulo,
            $descricao,
            $valor_aposta,
            $max_participantes,
            $usuario_id
        ]);

        $mesa_id = $pdo->lastInsertId();

        // Criador entra automaticamente na mesa
        $stmt = $pdo->prepare("
            INSERT INTO participantes
            (mesa_id, usuario_id, aceitou, pagou, iniciou)
            VALUES (?,?,?,?,?)
        ");

        $stmt->execute([
            $mesa_id,
            $usuario_id,
            1, // aceitou
            0, // ainda não pagou
            0  // ainda não iniciou
        ]);

        header("Location: mesa.php?id=" . $mesa_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Mesa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ======================
           ESTILOS GERAIS
        ====================== */
        body {
            background: #0f172a;
            font-family: 'Arial', sans-serif;
            font-style: italic;
            color: #f1f5f9;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
        }

        .card-header {
            font-style: italic;
            background: linear-gradient(90deg, #1e293b, #0f172a);
            color: #22c55e;
            font-weight: bold;
        }

        .form-label {
            font-style: italic;
        }

        .form-control {
            border-radius: 8px;
            font-style: italic;
        }

        .btn-success {
            background: #22c55e;
            font-weight: bold;
            font-style: italic;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .btn-secondary {
            font-style: italic;
        }

        .alert-danger {
            font-style: italic;
        }

        @media (max-width: 576px) {
            .card {
                margin: 10px;
            }

            .btn-success,
            .btn-secondary {
                width: 100%;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>

    <div class="container mt-5">

        <div class="card shadow">

            <div class="card-header">
                <h4>🎲 Criar Nova Mesa</h4>
            </div>

            <div class="card-body">

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Título da Mesa</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Valor Total da Aposta (R$)</label>
                        <input type="number" name="valor_aposta" step="0.01" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Máximo de Participantes</label>
                        <input type="number" name="max_participantes" min="2" max="50" class="form-control" required>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-success">Criar Mesa</button>
                        <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>