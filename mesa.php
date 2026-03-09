<?php
require 'conexao.php';
require 'header.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado");
}

$usuario_id = $_SESSION['usuario_id'];

$mesa_id = 0;

if (isset($_GET['id'])) {
    $mesa_id = intval($_GET['id']);
}

if (isset($_GET['mesa'])) {
    $mesa_id = intval($_GET['mesa']);
}

if ($mesa_id <= 0) {
    die("Mesa inválida.");
}

$stmt = $pdo->prepare("SELECT * FROM mesas WHERE id=?");
$stmt->execute([$mesa_id]);
$mesa = $stmt->fetch();

if (!$mesa) {
    die("Mesa não encontrada.");
}

$voto1 = $mesa['vencedor_reportado_1'];
$voto2 = $mesa['vencedor_reportado_2'];

$stmt = $pdo->prepare("
SELECT 
u.id,
u.nik,
p.pagou,
p.aceitou,
p.iniciou
FROM participantes p
JOIN usuarios u ON u.id = p.usuario_id
WHERE p.mesa_id=?
");

$stmt->execute([$mesa_id]);
$participantes = $stmt->fetchAll();

$adversario_id = null;

foreach ($participantes as $p) {
    if ($p['id'] != $usuario_id) {
        $adversario_id = $p['id'];
    }
}

$total = count($participantes);
$pagos = 0;

foreach ($participantes as $p) {
    if ($p['pagou']) {
        $pagos++;
    }
}

$progresso = $total > 0 ? round(($pagos / $total) * 100) : 0;

$stmt = $pdo->prepare("
SELECT pagou
FROM participantes
WHERE mesa_id=? AND usuario_id=?
");

$stmt->execute([$mesa_id, $usuario_id]);
$meu = $stmt->fetch();

$ja_pagou = $meu['pagou'] ?? 0;

if ($pagos == $total && $total > 0) {

    $pdo->prepare("
    UPDATE participantes
    SET iniciou=1
    WHERE mesa_id=?
    ")->execute([$mesa_id]);
}

$ja_votou = false;

$stmt = $pdo->prepare("
SELECT usuario_id
FROM participantes
WHERE mesa_id=?
ORDER BY id ASC
");

$stmt->execute([$mesa_id]);
$players = $stmt->fetchAll();

$j1 = $players[0]['usuario_id'] ?? null;
$j2 = $players[1]['usuario_id'] ?? null;

if ($usuario_id == $j1 && $voto1) {
    $ja_votou = true;
}

if ($usuario_id == $j2 && $voto2) {
    $ja_votou = true;
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <title>Mesa <?= $mesa_id ?></title>

    <style>
        body {
            background: #0f172a;
            font-family: Arial;
            color: #fff;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .card {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        input {
            padding: 10px;
            border-radius: 6px;
            border: none;
            width: 250px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            opacity: 0.9;
        }

        .btn-green {
            background: #22c55e;
            color: #fff;
        }

        .btn-red {
            background: #ef4444;
            color: #fff;
        }

        .btn-yellow {
            background: #facc15;
        }

        .progress {
            background: #334155;
            border-radius: 10px;
            overflow: hidden;
            height: 22px;
            margin-top: 10px;
        }

        .progress span {
            display: block;
            height: 100%;
            background: #22c55e;
            text-align: center;
            font-size: 12px;
            line-height: 22px;
        }

        .participante {
            display: flex;
            justify-content: space-between;
            background: #334155;
            padding: 10px;
            border-radius: 6px;
            margin-top: 8px;
        }

        .status {
            margin-top: 10px;
            font-size: 14px;
        }

        .pendente {
            color: #facc15;
        }

        .pago {
            color: #22c55e;
            font-weight: bold;
        }

        .vencedor {
            background: #22c55e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 22px;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2>🎲 Mesa #<?= $mesa_id ?></h2>

        <!-- CONVITE -->

        <div class="card">

            <h3>Enviar convite</h3>

            <form method="post" action="enviar_convite.php">

                <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">

                <input type="text" name="destino" placeholder="Nik ou Email" required>

                <button class="btn-green">Enviar</button>

            </form>

        </div>

        <!-- PROGRESSO -->

        <div class="card">

            <h3>Pagamentos da mesa</h3>

            <div class="progress">
                <span style="width:<?= $progresso ?>%">
                    <?= $progresso ?>%
                </span>
            </div>

            <p><?= $pagos ?> de <?= $total ?> participantes pagaram</p>

        </div>

        <!-- PARTICIPANTES -->

        <div class="card">

            <h3>Participantes</h3>

            <?php foreach ($participantes as $p): ?>

                <div class="participante">

                    <span><?= htmlspecialchars($p['nik']) ?></span>

                    <span>

                        <?php if ($p['aceitou'] == 0): ?>

                            <span class="pendente">Convite pendente</span>

                        <?php elseif ($p['pagou']): ?>

                            <span class="pago">Pago</span>

                        <?php else: ?>

                            <span class="pendente">Aguardando pagamento</span>

                        <?php endif; ?>

                    </span>

                </div>

            <?php endforeach; ?>

        </div>

        <!-- VENCEDOR -->

        <?php if ($mesa['status'] != 'finalizada' && $pagos == $total): ?>

            <div class="card">

                <h3>Quem venceu?</h3>

                <form method="post" action="reportar_vencedor.php">

                    <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">

                    <button class="btn-green"
                        name="vencedor_id"
                        value="<?= $usuario_id ?>"
                        <?= $ja_votou ? 'disabled' : '' ?>>

                        Fui eu

                    </button>

                    <?php if ($adversario_id): ?>

                        <button class="btn-red"
                            name="vencedor_id"
                            value="<?= $adversario_id ?>"
                            <?= $ja_votou ? 'disabled' : '' ?>>

                            Adversário

                        </button>

                    <?php endif; ?>

                </form>

                <?php if ($ja_votou): ?>

                    <p style="color:#22c55e;margin-top:10px;">
                        Você já confirmou o resultado.
                    </p>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <!-- PAGAMENTO -->

        <div class="card">

            <form method="post" action="confirmar_pagamento.php">

                <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">

                <button class="btn-yellow" <?= $ja_pagou ? 'disabled' : '' ?>>

                    <?= $ja_pagou ? 'Pagamento confirmado' : 'Confirmar pagamento' ?>

                </button>

            </form>

            <div class="status">
                Status da mesa: <b><?= $mesa['status'] ?></b>
            </div>

        </div>

        <!-- RESULTADO -->

        <?php if ($mesa['status'] == 'finalizada'): ?>

            <?php
            $stmt = $pdo->prepare("SELECT nik FROM usuarios WHERE id=?");
            $stmt->execute([$mesa['vencedor_id']]);
            $v = $stmt->fetch();
            ?>

            <div class="vencedor">

                🏆 Vencedor: <?= htmlspecialchars($v['nik']) ?>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>