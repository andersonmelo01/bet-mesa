<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$erro = '';

/* =========================
   CRIAR MESA
========================= */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $valor_aposta = floatval($_POST['valor_aposta']);
    $max_participantes = intval($_POST['max_participantes']);

    if ($titulo == '' || $valor_aposta <= 0 || $max_participantes < 2) {

        $erro = "Preencha os dados corretamente.";
    } else {

        /* Criar mesa */

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

        /* =========================
           CRIADOR ENTRA NA MESA
        ========================= */

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
    <title>Criar Mesa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
        }

        .card {
            border-radius: 12px;
        }
    </style>

</head>

<body>

    <div class="container mt-5">

        <div class="card shadow">

            <div class="card-header bg-dark text-white">
                <h4>🎲 Criar Nova Mesa</h4>
            </div>

            <div class="card-body">

                <?php if ($erro): ?>

                    <div class="alert alert-danger">
                        <?= $erro ?>
                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">Título da mesa</label>

                        <input
                            type="text"
                            name="titulo"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Descrição</label>

                        <textarea
                            name="descricao"
                            class="form-control"
                            rows="3"></textarea>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Valor total da aposta (R$)</label>

                        <input
                            type="number"
                            name="valor_aposta"
                            step="0.01"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Máximo de participantes</label>

                        <input
                            type="number"
                            name="max_participantes"
                            min="2"
                            max="50"
                            class="form-control"
                            required>

                    </div>

                    <button class="btn btn-success">
                        Criar Mesa
                    </button>

                    <a href="dashboard.php" class="btn btn-secondary">
                        Voltar
                    </a>

                </form>

            </div>

        </div>

    </div>

</body>

</html>